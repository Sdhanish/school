<?php
/**
 * Comprehensive verification of all Fee and Finance module routes.
 * Run via CLI: php tests/fixtures/test_all_finance_routes.php
 */

$baseUrl = 'http://localhost/schoolnew/';
$cookieFile = __DIR__ . '/cookie_admin_all.txt';
if (file_exists($cookieFile)) @unlink($cookieFile);

// Login
$loginUrl = $baseUrl . 'auth/login';
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$page = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_test_name" value="([^"]+)"/', $page, $m);
$csrf = $m[1] ?? '';

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_test_name' => $csrf,
    'email'          => 'Admin',
    'password'       => '123456'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

$routesToTest = [
    // 1. Dashboard & Core
    'finance'                              => 'Fee & Finance Dashboard',
    'finance/dashboard'                    => 'Fee & Finance Dashboard',
    'fee-finance/dashboard'                => 'Fee & Finance Dashboard',
    'fees'                                 => 'Fee & Finance Dashboard',
    'fees/dashboard'                       => 'Fee & Finance Dashboard',

    // 2. Chart of Accounts
    'fee-finance/account-groups'           => 'Account Groups',
    'finance/account_groups'               => 'Account Groups',
    'fee-finance/account-heads'            => 'Account Heads',
    'finance/account_heads'                => 'Account Heads',
    'fee-finance/custom-accounts'          => 'Custom Accounts',
    'finance/custom_accounts'              => 'Custom Accounts',

    // 3. Ledgers
    'finance/ledgers'                      => 'Ledgers',
    'finance/ledger_students'              => 'Student',
    'finance/ledger_staff'                 => 'Staff',
    'finance/ledger_other_parties'         => 'Other Part',
    'finance/ledger_general'               => 'General Ledger',
    'fees/student_fees'                    => 'Student',

    // 4. Fees
    'finance/fee_types'                    => 'Fee Types',
    'fees/categories'                      => 'Fee Types',
    'fees/fee_types'                       => 'Fee Types',
    'finance/fee_structures'               => 'Fee Structure',
    'fees/structures'                      => 'Fee Structure',
    'fees/fee_structures'                  => 'Fee Structure',
    'finance/fee_assignments'              => 'Fee Assignment',
    'fees/assignments'                     => 'Fee Assignment',
    'fees/fee_assignments'                 => 'Fee Assignment',
    'finance/fee_collection'               => 'Fee Collection',
    'fees/collection'                      => 'Fee Collection',
    'fees/collection?student_id=49'        => 'Fee Collection',
    'finance/pending_fees'                 => 'Pending Fees',
    'fees/due_fees'                        => 'Pending Fees',
    'fees/pending_fees'                    => 'Pending Fees',
    'finance/fee_receipts'                 => 'Receipts',
    'fees/payments'                        => 'Receipts',
    'fees/receipts'                        => 'Receipts',
    'fees/fee_receipts'                    => 'Receipts',

    // 5. Transactions
    'finance/transactions_income'          => 'Income Transactions',
    'finance/income'                       => 'Income Transactions',
    'finance/transactions_expense'         => 'Expense Transactions',
    'finance/adjustments'                  => 'Adjustments',
    'fees/adjustments'                     => 'Adjustments',
    'finance/refunds'                      => 'Refunds',
    'fees/refunds'                         => 'Refunds',
    'finance/journal_entries'              => 'Journal Entries',

    // 6. Expenses
    'finance/expenses'                     => 'Expense',
    'finance/expense_entry'                => 'Expense',
    'finance/staff_payouts'                => 'Staff Payouts',
    'finance/vendor_payments'              => 'Vendor Payment',
    'finance/other_expenses'               => 'Other Expenses',
    'finance/expense_types'                => 'Expense',

    // 7. Cash & Bank
    'finance/cash_accounts'                => 'Cash Accounts',
    'finance/bank_accounts'                => 'Bank Accounts',
    'finance/cash_bank'                    => 'Cash Accounts',
    'finance/transfers'                    => 'Transfers',

    // 8. Reports
    'finance/reports'                      => 'Reports',
    'fees/reports'                         => 'Reports',
];

echo "====================================================================\n";
echo "AUDIT ALL ROUTES IN FEE & FINANCE MODULE (" . count($routesToTest) . " Routes)\n";
echo "====================================================================\n\n";

$passed = 0;
$failed = 0;

foreach ($routesToTest as $route => $expectedSnippet) {
    $url = $baseUrl . $route;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $is404 = (strpos($html, '404 - Page Not Found') !== false || $code === 404);
    $hasExpected = (stripos($html, $expectedSnippet) !== false);

    if ($code === 200 && !$is404 && $hasExpected) {
        echo sprintf("  [PASS] %-40s => HTTP %d | Contains '%s'\n", $route, $code, $expectedSnippet);
        $passed++;
    } else {
        echo sprintf("  [FAIL] %-40s => HTTP %d | 404: %s | Match '%s': %s\n", 
            $route, 
            $code, 
            $is404 ? 'YES' : 'NO', 
            $expectedSnippet, 
            $hasExpected ? 'YES' : 'NO'
        );
        $failed++;
    }
}

echo "\n====================================================================\n";
echo "TOTAL ROUTES AUDITED: " . count($routesToTest) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "====================================================================\n";

@unlink($cookieFile);
exit($failed === 0 ? 0 : 1);
