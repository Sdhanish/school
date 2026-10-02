<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subject_model extends CI_Model {

    protected $table = 'tbl_subjects';
    protected $primaryKey = 'subject_id';

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

    /**
     * Get all subjects, optionally filtered by class_id.
     * @param int|null $class_id  If provided, only return subjects for that class.
     *                            Pass NULL (or omit) for all subjects.
     */
    public function get_all($class_id = NULL, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        $this->db
            ->select('sub.subject_id, sub.school_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id, sub.teacher_id, sub.description, sub.status, sub.is_deleted, sub.created_at, sub.updated_at, c.class_name, s.full_name as teacher_name')
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sub.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n')
            ->order_by('sub.class_id', 'ASC')
            ->order_by('sub.subject_name', 'ASC');

        if ($class_id && $class_id !== TRUE) {
            $this->db->where('sub.class_id', (int)$class_id);
        }

        return $this->db->get()->result();
    }

    public function get_dropdown($class_id = NULL, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        $this->db
            ->select('subject_id, school_id, subject_name, subject_code, class_id')
            ->where('school_id', (int)$school_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('subject_name', 'ASC');

        if ($class_id) {
            $this->db->where('class_id', (int)$class_id);
        }

        return $this->db->get($this->table)->result();
    }

    /**
     * Get all active subjects (no class filter). 
     * Use this when you need every subject in the system.
     */
    public function get_all_active($school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        return $this->db
            ->select('sub.subject_id, sub.school_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id, sub.teacher_id, sub.description, sub.status, sub.is_deleted, sub.created_at, sub.updated_at, c.class_name, s.full_name as teacher_name')
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sub.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n')
            ->order_by('c.class_name', 'ASC')
            ->order_by('sub.subject_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Server-side DataTables query for subjects list
     */
    public function get_datatables_data($filters = array(), $limit = 25, $start = 0, $order_col = 0, $order_dir = 'ASC')
    {
        $allowed_cols = array(
            0 => 'sub.subject_name',
            1 => 'sub.subject_code',
            2 => 'c.class_name',
            3 => 'sub.subject_type',
            4 => 'sub.subject_id',
            5 => 'sub.subject_id',
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'sub.subject_name';
        $dir = (strtoupper($order_dir) === 'DESC') ? 'DESC' : 'ASC';

        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : $this->get_school_id();

        $this->db
            ->select('sub.subject_id, sub.school_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id, sub.teacher_id, sub.description, sub.status, sub.is_deleted, sub.created_at, sub.updated_at, c.class_name, s.full_name as teacher_name')
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sub.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n');

        if (!empty($filters['class_id'])) {
            $this->db->where('sub.class_id', (int)$filters['class_id']);
        }

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('sub.subject_name', $q)
                ->or_like('sub.subject_code', $q)
                ->or_like('sub.subject_type', $q)
                ->or_like('c.class_name', $q)
                ->or_like('sub.description', $q)
                ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function count_filtered($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : $this->get_school_id();

        $this->db
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n');

        if (!empty($filters['class_id'])) {
            $this->db->where('sub.class_id', (int)$filters['class_id']);
        }

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('sub.subject_name', $q)
                ->or_like('sub.subject_code', $q)
                ->or_like('sub.subject_type', $q)
                ->or_like('c.class_name', $q)
                ->or_like('sub.description', $q)
                ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function count_all($school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        return $this->db
            ->where('school_id', (int)$school_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);
    }

    /**
     * Get subjects assigned to a class/section/year via tbl_subject_allocations.
     * This is the CORRECT method for the timetable builder — it only returns
     * subjects that have been explicitly allocated to the selected class.
     *
     * Falls back to tbl_subjects.class_id if no allocations exist yet.
     *
     * @param int $year_id
     * @param int $class_id
     * @param int|null $section_id  Optional — if provided, also filter by section.
     */
    public function get_for_class($year_id, $class_id, $section_id = NULL, $school_id = NULL)
    {
        if (empty($class_id)) {
            return array();
        }

        $school_id = $this->get_school_id($school_id);

        // Primary: subjects from subject_allocations table
        $this->db
            ->select('sub.subject_id, sub.subject_name, sub.subject_code, sub.subject_type,
                      sub.class_id, sub.teacher_id, sub.status,
                      c.class_name, s.full_name as teacher_name,
                      sa.allocation_id, sa.weekly_periods_target')
            ->from('tbl_subject_allocations sa')
            ->join('tbl_subjects sub', 'sub.subject_id = sa.subject_id AND sub.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_classes c', 'c.class_id = sa.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sa.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sa.school_id', (int)$school_id);

        if (!empty($year_id)) {
            $this->db->where('sa.academic_year_id', (int)$year_id);
        }

        $this->db
            ->where('sa.class_id', (int)$class_id)
            ->where('sa.status', 1)
            ->where('sa.is_deleted', 'n')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n')
            ->order_by('sub.subject_name', 'ASC');

        if ($section_id) {
            $this->db->where('sa.division_id', (int)$section_id);
        }

        $via_allocations = $this->db->get()->result();

        if (!empty($via_allocations)) {
            // Avoid duplicate subjects if allocated across multiple divisions/sections
            $unique_subjects = [];
            foreach ($via_allocations as $sub) {
                if (!isset($unique_subjects[$sub->subject_id])) {
                    $unique_subjects[$sub->subject_id] = $sub;
                }
            }
            return array_values($unique_subjects);
        }

        // Fallback: subjects linked directly to class in tbl_subjects
        $fallback = $this->db
            ->select('sub.subject_id, sub.school_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id, sub.teacher_id, sub.description, sub.status, sub.is_deleted, sub.created_at, sub.updated_at, c.class_name, s.full_name as teacher_name')
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sub.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.class_id', (int)$class_id)
            ->where('sub.status', 1)
            ->where('sub.is_deleted', 'n')
            ->order_by('sub.subject_name', 'ASC')
            ->get()
            ->result();

        $unique_fallback = [];
        foreach ($fallback as $sub) {
            if (!isset($unique_fallback[$sub->subject_id])) {
                $unique_fallback[$sub->subject_id] = $sub;
            }
        }
        return array_values($unique_fallback);
    }

    public function get_by_id($id, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        return $this->db
            ->select('sub.subject_id, sub.school_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id, sub.teacher_id, sub.description, sub.status, sub.is_deleted, sub.created_at, sub.updated_at, c.class_name, s.full_name as teacher_name')
            ->from('tbl_subjects sub')
            ->join('tbl_classes c', 'c.class_id = sub.class_id AND c.school_id = ' . (int)$school_id, 'left')
            ->join('tbl_staff s', 's.staff_id = sub.teacher_id AND s.school_id = ' . (int)$school_id, 'left')
            ->where('sub.school_id', (int)$school_id)
            ->where('sub.subject_id', (int)$id)
            ->where('sub.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function insert($data, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id ?: ($data['school_id'] ?? null));
        $data['school_id'] = (int)$school_id;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id ?: ($data['school_id'] ?? null));
        unset($data['school_id']); // Guard against altering the school_id of an existing subject
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', (int)$school_id)
            ->update($this->table, $data);
    }

    public function soft_delete($id, $school_id = NULL)
    {
        $school_id = $this->get_school_id($school_id);
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', (int)$school_id)
            ->update($this->table, array('status' => 0, 'is_deleted' => 'y', 'updated_at' => date('Y-m-d H:i:s')));
    }
}
