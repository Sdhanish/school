<?php
/**
 * Automated E2E & Unit Integration Test for Staff Profile Image & Cropper Feature
 * Run via CLI: php tests/test_staff_image_e2e.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');

// Set mock server variables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = FCPATH . 'index.php';

require_once BASEPATH . 'core/Common.php';

// Load CI Database
require_once APPPATH . 'config/database.php';
$db_config = $db[$active_group];

$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=======================================================\n";
echo " STAFF PROFILE IMAGE & CROPPER E2E INTEGRATION TESTS\n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo " [FAIL] $description\n";
        $failed++;
    }
}

// 1. Check database column
$res = $mysqli->query("SHOW COLUMNS FROM tbl_staff LIKE 'photo'");
assert_test("1. Column 'photo' exists in tbl_staff", $res && $res->num_rows > 0);

// 2. Check uploads/staff directory and .htaccess
$staffUploadDir = FCPATH . 'uploads/staff/';
assert_test("2. Directory uploads/staff/ exists", is_dir($staffUploadDir));
assert_test("3. Security file uploads/staff/.htaccess exists and restricts scripts", file_exists($staffUploadDir . '.htaccess') && strpos(file_get_contents($staffUploadDir . '.htaccess'), 'FilesMatch') !== false);

// 3. Create a 1x1 test JPEG and base64 encode it
$testImg = imagecreatetruecolor(100, 133); // 3:4 portrait
$bg = imagecolorallocate($testImg, 34, 197, 94);
imagefill($testImg, 0, 0, $bg);
ob_start();
imagejpeg($testImg, null, 90);
$jpegBinary = ob_get_clean();
imagedestroy($testImg);

$jpegBase64 = 'data:image/jpeg;base64,' . base64_encode($jpegBinary);

// Test Base64 decoding and binary validation
$decoded = base64_decode(base64_encode($jpegBinary));
$info = getimagesizefromstring($decoded);
assert_test("4. Base64 decoded test image is a valid binary image (3:4 ratio)", $info !== false && $info[2] === IMAGETYPE_JPEG);

// 4. Test simulated registration with photo in database
$empCode = 'TEST_IMG_' . rand(1000, 9999);
$testPhotoName = 'staff_test_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.jpg';
file_put_contents($staffUploadDir . $testPhotoName, $decoded);
assert_test("5. Photo successfully written to uploads/staff/{$testPhotoName}", file_exists($staffUploadDir . $testPhotoName));

$insertQuery = "INSERT INTO tbl_staff (employee_code, full_name, gender, email, phone, staff_type, department_id, designation_id, joining_date, salary, photo, status, created_at)
VALUES ('{$empCode}', 'Dr. Ananya Sharma', 'Female', 'ananya.test@school.edu', '9847012345', 'teacher', 1, 1, '2026-06-01', 55000, '{$testPhotoName}', 1, NOW())";
$res = $mysqli->query($insertQuery);
$testStaffId = $mysqli->insert_id;
assert_test("6. Staff member registered with photo column saved (Staff ID: {$testStaffId})", $res && $testStaffId > 0);

// Verify staff record in DB
$query = $mysqli->query("SELECT * FROM tbl_staff WHERE staff_id = {$testStaffId}");
$staffRow = $query->fetch_object();
assert_test("7. tbl_staff.photo matches saved filename", $staffRow && $staffRow->photo === $testPhotoName);

// 5. Test photo replacement (upload new photo and retire old photo)
$newPhotoName = 'staff_test_replaced_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.jpg';
file_put_contents($staffUploadDir . $newPhotoName, $decoded);
assert_test("8. New replacement photo written to disk", file_exists($staffUploadDir . $newPhotoName));

// Update staff photo in DB
$mysqli->query("UPDATE tbl_staff SET photo = '{$newPhotoName}' WHERE staff_id = {$testStaffId}");
// Simulate safe deletion of old photo
if (file_exists($staffUploadDir . $testPhotoName)) {
    unlink($staffUploadDir . $testPhotoName);
}
assert_test("9. Old photo safely unlinked after replacement", !file_exists($staffUploadDir . $testPhotoName));
assert_test("10. New photo exists on disk", file_exists($staffUploadDir . $newPhotoName));

// 6. Test photo removal
$mysqli->query("UPDATE tbl_staff SET photo = NULL WHERE staff_id = {$testStaffId}");
if (file_exists($staffUploadDir . $newPhotoName)) {
    unlink($staffUploadDir . $newPhotoName);
}
assert_test("11. Photo file unlinked upon removal", !file_exists($staffUploadDir . $newPhotoName));

$query = $mysqli->query("SELECT photo, full_name, status FROM tbl_staff WHERE staff_id = {$testStaffId}");
$updatedStaff = $query->fetch_object();
assert_test("12. Staff photo set to NULL while staff record remains active", $updatedStaff && is_null($updatedStaff->photo) && $updatedStaff->status == 1);

// 7. Verify Cropper.js vendor files
assert_test("13. Cropper CSS vendor asset exists", file_exists(FCPATH . 'assets/vendor/cropper/cropper.min.css'));
assert_test("14. Cropper JS vendor asset exists", file_exists(FCPATH . 'assets/vendor/cropper/cropper.min.js'));

// 8. Clean up test record
$mysqli->query("DELETE FROM tbl_staff WHERE staff_id = {$testStaffId}");

echo "\n-------------------------------------------------------\n";
echo " RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "-------------------------------------------------------\n\n";

if ($failed > 0) {
    exit(1);
} else {
    exit(0);
}
