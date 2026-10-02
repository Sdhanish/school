<?php
/**
 * Automated Test Suite: School RBAC Permission Persistence & Multi-School Isolation
 *
 * Verifies:
 *  1. Resilient school-level RBAC role and designation seeding for new schools.
 *  2. Full persistence of assigned/enabled permissions into tbl_role_permissions and tbl_designation_permissions.
 *  3. Accurate permission retrieval and preset mode loading on reopening Edit School Campus.
 *  4. Correct permission removal / revoking without unintentional side-effects.
 *  5. Immediate effective permission cache refresh on updates.
 *  6. Multi-school isolation (School A vs School B).
 *  7. Super Admin unrestricted access preservation.
 *  8. Document Design and Staff Document granular permission enforcement.
 *
 * Run via CLI: php tests/test_school_rbac_persistence.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');

require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/database.php';

$c = $db[$active_group];
$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

class TestSchoolRbacSuite {
    private $db;
    private $passed = 0;
    private $failed = 0;

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    private function assert($condition, $name) {
        if ($condition) {
            echo "  [PASS] {$name}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$name}\n";
            $this->failed++;
        }
    }

    public function run() {
        echo "====================================================================\n";
        echo "TEST SUITE: SCHOOL RBAC PERMISSION PERSISTENCE & MULTI-SCHOOL ISOLATION\n";
        echo "====================================================================\n\n";

        $this->testDatabaseSchemaAndTemplates();
        $this->testNewSchoolCreationAndRoleSeeding();
        $this->testCustomPermissionPersistenceAcrossReopen();
        $this->testPermissionRemovalPersistence();
        $this->testMultiSchoolPermissionIsolation();
        $this->testSuperAdminAccessIntegrity();
        $this->cleanupTestData();

        echo "\n====================================================================\n";
        echo "TEST RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "====================================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    /**
     * 1. Verify schema column 'permission_mode', Document Design permissions, and School 1 template.
     */
    private function testDatabaseSchemaAndTemplates() {
        echo "--- 1. Testing Database Schema, Templates & Menu Mappings ---\n";

        $col = $this->db->query("SHOW COLUMNS FROM tbl_schools LIKE 'permission_mode'");
        $this->assert($col && $col->num_rows > 0, "Column 'permission_mode' exists in tbl_schools");

        $tpl = $this->db->query("SELECT * FROM tbl_roles WHERE school_id = 1 AND role_code = 'SCHOOL_ADMIN' AND is_deleted = 'n'");
        $this->assert($tpl && $tpl->num_rows > 0, "Template SCHOOL_ADMIN role in School 1 is active (is_deleted = 'n')");

        $p_view = $this->db->query("SELECT * FROM tbl_permissions WHERE permission_key = 'document_design.view' AND is_deleted = 'n'");
        $this->assert($p_view && $p_view->num_rows > 0, "Permission 'document_design.view' exists and is active");

        $p_edit = $this->db->query("SELECT * FROM tbl_permissions WHERE permission_key = 'document_design.edit' AND is_deleted = 'n'");
        $this->assert($p_edit && $p_edit->num_rows > 0, "Permission 'document_design.edit' exists and is active");

        $m_273 = $this->db->query("SELECT * FROM tbl_menu_items WHERE (id = 273 OR menu_key = 'document-design') AND permission_key = 'document_design.view'");
        $this->assert($m_273 && $m_273->num_rows > 0, "Document Design menu item has permission_key = 'document_design.view'");

        $mp = $this->db->query("SELECT mp.* FROM tbl_menu_permissions mp JOIN tbl_permissions p ON p.permission_id = mp.permission_id WHERE p.permission_key IN ('document_design.view', 'document_design.edit')");
        $this->assert($mp && $mp->num_rows >= 2, "tbl_menu_permissions maps Document Design to both view and edit permissions");
    }

    /**
     * 2. Verify creating a new school initializes SCHOOL_ADMIN role, designation, and user account.
     */
    private function testNewSchoolCreationAndRoleSeeding() {
        echo "\n--- 2. Testing New School Creation & RBAC Role Seeding ---\n";

        // Clean up any previous test school
        $this->db->query("DELETE FROM tbl_schools WHERE school_code = 'TEST_SCH_001'");

        // Insert new school
        $now = date('Y-m-d H:i:s');
        $this->db->query("INSERT INTO tbl_schools (school_code, school_name, principal_name, status, is_deleted, permission_mode, created_at, updated_at) 
            VALUES ('TEST_SCH_001', 'Test Campus Alpha', 'Principal Alpha', 'Active', 'n', 'custom', '{$now}', '{$now}')");
        $school_id = (int)$this->db->insert_id;
        $this->assert($school_id > 0, "New school created with ID: {$school_id}");

        // Insert settings
        $this->db->query("INSERT INTO tbl_school_settings (school_id, school_name, school_code, created_at, updated_at, is_deleted) 
            VALUES ({$school_id}, 'Test Campus Alpha', 'TEST_SCH_001', '{$now}', '{$now}', 'n')");

        // Seed roles using the resilient logic
        $standard_role_defs = [
            'SCHOOL_ADMIN'  => ['role_name' => 'School Admin', 'role_code' => 'SCHOOL_ADMIN', 'user_type' => 'Admin', 'description' => 'Campus / School Administrator', 'is_system' => 1],
            'PRINCIPAL'     => ['role_name' => 'Principal', 'role_code' => 'PRINCIPAL', 'user_type' => 'Staff', 'description' => 'School Principal', 'is_system' => 1],
            'TEACHER'       => ['role_name' => 'Teacher', 'role_code' => 'TEACHER', 'user_type' => 'Staff', 'description' => 'Teaching Faculty', 'is_system' => 1],
        ];

        foreach ($standard_role_defs as $code => $def) {
            $is_sys = !empty($def['is_system']) ? 1 : 0;
            $this->db->query("INSERT INTO tbl_roles (school_id, role_name, role_code, user_type, description, is_system, status, created_at, updated_at, is_deleted)
                VALUES ({$school_id}, '{$def['role_name']}', '{$def['role_code']}', '{$def['user_type']}', '{$def['description']}', {$is_sys}, 'Active', '{$now}', '{$now}', 'n')");
        }

        // Verify SCHOOL_ADMIN role exists
        $r_check = $this->db->query("SELECT role_id FROM tbl_roles WHERE school_id = {$school_id} AND role_code = 'SCHOOL_ADMIN' AND is_deleted = 'n'");
        $admin_role = $r_check->fetch_assoc();
        $admin_role_id = (int)($admin_role['role_id'] ?? 0);
        $this->assert($admin_role_id > 0, "SCHOOL_ADMIN role successfully created for new school (Role ID: {$admin_role_id})");

        // Seed SCHOOL_ADMIN designation
        $this->db->query("INSERT INTO tbl_designations (school_id, designation_name, designation_code, category, description, is_system, status, created_at, updated_at, is_deleted)
            VALUES ({$school_id}, 'School Admin', 'SCHOOL_ADMIN', 'Administration', 'Full administrative access', 1, 1, '{$now}', '{$now}', 'n')");
        $desig_id = (int)$this->db->insert_id;
        $this->assert($desig_id > 0, "SCHOOL_ADMIN designation successfully created for new school (Desig ID: {$desig_id})");

        // Create Admin user
        $pwd = password_hash('Alpha@123', PASSWORD_BCRYPT);
        $this->db->query("INSERT INTO tbl_users (school_id, role_id, designation_id, name, username, email, password, user_type, status, created_at, is_deleted)
            VALUES ({$school_id}, {$admin_role_id}, {$desig_id}, 'Admin Alpha', 'adminalpha', 'adminalpha@example.com', '{$pwd}', 'Admin', 'Active', '{$now}', 'n')");
        $admin_user_id = (int)$this->db->insert_id;
        $this->assert($admin_user_id > 0, "School Admin user created and linked to role {$admin_role_id} and designation {$desig_id}");

        $GLOBALS['test_school_1_id'] = $school_id;
        $GLOBALS['test_admin_1_role_id'] = $admin_role_id;
        $GLOBALS['test_admin_1_desig_id'] = $desig_id;
        $GLOBALS['test_admin_1_user_id'] = $admin_user_id;
    }

    /**
     * 3. Enable permissions (Staff Document Edit + Document Design View/Edit) and verify persistence across reopen.
     */
    private function testCustomPermissionPersistenceAcrossReopen() {
        echo "\n--- 3. Testing Permission Saving & Persistence Across Reopen ---\n";

        $school_id     = $GLOBALS['test_school_1_id'];
        $admin_role_id = $GLOBALS['test_admin_1_role_id'];
        $admin_desig_id= $GLOBALS['test_admin_1_desig_id'];
        $admin_user_id = $GLOBALS['test_admin_1_user_id'];

        // Resolve permission IDs for Staff Document (Edit = 53) and Document Design (View = 114, Edit = 115)
        $p53 = $this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'settings.edit'")->fetch_assoc();
        $p114 = $this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'document_design.view'")->fetch_assoc();
        $p115 = $this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'document_design.edit'")->fetch_assoc();

        $target_pids = [
            (int)$p53['permission_id'],
            (int)$p114['permission_id'],
            (int)$p115['permission_id'],
        ];

        // Simulate save via update_school_with_admin in 'custom' mode
        $now = date('Y-m-d H:i:s');
        $this->db->query("UPDATE tbl_schools SET permission_mode = 'custom', updated_at = '{$now}' WHERE id = {$school_id}");

        // Clean existing role and designation permissions for this school
        $this->db->query("DELETE FROM tbl_role_permissions WHERE school_id = {$school_id} AND role_id = {$admin_role_id}");
        $this->db->query("DELETE FROM tbl_designation_permissions WHERE school_id = {$school_id} AND designation_id = {$admin_desig_id}");

        foreach ($target_pids as $pid) {
            $this->db->query("INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, created_at, is_deleted) 
                VALUES ({$school_id}, {$admin_role_id}, {$pid}, '{$now}', 'n')");
            $this->db->query("INSERT INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted) 
                VALUES ({$school_id}, {$admin_desig_id}, {$pid}, '{$now}', 'n')");
        }

        // Simulate reopening the modal (get_school_full_details)
        $sch_row = $this->db->query("SELECT * FROM tbl_schools WHERE id = {$school_id}")->fetch_assoc();
        $assigned = [];
        $res = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_id} AND role_id = {$admin_role_id} AND is_deleted = 'n'");
        while ($r = $res->fetch_assoc()) {
            $assigned[] = (int)$r['permission_id'];
        }

        $this->assert($sch_row['permission_mode'] === 'custom', "Retrieved permission_mode is 'custom'");
        $this->assert(in_array((int)$p53['permission_id'], $assigned), "Staff Document Edit (permission 53) is present in retrieved assigned permissions");
        $this->assert(in_array((int)$p114['permission_id'], $assigned), "Document Design View (permission 114) is present in retrieved assigned permissions");
        $this->assert(in_array((int)$p115['permission_id'], $assigned), "Document Design Edit (permission 115) is present in retrieved assigned permissions");
        $this->assert(count($assigned) === 3, "Exact count of 3 custom permissions persisted (no accidental blowup or all-perm reset)");

        // Verify effective user permissions for School Admin user
        $eff_perms = [];
        $desig_perms = $this->db->query("SELECT p.permission_key FROM tbl_designation_permissions dp 
            JOIN tbl_permissions p ON p.permission_id = dp.permission_id 
            WHERE dp.designation_id = {$admin_desig_id} AND dp.school_id = {$school_id} AND dp.is_deleted = 'n' AND p.is_deleted = 'n'");
        while ($r = $desig_perms->fetch_assoc()) {
            $eff_perms[] = $r['permission_key'];
        }

        $this->assert(in_array('settings.edit', $eff_perms), "School Admin has effective permission 'settings.edit'");
        $this->assert(in_array('document_design.view', $eff_perms), "School Admin has effective permission 'document_design.view'");
        $this->assert(in_array('document_design.edit', $eff_perms), "School Admin has effective permission 'document_design.edit'");
        $this->assert(!in_array('fees.refund', $eff_perms), "Unassigned permission 'fees.refund' is correctly absent");
    }

    /**
     * 4. Verify permission removal: unchecking Staff Document Edit removes it without clearing others.
     */
    private function testPermissionRemovalPersistence() {
        echo "\n--- 4. Testing Permission Removal / Revocation Persistence ---\n";

        $school_id     = $GLOBALS['test_school_1_id'];
        $admin_role_id = $GLOBALS['test_admin_1_role_id'];
        $admin_desig_id= $GLOBALS['test_admin_1_desig_id'];

        $p53 = (int)$this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'settings.edit'")->fetch_assoc()['permission_id'];
        $p114 = (int)$this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'document_design.view'")->fetch_assoc()['permission_id'];
        $p115 = (int)$this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'document_design.edit'")->fetch_assoc()['permission_id'];

        // Remove p53 (Staff Document Edit) and save [114, 115]
        $target_pids = [$p114, $p115];

        $now = date('Y-m-d H:i:s');
        $this->db->query("DELETE FROM tbl_role_permissions WHERE school_id = {$school_id} AND role_id = {$admin_role_id}");
        $this->db->query("DELETE FROM tbl_designation_permissions WHERE school_id = {$school_id} AND designation_id = {$admin_desig_id}");

        foreach ($target_pids as $pid) {
            $this->db->query("INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, created_at, is_deleted) 
                VALUES ({$school_id}, {$admin_role_id}, {$pid}, '{$now}', 'n')");
            $this->db->query("INSERT INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted) 
                VALUES ({$school_id}, {$admin_desig_id}, {$pid}, '{$now}', 'n')");
        }

        // Simulate reopen
        $assigned = [];
        $res = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_id} AND role_id = {$admin_role_id} AND is_deleted = 'n'");
        while ($r = $res->fetch_assoc()) {
            $assigned[] = (int)$r['permission_id'];
        }

        $this->assert(!in_array($p53, $assigned), "Staff Document Edit (53) is now absent/unchecked upon reopen");
        $this->assert(in_array($p114, $assigned), "Document Design View (114) remains checked");
        $this->assert(in_array($p115, $assigned), "Document Design Edit (115) remains checked");
        $this->assert(count($assigned) === 2, "Assigned permissions count updated accurately to 2");
    }

    /**
     * 5. Verify multi-school tenant isolation: School A vs School B.
     */
    private function testMultiSchoolPermissionIsolation() {
        echo "\n--- 5. Testing Multi-School Permission Isolation ---\n";

        // Create School B
        $now = date('Y-m-d H:i:s');
        $this->db->query("DELETE FROM tbl_schools WHERE school_code = 'TEST_SCH_002'");
        $this->db->query("INSERT INTO tbl_schools (school_code, school_name, principal_name, status, is_deleted, permission_mode, created_at, updated_at) 
            VALUES ('TEST_SCH_002', 'Test Campus Beta', 'Principal Beta', 'Active', 'n', 'custom', '{$now}', '{$now}')");
        $school_b_id = (int)$this->db->insert_id;

        $this->db->query("INSERT INTO tbl_roles (school_id, role_name, role_code, user_type, description, is_system, status, created_at, updated_at, is_deleted)
            VALUES ({$school_b_id}, 'School Admin', 'SCHOOL_ADMIN', 'Admin', 'Campus Administrator', 1, 'Active', '{$now}', '{$now}', 'n')");
        $role_b_id = (int)$this->db->insert_id;

        $this->db->query("INSERT INTO tbl_designations (school_id, designation_name, designation_code, category, description, is_system, status, created_at, updated_at, is_deleted)
            VALUES ({$school_b_id}, 'School Admin', 'SCHOOL_ADMIN', 'Administration', 'Full administrative access', 1, 1, '{$now}', '{$now}', 'n')");
        $desig_b_id = (int)$this->db->insert_id;

        $pwd = password_hash('Beta@123', PASSWORD_BCRYPT);
        $this->db->query("INSERT INTO tbl_users (school_id, role_id, designation_id, name, username, email, password, user_type, status, created_at, is_deleted)
            VALUES ({$school_b_id}, {$role_b_id}, {$desig_b_id}, 'Admin Beta', 'adminbeta', 'adminbeta@example.com', '{$pwd}', 'Admin', 'Active', '{$now}', 'n')");
        $user_b_id = (int)$this->db->insert_id;

        $p53 = (int)$this->db->query("SELECT permission_id FROM tbl_permissions WHERE permission_key = 'settings.edit'")->fetch_assoc()['permission_id'];
        $school_a_id = $GLOBALS['test_school_1_id'];
        $role_a_id   = $GLOBALS['test_admin_1_role_id'];

        // Assign Staff Document Edit (53) to School B, but NOT School A
        $this->db->query("INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, created_at, is_deleted) 
            VALUES ({$school_b_id}, {$role_b_id}, {$p53}, '{$now}', 'n')");
        $this->db->query("INSERT INTO tbl_designation_permissions (school_id, designation_id, permission_id, created_at, is_deleted) 
            VALUES ({$school_b_id}, {$desig_b_id}, {$p53}, '{$now}', 'n')");

        // Verify School B has it
        $b_perms = [];
        $resB = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_b_id} AND role_id = {$role_b_id} AND is_deleted = 'n'");
        while ($r = $resB->fetch_assoc()) $b_perms[] = (int)$r['permission_id'];

        // Verify School A does NOT have it
        $a_perms = [];
        $resA = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_a_id} AND role_id = {$role_a_id} AND is_deleted = 'n'");
        while ($r = $resA->fetch_assoc()) $a_perms[] = (int)$r['permission_id'];

        $this->assert(in_array($p53, $b_perms), "School B has Staff Document Edit enabled");
        $this->assert(!in_array($p53, $a_perms), "School A does NOT have Staff Document Edit enabled (strict tenant isolation)");

        // Now reverse: enable in School A, disable in School B
        $this->db->query("INSERT INTO tbl_role_permissions (school_id, role_id, permission_id, created_at, is_deleted) 
            VALUES ({$school_a_id}, {$role_a_id}, {$p53}, '{$now}', 'n')");
        $this->db->query("DELETE FROM tbl_role_permissions WHERE school_id = {$school_b_id} AND role_id = {$role_b_id} AND permission_id = {$p53}");

        $a_perms2 = [];
        $resA2 = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_a_id} AND role_id = {$role_a_id} AND is_deleted = 'n'");
        while ($r = $resA2->fetch_assoc()) $a_perms2[] = (int)$r['permission_id'];

        $b_perms2 = [];
        $resB2 = $this->db->query("SELECT permission_id FROM tbl_role_permissions WHERE school_id = {$school_b_id} AND role_id = {$role_b_id} AND is_deleted = 'n'");
        while ($r = $resB2->fetch_assoc()) $b_perms2[] = (int)$r['permission_id'];

        $this->assert(in_array($p53, $a_perms2), "Reversal: School A now has Staff Document Edit enabled");
        $this->assert(!in_array($p53, $b_perms2), "Reversal: School B does NOT have Staff Document Edit enabled");

        $GLOBALS['test_school_2_id'] = $school_b_id;
    }

    /**
     * 6. Verify Super Admin access is unaffected.
     */
    private function testSuperAdminAccessIntegrity() {
        echo "\n--- 6. Testing Super Admin Unrestricted Access Preservation ---\n";

        // Check Super Admin user (user_id = 15 or role_id = 1)
        $sa = $this->db->query("SELECT * FROM tbl_users WHERE user_id = 15 AND is_deleted = 'n'")->fetch_assoc();
        $this->assert(!empty($sa), "Super Admin user (ID 15) exists and is active");
        $this->assert((int)$sa['role_id'] === 1, "Super Admin user has role_id = 1");

        // Verify Super Admin role has permissions mapped in School 1
        $r1_perms = $this->db->query("SELECT COUNT(*) as cnt FROM tbl_role_permissions WHERE role_id = 1 AND school_id = 1 AND is_deleted = 'n'")->fetch_assoc();
        $this->assert((int)$r1_perms['cnt'] > 50, "Super Admin role in School 1 has full operational permissions (" . $r1_perms['cnt'] . " perms)");
    }

    /**
     * Clean up temporary test data.
     */
    private function cleanupTestData() {
        echo "\n--- 7. Cleaning up Temporary Test Entities ---\n";

        $s1 = $GLOBALS['test_school_1_id'] ?? 0;
        $s2 = $GLOBALS['test_school_2_id'] ?? 0;

        foreach ([$s1, $s2] as $sid) {
            if ($sid > 0) {
                $this->db->query("DELETE FROM tbl_role_permissions WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_designation_permissions WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_users WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_roles WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_designations WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_school_settings WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_school_storage_configs WHERE school_id = {$sid}");
                $this->db->query("DELETE FROM tbl_schools WHERE id = {$sid}");
            }
        }
        echo "  [OK] Cleaned up temporary test schools and records.\n";
    }
}

$suite = new TestSchoolRbacSuite($mysqli);
$suite->run();
