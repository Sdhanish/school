<?php
/**
 * Migration Script: Complete Expenses Module Database Setup
 * Sets up tbl_finance_expenses columns, menu items, permissions, and role mappings
 * Run via CLI: php application/migrations/migrate_expenses_module.php
 */
define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/../system/');
define('APPPATH', dirname(__DIR__) . '/');

require_once APPPATH . 'config/database.php';
$cfg = $db[$active_group];
$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== MIGRATING EXPENSES MODULE ===\n";

// 1. Update tbl_finance_transactions.transaction_type ENUM
$mysqli->query("ALTER TABLE `tbl_finance_transactions` MODIFY `transaction_type` ENUM(
    'Income',
    'Expense',
    'Transfer',
    'Journal_Entry',
    'Adjustment',
    'Refund',
    'Fee_Invoice',
    'Fee_Payment',
    'Staff_Payout',
    'Vendor_Payment',
    'Other_Expense',
    'Opening_Balance'
) NOT NULL");
echo "1. Updated tbl_finance_transactions.transaction_type ENUM: " . ($mysqli->error ?: 'OK') . "\n";

// 2. Check and add columns to tbl_finance_expenses
$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'submodule'");
if ($col_check->num_rows == 0) {
    $mysqli->query("ALTER TABLE tbl_finance_expenses ADD COLUMN `submodule` ENUM('Expense_Entry', 'Staff_Payout', 'Vendor_Payment', 'Other_Expense') NOT NULL DEFAULT 'Expense_Entry' AFTER `academic_year_id`");
    echo "2a. Added submodule to tbl_finance_expenses\n";
} else {
    echo "2a. submodule already exists in tbl_finance_expenses\n";
}

$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'vendor_id'");
if ($col_check->num_rows == 0) {
    $mysqli->query("ALTER TABLE tbl_finance_expenses ADD COLUMN `vendor_id` INT(10) UNSIGNED NULL AFTER `staff_id`");
    echo "2b. Added vendor_id to tbl_finance_expenses\n";
} else {
    echo "2b. vendor_id already exists in tbl_finance_expenses\n";
}

$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'party_type'");
if ($col_check->num_rows == 0) {
    $mysqli->query("ALTER TABLE tbl_finance_expenses ADD COLUMN `party_type` VARCHAR(50) NULL DEFAULT 'General' AFTER `vendor_id`");
    echo "2c. Added party_type to tbl_finance_expenses\n";
} else {
    echo "2c. party_type already exists in tbl_finance_expenses\n";
}

$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_finance_expenses LIKE 'reason'");
if ($col_check->num_rows == 0) {
    $mysqli->query("ALTER TABLE tbl_finance_expenses ADD COLUMN `reason` TEXT NULL AFTER `description`");
    echo "2d. Added reason to tbl_finance_expenses\n";
} else {
    echo "2d. reason already exists in tbl_finance_expenses\n";
}

// 3. Update tbl_menu_items routes and names for Expenses Group (Parent ID: 351)
// 352: Expense Entry -> finance/expenses (or finance/expense_entry)
$mysqli->query("UPDATE tbl_menu_items SET route = 'finance/expenses', menu_name = 'Expense Entry', permission_key = 'finance.expenses.view' WHERE id = 352");
// 353: Staff Payout -> finance/staff_payouts
$mysqli->query("UPDATE tbl_menu_items SET route = 'finance/staff_payouts', menu_name = 'Staff Payout', permission_key = 'finance.staff_payouts.view' WHERE id = 353");
// 354: Vendor Payment -> finance/vendor_payments
$mysqli->query("UPDATE tbl_menu_items SET route = 'finance/vendor_payments', menu_name = 'Vendor Payment', permission_key = 'finance.vendor_payments.view' WHERE id = 354");
// 355: Other Expenses -> finance/other_expenses
$mysqli->query("UPDATE tbl_menu_items SET route = 'finance/other_expenses', menu_name = 'Other Expenses', permission_key = 'finance.other_expenses.view' WHERE id = 355");
echo "3. Updated tbl_menu_items for parent 351: " . ($mysqli->error ?: 'OK') . "\n";

