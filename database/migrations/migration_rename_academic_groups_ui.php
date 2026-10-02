<?php
/**
 * Database Migration: Rename Academic Groups UI terminology in Menu and Permissions
 * Route: php database/migrations/migration_rename_academic_groups_ui.php
 */
define('BASEPATH', '1');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../../application/config/database.php';
$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$mysqli->query("UPDATE tbl_menu_items SET menu_name = 'Department / Groups' WHERE menu_key = 'academic-groups'");
echo "Updated tbl_menu_items: " . $mysqli->affected_rows . " rows.\n";

$mysqli->query("UPDATE tbl_permissions SET permission_name = 'View Department / Groups', description = 'View Department / Groups listing and details' WHERE permission_key = 'academic_groups.view'");
$mysqli->query("UPDATE tbl_permissions SET permission_name = 'Create Department / Groups', description = 'Add new Department / Group' WHERE permission_key = 'academic_groups.create'");
$mysqli->query("UPDATE tbl_permissions SET permission_name = 'Edit Department / Groups', description = 'Edit Department / Group details, display order, and status' WHERE permission_key = 'academic_groups.edit'");
$mysqli->query("UPDATE tbl_permissions SET permission_name = 'Delete Department / Groups', description = 'Deactivate or soft-delete Department / Group' WHERE permission_key = 'academic_groups.delete'");
echo "Updated tbl_permissions labels.\n";
