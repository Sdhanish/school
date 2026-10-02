<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subject_teacher_model extends CI_Model {

    protected $table = 'tbl_subject_teachers';
    protected $primaryKey = 'subject_teacher_id';
    protected $last_error = '';

    public function get_last_error()
    {
        return $this->last_error;
    }

    protected function get_school_id($school_id = null)
    {
        if ($school_id !== null && (int)$school_id > 0) {
            return (int)$school_id;
        }
        if (function_exists('get_current_school_id')) {
            $sid = (int)get_current_school_id();
            if ($sid > 0) {
                return $sid;
            }
        }
        return 1;
    }

    public function get_all($filters = array(), $school_id = null)
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : $this->get_school_id($school_id);
        $this->db
            ->select('st.subject_teacher_id, st.school_id, st.academic_year_id, st.class_id, st.division_id, st.subject_id, st.staff_id, st.status, st.is_deleted, st.created_at, st.updated_at, y.year_name, c.class_name, div.division_name as division_name, div.division_name as section_name, sub.subject_name, sub.subject_code, s.full_name as teacher_name, s.employee_code')
            ->from('tbl_subject_teachers st')
            ->join('tbl_academic_years y', 'y.academic_year_id = st.academic_year_id AND y.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id AND div.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = st.subject_id AND sub.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = st.staff_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('st.school_id', $school_id)
            ->where('st.status', 1)
            ->where('st.is_deleted', 'n')
            ->where('c.is_deleted', 'n')
            ->order_by('st.class_id', 'ASC')
            ->order_by('st.division_id', 'ASC')
            ->order_by('sub.subject_name', 'ASC');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('st.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('st.class_id', $filters['class_id']);
        }
        $div_id = $filters['division_id'] ?? ($filters['section_id'] ?? null);
        if (!empty($div_id)) {
            $this->db->where('st.division_id', $div_id);
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('st.subject_id', $filters['subject_id']);
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('st.staff_id', $filters['staff_id']);
        }

        return $this->db->get()->result();
    }

    public function assign($academic_year_id, $class_id, $division_id, $subject_id, $staff_id, $school_id = null)
    {
        $this->last_error = '';
        $school_id = $this->get_school_id($school_id);

        // Enforce teacher only
        $staff = $this->db
            ->select('staff_id, status, staff_type, category, academic_group_id')
            ->where('staff_id', (int)$staff_id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get('tbl_staff')
            ->row();

        if (!$staff || $staff->status != 1 || (strtolower($staff->staff_type ?? '') !== 'teacher' && strtolower($staff->category ?? '') !== 'teaching' && strtolower($staff->category ?? '') !== 'teacher')) {
            $this->last_error = 'Only active teaching faculty can be assigned as Subject Teacher.';
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

        $existing = $this->db
            ->where('school_id', $school_id)
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('class_id', (int)$class_id)
            ->where('division_id', (int)$division_id)
            ->where('subject_id', (int)$subject_id)
            ->get($this->table)
            ->row();

        if ($existing) {
            $this->db->where('subject_teacher_id', $existing->subject_teacher_id)
                ->where('school_id', $school_id)
                ->update($this->table, array(
                    'staff_id'   => (int)$staff_id,
                    'status'     => 1,
                    'is_deleted' => 'n',
                    'updated_at' => date('Y-m-d H:i:s')
                ));
            return $existing->subject_teacher_id;
        } else {
            $this->db->insert($this->table, array(
                'school_id'        => $school_id,
                'academic_year_id' => (int)$academic_year_id,
                'class_id'         => (int)$class_id,
                'division_id'      => (int)$division_id,
                'subject_id'       => (int)$subject_id,
                'staff_id'         => (int)$staff_id,
                'status'           => 1,
                'is_deleted'       => 'n',
                'created_at'       => date('Y-m-d H:i:s')
            ));
            return $this->db->insert_id();
        }
    }

    public function get_teachers_by_subject($academic_year_id, $class_id, $division_id, $subject_id, $school_id = null)
    {
        $school_id = $this->get_school_id($school_id);
        $this->db
            ->select('s.staff_id, s.full_name, s.employee_code')
            ->from('tbl_subject_teachers st')
            ->join('tbl_staff s', 's.staff_id = st.staff_id AND s.school_id = ' . (int)$school_id, 'inner')
            ->where('st.school_id', $school_id)
            ->where('s.school_id', $school_id)
            ->where('st.academic_year_id', (int)$academic_year_id)
            ->where('st.class_id', (int)$class_id)
            ->where('st.division_id', (int)$division_id)
            ->where('st.subject_id', (int)$subject_id)
            ->where('st.status', 1)
            ->where('st.is_deleted', 'n')
            ->where('s.status', 1)
            ->where('s.is_deleted', 'n');

        return $this->db->get()->result();
    }

    public function is_assigned($academic_year_id, $class_id, $division_id, $subject_id, $staff_id, $school_id = null)
    {
        $school_id = $this->get_school_id($school_id);
        $this->db
            ->where('school_id', $school_id)
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('class_id', (int)$class_id)
            ->where('subject_id', (int)$subject_id)
            ->where('staff_id', (int)$staff_id)
            ->where('status', 1)
            ->where('is_deleted', 'n');

        if (!empty($division_id)) {
            $this->db->where('division_id', (int)$division_id);
        }

        return ($this->db->count_all_results($this->table) > 0);
    }

    /**
     * Get all active assignments for a specific subject (and optional academic year).
     * Used by the Edit Subject modal to pre-populate inline assignment rows.
     */
    public function get_by_subject($subject_id, $academic_year_id = null, $school_id = null)
    {
        $school_id = $this->get_school_id($school_id);
        $this->db
            ->select('st.subject_teacher_id, st.academic_year_id, st.class_id, st.division_id, st.staff_id,
                      c.class_name, div.division_name, s.full_name as teacher_name, s.employee_code')
            ->from('tbl_subject_teachers st')
            ->join('tbl_classes c', 'c.class_id = st.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id AND div.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = st.staff_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('st.school_id', $school_id)
            ->where('st.subject_id', (int)$subject_id)
            ->where('st.status', 1)
            ->where('st.is_deleted', 'n')
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_id', 'ASC');

        if (!empty($academic_year_id)) {
            $this->db->where('st.academic_year_id', (int)$academic_year_id);
        }

        return $this->db->get()->result();
    }

    /**
     * Atomically synchronize all teacher assignments for a subject within an academic year.
     * Inserts/updates submitted assignments and soft-deletes removed ones.
     *
     * @param int $subject_id
     * @param int $academic_year_id
     * @param array $assignments Array of ['class_id' => int, 'division_id' => int, 'staff_id' => int]
     * @param int|null $school_id
     * @return array List of assigned staff_ids
     */
    public function sync_subject_assignments($subject_id, $academic_year_id, array $assignments, $school_id = null)
    {
        $school_id = $this->get_school_id($school_id);
        $subject_id = (int)$subject_id;
        $academic_year_id = (int)$academic_year_id;

        if (!$subject_id || !$academic_year_id) {
            return array();
        }

        // Fetch currently active assignments for this subject, academic year, and school
        $current_rows = $this->db
            ->select('subject_teacher_id, class_id, division_id, staff_id')
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('subject_id', $subject_id)
            ->where('academic_year_id', $academic_year_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->get()
            ->result();

        $active_map = array();
        foreach ($current_rows as $row) {
            $key = (int)$row->class_id . '_' . (int)$row->division_id;
            $active_map[$key] = $row;
        }

        $kept_keys = array();
        $assigned_staff_ids = array();

        foreach ($assignments as $a) {
            $class_id = (int)($a['class_id'] ?? 0);
            $division_id = (int)($a['division_id'] ?? 0);
            $staff_id = (int)($a['staff_id'] ?? 0);

            if (!$class_id || !$division_id || !$staff_id) {
                continue;
            }

            // Verify teacher belongs to this school, is active, is a teacher, and matches class academic group
            $staff = $this->db
                ->select('staff_id, status, is_deleted, school_id, staff_type, category, academic_group_id')
                ->where('staff_id', $staff_id)
                ->where('school_id', $school_id)
                ->where('status', 1)
                ->where('is_deleted', 'n')
                ->get('tbl_staff')
                ->row();

            if (!$staff) {
                continue;
            }

            // Verify teacher and class belong to the same group
            $cls_group = $this->db
                ->select('academic_group_id')
                ->where('class_id', $class_id)
                ->where('school_id', $school_id)
                ->where('is_deleted', 'n')
                ->get('tbl_classes')
                ->row();

            if (!$cls_group || empty($staff->academic_group_id) || (int)$staff->academic_group_id !== (int)$cls_group->academic_group_id) {
                continue;
            }

            // Verify class belongs to this school
            $cls = $this->db
                ->select('class_id')
                ->where('class_id', $class_id)
                ->where('school_id', $school_id)
                ->where('is_deleted', 'n')
                ->get('tbl_classes')
                ->row();
            if (!$cls) {
                continue;
            }

            // Verify division belongs to class & this school
            $div = $this->db
                ->select('division_id')
                ->where('division_id', $division_id)
                ->where('class_id', $class_id)
                ->where('school_id', $school_id)
                ->where('is_deleted', 'n')
                ->get('tbl_divisions')
                ->row();
            if (!$div) {
                continue;
            }

            $key = $class_id . '_' . $division_id;
            $kept_keys[$key] = true;
            $assigned_staff_ids[] = $staff_id;

            // Check if record exists in table (either active or inactive)
            $existing = $this->db
                ->select('subject_teacher_id, staff_id, status, is_deleted')
                ->where('school_id', $school_id)
                ->where('academic_year_id', $academic_year_id)
                ->where('class_id', $class_id)
                ->where('division_id', $division_id)
                ->where('subject_id', $subject_id)
                ->get($this->table)
                ->row();

            if ($existing) {
                $this->db
                    ->where('subject_teacher_id', (int)$existing->subject_teacher_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, array(
                        'staff_id'   => $staff_id,
                        'status'     => 1,
                        'is_deleted' => 'n',
                        'updated_at' => date('Y-m-d H:i:s')
                    ));
            } else {
                $this->db->insert($this->table, array(
                    'school_id'        => $school_id,
                    'academic_year_id' => $academic_year_id,
                    'class_id'         => $class_id,
                    'division_id'      => $division_id,
                    'subject_id'       => $subject_id,
                    'staff_id'         => $staff_id,
                    'status'           => 1,
                    'is_deleted'       => 'n',
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s')
                ));
            }
        }

        // Soft-delete any existing active records that are not in the submitted list
        foreach ($active_map as $key => $row) {
            if (!isset($kept_keys[$key])) {
                $this->db
                    ->where('subject_teacher_id', (int)$row->subject_teacher_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, array(
                        'status'     => 0,
                        'is_deleted' => 'y',
                        'updated_at' => date('Y-m-d H:i:s')
                    ));
            }
        }

        return array_values(array_unique($assigned_staff_ids));
    }

    public function delete($id, $school_id = null)
    {
        $school_id = $this->get_school_id($school_id);
        return $this->db
            ->where('school_id', $school_id)
            ->where('subject_teacher_id', (int)$id)
            ->update($this->table, array(
                'status'     => 0,
                'is_deleted' => 'y',
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

}
