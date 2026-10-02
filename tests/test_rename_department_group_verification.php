<?php
/**
 * Test Suite: Department / Group Terminology Rename & Enforcement Verification
 * Run: php tests/test_rename_department_group_verification.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');

require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/database.php';
require_once BASEPATH . 'database/DB.php';
$db_conn =& DB();

require_once BASEPATH . 'core/Loader.php';
require_once BASEPATH . 'core/Model.php';

class MockCI {
    public $db;
    public $load;
    public function __construct($db) {
        $this->db = $db;
        $this->load = new CI_Loader();
    }
}
$mock_ci = new MockCI($db_conn);
function &get_instance() {
    global $mock_ci;
    return $mock_ci;
}

if (!function_exists('get_current_school_id')) {
    function get_current_school_id() { return 1; }
}
if (!function_exists('get_current_academic_year_id')) {
    function get_current_academic_year_id() { return 1; }
}

require_once APPPATH . 'models/Staff_model.php';
require_once APPPATH . 'models/Academic_group_model.php';
require_once APPPATH . 'models/Class_teacher_model.php';
require_once APPPATH . 'models/Subject_teacher_model.php';

$staff_model = new Staff_model();
$group_model = new Academic_group_model();
$class_teacher_model = new Class_teacher_model();
$subject_teacher_model = new Subject_teacher_model();

$passed = 0;
$failed = 0;

function assert_true($cond, $msg, &$passed, &$failed) {
    if ($cond) {
        echo "  [PASS] " . $msg . "\n";
        $passed++;
    } else {
        echo "  [FAIL] " . $msg . "\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo " TEST SUITE: DEPARTMENT / GROUP UI RENAME & WORKFLOW VERIFICATION\n";
echo "======================================================================\n\n";

$school_id = 1;

// --------------------------------------------------------------------
// 1. Verify Existing Group Records Intact (KG's, LP, UP, HS, SS)
// --------------------------------------------------------------------
echo "--- 1. Verify Existing Group Records Intact ---\n";
$groups = $group_model->get_all(true);
$existing_groups = [];
foreach ($groups as $g) {
    $existing_groups[$g->group_name] = (int)$g->academic_group_id;
}

assert_true(isset($existing_groups["KG's"]), "Existing group KG's exists with ID " . ($existing_groups["KG's"] ?? 'null'), $passed, $failed);
assert_true(isset($existing_groups["LP"]), "Existing group LP exists with ID " . ($existing_groups["LP"] ?? 'null'), $passed, $failed);
assert_true(isset($existing_groups["UP"]), "Existing group UP exists with ID " . ($existing_groups["UP"] ?? 'null'), $passed, $failed);
assert_true(isset($existing_groups["HS"]), "Existing group HS exists with ID " . ($existing_groups["HS"] ?? 'null'), $passed, $failed);
assert_true(isset($existing_groups["SS"]), "Existing group SS exists with ID " . ($existing_groups["SS"] ?? 'null'), $passed, $failed);

// --------------------------------------------------------------------
// 2. Verify Database Schema Unchanged (Source of truth is tbl_academic_groups)
// --------------------------------------------------------------------
echo "\n--- 2. Verify Database Schema Unchanged ---\n";
$col_staff_group = $db_conn->query("SHOW COLUMNS FROM tbl_staff LIKE 'academic_group_id'")->num_rows();
assert_true($col_staff_group === 1, "tbl_staff uses academic_group_id as Department / Group column", $passed, $failed);

$col_class_group = $db_conn->query("SHOW COLUMNS FROM tbl_classes LIKE 'academic_group_id'")->num_rows();
assert_true($col_class_group === 1, "tbl_classes maintains academic_group_id column", $passed, $failed);

// --------------------------------------------------------------------
// 3. Verify Menu Items & Navigation UI Terminology
// --------------------------------------------------------------------
echo "\n--- 3. Verify Menu Items & Navigation UI Terminology ---\n";
$menu_res = $db_conn->query("SELECT menu_name FROM tbl_menu_items WHERE menu_key = 'academic-groups'")->row();
assert_true($menu_res && $menu_res->menu_name === 'Department / Groups', "tbl_menu_items 'academic-groups' is named 'Department / Groups'", $passed, $failed);

$app_js = file_get_contents(FCPATH . 'assets/app.js');
assert_true(strpos($app_js, '"academic-groups": "Department / Groups"') !== false, "app.js PAGE_TITLES maps 'academic-groups' to 'Department / Groups'", $passed, $failed);

// --------------------------------------------------------------------
// 4. Verify View Files Terminology
// --------------------------------------------------------------------
echo "\n--- 4. Verify View Files Terminology ---\n";
$add_staff = file_get_contents(APPPATH . 'views/pages/staff/add.php');
assert_true(strpos($add_staff, 'Department / Group *') !== false, "staff/add.php uses 'Department / Group *' label", $passed, $failed);
assert_true(strpos($add_staff, 'Select Department / Group') !== false, "staff/add.php uses 'Select Department / Group' placeholder", $passed, $failed);

$edit_staff = file_get_contents(APPPATH . 'views/pages/staff/edit.php');
assert_true(strpos($edit_staff, 'Department / Group *') !== false, "staff/edit.php uses 'Department / Group *' label", $passed, $failed);

$profile_staff = file_get_contents(APPPATH . 'views/pages/staff/profile.php');
assert_true(strpos($profile_staff, 'Department / Group:') !== false, "staff/profile.php uses 'Department / Group:' badge", $passed, $failed);
assert_true(strpos($profile_staff, '<div class="text-on-surface-variant text-[12px]">Department / Group</div>') !== false, "staff/profile.php uses 'Department / Group' overview detail", $passed, $failed);

$teachers_staff = file_get_contents(APPPATH . 'views/pages/staff/teachers.php');
assert_true(strpos($teachers_staff, 'All Department / Groups') !== false, "staff/teachers.php filter has 'All Department / Groups'", $passed, $failed);
assert_true(strpos($teachers_staff, '<span class="text-on-surface-variant">Department / Group</span>') !== false, "staff/teachers.php card displays 'Department / Group'", $passed, $failed);

$index_staff = file_get_contents(APPPATH . 'views/pages/staff/index.php');
assert_true(strpos($index_staff, 'All Department / Groups') !== false, "staff/index.php filter has 'All Department / Groups'", $passed, $failed);
assert_true(strpos($index_staff, '>Department / Group</th>') !== false, "staff/index.php table has 'Department / Group' header", $passed, $failed);

$groups_view = file_get_contents(APPPATH . 'views/pages/academics/academic_groups.php');
assert_true(strpos($groups_view, 'Add Department / Group') !== false, "academic_groups.php modal and button say 'Add Department / Group'", $passed, $failed);
assert_true(strpos($groups_view, 'Edit Department / Group') !== false, "academic_groups.php edit modal says 'Edit Department / Group'", $passed, $failed);
assert_true(strpos($groups_view, 'Department / Group Name') !== false, "academic_groups.php table has 'Department / Group Name'", $passed, $failed);

$classes_view = file_get_contents(APPPATH . 'views/pages/academics/classes.php');
assert_true(strpos($classes_view, 'Manage Department / Groups') !== false, "academics/classes.php top button says 'Manage Department / Groups'", $passed, $failed);
assert_true(strpos($classes_view, '>Department / Group</th>') !== false, "academics/classes.php table has 'Department / Group' column", $passed, $failed);
assert_true(strpos($classes_view, 'Select Department / Group') !== false, "academics/classes.php modal has 'Select Department / Group'", $passed, $failed);

$period_setup = file_get_contents(APPPATH . 'views/pages/timetable/period_setup.php');
assert_true(strpos($period_setup, 'Select Department / Group') !== false, "period_setup.php has 'Select Department / Group'", $passed, $failed);

// --------------------------------------------------------------------
// 5. Verify Teacher-Class Assignment Validation Logic (UP, LP, KG)
// --------------------------------------------------------------------
echo "\n--- 5. Verify Teacher-Class Assignment Validation Rules ---\n";
$up_group_id = $existing_groups['UP'];
$lp_group_id = $existing_groups['LP'];
$kg_group_id = $existing_groups["KG's"];

// Find classes for UP, LP, KG
$up_class = $db_conn->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes WHERE academic_group_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$up_group_id, $school_id])->row();
$lp_class = $db_conn->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes WHERE academic_group_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$lp_group_id, $school_id])->row();
$kg_class = $db_conn->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes WHERE academic_group_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$kg_group_id, $school_id])->row();

assert_true(!empty($up_class) && !empty($lp_class) && !empty($kg_class), "Found active classes for UP, LP, and KG groups", $passed, $failed);

// Create a test teacher assigned to UP Department / Group
$test_teacher_code = 'TEST_UP_' . time();
$teacher_up_id = $staff_model->insert(array(
    'school_id'         => $school_id,
    'employee_code'     => $test_teacher_code,
    'full_name'         => 'Test Teacher John UP',
    'gender'            => 'Male',
    'phone'             => '+919876543299',
    'email'             => 'test_up_' . time() . '@school.com',
    'staff_type'        => 'teacher',
    'category'          => 'Teaching',
    'academic_group_id' => $up_group_id,
    'joining_date'      => date('Y-m-d'),
    'status'            => 1,
    'created_at'        => date('Y-m-d H:i:s'),
));

assert_true($teacher_up_id > 0, "Created test teacher assigned to UP Department / Group (ID: {$teacher_up_id})", $passed, $failed);

// Test A: Teacher = UP, Class = UP -> Allowed
$check_up = $staff_model->validate_teacher_class_group($teacher_up_id, $up_class->class_id, $school_id);
assert_true($check_up['valid'] === true, "Teacher (UP) -> Class ({$up_class->class_name} / UP): Assignment ALLOWED", $passed, $failed);

// Test B: Teacher = UP, Class = LP -> Blocked
$check_lp = $staff_model->validate_teacher_class_group($teacher_up_id, $lp_class->class_id, $school_id);
assert_true($check_lp['valid'] === false && $check_lp['error'] === 'Teacher and class must belong to the same Department / Group.', "Teacher (UP) -> Class ({$lp_class->class_name} / LP): Assignment BLOCKED with exact error message", $passed, $failed);

// Test C: Teacher = UP, Class = KG -> Blocked
$check_kg = $staff_model->validate_teacher_class_group($teacher_up_id, $kg_class->class_id, $school_id);
assert_true($check_kg['valid'] === false && $check_kg['error'] === 'Teacher and class must belong to the same Department / Group.', "Teacher (UP) -> Class ({$kg_class->class_name} / KG): Assignment BLOCKED with exact error message", $passed, $failed);

// --------------------------------------------------------------------
// 6. Cleanup Temporary Test Data
// --------------------------------------------------------------------
echo "\n--- 6. Cleanup Temporary Test Data ---\n";
$db_conn->where('staff_id', $teacher_up_id)->delete('tbl_staff');
echo "  Cleaned up test teacher (ID: {$teacher_up_id})\n";

// --------------------------------------------------------------------
// SUMMARY
// --------------------------------------------------------------------
echo "\n======================================================================\n";
echo " VERIFICATION SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================================\n";

if ($failed === 0) {
    echo "\n>>> ALL VERIFICATION CHECKS PASSED PERFECTLY!\n\n";
    exit(0);
} else {
    echo "\n>>> SOME VERIFICATION CHECKS FAILED!\n\n";
    exit(1);
}
