<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * School_model
 *
 * Central model for managing multi-school tenant records, school storage,
 * status activation/deactivation, and automatic seeding for new schools.
 */
class School_model extends CI_Model {

    protected $table = 'tbl_schools';
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all schools with optional inactive inclusion.
     */
    public function get_all($include_inactive = true)
    {
        $this->db->from($this->table)->where('is_deleted', 'n');
        if (!$include_inactive) {
            $this->db->where('status', 'Active');
        }
        return $this->db->order_by('id', 'ASC')->get()->result();
    }

    /**
     * Get all active, non-deleted schools.
     */
    public function get_active_schools()
    {
        return $this->get_all(false);
    }

    /**
     * Get the default first active, non-deleted school.
     */
    public function get_default_school()
    {
        return $this->db
            ->where('status', 'Active')
            ->where('is_deleted', 'n')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    /**
     * Get school by primary ID with caching.
     */
    public function get_by_id($id)
    {
        static $cached_schools = [];
        $id = (int)$id;
        if ($id <= 0) return null;

        if (isset($cached_schools[$id])) {
            return $cached_schools[$id];
        }

        $res = $this->db
            ->where($this->primaryKey, $id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        $cached_schools[$id] = $res;
        return $res;
    }

    /**
     * Get school by school code.
     */
    public function get_by_code($code)
    {
        return $this->db
            ->where('school_code', trim((string)$code))
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();
    }

    /**
     * Check if a school code exists (excluding current ID).
     */
    public function is_code_exists($code, $exclude_id = null)
    {
        $this->db->from($this->table)
            ->where('school_code', trim((string)$code))
            ->where('is_deleted', 'n');

        if (!empty($exclude_id)) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        return $this->db->count_all_results() > 0;
    }

    /**
     * Auto-generate a clean, unique school code (e.g. NGPM001) per Requirement §2.
     * Extracts uppercase initials or prefix from school name and appends a 3-digit sequence.
     */
    public function generate_school_code($school_name = '')
    {
        $prefix = '';
        $clean_name = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', (string)$school_name));
        $words = preg_split('/\s+/', $clean_name, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) >= 2) {
            foreach ($words as $w) {
                $prefix .= strtoupper(substr($w, 0, 1));
                if (strlen($prefix) >= 4) break;
            }
        } elseif (count($words) === 1 && strlen($words[0]) >= 3) {
            $prefix = strtoupper(substr($words[0], 0, 4));
        }

        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'SCH');
        }

        // Find existing codes with this prefix
        $existing = $this->db
            ->select('school_code')
            ->like('school_code', $prefix, 'after')
            ->get($this->table)
            ->result();

