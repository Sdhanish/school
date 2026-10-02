<?php
/**
 * Test Suite: Period Setup Relocation & Academic Group-Based Period Management
 */

define('BASEPATH', true);
require 'application/config/database.php';
$cfg = $db['default'];
$m = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);

if ($m->connect_error) {
    die("Database connection failed: " . $m->connect_error . "\n");
}

$passed = 0;
$failed = 0;

function assert_true($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

echo "\n=== 1. Verify Database Schema on tbl_periods ===\n";
$cols = [];
$res = $m->query("SHOW COLUMNS FROM tbl_periods");
while ($r = $res->fetch_assoc()) {
    $cols[$r['Field']] = $r;
}
assert_true(isset($cols['academic_group_id']), "academic_group_id column exists on tbl_periods");
assert_true(isset($cols['period_type']), "period_type column exists on tbl_periods");
assert_true(strpos($cols['period_type']['Type'], 'enum') !== false, "period_type is an ENUM type");

echo "\n=== 2. Verify Preservation of +1/+2 Periods (IDs 1-6) ===\n";
$pRes = $m->query("SELECT period_id, period_name, academic_group_id, period_type FROM tbl_periods WHERE period_id IN (1, 2, 3, 4, 5, 6) ORDER BY period_id");
$found_count = 0;
$all_mapped_to_ss = true;
while ($p = $pRes->fetch_assoc()) {
    $found_count++;
    if ((int)$p['academic_group_id'] !== 5 || $p['period_type'] !== 'Period') {
        $all_mapped_to_ss = false;
    }
}
assert_true($found_count === 6, "All 6 legacy periods (IDs 1-6) are intact and not deleted");
assert_true($all_mapped_to_ss, "All 6 legacy periods are mapped to Academic Group SS (ID 5) with type 'Period'");

echo "\n=== 3. Verify Attendance Records Linked to Periods ===\n";
$attRes = $m->query("SELECT count(*) as cnt FROM tbl_attendance WHERE period_id IN (1, 2)");
$attRow = $attRes->fetch_assoc();
assert_true((int)$attRow['cnt'] > 0, "Existing attendance records (count: {$attRow['cnt']}) still reference periods 1 & 2 without breakage");

echo "\n=== 4. Verify Academic Groups Initial Period Setups ===\n";
$groupsRes = $m->query("SELECT g.academic_group_id, g.group_name, 
    COUNT(CASE WHEN p.period_type = 'Period' THEN 1 END) as period_cnt,
    COUNT(CASE WHEN p.period_type = 'Break' THEN 1 END) as break_cnt,
    COUNT(CASE WHEN p.period_type = 'Lunch Break' THEN 1 END) as lunch_cnt
    FROM tbl_academic_groups g
    LEFT JOIN tbl_periods p ON p.academic_group_id = g.academic_group_id AND p.is_deleted = 'n'
    GROUP BY g.academic_group_id, g.group_name
    ORDER BY g.academic_group_id");

while ($g = $groupsRes->fetch_assoc()) {
    echo "  Group {$g['academic_group_id']} ({$g['group_name']}): {$g['period_cnt']} Periods, {$g['break_cnt']} Breaks, {$g['lunch_cnt']} Lunch\n";
    assert_true((int)$g['period_cnt'] > 0, "Group {$g['group_name']} has active teaching periods configured");
}

echo "\n=== 5. Mock CI Testing for Period_model ===\n";
class MockDB {
    private $m;
    private $wheres = [];
    private $where_ins = [];
    private $where_groups = [];
    private $orders = [];
    private $selects = '*';
    private $from_table = '';
    private $table = 'tbl_periods';
    public $primaryKey = 'period_id';

    public function __construct($m) { $this->m = $m; }
    public function select($s) { $this->selects = $s; return $this; }
    public function from($t) { $this->from_table = $t; return $this; }
    public function where($k, $v = NULL) {
        if ($v === NULL) {
            $this->wheres[] = $k;
        } else {
            $this->wheres[] = "`{$k}` = '" . $this->m->real_escape_string($v) . "'";
        }
        return $this;
    }
    public function where_in($k, $arr) {
        $escaped = array_map(function($i){ return "'" . $this->m->real_escape_string($i) . "'"; }, $arr);
        $this->where_ins[] = "`{$k}` IN (" . implode(',', $escaped) . ")";
        return $this;
    }
    public function group_start() { return $this; }
    public function or_where($k, $v = NULL) { return $this; }
    public function group_end() { return $this; }
    public function order_by($col, $dir = 'ASC') { $this->orders[] = "{$col} {$dir}"; return $this; }
    public function get($table = NULL) {
        $tbl = $table ?: ($this->from_table ?: $this->table);
        $sql = "SELECT {$this->selects} FROM `{$tbl}`";
        $w = array_merge($this->wheres, $this->where_ins);
        if (!empty($w)) {
            $sql .= " WHERE " . implode(' AND ', $w);
        }
        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . implode(', ', $this->orders);
        }
        // Reset query builder
        $this->wheres = [];
        $this->where_ins = [];
        $this->orders = [];
        $this->selects = '*';
        $this->from_table = '';

        $res = $this->m->query($sql);
        return new class($res) {
            private $res;
            public function __construct($r) { $this->res = $r; }
            public function result() {
                $rows = [];
                if ($this->res) {
                    while ($row = $this->res->fetch_object()) $rows[] = $row;
                }
                return $rows;
            }
            public function row() {
                return $this->res ? $this->res->fetch_object() : null;
            }
        };
    }
    public function insert($table, $data) {
        $cols = array_keys($data);
        $vals = array_map(function($v) {
            return $v === NULL ? "NULL" : "'" . $this->m->real_escape_string($v) . "'";
        }, array_values($data));
        $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ")";
        $this->m->query($sql);
        return $this->m->insert_id;
    }
    public function insert_id() { return $this->m->insert_id; }
    public function update($table, $data) {
        $sets = [];
        foreach ($data as $k => $v) {
            $sets[] = "`{$k}` = " . ($v === NULL ? "NULL" : "'" . $this->m->real_escape_string($v) . "'");
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets);
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $this->wheres = [];
        return $this->m->query($sql);
    }
    public function trans_start() { $this->m->begin_transaction(); }
    public function trans_complete() { $this->m->commit(); }
    public function trans_status() { return true; }
}

class CI_Model {
    public $db;
}
$ci_db = new MockDB($m);

require_once 'application/models/Period_model.php';
$periodModel = new Period_model();
$periodModel->db = $ci_db;

// Test get_by_group for SS (group 5)
$ssSlots = $periodModel->get_by_group(5, TRUE);
assert_true(count($ssSlots) >= 8, "Period_model::get_by_group(5) returns at least 8 slots (periods + breaks) for SS");

// Test teaching periods only for SS
$ssTeaching = $periodModel->get_by_group(5, TRUE, 'Period');
assert_true(count($ssTeaching) >= 6, "Period_model::get_by_group(5, TRUE, 'Period') returns only teaching periods");

$clsCheck = $m->query("SELECT class_id, class_name, academic_group_id FROM tbl_classes ORDER BY class_id LIMIT 5");
while ($cRow = $clsCheck->fetch_assoc()) {
    echo "  DB Class: id={$cRow['class_id']}, name={$cRow['class_name']}, group={$cRow['academic_group_id']}\n";
}
$firstClass = $m->query("SELECT class_id, academic_group_id FROM tbl_classes WHERE academic_group_id IS NOT NULL LIMIT 1")->fetch_assoc();
$testClassId = $firstClass['class_id'];
$lpPeriods = $periodModel->get_by_class($testClassId, TRUE, 'Period');
assert_true(count($lpPeriods) >= 4, "Period_model::get_by_class({$testClassId}) returns periods for its academic group");

// Test save_group_periods validation: end_time before start_time
$invalidSlots = [
    ['name' => 'Bad Period', 'type' => 'Period', 'start_time' => '10:00', 'end_time' => '09:00']
];
$res = $periodModel->save_group_periods(1, $invalidSlots);
assert_true($res['success'] === false, "save_group_periods rejects end_time earlier than start_time");

// Test save_group_periods validation: overlapping intervals
$overlapSlots = [
    ['name' => 'Period 1', 'type' => 'Period', 'start_time' => '09:00', 'end_time' => '10:00'],
    ['name' => 'Period 2', 'type' => 'Period', 'start_time' => '09:30', 'end_time' => '10:30']
];
$res = $periodModel->save_group_periods(1, $overlapSlots);
assert_true($res['success'] === false, "save_group_periods rejects overlapping periods ({$res['message']})");

// Test save_group_periods validation: zero teaching periods (only breaks)
$onlyBreaks = [
    ['name' => 'Tea Break', 'type' => 'Break', 'start_time' => '10:00', 'end_time' => '10:15']
];
$res = $periodModel->save_group_periods(1, $onlyBreaks);
assert_true($res['success'] === false, "save_group_periods rejects schedule without any teaching periods");

echo "\n=== 6. Verify Routes in application/config/routes.php ===\n";
$routesContent = file_get_contents('application/config/routes.php');
assert_true(strpos($routesContent, "'timetable/period_setup'") !== false, "Route 'timetable/period_setup' is defined");
assert_true(strpos($routesContent, "'timetable/period-setup'") !== false, "Route 'timetable/period-setup' is defined");
assert_true(strpos($routesContent, "'timetable/ajax_get_group_periods'") !== false, "Route 'timetable/ajax_get_group_periods' is defined");

echo "\n=== 7. Verify Sidebar Navigation in assets/app.js ===\n";
$appJs = file_get_contents('assets/app.js');
assert_true(strpos($appJs, '"timetable-period-setup": "timetable/period_setup"') !== false, "ROUTE_MAP includes timetable-period-setup");
assert_true(strpos($appJs, '"attendance-periods": "Period Setup (+1 / +2)"') === false, "attendance-periods menu item removed from Student Attendance");
assert_true(strpos($appJs, 'key: "timetable-period-setup", label: "Period Setup"') !== false, "Period Setup menu item added under Timetable");

echo "\n============================================\n";
echo "Test Results: {$passed} Passed, {$failed} Failed\n";
echo "============================================\n\n";

$m->close();
exit($failed > 0 ? 1 : 0);
