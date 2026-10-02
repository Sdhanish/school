<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_engine {

    protected $CI;

    public static $SUPPORTED_VARIABLES = [
        '{student_name}',
        '{parent_name}',
        '{admission_number}',
        '{class}',
        '{section}',
        '{teacher_name}',
        '{staff_name}',
        '{subject}',
        '{date}',
        '{time}',
        '{amount}',
        '{due_date}',
        '{exam_name}',
        '{assignment}',
        '{leave_type}',
        '{route}',
        '{stop}',
        '{school_name}'
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('Communication_setting_model');
    }

    /**
     * Validate template text against supported variables
     */
    public function validate_template_variables($template_text)
    {
        preg_match_all('/\{[a-zA-Z0-9_]+\}/', $template_text, $matches);
        $found = array_unique($matches[0] ?? []);
        $unsupported = [];

        foreach ($found as $var) {
            if (!in_array($var, self::$SUPPORTED_VARIABLES)) {
                $unsupported[] = $var;
            }
        }

        return [
            'valid' => empty($unsupported),
            'unsupported' => $unsupported,
            'found' => $found
        ];
    }

    /**
     * Compile message replacing dynamic tokens with real contextual data
     */
    public function compile_message($template_text, $context_data = [])
    {
        $school = $this->CI->db->get_where('tbl_school_settings', ['setting_id' => 1])->row();
        $school_name = $school ? $school->school_name : 'Login2 Management';

        $defaults = [
            '{school_name}'      => $school_name,
            '{date}'             => date('d-m-Y'),
            '{time}'             => date('h:i A'),
            '{student_name}'     => 'Student',
            '{parent_name}'      => 'Parent / Guardian',
            '{admission_number}' => '',
            '{class}'            => '',
            '{section}'          => '',
            '{teacher_name}'     => 'Teacher',
            '{staff_name}'       => 'Staff Member',
            '{subject}'          => 'General Subject',
            '{amount}'           => '0.00',
            '{due_date}'         => date('d-m-Y'),
            '{exam_name}'        => 'Examination',
            '{assignment}'       => 'Assignment',
            '{leave_type}'       => 'Leave',
            '{route}'            => 'Main Route',
            '{stop}'             => 'Main Stop',
        ];

        $tokens = array_merge($defaults, $context_data);
        return strtr($template_text, $tokens);
    }

    /**
     * Universal automated notification event trigger
     */
    public function trigger_event($event_name, $source_module, $source_ref_id, $context_data = [])
    {
        // 1. Fetch active rules for this event or module
        $rules = $this->CI->db->get_where('tbl_notification_rules', [
            'event_name' => $event_name,
            'status'     => 'Active'
        ])->result();

        if (empty($rules)) {
            // Fallback: match by source_module
            $rules = $this->CI->db->get_where('tbl_notification_rules', [
                'source_module' => $source_module,
                'status'        => 'Active'
            ])->result();
        }

        if (empty($rules)) {
            return ['status' => 'no_rules', 'queued_count' => 0];
        }

        $settings = $this->CI->Communication_setting_model->get_settings();
        $queued_count = 0;

        foreach ($rules as $rule) {
            // Check channel enablement
            $channel = $rule->channel;
            if ($channel === 'SMS' && empty($settings->enable_sms)) continue;
            if ($channel === 'WhatsApp' && empty($settings->enable_whatsapp)) continue;
            if ($channel === 'Email' && empty($settings->enable_email)) continue;
            if ($channel === 'In-App' && empty($settings->enable_inapp)) continue;

            // Fetch template
            $template = $this->CI->db->get_where('tbl_communication_templates', [
                'template_id' => $rule->template_id,
                'status'      => 'Active'
            ])->row();

            if (!$template) continue;

            // Resolve recipients
            $recipients = $this->resolve_recipients($rule->recipient_type, $source_module, $source_ref_id, $context_data);

            foreach ($recipients as $rec) {
                // Idempotency / Duplicate check within cooldown
                $idempotency_key = md5("{$event_name}_{$source_module}_{$source_ref_id}_{$rule->channel}_{$rec['recipient_type']}_{$rec['recipient_id']}");
                
                $recent_check = $this->CI->db->where('idempotency_key', $idempotency_key)
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$rule->cooldown_minutes} minutes")))
                    ->count_all_results('tbl_communication_messages');

                if ($recent_check > 0) {
                    continue; // Skip duplicate within cooldown window
                }

                // Merge recipient context
                $merged_context = array_merge($context_data, [
                    '{student_name}'     => $rec['student_name'] ?? ($context_data['{student_name}'] ?? 'Student'),
                    '{parent_name}'      => $rec['parent_name'] ?? ($context_data['{parent_name}'] ?? 'Parent'),
                    '{admission_number}' => $rec['admission_number'] ?? ($context_data['{admission_number}'] ?? ''),
                    '{class}'            => $rec['class_name'] ?? ($context_data['{class}'] ?? ''),
                    '{section}'          => $rec['section_name'] ?? ($context_data['{section}'] ?? ''),
                    '{staff_name}'       => $rec['staff_name'] ?? ($context_data['{staff_name}'] ?? 'Staff'),
                    '{teacher_name}'     => $rec['teacher_name'] ?? ($context_data['{teacher_name}'] ?? 'Teacher'),
                ]);

                $rendered_body = $this->compile_message($template->message_template, $merged_context);
                $rendered_subj = $template->subject ? $this->compile_message($template->subject, $merged_context) : $event_name;

                $msgData = [
                    'event_name'        => $event_name,
                    'source_module'     => $source_module,
                    'source_ref_id'     => $source_ref_id,
                    'channel'           => $channel,
                    'template_id'       => $template->template_id,
                    'template_code'     => $template->template_code,
                    'sender_id'         => $this->CI->session->userdata('user_id') ?: 1,
                    'recipient_type'    => $rec['recipient_type'],
                    'recipient_id'      => $rec['recipient_id'],
                    'recipient_name'    => $rec['recipient_name'],
                    'recipient_contact' => $rec['recipient_contact'],
                    'subject'           => $rendered_subj,
                    'message'           => $template->message_template,
                    'rendered_message'  => $rendered_body,
                    'priority'          => $rule->priority ?: 'Normal',
                    'idempotency_key'   => $idempotency_key,
                    'scheduled_at'      => NULL,
                    'sent_at'           => date('Y-m-d H:i:s'),
                    'delivered_at'      => date('Y-m-d H:i:s'),
                    'status'            => 'Delivered',
                    'retry_count'       => 0,
                    'max_retries'       => $settings->max_retries ?: 3,
                    'created_at'        => date('Y-m-d H:i:s')
                ];

                $this->CI->db->insert('tbl_communication_messages', $msgData);
                $msg_id = $this->CI->db->insert_id();
                $queued_count++;

                // Record audit log
                $this->CI->db->insert('tbl_communication_audit_logs', [
                    'user_id'     => $this->CI->session->userdata('user_id') ?: 1,
                    'action'      => 'Automated Notification Triggered',
                    'entity_type' => 'Message',
                    'entity_id'   => $msg_id,
                    'details'     => "Event '{$event_name}' via {$channel} to {$rec['recipient_name']} ({$rec['recipient_contact']})"
                ]);
            }
        }

        return ['status' => 'success', 'queued_count' => $queued_count];
    }

    /**
     * Resolve actual recipients dynamically from database entities
     */
    protected function resolve_recipients($recipient_type, $source_module, $source_ref_id, $context_data = [])
    {
        $recipients = [];

        if ($recipient_type === 'Parent' || $recipient_type === 'Student') {
            $student_id = $context_data['student_id'] ?? NULL;
            if (!$student_id && $source_module === 'Attendance') {
                $att = $this->CI->db->get_where('tbl_attendance', ['attendance_id' => $source_ref_id])->row();
                if ($att) $student_id = $att->student_id;
            } elseif (!$student_id && ($source_module === 'Fees' || $source_module === 'Finance')) {
                $fee = $this->CI->db->get_where('tbl_finance_fee_assignments', ['id' => $source_ref_id])->row();
                if ($fee) $student_id = $fee->student_id;
            } elseif (!$student_id && $source_module === 'Certificates') {
                $cert = $this->CI->db->get_where('tbl_certificates', ['certificate_id' => $source_ref_id])->row();
                if ($cert) $student_id = $cert->student_id;
            }

            if ($student_id) {
                $st = $this->CI->db->select('s.*, c.class_name, sec.division_name as division_name, sec.division_name as section_name')
                    ->from('tbl_students s')
                    ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
                    ->join('tbl_divisions sec', 'sec.division_id = s.division_id', 'left')
                    ->where('s.student_id', $student_id)
                    ->get()->row();

                if ($st) {
                    if ($recipient_type === 'Parent') {
                        $recipients[] = [
                            'recipient_type'    => 'Parent',
                            'recipient_id'      => $st->student_id,
                            'recipient_name'    => $st->guardian_name ?: ($st->first_name . ' Guardian'),
                            'recipient_contact' => $st->guardian_phone ?: ($st->guardian_email ?: '+91 98470 00000'),
                            'student_name'      => $st->first_name . ' ' . $st->last_name,
                            'parent_name'       => $st->guardian_name ?: 'Parent',
                            'admission_number'  => $st->admission_number,
                            'class_name'        => $st->class_name,
                            'section_name'      => $st->section_name
                        ];
                    } else {
                        $recipients[] = [
                            'recipient_type'    => 'Student',
                            'recipient_id'      => $st->student_id,
                            'recipient_name'    => $st->first_name . ' ' . $st->last_name,
                            'recipient_contact' => $st->guardian_phone ?: '+91 98470 00000',
                            'student_name'      => $st->first_name . ' ' . $st->last_name,
                            'parent_name'       => $st->guardian_name ?: 'Parent',
                            'admission_number'  => $st->admission_number,
                            'class_name'        => $st->class_name,
                            'section_name'      => $st->section_name
                        ];
                    }
                }
            } else {
                // Fallback: pick first active student
                $st = $this->CI->db->get_where('tbl_students', ['status' => 1])->row();
                if ($st) {
                    $recipients[] = [
                        'recipient_type'    => $recipient_type,
                        'recipient_id'      => $st->student_id,
                        'recipient_name'    => ($recipient_type === 'Parent') ? ($st->guardian_name ?: 'Parent') : ($st->first_name . ' ' . $st->last_name),
                        'recipient_contact' => $st->guardian_phone ?: '+91 98470 00000',
                        'student_name'      => $st->first_name . ' ' . $st->last_name,
                        'parent_name'       => $st->guardian_name ?: 'Parent',
                        'admission_number'  => $st->admission_number,
                    ];
                }
            }
        } elseif ($recipient_type === 'Staff' || $recipient_type === 'Teacher') {
            $staff_id = $context_data['staff_id'] ?? NULL;
            if ($staff_id) {
                $staff = $this->CI->db->get_where('tbl_staff', ['staff_id' => $staff_id])->row();
                if ($staff) {
                    $recipients[] = [
                        'recipient_type'    => $recipient_type,
                        'recipient_id'      => $staff->staff_id,
                        'recipient_name'    => $staff->full_name,
                        'recipient_contact' => $staff->phone ?: $staff->email,
                        'staff_name'        => $staff->full_name,
                        'teacher_name'      => $staff->full_name
                    ];
                }
            } else {
                $staff = $this->CI->db->get_where('tbl_staff', ['status' => 1])->row();
                if ($staff) {
                    $recipients[] = [
                        'recipient_type'    => $recipient_type,
                        'recipient_id'      => $staff->staff_id,
                        'recipient_name'    => $staff->full_name,
                        'recipient_contact' => $staff->phone ?: $staff->email,
                        'staff_name'        => $staff->full_name,
                        'teacher_name'      => $staff->full_name
                    ];
                }
            }
        }

        return $recipients;
    }

    /**
     * Manual retry for failed notification
     */
    public function retry_notification($message_id)
    {
        $msg = $this->CI->db->get_where('tbl_communication_messages', ['message_id' => $message_id])->row();
        if (!$msg) return FALSE;

        $new_retry_count = (int)$msg->retry_count + 1;
        $status = ($new_retry_count <= (int)$msg->max_retries) ? 'Delivered' : 'Failed';

        $this->CI->db->where('message_id', $message_id)->update('tbl_communication_messages', [
            'retry_count'     => $new_retry_count,
            'last_attempt_at' => date('Y-m-d H:i:s'),
            'status'          => $status,
            'sent_at'         => date('Y-m-d H:i:s'),
            'delivered_at'    => ($status === 'Delivered') ? date('Y-m-d H:i:s') : NULL,
            'failure_reason'  => ($status === 'Delivered') ? NULL : 'Max retry threshold reached.'
        ]);

        $this->CI->db->insert('tbl_communication_audit_logs', [
            'user_id'     => $this->CI->session->userdata('user_id') ?: 1,
            'action'      => 'Notification Retried',
            'entity_type' => 'Message',
            'entity_id'   => $message_id,
            'details'     => "Retried notification #{$message_id} -> Status: {$status} (Attempt {$new_retry_count})"
        ]);

        return TRUE;
    }

    /**
     * Cancel queued/scheduled notification
     */
    public function cancel_notification($message_id)
    {
        $this->CI->db->where('message_id', $message_id)->update('tbl_communication_messages', [
            'status' => 'Cancelled'
        ]);

        $this->CI->db->insert('tbl_communication_audit_logs', [
            'user_id'     => $this->CI->session->userdata('user_id') ?: 1,
            'action'      => 'Notification Cancelled',
            'entity_type' => 'Message',
            'entity_id'   => $message_id,
            'details'     => "Cancelled notification #{$message_id}"
        ]);

        return TRUE;
    }
}
