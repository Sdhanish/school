<?php
/**
 * Test Suite: Examination Module Division Migration and Error 1054 Resolution
 * Tests schema integrity, model query execution, and schedule lifecycle with division_id.
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

echo "\n=== 1. Verify Database Schema for Division Keys ===\n";
// Check tbl_exam_schedules has division_id and NOT section_id
$res = $m->query("SHOW COLUMNS FROM tbl_exam_schedules LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_exam_schedules has column 'division_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_exam_schedules LIKE 'section_id'");
assert_true($res && $res->num_rows === 0, "tbl_exam_schedules does NOT have column 'section_id'");

// Check tbl_student_results has division_id and division_rank
$res = $m->query("SHOW COLUMNS FROM tbl_student_results LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_student_results has column 'division_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_student_results LIKE 'division_rank'");
assert_true($res && $res->num_rows === 1, "tbl_student_results has column 'division_rank'");

// Check tbl_exam_marks has division_id
$res = $m->query("SHOW COLUMNS FROM tbl_exam_marks LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_exam_marks has column 'division_id'");


echo "\n=== 2. Verify Exam Model Queries (No Error 1054 or Reserved Word Issues) ===\n";
// Upcoming exam schedules query
$today = date('Y-m-d');
$sql_upcoming = "SELECT s.*, e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name, st.full_name as teacher_name
FROM tbl_exam_schedules s
INNER JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
LEFT JOIN tbl_staff st ON st.staff_id = s.teacher_id
WHERE s.exam_date >= '{$today}' AND s.academic_year_id = 1
ORDER BY s.exam_date ASC, s.start_time ASC
LIMIT 6";
$res = $m->query($sql_upcoming);
assert_true($res !== false, "Exam_model::get_upcoming_exam_schedules query executed without SQL error");

// Marks entry progress summary query
$sql_progress = "SELECT s.schedule_id, s.exam_id, e.exam_name, c.class_id, c.class_name, d.division_id, d.division_name as division_name, sub.subject_name,
    COUNT(st.student_id) as total_students,
    COUNT(m.mark_id) as entered_marks_count,
    SUM(CASE WHEN m.status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN m.status = 'Submitted' THEN 1 ELSE 0 END) as submitted_count,
    SUM(CASE WHEN m.status = 'Draft' THEN 1 ELSE 0 END) as draft_count
FROM tbl_exam_schedules s
INNER JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
LEFT JOIN tbl_students st ON st.class_id = s.class_id AND st.division_id = s.division_id AND st.status = 1 AND st.academic_year_id = 1
LEFT JOIN tbl_exam_marks m ON m.schedule_id = s.schedule_id AND m.student_id = st.student_id
WHERE s.academic_year_id = 1
GROUP BY s.schedule_id
ORDER BY c.class_id ASC, d.division_id ASC, sub.subject_name ASC";
$res = $m->query($sql_progress);
assert_true($res !== false, "Exam_model::get_marks_entry_progress_summary query executed without SQL error");


echo "\n=== 3. Verify Exam Schedule Model Queries ===\n";
// Exam_schedule_model::get_all query
$sql_all_sched = "SELECT s.*, e.exam_name, e.start_date as exam_start_date, e.end_date as exam_end_date,
    c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code,
    st.full_name as teacher_name, y.year_name,
    (SELECT COUNT(*) FROM tbl_students stu WHERE stu.class_id = s.class_id AND stu.division_id = s.division_id AND stu.status = 1) as total_students,
    (SELECT COUNT(*) FROM tbl_exam_marks m WHERE m.schedule_id = s.schedule_id) as marks_entered_count
FROM tbl_exam_schedules s
LEFT JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
LEFT JOIN tbl_staff st ON st.staff_id = s.teacher_id
LEFT JOIN tbl_academic_years y ON y.academic_year_id = s.academic_year_id
WHERE s.division_id = 8
ORDER BY s.exam_date ASC, s.start_time ASC";
$res = $m->query($sql_all_sched);
assert_true($res !== false, "Exam_schedule_model::get_all with division_id filter executed without error");

// Exam_schedule_model::check_room_conflict query
$sql_conflict = "SELECT s.*, e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name
FROM tbl_exam_schedules s
LEFT JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
WHERE s.exam_date = '{$today}' AND s.room_no = 'Hall A-101'";
$res = $m->query($sql_conflict);
assert_true($res !== false, "Exam_schedule_model::check_room_conflict query executed without error");


echo "\n=== 4. Verify Exam Mark Model Queries ===\n";
// Exam_mark_model::get_marks_sheet query
$sql_marks_sheet = "SELECT s.*, e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code
FROM tbl_exam_schedules s
LEFT JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
WHERE s.schedule_id = 1";
$res = $m->query($sql_marks_sheet);
assert_true($res !== false, "Exam_mark_model::get_marks_sheet schedule join query executed without error");

// Exam_mark_model::get_marksheets_for_verification query
$sql_verify = "SELECT s.schedule_id, s.exam_id, s.class_id, s.division_id, s.subject_id, s.exam_date,
    e.exam_name, c.class_name, d.division_name as division_name, sub.subject_name, sub.subject_code,
    u.name as entered_by_name,
    COUNT(st.student_id) as total_students,
    COUNT(m.mark_id) as marks_entered_count,
    SUM(CASE WHEN m.status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN m.status = 'Submitted' THEN 1 ELSE 0 END) as submitted_count,
    SUM(CASE WHEN m.status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN m.status = 'Draft' THEN 1 ELSE 0 END) as draft_count,
    MAX(m.submitted_at) as latest_submitted_at
FROM tbl_exam_schedules s
INNER JOIN tbl_exams e ON e.exam_id = s.exam_id
LEFT JOIN tbl_classes c ON c.class_id = s.class_id
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
LEFT JOIN tbl_subjects sub ON sub.subject_id = s.subject_id
LEFT JOIN tbl_students st ON st.class_id = s.class_id AND st.division_id = s.division_id AND st.status = 1
LEFT JOIN tbl_exam_marks m ON m.schedule_id = s.schedule_id AND m.student_id = st.student_id
LEFT JOIN tbl_users u ON u.user_id = m.entered_by
WHERE s.division_id = 8
GROUP BY s.schedule_id
ORDER BY s.exam_date DESC";
$res = $m->query($sql_verify);
assert_true($res !== false, "Exam_mark_model::get_marksheets_for_verification query executed without error");


echo "\n=== 5. Verify Result Model Queries ===\n";
// Result_model::get_results_list query
$sql_results_list = "SELECT r.*, e.exam_name, e.status as exam_status, st.admission_number, st.roll_number, st.first_name, st.last_name, st.photo,
    c.class_name, d.division_name as division_name, y.year_name
FROM tbl_student_results r
LEFT JOIN tbl_exams e ON e.exam_id = r.exam_id
LEFT JOIN tbl_students st ON st.student_id = r.student_id
LEFT JOIN tbl_classes c ON c.class_id = r.class_id
LEFT JOIN tbl_divisions d ON d.division_id = r.division_id
LEFT JOIN tbl_academic_years y ON y.academic_year_id = r.academic_year_id
ORDER BY r.percentage DESC";
$res = $m->query($sql_results_list);
assert_true($res !== false, "Result_model::get_results_list query executed without error");

// Result_model rank calculations query
$sql_sec_ranks = "SELECT result_id, division_id, percentage as score, pass_status
FROM tbl_student_results
WHERE exam_id = 1
ORDER BY division_id ASC, percentage DESC";
$res = $m->query($sql_sec_ranks);
assert_true($res !== false, "Result_model division-wise rank selection query executed without error");


echo "\n=== 6. Test Exam Schedule Lifecycle with division_id ===\n";
// Find unallocated subject for exam_id=1, class_id=1, division_id=8
$sub_res = $m->query("SELECT subject_id FROM tbl_subjects WHERE subject_id NOT IN (SELECT subject_id FROM tbl_exam_schedules WHERE exam_id=1 AND class_id=1 AND division_id=8) LIMIT 1");
$sub_row = $sub_res ? $sub_res->fetch_assoc() : null;
$test_sub_id = $sub_row ? (int)$sub_row['subject_id'] : 999;

// 1. Insert test schedule with division_id
$insert_sql = "INSERT INTO tbl_exam_schedules (exam_id, academic_year_id, class_id, division_id, subject_id, exam_date, start_time, end_time, max_marks, passing_marks, room_no, status, created_at)
VALUES (1, 1, 1, 8, {$test_sub_id}, '2026-10-15', '10:00:00', '13:00:00', 100.00, 40.00, 'Test Room 101', 'Scheduled', NOW())";
$res_in = $m->query($insert_sql);
if (!$res_in) echo "  [DEBUG INSERT ERROR] " . $m->error . "\n";
$new_id = $m->insert_id;
assert_true($new_id > 0, "Created new exam schedule #{$new_id} with division_id = 8 and subject_id = {$test_sub_id}");

// 2. Fetch using query joining tbl_divisions d
$fetch_sql = "SELECT s.*, d.division_name
FROM tbl_exam_schedules s
LEFT JOIN tbl_divisions d ON d.division_id = s.division_id
WHERE s.schedule_id = {$new_id}";
$res = $m->query($fetch_sql);
$sched_row = $res ? $res->fetch_assoc() : null;
assert_true($sched_row && $sched_row['division_id'] == 8, "Fetched schedule #{$new_id} confirms division_id = 8 and joined division_name");

// 3. Update schedule division_id
$update_sql = "UPDATE tbl_exam_schedules SET division_id = 8, room_no = 'Updated Hall 202' WHERE schedule_id = {$new_id}";
$up_ok = $m->query($update_sql);
assert_true($up_ok && $m->affected_rows >= 0, "Updated schedule #{$new_id} successfully");

// 4. Delete the test record
$m->query("DELETE FROM tbl_exam_schedules WHERE schedule_id = {$new_id}");
$check_del = $m->query("SELECT schedule_id FROM tbl_exam_schedules WHERE schedule_id = {$new_id}");
assert_true($check_del && $check_del->num_rows === 0, "Cleaned up test exam schedule #{$new_id}");


echo "\n=== 7. Verify Existing Data Preserved ===\n";
$res_count = $m->query("SELECT COUNT(*) as cnt FROM tbl_exam_schedules");
$cnt_row = $res_count->fetch_assoc();
assert_true((int)$cnt_row['cnt'] >= 3, "Existing exam schedules preserved (count: {$cnt_row['cnt']})");

$res_exams = $m->query("SELECT COUNT(*) as cnt FROM tbl_exams");
$cnt_exams = $res_exams->fetch_assoc();
assert_true((int)$cnt_exams['cnt'] > 0, "Existing exams preserved (count: {$cnt_exams['cnt']})");

$res_divs = $m->query("SELECT COUNT(*) as cnt FROM tbl_divisions WHERE is_deleted = 'n'");
$cnt_divs = $res_divs->fetch_assoc();
assert_true((int)$cnt_divs['cnt'] > 0, "Existing divisions preserved (count: {$cnt_divs['cnt']})");

echo "\n============================================\n";
echo "Test Results: Passed: {$passed} | Failed: {$failed}\n";
echo "============================================\n";

$m->close();
exit($failed > 0 ? 1 : 0);
