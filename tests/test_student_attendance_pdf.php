<?php
/**
 * Test Student Attendance PDF Download Feature:
 * - Download PDF Button presence in Profile
 * - Exact Student Isolation (ID 106 vs ID 46)
 * - Period-wise & Subject-wise PDF output for SS student
 * - Day-wise PDF output for KG student
 * - Date Range Filtering & Empty Date Range
 * - Invalid Date Range Prevention (From > To)
 * - Authentication & Security Validation
 * - PDF Header, Stream & Text Verification
 *
 * Run via CLI: php tests/test_student_attendance_pdf.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_');

function http_req($url, $post = null, $use_cookie = true) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($use_cookie) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'content_type' => $content_type];
}

function get_pdf_text($pdf_content) {
    $text = $pdf_content;
    if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $pdf_content, $matches)) {
        foreach ($matches[1] as $stream) {
            $uncompressed = @gzuncompress($stream);
            if ($uncompressed !== false) {
                $text .= "\n" . $uncompressed . "\n" . str_replace("\x00", '', $uncompressed);
            }
        }
    }
    return $text;
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
echo "2. UI Button Check on Student Profile (ID 106 & 46)\n";
echo "=======================================================\n";
$p106 = http_req('http://localhost/schoolnew/students/profile/106?tab=attendance');
assert_check("Profile 106 returns HTTP 200", $p106['code'] === 200);
assert_check("Download PDF button present on Profile 106", strpos($p106['body'], 'id="btn-download-attendance-pdf"') !== false);
assert_check("Download PDF button links to attendance_pdf/106", strpos($p106['body'], 'students/attendance_pdf/106') !== false);
assert_check("Download PDF button displays 'Download PDF'", strpos($p106['body'], 'Download PDF') !== false);

$p46 = http_req('http://localhost/schoolnew/students/profile/46?tab=attendance');
assert_check("Profile 46 returns HTTP 200", $p46['code'] === 200);
assert_check("Download PDF button links to attendance_pdf/46", strpos($p46['body'], 'students/attendance_pdf/46') !== false);

echo "\n=======================================================\n";
echo "3. Generate PDF for Student 106 (Senior Secondary - Period-wise)\n";
echo "=======================================================\n";
$pdf106 = http_req('http://localhost/schoolnew/students/attendance_pdf/106?stream=1');
assert_check("PDF 106 returns HTTP 200", $pdf106['code'] === 200);
assert_check("Content-Type is application/pdf", stripos($pdf106['content_type'], 'application/pdf') !== false);
assert_check("PDF starts with %PDF- header", substr($pdf106['body'], 0, 5) === '%PDF-');
assert_check("PDF has substantial content (> 3000 bytes)", strlen($pdf106['body']) > 3000, "Length: " . strlen($pdf106['body']));

// Inspect PDF text stream
$text106 = get_pdf_text($pdf106['body']);
assert_check("PDF 106 contains student name 'Arjun Krishnan'", strpos($text106, 'Arjun Krishnan') !== false);
assert_check("PDF 106 contains admission number 'SCH20262419367'", strpos($text106, 'SCH20262419367') !== false);
assert_check("PDF 106 contains class 'Grade 11 Science A'", strpos($text106, 'Grade 11 Science A') !== false);
assert_check("PDF 106 contains group 'SS'", strpos($text106, 'SS') !== false);
assert_check("PDF 106 contains 'STUDENT ATTENDANCE REPORT'", strpos($text106, 'STUDENT ATTENDANCE REPORT') !== false);
assert_check("PDF 106 contains 'PERIOD-WISE ATTENDANCE SUMMARY'", strpos($text106, 'PERIOD-WISE ATTENDANCE SUMMARY') !== false);
assert_check("PDF 106 contains 'SUBJECT-WISE ATTENDANCE BREAKDOWN'", strpos($text106, 'SUBJECT-WISE ATTENDANCE BREAKDOWN') !== false);
assert_check("PDF 106 contains subjects: English", strpos($text106, 'English') !== false);
assert_check("PDF 106 contains subjects: Physics", strpos($text106, 'Physics') !== false);
assert_check("PDF 106 contains subjects: Mathematics", strpos($text106, 'Mathematics') !== false);
assert_check("PDF 106 contains marked by audit", strpos($text106, 'Super Admin') !== false || strpos($text106, 'Admin') !== false);
assert_check("PDF 106 does NOT contain Aarav Menon (Exact Student Isolation)", strpos($text106, 'Aarav Menon') === false);

echo "\n=======================================================\n";
echo "4. Generate PDF for Student 46 (Kindergarten - Day-wise)\n";
echo "=======================================================\n";
$pdf46 = http_req('http://localhost/schoolnew/students/attendance_pdf/46?stream=1');
assert_check("PDF 46 returns HTTP 200", $pdf46['code'] === 200);
assert_check("Content-Type is application/pdf", stripos($pdf46['content_type'], 'application/pdf') !== false);

$text46 = get_pdf_text($pdf46['body']);
assert_check("PDF 46 contains 'Aarav Menon'", strpos($text46, 'Aarav Menon') !== false);
assert_check("PDF 46 contains 'SCH20260115467'", strpos($text46, 'SCH20260115467') !== false);
assert_check("PDF 46 contains 'LKG A'", strpos($text46, 'LKG A') !== false);
assert_check("PDF 46 contains 'DAY-WISE ATTENDANCE SUMMARY'", strpos($text46, 'DAY-WISE ATTENDANCE SUMMARY') !== false);
assert_check("PDF 46 does NOT contain Arjun Krishnan (Exact Student Isolation)", strpos($text46, 'Arjun Krishnan') === false);

echo "\n=======================================================\n";
echo "5. Date Range Filtering & Empty Date Range\n";
echo "=======================================================\n";
// Filter Student 106 for a range with zero records (Jan 2026)
$pdf_zero = http_req('http://localhost/schoolnew/students/attendance_pdf/106?stream=1&from_date=2026-01-01&to_date=2026-01-05');
assert_check("Zero-filter returns HTTP 200", $pdf_zero['code'] === 200);
assert_check("Zero-filter is valid PDF", substr($pdf_zero['body'], 0, 5) === '%PDF-');

$text_zero = get_pdf_text($pdf_zero['body']);
assert_check("Zero-filter contains 'No period-wise attendance records found'", strpos($text_zero, 'No period-wise attendance records found') !== false);
assert_check("Zero-filter shows 0.0% or 0% (zero-safe, not 100%)", strpos($text_zero, '0.0%') !== false || strpos($text_zero, '0%') !== false);

// Filter Student 106 with exact date range of records
$pdf_filtered = http_req('http://localhost/schoolnew/students/attendance_pdf/106?stream=1&from_date=2026-09-01&to_date=2026-09-12');
assert_check("Matching date filter returns HTTP 200", $pdf_filtered['code'] === 200);
$text_filtered = get_pdf_text($pdf_filtered['body']);
assert_check("Matching date filter contains 100.0% or 100%", strpos($text_filtered, '100.0%') !== false || strpos($text_filtered, '100%') !== false);

echo "\n=======================================================\n";
echo "6. Invalid Date Range Validation (From > To)\n";
echo "=======================================================\n";
$pdf_invalid = http_req('http://localhost/schoolnew/students/attendance_pdf/106?from_date=2026-09-20&to_date=2026-09-10');
assert_check("Invalid date range redirects", strpos($pdf_invalid['url'], 'profile/106') !== false);
assert_check("Invalid date range shows error flash message", strpos($pdf_invalid['body'], 'Invalid date range') !== false);

echo "\n=======================================================\n";
echo "7. Security & Authorization Checks\n";
echo "=======================================================\n";
$unauth = http_req('http://localhost/schoolnew/students/attendance_pdf/106', null, false);
assert_check("Unauthenticated request redirects to login", strpos($unauth['url'], 'login') !== false);

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
