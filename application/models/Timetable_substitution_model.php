<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timetable_substitution_model extends CI_Model {

    protected $table = 'tbl_teacher_substitutions';
    protected $primaryKey = 'substitution_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_substitutions($date = NULL, $status = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('sub.*, tt.day, p.period_name, p.start_time, p.end_time, c.class_name, d.division_name as division_name, d.division_name as section_name, s_orig.full_name as original_teacher, s_sub.full_name as substitute_teacher, subj.subject_name')
            ->from('tbl_teacher_substitutions sub')
            ->join('tbl_timetable tt', 'tt.timetable_id = sub.timetable_id', 'left')
            ->join('tbl_periods p', 'p.period_id = tt.period_id', 'left')
            ->join('tbl_classes c', 'c.class_id = tt.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = tt.division_id', 'left')
            ->join('tbl_subjects subj', 'subj.subject_id = tt.subject_id', 'left')
            ->join('tbl_staff s_orig', 's_orig.staff_id = sub.original_teacher_id', 'left')
            ->join('tbl_staff s_sub', 's_sub.staff_id = sub.substitute_teacher_id', 'left')
            ->where('sub.school_id', $school_id)
            ->order_by('sub.substitution_date', 'DESC')
            ->order_by('p.period_order', 'ASC');

        if ($date) $this->db->where('sub.substitution_date', $date);
        if ($status) $this->db->where('sub.status', $status);

        return $this->db->get()->result();
    }

    public function get_free_teachers($year_id, $day, $period_id)
    {
        $school_id = $this->get_school_id();
        // Get IDs of teachers who already have a class on this day & period in this school
        $busy_teachers = $this->db
            ->select('teacher_id')
            ->from('tbl_timetable')
            ->where('school_id', $school_id)
            ->where('academic_year_id', (int)$year_id)
            ->where('day', $day)
            ->where('period_id', (int)$period_id)
            ->where('status', 1)
            ->get()
            ->result_array();

        $busy_ids = array_column($busy_teachers, 'teacher_id');

        // Query all active teaching staff NOT in $busy_ids for this school
        $this->db
            ->select('s.*, d.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_designations d', 'd.designation_id = s.designation_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.staff_type', 'teacher')
            ->where('s.status', 1);

        if (!empty($busy_ids)) {
            $this->db->where_not_in('s.staff_id', $busy_ids);
        }

        $free_teachers = $this->db->order_by('s.full_name', 'ASC')->get()->result();

        // Calculate today's workload for each free teacher
        foreach ($free_teachers as &$t) {
            $day_classes = $this->db
                ->where('school_id', $school_id)
                ->where('academic_year_id', (int)$year_id)
                ->where('teacher_id', $t->staff_id)
                ->where('day', $day)
                ->where('status', 1)
                ->count_all_results('tbl_timetable');
            $t->classes_today = (int)$day_classes;
        }

        return $free_teachers;
    }

    public function save_substitution($data, $id = NULL)
    {
        $school_id = $this->get_school_id();
        if ($id) {
            $this->db
                ->where($this->primaryKey, (int)$id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
            return $id;
        } else {
            if (empty($data['school_id'])) {
                $data['school_id'] = $school_id;
            }
            $data['status'] = $data['status'] ?? 'Scheduled';
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
    }

    public function delete_substitution($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->delete($this->table);
    }
}
