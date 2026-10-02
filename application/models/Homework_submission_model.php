<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Homework_submission_model extends CI_Model {

    protected $table = 'tbl_assignment_submissions';
    protected $primaryKey = 'submission_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Grade_model');
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_submissions($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('sub.*, a.title as assignment_title, a.max_marks, a.due_date, a.due_time, s.first_name, s.last_name, s.admission_number, s.roll_number, c.class_name, div.division_name as division_name, div.division_name as section_name, subj.subject_name, rev.full_name as reviewer_name')
            ->from('tbl_assignment_submissions sub')
            ->join('tbl_assignments a', 'a.assignment_id = sub.assignment_id', 'left')
            ->join('tbl_students s', 's.student_id = sub.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = s.division_id', 'left')
            ->join('tbl_subjects subj', 'subj.subject_id = a.subject_id', 'left')
            ->join('tbl_staff rev', 'rev.staff_id = sub.reviewed_by', 'left')
            ->where('sub.school_id', $school_id)
            ->order_by('sub.submitted_at', 'DESC');

        if (!empty($filters['assignment_id'])) $this->db->where('sub.assignment_id', $filters['assignment_id']);
        if (!empty($filters['class_id'])) $this->db->where('s.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('s.division_id', $filters['division_id']);
        if (!empty($filters['student_id'])) $this->db->where('sub.student_id', $filters['student_id']);
        if (!empty($filters['status'])) $this->db->where('sub.status', $filters['status']);
        if (!empty($filters['is_late'])) $this->db->where('sub.is_late', $filters['is_late']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('s.first_name', $q)
                ->or_like('s.last_name', $q)
                ->or_like('s.admission_number', $q)
                ->or_like('a.title', $q)
            ->group_end();
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('sub.*, a.title as assignment_title, a.description as assignment_desc, a.instructions, a.max_marks, a.due_date, a.due_time, a.allow_resubmission, s.first_name, s.last_name, s.admission_number, s.roll_number, c.class_name, div.division_name as division_name, div.division_name as section_name, subj.subject_name, rev.full_name as reviewer_name, t.full_name as teacher_name')
            ->from('tbl_assignment_submissions sub')
            ->join('tbl_assignments a', 'a.assignment_id = sub.assignment_id', 'left')
            ->join('tbl_students s', 's.student_id = sub.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = s.division_id', 'left')
            ->join('tbl_subjects subj', 'subj.subject_id = a.subject_id', 'left')
            ->join('tbl_staff rev', 'rev.staff_id = sub.reviewed_by', 'left')
            ->join('tbl_staff t', 't.staff_id = a.teacher_id', 'left')
            ->where('sub.school_id', $school_id)
            ->where('sub.submission_id', (int)$id)
            ->get()
            ->row();
    }

    public function get_by_assignment_and_student($assignment_id, $student_id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where('school_id', $school_id)
            ->where('assignment_id', (int)$assignment_id)
            ->where('student_id', (int)$student_id)
            ->get($this->table)
            ->row();
    }

    public function get_history($submission_id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where('school_id', $school_id)
            ->where('submission_id', (int)$submission_id)
            ->order_by('version', 'ASC')
            ->get('tbl_submission_history')
            ->result();
    }

    public function submit_assignment($assignment_id, $student_id, $data)
    {
        $school_id = $this->get_school_id();
        $assignment = $this->db
            ->where('school_id', $school_id)
            ->where('assignment_id', (int)$assignment_id)
            ->get('tbl_assignments')
            ->row();
        if (!$assignment) {
            return ['success' => FALSE, 'message' => 'Assignment not found.'];
        }

        // Check if student already submitted
        $existing = $this->get_by_assignment_and_student($assignment_id, $student_id);

        // Check late status
        $due_timestamp = strtotime($assignment->due_date . ' ' . ($assignment->due_time ?: '23:59:59'));
        $now = time();
        $is_late = ($now > $due_timestamp) ? 1 : 0;
        $late_mins = $is_late ? max(0, round(($now - $due_timestamp) / 60)) : 0;

        if ($is_late && !$assignment->allow_late_submission) {
            return ['success' => FALSE, 'message' => 'Submission deadline has passed and late submissions are not allowed for this assignment.'];
        }

        $status = $is_late ? 'Late' : 'Submitted';

        if ($existing) {
            if (!$assignment->allow_resubmission && $existing->status !== 'Returned') {
                return ['success' => FALSE, 'message' => 'Resubmission is disabled for this assignment.'];
            }

            // Archive current version to history
            $this->db->insert('tbl_submission_history', [
                'school_id'       => $school_id,
                'submission_id'   => $existing->submission_id,
                'assignment_id'   => $assignment_id,
                'student_id'      => $student_id,
                'version'         => $existing->submission_version,
                'submitted_text'  => $existing->submitted_text,
                'submitted_files' => $existing->submitted_files,
                'submitted_at'    => $existing->submitted_at,
                'marks_obtained'  => $existing->marks_obtained,
                'grade'           => $existing->grade,
                'teacher_remarks' => $existing->teacher_remarks,
                'status'          => $existing->status
            ]);

            // Update to new version
            $updateData = [
                'submission_version'    => (int)$existing->submission_version + 1,
                'submitted_text'        => $data['submitted_text'] ?? $existing->submitted_text,
                'submitted_files'       => $data['submitted_files'] ?? $existing->submitted_files,
                'submitted_at'          => date('Y-m-d H:i:s'),
                'is_late'               => $is_late,
                'late_duration_minutes' => $late_mins,
                'status'                => $status,
                'updated_at'            => date('Y-m-d H:i:s')
            ];
            $this->db->where('submission_id', $existing->submission_id)
                ->where('school_id', $school_id)
                ->update($this->table, $updateData);
            return ['success' => TRUE, 'submission_id' => $existing->submission_id, 'version' => $updateData['submission_version']];
        } else {
            // First time submission
            $insertData = [
                'school_id'             => $school_id,
                'assignment_id'         => $assignment_id,
                'student_id'            => $student_id,
                'submission_version'    => 1,
                'submitted_text'        => $data['submitted_text'] ?? '',
                'submitted_files'       => $data['submitted_files'] ?? '',
                'submitted_at'          => date('Y-m-d H:i:s'),
                'is_late'               => $is_late,
                'late_duration_minutes' => $late_mins,
                'status'                => $status,
                'created_at'            => date('Y-m-d H:i:s')
            ];
            $this->db->insert($this->table, $insertData);
            return ['success' => TRUE, 'submission_id' => $this->db->insert_id(), 'version' => 1];
        }
    }

    public function review_submission($submission_id, $reviewData)
    {
        $school_id = $this->get_school_id();
        $submission = $this->get_by_id($submission_id);
        if (!$submission) return ['success' => FALSE, 'message' => 'Submission record not found.'];

        $marks = isset($reviewData['marks_obtained']) && $reviewData['marks_obtained'] !== '' ? (float)$reviewData['marks_obtained'] : NULL;
        $max_marks = (float)$submission->max_marks;

        if ($marks !== NULL && ($marks < 0 || $marks > $max_marks)) {
            return ['success' => FALSE, 'message' => "Marks obtained ({$marks}) cannot exceed maximum marks ({$max_marks}) or be negative."];
        }

        // Auto-calculate letter grade via Grade_model if marks present
        $grade = NULL;
        if ($marks !== NULL && $max_marks > 0) {
            $pct = round(($marks / $max_marks) * 100, 2);
            $grade_obj = $this->Grade_model->resolve_grade_for_percentage($pct);
            $grade = $grade_obj ? $grade_obj->grade_name : NULL;
        }

        $status = ($reviewData['action'] ?? '') === 'return' ? 'Returned' : 'Reviewed';

        $data = [
            'marks_obtained'    => $marks,
            'grade'             => $grade,
            'teacher_remarks'   => $reviewData['teacher_remarks'] ?? '',
            'correction_reason' => $reviewData['correction_reason'] ?? NULL,
            'status'            => $status,
            'reviewed_by'       => $reviewData['reviewed_by'] ?? 1,
            'reviewed_at'       => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        $this->db
            ->where('submission_id', (int)$submission_id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
        return ['success' => TRUE, 'status' => $status, 'grade' => $grade];
    }
}
