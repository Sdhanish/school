<?php
/**
 * Migration: Add design_config column to tbl_document_designs if not exists.
 * Run via CLI: php application/migrations/migrate_id_card_design_config.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(dirname(__DIR__)) . '/system/');
define('APPPATH', dirname(dirname(__DIR__)) . '/application/');

require_once APPPATH . 'config/database.php';
$c = $db[$active_group];

$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error . "\n");
}

$check = $mysqli->query("SHOW COLUMNS FROM tbl_document_designs LIKE 'design_config'");
if ($check && $check->num_rows === 0) {
    $alt = $mysqli->query("ALTER TABLE tbl_document_designs ADD COLUMN design_config TEXT NULL DEFAULT NULL AFTER footer_image");
    if ($alt) {
        echo "Successfully added design_config column to tbl_document_designs.\n";
    } else {
        echo "Error adding column: " . $mysqli->error . "\n";
    }
} else {
    echo "design_config column already exists in tbl_document_designs.\n";
}
