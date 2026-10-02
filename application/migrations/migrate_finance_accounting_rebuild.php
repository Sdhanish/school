<?php
/**
 * Migration: Complete Fee & Finance Module Rebuild
 * Tables Created:
 *  - tbl_finance_account_groups
 *  - tbl_finance_accounts
 *  - tbl_finance_ledgers
 *  - tbl_finance_transactions
 *  - tbl_finance_transaction_items
 *  - tbl_finance_expenses
 *  - tbl_finance_expense_types
 *  - tbl_finance_transfers
 *
 * Seeds:
 *  - Standard Account Groups & System Accounts for all active schools
 *  - Standard Expense Types
 *  - Backfills existing student fees & payments into double-entry accounting transactions
 *  - Adds RBAC permissions & updates menu items
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', 'C:/xampp/htdocs/schoolnew/system/');
define('APPPATH', 'C:/xampp/htdocs/schoolnew/application/');

require_once APPPATH . 'config/database.php';
$cfg = $db[$active_group];
$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database'], $cfg['port'] ?? 3306);

if ($mysqli->connect_error) {
    die("Database Connection Error: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset("utf8mb4");

echo "=======================================================\n";
echo "STARTING FINANCE & ACCOUNTING REBUILD MIGRATION\n";
echo "=======================================================\n\n";

// 1. tbl_finance_account_groups
$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_finance_account_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_code` varchar(50) NOT NULL,
  `category` enum('Asset','Liability','Equity','Income','Expense') NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fag_school` (`school_id`),
  KEY `idx_fag_category` (`category`),
  KEY `idx_fag_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql1) or die("Error creating tbl_finance_account_groups: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_account_groups ready.\n";

// 2. tbl_finance_accounts (Chart of Accounts)
$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_finance_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `account_group_id` int(10) unsigned NOT NULL,
  `account_code` varchar(50) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_type` enum('Cash','Bank','Receivable','Payable','Income','Expense','Equity') NOT NULL,
  `parent_account_id` int(10) unsigned DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(30) DEFAULT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `opening_balance_type` enum('Debit','Credit') NOT NULL DEFAULT 'Debit',
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fa_school` (`school_id`),
  KEY `idx_fa_group` (`account_group_id`),
  KEY `idx_fa_type` (`account_type`),
  KEY `idx_fa_code` (`school_id`, `account_code`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql2) or die("Error creating tbl_finance_accounts: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_accounts ready.\n";

// 3. tbl_finance_ledgers (Entity Sub-Ledgers)
$sql3 = "CREATE TABLE IF NOT EXISTS `tbl_finance_ledgers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `academic_year_id` int(10) unsigned DEFAULT NULL,
  `entity_type` enum('Student','Staff','Vendor','General') NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `parent_account_id` int(10) unsigned NOT NULL,
  `ledger_code` varchar(50) NOT NULL,
  `ledger_name` varchar(150) NOT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `opening_balance_type` enum('Debit','Credit') NOT NULL DEFAULT 'Debit',
  `opening_balance_date` date DEFAULT NULL,
  `opening_balance_reason` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fl_school` (`school_id`),
  KEY `idx_fl_entity` (`school_id`, `entity_type`, `entity_id`, `is_deleted`),
  KEY `idx_fl_parent_acc` (`parent_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql3) or die("Error creating tbl_finance_ledgers: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_ledgers ready.\n";

// 4. tbl_finance_transactions (Transaction Headers)
$sql4 = "CREATE TABLE IF NOT EXISTS `tbl_finance_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `academic_year_id` int(10) unsigned DEFAULT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('Fee_Invoice','Fee_Payment','Expense','Staff_Payout','Transfer','Journal_Entry','Adjustment','Refund','Opening_Balance') NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Posted','Reversed','Draft') NOT NULL DEFAULT 'Posted',
  `reversed_transaction_id` int(10) unsigned DEFAULT NULL,
  `reversal_reason` text DEFAULT NULL,
  `reversed_by` int(10) unsigned DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ft_tx_num` (`transaction_number`),
  KEY `idx_ft_school_date` (`school_id`, `transaction_date`),
  KEY `idx_ft_ay` (`school_id`, `academic_year_id`),
  KEY `idx_ft_ref` (`reference_type`, `reference_id`),
  KEY `idx_ft_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql4) or die("Error creating tbl_finance_transactions: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_transactions ready.\n";

// 5. tbl_finance_transaction_items (Double-Entry Posting Lines)
$sql5 = "CREATE TABLE IF NOT EXISTS `tbl_finance_transaction_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `ledger_id` int(10) unsigned DEFAULT NULL,
  `entry_type` enum('Debit','Credit') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `student_id` int(10) unsigned DEFAULT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fti_tx` (`transaction_id`),
  KEY `idx_fti_acc` (`account_id`),
  KEY `idx_fti_ledger` (`ledger_id`),
  KEY `idx_fti_school` (`school_id`),
  KEY `idx_fti_student` (`student_id`),
  KEY `idx_fti_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql5) or die("Error creating tbl_finance_transaction_items: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_transaction_items ready.\n";

// 6. tbl_finance_expenses
$sql6 = "CREATE TABLE IF NOT EXISTS `tbl_finance_expenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `academic_year_id` int(10) unsigned DEFAULT NULL,
  `expense_number` varchar(50) NOT NULL,
  `expense_date` date NOT NULL,
  `expense_type_id` int(10) unsigned NOT NULL,
  `expense_account_id` int(10) unsigned NOT NULL,
  `payment_account_id` int(10) unsigned NOT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `payout_type` enum('Salary','Advance','Bonus','Allowance','Reimbursement','None') NOT NULL DEFAULT 'None',
  `payee_name` varchar(150) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('Cash','Bank Transfer','Cheque','UPI','Card','Other') NOT NULL DEFAULT 'Cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `receipt_voucher` varchar(100) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `transaction_id` int(10) unsigned DEFAULT NULL,
  `status` enum('Paid','Reversed','Cancelled') NOT NULL DEFAULT 'Paid',
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_fe_exp_num` (`expense_number`),
  KEY `idx_fe_school_date` (`school_id`, `expense_date`),
  KEY `idx_fe_type` (`expense_type_id`),
  KEY `idx_fe_staff` (`staff_id`),
  KEY `idx_fe_tx` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql6) or die("Error creating tbl_finance_expenses: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_expenses ready.\n";

// 7. tbl_finance_expense_types
$sql7 = "CREATE TABLE IF NOT EXISTS `tbl_finance_expense_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `type_code` varchar(50) NOT NULL,
  `default_expense_account_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fet_school` (`school_id`),
  KEY `idx_fet_code` (`school_id`, `type_code`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql7) or die("Error creating tbl_finance_expense_types: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_expense_types ready.\n";

// 8. tbl_finance_transfers
$sql8 = "CREATE TABLE IF NOT EXISTS `tbl_finance_transfers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `transfer_date` date NOT NULL,
  `from_account_id` int(10) unsigned NOT NULL,
  `to_account_id` int(10) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `transaction_id` int(10) unsigned DEFAULT NULL,
  `status` enum('Completed','Reversed') NOT NULL DEFAULT 'Completed',
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ftr_num` (`transfer_number`),
  KEY `idx_ftr_school` (`school_id`),
  KEY `idx_ftr_tx` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($sql8) or die("Error creating tbl_finance_transfers: " . $mysqli->error . "\n");
echo "[OK] tbl_finance_transfers ready.\n";

echo "\n=======================================================\n";
echo "SEEDING SYSTEM ACCOUNTS & INITIAL CONFIGURATION PER SCHOOL\n";
echo "=======================================================\n";

// Get all active schools
$schools_res = $mysqli->query("SELECT id as school_id, school_name, school_code FROM tbl_schools WHERE is_deleted = 'n'");
$schools = [];
while ($row = $schools_res->fetch_assoc()) {
    $schools[] = $row;
}

foreach ($schools as $sch) {
    $sid = (int)$sch['school_id'];
    echo "\nInitializing School ID {$sid} ({$sch['school_name']}):\n";

    // 1. Account Groups
    $groups = [
        ['Assets', 'ASSET', 'Asset', 'Current and fixed assets including cash, bank, receivables'],
        ['Liabilities', 'LIAB', 'Liability', 'Obligations including payables and advances'],
        ['Equity', 'EQUITY', 'Equity', 'Capital and retained earnings'],
        ['Income', 'INC', 'Income', 'Revenue from school fees and other receipts'],
        ['Expenses', 'EXP', 'Expense', 'Operational, administrative, and payroll expenses']
    ];

    $group_ids = [];
    foreach ($groups as $g) {
        $check = $mysqli->query("SELECT id FROM tbl_finance_account_groups WHERE school_id = {$sid} AND group_code = '{$g[1]}' AND is_deleted = 'n'");
        if ($check && $check->num_rows > 0) {
            $group_ids[$g[1]] = (int)$check->fetch_assoc()['id'];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO tbl_finance_account_groups (school_id, group_name, group_code, category, description, is_system) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("issss", $sid, $g[0], $g[1], $g[2], $g[3]);
            $stmt->execute();
            $group_ids[$g[1]] = (int)$stmt->insert_id;
            $stmt->close();
        }
    }
    echo "  - Account Groups initialized.\n";

    // 2. Standard Accounts in Chart of Accounts
    $standard_accounts = [
        // Assets
        ['Cash in Hand', '1010', 'Cash', 'ASSET', 'Primary cash on hand for school operations', 'Debit', 0.00],
        ['School Bank Account', '1020', 'Bank', 'ASSET', 'Primary institutional operational bank account', 'Debit', 0.00],
        ['Student Accounts Receivable', '1030', 'Receivable', 'ASSET', 'Master control account for student fees receivable', 'Debit', 0.00],
        // Liabilities
        ['Staff Payable', '2010', 'Payable', 'LIAB', 'Master control account for staff salaries and dues', 'Credit', 0.00],
        ['Vendor Payable', '2020', 'Payable', 'LIAB', 'Master control account for supplier and vendor dues', 'Credit', 0.00],
        // Equity
        ['Retained Surplus / Capital', '3010', 'Equity', 'EQUITY', 'Accumulated school surplus and reserves', 'Credit', 0.00],
        // Income
        ['Tuition Fee Income', '4010', 'Income', 'INC', 'Revenue generated from academic tuition fees', 'Credit', 0.00],
        ['Term Fee Income', '4020', 'Income', 'INC', 'Revenue from term and semester charges', 'Credit', 0.00],
        ['Admission Fee Income', '4030', 'Income', 'INC', 'Revenue from student admissions and enrollments', 'Credit', 0.00],
        ['Examination Fee Income', '4040', 'Income', 'INC', 'Revenue from examination and assessment charges', 'Credit', 0.00],
        ['Transport Fee Income', '4050', 'Income', 'INC', 'Revenue from school bus and transportation services', 'Credit', 0.00],
        ['Other Fee Income', '4090', 'Income', 'INC', 'Miscellaneous fee receipts and charges', 'Credit', 0.00],
        // Expenses
        ['Staff Salary Expense', '5010', 'Expense', 'EXP', 'Teaching and non-teaching personnel payroll', 'Debit', 0.00],
        ['Electricity & Utilities', '5020', 'Expense', 'EXP', 'Electricity, power, and utility bills', 'Debit', 0.00],
        ['Water & Sanitation', '5030', 'Expense', 'EXP', 'Water supply, sewage, and drinking water facilities', 'Debit', 0.00],
        ['Internet & Communications', '5040', 'Expense', 'EXP', 'Broadband, telephone, SMS, and connectivity charges', 'Debit', 0.00],
        ['Stationery & Printing', '5050', 'Expense', 'EXP', 'Office supplies, exam sheets, and general stationery', 'Debit', 0.00],
        ['Repairs & Maintenance', '5060', 'Expense', 'EXP', 'Building, electrical, and campus infrastructure maintenance', 'Debit', 0.00],
        ['Events & Functions', '5070', 'Expense', 'EXP', 'Annual day, sports day, and student extracurricular activities', 'Debit', 0.00],
        ['General Operating Expenses', '5090', 'Expense', 'EXP', 'Miscellaneous school operational disbursements', 'Debit', 0.00],
    ];

    $acc_map = [];
    foreach ($standard_accounts as $acc) {
        $gid = $group_ids[$acc[3]];
        $check = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '{$acc[1]}' AND is_deleted = 'n'");
        if ($check && $check->num_rows > 0) {
            $acc_map[$acc[1]] = (int)$check->fetch_assoc()['id'];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO tbl_finance_accounts (school_id, account_group_id, account_name, account_code, account_type, description, opening_balance, opening_balance_type, is_system) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("iissssds", $sid, $gid, $acc[0], $acc[1], $acc[2], $acc[4], $acc[6], $acc[5]);
            $stmt->execute();
            $acc_map[$acc[1]] = (int)$stmt->insert_id;
            $stmt->close();
        }
    }
    echo "  - Chart of Accounts initialized (" . count($acc_map) . " system accounts).\n";

    // 3. Standard Expense Types
    $expense_types = [
        ['Staff Salary', 'SALARY', '5010', 'Monthly staff salaries and compensation'],
        ['Electricity', 'ELECTRICITY', '5020', 'Power utility disbursements'],
        ['Water', 'WATER', '5030', 'Water supply and sanitation charges'],
        ['Internet & Telecom', 'INTERNET', '5040', 'Internet connectivity, telephony, and SMS'],
        ['Stationery & Supplies', 'STATIONERY', '5050', 'Office, classroom, and exam stationery supplies'],
        ['Repairs & Maintenance', 'MAINTENANCE', '5060', 'Facility upkeep, plumbing, and structural repairs'],
        ['School Events', 'EVENTS', '5070', 'Cultural, athletic, and institutional celebrations'],
        ['General Administrative', 'GENERAL', '5090', 'Miscellaneous petty cash and administrative expenses']
    ];

    foreach ($expense_types as $et) {
        $def_acc_id = $acc_map[$et[2]] ?? null;
        $check = $mysqli->query("SELECT id FROM tbl_finance_expense_types WHERE school_id = {$sid} AND type_code = '{$et[1]}' AND is_deleted = 'n'");
        if (!$check || $check->num_rows === 0) {
            $stmt = $mysqli->prepare("INSERT INTO tbl_finance_expense_types (school_id, type_name, type_code, default_expense_account_id, description, is_system) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("issis", $sid, $et[0], $et[1], $def_acc_id, $et[3]);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo "  - Expense Types configured.\n";

    // 4. Sub-Ledgers for Existing Students
    $receivable_acc_id = $acc_map['1030'];
    $students_res = $mysqli->query("SELECT student_id, admission_number, first_name, last_name, academic_year_id FROM tbl_students WHERE school_id = {$sid} AND is_deleted = 'n'");
    $stu_count = 0;
    while ($st = $students_res->fetch_assoc()) {
        $st_id = (int)$st['student_id'];
        $st_name = trim($st['first_name'] . ' ' . $st['last_name']);
        $st_code = 'STU-' . ($st['admission_number'] ?: $st_id);
        $ay_id = $st['academic_year_id'] ? (int)$st['academic_year_id'] : null;

        $check_l = $mysqli->query("SELECT id FROM tbl_finance_ledgers WHERE school_id = {$sid} AND entity_type = 'Student' AND entity_id = {$st_id} AND is_deleted = 'n'");
        if (!$check_l || $check_l->num_rows === 0) {
            $stmt = $mysqli->prepare("INSERT INTO tbl_finance_ledgers (school_id, academic_year_id, entity_type, entity_id, parent_account_id, ledger_code, ledger_name, opening_balance, opening_balance_type) VALUES (?, ?, 'Student', ?, ?, ?, ?, 0.00, 'Debit')");
            $stmt->bind_param("iiiiss", $sid, $ay_id, $st_id, $receivable_acc_id, $st_code, $st_name);
            $stmt->execute();
            $stmt->close();
            $stu_count++;
        }
    }
    echo "  - Initialized sub-ledgers for {$stu_count} students.\n";

    // 5. Sub-Ledgers for Existing Staff
    $payable_acc_id = $acc_map['2010'];
    $staff_res = $mysqli->query("SELECT staff_id, employee_code, full_name FROM tbl_staff WHERE school_id = {$sid} AND is_deleted = 'n'");
    $staff_count = 0;
    while ($sf = $staff_res->fetch_assoc()) {
        $sf_id = (int)$sf['staff_id'];
        $sf_name = trim($sf['full_name']);
        $sf_code = 'STF-' . ($sf['employee_code'] ?: $sf_id);

        $check_sl = $mysqli->query("SELECT id FROM tbl_finance_ledgers WHERE school_id = {$sid} AND entity_type = 'Staff' AND entity_id = {$sf_id} AND is_deleted = 'n'");
        if (!$check_sl || $check_sl->num_rows === 0) {
            $stmt = $mysqli->prepare("INSERT INTO tbl_finance_ledgers (school_id, entity_type, entity_id, parent_account_id, ledger_code, ledger_name, opening_balance, opening_balance_type) VALUES (?, 'Staff', ?, ?, ?, ?, 0.00, 'Credit')");
            $stmt->bind_param("iiiss", $sid, $sf_id, $payable_acc_id, $sf_code, $sf_name);
            $stmt->execute();
            $stmt->close();
            $staff_count++;
        }
    }
    echo "  - Initialized sub-ledgers for {$staff_count} staff members.\n";
}

echo "\n=======================================================\n";
echo "BACKFILLING HISTORICAL STUDENT FEES & PAYMENTS\n";
echo "=======================================================\n";

// Map Fee Heads to appropriate Income accounts
// Term 1, Term 2 -> Term Fee Income (4020)
// Tuition -> Tuition Fee Income (4010)
// Exam -> Examination Fee Income (4040)
// Transport -> Transport Fee Income (4050)
// Default -> Other Fee Income (4090)

$assigned_fees_res = $mysqli->query("
    SELECT sf.*, fh.head_name, st.first_name, st.last_name, st.admission_number 
    FROM tbl_student_fees sf
    INNER JOIN tbl_students st ON st.student_id = sf.student_id
    LEFT JOIN tbl_fee_structures fs ON fs.fee_structure_id = sf.fee_structure_id
    LEFT JOIN tbl_fee_heads fh ON fh.fee_head_id = fs.fee_head_id
    WHERE sf.is_deleted = 'n'
");

$invoice_tx_count = 0;
while ($sfee = $assigned_fees_res->fetch_assoc()) {
    $sid = (int)$sfee['school_id'];
    $sfee_id = (int)$sfee['student_fee_id'];
    $stu_id = (int)$sfee['student_id'];
    $ay_id = $sfee['academic_year_id'] ? (int)$sfee['academic_year_id'] : null;
    $amount = (float)$sfee['final_amount'];
    if ($amount <= 0) $amount = (float)$sfee['original_amount'];

    // Check if double-entry transaction already exists for this fee invoice
    $chk_tx = $mysqli->query("SELECT id FROM tbl_finance_transactions WHERE school_id = {$sid} AND reference_type = 'tbl_student_fees' AND reference_id = {$sfee_id} AND is_deleted = 'n'");
    if ($chk_tx && $chk_tx->num_rows > 0) {
        continue;
    }

    // Get Student Ledger
    $l_res = $mysqli->query("SELECT id, parent_account_id FROM tbl_finance_ledgers WHERE school_id = {$sid} AND entity_type = 'Student' AND entity_id = {$stu_id} AND is_deleted = 'n' LIMIT 1");
    if (!$l_res || $l_res->num_rows === 0) continue;
    $l_row = $l_res->fetch_assoc();
    $ledger_id = (int)$l_row['id'];
    $receivable_acc_id = (int)$l_row['parent_account_id'];

    // Determine Income Account
    $hname = strtolower($sfee['head_name'] ?? '');
    $acc_code = '4090'; // default other fee income
    if (strpos($hname, 'tuition') !== false) {
        $acc_code = '4010';
    } elseif (strpos($hname, 'term') !== false) {
        $acc_code = '4020';
    } elseif (strpos($hname, 'admission') !== false) {
        $acc_code = '4030';
    } elseif (strpos($hname, 'exam') !== false) {
        $acc_code = '4040';
    } elseif (strpos($hname, 'transport') !== false || strpos($hname, 'bus') !== false) {
        $acc_code = '4050';
    }

    $inc_res = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '{$acc_code}' AND is_deleted = 'n' LIMIT 1");
    if (!$inc_res || $inc_res->num_rows === 0) {
        $inc_res = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_type = 'Income' AND is_deleted = 'n' LIMIT 1");
    }
    $income_acc_id = (int)$inc_res->fetch_assoc()['id'];

    $tx_num = 'INV-TXN-' . str_pad($sfee_id, 6, '0', STR_PAD_LEFT);
    $tx_date = !empty($sfee['created_at']) ? date('Y-m-d', strtotime($sfee['created_at'])) : date('Y-m-d');
    $desc = "Fee invoice for {$sfee['first_name']} {$sfee['last_name']} ({$sfee['head_name']}) - Ref #{$sfee['invoice_no']}";

    // Insert Header
    $stmt_h = $mysqli->prepare("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status) VALUES (?, ?, ?, ?, 'Fee_Invoice', 'tbl_student_fees', ?, ?, ?, 'Posted')");
    $stmt_h->bind_param("iissids", $sid, $ay_id, $tx_num, $tx_date, $sfee_id, $amount, $desc);
    $stmt_h->execute();
    $tx_id = (int)$stmt_h->insert_id;
    $stmt_h->close();

    // 1. Debit Student Receivable (Sub-ledger)
    $stmt_d = $mysqli->prepare("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES (?, ?, ?, ?, 'Debit', ?, ?, 0.00, ?, ?)");
    $stmt_d->bind_param("iiiiddsi", $tx_id, $sid, $receivable_acc_id, $ledger_id, $amount, $amount, $desc, $stu_id);
    $stmt_d->execute();
    $stmt_d->close();

    // 2. Credit Fee Income Account
    $stmt_c = $mysqli->prepare("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES (?, ?, ?, NULL, 'Credit', ?, 0.00, ?, ?, ?)");
    $stmt_c->bind_param("iiiddsi", $tx_id, $sid, $income_acc_id, $amount, $amount, $desc, $stu_id);
    $stmt_c->execute();
    $stmt_c->close();

    $invoice_tx_count++;
}
echo "[OK] Backfilled {$invoice_tx_count} fee invoices into balanced double-entry journals.\n";

// Backfill Payments
$payments_res = $mysqli->query("
    SELECT fp.*, sf.academic_year_id, st.first_name, st.last_name 
    FROM tbl_fee_payments fp
    INNER JOIN tbl_students st ON st.student_id = fp.student_id
    INNER JOIN tbl_student_fees sf ON sf.student_fee_id = fp.student_fee_id
    WHERE fp.is_deleted = 'n'
");

$payment_tx_count = 0;
while ($pmt = $payments_res->fetch_assoc()) {
    $sid = (int)$pmt['school_id'];
    $pmt_id = (int)$pmt['payment_id'];
    $stu_id = (int)$pmt['student_id'];
    $ay_id = $pmt['academic_year_id'] ? (int)$pmt['academic_year_id'] : null;
    $amount = (float)$pmt['amount_paid'];

    // Check if double-entry transaction already exists for this payment
    $chk_tx = $mysqli->query("SELECT id FROM tbl_finance_transactions WHERE school_id = {$sid} AND reference_type = 'tbl_fee_payments' AND reference_id = {$pmt_id} AND is_deleted = 'n'");
    if ($chk_tx && $chk_tx->num_rows > 0) {
        continue;
    }

    // Get Student Ledger
    $l_res = $mysqli->query("SELECT id, parent_account_id FROM tbl_finance_ledgers WHERE school_id = {$sid} AND entity_type = 'Student' AND entity_id = {$stu_id} AND is_deleted = 'n' LIMIT 1");
    if (!$l_res || $l_res->num_rows === 0) continue;
    $l_row = $l_res->fetch_assoc();
    $ledger_id = (int)$l_row['id'];
    $receivable_acc_id = (int)$l_row['parent_account_id'];

    // Determine Cash vs Bank Account
    $mode = $pmt['payment_mode'];
    $acc_code = ($mode === 'Cash') ? '1010' : '1020';
    $acc_res = $mysqli->query("SELECT id FROM tbl_finance_accounts WHERE school_id = {$sid} AND account_code = '{$acc_code}' AND is_deleted = 'n' LIMIT 1");
    $receiving_acc_id = (int)$acc_res->fetch_assoc()['id'];

    $tx_num = 'PAY-TXN-' . str_pad($pmt_id, 6, '0', STR_PAD_LEFT);
    $tx_date = !empty($pmt['payment_date']) ? $pmt['payment_date'] : date('Y-m-d');
    $desc = "Fee collection receipt {$pmt['receipt_no']} for {$pmt['first_name']} {$pmt['last_name']} ({$mode})";

    // Insert Header
    $stmt_h = $mysqli->prepare("INSERT INTO tbl_finance_transactions (school_id, academic_year_id, transaction_number, transaction_date, transaction_type, reference_type, reference_id, total_amount, description, status) VALUES (?, ?, ?, ?, 'Fee_Payment', 'tbl_fee_payments', ?, ?, ?, 'Posted')");
    $stmt_h->bind_param("iissids", $sid, $ay_id, $tx_num, $tx_date, $pmt_id, $amount, $desc);
    $stmt_h->execute();
    $tx_id = (int)$stmt_h->insert_id;
    $stmt_h->close();

    // 1. Debit Receiving Account (Cash or Bank)
    $stmt_d = $mysqli->prepare("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES (?, ?, ?, NULL, 'Debit', ?, ?, 0.00, ?, ?)");
    $stmt_d->bind_param("iiiddsi", $tx_id, $sid, $receiving_acc_id, $amount, $amount, $desc, $stu_id);
    $stmt_d->execute();
    $stmt_d->close();

    // 2. Credit Student Receivable (Sub-ledger)
    $stmt_c = $mysqli->prepare("INSERT INTO tbl_finance_transaction_items (transaction_id, school_id, account_id, ledger_id, entry_type, amount, debit, credit, description, student_id) VALUES (?, ?, ?, ?, 'Credit', ?, 0.00, ?, ?, ?)");
    $stmt_c->bind_param("iiiiddsi", $tx_id, $sid, $receivable_acc_id, $ledger_id, $amount, $amount, $desc, $stu_id);
    $stmt_c->execute();
    $stmt_c->close();

    $payment_tx_count++;
}
echo "[OK] Backfilled {$payment_tx_count} fee payments into balanced double-entry journals.\n";

echo "\n=======================================================\n";
echo "UPDATING PERMISSIONS & MENU ITEMS\n";
echo "=======================================================\n";

// Add Permissions
$new_permissions = [
    ['Finance', 'view', 'finance.view', 'View Financial Accounts', 'View financial dashboard, chart of accounts, and ledgers'],
    ['Finance', 'manage', 'finance.manage_accounts', 'Manage Chart of Accounts', 'Create, edit, and configure accounts and account groups'],
    ['Finance', 'manage', 'finance.manage_ledgers', 'Manage Ledgers', 'Manage student, staff, and general ledgers'],
    ['Finance', 'manage', 'finance.manage_expenses', 'Manage Expenses', 'Record and manage school expenses and staff payouts'],
    ['Finance', 'manage', 'finance.manage_cash_bank', 'Manage Cash & Bank', 'Manage cash/bank accounts and fund transfers'],
    ['Finance', 'manage', 'finance.manage_journal', 'Manage Journal Entries', 'Create and post manual journal entries and adjustments'],
    ['Finance', 'reports', 'finance.reports', 'View Financial Reports', 'View and export trial balance, balance sheet, and financial statements']
];

foreach ($new_permissions as $perm) {
    $chk_p = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm[2]}'");
    if (!$chk_p || $chk_p->num_rows === 0) {
        $stmt_p = $mysqli->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, is_deleted) VALUES (?, ?, ?, ?, ?, 'n')");
        $stmt_p->bind_param("sssss", $perm[0], $perm[1], $perm[2], $perm[3], $perm[4]);
        $stmt_p->execute();
        $new_pid = (int)$stmt_p->insert_id;
        $stmt_p->close();

        // Grant to Super Admin and Accountant roles across all schools
        $roles_res = $mysqli->query("SELECT role_id, school_id FROM tbl_roles WHERE role_code IN ('SUPER_ADMIN', 'ACCOUNTANT', 'PRINCIPAL', 'SCHOOL_ADMIN') AND is_deleted = 'n'");
        while ($r = $roles_res->fetch_assoc()) {
            $rid = (int)$r['role_id'];
            $rsid = (int)$r['school_id'];
            $mysqli->query("INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id, school_id) VALUES ({$rid}, {$new_pid}, {$rsid})");
        }
    }
}
echo "[OK] Finance permissions installed & granted to administrative roles.\n";

// Update Menu Structure under Fees & Finance (parent_id = 54)
// Let's ensure new submenus exist:
// 1. Overview (ID 55) -> Route: finance (or fees)
// 2. Chart of Accounts -> Route: finance/accounts
// 3. Ledgers -> Route: finance/ledgers
// 4. Fee Setup (ID 56)
// 5. Student Fees (ID 59)
// 6. Payments & Receipts (ID 63)
// 7. Expenses & Payouts -> Route: finance/expenses
// 8. Cash & Bank -> Route: finance/cash_bank
// 9. Journal Entries -> Route: finance/journal_entries
// 10. Financial Reports -> Route: finance/reports

$submenus = [
    ['finance-accounts', 'Chart of Accounts', 'finance/accounts', 'account_balance_wallet', 'finance.manage_accounts', 2],
    ['finance-ledgers', 'Ledgers & Statements', 'finance/ledgers', 'menu_book', 'finance.manage_ledgers', 3],
    ['finance-expenses', 'Expenses & Payouts', 'finance/expenses', 'receipt_long', 'finance.manage_expenses', 7],
    ['finance-cash-bank', 'Cash & Bank Accounts', 'finance/cash_bank', 'account_balance', 'finance.manage_cash_bank', 8],
    ['finance-journal', 'Journal Entries', 'finance/journal_entries', 'edit_note', 'finance.manage_journal', 9],
    ['finance-reports', 'Financial Reports', 'finance/reports', 'summarize', 'finance.reports', 10],
];

foreach ($submenus as $sm) {
    $chk_m = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = '{$sm[0]}'");
    if (!$chk_m || $chk_m->num_rows === 0) {
        $stmt_m = $mysqli->prepare("INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_active) VALUES (54, ?, ?, ?, ?, 'sub', ?, ?, 1)");
        $stmt_m->bind_param("ssssis", $sm[0], $sm[1], $sm[2], $sm[3], $sm[5], $sm[4]);
        $stmt_m->execute();
        $stmt_m->close();
    }
}
echo "[OK] Fees & Finance navigation menu items updated.\n";

echo "\n=======================================================\n";
echo "MIGRATION COMPLETED SUCCESSFULLY!\n";
echo "=======================================================\n";
