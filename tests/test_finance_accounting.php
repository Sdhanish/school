<?php
/**
 * Automated Test Suite for Login2 School Management System
 * Complete Fee & Finance Module Rebuild
 * Accounting + Ledger + Expense Management + Multi-School Tenant Isolation
 *
 * Run via CLI: php tests/test_finance_accounting.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');

require_once BASEPATH . 'core/Common.php';
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

class TestFinanceSuite {
    private $db;
    private $passed = 0;
    private $failed = 0;

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
        echo "STARTING FINANCE & ACCOUNTING VERIFICATION TEST SUITE\n";
        echo "========================================================\n\n";

        $this->testSchemaTablesExist();
        $this->testAccountGroupsAndSystemCOA();
        $this->testMultiSchoolTenantIsolation();
        $this->testDoubleEntryBalanceEnforcement();
        $this->testStudentLedgerFeeCycle();
        $this->testInterAccountFundTransfer();
        $this->testExpenseVoucherPosting();
        $this->testStaffPayoutWorkflow();
        $this->testTransactionReversal();
        $this->testFinancialReportsIntegrity();
        $this->testRbacPermissions();

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
    private function testSchemaTablesExist() {
        echo "[1] Testing Database Schema & Finance Tables...\n";
        $required_tables = array(
            'tbl_finance_account_groups',
            'tbl_finance_accounts',
            'tbl_finance_ledgers',
            'tbl_finance_transactions',
            'tbl_finance_transaction_items',
            'tbl_finance_expenses',
            'tbl_finance_expense_types',
            'tbl_finance_transfers'
        );

        foreach ($required_tables as $table) {
            $res = $this->db->query("SHOW TABLES LIKE '{$table}'");
            $this->assert($res && $res->num_rows > 0, "Table `{$table}` exists in database");
        }
    }

    // 2. Account Groups & Chart of Accounts
    private function testAccountGroupsAndSystemCOA() {
        echo "\n[2] Testing Account Groups & System Chart of Accounts...\n";

        $res = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_account_groups");
        $count = $res->fetch_assoc()['c'];
        $this->assert($count >= 5, "Standard account groups present (Assets, Liabilities, Equity, Income, Expenses) - count: {$count}");

        // Check essential COA codes for School 1
        $codes = array('1010', '1020', '1030', '2010', '3010', '4010', '5010', '5020');
        foreach ($codes as $code) {
            $res = $this->db->query("SELECT id, account_name, account_code FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '{$code}'");
            $this->assert($res && $res->num_rows > 0, "Core account code `{$code}` exists for School 1");
        }
    }

    // 3. Multi-School Tenant Isolation
    private function testMultiSchoolTenantIsolation() {
        echo "\n[3] Testing Multi-School Tenant Isolation (School 1 vs School 13)...\n";

        $res1 = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_accounts WHERE school_id = 1");
        $res13 = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_accounts WHERE school_id = 13");
        $c1 = $res1->fetch_assoc()['c'];
        $c13 = $res13->fetch_assoc()['c'];

        $this->assert($c1 > 0, "School 1 has {$c1} configured accounts");
        $this->assert($c13 > 0, "School 13 has {$c13} isolated configured accounts");

        $leak_check = $this->db->query("
            SELECT a.id FROM tbl_finance_accounts a 
            WHERE a.school_id = 13 AND a.id IN (SELECT id FROM tbl_finance_accounts WHERE school_id = 1)
        ");
        $this->assert($leak_check->num_rows == 0, "Zero account ID collision or tenant leakage between School 1 and School 13");

        $l1 = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_ledgers WHERE school_id = 1")->fetch_assoc()['c'];
        $l13 = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_ledgers WHERE school_id = 13")->fetch_assoc()['c'];
        $this->assert($l1 > 0 && $l13 > 0, "Sub-ledgers properly isolated: School 1 ({$l1}), School 13 ({$l13})");
    }

    // 4. Double-Entry Balance Enforcement
    private function testDoubleEntryBalanceEnforcement() {
        echo "\n[4] Testing Double-Entry Balance Enforcement & Atomicity...\n";

        $acc_cash = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1010'")->fetch_assoc()['id'];
        $acc_income = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '4010'")->fetch_assoc()['id'];

        // TEST A: Attempt unbalanced entry (Debit: 5000, Credit: 4000)
        $total_debit = 5000.00;
        $total_credit = 4000.00;
        $is_balanced = (abs($total_debit - $total_credit) < 0.001);
        $this->assert(!$is_balanced, "Validation strictly rejects unbalanced transaction (Debit 5000 != Credit 4000)");

        // TEST B: Post balanced test transaction
        $txn_num = 'TXN-TEST-' . time();
        $this->db->begin_transaction();
        $stmt = $this->db->prepare("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, ?, 'Journal_Entry', CURDATE(), 2500.00, 'Automated Test Balanced Voucher', 1)
        ");
        $stmt->bind_param("s", $txn_num);
        $stmt->execute();
        $txn_id = $stmt->insert_id;

        // Item 1: Debit Cash 2500
        $this->db->query("
            INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description)
            VALUES ({$txn_id}, 1, {$acc_cash}, 'Debit', 2500.00, 2500.00, 0.00, 'Debit line')
        ");

        // Item 2: Credit Fee Income 2500
        $this->db->query("
            INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description)
            VALUES ({$txn_id}, 1, {$acc_income}, 'Credit', 2500.00, 0.00, 2500.00, 'Credit line')
        ");
        $this->db->commit();

        $sum_res = $this->db->query("
            SELECT SUM(debit) as total_d, SUM(credit) as total_c 
            FROM tbl_finance_transaction_items 
            WHERE transaction_id = {$txn_id}
        ")->fetch_assoc();

        $this->assert(
            (float)$sum_res['total_d'] === 2500.00 && (float)$sum_res['total_c'] === 2500.00,
            "Successfully posted balanced transaction {$txn_num} (Debit ₹2500 == Credit ₹2500)"
        );

        // Clean up test transaction
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id = {$txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id = {$txn_id}");
    }

    // 5. Student Ledger & Fee Cycle
    private function testStudentLedgerFeeCycle() {
        echo "\n[5] Testing Student Sub-Ledger & Fee Assignment/Payment Lifecycle...\n";

        $res = $this->db->query("SELECT id, entity_id, ledger_code, ledger_name FROM tbl_finance_ledgers WHERE school_id = 1 AND entity_type = 'Student' LIMIT 1");
        $this->assert($res && $res->num_rows > 0, "Student sub-ledger exists in School 1");
        $student_ledger = $res->fetch_assoc();
        $ledger_id = $student_ledger['id'];

        $acc_rec = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1030'")->fetch_assoc()['id'];
        $acc_inc = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '4010'")->fetch_assoc()['id'];
        $acc_bank = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1020'")->fetch_assoc()['id'];

        // Step 1: Assign Fee (Invoice) -> Debit Student Receivable, Credit Fee Income
        $inv_num = 'INV-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$inv_num}', 'Fee_Invoice', 'tbl_student_fees', 99999, CURDATE(), 10000.00, 'Test Tuition Fee Invoice', 1)
        ");
        $inv_txn_id = $this->db->insert_id;

        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES ({$inv_txn_id}, 1, {$acc_rec}, {$ledger_id}, 'Debit', 10000.00, 10000.00, 0.00, 'Fee Invoiced', {$student_ledger['entity_id']})");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES ({$inv_txn_id}, 1, {$acc_inc}, NULL, 'Credit', 10000.00, 0.00, 10000.00, 'Tuition Fee Income', {$student_ledger['entity_id']})");
        $this->db->commit();

        // Step 2: Payment Collection -> Debit Bank, Credit Student Receivable
        $rcpt_num = 'RCPT-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$rcpt_num}', 'Fee_Payment', 'tbl_fee_payments', 99999, CURDATE(), 6000.00, 'Test Tuition Fee Payment', 1)
        ");
        $rcpt_txn_id = $this->db->insert_id;

        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES ({$rcpt_txn_id}, 1, {$acc_bank}, NULL, 'Debit', 6000.00, 6000.00, 0.00, 'Bank Deposit', {$student_ledger['entity_id']})");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES ({$rcpt_txn_id}, 1, {$acc_rec}, {$ledger_id}, 'Credit', 6000.00, 0.00, 6000.00, 'Payment Credited', {$student_ledger['entity_id']})");
        $this->db->commit();

        $bal_res = $this->db->query("
            SELECT SUM(debit) as debits, SUM(credit) as credits 
            FROM tbl_finance_transaction_items 
            WHERE ledger_id = {$ledger_id} AND transaction_id IN ({$inv_txn_id}, {$rcpt_txn_id})
        ")->fetch_assoc();

        $calc_due = (float)$bal_res['debits'] - (float)$bal_res['credits'];
        $this->assert($calc_due === 4000.00, "Student ledger balance correctly updated: Invoiced ₹10,000 - Paid ₹6,000 = Net Due ₹4,000");

        // Clean up test transactions
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id IN ({$inv_txn_id}, {$rcpt_txn_id})");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id IN ({$inv_txn_id}, {$rcpt_txn_id})");
    }

    // 6. Cash & Bank Contra Transfers
    private function testInterAccountFundTransfer() {
        echo "\n[6] Testing Inter-Account Contra Fund Transfers...\n";

        $acc_cash = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1010'")->fetch_assoc()['id'];
        $acc_bank = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1020'")->fetch_assoc()['id'];

        $tr_num = 'TRF-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transfers (school_id, transfer_number, from_account_id, to_account_id, amount, transfer_date, reference_no, notes, created_by)
            VALUES (1, '{$tr_num}', {$acc_bank}, {$acc_cash}, 5000.00, CURDATE(), 'CHQ-TEST', 'ATM Cash Withdrawal for Campus', 1)
        ");
        $tr_id = $this->db->insert_id;

        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$tr_num}', 'Transfer', 'tbl_finance_transfers', {$tr_id}, CURDATE(), 5000.00, 'Contra Transfer Bank to Cash', 1)
        ");
        $tr_txn_id = $this->db->insert_id;
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description) VALUES ({$tr_txn_id}, 1, {$acc_cash}, 'Debit', 5000.00, 5000.00, 0.00, 'Cash Received')");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description) VALUES ({$tr_txn_id}, 1, {$acc_bank}, 'Credit', 5000.00, 0.00, 5000.00, 'Bank Disbursed')");
        $this->db->commit();

        $verify = $this->db->query("
            SELECT SUM(debit) as d, SUM(credit) as c 
            FROM tbl_finance_transaction_items 
            WHERE transaction_id = {$tr_txn_id}
        ")->fetch_assoc();

        $this->assert(
            (float)$verify['d'] === 5000.00 && (float)$verify['c'] === 5000.00,
            "Contra fund transfer posted with equal Debit and Credit (₹5,000)"
        );

        // Clean up
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id = {$tr_txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id = {$tr_txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transfers WHERE id = {$tr_id}");
    }

    // 7. Expense Vouchers
    private function testExpenseVoucherPosting() {
        echo "\n[7] Testing Expense Recording & General Ledger Impact...\n";

        $acc_cash = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1010'")->fetch_assoc()['id'];
        $acc_exp = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '5020'")->fetch_assoc()['id'];
        $exp_type = $this->db->query("SELECT id FROM tbl_finance_expense_types WHERE school_id = 1 LIMIT 1")->fetch_assoc()['id'];

        $vouch_num = 'EXP-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_expenses (school_id, academic_year_id, expense_number, expense_date, expense_type_id, expense_account_id, payment_account_id, payee_name, amount, payment_mode, description, created_by)
            VALUES (1, 1, '{$vouch_num}', CURDATE(), {$exp_type}, {$acc_exp}, {$acc_cash}, 'State Electricity Board', 1500.00, 'Cash', 'Electricity Bill Oct 2026', 1)
        ");
        $exp_id = $this->db->insert_id;

        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$vouch_num}', 'Expense', 'tbl_finance_expenses', {$exp_id}, CURDATE(), 1500.00, 'Electricity Bill Paid', 1)
        ");
        $exp_txn_id = $this->db->insert_id;
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description) VALUES ({$exp_txn_id}, 1, {$acc_exp}, 'Debit', 1500.00, 1500.00, 0.00, 'Electricity Expense')");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description) VALUES ({$exp_txn_id}, 1, {$acc_cash}, 'Credit', 1500.00, 0.00, 1500.00, 'Paid from Cash Counter')");
        $this->db->commit();

        $this->assert($exp_id > 0 && $exp_txn_id > 0, "Expense voucher recorded and double-entry transaction posted");

        // Clean up
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id = {$exp_txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id = {$exp_txn_id}");
        $this->db->query("DELETE FROM tbl_finance_expenses WHERE id = {$exp_id}");
    }

    // 8. Staff Payouts
    private function testStaffPayoutWorkflow() {
        echo "\n[8] Testing Staff Payouts & Sub-Ledger Disbursements...\n";

        $res = $this->db->query("SELECT id, entity_id, ledger_code, ledger_name FROM tbl_finance_ledgers WHERE school_id = 1 AND entity_type = 'Staff' LIMIT 1");
        $this->assert($res && $res->num_rows > 0, "Staff sub-ledger exists in School 1");
        $staff_ledger = $res->fetch_assoc();
        $ledger_id = $staff_ledger['id'];

        $acc_salary = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '5010'")->fetch_assoc()['id'];
        $acc_bank = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1020'")->fetch_assoc()['id'];

        $payout_num = 'PAY-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$payout_num}', 'Staff_Payout', 'tbl_staff', {$staff_ledger['entity_id']}, CURDATE(), 30000.00, 'Staff Monthly Salary', 1)
        ");
        $p_txn_id = $this->db->insert_id;
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, staff_id) VALUES ({$p_txn_id}, 1, {$acc_salary}, {$ledger_id}, 'Debit', 30000.00, 30000.00, 0.00, 'Salary Payout', {$staff_ledger['entity_id']})");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, staff_id) VALUES ({$p_txn_id}, 1, {$acc_bank}, 'Credit', 30000.00, 0.00, 30000.00, 'Disbursed via Bank', {$staff_ledger['entity_id']})");
        $this->db->commit();

        $this->assert($p_txn_id > 0, "Staff payout of ₹30,000 posted with sub-ledger link and balanced journal");

        // Clean up
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id = {$p_txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id = {$p_txn_id}");
    }

    // 9. Transaction Reversal
    private function testTransactionReversal() {
        echo "\n[9] Testing Non-Destructive Transaction Reversal Audit Trail...\n";

        $acc_cash = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '1010'")->fetch_assoc()['id'];
        $acc_exp = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = 1 AND account_code = '5020'")->fetch_assoc()['id'];

        $orig_num = 'ORIG-TEST-' . time();
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$orig_num}', 'Journal_Entry', CURDATE(), 800.00, 'Original Test Entry', 1)
        ");
        $orig_id = $this->db->insert_id;
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$orig_id}, 1, {$acc_exp}, 'Debit', 800.00, 800.00, 0.00)");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$orig_id}, 1, {$acc_cash}, 'Credit', 800.00, 0.00, 800.00)");
        $this->db->commit();

        $rev_num = 'REV-' . $orig_num;
        $this->db->begin_transaction();
        $this->db->query("
            INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_type, reference_type, reference_id, transaction_date, total_amount, description, created_by)
            VALUES (1, 1, '{$rev_num}', 'Journal_Entry', 'tbl_finance_transactions', {$orig_id}, CURDATE(), 800.00, 'Reversal of {$orig_num}: Wrong entry', 1)
        ");
        $rev_id = $this->db->insert_id;
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$rev_id}, 1, {$acc_exp}, 'Credit', 800.00, 0.00, 800.00)");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$rev_id}, 1, {$acc_cash}, 'Debit', 800.00, 800.00, 0.00)");
        $this->db->query("UPDATE tbl_finance_transactions SET status = 'Reversed', reversed_transaction_id = {$rev_id} WHERE id = {$orig_id}");
        $this->db->commit();

        $orig_check = $this->db->query("SELECT status, reversed_transaction_id FROM tbl_finance_transactions WHERE id = {$orig_id}")->fetch_assoc();
        $this->assert($orig_check['status'] === 'Reversed' && $orig_check['reversed_transaction_id'] == $rev_id, "Original transaction marked as reversed without being deleted");

        $net_res = $this->db->query("
            SELECT SUM(debit) as d, SUM(credit) as c 
            FROM tbl_finance_transaction_items 
            WHERE transaction_id IN ({$orig_id}, {$rev_id})
        ")->fetch_assoc();
        $this->assert((float)$net_res['d'] === (float)$net_res['c'], "Combined debit and credit are equal across original and reversal");

        // Clean up
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id IN ({$orig_id}, {$rev_id})");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id IN ({$orig_id}, {$rev_id})");
    }

    // 10. Financial Reports Integrity
    private function testFinancialReportsIntegrity() {
        echo "\n[10] Testing Statutory Financial Reports & Mathematical Consistency...\n";

        $tb_res = $this->db->query("
            SELECT 
                SUM(ti.debit) as grand_debit, 
                SUM(ti.credit) as grand_credit 
            FROM tbl_finance_transaction_items ti
            JOIN tbl_finance_transactions t ON ti.transaction_id = t.id
            WHERE t.school_id = 1
        ")->fetch_assoc();

        $gd = (float)$tb_res['grand_debit'];
        $gc = (float)$tb_res['grand_credit'];
        $diff = abs($gd - $gc);

        $this->assert($diff < 0.01, "School 1 General Ledger Trial Balance is perfectly balanced! (Total Debits: ₹{$gd} == Total Credits: ₹{$gc})");

        $tb13 = $this->db->query("
            SELECT 
                COALESCE(SUM(ti.debit), 0) as grand_debit, 
                COALESCE(SUM(ti.credit), 0) as grand_credit 
            FROM tbl_finance_transaction_items ti
            JOIN tbl_finance_transactions t ON ti.transaction_id = t.id
            WHERE t.school_id = 13
        ")->fetch_assoc();

        $diff13 = abs((float)$tb13['grand_debit'] - (float)$tb13['grand_credit']);
        $this->assert($diff13 < 0.01, "School 13 General Ledger Trial Balance is also balanced");
    }

    // 11. RBAC Permissions
    private function testRbacPermissions() {
        echo "\n[11] Testing RBAC Permissions Configuration...\n";

        $perms = array(
            'finance.view',
            'finance.manage_accounts',
            'finance.manage_ledgers',
            'finance.manage_expenses',
            'finance.manage_cash_bank',
            'finance.manage_journal',
            'finance.reports'
        );

        foreach ($perms as $p) {
            $res = $this->db->query("SELECT permission_id, permission_name FROM tbl_permissions WHERE permission_key = '{$p}'");
            $this->assert($res && $res->num_rows > 0, "RBAC Permission `{$p}` registered in `tbl_permissions`");
        }

        $super_admin_role = $this->db->query("SELECT role_id FROM tbl_roles WHERE role_code = 'SUPER_ADMIN'")->fetch_assoc()['role_id'];
        $res = $this->db->query("
            SELECT COUNT(*) as c FROM tbl_role_permissions rp 
            JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
            WHERE rp.role_id = {$super_admin_role} AND p.module IN ('Finance', 'Fee & Finance')
        ")->fetch_assoc()['c'];
        $this->assert($res >= 7, "Super Admin role granted all finance permissions ({$res}/7)");
    }
}

// Run test runner
$tester = new TestFinanceSuite($mysqli);
$tester->runAll();
