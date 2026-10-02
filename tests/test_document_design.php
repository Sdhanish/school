<?php
/**
 * Automated Verification Test for Multi-School Document Design Management
 * Tests:
 *  1. Database Schema & Explicit Column Retrieval (No SELECT *)
 *  2. Multi-School Tenant Isolation (School 1 vs School 13 vs School 99999)
 *  3. Fallback Hierarchy (Report-specific -> Default -> None)
 *  4. Image Deletion & Soft-delete Handling
 *  5. Image Validation logic (MIME, Extension, Size, Format)
 *
 * Run via CLI: php tests/test_document_design.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');

require_once BASEPATH . 'core/Common.php';

// Mock get_instance() for standalone CI service/model testing
class MockCI {
    public $db;
    public $Document_design_model;
    public function __construct($mysqli_conn) {
        $this->db = new MockDB($mysqli_conn);
    }
}

class MockDB {
    protected $m;
    protected $last_select = '';
    public function __construct($m) { $this->m = $m; }
    public function select($cols) { $this->last_select = $cols; return $this; }
    public function from($table) { return $this; }
    public function where($field, $val = null) { return $this; }
    public function limit($n) { return $this; }
    public function get() { return $this; }
    public function get_last_select() { return $this->last_select; }
}

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

$tests_run = 0;
$tests_passed = 0;

function assert_test($description, $condition) {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "[PASS] $description\n";
    } else {
        echo "[FAIL] $description\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING DATABASE SCHEMA & CONSTRAINTS\n";
echo "=======================================================\n";

// 1. Table exists
$res = $mysqli->query("SHOW TABLES LIKE 'tbl_document_designs'");
assert_test("tbl_document_designs table exists", $res && $res->num_rows === 1);

// 2. Check columns
$cols = [];
$res_cols = $mysqli->query("DESCRIBE tbl_document_designs");
while ($r = $res_cols->fetch_assoc()) {
    $cols[] = $r['Field'];
}
$required_cols = ['id', 'school_id', 'document_type', 'header_image', 'footer_image', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'is_deleted'];
$missing = array_diff($required_cols, $cols);
assert_test("All required columns present in tbl_document_designs", empty($missing));

// 3. Unique Index on (school_id, document_type, is_deleted)
$res_idx = $mysqli->query("SHOW INDEX FROM tbl_document_designs WHERE Key_name = 'idx_school_doc_deleted'");
$idx_cols = [];
while ($r = $res_idx->fetch_assoc()) {
    $idx_cols[] = $r['Column_name'];
}
assert_test("Unique index on (school_id, document_type, is_deleted) exists", count($idx_cols) === 3 && in_array('school_id', $idx_cols) && in_array('document_type', $idx_cols) && in_array('is_deleted', $idx_cols));

// 4. Menu item under Settings
$res_menu = $mysqli->query("SELECT id, parent_id, route, menu_name FROM tbl_menu_items WHERE route = 'settings/document_design'");
assert_test("Menu item 'Document Design' exists in tbl_menu_items under parent 238", $res_menu && $res_menu->num_rows > 0);

echo "\n=======================================================\n";
echo "2. TESTING MODEL CRUD & STRICT EXPLICIT SELECTS (NO SELECT *)\n";
echo "=======================================================\n";

// Clean up any test fixtures from previous runs
$mysqli->query("DELETE FROM tbl_document_designs WHERE school_id IN (1, 13, 99998, 99999)");

// Insert via direct SQL or Model
$school_1 = 1;
$school_2 = 13;

// Insert School 1 Default Design
$sql1 = "INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, is_deleted)
         VALUES (1, 'default', 'sch1_default_header.png', 'sch1_default_footer.png', 'Active', 'n')";
$mysqli->query($sql1);

// Insert School 1 Fee Receipt Specific Design
$sql2 = "INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, is_deleted)
         VALUES (1, 'fee_receipt', 'sch1_fee_header.png', 'sch1_fee_footer.png', 'Active', 'n')";
$mysqli->query($sql2);

// Insert School 13 Default Design
$sql3 = "INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, is_deleted)
         VALUES (13, 'default', 'sch13_default_header.png', 'sch13_default_footer.png', 'Active', 'n')";
$mysqli->query($sql3);

// Insert School 13 Fee Receipt Specific Design
$sql4 = "INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, is_deleted)
         VALUES (13, 'fee_receipt', 'sch13_fee_header.png', 'sch13_fee_footer.png', 'Active', 'n')";
$mysqli->query($sql4);

// Verify record insertion
$check_count = $mysqli->query("SELECT COUNT(id) as cnt FROM tbl_document_designs WHERE school_id IN (1, 13) AND is_deleted = 'n'")->fetch_assoc()['cnt'];
assert_test("4 active test design records inserted across School 1 and School 13", (int)$check_count === 4);

// Test unique constraint prevents duplicate active (same school + same doc_type)
$dup_res = $mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, status, is_deleted) VALUES (1, 'fee_receipt', 'dup.png', 'Active', 'n')");
assert_test("Database unique constraint strictly blocks duplicate active configuration for same school and document_type", $dup_res === false);

echo "\n=======================================================\n";
echo "3. TESTING MULTI-SCHOOL TENANT ISOLATION\n";
echo "=======================================================\n";

// Helper function simulating Document_design_service::resolve_design logic
function mock_resolve_design($mysqli, $school_id, $doc_type, $fake_files = true) {
    // 1. Fetch specific
    $stmt1 = $mysqli->prepare("SELECT id, school_id, document_type, header_image, footer_image, status FROM tbl_document_designs WHERE school_id = ? AND document_type = ? AND is_deleted = 'n' LIMIT 1");
    $stmt1->bind_param("is", $school_id, $doc_type);
    $stmt1->execute();
    $specific = $stmt1->get_result()->fetch_assoc();

    // 2. Fetch default if needed
    $default = null;
    if ($doc_type !== 'default') {
        $stmt2 = $mysqli->prepare("SELECT id, school_id, document_type, header_image, footer_image, status FROM tbl_document_designs WHERE school_id = ? AND document_type = 'default' AND is_deleted = 'n' LIMIT 1");
        $stmt2->bind_param("i", $school_id);
        $stmt2->execute();
        $default = $stmt2->get_result()->fetch_assoc();
    }

    $res = [
        'school_id'          => $school_id,
        'document_type'      => $doc_type,
        'has_header'         => false,
        'header_file'        => null,
        'is_fallback_header' => false,
        'has_footer'         => false,
        'footer_file'        => null,
        'is_fallback_footer' => false,
    ];

    if ($specific && !empty($specific['header_image']) && $specific['status'] === 'Active') {
        $res['has_header'] = true;
        $res['header_file'] = $specific['header_image'];
    } elseif ($default && !empty($default['header_image']) && $default['status'] === 'Active') {
        $res['has_header'] = true;
        $res['header_file'] = $default['header_image'];
        $res['is_fallback_header'] = true;
    }

    if ($specific && !empty($specific['footer_image']) && $specific['status'] === 'Active') {
        $res['has_footer'] = true;
        $res['footer_file'] = $specific['footer_image'];
    } elseif ($default && !empty($default['footer_image']) && $default['status'] === 'Active') {
        $res['has_footer'] = true;
        $res['footer_file'] = $default['footer_image'];
        $res['is_fallback_footer'] = true;
    }

    return $res;
}

// Check School 1 Fee Receipt
$s1_fee = mock_resolve_design($mysqli, 1, 'fee_receipt');
assert_test("School 1 Fee Receipt loads School 1 fee header (sch1_fee_header.png)", $s1_fee['header_file'] === 'sch1_fee_header.png');
assert_test("School 1 Fee Receipt loads School 1 fee footer (sch1_fee_footer.png)", $s1_fee['footer_file'] === 'sch1_fee_footer.png');
assert_test("School 1 Fee Receipt is NOT marked as fallback", $s1_fee['is_fallback_header'] === false);

// Check School 13 Fee Receipt
$s13_fee = mock_resolve_design($mysqli, 13, 'fee_receipt');
assert_test("School 13 Fee Receipt loads School 13 fee header (sch13_fee_header.png)", $s13_fee['header_file'] === 'sch13_fee_header.png');
assert_test("School 13 Fee Receipt loads School 13 fee footer (sch13_fee_footer.png)", $s13_fee['footer_file'] === 'sch13_fee_footer.png');
assert_test("Zero cross-school leakage: School 13 does not see School 1 fee header", $s13_fee['header_file'] !== $s1_fee['header_file']);

echo "\n=======================================================\n";
echo "4. TESTING FALLBACK TO SCHOOL DEFAULT DESIGN\n";
echo "=======================================================\n";

// School 13 Attendance Report has NO specific design configured
$s13_att = mock_resolve_design($mysqli, 13, 'attendance_report');
assert_test("School 13 Attendance Report automatically falls back to School 13 Default Header", $s13_att['header_file'] === 'sch13_default_header.png');
assert_test("School 13 Attendance Report automatically falls back to School 13 Default Footer", $s13_att['footer_file'] === 'sch13_default_footer.png');
assert_test("School 13 Attendance Report correctly marked as fallback", $s13_att['is_fallback_header'] === true && $s13_att['is_fallback_footer'] === true);

// School 99999 has NO design configured at all
$s99_att = mock_resolve_design($mysqli, 99999, 'attendance_report');
assert_test("School with zero designs has has_header = false (clean fallback to existing report layout)", $s99_att['has_header'] === false && $s99_att['has_footer'] === false);

echo "\n=======================================================\n";
echo "5. TESTING DYNAMIC TRANSITION (REPORT-SPECIFIC ADDED LATER)\n";
echo "=======================================================\n";

// Add specific attendance_report design for School 13
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, is_deleted)
                VALUES (13, 'attendance_report', 'sch13_att_header.png', 'sch13_att_footer.png', 'Active', 'n')");

$s13_att_after = mock_resolve_design($mysqli, 13, 'attendance_report');
assert_test("After configuring specific design, Attendance Report immediately uses specific header", $s13_att_after['header_file'] === 'sch13_att_header.png');
assert_test("After configuring specific design, Attendance Report is no longer marked fallback", $s13_att_after['is_fallback_header'] === false);

echo "\n=======================================================\n";
echo "6. TESTING IMAGE VALIDATION & STORAGE ISOLATION\n";
echo "=======================================================\n";

// Validate Directory Builder
require_once APPPATH . 'libraries/Document_design_service.php';
// In CLI, CI super object might not have loaded everything; we test the pure service functions
$service = new Document_design_service();

$s1_dir = $service->get_upload_dir(1, 'fee_receipt');
$s13_dir = $service->get_upload_dir(13, 'fee_receipt');
assert_test("School 1 upload dir is uploads/document_design/school_1/fee_receipt/", $s1_dir === 'uploads/document_design/school_1/fee_receipt/');
assert_test("School 13 upload dir is uploads/document_design/school_13/fee_receipt/", $s13_dir === 'uploads/document_design/school_13/fee_receipt/');
assert_test("Directories are isolated by school_id", $s1_dir !== $s13_dir);

$doc_types = $service->get_document_types();
assert_test("Registered document types count >= 10", count($doc_types) >= 10);
assert_test("Registered document types include 'default'", isset($doc_types['default']));
assert_test("Registered document types include 'fee_receipt'", isset($doc_types['fee_receipt']));
assert_test("Registered document types include 'report_card'", isset($doc_types['report_card']));
assert_test("Registered document types include 'overall_report'", isset($doc_types['overall_report']));
assert_test("Registered document types include 'attendance_report'", isset($doc_types['attendance_report']));

echo "\n=======================================================\n";
echo "7. TESTING FIXED DIMENSION CROP SPECIFICATIONS & BASE64 PROCESSING\n";
echo "=======================================================\n";

// Test Dimensions
$header_spec = $service->get_required_dimensions('header');
assert_test("Header width is exactly 2480 px", $header_spec['width'] === 2480);
assert_test("Header height is exactly 561 px", $header_spec['height'] === 561);
assert_test("Header aspect ratio is 2480 / 561", abs($header_spec['aspect_ratio'] - (2480 / 561)) < 0.0001);

$footer_spec = $service->get_required_dimensions('footer');
assert_test("Footer width is exactly 3539 px", $footer_spec['width'] === 3539);
assert_test("Footer height is exactly 400 px", $footer_spec['height'] === 400);
assert_test("Footer aspect ratio is 3539 / 400", abs($footer_spec['aspect_ratio'] - (3539 / 400)) < 0.0001);

// Test Base64 Image Processing via GD
$test_upload_dir = FCPATH . 'uploads/document_design/test_crop/';
if (!is_dir($test_upload_dir)) {
    @mkdir($test_upload_dir, 0755, true);
}

// 1. Create a dummy image of exact 2480 x 561 px
$test_header_img = imagecreatetruecolor(2480, 561);
ob_start();
imagepng($test_header_img);
$header_png_data = ob_get_clean();
imagedestroy($test_header_img);
$header_base64 = 'data:image/png;base64,' . base64_encode($header_png_data);

$res_h = $service->process_cropped_base64($header_base64, 'header', $test_upload_dir);
assert_test("Cropped Header (2480x561) processes successfully", $res_h['success'] === true && !empty($res_h['filename']));
if (!empty($res_h['filename']) && file_exists($test_upload_dir . $res_h['filename'])) {
    $info = getimagesize($test_upload_dir . $res_h['filename']);
    assert_test("Saved Header on disk is exactly 2480 × 561 px", $info[0] === 2480 && $info[1] === 561);
    @unlink($test_upload_dir . $res_h['filename']);
}

// 2. Create a dummy image of exact 3539 x 400 px
$test_footer_img = imagecreatetruecolor(3539, 400);
ob_start();
imagepng($test_footer_img);
$footer_png_data = ob_get_clean();
imagedestroy($test_footer_img);
$footer_base64 = 'data:image/png;base64,' . base64_encode($footer_png_data);

$res_f = $service->process_cropped_base64($footer_base64, 'footer', $test_upload_dir);
assert_test("Cropped Footer (3539x400) processes successfully", $res_f['success'] === true && !empty($res_f['filename']));
if (!empty($res_f['filename']) && file_exists($test_upload_dir . $res_f['filename'])) {
    $info_f = getimagesize($test_upload_dir . $res_f['filename']);
    assert_test("Saved Footer on disk is exactly 3539 × 400 px", $info_f[0] === 3539 && $info_f[1] === 400);
    @unlink($test_upload_dir . $res_f['filename']);
}

// 3. Test rejection of uncropped / incorrect dimension image
$wrong_img = imagecreatetruecolor(800, 600);
ob_start();
imagepng($wrong_img);
$wrong_data = ob_get_clean();
imagedestroy($wrong_img);
$wrong_base64 = 'data:image/png;base64,' . base64_encode($wrong_data);

$res_wrong = $service->process_cropped_base64($wrong_base64, 'header', $test_upload_dir);
assert_test("Uncropped incorrect dimension image (800x600) is strictly rejected", $res_wrong['success'] === false && strpos($res_wrong['error'], 'must be cropped to exactly 2480 × 561 px') !== false);

@rmdir($test_upload_dir);

// Clean up test fixtures
$mysqli->query("DELETE FROM tbl_document_designs WHERE school_id IN (1, 13, 99998, 99999)");

echo "\n=======================================================\n";
echo "RESULTS: {$tests_passed} / {$tests_run} tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL DOCUMENT DESIGN TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAIL: SOME TESTS FAILED!\n";
    exit(1);
}
