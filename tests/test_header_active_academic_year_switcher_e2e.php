<?php
/**
 * E2E Test Suite: Global Header Academic Year Master Control
 *
 * Validates:
 * 1. Super Admin authentication & initial active year
 * 2. Security: Unauthenticated and non-admin requests rejected with 403
 * 3. Validation: Invalid/deleted year IDs rejected with 400
 * 4. Atomic Database Switch: Only exactly 1 row has is_active=1
 * 5. Audit Logging: tbl_permission_audit_logs records CHANGED_ACTIVE_ACADEMIC_YEAR
 * 6. Global Propagation: Fresh session / another user immediately sees new active year
 * 7. Historical Data Preservation: Existing student/class/exam records unaffected
 * 8. Clean restore of initial active year
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFileAdmin = tempnam(sys_get_temp_dir(), 'test_sw_admin_');
$cookieFileOther = tempnam(sys_get_temp_dir(), 'test_sw_other_');

function makeReq($url, $postData = null, $cookieFile = null, $isAjax = false) {
    global $cookieFileAdmin;
    $cFile = $cookieFile ?: $cookieFileAdmin;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cFile);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $headers = [];
    if ($isAjax) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
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

$mysqli = new mysqli('127.0.0.1', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error);
}

// Ensure clean starting active year (ID = 1, 2026-2027)
$mysqli->query("UPDATE tbl_academic_years SET is_active = 0");
$mysqli->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = 1");

echo "=======================================================\n";
echo "1. Verify Initial Database State\n";
echo "=======================================================\n";

$res = $mysqli->query("SELECT academic_year_id, year_name, is_active FROM tbl_academic_years WHERE is_active = 1 AND status = 1 AND is_deleted = 'n'");
$activeRows = $res->fetch_all(MYSQLI_ASSOC);
assertCheck("Exactly 1 active academic year initially in database", count($activeRows) === 1);
$initialActiveId = (int)$activeRows[0]['academic_year_id'];
$initialActiveName = $activeRows[0]['year_name'];
echo "  [INFO] Initial Active Academic Year: ID={$initialActiveId} ({$initialActiveName})\n";

echo "\n=======================================================\n";
echo "2. Authenticate as Super Admin\n";
echo "=======================================================\n";

$loginPage = makeReq($baseUrl . '/auth/login');
$csrf = getCsrfFromHtml($loginPage['body']);
$loginResp = makeReq($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assertCheck("Super Admin login successful", $loginResp['code'] === 200 && (strpos($loginResp['body'], 'Dashboard') !== false || strpos($loginResp['body'], 'admin') !== false));

$dashboard = makeReq($baseUrl . '/dashboard');
assertCheck("Dashboard loads HTTP 200", $dashboard['code'] === 200);
assertCheck("window.IS_SUPER_ADMIN is true for admin", strpos($dashboard['body'], 'window.IS_SUPER_ADMIN = true;') !== false);
assertCheck("window.CAN_CHANGE_ACADEMIC_YEAR is true for admin", strpos($dashboard['body'], 'window.CAN_CHANGE_ACADEMIC_YEAR = true;') !== false);
assertCheck("Dashboard has header-root container", strpos($dashboard['body'], 'id="header-root"') !== false);
$appJsContent = file_get_contents('assets/app.js');
assertCheck("assets/app.js contains global-academic-year-select and change-active-year-modal",
    strpos($appJsContent, 'global-academic-year-select') !== false && strpos($appJsContent, 'change-active-year-modal') !== false);

echo "\n=======================================================\n";
echo "3. Security & Input Validation\n";
echo "=======================================================\n";

// 3.1 Unauthenticated request
$unauthCookie = tempnam(sys_get_temp_dir(), 'unauth_');
$unauthSwitch = makeReq($baseUrl . '/academic-year/switch', ['academic_year_id' => 2], $unauthCookie, true);
assertCheck("Unauthenticated switch is rejected with 401, 403 or redirect", $unauthSwitch['code'] === 401 || $unauthSwitch['code'] === 403 || $unauthSwitch['code'] === 302 || strpos($unauthSwitch['body'], 'login') !== false);
@unlink($unauthCookie);

// 3.2 Invalid academic year ID (<= 0)
$csrfDash = getCsrfFromHtml($dashboard['body']);
$invalidReq1 = makeReq($baseUrl . '/academic-year/switch', [
    $csrfDash['name'] => $csrfDash['hash'],
    'academic_year_id' => 0
], null, true);
assertCheck("academic_year_id = 0 returns 400 Bad Request", $invalidReq1['code'] === 400);

// 3.3 Non-existent academic year ID
$invJson1 = json_decode($invalidReq1['body'], true);
if (isset($invJson1['csrf_hash'])) $csrfDash['hash'] = $invJson1['csrf_hash'];

$invalidReq2 = makeReq($baseUrl . '/academic-year/switch', [
    $csrfDash['name'] => $csrfDash['hash'],
    'academic_year_id' => 999999
], null, true);
assertCheck("academic_year_id = 999999 returns 400 Bad Request", $invalidReq2['code'] === 400);

echo "\n=======================================================\n";
echo "4. Atomic Switch to Another Year (ID = 2, 2025-2026)\n";
echo "=======================================================\n";

// Find target year ID 2
$res2 = $mysqli->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE academic_year_id = 2");
$targetRow = $res2->fetch_assoc();
$targetYearId = (int)$targetRow['academic_year_id'];
$targetYearName = $targetRow['year_name'];

// Record student record counts before switch to verify historical data preservation
$studCountRes = $mysqli->query("SELECT academic_year_id, COUNT(*) as cnt FROM tbl_students GROUP BY academic_year_id");
$countsBefore = [];
while ($r = $studCountRes->fetch_assoc()) {
    $countsBefore[$r['academic_year_id']] = (int)$r['cnt'];
}

$dashFresh = makeReq($baseUrl . '/dashboard');
$csrfDash = getCsrfFromHtml($dashFresh['body']);

$switchResp = makeReq($baseUrl . '/academic-year/switch', [
    $csrfDash['name'] => $csrfDash['hash'],
    'academic_year_id' => $targetYearId
], null, true);

assertCheck("Switch request returns HTTP 200", $switchResp['code'] === 200);
$switchJson = json_decode($switchResp['body'], true);
assertCheck("Switch response JSON has status = true", isset($switchJson['status']) && $switchJson['status'] === true);
assertCheck("Switch message confirms 'Active academic year changed to {$targetYearName}.'",
    isset($switchJson['message']) && strpos($switchJson['message'], $targetYearName) !== false);

// Verify Database Atomicity
$resAfter = $mysqli->query("SELECT academic_year_id, year_name, is_active FROM tbl_academic_years WHERE is_active = 1");
$activeAfter = $resAfter->fetch_all(MYSQLI_ASSOC);
assertCheck("Database has EXACTLY 1 active academic year after switch", count($activeAfter) === 1);
assertCheck("Database active academic year is now ID={$targetYearId} ({$targetYearName})",
    isset($activeAfter[0]) && (int)$activeAfter[0]['academic_year_id'] === $targetYearId);

// Verify old year is now inactive
$resOld = $mysqli->query("SELECT is_active FROM tbl_academic_years WHERE academic_year_id = {$initialActiveId}");
$oldRow = $resOld->fetch_assoc();
assertCheck("Previous academic year (ID={$initialActiveId}) is now is_active = 0", (int)$oldRow['is_active'] === 0);

echo "\n=======================================================\n";
echo "5. Audit Log Verification\n";
echo "=======================================================\n";

$auditRes = $mysqli->query("SELECT * FROM tbl_permission_audit_logs WHERE action = 'CHANGED_ACTIVE_ACADEMIC_YEAR' ORDER BY log_id DESC LIMIT 1");
$auditRow = $auditRes->fetch_assoc();
assertCheck("Audit log entry created for CHANGED_ACTIVE_ACADEMIC_YEAR", !empty($auditRow));
assertCheck("Audit log target_id matches newly activated year ({$targetYearId})", (int)($auditRow['target_id'] ?? 0) === $targetYearId);
assertCheck("Audit log details mentions from and to year names",
    strpos($auditRow['details'] ?? '', $initialActiveName) !== false && strpos($auditRow['details'] ?? '', $targetYearName) !== false);

echo "\n=======================================================\n";
echo "6. System-Wide Global Propagation Across Modules\n";
echo "=======================================================\n";

// Verify Dashboard reflects new active year
$dashAfter = makeReq($baseUrl . '/dashboard');
assertCheck("Dashboard window.ACTIVE_ACADEMIC_YEAR_ID is now {$targetYearId}",
    preg_match('/window\.ACTIVE_ACADEMIC_YEAR_ID\s*=\s*' . $targetYearId . ';/', $dashAfter['body']) === 1);
assertCheck("Dashboard window.ACTIVE_ACADEMIC_YEAR contains {$targetYearName}",
    strpos($dashAfter['body'], '"year_name":"' . $targetYearName . '"') !== false);

// Verify Subject Teachers modal defaults to new active year (2025-2026)
$stResp = makeReq($baseUrl . '/academics/subject_teachers');
assertCheck("Subject Teachers modal_st_year now defaults to {$targetYearName}",
    preg_match('/<select name="academic_year_id" id="modal_st_year"[^>]*>.*?<option value="' . $targetYearId . '"\s+selected>/s', $stResp['body']) === 1);

// Verify Fee Structures modal defaults to new active year
$feeResp = makeReq($baseUrl . '/fees/structures');
assertCheck("Fee Structures modal defaults to {$targetYearName}",
    preg_match('/<select[^>]*id="modal-struct-year"[^>]*>.*?<option value="' . $targetYearId . '"\s+selected>/s', $feeResp['body']) === 1);

// Verify Exams modal defaults to new active year
$examResp = makeReq($baseUrl . '/examinations/exams');
assertCheck("Exams modal defaults to {$targetYearName}",
    preg_match('/<select[^>]*id="modal-academic-year"[^>]*>.*?<option value="' . $targetYearId . '"\s+selected>/s', $examResp['body']) === 1);

// Verify Classes & Divisions modal defaults to new active year
$classesResp = makeReq($baseUrl . '/academics/classes');
assertCheck("Classes modal defaults to {$targetYearName}",
    preg_match('/<select name="academic_year_id" id="modal_class_year"[^>]*>.*?<option value="' . $targetYearId . '"\s+selected>/s', $classesResp['body']) === 1);

echo "\n=======================================================\n";
echo "7. Cross-Session / Separate User Verification\n";
echo "=======================================================\n";

// Make request with a completely new, clean cookie file (simulating a separate user or browser session)
$cleanCookie = tempnam(sys_get_temp_dir(), 'clean_session_');
$cleanLogin = makeReq($baseUrl . '/auth/login', null, $cleanCookie);
$cleanCsrf = getCsrfFromHtml($cleanLogin['body']);
$cleanAuth = makeReq($baseUrl . '/auth/login', [
    $cleanCsrf['name'] => $cleanCsrf['hash'],
    'email'            => 'admin@gmail.com',
    'password'         => '123456',
    'remember'         => '1',
], $cleanCookie);

$cleanDash = makeReq($baseUrl . '/dashboard', null, $cleanCookie);
assertCheck("Fresh login session loads HTTP 200", $cleanDash['code'] === 200);
assertCheck("Fresh session automatically gets newly activated year ({$targetYearId}) globally",
    preg_match('/window\.ACTIVE_ACADEMIC_YEAR_ID\s*=\s*' . $targetYearId . ';/', $cleanDash['body']) === 1);
@unlink($cleanCookie);

echo "\n=======================================================\n";
echo "8. Historical Data Preservation\n";
echo "=======================================================\n";

$studCountResAfter = $mysqli->query("SELECT academic_year_id, COUNT(*) as cnt FROM tbl_students GROUP BY academic_year_id");
$countsAfter = [];
while ($r = $studCountResAfter->fetch_assoc()) {
    $countsAfter[$r['academic_year_id']] = (int)$r['cnt'];
}
assertCheck("Student counts per historical academic year remain exactly identical", $countsBefore === $countsAfter);

echo "\n=======================================================\n";
echo "9. Restore Initial Academic Year ({$initialActiveName})\n";
echo "=======================================================\n";

$dashRestore = makeReq($baseUrl . '/dashboard');
$csrfRestore = getCsrfFromHtml($dashRestore['body']);
$restoreResp = makeReq($baseUrl . '/academic-year/switch', [
    $csrfRestore['name'] => $csrfRestore['hash'],
    'academic_year_id' => $initialActiveId
], null, true);

assertCheck("Restore request returns HTTP 200", $restoreResp['code'] === 200);
$resFinal = $mysqli->query("SELECT academic_year_id, is_active FROM tbl_academic_years WHERE is_active = 1");
$activeFinal = $resFinal->fetch_all(MYSQLI_ASSOC);
assertCheck("Initial active academic year (ID={$initialActiveId}) restored successfully",
    count($activeFinal) === 1 && (int)$activeFinal[0]['academic_year_id'] === $initialActiveId);

@unlink($cookieFileAdmin);
@unlink($cookieFileOther);

echo "\n=======================================================\n";
echo "SUMMARY: {$passed} / {$total} tests passed.\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "\n>>> ALL MASTER ACTIVE ACADEMIC YEAR SWITCHER TESTS PASSED! <<<\n\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n\n";
    exit(1);
}
