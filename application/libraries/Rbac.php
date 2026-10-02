<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rbac (Role-Based Access Control) Library
 *
 * Centralised authorisation engine for role permissions, user overrides,
 * and data-level isolation.
 *
 * OPTIMISATIONS vs original:
 *  - is_super_admin() no longer defaults to TRUE when user_id is absent
 *    (was a security bug: unauthenticated / partial sessions got admin access)
 *  - get_effective_permissions() results are cached per request in $_cache
 *    to avoid repeated DB queries during the same PHP execution cycle
 *  - is_super_admin() result is also cached per user_id
 */
class Rbac {

    protected $CI;

    /** Per-request permission cache keyed by user_id */
    protected $_perm_cache = array();

    /** Per-request super-admin flag cache keyed by user_id */
    protected $_admin_cache = array();

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('User_model');
        $this->CI->load->model('Role_model');
        $this->CI->load->model('Permission_model');
    }

    // -------------------------------------------------------------------------
    // Super-admin detection
    // -------------------------------------------------------------------------

    /**
     * Returns TRUE only when the resolved user is a Super Admin.
     *
     * FIX: Original code returned TRUE when user_id was 0/NULL (no session),
     * effectively granting admin access to unauthenticated requests via CLI
     * or misconfigured sessions. This is now corrected — no user_id means FALSE.
     *
     * @param  int|null $user_id  Explicit user ID, or NULL to read from session.
     * @return bool
     */
    public function is_super_admin($user_id = NULL)
    {
        // --- Resolve user_id ---
        if ($user_id === NULL) {
            $user_data = $this->CI->session->userdata('user');
            if (empty($user_data)) {
                return FALSE; // FIX: was returning TRUE here — security bug
            }
            $user_id    = (int)($user_data['user_id'] ?? 0);
            $role_code        = $user_data['role_code'] ?? '';
            $role_name        = $user_data['role'] ?? '';
            $designation_code = $user_data['designation_code'] ?? '';

            // Fast path: check session role/designation before hitting the DB
            if ($role_code === 'SUPER_ADMIN' || $role_name === 'Super Admin' || $designation_code === 'SUPER_ADMIN') {
                return TRUE;
            }
        }

        if (!$user_id) {
            return FALSE; // FIX: no valid user → never super admin
        }

        // --- Per-request cache ---
        if (isset($this->_admin_cache[$user_id])) {
            return $this->_admin_cache[$user_id];
        }

        $user = $this->CI->User_model->get_by_id($user_id);
        $result = (
            $user &&
            (
                $user->role_name === 'Super Admin' ||
                $user->role_code === 'SUPER_ADMIN' ||
                (!empty($user->designation_code) && $user->designation_code === 'SUPER_ADMIN')
            )
        );

        $this->_admin_cache[$user_id] = $result;
        return $result;
    }

    // -------------------------------------------------------------------------
    // Permission checking
    // -------------------------------------------------------------------------

    /**
     * Check if a user has a specific permission key (e.g. 'students.view').
     *
     * Super Admins always return TRUE without a DB lookup.
     * Results are cached per user_id for the lifetime of the request.
     *
     * @param  string   $permission_key
     * @param  int|null $user_id  NULL reads from session.
     * @return bool
     */
    public function has_permission($permission_key, $user_id = NULL)
    {
        if ($this->is_super_admin($user_id)) {
            return TRUE;
        }

        if ($user_id === NULL) {
            $user_data = $this->CI->session->userdata('user');
            $user_id   = (int)($user_data['user_id'] ?? 0);
        }

        if (!$user_id) {
            return FALSE;
        }

        $effective = $this->_get_cached_permissions($user_id);
        return in_array($permission_key, $effective, TRUE);
    }

    /**
     * Check if user has any permission for a module (e.g. 'students').
     *
     * @param  string   $module_name  e.g. 'students', 'fees'
     * @param  int|null $user_id
     * @return bool
     */
    public function has_module_access($module_name, $user_id = NULL)
    {
        if ($this->is_super_admin($user_id)) {
            return TRUE;
        }

        if ($user_id === NULL) {
            $user_data = $this->CI->session->userdata('user');
            $user_id   = (int)($user_data['user_id'] ?? 0);
        }

        if (!$user_id) {
            return FALSE;
        }

        $prefix    = strtolower($module_name) . '.';
        $effective = $this->_get_cached_permissions($user_id);
        foreach ($effective as $perm) {
            if (strpos($perm, $prefix) === 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    // -------------------------------------------------------------------------
    // Data-level access control
    // -------------------------------------------------------------------------

    /**
     * Enforce data-level ownership:
     *  - Parent: can only access their linked children
     *  - Student: can only access their own record
     *  - All other roles: school-wide access (controlled by module permissions)
     *
     * @param  string   $entity_type  'student', etc.
     * @param  int      $entity_id
     * @param  int|null $user_id
     * @return bool
     */
    public function check_data_access($entity_type, $entity_id, $user_id = NULL)
    {
        if ($user_id === NULL) {
            $user_data = $this->CI->session->userdata('user');
            $user_id   = (int)($user_data['user_id'] ?? 0);
        }

        $user = $this->CI->User_model->get_by_id($user_id);
        if (!$user) return FALSE;

        // Admin & Principal have school-wide data access
        if (in_array($user->role_name, ['Super Admin', 'Admin', 'Principal'])) {
            return TRUE;
        }

        if ($user->user_type === 'Parent') {
            if ($entity_type === 'student') {
                $child = $this->CI->db
                    ->where('parent_user_id', $user_id)
                    ->where('student_id', (int)$entity_id)
                    ->get('tbl_parent_students')
                    ->row();
                return !empty($child);
            }
        } elseif ($user->user_type === 'Student') {
            if ($entity_type === 'student') {
                return ((int)$user->student_id === (int)$entity_id);
            }
        }

        return TRUE;
    }

    // -------------------------------------------------------------------------
    // Audit logging
    // -------------------------------------------------------------------------

    /**
     * Audit log a role or permission modification.
     */
    public function log_audit($action, $target_type, $target_id, $prev = NULL, $new = NULL, $details = '')
    {
        $user_data = $this->CI->session->userdata('user');
        $user_id   = (int)($user_data['user_id'] ?? 0) ?: 1;

        $data = [
            'user_id'        => $user_id,
            'action'         => $action,
            'target_type'    => $target_type,
            'target_id'      => (int)$target_id,
            'previous_value' => is_array($prev) ? json_encode($prev) : $prev,
            'new_value'      => is_array($new)  ? json_encode($new)  : $new,
            'details'        => $details,
            'created_at'     => date('Y-m-d H:i:s'),
        ];
        $this->CI->db->insert('tbl_permission_audit_logs', $data);
        return $this->CI->db->insert_id();
    }

    /**
     * Audit log a login event.
     */
    public function log_login_activity($username, $status, $user_id = NULL, $failure_reason = NULL)
    {
        $data = [
            'user_id'        => $user_id,
            'username'       => $username,
            'ip_address'     => $this->CI->input->ip_address(),
            'user_agent'     => substr($this->CI->input->user_agent() ?: 'Browser', 0, 250),
            'status'         => $status,
            'failure_reason' => $failure_reason,
            'created_at'     => date('Y-m-d H:i:s'),
            'is_deleted'     => 'n',
        ];
        $this->CI->db->insert('tbl_user_login_activity', $data);
        return $this->CI->db->insert_id();
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Return effective permissions for a user, using per-request cache.
     * Prevents repeated DB queries when multiple permission checks run per page.
     *
     * @param  int $user_id
     * @return array  Array of permission key strings.
     */
    protected function _get_cached_permissions($user_id)
    {
        if (!isset($this->_perm_cache[$user_id])) {
            $this->_perm_cache[$user_id] = $this->CI->User_model->get_effective_permissions($user_id);
        }
        return $this->_perm_cache[$user_id];
    }

    /**
     * Clear cached permissions so subsequent checks query fresh DB records.
     *
     * @param int|null $user_id
     * @return void
     */
    public function refresh_permissions($user_id = null)
    {
        if ($user_id !== null) {
            unset($this->_perm_cache[$user_id]);
        } else {
            $this->_perm_cache = [];
        }

        if (isset($this->CI->User_model) && method_exists($this->CI->User_model, 'clear_permission_cache')) {
            $this->CI->User_model->clear_permission_cache($user_id);
        }
    }
}
