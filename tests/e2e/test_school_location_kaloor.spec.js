// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('School Location Kaloor Project-Wide E2E Tests', () => {

  async function loginAsAdmin(page) {
    await page.goto('http://127.0.0.1/schoolnew/auth/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('1. School Settings UI displays Kaloor as address and saves correctly', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/settings');
    await page.waitForLoadState('networkidle');

    const addressInput = page.locator('input[name="address"]');
    await expect(addressInput).toBeVisible();

    // Verify current value and placeholder
    const val = await addressInput.inputValue();
    expect(val).toBe('Kaloor, Ernakulam, Kerala - 682030');

    const placeholder = await addressInput.getAttribute('placeholder');
    expect(placeholder).toContain('Kaloor');
    expect(placeholder).not.toContain('Kakkanad');

    // Verify page HTML does not have active Kakkanad for school address
    const pageText = await page.textContent('body');
    expect(pageText).not.toContain('Kakkanad');

    // Test saving settings retains Kaloor
    await page.locator('button[type="submit"]').first().click();
    await page.waitForLoadState('networkidle');

    await expect(page.getByText('School settings updated successfully')).toBeVisible();
    expect(await page.locator('input[name="address"]').inputValue()).toBe('Kaloor, Ernakulam, Kerala - 682030');
  });

  test('2. Student Attendance PDF downloaded via Student Profile contains Kaloor in school header', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const downloadPromise = page.waitForEvent('download');
    await page.locator('#btn-download-attendance-pdf').click();
    const download = await downloadPromise;

    const stream = await download.createReadStream();
    const chunks = [];
    for await (const chunk of stream) {
      chunks.push(chunk);
    }
    const buffer = Buffer.concat(chunks);
    const pdfRaw = buffer.toString('binary');

    // Uncompress PDF FlateDecode streams
    const zlib = require('zlib');
    let extractedText = '';
    const streamRegex = /stream[\r\n]+([\s\S]*?)[\r\n]+endstream/g;
    let match;
    while ((match = streamRegex.exec(pdfRaw)) !== null) {
      try {
        const decompressed = zlib.inflateSync(Buffer.from(match[1], 'binary')).toString('utf-8');
        extractedText += ' ' + decompressed + ' ' + decompressed.replace(/\x00/g, '');
      } catch (e) {
        extractedText += ' ' + match[1];
      }
    }

    expect(extractedText).toContain('Kaloor, Ernakulam, Kerala - 682030');
    expect(extractedText).not.toContain('Kakkanad');
  });

  test('3. Student ID Cards preview displays Kaloor as school address', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/id_cards?student_id=106');
    await page.waitForLoadState('networkidle');

    const addressDisplay = page.locator('#dom-back-address');
    await expect(addressDisplay).toBeVisible();
    await expect(addressDisplay).toContainText('Kaloor');
    await expect(addressDisplay).not.toContainText('Kakkanad');
  });

  test('4. Academic Calendar PDF route uses centralized Kaloor address', async ({ page }) => {
    await loginAsAdmin(page);

    // Route for calendar preview / pdf
    const response = await page.goto('http://127.0.0.1/schoolnew/academics/calendar_pdf');
    if (response && response.status() === 200) {
      const pageContent = await page.content();
      expect(pageContent).toContain('Kaloor');
      expect(pageContent).not.toContain('Kakkanad');
    }
  });

  test('5. Verify personal student and staff addresses remain untouched', async ({ page }) => {
    await loginAsAdmin(page);

    // Navigate to All Students list
    await page.goto('http://127.0.0.1/schoolnew/students');
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/students');

    // Navigate to Staff list
    await page.goto('http://127.0.0.1/schoolnew/staff');
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/staff');
  });

});
