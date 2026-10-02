<?php
/**
 * Comprehensive E2E Test for All Students -> Edit Student Redirect & Dedicated Edit Flow
 * Tests 1 to 10 as specified in prompt.
 * Run via CLI: php tests/test_all_students_edit_button_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_edit_btn_');

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
$csrf = get_csrf_token($login_page['body']);

$login_res = http_req('http://localhost/schoolnew/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assert_check("Admin login successful", strpos($login_res['url'], 'auth/login') === false && $login_res['code'] === 200);

// Connect to DB directly for count and verification
$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}

$initial_count = (int)$db->query("SELECT COUNT(*) as cnt FROM tbl_students WHERE is_deleted = 'n'")->fetch_assoc()['cnt'];
echo "  Total active students in DB before tests: {$initial_count}\n";

// Fetch two distinct students from DB
$s_res = $db->query("SELECT * FROM tbl_students WHERE status = 1 AND is_deleted = 'n' ORDER BY student_id ASC LIMIT 2");
$student1 = $s_res->fetch_assoc();
$student2 = $s_res->fetch_assoc();

if (!$student1 || !$student2) {
    die("Need at least 2 active students in DB to test.");
}

echo "  Student 1: ID {$student1['student_id']} ({$student1['first_name']} {$student1['last_name']}, Adm: {$student1['admission_number']})\n";
echo "  Student 2: ID {$student2['student_id']} ({$student2['first_name']} {$student2['last_name']}, Adm: {$student2['admission_number']})\n";

echo "\n=======================================================\n";
echo "Test 1: All Students List Edit Button for First Student\n";
echo "=======================================================\n";

// Fetch All Students DataTables JSON
$all_st_json = http_req('http://localhost/schoolnew/students/all_students_ajax', [
    'length' => 10,
    'start'  => 0,
    'search' => ['value' => $student1['admission_number']]
]);
assert_check("all_students_ajax returns HTTP 200", $all_st_json['code'] === 200);
$list_data = json_decode($all_st_json['body'], true);
assert_check("JSON parsed with data array", is_array($list_data) && !empty($list_data['data']));

// Extract actions column (index 10) for student 1
$first_row_actions = $list_data['data'][0][10] ?? '';
assert_check("Actions column exists for first student", !empty($first_row_actions));

// Extract pencil edit URL
preg_match('/href="([^"]*)"[^>]*title="Edit Student"/i', $first_row_actions, $edit_m);
if (empty($edit_m[1])) {
    // Also try matching <span>edit</span>
    preg_match('/<a\s+href="([^"]+)"[^>]*>.*?<span[^>]*>edit<\/span>.*?<\/a>/is', $first_row_actions, $edit_m);
}
$pencil_url = $edit_m[1] ?? '';
echo "  Pencil button URL for student 1: {$pencil_url}\n";

assert_check("Pencil button does NOT point to /students/add", strpos($pencil_url, 'students/add') === false);
assert_check("Pencil button points to /students/edit/{$student1['student_id']}", 
    strpos($pencil_url, 'students/edit/' . $student1['student_id']) !== false || strpos($pencil_url, 'students/edit?student_id=' . $student1['student_id']) !== false
);

// Open the Edit Student Page via the pencil URL
$edit_res1 = http_req($pencil_url);
assert_check("Dedicated Edit Student page loads (HTTP 200)", $edit_res1['code'] === 200);
assert_check("Loads Dedicated Edit view (not Add/Wizard)", 
    strpos($edit_res1['body'], 'Edit Student') !== false && 
    strpos($edit_res1['body'], 'student-edit-form') !== false &&
    strpos($edit_res1['body'], 'student_registration_wizard') === false
);
assert_check("Student 1 First Name is pre-filled", strpos($edit_res1['body'], 'value="' . htmlspecialchars($student1['first_name']) . '"') !== false);
assert_check("Student 1 Admission Number is pre-filled", strpos($edit_res1['body'], 'value="' . htmlspecialchars($student1['admission_number']) . '"') !== false);

echo "\n=======================================================\n";
echo "Test 2: All Students List Edit Button for Second Student\n";
echo "=======================================================\n";

$all_st_json2 = http_req('http://localhost/schoolnew/students/all_students_ajax', [
    'length' => 10,
    'start'  => 0,
    'search' => ['value' => $student2['admission_number']]
]);
$list_data2 = json_decode($all_st_json2['body'], true);
$second_row_actions = $list_data2['data'][0][10] ?? '';
preg_match('/href="([^"]*)"[^>]*title="Edit Student"/i', $second_row_actions, $edit_m2);
if (empty($edit_m2[1])) {
    preg_match('/<a\s+href="([^"]+)"[^>]*>.*?<span[^>]*>edit<\/span>.*?<\/a>/is', $second_row_actions, $edit_m2);
}
$pencil_url2 = $edit_m2[1] ?? '';
echo "  Pencil button URL for student 2: {$pencil_url2}\n";

assert_check("Pencil button points to /students/edit/{$student2['student_id']}", 
    strpos($pencil_url2, 'students/edit/' . $student2['student_id']) !== false || strpos($pencil_url2, 'students/edit?student_id=' . $student2['student_id']) !== false
);

$edit_res2 = http_req($pencil_url2);
assert_check("Student 2 Edit page loads (HTTP 200)", $edit_res2['code'] === 200);
assert_check("Student 2 First Name is pre-filled", strpos($edit_res2['body'], 'value="' . htmlspecialchars($student2['first_name']) . '"') !== false);
assert_check("Student 2 Admission Number is pre-filled", strpos($edit_res2['body'], 'value="' . htmlspecialchars($student2['admission_number']) . '"') !== false);
assert_check("Student 1 Admission Number does NOT appear in Student 2 form", strpos($edit_res2['body'], 'value="' . htmlspecialchars($student1['admission_number']) . '"') === false);

echo "\n=======================================================\n";
echo "Test 3: Admission Number Unchanged & Not Auto-Generated\n";
echo "=======================================================\n";

preg_match('/name="admission_number"[^>]*value="([^"]+)"/i', $edit_res1['body'], $adm_m);
$loaded_adm = $adm_m[1] ?? '';
assert_check("Form has existing admission number", $loaded_adm === $student1['admission_number'], "Loaded: '{$loaded_adm}', Expected: '{$student1['admission_number']}'");
assert_check("No auto-generated placeholder or prefix like EDU/NEW", 
    strpos($loaded_adm, '(Auto-generate)') === false
);

echo "\n=======================================================\n";
echo "Test 4: Update Existing Student (Does NOT create new record)\n";
echo "=======================================================\n";

$csrf_edit = get_csrf_token($edit_res1['body']);
$test_phone = '98765' . rand(10000, 99999);

$update_payload = [
    $csrf_edit['name'] => $csrf_edit['hash'],
    'admission_number' => $student1['admission_number'],
    'first_name'       => $student1['first_name'],
    'last_name'        => $student1['last_name'],
    'gender'           => $student1['gender'] ?? 'Male',
    'date_of_birth'    => $student1['date_of_birth'] ?? '2015-05-10',
    'blood_group'      => $student1['blood_group'] ?? 'O+',
    'academic_year_id' => $student1['academic_year_id'],
    'class_id'         => $student1['class_id'],
    'division_id'      => $student1['division_id'],
    'roll_number'      => $student1['roll_number'] ?? '10',
    'guardian_name'    => $student1['guardian_name'] ?? 'Guardian Name',
    'guardian_phone'   => $test_phone,
    'address'          => $student1['address'] ?? 'Test Address',
];

$upd_res = http_req("http://localhost/schoolnew/students/edit/{$student1['student_id']}", $update_payload);
assert_check("Update response code 200/302", in_array($upd_res['code'], [200, 302]));

// Verify update in DB
$db_student1 = $db->query("SELECT * FROM tbl_students WHERE student_id = {$student1['student_id']}")->fetch_assoc();
assert_check("Guardian phone updated in database", $db_student1['guardian_phone'] === $test_phone);
assert_check("Student ID remains unchanged ({$student1['student_id']})", (int)$db_student1['student_id'] === (int)$student1['student_id']);
assert_check("Admission Number remains unchanged", $db_student1['admission_number'] === $student1['admission_number']);

echo "\n=======================================================\n";
echo "Test 5: Student Count in Database Unchanged\n";
echo "=======================================================\n";

$after_count = (int)$db->query("SELECT COUNT(*) as cnt FROM tbl_students WHERE is_deleted = 'n'")->fetch_assoc()['cnt'];
assert_check("Student count did NOT increase (Before: {$initial_count}, After: {$after_count})", $after_count === $initial_count);

echo "\n=======================================================\n";
echo "Test 6: Class / Division Pre-Selected\n";
echo "=======================================================\n";

$cls_pattern = '/<option\s+value="' . (int)$student1['class_id'] . '"[^>]*selected/i';
assert_check("Student's existing Class is pre-selected", preg_match($cls_pattern, $edit_res1['body']));

if (!empty($student1['division_id'])) {
    $div_pattern = '/<option\s+value="' . (int)$student1['division_id'] . '"[^>]*selected/i';
    assert_check("Student's existing Division is pre-selected", preg_match($div_pattern, $edit_res1['body']));
} else {
    echo "  [INFO] Student has no division assigned initially.\n";
}

echo "\n=======================================================\n";
echo "Test 7: Student Profile Reflects Updated Data\n";
echo "=======================================================\n";

$prof_res = http_req("http://localhost/schoolnew/students/profile/{$student1['student_id']}");
assert_check("Profile returns HTTP 200", $prof_res['code'] === 200);
assert_check("Profile shows updated phone number", strpos($prof_res['body'], $test_phone) !== false);

echo "\n=======================================================\n";
echo "Test 8: Student ID Card View Works for Updated Student\n";
echo "=======================================================\n";

$idcard_res = http_req("http://localhost/schoolnew/students/id_cards?student_id={$student1['student_id']}");
assert_check("ID Cards returns HTTP 200", $idcard_res['code'] === 200);
assert_check("ID Cards has no PHP errors", strpos($idcard_res['body'], 'A PHP Error was encountered') === false);

echo "\n=======================================================\n";
echo "Test 9: Attendance Flow Remains Error-Free\n";
echo "=======================================================\n";

assert_check("Profile attendance has no section_id errors", 
    strpos($prof_res['body'], 'Undefined property') === false && 
    strpos($prof_res['body'], "Unknown column 'section_id'") === false
);

echo "\n=======================================================\n";
echo "Test 10: Student Registration / Add Student Workflow Untouched\n";
echo "=======================================================\n";

$add_res = http_req("http://localhost/schoolnew/students/add");
assert_check("students/add loads HTTP 200", $add_res['code'] === 200);
assert_check("students/add is genuinely Student Registration Wizard", 
    strpos($add_res['body'], 'Register Student') !== false || 
    strpos($add_res['body'], 'Step 1') !== false ||
    strpos($add_res['body'], 'Student Registration') !== false
);

// Restore original phone for student 1
$db->query("UPDATE tbl_students SET guardian_phone = '" . $db->real_escape_string($student1['guardian_phone'] ?? '') . "' WHERE student_id = {$student1['student_id']}");
echo "  Restored original phone for student {$student1['student_id']}.\n";

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
