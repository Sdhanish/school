<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_mark_model extends CI_Model {

    protected $table = 'tbl_exam_marks';
    protected $primaryKey = 'mark_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Grade_model');
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_marks_sheet($schedule_id)
    {
        $school_id = $this->get_school_id();
        $schedule = $this->db
            ->select('s.*, e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.schedule_id', (int)$schedule_id)
            ->get()
            ->row();

        if (!$schedule) return NULL;

        // Fetch students for this class & division
        $students = $this->db
            ->select('st.student_id, st.admission_number, st.roll_number, st.first_name, st.last_name, st.photo,
                m.mark_id, m.marks_obtained, m.is_absent, m.is_exempted, m.grade, m.grade_point, m.status as mark_status, m.remarks, m.rejection_reason')
            ->from('tbl_students st')
            ->join('tbl_exam_marks m', 'm.student_id = st.student_id AND m.schedule_id = ' . (int)$schedule_id, 'left')
            ->where('st.school_id', $school_id)
            ->where('st.class_id', $schedule->class_id)
            ->where('st.division_id', $schedule->division_id)
            ->where('st.status', 1)
            ->order_by('CAST(st.roll_number AS UNSIGNED)', 'ASC')
            ->order_by('st.first_name', 'ASC')
            ->get()
            ->result();

        $schedule->students = $students;
        return $schedule;
    }

    public function save_marks_batch($schedule_id, $marks_data, $user_id, $target_status = 'Draft')
    {
        $school_id = $this->get_school_id();
        $schedule = $this->db
            ->where('school_id', $school_id)
            ->where('schedule_id', (int)$schedule_id)
            ->get('tbl_exam_schedules')
            ->row();
        if (!$schedule) return FALSE;

        $max_marks = (float)$schedule->max_marks;
        if ($max_marks <= 0) $max_marks = 100.00;

        $saved_count = 0;

        foreach ($marks_data as $student_id => $row) {
            $is_absent   = !empty($row['is_absent']) ? 1 : 0;
            $is_exempted = !empty($row['is_exempted']) ? 1 : 0;
            $marks_raw   = isset($row['marks_obtained']) ? trim($row['marks_obtained']) : '';
            $remarks     = isset($row['remarks']) ? trim($row['remarks']) : '';

            $marks_obtained = NULL;
            $grade = NULL;
            $grade_point = 0.00;

            if ($is_absent) {
                $marks_obtained = 0.00;
                $grade = 'ABS';
                $grade_point = 0.00;
            } elseif ($is_exempted) {
                $marks_obtained = NULL;
                $grade = 'EXM';
                $grade_point = 0.00;
            } elseif ($marks_raw !== '' && is_numeric($marks_raw)) {
                $marks_obtained = min($max_marks, max(0.00, (float)$marks_raw));
                $pct = ($max_marks > 0) ? ($marks_obtained / $max_marks) * 100 : 0;
                $grade_obj = $this->Grade_model->resolve_grade_for_percentage($pct);
                $grade = $grade_obj->grade_name;
                $grade_point = $grade_obj->grade_point;
            }

            // Check existing mark record
            $existing = $this->db
                ->where('school_id', $school_id)
                ->where('exam_id', $schedule->exam_id)
                ->where('student_id', (int)$student_id)
                ->where('subject_id', $schedule->subject_id)
                ->get($this->table)
                ->row();

            $data = [
                'school_id'        => $school_id,
                'exam_id'          => $schedule->exam_id,
                'schedule_id'      => (int)$schedule_id,
                'student_id'       => (int)$student_id,
                'academic_year_id' => $schedule->academic_year_id,
                'class_id'         => $schedule->class_id,
                'division_id'      => $schedule->division_id,
                'subject_id'       => $schedule->subject_id,
                'marks_obtained'   => $marks_obtained,
                'is_absent'        => $is_absent,
                'is_exempted'      => $is_exempted,
                'grade'            => $grade,
                'grade_point'      => $grade_point,
                'status'           => $target_status,
                'remarks'          => $remarks,
                'entered_by'       => $user_id,
                'updated_at'       => date('Y-m-d H:i:s')
            ];

            if ($target_status === 'Submitted') {
                $data['submitted_at'] = date('Y-m-d H:i:s');
            }

            if ($existing) {
                $this->db
                    ->where('mark_id', $existing->mark_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert($this->table, $data);
            }

            $saved_count++;
        }

        return $saved_count;
    }

    public function get_marksheets_for_verification($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('s.schedule_id, s.exam_id, s.class_id, s.division_id, s.subject_id, s.exam_date,
                e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code,
                u.name as entered_by_name,
                COUNT(st.student_id) as total_students,
                COUNT(m.mark_id) as marks_entered_count,
                SUM(CASE WHEN m.status = "Approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN m.status = "Submitted" THEN 1 ELSE 0 END) as submitted_count,
                SUM(CASE WHEN m.status = "Rejected" THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN m.status = "Draft" THEN 1 ELSE 0 END) as draft_count,
                MAX(m.submitted_at) as latest_submitted_at')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'inner')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->join('tbl_students st', 'st.class_id = s.class_id AND st.division_id = s.division_id AND st.status = 1 AND st.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_exam_marks m', 'm.schedule_id = s.schedule_id AND m.student_id = st.student_id', 'left')
            ->join('tbl_users u', 'u.user_id = m.entered_by', 'left')
            ->where('s.school_id', $school_id)
            ->group_by('s.schedule_id')
            ->order_by('s.exam_date', 'DESC');

        if (!empty($filters['exam_id'])) $this->db->where('s.exam_id', $filters['exam_id']);
        if (!empty($filters['class_id'])) $this->db->where('s.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('s.division_id', $filters['division_id']);

        $rows = $this->db->get()->result();

        foreach ($rows as $r) {
            $total = (int)$r->total_students;
            if ($r->approved_count == $total && $total > 0) {
                $r->verification_status = 'Approved';
            } elseif ($r->rejected_count > 0) {
                $r->verification_status = 'Rejected / Correction Required';
            } elseif ($r->submitted_count > 0) {
                $r->verification_status = 'Submitted (Pending Verification)';
            } elseif ($r->marks_entered_count > 0) {
                $r->verification_status = 'Draft (Incomplete)';
            } else {
                $r->verification_status = 'Not Started';
            }
        }

        if (!empty($filters['status_filter'])) {
            $rows = array_filter($rows, function($r) use ($filters) {
                return ($r->verification_status === $filters['status_filter']);
            });
        }

        return array_values($rows);
    }

    public function verify_marksheet($schedule_id, $action, $user_id, $reason = '')
    {
        $school_id = $this->get_school_id();
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';

        $update_data = [
            'status'           => $new_status,
            'approved_by'      => $user_id,
            'approved_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => ($action === 'reject') ? $reason : NULL,
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        return $this->db
            ->where('school_id', $school_id)
            ->where('schedule_id', (int)$schedule_id)
            ->update($this->table, $update_data);
    }

    public function get_student_subject_marks($exam_id, $student_id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('m.*, sub.subject_name, sub.subject_code, s.max_marks, s.passing_marks, s.exam_date')
            ->from('tbl_exam_marks m')
            ->join('tbl_exam_schedules s', 's.schedule_id = m.schedule_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = m.subject_id', 'left')
            ->where('m.school_id', $school_id)
            ->where('m.exam_id', (int)$exam_id)
            ->where('m.student_id', (int)$student_id)
            ->order_by('sub.subject_name', 'ASC')
            ->get()
            ->result();
    }
}
