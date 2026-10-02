<?php
/**
 * Migration: Cash & Bank Module
 *
 * Configures:
 * 1. Schema updates for tbl_finance_accounts (bank_account_type)
 * 2. Schema updates for tbl_finance_transfers (academic_year_id, description, attachment, reversal tracking)
 * 3. Menu items routes in tbl_menu_items (finance/cash_accounts, finance/bank_accounts, finance/transfers)
 * 4. RBAC Permissions registration in tbl_permissions, tbl_menu_permissions, and tbl_role_permissions
 */

define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require __DIR__ . '/../config/database.php';

$db = $db['default'];
$mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset("utf8mb4");

echo "========================================================\n";
echo "MIGRATING CASH & BANK MODULE\n";
echo "========================================================\n\n";

// 1. Check & add columns in tbl_finance_accounts
echo "1. Checking tbl_finance_accounts columns...\n";
$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_finance_accounts LIKE 'bank_account_type'");
if ($col_check && $col_check->num_rows === 0) {
    $mysqli->query("ALTER TABLE tbl_finance_accounts ADD COLUMN `bank_account_type` VARCHAR(50) NULL DEFAULT 'Savings' AFTER `bank_name`");
    echo "  [ADDED] tbl_finance_accounts.bank_account_type\n";
} else {
    echo "  [OK] tbl_finance_accounts.bank_account_type already exists\n";
}

// 2. Check & add columns in tbl_finance_transfers
echo "\n2. Checking tbl_finance_transfers columns...\n";
$transfer_cols = [
    'academic_year_id' => "ALTER TABLE tbl_finance_transfers ADD COLUMN `academic_year_id` INT(10) UNSIGNED NULL AFTER `school_id`",
    'description'      => "ALTER TABLE tbl_finance_transfers ADD COLUMN `description` TEXT NULL AFTER `reference_no`",
    'attachment'       => "ALTER TABLE tbl_finance_transfers ADD COLUMN `attachment` VARCHAR(255) NULL AFTER `notes`",
    'reversed_reason'  => "ALTER TABLE tbl_finance_transfers ADD COLUMN `reversed_reason` VARCHAR(500) NULL AFTER `status`",
    'reversed_by'      => "ALTER TABLE tbl_finance_transfers ADD COLUMN `reversed_by` INT(10) UNSIGNED NULL AFTER `reversed_reason`",
    'reversed_at'      => "ALTER TABLE tbl_finance_transfers ADD COLUMN `reversed_at` DATETIME NULL AFTER `reversed_by`",
    'updated_by'       => "ALTER TABLE tbl_finance_transfers ADD COLUMN `updated_by` INT(10) UNSIGNED NULL",
    'updated_at'       => "ALTER TABLE tbl_finance_transfers ADD COLUMN `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($transfer_cols as $col_name => $alter_sql) {
    $col_res = $mysqli->query("SHOW COLUMNS FROM tbl_finance_transfers LIKE '{$col_name}'");
    if ($col_res && $col_res->num_rows === 0) {
        $mysqli->query($alter_sql);
        echo "  [ADDED] tbl_finance_transfers.{$col_name}\n";
    } else {
        echo "  [OK] tbl_finance_transfers.{$col_name} already exists\n";
    }
}

// 3. Update routes and menu configuration in tbl_menu_items
echo "\n3. Updating menu items for Cash & Bank submodules...\n";
$menus_to_update = [
    'finance_cb_cash'      => ['route' => 'finance/cash_accounts', 'perm' => 'finance.cash_accounts.view'],
    'finance_cb_bank'      => ['route' => 'finance/bank_accounts', 'perm' => 'finance.bank_accounts.view'],
    'finance_cb_transfers' => ['route' => 'finance/transfers',     'perm' => 'finance.transfers.view']
];

foreach ($menus_to_update as $menu_key => $info) {
    $mysqli->query("UPDATE tbl_menu_items SET route = '{$info['route']}', permission_key = '{$info['perm']}' WHERE menu_key = '{$menu_key}'");
    echo "  [UPDATED] Menu {$menu_key} -> Route: {$info['route']}, Perm: {$info['perm']}\n";
}

