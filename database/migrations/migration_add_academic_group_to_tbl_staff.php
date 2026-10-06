<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add academic_group_id column to tbl_staff
 *
 * Ensures tbl_staff schema aligns with Staff_model and Subject_teacher_model queries
 * that select and join s.academic_group_id against tbl_academic_groups.
 */

$CI =& get_instance();
$db = $CI->db;

$col_exists = $db->query("
    SELECT COUNT(*) as cnt 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'tbl_staff' 
      AND COLUMN_NAME = 'academic_group_id'
")->row()->cnt;

if (!$col_exists) {
    $db->query("ALTER TABLE `tbl_staff` ADD COLUMN `academic_group_id` INT(11) UNSIGNED DEFAULT NULL AFTER `department_id`");
    $db->query("ALTER TABLE `tbl_staff` ADD INDEX `idx_staff_academic_group` (`academic_group_id`)");
    echo "Added column academic_group_id to tbl_staff.\n";
} else {
    echo "Column academic_group_id already exists in tbl_staff.\n";
}
