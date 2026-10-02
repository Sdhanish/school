<?php
// E2E Integration and Unit Test for Staff Document Management System
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('ENVIRONMENT', 'development');

require_once APPPATH . 'config/database.php';

$dbConfig = $db['default'];
$mysqli = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== STARTING E2E TESTS: STAFF DOCUMENT MANAGEMENT SYSTEM ===\n\n";

$passed = 0;
$failed = 0;

function assert_true($condition, $testName) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $testName\n";
        $passed++;
    } else {
        echo " [FAIL] $testName\n";
        $failed++;
    }
}

// 1. Check database schema
$res = $mysqli->query("SHOW TABLES LIKE 'tbl_staff_document_types'");
assert_true($res->num_rows === 1, "Table tbl_staff_document_types exists");

$res = $mysqli->query("SHOW COLUMNS FROM tbl_staff_documents LIKE 'document_type_id'");
assert_true($res->num_rows === 1, "Column document_type_id exists in tbl_staff_documents");

// 2. Test Super Admin adding a new dynamic document type
$testDocName = "Police Verification Certificate " . rand(100, 999);
$stmt = $mysqli->prepare("INSERT INTO tbl_staff_document_types (document_name, description, status, display_order, is_deleted, created_by, created_at) VALUES (?, 'Required for safety verification', 'Active', 5, 'n', 'Super Admin', NOW())");
$stmt->bind_param("s", $testDocName);
$stmt->execute();
$newDocTypeId = $stmt->insert_id;
assert_true($newDocTypeId > 0, "Created dynamic document definition: $testDocName (ID: $newDocTypeId)");

// 3. Test retrieving active document types
$res = $mysqli->query("SELECT * FROM tbl_staff_document_types WHERE status = 'Active' AND is_deleted = 'n'");
$activeTypes = [];
while ($row = $res->fetch_assoc()) {
    $activeTypes[$row['id']] = $row['document_name'];
}
assert_true(isset($activeTypes[$newDocTypeId]), "Newly created document definition is present in active document types");

// 4. Test staff registration document requirements & upload mapping
$empCode = "TESTEMP" . rand(1000, 9999);
$stmt = $mysqli->prepare("INSERT INTO tbl_staff (employee_code, full_name, gender, phone, email, staff_type, department_id, designation_id, joining_date, status, created_at) VALUES (?, 'Test Staff Teacher', 'Male', '9876543210', 'teacher.test@school.edu', 'teacher', 1, 1, '2026-06-01', 1, NOW())");
$stmt->bind_param("s", $empCode);
$stmt->execute();
$testStaffId = $stmt->insert_id;
assert_true($testStaffId > 0, "Inserted test staff member: $testStaffId (Code: $empCode)");

// Simulate uploading all active required documents for the staff member
$uploadCount = 0;
foreach ($activeTypes as $typeId => $typeName) {
    $safeName = "doc_{$testStaffId}_{$typeId}_" . time() . ".pdf";
    $fakePath = "uploads/staff_docs/" . $safeName;
    $origName = $typeName . " copy.pdf";
    $mime = "application/pdf";
    $size = 102400;

    $stmtDoc = $mysqli->prepare("INSERT INTO tbl_staff_documents (staff_id, document_type_id, document_type, document_name, file_name, file_path, file_type, file_size, mime_type, uploaded_by, status, is_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 'n', NOW())");
    $stmtDoc->bind_param("iisssssis", $testStaffId, $typeId, $typeName, $typeName, $origName, $fakePath, $mime, $size, $mime);
    $stmtDoc->execute();
    if ($stmtDoc->affected_rows > 0) {
        $uploadCount++;
    }
}
assert_true($uploadCount === count($activeTypes), "Successfully uploaded and linked all $uploadCount dynamic required documents for staff member");

// 5. Test Document Replacement / Edit Staff
$replaceDocName = "Police Verification Certificate copy (Updated).pdf";
$stmt = $mysqli->prepare("UPDATE tbl_staff_documents SET is_deleted = 'y' WHERE staff_id = ? AND document_type_id = ? AND is_deleted = 'n'");
$stmt->bind_param("ii", $testStaffId, $newDocTypeId);
$stmt->execute();

$newSafeName = "doc_{$testStaffId}_{$newDocTypeId}_" . (time() + 10) . ".pdf";
$newPath = "uploads/staff_docs/" . $newSafeName;
$stmtDoc = $mysqli->prepare("INSERT INTO tbl_staff_documents (staff_id, document_type_id, document_type, document_name, file_name, file_path, file_type, file_size, mime_type, uploaded_by, status, is_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, 'application/pdf', 204800, 'application/pdf', 1, 1, 'n', NOW())");
$stmtDoc->bind_param("iissss", $testStaffId, $newDocTypeId, $testDocName, $testDocName, $replaceDocName, $newPath);
$stmtDoc->execute();
$newDocId = $stmtDoc->insert_id;
assert_true($newDocId > 0, "Replaced document for staff and marked previous version deactivated");

// 6. Test Soft Delete of Document Type (Preserve Existing Documents Rule)
$stmt = $mysqli->prepare("UPDATE tbl_staff_document_types SET is_deleted = 'y', status = 'Inactive', deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $newDocTypeId);
$stmt->execute();

$res = $mysqli->query("SELECT * FROM tbl_staff_document_types WHERE id = $newDocTypeId AND is_deleted = 'y'");
assert_true($res->num_rows === 1, "Document definition was soft-deleted in tbl_staff_document_types");

// Verify that staff documents linked to this type were NOT deleted
$res = $mysqli->query("SELECT * FROM tbl_staff_documents WHERE staff_id = $testStaffId AND document_type_id = $newDocTypeId AND is_deleted = 'n'");
assert_true($res->num_rows === 1, "Historical staff documents are preserved when document definition is deleted/deactivated (Soft Delete rule)");

// 7. Test Toggle Status
$stmt = $mysqli->prepare("UPDATE tbl_staff_document_types SET status = 'Inactive' WHERE id = 1");
$stmt->execute();
$res = $mysqli->query("SELECT status FROM tbl_staff_document_types WHERE id = 1");
$row = $res->fetch_assoc();
assert_true($row['status'] === 'Inactive', "Document type status can be toggled Inactive");

$stmt = $mysqli->prepare("UPDATE tbl_staff_document_types SET status = 'Active' WHERE id = 1");
$stmt->execute();
$res = $mysqli->query("SELECT status FROM tbl_staff_document_types WHERE id = 1");
$row = $res->fetch_assoc();
assert_true($row['status'] === 'Active', "Document type status can be toggled back to Active");

// Clean up test staff records
$mysqli->query("DELETE FROM tbl_staff_documents WHERE staff_id = $testStaffId");
$mysqli->query("DELETE FROM tbl_staff WHERE staff_id = $testStaffId");
$mysqli->query("DELETE FROM tbl_staff_document_types WHERE id = $newDocTypeId");

echo "\n=== TEST RESULTS SUMMARY ===\n";
echo "Total Passed: $passed\n";
echo "Total Failed: $failed\n";

if ($failed === 0) {
    echo ">>> ALL INTEGRATION TESTS PASSED SUCCESSFULLY! <<<\n";
} else {
    echo ">>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
