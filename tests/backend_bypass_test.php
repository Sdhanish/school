<?php
/**
 * Backend Bypass Security Test
 * Verifies that direct HTTP POST requests bypassing the frontend:
 * 1. Strictly reject unsupported file formats (xlsx, gif, webp, etc.)
 * 2. Strictly reject renamed executables (e.g. fake.pdf with MZ binary header)
 * 3. Successfully accept legitimate files (test.pdf)
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_doc_');

function http_req($url, $post = null, $isMultipart = false) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
        }
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return ['body' => $res, 'code' => $code, 'url' => $eff, 'err' => $err];
}

function get_csrf_token($body) {
    if (preg_match('/name="([^"]*csrf[^"]*)"\s+value="([^"]+)"/i', $body, $m)) {
        return ['name' => $m[1], 'hash' => $m[2]];
    }
    return ['name' => 'csrf_test_name', 'hash' => ''];
}

echo "=======================================================\n";
echo "1. Authenticate as Admin\n";
echo "=======================================================\n";
$login_page = http_req('http://localhost/schoolnew/auth/login');
$csrf = get_csrf_token($login_page['body']);

$login_res = http_req('http://localhost/schoolnew/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

if (strpos($login_res['url'], 'auth/login') !== false) {
    die("Authentication failed!\n");
}
echo "  [PASS] Authenticated successfully as Admin\n";

$fixturesDir = __DIR__ . '/fixtures/documents';
$uploadUrl = 'http://localhost/schoolnew/students/upload_document';

function post_document($uploadUrl, $filePath, $postFileName) {
    // Get fresh page for CSRF token if needed
    $prof = http_req('http://localhost/schoolnew/students/profile/117');
    $csrf = get_csrf_token($prof['body']);

    $cfile = new CURLFile($filePath, 'application/octet-stream', $postFileName);
    $postData = [
        $csrf['name']   => $csrf['hash'],
        'student_id'    => 117,
        'document_type' => 'Birth Certificate',
        'document_name' => 'Bypass Test - ' . $postFileName,
        'document_file' => $cfile,
        'redirect_to'   => 'http://localhost/schoolnew/students/profile/117#documents'
    ];
    return http_req($uploadUrl, $postData, true);
}

echo "\n=======================================================\n";
echo "2. Backend Bypass Tests (Mandatory Security Verification)\n";
echo "=======================================================\n";

// Test 2.1: Disallowed format: sheet.xlsx
$resXlsx = post_document($uploadUrl, $fixturesDir . '/test.xlsx', 'sheet.xlsx');
$xlsxRejected = (strpos($resXlsx['body'], 'Unsupported file format') !== false);
echo "  [" . ($xlsxRejected ? "PASS" : "FAIL") . "] Direct backend upload of sheet.xlsx: " . ($xlsxRejected ? "Rejected with correct error" : "Not rejected") . "\n";

// Test 2.2: Disallowed format: test.gif
$resGif = post_document($uploadUrl, $fixturesDir . '/test.gif', 'test.gif');
$gifRejected = (strpos($resGif['body'], 'Unsupported file format') !== false);
echo "  [" . ($gifRejected ? "PASS" : "FAIL") . "] Direct backend upload of test.gif: " . ($gifRejected ? "Rejected with correct error" : "Not rejected") . "\n";

// Test 2.3: Disallowed format: test.webp
$resWebp = post_document($uploadUrl, $fixturesDir . '/test.webp', 'test.webp');
$webpRejected = (strpos($resWebp['body'], 'Unsupported file format') !== false);
echo "  [" . ($webpRejected ? "PASS" : "FAIL") . "] Direct backend upload of test.webp: " . ($webpRejected ? "Rejected with correct error" : "Not rejected") . "\n";

// Test 2.4: Renamed executable disguised as PDF: fake.pdf
$resFake = post_document($uploadUrl, $fixturesDir . '/fake.pdf', 'fake.pdf');
$fakeRejected = (strpos($resFake['body'], 'Unsupported file format') !== false);
echo "  [" . ($fakeRejected ? "PASS" : "FAIL") . "] Direct backend upload of fake.pdf (renamed PE executable): " . ($fakeRejected ? "Rejected via MIME detection" : "Not rejected") . "\n";

// Test 2.5: Valid PDF: test.pdf
$resPdf = post_document($uploadUrl, $fixturesDir . '/test.pdf', 'test_backend_valid.pdf');
$pdfAccepted = (strpos($resPdf['body'], 'Document uploaded successfully') !== false);
echo "  [" . ($pdfAccepted ? "PASS" : "FAIL") . "] Direct backend upload of legitimate test.pdf: " . ($pdfAccepted ? "Accepted and stored" : "Failed") . "\n";

@unlink($cookie_file);
echo "\n=======================================================\n";
echo "All backend bypass checks completed.\n";
echo "=======================================================\n";
