<?php
/**
 * Comprehensive E2E Test Suite: Global Header Search
 *
 * Tests:
 * 1. Authentication & Security (401 for unauthorized, RBAC checks)
 * 2. Header markup & JS integration in assets/app.js
 * 3. Search validation (min 2 chars, max 100 chars, special chars sanitization)
 * 4. Admission number search (e.g. EDU2026132 -> Student Profile)
 * 5. Student name search (e.g. Anandhu, Arjun -> partial match)
 * 6. Student roll number search (e.g. 11)
 * 7. Phone number search (e.g. +919876543210, 9876543210)
 * 8. Staff / Teacher employee code search (e.g. TCH2026001)
 * 9. Staff name search
 * 10. Class & Division search (e.g. Grade 10)
 * 11. Subject search (e.g. Mathematics)
 * 12. No-results handling & empty states
 * 13. Full results page (/search?q=...) with tabs & cards
 * 14. Academic year awareness & active year prioritization
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFileAdmin = tempnam(sys_get_temp_dir(), 'test_search_admin_');
$cookieFileUnauth = tempnam(sys_get_temp_dir(), 'test_search_unauth_');

function makeReq($url, $postData = null, $cookieFile = null, $isAjax = false) {
    global $cookieFileAdmin;
    $cFile = $cookieFile ?: $cookieFileAdmin;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cFile);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $headers = [];
    if ($isAjax) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Accept: application/json';
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

function getCsrfFromHtml($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    if (preg_match('/window\.CSRF_TOKEN_NAME\s*=\s*"([^"]+)";.*?window\.CSRF_HASH\s*=\s*"([^"]+)";/s', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$passed = 0;
$total = 0;

function assertCheck($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
    }
}

echo "=======================================================\n";
echo "1. Security: Unauthenticated Access Guard\n";
echo "=======================================================\n";

$unauthAjax = makeReq($baseUrl . '/search/global?q=EDU2026132', null, $cookieFileUnauth, true);
assertCheck("Unauthenticated AJAX request to /search/global returns 401 Unauthorized", $unauthAjax['code'] === 401);

$unauthWeb = makeReq($baseUrl . '/search?q=EDU2026132', null, $cookieFileUnauth, false);
assertCheck("Unauthenticated web request to /search redirects (302/307) to login", in_array($unauthWeb['code'], [301, 302, 303, 307], true));

echo "\n=======================================================\n";
echo "2. Authenticate as Super Admin\n";
echo "=======================================================\n";

$loginPage = makeReq($baseUrl . '/auth/login', null, $cookieFileAdmin, false);
$csrf = getCsrfFromHtml($loginPage['body']);
$loginResp = makeReq($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
], $cookieFileAdmin, false);

assertCheck("Super Admin login successfully returns HTTP redirect (302/307) or 200", in_array($loginResp['code'], [200, 301, 302, 303, 307], true));

$dash = makeReq($baseUrl . '/dashboard', null, $cookieFileAdmin, false);
assertCheck("Dashboard loads with HTTP 200", $dash['code'] === 200);

echo "\n=======================================================\n";
echo "3. Verify Header Markup & JavaScript Assets\n";
echo "=======================================================\n";

$appJs = file_get_contents('assets/app.js');
assertCheck("assets/app.js defines id='global-search-container'", strpos($appJs, 'id="global-search-container"') !== false);
assertCheck("assets/app.js defines id='global-search-input'", strpos($appJs, 'id="global-search-input"') !== false);
assertCheck("assets/app.js defines id='global-search-dropdown'", strpos($appJs, 'id="global-search-dropdown"') !== false);
assertCheck("assets/app.js defines id='global-search-clear-btn'", strpos($appJs, 'id="global-search-clear-btn"') !== false);
assertCheck("assets/app.js has initGlobalHeaderSearch() function", strpos($appJs, 'function initGlobalHeaderSearch()') !== false);
assertCheck("assets/app.js has PAGE_URLS mapping for search", strpos($appJs, '"search": "search"') !== false);

echo "\n=======================================================\n";
echo "4. Input Validation (Short queries, long queries, special chars)\n";
echo "=======================================================\n";

// 1-character query
$resShort = makeReq($baseUrl . '/search/global?q=A', null, $cookieFileAdmin, true);
assertCheck("1-character query returns HTTP 200 with status=false", $resShort['code'] === 200);
$jsonShort = json_decode($resShort['body'], true);
assertCheck("Short query returns message requiring at least 2 characters", isset($jsonShort['status']) && $jsonShort['status'] === false && strpos($jsonShort['message'], 'at least 2') !== false);

// Empty query
$resEmpty = makeReq($baseUrl . '/search/global?q=', null, $cookieFileAdmin, true);
$jsonEmpty = json_decode($resEmpty['body'], true);
assertCheck("Empty query returns status=false", isset($jsonEmpty['status']) && $jsonEmpty['status'] === false);

// SQL injection & special characters test
$resSql = makeReq($baseUrl . '/search/global?q=' . urlencode("' OR '1'='1"), null, $cookieFileAdmin, true);
assertCheck("SQL injection string handled safely with HTTP 200", $resSql['code'] === 200);
$jsonSql = json_decode($resSql['body'], true);
assertCheck("SQL injection string returns valid JSON response without DB error", isset($jsonSql['status']) && $jsonSql['status'] === true);

// Very long query (120 chars)
$longQ = str_repeat("a", 120);
$resLong = makeReq($baseUrl . '/search/global?q=' . urlencode($longQ), null, $cookieFileAdmin, true);
assertCheck("Excessively long query handled cleanly with HTTP 200", $resLong['code'] === 200);

echo "\n=======================================================\n";
echo "5. Search by Exact Admission Number: EDU2026132\n";
echo "=======================================================\n";

$resAdm = makeReq($baseUrl . '/search/global?q=EDU2026132', null, $cookieFileAdmin, true);
assertCheck("AJAX query for EDU2026132 returns HTTP 200", $resAdm['code'] === 200);
$jsonAdm = json_decode($resAdm['body'], true);
assertCheck("Response status is true", isset($jsonAdm['status']) && $jsonAdm['status'] === true);
assertCheck("Total results >= 1", isset($jsonAdm['total_results']) && $jsonAdm['total_results'] >= 1);
$stItems = $jsonAdm['categories']['students']['items'] ?? [];
$foundStudent = false;
$foundStudentUrl = '';
foreach ($stItems as $item) {
    if ($item['admission_number'] === 'EDU2026132') {
        $foundStudent = true;
        $foundStudentUrl = $item['url'];
        break;
    }
}
assertCheck("Student with admission number EDU2026132 found in students category", $foundStudent);
assertCheck("Student URL points to students/profile/{student_id}", strpos($foundStudentUrl, 'students/profile/') !== false);

echo "\n=======================================================\n";
echo "6. Search by Student Name: Anandhu\n";
echo "=======================================================\n";

$resName = makeReq($baseUrl . '/search/global?q=Anandhu', null, $cookieFileAdmin, true);
$jsonName = json_decode($resName['body'], true);
assertCheck("Query 'Anandhu' returns results", isset($jsonName['total_results']) && $jsonName['total_results'] >= 1);
$foundAnandhu = false;
foreach ($jsonName['categories']['students']['items'] ?? [] as $item) {
    if (stripos($item['title'], 'Anandhu') !== false) {
        $foundAnandhu = true;
        break;
    }
}
assertCheck("Found student with name 'Anandhu S Uthaman'", $foundAnandhu);

echo "\n=======================================================\n";
echo "7. Search by Roll Number: 11\n";
echo "=======================================================\n";

$resRoll = makeReq($baseUrl . '/search/global?q=11', null, $cookieFileAdmin, true);
$jsonRoll = json_decode($resRoll['body'], true);
assertCheck("Query '11' returns results", isset($jsonRoll['total_results']) && $jsonRoll['total_results'] >= 1);
$firstStudent = $jsonRoll['categories']['students']['items'][0] ?? null;
assertCheck("Top student result has roll_number = '11' due to prioritized ranking", $firstStudent && $firstStudent['roll_number'] === '11');

echo "\n=======================================================\n";
echo "8. Search by Phone Number: +919876543210 & raw digits\n";
echo "=======================================================\n";

$resPhone1 = makeReq($baseUrl . '/search/global?q=' . urlencode('+919876543210'), null, $cookieFileAdmin, true);
$jsonPhone1 = json_decode($resPhone1['body'], true);
assertCheck("Formatted phone '+919876543210' returns matching record", isset($jsonPhone1['total_results']) && $jsonPhone1['total_results'] >= 1);

$resPhone2 = makeReq($baseUrl . '/search/global?q=9876543210', null, $cookieFileAdmin, true);
$jsonPhone2 = json_decode($resPhone2['body'], true);
assertCheck("Unformatted phone '9876543210' returns matching record", isset($jsonPhone2['total_results']) && $jsonPhone2['total_results'] >= 1);

echo "\n=======================================================\n";
echo "9. Search Staff / Teachers by Employee Code: TCH2026001\n";
echo "=======================================================\n";

$resStaff = makeReq($baseUrl . '/search/global?q=TCH2026001', null, $cookieFileAdmin, true);
$jsonStaff = json_decode($resStaff['body'], true);
assertCheck("Query 'TCH2026001' returns matching staff", isset($jsonStaff['categories']['staff']['count']) && $jsonStaff['categories']['staff']['count'] >= 1);
$staffItems = $jsonStaff['categories']['staff']['items'] ?? [];
$foundStaff = false;
$foundStaffUrl = '';
foreach ($staffItems as $item) {
    if ($item['employee_code'] === 'TCH2026001') {
        $foundStaff = true;
        $foundStaffUrl = $item['url'];
        break;
    }
}
assertCheck("Staff member with code TCH2026001 found in results", $foundStaff);
assertCheck("Staff profile URL points to staff/profile/{staff_id}", strpos($foundStaffUrl, 'staff/profile/') !== false);

echo "\n=======================================================\n";
echo "10. Search Classes & Divisions: Grade 10\n";
echo "=======================================================\n";

$resClass = makeReq($baseUrl . '/search/global?q=' . urlencode('Grade 10'), null, $cookieFileAdmin, true);
$jsonClass = json_decode($resClass['body'], true);
assertCheck("Query 'Grade 10' returns matching class", isset($jsonClass['categories']['classes']['count']) && $jsonClass['categories']['classes']['count'] >= 1);
$classItems = $jsonClass['categories']['classes']['items'] ?? [];
$foundClass = false;
$foundClassUrl = '';
foreach ($classItems as $item) {
    if (stripos($item['title'], 'Grade 10') !== false) {
        $foundClass = true;
        $foundClassUrl = $item['url'];
        break;
    }
}
assertCheck("Class 'Grade 10' found", $foundClass);
assertCheck("Class destination URL points to academics/classes", strpos($foundClassUrl, 'academics/classes') !== false);

echo "\n=======================================================\n";
echo "11. Search Subjects: Mathematics\n";
echo "=======================================================\n";

$resSub = makeReq($baseUrl . '/search/global?q=Mathematics', null, $cookieFileAdmin, true);
$jsonSub = json_decode($resSub['body'], true);
assertCheck("Query 'Mathematics' returns matching subjects", isset($jsonSub['categories']['subjects']['count']) && $jsonSub['categories']['subjects']['count'] >= 1);
$subItems = $jsonSub['categories']['subjects']['items'] ?? [];
$foundSub = false;
$foundSubUrl = '';
foreach ($subItems as $item) {
    if (stripos($item['title'], 'Mathematics') !== false) {
        $foundSub = true;
        $foundSubUrl = $item['url'];
        break;
    }
}
assertCheck("Subject 'Mathematics' found", $foundSub);
assertCheck("Subject destination URL points to academics/subjects", strpos($foundSubUrl, 'academics/subjects') !== false);

echo "\n=======================================================\n";
echo "12. Non-existent Search Term: XYZNONEXISTENT9999\n";
echo "=======================================================\n";

$resNone = makeReq($baseUrl . '/search/global?q=XYZNONEXISTENT9999', null, $cookieFileAdmin, true);
$jsonNone = json_decode($resNone['body'], true);
assertCheck("Non-existent query returns status=true", isset($jsonNone['status']) && $jsonNone['status'] === true);
assertCheck("Total results is 0", isset($jsonNone['total_results']) && $jsonNone['total_results'] === 0);

echo "\n=======================================================\n";
echo "13. Dedicated Full Search Results Page: /search?q=Anandhu\n";
echo "=======================================================\n";

$fullPage = makeReq($baseUrl . '/search?q=Anandhu', null, $cookieFileAdmin, false);
assertCheck("Full results page /search?q=Anandhu returns HTTP 200", $fullPage['code'] === 200);
assertCheck("Page displays search query in heading", strpos($fullPage['body'], 'Anandhu') !== false);
assertCheck("Page contains category tabs (Students, Staff, Classes, Subjects)",
    strpos($fullPage['body'], 'Students') !== false &&
    strpos($fullPage['body'], 'Staff') !== false &&
    strpos($fullPage['body'], 'Classes') !== false &&
    strpos($fullPage['body'], 'Subjects') !== false);
assertCheck("Page contains student profile link", strpos($fullPage['body'], 'students/profile/') !== false);

// Test empty search on full results page
$fullEmpty = makeReq($baseUrl . '/search', null, $cookieFileAdmin, false);
assertCheck("Full results page with empty query returns HTTP 200", $fullEmpty['code'] === 200);
assertCheck("Page shows guidance to enter at least 2 characters", strpos($fullEmpty['body'], 'Enter at least 2 characters') !== false);

echo "\n=======================================================\n";
echo "14. Active Academic Year Prioritization\n";
echo "=======================================================\n";

$resYearCheck = makeReq($baseUrl . '/search/global?q=Anandhu', null, $cookieFileAdmin, true);
$jsonYearCheck = json_decode($resYearCheck['body'], true);
$stCheck = $jsonYearCheck['categories']['students']['items'][0] ?? null;
assertCheck("Student in active academic year has is_active_year=true", $stCheck && isset($stCheck['is_active_year']) && $stCheck['is_active_year'] === true);

echo "\n=======================================================\n";
echo "TEST SUMMARY: {$passed} / {$total} checks passed\n";
echo "=======================================================\n";

// Cleanup temp cookies
@unlink($cookieFileAdmin);
@unlink($cookieFileUnauth);

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
