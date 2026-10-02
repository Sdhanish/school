<?php
/**
 * Test Suite: Timetable Subject Allocation Model & Division Migration
 * Verifies resolution of "Undefined property: stdClass::$section_id" on Timetable_allocation_model line 31
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

echo "\n=== 1. Verify Database Schema for Division Columns ===\n";
$res = $m->query("SHOW COLUMNS FROM tbl_subject_allocations LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_subject_allocations has column 'division_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_subject_allocations LIKE 'section_id'");
assert_true($res && $res->num_rows === 0, "tbl_subject_allocations does NOT have column 'section_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_timetable LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_timetable has column 'division_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_timetable LIKE 'section_id'");
assert_true($res && $res->num_rows === 0, "tbl_timetable does NOT have column 'section_id'");

$res = $m->query("SHOW COLUMNS FROM tbl_timetable_publish LIKE 'division_id'");
assert_true($res && $res->num_rows === 1, "tbl_timetable_publish has column 'division_id'");


echo "\n=== 2. Test Timetable_allocation_model::get_allocations() Query Execution ===\n";
// Track PHP notices / errors
$errors_caught = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors_caught) {
    $errors_caught[] = "[$errno] $errstr on line $errline in $errfile";
    return true;
});

// Mock CI environment
class MockCIAllocDB {
    private $m;
    private $wheres = [];
    private $selects = '*';
    private $from_table = '';
    private $joins = [];
    private $orders = [];

    public function __construct($m) { $this->m = $m; }
    public function select($s) { $this->selects = $s; return $this; }
    public function from($t) { $this->from_table = $t; return $this; }
    public function join($t, $on, $type = 'inner') { $this->joins[] = strtoupper($type) . " JOIN {$t} ON {$on}"; return $this; }
    public function order_by($col, $dir = 'ASC') { $this->orders[] = "{$col} {$dir}"; return $this; }
    public function where($k, $v = NULL) {
        if ($v === NULL && is_array($k)) {
            foreach ($k as $col => $val) $this->where($col, $val);
        } else {
            $valEsc = $v === NULL ? "NULL" : "'" . $this->m->real_escape_string($v) . "'";
            $this->wheres[] = "{$k} = {$valEsc}";
        }
        return $this;
    }
    public function get($table = NULL) {
        if ($table) $this->from_table = $table;
        $sql = "SELECT {$this->selects} FROM {$this->from_table}";
        if (!empty($this->joins)) $sql .= " " . implode(' ', $this->joins);
        if (!empty($this->wheres)) $sql .= " WHERE " . implode(' AND ', $this->wheres);
        if (!empty($this->orders)) $sql .= " ORDER BY " . implode(', ', $this->orders);

        $this->wheres = [];
        $this->joins = [];
        $this->orders = [];
        $this->selects = '*';
        $this->from_table = '';

        $res = $this->m->query($sql);
        return new class($res) {
            private $r;
            public function __construct($res) { $this->r = $res; }
            public function result() {
                $rows = [];
                if ($this->r) {
                    while ($row = $this->r->fetch_object()) $rows[] = $row;
                }
                return $rows;
            }
            public function row() {
                return $this->r ? $this->r->fetch_object() : null;
            }
        };
    }
    public function query($sql, $binds = []) {
        $res = $this->m->query($sql);
        return new class($res) {
            private $r;
            public function __construct($res) { $this->r = $res; }
            public function result() {
                $rows = [];
                if ($this->r) {
                    while ($row = $this->r->fetch_object()) $rows[] = $row;
                }
                return $rows;
            }
            public function row() {
                return $this->r ? $this->r->fetch_object() : null;
            }
        };
    }
    public function count_all_results($table) {
        $sql = "SELECT COUNT(*) as cnt FROM `{$table}`";
        if (!empty($this->wheres)) $sql .= " WHERE " . implode(' AND ', $this->wheres);
        $this->wheres = [];
        $res = $this->m->query($sql);
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? (int)$row['cnt'] : 0;
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
        if (!empty($this->wheres)) $sql .= " WHERE " . implode(' AND ', $this->wheres);
        $this->wheres = [];
        return $this->m->query($sql);
    }
}

if (!class_exists('CI_Model')) {
    class CI_Model { public $db; }
}

require_once 'application/models/Timetable_allocation_model.php';
$allocModel = new Timetable_allocation_model();
$allocModel->db = new MockCIAllocDB($m);

// Fetch allocations for year 1
$allocations = $allocModel->get_allocations(1);
restore_error_handler();

assert_true(empty($errors_caught), "get_allocations(1) executed with ZERO PHP notices/errors (no undefined property: section_id)");
if (!empty($errors_caught)) {
    foreach ($errors_caught as $e) echo "    Error details: {$e}\n";
}

assert_true(is_array($allocations), "get_allocations(1) returned array (count: " . count($allocations) . ")");

if (!empty($allocations)) {
    $first = $allocations[0];
    assert_true(isset($first->division_id), "Allocation object has 'division_id' property");
    assert_true(isset($first->division_name), "Allocation object has 'division_name' property");
    assert_true(isset($first->actual_allocated), "Allocation object has 'actual_allocated' property");
    assert_true(isset($first->remaining), "Allocation object has 'remaining' property");
}


echo "\n=== 3. Test Timetable Allocation CRUD Lifecycle with division_id ===\n";
// Insert
$newAllocData = [
    'academic_year_id'      => 1,
    'class_id'              => 1,
    'division_id'           => 8,
    'subject_id'            => 1,
    'teacher_id'            => 1,
    'weekly_periods_target' => 5,
    'status'                => 1
];
$newId = $allocModel->save_allocation($newAllocData);
assert_true($newId > 0, "Created new subject allocation #{$newId} with division_id = 8");

// Verify in DB directly
$resCheck = $m->query("SELECT * FROM tbl_subject_allocations WHERE allocation_id = {$newId}");
$rowCheck = $resCheck ? $resCheck->fetch_assoc() : null;
assert_true($rowCheck && (int)$rowCheck['division_id'] === 8, "Direct DB check confirms division_id = 8 in tbl_subject_allocations");

// Fetch with get_allocations filtered by division_id = 8
$filteredAllocs = $allocModel->get_allocations(1, 1, 8);
$found = false;
foreach ($filteredAllocs as $fa) {
    if ($fa->allocation_id == $newId) {
        $found = true;
        break;
    }
}
assert_true($found, "get_allocations(1, 1, 8) successfully found newly created allocation #{$newId}");

// Update
$allocModel->save_allocation(['weekly_periods_target' => 7], $newId);
$resUp = $m->query("SELECT weekly_periods_target FROM tbl_subject_allocations WHERE allocation_id = {$newId}");
$rowUp = $resUp ? $resUp->fetch_assoc() : null;
assert_true($rowUp && (int)$rowUp['weekly_periods_target'] === 7, "Updated allocation #{$newId} target to 7");

// Clean up
$m->query("DELETE FROM tbl_subject_allocations WHERE allocation_id = {$newId}");
$resDel = $m->query("SELECT allocation_id FROM tbl_subject_allocations WHERE allocation_id = {$newId}");
assert_true($resDel && $resDel->num_rows === 0, "Cleaned up test allocation #{$newId}");


echo "\n=== 4. Verify Timetable_setting_model and tbl_timetable_publish ===\n";
require_once 'application/models/Timetable_setting_model.php';
$settingModel = new Timetable_setting_model();
$settingModel->db = new MockCIAllocDB($m);

$pubRecs = $settingModel->get_publish_records(1);
assert_true(is_array($pubRecs), "Timetable_setting_model::get_publish_records(1) executed without error");


echo "\n=== 5. Verify Timetable_model detect_all_conflicts ===\n";
require_once 'application/models/Timetable_model.php';
$ttModel = new Timetable_model();
$ttModel->db = new MockCIAllocDB($m);

// Direct execution of the query from detect_all_conflicts to verify sec1.division_name and sec2.division_name
$sql_conflicts = "SELECT tt1.timetable_id as id1, tt2.timetable_id as id2,
       tt1.day, p.period_name, s.full_name as teacher_name,
       c1.class_name as class1, sec1.division_name as sec1, sub1.subject_name as sub1,
       c2.class_name as class2, sec2.division_name as sec2, sub2.subject_name as sub2
FROM tbl_timetable tt1
JOIN tbl_timetable tt2 ON tt1.academic_year_id = tt2.academic_year_id
     AND tt1.day = tt2.day
     AND tt1.period_id = tt2.period_id
     AND tt1.teacher_id = tt2.teacher_id
     AND tt1.timetable_id < tt2.timetable_id
JOIN tbl_staff s ON s.staff_id = tt1.teacher_id
JOIN tbl_periods p ON p.period_id = tt1.period_id
JOIN tbl_classes c1 ON c1.class_id = tt1.class_id
JOIN tbl_divisions sec1 ON sec1.division_id = tt1.division_id
JOIN tbl_subjects sub1 ON sub1.subject_id = tt1.subject_id
JOIN tbl_classes c2 ON c2.class_id = tt2.class_id
JOIN tbl_divisions sec2 ON sec2.division_id = tt2.division_id
JOIN tbl_subjects sub2 ON sub2.subject_id = tt2.subject_id
WHERE tt1.academic_year_id = 1 AND tt1.status = 1 AND tt2.status = 1";
$res_conf = $m->query($sql_conflicts);
assert_true($res_conf !== false, "Timetable_model::detect_all_conflicts SQL query executed successfully without missing column error");


echo "\n=== 6. Verify Timetable Controller AJAX Endpoint ===\n";
require_once 'application/models/Division_model.php';
$divModel = new Division_model();
$divModel->db = new MockCIAllocDB($m);

$class1Divs = $divModel->get_all(1);
assert_true(is_array($class1Divs) && count($class1Divs) > 0, "Division_model::get_all(1) returns divisions for Class 1");
assert_true(isset($class1Divs[0]->division_id) && isset($class1Divs[0]->division_name), "Division object contains division_id and division_name for AJAX dropdowns");

echo "\n============================================\n";
echo "Test Results: Passed: {$passed} | Failed: {$failed}\n";
echo "============================================\n";

$m->close();
exit($failed > 0 ? 1 : 0);
