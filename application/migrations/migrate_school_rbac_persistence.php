<?php
/**
 * Migration: School RBAC Permission Persistence & Document Design RBAC Integration
 *
 * 1. Add permission_mode column to tbl_schools (ENUM('all', 'custom') DEFAULT 'all').
 * 2. Reactivate template SCHOOL_ADMIN role in School 1 (role_id = 82).
 * 3. Seed Document Design granular permissions (document_design.view, document_design.edit) in tbl_permissions.
 * 4. Update tbl_menu_items (ID 273) to set permission_key = 'document_design.view'.
 * 5. Map tbl_menu_permissions for menu_id 273.
 * 6. Audit and repair all active schools to guarantee SCHOOL_ADMIN role and designation linkage.
 *
 * Run via CLI: php application/migrations/migrate_school_rbac_persistence.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(dirname(__DIR__)) . '/system/');
define('APPPATH', dirname(dirname(__DIR__)) . '/application/');

require_once APPPATH . 'config/database.php';
$c = $db[$active_group];

$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error . "\n");
}

echo "====================================================================\n";
echo "Starting School RBAC Persistence & Document Design Migration...\n";
echo "====================================================================\n";

// -----------------------------------------------------------------------------
// 1. Add permission_mode column to tbl_schools
// -----------------------------------------------------------------------------
$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_schools LIKE 'permission_mode'");
if ($col_check && $col_check->num_rows === 0) {
    $alt = $mysqli->query("ALTER TABLE tbl_schools ADD COLUMN permission_mode ENUM('all', 'custom') NOT NULL DEFAULT 'all' AFTER status");
    if ($alt) {
        echo "[OK] Added column 'permission_mode' to tbl_schools.\n";
    } else {
        echo "[ERROR] Failed to add column 'permission_mode': " . $mysqli->error . "\n";
    }
} else {
    echo "[INFO] Column 'permission_mode' already exists in tbl_schools.\n";
}

// -----------------------------------------------------------------------------
// 2. Reactivate template SCHOOL_ADMIN role in School 1
// -----------------------------------------------------------------------------
$role_check = $mysqli->query("SELECT * FROM tbl_roles WHERE school_id = 1 AND role_code = 'SCHOOL_ADMIN'");
if ($role_check && $role_check->num_rows > 0) {
    $r = $role_check->fetch_assoc();
    $mysqli->query("UPDATE tbl_roles SET is_deleted = 'n', status = 'Active', updated_at = NOW() WHERE role_id = " . (int)$r['role_id']);
    echo "[OK] Reactivated SCHOOL_ADMIN role (ID: " . $r['role_id'] . ") in School 1 as active template.\n";
} else {
    $mysqli->query("INSERT INTO tbl_roles (school_id, role_name, role_code, user_type, description, is_system, status, created_at, updated_at, is_deleted) 
        VALUES (1, 'School Admin', 'SCHOOL_ADMIN', 'Admin', 'Campus / School Administrator', 1, 'Active', NOW(), NOW(), 'n')");
    $new_r_id = $mysqli->insert_id;
    echo "[OK] Created template SCHOOL_ADMIN role (ID: {$new_r_id}) in School 1.\n";
}

// -----------------------------------------------------------------------------
// 3. Seed Document Design granular permissions in tbl_permissions
// -----------------------------------------------------------------------------
$doc_perms = [
    [
        'module'         => 'Settings',
        'action'         => 'view',
        'permission_key' => 'document_design.view',
        'permission_name'=> 'View Document Design',
        'description'    => 'View and preview school document templates and student ID card designs'
    ],
    [
        'module'         => 'Settings',
        'action'         => 'edit',
        'permission_key' => 'document_design.edit',
        'permission_name'=> 'Edit Document Design',
        'description'    => 'Create, configure, and customize school document templates and student ID card designs'
    ]
];

$perm_ids = [];
foreach ($doc_perms as $dp) {
    $p_check = $mysqli->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '" . $mysqli->real_escape_string($dp['permission_key']) . "'");
    if ($p_check && $p_check->num_rows > 0) {
        $p_row = $p_check->fetch_assoc();
        $pid = (int)$p_row['permission_id'];
        $mysqli->query("UPDATE tbl_permissions SET is_deleted = 'n', module = 'Settings', action = '{$dp['action']}', permission_name = '" . $mysqli->real_escape_string($dp['permission_name']) . "', description = '" . $mysqli->real_escape_string($dp['description']) . "' WHERE permission_id = {$pid}");
        $perm_ids[$dp['permission_key']] = $pid;
        echo "[INFO] Permission '{$dp['permission_key']}' already exists (ID: {$pid}). Ensured active.\n";
    } else {
        $mysqli->query("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted) 
            VALUES ('Settings', '{$dp['action']}', '{$dp['permission_key']}', '" . $mysqli->real_escape_string($dp['permission_name']) . "', '" . $mysqli->real_escape_string($dp['description']) . "', NOW(), 'n')");
        $pid = (int)$mysqli->insert_id;
        $perm_ids[$dp['permission_key']] = $pid;
        echo "[OK] Inserted permission '{$dp['permission_key']}' (ID: {$pid}).\n";
    }
}

// -----------------------------------------------------------------------------
// 4. Update tbl_menu_items for Document Design (ID 273)
// -----------------------------------------------------------------------------
$menu_273 = $mysqli->query("SELECT id FROM tbl_menu_items WHERE id = 273");
if ($menu_273 && $menu_273->num_rows > 0) {
    $mysqli->query("UPDATE tbl_menu_items SET permission_key = 'document_design.view', is_active = 1 WHERE id = 273");
    echo "[OK] Updated tbl_menu_items ID 273 permission_key = 'document_design.view'.\n";
} else {
    // If id is not 273, find by menu_key or route
    $menu_by_key = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = 'document-design' OR route = 'settings/document_design'");
    if ($menu_by_key && $menu_by_key->num_rows > 0) {
        $m_row = $menu_by_key->fetch_assoc();
        $m_id = (int)$m_row['id'];
        $mysqli->query("UPDATE tbl_menu_items SET permission_key = 'document_design.view', is_active = 1 WHERE id = {$m_id}");
        echo "[OK] Updated tbl_menu_items ID {$m_id} permission_key = 'document_design.view'.\n";
    }
}

// -----------------------------------------------------------------------------
// 5. Map tbl_menu_permissions for Document Design menu
// -----------------------------------------------------------------------------
$target_menu_id = 273;
$check_m = $mysqli->query("SELECT id FROM tbl_menu_items WHERE id = 273");
if (!$check_m || $check_m->num_rows === 0) {
    $check_m2 = $mysqli->query("SELECT id FROM tbl_menu_items WHERE menu_key = 'document-design' OR route = 'settings/document_design' LIMIT 1");
    if ($check_m2 && $check_m2->num_rows > 0) {
        $target_menu_id = (int)$check_m2->fetch_assoc()['id'];
    }
}

foreach ($perm_ids as $pkey => $pid) {
    $mp_check = $mysqli->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$target_menu_id} AND permission_id = {$pid}");
    if ($mp_check && $mp_check->num_rows === 0) {
        $mysqli->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$target_menu_id}, {$pid}, NOW())");
        echo "[OK] Mapped menu_id {$target_menu_id} to permission_id {$pid} ({$pkey}).\n";
    } else {
        echo "[INFO] Mapping already exists for menu_id {$target_menu_id} and permission_id {$pid}.\n";
    }
}

// -----------------------------------------------------------------------------
// 6. Ensure Super Admin (Role 1) and Active School Admin roles have Document Design permissions
// -----------------------------------------------------------------------------
// For Role 1 (Super Admin in School 1)
foreach ($perm_ids as $pkey => $pid) {
    $r1_check = $mysqli->query("SELECT id FROM tbl_role_permissions WHERE role_id = 1 AND permission_id = {$pid} AND school_id = 1");
    if ($r1_check && $r1_check->num_rows === 0) {
        $mysqli->query("INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, created_at, is_deleted) VALUES (1, 1, {$pid}, NOW(), 'n')");
        echo "[OK] Assigned permission {$pid} ({$pkey}) to Super Admin role in School 1.\n";
    }
}

// -----------------------------------------------------------------------------
// 7. Repair any active schools lacking SCHOOL_ADMIN role or designation
// -----------------------------------------------------------------------------
$schools = $mysqli->query("SELECT id, school_name FROM tbl_schools WHERE is_deleted = 'n'");
while ($s = $schools->fetch_assoc()) {
    $sid = (int)$s['id'];

    // Check SCHOOL_ADMIN role
    $r_check = $mysqli->query("SELECT role_id FROM tbl_roles WHERE school_id = {$sid} AND role_code = 'SCHOOL_ADMIN' AND is_deleted = 'n'");
    $sa_role_id = 0;
    if ($r_check && $r_check->num_rows > 0) {
        $sa_role_id = (int)$r_check->fetch_assoc()['role_id'];
    } else {
        $mysqli->query("INSERT INTO tbl_roles (school_id, role_name, role_code, user_type, description, is_system, status, created_at, updated_at, is_deleted)
            VALUES ({$sid}, 'School Admin', 'SCHOOL_ADMIN', 'Admin', 'Campus / School Administrator', 1, 'Active', NOW(), NOW(), 'n')");
        $sa_role_id = (int)$mysqli->insert_id;
        echo "[OK] Created missing SCHOOL_ADMIN role (ID: {$sa_role_id}) for School {$sid} ({$s['school_name']}).\n";
    }

    // Check SCHOOL_ADMIN designation
    $d_check = $mysqli->query("SELECT designation_id FROM tbl_designations WHERE school_id = {$sid} AND designation_code = 'SCHOOL_ADMIN' AND is_deleted = 'n'");
    $sa_desig_id = 0;
    if ($d_check && $d_check->num_rows > 0) {
        $sa_desig_id = (int)$d_check->fetch_assoc()['designation_id'];
    } else {
        $mysqli->query("INSERT INTO tbl_designations (school_id, designation_name, designation_code, category, description, is_system, status, created_at, updated_at, is_deleted)
            VALUES ({$sid}, 'School Admin', 'SCHOOL_ADMIN', 'Administration', 'Full administrative access for campus', 1, 1, NOW(), NOW(), 'n')");
        $sa_desig_id = (int)$mysqli->insert_id;
        echo "[OK] Created missing SCHOOL_ADMIN designation (ID: {$sa_desig_id}) for School {$sid} ({$s['school_name']}).\n";
    }

    // Check school admin user in tbl_users
    $u_check = $mysqli->query("SELECT user_id, role_id, designation_id FROM tbl_users WHERE school_id = {$sid} AND is_deleted = 'n' AND user_type = 'Admin' ORDER BY user_id ASC LIMIT 1");
    if ($u_check && $u_check->num_rows > 0) {
        $u_row = $u_check->fetch_assoc();
        $uid = (int)$u_row['user_id'];
        $cur_rid = (int)$u_row['role_id'];
        $cur_did = (int)$u_row['designation_id'];

        if ($cur_rid === 0 || $cur_did === 0) {
            $mysqli->query("UPDATE tbl_users SET role_id = {$sa_role_id}, designation_id = {$sa_desig_id} WHERE user_id = {$uid}");
            echo "[OK] Linked Admin User ID {$uid} in School {$sid} to role {$sa_role_id} and designation {$sa_desig_id}.\n";
        }
    }
}

echo "====================================================================\n";
echo "Migration Completed Successfully!\n";
echo "====================================================================\n";
