<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller — Base controller for every School page.
 *
 * Provides:
 *  - Authentication guard (redirects to login if no session)
 *  - RBAC permission enforcement via require_permission()
 *  - render() helper that loads header → page view → footer
 *  - Effective permissions are fetched once per render() call from the
 *    Rbac library's per-request cache (no extra DB queries)
 */
class MY_Controller extends CI_Controller {

    /** @var int  The active/selected school ID */
    public $school_id;

    /** @var object  The active/selected school record */
    public $current_school;

    /** @var object  The authenticated user from session */
    public $current_user;

    /** @var int  The active/selected academic year ID */
    public $academic_year_id;

    /** @var object  The active/selected academic year record */
    public $current_academic_year;

    public function __construct()
    {
        parent::__construct();
        if (isset($this->db) && is_object($this->db) && !empty($this->db->conn_id)) {
            $this->db->query("SET time_zone = '+05:30'");
        }
        $this->load->library('Rbac');
        $this->load->library('Navigation');
        $this->load->model('User_model');
        $this->load->model('School_model');
        $this->load->model('Academic_year_model');

        // Enforce authentication — return 401 for AJAX, redirect for web
        $user_data = $this->session->userdata('user');
        if (!$this->session->userdata('logged_in') || empty($user_data)) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'          => false,
                        'message'         => 'Session expired. Please log in again.',
                        'csrf_token_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash'       => $this->security->get_csrf_hash(),
                    ]));
                $this->output->_display();
                exit;
            }
            redirect('auth/login');
            exit;
        }

        // Build a consistent current_user object from the session 'user' array
        $this->current_user = (object)$user_data;

        // Initialize Centralized Active School Context
        $this->school_id      = get_current_school_id();
        $this->current_school = get_current_school();

        // Enforce School Status & Isolation for normal users
        if (!$this->rbac->is_super_admin()) {
            if (!$this->current_school || $this->current_school->status !== 'Active' || $this->current_school->is_deleted === 'y') {
                $this->session->sess_destroy();
                $this->session->set_flashdata('error', 'Your school account is currently deactivated. Please contact the administrator.');
                redirect('auth/login');
                exit;
            }
            $assigned_school = (int)($user_data['school_id'] ?? 1);
            if ($this->school_id !== $assigned_school) {
                $this->school_id = $assigned_school;
                $this->session->set_userdata('active_school_id', $assigned_school);
                $this->session->set_userdata('school_id', $assigned_school);
                $this->current_school = get_current_school(TRUE);
            }
        }

        // Initialize global academic year context for this school
        $this->academic_year_id      = get_current_academic_year_id();
        $this->current_academic_year = get_current_academic_year();
    }

    // -------------------------------------------------------------------------
    // Auth guard
    // -------------------------------------------------------------------------

    /**
     * Explicit auth check — available for legacy call sites but now redundant
     * since __construct() already enforces it.  Kept for backwards compatibility.
     */
    public function require_auth()
    {
        $user_data = $this->session->userdata('user');
        if (!$this->session->userdata('logged_in') || empty($user_data)) {
            redirect('auth/login');
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // Permission guard
    // -------------------------------------------------------------------------

    /**
     * Enforce backend authorisation for a protected controller action.
     *
     * Super Admins always pass.
     * Other users need the exact permission key assigned to their role.
     *
     * For AJAX requests a JSON 403 response is returned instead of a redirect.
     *
     * @param  string $permission_key  e.g. 'students.view', 'fees.collect'
     */
    public function require_permission($permission_key)
    {
        if ($this->rbac->is_super_admin()) {
            return;
        }

        $keys = is_array($permission_key) ? $permission_key : func_get_args();
        $has_access = FALSE;
        foreach ($keys as $k) {
            if (is_string($k) && $this->rbac->has_permission($k)) {
                $has_access = TRUE;
                break;
            }
        }

        if (!$has_access) {
            $required_str = is_array($permission_key) ? implode(' / ', $permission_key) : $permission_key;
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'  => 'error',
                        'message' => 'Access denied. Required permission: ' . $required_str,
                    ]));
                $this->output->_display();
                exit;
            }

            show_error('You do not have permission to access this page.', 403, '403 Forbidden');
        }
    }

    /**
     * Enforce strict school ownership on retrieved entity objects or arrays.
     * Prevents IDOR (Insecure Direct Object Reference) vulnerabilities.
     *
     * @param  object|array $record
     * @param  string       $school_id_field
     * @return bool
     */
    public function verify_record_ownership($record, $school_id_field = 'school_id')
    {
        if ($this->rbac->is_super_admin()) {
            return TRUE;
        }

        if (empty($record)) {
            return FALSE;
        }

        $record_sid = is_object($record) ? ($record->$school_id_field ?? null) : ($record[$school_id_field] ?? null);

        if ($record_sid === null || (int)$record_sid !== (int)$this->school_id) {
            log_message('error', "IDOR attempt prevented: User ID " . ($this->current_user->user_id ?? 0) . " from School {$this->school_id} attempted to access record belonging to School " . ($record_sid ?? 'NULL'));
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status'  => false,
                        'message' => 'Unauthorized access. Record does not belong to your active school.',
                    ]));
                $this->output->_display();
                exit;
            }
            show_error('The requested record does not belong to your school context.', 403, '403 Forbidden');
            exit;
        }

        return TRUE;
    }

    // -------------------------------------------------------------------------
    // Layout renderer
    // -------------------------------------------------------------------------

    /**
     * Renders a 3-part layout: templates/header -> $view -> templates/footer.
     * Automatically passes common view variables so controllers don't have to:
     *   - $current_user
     *   - $is_super_admin
     *   - $effective_permissions  (array of keys e.g. ['students.view', ...])
     *   - $title
     *   - $page_key               (used by sidebar JS to highlight active item)
     *   - $breadcrumb
     *
     * @param  string $view   Path relative to application/views/ (e.g. 'pages/students/index')
     * @param  array  $data   Data array passed to all three view files
     */
    public function render($view, array $data = array())
    {
        $uid = (int)($this->current_user->user_id ?? 0);

        $data['title']               = $data['title'] ?? 'Login2';
        
        // Auto-determine page_key if not explicitly specified in the controller
        if (empty($data['page_key'])) {
            $class  = strtolower($this->router->fetch_class());
            $method = strtolower($this->router->fetch_method());
            $data['page_key'] = $this->_resolve_page_key($class, $method, $view);
        }

        $data['breadcrumb']          = isset($data['breadcrumb'])
                                         ? json_encode($data['breadcrumb'])
                                         : NULL;
        $data['current_user']        = $this->current_user;
        $data['is_super_admin']      = $this->rbac->is_super_admin();

        // Permissions come from Rbac's per-request cache — no extra DB query
        // if has_permission() or is_super_admin() was already called this request
        $data['effective_permissions'] = $this->rbac->is_super_admin()
            ? ['*']
            : $this->User_model->get_effective_permissions($uid);

        // Dynamic Database-Driven Sidebar Menu Tree
        $data['sidebar_items']         = $this->navigation->get_sidebar_for_current_user();

        // Centralized Active School Context
        $data['current_school']        = $this->current_school;
        $data['current_school_id']     = $this->school_id;
        $data['available_schools']     = get_available_schools();
        $data['can_switch_school']     = can_switch_school($uid);

        // Global Academic Year Context
        $data['current_academic_year']    = $this->current_academic_year;
        $data['current_academic_year_id'] = $this->academic_year_id;
        $data['active_academic_year']     = get_active_academic_year();
        $data['active_academic_year_id']  = get_active_academic_year_id();
        $data['available_academic_years'] = get_available_academic_years();
        $data['can_change_academic_year'] = can_change_academic_year($uid);

        $this->load->view('templates/header', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Renders an isolated document layout for printable reports, receipts, and certificates.
     * Unlike render(), this NEVER loads the web application UI (templates/header, sidebar,
     * header navbar, search, school switcher, user profile).
     *
     * Result: Clean, isolated document structure containing ONLY document header, report content,
     * and document footer, with zero web application chrome.
     *
     * @param  string $view Path relative to application/views/
     * @param  array  $data Data array passed to view files
     */
    public function render_document($view, array $data = array())
    {
        $data['title']                    = $data['title'] ?? 'Official Document';
        $data['current_school']           = $this->current_school;
        $data['current_school_id']        = $this->school_id;
        $data['current_academic_year']    = $this->current_academic_year;
        $data['current_academic_year_id'] = $this->academic_year_id;

        $this->load->view('templates/document_header', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/document_footer', $data);
    }

    /**
     * Resolve the active page_key from controller, method, and view path.
     *
     * @param  string $class   Current controller (lowercase)
     * @param  string $method  Current method (lowercase)
     * @param  string $view    Current view path
     * @return string
     */
    protected function _resolve_page_key($class, $method, $view = '')
    {
        // 1. Dashboard
        if ($class === 'dashboard') {
            return 'dashboard';
        }

        // 2. Student Management
        if ($class === 'students') {
            switch ($method) {
                case 'overview':
                case 'index':
                    return 'students';
                case 'all':
                case 'all_students':
                    return 'all-students';
                case 'bulk_add':
                case 'bulk':
                case 'bulk_student_add':
                    return 'student-bulk-add';
                case 'list_students':
                case 'list':
                case 'student_list':
                    return 'student-directory';
                case 'register':
                case 'add':
                case 'create':
                    return 'student-registration';
                case 'admissions':
                case 'admission':
                    return 'admissions';
                case 'documents':
                    return 'student-documents';
                case 'id_cards':
                    return 'student-id-cards';
                case 'promotion':
                case 'promote':
                    return 'student-promotion';
                case 'transfers':
                case 'tc':
                    return 'student-transfers';
                case 'profile':
                    return 'student-profile';
                case 'search':
                    return 'student-search';
                default:
                    return 'students';
            }
        }

        // 3. Staff / Teacher Management
        if ($class === 'staff') {
            switch ($method) {
                case 'overview':
                case 'index':
                    return 'staff';
                case 'directory':
                case 'list':
                    return 'staff-directory';
                case 'teachers':
                    return 'teachers';
                case 'non_teaching':
                    return 'non-teaching-staff';
                case 'departments':
                case 'departments_designations':
                case 'designations':
                    return 'designations';
                case 'documents':
                    return 'staff-documents';
                case 'workload':
                    return 'teacher-workload';
                case 'attendance':
                    return 'staff-attendance';
                case 'leave':
                    return 'staff-leave';
                default:
                    return 'staff';
            }
        }

        // 4. Academic Management
        if ($class === 'academics') {
            switch ($method) {
                case 'overview':
                case 'index':
                    return 'academics';
                case 'years':
                    return 'academic-years';
                case 'academic_groups':
                case 'groups':
                    return 'academic-groups';
                case 'classes':
                    return 'classes';
                case 'divisions':
                case 'sections':
                    return 'divisions';
                case 'subjects':
                    return 'subjects';
                case 'timetable':
                    return 'timetable';
                case 'calendar':
                    return 'academic-calendar';
                default:
                    return 'academics';
            }
        }

        // 5. Student Attendance
        if ($class === 'attendance') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'attendance-dashboard';
                case 'mark_attendance':
                case 'mark':
                    return 'attendance-mark';
                case 'daily':
                case 'section_attendance':
                case 'history':
                case 'tracking':
                case 'class_attendance':
                case 'view_attendance':
                case 'student_attendance':
                    return 'attendance-class';
                case 'periods':
                    return 'attendance-periods';
                case 'period_wise':
                    return 'attendance-period-wise';
                case 'calendar':
                    return 'attendance-calendar';
                case 'reports':
                    return 'attendance-reports';
                case 'notifications':
                    return 'attendance-notifications';
                case 'notification_history':
                    return 'attendance-notification-history';
                case 'settings':
                    return 'attendance-settings';
                default:
                    return 'attendance-dashboard';
            }
        }

        // 6. Examination & Results
        if ($class === 'examinations') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'exam-dashboard';
                case 'exams':
                    return 'exams';
                case 'types':
                    return 'exam-types';
                case 'schedules':
                    return 'exam-schedules';
                case 'allocations':
                    return 'exam-allocations';
                case 'marks_entry':
                    return 'marks-entry';
                case 'verification':
                    return 'marks-verification';
                case 'grades':
                    return 'grade-management';
                case 'calculate':
                    return 'result-calculation';
                case 'results':
                case 'result_detail':
                    return 'results';
                case 'ranks':
                    return 'exam-ranks';
                case 'report_cards':
                case 'report_card':
                    return 'report-cards';
                case 'progress_reports':
                case 'progress_report':
                    return 'progress-reports';
                case 'publishing':
                    return 'result-publishing';
                case 'reports':
                    return 'exam-reports';
                case 'settings':
                    return 'exam-settings';
                default:
                    return 'exam-dashboard';
            }
        }

        // 7. Fees & Finance
        if ($class === 'fees') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'fee-dashboard';
                case 'categories':
                    return 'fee-categories';
                case 'structures':
                    return 'fee-structures';
                case 'assignments':
                    return 'fee-assignments';
                case 'student_fees':
                    return 'student-fees';
                case 'collection':
                    return 'fee-collection';
                case 'payments':
                    return 'payment-history';
                case 'receipts':
                case 'receipt':
                    return 'fee-receipts';
                case 'discounts':
                    return 'fee-discounts';
                case 'due_fees':
                    return 'due-fees';
                case 'reminders':
                case 'reminder_history':
                    return 'fee-reminders';
                case 'adjustments':
                    return 'fee-adjustments';
                case 'refunds':
                    return 'fee-refunds';
                case 'reports':
                    return 'collection-reports';
                case 'settings':
                    return 'finance-settings';
                default:
                    return 'fee-dashboard';
            }
        }

        // Finance & Accounting
        if ($class === 'finance') {
            switch ($method) {
                case 'dashboard':
                case 'index':
                    return 'finance_dashboard';
                case 'account_groups':
                case 'groups':
                    return 'finance_coa_groups';
                case 'account_heads':
                case 'heads':
                case 'accounts':
                    return 'finance_coa_heads';
                case 'custom_accounts':
                case 'custom':
                    return 'finance_coa_custom';
                case 'ledgers':
                    return 'finance_ledgers_group';
                case 'ledger_students':
                case 'student_ledger':
                case 'student_statement':
                    return 'finance_ledgers_student';
                case 'ledger_staff':
                case 'staff_statement':
                    return 'finance_ledgers_staff';
                case 'ledger_other_parties':
                case 'other_party_statement':
                    return 'finance_ledgers_party';
                case 'ledger_general':
                    return 'finance_ledgers_general';
                // Transactions
                case 'transactions_income':
                case 'income':
                    return 'finance_txn_income';
                case 'transactions_expense':
                case 'transaction_expense':
                    return 'finance_txn_expense';
                case 'transfers':
                case 'transfer':
                    return 'finance_txn_transfer';
                case 'journal_entries':
                case 'journal':
                    return 'finance_txn_journal';
                case 'adjustments':
                case 'adjustment':
                    return 'finance_txn_adjustment';
                case 'refunds':
                case 'refund':
                    return 'finance_txn_refund';
                // Fees
                case 'fee_types':
                    return 'finance_fee_types';
                case 'fee_structures':
                    return 'finance_fee_structures';
                case 'fee_assignments':
                    return 'finance_fee_assignments';
                case 'fee_collection':
                    return 'finance_fee_collection';
                case 'pending_fees':
                    return 'finance_fee_pending';
                case 'fee_receipts':
                case 'student_receipt':
                case 'receipt':
                    return 'finance_fee_receipts';
                // Expenses
                case 'expenses':
                case 'expense_entry':
                    return 'finance_exp_entry';
                case 'staff_payouts':
                case 'staff_payout':
                    return 'finance_exp_payout';
                case 'vendor_payments':
                case 'vendor_payment':
                    return 'finance_exp_vendor';
                case 'other_expenses':
                case 'other_expense':
                    return 'finance_exp_other';
                case 'expense_types':
                    return 'finance_expenses_group';
                // Cash & Bank
                case 'cash_accounts':
                case 'cash_bank':
                    return 'finance_cb_cash';
                case 'bank_accounts':
                    return 'finance_cb_bank';
                case 'transfers':
                case 'cash_transfers':
                    return 'finance_cb_transfers';
                // Staff Finance
                case 'salary_setup':
                    return 'finance_salary_setup';
                case 'salary_processing':
                    return 'finance_salary_processing';
                case 'salary_payable':
                    return 'finance_salary_payable';
                // Reports
                case 'reports':
                    return 'finance_reports_group';
                default:
                    return 'finance_dashboard';
            }
        }

        // 8. Timetable
        if ($class === 'timetable') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'timetable-dashboard';
                case 'classes':
                    return 'class-timetable';
                case 'teachers':
                    return 'teacher-timetable';
                case 'allocations':
                    return 'subject-allocation';
                case 'builder':
                    return 'timetable-builder';
                case 'period_setup':
                case 'periods':
                    return 'timetable-period-setup';
                case 'free_periods':
                    return 'free-periods';
                case 'conflicts':
                    return 'timetable-conflicts';
                case 'publish_lock':
                    return 'timetable-publish';
                case 'reports':
                    return 'timetable-reports';
                case 'settings':
                    return 'timetable-settings';
                default:
                    return 'timetable-dashboard';
            }
        }

        // 9. Homework / Assignments
        if ($class === 'homework') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'homework-dashboard';
                case 'assignments':
                    return 'homework-assignments';
                case 'create':
                case 'edit':
                    return 'homework-create';
                case 'types':
                    return 'homework-types';
                case 'subjects':
                    return 'homework-subjects';
                case 'classes':
                    return 'homework-classes';
                case 'calendar':
                    return 'homework-calendar';
                case 'submissions':
                case 'submission_detail':
                case 'review':
                case 'student_view':
                    return 'homework-submissions';
                case 'reports':
                    return 'homework-reports';
                case 'settings':
                    return 'homework-settings';
                default:
                    return 'homework-dashboard';
            }
        }

        // 10. Communication
        if ($class === 'communication') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'comm-dashboard';
                case 'templates':
                    return 'comm-templates';
                case 'sms_templates':
                    return 'comm-sms-templates';
                case 'whatsapp_templates':
                    return 'comm-whatsapp-templates';
                case 'email_templates':
                    return 'comm-email-templates';
                case 'automated_notifications':
                    return 'comm-automated';
                case 'queue':
                    return 'comm-queue';
                case 'history':
                case 'details':
                    return 'comm-history';
                case 'reports':
                    return 'comm-reports';
                case 'settings':
                    return 'comm-settings';
                case 'notices':
                case 'create_notice':
                    return 'notices';
                case 'announcements':
                    return 'announcements';
                default:
                    return 'comm-dashboard';
            }
        }

        // 11. Leave Management
        if ($class === 'leave') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'leave-dashboard';
                case 'student_leave':
                    return 'leave-student';
                case 'staff_leave':
                    return 'leave-staff';
                case 'types':
                    return 'leave-types';
                case 'request':
                    return 'leave-request';
                case 'approval':
                    return 'leave-approval';
                case 'balances':
                    return 'leave-balance';
                case 'calendar':
                    return 'leave-calendar';
                case 'history':
                case 'details':
                    return 'leave-history';
                case 'reports':
                    return 'leave-reports';
                case 'settings':
                    return 'leave-settings';
                default:
                    return 'leave-dashboard';
            }
        }

        // 12. Transport Management
        if ($class === 'transport') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'transport-dashboard';
                case 'vehicles':
                case 'vehicle_details':
                    return 'transport-vehicles';
                case 'drivers':
                case 'driver_details':
                    return 'transport-drivers';
                case 'routes':
                case 'route_details':
                    return 'transport-routes';
                case 'stops':
                    return 'transport-stops';
                case 'assignments':
                case 'bulk_assign':
                    return 'transport-assignments';
                case 'fees':
                    return 'transport-fees';
                case 'maintenance':
                case 'maintenance_history':
                    return 'transport-maintenance';
                case 'documents':
                    return 'transport-documents';
                case 'reports':
                    return 'transport-reports';
                case 'settings':
                    return 'transport-settings';
                default:
                    return 'transport-dashboard';
            }
        }

        // 13. Certificates & Documents
        if ($class === 'certificates') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'certificates-dashboard';
                case 'requests':
                case 'request_create':
                    return 'certificates-requests';
                case 'types':
                    return 'certificates-types';
                case 'bonafide':
                    return 'certificates-bonafide';
                case 'transfer_certificate':
                    return 'certificates-transfer';
                case 'study_certificate':
                    return 'certificates-study';
                case 'conduct_certificate':
                    return 'certificates-conduct';
                case 'generate':
                case 'preview':
                case 'print_cert':
                case 'issue':
                    return 'certificates-generate';
                case 'templates':
                    return 'certificates-templates';
                case 'documents':
                    return 'certificates-documents';
                case 'document_categories':
                    return 'certificates-doc-categories';
                case 'document_verification':
                    return 'certificates-doc-verification';
                case 'history':
                case 'reissue':
                    return 'certificates-history';
                case 'reports':
                    return 'certificates-reports';
                case 'settings':
                    return 'certificates-settings';
                default:
                    return 'certificates-dashboard';
            }
        }

        // 14. Reports
        if ($class === 'reports') {
            return 'reports';
        }

        // 15. User Management
        if ($class === 'users') {
            switch ($method) {
                case 'overview':
                case 'dashboard':
                case 'index':
                    return 'user-dashboard';
                case 'list_users':
                case 'list':
                    return 'users';
                case 'create':
                    return 'user-create';
                case 'details':
                case 'edit':
                    return 'user-details';
                case 'roles':
                    return 'user-roles';
                case 'permissions':
                case 'user_permissions':
                    return 'user-permissions';
                case 'role_permissions':
                    return 'user-role-permissions';
                case 'parents':
                    return 'user-parents';
                case 'students':
                    return 'user-students';
                case 'teachers':
                    return 'user-teachers';
                case 'staff':
                    return 'user-staff';
                case 'login_activity':
                    return 'user-login-activity';
                case 'security_settings':
                    return 'user-security-settings';
                case 'audit_logs':
                    return 'user-audit-logs';
                default:
                    return 'users';
            }
        }

        // 16. School Settings & Schools
        if ($class === 'schools' || ($class === 'settings' && $method === 'schools')) {
            return 'schools';
        }
        if ($class === 'settings') {
            if ($method === 'document_design' || $method === 'document_design_save') {
                return 'document-design';
            }
            if ($method === 'staff_documents') {
                return 'staff-document-settings';
            }
            return 'settings';
        }

        // Fallback: sanitized class name
        return str_replace('_', '-', $class);
    }

    /**
     * Standardized JSON output helper for API and AJAX controller responses.
     *
     * @param  mixed $data
     * @param  int   $status_code
     * @return CI_Output
     */
    public function json_output($data, $status_code = 200)
    {
        return $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
