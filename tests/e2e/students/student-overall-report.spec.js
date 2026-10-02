// @ts-check
const { test, expect } = require('../fixtures/auth.fixture');
const zlib = require('zlib');

function extractPdfText(buffer) {
  const content = buffer.toString('binary');
  const streamRegex = /stream[\r\n]+([\s\S]*?)[\r\n]+endstream/g;
  let text = '';
  let match;
  while ((match = streamRegex.exec(content)) !== null) {
    try {
      const decompressed = zlib.inflateSync(Buffer.from(match[1], 'binary'));
      const str = decompressed.toString('utf-8');
      text += '\n' + str.replace(/\x00/g, '');
    } catch (e) {}
  }
  return text;
}

test.describe('Student Overall Report Feature E2E Suite', () => {

  test('Workflow: Profile Overview -> Overall Report Button -> Dedicated Preview -> Back Button', async ({ authenticatedPage: page, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    // 1. Navigate to Student Profile (Student 46)
    await page.goto(`${baseURL}students/profile/46`);
    await page.waitForLoadState('networkidle');

    // 2. Verify Overall Report button is visible near Edit, ID Card, Back
    const overallReportBtn = page.locator('[data-testid="btn-overall-report"]');
    await expect(overallReportBtn).toBeVisible();
    await expect(overallReportBtn).toContainText('Overall Report');

    // 3. Click Overall Report button
    await overallReportBtn.click();
    await page.waitForLoadState('networkidle');

    // 4. Verify URL has navigated to dedicated overall report preview
    expect(page.url()).toContain('students/overall_report/46');

    // 5. Verify action buttons on report preview
    const downloadPdfBtn = page.locator('[data-testid="btn-download-pdf"]');
    const backBtn = page.locator('[data-testid="btn-back-profile"]');
    await expect(downloadPdfBtn).toBeVisible();
    await expect(backBtn).toBeVisible();

    // 6. Test Back button returns to Student Profile
    await backBtn.click();
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('students/profile/46');

    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

  test('Non-SS Student Report Verification (Student 46 - Aarav Menon)', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/overall_report/46`);
    await page.waitForLoadState('networkidle');

    // 1. School Header & Address
    const schoolName = page.locator('[data-testid="school-name"]');
    const schoolAddress = page.locator('[data-testid="school-address"]');
    const generatedAt = page.locator('[data-testid="report-generated-at"]');

    await expect(schoolName).toBeVisible();
    await expect(schoolAddress).toContainText('Kaloor');
    await expect(schoolAddress).not.toContainText('Kakkanad');
    await expect(generatedAt).toContainText('IST');

    // 2. Student Basic Details
    await expect(page.locator('[data-testid="student-full-name"]')).toContainText('Aarav Menon');
    await expect(page.locator('[data-testid="student-adm-no"]')).toContainText('SCH20260115467');
    await expect(page.locator('[data-testid="student-class-division"]')).toContainText('LKG A');
    await expect(page.locator('[data-testid="student-group-name"]')).toContainText("KG's");

    // 3. Examination Results (First Term Examination 2026)
    const examResultsSection = page.locator('[data-testid="section-exam-results"]');
    await expect(examResultsSection).toBeVisible();
    await expect(examResultsSection).toContainText('First Term Examination 2026');
    await expect(examResultsSection).toContainText('English');
    await expect(examResultsSection).toContainText('70.00');
    await expect(examResultsSection).toContainText('100.00');
    await expect(examResultsSection).toContainText('B+');

    // 4. Non-SS Attendance Model (Month-based attendance table present, SS period table absent)
    const nonSsTable = page.locator('[data-testid="non-ss-monthly-attendance-table"]');
    const ssTable = page.locator('[data-testid="ss-subject-attendance-table"]');
    await expect(nonSsTable).toBeVisible();
    await expect(ssTable).not.toBeVisible();
    await expect(page.locator('[data-testid="non-ss-overall-percentage"]')).toBeVisible();

    // 5. Fees & Finance Summary
    const feeSection = page.locator('[data-testid="section-fees"]');
    await expect(feeSection).toBeVisible();
    await expect(page.locator('[data-testid="fee-total-assigned"]')).toBeVisible();
    await expect(page.locator('[data-testid="fee-total-paid"]')).toBeVisible();
    await expect(page.locator('[data-testid="fee-total-due"]')).toBeVisible();
    await expect(page.locator('[data-testid="fee-total-overdue"]')).toBeVisible();

    // 6. Signatures Area
    const signaturesSection = page.locator('[data-testid="section-signatures"]');
    await expect(signaturesSection).toBeVisible();
    await expect(signaturesSection).toContainText('Class Teacher');
    await expect(signaturesSection).toContainText('Principal / Head');
    await expect(page.locator('[data-testid="principal-name"]')).toContainText('Antony Xavier');
  });

  test('SS Student Report Verification (Student 106 - Arjun Krishnan)', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/overall_report/106`);
    await page.waitForLoadState('networkidle');

    // 1. Student Identity & SS Group
    await expect(page.locator('[data-testid="student-full-name"]')).toContainText('Arjun Krishnan');
    await expect(page.locator('[data-testid="student-group-name"]')).toContainText('SS');

    // 2. SS Attendance Model: Subject-wise Period Attendance & KPI Summary Cards
    const ssSubjectTable = page.locator('[data-testid="ss-subject-attendance-table"]');
    const ssSummaryCards = page.locator('[data-testid="ss-attendance-summary-cards"]');
    const nonSsTable = page.locator('[data-testid="non-ss-monthly-attendance-table"]');

    await expect(ssSubjectTable).toBeVisible();
    await expect(ssSummaryCards).toBeVisible();
    await expect(nonSsTable).not.toBeVisible();
    await expect(ssSummaryCards).toContainText('Total Scheduled Periods');
    await expect(ssSummaryCards).toContainText('Periods Present');

    // 3. Clean Empty Exam State (Student 106 has no published exams)
    const noExamsNotice = page.locator('[data-testid="no-exams-notice"]');
    await expect(noExamsNotice).toBeVisible();
    await expect(noExamsNotice).toContainText('No examination results available for this academic year.');
  });

  test('Download Overall Report PDF via mPDF (Buffer & Filename Verification)', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/overall_report/46`);
    await page.waitForLoadState('networkidle');

    const downloadPdfBtn = page.locator('[data-testid="btn-download-pdf"]');
    await expect(downloadPdfBtn).toBeVisible();

    // Trigger download
    const downloadPromise = page.waitForEvent('download');
    await downloadPdfBtn.click();
    const download = await downloadPromise;

    // Verify dynamic filename format: Student_Overall_Report_<AdmissionNumber>_<AcademicYear>.pdf
    const filename = download.suggestedFilename();
    expect(filename).toMatch(/^Student_Overall_Report_SCH20260115467_2026-2027\.pdf$/i);

    // Read and verify PDF buffer
    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);

    // Assert valid PDF header and substantial content size (>20KB)
    expect(buffer.length).toBeGreaterThan(20000);
    const headerStr = buffer.slice(0, 10).toString('utf-8');
    expect(headerStr.startsWith('%PDF-')).toBe(true);

    // Verify extracted PDF text contents
    const pdfText = extractPdfText(buffer);
    expect(pdfText).toContain('Aarav');
    expect(pdfText).toContain('SCH20260115467');
    expect(pdfText).toContain('2026-2027');
    expect(pdfText).toContain('First Term Examination 2026');
    expect(pdfText).toContain('Kaloor');
    expect(pdfText.toLowerCase()).not.toContain('kakkanad');
    expect(pdfText).toContain('IST');
    expect(pdfText).toContain('Principal / Head');
  });

  test('Empty Data Resilience: Student with no exams, no attendance, no fees (Student 51)', async ({ authenticatedPage: page, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.goto(`${baseURL}students/overall_report/51`);
    await page.waitForLoadState('networkidle');

    // Verify page loads with 200 and renders empty notices without fatal PHP warnings
    await expect(page.locator('[data-testid="no-exams-notice"]')).toBeVisible();
    await expect(page.locator('[data-testid="fee-total-assigned"]')).toContainText('₹0.00');
    await expect(page.locator('[data-testid="fee-total-paid"]')).toContainText('₹0.00');

    // Verify PDF generation for empty student also succeeds
    const downloadPromise = page.waitForEvent('download');
    await page.locator('[data-testid="btn-download-pdf"]').click();
    const download = await downloadPromise;

    expect(download.suggestedFilename()).toMatch(/^Student_Overall_Report_.*\.pdf$/i);
    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    expect(buffer.slice(0, 5).toString('utf-8')).toBe('%PDF-');

    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

});
