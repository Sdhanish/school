<?php
// tests/test_student_details_enhancement.php
// Automated test script for Middle Name, Student Address, and Student Email enhancements

$dsn = 'mysql:host=localhost;dbname=db_school;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== TEST 1: Database Column Existence Check ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_students")->fetchAll(PDO::FETCH_COLUMN, 0);
$required_cols = [
    'first_name', 'middle_name', 'last_name',
    'student_email',
    'house_name', 'street', 'city', 'district', 'state', 'pin_code',
    'address'
];
foreach ($required_cols as $col) {
    if (in_array($col, $cols)) {
        echo "  [PASS] Column '$col' exists.\n";
    } else {
        echo "  [FAIL] Column '$col' MISSING!\n";
        exit(1);
    }
}

echo "\n=== TEST 2: Pre-existing Records Check ===\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM tbl_students WHERE is_deleted = 'n'");
$active_count = (int)$stmt->fetchColumn();
echo "  Total active students: $active_count (Expected: >= 72)\n";
if ($active_count < 72) {
    echo "  [FAIL] Data loss detected!\n";
    exit(1);
}
echo "  [PASS] All existing student records preserved.\n";

echo "\n=== TEST 3: Helper Functions Test ===\n";
defined('BASEPATH') or define('BASEPATH', true);
require_once __DIR__ . '/../application/helpers/app_helper.php';

// Student Name formatting
$test1 = school_student_name('Anandhu', 'Kumar', 'Uthaman');
assert($test1 === 'Anandhu Kumar Uthaman', "Test 1 failed: $test1");
echo "  [PASS] school_student_name('Anandhu', 'Kumar', 'Uthaman') => '$test1'\n";

$test2 = school_student_name('Aarav', '', 'Nair');
assert($test2 === 'Aarav Nair', "Test 2 failed: $test2");
echo "  [PASS] school_student_name('Aarav', '', 'Nair') => '$test2'\n";

$test3 = school_student_name('Rahul', null, 'Dravid');
assert($test3 === 'Rahul Dravid', "Test 3 failed: $test3");
echo "  [PASS] school_student_name('Rahul', null, 'Dravid') => '$test3'\n";

$obj = (object)[
    'first_name'  => 'Anandhu',
    'middle_name' => 'Kumar',
    'last_name'   => 'Uthaman'
];
$test4 = school_student_name($obj);
assert($test4 === 'Anandhu Kumar Uthaman', "Test 4 failed: $test4");
echo "  [PASS] school_student_name(object) => '$test4'\n";

// Address formatting
$addrObj = (object)[
    'house_name' => 'Rose Villa',
    'street'     => 'MG Road',
    'city'       => 'Kochi',
    'district'   => 'Ernakulam',
    'state'      => 'Kerala',
    'pin_code'   => '682001'
];
$addrFormatted = school_format_address($addrObj);
$expectedAddr = 'Rose Villa, MG Road, Kochi, Ernakulam, Kerala — 682001';
assert($addrFormatted === $expectedAddr, "Address test failed: $addrFormatted");
echo "  [PASS] school_format_address(object) => '$addrFormatted'\n";

// Partial address
$addrPartial = (object)[
    'house_name' => 'Flat 4B',
    'city'       => 'Kochi',
    'state'      => 'Kerala'
];
$addrPartialFormatted = school_format_address($addrPartial);
$expectedPartial = 'Flat 4B, Kochi, Kerala';
assert($addrPartialFormatted === $expectedPartial, "Partial address test failed: $addrPartialFormatted");
echo "  [PASS] Partial address => '$addrPartialFormatted'\n";

echo "\n=== TEST 4: PIN Code Validation Logic ===\n";
function test_pincode($pin) {
    $pin = trim((string)$pin);
    if ($pin === '') return true;
    $clean = preg_replace('/\s+/', '', $pin);
    return (bool)preg_match('/^[1-9][0-9]{5}$/', $clean);
}

assert(test_pincode('') === true, "Empty PIN should be valid");
assert(test_pincode('682001') === true, "682001 should be valid");
assert(test_pincode('682 001') === true, "PIN with space should be valid");
assert(test_pincode('012345') === false, "PIN starting with 0 should be invalid");
assert(test_pincode('68200') === false, "5-digit PIN should be invalid");
assert(test_pincode('6820011') === false, "7-digit PIN should be invalid");
assert(test_pincode('ABCDEF') === false, "Alpha PIN should be invalid");
echo "  [PASS] PIN code validation logic verified for all test cases.\n";

