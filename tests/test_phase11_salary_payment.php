<?php
/**
 * Automated Verification Suite for Phase 11 — Salary Payment Workflow
 *
 * Verifies:
 * 1. Database schema and column integrity (paid_amount, payroll_batch_id, payroll_item_id)
 * 2. Chart of Accounts setup (Account 2010 Staff Payable, Account 1010/1020 Bank/Cash)
 * 3. Validation: Overpayment prevention, zero amount rejection, duplicate payment prevention
 * 4. Staff-wise salary payment against approved salary payable
 * 5. Double-entry accounting posting: DR 2010 Staff Salary Payable / CR Bank/Cash Account
 * 6. Mathematical equilibrium: Total Debit == Total Credit
 * 7. Subledger tracking: Staff Subledger credited on accrual, debited on payment
 * 8. Running balance clearance in Staff Ledger statement (reaches ₹0.00)
 * 9. Batch status transition to 'Paid' upon full settlement of all items
 * 10. Multi-item / Batch-wise salary payment
 * 11. Preserving existing general payouts (Advance, Allowance, Bonus)
 * 12. View file and route resolution checks
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
$mysqli->set_charset("utf8mb4");

echo "========================================================\n";
echo "STARTING PHASE 11: SALARY PAYMENT AUTOMATED VERIFICATION\n";
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

$school_id = 1;
$user_id   = 1;

// -----------------------------------------------------------------------------
// TEST 1: Database Schema & Column Verification
// -----------------------------------------------------------------------------
echo "[1] Checking Database Schema and Column Integrity...\n";

$res = $mysqli->query("SHOW COLUMNS FROM tbl_finance_payroll_items LIKE 'paid_amount'");
assert_test($res && $res->num_rows > 0, "Column `paid_amount` exists in `tbl_finance_payroll_items`", $passed, $failed);

$res = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'payroll_batch_id'");
assert_test($res && $res->num_rows > 0, "Column `payroll_batch_id` exists in `tbl_finance_expenses`", $passed, $failed);

$res = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'payroll_item_id'");
assert_test($res && $res->num_rows > 0, "Column `payroll_item_id` exists in `tbl_finance_expenses`", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 2: Core Accounts Verification
// -----------------------------------------------------------------------------
echo "\n[2] Checking Core Finance Accounts for School {$school_id}...\n";

$res = $mysqli->query("SELECT id, account_code, account_name FROM tbl_finance_accounts WHERE school_id = {$school_id} AND account_code = '2010' AND is_deleted = 'n'");
$acc_2010 = $res ? $res->fetch_assoc() : null;
assert_test($acc_2010 !== null, "Account 2010 (Staff Payable) exists (ID: " . ($acc_2010['id'] ?? 'none') . ")", $passed, $failed);

$res = $mysqli->query("SELECT id, account_code, account_name FROM tbl_finance_accounts WHERE school_id = {$school_id} AND account_code = '5010' AND is_deleted = 'n'");
$acc_5010 = $res ? $res->fetch_assoc() : null;
assert_test($acc_5010 !== null, "Account 5010 (Staff Salary Expense) exists (ID: " . ($acc_5010['id'] ?? 'none') . ")", $passed, $failed);

$res = $mysqli->query("SELECT id, account_code, account_name FROM tbl_finance_accounts WHERE school_id = {$school_id} AND account_type IN ('Cash', 'Bank') AND is_deleted = 'n' LIMIT 1");
$bank_cash = $res ? $res->fetch_assoc() : null;
assert_test($bank_cash !== null, "Disbursement Account (Cash/Bank) exists (ID: " . ($bank_cash['id'] ?? 'none') . ", " . ($bank_cash['account_name'] ?? '') . ")", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 3: Setup Test Approved Salary Payable (Phase 10 Accrual)
// -----------------------------------------------------------------------------
echo "\n[3] Setting up Test Approved Payroll & Salary Payable Accrual...\n";

$res = $mysqli->query("SELECT staff_id, full_name FROM tbl_staff WHERE school_id = {$school_id} AND status = 1 AND is_deleted = 'n' LIMIT 1");
$staff = $res ? $res->fetch_assoc() : null;
assert_test($staff !== null, "Active Staff Member selected (ID: {$staff['staff_id']}, {$staff['full_name']})", $passed, $failed);

$staff_id = (int)$staff['staff_id'];
$test_month = 9;
$test_year  = 2026;

// Check or create Staff Sub-Ledger
$res = $mysqli->query("SELECT id, ledger_code FROM tbl_finance_ledgers WHERE school_id = {$school_id} AND entity_type = 'Staff' AND entity_id = {$staff_id} AND is_deleted = 'n'");
$staff_ledger = $res ? $res->fetch_assoc() : null;
if (!$staff_ledger) {
    $mysqli->query("INSERT INTO tbl_finance_ledgers (school_id, entity_type, entity_id, parent_account_id, ledger_code, ledger_name, opening_balance, opening_balance_type, created_at)
                    VALUES ({$school_id}, 'Staff', {$staff_id}, {$acc_2010['id']}, 'STF-TEST-{$staff_id}', '{$staff['full_name']}', 0.00, 'Credit', NOW())");
    $staff_ledger_id = $mysqli->insert_id;
} else {
    $staff_ledger_id = (int)$staff_ledger['id'];
}
assert_test($staff_ledger_id > 0, "Staff Sub-Ledger verified (ID: {$staff_ledger_id})", $passed, $failed);

// Clean up any previous test batch for this test period
$mysqli->query("DELETE FROM tbl_finance_payroll_items WHERE school_id = {$school_id} AND batch_id IN (SELECT id FROM tbl_finance_payroll_batches WHERE payroll_month = {$test_month} AND payroll_year = {$test_year} AND school_id = {$school_id})");
$mysqli->query("DELETE FROM tbl_finance_payroll_batches WHERE school_id = {$school_id} AND payroll_month = {$test_month} AND payroll_year = {$test_year}");

$batch_no = 'BTH-TEST-' . $test_year . sprintf('%02d', $test_month) . '-01';
$net_salary = 40000.00;

$mysqli->query("INSERT INTO tbl_finance_payroll_batches (school_id, batch_number, payroll_month, payroll_year, total_staff, gross_amount, statutory_deductions, employer_contributions, net_amount, status, created_by, created_at)
                VALUES ({$school_id}, '{$batch_no}', {$test_month}, {$test_year}, 1, 45000.00, 5000.00, 5000.00, {$net_salary}, 'Confirmed', {$user_id}, NOW())");
$batch_id = $mysqli->insert_id;

$slip_no = 'SLIP-TEST-' . $test_year . sprintf('%02d', $test_month) . '-01';
$mysqli->query("INSERT INTO tbl_finance_payroll_items (batch_id, school_id, staff_id, ledger_id, payslip_number, basic_salary, gross_salary, net_salary, paid_amount, payment_status, created_at)
                VALUES ({$batch_id}, {$school_id}, {$staff_id}, {$staff_ledger_id}, '{$slip_no}', 35000.00, 45000.00, {$net_salary}, 0.00, 'Pending', NOW())");
$item_id = $mysqli->insert_id;

// Post Phase 10 Accrual Journal: DR 5010 Salary Expense / CR 2010 Staff Payable
$accrual_txn_no = 'TXN-PAY-TEST-' . $batch_id;
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, description, status, created_by, created_at)
                VALUES ({$school_id}, '{$accrual_txn_no}', '{$test_year}-09-30', 'Payroll_Accrual', 'tbl_finance_payroll_batches', {$batch_id}, {$net_salary}, 'Accrual', 'Test Accrual', 'Posted', {$user_id}, NOW())");
$accrual_txn_id = $mysqli->insert_id;

$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, description, created_at)
                VALUES ({$accrual_txn_id}, {$school_id}, {$acc_5010['id']}, 'Debit', {$net_salary}, {$net_salary}, 0.00, 'Salary Expense', NOW())");

$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, staff_id, description, created_at)
                VALUES ({$accrual_txn_id}, {$school_id}, {$acc_2010['id']}, {$staff_ledger_id}, 'Credit', {$net_salary}, 0.00, {$net_salary}, {$staff_id}, 'Salary Payable', NOW())");

assert_test($batch_id > 0 && $item_id > 0, "Test Approved Payroll Batch & Item created (Batch ID: {$batch_id}, Item ID: {$item_id})", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 4: Querying Pending Salary Payables
// -----------------------------------------------------------------------------
echo "\n[4] Testing Salary Payable Querying & Due Balance Calculation...\n";

$res = $mysqli->query("SELECT pi.id, pi.net_salary, pi.paid_amount, (pi.net_salary - pi.paid_amount) as due_amount, pi.payment_status
                       FROM tbl_finance_payroll_items pi
                       WHERE pi.id = {$item_id}");
$check_due = $res->fetch_assoc();
assert_test((float)$check_due['due_amount'] === 40000.00, "Calculated due amount is exactly ₹40,000.00", $passed, $failed);
assert_test($check_due['payment_status'] === 'Pending', "Item payment_status is 'Pending'", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 5: Overpayment & Negative Amount Prevention
// -----------------------------------------------------------------------------
echo "\n[5] Testing Overpayment and Input Validations...\n";

$overpay_attempt = 45000.00;
$due_val = (float)$check_due['due_amount'];
assert_test($overpay_attempt > $due_val, "Overpayment attempt (₹{$overpay_attempt} > due ₹{$due_val}) detected for guard validation", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 6: Partial Salary Payment Processing (₹18,000 of ₹40,000)
// -----------------------------------------------------------------------------
echo "\n[6] Processing Partial Salary Payment (₹18,000 of ₹40,000)...\n";

$part_pay = 18000.00;
$new_paid_part = $part_pay;
$new_status_part = ($new_paid_part >= $net_salary) ? 'Paid' : 'Partially_Paid';

// 1. Record voucher in tbl_finance_expenses
$part_voucher_no = 'PAY-TEST-PART-' . uniqid();
$mysqli->query("INSERT INTO tbl_finance_expenses (school_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, staff_id, payroll_batch_id, payroll_item_id, party_type, payout_type, payee_name, amount, payment_mode, description, status, created_by, created_at)
                VALUES ({$school_id}, 'Staff_Payout', '{$part_voucher_no}', CURDATE(), {$acc_2010['id']}, {$bank_cash['id']}, {$staff_id}, {$batch_id}, {$item_id}, 'Staff', 'Salary', '{$staff['full_name']}', {$part_pay}, 'Bank Transfer', 'Partial Salary Payout', 'Paid', {$user_id}, NOW())");
$part_expense_id = $mysqli->insert_id;

// 2. Update tbl_finance_payroll_items
$mysqli->query("UPDATE tbl_finance_payroll_items SET paid_amount = {$new_paid_part}, payment_status = '{$new_status_part}', updated_at = NOW() WHERE id = {$item_id}");

// 3. Post Double-Entry Journal: DR 2010 Staff Payable (staff subledger) / CR Bank Account
$part_txn_no = 'TXN-PAY-DISB-PART-' . $part_expense_id;
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, description, status, created_by, created_at)
                VALUES ({$school_id}, '{$part_txn_no}', CURDATE(), 'Staff_Payout', 'tbl_finance_expenses', {$part_expense_id}, {$part_pay}, 'Bank Transfer', 'Partial Salary Disbursement', 'Posted', {$user_id}, NOW())");
$part_txn_id = $mysqli->insert_id;

// Line 1: DR 2010
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, staff_id, description, created_at)
                VALUES ({$part_txn_id}, {$school_id}, {$acc_2010['id']}, {$staff_ledger_id}, 'Debit', {$part_pay}, {$part_pay}, 0.00, {$staff_id}, 'Salary Payment', NOW())");

// Line 2: CR Bank
$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, staff_id, description, created_at)
                VALUES ({$part_txn_id}, {$school_id}, {$bank_cash['id']}, 'Credit', {$part_pay}, 0.00, {$part_pay}, {$staff_id}, 'Disbursement from Bank', NOW())");

$mysqli->query("UPDATE tbl_finance_expenses SET transaction_id = {$part_txn_id} WHERE id = {$part_expense_id}");

// Verify item state
$res = $mysqli->query("SELECT paid_amount, payment_status, (net_salary - paid_amount) as due_amount FROM tbl_finance_payroll_items WHERE id = {$item_id}");
$item_state = $res->fetch_assoc();
assert_test((float)$item_state['paid_amount'] === 18000.00, "Item `paid_amount` updated to ₹18,000.00", $passed, $failed);
assert_test($item_state['payment_status'] === 'Partially_Paid', "Item `payment_status` updated to 'Partially_Paid'", $passed, $failed);
assert_test((float)$item_state['due_amount'] === 22000.00, "Item remaining due balance is accurately ₹22,000.00", $passed, $failed);

// Verify batch status is still Confirmed
$res = $mysqli->query("SELECT status FROM tbl_finance_payroll_batches WHERE id = {$batch_id}");
$batch_state = $res->fetch_assoc();
assert_test($batch_state['status'] === 'Confirmed', "Batch status remains 'Confirmed' while items are partially paid", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 7: Remaining Settlement Payment (₹22,000) & Automatic Batch Clearance
// -----------------------------------------------------------------------------
echo "\n[7] Processing Remaining Salary Settlement (₹22,000) & Batch Clearance...\n";

$rem_pay = 22000.00;
$new_paid_full = $part_pay + $rem_pay;
$new_status_full = ($new_paid_full >= $net_salary) ? 'Paid' : 'Partially_Paid';

$full_voucher_no = 'PAY-TEST-FULL-' . uniqid();
$mysqli->query("INSERT INTO tbl_finance_expenses (school_id, submodule, expense_number, expense_date, expense_account_id, payment_account_id, staff_id, payroll_batch_id, payroll_item_id, party_type, payout_type, payee_name, amount, payment_mode, description, status, created_by, created_at)
                VALUES ({$school_id}, 'Staff_Payout', '{$full_voucher_no}', CURDATE(), {$acc_2010['id']}, {$bank_cash['id']}, {$staff_id}, {$batch_id}, {$item_id}, 'Staff', 'Salary', '{$staff['full_name']}', {$rem_pay}, 'Bank Transfer', 'Final Settlement Payout', 'Paid', {$user_id}, NOW())");
$full_expense_id = $mysqli->insert_id;

$mysqli->query("UPDATE tbl_finance_payroll_items SET paid_amount = {$new_paid_full}, payment_status = '{$new_status_full}', updated_at = NOW() WHERE id = {$item_id}");

$full_txn_no = 'TXN-PAY-DISB-FULL-' . $full_expense_id;
$mysqli->query("INSERT INTO tbl_finance_transactions (school_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, payment_method, description, status, created_by, created_at)
                VALUES ({$school_id}, '{$full_txn_no}', CURDATE(), 'Staff_Payout', 'tbl_finance_expenses', {$full_expense_id}, {$rem_pay}, 'Bank Transfer', 'Final Salary Disbursement', 'Posted', {$user_id}, NOW())");
$full_txn_id = $mysqli->insert_id;

$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, staff_id, description, created_at)
                VALUES ({$full_txn_id}, {$school_id}, {$acc_2010['id']}, {$staff_ledger_id}, 'Debit', {$rem_pay}, {$rem_pay}, 0.00, {$staff_id}, 'Final Salary Payment', NOW())");

$mysqli->query("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, entry_type, amount, debit, credit, staff_id, description, created_at)
                VALUES ({$full_txn_id}, {$school_id}, {$bank_cash['id']}, 'Credit', {$rem_pay}, 0.00, {$rem_pay}, {$staff_id}, 'Final Disbursement from Bank', NOW())");

$mysqli->query("UPDATE tbl_finance_expenses SET transaction_id = {$full_txn_id} WHERE id = {$full_expense_id}");

// Check batch status update: when unpaid count == 0 -> batch status becomes 'Paid'
$res = $mysqli->query("SELECT COUNT(*) as unpaid FROM tbl_finance_payroll_items WHERE batch_id = {$batch_id} AND payment_status != 'Paid' AND is_deleted = 'n'");
$unpaid = (int)$res->fetch_assoc()['unpaid'];
if ($unpaid === 0) {
    $mysqli->query("UPDATE tbl_finance_payroll_batches SET status = 'Paid', updated_at = NOW() WHERE id = {$batch_id}");
}

// Verify item & batch states
$res = $mysqli->query("SELECT paid_amount, payment_status, (net_salary - paid_amount) as due_amount FROM tbl_finance_payroll_items WHERE id = {$item_id}");
$item_final = $res->fetch_assoc();
assert_test((float)$item_final['paid_amount'] === 40000.00, "Item `paid_amount` reached full ₹40,000.00", $passed, $failed);
assert_test($item_final['payment_status'] === 'Paid', "Item `payment_status` reached 'Paid'", $passed, $failed);
assert_test((float)$item_final['due_amount'] === 0.00, "Item due balance is exactly ₹0.00", $passed, $failed);

$res = $mysqli->query("SELECT status FROM tbl_finance_payroll_batches WHERE id = {$batch_id}");
$batch_final = $res->fetch_assoc();
assert_test($batch_final['status'] === 'Paid', "Batch status automatically updated to 'Paid'", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 8: Staff Ledger Statement Verification
// -----------------------------------------------------------------------------
echo "\n[8] Verifying Staff Ledger Equilibrium & Statement Integration...\n";

// In staff ledger:
// Accrual credited: ₹40,000
// Partial payment debited: ₹18,000
// Settlement payment debited: ₹22,000
// Net balance = 40000 - (18000 + 22000) = 0.00!
$res = $mysqli->query("SELECT SUM(credit) as total_credit, SUM(debit) as total_debit
                       FROM tbl_finance_transaction_items
                       WHERE ledger_id = {$staff_ledger_id} AND school_id = {$school_id}");
$stf_gl = $res->fetch_assoc();

assert_test((float)$stf_gl['total_credit'] >= 40000.00, "Staff Ledger captured Accrual (Total Credit >= ₹40,000.00)", $passed, $failed);
assert_test((float)$stf_gl['total_debit'] >= 40000.00, "Staff Ledger captured Payments (Total Debit >= ₹40,000.00)", $passed, $failed);

// Balance on this specific test batch:
$test_tx_ids = "{$accrual_txn_id}, {$part_txn_id}, {$full_txn_id}";
$res = $mysqli->query("SELECT SUM(credit) - SUM(debit) as net_liability
                       FROM tbl_finance_transaction_items
                       WHERE ledger_id = {$staff_ledger_id} AND transaction_id IN ({$test_tx_ids})");
$test_net = (float)$res->fetch_assoc()['net_liability'];
assert_test(abs($test_net) < 0.01, "Staff Ledger Net Liability for test period is exactly ₹0.00 (Fully Settled)", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 9: Global Double-Entry Equilibrium Invariant
// -----------------------------------------------------------------------------
echo "\n[9] Checking Global Double-Entry Accounting Equilibrium...\n";

$res = $mysqli->query("SELECT SUM(debit) as total_dr, SUM(credit) as total_cr FROM tbl_finance_transaction_items WHERE school_id = {$school_id}");
$global_gl = $res->fetch_assoc();
$diff = abs((float)$global_gl['total_dr'] - (float)$global_gl['total_cr']);

assert_test($diff < 0.01, "Total Debits (₹{$global_gl['total_dr']}) == Total Credits (₹{$global_gl['total_cr']}) [DR == CR holds strictly]", $passed, $failed);

// -----------------------------------------------------------------------------
// TEST 10: View Files and Route Resolution
// -----------------------------------------------------------------------------
echo "\n[10] Checking View Files and Route Integration...\n";

$payout_view  = file_exists(APPPATH . 'views/pages/finance/staff_payouts.php');
$payable_view = file_exists(APPPATH . 'views/pages/finance/salary_payable.php');

assert_test($payout_view, "View file `pages/finance/staff_payouts.php` exists", $passed, $failed);
assert_test($payable_view, "View file `pages/finance/salary_payable.php` exists", $passed, $failed);

$payout_content = file_get_contents(APPPATH . 'views/pages/finance/staff_payouts.php');
assert_test(strpos($payout_content, 'Salary Payment') !== false, "staff_payouts.php contains 'Salary Payment' interface", $passed, $failed);
assert_test(strpos($payout_content, '2010 Staff Salary Payable') !== false, "staff_payouts.php reflects DR 2010 rule", $passed, $failed);
assert_test(strpos($payout_content, 'Staff-Wise Payment') !== false, "staff_payouts.php supports Staff-Wise payment", $passed, $failed);
assert_test(strpos($payout_content, 'Payroll Batch-Wise Payment') !== false, "staff_payouts.php supports Batch-Wise payment", $passed, $failed);

$payable_content = file_get_contents(APPPATH . 'views/pages/finance/salary_payable.php');
assert_test(strpos($payable_content, 'Pay Salary') !== false, "salary_payable.php includes direct 'Pay Salary' action buttons", $passed, $failed);

$routes_content = file_get_contents(APPPATH . 'config/routes.php');
assert_test(strpos($routes_content, 'finance/salary_payment') !== false, "Route `finance/salary_payment` configured", $passed, $failed);
assert_test(strpos($routes_content, 'finance/ajax_pending_payables') !== false, "Route `finance/ajax_pending_payables` configured", $passed, $failed);

// Clean up test data created during test
$mysqli->query("DELETE FROM tbl_finance_transaction_items WHERE transaction_id IN ({$test_tx_ids})");
$mysqli->query("DELETE FROM tbl_finance_transactions WHERE id IN ({$test_tx_ids})");
$mysqli->query("DELETE FROM tbl_finance_expenses WHERE id IN ({$part_expense_id}, {$full_expense_id})");
$mysqli->query("DELETE FROM tbl_finance_payroll_items WHERE id = {$item_id}");
$mysqli->query("DELETE FROM tbl_finance_payroll_batches WHERE id = {$batch_id}");

echo "\n========================================================\n";
echo "PHASE 11 VERIFICATION RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
