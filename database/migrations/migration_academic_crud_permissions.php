<?php
/**
 * Migration: Academic Management CRUD Permissions
 *
 * Adds granular CRUD permissions for:
 * 1. Academic Year (academic_years.create, .view, .edit, .delete)
 * 2. Classes & Divisions (classes.create, .view, .edit, .delete)
 * 3. Subjects (subjects.create, .view, .edit, .delete)
 * 4. Academic Calendar (academic_calendar.create, .view, .edit, .delete)
 *
 * Updates tbl_menu_items so Academic Calendar is child of Academic Setup (id: 26)
 * Maps permissions to tbl_menu_permissions
 * Cascades permissions to Super Admin and School Admin roles across all schools.
 */

define('BASEPATH', 'dummy');
require_once __DIR__ . '/../../application/config/database.php';
$db_conf = $db['default'];

try {
    $pdo = new PDO(
        'mysql:host=' . $db_conf['hostname'] . ';dbname=' . $db_conf['database'],
        $db_conf['username'],
        $db_conf['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connected to database successfully.\n";

    // 1. Define required permissions
    $permissions_to_add = [
        // Academic Year
        [
            'module'          => 'Academics',
            'action'          => 'view',
            'permission_key'  => 'academic_years.view',
            'permission_name' => 'View Academic Year',
            'description'     => 'View academic years and active academic session'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'create',
            'permission_key'  => 'academic_years.create',
            'permission_name' => 'Create Academic Year',
            'description'     => 'Add new academic year'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'edit',
            'permission_key'  => 'academic_years.edit',
            'permission_name' => 'Edit Academic Year',
            'description'     => 'Update academic year dates and status'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'delete',
            'permission_key'  => 'academic_years.delete',
            'permission_name' => 'Delete Academic Year',
            'description'     => 'Remove academic year'
        ],

        // Classes & Divisions
        [
            'module'          => 'Academics',
            'action'          => 'view',
            'permission_key'  => 'classes.view',
            'permission_name' => 'View Classes & Divisions',
            'description'     => 'View classes, divisions, and sections'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'create',
            'permission_key'  => 'classes.create',
            'permission_name' => 'Create Classes & Divisions',
            'description'     => 'Create new classes and divisions'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'edit',
            'permission_key'  => 'classes.edit',
            'permission_name' => 'Edit Classes & Divisions',
            'description'     => 'Modify class names, divisions, and capacity'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'delete',
            'permission_key'  => 'classes.delete',
            'permission_name' => 'Delete Classes & Divisions',
            'description'     => 'Delete classes and divisions'
        ],

        // Subjects
        [
            'module'          => 'Academics',
            'action'          => 'view',
            'permission_key'  => 'subjects.view',
            'permission_name' => 'View Subjects',
            'description'     => 'View subject list and subject teacher assignments'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'create',
            'permission_key'  => 'subjects.create',
            'permission_name' => 'Create Subjects',
            'description'     => 'Add new subjects'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'edit',
            'permission_key'  => 'subjects.edit',
            'permission_name' => 'Edit Subjects',
            'description'     => 'Edit subjects and assign subject teachers'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'delete',
            'permission_key'  => 'subjects.delete',
            'permission_name' => 'Delete Subjects',
            'description'     => 'Delete subjects'
        ],

        // Academic Calendar
        [
            'module'          => 'Academics',
            'action'          => 'view',
            'permission_key'  => 'academic_calendar.view',
            'permission_name' => 'View Academic Calendar',
            'description'     => 'View academic calendar and scheduled events'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'create',
            'permission_key'  => 'academic_calendar.create',
            'permission_name' => 'Create Academic Calendar',
            'description'     => 'Add events to academic calendar'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'edit',
            'permission_key'  => 'academic_calendar.edit',
            'permission_name' => 'Edit Academic Calendar',
            'description'     => 'Update calendar events'
        ],
        [
            'module'          => 'Academics',
            'action'          => 'delete',
            'permission_key'  => 'academic_calendar.delete',
            'permission_name' => 'Delete Academic Calendar',
            'description'     => 'Delete calendar events'
        ]
    ];

    $pdo->beginTransaction();

    // 2. Insert or update tbl_permissions
    $key_to_id = [];
    foreach ($permissions_to_add as $p) {
        $check = $pdo->prepare("SELECT permission_id FROM tbl_permissions WHERE permission_key = ?");
        $check->execute([$p['permission_key']]);
        $row = $check->fetch();

        if ($row) {
            $pid = (int)$row['permission_id'];
            $upd = $pdo->prepare("UPDATE tbl_permissions SET module = ?, action = ?, permission_name = ?, description = ?, is_deleted = 'n' WHERE permission_id = ?");
            $upd->execute([$p['module'], $p['action'], $p['permission_name'], $p['description'], $pid]);
            echo "Updated existing permission: {$p['permission_key']} (ID: $pid)\n";
        } else {
            $ins = $pdo->prepare("INSERT INTO tbl_permissions (module, action, permission_key, permission_name, description, is_deleted) VALUES (?, ?, ?, ?, ?, 'n')");
            $ins->execute([$p['module'], $p['action'], $p['permission_key'], $p['permission_name'], $p['description']]);
            $pid = (int)$pdo->lastInsertId();
            echo "Inserted new permission: {$p['permission_key']} (ID: $pid)\n";
        }
        $key_to_id[$p['permission_key']] = $pid;
    }

    // 3. Update tbl_menu_items
    // Academic Year (id: 27)
    $pdo->prepare("UPDATE tbl_menu_items SET permission_key = 'academic_years.view' WHERE id = 27")->execute();
    // Classes & Divisions (id: 28)
    $pdo->prepare("UPDATE tbl_menu_items SET permission_key = 'classes.view' WHERE id = 28")->execute();
    // Subjects (id: 29)
    $pdo->prepare("UPDATE tbl_menu_items SET permission_key = 'subjects.view' WHERE id = 29")->execute();
    // Academic Groups (id: 257) - Ensure sort_order = 4
    $pdo->prepare("UPDATE tbl_menu_items SET sort_order = 4 WHERE id = 257")->execute();
    // Academic Calendar (id: 30) - Set parent_id = 26 (Academic Setup), sort_order = 5, permission_key = academic_calendar.view
    $pdo->prepare("UPDATE tbl_menu_items SET parent_id = 26, sort_order = 5, permission_key = 'academic_calendar.view' WHERE id = 30")->execute();
    echo "Updated tbl_menu_items hierarchy and permission keys.\n";

    // 4. Update tbl_menu_permissions mappings
    $menu_mapping = [
        27 => ['academic_years.create', 'academic_years.view', 'academic_years.edit', 'academic_years.delete'],
        28 => ['classes.create', 'classes.view', 'classes.edit', 'classes.delete'],
        29 => ['subjects.create', 'subjects.view', 'subjects.edit', 'subjects.delete'],
        30 => ['academic_calendar.create', 'academic_calendar.view', 'academic_calendar.edit', 'academic_calendar.delete'],
    ];

    foreach ($menu_mapping as $menu_id => $keys) {
        // Clear existing mappings for this menu item to replace with granular CRUD operations
        $pdo->prepare("DELETE FROM tbl_menu_permissions WHERE menu_id = ?")->execute([$menu_id]);

        foreach ($keys as $k) {
            $pid = $key_to_id[$k];
            $pdo->prepare("INSERT INTO tbl_menu_permissions (menu_id, permission_id) VALUES (?, ?)")->execute([$menu_id, $pid]);
        }
        echo "Mapped Menu ID $menu_id to CRUD permissions: " . implode(', ', $keys) . "\n";
    }

    // 5. Cascade permissions to Roles:
    // a. All School Admin & Super Admin roles
    $admin_roles = $pdo->query("SELECT role_id, school_id FROM tbl_roles WHERE role_code IN ('SUPER_ADMIN', 'SCHOOL_ADMIN')")->fetchAll();
    $all_new_pids = array_values($key_to_id);

    $ins_role_perm = $pdo->prepare("
        INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, is_deleted)
        VALUES (?, ?, ?, 'n')
        ON DUPLICATE KEY UPDATE is_deleted = 'n'
    ");

    $role_count = 0;
    foreach ($admin_roles as $ar) {
        $rid = (int)$ar['role_id'];
        $sid = (int)$ar['school_id'];
        foreach ($all_new_pids as $pid) {
            // Check if already exists
            $chk = $pdo->prepare("SELECT id FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?");
            $chk->execute([$rid, $pid]);
            if (!$chk->fetch()) {
                $ins_role_perm->execute([$sid, $rid, $pid]);
                $role_count++;
            }
        }
    }
    echo "Granted new CRUD permissions to admin roles ($role_count records added).\n";

    // b. Any role that had academics.view (id: 12) -> grant view permissions
    $view_pids = [
        $key_to_id['academic_years.view'],
        $key_to_id['classes.view'],
        $key_to_id['subjects.view'],
        $key_to_id['academic_calendar.view']
    ];
    $roles_with_view = $pdo->query("SELECT DISTINCT role_id, school_id FROM tbl_role_permissions WHERE permission_id = 12 AND is_deleted = 'n'")->fetchAll();
    foreach ($roles_with_view as $rw) {
        $rid = (int)$rw['role_id'];
        $sid = (int)$rw['school_id'];
        foreach ($view_pids as $pid) {
            $chk = $pdo->prepare("SELECT id FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?");
            $chk->execute([$rid, $pid]);
            if (!$chk->fetch()) {
                $ins_role_perm->execute([$sid, $rid, $pid]);
            }
        }
    }

    // c. Any role that had academics.manage (id: 13) -> grant all CRUD permissions
    $roles_with_manage = $pdo->query("SELECT DISTINCT role_id, school_id FROM tbl_role_permissions WHERE permission_id = 13 AND is_deleted = 'n'")->fetchAll();
    foreach ($roles_with_manage as $rm) {
        $rid = (int)$rm['role_id'];
        $sid = (int)$rm['school_id'];
        foreach ($all_new_pids as $pid) {
            $chk = $pdo->prepare("SELECT id FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?");
            $chk->execute([$rid, $pid]);
            if (!$chk->fetch()) {
                $ins_role_perm->execute([$sid, $rid, $pid]);
            }
        }
    }

    // 6. Cascade permissions to Designations (tbl_designation_permissions):
    $admin_desigs = $pdo->query("SELECT designation_id, school_id FROM tbl_designations WHERE designation_code IN ('SUPER_ADMIN', 'SCHOOL_ADMIN')")->fetchAll();
    $ins_desig_perm = $pdo->prepare("
        INSERT INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted)
        VALUES (?, ?, ?, NOW(), 'n')
    ");

    $desig_count = 0;
    foreach ($admin_desigs as $ad) {
        $did = (int)$ad['designation_id'];
        $sid = (int)$ad['school_id'];
        foreach ($all_new_pids as $pid) {
            $chk = $pdo->prepare("SELECT id FROM tbl_designation_permissions WHERE designation_id = ? AND permission_id = ?");
            $chk->execute([$did, $pid]);
            if (!$chk->fetch()) {
                $ins_desig_perm->execute([$sid, $did, $pid]);
                $desig_count++;
            }
        }
    }
    echo "Granted new CRUD permissions to admin designations ($desig_count records added).\n";

    // Designations that had academics.view (12)
    $desigs_with_view = $pdo->query("SELECT DISTINCT designation_id, school_id FROM tbl_designation_permissions WHERE permission_id = 12 AND is_deleted = 'n'")->fetchAll();
    foreach ($desigs_with_view as $dw) {
        $did = (int)$dw['designation_id'];
        $sid = (int)$dw['school_id'];
        foreach ($view_pids as $pid) {
            $chk = $pdo->prepare("SELECT id FROM tbl_designation_permissions WHERE designation_id = ? AND permission_id = ?");
            $chk->execute([$did, $pid]);
            if (!$chk->fetch()) {
                $ins_desig_perm->execute([$sid, $did, $pid]);
            }
        }
    }

    // Designations that had academics.manage (13)
    $desigs_with_manage = $pdo->query("SELECT DISTINCT designation_id, school_id FROM tbl_designation_permissions WHERE permission_id = 13 AND is_deleted = 'n'")->fetchAll();
    foreach ($desigs_with_manage as $dm) {
        $did = (int)$dm['designation_id'];
        $sid = (int)$dm['school_id'];
        foreach ($all_new_pids as $pid) {
            $chk = $pdo->prepare("SELECT id FROM tbl_designation_permissions WHERE designation_id = ? AND permission_id = ?");
            $chk->execute([$did, $pid]);
            if (!$chk->fetch()) {
                $ins_desig_perm->execute([$sid, $did, $pid]);
            }
        }
    }

    $pdo->commit();
    echo "\n=== MIGRATION COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
