<?php
/**
 * Comprehensive Attendance Audit Trail ("Marked By") E2E & Backend Verification Test
 * Run via CLI: php tests/test_attendance_audit_e2e.php
 */

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

function make_session($email, $password) {
    $cookie_file = tempnam(sys_get_temp_dir(), 'ci_att_cookie_');
    $ch = curl_init('http://localhost/schoolnew/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $body = curl_exec($ch);
    preg_match('/name="csrf_test_name" value="([a-f0-9]+)"/i', $body, $m);
    $csrf = $m[1] ?? '';

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'csrf_test_name' => $csrf,
        'email'          => $email,
        'password'       => $password
    ]);
    $res = curl_exec($ch);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return [
        'cookie' => $cookie_file,
        'csrf'   => $csrf,
        'logged' => (strpos($eff, 'dashboard') !== false || strpos($eff, 'overview') !== false || strpos($eff, 'auth') === false)
    ];
}

function http_req_with_cookie($url, $cookie_file, $post = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff];
}

$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

echo "=======================================================\n";
echo "1. Verify Test Users & Roles in DB\n";
echo "=======================================================\n";
$teacher_user = $mysqli->query("SELECT u.user_id, u.name, r.role_name, s.full_name as staff_name FROM tbl_users u LEFT JOIN tbl_roles r ON r.role_id = u.role_id LEFT JOIN tbl_staff s ON s.staff_id = u.staff_id WHERE u.username = 'teacher'")->fetch_assoc();
$principal_user = $mysqli->query("SELECT u.user_id, u.name, r.role_name, s.full_name as staff_name FROM tbl_users u LEFT JOIN tbl_roles r ON r.role_id = u.role_id LEFT JOIN tbl_staff s ON s.staff_id = u.staff_id WHERE u.username = 'principal'")->fetch_assoc();
$admin_user = $mysqli->query("SELECT u.user_id, u.name, r.role_name, s.full_name as staff_name FROM tbl_users u LEFT JOIN tbl_roles r ON r.role_id = u.role_id LEFT JOIN tbl_staff s ON s.staff_id = u.staff_id WHERE u.username = 'Admin'")->fetch_assoc();

assert_check("Teacher user exists with role Teacher", $teacher_user && $teacher_user['role_name'] === 'Teacher', json_encode($teacher_user));
assert_check("Principal user exists with role Principal", $principal_user && $principal_user['role_name'] === 'Principal', json_encode($principal_user));
assert_check("Admin user exists with role Super Admin", $admin_user && $admin_user['role_name'] === 'Super Admin', json_encode($admin_user));

echo "\n=======================================================\n";
echo "2. Authenticate Sessions for All 3 Roles\n";
echo "=======================================================\n";
$sess_teacher = make_session('teacher@school.com', '123456');
assert_check("Teacher login success", $sess_teacher['logged']);

$sess_principal = make_session('principal@school.com', '123456');
assert_check("Principal login success", $sess_principal['logged']);

$sess_admin = make_session('admin@gmail.com', '123456');
assert_check("Admin login success", $sess_admin['logged']);

echo "\n=======================================================\n";
echo "3. Teacher Marks Attendance (Class 1, Student 46, Date 2026-09-15)\n";
echo "=======================================================\n";
$date_teacher = '2026-09-09';
// Clean any prior test attendance on this date
$mysqli->query("DELETE FROM tbl_attendance WHERE attendance_date = '$date_teacher'");

// Submit attendance as Teacher
$post_data = [
    'date' => $date_teacher,
    'academic_year_id' => 1,
    'class_id' => 1,
    'division_id' => 12,
    'attendance' => [
        46 => [
            'status' => 'Present',
            'remarks' => 'Good attendance by Teacher'
        ]
    ]
];
$resp_teacher = http_req_with_cookie('http://localhost/schoolnew/attendance/mark_attendance', $sess_teacher['cookie'], $post_data);
assert_check("Teacher mark HTTP response is valid", $resp_teacher['code'] === 200 || $resp_teacher['code'] === 302);

// Check DB directly
$res = $mysqli->query("SELECT * FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_teacher'")->fetch_assoc();
assert_check("Attendance created in DB", !empty($res));
assert_check("DB marked_by matches Teacher user_id ({$teacher_user['user_id']})", $res['marked_by'] == $teacher_user['user_id'], "DB has marked_by=" . ($res['marked_by'] ?? 'NULL'));
assert_check("DB updated_by is NULL on fresh mark", empty($res['updated_by']), "DB has updated_by=" . ($res['updated_by'] ?? 'NULL'));

