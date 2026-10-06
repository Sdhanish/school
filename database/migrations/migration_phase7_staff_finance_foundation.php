<?php
/**
 * Migration: Phase 7 — Staff Finance Foundation
 *
 * Implements the core Staff Finance foundation data model:
 *  Staff (tbl_staff) -> Salary Structure (tbl_finance_salary_structures) -> Salary Components (tbl_finance_salary_structure_items & tbl_finance_salary_components)
 */

$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== PHASE 7: STAFF FINANCE FOUNDATION MIGRATION ===\n";

// 1. Create tbl_finance_salary_components
$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_finance_salary_components` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `school_id` int(10) unsigned NOT NULL,
    `component_name` varchar(100) NOT NULL,
    `component_code` varchar(50) NOT NULL,
    `component_type` enum('Earning','Deduction') NOT NULL DEFAULT 'Earning',
    `description` varchar(255) DEFAULT NULL,
    `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
    `default_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
    `calculation_type` enum('Fixed','Percentage') NOT NULL DEFAULT 'Fixed',
    `percentage_value` decimal(5,2) DEFAULT 0.00,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    `is_deleted` char(1) NOT NULL DEFAULT 'n',
    `created_by` int(10) unsigned DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_type` (`component_type`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$m->query($sql1) or die("Error creating tbl_finance_salary_components: " . $m->error . "\n");
echo "[OK] tbl_finance_salary_components ready.\n";

// 2. Create tbl_finance_salary_structures
$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_finance_salary_structures` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `school_id` int(10) unsigned NOT NULL,
    `staff_id` int(10) unsigned NOT NULL,
    `structure_name` varchar(150) NOT NULL,
    `effective_from` date NOT NULL,
    `effective_to` date DEFAULT NULL,
    `gross_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
    `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
    `net_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
    `pay_frequency` enum('Monthly','Bi-Weekly','Weekly') NOT NULL DEFAULT 'Monthly',
    `remarks` text DEFAULT NULL,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    `is_deleted` char(1) NOT NULL DEFAULT 'n',
    `created_by` int(10) unsigned DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_school_id` (`school_id`),
    KEY `idx_staff_id` (`staff_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$m->query($sql2) or die("Error creating tbl_finance_salary_structures: " . $m->error . "\n");
echo "[OK] tbl_finance_salary_structures ready.\n";

// 3. Create tbl_finance_salary_structure_items
$sql3 = "CREATE TABLE IF NOT EXISTS `tbl_finance_salary_structure_items` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `structure_id` int(10) unsigned NOT NULL,
    `component_id` int(10) unsigned DEFAULT NULL,
    `component_name` varchar(100) NOT NULL,
    `component_type` enum('Earning','Deduction') NOT NULL DEFAULT 'Earning',
    `calculation_type` enum('Fixed','Percentage') NOT NULL DEFAULT 'Fixed',
    `percentage` decimal(5,2) DEFAULT 0.00,
    `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_structure_id` (`structure_id`),
    KEY `idx_component_id` (`component_id`),
    KEY `idx_comp_type` (`component_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$m->query($sql3) or die("Error creating tbl_finance_salary_structure_items: " . $m->error . "\n");
echo "[OK] tbl_finance_salary_structure_items ready.\n";

// 4. Seed Standard Default Salary Components for School 1
$defaultComponents = [
    // Earnings
    ['Basic Salary', 'BASIC', 'Earning', 'Core base compensation component of employee salary', 1, 0.00, 'Fixed', 0.00],
    ['House Rent Allowance (HRA)', 'HRA', 'Earning', 'Housing and rental living assistance allowance', 1, 0.00, 'Fixed', 0.00],
    ['Dearness Allowance (DA)', 'DA', 'Earning', 'Cost of living adjustment allowance', 1, 0.00, 'Fixed', 0.00],
    ['Transport Allowance', 'TA', 'Earning', 'Commute and local transportation allowance', 1, 0.00, 'Fixed', 0.00],
    ['Special Allowance', 'SPL_ALW', 'Earning', 'Discretionary educational & academic duty allowance', 1, 0.00, 'Fixed', 0.00],
    ['Medical Allowance', 'MED_ALW', 'Earning', 'Healthcare and medical expense allowance', 1, 0.00, 'Fixed', 0.00],
    // Deductions
    ['Provident Fund (PF)', 'PF', 'Deduction', 'Statutory employee retirement provident fund deduction', 0, 0.00, 'Percentage', 12.00],
    ['Professional Tax (PT)', 'PT', 'Deduction', 'State statutory employment tax deduction', 0, 200.00, 'Fixed', 0.00],
    ['Tax Deducted at Source (TDS)', 'TDS', 'Deduction', 'Income tax withholding deduction', 0, 0.00, 'Fixed', 0.00],
    ['Health Insurance / ESI', 'ESI', 'Deduction', 'Employee healthcare & state insurance contribution', 0, 0.00, 'Fixed', 0.00]
];

// Get distinct school_ids with staff
$schoolsRes = $m->query("SELECT DISTINCT school_id FROM tbl_staff WHERE is_deleted = 'n'");
$schoolIds = [1];
while ($sRow = $schoolsRes->fetch_assoc()) {
    $sid = (int)$sRow['school_id'];
    if ($sid > 0 && !in_array($sid, $schoolIds)) {
        $schoolIds[] = $sid;
    }
}

foreach ($schoolIds as $sid) {
    foreach ($defaultComponents as $c) {
        $chk = $m->query("SELECT id FROM tbl_finance_salary_components WHERE school_id = $sid AND component_code = '{$c[1]}' AND is_deleted = 'n'");
        if ($chk->num_rows == 0) {
            $stmt = $m->prepare("INSERT INTO tbl_finance_salary_components (school_id, component_name, component_code, component_type, description, is_taxable, default_amount, calculation_type, percentage_value, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param('issssidsd', $sid, $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7]);
            $stmt->execute();
        }
    }
}
echo "[OK] Default Salary Components seeded.\n";

// 5. Seed initial Salary Structure for existing staff if none exists
$staffRes = $m->query("SELECT staff_id, school_id, full_name, employee_code, salary FROM tbl_staff WHERE is_deleted = 'n' AND status = 1");
$seededCount = 0;
while ($st = $staffRes->fetch_assoc()) {
    $staffId = (int)$st['staff_id'];
    $sid = (int)$st['school_id'];
    $monthlySal = (float)$st['salary'];
    if ($monthlySal <= 0) $monthlySal = 35000.00;

    $structChk = $m->query("SELECT id FROM tbl_finance_salary_structures WHERE staff_id = $staffId AND school_id = $sid AND is_deleted = 'n'");
    if ($structChk->num_rows == 0) {
        // Break monthly salary into standard component breakdown:
        // Basic: 50%, HRA: 30%, TA: 10%, Special: 10%, PF: 12% of Basic, PT: ₹200
        $basic = round($monthlySal * 0.50, 2);
        $hra   = round($monthlySal * 0.30, 2);
        $ta    = round($monthlySal * 0.10, 2);
        $spl   = round($monthlySal - ($basic + $hra + $ta), 2);
        $gross = round($basic + $hra + $ta + $spl, 2);

        $pf    = round($basic * 0.12, 2);
        $pt    = 200.00;
        $totalDeductions = round($pf + $pt, 2);
        $netSalary = round($gross - $totalDeductions, 2);

        $structName = "Standard Pay Structure — " . $st['full_name'];
        $effDate = '2026-04-01';

        $stmt = $m->prepare("INSERT INTO tbl_finance_salary_structures (school_id, staff_id, structure_name, effective_from, gross_salary, total_deductions, net_salary, pay_frequency, remarks, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Monthly', 'Auto-initialized from staff baseline profile', 1, NOW())");
        $stmt->bind_param('iissddd', $sid, $staffId, $structName, $effDate, $gross, $totalDeductions, $netSalary);
        $stmt->execute();
        $structId = $m->insert_id;

        // Fetch component IDs
        $compMap = [];
        $cRes = $m->query("SELECT id, component_code FROM tbl_finance_salary_components WHERE school_id = $sid");
        while ($cr = $cRes->fetch_assoc()) {
            $compMap[$cr['component_code']] = (int)$cr['id'];
        }

        $items = [
            [$structId, $compMap['BASIC'] ?? null, 'Basic Salary', 'Earning', 'Fixed', 0.00, $basic, 1],
            [$structId, $compMap['HRA'] ?? null, 'House Rent Allowance (HRA)', 'Earning', 'Fixed', 0.00, $hra, 2],
            [$structId, $compMap['TA'] ?? null, 'Transport Allowance', 'Earning', 'Fixed', 0.00, $ta, 3],
            [$structId, $compMap['SPL_ALW'] ?? null, 'Special Allowance', 'Earning', 'Fixed', 0.00, $spl, 4],
            [$structId, $compMap['PF'] ?? null, 'Provident Fund (PF)', 'Deduction', 'Percentage', 12.00, $pf, 5],
            [$structId, $compMap['PT'] ?? null, 'Professional Tax (PT)', 'Deduction', 'Fixed', 0.00, $pt, 6],
        ];

        foreach ($items as $itm) {
            $stmtItem = $m->prepare("INSERT INTO tbl_finance_salary_structure_items (structure_id, component_id, component_name, component_type, calculation_type, percentage, amount, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtItem->bind_param('iisssddi', $itm[0], $itm[1], $itm[2], $itm[3], $itm[4], $itm[5], $itm[6], $itm[7]);
            $stmtItem->execute();
        }
        $seededCount++;
    }
}
echo "[OK] Initial Salary Structures initialized for $seededCount staff members.\n";

// 6. Update tbl_menu_items for finance_salary_setup to remove NEW badge if desired and confirm active
$m->query("UPDATE tbl_menu_items SET badge_text = NULL, is_active = 1 WHERE menu_key = 'finance_salary_setup'");
echo "[OK] tbl_menu_items for finance_salary_setup updated.\n";

echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
