<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Finance_model',
            'Academic_year_model',
            'Student_model',
            'Staff_model',
            'School_model'
        ));

        // Auto-initialize school finance defaults if this school has not been initialized yet
        if (!empty($this->school_id)) {
            $this->Finance_model->initialize_school_finance_defaults($this->school_id);
        }
    }

    // -------------------------------------------------------------------------
    // 1. Dashboard
    // -------------------------------------------------------------------------
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission(array('finance.dashboard.view', 'finance.view'));

        $metrics = $this->Finance_model->get_dashboard_metrics($this->school_id, $this->academic_year_id);
        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);

        $this->render('pages/finance/dashboard', array(
            'title'               => 'Fee & Finance Dashboard',
            'page_key'            => 'finance_dashboard',
            'breadcrumb'          => array('Fee & Finance', 'Dashboard'),
            'metrics'             => $metrics,
            'recent_transactions' => $metrics['recent_transactions'] ?? [],
            'cash_bank_accounts'  => $cash_bank_accounts,
        ));
    }

    // -------------------------------------------------------------------------
    // 2. Account Groups (Chart of Accounts — Level 1)
    // -------------------------------------------------------------------------
    public function account_groups()
    {
        $this->require_permission(array('finance.account_groups.view', 'finance.coa.view', 'finance.view'));

        $groups = $this->Finance_model->get_account_groups($this->school_id);

        $this->render('pages/finance/account_groups', array(
            'title'      => 'Account Groups — College Finance',
            'page_key'   => 'finance_coa_groups',
            'breadcrumb' => array('Fee & Finance', 'College Finance', 'Account Groups'),
            'groups'     => $groups,
            'can_create' => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_groups.create') || $this->rbac->has_permission('finance.coa.create'),
            'can_edit'   => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_groups.edit') || $this->rbac->has_permission('finance.coa.edit'),
            'can_delete' => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_groups.delete') || $this->rbac->has_permission('finance.coa.delete'),
        ));
    }

    public function account_groups_ajax()
    {
        $this->require_permission(array('finance.account_groups.view', 'finance.coa.view', 'finance.view'));

        $groups = $this->Finance_model->get_account_groups($this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'data'      => $groups,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function save_account_group_ajax()
    {
        $id = (int)$this->input->post('id');
        $is_edit = ($id > 0);

        $required_perm = $is_edit ? 'finance.account_groups.edit' : 'finance.account_groups.create';
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission($required_perm) && !$this->rbac->has_permission('finance.coa.create') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied. You do not have permission to ' . ($is_edit ? 'edit' : 'create') . ' account groups.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $group_name   = trim($this->input->post('group_name') ?? '');
        $group_type   = trim($this->input->post('group_type') ?? '');
        $description  = trim($this->input->post('description') ?? '');
        $display_order= (int)$this->input->post('display_order');
        $status       = $this->input->post('status') ? 1 : 0;

        if (empty($group_name)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Group Name is required.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $allowed_types = array('Asset', 'Liability', 'Equity', 'Income', 'Expense');
        if (!in_array($group_type, $allowed_types)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Invalid Group Type selected.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Verify school uniqueness
        if ($this->Finance_model->is_account_group_name_exists($group_name, $this->school_id, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => "Account Group '{$group_name}' already exists in your school.",
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Generate clean code if new
        $group_code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $group_name), 0, 10));
        if (empty($group_code)) {
            $group_code = 'GRP' . rand(100, 999);
        }

        $data = array(
            'school_id'    => $this->school_id,
            'group_name'   => $group_name,
            'group_code'   => $group_code,
            'category'     => $group_type,
            'description'  => !empty($description) ? $description : null,
            'display_order'=> $display_order,
            'status'       => $status,
        );

        if ($is_edit) {
            $existing = $this->Finance_model->get_account_group_by_id($id, $this->school_id);
            if (!$existing) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status'    => 'error',
                        'message'   => 'Account group not found in this school context.',
                        'csrf_hash' => $this->security->get_csrf_hash()
                    )));
                return;
            }
            $this->Finance_model->save_account_group($data, $id);
            $msg = 'Account group updated successfully.';
        } else {
            $id = $this->Finance_model->save_account_group($data);
            $msg = 'Account group created successfully.';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'message'   => $msg,
                'group_id'  => $id,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function toggle_account_group_status_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.account_groups.edit') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $new_status = $this->Finance_model->toggle_account_group_status($id, $this->school_id);

        if ($new_status === false) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Account group not found.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'     => 'success',
                'message'    => 'Account group status updated.',
                'new_status' => $new_status,
                'csrf_hash'  => $this->security->get_csrf_hash()
            )));
    }

    public function delete_account_group_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.account_groups.delete') && !$this->rbac->has_permission('finance.coa.delete')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $res = $this->Finance_model->delete_account_group($id, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => $res['success'] ? 'success' : 'error',
                'message'   => $res['message'],
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    // -------------------------------------------------------------------------
    // 3. Account Heads (Chart of Accounts — Level 2)
    // -------------------------------------------------------------------------
    public function account_heads()
    {
        $this->require_permission(array('finance.account_heads.view', 'finance.coa.view', 'finance.view'));

        $heads = $this->Finance_model->get_account_heads($this->school_id);
        $groups = $this->Finance_model->get_account_groups($this->school_id);

        $this->render('pages/finance/account_heads', array(
            'title'      => 'Account Heads — College Finance',
            'page_key'   => 'finance_coa_heads',
            'breadcrumb' => array('Fee & Finance', 'College Finance', 'Account Heads'),
            'heads'      => $heads,
            'groups'     => $groups,
            'can_create' => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_heads.create') || $this->rbac->has_permission('finance.coa.create'),
            'can_edit'   => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_heads.edit') || $this->rbac->has_permission('finance.coa.edit'),
            'can_delete' => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.account_heads.delete') || $this->rbac->has_permission('finance.coa.delete'),
        ));
    }

    public function account_heads_ajax()
    {
        $this->require_permission(array('finance.account_heads.view', 'finance.coa.view', 'finance.view'));

        $heads = $this->Finance_model->get_account_heads($this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'data'      => $heads,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function generate_account_code_ajax()
    {
        $group_id = (int)$this->input->get('group_id');
        $code = $this->Finance_model->generate_account_code($group_id, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'code'      => $code,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function save_account_head_ajax()
    {
        $id = (int)$this->input->post('id');
        $is_edit = ($id > 0);

        $required_perm = $is_edit ? 'finance.account_heads.edit' : 'finance.account_heads.create';
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission($required_perm) && !$this->rbac->has_permission('finance.coa.create') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $account_group_id    = (int)$this->input->post('account_group_id');
        $account_name        = trim($this->input->post('account_name') ?? '');
        $account_code        = trim($this->input->post('account_code') ?? '');
        $description         = trim($this->input->post('description') ?? '');
        $opening_balance     = (float)$this->input->post('opening_balance');
        $opening_balance_type= trim($this->input->post('opening_balance_type') ?: 'Debit');
        $status              = $this->input->post('status') ? 1 : 0;

        if (empty($account_name) || $account_group_id <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Account Group and Account Head Name are required fields.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Verify group belongs to this school
        $group = $this->Finance_model->get_account_group_by_id($account_group_id, $this->school_id);
        if (!$group) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Invalid Account Group selected.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Auto-generate account code if left empty
        if (empty($account_code)) {
            $account_code = $this->Finance_model->generate_account_code($account_group_id, $this->school_id);
        }

        // Verify unique account code in this school
        if ($this->Finance_model->is_account_code_exists($account_code, $this->school_id, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => "Account Code '{$account_code}' already exists in your school.",
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Verify unique account name in this school
        if ($this->Finance_model->is_account_head_name_exists($account_name, $this->school_id, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => "Account Head '{$account_name}' already exists in your school.",
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Determine account type
        $account_type = $group->category;
        if (!in_array($account_type, array('Cash', 'Bank', 'Receivable', 'Payable', 'Income', 'Expense', 'Equity'))) {
            $account_type = ($group->category === 'Asset') ? 'Cash' : 'Expense';
        }

        $data = array(
            'school_id'            => $this->school_id,
            'account_group_id'     => $account_group_id,
            'account_code'         => $account_code,
            'account_name'         => $account_name,
            'account_type'         => $account_type,
            'opening_balance'      => $opening_balance,
            'opening_balance_type' => in_array($opening_balance_type, array('Debit', 'Credit')) ? $opening_balance_type : 'Debit',
            'description'          => !empty($description) ? $description : null,
            'status'               => $status,
        );

        if ($is_edit) {
            $existing = $this->Finance_model->get_account_by_id($id, $this->school_id);
            if (!$existing) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status'    => 'error',
                        'message'   => 'Account head not found.',
                        'csrf_hash' => $this->security->get_csrf_hash()
                    )));
                return;
            }
            $this->Finance_model->save_account_head($data, $id);
            $msg = 'Account head updated successfully.';
        } else {
            $id = $this->Finance_model->save_account_head($data);
            $msg = 'Account head created successfully.';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'message'   => $msg,
                'head_id'   => $id,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function toggle_account_head_status_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.account_heads.edit') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $new_status = $this->Finance_model->toggle_account_head_status($id, $this->school_id);

        if ($new_status === false) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Account head not found.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'     => 'success',
                'message'    => 'Account head status updated.',
                'new_status' => $new_status,
                'csrf_hash'  => $this->security->get_csrf_hash()
            )));
    }

    public function delete_account_head_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.account_heads.delete') && !$this->rbac->has_permission('finance.coa.delete')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $res = $this->Finance_model->delete_account_head($id, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => $res['success'] ? 'success' : 'error',
                'message'   => $res['message'],
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    // -------------------------------------------------------------------------
    // 4. Custom Accounts (Chart of Accounts — Level 3)
    // -------------------------------------------------------------------------
    public function custom_accounts()
    {
        $this->require_permission(array('finance.custom_accounts.view', 'finance.coa.view', 'finance.view'));

        $custom_accounts = $this->Finance_model->get_custom_accounts($this->school_id);
        $account_heads = $this->Finance_model->get_account_heads($this->school_id);

        $this->render('pages/finance/custom_accounts', array(
            'title'           => 'Custom Accounts — College Finance',
            'page_key'        => 'finance_coa_heads',
            'breadcrumb'      => array('Fee & Finance', 'College Finance', 'Account Heads', 'Custom Accounts'),
            'custom_accounts' => $custom_accounts,
            'account_heads'   => $account_heads,
            'can_create'      => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.custom_accounts.create') || $this->rbac->has_permission('finance.coa.create'),
            'can_edit'        => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.custom_accounts.edit') || $this->rbac->has_permission('finance.coa.edit'),
            'can_delete'      => $this->rbac->is_super_admin() || $this->rbac->has_permission('finance.custom_accounts.delete') || $this->rbac->has_permission('finance.coa.delete'),
        ));
    }

    public function custom_accounts_ajax()
    {
        $this->require_permission(array('finance.custom_accounts.view', 'finance.coa.view', 'finance.view'));

        $custom_accounts = $this->Finance_model->get_custom_accounts($this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => 'success',
                'data'      => $custom_accounts,
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    public function save_custom_account_ajax()
    {
        $id = (int)$this->input->post('id');
        $is_edit = ($id > 0);

        $required_perm = $is_edit ? 'finance.custom_accounts.edit' : 'finance.custom_accounts.create';
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission($required_perm) && !$this->rbac->has_permission('finance.coa.create') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $account_name        = trim($this->input->post('account_name') ?? '');
        $account_head_id     = (int)$this->input->post('account_head_id');
        $account_number      = trim($this->input->post('account_number') ?? '');
        $account_type        = trim($this->input->post('account_type') ?: 'Other');
        $description         = trim($this->input->post('description') ?? '');
        $opening_balance     = (float)$this->input->post('opening_balance');
        $opening_balance_type= trim($this->input->post('opening_balance_type') ?: 'Debit');
        $status              = $this->input->post('status') ? 1 : 0;

        if (empty($account_name) || $account_head_id <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Account Name and Account Head are required fields.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Verify account head belongs to this school
        $head = $this->Finance_model->get_account_by_id($account_head_id, $this->school_id);
        if (!$head) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Invalid Account Head selected.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        // Verify uniqueness of custom account name within this school
        if ($this->Finance_model->is_custom_account_name_exists($account_name, $this->school_id, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => "Custom Account '{$account_name}' already exists in your school.",
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $allowed_types = array('Cash', 'Bank', 'Receivable', 'Payable', 'Other');
        if (!in_array($account_type, $allowed_types)) {
            $account_type = 'Other';
        }

        $data = array(
            'school_id'            => $this->school_id,
            'account_head_id'      => $account_head_id,
            'account_name'         => $account_name,
            'account_number'       => !empty($account_number) ? $account_number : null,
            'account_type'         => $account_type,
            'description'          => !empty($description) ? $description : null,
            'opening_balance'      => $opening_balance,
            'opening_balance_type' => in_array($opening_balance_type, array('Debit', 'Credit')) ? $opening_balance_type : 'Debit',
            'status'               => $status,
        );

        if ($is_edit) {
            $existing = $this->Finance_model->get_custom_account_by_id($id, $this->school_id);
            if (!$existing) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status'    => 'error',
                        'message'   => 'Custom account not found.',
                        'csrf_hash' => $this->security->get_csrf_hash()
                    )));
                return;
            }
            $this->Finance_model->save_custom_account($data, $id);
            $msg = 'Custom account updated successfully.';
        } else {
            $id = $this->Finance_model->save_custom_account($data);
            $msg = 'Custom account created successfully.';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'     => 'success',
                'message'    => $msg,
                'account_id' => $id,
                'csrf_hash'  => $this->security->get_csrf_hash()
            )));
    }

    public function toggle_custom_account_status_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.custom_accounts.edit') && !$this->rbac->has_permission('finance.coa.edit')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $new_status = $this->Finance_model->toggle_custom_account_status($id, $this->school_id);

        if ($new_status === false) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Custom account not found.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'     => 'success',
                'message'    => 'Custom account status updated.',
                'new_status' => $new_status,
                'csrf_hash'  => $this->security->get_csrf_hash()
            )));
    }

    public function delete_custom_account_ajax()
    {
        if (!$this->rbac->is_super_admin() && !$this->rbac->has_permission('finance.custom_accounts.delete') && !$this->rbac->has_permission('finance.coa.delete')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'    => 'error',
                    'message'   => 'Access denied.',
                    'csrf_hash' => $this->security->get_csrf_hash()
                )));
            return;
        }

        $id = (int)$this->input->post('id');
        $res = $this->Finance_model->delete_custom_account($id, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'    => $res['success'] ? 'success' : 'error',
                'message'   => $res['message'],
                'csrf_hash' => $this->security->get_csrf_hash()
            )));
    }

    // Backwards compatibility alias for old accounts route
    public function accounts()
    {
        redirect('finance/account-heads');
    }

    // -------------------------------------------------------------------------
    // 3. Ledgers Directory & Statements
    // -------------------------------------------------------------------------
    public function ledgers()
    {
        $this->require_permission('finance.manage_ledgers');

        $type = $this->input->get('type');
        $search = trim($this->input->get('search') ?? '');

        $filters = array();
        if (!empty($type)) {
            $filters['entity_type'] = $type;
        }
        if (!empty($search)) {
            $filters['search'] = $search;
        }

        $ledgers = $this->Finance_model->get_ledgers($this->school_id, $filters);

        $this->render('pages/finance/ledgers', array(
            'title'       => 'General Ledger Accounts Directory',
            'page_key'    => 'finance_ledgers_general',
            'breadcrumb'  => array('Fee & Finance', 'Accounting & Reports', 'General Ledger'),
            'ledgers'     => $ledgers,
            'active_type' => $type,
            'search'      => $search,
        ));
    }

    public function student_statement($student_id = 0)
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));
        $student_id = (int)$student_id;

        $student = $this->Student_model->get_by_id($student_id, $this->school_id);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('finance/ledger_students');
            return;
        }

        $from_date = $this->input->get('from_date');
        $to_date   = $this->input->get('to_date');

        $statement = $this->Finance_model->get_student_statement_ranged($student_id, $this->school_id, $from_date, $to_date);

        $student_name = $statement['student'] ? ($statement['student']->first_name . ' ' . $statement['student']->last_name) : ($student->first_name . ' ' . $student->last_name);

        $this->render('pages/finance/student_statement', array(
            'title'      => 'Student Ledger — ' . $student_name,
            'page_key'   => 'finance_ledgers_student',
            'breadcrumb' => array('Fee & Finance', 'Student Finance', 'Student Ledger', $student_name),
            'student'    => $statement['student'] ?? $student,
            'statement'  => $statement,
            'from_date'  => $from_date,
            'to_date'    => $to_date,
        ));
    }

    public function staff_statement($staff_id = 0)
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));
        $staff_id = (int)$staff_id;

        $staff = $this->Staff_model->get_by_id($staff_id);
        if (!$staff || (int)$staff->school_id !== $this->school_id) {
            $this->session->set_flashdata('error', 'Staff member not found.');
            redirect('finance/ledger_staff');
            return;
        }

        $from_date = $this->input->get('from_date');
        $to_date   = $this->input->get('to_date');

        $statement = $this->Finance_model->get_staff_statement_ranged($staff_id, $this->school_id, $from_date, $to_date);

        $staff_name = $statement['staff'] ? ($statement['staff']->full_name ?? ($statement['staff']->first_name . ' ' . $statement['staff']->last_name)) : ($staff->full_name ?? ($staff->first_name . ' ' . $staff->last_name));

        $this->render('pages/finance/staff_statement', array(
            'title'      => 'Staff Ledger — ' . $staff_name,
            'page_key'   => 'finance_ledgers_staff',
            'breadcrumb' => array('Fee & Finance', 'Staff Finance', 'Staff Ledger', $staff_name),
            'staff'      => $statement['staff'] ?? $staff,
            'statement'  => $statement,
            'from_date'  => $from_date,
            'to_date'    => $to_date,
        ));
    }

    // -------------------------------------------------------------------------
    // 3B. Dedicated Ledger Sub-Pages
    // -------------------------------------------------------------------------
    public function ledger_students()
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));

        $filters = array(
            'search'      => trim($this->input->get('search') ?? ''),
            'class_id'    => (int)$this->input->get('class_id'),
            'division_id' => (int)$this->input->get('division_id'),
        );

        $ledgers = $this->Finance_model->get_student_ledgers_list($this->school_id, $filters);

        $this->load->model('Class_model');
        $classes = $this->Class_model->get_all(['school_id' => $this->school_id, 'is_deleted' => 'n']);

        // Compute aggregate KPIs
        $total_outstanding = 0;
        $total_count       = count($ledgers);
        $credit_count      = 0;
        foreach ($ledgers as $l) {
            if ($l->current_balance > 0) $total_outstanding += $l->current_balance;
            if ($l->current_balance <= 0) $credit_count++;
        }

        $this->render('pages/finance/ledger_students', array(
            'title'             => 'Student Ledger — Student Finance',
            'page_key'          => 'finance_ledgers_student',
            'breadcrumb'        => array('Fee & Finance', 'Student Finance', 'Student Ledger'),
            'ledgers'           => $ledgers,
            'classes'           => $classes,
            'filters'           => $filters,
            'total_count'       => $total_count,
            'total_outstanding' => $total_outstanding,
            'credit_count'      => $credit_count,
        ));
    }

    public function ledger_staff()
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));

        $filters = array(
            'search' => trim($this->input->get('search') ?? ''),
        );

        $ledgers = $this->Finance_model->get_staff_ledgers_list($this->school_id, $filters);

        $total_payable = 0;
        $total_count   = count($ledgers);
        foreach ($ledgers as $l) {
            if ($l->current_balance > 0) $total_payable += $l->current_balance;
        }

        $this->render('pages/finance/ledger_staff', array(
            'title'         => 'Staff Ledger — Staff Finance',
            'page_key'      => 'finance_ledgers_staff',
            'breadcrumb'    => array('Fee & Finance', 'Staff Finance', 'Staff Ledger'),
            'ledgers'       => $ledgers,
            'filters'       => $filters,
            'total_count'   => $total_count,
            'total_payable' => $total_payable,
        ));
    }

    public function ledger_other_parties()
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));

        // Handle POST: create new other-party ledger
        if ($this->input->method() === 'post') {
            $action     = $this->input->post('action');
            $party_name = trim($this->input->post('party_name'));
            $party_type = trim($this->input->post('party_type') ?: 'Vendor');
            $contact    = trim($this->input->post('contact'));
            $notes      = trim($this->input->post('notes'));

            if ($action === 'create_party' && !empty($party_name)) {
                $ledger = $this->Finance_model->get_or_create_other_party_ledger($this->school_id, $party_name, $party_type, $contact ?: null, $notes ?: null);
                if ($ledger) {
                    $this->session->set_flashdata('success', "Ledger '{$party_name}' created with code {$ledger->ledger_code}.");
                } else {
                    $this->session->set_flashdata('error', 'Failed to create ledger. Check the party name.');
                }
            }
            redirect('finance/ledger_other_parties');
            return;
        }

        $filters = array(
            'search'     => trim($this->input->get('search') ?? ''),
            'party_type' => trim($this->input->get('party_type') ?? ''),
        );

        $ledgers = $this->Finance_model->get_other_party_ledgers_list($this->school_id, $filters);

        $total_payable = 0;
        $total_count   = count($ledgers);
        foreach ($ledgers as $l) {
            if ($l->current_balance > 0) $total_payable += $l->current_balance;
        }

        $this->render('pages/finance/ledger_other_parties', array(
            'title'         => 'Vendors & Payables Ledgers — College Finance',
            'page_key'      => 'finance_exp_vendor',
            'breadcrumb'    => array('Fee & Finance', 'College Finance', 'Vendors & Payables', 'Other Party Ledgers'),
            'ledgers'       => $ledgers,
            'filters'       => $filters,
            'total_count'   => $total_count,
            'total_payable' => $total_payable,
        ));
    }

    public function other_party_statement($ledger_id = 0)
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));
        $ledger_id = (int)$ledger_id;

        $ledger = $this->Finance_model->get_ledger_by_id($ledger_id, $this->school_id);
        if (!$ledger) {
            $this->session->set_flashdata('error', 'Ledger account not found.');
            redirect('finance/ledger_other_parties');
            return;
        }

        $from_date = $this->input->get('from_date');
        $to_date   = $this->input->get('to_date');

        $statement = $this->Finance_model->get_other_party_statement($ledger_id, $this->school_id, $from_date, $to_date);

        $this->render('pages/finance/other_party_statement', array(
            'title'      => 'Vendor Ledger Statement — ' . $ledger->ledger_name,
            'page_key'   => 'finance_exp_vendor',
            'breadcrumb' => array('Fee & Finance', 'College Finance', 'Vendors & Payables', $ledger->ledger_name),
            'ledger'     => $ledger,
            'statement'  => $statement,
            'from_date'  => $from_date,
            'to_date'    => $to_date,
        ));
    }

    public function ledger_general()
    {
        $this->require_permission(array('finance.ledgers.view', 'finance.manage_ledgers', 'finance.view'));

        $account_id = (int)$this->input->get('account_id');
        $from_date  = $this->input->get('from_date') ?: date('Y-m-01');
        $to_date    = $this->input->get('to_date')   ?: date('Y-m-d');

        $all_accounts = $this->Finance_model->get_accounts($this->school_id);

        $statement    = null;
        $selected_account = null;

        if ($account_id > 0) {
            $statement = $this->Finance_model->get_general_ledger_statement($account_id, $this->school_id, $from_date, $to_date);
            $selected_account = $statement ? $statement['account'] : null;
        }

        $this->render('pages/finance/ledger_general', array(
            'title'            => 'General Ledger — Accounting & Reports',
            'page_key'         => 'finance_ledgers_general',
            'breadcrumb'       => array('Fee & Finance', 'Accounting & Reports', 'General Ledger'),
            'all_accounts'     => $all_accounts,
            'account_id'       => $account_id,
            'selected_account' => $selected_account,
            'statement'        => $statement,
            'from_date'        => $from_date,
            'to_date'          => $to_date,
        ));
    }

    // -------------------------------------------------------------------------
    // =========================================================================
    // 4. CASH & BANK MANAGEMENT (3 Submodules: Cash, Bank, Transfers)
    // =========================================================================

    /**
     * Submodule 1: Cash Accounts
     */
    public function cash_accounts()
    {
        $this->require_permission(array('finance.cash_accounts.view', 'finance.manage_cash_bank', 'finance.view'));

        $filters = array(
            'search' => trim($this->input->get('search') ?? ''),
            'status' => trim($this->input->get('status') ?? ''),
        );

        $stats        = $this->Finance_model->get_cash_bank_kpi_stats($this->school_id, 'Cash', $this->academic_year_id);
        $accounts     = $this->Finance_model->get_cash_accounts_list($this->school_id, $filters);
        $parent_heads = $this->Finance_model->get_accounts_by_category('Asset', $this->school_id);

        $this->render('pages/finance/cash_accounts', array(
            'title'        => 'Cash Accounts — College Finance',
            'page_key'     => 'finance_cb_cash',
            'breadcrumb'   => array('Fee & Finance', 'College Finance', 'Cash Accounts'),
            'stats'        => $stats,
            'accounts'     => $accounts,
            'parent_heads' => $parent_heads,
            'filters'      => $filters,
        ));
    }

    /**
     * Submodule 2: Bank Accounts
     */
    public function bank_accounts()
    {
        $this->require_permission(array('finance.bank_accounts.view', 'finance.manage_cash_bank', 'finance.view'));

        $filters = array(
            'search'            => trim($this->input->get('search') ?? ''),
            'bank_name'         => trim($this->input->get('bank_name') ?? ''),
            'bank_account_type' => trim($this->input->get('bank_account_type') ?? ''),
            'status'            => trim($this->input->get('status') ?? ''),
        );

        $stats        = $this->Finance_model->get_cash_bank_kpi_stats($this->school_id, 'Bank', $this->academic_year_id);
        $accounts     = $this->Finance_model->get_bank_accounts_list($this->school_id, $filters);
        $parent_heads = $this->Finance_model->get_accounts_by_category('Asset', $this->school_id);

        $this->render('pages/finance/bank_accounts', array(
            'title'        => 'Bank Accounts — College Finance',
            'page_key'     => 'finance_cb_bank',
            'breadcrumb'   => array('Fee & Finance', 'College Finance', 'Bank Accounts'),
            'stats'        => $stats,
            'accounts'     => $accounts,
            'parent_heads' => $parent_heads,
            'filters'      => $filters,
        ));
    }

    /**
     * Backward-compatible route for /finance/cash_bank
     */
    public function cash_bank()
    {
        redirect('finance/cash_accounts');
    }

    /**
     * Save / Update Cash Account
     */
    public function save_cash_account()
    {
        $account_id = (int)$this->input->post('account_id');
        $perm_needed = ($account_id > 0) ? 'finance.cash_accounts.edit' : 'finance.cash_accounts.create';
        $this->require_permission(array($perm_needed, 'finance.manage_cash_bank', 'finance.view'));

        $user_id = $this->session->userdata('user_id');
        $res = $this->Finance_model->save_cash_account($this->school_id, $this->input->post(), $user_id, $account_id);

        if ($this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')->set_output(json_encode($res));
        }

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('finance/cash_accounts');
    }

    /**
     * Save / Update Bank Account
     */
    public function save_bank_account()
    {
        $account_id = (int)$this->input->post('account_id');
        $perm_needed = ($account_id > 0) ? 'finance.bank_accounts.edit' : 'finance.bank_accounts.create';
        $this->require_permission(array($perm_needed, 'finance.manage_cash_bank', 'finance.view'));

        $user_id = $this->session->userdata('user_id');
        $res = $this->Finance_model->save_bank_account($this->school_id, $this->input->post(), $user_id, $account_id);

        if ($this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')->set_output(json_encode($res));
        }

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('finance/bank_accounts');
    }

    /**
     * Toggle Active/Inactive status for Cash or Bank Account
     */
    public function toggle_account_status()
    {
        $this->require_permission(array('finance.cash_accounts.delete', 'finance.bank_accounts.delete', 'finance.manage_cash_bank', 'finance.view'));

        $account_id = (int)$this->input->post('account_id');
        $user_id = $this->session->userdata('user_id');
        $res = $this->Finance_model->toggle_account_status($this->school_id, $account_id, $user_id);

        if ($this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')->set_output(json_encode($res));
        }

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        $referer = $this->input->server('HTTP_REFERER') ?: site_url('finance/cash_accounts');
        redirect($referer);
    }

    /**
     * AJAX: View Account Details with recent lines
     */
    public function view_account_ajax($account_id)
    {
        $this->require_permission(array('finance.cash_accounts.view', 'finance.bank_accounts.view', 'finance.manage_cash_bank', 'finance.view'));
        $data = $this->Finance_model->get_account_detailed($this->school_id, (int)$account_id);

        if (!$data) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Account not found']));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * AJAX: Account Ledger Statement
     */
    public function account_ledger_ajax($account_id)
    {
        $this->require_permission(array('finance.cash_accounts.view', 'finance.bank_accounts.view', 'finance.manage_cash_bank', 'finance.view'));

        $date_from = $this->input->get('date_from');
        $date_to   = $this->input->get('date_to');

        $statement = $this->Finance_model->get_book_statement((int)$account_id, $this->school_id, $date_from, $date_to);

        if (!$statement) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Account not found']));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode($statement));
    }

    /**
     * Submodule 3: Inter-Account Transfers
     */
    public function transfers()
    {
        $this->require_permission(array('finance.transfers.view', 'finance.cash_bank.transfer', 'finance.manage_cash_bank', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.transfers.create', 'finance.cash_bank.transfer', 'finance.manage_cash_bank', 'finance.view'));

            $from_account_id = (int)$this->input->post('from_account_id');
            $to_account_id   = (int)$this->input->post('to_account_id');
            $amount          = round((float)$this->input->post('amount'), 2);
            $transfer_date   = trim($this->input->post('transfer_date') ?: date('Y-m-d'));
            $reference_no    = trim($this->input->post('reference_no'));
            $description     = trim($this->input->post('description') ?: $this->input->post('notes'));

            if ($from_account_id <= 0 || $to_account_id <= 0 || $amount <= 0) {
                $this->session->set_flashdata('error', 'Please provide valid Source Account, Destination Account, and positive Amount.');
                redirect('finance/transfers');
                return;
            }

            if ($from_account_id === $to_account_id) {
                $this->session->set_flashdata('error', 'Source Account and Destination Account cannot be the same account.');
                redirect('finance/transfers');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id    = $this->session->userdata('user_id');

            $result = $this->Finance_model->record_transfer(
                $this->school_id,
                $from_account_id,
                $to_account_id,
                $amount,
                $transfer_date,
                $reference_no,
                $description,
                $user_id,
                $this->academic_year_id,
                $attachment,
                $description
            );

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/transfers');
            return;
        }

        $filters = array(
            'search'          => trim($this->input->get('search') ?? ''),
            'from_account_id' => (int)$this->input->get('from_account_id'),
            'to_account_id'   => (int)$this->input->get('to_account_id'),
            'date_from'       => trim($this->input->get('from_date') ?? ''),
            'date_to'         => trim($this->input->get('to_date') ?? ''),
            'status'          => trim($this->input->get('status') ?? ''),
        );

        $transfers          = $this->Finance_model->get_filtered_transfers($this->school_id, $filters, $this->academic_year_id);
        $active_accounts    = $this->Finance_model->get_cash_and_bank_accounts($this->school_id, 1);
        $all_accounts       = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $stats              = $this->Finance_model->get_transfers_kpi_stats($this->school_id, $this->academic_year_id);

        $this->render('pages/finance/transfers', array(
            'title'              => 'Transfers — College Finance',
            'page_key'           => 'finance_cb_transfers',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Transfers'),
            'transfers'          => $transfers,
            'cash_bank_accounts' => $active_accounts,
            'all_accounts'       => $all_accounts,
            'stats'              => $stats,
            'filters'            => $filters,
        ));
    }

    /**
     * AJAX: View Transfer Voucher Details with journal lines
     */
    public function view_transfer_ajax($transfer_id)
    {
        $this->require_permission(array('finance.transfers.view', 'finance.cash_bank.transfer', 'finance.manage_cash_bank', 'finance.view'));
        $data = $this->Finance_model->get_transfer_detailed($this->school_id, (int)$transfer_id);

        if (!$data) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Transfer record not found']));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Reversal of posted fund transfer
     */
    public function reverse_transfer()
    {
        $this->require_permission(array('finance.transfers.delete', 'finance.manage_cash_bank', 'finance.view'));

        $transfer_id = (int)$this->input->post('transfer_id');
        $reason      = trim($this->input->post('reason'));
        $user_id     = $this->session->userdata('user_id');

        if ($transfer_id <= 0 || empty($reason)) {
            $this->session->set_flashdata('error', 'Please provide a valid reversal reason.');
            redirect('finance/transfers');
            return;
        }

        $res = $this->Finance_model->reverse_transfer($this->school_id, $transfer_id, $reason, $user_id);

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('finance/transfers');
    }

    // -------------------------------------------------------------------------
    // 5. EXPENSES MODULE (4 Submodules: Entry, Staff Payout, Vendor, Other)
    // -------------------------------------------------------------------------

    /**
     * Submodule 1: Expense Entry
     */
    public function expenses()
    {
        $this->require_permission(array('finance.expenses.view', 'finance.manage_expenses', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.expenses.create', 'finance.manage_expenses', 'finance.view'));

            $amount               = (float)$this->input->post('amount');
            $expense_account_id   = (int)$this->input->post('expense_account_id');
            $payment_account_id   = (int)$this->input->post('paid_from_account_id');
            $expense_date         = trim($this->input->post('expense_date') ?: date('Y-m-d'));
            $payment_mode         = trim($this->input->post('payment_method') ?: 'Cash');
            $reference_no         = trim($this->input->post('reference_no'));
            $payee_name           = trim($this->input->post('payee_name') ?: $this->input->post('title'));
            $party_type           = trim($this->input->post('party_type') ?: 'General');
            $party_ledger_id      = (int)$this->input->post('party_ledger_id');
            $expense_type_id      = (int)$this->input->post('expense_type_id');
            $description          = trim($this->input->post('description'));

            if ($amount <= 0 || $payment_account_id <= 0 || $expense_account_id <= 0) {
                $this->session->set_flashdata('error', 'Please fill in all mandatory fields: Amount, Expense Account, and Paid From Account.');
                redirect('finance/expenses');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'   => $this->academic_year_id,
                'submodule'          => 'Expense_Entry',
                'amount'             => $amount,
                'expense_account_id' => $expense_account_id,
                'payment_account_id' => $payment_account_id,
                'expense_date'       => $expense_date,
                'payment_mode'       => $payment_mode,
                'reference_no'       => $reference_no,
                'payee_name'         => !empty($payee_name) ? $payee_name : 'General',
                'party_type'         => $party_type,
                'party_ledger_id'    => $party_ledger_id,
                'expense_type_id'    => $expense_type_id,
                'description'        => $description,
                'attachment'         => $attachment,
            );

            $result = $this->Finance_model->record_expense_voucher($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Expense voucher {$result['expense_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/expenses');
            return;
        }

        $filters = array(
            'academic_year_id'   => $this->academic_year_id,
            'search'             => trim($this->input->get('search') ?? ''),
            'date_from'          => trim($this->input->get('from_date') ?? ''),
            'date_to'            => trim($this->input->get('to_date') ?? ''),
            'expense_account_id' => (int)$this->input->get('expense_account_id'),
            'payment_account_id' => (int)$this->input->get('payment_account_id'),
            'payment_mode'       => trim($this->input->get('payment_method') ?? ''),
            'status'             => trim($this->input->get('status') ?? ''),
        );

        $stats              = $this->Finance_model->get_expenses_module_stats($this->school_id, 'Expense_Entry', $this->academic_year_id);
        $expenses           = $this->Finance_model->get_filtered_expenses($this->school_id, 'Expense_Entry', $filters);
        $expense_types      = $this->Finance_model->get_expense_types($this->school_id);
        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $expense_accounts   = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);
        $party_ledgers      = $this->Finance_model->get_other_party_ledgers_list($this->school_id);
        $staff_members      = $this->db->select('staff_id, employee_code, full_name')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('full_name', 'ASC')
                                       ->get('tbl_staff')->result();

        $this->render('pages/finance/expenses', array(
            'title'              => 'Expenses — College Finance',
            'page_key'           => 'finance_exp_entry',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Expenses'),
            'stats'              => $stats,
            'expenses'           => $expenses,
            'expense_types'      => $expense_types,
            'cash_bank_accounts' => $cash_bank_accounts,
            'expense_accounts'   => $expense_accounts,
            'party_ledgers'      => $party_ledgers,
            'staff_members'      => $staff_members,
            'filters'            => $filters,
        ));
    }

    /**
     * Phase 1 Placeholder: Salary Setup (Staff Finance)
     */
    public function salary_setup()
    {
        $this->require_permission(array('finance.view'));

        $this->render('pages/finance/placeholder', array(
            'title'       => 'Salary Setup — Staff Finance',
            'module_name' => 'Salary Setup',
            'group_name'  => 'STAFF FINANCE',
            'badge'       => 'NEW',
            'description' => 'Staff salary structure, pay grades, and basic/allowance components configuration.',
            'page_key'    => 'finance_salary_setup',
            'breadcrumb'  => array('Fee & Finance', 'Staff Finance', 'Salary Setup'),
        ));
    }

    /**
     * Phase 1 Placeholder: Salary Processing (Staff Finance)
     */
    public function salary_processing()
    {
        $this->require_permission(array('finance.view'));

        $this->render('pages/finance/placeholder', array(
            'title'       => 'Salary Processing — Staff Finance',
            'module_name' => 'Salary Processing',
            'group_name'  => 'STAFF FINANCE',
            'badge'       => 'NEW',
            'description' => 'Monthly staff payroll calculation, attendance deductions, and payslip generation.',
            'page_key'    => 'finance_salary_processing',
            'breadcrumb'  => array('Fee & Finance', 'Staff Finance', 'Salary Processing'),
        ));
    }

    /**
     * Phase 1 Placeholder: Salary Payable (Staff Finance)
     */
    public function salary_payable()
    {
        $this->require_permission(array('finance.view'));

        $this->render('pages/finance/placeholder', array(
            'title'       => 'Salary Payable — Staff Finance',
            'module_name' => 'Salary Payable',
            'group_name'  => 'STAFF FINANCE',
            'badge'       => 'NEW',
            'description' => 'Approved salary payable liabilities, pending disbursements, and staff clearance registers.',
            'page_key'    => 'finance_salary_payable',
            'breadcrumb'  => array('Fee & Finance', 'Staff Finance', 'Salary Payable'),
        ));
    }

    /**
     * Submodule 2: Staff Payout / Salary Payment
     */
    public function staff_payouts()
    {
        $this->require_permission(array('finance.staff_payouts.view', 'finance.expenses.view', 'finance.manage_expenses', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.staff_payouts.create', 'finance.expenses.create', 'finance.manage_expenses', 'finance.view'));

            $staff_id             = (int)$this->input->post('staff_id');
            $payout_type          = trim($this->input->post('payout_type') ?: 'Salary');
            $amount               = (float)$this->input->post('amount');
            $payment_account_id   = (int)$this->input->post('paid_from_account_id');
            $expense_account_id   = (int)$this->input->post('expense_account_id');
            $expense_date         = trim($this->input->post('payout_date') ?: date('Y-m-d'));
            $payment_mode         = trim($this->input->post('payment_method') ?: 'Bank Transfer');
            $reference_no         = trim($this->input->post('reference_no'));
            $description          = trim($this->input->post('remarks') ?: $this->input->post('description'));

            if ($staff_id <= 0 || $amount <= 0 || $payment_account_id <= 0) {
                $this->session->set_flashdata('error', 'Please provide Staff Member, Amount, and Paid From Account.');
                redirect('finance/staff_payouts');
                return;
            }

            $staff = $this->Staff_model->get_by_id($staff_id);
            if (!$staff) {
                $this->session->set_flashdata('error', 'Staff member not found.');
                redirect('finance/staff_payouts');
                return;
            }

            // Resolve default expense/liability account if not chosen
            if ($expense_account_id <= 0) {
                if ($payout_type === 'Advance') {
                    $staff_payable = $this->Finance_model->get_account_by_code('2010', $this->school_id);
                    $expense_account_id = $staff_payable ? (int)$staff_payable->id : 0;
                } else {
                    $salary_acc = $this->Finance_model->get_account_by_code('5010', $this->school_id);
                    $expense_account_id = $salary_acc ? (int)$salary_acc->id : 0;
                }
                if ($expense_account_id <= 0) {
                    $any_exp = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);
                    $expense_account_id = !empty($any_exp) ? (int)$any_exp[0]->id : 0;
                }
            }

            $staff_name = $staff->full_name ?? ($staff->first_name . ' ' . $staff->last_name);
            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'   => $this->academic_year_id,
                'submodule'          => 'Staff_Payout',
                'amount'             => $amount,
                'expense_account_id' => $expense_account_id,
                'payment_account_id' => $payment_account_id,
                'staff_id'           => $staff_id,
                'payout_type'        => $payout_type,
                'payee_name'         => $staff_name,
                'expense_date'       => $expense_date,
                'payment_mode'       => $payment_mode,
                'reference_no'       => $reference_no,
                'description'        => "Staff {$payout_type} - {$staff_name}" . (!empty($description) ? ": {$description}" : ''),
                'attachment'         => $attachment,
            );

            $result = $this->Finance_model->record_expense_voucher($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Staff payout of ₹" . number_format($amount, 2) . " processed successfully! Voucher: {$result['expense_number']}");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/staff_payouts');
            return;
        }

        $filters = array(
            'academic_year_id'   => $this->academic_year_id,
            'search'             => trim($this->input->get('search') ?? ''),
            'date_from'          => trim($this->input->get('from_date') ?? ''),
            'date_to'            => trim($this->input->get('to_date') ?? ''),
            'staff_id'           => (int)$this->input->get('staff_id'),
            'payout_type'        => trim($this->input->get('payout_type') ?? ''),
            'payment_account_id' => (int)$this->input->get('payment_account_id'),
            'payment_mode'       => trim($this->input->get('payment_method') ?? ''),
            'status'             => trim($this->input->get('status') ?? ''),
        );

        $stats              = $this->Finance_model->get_expenses_module_stats($this->school_id, 'Staff_Payout', $this->academic_year_id);
        $payouts            = $this->Finance_model->get_filtered_expenses($this->school_id, 'Staff_Payout', $filters);
        $staff_members      = $this->Staff_model->get_all(array('school_id' => $this->school_id, 'status' => 1));
        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $expense_accounts   = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);

        $this->render('pages/finance/staff_payouts', array(
            'title'              => 'Salary Payment — Staff Finance',
            'page_key'           => 'finance_exp_payout',
            'breadcrumb'         => array('Fee & Finance', 'Staff Finance', 'Salary Payment'),
            'stats'              => $stats,
            'payouts'            => $payouts,
            'staff_members'      => $staff_members,
            'cash_bank_accounts' => $cash_bank_accounts,
            'expense_accounts'   => $expense_accounts,
            'filters'            => $filters,
        ));
    }

    /**
     * Submodule 3: Vendor Payment
     */
    public function vendor_payments()
    {
        $this->require_permission(array('finance.vendor_payments.view', 'finance.expenses.view', 'finance.manage_expenses', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.vendor_payments.create', 'finance.expenses.create', 'finance.manage_expenses', 'finance.view'));

            $amount               = (float)$this->input->post('amount');
            $payment_account_id   = (int)$this->input->post('paid_from_account_id');
            $debit_account_id     = (int)$this->input->post('debit_account_id');
            $vendor_name          = trim($this->input->post('vendor_name'));
            $vendor_id            = (int)$this->input->post('vendor_id');
            $expense_date         = trim($this->input->post('payment_date') ?: date('Y-m-d'));
            $payment_mode         = trim($this->input->post('payment_method') ?: 'Bank Transfer');
            $reference_no         = trim($this->input->post('reference_no'));
            $description          = trim($this->input->post('description'));

            if ($amount <= 0 || $payment_account_id <= 0 || (empty($vendor_name) && $vendor_id <= 0)) {
                $this->session->set_flashdata('error', 'Please provide Vendor, Amount, and Paid From Account.');
                redirect('finance/vendor_payments');
                return;
            }

            // If existing vendor ledger selected, grab name
            if ($vendor_id > 0) {
                $vl = $this->db->get_where('tbl_finance_ledgers', ['id' => $vendor_id, 'school_id' => $this->school_id])->row();
                if ($vl) {
                    $vendor_name = $vl->ledger_name;
                }
            }

            // Default debit account to Accounts Payable (2020) or general operational expense if unselected
            if ($debit_account_id <= 0) {
                $ap_acc = $this->Finance_model->get_account_by_code('2020', $this->school_id);
                $debit_account_id = $ap_acc ? (int)$ap_acc->id : 0;
                if ($debit_account_id <= 0) {
                    $any_exp = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);
                    $debit_account_id = !empty($any_exp) ? (int)$any_exp[0]->id : 0;
                }
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'   => $this->academic_year_id,
                'submodule'          => 'Vendor_Payment',
                'amount'             => $amount,
                'expense_account_id' => $debit_account_id,
                'payment_account_id' => $payment_account_id,
                'vendor_id'          => $vendor_id,
                'payee_name'         => $vendor_name,
                'expense_date'       => $expense_date,
                'payment_mode'       => $payment_mode,
                'reference_no'       => $reference_no,
                'description'        => "Vendor Payment - {$vendor_name}" . (!empty($description) ? ": {$description}" : ''),
                'attachment'         => $attachment,
            );

            $result = $this->Finance_model->record_expense_voucher($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Vendor payment {$result['expense_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/vendor_payments');
            return;
        }

        $filters = array(
            'academic_year_id'   => $this->academic_year_id,
            'search'             => trim($this->input->get('search') ?? ''),
            'date_from'          => trim($this->input->get('from_date') ?? ''),
            'date_to'            => trim($this->input->get('to_date') ?? ''),
            'vendor_id'          => (int)$this->input->get('vendor_id'),
            'payment_account_id' => (int)$this->input->get('payment_account_id'),
            'payment_mode'       => trim($this->input->get('payment_method') ?? ''),
            'status'             => trim($this->input->get('status') ?? ''),
        );

        $stats              = $this->Finance_model->get_expenses_module_stats($this->school_id, 'Vendor_Payment', $this->academic_year_id);
        $payments           = $this->Finance_model->get_filtered_expenses($this->school_id, 'Vendor_Payment', $filters);
        $vendors            = $this->Finance_model->get_other_party_ledgers_list($this->school_id, 'Vendor');
        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $expense_accounts   = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);
        $payable_accounts   = $this->Finance_model->get_accounts_by_category('Liability', $this->school_id);

        $this->render('pages/finance/vendor_payments', array(
            'title'              => 'Vendors & Payables — College Finance',
            'page_key'           => 'finance_exp_vendor',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Vendors & Payables'),
            'stats'              => $stats,
            'payments'           => $payments,
            'vendors'            => $vendors,
            'cash_bank_accounts' => $cash_bank_accounts,
            'expense_accounts'   => $expense_accounts,
            'payable_accounts'   => $payable_accounts,
            'filters'            => $filters,
        ));
    }

    /**
     * Submodule 4: Other Expenses
     */
    public function other_expenses()
    {
        $this->require_permission(array('finance.other_expenses.view', 'finance.expenses.view', 'finance.manage_expenses', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.other_expenses.create', 'finance.expenses.create', 'finance.manage_expenses', 'finance.view'));

            $amount               = (float)$this->input->post('amount');
            $expense_account_id   = (int)$this->input->post('expense_account_id');
            $payment_account_id   = (int)$this->input->post('paid_from_account_id');
            $reason               = trim($this->input->post('reason'));
            $payee_name           = trim($this->input->post('payee_name') ?: 'Miscellaneous Payee');
            $expense_date         = trim($this->input->post('expense_date') ?: date('Y-m-d'));
            $payment_mode         = trim($this->input->post('payment_method') ?: 'Cash');
            $reference_no         = trim($this->input->post('reference_no'));
            $description          = trim($this->input->post('description'));

            if ($amount <= 0 || $payment_account_id <= 0 || $expense_account_id <= 0 || empty($reason)) {
                $this->session->set_flashdata('error', 'Please fill in all mandatory fields: Amount, Expense Account, Paid From Account, and Reason.');
                redirect('finance/other_expenses');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'   => $this->academic_year_id,
                'submodule'          => 'Other_Expense',
                'amount'             => $amount,
                'expense_account_id' => $expense_account_id,
                'payment_account_id' => $payment_account_id,
                'reason'             => $reason,
                'payee_name'         => $payee_name,
                'expense_date'       => $expense_date,
                'payment_mode'       => $payment_mode,
                'reference_no'       => $reference_no,
                'description'        => $description,
                'attachment'         => $attachment,
            );

            $result = $this->Finance_model->record_expense_voucher($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Other expense voucher {$result['expense_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/other_expenses');
            return;
        }

        $filters = array(
            'academic_year_id'   => $this->academic_year_id,
            'search'             => trim($this->input->get('search') ?? ''),
            'date_from'          => trim($this->input->get('from_date') ?? ''),
            'date_to'            => trim($this->input->get('to_date') ?? ''),
            'expense_account_id' => (int)$this->input->get('expense_account_id'),
            'payment_account_id' => (int)$this->input->get('payment_account_id'),
            'payment_mode'       => trim($this->input->get('payment_method') ?? ''),
            'status'             => trim($this->input->get('status') ?? ''),
        );

        $stats              = $this->Finance_model->get_expenses_module_stats($this->school_id, 'Other_Expense', $this->academic_year_id);
        $expenses           = $this->Finance_model->get_filtered_expenses($this->school_id, 'Other_Expense', $filters);
        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $expense_accounts   = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);

        $this->render('pages/finance/other_expenses', array(
            'title'              => 'Other Expenses — College Finance',
            'page_key'           => 'finance_exp_entry',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Expenses', 'Other Expenses'),
            'stats'              => $stats,
            'expenses'           => $expenses,
            'cash_bank_accounts' => $cash_bank_accounts,
            'expense_accounts'   => $expense_accounts,
            'filters'            => $filters,
        ));
    }

    /**
     * AJAX: View Expense Voucher Details
     */
    public function view_expense_ajax($id)
    {
        $this->require_permission(array('finance.expenses.view', 'finance.manage_expenses', 'finance.view'));

        $id = (int)$id;
        $exp = $this->Finance_model->get_expense_detailed($this->school_id, $id);

        if (!$exp) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Expense voucher not found.']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $exp]));
    }

    /**
     * Reverse Expense Voucher (Opposite Double-Entry Posting)
     */
    public function reverse_expense()
    {
        $this->require_permission(array('finance.expenses.delete', 'finance.manage_expenses', 'finance.view'));

        $expense_id = (int)$this->input->post('expense_id');
        $reason     = trim($this->input->post('reversal_reason'));
        $return_url = $this->input->post('return_url') ?: 'finance/expenses';

        if ($expense_id <= 0 || empty($reason)) {
            if ($this->input->is_ajax_request()) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'A reversal reason is strictly mandatory.']));
            }
            $this->session->set_flashdata('error', 'A reversal reason is strictly mandatory.');
            redirect($return_url);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $result = $this->Finance_model->reverse_expense_voucher($this->school_id, $expense_id, $reason, $user_id);

        if ($this->input->is_ajax_request()) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        }

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        redirect($return_url);
    }

    // -------------------------------------------------------------------------
    // 7. Expense Types
    // -------------------------------------------------------------------------
    public function expense_types()
    {
        $this->require_permission('finance.manage_expenses');

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'delete') {
                $id = (int)$this->input->post('expense_type_id');
                $this->db->where('id', $id)->where('school_id', $this->school_id)->update('tbl_finance_expense_types', array('is_deleted' => 'y'));
                $this->session->set_flashdata('success', 'Expense category deleted successfully.');
                redirect('finance/expense_types');
                return;
            }

            $id = (int)$this->input->post('expense_type_id');
            $name = trim($this->input->post('name'));
            $account_id = (int)$this->input->post('account_id') ?: null;
            $description = trim($this->input->post('description'));

            if (empty($name)) {
                $this->session->set_flashdata('error', 'Category name is required.');
                redirect('finance/expense_types');
                return;
            }

            $type_code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $name));

            $data = array(
                'school_id'                  => $this->school_id,
                'type_name'                  => $name,
                'type_code'                  => $type_code,
                'default_expense_account_id' => $account_id,
                'description'                => $description,
                'status'                     => $this->input->post('is_active') ? 1 : 0,
            );

            $this->Finance_model->save_expense_type($data, ($id > 0) ? $id : null);
            $this->session->set_flashdata('success', ($id > 0) ? 'Expense category updated.' : 'Expense category created.');
            redirect('finance/expense_types');
            return;
        }

        $expense_types = $this->Finance_model->get_expense_types($this->school_id);
        $expense_accounts = $this->Finance_model->get_accounts($this->school_id, array('group_type' => 'Expense'));

        $this->render('pages/finance/expense_types', array(
            'title'            => 'Expense Categories — College Finance',
            'page_key'         => 'finance_exp_entry',
            'breadcrumb'       => array('Fee & Finance', 'College Finance', 'Expenses', 'Categories'),
            'expense_types'    => $expense_types,
            'expense_accounts' => $expense_accounts,
        ));
    }

    // -------------------------------------------------------------------------
    // 8. TRANSACTIONS MODULE
    // -------------------------------------------------------------------------

    private function _handle_attachment_upload($field_name = 'attachment')
    {
        if (empty($_FILES[$field_name]['name'])) {
            return null;
        }

        $upload_dir = FCPATH . 'uploads/finance/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $config['upload_path']   = $upload_dir;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png|webp|doc|docx|xls|xlsx';
        $config['max_size']      = 5120; // 5MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $data = $this->upload->data();
            return 'uploads/finance/' . $data['file_name'];
        }

        return null;
    }

    public function transactions_income()
    {
        $this->require_permission(array('finance.transactions.view', 'finance.transactions.create', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.transactions.create', 'finance.view'));

            $amount             = (float)$this->input->post('amount');
            $deposit_account_id = (int)$this->input->post('deposit_account_id');
            $income_account_id  = (int)$this->input->post('income_account_id');
            $transaction_date   = trim($this->input->post('transaction_date') ?: date('Y-m-d'));
            $payment_method     = trim($this->input->post('payment_method') ?: 'Cash');
            $reference_no       = trim($this->input->post('reference_no'));
            $party_type         = trim($this->input->post('party_type') ?: 'General');
            $party_id           = (int)$this->input->post('party_id');
            $party_name         = trim($this->input->post('party_name'));
            $description        = trim($this->input->post('description'));

            if ($amount <= 0 || $deposit_account_id <= 0 || $income_account_id <= 0) {
                $this->session->set_flashdata('error', 'Please fill in all required fields: Amount, Cash/Bank Account, and Income Account.');
                redirect('finance/transactions_income');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'   => $this->academic_year_id,
                'amount'             => $amount,
                'deposit_account_id' => $deposit_account_id,
                'income_account_id'  => $income_account_id,
                'transaction_date'   => $transaction_date,
                'payment_method'     => $payment_method,
                'reference_no'       => $reference_no,
                'party_type'         => $party_type,
                'party_id'           => $party_id,
                'party_name'         => $party_name,
                'description'        => $description,
                'attachment'         => $attachment,
            );

            $result = $this->Finance_model->record_income($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Income transaction {$result['transaction_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/transactions_income');
            return;
        }

        $filters = array(
            'search'           => trim($this->input->get('search') ?? ''),
            'from_date'        => trim($this->input->get('from_date') ?? ''),
            'to_date'          => trim($this->input->get('to_date') ?? ''),
            'payment_method'   => trim($this->input->get('payment_method') ?? ''),
            'status'           => trim($this->input->get('status') ?? ''),
            'academic_year_id' => $this->academic_year_id,
        );

        $stats = $this->Finance_model->get_transaction_stats($this->school_id, 'Income', $this->academic_year_id);
        $transactions = $this->Finance_model->get_filtered_transactions($this->school_id, 'Income', $filters, 100);

        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $income_accounts    = $this->Finance_model->get_accounts_by_category('Income', $this->school_id);
        $students           = $this->db->select('student_id, admission_number, first_name, last_name, class_id')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('first_name', 'ASC')
                                       ->get('tbl_students')->result();
        $staff_members      = $this->db->select('staff_id, employee_code, full_name')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('full_name', 'ASC')
                                       ->get('tbl_staff')->result();
        $party_ledgers      = $this->Finance_model->get_other_party_ledgers_list($this->school_id);

        $this->render('pages/finance/transactions_income', array(
            'title'              => 'Income — College Finance',
            'page_key'           => 'finance_txn_income',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Income'),
            'stats'              => $stats,
            'transactions'       => $transactions,
            'cash_bank_accounts' => $cash_bank_accounts,
            'income_accounts'    => $income_accounts,
            'students'           => $students,
            'staff_members'      => $staff_members,
            'party_ledgers'      => $party_ledgers,
            'filters'            => $filters,
        ));
    }

    public function transactions_expense()
    {
        $this->require_permission(array('finance.transactions.view', 'finance.transactions.create', 'finance.view'));

        if ($this->input->method() === 'post') {
            $this->require_permission(array('finance.transactions.create', 'finance.view'));

            $amount               = (float)$this->input->post('amount');
            $paid_from_account_id = (int)$this->input->post('paid_from_account_id');
            $expense_account_id   = (int)$this->input->post('expense_account_id');
            $transaction_date     = trim($this->input->post('transaction_date') ?: date('Y-m-d'));
            $payment_method       = trim($this->input->post('payment_method') ?: 'Cash');
            $reference_no         = trim($this->input->post('reference_no'));
            $party_type           = trim($this->input->post('party_type') ?: 'General');
            $party_id             = (int)$this->input->post('party_id');
            $party_name           = trim($this->input->post('party_name'));
            $description          = trim($this->input->post('description'));

            if ($amount <= 0 || $paid_from_account_id <= 0 || $expense_account_id <= 0) {
                $this->session->set_flashdata('error', 'Please fill in all required fields: Amount, Paid From Account, and Expense Account.');
                redirect('finance/transactions_expense');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'     => $this->academic_year_id,
                'amount'               => $amount,
                'paid_from_account_id' => $paid_from_account_id,
                'expense_account_id'   => $expense_account_id,
                'transaction_date'     => $transaction_date,
                'payment_method'       => $payment_method,
                'reference_no'         => $reference_no,
                'party_type'           => $party_type,
                'party_id'             => $party_id,
                'party_name'           => $party_name,
                'description'          => $description,
                'attachment'           => $attachment,
            );

            $result = $this->Finance_model->record_expense_transaction($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Expense transaction {$result['transaction_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/transactions_expense');
            return;
        }

        $filters = array(
            'search'           => trim($this->input->get('search') ?? ''),
            'from_date'        => trim($this->input->get('from_date') ?? ''),
            'to_date'          => trim($this->input->get('to_date') ?? ''),
            'payment_method'   => trim($this->input->get('payment_method') ?? ''),
            'status'           => trim($this->input->get('status') ?? ''),
            'academic_year_id' => $this->academic_year_id,
        );

        $stats = $this->Finance_model->get_transaction_stats($this->school_id, 'Expense', $this->academic_year_id);
        $transactions = $this->Finance_model->get_filtered_transactions($this->school_id, 'Expense', $filters, 100);

        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $expense_accounts   = $this->Finance_model->get_accounts_by_category('Expense', $this->school_id);
        $staff_members      = $this->db->select('staff_id, employee_code, full_name')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('full_name', 'ASC')
                                       ->get('tbl_staff')->result();
        $party_ledgers      = $this->Finance_model->get_other_party_ledgers_list($this->school_id);

        $this->render('pages/finance/transactions_expense', array(
            'title'              => 'Expense Transactions — College Finance',
            'page_key'           => 'finance_exp_entry',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Expenses', 'Transactions'),
            'stats'              => $stats,
            'transactions'       => $transactions,
            'cash_bank_accounts' => $cash_bank_accounts,
            'expense_accounts'   => $expense_accounts,
            'staff_members'      => $staff_members,
            'party_ledgers'      => $party_ledgers,
            'filters'            => $filters,
        ));
    }

    public function adjustments()
    {
        $this->require_permission(array('finance.transactions.create', 'finance.transactions.view', 'finance.view'));

        if ($this->input->method() === 'post') {
            $account_id        = (int)$this->input->post('account_id');
            $offset_account_id = (int)$this->input->post('offset_account_id');
            $amount            = (float)$this->input->post('amount');
            $adjustment_type   = trim($this->input->post('adjustment_type') ?: 'Debit_Adjustment');
            $transaction_date  = trim($this->input->post('transaction_date') ?: date('Y-m-d'));
            $reference_no      = trim($this->input->post('reference_no'));
            $reason            = trim($this->input->post('reason'));
            $narration         = trim($this->input->post('narration'));

            if ($account_id <= 0 || $offset_account_id <= 0 || $amount <= 0 || empty($reason)) {
                $this->session->set_flashdata('error', 'Please fill in Target Account, Offsetting Account, valid Amount, and mandatory Reason.');
                redirect('finance/adjustments');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'  => $this->academic_year_id,
                'account_id'        => $account_id,
                'offset_account_id' => $offset_account_id,
                'adjustment_type'   => $adjustment_type,
                'amount'            => $amount,
                'transaction_date'  => $transaction_date,
                'reference_no'      => $reference_no,
                'reason'            => $reason,
                'narration'         => $narration,
                'attachment'        => $attachment,
            );

            $result = $this->Finance_model->record_adjustment($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Adjustment {$result['transaction_number']} of ₹" . number_format($amount, 2) . " posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/adjustments');
            return;
        }

        $filters = array(
            'search'           => trim($this->input->get('search') ?? ''),
            'from_date'        => trim($this->input->get('from_date') ?? ''),
            'to_date'          => trim($this->input->get('to_date') ?? ''),
            'status'           => trim($this->input->get('status') ?? ''),
            'academic_year_id' => $this->academic_year_id,
        );

        $stats = $this->Finance_model->get_transaction_stats($this->school_id, 'Adjustment', $this->academic_year_id);
        $transactions = $this->Finance_model->get_filtered_transactions($this->school_id, 'Adjustment', $filters, 100);
        $all_accounts = $this->Finance_model->get_accounts($this->school_id);

        $this->render('pages/finance/adjustments', array(
            'title'        => 'Adjustments & Refunds — College Finance',
            'page_key'     => 'finance_txn_adjustment',
            'breadcrumb'   => array('Fee & Finance', 'College Finance', 'Adjustments / Refunds'),
            'stats'        => $stats,
            'transactions' => $transactions,
            'all_accounts' => $all_accounts,
            'filters'      => $filters,
        ));
    }

    public function refunds()
    {
        $this->require_permission(array('finance.transactions.reverse', 'finance.transactions.create', 'finance.transactions.view', 'finance.view'));

        if ($this->input->method() === 'post') {
            $refund_account_id    = (int)$this->input->post('refund_account_id');
            $paid_from_account_id = (int)$this->input->post('paid_from_account_id');
            $amount               = (float)$this->input->post('amount');
            $transaction_date     = trim($this->input->post('transaction_date') ?: date('Y-m-d'));
            $payment_method       = trim($this->input->post('payment_method') ?: 'Cash');
            $reference_no         = trim($this->input->post('reference_no'));
            $party_type           = trim($this->input->post('party_type') ?: 'Student');
            $party_id             = (int)$this->input->post('party_id');
            $party_name           = trim($this->input->post('party_name'));
            $reason               = trim($this->input->post('reason'));
            $description          = trim($this->input->post('description'));

            if ($amount <= 0 || $refund_account_id <= 0 || $paid_from_account_id <= 0 || empty($reason)) {
                $this->session->set_flashdata('error', 'Please fill in Refund Account, Cash/Bank Account, valid Amount, and mandatory Reason.');
                redirect('finance/refunds');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $data = array(
                'academic_year_id'     => $this->academic_year_id,
                'refund_account_id'    => $refund_account_id,
                'paid_from_account_id' => $paid_from_account_id,
                'amount'               => $amount,
                'transaction_date'     => $transaction_date,
                'payment_method'       => $payment_method,
                'reference_no'         => $reference_no,
                'party_type'           => $party_type,
                'party_id'             => $party_id,
                'party_name'           => $party_name,
                'reason'               => $reason,
                'description'          => $description,
                'attachment'           => $attachment,
            );

            $result = $this->Finance_model->record_refund($this->school_id, $data, $user_id);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Refund {$result['transaction_number']} of ₹" . number_format($amount, 2) . " processed successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/refunds');
            return;
        }

        $filters = array(
            'search'           => trim($this->input->get('search') ?? ''),
            'from_date'        => trim($this->input->get('from_date') ?? ''),
            'to_date'          => trim($this->input->get('to_date') ?? ''),
            'payment_method'   => trim($this->input->get('payment_method') ?? ''),
            'status'           => trim($this->input->get('status') ?? ''),
            'academic_year_id' => $this->academic_year_id,
        );

        $stats = $this->Finance_model->get_transaction_stats($this->school_id, 'Refund', $this->academic_year_id);
        $transactions = $this->Finance_model->get_filtered_transactions($this->school_id, 'Refund', $filters, 100);

        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $refund_accounts    = $this->Finance_model->get_accounts($this->school_id);
        $students           = $this->db->select('student_id, admission_number, first_name, last_name, class_id')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('first_name', 'ASC')
                                       ->get('tbl_students')->result();
        $staff_members      = $this->db->select('staff_id, employee_code, full_name')
                                       ->where('school_id', $this->school_id)
                                       ->where('is_deleted', 'n')
                                       ->where('status', 1)
                                       ->order_by('full_name', 'ASC')
                                       ->get('tbl_staff')->result();
        $party_ledgers      = $this->Finance_model->get_other_party_ledgers_list($this->school_id);

        $this->render('pages/finance/refunds', array(
            'title'              => 'Refunds — College Finance',
            'page_key'           => 'finance_txn_adjustment',
            'breadcrumb'         => array('Fee & Finance', 'College Finance', 'Adjustments / Refunds', 'Refunds'),
            'stats'              => $stats,
            'transactions'       => $transactions,
            'cash_bank_accounts' => $cash_bank_accounts,
            'refund_accounts'    => $refund_accounts,
            'students'           => $students,
            'staff_members'      => $staff_members,
            'party_ledgers'      => $party_ledgers,
            'filters'            => $filters,
        ));
    }

    public function view_transaction_ajax($transaction_id = 0)
    {
        $this->require_permission(array('finance.transactions.view', 'finance.view'));
        $transaction_id = (int)$transaction_id;

        $txn = $this->Finance_model->get_transaction_by_id_detailed($transaction_id, $this->school_id);
        if (!$txn) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Transaction not found.'
            )));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success'     => true,
            'transaction' => $txn
        )));
    }

    public function journal_entries()
    {
        $this->require_permission(array('finance.transactions.create', 'finance.manage_journal', 'finance.transactions.view', 'finance.view'));

        if ($this->input->method() === 'post') {
            $transaction_date = trim($this->input->post('transaction_date') ?: date('Y-m-d'));
            $reference_no     = trim($this->input->post('reference_no'));
            $narration        = trim($this->input->post('narration'));

            $accounts     = $this->input->post('account_id');
            $ledgers      = $this->input->post('ledger_id');
            $debits       = $this->input->post('debit');
            $credits      = $this->input->post('credit');
            $descriptions = $this->input->post('description');

            if (empty($accounts) || !is_array($accounts)) {
                $this->session->set_flashdata('error', 'Please enter at least two journal transaction lines.');
                redirect('finance/journal_entries');
                return;
            }

            $lines = array();
            $total_debit = 0.0;
            $total_credit = 0.0;

            for ($i = 0; $i < count($accounts); $i++) {
                $acc_id = (int)($accounts[$i] ?? 0);
                if ($acc_id <= 0) continue;

                $d = (float)($debits[$i] ?? 0);
                $c = (float)($credits[$i] ?? 0);

                if ($d <= 0 && $c <= 0) continue;

                $is_deb = ($d > 0);
                $amt = $is_deb ? $d : $c;

                $total_debit += $d;
                $total_credit += $c;

                $lines[] = array(
                    'account_id'  => $acc_id,
                    'ledger_id'   => !empty($ledgers[$i]) ? (int)$ledgers[$i] : null,
                    'entry_type'  => $is_deb ? 'Debit' : 'Credit',
                    'amount'      => $amt,
                    'description' => trim($descriptions[$i] ?? ''),
                );
            }

            if (count($lines) < 2) {
                $this->session->set_flashdata('error', 'A journal entry must contain at least one Debit and one Credit line.');
                redirect('finance/journal_entries');
                return;
            }

            if (abs($total_debit - $total_credit) > 0.001) {
                $this->session->set_flashdata('error', 'Journal entry must be balanced! Total Debit (₹' . number_format($total_debit, 2) . ') does not equal Total Credit (₹' . number_format($total_credit, 2) . ').');
                redirect('finance/journal_entries');
                return;
            }

            $attachment = $this->_handle_attachment_upload('attachment');
            $user_id = $this->session->userdata('user_id');

            $result = $this->Finance_model->post_double_entry_transaction(
                $this->school_id,
                array(
                    'academic_year_id' => $this->academic_year_id,
                    'transaction_type' => 'Journal_Entry',
                    'custom_prefix'    => 'JRN-',
                    'reference_type'   => 'Manual',
                    'reference_id'     => null,
                    'transaction_date' => $transaction_date,
                    'reference_no'     => $reference_no,
                    'attachment'       => $attachment,
                    'description'      => $narration,
                    'created_by'       => $user_id,
                ),
                $lines
            );

            if ($result['success']) {
                $this->session->set_flashdata('success', "Journal voucher {$result['transaction_number']} posted successfully!");
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }
            redirect('finance/journal_entries');
            return;
        }

        $filters = array(
            'search'           => trim($this->input->get('search') ?? ''),
            'from_date'        => trim($this->input->get('from_date') ?? ''),
            'to_date'          => trim($this->input->get('to_date') ?? ''),
            'status'           => trim($this->input->get('status') ?? ''),
            'academic_year_id' => $this->academic_year_id,
        );

        $stats = $this->Finance_model->get_transaction_stats($this->school_id, 'Journal_Entry', $this->academic_year_id);
        $transactions = $this->Finance_model->get_filtered_transactions($this->school_id, 'Journal_Entry', $filters, 100);

        $all_accounts = $this->Finance_model->get_accounts($this->school_id);
        $all_ledgers  = $this->Finance_model->get_ledgers($this->school_id);

        $this->render('pages/finance/journal_entries', array(
            'title'        => 'Journal Entries — Accounting & Reports',
            'page_key'     => 'finance_txn_journal',
            'breadcrumb'   => array('Fee & Finance', 'Accounting & Reports', 'Journal Entries'),
            'stats'        => $stats,
            'transactions' => $transactions,
            'all_accounts' => $all_accounts,
            'all_ledgers'  => $all_ledgers,
            'filters'      => $filters,
        ));
    }

    public function reverse_transaction()
    {
        $this->require_permission(array('finance.transactions.reverse', 'finance.manage_journal', 'finance.transactions.create', 'finance.view'));

        $is_ajax = $this->input->is_ajax_request();

        if ($this->input->method() !== 'post') {
            if ($is_ajax) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => false, 'message' => 'Invalid request method.')));
            }
            redirect('finance/journal_entries');
            return;
        }

        $transaction_id = (int)$this->input->post('transaction_id');
        $reason = trim($this->input->post('reason'));
        $return_url = trim($this->input->post('return_url') ?: 'finance/journal_entries');
        $user_id = $this->session->userdata('user_id');

        if (empty($reason)) {
            if ($is_ajax) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => false, 'message' => 'A reason is required to reverse an accounting transaction.')));
            }
            $this->session->set_flashdata('error', 'A reason is required to reverse an accounting transaction.');
            redirect($return_url);
            return;
        }

        $result = $this->Finance_model->reverse_transaction($this->school_id, $transaction_id, $reason, $user_id);
        if ($result['success']) {
            if ($is_ajax) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => true, 'message' => $result['message'])));
            }
            $this->session->set_flashdata('success', $result['message']);
        } else {
            if ($is_ajax) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => false, 'message' => $result['message'])));
            }
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect($return_url);
    }

    // -------------------------------------------------------------------------
    // 9. Financial Reports
    // -------------------------------------------------------------------------
    public function reports($report_type = 'trial_balance')
    {
        $this->require_permission('finance.reports');

        $allowed_reports = array('trial_balance', 'income_expense', 'balance_sheet', 'day_book', 'cash_book', 'bank_book');
        if (!in_array($report_type, $allowed_reports)) {
            $report_type = 'trial_balance';
        }

        $as_of_date = $this->input->get('as_of_date') ?: date('Y-m-d');
        $from_date = $this->input->get('from_date') ?: date('Y-m-01');
        $to_date = $this->input->get('to_date') ?: date('Y-m-d');
        $account_id = (int)$this->input->get('account_id');

        $report_data = array();

        switch ($report_type) {
            case 'trial_balance':
                $report_data = $this->Finance_model->get_trial_balance($this->school_id, $as_of_date);
                break;
            case 'income_expense':
                $report_data = $this->Finance_model->get_income_expense_statement($this->school_id, $from_date, $to_date);
                break;
            case 'balance_sheet':
                $report_data = $this->Finance_model->get_balance_sheet($this->school_id, $as_of_date);
                break;
            case 'day_book':
                $report_data = $this->Finance_model->get_day_book($this->school_id, $to_date);
                break;
            case 'cash_book':
                if ($account_id <= 0) {
                    $cash_acc = $this->Finance_model->get_account_by_code('1010', $this->school_id);
                    $account_id = $cash_acc ? (int)$cash_acc->id : 0;
                }
                $report_data = $this->Finance_model->get_book_statement($account_id, $this->school_id, $from_date, $to_date);
                break;
            case 'bank_book':
                if ($account_id <= 0) {
                    $bank_acc = $this->Finance_model->get_account_by_code('1020', $this->school_id);
                    $account_id = $bank_acc ? (int)$bank_acc->id : 0;
                }
                $report_data = $this->Finance_model->get_book_statement($account_id, $this->school_id, $from_date, $to_date);
                break;
        }

        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);

        $this->render('pages/finance/reports', array(
            'title'              => 'Financial Reports — Accounting & Reports',
            'page_key'           => 'finance_reports',
            'breadcrumb'         => array('Fee & Finance', 'Accounting & Reports', 'Financial Reports'),
            'report_type'        => $report_type,
            'report_data'        => $report_data,
            'as_of_date'         => $as_of_date,
            'from_date'          => $from_date,
            'to_date'            => $to_date,
            'account_id'         => $account_id,
            'cash_bank_accounts' => $cash_bank_accounts,
        ));
    }

    // -------------------------------------------------------------------------
    // 10. Fee Types
    // -------------------------------------------------------------------------
    public function fee_types()
    {
        $this->require_permission('finance.fees.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('finance.fees.create');
            $action = $this->input->post('action');
            if ($action === 'delete') {
                $this->require_permission('finance.fees.delete');
                $id = (int)$this->input->post('id');
                $this->Finance_model->delete_fee_type($id, $this->school_id);
                $this->session->set_flashdata('success', 'Fee type removed successfully.');
                redirect('finance/fee_types');
                return;
            }

            $data = [
                'id'          => $this->input->post('id'),
                'type_name'   => trim($this->input->post('type_name')),
                'type_code'   => trim($this->input->post('type_code')),
                'account_id'  => (int)$this->input->post('account_id'),
                'description' => trim($this->input->post('description')),
                'status'      => $this->input->post('status') ? 1 : 0,
            ];

            if (empty($data['type_name']) || empty($data['type_code']) || $data['account_id'] <= 0) {
                $this->session->set_flashdata('error', 'Please fill in Name, Code, and select an Income Account Head.');
            } else {
                $this->Finance_model->save_fee_type($data, $this->school_id);
                $this->session->set_flashdata('success', 'Fee type saved successfully.');
            }
            redirect('finance/fee_types');
            return;
        }

        $fee_types = $this->Finance_model->get_fee_types($this->school_id);
        $income_accounts = $this->Finance_model->get_accounts_by_category('Income', $this->school_id);

        $this->render('pages/finance/fee_types', [
            'title'           => 'Fee Types — Student Finance',
            'page_key'        => 'finance_fee_types',
            'breadcrumb'      => ['Fee & Finance', 'Student Finance', 'Fee Types'],
            'fee_types'       => $fee_types,
            'income_accounts' => $income_accounts,
        ]);
    }

    // -------------------------------------------------------------------------
    // 11. Fee Structures
    // -------------------------------------------------------------------------
    public function fee_structures()
    {
        $this->require_permission('finance.fees.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('finance.fees.create');
            $action = $this->input->post('action');
            if ($action === 'delete') {
                $this->require_permission('finance.fees.delete');
                $id = (int)$this->input->post('id');
                $this->Finance_model->delete_fee_structure($id, $this->school_id);
                $this->session->set_flashdata('success', 'Fee structure deleted successfully.');
                redirect('finance/fee_structures');
                return;
            }

            $data = [
                'id'               => $this->input->post('id'),
                'academic_year_id' => $this->academic_year_id,
                'class_id'         => (int)$this->input->post('class_id'),
                'fee_type_id'      => (int)$this->input->post('fee_type_id'),
                'structure_name'   => trim($this->input->post('structure_name')),
                'amount'           => (float)$this->input->post('amount'),
                'frequency'        => $this->input->post('frequency') ?: 'Annual',
                'due_date'         => $this->input->post('due_date') ?: null,
                'status'           => $this->input->post('status') ? 1 : 0,
            ];

            if (empty($data['structure_name']) || $data['class_id'] <= 0 || $data['fee_type_id'] <= 0 || $data['amount'] <= 0) {
                $this->session->set_flashdata('error', 'Please fill in Structure Name, Class, Fee Type, and valid Amount.');
            } else {
                $this->Finance_model->save_fee_structure($data, $this->school_id);
                $this->session->set_flashdata('success', 'Fee structure saved successfully.');
            }
            redirect('finance/fee_structures');
            return;
        }

        $structures = $this->Finance_model->get_fee_structures($this->school_id, $this->academic_year_id);
        $fee_types = $this->Finance_model->get_fee_types($this->school_id);
        $this->load->model('Class_model');
        $classes = $this->Class_model->get_all(['school_id' => $this->school_id, 'is_deleted' => 'n']);

        $this->render('pages/finance/fee_structures', [
            'title'      => 'Fee Structures — Student Finance',
            'page_key'   => 'finance_fee_structures',
            'breadcrumb' => ['Fee & Finance', 'Student Finance', 'Fee Structures'],
            'structures' => $structures,
            'fee_types'  => $fee_types,
            'classes'    => $classes,
        ]);
    }

    // -------------------------------------------------------------------------
    // 12. Fee Assignments
    // -------------------------------------------------------------------------
    public function fee_assignments()
    {
        $this->require_permission('finance.fees.assign');

        if ($this->input->method() === 'post') {
            $fee_structure_id = (int)$this->input->post('fee_structure_id');
            $class_id         = (int)$this->input->post('class_id');
            $division_id      = (int)$this->input->post('division_id');
            $due_date         = $this->input->post('due_date');
            $selected_students = $this->input->post('student_ids');

            $student_ids = [];
            if (!empty($selected_students) && is_array($selected_students)) {
                $student_ids = array_map('intval', $selected_students);
            } elseif ($class_id > 0) {
                // Fetch all students in this class/division
                $this->db->select('student_id')
                         ->where('school_id', $this->school_id)
                         ->where('class_id', $class_id)
                         ->where('is_deleted', 'n');
                if ($division_id > 0) {
                    $this->db->where('division_id', $division_id);
                }
                $rows = $this->db->get('tbl_students')->result();
                $student_ids = array_column($rows, 'student_id');
            }

            if (empty($student_ids) || $fee_structure_id <= 0) {
                $this->session->set_flashdata('error', 'Please select a fee structure and at least one student or class.');
            } else {
                $res = $this->Finance_model->assign_fee_structure_to_students(
                    $this->school_id,
                    $this->academic_year_id,
                    $student_ids,
                    $fee_structure_id,
                    $due_date,
                    $this->user_id
                );
                if ($res['success']) {
                    $this->session->set_flashdata('success', $res['message']);
                } else {
                    $this->session->set_flashdata('error', $res['message']);
                }
            }
            redirect('finance/fee_assignments');
            return;
        }

        $assignments = $this->Finance_model->get_fee_assignments($this->school_id, $this->academic_year_id);
        $structures = $this->Finance_model->get_fee_structures($this->school_id, $this->academic_year_id);
        $this->load->model('Class_model');
        $classes = $this->Class_model->get_all(['school_id' => $this->school_id, 'is_deleted' => 'n']);

        $this->render('pages/finance/fee_assignments', [
            'title'       => 'Fee Assignment — Student Finance',
            'page_key'    => 'finance_fee_assignments',
            'breadcrumb'  => ['Fee & Finance', 'Student Finance', 'Fee Assignment'],
            'assignments' => $assignments,
            'structures'  => $structures,
            'classes'     => $classes,
        ]);
    }

    // -------------------------------------------------------------------------
    // 13. Fee Collection
    // -------------------------------------------------------------------------
    public function fee_collection()
    {
        $this->require_permission('finance.fees.collect');

        if ($this->input->method() === 'post') {
            $payment_data = [
                'student_id'         => (int)$this->input->post('student_id'),
                'fee_assignment_id'  => (int)$this->input->post('fee_assignment_id'),
                'amount'             => (float)$this->input->post('amount'),
                'payment_mode'       => $this->input->post('payment_mode') ?: 'Cash',
                'deposit_account_id' => (int)$this->input->post('deposit_account_id'),
                'payment_date'       => $this->input->post('payment_date') ?: date('Y-m-d'),
                'reference_number'   => trim($this->input->post('reference_number')),
                'remarks'            => trim($this->input->post('remarks')),
                'academic_year_id'   => $this->academic_year_id,
            ];

            $res = $this->Finance_model->collect_fee_payment($payment_data, $this->school_id, $this->user_id);
            if ($res['success']) {
                $this->session->set_flashdata('success', "Payment of ₹" . number_format($payment_data['amount'], 2) . " recorded successfully. Receipt #: " . $res['receipt_number']);
            } else {
                $this->session->set_flashdata('error', $res['message']);
            }
            redirect('finance/fee_receipts');
            return;
        }

        $student_id = (int)$this->input->get('student_id');
        $student = null;
        $student_fees = [];
        $fee_summary = [
            'total_applicable' => 0.00,
            'total_paid'       => 0.00,
            'total_due'        => 0.00,
        ];

        if ($student_id > 0) {
            $student = $this->Student_model->get_by_id($student_id, $this->school_id);
            if (!$student) {
                $this->session->set_flashdata('error', 'Student #' . $student_id . ' was not found or does not belong to the active school.');
            } else {
                $all_assignments = $this->Finance_model->get_fee_assignments(
                    $this->school_id,
                    $this->academic_year_id,
                    ['student_id' => $student_id]
                );

                if (!empty($all_assignments)) {
                    foreach ($all_assignments as $fa) {
                        $fee_summary['total_applicable'] += (float)$fa->net_amount;
                        $fee_summary['total_paid']       += (float)$fa->paid_amount;
                        $fee_summary['total_due']        += (float)$fa->due_amount;
                        if ($fa->status === 'Pending' || (float)$fa->due_amount > 0) {
                            $student_fees[] = $fa;
                        }
                    }
                }
            }
        }

        $cash_bank_accounts = $this->Finance_model->get_cash_and_bank_accounts($this->school_id);
        $recent_collections = $this->Finance_model->get_fee_collections($this->school_id, $this->academic_year_id);

        $this->render('pages/finance/fee_collection', [
            'title'              => 'Fee Collection — Student Finance',
            'page_key'           => 'finance_fee_collection',
            'breadcrumb'         => ['Fee & Finance', 'Student Finance', 'Fee Collection'],
            'student'            => $student,
            'student_fees'       => $student_fees,
            'fee_summary'        => $fee_summary,
            'cash_bank_accounts' => $cash_bank_accounts,
            'recent_collections' => array_slice($recent_collections, 0, 10),
        ]);
    }

    // -------------------------------------------------------------------------
    // 14. Pending Fees / Outstanding Dues
    // -------------------------------------------------------------------------
    public function pending_fees()
    {
        $this->require_permission('finance.fees.view');

        $pending_fees = $this->Finance_model->get_pending_fees($this->school_id, $this->academic_year_id);

        $this->render('pages/finance/pending_fees', [
            'title'        => 'Outstanding Dues — Student Finance',
            'page_key'     => 'finance_fee_pending',
            'breadcrumb'   => ['Fee & Finance', 'Student Finance', 'Outstanding Dues'],
            'pending_fees' => $pending_fees,
        ]);
    }

    // -------------------------------------------------------------------------
    // 15. Fee Receipts
    // -------------------------------------------------------------------------
    public function fee_receipts()
    {
        $this->require_permission('finance.fees.collect');

        $collections = $this->Finance_model->get_fee_collections($this->school_id, $this->academic_year_id);

        $this->render('pages/finance/fee_receipts', [
            'title'       => 'Receipts — Student Finance',
            'page_key'    => 'finance_fee_receipts',
            'breadcrumb'  => ['Fee & Finance', 'Student Finance', 'Receipts'],
            'collections' => $collections,
        ]);
    }
}
