<?php
$db = mysqli_connect('localhost', 'root', '', 'db_school');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "=== 1. Updating tbl_finance_transactions schema ===\n";

// Update transaction_type enum to include Income, Expense, Transfer, Journal_Entry, Adjustment, Refund
$sql_enum = "ALTER TABLE tbl_finance_transactions MODIFY COLUMN transaction_type ENUM(
    'Income',
    'Expense',
    'Transfer',
    'Journal_Entry',
    'Adjustment',
    'Refund',
    'Fee_Invoice',
    'Fee_Payment',
    'Staff_Payout',
    'Opening_Balance'
) NOT NULL";
if (mysqli_query($db, $sql_enum)) {
    echo "[OK] transaction_type ENUM updated successfully.\n";
} else {
    echo "[FAIL] Failed to update transaction_type ENUM: " . mysqli_error($db) . "\n";
}

// Add columns if they do not exist
$columns_to_add = [
    'payment_method' => "VARCHAR(30) NULL DEFAULT NULL AFTER total_amount",
    'reference_no'   => "VARCHAR(100) NULL DEFAULT NULL AFTER payment_method",
    'party_type'     => "VARCHAR(50) NULL DEFAULT NULL AFTER reference_no",
    'party_name'     => "VARCHAR(150) NULL DEFAULT NULL AFTER party_type",
    'party_id'       => "INT(10) UNSIGNED NULL DEFAULT NULL AFTER party_name",
    'attachment'     => "VARCHAR(255) NULL DEFAULT NULL AFTER party_id"
];

foreach ($columns_to_add as $col => $col_def) {
    $res = mysqli_query($db, "SHOW COLUMNS FROM tbl_finance_transactions LIKE '$col'");
    if (mysqli_num_rows($res) === 0) {
        $add_sql = "ALTER TABLE tbl_finance_transactions ADD COLUMN $col $col_def";
        if (mysqli_query($db, $add_sql)) {
            echo "[OK] Added column $col.\n";
        } else {
            echo "[FAIL] Failed to add column $col: " . mysqli_error($db) . "\n";
        }
    } else {
        echo "[EXISTS] Column $col already exists.\n";
    }
}

echo "\n=== 2. Updating tbl_menu_items for Transactions module ===\n";
// Find parent transactions menu item
$res = mysqli_query($db, "SELECT id FROM tbl_menu_items WHERE menu_key = 'finance_transactions_group'");
$parent = mysqli_fetch_assoc($res);
$parent_id = $parent ? (int)$parent['id'] : 344;

$menu_updates = [
    ['finance_txn_income',     'Income',        'finance/transactions_income',  'payments',           1, 'finance.transactions.view'],
    ['finance_txn_expense',    'Expense',       'finance/transactions_expense', 'receipt_long',       2, 'finance.transactions.view'],
    ['finance_txn_transfer',   'Transfer',      'finance/transfers',            'swap_horiz',         3, 'finance.cash_bank.transfer'],
    ['finance_txn_journal',    'Journal Entry', 'finance/journal_entries',      'post_add',           4, 'finance.transactions.create'],
    ['finance_txn_adjustment', 'Adjustment',    'finance/adjustments',          'tune',               5, 'finance.transactions.create'],
    ['finance_txn_refund',     'Refund',        'finance/refunds',              'published_with_changes', 6, 'finance.transactions.reverse'],
];

foreach ($menu_updates as $mu) {
    $key   = $mu[0];
    $name  = $mu[1];
    $route = $mu[2];
    $icon  = $mu[3];
    $sort  = $mu[4];
    $perm  = $mu[5];

    $chk = mysqli_query($db, "SELECT id FROM tbl_menu_items WHERE menu_key = '$key'");
    if (mysqli_num_rows($chk) > 0) {
        $upd = "UPDATE tbl_menu_items SET 
                    menu_name = '$name', 
                    route = '$route', 
                    icon = '$icon', 
                    sort_order = $sort, 
                    permission_key = '$perm',
                    is_active = 1,
                    parent_id = $parent_id 
                WHERE menu_key = '$key'";
        mysqli_query($db, $upd);
        echo "[UPDATED] Menu item $key -> $route\n";
    } else {
        $ins = "INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_active)
                VALUES ($parent_id, '$key', '$name', '$route', '$icon', 'submenu', $sort, '$perm', 1)";
        mysqli_query($db, $ins);
        echo "[INSERTED] Menu item $key -> $route\n";
    }
}

echo "\n=== 3. Ensuring RBAC permissions ===\n";
$perms = [
    ['finance', 'transactions.view',   'finance.transactions.view',   'View Transactions',   'Permission to view transactions in fee & finance'],
    ['finance', 'transactions.create', 'finance.transactions.create', 'Create Transactions', 'Permission to create financial transactions, income, expense, journal entries, adjustments, refunds'],
    ['finance', 'transactions.edit',   'finance.transactions.edit',   'Edit Transactions',   'Permission to edit financial transactions'],
    ['finance', 'transactions.reverse','finance.transactions.reverse','Reverse Transactions','Permission to void or reverse financial transactions'],
];

foreach ($perms as $p) {
    $chk = mysqli_query($db, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$p[2]}'");
    if (mysqli_num_rows($chk) === 0) {
        $ins = "INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted)
                VALUES ('{$p[0]}', '{$p[1]}', '{$p[2]}', '{$p[3]}', '{$p[4]}', NOW(), 'n')";
        mysqli_query($db, $ins);
        $pid = mysqli_insert_id($db);
        echo "[INSERTED] Permission {$p[2]}\n";
    } else {
        $row = mysqli_fetch_assoc($chk);
        $pid = $row['permission_id'];
        echo "[EXISTS] Permission {$p[2]} (ID: $pid)\n";
    }

    // Grant to Super Admin role (role_id = 1) if not already
    $chk_rp = mysqli_query($db, "SELECT * FROM tbl_role_permissions WHERE role_id = 1 AND permission_id = $pid");
    if (mysqli_num_rows($chk_rp) === 0) {
        mysqli_query($db, "INSERT INTO tbl_role_permissions (role_id, permission_id, created_at) VALUES (1, $pid, NOW())");
        echo "  -> Granted to Super Admin role\n";
    }
}

echo "\n=== 4. Updating tbl_menu_permissions mappings ===\n";
foreach ($menu_updates as $mu) {
    $m_res = mysqli_query($db, "SELECT id FROM tbl_menu_items WHERE menu_key = '{$mu[0]}'");
    $p_res = mysqli_query($db, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$mu[5]}'");
    if ($m_row = mysqli_fetch_assoc($m_res)) {
        if ($p_row = mysqli_fetch_assoc($p_res)) {
            $mid = $m_row['id'];
            $pid = $p_row['permission_id'];
            $mp_chk = mysqli_query($db, "SELECT * FROM tbl_menu_permissions WHERE menu_id = $mid AND permission_id = $pid");
            if (mysqli_num_rows($mp_chk) === 0) {
                mysqli_query($db, "INSERT INTO tbl_menu_permissions (menu_id, permission_id) VALUES ($mid, $pid)");
                echo "[MAPPED] Menu $mid -> Perm $pid\n";
            }
        }
    }
}

echo "\nMigration complete!\n";
