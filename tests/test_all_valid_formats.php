<?php
/**
 * Test all 6 valid formats upload cleanly and create records in tbl_student_documents
 */

$cookie_file = tempnam(sys_get_temp_dir(), 'ci_cookie_doc_all_');

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

$login_page = http_req('http://localhost/schoolnew/auth/login');
$csrf = get_csrf_token($login_page['body']);

http_req('http://localhost/schoolnew/auth/login', [
    $csrf['name'] => $csrf['hash'],
    'email'       => 'admin@gmail.com',
    'password'    => '123456',
    'remember'    => '1',
]);

$fixturesDir = __DIR__ . '/fixtures/documents';
$uploadUrl = 'http://localhost/schoolnew/students/upload_document';

$validFormats = [
    'test.pdf'  => ['type' => 'Birth Certificate', 'title' => 'Valid PDF Cert'],
    'test.png'  => ['type' => 'Aadhaar Card / ID Proof', 'title' => 'Valid PNG Proof'],
    'test.jpg'  => ['type' => 'Passport Photo', 'title' => 'Valid JPG Photo'],
    'test.jpeg' => ['type' => 'Medical Fitness Certificate', 'title' => 'Valid JPEG Fitness'],
    'test.doc'  => ['type' => 'Previous School TC', 'title' => 'Valid DOC Transfer'],
    'test.docx' => ['type' => 'Transfer Certificate', 'title' => 'Valid DOCX TC'],
];

echo "=======================================================\n";
echo "Testing All 6 Allowed Student Document Formats\n";
echo "=======================================================\n";

foreach ($validFormats as $file => $meta) {
    $prof = http_req('http://localhost/schoolnew/students/profile/117');
    $csrf = get_csrf_token($prof['body']);

    $cfile = new CURLFile($fixturesDir . '/' . $file, 'application/octet-stream', $file);
    $postData = [
        $csrf['name']   => $csrf['hash'],
        'student_id'    => 117,
        'document_type' => $meta['type'],
        'document_name' => $meta['title'],
        'document_file' => $cfile,
        'redirect_to'   => 'http://localhost/schoolnew/students/profile/117#documents'
    ];
    $res = http_req($uploadUrl, $postData, true);
    $accepted = (strpos($res['body'], 'Document uploaded successfully') !== false);
    echo "  [" . ($accepted ? "PASS" : "FAIL") . "] Format: $file (" . $meta['type'] . "): " . ($accepted ? "Accepted & Saved" : "Rejected") . "\n";
}

@unlink($cookie_file);

// Verify in Database
$mysqli = new mysqli('localhost', 'root', '', 'db_school');
$res = $mysqli->query("SELECT document_name, document_type, file_path, status FROM tbl_student_documents WHERE student_id = 117 AND is_deleted = 'n' ORDER BY document_id DESC LIMIT 6");
echo "\nRecent documents in database for student 117:\n";
while ($row = $res->fetch_assoc()) {
    echo " - " . $row['document_name'] . " | " . $row['document_type'] . " | " . $row['file_path'] . "\n";
}
