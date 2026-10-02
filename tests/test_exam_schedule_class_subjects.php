<?php
/**
 * Comprehensive verification for Exam Schedule Subject Dropdown filtering by Class
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFile = __DIR__ . '/test_cookie_exam_sched.txt';

function log_result($desc, $passed, $info = '') {
    echo ($passed ? "[PASS] " : "[FAIL] ") . $desc . ($info ? " - " . $info : "") . "\n";
    if (!$passed) {
        // don't exit immediately so all checks run
    }
}

// 1. Lint check files
exec('php -l "c:/xampp/htdocs/schoolnew/application/controllers/Examinations.php"', $out1, $ret1);
log_result("Lint Examinations.php", $ret1 === 0, implode(' ', $out1));

exec('php -l "c:/xampp/htdocs/schoolnew/application/views/pages/examinations/schedules.php"', $out2, $ret2);
log_result("Lint schedules.php", $ret2 === 0, implode(' ', $out2));

// 2. Static source verification
$schedView = file_get_contents('c:/xampp/htdocs/schoolnew/application/views/pages/examinations/schedules.php');
log_result("Modal subject select does NOT dump all subjects statically", strpos($schedView, 'id="modal-sched-subject"') !== false && strpos($schedView, 'foreach ($subjects as $sub)') === false || (strpos($schedView, 'id="modal-sched-subject"') > strpos($schedView, 'foreach ($subjects as $sub)')));
log_result("Modal class change calls onModalClassChange", strpos($schedView, 'onchange="onModalClassChange(this.value)"') !== false);
log_result("Modal year change calls onModalYearChange", strpos($schedView, 'onchange="onModalYearChange(this.value)"') !== false);
log_result("loadModalSubjects function defined", strpos($schedView, 'function loadModalSubjects(') !== false);
log_result("editScheduleModal calls loadModalSubjects", strpos($schedView, 'loadModalSubjects(item.class_id, item.subject_id, item.academic_year_id') !== false);

// 3. Login to app
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => 'admin@gmail.com', 'password' => '123456']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

// 4. Test AJAX endpoints for multiple classes
// Class 1 (LKG)
$ch = curl_init("{$baseUrl}/examinations/ajax_get_subjects/1?academic_year_id=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$jsonLkg = curl_exec($ch);
curl_close($ch);
$lkgSubjects = json_decode($jsonLkg, true);

$lkgIds = array_column($lkgSubjects, 'subject_id');
$lkgNames = array_column($lkgSubjects, 'subject_name');
log_result("LKG AJAX subjects returned count: " . count($lkgSubjects), count($lkgSubjects) > 0);
log_result("LKG subjects has NO duplicate IDs", count($lkgIds) === count(array_unique($lkgIds)));
log_result("LKG subjects has NO duplicate names", count($lkgNames) === count(array_unique($lkgNames)));

// Class 18 (Grade 5)
$ch = curl_init("{$baseUrl}/examinations/ajax_get_subjects/18?academic_year_id=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$jsonG5 = curl_exec($ch);
curl_close($ch);
$g5Subjects = json_decode($jsonG5, true);

$g5Ids = array_column($g5Subjects, 'subject_id');
$g5Names = array_column($g5Subjects, 'subject_name');
log_result("Grade 5 AJAX subjects returned count: " . count($g5Subjects), count($g5Subjects) > 0);
log_result("Grade 5 subjects has NO duplicate IDs", count($g5Ids) === count(array_unique($g5Ids)));
log_result("Grade 5 subjects has NO duplicate names", count($g5Names) === count(array_unique($g5Names)));

// Ensure disjoint sets: LKG subject IDs should not overlap Grade 5 subject IDs
$overlap = array_intersect($lkgIds, $g5Ids);
log_result("LKG and Grade 5 subject IDs do not overlap", empty($overlap), "Overlap count: " . count($overlap));

// 5. Test page render of /examinations/schedules
$ch = curl_init("{$baseUrl}/examinations/schedules");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$html = curl_exec($ch);
curl_close($ch);

log_result("Schedules page rendered HTTP 200", !empty($html));
// Check that inside modal-sched-subject there is ONLY -- Select Subject -- initially
preg_match('/<select[^>]*id="modal-sched-subject"[^>]*>(.*?)<\/select>/s', $html, $modalSubMatch);
$modalSubHtml = $modalSubMatch[1] ?? '';
$optionCount = substr_count($modalSubHtml, '<option');
log_result("Modal subject select only has 1 initial placeholder option (not all 115)", $optionCount === 1, "Found option count: {$optionCount}");

// 6. Test backend validation: POST mismatched subject to class
// Attempt to schedule Grade 5 (class_id=18) with an LKG subject (Drawing id=13)
$ch = curl_init("{$baseUrl}/examinations/schedules");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'exam_id' => 1,
    'academic_year_id' => 1,
    'class_id' => 18, // Grade 5
    'division_id' => '',
    'subject_id' => 13, // Drawing for LKG, NOT in Grade 5
    'exam_date' => date('Y-m-d', strtotime('+10 days')),
    'start_time' => '10:00',
    'end_time' => '12:00',
    'max_marks' => 100,
    'passing_marks' => 35
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$postResp = curl_exec($ch);
curl_close($ch);

$rejected = (strpos($postResp, 'The selected subject is not allocated to the selected class') !== false);
log_result("Backend rejects mismatched class-subject schedule submission", $rejected);

// Clean up cookie file
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
echo "\nTesting complete.\n";
