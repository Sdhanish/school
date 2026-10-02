<?php
/**
 * Migration: Phase 1 — Fee & Finance Navigation Restructure
 *
 * Restructures the Fee & Finance navigation menu into 4 cohesive groups + Dashboard:
 *  1. Dashboard
 *  2. STUDENT FINANCE (Fee Types, Fee Structures, Fee Assignment, Outstanding Dues, Fee Collection, Receipts, Student Ledger)
 *  3. STAFF FINANCE (Salary Setup [Soon], Salary Processing [Soon], Salary Payable [Soon], Salary Payment, Staff Ledger)
 *  4. COLLEGE FINANCE (Account Groups, Account Heads, Cash Accounts, Bank Accounts, Income, Expenses, Vendors & Payables, Transfers, Adjustments / Refunds)
 *  5. ACCOUNTING & REPORTS (Journal Entries, General Ledger, Financial Reports)
 */

$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== PHASE 1: RESTRUCTURE FEE & FINANCE NAVIGATION ===\n";

function upsertMenuItem($m, $data) {
    $key = $m->real_escape_string($data['menu_key']);
    $res = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = '$key'");
    
    $fields = [];
    foreach ($data as $col => $val) {
        if ($val === null) {
            $fields[] = "`$col` = NULL";
        } else {
            $valEsc = $m->real_escape_string($val);
            $fields[] = "`$col` = '$valEsc'";
        }
    }
    $setClause = implode(', ', $fields);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $id = $row['id'];
        $m->query("UPDATE tbl_menu_items SET $setClause WHERE id = $id");
        return $id;
    } else {
        $cols = array_keys($data);
        $colNames = implode('`, `', $cols);
        $valStrings = [];
        foreach ($data as $val) {
            if ($val === null) {
                $valStrings[] = "NULL";
            } else {
                $valStrings[] = "'" . $m->real_escape_string($val) . "'";
            }
        }
        $valClause = implode(', ', $valStrings);
        $m->query("INSERT INTO tbl_menu_items (`$colNames`) VALUES ($valClause)");
        return $m->insert_id;
    }
}

// 1. Dashboard (Direct level 2 under Fee & Finance parent_id=54)
$dashId = upsertMenuItem($m, [
    'parent_id' => 54,
    'menu_key' => 'finance_dashboard',
    'menu_name' => 'Dashboard',
    'route' => 'fee-finance/dashboard',
    'icon' => 'dashboard',
    'menu_type' => 'submenu',
    'sort_order' => 10,
    'permission_key' => 'finance.dashboard.view',
    'aliases' => 'finance_dashboard,fee-finance/dashboard,dashboard',
    'badge_text' => null,
    'is_active' => 1
]);
echo "Dashboard [ID: $dashId] OK\n";

// 2. Group: STUDENT FINANCE
$studentGroupId = upsertMenuItem($m, [
    'parent_id' => 54,
    'menu_key' => 'finance_student_group',
    'menu_name' => 'STUDENT FINANCE',
    'route' => null,
    'icon' => 'school',
    'menu_type' => 'group',
    'sort_order' => 20,
    'permission_key' => 'finance.fees.view',
    'aliases' => 'student-finance,student_finance',
    'badge_text' => null,
    'is_active' => 1
]);

$studentItems = [
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_types',
        'menu_name' => 'Fee Types',
        'route' => 'finance/fee_types',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 1,
        'permission_key' => 'finance.fees.view',
        'aliases' => 'fee-types,fee_types',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_structures',
        'menu_name' => 'Fee Structures',
        'route' => 'finance/fee_structures',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 2,
        'permission_key' => 'finance.fees.view',
        'aliases' => 'fee-structures,fee_structures',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_assignments',
        'menu_name' => 'Fee Assignment',
        'route' => 'finance/fee_assignments',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 3,
        'permission_key' => 'finance.fees.assign',
        'aliases' => 'fee-assignments,fee_assignments',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_pending',
        'menu_name' => 'Outstanding Dues',
        'route' => 'finance/pending_fees',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 4,
        'permission_key' => 'finance.fees.view',
        'aliases' => 'outstanding-dues,pending_fees,finance_pending_fees,pending-fees',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_collection',
        'menu_name' => 'Fee Collection',
        'route' => 'finance/fee_collection',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 5,
        'permission_key' => 'finance.fees.collect',
        'aliases' => 'fee-collection,fee_collection',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_fee_receipts',
        'menu_name' => 'Receipts',
        'route' => 'finance/fee_receipts',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 6,
        'permission_key' => 'finance.fees.collect',
        'aliases' => 'receipts,fee-receipts,fee_receipts,finance_receipts',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $studentGroupId,
        'menu_key' => 'finance_ledgers_student',
        'menu_name' => 'Student Ledger',
        'route' => 'finance/ledger_students',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 7,
        'permission_key' => 'finance.ledgers.view',
        'aliases' => 'student-ledger,ledger_students,student_ledger',
        'badge_text' => null,
        'is_active' => 1
    ]
];
foreach ($studentItems as $item) {
    upsertMenuItem($m, $item);
}
echo "STUDENT FINANCE [ID: $studentGroupId] OK (7 items)\n";