// 4. Ensure permissions exist in tbl_permissions
$permissions = [
    // Expense Entry
    ['finance.expenses.view',   'Fee & Finance', 'view',   'View Expense Entries',         'View operational expense list & vouchers'],
    ['finance.expenses.create', 'Fee & Finance', 'create', 'Create Expense Entry',        'Record operational expense vouchers'],
    ['finance.expenses.edit',   'Fee & Finance', 'edit',   'Edit Expense Entry',          'Edit operational expense records'],
    ['finance.expenses.delete', 'Fee & Finance', 'delete', 'Delete / Void Expense Entry', 'Void or reverse operational expense vouchers'],
    // Staff Payout
    ['finance.staff_payouts.view',   'Fee & Finance', 'view',   'View Staff Payouts',         'View staff salaries, advances and payouts'],
    ['finance.staff_payouts.create', 'Fee & Finance', 'create', 'Create Staff Payout',        'Disburse staff salary, advance, bonus or allowance'],
    ['finance.staff_payouts.edit',   'Fee & Finance', 'edit',   'Edit Staff Payout',          'Edit staff payout disbursement records'],
    ['finance.staff_payouts.delete', 'Fee & Finance', 'delete', 'Delete / Void Staff Payout', 'Void or reverse staff payout transactions'],
    // Vendor Payment
    ['finance.vendor_payments.view',   'Fee & Finance', 'view',   'View Vendor Payments',         'View vendor and supplier payment transactions'],
    ['finance.vendor_payments.create', 'Fee & Finance', 'create', 'Create Vendor Payment',        'Record payments made to vendors and suppliers'],
    ['finance.vendor_payments.edit',   'Fee & Finance', 'edit',   'Edit Vendor Payment',          'Edit vendor payment transaction records'],
    ['finance.vendor_payments.delete', 'Fee & Finance', 'delete', 'Delete / Void Vendor Payment', 'Void or reverse vendor payment vouchers'],
    // Other Expenses
    ['finance.other_expenses.view',   'Fee & Finance', 'view',   'View Other Expenses',         'View miscellaneous and petty cash expenses'],
    ['finance.other_expenses.create', 'Fee & Finance', 'create', 'Create Other Expense',        'Record miscellaneous school expenses'],
    ['finance.other_expenses.edit',   'Fee & Finance', 'edit',   'Edit Other Expense',          'Edit miscellaneous expense vouchers'],
    ['finance.other_expenses.delete', 'Fee & Finance', 'delete', 'Delete / Void Other Expense', 'Void or reverse other expense transactions']
];

$perm_id_map = [];
foreach ($permissions as $p) {
    list($key, $mod, $action, $name, $desc) = $p;
    $chk = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$key}'");
    if ($chk->num_rows > 0) {
        $row = $chk->fetch_assoc();
        $pid = (int)$row['permission_id'];
        $perm_id_map[$key] = $pid;
        $mysqli->query("UPDATE tbl_permissions SET module = '{$mod}', action = '{$action}', permission_name = '{$name}', description = '{$desc}', is_deleted = 'n' WHERE permission_id = {$pid}");
    } else {
        $stmt = $mysqli->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted) VALUES (?, ?, ?, ?, ?, NOW(), 'n')");
        $stmt->bind_param("sssss", $mod, $action, $key, $name, $desc);
        $stmt->execute();
        $pid = (int)$stmt->insert_id;
        $perm_id_map[$key] = $pid;
    }
}
echo "4. Synchronized permissions in tbl_permissions\n";

// 5. Map permissions in tbl_menu_permissions
// 352 -> finance.expenses.*
// 353 -> finance.staff_payouts.*
// 354 -> finance.vendor_payments.*
// 355 -> finance.other_expenses.*
$menu_perm_map = [
    352 => ['finance.expenses.view', 'finance.expenses.create', 'finance.expenses.edit', 'finance.expenses.delete'],
    353 => ['finance.staff_payouts.view', 'finance.staff_payouts.create', 'finance.staff_payouts.edit', 'finance.staff_payouts.delete'],
    354 => ['finance.vendor_payments.view', 'finance.vendor_payments.create', 'finance.vendor_payments.edit', 'finance.vendor_payments.delete'],
    355 => ['finance.other_expenses.view', 'finance.other_expenses.create', 'finance.other_expenses.edit', 'finance.other_expenses.delete'],
];

foreach ($menu_perm_map as $menu_id => $keys) {
    // Clear old mappings for this menu item
    $mysqli->query("DELETE FROM tbl_menu_permissions WHERE menu_id = {$menu_id}");
    foreach ($keys as $k) {
        if (!empty($perm_id_map[$k])) {
            $pid = $perm_id_map[$k];
            $mysqli->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
        }
    }
}
echo "5. Synchronized tbl_menu_permissions for items 352, 353, 354, 355\n";

// 6. Grant newly added permissions to Super Admin (role_id = 1) across schools
$role_res = $mysqli->query("SELECT DISTINCT role_id, school_id FROM tbl_role_permissions WHERE role_id = 1");
$admin_roles = [];
while ($r = $role_res->fetch_assoc()) {
    $admin_roles[] = $r;
}
if (empty($admin_roles)) {
    $admin_roles[] = ['role_id' => 1, 'school_id' => 1];
}

foreach ($perm_id_map as $key => $pid) {
    foreach ($admin_roles as $ar) {
        $rid = (int)$ar['role_id'];
        $sid = (int)$ar['school_id'];
        $chk = $mysqli->query("SELECT id FROM tbl_role_permissions WHERE role_id = {$rid} AND permission_id = {$pid} AND school_id = {$sid}");
        if ($chk->num_rows == 0) {
            $mysqli->query("INSERT INTO tbl_role_permissions (role_id, permission_id, school_id, created_at, is_deleted) VALUES ({$rid}, {$pid}, {$sid}, NOW(), 'n')");
        }
    }
}
echo "6. Granted all expenses module permissions to admin roles in tbl_role_permissions\n";

echo "=== MIGRATION COMPLETE ===\n";
