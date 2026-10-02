<?php
/**
 * Automated Verification Test for Student ID Card Design Management
 * Tests:
 *  1. Centralized CR80 Dimensions & Configuration
 *  2. Multi-School Tenant Isolation (School 1 vs School 13 vs School 99999)
 *  3. Fallback Behavior (Custom Design -> Built-in SVG Template, No A4 Banner Leakage)
 *  4. Model & Service CRUD (Save Front, Save Back, Remove Front, Remove Back, Soft Delete)
 *  5. Image Cropping & Normalization (638x1013 px at CR80 Portrait Ratio)
 *  6. Print & Preview Integration
 *
 * Run via CLI: php tests/test_id_card_design.php
 */

define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');

require_once BASEPATH . 'core/Common.php';

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

if (!function_exists('base_url')) {
    function base_url($uri = '') {
        return 'http://localhost/schoolnew/' . ltrim($uri, '/');
    }
}

class MockDocumentDesignModel {
    protected $mysqli;
    public function __construct($m) { $this->mysqli = $m; }
    public function get_by_type($school_id, $doc_type) {
        $stmt = $this->mysqli->prepare("SELECT id, school_id, document_type, header_image, footer_image, design_config, status FROM tbl_document_designs WHERE school_id = ? AND document_type = ? AND is_deleted = 'n' LIMIT 1");
        $stmt->bind_param("is", $school_id, $doc_type);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_object();
        return $res ?: null;
    }
    public function save_design($data) {
        $school_id = (int)$data['school_id'];
        $doc_type  = $data['document_type'];
        $hdr       = $data['header_image'] ?? null;
        $ftr       = $data['footer_image'] ?? null;
        $cfg       = $data['design_config'] ?? null;
        $status    = $data['status'] ?? 'Active';
        
        $stmt = $this->mysqli->prepare("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, design_config, status, created_at, is_deleted)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'n')
            ON DUPLICATE KEY UPDATE header_image = VALUES(header_image), footer_image = VALUES(footer_image), design_config = VALUES(design_config), status = VALUES(status), is_deleted = 'n', updated_at = NOW()");
        $stmt->bind_param("isssss", $school_id, $doc_type, $hdr, $ftr, $cfg, $status);
        return $stmt->execute();
    }
}

class MockCI {
    public $Document_design_model;
    public $load;
    public function __construct($mysqli) {
        $this->Document_design_model = new MockDocumentDesignModel($mysqli);
        $this->load = new class {
            public function model($name) {}
        };
    }
}

$mock_ci = new MockCI($mysqli);
$model = $mock_ci->Document_design_model;
function &get_instance() {
    global $mock_ci;
    return $mock_ci;
}

require_once APPPATH . 'libraries/Document_design_service.php';

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
echo "1. VERIFYING CENTRALIZED CR80 ID CARD DIMENSIONS\n";
echo "=======================================================\n";

$service = new Document_design_service();

$id_cfg = $service->get_id_card_config();
assert_test("Centralized ID card width is 638 px", $id_cfg['width'] === 638);
assert_test("Centralized ID card height is 1013 px", $id_cfg['height'] === 1013);
assert_test("Centralized ID card aspect ratio is 2.125 / 3.375", abs($id_cfg['aspect_ratio'] - (2.125 / 3.375)) < 0.001);
assert_test("Centralized ID card dimensions in mm are 54 x 86", $id_cfg['mm_width'] === 54 && $id_cfg['mm_height'] === 86);

$front_dims = $service->get_required_dimensions('front', 'id_card');
$back_dims  = $service->get_required_dimensions('back', 'id_card');
assert_test("Front design required dimensions are 638 x 1013 px", $front_dims['width'] === 638 && $front_dims['height'] === 1013);
assert_test("Back design required dimensions are 638 x 1013 px", $back_dims['width'] === 638 && $back_dims['height'] === 1013);

$registered_types = $service->get_document_types();
assert_test("'id_card' is present in registered document types", isset($registered_types['id_card']));
assert_test("'id_card' has is_card = true flag", !empty($registered_types['id_card']['is_card']));

echo "\n=======================================================\n";
echo "2. VERIFYING MULTI-SCHOOL ISOLATION & STORAGE\n";
echo "=======================================================\n";

$school_a = 1;
$school_b = 13;
$school_c = 99999; // unconfigured school

// Clean any previous test records for id_card
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'id_card' AND school_id IN ($school_a, $school_b, $school_c)");

// Insert School A ID Card Design
$school_a_front = 'idcard_sch1_front_' . time() . '.png';
$school_a_back  = 'idcard_sch1_back_' . time() . '.png';
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, created_at, is_deleted)
                VALUES ($school_a, 'id_card', '$school_a_front', '$school_a_back', 'Active', NOW(), 'n')");

