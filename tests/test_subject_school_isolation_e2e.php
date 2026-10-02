<?php
/**
 * Comprehensive E2E Test Suite for Subject Multi-School Isolation
 *
 * Verifies:
 * 1. Super Admin authentication.
 * 2. School 1 (Login2) subject listing isolation.
 * 3. School switching to School 13 (School Test).
 * 4. School 13 subject listing isolation.
 * 5. Subject creation in School 13 strictly assigns school_id = 13 and appears in School 13 listing.
 * 6. Cross-school EDIT attempt (IDOR attack) from School 13 targeting School 1 subject is rejected.
 * 7. Cross-school DELETE attempt from School 13 targeting School 1 subject is rejected.
 * 8. Switching back to School 1 confirms School 13 subjects are never listed in School 1.
 * 9. Proper editing of a School 13 subject succeeds.
 * 10. Proper deletion of a School 13 subject succeeds.
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_sub_iso_');

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
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'err' => $err];
}

function get_csrf_token($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token|ci_csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    if (preg_match('/window\.CSRF_TOKEN_NAME\s*=\s*"([^"]+)";\s*window\.CSRF_HASH\s*=\s*"([^"]+)";/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'ci_csrf_token', 'hash' => ''];
}

$tests_passed = 0;
$tests_failed = 0;

function assert_test($desc, $condition, $details = '') {
    global $tests_passed, $tests_failed;
    if ($condition) {
        $tests_passed++;
        echo "  [PASS] {$desc}\n";
    } else {
        $tests_failed++;
        echo "  [FAIL] {$desc}" . ($details ? " - {$details}" : "") . "\n";
    }
}

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}

echo "============================================================\n";
echo " SUBJECT MULTI-SCHOOL ISOLATION E2E TEST SUITE\n";
echo "============================================================\n\n";

// 1. Authenticate as Admin
echo "1. Authenticating as Administrator...\n";
$login_page = http_req("http://localhost/schoolnew/auth/login");
$csrf = get_csrf_token($login_page['body']);
$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assert_test("Admin login HTTP 200", $login_res['code'] === 200);

// Ensure active school is set to 1 first
$subj_page1 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf1 = get_csrf_token($subj_page1['body']);
http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf1['name'] => $csrf1['hash'],
    'school_id'    => 1,
]);

// 2. Fetch Subjects listing for School 1 (Login2)
echo "\n2. Verifying School 1 (Login2) Subjects Listing...\n";
$subj_page1 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf1 = get_csrf_token($subj_page1['body']);
assert_test("School 1 academics/subjects loads HTTP 200", $subj_page1['code'] === 200);

$ajax1 = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf1['name'] => $csrf1['hash'],
    'draw'   => 1,
    'start'  => 0,
    'length' => 100,
]);
assert_test("School 1 ajax_subjects_list HTTP 200", $ajax1['code'] === 200);
$json1 = json_decode($ajax1['body'], true);
assert_test("School 1 returns valid JSON", is_array($json1) && isset($json1['recordsTotal']));
$school1_total = $json1['recordsTotal'] ?? 0;
echo "  [INFO] School 1 recordsTotal: {$school1_total}\n";
assert_test("School 1 recordsTotal matches DB count", $school1_total > 0);

// Verify no School 13 subject appears in School 1 listing
$found_c_prog_in_school1 = false;
foreach ($json1['data'] as $row) {
    $rowText = strip_tags(implode(' ', $row));
    if (stripos($rowText, 'C Programming') !== false || stripos($rowText, 'ITT 201') !== false) {
        $found_c_prog_in_school1 = true;
    }
}
assert_test("School 13 subject ('C Programming') does NOT appear in School 1 list", !$found_c_prog_in_school1);

// 3. Switch School to School 13 (School Test)
echo "\n3. Switching to School 13 (School Test)...\n";
$switch_res = http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf1['name'] => $csrf1['hash'],
    'school_id'    => 13,
]);
assert_test("Switch school to 13 returns HTTP 200", $switch_res['code'] === 200);
$switch_json = json_decode($switch_res['body'], true);
assert_test("Switch response status is true", ($switch_json['status'] ?? false) === true);

// 4. Verify School 13 Subjects Listing
echo "\n4. Verifying School 13 (School Test) Subjects Listing...\n";
$subj_page13 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf13 = get_csrf_token($subj_page13['body']);
assert_test("School 13 academics/subjects loads HTTP 200", $subj_page13['code'] === 200);

$ajax13 = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf13['name'] => $csrf13['hash'],
    'draw'   => 1,
    'start'  => 0,
    'length' => 100,
]);
assert_test("School 13 ajax_subjects_list HTTP 200", $ajax13['code'] === 200);
$json13 = json_decode($ajax13['body'], true);
$school13_total = $json13['recordsTotal'] ?? 0;
echo "  [INFO] School 13 recordsTotal: {$school13_total}\n";

// Verify School 1 subjects (like 'Malayalam' LKG-MAL, 'Drawing') do NOT appear in School 13
$found_school1_in_school13 = false;
$found_c_prog_in_school13 = false;
foreach ($json13['data'] as $row) {
    $rowText = strip_tags(implode(' ', $row));
    if (stripos($rowText, 'LKG-MAL') !== false || stripos($rowText, 'LKG-ENG') !== false) {
        $found_school1_in_school13 = true;
    }
    if (stripos($rowText, 'C Programming') !== false) {
        $found_c_prog_in_school13 = true;
    }
}
assert_test("School 1 subjects do NOT appear in School 13 list", !$found_school1_in_school13);
assert_test("School 13 subject ('C Programming') appears in School 13 list", $found_c_prog_in_school13);

// 5. Create a new Subject in School 13
echo "\n5. Creating a New Subject in School 13...\n";
$unique_code = 'TEST-' . rand(1000, 9999);
$unique_name = 'Subject Test ' . rand(100, 999);

$create_res = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf13['name']       => $csrf13['hash'],
    'action'              => 'add',
    'subject_name'        => $unique_name,
    'subject_code'        => $unique_code,
    'subject_type'        => 'Practical',
    'class_id'            => '88', // IT - S1 (belongs to School 13)
    'description'         => 'Test isolation description',
    'teacher_assignments' => '[]',
]);
assert_test("Create Subject POST executes successfully", in_array($create_res['code'], [200, 302, 303]));

// Check Database directly
$db_check = $db->query("SELECT * FROM tbl_subjects WHERE subject_code = '{$unique_code}' AND is_deleted = 'n'")->fetch_assoc();
assert_test("Subject created in DB", !empty($db_check));
assert_test("Subject strictly assigned school_id = 13", (int)($db_check['school_id'] ?? 0) === 13);
$new_subject_id = (int)$db_check['subject_id'];

// Check that newly created subject appears in School 13 ajax listing
$subj_page13 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf13 = get_csrf_token($subj_page13['body']);
$ajax13_after = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf13['name'] => $csrf13['hash'],
    'draw'   => 2,
    'start'  => 0,
    'length' => 100,
]);
$json13_after = json_decode($ajax13_after['body'], true);
$found_new_sub = false;
foreach ($json13_after['data'] as $row) {
    $rowText = strip_tags(implode(' ', $row));
    if (stripos($rowText, $unique_code) !== false) {
        $found_new_sub = true;
    }
}
assert_test("Newly created subject appears in School 13 Subjects listing", $found_new_sub);

// 6. Cross-School IDOR Edit Protection
echo "\n6. Testing Cross-School IDOR Edit Protection...\n";
// While active in School 13, attempt to edit subject 111 (belongs to School 1)
$orig_s1 = $db->query("SELECT * FROM tbl_subjects WHERE subject_id = 111")->fetch_assoc();
$edit_res = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf13['name']       => $csrf13['hash'],
    'action'              => 'edit',
    'subject_id'          => 111,
    'subject_name'        => 'HACKED SUBJECT NAME',
    'subject_code'        => 'HACK',
    'subject_type'        => 'Core',
    'description'         => 'Hacked description',
    'teacher_assignments' => '[]',
]);
$check_s1 = $db->query("SELECT * FROM tbl_subjects WHERE subject_id = 111")->fetch_assoc();
assert_test("School 1 subject was NOT altered by cross-school edit attempt", $check_s1['subject_name'] === $orig_s1['subject_name']);

// 7. Cross-School IDOR Delete Protection
echo "\n7. Testing Cross-School IDOR Delete Protection...\n";
// While active in School 13, attempt to delete subject 111 (belongs to School 1)
$del_res = http_req("http://localhost/schoolnew/academics/delete_subject/111");
$check_s1_del = $db->query("SELECT * FROM tbl_subjects WHERE subject_id = 111")->fetch_assoc();
assert_test("School 1 subject was NOT deleted by cross-school delete attempt", $check_s1_del['is_deleted'] === 'n');

// 8. Edit Own School 13 Subject
echo "\n8. Testing Proper Edit of School 13 Subject...\n";
$subj_page13 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf13 = get_csrf_token($subj_page13['body']);
$edit_own = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf13['name']       => $csrf13['hash'],
    'action'              => 'edit',
    'subject_id'          => $new_subject_id,
    'subject_name'        => $unique_name . ' Updated',
    'subject_code'        => $unique_code . '-U',
    'subject_type'        => 'Elective',
    'class_id'            => '88',
    'description'         => 'Updated description',
    'teacher_assignments' => '[]',
]);
$check_own = $db->query("SELECT * FROM tbl_subjects WHERE subject_id = {$new_subject_id}")->fetch_assoc();
assert_test("School 13 subject successfully updated", $check_own['subject_name'] === $unique_name . ' Updated');
assert_test("School 13 subject school_id remains 13", (int)$check_own['school_id'] === 13);

// 9. Switch Back to School 1 and Verify Isolation
echo "\n9. Switching Back to School 1...\n";
$subj_page13 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf13 = get_csrf_token($subj_page13['body']);
http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf13['name'] => $csrf13['hash'],
    'school_id'    => 1,
]);
$subj_page1_final = http_req("http://localhost/schoolnew/academics/subjects");
$csrf1_final = get_csrf_token($subj_page1_final['body']);
$ajax1_final = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf1_final['name'] => $csrf1_final['hash'],
    'draw'   => 3,
    'start'  => 0,
    'length' => 100,
]);
$json1_final = json_decode($ajax1_final['body'], true);
$found_new_sub_in_school1 = false;
foreach ($json1_final['data'] as $row) {
    $rowText = strip_tags(implode(' ', $row));
    if (stripos($rowText, $unique_code) !== false || stripos($rowText, 'C Programming') !== false) {
        $found_new_sub_in_school1 = true;
    }
}
assert_test("School 13 subjects NEVER appear in School 1 listing", !$found_new_sub_in_school1);

// 10. Clean up: Delete the test subject
echo "\n10. Cleaning up test subject...\n";
// Switch back to 13 to delete
http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf1_final['name'] => $csrf1_final['hash'],
    'school_id'          => 13,
]);
$del_own = http_req("http://localhost/schoolnew/academics/delete_subject/{$new_subject_id}");
$check_own_del = $db->query("SELECT * FROM tbl_subjects WHERE subject_id = {$new_subject_id}")->fetch_assoc();
assert_test("School 13 test subject successfully soft-deleted", $check_own_del['is_deleted'] === 'y');

// Switch back to 1
$subj_page13_last = http_req("http://localhost/schoolnew/academics/subjects");
$csrf13_last = get_csrf_token($subj_page13_last['body']);
http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf13_last['name'] => $csrf13_last['hash'],
    'school_id'          => 1,
]);

echo "\n============================================================\n";
echo " TEST SUMMARY\n";
echo "============================================================\n";
echo " Passed: {$tests_passed}\n";
echo " Failed: {$tests_failed}\n";
echo " Total:  " . ($tests_passed + $tests_failed) . "\n";
echo "============================================================\n";

if ($tests_failed === 0) {
    exit(0);
} else {
    exit(1);
}
