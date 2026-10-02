<?php
define('BASEPATH', '1');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../../application/config/database.php';
$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

$res = $mysqli->query("SELECT COUNT(*) as total, COUNT(academic_group_id) as with_group FROM tbl_staff");
$row = $res->fetch_assoc();
echo "Total staff: {$row['total']}, With group: {$row['with_group']}\n";

$col = $mysqli->query("SHOW COLUMNS FROM tbl_staff LIKE 'academic_group_id'")->fetch_assoc();
echo "Column info: " . json_encode($col) . "\n";