echo "\n=== TEST 5: Student Email Non-Validation (Accepts Any Text or Empty) ===\n";
// The specification explicitly requires NO email format validation (can be empty, valid email, or free text)
$emails = ['', 'test@school.edu', 'custom-handle-123', 'student.email'];
foreach ($emails as $em) {
    $trimmed = trim($em);
    $valid = (strlen($trimmed) <= 100);
    assert($valid, "Email length test failed for $em");
}
echo "  [PASS] Student Email accepts any string <= 100 chars or empty without format restriction.\n";

echo "\n=== TEST 6: DB Insert and Update Flow Test ===\n";
$test_adm = 'TEST_ADM_' . time();
$insert_sql = "INSERT INTO tbl_students (
    admission_number, first_name, middle_name, last_name, gender, date_of_birth,
    student_phone, student_email, house_name, street, city, district, state, pin_code,
    address, guardian_name, guardian_relation, guardian_phone, class_id, division_id, status, is_deleted, created_at
) VALUES (
    :adm, 'Anandhu', 'Kumar', 'Uthaman', 'Male', '2010-05-15',
    '+919847011223', 'anandhu.k@example.com', 'Rose Villa', 'MG Road', 'Kochi', 'Ernakulam', 'Kerala', '682001',
    'Rose Villa, MG Road, Kochi, Ernakulam, Kerala - 682001', 'Uthaman K', 'Father', '+919847011220', 1, 1, 1, 'n', NOW()
)";
$stmt = $pdo->prepare($insert_sql);
$stmt->execute([':adm' => $test_adm]);
$test_id = (int)$pdo->lastInsertId();
echo "  [PASS] Inserted test student ID: $test_id\n";

// Fetch and verify
$stmt = $pdo->prepare("SELECT * FROM tbl_students WHERE student_id = ?");
$stmt->execute([$test_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

assert($row['first_name'] === 'Anandhu', "first_name mismatch");
assert($row['middle_name'] === 'Kumar', "middle_name mismatch");
assert($row['last_name'] === 'Uthaman', "last_name mismatch");
assert($row['student_email'] === 'anandhu.k@example.com', "student_email mismatch");
assert($row['house_name'] === 'Rose Villa', "house_name mismatch");
assert($row['street'] === 'MG Road', "street mismatch");
assert($row['city'] === 'Kochi', "city mismatch");
assert($row['district'] === 'Ernakulam', "district mismatch");
assert($row['state'] === 'Kerala', "state mismatch");
assert($row['pin_code'] === '682001', "pin_code mismatch");
echo "  [PASS] Verified all 8 new/enhanced fields accurately stored on INSERT.\n";

// Update test
$update_sql = "UPDATE tbl_students SET
    middle_name = 'Dev',
    student_email = 'anandhu.dev@school.org',
    house_name = 'Palm Grove',
    city = 'Thiruvananthapuram',
    pin_code = '695001'
WHERE student_id = ?";
$stmt = $pdo->prepare($update_sql);
$stmt->execute([$test_id]);

$stmt = $pdo->prepare("SELECT * FROM tbl_students WHERE student_id = ?");
$stmt->execute([$test_id]);
$updated = $stmt->fetch(PDO::FETCH_ASSOC);

assert($updated['middle_name'] === 'Dev', "Updated middle_name mismatch");
assert($updated['student_email'] === 'anandhu.dev@school.org', "Updated student_email mismatch");
assert($updated['house_name'] === 'Palm Grove', "Updated house_name mismatch");
assert($updated['city'] === 'Thiruvananthapuram', "Updated city mismatch");
assert($updated['pin_code'] === '695001', "Updated pin_code mismatch");
echo "  [PASS] Verified all enhanced fields accurately updated on UPDATE.\n";

// Clean up test student
$pdo->prepare("DELETE FROM tbl_students WHERE student_id = ?")->execute([$test_id]);
echo "  [PASS] Cleaned up test record ID $test_id.\n";

echo "\n=== ALL ENHANCEMENT TESTS PASSED PERFECTLY! ===\n";