// 3. Group: STAFF FINANCE
$staffGroupId = upsertMenuItem($m, [
    'parent_id' => 54,
    'menu_key' => 'finance_staff_group',
    'menu_name' => 'STAFF FINANCE',
    'route' => null,
    'icon' => 'badge',
    'menu_type' => 'group',
    'sort_order' => 30,
    'permission_key' => 'finance.view',
    'aliases' => 'staff-finance,staff_finance',
    'badge_text' => null,
    'is_active' => 1
]);

$staffItems = [
    [
        'parent_id' => $staffGroupId,
        'menu_key' => 'finance_salary_setup',
        'menu_name' => 'Salary Setup',
        'route' => 'finance/salary_setup',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 1,
        'permission_key' => 'finance.view',
        'aliases' => 'salary-setup,salary_setup',
        'badge_text' => 'NEW',
        'is_active' => 1
    ],
    [
        'parent_id' => $staffGroupId,
        'menu_key' => 'finance_salary_processing',
        'menu_name' => 'Salary Processing',
        'route' => 'finance/salary_processing',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 2,
        'permission_key' => 'finance.view',
        'aliases' => 'salary-processing,salary_processing',
        'badge_text' => 'NEW',
        'is_active' => 1
    ],
    [
        'parent_id' => $staffGroupId,
        'menu_key' => 'finance_salary_payable',
        'menu_name' => 'Salary Payable',
        'route' => 'finance/salary_payable',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 3,
        'permission_key' => 'finance.view',
        'aliases' => 'salary-payable,salary_payable',
        'badge_text' => 'NEW',
        'is_active' => 1
    ],
    [
        'parent_id' => $staffGroupId,
        'menu_key' => 'finance_exp_payout',
        'menu_name' => 'Salary Payment',
        'route' => 'finance/staff_payouts',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 4,
        'permission_key' => 'finance.staff_payouts.view',
        'aliases' => 'salary-payment,salary_payment,staff_payouts,staff-payouts',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $staffGroupId,
        'menu_key' => 'finance_ledgers_staff',
        'menu_name' => 'Staff Ledger',
        'route' => 'finance/ledger_staff',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 5,
        'permission_key' => 'finance.ledgers.view',
        'aliases' => 'staff-ledger,ledger_staff,staff_ledger',
        'badge_text' => null,
        'is_active' => 1
    ]
];
foreach ($staffItems as $item) {
    upsertMenuItem($m, $item);
}
echo "STAFF FINANCE [ID: $staffGroupId] OK (5 items)\n";

// 4. Group: COLLEGE FINANCE
$collegeGroupId = upsertMenuItem($m, [
    'parent_id' => 54,
    'menu_key' => 'finance_college_group',
    'menu_name' => 'COLLEGE FINANCE',
    'route' => null,
    'icon' => 'account_balance',
    'menu_type' => 'group',
    'sort_order' => 40,
    'permission_key' => 'finance.view',
    'aliases' => 'college-finance,college_finance',
    'badge_text' => null,
    'is_active' => 1
]);

$collegeItems = [
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_coa_groups',
        'menu_name' => 'Account Groups',
        'route' => 'fee-finance/account-groups',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 1,
        'permission_key' => 'finance.account_groups.view',
        'aliases' => 'account-groups,account_groups,coa-groups',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_coa_heads',
        'menu_name' => 'Account Heads',
        'route' => 'fee-finance/account-heads',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 2,
        'permission_key' => 'finance.account_heads.view',
        'aliases' => 'account-heads,account_heads,coa-heads',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_cb_cash',
        'menu_name' => 'Cash Accounts',
        'route' => 'finance/cash_accounts',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 3,
        'permission_key' => 'finance.cash_accounts.view',
        'aliases' => 'cash-accounts,cash_accounts',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_cb_bank',
        'menu_name' => 'Bank Accounts',
        'route' => 'finance/bank_accounts',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 4,
        'permission_key' => 'finance.bank_accounts.view',
        'aliases' => 'bank-accounts,bank_accounts',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_txn_income',
        'menu_name' => 'Income',
        'route' => 'finance/transactions_income',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 5,
        'permission_key' => 'finance.transactions.view',
        'aliases' => 'income,transactions-income,transactions_income',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_exp_entry',
        'menu_name' => 'Expenses',
        'route' => 'finance/expenses',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 6,
        'permission_key' => 'finance.expenses.view',
        'aliases' => 'expenses,expense-entry,finance_expenses',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_exp_vendor',
        'menu_name' => 'Vendors & Payables',
        'route' => 'finance/vendor_payments',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 7,
        'permission_key' => 'finance.vendor_payments.view',
        'aliases' => 'vendors-payables,vendors_payables,vendor_payments,vendor-payments',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_cb_transfers',
        'menu_name' => 'Transfers',
        'route' => 'finance/transfers',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 8,
        'permission_key' => 'finance.transfers.view',
        'aliases' => 'transfers,finance_transfers,finance_txn_transfer',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $collegeGroupId,
        'menu_key' => 'finance_txn_adjustment',
        'menu_name' => 'Adjustments / Refunds',
        'route' => 'finance/adjustments',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 9,
        'permission_key' => 'finance.transactions.create',
        'aliases' => 'adjustments-refunds,adjustments_refunds,adjustments,refunds,finance_txn_refund',
        'badge_text' => null,
        'is_active' => 1
    ]
];
foreach ($collegeItems as $item) {
    upsertMenuItem($m, $item);
}
echo "COLLEGE FINANCE [ID: $collegeGroupId] OK (9 items)\n";

