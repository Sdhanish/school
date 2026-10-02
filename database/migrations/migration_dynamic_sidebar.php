<?php
/**
 * Migration: Database-Driven Dynamic Sidebar & Multi-School Permissions
 *
 * Creates:
 *  1. tbl_menu_items
 *  2. tbl_menu_permissions
 *  3. tbl_school_menus
 *
 * Seeds all 22 modules, subgroups, and child navigation items with icons,
 * routes, aliases, badges, sort orders, and RBAC permission linkages.
 */

$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== STEP 1: CREATE TABLES ===\n";

// 1. tbl_menu_items
$m->query("
CREATE TABLE IF NOT EXISTS `tbl_menu_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT UNSIGNED NULL DEFAULT NULL,
  `menu_key` VARCHAR(100) NOT NULL UNIQUE,
  `menu_name` VARCHAR(150) NOT NULL,
  `route` VARCHAR(255) NULL DEFAULT NULL,
  `icon` VARCHAR(100) NULL DEFAULT NULL,
  `menu_type` ENUM('main', 'group', 'submenu') NOT NULL DEFAULT 'main',
  `sort_order` INT NOT NULL DEFAULT 0,
  `badge_text` VARCHAR(50) NULL DEFAULT NULL,
  `permission_key` VARCHAR(100) NULL DEFAULT NULL,
  `aliases` VARCHAR(255) NULL DEFAULT NULL,
  `is_super_admin_only` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_menu_parent` (`parent_id`),
  INDEX `idx_menu_sort` (`sort_order`),
  INDEX `idx_menu_active` (`is_active`),
  INDEX `idx_menu_perm` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "tbl_menu_items: OK\n";

// 2. tbl_menu_permissions
$m->query("
CREATE TABLE IF NOT EXISTS `tbl_menu_permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `menu_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_mp_menu` (`menu_id`),
  INDEX `idx_mp_perm` (`permission_id`),
  UNIQUE KEY `uk_menu_perm` (`menu_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "tbl_menu_permissions: OK\n";

// 3. tbl_school_menus
$m->query("
CREATE TABLE IF NOT EXISTS `tbl_school_menus` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `school_id` INT UNSIGNED NOT NULL,
  `menu_id` INT UNSIGNED NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_sm_school` (`school_id`),
  INDEX `idx_sm_menu` (`menu_id`),
  UNIQUE KEY `uk_school_menu` (`school_id`, `menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "tbl_school_menus: OK\n";

echo "\n=== STEP 2: LOAD SEED DEFINITIONS ===\n";

$navPath = __DIR__ . '/../../scratch/nav_extracted.json';
$urlPath = __DIR__ . '/../../scratch/page_urls_extracted.json';

if (!file_exists($navPath) || !file_exists($urlPath)) {
    die("Error: Extracted nav or url files missing.\n");
}

$nav = json_decode(file_get_contents($navPath), true);
$page_urls = json_decode(file_get_contents($urlPath), true);

// Fetch existing permissions for permission_id mapping
$permMap = [];
$pRes = $m->query("SELECT permission_id, permission_key FROM tbl_permissions WHERE is_deleted = 'n'");
while ($row = $pRes->fetch_assoc()) {
    $permMap[$row['permission_key']] = (int)$row['permission_id'];
}
echo "Loaded " . count($permMap) . " system permissions for mapping.\n";

// Permission mapping lookup helper
function resolve_item_permission($key, $parentModKey) {
    // Specific item overrides
    $map = [
        // Students
        'student-bulk-add'      => 'students.create',
        'student-registration'  => 'students.create',
        'student-admission'     => 'students.create',
        'student-promotion'     => 'students.promote',
        'student-promote'       => 'students.promote',
        'student-transfers'     => 'students.view',
        'student-id-cards'      => 'students.view',
        'student-documents'     => 'students.view',
        'all-students'          => 'students.view',
        'students'              => 'students.view',

        // Staff
        'staff'                 => 'staff.view',
        'teachers'              => 'staff.view',
        'non-teaching-staff'    => 'staff.view',
        'staff-documents'       => 'staff.view',
        'designations'          => 'staff.view',
        'teacher-workload'      => 'staff.view',
        'staff-attendance'      => 'attendance.view',
        'staff-leave'           => 'leave.view',

        // Academics
        'academics'             => 'academics.view',
        'academic-years'        => 'academics.view',
        'classes'               => 'academics.view',
        'subjects'              => 'academics.view',
        'academic-calendar'     => 'academics.view',

        // Attendance
        'attendance-dashboard'  => 'attendance.view',
        'attendance-mark'       => 'attendance.mark',
        'attendance-class'      => 'attendance.view',
        'attendance-period-wise'=> 'attendance.view',
        'attendance-calendar'   => 'attendance.view',
        'attendance-notifications'        => 'attendance.view',
        'attendance-notification-history' => 'attendance.view',
        'attendance-settings'   => 'attendance.edit',

        // Examinations
        'exam-dashboard'        => 'exams.view',
        'exams'                 => 'exams.create',
        'grade-management'      => 'exams.view',
        'exam-schedules'        => 'exams.view',
        'marks-entry'           => 'exams.marks_entry',
        'result-calculation'    => 'exams.publish',
        'report-cards'          => 'exams.reports',
        'exam-ranks'            => 'exams.reports',
        'progress-reports'      => 'exams.reports',

        // Fees
        'fee-dashboard'         => 'fees.view',
        'fee-categories'        => 'fees.manage_structure',
        'fee-structures'        => 'fees.manage_structure',
        'fee-assignments'       => 'fees.assign',
        'fee-discounts'         => 'fees.assign',
        'due-fees'              => 'fees.view',
        'fee-collection'        => 'fees.collect',
        'fee-receipts'          => 'fees.collect',
        'fee-reminders'         => 'fees.collect',
        'collection-reports'    => 'fees.reports',
        'finance-settings'      => 'fees.manage_structure',

        // Timetable
        'timetable-dashboard'   => 'timetable.view',
        'class-timetable'       => 'timetable.view',
        'teacher-timetable'     => 'timetable.view',
        'subject-allocation'    => 'timetable.manage',
        'timetable-builder'     => 'timetable.manage',
        'timetable-period-setup'=> 'timetable.manage',
        'free-periods'          => 'timetable.manage',
        'timetable-reports'     => 'timetable.view',

        // Homework
        'homework-dashboard'    => 'homework.view',
        'homework-create'       => 'homework.create',
        'homework-subjects'     => 'homework.view',
        'homework-submissions'  => 'homework.review',
        'homework-assignments'  => 'homework.review',
        'homework-types'        => 'homework.review',
        'homework-classes'      => 'homework.view',
        'homework-reports'      => 'homework.view',

        // Communication
        'comm-dashboard'        => 'communication.view',
        'notices'               => 'communication.view',
        'announcements'         => 'communication.view',
        'comm-templates'        => 'communication.manage_templates',
        'comm-automated'        => 'communication.automated_rules',
        'comm-sms-templates'    => 'communication.manage_templates',
        'comm-whatsapp-templates'=> 'communication.manage_templates',
        'comm-email-templates'  => 'communication.manage_templates',
        'comm-history'          => 'communication.view',
        'comm-settings'         => 'communication.automated_rules',

        // Leave
        'leave-dashboard'       => 'leave.view',
        'leave-student'         => 'leave.view',
        'leave-staff'           => 'leave.view',
        'leave-types'           => 'leave.view',
        'leave-approval'        => 'leave.approve',
        'leave-balance'         => 'leave.view',
        'leave-history'         => 'leave.view',
        'leave-reports'         => 'leave.view',

        // Transport
        'transport-dashboard'   => 'transport.view',
        'transport-vehicles'    => 'transport.view',
        'transport-drivers'     => 'transport.view',
        'transport-routes'      => 'transport.view',
        'transport-stops'       => 'transport.view',
        'transport-assignments' => 'transport.manage',
        'transport-maintenance' => 'transport.manage',
        'transport-fees'        => 'transport.view',
        'transport-reports'     => 'transport.view',

        // Certificates
        'certificates-dashboard'=> 'certificates.view',
        'certificates-bonafide' => 'certificates.view',
        'certificates-transfer' => 'certificates.view',
        'certificates-study'    => 'certificates.view',
        'certificates-conduct'  => 'certificates.view',
        'certificates-requests' => 'certificates.view',
        'certificates-generate' => 'certificates.generate',
        'certificates-templates'=> 'certificates.generate',
        'certificates-documents'=> 'certificates.verify_docs',
        'certificates-reports'  => 'certificates.view',

        // Reports
        'reports'               => 'reports.view',
        'student-reports'       => 'reports.view',
        'attendance-reports'    => 'reports.view',
        'fee-reports'           => 'reports.view',
        'exam-reports'          => 'reports.view',
        'result-reports'        => 'reports.view',
        'staff-reports'         => 'reports.view',
        'academic-reports'      => 'reports.view',
        'transport-reports'     => 'reports.view',
        'library-reports'       => 'reports.view',
        'inventory-reports'     => 'reports.view',

        // User Management
        'user-dashboard'        => 'users.view',
        'users'                 => 'users.view',
        'user-roles'            => 'users.manage_roles',
        'user-role-permissions' => 'users.manage_roles',
        'user-parents'          => 'users.view',
        'user-students'         => 'users.view',
        'user-teachers'         => 'users.view',
        'user-staff'            => 'users.view',
        'user-security-settings'=> 'users.edit',
        'user-login-activity'   => 'users.view',
        'user-audit-logs'       => 'users.manage_roles',

        // Settings
        'settings'              => 'settings.view',
        'staff-document-settings'=> 'settings.edit',
        'settings/schools'      => NULL, // Super Admin only
    ];

    if (isset($map[$key])) {
        return $map[$key];
    }

    // Default module level fallback
    $fallbackMap = [
        'dashboard'     => 'dashboard.view',
        'students'      => 'students.view',
        'staff'         => 'staff.view',
        'academics'     => 'academics.view',
        'attendance'    => 'attendance.view',
        'examinations'  => 'exams.view',
        'fees'          => 'fees.view',
        'timetable'     => 'timetable.view',
        'homework'      => 'homework.view',
        'communication' => 'communication.view',
        'leave'         => 'leave.view',
        'transport'     => 'transport.view',
        'certificates'  => 'certificates.view',
        'reports'       => 'reports.view',
        'user-management'=> 'users.view',
        'settings'      => 'settings.view',
    ];

    return $fallbackMap[$parentModKey] ?? NULL;
}

echo "\n=== STEP 3: SEED MENU ITEMS ===\n";

$m->query("SET FOREIGN_KEY_CHECKS = 0");
$m->query("TRUNCATE TABLE tbl_menu_permissions");
$m->query("TRUNCATE TABLE tbl_menu_items");
$m->query("SET FOREIGN_KEY_CHECKS = 1");

$insertedModules = 0;
$insertedGroups  = 0;
$insertedItems   = 0;
$insertedPermMap = 0;

$sortOrder = 1;

foreach ($nav as $mod) {
    $modKey    = $mod['key'];
    $modLabel  = $mod['label'];
    $modIcon   = $mod['icon'] ?? NULL;
    $modSoon   = !empty($mod['soon']) ? 'Soon' : NULL;
    $modPerm   = resolve_item_permission($modKey, $modKey);
    $isSuperAdminOnly = ($modKey === 'settings/schools') ? 1 : 0;

    // Check or insert Level 1 Module
    $route = empty($mod['groups']) ? ($page_urls[$modKey] ?? $modKey) : NULL;

    $stmt = $m->prepare("
        INSERT INTO `tbl_menu_items` 
        (`parent_id`, `menu_key`, `menu_name`, `route`, `icon`, `menu_type`, `sort_order`, `badge_text`, `permission_key`, `is_super_admin_only`, `is_active`)
        VALUES (NULL, ?, ?, ?, ?, 'main', ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE 
            `parent_id` = NULL,
            `menu_name` = VALUES(`menu_name`),
            `route` = VALUES(`route`),
            `icon` = VALUES(`icon`),
            `sort_order` = VALUES(`sort_order`),
            `badge_text` = VALUES(`badge_text`),
            `permission_key` = VALUES(`permission_key`),
            `is_super_admin_only` = VALUES(`is_super_admin_only`),
            `is_active` = VALUES(`is_active`)
    ");
    $stmt->bind_param('ssssissi', $modKey, $modLabel, $route, $modIcon, $sortOrder, $modSoon, $modPerm, $isSuperAdminOnly);
    $stmt->execute();

    // Get module ID
    $mIdRes = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = '" . $m->real_escape_string($modKey) . "'");
    $moduleId = (int)$mIdRes->fetch_assoc()['id'];
    $insertedModules++;
    $sortOrder++;

    // Map module permission
    if ($modPerm && isset($permMap[$modPerm])) {
        $pid = $permMap[$modPerm];
        $m->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ($moduleId, $pid)");
        $insertedPermMap++;
    }

    if (empty($mod['groups'])) {
        continue;
    }

    // Process Level 2 Groups and Submenus
    $groupSort = 1;
    foreach ($mod['groups'] as $grp) {
        if (!empty($grp['key'])) {
            // Direct Level 2 item (e.g. Overview, Staff Attendance, Register Schools)
            $itemKey   = $grp['key'];
            $itemLabel = $grp['label'];
            $itemAliasesArr = (array)($grp['aliases'] ?? []);

            // If child has identical key to parent module (e.g. 'students' -> 'Overview'),
            // disambiguate key so parent module is NOT overwritten or made its own child!
            if ($itemKey === $modKey) {
                $itemKey = $modKey . '-overview';
                $itemAliasesArr[] = $modKey;
            }

            $itemSoon  = !empty($grp['soon']) ? 'Soon' : NULL;
            $itemPerm  = resolve_item_permission($itemKey, $modKey);
            $itemRoute = $page_urls[$itemKey] ?? ($page_urls[$grp['key']] ?? $itemKey);
            $itemAliases = !empty($itemAliasesArr) ? implode(',', array_unique($itemAliasesArr)) : NULL;
            $itemSuperAdmin = (!empty($grp['perm']) && $grp['perm'] === 'super_admin') ? 1 : 0;

            $stmt2 = $m->prepare("
                INSERT INTO `tbl_menu_items` 
                (`parent_id`, `menu_key`, `menu_name`, `route`, `icon`, `menu_type`, `sort_order`, `badge_text`, `permission_key`, `aliases`, `is_super_admin_only`, `is_active`)
                VALUES (?, ?, ?, ?, NULL, 'submenu', ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE 
                    `parent_id` = VALUES(`parent_id`),
                    `menu_name` = VALUES(`menu_name`),
                    `route` = VALUES(`route`),
                    `sort_order` = VALUES(`sort_order`),
                    `badge_text` = VALUES(`badge_text`),
                    `permission_key` = VALUES(`permission_key`),
                    `aliases` = VALUES(`aliases`),
                    `is_super_admin_only` = VALUES(`is_super_admin_only`),
                    `is_active` = VALUES(`is_active`)
            ");
            $stmt2->bind_param('isssisssi', $moduleId, $itemKey, $itemLabel, $itemRoute, $groupSort, $itemSoon, $itemPerm, $itemAliases, $itemSuperAdmin);
            $stmt2->execute();

            $subIdRes = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = '" . $m->real_escape_string($itemKey) . "'");
            $subItemId = (int)$subIdRes->fetch_assoc()['id'];
            $insertedItems++;
            $groupSort++;

            if ($itemPerm && isset($permMap[$itemPerm])) {
                $pid = $permMap[$itemPerm];
                $m->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ($subItemId, $pid)");
                $insertedPermMap++;
            }
        } elseif (!empty($grp['items'])) {
            // Dropdown Subgroup (e.g. Admissions, Academic Setup, Attendance, Timetables)
            $groupLabel = $grp['label'];
            $groupSlug  = $modKey . '_' . preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($groupLabel));
            $groupSoon  = !empty($grp['soon']) ? 'Soon' : NULL;

            $stmtGrp = $m->prepare("
                INSERT INTO `tbl_menu_items` 
                (`parent_id`, `menu_key`, `menu_name`, `route`, `icon`, `menu_type`, `sort_order`, `badge_text`, `permission_key`, `is_super_admin_only`, `is_active`)
                VALUES (?, ?, ?, NULL, NULL, 'group', ?, ?, NULL, 0, 1)
                ON DUPLICATE KEY UPDATE 
                    `parent_id` = VALUES(`parent_id`),
                    `menu_name` = VALUES(`menu_name`),
                    `sort_order` = VALUES(`sort_order`),
                    `badge_text` = VALUES(`badge_text`),
                    `is_active` = VALUES(`is_active`)
            ");
            $stmtGrp->bind_param('issis', $moduleId, $groupSlug, $groupLabel, $groupSort, $groupSoon);
            $stmtGrp->execute();

            $grpIdRes = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = '" . $m->real_escape_string($groupSlug) . "'");
            $groupId = (int)$grpIdRes->fetch_assoc()['id'];
            $insertedGroups++;
            $groupSort++;

            // Children inside this dropdown group (Level 3)
            $childSort = 1;
            foreach ($grp['items'] as $c) {
                $cKey   = $c['key'];
                $cLabel = $c['label'];
                $cSoon  = !empty($c['soon']) ? 'Soon' : NULL;
                $cPerm  = resolve_item_permission($cKey, $modKey);
                $cRoute = $page_urls[$cKey] ?? $cKey;
                $cAliases = !empty($c['aliases']) ? implode(',', (array)$c['aliases']) : NULL;
                $cSuperAdmin = (!empty($c['perm']) && $c['perm'] === 'super_admin') ? 1 : 0;

                $stmtChild = $m->prepare("
                    INSERT INTO `tbl_menu_items` 
                    (`parent_id`, `menu_key`, `menu_name`, `route`, `icon`, `menu_type`, `sort_order`, `badge_text`, `permission_key`, `aliases`, `is_super_admin_only`, `is_active`)
                    VALUES (?, ?, ?, ?, NULL, 'submenu', ?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE 
                        `parent_id` = VALUES(`parent_id`),
                        `menu_name` = VALUES(`menu_name`),
                        `route` = VALUES(`route`),
                        `sort_order` = VALUES(`sort_order`),
                        `badge_text` = VALUES(`badge_text`),
                        `permission_key` = VALUES(`permission_key`),
                        `aliases` = VALUES(`aliases`),
                        `is_super_admin_only` = VALUES(`is_super_admin_only`),
                        `is_active` = VALUES(`is_active`)
                ");
                $stmtChild->bind_param('isssisssi', $groupId, $cKey, $cLabel, $cRoute, $childSort, $cSoon, $cPerm, $cAliases, $cSuperAdmin);
                $stmtChild->execute();

                $cIdRes = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = '" . $m->real_escape_string($cKey) . "'");
                $childItemId = (int)$cIdRes->fetch_assoc()['id'];
                $insertedItems++;
                $childSort++;

                if ($cPerm && isset($permMap[$cPerm])) {
                    $pid = $permMap[$cPerm];
                    $m->query("INSERT IGNORE INTO tbl_menu_permissions (menu_id, permission_id) VALUES ($childItemId, $pid)");
                    $insertedPermMap++;
                }
            }
        }
    }
}

echo "Seeded:\n";
echo " - Main Modules: $insertedModules\n";
echo " - Subgroups:    $insertedGroups\n";
echo " - Submenu Items:$insertedItems\n";
echo " - Menu-Permission mappings: $insertedPermMap\n";

$totalInDb = $m->query("SELECT COUNT(*) as c FROM tbl_menu_items")->fetch_assoc()['c'];
echo "\nTotal items in tbl_menu_items: $totalInDb\n";

echo "\nMigration completed successfully!\n";
