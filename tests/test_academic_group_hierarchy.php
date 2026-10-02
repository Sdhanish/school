<?php
/**
 * Test Suite: Academic Group → Class → Division Hierarchy
 * Run: php tests/test_academic_group_hierarchy.php
 */

define('BASEPATH', '1');
define('ENVIRONMENT', 'development');

require_once __DIR__ . '/../application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

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
echo " TEST SUITE: Academic Group → Class → Division Structure\n";
echo "============================================================\n\n";

// -------------------------------------------------------------
// Test 1: Verify tbl_academic_groups table and default groups
// -------------------------------------------------------------
echo "1. Verify tbl_academic_groups table and default groups\n";
$res = $mysqli->query("SELECT * FROM tbl_academic_groups WHERE is_deleted = 'n' ORDER BY display_order ASC");
$groups = [];
while ($row = $res->fetch_assoc()) {
    $groups[$row['group_name']] = $row;
}

assert_true(isset($groups["KG's"]), "Academic Group 'KG\'s' exists", $passed, $failed);
assert_true(isset($groups["LP"]), "Academic Group 'LP' exists", $passed, $failed);
assert_true(isset($groups["UP"]), "Academic Group 'UP' exists", $passed, $failed);
assert_true(isset($groups["HS"]), "Academic Group 'HS' exists", $passed, $failed);
assert_true(isset($groups["SS"]), "Academic Group 'SS' exists", $passed, $failed);
assert_true(count($groups) >= 5, "At least 5 default academic groups exist (found " . count($groups) . ")", $passed, $failed);

