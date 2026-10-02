<?php
/**
 * Comprehensive Fee & Finance All Submodules Health Check
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

echo "========================================================================\n";
echo "COMPREHENSIVE FEE & FINANCE ALL SUBMODULES AUDIT & STATUS CHECK\n";
echo "========================================================================\n\n";

// 1. Fetch all menu items under Fee & Finance (parent_id = 54 or nested under 54)
$res = $mysqli->query("SELECT id, parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key FROM tbl_menu_items WHERE parent_id = 54 OR parent_id IN (SELECT id FROM tbl_menu_items WHERE parent_id = 54) ORDER BY parent_id, sort_order");

$groups = [];
$submodules = [];

while ($row = $res->fetch_assoc()) {
    if ($row['parent_id'] == 54 && $row['menu_type'] === 'group') {
        $groups[$row['id']] = $row;
    } else {
        $submodules[] = $row;
    }
}

echo "Found " . count($groups) . " Major Modules and " . count($submodules) . " Submodules.\n\n";

// List of all submodules with their routes and target view files
$expected_views = [
    'fee-finance/dashboard'       => 'application/views/pages/finance/dashboard.php',
    'fee-finance/account-groups'  => 'application/views/pages/finance/account_groups.php',
    'fee-finance/account-heads'   => 'application/views/pages/finance/account_heads.php',
    'fee-finance/custom-accounts' => 'application/views/pages/finance/custom_accounts.php',
    'finance/ledger_students'     => 'application/views/pages/finance/ledger_students.php',
    'finance/ledger_staff'        => 'application/views/pages/finance/ledger_staff.php',
    'finance/ledger_other_parties'=> 'application/views/pages/finance/ledger_other_parties.php',
    'finance/ledger_general'      => 'application/views/pages/finance/ledger_general.php',
    'finance/fee_types'           => 'application/views/pages/finance/fee_types.php',
    'finance/fee_structures'      => 'application/views/pages/finance/fee_structures.php',
    'finance/fee_assignments'     => 'application/views/pages/finance/fee_assignments.php',
    'finance/fee_collection'      => 'application/views/pages/finance/fee_collection.php',
    'finance/pending_fees'        => 'application/views/pages/finance/pending_fees.php',
    'finance/fee_receipts'        => 'application/views/pages/finance/fee_receipts.php',
    'finance/transactions_income' => 'application/views/pages/finance/transactions_income.php',
    'finance/transactions_expense'=> 'application/views/pages/finance/transactions_expense.php',
    'finance/transfers'           => 'application/views/pages/finance/transfers.php',
    'finance/journal_entries'     => 'application/views/pages/finance/journal_entries.php',
    'finance/adjustments'         => 'application/views/pages/finance/adjustments.php',
    'finance/refunds'             => 'application/views/pages/finance/refunds.php',
    'finance/expenses'            => 'application/views/pages/finance/expenses.php',
    'finance/staff_payouts'       => 'application/views/pages/finance/staff_payouts.php',
    'finance/vendor_payments'     => 'application/views/pages/finance/vendor_payments.php',
    'finance/other_expenses'      => 'application/views/pages/finance/other_expenses.php',
    'finance/cash_accounts'       => 'application/views/pages/finance/cash_accounts.php',
    'finance/bank_accounts'       => 'application/views/pages/finance/bank_accounts.php',
    'finance/reports'             => 'application/views/pages/finance/reports.php',
    'finance/student_statement'   => 'application/views/pages/finance/student_statement.php',
    'finance/staff_statement'     => 'application/views/pages/finance/staff_statement.php',
];

$all_good = true;

foreach ($submodules as $sub) {
    $parent_title = isset($groups[$sub['parent_id']]) ? $groups[$sub['parent_id']]['menu_name'] : 'Fee & Finance';
    $route = $sub['route'];
    $perm = $sub['permission_key'];
    
    // Check view file
    $view_path = isset($expected_views[$route]) ? $expected_views[$route] : null;
    $view_exists = $view_path && file_exists(__DIR__ . '/../' . $view_path);

    // Check permission exists in tbl_permissions
    $p_chk = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm}'");
    $perm_exists = ($p_chk && $p_chk->num_rows > 0);

    echo sprintf("[%s] %-22s -> %-30s | Perm: %-30s | View: %s\n",
        ($view_exists && $perm_exists) ? 'OK' : 'WARN',
        $parent_title . ' > ' . $sub['menu_name'],
        $route,
        $perm,
        $view_exists ? 'FOUND' : 'MISSING'
    );

    if (!$view_exists || !$perm_exists) {
        $all_good = false;
    }
}

echo "\n========================================================================\n";
echo "STATUS SUMMARY: " . ($all_good ? "ALL SUBMODULES HEALTHY & CONFIGURED!" : "SOME ITEMS NEED ATTENTION") . "\n";
echo "========================================================================\n";
