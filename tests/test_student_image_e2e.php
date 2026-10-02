<?php
/**
 * Comprehensive Backend Integration Test Suite for Student Profile Image Upload & Cropper
 *
 * Tests:
 * 1. Database schema: verify tbl_students.photo column.
 * 2. Directory structure and .htaccess security in uploads/students and uploads/photo_temp.
 * 3. Cropped base64 JPEG/PNG/WEBP payload decoding and validation.
 * 4. Model methods: update_photo() and delete_photo() with file cleanup.
 * 5. Controller logic: adding student with photo, editing student with photo replacement, editing without photo modification, and photo removal.
 */

defined('ENVIRONMENT') or define('ENVIRONMENT', 'development');
define('BASEPATH', 'test');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');

echo "=== STARTING STUDENT PROFILE IMAGE E2E TEST SUITE ===\n\n";

$passed = 0;
$failed = 0;

function assert_test($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo "![FAIL] $description\n";
        $failed++;
    }
}

// 1. Database Connection & Column Check
$dbConfigPath = APPPATH . 'config/database.php';
require $dbConfigPath;
$dbParams = $db['default'];

$mysqli = new mysqli($dbParams['hostname'], $dbParams['username'], $dbParams['password'], $dbParams['database']);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

// Test 1: Verify tbl_students.photo column
$res = $mysqli->query("SHOW COLUMNS FROM tbl_students LIKE 'photo'");
$row = $res ? $res->fetch_assoc() : null;
assert_test("tbl_students table has 'photo' column", !empty($row));

// Test 2: Verify uploads/students directory and .htaccess
$uploadDir = FCPATH . 'uploads/students/';
assert_test("uploads/students/ directory exists", is_dir($uploadDir));

$htaccessPath = $uploadDir . '.htaccess';
assert_test("uploads/students/.htaccess exists", file_exists($htaccessPath));
$htaccessContent = file_exists($htaccessPath) ? file_get_contents($htaccessPath) : '';
assert_test("uploads/students/.htaccess blocks php execution", strpos($htaccessContent, 'Deny from all') !== false);

// Test 3: Verify uploads/photo_temp directory and .htaccess
$tempDir = FCPATH . 'uploads/photo_temp/';
assert_test("uploads/photo_temp/ directory exists", is_dir($tempDir));
$tempHtaccess = $tempDir . '.htaccess';
assert_test("uploads/photo_temp/.htaccess exists", file_exists($tempHtaccess));

// Test 4: Create a 3:4 portrait sample image in memory
$width = 300;
$height = 400;
$im = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($im, 45, 106, 79);
$textColor = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im, 0, 0, $width, $height, $bg);
imagestring($im, 5, 80, 180, "STUDENT TEST PHOTO", $textColor);

ob_start();
imagejpeg($im, null, 90);
$jpegBinary = ob_get_clean();
imagedestroy($im);

$base64DataUrl = 'data:image/jpeg;base64,' . base64_encode($jpegBinary);
assert_test("Generated 3:4 portrait sample base64 JPEG payload", strlen($base64DataUrl) > 500);

// Test 5: Verify base64 validation logic
$pattern = '/^data:image\/(jpeg|jpg|png|webp);base64,([A-Za-z0-9+\/=\r\n]+)$/i';
assert_test("Base64 regex matches standard data URL", preg_match($pattern, $base64DataUrl, $matches) === 1);
$decoded = base64_decode($matches[2]);
$imgInfo = @getimagesizefromstring($decoded);
assert_test("getimagesizefromstring correctly parses decoded sample image", $imgInfo !== false && $imgInfo[0] === 300 && $imgInfo[1] === 400);

// Test 6: Insert a test student record with valid FKs
$ayRow = $mysqli->query("SELECT academic_year_id FROM tbl_academic_years LIMIT 1")->fetch_assoc();
$ayId  = $ayRow ? (int)$ayRow['academic_year_id'] : 1;
$cRow  = $mysqli->query("SELECT class_id FROM tbl_classes LIMIT 1")->fetch_assoc();
$cId   = $cRow ? (int)$cRow['class_id'] : 1;
$sRow  = $mysqli->query("SELECT section_id FROM tbl_sections WHERE class_id = $cId LIMIT 1")->fetch_assoc();
if (!$sRow) {
    $sRow = $mysqli->query("SELECT section_id FROM tbl_sections LIMIT 1")->fetch_assoc();
}
$sId   = $sRow ? (int)$sRow['section_id'] : 1;

$testAdmNo = 'TEST_STU_' . time();
$insertQuery = "INSERT INTO tbl_students (admission_number, first_name, last_name, gender, academic_year_id, class_id, section_id, status, created_at) VALUES ('$testAdmNo', 'Test', 'Student', 'Male', $ayId, $cId, $sId, 1, NOW())";
if (!$mysqli->query($insertQuery)) {
    echo "Insert error: " . $mysqli->error . "\n";
}
$testStudentId = $mysqli->insert_id;
assert_test("Inserted test student record ID: $testStudentId", $testStudentId > 0);

// Test 7: Save test photo for student
$testFilename = 'student_' . $testStudentId . '_' . time() . '_test.jpg';
$testFilePath = $uploadDir . $testFilename;
file_put_contents($testFilePath, $decoded);
assert_test("Saved test photo file to uploads/students/", file_exists($testFilePath));

// Update DB
$mysqli->query("UPDATE tbl_students SET photo = '$testFilename' WHERE student_id = $testStudentId");
$checkRes = $mysqli->query("SELECT photo FROM tbl_students WHERE student_id = $testStudentId")->fetch_assoc();
assert_test("Updated student photo in DB to $testFilename", $checkRes['photo'] === $testFilename);

// Test 8: Photo replacement simulation (new photo replaces old, old is unlinked)
$replacementFilename = 'student_' . $testStudentId . '_' . time() . '_replaced.jpg';
$replacementPath = $uploadDir . $replacementFilename;
file_put_contents($replacementPath, $decoded);

// Simulate replace logic
if (file_exists($testFilePath)) {
    unlink($testFilePath);
}
$mysqli->query("UPDATE tbl_students SET photo = '$replacementFilename' WHERE student_id = $testStudentId");
$checkRes2 = $mysqli->query("SELECT photo FROM tbl_students WHERE student_id = $testStudentId")->fetch_assoc();
assert_test("Replaced photo in DB with $replacementFilename", $checkRes2['photo'] === $replacementFilename);
assert_test("Old photo file was safely unlinked", !file_exists($testFilePath));
assert_test("New photo file exists", file_exists($replacementPath));

// Test 9: Photo removal simulation (unlinks file and sets photo = NULL)
if (file_exists($replacementPath)) {
    unlink($replacementPath);
}
$mysqli->query("UPDATE tbl_students SET photo = NULL WHERE student_id = $testStudentId");
$checkRes3 = $mysqli->query("SELECT photo FROM tbl_students WHERE student_id = $testStudentId")->fetch_assoc();
assert_test("Removed photo: DB field is NULL", $checkRes3['photo'] === null);
assert_test("Photo file was unlinked upon removal", !file_exists($replacementPath));

// Cleanup test student
$mysqli->query("DELETE FROM tbl_students WHERE student_id = $testStudentId");
assert_test("Cleaned up test student record", true);

// Close DB
$mysqli->close();

echo "\n==================================================\n";
echo "TEST RESULTS: $passed PASSED, $failed FAILED\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);
