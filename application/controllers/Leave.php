<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leave_model');
        $this->load->model('Leave_type_model');
        $this->load->model('Leave_balance_model');
        $this->load->model('Leave_setting_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Student_model');
        $this->load->model('Staff_model');
    }

    // 1. Leave Dashboard
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission('leave.view');
        $year_id = $this->academic_year_id;
        $data['title'] = 'Leave Dashboard';
        $data['stats'] = $this->Leave_model->get_dashboard_stats($year_id);
        $data['recent_student_leaves'] = $this->Leave_model->get_applications(['applicant_type' => 'Student', 'academic_year_id' => $year_id], 5);
        $data['recent_staff_leaves'] = $this->Leave_model->get_applications(['applicant_type' => 'Staff'], 5);
        $data['pending_approvals'] = $this->Leave_model->get_applications(['status' => 'Pending', 'academic_year_id' => $year_id], 6);

        $this->render('pages/leave/dashboard', $data);
    }

    // 2. Student Leave Directory
    public function student_leave()
    {
        $this->require_permission('leave.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $filters = [
            'academic_year_id' => $year_id,
            'applicant_type'   => 'Student',
            'class_id'         => $this->input->get('class_id') ?: NULL,
            'division_id'      => $this->input->get('division_id') ?: NULL,
            'leave_type_id'    => $this->input->get('leave_type_id') ?: NULL,
            'status'           => $this->input->get('status') ?: NULL,
            'search'           => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Student Leave Management';
        $data['filters'] = $filters;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['leave_types'] = $this->Leave_type_model->get_all('Students');

        $this->render('pages/leave/student_leave', $data);
    }

    public function ajax_student_leave_list()
    {
        $this->require_permission('leave.view');

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        $order = $this->input->post('order');
        $order_col = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

        $search_val = $this->input->post('search');
        $search = isset($search_val['value']) ? trim($search_val['value']) : '';

        $filters = [
            'academic_year_id' => $this->input->post('academic_year_id') ?: $this->academic_year_id,
            'applicant_type'   => 'Student',
            'class_id'         => $this->input->post('class_id') ?: NULL,
            'leave_type_id'    => $this->input->post('leave_type_id') ?: NULL,
            'status'           => $this->input->post('status') ?: NULL,
            'search'           => $search
        ];

        $total_records = $this->Leave_model->count_applications_all($this->school_id, 'Student', $filters['academic_year_id']);
        $filtered_records = $this->Leave_model->count_applications_filtered($filters);
        $records = $this->Leave_model->get_applications_datatables_data($filters, $start, $length, $order_col, $order_dir);

        $data = array();
        foreach ($records as $a) {
            $stBadge = ($a->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($a->status === 'Pending') ? 'bg-amber-100 text-amber-900' : (($a->status === 'Clarification Required') ? 'bg-primary-container text-on-primary-container' : 'bg-error-container text-error'));

            $student_html = '<strong class="text-on-surface block">' . html_escape($a->first_name . ' ' . $a->last_name) . '</strong>' .
                '<span class="text-[11px] text-on-surface-variant font-mono">' . html_escape($a->admission_number ?? $a->admission_no ?? '') . '</span>';

            $class_sec = html_escape(trim(($a->class_name ?? '') . ($a->section_name ? ' - ' . $a->section_name : '')));

            $leave_type_html = '<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">' .
                html_escape($a->type_name) . '</span>';

            $dates_html = date('d M Y', strtotime($a->from_date));
            if ($a->from_date !== $a->to_date) {
                $dates_html .= '<span class="block text-on-surface-variant text-[11px]">to ' . date('d M Y', strtotime($a->to_date)) . '</span>';
            }

            $days_html = '<div class="text-center font-bold text-on-surface">' . $a->duration_days . ($a->is_half_day ? '<span class="block text-[10px] text-primary">(Half Day)</span>' : '') . '</div>';

            $reason_text = html_escape(mb_strimwidth($a->reason ?? '', 0, 80, '...'));
            $reason_html = '<span class="text-[13px] text-on-surface-variant max-w-[200px] truncate block" title="' . html_escape($a->reason ?? '') . '">' . $reason_text . '</span>';

            $status_html = '<div class="text-center"><span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold ' . $stBadge . '">' . html_escape($a->status) . '</span></div>';

            $action_html = '<div class="text-center"><a href="' . site_url('leave/details/' . $a->application_id) . '" class="p-1.5 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">visibility</span>Details</a></div>';

            $data[] = array(
                $student_html,
                $class_sec,
                $leave_type_html,
                $dates_html,
                $days_html,
                $reason_html,
                $status_html,
                $action_html
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => (int)$total_records,
            'recordsFiltered' => (int)$filtered_records,
            'data' => $data
        ));
        exit;
    }

    // 3. Staff Leave Directory
    public function staff_leave()
    {
        $this->require_permission('leave.view');
        $filters = [
            'applicant_type' => 'Staff',
            'leave_type_id'  => $this->input->get('leave_type_id') ?: NULL,
            'status'         => $this->input->get('status') ?: NULL,
            'search'         => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Staff Leave Management';
        $data['filters'] = $filters;
        $data['leave_types'] = $this->Leave_type_model->get_all('Staff');

        $this->render('pages/leave/staff_leave', $data);
    }

    public function ajax_staff_leave_list()
    {
        $this->require_permission('leave.view');

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        $order = $this->input->post('order');
        $order_col = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

        $search_val = $this->input->post('search');
        $search = isset($search_val['value']) ? trim($search_val['value']) : '';

        $filters = [
            'applicant_type' => 'Staff',
            'leave_type_id'  => $this->input->post('leave_type_id') ?: NULL,
            'status'         => $this->input->post('status') ?: NULL,
            'search'         => $search
        ];

        $total_records = $this->Leave_model->count_applications_all($this->school_id, 'Staff');
        $filtered_records = $this->Leave_model->count_applications_filtered($filters);
        $records = $this->Leave_model->get_applications_datatables_data($filters, $start, $length, $order_col, $order_dir);

        $data = array();
        foreach ($records as $a) {
            $stBadge = ($a->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($a->status === 'Pending') ? 'bg-amber-100 text-amber-900' : (($a->status === 'Clarification Required') ? 'bg-primary-container text-on-primary-container' : 'bg-error-container text-error'));

            $staff_html = '<strong class="text-on-surface block">' . html_escape($a->staff_name) . '</strong>' .
                '<span class="text-[11px] text-on-surface-variant font-mono">' . html_escape($a->employee_code) . '</span>';

            $desig_html = html_escape($a->designation_name ?: 'Staff');

            $leave_type_html = '<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">' .
                html_escape($a->type_name) . '</span>';

            $dates_html = date('d M Y', strtotime($a->from_date));
            if ($a->from_date !== $a->to_date) {
                $dates_html .= '<span class="block text-on-surface-variant text-[11px]">to ' . date('d M Y', strtotime($a->to_date)) . '</span>';
            }

            $days_html = '<div class="text-center font-bold text-on-surface">' . $a->duration_days . ($a->is_half_day ? '<span class="block text-[10px] text-primary">(Half Day)</span>' : '') . '</div>';

            $reason_text = html_escape(mb_strimwidth($a->reason ?? '', 0, 80, '...'));
            $reason_html = '<span class="text-[13px] text-on-surface-variant max-w-[200px] truncate block" title="' . html_escape($a->reason ?? '') . '">' . $reason_text . '</span>';

            $status_html = '<div class="text-center"><span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold ' . $stBadge . '">' . html_escape($a->status) . '</span></div>';

            $action_html = '<div class="text-center"><a href="' . site_url('leave/details/' . $a->application_id) . '" class="p-1.5 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">visibility</span>Details</a></div>';

            $data[] = array(
                $staff_html,
                $desig_html,
                $leave_type_html,
                $dates_html,
                $days_html,
                $reason_html,
                $status_html,
                $action_html
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => (int)$total_records,
            'recordsFiltered' => (int)$filtered_records,
            'data' => $data
        ));
        exit;
    }

    // 4. Leave Types Management
    public function types()
    {
        $this->require_permission('leave.view');
        if ($this->input->post()) {
            $this->require_permission('leave.approve');
            $type_id = (int)$this->input->post('type_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Leave_type_model->delete_or_deactivate($type_id);
                $this->session->set_flashdata('success', 'Leave type removed/deactivated.');
            } else {
                $postData = [
                    'type_name'           => trim($this->input->post('type_name')),
                    'type_code'           => strtoupper(trim($this->input->post('type_code'))),
                    'applicable_to'       => $this->input->post('applicable_to') ?: 'Both',
                    'description'         => trim($this->input->post('description')),
                    'max_days'            => (int)$this->input->post('max_days'),
                    'requires_document'   => $this->input->post('requires_document') ? 1 : 0,
                    'requires_approval'   => $this->input->post('requires_approval') ? 1 : 0,
                    'allow_half_day'      => $this->input->post('allow_half_day') ? 1 : 0,
                    'allow_carry_forward' => $this->input->post('allow_carry_forward') ? 1 : 0,
                    'status'              => $this->input->post('status') ? 1 : 0
                ];

                if ($type_id > 0) {
                    $this->Leave_type_model->update($type_id, $postData);
                    $this->session->set_flashdata('success', 'Leave type updated.');
                } else {
                    $this->Leave_type_model->insert($postData);
                    $this->session->set_flashdata('success', 'New leave type created.');
                }
            }
            redirect('leave/types');
            return;
        }

        $data['title'] = 'Leave Types';
        $data['types'] = $this->Leave_type_model->get_all();

        $this->render('pages/leave/types', $data);
    }

    // 5. Leave Request Form (Student & Staff)
    public function request()
    {
        $this->require_permission('leave.apply');
        if ($this->input->post()) {
            $applicant_type = $this->input->post('applicant_type') ?: 'Student';
            $from_date = $this->input->post('from_date');
            $to_date = $this->input->post('to_date') ?: $from_date;
            $is_half_day = $this->input->post('is_half_day') ? 1 : 0;
            $half_day_type = $this->input->post('half_day_type') ?: 'Full Day';
            $leave_type_id = (int)$this->input->post('leave_type_id');

            $entity_id = ($applicant_type === 'Student') ? (int)$this->input->post('student_id') : (int)$this->input->post('staff_id');

            // Overlapping check
            if ($this->Leave_model->check_overlapping($applicant_type, $entity_id, $from_date, $to_date)) {
                $this->session->set_flashdata('error', 'A leave request already exists or overlaps with the selected date range.');
                redirect('leave/request');
                return;
            }

            // Duration calculation
            $duration = $this->Leave_model->calculate_duration($from_date, $to_date, $is_half_day);

            // Attachment upload
            $attachment = NULL;
            if (!empty($_FILES['attachment']['name'])) {
                $upload_path = './uploads/leaves/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                $config['max_size']      = 10240;
                $this->load->library('upload', $config);

                if ($this->upload->do_upload('attachment')) {
                    $upData = $this->upload->data();
                    $attachment = $upData['file_name'];
                }
            }

            // Class & Section lookup for student
            $class_id = NULL;
            $section_id = NULL;
            if ($applicant_type === 'Student') {
                $st = $this->Student_model->get_by_id($entity_id);
                if ($st) {
                    $class_id = $st->class_id;
                    $section_id = $st->section_id;
                }
            }

            $appData = [
                'applicant_type'   => $applicant_type,
                'student_id'       => ($applicant_type === 'Student') ? $entity_id : NULL,
                'staff_id'         => ($applicant_type === 'Staff') ? $entity_id : NULL,
                'academic_year_id' => $this->academic_year_id,
                'class_id'         => $class_id,
                'division_id'       => $section_id,
                'leave_type_id'    => $leave_type_id,
                'from_date'        => $from_date,
                'to_date'          => $to_date,
                'duration_days'    => $duration,
                'is_half_day'      => $is_half_day,
                'half_day_type'    => $half_day_type,
                'reason'           => trim($this->input->post('reason')),
                'attachment'       => $attachment,
                'status'           => 'Pending'
            ];

            $app_id = $this->Leave_model->submit_application($appData);
            $this->session->set_flashdata('success', "Leave request submitted successfully ({$duration} days).");

            redirect($applicant_type === 'Student' ? 'leave/student_leave' : 'leave/staff_leave');
            return;
        }

        $data['title'] = 'Apply for Leave';
        $data['leave_types'] = $this->Leave_type_model->get_all(NULL, TRUE);
        $data['students'] = $this->Student_model->get_all(['academic_year_id' => $this->academic_year_id, 'status' => 1], 50);
        $data['staff'] = $this->Staff_model->get_all();

        $this->render('pages/leave/request', $data);
    }

    // 6. Leave Approval Desk
    public function approval()
    {
        $this->require_permission('leave.approve');
        $filters = [
            'status' => $this->input->get('status') ?: 'Pending',
            'search' => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Leave Approval Desk';
        $data['filters'] = $filters;
        $data['applications'] = $this->Leave_model->get_applications($filters);

        $this->render('pages/leave/approval', $data);
    }

    // 7. Approval Action Handlers
    public function approve_action($id)
    {
        $this->require_permission('leave.approve');
        $approver_id = $this->session->userdata('user_id') ?: 1;
        $this->Leave_model->approve($id, $approver_id, 'Approved via approver desk');
        $this->session->set_flashdata('success', 'Leave request approved successfully.');
        redirect('leave/approval');
    }

    public function reject_action($id)
    {
        $this->require_permission('leave.approve');
        $approver_id = $this->session->userdata('user_id') ?: 1;
        $reason = trim($this->input->post('rejection_reason')) ?: 'Insufficient documentation or scheduling conflict.';
        $this->Leave_model->reject($id, $approver_id, $reason);
        $this->session->set_flashdata('success', 'Leave request rejected.');
        redirect('leave/approval');
    }

    public function clarification_action($id)
    {
        $this->require_permission('leave.approve');
        $approver_id = $this->session->userdata('user_id') ?: 1;
        $notes = trim($this->input->post('clarification_notes')) ?: 'Please submit medical prescription / event invitation.';
        $this->Leave_model->request_clarification($id, $approver_id, $notes);
        $this->session->set_flashdata('success', 'Clarification requested from applicant.');
        redirect('leave/approval');
    }

    public function cancel_action($id)
    {
        $this->require_permission('leave.apply');
        $user_id = $this->session->userdata('user_id') ?: 1;
        $this->Leave_model->cancel($id, $user_id, 'Staff', 'Cancelled by user');
        $this->session->set_flashdata('success', 'Leave application cancelled.');
        redirect('leave/approval');
    }

    // 8. Leave Balances Summary
    public function balances()
    {
        $this->require_permission('leave.view');
        $type = $this->input->get('type') ?: 'Staff';
        $data['title'] = 'Leave Balances Matrix';
        $data['type'] = $type;
        $data['balances'] = $this->Leave_balance_model->get_all_balances($this->academic_year_id, $type, 100);

        $this->render('pages/leave/balances', $data);
    }

    // 9. Leave Calendar
    public function calendar()
    {
        $this->require_permission('leave.view');
        $data['title'] = 'Leave Calendar';
        $data['leaves'] = $this->Leave_model->get_applications(['status' => 'Approved', 'academic_year_id' => $this->academic_year_id], 100);

        $this->render('pages/leave/calendar', $data);
    }

    // 10. Leave History
    public function history()
    {
        $this->require_permission('leave.view');
        $filters = [
            'status' => $this->input->get('status') ?: NULL,
            'search' => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Leave History & Audit Trail';
        $data['filters'] = $filters;
        $data['applications'] = $this->Leave_model->get_applications($filters, 100);

        $this->render('pages/leave/history', $data);
    }

    // 11. Leave Details View
    public function details($id)
    {
        $this->require_permission('leave.view');
        $app = $this->Leave_model->get_by_id($id);
        if (!$app) {
            $this->session->set_flashdata('error', 'Leave request not found.');
            redirect('leave/dashboard');
            return;
        }

        $data['title'] = 'Leave Request Details #' . $app->application_id;
        $data['app'] = $app;
        $data['history'] = $this->Leave_model->get_history($id);

        $this->render('pages/leave/details', $data);
    }

    // 12. Leave Reports
    public function reports()
    {
        $this->require_permission('leave.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $filters = [
            'academic_year_id' => $year_id,
            'applicant_type'   => $this->input->get('applicant_type') ?: NULL,
            'status'           => $this->input->get('status') ?: NULL,
            'class_id'         => $this->input->get('class_id') ?: NULL,
        ];

        $applications = $this->Leave_model->get_applications($filters, 500);

        // CSV Export
        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=leave_report_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['App ID', 'Type', 'Applicant Name', 'Class / Designation', 'Leave Type', 'From', 'To', 'Days', 'Status', 'Reason']);
            foreach ($applications as $a) {
                $name = ($a->applicant_type === 'Student') ? $a->first_name . ' ' . $a->last_name : $a->staff_name;
                $scope = ($a->applicant_type === 'Student') ? ($a->class_name . ' ' . $a->section_name) : ($a->designation_name ?: 'Staff');
                fputcsv($out, [$a->application_id, $a->applicant_type, $name, $scope, $a->type_name, $a->from_date, $a->to_date, $a->duration_days, $a->status, $a->reason]);
            }
            fclose($out);
            exit;
        }

        $data['title'] = 'Leave Analytics & Reports';
        $data['stats'] = $this->Leave_model->get_dashboard_stats($year_id);
        $data['filters'] = $filters;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['applications'] = $applications;

        $this->render('pages/leave/reports', $data);
    }

    // 13. Leave Settings
    public function settings()
    {
        $this->require_permission('leave.approve');
        if ($this->input->post()) {
            $postData = [
                'enable_student_leave'     => $this->input->post('enable_student_leave') ? 1 : 0,
                'enable_staff_leave'       => $this->input->post('enable_staff_leave') ? 1 : 0,
                'enable_half_day'          => $this->input->post('enable_half_day') ? 1 : 0,
                'working_days_only'        => $this->input->post('working_days_only') ? 1 : 0,
                'student_approval_workflow'=> trim($this->input->post('student_approval_workflow')),
                'staff_approval_workflow'  => trim($this->input->post('staff_approval_workflow')),
                'enable_balance_tracking'  => $this->input->post('enable_balance_tracking') ? 1 : 0,
                'allow_carry_forward'      => $this->input->post('allow_carry_forward') ? 1 : 0,
                'max_carry_forward_days'   => (int)$this->input->post('max_carry_forward_days'),
                'require_document_default' => $this->input->post('require_document_default') ? 1 : 0,
                'max_file_size_mb'         => (int)$this->input->post('max_file_size_mb'),
            ];

            $this->Leave_setting_model->update_settings($postData);
            $this->session->set_flashdata('success', 'Leave settings updated.');
            redirect('leave/settings');
            return;
        }

        $data['title'] = 'Leave Settings';
        $data['settings'] = $this->Leave_setting_model->get_settings();
        $data['audit_logs'] = $this->Leave_setting_model->get_audit_logs(25);

        $this->render('pages/leave/settings', $data);
    }
}
