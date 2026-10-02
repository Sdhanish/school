<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_model extends CI_Model {

    protected $table = 'tbl_exams';
    protected $primaryKey = 'exam_id';

    public function get_all($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('e.exam_id, e.exam_name, e.exam_type_id, e.academic_year_id, e.description, e.start_date, e.end_date, e.applicable_classes, e.status, e.created_at, e.updated_at, e.is_deleted, e.school_id, t.type_name, y.year_name,
                (SELECT COUNT(*) FROM tbl_exam_schedules s WHERE s.exam_id = e.exam_id AND s.school_id = ' . (int)$school_id . ') as schedule_count,
                (SELECT COUNT(DISTINCT m.student_id) FROM tbl_exam_marks m WHERE m.exam_id = e.exam_id AND m.school_id = ' . (int)$school_id . ') as marks_student_count,
                (SELECT COUNT(*) FROM tbl_student_results r WHERE r.exam_id = e.exam_id AND r.school_id = ' . (int)$school_id . ') as result_count')
            ->from('tbl_exams e')
            ->join('tbl_exam_types t', 't.exam_type_id = e.exam_type_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = e.academic_year_id', 'left')
            ->where('e.is_deleted', 'n')
            ->where('e.school_id', $school_id)
            ->order_by('e.start_date', 'DESC');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('e.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['exam_type_id'])) {
            $this->db->where('e.exam_type_id', $filters['exam_type_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('e.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('e.exam_name', $s)
                ->or_like('t.type_name', $s)
                ->or_like('e.description', $s)
                ->group_end();
        }

        $results = $this->db->get()->result();

        // Decode applicable classes efficiently without N+1 queries
        if (!empty($results)) {
            $class_lookup = [];
            $all_classes = $this->db->select('class_id, class_name')->where('is_deleted', 'n')->where('school_id', $school_id)->get('tbl_classes')->result();
            foreach ($all_classes as $cls) {
                $class_lookup[$cls->class_id] = $cls->class_name;
            }

            foreach ($results as $row) {
                $row->class_ids = !empty($row->applicable_classes) ? json_decode($row->applicable_classes, TRUE) : [];
                if (!is_array($row->class_ids)) $row->class_ids = [];
                
                if (!empty($row->class_ids)) {
                    $names = [];
                    foreach ($row->class_ids as $cid) {
                        if (isset($class_lookup[$cid])) {
                            $names[] = $class_lookup[$cid];
                        }
                    }
                    $row->class_names = !empty($names) ? $names : ['All Classes'];
                } else {
                    $row->class_names = ['All Classes'];
                }
            }
        }

        return $results;
    }

    public function get_by_id($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $exam = $this->db
            ->select('e.exam_id, e.exam_name, e.exam_type_id, e.academic_year_id, e.description, e.start_date, e.end_date, e.applicable_classes, e.status, e.created_at, e.updated_at, e.is_deleted, e.school_id, t.type_name, y.year_name')
            ->from('tbl_exams e')
            ->join('tbl_exam_types t', 't.exam_type_id = e.exam_type_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = e.academic_year_id', 'left')
            ->where('e.exam_id', (int)$id)
            ->where('e.school_id', $school_id)
            ->where('e.is_deleted', 'n')
            ->get()
            ->row();

        if ($exam) {
            $exam->class_ids = !empty($exam->applicable_classes) ? json_decode($exam->applicable_classes, TRUE) : [];
            if (!is_array($exam->class_ids)) $exam->class_ids = [];
        }

        return $exam;
    }

    public function insert($data)
    {
        if (isset($data['applicable_classes']) && is_array($data['applicable_classes'])) {
            $data['applicable_classes'] = json_encode(array_map('intval', $data['applicable_classes']));
        }
        if (!isset($data['school_id'])) {
            $data['school_id'] = get_current_school_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        if (isset($data['applicable_classes']) && is_array($data['applicable_classes'])) {
            $data['applicable_classes'] = json_encode(array_map('intval', $data['applicable_classes']));
        }
        unset($data['school_id']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where($this->primaryKey, (int)$id)->where('school_id', $school_id)->update($this->table, $data);
    }

    public function delete($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db->where('exam_id', (int)$id)->where('school_id', $school_id)->update('tbl_exam_marks', ['is_deleted' => 'y']);
        $this->db->where('exam_id', (int)$id)->where('school_id', $school_id)->update('tbl_exam_schedules', ['is_deleted' => 'y']);
        $this->db->where('exam_id', (int)$id)->where('school_id', $school_id)->update('tbl_student_results', ['is_deleted' => 'y']);
        return $this->db->where($this->primaryKey, (int)$id)->where('school_id', $school_id)->update($this->table, ['is_deleted' => 'y', 'status' => 'Archived']);
    }

    /* =========================================================================
       Dashboard & Statistical Queries
       ========================================================================= */
    public function get_dashboard_stats($year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id($school_id);
        $today = date('Y-m-d');

        $this->db->from($this->table)->where('is_deleted', 'n')->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $total_exams = $this->db->count_all_results();

        $this->db->from($this->table)->where('start_date >', $today)->where('is_deleted', 'n')->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $upcoming_exams = $this->db->count_all_results();

        $this->db->from($this->table)->where('end_date <', $today)->where('is_deleted', 'n')->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $completed_exams = $this->db->count_all_results();

        $this->db->from('tbl_student_results')->where('is_published', 1)->where('is_deleted', 'n')->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $published_results = $this->db->count_all_results();

        $this->db->from('tbl_exam_marks')->where('status', 'Draft')->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $pending_marks = $this->db->count_all_results();

        $this->db->from('tbl_exam_marks')->where_in('status', ['Submitted', 'Under Verification'])->where('school_id', $school_id);
        if ($year_id) $this->db->where('academic_year_id', $year_id);
        $pending_verification = $this->db->count_all_results();

        return (object) [
            'total_exams'          => $total_exams,
            'upcoming_exams'       => $upcoming_exams,
            'completed_exams'      => $completed_exams,
            'published_results'    => $published_results,
            'pending_marks'        => $pending_marks,
            'pending_verification' => $pending_verification,
        ];
    }

    public function get_upcoming_exam_schedules($year_id = NULL, $limit = 6, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id($school_id);
        $today = date('Y-m-d');
        $this->db
            ->select('s.schedule_id, s.exam_id, s.academic_year_id, s.class_id, s.division_id, s.subject_id, s.teacher_id, s.exam_date, s.start_time, s.end_time, s.room_no, s.max_marks, s.passing_marks, s.instructions, s.status, s.created_at, s.updated_at, s.is_deleted, s.school_id, e.exam_name, c.class_name, d.division_name, sub.subject_name, st.full_name as teacher_name')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'inner')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->join('tbl_staff st', 'st.staff_id = s.teacher_id', 'left')
            ->where('s.exam_date >=', $today)
            ->where('s.school_id', $school_id)
            ->order_by('s.exam_date', 'ASC')
            ->order_by('s.start_time', 'ASC')
            ->limit($limit);

        if ($year_id) $this->db->where('s.academic_year_id', $year_id);

        return $this->db->get()->result();
    }

    public function get_recent_published_results($year_id = NULL, $limit = 5, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id($school_id);
        $this->db
            ->select('e.exam_id, e.exam_name, c.class_name, div.division_name as division_name, div.division_name as section_name, r.published_at, COUNT(r.result_id) as total_students,
                SUM(CASE WHEN r.pass_status = "Pass" THEN 1 ELSE 0 END) as passed_count')
            ->from('tbl_student_results r')
            ->join('tbl_exams e', 'e.exam_id = r.exam_id', 'inner')
            ->join('tbl_classes c', 'c.class_id = r.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = r.division_id', 'left')
            ->where('r.is_published', 1)
            ->where('r.school_id', $school_id)
            ->group_by(['r.exam_id', 'r.class_id', 'r.division_id', 'r.published_at'])
            ->order_by('r.published_at', 'DESC')
            ->limit($limit);

        if ($year_id) $this->db->where('r.academic_year_id', $year_id);

        return $this->db->get()->result();
    }

    public function get_marks_entry_progress_summary($exam_id = NULL, $year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id($school_id);

        $this->db
            ->select('s.schedule_id, s.exam_id, e.exam_name, c.class_id, c.class_name, d.division_id, d.division_name as division_name, sub.subject_name,
                COUNT(st.student_id) as total_students,
                COUNT(m.mark_id) as entered_marks_count,
                SUM(CASE WHEN m.status = "Approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN m.status = "Submitted" THEN 1 ELSE 0 END) as submitted_count,
                SUM(CASE WHEN m.status = "Draft" THEN 1 ELSE 0 END) as draft_count')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'inner')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->join('tbl_students st', 'st.class_id = s.class_id AND st.division_id = s.division_id AND st.status = 1 AND st.academic_year_id = ' . (int)$year_id . ' AND st.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_exam_marks m', 'm.schedule_id = s.schedule_id AND m.student_id = st.student_id AND m.school_id = ' . (int)$school_id, 'left')
            ->where('s.academic_year_id', $year_id)
            ->where('s.school_id', $school_id)
            ->group_by('s.schedule_id')
            ->order_by('c.class_id', 'ASC')
            ->order_by('d.division_id', 'ASC')
            ->order_by('sub.subject_name', 'ASC');

        if ($exam_id) $this->db->where('s.exam_id', $exam_id);

        $rows = $this->db->get()->result();

        foreach ($rows as $r) {
            $total = (int)$r->total_students;
            $entered = (int)$r->entered_marks_count;
            $r->progress_pct = ($total > 0) ? round(($entered / $total) * 100, 1) : 0;
            if ($r->approved_count == $total && $total > 0) {
                $r->status_label = 'Approved';
            } elseif ($r->submitted_count > 0) {
                $r->status_label = 'Under Verification';
            } elseif ($entered > 0) {
                $r->status_label = 'Draft in Progress';
            } else {
                $r->status_label = 'Pending Entry';
            }
        }

        return $rows;
    }
}
