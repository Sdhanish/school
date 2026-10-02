<?php
/**
 * Comprehensive E2E Test: Academic Year Duplicate Entry Error Fix
 *
 * Verifies all required test scenarios:
 * Test 1 — Existing Academic Year: Creating duplicate rejects with friendly flash error, no DB error 1062
 * Test 2 — New Academic Year: Creating a new dynamic year succeeds
 * Test 3 — Edit Without Changing Name: Editing existing record without changing name succeeds
 * Test 4 — Edit to Existing Name: Renaming to another existing year's name rejects with friendly message
 * Test 5 — Double Submit / Race Condition: Concurrent/duplicate submission handled cleanly without DB error
 * Test 6 — Database Verification: Exactly one record created, unique key constraint preserved
 *
 * Run: php tests/test_academic_year_duplicate_entry_e2e.php
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_ay_');

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

$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}

echo "\n=======================================================\n";
echo "2. Verify Unique Constraint 'uk_academic_year_name' Exists\n";
echo "=======================================================\n";

$index_check = $db->query("SHOW INDEXES FROM tbl_academic_years WHERE Key_name = 'uk_academic_year_name'");
assert_test("Unique constraint 'uk_academic_year_name' is preserved in MySQL", $index_check->num_rows > 0);

echo "\n=======================================================\n";
echo "3. Test 1 — Existing Academic Year Creation Attempt\n";
echo "=======================================================\n";

// Get an existing active academic year
$existing_row = $db->query("SELECT * FROM tbl_academic_years WHERE status = 1 AND is_deleted = 'n' LIMIT 1")->fetch_assoc();
$existing_name = $existing_row['year_name'];
echo "  [INFO] Attempting to create duplicate of existing year: '$existing_name'\n";

$years_page = http_req("http://localhost/schoolnew/academics/years");
$csrf = get_csrf_token($years_page['body']);

// Attempt 1: Exact duplicate
$dup_res = http_req("http://localhost/schoolnew/academics/years", [
    $csrf['name']       => $csrf['hash'],
    'action'            => 'add',
    'year_name'         => $existing_name,
    'start_date'        => '2026-06-01',
    'end_date'          => '2027-03-31',
    'is_active'         => '0'
]);

assert_test("Duplicate attempt does NOT cause HTTP 500 error", $dup_res['code'] === 200);
assert_test("No raw database error page (Error Number: 1062) shown", strpos($dup_res['body'], 'Error Number: 1062') === false);
assert_test("User-friendly error message displayed", strpos($dup_res['body'], "Academic year $existing_name already exists.") !== false);

// Attempt 2: Duplicate with leading and trailing whitespace
$csrf = get_csrf_token($dup_res['body']);
$dup_space_res = http_req("http://localhost/schoolnew/academics/years", [
    $csrf['name']       => $csrf['hash'],
    'action'            => 'add',
    'year_name'         => "   $existing_name   ",
    'start_date'        => '2026-06-01',
    'end_date'          => '2027-03-31',
    'is_active'         => '0'
]);
assert_test("Whitespace-padded duplicate attempt does NOT cause 1062 error", strpos($dup_space_res['body'], 'Error Number: 1062') === false);
assert_test("Whitespace-padded duplicate rejected with friendly message", strpos($dup_space_res['body'], "Academic year $existing_name already exists.") !== false);

echo "\n=======================================================\n";
echo "4. Test 2 — New Academic Year Creation\n";
echo "=======================================================\n";

$unique_suffix = rand(3000, 9000);
$new_year_name = "2090-2091-" . $unique_suffix;

$csrf = get_csrf_token($dup_space_res['body']);
$add_res = http_req("http://localhost/schoolnew/academics/years", [
    $csrf['name']       => $csrf['hash'],
    'action'            => 'add',
    'year_name'         => $new_year_name,
    'start_date'        => '2090-06-01',
    'end_date'          => '2091-03-31',
    'is_active'         => '0'
]);

assert_test("New Academic Year add request returned HTTP 200", $add_res['code'] === 200);
assert_test("Success flash message displayed", strpos($add_res['body'], 'Academic Year added successfully!') !== false);

$db_new = $db->query("SELECT * FROM tbl_academic_years WHERE year_name = '$new_year_name'")->fetch_assoc();
assert_test("New Academic Year record found in tbl_academic_years", !empty($db_new));
$created_year_id = (int)$db_new['academic_year_id'];

echo "\n=======================================================\n";
echo "5. Test 3 — Edit Without Changing Name\n";
echo "=======================================================\n";

$csrf = get_csrf_token($add_res['body']);
$edit_same_res = http_req("http://localhost/schoolnew/academics/years", [
    $csrf['name']         => $csrf['hash'],
    'action'              => 'edit',
    'academic_year_id'    => $created_year_id,
    'year_name'           => $new_year_name,
    'start_date'          => '2090-06-15',
    'end_date'            => '2091-04-15',
    'is_active'           => '0'
]);

assert_test("Edit without changing name returned HTTP 200", $edit_same_res['code'] === 200);
assert_test("No duplicate error on editing own record", strpos($edit_same_res['body'], 'already exists') === false);
assert_test("Update success message displayed", strpos($edit_same_res['body'], 'Academic Year updated successfully!') !== false);

$db_updated = $db->query("SELECT start_date, end_date FROM tbl_academic_years WHERE academic_year_id = $created_year_id")->fetch_assoc();
assert_test("Record values updated in database", $db_updated['start_date'] === '2090-06-15' && $db_updated['end_date'] === '2091-04-15');

echo "\n=======================================================\n";
echo "6. Test 4 — Edit to Existing Name (Collision With Another Year)\n";
echo "=======================================================\n";

$csrf = get_csrf_token($edit_same_res['body']);
$edit_collision_res = http_req("http://localhost/schoolnew/academics/years", [
    $csrf['name']         => $csrf['hash'],
    'action'              => 'edit',
    'academic_year_id'    => $created_year_id,
    'year_name'           => $existing_name, // Collides with another record
    'start_date'          => '2090-06-15',
    'end_date'            => '2091-04-15',
    'is_active'           => '0'
]);

assert_test("Edit collision request returned HTTP 200 without DB error 1062", $edit_collision_res['code'] === 200 && strpos($edit_collision_res['body'], 'Error Number: 1062') === false);
assert_test("Collision rejected with user-friendly error", strpos($edit_collision_res['body'], "Academic year $existing_name already exists.") !== false);

$db_unchanged = $db->query("SELECT year_name FROM tbl_academic_years WHERE academic_year_id = $created_year_id")->fetch_assoc();
assert_test("Record was NOT renamed to colliding name", $db_unchanged['year_name'] === $new_year_name);

echo "\n=======================================================\n";
echo "7. Test 5 — Double Submit / Race Condition Simulation\n";
echo "=======================================================\n";

// Test double submission via concurrent curl_multi
$csrf = get_csrf_token($edit_collision_res['body']);
$dup_test_name = "2092-2093-" . rand(1000, 9999);

$mh = curl_multi_init();
$ch1 = curl_init("http://localhost/schoolnew/academics/years");
$ch2 = curl_init("http://localhost/schoolnew/academics/years");

$post_data = [
    $csrf['name'] => $csrf['hash'],
    'action'      => 'add',
    'year_name'   => $dup_test_name,
    'start_date'  => '2092-06-01',
    'end_date'    => '2093-03-31',
    'is_active'   => '0'
];

foreach ([$ch1, $ch2] as $ch) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_multi_add_handle($mh, $ch);
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$res1 = curl_multi_getcontent($ch1);
$res2 = curl_multi_getcontent($ch2);
curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);
curl_close($ch1);
curl_close($ch2);

// Neither request should show raw database error 1062
assert_test("Concurrent request 1 has no 1062 raw DB error", strpos($res1, 'Error Number: 1062') === false);
assert_test("Concurrent request 2 has no 1062 raw DB error", strpos($res2, 'Error Number: 1062') === false);

// Exactly one should be in the database
$conc_count = (int)$db->query("SELECT COUNT(*) AS total FROM tbl_academic_years WHERE year_name = '$dup_test_name'")->fetch_assoc()['total'];
assert_test("Database has exactly 1 record for concurrent submissions", $conc_count === 1);
$db->query("DELETE FROM tbl_academic_years WHERE year_name = '$dup_test_name'");

echo "\n=======================================================\n";
echo "8. Test 6 — Database Verification & Invariant Check\n";
echo "=======================================================\n";

$count_res = $db->query("SELECT COUNT(*) AS total FROM tbl_academic_years WHERE year_name = '$new_year_name'");
$count = (int)$count_res->fetch_assoc()['total'];
assert_test("Exactly ONE record exists for test year '$new_year_name'", $count === 1);

// Clean up test record
$db->query("DELETE FROM tbl_academic_years WHERE academic_year_id = $created_year_id");
$after_clean = $db->query("SELECT COUNT(*) AS total FROM tbl_academic_years WHERE academic_year_id = $created_year_id")->fetch_assoc()['total'];
assert_test("Test record cleaned up successfully", (int)$after_clean === 0);

@unlink($cookie_file);

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    exit(0);
} else {
    exit(1);
}
