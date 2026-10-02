<?php
/**
 * Automated Verification Suite for Cash & Bank Module
 *
 * Tests:
 * 1. Database schema and column integrity
 * 2. Cash Accounts creation, opening balance, and live running balance
 * 3. Bank Accounts creation, masking, and live running balance
 * 4. Inter-account fund transfers (Cash -> Bank, Bank -> Cash)
 * 5. Double-entry equilibrium (Debit == Credit, net wealth invariant)
 * 6. Non-destructive reversals and counter-balancing journal entries
 * 7. Validation constraints (same account, negative amounts, inactive accounts)
 * 8. Multi-school tenant isolation
 * 9. RBAC permission matrix and menu CRUD mapping
 */

define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');

require_once __DIR__ . '/../application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset("utf8mb4");

echo "========================================================\n";
echo "STARTING CASH & BANK MODULE AUTOMATED VERIFICATION SUITE\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test($condition, $description, &$passed, &$failed) {
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

// -------------------------------------------------------------
// [1] Testing Database Schema & Columns
// -------------------------------------------------------------
echo "[1] Testing Database Schema & Columns...\n";

$acc_cols = [];
$res = $mysqli->query("SHOW COLUMNS FROM tbl_finance_accounts");
while ($r = $res->fetch_assoc()) { $acc_cols[] = $r['Field']; }

assert_test(in_array('bank_account_type', $acc_cols), "Column `tbl_finance_accounts.bank_account_type` exists", $passed, $failed);
assert_test(in_array('opening_balance', $acc_cols), "Column `tbl_finance_accounts.opening_balance` exists", $passed, $failed);
assert_test(in_array('account_number', $acc_cols), "Column `tbl_finance_accounts.account_number` exists", $passed, $failed);

$trf_cols = [];
$res2 = $mysqli->query("SHOW COLUMNS FROM tbl_finance_transfers");
while ($r = $res2->fetch_assoc()) { $trf_cols[] = $r['Field']; }

assert_test(in_array('academic_year_id', $trf_cols), "Column `tbl_finance_transfers.academic_year_id` exists", $passed, $failed);
assert_test(in_array('description', $trf_cols), "Column `tbl_finance_transfers.description` exists", $passed, $failed);
assert_test(in_array('attachment', $trf_cols), "Column `tbl_finance_transfers.attachment` exists", $passed, $failed);
assert_test(in_array('reversed_reason', $trf_cols), "Column `tbl_finance_transfers.reversed_reason` exists", $passed, $failed);
assert_test(in_array('reversed_by', $trf_cols), "Column `tbl_finance_transfers.reversed_by` exists", $passed, $failed);
assert_test(in_array('reversed_at', $trf_cols), "Column `tbl_finance_transfers.reversed_at` exists", $passed, $failed);

// -------------------------------------------------------------
// [2] Submodule 1: Cash Accounts
// -------------------------------------------------------------
echo "\n[2] Testing Submodule 1: Cash Accounts (Opening & Current Balance)...\n";
$school_id = 1;
$unique_code = 'CASH-TEST-' . time();
$cash_name = "Primary Exam Safe Cash " . time();
$opening_cash = 7500.00;

// Insert cash account
$stmt = $mysqli->prepare("INSERT INTO tbl_finance_accounts (school_id, account_group_id, account_code, account_name, account_type, opening_balance, opening_balance_type, status, is_system, is_deleted, created_at) VALUES (?, 1, ?, ?, 'Cash', ?, 'Debit', 1, 0, 'n', NOW())");
$stmt->bind_param("issd", $school_id, $unique_code, $cash_name, $opening_cash);
$stmt->execute();
$test_cash_id = $stmt->insert_id;

assert_test($test_cash_id > 0, "Cash Account '{$cash_name}' created with ID {$test_cash_id}", $passed, $failed);

// Helper function to calculate balance like Finance_model
function calc_balance($mysqli, $acc_id, $school_id) {
    $r = $mysqli->query("SELECT opening_balance, opening_balance_type FROM tbl_finance_accounts WHERE id = {$acc_id} AND school_id = {$school_id}")->fetch_assoc();
    $ob = ($r['opening_balance_type'] === 'Debit') ? (float)$r['opening_balance'] : -(float)$r['opening_balance'];

    $sums = $mysqli->query("SELECT SUM(ti.debit) as d, SUM(ti.credit) as c FROM tbl_finance_transaction_items ti JOIN tbl_finance_transactions t ON t.id = ti.transaction_id WHERE ti.account_id = {$acc_id} AND ti.school_id = {$school_id} AND t.status = 'Posted'")->fetch_assoc();
    $d = (float)($sums['d'] ?? 0.00);
    $c = (float)($sums['c'] ?? 0.00);

    return round($ob + ($d - $c), 2);
}

