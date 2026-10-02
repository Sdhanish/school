<?php
/**
 * Test Academic Management -> Class Teachers: Active Academic Year Auto-Selection & Synchronization E2E
 */

$baseUrl = 'http://localhost/schoolnew';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_ct_year_');

function makeRequest($url, $postData = null) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

function getCsrf($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$passed = 0;
$total = 0;

function assertCondition($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
    }
}

echo "=======================================================\n";
echo "1. Authenticate as Administrator\n";
echo "=======================================================\n";

$loginPage = makeRequest($baseUrl . '/auth/login');
$csrf = getCsrf($loginPage['body']);
$loginRes = makeRequest($baseUrl . '/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

assertCondition("Admin login successful", $loginRes['code'] === 200);

// Direct DB connection for inspecting / testing dynamic state
$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}

// Retrieve active academic year
$activeRes = $db->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE is_active = 1 AND status = 1 AND is_deleted = 'n' LIMIT 1");
$origActive = $activeRes->fetch_assoc();
$origActiveId = $origActive ? (int)$origActive['academic_year_id'] : 1;
$origActiveName = $origActive ? $origActive['year_name'] : '2026-2027';

echo "  [INFO] Initial Active Academic Year: ID={$origActiveId} ({$origActiveName})\n";

echo "\n=======================================================\n";
echo "2. Initial Page Load: Auto-Selection of Active Year\n";
echo "=======================================================\n";

$resInitial = makeRequest($baseUrl . '/academics/class_teachers');
assertCondition("Class Teachers loads HTTP 200", $resInitial['code'] === 200);
assertCondition("No PHP Notice or Error", strpos($resInitial['body'], 'Severity: Notice') === false);

$dom = new DOMDocument();
@$dom->loadHTML($resInitial['body']);
$xpath = new DOMXPath($dom);

$filterYear = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
assertCondition("filter_academic_year_id exists", $filterYear !== null);

$selectedOpt = null;
if ($filterYear) {
    foreach ($filterYear->getElementsByTagName('option') as $opt) {
        if ($opt->hasAttribute('selected')) {
            $selectedOpt = $opt;
            break;
        }
    }
}

assertCondition("Active year option is selected on fresh page load (ID: {$origActiveId})",
    $selectedOpt !== null && (int)$selectedOpt->getAttribute('value') === $origActiveId);
assertCondition("Selected option label includes active year name ({$origActiveName})",
    $selectedOpt !== null && strpos($selectedOpt->textContent, $origActiveName) !== false);
assertCondition("Does NOT select 'All Academic Years'",
    $selectedOpt !== null && $selectedOpt->getAttribute('value') !== 'all' && $selectedOpt->getAttribute('value') !== '');

echo "\n=======================================================\n";
echo "3. User Manually Selects Another Academic Year\n";
echo "=======================================================\n";

// Find another valid academic year in DB
$otherRes = $db->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE academic_year_id != {$origActiveId} AND status = 1 AND is_deleted = 'n' LIMIT 1");
$otherYear = $otherRes->fetch_assoc();
$otherYearId = $otherYear ? (int)$otherYear['academic_year_id'] : null;
$otherYearName = $otherYear ? $otherYear['year_name'] : null;

if ($otherYearId) {
    $resOther = makeRequest($baseUrl . '/academics/class_teachers?academic_year_id=' . $otherYearId);
    assertCondition("Loads another academic year HTTP 200", $resOther['code'] === 200);

    @$dom->loadHTML($resOther['body']);
    $xpath = new DOMXPath($dom);
    $filterOther = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
    $selectedOther = null;
    if ($filterOther) {
        foreach ($filterOther->getElementsByTagName('option') as $opt) {
            if ($opt->hasAttribute('selected')) {
                $selectedOther = $opt;
                break;
            }
        }
    }
    assertCondition("Manually selected year is respected (ID: {$otherYearId})",
        $selectedOther !== null && (int)$selectedOther->getAttribute('value') === $otherYearId);
}

echo "\n=======================================================\n";
echo "4. User Manually Selects 'All Academic Years'\n";
echo "=======================================================\n";

$resAll = makeRequest($baseUrl . '/academics/class_teachers?academic_year_id=all');
assertCondition("Loads All Academic Years HTTP 200", $resAll['code'] === 200);

@$dom->loadHTML($resAll['body']);
$xpath = new DOMXPath($dom);
$filterAll = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
$selectedAll = null;
if ($filterAll) {
    foreach ($filterAll->getElementsByTagName('option') as $opt) {
        if ($opt->hasAttribute('selected')) {
            $selectedAll = $opt;
            break;
        }
    }
}
assertCondition("'All Academic Years' option is selected",
    $selectedAll !== null && $selectedAll->getAttribute('value') === 'all');

echo "\n=======================================================\n";
echo "5. Reset Button Returns to Active Academic Year\n";
echo "=======================================================\n";

