<?php
/**
 * Test Global Active Academic Year Selection Everywhere
 *
 * Verifies that the active academic year (from tbl_academic_years where is_active=1)
 * is dynamically selected by default across every audited module, modal, and filter.
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_global_year_');

function makeRequest($url, $postData = null) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

function getCsrf($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$passed = 0;
$total = 0;

function assertCondition($desc, $cond) {
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
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$loginPage = makeRequest($baseUrl . '/auth/login');
$csrf = getCsrf($loginPage['body']);
$loginResp = makeRequest($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

assertCondition("Admin login successful", strpos($loginResp['body'], 'Dashboard') !== false || strpos($loginResp['body'], 'admin') !== false || $loginResp['code'] === 200);

// Connect to DB directly to verify active year in database
$mysqli = new mysqli('127.0.0.1', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error);
}
$res = $mysqli->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE is_active = 1 AND status = 1 AND is_deleted = 'n' LIMIT 1");
$activeRow = $res->fetch_assoc();
$activeYearId = (int)$activeRow['academic_year_id'];
$activeYearName = $activeRow['year_name'];
echo "  [INFO] Active Academic Year in DB: ID={$activeYearId} ({$activeYearName})\n";

echo "\n=======================================================\n";
echo "2. Global Header Window Context Verification\n";
echo "=======================================================\n";

$dashboardResp = makeRequest($baseUrl . '/dashboard');
assertCondition("Dashboard loads HTTP 200", $dashboardResp['code'] === 200);
assertCondition("window.ACTIVE_ACADEMIC_YEAR_ID matches DB active year ID ({$activeYearId})",
    preg_match('/window\.ACTIVE_ACADEMIC_YEAR_ID\s*=\s*' . $activeYearId . ';/', $dashboardResp['body']) === 1);
assertCondition("window.ACTIVE_ACADEMIC_YEAR includes year_name ({$activeYearName})",
    strpos($dashboardResp['body'], '"year_name":"' . $activeYearName . '"') !== false);

echo "\n=======================================================\n";
echo "3. Subject Teachers Modal & Filter\n";
echo "=======================================================\n";

$stResp = makeRequest($baseUrl . '/academics/subject_teachers');
assertCondition("Subject Teachers loads HTTP 200", $stResp['code'] === 200);
assertCondition("Subject Teachers filter selects active year by default",
    preg_match('/<select id="filter_academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $stResp['body']) === 1);
assertCondition("Subject Teachers modal_st_year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal_st_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $stResp['body']) === 1);
assertCondition("Subject Teachers modal does NOT default to 2027-2028 when 2026-2027 is active",
    preg_match('/<select name="academic_year_id" id="modal_st_year"[^>]*>.*?<option value="3"\s+selected>/s', $stResp['body']) === 0);

echo "\n=======================================================\n";
echo "4. Fee Structures Modal & Filter\n";
echo "=======================================================\n";

$fsResp = makeRequest($baseUrl . '/fees/structures');
assertCondition("Fee Structures loads HTTP 200", $fsResp['code'] === 200);
assertCondition("Fee Structures modal-struct-year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal-struct-year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $fsResp['body']) === 1);
assertCondition("Fee Structures filter has (Active) indicator",
    strpos($fsResp['body'], $activeYearName . ' (Active)') !== false);

echo "\n=======================================================\n";
echo "5. Fee Bulk Assignments Modal\n";
echo "=======================================================\n";

$faResp = makeRequest($baseUrl . '/fees/assignments');
assertCondition("Fee Assignments loads HTTP 200", $faResp['code'] === 200);
assertCondition("Fee Assignments bulk-academic-year-id has active year selected by default",
    preg_match('/<select name="academic_year_id" id="bulk-academic-year-id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $faResp['body']) === 1);

echo "\n=======================================================\n";
echo "6. Examinations Exams & Schedules Modals & Filters\n";
echo "=======================================================\n";

$exResp = makeRequest($baseUrl . '/examinations/exams');
assertCondition("Exams page loads HTTP 200", $exResp['code'] === 200);
assertCondition("Exams modal-academic-year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal-academic-year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $exResp['body']) === 1);
assertCondition("Exams filter selects active year by default",
    preg_match('/<select name="academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $exResp['body']) === 1);

$schedResp = makeRequest($baseUrl . '/examinations/schedules');
assertCondition("Exam Schedules page loads HTTP 200", $schedResp['code'] === 200);
assertCondition("Exam Schedules modal-sched-year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal-sched-year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $schedResp['body']) === 1);

echo "\n=======================================================\n";
echo "7. Classes & Divisions Modal\n";
echo "=======================================================\n";

$clsResp = makeRequest($baseUrl . '/academics/classes');
assertCondition("Classes & Divisions loads HTTP 200", $clsResp['code'] === 200);
assertCondition("modal_class_year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal_class_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $clsResp['body']) === 1);

echo "\n=======================================================\n";
echo "8. Academic Calendar & Timetable\n";
echo "=======================================================\n";

$calResp = makeRequest($baseUrl . '/academics/calendar');
assertCondition("Calendar loads HTTP 200", $calResp['code'] === 200);
assertCondition("Calendar filter selects active year by default",
    preg_match('/<select id="filter_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $calResp['body']) === 1);
assertCondition("Calendar modal_event_year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal_event_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $calResp['body']) === 1);

$ttResp = makeRequest($baseUrl . '/academics/timetable');
assertCondition("Timetable loads HTTP 200", $ttResp['code'] === 200);
assertCondition("Timetable filter selects active year by default",
    preg_match('/<select id="tt_filter_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $ttResp['body']) === 1);
assertCondition("Timetable modal_tt_year has active year selected by default",
    preg_match('/<select name="academic_year_id" id="modal_tt_year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $ttResp['body']) === 1);

echo "\n=======================================================\n";
echo "9. All Students & Search Pages\n";
echo "=======================================================\n";

$studResp = makeRequest($baseUrl . '/students/all_students');
assertCondition("All Students loads HTTP 200", $studResp['code'] === 200);
assertCondition("All Students dropdown selects active year by default",
    preg_match('/<select id="all-students-academic-year"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $studResp['body']) === 1);

// Count how many times '(Active)' appears in all-students-academic-year select
preg_match('/<select id="all-students-academic-year"[^>]*>(.*?)<\/select>/s', $studResp['body'], $selMatches);
$selContent = $selMatches[1] ?? '';
$activeCount = substr_count($selContent, '(Active)');
assertCondition("Only EXACTLY 1 year is marked (Active) in All Students (found: {$activeCount})", $activeCount === 1);

$searchResp = makeRequest($baseUrl . '/students/search');
assertCondition("Student Search loads HTTP 200", $searchResp['code'] === 200);
assertCondition("Student Search dropdown auto-selects active year",
    preg_match('/<select name="academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $searchResp['body']) === 1);

echo "\n=======================================================\n";
echo "10. Staff Workload Modal\n";
echo "=======================================================\n";

$workloadResp = makeRequest($baseUrl . '/staff/workload');
assertCondition("Staff Workload loads HTTP 200", $workloadResp['code'] === 200);
assertCondition("Staff Workload modal selects active year by default",
    preg_match('/<select name="academic_year_id" id="workload_academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $workloadResp['body']) === 1);

echo "\n=======================================================\n";
echo "11. Attendance Views Academic Year Options\n";
echo "=======================================================\n";

$attResp = makeRequest($baseUrl . '/attendance');
assertCondition("Attendance Dashboard loads HTTP 200", $attResp['code'] === 200);
assertCondition("Attendance Dashboard filter selects active year by default",
    preg_match('/<select name="academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $attResp['body']) === 1);

$markAttResp = makeRequest($baseUrl . '/attendance/mark_attendance');
assertCondition("Mark Attendance loads HTTP 200", $markAttResp['code'] === 200);
assertCondition("Mark Attendance filter selects active year by default",
    preg_match('/<select name="academic_year_id"[^>]*>.*?<option value="' . $activeYearId . '"\s+selected>.*?' . preg_quote($activeYearName, '/') . '.*?\(Active\)/s', $markAttResp['body']) === 1);

echo "\n=======================================================\n";
echo "12. Dynamic Active Year Switch Verification (Simulate 2025-2026 Active)\n";
echo "=======================================================\n";

// Switch DB active year to ID 2 (2025-2026)
$mysqli->query("UPDATE tbl_academic_years SET is_active = 0");
$mysqli->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = 2");

$switchResp = makeRequest($baseUrl . '/academics/subject_teachers');
assertCondition("After switching DB active year to ID 2 (2025-2026), subject_teachers modal selects ID 2",
    preg_match('/<select name="academic_year_id" id="modal_st_year"[^>]*>.*?<option value="2"\s+selected>.*?2025-2026.*?\(Active\)/s', $switchResp['body']) === 1);

$switchFsResp = makeRequest($baseUrl . '/fees/structures');
assertCondition("After switching DB active year to ID 2, fee structures modal selects ID 2",
    preg_match('/<select name="academic_year_id" id="modal-struct-year"[^>]*>.*?<option value="2"\s+selected>.*?2025-2026.*?\(Active\)/s', $switchFsResp['body']) === 1);

// Restore DB active year back to ID 1 (2026-2027)
$mysqli->query("UPDATE tbl_academic_years SET is_active = 0");
$mysqli->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = 1");

$restoredResp = makeRequest($baseUrl . '/academics/subject_teachers');
assertCondition("After restoring active year to ID 1 (2026-2027), subject_teachers modal selects ID 1",
    preg_match('/<select name="academic_year_id" id="modal_st_year"[^>]*>.*?<option value="1"\s+selected>.*?2026-2027.*?\(Active\)/s', $restoredResp['body']) === 1);

echo "\n=======================================================\n";
echo "SUMMARY: {$passed} / {$total} tests passed.\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "\n>>> ALL GLOBAL ACTIVE ACADEMIC YEAR TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