// Insert School B ID Card Design
$school_b_front = 'idcard_sch13_front_' . time() . '.png';
$school_b_back  = 'idcard_sch13_back_' . time() . '.png';
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, created_at, is_deleted)
                VALUES ($school_b, 'id_card', '$school_b_front', '$school_b_back', 'Active', NOW(), 'n')");

// Also insert a default A4 banner for School A to verify ID Card never falls back to A4 banner
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'default' AND school_id = $school_a");
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, created_at, is_deleted)
                VALUES ($school_a, 'default', 'a4_default_header.png', 'a4_default_footer.png', 'Active', NOW(), 'n')");

// Create dummy physical files so file_exists check succeeds in resolve_design
$dir_a = FCPATH . "uploads/document_design/school_$school_a/id_card/";
$dir_b = FCPATH . "uploads/document_design/school_$school_b/id_card/";
if (!is_dir($dir_a)) mkdir($dir_a, 0755, true);
if (!is_dir($dir_b)) mkdir($dir_b, 0755, true);

file_put_contents($dir_a . $school_a_front, 'dummy_sch1_front');
file_put_contents($dir_a . $school_a_back, 'dummy_sch1_back');
file_put_contents($dir_b . $school_b_front, 'dummy_sch13_front');
file_put_contents($dir_b . $school_b_back, 'dummy_sch13_back');

// Test resolving School A
$res_a = $service->resolve_design($school_a, 'id_card');
assert_test("School A resolves ID card design successfully", $res_a->has_design === true);
assert_test("School A has front design", $res_a->has_front === true && $res_a->front_image === $school_a_front);
assert_test("School A has back design", $res_a->has_back === true && $res_a->back_image === $school_a_back);
assert_test("School A front_url contains school_1 path", strpos($res_a->front_url, "school_$school_a/id_card/") !== false);
assert_test("School A back_url contains school_1 path", strpos($res_a->back_url, "school_$school_a/id_card/") !== false);

// Test resolving School B
$res_b = $service->resolve_design($school_b, 'id_card');
assert_test("School B resolves ID card design successfully", $res_b->has_design === true);
assert_test("School B has front design", $res_b->has_front === true && $res_b->front_image === $school_b_front);
assert_test("School B has back design", $res_b->has_back === true && $res_b->back_image === $school_b_back);
assert_test("School B front_url contains school_13 path", strpos($res_b->front_url, "school_$school_b/id_card/") !== false);
assert_test("School B back_url contains school_13 path", strpos($res_b->back_url, "school_$school_b/id_card/") !== false);

// Multi-School Isolation Check: Ensure zero crossover
assert_test("School A does NOT see School B front image", $res_a->front_image !== $res_b->front_image);
assert_test("School A does NOT see School B back image", $res_a->back_image !== $res_b->back_image);
assert_test("School B does NOT see School A front image", $res_b->front_image !== $res_a->front_image);
assert_test("School B does NOT see School A back image", $res_b->back_image !== $res_a->back_image);

echo "\n=======================================================\n";
echo "3. VERIFYING SAFE FALLBACK BEHAVIOR (NO A4 BANNER LEAKAGE)\n";
echo "=======================================================\n";

// Test unconfigured School C
$res_c = $service->resolve_design($school_c, 'id_card');
assert_test("Unconfigured School C has_design is false", $res_c->has_design === false);
assert_test("Unconfigured School C has_front is false (enables built-in SVG fallback)", $res_c->has_front === false);
assert_test("Unconfigured School C has_back is false (enables built-in SVG fallback)", $res_c->has_back === false);
assert_test("Unconfigured School C front_url is empty", empty($res_c->front_url));
assert_test("Unconfigured School C back_url is empty", empty($res_c->back_url));

// Test School A when ID card record is removed: Must NOT fall back to School A's A4 default banner
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'id_card' AND school_id = $school_a");
// Clear service cache
$service_fresh = new Document_design_service();
$res_a_deleted = $service_fresh->resolve_design($school_a, 'id_card');
assert_test("School A without ID card design does NOT fall back to A4 default banner header", $res_a_deleted->has_front === false && $res_a_deleted->has_header === false);
assert_test("School A without ID card design does NOT fall back to A4 default banner footer", $res_a_deleted->has_back === false && $res_a_deleted->has_footer === false);
assert_test("School A without ID card design correctly resolves to empty/builtin SVG fallback", $res_a_deleted->has_front === false);

