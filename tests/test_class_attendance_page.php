<?php
/**
 * Comprehensive verification of Class Attendance SQL queries and workflows
 * Run via CLI: php tests/test_class_attendance_page.php
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
    public $session;
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

require_once APPPATH . 'helpers/app_helper.php';
require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'models/Division_model.php';
require_once APPPATH . 'models/Section_model.php';
require_once APPPATH . 'models/Attendance_model.php';

$mock_ci->load->model('Division_model');
$mock_ci->load->model('Section_model');
$att_model = new Attendance_model();
$att_model->db = $db_conn;

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
echo "1. TESTING CLASS ATTENDANCE OVERVIEW ACROSS ALL CLASSES\n";
echo "=======================================================\n";

$classes = $db_conn->query("SELECT class_id, class_name FROM tbl_classes WHERE status = 1")->result();

foreach ($classes as $c) {
    $cid = (int)$c->class_id;
    $cName = $c->class_name;
    $is_hs = is_higher_secondary_class($cid);

    try {
        $overview = $att_model->get_class_overview('2026-03-03', 1, $cid);
        assert_test("SQL query succeeded for Class: '{$cName}' (ID {$cid})", is_array($overview));
        assert_test("Class '{$cName}' has at least 1 section (defaults to A)", count($overview) >= 1);

        foreach ($overview as $sec) {
            assert_test("Section is '{$sec->section_name}' with non-negative student count ({$sec->total_students})", $sec->total_students >= 0);
            assert_test("Present count is valid ({$sec->present_count})", $sec->present_count >= 0);
            assert_test("Half Day count is valid ({$sec->half_day_count})", $sec->half_day_count >= 0);
            assert_test("Absent count is valid ({$sec->absent_count})", $sec->absent_count >= 0);
            assert_test("Excused count is strictly 0", (int)$sec->excused_count === 0);

            if ($is_hs) {
                assert_test("+1/+2 Class '{$cName}' retains late_count property", property_exists($sec, 'late_count'));
            } else {
                assert_test("LKG-10 Class '{$cName}' forces late_count to 0", $sec->late_count === 0);
            }
        }
    } catch (Throwable $e) {
        assert_test("SQL query failed for Class: '{$cName}' (ID {$cid}): " . $e->getMessage(), false);
    }
}

echo "\n=======================================================\n";
echo "2. TESTING GET_CLASS_OVERVIEW WITH NULL CLASS (DASHBOARD MODE)\n";
echo "=======================================================\n";

try {
    $all_overview = $att_model->get_class_overview('2026-03-03', 1, NULL);
    assert_test("Overview across all classes succeeded", is_array($all_overview) && count($all_overview) > 0);
} catch (Throwable $e) {
    assert_test("Overview across all classes failed: " . $e->getMessage(), false);
}

echo "\n=======================================================\n";
echo "3. TESTING ATTENDANCE REPORTS SQL QUERIES\n";
echo "=======================================================\n";

try {
    $rep_sum = $att_model->get_reports_summary(1);
    assert_test("get_reports_summary executed without SQL syntax errors", is_array($rep_sum));
} catch (Throwable $e) {
    assert_test("get_reports_summary error: " . $e->getMessage(), false);
}

try {
    $st_rep = $att_model->get_student_report(array('academic_year_id' => 1));
    assert_test("get_student_report executed without SQL syntax errors", is_array($st_rep));
} catch (Throwable $e) {
    assert_test("get_student_report error: " . $e->getMessage(), false);
}

try {
    $mo_rep = $att_model->get_monthly_report(array('academic_year_id' => 1));
    assert_test("get_monthly_report executed without SQL syntax errors", is_array($mo_rep));
} catch (Throwable $e) {
    assert_test("get_monthly_report error: " . $e->getMessage(), false);
}

try {
    $per_rep = $att_model->get_period_wise_report(array('academic_year_id' => 1));
    assert_test("get_period_wise_report executed without SQL syntax errors", is_array($per_rep));
} catch (Throwable $e) {
    assert_test("get_period_wise_report error: " . $e->getMessage(), false);
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
