<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_teacher_model extends CI_Model {

    protected $table = 'tbl_class_teachers';
    protected $primaryKey = 'class_teacher_id';
    protected $last_error = '';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_last_error()
    {
        return $this->last_error;
    }

    public function get_all($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('ct.class_teacher_id, ct.academic_year_id, ct.class_id, ct.division_id, ct.staff_id, ct.status, ct.school_id, ct.created_at, ct.updated_at, y.year_name, c.class_name, div.division_name as division_name, div.division_name as section_name, s.full_name as teacher_name, s.employee_code, s.phone')
            ->from('tbl_class_teachers ct')
            ->join('tbl_academic_years y', 'y.academic_year_id = ct.academic_year_id', 'left')
            ->join('tbl_classes c', 'c.class_id = ct.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ct.division_id', 'left')
            ->join('tbl_staff s', 's.staff_id = ct.staff_id', 'left')
            ->where('ct.school_id', $school_id)
            ->where('ct.status', 1)
            ->where('ct.is_deleted', 'n')
            ->order_by('ct.class_id', 'ASC')
            ->order_by('ct.division_id', 'ASC');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('ct.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('ct.class_id', $filters['class_id']);
        }
        $div_id = $filters['division_id'] ?? ($filters['section_id'] ?? null);
        if (!empty($div_id)) {
            $this->db->where('ct.division_id', $div_id);
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('ct.staff_id', $filters['staff_id']);
        }

        return $this->db->get()->result();
    }

    /**
     * Check if a teacher is already assigned as Class Teacher in a specific academic year.
     * Optionally excludes a specific division ID (e.g. during edit of that division).
     *
     * @param int $academic_year_id
     * @param int $staff_id
     * @param int|null $exclude_division_id
     * @return object|null Conflicting assignment record or null if available
     */
    public function check_teacher_availability($academic_year_id, $staff_id, $exclude_division_id = null)
    {
        if (empty($academic_year_id) || empty($staff_id)) {
            return null;
        }

        $school_id = $this->get_school_id();
        $this->db
            ->select('ct.class_teacher_id, ct.academic_year_id, ct.class_id, ct.division_id, ct.staff_id, c.class_name, div.division_name, y.year_name, s.full_name as teacher_name, s.employee_code')
            ->from('tbl_class_teachers ct')
            ->join('tbl_classes c', 'c.class_id = ct.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ct.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = ct.academic_year_id', 'left')
            ->join('tbl_staff s', 's.staff_id = ct.staff_id', 'left')
            ->where('ct.school_id', $school_id)
            ->where('ct.academic_year_id', (int)$academic_year_id)
            ->where('ct.staff_id', (int)$staff_id)
            ->where('ct.status', 1)
            ->where('ct.is_deleted', 'n');

        if (!empty($exclude_division_id)) {
            $this->db->where('ct.division_id !=', (int)$exclude_division_id);
        }

        return $this->db->get()->row();
    }

    /**
     * Get all active class teacher assignments for a specific academic year.
     *
     * @param int $academic_year_id
     * @return array
     */
    public function get_active_assignments_by_year($academic_year_id)
    {
        if (empty($academic_year_id)) {
            return array();
        }

        $school_id = $this->get_school_id();
        return $this->db
            ->select('ct.class_teacher_id, ct.academic_year_id, ct.class_id, ct.division_id, ct.staff_id, c.class_name, div.division_name, s.full_name as teacher_name, s.employee_code')
            ->from('tbl_class_teachers ct')
            ->join('tbl_classes c', 'c.class_id = ct.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ct.division_id', 'left')
            ->join('tbl_staff s', 's.staff_id = ct.staff_id', 'left')
            ->where('ct.school_id', $school_id)
            ->where('ct.academic_year_id', (int)$academic_year_id)
            ->where('ct.status', 1)
            ->where('ct.is_deleted', 'n')
            ->order_by('c.class_name', 'ASC')
            ->order_by('div.division_name', 'ASC')
            ->get()
            ->result();
    }

    public function assign($academic_year_id, $class_id, $division_id, $staff_id)
    {
        $this->last_error = '';
        $school_id = $this->get_school_id();

        // Enforce teacher faculty only
        $staff = $this->db
            ->select('staff_id, status, staff_type, category, academic_group_id')
            ->where('staff_id', (int)$staff_id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get('tbl_staff')
            ->row();

        if (!$staff || $staff->status != 1 || (!in_array(strtolower($staff->staff_type ?? ''), ['teacher']) && !in_array(strtolower($staff->category ?? ''), ['teaching', 'teacher']))) {
            $this->last_error = 'Only active teaching faculty can be assigned as Class Teacher.';
            return FALSE;
        }

        // Validate Academic Group rule: Teacher and class must belong to the same group
        $class_row = $this->db
            ->select('academic_group_id')
            ->where('class_id', (int)$class_id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get('tbl_classes')
            ->row();

        if (!$class_row || empty($staff->academic_group_id) || (int)$staff->academic_group_id !== (int)$class_row->academic_group_id) {
            $this->last_error = 'Teacher and class must belong to the same Department / Group.';
            return FALSE;
        }

        // Business Rule: One teacher = One Class Teacher assignment per Academic Year
        $conflict = $this->check_teacher_availability($academic_year_id, $staff_id, $division_id);
        if ($conflict) {
            $tName = $conflict->teacher_name ?: 'This teacher';
            $cName = $conflict->class_name ?: 'Class';
            $dName = $conflict->division_name ?: 'Division';
            $yName = $conflict->year_name ?: 'the selected academic year';
            $this->last_error = "{$tName} is already assigned as Class Teacher to {$cName} - Division {$dName} for the academic year {$yName}.";
            return FALSE;
        }

        // Check existing for this class+division+year
        $existing = $this->db
            ->where('school_id', $school_id)
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('class_id', (int)$class_id)
            ->where('division_id', (int)$division_id)
            ->get($this->table)
            ->row();

        if ($existing) {
            $this->db->where('class_teacher_id', $existing->class_teacher_id)
                ->where('school_id', $school_id)
                ->update($this->table, array(
                    'staff_id'   => (int)$staff_id,
                    'status'     => 1,
                    'is_deleted' => 'n',
                    'updated_at' => date('Y-m-d H:i:s')
                ));
            // Also sync tbl_divisions.class_teacher_id
            $this->db->where('division_id', (int)$division_id)
                ->where('school_id', $school_id)
                ->update('tbl_divisions', array('class_teacher_id' => (int)$staff_id));
            return $existing->class_teacher_id;
        } else {
            $this->db->insert($this->table, array(
                'school_id'        => $school_id,
                'academic_year_id' => (int)$academic_year_id,
                'class_id'         => (int)$class_id,
                'division_id'       => (int)$division_id,
                'staff_id'         => (int)$staff_id,
                'status'           => 1,
                'is_deleted'       => 'n',
                'created_at'       => date('Y-m-d H:i:s')
            ));
            $id = $this->db->insert_id();
            $this->db->where('division_id', (int)$division_id)
                ->where('school_id', $school_id)
                ->update('tbl_divisions', array('class_teacher_id' => (int)$staff_id));
            return $id;
        }
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        $ct = $this->db->where('class_teacher_id', (int)$id)->where('school_id', $school_id)->get($this->table)->row();
        if ($ct) {
            $div_id = $ct->division_id ?? ($ct->section_id ?? null);
            if ($div_id) {
                $this->db->where('division_id', (int)$div_id)->where('school_id', $school_id)->update('tbl_divisions', array('class_teacher_id' => NULL));
            }
            return $this->db->where('class_teacher_id', (int)$id)->where('school_id', $school_id)->update($this->table, ['is_deleted' => 'y', 'status' => 0]);
        }
        return FALSE;
    }
}
