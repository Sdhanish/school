<?php
/**
 * Migration Script: Fee & Finance Phase 1 Foundation
 * - Adds display_order to tbl_finance_account_groups
 * - Creates tbl_finance_custom_accounts
 * - Updates tbl_finance_ledgers entity_type enum to include 'Custom'
 * - Updates tbl_menu_items routes & keys for Fee & Finance Phase 1
 * - Adds granular permissions to tbl_permissions, tbl_menu_permissions, and assigns to super admin / admin roles
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('APPPATH', dirname(__DIR__, 2) . '/application/');

require_once APPPATH . 'config/database.php';
$db_config = $db['default'];

$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset("utf8mb4");

echo "=======================================================\n";
echo "FEE & FINANCE PHASE 1: DATABASE MIGRATION\n";
echo "=======================================================\n\n";

// 1. Add display_order column to tbl_finance_account_groups if it does not exist
echo "1. Checking tbl_finance_account_groups display_order...\n";
$col_check = $mysqli->query("SHOW COLUMNS FROM `tbl_finance_account_groups` LIKE 'display_order'");
if ($col_check && $col_check->num_rows == 0) {
    $mysqli->query("ALTER TABLE `tbl_finance_account_groups` ADD COLUMN `display_order` int(11) NOT NULL DEFAULT 0 AFTER `description`");
    $mysqli->query("ALTER TABLE `tbl_finance_account_groups` ADD KEY `idx_fag_display_order` (`display_order`)");
    echo "  [OK] Added display_order to tbl_finance_account_groups.\n";
} else {
    echo "  [EXISTS] display_order already present.\n";
}

// 2. Create tbl_finance_custom_accounts
echo "\n2. Creating tbl_finance_custom_accounts...\n";
$sql_custom = "CREATE TABLE IF NOT EXISTS `tbl_finance_custom_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `account_head_id` int(10) unsigned NOT NULL COMMENT 'FK to tbl_finance_accounts (Account Head)',
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_type` enum('Cash','Bank','Receivable','Payable','Other') NOT NULL DEFAULT 'Other',
  `bank_name` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `opening_balance_type` enum('Debit','Credit') NOT NULL DEFAULT 'Debit',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fca_school` (`school_id`),
  KEY `idx_fca_head` (`account_head_id`),
  KEY `idx_fca_type` (`account_type`),
  KEY `idx_fca_school_del` (`school_id`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$mysqli->query($sql_custom) or die("Error creating tbl_finance_custom_accounts: " . $mysqli->error . "\n");
echo "  [OK] tbl_finance_custom_accounts verified.\n";

// 3. Update tbl_finance_ledgers entity_type enum to include 'Custom'
echo "\n3. Checking tbl_finance_ledgers entity_type enum...\n";
$ledgers_check = $mysqli->query("SHOW COLUMNS FROM `tbl_finance_ledgers` LIKE 'entity_type'");
if ($ledgers_check && $row = $ledgers_check->fetch_assoc()) {
    if (strpos($row['Type'], "'Custom'") === false) {
        $mysqli->query("ALTER TABLE `tbl_finance_ledgers` MODIFY COLUMN `entity_type` enum('Student','Staff','Vendor','General','Custom') NOT NULL");
        echo "  [OK] Updated entity_type enum to include 'Custom'.\n";
    } else {
        echo "  [EXISTS] 'Custom' already in entity_type enum.\n";
    }
}

// 4. Update tbl_menu_items for Fee & Finance Phase 1
echo "\n4. Updating tbl_menu_items routes & hierarchy...\n";

// Update Menu 327 (Dashboard)
$mysqli->query("UPDATE `tbl_menu_items` SET `route` = 'fee-finance/dashboard', `permission_key` = 'finance.dashboard.view', `updated_at` = NOW() WHERE `id` = 327");

// Update Menu 329 (Account Groups)
$mysqli->query("UPDATE `tbl_menu_items` SET `route` = 'fee-finance/account-groups', `permission_key` = 'finance.account_groups.view', `updated_at` = NOW() WHERE `id` = 329");

// Update Menu 330 (Account Heads)
$mysqli->query("UPDATE `tbl_menu_items` SET `route` = 'fee-finance/account-heads', `permission_key` = 'finance.account_heads.view', `updated_at` = NOW() WHERE `id` = 330");

// Update Menu 331 (Custom Accounts)
$mysqli->query("UPDATE `tbl_menu_items` SET `route` = 'fee-finance/custom-accounts', `permission_key` = 'finance.custom_accounts.view', `updated_at` = NOW() WHERE `id` = 331");

echo "  [OK] tbl_menu_items routes updated.\n";

// 5. Add Granular Permissions to tbl_permissions
echo "\n5. Adding Granular Permissions to tbl_permissions...\n";

$permissions = [
    // Dashboard
    ['Fee & Finance', 'view',   'finance.dashboard.view',      'View Financial Dashboard',     'Access Fee & Finance Dashboard'],
    // Account Groups
    ['Fee & Finance', 'view',   'finance.account_groups.view',  'View Account Groups',          'View Account Groups in Chart of Accounts'],
    ['Fee & Finance', 'create', 'finance.account_groups.create','Create Account Group',         'Create new Account Groups'],
    ['Fee & Finance', 'edit',   'finance.account_groups.edit',  'Edit Account Group',           'Edit existing Account Groups'],
    ['Fee & Finance', 'delete', 'finance.account_groups.delete','Delete Account Group',         'Delete or deactivate Account Groups'],
    // Account Heads
    ['Fee & Finance', 'view',   'finance.account_heads.view',   'View Account Heads',           'View Account Heads in Chart of Accounts'],
    ['Fee & Finance', 'create', 'finance.account_heads.create', 'Create Account Head',          'Create new Account Heads'],
    ['Fee & Finance', 'edit',   'finance.account_heads.edit',   'Edit Account Head',            'Edit existing Account Heads'],
    ['Fee & Finance', 'delete', 'finance.account_heads.delete', 'Delete Account Head',          'Delete or deactivate Account Heads'],
    // Custom Accounts
    ['Fee & Finance', 'view',   'finance.custom_accounts.view', 'View Custom Accounts',         'View Custom Accounts in Chart of Accounts'],
    ['Fee & Finance', 'create', 'finance.custom_accounts.create','Create Custom Account',       'Create new school-specific Custom Accounts'],
    ['Fee & Finance', 'edit',   'finance.custom_accounts.edit', 'Edit Custom Account',         'Edit existing Custom Accounts'],
    ['Fee & Finance', 'delete', 'finance.custom_accounts.delete','Delete Custom Account',       'Delete or deactivate Custom Accounts'],
];

$stmt_check = $mysqli->prepare("SELECT permission_id FROM tbl_permissions WHERE permission_key = ?");
$stmt_insert = $mysqli->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted) VALUES (?, ?, ?, ?, ?, NOW(), 'n')");
$stmt_update = $mysqli->prepare("UPDATE tbl_permissions SET module = ?, action = ?, permission_name = ?, description = ? WHERE permission_id = ?");

$created_pids = [];
foreach ($permissions as $p) {
    $stmt_check->bind_param("s", $p[2]);
    $stmt_check->execute();
    $res = $stmt_check->get_result();
    if ($row = $res->fetch_assoc()) {
        $pid = (int)$row['permission_id'];
        $stmt_update->bind_param("ssssi", $p[0], $p[1], $p[3], $p[4], $pid);
        $stmt_update->execute();
        $created_pids[$p[2]] = $pid;
        echo "  [UPDATED] Permission {$p[2]} (ID: {$pid})\n";
    } else {
        $stmt_insert->bind_param("sssss", $p[0], $p[1], $p[2], $p[3], $p[4]);
        $stmt_insert->execute();
        $pid = (int)$mysqli->insert_id;
        $created_pids[$p[2]] = $pid;
        echo "  [INSERTED] Permission {$p[2]} (ID: {$pid})\n";
    }
}

// 6. Map Menu Permissions in tbl_menu_permissions
echo "\n6. Mapping Menu Permissions in tbl_menu_permissions...\n";

$menu_perm_mappings = [
    327 => ['finance.dashboard.view'],
    329 => ['finance.account_groups.view', 'finance.account_groups.create', 'finance.account_groups.edit', 'finance.account_groups.delete'],
    330 => ['finance.account_heads.view', 'finance.account_heads.create', 'finance.account_heads.edit', 'finance.account_heads.delete'],
    331 => ['finance.custom_accounts.view', 'finance.custom_accounts.create', 'finance.custom_accounts.edit', 'finance.custom_accounts.delete']
];

foreach ($menu_perm_mappings as $menu_id => $keys) {
    // Delete existing mappings for this menu item
    $mysqli->query("DELETE FROM tbl_menu_permissions WHERE menu_id = {$menu_id}");
    foreach ($keys as $k) {
        if (isset($created_pids[$k])) {
            $pid = $created_pids[$k];
            $mysqli->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
            echo "  [MAPPED] Menu {$menu_id} -> Permission {$k} (ID: {$pid})\n";
        }
    }
}

// 7. Grant Permissions to Admin Roles (role_id 1 = Super Admin, role_id 2 = School Admin)
echo "\n7. Granting permissions to Admin Roles in tbl_role_permissions...\n";
$role_res = $mysqli->query("SELECT role_id FROM tbl_roles WHERE role_name IN ('Super Admin', 'School Admin', 'Admin') OR role_id IN (1, 2)");
$admin_roles = [];
if ($role_res) {
    while ($r = $role_res->fetch_assoc()) {
        $admin_roles[] = (int)$r['role_id'];
    }
}
$admin_roles = array_unique($admin_roles);

foreach ($admin_roles as $rid) {
    foreach ($created_pids as $pkey => $pid) {
        $chk_rp = $mysqli->query("SELECT id FROM tbl_role_permissions WHERE role_id = {$rid} AND permission_id = {$pid}");
        if ($chk_rp && $chk_rp->num_rows == 0) {
            $mysqli->query("INSERT INTO tbl_role_permissions (role_id, permission_id, created_at) VALUES ({$rid}, {$pid}, NOW())");
        }
    }
}
echo "  [OK] Granted all new permissions to roles: " . implode(', ', $admin_roles) . "\n";

echo "\n=======================================================\n";
echo "FEE & FINANCE PHASE 1 MIGRATION COMPLETED SUCCESSFULLY!\n";
echo "=======================================================\n";
