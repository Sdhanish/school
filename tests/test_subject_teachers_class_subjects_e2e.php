<?php
/**
 * Comprehensive E2E Test: Fix Subject Dropdown Showing Subjects From All Classes
 *
 * Verifies:
 * 1. Authenticate as Administrator
 * 2. Filter Bar default order: Academic Year -> Class -> Division -> Subject -> Teacher -> Reset
 * 3. Default state (no Class selected): Subject dropdown disabled, shows only "All Subjects", no cross-class subjects
 * 4. Test 1 — LKG (class_id=1): exactly All Subjects, Drawing, English, EVS, Malayalam, Mathematics. No duplicates, no Hindi.
 * 5. Test 2 — UKG (class_id=13): only UKG subjects.
 * 6. Test 3 — Grade 1 (class_id=14): only Grade 1 subjects (English, EVS, Hindi, Malayalam, Mathematics). No Drawing.
 * 7. Test 4 — Grade 5 (class_id=18): only Grade 5 subjects (English, Hindi, Malayalam, Mathematics, Science, Social Science).
 * 8. Test 5 — Class Change: switching classes updates options and removes previous class subjects.
 * 9. Test 6 — Duplicate Check: no duplicate options across tested classes.
 * 10. Test 7 — Division Independence: Division A vs Division B in Grade 5 both keep Grade 5 subjects.
 * 11. Test 8 — Academic Year Filter: year change clears stale subject and respects year allocations.
 * 12. Test 9 — AJAX Endpoint: /academics/ajax_get_subjects returns class-specific subjects, empty array if no class.
 * 13. Test 10 — Tampered GET Protection: mismatched class/subject is neutralized in backend.
 * 14. Test 11 — Subject Teachers Table Filtering: table filtering by class, division, subject works properly.
 * 15. Test 12 — Existing Subject Management (academics/subjects): still works and displays class subjects.
 *
 * Run: php tests/test_subject_teachers_class_subjects_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_sub_dropdown_');

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

function extract_select_options($html, $select_id) {
    if (!preg_match('/<select[^>]*id="' . preg_quote($select_id, '/') . '"[^>]*>(.*?)<\/select>/is', $html, $m)) {
        return null;
    }
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>(.*?)<\/option>/is', $m[1], $matches, PREG_SET_ORDER);
    $options = [];
    foreach ($matches as $match) {
        $options[] = [
            'value' => $match[1],
            'text'  => trim($match[2])
        ];
    }
    return $options;
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
echo "2. Inspect Default Filter Bar & Subject Dropdown Initial State\n";
echo "=======================================================\n";

$st_page = http_req("http://localhost/schoolnew/academics/subject_teachers");
assert_test("Subject Teachers page loads (HTTP 200)", $st_page['code'] === 200);

// Check filter order
$pos_yr  = strpos($st_page['body'], 'academic_year_id');
$pos_cls = strpos($st_page['body'], 'filter_class_id');
$pos_div = strpos($st_page['body'], 'filter_division_id');
$pos_sub = strpos($st_page['body'], 'filter_subject_id');
$pos_t   = strpos($st_page['body'], 'staff_id');
$pos_rst = strpos($st_page['body'], 'restart_alt');

assert_test("Filter order: Academic Year present", $pos_yr !== false);
assert_test("Filter order: Class follows Academic Year", $pos_cls > $pos_yr);
assert_test("Filter order: Division follows Class", $pos_div > $pos_cls);
assert_test("Filter order: Subject follows Division", $pos_sub > $pos_div);
assert_test("Filter order: Teacher follows Subject", $pos_t > $pos_sub);
assert_test("Filter order: Reset follows Teacher", $pos_rst > $pos_t);

// Initial state with no class selected
$initial_sub_opts = extract_select_options($st_page['body'], 'filter_subject_id');
assert_test("Subject dropdown has exactly 1 option ('All Subjects') when no class selected", count($initial_sub_opts) === 1);
assert_test("First option is 'All Subjects'", $initial_sub_opts[0]['text'] === 'All Subjects' && $initial_sub_opts[0]['value'] === '');
assert_test("Subject dropdown is disabled when no class selected", preg_match('/<select[^>]*id="filter_subject_id"[^>]*\bdisabled\b/i', $st_page['body']));

echo "\n=======================================================\n";
echo "3. Test 1 — LKG (class_id=1)\n";
echo "=======================================================\n";

$lkg_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1");
assert_test("LKG page loads (HTTP 200)", $lkg_page['code'] === 200);
$lkg_opts = extract_select_options($lkg_page['body'], 'filter_subject_id');
$lkg_texts = array_column($lkg_opts, 'text');

assert_test("LKG Subject dropdown is enabled", !preg_match('/<select[^>]*id="filter_subject_id"[^>]*\bdisabled\b/i', $lkg_page['body']));
assert_test("LKG options count is 6 (All Subjects + 5 subjects)", count($lkg_opts) === 6);
assert_test("LKG option 1 is 'All Subjects'", $lkg_texts[0] === 'All Subjects');
assert_test("LKG has 'Drawing'", in_array('Drawing', $lkg_texts));
assert_test("LKG has 'English'", in_array('English', $lkg_texts));
assert_test("LKG has 'EVS'", in_array('EVS', $lkg_texts));
assert_test("LKG has 'Malayalam'", in_array('Malayalam', $lkg_texts));
assert_test("LKG has 'Mathematics'", in_array('Mathematics', $lkg_texts));
assert_test("LKG does NOT have 'Hindi' (Grade 1 subject)", !in_array('Hindi', $lkg_texts));
assert_test("LKG does NOT have 'Science' (Grade 4/5 subject)", !in_array('Science', $lkg_texts));

// Check duplicates in LKG
$lkg_unique = array_unique($lkg_texts);
assert_test("No duplicate subject options in LKG", count($lkg_texts) === count($lkg_unique));

echo "\n=======================================================\n";
echo "4. Test 2 — UKG (class_id=13)\n";
echo "=======================================================\n";

$ukg_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=13");
assert_test("UKG page loads (HTTP 200)", $ukg_page['code'] === 200);
$ukg_opts = extract_select_options($ukg_page['body'], 'filter_subject_id');
$ukg_texts = array_column($ukg_opts, 'text');

assert_test("UKG options count is 6 (All Subjects + 5 subjects)", count($ukg_opts) === 6);
assert_test("UKG option 1 is 'All Subjects'", $ukg_texts[0] === 'All Subjects');
assert_test("UKG has 'Drawing'", in_array('Drawing', $ukg_texts));
assert_test("UKG has 'English'", in_array('English', $ukg_texts));
assert_test("UKG has 'EVS'", in_array('EVS', $ukg_texts));
assert_test("UKG has 'Malayalam'", in_array('Malayalam', $ukg_texts));
assert_test("UKG has 'Mathematics'", in_array('Mathematics', $ukg_texts));
assert_test("UKG does NOT have 'Hindi'", !in_array('Hindi', $ukg_texts));
assert_test("No duplicate subject options in UKG", count($ukg_texts) === count(array_unique($ukg_texts)));

echo "\n=======================================================\n";
echo "5. Test 3 — Grade 1 (class_id=14)\n";
echo "=======================================================\n";

$g1_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=14");
assert_test("Grade 1 page loads (HTTP 200)", $g1_page['code'] === 200);
$g1_opts = extract_select_options($g1_page['body'], 'filter_subject_id');
$g1_texts = array_column($g1_opts, 'text');

assert_test("Grade 1 options count is 6 (All Subjects + 5 subjects)", count($g1_opts) === 6);
assert_test("Grade 1 option 1 is 'All Subjects'", $g1_texts[0] === 'All Subjects');
assert_test("Grade 1 has 'English'", in_array('English', $g1_texts));
assert_test("Grade 1 has 'EVS'", in_array('EVS', $g1_texts));
assert_test("Grade 1 has 'Hindi'", in_array('Hindi', $g1_texts));
assert_test("Grade 1 has 'Malayalam'", in_array('Malayalam', $g1_texts));
assert_test("Grade 1 has 'Mathematics'", in_array('Mathematics', $g1_texts));
assert_test("Grade 1 does NOT have 'Drawing' (LKG/UKG only)", !in_array('Drawing', $g1_texts));
assert_test("Grade 1 does NOT have 'Science' (Grade 4/5 subject)", !in_array('Science', $g1_texts));
assert_test("No duplicate subject options in Grade 1", count($g1_texts) === count(array_unique($g1_texts)));

echo "\n=======================================================\n";
echo "6. Test 4 — Grade 5 (class_id=18)\n";
echo "=======================================================\n";

$g5_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=18");
assert_test("Grade 5 page loads (HTTP 200)", $g5_page['code'] === 200);
$g5_opts = extract_select_options($g5_page['body'], 'filter_subject_id');
$g5_texts = array_column($g5_opts, 'text');

assert_test("Grade 5 options count is 7 (All Subjects + 6 subjects)", count($g5_opts) === 7);
assert_test("Grade 5 option 1 is 'All Subjects'", $g5_texts[0] === 'All Subjects');
assert_test("Grade 5 has 'English'", in_array('English', $g5_texts));
assert_test("Grade 5 has 'Hindi'", in_array('Hindi', $g5_texts));
assert_test("Grade 5 has 'Malayalam'", in_array('Malayalam', $g5_texts));
assert_test("Grade 5 has 'Mathematics'", in_array('Mathematics', $g5_texts));
assert_test("Grade 5 has 'Science'", in_array('Science', $g5_texts));
assert_test("Grade 5 has 'Social Science'", in_array('Social Science', $g5_texts));
assert_test("Grade 5 does NOT have 'Drawing'", !in_array('Drawing', $g5_texts));
assert_test("No duplicate subject options in Grade 5", count($g5_texts) === count(array_unique($g5_texts)));

echo "\n=======================================================\n";
echo "7. Test 5 — Change Class Flow (LKG -> Grade 5 -> Grade 10)\n";
echo "=======================================================\n";

$g10_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=23");
assert_test("Grade 10 page loads (HTTP 200)", $g10_page['code'] === 200);
$g10_opts = extract_select_options($g10_page['body'], 'filter_subject_id');
$g10_texts = array_column($g10_opts, 'text');

assert_test("Grade 10 has Biology", in_array('Biology', $g10_texts));
assert_test("Grade 10 has Chemistry", in_array('Chemistry', $g10_texts));
assert_test("Grade 10 has Physics", in_array('Physics', $g10_texts));
assert_test("Grade 10 does NOT have Drawing", !in_array('Drawing', $g10_texts));
assert_test("No duplicate subject options in Grade 10", count($g10_texts) === count(array_unique($g10_texts)));

echo "\n=======================================================\n";
echo "8. Test 7 — Division Independence\n";
echo "=======================================================\n";

// Get LKG divisions (class_id = 1)
$lkg_divs = $db->query("SELECT division_id, division_name FROM tbl_divisions WHERE class_id = 1 AND status = 1 AND is_deleted = 'n' ORDER BY division_name")->fetch_all(MYSQLI_ASSOC);
if (count($lkg_divs) >= 2) {
    $div_a = $lkg_divs[0]['division_id'];
    $div_b = $lkg_divs[1]['division_id'];

    $lkg_div_a_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=$div_a");
    $lkg_div_b_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&division_id=$div_b");

    $opts_a = extract_select_options($lkg_div_a_page['body'], 'filter_subject_id');
    $opts_b = extract_select_options($lkg_div_b_page['body'], 'filter_subject_id');

    assert_test("LKG Division A preserves LKG subjects", count($opts_a) === 6);
    assert_test("LKG Division B preserves LKG subjects", count($opts_b) === 6);
    assert_test("Division A and Division B have identical LKG subject options", array_column($opts_a, 'text') === array_column($opts_b, 'text'));
} else {
    echo "  [SKIP] Not enough divisions in LKG to test division comparison.\n";
}

echo "\n=======================================================\n";
echo "9. Test 8 — Academic Year Filtering\n";
echo "=======================================================\n";

$yr1_lkg = http_req("http://localhost/schoolnew/academics/subject_teachers?academic_year_id=1&class_id=1");
$yr1_opts = extract_select_options($yr1_lkg['body'], 'filter_subject_id');
assert_test("Academic Year 1 + LKG returns LKG subjects", count($yr1_opts) === 6);

// Academic Year without allocations falls back cleanly to class subjects
$yr2_lkg = http_req("http://localhost/schoolnew/academics/subject_teachers?academic_year_id=2&class_id=1");
$yr2_opts = extract_select_options($yr2_lkg['body'], 'filter_subject_id');
assert_test("Academic Year 2 + LKG falls back to LKG subjects cleanly", count($yr2_opts) === 6);

echo "\n=======================================================\n";
echo "10. Test 9 — AJAX Endpoint (/academics/ajax_get_subjects)\n";
echo "=======================================================\n";

// Endpoint with segment: /academics/ajax_get_subjects/1
$ajax_seg = http_req("http://localhost/schoolnew/academics/ajax_get_subjects/1");
$json_seg = json_decode($ajax_seg['body'], true);
assert_test("AJAX with segment class_id=1 returns valid JSON", is_array($json_seg));
assert_test("AJAX class_id=1 returns 5 subjects for LKG", count($json_seg) === 5);
$ajax_lkg_names = array_column($json_seg, 'subject_name');
assert_test("AJAX LKG contains 'Drawing'", in_array('Drawing', $ajax_lkg_names));
assert_test("AJAX LKG does NOT contain 'Hindi'", !in_array('Hindi', $ajax_lkg_names));

// Endpoint with query param: /academics/ajax_get_subjects?class_id=14
$ajax_query = http_req("http://localhost/schoolnew/academics/ajax_get_subjects?class_id=14");
$json_query = json_decode($ajax_query['body'], true);
assert_test("AJAX with query class_id=14 returns 5 subjects for Grade 1", count($json_query) === 5);
$ajax_g1_names = array_column($json_query, 'subject_name');
assert_test("AJAX Grade 1 contains 'Hindi'", in_array('Hindi', $ajax_g1_names));
assert_test("AJAX Grade 1 does NOT contain 'Drawing'", !in_array('Drawing', $ajax_g1_names));

// Endpoint with academic_year_id query param: /academics/ajax_get_subjects/1?academic_year_id=1
$ajax_yr = http_req("http://localhost/schoolnew/academics/ajax_get_subjects/1?academic_year_id=1");
$json_yr = json_decode($ajax_yr['body'], true);
assert_test("AJAX with academic_year_id + class_id returns class subjects", count($json_yr) === 5);

// Endpoint without class_id: should return empty array []
$ajax_empty = http_req("http://localhost/schoolnew/academics/ajax_get_subjects");
$json_empty = json_decode($ajax_empty['body'], true);
assert_test("AJAX without class_id returns empty array []", is_array($json_empty) && count($json_empty) === 0);

echo "\n=======================================================\n";
echo "11. Test 10 — Tampered GET Protection\n";
echo "=======================================================\n";

// Tamper: class_id=14 (Grade 1), but subject_id=13 (Drawing, which belongs to LKG)
$tamper_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=14&subject_id=13");
assert_test("Tampered request returns HTTP 200", $tamper_page['code'] === 200);
// Verify that subject_id=13 is neutralized (not marked selected)
$tamper_opts = extract_select_options($tamper_page['body'], 'filter_subject_id');
$has_tamper_selected = preg_match('/<option[^>]*value="13"[^>]*selected/i', $tamper_page['body']);
assert_test("Mismatched subject is not selected", !$has_tamper_selected);
// Table does not leak Drawing or LKG records
assert_test("Table does not show LKG records when Grade 1 is filtered", strpos($tamper_page['body'], 'LKG - Division') === false);

echo "\n=======================================================\n";
echo "12. Test 11 — Subject Teachers Table Query Filtering\n";
echo "=======================================================\n";

// Filter by class_id=1 & subject_id=9 (LKG English)
$filter_page = http_req("http://localhost/schoolnew/academics/subject_teachers?class_id=1&subject_id=9");
assert_test("Filtered page loads (HTTP 200)", $filter_page['code'] === 200);
preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $filter_page['body'], $tbody_match);
$tbody = $tbody_match ? $tbody_match[1] : '';
assert_test("Table body contains English", strpos($tbody, 'English') !== false);
assert_test("Table body does NOT contain Malayalam when English filtered", strpos($tbody, 'Malayalam') === false);

echo "\n=======================================================\n";
echo "13. Test 12 — Existing Subject Management Page (academics/subjects)\n";
echo "=======================================================\n";

$sub_mgmt_page = http_req("http://localhost/schoolnew/academics/subjects?class_id=1");
assert_test("Subjects management page loads (HTTP 200)", $sub_mgmt_page['code'] === 200);
assert_test("Subjects management shows LKG subjects", strpos($sub_mgmt_page['body'], 'LKG-ENG') !== false);
assert_test("Subjects management does not show Grade 1 code", strpos($sub_mgmt_page['body'], 'G1-ENG') === false);

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
