<?php
/**
 * Database Migration: Academic Groups CRUD Permissions & Navigation Sync
 *
 * 1. Adds granular permissions to tbl_permissions:
 *    - academic_groups.view
 *    - academic_groups.create
 *    - academic_groups.edit
 *    - academic_groups.delete
 *
 * 2. Adds Academic Groups submenu item under Academic Setup (parent_id: 26) in tbl_menu_items.
 *
 * 3. Maps all 4 permissions to the new menu item in tbl_menu_permissions.
 *
 * 4. Adds academic_year_id column to tbl_academic_groups for academic year isolation.
 *
 * 5. Seeds permissions to:
 *    - Super Admin role (role_id: 1)
 *    - Default School Admin role in School 1 (role_id: 2)
 *    - School Test School Admin role in School 13 (role_id: 101)
 */

$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== 1. Ensuring academic_year_id in tbl_academic_groups ===\n";
$col_check = $m->query("SHOW COLUMNS FROM tbl_academic_groups LIKE 'academic_year_id'");
if ($col_check->num_rows === 0) {
    $m->query("ALTER TABLE tbl_academic_groups ADD COLUMN academic_year_id INT(10) UNSIGNED NULL DEFAULT NULL AFTER school_id");
    $m->query("ALTER TABLE tbl_academic_groups ADD INDEX idx_ag_school_year (school_id, academic_year_id)");
    echo "  [OK] Added academic_year_id column and index to tbl_academic_groups\n";
} else {
    echo "  [EXISTS] academic_year_id column already exists\n";
}

echo "\n=== 2. Adding Permissions to tbl_permissions ===\n";
$perms = [
    [
        'module'          => 'Academics',
        'action'          => 'view',
        'permission_key'  => 'academic_groups.view',
        'permission_name' => 'View Academic Groups',
        'description'     => 'View Academic Groups listing and details'
    ],
    [
        'module'          => 'Academics',
        'action'          => 'create',
        'permission_key'  => 'academic_groups.create',
        'permission_name' => 'Create Academic Groups',
        'description'     => 'Add new Academic Group'
    ],
    [
        'module'          => 'Academics',
        'action'          => 'edit',
        'permission_key'  => 'academic_groups.edit',
        'permission_name' => 'Edit Academic Groups',
        'description'     => 'Edit Academic Group details, display order, and status'
    ],
    [
        'module'          => 'Academics',
        'action'          => 'delete',
        'permission_key'  => 'academic_groups.delete',
        'permission_name' => 'Delete Academic Groups',
        'description'     => 'Deactivate or soft-delete Academic Group'
    ],
];

$perm_ids = [];
foreach ($perms as $p) {
    $stmt = $m->prepare("SELECT permission_id FROM tbl_permissions WHERE permission_key = ?");
    $stmt->bind_param('s', $p['permission_key']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pid = (int)$row['permission_id'];
        echo "  [EXISTS] Permission '{$p['permission_key']}' has ID: {$pid}\n";
    } else {
        $ins = $m->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, created_at, is_deleted) VALUES (?, ?, ?, ?, ?, NOW(), 'n')");
        $ins->bind_param('sssss', $p['module'], $p['action'], $p['permission_key'], $p['permission_name'], $p['description']);
        $ins->execute();
        $pid = (int)$ins->insert_id;
        echo "  [CREATED] Permission '{$p['permission_key']}' created with ID: {$pid}\n";
    }
    $perm_ids[$p['permission_key']] = $pid;
}

echo "\n=== 3. Adding/Updating Academic Groups Menu Item in tbl_menu_items ===\n";
$m_check = $m->query("SELECT id FROM tbl_menu_items WHERE menu_key = 'academic-groups' OR (parent_id = 26 AND route = 'academics/academic_groups')");
if ($m_row = $m_check->fetch_assoc()) {
    $menu_id = (int)$m_row['id'];
    $m->query("UPDATE tbl_menu_items SET 
        parent_id = 26, 
        menu_key = 'academic-groups', 
        menu_name = 'Academic Groups', 
        route = 'academics/academic_groups', 
        icon = 'category', 
        menu_type = 'submenu', 
        sort_order = 4, 
        permission_key = 'academic_groups.view', 
        is_active = 1, 
        is_super_admin_only = 0, 
        is_soon = 0 
        WHERE id = {$menu_id}");
    echo "  [UPDATED] Menu item 'academic-groups' (ID: {$menu_id})\n";
} else {
    $m_ins = $m->prepare("INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, permission_key, is_super_admin_only, is_soon, is_active, created_at) VALUES (26, 'academic-groups', 'Academic Groups', 'academics/academic_groups', 'category', 'submenu', 4, 'academic_groups.view', 0, 0, 1, NOW())");
    $m_ins->execute();
    $menu_id = (int)$m_ins->insert_id;
    echo "  [CREATED] Menu item 'academic-groups' created with ID: {$menu_id}\n";
}

echo "\n=== 4. Linking Permissions in tbl_menu_permissions ===\n";
foreach ($perm_ids as $pkey => $pid) {
    $link_check = $m->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$menu_id} AND permission_id = {$pid}");
    if ($link_check->num_rows === 0) {
        $m->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
        echo "  [LINKED] Menu {$menu_id} -> Perm {$pid} ({$pkey})\n";
    } else {
        echo "  [ALREADY LINKED] Menu {$menu_id} -> Perm {$pid} ({$pkey})\n";
    }
}

echo "\n=== 5. Seeding Role Permissions ===\n";
// Roles to grant full academic_groups.* permissions:
// 1: Super Admin (all schools)
// 2: School Admin for School 1
// 101: School Admin for School 13 (School Test)
$target_roles = [
    ['role_id' => 1, 'school_id' => 1],
    ['role_id' => 2, 'school_id' => 1],
    ['role_id' => 101, 'school_id' => 13],
];

foreach ($target_roles as $tr) {
    $rId = (int)$tr['role_id'];
    $sId = (int)$tr['school_id'];

    foreach ($perm_ids as $pkey => $pid) {
        $rp_check = $m->query("SELECT id FROM tbl_role_permissions WHERE role_id = {$rId} AND permission_id = {$pid} AND school_id = {$sId}");
        if ($rp_check && $rp_check->num_rows === 0) {
            $m->query("INSERT INTO tbl_role_permissions (role_id, permission_id, school_id, created_at) VALUES ({$rId}, {$pid}, {$sId}, NOW())");
            echo "  [GRANTED] Role {$rId} (School {$sId}) -> {$pkey} (ID: {$pid})\n";
        } else {
            echo "  [EXISTS] Role {$rId} (School {$sId}) already has {$pkey}\n";
        }
    }
}

echo "\nMigration completed successfully!\n";
