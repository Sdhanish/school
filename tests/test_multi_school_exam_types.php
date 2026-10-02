<?php
/**
 * Automated Verification for Multi-School Exam Types and Isolation
 * Run via CLI: php tests/test_multi_school_exam_types.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Model.php';

// Bootstrap minimal CodeIgniter database & models
require_once APPPATH . 'config/database.php';
$db_config = $db[$active_group];

$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("DB Connection failed: " . $mysqli->connect_error . "\n");
}

$tests_run = 0;
$tests_passed = 0;

function assert_test($description, $condition) {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "[PASS] $description\n";
    } else {
        echo "[FAIL] $description\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING DATABASE STATE BEFORE SEEDING\n";
echo "=======================================================\n";

// Check Login2 (school 1) existing exam types
$res1 = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_exam_types WHERE school_id = 1 AND is_deleted = 'n'");
$row1 = $res1->fetch_assoc();
$login2_count = (int)$row1['cnt'];
assert_test("Login2 (school_id = 1) has active exam types", $login2_count > 0);

// Check Test School (school 13) state
$res13 = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_exam_types WHERE school_id = 13 AND is_deleted = 'n'");
$row13 = $res13->fetch_assoc();
$initial_test_count = (int)$row13['cnt'];
echo "Initial Test School (school_id = 13) active exam types count: $initial_test_count\n";

echo "\n=======================================================\n";
echo "2. TESTING DYNAMIC INITIALIZATION FOR TEST SCHOOL (13)\n";
echo "=======================================================\n";

// Bootstrap CI controller mock environment
class CI_DB_Mock {
    private $mysqli;
    public $select_clause = '*';
    public $where_clauses = [];
    public $order_clauses = [];
    public $from_table = '';
    public $limit_count = null;
    public $group_clauses = [];

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function select($cols) {
        $this->select_clause = $cols;
        return $this;
    }

    public function from($tbl) {
        $this->from_table = $tbl;
        return $this;
    }

    public function where($col, $val = null) {
        if (is_array($col)) {
            foreach ($col as $k => $v) $this->where($k, $v);
            return $this;
        }
        $this->where_clauses[] = "$col = '" . $this->mysqli->real_escape_string((string)$val) . "'";
        return $this;
    }

    public function order_by($col, $dir = 'ASC') {
        $this->order_clauses[] = "$col $dir";
        return $this;
    }

    public function group_by($col) {
        $this->group_clauses[] = $col;
        return $this;
    }

    public function count_all_results($table = '') {
        $tbl = $table ?: $this->from_table;
        $where = !empty($this->where_clauses) ? 'WHERE ' . implode(' AND ', $this->where_clauses) : '';
        $sql = "SELECT COUNT(*) as cnt FROM $tbl $where";
        $res = $this->mysqli->query($sql);
        $this->reset();
        $row = $res->fetch_assoc();
        return (int)$row['cnt'];
    }

    public function get($table = '') {
        $tbl = $table ?: $this->from_table;
        $where = !empty($this->where_clauses) ? 'WHERE ' . implode(' AND ', $this->where_clauses) : '';
        $order = !empty($this->order_clauses) ? 'ORDER BY ' . implode(', ', $this->order_clauses) : '';
        $group = !empty($this->group_clauses) ? 'GROUP BY ' . implode(', ', $this->group_clauses) : '';
        $limit = $this->limit_count ? "LIMIT {$this->limit_count}" : '';
        $sql = "SELECT {$this->select_clause} FROM $tbl $where $group $order $limit";
        $res = $this->mysqli->query($sql);
        $this->reset();

        return new class($res) {
            private $res;
            public function __construct($res) { $this->res = $res; }
            public function result() {
                $rows = [];
                if ($this->res) {
                    while ($r = $this->res->fetch_object()) $rows[] = $r;
                }
                return $rows;
            }
            public function row() {
                if ($this->res) {
                    return $this->res->fetch_object();
                }
                return null;
            }
        };
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $vals = array_map(function($v) {
            if ($v === null) return "NULL";
            return "'" . $this->mysqli->real_escape_string((string)$v) . "'";
        }, array_values($data));
        $sql = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ")";
        $this->mysqli->query($sql);
        $this->reset();
        return true;
    }

    public function insert_id() {
        return $this->mysqli->insert_id;
    }

    public function update($table, $data) {
        $sets = [];
        foreach ($data as $k => $v) {
            if ($v === null) $sets[] = "$k = NULL";
            else $sets[] = "$k = '" . $this->mysqli->real_escape_string((string)$v) . "'";
        }
        $where = !empty($this->where_clauses) ? 'WHERE ' . implode(' AND ', $this->where_clauses) : '';
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " $where";
        $ret = $this->mysqli->query($sql);
        $this->reset();
        return $ret;
    }

    private function reset() {
        $this->select_clause = '*';
        $this->where_clauses = [];
        $this->order_clauses = [];
        $this->from_table = '';
        $this->limit_count = null;
        $this->group_clauses = [];
    }
}

class CI_Model_Mock {
    public $db;
    public function __construct($db) {
        $this->db = $db;
    }
}

// Instantiate Exam_type_model logic test
require_once APPPATH . 'models/Exam_type_model.php';
$ci_db = new CI_DB_Mock($mysqli);

class Testable_Exam_type_model extends Exam_type_model {
    public function __construct($mock_db) {
        $this->db = $mock_db;
    }
}

$model = new Testable_Exam_type_model($ci_db);

// Fetch exam types for Test School (school 13)
$test_school_types = $model->get_exam_types(13, true);
assert_test("get_exam_types(13) returned non-empty list", !empty($test_school_types));
assert_test("Test School types count >= 8", count($test_school_types) >= 8);

$all_school_13 = true;
foreach ($test_school_types as $t) {
    if ((int)$t->school_id !== 13) {
        $all_school_13 = false;
        break;
    }
}
assert_test("All returned exam types belong strictly to school_id = 13", $all_school_13);

// Fetch exam types for Login2 (school 1)
$login2_types = $model->get_exam_types(1, true);
assert_test("get_exam_types(1) returned non-empty list", !empty($login2_types));
$all_school_1 = true;
foreach ($login2_types as $t) {
    if ((int)$t->school_id !== 1) {
        $all_school_1 = false;
        break;
    }
}
assert_test("All returned exam types for school 1 belong strictly to school_id = 1", $all_school_1);

echo "\n=======================================================\n";
echo "3. TESTING SCHOOL ISOLATION (MUTATIONS DO NOT LEAK)\n";
echo "=======================================================\n";

// Insert a unique type only in Test School (school 13)
$custom_name = "Test School Unique Exam " . time();
$new_id = $model->insert([
    'school_id'   => 13,
    'type_name'   => $custom_name,
    'description' => 'Custom exam for school 13',
    'status'      => 1
]);
assert_test("Custom exam inserted successfully with ID > 0", $new_id > 0);

// Verify it exists in School 13
assert_test("Custom exam exists in school 13", $model->is_name_exists($custom_name, null, 13));

// Verify it DOES NOT exist in School 1
assert_test("Custom exam DOES NOT exist in school 1 (Isolation preserved)", !$model->is_name_exists($custom_name, null, 1));

// Verify get_exam_types(1) does not include it
$school_1_names = array_map(function($t) { return $t->type_name; }, $model->get_exam_types(1, false));
assert_test("School 1 exam types list does not contain School 13 custom exam", !in_array($custom_name, $school_1_names));

// Delete the custom type
$model->delete($new_id, 13);
$deleted_record = $model->get_by_id($new_id, 13);
assert_test("Deleted exam type is no longer returned or marked deleted", empty($deleted_record) || $deleted_record->is_deleted === 'y');

echo "\n=======================================================\n";
echo "4. TESTING NEW SCHOOL INITIALIZATION\n";
echo "=======================================================\n";

$temp_school_id = 99999;
// Clean up in case of leftover
$mysqli->query("DELETE FROM tbl_exam_types WHERE school_id = $temp_school_id");

$seeded = $model->initialize_school_exam_types($temp_school_id);
assert_test("initialize_school_exam_types(99999) seeded default exam types", !empty($seeded));
assert_test("Seeded types count >= 8 for brand new school", count($seeded) >= 8);

$seeded_all_99999 = true;
foreach ($seeded as $st) {
    if ((int)$st->school_id !== $temp_school_id) {
        $seeded_all_99999 = false;
        break;
    }
}
assert_test("All newly seeded types for school 99999 have school_id = 99999", $seeded_all_99999);

// Second call should return existing and not duplicate
$second_call = $model->initialize_school_exam_types($temp_school_id);
assert_test("Subsequent call does not duplicate exam types", count($second_call) === count($seeded));

// Clean up temporary test school
$mysqli->query("DELETE FROM tbl_exam_types WHERE school_id = $temp_school_id");
assert_test("Cleaned up temporary test school records", true);

echo "\n=======================================================\n";
echo "5. TESTING EXAM SETTINGS DYNAMIC SCHOOL NAME\n";
echo "=======================================================\n";

require_once APPPATH . 'models/Exam_setting_model.php';
class Testable_Exam_setting_model extends Exam_setting_model {
    public function __construct($mock_db) {
        $this->db = $mock_db;
    }
}
$setting_model = new Testable_Exam_setting_model($ci_db);

$s13_settings = $setting_model->get_settings(13);
assert_test("Settings retrieved for school 13", !empty($s13_settings));
assert_test("Settings report_card_header does NOT contain hardcoded 'Login2'", strpos($s13_settings->report_card_header, 'Login2') === false);
assert_test("Settings report_card_header contains actual school name 'School Test'", strpos($s13_settings->report_card_header, 'School Test') !== false);

echo "\n=======================================================\n";
echo "RESULTS: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED.\n";
    exit(1);
}