// 5. Group: ACCOUNTING & REPORTS
$accountingGroupId = upsertMenuItem($m, [
    'parent_id' => 54,
    'menu_key' => 'finance_accounting_group',
    'menu_name' => 'ACCOUNTING & REPORTS',
    'route' => null,
    'icon' => 'analytics',
    'menu_type' => 'group',
    'sort_order' => 50,
    'permission_key' => 'finance.reports.view',
    'aliases' => 'accounting-reports,accounting_reports',
    'badge_text' => null,
    'is_active' => 1
]);

$accountingItems = [
    [
        'parent_id' => $accountingGroupId,
        'menu_key' => 'finance_txn_journal',
        'menu_name' => 'Journal Entries',
        'route' => 'finance/journal_entries',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 1,
        'permission_key' => 'finance.transactions.create',
        'aliases' => 'journal-entries,journal_entries',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $accountingGroupId,
        'menu_key' => 'finance_ledgers_general',
        'menu_name' => 'General Ledger',
        'route' => 'finance/ledger_general',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 2,
        'permission_key' => 'finance.ledgers.view',
        'aliases' => 'general-ledger,ledger_general,general_ledger',
        'badge_text' => null,
        'is_active' => 1
    ],
    [
        'parent_id' => $accountingGroupId,
        'menu_key' => 'finance_reports',
        'menu_name' => 'Financial Reports',
        'route' => 'finance/reports',
        'icon' => null,
        'menu_type' => 'submenu',
        'sort_order' => 3,
        'permission_key' => 'finance.reports.view',
        'aliases' => 'financial-reports,reports,finance-reports,finance_reports,finance_rpt_gl',
        'badge_text' => null,
        'is_active' => 1
    ]
];
foreach ($accountingItems as $item) {
    upsertMenuItem($m, $item);
}
echo "ACCOUNTING & REPORTS [ID: $accountingGroupId] OK (3 items)\n";

// 6. Deactivate old groups and redundant submenus
$deactivateKeys = [
    'finance_coa_group',
    'finance_ledgers_group',
    'finance_fees_group',
    'finance_transactions_group',
    'finance_expenses_group',
    'finance_cash_bank_group',
    'finance_reports_group',
    'finance_coa_custom',
    'finance_exp_other',
    'finance_txn_expense',
    'finance_txn_transfer',
    'finance_txn_refund',
    'finance_ledgers_party',
    'finance_rpt_account',
    'finance_rpt_tb',
    'finance_rpt_ie',
    'finance_rpt_bs',
    'finance_rpt_cb',
    'finance_rpt_bb',
    'finance_rpt_db',
    'finance_rpt_student',
    'finance_rpt_staff',
    'finance_rpt_due',
    'finance_rpt_collection',
    'finance_rpt_expense',
    'finance_rpt_gl'
];

$keysEsc = "'" . implode("', '", $deactivateKeys) . "'";
$m->query("UPDATE tbl_menu_items SET is_active = 0 WHERE menu_key IN ($keysEsc)");
echo "Deactivated old groups and redundant submenus: OK\n";

// 7. Sync permissions into tbl_menu_permissions
$res = $m->query("SELECT id, permission_key FROM tbl_menu_items WHERE permission_key IS NOT NULL AND permission_key != ''");
while ($row = $res->fetch_assoc()) {
    $menuId = (int)$row['id'];
    $permKey = $m->real_escape_string($row['permission_key']);
    $pRes = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '$permKey'");
    if ($pRes && $pRow = $pRes->fetch_assoc()) {
        $permId = (int)$pRow['permission_id'];
        $m->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ($menuId, $permId)");
    }
}
echo "Permissions sync in tbl_menu_permissions: OK\n";

echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
