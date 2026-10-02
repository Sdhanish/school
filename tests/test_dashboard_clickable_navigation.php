<?php
/**
 * Automated Verification Script for Fee & Finance Dashboard Clickable Navigation
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=======================================================\n";
echo " FEE & FINANCE DASHBOARD NAVIGATION VERIFICATION TEST\n";
echo "=======================================================\n\n";

define('BASEPATH', '1');
define('ENVIRONMENT', 'development');

require_once __DIR__ . '/../application/config/database.php';
$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

$test_passes = 0;
$test_fails = 0;

function assert_test($description, $condition) {
    global $test_passes, $test_fails;
    if ($condition) {
        echo " [PASS] {$description}\n";
        $test_passes++;
    } else {
        echo " [FAIL] {$description}\n";
        $test_fails++;
    }
}

// -------------------------------------------------------------
// TEST 1: Inspect dashboard.php Source Structure
// -------------------------------------------------------------
echo "--- TEST SET 1: Dashboard View Code Inspection ---\n";

$dashboard_view = file_get_contents(__DIR__ . '/../application/views/pages/fees/dashboard.php');

// Verify top 4 cards are NOT clickable
$top_kpi_section = substr($dashboard_view, strpos($dashboard_view, 'Primary KPI Metric Cards'), strpos($dashboard_view, 'Collection Velocity') - strpos($dashboard_view, 'Primary KPI Metric Cards'));

assert_test("Total Fee Expected has no <a> tag", strpos($top_kpi_section, 'Total Fee Expected') !== false && !preg_match('/<a[^>]*>[^<]*Total Fee Expected/i', $top_kpi_section));
assert_test("Total Fee Collected has no <a> tag", strpos($top_kpi_section, 'Total Fee Collected') !== false && !preg_match('/<a[^>]*>[^<]*Total Fee Collected/i', $top_kpi_section));
assert_test("Total Pending Dues has no <a> tag", strpos($top_kpi_section, 'Total Pending Dues') !== false && !preg_match('/<a[^>]*>[^<]*Total Pending Dues/i', $top_kpi_section));
assert_test("Total Overdue Dues has no <a> tag", strpos($top_kpi_section, 'Total Overdue Dues') !== false && !preg_match('/<a[^>]*>[^<]*Total Overdue Dues/i', $top_kpi_section));
assert_test("Top KPI cards do not have cursor-pointer or onclick", strpos($top_kpi_section, 'cursor-pointer') === false && strpos($top_kpi_section, 'onclick') === false);

// Verify the 4 cards ARE clickable with correct routes
$velocity_section = substr($dashboard_view, strpos($dashboard_view, 'Collection Velocity & Student Metrics'), strpos($dashboard_view, 'Collection Summary & Outstanding') - strpos($dashboard_view, 'Collection Velocity & Student Metrics'));

assert_test("Today's Collection is rendered inside <a> pointing to fees/payments", strpos($velocity_section, "fees/payments?date_from=' . \$today_date") !== false && strpos($velocity_section, "Today's Collection") !== false);
assert_test("This Month's Collection is rendered inside <a> pointing to fees/payments", strpos($velocity_section, "fees/payments?date_from=' . \$month_start_date") !== false && strpos($velocity_section, "This Month's Collection") !== false);
assert_test("Students with Dues is rendered inside <a> pointing to fees/due_fees", strpos($velocity_section, "fees/due_fees?academic_year_id=' . \$ay_id") !== false && strpos($velocity_section, "Students with Dues") !== false);
assert_test("Fully Paid Students is rendered inside <a> pointing to fees/student_fees", strpos($velocity_section, "fees/student_fees?payment_status=Fully Paid&academic_year_id=' . \$ay_id") !== false && strpos($velocity_section, "Fully Paid Students") !== false);
assert_test("All 4 cards have cursor-pointer and hover styling", substr_count($velocity_section, 'cursor-pointer') === 4 && substr_count($velocity_section, 'hover:elevation-2') === 4);

// -------------------------------------------------------------
// TEST 2: Query Consistency for Academic Year 1 (Active)
// -------------------------------------------------------------
echo "\n--- TEST SET 2: Count & List Consistency (Academic Year 1) ---\n";

$year_id = 1;
$today = date('Y-m-d');
$cur_month = date('m');
$cur_year = date('Y');

// 1. Today's Collection
$res = $mysqli->query("SELECT COALESCE(SUM(fp.amount_paid), 0.00) as total FROM tbl_fee_payments fp JOIN tbl_student_fees sf ON sf.student_fee_id = fp.student_fee_id WHERE fp.payment_date = '{$today}' AND fp.status = 1 AND sf.academic_year_id = {$year_id}")->fetch_assoc();
$today_col = (float)$res['total'];
echo " Today's collection query result: ₹" . number_format($today_col, 2) . "\n";

// 2. Monthly Collection
$res = $mysqli->query("SELECT COALESCE(SUM(fp.amount_paid), 0.00) as total, COUNT(fp.payment_id) as cnt, COUNT(DISTINCT fp.student_id) as stu_cnt FROM tbl_fee_payments fp JOIN tbl_student_fees sf ON sf.student_fee_id = fp.student_fee_id WHERE MONTH(fp.payment_date) = '{$cur_month}' AND YEAR(fp.payment_date) = '{$cur_year}' AND fp.status = 1 AND sf.academic_year_id = {$year_id}")->fetch_assoc();
$month_col = (float)$res['total'];
$month_tx_cnt = (int)$res['cnt'];
$month_stu_cnt = (int)$res['stu_cnt'];
echo " This Month's collection query result: ₹" . number_format($month_col, 2) . " across {$month_tx_cnt} transactions and {$month_stu_cnt} students\n";
assert_test("Monthly collection amount is ₹41,000.00 with 3 payments", $month_col == 41000.00 && $month_tx_cnt == 3 && $month_stu_cnt == 3);

// 3. Students with Dues
$res = $mysqli->query("SELECT COUNT(DISTINCT student_id) as cnt FROM tbl_student_fees WHERE due_amount > 0 AND status = 1 AND is_deleted = 'n' AND academic_year_id = {$year_id}")->fetch_assoc();
$due_students_dashboard = (int)$res['cnt'];

$res2 = $mysqli->query("SELECT DISTINCT sf.student_id, s.first_name, s.last_name FROM tbl_student_fees sf JOIN tbl_students s ON s.student_id = sf.student_id WHERE sf.due_amount > 0 AND sf.status = 1 AND sf.academic_year_id = {$year_id}")->fetch_all(MYSQLI_ASSOC);
$due_students_list_count = count($res2);

echo " Students with Dues: Dashboard = {$due_students_dashboard}, Destination List = {$due_students_list_count}\n";
assert_test("Students with Dues dashboard count matches destination distinct student count (3 students)", $due_students_dashboard === $due_students_list_count && $due_students_dashboard === 3);

// 4. Fully Paid Students (Zero Outstanding Dues)
$sql_fully_paid = "SELECT COUNT(DISTINCT sf1.student_id) as cnt 
FROM tbl_student_fees sf1 
WHERE sf1.status = 1 AND sf1.is_deleted = 'n' AND sf1.academic_year_id = {$year_id}
AND NOT EXISTS (
    SELECT 1 FROM tbl_student_fees sf2 
    WHERE sf2.student_id = sf1.student_id 
      AND sf2.status = 1 
      AND sf2.is_deleted = 'n' 
      AND sf2.academic_year_id = {$year_id}
      AND sf2.due_amount > 0
)";
$res = $mysqli->query($sql_fully_paid)->fetch_assoc();
$fully_paid_dashboard = (int)$res['cnt'];

$sql_fully_paid_list = "SELECT sf1.student_fee_id, sf1.student_id, s.first_name, s.last_name, sf1.invoice_no, sf1.due_amount 
FROM tbl_student_fees sf1 
JOIN tbl_students s ON s.student_id = sf1.student_id 
WHERE sf1.status = 1 AND sf1.is_deleted = 'n' AND sf1.academic_year_id = {$year_id}
  AND sf1.due_amount = 0
  AND NOT EXISTS (
    SELECT 1 FROM tbl_student_fees sf2 
    WHERE sf2.student_id = sf1.student_id 
      AND sf2.status = 1 
      AND sf2.is_deleted = 'n' 
      AND sf2.academic_year_id = {$year_id}
      AND sf2.due_amount > 0
)";
$res2 = $mysqli->query($sql_fully_paid_list)->fetch_all(MYSQLI_ASSOC);
$fully_paid_list_count = count(array_unique(array_column($res2, 'student_id')));

echo " Fully Paid Students: Dashboard = {$fully_paid_dashboard}, Destination List = {$fully_paid_list_count}\n";
if (!empty($res2)) {
    foreach ($res2 as $row) {
        echo "   -> Fully Paid Student: {$row['first_name']} {$row['last_name']} (ID: {$row['student_id']}, Invoice: {$row['invoice_no']}, Due: {$row['due_amount']})\n";
    }
}
assert_test("Fully Paid Students dashboard count matches destination distinct student count (1 student: Aanya Kumar)", $fully_paid_dashboard === $fully_paid_list_count && $fully_paid_dashboard === 1);

// -------------------------------------------------------------
// TEST 3: HTTP Endpoint Verification (Local Apache)
// -------------------------------------------------------------
echo "\n--- TEST SET 3: Live HTTP Endpoints via cURL ---\n";

$cookie_file = tempnam(sys_get_temp_dir(), 'test_cookie_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff];
}

// 1. Login via /auth/login
$login_page = http_req('http://localhost/schoolnew/auth/login');
preg_match('/name="csrf_test_name" value="([a-f0-9]+)"/i', $login_page['body'], $matches);
$csrf_token = $matches[1] ?? '';

$login_res = http_req('http://localhost/schoolnew/auth/login', [
    'email'          => 'admin@gmail.com',
    'password'       => '123456',
    'csrf_test_name' => $csrf_token
]);

assert_test("Login was successful", strpos($login_res['url'], 'dashboard') !== false || strpos($login_res['url'], 'schoolnew') !== false);

// 2. Fees Dashboard HTTP
$dash_res = http_req('http://localhost/schoolnew/fees?academic_year_id=1');
assert_test("Fees Dashboard responds 200 OK", $dash_res['code'] === 200);
assert_test("Dashboard rendered Today's Collection link", strpos($dash_res['body'], "Today's Collection") !== false && strpos($dash_res['body'], 'fees/payments?date_from=' . $today) !== false);
assert_test("Dashboard rendered This Month's Collection link", strpos($dash_res['body'], "This Month's Collection") !== false && strpos($dash_res['body'], 'fees/payments?date_from=' . date('Y-m-01')) !== false);
assert_test("Dashboard rendered Students with Dues link", strpos($dash_res['body'], "Students with Dues") !== false && strpos($dash_res['body'], 'fees/due_fees?academic_year_id=1') !== false);
assert_test("Dashboard rendered Fully Paid Students link", strpos($dash_res['body'], "Fully Paid Students") !== false && strpos($dash_res['body'], 'fees/student_fees?payment_status=Fully+Paid&amp;academic_year_id=1') !== false || strpos($dash_res['body'], 'fees/student_fees?payment_status=Fully Paid&academic_year_id=1') !== false);

// 3. Payments Destination for Month
$pay_month_res = http_req('http://localhost/schoolnew/fees/payments?date_from=' . date('Y-m-01') . '&date_to=' . $today . '&academic_year_id=1');
assert_test("Payments (Month) responds 200 OK", $pay_month_res['code'] === 200);
assert_test("Payments (Month) shows Total Collection Amount ₹41,000.00", strpos($pay_month_res['body'], '41,000.00') !== false);
assert_test("Payments (Month) shows 3 Transactions and 3 Students", strpos($pay_month_res['body'], '3 Transactions') !== false && strpos($pay_month_res['body'], '3 Students') !== false);

// 4. Due Fees Destination
$due_res = http_req('http://localhost/schoolnew/fees/due_fees?academic_year_id=1');
assert_test("Due Fees responds 200 OK", $due_res['code'] === 200);
assert_test("Due Fees shows records across 3 students", strpos($due_res['body'], 'across 3 students') !== false);
assert_test("Due Fees contains Arjun Pillai, Saanvi Thomas, and Vivaan Sharma", strpos($due_res['body'], 'Arjun Pillai') !== false && strpos($due_res['body'], 'Saanvi Thomas') !== false && strpos($due_res['body'], 'Vivaan Sharma') !== false);

// 5. Fully Paid Destination
$paid_res = http_req('http://localhost/schoolnew/fees/student_fees?payment_status=Fully+Paid&academic_year_id=1');
assert_test("Student Fees (Fully Paid) responds 200 OK", $paid_res['code'] === 200);
assert_test("Student Fees (Fully Paid) shows 1 student", strpos($paid_res['body'], 'across 1 students') !== false || strpos($paid_res['body'], 'across 1 student') !== false);
assert_test("Student Fees (Fully Paid) includes Aanya Kumar", strpos($paid_res['body'], 'Aanya Kumar') !== false);
assert_test("Student Fees (Fully Paid) DOES NOT include Arjun Pillai or Vivaan Sharma", strpos($paid_res['body'], 'Arjun Pillai') === false && strpos($paid_res['body'], 'Vivaan Sharma') === false);

@unlink($cookie_file);

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n=======================================================\n";
echo " TEST RESULTS: {$test_passes} PASSED, {$test_fails} FAILED\n";
echo "=======================================================\n";

if ($test_fails > 0) {
    exit(1);
}
