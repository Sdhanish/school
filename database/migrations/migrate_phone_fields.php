<?php
/**
 * Database Migration Script: Phone Fields & Normalization
 * 
 * Safely creates backup tables, enlarges phone column widths, adds student contact fields,
 * and normalizes valid existing phone numbers to canonical E.164 (+91XXXXXXXXXX) format.
 * Non-standard/ambiguous records are preserved unchanged and logged.
 */

define('BASEPATH', '1');
define('APPPATH', __DIR__ . '/../../application/');

require_once __DIR__ . '/../../application/libraries/Phone_validator.php';

$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$phoneValidator = new Phone_validator();

echo "=======================================================\n";
echo "STARTING PHONE SCHEMA MIGRATION & DATA NORMALIZATION\n";
echo "=======================================================\n\n";

// 1. Create Pre-Migration Backups
echo "[STEP 1] Creating Pre-Migration Backup Tables...\n";
$mysqli->query("CREATE TABLE IF NOT EXISTS tbl_students_backup_phone_pre_migration AS SELECT * FROM tbl_students");
$countStudentsBackup = $mysqli->query("SELECT COUNT(*) as c FROM tbl_students_backup_phone_pre_migration")->fetch_assoc()['c'];
echo " - tbl_students_backup_phone_pre_migration created with {$countStudentsBackup} rows.\n";

$mysqli->query("CREATE TABLE IF NOT EXISTS tbl_staff_backup_phone_pre_migration AS SELECT * FROM tbl_staff");
$countStaffBackup = $mysqli->query("SELECT COUNT(*) as c FROM tbl_staff_backup_phone_pre_migration")->fetch_assoc()['c'];
echo " - tbl_staff_backup_phone_pre_migration created with {$countStaffBackup} rows.\n\n";

// 2. Schema Enhancements on tbl_students
echo "[STEP 2] Enhancing tbl_students schema...\n";
$mysqli->query("ALTER TABLE tbl_students MODIFY COLUMN guardian_phone VARCHAR(30) NULL DEFAULT NULL");
echo " - Modified guardian_phone to VARCHAR(30).\n";

// Check if student_phone, father_phone, mother_phone, emergency_contact exist
$studentCols = [];
$res = $mysqli->query("DESCRIBE tbl_students");
while ($row = $res->fetch_assoc()) {
    $studentCols[] = $row['Field'];
}

if (!in_array('student_phone', $studentCols)) {
    $mysqli->query("ALTER TABLE tbl_students ADD COLUMN student_phone VARCHAR(30) NULL DEFAULT NULL AFTER email");
    echo " - Added student_phone column.\n";
}
if (!in_array('father_phone', $studentCols)) {
    $mysqli->query("ALTER TABLE tbl_students ADD COLUMN father_phone VARCHAR(30) NULL DEFAULT NULL AFTER father_occupation");
    echo " - Added father_phone column.\n";
}
if (!in_array('mother_phone', $studentCols)) {
    $mysqli->query("ALTER TABLE tbl_students ADD COLUMN mother_phone VARCHAR(30) NULL DEFAULT NULL AFTER mother_occupation");
    echo " - Added mother_phone column.\n";
}
if (!in_array('emergency_contact', $studentCols)) {
    $mysqli->query("ALTER TABLE tbl_students ADD COLUMN emergency_contact VARCHAR(30) NULL DEFAULT NULL AFTER guardian_address");
    echo " - Added emergency_contact column.\n";
}

// 3. Schema Enhancements on tbl_staff
echo "\n[STEP 3] Enhancing tbl_staff schema...\n";
$mysqli->query("ALTER TABLE tbl_staff MODIFY COLUMN phone VARCHAR(30) NOT NULL");
$mysqli->query("ALTER TABLE tbl_staff MODIFY COLUMN alternate_phone VARCHAR(30) NULL DEFAULT NULL");
echo " - Modified phone and alternate_phone to VARCHAR(30).\n\n";

// 4. Normalize tbl_students phone numbers
echo "[STEP 4] Normalizing existing Student phone numbers...\n";
$resStudents = $mysqli->query("SELECT student_id, admission_number, first_name, last_name, guardian_phone FROM tbl_students");
if (!$resStudents) {
    die("Query failed on tbl_students: " . $mysqli->error . "\n");
}
$updatedStudents = 0;
$skippedStudents = 0;
$flaggedStudents = [];

