<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_schedule_model extends CI_Model {

    protected $table = 'tbl_exam_schedules';
    protected $primaryKey = 'schedule_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select("s.*, e.exam_name, e.start_date as exam_start_date, e.end_date as exam_end_date,
                c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code,
                st.full_name as teacher_name, y.year_name,
                (SELECT COUNT(*) FROM tbl_students stu WHERE stu.school_id = {$school_id} AND stu.class_id = s.class_id AND stu.division_id = s.division_id AND stu.status = 1) as total_students,
                (SELECT COUNT(*) FROM tbl_exam_marks m WHERE m.school_id = {$school_id} AND m.schedule_id = s.schedule_id) as marks_entered_count")
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->join('tbl_staff st', 'st.staff_id = s.teacher_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = s.academic_year_id', 'left')
            ->where('s.school_id', $school_id)
            ->order_by('s.exam_date', 'ASC')
            ->order_by('s.start_time', 'ASC');

        if (!empty($filters['exam_id'])) {
            $this->db->where('s.exam_id', $filters['exam_id']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('s.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('s.class_id', $filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->group_start()
                ->where('s.division_id', $filters['division_id'])
                ->or_where('s.division_id IS NULL', NULL, FALSE)
                ->group_end();
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('s.subject_id', $filters['subject_id']);
        }
        if (!empty($filters['teacher_id'])) {
            $this->db->where('s.teacher_id', $filters['teacher_id']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('s.exam_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('s.exam_date <=', $filters['to_date']);
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('s.*, e.exam_name, e.start_date as exam_start_date, e.end_date as exam_end_date,
                c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code,
                st.full_name as teacher_name, y.year_name')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->join('tbl_staff st', 'st.staff_id = s.teacher_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = s.academic_year_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.schedule_id', (int)$id)
            ->get()
            ->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('schedule_id', (int)$id)
            ->delete('tbl_exam_marks');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->delete($this->table);
    }

    public function check_duplicate_schedule($exam_id, $class_id, $section_id, $subject_id, $exclude_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->where('class_id', (int)$class_id)
            ->where('subject_id', (int)$subject_id);

        if (!empty($section_id)) {
            $this->db->where('division_id', (int)$section_id);
        } else {
            $this->db->where('division_id IS NULL', NULL, FALSE);
        }

        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function check_room_conflict($exam_date, $start_time, $end_time, $room_no, $exclude_id = NULL)
    {
        if (empty($room_no)) return NULL;

        $school_id = $this->get_school_id();
        $this->db
            ->select('s.*, e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name')
            ->from('tbl_exam_schedules s')
            ->join('tbl_exams e', 'e.exam_id = s.exam_id', 'left')
            ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = s.subject_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.exam_date', $exam_date)
            ->where('s.room_no', $room_no)
            ->where('s.start_time <', $end_time)
            ->where('s.end_time >', $start_time);

        if ($exclude_id) {
            $this->db->where('s.' . $this->primaryKey . ' !=', (int)$exclude_id);
        }

        return $this->db->get()->row();
    }
}
