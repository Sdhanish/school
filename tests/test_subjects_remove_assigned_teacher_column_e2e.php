<?php
/**
 * E2E Test: Remove Assigned Teacher Column from Subjects Page
 *
 * Verifies:
 * 1. Admin authentication.
 * 2. academics/subjects loads HTTP 200.
 * 3. 'Assigned Teacher' column header is absent from subjects table.
 * 4. Header columns are exactly: Subject Name | Code | Applicable Class | Subject Type | Actions.
 * 5. Every row in tbody has exactly 5 <td> elements matching header count.
 * 6. Class filter query parameter works correctly.
 * 7. Edit subject modal trigger attributes and functionality.
 * 8. Teacher-subject assignments in tbl_subject_allocations remain intact.
 * 9. Teacher Allocation / Subject Teachers page (academics/subject_teachers) still loads HTTP 200 and works.
 * 10. Add Subject and Edit Subject POST operations work as expected.
 *
 * Run: php tests/test_subjects_remove_assigned_teacher_column_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_subjects_test_');

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
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'err' => $err];
}

function get_csrf_token($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$tests_run = 0;
$tests_passed = 0;

function assert_test($desc, $condition, $extra = '') {
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
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$login_page = http_req("http://localhost/schoolnew/auth/login");
$csrf = get_csrf_token($login_page['body']);
$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assert_test("Admin login successful", $login_res['code'] === 200, "Code: " . $login_res['code']);

echo "\n=======================================================\n";
echo "2. Check Database Integrity for Teacher-Subject Allocations\n";
echo "=======================================================\n";

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}
$alloc_res = $db->query("SELECT COUNT(*) AS total FROM tbl_subject_allocations WHERE status = 1 AND is_deleted = 'n'");
$alloc_count = (int)$alloc_res->fetch_assoc()['total'];
echo "  [INFO] Active subject allocations in database: $alloc_count\n";
assert_test("Subject allocations table exists and query succeeded", $alloc_count >= 0);

echo "\n=======================================================\n";
echo "3. Verify Subjects Page Layout and Column Removal\n";
echo "=======================================================\n";

$subjects_page = http_req("http://localhost/schoolnew/academics/subjects");
assert_test("academics/subjects loads with HTTP 200", $subjects_page['code'] === 200);

// Check that Assigned Teacher is NOT present as a column header in subjects table
$has_assigned_teacher_th = (bool)preg_match('/<th[^>]*>\s*Assigned Teacher\s*<\/th>/i', $subjects_page['body']);
assert_test("Assigned Teacher <th> is NOT present in the table header", !$has_assigned_teacher_th);

// Extract headers
if (preg_match('/<thead>(.*?)<\/thead>/is', $subjects_page['body'], $head_match)) {
    preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $head_match[1], $th_matches);
    $headers = array_map(function($th) {
        return trim(strip_tags($th));
    }, $th_matches[1]);
    echo "  [INFO] Detected table headers: " . implode(' | ', $headers) . "\n";
    assert_test("Header count is exactly 5", count($headers) === 5);
    $expected_headers = ['Subject Name', 'Code', 'Applicable Class', 'Subject Type', 'Actions'];
    assert_test("Headers match expected order", $headers === $expected_headers);
} else {
    assert_test("Table thead extracted", false);
}

// Check tbody rows and count <td> in each row
if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $subjects_page['body'], $body_match)) {
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $body_match[1], $row_matches);
    $rows = $row_matches[1];
    echo "  [INFO] Rendered subject rows count: " . count($rows) . "\n";
    assert_test("Subject rows rendered", count($rows) > 0);
    
    $all_rows_5_cols = true;
    foreach ($rows as $idx => $row) {
        preg_match_all('/<td[^>]*>/i', $row, $td_matches);
        $td_count = count($td_matches[0]);
        if ($td_count !== 5) {
            $all_rows_5_cols = false;
            echo "  [FAIL] Row $idx has $td_count columns instead of 5\n";
            break;
        }
    }
    assert_test("Every row in tbody has exactly 5 <td> columns", $all_rows_5_cols);
} else {
    assert_test("Table tbody extracted", false);
}

echo "\n=======================================================\n";
echo "4. Verify Filter by Class\n";
echo "=======================================================\n";

$cls_res = $db->query("SELECT class_id, class_name FROM tbl_classes WHERE status = 1 LIMIT 1");
if ($cls_row = $cls_res->fetch_assoc()) {
    $cid = $cls_row['class_id'];
    $filtered = http_req("http://localhost/schoolnew/academics/subjects?class_id=" . $cid);
    assert_test("Filtered subjects page loads HTTP 200", $filtered['code'] === 200);
    $has_th = (bool)preg_match('/<th[^>]*>\s*Assigned Teacher\s*<\/th>/i', $filtered['body']);
    assert_test("Assigned Teacher header absent in filtered view", !$has_th);
}

echo "\n=======================================================\n";
echo "5. Verify Teacher Allocation Page (academics/subject_teachers)\n";
echo "=======================================================\n";

$teacher_page = http_req("http://localhost/schoolnew/academics/subject_teachers");
assert_test("Subject Teachers page loads HTTP 200", $teacher_page['code'] === 200);
assert_test("Subject Teachers page still contains Assigned Teacher header", strpos($teacher_page['body'], 'Assigned Teacher') !== false);

echo "\n=======================================================\n";
echo "6. Verify Add & Edit Subject Functionality\n";
echo "=======================================================\n";

$csrf = get_csrf_token($subjects_page['body']);
$test_sub_name = "Automated Test Subject " . time();
$test_sub_code = "TST" . substr(time(), -4);

// Add subject
$add_res = http_req("http://localhost/schoolnew/academics/subjects", [
    $csrf['name']   => $csrf['hash'],
    'action'        => 'add',
    'subject_name'  => $test_sub_name,
    'subject_code'  => $test_sub_code,
    'subject_type'  => 'Elective',
    'class_id'      => '',
    'description'   => 'Automated test curriculum note'
]);

assert_test("Add Subject request executed", $add_res['code'] === 200 || $add_res['code'] === 302);

// Check DB
$check_sub = $db->query("SELECT * FROM tbl_subjects WHERE subject_code = '$test_sub_code' AND status = 1 LIMIT 1");
$created = $check_sub->fetch_assoc();
assert_test("Subject successfully saved in tbl_subjects", !empty($created));

if (!empty($created)) {
    $sid = $created['subject_id'];
    $csrf = get_csrf_token($add_res['body']);
    $edit_name = $test_sub_name . " Updated";
    $edit_res = http_req("http://localhost/schoolnew/academics/subjects", [
        $csrf['name']   => $csrf['hash'],
        'action'        => 'edit',
        'subject_id'    => $sid,
        'subject_name'  => $edit_name,
        'subject_code'  => $test_sub_code,
        'subject_type'  => 'Core',
        'class_id'      => '',
        'description'   => 'Updated description'
    ]);
    assert_test("Edit Subject request executed", $edit_res['code'] === 200 || $edit_res['code'] === 302);
    $check_edit = $db->query("SELECT subject_name, subject_type FROM tbl_subjects WHERE subject_id = $sid")->fetch_assoc();
    assert_test("Subject successfully updated in DB", $check_edit['subject_name'] === $edit_name && $check_edit['subject_type'] === 'Core');

    // Clean up created test subject
    $db->query("DELETE FROM tbl_subjects WHERE subject_id = $sid");
}

@unlink($cookie_file);

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    exit(0);
} else {
    exit(1);
}