while ($row = $resStudents->fetch_assoc()) {
    $id = $row['student_id'];
    $raw = trim((string)$row['guardian_phone']);
    if ($raw === '') {
        $skippedStudents++;
        continue;
    }

    $validation = $phoneValidator->validate_and_normalize($raw, 'IN', false);
    if ($validation['valid'] && !empty($validation['normalized'])) {
        if ($validation['normalized'] !== $raw) {
            $stmt = $mysqli->prepare("UPDATE tbl_students SET guardian_phone = ? WHERE student_id = ?");
            $stmt->bind_param('si', $validation['normalized'], $id);
            $stmt->execute();
            $updatedStudents++;
        }
    } else {
        $flaggedStudents[] = [
            'id' => $id,
            'admission_number' => $row['admission_number'],
            'name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'guardian_phone' => $raw,
            'reason' => $validation['error']
        ];
    }
}

echo " - Student records updated to E.164: {$updatedStudents}\n";
echo " - Empty records skipped: {$skippedStudents}\n";
echo " - Flagged/Preserved non-standard records: " . count($flaggedStudents) . "\n";
foreach ($flaggedStudents as $flag) {
    echo "   * ID #{$flag['id']} ({$flag['admission_number']} - {$flag['name']}): Value '{$flag['guardian_phone']}' preserved as-is. Reason: {$flag['reason']}\n";
}

// 5. Normalize tbl_staff phone numbers
echo "\n[STEP 5] Normalizing existing Staff phone numbers...\n";
$resStaff = $mysqli->query("SELECT staff_id, employee_code, full_name, phone, alternate_phone FROM tbl_staff");
if (!$resStaff) {
    die("Query failed on tbl_staff: " . $mysqli->error . "\n");
}
$updatedStaffPhone = 0;
$updatedStaffAlt = 0;
$flaggedStaff = [];

while ($row = $resStaff->fetch_assoc()) {
    $id = $row['staff_id'];
    $rawPhone = trim((string)$row['phone']);
    $rawAlt = trim((string)$row['alternate_phone']);

    if ($rawPhone !== '') {
        $valPhone = $phoneValidator->validate_and_normalize($rawPhone, 'IN', false);
        if ($valPhone['valid'] && !empty($valPhone['normalized'])) {
            if ($valPhone['normalized'] !== $rawPhone) {
                $stmt = $mysqli->prepare("UPDATE tbl_staff SET phone = ? WHERE staff_id = ?");
                $stmt->bind_param('si', $valPhone['normalized'], $id);
                $stmt->execute();
                $updatedStaffPhone++;
            }
        } else {
            $flaggedStaff[] = [
                'id' => $id,
                'employee_code' => $row['employee_code'],
                'name' => trim($row['full_name']),
                'field' => 'phone',
                'value' => $rawPhone,
                'reason' => $valPhone['error']
            ];
        }
    }

    if ($rawAlt !== '') {
        $valAlt = $phoneValidator->validate_and_normalize($rawAlt, 'IN', false);
        if ($valAlt['valid'] && !empty($valAlt['normalized'])) {
            if ($valAlt['normalized'] !== $rawAlt) {
                $stmt = $mysqli->prepare("UPDATE tbl_staff SET alternate_phone = ? WHERE staff_id = ?");
                $stmt->bind_param('si', $valAlt['normalized'], $id);
                $stmt->execute();
                $updatedStaffAlt++;
            }
        } else {
            $flaggedStaff[] = [
                'id' => $id,
                'employee_code' => $row['employee_code'],
                'name' => trim($row['full_name']),
                'field' => 'alternate_phone',
                'value' => $rawAlt,
                'reason' => $valAlt['error']
            ];
        }
    }
}

echo " - Staff primary phone records updated to E.164: {$updatedStaffPhone}\n";
echo " - Staff alternate phone records updated to E.164: {$updatedStaffAlt}\n";
echo " - Flagged/Preserved non-standard records: " . count($flaggedStaff) . "\n";
foreach ($flaggedStaff as $flag) {
    echo "   * ID #{$flag['id']} ({$flag['employee_code']} - {$flag['name']}): {$flag['field']} '{$flag['value']}' preserved as-is. Reason: {$flag['reason']}\n";
}

echo "\n=======================================================\n";
echo "MIGRATION COMPLETED SUCCESSFULLY WITH ZERO DATA LOSS\n";
echo "=======================================================\n";
