// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Student Attendance PDF Download E2E Tests', () => {

  async function loginAsAdmin(page) {
    await page.goto('http://127.0.0.1/schoolnew/auth/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('1. Verify [ Download PDF ] button UI and attributes on Student Profile', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', err => pageErrors.push(err.message));

    await loginAsAdmin(page);

    // Navigate to Student 106 Profile Attendance tab
    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const pdfBtn = page.locator('#btn-download-attendance-pdf');
    await expect(pdfBtn).toBeVisible();
    await expect(pdfBtn).toContainText('Download PDF');
    await expect(pdfBtn.locator('.material-symbols-outlined, .material-symbols-rounded, .material-icons, i')).toContainText('picture_as_pdf');

    // Verify href points to attendance_pdf/106
    const href = await pdfBtn.getAttribute('href');
    expect(href).toContain('/students/attendance_pdf/106');

    // Verify adjacent buttons exist in attendance tab
    await expect(page.locator('#tab-attendance a:has-text("View Interactive Calendar")')).toBeVisible();
    await expect(page.locator('#tab-attendance a:has-text("Detailed View")')).toBeVisible();

    expect(pageErrors.length).toBe(0);
  });

  test('2. Download PDF for Period-wise Student (Student 106) and verify response headers and content', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    // Trigger download
    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    // Verify downloaded filename
    const suggestedFilename = download.suggestedFilename();
    expect(suggestedFilename).toMatch(/Arjun_Krishnan_Attendance_Report.*\.pdf/i);

    // Save and verify file buffer starts with %PDF-
    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    expect(buffer.length).toBeGreaterThan(3000);
    const headerStr = buffer.slice(0, 10).toString('utf-8');
    expect(headerStr.startsWith('%PDF-')).toBe(true);
  });

  test('3. Date Range Filter Synchronization: dynamic query parameters sent on download', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    // Fill new from_date and to_date in the visible form
    await page.locator('input[name="from_date"]').fill('2026-09-01');
    await page.locator('input[name="to_date"]').fill('2026-09-12');

    // Trigger download - the JS handler dynamically appends the current input values
    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
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

  test('4. Download PDF for Day-wise Student (Student 46 - Kindergarten)', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/46?tab=attendance');
    await page.waitForLoadState('networkidle');

    const pdfBtn = page.locator('#btn-download-attendance-pdf');
    await expect(pdfBtn).toBeVisible();
    const href = await pdfBtn.getAttribute('href');
    expect(href).toContain('/students/attendance_pdf/46');

    // Trigger download
    const downloadPromise = page.waitForEvent('download');
    await pdfBtn.click();
    const download = await downloadPromise;

    // Verify downloaded filename
    const suggestedFilename = download.suggestedFilename();
    expect(suggestedFilename).toMatch(/Aarav_Menon_Attendance_Report.*\.pdf/i);

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    expect(buffer.length).toBeGreaterThan(3000);
    expect(buffer.slice(0, 5).toString('utf-8')).toBe('%PDF-');
  });

  test('5. Invalid Date Range Validation (From Date > To Date)', async ({ page }) => {
    await loginAsAdmin(page);

    // Attempt direct navigation with invalid date range
    await page.goto('http://127.0.0.1/schoolnew/students/attendance_pdf/106?from_date=2026-09-30&to_date=2026-09-01');
    await page.waitForLoadState('networkidle');

    // Should redirect back to profile page with error message
    expect(page.url()).toContain('/students/profile/106');
    const pageText = await page.textContent('body');
    expect(pageText).toContain('Invalid date range: From Date must be earlier than or equal to To Date.');
  });

});
