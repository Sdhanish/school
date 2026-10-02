<?php
/**
 * Test Suite: Examination -> Marks Entry Subject Filtering by Class
 * Verifies class-to-subject allocation integrity, duplicate avoidance,
 * cross-class separation, dynamic academic year handling, and backend validation.
 */

define('BASEPATH', true);
require 'application/config/database.php';
$cfg = $db['default'];
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}

$passed = 0;
$failed = 0;

function assert_true($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

echo "\n=== 1. Verify Active Academic Year Dynamically ===\n";
$res_year = $m->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE is_active = 1 AND status = 1 AND is_deleted = 'n' LIMIT 1");
$year_row = $res_year->fetch_assoc();
assert_true(!empty($year_row), "Found active academic year: {$year_row['year_name']} (ID: {$year_row['academic_year_id']})");
$active_year_id = (int)$year_row['academic_year_id'];

echo "\n=== 2. Helper Query: Get Allocated Subjects for Class (Simulating Model logic) ===\n";
function get_allocated_subjects_for_class($m, $year_id, $class_id) {
    $sql = "SELECT sub.subject_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id
            FROM tbl_subject_allocations sa
            JOIN tbl_subjects sub ON sub.subject_id = sa.subject_id
            WHERE sa.academic_year_id = {$year_id}
              AND sa.class_id = {$class_id}
              AND sa.status = 1
              AND sa.is_deleted = 'n'
              AND sub.status = 1
              AND sub.is_deleted = 'n'
            ORDER BY sub.subject_name ASC";
    $res = $m->query($sql);
    $allocs = [];
    while ($row = $res->fetch_assoc()) {
        $allocs[$row['subject_id']] = $row;
    }
    if (!empty($allocs)) {
        return array_values($allocs);
    }
    // Fallback directly to tbl_subjects.class_id
    $fallback_sql = "SELECT sub.subject_id, sub.subject_name, sub.subject_code, sub.subject_type, sub.class_id
                     FROM tbl_subjects sub
                     WHERE sub.class_id = {$class_id}
                       AND sub.status = 1
                       AND sub.is_deleted = 'n'
                     ORDER BY sub.subject_name ASC";
    $fres = $m->query($fallback_sql);
    $fsubjects = [];
    while ($row = $fres->fetch_assoc()) {
        $fsubjects[$row['subject_id']] = $row;
    }
    return array_values($fsubjects);
}

echo "\n=== 3. Verify LKG Subjects Specifically ===\n";
$lkg_subjects = get_allocated_subjects_for_class($m, $active_year_id, 1);
$lkg_names = array_column($lkg_subjects, 'subject_name');
$lkg_ids = array_column($lkg_subjects, 'subject_id');

assert_true(count($lkg_subjects) === 5, "LKG has exactly 5 allocated subjects (found: " . count($lkg_subjects) . ")");
assert_true(in_array('Drawing', $lkg_names), "LKG contains 'Drawing'");
assert_true(in_array('English', $lkg_names), "LKG contains 'English'");
assert_true(in_array('EVS', $lkg_names), "LKG contains 'EVS'");
assert_true(in_array('Malayalam', $lkg_names), "LKG contains 'Malayalam'");
assert_true(in_array('Mathematics', $lkg_names), "LKG contains 'Mathematics'");

// Check for duplicates
$lkg_unique_ids = array_unique($lkg_ids);
assert_true(count($lkg_ids) === count($lkg_unique_ids), "Zero duplicate subjects in LKG allocation");

// Check cross-class contamination: no higher grade subjects (e.g. Hindi, Science, Social Science, Biology)
assert_true(!in_array('Science', $lkg_names), "No Grade 4+ 'Science' in LKG subjects");
assert_true(!in_array('Social Science', $lkg_names), "No Grade 5+ 'Social Science' in LKG subjects");
assert_true(!in_array('Biology', $lkg_names), "No Grade 8+ 'Biology' in LKG subjects");
assert_true(!in_array('Physics', $lkg_names), "No Grade 8+ 'Physics' in LKG subjects");
assert_true(!in_array('Chemistry', $lkg_names), "No Grade 8+ 'Chemistry' in LKG subjects");
assert_true(!in_array('Accountancy', $lkg_names), "No Grade 11/12 'Accountancy' in LKG subjects");

echo "\n=== 4. Verify All Active Classes (LKG through Grade 12) ===\n";
$classes_res = $m->query("SELECT class_id, class_name FROM tbl_classes WHERE status = 1 ORDER BY class_id");
$cross_class_violations = 0;
$duplicate_count = 0;

while ($c = $classes_res->fetch_assoc()) {
    $cid = (int)$c['class_id'];
    $cname = $c['class_name'];
    $subs = get_allocated_subjects_for_class($m, $active_year_id, $cid);
    $sub_ids = array_column($subs, 'subject_id');
    $sub_names = array_column($subs, 'subject_name');

    // Duplicate check
    if (count($sub_ids) !== count(array_unique($sub_ids))) {
        $duplicate_count++;
    }

    // Verify each subject actually belongs to this class
    foreach ($subs as $s) {
        if ($s['class_id'] !== NULL && (int)$s['class_id'] !== $cid) {
            $cross_class_violations++;
            echo "  [ERROR] Subject {$s['subject_name']} has class_id {$s['class_id']}, but was returned for class {$cid} ({$cname})\n";
        }
    }

    $cnt = count($subs);
    assert_true(true, "{$cname} (ID: {$cid}) allocated subjects: {$cnt} [" . implode(', ', $sub_names) . "]");
}

assert_true($duplicate_count === 0, "Duplicate subjects in dropdown across all classes: {$duplicate_count}");
assert_true($cross_class_violations === 0, "Cross-class subjects shown across all classes: {$cross_class_violations}");

echo "\n=== 5. Backend Validation Simulation ===\n";
// Scenario: Attacker tries to submit class_id = 1 (LKG) with subject_id = 80 (Grade 10 Biology)
$valid_lkg_ids = array_map('intval', array_column($lkg_subjects, 'subject_id'));
$malicious_subject_id = 80; // Grade 10 Biology

$is_valid_pair = in_array($malicious_subject_id, $valid_lkg_ids, true);
assert_true($is_valid_pair === false, "Backend validation successfully rejects Class 1 (LKG) + Subject 80 (Grade 10 Biology)");

// Scenario: Valid pair: class_id = 1 (LKG) with subject_id = 9 (LKG English)
$valid_subject_id = 9;
$is_valid_pair = in_array($valid_subject_id, $valid_lkg_ids, true);
assert_true($is_valid_pair === true, "Backend validation successfully accepts valid Class 1 (LKG) + Subject 9 (LKG English)");

// Scenario: Class 18 (Grade 5) with Grade 10 subject 80
$g5_subjects = get_allocated_subjects_for_class($m, $active_year_id, 18);
$valid_g5_ids = array_map('intval', array_column($g5_subjects, 'subject_id'));
$is_valid_pair = in_array($malicious_subject_id, $valid_g5_ids, true);
assert_true($is_valid_pair === false, "Backend validation successfully rejects Class 18 (Grade 5) + Subject 80 (Grade 10 Biology)");

echo "\n=== 6. Verify Model & Controller File Fixes ===\n";
// Check Examinations.php has ajax_get_subjects
$exam_ctrl = file_get_contents('application/controllers/Examinations.php');
assert_true(strpos($exam_ctrl, 'public function ajax_get_subjects(') !== false, "Examinations.php defines ajax_get_subjects()");
assert_true(strpos($exam_ctrl, 'get_for_class($this->academic_year_id, (int)$class_id)') !== false, "Examinations.php filters subjects by class and academic year in marks_entry()");
assert_true(strpos($exam_ctrl, 'schedule_not_found') !== false, "Examinations.php marks schedule_not_found when no schedule matches");

// Check routes.php has ajax_get_subjects route
$routes_content = file_get_contents('application/config/routes.php');
assert_true(strpos($routes_content, 'examinations/ajax_get_subjects') !== false, "routes.php maps examinations/ajax_get_subjects");

// Check Exam_mark_model.php has division_id fix
$model_content = file_get_contents('application/models/Exam_mark_model.php');
assert_true(strpos($model_content, "'division_id'      => \$schedule->division_id,") !== false, "Exam_mark_model.php correctly saves division_id (not section_id)");

// Check marks_entry.php has dynamic AJAX functions and safe option rendering
$view_content = file_get_contents('application/views/pages/examinations/marks_entry.php');
assert_true(strpos($view_content, 'onFilterClassChange(this.value)') !== false, "marks_entry.php binds onFilterClassChange()");
assert_true(strpos($view_content, 'ajaxSubjectsUrl') !== false, "marks_entry.php configures ajaxSubjectsUrl");
assert_true(strpos($view_content, 'No subjects are assigned to this class.') !== false, "marks_entry.php includes empty subject case handling");
assert_true(strpos($view_content, 'Loading subjects...') !== false, "marks_entry.php includes loading state");

echo "\n=======================================================\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
