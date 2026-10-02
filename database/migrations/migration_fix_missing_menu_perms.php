<?php
/**
 * Migration: Fix ALL missing tbl_menu_permissions entries.
 * 
 * Problem: 29 submenus have a permission_key set in tbl_menu_items
 * but no row in tbl_menu_permissions, causing Navigation::get_permission_matrix_tree()
 * to return empty permissions for those items, which renders them greyed-out.
 *
 * Additionally 7 items have no permission_key at all.
 */
$m = new mysqli('localhost', 'root', '', 'db_school');
if ($m->connect_error) die("DB error: " . $m->connect_error . "\n");
$m->set_charset('utf8mb4');

$inserted = 0;
$updated  = 0;
$skipped  = 0;

function link_perm($m, $menu_id, $perm_key, &$inserted, &$skipped) {
    $p = $m->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm_key}' AND is_deleted = 'n'")->fetch_assoc();
    if (!$p) {
        echo "  [SKIP] Permission key '{$perm_key}' not found in tbl_permissions.\n";
        $skipped++;
        return;
    }
    $pid      = (int)$p['permission_id'];
    $menu_id  = (int)$menu_id;
    $existing = $m->query("SELECT id FROM tbl_menu_permissions WHERE menu_id = {$menu_id} AND permission_id = {$pid}")->fetch_assoc();
    if (!$existing) {
        $m->query("INSERT INTO tbl_menu_permissions (menu_id, permission_id, created_at) VALUES ({$menu_id}, {$pid}, NOW())");
        echo "  [LINK] Menu #{$menu_id} <-> Perm #{$pid} ({$perm_key})\n";
        $inserted++;
    }
    // Also update permission_key on the menu item for fallback resolution
    $m->query("UPDATE tbl_menu_items SET permission_key = '{$perm_key}' WHERE id = {$menu_id} AND (permission_key IS NULL OR permission_key = '')");
}

function set_perm_key($m, $menu_id, $perm_key, &$updated) {
    $res = $m->query("UPDATE tbl_menu_items SET permission_key = '{$perm_key}' WHERE id = {$menu_id} AND (permission_key IS NULL OR permission_key = '')");
    if ($m->affected_rows > 0) {
        echo "  [KEY] Set permission_key='{$perm_key}' on menu #{$menu_id}\n";
        $updated++;
    }
}

echo "\n=== FIX 1: Portal sub-items (IDs 107-113) ===\n";
// IDs 107-110: Portal sub-items inside "Parent & Student Portal" that have NO permission_key
set_perm_key($m, 107, 'portal.view', $updated); // Attendance
set_perm_key($m, 108, 'portal.view', $updated); // Homework
set_perm_key($m, 109, 'portal.view', $updated); // Exam Results
set_perm_key($m, 110, 'portal.view', $updated); // Timetable
link_perm($m, 107, 'portal.view',   $inserted, $skipped);
link_perm($m, 108, 'portal.view',   $inserted, $skipped);
link_perm($m, 109, 'portal.view',   $inserted, $skipped);
link_perm($m, 110, 'portal.view',   $inserted, $skipped);
// IDs 111-113 have permission_key but no mapping
link_perm($m, 111, 'portal.manage', $inserted, $skipped);
link_perm($m, 112, 'portal.manage', $inserted, $skipped);
link_perm($m, 113, 'portal.manage', $inserted, $skipped);

echo "\n=== FIX 2: Library sub-items (IDs 135-137) ===\n";
link_perm($m, 135, 'library.manage', $inserted, $skipped);
link_perm($m, 136, 'library.manage', $inserted, $skipped);
link_perm($m, 137, 'library.view',   $inserted, $skipped);

echo "\n=== FIX 3: Hostel sub-items (IDs 154, 156-159) ===\n";
link_perm($m, 154, 'hostel.manage',   $inserted, $skipped);
link_perm($m, 156, 'hostel.allocate', $inserted, $skipped);
link_perm($m, 157, 'hostel.manage',   $inserted, $skipped);
link_perm($m, 158, 'hostel.manage',   $inserted, $skipped);
set_perm_key($m, 159, 'hostel.view', $updated); // Hostel Reports has no perm_key
link_perm($m, 159, 'hostel.view',     $inserted, $skipped);

echo "\n=== FIX 4: Inventory sub-items (IDs 164, 167-171) ===\n";
link_perm($m, 164, 'inventory.manage', $inserted, $skipped);
link_perm($m, 167, 'inventory.stock',  $inserted, $skipped);
link_perm($m, 168, 'inventory.view',   $inserted, $skipped);
link_perm($m, 169, 'inventory.manage', $inserted, $skipped);
link_perm($m, 170, 'inventory.manage', $inserted, $skipped);
link_perm($m, 171, 'inventory.view',   $inserted, $skipped);

echo "\n=== FIX 5: Events sub-items (IDs 190, 192-196) ===\n";
link_perm($m, 190, 'events.manage', $inserted, $skipped);
link_perm($m, 192, 'events.manage', $inserted, $skipped);
link_perm($m, 193, 'events.manage', $inserted, $skipped);
link_perm($m, 194, 'events.manage', $inserted, $skipped);
link_perm($m, 195, 'events.view',   $inserted, $skipped);
link_perm($m, 196, 'events.view',   $inserted, $skipped);

echo "\n=== FIX 6: Communication sub-items (IDs 204, 207) ===\n";
set_perm_key($m, 204, 'communication.automated_rules', $updated); // Notification Rules
set_perm_key($m, 207, 'communication.view',            $updated); // Delivery Reports
link_perm($m, 204, 'communication.automated_rules', $inserted, $skipped);
link_perm($m, 207, 'communication.view',             $inserted, $skipped);

echo "\n=== SUMMARY ===\n";
echo "Inserted: {$inserted}\n";
echo "Updated permission_keys: {$updated}\n";
echo "Skipped (perm_key not found): {$skipped}\n";

echo "\n=== VERIFY: Submenus still missing permissions ===\n";
$res = $m->query("
    SELECT mi.id, mi.menu_name, mi.permission_key
    FROM tbl_menu_items mi
    WHERE mi.menu_type = 'submenu'
      AND mi.is_active = 1
      AND mi.is_super_admin_only = 0
      AND mi.id NOT IN (SELECT menu_id FROM tbl_menu_permissions)
      AND (mi.permission_key IS NULL OR mi.permission_key = '')
    ORDER BY mi.id
");
$missing = $res->num_rows;
if ($missing === 0) {
    echo "ALL submenus either have a permission mapping OR a permission_key for fallback.\n";
} else {
    while ($r = $res->fetch_assoc()) {
        echo "  [STILL MISSING] ID:{$r['id']} {$r['menu_name']} (key: {$r['permission_key']})\n";
    }
}
