<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academics extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Academic_year_model');
        $this->load->model('Academic_group_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Subject_model');
        $this->load->model('Staff_model');
        $this->load->model('Class_teacher_model');
        $this->load->model('Subject_teacher_model');
        $this->load->model('Period_model');
        $this->load->model('Timetable_model');
        $this->load->model('Academic_calendar_model');
        $this->load->model('Setting_model');
        $this->load->library('form_validation');
    }

    /* =========================================================================
       1. Academic Management Overview
       ========================================================================= */
    public function index()
    {
        $this->overview();
    }

    public function overview()
    {
        $this->require_permission('academics.view');
        $active_year = $this->Academic_year_model->get_active($this->school_id);
        $year_id     = $active_year ? (int)$active_year->academic_year_id : NULL;

        $can_view_academic_groups = $this->rbac->is_super_admin()
            || $this->rbac->has_permission('academic_groups.view')
            || $this->rbac->has_permission('academics.view')
            || $this->rbac->has_permission('academics.manage');

        $overview = $this->Class_model->get_overview_metrics($this->school_id);

        $this->render('pages/academics/overview', array_merge(array(
            'title'                    => 'Academic Management Overview',
            'page_key'                 => 'academics',
            'breadcrumb'               => array('Academic Management', 'Overview'),
            'active_year'              => $active_year,
            'can_view_academic_groups' => $can_view_academic_groups,
        ), $overview));
    }

    /* =========================================================================
       1. Academic Year Management
       ========================================================================= */
    public function years()
    {
        $this->require_permission(['academic_years.view', 'academics.view']);
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add') {
                $this->require_permission(['academic_years.create', 'academics.create', 'academics.manage']);
                $this->form_validation->set_rules('year_name', 'Year Name', 'required|trim');
                $this->form_validation->set_rules('start_date', 'Start Date', 'required');
                $this->form_validation->set_rules('end_date', 'End Date', 'required|callback_validate_academic_dates');

                if ($this->form_validation->run() === TRUE) {
                    $year_name  = trim(preg_replace('/\s+/', ' ', (string)$this->input->post('year_name')));
                    $start_date = $this->input->post('start_date');
                    $end_date   = $this->input->post('end_date');

                    // Strict date order validation: End Date must be greater than Start Date
                    if (strtotime($end_date) <= strtotime($start_date)) {
                        $this->session->set_flashdata('error', 'End Date must be greater than Start Date.');
                        redirect('academics/years');
                        return;
                    }

                    // Prevent duplicate academic year creation before INSERT
                    if ($this->Academic_year_model->is_year_name_exists($year_name)) {
                        $this->session->set_flashdata('error', "Academic year {$year_name} already exists.");
                        redirect('academics/years');
                        return;
                    }

                    $isActive = ($this->input->post('is_active') == '1') ? 1 : 0;
                    $insert_id = $this->Academic_year_model->insert(array(
                        'year_name'  => $year_name,
                        'start_date' => $start_date,
                        'end_date'   => $end_date,
                        'is_active'  => $isActive,
                        'status'     => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ));

                    if ($insert_id === FALSE) {
                        $this->session->set_flashdata('error', "Academic year {$year_name} already exists.");
                    } else {
                        $this->session->set_flashdata('success', 'Academic Year added successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                $this->require_permission(['academic_years.edit', 'academics.edit', 'academics.manage']);
                $id = (int)$this->input->post('academic_year_id');

                $this->form_validation->set_rules('academic_year_id', 'Academic Year ID', 'required|integer');
                $this->form_validation->set_rules('year_name', 'Year Name', 'required|trim');
                $this->form_validation->set_rules('start_date', 'Start Date', 'required');
                $this->form_validation->set_rules('end_date', 'End Date', 'required|callback_validate_academic_dates');

                if ($this->form_validation->run() === TRUE) {
                    $year_name  = trim(preg_replace('/\s+/', ' ', (string)$this->input->post('year_name')));
                    $start_date = $this->input->post('start_date');
                    $end_date   = $this->input->post('end_date');

                    // Strict date order validation: End Date must be greater than Start Date
                    if (strtotime($end_date) <= strtotime($start_date)) {
                        $this->session->set_flashdata('error', 'End Date must be greater than Start Date.');
                        redirect('academics/years');
                        return;
                    }

                    // Check duplicate for edit (excluding current record)
                    if ($this->Academic_year_model->is_year_name_exists($year_name, $id)) {
                        $this->session->set_flashdata('error', "Academic year {$year_name} already exists.");
                        redirect('academics/years');
                        return;
                    }

                    $isActive = ($this->input->post('is_active') == '1') ? 1 : 0;
                    $updated = $this->Academic_year_model->update($id, array(
                        'year_name'  => $year_name,
                        'start_date' => $start_date,
                        'end_date'   => $end_date,
                        'is_active'  => $isActive,
                        'updated_at' => date('Y-m-d H:i:s')
                    ));

                    if ($updated === FALSE) {
                        $this->session->set_flashdata('error', "Academic year {$year_name} already exists.");
                    } else {
                        $this->session->set_flashdata('success', 'Academic Year updated successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            }
            redirect('academics/years');
        }

        $years = $this->Academic_year_model->get_all();

        $this->render('pages/academics/years', array(
            'title'      => 'Academic Years',
            'page_key'   => 'academic-years',
            'breadcrumb' => array('Academic Management', 'Academic Year'),
            'years'      => $years,
        ));
    }

    public function switch_year()
    {
        $year_id = (int)($this->input->post('academic_year_id') ?: $this->input->get('academic_year_id'));

        if (!can_change_academic_year()) {
            log_message('error', 'Unauthorized attempt to change global active academic year by user ID: ' . ($this->current_user->user_id ?? 0));
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'          => false,
                        'message'         => 'You do not have permission to change the active academic year.',
                        'csrf_token_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash'       => $this->security->get_csrf_hash(),
                    ]));
                return;
            }
            $this->session->set_flashdata('error', 'You do not have permission to change the active academic year.');
            $redirect_url = $this->input->post('redirect_url') ?: ($this->input->server('HTTP_REFERER') ?: 'dashboard');
            redirect($redirect_url);
            return;
        }

        if ($year_id <= 0) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'          => false,
                        'message'         => 'Please provide a valid academic year ID.',
                        'csrf_token_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash'       => $this->security->get_csrf_hash(),
                    ]));
                return;
            }
            $this->session->set_flashdata('error', 'Please provide a valid academic year ID.');
            $redirect_url = $this->input->post('redirect_url') ?: ($this->input->server('HTTP_REFERER') ?: 'dashboard');
            redirect($redirect_url);
            return;
        }

        $year = $this->Academic_year_model->get_by_id($year_id, (int)$this->school_id);
        if (!$year || $year->is_deleted === 'y' || (int)$year->status !== 1 || (int)$year->school_id !== (int)$this->school_id) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'          => false,
                        'message'         => 'The selected academic year is invalid or does not belong to your school.',
                        'csrf_token_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash'       => $this->security->get_csrf_hash(),
                    ]));
                return;
            }
            $this->session->set_flashdata('error', 'Invalid academic year selected.');
            $redirect_url = $this->input->post('redirect_url') ?: ($this->input->server('HTTP_REFERER') ?: 'dashboard');
            redirect($redirect_url);
            return;
        }

        // Get currently active year prior to change for audit tracking
        $prev_active = $this->Academic_year_model->get_strictly_active_year((int)$this->school_id);
        $prev_name   = $prev_active ? $prev_active->year_name : 'None';
        $prev_id     = $prev_active ? (int)$prev_active->academic_year_id : 0;

        // Atomically set as globally active in the database for this school
        $activated = $this->Academic_year_model->set_active($year_id, (int)$this->school_id);
        if (!$activated) {
            log_message('error', "Academics::switch_year: Failed to set active academic year for ID {$year_id}");
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'          => false,
                        'message'         => 'Unable to change the active academic year. Please try again.',
                        'csrf_token_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash'       => $this->security->get_csrf_hash(),
                    ]));
                return;
            }
            $this->session->set_flashdata('error', 'Unable to change the active academic year. Please try again.');
            $redirect_url = $this->input->post('redirect_url') ?: ($this->input->server('HTTP_REFERER') ?: 'dashboard');
            redirect($redirect_url);
            return;
        }

        // Synchronize session and clear in-memory caches
        set_current_academic_year($year_id);
        clear_academic_year_cache();

        // Audit log the global change
        $user_id = (int)($this->current_user->user_id ?? 0) ?: 1;
        $this->rbac->log_audit(
            'CHANGED_ACTIVE_ACADEMIC_YEAR',
            'academic_year',
            $year_id,
            ['academic_year_id' => $prev_id, 'year_name' => $prev_name],
            ['academic_year_id' => (int)$year->academic_year_id, 'year_name' => $year->year_name],
            "Global active academic year changed from {$prev_name} to {$year->year_name}"
        );

        log_message('info', "Super Admin (ID: {$user_id}) changed global active academic year from '{$prev_name}' (ID: {$prev_id}) to '{$year->year_name}' (ID: {$year->academic_year_id}) at " . date('Y-m-d H:i:s'));

        $selected_date = get_academic_year_default_date($year);
        $success_message = 'Active academic year changed to ' . $year->year_name . '.';

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'             => true,
                    'message'            => $success_message,
                    'academic_year_id'   => (int)$year->academic_year_id,
                    'academic_year_name' => $year->year_name,
                    'year_name'          => $year->year_name,
                    'start_date'         => $year->start_date,
                    'end_date'           => $year->end_date,
                    'selected_date'      => $selected_date,
                    'csrf_token_name'    => $this->security->get_csrf_token_name(),
                    'csrf_hash'          => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        $this->session->set_flashdata('success', $success_message);
        $redirect_url = $this->input->post('redirect_url') ?: ($this->input->server('HTTP_REFERER') ?: 'dashboard');
        redirect($redirect_url);
    }

    public function set_active_year($id = NULL)
    {
        $this->require_permission(['academic_years.edit', 'academics.edit', 'academics.manage']);
        if (!empty($id)) {
            $this->Academic_year_model->set_active($id);
            set_current_academic_year((int)$id);
            $this->session->set_flashdata('success', 'Active academic session updated!');
        }
        redirect('academics/years');
    }

    public function delete_year($id = NULL)
    {
        $this->require_permission(['academic_years.delete', 'academics.delete', 'academics.manage']);
        $id = (int)$id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Invalid Academic Year selected.');
            redirect('academics/years');
            return;
        }

        $year = $this->Academic_year_model->get_by_id($id);
        if (!$year) {
            $this->session->set_flashdata('error', 'Academic Year not found.');
            redirect('academics/years');
            return;
        }

        // Active Academic Year must NOT be deleted
        if ((int)$year->is_active === 1) {
            $this->session->set_flashdata('error', 'Cannot delete the active academic year. Please set another academic year as active first.');
            redirect('academics/years');
            return;
        }

        // Check whether there are dependent records in any child tables
        $dependencies = $this->Academic_year_model->get_dependencies($id);
        if (!empty($dependencies)) {
            $details = [];
            foreach ($dependencies as $label => $count) {
                $details[] = "{$label}: {$count}";
            }
            $msg = "Cannot delete this academic year because it is being used by existing records (" . implode(', ', $details) . "). Please remove or reassign the dependent records first.";
            $this->session->set_flashdata('error', $msg);
            redirect('academics/years');
            return;
        }

        // Permanently delete from database when no dependencies exist
        $deleted = $this->Academic_year_model->permanent_delete($id);
        if ($deleted) {
            $this->session->set_flashdata('success', 'Academic Year permanently deleted.');
        } else {
            $this->session->set_flashdata('error', 'Cannot delete this academic year because it is being used by existing records. Please remove or reassign the dependent records first.');
        }
        redirect('academics/years');
    }

    /**
     * Form validation callback: Verify that End Date is strictly greater than Start Date.
     *
     * @param string $end_date
     * @return bool
     */
    public function validate_academic_dates($end_date)
    {
        $start_date = $this->input->post('start_date');
        if (!empty($start_date) && !empty($end_date)) {
            if (strtotime($end_date) <= strtotime($start_date)) {
                $this->form_validation->set_message('validate_academic_dates', 'End Date must be greater than Start Date.');
                return FALSE;
            }
        }
        return TRUE;
    }

    /* =========================================================================
       1B. Academic Groups Management (RBAC & School-Scoped)
       ========================================================================= */
    public function academic_groups()
    {
        $is_super_admin = $this->rbac->is_super_admin();
        $has_view = $is_super_admin || $this->rbac->has_permission('academic_groups.view') || $this->rbac->has_permission('academics.view') || $this->rbac->has_permission('academics.manage');

        if (!$has_view) {
            show_error('Access restricted. You do not have permission to view Academic Groups.', 403, '403 Forbidden');
            return;
        }

        $can_create = $is_super_admin || $this->rbac->has_permission('academic_groups.create') || $this->rbac->has_permission('academics.manage');
        $can_edit   = $is_super_admin || $this->rbac->has_permission('academic_groups.edit') || $this->rbac->has_permission('academics.manage');
        $can_delete = $is_super_admin || $this->rbac->has_permission('academic_groups.delete') || $this->rbac->has_permission('academics.manage');

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add') {
                if (!$can_create) {
                    show_error('Access restricted. You do not have permission to create Academic Groups.', 403, '403 Forbidden');
                    return;
                }

                $this->form_validation->set_rules('group_name', 'Group Name', 'required|trim');
                $this->form_validation->set_rules('attendance_type', 'Attendance Type', 'required|trim|in_list[daily,period]');
                if ($this->form_validation->run() === TRUE) {
                    $name = trim($this->input->post('group_name'));
                    if ($this->Academic_group_model->check_duplicate($name)) {
                        $this->session->set_flashdata('error', 'Department / Group "' . html_escape($name) . '" already exists.');
                    } else {
                        $classes_input = $this->input->post('classes', TRUE);
                        $status_val = $this->input->post('status') !== null ? (int)$this->input->post('status') : 1;
                        $attendance_type = strtolower(trim($this->input->post('attendance_type') ?: 'daily'));
                        if (!in_array($attendance_type, array('daily', 'period'))) {
                            $attendance_type = 'daily';
                        }
                        $this->Academic_group_model->insert(array(
                            'group_name'      => $name,
                            'description'     => $this->input->post('description', TRUE),
                            'display_order'   => (int)$this->input->post('display_order') ?: 0,
                            'status'          => $status_val,
                            'attendance_type' => $attendance_type
                        ), $classes_input);
                        $this->session->set_flashdata('success', 'Department / Group created successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                if (!$can_edit) {
                    show_error('Access restricted. You do not have permission to edit Department / Groups.', 403, '403 Forbidden');
                    return;
                }

                $id = (int)$this->input->post('academic_group_id');
                $group = $this->Academic_group_model->get_by_id($id);
                if (!$group) {
                    show_error('Department / Group not found or access denied.', 404, '404 Not Found');
                    return;
                }

                $this->form_validation->set_rules('group_name', 'Group Name', 'required|trim');
                $this->form_validation->set_rules('attendance_type', 'Attendance Type', 'required|trim|in_list[daily,period]');
                if ($this->form_validation->run() === TRUE) {
                    $name = trim($this->input->post('group_name'));
                    if ($this->Academic_group_model->check_duplicate($name, $id)) {
                        $this->session->set_flashdata('error', 'Department / Group "' . html_escape($name) . '" already exists.');
                    } else {
                        $classes_input = $this->input->post('classes', TRUE);
                        $status_val = $this->input->post('status') !== null ? (int)$this->input->post('status') : (int)$group->status;
                        $attendance_type = strtolower(trim($this->input->post('attendance_type') ?: ($group->attendance_type ?? 'daily')));
                        if (!in_array($attendance_type, array('daily', 'period'))) {
                            $attendance_type = 'daily';
                        }
                        $this->Academic_group_model->update($id, array(
                            'group_name'      => $name,
                            'description'     => $this->input->post('description', TRUE),
                            'display_order'   => (int)$this->input->post('display_order') ?: 0,
                            'status'          => $status_val,
                            'attendance_type' => $attendance_type,
                        ), $classes_input);
                        $this->session->set_flashdata('success', 'Department / Group updated successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            }
            redirect('academics/academic_groups');
            return;
        }

        $groups = $this->Academic_group_model->get_all(true);

        $this->render('pages/academics/academic_groups', array(
            'title'          => 'Department / Groups',
            'page_key'       => 'academic-groups',
            'breadcrumb'     => array('Academic Management', 'Department / Groups'),
            'groups'         => $groups,
            'is_super_admin' => $is_super_admin,
            'can_create'     => $can_create,
            'can_edit'       => $can_edit,
            'can_delete'     => $can_delete,
        ));
    }

    public function delete_academic_group($id = NULL)
    {
        $is_super_admin = $this->rbac->is_super_admin();
        $can_delete = $is_super_admin || $this->rbac->has_permission('academic_groups.delete') || $this->rbac->has_permission('academics.manage');
        if (!$can_delete) {
            show_error('Access restricted. You do not have permission to delete Department / Groups.', 403, '403 Forbidden');
            return;
        }

        $id = (int)$id;
        $group = $this->Academic_group_model->get_by_id($id);
        if (!$group) {
            show_error('Department / Group not found or access denied.', 404, '404 Not Found');
            return;
        }

        $this->Academic_group_model->soft_delete($id);
        $this->session->set_flashdata('success', 'Department / Group deactivated.');
        redirect('academics/academic_groups');
    }

    public function toggle_group_status($id = NULL, $status = 1)
    {
        $is_super_admin = $this->rbac->is_super_admin();
        $can_edit = $is_super_admin || $this->rbac->has_permission('academic_groups.edit') || $this->rbac->has_permission('academics.manage');
        if (!$can_edit) {
            show_error('Access restricted. You do not have permission to modify Department / Groups.', 403, '403 Forbidden');
            return;
        }

        $id = (int)$id;
        $group = $this->Academic_group_model->get_by_id($id);
        if (!$group) {
            show_error('Department / Group not found or access denied.', 404, '404 Not Found');
            return;
        }

        $this->Academic_group_model->set_status($id, (int)$status);
        $msg = (int)$status === 1 ? 'Department / Group enabled.' : 'Department / Group disabled.';
        $this->session->set_flashdata('success', $msg);
        redirect('academics/academic_groups');
    }

    /**
     * AJAX endpoint: Get standard allowed class options for an Academic Group from DB.
     */
    public function ajax_get_group_classes()
    {
        $is_super_admin = $this->rbac->is_super_admin();
        $has_view = $is_super_admin || $this->rbac->has_permission('academic_groups.view') || $this->rbac->has_permission('academics.view') || $this->rbac->has_permission('academics.manage');
        if (!$has_view) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => false, 'message' => 'Forbidden']));
        }

        $group_id = (int)$this->input->get_post('academic_group_id');
        $classes = $group_id ? $this->Academic_group_model->get_allowed_classes($group_id) : [];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'classes'         => $classes,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint: Get active classes configured under an Academic Group.
     */
    public function ajax_get_classes_by_group()
    {
        $this->require_permission(['classes.view', 'academic_groups.view', 'academics.view']);
        $group_id = (int)$this->input->get_post('academic_group_id');
        $year_id  = $this->input->get_post('academic_year_id');
        $classes  = $group_id ? $this->Class_model->get_by_group($group_id, $year_id) : $this->Class_model->get_all($year_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'classes'         => $classes,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint: Get teaching faculty belonging to an Academic Group.
     */
    public function ajax_get_teachers_by_group($group_id = null)
    {
        $this->require_permission(['classes.view', 'academic_groups.view', 'academics.view', 'staff.view']);
        $gid = $group_id ?: $this->input->get_post('academic_group_id') ?: $this->input->get_post('group_id');
        $teachers = $gid ? $this->Staff_model->get_teachers_by_group((int)$gid, $this->school_id) : [];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'           => true,
                'status'            => true,
                'academic_group_id' => $gid ? (int)$gid : null,
                'teachers'          => $teachers,
                'csrf_token_name'   => $this->security->get_csrf_token_name(),
                'csrf_hash'         => $this->security->get_csrf_hash()
            ]));
    }

    /* =========================================================================
       2. Classes Management (Unified Classes & Divisions Workflow)
       ========================================================================= */
    public function classes()
    {
        $this->require_permission(['classes.view', 'academics.view']);
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add') {
                $this->require_permission(['classes.create', 'academics.create', 'academics.manage']);
                $this->form_validation->set_rules('class_name', 'Class Name', 'required|trim');
                $this->form_validation->set_rules('academic_year_id', 'Academic Year', 'required|numeric');
                $this->form_validation->set_rules('academic_group_id', 'Academic Group', 'required|numeric');
                $this->form_validation->set_rules('capacity', 'Capacity', 'numeric');

                if ($this->form_validation->run() === TRUE) {
                    $academic_year_id  = (int)$this->input->post('academic_year_id');
                    $academic_group_id = (int)$this->input->post('academic_group_id');
                    $class_name        = trim($this->input->post('class_name'));
                    $capacity          = $this->input->post('capacity') ? intval($this->input->post('capacity')) : 40;
                    $class_code        = trim($this->input->post('class_code'));
                    if ($class_code === '') {
                        $class_code = 'CLS-' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $class_name));
                    }

                    // Duplicate class check for the academic year
                    if ($this->Class_model->check_duplicate($class_name, $academic_year_id)) {
                        $this->session->set_flashdata('error', 'Class "' . html_escape($class_name) . '" already exists in the selected Academic Year.');
                        redirect('academics/classes');
                        return;
                    }

                    // Parse submitted divisions & class teachers
                    $raw_divisions = $this->input->post('division_names');
                    $raw_teachers  = $this->input->post('division_teacher_ids');
                    $clean_divisions = [];
                    $clean_teachers  = [];
                    if (is_array($raw_divisions)) {
                        for ($i = 0; $i < count($raw_divisions); $i++) {
                            $tname = trim((string)$raw_divisions[$i]);
                            if ($tname !== '') {
                                $clean_divisions[] = $tname;
                                $t_id = isset($raw_teachers[$i]) ? (int)$raw_teachers[$i] : 0;
                                $clean_teachers[]  = $t_id > 0 ? $t_id : null;
                            }
                        }
                    }
                    if (empty($clean_divisions)) {
                        $clean_divisions = ['A'];
                        $clean_teachers  = [null];
                    }

                    // Case-insensitive duplicate division check
                    $upper_divisions = array_map('strtoupper', $clean_divisions);
                    if (count($upper_divisions) !== count(array_unique($upper_divisions))) {
                        $this->session->set_flashdata('error', 'Duplicate division names detected. Each division within a class must have a unique name.');
                        redirect('academics/classes');
                        return;
                    }

                    $academic_year = $this->Academic_year_model->get_by_id($academic_year_id);
                    $year_name = $academic_year ? $academic_year->year_name : 'the selected academic year';

                    // Validate selected teacher IDs
                    $seen_teachers = [];
                    for ($i = 0; $i < count($clean_teachers); $i++) {
                        $tid = $clean_teachers[$i];
                        if ($tid) {
                            // Validate teaching faculty & Department / Group integrity
                            $teacher_check = $this->Staff_model->validate_teacher_group($tid, $academic_group_id, $this->school_id);
                            if (!$teacher_check['valid']) {
                                $this->session->set_flashdata('error', $teacher_check['error']);
                                redirect('academics/classes');
                                return;
                            }
                            $stf = $this->Staff_model->get_by_id($tid, $this->school_id);

                            // 1. Intra-modal duplicate check (same teacher selected for multiple divisions in same class)
                            if (in_array($tid, $seen_teachers)) {
                                $this->session->set_flashdata('error', "{$stf->full_name} can be assigned as Class Teacher to only one division for the academic year {$year_name}.");
                                redirect('academics/classes');
                                return;
                            }
                            $seen_teachers[] = $tid;

                            // 2. Cross-class duplicate check (already assigned elsewhere in same academic year)
                            $conflict = $this->Class_teacher_model->check_teacher_availability($academic_year_id, $tid);
                            if ($conflict) {
                                $cName = $conflict->class_name ?: 'another class';
                                $dName = $conflict->division_name ?: 'division';
                                $this->session->set_flashdata('error', "{$stf->full_name} is already assigned as Class Teacher to {$cName} - Division {$dName} for the academic year {$year_name}.");
                                redirect('academics/classes');
                                return;
                            }
                        }
                    }

                    // Database Transaction for atomic Class + Divisions + Teacher assignments creation
                    $this->db->trans_begin();
                    try {
                        $class_id = $this->Class_model->insert(array(
                            'academic_year_id'  => $academic_year_id,
                            'academic_group_id' => $academic_group_id,
                            'class_name'        => $class_name,
                            'class_code'        => $class_code,
                            'capacity'          => $capacity,
                            'description'       => trim((string)$this->input->post('description')),
                            'status'            => 1,
                            'is_deleted'        => 'n',
                            'created_at'        => date('Y-m-d H:i:s')
                        ));

                        if (!$class_id) {
                            throw new Exception('Failed to create class record.');
                        }

                        $assigned_teacher_count = 0;
                        for ($i = 0; $i < count($clean_divisions); $i++) {
                            $div_name = $clean_divisions[$i];
                            $tid      = $clean_teachers[$i] ?? null;

                            $div_id = $this->Division_model->insert(array(
                                'class_id'         => $class_id,
                                'division_name'    => $div_name,
                                'class_teacher_id' => $tid ?: NULL,
                                'capacity'         => $capacity,
                                'status'           => 1,
                                'is_deleted'       => 'n',
                                'created_at'       => date('Y-m-d H:i:s')
                            ));
                            if (!$div_id) {
                                throw new Exception('Failed to create division "' . $div_name . '".');
                            }

                            if (!empty($tid)) {
                                $res = $this->Class_teacher_model->assign($academic_year_id, $class_id, $div_id, $tid);
                                if (!$res) {
                                    $err = $this->Class_teacher_model->get_last_error();
                                    throw new Exception($err ?: 'Failed to assign class teacher for division "' . $div_name . '".');
                                }
                                $assigned_teacher_count++;
                            }
                        }

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->session->set_flashdata('error', 'Database error occurred while creating class and divisions.');
                        } else {
                            $this->db->trans_commit();
                            if ($assigned_teacher_count === count($clean_divisions)) {
                                $this->session->set_flashdata('success', 'Class and divisions created successfully. Class teachers have been assigned where selected.');
                            } elseif ($assigned_teacher_count > 0) {
                                $this->session->set_flashdata('success', 'Class and divisions created successfully. Some divisions do not have a class teacher assigned; you can assign them later from Teacher Allocation.');
                            } else {
                                $this->session->set_flashdata('success', 'Class and divisions created successfully.');
                            }
                        }
                    } catch (Exception $e) {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('error', $e->getMessage());
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                $this->require_permission(['classes.edit', 'academics.edit', 'academics.manage']);
                $class_id = (int)$this->input->post('class_id');
                $this->form_validation->set_rules('class_id', 'Class ID', 'required|numeric');
                $this->form_validation->set_rules('class_name', 'Class Name', 'required|trim');
                $this->form_validation->set_rules('academic_year_id', 'Academic Year', 'required|numeric');
                $this->form_validation->set_rules('academic_group_id', 'Academic Group', 'required|numeric');
                $this->form_validation->set_rules('capacity', 'Capacity', 'numeric');

                if ($this->form_validation->run() === TRUE) {
                    $academic_year_id  = (int)$this->input->post('academic_year_id');
                    $academic_group_id = (int)$this->input->post('academic_group_id');
                    $class_name        = trim($this->input->post('class_name'));
                    $capacity          = $this->input->post('capacity') ? intval($this->input->post('capacity')) : 40;
                    $class_code        = trim($this->input->post('class_code'));
                    if ($class_code === '') {
                        $class_code = 'CLS-' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $class_name));
                    }

                    // Duplicate class check excluding this class
                    if ($this->Class_model->check_duplicate($class_name, $academic_year_id, $class_id)) {
                        $this->session->set_flashdata('error', 'Class "' . html_escape($class_name) . '" already exists in the selected Academic Year.');
                        redirect('academics/classes');
                        return;
                    }

                    $div_ids      = $this->input->post('division_ids') ?: array();
                    $div_names    = $this->input->post('division_names') ?: array();
                    $div_teachers = $this->input->post('division_teacher_ids') ?: array();

                    // Check duplicate division names among submitted active rows
                    $active_names = array();
                    for ($i = 0; $i < count($div_names); $i++) {
                        $name = trim((string)$div_names[$i]);
                        if ($name !== '') {
                            $active_names[] = strtoupper($name);
                        }
                    }
                    if (empty($active_names)) {
                        $this->session->set_flashdata('error', 'A class must have at least one division.');
                        redirect('academics/classes');
                        return;
                    }
                    if (count($active_names) !== count(array_unique($active_names))) {
                        $this->session->set_flashdata('error', 'Duplicate division names detected. Each division within a class must have a unique name.');
                        redirect('academics/classes');
                        return;
                    }

                    $academic_year = $this->Academic_year_model->get_by_id($academic_year_id);
                    $year_name = $academic_year ? $academic_year->year_name : 'the selected academic year';

                    // Validate any selected teacher IDs
                    $seen_teachers = [];
                    for ($i = 0; $i < count($div_names); $i++) {
                        $d_id   = isset($div_ids[$i]) ? trim((string)$div_ids[$i]) : '';
                        $d_name = trim((string)$div_names[$i]);
                        $tid    = isset($div_teachers[$i]) ? (int)$div_teachers[$i] : 0;
                        if ($d_name === '' || $tid <= 0) continue;

                        // Validate teaching faculty & Department / Group integrity
                        $teacher_check = $this->Staff_model->validate_teacher_group($tid, $academic_group_id, $this->school_id);
                        if (!$teacher_check['valid']) {
                            $this->session->set_flashdata('error', $teacher_check['error']);
                            redirect('academics/classes');
                            return;
                        }
                        $stf = $this->Staff_model->get_by_id($tid, $this->school_id);

                        // 1. Intra-modal duplicate check (same teacher selected for multiple divisions in same class)
                        if (in_array($tid, $seen_teachers)) {
                            $this->session->set_flashdata('error', "{$stf->full_name} can be assigned as Class Teacher to only one division for the academic year {$year_name}.");
                            redirect('academics/classes');
                            return;
                        }
                        $seen_teachers[] = $tid;

                        // 2. Cross-class duplicate check (assigned to another class in same academic year)
                        $conflict = $this->Class_teacher_model->check_teacher_availability($academic_year_id, $tid);
                        if ($conflict && (int)$conflict->class_id !== $class_id) {
                            $cName = $conflict->class_name ?: 'another class';
                            $dName = $conflict->division_name ?: 'division';
                            $this->session->set_flashdata('error', "{$stf->full_name} is already assigned as Class Teacher to {$cName} - Division {$dName} for the academic year {$year_name}.");
                            redirect('academics/classes');
                            return;
                        }
                    }

                    // Database Transaction for atomic Class + Divisions edit
                    $this->db->trans_begin();
                    try {
                        // Reset existing active teacher allocations for this class in this academic year
                        // so divisions within the class can be cleanly reassigned or updated
                        $this->Class_model->reset_class_teacher_allocations($class_id, $academic_year_id, $this->school_id);

                        // 1. Handle deleted divisions safely
                        $deleted_ids = $this->input->post('deleted_division_ids');
                        if (!empty($deleted_ids)) {
                            if (!is_array($deleted_ids)) {
                                $deleted_ids = array_filter(array_map('trim', explode(',', $deleted_ids)));
                            }
                            foreach ($deleted_ids as $del_id) {
                                $del_id = (int)$del_id;
                                if ($del_id > 0) {
                                    $div_record = $this->Division_model->get_by_id($del_id);
                                    if ($div_record && (int)$div_record->class_id === $class_id) {
                                        $deps = $this->Division_model->has_dependencies($del_id);
                                        if ($deps) {
                                            $dep_msgs = array();
                                            foreach ($deps as $type => $cnt) {
                                                $dep_msgs[] = "$cnt $type";
                                            }
                                            throw new Exception('Division "' . $div_record->division_name . '" cannot be deleted because it contains related records (' . implode(', ', $dep_msgs) . ').');
                                        }
                                        $this->Division_model->delete($del_id, $this->school_id);
                                    }
                                }
                            }
                        }

                        // 2. Handle updating existing or adding new divisions and teacher assignments
                        for ($i = 0; $i < count($div_names); $i++) {
                            $d_id   = isset($div_ids[$i]) ? trim((string)$div_ids[$i]) : '';
                            $d_name = trim((string)$div_names[$i]);
                            $d_tid  = isset($div_teachers[$i]) ? (int)$div_teachers[$i] : 0;
                            if ($d_name === '') continue;

                            if (is_numeric($d_id) && (int)$d_id > 0) {
                                $existing_div_id = (int)$d_id;
                                $existing_div = $this->Division_model->get_by_id($existing_div_id);
                                if ($existing_div && (int)$existing_div->class_id === $class_id) {
                                    $this->Division_model->update($existing_div_id, array(
                                        'division_name'    => $d_name,
                                        'class_teacher_id' => $d_tid > 0 ? $d_tid : NULL,
                                        'capacity'         => $capacity,
                                        'updated_at'       => date('Y-m-d H:i:s')
                                    ));

                                    if ($d_tid > 0) {
                                        $res = $this->Class_teacher_model->assign($academic_year_id, $class_id, $existing_div_id, $d_tid);
                                        if (!$res) {
                                            $err = $this->Class_teacher_model->get_last_error();
                                            throw new Exception($err ?: 'Failed to assign class teacher for division "' . $d_name . '".');
                                        }
                                    }
                                }
                            } else {
                                // New division added during edit
                                $new_div_id = $this->Division_model->insert(array(
                                    'class_id'         => $class_id,
                                    'division_name'    => $d_name,
                                    'class_teacher_id' => $d_tid > 0 ? $d_tid : NULL,
                                    'capacity'         => $capacity,
                                    'status'           => 1,
                                    'is_deleted'       => 'n',
                                    'created_at'       => date('Y-m-d H:i:s')
                                ));
                                if (!$new_div_id) {
                                    throw new Exception('Failed to add new division "' . $d_name . '".');
                                }

                                if ($d_tid > 0) {
                                    $res = $this->Class_teacher_model->assign($academic_year_id, $class_id, $new_div_id, $d_tid);
                                    if (!$res) {
                                        $err = $this->Class_teacher_model->get_last_error();
                                        throw new Exception($err ?: 'Failed to assign class teacher for division "' . $d_name . '".');
                                    }
                                }
                            }
                        }

                        // 3. Update class record
                        $this->Class_model->update($class_id, array(
                            'academic_year_id'  => $academic_year_id,
                            'academic_group_id' => $academic_group_id,
                            'class_name'        => $class_name,
                            'class_code'        => $class_code,
                            'capacity'          => $capacity,
                            'description'       => trim((string)$this->input->post('description')),
                            'updated_at'        => date('Y-m-d H:i:s')
                        ));

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->session->set_flashdata('error', 'Database error occurred while updating class and divisions.');
                        } else {
                            $this->db->trans_commit();
                            $this->session->set_flashdata('success', 'Class, divisions, and teacher assignments updated successfully.');
                        }
                    } catch (Exception $e) {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('error', $e->getMessage());
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            }
            redirect('academics/classes');
        }

        $year_id  = $this->input->get('year_id');
        $classes  = $this->Class_model->get_all_with_divisions($year_id);
        $years    = $this->Academic_year_model->get_all();
        $groups   = $this->Academic_group_model->get_all();
        $teachers = $this->Staff_model->get_teachers();

        $active_year = $this->Academic_year_model->get_active_year();
        $active_year_id = $active_year ? (int)$active_year->academic_year_id : 1;
        $active_assignments = $this->Class_teacher_model->get_active_assignments_by_year($active_year_id);

        $this->render('pages/academics/classes', array(
            'title'              => 'Classes & Divisions',
            'page_key'           => 'classes',
            'breadcrumb'         => array('Academic Management', 'Classes & Divisions'),
            'classes'            => $classes,
            'years'              => $years,
            'groups'             => $groups,
            'teachers'           => $teachers,
            'active_year_id'     => $active_year_id,
            'active_assignments' => $active_assignments,
        ));
    }

    public function delete_class($id = NULL)
    {
        $this->require_permission(['classes.delete', 'academics.delete', 'academics.manage']);
        if (!empty($id)) {
            $deps = $this->Class_model->has_dependencies($id);
            if ($deps) {
                $dep_msgs = array();
                foreach ($deps as $type => $cnt) {
                    $dep_msgs[] = "$cnt $type";
                }
                $this->session->set_flashdata('error', 'This class cannot be deactivated because it contains related records (' . implode(', ', $dep_msgs) . ').');
            } else {
                $success = $this->Class_model->soft_delete_with_divisions($id, $this->school_id);
                $this->session->set_flashdata(
                    $success ? 'success' : 'error',
                    $success ? 'Class record deactivated successfully.' : 'Failed to deactivate class. Please try again.'
                );
            }
        }
        redirect('academics/classes');
    }

    /* =========================================================================
       3. Divisions Management (Maintained for Backward Compatibility)
       ========================================================================= */
    public function divisions()
    {
        $this->require_permission(['classes.view', 'academics.view']);
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add') {
                $this->require_permission(['classes.create', 'academics.create', 'academics.manage']);
                $this->form_validation->set_rules('class_id', 'Class', 'required');
                $name_field = $this->input->post('division_name') !== NULL ? 'division_name' : 'section_name';
                $this->form_validation->set_rules($name_field, 'Division Name', 'required|trim');

                if ($this->form_validation->run() === TRUE) {
                    $class_id = $this->input->post('class_id');
                    $division_name = trim($this->input->post($name_field));

                    if ($this->Division_model->check_duplicate($class_id, $division_name)) {
                        $this->session->set_flashdata('error', 'Division "' . $division_name . '" already exists in the selected class.');
                    } else {
                        $this->Division_model->insert(array(
                            'class_id'      => $class_id,
                            'division_name' => $division_name,
                            'room_no'       => $this->input->post('room_no'),
                            'capacity'      => $this->input->post('capacity') ? intval($this->input->post('capacity')) : 40,
                            'description'   => $this->input->post('description'),
                            'status'        => 1,
                            'created_at'    => date('Y-m-d H:i:s')
                        ));
                        $this->session->set_flashdata('success', 'Division created successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                $this->require_permission(['classes.edit', 'academics.edit', 'academics.manage']);
                $id = $this->input->post('division_id') ?: $this->input->post('section_id');
                $class_id = $this->input->post('class_id');
                $name_field = $this->input->post('division_name') !== NULL ? 'division_name' : 'section_name';
                $division_name = trim($this->input->post($name_field));

                if ($this->Division_model->check_duplicate($class_id, $division_name, $id)) {
                    $this->session->set_flashdata('error', 'Division "' . $division_name . '" already exists in this class.');
                } else {
                    $this->Division_model->update($id, array(
                        'class_id'      => $class_id,
                        'division_name' => $division_name,
                        'room_no'       => $this->input->post('room_no'),
                        'capacity'      => $this->input->post('capacity') ? intval($this->input->post('capacity')) : 40,
                        'description'   => $this->input->post('description'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ));
                    $this->session->set_flashdata('success', 'Division updated successfully!');
                }
            }
            redirect('academics/divisions');
        }

        $class_id  = $this->input->get('class_id');
        $divisions = $this->Division_model->get_all($class_id);
        $classes   = $this->Class_model->get_all();

        $hierarchy = $this->Division_model->get_hierarchy(null, $class_id);
        $groups    = $this->Academic_group_model->get_all();
        $this->render('pages/academics/divisions', array(
            'title'      => 'Divisions',
            'page_key'   => 'divisions',
            'breadcrumb' => array('Academic Management', 'Divisions'),
            'divisions'  => $divisions,
            'sections'   => $divisions,
            'classes'    => $classes,
            'hierarchy'  => $hierarchy,
            'groups'     => $groups,
        ));
    }

    public function sections()
    {
        $this->divisions();
    }

    public function delete_division($id = NULL)
    {
        $this->require_permission(['classes.delete', 'academics.delete', 'academics.manage']);
        if (!empty($id)) {
            $deps = $this->Division_model->has_dependencies($id);
            if ($deps) {
                $dep_msgs = array();
                foreach ($deps as $type => $cnt) {
                    $dep_msgs[] = "$cnt $type";
                }
                $this->session->set_flashdata('error', 'This division cannot be deactivated because it contains related records (' . implode(', ', $dep_msgs) . ').');
            } else {
                $this->Division_model->soft_delete($id);
                $this->session->set_flashdata('success', 'Division deactivated successfully.');
            }
        }
        redirect('academics/classes');
    }


    public function delete_section($id = NULL)
    {
        $this->delete_division($id);
    }

    /**
     * AJAX endpoint: calculate next division letter for a class (starts from 'B')
     */
    public function get_next_division_ajax()
    {
        $this->require_permission(['classes.view', 'academics.view']);
        $class_id = (int)$this->input->get_post('class_id');
        $next_name = 'B';
        if ($class_id > 0) {
            $next_name = $this->Division_model->get_next_division_name($class_id);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'class_id'        => $class_id,
                'next_division'   => $next_name,
                'next_section'    => $next_name,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    public function get_next_section_ajax()
    {
        return $this->get_next_division_ajax();
    }

    /* =========================================================================
       4. Subjects Management
       ========================================================================= */
    public function subjects()
    {
        $this->require_permission(['subjects.view', 'academics.view']);
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');

            // Resolve the active academic year for assignment saving for this school
            $active_year_obj = $this->Academic_year_model->get_active_year($this->school_id);
            $save_year_id    = $active_year_obj ? (int)$active_year_obj->academic_year_id : 0;

            if ($action === 'add') {
                $this->require_permission(['subjects.create', 'academics.create', 'academics.manage']);
                $this->form_validation->set_rules('subject_name', 'Subject Name', 'required|trim');
                if ($this->form_validation->run() === TRUE) {
                    $class_id = $this->input->post('class_id') ?: NULL;
                    if ($class_id) {
                        $cls = $this->Class_model->get_by_id($class_id, $this->school_id);
                        if (!$cls) {
                            $this->session->set_flashdata('error', 'Selected class does not belong to the current school.');
                            redirect('academics/subjects');
                            return;
                        }
                    }

                    $this->db->trans_start();

                    $subject_id = $this->Subject_model->insert(array(
                        'school_id'    => (int)$this->school_id,
                        'class_id'     => $class_id,
                        'subject_name' => $this->input->post('subject_name'),
                        'subject_code' => $this->input->post('subject_code') ?: strtoupper(substr($this->input->post('subject_name'), 0, 4)),
                        'subject_type' => $this->input->post('subject_type') ?: 'Core',
                        'description'  => $this->input->post('description'),
                        'status'       => 1,
                        'is_deleted'   => 'n',
                        'created_at'   => date('Y-m-d H:i:s')
                    ), $this->school_id);

                    // Save inline teacher assignments
                    $assignments_json = $this->input->post('teacher_assignments');
                    $assigned_teachers = array();
                    if ($subject_id && $save_year_id) {
                        $assignments = !empty($assignments_json) ? json_decode($assignments_json, true) : array();
                        if (is_array($assignments)) {
                            $assigned_teachers = $this->Subject_teacher_model->sync_subject_assignments($subject_id, $save_year_id, $assignments, $this->school_id);
                        }
                    }
                    $primary_teacher_id = !empty($assigned_teachers) ? (int)$assigned_teachers[0] : NULL;
                    $this->Subject_model->update($subject_id, array('teacher_id' => $primary_teacher_id), $this->school_id);

                    $this->db->trans_complete();
                    if ($this->db->trans_status() === FALSE) {
                        $this->session->set_flashdata('error', 'Failed to save subject. Please try again.');
                    } else {
                        $this->session->set_flashdata('success', 'Subject created successfully!');
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                $this->require_permission(['subjects.edit', 'academics.edit', 'academics.manage']);
                $id = (int)$this->input->post('subject_id');

                $existing_subject = $this->Subject_model->get_by_id($id, $this->school_id);
                if (!$existing_subject) {
                    $this->session->set_flashdata('error', 'Subject not found or does not belong to the current school.');
                    redirect('academics/subjects');
                    return;
                }

                $class_id = $this->input->post('class_id') ?: NULL;
                if ($class_id) {
                    $cls = $this->Class_model->get_by_id($class_id, $this->school_id);
                    if (!$cls) {
                        $this->session->set_flashdata('error', 'Selected class does not belong to the current school.');
                        redirect('academics/subjects');
                        return;
                    }
                }

                $this->db->trans_start();

                $this->Subject_model->update($id, array(
                    'class_id'     => $class_id,
                    'subject_name' => $this->input->post('subject_name'),
                    'subject_code' => $this->input->post('subject_code'),
                    'subject_type' => $this->input->post('subject_type') ?: 'Core',
                    'description'  => $this->input->post('description'),
                    'updated_at'   => date('Y-m-d H:i:s')
                ), $this->school_id);

                // Process inline teacher assignments submitted with the form
                $assignments_json = $this->input->post('teacher_assignments');
                $assigned_teachers = array();
                if ($id && $save_year_id) {
                    $assignments = !empty($assignments_json) ? json_decode($assignments_json, true) : array();
                    if (is_array($assignments)) {
                        $assigned_teachers = $this->Subject_teacher_model->sync_subject_assignments($id, $save_year_id, $assignments, $this->school_id);
                    }
                }
                $primary_teacher_id = !empty($assigned_teachers) ? (int)$assigned_teachers[0] : NULL;
                $this->Subject_model->update($id, array('teacher_id' => $primary_teacher_id), $this->school_id);

                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    $this->session->set_flashdata('error', 'Failed to update subject. Please try again.');
                } else {
                    $this->session->set_flashdata('success', 'Subject updated successfully!');
                }
            }
            redirect('academics/subjects');
        }

        $classes        = $this->Class_model->get_all('all', $this->school_id);
        $teachers       = $this->Staff_model->get_teachers(array('school_id' => $this->school_id, 'status' => 1));
        $active_year    = $this->Academic_year_model->get_active_year($this->school_id);
        $active_year_id = $active_year ? (int)$active_year->academic_year_id : 0;
        $total_subjects = $this->Subject_model->count_all($this->school_id);

        $this->render('pages/academics/subjects', array(
            'title'          => 'Subjects',
            'page_key'       => 'subjects',
            'breadcrumb'     => array('Academic Management', 'Subjects'),
            'total_subjects' => $total_subjects,
            'classes'        => $classes,
            'teachers'       => $teachers,
            'active_year'    => $active_year,
            'active_year_id' => $active_year_id,
        ));
    }

    /**
     * AJAX endpoint: Server-side DataTables list for Subjects
     */
    public function ajax_subjects_list()
    {
        $this->require_permission(['subjects.view', 'academics.view']);

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length < 1 || $length > 100) {
            $length = 25;
        }

        $search = $this->input->post('search');
        $search_val = !empty($search['value']) ? trim($search['value']) : '';

        $order = $this->input->post('order');
        $order_col = !empty($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir = !empty($order[0]['dir']) ? $order[0]['dir'] : 'asc';

        $class_id = $this->input->post('class_id');

        $filters = array(
            'school_id' => (int)$this->school_id,
            'search'    => $search_val,
        );
        if (!empty($class_id)) {
            $filters['class_id'] = (int)$class_id;
        }

        $records_total    = $this->Subject_model->count_all($this->school_id);
        $records_filtered = $this->Subject_model->count_filtered($filters);
        $subjects         = $this->Subject_model->get_datatables_data($filters, $length, $start, $order_col, $order_dir);

        $active_year    = $this->Academic_year_model->get_active_year($this->school_id);
        $active_year_id = $active_year ? (int)$active_year->academic_year_id : 0;

        $subject_teachers_map = array();
        if (!empty($subjects) && $active_year_id) {
            $subject_ids = array_map(function($s) { return (int)$s->subject_id; }, $subjects);
            $rows = $this->db
                ->select('st.subject_id, s.staff_id, s.full_name')
                ->from('tbl_subject_teachers st')
                ->join('tbl_staff s', 's.staff_id = st.staff_id AND s.school_id = ' . (int)$this->school_id, 'inner')
                ->where_in('st.subject_id', $subject_ids)
                ->where('st.academic_year_id', (int)$active_year_id)
                ->where('st.school_id', (int)$this->school_id)
                ->where('st.status', 1)
                ->where('st.is_deleted', 'n')
                ->where('s.status', 1)
                ->where('s.is_deleted', 'n')
                ->get()
                ->result();

            foreach ($rows as $r) {
                $sub_id = (int)$r->subject_id;
                if (!isset($subject_teachers_map[$sub_id])) {
                    $subject_teachers_map[$sub_id] = array();
                }
                if (!in_array($r->full_name, $subject_teachers_map[$sub_id])) {
                    $subject_teachers_map[$sub_id][] = $r->full_name;
                }
            }
        }

        // Check tbl_subjects.teacher_id fallback for older records
        foreach ($subjects as $sub) {
            $sub_id = (int)$sub->subject_id;
            if (empty($subject_teachers_map[$sub_id]) && !empty($sub->teacher_id)) {
                $st = $this->db
                    ->select('staff_id, full_name')
                    ->where('staff_id', (int)$sub->teacher_id)
                    ->where('school_id', (int)$this->school_id)
                    ->where('status', 1)
                    ->where('is_deleted', 'n')
                    ->get('tbl_staff')
                    ->row();
                if ($st) {
                    $subject_teachers_map[$sub_id][] = $st->full_name;
                }
            }
        }

        $can_edit = $this->rbac->is_super_admin() || $this->rbac->has_permission('subjects.edit') || $this->rbac->has_permission('academics.edit') || $this->rbac->has_permission('academics.manage');
        $can_delete = $this->rbac->is_super_admin() || $this->rbac->has_permission('subjects.delete') || $this->rbac->has_permission('academics.delete') || $this->rbac->has_permission('academics.manage');

        $data = array();
        foreach ($subjects as $sub) {
            $badge = 'bg-surface-container-high text-on-surface';
            if ($sub->subject_type === 'Core') $badge = 'bg-primary-fixed/30 text-primary font-semibold';
            if ($sub->subject_type === 'Language') $badge = 'bg-secondary-container text-on-secondary-container font-semibold';
            if ($sub->subject_type === 'Elective') $badge = 'bg-purple-100 text-purple-800 font-semibold';
            if ($sub->subject_type === 'Practical') $badge = 'bg-amber-100 text-amber-900 font-semibold';
            if ($sub->subject_type === 'Other') $badge = 'bg-surface-container-highest text-on-surface-variant font-semibold';

            $nameCol = '<div class="flex items-center gap-2">' .
                       '<span class="material-symbols-outlined text-primary text-[20px]">menu_book</span>' .
                       '<div><div>' . html_escape($sub->subject_name) . '</div>' .
                       (!empty($sub->description) ? '<div class="text-[11px] text-on-surface-variant font-normal">' . html_escape($sub->description) . '</div>' : '') .
                       '</div></div>';

            $codeCol = '<span class="font-mono text-primary font-bold">' . html_escape($sub->subject_code) . '</span>';
            $classCol = html_escape($sub->class_name ?: 'General / All Classes');
            $typeCol = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] ' . $badge . '">' . html_escape($sub->subject_type) . '</span>';

            $teacher_names = $subject_teachers_map[$sub->subject_id] ?? array();
            $subNameJs = html_escape(addslashes($sub->subject_name));
            $subCodeJs = html_escape(addslashes($sub->subject_code));
            $subTypeJs = $sub->subject_type;
            $classIdJs = $sub->class_id ?: 'null';
            $subDescJs = html_escape(addslashes($sub->description ?: ''));

            if (!empty($teacher_names)) {
                $names_html = '';
                foreach ($teacher_names as $tname) {
                    $names_html .= '<div class="text-[13px] font-medium text-on-surface leading-snug">' . html_escape($tname) . '</div>';
                }
                $teachersCol = '<button type="button" onclick="openEditSubjectModal(' . $sub->subject_id . ', \'' . $subNameJs . '\', \'' . $subCodeJs . '\', \'' . $subTypeJs . '\', ' . $classIdJs . ', \'' . $subDescJs . '\')"' .
                               ' class="text-left group cursor-pointer hover:underline text-secondary flex flex-col gap-0.5" title="View/Edit Assignments">' .
                               $names_html .
                               '</button>';
            } else {
                $teachersCol = '<span class="text-[12px] text-on-surface-variant">—</span>';
            }

            $actionsCol = '<div class="flex items-center justify-end gap-1.5">';
            if ($can_edit) {
                $actionsCol .= '<button onclick="openEditSubjectModal(' . $sub->subject_id . ', \'' . $subNameJs . '\', \'' . $subCodeJs . '\', \'' . $subTypeJs . '\', ' . $classIdJs . ', \'' . $subDescJs . '\')"' .
                               ' class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors cursor-pointer" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></button>';
            }
            if ($can_delete) {
                $actionsCol .= '<a href="' . site_url('academics/delete_subject/' . $sub->subject_id) . '" onclick="return confirm(\'Deactivate subject?\')"' .
                               ' class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors" title="Deactivate"><span class="material-symbols-outlined text-[18px]">delete</span></a>';
            }
            $actionsCol .= '</div>';

            $data[] = array(
                $nameCol,
                $codeCol,
                $classCol,
                $typeCol,
                $teachersCol,
                $actionsCol
            );
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => $records_total,
            "recordsFiltered" => $records_filtered,
            "data"            => $data,
            "csrf_token_name" => $this->security->get_csrf_token_name(),
            "csrf_hash"       => $this->security->get_csrf_hash(),
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    public function delete_subject($id = NULL)
    {
        $this->require_permission(['subjects.delete', 'academics.delete', 'academics.manage']);
        $id = (int)$id;
        if (!empty($id)) {
            $existing_subject = $this->Subject_model->get_by_id($id, $this->school_id);
            if (!$existing_subject) {
                $this->session->set_flashdata('error', 'Subject not found or does not belong to the current school.');
                redirect('academics/subjects');
                return;
            }
            $this->Subject_model->soft_delete($id, $this->school_id);
            $this->session->set_flashdata('success', 'Subject deactivated.');
        }
        redirect('academics/subjects');
    }

    /* =========================================================================
       5. Class Teachers Assignment
       NOTE: Class Teacher assignment has been moved into the Classes & Divisions
             workflow (academics/classes). This endpoint exists solely to redirect
             any bookmarked or legacy URLs gracefully.
       ========================================================================= */
    public function class_teachers()
    {
        redirect('academics/classes', 'location', 301);
    }

    public function delete_class_teacher($id = NULL)
    {
        redirect('academics/classes', 'location', 301);
    }

    /* =========================================================================
       6. Subject Teachers Assignment
       NOTE: Subject Teacher assignment has been integrated into the Subjects
             module (academics/subjects). This endpoint exists solely to redirect
             any bookmarked or legacy URLs gracefully.
       ========================================================================= */
    public function subject_teachers()
    {
        redirect('academics/subjects', 'location', 301);
    }

    public function delete_subject_teacher($id = NULL)
    {
        redirect('academics/subjects', 'location', 301);
    }

    /**
     * AJAX: Return all active subject-teacher assignments for a given subject + academic year.
     * Used by the Edit Subject modal to pre-populate inline assignment rows.
     * GET /academics/ajax_get_subject_assignments?subject_id=X&academic_year_id=Y
     */
    public function ajax_get_subject_assignments()
    {
        header('Content-Type: application/json');
        $subject_id       = (int)$this->input->get('subject_id');
        $academic_year_id = (int)$this->input->get('academic_year_id');

        if (empty($subject_id)) {
            echo json_encode(array());
            return;
        }

        // Validate subject belongs to current school
        $subject = $this->Subject_model->get_by_id($subject_id, $this->school_id);
        if (!$subject) {
            echo json_encode(array());
            return;
        }

        $rows = $this->Subject_teacher_model->get_by_subject($subject_id, $academic_year_id ?: null, $this->school_id);
        echo json_encode($rows);
    }

    /**
     * AJAX: Save a single subject-teacher assignment.
     * POST /academics/ajax_save_subject_teacher
     * Body: academic_year_id, subject_id, class_id, division_id, staff_id
     * Returns JSON {success, subject_teacher_id} or {success:false, error}
     */
    public function ajax_save_subject_teacher()
    {
        header('Content-Type: application/json');
        if ($this->input->method() !== 'post') {
            echo json_encode(array('success' => false, 'error' => 'POST required.'));
            return;
        }
        $this->require_permission(['subjects.edit', 'academics.edit', 'academics.manage']);

        $year_id     = (int)$this->input->post('academic_year_id');
        $subject_id  = (int)$this->input->post('subject_id');
        $class_id    = (int)$this->input->post('class_id');
        $division_id = (int)($this->input->post('division_id') ?: $this->input->post('section_id'));
        $staff_id    = (int)$this->input->post('staff_id');

        if (!$year_id || !$subject_id || !$class_id || !$division_id || !$staff_id) {
            echo json_encode(array('success' => false, 'error' => 'All fields are required.'));
            return;
        }

        // Validate academic year belongs to this school
        $year = $this->Academic_year_model->get_by_id($year_id, $this->school_id);
        if (!$year || $year->status != 1 || $year->is_deleted !== 'n') {
            echo json_encode(array('success' => false, 'error' => 'Invalid academic year.'));
            return;
        }

        // Validate class belongs to this school
        $class = $this->Class_model->get_by_id($class_id, $this->school_id);
        if (!$class || $class->status != 1 || $class->is_deleted !== 'n') {
            echo json_encode(array('success' => false, 'error' => 'Invalid class selected.'));
            return;
        }

        // Validate division belongs to class & this school
        $division = $this->Division_model->get_by_id($division_id);
        if (!$division || $division->status != 1 || $division->is_deleted !== 'n' || (int)$division->class_id !== $class_id || (int)$division->school_id !== (int)$this->school_id) {
            echo json_encode(array('success' => false, 'error' => 'Division does not belong to the selected class.'));
            return;
        }

        // Validate subject exists and belongs to this school
        $subject = $this->Subject_model->get_by_id($subject_id, $this->school_id);
        if (!$subject || $subject->status != 1) {
            echo json_encode(array('success' => false, 'error' => 'Invalid subject or subject does not belong to this school.'));
            return;
        }

        // Validate staff is active teaching faculty of this school
        $staff = $this->db
            ->select('s.*, d.category as designation_category')
            ->from('tbl_staff s')
            ->join('tbl_designations d', 'd.designation_id = s.designation_id AND d.school_id = s.school_id', 'left')
            ->where('s.staff_id', $staff_id)
            ->where('s.school_id', $this->school_id)
            ->where('s.is_deleted', 'n')
            ->get()
            ->row();

        $is_teaching = $staff && (
            strcasecmp($staff->designation_category ?? '', 'Teaching') === 0 ||
            strcasecmp($staff->category ?? '', 'Teaching') === 0 ||
            strcasecmp($staff->staff_type ?? '', 'teacher') === 0
        );

        if (!$staff || (int)$staff->status !== 1 || !$is_teaching) {
            echo json_encode(array('success' => false, 'error' => 'Only active teaching faculty of this school can be assigned.'));
            return;
        }

        $result_id = $this->Subject_teacher_model->assign($year_id, $class_id, $division_id, $subject_id, $staff_id, $this->school_id);
        if ($result_id) {
            echo json_encode(array(
                'success'            => true,
                'subject_teacher_id' => (int)$result_id,
                'csrf_token_name'    => $this->security->get_csrf_token_name(),
                'csrf_hash'          => $this->security->get_csrf_hash()
            ));
        } else {
            $err = $this->Subject_teacher_model->get_last_error();
            echo json_encode(array(
                'success'         => false,
                'error'           => $err ?: 'Assignment failed. Only teaching staff can be assigned.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ));
        }
    }

    /**
     * AJAX: Delete a single subject-teacher assignment by ID.
     * POST /academics/ajax_delete_subject_teacher
     * Body: subject_teacher_id
     * Returns JSON {success} or {success:false, error}
     */
    public function ajax_delete_subject_teacher()
    {
        header('Content-Type: application/json');
        if ($this->input->method() !== 'post') {
            echo json_encode(array('success' => false, 'error' => 'POST required.'));
            return;
        }
        $this->require_permission(['subjects.edit', 'academics.edit', 'academics.manage']);

        $id = (int)$this->input->post('subject_teacher_id');
        if (empty($id)) {
            echo json_encode(array('success' => false, 'error' => 'Invalid ID.'));
            return;
        }

        $ok = $this->Subject_teacher_model->delete($id, $this->school_id);
        echo json_encode(array(
            'success'         => (bool)$ok,
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        ));
    }

    /* =========================================================================
       7. Timetable & Periods Management
       ========================================================================= */
    public function timetable()
    {
        $this->require_permission('timetable.view');
        if ($this->input->method() === 'post') {
            $this->require_permission('timetable.edit');
            $action = $this->input->post('action');
            if ($action === 'save_entry') {
                $ttId = $this->input->post('timetable_id');
                $entryData = array(
                    'academic_year_id' => $this->input->post('academic_year_id') ?: 1,
                    'class_id'         => $this->input->post('class_id'),
                    'section_id'       => $this->input->post('section_id'),
                    'day'              => $this->input->post('day'),
                    'period_id'        => $this->input->post('period_id'),
                    'subject_id'       => $this->input->post('subject_id'),
                    'teacher_id'       => $this->input->post('teacher_id')
                );

                $result = $this->Timetable_model->save_entry($entryData, $ttId ?: NULL);
                if ($result['success']) {
                    $this->session->set_flashdata('success', 'Timetable period scheduled successfully!');
                } else {
                    $this->session->set_flashdata('error', 'Timetable Conflict: ' . $result['message']);
                }
            } elseif ($action === 'add_period') {
                $this->Period_model->insert(array(
                    'period_name'  => $this->input->post('period_name'),
                    'start_time'   => $this->input->post('start_time'),
                    'end_time'     => $this->input->post('end_time'),
                    'period_order' => $this->input->post('period_order') ? intval($this->input->post('period_order')) : 1,
                    'status'       => 1,
                    'created_at'   => date('Y-m-d H:i:s')
                ));
                $this->session->set_flashdata('success', 'Period slot created successfully!');
            } elseif ($action === 'edit_period') {
                $pId = $this->input->post('period_id');
                $this->Period_model->update($pId, array(
                    'period_name'  => $this->input->post('period_name'),
                    'start_time'   => $this->input->post('start_time'),
                    'end_time'     => $this->input->post('end_time'),
                    'period_order' => $this->input->post('period_order') ? intval($this->input->post('period_order')) : 1,
                    'updated_at'   => date('Y-m-d H:i:s')
                ));
                $this->session->set_flashdata('success', 'Period slot updated successfully!');
            }
            redirect('academics/timetable?academic_year_id=' . $this->input->post('academic_year_id') . '&class_id=' . $this->input->post('class_id') . '&section_id=' . $this->input->post('section_id'));
        }

        $years    = $this->Academic_year_model->get_all();
        $classes  = $this->Class_model->get_all();
        $sections = $this->Section_model->get_all();
        $periods  = $this->Period_model->get_all();
        $subjects = $this->Subject_model->get_all();
        $teachers = $this->Staff_model->get_teachers();

        $active_year = get_active_academic_year(TRUE);
        $default_year_id = get_active_academic_year_id(TRUE) ?: 1;

        $selected_year    = $this->input->get('academic_year_id') ?: $default_year_id;
        $selected_class   = $this->input->get('class_id') ?: (isset($classes[0]) ? $classes[0]->class_id : 1);

        // Filter sections for the selected class
        $class_sections = array();
        foreach ($sections as $s) {
            if ($s->class_id == $selected_class) {
                $class_sections[] = $s;
            }
        }
        $selected_section = $this->input->get('section_id') ?: (isset($class_sections[0]) ? $class_sections[0]->section_id : (isset($sections[0]) ? $sections[0]->section_id : 1));

        $entries = $this->Timetable_model->get_entries(array(
            'academic_year_id' => $selected_year,
            'class_id'         => $selected_class,
            'section_id'       => $selected_section
        ));

        // Build Day x Period matrix
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $grid = array();
        foreach ($days as $d) {
            $grid[$d] = array();
            foreach ($periods as $p) {
                $grid[$d][$p->period_id] = NULL;
            }
        }
        foreach ($entries as $e) {
            if (isset($grid[$e->day]) && array_key_exists($e->period_id, $grid[$e->day])) {
                $grid[$e->day][$e->period_id] = $e;
            }
        }

        $this->render('pages/academics/timetable', array(
            'title'            => 'Timetable',
            'page_key'         => 'timetable',
            'breadcrumb'       => array('Academic Management', 'Timetable'),
            'years'            => $years,
            'classes'          => $classes,
            'sections'         => $sections,
            'class_sections'   => $class_sections,
            'periods'          => $periods,
            'subjects'         => $subjects,
            'teachers'         => $teachers,
            'days'             => $days,
            'grid'             => $grid,
            'selected_year'    => $selected_year,
            'selected_class'   => $selected_class,
            'selected_section' => $selected_section,
        ));
    }

    public function delete_timetable_entry($id = NULL)
    {
        $this->require_permission('timetable.edit');
        $year_id = $this->input->get('academic_year_id') ?: 1;
        $class_id = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        if (!empty($id)) {
            $this->Timetable_model->delete_entry($id);
            $this->session->set_flashdata('success', 'Timetable entry removed.');
        }
        redirect('academics/timetable?academic_year_id=' . $year_id . '&class_id=' . $class_id . '&section_id=' . $section_id);
    }

    public function delete_period($id = NULL)
    {
        $this->require_permission('timetable.edit');
        if (!empty($id)) {
            $this->Period_model->soft_delete($id);
            $this->session->set_flashdata('success', 'Period slot deleted.');
        }
        redirect('academics/timetable');
    }

    /* =========================================================================
       8. Academic Calendar Management
       ========================================================================= */
    public function calendar()
    {
        $this->require_permission(['academic_calendar.view', 'academics.view']);
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add') {
                $this->require_permission(['academic_calendar.create', 'academics.create', 'academics.manage']);
                $this->form_validation->set_rules('title', 'Event / Holiday Title', 'required|trim');
                $this->form_validation->set_rules('start_date', 'Start Date', 'required');

                if ($this->form_validation->run() === TRUE) {
                    $this->Academic_calendar_model->insert(array(
                        'academic_year_id' => $this->input->post('academic_year_id') ?: 1,
                        'title'            => $this->input->post('title'),
                        'event_type'       => $this->input->post('event_type') ?: 'Event',
                        'start_date'       => $this->input->post('start_date'),
                        'end_date'         => $this->input->post('end_date') ?: $this->input->post('start_date'),
                        'audience'         => $this->input->post('audience') ?: 'Whole School',
                        'venue'            => $this->input->post('venue'),
                        'description'      => $this->input->post('description'),
                        'status'           => 1,
                        'created_at'       => date('Y-m-d H:i:s')
                    ));
                    $this->session->set_flashdata('success', 'Academic calendar event added successfully!');
                } else {
                    $this->session->set_flashdata('error', validation_errors());
                }
            } elseif ($action === 'edit') {
                $this->require_permission(['academic_calendar.edit', 'academics.edit', 'academics.manage']);
                $id = $this->input->post('calendar_id');
                $this->Academic_calendar_model->update($id, array(
                    'academic_year_id' => $this->input->post('academic_year_id') ?: 1,
                    'title'            => $this->input->post('title'),
                    'event_type'       => $this->input->post('event_type') ?: 'Event',
                    'start_date'       => $this->input->post('start_date'),
                    'end_date'         => $this->input->post('end_date') ?: $this->input->post('start_date'),
                    'audience'         => $this->input->post('audience') ?: 'Whole School',
                    'venue'            => $this->input->post('venue'),
                    'description'      => $this->input->post('description'),
                    'updated_at'       => date('Y-m-d H:i:s')
                ));
                $this->session->set_flashdata('success', 'Academic calendar event updated successfully!');
            }

            $redirect_params = array();
            if ($this->input->post('redirect_academic_year')) $redirect_params['academic_year_id'] = $this->input->post('redirect_academic_year');
            if ($this->input->post('redirect_month')) $redirect_params['month'] = $this->input->post('redirect_month');
            if ($this->input->post('redirect_year')) $redirect_params['year'] = $this->input->post('redirect_year');
            if ($this->input->post('redirect_view')) $redirect_params['view_mode'] = $this->input->post('redirect_view');
            $qs = !empty($redirect_params) ? ('?' . http_build_query($redirect_params)) : '';
            redirect('academics/calendar' . $qs);
            return;
        }

        $years = $this->Academic_year_model->get_all();
        $active_year = $this->Academic_year_model->get_active_year();
        $selected_year = (int)($this->input->get('academic_year_id') ?: ($active_year ? $active_year->academic_year_id : 1));
        $selected_type = trim($this->input->get('event_type') ?: '');
        $selected_month = (int)($this->input->get('month') ?: date('n'));
        $selected_cal_year = (int)($this->input->get('year') ?: date('Y'));
        $view_mode = trim($this->input->get('view_mode') ?: ($this->input->get('view') ?: ''));

        if ($selected_month < 1 || $selected_month > 12) {
            $selected_month = (int)date('n');
        }
        if ($selected_cal_year < 1970 || $selected_cal_year > 2099) {
            $selected_cal_year = (int)date('Y');
        }

        $filters = array(
            'academic_year_id' => $selected_year,
            'event_type'       => $selected_type,
        );

        $events = $this->Academic_calendar_model->get_all($filters);
        $upcoming = $this->Academic_calendar_model->get_upcoming(5, $selected_year);
        $settings = $this->Setting_model->get_settings();
        $selected_year_obj = $this->Academic_year_model->get_by_id($selected_year);

        $this->render('pages/academics/calendar', array(
            'title'             => 'Academic Calendar',
            'page_key'          => 'academic-calendar',
            'breadcrumb'        => array('Academic Management', 'Academic Calendar'),
            'events'            => $events,
            'upcoming'          => $upcoming,
            'years'             => $years,
            'selected_year'     => $selected_year,
            'selected_year_obj' => $selected_year_obj,
            'selected_type'     => $selected_type,
            'selected_month'    => $selected_month,
            'selected_cal_year' => $selected_cal_year,
            'view_mode'         => $view_mode,
            'settings'          => $settings,
        ));
    }

    public function delete_calendar_event($id = NULL)
    {
        $this->require_permission(['academic_calendar.delete', 'academics.delete', 'academics.manage']);
        if (!empty($id)) {
            $this->Academic_calendar_model->soft_delete($id);
            $this->session->set_flashdata('success', 'Calendar event removed.');
        }

        $redirect_params = array();
        if ($this->input->post('redirect_academic_year')) $redirect_params['academic_year_id'] = $this->input->post('redirect_academic_year');
        if ($this->input->post('redirect_month')) $redirect_params['month'] = $this->input->post('redirect_month');
        if ($this->input->post('redirect_year')) $redirect_params['year'] = $this->input->post('redirect_year');
        if ($this->input->post('redirect_view')) $redirect_params['view_mode'] = $this->input->post('redirect_view');
        $qs = !empty($redirect_params) ? ('?' . http_build_query($redirect_params)) : '';
        redirect('academics/calendar' . $qs);
    }

    /**
     * Download / Print Academic Calendar PDF
     */
    public function calendar_pdf()
    {
        $this->require_permission(['academic_calendar.view', 'academics.view']);
        $active_year = $this->Academic_year_model->get_active_year((int)$this->school_id);
        $selected_year_id = (int)($this->input->get('academic_year_id') ?: ($active_year ? $active_year->academic_year_id : $this->academic_year_id));
        $selected_year = $this->Academic_year_model->get_by_id($selected_year_id, (int)$this->school_id);
        if (!$selected_year) {
            $selected_year = $active_year;
            $selected_year_id = $selected_year ? (int)$selected_year->academic_year_id : (int)$this->academic_year_id;
        }

        $settings = $this->Setting_model->get_settings();

        // Logo resolution
        $logo_url = '';
        if ($settings && !empty($settings->logo)) {
            if (file_exists(FCPATH . 'uploads/settings/' . $settings->logo)) {
                $logo_url = base_url('uploads/settings/' . $settings->logo);
            } elseif (file_exists(FCPATH . 'uploads/id_card/' . $settings->logo)) {
                $logo_url = base_url('uploads/id_card/' . $settings->logo);
            }
        }
        if (empty($logo_url) && $settings && !empty($settings->school_logo)) {
            if (file_exists(FCPATH . 'uploads/id_card/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/id_card/' . $settings->school_logo);
            } elseif (file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/settings/' . $settings->school_logo);
            }
        }
        if (empty($logo_url) && file_exists(FCPATH . 'assets/logo.png')) {
            $logo_url = base_url('assets/logo.png');
        }

        $events = $this->Academic_calendar_model->get_all(array(
            'academic_year_id' => $selected_year_id
        ));

        // Group events chronologically by month
        $events_by_month = array();
        foreach ($events as $ev) {
            $month_key = date('Y-m', strtotime($ev->start_date));
            if (!isset($events_by_month[$month_key])) {
                $events_by_month[$month_key] = array(
                    'label'     => date('F Y', strtotime($ev->start_date)),
                    'month_num' => (int)date('n', strtotime($ev->start_date)),
                    'year_num'  => (int)date('Y', strtotime($ev->start_date)),
                    'events'    => array()
                );
            }
            $events_by_month[$month_key]['events'][] = $ev;
        }

        // Calculate summary counters
        $total_events     = count($events);
        $total_holidays   = count(array_filter($events, function($e) { return $e->event_type === 'Holiday'; }));
        $total_exams      = count(array_filter($events, function($e) { return $e->event_type === 'Exam'; }));
        $total_breaks     = count(array_filter($events, function($e) { return $e->event_type === 'Term Break'; }));
        $total_activities = count(array_filter($events, function($e) { return in_array($e->event_type, array('Activity', 'Event')); }));
        $total_meetings   = count(array_filter($events, function($e) { return $e->event_type === 'Meeting'; }));

        $this->load->library('document_design_service');
        $document_design = $this->document_design_service->resolve_design($this->school_id, 'academic_calendar');

        $data = array(
            'title'            => 'Academic Calendar - ' . ($selected_year ? $selected_year->year_name : 'Annual Calendar'),
            'settings'         => $settings,
            'logo_url'         => $logo_url,
            'academic_year'    => $selected_year,
            'events'           => $events,
            'events_by_month'  => $events_by_month,
            'total_events'     => $total_events,
            'total_holidays'   => $total_holidays,
            'total_exams'      => $total_exams,
            'total_breaks'     => $total_breaks,
            'total_activities' => $total_activities,
            'total_meetings'   => $total_meetings,
            'autoprint'        => (int)$this->input->get('autoprint'),
            'autodownload'     => (int)$this->input->get('download'),
            'document_design'  => $document_design,
        );

        $this->load->view('pages/academics/calendar_pdf', $data);
    }

    public function download_calendar_pdf()
    {
        $this->calendar_pdf();
    }

    /* =========================================================================
       9. Dynamic AJAX Dropdowns & Helpers
       ========================================================================= */
    public function ajax_get_classes($year_id = NULL)
    {
        $this->require_permission(['classes.view', 'academics.view']);
        header('Content-Type: application/json');
        if (empty($year_id)) {
            $year_id = $this->input->get('academic_year_id') ?: $this->input->get('year_id');
        }
        if (empty($year_id) || $year_id === 'all') {
            $classes = $this->Class_model->get_all();
        } else {
            $classes = $this->Class_model->get_all((int)$year_id);
        }
        echo json_encode($classes ?: array());
    }

    public function ajax_get_divisions($class_id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($class_id)) {
            $class_id = $this->input->get('class_id');
        }
        if (empty($class_id)) {
            echo json_encode(array());
            return;
        }
        $divisions = $this->Division_model->get_all($class_id, $this->school_id);
        echo json_encode($divisions);
    }

    public function ajax_get_sections($class_id = NULL)
    {
        $this->ajax_get_divisions($class_id);
    }

    public function ajax_get_subjects($class_id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($class_id)) {
            $class_id = $this->input->get('class_id');
        }
        if (empty($class_id)) {
            echo json_encode(array());
            return;
        }
        $academic_year_id = $this->input->get('academic_year_id');
        $subjects = $this->Subject_model->get_for_class($academic_year_id, (int)$class_id, $this->school_id);
        echo json_encode($subjects);
    }

    public function ajax_get_teachers_for_subject()
    {
        header('Content-Type: application/json');
        $year_id    = $this->input->get('academic_year_id') ?: ($this->academic_year_id ?: NULL);
        $class_id   = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $subject_id = $this->input->get('subject_id');

        $assigned = array();
        if ($class_id && $section_id && $subject_id) {
            $assigned = $this->Subject_teacher_model->get_teachers_by_subject($year_id, $class_id, $section_id, $subject_id, $this->school_id);
        }

        // If no assigned teacher for this specific subject/class/section, return all active teachers of this school
        if (empty($assigned)) {
            $teachers = $this->Staff_model->get_teachers(array('school_id' => $this->school_id));
            echo json_encode(array('source' => 'all', 'teachers' => $teachers));
        } else {
            echo json_encode(array('source' => 'assigned', 'teachers' => $assigned));
        }
    }

    public function ajax_get_timetable_entry($id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($id)) {
            echo json_encode(array('success' => FALSE));
            return;
        }
        $entry = $this->Timetable_model->get_by_id($id);
        if ($entry) {
            echo json_encode(array('success' => TRUE, 'entry' => $entry));
        } else {
            echo json_encode(array('success' => FALSE));
        }
    }

    public function ajax_get_calendar_event($id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($id)) {
            echo json_encode(array('success' => FALSE));
            return;
        }
        $event = $this->Academic_calendar_model->get_by_id($id);
        if ($event) {
            echo json_encode(array('success' => TRUE, 'event' => $event));
        } else {
            echo json_encode(array('success' => FALSE));
        }
    }

    public function ajax_get_class_teacher_assignments($year_id = null)
    {
        $this->require_permission(['classes.view', 'academics.view']);
        header('Content-Type: application/json');
        $academic_year_id = (int)($year_id ?: ($this->input->get('academic_year_id') ?: $this->input->get('year_id')));
        if (empty($academic_year_id)) {
            $active_year = $this->Academic_year_model->get_active_year();
            $academic_year_id = $active_year ? (int)$active_year->academic_year_id : 1;
        }

        if (empty($academic_year_id)) {
            echo json_encode(array('status' => true, 'year_id' => 0, 'assignments' => array()));
            return;
        }

        $assignments = $this->Class_teacher_model->get_active_assignments_by_year($academic_year_id);
        echo json_encode(array(
            'status'      => true,
            'year_id'     => $academic_year_id,
            'assignments' => $assignments ?: array()
        ));
    }
}