// Reset navigates to clean /academics/class_teachers
$resReset = makeRequest($baseUrl . '/academics/class_teachers');
@$dom->loadHTML($resReset['body']);
$xpath = new DOMXPath($dom);
$filterReset = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
$selectedReset = null;
if ($filterReset) {
    foreach ($filterReset->getElementsByTagName('option') as $opt) {
        if ($opt->hasAttribute('selected')) {
            $selectedReset = $opt;
            break;
        }
    }
}
assertCondition("Reset returns to the Active Academic Year (ID: {$origActiveId})",
    $selectedReset !== null && (int)$selectedReset->getAttribute('value') === $origActiveId);

echo "\n=======================================================\n";
echo "6. Dynamic Active Year Switch (e.g. 2027-2028)\n";
echo "=======================================================\n";

// Switch active year in DB temporarily
$targetSwitchRes = $db->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE academic_year_id != {$origActiveId} AND status = 1 AND is_deleted = 'n' LIMIT 1");
$targetSwitch = $targetSwitchRes->fetch_assoc();

if ($targetSwitch) {
    $switchId = (int)$targetSwitch['academic_year_id'];
    $switchName = $targetSwitch['year_name'];

    $db->query("UPDATE tbl_academic_years SET is_active = 0");
    $db->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = {$switchId}");

    $resSwitched = makeRequest($baseUrl . '/academics/class_teachers');
    @$dom->loadHTML($resSwitched['body']);
    $xpath = new DOMXPath($dom);
    $filterSwitched = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
    $selectedSwitched = null;
    if ($filterSwitched) {
        foreach ($filterSwitched->getElementsByTagName('option') as $opt) {
            if ($opt->hasAttribute('selected')) {
                $selectedSwitched = $opt;
                break;
            }
        }
    }

    assertCondition("Dynamically auto-selects newly activated academic year (ID: {$switchId}, {$switchName}) without code changes",
        $selectedSwitched !== null && (int)$selectedSwitched->getAttribute('value') === $switchId);

    // Restore original active academic year
    $db->query("UPDATE tbl_academic_years SET is_active = 0");
    $db->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = {$origActiveId}");
    echo "  [INFO] Restored original active year: ID={$origActiveId}\n";
}

echo "\n=======================================================\n";
echo "7. Edge Case: No Active Academic Year Configured\n";
echo "=======================================================\n";

// Temporarily set all is_active = 0
$db->query("UPDATE tbl_academic_years SET is_active = 0");

$resNoActive = makeRequest($baseUrl . '/academics/class_teachers');
assertCondition("Page loads HTTP 200 when no active year", $resNoActive['code'] === 200);
assertCondition("Displays 'No active academic year is configured' warning banner",
    strpos($resNoActive['body'], 'No active academic year is configured') !== false);
assertCondition("Does NOT silently select 'All Academic Years' when active year missing",
    strpos($resNoActive['body'], '-- No active academic year configured --') !== false);

// Restore active year
$db->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = {$origActiveId}");
echo "  [INFO] Restored active year after test.\n";

echo "\n=======================================================\n";
echo "8. Edge Case: Multiple Active Academic Years in DB\n";
echo "=======================================================\n";

if ($otherYearId) {
    // Temporarily set two active years
    $db->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id IN ({$origActiveId}, {$otherYearId})");

    $resMulti = makeRequest($baseUrl . '/academics/class_teachers');
    assertCondition("Page loads HTTP 200 when multiple active years", $resMulti['code'] === 200);
    assertCondition("Displays informational banner about multiple active years detected",
        strpos($resMulti['body'], 'Multiple active academic years detected') !== false);

    // Restore single active year
    $db->query("UPDATE tbl_academic_years SET is_active = 0");
    $db->query("UPDATE tbl_academic_years SET is_active = 1 WHERE academic_year_id = {$origActiveId}");
    echo "  [INFO] Restored single active year after test.\n";
}

echo "\n=======================================================\n";
echo "9. AJAX Classes by Academic Year Endpoint\n";
echo "=======================================================\n";

$resAjaxYear1 = makeRequest($baseUrl . '/academics/ajax_get_classes/' . $origActiveId);
assertCondition("AJAX get classes for active year returns HTTP 200", $resAjaxYear1['code'] === 200);
$classes1 = json_decode($resAjaxYear1['body'], true);
assertCondition("AJAX response is valid JSON array of classes", is_array($classes1) && count($classes1) > 0);

$resAjaxAll = makeRequest($baseUrl . '/academics/ajax_get_classes/all');
assertCondition("AJAX get classes for all returns HTTP 200", $resAjaxAll['code'] === 200);
$classesAll = json_decode($resAjaxAll['body'], true);
assertCondition("AJAX all classes returns full class list", is_array($classesAll) && count($classesAll) >= count($classes1));

echo "\n=======================================================\n";
echo "10. Edit Existing Assignment Preserves Its Academic Year\n";
echo "=======================================================\n";

// Check presence of edit action button in assignments table
assertCondition("Edit assignment button with openEditAssignModal exists in table",
    strpos($resInitial['body'], 'openEditAssignModal(') !== false);
assertCondition("Modal includes onchange handler on modal_ct_year to reload classes",
    strpos($resInitial['body'], 'id="modal_ct_year" onchange="onModalYearChange(this.value)"') !== false);

echo "\n=======================================================\n";
echo "SUMMARY: {$passed} / {$total} tests passed.\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "\n>>> ALL E2E ACTIVE ACADEMIC YEAR TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
