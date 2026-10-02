<?php
/**
 * Test: PDF & Document UI Isolation Verification
 *
 * Verifies that:
 * 1. Document templates (templates/document_header.php, templates/document_footer.php)
 *    do not include any web application UI (#sidebar-root, #header-root, navbar, etc.)
 * 2. mPDF templates (overall_report_mpdf, attendance_pdf_mpdf) contain zero web application UI
 * 3. Document controllers (report_card, progress_report, receipt, tc_print) use render_document()
 * 4. assets/app.css contains strict @media print rules suppressing #sidebar-root, #header-root,
 *    nav, aside, headers, action bars, etc.
 */

define('BASEPATH', true);
define('FCPATH', dirname(__DIR__) . '/');

$tests_run = 0;
$tests_passed = 0;

function assert_check($desc, $cond) {
    global $tests_run, $tests_passed;
    $tests_run++;
    if ($cond) {
        $tests_passed++;
        echo "[PASS] $desc\n";
    } else {
        echo "[FAIL] $desc\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING DEDICATED DOCUMENT TEMPLATES (ZERO APP UI)\n";
echo "=======================================================\n";

$doc_header_file = FCPATH . 'application/views/templates/document_header.php';
assert_check("document_header.php exists", file_exists($doc_header_file));
$doc_header_content = file_get_contents($doc_header_file);

assert_check("document_header does NOT contain #sidebar-root in body markup", strpos($doc_header_content, '<div id="sidebar-root"') === false);
assert_check("document_header does NOT contain #header-root in body markup", strpos($doc_header_content, '<div id="header-root"') === false);
assert_check("document_header does NOT load templates/sidebar", strpos($doc_header_content, 'templates/sidebar') === false);
assert_check("document_header does NOT load templates/header", strpos($doc_header_content, 'templates/header') === false);
assert_check("document_header contains @media print rules hiding UI", strpos($doc_header_content, '@media print') !== false);

echo "\n=======================================================\n";
echo "2. VERIFYING MPDF SERVER-SIDE PDF VIEW TEMPLATES (ZERO APP UI)\n";
echo "=======================================================\n";

$overall_mpdf_file = FCPATH . 'application/views/pages/students/overall_report_mpdf.php';
$attendance_mpdf_file = FCPATH . 'application/views/pages/students/attendance_pdf_mpdf.php';

assert_check("overall_report_mpdf.php exists", file_exists($overall_mpdf_file));
$overall_mpdf = file_get_contents($overall_mpdf_file);
assert_check("overall_report_mpdf does NOT contain #sidebar-root", strpos($overall_mpdf, 'sidebar-root') === false);
assert_check("overall_report_mpdf does NOT contain #header-root", strpos($overall_mpdf, 'header-root') === false);
assert_check("overall_report_mpdf does NOT contain Dashboard navbar text", strpos($overall_mpdf, 'Dashboard') === false);
assert_check("overall_report_mpdf does NOT load templates/header", strpos($overall_mpdf, 'templates/header') === false);

assert_check("attendance_pdf_mpdf.php exists", file_exists($attendance_mpdf_file));
$attendance_mpdf = file_get_contents($attendance_mpdf_file);
assert_check("attendance_pdf_mpdf does NOT contain #sidebar-root", strpos($attendance_mpdf, 'sidebar-root') === false);
assert_check("attendance_pdf_mpdf does NOT contain #header-root", strpos($attendance_mpdf, 'header-root') === false);
assert_check("attendance_pdf_mpdf does NOT contain Dashboard navbar text", strpos($attendance_mpdf, 'Dashboard') === false);
assert_check("attendance_pdf_mpdf does NOT load templates/header", strpos($attendance_mpdf, 'templates/header') === false);

echo "\n=======================================================\n";
echo "3. VERIFYING CONTROLLER DEDICATED DOCUMENT ARCHITECTURE\n";
echo "=======================================================\n";

$exam_ctrl = file_get_contents(FCPATH . 'application/controllers/Examinations.php');
assert_check("Examinations::report_card calls render_document", strpos($exam_ctrl, "render_document('pages/examinations/report_card_view'") !== false);
assert_check("Examinations::progress_report calls render_document", strpos($exam_ctrl, "render_document('pages/examinations/progress_report_view'") !== false);

$fees_ctrl = file_get_contents(FCPATH . 'application/controllers/Fees.php');
assert_check("Fees::receipt calls render_document", strpos($fees_ctrl, "render_document('pages/fees/receipt_view'") !== false);

$students_ctrl = file_get_contents(FCPATH . 'application/controllers/Students.php');
assert_check("Students::tc calls render_document", strpos($students_ctrl, "render_document('pages/students/tc_print'") !== false);

$my_ctrl = file_get_contents(FCPATH . 'application/core/MY_Controller.php');
assert_check("MY_Controller has public function render_document", strpos($my_ctrl, "public function render_document(") !== false);
assert_check("MY_Controller::render_document loads templates/document_header", strpos($my_ctrl, "load->view('templates/document_header'") !== false);
assert_check("MY_Controller::render_document loads templates/document_footer", strpos($my_ctrl, "load->view('templates/document_footer'") !== false);

echo "\n=======================================================\n";
echo "4. VERIFYING GLOBAL PRINT STYLESHEET IN APP.CSS\n";
echo "=======================================================\n";

$app_css = file_get_contents(FCPATH . 'assets/app.css');
assert_check("app.css contains @media print", strpos($app_css, '@media print') !== false);
assert_check("app.css @media print hides #sidebar-root", strpos($app_css, '#sidebar-root') !== false);
assert_check("app.css @media print hides #header-root", strpos($app_css, '#header-root') !== false);
assert_check("app.css @media print hides .no-print", strpos($app_css, '.no-print') !== false);
assert_check("app.css @media print resets body background to white", strpos($app_css, 'background: #ffffff !important') !== false);

echo "\n=======================================================\n";
echo "5. VERIFYING DOCUMENT DESIGN HEADERS AND FOOTERS IN VIEWS\n";
echo "=======================================================\n";

$report_card_view = file_get_contents(FCPATH . 'application/views/pages/examinations/report_card_view.php');
assert_check("report_card_view includes document design header check", strpos($report_card_view, 'document_design->has_header') !== false);
assert_check("report_card_view includes document design footer check", strpos($report_card_view, 'document_design->has_footer') !== false);
assert_check("report_card_view has no-print on toolbar", strpos($report_card_view, 'no-print print:hidden') !== false);

$progress_view = file_get_contents(FCPATH . 'application/views/pages/examinations/progress_report_view.php');
assert_check("progress_report_view includes document design header check", strpos($progress_view, 'document_design->has_header') !== false);
assert_check("progress_report_view includes document design footer check", strpos($progress_view, 'document_design->has_footer') !== false);
assert_check("progress_report_view has no-print on toolbar", strpos($progress_view, 'no-print print:hidden') !== false);

$receipt_view = file_get_contents(FCPATH . 'application/views/pages/fees/receipt_view.php');
assert_check("receipt_view includes document design header check", strpos($receipt_view, 'document_design->has_header') !== false);
assert_check("receipt_view includes document design footer check", strpos($receipt_view, 'document_design->has_footer') !== false);
assert_check("receipt_view has no-print on toolbar", strpos($receipt_view, 'no-print print:hidden') !== false);

$tc_view = file_get_contents(FCPATH . 'application/views/pages/students/tc_print.php');
assert_check("tc_print includes document design header check", strpos($tc_view, 'document_design->has_header') !== false);
assert_check("tc_print includes document design footer check", strpos($tc_view, 'document_design->has_footer') !== false);
assert_check("tc_print has no-print on toolbar", strpos($tc_view, 'no-print') !== false);

echo "\n=======================================================\n";
echo "RESULTS: {$tests_passed} / {$tests_run} tests passed.\n";
echo "=======================================================\n";

if ($tests_passed === $tests_run) {
    echo "SUCCESS: ALL PDF & DOCUMENT UI ISOLATION TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAIL: SOME TESTS FAILED!\n";
    exit(1);
}
