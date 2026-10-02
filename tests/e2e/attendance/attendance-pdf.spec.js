// @ts-check
const { test, expect } = require('../fixtures/auth.fixture');

test.describe('Student Attendance mPDF Generation & Download Regression', () => {

  test('Happy Path: Download attendance PDF, verify %PDF- header and valid buffer size', async ({ authenticatedPage: page, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    // Navigate to student 106 profile attendance tab
    await page.goto(`${baseURL}students/profile/106?tab=attendance`);
    await page.waitForLoadState('networkidle');

    const pdfBtn = page.locator('[data-testid="btn-download-attendance-pdf"]');
    await expect(pdfBtn).toBeVisible();

    // Trigger download
    const downloadPromise = page.waitForEvent('download');
    await pdfBtn.click();
    const download = await downloadPromise;

    // Verify downloaded filename format
    const suggestedFilename = download.suggestedFilename();
    expect(suggestedFilename).toMatch(/Attendance_Report.*\.pdf$/i);

    // Verify PDF content buffer
    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    expect(buffer.length).toBeGreaterThan(3000);
    const headerStr = buffer.slice(0, 10).toString('utf-8');
    expect(headerStr.startsWith('%PDF-')).toBe(true);

    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

  test('Date Range Synchronization: Filter inputs update download URL parameters', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/profile/106?tab=attendance`);
    await page.waitForLoadState('networkidle');

    // Fill date range
    await page.fill('[data-testid="attendance-from-date"]', '2026-09-01');
    await page.fill('[data-testid="attendance-to-date"]', '2026-09-12');

    // Trigger download
    const downloadPromise = page.waitForEvent('download');
    await page.locator('[data-testid="btn-download-attendance-pdf"]').click();
    const download = await downloadPromise;

    expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    expect(buffer.length).toBeGreaterThan(1000);
    expect(buffer.slice(0, 5).toString('utf-8')).toBe('%PDF-');
  });

  test('Failure State: Invalid date range (From > To) redirects with validation error', async ({ authenticatedPage: page, baseURL }) => {
    // Navigate with inverted dates
    await page.goto(`${baseURL}students/attendance_pdf/106?from_date=2026-09-30&to_date=2026-09-01`);
    await page.waitForLoadState('networkidle');

    // Should redirect back to profile page with error message
    expect(page.url()).toContain('/students/profile/106');
    const pageText = await page.textContent('body');
    expect(pageText).toContain('Invalid date range: From Date must be earlier than or equal to To Date.');
  });

});
