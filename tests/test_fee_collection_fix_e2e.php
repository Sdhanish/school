<?php
/**
 * Automated Verification Suite for:
 * Fee Collection Page 404 Error Fix & Full Flow Validation
 * 
 * Run via CLI: php tests/test_fee_collection_fix_e2e.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');

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

class TestFeeCollectionFixE2ESuite {
    private $db;
    private $baseUrl = 'http://localhost/schoolnew/';
    private $adminCookie;
    private $noPermCookie;
    private $passed = 0;
    private $failed = 0;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->adminCookie = __DIR__ . '/fixtures/cookie_admin.txt';
        $this->noPermCookie = __DIR__ . '/fixtures/cookie_noperm.txt';
    }

    private function assert($condition, $description) {
        if ($condition) {
            echo "  [PASS] {$description}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$description}\n";
            $this->failed++;
        }
    }

    private function login($username, $password, $cookieFile) {
        if (file_exists($cookieFile)) @unlink($cookieFile);

        $loginUrl = $this->baseUrl . 'auth/login';
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $page = curl_exec($ch);
        curl_close($ch);

        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page, $m);
        $csrf = $m[1] ?? '';

        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'csrf_test_name' => $csrf,
            'email'          => $username,
            'password'       => $password
        ]));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    private function makeRequest($url, $cookieFile) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['code' => $code, 'body' => $html];
    }

    public function runAll() {
        echo "====================================================================\n";
        echo "TEST SUITE: FEE COLLECTION 404 FIX & FLOW VERIFICATION\n";
        echo "====================================================================\n\n";

        // Login as Super Admin
        $this->login('Admin', '123456', $this->adminCookie);

        $this->test1_FeeCollectionStudent49();
        $this->test2_FeeCollectionAnotherValidStudent();
        $this->test3_FeeCollectionInvalidStudent();
        $this->test4_FeeCollectionNoStudentId();
        $this->test5_FeeCollectionAcademicYearScoping();
        $this->test6_UserWithoutPermission();
        $this->test7_CanonicalLinksAndRoutes();

        echo "\n====================================================================\n";
        echo "TEST RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "====================================================================\n";

        return $this->failed === 0;
    }

    // Test 1: /fees/collection?student_id=49
    private function test1_FeeCollectionStudent49() {
        echo "--- Test 1: /fees/collection?student_id=49 Loads Properly ---\n";
        $url = $this->baseUrl . 'fees/collection?student_id=49';
        $res = $this->makeRequest($url, $this->adminCookie);

        $this->assert($res['code'] === 200, "HTTP Status is 200 OK (no 404)");
        $this->assert(strpos($res['body'], '404 - Page Not Found') === false, "404 Page Not Found error page is NOT displayed");
        $this->assert(strpos($res['body'], 'Fee Collection &amp; Receipts') !== false || strpos($res['body'], 'Fee Collection') !== false, "Page contains Fee Collection header");
        $this->assert(strpos($res['body'], 'Saanvi') !== false, "Student 49 'Saanvi' name is rendered dynamically");
        $this->assert(strpos($res['body'], 'SCH20260142443') !== false, "Student 49 Admission Number 'SCH20260142443' is rendered");
        $this->assert(strpos($res['body'], 'Student Fee Summary') !== false, "Student Fee Summary card is rendered");
    }

    // Test 2: /fees/collection?student_id=<valid_student_id>
    private function test2_FeeCollectionAnotherValidStudent() {
        echo "\n--- Test 2: Another Valid Student Loads Fee Data Correctly ---\n";
        // Find another valid student for school 1
        $row = $this->db->query("SELECT student_id, first_name, last_name, admission_number FROM tbl_students WHERE school_id = 1 AND is_deleted = 'n' AND student_id != 49 LIMIT 1")->fetch_assoc();
        $sid = (int)$row['student_id'];
        $fname = $row['first_name'];
        $adm = $row['admission_number'];

        $url = $this->baseUrl . "fees/collection?student_id={$sid}";
        $res = $this->makeRequest($url, $this->adminCookie);

        $this->assert($res['code'] === 200, "HTTP Status is 200 OK for student {$sid}");
        $this->assert(strpos($res['body'], '404 - Page Not Found') === false, "No 404 for student {$sid}");
        $this->assert(strpos($res['body'], $fname) !== false, "Correct student name '{$fname}' is rendered");
        $this->assert(strpos($res['body'], $adm) !== false, "Correct admission number '{$adm}' is rendered");
    }

    // Test 3: /fees/collection?student_id=999999
    private function test3_FeeCollectionInvalidStudent() {
        echo "\n--- Test 3: Invalid Student ID Handling (student_id=999999) ---\n";
        $url = $this->baseUrl . 'fees/collection?student_id=999999';
        $res = $this->makeRequest($url, $this->adminCookie);

        $this->assert($res['code'] === 200, "HTTP Status is 200 OK (graceful empty state)");
        $this->assert(strpos($res['body'], '404 - Page Not Found') === false, "No 404 error page displayed");
        $this->assert(strpos($res['body'], 'A PHP Error was encountered') === false, "No PHP error encountered");
        $this->assert(strpos($res['body'], 'Severity: Notice') === false, "No PHP notice encountered");
        $this->assert(strpos($res['body'], 'was not found') !== false || strpos($res['body'], 'Student not found') !== false, "Displays user-friendly 'Student was not found' flash error");
    }

    // Test 4: /fees/collection
    private function test4_FeeCollectionNoStudentId() {
        echo "\n--- Test 4: No Student ID (/fees/collection) ---\n";
        $url = $this->baseUrl . 'fees/collection';
        $res = $this->makeRequest($url, $this->adminCookie);

        $this->assert($res['code'] === 200, "HTTP Status is 200 OK for base /fees/collection");
        $this->assert(strpos($res['body'], '404 - Page Not Found') === false, "No 404 error page");
        $this->assert(strpos($res['body'], 'A PHP Error was encountered') === false, "No PHP errors encountered");
        $this->assert(strpos($res['body'], 'Fee Collection') !== false, "Fee Collection page loads with clean form");
        $this->assert(strpos($res['body'], 'Student Fee Summary') === false, "No student fee summary shown when no student ID provided");
    }

    // Test 5: Different academic year
    private function test5_FeeCollectionAcademicYearScoping() {
        echo "\n--- Test 5: Dynamic Academic Year Fee Data Scoping ---\n";
        // Create a test fee assignment for AY 1
        $sid = 1; // School 1
        $studentId = 49;
        $ay1 = 1;

        // Ensure we have an active fee structure and assignment for student 49
        $ft = $this->db->query("SELECT id FROM tbl_finance_fee_types WHERE school_id = 1 LIMIT 1")->fetch_assoc();
        if (!$ft) {
            $this->db->query("INSERT INTO tbl_finance_fee_types (school_id, type_name, type_code, account_id, status) VALUES (1, 'Tuition Fee AY Test', 'TUIT_AY_TEST', 1, 1)");
            $ft_id = $this->db->insert_id;
        } else {
            $ft_id = $ft['id'];
        }

        $invNoAY1 = 'TEST-AY1-' . time();
        $this->db->query("
            INSERT INTO tbl_finance_fee_assignments 
            (school_id, academic_year_id, student_id, fee_structure_id, ledger_id, invoice_number, invoice_date, due_date, assigned_amount, discount_amount, net_amount, paid_amount, due_amount, status)
            VALUES (1, 1, {$studentId}, 0, 1, '{$invNoAY1}', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 4500.00, 0.00, 4500.00, 0.00, 4500.00, 'Pending')
        ");
        $assignIdAY1 = $this->db->insert_id;

        // Fetch page while AY is 1
        $res = $this->makeRequest($this->baseUrl . "fees/collection?student_id={$studentId}", $this->adminCookie);
        $this->assert(strpos($res['body'], $invNoAY1) !== false, "Fee assignment {$invNoAY1} for AY 1 appears in the invoice dropdown");
        $this->assert(strpos($res['body'], '4,500.00') !== false, "Due amount ₹4,500.00 is calculated dynamically in fee summary");

        // Clean up test assignment
        $this->db->query("DELETE FROM tbl_finance_fee_assignments WHERE id = {$assignIdAY1}");
    }

    // Test 6: User without Fee Collection permission
    private function test6_UserWithoutPermission() {
        echo "\n--- Test 6: User Without Fee Collection Permission ---\n";
        // Create or find a user role without finance.fees.collect
        // Let's create a temporary test user with a teacher role or role without finance.fees.collect
        $teacherUser = $this->db->query("
            SELECT u.username FROM tbl_users u 
            JOIN tbl_roles r ON r.role_id = u.role_id 
            WHERE r.role_code = 'TEACHER' AND u.status = 'Active' LIMIT 1
        ")->fetch_assoc();

        if ($teacherUser) {
            $tUser = $teacherUser['username'];
            $this->login($tUser, '123456', $this->noPermCookie);
            $res = $this->makeRequest($this->baseUrl . 'fees/collection?student_id=49', $this->noPermCookie);

            // Role without permission should return 403 Forbidden, NOT 404!
            $this->assert($res['code'] === 403 || strpos($res['body'], '403 Forbidden') !== false || strpos($res['body'], 'permission') !== false, "Non-permitted user receives 403 Forbidden / Access Denied (not 404)");
            $this->assert(strpos($res['body'], '404 - Page Not Found') === false, "Does NOT misinterpret lack of permission as 404 Not Found");
        } else {
            echo "  [SKIP] No TEACHER test user found, skipping sub-check\n";
        }
    }

    // Test 7: Links and Route Aliases
    private function test7_CanonicalLinksAndRoutes() {
        echo "\n--- Test 7: Canonical Links and Route Aliases ---\n";
        $stmt_content = file_get_contents(APPPATH . 'views/pages/finance/student_statement.php');
        $this->assert(strpos($stmt_content, "site_url('finance/fee_collection?student_id='") !== false, "student_statement.php Collect Fee button points to finance/fee_collection");

        $dash_content = file_get_contents(APPPATH . 'views/pages/dashboard.php');
        $this->assert(strpos($dash_content, "site_url('finance/fee_collection')") !== false, "dashboard.php Fees Collected card points to finance/fee_collection");

        $routes_content = file_get_contents(APPPATH . 'config/routes.php');
        $this->assert(strpos($routes_content, "finance/fee_collection") !== false, "routes.php maps finance/fee_collection");
    }
}

$suite = new TestFeeCollectionFixE2ESuite($mysqli);
$success = $suite->runAll();
exit($success ? 0 : 1);
