<?php
// tests/test_http_endpoints.php

// Authenticate first
echo "Authenticating session...\n";
$pdo = new PDO('mysql:host=localhost;dbname=db_school', 'root', '');
$user = $pdo->query("SELECT u.username, u.password FROM tbl_users u WHERE u.username = 'Admin' AND u.status = 'Active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$loginUrl = 'http://localhost/schoolnew/auth/login';
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// Step A: fetch login page to get CSRF token
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$loginPage = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_test_name" value="([^"]+)"/', $loginPage, $m);
$csrf = $m[1] ?? '';

// Step B: Post login
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_test_name' => $csrf,
    'email'          => 'Admin',
    'password'       => '123456'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

function check_url($url, $patterns, $cookieFile) {
    echo "Testing URL: $url\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  HTTP Status: $httpCode\n";
    if ($httpCode !== 200) {
        echo "  [FAIL] Expected status 200, got $httpCode\n";
        return false;
    }

    $allPassed = true;
    foreach ($patterns as $pattern => $label) {
        if (strpos($html, $pattern) !== false) {
            echo "  [PASS] Found: $label ($pattern)\n";
        } else {
            echo "  [FAIL] Missing: $label ($pattern)\n";
            $allPassed = false;
        }
    }
    return $allPassed;
}

echo "=== CHECK 1: Student Registration (students/add) ===\n";
$add_patterns = [
    'name="middle_name"' => 'Middle Name input',
    'id="student_email"' => 'Student Email input',
    'id="house_name"'    => 'House Name input',
    'id="street"'        => 'Street input',
    'id="city"'          => 'City input',
    'id="district"'      => 'District input',
    'id="state"'         => 'State input',
    'id="pin_code"'      => 'PIN Code input',
    'Student Address'    => 'Student Address heading',
];
check_url('http://localhost/schoolnew/students/add', $add_patterns, $cookieFile);

echo "\n=== CHECK 2: Edit Student (students/edit/46) ===\n";
$edit_patterns = [
    'name="middle_name"' => 'Middle Name input',
    'id="student_email"' => 'Student Email input',
    'id="house_name"'    => 'House Name input',
    'id="pin_code"'      => 'PIN Code input',
    'Student Address'    => 'Student Address section',
];
check_url('http://localhost/schoolnew/students/edit/46', $edit_patterns, $cookieFile);

echo "\n=== CHECK 3: Student Profile (students/profile/46) ===\n";
$profile_patterns = [
    'Personal Info'      => 'Personal Info tab',
    'Student Email'      => 'Student Email display block',
];
check_url('http://localhost/schoolnew/students/profile/46', $profile_patterns, $cookieFile);

echo "\n=== CHECK 4: Step 1 Wizard Submission Test (students/wizard_step1) ===\n";
// Fetch CSRF token from add page
$ch = curl_init('http://localhost/schoolnew/students/add');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$addHtml = curl_exec($ch);
curl_close($ch);

preg_match('/CSRF_HASH\s*=\s*"([^"]+)"/', $addHtml, $cm);
if (empty($cm)) preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $addHtml, $cm);
$csrfHash = $cm[1] ?? '';

$step1Data = [
    'csrf_test_name'   => $csrfHash,
    'admission_number' => 'TEST_WIZ_' . rand(100, 999),
    'first_name'       => 'Anandhu',
    'middle_name'      => 's',
    'last_name'        => 'Uthaman',
    'gender'           => 'Male',
    'date_of_birth'    => '2012-05-15',
    'blood_group'      => 'B+',
    'student_phone'    => '98470 11223',
    'student_phone_country' => 'IN',
    'student_email'    => 'anandhu@example.com',
    'house_name'       => 'Rose Villa',
    'street'           => 'MG Road',
    'city'             => 'Kochi',
    'district'         => 'Ernakulam',
    'state'            => 'Kerala',
    'pin_code'         => '682001'
];

$ch = curl_init('http://localhost/schoolnew/students/wizard_step1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($step1Data));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resJson = curl_exec($ch);
curl_close($ch);

echo "  Response: $resJson\n";
$decoded = json_decode($resJson, true);
if (!empty($decoded['success']) && $decoded['success'] === true) {
    echo "  [PASS] wizard_step1 accepted all fields and returned success!\n";
} else {
    echo "  [FAIL] wizard_step1 returned error!\n";
}

echo "\n=== CHECK 5: Student Overall Report (students/overall_report/46) ===\n";
$report_patterns = [
    'Student Report' => 'Report heading',
];
check_url('http://localhost/schoolnew/students/overall_report/46', $report_patterns, $cookieFile);

echo "\nAll HTTP Endpoint rendering & submission checks complete!\n";
