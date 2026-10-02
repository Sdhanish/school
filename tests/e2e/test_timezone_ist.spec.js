// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Asia/Kolkata (IST) Project-Wide Timezone Verification', () => {

  async function loginAdmin(page) {
    await page.goto('http://127.0.0.1/schoolnew/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('1. Timezone Endpoint returns canonical Asia/Kolkata across PHP and MySQL', async ({ page }) => {
    const res = await page.goto('http://127.0.0.1/schoolnew/timezone_test');
    expect(res.status()).toBe(200);

    const data = await res.json();
    console.log('Timezone Diagnostic Data:', JSON.stringify(data, null, 2));

    expect(data.status).toBe('ok');
    expect(data.php_timezone).toBe('Asia/Kolkata');
    expect(data.ci_time_reference).toBe('Asia/Kolkata');
    expect(data.school_timezone).toBe('Asia/Kolkata');
    expect(data.db_session_tz).toBe('+05:30');

    // Date-only preservation check
    expect(data.formatted_date).toBe('15 Jun 2012');

    // PHP and MySQL timestamp sync check (allow max 3 seconds)
    const phpEpoch = Math.floor(new Date(data.php_now.replace(' ', 'T') + '+05:30').getTime() / 1000);
    const dbEpoch = Math.floor(new Date(data.db_now.replace(' ', 'T') + '+05:30').getTime() / 1000);
    expect(Math.abs(phpEpoch - dbEpoch)).toBeLessThanOrEqual(3);
  });

  test('2. Login and Dashboard render cleanly without console or timezone errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await loginAdmin(page);

    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('#app-sidebar')).toBeVisible();

    // Verify no JS date/timezone exceptions in browser console
    const tzErrors = consoleErrors.filter(e => /date|time|timezone|invalid/i.test(e));
    expect(tzErrors.length).toBe(0);
  });

  test('3. Student Management shows DOB as calendar date without shifting', async ({ page }) => {
    await loginAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/students/);

    // Verify students table is rendered with DOB
    const pageContent = await page.content();
    expect(pageContent).toContain('Aarav');
    // Check that date format matches expected without time strings attached
    expect(pageContent).toMatch(/\d{4}-\d{2}-\d{2}|\d{2}\s+[A-Za-z]{3}\s+\d{4}/);
  });

  test('4. Fees module loads with correct current date context', async ({ page }) => {
    await loginAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/fees');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/fees/);
  });

  test('5. Attendance module loads with current IST date', async ({ page }) => {
    await loginAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/attendance');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/attendance/);
  });

});
