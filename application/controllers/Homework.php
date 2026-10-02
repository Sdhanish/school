<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Homework extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Homework_model');
        $this->load->model('Homework_type_model');
        $this->load->model('Homework_submission_model');
        $this->load->model('Homework_notification_model');
        $this->load->model('Homework_setting_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Subject_model');
        $this->load->model('Staff_model');
        $this->load->model('Student_model');
        $this->load->model('Grade_model');
        $this->load->model('Subject_teacher_model');
    }

    // 1. Homework Dashboard
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission('homework.view');
        $year_id = $this->academic_year_id;
        $active_year = $this->Academic_year_model->get_by_id($year_id);

        $data['title'] = 'Homework Dashboard';
        $data['active_year'] = $active_year;
        $data['stats'] = $this->Homework_model->get_dashboard_stats($year_id);
        $data['recent_assignments'] = $this->Homework_model->get_all(['academic_year_id' => $year_id], 6);
        $data['upcoming_deadlines'] = $this->Homework_model->get_upcoming_deadlines($year_id, 6);

        $this->render('pages/homework/dashboard', $data);
    }

    // 2. Assignment List
    public function assignments()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;

        $filters = [
            'academic_year_id'   => $year_id,
            'class_id'           => $this->input->get('class_id') ?: NULL,
            'division_id'         => $this->input->get('division_id') ?: NULL,
            'subject_id'         => $this->input->get('subject_id') ?: NULL,
            'teacher_id'         => $this->input->get('teacher_id') ?: NULL,
            'assignment_type_id' => $this->input->get('assignment_type_id') ?: NULL,
            'status'             => $this->input->get('status') ?: NULL,
            'due_date'           => $this->input->get('due_date') ?: NULL,
            'search'             => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Assignments Directory';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['divisions'] = $filters['class_id'] ? $this->Division_model->get_by_class($filters['class_id']) : [];
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $data['teachers'] = $this->Staff_model->get_teaching_staff();
        $data['types'] = $this->Homework_type_model->get_all(TRUE);
        $data['filters'] = $filters;
        $data['assignments'] = $this->Homework_model->get_all($filters);

        $this->render('pages/homework/assignments', $data);
    }

    // 3. Create Assignment
    public function create()
    {
        $this->require_permission('homework.create');
        $year_id = (int)($this->input->post('academic_year_id') ?: $this->academic_year_id);

        if ($this->input->post()) {
            $class_id = (int)$this->input->post('class_id');
            $division_id = (int)$this->input->post('division_id');
            $subject_id = (int)$this->input->post('subject_id');
            $teacher_id = (int)$this->input->post('teacher_id');
            $status = $this->input->post('submit_action') === 'publish' ? 'Published' : 'Draft';

            // Teacher Department / Group Integrity Check
            if ($teacher_id > 0 && $class_id > 0) {
                $group_check = $this->Staff_model->validate_teacher_class_group($teacher_id, $class_id, $this->school_id);
                if (!$group_check['valid']) {
                    $this->session->set_flashdata('error', $group_check['error']);
                    redirect('homework/create');
                    return;
                }
            }

            // Teacher Subject Authorization Check (Section 6)
            $is_super = in_array($this->session->userdata('role_id'), [1, 2]); // Super Admin / Principal
            if (!$is_super && $teacher_id > 0) {
                $is_authorized = $this->Subject_teacher_model->is_assigned($year_id, $class_id, $division_id, $subject_id, $teacher_id);
                if (!$is_authorized) {
                    $this->session->set_flashdata('error', 'Selected teacher is not authorized to teach this subject in the selected class/division.');
                    redirect('homework/create');
                    return;
                }
            }

            // Handle attachment uploads
            $attachments = [];
            if (!empty($_FILES['attachments']['name'][0])) {
                $upload_path = './uploads/homework/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png|zip|txt';
                $config['max_size']      = 10240; // 10MB
                $this->load->library('upload', $config);

                $files = $_FILES['attachments'];
                $count = count($files['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (!empty($files['name'][$i])) {
                        $_FILES['file']['name']     = $files['name'][$i];
                        $_FILES['file']['type']     = $files['type'][$i];
                        $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                        $_FILES['file']['error']    = $files['error'][$i];
                        $_FILES['file']['size']     = $files['size'][$i];

                        if ($this->upload->do_upload('file')) {
                            $upData = $this->upload->data();
                            $attachments[] = [
                                'orig_name' => $files['name'][$i],
                                'file_name' => $upData['file_name'],
                                'file_size' => $upData['file_size'],
                                'file_type' => $upData['file_type']
                            ];
                        }
                    }
                }
            }

            $asgnData = [
                'academic_year_id'      => $year_id,
                'class_id'              => $class_id,
                'division_id'           => $division_id,
                'subject_id'            => $subject_id,
                'teacher_id'            => $teacher_id,
                'assignment_type_id'    => (int)$this->input->post('assignment_type_id'),
                'title'                 => trim($this->input->post('title')),
                'description'           => trim($this->input->post('description')),
                'instructions'          => trim($this->input->post('instructions')),
                'assigned_date'         => $this->input->post('assigned_date') ?: date('Y-m-d'),
                'due_date'              => $this->input->post('due_date'),
                'due_time'              => $this->input->post('due_time') ?: '23:59:00',
                'max_marks'             => (float)$this->input->post('max_marks') ?: 10.00,
                'allow_remarks'         => $this->input->post('allow_remarks') ? 1 : 0,
                'allow_file_submission' => $this->input->post('allow_file_submission') ? 1 : 0,
                'allow_text_submission' => $this->input->post('allow_text_submission') ? 1 : 0,
                'allow_multiple_files'  => $this->input->post('allow_multiple_files') ? 1 : 0,
                'allow_resubmission'    => $this->input->post('allow_resubmission') ? 1 : 0,
                'allow_late_submission' => $this->input->post('allow_late_submission') ? 1 : 0,
                'target_type'           => $this->input->post('target_type') ?: 'Section',
                'status'                => $status,
                'attachments'           => !empty($attachments) ? json_encode($attachments) : NULL,
                'created_by'            => $this->session->userdata('user_id') ?: 1
            ];

            $asgn_id = $this->Homework_model->insert($asgnData);

            // Queue student notifications if published
            if ($status === 'Published') {
                $this->Homework_notification_model->queue_class_notifications(
                    $asgn_id,
                    'New Assignment',
                    "New {$asgnData['title']} assignment has been published. Due on " . date('d M Y', strtotime($asgnData['due_date']))
                );
            }

            $this->session->set_flashdata('success', "Assignment '{$asgnData['title']}' created successfully (" . ($status === 'Published' ? 'Published' : 'Saved as Draft') . ")!");
            redirect('homework/assignments');
            return;
        }

        $data['title'] = 'Create Assignment';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['classes'] = $this->Class_model->get_all(TRUE);
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $data['teachers'] = $this->Staff_model->get_teaching_staff();
        $data['types'] = $this->Homework_type_model->get_all(TRUE);

        $this->render('pages/homework/create', $data);
    }

    // 4. Edit Assignment
    public function edit($id)
    {
        $this->require_permission('homework.create');
        $assignment = $this->Homework_model->get_by_id($id);
        if (!$assignment) {
            $this->session->set_flashdata('error', 'Assignment not found.');
            redirect('homework/assignments');
            return;
        }

        if ($this->input->post()) {
            $status = $this->input->post('submit_action') === 'publish' ? 'Published' : $this->input->post('status');

            $teacher_id = (int)$this->input->post('teacher_id');
            $class_id = (int)$this->input->post('class_id');

            // Teacher Department / Group Integrity Check
            if ($teacher_id > 0 && $class_id > 0) {
                $group_check = $this->Staff_model->validate_teacher_class_group($teacher_id, $class_id, $this->school_id);
                if (!$group_check['valid']) {
                    $this->session->set_flashdata('error', $group_check['error']);
                    redirect('homework/edit/' . $id);
                    return;
                }
            }

            $updateData = [
                'class_id'              => $class_id,
                'division_id'            => (int)$this->input->post('division_id'),
                'subject_id'            => (int)$this->input->post('subject_id'),
                'teacher_id'            => $teacher_id,
                'assignment_type_id'    => (int)$this->input->post('assignment_type_id'),
                'title'                 => trim($this->input->post('title')),
                'description'           => trim($this->input->post('description')),
                'instructions'          => trim($this->input->post('instructions')),
                'assigned_date'         => $this->input->post('assigned_date'),
                'due_date'              => $this->input->post('due_date'),
                'due_time'              => $this->input->post('due_time') ?: '23:59:00',
                'max_marks'             => (float)$this->input->post('max_marks'),
                'allow_remarks'         => $this->input->post('allow_remarks') ? 1 : 0,
                'allow_file_submission' => $this->input->post('allow_file_submission') ? 1 : 0,
                'allow_text_submission' => $this->input->post('allow_text_submission') ? 1 : 0,
                'allow_multiple_files'  => $this->input->post('allow_multiple_files') ? 1 : 0,
                'allow_resubmission'    => $this->input->post('allow_resubmission') ? 1 : 0,
                'allow_late_submission' => $this->input->post('allow_late_submission') ? 1 : 0,
                'target_type'           => $this->input->post('target_type') ?: 'Section',
                'status'                => $status,
            ];

            $this->Homework_model->update($id, $updateData);
            $this->session->set_flashdata('success', 'Assignment updated successfully.');
            redirect('homework/details/' . $id);
            return;
        }

        $data['title'] = 'Edit Assignment';
        $data['assignment'] = $assignment;
        $data['classes'] = $this->Class_model->get_all(TRUE);
        $data['divisions'] = $this->Division_model->get_by_class($assignment->class_id);
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $cls_obj = $this->Class_model->get_by_id($assignment->class_id, $this->school_id);
        $data['teachers'] = ($cls_obj && !empty($cls_obj->academic_group_id))
            ? $this->Staff_model->get_teachers_by_group($cls_obj->academic_group_id, $this->school_id)
            : $this->Staff_model->get_teaching_staff();
        $data['types'] = $this->Homework_type_model->get_all(TRUE);

        $this->render('pages/homework/edit', $data);
    }

    // 5. Assignment Details
    public function details($id)
    {
        $this->require_permission('homework.view');
        $assignment = $this->Homework_model->get_by_id($id);
        if (!$assignment) {
            $this->session->set_flashdata('error', 'Assignment not found.');
            redirect('homework/assignments');
            return;
        }

        $data['title'] = 'Assignment Details';
        $data['assignment'] = $assignment;
        $data['submissions'] = $this->Homework_submission_model->get_submissions(['assignment_id' => $id]);
        
        // Roster of enrolled students in this class/division
        $students = $this->db
            ->where('class_id', $assignment->class_id)
            ->where('division_id', $assignment->division_id)
            ->where('status', 1)
            ->order_by('roll_number', 'ASC')
            ->order_by('first_name', 'ASC')
            ->get('tbl_students')
            ->result();

        $sub_map = [];
        foreach ($data['submissions'] as $s) {
            $sub_map[$s->student_id] = $s;
        }
        $data['students'] = $students;
        $data['sub_map'] = $sub_map;

        $this->render('pages/homework/details', $data);
    }

    // 6. Assignment Types
    public function types()
    {
        $this->require_permission('homework.create');
        if ($this->input->post()) {
            $type_id = $this->input->post('type_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Homework_type_model->delete($type_id);
                $this->session->set_flashdata('success', 'Assignment type removed.');
            } else {
                $postData = [
                    'type_name'   => trim($this->input->post('type_name')),
                    'description' => trim($this->input->post('description')),
                    'status'      => $this->input->post('status') ? 1 : 0
                ];
                if ($type_id) {
                    $this->Homework_type_model->update($type_id, $postData);
                    $this->session->set_flashdata('success', 'Assignment type updated.');
                } else {
                    $this->Homework_type_model->insert($postData);
                    $this->session->set_flashdata('success', 'New assignment type created.');
                }
            }
            redirect('homework/types');
            return;
        }

        $data['title'] = 'Assignment Types';
        $data['types'] = $this->Homework_type_model->get_all();
        $this->render('pages/homework/types', $data);
    }

    // 7. Subject-wise Assignments
    public function subjects()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $subject_id = $this->input->get('subject_id');

        $filters = ['academic_year_id' => $year_id];
        if ($subject_id) $filters['subject_id'] = $subject_id;

        $data['title'] = 'Subject-wise Assignments';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $data['selected_subject'] = $subject_id;
        $data['assignments'] = $this->Homework_model->get_all($filters);

        $this->render('pages/homework/subjects', $data);
    }

    // 8. Class-wise Assignments
    public function classes()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $class_id = $this->input->get('class_id');
        $division_id = $this->input->get('division_id');

        $filters = ['academic_year_id' => $year_id];
        if ($class_id) $filters['class_id'] = $class_id;
        if ($division_id) $filters['division_id'] = $division_id;

        $data['title'] = 'Class-wise Assignments';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['selected_class'] = $class_id;
        $data['divisions'] = $class_id ? $this->Division_model->get_by_class($class_id) : [];
        $data['sections'] = $data['divisions'];
        $data['selected_division'] = $division_id;
        $data['selected_section'] = $division_id;
        $data['assignments'] = $this->Homework_model->get_all($filters);

        $this->render('pages/homework/classes', $data);
    }

    // 9. Homework Calendar
    public function calendar()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;

        $data['title'] = 'Homework Calendar';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['assignments'] = $this->Homework_model->get_all(['academic_year_id' => $year_id, 'status' => 'Published']);

        $this->render('pages/homework/calendar', $data);
    }

    // 10. Submission Tracking
    public function submissions()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $filters = [
            'academic_year_id' => $year_id,
            'assignment_id'    => $this->input->get('assignment_id') ?: NULL,
            'class_id'         => $this->input->get('class_id') ?: NULL,
            'division_id'       => $this->input->get('division_id') ?: NULL,
            'status'           => $this->input->get('status') ?: NULL,
            'search'           => $this->input->get('search') ?: NULL
        ];

        $data['title'] = 'Submission Tracking';
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['divisions'] = $filters['class_id'] ? $this->Division_model->get_by_class($filters['class_id']) : [];
        $data['assignments'] = $this->Homework_model->get_all(['academic_year_id' => $year_id, 'status' => 'Published']);
        $data['filters'] = $filters;
        $data['submissions'] = $this->Homework_submission_model->get_submissions($filters);

        $this->render('pages/homework/submissions', $data);
    }

    // 11. Submission Details
    public function submission_detail($id)
    {
        $this->require_permission('homework.view');
        $submission = $this->Homework_submission_model->get_by_id($id);
        if (!$submission) {
            $this->session->set_flashdata('error', 'Submission record not found.');
            redirect('homework/submissions');
            return;
        }

        $data['title'] = 'Submission Details';
        $data['submission'] = $submission;
        $data['history'] = $this->Homework_submission_model->get_history($id);

        $this->render('pages/homework/submission_detail', $data);
    }

    // 12. Teacher Review Form
    public function review($id)
    {
        $this->require_permission('homework.review');
        $submission = $this->Homework_submission_model->get_by_id($id);
        if (!$submission) {
            $this->session->set_flashdata('error', 'Submission record not found.');
            redirect('homework/submissions');
            return;
        }

        if ($this->input->post()) {
            $action = $this->input->post('review_action'); // 'complete' or 'return'
            $marks = $this->input->post('marks_obtained');
            $remarks = trim($this->input->post('teacher_remarks'));
            $correction_reason = trim($this->input->post('correction_reason'));

            $reviewData = [
                'action'            => $action,
                'marks_obtained'    => ($action === 'complete' && $marks !== '') ? $marks : NULL,
                'teacher_remarks'   => $remarks,
                'correction_reason' => ($action === 'return') ? $correction_reason : NULL,
                'reviewed_by'       => $this->session->userdata('user_id') ?: 1
            ];

            $res = $this->Homework_submission_model->review_submission($id, $reviewData);
            if ($res['success']) {
                // Queue student notification
                $notifType = ($action === 'return') ? 'Returned' : 'Submission Reviewed';
                $notifMsg = ($action === 'return') 
                    ? "Your assignment {$submission->assignment_title} has been returned for correction: {$correction_reason}"
                    : "Your assignment {$submission->assignment_title} has been evaluated by your teacher. Marks: {$marks}/{$submission->max_marks}";

                $this->Homework_notification_model->queue_notification(
                    $submission->assignment_id,
                    $submission->student_id,
                    $notifType,
                    $notifMsg
                );

                $this->session->set_flashdata('success', ($action === 'return') ? 'Assignment returned to student for correction.' : 'Submission successfully reviewed & graded!');
                redirect('homework/details/' . $submission->assignment_id);
                return;
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
        }

        $data['title'] = 'Review Submission';
        $data['submission'] = $submission;
        $data['history'] = $this->Homework_submission_model->get_history($id);

        $this->render('pages/homework/review', $data);
    }

    // 13. Student-Facing Submission View & Form
    public function student_view($id)
    {
        $this->require_permission('homework.view');
        $assignment = $this->Homework_model->get_by_id($id);
        if (!$assignment) {
            $this->session->set_flashdata('error', 'Assignment not found.');
            redirect('homework/assignments');
            return;
        }

        // Student ID: from query or active student context
        $student_id = (int)$this->input->get('student_id') ?: 1;
        $student = $this->Student_model->get_by_id($student_id);

        if ($this->input->post()) {
            $submitted_text = trim($this->input->post('submitted_text'));
            
            // Handle file uploads
            $files_uploaded = [];
            if (!empty($_FILES['submission_files']['name'][0])) {
                $upload_path = './uploads/submissions/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png|zip|txt';
                $config['max_size']      = 10240;
                $this->load->library('upload', $config);

                $files = $_FILES['submission_files'];
                $count = count($files['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (!empty($files['name'][$i])) {
                        $_FILES['file']['name']     = $files['name'][$i];
                        $_FILES['file']['type']     = $files['type'][$i];
                        $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                        $_FILES['file']['error']    = $files['error'][$i];
                        $_FILES['file']['size']     = $files['size'][$i];

                        if ($this->upload->do_upload('file')) {
                            $up = $this->upload->data();
                            $files_uploaded[] = [
                                'orig_name' => $files['name'][$i],
                                'file_name' => $up['file_name'],
                                'file_size' => $up['file_size']
                            ];
                        }
                    }
                }
            }

            $submitPayload = [
                'submitted_text'  => $submitted_text,
                'submitted_files' => !empty($files_uploaded) ? json_encode($files_uploaded) : NULL
            ];

            $res = $this->Homework_submission_model->submit_assignment($id, $student_id, $submitPayload);
            if ($res['success']) {
                $this->Homework_notification_model->queue_notification(
                    $id, $student_id, 'Submission Received', "Your submission for '{$assignment->title}' was received successfully."
                );
                $this->session->set_flashdata('success', 'Assignment submitted successfully!');
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
            redirect('homework/student_view/' . $id . '?student_id=' . $student_id);
            return;
        }

        $data['title'] = 'Submit Assignment';
        $data['assignment'] = $assignment;
        $data['student'] = $student;
        $data['submission'] = $this->Homework_submission_model->get_by_assignment_and_student($id, $student_id);
        if ($data['submission']) {
            $data['history'] = $this->Homework_submission_model->get_history($data['submission']->submission_id);
        } else {
            $data['history'] = [];
        }

        $this->render('pages/homework/student_view', $data);
    }

    // 14. Assignment Reports
    public function reports()
    {
        $this->require_permission('homework.view');
        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $report_type = $this->input->get('type') ?: 'completion';
        $class_id = $this->input->get('class_id');
        $subject_id = $this->input->get('subject_id');

        $filters = ['academic_year_id' => $year_id];
        if ($class_id) $filters['class_id'] = $class_id;
        if ($subject_id) $filters['subject_id'] = $subject_id;

        $assignments = $this->Homework_model->get_all($filters);

        // CSV Export
        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=assignment_report_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Assignment', 'Class', 'Section', 'Subject', 'Teacher', 'Assigned Date', 'Due Date', 'Total Students', 'Submitted', 'Pending', 'Late', 'Reviewed', 'Completion %']);
            foreach ($assignments as $a) {
                $st = $a->submission_stats;
                fputcsv($out, [
                    $a->title, $a->class_name, $a->section_name, $a->subject_name, $a->teacher_name,
                    $a->assigned_date, $a->due_date, $st->total_students, $st->submitted, $st->pending,
                    $st->late, $st->reviewed, $st->completion_pct . '%'
                ]);
            }
            fclose($out);
            exit;
        }

        $data['title'] = 'Assignment Reports';
        $data['academic_years'] = $this->Academic_year_model->get_all();
        $data['selected_year'] = $year_id;
        $data['report_type'] = $report_type;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['subjects'] = $this->Subject_model->get_all(TRUE);
        $data['assignments'] = $assignments;
        $data['filters'] = ['class_id' => $class_id, 'subject_id' => $subject_id];

        $this->render('pages/homework/reports', $data);
    }

    // 15. Homework Settings
    public function settings()
    {
        $this->require_permission('homework.create');
        if ($this->input->post()) {
            $postData = [
                'default_submission_deadline_days' => (int)$this->input->post('default_submission_deadline_days'),
                'allow_late_submissions_default'  => $this->input->post('allow_late_submissions_default') ? 1 : 0,
                'max_upload_size_mb'               => (int)$this->input->post('max_upload_size_mb'),
                'allowed_file_extensions'          => trim($this->input->post('allowed_file_extensions')),
                'enable_grading'                   => $this->input->post('enable_grading') ? 1 : 0,
                'enable_parent_notifications'      => $this->input->post('enable_parent_notifications') ? 1 : 0,
            ];

            $this->Homework_setting_model->update_settings($postData);
            $this->session->set_flashdata('success', 'Homework settings updated successfully.');
            redirect('homework/settings');
            return;
        }

        $data['title'] = 'Homework Settings';
        $data['settings'] = $this->Homework_setting_model->get_settings();
        $data['audit_logs'] = $this->Homework_setting_model->get_audit_logs(30);

        $this->render('pages/homework/settings', $data);
    }

    // Actions: Duplicate, Publish, Archive, Delete
    public function duplicate($id)
    {
        $this->require_permission('homework.create');
        $new_id = $this->Homework_model->duplicate($id);
        if ($new_id) {
            $this->session->set_flashdata('success', 'Assignment duplicated as Draft. You can now edit and publish it.');
            redirect('homework/edit/' . $new_id);
        } else {
            $this->session->set_flashdata('error', 'Failed to duplicate assignment.');
            redirect('homework/assignments');
        }
    }

    public function publish($id)
    {
        $this->require_permission('homework.create');
        $this->Homework_model->update($id, ['status' => 'Published']);
        $this->Homework_notification_model->queue_class_notifications($id, 'New Assignment', "An assignment has been published.");
        $this->session->set_flashdata('success', 'Assignment published successfully!');
        redirect('homework/assignments');
    }

    public function archive($id)
    {
        $this->require_permission('homework.create');
        $this->Homework_model->update($id, ['status' => 'Archived']);
        $this->session->set_flashdata('success', 'Assignment archived.');
        redirect('homework/assignments');
    }

    public function delete($id)
    {
        $this->require_permission('homework.create');
        $this->Homework_model->delete($id);
        $this->session->set_flashdata('success', 'Assignment removed.');
        redirect('homework/assignments');
    }
}
