<?php
/**
 * Database Migration: Designation-Based Multi-School RBAC & Permission Management
 *
 * 1. Enhances `tbl_designations` with designation_code and is_system columns.
 * 2. Creates `tbl_designation_permissions` table for designation-level permission mapping.
 * 3. Enhances `tbl_users` with designation_id column.
 * 4. Seeds predefined designations across all active schools.
 * 5. Migrates existing role permissions into `tbl_designation_permissions`.
 * 6. Maps users to designations and synchronizes legacy role IDs.
 * 7. Adds 'Designations & Permissions' menu item to tbl_menu_items.
 */

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

echo "=== 1. Enhancing tbl_designations Schema ===\n";
$col_check = $db->query("SHOW COLUMNS FROM tbl_designations LIKE 'designation_code'");
if ($col_check->num_rows === 0) {
    $db->query("ALTER TABLE tbl_designations ADD COLUMN designation_code VARCHAR(50) NOT NULL DEFAULT '' AFTER designation_name");
    echo "  [OK] Added designation_code column to tbl_designations\n";
} else {
    echo "  [EXISTS] designation_code already exists\n";
}

$col_check2 = $db->query("SHOW COLUMNS FROM tbl_designations LIKE 'is_system'");
if ($col_check2->num_rows === 0) {
    $db->query("ALTER TABLE tbl_designations ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 0 AFTER description");
    echo "  [OK] Added is_system column to tbl_designations\n";
} else {
    echo "  [EXISTS] is_system already exists\n";
}

// Ensure indexes
$idx_check = $db->query("SHOW INDEX FROM tbl_designations WHERE Key_name = 'idx_desig_school_code'");
if ($idx_check->num_rows === 0) {
    $db->query("ALTER TABLE tbl_designations ADD INDEX idx_desig_school_code (school_id, designation_code)");
    echo "  [OK] Added idx_desig_school_code index\n";
}

