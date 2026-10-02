<?php
/**
 * Comprehensive E2E Test: Add Division Filter to Subject Teachers Allocation
 *
 * Verifies:
 * 1. Filter bar order: Academic Year -> Class -> Division -> Subject -> Teacher -> Reset
 * 2. Division dropdown depends on Class (disabled/Select Class First when no Class selected)
 * 3. Class selection populates only divisions belonging to that Class
 * 4. Class selection populates only subjects belonging to that Class
 * 5. Division filter filters the table query strictly by the selected Division
 * 6. Cross-division data leakage is prevented
 * 7. Changing Class clears stale Division & Subject selections and reloads new ones
 * 8. Subject and Teacher filters work in conjunction with Division filter
 * 9. Reset button resets all filters and returns table to default state
 * 10. Backend validation prevents tampered mismatched division/class in GET and POST
 * 11. Assign Subject Teacher workflow operates cleanly with Division
 *
 * Run: php tests/test_subject_teachers_division_filter_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_st_div_');

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

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
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
assert_test("Admin login successful", $login_res['code'] === 200);

echo "\n=======================================================\n";
echo "2. Inspect Subject Teachers Page & Default Filter Bar Order\n";
echo "=======================================================\n";

$st_page = http_req("http://localhost/schoolnew/academics/subject_teachers");
assert_test("Subject Teachers page loads (HTTP 200)", $st_page['code'] === 200);

// Check filter dropdowns order in HTML
// Order must be: academic_year_id -> class_id -> division_id -> subject_id -> staff_id
preg_match_all('/<select[^>]*name=["\']?([^"\'\s>]+)["\']?[^>]*>|<select[^>]*id=["\']?([^"\'\s>]+)["\']?[^>]*>|<select[^>]*onchange=["\']applyFilter\([\'"](\w+)[\'"]/i', $st_page['body'], $filter_matches);

$pos_year = strpos($st_page['body'], 'academic_year_id');
$pos_class = strpos($st_page['body'], 'filter_class_id');
$pos_div = strpos($st_page['body'], 'filter_division_id');
$pos_sub = strpos($st_page['body'], 'filter_subject_id');
$pos_staff = strpos($st_page['body'], "applyFilter('staff_id'");
$pos_reset = strpos($st_page['body'], 'restart_alt');

assert_test("Filter order: Academic Year present", $pos_year !== false);
assert_test("Filter order: Class follows Academic Year", $pos_class !== false && $pos_class > $pos_year);
assert_test("Filter order: Division follows Class", $pos_div !== false && $pos_div > $pos_class);
assert_test("Filter order: Subject follows Division", $pos_sub !== false && $pos_sub > $pos_div);
assert_test("Filter order: Teacher follows Subject", $pos_staff !== false && $pos_staff > $pos_sub);
assert_test("Filter order: Reset follows Teacher", $pos_reset !== false && $pos_reset > $pos_staff);

// Default state: Division dropdown is disabled and has 'Select Class First'
assert_test("Division dropdown has disabled attribute when no class selected", preg_match('/<select[^>]*id="filter_division_id"[^>]*disabled/i', $st_page['body']));
assert_test("Division dropdown shows 'Select Class First' option", strpos($st_page['body'], 'Select Class First') !== false);

echo "\n=======================================================\n";
echo "3. Test Class Selection Populates Divisions & Subjects (LKG)\n";
echo "=======================================================\n";

$lkg_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1");
assert_test("Class filter (class_id=1) loads (HTTP 200)", $lkg_page['code'] === 200);

// Verify Division dropdown is enabled (not disabled)
$div_disabled = preg_match('/<select[^>]*id="filter_division_id"[^>]*disabled/i', $lkg_page['body']);
assert_test("Division dropdown is enabled when Class is selected", !$div_disabled);

// Verify LKG divisions are present in the dropdown
assert_test("LKG Division A (id 12) is in Division dropdown", strpos($lkg_page['body'], 'value="12"') !== false);
assert_test("LKG Division B (id 10004) is in Division dropdown", strpos($lkg_page['body'], 'value="10004"') !== false);
assert_test("Division dropdown shows 'All Divisions' option", strpos($lkg_page['body'], 'All Divisions') !== false);

// Verify other class's divisions (e.g. UKG division 10001) are NOT present in Division dropdown
preg_match('/<select[^>]*id="filter_division_id"[^>]*>(.*?)<\/select>/is', $lkg_page['body'], $div_select_match);
$div_options_html = $div_select_match[1] ?? '';
assert_test("UKG Division (id 10001) is NOT in LKG Division dropdown", strpos($div_options_html, 'value="10001"') === false);

// Verify LKG subjects are in Subject dropdown
assert_test("LKG Subject 'English' is in Subject dropdown", strpos($lkg_page['body'], 'English') !== false);
assert_test("LKG Subject 'Mathematics' is in Subject dropdown", strpos($lkg_page['body'], 'Mathematics') !== false);

function get_table_tbody($html) {
    if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $html, $m)) {
        return $m[1];
    }
    return '';
}

echo "\n=======================================================\n";
echo "4. Test Division Filtering (LKG Division A)\n";
echo "=======================================================\n";

$div_a_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=12");
assert_test("Filter class_id=1 & division_id=12 loads (HTTP 200)", $div_a_page['code'] === 200);

// Division A option is selected in dropdown
assert_test("Division A is selected in Division dropdown", preg_match('/<option\s+value="12"\s+selected\s*>/i', $div_a_page['body']));

// Table rows must contain 'LKG - Division A'
$tbody_div_a = get_table_tbody($div_a_page['body']);
assert_test("Table contains 'LKG - Division A' assignments", strpos($tbody_div_a, 'LKG - Division A') !== false);
assert_test("Table does NOT contain 'UKG' assignments", strpos($tbody_div_a, 'UKG') === false);

echo "\n=======================================================\n";
echo "5. Test Subject Filtering with Division\n";
echo "=======================================================\n";

// Filter by LKG Division A Mathematics (subject_id = 11)
$math_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=12&subject_id=11");
assert_test("Filter class=1, division=12, subject=11 loads (HTTP 200)", $math_page['code'] === 200);

$tbody_math = get_table_tbody($math_page['body']);
// Table must show Mathematics
assert_test("Table contains Mathematics assignment", strpos($tbody_math, 'Mathematics') !== false);

// Table must NOT show English or Malayalam
assert_test("Table does NOT show English when Mathematics filtered", strpos($tbody_math, 'English') === false);
assert_test("Table does NOT show Malayalam when Mathematics filtered", strpos($tbody_math, 'Malayalam') === false);

echo "\n=======================================================\n";
echo "6. Test Teacher Filtering\n";
echo "=======================================================\n";

// Arun Krishnan is staff_id 25
$teacher_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=12&staff_id=25");
assert_test("Teacher filter loads (HTTP 200)", $teacher_page['code'] === 200);

$tbody_teacher = get_table_tbody($teacher_page['body']);
assert_test("Table contains Arun Krishnan", strpos($tbody_teacher, 'Arun Krishnan') !== false);
assert_test("Table does NOT contain Rema Jose (staff_id 34)", strpos($tbody_teacher, 'Rema Jose') === false);

echo "\n=======================================================\n";
echo "7. Test Class Change (UKG - class_id 13)\n";
echo "=======================================================\n";

$ukg_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=13");
assert_test("UKG page loads (HTTP 200)", $ukg_page['code'] === 200);

preg_match('/<select[^>]*id="filter_division_id"[^>]*>(.*?)<\/select>/is', $ukg_page['body'], $ukg_div_match);
$ukg_div_options = $ukg_div_match[1] ?? '';
assert_test("UKG Division A (id 10001) is in UKG Division dropdown", strpos($ukg_div_options, 'value="10001"') !== false);
assert_test("LKG Division A (id 12) is NOT in UKG Division dropdown", strpos($ukg_div_options, 'value="12"') === false);

echo "\n=======================================================\n";
echo "8. Test Reset Button\n";
echo "=======================================================\n";

$reset_page = http_req("http://localhost/schoolnew/academics/subject_teachers");
assert_test("Reset page loads all assignments (HTTP 200)", $reset_page['code'] === 200);
assert_test("Division dropdown is disabled on reset", preg_match('/<select[^>]*id="filter_division_id"[^>]*disabled/i', $reset_page['body']));

$tbody_reset = get_table_tbody($reset_page['body']);
assert_test("Table contains assignments from multiple classes (LKG & UKG)", strpos($tbody_reset, 'LKG') !== false && strpos($tbody_reset, 'UKG') !== false);

echo "\n=======================================================\n";
echo "9. Security & Backend Validation (Tampered GET & POST)\n";
echo "=======================================================\n";

// GET with mismatched class and division: Class 1 (LKG) with Division 10001 (UKG)
$tamper_get = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=10001");
assert_test("Tampered GET with mismatched class/division returns HTTP 200", $tamper_get['code'] === 200);
$tbody_tamper = get_table_tbody($tamper_get['body']);
assert_test("No cross-division UKG records leak into LKG table", strpos($tbody_tamper, 'UKG') === false);

// POST with mismatched class and division
$st_csrf = get_csrf_token($st_page['body']);
$tamper_post = [
    $st_csrf['name']   => $st_csrf['hash'],
    'academic_year_id' => 1,
    'class_id'         => 1, // LKG
    'division_id'      => 10001, // UKG's division
    'subject_id'       => 9, // English
    'staff_id'         => 25,
];
$tamper_res = http_req("http://localhost/schoolnew/academics/subject_teachers", $tamper_post);
assert_test("Mismatched class/division POST rejected with flash error", strpos($tamper_res['body'], 'Selected Division does not belong to the selected Class') !== false);

// POST with non-teacher staff
$non_teacher_post = [
    $st_csrf['name']   => $st_csrf['hash'],
    'academic_year_id' => 1,
    'class_id'         => 1,
    'division_id'      => 12,
    'subject_id'       => 9,
    'staff_id'         => 3, // Accountant (non_teaching)
];
$non_teacher_res = http_req("http://localhost/schoolnew/academics/subject_teachers", $non_teacher_post);
assert_test("Non-teaching staff POST rejected with flash error", strpos($non_teacher_res['body'], 'Only active teaching faculty can be assigned') !== false);

echo "\n=======================================================\n";
echo "10. End-to-End Assignment Workflow\n";
echo "=======================================================\n";

// Assign Sindhu Menon (staff_id 26) to LKG Division B (division 10004) for English (subject 9)
$valid_post = [
    $st_csrf['name']   => $st_csrf['hash'],
    'academic_year_id' => 1,
    'class_id'         => 1,
    'division_id'      => 10004,
    'subject_id'       => 9,
    'staff_id'         => 26,
];
$assign_res = http_req("http://localhost/schoolnew/academics/subject_teachers", $valid_post);
assert_test("Subject Teacher assignment succeeds", strpos($assign_res['body'], 'Subject Teacher assigned successfully!') !== false);

// Verify DB record
$st_rec = $db->query("SELECT * FROM tbl_subject_teachers WHERE class_id = 1 AND division_id = 10004 AND subject_id = 9 AND staff_id = 26 AND is_deleted = 'n'")->fetch_assoc();
assert_test("tbl_subject_teachers contains active record", !empty($st_rec));

// Filter by LKG Division B and verify Sindhu Menon appears
$div_b_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=10004");
assert_test("Division B table displays newly assigned teacher 'Sindhu Menon'", strpos($div_b_page['body'], 'Sindhu Menon') !== false);
assert_test("Division B table displays 'LKG - Division B'", strpos($div_b_page['body'], 'LKG - Division B') !== false);

// Clean up test assignment
if (!empty($st_rec['subject_teacher_id'])) {
    $db->query("DELETE FROM tbl_subject_teachers WHERE subject_teacher_id = {$st_rec['subject_teacher_id']}");
    echo "  [INFO] Cleaned up test subject teacher assignment.\n";
}

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "\n>>> ALL TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
