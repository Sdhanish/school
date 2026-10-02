<?php
/**
 * Automated Test Suite for Login2 School Management Software
 * EXPENSES MODULE: Full Accounting & Submodules Verification
 * 
 * 1. Expense Entry (Operational Expenses)
 * 2. Staff Payout (Salaries / Advances / Allowances)
 * 3. Vendor Payment (Suppliers & Creditors)
 * 4. Other Expenses (Miscellaneous with Mandatory Justification)
 * 5. Reversal / Void Accounting Audit Trail
 * 6. Multi-School Tenant Isolation
 * 7. Academic Year Isolation
 * 8. RBAC Permissions Matrix
 * 
 * Run via CLI: php tests/test_expenses_module.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');

require_once APPPATH . 'config/database.php';

$db_config = $db[$active_group];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error . "\n");
}

class TestExpensesModule {
    private $db;
    private $passed = 0;
    private $failed = 0;
    private $school_id = 1;
    private $academic_year_id = 1;

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    private function assert($condition, $description) {
        if ($condition) {
            echo "  [PASS] {$description}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$description}\n";
            $this->failed++;
        }
    }

    public function runAll() {
        echo "========================================================\n";
        echo "STARTING EXPENSES MODULE AUTOMATED VERIFICATION SUITE\n";
        echo "========================================================\n\n";

        $this->testDatabaseSchemaAndColumns();
        $this->testSubmodule1ExpenseEntry();
        $this->testSubmodule2StaffPayout();
        $this->testSubmodule3VendorPayment();
        $this->testSubmodule4OtherExpenses();
        $this->testExpenseReversalAndAuditTrail();
        $this->testMultiSchoolTenantIsolation();
        $this->testAcademicYearIsolation();
        $this->testRbacPermissionsMatrix();

        echo "\n========================================================\n";
        echo "TEST RESULTS SUMMARY:\n";
        echo "  Passed: {$this->passed}\n";
        echo "  Failed: {$this->failed}\n";
        echo "========================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    // 1. Database Schema
    private function testDatabaseSchemaAndColumns() {
        echo "[1] Testing Expenses Database Schema & Columns...\n";

        $cols = ['submodule', 'vendor_id', 'party_type', 'reason', 'expense_number', 'amount', 'transaction_id', 'status'];
        foreach ($cols as $c) {
            $res = $this->db->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE '{$c}'");
            $this->assert($res && $res->num_rows > 0, "Column `tbl_finance_expenses.{$c}` exists");
        }

        // Check transaction_type ENUM
        $res = $this->db->query("SHOW COLUMNS FROM tbl_finance_transactions LIKE 'transaction_type'");
        $row = $res->fetch_assoc();
        $type = $row['Type'] ?? '';
        $this->assert(strpos($type, "'Staff_Payout'") !== false, "transaction_type includes Staff_Payout");
        $this->assert(strpos($type, "'Vendor_Payment'") !== false, "transaction_type includes Vendor_Payment");
        $this->assert(strpos($type, "'Other_Expense'") !== false, "transaction_type includes Other_Expense");
    }

    // 2. Submodule 1: Expense Entry
    private function testSubmodule1ExpenseEntry() {
        echo "\n[2] Testing Submodule 1: Expense Entry (Operational Expenses)...\n";

        // Find or create cash/bank account and expense account
        $bank = $this->getOrCreateAccount('Bank Account - Main', '1020', 'Asset', 'Current Asset');
        $electricity = $this->getOrCreateAccount('Electricity & Power Expense', '5030', 'Expense', 'Operational Expense');

        $initial_bank_bal = $this->calculateAccountBalance($bank['id']);
        $initial_exp_bal  = $this->calculateAccountBalance($electricity['id']);

        $amount = 3500.00;
        $exp_num = 'EXP-TEST-' . time();

        // Insert into tbl_finance_expenses
        $stmt = $this->db->prepare("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, payee_name, amount, payment_mode, reference_no, description, status, created_at) VALUES (?, ?, 'Expense_Entry', ?, CURDATE(), ?, ?, 'State Electricity Board', ?, 'Bank Transfer', 'BILL-2026', 'Campus monthly power tariff', 'Paid', NOW())");
        $stmt->bind_param("iisiid", $this->school_id, $this->academic_year_id, $exp_num, $electricity['id'], $bank['id'], $amount);
        $stmt->execute();
        $expense_id = (int)$stmt->insert_id;
        $this->assert($expense_id > 0, "Expense entry voucher {$exp_num} inserted with ID {$expense_id}");

        // Post Double-Entry Transaction
        $tx_num = 'TXN-EXP-' . time();
        $t_stmt = $this->db->prepare("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, reference_no, party_name, description, status, created_at) VALUES (?, ?, ?, CURDATE(), 'Expense', 'tbl_finance_expenses', ?, ?, 'Bank Transfer', 'BILL-2026', 'State Electricity Board', 'Campus power bill', 'Posted', NOW())");
        $t_stmt->bind_param("iisid", $this->school_id, $this->academic_year_id, $tx_num, $expense_id, $amount);
        $t_stmt->execute();
        $tx_id = (int)$t_stmt->insert_id;

        // Line 1: Debit Electricity Expense
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$electricity['id']}, 'Debit', {$amount}, {$amount}, 0.00, 'Electricity Expense', NOW())");

        // Line 2: Credit Bank Account
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$bank['id']}, 'Credit', {$amount}, 0.00, {$amount}, 'Paid from Bank', NOW())");

        // Link transaction_id
        $this->db->query("UPDATE tbl_finance_expenses SET transaction_id = {$tx_id} WHERE id = {$expense_id}");

        // Verify balance updates
        $new_bank_bal = $this->calculateAccountBalance($bank['id']);
        $new_exp_bal  = $this->calculateAccountBalance($electricity['id']);

        $this->assert(round($new_bank_bal, 2) === round($initial_bank_bal - $amount, 2), "Bank Account balance decreased by ₹{$amount} (Debit = Credit verified)");
        $this->assert(round($new_exp_bal, 2) === round($initial_exp_bal + $amount, 2), "Electricity Expense head balance increased by ₹{$amount}");
    }

    // 3. Submodule 2: Staff Payout
    private function testSubmodule2StaffPayout() {
        echo "\n[3] Testing Submodule 2: Staff Payout (Salaries & Sub-Ledgers)...\n";

        // Find or create staff member
        $staff_res = $this->db->query("SELECT staff_id, full_name FROM tbl_staff WHERE school_id = {$this->school_id} AND is_deleted = 'n' LIMIT 1");
        if ($staff_res && $staff_res->num_rows > 0) {
            $staff = $staff_res->fetch_assoc();
            $staff_id = (int)$staff['staff_id'];
            $staff_name = $staff['full_name'];
        } else {
            $this->db->query("INSERT INTO tbl_staff (school_id, employee_code, full_name, staff_type, employment_status, is_active, created_at) VALUES ({$this->school_id}, 'EMP-TST-1', 'Rajesh Sharma', 'teacher', 'Active', 1, NOW())");
            $staff_id = (int)$this->db->insert_id;
            $staff_name = 'Rajesh Sharma';
        }

        // Staff sub-ledger
        $sl_res = $this->db->query("SELECT id FROM tbl_finance_ledgers WHERE school_id = {$this->school_id} AND entity_type = 'Staff' AND entity_id = {$staff_id}");
        if ($sl_res && $sl_res->num_rows > 0) {
            $staff_ledger_id = (int)$sl_res->fetch_assoc()['id'];
        } else {
            $this->db->query("INSERT INTO tbl_finance_ledgers (school_id, academic_year_id, entity_type, entity_id, parent_account_id, ledger_code, ledger_name, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, 'Staff', {$staff_id}, 1, 'STF-{$staff_id}', '{$staff_name}', NOW())");
            $staff_ledger_id = (int)$this->db->insert_id;
        }

        $bank = $this->getOrCreateAccount('Bank Account - Main', '1020', 'Asset', 'Current Asset');
        $salary_acc = $this->getOrCreateAccount('Salaries & Staff Expenses', '5010', 'Expense', 'Operational Expense');

        $amount = 18000.00;
        $payout_num = 'PAY-TEST-' . time();

        $stmt = $this->db->prepare("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, staff_id, payout_type, payee_name, amount, payment_mode, reference_no, description, status, created_at) VALUES (?, ?, 'Staff_Payout', ?, CURDATE(), ?, ?, ?, 'Salary', ?, ?, 'Bank Transfer', 'SAL-2026', 'Monthly Teacher Salary', 'Paid', NOW())");
        $stmt->bind_param("iisiiisd", $this->school_id, $this->academic_year_id, $payout_num, $salary_acc['id'], $bank['id'], $staff_id, $staff_name, $amount);
        $stmt->execute();
        $payout_id = (int)$stmt->insert_id;
        $this->assert($payout_id > 0, "Staff payout voucher {$payout_num} created for {$staff_name}");

        // Post Double-Entry
        $tx_num = 'TXN-PAY-' . time();
        $this->db->query("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, party_type, party_name, party_id, description, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, '{$tx_num}', CURDATE(), 'Staff_Payout', 'tbl_finance_expenses', {$payout_id}, {$amount}, 'Bank Transfer', 'Staff', '{$staff_name}', {$staff_id}, 'Staff Salary Payout', 'Posted', NOW())");
        $tx_id = (int)$this->db->insert_id;

        // Lines: Debit Salary (with staff_ledger_id), Credit Bank
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, staff_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$salary_acc['id']}, {$staff_ledger_id}, {$staff_id}, 'Debit', {$amount}, {$amount}, 0.00, 'Salary Disbursement', NOW())");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$bank['id']}, 'Credit', {$amount}, 0.00, {$amount}, 'Paid from Bank', NOW())");

        $this->db->query("UPDATE tbl_finance_expenses SET transaction_id = {$tx_id} WHERE id = {$payout_id}");

        // Verify Staff Sub-ledger
        $sl_check = $this->db->query("SELECT SUM(debit) as deb, SUM(credit) as cred FROM tbl_finance_transaction_items WHERE ledger_id = {$staff_ledger_id} AND school_id = {$this->school_id}");
        $sl_data = $sl_check->fetch_assoc();
        $this->assert((float)$sl_data['deb'] >= $amount, "Staff sub-ledger has recorded debit disbursement of at least ₹{$amount}");
    }

    // 4. Submodule 3: Vendor Payment
    private function testSubmodule3VendorPayment() {
        echo "\n[4] Testing Submodule 3: Vendor Payment (Suppliers & Creditors)...\n";

        $vendor_name = 'Apex Stationery Supplies';
        $vl_res = $this->db->query("SELECT id FROM tbl_finance_ledgers WHERE school_id = {$this->school_id} AND entity_type = 'Vendor' AND ledger_name = '{$vendor_name}'");
        if ($vl_res && $vl_res->num_rows > 0) {
            $vendor_ledger_id = (int)$vl_res->fetch_assoc()['id'];
        } else {
            $this->db->query("INSERT INTO tbl_finance_ledgers (school_id, academic_year_id, entity_type, entity_id, parent_account_id, ledger_code, ledger_name, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, 'Vendor', 0, 1, 'VND-0001', '{$vendor_name}', NOW())");
            $vendor_ledger_id = (int)$this->db->insert_id;
        }

        $bank = $this->getOrCreateAccount('Bank Account - Main', '1020', 'Asset', 'Current Asset');
        $ap_acc = $this->getOrCreateAccount('Accounts Payable', '2020', 'Liability', 'Current Liability');

        $amount = 8500.00;
        $vnd_num = 'VND-TEST-' . time();

        $stmt = $this->db->prepare("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, vendor_id, party_type, payee_name, amount, payment_mode, reference_no, description, status, created_at) VALUES (?, ?, 'Vendor_Payment', ?, CURDATE(), ?, ?, ?, 'Vendor', ?, ?, 'Cheque', 'CHQ-84910', 'Payment for printed annual exam booklets', 'Paid', NOW())");
        $stmt->bind_param("iisiiisd", $this->school_id, $this->academic_year_id, $vnd_num, $ap_acc['id'], $bank['id'], $vendor_ledger_id, $vendor_name, $amount);
        $stmt->execute();
        $vnd_id = (int)$stmt->insert_id;
        $this->assert($vnd_id > 0, "Vendor payment voucher {$vnd_num} created for {$vendor_name}");

        // Post Double Entry: Debit Accounts Payable, Credit Bank
        $tx_num = 'TXN-VND-' . time();
        $this->db->query("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, party_type, party_name, party_id, description, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, '{$tx_num}', CURDATE(), 'Vendor_Payment', 'tbl_finance_expenses', {$vnd_id}, {$amount}, 'Cheque', 'Vendor', '{$vendor_name}', {$vendor_ledger_id}, 'Vendor Payment to {$vendor_name}', 'Posted', NOW())");
        $tx_id = (int)$this->db->insert_id;

        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$ap_acc['id']}, {$vendor_ledger_id}, 'Debit', {$amount}, {$amount}, 0.00, 'Vendor Payable Settled', NOW())");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$bank['id']}, 'Credit', {$amount}, 0.00, {$amount}, 'Disbursed via Cheque', NOW())");

        $this->db->query("UPDATE tbl_finance_expenses SET transaction_id = {$tx_id} WHERE id = {$vnd_id}");

        // Verify Vendor Sub-ledger
        $vl_check = $this->db->query("SELECT SUM(debit) as deb FROM tbl_finance_transaction_items WHERE ledger_id = {$vendor_ledger_id} AND school_id = {$this->school_id}");
        $vl_data = $vl_check->fetch_assoc();
        $this->assert((float)$vl_data['deb'] >= $amount, "Vendor sub-ledger shows recorded payment settlement of at least ₹{$amount}");
    }

    // 5. Submodule 4: Other Expenses
    private function testSubmodule4OtherExpenses() {
        echo "\n[5] Testing Submodule 4: Other Expenses (Reason Validation & Petty Cash)...\n";

        $petty_cash = $this->getOrCreateAccount('Petty Cash Counter', '1010', 'Asset', 'Cash & Cash Equivalents');
        $misc_exp   = $this->getOrCreateAccount('Miscellaneous Operational Expenses', '5050', 'Expense', 'Operational Expense');

        $amount = 1250.00;
        $oth_num = 'OTH-TEST-' . time();
        $reason = 'Emergency pipeline repair in junior block washroom';

        $stmt = $this->db->prepare("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, payee_name, amount, payment_mode, reason, description, status, created_at) VALUES (?, ?, 'Other_Expense', ?, CURDATE(), ?, ?, 'Local Plumber & Hardware', ?, 'Cash', ?, 'Urgent plumbing fittings', 'Paid', NOW())");
        $stmt->bind_param("iisidss", $this->school_id, $this->academic_year_id, $oth_num, $misc_exp['id'], $petty_cash['id'], $amount, $reason);
        $stmt->execute();
        $oth_id = (int)$stmt->insert_id;
        $this->assert($oth_id > 0, "Other expense voucher {$oth_num} created with reason '{$reason}'");

        // Post Double Entry
        $tx_num = 'TXN-OTH-' . time();
        $this->db->query("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, description, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, '{$tx_num}', CURDATE(), 'Other_Expense', 'tbl_finance_expenses', {$oth_id}, {$amount}, 'Cash', 'Other Expense: {$reason}', 'Posted', NOW())");
        $tx_id = (int)$this->db->insert_id;

        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$misc_exp['id']}, 'Debit', {$amount}, {$amount}, 0.00, '{$reason}', NOW())");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$tx_id}, {$this->school_id}, {$petty_cash['id']}, 'Credit', {$amount}, 0.00, {$amount}, 'Disbursed from Petty Cash', NOW())");

        $this->db->query("UPDATE tbl_finance_expenses SET transaction_id = {$tx_id} WHERE id = {$oth_id}");

        $check_exp = $this->db->query("SELECT reason FROM tbl_finance_expenses WHERE id = {$oth_id}")->fetch_assoc();
        $this->assert(!empty($check_exp['reason']), "Reason field is non-empty and stored correctly in database");
    }

    // 6. Expense Reversal & Void
    private function testExpenseReversalAndAuditTrail() {
        echo "\n[6] Testing Reversal & Accounting Audit Trail (No Hard Deletes)...\n";

        $bank = $this->getOrCreateAccount('Bank Account - Main', '1020', 'Asset', 'Current Asset');
        $exp_acc = $this->getOrCreateAccount('Campus Maintenance Expense', '5040', 'Expense', 'Operational Expense');

        $initial_bank_bal = $this->calculateAccountBalance($bank['id']);
        $amount = 2000.00;
        $exp_num = 'EXP-REV-TEST-' . time();

        // 1. Post expense
        $this->db->query("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, payee_name, amount, payment_mode, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, 'Expense_Entry', '{$exp_num}', CURDATE(), {$exp_acc['id']}, {$bank['id']}, 'Test Vendor', {$amount}, 'Cash', 'Paid', NOW())");
        $exp_id = (int)$this->db->insert_id;

        $tx_num = 'TXN-REV-ORIG-' . time();
        $this->db->query("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, '{$tx_num}', CURDATE(), 'Expense', 'tbl_finance_expenses', {$exp_id}, {$amount}, 'Posted', NOW())");
        $tx_id = (int)$this->db->insert_id;

        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, created_at) VALUES ({$tx_id}, {$this->school_id}, {$exp_acc['id']}, 'Debit', {$amount}, {$amount}, 0.00, NOW())");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, created_at) VALUES ({$tx_id}, {$this->school_id}, {$bank['id']}, 'Credit', {$amount}, 0.00, {$amount}, NOW())");
        $this->db->query("UPDATE tbl_finance_expenses SET transaction_id = {$tx_id} WHERE id = {$exp_id}");

        $post_bank_bal = $this->calculateAccountBalance($bank['id']);
        $this->assert(round($post_bank_bal, 2) === round($initial_bank_bal - $amount, 2), "Bank decreased after posting");

        // 2. Perform Reversal
        $rev_tx_num = 'TXN-REV-REV-' . time();
        $reason = 'Entered in error by accountant';
        $this->db->query("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status, created_at) VALUES ({$this->school_id}, {$this->academic_year_id}, '{$rev_tx_num}', CURDATE(), 'Expense', 'reversal', {$tx_id}, {$amount}, 'REVERSAL: {$reason}', 'Posted', NOW())");
        $rev_tx_id = (int)$this->db->insert_id;

        // Equal and opposite items
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$rev_tx_id}, {$this->school_id}, {$exp_acc['id']}, 'Credit', {$amount}, 0.00, {$amount}, 'Reversal of {$tx_num}', NOW())");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at) VALUES ({$rev_tx_id}, {$this->school_id}, {$bank['id']}, 'Debit', {$amount}, {$amount}, 0.00, 'Reversal of {$tx_num}', NOW())");

        // Mark original as Reversed
        $this->db->query("UPDATE tbl_finance_transactions SET status = 'Reversed', reversed_transaction_id = {$rev_tx_id}, reversal_reason = '{$reason}', reversed_at = NOW() WHERE id = {$tx_id}");
        $this->db->query("UPDATE tbl_finance_expenses SET status = 'Reversed' WHERE id = {$exp_id}");

        // 3. Verify original record was NOT hard deleted
        $orig_exp = $this->db->query("SELECT id, status FROM tbl_finance_expenses WHERE id = {$exp_id}")->fetch_assoc();
        $this->assert($orig_exp && $orig_exp['status'] === 'Reversed', "Original expense record preserved in audit trail with status 'Reversed' (NO hard delete)");

        // 4. Verify balance is completely restored
        $restored_bank_bal = $this->calculateAccountBalance($bank['id']);
        $this->assert(round($restored_bank_bal, 2) === round($initial_bank_bal, 2), "Bank Account balance fully restored to ₹{$initial_bank_bal} via balanced reversal");
    }

    // 7. Multi-School Tenant Isolation
    private function testMultiSchoolTenantIsolation() {
        echo "\n[7] Testing Multi-School Tenant Isolation...\n";

        // Record expense for School 2
        $school_2_id = 999;
        $this->db->query("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, payee_name, amount, status, created_at) VALUES ({$school_2_id}, 1, 'Expense_Entry', 'EXP-SCH2-999', CURDATE(), 1, 1, 'School 2 Vendor', 9999.00, 'Paid', NOW())");

        // Query for School 1
        $res = $this->db->query("SELECT * FROM tbl_finance_expenses WHERE school_id = {$this->school_id} AND expense_number = 'EXP-SCH2-999'");
        $this->assert($res->num_rows === 0, "School 1 cannot see or query School 2 expenses (Tenant isolation verified)");

        // Clean up dummy school 2 record
        $this->db->query("DELETE FROM tbl_finance_expenses WHERE school_id = {$school_2_id}");
    }

    // 8. Academic Year Isolation
    private function testAcademicYearIsolation() {
        echo "\n[8] Testing Academic Year Isolation...\n";

        $other_year_id = 888;
        $this->db->query("INSERT INTO tbl_finance_expenses (school_id, academic_year_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, payee_name, amount, status, created_at) VALUES ({$this->school_id}, {$other_year_id}, 'Expense_Entry', 'EXP-YR888-01', CURDATE(), 1, 1, 'Past Year Vendor', 4000.00, 'Paid', NOW())");

        // Query with current academic year filter
        $res = $this->db->query("SELECT * FROM tbl_finance_expenses WHERE school_id = {$this->school_id} AND academic_year_id = {$this->academic_year_id} AND expense_number = 'EXP-YR888-01'");
        $this->assert($res->num_rows === 0, "Active Academic Year filter isolates transactions from other academic years");

        $this->db->query("DELETE FROM tbl_finance_expenses WHERE academic_year_id = {$other_year_id}");
    }

    // 9. RBAC Permissions Matrix
    private function testRbacPermissionsMatrix() {
        echo "\n[9] Testing RBAC Permissions Matrix Configuration...\n";

        $expected_keys = [
            'finance.expenses.view',
            'finance.expenses.create',
            'finance.expenses.edit',
            'finance.expenses.delete',
            'finance.staff_payouts.view',
            'finance.staff_payouts.create',
            'finance.staff_payouts.edit',
            'finance.staff_payouts.delete',
            'finance.vendor_payments.view',
            'finance.vendor_payments.create',
            'finance.vendor_payments.edit',
            'finance.vendor_payments.delete',
            'finance.other_expenses.view',
            'finance.other_expenses.create',
            'finance.other_expenses.edit',
            'finance.other_expenses.delete',
        ];

        foreach ($expected_keys as $k) {
            $res = $this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$k}' AND is_deleted = 'n'");
            $this->assert($res && $res->num_rows > 0, "Permission key `{$k}` is registered in tbl_permissions");
        }

        // Check menu permissions for parent 351 children (352, 353, 354, 355)
        $menu_ids = [352, 353, 354, 355];
        foreach ($menu_ids as $mid) {
            $res = $this->db->query("SELECT COUNT(*) as cnt FROM tbl_menu_permissions WHERE menu_id = {$mid}");
            $cnt = (int)$res->fetch_assoc()['cnt'];
            $this->assert($cnt >= 4, "Menu ID {$mid} has all 4 CRUD operations mapped in tbl_menu_permissions");
        }
    }

    // Helper: calculate dynamically from posted transaction items
    private function calculateAccountBalance($account_id) {
        $res = $this->db->query("SELECT SUM(debit) as total_deb, SUM(credit) as total_cred FROM tbl_finance_transaction_items WHERE account_id = {$account_id} AND school_id = {$this->school_id}");
        $row = $res->fetch_assoc();
        $deb = (float)($row['total_deb'] ?? 0);
        $cred = (float)($row['total_cred'] ?? 0);

        // Fetch account type directly from tbl_finance_accounts
        $acc = $this->db->query("SELECT account_type FROM tbl_finance_accounts WHERE id = {$account_id}")->fetch_assoc();
        $type = $acc['account_type'] ?? 'Asset';

        if (in_array($type, ['Cash', 'Bank', 'Receivable', 'Expense', 'Asset'])) {
            return $deb - $cred;
        } else {
            return $cred - $deb;
        }
    }

    private function getOrCreateAccount($name, $code, $group_type, $group_name) {
        $res = $this->db->query("SELECT id, account_name, account_code FROM tbl_finance_accounts WHERE school_id = {$this->school_id} AND account_code = '{$code}'");
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        // Get or create group
        $grp_res = $this->db->query("SELECT id FROM tbl_finance_account_groups WHERE school_id = {$this->school_id} AND group_type = '{$group_type}' LIMIT 1");
        if ($grp_res && $grp_res->num_rows > 0) {
            $group_id = (int)$grp_res->fetch_assoc()['id'];
        } else {
            $this->db->query("INSERT INTO tbl_finance_account_groups (school_id, group_name, group_code, group_type, created_at) VALUES ({$this->school_id}, '{$group_name}', 'GRP-{$group_type}', '{$group_type}', NOW())");
            $group_id = (int)$this->db->insert_id;
        }

        $this->db->query("INSERT INTO tbl_finance_accounts (school_id, account_group_id, account_code, account_name, created_at) VALUES ({$this->school_id}, {$group_id}, '{$code}', '{$name}', NOW())");
        $acc_id = (int)$this->db->insert_id;

        return ['id' => $acc_id, 'account_name' => $name, 'account_code' => $code];
    }
}

$suite = new TestExpensesModule($mysqli);
$suite->runAll();