echo "\n=======================================================\n";
echo "4. Principal Edits Attendance (Same Record, Date 2026-09-15)\n";
echo "=======================================================\n";
// Submit update as Principal
$post_data_edit = [
    'date' => $date_teacher,
    'academic_year_id' => 1,
    'class_id' => 1,
    'division_id' => 12,
    'attendance' => [
        46 => [
            'status' => 'Half Day',
            'remarks' => 'Status corrected by Principal'
        ]
    ]
];
$resp_edit = http_req_with_cookie('http://localhost/schoolnew/attendance/mark_attendance', $sess_principal['cookie'], $post_data_edit);
assert_check("Principal update HTTP response is valid", $resp_edit['code'] === 200 || $resp_edit['code'] === 302);

// Verify DB: Original marked_by must STILL be Teacher (66), while updated_by must be Principal (65)
$res_after_edit = $mysqli->query("SELECT * FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_teacher'")->fetch_assoc();
assert_check("Status updated to Half Day", $res_after_edit['attendance_status'] === 'Half Day');
assert_check("Original marked_by PRESERVED as Teacher ({$teacher_user['user_id']})", $res_after_edit['marked_by'] == $teacher_user['user_id'], "marked_by is " . $res_after_edit['marked_by']);
assert_check("Audit updated_by recorded as Principal ({$principal_user['user_id']})", $res_after_edit['updated_by'] == $principal_user['user_id'], "updated_by is " . $res_after_edit['updated_by']);

echo "\n=======================================================\n";
echo "5. Principal Marks Separate Attendance (Date 2026-09-11)\n";
echo "=======================================================\n";
$date_principal = '2026-09-11';
$mysqli->query("DELETE FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_principal'");

$post_data_principal = [
    'date' => $date_principal,
    'academic_year_id' => 1,
    'class_id' => 1,
    'division_id' => 12,
    'attendance' => [
        46 => [
            'status' => 'Present',
            'remarks' => 'Marked by Principal directly'
        ]
    ]
];
$resp_p = http_req_with_cookie('http://localhost/schoolnew/attendance/mark_attendance', $sess_principal['cookie'], $post_data_principal);
$res_p = $mysqli->query("SELECT * FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_principal'")->fetch_assoc();
assert_check("DB marked_by matches Principal user_id ({$principal_user['user_id']})", $res_p['marked_by'] == $principal_user['user_id'], "marked_by is " . ($res_p['marked_by'] ?? 'NULL'));

echo "\n=======================================================\n";
echo "6. Historical Record Without Marker (Date 2026-09-01)\n";
echo "=======================================================\n";
$date_hist = '2026-09-01';
$mysqli->query("DELETE FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_hist'");
$mysqli->query("INSERT INTO tbl_attendance (student_id, academic_year_id, class_id, division_id, attendance_date, attendance_type, attendance_status, remarks, marked_by, updated_by, created_at, updated_at) VALUES (46, 1, 1, 12, '$date_hist', 'Daily', 'Absent', 'Historical test', NULL, NULL, '$date_hist 09:00:00', '$date_hist 09:00:00')");
$res_hist = $mysqli->query("SELECT * FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_hist'")->fetch_assoc();
assert_check("Historical record exists with marked_by = NULL", empty($res_hist['marked_by']));

echo "\n=======================================================\n";
echo "7. Student Profile Attendance Audit Trail Verification\n";
echo "=======================================================\n";
$profile_view = http_req_with_cookie('http://localhost/schoolnew/students/profile/46', $sess_admin['cookie']);
assert_check("Profile page loaded successfully", $profile_view['code'] === 200);

// Check column header
assert_check("Header contains 'MARKED BY'", stripos($profile_view['body'], 'MARKED BY') !== false);

// Check Teacher marker resolution: 'Teacher · Arun Krishnan'
assert_check("Profile shows 'Teacher · Arun Krishnan'", stripos($profile_view['body'], 'Teacher · Arun Krishnan') !== false);

// Check Principal edit subtitle: 'Edited by: Principal · Antony Xavier'
assert_check("Profile shows 'Edited by: Principal · Antony Xavier'", stripos($profile_view['body'], 'Edited by: Principal · Antony Xavier') !== false);

// Check Principal record marker resolution: 'Principal · Antony Xavier'
assert_check("Profile shows 'Principal · Antony Xavier'", stripos($profile_view['body'], 'Principal · Antony Xavier') !== false);

// Check Historical record without marker shows 'Not Available'
assert_check("Profile shows 'Not Available' for NULL historical record", stripos($profile_view['body'], 'Not Available') !== false);

echo "\n=======================================================\n";
echo "8. Individual Student Attendance Details Verification\n";
echo "=======================================================\n";
$indiv_view = http_req_with_cookie('http://localhost/schoolnew/attendance/student_attendance?student_id=46', $sess_admin['cookie']);
assert_check("Individual attendance page loaded", $indiv_view['code'] === 200);
assert_check("Individual page shows 'Teacher · Arun Krishnan'", stripos($indiv_view['body'], 'Teacher · Arun Krishnan') !== false);
assert_check("Individual page shows 'Principal · Antony Xavier'", stripos($indiv_view['body'], 'Principal · Antony Xavier') !== false);
assert_check("Individual page shows 'Not Available'", stripos($indiv_view['body'], 'Not Available') !== false);

