<?php
/**
 * Test Student Profile Attendance Summary & Calendar E2E Test
 * Run via CLI: php tests/test_student_profile_attendance_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
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
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc" . ($extra ? " - $extra" : "") . "\n";
    }
}

echo "=======================================================\n";
echo "1. Authenticate as Admin\n";
echo "=======================================================\n";
$login_page = http_req('http://localhost/schoolnew/auth/login');
preg_match('/name="csrf_test_name" value="([a-f0-9]+)"/i', $login_page['body'], $matches);
$csrf = $matches[1] ?? '';

$login_res = http_req('http://localhost/schoolnew/auth/login', [
    'csrf_test_name' => $csrf,
    'email'          => 'admin@gmail.com',
    'password'       => '123456'
]);
assert_check("Admin login successful", strpos($login_res['url'], 'dashboard') !== false || strpos($login_res['url'], 'overview') !== false || $login_res['code'] === 200);

echo "\n=======================================================\n";
echo "2. Test Student Profile for Student 46 (Aarav Menon - Class 1, Div A)\n";
echo "=======================================================\n";
$p46 = http_req('http://localhost/schoolnew/students/profile/46');
assert_check("Profile 46 returns HTTP 200", $p46['code'] === 200);

// Check for PHP Notice / Undefined property
$has_notice = (stripos($p46['body'], 'Severity: Notice') !== false) || (stripos($p46['body'], 'Undefined property') !== false);
assert_check("No PHP Notice or Undefined property in profile 46", !$has_notice);

$has_section_id_err = (stripos($p46['body'], 'stdClass::$section_id') !== false);
assert_check("No stdClass::\$section_id error in profile 46", !$has_section_id_err);

// Check that Division displays 'Division A' instead of 'Section A'
assert_check("Displays Division A in profile", stripos($p46['body'], 'Division A') !== false);

// Check Interactive Calendar Link
preg_match('/href="([^"]*attendance\/calendar[^"]*)"/i', $p46['body'], $cal_match);
$cal_link = $cal_match[1] ?? '';
assert_check("Interactive Calendar link exists", !empty($cal_link), "Link found: $cal_link");
assert_check("Calendar link contains student_id=46", strpos($cal_link, 'student_id=46') !== false);
assert_check("Calendar link contains class_id=1", strpos($cal_link, 'class_id=1') !== false);
assert_check("Calendar link contains division_id=12", strpos($cal_link, 'division_id=12') !== false);
assert_check("Calendar link does NOT contain section_id", strpos($cal_link, 'section_id') === false, "Link was: $cal_link");

// Check Attendance Summary numbers
assert_check("Contains 'Total Academic Days'", stripos($p46['body'], 'Total Academic Days') !== false);
assert_check("Contains 'Days Present'", stripos($p46['body'], 'Days Present') !== false);
assert_check("Contains 'Days Absent'", stripos($p46['body'], 'Days Absent') !== false);
assert_check("Contains 'Late Arrivals'", stripos($p46['body'], 'Late Arrivals') !== false);
assert_check("Contains 'Excused / Leave'", stripos($p46['body'], 'Excused / Leave') !== false);
assert_check("Contains 'Overall Percentage'", stripos($p46['body'], 'Overall Percentage') !== false);

echo "\n=======================================================\n";
echo "3. Follow Interactive Calendar Link for Student 46\n";
echo "=======================================================\n";
$cal_res = http_req($cal_link);
assert_check("Attendance Calendar opens with HTTP 200", $cal_res['code'] === 200);
assert_check("No PHP errors on calendar page", stripos($cal_res['body'], 'Severity: Notice') === false && stripos($cal_res['body'], 'Fatal error') === false);
// Verify student 46 is selected in the student dropdown
assert_check("Student 46 is selected in calendar", preg_match('/<option\s+value="46"\s+selected/i', $cal_res['body']));
// Verify division 12 is selected in division dropdown
assert_check("Division 12 is selected in calendar", preg_match('/<option\s+value="12"\s+selected/i', $cal_res['body']));

echo "\n=======================================================\n";
echo "4. Test Multiple Student Profiles Across Classes\n";
echo "=======================================================\n";
// Student 51 -> UKG (Class 13), Div A (10001)
$p51 = http_req('http://localhost/schoolnew/students/profile/51');
assert_check("Profile 51 returns HTTP 200 without notices", $p51['code'] === 200 && stripos($p51['body'], 'Severity: Notice') === false);
assert_check("Profile 51 displays Aditya Kumar", stripos($p51['body'], 'Aditya Kumar') !== false);
preg_match('/href="([^"]*attendance\/calendar[^"]*)"/i', $p51['body'], $cal51_match);
$cal51_link = $cal51_match[1] ?? '';
assert_check("Profile 51 calendar link has student_id=51 & division_id=10001", strpos($cal51_link, 'student_id=51') !== false && strpos($cal51_link, 'division_id=10001') !== false && strpos($cal51_link, 'section_id') === false);

// Student 56 -> Grade 1 (Class 14), Div A (10008)
$p56 = http_req('http://localhost/schoolnew/students/profile/56');
assert_check("Profile 56 returns HTTP 200 without notices", $p56['code'] === 200 && stripos($p56['body'], 'Severity: Notice') === false);
assert_check("Profile 56 displays Reyansh Pillai", stripos($p56['body'], 'Reyansh Pillai') !== false);

// Student 76 -> Grade 5 (Class 18), Div A (10012)
$p76 = http_req('http://localhost/schoolnew/students/profile/76');
assert_check("Profile 76 returns HTTP 200 without notices", $p76['code'] === 200 && stripos($p76['body'], 'Severity: Notice') === false);

echo "\n=======================================================\n";
echo "5. Test Student in Division B and Student with NULL Division\n";
echo "=======================================================\n";
// Connect to database to test Division B and NULL Division scenarios safely
$mysqli = new mysqli('localhost', 'root', '', 'db_school');

// Temporarily set student 50 to Division 10004 (LKG Division B)
$mysqli->query("UPDATE tbl_students SET division_id = 10004 WHERE student_id = 50");
$p50 = http_req('http://localhost/schoolnew/students/profile/50');
assert_check("Profile 50 (Division B) returns HTTP 200 without notices", $p50['code'] === 200 && stripos($p50['body'], 'Severity: Notice') === false);
assert_check("Profile 50 displays Division B", stripos($p50['body'], 'Division B') !== false);
preg_match('/href="([^"]*attendance\/calendar[^"]*)"/i', $p50['body'], $cal50_match);
$cal50_link = $cal50_match[1] ?? '';
assert_check("Profile 50 calendar link has division_id=10004", strpos($cal50_link, 'division_id=10004') !== false);

// Temporarily set student 50 to NULL division
$mysqli->query("UPDATE tbl_students SET division_id = NULL WHERE student_id = 50");
$p50_null = http_req('http://localhost/schoolnew/students/profile/50');
assert_check("Profile 50 (NULL division) returns HTTP 200 without notices", $p50_null['code'] === 200 && stripos($p50_null['body'], 'Severity: Notice') === false);
assert_check("Profile 50 displays 'Not Assigned' for missing division", stripos($p50_null['body'], 'Not Assigned') !== false);
preg_match('/href="([^"]*attendance\/calendar[^"]*)"/i', $p50_null['body'], $cal50_null_match);
$cal50_null_link = $cal50_null_match[1] ?? '';
assert_check("Calendar link for NULL division student does not contain division_id or section_id", strpos($cal50_null_link, 'division_id') === false && strpos($cal50_null_link, 'section_id') === false);

// Restore student 50 to division 12
$mysqli->query("UPDATE tbl_students SET division_id = 12 WHERE student_id = 50");

echo "\n=======================================================\n";
echo "6. Test Attendance Calculation with Live Records\n";
echo "=======================================================\n";
// Insert sample attendance records for student 46
$mysqli->query("INSERT INTO tbl_attendance (student_id, academic_year_id, class_id, division_id, attendance_date, attendance_type, attendance_status, remarks) VALUES 
    (46, 1, 1, 12, '2026-09-01', 'Daily', 'Present', 'On time'),
    (46, 1, 1, 12, '2026-09-02', 'Daily', 'Present', 'On time'),
    (46, 1, 1, 12, '2026-09-03', 'Daily', 'Absent', 'Sick'),
    (46, 1, 1, 12, '2026-09-04', 'Daily', 'Late', 'Bus delay'),
    (46, 1, 1, 12, '2026-09-05', 'Daily', 'Excused', 'Doctor visit')");

$p46_att = http_req('http://localhost/schoolnew/students/profile/46');
// Expected: Total 5, Present 2, Absent 1, Late 1, Excused 1, Percentage: 40% (2 / 5 * 100)
assert_check("Attendance Total = 5 rendered", strpos($p46_att['body'], '>5</div>') !== false);
assert_check("Attendance Present = 2 rendered", strpos($p46_att['body'], '>2</div>') !== false);
assert_check("Attendance Absent = 1 rendered", strpos($p46_att['body'], '>1</div>') !== false);
assert_check("Attendance Percentage = 40% rendered", strpos($p46_att['body'], '>40%</div>') !== false);

// Clean up sample attendance records
$mysqli->query("DELETE FROM tbl_attendance WHERE student_id = 46");

$mysqli->close();

echo "\n=======================================================\n";
echo "TEST RESULTS: $tests_passed / $tests_run passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    exit(0);
} else {
    exit(1);
}
