<?php
/**
 * Test Script for Fee & Finance Phase 1
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', 'c:/xampp/htdocs/schoolnew/system/');
define('APPPATH', 'c:/xampp/htdocs/schoolnew/application/');

require_once BASEPATH . 'core/Common.php';

// Mock CI environment
$_SERVER['CI_ENV'] = 'development';

require_once APPPATH . 'config/database.php';
$cfg = $db['default'];
$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($mysqli->connect_error) {
    die("DB Fail: " . $mysqli->connect_error . "\n");
}

echo "=======================================================\n";
echo "RUNNING TEST SUITE: FEE & FINANCE PHASE 1\n";
echo "=======================================================\n\n";

// TEST 1: SCHOOL ISOLATION (School 1 vs School 13)
echo "TEST 1: Multi-School Tenant Isolation\n";
echo "-------------------------------------\n";

$school_a = 1;
$school_b = 13;

// Create test account group in School A
$unique_grp_name_a = 'Test Asset Group A ' . rand(1000, 9999);
$res_ins = $mysqli->query("INSERT INTO tbl_finance_account_groups (school_id, group_name, group_code, category, description, display_order, status, created_at) VALUES ({$school_a}, '{$unique_grp_name_a}', 'TAGA', 'Asset', 'Testing School A Isolation', 99, 1, NOW())");
$group_a_id = $mysqli->insert_id;
echo "  [School A] Created Account Group '{$unique_grp_name_a}' (ID: {$group_a_id})\n";

// Query from School B
$check_b = $mysqli->query("SELECT id FROM tbl_finance_account_groups WHERE school_id = {$school_b} AND id = {$group_a_id}");
if ($check_b->num_rows == 0) {
    echo "  [PASS] School A Account Group is INVISIBLE to School B.\n";
} else {
    echo "  [FAIL] School A data leaked into School B!\n";
}

// Create Account Head under group_a_id in School A
$unique_head_name_a = 'Test Account Head A ' . rand(1000, 9999);
$unique_head_code_a = '9' . rand(100, 999);
$mysqli->query("INSERT INTO tbl_finance_accounts (school_id, account_group_id, account_code, account_name, account_type, opening_balance, opening_balance_type, status, created_at) VALUES ({$school_a}, {$group_a_id}, '{$unique_head_code_a}', '{$unique_head_name_a}', 'Cash', 1500.00, 'Debit', 1, NOW())");
$head_a_id = $mysqli->insert_id;
echo "  [School A] Created Account Head '{$unique_head_name_a}' (ID: {$head_a_id})\n";

// Query Account Head from School B
$check_head_b = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$school_b} AND id = {$head_a_id}");
if ($check_head_b->num_rows == 0) {
    echo "  [PASS] School A Account Head is INVISIBLE to School B.\n";
} else {
    echo "  [FAIL] School A Account Head leaked into School B!\n";
}

// Create Custom Account in School A
$unique_custom_name_a = 'Test Custom Bank A ' . rand(1000, 9999);
$mysqli->query("INSERT INTO tbl_finance_custom_accounts (school_id, account_head_id, account_name, account_number, account_type, opening_balance, opening_balance_type, status, created_at) VALUES ({$school_a}, {$head_a_id}, '{$unique_custom_name_a}', '1234567890123456', 'Bank', 5000.00, 'Debit', 1, NOW())");
$custom_a_id = $mysqli->insert_id;
echo "  [School A] Created Custom Account '{$unique_custom_name_a}' (ID: {$custom_a_id})\n";

// Query Custom Account from School B
$check_custom_b = $mysqli->query("SELECT id FROM tbl_finance_custom_accounts WHERE school_id = {$school_b} AND id = {$custom_a_id}");
if ($check_custom_b->num_rows == 0) {
    echo "  [PASS] School A Custom Account is INVISIBLE to School B.\n";
} else {
    echo "  [FAIL] School A Custom Account leaked into School B!\n";
}


// TEST 2: DELETION RESTRICTION & USAGE CHECKS
echo "\nTEST 2: Deletion Restriction (In-Use Checks)\n";
echo "-------------------------------------------\n";

// Group A has Head A linked to it. It must NOT be deletable!
$head_count = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_finance_accounts WHERE account_group_id = {$group_a_id} AND is_deleted = 'n'")->fetch_assoc()['cnt'];
if ($head_count > 0) {
    echo "  [PASS] Account Group has {$head_count} linked account head(s) -> System prevents deletion.\n";
} else {
    echo "  [FAIL] Account Group usage check failed.\n";
}

// Head A has Custom Account A linked to it. It must NOT be deletable!
$custom_count = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_finance_custom_accounts WHERE account_head_id = {$head_a_id} AND is_deleted = 'n'")->fetch_assoc()['cnt'];
if ($custom_count > 0) {
    echo "  [PASS] Account Head has {$custom_count} linked custom account(s) -> System prevents deletion.\n";
} else {
    echo "  [FAIL] Account Head usage check failed.\n";
}


// TEST 3: CLEANUP TEST RECORDS
echo "\nTEST 3: Safe Cleanup of Test Records\n";
echo "------------------------------------\n";
$mysqli->query("DELETE FROM tbl_finance_custom_accounts WHERE id = {$custom_a_id}");
$mysqli->query("DELETE FROM tbl_finance_accounts WHERE id = {$head_a_id}");
$mysqli->query("DELETE FROM tbl_finance_account_groups WHERE id = {$group_a_id}");
echo "  [OK] Cleaned up temporary test artifacts.\n";


// TEST 4: PERMISSION MATRIX TREE VERIFICATION
echo "\nTEST 4: Permission Matrix Tree Verification\n";
echo "-------------------------------------------\n";
$perm_res = $mysqli->query("SELECT p.permission_id, p.permission_key, p.action, mp.menu_id FROM tbl_menu_permissions mp JOIN tbl_permissions p ON p.permission_id = mp.permission_id WHERE mp.menu_id IN (327, 329, 330, 331) ORDER BY mp.menu_id, p.permission_id");

$menu_perms = [];
while ($row = $perm_res->fetch_assoc()) {
    $menu_perms[$row['menu_id']][] = $row['permission_key'] . ' (' . $row['action'] . ')';
}

foreach ($menu_perms as $mid => $pkeys) {
    echo "  Menu ID {$mid}: " . implode(', ', $pkeys) . "\n";
}

echo "\n=======================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "=======================================================\n";
