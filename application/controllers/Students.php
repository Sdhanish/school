<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Student_academic_model');
        $this->load->model('Student_document_model');
        $this->load->model('Staff_model');
        $this->load->model('Academic_group_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Id_card_model');
        $this->load->model('Setting_model');
        $this->load->library('phone_validator');
    }

    /* =========================================================================
       1. Student Management Overview
       ========================================================================= */
    public function index()
    {
        $this->overview();
    }

    public function overview()
    {
        $this->require_permission('students.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $stats = $this->Student_model->get_dashboard_stats($year_id, (int)$this->school_id);

        $this->render('pages/students/overview', array(
            'title'      => 'Student Management Overview',
            'page_key'   => 'students',
            'breadcrumb' => array('Student Management', 'Overview'),
            'stats'      => $stats,
        ));
    }

    /**
     * All Students (Class & Academic Year Wise) main view.
     */
    public function all_students()
    {
        $this->require_permission('students.view');

        $years = $this->Academic_year_model->get_all((int)$this->school_id);
        $selected_year = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        if (!$selected_year) {
            $selected_year = (int)(get_active_academic_year_id(TRUE, (int)$this->school_id) ?: 0);
        }

        $status_param = $this->input->get('status');
        $status_for_counts = ($status_param === 'All') ? 'All' : (($status_param !== NULL && $status_param !== '') ? (int)$status_param : 1);

        $classes_with_counts = $this->Student_model->get_classes_with_student_count($selected_year, $status_for_counts, (int)$this->school_id);

        $raw_class = $this->input->get('class_id');
        if ($raw_class === 'all' || $raw_class === '0' || $raw_class === '') {
            $selected_class = NULL;
        } elseif ($raw_class !== NULL) {
            $selected_class = (int)$raw_class;
        } else {
            $selected_class = NULL;
        }

        $sections = $this->Division_model->get_all(NULL, (int)$this->school_id);
        $groups   = $this->Academic_group_model->get_all(false, $selected_year);

        $this->render('pages/students/all_students', array(
            'title'               => 'All Students',
            'page_key'            => 'all-students',
            'breadcrumb'          => array('Student Management', 'All Students'),
            'years'               => $years,
            'groups'              => $groups,
            'selected_year'       => $selected_year,
            'selected_class'      => $selected_class,
            'classes_with_counts' => $classes_with_counts,
            'sections'            => $sections,
            'divisions'           => $sections,
        ));
    }

    /**
     * AJAX endpoint for loading class list and student counts when academic year or status filter changes.
     */
    public function class_counts_ajax()
    {
        $this->require_permission('students.view');
        $academic_year_id = (int)$this->input->get_post('academic_year_id');
        if (!$academic_year_id) {
            $academic_year_id = $this->academic_year_id;
        }

        $status_raw = $this->input->get_post('status');
        $status = ($status_raw !== NULL && $status_raw !== '' && $status_raw !== 'All' && is_numeric($status_raw)) ? (int)$status_raw : ($status_raw === 'All' ? 'All' : 1);

        $classes = $this->Student_model->get_classes_with_student_count($academic_year_id, $status, (int)$this->school_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'academic_year_id'=> $academic_year_id,
                'classes'         => $classes,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX DataTables endpoint for All Students class & academic year filtered list.
     */
    public function all_students_ajax()
    {
        $this->require_permission('students.view');

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length') ?: 10;
        $order  = $this->input->post('order');
        $search = $this->input->post('search');

        $order_col_idx = isset($order[0]['column']) ? (int)$order[0]['column'] : 1;
        $order_dir     = (isset($order[0]['dir']) && strtolower($order[0]['dir']) === 'desc') ? 'DESC' : 'ASC';
        $search_val    = (isset($search['value']) && is_string($search['value'])) ? trim($search['value']) : '';

        $column_map = [
            0 => 'st.student_id',
            1 => 'st.admission_number',
            2 => 'st.roll_number',
            3 => 'c.class_name',
            4 => 'sec.section_name',
            5 => 'st.date_of_birth',
            6 => 'st.gender',
            7 => 'st.guardian_name',
            8 => 'st.guardian_phone',
            9 => 'st.status',
            10 => 'st.student_id'
        ];
        $order_col = isset($column_map[$order_col_idx]) ? $column_map[$order_col_idx] : 'st.student_id';

        $academic_year_id_raw = $this->input->post('academic_year_id') ?: $this->input->get('academic_year_id');
        $academic_year_id = (!empty($academic_year_id_raw) && is_numeric($academic_year_id_raw)) ? (int)$academic_year_id_raw : (int)$this->academic_year_id;

        $class_id_raw = $this->input->post('class_id');
        $class_id = (!empty($class_id_raw) && is_numeric($class_id_raw) && (int)$class_id_raw > 0) ? (int)$class_id_raw : NULL;

        $division_id_raw = $this->input->post('division_id') ?: $this->input->post('section_id');
        $division_id = (!empty($division_id_raw) && is_numeric($division_id_raw) && (int)$division_id_raw > 0) ? (int)$division_id_raw : NULL;
        $section_id  = $division_id;

        $status_raw = $this->input->post('status');
        $status = ($status_raw !== NULL && $status_raw !== '' && $status_raw !== 'All' && is_numeric($status_raw)) ? (int)$status_raw : NULL;

        $gender_raw = $this->input->post('gender') ?: $this->input->get('gender');
        $gender = (!empty($gender_raw) && in_array($gender_raw, array('Male', 'Female', 'Other'))) ? $gender_raw : NULL;

        $custom_search = $this->input->post('custom_search');
        $effective_search = !empty($custom_search) ? trim($custom_search) : $search_val;

        $filters = array(
            'school_id'        => (int)$this->school_id,
            'academic_year_id' => $academic_year_id,
            'class_id'         => $class_id,
            'division_id'      => $division_id,
            'section_id'       => $section_id,
            'status'           => $status,
            'gender'           => $gender,
            'search'           => $effective_search,
        );

        $total_filters = array(
            'school_id'        => (int)$this->school_id,
            'academic_year_id' => $academic_year_id,
            'class_id'         => $class_id,
            'status'           => $status,
            'gender'           => $gender,
        );

        $records_total    = $this->Student_model->count_all_students($total_filters);
        $records_filtered = $this->Student_model->count_all_students($filters);
        $students         = $this->Student_model->get_all_students_paginated($filters, $length, $start, $order_col, $order_dir);

        $data = array();
        foreach ($students as $st) {
            $fullName = trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name);
            $nameParts = explode(' ', $fullName);
            $initials = '';
            foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
            $initials = substr($initials, 0, 2) ?: 'ST';

            // Photo Avatar (40px x 40px with object-fit: cover and robust server + client fallback)
            $hasPhoto = !empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo);
            if ($hasPhoto) {
                $photoUrl = base_url('uploads/students/' . $st->photo);
                $fallbackJs = "this.onerror=null; this.parentElement.className='w-[40px] h-[40px] rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs'; this.parentElement.innerHTML='" . html_escape($initials) . "';";
                $photoHtml = '<div class="w-[40px] h-[40px] rounded-xl border border-slate-200 overflow-hidden shrink-0 shadow-2xs bg-slate-100 flex items-center justify-center">' .
                    '<img src="' . $photoUrl . '" alt="' . html_escape($fullName) . '" class="w-full h-full object-cover" onerror="' . $fallbackJs . '"/>' .
                '</div>';
            } else {
                $photoHtml = '<div class="w-[40px] h-[40px] rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">' .
                    html_escape($initials) .
                '</div>';
            }

            // Admission Number & Roll
            $admHtml = '<span class="font-bold text-emerald-800 font-mono text-xs">' . html_escape($st->admission_number) . '</span>';

            // Student Name + Profile Link
            $nameHtml = '<div class="flex items-center gap-3">' .
                $photoHtml .
                '<div class="min-w-0">' .
                    '<a href="' . site_url('students/profile/' . $st->student_id) . '" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors text-xs truncate block">' . html_escape($fullName) . '</a>' .
                    '<span class="text-[11px] text-slate-500 font-mono block">Adm: ' . html_escape($st->admission_number) . '</span>' .
                '</div>' .
            '</div>';

            $rollHtml = !empty($st->roll_number) ? '<span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">' . html_escape($st->roll_number) . '</span>' : '<span class="text-slate-400 font-mono text-xs">-</span>';

            $classHtml = '<span class="text-xs font-semibold text-slate-800">' . html_escape($st->class_name ?: 'Grade 10') . '</span>';
            $sectionHtml = !empty($st->section_name) ? '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">' . html_escape($st->section_name) . '</span>' : '<span class="text-slate-400 text-xs">-</span>';

            $dobFormatted = !empty($st->date_of_birth) ? date('d-m-Y', strtotime($st->date_of_birth)) : '<span class="text-slate-400">-</span>';
            $genderHtml = '<span class="text-xs text-slate-700">' . html_escape($st->gender ?: 'N/A') . '</span>';

            $guardianName = !empty($st->guardian_name) ? $st->guardian_name : '—';
            $guardianPhone = !empty($st->guardian_phone) ? $st->guardian_phone : '—';

            $guardianHtml = '<div class="text-xs">' .
                '<div class="font-bold text-slate-800 truncate">' . html_escape($guardianName) . '</div>' .
                '<div class="text-[11px] text-slate-500 font-mono mt-0.5">' . html_escape($guardianPhone) . '</div>' .
            '</div>';

            $contactHtml = '<span class="font-mono text-xs text-slate-700">' . html_escape($guardianPhone) . '</span>';

            $statusBadge = ($st->status == 1)
                ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active</span>'
                : '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive</span>';

            // Actions Menu
            $actionsHtml = '<div class="flex items-center justify-end gap-1.5">' .
                '<a href="' . site_url('students/profile/' . $st->student_id) . '" data-testid="btn-view-profile" data-testid-student="' . $st->student_id . '" title="View Profile" class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition-colors shadow-2xs">' .
                    '<span class="material-symbols-outlined text-[17px]">visibility</span>' .
                '</a>' .
                '<a href="' . site_url('students/edit/' . $st->student_id) . '" data-testid="btn-edit-student" title="Edit Student" class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-700 transition-colors shadow-2xs">' .
                    '<span class="material-symbols-outlined text-[17px]">edit</span>' .
                '</a>' .
                '<a href="' . site_url('students/id_cards?student_id=' . $st->student_id) . '" data-testid="btn-id-card" title="Generate ID Card" class="p-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition-colors shadow-2xs">' .
                    '<span class="material-symbols-outlined text-[17px]">badge</span>' .
                '</a>' .
            '</div>';

            $data[] = array(
                $nameHtml,
                $admHtml,
                $rollHtml,
                $classHtml,
                $sectionHtml,
                $dobFormatted,
                $genderHtml,
                $guardianHtml,
                $contactHtml,
                $statusBadge,
                $actionsHtml
            );
        }

        $output = array(
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $data,
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    public function list_students()
    {
        $this->require_permission('students.view');
        $filters = array(
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $this->input->get('class_id'),
            'section_id'       => $this->input->get('section_id'),
            'gender'           => $this->input->get('gender'),
            'status'           => $this->input->get('status'),
            'search'           => $this->input->get('search'),
        );

        $students = $this->Student_model->get_all($filters);
        $classes  = $this->Class_model->get_all($filters['academic_year_id']);
        $sections = $this->Section_model->get_all();
        $years    = $this->Academic_year_model->get_all();

        $this->render('pages/students/index', array(
            'title'    => 'Student Directory',
            'page_key' => 'student-directory',
            'students' => $students,
            'classes'  => $classes,
            'sections' => $sections,
            'years'    => $years,
            'filters'  => $filters,
        ));
    }

    public function ajax_list()
    {
        $this->require_permission('students.view');

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $order  = $this->input->post('order');
        $search = $this->input->post('search');

        $order_col_idx = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir     = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';
        $search_val    = isset($search['value']) ? trim($search['value']) : '';

        $filters = array(
            'academic_year_id' => $this->input->post('academic_year_id') ?: ($this->input->get('academic_year_id') ?: $this->academic_year_id),
            'class_id'         => $this->input->post('class_id') ?: $this->input->get('class_id'),
            'division_id'      => $this->input->post('division_id') ?: ($this->input->get('division_id') ?: ($this->input->post('section_id') ?: $this->input->get('section_id'))),
            'section_id'       => $this->input->post('division_id') ?: ($this->input->get('division_id') ?: ($this->input->post('section_id') ?: $this->input->get('section_id'))),
            'gender'           => $this->input->post('gender') ?: $this->input->get('gender'),
            'status'           => $this->input->post('status') !== NULL ? $this->input->post('status') : $this->input->get('status'),
            'search'           => $search_val,
        );

        $records_total    = $this->Student_model->get_datatables_count_all($filters);
        $records_filtered = $this->Student_model->count_filtered($filters);
        $students         = $this->Student_model->get_datatables_data($filters, $length, $start, $order_col_idx, $order_dir);

        $data = array();
        foreach ($students as $st) {
            $nameParts = explode(' ', trim($st->first_name . ' ' . $st->last_name));
            $initials = '';
            foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
            if (strlen($initials) > 2) $initials = substr($initials, 0, 2);

            $statusBadge = ($st->status == 1)
                ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>'
                : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive</span>';

            $classDisplay = trim(($st->class_name ?: '') . ' ' . ($st->section_name ?: ''));

            $admissionCol = '<a href="' . site_url('students/profile/' . $st->student_id) . '" class="text-primary font-medium hover:underline">' . html_escape($st->admission_number) . '</a>';
            if (!empty($st->roll_number)) {
                $admissionCol .= ' <span class="text-[11px] text-on-surface-variant ml-1 font-mono">#' . html_escape($st->roll_number) . '</span>';
            }

            $hasPhoto = !empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo);
            if ($hasPhoto) {
                $photoCol = '<div class="w-9 h-9 rounded-xl border border-slate-200 overflow-hidden shrink-0 shadow-2xs bg-slate-100 flex items-center justify-center">' .
                    '<img src="' . base_url('uploads/students/' . $st->photo) . '" alt="' . html_escape($st->first_name) . '" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.className=\'w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center text-[11px] font-semibold shrink-0\'; this.parentElement.innerHTML=\'' . html_escape($initials) . '\';"/>' .
                '</div>';
            } else {
                $photoCol = '<div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center text-[11px] font-semibold shrink-0">' . html_escape($initials) . '</div>';
            }

            $studentCol = '<div class="flex items-center gap-2.5">' .
                $photoCol .
                '<div>' .
                    '<div class="font-medium text-on-surface">' . html_escape($st->first_name . ' ' . $st->last_name) . '</div>' .
                    '<div class="text-[12px] text-on-surface-variant">' . html_escape($classDisplay . ' · ' . $st->gender) . '</div>' .
                '</div>' .
            '</div>';

            $actionsCol = '<div class="flex items-center justify-end gap-1.5">' .
                '<a href="' . site_url('students/profile/' . $st->student_id) . '" title="View Profile" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">visibility</span></a>' .
                '<a href="' . site_url('students/edit/' . $st->student_id) . '" title="Edit Student" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span></a>' .
                '<a href="' . site_url('students/id_cards?student_id=' . $st->student_id) . '" title="ID Card" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">badge</span></a>' .
            '</div>';

            $data[] = array(
                $admissionCol,
                $studentCol,
                html_escape($classDisplay),
                html_escape($st->gender),
                school_date($st->date_of_birth),
                html_escape($st->guardian_name ?: '—'),
                html_escape($st->guardian_phone ?: '—'),
                $statusBadge,
                $actionsCol
            );
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => $records_total,
            "recordsFiltered" => $records_filtered,
            "data"            => $data,
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    /* =========================================================================
       2. Student Registration Wizard
       ========================================================================= */

    /**
     * Alias — /students/register maps here
     */
    public function register()
    {
        // Fresh registration entry point: clear any stale wizard state
        $this->session->unset_userdata('student_registration_wizard');
        $this->add();
    }

    /**
     * Main wizard dispatcher.
     *
     * GET  students/add          → Step 1 (Student Details)
     * GET  students/add?step=2   → Step 2 (Academic Details) — guarded
     * GET  students/add?step=3   → Step 3 (Parent / Guardian) — guarded
     *
     * The wizard stores intermediate data in the CI session under the key
     * 'student_registration_wizard'.  No database rows are written until the
     * final Save Student action (wizard_save).
     */
    public function add()
    {
        $this->require_permission('students.create');

        $step    = (int)($this->input->get('step') ?: 1);
        $wizard  = $this->session->userdata('student_registration_wizard') ?: array();

        // Reset wizard session if explicit reset requested
        if ($this->input->get('reset')) {
            $this->session->unset_userdata('student_registration_wizard');
            redirect('students/add');
            return;
        }

        // If visiting step 1 directly or if wizard was marked submitted, reset submitted state
        if ($step === 1 && !empty($wizard['submitted'])) {
            $wizard['submitted'] = FALSE;
            $wizard['wizard_token'] = sha1(uniqid('wiz_', TRUE));
            $this->session->set_userdata('student_registration_wizard', $wizard);
        }

        // ── Guard: disallow skipping ahead ────────────────────────────────────
        if ($step === 2 && empty($wizard['student_details'])) {
            redirect('students/add');
            return;
        }
        if ($step === 3 && (empty($wizard['student_details']) || empty($wizard['academic_details']))) {
            if (empty($wizard['student_details'])) {
                redirect('students/add');
            } else {
                redirect('students/add?step=2');
            }
            return;
        }
        if ($step < 1 || $step > 3) {
            redirect('students/add');
            return;
        }

        // ── Initialise a wizard token on first visit ───────────────────────────
        if (empty($wizard['wizard_token'])) {
            $wizard['wizard_token'] = sha1(uniqid('wiz_', TRUE));
            $wizard['submitted'] = FALSE;
            $this->session->set_userdata('student_registration_wizard', $wizard);
        }

        $groups        = $this->Academic_group_model->get_all();
        $classes       = $this->Class_model->get_all($this->academic_year_id);
        $sections      = $this->Section_model->get_all();
        $academic_years = $this->Academic_year_model->get_all();

        $this->render('pages/students/add', array(
            'title'          => 'Student Registration',
            'page_key'       => 'student-registration',
            'breadcrumb'     => array('Student Management', 'Student Registration'),
            'groups'         => $groups,
            'classes'        => $classes,
            'sections'       => $sections,
            'divisions'      => $sections,
            'academic_years' => $academic_years,
            'current_step'   => $step,
            'wizard'         => $wizard,
        ));
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard Photo Upload — multipart file-only endpoint for Student Image
       POST students/wizard_photo_upload
       Allowed: JPG, JPEG, PNG (max 10MB)
       Returns JSON { success, temp_path, display_name, preview_url, error }
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_photo_upload()
    {
        try {
            $this->require_permission('students.create');

            if ($this->input->method() !== 'post') {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Invalid request.')));
                return;
            }

            $max_size = 3 * 1024 * 1024; // 3 MB
            $allowed_ext = array('jpg', 'jpeg', 'png', 'webp');
            $allowed_mimes = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp');
            $allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
            if (defined('IMAGETYPE_WEBP')) {
                $allowed_types[] = IMAGETYPE_WEBP;
            }

            $temp_dir = FCPATH . 'uploads/photo_temp/';
            if (!is_dir($temp_dir)) {
                @mkdir($temp_dir, 0775, TRUE);
            }

            // 1. Check if cropped image payload is submitted (Base64 data URL from Cropper)
            $croppedData = $this->input->post('cropped_image_data');
            if (!empty($croppedData)) {
                if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,([A-Za-z0-9+\/=\r\n]+)$/i', $croppedData, $matches)) {
                    $ext = strtolower($matches[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $decoded = base64_decode($matches[2]);

                    if ($decoded === FALSE || strlen($decoded) < 50) {
                        $this->output->set_content_type('application/json')
                                     ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                        return;
                    }

                    if (strlen($decoded) > $max_size) {
                        $this->output->set_content_type('application/json')
                                     ->set_output(json_encode(array('success' => FALSE, 'error' => 'Image size must not exceed 3 MB.')));
                        return;
                    }

                    $img_info = @getimagesizefromstring($decoded);
                    if ($img_info === FALSE) {
                        $this->output->set_content_type('application/json')
                                     ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                        return;
                    }

                    $detected_type = isset($img_info[2]) ? $img_info[2] : 0;
                    $detected_mime = isset($img_info['mime']) ? strtolower($img_info['mime']) : '';
                    if (!in_array($detected_type, $allowed_types, TRUE) && !in_array($detected_mime, $allowed_mimes, TRUE)) {
                        $this->output->set_content_type('application/json')
                                     ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                        return;
                    }

                    $safe_name = 'photo_temp_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    if (file_put_contents($temp_dir . $safe_name, $decoded) === FALSE) {
                        $this->output->set_content_type('application/json')
                                     ->set_output(json_encode(array('success' => FALSE, 'error' => 'Failed to store image.')));
                        return;
                    }

                    // Clean up any previously uploaded temp photo for this wizard session
                    $wizard = $this->session->userdata('student_registration_wizard') ?: array();
                    $old_sd = isset($wizard['student_details']) ? $wizard['student_details'] : array();
                    if (!empty($old_sd['photo_temp_path'])) {
                        $old_file = FCPATH . $old_sd['photo_temp_path'];
                        if (is_file($old_file) && $old_file !== ($temp_dir . $safe_name)) {
                            @unlink($old_file);
                        }
                    }

                    $temp_path = 'uploads/photo_temp/' . $safe_name;
                    $orig_name = $this->input->post('photo_display_name') ?: ('student_photo.' . $ext);

                    if (!isset($wizard['student_details'])) {
                        $wizard['student_details'] = array();
                    }
                    $wizard['student_details']['photo_temp_path']    = $temp_path;
                    $wizard['student_details']['photo_display_name'] = $orig_name;
                    $this->session->set_userdata('student_registration_wizard', $wizard);

                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(array(
                                     'success'      => TRUE,
                                     'temp_path'    => $temp_path,
                                     'display_name' => $orig_name,
                                     'preview_url'  => base_url($temp_path),
                                 )));
                    return;
                } else {
                    $this->output->set_content_type('application/json')
                                 ->set_output(json_encode(array('success' => FALSE, 'error' => 'Invalid image format.')));
                    return;
                }
            }

            // 2. Direct multipart upload
            if (empty($_FILES['student_image']['name'])) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select an image to upload.')));
                return;
            }

            $upload_err = isset($_FILES['student_image']['error']) ? (int)$_FILES['student_image']['error'] : UPLOAD_ERR_NO_FILE;
            if ($upload_err !== UPLOAD_ERR_OK) {
                $err_msg = 'Upload error. Please try again.';
                if ($upload_err === UPLOAD_ERR_INI_SIZE || $upload_err === UPLOAD_ERR_FORM_SIZE) {
                    $err_msg = 'Image size must not exceed 3 MB.';
                } elseif ($upload_err === UPLOAD_ERR_NO_FILE) {
                    $err_msg = 'Please select an image to upload.';
                }
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => $err_msg)));
                return;
            }

            $orig_name = $_FILES['student_image']['name'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext, TRUE)) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                return;
            }

            if ($_FILES['student_image']['size'] > $max_size) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Image size must not exceed 3 MB.')));
                return;
            }

            $img_info = @getimagesize($_FILES['student_image']['tmp_name']);
            if ($img_info === FALSE) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                return;
            }

            $detected_type = isset($img_info[2]) ? $img_info[2] : 0;
            $detected_mime = isset($img_info['mime']) ? strtolower($img_info['mime']) : '';
            if (!in_array($detected_type, $allowed_types, TRUE) && !in_array($detected_mime, $allowed_mimes, TRUE)) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Please select a valid JPG, JPEG, PNG, or WEBP image.')));
                return;
            }

            $safe_name = 'photo_temp_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
            if (!@move_uploaded_file($_FILES['student_image']['tmp_name'], $temp_dir . $safe_name)) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Failed to store image.')));
                return;
            }

            $wizard = $this->session->userdata('student_registration_wizard') ?: array();
            $old_sd = isset($wizard['student_details']) ? $wizard['student_details'] : array();
            if (!empty($old_sd['photo_temp_path'])) {
                $old_file = FCPATH . $old_sd['photo_temp_path'];
                if (is_file($old_file) && $old_file !== ($temp_dir . $safe_name)) {
                    @unlink($old_file);
                }
            }

            $temp_path = 'uploads/photo_temp/' . $safe_name;

            if (!isset($wizard['student_details'])) {
                $wizard['student_details'] = array();
            }
            $wizard['student_details']['photo_temp_path']    = $temp_path;
            $wizard['student_details']['photo_display_name'] = $orig_name;
            $this->session->set_userdata('student_registration_wizard', $wizard);

            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'success'      => TRUE,
                             'temp_path'    => $temp_path,
                             'display_name' => $orig_name,
                             'preview_url'  => base_url($temp_path),
                         )));
        } catch (Throwable $e) {
            log_message('error', 'wizard_photo_upload exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'success' => FALSE,
                             'error'   => 'Image upload failed. Please ensure the file is a valid image.'
                         )));
        }
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard Step 1 — Validate & save Student Details to session
       POST students/wizard_step1
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_step1()
    {
        $this->require_permission('students.create');

        if ($this->input->method() !== 'post') {
            redirect('students/add');
            return;
        }

        $this->form_validation->set_rules('admission_number', 'Admission Number', 'required|trim');
        $this->form_validation->set_rules('first_name',       'First Name',        'required|trim');
        $this->form_validation->set_rules('middle_name',      'Middle Name',       'trim|max_length[50]');
        $this->form_validation->set_rules('last_name',        'Last Name',         'trim|max_length[50]');
        $this->form_validation->set_rules('date_of_birth',    'Date of Birth',     'trim|callback_validate_dob');
        $this->form_validation->set_rules('student_email',    'Student Email',     'trim|max_length[100]');
        $this->form_validation->set_rules('house_name',       'House Name',        'trim|max_length[100]');
        $this->form_validation->set_rules('street',           'Street',            'trim|max_length[100]');
        $this->form_validation->set_rules('city',             'City',              'trim|max_length[100]');
        $this->form_validation->set_rules('district',         'District',          'trim|max_length[100]');
        $this->form_validation->set_rules('state',            'State',             'trim|max_length[100]');
        $this->form_validation->set_rules('pin_code',          'PIN Code',          'trim|callback_validate_pincode');

        if ($this->form_validation->run() !== TRUE) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'errors'  => $this->_collect_validation_errors(),
                )));
            return;
        }

        // Store step-1 data in session (no DB write)
        $wizard = $this->session->userdata('student_registration_wizard') ?: array();

        // Fresh or updated step 1 always resets submitted status
        $wizard['submitted'] = FALSE;
        if (empty($wizard['wizard_token'])) {
            $wizard['wizard_token'] = sha1(uniqid('wiz_', TRUE));
        }

        $photo_temp_path = $this->input->post('photo_temp_path', TRUE);
        $photo_display_name = $this->input->post('photo_display_name', TRUE);

        // If user cleared the photo, delete old temp file
        $old_photo = isset($wizard['student_details']['photo_temp_path']) ? $wizard['student_details']['photo_temp_path'] : '';
        if (empty($photo_temp_path) && !empty($old_photo)) {
            $old_f = FCPATH . $old_photo;
            if (is_file($old_f)) {
                @unlink($old_f);
            }
        }

        $student_phone_raw = $this->input->post('student_phone', TRUE);
        $student_phone_country = $this->input->post('student_phone_country', TRUE) ?: 'IN';
        $student_phone_norm = NULL;
        if (!empty($student_phone_raw)) {
            $sp_check = $this->phone_validator->validate_and_normalize($student_phone_raw, $student_phone_country, false);
            if (!$sp_check['valid']) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => FALSE,
                        'errors'  => array('student_phone' => $sp_check['error']),
                    )));
                return;
            }
            $student_phone_norm = $sp_check['normalized'];
        }

        $wizard['student_details'] = array(
            'admission_number'   => $this->input->post('admission_number', TRUE),
            'first_name'         => $this->input->post('first_name',       TRUE),
            'middle_name'        => $this->input->post('middle_name',      TRUE) ?: '',
            'last_name'          => $this->input->post('last_name',        TRUE) ?: '',
            'gender'             => $this->input->post('gender',           TRUE) ?: 'Male',
            'date_of_birth'      => $this->input->post('date_of_birth',    TRUE) ?: '',
            'blood_group'        => $this->input->post('blood_group',      TRUE),
            'student_phone'      => $student_phone_norm,
            'student_email'      => $this->input->post('student_email',    TRUE) ?: '',
            'house_name'         => $this->input->post('house_name',       TRUE) ?: '',
            'street'             => $this->input->post('street',           TRUE) ?: '',
            'city'               => $this->input->post('city',             TRUE) ?: '',
            'district'           => $this->input->post('district',         TRUE) ?: '',
            'state'              => $this->input->post('state',            TRUE) ?: '',
            'pin_code'           => $this->input->post('pin_code',         TRUE) ?: '',
            'photo_temp_path'    => $photo_temp_path ?: '',
            'photo_display_name' => $photo_display_name ?: '',
        );

        $this->session->set_userdata('student_registration_wizard', $wizard);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'  => TRUE,
                'redirect' => site_url('students/add?step=2'),
            )));
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard TC Upload — separate multipart file-only endpoint
       POST students/wizard_tc_upload
       Returns JSON { success, temp_path, display_name, error }
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_tc_upload()
    {
        try {
            $this->require_permission('students.create');

            if ($this->input->method() !== 'post') {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Invalid request.')));
                return;
            }

            if (empty($_FILES['tc_document']['name'])) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'TC Document is required.')));
                return;
            }

            $upload_err = isset($_FILES['tc_document']['error']) ? (int)$_FILES['tc_document']['error'] : UPLOAD_ERR_NO_FILE;
            if ($upload_err !== UPLOAD_ERR_OK) {
                $err_msg = 'Upload error. Please try again.';
                if ($upload_err === UPLOAD_ERR_INI_SIZE || $upload_err === UPLOAD_ERR_FORM_SIZE) {
                    $err_msg = 'TC Document must not exceed 3 MB.';
                } elseif ($upload_err === UPLOAD_ERR_NO_FILE) {
                    $err_msg = 'TC Document is required.';
                }
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => $err_msg)));
                return;
            }

            // ── Security: whitelist extensions + binary header validation ──────────
            $allowed_ext  = array('pdf', 'jpg', 'jpeg', 'png');
            $orig_name    = $_FILES['tc_document']['name'];
            $ext          = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext, TRUE)) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'TC Document must be a PDF, JPG, JPEG, or PNG file.')));
                return;
            }

            // Binary header checks using shared validate_uploaded_tc_file helper
            $tmp_path = $_FILES['tc_document']['tmp_name'];
            $val_check = validate_uploaded_tc_file($tmp_path, $ext);
            if (!$val_check['valid']) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => $val_check['error'])));
                return;
            }

            // Size limit: 3MB
            $max_size = 3 * 1024 * 1024;
            if ($_FILES['tc_document']['size'] > $max_size) {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'TC Document must not exceed 3 MB.')));
                return;
            }

            // ── Safe filename + temp storage ──────────────────────────────────────
            try {
                $rand_token = bin2hex(random_bytes(6));
            } catch (Exception $re) {
                $rand_token = substr(md5(uniqid(mt_rand(), true)), 0, 12);
            }
            $safe_name  = 'tc_' . time() . '_' . $rand_token . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
            $temp_dir   = FCPATH . 'uploads/tc_temp/';

            if (!is_dir($temp_dir)) {
                @mkdir($temp_dir, 0775, TRUE);
            }
            if (!is_dir($temp_dir)) {
                $temp_dir = './uploads/tc_temp/';
                if (!is_dir($temp_dir)) {
                    @mkdir($temp_dir, 0775, TRUE);
                }
            }

            if (!@move_uploaded_file($_FILES['tc_document']['tmp_name'], $temp_dir . $safe_name)) {
                log_message('error', 'wizard_tc_upload: move_uploaded_file failed to ' . $temp_dir . $safe_name);
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('success' => FALSE, 'error' => 'Failed to store file. Please check upload folder permissions.')));
                return;
            }

            // Clean up any previously uploaded temp TC for this wizard session
            $wizard = $this->session->userdata('student_registration_wizard') ?: array();
            $old_ad = isset($wizard['academic_details']) ? $wizard['academic_details'] : array();
            if (!empty($old_ad['prev_school']['tc_temp_path'])) {
                $old_file = FCPATH . $old_ad['prev_school']['tc_temp_path'];
                if (is_file($old_file) && $old_file !== ($temp_dir . $safe_name)) {
                    @unlink($old_file);
                }
            }

            $temp_path = 'uploads/tc_temp/' . $safe_name;

            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'success'      => TRUE,
                             'temp_path'    => $temp_path,
                             'display_name' => $orig_name,
                         )));
        } catch (Throwable $e) {
            log_message('error', 'wizard_tc_upload exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'success' => FALSE,
                             'error'   => 'TC Document upload failed. Please ensure the file is valid.'
                         )));
        }
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard Step 2 — Validate & save Academic Details to session
       POST students/wizard_step2
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_step2()
    {
        $this->require_permission('students.create');

        if ($this->input->method() !== 'post') {
            redirect('students/add?step=2');
            return;
        }

        $wizard = $this->session->userdata('student_registration_wizard') ?: array();
        if (empty($wizard['student_details'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'  => FALSE,
                    'redirect' => site_url('students/add'),
                    'message'  => 'Please complete Step 1 first.',
                )));
            return;
        }

        // ── Server-side validation ────────────────────────────────────────────
        $this->form_validation->set_rules('class_id', 'Class', 'required');

        $no_prev_school = ($this->input->post('no_previous_school') == '1');

        if (!$no_prev_school) {
            $this->form_validation->set_rules('prev_school_name',  'Previous School Name',    'trim|required');
            $this->form_validation->set_rules('tc_number',         'TC Number',               'trim|required',
                array('required' => 'TC Number is required.')
            );
            $this->form_validation->set_rules('prev_school_board',  'Previous School Board',   'trim');
            $this->form_validation->set_rules('prev_class',         'Previous Class',          'trim');
            $this->form_validation->set_rules('prev_academic_year', 'Previous Academic Year',  'trim');
            $this->form_validation->set_rules('prev_percentage',
                'Previous Percentage',
                'trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
            );
        }

        $errors = array();
        if ($this->form_validation->run() !== TRUE) {
            $errors = $this->_collect_validation_errors();
        }

        // Validate mandatory TC document when previous school is applicable
        if (!$no_prev_school) {
            $tc_temp_path = $this->input->post('tc_temp_path', TRUE);
            if (empty($tc_temp_path) || !is_file(FCPATH . $tc_temp_path)) {
                $errors['tc_document'] = 'TC Document is required.';
            }
        }

        if (!empty($errors)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'errors'  => $errors,
                )));
            return;
        }

        // ── Build academic_details sub-array ──────────────────────────────────
        $academic_details = array(
            'academic_year_id'   => $this->academic_year_id,
            'class_id'           => (int)$this->input->post('class_id'),
            'division_id'        => (int)($this->input->post('division_id') ?: $this->input->post('section_id')) ?: 0,
            'section_id'         => (int)($this->input->post('division_id') ?: $this->input->post('section_id')) ?: 0,
            'roll_number'        => $this->input->post('roll_number', TRUE),
            'no_previous_school' => $no_prev_school ? 1 : 0,
        );

        // ── Previous school fields (only when applicable) ─────────────────────
        if (!$no_prev_school) {
            $academic_details['prev_school'] = array(
                'school_name'            => $this->input->post('prev_school_name',    TRUE),
                'school_address'         => $this->input->post('prev_school_address', TRUE),
                'school_board'           => $this->input->post('prev_school_board',   TRUE),
                'previous_class'         => $this->input->post('prev_class',           TRUE),
                'previous_academic_year' => $this->input->post('prev_academic_year',  TRUE),
                'date_of_leaving'        => $this->input->post('date_of_leaving',     TRUE) ?: NULL,
                'reason_for_leaving'     => $this->input->post('reason_for_leaving',  TRUE),
                'tc_number'              => $this->input->post('tc_number',            TRUE),
                'previous_percentage'    => strlen($this->input->post('prev_percentage')) > 0
                                               ? (float)$this->input->post('prev_percentage')
                                               : NULL,
                'tc_temp_path'           => $this->input->post('tc_temp_path', TRUE),
            );
        } else {
            $academic_details['prev_school'] = array();
            // Clean up any temp TC file from a previous attempt
            $old_ad = isset($wizard['academic_details']) ? $wizard['academic_details'] : array();
            if (!empty($old_ad['prev_school']['tc_temp_path'])) {
                $old_file = FCPATH . $old_ad['prev_school']['tc_temp_path'];
                if (is_file($old_file)) {
                    @unlink($old_file);
                }
            }
        }

        // ── Academic activities ───────────────────────────────────────────────
        $raw_academic = $this->input->post('academic_activities');
        $activities   = array();
        if (!empty($raw_academic) && is_array($raw_academic)) {
            foreach ($raw_academic as $act) {
                if (!empty($act['activity_name'])) {
                    $activities[] = array(
                        'category'        => 'Academic',
                        'activity_type'   => isset($act['activity_type'])   ? $act['activity_type']   : 'Achievement',
                        'activity_name'   => isset($act['activity_name'])   ? $act['activity_name']   : '',
                        'description'     => isset($act['description'])     ? $act['description']     : '',
                        'level'           => isset($act['level'])           ? $act['level']           : '',
                        'position_result' => isset($act['position_result']) ? $act['position_result'] : '',
                        'year'            => isset($act['year']) && is_numeric($act['year'])
                                                ? (int)$act['year'] : NULL,
                    );
                }
            }
        }
        $academic_details['activities'] = $activities;

        // ── Extracurricular activities ────────────────────────────────────────
        $raw_extra     = $this->input->post('extracurricular');
        $extracurricular = array();
        if (!empty($raw_extra) && is_array($raw_extra)) {
            foreach ($raw_extra as $act) {
                if (!empty($act['activity_name'])) {
                    $extracurricular[] = array(
                        'category'        => 'Extracurricular',
                        'activity_type'   => isset($act['activity_type'])   ? $act['activity_type']   : 'Sports',
                        'activity_name'   => isset($act['activity_name'])   ? $act['activity_name']   : '',
                        'description'     => isset($act['description'])     ? $act['description']     : '',
                        'level'           => isset($act['level'])           ? $act['level']           : '',
                        'position_result' => isset($act['position_result']) ? $act['position_result'] : '',
                        'year'            => isset($act['year']) && is_numeric($act['year'])
                                                ? (int)$act['year'] : NULL,
                    );
                }
            }
        }
        $academic_details['extracurricular'] = $extracurricular;

        $wizard['submitted'] = FALSE;
        $wizard['academic_details'] = $academic_details;
        $this->session->set_userdata('student_registration_wizard', $wizard);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'  => TRUE,
                'redirect' => site_url('students/add?step=3'),
            )));
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard Final Save — Validate Step 3, merge all data, run DB transaction
       POST students/wizard_save
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_save()
    {
        $this->require_permission('students.create');

        if ($this->input->method() !== 'post') {
            redirect('students/add?step=3');
            return;
        }

        $wizard = $this->session->userdata('student_registration_wizard') ?: array();

        // Guard: require all prior steps
        if (empty($wizard['student_details']) || empty($wizard['academic_details'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'  => FALSE,
                    'redirect' => site_url('students/add'),
                    'message'  => 'Registration session expired. Please start again.',
                )));
            return;
        }

        // Anti-duplicate: check wizard token consumed flag
        if (!empty($wizard['submitted'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'  => FALSE,
                    'message'  => 'This registration has already been submitted.',
                )));
            return;
        }

        $this->form_validation->set_rules('guardian_name', 'Guardian Name', 'required|trim', array(
            'required' => 'Guardian name is required.'
        ));

        // Phone validation
        $phone_errors = array();

        $gp_val = $this->input->post('guardian_phone', TRUE);
        $gp_check = $this->phone_validator->validate_and_normalize($gp_val, 'IN', true);
        if (!$gp_check['valid']) {
            $phone_errors['guardian_phone'] = $gp_check['error'];
        }

        $fp_val = $this->input->post('father_phone', TRUE);
        $fp_norm = NULL;
        if (!empty($fp_val)) {
            $fp_check = $this->phone_validator->validate_and_normalize($fp_val, 'IN', false);
            if (!$fp_check['valid']) {
                $phone_errors['father_phone'] = $fp_check['error'];
            } else {
                $fp_norm = $fp_check['normalized'];
            }
        }

        $mp_val = $this->input->post('mother_phone', TRUE);
        $mp_norm = NULL;
        if (!empty($mp_val)) {
            $mp_check = $this->phone_validator->validate_and_normalize($mp_val, 'IN', false);
            if (!$mp_check['valid']) {
                $phone_errors['mother_phone'] = $mp_check['error'];
            } else {
                $mp_norm = $mp_check['normalized'];
            }
        }

        $ec_val = $this->input->post('emergency_contact', TRUE);
        $ec_norm = NULL;
        if (!empty($ec_val)) {
            $ec_check = $this->phone_validator->validate_and_normalize($ec_val, 'IN', false);
            if (!$ec_check['valid']) {
                $phone_errors['emergency_contact'] = $ec_check['error'];
            } else {
                $ec_norm = $ec_check['normalized'];
            }
        }

        if (!empty($phone_errors) || $this->form_validation->run() !== TRUE) {
            $errs = array_merge($this->_collect_validation_errors(), $phone_errors);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'errors'  => $errs,
                )));
            return;
        }

        $sd = isset($wizard['student_details']) ? $wizard['student_details'] : array();
        if (!empty($sd['date_of_birth']) && $sd['date_of_birth'] > date('Y-m-d')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'errors'  => array('date_of_birth' => 'Date of Birth cannot be a future date.'),
                )));
            return;
        }

        // Mark submitted immediately to prevent duplicate concurrent posts
        $wizard['submitted'] = TRUE;
        $this->session->set_userdata('student_registration_wizard', $wizard);

        try {
            $parent_details = array(
                'guardian_name'     => $this->input->post('guardian_name',     TRUE),
                'guardian_relation' => $this->input->post('guardian_relation', TRUE) ?: 'Father',
                'guardian_phone'    => $gp_check['normalized'],
                'father_phone'      => $fp_norm,
                'mother_phone'      => $mp_norm,
                'emergency_contact' => $ec_norm,
                'guardian_email'    => $this->input->post('guardian_email',    TRUE),
                'address'           => $this->input->post('address',           TRUE),
            );

            // ── Merge all three steps ──────────────────────────────────────────────
            $sd = $wizard['student_details'];
            $ad = $wizard['academic_details'];

            $class_id    = !empty($ad['class_id']) ? (int)$ad['class_id'] : NULL;
            $division_id = !empty($ad['division_id']) ? (int)$ad['division_id'] : (!empty($ad['section_id']) ? (int)$ad['section_id'] : ($class_id ? $this->Division_model->get_default_division_id($class_id, (int)$this->school_id) : NULL));

            // ── Photo Handling: Move from temp → permanent uploads/students/ ──────
            $photo_filename   = NULL;
            $moved_photo_path = NULL;
            if (!empty($sd['photo_temp_path'])) {
                $temp_photo = FCPATH . $sd['photo_temp_path'];
                if (is_file($temp_photo)) {
                    $photo_dest_dir = FCPATH . 'uploads/students/';
                    if (!is_dir($photo_dest_dir)) {
                        mkdir($photo_dest_dir, 0755, TRUE);
                    }
                    $photo_ext = strtolower(pathinfo($sd['photo_temp_path'], PATHINFO_EXTENSION));
                    if ($photo_ext === 'jpeg') $photo_ext = 'jpg';
                    $photo_filename = 'photo_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $photo_ext;
                    $photo_dest_path = $photo_dest_dir . $photo_filename;
                    if (rename($temp_photo, $photo_dest_path)) {
                        $moved_photo_path = $photo_dest_path;
                    } else {
                        $photo_filename = NULL;
                    }
                }
            }

            $student_data = array(
                'school_id'         => (int)$this->school_id,
                'admission_number'  => $sd['admission_number'],
                'first_name'        => $sd['first_name'],
                'middle_name'       => !empty($sd['middle_name']) ? $sd['middle_name'] : NULL,
                'last_name'         => $sd['last_name']         ?: '',
                'gender'            => $sd['gender']            ?: 'Male',
                'date_of_birth'     => $sd['date_of_birth']     ?: date('Y-m-d'),
                'blood_group'       => $sd['blood_group']       ?: '',
                'photo'             => $photo_filename,
                'academic_year_id'  => !empty($ad['academic_year_id']) ? (int)$ad['academic_year_id'] : $this->academic_year_id,
                'class_id'          => $class_id,
                'division_id'       => $division_id,
                'roll_number'       => !empty($ad['roll_number']) ? trim($ad['roll_number']) : '',
                'guardian_name'     => $parent_details['guardian_name'],
                'guardian_relation' => $parent_details['guardian_relation'],
                'guardian_phone'    => $parent_details['guardian_phone']    ?: '',
                'student_phone'     => !empty($sd['student_phone']) ? $sd['student_phone'] : NULL,
                'student_email'     => !empty($sd['student_email']) ? $sd['student_email'] : NULL,
                'house_name'        => !empty($sd['house_name']) ? $sd['house_name'] : NULL,
                'street'            => !empty($sd['street']) ? $sd['street'] : NULL,
                'city'              => !empty($sd['city']) ? $sd['city'] : NULL,
                'district'          => !empty($sd['district']) ? $sd['district'] : NULL,
                'state'             => !empty($sd['state']) ? $sd['state'] : NULL,
                'pin_code'          => !empty($sd['pin_code']) ? $sd['pin_code'] : NULL,
                'father_phone'      => !empty($parent_details['father_phone']) ? $parent_details['father_phone'] : NULL,
                'mother_phone'      => !empty($parent_details['mother_phone']) ? $parent_details['mother_phone'] : NULL,
                'emergency_contact' => !empty($parent_details['emergency_contact']) ? $parent_details['emergency_contact'] : NULL,
                'guardian_email'    => $parent_details['guardian_email']    ?: '',
                'address'           => !empty($parent_details['address']) ? $parent_details['address'] : (school_format_address($sd) ?: ''),
                'status'            => 1,
                'is_deleted'        => 'n',
                'created_at'        => date('Y-m-d H:i:s'),
            );

            // ── Database transaction ──────────────────────────────────────────────
            $this->db->trans_start();

            // 1. Insert core student record via model
            $new_id = (int)$this->Student_model->insert($student_data);

            if ($new_id > 0) {
                $no_prev_school = !empty($ad['no_previous_school']);
                $prev_school    = isset($ad['prev_school']) ? $ad['prev_school'] : array();

                // 2. Previous school record
                if (!$no_prev_school && !empty($prev_school['school_name'])) {
                    $tc_document_id = NULL;

                    // Move TC file from temp → permanent location
                    if (!empty($prev_school['tc_temp_path'])) {
                        $temp_path  = FCPATH . $prev_school['tc_temp_path'];
                        $dest_dir   = FCPATH . 'uploads/documents/';
                        if (!is_dir($dest_dir)) {
                            mkdir($dest_dir, 0755, TRUE);
                        }
                        $dest_name = 'tc_' . $new_id . '_' . basename($prev_school['tc_temp_path']);
                        $dest_path = $dest_dir . $dest_name;

                        if (is_file($temp_path) && rename($temp_path, $dest_path)) {
                            $perm_path      = 'uploads/documents/' . $dest_name;
                            $tc_document_id = $this->Student_academic_model->insert_tc_document(
                                $new_id,
                                $perm_path,
                                isset($prev_school['tc_number']) ? $prev_school['tc_number'] : '',
                                (int)$this->school_id
                            );
                        }
                    }

                    $prev_school_data = array(
                        'school_id'              => (int)$this->school_id,
                        'student_id'             => $new_id,
                        'school_name'            => $prev_school['school_name'],
                        'school_address'         => isset($prev_school['school_address'])         ? $prev_school['school_address']         : NULL,
                        'school_board'           => isset($prev_school['school_board'])           ? $prev_school['school_board']           : NULL,
                        'previous_class'         => isset($prev_school['previous_class'])         ? $prev_school['previous_class']         : NULL,
                        'previous_academic_year' => isset($prev_school['previous_academic_year']) ? $prev_school['previous_academic_year'] : NULL,
                        'date_of_leaving'        => !empty($prev_school['date_of_leaving'])       ? $prev_school['date_of_leaving']        : NULL,
                        'reason_for_leaving'     => isset($prev_school['reason_for_leaving'])     ? $prev_school['reason_for_leaving']     : NULL,
                        'tc_number'              => isset($prev_school['tc_number'])               ? $prev_school['tc_number']               : NULL,
                        'tc_document_id'         => $tc_document_id,
                        'previous_percentage'    => isset($prev_school['previous_percentage'])    ? $prev_school['previous_percentage']    : NULL,
                        'status'                 => 1,
                        'created_at'             => date('Y-m-d H:i:s'),
                    );
                    $this->Student_academic_model->insert_previous_school($prev_school_data, (int)$this->school_id);
                }

                // 3. Academic activities
                $all_activities = array();

                if (!empty($ad['activities']) && is_array($ad['activities'])) {
                    foreach ($ad['activities'] as $act) {
                        if (!is_array($act) || empty($act['activity_name'])) continue;
                        $all_activities[] = array(
                            'school_id'       => (int)$this->school_id,
                            'student_id'      => $new_id,
                            'category'        => 'Academic',
                            'activity_type'   => !empty($act['activity_type']) && is_string($act['activity_type']) ? $act['activity_type'] : 'Achievement',
                            'activity_name'   => is_string($act['activity_name']) ? $act['activity_name'] : '',
                            'level'           => !empty($act['level']) && is_string($act['level']) ? $act['level'] : NULL,
                            'position_result' => !empty($act['position_result']) && is_string($act['position_result']) ? $act['position_result'] : NULL,
                            'year'            => !empty($act['year']) && is_numeric($act['year']) ? (int)$act['year'] : (int)date('Y'),
                            'description'     => !empty($act['description']) && is_string($act['description']) ? $act['description'] : NULL,
                            'status'          => 1,
                            'created_at'      => date('Y-m-d H:i:s'),
                        );
                    }
                }

                if (!empty($ad['extracurricular']) && is_array($ad['extracurricular'])) {
                    foreach ($ad['extracurricular'] as $extra) {
                        if (!is_array($extra) || empty($extra['activity_name'])) continue;
                        $all_activities[] = array(
                            'school_id'       => (int)$this->school_id,
                            'student_id'      => $new_id,
                            'category'        => 'Extracurricular',
                            'activity_type'   => !empty($extra['activity_type']) && is_string($extra['activity_type']) ? $extra['activity_type'] : 'Sports',
                            'activity_name'   => is_string($extra['activity_name']) ? $extra['activity_name'] : '',
                            'level'           => !empty($extra['level']) && is_string($extra['level']) ? $extra['level'] : NULL,
                            'position_result' => !empty($extra['position_result']) && is_string($extra['position_result']) ? $extra['position_result'] : NULL,
                            'year'            => !empty($extra['year']) && is_numeric($extra['year']) ? (int)$extra['year'] : (int)date('Y'),
                            'description'     => !empty($extra['description']) && is_string($extra['description']) ? $extra['description'] : NULL,
                            'status'          => 1,
                            'created_at'      => date('Y-m-d H:i:s'),
                        );
                    }
                }

                if (!empty($all_activities)) {
                    $this->Student_academic_model->insert_activities_batch($all_activities, (int)$this->school_id);
                }
            }

            $this->db->trans_complete();

            if (!$this->db->trans_status() || !$new_id) {
                // Roll back happened automatically; clear submitted flag so user can retry
                $wizard['submitted'] = FALSE;
                $this->session->set_userdata('student_registration_wizard', $wizard);

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => FALSE,
                        'message' => 'Failed to save student. Please try again.',
                    )));
                return;
            }

            // ── Success: clear wizard session data ────────────────────────────────
            $this->session->unset_userdata('student_registration_wizard');
            $this->session->set_flashdata('success', 'Student registered successfully.');

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'  => TRUE,
                    'redirect' => site_url('students/profile/' . $new_id),
                )));
        } catch (Throwable $e) {
            log_message('error', 'wizard_save exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->db->trans_rollback();
            $wizard['submitted'] = FALSE;
            $this->session->set_userdata('student_registration_wizard', $wizard);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'message' => 'An error occurred while saving: ' . $e->getMessage(),
                )));
        }
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Wizard Cancel — clear session, redirect to student list
       GET/POST students/wizard_cancel
    ───────────────────────────────────────────────────────────────────────── */
    public function wizard_cancel()
    {
        $this->require_permission('students.create');

        // Clean up any temp TC file
        $wizard = $this->session->userdata('student_registration_wizard') ?: array();
        $ad     = isset($wizard['academic_details']) ? $wizard['academic_details'] : array();
        if (!empty($ad['prev_school']['tc_temp_path'])) {
            $temp = FCPATH . $ad['prev_school']['tc_temp_path'];
            if (is_file($temp)) {
                @unlink($temp);
            }
        }

        $this->session->unset_userdata('student_registration_wizard');
        redirect('students');
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Internal: collect form_validation errors into a flat array
    ───────────────────────────────────────────────────────────────────────── */
    private function _collect_validation_errors()
    {
        $errors = array();
        foreach ($this->form_validation->error_array() as $field => $msg) {
            $errors[$field] = $msg;
        }
        return $errors;
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Validation Callback: Date of Birth cannot be a future date
    ───────────────────────────────────────────────────────────────────────── */
    public function validate_dob($dob)
    {
        $dob = trim((string)$dob);
        if ($dob === '') {
            return TRUE;
        }

        $d = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$d || $d->format('Y-m-d') !== $dob) {
            $this->form_validation->set_message('validate_dob', 'Please enter a valid Date of Birth.');
            return FALSE;
        }

        $today = date('Y-m-d');
        if ($dob > $today) {
            $this->form_validation->set_message('validate_dob', 'Date of Birth cannot be a future date.');
            return FALSE;
        }

        return TRUE;
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Validation Callback: PIN Code (Optional, but if entered must be 6 digits)
    ───────────────────────────────────────────────────────────────────────── */
    public function validate_pincode($pin)
    {
        $pin = trim((string)$pin);
        if ($pin === '') {
            return TRUE;
        }
        $clean_pin = preg_replace('/\s+/', '', $pin);
        if (!preg_match('/^[1-9][0-9]{5}$/', $clean_pin)) {
            $this->form_validation->set_message('validate_pincode', 'Please enter a valid 6-digit Indian PIN code (e.g. 682001).');
            return FALSE;
        }
        return TRUE;
    }

    /* ─────────────────────────────────────────────────────────────────────────
       Validation Callback: Division must be valid for the selected Class
    ───────────────────────────────────────────────────────────────────────── */
    public function validate_class_division($division_id)
    {
        if (empty($division_id)) {
            $division_id = $this->input->post('section_id');
        }
        $class_id = (int)$this->input->post('class_id');
        $division_id = (int)$division_id;
        if (!empty($division_id) && !empty($class_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $this->form_validation->set_message('validate_class_division', 'The selected division is invalid for the chosen class.');
                return FALSE;
            }
        }
        return TRUE;
    }


    /* =========================================================================
       3. Student Edit
       ========================================================================= */
    public function edit($student_id = NULL)
    {
        $this->require_permission('students.edit');

        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }

        if (!$student_id) {
            redirect('students');
            return;
        }

        $student = $this->Student_model->get_by_id($student_id);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('students');
            return;
        }

        $this->load->model('Student_academic_model');

        $photo_error = NULL;
        $tc_error    = NULL;

        $prev_school = $this->Student_academic_model->get_previous_school($student_id);
        $tc_document = $this->Student_academic_model->get_tc_document($student_id, $prev_school ? $prev_school->tc_document_id : NULL);

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'trim');
            $this->form_validation->set_rules('gender', 'Gender', 'trim');
            $this->form_validation->set_rules('date_of_birth', 'Date of Birth', 'trim|callback_validate_dob');
            $this->form_validation->set_rules('blood_group', 'Blood Group', 'trim');
            $this->form_validation->set_rules('nationality', 'Nationality', 'trim');
            $this->form_validation->set_rules('religion', 'Religion', 'trim');
            $this->form_validation->set_rules('class_id', 'Class', 'required|integer');
            $this->form_validation->set_rules('division_id', 'Division', 'trim|callback_validate_class_division');
            if ($this->input->post('academic_year_id')) {
                $this->form_validation->set_rules('academic_year_id', 'Academic Year', 'integer');
            }
            $this->form_validation->set_rules('roll_number', 'Roll Number', 'trim');
            $this->form_validation->set_rules('guardian_name', 'Guardian Name', 'required|trim');
            $this->form_validation->set_rules('guardian_relation', 'Guardian Relation', 'trim');
            $this->form_validation->set_rules('guardian_phone', 'Guardian Phone', 'required|trim');
            $this->form_validation->set_rules('guardian_email', 'Guardian Email', 'trim|valid_email');
            $this->form_validation->set_rules('address', 'Address', 'trim');
            $this->form_validation->set_rules('student_email', 'Student Email', 'trim|max_length[100]');
            $this->form_validation->set_rules('house_name', 'House Name', 'trim|max_length[100]');
            $this->form_validation->set_rules('street', 'Street', 'trim|max_length[100]');
            $this->form_validation->set_rules('city', 'City', 'trim|max_length[100]');
            $this->form_validation->set_rules('district', 'District', 'trim|max_length[100]');
            $this->form_validation->set_rules('state', 'State', 'trim|max_length[100]');
            $this->form_validation->set_rules('pin_code', 'PIN Code', 'trim|callback_validate_pincode');

            $no_prev_school = ($this->input->post('no_previous_school') == '1');
            if (!$no_prev_school) {
                $has_prev_input = (
                    strlen(trim((string)$this->input->post('prev_school_name'))) > 0 ||
                    strlen(trim((string)$this->input->post('tc_number'))) > 0 ||
                    !empty($_FILES['tc_document']['name']) ||
                    $prev_school !== NULL
                );

                if ($has_prev_input) {
                    $this->form_validation->set_rules('prev_school_name', 'Previous School Name', 'required|trim');
                    $this->form_validation->set_rules('tc_number', 'TC Number', 'required|trim');
                }
                $this->form_validation->set_rules('prev_school_address', 'Previous School Address', 'trim');
                $this->form_validation->set_rules('prev_school_board', 'Previous School Board', 'trim');
                $this->form_validation->set_rules('prev_class', 'Previous Class', 'trim');
                $this->form_validation->set_rules('prev_academic_year', 'Previous Academic Year', 'trim');
                $this->form_validation->set_rules('date_of_leaving', 'Date of Leaving', 'trim');
                $this->form_validation->set_rules('prev_percentage', 'Previous Percentage', 'trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
                $this->form_validation->set_rules('reason_for_leaving', 'Reason for Leaving', 'trim');
            }

            // Validate all phone fields
            $phone_error = null;

            $gp_country = $this->input->post('guardian_phone_country', TRUE) ?: 'IN';
            $gp_val = $this->input->post('guardian_phone', TRUE);
            $gp_check = $this->phone_validator->validate_and_normalize($gp_val, $gp_country, true);
            if (!$gp_check['valid']) {
                $phone_error = $gp_check['error'];
            }
            $gp_norm = $gp_check['normalized'];

            $sp_country = $this->input->post('student_phone_country', TRUE) ?: 'IN';
            $sp_val = $this->input->post('student_phone', TRUE);
            $sp_norm = NULL;
            if (!empty($sp_val)) {
                $sp_check = $this->phone_validator->validate_and_normalize($sp_val, $sp_country, false);
                if (!$sp_check['valid']) {
                    $phone_error = $sp_check['error'];
                } else {
                    $sp_norm = $sp_check['normalized'];
                }
            }

            $fp_country = $this->input->post('father_phone_country', TRUE) ?: 'IN';
            $fp_val = $this->input->post('father_phone', TRUE);
            $fp_norm = NULL;
            if (!empty($fp_val)) {
                $fp_check = $this->phone_validator->validate_and_normalize($fp_val, $fp_country, false);
                if (!$fp_check['valid']) {
                    $phone_error = $fp_check['error'];
                } else {
                    $fp_norm = $fp_check['normalized'];
                }
            }

            $mp_country = $this->input->post('mother_phone_country', TRUE) ?: 'IN';
            $mp_val = $this->input->post('mother_phone', TRUE);
            $mp_norm = NULL;
            if (!empty($mp_val)) {
                $mp_check = $this->phone_validator->validate_and_normalize($mp_val, $mp_country, false);
                if (!$mp_check['valid']) {
                    $phone_error = $mp_check['error'];
                } else {
                    $mp_norm = $mp_check['normalized'];
                }
            }

            $ec_country = $this->input->post('emergency_contact_country', TRUE) ?: 'IN';
            $ec_val = $this->input->post('emergency_contact', TRUE);
            $ec_norm = NULL;
            if (!empty($ec_val)) {
                $ec_check = $this->phone_validator->validate_and_normalize($ec_val, $ec_country, false);
                if (!$ec_check['valid']) {
                    $phone_error = $ec_check['error'];
                } else {
                    $ec_norm = $ec_check['normalized'];
                }
            }

            if ($this->form_validation->run() === TRUE && empty($phone_error)) {
                // 1. Process Photo
                $photo_result = $this->process_student_photo($student_id);
                if ($photo_result['success'] === FALSE && !empty($photo_result['error'])) {
                    $photo_error = $photo_result['error'];
                }

                // 2. Process TC Document (if uploaded)
                $existing_tc_doc_id = $prev_school ? (int)$prev_school->tc_document_id : ($tc_document ? (int)$tc_document->document_id : NULL);
                $tc_number_val      = trim((string)$this->input->post('tc_number', TRUE));
                $tc_result          = $this->_process_tc_document_upload($student_id, $existing_tc_doc_id, $tc_number_val);

                if ($tc_result['success'] === FALSE && !empty($tc_result['error'])) {
                    $tc_error = $tc_result['error'];
                }

                if (empty($photo_error) && empty($tc_error)) {
                    $class_id = (int)($this->input->post('class_id') ?: $student->class_id);
                    $division_id_input = $this->input->post('division_id') !== NULL ? $this->input->post('division_id') : $this->input->post('section_id');
                    $division_id = ($division_id_input !== '' && $division_id_input !== NULL) ? (int)$division_id_input : (isset($student->division_id) ? (int)$student->division_id : NULL);

                    $data = array(
                        'admission_number'  => $student->admission_number, // Must never change or regenerate
                        'first_name'        => $this->input->post('first_name', TRUE),
                        'middle_name'       => $this->input->post('middle_name', TRUE) ?: NULL,
                        'last_name'         => $this->input->post('last_name', TRUE) ?: '',
                        'gender'            => $this->input->post('gender', TRUE) ?: $student->gender,
                        'date_of_birth'     => $this->input->post('date_of_birth', TRUE) ?: $student->date_of_birth,
                        'blood_group'       => $this->input->post('blood_group', TRUE) ?: NULL,
                        'nationality'       => $this->input->post('nationality', TRUE) ?: ($student->nationality ?: 'Indian'),
                        'religion'          => $this->input->post('religion', TRUE) ?: NULL,
                        'academic_year_id'  => $this->input->post('academic_year_id') ? (int)$this->input->post('academic_year_id') : (int)$student->academic_year_id,
                        'class_id'          => $class_id,
                        'division_id'       => $division_id,
                        'roll_number'       => $this->input->post('roll_number', TRUE) ?: NULL,
                        'guardian_name'     => $this->input->post('guardian_name', TRUE),
                        'guardian_relation' => $this->input->post('guardian_relation', TRUE) ?: ($student->guardian_relation ?: 'Father'),
                        'guardian_phone'    => $gp_norm,
                        'student_phone'     => $sp_norm,
                        'student_email'     => $this->input->post('student_email', TRUE) ?: NULL,
                        'house_name'        => $this->input->post('house_name', TRUE) ?: NULL,
                        'street'            => $this->input->post('street', TRUE) ?: NULL,
                        'city'              => $this->input->post('city', TRUE) ?: NULL,
                        'district'          => $this->input->post('district', TRUE) ?: NULL,
                        'state'             => $this->input->post('state', TRUE) ?: NULL,
                        'pin_code'          => $this->input->post('pin_code', TRUE) ?: NULL,
                        'father_phone'      => $fp_norm,
                        'mother_phone'      => $mp_norm,
                        'emergency_contact' => $ec_norm,
                        'guardian_email'    => $this->input->post('guardian_email', TRUE) ?: NULL,
                        'address'           => $this->input->post('address', TRUE) ?: (school_format_address($_POST) ?: NULL),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    );

                    // Check if photo was requested to be removed
                    if ($this->input->post('remove_photo') === '1') {
                        $this->Student_model->delete_photo($student_id);
                        $data['photo'] = NULL;
                    } elseif (!empty($photo_result['file_name'])) {
                        if (!empty($student->photo)) {
                            $oldFile = FCPATH . 'uploads/students/' . $student->photo;
                            if (file_exists($oldFile) && is_file($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                        $data['photo'] = $photo_result['file_name'];
                    }

                    // Perform UPDATE on tbl_students
                    $this->Student_model->update($student_id, $data);

                    // 3. Save Previous School Data
                    $tc_document_id = !empty($tc_result['document_id']) ? (int)$tc_result['document_id'] : $existing_tc_doc_id;

                    if (!$no_prev_school && (strlen(trim((string)$this->input->post('prev_school_name'))) > 0 || strlen($tc_number_val) > 0)) {
                        $prev_school_data = array(
                            'student_id'             => (int)$student_id,
                            'school_name'            => $this->input->post('prev_school_name', TRUE),
                            'school_address'         => $this->input->post('prev_school_address', TRUE) ?: NULL,
                            'school_board'           => $this->input->post('prev_school_board', TRUE) ?: NULL,
                            'previous_class'         => $this->input->post('prev_class', TRUE) ?: NULL,
                            'previous_academic_year' => $this->input->post('prev_academic_year', TRUE) ?: NULL,
                            'date_of_leaving'        => $this->input->post('date_of_leaving', TRUE) ?: NULL,
                            'reason_for_leaving'     => $this->input->post('reason_for_leaving', TRUE) ?: NULL,
                            'tc_number'              => $tc_number_val ?: NULL,
                            'tc_document_id'         => $tc_document_id ?: NULL,
                            'previous_percentage'    => strlen($this->input->post('prev_percentage')) > 0 ? (float)$this->input->post('prev_percentage') : NULL,
                            'status'                 => 1,
                        );
                        $this->Student_academic_model->save_previous_school($student_id, $prev_school_data);

                        // If TC number changed and document exists, update document record
                        if ($tc_document_id && !empty($tc_number_val)) {
                            $this->Student_academic_model->update_tc_number($tc_document_id, $tc_number_val, $this->school_id);
                        }
                    } elseif ($no_prev_school) {
                        if ($prev_school) {
                            $this->Student_academic_model->deactivate_previous_school($prev_school->prev_school_id, $this->school_id);
                        }
                    }

                    // 4. Save Academic Activities
                    $raw_academic = $this->input->post('academic_activities');
                    $academic_acts = array();
                    if (!empty($raw_academic) && is_array($raw_academic)) {
                        foreach ($raw_academic as $act) {
                            if (!empty($act['activity_name'])) {
                                $academic_acts[] = array(
                                    'category'        => 'Academic',
                                    'activity_type'   => !empty($act['activity_type']) ? $act['activity_type'] : 'Achievement',
                                    'activity_name'   => trim($act['activity_name']),
                                    'position_result' => !empty($act['position_result']) ? trim($act['position_result']) : NULL,
                                    'year'            => !empty($act['year']) && is_numeric($act['year']) ? (int)$act['year'] : (int)date('Y'),
                                    'description'     => !empty($act['description']) ? trim($act['description']) : NULL,
                                );
                            }
                        }
                    }
                    $this->Student_academic_model->save_activities($student_id, $academic_acts, 'Academic');

                    // 5. Save Extracurricular Activities
                    $raw_extra = $this->input->post('extracurricular');
                    $extra_acts = array();
                    if (!empty($raw_extra) && is_array($raw_extra)) {
                        foreach ($raw_extra as $xact) {
                            if (!empty($xact['activity_name'])) {
                                $extra_acts[] = array(
                                    'category'        => 'Extracurricular',
                                    'activity_type'   => !empty($xact['activity_type']) ? $xact['activity_type'] : 'Sports',
                                    'activity_name'   => trim($xact['activity_name']),
                                    'level'           => !empty($xact['level']) ? trim($xact['level']) : NULL,
                                    'position_result' => !empty($xact['position_result']) ? trim($xact['position_result']) : NULL,
                                    'year'            => !empty($xact['year']) && is_numeric($xact['year']) ? (int)$xact['year'] : (int)date('Y'),
                                    'description'     => !empty($xact['description']) ? trim($xact['description']) : NULL,
                                );
                            }
                        }
                    }
                    $this->Student_academic_model->save_activities($student_id, $extra_acts, 'Extracurricular');

                    $this->session->set_flashdata('success', 'Student details updated successfully.');
                    redirect('students/profile/' . $student_id);
                    return;
                }
            }
        }

        $classes   = $this->Class_model->get_all($student->academic_year_id);
        $divisions = $student->class_id ? $this->Division_model->get_by_class($student->class_id) : $this->Division_model->get_all();
        $sections  = $divisions;
        $years     = $this->Academic_year_model->get_all();
        $groups    = $this->Academic_group_model->get_all();

        // Refresh previous school, TC document, and activities for view rendering
        $prev_school           = $this->Student_academic_model->get_previous_school($student_id);
        $tc_document           = $this->Student_academic_model->get_tc_document($student_id, $prev_school ? $prev_school->tc_document_id : NULL);
        $saved_activities      = $this->Student_academic_model->get_activities($student_id, 'Academic');
        $saved_extracurricular = $this->Student_academic_model->get_activities($student_id, 'Extracurricular');
        $documents             = $this->Student_document_model->get_all(array('student_id' => (int)$student_id));

        $this->render('pages/students/edit', array(
            'title'                 => 'Edit Student',
            'page_key'              => 'students',
            'breadcrumb'            => array('Student Management', 'Edit Student'),
            'student'               => $student,
            'student_id'            => $student_id,
            'groups'                => $groups,
            'classes'               => $classes,
            'divisions'             => $divisions,
            'sections'              => $sections,
            'years'                 => $years,
            'prev_school'           => $prev_school,
            'tc_document'           => $tc_document,
            'saved_activities'      => $saved_activities,
            'saved_extracurricular' => $saved_extracurricular,
            'documents'             => $documents,
            'photo_error'           => $photo_error,
            'tc_error'              => $tc_error,
        ));
    }

    /**
     * Process uploaded TC Document for student edit flow.
     * Validates PDF, JPG, JPEG, PNG format and size limit (max 10MB).
     *
     * @param  int      $student_id
     * @param  int|null $existing_document_id
     * @param  string   $tc_number
     * @return array
     */
    protected function _process_tc_document_upload($student_id, $existing_document_id = NULL, $tc_number = '')
    {
        if (empty($_FILES['tc_document']['name'])) {
            return array('success' => TRUE, 'document_id' => $existing_document_id);
        }

        $fileError = isset($_FILES['tc_document']['error']) ? (int)$_FILES['tc_document']['error'] : UPLOAD_ERR_NO_FILE;
        if ($fileError !== UPLOAD_ERR_OK) {
            return array('success' => FALSE, 'error' => 'Unable to upload TC Document. Please check file size and permissions.');
        }

        $orig_name = $_FILES['tc_document']['name'];
        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $allowed = array('pdf', 'jpg', 'jpeg', 'png');
        if (!in_array($ext, $allowed, TRUE)) {
            return array('success' => FALSE, 'error' => 'TC Document must be a PDF, JPG, JPEG, or PNG file.');
        }

        $max_size = 10 * 1024 * 1024; // 10 MB
        if ($_FILES['tc_document']['size'] > $max_size || $_FILES['tc_document']['size'] <= 0) {
            return array('success' => FALSE, 'error' => 'TC Document must not exceed 10 MB.');
        }

        $tmp_path = $_FILES['tc_document']['tmp_name'];
        $val_check = validate_uploaded_tc_file($tmp_path, $ext);
        if (!$val_check['valid']) {
            return array('success' => FALSE, 'error' => $val_check['error']);
        }

        $dest_dir = FCPATH . 'uploads/documents/';
        if (!is_dir($dest_dir)) {
            @mkdir($dest_dir, 0755, TRUE);
        }

        try {
            $rand_token = bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $rand_token = substr(md5(uniqid(mt_rand(), true)), 0, 12);
        }
        $safe_name = 'tc_' . $student_id . '_' . time() . '_' . $rand_token . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $dest_path = $dest_dir . $safe_name;

        if (!@move_uploaded_file($tmp_path, $dest_path)) {
            return array('success' => FALSE, 'error' => 'Failed to move uploaded TC document.');
        }

        $perm_path = 'uploads/documents/' . $safe_name;

        if ($existing_document_id) {
            $this->Student_academic_model->update_tc_document($existing_document_id, $perm_path, $tc_number);
            $doc_id = (int)$existing_document_id;
        } else {
            $doc_id = $this->Student_academic_model->insert_tc_document($student_id, $perm_path, $tc_number);
        }

        return array('success' => TRUE, 'document_id' => $doc_id, 'file_path' => $perm_path);
    }

    public function remove_photo($student_id = NULL)
    {
        $this->require_permission('students.edit');

        if (empty($student_id)) {
            show_404();
            return;
        }

        $this->Student_model->delete_photo($student_id);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => TRUE, 'message' => 'Student photo removed successfully.')));
            return;
        }

        $this->session->set_flashdata('success', 'Student photo removed.');
        $redirect = $this->input->get('redirect_to') ?: ('students/edit/' . $student_id);
        redirect($redirect);
    }

    /* =========================================================================
       Private: Process and Validate Student Photo Upload / Base64 Cropped Data
       ========================================================================= */
    private function process_student_photo($student_id = NULL)
    {
        $uploadDir = FCPATH . 'uploads/students/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $maxSize = 3 * 1024 * 1024; // 3 MB
        $allowedExts = array('jpg', 'jpeg', 'png', 'webp');
        $allowedMimes = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp');
        $allowedTypes = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
        if (defined('IMAGETYPE_WEBP')) {
            $allowedTypes[] = IMAGETYPE_WEBP;
        }

        // 1. Check if cropped image payload is submitted (Base64 data URL from Cropper)
        $croppedData = $this->input->post('cropped_image_data');
        if (!empty($croppedData)) {
            if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,([A-Za-z0-9+\/=\r\n]+)$/i', $croppedData, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $decoded = base64_decode($matches[2]);

                if ($decoded === FALSE || strlen($decoded) < 50) {
                    return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
                }

                if (strlen($decoded) > $maxSize) {
                    return array('success' => FALSE, 'error' => 'Student image must not exceed 3 MB.');
                }

                $imgInfo = @getimagesizefromstring($decoded);
                if ($imgInfo === FALSE) {
                    return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
                }

                $detectedType = isset($imgInfo[2]) ? $imgInfo[2] : 0;
                $detectedMime = isset($imgInfo['mime']) ? strtolower($imgInfo['mime']) : '';
                if (!in_array($detectedType, $allowedTypes, TRUE) && !in_array($detectedMime, $allowedMimes, TRUE)) {
                    return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
                }

                $safeName = 'student_' . ($student_id ?: 'new') . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.' . $ext;
                $destPath = $uploadDir . $safeName;

                if (file_put_contents($destPath, $decoded) !== FALSE) {
                    return array('success' => TRUE, 'file_name' => $safeName);
                } else {
                    return array('success' => FALSE, 'error' => 'Unable to upload student image. Please try again.');
                }
            } else {
                return array('success' => FALSE, 'error' => 'Invalid cropped image format.');
            }
        }

        // 2. Fallback check: Direct standard file upload $_FILES['student_image']
        if (isset($_FILES['student_image']['name']) && !empty($_FILES['student_image']['name'])) {
            $fileError = isset($_FILES['student_image']['error']) ? (int)$_FILES['student_image']['error'] : UPLOAD_ERR_NO_FILE;
            if ($fileError !== UPLOAD_ERR_OK) {
                if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
                    return array('success' => FALSE, 'error' => 'Student image must not exceed 3 MB.');
                }
                return array('success' => FALSE, 'error' => 'Unable to upload student image. Please try again.');
            }

            $origName = $_FILES['student_image']['name'];
            $fileSize = $_FILES['student_image']['size'];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts, TRUE)) {
                return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
            }

            if ($fileSize > $maxSize || $fileSize <= 0) {
                return array('success' => FALSE, 'error' => 'Student image must not exceed 3 MB.');
            }

            $imgInfo = @getimagesize($_FILES['student_image']['tmp_name']);
            if ($imgInfo === FALSE) {
                return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
            }

            $detectedType = isset($imgInfo[2]) ? $imgInfo[2] : 0;
            $detectedMime = isset($imgInfo['mime']) ? strtolower($imgInfo['mime']) : '';
            if (!in_array($detectedType, $allowedTypes, TRUE) && !in_array($detectedMime, $allowedMimes, TRUE)) {
                return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
            }

            if ($ext === 'jpeg') $ext = 'jpg';
            $safeName = 'student_' . ($student_id ?: 'new') . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.' . $ext;
            $destPath = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES['student_image']['tmp_name'], $destPath)) {
                return array('success' => TRUE, 'file_name' => $safeName);
            } else {
                return array('success' => FALSE, 'error' => 'Unable to upload student image. Please try again.');
            }
        }

        return array('success' => TRUE, 'file_name' => NULL);
    }

    /* =========================================================================
       4. Student Delete (Safe Deactivation)
       ========================================================================= */
    public function delete($student_id = NULL)
    {
        $this->require_permission('students.delete');

        if (!$student_id) {
            redirect('students');
            return;
        }

        $student = $this->Student_model->get_by_id($student_id);
        if ($student) {
            $this->Student_model->soft_delete($student_id);
            $this->session->set_flashdata('success', 'Student record has been deactivated safely.');
        } else {
            $this->session->set_flashdata('error', 'Student not found.');
        }

        redirect('students');
    }

    /* =========================================================================
       5. Student Profile
       ========================================================================= */
    public function profile($student_id = NULL)
    {
        $this->require_permission('students.view');
        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }
        if (!$student_id) {
            $first = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id));
            $student_id = !empty($first) ? $first[0]->student_id : 1;
        }

        $academic_year_id = (int)($this->input->get('attendance_year_id') ?: ($this->input->get('academic_year_id') ?: ($this->academic_year_id ?: get_current_academic_year_id())));
        $ay_record = get_academic_year_record($academic_year_id);

        $from_date = $this->input->get('from_date');
        $to_date   = $this->input->get('to_date');

        if (!$from_date && $ay_record) {
            $from_date = $ay_record->start_date;
        }
        if (!$to_date && $ay_record) {
            $today = function_exists('school_today') ? school_today() : date('Y-m-d');
            $from_ts = strtotime($from_date);
            $ay_end_ts = strtotime($ay_record->end_date);
            $today_ts = strtotime($today);
            if ($today_ts >= $from_ts && $today_ts <= $ay_end_ts) {
                $to_date = $today;
            } elseif ($today_ts > $ay_end_ts) {
                $to_date = $ay_record->end_date;
            } else {
                $to_date = $ay_record->end_date;
            }
        }

        $student = $this->Student_model->get_profile($student_id, $academic_year_id, $from_date, $to_date);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student profile not found.');
            redirect('students');
            return;
        }

        $classes   = $this->Class_model->get_all($student->academic_year_id);
        $divisions = $student->class_id ? $this->Division_model->get_by_class($student->class_id) : $this->Division_model->get_all();
        $sections  = $divisions;
        $years     = $this->Academic_year_model->get_all();

        $this->render('pages/students/profile', array(
            'title'                => 'Student Profile',
            'page_key'             => 'student-profile',
            'breadcrumb'           => array('Student Management', 'Student Profile'),
            'student'              => $student,
            'student_id'           => $student_id,
            'classes'              => $classes,
            'divisions'            => $divisions,
            'sections'             => $sections,
            'years'                => $years,
            'attendance_year_id'   => $academic_year_id,
            'attendance_from_date' => $from_date,
            'attendance_to_date'   => $to_date,
            'active_ay_record'     => $ay_record,
        ));
    }

    /**
     * Download Student Attendance PDF Report
     * Route: GET students/attendance_pdf/{student_id}
     */
    public function attendance_pdf($student_id = NULL)
    {
        $this->require_permission('students.view');
        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }
        $student_id = (int)$student_id;
        if (!$student_id) {
            show_404();
            return;
        }

        $academic_year_id = (int)($this->input->get('attendance_year_id') ?: ($this->input->get('academic_year_id') ?: ($this->academic_year_id ?: get_current_academic_year_id())));
        $ay_record = get_academic_year_record($academic_year_id);

        $from_date = $this->input->get('from_date');
        $to_date   = $this->input->get('to_date');

        if (!$from_date && $ay_record) {
            $from_date = $ay_record->start_date;
        }
        if (!$to_date && $ay_record) {
            $today = function_exists('school_today') ? school_today() : date('Y-m-d');
            $from_ts = strtotime($from_date);
            $ay_end_ts = strtotime($ay_record->end_date);
            $today_ts = strtotime($today);
            if ($today_ts >= $from_ts && $today_ts <= $ay_end_ts) {
                $to_date = $today;
            } elseif ($today_ts > $ay_end_ts) {
                $to_date = $ay_record->end_date;
            } else {
                $to_date = $ay_record->end_date;
            }
        }

        // Validate Date Range
        if ($from_date && $to_date && strtotime($from_date) > strtotime($to_date)) {
            $this->session->set_flashdata('error', 'Invalid date range: From Date must be earlier than or equal to To Date.');
            redirect('students/profile/' . $student_id . '?tab=attendance');
            return;
        }

        // Fetch Student Profile & Attendance Data
        $student = $this->Student_model->get_profile($student_id, $academic_year_id, $from_date, $to_date);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student profile not found.');
            redirect('students');
            return;
        }

        $settings = $this->Setting_model->get_settings();

        $this->load->library('Attendance_pdf_service');
        $output_mode = ($this->input->get('stream') === '1' || $this->input->get('preview') === '1') ? 'I' : 'D';
        $this->attendance_pdf_service->generate($student, $settings, $ay_record, $from_date, $to_date, $output_mode);
    }

    /**
     * Dedicated Student Overall Report Preview
     * Route: GET students/overall_report/{student_id}
     */
    public function overall_report($student_id = NULL)
    {
        $this->require_permission('students.view');
        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }
        $student_id = (int)$student_id;
        if (!$student_id) {
            show_404();
            return;
        }

        $report_data = $this->_gather_overall_report_data($student_id);
        if (!$report_data) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('students');
            return;
        }

        $report_data['title']      = 'Student Overall Report - ' . school_student_name($report_data['student']->first_name, $report_data['student']->middle_name ?? '', $report_data['student']->last_name);
        $report_data['page_key']   = 'student-overall-report';
        $report_data['breadcrumb'] = array('Student Management', 'Student Profile', 'Overall Report');

        $this->render('pages/students/overall_report', $report_data);
    }

    /**
     * Download Student Overall Report PDF
     * Route: GET students/overall_report_pdf/{student_id}
     */
    public function overall_report_pdf($student_id = NULL)
    {
        $this->require_permission('students.view');
        if (!$student_id) {
            $student_id = $this->input->get('student_id');
        }
        $student_id = (int)$student_id;
        if (!$student_id) {
            show_404();
            return;
        }

        $report_data = $this->_gather_overall_report_data($student_id);
        if (!$report_data) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('students');
            return;
        }

        $this->load->library('Overall_report_pdf_service');
        $output_mode = ($this->input->get('stream') === '1' || $this->input->get('preview') === '1') ? 'I' : 'D';

        $this->overall_report_pdf_service->generate(
            $report_data['student'],
            $report_data['settings'],
            $report_data['academic_year'],
            $report_data['exam_reports'],
            $report_data['attendance_data'],
            $report_data['fee_summary'],
            $report_data['teacher_name'],
            $report_data['principal_name'],
            $output_mode
        );
    }

    /**
     * Helper to gather consolidated data for the Student Overall Report
     */
    protected function _gather_overall_report_data($student_id)
    {
        $student_id = (int)$student_id;
        if (!$student_id) return NULL;

        $this->load->model('Student_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Attendance_model');
        $this->load->model('Exam_model');
        $this->load->model('Exam_mark_model');
        $this->load->model('Result_model');
        $this->load->model('Setting_model');

        // 1. Fetch Student Basic Record
        $raw_student = $this->Student_model->get_by_id($student_id);
        if (!$raw_student) return NULL;

        // Resolve active academic year context
        $academic_year_id = !empty($raw_student->academic_year_id) ? (int)$raw_student->academic_year_id : (int)get_current_academic_year_id();
        $academic_year = get_academic_year_record($academic_year_id);

        // 2. Load Full Profile (documents, promotions, attendance)
        $student = $this->Student_model->get_profile($student_id, $academic_year_id);
        if (!$student) $student = $raw_student;

        if (!isset($student->division_name) && isset($student->section_name)) {
            $student->division_name = $student->section_name;
        }

        // 3. School Settings (Strictly Kaloor)
        $settings = $this->Setting_model->get_settings();
        $school_name = !empty($settings->school_name) ? $settings->school_name : 'Login2 School';
        $raw_address = !empty($settings->address) ? $settings->address : 'Kaloor, Ernakulam, Kerala - 682030';
        $school_address = str_ireplace('Kakkanad', 'Kaloor', $raw_address);

        $contact_parts = array();
        if (!empty($settings->phone))    $contact_parts[] = 'Phone: ' . $settings->phone;
        if (!empty($settings->email))    $contact_parts[] = 'Email: ' . $settings->email;
        if (!empty($settings->website))  $contact_parts[] = 'Web: ' . $settings->website;
        $school_contact = implode(' | ', $contact_parts);

        // School Logo Data URI
        $school_logo_src = '';
        $logo_path = '';
        if (!empty($settings->logo) && file_exists(FCPATH . 'uploads/settings/' . $settings->logo)) {
            $logo_path = FCPATH . 'uploads/settings/' . $settings->logo;
        } elseif (!empty($settings->school_logo) && file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo)) {
            $logo_path = FCPATH . 'uploads/settings/' . $settings->school_logo;
        } elseif (!empty($settings->logo) && file_exists(FCPATH . 'uploads/id_card/' . $settings->logo)) {
            $logo_path = FCPATH . 'uploads/id_card/' . $settings->logo;
        } elseif (file_exists(FCPATH . 'assets/logo.png')) {
            $logo_path = FCPATH . 'assets/logo.png';
        }
        if (!empty($logo_path) && file_exists($logo_path)) {
            $ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));
            $data = @file_get_contents($logo_path);
            if ($data !== false) {
                $mime = ($ext === 'svg') ? 'svg+xml' : ($ext === 'jpg' ? 'jpeg' : $ext);
                $school_logo_src = 'data:image/' . $mime . ';base64,' . base64_encode($data);
            }
        }

        // Student Photo Data URI
        $student_photo_src = '';
        if (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo)) {
            $photo_path = FCPATH . 'uploads/students/' . $student->photo;
            $ext = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));
            $p_data = @file_get_contents($photo_path);
            if ($p_data !== false) {
                $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                $student_photo_src = 'data:image/' . $mime . ';base64,' . base64_encode($p_data);
            }
        }

        // 4. Centralized Asia/Kolkata Timestamp
        $tz = new DateTimeZone('Asia/Kolkata');
        $now = new DateTime('now', $tz);
        $generated_at = $now->format('d M Y, h:i A');

        // 5. Attendance Type Detection (Database-Driven: Daily vs Period)
        $is_period_group = (get_attendance_workflow_type($student ?: ($student->class_id ?? 0)) === 'period');
        $is_ss           = $is_period_group;

        // 6. Attendance Summary
        $from_date = $academic_year ? $academic_year->start_date : NULL;
        $attendance_data = $this->Attendance_model->get_student_profile_attendance($student_id, $academic_year_id, $from_date, NULL);
        if (!$attendance_data) {
            $attendance_data = (object) array(
                'day_summary'    => (object) array('working_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'percentage' => 0.0),
                'day_monthly'    => array(),
                'period_summary' => (object) array('total_periods' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'percentage' => 0.0),
                'subject_summary'=> array(),
                'is_higher_sec'  => $is_period_group
            );
        }
        $attendance_data->is_ss = $is_period_group;

        // 7. Exam Results for Current Academic Year
        $exams = $this->Exam_model->get_all(['academic_year_id' => $academic_year_id]);
        $exam_reports = array();

        foreach ($exams as $ex) {
            $marks = $this->Exam_mark_model->get_student_subject_marks($ex->exam_id, $student_id);
            if (!empty($marks)) {
                $exam_res = $this->Result_model->get_student_exam_result($student_id, $ex->exam_id);

                $total_marks = 0.0;
                $max_marks = 0.0;
                foreach ($marks as $m) {
                    if (empty($m->is_exempted)) {
                        $total_marks += (float)($m->marks_obtained ?? 0);
                        $max_marks += (float)($m->max_marks ?? 100);
                    }
                }
                $pct = ($max_marks > 0) ? round(($total_marks / $max_marks) * 100, 1) : 0.0;

                $exam_reports[] = (object) array(
                    'exam_id'       => $ex->exam_id,
                    'exam_name'     => $ex->exam_name,
                    'exam_date'     => $ex->start_date,
                    'subject_marks' => $marks,
                    'total_marks'   => $exam_res ? (float)$exam_res->total_marks : $total_marks,
                    'max_marks'     => $exam_res ? (float)$exam_res->max_marks : $max_marks,
                    'percentage'    => $exam_res ? (float)$exam_res->percentage : $pct,
                    'overall_grade' => $exam_res ? $exam_res->overall_grade : ($marks[0]->grade ?? '—'),
                    'pass_status'   => $exam_res ? $exam_res->pass_status : ($pct >= 35.0 ? 'Pass' : 'Fail'),
                    'class_rank'    => $exam_res ? $exam_res->class_rank : NULL,
                );
            }
        }

        // 8. Fee Summary for Current Academic Year (from new Finance Foundation)
        $profile_fee_data = $this->Student_model->get_student_fee_profile($student_id, $this->school_id);
        $fee_summary = $profile_fee_data->summary;

        // 9. Class Teacher Lookup
        $teacher_name = '';
        if (!empty($student->division_id)) {
            $div_row = $this->Division_model->get_by_id($student->division_id, $this->school_id);
            if ($div_row && !empty($div_row->class_teacher_name)) {
                $teacher_name = $div_row->class_teacher_name;
            }
        }
        if (empty($teacher_name) && !empty($student->class_id)) {
            $cls_row = $this->Class_model->get_by_id($student->class_id, $this->school_id);
            if ($cls_row && !empty($cls_row->class_teacher_id)) {
                $staff = $this->Staff_model->get_by_id($cls_row->class_teacher_id, $this->school_id);
                if ($staff) {
                    $teacher_name = !empty($staff->full_name) ? $staff->full_name : '';
                }
            }
        }

        $principal_name = !empty($settings->principal_name) ? $settings->principal_name : 'Principal';

        return array(
            'student'           => $student,
            'settings'          => $settings,
            'academic_year'     => $academic_year,
            'school_name'       => $school_name,
            'school_address'    => $school_address,
            'school_contact'    => $school_contact,
            'school_logo_src'   => $school_logo_src,
            'student_photo_src' => $student_photo_src,
            'generated_at'      => $generated_at,
            'is_ss'             => $is_ss,
            'attendance_data'   => $attendance_data,
            'exam_reports'      => $exam_reports,
            'fee_summary'       => $fee_summary,
            'teacher_name'      => $teacher_name,
            'principal_name'    => $principal_name,
        );
    }

    /* =========================================================================
       5B. Bulk Student Management
       ========================================================================= */

    /**
     * Bulk Student Add Page (Dual Mode: CSV/Excel Import & Bulk Entry).
     */
    public function bulk_add()
    {
        $this->require_permission('students.create');

        $selected_year = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        $classes = $this->Class_model->get_all($selected_year);
        $years   = $this->Academic_year_model->get_all();

        $selected_class = $this->input->get('class_id') ? (int)$this->input->get('class_id') : (!empty($classes) ? $classes[0]->class_id : NULL);
        $sections = $selected_class ? $this->Section_model->get_sections_for_class($selected_class) : array();

        $this->render('pages/students/bulk_add', array(
            'title'          => 'Bulk Student Add',
            'page_key'       => 'student-bulk-add',
            'breadcrumb'     => array('Student Management', 'Bulk Student Add'),
            'years'          => $years,
            'classes'        => $classes,
            'sections'       => $sections,
            'selected_year'  => $selected_year,
            'selected_class' => $selected_class,
        ));
    }

    /**
     * Download Sample CSV Import Template.
     */
    public function bulk_template()
    {
        $this->require_permission('students.view');

        $filename = 'student_bulk_import_template.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($out, "\xEF\xBB\xBF");

        // Headers
        fputcsv($out, array(
            'admission_number',
            'first_name',
            'middle_name',
            'last_name',
            'date_of_birth',
            'gender',
            'blood_group',
            'guardian_name',
            'guardian_relation',
            'guardian_phone',
            'guardian_email',
            'address',
            'roll_number'
        ));

        // Sample Rows
        fputcsv($out, array(
            'EDU2026001',
            'Aarav',
            '',
            'Verma',
            '2012-05-14',
            'Male',
            'B+',
            'Rajesh Verma',
            'Father',
            '+91 9876543210',
            'rajesh.verma@example.com',
            'Kochi, Kerala',
            '101'
        ));

        fputcsv($out, array(
            '',
            'Ananya',
            'K',
            'Nair',
            '2013-08-20',
            'Female',
            'O+',
            'Suresh Nair',
            'Father',
            '+91 9876543211',
            'suresh.nair@example.com',
            'Ernakulam, Kerala',
            '102'
        ));

        fclose($out);
        exit;
    }

    /**
     * AJAX: Parse & Validate uploaded Excel/CSV file.
     */
    public function bulk_validate_ajax()
    {
        $this->require_permission('students.create');

        $academic_year_id = (int)$this->input->post('academic_year_id');
        $class_id         = (int)$this->input->post('class_id');
        $section_id       = (int)($this->input->post('division_id') ?: $this->input->post('section_id'));

        if (!$academic_year_id || !$class_id) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Please select Academic Year and Class before uploading.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        if (empty($_FILES['import_file']['name'])) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Please select an Excel (.xlsx) or CSV (.csv) file to upload.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $orig_name = $_FILES['import_file']['name'];
        $tmp_path  = $_FILES['import_file']['tmp_name'];
        $file_size = (int)$_FILES['import_file']['size'];
        $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        $allowed_exts = array('csv', 'xlsx', 'xls', 'txt');
        if (!in_array($ext, $allowed_exts, true)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Unsupported file type. Please upload a .csv or .xlsx file.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        if ($file_size > 10 * 1024 * 1024) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'File size exceeds maximum limit of 10 MB.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        try {
            $this->load->library('Simple_excel_reader');
            $raw_rows = $this->simple_excel_reader->parse_file($tmp_path, $orig_name);

            if (empty($raw_rows)) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status'  => false,
                    'message' => 'Uploaded file is empty or does not contain valid student rows.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                )));
            }

            $validation = $this->Student_model->bulk_validate_students($raw_rows, $academic_year_id, $class_id, $section_id);

            // Cache validated data in session for instant import
            $this->session->set_userdata('bulk_import_pending', array(
                'academic_year_id' => $academic_year_id,
                'class_id'         => $class_id,
                'section_id'       => $section_id,
                'rows'             => $validation['rows']
            ));

            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => true,
                'message'         => 'File parsed and validated successfully.',
                'total_count'     => $validation['total_count'],
                'valid_count'     => $validation['valid_count'],
                'error_count'     => $validation['error_count'],
                'rows'            => $validation['rows'],
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        } catch (Exception $e) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Error parsing file: ' . $e->getMessage(),
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }
    }

    /**
     * AJAX: Revalidate an individual edited student row in the pending bulk import dataset.
     */
    public function bulk_revalidate_row_ajax()
    {
        $this->require_permission('students.create');

        $academic_year_id = (int)$this->input->post('academic_year_id');
        $class_id         = (int)$this->input->post('class_id');
        $section_id       = (int)($this->input->post('division_id') ?: $this->input->post('section_id'));
        $row_num          = (int)$this->input->post('row_num');

        // Edited fields from client
        $edited_data = array(
            'first_name'        => trim((string)$this->input->post('first_name', TRUE)),
            'middle_name'       => trim((string)$this->input->post('middle_name', TRUE)),
            'last_name'         => trim((string)$this->input->post('last_name', TRUE)),
            'date_of_birth'     => trim((string)$this->input->post('date_of_birth', TRUE)),
            'gender'            => trim((string)$this->input->post('gender', TRUE)),
            'blood_group'       => trim((string)$this->input->post('blood_group', TRUE)),
            'guardian_name'     => trim((string)$this->input->post('guardian_name', TRUE)),
            'guardian_relation' => trim((string)$this->input->post('guardian_relation', TRUE)),
            'guardian_phone'    => trim((string)$this->input->post('guardian_phone', TRUE)),
            'guardian_email'    => trim((string)$this->input->post('guardian_email', TRUE)),
            'address'           => trim((string)$this->input->post('address', TRUE)),
            'admission_number'  => trim((string)$this->input->post('admission_number', TRUE)),
            'roll_number'       => trim((string)$this->input->post('roll_number', TRUE)),
        );

        // Fetch pending batch rows from session
        $pending = $this->session->userdata('bulk_import_pending');
        $all_rows = array();

        if ($pending && !empty($pending['rows']) && is_array($pending['rows'])) {
            $all_rows = $pending['rows'];
            if (!empty($pending['academic_year_id'])) $academic_year_id = (int)$pending['academic_year_id'];
            if (!empty($pending['class_id'])) $class_id = (int)$pending['class_id'];
            if (!empty($pending['section_id'])) $section_id = (int)$pending['section_id'];
        } else {
            // Fallback: Check posted all_rows if session was not populated
            $raw_all = $this->input->post('all_rows');
            if (!empty($raw_all)) {
                $decoded = json_decode($raw_all, true);
                if (is_array($decoded)) {
                    $all_rows = $decoded;
                }
            }
        }

        if (empty($all_rows)) {
            $all_rows = array($edited_data);
            $target_index = 0;
        } else {
            // Locate the target row by row_num
            $target_index = -1;
            foreach ($all_rows as $idx => $r) {
                $current_row_num = isset($r['row_num']) ? (int)$r['row_num'] : ($idx + 1);
                if ($current_row_num === $row_num) {
                    $target_index = $idx;
                    break;
                }
            }
            if ($target_index === -1) {
                $target_index = $row_num > 0 ? ($row_num - 1) : 0;
            }
        }

        // Prepare raw rows for re-validation by bulk_validate_students
        $raw_rows_to_validate = array();
        foreach ($all_rows as $idx => $r) {
            if ($idx === $target_index) {
                $raw_rows_to_validate[] = $edited_data;
            } else {
                $raw_rows_to_validate[] = array(
                    'first_name'        => $r['first_name'] ?? '',
                    'middle_name'       => $r['middle_name'] ?? '',
                    'last_name'         => $r['last_name'] ?? '',
                    'date_of_birth'     => $r['date_of_birth'] ?? '',
                    'gender'            => $r['gender'] ?? '',
                    'blood_group'       => $r['blood_group'] ?? '',
                    'guardian_name'     => $r['guardian_name'] ?? '',
                    'guardian_relation' => $r['guardian_relation'] ?? '',
                    'guardian_phone'    => $r['guardian_phone'] ?? '',
                    'guardian_email'    => $r['guardian_email'] ?? '',
                    'address'           => $r['address'] ?? '',
                    'admission_number'  => $r['admission_number'] ?? '',
                    'roll_number'       => $r['roll_number'] ?? '',
                );
            }
        }

        // Revalidate using the exact same Student_model->bulk_validate_students
        $validation = $this->Student_model->bulk_validate_students(
            $raw_rows_to_validate,
            $academic_year_id,
            $class_id,
            $section_id
        );

        $validated_target_row = isset($validation['rows'][$target_index]) ? $validation['rows'][$target_index] : null;

        if (!$validated_target_row || !$validated_target_row['is_valid']) {
            // Validation failed for the edited row: return errors, DO NOT commit to session
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'is_valid'        => false,
                'errors'          => $validated_target_row ? $validated_target_row['errors'] : array('Validation failed for edited student row.'),
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Validation passed! Update session cache with all newly validated rows
        $this->session->set_userdata('bulk_import_pending', array(
            'academic_year_id' => $academic_year_id,
            'class_id'         => $class_id,
            'section_id'       => $section_id,
            'rows'             => $validation['rows']
        ));

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'          => true,
            'is_valid'        => true,
            'message'         => 'Row revalidated and updated successfully.',
            'updated_row'     => $validated_target_row,
            'total_count'     => $validation['total_count'],
            'valid_count'     => $validation['valid_count'],
            'error_count'     => $validation['error_count'],
            'rows'            => $validation['rows'],
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        )));
    }

    /**
     * AJAX: Import validated rows from uploaded file into database.
     */
    public function bulk_import_ajax()
    {
        $this->require_permission('students.create');

        $academic_year_id = (int)$this->input->post('academic_year_id');
        $class_id         = (int)$this->input->post('class_id');
        $section_id       = (int)($this->input->post('division_id') ?: $this->input->post('section_id'));

        $pending = $this->session->userdata('bulk_import_pending');
        $rows_to_insert = array();

        if ($pending && !empty($pending['rows'])) {
            foreach ($pending['rows'] as $r) {
                $g_phone = isset($r['guardian_phone']) ? trim((string)$r['guardian_phone']) : '';
                if (!empty($r['is_valid']) && $g_phone !== '' && $g_phone !== '—' && $g_phone !== '-') {
                    $rows_to_insert[] = $r;
                }
            }
            if (!empty($pending['academic_year_id'])) $academic_year_id = (int)$pending['academic_year_id'];
            if (!empty($pending['class_id'])) $class_id = (int)$pending['class_id'];
            if (!empty($pending['section_id'])) $section_id = (int)$pending['section_id'];
        }

        // Fallback: Check posted JSON rows
        if (empty($rows_to_insert)) {
            $raw_posted = $this->input->post('valid_rows');
            if (!empty($raw_posted) && is_array($raw_posted)) {
                $validation = $this->Student_model->bulk_validate_students($raw_posted, $academic_year_id, $class_id, $section_id, (int)$this->school_id);
                foreach ($validation['rows'] as $r) {
                    $g_phone = isset($r['guardian_phone']) ? trim((string)$r['guardian_phone']) : '';
                    if (!empty($r['is_valid']) && $g_phone !== '' && $g_phone !== '—' && $g_phone !== '-') {
                        $rows_to_insert[] = $r;
                    }
                }
            }
        }

        if (empty($rows_to_insert)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'No valid student records with required contact numbers found to import.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $result = $this->Student_model->bulk_insert_students($rows_to_insert, $academic_year_id, $class_id, $section_id, (int)$this->school_id);

        // Clear session cache
        $this->session->unset_userdata('bulk_import_pending');

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'           => $result['success'],
            'message'          => $result['message'],
            'inserted_count'   => $result['inserted_count'],
            'academic_year_id' => $academic_year_id,
            'class_id'         => $class_id,
            'section_id'       => $section_id,
            'csrf_token_name'  => $this->security->get_csrf_token_name(),
            'csrf_hash'        => $this->security->get_csrf_hash()
        )));
    }

    /**
     * AJAX: Save direct spreadsheet-style bulk entry rows.
     */
    public function bulk_entry_save_ajax()
    {
        $this->require_permission('students.create');

        $academic_year_id = (int)$this->input->post('academic_year_id');
        $class_id         = (int)$this->input->post('class_id');
        $division_id      = (int)($this->input->post('division_id') ?: $this->input->post('section_id'));
        $section_id       = $division_id;
        $raw_entries      = $this->input->post('entries');

        if (!$academic_year_id || !$class_id) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Please select Academic Year and Class.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        if (empty($raw_entries) || !is_array($raw_entries)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Please enter at least one student in the table.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Filter out completely blank rows
        $filtered_entries = array();
        foreach ($raw_entries as $e) {
            if (!empty($e['first_name']) || !empty($e['last_name']) || !empty($e['guardian_name']) || !empty($e['admission_number'])) {
                $filtered_entries[] = $e;
            }
        }

        if (empty($filtered_entries)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'All entered rows are blank. Please enter student details.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $validation = $this->Student_model->bulk_validate_students($filtered_entries, $academic_year_id, $class_id, $section_id, (int)$this->school_id);

        if ($validation['error_count'] > 0) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Validation failed on ' . $validation['error_count'] . ' row(s). Please review the errors.',
                'total_count'     => $validation['total_count'],
                'valid_count'     => $validation['valid_count'],
                'error_count'     => $validation['error_count'],
                'rows'            => $validation['rows'],
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $result = $this->Student_model->bulk_insert_students($validation['rows'], $academic_year_id, $class_id, $section_id, (int)$this->school_id);

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'           => $result['success'],
            'message'          => $result['message'],
            'inserted_count'   => $result['inserted_count'],
            'academic_year_id' => $academic_year_id,
            'class_id'         => $class_id,
            'section_id'       => $section_id,
            'csrf_token_name'  => $this->security->get_csrf_token_name(),
            'csrf_hash'        => $this->security->get_csrf_hash()
        )));
    }

    /* =========================================================================
       6. Admission Management
       ========================================================================= */
    public function admissions()
    {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');

            if ($action === 'new_admission') {
                $appNo = 'APP' . date('Y') . sprintf('%03d', rand(100, 999));
                $adm_phone_raw = $this->input->post('guardian_phone', TRUE);
                $adm_phone_country = $this->input->post('guardian_phone_country', TRUE) ?: 'IN';
                $adm_phone_check = $this->phone_validator->validate_and_normalize($adm_phone_raw, $adm_phone_country, true);
                if (!$adm_phone_check['valid']) {
                    $this->session->set_flashdata('error', $adm_phone_check['error']);
                    redirect('students/admissions');
                    return;
                }

                $admData = array(
                    'application_number' => $appNo,
                    'first_name'        => $this->input->post('first_name', TRUE),
                    'last_name'         => $this->input->post('last_name', TRUE),
                    'gender'            => $this->input->post('gender', TRUE),
                    'date_of_birth'     => $this->input->post('date_of_birth', TRUE) ?: date('Y-m-d'),
                    'blood_group'       => $this->input->post('blood_group', TRUE),
                    'academic_year_id'  => $this->input->post('academic_year_id') ?: $this->academic_year_id,
                    'class_id'          => $this->input->post('class_id') ?: 1,
                    'guardian_name'     => $this->input->post('guardian_name', TRUE),
                    'guardian_relation' => $this->input->post('guardian_relation', TRUE) ?: 'Father',
                    'guardian_phone'    => $adm_phone_check['normalized'],
                    'guardian_email'    => $this->input->post('guardian_email', TRUE),
                    'address'           => $this->input->post('address', TRUE),
                    'application_date'  => date('Y-m-d'),
                    'status'            => 'Pending',
                    'created_at'        => date('Y-m-d H:i:s')
                );
                $this->Student_model->add_admission($admData);
                $this->session->set_flashdata('success', 'New admission application submitted successfully (App No: ' . $appNo . ').');
                redirect('students/admissions');
                return;
            }

            if ($action === 'admit') {
                $admission_id = $this->input->post('admission_id');
                $section_id   = $this->input->post('section_id');
                $roll_number  = $this->input->post('roll_number');

                $new_student_id = $this->Student_model->convert_admission_to_student($admission_id, $section_id, $roll_number);
                if ($new_student_id) {
                    $this->session->set_flashdata('success', 'Student admitted and registered successfully.');
                    redirect('students/profile/' . $new_student_id);
                    return;
                }
            }

            if ($action === 'update_status') {
                $admission_id = $this->input->post('admission_id');
                $status       = $this->input->post('status');
                $this->Student_model->update_admission_status($admission_id, $status);
                $this->session->set_flashdata('success', 'Admission status updated to ' . $status . '.');
                redirect('students/admissions');
                return;
            }
        }

        $filters = array(
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'status'           => $this->input->get('status'),
            'class_id'         => $this->input->get('class_id'),
            'search'           => $this->input->get('search'),
        );

        $admissions = $this->Student_model->get_admissions($filters);
        $classes    = $this->Class_model->get_all($filters['academic_year_id']);
        $sections   = $this->Section_model->get_all();
        $years      = $this->Academic_year_model->get_all();

        $this->render('pages/students/admissions', array(
            'title'      => 'Admission Management',
            'page_key'   => 'admissions',
            'admissions' => $admissions,
            'classes'    => $classes,
            'sections'   => $sections,
            'years'      => $years,
            'filters'    => $filters,
        ));
    }

    /* =========================================================================
       7. Student Documents
       ========================================================================= */
    public function documents()
    {
        $this->require_permission('students.view');
        $filters = array(
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'student_id'       => $this->input->get('student_id'),
            'class_id'         => $this->input->get('class_id'),
            'document_type'    => $this->input->get('document_type'),
        );

        $documents = $this->Student_model->get_all_documents($filters);
        $students  = $this->Student_model->get_all(array('academic_year_id' => $filters['academic_year_id']));
        $classes   = $this->Class_model->get_all($filters['academic_year_id']);

        $this->render('pages/students/documents', array(
            'title'     => 'Student Documents',
            'page_key'  => 'student-documents',
            'documents' => $documents,
            'students'  => $students,
            'classes'   => $classes,
            'filters'   => $filters,
        ));
    }

    public function upload_document($student_id = NULL)
    {
        $this->require_permission('students.edit');
        if ($this->input->method() === 'post') {
            $student_id = $this->input->post('student_id') ?: $student_id;
            $docType    = $this->input->post('document_type', TRUE) ?: 'Other';
            $docName    = trim((string)$this->input->post('document_name', TRUE));
            $referrer   = $this->input->post('redirect_to');

            // Determine redirect target with #documents anchor if targeting student profile
            $redirectTarget = $referrer ?: ('students/profile/' . $student_id . '#documents');
            if (strpos($redirectTarget, 'profile') !== false && strpos($redirectTarget, '#') === false) {
                $redirectTarget .= '#documents';
            }

            if (empty($_FILES['document_file']['name'])) {
                $this->session->set_flashdata('error', 'Please select a document file to upload.');
                redirect($redirectTarget);
                return;
            }

            // Centralized server-side validation (extension + deep MIME detection)
            $validation = validate_student_document_file($_FILES['document_file']);
            if (!$validation['valid']) {
                $this->session->set_flashdata('error', $validation['error']);
                redirect($redirectTarget);
                return;
            }

            $uploadDir = FCPATH . 'uploads/documents/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $safeExt = $validation['ext'];
            $cleanBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['document_file']['name'], PATHINFO_FILENAME));
            $fileName = time() . '_' . substr($cleanBase, 0, 50) . '.' . $safeExt;
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['document_file']['tmp_name'], $targetPath)) {
                $this->session->set_flashdata('error', 'Failed to save the uploaded document. Please try again.');
                redirect($redirectTarget);
                return;
            }

            $filePath = 'uploads/documents/' . $fileName;
            if (empty($docName)) {
                $docName = pathinfo($_FILES['document_file']['name'], PATHINFO_FILENAME) ?: $docType;
            }

            $data = array(
                'student_id'    => $student_id,
                'document_type' => $docType,
                'document_name' => $docName,
                'file_path'     => $filePath,
                'status'        => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            );
            $this->Student_model->add_document($data);
            $this->session->set_flashdata('success', 'Document uploaded successfully.');

            redirect($redirectTarget);
            return;
        }

        redirect('students');
    }

    public function delete_document($document_id = NULL)
    {
        $this->require_permission('students.edit');
        if ($document_id) {
            $this->Student_model->delete_document($document_id);
            $this->session->set_flashdata('success', 'Document removed successfully.');
        }
        $redirect = $this->input->get('redirect_to');
        redirect($redirect ?: 'students/documents');
    }

    /* =========================================================================
       8. Student ID Cards & Services
       ========================================================================= */
    public function id_cards()
    {
        $this->require_permission('students.view');
        $student_id  = $this->input->get('student_id');
        $class_id    = $this->input->get('class_id');
        // Support both ?division_id= and legacy ?section_id= in URL
        $division_id = $this->input->get('division_id') ?: $this->input->get('section_id');

        $selected_student = null;
        if (!empty($student_id)) {
            $selected_student = $this->Id_card_model->get_student_card_data((int)$student_id);
            if ($selected_student && empty($class_id)) {
                $class_id    = $selected_student->class_id;
                $division_id = $selected_student->division_id;
            }
        }

        $students = $this->Student_model->get_all(array(
            'academic_year_id' => $this->academic_year_id,
            'class_id'         => $class_id,
            'division_id'      => $division_id,
            'status'           => 1
        ));

        $classes   = $this->Class_model->get_all($this->academic_year_id);
        $divisions = $this->Division_model->get_all($class_id ?: null);
        $settings  = $this->Id_card_model->get_settings();

        if (!$selected_student && !empty($students)) {
            $selected_student = $this->Id_card_model->get_student_card_data($students[0]->student_id);
        }

        $this->load->library('document_design_service');
        $id_card_design = $this->document_design_service->resolve_design($this->school_id, 'id_card');

        $this->render('pages/students/id_cards', array(
            'title'             => 'Student ID Cards',
            'page_key'          => 'student-id-cards',
            'students'          => $students,
            'classes'           => $classes,
            'sections'          => $divisions, // kept as 'sections' for view backward-compat
            'settings'          => $settings,
            'id_card_design'    => $id_card_design,
            'selected_class'    => $class_id,
            'selected_section'  => $division_id, // kept as 'selected_section' for view backward-compat
            'selected_division' => $division_id,
            'selected_student'  => $selected_student,
        ));
    }

    /**
     * AJAX endpoint to fetch fresh, complete data for a single student ID Card.
     */
    public function id_card_preview_ajax()
    {
        $this->require_permission('students.view');
        $student_id = (int)$this->input->get_post('student_id');

        if (!$student_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Student ID is required.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        $student = $this->Id_card_model->get_student_card_data($student_id);
        if (!$student) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Student record not found.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        $settings = $this->Id_card_model->get_settings();

        // Calculate initials for fallback photo
        $nameParts = explode(' ', trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name));
        $initials = '';
        foreach ($nameParts as $np) {
            if (!empty($np)) $initials .= strtoupper($np[0]);
        }
        $initials = substr($initials, 0, 2);

        // Photo URL resolution
        $photo_url = '';
        $has_photo = false;
        if (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo)) {
            $photo_url = base_url('uploads/students/' . $student->photo);
            $has_photo = true;
        }

        // Logo URL resolution
        $logo_url = '';
        if (!empty($settings->school_logo)) {
            if (file_exists(FCPATH . 'uploads/id_card/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/id_card/' . $settings->school_logo);
            } elseif (file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/settings/' . $settings->school_logo);
            }
        }
        if (empty($logo_url) && file_exists(FCPATH . 'assets/logo.png')) {
            $logo_url = base_url('assets/logo.png');
        }

        // Signature URL resolution
        $signature_url = '';
        if (!empty($settings->principal_signature) && file_exists(FCPATH . 'uploads/id_card/' . $settings->principal_signature)) {
            $signature_url = base_url('uploads/id_card/' . $settings->principal_signature);
        }

        $this->load->library('document_design_service');
        $id_card_design = $this->document_design_service->resolve_design($this->school_id, 'id_card');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'         => true,
                'student'        => $student,
                'full_name'      => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'initials'       => $initials ?: 'ST',
                'photo_url'      => $photo_url,
                'has_photo'      => $has_photo,
                'dob_formatted'  => !empty($student->date_of_birth) ? date('d-m-Y', strtotime($student->date_of_birth)) : 'N/A',
                'class_display'  => trim(($student->class_name ?: '') . ' ' . ($student->section_name ?: '')),
                'settings'       => $settings,
                'id_card_design' => [
                    'has_front'    => !empty($id_card_design->has_front),
                    'front_url'    => !empty($id_card_design->front_url) ? $id_card_design->front_url : '',
                    'has_back'     => !empty($id_card_design->has_back),
                    'back_url'     => !empty($id_card_design->back_url) ? $id_card_design->back_url : '',
                    'field_config' => !empty($id_card_design->field_config) ? $id_card_design->field_config : null,
                ],
                'logo_url'        => $logo_url,
                'signature_url'   => $signature_url,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint to fetch data for multiple students for bulk rendering.
     */
    public function id_card_bulk_data_ajax()
    {
        $this->require_permission('students.view');
        $student_ids = $this->input->post('student_ids');

        if (empty($student_ids) || !is_array($student_ids)) {
            $class_id    = $this->input->post('class_id');
            $division_id = $this->input->post('division_id') ?: $this->input->post('section_id');
            $raw_students = $this->Student_model->get_all([
                'academic_year_id' => $this->academic_year_id,
                'class_id'         => $class_id,
                'division_id'      => $division_id,
                'status'           => 1
            ]);
            $student_ids = array_map(function($st) { return $st->student_id; }, $raw_students);
        }

        if (empty($student_ids)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'No students selected for bulk generation.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        $students = $this->Id_card_model->get_students_bulk($student_ids);
        $settings = $this->Id_card_model->get_settings();

        // Logo URL resolution
        $logo_url = '';
        if (!empty($settings->school_logo)) {
            if (file_exists(FCPATH . 'uploads/id_card/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/id_card/' . $settings->school_logo);
            } elseif (file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo)) {
                $logo_url = base_url('uploads/settings/' . $settings->school_logo);
            }
        }

        // Signature URL resolution
        $signature_url = '';
        if (!empty($settings->principal_signature) && file_exists(FCPATH . 'uploads/id_card/' . $settings->principal_signature)) {
            $signature_url = base_url('uploads/id_card/' . $settings->principal_signature);
        }

        $formatted = [];
        foreach ($students as $st) {
            $nameParts = explode(' ', trim($st->first_name . ' ' . $st->middle_name . ' ' . $st->last_name));
            $initials = '';
            foreach ($nameParts as $np) {
                if (!empty($np)) $initials .= strtoupper($np[0]);
            }
            $initials = substr($initials, 0, 2);

            $photo_url = '';
            $has_photo = false;
            if (!empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo)) {
                $photo_url = base_url('uploads/students/' . $st->photo);
                $has_photo = true;
            }

            $formatted[] = [
                'student_id'       => $st->student_id,
                'admission_number' => $st->admission_number,
                'roll_number'      => $st->roll_number ?: 'N/A',
                'full_name'        => trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name),
                'initials'         => $initials ?: 'ST',
                'gender'           => $st->gender ?: 'N/A',
                'blood_group'      => $st->blood_group ?: 'N/A',
                'dob_formatted'    => !empty($st->date_of_birth) ? date('d-m-Y', strtotime($st->date_of_birth)) : 'N/A',
                'class_display'    => trim(($st->class_name ?: '') . ' ' . ($st->section_name ?: '')),
                'year_name'        => $st->year_name ?: (get_active_academic_year(TRUE)->year_name ?? '—'),
                'guardian_name'    => $st->guardian_name ?: 'N/A',
                'guardian_phone'   => $st->guardian_phone ?: 'N/A',
                'photo_url'        => $photo_url,
                'has_photo'        => $has_photo,
            ];
        }

        $this->load->library('document_design_service');
        $id_card_design = $this->document_design_service->resolve_design($this->school_id, 'id_card');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'         => true,
                'count'          => count($formatted),
                'students'       => $formatted,
                'settings'       => $settings,
                'id_card_design' => [
                    'has_front'    => !empty($id_card_design->has_front),
                    'front_url'    => !empty($id_card_design->front_url) ? $id_card_design->front_url : '',
                    'has_back'     => !empty($id_card_design->has_back),
                    'back_url'     => !empty($id_card_design->back_url) ? $id_card_design->back_url : '',
                    'field_config' => !empty($id_card_design->field_config) ? $id_card_design->field_config : null,
                ],
                'logo_url'        => $logo_url,
                'signature_url'   => $signature_url,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint for ID Card Generation History DataTables.
     */
    public function id_card_history_ajax()
    {
        $this->require_permission('students.view');

        $draw        = (int)$this->input->post('draw');
        $start       = (int)$this->input->post('start');
        $length      = (int)$this->input->post('length') ?: 25;
        $order_arr   = $this->input->post('order');
        $order_col   = isset($order_arr[0]['column']) ? (int)$order_arr[0]['column'] : 0;
        $order_dir   = isset($order_arr[0]['dir']) ? $order_arr[0]['dir'] : 'DESC';
        $search_val  = $this->input->post('search')['value'] ?? '';

        $filters = [
            'academic_year_id' => $this->input->post('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $this->input->post('class_id'),
            'division_id'      => $this->input->post('division_id') ?: $this->input->post('section_id'),
            'status'           => $this->input->post('status'),
            'search'           => $search_val
        ];

        $total_records = $this->Id_card_model->get_history_count(['academic_year_id' => $filters['academic_year_id']]);
        $filtered_count = $this->Id_card_model->get_history_count($filters);
        $records = $this->Id_card_model->get_history($filters, $length, $start, $order_col, $order_dir);

        $data = [];
        foreach ($records as $r) {
            $fullName = trim($r->first_name . ' ' . $r->last_name);
            $classDisplay = trim(($r->class_name ?: '') . ' ' . ($r->section_name ?: ''));

            $statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-container text-on-secondary-container">' . html_escape($r->status) . '</span>';
            if ($r->status === 'Re-generated') {
                $statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-fixed text-on-primary-fixed">' . html_escape($r->status) . '</span>';
            }

            $actions = '
                <div class="flex items-center justify-end gap-1">
                    <button type="button" onclick="previewHistoryCard(' . $r->student_id . ')" title="Preview ID Card" class="p-1.5 rounded-lg text-primary hover:bg-primary-fixed/40 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </button>
                    <button type="button" onclick="printSingleCard(' . $r->student_id . ')" title="Print ID Card" class="p-1.5 rounded-lg text-secondary hover:bg-secondary-fixed/40 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                    </button>
                    <button type="button" onclick="downloadSinglePdf(' . $r->student_id . ')" title="Download PDF" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    </button>
                    <button type="button" onclick="downloadSingleImage(' . $r->student_id . ', \'png\')" title="Download PNG" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-[18px]">image</span>
                    </button>
                    <button type="button" onclick="regenerateCard(' . $r->student_id . ')" title="Regenerate with Latest Info" class="p-1.5 rounded-lg text-tertiary hover:bg-tertiary-fixed-dim/30 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">sync</span>
                    </button>
                </div>
            ';

            $data[] = [
                '<span class="font-mono font-medium text-primary text-xs">' . html_escape($r->card_number) . '</span>',
                '<div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-xs font-bold text-primary shrink-0 overflow-hidden">
                        ' . (!empty($r->photo) && file_exists(FCPATH . 'uploads/students/' . $r->photo) ? '<img src="' . base_url('uploads/students/' . $r->photo) . '" class="w-full h-full object-cover"/>' : strtoupper(substr($r->first_name, 0, 1) . substr($r->last_name, 0, 1))) . '
                    </div>
                    <div>
                        <div class="font-semibold text-on-surface text-sm">' . html_escape($fullName) . '</div>
                        <div class="text-xs text-on-surface-variant font-mono">Adm: ' . html_escape($r->admission_number) . ($r->roll_number ? ' · Roll: ' . html_escape($r->roll_number) : '') . '</div>
                    </div>
                </div>',
                '<span class="text-sm font-medium text-on-surface">' . html_escape($classDisplay ?: 'N/A') . '</span>',
                '<span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-mono bg-surface-container text-on-surface-variant font-medium">v' . (int)$r->card_version . '</span>',
                '<div class="text-xs text-on-surface-variant">
                    <div>' . date('d M Y, h:i A', strtotime($r->generated_at)) . '</div>
                    <div class="text-[11px] text-on-surface-variant/70">By ' . html_escape($r->generated_by_name ?: 'System') . '</div>
                </div>',
                $statusBadge,
                $actions
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw'            => $draw,
                'recordsTotal'    => $total_records,
                'recordsFiltered' => $filtered_count,
                'data'            => $data,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint to record generation or print event.
     */
    public function id_card_record_ajax()
    {
        $this->require_permission('students.view');
        $student_id = (int)$this->input->post('student_id');
        $action     = trim($this->input->post('action')) ?: 'Generated';
        $user_id    = (int)($this->current_user->user_id ?? 1);

        if (!$student_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Student ID is required.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        $record = $this->Id_card_model->record_generation($student_id, $this->academic_year_id, $user_id, $action);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'ID card activity recorded.',
                'record'  => $record,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint to regenerate an ID card with latest live student information.
     */
    public function id_card_regenerate_ajax()
    {
        $this->require_permission('students.view');
        $student_id = (int)$this->input->post('student_id');
        $user_id    = (int)($this->current_user->user_id ?? 1);

        if (!$student_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Student ID is required.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        // 1. Fetch fresh student data directly from DB
        $student = $this->Id_card_model->get_student_card_data($student_id);
        if (!$student) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Student record not found.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        // 2. Increment version and update history
        $record = $this->Id_card_model->record_generation($student_id, $this->academic_year_id, $user_id, 'Regenerate');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'ID card regenerated successfully with latest student information (v' . ($record->card_version ?? 1) . ').',
                'record'  => $record,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * Save ID Card configuration settings (School name, address, signature, logo, etc.).
     */
    public function id_card_settings_save()
    {
        $this->require_permission('students.view');

        if ($this->input->method() !== 'post') {
            redirect('students/id_cards#settings');
            return;
        }

        $card_title        = trim($this->input->post('card_title', TRUE)) ?: 'STUDENT IDENTITY CARD';
        $school_name       = trim($this->input->post('school_name', TRUE));
        $school_code       = trim($this->input->post('school_code', TRUE));
        $school_address    = trim($this->input->post('school_address', TRUE));
        $phone             = trim($this->input->post('phone', TRUE));
        $email             = trim($this->input->post('email', TRUE));
        $website           = trim($this->input->post('website', TRUE));
        $emergency_contact = trim($this->input->post('emergency_contact', TRUE));
        $principal_name    = trim($this->input->post('principal_name', TRUE));
        $return_text       = trim($this->input->post('return_text', TRUE));
        $validity_text     = trim($this->input->post('validity_text', TRUE));
        $accent_color      = trim($this->input->post('accent_color', TRUE)) ?: '#091426';
        $secondary_color   = trim($this->input->post('secondary_color', TRUE)) ?: '#006c4a';

        $data = [
            'card_title'        => $card_title,
            'school_name'       => $school_name,
            'school_code'       => $school_code,
            'school_address'    => $school_address,
            'phone'             => $phone,
            'email'             => $email,
            'website'           => $website,
            'emergency_contact' => $emergency_contact,
            'principal_name'    => $principal_name,
            'return_text'       => $return_text,
            'validity_text'     => $validity_text,
            'accent_color'      => $accent_color,
            'secondary_color'   => $secondary_color,
        ];

        // Handle file uploads (Logo and Principal Signature)
        $upload_dir = FCPATH . 'uploads/id_card/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, TRUE);
        }

        $config['upload_path']   = $upload_dir;
        $config['allowed_types'] = 'jpg|jpeg|png|gif|svg';
        $config['max_size']      = 5120; // 5MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        if (!empty($_FILES['school_logo']['name'])) {
            $this->upload->initialize($config);
            if ($this->upload->do_upload('school_logo')) {
                $upload_res = $this->upload->data();
                $data['school_logo'] = $upload_res['file_name'];
            }
        }

        if (!empty($_FILES['principal_signature']['name'])) {
            $this->upload->initialize($config);
            if ($this->upload->do_upload('principal_signature')) {
                $upload_res = $this->upload->data();
                $data['principal_signature'] = $upload_res['file_name'];
            }
        }

        $this->Id_card_model->save_settings($data);

        // Also update tbl_school_settings if relevant to keep core info in sync
        $this->Setting_model->update_settings([
            'school_name'    => $school_name,
            'school_code'    => $school_code,
            'principal_name' => $principal_name,
            'phone'          => $phone,
            'email'          => $email,
            'website'        => $website,
            'address'        => $school_address
        ]);

        if ($this->input->is_ajax_request()) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => true,
                    'message' => 'ID Card settings saved successfully.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ]));
        }

        $this->session->set_flashdata('success', 'ID Card settings saved successfully.');
        redirect('students/id_cards');
    }

    /**
     * Standalone clean print view for single or bulk ID cards.
     */
    public function id_card_print()
    {
        $this->require_permission('students.view');
        $student_id  = $this->input->get('student_id');
        $student_ids = $this->input->get('student_ids');
        $side        = $this->input->get('side') ?: 'both'; // 'both', 'front', 'back'

        $ids = [];
        if (!empty($student_id)) {
            $ids[] = (int)$student_id;
        } elseif (!empty($student_ids)) {
            $ids = array_map('intval', explode(',', $student_ids));
        } else {
            $class_id    = $this->input->get('class_id');
            $division_id = $this->input->get('division_id') ?: $this->input->get('section_id');
            $raw_students = $this->Student_model->get_all([
                'academic_year_id' => $this->academic_year_id,
                'class_id'         => $class_id,
                'division_id'      => $division_id,
                'status'           => 1
            ]);
            $ids = array_map(function($st) { return $st->student_id; }, $raw_students);
        }

        if (empty($ids)) {
            show_error('No students selected for printing.', 404);
            return;
        }

        $students = $this->Id_card_model->get_students_bulk($ids);
        $settings = $this->Id_card_model->get_settings();

        // Record print action for each student
        $uid = (int)($this->current_user->user_id ?? 1);
        foreach ($ids as $sid) {
            $this->Id_card_model->record_generation($sid, $this->academic_year_id, $uid, 'Printed');
        }

        $this->load->library('document_design_service');
        $document_design = $this->document_design_service->resolve_design($this->school_id, 'id_card');

        $this->load->view('pages/students/id_card_print', [
            'students'        => $students,
            'settings'        => $settings,
            'side'            => $side,
            'document_design' => $document_design,
        ]);
    }

    /* =========================================================================
       9. Student Promotion
       ========================================================================= */
    public function promotion()
    {
        $this->require_permission('students.promote');
        if ($this->input->method() === 'post') {
            $student_ids  = $this->input->post('student_ids');
            $from_year    = (int)$this->input->post('from_academic_year_id');
            $from_class   = (int)$this->input->post('from_class_id');
            $from_sec     = ($this->input->post('from_division_id') !== NULL && $this->input->post('from_division_id') !== '')
                ? (int)$this->input->post('from_division_id')
                : ($this->input->post('from_section_id') ? (int)$this->input->post('from_section_id') : NULL);
            $to_year      = (int)$this->input->post('to_academic_year_id');
            $to_class     = (int)$this->input->post('to_class_id');
            $to_sec_raw   = $this->input->post('to_division_id') ?: $this->input->post('to_section_id');

            // Default Division A fallback if empty
            if (empty($to_sec_raw)) {
                $to_sec = $this->Division_model->get_default_division_id($to_class);
            } else {
                $to_sec = (int)$to_sec_raw;
            }

            $promo_type   = $this->input->post('promotion_type') ?: 'Promoted';
            $remarks      = $this->input->post('remarks', TRUE);

            if (!empty($student_ids) && is_array($student_ids)) {
                if (empty($to_class)) {
                    $this->session->set_flashdata('error', 'Please select a valid Target Class.');
                    redirect('students/promotion?from_year=' . $from_year . '&from_class=' . $from_class . ($from_sec ? '&from_division=' . $from_sec : ''));
                    return;
                }

                $result = $this->Student_model->promote_students($student_ids, $from_year, $from_class, $from_sec, $to_year, $to_class, $to_sec, $promo_type, $remarks);
                if ($result) {
                    $this->session->set_flashdata('success', count($student_ids) . ' student(s) ' . strtolower($promo_type) . ' successfully.');
                    redirect('students/promotion?from_year=' . $to_year . '&from_class=' . $to_class . '&from_division=' . $to_sec);
                    return;
                }
            } else {
                $this->session->set_flashdata('error', 'Please select at least one student to promote.');
            }
        }

        $years = $this->Academic_year_model->get_all();
        $from_year = (int)($this->input->get('from_year') ?: $this->academic_year_id);
        if (!$from_year) {
            $from_year = (int)(get_active_academic_year_id(TRUE) ?: 1);
        }

        $classes = $this->Class_model->get_all($from_year);
        $from_class_raw = $this->input->get('from_class');
        if ($from_class_raw !== NULL && $from_class_raw !== '' && is_numeric($from_class_raw)) {
            $from_class = (int)$from_class_raw;
        } else {
            $from_class = !empty($classes) ? (int)$classes[0]->class_id : NULL;
        }

        $from_sec = ($this->input->get('from_division') !== NULL && $this->input->get('from_division') !== '' && is_numeric($this->input->get('from_division')))
            ? (int)$this->input->get('from_division')
            : (($this->input->get('from_section') !== NULL && $this->input->get('from_section') !== '' && is_numeric($this->input->get('from_section'))) ? (int)$this->input->get('from_section') : NULL);

        // Fetch students strictly for selected academic year + class (+ division if specified)
        $students = [];
        if ($from_class) {
            $filters = array(
                'academic_year_id' => $from_year,
                'class_id'         => $from_class,
                'status'           => 1
            );
            if ($from_sec !== NULL) {
                $filters['division_id'] = $from_sec;
                $filters['section_id']  = $from_sec;
            }
            $students = $this->Student_model->get_all($filters);
        }

        $source_sections = $from_class ? $this->Division_model->get_divisions_for_class($from_class) : [];
        $groups          = $this->Academic_group_model->get_all();
        $promotions_history = $this->Student_model->get_promotions();

        $this->render('pages/students/promotion', array(
            'title'              => 'Student Promotion',
            'page_key'           => 'student-promotion',
            'students'           => $students,
            'promotions_history' => $promotions_history,
            'classes'            => $classes,
            'source_sections'    => $source_sections,
            'source_divisions'   => $source_sections,
            'years'              => $years,
            'groups'             => $groups,
            'from_year'          => $from_year,
            'from_class'         => $from_class,
            'from_sec'           => $from_sec,
        ));
    }

    /**
     * AJAX endpoint: fetch sections by class ID with default Section A fallback
     */
    public function get_sections_ajax()
    {
        $this->require_permission('students.view');
        $class_id = (int)$this->input->get_post('class_id');
        
        $divisions = [];
        if ($class_id > 0) {
            $divisions = $this->Division_model->get_divisions_for_class($class_id);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'class_id'        => $class_id,
                'divisions'       => $divisions,
                'sections'        => $divisions,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    public function get_divisions_ajax()
    {
        return $this->get_sections_ajax();
    }

    /**
     * AJAX endpoint: fetch classes by academic group ID and year ID
     */
    public function get_classes_by_group_ajax()
    {
        $this->require_permission('students.view');
        $academic_group_id = (int)$this->input->get_post('academic_group_id');
        $academic_year_id  = (int)$this->input->get_post('academic_year_id') ?: (int)$this->academic_year_id;

        $classes = $academic_group_id ? $this->Class_model->get_by_group($academic_group_id, $academic_year_id) : $this->Class_model->get_all($academic_year_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'            => true,
                'academic_group_id' => $academic_group_id,
                'classes'           => $classes,
                'csrf_token_name'   => $this->security->get_csrf_token_name(),
                'csrf_hash'         => $this->security->get_csrf_hash()
            ]));
    }

    /**
     * AJAX endpoint: fetch classes by academic year ID
     */
    public function get_classes_ajax()
    {
        $this->require_permission('students.view');
        $academic_year_id = (int)$this->input->get_post('academic_year_id');
        if (!$academic_year_id) {
            $academic_year_id = (int)$this->academic_year_id;
        }

        $classes = $this->Class_model->get_all($academic_year_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'academic_year_id'=> $academic_year_id,
                'classes'         => $classes,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]));
    }

    /* =========================================================================
       10. Transfer / TC Management
       ========================================================================= */
    public function transfers()
    {
        $this->require_permission('students.edit');
        if ($this->input->method() === 'post') {
            $student_id = $this->input->post('student_id');
            $student    = $this->Student_model->get_by_id($student_id);

            if ($student) {
                $tcNumber = 'TC/' . date('Y') . '/' . sprintf('%03d', rand(10, 999));
                $tcData = array(
                    'student_id'        => $student_id,
                    'tc_number'         => $tcNumber,
                    'transfer_date'     => $this->input->post('transfer_date') ?: date('Y-m-d'),
                    'reason'            => $this->input->post('reason', TRUE) ?: 'Parent Relocation',
                    'previous_class_id' => $student->class_id,
                    'academic_year_id'  => $student->academic_year_id,
                    'conduct'           => $this->input->post('conduct', TRUE) ?: 'Good',
                    'dues_cleared'      => 1,
                    'status'            => 'Issued',
                    'remarks'           => $this->input->post('remarks', TRUE),
                    'created_at'        => date('Y-m-d H:i:s'),
                );

                $transfer_id = $this->Student_model->issue_transfer($tcData);
                $this->session->set_flashdata('success', 'Transfer Certificate ' . $tcNumber . ' issued successfully.');
                redirect('students/tc/' . $transfer_id);
                return;
            }
        }

        $transfers = $this->Student_model->get_transfers();
        $students  = $this->Student_model->get_all(array('status' => 1, 'academic_year_id' => $this->academic_year_id));
        $classes   = $this->Class_model->get_all($this->academic_year_id);

        $this->render('pages/students/transfers', array(
            'title'     => 'Transfer / TC Management',
            'page_key'  => 'student-transfers',
            'transfers' => $transfers,
            'students'  => $students,
            'classes'   => $classes,
        ));
    }

    public function tc($transfer_id = NULL)
    {
        $this->require_permission('students.view');
        if (!$transfer_id) {
            redirect('students/transfers');
            return;
        }

        $transfer = $this->Student_model->get_transfer_by_id($transfer_id);
        if (!$transfer) {
            $this->session->set_flashdata('error', 'Transfer certificate record not found.');
            redirect('students/transfers');
            return;
        }

        $this->load->library('document_design_service');
        $target_school_id = !empty($transfer->school_id) ? (int)$transfer->school_id : $this->school_id;
        $document_design = $this->document_design_service->resolve_design($target_school_id, 'transfer_certificate');

        $this->render_document('pages/students/tc_print', array(
            'title'           => 'Transfer Certificate - ' . $transfer->tc_number,
            'page_key'        => 'student-transfers',
            'transfer'        => $transfer,
            'document_design' => $document_design,
        ));
    }

    /* =========================================================================
       11. Student Search & Filtering
       ========================================================================= */
    public function search()
    {
        $this->require_permission('students.view');
        $filters = array(
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $this->input->get('class_id'),
            'section_id'       => $this->input->get('section_id'),
            'gender'           => $this->input->get('gender'),
            'status'           => $this->input->get('status'),
            'search'           => $this->input->get('search'),
        );

        $hasSearch = !empty($filters['search']) || !empty($filters['class_id']) || !empty($filters['section_id']) || !empty($filters['gender']) || !empty($filters['academic_year_id']) || ($filters['status'] !== '' && $filters['status'] !== NULL);

        $students = $hasSearch ? $this->Student_model->get_all($filters) : $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id), 20);
        $totalCount = $this->Student_model->count_filtered($filters);

        $classes  = $this->Class_model->get_all($filters['academic_year_id']);
        $sections = $this->Section_model->get_all();
        $years    = $this->Academic_year_model->get_all();

        $this->render('pages/students/search', array(
            'title'      => 'Student Search & Filtering',
            'page_key'   => 'student-search',
            'students'   => $students,
            'total_count'=> $totalCount,
            'classes'    => $classes,
            'sections'   => $sections,
            'years'      => $years,
            'filters'    => $filters,
        ));
    }
}

