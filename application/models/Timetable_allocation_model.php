<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timetable_allocation_model extends CI_Model {

    protected $table = 'tbl_subject_allocations';
    protected $primaryKey = 'allocation_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_allocations($year_id, $class_id = NULL, $division_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('a.*, sub.subject_name, sub.subject_code, c.class_name, d.division_name as division_name, d.division_name as section_name, s.full_name as teacher_name')
            ->from('tbl_subject_allocations a')
            ->join('tbl_subjects sub', 'sub.subject_id = a.subject_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = a.division_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.teacher_id', 'left')
            ->where('a.school_id', $school_id)
            ->where('a.academic_year_id', (int)$year_id)
            ->where('a.is_deleted', 'n')
            ->where('a.status', 1);

        if ($class_id) $this->db->where('a.class_id', (int)$class_id);
        if ($division_id) $this->db->where('a.division_id', (int)$division_id);

        $allocations = $this->db->get()->result();

        // Calculate actual allocated periods in tbl_timetable
        foreach ($allocations as &$alloc) {
            $allocated_cnt = $this->db
                ->where('school_id', $school_id)
                ->where('academic_year_id', (int)$year_id)
                ->where('class_id', $alloc->class_id)
                ->where('division_id', $alloc->division_id)
                ->where('subject_id', $alloc->subject_id)
                ->where('is_deleted', 'n')
                ->where('status', 1)
                ->count_all_results('tbl_timetable');

            $alloc->actual_allocated = (int)$allocated_cnt;
            $alloc->remaining = max(0, (int)$alloc->weekly_periods_target - $alloc->actual_allocated);
        }

        return $allocations;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();
    }

    public function save_allocation($data, $id = NULL)
    {
        $school_id = $this->get_school_id();
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db
                ->where($this->primaryKey, (int)$id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
            return $id;
        } else {
            if (empty($data['school_id'])) {
                $data['school_id'] = $school_id;
            }
            $data['status'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
    }

    public function delete_allocation($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['is_deleted' => 'y']);
    }
}