echo "\n=== 2. Creating tbl_designation_permissions Table ===\n";
$create_dp = "CREATE TABLE IF NOT EXISTS tbl_designation_permissions (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT(10) UNSIGNED NOT NULL,
    designation_id INT(10) UNSIGNED NOT NULL,
    permission_id INT(10) UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    is_deleted CHAR(1) NOT NULL DEFAULT 'n',
    UNIQUE KEY uq_sch_desig_perm (school_id, designation_id, permission_id),
    KEY idx_desig_school (school_id, designation_id),
    KEY idx_desig_perm (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->query($create_dp);
echo "  [OK] tbl_designation_permissions verified/created\n";

echo "\n=== 3. Enhancing tbl_users Schema ===\n";
$u_check = $db->query("SHOW COLUMNS FROM tbl_users LIKE 'designation_id'");
if ($u_check->num_rows === 0) {
    $db->query("ALTER TABLE tbl_users ADD COLUMN designation_id INT(10) UNSIGNED NULL DEFAULT NULL AFTER role_id");
    $db->query("ALTER TABLE tbl_users ADD INDEX idx_users_designation (school_id, designation_id)");
    echo "  [OK] Added designation_id column and index to tbl_users\n";
} else {
    echo "  [EXISTS] designation_id already exists in tbl_users\n";
}

echo "\n=== 4. Seeding Predefined Designations Per School ===\n";
$predefined_templates = [
    'SUPER_ADMIN' => [
        'name'        => 'Super Admin',
        'category'    => 'Administration',
        'description' => 'Unrestricted master access to all school modules, settings, and tenants.',
        'is_system'   => 1,
        'only_school' => 1, // Only exists in School 1 (global)
    ],
    'SCHOOL_ADMIN' => [
        'name'        => 'School Admin',
        'category'    => 'Administration',
        'description' => 'Full administrative, operational, and configuration access for this campus.',
        'is_system'   => 1,
    ],
    'PRINCIPAL' => [
        'name'        => 'Principal',
        'category'    => 'Administration',
        'description' => 'Executive academic leadership, staff oversight, and institutional reports.',
        'is_system'   => 1,
    ],
    'TEACHER' => [
        'name'        => 'Teacher',
        'category'    => 'Teaching',
        'description' => 'Classroom teaching faculty, student attendance, exams, homework.',
        'is_system'   => 1,
    ],
    'NON_TEACHING_STAFF' => [
        'name'        => 'Non-Teaching Staff',
        'category'    => 'Support',
        'description' => 'Administrative, clerical, and operational support staff.',
        'is_system'   => 1,
    ],
    'ACCOUNTANT' => [
        'name'        => 'Accountant',
        'category'    => 'Finance',
        'description' => 'Fee collection, structure configuration, receipts, and financial ledger.',
        'is_system'   => 1,
    ],
    'LIBRARIAN' => [
        'name'        => 'Librarian',
        'category'    => 'Support',
        'description' => 'Library catalog, book circulation, fines, and repository management.',
        'is_system'   => 1,
    ],
    'TRANSPORT_STAFF' => [
        'name'        => 'Transport Staff',
        'category'    => 'Support',
        'description' => 'Fleet, routes, bus stops, vehicle maintenance, and student transit allocations.',
        'is_system'   => 1,
    ],
];

// Fetch all schools
$schools = $db->query("SELECT id, school_name FROM tbl_schools WHERE is_deleted = 'n'");
$all_schools = [];
while ($s = $schools->fetch_assoc()) {
    $all_schools[] = (int)$s['id'];
}
if (empty($all_schools)) {
    $all_schools = [1];
}

$desig_id_map = []; // [school_id][code] => designation_id

foreach ($all_schools as $sch_id) {
    foreach ($predefined_templates as $code => $tpl) {
        if (!empty($tpl['only_school']) && $tpl['only_school'] !== $sch_id) {
            continue;
        }

        // Check if designation exists by code or by name in this school
        $chk = $db->query("SELECT designation_id, designation_code FROM tbl_designations WHERE school_id = {$sch_id} AND (designation_code = '{$code}' OR designation_name = '{$tpl['name']}') LIMIT 1");
        if ($row = $chk->fetch_assoc()) {
            $desig_id = (int)$row['designation_id'];
            $db->query("UPDATE tbl_designations SET 
                designation_name = '{$tpl['name']}', 
                designation_code = '{$code}', 
                category = '{$tpl['category']}', 
                description = '" . $db->real_escape_string($tpl['description']) . "', 
                is_system = {$tpl['is_system']}, 
                status = 1, 
                is_deleted = 'n', 
                updated_at = NOW() 
                WHERE designation_id = {$desig_id}");
        } else {
            $stmt = $db->prepare("INSERT INTO tbl_designations (school_id, designation_name, designation_code, category, description, is_system, status, created_at, is_deleted) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), 'n')");
            $stmt->bind_param('issssi', $sch_id, $tpl['name'], $code, $tpl['category'], $tpl['description'], $tpl['is_system']);
            $stmt->execute();
            $desig_id = (int)$stmt->insert_id;
        }
        $desig_id_map[$sch_id][$code] = $desig_id;
    }
}
echo "  [OK] Predefined designations seeded for " . count($all_schools) . " school(s)\n";

echo "\n=== 5. Migrating Role Permissions into tbl_designation_permissions ===\n";
// Map role_code to designation_code
$role_to_desig_code = [
    'SUPER_ADMIN'    => 'SUPER_ADMIN',
    'SCHOOL_ADMIN'   => 'SCHOOL_ADMIN',
    'PRINCIPAL'      => 'PRINCIPAL',
    'TEACHER'        => 'TEACHER',
    'ACCOUNTANT'     => 'ACCOUNTANT',
    'LIBRARIAN'      => 'LIBRARIAN',
    'TRANSPORT_MGR'  => 'TRANSPORT_STAFF',
    'RECEPTIONIST'   => 'NON_TEACHING_STAFF',
];

$all_roles = $db->query("SELECT role_id, role_code, role_name, school_id FROM tbl_roles WHERE is_deleted = 'n'")->fetch_all(MYSQLI_ASSOC);
$migrated_perms_count = 0;

foreach ($all_roles as $r) {
    $rId = (int)$r['role_id'];
    $schId = (int)$r['school_id'];
    $rCode = strtoupper(trim($r['role_code']));

    $dCode = $role_to_desig_code[$rCode] ?? null;
    if (!$dCode && isset($desig_id_map[$schId])) {
        // Fallback: match by name
        foreach ($predefined_templates as $pk => $pv) {
            if (strcasecmp($pv['name'], $r['role_name']) === 0) {
                $dCode = $pk;
                break;
            }
        }
    }

    if ($dCode && isset($desig_id_map[$schId][$dCode])) {
        $desig_id = $desig_id_map[$schId][$dCode];

        // Fetch permissions for this role
        $rp_res = $db->query("SELECT permission_id FROM tbl_role_permissions WHERE role_id = {$rId} AND is_deleted = 'n'");
        while ($rp = $rp_res->fetch_assoc()) {
            $pid = (int)$rp['permission_id'];
            $chk_dp = $db->query("SELECT id FROM tbl_designation_permissions WHERE school_id = {$schId} AND designation_id = {$desig_id} AND permission_id = {$pid}");
            if ($chk_dp->num_rows === 0) {
                $db->query("INSERT INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted) VALUES ({$schId}, {$desig_id}, {$pid}, NOW(), 'n')");
                $migrated_perms_count++;
            }
        }
    }
}
echo "  [OK] Migrated/linked {$migrated_perms_count} permission entries to tbl_designation_permissions\n";

// Ensure School Admin in all schools has all active permissions if not populated
$all_active_perm_ids = [];
$p_res = $db->query("SELECT permission_id FROM tbl_permissions WHERE is_deleted = 'n'");
while ($pr = $p_res->fetch_assoc()) {
    $all_active_perm_ids[] = (int)$pr['permission_id'];
}

foreach ($all_schools as $sch_id) {
    if (isset($desig_id_map[$sch_id]['SCHOOL_ADMIN'])) {
        $sa_desig_id = $desig_id_map[$sch_id]['SCHOOL_ADMIN'];
        $existing_count = (int)$db->query("SELECT COUNT(*) FROM tbl_designation_permissions WHERE school_id = {$sch_id} AND designation_id = {$sa_desig_id}")->fetch_row()[0];
        if ($existing_count === 0) {
            foreach ($all_active_perm_ids as $pid) {
                $db->query("INSERT IGNORE INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted) VALUES ({$sch_id}, {$sa_desig_id}, {$pid}, NOW(), 'n')");
            }
            echo "  [GRANTED] School {$sch_id} School Admin initialized with all " . count($all_active_perm_ids) . " permissions\n";
        }
    }
}

echo "\n=== 6. Synchronizing Users with Designations ===\n";
$users = $db->query("SELECT user_id, role_id, user_type, school_id, email, username FROM tbl_users WHERE is_deleted = 'n'");
$synced_users = 0;

while ($u = $users->fetch_assoc()) {
    $uid = (int)$u['user_id'];
    $schId = (int)$u['school_id'];
    $rid = (int)$u['role_id'];

    // Find role
    $r_row = $db->query("SELECT role_code, role_name FROM tbl_roles WHERE role_id = {$rid}")->fetch_assoc();
    $target_code = null;
    if ($r_row) {
        $rCode = strtoupper(trim($r_row['role_code']));
        $target_code = $role_to_desig_code[$rCode] ?? null;
    }

    if (!$target_code) {
        $uType = strtoupper(trim($u['user_type']));
        if ($uType === 'ADMIN') $target_code = ($schId === 1 && $uid === 15) ? 'SUPER_ADMIN' : 'SCHOOL_ADMIN';
        elseif ($uType === 'PRINCIPAL') $target_code = 'PRINCIPAL';
        elseif ($uType === 'TEACHER') $target_code = 'TEACHER';
        elseif ($uType === 'ACCOUNTANT') $target_code = 'ACCOUNTANT';
        else $target_code = 'NON_TEACHING_STAFF';
    }

    if ($target_code && isset($desig_id_map[$schId][$target_code])) {
        $desig_id = $desig_id_map[$schId][$target_code];
        $db->query("UPDATE tbl_users SET designation_id = {$desig_id} WHERE user_id = {$uid}");
        $synced_users++;
    }
}
echo "  [OK] Synchronized {$synced_users} users with their respective designations\n";

echo "\n=== 7. Registering Designations Menu Item in tbl_menu_items ===\n";
$m_check = $db->query("SELECT id FROM tbl_menu_items WHERE menu_key = 'user-designations' OR route = 'users/designations'");
if ($m_row = $m_check->fetch_assoc()) {
    $menu_id = (int)$m_row['id'];
    $db->query("UPDATE tbl_menu_items SET 
        parent_id = 223, 
        menu_key = 'user-designations', 
        menu_name = 'Designations & Permissions', 
        route = 'users/designations', 
        icon = 'badge', 
        menu_type = 'submenu', 
        sort_order = 3, 
        permission_key = 'users.manage_roles', 
        is_active = 1, 
        is_super_admin_only = 0, 
        is_soon = 0 
        WHERE id = {$menu_id}");
    echo "  [UPDATED] Menu item 'user-designations' (ID: {$menu_id})\n";
} else {
    $m_ins = $db->prepare("INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_super_admin_only, is_soon, is_active, created_at) VALUES (223, 'user-designations', 'Designations & Permissions', 'users/designations', 'badge', 'submenu', 3, 'users.manage_roles', 0, 0, 1, NOW())");
    $m_ins->execute();
    $menu_id = (int)$m_ins->insert_id;
    echo "  [CREATED] Menu item 'user-designations' created with ID: {$menu_id}\n";
}

echo "\nMigration for Designation-Based RBAC completed successfully!\n";
