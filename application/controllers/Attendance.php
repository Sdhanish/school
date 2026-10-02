<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Attendance_model');
        $this->load->model('Period_model');
        $this->load->model('Attendance_notification_model');
        $this->load->model('Attendance_setting_model');
        $this->load->model('Academic_group_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Student_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_teacher_model');
        $this->load->model('Subject_model');
    }

    // Permission keys: attendance.view, attendance.mark

    /* =========================================================================
       1. Attendance Dashboard
       ========================================================================= */
    public function index()
    {
        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $date       = normalize_date_to_academic_year($this->input->get('date'), $year_id);
        $class_id   = $this->input->get('class_id') ?: NULL;
        $division_id = $this->input->get('division_id') ?: ($this->input->get('section_id') ?: NULL);
        $section_id = $division_id;

        $current_year    = get_academic_year_record($year_id);
        $stats           = $this->Attendance_model->get_dashboard_stats($date, $class_id, $section_id, $year_id);
        $class_overview  = $this->Attendance_model->get_class_overview($date, $year_id, $class_id, $section_id);
        $recent_activity = $this->Attendance_model->get_recent_activity(8, $year_id);
        $classes         = $this->Class_model->get_all($year_id);
        $sections        = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $years           = $this->Academic_year_model->get_all();

        $this->render('pages/attendance/dashboard', array(
            'title'           => 'Attendance Dashboard',
            'page_key'        => 'attendance-dashboard',
            'breadcrumb'      => array('Attendance', 'Attendance Dashboard'),
            'stats'           => $stats,
            'class_overview'  => $class_overview,
            'recent_activity' => $recent_activity,
            'classes'         => $classes,
            'divisions'       => $sections,
            'sections'        => $sections,
            'years'           => $years,
            'current_year'    => $current_year,
            'date'            => $date,
            'class_id'        => $class_id,
            'division_id'     => $division_id,
            'section_id'      => $section_id,
            'year_id'         => $year_id,
        ));
    }

    /* =========================================================================
       2. Daily Attendance
       ========================================================================= */
    public function daily()
    {
        $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        redirect('attendance/class_attendance' . $query);
    }

    /* =========================================================================
       3. Period Setup (Relocated to Timetable Module)
       ========================================================================= */
    public function periods($action = NULL, $id = NULL)
    {
        // Period setup is now centralized under Timetable -> Period Setup
        redirect('timetable/period_setup');
    }

    /* =========================================================================
       4. Period-wise Attendance (+1 and +2 Only)
       ========================================================================= */
    public function period_wise()
    {
        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $date        = normalize_date_to_academic_year($this->input->get('date'), $year_id);
        $class_id    = $this->input->get('class_id') ?: NULL;
        $division_id = $this->input->get('division_id') ?: ($this->input->get('section_id') ?: NULL);
        $period_id   = $this->input->get('period_id') ?: NULL;

        $url = "attendance/mark_attendance?academic_year_id={$year_id}&date={$date}";
        if ($class_id) $url .= "&class_id={$class_id}";
        if ($division_id) $url .= "&division_id={$division_id}&section_id={$division_id}";
        if ($period_id) $url .= "&period_id={$period_id}";

        redirect($url);
        return;
    }

    /* =========================================================================
       5. Mark Attendance (Dedicated page for taking/updating attendance)
       ========================================================================= */
    public function mark_attendance()
    {
        $this->require_permission('attendance.mark');

        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        // Allow any date (including past dates)
        $raw_date = $this->input->get('date') ?: date('Y-m-d');
        $date     = date('Y-m-d', strtotime($raw_date));

        $all_classes = $this->Class_model->get_all($year_id);
        $classes     = $all_classes;

        // Role & permission handling: Teaching staff permitted classes
        $user_role  = $this->session->userdata('role_code') ?: ($this->current_user->role_code ?? '');
        $staff_id   = $this->session->userdata('staff_id') ?: ($this->current_user->staff_id ?? NULL);
        $is_teacher = ($user_role === 'TEACHER' || ($this->current_user->user_type ?? '') === 'Staff');
        $is_admin   = $this->rbac->is_super_admin() || in_array($user_role, array('SUPER_ADMIN', 'ADMIN', 'PRINCIPAL'));

        if ($is_teacher && !$is_admin && $staff_id) {
            $assigned = $this->Class_teacher_model->get_all(array('staff_id' => $staff_id, 'academic_year_id' => $year_id));
            if (!empty($assigned)) {
                $assigned_class_ids = array_unique(array_map(function($a) { return (int)$a->class_id; }, $assigned));
                $filtered = array_filter($all_classes, function($c) use ($assigned_class_ids) {
                    return in_array((int)$c->class_id, $assigned_class_ids);
                });
                if (!empty($filtered)) {
                    $classes = array_values($filtered);
                }
            }
        }

        $selected_group_id = $this->input->get('group_id') ? (int)$this->input->get('group_id') : NULL;
        $class_id = (int)($this->input->get('class_id') ?: (!empty($classes) ? $classes[0]->class_id : 1));

        $years          = $this->Academic_year_model->get_all();
        $selected_class = $class_id ? $this->Class_model->get_by_id($class_id) : NULL;
        if ($selected_class && !$selected_group_id) {
            $selected_group_id = (int)$selected_class->academic_group_id;
        }

        // Attendance type is strictly database-driven from tbl_academic_groups
        $attendance_type = 'daily';
        if ($selected_class && !empty($selected_class->attendance_type)) {
            $attendance_type = strtolower($selected_class->attendance_type);
        } elseif ($selected_group_id) {
            $attendance_type = $this->Academic_group_model->get_attendance_type($selected_group_id, $this->school_id);
        } elseif ($class_id) {
            $attendance_type = $this->Academic_group_model->get_attendance_type_by_class_id($class_id, $this->school_id);
        }
        $is_period_group = ($attendance_type === 'period');
        $is_higher_sec   = $is_period_group;

        // Division handling: Fallback to default Division A if none configured
        $divisions          = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $sections           = $divisions;
        $default_division_id = $class_id ? $this->Division_model->get_default_division_id($class_id) : NULL;
        $default_section_id  = $default_division_id;
        $division_id        = $this->input->get('division_id') ? (int)$this->input->get('division_id') : ($this->input->get('section_id') ? (int)$this->input->get('section_id') : $default_division_id);
        $section_id         = $division_id;
        $selected_division  = $division_id ? $this->Division_model->get_by_id($division_id) : NULL;
        $selected_section   = $selected_division;

        // Period and Subject handling for period-type groups
        $subjects   = array();
        $subject_id = $this->input->get('subject_id') ? (int)$this->input->get('subject_id') : NULL;
        $periods    = array();
        $period_id  = $this->input->get('period_id') ? (int)$this->input->get('period_id') : NULL;

        if ($is_period_group) {
            $subjects = $class_id ? $this->Subject_model->get_dropdown($class_id) : array();
            $periods  = $class_id ? $this->Period_model->get_by_class($class_id, TRUE, 'Period', $year_id) : ($selected_group_id ? $this->Period_model->get_by_group($selected_group_id, TRUE, 'Period', $year_id) : array());
        }

        // Handle POST submission for attendance marking/updating
        if ($this->input->method() === 'post') {
            $this->require_permission('attendance.mark');
            $post_attendance = $this->input->post('attendance');
            $post_raw_date   = $this->input->post('date') ?: $date;
            $post_date       = date('Y-m-d', strtotime($post_raw_date));
            $post_class_id   = (int)($this->input->post('class_id') ?: $class_id);
            $post_division_id = (int)($this->input->post('division_id') ?: ($this->input->post('section_id') ?: $section_id));
            $post_section_id  = $post_division_id;
            $post_year_id    = (int)($this->input->post('academic_year_id') ?: $year_id);
            $user_id         = (int)$this->session->userdata('user_id');

            $post_class = $this->Class_model->get_by_id($post_class_id);
            $post_attendance_type = ($post_class && !empty($post_class->attendance_type)) ? strtolower($post_class->attendance_type) : $this->Academic_group_model->get_attendance_type_by_class_id($post_class_id, $this->school_id);

            if ($post_attendance_type === 'period') {
                // Period-wise attendance
                $post_period_id  = (int)$this->input->post('period_id');
                $post_subject_id = $this->input->post('subject_id') ? (int)$this->input->post('subject_id') : NULL;

                if (!$post_period_id) {
                    $this->session->set_flashdata('error', 'Please select a period to save attendance.');
                    redirect("attendance/mark_attendance?class_id={$post_class_id}&section_id={$post_section_id}&date={$post_date}&academic_year_id={$post_year_id}");
                    return;
                }

                if (is_array($post_attendance) && !empty($post_attendance)) {
                    $saved = $this->Attendance_model->save_period_attendance(
                        $post_attendance, $post_date, $post_period_id, $post_year_id, $post_class_id, $post_section_id, $user_id, $post_subject_id
                    );
                    $this->session->set_flashdata('success', "Period attendance saved for {$saved} student(s) on " . date('d M Y', strtotime($post_date)) . '.');
                } else {
                    $this->session->set_flashdata('error', 'No student attendance records were submitted.');
                }

                $red = "attendance/mark_attendance?class_id={$post_class_id}&section_id={$post_section_id}&date={$post_date}&academic_year_id={$post_year_id}&period_id={$post_period_id}";
                if ($post_subject_id) $red .= "&subject_id={$post_subject_id}";
                redirect($red);
                return;
            } else {
                // Daily attendance
                if (is_array($post_attendance) && !empty($post_attendance)) {
                    $saved = $this->Attendance_model->save_daily_attendance(
                        $post_attendance, $post_date, $post_year_id, $post_class_id, $post_section_id, $user_id
                    );
                    $this->session->set_flashdata('success', "Daily attendance saved for {$saved} student(s) on " . date('d M Y', strtotime($post_date)) . '.');
                } else {
                    $this->session->set_flashdata('error', 'No student attendance records were submitted.');
                }

                redirect("attendance/mark_attendance?class_id={$post_class_id}&section_id={$post_section_id}&date={$post_date}&academic_year_id={$post_year_id}");
                return;
            }
        }

        // Load students roll sheet and check existing attendance
        $students          = array();
        $is_already_marked = FALSE;

        if ($class_id && $section_id) {
            if ($is_period_group) {
                if ($period_id) {
                    $students          = $this->Attendance_model->get_period_sheet($date, $period_id, $class_id, $section_id, $year_id, $subject_id);
                    $is_already_marked = $this->Attendance_model->check_period_marked($date, $period_id, $class_id, $section_id, $year_id, $subject_id);
                }
            } else {
                $students          = $this->Attendance_model->get_daily_sheet($date, $class_id, $section_id, $year_id);
                $is_already_marked = $this->Attendance_model->check_daily_marked($date, $class_id, $section_id, $year_id);
            }
        }

        $current_year = get_academic_year_record($year_id);

        $this->render('pages/attendance/mark_attendance', array(
            'title'             => 'Mark Student Attendance',
            'page_key'          => 'attendance-mark',
            'breadcrumb'        => array('Attendance', 'Mark Attendance'),
            'groups'            => $this->Academic_group_model->get_all(false, $year_id),
            'selected_group_id' => $selected_group_id,
            'classes'           => $classes,
            'years'             => $years,
            'divisions'         => $sections,
            'sections'          => $sections,
            'division_id'       => $section_id,
            'current_year'      => $current_year,
            'selected_class'    => $selected_class,
            'selected_division' => $selected_section,
            'selected_section'  => $selected_section,
            'class_id'          => $class_id,
            'section_id'        => $section_id,
            'year_id'           => $year_id,
            'date'              => $date,
            'attendance_type'   => $attendance_type,
            'is_period_group'   => $is_period_group,
            'is_higher_sec'     => $is_period_group,
            'subjects'          => $subjects,
            'subject_id'        => $subject_id,
            'periods'           => $periods,
            'period_id'         => $period_id,
            'students'          => $students,
            'is_already_marked' => $is_already_marked,
        ));
    }

    /* =========================================================================
       6. Class Attendance (View Only - Summary & Section Cards)
       ========================================================================= */
    public function class_attendance()
    {
        // Class Attendance is strictly VIEW-ONLY. If a POST is received, redirect to Mark Attendance.
        if ($this->input->method() === 'post') {
            $class_id   = $this->input->post('class_id') ?: 1;
            $section_id = $this->input->post('section_id') ?: NULL;
            $date       = $this->input->post('date') ?: date('Y-m-d');
            $year_id    = $this->input->post('academic_year_id') ?: $this->academic_year_id;
            redirect("attendance/mark_attendance?class_id={$class_id}&section_id={$section_id}&date={$date}&academic_year_id={$year_id}");
            return;
        }

        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $date     = normalize_date_to_academic_year($this->input->get('date'), $year_id);
        $class_id = $this->input->get('class_id') ?: 1;

        $classes        = $this->Class_model->get_all($year_id);
        $years          = $this->Academic_year_model->get_all();
        $selected_class = $this->Class_model->get_by_id($class_id);
        $selected_group_id = $this->input->get('group_id') ? (int)$this->input->get('group_id') : ($selected_class ? (int)$selected_class->academic_group_id : NULL);
        $attendance_type = ($selected_class && !empty($selected_class->attendance_type)) ? strtolower($selected_class->attendance_type) : ($class_id ? $this->Academic_group_model->get_attendance_type_by_class_id($class_id, $this->school_id) : 'daily');
        $is_period_group = ($attendance_type === 'period');
        $is_higher_sec   = $is_period_group;

        // Sections & default Section A fallback
        $sections           = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $default_section_id = $class_id ? $this->Division_model->get_default_division_id($class_id) : NULL;
        $section_id         = $this->input->get('section_id') ? (int)$this->input->get('section_id') : $default_section_id;
        $selected_section   = $section_id ? $this->Division_model->get_by_id($section_id) : NULL;

        $sections_overview = $this->Attendance_model->get_class_overview($date, $year_id, $class_id, $section_id);
        $current_year      = get_academic_year_record($year_id);

        $this->render('pages/attendance/class_attendance', array(
            'title'             => 'Class Attendance',
            'page_key'          => 'attendance-class',
            'breadcrumb'        => array('Attendance', 'Class Attendance'),
            'groups'            => $this->Academic_group_model->get_all(false, $year_id),
            'selected_group_id' => $selected_group_id,
            'classes'           => $classes,
            'years'             => $years,
            'divisions'         => $sections,
            'sections'          => $sections,
            'division_id'       => $section_id,
            'current_year'      => $current_year,
            'divisions_overview' => $sections_overview,
            'sections_overview' => $sections_overview,
            'selected_class'    => $selected_class,
            'selected_division' => $selected_section,
            'selected_section'  => $selected_section,
            'class_id'          => $class_id,
            'section_id'        => $section_id,
            'year_id'           => $year_id,
            'date'              => $date,
            'attendance_type'   => $attendance_type,
            'is_period_group'   => $is_period_group,
            'is_higher_sec'     => $is_higher_sec,
        ));
    }

    /* =========================================================================
       6. View Attendance (Date Range Report)
       ========================================================================= */
    public function view_attendance()
    {
        $this->require_permission('attendance.view');

        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);

        // Tenant security check: validate academic year belongs to this school
        $selected_year = $this->Academic_year_model->get_by_id($year_id);
        if (!$selected_year || (int)$selected_year->school_id !== (int)$this->school_id) {
            $active_year = $this->Academic_year_model->get_active($this->school_id);
            $year_id = $active_year ? (int)$active_year->academic_year_id : $this->academic_year_id;
        } else if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $current_year = get_academic_year_record($year_id);
        $classes      = $this->Class_model->get_all($year_id);
        $years        = $this->Academic_year_model->get_all($this->school_id);

        // Default Class & tenant security check
        $default_class_id = !empty($classes) ? (int)$classes[0]->class_id : 0;
        $req_class_id     = $this->input->get('class_id') ? (int)$this->input->get('class_id') : $default_class_id;
        $selected_class   = $req_class_id ? $this->Class_model->get_by_id($req_class_id) : NULL;

        if (!$selected_class || (int)$selected_class->school_id !== (int)$this->school_id) {
            $selected_class = !empty($classes) ? $classes[0] : NULL;
            $class_id       = $selected_class ? (int)$selected_class->class_id : 0;
        } else {
            $class_id       = (int)$selected_class->class_id;
        }

        $selected_group_id = $this->input->get('group_id') ? (int)$this->input->get('group_id') : ($selected_class ? (int)$selected_class->academic_group_id : NULL);
        $attendance_type = ($selected_class && !empty($selected_class->attendance_type)) ? strtolower($selected_class->attendance_type) : ($class_id ? $this->Academic_group_model->get_attendance_type_by_class_id($class_id, $this->school_id) : 'daily');
        $is_period_group = ($attendance_type === 'period');
        $is_higher_sec   = $is_period_group;

        // Divisions handling & tenant security check (handles both division_id and section_id)
        $divisions           = $class_id ? $this->Division_model->get_by_class($class_id) : array();
        $sections            = $divisions;
        $default_division_id = $class_id ? (int)$this->Division_model->get_default_division_id($class_id) : NULL;
        $req_division_id     = $this->input->get('division_id') ? (int)$this->input->get('division_id') : ($this->input->get('section_id') ? (int)$this->input->get('section_id') : $default_division_id);
        $selected_division   = $req_division_id ? $this->Division_model->get_by_id($req_division_id) : NULL;

        if (!$selected_division || (int)$selected_division->school_id !== (int)$this->school_id || ((int)$selected_division->class_id !== (int)$class_id && $class_id > 0)) {
            $division_id       = $default_division_id;
            $selected_division = $default_division_id ? $this->Division_model->get_by_id($default_division_id) : NULL;
        } else {
            $division_id       = (int)$selected_division->division_id;
        }
        $section_id         = $division_id;
        $selected_section   = $selected_division;

        // Default Dates (From: 1st of current month normalized; To: today normalized)
        $today_norm     = normalize_date_to_academic_year(NULL, $year_id);
        $first_of_month = date('Y-m-01', strtotime($today_norm));

        $raw_from = $this->input->get('from_date');
        $raw_to   = $this->input->get('to_date');

        $from_date = $raw_from ? normalize_date_to_academic_year($raw_from, $year_id) : $first_of_month;
        $to_date   = $raw_to   ? normalize_date_to_academic_year($raw_to, $year_id)   : $today_norm;

        // Validation for date range
        $date_warning = NULL;
        if (strtotime($from_date) > strtotime($to_date)) {
            $date_warning = 'From Date cannot be later than To Date. Please select a valid date range.';
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        // Fetch report data with explicit school context
        $report_data = $this->Attendance_model->get_range_attendance_report($from_date, $to_date, $class_id, $section_id, $year_id, $this->school_id);

        $this->render('pages/attendance/view_attendance', array(
            'title'             => 'View Attendance',
            'page_key'          => 'attendance-class',
            'breadcrumb'        => array('Attendance', 'Class Attendance', 'View Attendance'),
            'groups'            => $this->Academic_group_model->get_all(false, $year_id),
            'selected_group_id' => $selected_group_id,
            'years'             => $years,
            'classes'           => $classes,
            'divisions'         => $sections,
            'sections'          => $sections,
            'division_id'       => $division_id,
            'current_year'      => $current_year,
            'selected_class'    => $selected_class,
            'selected_division' => $selected_division,
            'selected_section'  => $selected_section,
            'academic_year_id'  => $year_id,
            'class_id'          => $class_id,
            'section_id'        => $section_id,
            'from_date'         => $from_date,
            'to_date'           => $to_date,
            'date_warning'      => $date_warning,
            'attendance_type'   => $attendance_type,
            'is_period_group'   => $is_period_group,
            'is_higher_sec'     => $is_higher_sec,
            'summary'           => $report_data->summary,
            'students'          => $report_data->students,
            'working_info'      => $report_data->working_info,
        ));
    }

    /* =========================================================================
       7. Individual Student Attendance Details
       ========================================================================= */
    public function student_attendance($student_id = NULL)
    {
        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }
        $student_id = (int)$student_id;

        if (!$student_id) {
            $this->session->set_flashdata('error', 'Please select a student to view attendance details.');
            redirect('student-attendance/view');
            return;
        }

        $this->load->model('Student_model');
        $student = $this->Student_model->get_by_id($student_id);

        if (!$student) {
            $this->session->set_flashdata('error', 'Student record not found.');
            redirect('student-attendance/view');
            return;
        }

        // Academic Year resolution
        $year_id = (int)($this->input->get('academic_year_id') ?: ($student->academic_year_id ?: $this->academic_year_id));
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $current_year = get_academic_year_record($year_id);
        $years        = $this->Academic_year_model->get_all();

        // Period filter mode: 'academic_year' (default) vs 'custom'
        $period_mode = $this->input->get('period_mode') ?: 'academic_year';
        $today_norm  = normalize_date_to_academic_year(NULL, $year_id);

        if ($period_mode === 'custom' && ($this->input->get('from_date') || $this->input->get('to_date'))) {
            $raw_from = $this->input->get('from_date');
            $raw_to   = $this->input->get('to_date');
            $from_date = $raw_from ? normalize_date_to_academic_year($raw_from, $year_id) : ($current_year ? $current_year->start_date : date('Y-01-01'));
            $to_date   = $raw_to   ? normalize_date_to_academic_year($raw_to, $year_id)   : $today_norm;
        } else {
            // Default: Academic Year Attendance
            $period_mode = 'academic_year';
            $from_date = ($current_year && !empty($current_year->start_date)) ? $current_year->start_date : date('Y-01-01');
            $to_date   = $today_norm;
        }

        // Validate Date Range
        $date_warning = NULL;
        if (strtotime($from_date) > strtotime($to_date)) {
            $date_warning = 'From Date cannot be later than To Date. Please select a valid date range.';
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        // Fetch detailed attendance
        $attendance_data = $this->Attendance_model->get_individual_student_attendance($student_id, $from_date, $to_date, $year_id);
        $is_higher_sec   = is_higher_secondary_class($student->class_id);

        $this->render('pages/attendance/student_attendance', array(
            'title'                 => 'Student Attendance Details',
            'page_key'              => 'attendance-class',
            'breadcrumb'            => array('Attendance', 'Class Attendance', 'Student Details'),
            'student'               => $student,
            'years'                 => $years,
            'current_year'          => $current_year,
            'academic_year_id'      => $year_id,
            'period_mode'           => $period_mode,
            'from_date'             => $from_date,
            'to_date'               => $to_date,
            'date_warning'          => $date_warning,
            'is_higher_sec'         => $is_higher_sec,
            'overall_summary'       => $attendance_data ? $attendance_data->overall_summary : NULL,
            'subject_wise'          => $attendance_data ? $attendance_data->subject_wise : array(),
            'has_subject_records'   => $attendance_data ? $attendance_data->has_subject_records : FALSE,
            'daily_records'         => $attendance_data ? $attendance_data->daily_records : array(),
            'period_records'        => $attendance_data ? $attendance_data->period_records : array(),
            'working_info'          => $attendance_data ? $attendance_data->working_info : NULL,
        ));
    }

    /* =========================================================================
       6. Section Attendance
       ========================================================================= */
    public function section_attendance()
    {
        $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        redirect('attendance/class_attendance' . $query);
    }

    public function history()
    {
        $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        redirect('attendance/class_attendance' . $query);
    }

    public function tracking()
    {
        $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        redirect('attendance/class_attendance' . $query);
    }

    /* =========================================================================
       9. Attendance Calendar
       ========================================================================= */
    public function calendar()
    {
        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $current_year = get_academic_year_record($year_id);
        $month        = (int)($this->input->get('month') ?: date('n'));
        $year         = (int)($this->input->get('year') ?: ($current_year ? date('Y', strtotime($current_year->start_date)) : date('Y')));
        $class_id     = $this->input->get('class_id') ?: NULL;
        $division_id  = $this->input->get('division_id') ?: ($this->input->get('section_id') ?: NULL);
        $section_id   = $division_id;
        $student_id   = $this->input->get('student_id') ?: NULL;
        $type         = $this->input->get('attendance_type') ?: 'Daily';

        if ($student_id && (!$class_id || !$division_id)) {
            $st_rec = $this->Student_model->get_by_id($student_id);
            if ($st_rec) {
                if (!$class_id && !empty($st_rec->class_id)) {
                    $class_id = $st_rec->class_id;
                }
                if (!$division_id && !empty($st_rec->division_id)) {
                    $division_id = $st_rec->division_id;
                    $section_id  = $division_id;
                }
            }
        }

        $matrix   = $this->Attendance_model->get_calendar_data($year, $month, $class_id, $division_id, $student_id, $type, $year_id);
        $classes  = $this->Class_model->get_all($year_id);
        $sections = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $student_filters = array('academic_year_id' => $year_id, 'status' => 1);
        if ($class_id) $student_filters['class_id'] = $class_id;
        if ($division_id) $student_filters['division_id'] = $division_id;
        $students = $class_id ? $this->Student_model->get_all($student_filters) : array();

        $selected_date = $this->input->get('date') ? normalize_date_to_academic_year($this->input->get('date'), $year_id) : NULL;
        $day_details = array();
        if ($selected_date) {
            $day_details = $this->Attendance_model->get_date_attendance_details($selected_date, $class_id, $division_id, $student_id, $type, $year_id);
        }

        $this->render('pages/attendance/calendar', array(
            'title'         => 'Attendance Calendar',
            'page_key'      => 'attendance-calendar',
            'breadcrumb'    => array('Attendance', 'Attendance Calendar'),
            'matrix'        => $matrix,
            'month'         => $month,
            'year'          => $year,
            'class_id'      => $class_id,
            'division_id'   => $division_id,
            'section_id'    => $division_id,
            'student_id'    => $student_id,
            'type'          => $type,
            'classes'       => $classes,
            'divisions'     => $sections,
            'sections'      => $sections,
            'students'      => $students,
            'current_year'  => $current_year,
            'selected_date' => $selected_date,
            'day_details'   => $day_details,
        ));
    }

    /* =========================================================================
       10. Attendance Reports
       ========================================================================= */
    public function reports()
    {
        $year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if ($this->input->get('academic_year_id')) {
            set_current_academic_year($year_id);
            $this->academic_year_id = $year_id;
        }

        $report_type = $this->input->get('type') ?: 'class_summary'; // class_summary, daily, student, section, monthly, period
        $class_id    = $this->input->get('class_id') ?: NULL;
        $section_id  = $this->input->get('section_id') ?: NULL;
        $student_id  = $this->input->get('student_id') ?: NULL;
        $date        = normalize_date_to_academic_year($this->input->get('date'), $year_id);
        $from_date   = $this->input->get('from_date') ? normalize_date_to_academic_year($this->input->get('from_date'), $year_id) : normalize_date_to_academic_year(NULL, $year_id);
        $to_date     = $this->input->get('to_date') ? normalize_date_to_academic_year($this->input->get('to_date'), $year_id) : normalize_date_to_academic_year(NULL, $year_id);
        $month       = (int)($this->input->get('month') ?: date('n'));
        $year        = (int)($this->input->get('year') ?: date('Y'));

        // If class is LKG-10 and period report is requested, revert to class_summary
        if ($class_id && !is_higher_secondary_class($class_id) && $report_type === 'period') {
            $report_type = 'class_summary';
        }

        $filters = array(
            'academic_year_id' => $year_id,
            'class_id'         => $class_id,
            'section_id'       => $section_id,
            'student_id'       => $student_id,
            'date'             => $date,
            'from_date'        => $from_date,
            'to_date'          => $to_date,
            'month'            => $month,
            'year'             => $year,
        );

        $data_results = array();

        if ($report_type === 'daily') {
            $data_results = $this->Attendance_model->get_daily_sheet($date, $class_id, $section_id, $year_id);
        } elseif ($report_type === 'student') {
            $data_results = $this->Attendance_model->get_student_report($filters);
        } elseif ($report_type === 'section') {
            $data_results = $this->Attendance_model->get_reports_summary($year_id, $class_id);
        } elseif ($report_type === 'monthly') {
            $data_results = $this->Attendance_model->get_monthly_report($filters);
        } elseif ($report_type === 'period') {
            $data_results = $this->Attendance_model->get_period_wise_report($filters);
        } else {
            // Default class overview
            $data_results = $this->Attendance_model->get_reports_summary($year_id, $class_id);
        }

        // Export CSV if requested
        if ($this->input->get('export') === 'csv') {
            $this->_export_reports_csv($report_type, $data_results);
            return;
        }

        $current_year = get_academic_year_record($year_id);
        $classes  = $this->Class_model->get_all($year_id);
        $sections = $class_id ? $this->Division_model->get_by_class($class_id) : $this->Division_model->get_all();
        $students = ($class_id && $section_id) ? $this->Student_model->get_by_section($section_id) : array();
        $is_higher_sec = $class_id ? is_higher_secondary_class($class_id) : FALSE;

        $this->render('pages/attendance/reports', array(
            'title'         => 'Attendance Reports',
            'page_key'      => 'attendance-reports',
            'breadcrumb'    => array('Attendance', 'Attendance Reports'),
            'report_type'   => $report_type,
            'reports'       => $data_results,
            'classes'       => $classes,
            'divisions'     => $sections,
            'sections'      => $sections,
            'division_id'     => $section_id,
            'students'      => $students,
            'current_year'  => $current_year,
            'year_id'       => $year_id,
            'class_id'      => $class_id,
            'is_higher_sec' => $is_higher_sec,
            'filters'       => $filters,
        ));
    }

    private function _export_reports_csv($report_type, $data)
    {
        $filename = 'Attendance_Report_' . $report_type . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');

        if ($report_type === 'student' || $report_type === 'monthly') {
            $is_higher_sec = !empty($data) && isset($data[0]->class_name) && is_higher_secondary_class($data[0]->class_name);
            if ($is_higher_sec) {
                fputcsv($output, array('Admission No', 'Roll No', 'Student Name', 'Class', 'Division', 'Present', 'Half Day', 'Absent', 'Late Coming', 'Total Classes', 'Attendance %'));
                foreach ($data as $r) {
                    fputcsv($output, array(
                        $r->admission_number,
                        $r->roll_number,
                        $r->first_name . ' ' . $r->last_name,
                        $r->class_name,
                        ($r->division_name ?? $r->section_name),
                        $r->present_count ?: 0,
                        $r->half_day_count ?: 0,
                        $r->absent_count ?: 0,
                        $r->late_count ?: 0,
                        $r->total_days ?: 0,
                        $r->percentage . '%'
                    ));
                }
            } else {
                fputcsv($output, array('Admission No', 'Roll No', 'Student Name', 'Class', 'Division', 'Present Days', 'Half Day', 'Absent Days', 'Total Days', 'Attendance %'));
                foreach ($data as $r) {
                    fputcsv($output, array(
                        $r->admission_number,
                        $r->roll_number,
                        $r->first_name . ' ' . $r->last_name,
                        $r->class_name,
                        ($r->division_name ?? $r->section_name),
                        $r->present_count ?: 0,
                        $r->half_day_count ?: 0,
                        $r->absent_count ?: 0,
                        $r->total_days ?: 0,
                        $r->percentage . '%'
                    ));
                }
            }
        } elseif ($report_type === 'period') {
            fputcsv($output, array('Period #', 'Period Name', 'Time', 'Present Count', 'Half Day', 'Absent Count', 'Late Coming', 'Total', 'Attendance %'));
            foreach ($data as $r) {
                fputcsv($output, array(
                    $r->period_number,
                    $r->period_name,
                    $r->start_time . ' - ' . $r->end_time,
                    $r->present_count ?: 0,
                    $r->half_day_count ?: 0,
                    $r->absent_count ?: 0,
                    $r->late_count ?: 0,
                    $r->total_count ?: 0,
                    $r->percentage . '%'
                ));
            }
        } else {
            fputcsv($output, array('Class', 'Division', 'Present', 'Half Day', 'Absent', 'Late Coming', 'Attendance %'));
            foreach ($data as $r) {
                $is_hs = isset($r->class_name) && is_higher_secondary_class($r->class_name);
                fputcsv($output, array(
                    isset($r->class_name) ? $r->class_name : '',
                    isset($r->section_name) ? $r->section_name : '',
                    isset($r->present_count) ? $r->present_count : 0,
                    isset($r->half_day_count) ? $r->half_day_count : 0,
                    isset($r->absent_count) ? $r->absent_count : 0,
                    $is_hs ? (isset($r->late_count) ? $r->late_count : 0) : '-',
                    (isset($r->percentage) ? $r->percentage : 0) . '%'
                ));
            }
        }
        fclose($output);
        exit;
    }

    /* =========================================================================
       11. Parent Notification Management
       ========================================================================= */
    public function notifications()
    {
        if ($this->input->method() === 'post') {
            $this->require_permission('attendance.mark');
            $action = $this->input->post('action');
            if ($action === 'update_status') {
                $id     = (int)$this->input->post('notification_id');
                $status = $this->input->post('status');
                $this->Attendance_notification_model->update_status($id, $status);
                $this->session->set_flashdata('success', 'Notification status updated.');
                redirect('attendance/notifications');
                return;
            }
            if ($action === 'delete') {
                $id = (int)$this->input->post('notification_id');
                $this->Attendance_notification_model->delete($id);
                $this->session->set_flashdata('success', 'Notification removed.');
                redirect('attendance/notifications');
                return;
            }
        }

        $filters = array(
            'status'            => $this->input->get('status') ?: 'Pending',
            'notification_type' => $this->input->get('type') ?: NULL,
            'class_id'          => $this->input->get('class_id') ?: NULL,
            'date'              => $this->input->get('date') ?: NULL,
            'search'            => $this->input->get('search') ?: NULL,
        );

        $notifications = $this->Attendance_notification_model->get_all($filters, 100);
        $classes       = $this->Class_model->get_all();

        $this->render('pages/attendance/notifications', array(
            'title'         => 'Parent Notifications',
            'page_key'      => 'attendance-notifications',
            'breadcrumb'    => array('Attendance', 'Parent Notifications'),
            'notifications' => $notifications,
            'classes'       => $classes,
            'filters'       => $filters,
        ));
    }

    /* =========================================================================
       12. Notification History
       ========================================================================= */
    public function notification_history()
    {
        $filters = array(
            'status'            => $this->input->get('status') ?: NULL,
            'notification_type' => $this->input->get('type') ?: NULL,
            'class_id'          => $this->input->get('class_id') ?: NULL,
            'from_date'         => $this->input->get('from_date') ?: date('Y-m-01'),
            'to_date'           => $this->input->get('to_date') ?: date('Y-m-d'),
            'search'            => $this->input->get('search') ?: NULL,
        );

        $notifications = $this->Attendance_notification_model->get_all($filters, 200);
        $classes       = $this->Class_model->get_all();

        $this->render('pages/attendance/notification_history', array(
            'title'         => 'Notification History',
            'page_key'      => 'attendance-notification-history',
            'breadcrumb'    => array('Attendance', 'Notification History'),
            'notifications' => $notifications,
            'classes'       => $classes,
            'filters'       => $filters,
        ));
    }

    /* =========================================================================
       13. Attendance Settings
       ========================================================================= */
    public function settings()
    {
        $this->require_permission('attendance.edit');

        if ($this->input->method() === 'post') {
            $data = array(
                'enable_present'              => $this->input->post('enable_present') ? 1 : 0,
                'enable_absent'               => $this->input->post('enable_absent') ? 1 : 0,
                'enable_late'                 => $this->input->post('enable_late') ? 1 : 0,
                'enable_excused'              => $this->input->post('enable_excused') ? 1 : 0,
                'enable_period_attendance'    => $this->input->post('enable_period_attendance') ? 1 : 0,
                'enable_absent_notification'  => $this->input->post('enable_absent_notification') ? 1 : 0,
                'enable_late_notification'    => $this->input->post('enable_late_notification') ? 1 : 0,
                'enable_summary_notification' => $this->input->post('enable_summary_notification') ? 1 : 0,
                'absent_template'             => trim($this->input->post('absent_template')),
                'late_template'               => trim($this->input->post('late_template')),
                'excused_template'            => trim($this->input->post('excused_template')),
                'summary_template'            => trim($this->input->post('summary_template')),
                'notification_timing'         => $this->input->post('notification_timing') ?: 'On Marking',
            );

            $this->Attendance_setting_model->update_settings($data);
            $this->session->set_flashdata('success', 'Attendance settings updated successfully.');
            redirect('attendance/settings');
            return;
        }

        $settings = $this->Attendance_setting_model->get_settings();
        $periods  = $this->Period_model->get_all(FALSE);

        $this->render('pages/attendance/settings', array(
            'title'      => 'Attendance Settings',
            'page_key'   => 'attendance-settings',
            'breadcrumb' => array('Attendance', 'Attendance Settings'),
            'settings'   => $settings,
            'periods'    => $periods,
        ));
    }
}
