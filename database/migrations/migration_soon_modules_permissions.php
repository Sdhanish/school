<?php
/**
 * Migration: Add is_soon column to tbl_menu_items and seed SOON module permissions.
 */
$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== STEP 1: ADD is_soon COLUMN TO tbl_menu_items IF NOT EXISTS ===\n";
$col_chk = $m->query("SHOW COLUMNS FROM tbl_menu_items LIKE 'is_soon'")->fetch_assoc();
if (!$col_chk) {
    $m->query("ALTER TABLE tbl_menu_items ADD COLUMN is_soon TINYINT(1) NOT NULL DEFAULT 0 AFTER is_super_admin_only");
    echo "Added is_soon column to tbl_menu_items.\n";
} else {
    echo "is_soon column already exists.\n";
}

$m->query("UPDATE tbl_menu_items SET is_soon = 1 WHERE badge_text = 'Soon'");
echo "Updated is_soon = 1 for all items with badge_text = 'Soon'.\n";

echo "\n=== STEP 2: SEED PERMISSIONS FOR ALL SOON MODULES ===\n";

$soon_perms = [
    // Portal
    ['module' => 'Portal',    'action' => 'view',     'permission_key' => 'portal.view',      'permission_name' => 'View Parent Portal',           'description' => 'Access and view parent portal dashboard and records'],
    ['module' => 'Portal',    'action' => 'manage',   'permission_key' => 'portal.manage',    'permission_name' => 'Manage Parent Portal',         'description' => 'Manage parent accounts, messages, and settings'],
    ['module' => 'Portal',    'action' => 'student',  'permission_key' => 'portal.student',   'permission_name' => 'Manage Student Portal',        'description' => 'Manage student portal features and permissions'],

    // Library
    ['module' => 'Library',   'action' => 'view',     'permission_key' => 'library.view',     'permission_name' => 'Manage Library',               'description' => 'View library books, catalog, and status'],
    ['module' => 'Library',   'action' => 'manage',   'permission_key' => 'library.manage',   'permission_name' => 'Manage Books',                 'description' => 'Add, edit, and categorize library books'],
    ['module' => 'Library',   'action' => 'issue',    'permission_key' => 'library.issue',    'permission_name' => 'Issue / Return Books',          'description' => 'Issue books to students/staff and manage returns'],
    ['module' => 'Library',   'action' => 'reports',  'permission_key' => 'library.reports',  'permission_name' => 'Library Reports',              'description' => 'View and export library circulation reports'],

    // Hostel
    ['module' => 'Hostel',    'action' => 'view',     'permission_key' => 'hostel.view',      'permission_name' => 'View Hostel',                  'description' => 'View hostel buildings, rooms, and allocations'],
    ['module' => 'Hostel',    'action' => 'manage',   'permission_key' => 'hostel.manage',    'permission_name' => 'Manage Hostel',                'description' => 'Configure buildings, rooms, and beds'],
    ['module' => 'Hostel',    'action' => 'allocate', 'permission_key' => 'hostel.allocate',  'permission_name' => 'Student Allocation & Attendance','description' => 'Allocate hostel beds and track attendance'],

    // Inventory
    ['module' => 'Inventory', 'action' => 'view',     'permission_key' => 'inventory.view',   'permission_name' => 'View Inventory',               'description' => 'View inventory items, categories, and stock'],
    ['module' => 'Inventory', 'action' => 'manage',   'permission_key' => 'inventory.manage', 'permission_name' => 'Manage Inventory',             'description' => 'Manage products, suppliers, and purchase orders'],
    ['module' => 'Inventory', 'action' => 'stock',    'permission_key' => 'inventory.stock',  'permission_name' => 'Manage Stock',                 'description' => 'Manage stock in, stock out, and low-stock alerts'],

    // Events
    ['module' => 'Events',    'action' => 'view',     'permission_key' => 'events.view',      'permission_name' => 'View Events & Activities',     'description' => 'View school events, activities, and calendar'],
    ['module' => 'Events',    'action' => 'manage',   'permission_key' => 'events.manage',    'permission_name' => 'Manage Events & Activities',   'description' => 'Organize and manage school events, programs, competitions'],
];

