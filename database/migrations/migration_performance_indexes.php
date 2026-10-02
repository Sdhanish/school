<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add composite performance indexes
 *
 * Adds compound indexes on the most-frequently filtered column combinations.
 * Each index is checked for existence before creation to be idempotent.
 *
 * Applied: 2026-09-19
 */

$CI =& get_instance();
$db = $CI->db;

$indexes = [
    // Students: most common filter = school + academic year + soft-delete
    [
        'table' => 'tbl_students',
        'name'  => 'idx_school_ay_deleted',
        'cols'  => '(school_id, academic_year_id, is_deleted)',
    ],
    // Students: class roster filter = school + class + division
    [
        'table' => 'tbl_students',
        'name'  => 'idx_school_class_div',
        'cols'  => '(school_id, class_id, division_id)',
    ],
    // Staff: filter by school + designation (for teacher/staff type pages)
    [
        'table' => 'tbl_staff',
        'name'  => 'idx_school_designation',
        'cols'  => '(school_id, designation_id)',
    ],
    // Attendance: date-range queries scoped to a school
    [
        'table' => 'tbl_attendance',
        'name'  => 'idx_school_date',
        'cols'  => '(school_id, attendance_date)',
    ],
];

$results = [];

foreach ($indexes as $idx) {
    // Check if index already exists
    $exists = $db->query(
        "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = ?
           AND INDEX_NAME   = ?",
        [$idx['table'], $idx['name']]
    )->row()->cnt;

    if ($exists) {
        $results[] = "SKIP  {$idx['table']}.{$idx['name']} (already exists)";
        continue;
    }

    $sql = "ALTER TABLE {$idx['table']} ADD INDEX {$idx['name']} {$idx['cols']}";
    $db->query($sql);

    if ($db->error()['code'] === 0) {
        $results[] = "OK    {$idx['table']}.{$idx['name']}";
    } else {
        $results[] = "ERROR {$idx['table']}.{$idx['name']}: " . $db->error()['message'];
    }
}

echo json_encode(['status' => 'done', 'results' => $results], JSON_PRETTY_PRINT);