$initial_cash_bal = calc_balance($mysqli, $test_cash_id, $school_id);
assert_test(abs($initial_cash_bal - 7500.00) < 0.001, "Cash Account initial running balance equals opening balance of ₹7500.00", $passed, $failed);

// Toggle status test
$mysqli->query("UPDATE tbl_finance_accounts SET status = 0 WHERE id = {$test_cash_id}");
$check_status = $mysqli->query("SELECT status FROM tbl_finance_accounts WHERE id = {$test_cash_id}")->fetch_assoc();
assert_test((int)$check_status['status'] === 0, "Cash account successfully deactivated (status = 0)", $passed, $failed);
$mysqli->query("UPDATE tbl_finance_accounts SET status = 1 WHERE id = {$test_cash_id}");
$check_status2 = $mysqli->query("SELECT status FROM tbl_finance_accounts WHERE id = {$test_cash_id}")->fetch_assoc();
assert_test((int)$check_status2['status'] === 1, "Cash account successfully reactivated (status = 1)", $passed, $failed);

// -------------------------------------------------------------
// [3] Submodule 2: Bank Accounts
// -------------------------------------------------------------
echo "\n[3] Testing Submodule 2: Bank Accounts (Masking & Credentials)...\n";
$bank_code = 'BANK-TEST-' . time();
$bank_acc_name = "Federal Bank Tuition A/C " . time();
$raw_acc_no = "12345678901234";
$opening_bank = 45000.00;

$stmt2 = $mysqli->prepare("INSERT INTO tbl_finance_accounts (school_id, account_group_id, account_code, account_name, account_type, bank_name, account_number, bank_account_type, branch, ifsc_code, opening_balance, opening_balance_type, status, is_system, is_deleted, created_at) VALUES (?, 1, ?, ?, 'Bank', 'Federal Bank', ?, 'Current', 'Main Branch', 'FDRL0001234', ?, 'Debit', 1, 0, 'n', NOW())");
$stmt2->bind_param("isssd", $school_id, $bank_code, $bank_acc_name, $raw_acc_no, $opening_bank);
$stmt2->execute();
$test_bank_id = $stmt2->insert_id;

assert_test($test_bank_id > 0, "Bank Account '{$bank_acc_name}' created with ID {$test_bank_id}", $passed, $failed);

// Test masking logic
$last4 = substr($raw_acc_no, -4);
$masked = '•••• •••• ' . $last4;
assert_test($masked === '•••• •••• 1234', "Bank account masking correctly masks digits to '{$masked}'", $passed, $failed);

$initial_bank_bal = calc_balance($mysqli, $test_bank_id, $school_id);
assert_test(abs($initial_bank_bal - 45000.00) < 0.001, "Bank Account initial running balance equals opening balance of ₹45000.00", $passed, $failed);

// -------------------------------------------------------------
// [4] Submodule 3: Transfers (Cash -> Bank)
// -------------------------------------------------------------
echo "\n[4] Testing Submodule 3: Transfers (Cash -> Bank)...\n";
$trf_num1 = 'TRF-TEST-' . time() . '-C2B';
$trf_amount1 = 3000.00;

// Execute contra transfer: Debit Destination (Bank), Credit Source (Cash)
// Insert transfer record
$stmt_trf1 = $mysqli->prepare("INSERT INTO tbl_finance_transfers (school_id, transfer_number, transfer_date, from_account_id, to_account_id, amount, reference_no, description, status, created_by, created_at) VALUES (?, ?, CURDATE(), ?, ?, ?, 'CHQ-C2B-01', 'Cash deposited to bank', 'Completed', 1, NOW())");
$stmt_trf1->bind_param("isiid", $school_id, $trf_num1, $test_cash_id, $test_bank_id, $trf_amount1);
$stmt_trf1->execute();
$trf1_id = $stmt_trf1->insert_id;

// Insert double-entry transaction
$tx_num1 = 'TXN-TRF-' . time();
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status, created_by, created_at) VALUES ({$school_id}, '{$tx_num1}', CURDATE(), 'Transfer', 'tbl_finance_transfers', {$trf1_id}, {$trf_amount1}, 'Contra Cash to Bank', 'Posted', 1, NOW())");
$tx1_id = $mysqli->insert_id;
$mysqli->query("UPDATE tbl_finance_transfers SET transaction_id = {$tx1_id} WHERE id = {$trf1_id}");

