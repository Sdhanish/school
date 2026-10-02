<?php
define('BASEPATH', '1');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../application/config/database.php';
$d = $db['default'];
$mysqli = new mysqli($d['hostname'], $d['username'], $d['password'], $d['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$check = $mysqli->query("SHOW COLUMNS FROM `tbl_staff` LIKE 'academic_group_id'");
if ($check->num_rows > 0) {
    echo "Column academic_group_id already exists in tbl_staff.\n";
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/../database/migration_teacher_group.sql');
if ($mysqli->multi_query($sql)) {
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Successfully added academic_group_id column and foreign key to tbl_staff!\n";
} else {
    echo "Migration failed: " . $mysqli->error . "\n";
    exit(1);
}
