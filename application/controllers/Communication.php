<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communication extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notice_model');
        $this->load->model('Announcement_model');
        $this->load->model('Communication_model');
        $this->load->model('Conversation_model');
        $this->load->model('Communication_group_model');
        $this->load->model('Communication_setting_model');
        $this->load->model('Notification_rule_model');
        $this->load->model('Notification_queue_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Staff_model');
        $this->load->model('Student_model');
        $this->load->library('Notification_engine');
    }

    public function index()
    {
        $this->dashboard();
    }

    // 1. Notification Dashboard
    public function dashboard()
    {
        $this->require_permission('communication.view');
        $data['title'] = 'Notification & Communication Dashboard';
        $data['stats'] = $this->Communication_model->get_dashboard_stats();
        $data['recent_notifications'] = $this->Communication_model->get_messages([], 6);
        $data['failed_notifications'] = $this->Notification_queue_model->get_failed();
        $data['active_rules_count'] = count($this->Notification_rule_model->get_all(['status' => 'Active']));
        $data['queued_count'] = count($this->Notification_queue_model->get_queue(['status' => 'Pending']));

        $this->render('pages/communication/dashboard', $data);
    }

    // 2. Centralized Notification Templates
    public function templates()
    {
        $this->require_permission('communication.manage_templates');
        if ($this->input->post('action') === 'create') {
            $code = strtoupper(trim($this->input->post('template_code')));
            
            // Check code uniqueness
            $existing = $this->Communication_model->get_template_by_code($code);
            if ($existing) {
                $this->session->set_flashdata('error', "Template code '{$code}' already exists!");
                redirect('communication/templates');
                return;
            }

            // Validate variables
            $val = $this->notification_engine->validate_template_variables($this->input->post('message_template'));
            if (!$val['valid']) {
                $this->session->set_flashdata('error', "Unsupported template variable: " . implode(', ', $val['unsupported']));
                redirect('communication/templates');
                return;
            }

            $tmplData = [
                'template_name'      => trim($this->input->post('template_name')),
                'template_code'      => $code,
                'category'           => $this->input->post('category') ?: 'General',
                'communication_type' => $this->input->post('category') ?: 'General',
                'channel'            => $this->input->post('channel') ?: 'SMS',
                'subject'            => $this->input->post('subject') ?: NULL,
                'message_template'   => trim($this->input->post('message_template')),
                'variables'          => implode(', ', $val['found'] ?: ['{student_name}', '{parent_name}']),
                'character_limit'    => $this->input->post('character_limit') ? (int)$this->input->post('character_limit') : NULL,
                'description'        => trim($this->input->post('description')),
                'is_system'          => 0,
                'status'             => 'Active',
            ];

            $this->Communication_model->insert_template($tmplData);
            $this->session->set_flashdata('success', "Template '{$tmplData['template_name']}' created successfully!");
            redirect('communication/templates');
            return;
        }

        $filters = [
            'channel'  => $this->input->get('channel') ?: NULL,
            'category' => $this->input->get('category') ?: NULL,
            'status'   => $this->input->get('status') !== NULL ? $this->input->get('status') : '',
            'search'   => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Notification Templates';
        $data['filters'] = $filters;
        $data['templates'] = $this->Communication_model->get_templates($filters);
        $data['supported_vars'] = Notification_engine::$SUPPORTED_VARIABLES;

        $this->render('pages/communication/templates', $data);
    }

    // 3. SMS Templates
    public function sms_templates()
    {
        $this->require_permission('communication.manage_templates');
        $filters = [
            'channel'  => 'SMS',
            'category' => $this->input->get('category') ?: NULL,
            'status'   => $this->input->get('status') !== NULL ? $this->input->get('status') : '',
            'search'   => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'SMS Templates';
        $data['filters'] = $filters;
        $data['templates'] = $this->Communication_model->get_templates($filters);
        $data['supported_vars'] = Notification_engine::$SUPPORTED_VARIABLES;

        $this->render('pages/communication/sms_templates', $data);
    }

    // 4. WhatsApp Templates
    public function whatsapp_templates()
    {
        $this->require_permission('communication.manage_templates');
        $filters = [
            'channel'  => 'WhatsApp',
            'category' => $this->input->get('category') ?: NULL,
            'status'   => $this->input->get('status') !== NULL ? $this->input->get('status') : '',
            'search'   => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'WhatsApp Templates';
        $data['filters'] = $filters;
        $data['templates'] = $this->Communication_model->get_templates($filters);
        $data['supported_vars'] = Notification_engine::$SUPPORTED_VARIABLES;

        $this->render('pages/communication/whatsapp_templates', $data);
    }

    // 5. Email Templates
    public function email_templates()
    {
        $this->require_permission('communication.manage_templates');
        $filters = [
            'channel'  => 'Email',
            'category' => $this->input->get('category') ?: NULL,
            'status'   => $this->input->get('status') !== NULL ? $this->input->get('status') : '',
            'search'   => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Email Templates';
        $data['filters'] = $filters;
        $data['templates'] = $this->Communication_model->get_templates($filters);
        $data['supported_vars'] = Notification_engine::$SUPPORTED_VARIABLES;

        $this->render('pages/communication/email_templates', $data);
    }

    // Template Duplicate / Toggle
    public function duplicate_template($id)
    {
        $this->require_permission('communication.manage_templates');
        $new_id = $this->Communication_model->duplicate_template($id);
        if ($new_id) {
            $this->session->set_flashdata('success', 'Template duplicated successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to duplicate template.');
        }
        redirect($this->agent->referrer() ?: 'communication/templates');
    }

    public function toggle_template($id)
    {
        $this->require_permission('communication.manage_templates');
        $tmpl = $this->Communication_model->get_template_by_id($id);
        if ($tmpl) {
            $new_st = ($tmpl->status === 'Active') ? 'Inactive' : 'Active';
            $this->Communication_model->update_template($id, ['status' => $new_st]);
            $this->session->set_flashdata('success', "Template status updated to {$new_st}.");
        }
        redirect($this->agent->referrer() ?: 'communication/templates');
    }

    // 6. Automated Notifications & Rules
    public function automated_notifications()
    {
        $this->require_permission('communication.automated_rules');
        if ($this->input->post('action') === 'create_rule') {
            $ruleData = [
                'rule_name'        => trim($this->input->post('rule_name')),
                'event_name'       => trim($this->input->post('event_name')),
                'source_module'    => $this->input->post('source_module') ?: 'General',
                'template_id'      => (int)$this->input->post('template_id'),
                'channel'          => $this->input->post('channel') ?: 'In-App',
                'recipient_type'   => $this->input->post('recipient_type') ?: 'Parent',
                'frequency'        => $this->input->post('frequency') ?: 'Once per event',
                'cooldown_minutes' => $this->input->post('cooldown_minutes') ? (int)$this->input->post('cooldown_minutes') : 60,
                'priority'         => $this->input->post('priority') ?: 'Normal',
                'status'           => 'Active',
                'created_by'       => $this->session->userdata('user_id') ?: 1,
            ];

            $this->Notification_rule_model->insert($ruleData);
            $this->session->set_flashdata('success', "Notification rule '{$ruleData['rule_name']}' created successfully!");
            redirect('communication/automated_notifications');
            return;
        }

        $filters = [
            'source_module' => $this->input->get('source_module') ?: NULL,
            'channel'       => $this->input->get('channel') ?: NULL,
            'status'        => $this->input->get('status') ?: NULL,
            'search'        => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Automated Notifications & Rules';
        $data['filters'] = $filters;
        $data['rules'] = $this->Notification_rule_model->get_all($filters);
        $data['templates'] = $this->Communication_model->get_templates(['status' => 'Active']);

        $this->render('pages/communication/automated_notifications', $data);
    }

    public function toggle_rule($rule_id)
    {
        $this->require_permission('communication.automated_rules');
        $this->Notification_rule_model->toggle_status($rule_id);
        $this->session->set_flashdata('success', 'Notification rule status updated.');
        redirect('communication/automated_notifications');
    }

    public function test_rule($rule_id)
    {
        $this->require_permission('communication.automated_rules');
        $rule = $this->Notification_rule_model->get_by_id($rule_id);
        if (!$rule) {
            $this->session->set_flashdata('error', 'Rule not found.');
            redirect('communication/automated_notifications');
            return;
        }

        // Trigger test event
        $sample_student = $this->Student_model->get_all(['limit' => 1])[0] ?? NULL;
        $context = [
            'student_id'         => $sample_student ? $sample_student->student_id : 1,
            '{student_name}'     => $sample_student ? ($sample_student->first_name . ' ' . $sample_student->last_name) : 'Aarav Nair',
            '{parent_name}'      => $sample_student ? ($sample_student->guardian_name ?: 'Suresh Nair') : 'Suresh Nair',
            '{admission_number}' => $sample_student ? $sample_student->admission_number : 'EDU2026001',
            '{class}'            => $sample_student ? $sample_student->class_name : 'Grade 10',
            '{section}'          => $sample_student ? $sample_student->section_name : 'A',
            '{amount}'           => '12,000.00',
            '{due_date}'         => date('d-m-Y', strtotime('+5 days')),
            '{exam_name}'        => 'Mid-Term Examination',
            '{assignment}'       => 'Mathematics Quadratic Equations Set',
            '{subject}'          => 'Mathematics'
        ];

        $res = $this->notification_engine->trigger_event($rule->event_name, $rule->source_module, 1, $context);
        $this->session->set_flashdata('success', "Test trigger executed for rule '{$rule->rule_name}'. Notifications queued/sent: {$res['queued_count']}");
        redirect('communication/queue');
    }

    // 7. Notification Queue
    public function queue()
    {
        $this->require_permission('communication.view');
        $filters = [
            'status'        => $this->input->get('status') ?: NULL,
            'channel'       => $this->input->get('channel') ?: NULL,
            'priority'      => $this->input->get('priority') ?: NULL,
            'source_module' => $this->input->get('source_module') ?: NULL,
        ];

        $data['title'] = 'Notification Queue';
        $data['filters'] = $filters;
        $data['queue'] = $this->Notification_queue_model->get_queue($filters);

        $this->render('pages/communication/queue', $data);
    }

    public function process_queue_item($id)
    {
        $this->require_permission('communication.send');
        $this->Notification_queue_model->process_item($id);
        $this->session->set_flashdata('success', "Queue item #{$id} processed and dispatched!");
        redirect('communication/queue');
    }

    public function cancel_queue_item($id)
    {
        $this->require_permission('communication.send');
        $this->notification_engine->cancel_notification($id);
        $this->session->set_flashdata('success', "Notification #{$id} cancelled.");
        redirect('communication/queue');
    }

    // 8. Notification History
    public function history()
    {
        $this->require_permission('communication.view');
        $filters = [
            'channel'        => $this->input->get('channel') ?: NULL,
            'source_module'  => $this->input->get('source_module') ?: NULL,
            'status'         => $this->input->get('status') ?: NULL,
            'recipient_type' => $this->input->get('recipient_type') ?: NULL,
            'start_date'     => $this->input->get('start_date') ?: NULL,
            'end_date'       => $this->input->get('end_date') ?: NULL,
            'search'         => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Notification History';
        $data['filters'] = $filters;
        $data['history'] = $this->Communication_model->get_messages($filters, 100);

        $this->render('pages/communication/history', $data);
    }

    // 9. Notification Details
    public function details($id)
    {
        $this->require_permission('communication.view');
        $msg = $this->Communication_model->get_message_by_id($id);
        if (!$msg) {
            $this->session->set_flashdata('error', 'Notification record not found.');
            redirect('communication/history');
            return;
        }

        $data['title'] = 'Notification Details #' . $msg->message_id;
        $data['msg'] = $msg;

        $this->render('pages/communication/details', $data);
    }

    // 10. Failed Notifications
    public function failed()
    {
        $this->require_permission('communication.view');
        $filters = [
            'channel'       => $this->input->get('channel') ?: NULL,
            'source_module' => $this->input->get('source_module') ?: NULL,
        ];

        $data['title'] = 'Failed Notifications';
        $data['filters'] = $filters;
        $data['failed_list'] = $this->Notification_queue_model->get_failed($filters);

        $this->render('pages/communication/failed', $data);
    }

    public function retry_failed($id)
    {
        $this->require_permission('communication.send');
        $this->notification_engine->retry_notification($id);
        $this->session->set_flashdata('success', "Notification #{$id} retried successfully.");
        redirect('communication/failed');
    }

    // 11. Delivery Reports
    public function reports()
    {
        $this->require_permission('communication.view');
        $reports = $this->Communication_model->get_delivery_reports();
        
        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=notification_delivery_report_' . date('Y-m-d') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Channel', 'Total Sent', 'Delivered', 'Failed', 'Pending', 'Delivery Rate %']);
            foreach ($reports['channel_report'] as $r) {
                fputcsv($out, [$r->channel, $r->total, $r->delivered, $r->failed, $r->pending, $r->rate . '%']);
            }
            fputcsv($out, []);
            fputcsv($out, ['Module', 'Total Messages', 'Delivered', 'Failed', 'Delivery Rate %']);
            foreach ($reports['module_report'] as $m) {
                fputcsv($out, [$m->module, $m->total, $m->delivered, $m->failed, $m->rate . '%']);
            }
            fclose($out);
            exit;
        }

        $data['title'] = 'Notification Delivery Reports';
        $data['reports'] = $reports;
        $data['stats'] = $this->Communication_model->get_dashboard_stats();

        $this->render('pages/communication/reports', $data);
    }

    // 12. Notification Settings
    public function settings()
    {
        $this->require_permission('communication.manage_templates');
        if ($this->input->post()) {
            $settingsData = [
                'enable_inapp'            => $this->input->post('enable_inapp') ? 1 : 0,
                'enable_sms'              => $this->input->post('enable_sms') ? 1 : 0,
                'enable_whatsapp'         => $this->input->post('enable_whatsapp') ? 1 : 0,
                'enable_email'            => $this->input->post('enable_email') ? 1 : 0,
                'sms_provider'            => trim($this->input->post('sms_provider')),
                'sms_sender_id'           => trim($this->input->post('sms_sender_id')),
                'whatsapp_provider'       => trim($this->input->post('whatsapp_provider')),
                'email_from_name'         => trim($this->input->post('email_from_name')),
                'email_from_address'      => trim($this->input->post('email_from_address')),
                'max_retries'             => (int)$this->input->post('max_retries'),
                'retry_interval_minutes'  => (int)$this->input->post('retry_interval_minutes'),
                'enable_scheduled_jobs'   => $this->input->post('enable_scheduled_jobs') ? 1 : 0,
            ];

            $this->Communication_setting_model->update_settings($settingsData);
            $this->session->set_flashdata('success', 'Notification settings saved successfully!');
            redirect('communication/settings');
            return;
        }

        $data['title'] = 'Notification & Communication Settings';
        $data['settings'] = $this->Communication_setting_model->get_settings();
        $data['audit_logs'] = $this->Communication_model->get_recent_audit_logs(30);

        $this->render('pages/communication/settings', $data);
    }

    // AJAX Variable Live Preview
    public function preview_template()
    {
        $this->require_permission('communication.manage_templates');
        $raw_template = $this->input->post('template_text');
        $sample_student = $this->Student_model->get_all(['limit' => 1])[0] ?? NULL;
        $context = [
            '{student_name}'     => $sample_student ? ($sample_student->first_name . ' ' . $sample_student->last_name) : 'Aarav Nair',
            '{parent_name}'      => $sample_student ? ($sample_student->guardian_name ?: 'Suresh Nair') : 'Suresh Nair',
            '{admission_number}' => $sample_student ? $sample_student->admission_number : 'EDU2026001',
            '{class}'            => $sample_student ? $sample_student->class_name : 'Grade 10',
            '{section}'          => $sample_student ? $sample_student->section_name : 'A',
            '{amount}'           => '12,000.00',
            '{due_date}'         => date('d-m-Y', strtotime('+5 days')),
            '{exam_name}'        => 'Mid-Term Examination 2026',
            '{assignment}'       => 'Quadratic Equations Assignment 2',
            '{subject}'          => 'Mathematics',
            '{staff_name}'       => 'Priya Varma',
            '{teacher_name}'     => 'Priya Varma',
            '{leave_type}'       => 'Medical Leave',
            '{route}'            => 'Route 01 - North',
            '{stop}'             => 'Aluva Bypass'
        ];

        $compiled = $this->notification_engine->compile_message($raw_template, $context);
        $val = $this->notification_engine->validate_template_variables($raw_template);

        echo json_encode([
            'valid'       => $val['valid'],
            'unsupported' => $val['unsupported'],
            'compiled'    => $compiled,
            'char_count'  => mb_strlen($compiled)
        ]);
    }

    // Existing Notices & Circulars Handlers
    public function notices()
    {
        $this->require_permission('communication.view');
        $filters = [
            'category'    => $this->input->get('category') ?: NULL,
            'priority'    => $this->input->get('priority') ?: NULL,
            'target_role' => $this->input->get('target_role') ?: NULL,
            'status'      => $this->input->get('status') ?: NULL,
            'search'      => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Notices & Circulars';
        $data['filters'] = $filters;
        $data['notices'] = $this->Notice_model->get_all($filters);

        $this->render('pages/communication/notices', $data);
    }

    public function create_notice()
    {
        $this->require_permission('communication.send');
        if ($this->input->post()) {
            $attachment = NULL;
            if (!empty($_FILES['attachment']['name'])) {
                $upload_path = './uploads/notices/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png|zip|txt';
                $config['max_size']      = 10240;
                $this->load->library('upload', $config);

                if ($this->upload->do_upload('attachment')) {
                    $upData = $this->upload->data();
                    $attachment = $upData['file_name'];
                }
            }

            $status = $this->input->post('submit_action') === 'publish' ? 'Published' : ($this->input->post('submit_action') === 'schedule' ? 'Scheduled' : 'Draft');

            $noticeData = [
                'title'        => trim($this->input->post('title')),
                'category'     => $this->input->post('category') ?: 'General',
                'content'      => trim($this->input->post('content')),
                'target_role'  => $this->input->post('target_role') ?: 'All',
                'target_type'  => $this->input->post('target_type') ?: 'Entire School',
                'class_id'     => $this->input->post('class_id') ?: NULL,
                'division_id'   => $this->input->post('division_id') ?: NULL,
                'priority'     => $this->input->post('priority') ?: 'Normal',
                'attachment'   => $attachment,
                'publish_date' => $this->input->post('publish_date') ?: date('Y-m-d'),
                'expiry_date'  => $this->input->post('expiry_date') ?: NULL,
                'posted_by'    => $this->session->userdata('full_name') ?: 'Admin',
                'posted_by_id' => $this->session->userdata('user_id') ?: 1,
                'status'       => $status,
            ];

            $this->Notice_model->insert($noticeData);
            $this->session->set_flashdata('success', "Notice '{$noticeData['title']}' created successfully (" . $status . ")!");
            redirect('communication/notices');
            return;
        }

        $data['title'] = 'Create Notice';
        $data['classes'] = $this->Class_model->get_all();
        $this->render('pages/communication/create_notice', $data);
    }

    public function announcements()
    {
        $this->require_permission('communication.view');
        $data['title'] = 'School Announcements';
        $data['announcements'] = $this->Announcement_model->get_all();
        $this->render('pages/communication/announcements', $data);
    }

    public function conversations()
    {
        $this->require_permission('communication.view');
        $data['title'] = 'Conversations';
        $data['conversations'] = $this->Conversation_model->get_all();
        $this->render('pages/communication/conversations', $data);
    }

    public function groups()
    {
        $this->require_permission('communication.view');
        $data['title'] = 'Communication Groups';
        $data['groups'] = $this->Communication_group_model->get_all();
        $this->render('pages/communication/groups', $data);
    }
}
