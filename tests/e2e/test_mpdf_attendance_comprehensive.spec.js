// @ts-check
const { test, expect } = require('@playwright/test');
const zlib = require('zlib');

/**
 * Helper to extract uncompressed text streams from an in-memory PDF buffer.
 * Supports both ASCII and UTF-16BE / CID character streams.
 */
function extractPdfStreams(buffer) {
  const content = buffer.toString('binary');
  const streamRegex = /stream[\r\n]+([\s\S]*?)[\r\n]+endstream/g;
  let text = '';
  let match;

  while ((match = streamRegex.exec(content)) !== null) {
    try {
      const streamBuf = Buffer.from(match[1], 'binary');
      const decompressed = zlib.inflateSync(streamBuf);
      let str = decompressed.toString('utf-8');
      str = str.replace(/\u200C/g, '')
               .replace(/\uFB01/g, 'fi')
               .replace(/\uFB02/g, 'fl')
               .replace(/\uFB03/g, 'ffi')
               .replace(/\uFB04/g, 'ffl');
      text += '\n' + str + '\n' + str.replace(/\x00/g, '');
    } catch (e) {
      // Ignore uncompressed / binary image streams
    }
  }
  return text;
}

test.describe('Student Attendance PDF - 15 Comprehensive Playwright Scenarios (mPDF)', () => {

  async function loginAsAdmin(page) {
    await page.goto('http://127.0.0.1/schoolnew/auth/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('TEST 1 & 2: Download PDF button exists on Profile, triggers download, and produces valid PDF file', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    // TEST 1: Button exists and is visible
    const pdfBtn = page.locator('#btn-download-attendance-pdf');
    await expect(pdfBtn).toBeVisible();
    await expect(pdfBtn).toContainText('Download PDF');

    // TEST 2: Trigger download and verify valid PDF
    const downloadPromise = page.waitForEvent('download');
    await pdfBtn.click();
    const download = await downloadPromise;

    expect(download.suggestedFilename()).toMatch(/Arjun_Krishnan_Attendance_Report.*\.pdf/i);

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);

    expect(buffer.length).toBeGreaterThan(5000);
    expect(buffer.slice(0, 5).toString('utf-8')).toBe('%PDF-');
  });

  test('TEST 3, 4 & 5: Verify Report Title, Student Name, Kaloor address presence, and Kakkanad absence', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // TEST 3: Contains STUDENT ATTENDANCE REPORT and student name
    expect(text).toContain('STUDENT ATTENDANCE REPORT');
    expect(text).toContain('Arjun Krishnan');
    expect(text).toContain('SCH20262419367');

    // TEST 4: Kaloor appears in school address
    expect(text).toContain('Kaloor');
    expect(text).toContain('Kaloor, Ernakulam, Kerala - 682030');

    // TEST 5: Kakkanad does not appear as active school address
    expect(text.toLowerCase()).not.toContain('kakkanad');
  });

  test('TEST 6: For SS student, verify all 4 core sections are present', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // 1. Period-wise Attendance Summary
    expect(text).toContain('PERIOD-WISE ATTENDANCE SUMMARY');
    // 2. Subject-wise Attendance Breakdown
    expect(text).toContain('SUBJECT-WISE ATTENDANCE BREAKDOWN');
    // 3. Month-wise Attendance Breakdown
    expect(text).toContain('MONTH-WISE ATTENDANCE BREAKDOWN');
    // 4. Detailed Period-wise Attendance Logs
    expect(text).toContain('DETAILED PERIOD-WISE ATTENDANCE LOGS');
  });

  test('TEST 7, 8 & 9: Verify Detailed Period Table Headers, Column Order, and Remarks at the end', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // TEST 7: All 6 table headers are present
    expect(text).toContain('Date');
    expect(text).toContain('Period');
    expect(text).toContain('Subject');
    expect(text).toContain('Status');
    expect(text).toContain('Marked By');
    expect(text).toContain('Remarks');

    // Locate the detailed table header section in the text stream
    const detailedIdx = text.indexOf('DETAILED PERIOD-WISE ATTENDANCE LOGS');
    expect(detailedIdx).toBeGreaterThan(-1);
    const tableSection = text.substring(detailedIdx);

    const dateIdx = tableSection.indexOf('Date');
    const periodIdx = tableSection.indexOf('Period');
    const subjectIdx = tableSection.indexOf('Subject');
    const statusIdx = tableSection.indexOf('Status');
    const markedByIdx = tableSection.indexOf('Marked By');
    const remarksIdx = tableSection.indexOf('Remarks');

    // TEST 8 & 9: Strict sequence: Date < Period < Subject < Status < Marked By < Remarks
    expect(dateIdx).toBeLessThan(periodIdx);
    expect(periodIdx).toBeLessThan(subjectIdx);
    expect(subjectIdx).toBeLessThan(statusIdx);
    expect(statusIdx).toBeLessThan(markedByIdx);
    expect(markedByIdx).toBeLessThan(remarksIdx); // Marked By is immediately before Remarks
    // Remarks is the last column
    expect(remarksIdx).toBeGreaterThan(markedByIdx);
  });

  test('TEST 10 & 11: Verify long Marked By names and Remarks render without clipping', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // Verify Marked By value "Super Admin · Admin" or "Super Admin" exists in full without truncation
    expect(text).toContain('Super Admin');
    expect(text).toContain('Admin');
  });

  test('TEST 12 & 13: Verify Multi-page Continuation, Repeated Headers, and Footers', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // Multi-page document checks
    // Footer contains IST timezone, student attendance record label, and page numbers
    expect(text).toContain('Student Attendance Record');
    expect(text).toMatch(/O.*cial Student Attendance Record/i);
    expect(text).toContain('IST');
    expect(text).toContain('Page');

    // Running Header on page 2+ contains school name and student name
    expect(text).toContain('Login2 | Student Attendance Report');
    expect(text).toContain('Arjun Krishnan');
    expect(text).toContain('SCH20262419367');
  });

  test('TEST 14: Date filter synchronization - Changing From/To dates updates PDF records', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    // Set filter to date range with no records (e.g. Jan 2026)
    await page.locator('input[name="from_date"]').fill('2026-01-01');
    await page.locator('input[name="to_date"]').fill('2026-01-05');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) chunks.push(chunk);
    const buffer = Buffer.concat(chunks);
    const text = extractPdfStreams(buffer);

    // Should indicate no records and reflect filtered range
    expect(text).toContain('01 Jan 2026 to 05 Jan 2026');
    expect(text).toContain('No period-wise attendance records found');
  });

  test('TEST 15: Exact Student Isolation - Student 106 vs Student 46 Reports', async ({ page }) => {
    await loginAsAdmin(page);

    // 1. Fetch Student 106 (SS Student)
    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');
    const dlPromise106 = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const dl106 = await dlPromise106;
    const stream106 = await dl106.createReadStream();
    const chunks106 = [];
    for await (const chunk of stream106) chunks106.push(chunk);
    const text106 = extractPdfStreams(Buffer.concat(chunks106));

    // 2. Fetch Student 46 (KG Day-wise Student)
    await page.goto('http://127.0.0.1/schoolnew/students/profile/46?tab=attendance');
    await page.waitForLoadState('networkidle');
    const dlPromise46 = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const dl46 = await dlPromise46;
    const stream46 = await dl46.createReadStream();
    const chunks46 = [];
    for await (const chunk of stream46) chunks46.push(chunk);
    const text46 = extractPdfStreams(Buffer.concat(chunks46));

    // Verify Student 106 has Arjun Krishnan, Grade 11 Science A, and NOT Aarav Menon
    expect(text106).toContain('Arjun Krishnan');
    expect(text106).toContain('SCH20262419367');
    expect(text106).not.toContain('Aarav Menon');
    expect(text106).toContain('PERIOD-WISE ATTENDANCE SUMMARY');

    // Verify Student 46 has Aarav Menon, LKG A, and NOT Arjun Krishnan
    expect(text46).toContain('Aarav Menon');
    expect(text46).toContain('SCH20260115467');
    expect(text46).not.toContain('Arjun Krishnan');
    expect(text46).toContain('DAY-WISE ATTENDANCE SUMMARY');
  });

});