echo "\n=======================================================\n";
echo "9. Attendance Dashboard Recent Activity Verification\n";
echo "=======================================================\n";
$dash_view = http_req_with_cookie('http://localhost/schoolnew/attendance', $sess_admin['cookie']);
assert_check("Attendance Dashboard loaded", $dash_view['code'] === 200);
assert_check("Dashboard contains 'Marked by'", stripos($dash_view['body'], 'Marked by') !== false);

echo "\n=======================================================\n";
echo "10. Mark Attendance Audit Banner When Editing Saved Attendance\n";
echo "=======================================================\n";
$mark_saved_view = http_req_with_cookie("http://localhost/schoolnew/attendance/mark_attendance?class_id=1&section_id=12&date=$date_teacher&academic_year_id=1", $sess_admin['cookie']);
assert_check("Editing Saved Attendance badge present", stripos($mark_saved_view['body'], 'Editing Saved Attendance') !== false);
assert_check("Banner shows 'Originally marked by: Teacher · Arun Krishnan'", stripos($mark_saved_view['body'], 'Originally marked by:') !== false && stripos($mark_saved_view['body'], 'Teacher · Arun Krishnan') !== false);
assert_check("Banner shows 'Last updated by: Principal · Antony Xavier'", stripos($mark_saved_view['body'], 'Last updated by:') !== false && stripos($mark_saved_view['body'], 'Principal · Antony Xavier') !== false);

echo "\n=======================================================\n";
echo "11. Security & Spoofing Attempt Test\n";
echo "=======================================================\n";
// As Teacher session, attempt to spoof marked_by=15 (Admin)
$date_spoof = '2026-09-05';
$mysqli->query("DELETE FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_spoof'");
$post_spoofed = [
    'date' => $date_spoof,
    'academic_year_id' => 1,
    'class_id' => 1,
    'division_id' => 12,
    'marked_by' => 15,
    'user_id' => 15,
    'attendance' => [
        46 => [
            'status' => 'Present',
            'remarks' => 'Spoof attempt'
        ]
    ]
];
http_req_with_cookie('http://localhost/schoolnew/attendance/mark_attendance', $sess_teacher['cookie'], $post_spoofed);
$res_spoof = $mysqli->query("SELECT * FROM tbl_attendance WHERE student_id = 46 AND attendance_date = '$date_spoof'")->fetch_assoc();
assert_check("DB marked_by ignored spoofed value and used authenticated Teacher ID (66)", $res_spoof['marked_by'] == 66, "DB marked_by was: " . $res_spoof['marked_by']);

echo "\n=======================================================\n";
echo "12. Staff Attendance Audit Trail Test\n";
echo "=======================================================\n";
$date_staff = '2026-09-08';
$mysqli->query("DELETE FROM tbl_staff_attendance WHERE staff_id = 25 AND attendance_date = '$date_staff'");
$staff_post = [
    'attendance_date' => $date_staff,
    'attendance' => [
        25 => [
            'status' => 'Present',
            'remarks' => 'On duty'
        ]
    ]
];
http_req_with_cookie("http://localhost/schoolnew/staff/attendance?date=$date_staff", $sess_admin['cookie'], $staff_post);
$res_staff = $mysqli->query("SELECT * FROM tbl_staff_attendance WHERE staff_id = 25 AND attendance_date = '$date_staff'")->fetch_assoc();
assert_check("Staff attendance saved with marked_by = Admin ID (15)", $res_staff['marked_by'] == 15, "DB marked_by: " . ($res_staff['marked_by'] ?? 'NULL'));

$staff_view = http_req_with_cookie("http://localhost/schoolnew/staff/attendance?date=$date_staff", $sess_admin['cookie']);
assert_check("Staff attendance page has 'Marked By' header", stripos($staff_view['body'], 'Marked By') !== false);
assert_check("Staff attendance displays 'Super Admin · Admin'", stripos($staff_view['body'], 'Super Admin · Admin') !== false);

// Clean up test dates
$mysqli->query("DELETE FROM tbl_attendance WHERE student_id = 46 AND attendance_date IN ('$date_teacher', '$date_principal', '$date_hist', '$date_spoof')");
$mysqli->query("DELETE FROM tbl_staff_attendance WHERE staff_id = 25 AND attendance_date = '$date_staff'");

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "\n>>> ALL ATTENDANCE AUDIT TRAIL E2E TESTS PASSED SUCCESSFULLY! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
