<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Finance_model
 *
 * Core accounting & financial management engine for Login2 School Management.
 * Enforces strict multi-school tenant isolation, academic year scoping,
 * double-entry bookkeeping (Total Debits = Total Credits), sub-ledgers,
 * expense management, staff payouts, cash/bank accounts, and financial reports.
 */
class Finance_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =========================================================================
    // 1. SCHOOL INITIALIZATION & SEEDING (Used on School Creation)
    // =========================================================================

    /**
     * Initialize system account groups, Chart of Accounts, and expense types for a new or existing school.
     *
     * @param int $school_id
     * @return bool
     */
    public function initialize_school_finance_defaults($school_id)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) return false;

        $this->db->trans_start();

        // 1. Account Groups
        $groups = [
            ['Assets', 'ASSET', 'Asset', 'Current and fixed assets including cash, bank, receivables'],
            ['Liabilities', 'LIAB', 'Liability', 'Obligations including payables and advances'],
            ['Equity', 'EQUITY', 'Equity', 'Capital and retained earnings'],
            ['Income', 'INC', 'Income', 'Revenue from school fees and other receipts'],
            ['Expenses', 'EXP', 'Expense', 'Operational, administrative, and payroll expenses']
        ];

        $group_ids = [];
        foreach ($groups as $g) {
            $existing = $this->db->select('id')
                                 ->from('tbl_finance_account_groups')
                                 ->where('school_id', $school_id)
                                 ->where('group_code', $g[1])
                                 ->where('is_deleted', 'n')
                                 ->get()->row();
            if ($existing) {
                $group_ids[$g[1]] = (int)$existing->id;
            } else {
                $this->db->insert('tbl_finance_account_groups', [
                    'school_id'   => $school_id,
                    'group_name'  => $g[0],
                    'group_code'  => $g[1],
                    'category'    => $g[2],
                    'description' => $g[3],
                    'is_system'   => 1,
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);
                $group_ids[$g[1]] = (int)$this->db->insert_id();
            }
        }

        // 2. Chart of Accounts
        $standard_accounts = [
            // Assets
            ['Cash in Hand', '1010', 'Cash', 'ASSET', 'Primary cash on hand for school operations', 'Debit', 0.00],
            ['School Bank Account', '1020', 'Bank', 'ASSET', 'Primary institutional operational bank account', 'Debit', 0.00],
            ['Student Accounts Receivable', '1030', 'Receivable', 'ASSET', 'Master control account for student fees receivable', 'Debit', 0.00],
            // Liabilities
            ['Staff Payable', '2010', 'Payable', 'LIAB', 'Master control account for staff salaries and dues', 'Credit', 0.00],
            ['Vendor Payable', '2020', 'Payable', 'LIAB', 'Master control account for supplier and vendor dues', 'Credit', 0.00],
            // Equity
            ['Retained Surplus / Capital', '3010', 'Equity', 'EQUITY', 'Accumulated school surplus and reserves', 'Credit', 0.00],
            // Income
            ['Tuition Fee Income', '4010', 'Income', 'INC', 'Revenue generated from academic tuition fees', 'Credit', 0.00],
            ['Term Fee Income', '4020', 'Income', 'INC', 'Revenue from term and semester charges', 'Credit', 0.00],
            ['Admission Fee Income', '4030', 'Income', 'INC', 'Revenue from student admissions and enrollments', 'Credit', 0.00],
            ['Examination Fee Income', '4040', 'Income', 'INC', 'Revenue from examination and assessment charges', 'Credit', 0.00],
            ['Transport Fee Income', '4050', 'Income', 'INC', 'Revenue from school bus and transportation services', 'Credit', 0.00],
            ['Other Fee Income', '4090', 'Income', 'INC', 'Miscellaneous fee receipts and charges', 'Credit', 0.00],
            // Expenses
            ['Staff Salary Expense', '5010', 'Expense', 'EXP', 'Teaching and non-teaching personnel payroll', 'Debit', 0.00],
            ['Electricity & Utilities', '5020', 'Expense', 'EXP', 'Electricity, power, and utility bills', 'Debit', 0.00],
            ['Water & Sanitation', '5030', 'Expense', 'EXP', 'Water supply, sewage, and drinking water facilities', 'Debit', 0.00],
            ['Internet & Communications', '5040', 'Expense', 'EXP', 'Broadband, telephone, SMS, and connectivity charges', 'Debit', 0.00],
            ['Stationery & Printing', '5050', 'Expense', 'EXP', 'Office supplies, exam sheets, and general stationery', 'Debit', 0.00],
            ['Repairs & Maintenance', '5060', 'Expense', 'EXP', 'Building, electrical, and campus infrastructure maintenance', 'Debit', 0.00],
            ['Events & Functions', '5070', 'Expense', 'EXP', 'Annual day, sports day, and student extracurricular activities', 'Debit', 0.00],
            ['General Operating Expenses', '5090', 'Expense', 'EXP', 'Miscellaneous school operational disbursements', 'Debit', 0.00],
        ];

        $acc_map = [];
        foreach ($standard_accounts as $acc) {
            $gid = $group_ids[$acc[3]] ?? null;
            if (!$gid) continue;

            $existing = $this->db->select('id')
                                 ->from('tbl_finance_accounts')
                                 ->where('school_id', $school_id)
                                 ->where('account_code', $acc[1])
                                 ->where('is_deleted', 'n')
                                 ->get()->row();
            if ($existing) {
                $acc_map[$acc[1]] = (int)$existing->id;
            } else {
                $this->db->insert('tbl_finance_accounts', [
                    'school_id'            => $school_id,
                    'account_group_id'     => $gid,
                    'account_name'         => $acc[0],
                    'account_code'         => $acc[1],
                    'account_type'         => $acc[2],
                    'description'          => $acc[4],
                    'opening_balance'      => $acc[6],
                    'opening_balance_type' => $acc[5],
                    'is_system'            => 1,
                    'status'               => 1,
                    'created_at'           => date('Y-m-d H:i:s')
                ]);
                $acc_map[$acc[1]] = (int)$this->db->insert_id();
            }
        }

        // 3. Expense Types
        $expense_types = [
            ['Staff Salary', 'SALARY', '5010', 'Monthly staff salaries and compensation'],
            ['Electricity', 'ELECTRICITY', '5020', 'Power utility disbursements'],
            ['Water', 'WATER', '5030', 'Water supply and sanitation charges'],
            ['Internet & Telecom', 'INTERNET', '5040', 'Internet connectivity, telephony, and SMS'],
            ['Stationery & Supplies', 'STATIONERY', '5050', 'Office, classroom, and exam stationery supplies'],
            ['Repairs & Maintenance', 'MAINTENANCE', '5060', 'Facility upkeep, plumbing, and structural repairs'],
            ['School Events', 'EVENTS', '5070', 'Cultural, athletic, and institutional celebrations'],
            ['General Administrative', 'GENERAL', '5090', 'Miscellaneous petty cash and administrative expenses']
        ];

        foreach ($expense_types as $et) {
            $def_acc_id = $acc_map[$et[2]] ?? null;
            $chk = $this->db->select('id')
                            ->from('tbl_finance_expense_types')
                            ->where('school_id', $school_id)
                            ->where('type_code', $et[1])
                            ->where('is_deleted', 'n')
                            ->get()->row();
            if (!$chk) {
                $this->db->insert('tbl_finance_expense_types', [
                    'school_id'                  => $school_id,
                    'type_name'                  => $et[0],
                    'type_code'                  => $et[1],
                    'default_expense_account_id' => $def_acc_id,
                    'description'                => $et[3],
                    'is_system'                  => 1,
                    'status'                     => 1,
                    'created_at'                 => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 4. Default Settings in tbl_finance_settings if missing
        $chk_set = $this->db->select('setting_id')->from('tbl_finance_settings')->where('school_id', $school_id)->get()->row();
        if (!$chk_set) {
            $this->db->insert('tbl_finance_settings', [
                'school_id'                  => $school_id,
                'currency_symbol'            => '₹',
                'currency_code'              => 'INR',
                'receipt_prefix'             => 'REC-' . date('Y') . '-',
                'next_receipt_number'        => 1001,
                'receipt_footer'             => 'Thank you for your payment. This is an official computer-generated receipt.',
                'authorized_signature_title' => 'Accounts Officer',
                'allow_partial_payments'     => 1,
                'allow_overpayment'          => 0,
                'require_transaction_ref'    => 0,
                'grace_period_days'          => 7,
                'discount_approval_required' => 1,
                'created_at'                 => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =========================================================================
    // 2. CHART OF ACCOUNTS & GROUPS
    // =========================================================================

    public function get_account_groups($school_id)
    {
        $school_id = (int)$school_id;
        return $this->db->select('g.id, g.school_id, g.group_name, g.group_name as name, g.group_code, g.category, g.category as type, g.category as group_type, g.parent_id, g.description, g.display_order, g.is_system, g.status, g.status as is_active, g.created_at, (SELECT COUNT(*) FROM tbl_finance_accounts a WHERE a.account_group_id = g.id AND a.is_deleted = "n") as account_count')
                        ->from('tbl_finance_account_groups g')
                        ->where('g.school_id', $school_id)
                        ->where('g.is_deleted', 'n')
                        ->order_by('g.display_order ASC, FIELD(g.category, "Asset", "Liability", "Equity", "Income", "Expense"), g.group_name ASC')
                        ->get()->result();
    }

    public function get_account_group_by_id($id, $school_id)
    {
        return $this->db->get_where('tbl_finance_account_groups', [
            'id'         => (int)$id,
            'school_id'  => (int)$school_id,
            'is_deleted' => 'n'
        ])->row();
    }

    public function save_account_group(array $data, $id = null)
    {
        if ($id && (int)$id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int)$id)->where('school_id', (int)$data['school_id'])->update('tbl_finance_account_groups', $data);
            return (int)$id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_account_groups', $data);
            return (int)$this->db->insert_id();
        }
    }

    public function is_account_group_name_exists($group_name, $school_id, $exclude_id = null)
    {
        $this->db->where('school_id', (int)$school_id)
                 ->where('LOWER(TRIM(group_name))', strtolower(trim($group_name)))
                 ->where('is_deleted', 'n');
        if ($exclude_id && (int)$exclude_id > 0) {
            $this->db->where('id !=', (int)$exclude_id);
        }
        return $this->db->count_all_results('tbl_finance_account_groups') > 0;
    }

    public function is_account_group_in_use($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;
        $count = $this->db->where('account_group_id', $id)
                          ->where('school_id', $school_id)
                          ->where('is_deleted', 'n')
                          ->count_all_results('tbl_finance_accounts');
        return ($count > 0);
    }

    public function toggle_account_group_status($id, $school_id, $status = null)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;
        $grp = $this->get_account_group_by_id($id, $school_id);
        if (!$grp) return false;

        $new_status = ($status !== null) ? (int)$status : ($grp->status == 1 ? 0 : 1);
        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_account_groups', [
            'status'     => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    public function delete_account_group($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;

        $grp = $this->get_account_group_by_id($id, $school_id);
        if (!$grp) {
            return ['success' => false, 'message' => 'Account group not found.'];
        }
        if (!empty($grp->is_system)) {
            return ['success' => false, 'message' => 'System account groups cannot be deleted.'];
        }
        if ($this->is_account_group_in_use($id, $school_id)) {
            return ['success' => false, 'message' => 'This account group is currently in use and cannot be deleted.'];
        }

        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_account_groups', [
            'is_deleted' => 'y',
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return ['success' => true, 'message' => 'Account group deleted successfully.'];
    }

    // -------------------------------------------------------------------------
    // ACCOUNT HEADS
    // -------------------------------------------------------------------------

    public function get_account_heads($school_id, $filters = [])
    {
        return $this->get_accounts($school_id, $filters);
    }

    public function get_accounts($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('a.id, a.school_id, a.account_group_id, a.account_code, a.account_name, a.account_type, a.parent_account_id, a.bank_name, a.account_number, a.branch, a.branch as branch_name, a.ifsc_code, a.opening_balance, a.opening_balance_type, a.description, a.status, a.status as is_active, a.is_system, a.created_at, g.group_name, g.category as group_category, g.category as group_type')
                 ->from('tbl_finance_accounts a')
                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                 ->where('a.school_id', $school_id)
                 ->where('a.is_deleted', 'n');

        if (!empty($filters['account_group_id'])) {
            $this->db->where('a.account_group_id', (int)$filters['account_group_id']);
        }
        if (!empty($filters['account_type'])) {
            $this->db->where('a.account_type', $filters['account_type']);
        }
        if (!empty($filters['category'])) {
            $this->db->where('g.category', $filters['category']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('a.status', (int)$filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('a.account_name', $s)
                     ->or_like('a.account_code', $s)
                     ->or_like('g.group_name', $s)
                     ->group_end();
        }

        $accounts = $this->db->order_by('FIELD(g.category, "Asset", "Liability", "Equity", "Income", "Expense"), a.account_code ASC')
                             ->get()->result();

        // Calculate live balances derived from double-entry lines
        foreach ($accounts as $acc) {
            $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
        }

        return $accounts;
    }

    public function get_account_by_id($id, $school_id)
    {
        $acc = $this->db->select('a.*, g.group_name, g.category as group_category, g.category as group_type')
                        ->from('tbl_finance_accounts a')
                        ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                        ->where('a.id', (int)$id)
                        ->where('a.school_id', (int)$school_id)
                        ->where('a.is_deleted', 'n')
                        ->get()->row();
        if ($acc) {
            $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
        }
        return $acc;
    }

    public function get_account_head_by_id($id, $school_id)
    {
        return $this->get_account_by_id($id, $school_id);
    }

    public function get_account_by_code($code, $school_id)
    {
        return $this->db->select('a.*, g.group_name, g.category as group_category, g.category as group_type')
                        ->from('tbl_finance_accounts a')
                        ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                        ->where('a.account_code', $code)
                        ->where('a.school_id', (int)$school_id)
                        ->where('a.is_deleted', 'n')
                        ->get()->row();
    }

    public function is_account_head_name_exists($name, $school_id, $exclude_id = null)
    {
        $this->db->where('school_id', (int)$school_id)
                 ->where('LOWER(TRIM(account_name))', strtolower(trim($name)))
                 ->where('is_deleted', 'n');
        if ($exclude_id && (int)$exclude_id > 0) {
            $this->db->where('id !=', (int)$exclude_id);
        }
        return $this->db->count_all_results('tbl_finance_accounts') > 0;
    }

    public function is_account_code_exists($code, $school_id, $exclude_id = null)
    {
        $this->db->where('school_id', (int)$school_id)
                 ->where('account_code', trim($code))
                 ->where('is_deleted', 'n');
        if ($exclude_id && (int)$exclude_id > 0) {
            $this->db->where('id !=', (int)$exclude_id);
        }
        return $this->db->count_all_results('tbl_finance_accounts') > 0;
    }

    public function generate_account_code($group_id, $school_id)
    {
        $school_id = (int)$school_id;
        $group = $this->get_account_group_by_id($group_id, $school_id);
        if (!$group) return '1000';

        $base = 1000;
        switch ($group->category) {
            case 'Asset':     $base = 1000; break;
            case 'Liability': $base = 2000; break;
            case 'Equity':    $base = 3000; break;
            case 'Income':    $base = 4000; break;
            case 'Expense':   $base = 5000; break;
            default:          $base = 1000; break;
        }

        $highest = $this->db->select('MAX(CAST(account_code AS UNSIGNED)) as max_code')
                            ->from('tbl_finance_accounts')
                            ->where('school_id', $school_id)
                            ->where('account_group_id', (int)$group_id)
                            ->where('is_deleted', 'n')
                            ->where('account_code REGEXP', '^[0-9]+$')
                            ->get()->row();

        if (!empty($highest->max_code) && $highest->max_code >= $base) {
            $next = ((int)$highest->max_code) + 10;
        } else {
            $next = $base + 10;
        }

        // Ensure uniqueness
        while ($this->is_account_code_exists((string)$next, $school_id)) {
            $next += 10;
        }

        return (string)$next;
    }

    public function is_account_head_in_use($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;

        // Check transaction items
        $tx_count = $this->db->where('account_id', $id)
                             ->where('school_id', $school_id)
                             ->count_all_results('tbl_finance_transaction_items');
        if ($tx_count > 0) return true;

        // Check custom accounts referencing this head
        $ca_count = $this->db->where('account_head_id', $id)
                             ->where('school_id', $school_id)
                             ->where('is_deleted', 'n')
                             ->count_all_results('tbl_finance_custom_accounts');
        if ($ca_count > 0) return true;

        // Check fee types
        $ft_count = $this->db->where('account_id', $id)
                             ->where('school_id', $school_id)
                             ->where('is_deleted', 'n')
                             ->count_all_results('tbl_finance_fee_types');
        if ($ft_count > 0) return true;

        // Check expenses
        $exp_count = $this->db->where('expense_account_id', $id)
                              ->where('school_id', $school_id)
                              ->count_all_results('tbl_finance_expenses');
        if ($exp_count > 0) return true;

        // Check ledgers
        $led_count = $this->db->where('parent_account_id', $id)
                              ->where('school_id', $school_id)
                              ->where('is_deleted', 'n')
                              ->count_all_results('tbl_finance_ledgers');
        if ($led_count > 0) return true;

        return false;
    }

    public function toggle_account_head_status($id, $school_id, $status = null)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;
        $acc = $this->get_account_by_id($id, $school_id);
        if (!$acc) return false;

        $new_status = ($status !== null) ? (int)$status : ($acc->status == 1 ? 0 : 1);
        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_accounts', [
            'status'     => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    public function save_account(array $data, $id = null)
    {
        if ($id && (int)$id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int)$id)->where('school_id', (int)$data['school_id'])->update('tbl_finance_accounts', $data);
            return (int)$id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_accounts', $data);
            return (int)$this->db->insert_id();
        }
    }

    public function save_account_head(array $data, $id = null)
    {
        return $this->save_account($data, $id);
    }

    public function delete_account($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;

        $acc = $this->get_account_by_id($id, $school_id);
        if (!$acc) {
            return ['success' => false, 'message' => 'Account head not found.'];
        }

        if (!empty($acc->is_system)) {
            return ['success' => false, 'message' => 'System accounts cannot be deleted. Deactivate it instead.'];
        }

        if ($this->is_account_head_in_use($id, $school_id)) {
            return ['success' => false, 'message' => 'Cannot delete this account head because it has transactions, linked custom accounts, or is in use. Deactivate it instead to preserve accounting history.'];
        }

        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_accounts', [
            'is_deleted' => 'y',
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return ['success' => true, 'message' => 'Account head deleted successfully.'];
    }

    public function delete_account_head($id, $school_id)
    {
        return $this->delete_account($id, $school_id);
    }

    // -------------------------------------------------------------------------
    // CUSTOM ACCOUNTS
    // -------------------------------------------------------------------------

    public function get_custom_accounts($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('ca.*, a.account_name as account_head_name, a.account_code as account_head_code, g.group_name, g.category as group_type')
                 ->from('tbl_finance_custom_accounts ca')
                 ->join('tbl_finance_accounts a', 'a.id = ca.account_head_id', 'inner')
                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                 ->where('ca.school_id', $school_id)
                 ->where('ca.is_deleted', 'n');

        if (!empty($filters['account_head_id'])) {
            $this->db->where('ca.account_head_id', (int)$filters['account_head_id']);
        }
        if (!empty($filters['account_type'])) {
            $this->db->where('ca.account_type', $filters['account_type']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('ca.status', (int)$filters['status']);
        }

        $records = $this->db->order_by('ca.id', 'DESC')->get()->result();

        foreach ($records as $r) {
            $r->current_balance = $this->calculate_custom_account_balance($r->id, $school_id);
            if (!empty($r->account_number)) {
                $raw = trim($r->account_number);
                $len = strlen($raw);
                if ($len > 4) {
                    $r->masked_account_number = 'XXXX XXXX ' . substr($raw, -4);
                } else {
                    $r->masked_account_number = $raw;
                }
            } else {
                $r->masked_account_number = '—';
            }
        }

        return $records;
    }

    public function get_custom_account_by_id($id, $school_id)
    {
        $r = $this->db->select('ca.*, a.account_name as account_head_name, a.account_code as account_head_code, g.group_name, g.category as group_type')
                      ->from('tbl_finance_custom_accounts ca')
                      ->join('tbl_finance_accounts a', 'a.id = ca.account_head_id', 'inner')
                      ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                      ->where('ca.id', (int)$id)
                      ->where('ca.school_id', (int)$school_id)
                      ->where('ca.is_deleted', 'n')
                      ->get()->row();
        if ($r) {
            $r->current_balance = $this->calculate_custom_account_balance($r->id, $school_id);
            if (!empty($r->account_number)) {
                $raw = trim($r->account_number);
                $len = strlen($raw);
                if ($len > 4) {
                    $r->masked_account_number = 'XXXX XXXX ' . substr($raw, -4);
                } else {
                    $r->masked_account_number = $raw;
                }
            } else {
                $r->masked_account_number = '—';
            }
        }
        return $r;
    }

    public function is_custom_account_name_exists($name, $school_id, $exclude_id = null)
    {
        $this->db->where('school_id', (int)$school_id)
                 ->where('LOWER(TRIM(account_name))', strtolower(trim($name)))
                 ->where('is_deleted', 'n');
        if ($exclude_id && (int)$exclude_id > 0) {
            $this->db->where('id !=', (int)$exclude_id);
        }
        return $this->db->count_all_results('tbl_finance_custom_accounts') > 0;
    }

    public function save_custom_account(array $data, $id = null)
    {
        $school_id = (int)$data['school_id'];
        $ca_id = null;

        if ($id && (int)$id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int)$id)->where('school_id', $school_id)->update('tbl_finance_custom_accounts', $data);
            $ca_id = (int)$id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_custom_accounts', $data);
            $ca_id = (int)$this->db->insert_id();
        }

        // Maintain corresponding ledger in tbl_finance_ledgers
        $existing_ledger = $this->db->get_where('tbl_finance_ledgers', [
            'school_id'   => $school_id,
            'entity_type' => 'Custom',
            'entity_id'   => $ca_id,
            'is_deleted'  => 'n'
        ])->row();

        $ledger_data = [
            'school_id'            => $school_id,
            'entity_type'          => 'Custom',
            'entity_id'            => $ca_id,
            'parent_account_id'    => $data['account_head_id'],
            'ledger_code'          => 'CA-' . str_pad($ca_id, 4, '0', STR_PAD_LEFT),
            'ledger_name'          => $data['account_name'],
            'opening_balance'      => $data['opening_balance'] ?? 0.00,
            'opening_balance_type' => $data['opening_balance_type'] ?? 'Debit',
            'status'               => $data['status'] ?? 1,
            'is_deleted'           => 'n',
        ];

        if ($existing_ledger) {
            $ledger_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $existing_ledger->id)->update('tbl_finance_ledgers', $ledger_data);
        } else {
            $ledger_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_ledgers', $ledger_data);
        }

        return $ca_id;
    }

    public function calculate_custom_account_balance($custom_account_id, $school_id)
    {
        $custom_account_id = (int)$custom_account_id;
        $school_id = (int)$school_id;

        $ca = $this->db->select('ca.*, g.category as group_category')
                       ->from('tbl_finance_custom_accounts ca')
                       ->join('tbl_finance_accounts a', 'a.id = ca.account_head_id', 'inner')
                       ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                       ->where('ca.id', $custom_account_id)
                       ->where('ca.school_id', $school_id)
                       ->get()->row();
        if (!$ca) return 0.00;

        $opening = (float)$ca->opening_balance;
        $opening_type = $ca->opening_balance_type;

        // Check if ledger row exists
        $ledger = $this->db->get_where('tbl_finance_ledgers', [
            'school_id'   => $school_id,
            'entity_type' => 'Custom',
            'entity_id'   => $custom_account_id,
            'is_deleted'  => 'n'
        ])->row();

        if (!$ledger) {
            return round($opening, 2);
        }

        $sums = $this->db->select('SUM(ti.debit) as total_debit, SUM(ti.credit) as total_credit')
                         ->from('tbl_finance_transaction_items ti')
                         ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                         ->where('ti.ledger_id', $ledger->id)
                         ->where('ti.school_id', $school_id)
                         ->where('t.status', 'Posted')
                         ->get()->row();

        $total_debit = (float)($sums->total_debit ?? 0.00);
        $total_credit = (float)($sums->total_credit ?? 0.00);

        $is_debit_normal = in_array($ca->group_category, ['Asset', 'Expense']);

        if ($is_debit_normal) {
            $net_opening = ($opening_type === 'Debit') ? $opening : -$opening;
            return round($net_opening + ($total_debit - $total_credit), 2);
        } else {
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            return round($net_opening + ($total_credit - $total_debit), 2);
        }
    }

    public function is_custom_account_in_use($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;

        $ledger = $this->db->get_where('tbl_finance_ledgers', [
            'school_id'   => $school_id,
            'entity_type' => 'Custom',
            'entity_id'   => $id,
            'is_deleted'  => 'n'
        ])->row();

        if ($ledger) {
            $tx_count = $this->db->where('ledger_id', $ledger->id)
                                 ->where('school_id', $school_id)
                                 ->count_all_results('tbl_finance_transaction_items');
            if ($tx_count > 0) return true;
        }
        return false;
    }

    public function toggle_custom_account_status($id, $school_id, $status = null)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;
        $ca = $this->get_custom_account_by_id($id, $school_id);
        if (!$ca) return false;

        $new_status = ($status !== null) ? (int)$status : ($ca->status == 1 ? 0 : 1);
        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_custom_accounts', [
            'status'     => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        // Also update ledger
        $this->db->where('school_id', $school_id)
                 ->where('entity_type', 'Custom')
                 ->where('entity_id', $id)
                 ->update('tbl_finance_ledgers', ['status' => $new_status]);
        return $new_status;
    }

    public function delete_custom_account($id, $school_id)
    {
        $id = (int)$id;
        $school_id = (int)$school_id;

        $ca = $this->get_custom_account_by_id($id, $school_id);
        if (!$ca) {
            return ['success' => false, 'message' => 'Custom account not found.'];
        }

        if ($this->is_custom_account_in_use($id, $school_id)) {
            return ['success' => false, 'message' => 'Cannot delete custom account with existing transactions. Deactivate it instead.'];
        }

        $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_custom_accounts', [
            'is_deleted' => 'y',
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->where('school_id', $school_id)
                 ->where('entity_type', 'Custom')
                 ->where('entity_id', $id)
                 ->update('tbl_finance_ledgers', ['is_deleted' => 'y', 'status' => 0]);

        return ['success' => true, 'message' => 'Custom account deleted successfully.'];
    }

    /**
     * Compute current balance of an account from opening balance + double-entry lines.
     * Normal Balance:
     * - Assets & Expenses: Balance = Debit - Credit
     * - Liabilities, Equity & Income: Balance = Credit - Debit
     */
    public function calculate_account_balance($account_id, $school_id, $up_to_date = null)
    {
        $account_id = (int)$account_id;
        $school_id = (int)$school_id;

        $acc = $this->db->select('a.*, g.category as group_category')
                        ->from('tbl_finance_accounts a')
                        ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                        ->where('a.id', $account_id)
                        ->where('a.school_id', $school_id)
                        ->get()->row();
        if (!$acc) return 0.00;

        $opening = (float)$acc->opening_balance;
        $opening_type = $acc->opening_balance_type;

        $this->db->select('SUM(ti.debit) as total_debit, SUM(ti.credit) as total_credit')
                 ->from('tbl_finance_transaction_items ti')
                 ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                 ->where('ti.account_id', $account_id)
                 ->where('ti.school_id', $school_id)
                 ->where('t.status', 'Posted');

        if ($up_to_date) {
            $this->db->where('t.transaction_date <=', $up_to_date);
        }

        $sums = $this->db->get()->row();
        $total_debit = (float)($sums->total_debit ?? 0.00);
        $total_credit = (float)($sums->total_credit ?? 0.00);

        $is_debit_normal = in_array($acc->group_category, ['Asset', 'Expense']);

        if ($is_debit_normal) {
            $net_opening = ($opening_type === 'Debit') ? $opening : -$opening;
            return round($net_opening + ($total_debit - $total_credit), 2);
        } else {
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            return round($net_opening + ($total_credit - $total_debit), 2);
        }
    }

    // =========================================================================
    // 3. SUB-LEDGERS (Student, Staff, Vendor, General)
    // =========================================================================

    /**
     * Get or create student sub-ledger linked to Accounts Receivable.
     */
    public function get_or_create_student_ledger($school_id, $student_id, $academic_year_id = null)
    {
        $school_id = (int)$school_id;
        $student_id = (int)$student_id;

        $existing = $this->db->get_where('tbl_finance_ledgers', [
            'school_id'   => $school_id,
            'entity_type' => 'Student',
            'entity_id'   => $student_id,
            'is_deleted'  => 'n'
        ])->row();

        if ($existing) {
            return $existing;
        }

        // Get receivable account
        $rec_acc = $this->get_account_by_code('1030', $school_id);
        if (!$rec_acc) {
            $this->initialize_school_finance_defaults($school_id);
            $rec_acc = $this->get_account_by_code('1030', $school_id);
        }

        $st = $this->db->get_where('tbl_students', ['student_id' => $student_id, 'school_id' => $school_id])->row();
        $st_name = $st ? trim($st->first_name . ' ' . $st->last_name) : 'Student #' . $student_id;
        $st_code = 'STU-' . ($st->admission_number ?? $student_id);

        $data = [
            'school_id'            => $school_id,
            'academic_year_id'     => $academic_year_id ?: ($st->academic_year_id ?? null),
            'entity_type'          => 'Student',
            'entity_id'            => $student_id,
            'parent_account_id'    => (int)$rec_acc->id,
            'ledger_code'          => $st_code,
            'ledger_name'          => $st_name,
            'opening_balance'      => 0.00,
            'opening_balance_type' => 'Debit',
            'created_at'           => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_finance_ledgers', $data);
        $data['id'] = $this->db->insert_id();
        return (object)$data;
    }

    /**
     * Get or create staff sub-ledger linked to Staff Payable.
     */
    public function get_or_create_staff_ledger($school_id, $staff_id)
    {
        $school_id = (int)$school_id;
        $staff_id = (int)$staff_id;

        $existing = $this->db->get_where('tbl_finance_ledgers', [
            'school_id'   => $school_id,
            'entity_type' => 'Staff',
            'entity_id'   => $staff_id,
            'is_deleted'  => 'n'
        ])->row();

        if ($existing) {
            return $existing;
        }

        $pay_acc = $this->get_account_by_code('2010', $school_id);
        if (!$pay_acc) {
            $this->initialize_school_finance_defaults($school_id);
            $pay_acc = $this->get_account_by_code('2010', $school_id);
        }

        $sf = $this->db->get_where('tbl_staff', ['staff_id' => $staff_id, 'school_id' => $school_id])->row();
        $sf_name = $sf ? trim($sf->full_name) : 'Staff #' . $staff_id;
        $sf_code = 'STF-' . ($sf->employee_code ?? $staff_id);

        $data = [
            'school_id'            => $school_id,
            'entity_type'          => 'Staff',
            'entity_id'            => $staff_id,
            'parent_account_id'    => (int)$pay_acc->id,
            'ledger_code'          => $sf_code,
            'ledger_name'          => $sf_name,
            'opening_balance'      => 0.00,
            'opening_balance_type' => 'Credit',
            'created_at'           => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_finance_ledgers', $data);
        $data['id'] = $this->db->insert_id();
        return (object)$data;
    }

    public function get_ledgers($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('l.*, a.account_name as parent_account_name, a.account_code as parent_account_code')
                 ->from('tbl_finance_ledgers l')
                 ->join('tbl_finance_accounts a', 'a.id = l.parent_account_id', 'inner')
                 ->where('l.school_id', $school_id)
                 ->where('l.is_deleted', 'n');

        if (!empty($filters['entity_type'])) {
            $this->db->where('l.entity_type', $filters['entity_type']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('l.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('l.ledger_name', $s)
                     ->or_like('l.ledger_code', $s)
                     ->group_end();
        }

        $ledgers = $this->db->order_by('l.ledger_name', 'ASC')->get()->result();

        foreach ($ledgers as $l) {
            $l->current_balance = $this->calculate_ledger_balance($l->id, $school_id);
        }

        return $ledgers;
    }

    public function get_ledger_by_id($id, $school_id)
    {
        $l = $this->db->select('l.*, a.account_name as parent_account_name, a.account_code as parent_account_code')
                      ->from('tbl_finance_ledgers l')
                      ->join('tbl_finance_accounts a', 'a.id = l.parent_account_id', 'inner')
                      ->where('l.id', (int)$id)
                      ->where('l.school_id', (int)$school_id)
                      ->where('l.is_deleted', 'n')
                      ->get()->row();
        if ($l) {
            $l->current_balance = $this->calculate_ledger_balance($l->id, $school_id);
        }
        return $l;
    }

    public function update_ledger_opening_balance($ledger_id, $school_id, $amount, $type, $date = null, $reason = null)
    {
        $ledger_id = (int)$ledger_id;
        $school_id = (int)$school_id;
        $amount = (float)$amount;
        $type = in_array($type, ['Debit', 'Credit']) ? $type : 'Debit';

        $this->db->where('id', $ledger_id)->where('school_id', $school_id)->update('tbl_finance_ledgers', [
            'opening_balance'        => $amount,
            'opening_balance_type'   => $type,
            'opening_balance_date'   => $date ?: date('Y-m-d'),
            'opening_balance_reason' => $reason,
            'updated_at'             => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    public function calculate_ledger_balance($ledger_id, $school_id, $up_to_date = null)
    {
        $ledger_id = (int)$ledger_id;
        $school_id = (int)$school_id;

        $l = $this->db->get_where('tbl_finance_ledgers', ['id' => $ledger_id, 'school_id' => $school_id])->row();
        if (!$l) return 0.00;

        $opening = (float)$l->opening_balance;
        $opening_type = $l->opening_balance_type;

        $this->db->select('SUM(ti.debit) as total_debit, SUM(ti.credit) as total_credit')
                 ->from('tbl_finance_transaction_items ti')
                 ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                 ->where('ti.ledger_id', $ledger_id)
                 ->where('ti.school_id', $school_id)
                 ->where('t.status', 'Posted');

        if ($up_to_date) {
            $this->db->where('t.transaction_date <=', $up_to_date);
        }

        $sums = $this->db->get()->row();
        $total_debit = (float)($sums->total_debit ?? 0.00);
        $total_credit = (float)($sums->total_credit ?? 0.00);

        // For Student (Receivable): Balance = Opening + Debit - Credit
        // For Staff (Payable): Balance = Opening + Credit - Debit
        if ($l->entity_type === 'Staff' || $l->opening_balance_type === 'Credit') {
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            return round($net_opening + ($total_credit - $total_debit), 2);
        } else {
            $net_opening = ($opening_type === 'Debit') ? $opening : -$opening;
            return round($net_opening + ($total_debit - $total_credit), 2);
        }
    }

    /**
     * Unified alias used internally by fee assignment & collection.
     */
    public function get_or_create_sub_ledger($school_id, $entity_type, $entity_id)
    {
        if ($entity_type === 'Student') {
            return $this->get_or_create_student_ledger($school_id, $entity_id);
        } elseif ($entity_type === 'Staff') {
            return $this->get_or_create_staff_ledger($school_id, $entity_id);
        }
        return null;
    }

    // =========================================================================
    // 3B. LEDGER LIST QUERIES (Aggregated, School-Isolated)
    // =========================================================================

    /**
     * Get paginated list of Student ledgers with aggregated debit/credit/balance.
     */
    public function get_student_ledgers_list($school_id, $filters = [])
    {
        $school_id = (int)$school_id;

        $this->db->select('
            l.id as ledger_id,
            l.ledger_code,
            l.ledger_name,
            l.opening_balance,
            l.opening_balance_type,
            l.status,
            l.entity_id as student_id,
            s.first_name, s.last_name,
            s.admission_number,
            c.class_name,
            d.division_name,
            a.account_name as control_account_name,
            a.account_code as control_account_code,
            COALESCE(SUM(ti.debit),0) as total_debit,
            COALESCE(SUM(ti.credit),0) as total_credit
        ')
        ->from('tbl_finance_ledgers l')
        ->join('tbl_students s', 's.student_id = l.entity_id', 'left')
        ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
        ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
        ->join('tbl_finance_accounts a', 'a.id = l.parent_account_id', 'left')
        ->join('tbl_finance_transaction_items ti', 'ti.ledger_id = l.id AND ti.school_id = ' . $school_id, 'left')
        ->join('tbl_finance_transactions t', 't.id = ti.transaction_id AND t.status = \'Posted\'', 'left')
        ->where('l.school_id', $school_id)
        ->where('l.entity_type', 'Student')
        ->where('l.is_deleted', 'n');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('s.first_name', $s)
                     ->or_like('s.last_name', $s)
                     ->or_like('CONCAT(s.first_name, " ", s.last_name)', $s)
                     ->or_like('s.admission_number', $s)
                     ->or_like('l.ledger_code', $s)
                     ->group_end();
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('s.class_id', (int)$filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->where('s.division_id', (int)$filters['division_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('l.status', (int)$filters['status']);
        }

        $ledgers = $this->db->group_by('l.id')
                            ->order_by('s.first_name ASC, s.last_name ASC')
                            ->get()->result();

        foreach ($ledgers as $l) {
            $opening = (float)$l->opening_balance;
            $opening_type = $l->opening_balance_type ?? 'Debit';
            $debit  = (float)$l->total_debit;
            $credit = (float)$l->total_credit;
            // Student is a receivable (Debit-normal). Balance = opening_debit + debit - credit
            $net_opening = ($opening_type === 'Debit') ? $opening : -$opening;
            $l->current_balance = round($net_opening + ($debit - $credit), 2);
            // Normalize name
            $l->student_name = trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? ''));
            $l->admission_no_display = $l->admission_number ?: '—';
        }

        return $ledgers;
    }

    /**
     * Get paginated list of Staff ledgers with aggregated debit/credit/balance.
     */
    public function get_staff_ledgers_list($school_id, $filters = [])
    {
        $school_id = (int)$school_id;

        $this->db->select('
            l.id as ledger_id,
            l.ledger_code,
            l.ledger_name,
            l.opening_balance,
            l.opening_balance_type,
            l.status,
            l.entity_id as staff_id,
            sf.full_name,
            sf.employee_code,
            des.designation_name,
            a.account_name as control_account_name,
            COALESCE(SUM(ti.debit),0) as total_debit,
            COALESCE(SUM(ti.credit),0) as total_credit
        ')
        ->from('tbl_finance_ledgers l')
        ->join('tbl_staff sf', 'sf.staff_id = l.entity_id', 'left')
        ->join('tbl_designations des', 'des.designation_id = sf.designation_id', 'left')
        ->join('tbl_finance_accounts a', 'a.id = l.parent_account_id', 'left')
        ->join('tbl_finance_transaction_items ti', 'ti.ledger_id = l.id AND ti.school_id = ' . $school_id, 'left')
        ->join('tbl_finance_transactions t', 't.id = ti.transaction_id AND t.status = \'Posted\'', 'left')
        ->where('l.school_id', $school_id)
        ->where('l.entity_type', 'Staff')
        ->where('l.is_deleted', 'n');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('sf.full_name', $s)
                     ->or_like('sf.employee_code', $s)
                     ->or_like('l.ledger_code', $s)
                     ->group_end();
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('l.status', (int)$filters['status']);
        }

        $ledgers = $this->db->group_by('l.id')
                            ->order_by('sf.full_name ASC')
                            ->get()->result();

        foreach ($ledgers as $l) {
            $opening = (float)$l->opening_balance;
            $opening_type = $l->opening_balance_type ?? 'Credit';
            $debit  = (float)$l->total_debit;
            $credit = (float)$l->total_credit;
            // Staff is payable (Credit-normal). Balance = opening_credit + credit - debit
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            $l->current_balance = round($net_opening + ($credit - $debit), 2);
        }

        return $ledgers;
    }

    /**
     * Get or create a third-party (Vendor/Other) ledger account.
     */
    public function get_or_create_other_party_ledger($school_id, $party_name, $party_type = 'Vendor', $contact = null, $notes = null)
    {
        $school_id = (int)$school_id;
        $party_name = trim($party_name);
        if (empty($party_name)) return null;

        // Check by name & type
        $existing = $this->db->where('school_id', $school_id)
                              ->where('entity_type', 'Vendor')
                              ->where('ledger_name', $party_name)
                              ->where('is_deleted', 'n')
                              ->get('tbl_finance_ledgers')->row();
        if ($existing) return $existing;

        $vendor_acc = $this->get_account_by_code('2020', $school_id);
        if (!$vendor_acc) {
            $this->initialize_school_finance_defaults($school_id);
            $vendor_acc = $this->get_account_by_code('2020', $school_id);
        }

        $count = $this->db->where('school_id', $school_id)->where('entity_type', 'Vendor')->count_all_results('tbl_finance_ledgers');
        $ledger_code = 'VND-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $data = [
            'school_id'            => $school_id,
            'entity_type'          => 'Vendor',
            'entity_id'            => 0,
            'parent_account_id'    => $vendor_acc ? (int)$vendor_acc->id : null,
            'ledger_code'          => $ledger_code,
            'ledger_name'          => $party_name,
            'phone'                => $contact,
            'notes'                => $notes,
            'opening_balance'      => 0.00,
            'opening_balance_type' => 'Credit',
            'status'               => 1,
            'created_at'           => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tbl_finance_ledgers', $data);
        $data['id'] = $this->db->insert_id();
        return (object)$data;
    }

    /**
     * Get paginated list of Other Party (Vendor/Supplier) ledgers.
     */
    public function get_other_party_ledgers_list($school_id, $filters = [])
    {
        $school_id = (int)$school_id;

        $this->db->select('
            l.id as ledger_id,
            l.ledger_code,
            l.ledger_name,
            l.entity_type as party_type,
            l.phone as contact,
            l.opening_balance,
            l.opening_balance_type,
            l.status,
            a.account_name as control_account_name,
            COALESCE(SUM(ti.debit),0) as total_debit,
            COALESCE(SUM(ti.credit),0) as total_credit
        ')
        ->from('tbl_finance_ledgers l')
        ->join('tbl_finance_accounts a', 'a.id = l.parent_account_id', 'left')
        ->join('tbl_finance_transaction_items ti', 'ti.ledger_id = l.id AND ti.school_id = ' . $school_id, 'left')
        ->join('tbl_finance_transactions t', 't.id = ti.transaction_id AND t.status = \'Posted\'', 'left')
        ->where('l.school_id', $school_id)
        ->where_in('l.entity_type', ['Vendor', 'Other', 'Supplier', 'Contractor', 'Custom'])
        ->where('l.is_deleted', 'n');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('l.ledger_name', $s)
                     ->or_like('l.ledger_code', $s)
                     ->or_like('l.phone', $s)
                     ->group_end();
        }
        if (!empty($filters['party_type'])) {
            $this->db->where('l.entity_type', $filters['party_type']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('l.status', (int)$filters['status']);
        }

        $ledgers = $this->db->group_by('l.id')
                            ->order_by('l.ledger_name ASC')
                            ->get()->result();

        foreach ($ledgers as $l) {
            $opening = (float)$l->opening_balance;
            $opening_type = $l->opening_balance_type ?? 'Credit';
            $debit  = (float)$l->total_debit;
            $credit = (float)$l->total_credit;
            // Vendor payable is Credit-normal
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            $l->current_balance = round($net_opening + ($credit - $debit), 2);
        }

        return $ledgers;
    }

    /**
     * Other Party Ledger Statement (Vendor/Supplier/Contractor).
     */
    public function get_other_party_statement($ledger_id, $school_id, $date_from = null, $date_to = null)
    {
        $ledger_id = (int)$ledger_id;
        $school_id = (int)$school_id;

        $ledger = $this->get_ledger_by_id($ledger_id, $school_id);
        if (!$ledger) return null;

        $opening = (float)$ledger->opening_balance;
        $opening_type = $ledger->opening_balance_type ?? 'Credit';

        // If date_from set, compute opening balance before that date
        if ($date_from) {
            $prev_date = date('Y-m-d', strtotime($date_from . ' -1 day'));
            $prior = $this->db->select('COALESCE(SUM(ti.debit),0) as pd, COALESCE(SUM(ti.credit),0) as pc')
                               ->from('tbl_finance_transaction_items ti')
                               ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                               ->where('ti.ledger_id', $ledger_id)
                               ->where('ti.school_id', $school_id)
                               ->where('t.status', 'Posted')
                               ->where('t.transaction_date <=', $prev_date)
                               ->get()->row();
            $prior_debit  = (float)($prior->pd ?? 0);
            $prior_credit = (float)($prior->pc ?? 0);
            $net_opening = ($opening_type === 'Credit') ? $opening : -$opening;
            $opening = round($net_opening + ($prior_credit - $prior_debit), 2);
        }

        $query = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc, a.account_name, t.reference_type, t.reference_id')
                           ->from('tbl_finance_transaction_items ti')
                           ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                           ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                           ->where('ti.ledger_id', $ledger_id)
                           ->where('ti.school_id', $school_id)
                           ->where('t.status', 'Posted');

        if ($date_from) $this->db->where('t.transaction_date >=', $date_from);
        if ($date_to)   $this->db->where('t.transaction_date <=', $date_to);

        $lines = $this->db->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')->get()->result();

        $running = $opening;
        $total_debit  = 0.00;
        $total_credit = 0.00;
        foreach ($lines as $l) {
            // Vendor payable: credit increases balance
            $running = round($running + ((float)$l->credit - (float)$l->debit), 2);
            $l->running_balance = $running;
            $total_debit  += (float)$l->debit;
            $total_credit += (float)$l->credit;
        }

        return [
            'ledger'          => $ledger,
            'opening_balance' => $opening,
            'lines'           => $lines,
            'total_debit'     => round($total_debit, 2),
            'total_credit'    => round($total_credit, 2),
            'closing_balance' => $running,
        ];
    }

    /**
     * General Ledger Statement for a Chart of Accounts account head.
     * Supports date range: opening balance before from_date + lines within range.
     */
    public function get_general_ledger_statement($account_id, $school_id, $date_from = null, $date_to = null)
    {
        $account_id = (int)$account_id;
        $school_id  = (int)$school_id;

        $acc = $this->get_account_by_id($account_id, $school_id);
        if (!$acc) return null;

        $opening_balance_total = (float)$acc->opening_balance;
        $opening_type = $acc->opening_balance_type ?? 'Debit';
        $is_debit_normal = in_array($acc->group_category, ['Asset', 'Expense']);

        // Compute opening balance before date_from
        if ($date_from) {
            $prev_date = date('Y-m-d', strtotime($date_from . ' -1 day'));
            $opening_balance_total = $this->calculate_account_balance($account_id, $school_id, $prev_date);
        } else {
            $opening_balance_total = $this->calculate_account_balance($account_id, $school_id);
            // Reverse to get true "opening" as of all-time start by using account's stored opening_balance
            // For GL statement: opening shown is the full account's balance up to start of current filter
            // If no date filter, opening = account's stored opening_balance
            $net_opening = ($opening_type === 'Debit') ? (float)$acc->opening_balance : -(float)$acc->opening_balance;
            if (!$is_debit_normal) {
                $net_opening = ($opening_type === 'Credit') ? (float)$acc->opening_balance : -(float)$acc->opening_balance;
            }
            $opening_balance_total = $net_opening;
        }

        // Fetch transaction lines
        $query = $this->db->select('ti.id, ti.debit, ti.credit, ti.entry_type, ti.description,
                                    t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc,
                                    t.reference_type, t.reference_id, t.status as tx_status,
                                    l.ledger_name, l.ledger_code,
                                    u.name as created_by_name')
                           ->from('tbl_finance_transaction_items ti')
                           ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                           ->join('tbl_finance_ledgers l', 'l.id = ti.ledger_id', 'left')
                           ->join('tbl_users u', 'u.user_id = t.created_by', 'left')
                           ->where('ti.account_id', $account_id)
                           ->where('ti.school_id', $school_id)
                           ->where('t.status', 'Posted');

        if ($date_from) $this->db->where('t.transaction_date >=', $date_from);
        if ($date_to)   $this->db->where('t.transaction_date <=', $date_to);

        $lines = $this->db->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')->get()->result();

        $running = $opening_balance_total;
        $total_debit  = 0.00;
        $total_credit = 0.00;

        foreach ($lines as $line) {
            $d = (float)$line->debit;
            $c = (float)$line->credit;
            if ($is_debit_normal) {
                $running = round($running + ($d - $c), 2);
            } else {
                $running = round($running + ($c - $d), 2);
            }
            $line->running_balance = $running;
            $total_debit  += $d;
            $total_credit += $c;
        }

        return [
            'account'         => $acc,
            'opening_balance' => $opening_balance_total,
            'is_debit_normal' => $is_debit_normal,
            'lines'           => $lines,
            'total_debit'     => round($total_debit, 2),
            'total_credit'    => round($total_credit, 2),
            'closing_balance' => $running,
            'date_from'       => $date_from,
            'date_to'         => $date_to,
        ];
    }

    /**
     * Get list of all account heads suitable for General Ledger selector.
     */
    public function get_accounts_by_category($category, $school_id)
    {
        $school_id = (int)$school_id;
        return $this->db->select('a.id, a.account_name, a.account_code, a.account_type, g.group_name, g.category as group_category')
                        ->from('tbl_finance_accounts a')
                        ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                        ->where('a.school_id', $school_id)
                        ->where('g.category', $category)
                        ->where('a.is_deleted', 'n')
                        ->where('a.status', 1)
                        ->order_by('a.account_code ASC')
                        ->get()->result();
    }

    /**
     * Get student statement with date-range support.
     * Overrides the base get_student_statement to correctly handle opening balance before date_from.
     */
    public function get_student_statement_ranged($student_id, $school_id, $date_from = null, $date_to = null)
    {
        $student_id = (int)$student_id;
        $school_id  = (int)$school_id;

        $ledger = $this->get_or_create_student_ledger($school_id, $student_id);
        $st = $this->db->select('st.*, c.class_name, div.division_name')
                       ->from('tbl_students st')
                       ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
                       ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
                       ->where('st.student_id', $student_id)
                       ->where('st.school_id', $school_id)
                       ->get()->row();

        $base_opening = (float)$ledger->opening_balance;
        $opening_type = $ledger->opening_balance_type ?? 'Debit';

        // Compute opening balance before date_from if filter applied
        if ($date_from) {
            $prev_date = date('Y-m-d', strtotime($date_from . ' -1 day'));
            $prior = $this->db->select('COALESCE(SUM(ti.debit),0) as pd, COALESCE(SUM(ti.credit),0) as pc')
                               ->from('tbl_finance_transaction_items ti')
                               ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                               ->where('ti.ledger_id', $ledger->id)
                               ->where('ti.school_id', $school_id)
                               ->where('t.status', 'Posted')
                               ->where('t.transaction_date <=', $prev_date)
                               ->get()->row();
            $net_opening = ($opening_type === 'Debit') ? $base_opening : -$base_opening;
            $opening = round($net_opening + ((float)($prior->pd ?? 0) - (float)($prior->pc ?? 0)), 2);
        } else {
            $opening = ($opening_type === 'Debit') ? $base_opening : -$base_opening;
        }

        $query = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc, t.reference_type, t.reference_id, a.account_name, u.name as created_by_name')
                           ->from('tbl_finance_transaction_items ti')
                           ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                           ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                           ->join('tbl_users u', 'u.user_id = t.created_by', 'left')
                           ->where('ti.ledger_id', $ledger->id)
                           ->where('ti.school_id', $school_id)
                           ->where('t.status', 'Posted');

        if ($date_from) $this->db->where('t.transaction_date >=', $date_from);
        if ($date_to)   $this->db->where('t.transaction_date <=', $date_to);

        $lines = $this->db->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')->get()->result();

        $running = $opening;
        $total_debit  = 0.00;
        $total_credit = 0.00;
        foreach ($lines as $l) {
            $running = round($running + ((float)$l->debit - (float)$l->credit), 2);
            $l->running_balance = $running;
            $total_debit  += (float)$l->debit;
            $total_credit += (float)$l->credit;
        }

        return [
            'student'         => $st,
            'ledger'          => $ledger,
            'opening_balance' => $opening,
            'lines'           => $lines,
            'total_debit'     => round($total_debit, 2),
            'total_credit'    => round($total_credit, 2),
            'closing_balance' => $running,
        ];
    }

    /**
     * Get staff statement with date-range support.
     */
    public function get_staff_statement_ranged($staff_id, $school_id, $date_from = null, $date_to = null)
    {
        $staff_id  = (int)$staff_id;
        $school_id = (int)$school_id;

        $ledger = $this->get_or_create_staff_ledger($school_id, $staff_id);
        $sf = $this->db->select('sf.staff_id, sf.school_id, sf.employee_code, sf.full_name, sf.email, sf.phone, sf.staff_type, sf.salary, des.designation_name')
                       ->from('tbl_staff sf')
                       ->join('tbl_designations des', 'des.designation_id = sf.designation_id', 'left')
                       ->where('sf.staff_id', $staff_id)
                       ->where('sf.school_id', $school_id)
                       ->get()->row();

        $base_opening = (float)$ledger->opening_balance;
        $opening_type = $ledger->opening_balance_type ?? 'Credit';

        if ($date_from) {
            $prev_date = date('Y-m-d', strtotime($date_from . ' -1 day'));
            $prior = $this->db->select('COALESCE(SUM(ti.debit),0) as pd, COALESCE(SUM(ti.credit),0) as pc')
                               ->from('tbl_finance_transaction_items ti')
                               ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                               ->where('ti.ledger_id', $ledger->id)
                               ->where('ti.school_id', $school_id)
                               ->where('t.status', 'Posted')
                               ->where('t.transaction_date <=', $prev_date)
                               ->get()->row();
            $net_opening = ($opening_type === 'Credit') ? $base_opening : -$base_opening;
            $opening = round($net_opening + ((float)($prior->pc ?? 0) - (float)($prior->pd ?? 0)), 2);
        } else {
            $opening = ($opening_type === 'Credit') ? $base_opening : -$base_opening;
        }

        $query = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc, t.reference_type, t.reference_id, a.account_name, u.name as created_by_name')
                           ->from('tbl_finance_transaction_items ti')
                           ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                           ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                           ->join('tbl_users u', 'u.user_id = t.created_by', 'left')
                           ->where('ti.ledger_id', $ledger->id)
                           ->where('ti.school_id', $school_id)
                           ->where('t.status', 'Posted');

        if ($date_from) $this->db->where('t.transaction_date >=', $date_from);
        if ($date_to)   $this->db->where('t.transaction_date <=', $date_to);

        $lines = $this->db->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')->get()->result();

        $running = $opening;
        $total_debit  = 0.00;
        $total_credit = 0.00;
        foreach ($lines as $l) {
            $running = round($running + ((float)$l->credit - (float)$l->debit), 2);
            $l->running_balance = $running;
            $total_debit  += (float)$l->debit;
            $total_credit += (float)$l->credit;
        }

        return [
            'staff'           => $sf,
            'ledger'          => $ledger,
            'opening_balance' => $opening,
            'lines'           => $lines,
            'total_debit'     => round($total_debit, 2),
            'total_credit'    => round($total_credit, 2),
            'closing_balance' => $running,
        ];
    }

    // =========================================================================
    // 4. DOUBLE-ENTRY TRANSACTION ENGINE (Atomic & Balanced)
    // =========================================================================

    /**
     * Post a balanced double-entry transaction.
     *
     * @param int   $school_id
     * @param array $header_data [academic_year_id, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, created_by]
     * @param array $lines       Array of lines: [['account_id'=>..., 'ledger_id'=>..., 'entry_type'=>'Debit'|'Credit', 'amount'=>..., 'description'=>..., 'student_id'=>..., 'staff_id'=>...], ...]
     * @return array ['success' => bool, 'transaction_id' => int, 'transaction_number' => string, 'message' => string]
     * @throws Exception
     */
    public function post_double_entry_transaction($school_id, array $header_data, array $lines)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) {
            return ['success' => false, 'message' => 'Invalid school ID for transaction.'];
        }

        if (empty($lines) || count($lines) < 2) {
            return ['success' => false, 'message' => 'Transaction must contain at least 2 lines (1 Debit and 1 Credit).'];
        }

        // 1. Balance Invariant Verification: SUM(Debits) === SUM(Credits)
        $total_debit = 0.00;
        $total_credit = 0.00;
        foreach ($lines as $line) {
            $amt = round((float)($line['amount'] ?? 0.00), 2);
            if ($amt <= 0) {
                return ['success' => false, 'message' => 'Line amounts must be greater than zero.'];
            }
            if ($line['entry_type'] === 'Debit') {
                $total_debit += $amt;
            } elseif ($line['entry_type'] === 'Credit') {
                $total_credit += $amt;
            } else {
                return ['success' => false, 'message' => 'Invalid entry type. Must be Debit or Credit.'];
            }
        }

        $total_debit = round($total_debit, 2);
        $total_credit = round($total_credit, 2);

        if (abs($total_debit - $total_credit) > 0.001) {
            return [
                'success' => false,
                'message' => "Unbalanced transaction rejected! Total Debits (₹{$total_debit}) must equal Total Credits (₹{$total_credit})."
            ];
        }

        // Begin Atomic Transaction
        $this->db->trans_start();

        // 2. Generate unique transaction number
        $tx_prefix = !empty($header_data['custom_prefix']) ? $header_data['custom_prefix'] . date('Ymd') . '-' : 'TXN-' . date('Ymd') . '-';
        $rand = strtoupper(substr(uniqid(), -4));
        $next_num = $this->db->from('tbl_finance_transactions')
                             ->where('school_id', $school_id)
                             ->count_all_results() + 1;
        $tx_number = $tx_prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT) . '-' . $rand;

        // 3. Insert Header
        $header = [
            'school_id'          => $school_id,
            'academic_year_id'   => !empty($header_data['academic_year_id']) ? (int)$header_data['academic_year_id'] : null,
            'transaction_number' => $tx_number,
            'transaction_date'   => !empty($header_data['transaction_date']) ? $header_data['transaction_date'] : date('Y-m-d'),
            'transaction_type'   => $header_data['transaction_type'] ?? 'Journal_Entry',
            'reference_type'     => $header_data['reference_type'] ?? null,
            'reference_id'       => !empty($header_data['reference_id']) ? (int)$header_data['reference_id'] : null,
            'total_amount'       => $total_debit,
            'payment_method'     => $header_data['payment_method'] ?? null,
            'reference_no'       => $header_data['reference_no'] ?? null,
            'party_type'         => $header_data['party_type'] ?? null,
            'party_name'         => $header_data['party_name'] ?? null,
            'party_id'           => !empty($header_data['party_id']) ? (int)$header_data['party_id'] : null,
            'attachment'         => $header_data['attachment'] ?? null,
            'description'        => $header_data['description'] ?? '',
            'status'             => 'Posted',
            'created_by'         => !empty($header_data['created_by']) ? (int)$header_data['created_by'] : null,
            'created_at'         => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_finance_transactions', $header);
        $tx_id = (int)$this->db->insert_id();

        // 4. Insert Lines
        foreach ($lines as $line) {
            $amt = round((float)$line['amount'], 2);
            $is_deb = ($line['entry_type'] === 'Debit');
            $this->db->insert('tbl_finance_transaction_items', [
                'transaction_id' => $tx_id,
                'school_id'      => $school_id,
                'account_id'     => (int)$line['account_id'],
                'ledger_id'      => !empty($line['ledger_id']) ? (int)$line['ledger_id'] : null,
                'entry_type'     => $line['entry_type'],
                'amount'         => $amt,
                'debit'          => $is_deb ? $amt : 0.00,
                'credit'         => $is_deb ? 0.00 : $amt,
                'description'    => $line['description'] ?? $header['description'],
                'student_id'     => !empty($line['student_id']) ? (int)$line['student_id'] : null,
                'staff_id'       => !empty($line['staff_id']) ? (int)$line['staff_id'] : null,
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Database error while posting transaction.'];
        }

        return [
            'success'            => true,
            'transaction_id'     => $tx_id,
            'transaction_number' => $tx_number,
            'message'            => "Transaction {$tx_number} posted successfully."
        ];
    }

    /**
     * Reverse an existing posted transaction with full audit trail (No hard delete).
     */
    public function reverse_transaction($school_id, $transaction_id, $reason, $user_id)
    {
        $school_id = (int)$school_id;
        $transaction_id = (int)$transaction_id;

        $orig = $this->db->get_where('tbl_finance_transactions', [
            'id'        => $transaction_id,
            'school_id' => $school_id,
            'status'    => 'Posted'
        ])->row();

        if (!$orig) {
            return ['success' => false, 'message' => 'Transaction not found or already reversed.'];
        }

        $orig_lines = $this->db->get_where('tbl_finance_transaction_items', [
            'transaction_id' => $transaction_id,
            'school_id'      => $school_id
        ])->result_array();

        if (empty($orig_lines)) {
            return ['success' => false, 'message' => 'Transaction has no lines to reverse.'];
        }

        $rev_lines = [];
        foreach ($orig_lines as $l) {
            $rev_lines[] = [
                'account_id'  => $l['account_id'],
                'ledger_id'   => $l['ledger_id'],
                'entry_type'  => ($l['entry_type'] === 'Debit') ? 'Credit' : 'Debit',
                'amount'      => (float)$l['amount'],
                'description' => "Reversal of {$orig->transaction_number}: " . ($l['description'] ?? ''),
                'student_id'  => $l['student_id'],
                'staff_id'    => $l['staff_id']
            ];
        }

        $header_data = [
            'academic_year_id' => $orig->academic_year_id,
            'transaction_date' => date('Y-m-d'),
            'transaction_type' => $orig->transaction_type,
            'reference_type'   => 'reversal',
            'reference_id'     => $transaction_id,
            'total_amount'     => $orig->total_amount,
            'description'      => "REVERSAL: " . $reason . " (Orig: {$orig->transaction_number})",
            'created_by'       => $user_id
        ];

        $post_res = $this->post_double_entry_transaction($school_id, $header_data, $rev_lines);
        if (!$post_res['success']) {
            return $post_res;
        }

        $rev_tx_id = $post_res['transaction_id'];

        // Mark original as Reversed
        $this->db->where('id', $transaction_id)->where('school_id', $school_id)->update('tbl_finance_transactions', [
            'status'                  => 'Reversed',
            'reversed_transaction_id' => $rev_tx_id,
            'reversal_reason'         => $reason,
            'reversed_by'             => $user_id,
            'reversed_at'             => date('Y-m-d H:i:s')
        ]);

        return [
            'success'                => true,
            'reversed_transaction_id' => $rev_tx_id,
            'message'                => "Transaction {$orig->transaction_number} reversed successfully."
        ];
    }

    // =========================================================================
    // 4B. TRANSACTION SUBMODULES (Income, Expense, Adjustment, Refund, Queries)
    // =========================================================================

    /**
     * Record Income Transaction.
     * Debit: Cash/Bank Account (Asset increases)
     * Credit: Income Account Head (Income increases)
     */
    public function record_income($school_id, array $data, $created_by)
    {
        $school_id = (int)$school_id;
        $amount = round((float)($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Income amount must be greater than zero.'];
        }

        $deposit_account_id = (int)($data['deposit_account_id'] ?? 0);
        $income_account_id  = (int)($data['income_account_id'] ?? 0);

        $deposit_acc = $this->get_account_by_id($deposit_account_id, $school_id);
        $income_acc  = $this->get_account_by_id($income_account_id, $school_id);

        if (!$deposit_acc || !$income_acc) {
            return ['success' => false, 'message' => 'Please select a valid Cash/Bank account and Income account.'];
        }

        $party_type = $data['party_type'] ?? 'General';
        $party_id   = !empty($data['party_id']) ? (int)$data['party_id'] : null;
        $party_name = trim($data['party_name'] ?? '');

        $ledger_id = null;
        $student_id = null;
        $staff_id = null;

        if ($party_type === 'Student' && $party_id) {
            $student_id = $party_id;
            $sl = $this->get_or_create_student_ledger($school_id, $student_id);
            $ledger_id = $sl ? $sl->id : null;
            if (empty($party_name)) {
                $st = $this->db->get_where('tbl_students', ['student_id' => $student_id])->row();
                $party_name = $st ? trim($st->first_name . ' ' . $st->last_name) : 'Student #' . $student_id;
            }
        } elseif ($party_type === 'Staff' && $party_id) {
            $staff_id = $party_id;
            $sfl = $this->get_or_create_staff_ledger($school_id, $staff_id);
            $ledger_id = $sfl ? $sfl->id : null;
            if (empty($party_name)) {
                $sf = $this->db->get_where('tbl_staff', ['staff_id' => $staff_id])->row();
                $party_name = $sf ? trim($sf->full_name) : 'Staff #' . $staff_id;
            }
        } elseif (in_array($party_type, ['Vendor', 'Other']) && !empty($party_name)) {
            $pl = $this->get_or_create_other_party_ledger($school_id, $party_name, $party_type);
            $ledger_id = $pl ? $pl->id : null;
        }

        $narration = trim($data['description'] ?? '');
        $display_narration = !empty($narration) ? $narration : "Income received from " . (!empty($party_name) ? $party_name : 'General') . " - " . $income_acc->account_name;

        $lines = [
            [
                'account_id'  => $deposit_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'description' => "Deposit received into {$deposit_acc->account_name}"
            ],
            [
                'account_id'  => $income_account_id,
                'ledger_id'   => $ledger_id,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'student_id'  => $student_id,
                'staff_id'    => $staff_id,
                'description' => $display_narration
            ]
        ];

        $header_data = [
            'academic_year_id' => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
            'transaction_type' => 'Income',
            'custom_prefix'    => 'INC-',
            'payment_method'   => $data['payment_method'] ?? 'Cash',
            'reference_no'     => $data['reference_no'] ?? null,
            'party_type'       => $party_type,
            'party_name'       => $party_name,
            'party_id'         => $party_id,
            'attachment'       => $data['attachment'] ?? null,
            'description'      => $display_narration,
            'created_by'       => $created_by
        ];

        return $this->post_double_entry_transaction($school_id, $header_data, $lines);
    }

    /**
     * Record Expense Transaction.
     * Debit: Expense Account Head (Expense increases)
     * Credit: Cash/Bank Account (Asset decreases)
     */
    public function record_expense_transaction($school_id, array $data, $created_by)
    {
        $school_id = (int)$school_id;
        $amount = round((float)($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Expense amount must be greater than zero.'];
        }

        $paid_from_account_id = (int)($data['paid_from_account_id'] ?? 0);
        $expense_account_id   = (int)($data['expense_account_id'] ?? 0);

        $payment_acc = $this->get_account_by_id($paid_from_account_id, $school_id);
        $expense_acc = $this->get_account_by_id($expense_account_id, $school_id);

        if (!$payment_acc || !$expense_acc) {
            return ['success' => false, 'message' => 'Please select a valid Paid From account and Expense account.'];
        }

        $party_type = $data['party_type'] ?? 'General';
        $party_id   = !empty($data['party_id']) ? (int)$data['party_id'] : null;
        $party_name = trim($data['party_name'] ?? '');

        $ledger_id = null;
        $staff_id = null;

        if ($party_type === 'Staff' && $party_id) {
            $staff_id = $party_id;
            $sfl = $this->get_or_create_staff_ledger($school_id, $staff_id);
            $ledger_id = $sfl ? $sfl->id : null;
            if (empty($party_name)) {
                $sf = $this->db->get_where('tbl_staff', ['staff_id' => $staff_id])->row();
                $party_name = $sf ? trim($sf->full_name) : 'Staff #' . $staff_id;
            }
        } elseif (in_array($party_type, ['Vendor', 'Other']) && !empty($party_name)) {
            $pl = $this->get_or_create_other_party_ledger($school_id, $party_name, $party_type);
            $ledger_id = $pl ? $pl->id : null;
        }

        $narration = trim($data['description'] ?? '');
        $display_narration = !empty($narration) ? $narration : "Expense paid to " . (!empty($party_name) ? $party_name : 'General') . " - " . $expense_acc->account_name;

        $lines = [
            [
                'account_id'  => $expense_account_id,
                'ledger_id'   => $ledger_id,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'staff_id'    => $staff_id,
                'description' => $display_narration
            ],
            [
                'account_id'  => $paid_from_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'description' => "Disbursed from {$payment_acc->account_name}"
            ]
        ];

        $header_data = [
            'academic_year_id' => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
            'transaction_type' => 'Expense',
            'custom_prefix'    => 'EXP-',
            'payment_method'   => $data['payment_method'] ?? 'Cash',
            'reference_no'     => $data['reference_no'] ?? null,
            'party_type'       => $party_type,
            'party_name'       => $party_name,
            'party_id'         => $party_id,
            'attachment'       => $data['attachment'] ?? null,
            'description'      => $display_narration,
            'created_by'       => $created_by
        ];

        return $this->post_double_entry_transaction($school_id, $header_data, $lines);
    }

    /**
     * Record Accounting Adjustment.
     * Every adjustment must have a mandatory reason and valid offsetting account.
     */
    public function record_adjustment($school_id, array $data, $created_by)
    {
        $school_id = (int)$school_id;
        $amount = round((float)($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Adjustment amount must be greater than zero.'];
        }

        $reason = trim($data['reason'] ?? '');
        if (empty($reason)) {
            return ['success' => false, 'message' => 'An adjustment reason is strictly mandatory.'];
        }

        $account_id        = (int)($data['account_id'] ?? 0);
        $offset_account_id = (int)($data['offset_account_id'] ?? 0);

        if ($account_id <= 0 || $offset_account_id <= 0) {
            return ['success' => false, 'message' => 'Please select both Target Account and Offsetting Account.'];
        }

        if ($account_id === $offset_account_id) {
            return ['success' => false, 'message' => 'Target Account and Offsetting Account must be distinct.'];
        }

        $target_acc = $this->get_account_by_id($account_id, $school_id);
        $offset_acc = $this->get_account_by_id($offset_account_id, $school_id);

        if (!$target_acc || !$offset_acc) {
            return ['success' => false, 'message' => 'Selected accounts not found for this school.'];
        }

        $adj_type = ($data['adjustment_type'] === 'Credit_Adjustment') ? 'Credit_Adjustment' : 'Debit_Adjustment';
        $narration = trim($data['narration'] ?? '');
        $desc = !empty($narration) ? "Adjustment: {$reason} - {$narration}" : "Adjustment: {$reason}";

        if ($adj_type === 'Debit_Adjustment') {
            $lines = [
                [
                    'account_id'  => $account_id,
                    'ledger_id'   => !empty($data['ledger_id']) ? (int)$data['ledger_id'] : null,
                    'entry_type'  => 'Debit',
                    'amount'      => $amount,
                    'description' => "Debit adjustment on {$target_acc->account_name}: {$reason}"
                ],
                [
                    'account_id'  => $offset_account_id,
                    'ledger_id'   => null,
                    'entry_type'  => 'Credit',
                    'amount'      => $amount,
                    'description' => "Offsetting credit on {$offset_acc->account_name} for {$reason}"
                ]
            ];
        } else {
            $lines = [
                [
                    'account_id'  => $offset_account_id,
                    'ledger_id'   => null,
                    'entry_type'  => 'Debit',
                    'amount'      => $amount,
                    'description' => "Offsetting debit on {$offset_acc->account_name} for {$reason}"
                ],
                [
                    'account_id'  => $account_id,
                    'ledger_id'   => !empty($data['ledger_id']) ? (int)$data['ledger_id'] : null,
                    'entry_type'  => 'Credit',
                    'amount'      => $amount,
                    'description' => "Credit adjustment on {$target_acc->account_name}: {$reason}"
                ]
            ];
        }

        $header_data = [
            'academic_year_id' => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
            'transaction_type' => 'Adjustment',
            'custom_prefix'    => 'ADJ-',
            'reference_no'     => $data['reference_no'] ?? null,
            'party_name'       => $data['party_name'] ?? null,
            'attachment'       => $data['attachment'] ?? null,
            'description'      => $desc,
            'created_by'       => $created_by
        ];

        return $this->post_double_entry_transaction($school_id, $header_data, $lines);
    }

    /**
     * Record Refund Transaction.
     * Debit: Refund / Income Reversal Account (and student/party sub-ledger)
     * Credit: Cash/Bank Account (Asset disbursed)
     */
    public function record_refund($school_id, array $data, $created_by)
    {
        $school_id = (int)$school_id;
        $amount = round((float)($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Refund amount must be greater than zero.'];
        }

        $reason = trim($data['reason'] ?? '');
        if (empty($reason)) {
            return ['success' => false, 'message' => 'A refund reason is required.'];
        }

        $refund_account_id    = (int)($data['refund_account_id'] ?? 0);
        $paid_from_account_id = (int)($data['paid_from_account_id'] ?? 0);

        $refund_acc  = $this->get_account_by_id($refund_account_id, $school_id);
        $payment_acc = $this->get_account_by_id($paid_from_account_id, $school_id);

        if (!$refund_acc || !$payment_acc) {
            return ['success' => false, 'message' => 'Please select valid Refund Account and Cash/Bank Account.'];
        }

        $party_type = $data['party_type'] ?? 'Student';
        $party_id   = !empty($data['party_id']) ? (int)$data['party_id'] : null;
        $party_name = trim($data['party_name'] ?? '');

        $ledger_id = null;
        $student_id = null;
        $staff_id = null;

        if ($party_type === 'Student' && $party_id) {
            $student_id = $party_id;
            $sl = $this->get_or_create_student_ledger($school_id, $student_id);
            $ledger_id = $sl ? $sl->id : null;
            if (empty($party_name)) {
                $st = $this->db->get_where('tbl_students', ['student_id' => $student_id])->row();
                $party_name = $st ? trim($st->first_name . ' ' . $st->last_name) : 'Student #' . $student_id;
            }
        } elseif ($party_type === 'Staff' && $party_id) {
            $staff_id = $party_id;
            $sfl = $this->get_or_create_staff_ledger($school_id, $staff_id);
            $ledger_id = $sfl ? $sfl->id : null;
            if (empty($party_name)) {
                $sf = $this->db->get_where('tbl_staff', ['staff_id' => $staff_id])->row();
                $party_name = $sf ? trim($sf->full_name) : 'Staff #' . $staff_id;
            }
        } elseif (in_array($party_type, ['Vendor', 'Other']) && !empty($party_name)) {
            $pl = $this->get_or_create_other_party_ledger($school_id, $party_name, $party_type);
            $ledger_id = $pl ? $pl->id : null;
        }

        $narration = trim($data['description'] ?? '');
        $orig_ref  = trim($data['reference_no'] ?? '');
        $desc = "Refund to " . (!empty($party_name) ? $party_name : 'Party') . ": {$reason}" . (!empty($orig_ref) ? " (Orig Ref: {$orig_ref})" : '');
        if (!empty($narration)) {
            $desc .= " - {$narration}";
        }

        $lines = [
            [
                'account_id'  => $refund_account_id,
                'ledger_id'   => $ledger_id,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'student_id'  => $student_id,
                'staff_id'    => $staff_id,
                'description' => $desc
            ],
            [
                'account_id'  => $paid_from_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'description' => "Disbursed refund from {$payment_acc->account_name}"
            ]
        ];

        $header_data = [
            'academic_year_id' => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
            'transaction_type' => 'Refund',
            'custom_prefix'    => 'REF-',
            'payment_method'   => $data['payment_method'] ?? 'Cash',
            'reference_no'     => $orig_ref,
            'party_type'       => $party_type,
            'party_name'       => $party_name,
            'party_id'         => $party_id,
            'attachment'       => $data['attachment'] ?? null,
            'description'      => $desc,
            'created_by'       => $created_by
        ];

        return $this->post_double_entry_transaction($school_id, $header_data, $lines);
    }

    /**
     * Get transaction KPIs & aggregates.
     */
    public function get_transaction_stats($school_id, $type, $academic_year_id = null)
    {
        $school_id = (int)$school_id;

        $qb = $this->db->select('COALESCE(SUM(total_amount), 0) as total_amount, COUNT(id) as total_count')
                       ->from('tbl_finance_transactions')
                       ->where('school_id', $school_id)
                       ->where('transaction_type', $type)
                       ->where('status', 'Posted');

        if ($academic_year_id) {
            $qb->where('academic_year_id', (int)$academic_year_id);
        }

        $row = $qb->get()->row();
        $total_amount = (float)($row->total_amount ?? 0);
        $total_count  = (int)($row->total_count ?? 0);

        // Month
        $month_start = date('Y-m-01');
        $month_end   = date('Y-m-t');
        $m_row = $this->db->select('COALESCE(SUM(total_amount), 0) as month_amount')
                          ->from('tbl_finance_transactions')
                          ->where('school_id', $school_id)
                          ->where('transaction_type', $type)
                          ->where('status', 'Posted')
                          ->where('transaction_date >=', $month_start)
                          ->where('transaction_date <=', $month_end)
                          ->get()->row();
        $month_amount = (float)($m_row->month_amount ?? 0);

        // Today
        $today = date('Y-m-d');
        $t_row = $this->db->select('COALESCE(SUM(total_amount), 0) as today_amount')
                          ->from('tbl_finance_transactions')
                          ->where('school_id', $school_id)
                          ->where('transaction_type', $type)
                          ->where('status', 'Posted')
                          ->where('transaction_date', $today)
                          ->get()->row();
        $today_amount = (float)($t_row->today_amount ?? 0);

        return [
            'total_amount' => $total_amount,
            'month_amount' => $month_amount,
            'today_amount' => $today_amount,
            'total_count'  => $total_count
        ];
    }

    /**
     * Get paginated transactions with full filter support.
     */
    public function get_filtered_transactions($school_id, $type = null, $filters = [], $limit = 100, $offset = 0)
    {
        $school_id = (int)$school_id;

        $this->db->select('t.*, u.name as created_by_name, rev_u.name as reversed_by_name')
                 ->from('tbl_finance_transactions t')
                 ->join('tbl_users u', 'u.user_id = t.created_by', 'left')
                 ->join('tbl_users rev_u', 'rev_u.user_id = t.reversed_by', 'left')
                 ->where('t.school_id', $school_id);

        if (!empty($type)) {
            if (is_array($type)) {
                $this->db->where_in('t.transaction_type', $type);
            } else {
                $this->db->where('t.transaction_type', $type);
            }
        }

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('t.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['payment_method'])) {
            $this->db->where('t.payment_method', $filters['payment_method']);
        }
        if (!empty($filters['party_type'])) {
            $this->db->where('t.party_type', $filters['party_type']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('t.transaction_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('t.transaction_date <=', $filters['to_date']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                     ->like('t.transaction_number', $s)
                     ->or_like('t.description', $s)
                     ->or_like('t.reference_no', $s)
                     ->or_like('t.party_name', $s)
                     ->group_end();
        }

        $transactions = $this->db->order_by('t.transaction_date', 'DESC')
                                 ->order_by('t.id', 'DESC')
                                 ->limit((int)$limit, (int)$offset)
                                 ->get()->result();

        // Fetch primary accounts for quick display
        foreach ($transactions as $t) {
            $items = $this->db->select('ti.entry_type, ti.amount, ti.description as item_desc, a.account_name, a.account_code, a.account_type')
                              ->from('tbl_finance_transaction_items ti')
                              ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                              ->where('ti.transaction_id', $t->id)
                              ->get()->result();
            $t->items = $items;
            $debit_accs = [];
            $credit_accs = [];
            foreach ($items as $it) {
                if ($it->entry_type === 'Debit') {
                    $debit_accs[] = $it->account_name;
                } else {
                    $credit_accs[] = $it->account_name;
                }
            }
            $t->debit_accounts_str  = implode(', ', array_unique($debit_accs));
            $t->credit_accounts_str = implode(', ', array_unique($credit_accs));
        }

        return $transactions;
    }

    /**
     * Get detailed transaction with all item lines, account codes, and linked ledgers.
     */
    public function get_transaction_by_id_detailed($transaction_id, $school_id)
    {
        $school_id = (int)$school_id;
        $transaction_id = (int)$transaction_id;

        $t = $this->db->select('t.*, u.name as created_by_name, rev_u.name as reversed_by_name')
                      ->from('tbl_finance_transactions t')
                      ->join('tbl_users u', 'u.user_id = t.created_by', 'left')
                      ->join('tbl_users rev_u', 'rev_u.user_id = t.reversed_by', 'left')
                      ->where('t.id', $transaction_id)
                      ->where('t.school_id', $school_id)
                      ->get()->row();

        if (!$t) return null;

        $items = $this->db->select('ti.*, a.account_name, a.account_code, a.account_type, g.group_name, g.category as group_category, l.ledger_name, l.ledger_code, s.first_name as st_first, s.last_name as st_last, sf.full_name as sf_name')
                          ->from('tbl_finance_transaction_items ti')
                          ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                          ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                          ->join('tbl_finance_ledgers l', 'l.id = ti.ledger_id', 'left')
                          ->join('tbl_students s', 's.student_id = ti.student_id', 'left')
                          ->join('tbl_staff sf', 'sf.staff_id = ti.staff_id', 'left')
                          ->where('ti.transaction_id', $transaction_id)
                          ->where('ti.school_id', $school_id)
                          ->order_by('ti.entry_type', 'DESC')
                          ->order_by('ti.id', 'ASC')
                          ->get()->result();

        $t->items = $items;
        return $t;
    }

    // =========================================================================
    // 5. EXPENSES & STAFF PAYOUTS
    // =========================================================================

    public function get_expense_types($school_id)
    {
        $school_id = (int)$school_id;
        return $this->db->select('et.id, et.school_id, et.type_name, et.type_code, et.default_expense_account_id, et.description, et.status, et.status as is_active, et.is_system, a.account_name as default_account_name')
                        ->from('tbl_finance_expense_types et')
                        ->join('tbl_finance_accounts a', 'a.id = et.default_expense_account_id', 'left')
                        ->where('et.school_id', $school_id)
                        ->where('et.is_deleted', 'n')
                        ->order_by('et.type_name', 'ASC')
                        ->get()->result();
    }

    public function save_expense_type(array $data, $id = null)
    {
        if ($id && (int)$id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int)$id)->where('school_id', (int)$data['school_id'])->update('tbl_finance_expense_types', $data);
            return (int)$id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_expense_types', $data);
            return (int)$this->db->insert_id();
        }
    }

    public function get_expenses($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('e.*, et.type_name, ea.account_name as expense_account_name, pa.account_name as payment_account_name, u.name as created_by_name')
                 ->from('tbl_finance_expenses e')
                 ->join('tbl_finance_expense_types et', 'et.id = e.expense_type_id', 'left')
                 ->join('tbl_finance_accounts ea', 'ea.id = e.expense_account_id', 'inner')
                 ->join('tbl_finance_accounts pa', 'pa.id = e.payment_account_id', 'inner')
                 ->join('tbl_users u', 'u.user_id = e.created_by', 'left')
                 ->where('e.school_id', $school_id)
                 ->where('e.is_deleted', 'n');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('e.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['expense_type_id'])) {
            $this->db->where('e.expense_type_id', (int)$filters['expense_type_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('e.expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('e.expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['payment_mode'])) {
            $this->db->where('e.payment_mode', $filters['payment_mode']);
        }
        if (!empty($filters['is_payout'])) {
            $this->db->where('e.payout_type !=', 'None');
        } elseif (isset($filters['is_payout']) && $filters['is_payout'] === false) {
            $this->db->where('e.payout_type', 'None');
        }

        return $this->db->order_by('e.expense_date DESC, e.id DESC')->get()->result();
    }

    /**
     * Record a school expense or staff payout and automatically post the double-entry transaction.
     */
    public function record_expense($school_id, array $data, $created_by = null)
    {
        $school_id = (int)$school_id;
        $amount = round((float)$data['amount'], 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Expense amount must be greater than zero.'];
        }

        $expense_account_id = (int)$data['expense_account_id'];
        $payment_account_id = (int)$data['payment_account_id']; // Cash or Bank Account

        $exp_prefix = 'EXP-' . date('Ymd') . '-';
        $rand = strtoupper(substr(uniqid(), -4));
        $exp_num = $exp_prefix . $rand;

        $payout_type = $data['payout_type'] ?? 'None';
        $staff_id = !empty($data['staff_id']) ? (int)$data['staff_id'] : null;

        $exp_data = [
            'school_id'          => $school_id,
            'academic_year_id'   => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'expense_number'     => $exp_num,
            'expense_date'       => $data['expense_date'] ?: date('Y-m-d'),
            'expense_type_id'    => (int)$data['expense_type_id'],
            'expense_account_id' => $expense_account_id,
            'payment_account_id' => $payment_account_id,
            'staff_id'           => $staff_id,
            'payout_type'        => $payout_type,
            'payee_name'         => $data['payee_name'],
            'amount'             => $amount,
            'payment_mode'       => $data['payment_mode'] ?? 'Cash',
            'reference_no'       => $data['reference_no'] ?? null,
            'description'        => $data['description'] ?? '',
            'receipt_voucher'    => $data['receipt_voucher'] ?? null,
            'status'             => 'Paid',
            'created_by'         => $created_by,
            'created_at'         => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_finance_expenses', $exp_data);
        $expense_id = (int)$this->db->insert_id();

        // Build Double-Entry Lines:
        // Debit: Expense Account (or Staff Payable if clearing existing dues)
        // Credit: Paid From Account (Cash / Bank)
        $staff_ledger_id = null;
        if ($staff_id > 0) {
            $sl = $this->get_or_create_staff_ledger($school_id, $staff_id);
            $staff_ledger_id = $sl->id;
        }

        $lines = [
            [
                'account_id'  => $expense_account_id,
                'ledger_id'   => $staff_ledger_id,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'description' => "Expense: {$data['payee_name']} - {$data['description']}",
                'staff_id'    => $staff_id
            ],
            [
                'account_id'  => $payment_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'description' => "Disbursement from account for {$exp_num}",
                'staff_id'    => $staff_id
            ]
        ];

        $tx_res = $this->post_double_entry_transaction($school_id, [
            'academic_year_id' => $exp_data['academic_year_id'],
            'transaction_date' => $exp_data['expense_date'],
            'transaction_type' => ($payout_type !== 'None') ? 'Staff_Payout' : 'Expense',
            'reference_type'   => 'tbl_finance_expenses',
            'reference_id'     => $expense_id,
            'total_amount'     => $amount,
            'description'      => "Expense #{$exp_num}: {$data['payee_name']} (₹" . number_format($amount, 2) . ")",
            'created_by'       => $created_by
        ], $lines);

        if ($tx_res['success']) {
            $this->db->where('id', $expense_id)->update('tbl_finance_expenses', ['transaction_id' => $tx_res['transaction_id']]);
        }

        return [
            'success'        => true,
            'expense_id'     => $expense_id,
            'expense_number' => $exp_num,
            'transaction_id' => $tx_res['transaction_id'] ?? null,
            'message'        => "Expense {$exp_num} recorded successfully."
        ];
    }

    /**
     * Get KPI statistics for Expenses module submodules.
     */
    public function get_expenses_module_stats($school_id, $submodule = null, $academic_year_id = null)
    {
        $school_id = (int)$school_id;
        $academic_year_id = (int)$academic_year_id;

        $cur_month_start = date('Y-m-01');
        $cur_month_end   = date('Y-m-t');
        $today           = date('Y-m-d');

        // Total Academic Year
        $this->db->select_sum('amount', 'total_amount')
                 ->select('COUNT(*) as total_count')
                 ->from('tbl_finance_expenses')
                 ->where('school_id', $school_id)
                 ->where('status !=', 'Reversed')
                 ->where('is_deleted', 'n');
        if (!empty($academic_year_id)) {
            $this->db->where('academic_year_id', $academic_year_id);
        }
        if (!empty($submodule)) {
            $this->db->where('submodule', $submodule);
        }
        $year_stat = $this->db->get()->row();

        // Total This Month
        $this->db->select_sum('amount', 'month_amount')
                 ->from('tbl_finance_expenses')
                 ->where('school_id', $school_id)
                 ->where('status !=', 'Reversed')
                 ->where('is_deleted', 'n')
                 ->where('expense_date >=', $cur_month_start)
                 ->where('expense_date <=', $cur_month_end);
        if (!empty($academic_year_id)) {
            $this->db->where('academic_year_id', $academic_year_id);
        }
        if (!empty($submodule)) {
            $this->db->where('submodule', $submodule);
        }
        $month_stat = $this->db->get()->row();

        // Today's Disbursements
        $this->db->select_sum('amount', 'today_amount')
                 ->from('tbl_finance_expenses')
                 ->where('school_id', $school_id)
                 ->where('status !=', 'Reversed')
                 ->where('is_deleted', 'n')
                 ->where('expense_date', $today);
        if (!empty($academic_year_id)) {
            $this->db->where('academic_year_id', $academic_year_id);
        }
        if (!empty($submodule)) {
            $this->db->where('submodule', $submodule);
        }
        $today_stat = $this->db->get()->row();

        return [
            'total_amount' => round((float)($year_stat->total_amount ?? 0), 2),
            'total_count'  => (int)($year_stat->total_count ?? 0),
            'month_amount' => round((float)($month_stat->month_amount ?? 0), 2),
            'today_amount' => round((float)($today_stat->today_amount ?? 0), 2),
        ];
    }

    /**
     * Get filtered expense list for specific submodule with multi-school & academic year filtering.
     */
    public function get_filtered_expenses($school_id, $submodule = null, $filters = [], $limit = 200)
    {
        $school_id = (int)$school_id;
        $this->db->select('e.*, et.type_name, ea.account_name as expense_account_name, ea.account_code as expense_account_code, pa.account_name as payment_account_name, pa.account_code as payment_account_code, u.name as created_by_name, s.employee_code, s.full_name as staff_full_name, t.transaction_number, t.status as tx_status')
                 ->from('tbl_finance_expenses e')
                 ->join('tbl_finance_expense_types et', 'et.id = e.expense_type_id', 'left')
                 ->join('tbl_finance_accounts ea', 'ea.id = e.expense_account_id', 'left')
                 ->join('tbl_finance_accounts pa', 'pa.id = e.payment_account_id', 'left')
                 ->join('tbl_users u', 'u.user_id = e.created_by', 'left')
                 ->join('tbl_staff s', 's.staff_id = e.staff_id', 'left')
                 ->join('tbl_finance_transactions t', 't.id = e.transaction_id', 'left')
                 ->where('e.school_id', $school_id)
                 ->where('e.is_deleted', 'n');

        if (!empty($submodule)) {
            $this->db->where('e.submodule', $submodule);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('e.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('e.expense_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('e.expense_date <=', $filters['date_to']);
        }
        if (!empty($filters['expense_account_id'])) {
            $this->db->where('e.expense_account_id', (int)$filters['expense_account_id']);
        }
        if (!empty($filters['payment_account_id'])) {
            $this->db->where('e.payment_account_id', (int)$filters['payment_account_id']);
        }
        if (!empty($filters['payment_mode'])) {
            $this->db->where('e.payment_mode', $filters['payment_mode']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('e.status', $filters['status']);
        }
        if (!empty($filters['payout_type']) && $filters['payout_type'] !== 'all') {
            $this->db->where('e.payout_type', $filters['payout_type']);
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('e.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['vendor_id'])) {
            $this->db->where('e.vendor_id', (int)$filters['vendor_id']);
        }
        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                     ->like('e.expense_number', $q)
                     ->or_like('e.payee_name', $q)
                     ->or_like('e.reference_no', $q)
                     ->or_like('e.description', $q)
                     ->or_like('ea.account_name', $q)
                     ->or_like('pa.account_name', $q)
                     ->or_like('s.full_name', $q)
                     ->group_end();
        }

        $this->db->order_by('e.expense_date DESC, e.id DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }
        return $this->db->get()->result();
    }

    /**
     * Complete Expense Voucher Recording with Double-Entry and Ledgers.
     */
    public function record_expense_voucher($school_id, array $data, $created_by = null)
    {
        $school_id = (int)$school_id;
        $amount = round((float)($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Expense amount must be greater than zero.'];
        }

        $submodule = $data['submodule'] ?? 'Expense_Entry';
        $expense_account_id = (int)($data['expense_account_id'] ?? 0);
        $payment_account_id = (int)($data['payment_account_id'] ?? 0);

        $payment_acc = $this->get_account_by_id($payment_account_id, $school_id);
        $expense_acc = $this->get_account_by_id($expense_account_id, $school_id);

        if (!$payment_acc || !$expense_acc) {
            return ['success' => false, 'message' => 'Please select a valid Cash/Bank account and Expense/Debit account.'];
        }

        $prefix_map = [
            'Expense_Entry'  => 'EXP-',
            'Staff_Payout'   => 'PAY-',
            'Vendor_Payment' => 'VND-',
            'Other_Expense'  => 'OTH-'
        ];
        $prefix = $prefix_map[$submodule] ?? 'EXP-';

        $tx_type_map = [
            'Expense_Entry'  => 'Expense',
            'Staff_Payout'   => 'Staff_Payout',
            'Vendor_Payment' => 'Vendor_Payment',
            'Other_Expense'  => 'Other_Expense'
        ];
        $tx_type = $tx_type_map[$submodule] ?? 'Expense';

        $rand = strtoupper(substr(uniqid(), -4));
        $next_num = $this->db->where('school_id', $school_id)->count_all_results('tbl_finance_expenses') + 1;
        $voucher_no = $prefix . date('Ymd') . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT) . '-' . $rand;

        $staff_id   = !empty($data['staff_id']) ? (int)$data['staff_id'] : null;
        $vendor_id  = !empty($data['vendor_id']) ? (int)$data['vendor_id'] : null;
        $party_type = $data['party_type'] ?? 'General';
        $payee_name = trim($data['payee_name'] ?? '');
        $payout_type = $data['payout_type'] ?? 'None';
        $reason     = trim($data['reason'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($submodule === 'Other_Expense' && empty($reason)) {
            return ['success' => false, 'message' => 'A reason is strictly mandatory for Other/Miscellaneous expenses.'];
        }

        $ledger_id = null;
        if ($submodule === 'Staff_Payout' && $staff_id > 0) {
            $sfl = $this->get_or_create_staff_ledger($school_id, $staff_id);
            $ledger_id = $sfl ? $sfl->id : null;
            if (empty($payee_name)) {
                $sf = $this->db->get_where('tbl_staff', ['staff_id' => $staff_id])->row();
                $payee_name = $sf ? trim($sf->full_name) : 'Staff #' . $staff_id;
            }
            $party_type = 'Staff';
        } elseif ($submodule === 'Vendor_Payment') {
            $party_type = 'Vendor';
            if (!empty($payee_name)) {
                $vl = $this->get_or_create_other_party_ledger($school_id, $payee_name, 'Vendor');
                $ledger_id = $vl ? $vl->id : null;
                $vendor_id = $vl ? $vl->id : null;
            }
        } elseif (!empty($data['party_ledger_id'])) {
            $ledger_id = (int)$data['party_ledger_id'];
        }

        if (empty($payee_name)) {
            $payee_name = 'General';
        }

        $exp_data = [
            'school_id'          => $school_id,
            'academic_year_id'   => !empty($data['academic_year_id']) ? (int)$data['academic_year_id'] : null,
            'submodule'          => $submodule,
            'expense_number'     => $voucher_no,
            'expense_date'       => !empty($data['expense_date']) ? $data['expense_date'] : date('Y-m-d'),
            'expense_type_id'    => !empty($data['expense_type_id']) ? (int)$data['expense_type_id'] : 0,
            'expense_account_id' => $expense_account_id,
            'payment_account_id' => $payment_account_id,
            'staff_id'           => $staff_id,
            'vendor_id'          => $vendor_id,
            'party_type'         => $party_type,
            'payout_type'        => $payout_type,
            'payee_name'         => $payee_name,
            'amount'             => $amount,
            'payment_mode'       => $data['payment_mode'] ?? 'Cash',
            'reference_no'       => $data['reference_no'] ?? null,
            'description'        => $description,
            'reason'             => $reason,
            'attachment'         => $data['attachment'] ?? null,
            'status'             => 'Paid',
            'created_by'         => $created_by,
            'created_at'         => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_finance_expenses', $exp_data);
        $expense_id = (int)$this->db->insert_id();

        $line_desc = !empty($description) ? $description : "{$tx_type} to {$payee_name} - {$expense_acc->account_name}";
        if (!empty($reason)) {
            $line_desc = "Reason: {$reason} | " . $line_desc;
        }

        $lines = [
            [
                'account_id'  => $expense_account_id,
                'ledger_id'   => $ledger_id,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'staff_id'    => $staff_id,
                'description' => $line_desc
            ],
            [
                'account_id'  => $payment_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'staff_id'    => $staff_id,
                'description' => "Disbursement from {$payment_acc->account_name} for {$voucher_no}"
            ]
        ];

        $header_data = [
            'academic_year_id' => $exp_data['academic_year_id'],
            'transaction_date' => $exp_data['expense_date'],
            'transaction_type' => $tx_type,
            'custom_prefix'    => $prefix,
            'reference_type'   => 'tbl_finance_expenses',
            'reference_id'     => $expense_id,
            'total_amount'     => $amount,
            'payment_method'   => $exp_data['payment_mode'],
            'reference_no'     => $exp_data['reference_no'],
            'party_type'       => $party_type,
            'party_name'       => $payee_name,
            'party_id'         => $staff_id ?: $vendor_id,
            'attachment'       => $exp_data['attachment'],
            'description'      => $line_desc,
            'created_by'       => $created_by
        ];

        $tx_res = $this->post_double_entry_transaction($school_id, $header_data, $lines);
        if ($tx_res['success']) {
            $this->db->where('id', $expense_id)->update('tbl_finance_expenses', ['transaction_id' => $tx_res['transaction_id']]);
        }

        return [
            'success'            => true,
            'expense_id'         => $expense_id,
            'expense_number'     => $voucher_no,
            'transaction_id'     => $tx_res['transaction_id'] ?? null,
            'transaction_number' => $tx_res['transaction_number'] ?? null,
            'message'            => "Voucher {$voucher_no} posted successfully."
        ];
    }

    /**
     * Get detailed expense record including double-entry transaction and items.
     */
    public function get_expense_detailed($school_id, $expense_id)
    {
        $school_id = (int)$school_id;
        $expense_id = (int)$expense_id;

        $exp = $this->db->select('e.*, et.type_name, ea.account_name as expense_account_name, ea.account_code as expense_account_code, pa.account_name as payment_account_name, pa.account_code as payment_account_code, u.name as created_by_name, s.full_name as staff_name, s.employee_code, t.transaction_number, t.status as tx_status, t.is_reversed, t.reversal_reason, rev_u.name as reversed_by_name')
                        ->from('tbl_finance_expenses e')
                        ->join('tbl_finance_expense_types et', 'et.id = e.expense_type_id', 'left')
                        ->join('tbl_finance_accounts ea', 'ea.id = e.expense_account_id', 'left')
                        ->join('tbl_finance_accounts pa', 'pa.id = e.payment_account_id', 'left')
                        ->join('tbl_users u', 'u.user_id = e.created_by', 'left')
                        ->join('tbl_staff s', 's.staff_id = e.staff_id', 'left')
                        ->join('tbl_finance_transactions t', 't.id = e.transaction_id', 'left')
                        ->join('tbl_users rev_u', 'rev_u.user_id = t.reversed_by', 'left')
                        ->where('e.id', $expense_id)
                        ->where('e.school_id', $school_id)
                        ->get()->row();

        if (!$exp) return null;

        $lines = [];
        if (!empty($exp->transaction_id)) {
            $lines = $this->db->select('ti.*, a.account_name, a.account_code, l.ledger_name, l.ledger_code')
                              ->from('tbl_finance_transaction_items ti')
                              ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                              ->join('tbl_finance_ledgers l', 'l.id = ti.ledger_id', 'left')
                              ->where('ti.transaction_id', $exp->transaction_id)
                              ->where('ti.school_id', $school_id)
                              ->order_by('ti.entry_type', 'ASC')
                              ->order_by('ti.id', 'ASC')
                              ->get()->result();
        }
        $exp->lines = $lines;
        return $exp;
    }

    /**
     * Reverse an expense voucher with audit trail and opposite accounting entry.
     */
    public function reverse_expense_voucher($school_id, $expense_id, $reason, $user_id)
    {
        $school_id = (int)$school_id;
        $expense_id = (int)$expense_id;

        $exp = $this->db->get_where('tbl_finance_expenses', [
            'id'        => $expense_id,
            'school_id' => $school_id,
            'status'    => 'Paid'
        ])->row();

        if (!$exp) {
            return ['success' => false, 'message' => 'Expense voucher not found or already reversed.'];
        }

        if (empty($exp->transaction_id)) {
            return ['success' => false, 'message' => 'Linked financial transaction not found for this voucher.'];
        }

        $rev_res = $this->reverse_transaction($school_id, $exp->transaction_id, $reason, $user_id);
        if (!$rev_res['success']) {
            return $rev_res;
        }

        $this->db->where('id', $expense_id)->where('school_id', $school_id)->update('tbl_finance_expenses', [
            'status'     => 'Reversed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => "Expense voucher {$exp->expense_number} reversed successfully."
        ];
    }

    // =========================================================================
    // 6. CASH & BANK ACCOUNTS & TRANSFERS
    // =========================================================================

    public function get_cash_and_bank_accounts($school_id, $status = null)
    {
        $school_id = (int)$school_id;
        $this->db->select('a.id, a.school_id, a.account_group_id, a.account_name, a.account_code, a.account_type, a.bank_account_type, a.description, a.opening_balance, a.opening_balance_type, a.bank_name, a.account_number, a.ifsc_code, a.branch, a.branch as branch_name, a.status, a.status as is_active, a.is_system, g.group_name')
                 ->from('tbl_finance_accounts a')
                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                 ->where('a.school_id', $school_id)
                 ->where_in('a.account_type', ['Cash', 'Bank'])
                 ->where('a.is_deleted', 'n');

        if ($status !== null) {
            $this->db->where('a.status', (int)$status);
        }

        $accounts = $this->db->order_by('a.account_type ASC, a.account_name ASC')->get()->result();

        foreach ($accounts as $acc) {
            $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
            $num = trim((string)($acc->account_number ?? ''));
            $acc->masked_account_number = (strlen($num) > 4) ? ('•••• •••• ' . substr($num, -4)) : ($num ?: '—');
        }
        return $accounts;
    }

    /**
     * Get Cash Accounts with filters, parent account head, and dynamic running balances.
     */
    public function get_cash_accounts_list($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('a.*, g.group_name, p.account_name as parent_head_name, p.account_code as parent_head_code, u.name as created_by_name')
                 ->from('tbl_finance_accounts a')
                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'left')
                 ->join('tbl_finance_accounts p', 'p.id = a.parent_account_id', 'left')
                 ->join('tbl_users u', 'u.user_id = a.created_by', 'left')
                 ->where('a.school_id', $school_id)
                 ->where('a.account_type', 'Cash')
                 ->where('a.is_deleted', 'n');

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                     ->like('a.account_name', $s)
                     ->or_like('a.account_code', $s)
                     ->or_like('a.description', $s)
                     ->group_end();
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('a.status', (int)$filters['status']);
        }

        $accounts = $this->db->order_by('a.is_system DESC, a.account_code ASC, a.account_name ASC')->get()->result();

        foreach ($accounts as $acc) {
            $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
        }

        return $accounts;
    }

    /**
     * Get Bank Accounts with filters, masked account number, branch, IFSC, and dynamic balance.
     */
    public function get_bank_accounts_list($school_id, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('a.*, g.group_name, p.account_name as parent_head_name, p.account_code as parent_head_code, u.name as created_by_name')
                 ->from('tbl_finance_accounts a')
                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'left')
                 ->join('tbl_finance_accounts p', 'p.id = a.parent_account_id', 'left')
                 ->join('tbl_users u', 'u.user_id = a.created_by', 'left')
                 ->where('a.school_id', $school_id)
                 ->where('a.account_type', 'Bank')
                 ->where('a.is_deleted', 'n');

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                     ->like('a.bank_name', $s)
                     ->or_like('a.account_name', $s)
                     ->or_like('a.account_number', $s)
                     ->or_like('a.branch', $s)
                     ->or_like('a.ifsc_code', $s)
                     ->group_end();
        }

        if (!empty($filters['bank_name'])) {
            $this->db->like('a.bank_name', trim($filters['bank_name']));
        }

        if (!empty($filters['bank_account_type'])) {
            $this->db->where('a.bank_account_type', trim($filters['bank_account_type']));
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('a.status', (int)$filters['status']);
        }

        $accounts = $this->db->order_by('a.is_system DESC, a.bank_name ASC, a.account_name ASC')->get()->result();

        foreach ($accounts as $acc) {
            $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
            $num = trim((string)($acc->account_number ?? ''));
            $acc->masked_account_number = (strlen($num) > 4) ? ('•••• •••• ' . substr($num, -4)) : ($num ?: '—');
        }

        return $accounts;
    }

    /**
     * KPI Stat cards for Cash or Bank accounts.
     */
    public function get_cash_bank_kpi_stats($school_id, $type = 'Cash', $academic_year_id = null)
    {
        $school_id = (int)$school_id;
        $accounts = $this->db->select('id, status')->from('tbl_finance_accounts')
                             ->where('school_id', $school_id)
                             ->where('account_type', $type)
                             ->where('is_deleted', 'n')
                             ->get()->result();

        $total_balance   = 0.00;
        $total_accounts  = count($accounts);
        $active_accounts = 0;

        foreach ($accounts as $acc) {
            if ($acc->status == 1) {
                $active_accounts++;
                $total_balance += $this->calculate_account_balance($acc->id, $school_id);
            }
        }

        // Today's Inflow (Debits) & Outflow (Credits)
        $today = date('Y-m-d');
        $movement = $this->db->select('SUM(ti.debit) as inflow, SUM(ti.credit) as outflow')
                             ->from('tbl_finance_transaction_items ti')
                             ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                             ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                             ->where('ti.school_id', $school_id)
                             ->where('a.account_type', $type)
                             ->where('t.status', 'Posted')
                             ->where('t.transaction_date', $today)
                             ->get()->row();

        return (object)[
            'total_balance'   => $total_balance,
            'total_accounts'  => $total_accounts,
            'active_accounts' => $active_accounts,
            'today_inflow'    => (float)($movement->inflow ?? 0.00),
            'today_outflow'   => (float)($movement->outflow ?? 0.00),
        ];
    }

    /**
     * Create or update a physical Cash Account.
     */
    public function save_cash_account($school_id, $data, $user_id, $account_id = null)
    {
        $school_id = (int)$school_id;
        $account_name = trim($data['account_name'] ?? '');
        if (empty($account_name)) {
            return ['success' => false, 'message' => 'Cash Account Name is required.'];
        }

        $opening_balance = max(0.00, round((float)($data['opening_balance'] ?? 0.00), 2));
        $description     = trim($data['description'] ?? '');
        $status          = isset($data['status']) ? (int)$data['status'] : 1;
        $parent_head_id  = !empty($data['parent_account_id']) ? (int)$data['parent_account_id'] : null;

        // Find or fallback to Cash parent head (1010)
        if (!$parent_head_id) {
            $def_head = $this->get_account_by_code('1010', $school_id);
            $parent_head_id = $def_head ? (int)$def_head->id : null;
        }

        // Find Asset group
        $asset_grp = $this->db->select('id')->from('tbl_finance_account_groups')
                              ->where('category', 'Asset')
                              ->order_by('id ASC')
                              ->get()->row();
        $group_id = $asset_grp ? (int)$asset_grp->id : 1;

        if ($account_id > 0) {
            // Edit existing
            $existing = $this->get_account_by_id($account_id, $school_id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Cash Account not found.'];
            }

            $account_code = trim($data['account_code'] ?? $existing->account_code);
            // Verify code uniqueness
            $dup = $this->db->where('school_id', $school_id)
                            ->where('account_code', $account_code)
                            ->where('id !=', $account_id)
                            ->where('is_deleted', 'n')
                            ->count_all_results('tbl_finance_accounts');
            if ($dup > 0) {
                return ['success' => false, 'message' => "Account Code '{$account_code}' is already in use."];
            }

            $update_data = [
                'account_name'       => $account_name,
                'account_code'       => $account_code,
                'description'        => $description,
                'parent_account_id'  => $parent_head_id,
                'status'             => $status,
                'updated_by'         => $user_id,
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            // Allow modifying opening balance only if no transactions exist yet
            $has_tx = $this->db->where('account_id', $account_id)->where('school_id', $school_id)->count_all_results('tbl_finance_transaction_items');
            if ($has_tx === 0) {
                $update_data['opening_balance'] = $opening_balance;
            }

            $this->db->where('id', $account_id)->where('school_id', $school_id)->update('tbl_finance_accounts', $update_data);
            return ['success' => true, 'account_id' => $account_id, 'message' => "Cash Account '{$account_name}' updated successfully."];
        } else {
            // Create new
            $account_code = trim($data['account_code'] ?? '');
            if (empty($account_code)) {
                $count = $this->db->where('school_id', $school_id)->where('account_type', 'Cash')->count_all_results('tbl_finance_accounts');
                $account_code = '101' . ($count + 1);
            }

            // Check code uniqueness
            $dup = $this->db->where('school_id', $school_id)
                            ->where('account_code', $account_code)
                            ->where('is_deleted', 'n')
                            ->count_all_results('tbl_finance_accounts');
            if ($dup > 0) {
                $account_code = 'CASH-' . strtoupper(substr(uniqid(), -4));
            }

            $insert_data = [
                'school_id'            => $school_id,
                'account_group_id'     => $group_id,
                'account_code'         => $account_code,
                'account_name'         => $account_name,
                'account_type'         => 'Cash',
                'parent_account_id'    => $parent_head_id,
                'opening_balance'      => $opening_balance,
                'opening_balance_type' => 'Debit',
                'description'          => $description,
                'status'               => $status,
                'is_system'            => 0,
                'is_deleted'           => 'n',
                'created_by'           => $user_id,
                'created_at'           => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tbl_finance_accounts', $insert_data);
            $new_id = (int)$this->db->insert_id();

            return ['success' => true, 'account_id' => $new_id, 'message' => "Cash Account '{$account_name}' created successfully with Code: {$account_code}."];
        }
    }

    /**
     * Create or update a Bank Account.
     */
    public function save_bank_account($school_id, $data, $user_id, $account_id = null)
    {
        $school_id = (int)$school_id;
        $bank_name    = trim($data['bank_name'] ?? '');
        $account_name = trim($data['account_name'] ?? '');
        $acc_num      = trim($data['account_number'] ?? '');

        if (empty($bank_name) || empty($account_name)) {
            return ['success' => false, 'message' => 'Bank Name and Account Display Name are required.'];
        }

        $opening_balance   = max(0.00, round((float)($data['opening_balance'] ?? 0.00), 2));
        $bank_account_type = trim($data['bank_account_type'] ?? 'Savings');
        $branch            = trim($data['branch'] ?? '');
        $ifsc_code         = strtoupper(trim($data['ifsc_code'] ?? ''));
        $description       = trim($data['description'] ?? '');
        $status            = isset($data['status']) ? (int)$data['status'] : 1;
        $parent_head_id    = !empty($data['parent_account_id']) ? (int)$data['parent_account_id'] : null;

        // Find or fallback to Bank parent head (1020)
        if (!$parent_head_id) {
            $def_head = $this->get_account_by_code('1020', $school_id);
            $parent_head_id = $def_head ? (int)$def_head->id : null;
        }

        // Find Asset group
        $asset_grp = $this->db->select('id')->from('tbl_finance_account_groups')
                              ->where('category', 'Asset')
                              ->order_by('id ASC')
                              ->get()->row();
        $group_id = $asset_grp ? (int)$asset_grp->id : 1;

        if ($account_id > 0) {
            $existing = $this->get_account_by_id($account_id, $school_id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Bank Account not found.'];
            }

            $account_code = trim($data['account_code'] ?? $existing->account_code);
            $dup = $this->db->where('school_id', $school_id)
                            ->where('account_code', $account_code)
                            ->where('id !=', $account_id)
                            ->where('is_deleted', 'n')
                            ->count_all_results('tbl_finance_accounts');
            if ($dup > 0) {
                return ['success' => false, 'message' => "Account Code '{$account_code}' is already in use."];
            }

            $update_data = [
                'bank_name'          => $bank_name,
                'account_name'       => $account_name,
                'account_code'       => $account_code,
                'account_number'     => $acc_num ?: $existing->account_number,
                'bank_account_type'  => $bank_account_type,
                'branch'             => $branch,
                'ifsc_code'          => $ifsc_code,
                'description'        => $description,
                'parent_account_id'  => $parent_head_id,
                'status'             => $status,
                'updated_by'         => $user_id,
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            $has_tx = $this->db->where('account_id', $account_id)->where('school_id', $school_id)->count_all_results('tbl_finance_transaction_items');
            if ($has_tx === 0) {
                $update_data['opening_balance'] = $opening_balance;
            }

            $this->db->where('id', $account_id)->where('school_id', $school_id)->update('tbl_finance_accounts', $update_data);
            return ['success' => true, 'account_id' => $account_id, 'message' => "Bank Account '{$account_name}' updated successfully."];
        } else {
            $account_code = trim($data['account_code'] ?? '');
            if (empty($account_code)) {
                $count = $this->db->where('school_id', $school_id)->where('account_type', 'Bank')->count_all_results('tbl_finance_accounts');
                $account_code = '102' . ($count + 1);
            }

            $dup = $this->db->where('school_id', $school_id)
                            ->where('account_code', $account_code)
                            ->where('is_deleted', 'n')
                            ->count_all_results('tbl_finance_accounts');
            if ($dup > 0) {
                $account_code = 'BANK-' . strtoupper(substr(uniqid(), -4));
            }

            $insert_data = [
                'school_id'            => $school_id,
                'account_group_id'     => $group_id,
                'account_code'         => $account_code,
                'account_name'         => $account_name,
                'account_type'         => 'Bank',
                'parent_account_id'    => $parent_head_id,
                'bank_name'            => $bank_name,
                'account_number'       => $acc_num,
                'bank_account_type'    => $bank_account_type,
                'branch'               => $branch,
                'ifsc_code'            => $ifsc_code,
                'opening_balance'      => $opening_balance,
                'opening_balance_type' => 'Debit',
                'description'          => $description,
                'status'               => $status,
                'is_system'            => 0,
                'is_deleted'           => 'n',
                'created_by'           => $user_id,
                'created_at'           => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tbl_finance_accounts', $insert_data);
            $new_id = (int)$this->db->insert_id();

            return ['success' => true, 'account_id' => $new_id, 'message' => "Bank Account '{$account_name}' created successfully with Code: {$account_code}."];
        }
    }

    /**
     * Toggle status (Active / Inactive) for Cash or Bank account.
     */
    public function toggle_account_status($school_id, $account_id, $user_id)
    {
        $school_id = (int)$school_id;
        $acc = $this->get_account_by_id($account_id, $school_id);
        if (!$acc) {
            return ['success' => false, 'message' => 'Account not found.'];
        }

        $new_status = ($acc->status == 1) ? 0 : 1;
        $this->db->where('id', $account_id)
                 ->where('school_id', $school_id)
                 ->update('tbl_finance_accounts', [
                     'status'     => $new_status,
                     'updated_by' => $user_id,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $status_label = ($new_status == 1) ? 'activated' : 'deactivated';
        return ['success' => true, 'new_status' => $new_status, 'message' => "Account '{$acc->account_name}' {$status_label} successfully."];
    }

    /**
     * Inter-account fund transfer (Cash -> Bank, Bank -> Cash, Bank -> Bank, Cash -> Cash).
     * Non-income, non-expense double-entry contra posting.
     */
    public function record_transfer($school_id, $from_acc_id, $to_acc_id, $amount, $date, $ref_no, $notes, $created_by, $academic_year_id = null, $attachment = null, $description = null)
    {
        $school_id = (int)$school_id;
        $from_acc_id = (int)$from_acc_id;
        $to_acc_id = (int)$to_acc_id;
        $amount = round((float)$amount, 2);

        if ($from_acc_id === $to_acc_id) {
            return ['success' => false, 'message' => 'Source and destination accounts must be different.'];
        }
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Transfer amount must be greater than zero.'];
        }

        $from_acc = $this->get_account_by_id($from_acc_id, $school_id);
        $to_acc   = $this->get_account_by_id($to_acc_id, $school_id);
        if (!$from_acc || !$to_acc) {
            return ['success' => false, 'message' => 'One or both accounts do not exist in the active school context.'];
        }

        if ($from_acc->status != 1 || $to_acc->status != 1) {
            return ['success' => false, 'message' => 'Cannot transfer funds to or from an inactive account.'];
        }

        $trf_num = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $this->db->insert('tbl_finance_transfers', [
            'school_id'        => $school_id,
            'academic_year_id' => $academic_year_id,
            'transfer_number'  => $trf_num,
            'transfer_date'    => $date ?: date('Y-m-d'),
            'from_account_id'  => $from_acc_id,
            'to_account_id'    => $to_acc_id,
            'amount'           => $amount,
            'reference_no'     => $ref_no,
            'notes'            => $notes,
            'description'      => $description ?: $notes,
            'attachment'       => $attachment,
            'status'           => 'Completed',
            'created_by'       => $created_by,
            'created_at'       => date('Y-m-d H:i:s')
        ]);
        $trf_id = (int)$this->db->insert_id();

        // Double Entry:
        // Debit: Destination Account (Increases Asset)
        // Credit: Source Account (Decreases Asset)
        $lines = [
            [
                'account_id'  => $to_acc_id,
                'ledger_id'   => null,
                'entry_type'  => 'Debit',
                'amount'      => $amount,
                'description' => "Transfer in from {$from_acc->account_name} ({$trf_num})"
            ],
            [
                'account_id'  => $from_acc_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => $amount,
                'description' => "Transfer out to {$to_acc->account_name} ({$trf_num})"
            ]
        ];

        $tx_res = $this->post_double_entry_transaction($school_id, [
            'academic_year_id' => $academic_year_id,
            'transaction_date' => $date ?: date('Y-m-d'),
            'transaction_type' => 'Transfer',
            'reference_type'   => 'tbl_finance_transfers',
            'reference_id'     => $trf_id,
            'total_amount'     => $amount,
            'description'      => "Funds Transfer {$trf_num}: {$from_acc->account_name} → {$to_acc->account_name}" . (!empty($notes) ? " ({$notes})" : ""),
            'created_by'       => $created_by
        ], $lines);

        if ($tx_res['success']) {
            $this->db->where('id', $trf_id)->update('tbl_finance_transfers', ['transaction_id' => $tx_res['transaction_id']]);
        }

        return [
            'success'         => true,
            'transfer_id'     => $trf_id,
            'transfer_number' => $trf_num,
            'message'         => "Transferred ₹" . number_format($amount, 2) . " from {$from_acc->account_name} to {$to_acc->account_name} successfully."
        ];
    }

    /**
     * Reversal of posted fund transfer (counter-balancing journal entry, non-destructive).
     */
    public function reverse_transfer($school_id, $transfer_id, $reason, $user_id)
    {
        $school_id   = (int)$school_id;
        $transfer_id = (int)$transfer_id;
        $reason      = trim($reason);

        if (empty($reason)) {
            return ['success' => false, 'message' => 'A valid reversal reason is required to void this fund transfer.'];
        }

        $trf = $this->db->get_where('tbl_finance_transfers', [
            'id'        => $transfer_id,
            'school_id' => $school_id,
            'status'    => 'Completed'
        ])->row();

        if (!$trf) {
            return ['success' => false, 'message' => 'Transfer record not found or already reversed.'];
        }

        $from_acc = $this->get_account_by_id($trf->from_account_id, $school_id);
        $to_acc   = $this->get_account_by_id($trf->to_account_id, $school_id);

        // Counter-entry:
        // Debit: Source Account (restores money to from_account)
        // Credit: Destination Account (deducts money from to_account)
        $lines = [
            [
                'account_id'  => $trf->from_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Debit',
                'amount'      => (float)$trf->amount,
                'description' => "Reversal of Transfer {$trf->transfer_number} to {$to_acc->account_name}"
            ],
            [
                'account_id'  => $trf->to_account_id,
                'ledger_id'   => null,
                'entry_type'  => 'Credit',
                'amount'      => (float)$trf->amount,
                'description' => "Reversal of Transfer {$trf->transfer_number} from {$from_acc->account_name}"
            ]
        ];

        $rev_tx = $this->post_double_entry_transaction($school_id, [
            'academic_year_id' => $trf->academic_year_id,
            'transaction_date' => date('Y-m-d'),
            'transaction_type' => 'Transfer',
            'reference_type'   => 'tbl_finance_transfers_reversal',
            'reference_id'     => $transfer_id,
            'total_amount'     => (float)$trf->amount,
            'description'      => "Reversal of Fund Transfer {$trf->transfer_number}: {$reason}",
            'created_by'       => $user_id
        ], $lines);

        if (!$rev_tx['success']) {
            return ['success' => false, 'message' => 'Failed to post reversal journal entry: ' . $rev_tx['message']];
        }

        $this->db->where('id', $transfer_id)->where('school_id', $school_id)->update('tbl_finance_transfers', [
            'status'          => 'Reversed',
            'reversed_reason' => $reason,
            'reversed_by'     => $user_id,
            'reversed_at'     => date('Y-m-d H:i:s'),
            'updated_by'      => $user_id,
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => "Transfer {$trf->transfer_number} has been reversed successfully. Account balances restored."
        ];
    }

    /**
     * Get filtered transfers with pagination, search, date range, and account filters.
     */
    public function get_filtered_transfers($school_id, $filters = [], $academic_year_id = null, $limit = 100)
    {
        $school_id = (int)$school_id;
        $this->db->select('tr.*, 
                           fa.account_name as from_account_name, fa.account_code as from_account_code, fa.account_type as from_account_type, fa.bank_name as from_bank_name,
                           ta.account_name as to_account_name, ta.account_code as to_account_code, ta.account_type as to_account_type, ta.bank_name as to_bank_name,
                           u.name as created_by_name, ru.name as reversed_by_name')
                 ->from('tbl_finance_transfers tr')
                 ->join('tbl_finance_accounts fa', 'fa.id = tr.from_account_id', 'inner')
                 ->join('tbl_finance_accounts ta', 'ta.id = tr.to_account_id', 'inner')
                 ->join('tbl_users u', 'u.user_id = tr.created_by', 'left')
                 ->join('tbl_users ru', 'ru.user_id = tr.reversed_by', 'left')
                 ->where('tr.school_id', $school_id)
                 ->where('tr.is_deleted', 'n');

        if (!empty($academic_year_id)) {
            $this->db->group_start()
                     ->where('tr.academic_year_id', (int)$academic_year_id)
                     ->or_where('tr.academic_year_id IS NULL', null, false)
                     ->group_end();
        }

        if (!empty($filters['from_account_id'])) {
            $this->db->where('tr.from_account_id', (int)$filters['from_account_id']);
        }
        if (!empty($filters['to_account_id'])) {
            $this->db->where('tr.to_account_id', (int)$filters['to_account_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('tr.transfer_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('tr.transfer_date <=', $filters['date_to']);
        }
        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $this->db->where('tr.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                     ->like('tr.transfer_number', $s)
                     ->or_like('tr.reference_no', $s)
                     ->or_like('tr.notes', $s)
                     ->or_like('tr.description', $s)
                     ->or_like('fa.account_name', $s)
                     ->or_like('ta.account_name', $s)
                     ->group_end();
        }

        return $this->db->order_by('tr.transfer_date DESC, tr.id DESC')->limit($limit)->get()->result();
    }

    /**
     * Transfers KPI stat counters.
     */
    public function get_transfers_kpi_stats($school_id, $academic_year_id = null)
    {
        $school_id = (int)$school_id;
        $this->db->select('tr.*, fa.account_type as from_type, ta.account_type as to_type')
                 ->from('tbl_finance_transfers tr')
                 ->join('tbl_finance_accounts fa', 'fa.id = tr.from_account_id', 'inner')
                 ->join('tbl_finance_accounts ta', 'ta.id = tr.to_account_id', 'inner')
                 ->where('tr.school_id', $school_id)
                 ->where('tr.is_deleted', 'n');

        if (!empty($academic_year_id)) {
            $this->db->group_start()
                     ->where('tr.academic_year_id', (int)$academic_year_id)
                     ->or_where('tr.academic_year_id IS NULL', null, false)
                     ->group_end();
        }

        $all = $this->db->get()->result();

        $total_amount     = 0.00;
        $completed_count  = 0;
        $reversed_count   = 0;
        $cash_to_bank_vol = 0.00;
        $bank_to_cash_vol = 0.00;
        $bank_to_bank_vol = 0.00;

        foreach ($all as $t) {
            if ($t->status === 'Completed') {
                $amt = (float)$t->amount;
                $total_amount += $amt;
                $completed_count++;

                if ($t->from_type === 'Cash' && $t->to_type === 'Bank') {
                    $cash_to_bank_vol += $amt;
                } elseif ($t->from_type === 'Bank' && $t->to_type === 'Cash') {
                    $bank_to_cash_vol += $amt;
                } else {
                    $bank_to_bank_vol += $amt;
                }
            } else {
                $reversed_count++;
            }
        }

        return (object)[
            'total_amount'      => $total_amount,
            'completed_count'   => $completed_count,
            'reversed_count'    => $reversed_count,
            'cash_to_bank_vol'  => $cash_to_bank_vol,
            'bank_to_cash_vol'  => $bank_to_cash_vol,
            'bank_to_bank_vol'  => $bank_to_bank_vol,
        ];
    }

    /**
     * Get detailed transfer voucher information with accounting journal lines.
     */
    public function get_transfer_detailed($school_id, $transfer_id)
    {
        $school_id   = (int)$school_id;
        $transfer_id = (int)$transfer_id;

        $trf = $this->db->select('tr.*, 
                                  fa.account_name as from_account_name, fa.account_code as from_account_code, fa.account_type as from_account_type, fa.bank_name as from_bank_name,
                                  ta.account_name as to_account_name, ta.account_code as to_account_code, ta.account_type as to_account_type, ta.bank_name as to_bank_name,
                                  u.name as created_by_name, ru.name as reversed_by_name')
                        ->from('tbl_finance_transfers tr')
                        ->join('tbl_finance_accounts fa', 'fa.id = tr.from_account_id', 'inner')
                        ->join('tbl_finance_accounts ta', 'ta.id = tr.to_account_id', 'inner')
                        ->join('tbl_users u', 'u.user_id = tr.created_by', 'left')
                        ->join('tbl_users ru', 'ru.user_id = tr.reversed_by', 'left')
                        ->where('tr.school_id', $school_id)
                        ->where('tr.id', $transfer_id)
                        ->get()->row();

        if (!$trf) return null;

        $lines = [];
        if (!empty($trf->transaction_id)) {
            $lines = $this->db->select('ti.*, a.account_name, a.account_code, a.account_type')
                              ->from('tbl_finance_transaction_items ti')
                              ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                              ->where('ti.transaction_id', $trf->transaction_id)
                              ->where('ti.school_id', $school_id)
                              ->order_by('ti.entry_type DESC, ti.id ASC')
                              ->get()->result();
        }

        return [
            'transfer' => $trf,
            'lines'    => $lines
        ];
    }

    /**
     * Get detailed account profile with opening balance, current balance, and recent ledger lines.
     */
    public function get_account_detailed($school_id, $account_id)
    {
        $school_id  = (int)$school_id;
        $account_id = (int)$account_id;

        $acc = $this->db->select('a.*, g.group_name, p.account_name as parent_head_name, p.account_code as parent_head_code, u.name as created_by_name')
                        ->from('tbl_finance_accounts a')
                        ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'left')
                        ->join('tbl_finance_accounts p', 'p.id = a.parent_account_id', 'left')
                        ->join('tbl_users u', 'u.user_id = a.created_by', 'left')
                        ->where('a.school_id', $school_id)
                        ->where('a.id', $account_id)
                        ->where('a.is_deleted', 'n')
                        ->get()->row();

        if (!$acc) return null;

        $acc->current_balance = $this->calculate_account_balance($acc->id, $school_id);
        $num = trim((string)($acc->account_number ?? ''));
        $acc->masked_account_number = (strlen($num) > 4) ? ('•••• •••• ' . substr($num, -4)) : ($num ?: '—');

        // Recent 10 transaction lines
        $recent_lines = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc')
                                 ->from('tbl_finance_transaction_items ti')
                                 ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                                 ->where('ti.account_id', $account_id)
                                 ->where('ti.school_id', $school_id)
                                 ->where('t.status', 'Posted')
                                 ->order_by('t.transaction_date DESC, t.id DESC, ti.id DESC')
                                 ->limit(10)
                                 ->get()->result();

        return [
            'account'      => $acc,
            'recent_lines' => $recent_lines
        ];
    }

    public function get_transfers($school_id, $limit = 50)
    {
        $school_id = (int)$school_id;
        return $this->db->select('tr.*, fa.account_name as from_account_name, ta.account_name as to_account_name, u.name as created_by_name')
                        ->from('tbl_finance_transfers tr')
                        ->join('tbl_finance_accounts fa', 'fa.id = tr.from_account_id', 'inner')
                        ->join('tbl_finance_accounts ta', 'ta.id = tr.to_account_id', 'inner')
                        ->join('tbl_users u', 'u.user_id = tr.created_by', 'left')
                        ->where('tr.school_id', $school_id)
                        ->where('tr.is_deleted', 'n')
                        ->order_by('tr.transfer_date DESC, tr.id DESC')
                        ->limit($limit)
                        ->get()->result();
    }

    // =========================================================================
    // 7. FINANCIAL REPORTING ENGINES
    // =========================================================================

    /**
     * Trial Balance: All accounts with accumulated Debits and Credits and net closing balance.
     * Enforces: SUM(Closing Debit) === SUM(Closing Credit).
     */
    public function get_trial_balance($school_id, $as_of_date = null)
    {
        $school_id = (int)$school_id;
        $as_of_date = $as_of_date ?: date('Y-m-d');

        $accounts = $this->db->select('a.*, g.group_name, g.category as group_category')
                             ->from('tbl_finance_accounts a')
                             ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                             ->where('a.school_id', $school_id)
                             ->where('a.is_deleted', 'n')
                             ->order_by('FIELD(g.category, "Asset", "Liability", "Equity", "Income", "Expense"), a.account_code ASC')
                             ->get()->result();

        $rows = [];
        $total_debit = 0.00;
        $total_credit = 0.00;

        foreach ($accounts as $acc) {
            $bal = $this->calculate_account_balance($acc->id, $school_id, $as_of_date);
            if (abs($bal) < 0.001) continue; // Skip zero-balance accounts

            $is_debit = false;
            if (in_array($acc->group_category, ['Asset', 'Expense'])) {
                $is_debit = ($bal >= 0);
            } else {
                $is_debit = ($bal < 0);
            }

            $deb_amt = $is_debit ? abs($bal) : 0.00;
            $cred_amt = !$is_debit ? abs($bal) : 0.00;

            $total_debit += $deb_amt;
            $total_credit += $cred_amt;

            $rows[] = (object)[
                'account_id'    => $acc->id,
                'account_code'  => $acc->account_code,
                'account_name'  => $acc->account_name,
                'category'      => $acc->group_category,
                'group_name'    => $acc->group_name,
                'debit_amount'  => $deb_amt,
                'credit_amount' => $cred_amt
            ];
        }

        return [
            'rows'         => $rows,
            'total_debit'  => round($total_debit, 2),
            'total_credit' => round($total_credit, 2),
            'is_balanced'  => (abs($total_debit - $total_credit) < 0.01),
            'as_of_date'   => $as_of_date
        ];
    }

    /**
     * Income & Expense Statement (Profit & Loss).
     */
    public function get_income_expense_statement($school_id, $date_from = null, $date_to = null)
    {
        $school_id = (int)$school_id;
        $date_from = $date_from ?: date('Y-01-01');
        $date_to = $date_to ?: date('Y-m-d');

        // Income
        $income_rows = $this->db->select('a.id, a.account_code, a.account_name, SUM(ti.credit - ti.debit) as net_amount')
                                ->from('tbl_finance_transaction_items ti')
                                ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                                ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                                ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                                ->where('ti.school_id', $school_id)
                                ->where('g.category', 'Income')
                                ->where('t.status', 'Posted')
                                ->where('t.transaction_date >=', $date_from)
                                ->where('t.transaction_date <=', $date_to)
                                ->group_by('a.id')
                                ->get()->result();

        // Expenses
        $expense_rows = $this->db->select('a.id, a.account_code, a.account_name, SUM(ti.debit - ti.credit) as net_amount')
                                 ->from('tbl_finance_transaction_items ti')
                                 ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                                 ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                                 ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                                 ->where('ti.school_id', $school_id)
                                 ->where('g.category', 'Expense')
                                 ->where('t.status', 'Posted')
                                 ->where('t.transaction_date >=', $date_from)
                                 ->where('t.transaction_date <=', $date_to)
                                 ->group_by('a.id')
                                 ->get()->result();

        $total_income = 0.00;
        foreach ($income_rows as $ir) {
            $total_income += (float)$ir->net_amount;
        }

        $total_expense = 0.00;
        foreach ($expense_rows as $er) {
            $total_expense += (float)$er->net_amount;
        }

        $net_surplus = round($total_income - $total_expense, 2);

        return [
            'income_rows'   => $income_rows,
            'expense_rows'  => $expense_rows,
            'total_income'  => round($total_income, 2),
            'total_expense' => round($total_expense, 2),
            'net_surplus'   => $net_surplus,
            'is_surplus'    => ($net_surplus >= 0),
            'date_from'     => $date_from,
            'date_to'       => $date_to
        ];
    }

    /**
     * Balance Sheet: Assets, Liabilities, and Equity.
     */
    public function get_balance_sheet($school_id, $as_of_date = null)
    {
        $school_id = (int)$school_id;
        $as_of_date = $as_of_date ?: date('Y-m-d');

        $tb = $this->get_trial_balance($school_id, $as_of_date);

        $assets = [];
        $liabilities = [];
        $equity = [];
        $total_assets = 0.00;
        $total_liabilities = 0.00;
        $total_equity = 0.00;

        foreach ($tb['rows'] as $r) {
            if ($r->category === 'Asset') {
                $amt = $r->debit_amount - $r->credit_amount;
                $assets[] = (object)['account_name' => $r->account_name, 'amount' => $amt];
                $total_assets += $amt;
            } elseif ($r->category === 'Liability') {
                $amt = $r->credit_amount - $r->debit_amount;
                $liabilities[] = (object)['account_name' => $r->account_name, 'amount' => $amt];
                $total_liabilities += $amt;
            } elseif ($r->category === 'Equity') {
                $amt = $r->credit_amount - $r->debit_amount;
                $equity[] = (object)['account_name' => $r->account_name, 'amount' => $amt];
                $total_equity += $amt;
            }
        }

        // Add Current Period Surplus/Deficit to Equity
        $pl = $this->get_income_expense_statement($school_id, '1970-01-01', $as_of_date);
        $current_surplus = $pl['net_surplus'];
        if (abs($current_surplus) > 0.001) {
            $equity[] = (object)['account_name' => 'Current Period Surplus / (Deficit)', 'amount' => $current_surplus];
            $total_equity += $current_surplus;
        }

        return [
            'assets'             => $assets,
            'liabilities'        => $liabilities,
            'equity'             => $equity,
            'total_assets'       => round($total_assets, 2),
            'total_liabilities'  => round($total_liabilities, 2),
            'total_equity'       => round($total_equity, 2),
            'total_liab_equity'  => round($total_liabilities + $total_equity, 2),
            'is_balanced'        => (abs($total_assets - ($total_liabilities + $total_equity)) < 0.05),
            'as_of_date'         => $as_of_date
        ];
    }

    /**
     * Day Book: Chronological transaction register for a single date.
     */
    public function get_day_book($school_id, $date = null)
    {
        $school_id = (int)$school_id;
        $date = $date ?: date('Y-m-d');

        return $this->db->select('t.*, ti.entry_type, ti.debit, ti.credit, a.account_name, a.account_code, l.ledger_name')
                        ->from('tbl_finance_transactions t')
                        ->join('tbl_finance_transaction_items ti', 'ti.transaction_id = t.id', 'inner')
                        ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                        ->join('tbl_finance_ledgers l', 'l.id = ti.ledger_id', 'left')
                        ->where('t.school_id', $school_id)
                        ->where('t.transaction_date', $date)
                        ->order_by('t.id ASC, ti.entry_type DESC')
                        ->get()->result();
    }

    /**
     * Cash Book or Bank Book: Running ledger for Cash or Bank accounts.
     */
    public function get_book_statement($account_id, $school_id, $date_from = null, $date_to = null)
    {
        $account_id = (int)$account_id;
        $school_id = (int)$school_id;
        $date_from = $date_from ?: date('Y-m-01');
        $date_to = $date_to ?: date('Y-m-d');

        $acc = $this->get_account_by_id($account_id, $school_id);
        if (!$acc) return null;

        // Opening balance prior to $date_from
        $prev_date = date('Y-m-d', strtotime($date_from . ' -1 day'));
        $opening_bal = $this->calculate_account_balance($account_id, $school_id, $prev_date);

        $lines = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc')
                          ->from('tbl_finance_transaction_items ti')
                          ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                          ->where('ti.account_id', $account_id)
                          ->where('ti.school_id', $school_id)
                          ->where('t.status', 'Posted')
                          ->where('t.transaction_date >=', $date_from)
                          ->where('t.transaction_date <=', $date_to)
                          ->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')
                          ->get()->result();

        $running = $opening_bal;
        foreach ($lines as $l) {
            $running = round($running + ($l->debit - $l->credit), 2);
            $l->running_balance = $running;
        }

        return [
            'account'         => $acc,
            'opening_balance' => $opening_bal,
            'lines'           => $lines,
            'closing_balance' => $running,
            'date_from'       => $date_from,
            'date_to'         => $date_to
        ];
    }

    /**
     * Student Ledger Statement.
     */
    public function get_student_statement($student_id, $school_id, $date_from = null, $date_to = null)
    {
        $student_id = (int)$student_id;
        $school_id = (int)$school_id;

        $ledger = $this->get_or_create_student_ledger($school_id, $student_id);
        $st = $this->db->select('st.*, c.class_name, div.division_name')
                       ->from('tbl_students st')
                       ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
                       ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
                       ->where('st.student_id', $student_id)
                       ->where('st.school_id', $school_id)
                       ->get()->row();

        $lines = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc, a.account_name')
                          ->from('tbl_finance_transaction_items ti')
                          ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                          ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                          ->where('ti.ledger_id', $ledger->id)
                          ->where('ti.school_id', $school_id)
                          ->where('t.status', 'Posted')
                          ->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')
                          ->get()->result();

        $opening = (float)$ledger->opening_balance;
        $running = $opening;

        $total_invoiced = 0.00;
        $total_paid = 0.00;

        foreach ($lines as $l) {
            $running = round($running + ($l->debit - $l->credit), 2);
            $l->running_balance = $running;
            $total_invoiced += (float)$l->debit;
            $total_paid += (float)$l->credit;
        }

        return [
            'student'         => $st,
            'ledger'          => $ledger,
            'opening_balance' => $opening,
            'lines'           => $lines,
            'total_invoiced'  => round($total_invoiced, 2),
            'total_paid'      => round($total_paid, 2),
            'closing_balance' => $running
        ];
    }

    /**
     * Staff Ledger Statement.
     */
    public function get_staff_statement($staff_id, $school_id)
    {
        $staff_id = (int)$staff_id;
        $school_id = (int)$school_id;

        $ledger = $this->get_or_create_staff_ledger($school_id, $staff_id);
        $sf = $this->db->select('sf.staff_id, sf.school_id, sf.employee_code, sf.full_name, sf.email, sf.phone, sf.staff_type, sf.salary, des.designation_name')
                       ->from('tbl_staff sf')
                       ->join('tbl_designations des', 'des.designation_id = sf.designation_id', 'left')
                       ->where('sf.staff_id', $staff_id)
                       ->where('sf.school_id', $school_id)
                       ->get()->row();

        $lines = $this->db->select('ti.*, t.transaction_number, t.transaction_date, t.transaction_type, t.description as tx_desc, a.account_name')
                          ->from('tbl_finance_transaction_items ti')
                          ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                          ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                          ->where('ti.ledger_id', $ledger->id)
                          ->where('ti.school_id', $school_id)
                          ->where('t.status', 'Posted')
                          ->order_by('t.transaction_date ASC, t.id ASC, ti.id ASC')
                          ->get()->result();

        $opening = (float)$ledger->opening_balance;
        $running = $opening;

        $total_accrued = 0.00;
        $total_disbursed = 0.00;

        foreach ($lines as $l) {
            $running = round($running + ($l->credit - $l->debit), 2);
            $l->running_balance = $running;
            $total_accrued += (float)$l->credit;
            $total_disbursed += (float)$l->debit;
        }

        return [
            'staff'           => $sf,
            'ledger'          => $ledger,
            'opening_balance' => $opening,
            'lines'           => $lines,
            'total_accrued'   => round($total_accrued, 2),
            'total_disbursed' => round($total_disbursed, 2),
            'closing_balance' => $running
        ];
    }

    // =========================================================================
    // 8. DASHBOARD KPIS & VISUAL METRICS
    // =========================================================================

    public function get_dashboard_metrics($school_id, $academic_year_id = null)
    {
        $school_id = (int)$school_id;

        // 1. Total Receivable (Pending from students / accounts receivable)
        $rec_acc = $this->get_account_by_code('1030', $school_id);
        $ar_balance = $rec_acc ? $this->calculate_account_balance($rec_acc->id, $school_id) : 0.00;
        
        $fee_qb = $this->db->select('SUM(due_amount) as total_due')
                            ->from('tbl_finance_fee_assignments')
                            ->where('school_id', $school_id)
                            ->where('is_deleted', 'n')
                            ->where_in('status', ['Pending', 'Partially_Paid']);
        if ($academic_year_id) {
            $fee_qb->where('academic_year_id', (int)$academic_year_id);
        }
        $fee_row = $fee_qb->get()->row();
        $fee_due = ($fee_row && $fee_row->total_due !== null) ? (float)$fee_row->total_due : 0.00;
        $total_receivable = max((float)$ar_balance, (float)$fee_due);

        // 2. Total Received (Actual amount received from fee collections / payments)
        $coll_qb = $this->db->select('SUM(amount) as total_paid')
                             ->from('tbl_finance_fee_collections')
                             ->where('school_id', $school_id)
                             ->where('status', 'Valid')
                             ->where('is_deleted', 'n');
        if ($academic_year_id) {
            $coll_qb->where('academic_year_id', (int)$academic_year_id);
        }
        $coll_row = $coll_qb->get()->row();
        if ($coll_row && $coll_row->total_paid !== null && (float)$coll_row->total_paid > 0) {
            $total_received = (float)$coll_row->total_paid;
        } else {
            $inc_qb = $this->db->select('SUM(ti.credit - ti.debit) as total_income')
                                ->from('tbl_finance_transaction_items ti')
                                ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                                ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                                ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                                ->where('ti.school_id', $school_id)
                                ->where('g.category', 'Income')
                                ->where('t.status', 'Posted');
            if ($academic_year_id) {
                $inc_qb->where('t.academic_year_id', (int)$academic_year_id);
            }
            $inc_row = $inc_qb->get()->row();
            $total_received = max(0.00, (float)($inc_row->total_income ?? 0.00));
        }

        // 3. Total Payable (Amount school currently owes: Staff + Vendor)
        $pay_acc = $this->get_account_by_code('2010', $school_id);
        $vendor_acc = $this->get_account_by_code('2020', $school_id);
        $staff_payable = $pay_acc ? $this->calculate_account_balance($pay_acc->id, $school_id) : 0.00;
        $vendor_payable = $vendor_acc ? $this->calculate_account_balance($vendor_acc->id, $school_id) : 0.00;
        $total_payable = max(0.00, $staff_payable + $vendor_payable);

        // 4. Total Expenses (Recorded expenses)
        $exp_qb = $this->db->select('SUM(ti.debit - ti.credit) as total_expense')
                            ->from('tbl_finance_transaction_items ti')
                            ->join('tbl_finance_transactions t', 't.id = ti.transaction_id', 'inner')
                            ->join('tbl_finance_accounts a', 'a.id = ti.account_id', 'inner')
                            ->join('tbl_finance_account_groups g', 'g.id = a.account_group_id', 'inner')
                            ->where('ti.school_id', $school_id)
                            ->where('g.category', 'Expense')
                            ->where('t.status', 'Posted');
        if ($academic_year_id) {
            $exp_qb->where('t.academic_year_id', (int)$academic_year_id);
        }
        $exp_row = $exp_qb->get()->row();
        $total_expenses = max(0.00, (float)($exp_row->total_expense ?? 0.00));
        if ($total_expenses == 0.00) {
            $direct_exp_qb = $this->db->select('SUM(amount) as total_exp')
                                   ->from('tbl_finance_expenses')
                                   ->where('school_id', $school_id)
                                   ->where('is_deleted', 'n')
                                   ->where('status', 'Approved');
            if ($academic_year_id) {
                $direct_exp_qb->where('academic_year_id', (int)$academic_year_id);
            }
            $direct_exp = $direct_exp_qb->get()->row();
            $total_expenses = max(0.00, (float)($direct_exp->total_exp ?? 0.00));
        }

        // 5. Cash Balance (Live from Cash Account Heads + Custom Cash Accounts)
        $cash_accounts = $this->db->select('id')->from('tbl_finance_accounts')->where('school_id', $school_id)->where('account_type', 'Cash')->where('is_deleted', 'n')->get()->result();
        $cash_balance = 0.00;
        foreach ($cash_accounts as $ca) {
            $cash_balance += $this->calculate_account_balance($ca->id, $school_id);
        }
        $custom_cash = $this->db->select('id')->from('tbl_finance_custom_accounts')->where('school_id', $school_id)->where('account_type', 'Cash')->where('is_deleted', 'n')->get()->result();
        foreach ($custom_cash as $cca) {
            $cash_balance += $this->calculate_custom_account_balance($cca->id, $school_id);
        }

        // 6. Bank Balance (Live from Bank Account Heads + Custom Bank Accounts)
        $bank_accounts = $this->db->select('id')->from('tbl_finance_accounts')->where('school_id', $school_id)->where('account_type', 'Bank')->where('is_deleted', 'n')->get()->result();
        $bank_balance = 0.00;
        foreach ($bank_accounts as $ba) {
            $bank_balance += $this->calculate_account_balance($ba->id, $school_id);
        }
        $custom_bank = $this->db->select('id')->from('tbl_finance_custom_accounts')->where('school_id', $school_id)->where('account_type', 'Bank')->where('is_deleted', 'n')->get()->result();
        foreach ($custom_bank as $cba) {
            $bank_balance += $this->calculate_custom_account_balance($cba->id, $school_id);
        }

        // 7. Pending Payments (Outstanding fee dues)
        $pending_payments = (float)$fee_due;

        // 8. Active Accounts
        $active_heads_cnt = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_accounts');
        $active_custom_cnt = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_custom_accounts');
        $active_accounts = $active_heads_cnt + $active_custom_cnt;

        // 9. Account Summary
        $summary = [
            'account_groups'   => $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_account_groups'),
            'account_heads'    => $active_heads_cnt,
            'custom_accounts'  => $active_custom_cnt,
            'student_accounts' => $this->db->where('school_id', $school_id)->where('entity_type', 'Student')->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_ledgers'),
            'staff_accounts'   => $this->db->where('school_id', $school_id)->where('entity_type', 'Staff')->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_ledgers'),
        ];

        // 10. Recent Transactions
        // Date, Reference, Description, Account, Type, Debit, Credit, Status
        $recent_txs = $this->db->select('t.id, t.school_id, t.transaction_number, t.transaction_date, t.transaction_type, t.description, t.total_amount, t.status,
                                        (SELECT a2.account_name FROM tbl_finance_transaction_items ti2 JOIN tbl_finance_accounts a2 ON a2.id = ti2.account_id WHERE ti2.transaction_id = t.id LIMIT 1) as account_name,
                                        (SELECT SUM(ti3.debit) FROM tbl_finance_transaction_items ti3 WHERE ti3.transaction_id = t.id) as debit_amount,
                                        (SELECT SUM(ti4.credit) FROM tbl_finance_transaction_items ti4 WHERE ti4.transaction_id = t.id) as credit_amount')
                               ->from('tbl_finance_transactions t')
                               ->where('t.school_id', $school_id)
                               ->order_by('t.transaction_date', 'DESC')
                               ->order_by('t.id', 'DESC')
                               ->limit(10)
                               ->get()->result();

        return [
            'total_receivable'    => round($total_receivable, 2),
            'total_received'      => round($total_received, 2),
            'total_payable'       => round($total_payable, 2),
            'total_expenses'      => round($total_expenses, 2),
            'cash_balance'        => round($cash_balance, 2),
            'bank_balance'        => round($bank_balance, 2),
            'pending_payments'    => round($pending_payments, 2),
            'active_accounts'     => $active_accounts,
            'account_summary'     => $summary,
            'recent_transactions' => $recent_txs,
            // Backwards compatibility keys
            'total_income'        => round($total_received, 2),
            'total_expense'       => round($total_expenses, 2),
            'student_receivables' => round($total_receivable, 2),
            'staff_payables'      => round($total_payable, 2),
            'total_cash'          => round($cash_balance, 2),
            'total_bank'          => round($bank_balance, 2),
        ];
    }

    // =========================================================================
    // 9. FEE & FINANCE FOUNDATION (FEES & INVOICES & COLLECTIONS)
    // =========================================================================

    public function get_fee_types($school_id)
    {
        $school_id = (int)$school_id;
        return $this->db->select('ft.id, ft.school_id, ft.type_name, ft.type_code, ft.account_id, ft.description, ft.status, ft.created_at, a.account_name, a.account_code')
                        ->from('tbl_finance_fee_types ft')
                        ->join('tbl_finance_accounts a', 'a.id = ft.account_id', 'left')
                        ->where('ft.school_id', $school_id)
                        ->where('ft.is_deleted', 'n')
                        ->order_by('ft.type_name', 'ASC')
                        ->get()->result();
    }

    public function get_fee_type_by_id($id, $school_id)
    {
        return $this->db->select('id, school_id, type_name, type_code, account_id, description, status')
                        ->where('id', (int)$id)
                        ->where('school_id', (int)$school_id)
                        ->where('is_deleted', 'n')
                        ->get('tbl_finance_fee_types')->row();
    }

    public function save_fee_type($data, $school_id)
    {
        $school_id = (int)$school_id;
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        unset($data['id']);
        $data['school_id'] = $school_id;

        if ($id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_fee_types', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_fee_types', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_fee_type($id, $school_id)
    {
        return $this->db->where('id', (int)$id)
                        ->where('school_id', (int)$school_id)
                        ->update('tbl_finance_fee_types', ['is_deleted' => 'y', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function get_fee_structures($school_id, $academic_year_id = null)
    {
        $school_id = (int)$school_id;
        $this->db->select('fs.id, fs.school_id, fs.academic_year_id, fs.class_id, fs.fee_type_id, fs.structure_name, fs.amount, fs.frequency, fs.due_date, fs.status, fs.created_at, ft.type_name, c.class_name')
                 ->from('tbl_finance_fee_structures fs')
                 ->join('tbl_finance_fee_types ft', 'ft.id = fs.fee_type_id', 'left')
                 ->join('tbl_classes c', 'c.class_id = fs.class_id', 'left')
                 ->where('fs.school_id', $school_id)
                 ->where('fs.is_deleted', 'n');

        if ($academic_year_id) {
            $this->db->where('fs.academic_year_id', (int)$academic_year_id);
        }

        return $this->db->order_by('fs.id', 'DESC')->get()->result();
    }

    public function get_fee_structure_by_id($id, $school_id)
    {
        return $this->db->select('id, school_id, academic_year_id, class_id, fee_type_id, structure_name, amount, frequency, due_date, status')
                        ->where('id', (int)$id)
                        ->where('school_id', (int)$school_id)
                        ->where('is_deleted', 'n')
                        ->get('tbl_finance_fee_structures')->row();
    }

    public function save_fee_structure($data, $school_id)
    {
        $school_id = (int)$school_id;
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        unset($data['id']);
        $data['school_id'] = $school_id;

        if ($id > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $id)->where('school_id', $school_id)->update('tbl_finance_fee_structures', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_finance_fee_structures', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_fee_structure($id, $school_id)
    {
        return $this->db->where('id', (int)$id)
                        ->where('school_id', (int)$school_id)
                        ->update('tbl_finance_fee_structures', ['is_deleted' => 'y', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function get_fee_assignments($school_id, $academic_year_id = null, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('fa.id, fa.school_id, fa.academic_year_id, fa.student_id, fa.fee_structure_id, fa.ledger_id, fa.invoice_number, fa.invoice_date, fa.due_date, fa.assigned_amount, fa.discount_amount, fa.net_amount, fa.paid_amount, fa.due_amount, fa.status, fa.remarks, fa.created_at, s.first_name, s.last_name, s.admission_number, s.admission_number as admission_no, c.class_name, d.division_name, ft.type_name as fee_name')
                 ->from('tbl_finance_fee_assignments fa')
                 ->join('tbl_students s', 's.student_id = fa.student_id', 'left')
                 ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
                 ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
                 ->join('tbl_finance_fee_structures fs', 'fs.id = fa.fee_structure_id', 'left')
                 ->join('tbl_finance_fee_types ft', 'ft.id = fs.fee_type_id', 'left')
                 ->where('fa.school_id', $school_id)
                 ->where('fa.is_deleted', 'n');

        if ($academic_year_id) {
            $this->db->where('fa.academic_year_id', (int)$academic_year_id);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('fa.student_id', (int)$filters['student_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('fa.status', $filters['status']);
        }

        return $this->db->order_by('fa.id', 'DESC')->get()->result();
    }

    public function assign_fee_structure_to_students($school_id, $academic_year_id, $student_ids, $fee_structure_id, $due_date, $created_by = null)
    {
        $school_id = (int)$school_id;
        $academic_year_id = (int)$academic_year_id;
        $struct = $this->get_fee_structure_by_id($fee_structure_id, $school_id);
        if (!$struct) {
            return ['success' => false, 'message' => 'Fee structure not found.'];
        }

        $type = $this->get_fee_type_by_id($struct->fee_type_id, $school_id);
        $income_account_id = $type ? (int)$type->account_id : 0;
        if ($income_account_id <= 0) {
            $def_inc = $this->get_account_by_code('4010', $school_id);
            $income_account_id = $def_inc ? (int)$def_inc->id : 0;
        }

        $receivable_acc = $this->get_account_by_code('1030', $school_id);
        $receivable_account_id = $receivable_acc ? (int)$receivable_acc->id : 0;

        $assigned_count = 0;
        $this->db->trans_begin();

        try {
            foreach ($student_ids as $stu_id) {
                $stu_id = (int)$stu_id;
                if ($stu_id <= 0) continue;

                // Ensure student ledger exists
                $ledger = $this->get_or_create_sub_ledger($school_id, 'Student', $stu_id);
                $ledger_id = (int)$ledger->id;

                $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $amount = (float)$struct->amount;

                // 1. Insert Fee Assignment
                $assign_data = [
                    'school_id'        => $school_id,
                    'academic_year_id' => $academic_year_id,
                    'student_id'       => $stu_id,
                    'fee_structure_id' => (int)$struct->id,
                    'ledger_id'        => $ledger_id,
                    'invoice_number'   => $invoice_no,
                    'invoice_date'     => date('Y-m-d'),
                    'due_date'         => $due_date ?: ($struct->due_date ?: date('Y-m-d', strtotime('+30 days'))),
                    'assigned_amount'  => $amount,
                    'discount_amount'  => 0.00,
                    'net_amount'       => $amount,
                    'paid_amount'      => 0.00,
                    'due_amount'       => $amount,
                    'status'           => 'Pending',
                    'created_by'       => $created_by,
                    'created_at'       => date('Y-m-d H:i:s'),
                ];
                $this->db->insert('tbl_finance_fee_assignments', $assign_data);
                $assign_id = $this->db->insert_id();

                // 2. Post Double-Entry Journal Transaction
                if ($receivable_account_id > 0 && $income_account_id > 0 && $amount > 0) {
                    $txn_number = 'TXN-INV-' . date('Ymd') . '-' . str_pad($assign_id, 4, '0', STR_PAD_LEFT);
                    $txn_data = [
                        'school_id'          => $school_id,
                        'academic_year_id'   => $academic_year_id,
                        'transaction_number' => $txn_number,
                        'transaction_date'   => date('Y-m-d'),
                        'transaction_type'   => 'Fee_Invoice',
                        'reference_type'     => 'tbl_finance_fee_assignments',
                        'reference_id'       => $assign_id,
                        'total_amount'       => $amount,
                        'description'        => "Fee invoice {$invoice_no} assigned ({$struct->structure_name})",
                        'status'             => 'Posted',
                        'created_by'         => $created_by,
                        'created_at'         => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('tbl_finance_transactions', $txn_data);
                    $txn_id = $this->db->insert_id();

                    // Line 1: Debit Student Accounts Receivable
                    $this->db->insert('tbl_finance_transaction_items', [
                        'transaction_id' => $txn_id,
                        'school_id'      => $school_id,
                        'account_id'     => $receivable_account_id,
                        'ledger_id'      => $ledger_id,
                        'entry_type'     => 'Debit',
                        'amount'         => $amount,
                        'debit'          => $amount,
                        'credit'         => 0.00,
                        'description'    => "Fee Invoice {$invoice_no}",
                        'student_id'     => $stu_id,
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);

                    // Line 2: Credit Fee Income Account
                    $this->db->insert('tbl_finance_transaction_items', [
                        'transaction_id' => $txn_id,
                        'school_id'      => $school_id,
                        'account_id'     => $income_account_id,
                        'ledger_id'      => null,
                        'entry_type'     => 'Credit',
                        'amount'         => $amount,
                        'debit'          => 0.00,
                        'credit'         => $amount,
                        'description'    => "Fee Income: {$struct->structure_name}",
                        'student_id'     => $stu_id,
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);

                    $this->db->where('id', $assign_id)->update('tbl_finance_fee_assignments', ['transaction_id' => $txn_id]);
                }

                $assigned_count++;
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Database error occurred during fee assignment.'];
            }

            $this->db->trans_commit();
            return ['success' => true, 'count' => $assigned_count, 'message' => "Successfully assigned fee to {$assigned_count} student(s)."];
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function get_fee_collections($school_id, $academic_year_id = null, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('fc.id, fc.school_id, fc.academic_year_id, fc.student_id, fc.fee_assignment_id, fc.ledger_id, fc.receipt_number, fc.receipt_date, fc.amount, fc.payment_mode, fc.deposit_account_id, fc.reference_number, fc.transaction_id, fc.remarks, fc.status, fc.created_at, s.first_name, s.last_name, s.admission_number, s.admission_number as admission_no, c.class_name, a.account_name as deposit_account_name')
                 ->from('tbl_finance_fee_collections fc')
                 ->join('tbl_students s', 's.student_id = fc.student_id', 'left')
                 ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
                 ->join('tbl_finance_accounts a', 'a.id = fc.deposit_account_id', 'left')
                 ->where('fc.school_id', $school_id)
                 ->where('fc.is_deleted', 'n');

        if ($academic_year_id) {
            $this->db->where('fc.academic_year_id', (int)$academic_year_id);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('fc.student_id', (int)$filters['student_id']);
        }

        return $this->db->order_by('fc.id', 'DESC')->get()->result();
    }

    public function collect_fee_payment($payment_data, $school_id, $created_by = null)
    {
        $school_id = (int)$school_id;
        $student_id = (int)$payment_data['student_id'];
        $amount = (float)$payment_data['amount'];
        $assignment_id = !empty($payment_data['fee_assignment_id']) ? (int)$payment_data['fee_assignment_id'] : null;
        $deposit_account_id = (int)$payment_data['deposit_account_id'];
        $payment_mode = $payment_data['payment_mode'] ?: 'Cash';
        $academic_year_id = !empty($payment_data['academic_year_id']) ? (int)$payment_data['academic_year_id'] : get_current_academic_year_id($school_id);

        if ($amount <= 0 || $student_id <= 0 || $deposit_account_id <= 0) {
            return ['success' => false, 'message' => 'Invalid payment data provided.'];
        }

        $receivable_acc = $this->get_account_by_code('1030', $school_id);
        $receivable_account_id = $receivable_acc ? (int)$receivable_acc->id : 0;
        if ($receivable_account_id <= 0) {
            return ['success' => false, 'message' => 'Student Receivables account (1030) not configured for this school.'];
        }

        $this->db->trans_begin();

        try {
            $ledger = $this->get_or_create_sub_ledger($school_id, 'Student', $student_id);
            $ledger_id = (int)$ledger->id;
            $receipt_no = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // 1. Record fee collection
            $coll_data = [
                'school_id'          => $school_id,
                'academic_year_id'   => $academic_year_id,
                'student_id'         => $student_id,
                'fee_assignment_id'  => $assignment_id,
                'ledger_id'          => $ledger_id,
                'receipt_number'     => $receipt_no,
                'receipt_date'       => $payment_data['payment_date'] ?: date('Y-m-d'),
                'amount'             => $amount,
                'payment_mode'       => $payment_mode,
                'deposit_account_id' => $deposit_account_id,
                'reference_number'   => $payment_data['reference_number'] ?? null,
                'remarks'            => $payment_data['remarks'] ?? null,
                'status'             => 'Valid',
                'created_by'         => $created_by,
                'created_at'         => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('tbl_finance_fee_collections', $coll_data);
            $coll_id = $this->db->insert_id();

            // 2. Post Double-Entry Journal Transaction
            $txn_number = 'TXN-REC-' . date('Ymd') . '-' . str_pad($coll_id, 4, '0', STR_PAD_LEFT);
            $txn_data = [
                'school_id'          => $school_id,
                'academic_year_id'   => $academic_year_id,
                'transaction_number' => $txn_number,
                'transaction_date'   => $payment_data['payment_date'] ?: date('Y-m-d'),
                'transaction_type'   => 'Fee_Payment',
                'reference_type'     => 'tbl_finance_fee_collections',
                'reference_id'       => $coll_id,
                'total_amount'       => $amount,
                'description'        => "Fee receipt {$receipt_no} collected via {$payment_mode}",
                'status'             => 'Posted',
                'created_by'         => $created_by,
                'created_at'         => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbl_finance_transactions', $txn_data);
            $txn_id = $this->db->insert_id();

            // Line 1: Debit Cash or Bank (Asset Increase)
            $this->db->insert('tbl_finance_transaction_items', [
                'transaction_id' => $txn_id,
                'school_id'      => $school_id,
                'account_id'     => $deposit_account_id,
                'ledger_id'      => null,
                'entry_type'     => 'Debit',
                'amount'         => $amount,
                'debit'          => $amount,
                'credit'         => 0.00,
                'description'    => "Receipt {$receipt_no} deposit ({$payment_mode})",
                'student_id'     => $student_id,
                'created_at'     => date('Y-m-d H:i:s')
            ]);

            // Line 2: Credit Student Accounts Receivable (Asset Decrease)
            $this->db->insert('tbl_finance_transaction_items', [
                'transaction_id' => $txn_id,
                'school_id'      => $school_id,
                'account_id'     => $receivable_account_id,
                'ledger_id'      => $ledger_id,
                'entry_type'     => 'Credit',
                'amount'         => $amount,
                'debit'          => 0.00,
                'credit'         => $amount,
                'description'    => "Fee Receipt {$receipt_no} applied to student ledger",
                'student_id'     => $student_id,
                'created_at'     => date('Y-m-d H:i:s')
            ]);

            $this->db->where('id', $coll_id)->update('tbl_finance_fee_collections', ['transaction_id' => $txn_id]);

            // 3. Update Assignment if linked
            if ($assignment_id > 0) {
                $assign = $this->db->select('id, net_amount, paid_amount, due_amount')
                                   ->where('id', $assignment_id)
                                   ->where('school_id', $school_id)
                                   ->get('tbl_finance_fee_assignments')->row();
                if ($assign) {
                    $new_paid = (float)$assign->paid_amount + $amount;
                    $new_due = max(0, (float)$assign->net_amount - $new_paid);
                    $new_status = $new_due <= 0.001 ? 'Paid' : 'Partially_Paid';
                    $this->db->where('id', $assignment_id)->update('tbl_finance_fee_assignments', [
                        'paid_amount' => $new_paid,
                        'due_amount'  => $new_due,
                        'status'      => $new_status,
                        'updated_at'  => date('Y-m-d H:i:s')
                    ]);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Database transaction failed during fee collection.'];
            }

            $this->db->trans_commit();
            return ['success' => true, 'receipt_id' => $coll_id, 'receipt_number' => $receipt_no, 'message' => 'Payment collected successfully.'];
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function get_pending_fees($school_id, $academic_year_id = null, $filters = [])
    {
        $school_id = (int)$school_id;
        $this->db->select('fa.id, fa.school_id, fa.academic_year_id, fa.student_id, fa.invoice_number, fa.due_date, fa.net_amount, fa.paid_amount, fa.due_amount, fa.status, s.first_name, s.last_name, s.admission_number, s.admission_number as admission_no, c.class_name, d.division_name, ft.type_name as fee_name')
                 ->from('tbl_finance_fee_assignments fa')
                 ->join('tbl_students s', 's.student_id = fa.student_id', 'left')
                 ->join('tbl_classes c', 'c.class_id = s.class_id', 'left')
                 ->join('tbl_divisions d', 'd.division_id = s.division_id', 'left')
                 ->join('tbl_finance_fee_structures fs', 'fs.id = fa.fee_structure_id', 'left')
                 ->join('tbl_finance_fee_types ft', 'ft.id = fs.fee_type_id', 'left')
                 ->where('fa.school_id', $school_id)
                 ->where('fa.is_deleted', 'n')
                 ->where('fa.due_amount >', 0)
                 ->where_in('fa.status', ['Pending', 'Partially_Paid']);

        if ($academic_year_id) {
            $this->db->where('fa.academic_year_id', (int)$academic_year_id);
        }

        return $this->db->order_by('fa.due_date', 'ASC')->get()->result();
    }
}

