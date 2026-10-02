<?php
/**
 * Test Suite: Department / Groups Section on Designations Page Verification
 * Run: php tests/test_department_groups_on_designations.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');

require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/database.php';
require_once BASEPATH . 'database/DB.php';
$db =& DB();

require_once BASEPATH . 'core/Loader.php';
require_once BASEPATH . 'core/Model.php';

class MockRbac {
    public function is_super_admin() { return true; }
    public function has_permission($perm) { return true; }
}

class MockCI {
    public $db;
    public $load;
    public $rbac;
    public $school_id = 1;
    public function __construct($db) {
        $this->db = $db;
        $this->load = new CI_Loader();
        $this->rbac = new MockRbac();
    }
}
$mock_ci = new MockCI($db);
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
require_once APPPATH . 'models/Designation_model.php';
require_once APPPATH . 'models/Subject_teacher_model.php';

$staff_model = new Staff_model();
$group_model = new Academic_group_model();
$desig_model = new Designation_model();
$subject_teacher_model = new Subject_teacher_model();

$passed = 0;
$failed = 0;

function assert_test($cond, $msg, &$passed, &$failed) {
    if ($cond) {
        echo "  [PASS] " . $msg . "\n";
        $passed++;
    } else {
        echo "  [FAIL] " . $msg . "\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo " TEST SUITE: DEPARTMENT / GROUPS SECTION ON DESIGNATIONS PAGE\n";
echo "======================================================================\n\n";

// --- 1. Dynamic Group Loading & Model Verification ---
echo "--- 1. Dynamic Academic Groups Loading via Model ---\n";
$groups = $group_model->get_groups_with_staff_counts(1, true);
assert_test(is_array($groups) && count($groups) > 0, "Academic_group_model::get_groups_with_staff_counts returns array of groups", $passed, $failed);

$has_required_props = true;
foreach ($groups as $g) {
    if (!isset($g->academic_group_id) || !isset($g->group_name) || !isset($g->staff_count) || !isset($g->status)) {
        $has_required_props = false;
        break;
    }
}
assert_test($has_required_props, "All groups have academic_group_id, group_name, staff_count, status", $passed, $failed);

// --- 2. Dynamic Staff Count Calculation & Teacher Move ---
echo "\n--- 2. Dynamic Staff Count Calculation & Automatic Update ---\n";
$staff_samples = $db->select('staff_id, full_name')->from('tbl_staff')->where('school_id', 1)->where('status', 1)->where('is_deleted', 'n')->limit(2)->get()->result();

if (count($staff_samples) >= 2) {
    $s1 = $staff_samples[0]->staff_id;
    $s2 = $staff_samples[1]->staff_id;
    
    // Assign s1 to group 2 (LP), s2 to group 3 (UP)
    $db->where('staff_id', $s1)->update('tbl_staff', ['academic_group_id' => 2]);
    $db->where('staff_id', $s2)->update('tbl_staff', ['academic_group_id' => 3]);
    
    $g_check = $group_model->get_groups_with_staff_counts(1, true);
    $lp_count = 0;
    $up_count = 0;
    foreach ($g_check as $gc) {
        if ((int)$gc->academic_group_id === 2) $lp_count = (int)$gc->staff_count;
        if ((int)$gc->academic_group_id === 3) $up_count = (int)$gc->staff_count;
    }
    assert_test($lp_count >= 1, "LP group dynamically counts at least 1 staff (got $lp_count)", $passed, $failed);
    assert_test($up_count >= 1, "UP group dynamically counts at least 1 staff (got $up_count)", $passed, $failed);
    
    // Move s1 from LP (2) to UP (3)
    $db->where('staff_id', $s1)->update('tbl_staff', ['academic_group_id' => 3]);
    
    $g_check_after = $group_model->get_groups_with_staff_counts(1, true);
    $lp_count_after = 0;
    $up_count_after = 0;
    foreach ($g_check_after as $gc) {
        if ((int)$gc->academic_group_id === 2) $lp_count_after = (int)$gc->staff_count;
        if ((int)$gc->academic_group_id === 3) $up_count_after = (int)$gc->staff_count;
    }
    assert_test($lp_count_after === ($lp_count - 1), "After moving staff from LP to UP, LP staff count decreased by 1", $passed, $failed);
    assert_test($up_count_after === ($up_count + 1), "After moving staff from LP to UP, UP staff count increased by 1", $passed, $failed);
    
    // Cleanup temporary assignment
    $db->where('staff_id', $s1)->update('tbl_staff', ['academic_group_id' => null]);
    $db->where('staff_id', $s2)->update('tbl_staff', ['academic_group_id' => null]);
}

// --- 3. View File Layout & Structure Verification ---
echo "\n--- 3. View File (designations.php) Structure Verification ---\n";
$view_path = APPPATH . 'views/pages/staff/designations.php';
$view_content = file_get_contents($view_path);

assert_test(strpos($view_content, 'Department / Groups &amp; Designations') !== false || strpos($view_content, 'Department / Groups & Designations') !== false, "Page header title contains 'Department / Groups & Designations'", $passed, $failed);
assert_test(strpos($view_content, 'btn-open-add-group') !== false, "Add Department / Group button is present", $passed, $failed);
assert_test(strpos($view_content, 'btn-open-add-desig') !== false, "Add Designation button is present", $passed, $failed);
assert_test(strpos($view_content, 'Department / Groups (<span id="group-total-count">') !== false, "Department / Groups section card header is present with dynamic count", $passed, $failed);
assert_test(strpos($view_content, 'Designations (<span id="desig-total-count">') !== false, "Designations section card header is present with dynamic count", $passed, $failed);
assert_test(strpos($view_content, 'grid-cols-1 xl:grid-cols-2') !== false || strpos($view_content, 'grid-cols-1 lg:grid-cols-2') !== false, "Two-card responsive grid layout is implemented", $passed, $failed);

// Column checks
assert_test(strpos($view_content, 'Department / Group Name') !== false, "Table header 'Department / Group Name' is present", $passed, $failed);
assert_test(strpos($view_content, 'btn-edit-group') !== false, "Edit action button exists for Department / Groups", $passed, $failed);
assert_test(strpos($view_content, 'modal-group') !== false, "Modal for Department / Group Add/Edit exists", $passed, $failed);

// Strict NO delete check for Department / Groups
assert_test(strpos($view_content, 'btn-delete-group') === false, "CRITICAL: No btn-delete-group exists in Department / Groups table", $passed, $failed);
assert_test(strpos($view_content, 'delete_academic_group') === false, "CRITICAL: No delete_academic_group link or action in designations view", $passed, $failed);

// --- 4. Controller Methods Verification ---
echo "\n--- 4. Controller (Staff.php) Methods Verification ---\n";
if (!class_exists('CI_Controller')) {
    class CI_Controller {}
}
if (!class_exists('MY_Controller')) {
    class MY_Controller extends CI_Controller {
        public $school_id = 1;
        public $rbac;
        public function __construct() {
            $this->rbac = new MockRbac();
        }
    }
}
$staff_ctrl_file = file_get_contents(APPPATH . 'controllers/Staff.php');

assert_test(strpos($staff_ctrl_file, 'function ajax_get_academic_group') !== false, "Staff::ajax_get_academic_group() exists", $passed, $failed);
assert_test(strpos($staff_ctrl_file, 'function ajax_save_academic_group') !== false, "Staff::ajax_save_academic_group() exists", $passed, $failed);
assert_test(strpos($staff_ctrl_file, 'function ajax_delete_academic_group') === false, "CRITICAL: Staff::ajax_delete_academic_group() does NOT exist (No delete operation)", $passed, $failed);
assert_test(strpos($staff_ctrl_file, "action === 'add_group'") !== false, "Staff::designations() handles action === 'add_group' POST fallback", $passed, $failed);
assert_test(strpos($staff_ctrl_file, "action === 'edit_group'") !== false, "Staff::designations() handles action === 'edit_group' POST fallback", $passed, $failed);

// --- 5. Academic Group Creation & Edit Logic via Model ---
echo "\n--- 5. Academic Group Creation & Edit Functionality ---\n";
$test_group_name = 'Test Auto Group ' . time();
$new_group_id = $group_model->insert([
    'school_id'       => 1,
    'group_name'      => $test_group_name,
    'description'     => 'Automated test department group',
    'attendance_type' => 'daily',
    'display_order'   => 99,
    'status'          => 1
], 'Class X, Class Y');

assert_test($new_group_id > 0, "Created new Department / Group successfully (ID: $new_group_id)", $passed, $failed);

$fetched_group = $group_model->get_by_id($new_group_id, 1);
assert_test($fetched_group && $fetched_group->group_name === $test_group_name, "Fetched newly created group by ID", $passed, $failed);
assert_test(strpos($fetched_group->configured_classes_text, 'Class X') !== false, "Configured classes were saved and retrieved", $passed, $failed);

// Update group
$updated_name = $test_group_name . ' (Updated)';
$update_res = $group_model->update($new_group_id, [
    'group_name'  => $updated_name,
    'description' => 'Updated description'
], 'Class X, Class Y, Class Z');

assert_test($update_res, "Updated Department / Group successfully", $passed, $failed);
$fetched_updated = $group_model->get_by_id($new_group_id, 1);
assert_test($fetched_updated->group_name === $updated_name, "Verified updated name in database", $passed, $failed);

// Clean up test group
$db->where('academic_group_id', $new_group_id)->delete('tbl_academic_group_classes');
$db->where('academic_group_id', $new_group_id)->delete('tbl_academic_groups');
echo "  Cleaned up temporary test group (ID: $new_group_id)\n";

// --- 6. Teacher-Class Group Rule Verification ---
echo "\n--- 6. Teacher-Class Group Rule Intact ---\n";
// Find a teacher and class
$up_class = $db->select('class_id, academic_group_id')->from('tbl_classes')->where('academic_group_id', 3)->where('status', 1)->where('is_deleted', 'n')->get()->row();
$lp_class = $db->select('class_id, academic_group_id')->from('tbl_classes')->where('academic_group_id', 2)->where('status', 1)->where('is_deleted', 'n')->get()->row();

if ($up_class && $lp_class) {
    // Create test teacher assigned to UP (3)
    $test_teacher_id = $staff_model->insert([
        'school_id'         => 1,
        'employee_code'     => 'TSTGRP' . time(),
        'full_name'         => 'Test Teacher Group Rule',
        'gender'            => 'Male',
        'status'            => 1,
        'staff_type'        => 'Teaching',
        'category'          => 'Teaching',
        'academic_group_id' => 3
    ]);
    
    // Verify rule allows UP class
    $val_up = $staff_model->validate_teacher_class_group($test_teacher_id, $up_class->class_id);
    assert_test($val_up['valid'] === true, "Teacher (UP) -> Class (UP): Assignment ALLOWED", $passed, $failed);
    
    // Verify rule blocks LP class
    $val_lp = $staff_model->validate_teacher_class_group($test_teacher_id, $lp_class->class_id);
    assert_test($val_lp['valid'] === false, "Teacher (UP) -> Class (LP): Assignment BLOCKED by rule", $passed, $failed);
    
    // Clean up test teacher
    $db->where('staff_id', $test_teacher_id)->delete('tbl_staff');
    echo "  Cleaned up temporary test teacher (ID: $test_teacher_id)\n";
}

// --- 7. No New Department Models, Controllers, or Tables Created ---
echo "\n--- 7. Verify No New Department Model or Controller Created ---\n";
$dept_ctrl_exists = file_exists(APPPATH . 'controllers/Departments.php') || file_exists(APPPATH . 'controllers/Department.php');
assert_test(!$dept_ctrl_exists, "No new Department controller exists (Departments.php / Department.php)", $passed, $failed);

$dept_model_exists = file_exists(APPPATH . 'models/Department_model.php');
assert_test(!$dept_model_exists, "No new Department model exists (Department_model.php)", $passed, $failed);

// Verify that designations view and Staff.php use academic_group_id and Academic_group_model
$uses_academic_group_model = strpos($staff_ctrl_file, 'Academic_group_model->get_groups_with_staff_counts') !== false;
assert_test($uses_academic_group_model, "Staff::designations() reuses Academic_group_model (not Department model)", $passed, $failed);

$view_uses_academic_group_id = strpos($view_content, 'academic_group_id') !== false;
assert_test($view_uses_academic_group_id, "Designations view reuses academic_group_id (not department_id)", $passed, $failed);

$view_no_dept_id = strpos($view_content, 'department_id') === false;
assert_test($view_no_dept_id, "Designations view does NOT use department_id", $passed, $failed);

echo "\n======================================================================\n";
echo " TEST SUMMARY: $passed PASSED, $failed FAILED\n";
echo "======================================================================\n";

if ($failed === 0) {
    echo "\n>>> ALL ACCEPTANCE CRITERIA VERIFIED SUCCESSFULLY!\n\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED!\n\n";
    exit(1);
}
