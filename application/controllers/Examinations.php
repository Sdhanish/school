<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Examinations extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Exam_model');
        $this->load->model('Exam_type_model');
        $this->load->model('Exam_schedule_model');
        $this->load->model('Exam_mark_model');
        $this->load->model('Grade_model');
        $this->load->model('Result_model');
        $this->load->model('Exam_setting_model');
        $this->load->model('Exam_audit_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Subject_model');
        $this->load->model('Staff_model');
        $this->load->model('Student_model');
    }

    // Permission keys used in this controller (from tbl_permissions):
    //   exams.view         — view dashboard, results, report cards
    //   exams.create       — create/edit/delete exams, schedules, grades
    //   exams.marks_entry  — enter / verify student marks
    //   exams.publish      — publish results

    /* =========================================================================
       1. Examination Dashboard
       ========================================================================= */
    public function index()
    {
        $this->require_permission('exams.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;

        $data = [
            'title'             => 'Examination Dashboard',
            'page_key'          => 'exam-dashboard',
            'stats'             => $this->Exam_model->get_dashboard_stats($year_id),
            'upcoming_exams'    => $this->Exam_model->get_upcoming_exam_schedules($year_id, 6),
            'recent_results'    => $this->Exam_model->get_recent_published_results($year_id, 5),
            'progress_summary'  => $this->Exam_model->get_marks_entry_progress_summary(NULL, $year_id),
            'academic_years'    => $this->Academic_year_model->get_all(),
            'selected_year'     => $year_id
        ];

        $this->render('pages/examinations/dashboard', $data);
    }

    /* =========================================================================
       2. Exam Management (Creation & Lifecycle)
       ========================================================================= */
    public function exams()
    {
        $this->require_permission('exams.view');

        // Handle POST Create / Edit / Delete
        if ($this->input->method() === 'post') {
            $this->require_permission('exams.create');
            $action = $this->input->post('action');
            $exam_id = (int)$this->input->post('exam_id');

            if ($action === 'delete') {
                $this->Exam_model->delete($exam_id);
                $this->Exam_audit_model->log($this->current_user->user_id, 'EXAM_DELETED', 'tbl_exams', $exam_id, 'Deleted exam record.');
                $this->session->set_flashdata('success', 'Exam record deleted successfully.');
                redirect('examinations/exams');
            }

            $exam_name        = trim($this->input->post('exam_name'));
            $exam_type_id     = (int)$this->input->post('exam_type_id');
            $academic_year_id = (int)($this->input->post('academic_year_id') ?: $this->academic_year_id);
            $description      = trim($this->input->post('description'));
            $start_date       = $this->input->post('start_date');
            $end_date         = $this->input->post('end_date');
            $status           = $this->input->post('status') ?: 'Draft';
            $classes          = $this->input->post('classes') ?: [];

            if (empty($exam_name) || empty($exam_type_id) || empty($start_date) || empty($end_date)) {
                $this->session->set_flashdata('error', 'Please fill all required exam fields.');
                redirect('examinations/exams');
            }

            if (strtotime($end_date) < strtotime($start_date)) {
                $this->session->set_flashdata('error', 'End date cannot be earlier than start date.');
                redirect('examinations/exams');
            }

            $save_data = [
                'exam_name'          => $exam_name,
                'exam_type_id'       => $exam_type_id,
                'academic_year_id'   => $academic_year_id,
                'description'        => $description,
                'start_date'         => $start_date,
                'end_date'           => $end_date,
                'applicable_classes' => $classes,
                'status'             => $status
            ];

            if ($exam_id > 0) {
                $this->Exam_model->update($exam_id, $save_data);
                $this->Exam_audit_model->log($this->current_user->user_id, 'EXAM_UPDATED', 'tbl_exams', $exam_id, "Updated exam '{$exam_name}'");
                $this->session->set_flashdata('success', 'Exam updated successfully.');
            } else {
                $new_id = $this->Exam_model->insert($save_data);
                $this->Exam_audit_model->log($this->current_user->user_id, 'EXAM_CREATED', 'tbl_exams', $new_id, "Created exam '{$exam_name}'");
                $this->session->set_flashdata('success', 'Exam created successfully.');
            }

            redirect('examinations/exams');
        }

        $raw_year = $this->input->get('academic_year_id');
        $selected_year_id = ($raw_year === NULL) ? $this->academic_year_id : $raw_year;

        $filters = [
            'academic_year_id' => $selected_year_id,
            'exam_type_id'     => $this->input->get('exam_type_id'),
            'status'           => $this->input->get('status'),
            'search'           => $this->input->get('search'),
        ];

        $data = [
            'title'          => 'Exam Management',
            'page_key'       => 'exams',
            'exams'          => $this->Exam_model->get_all($filters),
            'exam_types'     => $this->Exam_type_model->get_all(TRUE),
            'academic_years' => $this->Academic_year_model->get_all(),
            'classes'        => $this->Class_model->get_all($filters['academic_year_id'] ?: $this->academic_year_id),
            'filters'        => $filters
        ];

        $this->render('pages/examinations/exams', $data);
    }

    /* =========================================================================
       3. Exam Types
       ========================================================================= */
    public function types()
    {
        $this->require_permission('exams.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('exams.create');
            $action = $this->input->post('action');
            $type_id = (int)$this->input->post('exam_type_id');

            if ($action === 'delete') {
                $this->Exam_type_model->delete($type_id);
                $this->session->set_flashdata('success', 'Exam type deleted / deactivated.');
                redirect('examinations/types');
            }

            if ($action === 'toggle') {
                $this->Exam_type_model->toggle_status($type_id);
                $this->session->set_flashdata('success', 'Exam type status updated.');
                redirect('examinations/types');
            }

            $type_name   = trim($this->input->post('type_name'));
            $description = trim($this->input->post('description'));
            $status      = $this->input->post('status') ? 1 : 0;

            if (empty($type_name)) {
                $this->session->set_flashdata('error', 'Exam type name is required.');
                redirect('examinations/types');
            }

            if ($this->Exam_type_model->is_name_exists($type_name, $type_id)) {
                $this->session->set_flashdata('error', "Exam type '{$type_name}' already exists.");
                redirect('examinations/types');
            }

            $data = [
                'type_name'   => $type_name,
                'description' => $description,
                'status'      => $status
            ];

            if ($type_id > 0) {
                $this->Exam_type_model->update($type_id, $data);
                $this->session->set_flashdata('success', 'Exam type updated successfully.');
            } else {
                $this->Exam_type_model->insert($data);
                $this->session->set_flashdata('success', 'Exam type added successfully.');
            }

            redirect('examinations/types');
        }

        $data = [
            'title'      => 'Exam Types',
            'page_key'   => 'exam-types',
            'exam_types' => $this->Exam_type_model->get_all()
        ];

        $this->render('pages/examinations/types', $data);
    }

    /* =========================================================================
       4. Exam Schedules
       ========================================================================= */
    public function schedules()
    {
        $this->require_permission('exams.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('exams.create');
            $action      = $this->input->post('action');
            $schedule_id = (int)$this->input->post('schedule_id');

            if ($action === 'delete') {
                $this->Exam_schedule_model->delete($schedule_id);
                $this->session->set_flashdata('success', 'Schedule deleted successfully.');
                redirect('examinations/schedules');
            }

            $exam_id          = (int)$this->input->post('exam_id');
            $academic_year_id = (int)($this->input->post('academic_year_id') ?: $this->academic_year_id);
            $class_id         = (int)$this->input->post('class_id');
            $raw_division_id  = $this->input->post('division_id') !== NULL ? $this->input->post('division_id') : $this->input->post('section_id');
            $division_id      = (!empty($raw_division_id) && $raw_division_id !== 'all') ? (int)$raw_division_id : NULL;
            $subject_id       = (int)$this->input->post('subject_id');
            $teacher_id       = $this->input->post('teacher_id') ? (int)$this->input->post('teacher_id') : NULL;
            $exam_date        = $this->input->post('exam_date');
            $start_time       = $this->input->post('start_time');
            $end_time         = $this->input->post('end_time');
            $max_marks        = (float)$this->input->post('max_marks') ?: 100.00;
            $passing_marks    = (float)$this->input->post('passing_marks') ?: 35.00;
            $room_no          = trim($this->input->post('room_no'));
            $instructions     = trim($this->input->post('instructions'));

            if (empty($exam_id) || empty($class_id) || empty($subject_id) || empty($exam_date)) {
                $this->session->set_flashdata('error', 'Please fill all required schedule fields.');
                redirect('examinations/schedules');
            }

            // Backend validation: if division_id is specified, ensure it belongs to class_id
            if (!empty($division_id) && !$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $this->session->set_flashdata('error', 'The selected division does not belong to the selected class.');
                redirect('examinations/schedules');
            }

            // Backend validation: verify that the subject belongs to the selected class according to Subject Allocation
            $allocated_subjects = $this->Subject_model->get_for_class($academic_year_id, $class_id);
            $valid_subject_ids  = !empty($allocated_subjects) ? array_map('intval', array_column($allocated_subjects, 'subject_id')) : [];

            // If editing an existing schedule, allow retaining the subject only if it was already saved on this schedule for this class
            $is_existing_unmodified_subject = false;
            if ($schedule_id > 0) {
                $existing_sched = $this->Exam_schedule_model->get_by_id($schedule_id);
                if ($existing_sched && (int)$existing_sched->class_id === $class_id && (int)$existing_sched->subject_id === $subject_id) {
                    $is_existing_unmodified_subject = true;
                }
            }

            if (!in_array($subject_id, $valid_subject_ids, true) && !$is_existing_unmodified_subject) {
                $this->session->set_flashdata('error', 'The selected subject is not allocated to the selected class for this academic year.');
                redirect('examinations/schedules');
            }

            if (strtotime($end_time) <= strtotime($start_time)) {
                $this->session->set_flashdata('error', 'End time must be later than start time.');
                redirect('examinations/schedules');
            }

            if ($passing_marks > $max_marks) {
                $this->session->set_flashdata('error', 'Passing marks cannot exceed maximum marks.');
                redirect('examinations/schedules');
            }

            if ($this->Exam_schedule_model->check_duplicate_schedule($exam_id, $class_id, $division_id, $subject_id, $schedule_id)) {
                $this->session->set_flashdata('error', 'A schedule for this exam, class, division, and subject already exists.');
                redirect('examinations/schedules');
            }

            // Room conflict check
            $room_conflict = $this->Exam_schedule_model->check_room_conflict($exam_date, $start_time, $end_time, $room_no, $schedule_id);
            if ($room_conflict) {
                $this->session->set_flashdata('error', "Room '{$room_no}' is already booked on {$exam_date} for {$room_conflict->subject_name} ({$room_conflict->start_time} - {$room_conflict->end_time}).");
                redirect('examinations/schedules');
            }

            $save_data = [
                'exam_id'          => $exam_id,
                'academic_year_id' => $academic_year_id,
                'class_id'         => $class_id,
                'division_id'      => $division_id,
                'subject_id'       => $subject_id,
                'teacher_id'       => $teacher_id,
                'exam_date'        => $exam_date,
                'start_time'       => $start_time,
                'end_time'         => $end_time,
                'max_marks'        => $max_marks,
                'passing_marks'    => $passing_marks,
                'room_no'          => $room_no,
                'instructions'     => $instructions
            ];

            if ($schedule_id > 0) {
                $this->Exam_schedule_model->update($schedule_id, $save_data);
                $this->session->set_flashdata('success', 'Exam schedule updated successfully.');
            } else {
                $this->Exam_schedule_model->insert($save_data);
                $this->session->set_flashdata('success', 'Exam schedule created successfully.');
            }

            redirect('examinations/schedules');
        }

        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'exam_id'          => $this->input->get('exam_id'),
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $class_id,
            'division_id'      => $division_id,
            'subject_id'       => $this->input->get('subject_id'),
            'from_date'        => $this->input->get('from_date'),
            'to_date'          => $this->input->get('to_date')
        ];

        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'          => 'Exam Schedules',
            'page_key'       => 'exam-schedules',
            'schedules'      => $this->Exam_schedule_model->get_all($filters),
            'exams'          => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'academic_years' => $this->Academic_year_model->get_all(),
            'classes'        => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'      => $divisions,
            'sections'       => $divisions,
            'subjects'       => !empty($class_id) ? $this->Subject_model->get_for_class($filters['academic_year_id'], (int)$class_id) : $this->Subject_model->get_all(),
            'teachers'       => $this->Staff_model->get_teachers(),
            'filters'        => $filters
        ];

        $this->render('pages/examinations/schedules', $data);
    }

    /* =========================================================================
       5. Add Schedule (Subject Allocation Workflow)
       ========================================================================= */
    public function add_schedule()
    {
        $this->allocations();
    }

    public function allocations()
    {
        $this->require_permission('exams.create');

        if ($this->input->method() === 'post') {
            $exam_id          = (int)$this->input->post('exam_id');
            $academic_year_id = (int)($this->input->post('academic_year_id') ?: $this->academic_year_id);
            $class_id         = (int)$this->input->post('class_id');
            $raw_div          = $this->input->post('division_id') !== NULL ? $this->input->post('division_id') : $this->input->post('section_id');
            $subjects         = $this->input->post('subjects') ?: [];

            if (empty($exam_id) || empty($class_id) || empty($raw_div)) {
                $this->session->set_flashdata('error', 'Please select Exam, Class, and Division.');
                redirect('examinations/allocations');
            }

            // Check that at least one subject is selected
            $has_selected_subject = false;
            foreach ($subjects as $row) {
                if (!empty($row['selected'])) {
                    $has_selected_subject = true;
                    break;
                }
            }

            if (!$has_selected_subject) {
                $this->session->set_flashdata('error', 'Please select at least one subject to schedule.');
                redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}&division_id={$raw_div}");
            }

            // Determine target divisions based on division selection
            if ($raw_div === 'all') {
                $target_divisions = $this->Division_model->get_all($class_id);
                if (empty($target_divisions)) {
                    $this->session->set_flashdata('error', 'No divisions found for the selected class.');
                    redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}");
                }
            } else {
                $div_id = (int)$raw_div;
                if (!$this->Division_model->is_valid_division_for_class($div_id, $class_id)) {
                    $this->session->set_flashdata('error', 'The selected division does not belong to the selected class.');
                    redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}");
                }
                $target_divisions = [(object)['division_id' => $div_id]];
            }

            // Begin database transaction for atomicity
            $this->db->trans_start();

            // Validate that submitted subjects belong to the selected class
            $valid_class_subjects = $this->Subject_model->get_for_class($academic_year_id, (int)$class_id);
            $valid_subject_ids = array_map(function($s) { return (int)$s->subject_id; }, $valid_class_subjects);

            $saved_count    = 0;
            $division_count = count($target_divisions);
            $subject_count  = 0;

            foreach ($subjects as $sub_id => $row) {
                if (empty($row['selected'])) continue;
                if (!in_array((int)$sub_id, $valid_subject_ids, true)) {
                    continue; // Skip any subject not belonging to this class
                }
                $subject_count++;

                $exam_date     = $row['exam_date'] ?: date('Y-m-d');
                $start_time    = $row['start_time'] ?: '09:00:00';
                $end_time      = $row['end_time'] ?: '12:00:00';
                $max_marks     = (float)($row['max_marks'] ?: 100.00);
                $passing_marks = (float)($row['passing_marks'] ?: 35.00);
                $room_no       = trim($row['room_no'] ?? 'Hall 1');
                $teacher_id    = !empty($row['teacher_id']) ? (int)$row['teacher_id'] : NULL;

                if (!empty($teacher_id)) {
                    $group_check = $this->Staff_model->validate_teacher_class_group($teacher_id, $class_id, $this->school_id);
                    if (!$group_check['valid']) {
                        $this->session->set_flashdata('error', $group_check['error']);
                        redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}&division_id={$raw_div}");
                        return;
                    }
                }

                foreach ($target_divisions as $div) {
                    $cur_div_id = (int)$div->division_id;

                    $save_data = [
                        'exam_id'          => $exam_id,
                        'academic_year_id' => $academic_year_id,
                        'class_id'         => $class_id,
                        'division_id'      => $cur_div_id,
                        'subject_id'       => (int)$sub_id,
                        'teacher_id'       => $teacher_id,
                        'exam_date'        => $exam_date,
                        'start_time'       => $start_time,
                        'end_time'         => $end_time,
                        'max_marks'        => $max_marks,
                        'passing_marks'    => $passing_marks,
                        'room_no'          => $room_no
                    ];

                    // Upsert per division and subject
                    $existing = $this->db
                        ->where('exam_id', $exam_id)
                        ->where('class_id', $class_id)
                        ->where('division_id', $cur_div_id)
                        ->where('subject_id', (int)$sub_id)
                        ->get('tbl_exam_schedules')
                        ->row();

                    if ($existing) {
                        $this->Exam_schedule_model->update($existing->schedule_id, $save_data);
                    } else {
                        $this->Exam_schedule_model->insert($save_data);
                    }
                    $saved_count++;
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Database error occurred while saving schedules. All changes rolled back.');
                redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}&division_id={$raw_div}");
            }

            // Accurate success message based on scope
            if ($raw_div === 'all') {
                if ($subject_count > 1) {
                    $success_msg = "{$saved_count} exam schedules created successfully for {$division_count} divisions and {$subject_count} subjects.";
                } else {
                    $success_msg = "{$saved_count} exam schedules created successfully for {$division_count} divisions.";
                }
            } else {
                $success_msg = "Saved schedule for {$saved_count} subjects for this exam.";
            }

            $this->session->set_flashdata('success', $success_msg);
            redirect("examinations/allocations?exam_id={$exam_id}&class_id={$class_id}&division_id={$raw_div}");
        }

        $exam_id     = $this->input->get('exam_id');
        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id') !== NULL ? $this->input->get('division_id') : $this->input->get('section_id');

        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $division_id = NULL;
        if (!empty($raw_div)) {
            if ($raw_div === 'all') {
                $division_id = !empty($divisions) ? 'all' : NULL;
            } else {
                $division_id = (!empty($class_id) && $this->Division_model->is_valid_division_for_class((int)$raw_div, $class_id)) ? (int)$raw_div : NULL;
            }
        }

        $allocated_map = [];
        if ($exam_id && $class_id && $division_id) {
            if ($division_id === 'all') {
                $schedules = $this->Exam_schedule_model->get_all([
                    'exam_id'  => $exam_id,
                    'class_id' => $class_id
                ]);
                foreach ($schedules as $s) {
                    if (!isset($allocated_map[$s->subject_id])) {
                        $allocated_map[$s->subject_id] = $s;
                    }
                }
            } else {
                $schedules = $this->Exam_schedule_model->get_all([
                    'exam_id'     => $exam_id,
                    'class_id'    => $class_id,
                    'division_id' => $division_id
                ]);
                foreach ($schedules as $s) {
                    $allocated_map[$s->subject_id] = $s;
                }
            }
        }

        $classes = $this->Class_model->get_all($this->academic_year_id);
        $selected_class_name = '';
        foreach ($classes as $c) {
            if ($c->class_id == $class_id) {
                $selected_class_name = $c->class_name;
                break;
            }
        }

        // Fetch subjects assigned to this class only
        $subjects = [];
        if (!empty($class_id)) {
            $subjects = $this->Subject_model->get_for_class($this->academic_year_id, (int)$class_id);
        }

        $cls_obj = !empty($class_id) ? $this->Class_model->get_by_id((int)$class_id, $this->school_id) : null;
        $teachers = ($cls_obj && !empty($cls_obj->academic_group_id))
            ? $this->Staff_model->get_teachers_by_group((int)$cls_obj->academic_group_id, $this->school_id)
            : $this->Staff_model->get_teachers();

        $data = [
            'title'               => 'Add Schedule',
            'page_key'            => 'exam-allocations',
            'exams'               => $this->Exam_model->get_all(['academic_year_id' => $this->academic_year_id]),
            'classes'             => $classes,
            'divisions'           => $divisions,
            'sections'            => $divisions,
            'subjects'            => $subjects,
            'teachers'            => $teachers,
            'allocated_map'       => $allocated_map,
            'selected_exam'       => $exam_id,
            'selected_class'      => $class_id,
            'selected_class_name' => $selected_class_name,
            'selected_division'   => $division_id,
            'selected_section'    => $division_id,
            'academic_year_id'    => $this->academic_year_id
        ];

        $this->render('pages/examinations/allocations', $data);
    }

    /* =========================================================================
       6. Marks Entry (Teacher interface)
       ========================================================================= */
    public function marks_entry()
    {
        $this->require_permission('exams.marks_entry');

        $schedule_id = (int)$this->input->get('schedule_id');

        if ($this->input->method() === 'post') {
            $schedule_id   = (int)$this->input->post('schedule_id');
            $marks_data    = $this->input->post('marks') ?: [];
            $target_status = ($this->input->post('action') === 'submit') ? 'Submitted' : 'Draft';

            $sched_check = $this->Exam_schedule_model->get_by_id($schedule_id);
            if (!$sched_check) {
                $this->session->set_flashdata('error', 'Invalid exam schedule record.');
                redirect('examinations/marks_entry');
            }

            // Backend validation: verify the subject belongs to the class
            $valid_class_subjects = $this->Subject_model->get_for_class($sched_check->academic_year_id, (int)$sched_check->class_id);
            $valid_subject_ids = array_map(function($s) { return (int)$s->subject_id; }, $valid_class_subjects);
            if (!in_array((int)$sched_check->subject_id, $valid_subject_ids, true)) {
                $this->session->set_flashdata('error', 'Selected subject is not allocated to this class.');
                redirect('examinations/marks_entry');
            }

            $saved = $this->Exam_mark_model->save_marks_batch($schedule_id, $marks_data, $this->current_user->user_id, $target_status);

            $this->Exam_audit_model->log($this->current_user->user_id, 'MARKS_' . strtoupper($target_status), 'tbl_exam_schedules', $schedule_id, "Saved {$saved} student marks as {$target_status}");

            $msg = ($target_status === 'Submitted') ? 'Marks submitted successfully for verification.' : 'Marks saved as Draft successfully.';
            $this->session->set_flashdata('success', $msg);
            redirect('examinations/marks_entry?schedule_id=' . $schedule_id);
        }

        // Context parameters from GET request
        $exam_id     = $this->input->get('exam_id');
        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id') ?: $this->input->get('section_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;
        $subject_id  = $this->input->get('subject_id');

        // If schedule_id is provided directly, resolve contextual IDs
        if ($schedule_id > 0) {
            $sched_record = $this->Exam_schedule_model->get_by_id($schedule_id);
            if ($sched_record) {
                if (empty($exam_id))     $exam_id     = $sched_record->exam_id;
                if (empty($class_id))    $class_id    = $sched_record->class_id;
                if (empty($division_id)) $division_id = $sched_record->division_id;
                if (empty($subject_id))  $subject_id  = $sched_record->subject_id;
            }
        }

        // Validate division belongs to class
        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        // Fetch subjects allocated ONLY to the selected class for active academic year
        $subjects = !empty($class_id) 
            ? $this->Subject_model->get_for_class($this->academic_year_id, (int)$class_id) 
            : [];
        $valid_subject_ids = array_map(function($s) { return (int)$s->subject_id; }, $subjects);

        // Backend validation: ensure selected subject belongs to selected class
        if (!empty($subject_id)) {
            if (!in_array((int)$subject_id, $valid_subject_ids, true)) {
                $subject_id = NULL;
                $schedule_id = 0;
                $this->session->set_flashdata('error', 'The selected subject is not allocated to this class.');
            }
        }

        // Resolve schedule_id from exam + class + division + subject if not provided
        $schedule_not_found = false;
        if (!$schedule_id && $exam_id && $class_id && $division_id && $subject_id) {
            // First attempt: exact match with division
            $sched = $this->db
                ->where('exam_id', (int)$exam_id)
                ->where('class_id', (int)$class_id)
                ->where('division_id', (int)$division_id)
                ->where('subject_id', (int)$subject_id)
                ->where('is_deleted', 'n')
                ->get('tbl_exam_schedules')
                ->row();

            // Fallback attempt: schedule applies to all divisions of this class (division_id IS NULL)
            if (!$sched) {
                $sched = $this->db
                    ->where('exam_id', (int)$exam_id)
                    ->where('class_id', (int)$class_id)
                    ->where('division_id IS NULL', NULL, FALSE)
                    ->where('subject_id', (int)$subject_id)
                    ->where('is_deleted', 'n')
                    ->get('tbl_exam_schedules')
                    ->row();
            }

            if ($sched) {
                $schedule_id = (int)$sched->schedule_id;
            } else {
                $schedule_not_found = true;
            }
        }

        $marksheet = ($schedule_id > 0) ? $this->Exam_mark_model->get_marks_sheet($schedule_id) : NULL;

        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'               => 'Marks Entry',
            'page_key'            => 'marks-entry',
            'marksheet'           => $marksheet,
            'schedule_id'         => $schedule_id,
            'schedule_not_found'  => $schedule_not_found,
            'exams'               => $this->Exam_model->get_all(['academic_year_id' => $this->academic_year_id]),
            'classes'             => $this->Class_model->get_all($this->academic_year_id),
            'divisions'           => $divisions,
            'sections'            => $divisions,
            'subjects'            => $subjects,
            'selected_exam'       => $exam_id,
            'selected_class'      => $class_id,
            'selected_division'   => $division_id,
            'selected_section'    => $division_id,
            'selected_subject'    => $subject_id
        ];

        $this->render('pages/examinations/marks_entry', $data);
    }

    /* =========================================================================
       7. Marks Verification
       ========================================================================= */
    public function verification()
    {
        $this->require_permission('exams.marks_entry');

        if ($this->input->method() === 'post') {
            $schedule_id = (int)$this->input->post('schedule_id');
            $action      = $this->input->post('action'); // 'approve' or 'reject'
            $reason      = trim($this->input->post('rejection_reason'));

            $this->Exam_mark_model->verify_marksheet($schedule_id, $action, $this->current_user->user_id, $reason);

            $this->Exam_audit_model->log($this->current_user->user_id, 'MARKS_' . strtoupper($action), 'tbl_exam_schedules', $schedule_id, "Marksheet {$action}ed. Reason: {$reason}");

            $msg = ($action === 'approve') ? 'Marks approved successfully.' : 'Marksheet rejected and returned for correction.';
            $this->session->set_flashdata('success', $msg);
            redirect('examinations/verification');
        }

        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'exam_id'          => $this->input->get('exam_id'),
            'class_id'         => $class_id,
            'division_id'      => $division_id,
            'status_filter'    => $this->input->get('status_filter')
        ];

        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'          => 'Marks Verification',
            'page_key'       => 'marks-verification',
            'marksheets'     => $this->Exam_mark_model->get_marksheets_for_verification($filters),
            'exams'          => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'classes'        => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'      => $divisions,
            'sections'       => $divisions,
            'filters'        => $filters
        ];

        $this->render('pages/examinations/verification', $data);
    }

    /* =========================================================================
       8. Grade Management
       ========================================================================= */
    public function grades()
    {
        $this->require_permission('exams.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('exams.create');
            $action   = $this->input->post('action');
            $grade_id = (int)$this->input->post('grade_id');

            if ($action === 'delete') {
                $this->Grade_model->delete($grade_id);
                $this->session->set_flashdata('success', 'Grade scale deleted.');
                redirect('examinations/grades');
            }

            $grade_name     = trim($this->input->post('grade_name'));
            $min_percentage = (float)$this->input->post('min_percentage');
            $max_percentage = (float)$this->input->post('max_percentage');
            $grade_point    = (float)$this->input->post('grade_point');
            $description    = trim($this->input->post('description'));
            $status         = $this->input->post('status') ? 1 : 0;

            if (empty($grade_name)) {
                $this->session->set_flashdata('error', 'Grade name is required.');
                redirect('examinations/grades');
            }

            if ($max_percentage <= $min_percentage) {
                $this->session->set_flashdata('error', 'Maximum percentage must be strictly greater than minimum percentage.');
                redirect('examinations/grades');
            }

            // Check overlap
            $overlap = $this->Grade_model->check_overlap($min_percentage, $max_percentage, $grade_id);
            if ($overlap) {
                $this->session->set_flashdata('error', "Percentage range {$min_percentage}% - {$max_percentage}% overlaps with existing grade {$overlap->grade_name} ({$overlap->min_percentage}% - {$overlap->max_percentage}%).");
                redirect('examinations/grades');
            }

            $data = [
                'grade_name'     => $grade_name,
                'min_percentage' => $min_percentage,
                'max_percentage' => $max_percentage,
                'grade_point'    => $grade_point,
                'description'    => $description,
                'status'         => $status
            ];

            if ($grade_id > 0) {
                $this->Grade_model->update($grade_id, $data);
                $this->session->set_flashdata('success', 'Grade scale updated successfully.');
            } else {
                $this->Grade_model->insert($data);
                $this->session->set_flashdata('success', 'Grade scale added successfully.');
            }

            redirect('examinations/grades');
        }

        $data = [
            'title'    => 'Grade Management',
            'page_key' => 'grade-management',
            'grades'   => $this->Grade_model->get_all()
        ];

        $this->render('pages/examinations/grades', $data);
    }

    /* =========================================================================
       9. Result Calculation
       ========================================================================= */
    public function calculate()
    {
        $this->require_permission('exams.create');

        if ($this->input->method() === 'post') {
            $exam_id    = (int)$this->input->post('exam_id');
            $class_id   = $this->input->post('class_id') ? (int)$this->input->post('class_id') : NULL;
            $division_id = $this->input->post('division_id') ? (int)$this->input->post('division_id') : NULL;

            if (empty($exam_id)) {
                $this->session->set_flashdata('error', 'Please select an exam to calculate results.');
                redirect('examinations/calculate');
            }

            if (!empty($class_id) && !empty($division_id)) {
                if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                    $this->session->set_flashdata('error', 'The selected division does not belong to the selected class.');
                    redirect('examinations/calculate');
                }
            }

            $count = $this->Result_model->calculate_results_for_exam($exam_id, $class_id, $division_id, $this->current_user->user_id);

            $this->session->set_flashdata('success', "Results and ranks calculated successfully for {$count} students.");
            redirect('examinations/results?exam_id=' . $exam_id);
        }

        $class_id  = $this->input->get('class_id');
        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'          => 'Result Calculation',
            'page_key'       => 'result-calculation',
            'exams'          => $this->Exam_model->get_all(['academic_year_id' => $this->academic_year_id]),
            'classes'        => $this->Class_model->get_all($this->academic_year_id),
            'divisions'      => $divisions,
            'sections'       => $divisions,
            'settings'       => $this->Exam_setting_model->get_settings()
        ];

        $this->render('pages/examinations/calculate', $data);
    }

    /* =========================================================================
       10. Student Results List
       ========================================================================= */
    public function results()
    {
        $this->require_permission('exams.view');
        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'exam_id'          => $this->input->get('exam_id'),
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $class_id,
            'division_id'      => $division_id,
            'pass_status'      => $this->input->get('pass_status'),
            'is_published'     => $this->input->get('is_published'),
            'search'           => $this->input->get('search')
        ];

        $results = $this->Result_model->get_results_list($filters);
        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'          => 'Student Results',
            'page_key'       => 'results',
            'results'        => $results,
            'exams'          => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'academic_years' => $this->Academic_year_model->get_all(),
            'classes'        => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'      => $divisions,
            'sections'       => $divisions,
            'filters'        => $filters
        ];

        $this->render('pages/examinations/results', $data);
    }

    /* =========================================================================
       11. Single Result Detail
       ========================================================================= */
    public function result_detail($result_id = NULL)
    {
        $this->require_permission('exams.view');
        $result = $this->Result_model->get_result_by_id($result_id);
        if (!$result) {
            $this->session->set_flashdata('error', 'Result record not found.');
            redirect('examinations/results');
        }

        $data = [
            'title'    => 'Result Details — ' . $result->first_name . ' ' . $result->last_name,
            'page_key' => 'results',
            'result'   => $result
        ];

        $this->render('pages/examinations/result_detail', $data);
    }

    /* =========================================================================
       12. Rank / Position
       ========================================================================= */
    public function ranks()
    {
        $this->require_permission('exams.view');
        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'exam_id'          => $this->input->get('exam_id'),
            'class_id'         => $class_id,
            'division_id'      => $division_id
        ];

        $ranks_data = $filters['exam_id'] ? $this->Result_model->get_results_list($filters) : [];
        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'      => 'Rank & Positions',
            'page_key'   => 'exam-ranks',
            'results'    => $ranks_data,
            'exams'      => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'classes'    => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'  => $divisions,
            'sections'   => $divisions,
            'filters'    => $filters,
            'settings'   => $this->Exam_setting_model->get_settings()
        ];

        $this->render('pages/examinations/ranks', $data);
    }

    /* =========================================================================
       13. Report Cards
       ========================================================================= */
    public function report_cards()
    {
        $this->require_permission('exams.view');
        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'exam_id'          => $this->input->get('exam_id'),
            'class_id'         => $class_id,
            'division_id'      => $division_id
        ];

        $results = ($filters['exam_id'] && $filters['class_id']) ? $this->Result_model->get_results_list($filters) : [];
        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'      => 'Report Cards',
            'page_key'   => 'report-cards',
            'results'    => $results,
            'exams'      => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'classes'    => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'  => $divisions,
            'sections'   => $divisions,
            'filters'    => $filters
        ];

        $this->render('pages/examinations/report_cards', $data);
    }

    public function report_card($result_id = NULL)
    {
        $this->require_permission('exams.view');
        $result = $this->Result_model->get_result_by_id($result_id);
        if (!$result) {
            $this->session->set_flashdata('error', 'Report card not found.');
            redirect('examinations/report_cards');
        }

        // Attendance summary
        $this->load->model('Attendance_model');
        $attendance = $this->Attendance_model->get_student_profile_attendance($result->student_id, $result->academic_year_id);

        $this->load->library('document_design_service');
        $target_school_id = !empty($result->school_id) ? (int)$result->school_id : $this->school_id;
        $document_design = $this->document_design_service->resolve_design($target_school_id, 'report_card');

        $data = [
            'title'           => 'Report Card — ' . $result->first_name . ' ' . $result->last_name,
            'page_key'        => 'report-cards',
            'result'          => $result,
            'attendance'      => $attendance,
            'settings'        => $this->Exam_setting_model->get_settings($target_school_id),
            'document_design' => $document_design,
        ];

        $this->render_document('pages/examinations/report_card_view', $data);
    }

    /* =========================================================================
       14. Progress Reports (Multi-Exam Comparative)
       ========================================================================= */
    public function progress_reports()
    {
        $this->require_permission('exams.view');
        $filters = [
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'class_id'         => $this->input->get('class_id') ?: '',
            'division_id'      => $this->input->get('division_id') ?: $this->input->get('section_id') ?: '',
            'search'           => $this->input->get('search') ?: ''
        ];

        $students = $this->Result_model->get_progress_report_students($filters);
        $divisions = !empty($filters['class_id'])
            ? $this->Division_model->get_by_class($filters['class_id'])
            : [];

        $data = [
            'title'      => 'Progress Reports',
            'page_key'   => 'progress-reports',
            'students'   => $students,
            'classes'    => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'  => $divisions,
            'sections'   => $divisions,
            'filters'    => $filters
        ];

        $this->render('pages/examinations/progress_reports', $data);
    }

    public function progress_report($student_id = NULL)
    {
        $this->require_permission('exams.view');
        $report = $this->Result_model->get_student_progress_report($student_id);
        if (!$report) {
            $this->session->set_flashdata('error', 'Student progress data not found.');
            redirect('examinations/progress_reports');
        }

        $this->load->library('document_design_service');
        $target_school_id = !empty($report->student->school_id) ? (int)$report->student->school_id : $this->school_id;
        $document_design = $this->document_design_service->resolve_design($target_school_id, 'progress_report');

        $data = [
            'title'           => 'Progress Report — ' . $report->student->first_name . ' ' . $report->student->last_name,
            'page_key'        => 'progress-reports',
            'report'          => $report,
            'document_design' => $document_design,
        ];

        $this->render_document('pages/examinations/progress_report_view', $data);
    }

    /* =========================================================================
       15. Result Publishing & Locking
       ========================================================================= */
    public function publishing()
    {
        $this->require_permission('exams.publish');

        if ($this->input->method() === 'post') {
            $action  = $this->input->post('action');
            $exam_id = (int)$this->input->post('exam_id');

            if ($action === 'publish') {
                $this->Result_model->publish_results($exam_id, NULL, NULL, $this->current_user->user_id);
                $this->session->set_flashdata('success', 'Exam results published and locked successfully.');
            } elseif ($action === 'unlock') {
                $reason = trim($this->input->post('unlock_reason'));
                $this->Result_model->unlock_results_for_correction($exam_id, $this->current_user->user_id, $reason);
                $this->session->set_flashdata('success', 'Exam results unlocked for correction.');
            }

            redirect('examinations/publishing');
        }

        $data = [
            'title'    => 'Result Publishing',
            'page_key' => 'result-publishing',
            'exams'    => $this->Exam_model->get_all(['academic_year_id' => $this->academic_year_id]),
            'logs'     => $this->Exam_audit_model->get_logs(20, 'tbl_exams')
        ];

        $this->render('pages/examinations/publishing', $data);
    }

    /* =========================================================================
       16. Examination Reports (with CSV Export)
       ========================================================================= */
    public function reports()
    {
        $this->require_permission('exams.reports');
        $report_type = $this->input->get('type') ?: 'exam_performance';

        $class_id    = $this->input->get('class_id');
        $raw_div     = $this->input->get('division_id');
        $division_id = (!empty($raw_div) && $raw_div !== 'all') ? $raw_div : NULL;

        if (!empty($class_id) && !empty($division_id)) {
            if (!$this->Division_model->is_valid_division_for_class($division_id, $class_id)) {
                $division_id = NULL;
            }
        } elseif (empty($class_id)) {
            $division_id = NULL;
        }

        $filters = [
            'academic_year_id' => $this->input->get('academic_year_id') ?: $this->academic_year_id,
            'exam_id'          => $this->input->get('exam_id'),
            'class_id'         => $class_id,
            'division_id'      => $division_id
        ];

        $results = [];
        if ($report_type === 'rank_report') {
            $results = $this->Result_model->get_results_list($filters);
        } else {
            $results = $this->Result_model->get_results_list($filters);
        }

        // CSV Export
        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="examination_report_' . date('Ymd_His') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Roll No', 'Admission No', 'Student', 'Class', 'Division', 'Total Marks', 'Max Marks', 'Percentage', 'Grade', 'Status', 'Rank']);
            foreach ($results as $r) {
                fputcsv($out, [
                    $r->roll_number,
                    $r->admission_number,
                    $r->first_name . ' ' . $r->last_name,
                    $r->class_name,
                    $r->division_name,
                    $r->total_marks,
                    $r->max_marks,
                    $r->percentage . '%',
                    $r->overall_grade,
                    $r->pass_status,
                    $r->class_rank ?: '—'
                ]);
            }
            fclose($out);
            exit;
        }

        $divisions = !empty($class_id) ? $this->Division_model->get_all($class_id) : [];

        $data = [
            'title'       => 'Examination Reports',
            'page_key'    => 'exam-reports',
            'report_type' => $report_type,
            'results'     => $results,
            'exams'       => $this->Exam_model->get_all(['academic_year_id' => $filters['academic_year_id']]),
            'classes'     => $this->Class_model->get_all($filters['academic_year_id']),
            'divisions'   => $divisions,
            'sections'    => $divisions,
            'filters'     => $filters
        ];

        $this->render('pages/examinations/reports', $data);
    }

    /* =========================================================================
       17. Examination Settings
       ========================================================================= */
    public function settings()
    {
        $this->require_permission('exams.create');

        if ($this->input->method() === 'post') {
            $save_data = [
                'decimal_precision'              => (int)$this->input->post('decimal_precision') ?: 2,
                'default_max_marks'              => (float)$this->input->post('default_max_marks') ?: 100.00,
                'default_passing_marks'          => (float)$this->input->post('default_passing_marks') ?: 35.00,
                'subject_pass_mark_rule'         => $this->input->post('subject_pass_mark_rule') ? 1 : 0,
                'overall_pass_percentage'        => (float)$this->input->post('overall_pass_percentage') ?: 35.00,
                'single_subject_fail_overall'    => $this->input->post('single_subject_fail_overall') ? 1 : 0,
                'rank_criteria'                  => $this->input->post('rank_criteria') ?: 'Percentage',
                'include_failed_in_rank'         => $this->input->post('include_failed_in_rank') ? 1 : 0,
                'show_rank_on_report_card'       => $this->input->post('show_rank_on_report_card') ? 1 : 0,
                'show_attendance_on_report_card' => $this->input->post('show_attendance_on_report_card') ? 1 : 0,
                'report_card_header'             => trim($this->input->post('report_card_header')),
                'principal_signature_title'      => trim($this->input->post('principal_signature_title')),
                'teacher_signature_title'        => trim($this->input->post('teacher_signature_title'))
            ];

            $this->Exam_setting_model->update_settings($save_data);
            $this->session->set_flashdata('success', 'Examination settings updated successfully.');
            redirect('examinations/settings');
        }

        $data = [
            'title'    => 'Examination Settings',
            'page_key' => 'exam-settings',
            'settings' => $this->Exam_setting_model->get_settings()
        ];

        $this->render('pages/examinations/settings', $data);
    }

    /* =========================================================================
       18. AJAX Dropdown Helpers
       ========================================================================= */
    public function ajax_get_divisions($class_id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($class_id)) {
            $class_id = $this->input->get('class_id');
        }
        if (empty($class_id)) {
            echo json_encode([]);
            return;
        }
        $divisions = $this->Division_model->get_all((int)$class_id);
        echo json_encode($divisions);
    }

    public function ajax_get_subjects($class_id = NULL)
    {
        header('Content-Type: application/json');
        if (empty($class_id)) {
            $class_id = $this->input->get('class_id');
        }
        if (empty($class_id)) {
            echo json_encode([]);
            return;
        }
        $academic_year_id = (int)($this->input->get('academic_year_id') ?: $this->academic_year_id);
        $subjects = $this->Subject_model->get_for_class($academic_year_id, (int)$class_id);
        echo json_encode($subjects);
    }
}