// -------------------------------------------------------------
// Test 2: Verify tbl_academic_group_classes standard class mapping
// -------------------------------------------------------------
echo "\n2. Verify tbl_academic_group_classes template classes\n";
$res2 = $mysqli->query("
    SELECT ag.group_name, agc.class_name 
    FROM tbl_academic_group_classes agc
    JOIN tbl_academic_groups ag ON ag.academic_group_id = agc.academic_group_id
    ORDER BY ag.display_order ASC, agc.display_order ASC
");
$group_classes = [];
while ($row = $res2->fetch_assoc()) {
    $group_classes[$row['group_name']][] = $row['class_name'];
}

assert_true(isset($group_classes["KG's"]) && in_array('LKG', $group_classes["KG's"]) && in_array('UKG', $group_classes["KG's"]), "KG's contains LKG and UKG", $passed, $failed);
assert_true(isset($group_classes["LP"]) && in_array('1', $group_classes["LP"]) && in_array('4', $group_classes["LP"]), "LP contains Class 1 through 4", $passed, $failed);
assert_true(isset($group_classes["UP"]) && in_array('5', $group_classes["UP"]) && in_array('7', $group_classes["UP"]), "UP contains Class 5 through 7", $passed, $failed);
assert_true(isset($group_classes["HS"]) && in_array('8', $group_classes["HS"]) && in_array('10', $group_classes["HS"]), "HS contains Class 8 through 10", $passed, $failed);
assert_true(isset($group_classes["SS"]) && in_array('11', $group_classes["SS"]) && in_array('12', $group_classes["SS"]), "SS contains Class 11 and 12", $passed, $failed);

// -------------------------------------------------------------
// Test 3: Verify tbl_classes.academic_group_id column and FK constraint
// -------------------------------------------------------------
echo "\n3. Verify tbl_classes schema and relationship\n";
$col_check = $mysqli->query("SHOW COLUMNS FROM tbl_classes LIKE 'academic_group_id'");
assert_true($col_check->num_rows === 1, "tbl_classes has academic_group_id column", $passed, $failed);

$fk_check = $mysqli->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = '{$db_config['database']}' 
      AND TABLE_NAME = 'tbl_classes' 
      AND COLUMN_NAME = 'academic_group_id'
      AND REFERENCED_TABLE_NAME = 'tbl_academic_groups'
");
assert_true($fk_check->num_rows > 0, "Foreign key constraint on tbl_classes.academic_group_id exists", $passed, $failed);

// -------------------------------------------------------------
// Test 4: Verify existing classes have valid academic_group_id mapped
// -------------------------------------------------------------
echo "\n4. Verify existing classes mapping\n";
$unmapped = $mysqli->query("SELECT class_id, class_name FROM tbl_classes WHERE (academic_group_id IS NULL OR academic_group_id = 0) AND is_deleted = 'n'");
assert_true($unmapped->num_rows === 0, "All active classes have an assigned academic_group_id", $passed, $failed);

// Check specific standard classes:
$lkg = $mysqli->query("SELECT c.class_name, ag.group_name FROM tbl_classes c JOIN tbl_academic_groups ag ON ag.academic_group_id = c.academic_group_id WHERE c.class_id = 1")->fetch_assoc();
assert_true($lkg && $lkg['group_name'] === "KG's", "Class 1 (LKG) belongs to KG's", $passed, $failed);

$grade10 = $mysqli->query("SELECT c.class_name, ag.group_name FROM tbl_classes c JOIN tbl_academic_groups ag ON ag.academic_group_id = c.academic_group_id WHERE c.class_id = 8")->fetch_assoc();
assert_true($grade10 && $grade10['group_name'] === "HS", "Class 8 (Grade 10) belongs to HS", $passed, $failed);

// -------------------------------------------------------------
// Test 5: Verify Division auto-derives Academic Group (Division → Class → Academic Group)
// -------------------------------------------------------------
echo "\n5. Verify Division → Class → Academic Group derivation\n";
$div_query = $mysqli->query("
    SELECT d.division_id, d.division_name, c.class_id, c.class_name, ag.academic_group_id, ag.group_name
    FROM tbl_divisions d
    JOIN tbl_classes c ON c.class_id = d.class_id
    JOIN tbl_academic_groups ag ON ag.academic_group_id = c.academic_group_id
    WHERE d.is_deleted = 'n'
    LIMIT 5
");
assert_true($div_query->num_rows > 0, "Divisions successfully derive Academic Group via Class", $passed, $failed);
while ($row = $div_query->fetch_assoc()) {
    assert_true(!empty($row['group_name']), "Division {$row['division_name']} (Class {$row['class_name']}) derives Group {$row['group_name']}", $passed, $failed);
}

// -------------------------------------------------------------
// Test 6: Verify Division uniqueness within Class (Class + Division)
// -------------------------------------------------------------
echo "\n6. Verify Division uniqueness within Class\n";
$dup_div = $mysqli->query("
    SELECT class_id, division_name, COUNT(*) as cnt 
    FROM tbl_divisions 
    WHERE is_deleted = 'n'
    GROUP BY class_id, division_name 
    HAVING cnt > 1
");
assert_true($dup_div->num_rows === 0, "No duplicate divisions exist within the same Class", $passed, $failed);

// Verify that different classes CAN share the same division name (e.g. Division A in multiple classes)
$shared_div = $mysqli->query("
    SELECT division_name, COUNT(DISTINCT class_id) as class_cnt 
    FROM tbl_divisions 
    WHERE is_deleted = 'n' AND division_name = 'A'
");
$row_shared = $shared_div->fetch_assoc();
assert_true($row_shared && $row_shared['class_cnt'] >= 1, "Division 'A' is scoped per Class without global collision", $passed, $failed);

// -------------------------------------------------------------
// Test 7: Verify Academic Hierarchy Query
// -------------------------------------------------------------
echo "\n7. Verify Complete Academic Hierarchy Query\n";
$hierarchy = $mysqli->query("
    SELECT ag.academic_group_id, ag.group_name, c.class_id, c.class_name, 
           GROUP_CONCAT(d.division_name ORDER BY d.division_name SEPARATOR ', ') as divisions
    FROM tbl_academic_groups ag
    JOIN tbl_classes c ON c.academic_group_id = ag.academic_group_id AND c.is_deleted = 'n'
    LEFT JOIN tbl_divisions d ON d.class_id = c.class_id AND d.is_deleted = 'n'
    WHERE ag.is_deleted = 'n'
    GROUP BY ag.academic_group_id, ag.group_name, c.class_id, c.class_name
    ORDER BY ag.display_order ASC, c.class_id ASC
");
assert_true($hierarchy->num_rows > 0, "Complete Academic Hierarchy query returns records", $passed, $failed);
echo "   Sample Hierarchy Records:\n";
while ($h = $hierarchy->fetch_assoc()) {
    echo "     - [{$h['group_name']}] {$h['class_name']} &rarr; Divisions: [{$h['divisions']}]\n";
}

// -------------------------------------------------------------
// Test 8: Verify Soft Delete Behavior
// -------------------------------------------------------------
echo "\n8. Verify Soft Delete & Status Schema\n";
$sd_check = $mysqli->query("SHOW COLUMNS FROM tbl_academic_groups LIKE 'is_deleted'");
assert_true($sd_check->num_rows === 1, "tbl_academic_groups has is_deleted column for soft delete", $passed, $failed);

$status_check = $mysqli->query("SHOW COLUMNS FROM tbl_academic_groups LIKE 'status'");
assert_true($status_check->num_rows === 1, "tbl_academic_groups has status column for enable/disable", $passed, $failed);

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n============================================================\n";
echo " TEST SUMMARY\n";
echo " Passed: {$passed}\n";
echo " Failed: {$failed}\n";
echo "============================================================\n";

$mysqli->close();
exit($failed > 0 ? 1 : 0);
