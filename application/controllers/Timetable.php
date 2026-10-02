<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timetable extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Timetable_model');
        $this->load->model('Timetable_allocation_model');
        $this->load->model('Timetable_substitution_model');
        $this->load->model('Timetable_setting_model');
        $this->load->model('Period_model');
        $this->load->model('Academic_group_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Subject_model');
        $this->load->model('Staff_model');
    }

    // 1. Timetable Dashboard
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->academic_year_id;
        $active_year = $this->Academic_year_model->get_by_id($year_id);

        $data['title'] = 'Timetable Dashboard';
        $data['active_year'] = $active_year;
        $data['stats'] = $this->Timetable_model->get_dashboard_stats($year_id);
        $data['recent_entries'] = $this->Timetable_model->get_entries(['academic_year_id' => $year_id]);
        $data['periods'] = $this->Period_model->get_all(TRUE);
        $data['conflicts'] = $this->Timetable_model->detect_all_conflicts($year_id);
        $data['classes'] = $this->Class_model->get_all($year_id);

        $this->render('pages/timetable/dashboard', $data);
    }

    // 2. Class Timetable Matrix
    public function classes()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        
        $classes = $this->Class_model->get_all($year_id);
        $class_id = $this->input->get('class_id') ?: ($classes[0]->class_id ?? 1);
        
        $sections = $this->Division_model->get_by_class($class_id);
        $section_id = $this->input->get('division_id') ?: ($this->input->get('section_id') ?: ($sections[0]->division_id ?? ($sections[0]->section_id ?? 1)));

        // Handle Add/Edit schedule slot post
        if ($this->input->post()) {
            $this->require_permission('timetable.manage');
            $tt_id = $this->input->post('timetable_id');
            $teacher_id = (int)$this->input->post('teacher_id');

            if ($teacher_id > 0 && $class_id > 0) {
                $group_check = $this->Staff_model->validate_teacher_class_group($teacher_id, $class_id, $this->school_id);
                if (!$group_check['valid']) {
                    $this->session->set_flashdata('error', $group_check['error']);
                    redirect("timetable/classes?academic_year_id={$year_id}&class_id={$class_id}&division_id={$section_id}");
                    return;
                }
            }

            $postData = [
                'academic_year_id' => $year_id,
                'class_id'         => $class_id,
                'division_id'       => $section_id,
                'day'              => $this->input->post('day'),
                'period_id'        => $this->input->post('period_id'),
                'subject_id'       => $this->input->post('subject_id'),
                'teacher_id'       => $teacher_id ?: NULL,
                'room_no'          => $this->input->post('room_no'),
            ];

            $res = $this->Timetable_model->save_entry($postData, $tt_id ?: NULL);
            if ($res['success']) {
                $this->session->set_flashdata('success', 'Schedule slot saved successfully!');
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
            redirect("timetable/classes?academic_year_id={$year_id}&class_id={$class_id}&division_id={$section_id}");
            return;
        }

        $data['title'] = 'Class Timetable';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['classes'] = $classes;
        $data['selected_class'] = $class_id;
        $data['divisions'] = $sections;
        $data['sections'] = $sections;
        $data['selected_division'] = $section_id;
        $data['selected_section'] = $section_id;
        $data['periods'] = $this->Period_model->get_by_class($class_id, TRUE, 'Period');
        $data['all_slots'] = $this->Period_model->get_by_class($class_id, TRUE);
        $data['working_days'] = $this->Timetable_setting_model->get_working_days_array();
        $data['matrix'] = $this->Timetable_model->get_matrix_for_class($year_id, $class_id, $section_id);
        $data['subjects'] = $this->Subject_model->get_for_class($year_id, $class_id, $section_id);
        $cls_obj = $this->Class_model->get_by_id($class_id, $this->school_id);
        $data['teachers'] = ($cls_obj && !empty($cls_obj->academic_group_id))
            ? $this->Staff_model->get_teachers_by_group($cls_obj->academic_group_id, $this->school_id)
            : $this->Staff_model->get_teaching_staff();
        $data['is_locked'] = $this->Timetable_model->is_schedule_locked($year_id, $class_id, $section_id);

        $this->render('pages/timetable/classes', $data);
    }

    // 3. Teacher Timetable Matrix
    public function teachers()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $group_id = $this->input->get('academic_group_id');
        
        $teachers = $group_id
            ? $this->Staff_model->get_teachers_by_group((int)$group_id, $this->school_id)
            : $this->Staff_model->get_teaching_staff();
        $teacher_id = $this->input->get('teacher_id') ?: ($teachers[0]->staff_id ?? 1);

        $data['title'] = 'Teacher Timetable';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['groups'] = $this->Academic_group_model->get_all();
        $data['selected_group'] = $group_id;
        $data['teachers'] = $teachers;
        $data['selected_teacher'] = $teacher_id;
        $data['current_teacher'] = $this->Staff_model->get_by_id($teacher_id);
        $data['periods'] = $this->Period_model->get_for_teacher($teacher_id, $year_id);
        $data['working_days'] = $this->Timetable_setting_model->get_working_days_array();
        $data['matrix'] = $this->Timetable_model->get_matrix_for_teacher($year_id, $teacher_id);
        
        // Workload count
        $entries = $this->Timetable_model->get_entries(['academic_year_id' => $year_id, 'teacher_id' => $teacher_id]);
        $data['weekly_periods_count'] = count($entries);

        $this->render('pages/timetable/teachers', $data);
    }

    // 4. Subject Allocation
    public function allocations()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $class_id = $this->input->get('class_id');
        $division_id = $this->input->get('division_id') ?: $this->input->get('section_id');

        if ($this->input->post()) {
            $this->require_permission('timetable.manage');
            $alloc_id = $this->input->post('allocation_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Timetable_allocation_model->delete_allocation($alloc_id);
                $this->session->set_flashdata('success', 'Subject allocation removed successfully.');
            } else {
                $target_class_id = (int)$this->input->post('class_id');
                $target_teacher_id = (int)$this->input->post('teacher_id');

                if ($target_teacher_id > 0 && $target_class_id > 0) {
                    $group_check = $this->Staff_model->validate_teacher_class_group($target_teacher_id, $target_class_id, $this->school_id);
                    if (!$group_check['valid']) {
                        $this->session->set_flashdata('error', $group_check['error']);
                        redirect("timetable/allocations?academic_year_id={$year_id}&class_id={$class_id}&division_id={$division_id}");
                        return;
                    }
                }

                $postData = [
                    'academic_year_id'      => $year_id,
                    'class_id'              => $target_class_id,
                    'division_id'           => $this->input->post('division_id'),
                    'subject_id'            => $this->input->post('subject_id'),
                    'teacher_id'            => $target_teacher_id ?: NULL,
                    'weekly_periods_target' => (int)$this->input->post('weekly_periods_target'),
                    'status'                => 1
                ];
                $this->Timetable_allocation_model->save_allocation($postData, $alloc_id ?: NULL);
                $this->session->set_flashdata('success', 'Subject quota saved successfully!');
            }
            redirect("timetable/allocations?academic_year_id={$year_id}&class_id={$class_id}&division_id={$division_id}");
            return;
        }

        $data['title'] = 'Subject Allocation';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['groups'] = $this->Academic_group_model->get_all();
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['selected_class'] = $class_id;
        $divisions = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $data['divisions'] = $divisions;
        $data['sections'] = $divisions;
        $data['selected_division'] = $division_id;
        $data['selected_section'] = $division_id;
        $data['allocations'] = $this->Timetable_allocation_model->get_allocations($year_id, $class_id, $division_id);
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $data['teachers'] = $this->Staff_model->get_teaching_staff();

        $this->render('pages/timetable/allocations', $data);
    }

    // 5. Timetable Builder
    public function builder()
    {
        $this->require_permission('timetable.manage');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        
        $classes = $this->Class_model->get_all($year_id);
        $class_id = $this->input->get('class_id') ?: ($classes[0]->class_id ?? 1);
        
        $sections = $this->Division_model->get_by_class($class_id);
        $section_id = $this->input->get('division_id') ?: ($this->input->get('section_id') ?: ($sections[0]->division_id ?? ($sections[0]->section_id ?? 1)));

        if ($this->input->post()) {
            $tt_id = $this->input->post('timetable_id');
            $postData = [
                'academic_year_id' => $year_id,
                'class_id'         => $class_id,
                'division_id'       => $section_id,
                'day'              => $this->input->post('day'),
                'period_id'        => $this->input->post('period_id'),
                'subject_id'       => $this->input->post('subject_id'),
                'teacher_id'       => $this->input->post('teacher_id'),
                'room_no'          => $this->input->post('room_no'),
            ];

            $res = $this->Timetable_model->save_entry($postData, $tt_id ?: NULL);
            if ($res['success']) {
                $this->session->set_flashdata('success', 'Slot scheduled successfully!');
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
            redirect("timetable/builder?academic_year_id={$year_id}&class_id={$class_id}&division_id={$section_id}");
            return;
        }

        $data['title'] = 'Timetable Builder';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['classes'] = $classes;
        $data['selected_class'] = $class_id;
        $data['divisions'] = $sections;
        $data['sections'] = $sections;
        $data['selected_division'] = $section_id;
        $data['selected_section'] = $section_id;
        $data['periods'] = $this->Period_model->get_by_class($class_id, TRUE, 'Period');
        $data['all_slots'] = $this->Period_model->get_by_class($class_id, TRUE);
        $data['working_days'] = $this->Timetable_setting_model->get_working_days_array();
        $data['matrix'] = $this->Timetable_model->get_matrix_for_class($year_id, $class_id, $section_id);
        $data['subjects'] = $this->Subject_model->get_for_class($year_id, $class_id, $section_id);
        $data['teachers'] = $this->Staff_model->get_teaching_staff();
        $data['is_locked'] = $this->Timetable_model->is_schedule_locked($year_id, $class_id, $section_id);

        $this->render('pages/timetable/builder', $data);
    }

    // 6. Free Period & Teacher Substitution
    public function free_periods()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->academic_year_id;
        $date = $this->input->get('date') ?: date('Y-m-d');
        $day = date('l', strtotime($date));
        $period_id = $this->input->get('period_id');

        if ($this->input->post()) {
            $this->require_permission('timetable.manage');
            $postData = [
                'timetable_id'          => $this->input->post('timetable_id'),
                'substitution_date'     => $this->input->post('substitution_date'),
                'original_teacher_id'   => $this->input->post('original_teacher_id'),
                'substitute_teacher_id' => $this->input->post('substitute_teacher_id'),
                'reason'                => $this->input->post('reason'),
                'status'                => 'Scheduled',
                'created_by'            => $this->session->userdata('user_id') ?: 1
            ];
            $this->Timetable_substitution_model->save_substitution($postData);
            $this->session->set_flashdata('success', 'Teacher proxy substitution assigned successfully!');
            redirect("timetable/free_periods?date={$date}&period_id={$period_id}");
            return;
        }

        $data['title'] = 'Free Period & Substitution';
        $data['selected_date'] = $date;
        $data['day_of_week'] = $day;
        $data['periods'] = $this->Period_model->get_all(TRUE);
        $data['selected_period'] = $period_id ?: ($data['periods'][0]->period_id ?? 1);
        $data['free_teachers'] = $this->Timetable_substitution_model->get_free_teachers($year_id, $day, $data['selected_period']);
        $data['substitutions'] = $this->Timetable_substitution_model->get_substitutions($date);
        $data['day_schedules'] = $this->Timetable_model->get_entries(['academic_year_id' => $year_id, 'day' => $day, 'period_id' => $data['selected_period']]);

        $this->render('pages/timetable/free_periods', $data);
    }

    // 7. Conflict Management
    public function conflicts()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;

        $data['title'] = 'Timetable Conflicts';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['conflicts'] = $this->Timetable_model->detect_all_conflicts($year_id);

        $this->render('pages/timetable/conflicts', $data);
    }

    // 8. Publish & Lock
    public function publish_lock()
    {
        $this->require_permission('timetable.manage');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;

        if ($this->input->post()) {
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('division_id');
            $status = $this->input->post('status');

            $this->Timetable_setting_model->update_publish_status(
                $year_id, $class_id, $section_id, $status, $this->session->userdata('user_id') ?: 1
            );
            $this->session->set_flashdata('success', "Timetable status updated to {$status}!");
            redirect("timetable/publish_lock?academic_year_id={$year_id}");
            return;
        }

        $data['title'] = 'Publish & Lock Timetable';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['publish_records'] = $this->Timetable_setting_model->get_publish_records($year_id);

        $this->render('pages/timetable/publish_lock', $data);
    }

    // 9. Timetable Reports
    public function reports()
    {
        $this->require_permission('timetable.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $report_type = $this->input->get('type') ?: 'master';
        $class_id = $this->input->get('class_id');
        $teacher_id = $this->input->get('teacher_id');

        $filters = ['academic_year_id' => $year_id];
        if ($class_id) $filters['class_id'] = $class_id;
        if ($teacher_id) $filters['teacher_id'] = $teacher_id;

        $report_data = $this->Timetable_model->get_entries($filters);

        // CSV Export
        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=timetable_report_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Day', 'Period', 'Start Time', 'End Time', 'Class', 'Division', 'Subject', 'Teacher', 'Room']);
            foreach ($report_data as $r) {
                fputcsv($out, [
                    $r->day, $r->period_name, $r->start_time, $r->end_time,
                    $r->class_name, ($r->division_name ?: $r->section_name), $r->subject_name, $r->teacher_name, $r->room_no
                ]);
            }
            fclose($out);
            exit;
        }

        $data['title'] = 'Timetable Reports';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['report_type'] = $report_type;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['teachers'] = $this->Staff_model->get_teaching_staff();
        $data['periods'] = $this->Period_model->get_all(TRUE);
        $data['working_days'] = $this->Timetable_setting_model->get_working_days_array();
        $data['report_data'] = $report_data;
        $data['filters'] = ['class_id' => $class_id, 'teacher_id' => $teacher_id];

        $this->render('pages/timetable/reports', $data);
    }

    // 10. Timetable Settings
    public function settings()
    {
        $this->require_permission('timetable.manage');
        if ($this->input->post()) {
            $working_days = $this->input->post('working_days');
            $working_days_str = is_array($working_days) ? implode(',', $working_days) : 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday';

            $postData = [
                'working_days'            => $working_days_str,
                'max_periods_per_day'     => (int)$this->input->post('max_periods_per_day'),
                'max_consecutive_periods' => (int)$this->input->post('max_consecutive_periods'),
                'allow_teacher_overlap'   => $this->input->post('allow_teacher_overlap') ? 1 : 0,
                'auto_publish'            => $this->input->post('auto_publish') ? 1 : 0,
            ];

            $this->Timetable_setting_model->update_settings($postData);
            $this->session->set_flashdata('success', 'Timetable settings saved successfully!');
            redirect('timetable/settings');
            return;
        }

        $data['title'] = 'Timetable Settings';
        $data['settings'] = $this->Timetable_setting_model->get_settings();
        $data['working_days_selected'] = $this->Timetable_setting_model->get_working_days_array();

        $this->render('pages/timetable/settings', $data);
    }

    // AJAX: Get Entry Data
    public function ajax_get_entry($id)
    {
        $this->require_permission('timetable.view');
        $entry = $this->Timetable_model->get_by_id($id);
        $this->output->set_content_type('application/json')->set_output(json_encode($entry));
    }

    // Delete Slot
    public function delete_slot($id)
    {
        $this->require_permission('timetable.manage');
        $entry = $this->Timetable_model->get_by_id($id);
        $year_id = $entry ? $entry->academic_year_id : 1;
        $class_id = $entry ? $entry->class_id : 1;
        $division_id = $entry ? ($entry->division_id ?? $entry->section_id) : 1;

        $res = $this->Timetable_model->delete_entry($id);
        if ($res['success']) {
            $this->session->set_flashdata('success', 'Schedule slot removed successfully.');
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect("timetable/classes?academic_year_id={$year_id}&class_id={$class_id}&division_id={$division_id}");
    }

    // 12. Period Setup (Academic Group-Based)
    public function period_setup()
    {
        $this->require_permission('timetable.manage');

        $groups = $this->Academic_group_model->get_all();
        $selected_group_id = (int)$this->input->get('academic_group_id');
        if (!$selected_group_id && !empty($groups)) {
            $selected_group_id = (int)$groups[0]->academic_group_id;
        }

        if ($this->input->method() === 'post') {
            $group_id = (int)($this->input->post('academic_group_id') ?: $selected_group_id);
            $raw_slots = $this->input->post('slots');

            if (is_string($raw_slots)) {
                $slots = json_decode($raw_slots, true) ?: [];
            } else {
                $slots = is_array($raw_slots) ? $raw_slots : [];
            }

            $res = $this->Period_model->save_group_periods($group_id, $slots);

            if ($this->input->is_ajax_request()) {
                return $this->output->set_content_type('application/json')->set_output(json_encode([
                    'success'   => $res['success'],
                    'message'   => $res['message'],
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]));
            }

            if ($res['success']) {
                $this->session->set_flashdata('success', $res['message']);
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
            redirect('timetable/period_setup?academic_group_id=' . $group_id);
            return;
        }

        $slots = $this->Period_model->get_by_group($selected_group_id, FALSE);

        $data = [
            'title'             => 'Period Setup',
            'page_key'          => 'timetable-period-setup',
            'breadcrumb'        => array('Timetable', 'Period Setup'),
            'groups'            => $groups,
            'selected_group_id' => $selected_group_id,
            'slots'             => $slots,
        ];

        $this->render('pages/timetable/period_setup', $data);
    }

    public function periods()
    {
        $this->period_setup();
    }

    public function ajax_get_group_periods()
    {
        $this->require_permission('timetable.view');
        $group_id = (int)$this->input->get('academic_group_id');
        $slots = $this->Period_model->get_by_group($group_id, FALSE);

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'   => true,
            'group_id'  => $group_id,
            'slots'     => $slots,
            'csrf_hash' => $this->security->get_csrf_hash()
        ]));
    }

    public function ajax_get_divisions($class_id = NULL)
    {
        if (!$class_id) {
            $class_id = $this->input->get('class_id');
        }
        $divisions = $class_id ? $this->Division_model->get_all($class_id) : [];
        return $this->output->set_content_type('application/json')->set_output(json_encode($divisions));
    }
}

