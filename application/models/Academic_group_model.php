<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academic_group_model extends CI_Model {

    protected $table = 'tbl_academic_groups';
    protected $classesTable = 'tbl_academic_group_classes';
    protected $primaryKey = 'academic_group_id';

    public function __construct()
    {
        parent::__construct();
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    protected function get_academic_year_id()
    {
        return function_exists('get_current_academic_year_id') ? (int)get_current_academic_year_id() : 0;
    }

    /**
     * Get active academic groups for dropdowns (dynamic from database, not academic-year dependent).
     *
     * @param int|null $school_id
     * @return array
     */
    public function get_groups_for_dropdown($school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        return $this->db
            ->select('academic_group_id, group_name, display_order')
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('display_order', 'ASC')
            ->order_by('academic_group_id', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get academic groups with dynamic staff count for Department / Groups UI.
     * Uses explicit column selection and no SELECT *.
     *
     * @param int|null $school_id
     * @param bool $include_inactive
     * @return array
     */
    public function get_groups_with_staff_counts($school_id = null, $include_inactive = true)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();

        $this->db
            ->select("ag.academic_group_id, ag.group_name, ag.description, ag.display_order, ag.status, ag.attendance_type, ag.school_id, ag.academic_year_id, ag.created_at, ag.updated_at,
                      (SELECT COUNT(staff_id) 
                       FROM tbl_staff 
                       WHERE academic_group_id = ag.academic_group_id 
                         AND school_id = {$school_id} 
                         AND status = 1 
                         AND is_deleted = 'n') as staff_count")
            ->from($this->table . ' ag')
            ->where('ag.school_id', $school_id)
            ->where('ag.is_deleted', 'n');

        if (!$include_inactive) {
            $this->db->where('ag.status', 1);
        }

        $this->db->order_by('ag.display_order', 'ASC')
                 ->order_by('ag.academic_group_id', 'ASC');

        $groups = $this->db->get()->result();

        // Attach configured classes
        foreach ($groups as &$grp) {
            $allowed = $this->get_allowed_classes($grp->academic_group_id, $school_id);
            $names = [];
            foreach ($allowed as $al) {
                $names[] = $al->class_name;
            }
            $grp->configured_classes_array = $names;
            $grp->configured_classes_text = implode(', ', $names);
        }

        return $groups;
    }

    /**
     * Get all academic groups ordered by display_order.
     *
     * @param bool $include_inactive
     * @param int|null $year_id
     * @return array
     */
    public function get_all($include_inactive = false, $year_id = null)
    {
        $school_id = $this->get_school_id();
        if ($year_id === null) {
            $year_id = $this->get_academic_year_id();
        }

        $year_cond = "";
        if ($year_id > 0) {
            $year_cond = " AND (academic_year_id = {$year_id} OR academic_year_id IS NULL)";
        }

        $this->db
            ->select("ag.academic_group_id, ag.group_name, ag.description, ag.display_order, ag.status, ag.attendance_type, ag.school_id, ag.academic_year_id, ag.created_at, ag.updated_at, (SELECT COUNT(class_id) FROM tbl_classes WHERE academic_group_id = ag.academic_group_id AND school_id = {$school_id}{$year_cond} AND status = 1 AND is_deleted = 'n') as class_count")
            ->from($this->table . ' ag')
            ->where('ag.school_id', $school_id)
            ->where('ag.is_deleted', 'n');

        if ($year_id > 0) {
            $this->db->group_start()
                ->where('ag.academic_year_id', $year_id)
                ->or_where('ag.academic_year_id IS NULL', null, false)
                ->group_end();
        }

        $this->db->order_by('ag.display_order', 'ASC')
            ->order_by('ag.academic_group_id', 'ASC');

        if (!$include_inactive) {
            $this->db->where('ag.status', 1);
        }

        $groups = $this->db->get()->result();

        // Attach configured classes
        foreach ($groups as &$grp) {
            $allowed = $this->get_allowed_classes($grp->academic_group_id);
            $names = [];
            foreach ($allowed as $al) {
                $names[] = $al->class_name;
            }
            $grp->configured_classes_array = $names;
            $grp->configured_classes_text = implode(', ', $names);
        }

        return $groups;
    }

    /**
     * Get single academic group by ID.
     *
     * @param int $id
     * @param int|null $school_id
     * @return object|null
     */
    public function get_by_id($id, $school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        $group = $this->db
            ->select('academic_group_id, group_name, description, display_order, status, attendance_type, school_id, academic_year_id, created_at, updated_at')
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        if ($group) {
            $allowed = $this->get_allowed_classes($group->academic_group_id);
            $names = [];
            foreach ($allowed as $al) {
                $names[] = $al->class_name;
            }
            $group->configured_classes_array = $names;
            $group->configured_classes_text = implode(', ', $names);
        }

        return $group;
    }

    /**
     * Get attendance type for an academic group ('daily' or 'period').
     *
     * @param int $group_id
     * @param int|null $school_id
     * @return string
     */
    public function get_attendance_type($group_id, $school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        $row = $this->db
            ->select('attendance_type')
            ->where($this->primaryKey, (int)$group_id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        return ($row && !empty($row->attendance_type)) ? strtolower($row->attendance_type) : 'daily';
    }

    /**
     * Get attendance type for a class by looking up its associated academic group.
     *
     * @param int $class_id
     * @param int|null $school_id
     * @return string
     */
    public function get_attendance_type_by_class_id($class_id, $school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        $row = $this->db
            ->select('ag.attendance_type')
            ->from('tbl_classes c')
            ->join($this->table . ' ag', 'ag.academic_group_id = c.academic_group_id', 'inner')
            ->where('c.class_id', (int)$class_id)
            ->where('c.school_id', $school_id)
            ->where('c.is_deleted', 'n')
            ->where('ag.school_id', $school_id)
            ->where('ag.is_deleted', 'n')
            ->get()
            ->row();

        return ($row && !empty($row->attendance_type)) ? strtolower($row->attendance_type) : 'daily';
    }

    /**
     * Check if group name already exists (case-insensitive) in current school and academic year.
     *
     * @param string $group_name
     * @param int|null $exclude_id
     * @param int|null $year_id
     * @return bool
     */
    public function check_duplicate($group_name, $exclude_id = null, $year_id = null)
    {
        $school_id = $this->get_school_id();
        if ($year_id === null) {
            $year_id = $this->get_academic_year_id();
        }

        $this->db
            ->where('school_id', $school_id)
            ->where('LOWER(group_name)', strtolower(trim($group_name)))
            ->where('is_deleted', 'n');

        if ($year_id > 0) {
            $this->db->group_start()
                ->where('academic_year_id', $year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }

        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }

        return ($this->db->count_all_results($this->table) > 0);
    }

    /**
     * Insert new academic group.
     *
     * @param array $data
     * @param array|string|null $class_names
     * @return int
     */
    public function insert($data, $class_names = null)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        if (empty($data['academic_year_id'])) {
            $year_id = $this->get_academic_year_id();
            if ($year_id > 0) {
                $data['academic_year_id'] = $year_id;
            }
        }
        if (empty($data['attendance_type']) || !in_array(strtolower($data['attendance_type']), array('daily', 'period'))) {
            $data['attendance_type'] = 'daily';
        } else {
            $data['attendance_type'] = strtolower($data['attendance_type']);
        }
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }
        if (!isset($data['is_deleted'])) {
            $data['is_deleted'] = 'n';
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $this->db->insert($this->table, $data);
        $group_id = $this->db->insert_id();

        if (!empty($class_names)) {
            $this->save_configured_classes($group_id, $class_names, $data['school_id']);
        }

        return $group_id;
    }

    /**
     * Update academic group.
     *
     * @param int $id
     * @param array $data
     * @param array|string|null $class_names
     * @return bool
     */
    public function update($id, $data, $class_names = null)
    {
        $school_id = $this->get_school_id();
        if (isset($data['attendance_type'])) {
            $data['attendance_type'] = in_array(strtolower($data['attendance_type']), array('daily', 'period')) ? strtolower($data['attendance_type']) : 'daily';
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $res = $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);

        if ($class_names !== null) {
            $this->save_configured_classes($id, $class_names, $school_id);
        }

        return $res;
    }

    /**
     * Save configured classes for an academic group in tbl_academic_group_classes.
     *
     * @param int $group_id
     * @param array|string $class_names
     * @param int|null $school_id
     */
    public function save_configured_classes($group_id, $class_names, $school_id = null)
    {
        if ($school_id === null) {
            $school_id = $this->get_school_id();
        }
        if (is_string($class_names)) {
            $class_names = array_filter(array_map('trim', explode(',', $class_names)));
        }
        if (!is_array($class_names)) {
            return;
        }

        $this->db->where('academic_group_id', (int)$group_id)
            ->where('school_id', (int)$school_id)
            ->delete($this->classesTable);

        $order = 1;
        foreach ($class_names as $cname) {
            $cname = trim($cname);
            if ($cname !== '') {
                $this->db->insert($this->classesTable, [
                    'academic_group_id' => (int)$group_id,
                    'class_name'        => $cname,
                    'display_order'     => $order++,
                    'school_id'         => (int)$school_id,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Soft delete an academic group.
     *
     * @param int $id
     * @return bool
     */
    public function soft_delete($id)
    {
        return $this->update($id, array('is_deleted' => 'y', 'status' => 0));
    }

    /**
     * Enable/Disable status.
     *
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function set_status($id, $status)
    {
        return $this->update($id, array('status' => (int)$status));
    }

    /**
     * Fetch the database-configured allowed class names for an Academic Group.
     * Comes from tbl_academic_group_classes so it is never hardcoded.
     *
     * @param int $academic_group_id
     * @return array
     */
    public function get_allowed_classes($academic_group_id, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->get_school_id();
        return $this->db
            ->where('academic_group_id', (int)$academic_group_id)
            ->where('school_id', $sid)
            ->order_by('display_order', 'ASC')
            ->get($this->classesTable)
            ->result();
    }

    /**
     * Get complete hierarchy of Academic Group -> Class -> Division.
     * Optimized to load all records in 3 bulk queries instead of nested N+1 loops.
     *
     * @param int|null $academic_year_id
     * @return array
     */
    public function get_hierarchy($academic_year_id = NULL)
    {
        $this->load->model('Division_model');
        $groups = $this->get_all(false);
        if (empty($groups)) {
            return [];
        }

        $school_id = $this->get_school_id();

        // 1. Fetch all active classes for this school in a single query
        $this->db
            ->select('c.*, y.year_name')
            ->from('tbl_classes c')
            ->join('tbl_academic_years y', 'y.academic_year_id = c.academic_year_id', 'left')
            ->where('c.school_id', $school_id)
            ->where('c.status', 1)
            ->where('c.is_deleted', 'n')
            ->order_by('c.class_id', 'ASC');

        if ($academic_year_id) {
            $this->db->where('c.academic_year_id', (int)$academic_year_id);
        }

        $all_classes = $this->db->get()->result();

        // 2. Fetch all active divisions for this school in a single query
        $all_divisions = $this->Division_model->get_all(NULL, $school_id);
        $divisions_by_class = [];
        foreach ($all_divisions as $div) {
            $divisions_by_class[$div->class_id][] = $div;
        }

        // 3. Map divisions to classes, and classes to groups
        $classes_by_group = [];
        foreach ($all_classes as $cls) {
            $cls->divisions = $divisions_by_class[$cls->class_id] ?? [];
            $classes_by_group[$cls->academic_group_id][] = $cls;
        }

        $hierarchy = [];
        foreach ($groups as $grp) {
            $grp->classes = $classes_by_group[$grp->academic_group_id] ?? [];
            $hierarchy[] = $grp;
        }

        return $hierarchy;
    }
}
