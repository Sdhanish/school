<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require 'application/config/database.php';
$d = $db['default'];
$conn = mysqli_connect($d['hostname'], $d['username'], $d['password'], $d['database']);

$sql = file_get_contents('database/migration_section_to_division.sql');

// Split SQL queries safely
$queries = array_filter(array_map('trim', explode(';', $sql)), function($q) {
    // Remove comments
    $lines = explode("\n", $q);
    $clean_lines = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        if (strpos($trim, '--') === 0 || empty($trim)) continue;
        $clean_lines[] = $line;
    }
    $clean_q = trim(implode("\n", $clean_lines));
    return !empty($clean_q);
});

$success_count = 0;
$errors = [];

foreach ($queries as $i => $q) {
    // Re-clean lines
    $lines = explode("\n", $q);
    $clean_lines = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        if (strpos($trim, '--') === 0) continue;
        $clean_lines[] = $line;
    }
    $exec_q = trim(implode("\n", $clean_lines));
    if (empty($exec_q)) continue;

    $res = mysqli_query($conn, $exec_q);
    if ($res) {
        $success_count++;
        echo "[OK] Step " . ($i+1) . ": " . substr(str_replace("\n", " ", $exec_q), 0, 65) . "...\n";
    } else {
        $err = mysqli_error($conn);
        $errors[] = "Query failed: $exec_q | Error: $err";
        echo "[ERROR] Step " . ($i+1) . ": $err\n";
    }
}

echo "\nMigration finished. Executed: $success_count queries. Errors: " . count($errors) . "\n";
if (!empty($errors)) {
    print_r($errors);
    exit(1);
} else {
    echo "SUCCESS: Database migration completed cleanly!\n";
    exit(0);
}
unlink(__FILE__);
