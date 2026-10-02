<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Schools Controller
 *
 * Dedicated controller for Super Admin Multi-School Management:
 *  - List, Create, Edit, View, and Soft-toggle schools
 *  - Global active school switching endpoint for Super Admin
 *  - Per-school storage / Google Drive configuration
 */
class Schools extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('School_model');
        $this->load->model('Permission_model');
        $this->load->model('User_model');
    }

    /**
     * Super Admin guard helper.
     */
    protected function require_super_admin()
    {
        if (!$this->rbac->is_super_admin()) {
            if ($this->input->is_ajax_request()) {
                $this->json_output([
                    'status'  => false,
                    'message' => 'Unauthorized. Only Super Admin can manage schools.'
                ], 403)->_display();
                exit;
            }
            show_error('Access denied. Only Super Admin can access School Management.', 403, '403 Forbidden');
            exit;
        }
    }

    /**
     * List all schools with summary stats and status controls.
     * Route: /settings/schools or /schools
     */
    public function index()
    {
        $this->require_super_admin();

        $schools = $this->School_model->get_all(true);
        foreach ($schools as $s) {
            $s->stats = $this->School_model->get_school_stats($s->id);
            $s->storage = $this->School_model->get_storage_config($s->id);
        }

        $permission_matrix_tree = $this->navigation->get_permission_matrix_tree();
        $active_modules_count   = count($permission_matrix_tree);

        $this->render('pages/schools/index', [
            'title'                  => 'School Management',
            'page_key'               => 'schools',
            'breadcrumb'             => ['Login2 Settings', 'Schools'],
            'schools'                => $schools,
            'permission_matrix_tree' => $permission_matrix_tree,
            'active_modules_count'   => $active_modules_count,
        ]);
    }

    /**
     * AJAX endpoint to generate a unique school code preview.
     */
    public function generate_code()
    {
        $this->require_super_admin();
        $name = $this->input->get('name', TRUE) ?: $this->input->post('name', TRUE);
        $code = $this->School_model->generate_school_code($name);

        return $this->json_output([
            'status'          => true,
            'school_code'     => $code,
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        ]);
    }

    /**
     * Create school POST endpoint.
     * Atomically creates School Record, School Settings, Roles, Permissions, Storage,
     * and School Admin User Account in one database transaction.
     */
    public function create()
    {
        $this->require_super_admin();

        // Section 1: School Validation
        $this->form_validation->set_rules('school_name', 'School Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('school_code', 'School Code', 'trim|max_length[50]');

        // Section 2: School Admin User Validation
        $this->form_validation->set_rules('admin_name', 'Admin Full Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('admin_username', 'Admin Username', 'required|trim|alpha_dash|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('admin_password', 'Admin Password', 'required|min_length[6]');
        $this->form_validation->set_rules('admin_email', 'Admin Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('admin_phone', 'Admin Phone', 'trim|max_length[20]');

        if ($this->form_validation->run() === FALSE) {
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => validation_errors('', ''),
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', validation_errors('', ''));
            redirect('settings/schools');
            return;
        }

        // 1. Process School Code
        $school_name = trim($this->input->post('school_name', TRUE));
        $code = strtoupper(trim($this->input->post('school_code', TRUE)));
        if (empty($code)) {
            $code = $this->School_model->generate_school_code($school_name);
        }

        if (!preg_match('/^[A-Z0-9_\-]{3,20}$/', $code)) {
            $msg = "School code '{$code}' is invalid. It must be 3-20 uppercase alphanumeric characters (e.g. NGPM001).";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        if ($this->School_model->is_code_exists($code)) {
            $msg = "School code '{$code}' is already registered. Please choose a unique school code.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        // 2. Process Admin Username & Email Uniqueness
        $admin_username = trim($this->input->post('admin_username', TRUE));
        if ($this->User_model->is_username_exists($admin_username)) {
            $msg = "Admin username '{$admin_username}' is already taken. Please choose a unique username.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        $admin_email = trim($this->input->post('admin_email', TRUE));
        if ($this->User_model->is_email_exists($admin_email)) {
            $msg = "Admin email '{$admin_email}' is already registered to another user account.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        // 3. Handle logo upload if provided
        $logo_filename = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload_path = FCPATH . 'uploads/schools/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|webp|svg';
            $config['max_size']      = 2048; // 2MB
            $config['file_name']     = 'school_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $code)) . '_' . time();

            $this->load->library('upload', $config);
            if ($this->upload->do_upload('logo')) {
                $upload_data = $this->upload->data();
                $logo_filename = $upload_data['file_name'];
            }
        }

        $school_data = [
            'school_code'      => $code,
            'school_name'      => $school_name,
            'principal_name'   => trim($this->input->post('principal_name', TRUE)),
            'phone'            => trim($this->input->post('phone', TRUE)),
            'email'            => trim($this->input->post('email', TRUE)),
            'website'          => trim($this->input->post('website', TRUE)),
            'address'          => trim($this->input->post('address', TRUE)),
            'established_year' => (int)$this->input->post('established_year', TRUE) ?: date('Y'),
            'logo'             => $logo_filename,
            'status'           => $this->input->post('status') === 'Inactive' ? 'Inactive' : 'Active'
        ];

        $admin_data = [
            'name'     => trim($this->input->post('admin_name', TRUE)),
            'username' => $admin_username,
            'password' => $this->input->post('admin_password'),
            'email'    => $admin_email,
            'phone'    => trim($this->input->post('admin_phone', TRUE))
        ];

        // 4. Permissions
        $permission_mode = $this->input->post('permission_mode', TRUE) ?: 'all';
        $permissions = [];
        if ($permission_mode === 'custom') {
            $raw_perms = $this->input->post('permissions');
            if (is_array($raw_perms)) {
                $permissions = array_values(array_unique(array_filter(array_map('intval', $raw_perms))));
            }
        }

        $storage_data = [
            'provider'         => $this->input->post('storage_provider', TRUE) ?: 'local',
            'folder_id'        => trim($this->input->post('storage_folder_id', TRUE)),
            'credentials_json' => trim($this->input->post('storage_credentials_json', TRUE))
        ];

        // 5. ATOMIC TRANSACTIONAL CREATION
        $result = $this->School_model->create_school_with_admin($school_data, $admin_data, $permissions, $storage_data, $permission_mode);

        if (!$result) {
            $err_msg = "Transaction failed: Could not create school and administrator account. All changes rolled back.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $err_msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 500);
            }
            $this->session->set_flashdata('error', $err_msg);
            redirect('settings/schools');
            return;
        }

        $new_id = $result['school_id'];
        $msg = "School '" . html_escape($school_data['school_name']) . "' and School Admin account '" . html_escape($admin_data['username']) . "' created successfully in one transaction.";

        if ($this->input->is_ajax_request()) {
            return $this->json_output([
                'status'          => true,
                'message'         => $msg,
                'school_id'       => $new_id,
                'admin_user_id'   => $result['admin_user_id'],
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]);
        }

        $this->session->set_flashdata('success', $msg);
        redirect('settings/schools');
    }

    /**
     * Retrieve full details of a school for pre-filling the unified form (AJAX GET).
     */
    public function get_details($id)
    {
        $this->require_super_admin();
        $id = (int)$id;
        $details = $this->School_model->get_school_full_details($id);
        if (!$details) {
            return $this->json_output(['status' => false, 'message' => 'School not found.'], 404);
        }

        return $this->json_output([
            'status'          => true,
            'school'          => $details['school'],
            'storage'         => $details['storage'],
            'admin'           => $details['admin'],
            'permissions'     => $details['permissions'],
            'permission_mode' => $details['permission_mode'],
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        ]);
    }

    /**
     * Edit school endpoint (GET for details JSON, POST for transaction-safe update).
     */
    public function edit($id)
    {
        $this->require_super_admin();
        $id = (int)$id;
        $school = $this->School_model->get_by_id($id);
        if (!$school) {
            if ($this->input->is_ajax_request()) {
                return $this->json_output(['status' => false, 'message' => 'School not found.'], 404);
            }
            $this->session->set_flashdata('error', 'School not found.');
            redirect('settings/schools');
            return;
        }

        // If GET / AJAX request without POST, return full details for pre-filling modal
        if ($this->input->method() !== 'post') {
            return $this->get_details($id);
        }

        // Section 1: School Validation
        $this->form_validation->set_rules('school_name', 'School Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('school_code', 'School Code', 'required|trim|max_length[50]');

        // Section 2: School Admin User Validation
        $this->form_validation->set_rules('admin_name', 'Admin Full Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('admin_username', 'Admin Username', 'required|trim|alpha_dash|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('admin_email', 'Admin Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('admin_phone', 'Admin Phone', 'trim|max_length[20]');

        // Password is optional in Edit mode (only validate if provided)
        $new_password = $this->input->post('admin_password');
        if (!empty($new_password)) {
            $this->form_validation->set_rules('admin_password', 'Admin Password', 'min_length[6]');
        }

        if ($this->form_validation->run() === FALSE) {
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => validation_errors('', ''),
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', validation_errors('', ''));
            redirect('settings/schools');
            return;
        }

        // Uniqueness check for school code (excluding current school)
        $code = strtoupper(trim($this->input->post('school_code', TRUE)));
        if ($this->School_model->is_code_exists($code, $id)) {
            $msg = "School code '{$code}' is already registered to another school.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        // Locate existing admin to exclude from username and email uniqueness
        $existing_admin = $this->School_model->get_school_admin($id);
        $exclude_user_id = $existing_admin ? (int)$existing_admin->user_id : null;

        $admin_username = trim($this->input->post('admin_username', TRUE));
        if ($this->User_model->is_username_exists($admin_username, $exclude_user_id)) {
            $msg = "Admin username '{$admin_username}' is already taken. Please choose a unique username.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        $admin_email = trim($this->input->post('admin_email', TRUE));
        if ($this->User_model->is_email_exists($admin_email, $exclude_user_id)) {
            $msg = "Admin email '{$admin_email}' is already registered to another user account.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 400);
            }
            $this->session->set_flashdata('error', $msg);
            redirect('settings/schools');
            return;
        }

        // Handle logo update if a new file is uploaded
        $logo_filename = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload_path = FCPATH . 'uploads/schools/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|webp|svg';
            $config['max_size']      = 2048;
            $config['file_name']     = 'school_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $code)) . '_' . time();

            $this->load->library('upload', $config);
            if ($this->upload->do_upload('logo')) {
                $upload_data = $this->upload->data();
                $logo_filename = $upload_data['file_name'];
            }
        }

        $school_data = [
            'school_code'      => $code,
            'school_name'      => trim($this->input->post('school_name', TRUE)),
            'principal_name'   => trim($this->input->post('principal_name', TRUE)),
            'phone'            => trim($this->input->post('phone', TRUE)),
            'email'            => trim($this->input->post('email', TRUE)),
            'website'          => trim($this->input->post('website', TRUE)),
            'address'          => trim($this->input->post('address', TRUE)),
            'established_year' => (int)$this->input->post('established_year', TRUE) ?: $school->established_year,
            'status'           => $this->input->post('status') === 'Inactive' ? 'Inactive' : 'Active'
        ];
        if ($logo_filename !== null) {
            $school_data['logo'] = $logo_filename;
        }

        $admin_data = [
            'name'     => trim($this->input->post('admin_name', TRUE)),
            'username' => $admin_username,
            'password' => $new_password, // Empty string means keep existing password
            'email'    => $admin_email,
            'phone'    => trim($this->input->post('admin_phone', TRUE))
        ];

        // Permissions
        $permission_mode = $this->input->post('permission_mode', TRUE) ?: 'all';
        $permissions = [];
        if ($permission_mode === 'custom') {
            $raw_perms = $this->input->post('permissions');
            if (is_array($raw_perms)) {
                $permissions = array_values(array_unique(array_filter(array_map('intval', $raw_perms))));
            }
        }

        // Storage / Google Drive configuration
        $existing_storage = $this->School_model->get_storage_config($id);
        $storage_provider = $this->input->post('storage_provider', TRUE) ?: ($existing_storage->provider ?? 'local');
        $folder_id        = trim($this->input->post('storage_folder_id', TRUE));
        $credentials_json = trim($this->input->post('storage_credentials_json', TRUE));

        // Preserve existing credentials JSON if left empty
        if ($credentials_json === '' && !empty($existing_storage->credentials_json)) {
            $credentials_json = $existing_storage->credentials_json;
        }

        $storage_data = [
            'provider'         => $storage_provider,
            'folder_id'        => $folder_id,
            'credentials_json' => $credentials_json
        ];

        // Execute atomic transactional update
        $result = $this->School_model->update_school_with_admin($id, $school_data, $admin_data, $permissions, $storage_data, $permission_mode);

        if (!$result) {
            $err_msg = "Transaction failed: Could not update school and administrator account. All changes rolled back.";
            if ($this->input->is_ajax_request()) {
                return $this->json_output([
                    'status'          => false,
                    'message'         => $err_msg,
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                ], 500);
            }
            $this->session->set_flashdata('error', $err_msg);
            redirect('settings/schools');
            return;
        }

        $msg = "School '" . html_escape($school_data['school_name']) . "' and School Admin account '" . html_escape($admin_data['username']) . "' updated successfully.";
        if ($this->input->is_ajax_request()) {
            return $this->json_output([
                'status'          => true,
                'message'         => $msg,
                'school_id'       => $id,
                'admin_user_id'   => $result['admin_user_id'],
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]);
        }

        $this->session->set_flashdata('success', $msg);
        redirect('settings/schools');
    }


    /**
     * Toggle Active / Inactive status of school.
     */
    public function toggle_status($id)
    {
        $this->require_super_admin();
        $id = (int)$id;
        $school = $this->School_model->get_by_id($id);
        if (!$school) {
            if ($this->input->is_ajax_request()) {
                return $this->json_output(['status' => false, 'message' => 'School not found.'], 404);
            }
            $this->session->set_flashdata('error', 'School not found.');
            redirect('settings/schools');
            return;
        }

        // Do not allow deactivating the currently active school without switching first
        if ($id === get_current_school_id() && $school->status === 'Active') {
            $active_count = count($this->School_model->get_active_schools());
            if ($active_count <= 1) {
                $msg = 'Cannot deactivate the only active school in the system.';
                if ($this->input->is_ajax_request()) {
                    return $this->json_output(['status' => false, 'message' => $msg], 400);
                }
                $this->session->set_flashdata('error', $msg);
                redirect('settings/schools');
                return;
            }
        }

        $this->School_model->toggle_status($id);
        clear_school_cache();

        $new_status = ($school->status === 'Active') ? 'Inactive' : 'Active';
        $msg = "School '{$school->school_name}' is now {$new_status}.";

        if ($this->input->is_ajax_request()) {
            return $this->json_output([
                'status'          => true,
                'message'         => $msg,
                'new_status'      => $new_status,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ]);
        }
        $this->session->set_flashdata('success', $msg);
        redirect('settings/schools');
    }

    /**
     * Centralized Switch School AJAX Endpoint
     * Route: POST /schools/switch_school or /settings/schools/switch
     */
    public function switch_school()
    {
        $this->require_super_admin();

        $school_id = (int)($this->input->post('school_id') ?: $this->input->get('school_id'));
        if ($school_id <= 0) {
            return $this->json_output([
                'status'          => false,
                'message'         => 'Invalid school ID specified.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ], 400);
        }

        $school = $this->School_model->get_by_id($school_id);
        if (!$school || $school->is_deleted === 'y' || $school->status !== 'Active') {
            return $this->json_output([
                'status'          => false,
                'message'         => 'The selected school is inactive or not found.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ], 400);
        }

        $switched = set_current_school_id($school_id);
        if (!$switched) {
            return $this->json_output([
                'status'          => false,
                'message'         => 'Failed to switch school context.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            ], 500);
        }

        $active_year = get_active_academic_year(false, true, $school_id);

        return $this->json_output([
            'status'             => true,
            'message'            => "Active school switched to {$school->school_name}.",
            'school_id'          => (int)$school->id,
            'school_name'        => $school->school_name,
            'school_code'        => $school->school_code,
            'academic_year_id'   => $active_year ? (int)$active_year->academic_year_id : 1,
            'academic_year_name' => $active_year ? $active_year->year_name : '',
            'csrf_token_name'    => $this->security->get_csrf_token_name(),
            'csrf_hash'          => $this->security->get_csrf_hash()
        ]);
    }
}
