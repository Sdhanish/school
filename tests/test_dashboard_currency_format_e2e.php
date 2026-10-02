<?php
/**
 * Test Dashboard Fee Amount Formatting & Currency Helper
 *
 * Verifies:
 * 1. school_currency() helper function formatting across all test cases:
 *    - < 1,000 (0, 5, 50, 500, 999)
 *    - 1,000
 *    - 41,000
 *    - 62,500
 *    - 1,00,000+ (100,000; 1,250,000; 10,000,000)
 *    - Decimals (e.g. 62500.50, 41000.75, 123456789.5)
 *    - Negative values (-41000)
 *    - Asserts exact expected format and absence of duplicate commas (,,)
 * 2. E2E Dashboard page verification:
 *    - Login as admin
 *    - Load /dashboard
 *    - Verify HTTP 200
 *    - Extract "Fees Collected (MTD)" card value and "Pending Fees" card value
 *    - Ensure no duplicate comma (,,) exists anywhere in rendered currency amounts
 *
 * Run: php tests/test_dashboard_currency_format_e2e.php
 */

define('BASEPATH', true);
require_once __DIR__ . '/../application/helpers/app_helper.php';

$tests_run = 0;
$tests_passed = 0;

function assert_test($desc, $condition, $extra = '') {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "  [PASS] $desc\n";
    } else {
        echo "  [FAIL] $desc" . ($extra ? " - $extra" : "") . "\n";
    }
}

echo "=======================================================\n";
echo "1. Unit Testing school_currency() Helper\n";
echo "=======================================================\n";

$expectations = [
    [0, '₹ 0.00'],
    [5, '₹ 5.00'],
    [50, '₹ 50.00'],
    [500, '₹ 500.00'],
    [999, '₹ 999.00'],
    [1000, '₹ 1,000.00'],
    [41000, '₹ 41,000.00'],
    [62500, '₹ 62,500.00'],
    [100000, '₹ 1,00,000.00'],
    [1250000, '₹ 12,50,000.00'],
    [10000000, '₹ 1,00,00,000.00'],
    [62500.5, '₹ 62,500.50'],
    [41000.75, '₹ 41,000.75'],
    [123456789.5, '₹ 12,34,56,789.50'],
    [-41000, '-₹ 41,000.00'],
    [-62500, '-₹ 62,500.00'],
];

foreach ($expectations as $exp) {
    $input = $exp[0];
    $expected = $exp[1];
    $actual = school_currency($input);
    assert_test(
        "Formatting $input -> expected '$expected'",
        $actual === $expected && strpos($actual, ',,') === false,
        "Got '$actual'"
    );
}

echo "\n=======================================================\n";
echo "2. E2E Testing Dashboard Page (HTTP)\n";
echo "=======================================================\n";

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_dash_curr_');

function http_req($url, $post = null) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'err' => $err];
}

function get_csrf_token($html) {
    if (preg_match('/name="(csrf_test_name|csrf_token)"\s+value="([^"]+)"/i', $html, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_token', 'hash' => ''];
}

$login_page = http_req("http://localhost/schoolnew/auth/login");
$csrf = get_csrf_token($login_page['body']);
$login_res = http_req("http://localhost/schoolnew/auth/login", [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

assert_test("Admin login successful", $login_res['code'] === 200, "HTTP code: " . $login_res['code']);

$dash_res = http_req("http://localhost/schoolnew/dashboard");
assert_test("Dashboard page loads with HTTP 200", $dash_res['code'] === 200, "HTTP code: " . $dash_res['code']);

$has_double_comma = (strpos($dash_res['body'], ',,') !== false);
assert_test("Dashboard page has NO occurrences of double commas (,,)", !$has_double_comma);

// Extract Fees Collected (MTD) value
if (preg_match('/<div class="mt-3 font-headline-lg text-headline-lg text-on-surface">\s*([^<]+)\s*<\/div>\s*<div class="[^"]*">\s*<span>Fees Collected \(MTD\)<\/span>/is', $dash_res['body'], $m)) {
    $mtd_val = trim($m[1]);
    echo "  [INFO] Fees Collected (MTD) displayed as: $mtd_val\n";
    assert_test("Fees Collected (MTD) has no duplicate commas", strpos($mtd_val, ',,') === false);
    assert_test("Fees Collected (MTD) has currency symbol ₹", strpos($mtd_val, '₹') !== false);
} else {
    assert_test("Fees Collected (MTD) block found", false, "Pattern did not match");
}

// Extract Pending Fees value
if (preg_match('/<div class="mt-3 font-headline-lg text-headline-lg text-on-surface">\s*([^<]+)\s*<\/div>\s*<div class="[^"]*">\s*<span>Pending Fees<\/span>/is', $dash_res['body'], $m)) {
    $pending_val = trim($m[1]);
    echo "  [INFO] Pending Fees displayed as: $pending_val\n";
    assert_test("Pending Fees has no duplicate commas", strpos($pending_val, ',,') === false);
    assert_test("Pending Fees has currency symbol ₹", strpos($pending_val, '₹') !== false);
} else {
    assert_test("Pending Fees block found", false, "Pattern did not match");
}

@unlink($cookie_file);

echo "\n=======================================================\n";
echo "SUMMARY: $tests_passed / $tests_run tests passed\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    exit(0);
} else {
    exit(1);
}
