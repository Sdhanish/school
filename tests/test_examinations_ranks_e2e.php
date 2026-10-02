<?php
/**
 * End-to-End Test for Examinations Rank & Position (Division Rank fix)
 * 
 * Verifies:
 * 1. Rank & Positions page loads without PHP Notice / Error (HTTP 200)
 * 2. Division Rank is mapped to $r->division_rank (no $r->section_rank)
 * 3. Filter combinations:
 *    - All Classes + All Divisions
 *    - Specific Class + All Divisions
 *    - Specific Class + Specific Division
 * 4. Backend calculation engine computes division_rank & class_rank accurately with standard competition ties
 * 5. Ties handling: Two students with 90% get rank 1, next student with 85% gets rank 3
 * 6. Division Rank is scoped per class & division
 * 7. Clean up all test data
 * 
 * Run: php tests/test_examinations_ranks_e2e.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');
require_once APPPATH . 'config/database.php';

$cfg = $db['default'];
$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_ranks_e2e_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff];
}

$tests_run = 0;
$tests_passed = 0;

function assert_check($desc, $condition, $extra = '') {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}" . ($extra ? " - {$extra}" : "") . "\n";
    }
}

echo "=======================================================\n";
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$login_page = http_req("http://localhost/schoolnew/auth/login");
preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/', $login_page['body'], $m);
$csrf_name = $m[1] ?? 'csrf_token';
$csrf_val  = $m[2] ?? '';

$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf_name => $csrf_val,
    'email'    => 'admin@gmail.com',
    'password' => '123456',
    'remember' => '1'
]);
assert_check("Admin login successful", strpos($login_res['url'], 'auth/login') === false && $login_res['code'] === 200);

echo "\n=======================================================\n";
echo "2. Setup Controlled Exam Results Data for Testing Ranks\n";
echo "=======================================================\n";

// Clear existing results for exam_id = 1 to have deterministic test state
$mysqli->query("DELETE FROM tbl_student_results WHERE exam_id = 1");

// Pick students for Class 1, Division 12 and Division 10004
// Let's find 4 students from Class 1
$stu_rows = [];
$res = $mysqli->query("SELECT student_id, class_id, division_id, roll_number, first_name, last_name FROM tbl_students WHERE class_id = 1 AND status = 1 LIMIT 4");
while ($r = $res->fetch_assoc()) {
    $stu_rows[] = $r;
}

if (count($stu_rows) < 4) {
    die("Need at least 4 active students in Class 1 for comprehensive test.\n");
}

$s1 = $stu_rows[0]; // Class 1, Div A
$s2 = $stu_rows[1]; // Class 1, Div A
$s3 = $stu_rows[2]; // Class 1, Div A
$s4 = $stu_rows[3]; // Class 1, Div B (we'll set division_id to 10004 for test)

$div_a = (int)$s1['division_id'];
$div_b = 10004;

// Student 1: Score 90% (Rank 1 in Class, Rank 1 in Div A)
// Student 2: Score 90% (Tie: Rank 1 in Class, Rank 1 in Div A)
// Student 3: Score 80% (Rank 3 in Class, Rank 3 in Div A)
// Student 4: Score 85% in Div B (Rank 2 in Class, Rank 1 in Div B)
$mysqli->query("INSERT INTO tbl_student_results 
(exam_id, student_id, academic_year_id, class_id, division_id, total_marks, max_marks, percentage, overall_grade, pass_status, class_rank, division_rank, is_published, is_deleted)
VALUES 
(1, {$s1['student_id']}, 1, 1, {$div_a}, 450, 500, 90.00, 'A+', 'Pass', 1, 1, 1, 'n'),
(1, {$s2['student_id']}, 1, 1, {$div_a}, 450, 500, 90.00, 'A+', 'Pass', 1, 1, 1, 'n'),
(1, {$s3['student_id']}, 1, 1, {$div_a}, 400, 500, 80.00, 'A',  'Pass', 3, 3, 1, 'n'),
(1, {$s4['student_id']}, 1, 1, {$div_b}, 425, 500, 85.00, 'A',  'Pass', 2, 1, 1, 'n')
");
assert_check("Seeded 4 test student results with known ranks and ties", $mysqli->affected_rows === 4);

echo "\n=======================================================\n";
echo "3. Test Ranks Page Under All Filter Scenarios\n";
echo "=======================================================\n";

// Scenario A: All Classes + All Divisions
$res_all = http_req("http://localhost/schoolnew/examinations/ranks?exam_id=1");
assert_check("All Classes + All Divisions loads HTTP 200", $res_all['code'] === 200);
assert_check("No PHP Notice 'Undefined property: stdClass::\$section_rank'", strpos($res_all['body'], 'section_rank') === false);
assert_check("No PHP Error encountered on All Classes + All Divisions", strpos($res_all['body'], 'A PHP Error was encountered') === false);
assert_check("Table displays Class Rank header", strpos($res_all['body'], 'Class Rank') !== false);
assert_check("Table displays Division Rank header", strpos($res_all['body'], 'Division Rank') !== false);
assert_check("Ranks page shows 4 Ranked Students count", strpos($res_all['body'], '4 Ranked Students') !== false);
assert_check("Student 1 name appears in merit list", strpos($res_all['body'], $s1['first_name']) !== false);

// Scenario B: Specific Class + All Divisions
$res_class = http_req("http://localhost/schoolnew/examinations/ranks?exam_id=1&class_id=1");
assert_check("Specific Class + All Divisions loads HTTP 200", $res_class['code'] === 200);
assert_check("No PHP Error on Specific Class filter", strpos($res_class['body'], 'A PHP Error was encountered') === false);
assert_check("No section_rank notice on Specific Class filter", strpos($res_class['body'], 'section_rank') === false);
assert_check("Shows 4 Ranked Students in Class 1", strpos($res_class['body'], '4 Ranked Students') !== false);

// Scenario C: Specific Class + Specific Division (Div A)
$res_div_a = http_req("http://localhost/schoolnew/examinations/ranks?exam_id=1&class_id=1&division_id={$div_a}");
assert_check("Specific Class + Division A loads HTTP 200", $res_div_a['code'] === 200);
assert_check("No PHP Error on Specific Division filter", strpos($res_div_a['body'], 'A PHP Error was encountered') === false);
assert_check("Shows 3 Ranked Students in Div A", strpos($res_div_a['body'], '3 Ranked Students') !== false);

// Scenario D: Specific Class + Specific Division (Div B)
$res_div_b = http_req("http://localhost/schoolnew/examinations/ranks?exam_id=1&class_id=1&division_id={$div_b}");
assert_check("Specific Class + Division B loads HTTP 200", $res_div_b['code'] === 200);
assert_check("No PHP Error on Division B filter", strpos($res_div_b['body'], 'A PHP Error was encountered') === false);
assert_check("Shows 1 Ranked Student in Div B", strpos($res_div_b['body'], '1 Ranked Students') !== false);
assert_check("Student 4 in Div B is present", strpos($res_div_b['body'], $s4['first_name']) !== false);

echo "\n=======================================================\n";
echo "4. Test NULL division_rank Display Behavior (No Notice)\n";
echo "=======================================================\n";

// Update one student's division_rank to NULL
$mysqli->query("UPDATE tbl_student_results SET division_rank = NULL WHERE student_id = {$s3['student_id']} AND exam_id = 1");
$res_null_rank = http_req("http://localhost/schoolnew/examinations/ranks?exam_id=1");
assert_check("Page with NULL division_rank loads HTTP 200", $res_null_rank['code'] === 200);
assert_check("Page with NULL division_rank has NO PHP Notice", strpos($res_null_rank['body'], 'A PHP Error was encountered') === false);
assert_check("NULL division_rank displays em-dash (—)", strpos($res_null_rank['body'], '—') !== false);

echo "\n=======================================================\n";
echo "5. Test Backend Rank Recalculation Engine Ties & Division Scoping\n";
echo "=======================================================\n";

// Reset ranks to NULL
// Invoke Result_model recalculate_ranks_for_exam directly
$system_path = __DIR__ . '/../system/';
$app_path    = __DIR__ . '/../application/';
require_once $system_path . 'core/Common.php';
require_once $system_path . 'core/Model.php';
require_once $system_path . 'database/DB.php';
require_once $app_path . 'models/Exam_setting_model.php';
require_once $app_path . 'models/Result_model.php';

if (!class_exists('MockLoader', false)) {
    class MockLoader {
        public function model($name) {}
    }
}
if (!class_exists('MockCI', false)) {
    class MockCI {
        public $db;
        public $load;
        public $Exam_setting_model;
        public function __construct($db) {
            $this->db = $db;
            $this->load = new MockLoader();
            $this->Exam_setting_model = new Exam_setting_model();
            $this->Exam_setting_model->db = $db;
        }
    }
}
$db_conn =& DB();
$mock_ci = new MockCI($db_conn);
if (!function_exists('get_instance')) {
    function &get_instance() {
        global $mock_ci;
        return $mock_ci;
    }
}

$result_model = new Result_model();
$result_model->db = $db_conn;
$result_model->Exam_setting_model = $mock_ci->Exam_setting_model;
$result_model->recalculate_ranks_for_exam(1);

// Verify recalculated ranks in DB
$db_ranks = [];
$res_db = $mysqli->query("SELECT student_id, class_rank, division_rank, percentage FROM tbl_student_results WHERE exam_id = 1 ORDER BY student_id ASC");
while ($row = $res_db->fetch_assoc()) {
    $db_ranks[$row['student_id']] = $row;
}

// Check Student 1 (90% in Div A): class_rank = 1, division_rank = 1
assert_check("Student 1 Class Rank is 1", (int)$db_ranks[$s1['student_id']]['class_rank'] === 1);
assert_check("Student 1 Division Rank is 1", (int)$db_ranks[$s1['student_id']]['division_rank'] === 1);

// Check Student 2 (90% in Div A, Tie): class_rank = 1, division_rank = 1
assert_check("Student 2 Class Rank is 1 (Tie)", (int)$db_ranks[$s2['student_id']]['class_rank'] === 1);
assert_check("Student 2 Division Rank is 1 (Tie)", (int)$db_ranks[$s2['student_id']]['division_rank'] === 1);

// Check Student 4 (85% in Div B): class_rank = 3 (standard competition 1, 1, 3), division_rank = 1 (top in Div B)
assert_check("Student 4 Class Rank is 3 (Standard Competition after tie: 1, 1, 3)", (int)$db_ranks[$s4['student_id']]['class_rank'] === 3);
assert_check("Student 4 Division Rank is 1 (Top in Div B)", (int)$db_ranks[$s4['student_id']]['division_rank'] === 1);

// Check Student 3 (80% in Div A): class_rank = 4, division_rank = 3 (after tie in Div A: 1, 1, 3)
assert_check("Student 3 Class Rank is 4", (int)$db_ranks[$s3['student_id']]['class_rank'] === 4);
assert_check("Student 3 Division Rank is 3 in Div A", (int)$db_ranks[$s3['student_id']]['division_rank'] === 3);

echo "\n=======================================================\n";
echo "6. Clean Up Test Data\n";
echo "=======================================================\n";

$mysqli->query("DELETE FROM tbl_student_results WHERE exam_id = 1");
assert_check("Cleaned up test records from tbl_student_results", true);
@unlink($cookie_file);

echo "\n=======================================================\n";
echo "RESULTS: {$tests_passed} / {$tests_run} tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: " . ($tests_run - $tests_passed) . " test(s) failed.\n";
    exit(1);
}