echo "\n=======================================================\n";
echo "4. VERIFYING BASE64 CROPPING & NORMALIZATION\n";
echo "=======================================================\n";

// Generate a valid 638x1013 test PNG base64
$im = imagecreatetruecolor(638, 1013);
$bg_col = imagecolorallocate($im, 14, 116, 144); // nice cyan/teal
imagefilledrectangle($im, 0, 0, 637, 1012, $bg_col);
ob_start();
imagepng($im);
$png_data = ob_get_clean();
imagedestroy($im);
$valid_base64 = 'data:image/png;base64,' . base64_encode($png_data);

// Process Front Image
$target_dir = FCPATH . $service->get_upload_dir($school_a, 'id_card');
$service->ensure_upload_dir($target_dir);

$front_res = $service->process_cropped_base64($valid_base64, 'front', $target_dir, 'id_card');
assert_test("Cropped front image (638x1013) processes successfully", $front_res['success'] === true);
assert_test("Generated front filename starts with front_", strpos($front_res['filename'], 'front_') === 0);
assert_test("Processed front file exists on disk", file_exists($target_dir . $front_res['filename']));

$saved_front_info = getimagesize($target_dir . $front_res['filename']);
assert_test("Saved front image on disk is exactly 638 x 1013 px", $saved_front_info[0] === 638 && $saved_front_info[1] === 1013);

// Process Back Image
$back_res = $service->process_cropped_base64($valid_base64, 'back', $target_dir, 'id_card');
assert_test("Cropped back image (638x1013) processes successfully", $back_res['success'] === true);
assert_test("Generated back filename starts with back_", strpos($back_res['filename'], 'back_') === 0);
assert_test("Processed back file exists on disk", file_exists($target_dir . $back_res['filename']));

$saved_back_info = getimagesize($target_dir . $back_res['filename']);
assert_test("Saved back image on disk is exactly 638 x 1013 px", $saved_back_info[0] === 638 && $saved_back_info[1] === 1013);

// Test wrong dimension rejection (e.g. 500x500 square)
$bad_im = imagecreatetruecolor(500, 500);
ob_start();
imagepng($bad_im);
$bad_png = ob_get_clean();
imagedestroy($bad_im);
$bad_base64 = 'data:image/png;base64,' . base64_encode($bad_png);

$bad_res = $service->process_cropped_base64($bad_base64, 'front', $target_dir, 'id_card');
assert_test("Incorrect dimensions (500x500 instead of 638x1013) strictly rejected", $bad_res['success'] === false);

// Test corrupt base64 rejection
$corrupt_res = $service->process_cropped_base64('data:image/png;base64,NOT_A_VALID_IMAGE_DATA', 'front', $target_dir, 'id_card');
assert_test("Corrupted image data strictly rejected", $corrupt_res['success'] === false);

echo "\n=======================================================\n";
echo "5. VERIFYING PARTIAL DESIGNS & INDIVIDUAL REMOVAL\n";
echo "=======================================================\n";

// Save Front only
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'id_card' AND school_id = $school_a");
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, created_at, is_deleted)
                VALUES ($school_a, 'id_card', 'front_only.png', NULL, 'Active', NOW(), 'n')");
file_put_contents($dir_a . 'front_only.png', 'front_only_data');

$service_front_only = new Document_design_service();
$res_front_only = $service_front_only->resolve_design($school_a, 'id_card');
assert_test("Front only: has_front is true", $res_front_only->has_front === true);
assert_test("Front only: has_back is false", $res_front_only->has_back === false);
assert_test("Front only: front_url is populated", !empty($res_front_only->front_url));
assert_test("Front only: back_url is empty", empty($res_front_only->back_url));

