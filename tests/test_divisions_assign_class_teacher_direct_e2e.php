<?php
/**
 * Comprehensive E2E Test: Divisions -> Assign Class Teacher Direct Workflow
 *
 * Verifies:
 * 1. Admin authentication.
 * 2. Academic Management -> Divisions generates exact context URLs for unassigned divisions.
 * 3. Assign Teacher link navigates to Class Teachers and automatically opens modal.
 * 4. Modal pre-selects exact Academic Year, Class, and Division from the clicked row.
 * 5. Teacher dropdown remains unselected initially ("Select Teacher").
 * 6. Direct access to Class Teachers without params does not auto-open the modal.
 * 7. Different division rows carry and pre-select their exact respective values (Row A vs Row B).
 * 8. Backend validation rejects tampered division-class mismatch.
 * 9. Backend validation rejects non-teaching faculty.
 * 10. Valid assignment saves correctly, updates tbl_divisions, avoids duplicates, and displays on Divisions page.
 *
 * Run: php tests/test_divisions_assign_class_teacher_direct_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_div_ct_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($post !== null) {
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

function get_csrf_token($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$tests_run = 0;
$tests_passed = 0;

function assert_test($desc, $condition, $extra = '') {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc" . ($extra ? " - $extra" : "") . "\n";
    }
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
assert_test("Admin login successful", $login_res['code'] === 200);

echo "\n=======================================================\n";
echo "2. Inspect Divisions Page Links\n";
echo "=======================================================\n";

$divs_page = http_req("http://localhost/schoolnew/academics/divisions");
assert_test("Divisions page loads (HTTP 200)", $divs_page['code'] === 200);

// Find all "+ Assign Teacher" links
preg_match_all('/<a\s+href="([^"]*academics\/class_teachers[^"]*)"[^>]*>\s*\+\s*Assign Teacher\s*<\/a>/i', $divs_page['body'], $matches);
assert_test("Found '+ Assign Teacher' links on divisions page", count($matches[1]) > 0, "Found: " . count($matches[1]));

$first_link = $matches[1][0] ?? '';
echo "  [INFO] Sample '+ Assign Teacher' link: $first_link\n";
assert_test("Link contains open_assign=1", strpos($first_link, 'open_assign=1') !== false);
assert_test("Link contains academic_year_id", preg_match('/academic_year_id=\d+/', $first_link));
assert_test("Link contains class_id", preg_match('/class_id=\d+/', $first_link));
assert_test("Link contains division_id", preg_match('/division_id=\d+/', $first_link));
assert_test("Link does NOT contain legacy section_id", strpos($first_link, 'section_id=') === false);

echo "\n=======================================================\n";
echo "3. Test Auto-Open Modal & Pre-Selection with Exact Row Context\n";
echo "=======================================================\n";

// Pick two distinct division rows from DB with active classes
$row_a = $db->query("SELECT d.division_id, d.division_name, d.class_id, c.class_name, c.academic_year_id 
                     FROM tbl_divisions d 
                     JOIN tbl_classes c ON c.class_id = d.class_id 
                     WHERE d.status = 1 AND d.is_deleted = 'n' AND c.status = 1 AND c.is_deleted = 'n' AND d.class_teacher_id IS NULL
                     LIMIT 1")->fetch_assoc();

$row_b = $db->query("SELECT d.division_id, d.division_name, d.class_id, c.class_name, c.academic_year_id 
                     FROM tbl_divisions d 
                     JOIN tbl_classes c ON c.class_id = d.class_id 
                     WHERE d.status = 1 AND d.is_deleted = 'n' AND c.status = 1 AND c.is_deleted = 'n' AND d.class_teacher_id IS NULL AND d.class_id != {$row_a['class_id']}
                     LIMIT 1")->fetch_assoc();

if (!$row_b) {
    // If all in same class, pick different division
    $row_b = $db->query("SELECT d.division_id, d.division_name, d.class_id, c.class_name, c.academic_year_id 
                         FROM tbl_divisions d 
                         JOIN tbl_classes c ON c.class_id = d.class_id 
                         WHERE d.status = 1 AND d.is_deleted = 'n' AND c.status = 1 AND c.is_deleted = 'n' AND d.division_id != {$row_a['division_id']}
                         LIMIT 1")->fetch_assoc();
}

$year_a = $row_a['academic_year_id'] ?: 1;
$url_a = "http://localhost/schoolnew/academics/class_teachers?open_assign=1&academic_year_id={$year_a}&class_id={$row_a['class_id']}&division_id={$row_a['division_id']}";
echo "  [INFO] Testing Row A: {$row_a['class_name']} - Division {$row_a['division_name']} (URL: $url_a)\n";

$ct_page_a = http_req($url_a);
assert_test("Class Teachers page loads with Row A context (HTTP 200)", $ct_page_a['code'] === 200);

// Verify modal is NOT hidden
$modal_not_hidden_a = preg_match('/id="modal-assign-ct"[^>]*class="[^"]*fixed[^"]*"(?!.*hidden)/i', $ct_page_a['body']) 
    || (strpos($ct_page_a['body'], 'id="modal-assign-ct"') !== false && !preg_match('/id="modal-assign-ct"[^>]*class="[^"]*\bhidden\b[^"]*"/i', $ct_page_a['body']));
assert_test("Assign Class Teacher modal is automatically open for Row A", $modal_not_hidden_a);

// Verify Academic Year preselected
$year_selected_a = preg_match('/<option\s+value="' . $year_a . '"\s+selected\s*>/i', $ct_page_a['body']);
assert_test("Academic Year ($year_a) is pre-selected", $year_selected_a);

// Verify Class preselected
$class_selected_a = preg_match('/<option\s+value="' . $row_a['class_id'] . '"\s+selected\s*>' . preg_quote($row_a['class_name'], '/') . '<\/option>/i', $ct_page_a['body']);
assert_test("Class ({$row_a['class_name']}) is pre-selected", $class_selected_a);

// Verify Division preselected
$div_selected_a = preg_match('/<option\s+value="' . $row_a['division_id'] . '"\s+selected\s*>\s*Division\s+' . preg_quote($row_a['division_name'], '/') . '\s*<\/option>/i', $ct_page_a['body']);
assert_test("Division (Division {$row_a['division_name']}) is pre-selected", $div_selected_a);

// Verify Teacher dropdown is unselected ("Select Teacher")
$teacher_empty_a = preg_match('/id="modal_ct_staff"[^>]*>.*?<option\s+value=""\s*>Select Teacher<\/option>/is', $ct_page_a['body']);
$no_teacher_selected_a = !preg_match('/id="modal_ct_staff"[^>]*>.*?<option\s+value="[1-9]\d*"\s+selected\s*>/is', $ct_page_a['body']);
assert_test("Teacher dropdown remains unselected initially ('Select Teacher')", $teacher_empty_a && $no_teacher_selected_a);

echo "\n=======================================================\n";
echo "4. Test Row B Mapping (Ensures No Hardcoding)\n";
echo "=======================================================\n";

$year_b = $row_b['academic_year_id'] ?: 1;
$url_b = "http://localhost/schoolnew/academics/class_teachers?open_assign=1&academic_year_id={$year_b}&class_id={$row_b['class_id']}&division_id={$row_b['division_id']}";
echo "  [INFO] Testing Row B: {$row_b['class_name']} - Division {$row_b['division_name']} (URL: $url_b)\n";

$ct_page_b = http_req($url_b);
assert_test("Class Teachers page loads with Row B context (HTTP 200)", $ct_page_b['code'] === 200);

$class_selected_b = preg_match('/<option\s+value="' . $row_b['class_id'] . '"\s+selected\s*>' . preg_quote($row_b['class_name'], '/') . '<\/option>/i', $ct_page_b['body']);
assert_test("Row B Class ({$row_b['class_name']}) is pre-selected", $class_selected_b);

$div_selected_b = preg_match('/<option\s+value="' . $row_b['division_id'] . '"\s+selected\s*>\s*Division\s+' . preg_quote($row_b['division_name'], '/') . '\s*<\/option>/i', $ct_page_b['body']);
assert_test("Row B Division (Division {$row_b['division_name']}) is pre-selected", $div_selected_b);

// Ensure Row B does NOT select Row A's division
assert_test("Row B does NOT select Row A's division ({$row_a['division_name']})", $row_a['division_id'] === $row_b['division_id'] || strpos($ct_page_b['body'], 'value="' . $row_a['division_id'] . '" selected') === false);

echo "\n=======================================================\n";
echo "4b. Test Same Class, Different Division (LKG Division B vs Division A)\n";
echo "=======================================================\n";

$lkg_div_b = $db->query("SELECT d.division_id, d.division_name, d.class_id, c.class_name, c.academic_year_id 
                         FROM tbl_divisions d 
                         JOIN tbl_classes c ON c.class_id = d.class_id 
                         WHERE c.class_name = 'LKG' AND d.division_name = 'B' AND d.status = 1 AND d.is_deleted = 'n'
                         LIMIT 1")->fetch_assoc();

if ($lkg_div_b) {
    $url_lkg_b = "http://localhost/schoolnew/academics/class_teachers?open_assign=1&academic_year_id={$lkg_div_b['academic_year_id']}&class_id={$lkg_div_b['class_id']}&division_id={$lkg_div_b['division_id']}";
    $ct_page_lkg_b = http_req($url_lkg_b);
    assert_test("LKG Division B page loads (HTTP 200)", $ct_page_lkg_b['code'] === 200);
    assert_test("LKG Class is pre-selected for Division B", preg_match('/<option\s+value="' . $lkg_div_b['class_id'] . '"\s+selected\s*>LKG<\/option>/i', $ct_page_lkg_b['body']));
    assert_test("Division B is pre-selected", preg_match('/<option\s+value="' . $lkg_div_b['division_id'] . '"\s+selected\s*>\s*Division\s+B\s*<\/option>/i', $ct_page_lkg_b['body']));
    assert_test("Division A is NOT selected when Division B requested", !preg_match('/<option\s+value="\d+"\s+selected\s*>\s*Division\s+A\s*<\/option>/i', $ct_page_lkg_b['body']));
}

echo "\n=======================================================\n";
echo "4c. Test Grade 5 Division Mapping\n";
echo "=======================================================\n";

$g5_div = $db->query("SELECT d.division_id, d.division_name, d.class_id, c.class_name, c.academic_year_id 
                      FROM tbl_divisions d 
                      JOIN tbl_classes c ON c.class_id = d.class_id 
                      WHERE c.class_name = 'Grade 5' AND d.status = 1 AND d.is_deleted = 'n' AND c.status = 1 AND c.is_deleted = 'n'
                      LIMIT 1")->fetch_assoc();

if ($g5_div) {
    $url_g5 = "http://localhost/schoolnew/academics/class_teachers?open_assign=1&academic_year_id={$g5_div['academic_year_id']}&class_id={$g5_div['class_id']}&division_id={$g5_div['division_id']}";
    $ct_page_g5 = http_req($url_g5);
    assert_test("Grade 5 page loads (HTTP 200)", $ct_page_g5['code'] === 200);
    assert_test("Grade 5 Class is pre-selected", preg_match('/<option\s+value="' . $g5_div['class_id'] . '"\s+selected\s*>Grade 5<\/option>/i', $ct_page_g5['body']));
    assert_test("Grade 5 Division is pre-selected", preg_match('/<option\s+value="' . $g5_div['division_id'] . '"\s+selected\s*>\s*Division\s+' . preg_quote($g5_div['division_name'], '/') . '\s*<\/option>/i', $ct_page_g5['body']));
}

echo "\n=======================================================\n";
echo "5. Direct Access Verification (Without Context)\n";
echo "=======================================================\n";

$direct_page = http_req("http://localhost/schoolnew/academics/class_teachers");
assert_test("Direct access to Class Teachers loads (HTTP 200)", $direct_page['code'] === 200);

// Verify modal is hidden
$modal_hidden = preg_match('/id="modal-assign-ct"[^>]*class="[^"]*\bhidden\b[^"]*"/i', $direct_page['body']);
assert_test("Modal is hidden by default on direct access", $modal_hidden);

// Verify division select shows 'Select Class First'
$has_select_class_first = strpos($direct_page['body'], 'Select Class First') !== false;
assert_test("Division dropdown shows 'Select Class First' on direct access", $has_select_class_first);

echo "\n=======================================================\n";
echo "6. Security & Backend Validation\n";
echo "=======================================================\n";

$ct_csrf = get_csrf_token($direct_page['body']);

// Test 6.1: Tampered division that does not belong to class
$tamper_post = [
    $ct_csrf['name']   => $ct_csrf['hash'],
    'academic_year_id' => 1,
    'class_id'         => 1, // LKG
    'division_id'      => 10000, // Grade 10's division
    'staff_id'         => 25, // Teacher
];
$tamper_res = http_req("http://localhost/schoolnew/academics/class_teachers", $tamper_post);
assert_test("Mismatched class/division rejected by backend", strpos($tamper_res['body'], 'Selected Division does not belong to the selected Class') !== false);

// Test 6.2: Non-teacher staff assignment rejected
$non_teacher_post = [
    $ct_csrf['name']   => $ct_csrf['hash'],
    'academic_year_id' => 1,
    'class_id'         => $row_a['class_id'],
    'division_id'      => $row_a['division_id'],
    'staff_id'         => 3, // Fathima Beevi - Accountant (non_teaching)
];
$non_teacher_res = http_req("http://localhost/schoolnew/academics/class_teachers", $non_teacher_post);
assert_test("Non-teaching staff assignment rejected by backend", strpos($non_teacher_res['body'], 'Only active teaching faculty can be assigned') !== false);

echo "\n=======================================================\n";
echo "7. End-to-End Assignment & Sync Verification\n";
echo "=======================================================\n";

// Save original division state
$orig_ct_id = $row_a['division_id'];
$orig_db_row = $db->query("SELECT class_teacher_id FROM tbl_divisions WHERE division_id = $orig_ct_id")->fetch_assoc();

// Find an active teacher
$teacher = $db->query("SELECT staff_id, full_name FROM tbl_staff WHERE staff_type = 'teacher' AND status = 1 AND is_deleted = 'n' LIMIT 1")->fetch_assoc();
echo "  [INFO] Assigning teacher '{$teacher['full_name']}' (ID: {$teacher['staff_id']}) to {$row_a['class_name']} - Division {$row_a['division_name']}\n";

$valid_post = [
    $ct_csrf['name']   => $ct_csrf['hash'],
    'academic_year_id' => $year_a,
    'class_id'         => $row_a['class_id'],
    'division_id'      => $row_a['division_id'],
    'staff_id'         => $teacher['staff_id'],
];
$assign_res = http_req("http://localhost/schoolnew/academics/class_teachers", $valid_post);
assert_test("Class Teacher assignment succeeds", strpos($assign_res['body'], 'Class Teacher assigned successfully!') !== false);

// Verify database tbl_class_teachers record
$ct_rec = $db->query("SELECT * FROM tbl_class_teachers WHERE class_id = {$row_a['class_id']} AND division_id = {$row_a['division_id']} AND academic_year_id = $year_a AND is_deleted = 'n'")->fetch_assoc();
assert_test("tbl_class_teachers has active assignment record", $ct_rec && (int)$ct_rec['staff_id'] === (int)$teacher['staff_id']);

// Verify tbl_divisions.class_teacher_id sync
$div_sync = $db->query("SELECT class_teacher_id FROM tbl_divisions WHERE division_id = {$row_a['division_id']}")->fetch_assoc();
assert_test("tbl_divisions.class_teacher_id is synced", (int)$div_sync['class_teacher_id'] === (int)$teacher['staff_id']);

// Check divisions page displays teacher name instead of "+ Assign Teacher"
$div_page_updated = http_req("http://localhost/schoolnew/academics/divisions");
assert_test("Divisions page now displays assigned teacher name", strpos($div_page_updated['body'], htmlspecialchars($teacher['full_name'])) !== false);

// Test re-assignment does not create duplicate
$reassign_res = http_req("http://localhost/schoolnew/academics/class_teachers", $valid_post);
$ct_count = $db->query("SELECT COUNT(*) as cnt FROM tbl_class_teachers WHERE class_id = {$row_a['class_id']} AND division_id = {$row_a['division_id']} AND academic_year_id = $year_a AND is_deleted = 'n'")->fetch_assoc()['cnt'];
assert_test("Re-assignment updates existing record without duplicate (count = 1)", (int)$ct_count === 1);

// Revert test assignment to keep database clean
$db->query("DELETE FROM tbl_class_teachers WHERE class_teacher_id = {$ct_rec['class_teacher_id']}");
$orig_staff_val = $orig_db_row['class_teacher_id'] ? (int)$orig_db_row['class_teacher_id'] : "NULL";
$db->query("UPDATE tbl_divisions SET class_teacher_id = $orig_staff_val WHERE division_id = {$row_a['division_id']}");
echo "  [INFO] Cleaned up test assignment record.\n";

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "\n>>> ALL TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
