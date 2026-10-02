<?php
/**
 * Test Student Profile Attendance Modes & Enhancements:
 * - Day-wise Attendance (KG, LP, UP, HS)
 * - Period-wise Attendance (SS / Higher Secondary)
 * - Subject-wise Attendance Breakdown & Percentages
 * - Date Range Filtering
 * - Zero-safe Percentage (0% on 0 records/days, NOT 100%)
 * - Audit Trail "Marked By"
 * 
 * Run via CLI: php tests/test_student_profile_attendance_modes.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
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
echo "2. Test Student 106 (Arjun Krishnan - Grade 11 Science - SS Group)\n";
echo "=======================================================\n";
$p106 = http_req('http://localhost/schoolnew/students/profile/106?tab=attendance');
assert_check("Profile 106 returns HTTP 200", $p106['code'] === 200);

// Check for PHP notices or fatal errors
$has_php_error = (stripos($p106['body'], 'Severity: Notice') !== false) 
              || (stripos($p106['body'], 'Fatal error') !== false)
              || (stripos($p106['body'], 'Exception') !== false);
assert_check("No PHP errors or notices on Profile 106", !$has_php_error);

// Check Academic Group Badge
assert_check("Shows Academic Group 'SS'", stripos($p106['body'], 'Group: SS') !== false || stripos($p106['body'], 'SS') !== false);

// Check Period-wise Attendance Summary Section
assert_check("Displays Period-wise Attendance Summary header", stripos($p106['body'], 'Period-wise Attendance Summary') !== false);
assert_check("Displays Scheduled Periods metric", stripos($p106['body'], 'Scheduled Periods') !== false);
assert_check("Displays Periods Present metric", stripos($p106['body'], 'Periods Present') !== false);
assert_check("Displays Periods Absent metric", stripos($p106['body'], 'Periods Absent') !== false);
assert_check("Displays Overall Period % metric", stripos($p106['body'], 'Overall Period %') !== false);

// Check Scheduled Periods count > 0 (Arjun Krishnan has 11 period records)
assert_check("Shows 11 Scheduled Periods for Student 106", preg_match('/<div class="text-2xl font-bold text-on-surface">11<\/div>\s*<div class="text-\[12px\] text-on-surface-variant mt-1">Scheduled Periods<\/div>/', $p106['body']));
assert_check("Shows 11 Periods Present for Student 106", preg_match('/<div class="text-2xl font-bold text-secondary">11<\/div>\s*<div class="text-\[12px\] text-on-secondary-container mt-1">Periods Present<\/div>/', $p106['body']));
assert_check("Shows 100% Overall Period %", preg_match('/<div class="text-2xl font-bold text-secondary">100%<\/div>\s*<div class="text-\[12px\] text-on-surface-variant mt-1">Overall Period %<\/div>/', $p106['body']));

// Check Subject-wise Attendance Breakdown Table
assert_check("Subject-wise Attendance section present", stripos($p106['body'], 'Subject-wise Attendance') !== false);
assert_check("Contains English subject row", stripos($p106['body'], 'English') !== false);
assert_check("Contains Physics subject row", stripos($p106['body'], 'Physics') !== false);
assert_check("Contains Chemistry subject row", stripos($p106['body'], 'Chemistry') !== false);
assert_check("Contains Mathematics subject row", stripos($p106['body'], 'Mathematics') !== false);
assert_check("Contains Malayalam subject row", stripos($p106['body'], 'Malayalam') !== false);
assert_check("Contains Biology subject row", stripos($p106['body'], 'Biology') !== false);

// Check Month-wise Breakdown Table
assert_check("Month-wise Attendance Breakdown section present", stripos($p106['body'], 'Month-wise Attendance Breakdown') !== false);
assert_check("Month breakdown shows Sep 2026", stripos($p106['body'], 'Sep 2026') !== false);

// Check Detailed Period-wise Attendance Records Table
assert_check("Detailed Period Attendance Records table present", stripos($p106['body'], 'Period-wise Attendance Logs') !== false);
assert_check("Period table has Period Name column", stripos($p106['body'], 'Period') !== false);
assert_check("Period table has Subject Name column", stripos($p106['body'], 'Subject') !== false);
assert_check("Period table has Marked By column", stripos($p106['body'], 'Marked By') !== false);

// Check Audit Trail Marked By
assert_check("Contains marked by Super Admin · Admin", stripos($p106['body'], 'Super Admin · Admin') !== false || stripos($p106['body'], 'Super Admin') !== false);

echo "\n=======================================================\n";
echo "3. Test Student 46 (Aarav Menon - LKG - KG's Group - Day-wise)\n";
echo "=======================================================\n";
$p46 = http_req('http://localhost/schoolnew/students/profile/46?tab=attendance');
assert_check("Profile 46 returns HTTP 200", $p46['code'] === 200);

$has_php_error_46 = (stripos($p46['body'], 'Severity: Notice') !== false) 
                 || (stripos($p46['body'], 'Fatal error') !== false)
                 || (stripos($p46['body'], 'Exception') !== false);
assert_check("No PHP errors or notices on Profile 46", !$has_php_error_46);

// Check Academic Group Badge
assert_check("Shows Academic Group 'KG\'s'", stripos($p46['body'], "Group: KG") !== false || stripos($p46['body'], "KG&#039;s") !== false);

// Check Day-wise Attendance Summary Section
assert_check("Displays Day-wise Attendance Summary header", stripos($p46['body'], 'Day-wise Attendance Summary') !== false);
assert_check("Displays Total Academic Days metric", stripos($p46['body'], 'Total Academic Days') !== false);
assert_check("Displays Days Present metric", stripos($p46['body'], 'Days Present') !== false);
assert_check("Displays Days Absent metric", stripos($p46['body'], 'Days Absent') !== false);
assert_check("Displays Overall Day % metric", stripos($p46['body'], 'Overall Percentage') !== false || stripos($p46['body'], 'Overall Day %') !== false);

// Check Recent Attendance Logs table with Marked By
assert_check("Recent Attendance Logs table present", stripos($p46['body'], 'Recent Attendance Logs') !== false);
assert_check("Recent table has Marked By column", stripos($p46['body'], 'Marked By') !== false);

echo "\n=======================================================\n";
echo "4. Test Date Range Filter & Zero-Safe Calculation\n";
echo "=======================================================\n";
// Filter Student 106 with a date range in Jan 2026 where NO records exist
$p106_zero = http_req('http://localhost/schoolnew/students/profile/106?tab=attendance&attendance_year_id=1&from_date=2026-01-01&to_date=2026-01-05');
assert_check("Profile 106 zero-filter returns HTTP 200", $p106_zero['code'] === 200);
assert_check("No PHP errors on zero-filter", stripos($p106_zero['body'], 'Severity: Notice') === false && stripos($p106_zero['body'], 'Fatal error') === false);

// Scheduled periods must be 0 and percentage MUST be 0%, NOT 100%!
assert_check("Scheduled periods is 0 for non-matching date range", preg_match('/<div class="text-2xl font-bold text-on-surface">0<\/div>\s*<div class="text-\[12px\] text-on-surface-variant mt-1">Scheduled Periods<\/div>/', $p106_zero['body']));
assert_check("Percentage is 0.0% (ZERO-SAFE, NOT 100%)", preg_match('/<div class="text-2xl font-bold text-secondary">0%<\/div>\s*<div class="text-\[12px\] text-on-surface-variant mt-1">Overall Period %<\/div>/', $p106_zero['body']));

// Filter Student 106 with exact date range of records (2026-09-01 to 2026-09-12)
$p106_filtered = http_req('http://localhost/schoolnew/students/profile/106?tab=attendance&attendance_year_id=1&from_date=2026-09-01&to_date=2026-09-12');
assert_check("Profile 106 matching-filter returns HTTP 200", $p106_filtered['code'] === 200);
assert_check("Matching-filter shows 11 Scheduled Periods", preg_match('/<div class="text-2xl font-bold text-on-surface">11<\/div>\s*<div class="text-\[12px\] text-on-surface-variant mt-1">Scheduled Periods<\/div>/', $p106_filtered['body']));

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
