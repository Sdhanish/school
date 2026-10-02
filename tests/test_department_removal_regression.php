<?php
/**
 * Test Suite: Complete Department Removal & Designation Integrity Regression Test
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
require_once APPPATH . 'models/Designation_model.php';

$staff_model = new Staff_model();
$desig_model = new Designation_model();

$passed = 0;
$failed = 0;

function it($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $description\n";
        $passed++;
    } else {
        echo "  [FAIL] $description\n";
        $failed++;
    }
}

echo "========================================================\n";
echo "DEPARTMENT REMOVAL & DESIGNATION INTEGRITY TEST SUITE\n";
echo "========================================================\n\n";

$school_id = 1;

// 1. Staff List & Retrieval
echo "--- Testing Staff Module ---\n";
$staff_all = $staff_model->get_all(['school_id' => $school_id]);
it("Staff get_all returns records without error", is_array($staff_all) && count($staff_all) > 0);
$sample_staff = $staff_all[0];
it("Sample staff has valid staff_id and full_name", !empty($sample_staff->staff_id) && !empty($sample_staff->full_name));
it("Sample staff has designation_name accessible", property_exists($sample_staff, 'designation_name'));

// 2. Staff Profile & Single Fetch
$staff_single = $staff_model->get_by_id($sample_staff->staff_id, $school_id);
it("Staff get_by_id returns valid object", is_object($staff_single) && $staff_single->staff_id == $sample_staff->staff_id);
it("Staff get_by_id contains designation_name", !empty($staff_single->designation_name));

// 3. Staff Registration / Add
$unique_code = 'TEST' . substr(time(), -5);
$test_staff_id = $staff_model->insert([
    'school_id' => $school_id,
    'employee_code' => $unique_code,
    'full_name' => 'Automated Regression Staff',
    'email' => 'reg_test_' . time() . '@school.local',
    'phone' => '98950' . rand(10000, 99999),
    'gender' => 'Male',
    'staff_type' => 'teacher',
    'designation_id' => 1,
    'joining_date' => date('Y-m-d'),
    'status' => 1,
    'is_deleted' => 'n',
    'created_at' => date('Y-m-d H:i:s')
]);
it("Staff insert succeeds without department_id", $test_staff_id > 0);

// 4. Staff Edit / Update
$update_res = $staff_model->update($test_staff_id, [
    'full_name' => 'Automated Regression Staff Updated',
    'qualification' => 'M.Sc, B.Ed',
], $school_id);
it("Staff update succeeds without department_id", $update_res !== false);

$updated_staff = $staff_model->get_by_id($test_staff_id, $school_id);
it("Updated staff reflects changes", $updated_staff->full_name === 'Automated Regression Staff Updated');

// 5. Designation Integrity & CRUD
echo "\n--- Testing Designation Module ---\n";
$desigs = $desig_model->get_all(false, $school_id);
it("Designation get_all returns active records", is_array($desigs) && count($desigs) > 0);

$desig_categories = $desig_model->get_categories($school_id);
it("Designation get_categories returns category list", is_array($desig_categories) && count($desig_categories) > 0);

// Add Designation
$new_desig_name = "QA Automation Lead " . time();
$new_desig_id = $desig_model->insert([
    'school_id' => $school_id,
    'designation_name' => $new_desig_name,
    'category' => 'Teaching',
    'description' => 'Test designation created during regression test',
    'status' => 1,
    'created_at' => date('Y-m-d H:i:s')
]);
it("Designation insert succeeds", $new_desig_id > 0);

// Fetch Designation
$fetched_desig = $desig_model->get_by_id($new_desig_id, $school_id);
it("Designation get_by_id returns correct record", $fetched_desig && $fetched_desig->designation_name === $new_desig_name);

// Edit Designation
$desig_model->update($new_desig_id, [
    'description' => 'Updated test description',
], $school_id);
$re_fetched = $desig_model->get_by_id($new_desig_id, $school_id);
it("Designation update succeeds", $re_fetched->description === 'Updated test description');

// Designation Staff Count
$staff_count_new = $desig_model->count_staff_usage($new_desig_id, $school_id);
it("New designation has 0 staff count", $staff_count_new === 0);

// Delete Designation
$del_res = $desig_model->soft_delete($new_desig_id, $school_id);
it("Designation soft_delete succeeds when unassigned", $del_res === true);

// 6. Teacher Workload & Group
echo "\n--- Testing Teacher Workload & Group ---\n";
$teachers = $staff_model->get_teachers(['school_id' => $school_id]);
it("Staff_model::get_teachers returns valid array", is_array($teachers));

$teacher_group = $staff_model->get_teacher_group($sample_staff->staff_id, $school_id);
it("Staff_model::get_teacher_group runs without error", $teacher_group !== false);

// 7. Attendance
echo "\n--- Testing Staff Attendance ---\n";
$att_list = $staff_model->get_attendance_for_date(date('Y-m-d'), $school_id);
it("Staff_model::get_attendance_for_date returns records without department filter", is_array($att_list));

// 8. Leave Management
echo "\n--- Testing Leave Management ---\n";
$leaves = $staff_model->get_leaves(['school_id' => $school_id], $school_id);
it("Staff_model::get_leaves returns records with explicit columns", is_array($leaves));

// 9. Dashboard Stats
echo "\n--- Testing Staff Dashboard Stats ---\n";
$dashboard_stats = $staff_model->get_dashboard_stats($school_id);
it("Dashboard stats returns total_staff", isset($dashboard_stats->total_staff));
it("Dashboard stats returns total_teachers", isset($dashboard_stats->total_teachers));
it("Dashboard stats returns total_designations", isset($dashboard_stats->total_designations));
it("Dashboard stats returns designations breakdown array", is_array($dashboard_stats->designations));
it("Dashboard stats does not contain departments property", !isset($dashboard_stats->departments));

// Clean up test staff record
$staff_model->soft_delete($test_staff_id, $school_id);

echo "\n========================================================\n";
echo "TEST RESULTS: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";
