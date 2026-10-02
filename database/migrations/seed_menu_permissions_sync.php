<?php
/**
 * Migration / Seeder: Ensure complete mapping between tbl_menu_items, tbl_menu_permissions, and tbl_permissions.
 */
$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

echo "=== STEP 1: ADD LIBRARY PERMISSIONS TO tbl_permissions IF MISSING ===\n";

$lib_perms = [
    ['module' => 'Library', 'action' => 'view',   'permission_key' => 'library.view',   'permission_name' => 'View Library',        'description' => 'View library books, catalog, and status'],
    ['module' => 'Library', 'action' => 'manage', 'permission_key' => 'library.manage', 'permission_name' => 'Manage Books',        'description' => 'Add, edit, and categorize library books'],
    ['module' => 'Library', 'action' => 'issue',  'permission_key' => 'library.issue',  'permission_name' => 'Issue / Return Books', 'description' => 'Issue books to students/staff and manage returns'],
];

foreach ($lib_perms as $lp) {
    $exists = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$lp['permission_key']}'")->fetch_assoc();
    if (!$exists) {
        $stmt = $m->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, is_deleted) VALUES (?, ?, ?, ?, ?, 'n')");
        $stmt->bind_param('sssss', $lp['module'], $lp['action'], $lp['permission_key'], $lp['permission_name'], $lp['description']);
        $stmt->execute();
        echo "Inserted permission: {$lp['permission_key']} (ID: {$stmt->insert_id})\n";
    } else {
        echo "Permission already exists: {$lp['permission_key']} (ID: {$exists['permission_id']})\n";
    }
}

// Update permission_key on Library menu items
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.view' WHERE id IN (125, 126, 138) AND (permission_key IS NULL OR permission_key = '')");
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.manage' WHERE id IN (128, 129, 130, 131) AND (permission_key IS NULL OR permission_key = '')");
$m->query("UPDATE tbl_menu_items SET permission_key = 'library.issue' WHERE id IN (133, 134) AND (permission_key IS NULL OR permission_key = '')");

echo "\n=== STEP 2: MAP UNMAPPED GRANULAR PERMISSIONS IN tbl_menu_permissions ===\n";

// Helper function to link menu and permission
function link_menu_perm($m, $menu_id, $perm_key) {
    $p = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm_key}' AND is_deleted = 'n'")->fetch_assoc();
    if (!$p) {
        echo "Warning: Permission key '{$perm_key}' not found in tbl_permissions.\n";
        return;
    }
    $pid = (int)$p['permission_id'];
    $menu_id = (int)$menu_id;

    $chk = $m->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$menu_id} AND permission_id = {$pid}")->fetch_assoc();
    if (!$chk) {
        $m->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
        echo "Linked Menu #{$menu_id} <-> Perm #{$pid} ({$perm_key})\n";
    }
}

// Students
link_menu_perm($m, 4, 'students.edit');
link_menu_perm($m, 4, 'students.delete');
link_menu_perm($m, 4, 'students.export');

// Staff
link_menu_perm($m, 16, 'staff.create');
link_menu_perm($m, 16, 'staff.edit');
link_menu_perm($m, 16, 'staff.delete');
link_menu_perm($m, 17, 'staff.create');
link_menu_perm($m, 17, 'staff.edit');
link_menu_perm($m, 17, 'staff.delete');

// Academics
link_menu_perm($m, 24, 'academics.manage');
link_menu_perm($m, 27, 'academics.manage');
link_menu_perm($m, 28, 'academics.manage');
link_menu_perm($m, 29, 'academics.manage');
link_menu_perm($m, 30, 'academics.manage');

// Attendance
link_menu_perm($m, 31, 'attendance.reports');
link_menu_perm($m, 211, 'attendance.reports');

// Fees
link_menu_perm($m, 54, 'fees.refund');
link_menu_perm($m, 63, 'fees.refund');

// Communication
link_menu_perm($m, 90, 'communication.send');
link_menu_perm($m, 92, 'communication.send');
link_menu_perm($m, 93, 'communication.send');
link_menu_perm($m, 94, 'communication.send');

// Leave
link_menu_perm($m, 115, 'leave.apply');
link_menu_perm($m, 118, 'leave.apply');
link_menu_perm($m, 119, 'leave.apply');

// Users
link_menu_perm($m, 223, 'users.create');
link_menu_perm($m, 223, 'users.delete');
link_menu_perm($m, 225, 'users.create');
link_menu_perm($m, 225, 'users.delete');

// Library
link_menu_perm($m, 125, 'library.view');
link_menu_perm($m, 126, 'library.view');
link_menu_perm($m, 128, 'library.manage');
link_menu_perm($m, 129, 'library.manage');
link_menu_perm($m, 130, 'library.manage');
link_menu_perm($m, 131, 'library.manage');
link_menu_perm($m, 133, 'library.issue');
link_menu_perm($m, 134, 'library.issue');
link_menu_perm($m, 138, 'library.view');

echo "\n=== STEP 3: VERIFY ALL PERMISSIONS ARE MAPPED ===\n";

$res = $m->query("
    SELECT p.permission_id, p.permission_key, p.permission_name, p.module, COUNT(mp.id) as link_count
    FROM tbl_permissions p
    LEFT JOIN tbl_menu_permissions mp ON mp.permission_id = p.permission_id
    WHERE p.is_deleted = 'n'
    GROUP BY p.permission_id
    ORDER BY p.module, p.permission_id
");

$unlinked = 0;
while ($r = $res->fetch_assoc()) {
    if ((int)$r['link_count'] === 0) {
        echo "[UNLINKED] #{$r['permission_id']} {$r['permission_key']} ({$r['permission_name']})\n";
        $unlinked++;
    }
}

if ($unlinked === 0) {
    echo "SUCCESS: 100% of permissions in tbl_permissions are mapped to menu items!\n";
} else {
    echo "WARNING: {$unlinked} permissions still unlinked.\n";
}

echo "Total permissions in tbl_permissions: " . $res->num_rows . "\n";
