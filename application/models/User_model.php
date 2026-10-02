<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table = 'tbl_users';
    protected $primaryKey = 'user_id';

    // =========================================================================
    // Internal helper: add school_id scope to the active query.
    // Super Admin is excluded from scoping (they see/manage all users).
    // $force_school_id can be passed explicitly from non-CI contexts (e.g. CLI).
    // =========================================================================
    private function _apply_school_scope($force_school_id = NULL)
    {
        $school_id = $force_school_id !== NULL ? (int)$force_school_id : NULL;

        // Try CI framework context
        if ($school_id === NULL && function_exists('get_current_school_id')) {
            $school_id = (int)get_current_school_id();
        }

        if ($school_id > 0) {
            $this->db->where('u.school_id', $school_id);
        }
    }

    public function get_all(array $filters = array())
    {
        $school_id = $filters['_school_id'] ?? NULL;
        unset($filters['_school_id']);

        $this->db
            ->select('u.user_id, u.name, u.username, u.email, u.phone, u.role_id, u.designation_id, u.staff_id, u.student_id,
                      u.school_id, u.user_type, u.status, u.last_login_at, u.last_login_at as last_login, u.created_at, u.updated_at, u.is_deleted,
                      COALESCE(dg.designation_name, r.role_name) as designation_name,
                      COALESCE(dg.designation_code, r.role_code) as designation_code,
                      dg.category as designation_category,
                      r.role_name, r.role_code, r.user_type as role_user_type,
                      s.full_name as staff_name,
                      st.first_name as student_first_name, st.last_name as student_last_name, st.admission_number')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id AND r.school_id = u.school_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id AND dg.school_id = u.school_id', 'left')
            ->join('tbl_staff s', 's.staff_id = u.staff_id AND s.school_id = u.school_id', 'left')
            ->join('tbl_students st', 'st.student_id = u.student_id AND st.school_id = u.school_id', 'left')
            ->where('u.is_deleted', 'n');

        $this->_apply_school_scope($school_id);

        if (!empty($filters['role_id'])) {
            $this->db->where('u.role_id', (int)$filters['role_id']);
        }
        if (!empty($filters['user_type'])) {
            $this->db->where('u.user_type', $filters['user_type']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('u.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start()
                ->like('u.name', $search)
                ->or_like('u.username', $search)
                ->or_like('u.email', $search)
                ->or_like('u.phone', $search)
                ->or_like('st.admission_number', $search)
            ->group_end();
        }

        return $this->db->order_by('u.user_id', 'ASC')->get()->result();
    }

    public function count_filtered(array $filters = array())
    {
        $school_id = $filters['_school_id'] ?? NULL;
        unset($filters['_school_id']);

        $this->db
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id AND r.school_id = u.school_id', 'left')
            ->join('tbl_staff s', 's.staff_id = u.staff_id AND s.school_id = u.school_id', 'left')
            ->join('tbl_students st', 'st.student_id = u.student_id AND st.school_id = u.school_id', 'left')
            ->where('u.is_deleted', 'n');

        $this->_apply_school_scope($school_id);

        if (!empty($filters['role_id'])) {
            $this->db->where('u.role_id', (int)$filters['role_id']);
        }
        if (!empty($filters['user_type'])) {
            $this->db->where('u.user_type', $filters['user_type']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('u.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start()
                ->like('u.name', $search)
                ->or_like('u.username', $search)
                ->or_like('u.email', $search)
                ->or_like('u.phone', $search)
                ->or_like('st.admission_number', $search)
            ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function get_datatables_data(array $filters = array(), $limit = 25, $start = 0, $order_col = 'u.user_id', $order_dir = 'ASC')
    {
        $school_id = $filters['_school_id'] ?? NULL;
        unset($filters['_school_id']);

        $allowed_cols = array(
            0 => 'u.name',
            1 => 'u.username',
            2 => 'r.role_name',
            3 => 'u.email',
            4 => 'u.status',
            5 => 'u.created_at',
            6 => 'u.user_id'
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'u.user_id';
        $dir = (strtoupper($order_dir) === 'DESC') ? 'DESC' : 'ASC';

        $this->db
            ->select('u.user_id, u.name, u.username, u.email, u.phone, u.role_id, u.designation_id, u.staff_id, u.student_id,
                      u.school_id, u.user_type, u.status, u.last_login_at, u.last_login_at as last_login, u.created_at, u.updated_at,
                      COALESCE(dg.designation_name, r.role_name) as designation_name,
                      COALESCE(dg.designation_code, r.role_code) as designation_code,
                      r.role_name, r.role_code, r.user_type as role_user_type,
                      s.full_name as staff_name,
                      st.first_name as student_first_name, st.last_name as student_last_name, st.admission_number')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id AND r.school_id = u.school_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id AND dg.school_id = u.school_id', 'left')
            ->join('tbl_staff s', 's.staff_id = u.staff_id AND s.school_id = u.school_id', 'left')
            ->join('tbl_students st', 'st.student_id = u.student_id AND st.school_id = u.school_id', 'left')
            ->where('u.is_deleted', 'n');

        $this->_apply_school_scope($school_id);

        if (!empty($filters['role_id'])) {
            $this->db->where('u.role_id', (int)$filters['role_id']);
        }
        if (!empty($filters['user_type'])) {
            $this->db->where('u.user_type', $filters['user_type']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('u.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start()
                ->like('u.name', $search)
                ->or_like('u.username', $search)
                ->or_like('u.email', $search)
                ->or_like('u.phone', $search)
                ->or_like('st.admission_number', $search)
                ->or_like('r.role_name', $search)
            ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function get_datatables_count_all($force_school_id = NULL)
    {
        $school_id = $force_school_id !== NULL ? (int)$force_school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        $this->db->reset_query();
        $this->db->where('is_deleted', 'n');
        if ($school_id > 0) {
            $this->db->where('school_id', $school_id);
        }
        return $this->db->count_all_results('tbl_users');
    }

    public function get_dropdown($school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);

        $this->db
            ->select('user_id, name, username, email')
            ->where('is_deleted', 'n')
            ->where('status', 'Active');

        if ($sid > 0) {
            $this->db->where('school_id', $sid);
        }

        return $this->db
            ->order_by('name', 'ASC')
            ->get('tbl_users')
            ->result();
    }

    public function get_by_id($id)
    {
        static $cached_users = [];
        $id = (int)$id;
        if (isset($cached_users[$id])) {
            return $cached_users[$id];
        }

        $res = $this->db
            ->select('u.user_id, u.name, u.username, u.email, u.phone, u.role_id, u.designation_id, u.staff_id, u.student_id,
                      u.school_id, u.user_type, u.status, u.last_login_at, u.last_login_at as last_login,
                      u.created_at, u.updated_at, u.is_deleted,
                      COALESCE(dg.designation_name, r.role_name) as designation_name,
                      COALESCE(dg.designation_code, r.role_code) as designation_code,
                      r.role_name, r.role_code,
                      s.full_name as staff_name, s.employee_code,
                      st.first_name as student_first_name, st.last_name as student_last_name, st.admission_number')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id', 'left')
            ->join('tbl_staff s', 's.staff_id = u.staff_id', 'left')
            ->join('tbl_students st', 'st.student_id = u.student_id', 'left')
            ->where('u.user_id', $id)
            ->where('u.is_deleted', 'n')
            ->get()
            ->row();

        $cached_users[$id] = $res;
        return $res;
    }

    /**
     * Verify that a user_id belongs to the current active school.
     * Super Admin always passes. Returns FALSE if no match.
     *
     * @param  int $user_id
     * @return bool
     */
    public function verify_school_ownership($user_id)
    {
        if (class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin()) {
            return TRUE;
        }

        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        if (!$school_id) return TRUE; // graceful fallback

        $count = $this->db
            ->where('user_id', (int)$user_id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);

        return $count > 0;
    }

    /**
     * Get a user record WITH the password hash for authentication purposes only.
     * Do NOT use this in list views or admin interfaces.
     */
    public function get_for_auth($id)
    {
        return $this->db
            ->select('u.*, r.role_name, r.role_code, COALESCE(dg.designation_name, r.role_name) as designation_name, COALESCE(dg.designation_code, r.role_code) as designation_code, dg.category as designation_category')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id', 'left')
            ->where('u.user_id', $id)
            ->where('u.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function get_by_username_or_email($identifier)
    {
        // NOTE: No school_id filter — login must find the user first,
        // then Auth.php checks school status after authentication.
        return $this->db
            ->select('u.*, r.role_name, r.role_code, COALESCE(dg.designation_name, r.role_name) as designation_name, COALESCE(dg.designation_code, r.role_code) as designation_code, dg.category as designation_category')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id', 'left')
            ->where('u.is_deleted', 'n')
            ->group_start()
                ->where('u.email', $identifier)
                ->or_where('u.username', $identifier)
            ->group_end()
            ->get()
            ->row();
    }

    /**
     * Check if a username is already taken among ACTIVE users (is_deleted = 'n')
     * Username uniqueness is GLOBAL (avoids login ambiguity across schools).
     */
    public function is_username_exists($username, $exclude_user_id = null)
    {
        $username = trim((string)$username);
        if ($username === '') {
            return false;
        }

        $this->db->from($this->table)
            ->where('username', $username)
            ->where('is_deleted', 'n');

        if (!empty($exclude_user_id)) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_user_id);
        }

        return $this->db->count_all_results() > 0;
    }

    /**
     * Check if an email is already taken among ACTIVE users (is_deleted = 'n')
     * Email uniqueness is GLOBAL to avoid login ambiguity.
     */
    public function is_email_exists($email, $exclude_user_id = null)
    {
        $email = trim((string)$email);
        if ($email === '') {
            return false;
        }

        $this->db->from($this->table)
            ->where('email', $email)
            ->where('is_deleted', 'n');

        if (!empty($exclude_user_id)) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_user_id);
        }

        return $this->db->count_all_results() > 0;
    }

    /**
     * Verify user credentials for login (only active, non-deleted users)
     * No school_id filter — school validation happens in Auth.php after this call.
     */
    public function verify_credentials($identifier, $password)
    {
        $identifier = trim((string)$identifier);
        if ($identifier === '' || empty($password)) {
            return false;
        }

        $user = $this->db
            ->select('u.*, r.role_name, r.role_code, COALESCE(dg.designation_name, r.role_name) as designation_name, COALESCE(dg.designation_code, r.role_code) as designation_code, dg.category as designation_category')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = u.designation_id', 'left')
            ->where('u.is_deleted', 'n')
            ->group_start()
                ->where('u.email', $identifier)
                ->or_where('u.username', $identifier)
            ->group_end()
            ->get()
            ->row();

        if (!$user) {
            return false;
        }

        // Account must be active and not locked
        if ($user->status !== 'Active') {
            return false;
        }

        if (!empty($user->locked_until) && strtotime($user->locked_until) > time()) {
            return false;
        }

        $password_valid = false;
        if (password_verify($password, $user->password)) {
            $password_valid = true;
        } elseif ($user->password === $password || md5($password) === $user->password || sha1($password) === $user->password) {
            $password_valid = true;
            // Upgrade to bcrypt hash
            $this->update($user->user_id, ['password' => $password]);
        }

        if ($password_valid) {
            // Reset failed login counter and update last login time
            $this->db->where('user_id', $user->user_id)->update('tbl_users', [
                'failed_login_attempts' => 0,
                'locked_until'          => NULL,
                'last_login_at'         => date('Y-m-d H:i:s')
            ]);

            // Record successful login
            $this->log_login_activity($user->user_id, $user->username, 'Successful');
            return $user;
        } else {
            // Track failed attempts
            $new_fails = (int)$user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $new_fails];
            if ($new_fails >= 5) {
                $updates['status'] = 'Locked';
                $updates['locked_until'] = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            }
            $this->db->where('user_id', $user->user_id)->update('tbl_users', $updates);

            $this->log_login_activity($user->user_id, $user->username, ($new_fails >= 5) ? 'Locked' : 'Failed', 'Invalid Password');
            return false;
        }
    }

    /**
     * Record login activity into tbl_user_login_activity
     */
    public function log_login_activity($user_id, $username, $status, $failure_reason = NULL)
    {
        $ip = $this->input->ip_address() ?: '0.0.0.0';
        $ua = substr($this->input->user_agent() ?: 'Browser', 0, 250);

        $this->db->insert('tbl_user_login_activity', [
            'user_id'        => $user_id ? (int)$user_id : NULL,
            'username'       => $username,
            'ip_address'     => $ip,
            'user_agent'     => $ua,
            'status'         => $status,
            'failure_reason' => $failure_reason,
            'created_at'     => date('Y-m-d H:i:s'),
            'is_deleted'     => 'n'
        ]);
    }


    /** In-memory cache for get_effective_permissions */
    protected static $_effective_perm_cache = [];

    /**
     * Clear effective permission in-memory cache
     */
    public function clear_permission_cache($user_id = null)
    {
        if ($user_id !== null) {
            unset(self::$_effective_perm_cache[(int)$user_id]);
        } else {
            self::$_effective_perm_cache = [];
        }
    }

    /**
     * Compute Effective Permissions for a user
     * Role Permissions + (User Overrides: Grant) - (User Overrides: Revoke)
     */
    public function get_effective_permissions($user_id)
    {
        $uid = (int)$user_id;
        if (isset(self::$_effective_perm_cache[$uid])) {
            return self::$_effective_perm_cache[$uid];
        }

        $user = $this->get_by_id($uid);
        if (!$user) return [];

        // Super Admin has all system permissions enabled
        if ($user->role_name === 'Super Admin' || $user->role_code === 'SUPER_ADMIN' || (!empty($user->designation_code) && $user->designation_code === 'SUPER_ADMIN') || (int)$user->role_id === 1) {
            $all = $this->db->select('permission_key')->where('is_deleted', 'n')->get('tbl_permissions')->result();
            $res = array_map(function($r) { return $r->permission_key; }, $all);
            self::$_effective_perm_cache[$uid] = $res;
            return $res;
        }

        // 1. Get Base Designation / Role Permissions
        $permissions = [];
        if (!empty($user->designation_id)) {
            $desig_perms = $this->db
                ->select('p.permission_key')
                ->from('tbl_designation_permissions dp')
                ->join('tbl_permissions p', 'p.permission_id = dp.permission_id')
                ->where('dp.designation_id', (int)$user->designation_id)
                ->where('dp.school_id', (int)$user->school_id)
                ->where('dp.is_deleted', 'n')
                ->where('p.is_deleted', 'n')
                ->get()
                ->result();
            if (!empty($desig_perms)) {
                $permissions = array_map(function($r) { return $r->permission_key; }, $desig_perms);
            }
        }

        // Fallback to role permissions if designation permissions not yet mapped
        if (empty($permissions) && !empty($user->role_id)) {
            $role_perms = $this->db
                ->select('p.permission_key')
                ->from('tbl_role_permissions rp')
                ->join('tbl_permissions p', 'p.permission_id = rp.permission_id')
                ->where('rp.role_id', (int)$user->role_id)
                ->where('rp.school_id', (int)$user->school_id)
                ->where('rp.is_deleted', 'n')
                ->where('p.is_deleted', 'n')
                ->get()
                ->result();
            $permissions = array_map(function($r) { return $r->permission_key; }, $role_perms);
        }

        // 2. Apply User Overrides
        $overrides = $this->db
            ->select('p.permission_key, up.override_type')
            ->from('tbl_user_permissions up')
            ->join('tbl_permissions p', 'p.permission_id = up.permission_id')
            ->where('up.user_id', $uid)
            ->where('up.school_id', (int)$user->school_id)
            ->where('up.is_deleted', 'n')
            ->get()
            ->result();

        foreach ($overrides as $ov) {
            if ($ov->override_type === 'Grant') {
                if (!in_array($ov->permission_key, $permissions)) {
                    $permissions[] = $ov->permission_key;
                }
            } elseif ($ov->override_type === 'Revoke') {
                $permissions = array_diff($permissions, [$ov->permission_key]);
            }
        }

        $result = array_values(array_unique($permissions));
        self::$_effective_perm_cache[$uid] = $result;
        return $result;
    }

    public function get_user_overrides($user_id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);

        $this->db
            ->select('up.*, p.permission_key, p.permission_name, p.module')
            ->from('tbl_user_permissions up')
            ->join('tbl_permissions p', 'p.permission_id = up.permission_id')
            ->where('up.user_id', (int)$user_id)
            ->where('up.is_deleted', 'n');

        if ($sid > 0) {
            $this->db->where('up.school_id', $sid);
        }

        return $this->db->get()->result();
    }

    public function set_user_override($user_id, $permission_id, $override_type, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        if ($sid <= 0) {
            $user = $this->get_by_id($user_id);
            $sid = $user ? (int)$user->school_id : 1;
        }

        $q = $this->db
            ->where('user_id', (int)$user_id)
            ->where('permission_id', (int)$permission_id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        $exists = $q->get('tbl_user_permissions')->row();

        if ($exists) {
            return $this->db
                ->where('id', $exists->id)
                ->where('school_id', $sid)
                ->update('tbl_user_permissions', ['override_type' => $override_type, 'is_deleted' => 'n']);
        } else {
            return $this->db->insert('tbl_user_permissions', [
                'school_id'     => $sid,
                'user_id'       => (int)$user_id,
                'permission_id' => (int)$permission_id,
                'override_type' => $override_type,
                'created_at'    => date('Y-m-d H:i:s'),
                'is_deleted'    => 'n'
            ]);
        }
    }

    public function remove_user_override($user_id, $permission_id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        $q = $this->db
            ->where('user_id', (int)$user_id)
            ->where('permission_id', (int)$permission_id);
        if ($sid > 0) {
            $q->where('school_id', $sid);
        }
        return $q->delete('tbl_user_permissions');
    }

    public function get_parent_children($parent_user_id, $school_id = null)
    {
        $sid = ($school_id !== null && (int)$school_id > 0) ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        $this->db
            ->select('ps.*, s.first_name, s.last_name, s.admission_number, c.class_name, div.division_name as division_name, div.division_name as section_name')
            ->from('tbl_parent_students ps')
            ->join('tbl_students s', 's.student_id = ps.student_id AND s.school_id = ps.school_id')
            ->join('tbl_classes c', 'c.class_id = s.class_id AND c.school_id = ps.school_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = s.section_id AND div.school_id = ps.school_id', 'left')
            ->where('ps.parent_user_id', (int)$parent_user_id);

        if ($sid > 0) {
            $this->db->where('ps.school_id', $sid);
        }

        return $this->db->get()->result();
    }

    public function link_parent_student($parent_user_id, $student_id, $rel = 'Parent')
    {
        return $this->db->query("INSERT IGNORE INTO tbl_parent_students (parent_user_id, student_id, relationship) VALUES (?, ?, ?)",
            [(int)$parent_user_id, (int)$student_id, $rel]);
    }

    public function get_dashboard_stats()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = (class_exists('CI', FALSE) && isset(CI::$APP->rbac)) ? CI::$APP->rbac->is_super_admin() : FALSE;
        $school_clause = (!$is_super && $school_id > 0) ? "AND school_id = {$school_id}" : '';

        $row = $this->db->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_users,
                SUM(CASE WHEN status = 'Locked' THEN 1 ELSE 0 END) as locked_users,
                SUM(CASE WHEN status = 'Suspended' THEN 1 ELSE 0 END) as suspended_users,
                SUM(CASE WHEN user_type = 'Admin' THEN 1 ELSE 0 END) as admin_users,
                SUM(CASE WHEN user_type = 'Teacher' THEN 1 ELSE 0 END) as teacher_users,
                SUM(CASE WHEN user_type = 'Staff' OR user_type IN ('Accountant','Transport Manager','Receptionist','Librarian') THEN 1 ELSE 0 END) as staff_users,
                SUM(CASE WHEN user_type = 'Parent' THEN 1 ELSE 0 END) as parent_users,
                SUM(CASE WHEN user_type = 'Student' THEN 1 ELSE 0 END) as student_users
            FROM tbl_users
            WHERE is_deleted = 'n' {$school_clause}
        ")->row();

        return (object)[
            'total_users'     => (int)($row->total_users ?? 0),
            'active_users'    => (int)($row->active_users ?? 0),
            'inactive_users'  => (int)($row->inactive_users ?? 0),
            'locked_users'    => (int)($row->locked_users ?? 0),
            'suspended_users' => (int)($row->suspended_users ?? 0),
            'admin_users'     => (int)($row->admin_users ?? 0),
            'teacher_users'   => (int)($row->teacher_users ?? 0),
            'staff_users'     => (int)($row->staff_users ?? 0),
            'parent_users'    => (int)($row->parent_users ?? 0),
            'student_users'   => (int)($row->student_users ?? 0)
        ];
    }

    /**
     * Safety Check: Ensure at least one active Admin remains for the current school.
     */
    public function count_active_admins()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = (class_exists('CI', FALSE) && isset(CI::$APP->rbac)) ? CI::$APP->rbac->is_super_admin() : FALSE;

        $this->db
            ->where('user_type', 'Admin')
            ->where('status', 'Active')
            ->where('is_deleted', 'n');

        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }

        return (int)$this->db->count_all_results($this->table);
    }

    public function insert($data)
    {
        // Always inject school_id from server-side context unless explicitly provided
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }

        // Synchronize designation_id and role_id
        if (!empty($data['designation_id']) && empty($data['role_id'])) {
            $data['role_id'] = (int)$data['designation_id'];
        } elseif (!empty($data['role_id']) && empty($data['designation_id'])) {
            $data['designation_id'] = (int)$data['role_id'];
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        // Synchronize designation_id and role_id if updated
        if (!empty($data['designation_id']) && empty($data['role_id'])) {
            $data['role_id'] = (int)$data['designation_id'];
        } elseif (!empty($data['role_id']) && empty($data['designation_id'])) {
            $data['designation_id'] = (int)$data['role_id'];
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        return $this->db
            ->where($this->primaryKey, $id)
            ->update($this->table, $data);
    }

    /**
     * School-safe update: only updates if user belongs to current school.
     * Returns affected_rows > 0 to detect cross-school IDOR.
     */
    public function update_scoped($id, $data, $school_id = NULL)
    {
        if ($school_id === NULL && function_exists('get_current_school_id')) {
            $school_id = (int)get_current_school_id();
        }

        // Synchronize designation_id and role_id if updated
        if (!empty($data['designation_id']) && empty($data['role_id'])) {
            $data['role_id'] = (int)$data['designation_id'];
        } elseif (!empty($data['role_id']) && empty($data['designation_id'])) {
            $data['designation_id'] = (int)$data['role_id'];
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        $this->db
            ->where($this->primaryKey, $id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function toggle_status($id)
    {
        $user = $this->get_by_id($id);
        if (!$user) return FALSE;

        // Last active Admin safety guard
        if ($user->user_type === 'Admin' && $user->status === 'Active') {
            if ($this->count_active_admins() <= 1) {
                return 'LAST_ADMIN';
            }
        }

        $new_status = ($user->status === 'Active') ? 'Inactive' : 'Active';

        // Scope update to current school (IDOR guard)
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = (class_exists('CI', FALSE) && isset(CI::$APP->rbac)) ? CI::$APP->rbac->is_super_admin() : FALSE;

        $q = $this->db->where($this->primaryKey, $id);
        if (!$is_super && $school_id > 0) {
            $q->where('school_id', $school_id);
        }
        $q->update($this->table, ['status' => $new_status]);
        return $this->db->affected_rows() > 0;
    }

    public function unlock_user($id)
    {
        return $this->db->where($this->primaryKey, $id)->update($this->table, [
            'status'                => 'Active',
            'failed_login_attempts' => 0,
            'locked_until'          => NULL
        ]);
    }

    public function soft_delete($id)
    {
        // Scoped delete: only deletes if user belongs to current school (IDOR guard)
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = (class_exists('CI', FALSE) && isset(CI::$APP->rbac)) ? CI::$APP->rbac->is_super_admin() : FALSE;

        $q = $this->db->where($this->primaryKey, $id);
        if (!$is_super && $school_id > 0) {
            $q->where('school_id', $school_id);
        }
        $q->update($this->table, [
            'status'     => 'Inactive',
            'is_deleted' => 'y',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function get_login_activity($limit = 100)
    {
        return $this->db
            ->order_by('activity_id', 'DESC')
            ->limit($limit)
            ->get('tbl_user_login_activity')
            ->result();
    }

    public function get_audit_logs($limit = 100)
    {
        return $this->db
            ->select('pal.*, u.name as user_name, u.username')
            ->from('tbl_permission_audit_logs pal')
            ->join('tbl_users u', 'u.user_id = pal.user_id', 'left')
            ->order_by('pal.log_id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    /**
     * Get password hash for a specific user ID with multi-school isolation.
     * Selects only required columns (never SELECT *).
     */
    public function get_user_password_hash($user_id, $school_id = NULL)
    {
        $this->db
            ->select('user_id, password, status, school_id')
            ->from($this->table)
            ->where($this->primaryKey, (int)$user_id)
            ->where('is_deleted', 'n');

        if ($school_id !== NULL) {
            $this->db->where('school_id', (int)$school_id);
        }

        $row = $this->db->get()->row();
        return $row ? $row->password : null;
    }

    /**
     * Verify whether a provided plain-text password matches the stored hash for a user.
     */
    public function verify_current_password($user_id, $plain_password, $school_id = NULL)
    {
        $hash = $this->get_user_password_hash($user_id, $school_id);
        if (empty($hash) || empty($plain_password)) {
            return false;
        }

        if (password_verify($plain_password, $hash)) {
            return true;
        }

        // Backward compatibility with legacy unsalted hashes if any exist
        if ($hash === $plain_password || md5($plain_password) === $hash || sha1($plain_password) === $hash) {
            return true;
        }

        return false;
    }

    /**
     * Change a user's password using standard bcrypt password_hash.
     */
    public function change_user_password($user_id, $new_password, $school_id = NULL)
    {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $this->db
            ->where($this->primaryKey, (int)$user_id)
            ->where('is_deleted', 'n');

        if ($school_id !== NULL) {
            $this->db->where('school_id', (int)$school_id);
        }

        $this->db->update($this->table, [
            'password'   => $hash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() >= 0;
    }
}