foreach ($soon_perms as $sp) {
    $exists = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$sp['permission_key']}'")->fetch_assoc();
    if (!$exists) {
        $stmt = $m->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, is_deleted) VALUES (?, ?, ?, ?, ?, 'n')");
        $stmt->bind_param('sssss', $sp['module'], $sp['action'], $sp['permission_key'], $sp['permission_name'], $sp['description']);
        $stmt->execute();
        echo "Inserted permission: {$sp['permission_key']} (ID: {$stmt->insert_id})\n";
    } else {
        // Update permission_name and description if needed
        $stmt = $m->prepare("UPDATE tbl_permissions SET permission_name = ?, description = ?, is_deleted = 'n' WHERE permission_id = ?");
        $stmt->bind_param('ssi', $sp['permission_name'], $sp['description'], $exists['permission_id']);
        $stmt->execute();
        echo "Updated permission: {$sp['permission_key']} (ID: {$exists['permission_id']})\n";
    }
}

echo "\n=== STEP 3: MAP SOON MODULE PERMISSIONS IN tbl_menu_permissions & tbl_menu_items ===\n";

function link_menu_perm($m, $menu_id, $perm_key) {
    $p = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm_key}' AND is_deleted = 'n'")->fetch_assoc();
    if (!$p) return;
    $pid = (int)$p['permission_id'];
    $menu_id = (int)$menu_id;

    $chk = $m->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$menu_id} AND permission_id = {$pid}")->fetch_assoc();
    if (!$chk) {
        $m->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
        echo "Linked Menu #{$menu_id} <-> Perm #{$pid} ({$perm_key})\n";
    }
}

// 1. Portal (id 103)
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.view' WHERE id = 103");
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.view' WHERE id = 104"); // Overview
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.student' WHERE id = 105"); // Student Profile
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.manage' WHERE id = 111"); // Fees
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.manage' WHERE id = 112"); // Notices
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.manage' WHERE id = 113"); // Leave Requests
$m->query("UPDATE tbl_menu_items SET permission_key = 'portal.manage' WHERE id = 114"); // Messages
link_menu_perm($m, 103, 'portal.view');
link_menu_perm($m, 103, 'portal.manage');
link_menu_perm($m, 103, 'portal.student');
link_menu_perm($m, 104, 'portal.view');
link_menu_perm($m, 105, 'portal.student');
link_menu_perm($m, 114, 'portal.manage');

// 2. Library (id 125)
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.view' WHERE id IN (125, 126, 137)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.manage' WHERE id IN (128, 129, 130, 131, 135, 136)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.issue' WHERE id IN (133, 134)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.reports' WHERE id = 138");
link_menu_perm($m, 125, 'library.view');
link_menu_perm($m, 126, 'library.view');
link_menu_perm($m, 128, 'library.manage');
link_menu_perm($m, 133, 'library.issue');
link_menu_perm($m, 134, 'library.issue');
link_menu_perm($m, 138, 'library.reports');

// 3. Hostel (id 150)
$m->query("UPDATE tbl_menu_items SET permission_key = 'hostel.view' WHERE id IN (150, 151)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'hostel.manage' WHERE id IN (153, 154, 157, 158)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'hostel.allocate' WHERE id IN (155, 156)");
link_menu_perm($m, 150, 'hostel.view');
link_menu_perm($m, 150, 'hostel.manage');
link_menu_perm($m, 150, 'hostel.allocate');
link_menu_perm($m, 151, 'hostel.view');
link_menu_perm($m, 153, 'hostel.manage');
link_menu_perm($m, 155, 'hostel.allocate');

// 4. Inventory (id 160)
$m->query("UPDATE tbl_menu_items SET permission_key = 'inventory.view' WHERE id IN (160, 161, 168, 171)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'inventory.manage' WHERE id IN (163, 164, 169, 170)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'inventory.stock' WHERE id IN (166, 167)");
link_menu_perm($m, 160, 'inventory.view');
link_menu_perm($m, 160, 'inventory.manage');
link_menu_perm($m, 160, 'inventory.stock');
link_menu_perm($m, 161, 'inventory.view');
link_menu_perm($m, 163, 'inventory.manage');
link_menu_perm($m, 166, 'inventory.stock');

// 5. Events (id 186)
$m->query("UPDATE tbl_menu_items SET permission_key = 'events.view' WHERE id IN (186, 187, 195, 196)");
$m->query("UPDATE tbl_menu_items SET permission_key = 'events.manage' WHERE id IN (189, 190, 192, 193, 194)");
link_menu_perm($m, 186, 'events.view');
link_menu_perm($m, 186, 'events.manage');
link_menu_perm($m, 187, 'events.view');
link_menu_perm($m, 189, 'events.manage');

echo "\nMigration and permission linking for SOON modules completed successfully!\n";
