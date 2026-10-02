<?php
define('BASEPATH', 'system/');
require 'application/config/database.php';
$cfg = $db[$active_group];
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($m->connect_error) {
    die("Connection failed: " . $m->connect_error . "\n");
}

$sql = "CREATE TABLE IF NOT EXISTS `tbl_document_designs` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT(10) UNSIGNED NOT NULL,
  `document_type` VARCHAR(60) NOT NULL,
  `header_image` VARCHAR(255) DEFAULT NULL,
  `footer_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` INT(10) UNSIGNED DEFAULT NULL,
  `updated_by` INT(10) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` CHAR(1) NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_school_doc_deleted` (`school_id`, `document_type`, `is_deleted`),
  KEY `idx_school_status` (`school_id`, `status`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($m->query($sql)) {
    echo "tbl_document_designs created/verified successfully.\n";
} else {
    echo "Error creating table: " . $m->error . "\n";
}

// Ensure menu item in tbl_menu_items under parent_id = 238
$check = $m->query("SELECT id FROM tbl_menu_items WHERE route = 'settings/document_design'");
if ($check->num_rows === 0) {
    $menu_sql = "INSERT INTO tbl_menu_items (parent_id, menu_key, menu_name, route, icon, menu_type, sort_order, is_super_admin_only, is_soon, is_active, created_at) 
                 VALUES (238, 'document-design', 'Document Design', 'settings/document_design', 'palette', 'submenu', 5, 0, 0, 1, NOW())";
    if ($m->query($menu_sql)) {
        echo "Menu item 'Document Design' added under Login2 Settings successfully.\n";
    } else {
        echo "Error inserting menu item: " . $m->error . "\n";
    }
} else {
    echo "Menu item 'Document Design' already exists.\n";
}
