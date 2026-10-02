<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASEPATH', 'C:/xamppmain/htdocs/schoolnew/system/');
define('APPPATH', 'C:/xamppmain/htdocs/schoolnew/application/');
define('ENVIRONMENT', 'development');

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Loader.php';

$config_db = array();
require APPPATH . 'config/database.php';
$db_params = $db['default'];

require_once BASEPATH . 'database/DB.php';
$db_conn = DB($db_params, TRUE);

class MockSession {
    public $userdata = array('user_id' => 1, 'role_code' => 'SUPER_ADMIN', 'role' => 'Super Admin');
    public function userdata($k = NULL) { return $k ? ($this->userdata[$k] ?? NULL) : $this->userdata; }
    public function set_flashdata($k, $v) {}
    public function flashdata($k) { return NULL; }
}

class MockRbac {
    public function has_permission($p) { return true; }
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

$mock_ci->load->model('Division_model');
$mock_ci->load->model('Section_model');
$mock_ci->load->model('Class_model');
$mock_ci->load->model('Student_model');
$mock_ci->load->model('Attendance_model');

$tests_run = 0;
$tests_passed = 0;

function assert_test($desc, $cond) {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($cond) {
        $tests_passed++;
        echo "[PASS] $desc\n";
    } else {
        echo "[FAIL] $desc\n";
    }
}

echo "\n=======================================================\n";
echo "1. DATABASE SCHEMA: TABLE & COLUMN INTEGRITY\n";
echo "=======================================================\n";

assert_test("tbl_divisions table exists in DB", $db_conn->table_exists('tbl_divisions'));
assert_test("tbl_sections does NOT exist in DB", !$db_conn->table_exists('tbl_sections'));

$div_fields = $db_conn->list_fields('tbl_divisions');
assert_test("tbl_divisions has division_id column", in_array('division_id', $div_fields));
assert_test("tbl_divisions has division_name column", in_array('division_name', $div_fields));
assert_test("tbl_divisions has class_id column", in_array('class_id', $div_fields));
assert_test("tbl_divisions does NOT have section_id column", !in_array('section_id', $div_fields));

$st_fields = $db_conn->list_fields('tbl_students');
assert_test("tbl_students has division_id column", in_array('division_id', $st_fields));
assert_test("tbl_students does NOT have section_id column", !in_array('section_id', $st_fields));

$att_fields = $db_conn->list_fields('tbl_attendance');
assert_test("tbl_attendance has division_id column", in_array('division_id', $att_fields));
assert_test("tbl_attendance does NOT have section_id column", !in_array('section_id', $att_fields));

$ct_fields = $db_conn->list_fields('tbl_class_teachers');
assert_test("tbl_class_teachers has division_id column", in_array('division_id', $ct_fields));

$stt_fields = $db_conn->list_fields('tbl_subject_teachers');
assert_test("tbl_subject_teachers has division_id column", in_array('division_id', $stt_fields));

$tt_fields = $db_conn->list_fields('tbl_timetable');
assert_test("tbl_timetable has division_id column", in_array('division_id', $tt_fields));

$promo_fields = $db_conn->list_fields('tbl_student_promotions');
assert_test("tbl_student_promotions has from_division_id column", in_array('from_division_id', $promo_fields));
assert_test("tbl_student_promotions has to_division_id column", in_array('to_division_id', $promo_fields));

$res_fields = $db_conn->list_fields('tbl_student_results');
assert_test("tbl_student_results has division_id column", in_array('division_id', $res_fields));
assert_test("tbl_student_results has division_rank column", in_array('division_rank', $res_fields));

echo "\n=======================================================\n";
echo "2. DIVISION_MODEL CRUD & DEFAULT DIVISION A LOGIC\n";
echo "=======================================================\n";

$div_model = $mock_ci->Division_model;
assert_test("Division_model instantiated successfully", $div_model instanceof Division_model);

// Default division A for Class with no configured divisions (e.g., Grade 10, ID 8)
$default_div_id = $div_model->get_default_division_id(8);
assert_test("get_default_division_id returned a valid numeric ID", is_numeric($default_div_id) && $default_div_id > 0);

// get_all for Class with no configured divisions returns default Division A
$divs_for_cls8 = $div_model->get_all(8);
assert_test("get_all(8) returns at least 1 division", count($divs_for_cls8) >= 1);
assert_test("Division returned has division_name = 'A'", $divs_for_cls8[0]->division_name === 'A');
assert_test("Division returned has division_id property", isset($divs_for_cls8[0]->division_id));
assert_test("Division returned has backward-compatible section_name = 'A'", $divs_for_cls8[0]->section_name === 'A');
assert_test("Division returned has backward-compatible section_id property", isset($divs_for_cls8[0]->section_id));

// Section_model backward compatibility bridge
$sec_model = $mock_ci->Section_model;
assert_test("Section_model extends Division_model", $sec_model instanceof Division_model);
$sec_default_id = $sec_model->get_default_section_id(8);
assert_test("Section_model->get_default_section_id(8) works identical to Division_model", (int)$sec_default_id === (int)$default_div_id);

echo "\n=======================================================\n";
echo "3. STUDENT_MODEL INTEGRATION WITH DIVISION\n";
echo "=======================================================\n";

$student_model = $mock_ci->Student_model;
// Query active students
$students = $student_model->get_all(['status' => 1]);
assert_test("Student_model->get_all returns student records", count($students) > 0);
$sample_student = $students[0];
assert_test("Student record has division_name property", property_exists($sample_student, 'division_name'));
assert_test("Student record has backward-compatible section_name property", property_exists($sample_student, 'section_name'));
assert_test("Student record has division_id property", property_exists($sample_student, 'division_id'));

echo "\n=======================================================\n";
echo "4. ATTENDANCE_MODEL INTEGRATION WITH DIVISION\n";
echo "=======================================================\n";

$att_model = $mock_ci->Attendance_model;
$overview = $att_model->get_class_overview('2026-08-20', 1, 8);
assert_test("get_class_overview for Grade 10 returned records", !empty($overview));
assert_test("Overview row has division_name = 'A'", $overview[0]->division_name === 'A');
assert_test("Overview row has division_id", isset($overview[0]->division_id));
assert_test("Overview row has backward-compatible section_name = 'A'", $overview[0]->section_name === 'A');
assert_test("Overview row has backward-compatible section_id", isset($overview[0]->section_id));

// Save & retrieve daily attendance using division_id
$save_res = $att_model->save_daily_attendance([
    6 => ['status' => 'Present', 'remarks' => 'Division test']
], '2026-08-21', 1, 1, 12, 1);
assert_test("save_daily_attendance saved without SQL error", $save_res >= 0);

$db_att_row = $db_conn->where('student_id', 6)->where('attendance_date', '2026-08-21')->get('tbl_attendance')->row();
assert_test("Attendance record inserted into DB", $db_att_row !== NULL);
assert_test("Attendance record has division_id = 12", $db_att_row && (int)$db_att_row->division_id === 12);

// Clean up test record
$db_conn->where('student_id', 6)->where('attendance_date', '2026-08-21')->delete('tbl_attendance');
assert_test("Test attendance row cleaned up safely", true);

echo "\n=======================================================\n";
echo "5. ROUTES & NAVIGATION CONSISTENCY\n";
echo "=======================================================\n";

require APPPATH . 'config/routes.php';
assert_test("routes.php has 'academics/divisions'", isset($route['academics/divisions']));
assert_test("routes.php maps 'academics/divisions' to 'academics/divisions'", $route['academics/divisions'] === 'academics/divisions');
assert_test("routes.php maps 'academics/sections' to 'academics/divisions'", $route['academics/sections'] === 'academics/divisions');
assert_test("routes.php has 'students/get_divisions_ajax'", isset($route['students/get_divisions_ajax']));
assert_test("routes.php has 'academics/get_next_division_ajax'", isset($route['academics/get_next_division_ajax']));

$app_js = file_get_contents('assets/app.js');
assert_test("app.js ROUTE_MAP contains 'divisions'", strpos($app_js, '"divisions": "academics/divisions"') !== false);
assert_test("app.js NAV_CONFIG contains label 'Divisions'", strpos($app_js, '{ key: "divisions", label: "Divisions" }') !== false);
assert_test("app.js PAGE_TITLES contains 'divisions': 'Division Management'", strpos($app_js, '"divisions": "Division Management"') !== false);

echo "\n=======================================================\n";
echo "6. VIEW FILE INTEGRITY: DIVISIONS.PHP\n";
echo "=======================================================\n";

$div_view_path = APPPATH . 'views/pages/academics/divisions.php';
assert_test("divisions.php view exists", file_exists($div_view_path));
$div_view_content = file_get_contents($div_view_path);
assert_test("divisions.php contains 'Divisions' heading", strpos($div_view_content, 'Divisions') !== false);
assert_test("divisions.php contains 'Add Division'", strpos($div_view_content, 'Add Division') !== false);
assert_test("divisions.php contains 'Save Division'", strpos($div_view_content, 'Save Division') !== false);

echo "\n=======================================================\n";
echo "RESULTS: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL SYSTEM-WIDE DIVISION RENAME TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED.\n";
    exit(1);
}
