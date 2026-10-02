<?php
/**
 * Automated Test Suite for Centralized Phone Validation & Country-Code System
 */

define('BASEPATH', '1');
define('APPPATH', __DIR__ . '/../application/');

require_once __DIR__ . '/../application/libraries/Phone_validator.php';

$v = new Phone_validator();
$passed = 0;
$failed = 0;

function assertTest($description, $condition, $extra = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] " . $description . "\n";
        $passed++;
    } else {
        echo " [FAIL] " . $description . ($extra ? " (" . $extra . ")" : "") . "\n";
        $failed++;
    }
}

echo "========================================================\n";
echo "RUNNING CENTRALIZED PHONE VALIDATION SYSTEM TESTS\n";
echo "========================================================\n\n";

// ── 1. INDIA MOBILE VALIDATION TESTS ─────────────────────────────
echo "[SECTION 1] India (+91) Mobile Validation Rules\n";

$validIndian = [
    '9847011223' => '+919847011223',
    '+91 98470 11223' => '+919847011223',
    '09847011223' => '+919847011223',
    '9876543210' => '+919876543210',
    '7012345678' => '+917012345678',
    '8123456789' => '+918123456789',
    '6123456789' => '+916123456789',
];

foreach ($validIndian as $input => $expected) {
    $res = $v->validate_and_normalize($input, 'IN', true);
    assertTest("Valid India number '{$input}' -> '{$expected}'", $res['valid'] && $res['normalized'] === $expected, $res['error'] ?? '');
}

$invalidIndian = [
    '1234567890' => 'Indian mobile number must start with 6, 7, 8, or 9.',
    '5123456789' => 'Indian mobile number must start with 6, 7, 8, or 9.',
    '984701122'  => 'Indian mobile number must be exactly 10 digits.',
    '98470112234'=> 'Indian mobile number must be exactly 10 digits.',
    '0000000000' => 'Invalid phone number: repeated digits are not allowed.',
    '1111111111' => 'Invalid phone number: repeated digits are not allowed.',
    '9999999999' => 'Invalid phone number: repeated digits are not allowed.',
];

foreach ($invalidIndian as $input => $reason) {
    $res = $v->validate_and_normalize($input, 'IN', true);
    assertTest("Invalid India number '{$input}' properly rejected", !$res['valid'], "Got normalized: " . ($res['normalized'] ?? 'none'));
}

// ── 2. INTERNATIONAL NUMBER VALIDATION TESTS ──────────────────────
echo "\n[SECTION 2] International Phone Validation Rules\n";

$internationalCases = [
    ['num' => '2025550123', 'country' => 'US', 'expected' => '+12025550123'],
    ['num' => '07400123456', 'country' => 'GB', 'expected' => '+447400123456'],
    ['num' => '0501234567', 'country' => 'AE', 'expected' => '+971501234567'],
    ['num' => '0512345678', 'country' => 'SA', 'expected' => '+966512345678'],
];

foreach ($internationalCases as $c) {
    $res = $v->validate_and_normalize($c['num'], $c['country'], true);
    assertTest("Valid {$c['country']} number '{$c['num']}' -> '{$c['expected']}'", $res['valid'] && $res['normalized'] === $c['expected'], $res['error'] ?? '');
}

// ── 3. OPTIONAL FIELDS WHEN EMPTY ────────────────────────────────
echo "\n[SECTION 3] Optional Field Handling\n";
$optEmpty = $v->validate_and_normalize('', 'IN', false);
assertTest("Optional empty field returns valid with null normalized", $optEmpty['valid'] && $optEmpty['normalized'] === null);

$reqEmpty = $v->validate_and_normalize('', 'IN', true);
assertTest("Required empty field returns invalid", !$reqEmpty['valid'] && $reqEmpty['error'] !== null);

// ── 4. STORED NUMBER PARSING TESTS (FOR EDIT FORMS) ──────────────
echo "\n[SECTION 4] Stored E.164 Number Parsing for Edit Forms\n";

$parseCases = [
    '+919847011223' => ['country' => 'IN', 'calling_code' => '+91', 'national' => '9847011223'],
    '+971501234567' => ['country' => 'AE', 'calling_code' => '+971', 'national' => '501234567'],
    '+447400123456' => ['country' => 'GB', 'calling_code' => '+44', 'national' => '7400123456'],
    '9847011223'    => ['country' => 'IN', 'calling_code' => '+91', 'national' => '9847011223'],
];

