<?php
/**
 * Backend Data Integrity & DOB Verification Script
 */
$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("Connect error: " . $mysqli->connect_error);
}

echo "=== 1. Checking Database Schema for date_of_birth ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_students LIKE 'date_of_birth'");
$col = $res->fetch_assoc();
echo "Column Name: " . $col['Field'] . "\n";
echo "Data Type: " . $col['Type'] . "\n";
echo "Null Allowed: " . $col['Null'] . "\n";
echo "Default Value: " . ($col['Default'] === NULL ? 'NULL' : var_export($col['Default'], true)) . "\n";

echo "\n=== 2. Checking Existing Student Records (Integrity Check) ===\n";
$res = $mysqli->query("SELECT COUNT(*) as total, COUNT(date_of_birth) as with_dob FROM tbl_students");
$counts = $res->fetch_assoc();
echo "Total Students: " . $counts['total'] . "\n";
echo "Students with DOB: " . $counts['with_dob'] . "\n";

$res = $mysqli->query("SELECT student_id, first_name, date_of_birth FROM tbl_students WHERE student_id IN (1, 2, 46, 117) ORDER BY student_id ASC");
while ($row = $res->fetch_assoc()) {
    echo "Student ID {$row['student_id']}: {$row['first_name']} => DOB: {$row['date_of_birth']}\n";
}

echo "\n=== 3. Verifying View Source Directly in File ===\n";
$view_content = file_get_contents(__DIR__ . '/../application/views/pages/students/add.php');
if (strpos($view_content, "id=\"date_of_birth\"") !== false) {
    if (strpos($view_content, "value=\"<?php echo wval(\$sd, 'date_of_birth'); ?>\"") !== false) {
        echo "SUCCESS: add.php uses wval(\$sd, 'date_of_birth') without hardcoded default fallback!\n";
    } else {
        echo "FAILURE: add.php does not have correct wval for DOB!\n";
        exit(1);
    }
} else {
    echo "FAILURE: Could not find id=\"date_of_birth\" in add.php\n";
    exit(1);
}

echo "\n=== 4. Verifying Controller Step 1 Default ===\n";
$controller_content = file_get_contents(__DIR__ . '/../application/controllers/Students.php');
if (strpos($controller_content, "'date_of_birth'      => \$this->input->post('date_of_birth',    TRUE) ?: ''") !== false) {
    echo "SUCCESS: wizard_step1 stores empty string when DOB is not provided.\n";
} else {
    echo "FAILURE: wizard_step1 has unexpected default.\n";
    exit(1);
}

echo "\n=== 5. Verifying Edit Student View Binding ===\n";
$edit_content = file_get_contents(__DIR__ . '/../application/views/pages/students/edit.php');
if (strpos($edit_content, 'id="date_of_birth"') !== false && strpos($edit_content, '$student->date_of_birth') !== false) {
    echo "SUCCESS: edit.php correctly binds to \$student->date_of_birth!\n";
} else {
    echo "FAILURE: edit.php does not bind to \$student->date_of_birth!\n";
    exit(1);
}

echo "\nAll backend code integrity checks PASSED.\n";
