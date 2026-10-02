<?php
/**
 * Test Suite: School Location Change from "Kakkanad" to "Kaloor" Verification
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'test_kaloor_');

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

function assert_check($desc, $cond, $detail = '') {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($cond) {
        $tests_passed++;
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc" . ($detail ? " ($detail)" : "") . "\n";
    }
}

echo "=======================================================\n";
echo "1. Database Verification\n";
echo "=======================================================\n";
$mysqli = new mysqli('localhost', 'root', '', 'db_school');
assert_check("Database connection established", !$mysqli->connect_error);

$res = $mysqli->query("SELECT * FROM tbl_school_settings WHERE setting_id = 1");
$settings = $res->fetch_assoc();
assert_check("tbl_school_settings setting_id=1 exists", !empty($settings));
assert_check("tbl_school_settings address contains 'Kaloor'", strpos($settings['address'], 'Kaloor') !== false, $settings['address']);
assert_check("tbl_school_settings address is exactly 'Kaloor, Ernakulam, Kerala - 682030'", $settings['address'] === 'Kaloor, Ernakulam, Kerala - 682030', $settings['address']);
assert_check("tbl_school_settings address does NOT contain 'Kakkanad'", stripos($settings['address'], 'Kakkanad') === false);

echo "\n=======================================================\n";
echo "2. Admin Authentication\n";
echo "=======================================================\n";
$login_page = http_req('http://localhost/schoolnew/auth/login');
preg_match('/name="csrf_test_name" value="([a-f0-9]+)"/i', $login_page['body'], $matches);
$csrf = $matches[1] ?? '';

$login = http_req('http://localhost/schoolnew/auth/login', [
    'csrf_test_name' => $csrf,
    'email'          => 'admin@gmail.com',
    'password'       => '123456'
]);
assert_check("Admin login successful", strpos($login['url'], 'dashboard') !== false || strpos($login['url'], 'overview') !== false || $login['code'] === 200);

echo "\n=======================================================\n";
echo "3. School Settings Page Web UI\n";
echo "=======================================================\n";
$settings_page = http_req('http://localhost/schoolnew/settings');
assert_check("Settings page loads HTTP 200", $settings_page['code'] === 200);
assert_check("Settings page address value contains 'Kaloor'", strpos($settings_page['body'], 'value="Kaloor, Ernakulam, Kerala - 682030"') !== false);
assert_check("Settings page placeholder contains 'Kaloor'", strpos($settings_page['body'], 'placeholder="Kaloor, Ernakulam, Kerala - 682030"') !== false);
assert_check("Settings page does not contain 'Kakkanad'", stripos($settings_page['body'], 'Kakkanad') === false);

echo "\n=======================================================\n";
echo "4. Student Attendance PDF Inspection\n";
echo "=======================================================\n";
$pdf_res = http_req('http://localhost/schoolnew/students/attendance_pdf/106?stream=1');
assert_check("Student Attendance PDF returns HTTP 200", $pdf_res['code'] === 200);
assert_check("Content-Type is application/pdf", stripos($pdf_res['content_type'], 'application/pdf') !== false);

$pdf_text = get_pdf_text($pdf_res['body']);
assert_check("Attendance PDF header contains 'Kaloor'", strpos($pdf_text, 'Kaloor') !== false);
assert_check("Attendance PDF header contains 'Kaloor, Ernakulam, Kerala - 682030'", strpos($pdf_text, 'Kaloor, Ernakulam, Kerala - 682030') !== false);
assert_check("Attendance PDF header does NOT contain 'Kakkanad'", stripos($pdf_text, 'Kakkanad') === false);

echo "\n=======================================================\n";
echo "5. Student ID Card Settings & Previews\n";
echo "=======================================================\n";
$id_page = http_req('http://localhost/schoolnew/students/id_cards?student_id=106');
assert_check("ID Cards page loads HTTP 200", $id_page['code'] === 200);
assert_check("ID Cards page displays 'Kaloor'", strpos($id_page['body'], 'Kaloor') !== false);
assert_check("ID Cards page does NOT contain 'Kakkanad'", stripos($id_page['body'], 'Kakkanad') === false);

echo "\n=======================================================\n";
echo "6. Certificates Preview / Print (Centralized School Address)\n";
echo "=======================================================\n";
$cert_page = http_req('http://localhost/schoolnew/certificates');
assert_check("Certificates page loads HTTP 200", $cert_page['code'] === 200);
assert_check("Certificates page does NOT contain 'Kakkanad'", stripos($cert_page['body'], 'Kakkanad') === false);

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