foreach ($parseCases as $stored => $exp) {
    $p = $v->parse_number($stored);
    $ok = ($p['country'] === $exp['country'] && $p['calling_code'] === $exp['calling_code'] && $p['national_number'] === $exp['national']);
    assertTest("Parse '{$stored}' -> {$exp['country']} ({$exp['calling_code']}) {$exp['national']}", $ok, "Got {$p['country']} {$p['calling_code']} {$p['national_number']}");
}

// ── 5. DATABASE INTEGRITY & DATA PRESERVATION CHECK ──────────────
echo "\n[SECTION 5] Database Integrity & Data Loss Prevention\n";

$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    echo " [FAIL] Database connection failed: " . $mysqli->connect_error . "\n";
    $failed++;
} else {
    // Check backup table exists and row count matches active table
    $resBackup = $mysqli->query("SELECT COUNT(*) as c FROM tbl_students_backup_phone_pre_migration");
    $countBackup = $resBackup ? (int)$resBackup->fetch_assoc()['c'] : 0;
    
    $resActive = $mysqli->query("SELECT COUNT(*) as c FROM tbl_students");
    $countActive = $resActive ? (int)$resActive->fetch_assoc()['c'] : 0;

    assertTest("Student row count matches backup table ({$countActive} rows)", $countActive > 0 && $countActive === $countBackup);

    // Check staff row count matches backup table
    $resStaffBackup = $mysqli->query("SELECT COUNT(*) as c FROM tbl_staff_backup_phone_pre_migration");
    $countStaffBackup = $resStaffBackup ? (int)$resStaffBackup->fetch_assoc()['c'] : 0;

    $resStaffActive = $mysqli->query("SELECT COUNT(*) as c FROM tbl_staff");
    $countStaffActive = $resStaffActive ? (int)$resStaffActive->fetch_assoc()['c'] : 0;

    assertTest("Staff row count matches backup table ({$countStaffActive} rows)", $countStaffActive > 0 && $countStaffActive === $countStaffBackup);

    // Verify non-standard legacy student records #116 and #117 are safely preserved
    $res116 = $mysqli->query("SELECT guardian_phone FROM tbl_students WHERE student_id = 116");
    $phone116 = $res116 ? $res116->fetch_assoc()['guardian_phone'] : null;
    assertTest("Student #116 preserved unchanged ('{$phone116}')", $phone116 === '+91 00000 00000');

    $res117 = $mysqli->query("SELECT guardian_phone FROM tbl_students WHERE student_id = 117");
    $phone117 = $res117 ? $res117->fetch_assoc()['guardian_phone'] : null;
    assertTest("Student #117 preserved unchanged ('{$phone117}')", $phone117 === '5665566556');

    // Verify columns exist on tbl_students
    $cols = [];
    $resCols = $mysqli->query("DESCRIBE tbl_students");
    while ($r = $resCols->fetch_assoc()) { $cols[] = $r['Field']; }

    assertTest("student_phone column exists", in_array('student_phone', $cols));
    assertTest("father_phone column exists", in_array('father_phone', $cols));
    assertTest("mother_phone column exists", in_array('mother_phone', $cols));
    assertTest("emergency_contact column exists", in_array('emergency_contact', $cols));
}

// ── 6. STATIC FACADE TESTS ─────────────────────────────────────────
echo "\n[SECTION 6] Phone_validator Static Facade Methods\n";
$staticNorm = Phone_validator::normalize('9847011223', 'IN', true);
assertTest("Phone_validator::normalize('9847011223') -> '+919847011223'", $staticNorm === '+919847011223');

$staticValid = Phone_validator::validate('9847011223', 'IN', true);
assertTest("Phone_validator::validate('9847011223') -> valid", $staticValid['valid'] === true);

$staticParse = Phone_validator::parse('+971501234567');
assertTest("Phone_validator::parse('+971501234567') -> AE", $staticParse['country'] === 'AE');

echo "\n========================================================\n";
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

exit($failed > 0 ? 1 : 0);
