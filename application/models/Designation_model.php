<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Designation Model
 *
 * Single Source of Truth for School-Level Designations and Designation-Based RBAC.
 * Manages designations, user counts, and designation permission sets per school.
 */
class Designation_model extends CI_Model {

    protected $table = 'tbl_designations';
    protected $permTable = 'tbl_designation_permissions';
    protected $primaryKey = 'designation_id';

    public function __construct()
    {
        parent::__construct();
    }

    private function _get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
    }

    private function _is_super_admin()
    {
        $CI =& get_instance();
        return isset($CI->rbac) && $CI->rbac->is_super_admin();
    }

    /**
     * Get all designations for a school.
     *
     * @param bool|array $include_inactive
     * @param int|null $school_id
     * @return array
     */
    public function get_all($include_inactive = false, $school_id = null)
    {
        if (is_array($include_inactive)) {
            $filters = $include_inactive;
            if (isset($filters['school_id'])) {
                $school_id = (int)$filters['school_id'];
            }
            $include_inactive = !empty($filters['include_inactive']) || (isset($filters['status']) && $filters['status'] === 'all');
        }

        if ($school_id === null || $school_id <= 0) {
            $school_id = $this->_get_school_id();
        }

        $this->db
            ->select("dg.*, 
                      (SELECT COUNT(user_id) FROM tbl_users WHERE designation_id = dg.designation_id AND school_id = dg.school_id AND status = 'Active' AND is_deleted = 'n') as user_count,
                      (SELECT COUNT(staff_id) FROM tbl_staff WHERE designation_id = dg.designation_id AND school_id = dg.school_id AND status = 1 AND is_deleted = 'n') as staff_count")
            ->from($this->table . ' dg')
            ->where('dg.is_deleted', 'n');

        if ($school_id > 0) {
            $this->db->where('dg.school_id', (int)$school_id);
        }

        if (!$include_inactive) {
            $this->db->where('dg.status', 1);
        }

        return $this->db
            ->order_by('dg.designation_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get designations with user and permission counts for Designation Management UI.
     *
     * @param int|null $school_id
     * @return array
     */
    public function get_designations_with_counts($school_id = null)
    {
        if ($school_id === null || $school_id <= 0) {
            $school_id = $this->_get_school_id();
        }

        $this->db
            ->select("dg.*, 
                      COUNT(DISTINCT u.user_id) as total_users,
                      SUM(CASE WHEN u.status = 'Active' AND u.is_deleted = 'n' THEN 1 ELSE 0 END) as active_users,
                      (SELECT COUNT(*) FROM {$this->permTable} dp WHERE dp.designation_id = dg.designation_id AND dp.school_id = dg.school_id AND dp.is_deleted = 'n') as permission_count")
            ->from($this->table . ' dg')
            ->join('tbl_users u', "u.designation_id = dg.designation_id AND u.is_deleted = 'n' AND u.school_id = dg.school_id", 'left')
            ->where('dg.is_deleted', 'n');

        if ($school_id > 0) {
            $this->db->where('dg.school_id', (int)$school_id);
        }

        return $this->db
            ->group_by('dg.designation_id')
            ->order_by('dg.designation_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get single designation by ID (scoped by school).
     */
    public function get_by_id($id, $school_id = null)
    {
        if ($school_id === null || $school_id <= 0) {
            $school_id = $this->_get_school_id();
        }

        $this->db->where($this->primaryKey, (int)$id)
            ->where('is_deleted', 'n');

        if ($school_id > 0) {
            $this->db->where('school_id', (int)$school_id);
        }

        return $this->db->get($this->table)->row();
    }

    /**
     * Get single designation by code and school.
     */
    public function get_by_code($code, $school_id = null)
    {
        if ($school_id === null || $school_id <= 0) {
            $school_id = $this->_get_school_id();
        }

        $this->db->where('designation_code', strtoupper(trim($code)))
            ->where('is_deleted', 'n');

        if ($school_id > 0) {
            $this->db->where('school_id', (int)$school_id);
        }

        return $this->db->get($this->table)->row();
    }

    /**
     * Get all permission IDs assigned to a designation.
     */
    public function get_designation_permission_ids($designation_id, $school_id = null)
    {
        $desig = $this->get_by_id($designation_id, $school_id);
        if ($desig && ($desig->designation_code === 'SUPER_ADMIN' || $desig->designation_name === 'Super Admin')) {
            $all = $this->db->select('permission_id')->where('is_deleted', 'n')->get('tbl_permissions')->result();
            return array_map(function($r) { return (int)$r->permission_id; }, $all);
        }

        $sid = $school_id ? (int)$school_id : ($desig ? (int)$desig->school_id : $this->_get_school_id());

        $this->db
            ->select('permission_id')
            ->where('designation_id', (int)$designation_id)
            ->where('is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        $rows = $this->db->get($this->permTable)->result();
        return array_map(function($r) { return (int)$r->permission_id; }, $rows);
    }

    /**
     * Get all permission keys assigned to a designation.
     */
    public function get_designation_permission_keys($designation_id, $school_id = null)
    {
        $desig = $this->get_by_id($designation_id, $school_id);
        if ($desig && ($desig->designation_code === 'SUPER_ADMIN' || $desig->designation_name === 'Super Admin')) {
            $all = $this->db->select('permission_key')->where('is_deleted', 'n')->get('tbl_permissions')->result();
            return array_map(function($r) { return $r->permission_key; }, $all);
        }

        $sid = $school_id ? (int)$school_id : ($desig ? (int)$desig->school_id : $this->_get_school_id());

        $this->db
            ->select('p.permission_key')
            ->from($this->permTable . ' dp')
            ->join('tbl_permissions p', 'p.permission_id = dp.permission_id')
            ->where('dp.designation_id', (int)$designation_id)
            ->where('dp.is_deleted', 'n')
            ->where('p.is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('dp.school_id', $sid);
        }

        $rows = $this->db->get()->result();
        return array_map(function($r) { return $r->permission_key; }, $rows);
    }

    /**
     * Replace all permissions for a designation (school-safe and transactional).
     *
     * @param int $designation_id
     * @param array $permission_ids
     * @param int|null $school_id
     * @return bool
     */
    public function set_designation_permissions($designation_id, array $permission_ids, $school_id = null)
    {
        if ($school_id === null) {
            $school_id = $this->_get_school_id();
        }

        $desig = $this->get_by_id($designation_id, $school_id);
        if (!$desig) {
            return false;
        }

        $target_school_id = (int)$desig->school_id;

        $this->db->trans_start();

        // 1. Clear existing designation permissions for this school
        $this->db->where('designation_id', (int)$designation_id)
            ->where('school_id', $target_school_id)
            ->delete($this->permTable);

        // 2. Insert new permissions
        $inserted = [];
        $unique_pids = array_values(array_unique(array_filter(array_map('intval', $permission_ids))));
        foreach ($unique_pids as $pid) {
            if ($pid > 0 && !isset($inserted[$pid])) {
                $inserted[$pid] = true;
                $this->db->insert($this->permTable, [
                    'school_id'      => $target_school_id,
                    'designation_id' => (int)$designation_id,
                    'permission_id'  => $pid,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'is_deleted'     => 'n'
                ]);
            }
        }

        // 3. Keep tbl_roles and tbl_role_permissions in sync for backward compatibility
        $matching_role = $this->db
            ->where('school_id', $target_school_id)
            ->group_start()
                ->where('role_code', $desig->designation_code)
                ->or_where('role_name', $desig->designation_name)
            ->group_end()
            ->get('tbl_roles')
            ->row();

        if ($matching_role) {
            $this->db->where('role_id', $matching_role->role_id)
                ->where('school_id', $target_school_id)
                ->delete('tbl_role_permissions');

            foreach (array_keys($inserted) as $pid) {
                $this->db->insert('tbl_role_permissions', [
                    'school_id'     => $target_school_id,
                    'role_id'       => (int)$matching_role->role_id,
                    'permission_id' => (int)$pid,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'is_deleted'    => 'n'
                ]);
            }
        }

        $this->db->trans_complete();

        // Refresh RBAC in-memory cache if library is present
        if ($this->db->trans_status() && isset($this->rbac) && method_exists($this->rbac, 'refresh_permissions')) {
            $this->rbac->refresh_permissions();
        }

        return $this->db->trans_status();
    }

    /**
     * Get distinct designation categories for a school from the database.
     *
     * @param int|null $school_id
     * @return array
     */
    public function get_categories($school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) 
            ? (int)$school_id 
            : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);

        $this->db
            ->distinct()
            ->select('category')
            ->from($this->table)
            ->where('is_deleted', 'n')
            ->where('category IS NOT NULL', null, false)
            ->where('category !=', '');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        $rows = $this->db->order_by('category', 'ASC')->get()->result();
        $cats = [];
        foreach ($rows as $r) {
            if (!empty(trim($r->category))) {
                $cats[] = trim($r->category);
            }
        }
        return array_values(array_unique($cats));
    }

    /**
     * Initialize predefined system designations and permissions for a newly registered school
     * dynamically from database templates (tbl_designations and tbl_designation_permissions).
     *
     * @param int $school_id
     * @param array $admin_perm_ids Custom or full permissions for School Admin
     * @return array Map of code => designation_id
     */
    public function initialize_school_designations($school_id, array $admin_perm_ids = [])
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) return [];

        // 1. Fetch system designation templates from database
        $templates = $this->db
            ->where('is_system', 1)
            ->where('designation_code !=', 'SUPER_ADMIN')
            ->where('is_deleted', 'n')
            ->group_by('designation_code')
            ->order_by('designation_id', 'ASC')
            ->get($this->table)
            ->result();

        // Fetch all active permissions
        $all_perms = $this->db->select('permission_id')->where('is_deleted', 'n')->get('tbl_permissions')->result();
        $all_active_pids = array_map(function($ap) { return (int)$ap->permission_id; }, $all_perms);

        $result_map = [];

        foreach ($templates as $tmpl) {
            $code = strtoupper(trim($tmpl->designation_code));
            $name = trim($tmpl->designation_name);

            // Avoid duplicate designation creation
            $row = $this->db
                ->where('school_id', $school_id)
                ->group_start()
                    ->where('designation_code', $code)
                    ->or_where('designation_name', $name)
                ->group_end()
                ->get($this->table)
                ->row();

            if ($row) {
                $desig_id = (int)$row->designation_id;
                $this->db->where('designation_id', $desig_id)->update($this->table, [
                    'designation_name' => $name,
                    'designation_code' => $code,
                    'category'         => $tmpl->category,
                    'description'      => $tmpl->description,
                    'is_system'        => 1,
                    'status'           => 1,
                    'is_deleted'       => 'n',
                    'updated_at'       => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert($this->table, [
                    'school_id'        => $school_id,
                    'designation_name' => $name,
                    'designation_code' => $code,
                    'category'         => $tmpl->category,
                    'description'      => $tmpl->description,
                    'is_system'        => 1,
                    'status'           => 1,
                    'is_deleted'       => 'n',
                    'created_at'       => date('Y-m-d H:i:s')
                ]);
                $desig_id = (int)$this->db->insert_id();
            }

            $result_map[$code] = $desig_id;

            // Fetch template permissions directly from database for this designation
            $target_pids = [];
            if ($code === 'SCHOOL_ADMIN') {
                $target_pids = !empty($admin_perm_ids) ? $admin_perm_ids : $all_active_pids;
            } else {
                $tmpl_perms = $this->db
                    ->select('permission_id')
                    ->where('designation_id', (int)$tmpl->designation_id)
                    ->where('is_deleted', 'n')
                    ->get($this->permTable)
                    ->result();
                $target_pids = array_map(function($p) { return (int)$p->permission_id; }, $tmpl_perms);
            }

            if (!empty($target_pids)) {
                $this->set_designation_permissions($desig_id, $target_pids, $school_id);
            }
        }

        return $result_map;
    }

    /**
     * Insert a new custom designation.
     */
    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }

        if (empty($data['designation_code']) && !empty($data['designation_name'])) {
            $slug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim($data['designation_name'])));
            $data['designation_code'] = $slug;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status']     = 1;
        $data['is_deleted'] = 'n';

        $this->db->insert($this->table, $data);
        $insert_id = (int)$this->db->insert_id();

        // Also create synchronized role in tbl_roles for backward compatibility
        $this->db->insert('tbl_roles', [
            'school_id'   => (int)$data['school_id'],
            'role_name'   => $data['designation_name'],
            'role_code'   => $data['designation_code'],
            'user_type'   => $data['category'] ?? 'Staff',
            'description' => $data['description'] ?? '',
            'is_system'   => 0,
            'status'      => 'Active',
            'created_at'  => date('Y-m-d H:i:s'),
            'is_deleted'  => 'n'
        ]);

        return $insert_id;
    }

    public function count_staff_usage($designation_id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $this->db
            ->where('designation_id', (int)$designation_id)
            ->where('status', 1)
            ->where('is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        return (int)$this->db->count_all_results('tbl_staff');
    }

    public function count_user_usage($designation_id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $this->db
            ->where('designation_id', (int)$designation_id)
            ->where('status', 'Active')
            ->where('is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        return (int)$this->db->count_all_results('tbl_users');
    }

    public function check_name_exists($name, $exclude_id = null, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $this->db
            ->where('designation_name', trim($name))
            ->where('is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        if ($exclude_id !== null && (int)$exclude_id > 0) {
            $this->db->where('designation_id !=', (int)$exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Update designation details (scoped by school).
     */
    public function update($id, $data, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $desig = $this->get_by_id($id, $sid);
        if (!$desig) {
            return false;
        }

        $q = $this->db->where($this->primaryKey, (int)$id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }

        $res = $q->update($this->table, $data);

        // Keep matching role in sync in tbl_roles if designation_name or details changed
        if ($res && !empty($desig->designation_code)) {
            $role_update = [];
            if (!empty($data['designation_name'])) {
                $role_update['role_name'] = $data['designation_name'];
            }
            if (isset($data['description'])) {
                $role_update['description'] = $data['description'];
            }
            if (isset($data['category'])) {
                $role_update['user_type'] = $data['category'];
            }
            if (isset($data['status'])) {
                $role_update['status'] = ((int)$data['status'] === 1) ? 'Active' : 'Inactive';
            }
            if (!empty($role_update)) {
                $this->db
                    ->where('school_id', $sid)
                    ->where('role_code', $desig->designation_code)
                    ->update('tbl_roles', $role_update);
            }
        }

        return $res;
    }

    /**
     * Soft delete designation (safely deactivates designation and matching role).
     */
    public function soft_delete($id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        $desig = $this->get_by_id($id, $sid);
        if (!$desig) {
            return false;
        }

        $q = $this->db->where($this->primaryKey, (int)$id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }

        $res = $q->update($this->table, ['status' => 0, 'is_deleted' => 'y', 'updated_at' => date('Y-m-d H:i:s')]);

        if ($res && !empty($desig->designation_code)) {
            $this->db
                ->where('school_id', $sid)
                ->where('role_code', $desig->designation_code)
                ->update('tbl_roles', [
                    'status'     => 'Inactive',
                    'is_deleted' => 'y'
                ]);
        }

        return $res;
    }
}