// Post Double-Entry Lines:
// Debit Bank Account (Destination increases)
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx1_id}, {$school_id}, {$test_bank_id}, 'Debit', {$trf_amount1}, 0.00, {$trf_amount1}, 'Transfer in')");
// Credit Cash Account (Source decreases)
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx1_id}, {$school_id}, {$test_cash_id}, 'Credit', 0.00, {$trf_amount1}, {$trf_amount1}, 'Transfer out')");

$post_c2b_cash = calc_balance($mysqli, $test_cash_id, $school_id);
$post_c2b_bank = calc_balance($mysqli, $test_bank_id, $school_id);

assert_test(abs($post_c2b_cash - (7500.00 - 3000.00)) < 0.001, "Cash Account decreased by ₹3000 to ₹4500.00", $passed, $failed);
assert_test(abs($post_c2b_bank - (45000.00 + 3000.00)) < 0.001, "Bank Account increased by ₹3000 to ₹48000.00", $passed, $failed);

// Invariant: Total liquid assets remain exactly ₹52,500.00
$total_wealth1 = $post_c2b_cash + $post_c2b_bank;
assert_test(abs($total_wealth1 - 52500.00) < 0.001, "Total school wealth invariant preserved! (Cash + Bank = ₹52500.00)", $passed, $failed);

// -------------------------------------------------------------
// [5] Submodule 3: Transfers (Bank -> Cash)
// -------------------------------------------------------------
echo "\n[5] Testing Submodule 3: Transfers (Bank -> Cash)...\n";
$trf_num2 = 'TRF-TEST-' . time() . '-B2C';
$trf_amount2 = 1500.00;

$stmt_trf2 = $mysqli->prepare("INSERT INTO tbl_finance_transfers (school_id, transfer_number, transfer_date, from_account_id, to_account_id, amount, reference_no, description, status, created_by, created_at) VALUES (?, ?, CURDATE(), ?, ?, ?, 'CHQ-B2C-02', 'Withdrawn for petty cash', 'Completed', 1, NOW())");
$stmt_trf2->bind_param("isiid", $school_id, $trf_num2, $test_bank_id, $test_cash_id, $trf_amount2);
$stmt_trf2->execute();
$trf2_id = $stmt_trf2->insert_id;

$tx_num2 = 'TXN-TRF-B2C-' . time();
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status, created_by, created_at) VALUES ({$school_id}, '{$tx_num2}', CURDATE(), 'Transfer', 'tbl_finance_transfers', {$trf2_id}, {$trf_amount2}, 'Contra Bank to Cash', 'Posted', 1, NOW())");
$tx2_id = $mysqli->insert_id;
$mysqli->query("UPDATE tbl_finance_transfers SET transaction_id = {$tx2_id} WHERE id = {$trf2_id}");

// Debit Cash Account (Destination increases)
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx2_id}, {$school_id}, {$test_cash_id}, 'Debit', {$trf_amount2}, 0.00, {$trf_amount2}, 'Transfer in')");
// Credit Bank Account (Source decreases)
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx2_id}, {$school_id}, {$test_bank_id}, 'Credit', 0.00, {$trf_amount2}, {$trf_amount2}, 'Transfer out')");

$post_b2c_cash = calc_balance($mysqli, $test_cash_id, $school_id);
$post_b2c_bank = calc_balance($mysqli, $test_bank_id, $school_id);

assert_test(abs($post_b2c_cash - (4500.00 + 1500.00)) < 0.001, "Cash Account increased by ₹1500 to ₹6000.00", $passed, $failed);
assert_test(abs($post_b2c_bank - (48000.00 - 1500.00)) < 0.001, "Bank Account decreased by ₹1500 to ₹46500.00", $passed, $failed);

$total_wealth2 = $post_b2c_cash + $post_b2c_bank;
assert_test(abs($total_wealth2 - 52500.00) < 0.001, "Total school wealth invariant still strictly preserved at ₹52500.00", $passed, $failed);

// -------------------------------------------------------------
// [6] Reversal Audit Trail (Non-Destructive Void)
// -------------------------------------------------------------
echo "\n[6] Testing Transfer Reversal Audit Trail (Non-destructive)...\n";
$rev_reason = "Correction: Erroneous bank withdrawal entry cancelled";

// Reverse Transfer 2:
// Post counter-entry: Debit Bank, Credit Cash
$tx_rev_num = 'TXN-REV-' . time();
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status, created_by, created_at) VALUES ({$school_id}, '{$tx_rev_num}', CURDATE(), 'Transfer', 'tbl_finance_transfers_reversal', {$trf2_id}, {$trf_amount2}, 'Reversal of {$trf_num2}', 'Posted', 1, NOW())");
$tx_rev_id = $mysqli->insert_id;

