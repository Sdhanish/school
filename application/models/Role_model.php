<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    protected $table = 'tbl_roles';
    protected $primaryKey = 'role_id';

    /**
     * Internal helper: resolve current school_id from helper or session.
     */
    private function _get_school_id()
    {
        if (function_exists('get_current_school_id')) {
            return (int)get_current_school_id();
        }
        return 0;
    }

    /**
     * Is the current user Super Admin?
     */
    private function _is_super_admin()
    {
        $CI =& get_instance();
        return isset($CI->rbac) && $CI->rbac->is_super_admin();
    }

    /**
     * Get all roles for the specified or current school.
     *
     * @param  bool $include_inactive  If TRUE, include Inactive roles.
     * @param  int|null $school_id
     * @return array
     */
    public function get_all($include_inactive = FALSE, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();

        $this->db->from($this->table);

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        if (!$include_inactive) {
            $this->db->where('status', 'Active');
        }

        return $this->db->order_by('role_id', 'ASC')->get()->result();
    }

    /**
     * Get roles with user counts, scoped to the specified or current school.
     */
    public function get_roles_with_counts($school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();

        $school_clause_role = ($sid > 0) ? "AND r.school_id = {$sid}" : '';
        $school_clause_user = ($sid > 0) ? "AND u.school_id = {$sid}" : '';
        $school_clause_perm = ($sid > 0) ? "AND rp.school_id = {$sid}" : '';

        return $this->db->query("
            SELECT r.*, 
                   COUNT(u.user_id) as total_users,
                   SUM(CASE WHEN u.status = 'Active' THEN 1 ELSE 0 END) as active_users,
                   SUM(CASE WHEN u.status != 'Active' THEN 1 ELSE 0 END) as inactive_users,
                   (SELECT COUNT(*) FROM tbl_role_permissions rp WHERE rp.role_id = r.role_id {$school_clause_perm}) as permission_count
            FROM tbl_roles r
            LEFT JOIN tbl_users u ON u.role_id = r.role_id AND u.is_deleted = 'n' {$school_clause_user}
            WHERE 1=1 {$school_clause_role}
            GROUP BY r.role_id
            ORDER BY r.role_id ASC
        ")->result();
    }

    /**
     * Get role by ID.
     */
    public function get_by_id($id, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : 0;
        $q = $this->db->where($this->primaryKey, (int)$id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        return $q->get($this->table)->row();
    }

    public function get_by_code($code, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : 0;
        $q = $this->db->where('role_code', $code);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        return $q->get($this->table)->row();
    }

    /**
     * Get all permission IDs assigned to a role.
     */
    public function get_role_permission_ids($role_id, $school_id = NULL)
    {
        $role = $this->get_by_id($role_id, $school_id);
        if ($role && ($role->role_code === 'SUPER_ADMIN' || $role->role_name === 'Super Admin')) {
            $all = $this->db->select('permission_id')->get('tbl_permissions')->result();
            return array_map(function($r) { return (int)$r->permission_id; }, $all);
        }

        $sid = $school_id ? (int)$school_id : ($role ? (int)$role->school_id : $this->_get_school_id());

        $q = $this->db
            ->select('permission_id')
            ->where('role_id', (int)$role_id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        $rows = $q->get('tbl_role_permissions')->result();
        return array_map(function($r) { return (int)$r->permission_id; }, $rows);
    }

    /**
     * Get all permission keys assigned to a role.
     */
    public function get_role_permission_keys($role_id, $school_id = NULL)
    {
        $role = $this->get_by_id($role_id, $school_id);
        if ($role && ($role->role_code === 'SUPER_ADMIN' || $role->role_name === 'Super Admin')) {
            $all = $this->db->select('permission_key')->get('tbl_permissions')->result();
            return array_map(function($r) { return $r->permission_key; }, $all);
        }

        $sid = $school_id ? (int)$school_id : ($role ? (int)$role->school_id : $this->_get_school_id());

        $this->db
            ->select('p.permission_key')
            ->from('tbl_role_permissions rp')
            ->join('tbl_permissions p', 'p.permission_id = rp.permission_id')
            ->where('rp.role_id', (int)$role_id);

        if ($sid > 0) {
            $this->db->where('rp.school_id', $sid);
        }

        $rows = $this->db->get()->result();
        return array_map(function($r) { return $r->permission_key; }, $rows);
    }

    /**
     * Replace all permissions for a role (school-safe).
     * Validates that the role belongs to the target school before modifying.
     */
    public function set_role_permissions($role_id, array $permission_ids, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $role = $this->get_by_id($role_id);
        if (!$role) {
            return FALSE;
        }

        if ($sid > 0 && (int)$role->school_id !== $sid && $role->role_code !== 'SUPER_ADMIN') {
            return FALSE; // Cross-school modification attempt blocked
        }

        $target_sid = (int)$role->school_id;

        $this->db->trans_start();
        $this->db->where('role_id', (int)$role_id)
            ->where('school_id', $target_sid)
            ->delete('tbl_role_permissions');

        $inserted = [];
        $unique_pids = array_values(array_unique(array_filter(array_map('intval', $permission_ids))));
        foreach ($unique_pids as $pid) {
            $pid = (int)$pid;
            if ($pid > 0 && !isset($inserted[$pid])) {
                $inserted[$pid] = true;
                $this->db->insert('tbl_role_permissions', [
                    'role_id'       => (int)$role_id,
                    'permission_id' => $pid,
                    'school_id'     => $target_sid > 0 ? $target_sid : NULL,
                ]);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Insert a new role, always injecting school_id from current context.
     */
    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update a role — scoped to current school to prevent cross-school edits.
     */
    public function update($id, $data, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $q = $this->db->where($this->primaryKey, (int)$id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        $q->update($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function toggle_status($id, $school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->_get_school_id();
        $role = $this->get_by_id($id);
        if (!$role || $role->is_system) return FALSE; // Cannot deactivate protected system role

        // School ownership check
        if ($sid > 0 && (int)$role->school_id !== $sid) {
            return FALSE;
        }

        $new_status = ($role->status === 'Active') ? 'Inactive' : 'Active';
        $this->db->where($this->primaryKey, (int)$id)->update($this->table, ['status' => $new_status]);
        return $this->db->affected_rows() > 0;
    }
}
