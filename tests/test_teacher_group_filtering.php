<?php
define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('VIEWPATH', APPPATH . 'views/');

require_once BASEPATH . 'core/Common.php';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../index.php';

require_once APPPATH . 'config/constants.php';

$GLOBALS['CFG'] =& load_class('Config', 'core');
$GLOBALS['UNI'] =& load_class('Utf8', 'core');
$GLOBALS['SEC'] =& load_class('Security', 'core');

function &get_instance() {
    return CI_Controller::get_instance();
}

require_once BASEPATH . 'core/Controller.php';
$ci = new CI_Controller();

$db = DB();
$ci->db = $db;

$ci->load->model('Staff_model');
$ci->load->model('Academic_group_model');
$ci->load->model('Class_model');
$ci->load->model('Division_model');
$ci->load->model('Class_teacher_model');

echo "========================================================\n";
echo "GLOBAL TEACHER / FACULTY GROUP FILTERING TEST SUITE\n";
echo "========================================================\n\n";

$pass_count = 0;
$fail_count = 0;

function assert_test($description, $condition, $details = '') {
    global $pass_count, $fail_count;
    if ($condition) {
        echo "[PASS] " . $description . "\n";
        $pass_count++;
    } else {
        echo "[FAIL] " . $description . " - Details: " . $details . "\n";
        $fail_count++;
    }
}

// 0. Fetch existing groups
$groups = $ci->Academic_group_model->get_all();
assert_test("Groups exist in database", count($groups) >= 2, "Found: " . count($groups));

$up_group = null;
$lp_group = null;
foreach ($groups as $g) {
    if (stripos($g->group_name, 'UP') !== false) {
        $up_group = $g;
    }
    if (stripos($g->group_name, 'LP') !== false) {
        $lp_group = $g;
    }
}
if (!$up_group && isset($groups[0])) $up_group = $groups[0];
if (!$lp_group && isset($groups[1])) $lp_group = $groups[1];

echo "Using UP Group: ID={$up_group->academic_group_id} ({$up_group->group_name})\n";
echo "Using LP Group: ID={$lp_group->academic_group_id} ({$lp_group->group_name})\n\n";

// TEST 1: Select Group = UP -> Returns ONLY UP teachers, no other groups
$up_teachers = $ci->Staff_model->get_teachers_by_group($up_group->academic_group_id);
assert_test("TEST 1: Teachers returned for UP group", count($up_teachers) > 0, "Count: " . count($up_teachers));
$all_up_match = true;
foreach ($up_teachers as $t) {
    if ((int)$t->academic_group_id !== (int)$up_group->academic_group_id) {
        $all_up_match = false;
        break;
    }
}
assert_test("TEST 1: All returned teachers strictly belong to UP group (no KG, LP, HS)", $all_up_match);

// TEST 2: Select Group = LP -> Returns ONLY LP teachers
$lp_teachers = $ci->Staff_model->get_teachers_by_group($lp_group->academic_group_id);
assert_test("TEST 2: Teachers returned for LP group", count($lp_teachers) > 0, "Count: " . count($lp_teachers));
$all_lp_match = true;
foreach ($lp_teachers as $t) {
    if ((int)$t->academic_group_id !== (int)$lp_group->academic_group_id) {
        $all_lp_match = false;
        break;
    }
}
assert_test("TEST 2: All returned teachers strictly belong to LP group", $all_lp_match);

// Verify UP and LP teacher sets are completely disjoint
$up_ids = array_map(function($t) { return $t->staff_id; }, $up_teachers);
$lp_ids = array_map(function($t) { return $t->staff_id; }, $lp_teachers);
$intersect = array_intersect($up_ids, $lp_ids);
assert_test("TEST 1 & 2: UP and LP teacher pools have no overlap", empty($intersect));

// TEST 3: No group selected -> Returns empty array (frontend disables dropdown)
$empty_teachers = $ci->Staff_model->get_teachers_by_group(null);
assert_test("TEST 3: When no group selected, get_teachers_by_group returns empty array", empty($empty_teachers));

