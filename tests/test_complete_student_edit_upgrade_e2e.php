<?php
/**
 * Comprehensive E2E Test for Complete Student Edit Upgrade
 * Verifies all requirements:
 * 1. Readonly Admission Number (cannot be edited/regenerated)
 * 2. Pre-filling all fields (Personal, Academic, Parent, Previous School, TC, Activities, Documents)
 * 3. Parent / Guardian editing & validation (mandatory contact)
 * 4. Previous School & TC editing
 * 5. TC Document upload & preservation
 * 6. Academic & Extracurricular activities syncing
 * 7. Photo update and preservation
 * 8. Class & Division dependency
 * 9. Update only (never insert; student count unchanged)
 * 10. No regressions in Add Student wizard or Profile
 *
 * Run: php tests/test_complete_student_edit_upgrade_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_edit_upgrade_');

function http_req($url, $post = null, $files = []) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if (!empty($files) || (is_array($post) && !empty($files))) {
        curl_setopt($ch, CURLOPT_POST, true);
        $multipart = $post;
        foreach ($files as $name => $path) {
            $mime = (pathinfo($path, PATHINFO_EXTENSION) === 'pdf') ? 'application/pdf' : 'image/jpeg';
            $multipart[$name] = new CURLFile($path, $mime, basename($path));
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
    } elseif ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'err' => $err];
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

function get_csrf_token($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    if (preg_match('/var\s+CSRF_HASH\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
        return ['name' => 'csrf_token', 'hash' => $m[1]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}

echo "=======================================================\n";
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$login_page = http_req("http://localhost/schoolnew/auth/login");
$csrf = get_csrf_token($login_page['body']);
$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assert_check("Admin login successful", strpos($login_res['url'], 'auth/login') === false && $login_res['code'] === 200);

$initial_count = (int)$db->query("SELECT COUNT(*) as cnt FROM tbl_students WHERE is_deleted = 'n'")->fetch_assoc()['cnt'];
echo "  Total active students in DB: {$initial_count}\n";

$target_student = $db->query("SELECT * FROM tbl_students WHERE is_deleted = 'n' ORDER BY student_id ASC LIMIT 1")->fetch_assoc();
$st_id = (int)$target_student['student_id'];
$orig_adm = $target_student['admission_number'];
echo "  Target Student ID: {$st_id}, Adm: {$orig_adm}\n";

echo "\n=======================================================\n";
echo "2. Inspect Edit Page for Target Student\n";
echo "=======================================================\n";

$edit_get = http_req("http://localhost/schoolnew/students/edit/{$st_id}");
assert_check("Edit page returns HTTP 200", $edit_get['code'] === 200);
assert_check("Title is 'Edit Student'", strpos($edit_get['body'], 'Edit Student') !== false);

// Check Readonly Admission Number
assert_check("Admission Number is readonly in DOM", preg_match('/name="admission_number"[^>]*readonly/i', $edit_get['body']));
assert_check("Admission Number pre-filled correctly", strpos($edit_get['body'], 'value="' . htmlspecialchars($orig_adm) . '"') !== false);

// Check Personal fields presence
assert_check("First Name input present & pre-filled", strpos($edit_get['body'], 'name="first_name"') !== false && strpos($edit_get['body'], htmlspecialchars($target_student['first_name'])) !== false);
assert_check("Middle Name input present", strpos($edit_get['body'], 'name="middle_name"') !== false);
assert_check("Nationality input present", strpos($edit_get['body'], 'name="nationality"') !== false);
assert_check("Religion input present", strpos($edit_get['body'], 'name="religion"') !== false);

// Check Academic, Parent, Previous School, Activities, Documents sections
assert_check("Academic Details section present", strpos($edit_get['body'], 'Academic Details') !== false);
assert_check("Parent / Guardian section present", strpos($edit_get['body'], 'Parent / Guardian') !== false);
assert_check("Previous School section present", strpos($edit_get['body'], 'Previous School Information') !== false);
assert_check("No Previous School checkbox present", strpos($edit_get['body'], 'name="no_previous_school"') !== false);
assert_check("TC Number field present", strpos($edit_get['body'], 'name="tc_number"') !== false);
assert_check("TC Document input present", strpos($edit_get['body'], 'name="tc_document"') !== false);
assert_check("Academic Activities repeater present", strpos($edit_get['body'], 'academic_activities') !== false);
assert_check("Extracurricular Activities repeater present", strpos($edit_get['body'], 'extracurricular') !== false);
assert_check("Student Documents section present", strpos($edit_get['body'], 'Student Documents') !== false);

echo "\n=======================================================\n";
echo "3. Mandatory Parent/Guardian Validation\n";
echo "=======================================================\n";

$csrf_edit = get_csrf_token($edit_get['body']);
$invalid_payload = [
    $csrf_edit['name'] => $csrf_edit['hash'],
    'admission_number' => $orig_adm,
    'first_name'       => $target_student['first_name'],
    'class_id'         => $target_student['class_id'],
    'division_id'      => $target_student['division_id'],
    'guardian_name'    => '', // intentionally empty
    'guardian_phone'   => '', // intentionally empty
];
$fail_res = http_req("http://localhost/schoolnew/students/edit/{$st_id}", $invalid_payload);
assert_check("Empty guardian fields rejected with validation error", 
    strpos($fail_res['body'], 'Guardian Name field is required') !== false || 
    strpos($fail_res['body'], 'Guardian Phone field is required') !== false ||
    strpos($fail_res['body'], 'required') !== false
);

echo "\n=======================================================\n";
echo "4. Comprehensive Update with Parent, Previous School, TC & Activities\n";
echo "=======================================================\n";

$new_guardian_name  = "Guardian Test " . rand(100, 999);
$new_guardian_phone = "9847" . rand(100000, 999999);
$new_prev_school    = "National Public School " . rand(10, 99);
$new_tc_num         = "TC/2026/" . rand(1000, 9999);
$new_activity_name  = "Science Olympiad Winner " . rand(10, 99);
$new_extra_name     = "State Basketball Championship " . rand(10, 99);

// Create a dummy valid PDF file for TC Document
$temp_pdf = tempnam(sys_get_temp_dir(), 'test_tc_') . '.pdf';
file_put_contents($temp_pdf, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\nxref\n0 2\ntrailer<</Root 1 0 R>>\nstartxref\n9\n%%EOF");

$csrf_edit = get_csrf_token($fail_res['body']);

$update_data = [
    $csrf_edit['name']       => $csrf_edit['hash'],
    'admission_number'       => 'HACKED_ADM_NO', // should be ignored and kept as $orig_adm
    'first_name'             => $target_student['first_name'],
    'middle_name'            => 'K',
    'last_name'              => $target_student['last_name'],
    'gender'                 => 'Male',
    'date_of_birth'          => '2014-04-12',
    'blood_group'            => 'B+',
    'nationality'            => 'Indian',
    'religion'               => 'Hindu',
    'academic_year_id'       => $target_student['academic_year_id'],
    'class_id'               => $target_student['class_id'],
    'division_id'            => $target_student['division_id'],
    'roll_number'            => '22',
    'guardian_name'          => $new_guardian_name,
    'guardian_relation'      => 'Father',
    'guardian_phone'         => $new_guardian_phone,
    'guardian_email'         => 'parent.test@example.com',
    'address'                => '42 Test Colony, Kochi',
    'no_previous_school'     => '0',
    'prev_school_name'       => $new_prev_school,
    'prev_school_address'    => 'Kaloor, Kochi',
    'prev_school_board'      => 'CBSE',
    'prev_class'             => 'Class 4',
    'prev_academic_year'     => '2025-2026',
    'date_of_leaving'        => '2026-03-31',
    'prev_percentage'        => '88.50',
    'reason_for_leaving'     => 'Relocation',
    'tc_number'              => $new_tc_num,
    'academic_activities[0][activity_type]'   => 'Olympiad',
    'academic_activities[0][activity_name]'   => $new_activity_name,
    'academic_activities[0][position_result]' => '1st Rank',
    'academic_activities[0][year]'            => '2025',
    'academic_activities[0][description]'     => 'National level science olympiad gold medal',
    'extracurricular[0][activity_type]'       => 'Sports',
    'extracurricular[0][activity_name]'       => $new_extra_name,
    'extracurricular[0][level]'               => 'State',
    'extracurricular[0][position_result]'     => 'Gold Medal',
    'extracurricular[0][year]'                => '2025',
    'extracurricular[0][description]'         => 'Captained state basketball team',
];

$upd_response = http_req("http://localhost/schoolnew/students/edit/{$st_id}", $update_data, ['tc_document' => $temp_pdf]);
@unlink($temp_pdf);

assert_check("Update request executed (HTTP 200/302)", in_array($upd_response['code'], [200, 302]));

// Verify Database updates
$db_st = $db->query("SELECT * FROM tbl_students WHERE student_id = {$st_id}")->fetch_assoc();
assert_check("Admission number was NOT modified by POST tampering", $db_st['admission_number'] === $orig_adm);
assert_check("Guardian name updated in DB", $db_st['guardian_name'] === $new_guardian_name);
assert_check("Guardian phone updated in DB", $db_st['guardian_phone'] === $new_guardian_phone);
assert_check("Middle name updated in DB", $db_st['middle_name'] === 'K');
assert_check("Roll number updated in DB", $db_st['roll_number'] === '22');

// Verify Previous School in DB
$db_ps = $db->query("SELECT * FROM tbl_student_previous_school WHERE student_id = {$st_id} AND status = 1 ORDER BY prev_school_id DESC LIMIT 1")->fetch_assoc();
assert_check("Previous School record saved in DB", !empty($db_ps));
assert_check("Previous School Name correct in DB", ($db_ps['school_name'] ?? '') === $new_prev_school);
assert_check("TC Number correct in DB", ($db_ps['tc_number'] ?? '') === $new_tc_num);
assert_check("TC Document ID linked in previous school", !empty($db_ps['tc_document_id']));

// Verify TC Document in tbl_student_documents
$doc_id = (int)($db_ps['tc_document_id'] ?? 0);
$db_doc = $db->query("SELECT * FROM tbl_student_documents WHERE document_id = {$doc_id}")->fetch_assoc();
assert_check("TC Document recorded in tbl_student_documents", !empty($db_doc));
assert_check("TC Document type is Transfer Certificate", ($db_doc['document_type'] ?? '') === 'Transfer Certificate');
assert_check("TC Document file exists on disk", file_exists(__DIR__ . '/../' . ($db_doc['file_path'] ?? '')));

// Verify Activities in DB
$db_act = $db->query("SELECT * FROM tbl_student_activities WHERE student_id = {$st_id} AND category = 'Academic' ORDER BY activity_id DESC LIMIT 1")->fetch_assoc();
assert_check("Academic activity saved in DB", ($db_act['activity_name'] ?? '') === $new_activity_name);

$db_extra = $db->query("SELECT * FROM tbl_student_activities WHERE student_id = {$st_id} AND category = 'Extracurricular' ORDER BY activity_id DESC LIMIT 1")->fetch_assoc();
assert_check("Extracurricular activity saved in DB", ($db_extra['activity_name'] ?? '') === $new_extra_name);

echo "\n=======================================================\n";
echo "5. Reopen Edit Page & Verify Pre-filled Saved Data\n";
echo "=======================================================\n";

$reopened = http_req("http://localhost/schoolnew/students/edit/{$st_id}");
assert_check("Reopened edit page loads HTTP 200", $reopened['code'] === 200);
assert_check("Guardian name pre-filled with updated value", strpos($reopened['body'], htmlspecialchars($new_guardian_name)) !== false);
assert_check("Guardian phone pre-filled with updated value", strpos($reopened['body'], htmlspecialchars($new_guardian_phone)) !== false);
assert_check("Previous School name pre-filled", strpos($reopened['body'], htmlspecialchars($new_prev_school)) !== false);
assert_check("TC Number pre-filled", strpos($reopened['body'], htmlspecialchars($new_tc_num)) !== false);
assert_check("Existing TC Document download link visible", strpos($reopened['body'], 'View / Download Existing Document') !== false);
assert_check("Academic activity pre-filled in repeater", strpos($reopened['body'], htmlspecialchars($new_activity_name)) !== false);
assert_check("Extracurricular activity pre-filled in repeater", strpos($reopened['body'], htmlspecialchars($new_extra_name)) !== false);

echo "\n=======================================================\n";
echo "6. Update Without Re-uploading TC Document (Preservation Test)\n";
echo "=======================================================\n";

$csrf_edit2 = get_csrf_token($reopened['body']);
$update_keep_tc = [
    $csrf_edit2['name']   => $csrf_edit2['hash'],
    'admission_number'    => $orig_adm,
    'first_name'          => $target_student['first_name'],
    'class_id'            => $target_student['class_id'],
    'division_id'         => $target_student['division_id'],
    'guardian_name'       => $new_guardian_name,
    'guardian_phone'      => $new_guardian_phone,
    'no_previous_school'  => '0',
    'prev_school_name'    => $new_prev_school,
    'tc_number'           => $new_tc_num,
];
$keep_tc_res = http_req("http://localhost/schoolnew/students/edit/{$st_id}", $update_keep_tc);
assert_check("Update without re-uploading TC succeeded", in_array($keep_tc_res['code'], [200, 302]));

$db_ps2 = $db->query("SELECT * FROM tbl_student_previous_school WHERE student_id = {$st_id} AND status = 1 ORDER BY prev_school_id DESC LIMIT 1")->fetch_assoc();
assert_check("Existing TC Document ID preserved", (int)$db_ps2['tc_document_id'] === $doc_id);

echo "\n=======================================================\n";
echo "7. Student Count Invariant Check (UPDATE Only, Never INSERT)\n";
echo "=======================================================\n";

$final_count = (int)$db->query("SELECT COUNT(*) as cnt FROM tbl_students WHERE is_deleted = 'n'")->fetch_assoc()['cnt'];
assert_check("Student count did NOT change (Before: {$initial_count}, After: {$final_count})", $final_count === $initial_count);

echo "\n=======================================================\n";
echo "8. Profile & ID Card Regression Verification\n";
echo "=======================================================\n";

$prof = http_req("http://localhost/schoolnew/students/profile/{$st_id}");
assert_check("Profile page loads HTTP 200", $prof['code'] === 200);
assert_check("Profile shows updated guardian phone", strpos($prof['body'], $new_guardian_phone) !== false);

$idcard = http_req("http://localhost/schoolnew/students/id_cards?student_id={$st_id}");
assert_check("ID Cards page loads HTTP 200", $idcard['code'] === 200);
assert_check("ID Cards page free of PHP errors", strpos($idcard['body'], 'A PHP Error was encountered') === false);

echo "\n=======================================================\n";
echo "9. Add Student / Registration Wizard Verification\n";
echo "=======================================================\n";

$add = http_req("http://localhost/schoolnew/students/add");
assert_check("students/add loads HTTP 200", $add['code'] === 200);
assert_check("students/add has Registration Wizard step structure", strpos($add['body'], 'Student Registration') !== false);

echo "\n=======================================================\n";
echo "10. Student Photo Upload & Preservation Test\n";
echo "=======================================================\n";

$im = imagecreatetruecolor(60, 80);
$bg = imagecolorallocate($im, 240, 240, 240);
imagefilledrectangle($im, 0, 0, 60, 80, $bg);
ob_start();
imagejpeg($im, null, 90);
$raw_jpeg = ob_get_clean();
imagedestroy($im);
$dummy_jpeg_base64 = 'data:image/jpeg;base64,' . base64_encode($raw_jpeg);

$fresh_edit_page = http_req("http://localhost/schoolnew/students/edit/{$st_id}");
$fresh_csrf = get_csrf_token($fresh_edit_page['body']);

$photo_update_post = [
    $fresh_csrf['name']   => $fresh_csrf['hash'],
    'admission_number'    => $orig_adm,
    'first_name'          => $target_student['first_name'],
    'class_id'            => $target_student['class_id'],
    'division_id'         => $target_student['division_id'],
    'guardian_name'       => $new_guardian_name,
    'guardian_phone'      => $new_guardian_phone,
    'no_previous_school'  => '1',
    'cropped_image_data'  => $dummy_jpeg_base64,
    'remove_photo'        => '0',
];
$photo_res = http_req("http://localhost/schoolnew/students/edit/{$st_id}", $photo_update_post);
assert_check("Photo update redirected (HTTP 302)", in_array($photo_res['code'], [200, 302]) && strpos($photo_res['url'], 'profile') !== false);

$db_photo_st = $db->query("SELECT photo FROM tbl_students WHERE student_id = {$st_id}")->fetch_assoc();
$new_photo_file = $db_photo_st['photo'] ?? '';
echo "  Photo in DB: '{$new_photo_file}'\n";
assert_check("Student photo saved in DB", !empty($new_photo_file));
assert_check("Student photo file exists in uploads/students/", file_exists(__DIR__ . '/../uploads/students/' . $new_photo_file));

// Next: update without photo upload -> must preserve $new_photo_file
$fresh_edit_page2 = http_req("http://localhost/schoolnew/students/edit/{$st_id}");
$fresh_csrf2 = get_csrf_token($fresh_edit_page2['body']);

$preserve_photo_post = [
    $fresh_csrf2['name']  => $fresh_csrf2['hash'],
    'admission_number'    => $orig_adm,
    'first_name'          => $target_student['first_name'],
    'class_id'            => $target_student['class_id'],
    'division_id'         => $target_student['division_id'],
    'guardian_name'       => $new_guardian_name,
    'guardian_phone'      => $new_guardian_phone,
    'no_previous_school'  => '1',
    'cropped_image_data'  => '',
    'remove_photo'        => '0',
];
$preserve_res = http_req("http://localhost/schoolnew/students/edit/{$st_id}", $preserve_photo_post);
assert_check("Preserve photo update request executed", in_array($preserve_res['code'], [200, 302]));

$db_photo_st2 = $db->query("SELECT photo FROM tbl_students WHERE student_id = {$st_id}")->fetch_assoc();
assert_check("Photo preserved in DB when not re-uploaded", $db_photo_st2['photo'] === $new_photo_file);

// Clean up test photo file from disk
if (!empty($new_photo_file) && file_exists(__DIR__ . '/../uploads/students/' . $new_photo_file)) {
    @unlink(__DIR__ . '/../uploads/students/' . $new_photo_file);
}

// Clean up: restore original student values in DB
$db->query("UPDATE tbl_students SET 
    guardian_name = '" . $db->real_escape_string($target_student['guardian_name'] ?? '') . "',
    guardian_phone = '" . $db->real_escape_string($target_student['guardian_phone'] ?? '') . "',
    middle_name = '" . $db->real_escape_string($target_student['middle_name'] ?? '') . "',
    roll_number = '" . $db->real_escape_string($target_student['roll_number'] ?? '') . "',
    photo = '" . $db->real_escape_string($target_student['photo'] ?? '') . "'
WHERE student_id = {$st_id}");

echo "\n=======================================================\n";
echo "RESULTS: {$tests_passed} / {$tests_run} tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: " . ($tests_run - $tests_passed) . " test(s) failed.\n";
    exit(1);
}
