<?php
/**
 * Comprehensive E2E Tests for Student Edit/Update & Division Workflow
 * Tests 1 to 8 as specified in requirements.
 * Run via CLI: php tests/test_student_edit_update_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_edit_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'err' => $err];
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

function get_csrf_token($body) {
    if (preg_match('/name="([^"]*csrf[^"]*)"\s+value="([^"]+)"/i', $body, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_test_name', 'hash' => ''];
}

echo "=======================================================\n";
echo "1. Authenticate as Admin\n";
echo "=======================================================\n";
$login_page = http_req('http://localhost/schoolnew/auth/login');
assert_check("Login page loads (HTTP 200)", $login_page['code'] === 200);

$csrf = get_csrf_token($login_page['body']);

$login_res = http_req('http://localhost/schoolnew/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

assert_check("Admin login successful", strpos($login_res['url'], 'auth/login') === false && $login_res['code'] === 200);

echo "\n=======================================================\n";
echo "2. Find Target Student & Verify Schema & Divisions\n";
echo "=======================================================\n";

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}

// Find a student with class_id and division_id
$st_res = $db->query("SELECT s.*, c.class_name, d.division_name 
                      FROM tbl_students s 
                      LEFT JOIN tbl_classes c ON c.class_id = s.class_id 
                      LEFT JOIN tbl_divisions d ON d.division_id = s.division_id 
                      WHERE s.status = 1 AND s.is_deleted = 'n' AND s.class_id IS NOT NULL 
                      ORDER BY s.student_id ASC LIMIT 1");
$target_student = $st_res->fetch_assoc();

if (!$target_student) {
    die("No active student found for test.");
}

$student_id = (int)$target_student['student_id'];
$initial_class_id = (int)$target_student['class_id'];
$initial_division_id = (int)$target_student['division_id'];
$initial_admission_no = $target_student['admission_number'];
$initial_first_name = $target_student['first_name'];
$initial_last_name = $target_student['last_name'];
$initial_academic_year = (int)$target_student['academic_year_id'];

echo "  Target Student ID: {$student_id} ({$initial_first_name} {$initial_last_name}, Adm: {$initial_admission_no})\n";
echo "  Class ID: {$initial_class_id}, Division ID: {$initial_division_id}, Year ID: {$initial_academic_year}\n";

// Find all divisions for this class
$div_res = $db->query("SELECT division_id, division_name FROM tbl_divisions WHERE class_id = {$initial_class_id} AND status = 1 AND is_deleted = 0 ORDER BY division_id ASC");
$class_divisions = [];
while ($row = $div_res->fetch_assoc()) {
    $class_divisions[] = $row;
}
echo "  Divisions available for class {$initial_class_id}: " . count($class_divisions) . "\n";

echo "\n=======================================================\n";
echo "Test 1: Edit Student without changing Class/Division\n";
echo "=======================================================\n";

// GET Edit page
$edit_page = http_req("http://localhost/schoolnew/students/edit/{$student_id}");
assert_check("Edit page loads (HTTP 200)", $edit_page['code'] === 200);
assert_check("Edit page does not show PHP Error 1054 or Unknown column", 
    strpos($edit_page['body'], "Unknown column 'section_id'") === false &&
    strpos($edit_page['body'], 'A PHP Error was encountered') === false
);
assert_check("Division dropdown exists with name='division_id'", 
    strpos($edit_page['body'], 'name="division_id"') !== false
);

// Extract CSRF
$edit_csrf = get_csrf_token($edit_page['body']);

// Post update keeping same class & division
$update_post = [
    $edit_csrf['name'] => $edit_csrf['hash'],
    'admission_number' => $initial_admission_no,
    'first_name'       => $initial_first_name,
    'last_name'        => $initial_last_name,
    'gender'           => $target_student['gender'] ?? 'Male',
    'date_of_birth'    => $target_student['date_of_birth'] ?? '2015-05-10',
    'blood_group'      => $target_student['blood_group'] ?? 'O+',
    'academic_year_id' => $initial_academic_year,
    'class_id'         => $initial_class_id,
    'division_id'      => $initial_division_id,
    'roll_number'      => $target_student['roll_number'] ?? '10',
    'guardian_name'    => $target_student['guardian_name'] ?? 'Parent Guardian',
    'guardian_phone'   => $target_student['guardian_phone'] ?? '9876543210',
    'address'          => $target_student['address'] ?? 'Test Address 123',
];

$post_res = http_req("http://localhost/schoolnew/students/edit/{$student_id}", $update_post);
assert_check("Update response HTTP code 200/302", in_array($post_res['code'], [200, 302]));
assert_check("No Unknown column 'section_id' in response", strpos($post_res['body'], "Unknown column 'section_id'") === false);
assert_check("No Error Number: 1054 in response", strpos($post_res['body'], 'Error Number: 1054') === false);
assert_check("Update succeeded without fatal database error", strpos($post_res['body'], 'An uncaught Exception was encountered') === false);

echo "\n=======================================================\n";
echo "Test 2: Change Division (e.g. Div A -> Div B)\n";
echo "=======================================================\n";

// Pick an alternate division for the same class if available
$alternate_division_id = null;
$alternate_division_name = '';
foreach ($class_divisions as $cd) {
    if ((int)$cd['division_id'] !== $initial_division_id) {
        $alternate_division_id = (int)$cd['division_id'];
        $alternate_division_name = $cd['division_name'];
        break;
    }
}

if (!$alternate_division_id) {
    // Insert a test division for this class so we can test switching
    $db->query("INSERT INTO tbl_divisions (class_id, division_name, status, is_deleted, created_at) VALUES ({$initial_class_id}, 'Test-B', 1, 0, NOW())");
    $alternate_division_id = $db->insert_id;
    $alternate_division_name = 'Test-B';
    echo "  Created temporary division ID {$alternate_division_id} ('Test-B') for class {$initial_class_id}\n";
}

echo "  Switching student {$student_id} from division {$initial_division_id} to {$alternate_division_id} ({$alternate_division_name})\n";

// Fetch fresh CSRF token from edit page
$edit_page2 = http_req("http://localhost/schoolnew/students/edit/{$student_id}");
$csrf2 = get_csrf_token($edit_page2['body']);

$update_post2 = $update_post;
$update_post2[$csrf2['name']] = $csrf2['hash'];
$update_post2['division_id']  = $alternate_division_id;

$post_res2 = http_req("http://localhost/schoolnew/students/edit/{$student_id}", $update_post2);
assert_check("Change division update HTTP code 200/302", in_array($post_res2['code'], [200, 302]));
assert_check("No Unknown column 'section_id' on division change", strpos($post_res2['body'], "Unknown column 'section_id'") === false);

// Verify in DB directly
$db_check = $db->query("SELECT division_id FROM tbl_students WHERE student_id = {$student_id}")->fetch_assoc();
assert_check("Database correctly saved new division_id ({$alternate_division_id})", (int)$db_check['division_id'] === $alternate_division_id, "Actual in DB: " . $db_check['division_id']);

echo "\n=======================================================\n";
echo "Test 3: Change Class & AJAX Divisions Dynamic Refresh\n";
echo "=======================================================\n";

// Find another class
$other_cls = $db->query("SELECT class_id, class_name FROM tbl_classes WHERE class_id != {$initial_class_id} AND status = 1 AND is_deleted = 0 ORDER BY class_id ASC LIMIT 1")->fetch_assoc();
$other_class_id = (int)$other_cls['class_id'];
$other_class_name = $other_cls['class_name'];

// Refresh a GET page to get current valid CSRF token
$refresh_page = http_req("http://localhost/schoolnew/students/edit/{$student_id}");
$csrf_ajax = get_csrf_token($refresh_page['body']);

// Call AJAX get_divisions_ajax
$ajax_res = http_req("http://localhost/schoolnew/students/get_divisions_ajax", [
    $csrf_ajax['name'] => $csrf_ajax['hash'],
    'class_id'         => $other_class_id
]);

assert_check("get_divisions_ajax returns HTTP 200", $ajax_res['code'] === 200);
$json_data = json_decode($ajax_res['body'], true);
assert_check("get_divisions_ajax returns valid JSON with status=true", is_array($json_data) && ($json_data['status'] ?? false) === true);
assert_check("get_divisions_ajax returns divisions array", isset($json_data['divisions']) && is_array($json_data['divisions']));

// Verify returned divisions actually belong to other_class_id
$all_match = true;
if (!empty($json_data['divisions'])) {
    foreach ($json_data['divisions'] as $d) {
        if ((int)$d['class_id'] !== $other_class_id) {
            $all_match = false;
            break;
        }
    }
}
assert_check("All returned divisions belong to the requested class ({$other_class_name})", $all_match);

echo "\n=======================================================\n";
echo "Test 4: Invalid Class + Division Combination Backend Rejection\n";
echo "=======================================================\n";

// Attempt to submit class A with a division that belongs to class B
$other_class_div = $db->query("SELECT division_id FROM tbl_divisions WHERE class_id = {$other_class_id} AND status = 1 AND is_deleted = 0 LIMIT 1")->fetch_assoc();
$foreign_div_id = (int)$other_class_div['division_id'];

// Get fresh CSRF
$edit_page3 = http_req("http://localhost/schoolnew/students/edit/{$student_id}");
$csrf3 = get_csrf_token($edit_page3['body']);

$invalid_post = $update_post;
$invalid_post[$csrf3['name']] = $csrf3['hash'];
$invalid_post['class_id']     = $initial_class_id;     // Class A
$invalid_post['division_id']  = $foreign_div_id;       // Belongs to Class B!

$invalid_res = http_req("http://localhost/schoolnew/students/edit/{$student_id}", $invalid_post);

// Should be rejected by validate_class_division callback
assert_check("Invalid class + division rejected by backend validation", 
    strpos($invalid_res['body'], 'The selected division is invalid for the chosen class.') !== false,
    "Expected validation error message in response"
);

// Verify DB was NOT updated with foreign_div_id
$db_check2 = $db->query("SELECT division_id FROM tbl_students WHERE student_id = {$student_id}")->fetch_assoc();
assert_check("Database was NOT updated with invalid division", (int)$db_check2['division_id'] !== $foreign_div_id);

// Restore original division for student
$db->query("UPDATE tbl_students SET division_id = {$initial_division_id} WHERE student_id = {$student_id}");
echo "  Restored student {$student_id} to original division {$initial_division_id}\n";

echo "\n=======================================================\n";
echo "Test 5: Student Profile View\n";
echo "=======================================================\n";

$profile_res = http_req("http://localhost/schoolnew/students/profile/{$student_id}");
assert_check("Student profile loads (HTTP 200)", $profile_res['code'] === 200);
assert_check("Profile has no PHP error/notice", strpos($profile_res['body'], 'A PHP Error was encountered') === false);
assert_check("Profile has no Undefined property: stdClass::\$section_id", strpos($profile_res['body'], 'Undefined property') === false);
assert_check("Profile shows student name", strpos($profile_res['body'], $initial_first_name) !== false);

echo "\n=======================================================\n";
echo "Test 6: Student Listing View (All Students)\n";
echo "=======================================================\n";

$listing_res = http_req("http://localhost/schoolnew/students/all_students");
assert_check("All Students page loads (HTTP 200)", $listing_res['code'] === 200);
$has_php_err = strpos($listing_res['body'], 'A PHP Error was encountered') !== false;
if ($has_php_err && preg_match('/<h4>A PHP Error was encountered<\/h4>(.*?)<\/div>/s', $listing_res['body'], $em)) {
    $err_snippet = strip_tags($em[0]);
} else {
    $err_snippet = '';
}
assert_check("No PHP errors on all students page", !$has_php_err, $err_snippet);

// Check DataTables endpoint with student's class and division
$ajax_list_res = http_req("http://localhost/schoolnew/students/all_students_ajax", [
    'academic_year_id' => $initial_academic_year,
    'class_id'         => $initial_class_id,
    'division_id'      => $initial_division_id,
    'search'           => ['value' => $initial_admission_no]
]);
assert_check("all_students_ajax returns HTTP 200", $ajax_list_res['code'] === 200);
$list_json = json_decode($ajax_list_res['body'], true);
assert_check("all_students_ajax returns valid JSON", is_array($list_json));
assert_check("Target student admission number appears in list", strpos($ajax_list_res['body'], $initial_admission_no) !== false);

echo "\n=======================================================\n";
echo "Test 7: Student ID Card View\n";
echo "=======================================================\n";

$idcard_res = http_req("http://localhost/schoolnew/students/id_cards?student_id={$student_id}");
assert_check("ID Cards page loads (HTTP 200)", $idcard_res['code'] === 200);
assert_check("ID Card page has no PHP errors", strpos($idcard_res['body'], 'A PHP Error was encountered') === false);
assert_check("ID Card page has no section_id error", strpos($idcard_res['body'], "Unknown column 'section_id'") === false);

echo "\n=======================================================\n";
echo "Test 8: Student Profile Attendance Summary & Calendar\n";
echo "=======================================================\n";

// Check Attendance link on profile
preg_match('/href="([^"]*attendance\/calendar[^"]*)"/i', $profile_res['body'], $cal_match);
$cal_link = $cal_match[1] ?? '';
assert_check("Profile contains interactive calendar link", !empty($cal_link));
assert_check("Calendar link does NOT contain section_id", strpos($cal_link, 'section_id') === false);
assert_check("Calendar link contains division_id", strpos($cal_link, 'division_id') !== false);

$cal_res = http_req($cal_link);
assert_check("Calendar link loads (HTTP 200)", $cal_res['code'] === 200);
assert_check("Calendar page has no PHP error/notice", strpos($cal_res['body'], 'A PHP Error was encountered') === false);

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
