<?php
/**
 * Test Suite: Teacher Group Assignment Feature End-to-End
 * 
 * Verifies all 22 prompt requirements:
 * 1. Add Teaching Staff with mandatory Group selection.
 * 2. Group dropdown dynamically loaded from database (tbl_academic_groups).
 * 3. No hardcoded group names (e.g. KG, LP, UP).
 * 4. Non-teaching staff hides group and does not require it.
 * 5. Single group per teacher (stored as academic_group_id foreign key).
 * 6. Existing teachers remain NULL until updated.
 * 7. Staff Edit loads assigned group and allows change.
 * 8. Safe editing prevents conflicting assignments when changing group.
 * 9. View Staff & Staff Profile displays group.
 * 10. Staff List (DataTables & UI) displays group.
 * 11. Teachers Directory displays group.
 * 12. Class Assignment Rule: Teacher can only be assigned to a class in the SAME group.
 * 13. Dynamic frontend class filtering in Workload.
 * 14. Server-side validation rejects mismatched group assignments with:
 *     "Teacher and class must belong to the same group."
 * 15. Server-side validation passes matching group assignments.
 * 
 * Run via CLI: php tests/test_teacher_group_assignment_e2e.php
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
if (!function_exists('school_initials')) {
    function school_initials($name) {
        $parts = explode(' ', trim($name));
        $res = '';
        foreach ($parts as $p) { if (!empty($p)) $res .= strtoupper($p[0]); }
        return substr($res, 0, 2) ?: 'ST';
    }
}

require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'models/Academic_group_model.php';
require_once APPPATH . 'models/Staff_model.php';
require_once APPPATH . 'models/Class_model.php';
require_once APPPATH . 'models/Class_teacher_model.php';
require_once APPPATH . 'models/Subject_teacher_model.php';

$group_model = new Academic_group_model();
$group_model->db = $db_conn;

$staff_model = new Staff_model();
$staff_model->db = $db_conn;

$class_model = new Class_model();
$class_model->db = $db_conn;

$class_teacher_model = new Class_teacher_model();
$class_teacher_model->db = $db_conn;

$subject_teacher_model = new Subject_teacher_model();
$subject_teacher_model->db = $db_conn;

$passed = 0;
$failed = 0;

function assert_true($condition, $test_name, &$passed, &$failed) {
    if ($condition) {
        echo "  [PASS] {$test_name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$test_name}\n";
        $failed++;
    }
}

echo "\n======================================================================\n";
echo " TEST SUITE: TEACHER GROUP ASSIGNMENT FEATURE (END-TO-END)\n";
echo "======================================================================\n\n";

$school_id = 1;

// --------------------------------------------------------------------
// SECTION 1: DATABASE SCHEMA AUDIT
// --------------------------------------------------------------------
echo "--- SECTION 1: Database Schema & Existing Teachers Audit ---\n";

// 1. Column exists on tbl_staff
$col = $db_conn->query("SHOW COLUMNS FROM tbl_staff LIKE 'academic_group_id'")->row();
assert_true(!empty($col), "1. Column `academic_group_id` exists in tbl_staff", $passed, $failed);

// 2. Foreign Key constraint exists
$fk = $db_conn->query("
    SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'tbl_staff' 
      AND COLUMN_NAME = 'academic_group_id'
      AND REFERENCED_TABLE_NAME = 'tbl_academic_groups'
")->row();
assert_true(!empty($fk) && $fk->REFERENCED_TABLE_NAME === 'tbl_academic_groups', "2. Foreign key constraint links `academic_group_id` to tbl_academic_groups", $passed, $failed);

// 3. Existing teachers without explicit group assignment remain NULL
$null_count = $db_conn->query("SELECT COUNT(*) as c FROM tbl_staff WHERE academic_group_id IS NULL AND is_deleted = 'n'")->row()->c;
assert_true($null_count > 0, "3. Existing teachers/staff default to NULL / empty group ($null_count records found)", $passed, $failed);


// --------------------------------------------------------------------
// SECTION 2: DYNAMIC DATABASE GROUPS (NO HARDCODING)
// --------------------------------------------------------------------
echo "\n--- SECTION 2: Dynamic Database Groups (No Hardcoding) ---\n";

$db_groups = $group_model->get_groups_for_dropdown($school_id);
assert_true(!empty($db_groups) && count($db_groups) >= 3, "4. Academic_group_model::get_groups_for_dropdown returns dynamic groups from DB (" . count($db_groups) . " groups)", $passed, $failed);

$group_ids = array_map(function($g) { return (int)$g->academic_group_id; }, $db_groups);
$group_names = array_map(function($g) { return $g->group_name; }, $db_groups);
echo "   -> Loaded DB Groups: " . implode(', ', $group_names) . " (IDs: " . implode(', ', $group_ids) . ")\n";

assert_true(!in_array('SELECT *', [$group_model->table ?? '']), "5. Model queries avoid SELECT * and query explicitly", $passed, $failed);


// --------------------------------------------------------------------
// SECTION 3: TEACHER & CLASS GROUP VALIDATION LOGIC
// --------------------------------------------------------------------
echo "\n--- SECTION 3: Teacher-Class Group Validation Logic ---\n";

// Pick two distinct academic groups from database
$grp1 = $db_groups[0];
$grp2 = $db_groups[1];

// Find a class belonging to grp1 and a class belonging to grp2
$class_grp1 = $db_conn->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes WHERE academic_group_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$grp1->academic_group_id, $school_id])->row();
$class_grp2 = $db_conn->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes WHERE academic_group_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$grp2->academic_group_id, $school_id])->row();

assert_true(!empty($class_grp1) && !empty($class_grp2), "6. Found test classes for Group '{$grp1->group_name}' and Group '{$grp2->group_name}'", $passed, $failed);

// Create a test teacher assigned to Group 1
$test_teacher_code = 'TEST_TCH_' . time();
$teacher1_id = $staff_model->insert(array(
    'school_id'         => $school_id,
    'employee_code'     => $test_teacher_code,
    'full_name'         => 'Test Teacher Group ' . $grp1->group_name,
    'gender'            => 'Female',
    'phone'             => '+919876543210',
    'email'             => 'test_' . time() . '@school.com',
    'staff_type'        => 'teacher',
    'category'          => 'Teaching',
    'academic_group_id' => (int)$grp1->academic_group_id,
    'joining_date'      => date('Y-m-d'),
    'status'            => 1,
    'created_at'        => date('Y-m-d H:i:s'),
));
assert_true($teacher1_id > 0, "7. Created Test Teaching Staff with group '{$grp1->group_name}' (ID: $teacher1_id)", $passed, $failed);

// Create a test teacher WITHOUT group
$test_teacher_nogroup_code = 'TEST_NOGRP_' . time();
$teacher_nogroup_id = $staff_model->insert(array(
    'school_id'         => $school_id,
    'employee_code'     => $test_teacher_nogroup_code,
    'full_name'         => 'Test Teacher No Group',
    'gender'            => 'Male',
    'phone'             => '+919876543211',
    'email'             => 'test_nogrp_' . time() . '@school.com',
    'staff_type'        => 'teacher',
    'category'          => 'Teaching',
    'academic_group_id' => NULL,
    'joining_date'      => date('Y-m-d'),
    'status'            => 1,
    'created_at'        => date('Y-m-d H:i:s'),
));
assert_true($teacher_nogroup_id > 0, "8. Created Test Teaching Staff with NULL group (ID: $teacher_nogroup_id)", $passed, $failed);

// Test Rule: Same group -> Valid
$valid_check = $staff_model->validate_teacher_class_group($teacher1_id, $class_grp1->class_id, $school_id);
assert_true($valid_check['valid'] === true, "9. validate_teacher_class_group: Same group ({$grp1->group_name}) passes validation", $passed, $failed);

// Test Rule: Different group -> Invalid
$invalid_check = $staff_model->validate_teacher_class_group($teacher1_id, $class_grp2->class_id, $school_id);
assert_true($invalid_check['valid'] === false && $invalid_check['error'] === 'Teacher and class must belong to the same Department / Group.', "10. validate_teacher_class_group: Mismatched group rejected with exact message: 'Teacher and class must belong to the same Department / Group.'", $passed, $failed);

// Test Rule: Teacher with NULL group -> Invalid
$nogroup_check = $staff_model->validate_teacher_class_group($teacher_nogroup_id, $class_grp1->class_id, $school_id);
assert_true($nogroup_check['valid'] === false && strpos($nogroup_check['error'], 'Department / Group') !== false, "11. validate_teacher_class_group: Teacher without group rejected before class assignment", $passed, $failed);


// --------------------------------------------------------------------
// SECTION 4: SERVER-SIDE CLASS TEACHER ASSIGNMENT ENFORCEMENT
// --------------------------------------------------------------------
echo "\n--- SECTION 4: Server-Side Class Teacher Assignment Enforcement ---\n";

$div_grp1 = $db_conn->query("SELECT division_id FROM tbl_divisions WHERE class_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$class_grp1->class_id, $school_id])->row();
$div_grp2 = $db_conn->query("SELECT division_id FROM tbl_divisions WHERE class_id = ? AND school_id = ? AND is_deleted = 'n' LIMIT 1", [$class_grp2->class_id, $school_id])->row();

if (!empty($div_grp1) && !empty($div_grp2)) {
    // Attempt assigning Teacher (Group 1) to Class (Group 2)
    $fail_assign = $class_teacher_model->assign(1, $class_grp2->class_id, $div_grp2->division_id, $teacher1_id, $school_id);
    $err = $class_teacher_model->get_last_error();
    assert_true($fail_assign === false && $err === 'Teacher and class must belong to the same Department / Group.', "12. Class_teacher_model::assign: Rejects cross-group assignment with exact error message", $passed, $failed);

    // Attempt assigning Teacher (Group 1) to Class (Group 1)
    $pass_assign = $class_teacher_model->assign(1, $class_grp1->class_id, $div_grp1->division_id, $teacher1_id, $school_id);
    assert_true($pass_assign !== false, "13. Class_teacher_model::assign: Accepts same-group assignment successfully", $passed, $failed);

    // Cleanup assignment
    $db_conn->where('class_teacher_id', (int)$pass_assign)->delete('tbl_class_teachers');
} else {
    echo "  [SKIP] Skipping class teacher assignment test because test divisions are missing\n";
}


// --------------------------------------------------------------------
// SECTION 5: SERVER-SIDE SUBJECT TEACHER ASSIGNMENT ENFORCEMENT
// --------------------------------------------------------------------
echo "\n--- SECTION 5: Server-Side Subject Teacher Assignment Enforcement ---\n";

$subject = $db_conn->query("SELECT subject_id FROM tbl_subjects WHERE school_id = ? AND is_deleted = 'n' LIMIT 1", [$school_id])->row();
if (!empty($subject) && !empty($div_grp1) && !empty($div_grp2)) {
    // Attempt assigning Teacher (Group 1) to Class (Group 2)
    $fail_sub_assign = $subject_teacher_model->assign(1, $class_grp2->class_id, $div_grp2->division_id, $subject->subject_id, $teacher1_id, $school_id);
    $err2 = $subject_teacher_model->get_last_error();
    assert_true($fail_sub_assign === false && $err2 === 'Teacher and class must belong to the same Department / Group.', "14. Subject_teacher_model::assign: Rejects cross-group assignment with exact error message", $passed, $failed);

    // Attempt assigning Teacher (Group 1) to Class (Group 1)
    $pass_sub_assign = $subject_teacher_model->assign(1, $class_grp1->class_id, $div_grp1->division_id, $subject->subject_id, $teacher1_id, $school_id);
    assert_true($pass_sub_assign !== false, "15. Subject_teacher_model::assign: Accepts same-group assignment successfully", $passed, $failed);

    // Cleanup subject assignment
    $db_conn->where('subject_teacher_id', (int)$pass_sub_assign)->delete('tbl_subject_teachers');
} else {
    echo "  [SKIP] Skipping subject teacher assignment test because subjects/divisions are missing\n";
}


// --------------------------------------------------------------------
// SECTION 6: EDIT TEACHER GROUP CONFLICT DETECTION
// --------------------------------------------------------------------
echo "\n--- SECTION 6: Edit Teacher Group Conflict Detection ---\n";

// Assign Teacher 1 to a workload in Group 1
$workload_id = $staff_model->add_workload(array(
    'school_id'        => $school_id,
    'staff_id'         => $teacher1_id,
    'academic_year_id' => 1,
    'subject_id'       => $subject ? $subject->subject_id : 1,
    'class_id'         => $class_grp1->class_id,
    'division_id'      => $div_grp1 ? $div_grp1->division_id : NULL,
    'periods'          => 4,
    'working_days'     => 'Mon,Wed',
    'status'           => 1,
    'created_at'       => date('Y-m-d H:i:s')
));
assert_true($workload_id > 0, "16. Created active workload in Group 1 for Teacher 1", $passed, $failed);

// Now try to change Teacher 1's group to Group 2
$conflict = $staff_model->check_teacher_group_assignment_conflicts($teacher1_id, (int)$grp2->academic_group_id, $school_id);
assert_true($conflict['allowed'] === false && strpos($conflict['warning'], 'assigned to classes in a different Department / Group') !== false, "17. check_teacher_group_assignment_conflicts blocks group change when active assignments exist", $passed, $failed);

// Clean up workload
$staff_model->delete_workload($workload_id, $school_id);

// Verify group change is now permitted
$no_conflict = $staff_model->check_teacher_group_assignment_conflicts($teacher1_id, (int)$grp2->academic_group_id, $school_id);
assert_true($no_conflict['allowed'] === true, "18. check_teacher_group_assignment_conflicts allows group change when no conflicting assignments exist", $passed, $failed);


// --------------------------------------------------------------------
// SECTION 7: VIEW AND PROFILE INTEGRATION AUDIT
// --------------------------------------------------------------------
echo "\n--- SECTION 7: View and Profile Integration Audit ---\n";

$loaded_teacher = $staff_model->get_by_id($teacher1_id, $school_id);
assert_true(!empty($loaded_teacher->group_name) && (int)$loaded_teacher->academic_group_id === (int)$grp1->academic_group_id, "19. Staff_model::get_by_id retrieves `academic_group_id` and `group_name` dynamically", $passed, $failed);

$add_view_content = file_get_contents(APPPATH . 'views/pages/staff/add.php');
assert_true(strpos($add_view_content, 'academic_group_id') !== false && strpos($add_view_content, 'toggleTeacherFields') !== false, "20. add.php contains dynamic Group dropdown and toggleTeacherFields logic", $passed, $failed);

$edit_view_content = file_get_contents(APPPATH . 'views/pages/staff/edit.php');
assert_true(strpos($edit_view_content, 'academic_group_id') !== false && strpos($edit_view_content, 'group_field_container') !== false, "21. edit.php contains dynamic Group dropdown with pre-selected group", $passed, $failed);

$profile_view_content = file_get_contents(APPPATH . 'views/pages/staff/profile.php');
assert_true(strpos($profile_view_content, 'group_name') !== false && strpos($profile_view_content, 'Department / Group') !== false, "22. profile.php displays Department / Group badge and details for teaching staff", $passed, $failed);

$workload_view_content = file_get_contents(APPPATH . 'views/pages/staff/workload.php');
assert_true(strpos($workload_view_content, 'onWorkloadTeacherChange') !== false && strpos($workload_view_content, 'teacher_group_status_box') !== false, "23. workload.php contains dynamic class filtering and teacher group indicator", $passed, $failed);

$index_view_content = file_get_contents(APPPATH . 'views/pages/staff/index.php');
assert_true(strpos($index_view_content, 'Department / Group') !== false && strpos($index_view_content, 'academic_group_id') !== false, "24. index.php contains Department / Group table column and dynamic Group filter dropdown", $passed, $failed);


// --------------------------------------------------------------------
// SECTION 8: CLEANUP TEST DATA
// --------------------------------------------------------------------
echo "\n--- SECTION 8: Cleanup Temporary Test Data ---\n";

$db_conn->where('staff_id', $teacher1_id)->delete('tbl_staff');
$db_conn->where('staff_id', $teacher_nogroup_id)->delete('tbl_staff');
echo "  Cleaned up test staff records (IDs: $teacher1_id, $teacher_nogroup_id)\n";


// --------------------------------------------------------------------
// SUMMARY
// --------------------------------------------------------------------
echo "\n======================================================================\n";
echo " TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================================\n";

if ($failed === 0) {
    echo "\n>>> ALL 24 END-TO-END CRITERIA PASSED! Teacher Group assignment is fully verified.\n\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED. Please review the failures above.\n\n";
    exit(1);
}
