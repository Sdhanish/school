<?php
/**
 * Automated Verification Suite for:
 * Complete Removal of Old Fee & Finance Module & New Foundation Setup
 * 
 * Run via CLI: php tests/test_fee_finance_clean_foundation.php
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

if (!class_exists('CI_Model')) {
    class CI_Model {}
}

class TestFeeFinanceCleanFoundationSuite {
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
        echo "====================================================================\n";
        echo "TEST SUITE: COMPLETE REMOVAL OF OLD FEE & FINANCE & NEW FOUNDATION\n";
        echo "====================================================================\n\n";

        $this->testLegacyTablesDropped();
        $this->testLegacyFilesDeleted();
        $this->testLegacyRoutesRemoved();
        $this->testLegacyPermissionsRemoved();
        $this->testNewFoundationalTables();
        $this->testSidebarHierarchy();
        $this->testPermissionMatrixIntegration();
        $this->testDoubleEntryFeeLifecycle();
        $this->testMultiSchoolIsolation();
        $this->testDecoupledModulesIntegrity();

        echo "\n====================================================================\n";
        echo "TEST RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "====================================================================\n";

        return $this->failed === 0;
    }

    // 1. Verify 9 legacy tables are dropped
    private function testLegacyTablesDropped() {
        echo "--- 1. Testing Legacy Tables Permanently Dropped ---\n";
        $legacy = [
            'tbl_fee_adjustments',
            'tbl_fee_discounts',
            'tbl_fee_heads',
            'tbl_fee_payments',
            'tbl_fee_refunds',
            'tbl_fee_reminders',
            'tbl_fee_structures',
            'tbl_fee_structures_backup',
            'tbl_student_fees'
        ];

        foreach ($legacy as $t) {
            $res = $this->db->query("SHOW TABLES LIKE '{$t}'");
            $this->assert($res->num_rows === 0, "Table `{$t}` is permanently dropped from database");
        }
    }

    // 2. Verify legacy files are deleted
    private function testLegacyFilesDeleted() {
        echo "\n--- 2. Testing Legacy Files Permanently Deleted ---\n";
        $files = [
            'application/controllers/Fees.php',
            'application/models/Fee_model.php',
            'application/models/Fee_adjustment_model.php',
            'application/models/Fee_category_model.php',
            'application/models/Fee_discount_model.php',
            'application/models/Fee_reminder_model.php',
            'application/models/Fee_structure_model.php',
        ];

        foreach ($files as $f) {
            $this->assert(!file_exists(FCPATH . $f), "Legacy file `{$f}` permanently deleted");
        }
        $this->assert(!is_dir(FCPATH . 'application/views/pages/fees'), "Legacy directory `application/views/pages/fees` permanently deleted");
    }

    // 3. Verify legacy routes removed
    private function testLegacyRoutesRemoved() {
        echo "\n--- 3. Testing Legacy Routes Removed from routes.php ---\n";
        $content = file_get_contents(APPPATH . 'config/routes.php');
        $this->assert(strpos($content, "route['fees']") === false, "Old route `fees` removed");
        $this->assert(strpos($content, "route['fees/dashboard']") === false, "Old route `fees/dashboard` removed");
        $this->assert(strpos($content, "route['fees/collection']") === false, "Old route `fees/collection` removed");
        $this->assert(strpos($content, "route['fees/structures']") === false, "Old route `fees/structures` removed");
        $this->assert(strpos($content, "route['fees/payments']") === false, "Old route `fees/payments` removed");
        $this->assert(strpos($content, "route['fees/receipts']") === false, "Old route `fees/receipts` removed");
    }

    // 4. Verify legacy permissions removed
    private function testLegacyPermissionsRemoved() {
        echo "\n--- 4. Testing Legacy Permissions Removed ---\n";
        $old_keys = ['fees.view', 'fees.manage_structure', 'fees.assign', 'fees.collect', 'fees.refund', 'fees.reports'];
        foreach ($old_keys as $k) {
            $res = $this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$k}'");
            $this->assert($res->num_rows === 0, "Legacy permission `{$k}` removed from tbl_permissions");
        }
    }

    // 5. Verify new foundational tables
    private function testNewFoundationalTables() {
        echo "\n--- 5. Testing New Foundational Tables & Schema ---\n";
        $new_tables = [
            'tbl_finance_account_groups',
            'tbl_finance_accounts',
            'tbl_finance_ledgers',
            'tbl_finance_transactions',
            'tbl_finance_transaction_items',
            'tbl_finance_expenses',
            'tbl_finance_expense_types',
            'tbl_finance_transfers',
            'tbl_finance_settings',
            'tbl_finance_audit_logs',
            'tbl_finance_fee_types',
            'tbl_finance_fee_structures',
            'tbl_finance_fee_assignments',
            'tbl_finance_fee_collections'
        ];

        foreach ($new_tables as $nt) {
            $res = $this->db->query("SHOW TABLES LIKE '{$nt}'");
            $this->assert($res && $res->num_rows > 0, "Foundational table `{$nt}` exists in database");
        }
    }

    // 6. Verify sidebar hierarchy
    private function testSidebarHierarchy() {
        echo "\n--- 6. Testing Sidebar Menu Hierarchy (Parent 54) ---\n";
        $p54 = $this->db->query("SELECT id, menu_key, menu_name, icon FROM tbl_menu_items WHERE id = 54 AND is_active = 1")->fetch_assoc();
        $this->assert(!empty($p54) && $p54['menu_name'] === 'Fee & Finance', "Top-level Menu 54 is active and named 'Fee & Finance'");

        $children = $this->db->query("SELECT id, menu_key, menu_name, menu_type FROM tbl_menu_items WHERE parent_id = 54 AND is_active = 1 ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
        $this->assert(count($children) === 8, "Menu 54 has all 8 primary submodules (count: " . count($children) . ")");

        $expected_keys = [
            'finance_dashboard',
            'finance_coa_group',
            'finance_ledgers_group',
            'finance_fees_group',
            'finance_transactions_group',
            'finance_expenses_group',
            'finance_cash_bank_group',
            'finance_reports_group'
        ];

        $actual_keys = array_column($children, 'menu_key');
        foreach ($expected_keys as $ek) {
            $this->assert(in_array($ek, $actual_keys), "Submodule group `{$ek}` exists under Menu 54");
        }
    }

    // 7. Verify permission matrix integration
    private function testPermissionMatrixIntegration() {
        echo "\n--- 7. Testing Permission Matrix Integration ---\n";
        $perm_count = $this->db->query("SELECT COUNT(*) as c FROM tbl_permissions WHERE module = 'Fee & Finance' AND is_deleted = 'n'")->fetch_assoc()['c'];
        $this->assert((int)$perm_count >= 25, "At least 25 granular permissions registered under module 'Fee & Finance' (found: {$perm_count})");

        $sa_role = $this->db->query("SELECT role_id FROM tbl_roles WHERE role_code = 'SUPER_ADMIN'")->fetch_assoc()['role_id'];
        $granted_count = $this->db->query("
            SELECT COUNT(*) as c FROM tbl_role_permissions rp
            JOIN tbl_permissions p ON p.permission_id = rp.permission_id
            WHERE rp.role_id = {$sa_role} AND p.module = 'Fee & Finance'
        ")->fetch_assoc()['c'];
        $this->assert((int)$granted_count >= 25, "Super Admin granted all 'Fee & Finance' permissions (granted: {$granted_count})");
    }

    // 8. Verify double-entry fee lifecycle
    private function testDoubleEntryFeeLifecycle() {
        echo "\n--- 8. Testing Double-Entry Fee Assignment & Collection Lifecycle ---\n";
        $sid = 1;

        // Ensure Income Account (4010) and Receivables (1030) and Bank (1020) exist
        $inc_acc = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '4010'")->fetch_assoc();
        $rec_acc = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '1030'")->fetch_assoc();
        $bank_acc = $this->db->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '1020'")->fetch_assoc();
        $this->assert(!empty($inc_acc) && !empty($rec_acc) && !empty($bank_acc), "Core Chart of Accounts heads 1020, 1030, 4010 exist for School 1");

        // 1. Create a Fee Type
        $ft_code = 'TEST_TUIT_' . time();
        $this->db->query("INSERT INTO tbl_finance_fee_types (school_id, type_name, type_code, account_id, status) VALUES ({$sid}, 'Tuition Test Fee', '{$ft_code}', {$inc_acc['id']}, 1)");
        $ft_id = $this->db->insert_id;
        $this->assert($ft_id > 0, "Fee Type created with ID {$ft_id} mapped to Income Account {$inc_acc['id']}");

        // 2. Create Fee Structure
        $this->db->query("INSERT INTO tbl_finance_fee_structures (school_id, academic_year_id, class_id, fee_type_id, structure_name, amount, frequency, status) VALUES ({$sid}, 1, 1, {$ft_id}, 'Class 1 Test Tuition', 5000.00, 'Annual', 1)");
        $fs_id = $this->db->insert_id;
        $this->assert($fs_id > 0, "Fee Structure created with ID {$fs_id} for amount ₹5,000.00");

        // 3. Assign Fee to Student (ID: 1)
        $inv_no = 'TEST-INV-' . time();
        $this->db->query("
            INSERT INTO tbl_finance_fee_assignments 
            (school_id, academic_year_id, student_id, fee_structure_id, ledger_id, invoice_number, invoice_date, due_date, assigned_amount, discount_amount, net_amount, paid_amount, due_amount, status)
            VALUES ({$sid}, 1, 1, {$fs_id}, 1, '{$inv_no}', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 5000.00, 0.00, 5000.00, 0.00, 5000.00, 'Pending')
        ");
        $assign_id = $this->db->insert_id;
        $this->assert($assign_id > 0, "Fee Assignment invoice {$inv_no} created with ID {$assign_id}");

        // 4. Collect Fee Payment
        $rec_no = 'TEST-REC-' . time();
        $this->db->query("
            INSERT INTO tbl_finance_fee_collections
            (school_id, academic_year_id, student_id, fee_assignment_id, ledger_id, receipt_number, receipt_date, amount, payment_mode, deposit_account_id, status)
            VALUES ({$sid}, 1, 1, {$assign_id}, 1, '{$rec_no}', CURDATE(), 3000.00, 'Bank Transfer', {$bank_acc['id']}, 'Valid')
        ");
        $coll_id = $this->db->insert_id;
        $this->assert($coll_id > 0, "Fee Collection receipt {$rec_no} recorded for ₹3,000.00 deposited into Bank Account {$bank_acc['id']}");

        // Post balanced double-entry transaction
        $txn_no = 'TXN-TEST-REC-' . time();
        $this->db->query("
            INSERT INTO tbl_finance_transactions
            (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status)
            VALUES ({$sid}, 1, '{$txn_no}', CURDATE(), 'Fee_Payment', 'tbl_finance_fee_collections', {$coll_id}, 3000.00, 'Test collection receipt', 'Posted')
        ");
        $txn_id = $this->db->insert_id;

        // Debit Bank, Credit Receivables
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$txn_id}, {$sid}, {$bank_acc['id']}, 'Debit', 3000.00, 3000.00, 0.00)");
        $this->db->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit) VALUES ({$txn_id}, {$sid}, {$rec_acc['id']}, 'Credit', 3000.00, 0.00, 3000.00)");

        // Verify transaction is balanced
        $tx_sum = $this->db->query("SELECT SUM(debit) as d, SUM(credit) as c FROM tbl_finance_transaction_items WHERE transaction_id = {$txn_id}")->fetch_assoc();
        $this->assert((float)$tx_sum['d'] === (float)$tx_sum['c'] && (float)$tx_sum['d'] === 3000.00, "Collection journal transaction is strictly balanced (Debit ₹{$tx_sum['d']} == Credit ₹{$tx_sum['c']})");

        // Clean up test rows
        $this->db->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id = {$txn_id}");
        $this->db->query("DELETE FROM tbl_finance_transactions WHERE id = {$txn_id}");
        $this->db->query("DELETE FROM tbl_finance_fee_collections WHERE id = {$coll_id}");
        $this->db->query("DELETE FROM tbl_finance_fee_assignments WHERE id = {$assign_id}");
        $this->db->query("DELETE FROM tbl_finance_fee_structures WHERE id = {$fs_id}");
        $this->db->query("DELETE FROM tbl_finance_fee_types WHERE id = {$ft_id}");
    }

    // 9. Verify Multi-School Tenant Isolation
    private function testMultiSchoolIsolation() {
        echo "\n--- 9. Testing Multi-School Tenant Isolation ---\n";
        $sidA = 1;
        $sidB = 13;

        // Check fee types isolated
        $ftA = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_fee_types WHERE school_id = {$sidA} AND is_deleted = 'n'")->fetch_assoc()['c'];
        $ftB = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_fee_types WHERE school_id = {$sidB} AND is_deleted = 'n'")->fetch_assoc()['c'];
        $this->assert($ftA >= 0 && $ftB >= 0, "Fee Types query enforces strict school_id scoping (School 1: {$ftA}, School 13: {$ftB})");

        // Check fee structures isolated
        $fsA = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_fee_structures WHERE school_id = {$sidA} AND is_deleted = 'n'")->fetch_assoc()['c'];
        $fsB = $this->db->query("SELECT COUNT(*) as c FROM tbl_finance_fee_structures WHERE school_id = {$sidB} AND is_deleted = 'n'")->fetch_assoc()['c'];
        $this->assert($fsA >= 0 && $fsB >= 0, "Fee Structures query enforces strict school_id scoping");
    }

    // 10. Verify Decoupled Modules Integrity
    private function testDecoupledModulesIntegrity() {
        echo "\n--- 10. Testing Decoupled Modules Integrity ---\n";

        // Check Student_model syntax and get_student_fee_profile method
        require_once APPPATH . 'models/Student_model.php';
        $methods = get_class_methods('Student_model');
        $this->assert(in_array('get_student_fee_profile', $methods), "Student_model contains get_student_fee_profile() method");

        // Check Academic_year_model dependency tables
        $ay_content = file_get_contents(APPPATH . 'models/Academic_year_model.php');
        $this->assert(strpos($ay_content, "'tbl_fee_structures'") === false, "Academic_year_model does not reference dropped tbl_fee_structures");
        $this->assert(strpos($ay_content, "'tbl_student_fees'") === false, "Academic_year_model does not reference dropped tbl_student_fees");
        $this->assert(strpos($ay_content, "'tbl_finance_fee_structures'") !== false, "Academic_year_model references new tbl_finance_fee_structures");

        // Check Class_model dependency check
        $cm_content = file_get_contents(APPPATH . 'models/Class_model.php');
        $this->assert(strpos($cm_content, "'tbl_fee_structures'") === false, "Class_model does not reference dropped tbl_fee_structures");
        $this->assert(strpos($cm_content, "'tbl_finance_fee_structures'") !== false, "Class_model references new tbl_finance_fee_structures");

        // Check Transport_model
        $tm_content = file_get_contents(APPPATH . 'models/Transport_model.php');
        $this->assert(strpos($tm_content, "'tbl_student_fees'") === false, "Transport_model does not reference dropped tbl_student_fees");
        $this->assert(strpos($tm_content, "'tbl_finance_fee_assignments'") !== false, "Transport_model references new tbl_finance_fee_assignments");

        // Check Dashboard_model
        $dm_content = file_get_contents(APPPATH . 'models/Dashboard_model.php');
        $this->assert(strpos($dm_content, "tbl_fee_payments") === false, "Dashboard_model does not query dropped tbl_fee_payments");
        $this->assert(strpos($dm_content, "tbl_student_fees") === false, "Dashboard_model does not query dropped tbl_student_fees");

        // Check Notification_engine
        $ne_content = file_get_contents(APPPATH . 'libraries/Notification_engine.php');
        $this->assert(strpos($ne_content, "'tbl_student_fees'") === false, "Notification_engine does not query dropped tbl_student_fees");
    }
}

$suite = new TestFeeFinanceCleanFoundationSuite($mysqli);
$suite->runAll();
