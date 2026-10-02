<?php
/**
 * Test Suite: Fix MySQL Error 1583 in Staff/Teacher Management
 * Run via CLI: php tests/test_staff_teachers_query.php
 */
define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', dirname(__DIR__) . '/');
define('VIEWPATH', APPPATH . 'views/');

require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/database.php';
require_once BASEPATH . 'database/DB.php';
$db_conn =& DB();

require_once BASEPATH . 'core/Loader.php';

class MockCI {
    public $db;
    public $load;
    public function __construct($db) {
        $this->db = $db;
        $this->load = new CI_Loader();
    }
}
$mock_ci = new MockCI($db_conn);
function &get_instance() {
    global $mock_ci;
    return $mock_ci;
}

require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'models/Staff_model.php';

$staff_model = new Staff_model();
$staff_model->db = $db_conn;

$passed = 0;
$failed = 0;

function assert_true($condition, $test_name, &$passed, &$failed) {
    if ($condition) {
        echo "  [PASS] {$test_name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$test_name}\n";
        $failed++;
    }
}

echo "\n============================================================\n";
echo " TEST SUITE: Fix MySQL Error 1583 in Staff/Teacher Management\n";
echo "============================================================\n\n";

// -------------------------------------------------------------
// Test 1: Staff_model::get_teachers() loads without Error 1583
// -------------------------------------------------------------
echo "1. Verify Staff_model::get_teachers() runs without SQL errors\n";
try {
    $teachers = $staff_model->get_teachers();
    assert_true(is_array($teachers) && count($teachers) > 0, "get_teachers() executed successfully and returned " . count($teachers) . " teachers", $passed, $failed);
} catch (Throwable $e) {
    assert_true(false, "get_teachers() threw exception: " . $e->getMessage(), $passed, $failed);
}

// -------------------------------------------------------------
// Test 2: Verify handled divisions formatting for single assigned division
// -------------------------------------------------------------
echo "\n2. Verify single assigned division handling\n";
// Assign teacher 1 to division 12 (Class LKG, Division A)
$db_conn->query("UPDATE tbl_divisions SET class_teacher_id = 1 WHERE division_id = 12");
$teachers = $staff_model->get_teachers();
$teacher1 = null;
foreach ($teachers as $t) {
    if ((int)$t->staff_id === 1) {
        $teacher1 = $t;
        break;
    }
}
assert_true($teacher1 !== null, "Teacher with ID 1 found", $passed, $failed);
assert_true(isset($teacher1->sections_handled), "Property 'sections_handled' exists on teacher object", $passed, $failed);
assert_true(isset($teacher1->divisions_handled), "Property 'divisions_handled' exists on teacher object", $passed, $failed);
assert_true(strpos($teacher1->sections_handled, 'LKG - A') !== false, "Assigned division matches expected 'LKG - A' (got '{$teacher1->sections_handled}')", $passed, $failed);
assert_true($teacher1->sections_handled === $teacher1->divisions_handled, "sections_handled equals divisions_handled", $passed, $failed);

// -------------------------------------------------------------
// Test 3: Verify multiple assigned divisions formatting
// -------------------------------------------------------------
echo "\n3. Verify multiple assigned divisions formatting\n";
// Temporarily create a second division for class 13 (UKG - A) assigned to teacher 1
$db_conn->query("INSERT INTO tbl_divisions (division_id, class_id, division_name, class_teacher_id, status, is_deleted) VALUES (9999, 13, 'B', 1, 1, 'n') ON DUPLICATE KEY UPDATE class_teacher_id = 1, status = 1, is_deleted = 'n'");
$teachers = $staff_model->get_teachers();
$teacher1 = null;
foreach ($teachers as $t) {
    if ((int)$t->staff_id === 1) {
        $teacher1 = $t;
        break;
    }
}
assert_true($teacher1 !== null && strpos($teacher1->sections_handled, 'LKG - A') !== false && strpos($teacher1->sections_handled, 'UKG - B') !== false, "Multiple divisions correctly formatted: '{$teacher1->sections_handled}'", $passed, $failed);
// Clean up temporary division
$db_conn->query("DELETE FROM tbl_divisions WHERE division_id = 9999");

// -------------------------------------------------------------
// Test 4: Verify staff with no assigned division
// -------------------------------------------------------------
echo "\n4. Verify staff with no assigned division\n";
$teacher_unassigned = null;
foreach ($teachers as $t) {
    if ((int)$t->staff_id === 2) {
        $teacher_unassigned = $t;
        break;
    }
}
assert_true($teacher_unassigned !== null, "Unassigned teacher (ID 2) found", $passed, $failed);
assert_true(empty($teacher_unassigned->sections_handled), "Unassigned teacher has empty sections_handled without SQL errors", $passed, $failed);

// -------------------------------------------------------------
// Test 5: Verify filters (search, department, etc.)
// -------------------------------------------------------------
echo "\n5. Verify get_teachers() with filters\n";
$filtered = $staff_model->get_teachers(['search' => 'Priya']);
assert_true(count($filtered) >= 1 && (int)$filtered[0]->staff_id === 1, "Filter by search 'Priya' returned expected teacher", $passed, $failed);

// -------------------------------------------------------------
// Test 6: Verify DataTables query
// -------------------------------------------------------------
echo "\n6. Verify get_datatables_data() and get_datatables_count_all()\n";
$dt_rows = $staff_model->get_datatables_data([], 10, 0, 0, 'ASC');
assert_true(is_array($dt_rows) && count($dt_rows) > 0, "get_datatables_data() returned " . count($dt_rows) . " staff members", $passed, $failed);
$dt_count = $staff_model->get_datatables_count_all();
assert_true($dt_count > 0, "get_datatables_count_all() returned {$dt_count}", $passed, $failed);

// Clean up test assignment on division 12
$db_conn->query("UPDATE tbl_divisions SET class_teacher_id = NULL WHERE division_id = 12");

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n============================================================\n";
echo " TEST SUMMARY\n";
echo " Passed: {$passed}\n";
echo " Failed: {$failed}\n";
echo "============================================================\n";

exit($failed > 0 ? 1 : 0);