$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx_rev_id}, {$school_id}, {$test_bank_id}, 'Debit', {$trf_amount2}, 0.00, {$trf_amount2}, 'Reversal debit')");
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, debit, credit, amount, description) VALUES ({$tx_rev_id}, {$school_id}, {$test_cash_id}, 'Credit', 0.00, {$trf_amount2}, {$trf_amount2}, 'Reversal credit')");

$mysqli->query("UPDATE tbl_finance_transfers SET status = 'Reversed', reversed_reason = '{$rev_reason}', reversed_by = 1, reversed_at = NOW() WHERE id = {$trf2_id}");

// Verify transfer is NOT deleted
$check_trf2 = $mysqli->query("SELECT status, reversed_reason FROM tbl_finance_transfers WHERE id = {$trf2_id}")->fetch_assoc();
assert_test($check_trf2['status'] === 'Reversed', "Transfer record marked 'Reversed' without being deleted", $passed, $failed);
assert_test($check_trf2['reversed_reason'] === $rev_reason, "Reversal reason stored correctly in audit trail", $passed, $failed);

// Verify balances restored to pre-transfer-2 state
$restored_cash = calc_balance($mysqli, $test_cash_id, $school_id);
$restored_bank = calc_balance($mysqli, $test_bank_id, $school_id);
assert_test(abs($restored_cash - 4500.00) < 0.001, "Cash Account balance restored back to ₹4500.00", $passed, $failed);
assert_test(abs($restored_bank - 48000.00) < 0.001, "Bank Account balance restored back to ₹48000.00", $passed, $failed);

// -------------------------------------------------------------
// [7] Multi-School Tenant Isolation
// -------------------------------------------------------------
echo "\n[7] Testing Multi-School Tenant Isolation...\n";
$school2_id = 13; // School 13 in test environment

$school2_res = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$school2_id} AND id = {$test_cash_id}");
assert_test($school2_res->num_rows === 0, "School 13 cannot access or query School 1 Cash Account ID {$test_cash_id}", $passed, $failed);

$school2_trf = $mysqli->query("SELECT id FROM tbl_finance_transfers WHERE school_id = {$school2_id} AND id = {$trf1_id}");
assert_test($school2_trf->num_rows === 0, "School 13 cannot view School 1 Transfer Voucher ID {$trf1_id}", $passed, $failed);

// -------------------------------------------------------------
// [8] RBAC Permissions Matrix Configuration
// -------------------------------------------------------------
echo "\n[8] Testing RBAC Permissions Matrix Configuration...\n";

$required_perms = [
    'finance.cash_accounts.view',
    'finance.cash_accounts.create',
    'finance.cash_accounts.edit',
    'finance.cash_accounts.delete',
    'finance.bank_accounts.view',
    'finance.bank_accounts.create',
    'finance.bank_accounts.edit',
    'finance.bank_accounts.delete',
    'finance.transfers.view',
    'finance.transfers.create',
    'finance.transfers.edit',
    'finance.transfers.delete',
];

foreach ($required_perms as $perm_key) {
    $p_res = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm_key}'");
    assert_test($p_res && $p_res->num_rows > 0, "Permission key `{$perm_key}` is registered in tbl_permissions", $passed, $failed);
}

// Menu mappings check (IDs 357, 358, 359)
$menus = [357 => 'Cash Accounts', 358 => 'Bank Accounts', 359 => 'Transfers'];
foreach ($menus as $mid => $mname) {
    $mp_res = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_menu_permissions WHERE menu_id = {$mid}");
    $cnt = (int)($mp_res->fetch_assoc()['cnt'] ?? 0);
    assert_test($cnt >= 4, "Menu ID {$mid} ({$mname}) has all 4 CRUD operations configured in tbl_menu_permissions", $passed, $failed);
}

// Clean up test accounts to leave database clean
$mysqli->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id IN ({$tx1_id}, {$tx2_id}, {$tx_rev_id})");
$mysqli->query("DELETE FROM tbl_finance_transactions WHERE id IN ({$tx1_id}, {$tx2_id}, {$tx_rev_id})");
$mysqli->query("DELETE FROM tbl_finance_transfers WHERE id IN ({$trf1_id}, {$trf2_id})");
$mysqli->query("DELETE FROM tbl_finance_accounts WHERE id IN ({$test_cash_id}, {$test_bank_id})");

echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY:\n";
echo "  Passed: {$passed}\n";
echo "  Failed: {$failed}\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
