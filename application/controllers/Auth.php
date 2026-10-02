<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth
 *
 * Handles user login and logout.
 * Credentials are verified against tbl_users using bcrypt (password_verify).
 * Accounts must be Active and not locked to succeed.
 * After successful authentication, the user's school is verified:
 *   - If the school is inactive or deleted, login is rejected.
 *   - school_id is stored in the session for the centralized school context.
 */
class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (isset($this->db) && is_object($this->db) && !empty($this->db->conn_id)) {
            $this->db->query("SET time_zone = '+05:30'");
        }
        $this->load->model('User_model');
        $this->load->helper('app'); // school_initials()
    }

    public function index()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        } else {
            redirect('auth/login');
        }
    }

    public function login()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
            return;
        }

        if ($this->input->method() === 'post')
        {
            $this->form_validation->set_rules('email',    'Email or Username', 'required|trim');
            $this->form_validation->set_rules('password', 'Password',          'required');

            if ($this->form_validation->run() === TRUE)
            {
                $identifier = $this->input->post('email',    TRUE);
                $password   = $this->input->post('password');

                $user = $this->User_model->verify_credentials($identifier, $password);

                if ($user)
                {
                    // --------------------------------------------------------
                    // MULTI-SCHOOL: Verify that the user's school is active.
                    // Super Admin (role_code = SUPER_ADMIN) bypasses this check.
                    // --------------------------------------------------------
                    $is_super_admin = (
                        (isset($user->designation_code) && $user->designation_code === 'SUPER_ADMIN') ||
                        $user->role_code === 'SUPER_ADMIN' ||
                        $user->role_name === 'Super Admin' ||
                        (int)$user->role_id === 1
                    );

                    if (!$is_super_admin && !empty($user->school_id)) {
                        $school = $this->db
                            ->where('id', (int)$user->school_id)
                            ->get('tbl_schools')
                            ->row();

                        if (!$school || $school->status !== 'Active' || $school->is_deleted === 'y') {
                            $this->session->set_flashdata('error', 'Your school account is currently inactive. Please contact the administrator.');
                            $this->load->view('auth/login');
                            return;
                        }
                    }
                    // --------------------------------------------------------

                    $initials = school_initials($user->name);

                    // Initialize active academic year in user session
                    // For non-Super-Admin, load academic year belonging to their school
                    $this->load->model('Academic_year_model');
                    $user_school_id = $is_super_admin ? 1 : (int)($user->school_id ?? 1);
                    $active_year = $this->db
                        ->where('school_id', $user_school_id)
                        ->where('is_active', 1)
                        ->where('is_deleted', 'n')
                        ->order_by('academic_year_id', 'DESC')
                        ->limit(1)
                        ->get('tbl_academic_years')
                        ->row();

                    if (!$active_year) {
                        // Fallback: any active year for any school
                        $active_year = $this->Academic_year_model->get_active_year();
                    }
                    $active_year_id = $active_year ? (int)$active_year->academic_year_id : 1;

                    // Store the canonical nested 'user' array plus essential
                    // top-level keys for backward compatibility with controllers
                    // and models that read userdata('user_id') / userdata('user_role')
                    $this->session->set_userdata([
                        'logged_in'  => TRUE,
                        'selected_academic_year_id' => $active_year_id,
                        'academic_year_id'          => $active_year_id,
                        // Active school context — server-side authoritative
                        'active_school_id' => $is_super_admin ? 1 : (int)($user->school_id ?? 1),
                        'school_id'        => $is_super_admin ? NULL : (int)($user->school_id ?? 1),
                        // Legacy flat keys (still read by 30+ controller/model locations)
                        'user_id'    => (int)$user->user_id,
                        'user_name'  => $user->name,
                        'user_email' => $user->email,
                        'user_role'  => $user->role_name,
                        'role_id'    => (int)$user->role_id,
                        'designation_id'   => !empty($user->designation_id) ? (int)$user->designation_id : null,
                        'designation_name' => $user->designation_name ?? $user->role_name,
                        'designation_code' => $user->designation_code ?? $user->role_code,
                        // Canonical nested object (used by MY_Controller + Rbac)
                        'user'       => [
                            'user_id'   => (int)$user->user_id,
                            'name'      => $user->name,
                            'username'  => $user->username,
                            'email'     => $user->email,
                            'role_id'   => (int)$user->role_id,
                            'role'      => $user->role_name,
                            'role_code' => $user->role_code ?: 'USER',
                            'designation_id'   => !empty($user->designation_id) ? (int)$user->designation_id : null,
                            'designation_name' => $user->designation_name ?? $user->role_name,
                            'designation_code' => $user->designation_code ?? $user->role_code,
                            'user_type' => $user->user_type ?: 'Admin',
                            'school_id' => $is_super_admin ? NULL : (int)($user->school_id ?? 1),
                            'initials'  => $initials,
                        ],
                    ]);

                    redirect('dashboard');
                    return;
                }
                else
                {
                    $this->session->set_flashdata('error', 'Invalid email address or password. Please try again.');
                }
            }
            else
            {
                $this->session->set_flashdata('error', validation_errors('', ''));
            }
        }

        $this->load->view('auth/login');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
