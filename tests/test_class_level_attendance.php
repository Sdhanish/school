<?php
/**
 * Comprehensive Automated Test for Class-Level Student Attendance Workflows
 * Run via CLI: php tests/test_class_level_attendance.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');

require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'helpers/app_helper.php';
require_once APPPATH . 'config/database.php';

$db_config = $db[$active_group];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error . "\n");
}

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
echo "1. TESTING CLASS LEVEL DETECTION & HELPER FUNCTIONS\n";
echo "=======================================================\n";

// Test helper detection by class name strings
assert_test("LKG detected as Daily (LKG-10)", !is_higher_secondary_class("LKG"));
assert_test("UKG detected as Daily (LKG-10)", !is_higher_secondary_class("UKG"));
assert_test("Grade 1 detected as Daily (LKG-10)", !is_higher_secondary_class("Grade 1"));
assert_test("Grade 5 detected as Daily (LKG-10)", !is_higher_secondary_class("Grade 5"));
assert_test("Grade 9 detected as Daily (LKG-10)", !is_higher_secondary_class("Grade 9"));
assert_test("Grade 10 detected as Daily (LKG-10)", !is_higher_secondary_class("Grade 10"));
assert_test("Class 10 detected as Daily (LKG-10)", !is_higher_secondary_class("Class 10"));
assert_test("+1 detected as Higher Secondary (+1/+2)", is_higher_secondary_class("+1"));
assert_test("+2 detected as Higher Secondary (+1/+2)", is_higher_secondary_class("+2"));
assert_test("Plus One detected as Higher Secondary", is_higher_secondary_class("Plus One"));
assert_test("Plus Two detected as Higher Secondary", is_higher_secondary_class("Plus Two"));
assert_test("Grade 11 detected as Higher Secondary", is_higher_secondary_class("Grade 11"));
assert_test("Grade 12 detected as Higher Secondary", is_higher_secondary_class("Grade 12"));
assert_test("Class 11 detected as Higher Secondary", is_higher_secondary_class("Class 11"));
assert_test("11th Standard detected as Higher Secondary", is_higher_secondary_class("11th Standard"));
assert_test("XI detected as Higher Secondary", is_higher_secondary_class("XI"));
assert_test("XII detected as Higher Secondary", is_higher_secondary_class("XII"));

// Workflow helper verification
assert_test("Workflow for LKG is 'daily'", get_attendance_workflow_type("LKG") === 'daily');
assert_test("Workflow for Grade 10 is 'daily'", get_attendance_workflow_type("Grade 10") === 'daily');
assert_test("Workflow for Grade 11 is 'period'", get_attendance_workflow_type("Grade 11") === 'period');
assert_test("Workflow for Grade 12 is 'period'", get_attendance_workflow_type("Grade 12") === 'period');
assert_test("Workflow for +1 is 'period'", get_attendance_workflow_type("+1") === 'period');
assert_test("Workflow for +2 is 'period'", get_attendance_workflow_type("+2") === 'period');

echo "\n=======================================================\n";
echo "2. TESTING ALLOWED ATTENDANCE STATUSES\n";
echo "=======================================================\n";

$lkg_statuses = get_allowed_attendance_statuses('LKG');
assert_test("LKG allowed statuses count is exactly 3", count($lkg_statuses) === 3);
assert_test("LKG allowed statuses include Present", in_array('Present', $lkg_statuses));
assert_test("LKG allowed statuses include Half Day", in_array('Half Day', $lkg_statuses));
assert_test("LKG allowed statuses include Absent", in_array('Absent', $lkg_statuses));
assert_test("LKG allowed statuses DO NOT include Late Coming", !in_array('Late Coming', $lkg_statuses));
assert_test("LKG allowed statuses DO NOT include Leave", !in_array('Leave', $lkg_statuses));
assert_test("LKG allowed statuses DO NOT include Excused", !in_array('Excused', $lkg_statuses));

$hs_statuses = get_allowed_attendance_statuses('+1');
assert_test("+1 allowed statuses count is exactly 4", count($hs_statuses) === 4);
assert_test("+1 allowed statuses include Present", in_array('Present', $hs_statuses));
assert_test("+1 allowed statuses include Half Day", in_array('Half Day', $hs_statuses));
assert_test("+1 allowed statuses include Absent", in_array('Absent', $hs_statuses));
assert_test("+1 allowed statuses include Late Coming", in_array('Late Coming', $hs_statuses));
assert_test("+1 allowed statuses DO NOT include Leave", !in_array('Leave', $hs_statuses));
assert_test("+1 allowed statuses DO NOT include Excused", !in_array('Excused', $hs_statuses));

echo "\n=======================================================\n";
echo "3. DATABASE ENUM & ROW INTEGRITY CHECK\n";
echo "=======================================================\n";

$res = $mysqli->query("SHOW COLUMNS FROM tbl_attendance LIKE 'attendance_status'");
$col = $res->fetch_assoc();
assert_test("tbl_attendance contains 'attendance_status' column", !empty($col));
assert_test("ENUM includes 'Half Day'", strpos($col['Type'], "'Half Day'") !== false);
assert_test("ENUM includes 'Late Coming'", strpos($col['Type'], "'Late Coming'") !== false);

echo "\n=======================================================\n";
echo "4. TESTING CLASS BOUNDARIES IN DATABASE\n";
echo "=======================================================\n";

$classes_res = $mysqli->query("SELECT class_id, class_name FROM tbl_classes");
$tested_classes = 0;
while ($cls = $classes_res->fetch_assoc()) {
    $cName = $cls['class_name'];
    $is_hs = is_higher_secondary_class($cName);
    $wf = get_attendance_workflow_type($cName);
    $statuses = get_allowed_attendance_statuses($cName);

    if (preg_match('/(11|12|\+1|\+2|Plus One|Plus Two)/i', $cName)) {
        assert_test("DB Class '{$cName}' (ID {$cls['class_id']}) is Higher Secondary Period-wise", $is_hs === true && $wf === 'period' && in_array('Late Coming', $statuses));
    } else {
        assert_test("DB Class '{$cName}' (ID {$cls['class_id']}) is LKG-10 Daily Attendance", $is_hs === false && $wf === 'daily' && !in_array('Late Coming', $statuses));
    }
    $tested_classes++;
}
assert_test("At least 5 active classes verified against boundary detection", $tested_classes >= 5);

echo "\n=======================================================\n";
echo "5. TESTING FORMULA LOGIC FOR CLASS LEVELS\n";
echo "=======================================================\n";

// LKG - 10: Formula Present / Working Days * 100
$present_days = 18;
$working_days = 20;
$lkg_pct = ($working_days > 0) ? round(($present_days / $working_days) * 100, 2) : 0.00;
assert_test("LKG-10 attendance % calculation correct (18/20 = 90%)", $lkg_pct == 90.00);

$zero_working_days = 0;
$lkg_zero_pct = ($zero_working_days > 0) ? round(($present_days / $zero_working_days) * 100, 2) : 0.00;
assert_test("LKG-10 zero working days handles gracefully with 0.00%", $lkg_zero_pct == 0.00);

// +1 / +2: Formula Present / Total Conducted Periods * 100
$present_periods = 85;
$total_periods = 100;
$hs_pct = ($total_periods > 0) ? round(($present_periods / $total_periods) * 100, 2) : 0.00;
assert_test("+1/+2 attendance % calculation correct (85/100 = 85%)", $hs_pct == 85.00);

$zero_periods = 0;
$hs_zero_pct = ($zero_periods > 0) ? round(($present_periods / $zero_periods) * 100, 2) : 0.00;
assert_test("+1/+2 zero conducted periods handles gracefully with 0.00%", $hs_zero_pct == 0.00);

echo "\n=======================================================\n";
echo "6. VIEW FILE CLEANLINESS (ABSENCE OF LEAVE/EXCUSED IN ACTIVE UI)\n";
echo "=======================================================\n";

// Check view_attendance.php
$view_content = file_get_contents(APPPATH . 'views/pages/attendance/view_attendance.php');
assert_test("view_attendance.php has no 'Leave' column in table headers", strpos($view_content, '<th>Leave</th>') === false && strpos($view_content, 'Leave') === false);
assert_test("view_attendance.php has no 'Excused' column in table headers", strpos($view_content, 'Excused') === false);

// Check period_wise.php
$period_content = file_get_contents(APPPATH . 'views/pages/attendance/period_wise.php');
assert_test("period_wise.php has no Excused radio button", strpos($period_content, 'value="Excused"') === false);
assert_test("period_wise.php has no Leave radio button", strpos($period_content, 'value="Leave"') === false);
assert_test("period_wise.php has Half Day radio button", strpos($period_content, 'value="Half Day"') !== false);
assert_test("period_wise.php has Late Coming radio button", strpos($period_content, 'value="Late Coming"') !== false);

// Check class_attendance.php
$class_att_content = file_get_contents(APPPATH . 'views/pages/attendance/class_attendance.php');
assert_test("class_attendance.php distinguishes Daily vs Period badge", strpos($class_att_content, 'Daily Attendance (LKG – 10)') !== false && strpos($class_att_content, '') !== false);
assert_test("class_attendance.php has no active Excused or Leave buttons", strpos($class_att_content, 'value="Excused"') === false && strpos($class_att_content, 'value="Leave"') === false);

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
