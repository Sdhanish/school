<?php
/**
 * E2E Automated Test Suite: Class Teacher Assignment During Class & Division Creation
 *
 * Verifies:
 * 1. GET academics/classes includes AVAILABLE_TEACHERS JSON data.
 * 2. POST academics/classes (action=add) creates class, divisions, and tbl_class_teachers entries.
 * 3. Verifies tbl_divisions.class_teacher_id is set and synchronized.
 * 4. Verifies tbl_class_teachers contains the active allocation records.
 * 5. Verifies academics/class_teachers page displays the new assignments.
 * 6. Verifies GET academics/classes renders division pills with class teacher names.
 * 7. POST academics/classes (action=edit) correctly updates, unassigns, and adds new division teacher assignments.
 * 8. Validation: Duplicate division names are rejected.
 * 9. Clean up test records.
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_cls_teacher_');

function makeReq($url, $postData = null, $cookie = null, $isAjax = false) {
    global $cookieFile;
    $cFile = $cookie ?: $cookieFile;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cFile);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $headers = [];
    if ($isAjax) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

function getCsrfFromHtml($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    if (preg_match('/window\.CSRF_TOKEN_NAME\s*=\s*"([^"]+)";.*?window\.CSRF_HASH\s*=\s*"([^"]+)";/s', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$passed = 0;
$total = 0;

function assertCheck($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
    }
}

$mysqli = new mysqli('127.0.0.1', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error);
}

echo "=======================================================\n";
echo "1. Login Super Admin\n";
echo "=======================================================\n";

$loginPage = makeReq($baseUrl . '/auth/login');
$csrf = getCsrfFromHtml($loginPage['body']);
$loginResp = makeReq($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);
assertCheck("Super Admin login successful", $loginResp['code'] === 200);

echo "\n=======================================================\n";
echo "2. Check Academics/Classes View & Teacher Data\n";
echo "=======================================================\n";

$classesPage = makeReq($baseUrl . '/academics/classes');
assertCheck("Classes page loads HTTP 200", $classesPage['code'] === 200);
assertCheck("Classes page contains AVAILABLE_TEACHERS JS array", strpos($classesPage['body'], 'const AVAILABLE_TEACHERS =') !== false);
assertCheck("Classes page contains Divisions & Class Teachers section", strpos($classesPage['body'], 'Divisions & Class Teachers') !== false);
assertCheck("Classes page contains select-division-teacher", strpos($classesPage['body'], 'select-division-teacher') !== false);

// Find active teachers from tbl_staff who are unassigned in Year 1
$tRes = $mysqli->query("
    SELECT s.staff_id, s.full_name, s.employee_code 
    FROM tbl_staff s 
    WHERE (s.staff_type = 'teacher' OR s.category = 'Teaching') 
      AND s.status = 1 
      AND s.is_deleted = 'n'
      AND s.staff_id NOT IN (
          SELECT ct.staff_id 
          FROM tbl_class_teachers ct 
          WHERE ct.academic_year_id = 1 AND ct.status = 1 AND ct.is_deleted = 'n'
      )
    LIMIT 3
");
$teachers = $tRes->fetch_all(MYSQLI_ASSOC);
if (count($teachers) < 3) {
    // Insert temporary teachers if needed
    while (count($teachers) < 3) {
        $idx = count($teachers) + 1;
        $mysqli->query("INSERT INTO tbl_staff (full_name, employee_code, staff_type, category, status, is_deleted) VALUES ('Auto Test Teacher $idx', 'ATT00$idx', 'teacher', 'Teaching', 1, 'n')");
        $teachers[] = [
            'staff_id'      => $mysqli->insert_id,
            'full_name'     => "Auto Test Teacher $idx",
            'employee_code' => "ATT00$idx"
        ];
    }
}
$teacher1 = $teachers[0];
$teacher2 = $teachers[1];
$teacher3 = $teachers[2];
echo "  [INFO] Using Teacher 1: ID={$teacher1['staff_id']} ({$teacher1['full_name']}), Teacher 2: ID={$teacher2['staff_id']} ({$teacher2['full_name']}), Teacher 3: ID={$teacher3['staff_id']} ({$teacher3['full_name']})\n";

// Cleanup any leftover test class from previous runs
$cleanName = 'TEST_GRADE_X_AUTO';
$resCls = $mysqli->query("SELECT class_id FROM tbl_classes WHERE class_name = '$cleanName'");
while ($r = $resCls->fetch_assoc()) {
    $cid = (int)$r['class_id'];
    $mysqli->query("DELETE FROM tbl_class_teachers WHERE class_id = $cid");
    $mysqli->query("DELETE FROM tbl_divisions WHERE class_id = $cid");
    $mysqli->query("DELETE FROM tbl_classes WHERE class_id = $cid");
}

echo "\n=======================================================\n";
echo "3. Create Class with Divisions & Class Teachers (action=add)\n";
echo "=======================================================\n";

$csrf = getCsrfFromHtml($classesPage['body']);
$postAdd = [
    $csrf['name']            => $csrf['hash'],
    'action'                 => 'add',
    'academic_year_id'       => 1,
    'academic_group_id'      => 1,
    'class_name'             => $cleanName,
    'class_code'             => 'CLS-TESTX',
    'capacity'               => 35,
    'description'            => 'Automated test class with assigned teachers',
    'division_names'         => ['A', 'B', 'C'],
    'division_teacher_ids'   => [$teacher1['staff_id'], $teacher2['staff_id'], '']
];

$addResp = makeReq($baseUrl . '/academics/classes', $postAdd);
assertCheck("Add class request executed HTTP 200", $addResp['code'] === 200);

// Verify in DB
$cRow = $mysqli->query("SELECT * FROM tbl_classes WHERE class_name = '$cleanName' AND is_deleted = 'n'")->fetch_assoc();
assertCheck("Class record inserted in tbl_classes", !empty($cRow));
$testClassId = (int)($cRow['class_id'] ?? 0);

$divRows = $mysqli->query("SELECT * FROM tbl_divisions WHERE class_id = $testClassId AND is_deleted = 'n' ORDER BY division_name ASC")->fetch_all(MYSQLI_ASSOC);
assertCheck("Exactly 3 divisions created in tbl_divisions", count($divRows) === 3);

$divA = null;
$divB = null;
$divC = null;
foreach ($divRows as $dr) {
    if ($dr['division_name'] === 'A') $divA = $dr;
    if ($dr['division_name'] === 'B') $divB = $dr;
    if ($dr['division_name'] === 'C') $divC = $dr;
}

assertCheck("Division A has class_teacher_id = {$teacher1['staff_id']}", (int)($divA['class_teacher_id'] ?? 0) === (int)$teacher1['staff_id']);
assertCheck("Division B has class_teacher_id = {$teacher2['staff_id']}", (int)($divB['class_teacher_id'] ?? 0) === (int)$teacher2['staff_id']);
assertCheck("Division C has class_teacher_id = NULL / 0", empty($divC['class_teacher_id']));

// Verify tbl_class_teachers
$ctA = $mysqli->query("SELECT * FROM tbl_class_teachers WHERE class_id = $testClassId AND division_id = {$divA['division_id']} AND status = 1 AND is_deleted = 'n'")->fetch_assoc();
assertCheck("tbl_class_teachers has active record for Div A with teacher 1", !empty($ctA) && (int)$ctA['staff_id'] === (int)$teacher1['staff_id']);

$ctB = $mysqli->query("SELECT * FROM tbl_class_teachers WHERE class_id = $testClassId AND division_id = {$divB['division_id']} AND status = 1 AND is_deleted = 'n'")->fetch_assoc();
assertCheck("tbl_class_teachers has active record for Div B with teacher 2", !empty($ctB) && (int)$ctB['staff_id'] === (int)$teacher2['staff_id']);

$ctC = $mysqli->query("SELECT * FROM tbl_class_teachers WHERE class_id = $testClassId AND division_id = {$divC['division_id']} AND status = 1 AND is_deleted = 'n'")->fetch_assoc();
assertCheck("tbl_class_teachers has NO record for unassigned Div C", empty($ctC));

echo "\n=======================================================\n";
echo "4. Verify Classes & Class Teachers UI Views\n";
echo "=======================================================\n";

$classesViewResp = makeReq($baseUrl . '/academics/classes');
assertCheck("Classes list shows Div A with Teacher 1 name", strpos($classesViewResp['body'], $teacher1['full_name']) !== false);
assertCheck("Classes list shows Div B with Teacher 2 name", strpos($classesViewResp['body'], $teacher2['full_name']) !== false);

$teachersViewResp = makeReq($baseUrl . '/academics/class_teachers');
assertCheck("academics/class_teachers loads HTTP 200", $teachersViewResp['code'] === 200);
assertCheck("academics/class_teachers displays new class allocation for Div A", strpos($teachersViewResp['body'], $cleanName) !== false);

echo "\n=======================================================\n";
echo "5. Edit Class: Reassign, Unassign, and Add New Division (action=edit)\n";
echo "=======================================================\n";

// In edit:
// Div A: reassign to Teacher 2
// Div B: unassign (empty teacher)
// Div C: assign to Teacher 1
// Div D: new division assigned to Teacher 2
$csrf = getCsrfFromHtml($classesViewResp['body']);
$postEdit = [
    $csrf['name']            => $csrf['hash'],
    'action'                 => 'edit',
    'class_id'               => $testClassId,
    'academic_year_id'       => 1,
    'academic_group_id'      => 1,
    'class_name'             => $cleanName,
    'class_code'             => 'CLS-TESTX',
    'capacity'               => 40,
    'description'            => 'Updated automated test class',
    'division_ids'           => [$divA['division_id'], $divB['division_id'], $divC['division_id'], ''],
    'division_names'         => ['A', 'B', 'C', 'D'],
    'division_teacher_ids'   => [$teacher2['staff_id'], '', $teacher1['staff_id'], $teacher3['staff_id']],
    'deleted_division_ids'   => ''
];

$editResp = makeReq($baseUrl . '/academics/classes', $postEdit);
assertCheck("Edit class request executed HTTP 200", $editResp['code'] === 200);

// Verify updated DB state
$divAUpdated = $mysqli->query("SELECT * FROM tbl_divisions WHERE division_id = {$divA['division_id']}")->fetch_assoc();
assertCheck("Div A class_teacher_id updated to Teacher 2 ({$teacher2['staff_id']})", (int)$divAUpdated['class_teacher_id'] === (int)$teacher2['staff_id']);
$ctAUpdated = $mysqli->query("SELECT * FROM tbl_class_teachers WHERE class_id = $testClassId AND division_id = {$divA['division_id']} AND status = 1 AND is_deleted = 'n'")->fetch_assoc();
assertCheck("tbl_class_teachers Div A active record updated to Teacher 2", !empty($ctAUpdated) && (int)$ctAUpdated['staff_id'] === (int)$teacher2['staff_id']);

$divBUpdated = $mysqli->query("SELECT * FROM tbl_divisions WHERE division_id = {$divB['division_id']}")->fetch_assoc();
assertCheck("Div B class_teacher_id cleared to NULL/0", empty($divBUpdated['class_teacher_id']));
$ctBUpdated = $mysqli->query("SELECT * FROM tbl_class_teachers WHERE class_id = $testClassId AND division_id = {$divB['division_id']} AND status = 1 AND is_deleted = 'n'")->fetch_assoc();
assertCheck("tbl_class_teachers Div B active record removed/soft-deleted", empty($ctBUpdated));

$divCUpdated = $mysqli->query("SELECT * FROM tbl_divisions WHERE division_id = {$divC['division_id']}")->fetch_assoc();
assertCheck("Div C class_teacher_id assigned to Teacher 1 ({$teacher1['staff_id']})", (int)$divCUpdated['class_teacher_id'] === (int)$teacher1['staff_id']);

$divD = $mysqli->query("SELECT * FROM tbl_divisions WHERE class_id = $testClassId AND division_name = 'D' AND is_deleted = 'n'")->fetch_assoc();
assertCheck("New Div D created", !empty($divD));
assertCheck("New Div D assigned to Teacher 3 ({$teacher3['staff_id']})", !empty($divD) && (int)$divD['class_teacher_id'] === (int)$teacher3['staff_id']);

echo "\n=======================================================\n";
echo "6. Validation: Duplicate Division Names Rejected\n";
echo "=======================================================\n";

$classesPage = makeReq($baseUrl . '/academics/classes');
$csrf = getCsrfFromHtml($classesPage['body']);
$postDup = [
    $csrf['name']            => $csrf['hash'],
    'action'                 => 'add',
    'academic_year_id'       => 1,
    'academic_group_id'      => 1,
    'class_name'             => 'TEST_DUP_CLASS',
    'class_code'             => 'CLS-TESTDUP',
    'capacity'               => 30,
    'division_names'         => ['A', 'A'],
    'division_teacher_ids'   => ['', '']
];
$dupResp = makeReq($baseUrl . '/academics/classes', $postDup);
$dupCheck = $mysqli->query("SELECT * FROM tbl_classes WHERE class_name = 'TEST_DUP_CLASS'")->fetch_assoc();
assertCheck("Duplicate divisions rejected, class not created", empty($dupCheck));

echo "\n=======================================================\n";
echo "7. Cleanup Test Records\n";
echo "=======================================================\n";

$mysqli->query("DELETE FROM tbl_class_teachers WHERE class_id = $testClassId");
$mysqli->query("DELETE FROM tbl_divisions WHERE class_id = $testClassId");
$mysqli->query("DELETE FROM tbl_classes WHERE class_id = $testClassId");

$remCheck = $mysqli->query("SELECT * FROM tbl_classes WHERE class_id = $testClassId")->fetch_assoc();
assertCheck("Test class and divisions cleaned up cleanly", empty($remCheck));

@unlink($cookieFile);

echo "\n=======================================================\n";
echo "E2E Test Results: $passed / $total Passed\n";
echo "=======================================================\n";

if ($passed !== $total) {
    exit(1);
}
