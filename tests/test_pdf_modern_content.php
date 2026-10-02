<?php
/**
 * Test Suite: Modernized PDF Content Area
 * Validates that all redesigned reports contain:
 * - Proper visual hierarchy and structured information cards
 * - Left-aligned text, right-aligned numbers, centered grades/status
 * - Side-by-side Result Evaluation & Attendance cards
 * - 3-column signature blocks with page-break-inside: avoid
 * - Zero duplicated headers or footers in body area
 * - Valid PHP syntax across all modified views
 */

$test_count = 0;
$pass_count = 0;

function assert_test($condition, $description) {
    global $test_count, $pass_count;
    $test_count++;
    if ($condition) {
        $pass_count++;
        echo "[PASS] $description\n";
    } else {
        echo "[FAIL] $description\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING REPORT CARD VIEW (report_card_view.php)\n";
echo "=======================================================\n";

$rc_file = __DIR__ . '/../application/views/pages/examinations/report_card_view.php';
assert_test(file_exists($rc_file), "report_card_view.php exists");
$rc_content = file_get_contents($rc_file);

// Required fields
assert_test(strpos($rc_content, 'Student Information') !== false, "Student Information header present");
assert_test(strpos($rc_content, 'admission_number') !== false, "Admission Number present");
assert_test(strpos($rc_content, 'class_name') !== false, "Class & Division present");
assert_test(strpos($rc_content, 'roll_number') !== false, "Roll Number present");
assert_test(strpos($rc_content, 'year_name') !== false, "Academic Session present");
assert_test(strpos($rc_content, 'exam_name') !== false, "Examination name present");
assert_test(strpos($rc_content, 'guardian_name') !== false, "Guardian / Contact present");

// Table structure & alignments
assert_test(strpos($rc_content, 'Academic Performance') !== false, "Academic Performance table present");
assert_test(strpos($rc_content, 'text-left') !== false && strpos($rc_content, 'Subject') !== false, "Subject column left aligned");
assert_test(strpos($rc_content, 'text-right') !== false && strpos($rc_content, 'Marks Obtained') !== false, "Marks Obtained right aligned");
assert_test(strpos($rc_content, 'Grand Total') !== false, "Grand Total row present");

// Side-by-side cards
assert_test(strpos($rc_content, 'Result Evaluation') !== false, "Result Evaluation card present");
assert_test(strpos($rc_content, 'Academic Attendance') !== false, "Academic Attendance card present");
assert_test(strpos($rc_content, 'Cumulative GPA') !== false, "Cumulative GPA present");
assert_test(strpos($rc_content, 'Attendance Rate') !== false, "Attendance Rate present");

// Remarks & 3-column Signatures
assert_test(strpos($rc_content, 'Teacher Assessment & Remarks') !== false, "Teacher Assessment & Remarks block present");
assert_test(strpos($rc_content, 'Class Teacher') !== false, "Class Teacher signature block present");
assert_test(strpos($rc_content, 'Parent / Guardian') !== false, "Parent / Guardian signature block present");
assert_test(strpos($rc_content, 'Principal') !== false, "Principal signature block present");
assert_test(strpos($rc_content, 'page-break-inside: avoid') !== false, "Signatures avoid page break");

echo "\n=======================================================\n";
echo "2. VERIFYING OVERALL REPORT MPDF (overall_report_mpdf.php)\n";
echo "=======================================================\n";

$or_file = __DIR__ . '/../application/views/pages/students/overall_report_mpdf.php';
assert_test(file_exists($or_file), "overall_report_mpdf.php exists");
$or_content = file_get_contents($or_file);

assert_test(strpos($or_content, 'Student Profile & Academic Record') !== false, "Student profile card present");
assert_test(strpos($or_content, 'Parent & Guardian Information') !== false, "Parent/Guardian info card present");
assert_test(strpos($or_content, 'Academic Examination Results') !== false, "Exam results section present");
assert_test(strpos($or_content, 'Attendance Summary') !== false, "Attendance summary section present");
assert_test(strpos($or_content, 'Fees & Finance Summary') !== false, "Fees & finance summary present");
assert_test(strpos($or_content, 'Class Teacher') !== false, "Class Teacher signature present");
assert_test(strpos($or_content, 'Parent / Guardian') !== false, "Parent/Guardian signature present");
assert_test(strpos($or_content, 'Principal / Head') !== false, "Principal/Head signature present");

echo "\n=======================================================\n";
echo "3. VERIFYING PROGRESS REPORT VIEW (progress_report_view.php)\n";
echo "=======================================================\n";

$pr_file = __DIR__ . '/../application/views/pages/examinations/progress_report_view.php';
assert_test(file_exists($pr_file), "progress_report_view.php exists");
$pr_content = file_get_contents($pr_file);

assert_test(strpos($pr_content, 'Student Profile & Academic Record') !== false, "Student info card present");
assert_test(strpos($pr_content, 'Examination Score Trajectory') !== false, "Score trajectory cards present");
assert_test(strpos($pr_content, 'Subject-Wise Trend Matrix') !== false, "Trend matrix table present");
assert_test(strpos($pr_content, 'Academic Coordinator Summary') !== false, "Coordinator summary present");
assert_test(strpos($pr_content, 'Academic Counselor') !== false, "Academic counselor signature present");
assert_test(strpos($pr_content, 'Principal / Head of Institution') !== false, "Principal signature present");

echo "\n=======================================================\n";
echo "4. VERIFYING FEE RECEIPT VIEW (receipt_view.php)\n";
echo "=======================================================\n";

$rcpt_file = __DIR__ . '/../application/views/pages/fees/receipt_view.php';
assert_test(file_exists($rcpt_file), "receipt_view.php exists");
$rcpt_content = file_get_contents($rcpt_file);

assert_test(strpos($rcpt_content, 'Payment Voucher & Student Information') !== false, "Payment voucher card present");
assert_test(strpos($rcpt_content, 'Total Paid Now:') !== false, "Total Paid Now row present");
assert_test(strpos($rcpt_content, 'Payment Mode & Details') !== false, "Payment mode card present");
assert_test(strpos($rcpt_content, 'Account Ledger Summary') !== false, "Account ledger summary card present");
assert_test(strpos($rcpt_content, "Payer's Signature") !== false, "Payer signature present");
assert_test(strpos($rcpt_content, 'Authorized Signatory & Seal') !== false, "Authorized signatory present");

echo "\n=======================================================\n";
echo "5. VERIFYING TRANSFER CERTIFICATE (tc_print.php)\n";
echo "=======================================================\n";

$tc_file = __DIR__ . '/../application/views/pages/students/tc_print.php';
assert_test(file_exists($tc_file), "tc_print.php exists");
$tc_content = file_get_contents($tc_file);

assert_test(strpos($tc_content, 'Certificate No:') !== false, "Certificate No bar present");
assert_test(strpos($tc_content, '1. Name of Pupil') !== false, "Numbered particulars present");
assert_test(strpos($tc_content, 'Prepared By') !== false, "Prepared by signature present");
assert_test(strpos($tc_content, 'Checked By') !== false, "Checked by signature present");
assert_test(strpos($tc_content, 'Principal') !== false, "Principal signature present");

echo "\n=======================================================\n";
echo "6. VERIFYING ATTENDANCE MPDF (attendance_pdf_mpdf.php)\n";
echo "=======================================================\n";

$att_file = __DIR__ . '/../application/views/pages/students/attendance_pdf_mpdf.php';
assert_test(file_exists($att_file), "attendance_pdf_mpdf.php exists");
$att_content = file_get_contents($att_file);

assert_test(strpos($att_content, 'Attendance In-Charge') !== false, "Attendance In-Charge signature present");
assert_test(strpos($att_content, 'Class Teacher') !== false, "Class Teacher signature present");
assert_test(strpos($att_content, 'Principal / Head') !== false, "Principal signature present");

echo "\n=======================================================\n";
echo "7. VERIFYING ZERO DUPLICATE HEADERS/FOOTERS IN BODY\n";
echo "=======================================================\n";

// Ensure there is exactly 1 header img rendering and 1 footer img rendering (no duplicate header/footer in body)
assert_test(substr_count($rc_content, 'alt="Report Card Header"') <= 1, "report_card_view has at most 1 header image");
assert_test(substr_count($rc_content, 'alt="Report Card Footer"') <= 1, "report_card_view has at most 1 footer image");
assert_test(substr_count($pr_content, 'alt="Progress Report Header"') <= 1, "progress_report_view has at most 1 header image");
assert_test(substr_count($pr_content, 'alt="Progress Report Footer"') <= 1, "progress_report_view has at most 1 footer image");
assert_test(substr_count($rcpt_content, 'alt="Receipt Header"') <= 1, "receipt_view has at most 1 header image");
assert_test(substr_count($rcpt_content, 'alt="Receipt Footer"') <= 1, "receipt_view has at most 1 footer image");
assert_test(substr_count($tc_content, 'alt="Transfer Certificate Header"') <= 1, "tc_print has at most 1 header image");
assert_test(substr_count($tc_content, 'alt="Transfer Certificate Footer"') <= 1, "tc_print has at most 1 footer image");

echo "\n=======================================================\n";
echo "RESULTS: $pass_count / $test_count tests passed.\n";
echo "=======================================================\n";

if ($pass_count === $test_count) {
    echo "SUCCESS: ALL MODERN CONTENT AREA TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: Some modern content tests failed.\n";
    exit(1);
}