        $max_num = 0;
        foreach ($existing as $row) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $row->school_code, $matches)) {
                $num = (int)$matches[1];
                if ($num > $max_num) {
                    $max_num = $num;
                }
            }
        }

        $next_num = $max_num + 1;
        return sprintf('%s%03d', $prefix, $next_num);
    }

    /**
     * Insert a new school, create default school settings row,
     * seed default standard roles (including School Admin), and initialize active academic year.
     */
    public function insert(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_deleted'] = 'n';
        $data['status']     = !empty($data['status']) ? $data['status'] : 'Active';

        $this->db->insert($this->table, $data);
        $school_id = (int)$this->db->insert_id();

        if ($school_id > 0) {
            // 1. Create a matching tbl_school_settings record for this school
            $this->db->insert('tbl_school_settings', [
                'school_id'        => $school_id,
                'school_name'      => $data['school_name'],
                'school_code'      => $data['school_code'],
                'established_year' => $data['established_year'] ?? date('Y'),
                'principal_name'   => $data['principal_name'] ?? '',
                'phone'            => $data['phone'] ?? '',
                'email'            => $data['email'] ?? '',
                'website'          => $data['website'] ?? '',
                'logo'             => $data['logo'] ?? '',
                'address'          => $data['address'] ?? '',
                'description'      => 'School registered on ' . date('Y-m-d'),
                'updated_at'       => date('Y-m-d H:i:s'),
                'is_deleted'       => 'n'
            ]);

            // 2. Seed standard school-specific roles for this school
            $this->seed_default_school_roles($school_id);

            // 3. Seed initial default academic year for this school
            $curr_year = (int)date('Y');
            $next_year = $curr_year + 1;
            $this->seed_default_academic_year($school_id, "{$curr_year}-{$next_year}", "{$curr_year}-06-01", "{$next_year}-03-31");

            // 4. Create default storage configuration for this school
            $this->db->insert('tbl_school_storage_configs', [
                'school_id'        => $school_id,
                'provider'         => 'local',
                'credentials_json' => null,
                'folder_id'        => 'uploads/schools/' . $school_id,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            // 5. Seed default designations dynamically from database templates
            if (!isset($this->Designation_model)) {
                $this->load->model('Designation_model');
            }
            $this->Designation_model->initialize_school_designations($school_id);


            // 7. Seed default exam types dynamically from database templates
            if (!isset($this->Exam_type_model)) {
                $this->load->model('Exam_type_model');
            }
            $this->Exam_type_model->initialize_school_exam_types($school_id);
        }

        return $school_id;
    }

    /**
     * Atomically create school, settings, roles, permissions, storage, and School Admin user.
     * Everything executes within a single database transaction. Rollback on any failure.
     *
     * @param  array $school_data
     * @param  array $admin_data
     * @param  array $admin_permissions
     * @param  array $storage_data
     * @return array|false ['school_id' => ..., 'admin_user_id' => ..., 'admin_role_id' => ...] or FALSE on error
     */
    public function create_school_with_admin(array $school_data, array $admin_data, array $admin_permissions = [], array $storage_data = [], $permission_mode = 'all')
    {
        $this->db->trans_begin();

        try {
            // 1. Insert School
            $school_data['created_at']      = date('Y-m-d H:i:s');
            $school_data['is_deleted']      = 'n';
            $school_data['status']          = !empty($school_data['status']) ? $school_data['status'] : 'Active';
            $school_data['permission_mode'] = in_array($permission_mode, ['all', 'custom'], true) ? $permission_mode : 'all';

            $this->db->insert($this->table, $school_data);
            $school_id = (int)$this->db->insert_id();

            if ($school_id <= 0) {
                throw new Exception("Failed to insert school record.");
            }

            // 2. Insert Settings
            $this->db->insert('tbl_school_settings', [
                'school_id'        => $school_id,
                'school_name'      => $school_data['school_name'],
                'school_code'      => $school_data['school_code'],
                'established_year' => $school_data['established_year'] ?? date('Y'),
                'principal_name'   => $school_data['principal_name'] ?? '',
                'phone'            => $school_data['phone'] ?? '',
                'email'            => $school_data['email'] ?? '',
                'website'          => $school_data['website'] ?? '',
                'logo'             => $school_data['logo'] ?? '',
                'address'          => $school_data['address'] ?? '',
                'description'      => 'School registered on ' . date('Y-m-d'),
                'updated_at'       => date('Y-m-d H:i:s'),
                'is_deleted'       => 'n'
            ]);

            // Determine target permissions based on permission_mode
            if ($permission_mode === 'custom') {
                $target_perm_ids = array_values(array_unique(array_filter(array_map('intval', $admin_permissions))));
            } else {
                $all_perms = $this->db->select('permission_id')->where('is_deleted', 'n')->get('tbl_permissions')->result();
                $target_perm_ids = array_values(array_unique(array_map(function($p) { return (int)$p->permission_id; }, $all_perms)));
            }

            // 3. Seed default roles including School Admin + permissions
            $seeded_roles = $this->seed_default_school_roles($school_id, $target_perm_ids);
            $admin_role_id = $seeded_roles['SCHOOL_ADMIN'] ?? null;

            if (!$admin_role_id) {
                throw new Exception("Failed to seed School Admin role.");
            }

            // 3B. Seed default designations including School Admin + permissions
            if (!isset($this->Designation_model)) {
                $this->load->model('Designation_model');
            }
            $seeded_desigs = $this->Designation_model->initialize_school_designations($school_id, $target_perm_ids);
            $admin_desig_id = $seeded_desigs['SCHOOL_ADMIN'] ?? null;

            // Ensure tbl_role_permissions and tbl_designation_permissions are exactly matching target_perm_ids
            $this->db->where('role_id', $admin_role_id)->where('school_id', $school_id)->delete('tbl_role_permissions');
            foreach ($target_perm_ids as $pid) {
                $this->db->insert('tbl_role_permissions', [
                    'school_id'     => $school_id,
                    'role_id'       => $admin_role_id,
                    'permission_id' => (int)$pid,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'is_deleted'    => 'n'
                ]);
            }
            if ($admin_desig_id > 0) {
                $this->db->where('designation_id', $admin_desig_id)->where('school_id', $school_id)->delete('tbl_designation_permissions');
                foreach ($target_perm_ids as $pid) {
                    $this->db->insert('tbl_designation_permissions', [
                        'school_id'      => $school_id,
                        'designation_id' => $admin_desig_id,
                        'permission_id'  => (int)$pid,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'is_deleted'     => 'n'
                    ]);
                }
            }


            // 3D. Seed default exam types for the school dynamically from database
            if (!isset($this->Exam_type_model)) {
                $this->load->model('Exam_type_model');
            }
            $this->Exam_type_model->initialize_school_exam_types($school_id);

            // 3E. Seed default Chart of Accounts, Expense Types, and Finance settings
            if (!isset($this->Finance_model)) {
                $this->load->model('Finance_model');
            }
            $this->Finance_model->initialize_school_finance_defaults($school_id);

            // 4. Seed academic year
            $curr_year = (int)date('Y');
            $next_year = $curr_year + 1;
            $this->seed_default_academic_year($school_id, "{$curr_year}-{$next_year}", "{$curr_year}-06-01", "{$next_year}-03-31");

            // 5. Seed storage config
            $provider = $storage_data['provider'] ?? 'local';
            $this->db->insert('tbl_school_storage_configs', [
                'school_id'        => $school_id,
                'provider'         => $provider,
                'credentials_json' => !empty($storage_data['credentials_json']) ? $storage_data['credentials_json'] : null,
                'folder_id'        => !empty($storage_data['folder_id']) ? $storage_data['folder_id'] : ('uploads/schools/' . $school_id),
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            // 6. Create School Admin user account
            $admin_user = [
                'school_id'      => $school_id,
                'role_id'        => $admin_role_id,
                'designation_id' => $admin_desig_id ?: $admin_role_id,
                'name'           => trim($admin_data['name']),
                'username'       => trim($admin_data['username']),
                'password'       => password_hash($admin_data['password'], PASSWORD_BCRYPT),
                'email'          => trim($admin_data['email']),
                'phone'          => !empty($admin_data['phone']) ? trim($admin_data['phone']) : null,
                'user_type'      => 'Admin',
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'is_deleted'     => 'n'
            ];
            $this->db->insert('tbl_users', $admin_user);
            $admin_user_id = (int)$this->db->insert_id();

            if ($admin_user_id <= 0) {
                throw new Exception("Failed to create School Admin user account.");
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();
            return [
                'school_id'     => $school_id,
                'admin_user_id' => $admin_user_id,
                'admin_role_id' => $admin_role_id
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'create_school_with_admin failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update school metadata.
     */
    public function update($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where($this->primaryKey, (int)$id)->update($this->table, $data);

        // Also sync basic details into tbl_school_settings
        $sync_data = array_intersect_key($data, array_flip(['school_name', 'school_code', 'phone', 'email', 'website', 'principal_name', 'logo', 'address', 'established_year']));
        if (!empty($sync_data)) {
            $sync_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('school_id', (int)$id)->update('tbl_school_settings', $sync_data);
        }

        return true;
    }

    /**
     * Toggle school Active / Inactive status.
     */
    public function toggle_status($id)
    {
        $school = $this->get_by_id($id);
        if (!$school) return false;

        $new_status = ($school->status === 'Active') ? 'Inactive' : 'Active';
        return $this->update($id, ['status' => $new_status]);
    }

    /**
     * Soft delete school.
     */
    public function soft_delete($id)
    {
        return $this->update($id, [
            'is_deleted' => 'y',
            'status'     => 'Inactive'
        ]);
    }

    /**
     * Seed standard default roles for a newly created school
     * (including School Admin, Principal, Teacher, Accountant, Staff, Parent, Student).
     *
     * @param  int   $school_id
     * @param  array $admin_permissions  Optional array of permission IDs for the School Admin
     * @return array Map of role_code => role_id
     */
    public function seed_default_school_roles($school_id, array $admin_permissions = [])
    {
        // 1. Fetch template roles dynamically from database (School 1 or active roles where role_code != 'SUPER_ADMIN')
        $templates = $this->db
            ->where('school_id', 1)
            ->where('role_code !=', 'SUPER_ADMIN')
            ->where('is_deleted', 'n')
            ->get('tbl_roles')
            ->result_array();

        if (empty($templates)) {
            $templates = $this->db
                ->where('role_code !=', 'SUPER_ADMIN')
                ->where('is_deleted', 'n')
                ->group_by('role_code')
                ->get('tbl_roles')
                ->result_array();
        }

        // Standard baseline role definitions to guarantee standard roles (especially SCHOOL_ADMIN) are never missed
        $standard_role_defs = [
            'SCHOOL_ADMIN'  => ['role_name' => 'School Admin', 'role_code' => 'SCHOOL_ADMIN', 'user_type' => 'Admin', 'description' => 'Campus / School Administrator', 'is_system' => 1],
            'PRINCIPAL'     => ['role_name' => 'Principal', 'role_code' => 'PRINCIPAL', 'user_type' => 'Staff', 'description' => 'School Principal', 'is_system' => 1],
            'TEACHER'       => ['role_name' => 'Teacher', 'role_code' => 'TEACHER', 'user_type' => 'Staff', 'description' => 'Teaching Faculty', 'is_system' => 1],
            'ACCOUNTANT'    => ['role_name' => 'Accountant', 'role_code' => 'ACCOUNTANT', 'user_type' => 'Staff', 'description' => 'School Accountant', 'is_system' => 1],
            'RECEPTIONIST'  => ['role_name' => 'Receptionist', 'role_code' => 'RECEPTIONIST', 'user_type' => 'Staff', 'description' => 'Front Desk / Receptionist', 'is_system' => 1],
            'LIBRARIAN'     => ['role_name' => 'Librarian', 'role_code' => 'LIBRARIAN', 'user_type' => 'Staff', 'description' => 'School Librarian', 'is_system' => 1],
            'TRANSPORT_MGR' => ['role_name' => 'Transport Manager', 'role_code' => 'TRANSPORT_MGR', 'user_type' => 'Staff', 'description' => 'Transport Incharge', 'is_system' => 1],
            'PARENT'        => ['role_name' => 'Parent', 'role_code' => 'PARENT', 'user_type' => 'Parent', 'description' => 'Student Parent / Guardian', 'is_system' => 1],
            'STUDENT'       => ['role_name' => 'Student', 'role_code' => 'STUDENT', 'user_type' => 'Student', 'description' => 'Enrolled Student', 'is_system' => 1],
        ];

        $roles_to_seed = [];
        foreach ($templates as $t) {
            $code = strtoupper(trim($t['role_code']));
            if (!empty($code)) {
                $roles_to_seed[$code] = $t;
            }
        }
        foreach ($standard_role_defs as $code => $def) {
            if (!isset($roles_to_seed[$code])) {
                $roles_to_seed[$code] = $def;
            }
        }

        $seeded_roles = [];

        foreach ($roles_to_seed as $r) {
            $role_code = strtoupper(trim($r['role_code']));
            $role_name = trim($r['role_name']);
            $is_sys    = !empty($r['is_system']) ? 1 : 0;
            $user_type = $r['user_type'] ?? 'Staff';
            $desc      = $r['description'] ?? '';

            // Check if already exists for this school
            $exists = $this->db
                ->where('school_id', (int)$school_id)
                ->where('role_code', $role_code)
                ->get('tbl_roles')
                ->row();

            if ($exists) {
                if ($exists->is_deleted === 'y') {
                    $this->db->where('role_id', (int)$exists->role_id)->update('tbl_roles', [
                        'is_deleted' => 'n',
                        'status'     => 'Active',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $seeded_roles[$role_code] = (int)$exists->role_id;

                if ($role_code === 'SCHOOL_ADMIN' && !empty($admin_permissions)) {
                    $this->db->where('school_id', (int)$school_id)->where('role_id', (int)$exists->role_id)->delete('tbl_role_permissions');
                    $perm_ids = array_values(array_unique(array_filter(array_map('intval', $admin_permissions))));
                    $inserted_pids = [];
                    foreach ($perm_ids as $pid) {
                        $pid = (int)$pid;
                        if ($pid > 0 && !isset($inserted_pids[$pid])) {
                            $inserted_pids[$pid] = true;
                            $this->db->insert('tbl_role_permissions', [
                                'school_id'     => (int)$school_id,
                                'role_id'       => (int)$exists->role_id,
                                'permission_id' => $pid,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'is_deleted'    => 'n'
                            ]);
                        }
                    }
                }
                continue;
            }

            $insert_data = [
                'school_id'   => (int)$school_id,
                'role_name'   => $role_name,
                'role_code'   => $role_code,
                'user_type'   => $user_type,
                'description' => $desc,
                'is_system'   => $is_sys,
                'status'      => 'Active',
                'created_at'  => date('Y-m-d H:i:s'),
                'is_deleted'  => 'n'
            ];

            $this->db->insert('tbl_roles', $insert_data);
            $new_role_id = (int)$this->db->insert_id();
            $seeded_roles[$role_code] = $new_role_id;

            if ($role_code === 'SCHOOL_ADMIN') {
                if (!empty($admin_permissions)) {
                    $perm_ids = array_values(array_unique(array_filter(array_map('intval', $admin_permissions))));
                } else {
                    $all_perms = $this->db->select('permission_id')->where('is_deleted', 'n')->get('tbl_permissions')->result();
                    $perm_ids = array_values(array_unique(array_map(function($p) { return (int)$p->permission_id; }, $all_perms)));
                }

                $inserted_pids = [];
                foreach ($perm_ids as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0 && !isset($inserted_pids[$pid])) {
                        $inserted_pids[$pid] = true;
                        $this->db->insert('tbl_role_permissions', [
                            'school_id'     => (int)$school_id,
                            'role_id'       => (int)$new_role_id,
                            'permission_id' => $pid,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'is_deleted'    => 'n'
                        ]);
                    }
                }
            } else {
                // Clone default permissions from School 1's corresponding role code if available
                $template_role = $this->db->where('school_id', 1)->where('role_code', $role_code)->where('is_deleted', 'n')->get('tbl_roles')->row();
                if ($template_role) {
                    $perms = $this->db->where('role_id', $template_role->role_id)->where('is_deleted', 'n')->get('tbl_role_permissions')->result();
                    $inserted_pids = [];
                    foreach ($perms as $p) {
                        $pid = (int)$p->permission_id;
                        if ($pid > 0 && !isset($inserted_pids[$pid])) {
                            $inserted_pids[$pid] = true;
                            $this->db->insert('tbl_role_permissions', [
                                'school_id'     => (int)$school_id,
                                'role_id'       => (int)$new_role_id,
                                'permission_id' => $pid,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'is_deleted'    => 'n'
                            ]);
                        }
                    }
                }
            }
        }

        return $seeded_roles;
    }

    /**
     * Seed initial academic year for a newly created school.
     */
    public function seed_default_academic_year($school_id, $year_name, $start_date, $end_date)
    {
        $exists = $this->db->where('school_id', (int)$school_id)->where('year_name', $year_name)->get('tbl_academic_years')->row();
        if (!$exists) {
            $this->db->insert('tbl_academic_years', [
                'school_id'   => (int)$school_id,
                'year_name'   => $year_name,
                'start_date'  => $start_date,
                'end_date'    => $end_date,
                'is_active'   => 1,
                'status'      => 1,
                'is_deleted'  => 'n',
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Retrieve storage configuration (Google Drive / Local / S3) for a school.
     */
    public function get_storage_config($school_id)
    {
        return $this->db
            ->where('school_id', (int)$school_id)
            ->where('is_active', 1)
            ->get('tbl_school_storage_configs')
            ->row();
    }

    /**
     * Save/update storage configuration for a school.
     */
    public function save_storage_config($school_id, array $data)
    {
        $existing = $this->get_storage_config($school_id);
        $data['school_id'] = (int)$school_id;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            return $this->db->where('id', $existing->id)->update('tbl_school_storage_configs', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['is_active']  = 1;
            return $this->db->insert('tbl_school_storage_configs', $data);
        }
    }

    /**
     * Get aggregate dashboard stats for a school.
     */
    public function get_school_stats($school_id)
    {
        $sid = (int)$school_id;
        $total_students = $this->db->where('school_id', $sid)->where('is_deleted', 'n')->count_all_results('tbl_students');
        $total_teachers = $this->db->where('school_id', $sid)->where('is_deleted', 'n')->where('status', 1)->count_all_results('tbl_staff');
        $total_classes  = $this->db->where('school_id', $sid)->where('is_deleted', 'n')->where('status', 1)->count_all_results('tbl_classes');
        $total_users    = $this->db->where('school_id', $sid)->where('is_deleted', 'n')->count_all_results('tbl_users');

        return (object)[
            'total_students' => $total_students,
            'total_teachers' => $total_teachers,
            'total_classes'  => $total_classes,
            'total_users'    => $total_users,
        ];
    }

    /**
     * Locate the primary School Admin user for a given school.
     * Searches for active users in this school assigned the SCHOOL_ADMIN role (or user_type = 'Admin').
     * Strips sensitive password hashes before returning.
     */
    public function get_school_admin($school_id)
    {
        $sid = (int)$school_id;
        if ($sid <= 0) return null;

        // Try to find user with SCHOOL_ADMIN role code first
        $admin = $this->db->select('u.user_id, u.school_id, u.role_id, u.name, u.username, u.email, u.phone, u.status, u.user_type, r.role_code, r.role_name')
            ->from('tbl_users u')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->where('u.school_id', $sid)
            ->where('r.role_code', 'SCHOOL_ADMIN')
            ->where('u.is_deleted', 'n')
            ->order_by('u.user_id', 'ASC')
            ->get()
            ->row();

        if (!$admin) {
            // Fallback to any Admin user_type for this school
            $admin = $this->db->select('u.user_id, u.school_id, u.role_id, u.name, u.username, u.email, u.phone, u.status, u.user_type, r.role_code, r.role_name')
                ->from('tbl_users u')
                ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
                ->where('u.school_id', $sid)
                ->where('u.user_type', 'Admin')
                ->where('u.is_deleted', 'n')
                ->order_by('u.user_id', 'ASC')
                ->get()
                ->row();
        }

        return $admin;
    }

    /**
     * Get permission IDs assigned to a school's role.
     */
    public function get_school_admin_permissions($school_id, $role_id)
    {
        $sid = (int)$school_id;
        $rid = (int)$role_id;
        if ($sid <= 0 || $rid <= 0) return [];

        $rows = $this->db->select('permission_id')
            ->where('school_id', $sid)
            ->where('role_id', $rid)
            ->where('is_deleted', 'n')
            ->get('tbl_role_permissions')
            ->result();

        return array_map(function($r) { return (int)$r->permission_id; }, $rows);
    }

    /**
     * Retrieve full details of a school for the edit workflow:
     * - School campus metadata
     * - Storage / Google Drive configuration
     * - Primary School Administrator account
     * - School Admin permissions & preset mode ('all' or 'custom')
     */
    public function get_school_full_details($school_id)
    {
        $sid = (int)$school_id;
        $school = $this->get_by_id($sid);
        if (!$school) return null;

        $storage = $this->get_storage_config($sid);
        $admin   = $this->get_school_admin($sid);

        // Resolve School Admin role ID dynamically
        $admin_role_id = 0;
        if ($admin && !empty($admin->role_id)) {
            $admin_role_id = (int)$admin->role_id;
        } else {
            $admin_role = $this->db->where('school_id', $sid)->where('role_code', 'SCHOOL_ADMIN')->where('is_deleted', 'n')->get('tbl_roles')->row();
            if ($admin_role) {
                $admin_role_id = (int)$admin_role->role_id;
            }
        }

        $assigned_perms = $admin_role_id > 0 ? $this->get_school_admin_permissions($sid, $admin_role_id) : [];

        // Check total active permissions in the catalog
        $total_active_perms = $this->db->where('is_deleted', 'n')->count_all_results('tbl_permissions');
        $permission_mode = !empty($school->permission_mode) ? $school->permission_mode : ((count($assigned_perms) >= $total_active_perms && $total_active_perms > 0) ? 'all' : 'custom');

        return [
            'school'          => $school,
            'storage'         => $storage,
            'admin'           => $admin,
            'permissions'     => $assigned_perms,
            'permission_mode' => $permission_mode
        ];
    }

    /**
     * Atomically update School Campus metadata, Storage config, School Admin user, and Permissions.
     * All updates are wrapped in an ACID transaction with strict school_id isolation.
     */
    public function update_school_with_admin($school_id, array $school_data, array $admin_data, array $permissions, array $storage_data, $permission_mode = 'all')
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) return false;

        $this->db->trans_begin();

        try {
            // 1. Update tbl_schools (including permission_mode)
            $school_data['updated_at'] = date('Y-m-d H:i:s');
            $school_data['permission_mode'] = in_array($permission_mode, ['all', 'custom'], true) ? $permission_mode : 'all';
            $this->db->where('id', $school_id)->update('tbl_schools', $school_data);

            // 2. Sync to tbl_school_settings
            $sync_data = array_intersect_key($school_data, array_flip(['school_name', 'school_code', 'phone', 'email', 'website', 'principal_name', 'logo', 'address', 'established_year']));
            if (!empty($sync_data)) {
                $sync_data['updated_at'] = date('Y-m-d H:i:s');
                $this->db->where('school_id', $school_id)->update('tbl_school_settings', $sync_data);
            }

            // 3. Save / Update Storage Config
            $this->save_storage_config($school_id, $storage_data);

            // 4. Locate or Seed School Admin Role
            $admin_role = $this->db->where('school_id', $school_id)
                ->where('role_code', 'SCHOOL_ADMIN')
                ->where('is_deleted', 'n')
                ->get('tbl_roles')
                ->row();

            $admin_role_id = $admin_role ? (int)$admin_role->role_id : 0;
            if (!$admin_role_id) {
                $seeded_roles = $this->seed_default_school_roles($school_id, ($permission_mode === 'all' ? [] : $permissions));
                $admin_role_id = $seeded_roles['SCHOOL_ADMIN'] ?? 0;
            }

            // 4B. Locate or Seed School Admin Designation
            if (!isset($this->Designation_model)) {
                $this->load->model('Designation_model');
            }
            $sa_desig = $this->db->where('school_id', $school_id)
                ->where('designation_code', 'SCHOOL_ADMIN')
                ->where('is_deleted', 'n')
                ->get('tbl_designations')
                ->row();
            $admin_desig_id = $sa_desig ? (int)$sa_desig->designation_id : 0;
            if (!$admin_desig_id) {
                $seeded_desigs = $this->Designation_model->initialize_school_designations($school_id, ($permission_mode === 'all' ? [] : $permissions));
                $admin_desig_id = $seeded_desigs['SCHOOL_ADMIN'] ?? 0;
            }

            // 5. Update Existing School Admin User in-place (Never duplicate)
            $existing_admin = $this->get_school_admin($school_id);
            if ($existing_admin) {
                $update_user = [
                    'name'           => trim($admin_data['name']),
                    'username'       => trim($admin_data['username']),
                    'email'          => trim($admin_data['email']),
                    'phone'          => !empty($admin_data['phone']) ? trim($admin_data['phone']) : null,
                    'role_id'        => $admin_role_id,
                    'designation_id' => $admin_desig_id ?: $admin_role_id,
                    'updated_at'     => date('Y-m-d H:i:s')
                ];

                // If password was provided, securely hash with bcrypt
                if (!empty($admin_data['password'])) {
                    $update_user['password'] = password_hash($admin_data['password'], PASSWORD_BCRYPT);
                }

                // Strict isolation: require both user_id and school_id
                $this->db->where('user_id', (int)$existing_admin->user_id)
                    ->where('school_id', $school_id)
                    ->update('tbl_users', $update_user);

                $admin_user_id = (int)$existing_admin->user_id;
            } else {
                // Legacy school without an admin user: create one safely
                $pwd = !empty($admin_data['password']) ? $admin_data['password'] : 'Admin@123';
                $new_admin_user = [
                    'school_id'      => $school_id,
                    'role_id'        => $admin_role_id,
                    'designation_id' => $admin_desig_id ?: $admin_role_id,
                    'name'           => trim($admin_data['name']),
                    'username'       => trim($admin_data['username']),
                    'password'       => password_hash($pwd, PASSWORD_BCRYPT),
                    'email'          => trim($admin_data['email']),
                    'phone'          => !empty($admin_data['phone']) ? trim($admin_data['phone']) : null,
                    'user_type'      => 'Admin',
                    'status'         => 'Active',
                    'created_at'     => date('Y-m-d H:i:s'),
                    'is_deleted'     => 'n'
                ];
                $this->db->insert('tbl_users', $new_admin_user);
                $admin_user_id = (int)$this->db->insert_id();
            }

            // 6. Update School Admin Permissions (Role & Designation)
            if ($admin_role_id > 0) {
                // Determine target permissions
                if ($permission_mode === 'custom') {
                    $perm_ids = array_values(array_unique(array_filter(array_map('intval', $permissions))));
                } else {
                    $all_perms = $this->db->select('permission_id')->where('is_deleted', 'n')->get('tbl_permissions')->result();
                    $perm_ids = array_values(array_unique(array_map(function($p) { return (int)$p->permission_id; }, $all_perms)));
                }

                // Strictly scoped deletion to school_id and role_id
                $this->db->where('role_id', $admin_role_id)
                    ->where('school_id', $school_id)
                    ->delete('tbl_role_permissions');

                $inserted_pids = [];
                foreach ($perm_ids as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0 && !isset($inserted_pids[$pid])) {
                        $inserted_pids[$pid] = true;
                        $this->db->insert('tbl_role_permissions', [
                            'school_id'     => $school_id,
                            'role_id'       => $admin_role_id,
                            'permission_id' => $pid,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'is_deleted'    => 'n'
                        ]);
                    }
                }

                // Synchronize tbl_designation_permissions atomically within this same transaction
                if ($admin_desig_id > 0) {
                    $this->db->where('designation_id', $admin_desig_id)
                        ->where('school_id', $school_id)
                        ->delete('tbl_designation_permissions');

                    foreach ($inserted_pids as $pid => $val) {
                        $this->db->insert('tbl_designation_permissions', [
                            'school_id'      => $school_id,
                            'designation_id' => $admin_desig_id,
                            'permission_id'  => (int)$pid,
                            'created_at'     => date('Y-m-d H:i:s'),
                            'is_deleted'     => 'n'
                        ]);
                    }
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();

            if (isset($this->rbac) && method_exists($this->rbac, 'refresh_permissions')) {
                $this->rbac->refresh_permissions();
            }
            clear_school_cache();

            return [
                'school_id'     => $school_id,
                'admin_user_id' => $admin_user_id
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'update_school_with_admin failed: ' . $e->getMessage());
            return false;
        }
    }
}