// 4. Register RBAC Permissions in tbl_permissions
echo "\n4. Registering Cash & Bank Permissions in tbl_permissions...\n";
$permissions = [
    // Cash Accounts
    ['finance.cash_accounts.view',   'Fee & Finance', 'View Cash Accounts',             'View and monitor physical cash accounts and registers'],
    ['finance.cash_accounts.create', 'Fee & Finance', 'Create Cash Account',            'Create new cash accounts with opening balances'],
    ['finance.cash_accounts.edit',   'Fee & Finance', 'Edit Cash Account',              'Modify cash account details and opening balance settings'],
    ['finance.cash_accounts.delete', 'Fee & Finance', 'Deactivate Cash Account',        'Deactivate or remove unused cash accounts'],

    // Bank Accounts
    ['finance.bank_accounts.view',   'Fee & Finance', 'View Bank Accounts',             'View and inspect school bank accounts and branch details'],
    ['finance.bank_accounts.create', 'Fee & Finance', 'Create Bank Account',            'Add new bank account heads and credentials'],
    ['finance.bank_accounts.edit',   'Fee & Finance', 'Edit Bank Account',              'Modify bank account credentials, branch, and IFSC'],
    ['finance.bank_accounts.delete', 'Fee & Finance', 'Deactivate Bank Account',        'Deactivate or remove unused bank accounts'],

    // Transfers
    ['finance.transfers.view',       'Fee & Finance', 'View Fund Transfers',            'View inter-account contra transfers and vouchers'],
    ['finance.transfers.create',     'Fee & Finance', 'Create Fund Transfer',           'Execute inter-account contra transfers between cash/bank'],
    ['finance.transfers.edit',       'Fee & Finance', 'Edit Fund Transfer',             'Modify transfer references and narration before closing'],
    ['finance.transfers.delete',     'Fee & Finance', 'Void / Reverse Fund Transfer',   'Reverse posted inter-account transfers with audit reason'],
];

$perm_id_map = [];
foreach ($permissions as $p) {
    $key   = $p[0];
    $mod   = $p[1];
    $name  = $p[2];
    $desc  = $p[3];

    $chk = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$key}' LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
        $row = $chk->fetch_assoc();
        $perm_id_map[$key] = (int)$row['permission_id'];
        $mysqli->query("UPDATE tbl_permissions SET module = '{$mod}', permission_name = '{$name}', description = '{$desc}' WHERE permission_id = {$row['permission_id']}");
        echo "  [EXISTS] Permission: {$key} (ID: {$row['permission_id']})\n";
    } else {
        $mysqli->query("INSERT INTO tbl_permissions (module, permission_key, permission_name, description) VALUES ('{$mod}', '{$key}', '{$name}', '{$desc}')");
        $new_id = $mysqli->insert_id;
        $perm_id_map[$key] = $new_id;
        echo "  [INSERTED] Permission: {$key} (ID: {$new_id})\n";
    }
}

// 5. Link menu items in tbl_menu_permissions
echo "\n5. Linking Menu Items to CRUD Permissions in tbl_menu_permissions...\n";
$menu_perm_mappings = [
    'finance_cb_cash' => [
        'view'   => 'finance.cash_accounts.view',
        'create' => 'finance.cash_accounts.create',
        'edit'   => 'finance.cash_accounts.edit',
        'delete' => 'finance.cash_accounts.delete',
    ],
    'finance_cb_bank' => [
        'view'   => 'finance.bank_accounts.view',
        'create' => 'finance.bank_accounts.create',
        'edit'   => 'finance.bank_accounts.edit',
        'delete' => 'finance.bank_accounts.delete',
    ],
    'finance_cb_transfers' => [
        'view'   => 'finance.transfers.view',
        'create' => 'finance.transfers.create',
        'edit'   => 'finance.transfers.edit',
        'delete' => 'finance.transfers.delete',
    ],
];

foreach ($menu_perm_mappings as $menu_key => $ops) {
    $m_res = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = '{$menu_key}' LIMIT 1");
    if ($m_res && $m_row = $m_res->fetch_assoc()) {
        $menu_id = (int)$m_row['id'];
        foreach ($ops as $op_type => $pkey) {
            if (isset($perm_id_map[$pkey])) {
                $pid = $perm_id_map[$pkey];
                $chk = $mysqli->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$menu_id} AND permission_id = {$pid} LIMIT 1");
                if ($chk && $chk->num_rows === 0) {
                    $mysqli->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id) VALUES ({$menu_id}, {$pid})");
                }
            }
        }
        echo "  [MAPPED] Menu ID {$menu_id} ({$menu_key}) -> CRUD permissions configured\n";
    }
}

// 6. Grant Permissions to Admin Roles (Super Admin = 1, School Admin = 2)
echo "\n6. Granting permissions to Admin roles...\n";
$admin_roles = [1, 2];
foreach ($admin_roles as $role_id) {
    foreach ($perm_id_map as $pkey => $pid) {
        $chk = $mysqli->query("SELECT id FROM tbl_role_permissions WHERE role_id = {$role_id} AND permission_id = {$pid} LIMIT 1");
        if ($chk && $chk->num_rows === 0) {
            $mysqli->query("INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ({$role_id}, {$pid})");
        }
    }
    echo "  [GRANTED] Role {$role_id} received all Cash & Bank module permissions\n";
}

echo "\n========================================================\n";
echo "CASH & BANK MODULE MIGRATION COMPLETE!\n";
echo "========================================================\n";
