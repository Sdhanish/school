<?php
/**
 * Comprehensive E2E Test Suite for Subject Teacher Assignment & School Isolation
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_sta_');

function http_req($url, $post = null, $headers = []) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
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
echo " SUBJECT TEACHER ASSIGNMENT E2E TEST SUITE\n";
echo "============================================================\n\n";

// 1. Authenticate as Super Admin
echo "1. Authenticating as Super Admin...\n";
$login_page = http_req("http://localhost/schoolnew/auth/login");
$csrf = get_csrf_token($login_page['body']);
$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assert_test("Admin login HTTP 200", $login_res['code'] === 200);

// 2. Switch to School 13 (School Test)
echo "\n2. Switching to School 13 (School Test)...\n";
$dash = http_req("http://localhost/schoolnew/dashboard");
$csrf = get_csrf_token($dash['body']);
$switch_res = http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf['name'] => $csrf['hash'],
    'school_id'    => 13,
], ['X-Requested-With: XMLHttpRequest']);
assert_test("Switch school to 13 returns HTTP 200", $switch_res['code'] === 200);
$switch_json = json_decode($switch_res['body'], true);
assert_test("Active school is 13", ($switch_json['school_id'] ?? 0) === 13);
assert_test("Active academic year is 24 (2026-2027)", ($switch_json['academic_year_id'] ?? 0) === 24);

// 3. Inspect Subjects Page in School 13
echo "\n3. Inspecting Subjects Page in School 13...\n";
$subj_page = http_req("http://localhost/schoolnew/academics/subjects");
assert_test("Subjects page HTTP 200", $subj_page['code'] === 200);
$csrf = get_csrf_token($subj_page['body']);

// Verify School 13 classes exist in HTML
assert_test("Class 'IT - S1' (88) present in class options", strpos($subj_page['body'], 'value="88"') !== false);
assert_test("Class 'IT-S2' (93) present in class options", strpos($subj_page['body'], 'value="93"') !== false);

// Verify ALL_TEACHERS JS variable only contains School 13 teachers
if (preg_match('/var ALL_TEACHERS = (\[.*?\]);/s', $subj_page['body'], $m)) {
    $teachers_in_js = json_decode($m[1], true);
    $staff_ids = array_column($teachers_in_js, 'staff_id');
    assert_test("Teacher 'test' (129) is in ALL_TEACHERS", in_array(129, $staff_ids));
    assert_test("School 1 teachers (e.g. 25 Arun Krishnan) NOT in ALL_TEACHERS", !in_array(25, $staff_ids));
} else {
    assert_test("ALL_TEACHERS found in HTML", false, "Could not match ALL_TEACHERS");
}

// 4. Test Assigning Teacher to Subject 113 (C Programming)
echo "\n4. Assigning Teacher to Subject 113 (C Programming)...\n";
$edit_res = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf['name']         => $csrf['hash'],
    'action'              => 'edit',
    'subject_id'          => 113,
    'subject_name'        => 'C Programming',
    'subject_code'        => 'ITT 201',
    'subject_type'        => 'Core',
    'class_id'            => 88,
    'description'         => 'Advanced C Course',
    'teacher_assignments' => json_encode([
        ['class_id' => 88, 'division_id' => 10126, 'staff_id' => 129]
    ])
]);
assert_test("Edit Subject POST returned HTTP 200", $edit_res['code'] === 200);

// Check DB directly
$res = $db->query("SELECT * FROM tbl_subject_teachers WHERE subject_id = 113 AND school_id = 13 AND academic_year_id = 24 AND is_deleted = 'n' AND status = 1");
$row = $res->fetch_assoc();
assert_test("Assignment record created in tbl_subject_teachers", !empty($row));
assert_test("Assignment staff_id is 129 (test)", ($row['staff_id'] ?? 0) == 129);
assert_test("Assignment class_id is 88", ($row['class_id'] ?? 0) == 88);
assert_test("Assignment division_id is 10126", ($row['division_id'] ?? 0) == 10126);

// Check tbl_subjects.teacher_id updated
$s_res = $db->query("SELECT teacher_id FROM tbl_subjects WHERE subject_id = 113 AND school_id = 13");
$s_row = $s_res->fetch_assoc();
assert_test("tbl_subjects.teacher_id synchronized to 129", ($s_row['teacher_id'] ?? 0) == 129);

// 5. Test ajax_get_subject_assignments
echo "\n5. Testing ajax_get_subject_assignments...\n";
$get_assign_res = http_req("http://localhost/schoolnew/academics/ajax_get_subject_assignments?subject_id=113&academic_year_id=24");
assert_test("ajax_get_subject_assignments returns HTTP 200", $get_assign_res['code'] === 200);
$assign_data = json_decode($get_assign_res['body'], true);
assert_test("Assignment returned in JSON", !empty($assign_data) && count($assign_data) >= 1);
assert_test("Assignment has teacher_name = 'test'", ($assign_data[0]['teacher_name'] ?? '') === 'test');
assert_test("Assignment has division_name = 'A'", ($assign_data[0]['division_name'] ?? '') === 'A');

// 6. Test Subjects DataTable Teachers Column Output
echo "\n6. Testing Subjects DataTable Teachers Column Output...\n";
$dt_res = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf['name'] => $csrf['hash'],
    'draw'        => 1,
    'start'       => 0,
    'length'      => 25,
], ['X-Requested-With: XMLHttpRequest']);
assert_test("ajax_subjects_list returns HTTP 200", $dt_res['code'] === 200);
$dt_json = json_decode($dt_res['body'], true);
assert_test("ajax_subjects_list returns valid JSON", is_array($dt_json));

$found_teacher_in_dt = false;
foreach ($dt_json['data'] as $sub_row) {
    if (strpos($sub_row[4], 'test') !== false) {
        $found_teacher_in_dt = true;
        break;
    }
}
assert_test("Assigned teacher name 'test' appears in DataTable Teachers column", $found_teacher_in_dt);

// 7. Test Removing Assigned Teacher
echo "\n7. Testing Removing Teacher Assignment (Reconciliation)...\n";
$subj_page2 = http_req("http://localhost/schoolnew/academics/subjects");
$csrf2 = get_csrf_token($subj_page2['body']);

// Submit with empty teacher_assignments []
$edit_remove_res = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf2['name']        => $csrf2['hash'],
    'action'              => 'edit',
    'subject_id'          => 113,
    'subject_name'        => 'C Programming',
    'subject_code'        => 'ITT 201',
    'subject_type'        => 'Core',
    'class_id'            => 88,
    'description'         => 'Advanced C Course',
    'teacher_assignments' => '[]'
]);
assert_test("Remove assignments POST returns HTTP 200", $edit_remove_res['code'] === 200);

// Verify soft deletion in tbl_subject_teachers
$res_del = $db->query("SELECT * FROM tbl_subject_teachers WHERE subject_id = 113 AND school_id = 13 AND academic_year_id = 24 AND is_deleted = 'y' AND status = 0");
$row_del = $res_del->fetch_assoc();
assert_test("Previous assignment soft-deleted in tbl_subject_teachers", !empty($row_del));

// Verify tbl_subjects.teacher_id reset to NULL
$s_res2 = $db->query("SELECT teacher_id FROM tbl_subjects WHERE subject_id = 113 AND school_id = 13");
$s_row2 = $s_res2->fetch_assoc();
assert_test("tbl_subjects.teacher_id reset to NULL", empty($s_row2['teacher_id']));

// Verify DataTable now shows '—'
$dt_res2 = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf2['name'] => $csrf2['hash'],
    'draw'         => 2,
    'start'        => 0,
    'length'       => 25,
], ['X-Requested-With: XMLHttpRequest']);
$dt_json2 = json_decode($dt_res2['body'], true);
$found_dash_in_dt = false;
foreach ($dt_json2['data'] as $sub_row) {
    if (strpos($sub_row[4], '—') !== false) {
        $found_dash_in_dt = true;
        break;
    }
}
assert_test("DataTable displays '—' when no teacher assigned", $found_dash_in_dt);

// 8. Re-assign Teacher to leave subject configured properly
echo "\n8. Re-assigning Teacher for active use...\n";
http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf2['name']        => $csrf2['hash'],
    'action'              => 'edit',
    'subject_id'          => 113,
    'subject_name'        => 'C Programming',
    'subject_code'        => 'ITT 201',
    'subject_type'        => 'Core',
    'class_id'            => 88,
    'description'         => 'Advanced C Course',
    'teacher_assignments' => json_encode([
        ['class_id' => 88, 'division_id' => 10126, 'staff_id' => 129]
    ])
]);

// 9. Switch back to School 1 (Login2) and verify strict isolation
echo "\n9. Testing Multi-School Isolation in School 1 (Login2)...\n";
$dash2 = http_req("http://localhost/schoolnew/dashboard");
$csrf3 = get_csrf_token($dash2['body']);
http_req("http://localhost/schoolnew/settings/schools/switch", [
    $csrf3['name'] => $csrf3['hash'],
    'school_id'    => 1,
], ['X-Requested-With: XMLHttpRequest']);

$dt_school1 = http_req("http://localhost/schoolnew/academics/ajax_subjects_list", [
    $csrf3['name'] => $csrf3['hash'],
    'draw'         => 3,
    'start'        => 0,
    'length'       => 25,
], ['X-Requested-With: XMLHttpRequest']);
$dt_s1_json = json_decode($dt_school1['body'], true);

$leak_found = false;
foreach ($dt_s1_json['data'] as $r) {
    if (strpos($r[4], 'test') !== false) {
        $leak_found = true;
    }
}
assert_test("School 13 teacher 'test' does NOT appear in School 1 listing", !$leak_found);

echo "\n============================================================\n";
echo " TEST SUMMARY\n";
echo "============================================================\n";
echo " Passed: {$tests_passed}\n";
echo " Failed: {$tests_failed}\n";
echo " Total:  " . ($tests_passed + $tests_failed) . "\n";
echo "============================================================\n";

if ($tests_failed > 0) {
    exit(1);
}
exit(0);
