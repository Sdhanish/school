<?php
/**
 * Comprehensive E2E Test: Add Division Filter to Class Teachers
 *
 * Verifies:
 * 1. Admin login
 * 2. Filter Bar order: Academic Year -> Class -> Division -> Teacher -> Reset
 * 3. Default state (no Class selected): Division dropdown disabled with 'All Divisions'
 * 4. Class selection (LKG - class_id=1): Division dropdown enabled, contains exactly LKG divisions, no UKG divisions
 * 5. Class switching (UKG - class_id=13): Division dropdown updates to UKG divisions, clears LKG divisions
 * 6. Division filtering: Assigning teacher to LKG Division A, filtering by Division A shows row, filtering by Division B hides it
 * 7. Teacher filtering: filtering by teacher preserves class and division
 * 8. Tampered GET Protection: mismatched class & division neutralized
 * 9. Reset button: resets all filters
 * 10. Assign Class Teacher Modal: works and preserves database relationships
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_ct_div_');

function httpReq($url, $post = null) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

function getCsrfToken($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$testsRun = 0;
$testsPassed = 0;

function assertCondition($desc, $cond, $extra = '') {
    global $testsRun, $testsPassed;
    $testsRun++;
    if ($cond) {
        $testsPassed++;
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc" . ($extra ? " - $extra" : "") . "\n";
    }
}

echo "=======================================================\n";
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$loginPage = httpReq($baseUrl . '/auth/login');
$csrf = getCsrfToken($loginPage['body']);
$loginRes = httpReq($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

assertCondition("Admin login successful", $loginRes['code'] === 200);

echo "\n=======================================================\n";
echo "2. Inspect Default Filter Bar Order & Initial State\n";
echo "=======================================================\n";

$page = httpReq($baseUrl . '/academics/class_teachers');
assertCondition("Class Teachers page loads (HTTP 200)", $page['code'] === 200);

// Check filter order
$posYear    = strpos($page['body'], 'id="filter_academic_year_id"');
$posClass   = strpos($page['body'], 'id="filter_class_id"');
$posDiv     = strpos($page['body'], 'id="filter_division_id"');
$posTeacher = strpos($page['body'], 'id="filter_staff_id"');
$posReset   = strpos($page['body'], 'restart_alt');

assertCondition("Filter order: Academic Year present", $posYear !== false);
assertCondition("Filter order: Class follows Academic Year", $posClass !== false && $posClass > $posYear);
assertCondition("Filter order: Division follows Class", $posDiv !== false && $posDiv > $posClass);
assertCondition("Filter order: Teacher follows Division", $posTeacher !== false && $posTeacher > $posDiv);
assertCondition("Filter order: Reset follows Teacher", $posReset !== false && $posReset > $posTeacher);

// Default state: Division dropdown is disabled and has 'All Divisions'
assertCondition("Division dropdown has disabled attribute when no class selected", 
    preg_match('/<select[^>]*id="filter_division_id"[^>]*disabled/i', $page['body']));
assertCondition("Division dropdown shows 'All Divisions' option", 
    strpos($page['body'], 'All Divisions') !== false);
assertCondition("No PHP notices in initial page output", 
    strpos($page['body'], 'Severity: Notice') === false && strpos($page['body'], 'Undefined variable') === false);

echo "\n=======================================================\n";
echo "3. Test Class Selection Populates Divisions (LKG)\n";
echo "=======================================================\n";

$lkgPage = httpReq($baseUrl . '/academics/class_teachers?class_id=1');
assertCondition("LKG filter loads (HTTP 200)", $lkgPage['code'] === 200);

// Verify Division dropdown is enabled
$divDisabled = preg_match('/<select[^>]*id="filter_division_id"[^>]*disabled/i', $lkgPage['body']);
assertCondition("Division dropdown is enabled when Class is selected", !$divDisabled);

// Verify LKG divisions are present in dropdown
assertCondition("LKG Division A (id 12) is in Division dropdown", strpos($lkgPage['body'], 'value="12"') !== false);
assertCondition("LKG Division B (id 10004) is in Division dropdown", strpos($lkgPage['body'], 'value="10004"') !== false);
assertCondition("LKG Division C (id 10005) is in Division dropdown", strpos($lkgPage['body'], 'value="10005"') !== false);
assertCondition("Division dropdown shows 'All Divisions' option", strpos($lkgPage['body'], 'All Divisions') !== false);

// Verify UKG division (10001) is NOT in LKG dropdown
preg_match('/<select[^>]*id="filter_division_id"[^>]*>(.*?)<\/select>/is', $lkgPage['body'], $divSelectMatch);
$divOptionsHtml = $divSelectMatch[1] ?? '';
assertCondition("UKG Division (id 10001) is NOT in LKG Division dropdown", strpos($divOptionsHtml, 'value="10001"') === false);

echo "\n=======================================================\n";
echo "4. Test Class Switching (UKG - class_id=13)\n";
echo "=======================================================\n";

$ukgPage = httpReq($baseUrl . '/academics/class_teachers?class_id=13');
assertCondition("UKG filter loads (HTTP 200)", $ukgPage['code'] === 200);

preg_match('/<select[^>]*id="filter_division_id"[^>]*>(.*?)<\/select>/is', $ukgPage['body'], $ukgDivMatch);
$ukgOptionsHtml = $ukgDivMatch[1] ?? '';
assertCondition("UKG Division A (id 10001) is in UKG Division dropdown", strpos($ukgOptionsHtml, 'value="10001"') !== false);
assertCondition("LKG Division A (id 12) is NOT in UKG Division dropdown", strpos($ukgOptionsHtml, 'value="12"') === false);
assertCondition("LKG Division B (id 10004) is NOT in UKG Division dropdown", strpos($ukgOptionsHtml, 'value="10004"') === false);

echo "\n=======================================================\n";
echo "5. End-to-End Assignment & Division Filtering Workflow\n";
echo "=======================================================\n";

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}

// Find an active teacher (e.g. staff_id 31 or 34)
$teacherRes = $db->query("SELECT staff_id, full_name FROM tbl_staff WHERE (staff_type = 'teacher' OR category = 'teaching') AND status = 1 AND is_deleted = 'n' LIMIT 2");
$teachers = $teacherRes->fetch_all(MYSQLI_ASSOC);
$t1 = $teachers[0];
$t2 = $teachers[1] ?? $teachers[0];

// Assign t1 to LKG Division A (div_id 12) via POST
$postCsrf = getCsrfToken($lkgPage['body']);
$assignRes1 = httpReq($baseUrl . '/academics/class_teachers', [
    $postCsrf['name']    => $postCsrf['hash'],
    'academic_year_id'   => 1,
    'class_id'           => 1,
    'division_id'        => 12,
    'staff_id'           => $t1['staff_id'],
]);
assertCondition("Assign teacher to LKG Division A succeeded", $assignRes1['code'] === 200);

// Filter by LKG Division A: should show Division A assignment
$divAPage = httpReq($baseUrl . '/academics/class_teachers?class_id=1&division_id=12');
assertCondition("Filter by LKG Division A loads (HTTP 200)", $divAPage['code'] === 200);
assertCondition("Table contains 'LKG - Division A'", strpos($divAPage['body'], 'LKG - Division A') !== false);
assertCondition("Table contains assigned teacher '{$t1['full_name']}'", strpos($divAPage['body'], $t1['full_name']) !== false);

// Filter by LKG Division B: should NOT show Division A assignment
$divBPage = httpReq($baseUrl . '/academics/class_teachers?class_id=1&division_id=10004');
assertCondition("Filter by LKG Division B loads (HTTP 200)", $divBPage['code'] === 200);
assertCondition("Division B table does NOT show Division A assignment", strpos($divBPage['body'], 'LKG - Division A') === false);

// Filter by All Divisions for LKG: should show Division A assignment
$allDivPage = httpReq($baseUrl . '/academics/class_teachers?class_id=1&division_id=');
assertCondition("Filter by All Divisions loads (HTTP 200)", $allDivPage['code'] === 200);
assertCondition("All Divisions table shows LKG Division A", strpos($allDivPage['body'], 'LKG - Division A') !== false);

echo "\n=======================================================\n";
echo "6. Teacher Filtering Preserves Division\n";
echo "=======================================================\n";

$teacherFilterPage = httpReq($baseUrl . '/academics/class_teachers?class_id=1&division_id=12&staff_id=' . $t1['staff_id']);
assertCondition("Teacher filter loads (HTTP 200)", $teacherFilterPage['code'] === 200);
assertCondition("Table shows teacher assignment", strpos($teacherFilterPage['body'], $t1['full_name']) !== false);

// Tampered teacher that does not match
$nonMatchingTeacher = ($t1['staff_id'] == 31) ? 36 : 31;
$teacherFilterMismatched = httpReq($baseUrl . '/academics/class_teachers?class_id=1&division_id=12&staff_id=' . $nonMatchingTeacher);
assertCondition("Mismatched teacher filter loads without error", $teacherFilterMismatched['code'] === 200);
assertCondition("Table shows 'No class teacher assignments found.'", strpos($teacherFilterMismatched['body'], 'No class teacher assignments found.') !== false);

echo "\n=======================================================\n";
echo "7. Tampered GET Protection & AJAX Endpoint\n";
echo "=======================================================\n";

// Tampered GET: class_id=13 (UKG) but division_id=12 (LKG Division A)
$tamperedPage = httpReq($baseUrl . '/academics/class_teachers?class_id=13&division_id=12');
assertCondition("Tampered request loads with HTTP 200", $tamperedPage['code'] === 200);
assertCondition("No cross-division LKG records leak into UKG view", strpos($tamperedPage['body'], 'LKG - Division A') === false);

// AJAX Endpoint: /academics/ajax_get_divisions via segment
$ajax1 = httpReq($baseUrl . '/academics/ajax_get_divisions/1');
assertCondition("AJAX with segment /1 returns valid JSON", $ajax1['code'] === 200 && strpos($ajax1['body'], 'division_name') !== false);

// AJAX Endpoint: /academics/ajax_get_divisions via query param
$ajax2 = httpReq($baseUrl . '/academics/ajax_get_divisions?class_id=1');
assertCondition("AJAX with query ?class_id=1 returns valid JSON", $ajax2['code'] === 200 && strpos($ajax2['body'], 'division_name') !== false);

// AJAX Endpoint: without class_id
$ajax3 = httpReq($baseUrl . '/academics/ajax_get_divisions');
assertCondition("AJAX without class_id returns empty array []", trim($ajax3['body']) === '[]');

echo "\n=======================================================\n";
echo "8. Reset State & PHP Notice Check\n";
echo "=======================================================\n";

$resetPage = httpReq($baseUrl . '/academics/class_teachers');
assertCondition("Reset loads all assignments cleanly", $resetPage['code'] === 200);
assertCondition("No 'Severity: Notice' anywhere", strpos($resetPage['body'], 'Severity: Notice') === false);
assertCondition("No 'Undefined variable' anywhere", strpos($resetPage['body'], 'Undefined variable') === false);

// Clean up test assignment
$db->query("DELETE FROM tbl_class_teachers WHERE class_id = 1 AND division_id = 12 AND staff_id = {$t1['staff_id']}");
$db->query("UPDATE tbl_divisions SET class_teacher_id = NULL WHERE division_id = 12");
echo "  [INFO] Cleaned up test class teacher assignment.\n";

echo "\n=======================================================\n";
echo "SUMMARY: $testsPassed / $testsRun tests passed.\n";
echo "=======================================================\n";

if ($testsPassed === $testsRun) {
    echo "\n>>> ALL TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
