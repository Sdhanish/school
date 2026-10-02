<?php
/**
 * Test Academic Year Filter & Undefined Variable Fix in Subject Teachers
 *
 * Verifies:
 * 1. No PHP notices/errors (specifically "Undefined variable: selected_year_id")
 * 2. Academic Year dropdown options are clean and contain no error text
 * 3. Default academic year is dynamically selected from active year (never hardcoded)
 * 4. "All Academic Years" selection works without notices
 * 5. Specific academic year selection works without notices
 * 6. Filter preservation across combinations
 */


$baseUrl = 'http://localhost/schoolnew';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_year_');

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

// Connect to DB directly to check active academic year dynamically
$db = new mysqli('localhost', 'root', '', 'db_school');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}

$activeYearRes = $db->query("SELECT academic_year_id, year_name FROM tbl_academic_years WHERE is_active = 1 LIMIT 1");
$activeYear = $activeYearRes->fetch_assoc();
$activeYearId = $activeYear ? $activeYear['academic_year_id'] : null;
$activeYearName = $activeYear ? $activeYear['year_name'] : null;

echo "  [INFO] Active Academic Year in DB: ID={$activeYearId} ({$activeYearName})\n";

echo "\n=======================================================\n";
echo "2. Direct Access (No Query Params) - Academic Year Default & Notice Check\n";
echo "=======================================================\n";

$res1 = makeRequest($baseUrl . '/academics/subject_teachers');

assertCondition("Subject Teachers loads with HTTP 200", $res1['code'] === 200);
assertCondition("No 'Undefined variable: selected_year_id' notice", strpos($res1['body'], 'Undefined variable: selected_year_id') === false);
assertCondition("No 'Undefined variable' notice of any kind", strpos($res1['body'], 'Undefined variable') === false);
assertCondition("No 'Severity: Notice' in page output", strpos($res1['body'], 'Severity: Notice') === false);
assertCondition("No PHP error text inside dropdown options", !preg_match('/<option[^>]*>.*?(?:PHP Error|Severity:|Notice:).*?<\/option>/is', $res1['body']));

// Check that Academic Year dropdown has selected attribute on the active year
$dom = new DOMDocument();
@$dom->loadHTML($res1['body']);
$xpath = new DOMXPath($dom);
$yearSelect = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);

assertCondition("filter_academic_year_id select element exists", $yearSelect !== null);

if ($yearSelect) {
    $selectedOption = null;
    $options = $yearSelect->getElementsByTagName('option');
    foreach ($options as $opt) {
        if ($opt->hasAttribute('selected')) {
            $selectedOption = $opt;
            break;
        }
    }
    assertCondition("Active academic year is dynamically selected by default (ID: $activeYearId)", 
        $selectedOption !== null && $selectedOption->getAttribute('value') == $activeYearId);
}

echo "\n=======================================================\n";
echo "3. Explicit 'All Academic Years' Filter (academic_year_id=)\n";
echo "=======================================================\n";

$res2 = makeRequest($baseUrl . '/academics/subject_teachers?academic_year_id=');

assertCondition("Page loads with HTTP 200", $res2['code'] === 200);
assertCondition("No 'Undefined variable' notice", strpos($res2['body'], 'Undefined variable') === false);
assertCondition("No 'Severity: Notice' in output", strpos($res2['body'], 'Severity: Notice') === false);

@$dom->loadHTML($res2['body']);
$xpath = new DOMXPath($dom);
$yearSelect2 = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
if ($yearSelect2) {
    $selectedOption2 = null;
    $options2 = $yearSelect2->getElementsByTagName('option');
    foreach ($options2 as $opt) {
        if ($opt->hasAttribute('selected')) {
            $selectedOption2 = $opt;
            break;
        }
    }
    assertCondition("'All Academic Years' (empty value) is selected when explicitly chosen", 
        $selectedOption2 === null || $selectedOption2->getAttribute('value') === '');
}

echo "\n=======================================================\n";
echo "4. Explicit Specific Academic Year Filter\n";
echo "=======================================================\n";

// Get all years
$allYears = $db->query("SELECT academic_year_id, year_name FROM tbl_academic_years ORDER BY academic_year_id ASC");
$yearsList = [];
while ($row = $allYears->fetch_assoc()) {
    $yearsList[] = $row;
}

if (count($yearsList) > 0) {
    $testYear = $yearsList[0];
    $res3 = makeRequest($baseUrl . '/academics/subject_teachers?academic_year_id=' . $testYear['academic_year_id']);

    assertCondition("Specific year page loads with HTTP 200", $res3['code'] === 200);
    assertCondition("No notices on specific year filter", strpos($res3['body'], 'Undefined variable') === false);

    @$dom->loadHTML($res3['body']);
    $xpath = new DOMXPath($dom);
    $yearSelect3 = $xpath->query('//select[@id="filter_academic_year_id"]')->item(0);
    $selectedOption3 = null;
    if ($yearSelect3) {
        foreach ($yearSelect3->getElementsByTagName('option') as $opt) {
            if ($opt->hasAttribute('selected')) {
                $selectedOption3 = $opt;
                break;
            }
        }
    }
    assertCondition("Specific academic year is selected (ID: {$testYear['academic_year_id']})", 
        $selectedOption3 !== null && $selectedOption3->getAttribute('value') == $testYear['academic_year_id']);
}

echo "\n=======================================================\n";
echo "5. Combined Filters & Defensive Variable Verification\n";
echo "=======================================================\n";

// Check query with all parameters present
$res4 = makeRequest($baseUrl . '/academics/subject_teachers?academic_year_id=' . $activeYearId . '&class_id=1&division_id=12&subject_id=11&staff_id=31');

assertCondition("Combined filter request loads with HTTP 200", $res4['code'] === 200);
assertCondition("No PHP notices in combined request", strpos($res4['body'], 'Undefined variable') === false && strpos($res4['body'], 'Severity: Notice') === false);

// Check query with alternate parameter names (teacher_id and section_id compatibility)
$res5 = makeRequest($baseUrl . '/academics/subject_teachers?academic_year_id=' . $activeYearId . '&class_id=1&section_id=12&teacher_id=31');

assertCondition("Legacy param aliases (section_id, teacher_id) load with HTTP 200", $res5['code'] === 200);
assertCondition("No PHP notices with legacy param aliases", strpos($res5['body'], 'Undefined variable') === false && strpos($res5['body'], 'Severity: Notice') === false);

echo "\n=======================================================\n";
echo "SUMMARY: $passed / $total tests passed.\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "\n>>> ALL TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> SOME TESTS FAILED! <<<\n";
    exit(1);
}