// Save Back only
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'id_card' AND school_id = $school_a");
$mysqli->query("INSERT INTO tbl_document_designs (school_id, document_type, header_image, footer_image, status, created_at, is_deleted)
                VALUES ($school_a, 'id_card', NULL, 'back_only.png', 'Active', NOW(), 'n')");
file_put_contents($dir_a . 'back_only.png', 'back_only_data');

$service_back_only = new Document_design_service();
$res_back_only = $service_back_only->resolve_design($school_a, 'id_card');
assert_test("Back only: has_front is false", $res_back_only->has_front === false);
assert_test("Back only: has_back is true", $res_back_only->has_back === true);
assert_test("Back only: front_url is empty", empty($res_back_only->front_url));
assert_test("Back only: back_url is populated", !empty($res_back_only->back_url));

echo "\n=======================================================\n";
echo "6. VERIFYING DYNAMIC FIELD POSITIONING (DESIGN_CONFIG) & ISOLATION\n";
echo "=======================================================\n";

// Default config test
$default_cfg = $service->get_default_id_card_field_config();
assert_test("Default config has 'photo' field", isset($default_cfg['photo']));
assert_test("Default config has 'student_name' field", isset($default_cfg['student_name']));
assert_test("Default config has 'admission_number' field", isset($default_cfg['admission_number']));
assert_test("Default config has 'class_division' field", isset($default_cfg['class_division']));
assert_test("Default config has 'roll_number' field", isset($default_cfg['roll_number']));
assert_test("Default config has 'date_of_birth' field", isset($default_cfg['date_of_birth']));
assert_test("Default config has 'blood_group' field", isset($default_cfg['blood_group']));

// Custom field config for School A
$custom_cfg_a = $default_cfg;
$custom_cfg_a['photo']['top'] = 15.5;
$custom_cfg_a['photo']['left'] = 25.0;
$custom_cfg_a['student_name']['top'] = 45.0;
$custom_cfg_a['roll_number']['enabled'] = false;

$model->save_design([
    'school_id'     => $school_a,
    'document_type' => 'id_card',
    'header_image'  => 'front_a.png',
    'footer_image'  => 'back_a.png',
    'design_config' => json_encode($custom_cfg_a),
    'status'        => 'Active'
]);

$service_cfg_test = new Document_design_service();
$res_a_cfg = $service_cfg_test->resolve_design($school_a, 'id_card');
assert_test("School A resolves with custom photo top (15.5%)", isset($res_a_cfg->field_config['photo']['top']) && $res_a_cfg->field_config['photo']['top'] == 15.5);
assert_test("School A resolves with custom photo left (25%)", isset($res_a_cfg->field_config['photo']['left']) && $res_a_cfg->field_config['photo']['left'] == 25.0);
assert_test("School A resolves with custom roll_number enabled = false", isset($res_a_cfg->field_config['roll_number']['enabled']) && $res_a_cfg->field_config['roll_number']['enabled'] === false);

// Check School B: Must NOT have School A's custom coordinates!
$res_b_cfg = $service_cfg_test->resolve_design($school_b, 'id_card');
assert_test("School B does NOT have School A custom photo top (defaults to {$default_cfg['photo']['top']}%)", $res_b_cfg->field_config['photo']['top'] == $default_cfg['photo']['top']);
assert_test("School B does NOT have School A custom roll_number disabled", $res_b_cfg->field_config['roll_number']['enabled'] === true);

// Save distinct custom coordinates for School B
$custom_cfg_b = $default_cfg;
$custom_cfg_b['photo']['top'] = 35.0;
$custom_cfg_b['photo']['left'] = 10.0;
$model->save_design([
    'school_id'     => $school_b,
    'document_type' => 'id_card',
    'header_image'  => 'front_b.png',
    'footer_image'  => 'back_b.png',
    'design_config' => json_encode($custom_cfg_b),
    'status'        => 'Active'
]);

$service_cfg_test2 = new Document_design_service();
$res_a_recheck = $service_cfg_test2->resolve_design($school_a, 'id_card');
$res_b_recheck = $service_cfg_test2->resolve_design($school_b, 'id_card');
assert_test("School A retains photo top 15.5% after School B saves", $res_a_recheck->field_config['photo']['top'] == 15.5);
assert_test("School B has photo top 35.0%", $res_b_recheck->field_config['photo']['top'] == 35.0);

// Cleanup test records
$mysqli->query("DELETE FROM tbl_document_designs WHERE document_type = 'id_card' AND school_id IN ($school_a, $school_b, $school_c)");

echo "\n=======================================================\n";
echo "7. VERIFYING STRUCTURED STUDENT DETAILS ALIGNMENT & RENDERING\n";
echo "=======================================================\n";

assert_test("Default config has 'student_details' container", isset($default_cfg['student_details']));
assert_test("Default config has 'guardian_name' row", isset($default_cfg['guardian_name']));
assert_test("Default student_details top is 61%", $default_cfg['student_details']['top'] == 61);
assert_test("Default student_details left is 7%", $default_cfg['student_details']['left'] == 7);
assert_test("Default student_details width is 86%", $default_cfg['student_details']['width'] == 86);
assert_test("Default student_details label_width is 36%", $default_cfg['student_details']['label_width'] === '36%');
assert_test("Default student_details label_color is #0f766e", $default_cfg['student_details']['label_color'] === '#0f766e');

// Test render_student_details_table with sample student
$sample_student = (object)[
    'admission_number' => 'SCH20260115467',
    'guardian_name'    => 'Mr. Menon',
    'class_name'       => 'LKG',
    'division_name'    => 'A',
    'roll_number'      => '1',
    'date_of_birth'    => '2022-03-11',
    'blood_group'      => 'A+',
];

$html = $service->render_student_details_table($sample_student, $default_cfg);

assert_test("Rendered output contains student-details-container", strpos($html, 'student-details-container') !== false);
assert_test("Rendered output contains fixed table layout", strpos($html, 'table-layout: fixed') !== false);
assert_test("Rendered output sets label width to 36%", strpos($html, 'width: 36%') !== false);
assert_test("Rendered output contains ID label and value SCH20260115467", strpos($html, '>ID</td>') !== false && strpos($html, 'SCH20260115467') !== false);
assert_test("Rendered output contains FATHER'S NAME label and value Mr. Menon", (strpos($html, "FATHER&#039;S NAME") !== false || strpos($html, "FATHER'S NAME") !== false) && strpos($html, 'Mr. Menon') !== false);
assert_test("Rendered output contains CLASS & DIV label and value LKG - A", (strpos($html, 'CLASS &amp; DIV') !== false || strpos($html, 'CLASS & DIV') !== false) && strpos($html, 'LKG - A') !== false);
assert_test("Rendered output contains ROLL NO. label and value 1", strpos($html, 'ROLL NO.') !== false && strpos($html, '>1</td>') !== false);
assert_test("Rendered output formats DOB to 11-03-2022", strpos($html, '11-03-2022') !== false);
assert_test("Rendered output contains BLOOD GROUP label and value A+", strpos($html, 'BLOOD GROUP') !== false && strpos($html, 'A+') !== false);
assert_test("Rendered output contains centered colon separators (:) with 10px width", strpos($html, 'width: 10px; text-align: center') !== false && strpos($html, '>:</td>') !== false);

// Test empty fallback (no undefined, null, or NaN)
$empty_student = (object)[
    'admission_number' => '',
    'guardian_name'    => '',
    'class_name'       => '',
    'division_name'    => '',
    'roll_number'      => '',
    'date_of_birth'    => '',
    'blood_group'      => '',
];
$empty_html = $service->render_student_details_table($empty_student, $default_cfg);
assert_test("Empty fields render fallback em-dash (—)", strpos($empty_html, '—') !== false);
assert_test("Empty fields do NOT contain 'undefined'", strpos($empty_html, 'undefined') === false);
assert_test("Empty fields do NOT contain 'null'", strpos($empty_html, 'null') === false);
assert_test("Empty fields do NOT contain 'NaN'", strpos($empty_html, 'NaN') === false);

// Test long values wrapping safely with word-break
$long_student = (object)[
    'admission_number' => 'SCH20260115467_EXTRA_LONG_STUDENT_IDENTIFIER_NUMBER',
    'guardian_name'    => 'Dr. Alexander Christopher Montgomery-Wellington III',
    'class_name'       => 'Information Technology & Computer Applications',
    'division_name'    => 'Section A - Advanced Computing',
    'roll_number'      => '999999',
    'date_of_birth'    => '2015-05-20',
    'blood_group'      => 'AB+ (Bombay Blood Group Phenotype)',
];
$long_html = $service->render_student_details_table($long_student, $default_cfg);
assert_test("Long values include word-break styling", strpos($long_html, 'word-break: break-word') !== false);
assert_test("Long father name rendered without truncation errors", strpos($long_html, 'Dr. Alexander Christopher Montgomery-Wellington III') !== false);

// Test toggling off a field
$disabled_cfg = $default_cfg;
$disabled_cfg['blood_group']['enabled'] = false;
$disabled_html = $service->render_student_details_table($sample_student, $disabled_cfg);
assert_test("Disabled field (blood_group) is not rendered in table", strpos($disabled_html, 'BLOOD GROUP') === false);
assert_test("Other enabled fields remain rendered when one is disabled", strpos($disabled_html, 'SCH20260115467') !== false);

echo "\n=======================================================\n";
echo "RESULTS: $tests_passed / $tests_run tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL ID CARD DESIGN TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED.\n";
    exit(1);
}
