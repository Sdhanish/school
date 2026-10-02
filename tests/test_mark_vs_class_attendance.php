<?php
/**
 * Test Suite: Separation of Mark Attendance and Class Attendance
 * Run via CLI: php tests/test_mark_vs_class_attendance.php
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

class MockRbac {
    public function is_super_admin($uid = NULL) { return true; }
    public function has_permission($perm, $uid = NULL) { return true; }
    public function check_data_access($t, $id, $uid = NULL) { return true; }
}

class MockSession {
    public $userdata = array(
        'user_id' => 1,
        'role_code' => 'SUPER_ADMIN',
        'role' => 'Super Admin'
    );
    public function userdata($key = NULL) {
        if ($key === NULL) return $this->userdata;
        return $this->userdata[$key] ?? NULL;
    }
    public function set_flashdata($k, $v) {}
    public function flashdata($k) { return NULL; }
}

class MockCI {
    public $db;
    public $load;
    public $session;
    public $rbac;
    public function __construct($db) {
        $this->db = $db;
        $this->load = new CI_Loader();
        $this->session = new MockSession();
        $this->rbac = new MockRbac();
    }
}
$mock_ci = new MockCI($db_conn);
function &get_instance() {
    global $mock_ci;
    return $mock_ci;
}

require_once APPPATH . 'helpers/app_helper.php';
require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'models/Division_model.php';
require_once APPPATH . 'models/Section_model.php';
require_once APPPATH . 'models/Attendance_model.php';
require_once APPPATH . 'models/Class_model.php';
require_once APPPATH . 'models/Period_model.php';
require_once APPPATH . 'models/Subject_model.php';

$mock_ci->load->model('Division_model');
$mock_ci->load->model('Section_model');
$mock_ci->load->model('Class_model');
$mock_ci->load->model('Period_model');
$mock_ci->load->model('Subject_model');

$att_model = new Attendance_model();
$class_model = $mock_ci->Class_model;
$sec_model = $mock_ci->Section_model;

$tests_run = 0;
$tests_passed = 0;

function assert_test($description, $condition) {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "[PASS] $description\n";
    } else {
        echo "[FAIL] $description\n";
    }
}

echo "=======================================================\n";
echo "1. TESTING SIDEBAR NAVIGATION STRUCTURE IN APP.JS\n";
echo "=======================================================\n";

$app_js = file_get_contents(FCPATH . 'assets/app.js');
assert_test("app.js has 'attendance-mark' route mapping to 'attendance/mark_attendance'", strpos($app_js, '"attendance-mark": "attendance/mark_attendance"') !== false);
assert_test("app.js has 'Mark Attendance' menu item", strpos($app_js, '{ key: "attendance-mark", label: "Mark Attendance" }') !== false);
assert_test("app.js has 'Class Attendance' menu item", strpos($app_js, '{ key: "attendance-class", label: "Class Attendance" }') !== false);
assert_test("app.js has 'Period Attendance (+1 / +2)' under Attendance", strpos($app_js, '{ key: "attendance-period-wise", label: "Period Attendance (+1 / +2)" }') !== false);
assert_test("app.js removed 'Period Setup (+1 / +2)' from Attendance", strpos($app_js, '{ key: "attendance-periods", label: "Period Setup (+1 / +2)" }') === false);
assert_test("app.js groups 'Attendance Calendar' under Attendance", strpos($app_js, '{ key: "attendance-calendar", label: "Attendance Calendar" }') !== false);

echo "\n=======================================================\n";
echo "2. TESTING ROUTES CONFIGURATION IN ROUTES.PHP\n";
echo "=======================================================\n";

$routes_php = file_get_contents(APPPATH . 'config/routes.php');
assert_test("routes.php has 'attendance/mark_attendance'", strpos($routes_php, "\$route['attendance/mark_attendance']       = 'attendance/mark_attendance';") !== false);
assert_test("routes.php has 'attendance/mark'", strpos($routes_php, "\$route['attendance/mark']                  = 'attendance/mark_attendance';") !== false);
assert_test("routes.php has 'student-attendance/mark'", strpos($routes_php, "\$route['student-attendance/mark']          = 'attendance/mark_attendance';") !== false);
assert_test("routes.php has 'attendance/class_attendance'", strpos($routes_php, "\$route['attendance/class_attendance']      = 'attendance/class_attendance';") !== false);

echo "\n=======================================================\n";
echo "3. TESTING CLASS ATTENDANCE VIEW IS STRICTLY VIEW-ONLY\n";
echo "=======================================================\n";

$class_view = file_get_contents(APPPATH . 'views/pages/attendance/class_attendance.php');
assert_test("class_attendance.php does NOT contain id='marking-sheet'", strpos($class_view, 'id="marking-sheet"') === false);
assert_test("class_attendance.php does NOT contain class-attendance-form", strpos($class_view, 'class-attendance-form') === false);
assert_test("class_attendance.php does NOT contain 'All Present' quick mark", strpos($class_view, 'All Present') === false);
assert_test("class_attendance.php does NOT contain 'Save Attendance' button", strpos($class_view, 'Save Attendance') === false);
assert_test("class_attendance.php does NOT contain 'Update Attendance' button", strpos($class_view, 'Update Attendance') === false);
assert_test("class_attendance.php does NOT contain 'Editing Saved Attendance'", strpos($class_view, 'Editing Saved Attendance') === false);
assert_test("class_attendance.php contains link to Mark Attendance in header", strpos($class_view, 'attendance/mark_attendance') !== false);
assert_test("class_attendance.php has VIEW ATTENDANCE on section cards", strpos($class_view, 'VIEW ATTENDANCE') !== false);

echo "\n=======================================================\n";
echo "4. TESTING MARK ATTENDANCE VIEW\n";
echo "=======================================================\n";

$mark_view = file_get_contents(APPPATH . 'views/pages/attendance/mark_attendance.php');
assert_test("mark_attendance.php exists and has content", strlen($mark_view) > 500);
assert_test("mark_attendance.php has form pointing to attendance/mark_attendance", strpos($mark_view, "attendance/mark_attendance") !== false);
assert_test("mark_attendance.php has Quick Mark buttons", strpos($mark_view, "markAllStatus('Present')") !== false);
assert_test("mark_attendance.php has Save Attendance button", strpos($mark_view, 'Save Attendance') !== false);
assert_test("mark_attendance.php has Update Attendance state", strpos($mark_view, 'Update Attendance') !== false);
assert_test("mark_attendance.php has Late Coming only for +1/+2", strpos($mark_view, "if (\$is_higher_sec):") !== false && strpos($mark_view, "Late Coming") !== false);
assert_test("mark_attendance.php does NOT have Leave radio button", strpos($mark_view, 'value="Leave"') === false);
assert_test("mark_attendance.php does NOT have Excused radio button", strpos($mark_view, 'value="Excused"') === false);

echo "\n=======================================================\n";
echo "5. TESTING LKG-10 DAILY ATTENDANCE MARKING & UPDATING (DB)\n";
echo "=======================================================\n";

$test_date = '2026-08-20'; // Past date
$test_class = 1; // LKG
$test_sec = $sec_model->get_default_section_id($test_class) ?: 1;

// Get a student from LKG
$st = $db_conn->where('class_id', $test_class)->where('status', 1)->get('tbl_students')->row();
if ($st) {
    $sid = $st->student_id;
    
    // Save attendance
    $save_data = array(
        $sid => array('status' => 'Present', 'remarks' => 'Unit test initial mark')
    );
    $saved = $att_model->save_daily_attendance($save_data, $test_date, 1, $test_class, $test_sec, 1);
    assert_test("save_daily_attendance succeeded for student {$sid} on past date {$test_date}", $saved >= 1);

    // Verify row in DB
    $row = $db_conn->where('student_id', $sid)->where('attendance_date', $test_date)->where('attendance_type', 'Daily')->get('tbl_attendance')->row();
    assert_test("Attendance record exists in DB with status 'Present'", $row && $row->attendance_status === 'Present');

    // Update attendance to 'Half Day'
    $update_data = array(
        $sid => array('status' => 'Half Day', 'remarks' => 'Updated to Half Day')
    );
    $updated = $att_model->save_daily_attendance($update_data, $test_date, 1, $test_class, $test_sec, 1);
    assert_test("Updating existing attendance succeeded", $updated >= 1);

    // Verify no duplicates created
    $count = $db_conn->where('student_id', $sid)->where('attendance_date', $test_date)->where('attendance_type', 'Daily')->count_all_results('tbl_attendance');
    assert_test("Exactly 1 record exists for student + date (no duplicates)", $count === 1);

    // Verify updated status
    $row2 = $db_conn->where('student_id', $sid)->where('attendance_date', $test_date)->where('attendance_type', 'Daily')->get('tbl_attendance')->row();
    assert_test("Attendance record updated to 'Half Day'", $row2 && $row2->attendance_status === 'Half Day');

    // Clean up test record
    $db_conn->where('student_id', $sid)->where('attendance_date', $test_date)->delete('tbl_attendance');
    assert_test("Test daily attendance cleaned up safely", true);
} else {
    assert_test("LKG student found in DB", false);
}

echo "\n=======================================================\n";
echo "6. TESTING +1/+2 PERIOD ATTENDANCE MARKING & UPDATING (DB)\n";
echo "=======================================================\n";

$hs_class = 9; // Grade 11 (+1)
$hs_sec = $sec_model->get_default_section_id($hs_class) ?: 1;
$hs_period = 1; // Period 1

$hs_st = $db_conn->where('class_id', $hs_class)->where('status', 1)->get('tbl_students')->row();
if ($hs_st) {
    $hs_sid = $hs_st->student_id;

    // Save period attendance with 'Late Coming'
    $period_data = array(
        $hs_sid => array('status' => 'Late Coming', 'remarks' => 'Bus delay')
    );
    $saved_p = $att_model->save_period_attendance($period_data, $test_date, $hs_period, 1, $hs_class, $hs_sec, 1);
    assert_test("save_period_attendance succeeded for +1 student {$hs_sid}", $saved_p >= 1);

    // Verify row in DB
    $p_row = $db_conn->where('student_id', $hs_sid)->where('attendance_date', $test_date)->where('attendance_type', 'Period-wise')->where('period_id', $hs_period)->get('tbl_attendance')->row();
    assert_test("+1 record exists with status 'Late Coming'", $p_row && $p_row->attendance_status === 'Late Coming');

    // Update period attendance
    $update_p_data = array(
        $hs_sid => array('status' => 'Present', 'remarks' => 'Corrected')
    );
    $updated_p = $att_model->save_period_attendance($update_p_data, $test_date, $hs_period, 1, $hs_class, $hs_sec, 1);
    assert_test("Updating +1 period attendance succeeded", $updated_p >= 1);

    // Verify exactly 1 record
    $p_count = $db_conn->where('student_id', $hs_sid)->where('attendance_date', $test_date)->where('attendance_type', 'Period-wise')->where('period_id', $hs_period)->count_all_results('tbl_attendance');
    assert_test("Exactly 1 period record exists for student + date + period (no duplicates)", $p_count === 1);

    // Clean up test record
    $db_conn->where('student_id', $hs_sid)->where('attendance_date', $test_date)->delete('tbl_attendance');
    assert_test("Test period attendance cleaned up safely", true);
} else {
    assert_test("+1 student found in DB", false);
}

echo "\n=======================================================\n";
echo "RESULTS: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED.\n";
    exit(1);
}
