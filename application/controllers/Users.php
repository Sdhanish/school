<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Role_model');
        $this->load->model('Permission_model');
        $this->load->model('Designation_model');
        $this->load->model('Security_setting_model');
        $this->load->library('Rbac');
        $this->load->helper(['form', 'url']);
    }

    /**
     * Verify a user_id belongs to the current active school.
     * Super Admin bypasses this check.
     * Returns FALSE and sends a 403 / redirect on failure.
     */
    private function _verify_user_school($user_id, $redirect_to = 'users/list')
    {
        if ($this->rbac->is_super_admin()) {
            return TRUE;
        }
        if (!$this->User_model->verify_school_ownership($user_id)) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => false, 'message' => 'Access denied.']));
                $this->output->_display();
                exit;
            }
            $this->session->set_flashdata('error', 'Access denied. You cannot manage users from another school.');
            redirect($redirect_to);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 1. User & Permission Dashboard
     */
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission('users.view');

        $stats = $this->User_model->get_dashboard_stats();
        $roles_summary = $this->Role_model->get_roles_with_counts();
        $recent_activity = $this->User_model->get_login_activity(8);

        $this->render('pages/users/dashboard', [
            'title'           => 'User & Permission Dashboard',
            'page_key'        => 'user-dashboard',
            'stats'           => $stats,
            'roles_summary'   => $roles_summary,
            'recent_activity' => $recent_activity,
        ]);
    }

    /**
     * 2. All Users Management Directory
     */
    public function list_users()
    {
        $this->require_permission('users.view');

        $filters = [
            'role_id'   => $this->input->get('role_id', TRUE),
            'user_type' => $this->input->get('user_type', TRUE),
            'status'    => $this->input->get('status', TRUE),
            'search'    => $this->input->get('search', TRUE),
            '_school_id' => $this->school_id,
        ];

        $users = $this->User_model->get_all($filters);
        $roles = $this->Role_model->get_all(false, $this->school_id);
        $designations = $this->Designation_model->get_all(['school_id' => $this->school_id]);

        $this->render('pages/users/index', [
            'title'        => 'User Management',
            'page_key'     => 'users',
            'users'        => $users,
            'roles'        => $roles,
            'designations' => $designations,
            'filters'      => $filters,
        ]);
    }

    public function ajax_list()
    {
        $this->require_permission('users.view');

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $order  = $this->input->post('order');
        $search = $this->input->post('search');

        $order_col_idx = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir     = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';
        $search_val    = isset($search['value']) ? trim($search['value']) : '';

        $filters = [
            '_school_id' => $this->school_id,
            'role_id'    => $this->input->post('role_id', TRUE) ?: $this->input->get('role_id', TRUE),
            'user_type'  => $this->input->post('user_type', TRUE) ?: $this->input->get('user_type', TRUE),
            'status'     => $this->input->post('status', TRUE) ?: $this->input->get('status', TRUE),
            'search'     => $search_val,
        ];

        $records_total    = $this->User_model->get_datatables_count_all($this->school_id);
        $records_filtered = $this->User_model->count_filtered($filters);
        $user_list        = $this->User_model->get_datatables_data($filters, $length, $start, $order_col_idx, $order_dir);

        $data = [];
        foreach ($user_list as $u) {
            $initials = school_initials($u->name);
            $statusBadge = school_status_badge($u->status);

            $userCol = '<div class="flex items-center gap-2.5">' .
                '<div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center text-[11px] font-semibold shrink-0">' . html_escape($initials) . '</div>' .
                '<div>' .
                    '<div class="font-medium text-on-surface">' . html_escape($u->name) . '</div>' .
                    '<div class="text-[12px] text-on-surface-variant">' . html_escape($u->user_type ?: 'User') . '</div>' .
                '</div>' .
            '</div>';

            $displayRole = !empty($u->designation_name) ? $u->designation_name : ($u->role_name ?: 'No Role');
            $roleCol = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-primary-fixed/30 text-primary">' . html_escape($displayRole) . '</span>';

            $contactCol = '<div>' .
                '<div class="text-on-surface">' . html_escape($u->email ?: '—') . '</div>' .
                '<div class="text-[12px] text-on-surface-variant">' . html_escape($u->phone ?: '—') . '</div>' .
            '</div>';

            $actionsCol = '<div class="flex items-center justify-end gap-1.5">' .
                '<a href="' . site_url('users/details/' . $u->user_id) . '" title="View Details" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">visibility</span></a>' .
                '<a href="' . site_url('users/edit/' . $u->user_id) . '" title="Edit User" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span></a>' .
            '</div>';

            $data[] = [
                $userCol,
                html_escape($u->username),
                $roleCol,
                $contactCol,
                $statusBadge,
                school_date($u->created_at),
                $actionsCol
            ];
        }

        $output = [
            "draw"            => $draw,
            "recordsTotal"    => $records_total,
            "recordsFiltered" => $records_filtered,
            "data"            => $data,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    /**
     * 3. Add User Form & Creation
     */
    public function create()
    {
        $this->require_permission('users.create');

        if ($this->input->method() === 'post') {
            $name           = trim((string)$this->input->post('name', TRUE));
            $username       = trim((string)$this->input->post('username', TRUE));
            $email          = trim((string)$this->input->post('email', TRUE));
            $phone          = trim((string)$this->input->post('phone', TRUE));
            $user_type      = trim((string)$this->input->post('user_type', TRUE));
            $designation_id = (int)$this->input->post('designation_id', TRUE);
            $role_id        = (int)$this->input->post('role_id', TRUE);
            $password       = (string)$this->input->post('password');
            $staff_id       = $this->input->post('staff_id') ? (int)$this->input->post('staff_id') : NULL;
            $student_id     = $this->input->post('student_id') ? (int)$this->input->post('student_id') : NULL;

            // 1. Basic required validation
            if ($name === '' || $username === '' || $email === '' || $password === '' || (empty($role_id) && empty($designation_id)) || $user_type === '') {
                $this->session->set_flashdata('error', 'Please fill in all required fields.');
                redirect('users/create');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->session->set_flashdata('error', 'Please provide a valid email address.');
                redirect('users/create');
                return;
            }

            if (strlen($password) < 6) {
                $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
                redirect('users/create');
                return;
            }

            // 2. Check duplicate username or email among active users (global uniqueness)
            $username_exists = $this->User_model->is_username_exists($username);
            $email_exists    = $this->User_model->is_email_exists($email);

            if ($username_exists && $email_exists) {
                $this->session->set_flashdata('error', 'Username and email are already registered.');
                redirect('users/create');
                return;
            } elseif ($username_exists) {
                $this->session->set_flashdata('error', 'Username is already registered.');
                redirect('users/create');
                return;
            } elseif ($email_exists) {
                $this->session->set_flashdata('error', 'Email is already registered.');
                redirect('users/create');
                return;
            }

            // 3. Create user — school_id is injected server-side by User_model::insert()
            // NEVER trust school_id from POST/GET.
            $user_id = $this->User_model->insert([
                'name'           => $name,
                'username'       => $username,
                'email'          => $email,
                'phone'          => $phone,
                'user_type'      => $user_type,
                'designation_id' => $designation_id ?: NULL,
                'role_id'        => $role_id ?: NULL,
                'password'       => $password,
                'staff_id'       => $staff_id,
                'student_id'     => $student_id,
                'status'         => 'Active',
                'is_deleted'     => 'n',
                // school_id will be auto-injected by User_model::insert() from get_current_school_id()
            ]);

            // If Parent, link child if provided
            if ($user_type === 'Parent' && $student_id) {
                $this->User_model->link_parent_student($user_id, $student_id, 'Parent');
            }

            $this->rbac->log_audit('User Created', 'User', $user_id, NULL, ['username' => $username, 'designation_id' => $designation_id, 'role_id' => $role_id], "Created user account: {$username}");

            $this->session->set_flashdata('success', "User account '{$username}' created successfully.");
            redirect('users/list');
            return;
        }

        // School-scoped dropdowns: only show roles, designations, staff, and students from current school
        $roles = $this->Role_model->get_all(false, $this->school_id);
        $designations = $this->Designation_model->get_all(['school_id' => $this->school_id]);
        $staff = $this->db
            ->where('status', 1)
            ->where('school_id', $this->school_id)
            ->get('tbl_staff')->result();
        $students = $this->db
            ->select('student_id, first_name, last_name, admission_number')
            ->where('status', 'Active')
            ->where('school_id', $this->school_id)
            ->get('tbl_students')->result();

        $this->render('pages/users/create', [
            'title'        => 'Add New User',
            'page_key'     => 'user-create',
            'roles'        => $roles,
            'designations' => $designations,
            'staff'        => $staff,
            'students'     => $students,
        ]);
    }

    /**
     * 4. User Details & Effective Permissions View
     */
    public function details($id = NULL)
    {
        $this->require_permission('users.view');

        if ($id === NULL) {
            $id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : (int)($this->session->userdata('user_id') ?: 1);
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $first = $this->User_model->get_all([], 1);
            if (!empty($first)) {
                $user = $first[0];
                $id = $user->user_id;
            } else {
                $this->session->set_flashdata('error', 'User not found.');
                redirect('users/list');
                return;
            }
        }

        // Multi-school IDOR protection: ensure user belongs to current school
        if (!$this->_verify_user_school($id)) return;

        $effective_perms   = $this->User_model->get_effective_permissions($id);
        $role_perms        = $this->Role_model->get_role_permission_keys($user->role_id);
        $designation_perms = !empty($user->designation_id) ? $this->Designation_model->get_designation_permission_keys($user->designation_id, $user->school_id) : [];
        $overrides         = $this->User_model->get_user_overrides($id);
        $children          = ($user->user_type === 'Parent') ? $this->User_model->get_parent_children($id) : [];

        $this->render('pages/users/details', [
            'title'             => "User Profile: {$user->name}",
            'page_key'          => 'user-details',
            'user'              => $user,
            'effective_perms'   => $effective_perms,
            'role_perms'        => $role_perms,
            'designation_perms' => $designation_perms,
            'overrides'         => $overrides,
            'children'          => $children,
        ]);
    }

    /**
     * 5. Edit User Profile & Role
     */
    public function edit($id)
    {
        $this->require_permission('users.edit');

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found.');
            redirect('users/list');
            return;
        }

        // Multi-school IDOR protection
        if (!$this->_verify_user_school($id)) return;

        if ($this->input->method() === 'post') {
            $name            = trim($this->input->post('name', TRUE));
            $phone           = trim($this->input->post('phone', TRUE));
            $new_designation = (int)$this->input->post('designation_id', TRUE);
            $new_role        = (int)$this->input->post('role_id', TRUE);
            $user_type       = $this->input->post('user_type', TRUE);

            $prev_role  = $user->role_id;
            $prev_desig = $user->designation_id ?? null;

            $data = [
                'name'           => $name,
                'phone'          => $phone,
                'designation_id' => $new_designation ?: NULL,
                'role_id'        => $new_role ?: NULL,
                'user_type'      => $user_type,
            ];

            $this->User_model->update($id, $data);

            if ($prev_role != $new_role || $prev_desig != $new_designation) {
                $this->rbac->log_audit('Role/Designation Changed', 'User', $id, "Desig #{$prev_desig} / Role #{$prev_role}", "Desig #{$new_designation} / Role #{$new_role}", "Updated role/designation for user: {$user->username}");
            }

            $this->session->set_flashdata('success', 'User updated successfully.');
            redirect('users/details/' . $id);
            return;
        }

        $roles = $this->Role_model->get_all(false, $user->school_id);
        $designations = $this->Designation_model->get_all(['school_id' => $user->school_id]);
        $this->render('pages/users/edit', [
            'title'        => "Edit User: {$user->name}",
            'page_key'     => 'users',
            'user'         => $user,
            'roles'        => $roles,
            'designations' => $designations,
        ]);
    }

    /**
     * 6. Toggle Status (Active / Inactive) with Last Admin Safety Guard
     */
    public function toggle_status($id)
    {
        $this->require_permission('users.delete');

        // Multi-school IDOR protection
        if (!$this->_verify_user_school($id, $this->agent->is_referral() ? $this->agent->referrer() : 'users/list')) return;

        $res = $this->User_model->toggle_status($id);
        if ($res === 'LAST_ADMIN') {
            $this->session->set_flashdata('error', 'Safety Restriction: Cannot deactivate the last remaining active Administrator.');
        } elseif ($res) {
            $this->session->set_flashdata('success', 'User account status updated.');
            $this->rbac->log_audit('Status Toggled', 'User', $id, NULL, NULL, "Toggled active/inactive status");
        } else {
            $this->session->set_flashdata('error', 'Failed to update user status.');
        }
        redirect($this->agent->is_referral() ? $this->agent->referrer() : 'users/list');
    }

    /**
     * Soft-Delete User Account with Last Admin Safety Guard
     */
    public function delete($id)
    {
        $this->require_permission('users.delete');

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found.');
            redirect('users/list');
            return;
        }

        // Multi-school IDOR protection
        if (!$this->_verify_user_school($id)) return;

        // Safety Guard: Cannot delete the last active administrator
        if ($user->user_type === 'Admin' && $user->status === 'Active') {
            if ($this->User_model->count_active_admins() <= 1) {
                $this->session->set_flashdata('error', 'Safety Restriction: Cannot delete the last remaining active Administrator.');
                redirect('users/list');
                return;
            }
        }

        $this->User_model->soft_delete($id);
        $this->rbac->log_audit('User Deleted', 'User', $id, NULL, NULL, "Soft deleted user account: {$user->username}");
        $this->session->set_flashdata('success', "User account '{$user->username}' deleted successfully.");
        redirect('users/list');
    }

    /**
     * 7. Unlock Locked Account
     */
    public function unlock($id)
    {
        $this->require_permission('users.edit');

        $this->User_model->unlock_user($id);
        $this->rbac->log_audit('Account Unlocked', 'User', $id, 'Locked', 'Active', "Account unlocked by admin");
        $this->session->set_flashdata('success', 'Account unlocked successfully.');
        redirect($this->agent->is_referral() ? $this->agent->referrer() : 'users/list');
    }

    /**
     * 8. Reset Password
     */
    public function reset_password($id)
    {
        $this->require_permission('users.edit');

        // Multi-school IDOR protection
        if (!$this->_verify_user_school($id, 'users/list')) return;

        if ($this->input->method() === 'post') {
            $pwd = $this->input->post('password');
            if (strlen($pwd) < 6) {
                $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
                redirect('users/details/' . $id);
                return;
            }

            $this->User_model->update($id, ['password' => $pwd]);
            $this->rbac->log_audit('Password Reset', 'User', $id, NULL, NULL, "Admin reset password");
            $this->session->set_flashdata('success', 'Password reset successfully.');
            redirect('users/details/' . $id);
            return;
        }
    }

    /**
     * 9. Roles Management
     */
    public function roles()
    {
        $this->require_permission('users.manage_roles');

        if ($this->input->method() === 'post') {
            $name = trim($this->input->post('role_name', TRUE));
            $code = strtoupper(trim($this->input->post('role_code', TRUE)));
            $type = $this->input->post('user_type', TRUE);
            $desc = trim($this->input->post('description', TRUE));

            $dup = $this->Role_model->get_by_code($code);
            if ($dup) {
                $this->session->set_flashdata('error', "Role code '{$code}' already exists.");
                redirect('users/roles');
                return;
            }

            $role_id = $this->Role_model->insert([
                'role_name'   => $name,
                'role_code'   => $code,
                'user_type'   => $type,
                'description' => $desc,
                'is_system'   => 0,
                'status'      => 'Active'
            ]);

            $this->rbac->log_audit('Role Created', 'Role', $role_id, NULL, $name, "Created custom role: {$name} ({$code})");
            $this->session->set_flashdata('success', "Role '{$name}' created successfully.");
            redirect('users/roles');
            return;
        }

        $roles = $this->Role_model->get_roles_with_counts();

        $this->render('pages/users/roles', [
            'title'    => 'Role Management',
            'page_key' => 'user-roles',
            'roles'    => $roles,
        ]);
    }

    /**
     * 10. Role Permissions Matrix
     */
    public function role_permissions($role_id = NULL)
    {
        $this->require_permission('users.manage_roles');

        $roles = $this->Role_model->get_all(false, $this->school_id);
        if (empty($roles)) {
            $this->session->set_flashdata('error', 'No roles configured for this school.');
            redirect('users/roles');
            return;
        }

        if ($role_id === NULL) {
            $role_id = $this->input->get('role_id') ? (int)$this->input->get('role_id') : (int)$roles[0]->role_id;
        }

        $role = $this->Role_model->get_by_id($role_id, $this->school_id);
        if (!$role) {
            $role = $roles[0];
            $role_id = $role->role_id;
        }

        if ($this->input->method() === 'post') {
            $selected_perms = $this->input->post('permissions');
            $selected_perms = is_array($selected_perms) ? $selected_perms : [];

            $this->Role_model->set_role_permissions($role_id, $selected_perms, $this->school_id);
            $this->rbac->log_audit('Permissions Updated', 'Role', $role_id, NULL, count($selected_perms) . ' permissions', "Updated permission matrix for role: {$role->role_name}");

            $this->session->set_flashdata('success', "Permissions updated for role '{$role->role_name}'.");
            redirect('users/role_permissions/' . $role_id);
            return;
        }

        $grouped_permissions = $this->Permission_model->get_grouped_by_module();
        $active_perm_ids     = $this->Role_model->get_role_permission_ids($role_id, $this->school_id);

        $this->render('pages/users/role_permissions', [
            'title'               => "Role Permissions: {$role->role_name}",
            'page_key'            => 'user-role-permissions',
            'role'                => $role,
            'roles'               => $roles,
            'grouped_permissions' => $grouped_permissions,
            'active_perm_ids'     => $active_perm_ids,
        ]);
    }

    /**
     * 10b. Designation Management
     */
    public function designations()
    {
        $this->require_permission('users.manage_roles');

        $school_id = $this->school_id;
        $designations = $this->Designation_model->get_designations_with_counts($school_id);

        $this->render('pages/users/designations', [
            'title'        => 'Designation Management',
            'page_key'     => 'user-designations',
            'designations' => $designations,
        ]);
    }

    /**
     * 10c. Create Custom Designation
     */
    public function add_designation()
    {
        $this->require_permission('users.manage_roles');

        if ($this->input->method() === 'post') {
            $name        = trim((string)$this->input->post('designation_name', TRUE));
            $category    = trim((string)$this->input->post('category', TRUE)) ?: 'Support';
            $description = trim((string)$this->input->post('description', TRUE));

            $valid_categories = ['Administration', 'Teaching', 'Finance', 'Support'];
            if (!in_array($category, $valid_categories)) {
                $category = 'Support';
            }

            if ($name === '') {
                $this->session->set_flashdata('error', 'Designation name is required.');
                redirect('users/designations');
                return;
            }

            $code = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($name)));
            $code = trim($code, '_');

            $school_id = $this->school_id;

            $existing = $this->db
                ->where('school_id', $school_id)
                ->where('designation_name', $name)
                ->where('is_deleted', 'n')
                ->get('tbl_designations')
                ->row();

            if ($existing) {
                $this->session->set_flashdata('error', "Designation '{$name}' already exists in this school.");
                redirect('users/designations');
                return;
            }

            $id = $this->Designation_model->insert([
                'school_id'        => $school_id,
                'designation_name' => $name,
                'designation_code' => $code,
                'category'         => $category,
                'description'      => $description,
                'is_system'        => 0,
                'status'           => 1,
            ]);

            $this->rbac->log_audit('Designation Created', 'Designation', $id, NULL, $name, "Created designation: {$name} ({$code})");
            $this->session->set_flashdata('success', "Designation '{$name}' created successfully.");
            redirect('users/designations');
            return;
        }

        redirect('users/designations');
    }

    /**
     * 10d. Designation Permissions Matrix Editor
     */
    public function designation_permissions($designation_id = NULL)
    {
        $this->require_permission('users.manage_roles');

        $school_id = $this->school_id;
        $designations = $this->Designation_model->get_all(['school_id' => $school_id]);
        if (empty($designations)) {
            $this->session->set_flashdata('error', 'No designations configured for this school.');
            redirect('users/designations');
            return;
        }

        if ($designation_id === NULL) {
            $first_id = $designations[0]->designation_id ?? $designations[0]->id;
            $designation_id = $this->input->get('designation_id') ? (int)$this->input->get('designation_id') : (int)$first_id;
        } else {
            $designation_id = (int)$designation_id;
        }

        $designation = $this->Designation_model->get_by_id($designation_id, $school_id);
        if (!$designation) {
            $this->session->set_flashdata('error', 'Designation not found or access denied.');
            redirect('users/designations');
            return;
        }

        if ($this->input->method() === 'post') {
            $selected_perms = $this->input->post('permissions');
            $selected_perms = is_array($selected_perms) ? $selected_perms : [];

            $this->Designation_model->set_designation_permissions($designation_id, $selected_perms, $school_id);
            $this->rbac->refresh_permissions();
            $this->rbac->log_audit('Permissions Updated', 'Designation', $designation_id, NULL, count($selected_perms) . ' permissions', "Updated permission matrix for designation: {$designation->designation_name}");

            $this->session->set_flashdata('success', "Permissions updated successfully for designation '{$designation->designation_name}'.");
            redirect('users/designation_permissions/' . $designation_id);
            return;
        }

        $this->load->library('Navigation');
        $permission_tree = $this->navigation->get_permission_matrix_tree();
        $active_perm_ids = $this->Designation_model->get_designation_permission_ids($designation_id, $school_id);

        $this->render('pages/users/designation_permissions', [
            'title'               => "Designation Permissions: {$designation->designation_name}",
            'page_key'            => 'user-designations',
            'designation'         => $designation,
            'designations'        => $designations,
            'permission_tree'     => $permission_tree,
            'active_perm_ids'     => $active_perm_ids,
        ]);
    }

    /**
     * 11. Permissions Catalog
     */
    public function permissions()
    {
        $this->require_permission('users.manage_roles');

        $grouped_permissions = $this->Permission_model->get_grouped_by_module();

        $this->render('pages/users/permissions', [
            'title'               => 'Permissions Catalog',
            'page_key'            => 'user-permissions',
            'grouped_permissions' => $grouped_permissions,
        ]);
    }

    /**
     * 12. User Specific Permission Overrides
     */
    public function user_permissions($user_id = NULL)
    {
        $this->require_permission('users.manage_roles');

        if ($user_id === NULL) {
            $user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : (int)($this->session->userdata('user_id') ?: 1);
        }

        $user = $this->User_model->get_by_id($user_id);
        if (!$user) {
            $first = $this->User_model->get_all([], 1);
            if (!empty($first)) {
                $user = $first[0];
                $user_id = $user->user_id;
            } else {
                $this->session->set_flashdata('error', 'User not found.');
                redirect('users/list');
                return;
            }
        }

        // Multi-school IDOR protection
        if (!$this->_verify_user_school($user_id)) return;

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            $pid    = (int)$this->input->post('permission_id');
            $type   = $this->input->post('override_type');

            if ($action === 'set') {
                $this->User_model->set_user_override($user_id, $pid, $type);
                $this->rbac->log_audit('User Override Set', 'User', $user_id, NULL, "{$type} Perm #{$pid}", "Set override for user: {$user->username}");
            } elseif ($action === 'remove') {
                $this->User_model->remove_user_override($user_id, $pid);
                $this->rbac->log_audit('User Override Removed', 'User', $user_id, NULL, "Removed Perm #{$pid}", "Reset override for user: {$user->username}");
            }

            $this->session->set_flashdata('success', 'User permission override updated.');
            redirect('users/user_permissions/' . $user_id);
            return;
        }

        $all_permissions = $this->Permission_model->get_all();
        $role_perm_ids   = $this->Role_model->get_role_permission_ids($user->role_id);
        $overrides       = $this->User_model->get_user_overrides($user_id);

        $override_map = [];
        foreach ($overrides as $ov) {
            $override_map[$ov->permission_id] = $ov->override_type;
        }

        $this->render('pages/users/user_permissions', [
            'title'           => "User Permissions: {$user->name}",
            'page_key'        => 'users',
            'user'            => $user,
            'all_permissions' => $all_permissions,
            'role_perm_ids'   => $role_perm_ids,
            'override_map'    => $override_map,
        ]);
    }

    /**
     * 13. Filtered Account Views: Parents, Students, Teachers, Staff
     */
    public function parents()
    {
        $this->require_permission('users.view');
        $users = $this->User_model->get_all(['user_type' => 'Parent']);
        $this->render('pages/users/parents', [
            'title'    => 'Parent Accounts',
            'page_key' => 'user-parents',
            'users'    => $users,
        ]);
    }

    public function students()
    {
        $this->require_permission('users.view');
        $users = $this->User_model->get_all(['user_type' => 'Student']);
        $this->render('pages/users/students', [
            'title'    => 'Student Accounts',
            'page_key' => 'user-students',
            'users'    => $users,
        ]);
    }

    public function teachers()
    {
        $this->require_permission('users.view');
        $users = $this->User_model->get_all(['user_type' => 'Teacher']);
        $this->render('pages/users/teachers', [
            'title'    => 'Teacher Accounts',
            'page_key' => 'user-teachers',
            'users'    => $users,
        ]);
    }

    public function staff()
    {
        $this->require_permission('users.view');
        $users = $this->User_model->get_all(['user_type' => 'Staff']);
        $this->render('pages/users/staff', [
            'title'    => 'Staff Accounts',
            'page_key' => 'user-staff',
            'users'    => $users,
        ]);
    }

    /**
     * 14. Login Activity Ledger
     */
    public function login_activity()
    {
        $this->require_permission('users.view');
        $activity = $this->User_model->get_login_activity(150);
        $this->render('pages/users/login_activity', [
            'title'    => 'Login Activity & Audits',
            'page_key' => 'user-login-activity',
            'activity' => $activity,
        ]);
    }

    /**
     * 15. Security Settings
     */
    public function security_settings()
    {
        $this->require_permission('users.manage_roles');

        if ($this->input->method() === 'post') {
            $data = [
                'max_failed_attempts'       => (int)$this->input->post('max_failed_attempts'),
                'lockout_duration_minutes'  => (int)$this->input->post('lockout_duration_minutes'),
                'session_timeout_minutes'   => (int)$this->input->post('session_timeout_minutes'),
                'password_min_length'       => (int)$this->input->post('password_min_length'),
                'require_special_chars'     => $this->input->post('require_special_chars') ? 1 : 0,
                'require_numbers'           => $this->input->post('require_numbers') ? 1 : 0,
                'password_expiry_days'      => (int)$this->input->post('password_expiry_days'),
                'allow_concurrent_sessions' => $this->input->post('allow_concurrent_sessions') ? 1 : 0,
            ];

            $this->Security_setting_model->update_settings($data);
            $this->rbac->log_audit('Security Updated', 'Security', 1, NULL, json_encode($data), "Updated global security policies");

            $this->session->set_flashdata('success', 'Security settings saved successfully.');
            redirect('users/security_settings');
            return;
        }

        $settings = $this->Security_setting_model->get_settings();
        $this->render('pages/users/security_settings', [
            'title'    => 'Security & Password Policies',
            'page_key' => 'user-security-settings',
            'settings' => $settings,
        ]);
    }

    /**
     * 16. Permission Audit Logs
     */
    public function audit_logs()
    {
        $this->require_permission('users.view');
        $logs = $this->User_model->get_audit_logs(150);
        $this->render('pages/users/audit_logs', [
            'title'    => 'Permission Audit Logs',
            'page_key' => 'user-audit-logs',
            'logs'     => $logs,
        ]);
    }

    /**
     * 17. Self Change Password (Accessible to all authenticated users)
     */
    public function change_password()
    {
        // Enforce user is logged in
        $user_id = (int)$this->session->userdata('user_id');
        if (!$user_id && !empty($this->current_user->user_id)) {
            $user_id = (int)$this->current_user->user_id;
        }

        if (!$user_id) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'    => false,
                        'message'   => 'Session expired. Please log in again.',
                        'csrf_hash' => $this->security->get_csrf_hash()
                    ]));
                return;
            }
            redirect('auth/login');
            return;
        }

        // Handle POST request (AJAX or standard form submission)
        if ($this->input->method() === 'post') {
            $current_password = (string)$this->input->post('current_password');
            $new_password     = (string)$this->input->post('new_password');
            $confirm_password = (string)$this->input->post('confirm_password');

            // 1. Required fields
            if ($current_password === '') {
                $this->_change_password_response(false, 'Current password is required.');
                return;
            }
            if ($new_password === '') {
                $this->_change_password_response(false, 'New password is required.');
                return;
            }
            if ($confirm_password === '') {
                $this->_change_password_response(false, 'Please confirm your new password.');
                return;
            }

            // 2. Passwords must match
            if ($new_password !== $confirm_password) {
                $this->_change_password_response(false, 'New password and confirm password do not match.');
                return;
            }

            // 3. New password cannot be the same as the current password
            if ($current_password === $new_password) {
                $this->_change_password_response(false, 'New password cannot be the same as the current password.');
                return;
            }

            // 4. Verify current password against database
            $school_id = $this->rbac->is_super_admin() ? NULL : $this->school_id;
            if (!$this->User_model->verify_current_password($user_id, $current_password, $school_id)) {
                $this->_change_password_response(false, 'Current password is incorrect.');
                return;
            }

            // 5. Password complexity validation from system security settings
            $sec_settings = $this->Security_setting_model->get_settings($this->school_id);
            $min_len = !empty($sec_settings->password_min_length) ? (int)$sec_settings->password_min_length : 8;
            if ($min_len < 6) {
                $min_len = 6;
            }

            if (strlen($new_password) < $min_len) {
                $this->_change_password_response(false, "New password must be at least {$min_len} characters long.");
                return;
            }

            if (!empty($sec_settings->require_numbers) && !preg_match('/[0-9]/', $new_password)) {
                $this->_change_password_response(false, 'New password must contain at least one number (0-9).');
                return;
            }

            if (!empty($sec_settings->require_special_chars) && !preg_match('/[^a-zA-Z0-9]/', $new_password)) {
                $this->_change_password_response(false, 'New password must contain at least one special character (e.g. !@#$%^&*).');
                return;
            }

            // 6. Update password securely in database using bcrypt
            $updated = $this->User_model->change_user_password($user_id, $new_password, $school_id);
            if (!$updated) {
                $this->_change_password_response(false, 'Failed to update password. Please try again.');
                return;
            }

            // 7. Regenerate session ID to prevent session fixation
            $this->session->sess_regenerate(false);

            // 8. Log audit entry (NEVER log the actual password)
            $this->rbac->log_audit('Password Changed', 'User', $user_id, NULL, NULL, 'User successfully changed their password');

            $this->_change_password_response(true, 'Password changed successfully.');
            return;
        }

        // GET request: render standalone page
        $sec_settings = $this->Security_setting_model->get_settings($this->school_id);
        $this->render('pages/users/change_password', [
            'title'    => 'Change Password',
            'page_key' => 'change-password',
            'settings' => $sec_settings,
        ]);
    }

    private function _change_password_response($success, $message)
    {
        $csrf_hash = $this->security->get_csrf_hash();
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_status_header($success ? 200 : 422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'    => $success,
                    'message'   => $message,
                    'csrf_hash' => $csrf_hash
                ]));
            return;
        }

        if ($success) {
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $message);
        }
        redirect('users/change_password');
    }
}
