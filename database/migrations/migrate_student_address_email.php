<?php
/**
 * Database Migration Script: Student Address & Student Email Fields
 * 
 * Safely creates a backup table and adds nullable/optional columns:
 * - student_email
 * - house_name
 * - street
 * - city
 * - district
 * - state
 * - pin_code
 * to tbl_students. Preserves all existing records.
 */

$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

echo "=======================================================\n";
echo "STARTING STUDENT ADDRESS & EMAIL SCHEMA MIGRATION\n";
echo "=======================================================\n\n";

// 1. Create Pre-Migration Backup
echo "[STEP 1] Creating Pre-Migration Backup Table...\n";
$mysqli->query("CREATE TABLE IF NOT EXISTS tbl_students_backup_name_address_email AS SELECT * FROM tbl_students");
$countBackup = $mysqli->query("SELECT COUNT(*) as c FROM tbl_students_backup_name_address_email")->fetch_assoc()['c'];
echo " - tbl_students_backup_name_address_email verified with {$countBackup} rows.\n\n";

// 2. Inspect Existing Columns
echo "[STEP 2] Inspecting tbl_students Columns...\n";
$existingCols = [];
$res = $mysqli->query("DESCRIBE tbl_students");
while ($row = $res->fetch_assoc()) {
    $existingCols[] = $row['Field'];
}

$columnsToAdd = [
    'student_email' => "VARCHAR(100) NULL DEFAULT NULL AFTER student_phone",
    'house_name'    => "VARCHAR(100) NULL DEFAULT NULL AFTER student_email",
    'street'        => "VARCHAR(100) NULL DEFAULT NULL AFTER house_name",
    'city'          => "VARCHAR(100) NULL DEFAULT NULL AFTER street",
    'district'      => "VARCHAR(100) NULL DEFAULT NULL AFTER city",
    'state'         => "VARCHAR(100) NULL DEFAULT NULL AFTER district",
    'pin_code'      => "VARCHAR(20) NULL DEFAULT NULL AFTER state",
];

// Note: middle_name already exists in tbl_students
if (!in_array('middle_name', $existingCols)) {
    $columnsToAdd['middle_name'] = "VARCHAR(50) NULL DEFAULT NULL AFTER first_name";
}

$addedCount = 0;
foreach ($columnsToAdd as $colName => $colDef) {
    if (!in_array($colName, $existingCols)) {
        $sql = "ALTER TABLE tbl_students ADD COLUMN {$colName} {$colDef}";
        if ($mysqli->query($sql)) {
            echo " - Successfully added column: {$colName}\n";
            $addedCount++;
        } else {
            echo " - Error adding {$colName}: " . $mysqli->error . "\n";
        }
    } else {
        echo " - Column already exists: {$colName}\n";
    }
}

echo "\n[STEP 3] Migration Completed. Total columns added: {$addedCount}\n";

// 3. Verify Active Student Rows
$countActive = $mysqli->query("SELECT COUNT(*) as c FROM tbl_students")->fetch_assoc()['c'];
echo " - Total active student records: {$countActive} (matches backup: " . ($countActive === $countBackup ? 'YES' : 'NO') . ")\n\n";

$mysqli->close();