$zero_group_teachers = $ci->Staff_model->get_teachers_by_group(0);
assert_test("TEST 3: When group ID is 0, returns empty array", empty($zero_group_teachers));

// TEST 4: Group UP -> LP transition
// If a user selects UP Teacher A, and then switches group to LP:
// Teacher A must be identified as NOT belonging to LP
$up_teacher_a = $up_teachers[0];
$val_switch = $ci->Staff_model->validate_teacher_group($up_teacher_a->staff_id, $lp_group->academic_group_id);
assert_test("TEST 4: Old UP teacher fails validation against new LP group", !$val_switch['valid'], $val_switch['error'] ?? '');

// TEST 5: Select Group = UP, Class = UP Class -> Only UP teachers valid for that class
$up_classes = $ci->Class_model->get_by_group($up_group->academic_group_id);
if (!empty($up_classes)) {
    $up_class = $up_classes[0];
    $val_tc_match = $ci->Staff_model->validate_teacher_class_group($up_teacher_a->staff_id, $up_class->class_id);
    assert_test("TEST 5: UP Teacher with UP Class is valid", $val_tc_match['valid']);

    $lp_teacher_b = $lp_teachers[0];
    $val_tc_mismatch = $ci->Staff_model->validate_teacher_class_group($lp_teacher_b->staff_id, $up_class->class_id);
    assert_test("TEST 5: LP Teacher with UP Class is strictly rejected", !$val_tc_mismatch['valid'], $val_tc_mismatch['error'] ?? '');
} else {
    assert_test("TEST 5: Classes exist for UP group", false, "No classes under UP group");
}

// TEST 6: Try manually submitting Group = UP, Teacher = LP Teacher -> Backend rejects
$val_tamper = $ci->Staff_model->validate_teacher_group($lp_teachers[0]->staff_id, $up_group->academic_group_id);
assert_test("TEST 6: Backend server-side validation rejects LP teacher submitted for UP group", !$val_tamper['valid']);
assert_test("TEST 6: Error message clearly mentions group mismatch", stripos($val_tamper['error'], 'belongs to Department / Group') !== false);

// TEST 7: Edit existing record: Group = UP, Teacher = UP Teacher A -> Valid
$val_edit_same = $ci->Staff_model->validate_teacher_group($up_teacher_a->staff_id, $up_group->academic_group_id);
assert_test("TEST 7: Pre-selected UP teacher in UP group is valid on edit load", $val_edit_same['valid']);

// TEST 8: Edit existing record: Change UP -> LP requires selecting LP teacher
$lp_teacher_a = $lp_teachers[0];
$val_edit_change = $ci->Staff_model->validate_teacher_group($lp_teacher_a->staff_id, $lp_group->academic_group_id);
assert_test("TEST 8: Selecting valid LP teacher for updated LP group succeeds", $val_edit_change['valid']);

// TEST 9: Teaching Staff filter integrity (Only active Teaching staff, no Non-Teaching staff)
$non_teaching = $ci->db
    ->select('staff_id, full_name, staff_type, category')
    ->from('tbl_staff')
    ->where('is_deleted', 'n')
    ->where('staff_type !=', 'Teacher')
    ->where('category !=', 'Teaching')
    ->limit(1)
    ->get()
    ->row();

if ($non_teaching) {
    $val_non_teach = $ci->Staff_model->validate_teacher_group($non_teaching->staff_id, $up_group->academic_group_id);
    assert_test("TEST 9: Non-teaching staff is rejected by validate_teacher_group", !$val_non_teach['valid'], $val_non_teach['error'] ?? '');
} else {
    echo "[INFO] No non-teaching staff found to test rejection, skipping non-teaching check.\n";
}

echo "\n========================================================\n";
echo "SUMMARY: {$pass_count} PASSED, {$fail_count} FAILED\n";
echo "========================================================\n";

if ($fail_count > 0) {
    exit(1);
} else {
    exit(0);
}
