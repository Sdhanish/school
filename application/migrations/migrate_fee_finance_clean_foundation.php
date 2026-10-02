<?php
/**
 * Migration: Complete Removal of Old Fee & Finance Module & New Foundation Setup
 * 
 * Run via CLI: php application/migrations/migrate_fee_finance_clean_foundation.php
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

$mysqli->query("SET FOREIGN_KEY_CHECKS = 0;");

echo "====================================================================\n";
echo "MIGRATION: PERMANENT REMOVAL OF OLD FEE & FINANCE & NEW FOUNDATION\n";
echo "====================================================================\n\n";

// -------------------------------------------------------------------------
// STEP 1: DROP 9 LEGACY TABLES
// -------------------------------------------------------------------------
echo "1. Permanently Dropping Legacy Tables...\n";
$legacy_tables = [
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

foreach ($legacy_tables as $table) {
    $drop_sql = "DROP TABLE IF EXISTS `{$table}`";
    if ($mysqli->query($drop_sql)) {
        echo "  [DROPPED] Table `{$table}` dropped successfully.\n";
    } else {
        echo "  [ERROR] Failed to drop `{$table}`: " . $mysqli->error . "\n";
    }
}

// -------------------------------------------------------------------------
// STEP 2: REMOVE LEGACY PERMISSIONS (IDs 23-28 and old fee keys)
// -------------------------------------------------------------------------
echo "\n2. Cleaning Up Legacy Fee Permissions...\n";
$legacy_perm_keys = [
    'fees.view',
    'fees.manage_structure',
    'fees.assign',
    'fees.collect',
    'fees.refund',
    'fees.reports'
];

$in_keys = "'" . implode("','", $legacy_perm_keys) . "'";
$res = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key IN ({$in_keys}) OR permission_id IN (23,24,25,26,27,28)");
$legacy_pids = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $legacy_pids[] = (int)$r['permission_id'];
    }
}

if (!empty($legacy_pids)) {
    $pids_str = implode(',', $legacy_pids);
    $mysqli->query("DELETE FROM tbl_menu_permissions WHERE permission_id IN ({$pids_str})");
    $mysqli->query("DELETE FROM tbl_role_permissions WHERE permission_id IN ({$pids_str})");
    $mysqli->query("DELETE FROM tbl_designation_permissions WHERE permission_id IN ({$pids_str})");
    $mysqli->query("DELETE FROM tbl_permissions WHERE permission_id IN ({$pids_str})");
    echo "  [OK] Removed legacy permission IDs: {$pids_str}\n";
} else {
    echo "  [SKIP] No legacy permissions found.\n";
}

// Also remove old finance permission keys if any obsolete ones exist
$old_fin_keys = [
    'finance.view', 'finance.manage_accounts', 'finance.manage_ledgers',
    'finance.manage_expenses', 'finance.manage_cash_bank', 'finance.manage_journal', 'finance.reports'
];
// We will replace them with comprehensive granular permissions

// -------------------------------------------------------------------------
// STEP 3: REMOVE OLD FEE & FINANCE SUBMENU ITEMS
// -------------------------------------------------------------------------
echo "\n3. Cleaning Up Legacy Menu Items...\n";
// Find all submenus under 54 or old fees routes
$res_m = $mysqli->query("SELECT id FROM tbl_menu_items WHERE parent_id = 54 OR id IN (55,56,57,58,59,60,61,62,63,64,65,66,67,68,212,213,274,275,276,277,278,279) OR route LIKE 'fees%'");
$del_menu_ids = [];
if ($res_m) {
    while ($rm = $res_m->fetch_assoc()) {
        $mid = (int)$rm['id'];
        if ($mid !== 54) { // keep parent 54
            $del_menu_ids[] = $mid;
        }
    }
}

// Also find any children of these menus
if (!empty($del_menu_ids)) {
    $mids_str = implode(',', $del_menu_ids);
    $res_c = $mysqli->query("SELECT id FROM tbl_menu_items WHERE parent_id IN ({$mids_str})");
    while ($rc = $res_c->fetch_assoc()) {
        $del_menu_ids[] = (int)$rc['id'];
    }
    $del_menu_ids = array_unique($del_menu_ids);
    $all_mids_str = implode(',', $del_menu_ids);

    $mysqli->query("DELETE FROM tbl_menu_permissions WHERE menu_id IN ({$all_mids_str})");
    $mysqli->query("DELETE FROM tbl_school_menus WHERE menu_id IN ({$all_mids_str})");
    $mysqli->query("DELETE FROM tbl_menu_items WHERE id IN ({$all_mids_str})");
    echo "  [OK] Deleted legacy menu IDs: {$all_mids_str}\n";
}

// -------------------------------------------------------------------------
// STEP 4: RECONFIGURE TOP-LEVEL MENU 54 ("Fee & Finance")
// -------------------------------------------------------------------------
echo "\n4. Reconfiguring Top-Level Menu 54...\n";
$chk_54 = $mysqli->query("SELECT id FROM tbl_menu_items WHERE id = 54");
if ($chk_54 && $chk_54->num_rows > 0) {
    $mysqli->query("
        UPDATE tbl_menu_items 
        SET parent_id = NULL,
            menu_key = 'fee_finance',
            menu_name = 'Fee & Finance',
            route = NULL,
            icon = 'account_balance_wallet',
            menu_type = 'main',
            sort_order = 7,
            permission_key = 'finance.view',
            badge_text = NULL,
            is_super_admin_only = 0,
            is_soon = 0,
            is_active = 1,
            updated_at = NOW()
        WHERE id = 54
    ");
    echo "  [OK] Updated Menu 54 as top-level 'Fee & Finance'.\n";
} else {
    $mysqli->query("
        INSERT INTO tbl_menu_items (id, parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_active, created_at)
        VALUES (54, NULL, 'fee_finance', 'Fee & Finance', NULL, 'account_balance_wallet', 'main', 7, 'finance.view', 1, NOW())
    ");
    echo "  [OK] Re-created Menu 54 as top-level 'Fee & Finance'.\n";
}

// -------------------------------------------------------------------------
// STEP 5: CREATE NEW CLEAN FOUNDATIONAL FEE TABLES
// -------------------------------------------------------------------------
echo "\n5. Creating Clean New Foundational Fee Tables...\n";

// 5a. tbl_finance_fee_types
$mysqli->query("
    CREATE TABLE IF NOT EXISTS `tbl_finance_fee_types` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `school_id` int(10) unsigned NOT NULL,
        `type_name` varchar(100) NOT NULL,
        `type_code` varchar(50) NOT NULL,
        `account_id` int(10) unsigned NOT NULL COMMENT 'Income Account Head in Chart of Accounts',
        `description` text DEFAULT NULL,
        `status` tinyint(1) NOT NULL DEFAULT 1,
        `is_deleted` char(1) NOT NULL DEFAULT 'n',
        `created_by` int(10) unsigned DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_fee_types_school_del` (`school_id`, `is_deleted`),
        KEY `idx_fee_types_account` (`account_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "  [OK] Table `tbl_finance_fee_types` verified.\n";

// 5b. tbl_finance_fee_structures
$mysqli->query("
    CREATE TABLE IF NOT EXISTS `tbl_finance_fee_structures` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `school_id` int(10) unsigned NOT NULL,
        `academic_year_id` int(10) unsigned NOT NULL,
        `class_id` int(10) unsigned NOT NULL,
        `fee_type_id` int(10) unsigned NOT NULL,
        `structure_name` varchar(150) NOT NULL,
        `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
        `frequency` enum('One-Time','Annual','Term-Wise','Monthly') NOT NULL DEFAULT 'Annual',
        `due_date` date DEFAULT NULL,
        `status` tinyint(1) NOT NULL DEFAULT 1,
        `is_deleted` char(1) NOT NULL DEFAULT 'n',
        `created_by` int(10) unsigned DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_fee_struct_school_ay_class` (`school_id`, `academic_year_id`, `class_id`, `is_deleted`),
        KEY `idx_fee_struct_type` (`fee_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "  [OK] Table `tbl_finance_fee_structures` verified.\n";

// 5c. tbl_finance_fee_assignments
$mysqli->query("
    CREATE TABLE IF NOT EXISTS `tbl_finance_fee_assignments` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `school_id` int(10) unsigned NOT NULL,
        `academic_year_id` int(10) unsigned NOT NULL,
        `student_id` int(10) unsigned NOT NULL,
        `fee_structure_id` int(10) unsigned NOT NULL,
        `ledger_id` int(10) unsigned NOT NULL COMMENT 'Student sub-ledger in tbl_finance_ledgers',
        `invoice_number` varchar(50) NOT NULL,
        `invoice_date` date NOT NULL,
        `due_date` date NOT NULL,
        `assigned_amount` decimal(12,2) NOT NULL,
        `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
        `net_amount` decimal(12,2) NOT NULL,
        `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
        `due_amount` decimal(12,2) NOT NULL,
        `status` enum('Pending','Partially_Paid','Paid','Waived','Cancelled') NOT NULL DEFAULT 'Pending',
        `transaction_id` int(10) unsigned DEFAULT NULL COMMENT 'Double-entry transaction header ID',
        `remarks` text DEFAULT NULL,
        `is_deleted` char(1) NOT NULL DEFAULT 'n',
        `created_by` int(10) unsigned DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_fee_assign_inv` (`invoice_number`),
        KEY `idx_fee_assign_school_student_ay` (`school_id`, `student_id`, `academic_year_id`, `is_deleted`),
        KEY `idx_fee_assign_status` (`status`),
        KEY `idx_fee_assign_struct` (`fee_structure_id`),
        KEY `idx_fee_assign_ledger` (`ledger_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "  [OK] Table `tbl_finance_fee_assignments` verified.\n";

// 5d. tbl_finance_fee_collections
$mysqli->query("
    CREATE TABLE IF NOT EXISTS `tbl_finance_fee_collections` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `school_id` int(10) unsigned NOT NULL,
        `academic_year_id` int(10) unsigned NOT NULL,
        `student_id` int(10) unsigned NOT NULL,
        `fee_assignment_id` int(10) unsigned DEFAULT NULL,
        `ledger_id` int(10) unsigned NOT NULL,
        `receipt_number` varchar(50) NOT NULL,
        `receipt_date` date NOT NULL,
        `amount` decimal(12,2) NOT NULL,
        `payment_mode` enum('Cash','Bank Transfer','Cheque','UPI','Card','Online','Other') NOT NULL DEFAULT 'Cash',
        `deposit_account_id` int(10) unsigned NOT NULL COMMENT 'Cash or Bank account debited',
        `reference_number` varchar(100) DEFAULT NULL,
        `transaction_id` int(10) unsigned DEFAULT NULL COMMENT 'Linked double-entry transaction header ID',
        `remarks` text DEFAULT NULL,
        `status` enum('Valid','Reversed','Cancelled') NOT NULL DEFAULT 'Valid',
        `is_deleted` char(1) NOT NULL DEFAULT 'n',
        `created_by` int(10) unsigned DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_fee_coll_receipt` (`receipt_number`),
        KEY `idx_fee_coll_school_student_ay` (`school_id`, `student_id`, `academic_year_id`, `is_deleted`),
        KEY `idx_fee_coll_mode` (`payment_mode`),
        KEY `idx_fee_coll_tx` (`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "  [OK] Table `tbl_finance_fee_collections` verified.\n";

// -------------------------------------------------------------------------
// STEP 6: REGISTER NEW GRANULAR PERMISSIONS IN TBL_PERMISSIONS
// -------------------------------------------------------------------------
echo "\n6. Registering Granular Permissions for Fee & Finance...\n";

$new_permissions = [
    // Top-Level Module & Dashboard
    ['Fee & Finance', 'view',     'finance.view',              'View Fee & Finance Module',       'Access to Fee & Finance main module'],
    ['Fee & Finance', 'view',     'finance.dashboard.view',    'View Financial Dashboard',        'Access to Financial Overview and KPI metrics'],
    
    // Chart of Accounts
    ['Fee & Finance', 'view',     'finance.coa.view',          'View Chart of Accounts',          'View Account Groups, Heads, and Balances'],
    ['Fee & Finance', 'create',   'finance.coa.create',        'Create Account / Group',          'Add new account groups or ledger accounts'],
    ['Fee & Finance', 'edit',     'finance.coa.edit',          'Edit Account / Group',            'Modify existing accounts and groups'],
    ['Fee & Finance', 'delete',   'finance.coa.delete',        'Delete Account',                  'Delete non-system custom accounts'],

    // Ledgers
    ['Fee & Finance', 'view',     'finance.ledgers.view',      'View Ledgers & Statements',       'View Student, Staff, and General Ledgers'],
    ['Fee & Finance', 'print',    'finance.ledgers.print',     'Print Ledger Statements',         'Print formal ledger statements'],
    ['Fee & Finance', 'export',   'finance.ledgers.export',    'Export Ledger Data',              'Export ledger transaction histories'],

    // Fees Management
    ['Fee & Finance', 'view',     'finance.fees.view',         'View Fee Setup & Invoices',       'View fee types, structures, and student fees'],
    ['Fee & Finance', 'create',   'finance.fees.create',       'Create Fee Structure',            'Create new fee heads and class fee structures'],
    ['Fee & Finance', 'edit',     'finance.fees.edit',         'Edit Fee Structure',              'Modify fee amounts and due dates'],
    ['Fee & Finance', 'delete',   'finance.fees.delete',       'Delete Fee Structure',            'Delete uncollected fee structures'],
    ['Fee & Finance', 'assign',   'finance.fees.assign',       'Assign Fees to Students',         'Bulk or individual student fee allocation'],
    ['Fee & Finance', 'collect',  'finance.fees.collect',      'Collect Fees & Receipts',         'Record fee collection payments and issue receipts'],

    // Transactions & Journal
    ['Fee & Finance', 'view',     'finance.transactions.view', 'View Financial Transactions',     'View all posted accounting transactions'],
    ['Fee & Finance', 'create',   'finance.transactions.create','Create Journal Entries',         'Create double-entry manual journal vouchers'],
    ['Fee & Finance', 'reverse',  'finance.transactions.reverse','Reverse Transactions',          'Post compensating reversal journal vouchers'],

    // Expenses & Payouts
    ['Fee & Finance', 'view',     'finance.expenses.view',     'View Expenses & Payouts',         'View school expenditures and staff disbursements'],
    ['Fee & Finance', 'create',   'finance.expenses.create',   'Create Expense Entry',            'Record expense vouchers and staff salary payouts'],
    ['Fee & Finance', 'edit',     'finance.expenses.edit',     'Edit Expense Entry',              'Modify unposted expense details'],
    ['Fee & Finance', 'delete',   'finance.expenses.delete',   'Delete Expense Entry',            'Remove draft/unposted expense vouchers'],
    ['Fee & Finance', 'approve',  'finance.expenses.approve',  'Approve Expense Vouchers',        'Authorize pending expense payments'],

    // Cash & Bank
    ['Fee & Finance', 'view',     'finance.cash_bank.view',    'View Cash & Bank Accounts',       'Monitor cash registers and bank accounts'],
    ['Fee & Finance', 'create',   'finance.cash_bank.create',  'Add Cash / Bank Account',         'Create new bank or cash account heads'],
    ['Fee & Finance', 'transfer', 'finance.cash_bank.transfer','Transfer Funds Between Accounts', 'Execute inter-account contra fund transfers'],

    // Reports
    ['Fee & Finance', 'view',     'finance.reports.view',      'View Financial Reports',          'Access Trial Balance, P&L, Balance Sheet, Ledgers'],
    ['Fee & Finance', 'export',   'finance.reports.export',    'Export Financial Reports',        'Export statutory accounting reports to Excel/CSV'],
    ['Fee & Finance', 'print',    'finance.reports.print',     'Print Financial Reports',         'Print formal accounting statements & PDF reports']
];

$perm_id_map = [];
foreach ($new_permissions as $p) {
    $mod   = $p[0];
    $act   = $p[1];
    $key   = $p[2];
    $name  = $p[3];
    $desc  = $p[4];

    $chk = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$key}'");
    if ($chk && $chk->num_rows > 0) {
        $row = $chk->fetch_assoc();
        $pid = (int)$row['permission_id'];
        $mysqli->query("UPDATE tbl_permissions SET module = '{$mod}', action = '{$act}', permission_name = '{$name}', description = '{$desc}', is_deleted = 'n' WHERE permission_id = {$pid}");
        $perm_id_map[$key] = $pid;
    } else {
        $mysqli->query("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted) VALUES ('{$mod}', '{$act}', '{$key}', '{$name}', '{$desc}', NOW(), 'n')");
        $pid = $mysqli->insert_id;
        $perm_id_map[$key] = $pid;
    }
}
echo "  [OK] Registered " . count($perm_id_map) . " granular permissions in `tbl_permissions`.\n";

// -------------------------------------------------------------------------
// STEP 7: BUILD NEW SIDEBAR MENU HIERARCHY IN TBL_MENU_ITEMS
// -------------------------------------------------------------------------
echo "\n7. Creating Database-Driven Sidebar Menu Items under Parent 54...\n";

// Define the menu structure
$menu_structure = [
    // 1. Dashboard
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_dashboard',
        'menu_name'  => 'Dashboard',
        'route'      => 'finance/dashboard',
        'icon'       => 'dashboard',
        'menu_type'  => 'submenu',
        'sort_order' => 1,
        'perm_key'   => 'finance.dashboard.view',
        'subitems'   => []
    ],
    // 2. Chart of Accounts
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_coa_group',
        'menu_name'  => 'Chart of Accounts',
        'route'      => NULL,
        'icon'       => 'account_tree',
        'menu_type'  => 'group',
        'sort_order' => 2,
        'perm_key'   => 'finance.coa.view',
        'subitems'   => [
            ['finance_coa_groups',   'Account Groups',  'finance/accounts', 'finance.coa.view',   ['finance.coa.view', 'finance.coa.create', 'finance.coa.edit', 'finance.coa.delete']],
            ['finance_coa_heads',    'Account Heads',   'finance/accounts', 'finance.coa.view',   ['finance.coa.view', 'finance.coa.create', 'finance.coa.edit', 'finance.coa.delete']],
            ['finance_coa_custom',   'Custom Accounts', 'finance/accounts', 'finance.coa.view',   ['finance.coa.view', 'finance.coa.create', 'finance.coa.edit', 'finance.coa.delete']],
        ]
    ],
    // 3. Ledgers
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_ledgers_group',
        'menu_name'  => 'Ledgers',
        'route'      => NULL,
        'icon'       => 'menu_book',
        'menu_type'  => 'group',
        'sort_order' => 3,
        'perm_key'   => 'finance.ledgers.view',
        'subitems'   => [
            ['finance_ledgers_student', 'Student Ledgers',     'finance/ledgers', 'finance.ledgers.view', ['finance.ledgers.view', 'finance.ledgers.print', 'finance.ledgers.export']],
            ['finance_ledgers_staff',   'Staff Ledgers',       'finance/ledgers', 'finance.ledgers.view', ['finance.ledgers.view', 'finance.ledgers.print', 'finance.ledgers.export']],
            ['finance_ledgers_party',   'Other Party Ledgers', 'finance/ledgers', 'finance.ledgers.view', ['finance.ledgers.view', 'finance.ledgers.print', 'finance.ledgers.export']],
            ['finance_ledgers_general', 'General Ledgers',     'finance/ledgers', 'finance.ledgers.view', ['finance.ledgers.view', 'finance.ledgers.print', 'finance.ledgers.export']],
        ]
    ],
    // 4. Fees
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_fees_group',
        'menu_name'  => 'Fees',
        'route'      => NULL,
        'icon'       => 'payments',
        'menu_type'  => 'group',
        'sort_order' => 4,
        'perm_key'   => 'finance.fees.view',
        'subitems'   => [
            ['finance_fee_types',       'Fee Types',       'finance/fee_types',       'finance.fees.view',    ['finance.fees.view', 'finance.fees.create', 'finance.fees.edit', 'finance.fees.delete']],
            ['finance_fee_structures',  'Fee Structure',   'finance/fee_structures',  'finance.fees.view',    ['finance.fees.view', 'finance.fees.create', 'finance.fees.edit', 'finance.fees.delete']],
            ['finance_fee_assignments', 'Fee Assignment',  'finance/fee_assignments', 'finance.fees.assign',  ['finance.fees.view', 'finance.fees.assign', 'finance.fees.delete']],
            ['finance_fee_collection',  'Fee Collection',  'finance/fee_collection',  'finance.fees.collect', ['finance.fees.view', 'finance.fees.collect']],
            ['finance_fee_pending',     'Pending Fees',    'finance/pending_fees',    'finance.fees.view',    ['finance.fees.view', 'finance.fees.collect']],
            ['finance_fee_receipts',    'Fee Receipts',    'finance/fee_receipts',    'finance.fees.collect', ['finance.fees.view', 'finance.fees.collect']],
        ]
    ],
    // 5. Transactions
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_transactions_group',
        'menu_name'  => 'Transactions',
        'route'      => NULL,
        'icon'       => 'swap_horiz',
        'menu_type'  => 'group',
        'sort_order' => 5,
        'perm_key'   => 'finance.transactions.view',
        'subitems'   => [
            ['finance_txn_income',     'Income',         'finance/journal_entries', 'finance.transactions.view',    ['finance.transactions.view', 'finance.transactions.create']],
            ['finance_txn_expense',    'Expense',        'finance/expenses',        'finance.transactions.view',    ['finance.transactions.view', 'finance.transactions.create']],
            ['finance_txn_transfer',   'Transfer',       'finance/transfers',       'finance.cash_bank.transfer',   ['finance.cash_bank.view', 'finance.cash_bank.transfer']],
            ['finance_txn_journal',    'Journal Entry',  'finance/journal_entries', 'finance.transactions.create',  ['finance.transactions.view', 'finance.transactions.create', 'finance.transactions.reverse']],
            ['finance_txn_adjustment', 'Adjustment',     'finance/journal_entries', 'finance.transactions.create',  ['finance.transactions.view', 'finance.transactions.create']],
            ['finance_txn_refund',     'Refund',         'finance/journal_entries', 'finance.transactions.reverse', ['finance.transactions.view', 'finance.transactions.reverse']],
        ]
    ],
    // 6. Expenses
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_expenses_group',
        'menu_name'  => 'Expenses',
        'route'      => NULL,
        'icon'       => 'receipt',
        'menu_type'  => 'group',
        'sort_order' => 6,
        'perm_key'   => 'finance.expenses.view',
        'subitems'   => [
            ['finance_exp_entry',  'Expense Entry',   'finance/expenses',      'finance.expenses.view',    ['finance.expenses.view', 'finance.expenses.create', 'finance.expenses.edit', 'finance.expenses.delete', 'finance.expenses.approve']],
            ['finance_exp_payout', 'Staff Payout',    'finance/staff_payouts', 'finance.expenses.view',    ['finance.expenses.view', 'finance.expenses.create', 'finance.expenses.approve']],
            ['finance_exp_vendor', 'Vendor Payment',  'finance/expenses',      'finance.expenses.view',    ['finance.expenses.view', 'finance.expenses.create', 'finance.expenses.edit', 'finance.expenses.delete']],
            ['finance_exp_other',  'Other Expenses',  'finance/expenses',      'finance.expenses.view',    ['finance.expenses.view', 'finance.expenses.create', 'finance.expenses.delete']],
        ]
    ],
    // 7. Cash & Bank
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_cash_bank_group',
        'menu_name'  => 'Cash & Bank',
        'route'      => NULL,
        'icon'       => 'account_balance',
        'menu_type'  => 'group',
        'sort_order' => 7,
        'perm_key'   => 'finance.cash_bank.view',
        'subitems'   => [
            ['finance_cb_cash',      'Cash Accounts', 'finance/cash_bank', 'finance.cash_bank.view',     ['finance.cash_bank.view', 'finance.cash_bank.create']],
            ['finance_cb_bank',      'Bank Accounts', 'finance/cash_bank', 'finance.cash_bank.view',     ['finance.cash_bank.view', 'finance.cash_bank.create']],
            ['finance_cb_transfers', 'Transfers',     'finance/transfers', 'finance.cash_bank.transfer', ['finance.cash_bank.view', 'finance.cash_bank.transfer']],
        ]
    ],
    // 8. Reports
    [
        'parent_id'  => 54,
        'menu_key'   => 'finance_reports_group',
        'menu_name'  => 'Reports',
        'route'      => NULL,
        'icon'       => 'assessment',
        'menu_type'  => 'group',
        'sort_order' => 8,
        'perm_key'   => 'finance.reports.view',
        'subitems'   => [
            ['finance_rpt_gl',         'General Ledger',    'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_account',    'Account Ledger',    'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_tb',         'Trial Balance',     'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_ie',         'Income & Expense',  'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_bs',         'Balance Sheet',     'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_cb',         'Cash Book',         'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_bb',         'Bank Book',         'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_db',         'Day Book',          'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_student',    'Student Statement', 'finance/student_statement', 'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_staff',      'Staff Statement',   'finance/staff_statement',   'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_due',        'Outstanding Fees',  'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_collection', 'Fee Collection',    'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
            ['finance_rpt_expense',    'Expense Report',    'finance/reports',           'finance.reports.view', ['finance.reports.view', 'finance.reports.export', 'finance.reports.print']],
        ]
    ]
];

$sort = 1;
foreach ($menu_structure as $top_group) {
    // Check if group exists
    $chk_g = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = '{$top_group['menu_key']}'");
    $gid = 0;
    if ($chk_g && $chk_g->num_rows > 0) {
        $row_g = $chk_g->fetch_assoc();
        $gid = (int)$row_g['id'];
        $mysqli->query("UPDATE tbl_menu_items SET parent_id = 54, menu_name = '{$top_group['menu_name']}', route = " . ($top_group['route'] ? "'{$top_group['route']}'" : "NULL") . ", icon = '{$top_group['icon']}', menu_type = '{$top_group['menu_type']}', sort_order = {$sort}, permission_key = '{$top_group['perm_key']}', is_active = 1 WHERE id = {$gid}");
    } else {
        $rt_val = $top_group['route'] ? "'{$top_group['route']}'" : "NULL";
        $mysqli->query("INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_active, created_at) VALUES (54, '{$top_group['menu_key']}', '{$top_group['menu_name']}', {$rt_val}, '{$top_group['icon']}', '{$top_group['menu_type']}', {$sort}, '{$top_group['perm_key']}', 1, NOW())");
        $gid = $mysqli->insert_id;
    }

    // Link parent/group permission
    if (!empty($top_group['perm_key']) && isset($perm_id_map[$top_group['perm_key']])) {
        $pid = $perm_id_map[$top_group['perm_key']];
        $mysqli->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ({$gid}, {$pid})");
    }

    // Insert subitems
    $sub_sort = 1;
    foreach ($top_group['subitems'] as $sub) {
        $s_key   = $sub[0];
        $s_name  = $sub[1];
        $s_route = $sub[2];
        $s_perm  = $sub[3];
        $s_ops   = $sub[4] ?? [$s_perm];

        $chk_s = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = '{$s_key}'");
        $sid = 0;
        if ($chk_s && $chk_s->num_rows > 0) {
            $row_s = $chk_s->fetch_assoc();
            $sid = (int)$row_s['id'];
            $mysqli->query("UPDATE tbl_menu_items SET parent_id = {$gid}, menu_name = '{$s_name}', route = '{$s_route}', menu_type = 'submenu', sort_order = {$sub_sort}, permission_key = '{$s_perm}', is_active = 1 WHERE id = {$sid}");
        } else {
            $mysqli->query("INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_active, created_at) VALUES ({$gid}, '{$s_key}', '{$s_name}', '{$s_route}', NULL, 'submenu', {$sub_sort}, '{$s_perm}', 1, NOW())");
            $sid = $mysqli->insert_id;
        }

        // Map all operations for this submodule to tbl_menu_permissions
        foreach ($s_ops as $op_key) {
            if (isset($perm_id_map[$op_key])) {
                $op_pid = $perm_id_map[$op_key];
                $mysqli->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ({$sid}, {$op_pid})");
            }
        }
        $sub_sort++;
    }

    $sort++;
}
echo "  [OK] Successfully configured full Fee & Finance menu hierarchy under Menu 54.\n";

// Map parent 54 to finance.view
if (isset($perm_id_map['finance.view'])) {
    $mysqli->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES (54, {$perm_id_map['finance.view']})");
}

// -------------------------------------------------------------------------
// STEP 8: ASSIGN ALL NEW PERMISSIONS TO SUPER ADMIN (Role ID 1 in School 1)
// -------------------------------------------------------------------------
echo "\n8. Granting All New Permissions to Super Admin Role (Role ID: 1, School ID: 1)...\n";
foreach ($perm_id_map as $pkey => $pid) {
    $mysqli->query("INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id, school_id) VALUES (1, {$pid}, 1)");
}
echo "  [OK] Granted " . count($perm_id_map) . " permissions to Super Admin.\n";

// Also assign to existing SCHOOL_ADMIN roles if school is in 'all' permission mode
$schools_res = $mysqli->query("SELECT id, permission_mode FROM tbl_schools WHERE is_deleted = 'n'");
while ($sch = $schools_res->fetch_assoc()) {
    $sid = (int)$sch['id'];
    $pmode = $sch['permission_mode'] ?? 'all';
    if ($pmode === 'all') {
        // Find SCHOOL_ADMIN role for this school
        $r_res = $mysqli->query("SELECT role_id FROM tbl_roles WHERE school_id = {$sid} AND role_code = 'SCHOOL_ADMIN' AND is_deleted = 'n' LIMIT 1");
        if ($r_res && $r_res->num_rows > 0) {
            $r_row = $r_res->fetch_assoc();
            $admin_role_id = (int)$r_row['role_id'];
            foreach ($perm_id_map as $pkey => $pid) {
                $mysqli->query("INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id, school_id) VALUES ({$admin_role_id}, {$pid}, {$sid})");
            }
        }
    }
}
echo "  [OK] Synchronized permissions for active schools in 'all' mode.\n";

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1;");

echo "\n====================================================================\n";
echo "MIGRATION COMPLETED SUCCESSFULLY!\n";
echo "====================================================================\n";
