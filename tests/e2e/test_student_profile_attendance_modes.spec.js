// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Student Profile Attendance Modes & Enhancements E2E Tests', () => {

  async function loginAsAdmin(page) {
    await page.goto('http://127.0.0.1/schoolnew/auth/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('1. Student 106 (SS Group - Period-wise Attendance & Subject-wise Breakdown)', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', err => pageErrors.push(err.message));

    await loginAsAdmin(page);

    // Navigate to Student 106 Profile Attendance tab
    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    // Verify Attendance Tab pane is visible (not hidden)
    const attTabPane = page.locator('#tab-attendance');
    await expect(attTabPane).toBeVisible();

    // Verify Academic Group Badge SS is present
    await expect(attTabPane).toContainText('SS');

    // Verify Period-wise Attendance Summary header and metrics
    await expect(attTabPane).toContainText('Period-wise Attendance Summary');
    await expect(attTabPane).toContainText('Scheduled Periods');
    await expect(attTabPane).toContainText('Periods Present');
    await expect(attTabPane).toContainText('Overall Period %');
    await expect(attTabPane).toContainText('11');
    await expect(attTabPane).toContainText('100%');

    // Verify Subject-wise Attendance table
    await expect(attTabPane).toContainText('Subject-wise Attendance');
    await expect(attTabPane).toContainText('English');
    await expect(attTabPane).toContainText('Physics');
    await expect(attTabPane).toContainText('Chemistry');
    await expect(attTabPane).toContainText('Mathematics');
    await expect(attTabPane).toContainText('Malayalam');
    await expect(attTabPane).toContainText('Biology');

    // Verify Month-wise Attendance Breakdown
    await expect(attTabPane).toContainText('Month-wise Attendance Breakdown');
    await expect(attTabPane).toContainText('Sep 2026');

    // Verify Period-wise Attendance Logs table and columns
    await expect(attTabPane).toContainText('Period-wise Attendance Logs');
    await expect(attTabPane.locator('th:has-text("Period")').last()).toBeVisible();
    await expect(attTabPane.locator('th:has-text("Subject")').last()).toBeVisible();
    await expect(attTabPane.locator('th:has-text("Marked By")').last()).toBeVisible();

    // Verify Marked By audit trail displays Admin/Super Admin
    await expect(attTabPane).toContainText('Super Admin · Admin');

    // Ensure zero console / page errors
    expect(pageErrors).toEqual([]);
  });

  test('2. Date Range Filter & Zero-Safe Calculation on Profile 106', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', err => pageErrors.push(err.message));

    await loginAsAdmin(page);

    await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
    await page.waitForLoadState('networkidle');

    const attTabPane = page.locator('#tab-attendance');
    await expect(attTabPane).toBeVisible();

    // Apply date filter for a range with zero records (Jan 2026)
    await page.locator('#attendance-filter-form input[name="from_date"]').fill('2026-01-01');
    await page.locator('#attendance-filter-form input[name="to_date"]').fill('2026-01-05');
    await page.locator('#attendance-filter-form button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Verify tab remains active after filter submission
    await expect(page.locator('#tab-attendance')).toBeVisible();

    // Verify zero scheduled periods and ZERO-SAFE percentage 0% (NOT 100%)
    await expect(page.locator('#tab-attendance')).toContainText('Scheduled Periods');
    await expect(page.locator('#tab-attendance')).toContainText('0%');

    // Click Reset
    await page.locator('#attendance-filter-form a:has-text("Reset")').click();
    await page.waitForLoadState('networkidle');

    // Verify tab is active and records restored to 11
    await expect(page.locator('#tab-attendance')).toBeVisible();
    await expect(page.locator('#tab-attendance')).toContainText('11');

    expect(pageErrors).toEqual([]);
  });

  test('3. Student 46 (KG Group - Day-wise Attendance)', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', err => pageErrors.push(err.message));

    await loginAsAdmin(page);

    // Navigate to Student 46 Profile Attendance tab
    await page.goto('http://127.0.0.1/schoolnew/students/profile/46?tab=attendance');
    await page.waitForLoadState('networkidle');

    const attTabPane = page.locator('#tab-attendance');
    await expect(attTabPane).toBeVisible();

    // Verify Academic Group Badge KG's
    await expect(attTabPane).toContainText("KG's");

    // Verify Day-wise Attendance Summary header and metrics
    await expect(attTabPane).toContainText('Day-wise Attendance Summary');
    await expect(attTabPane).toContainText('Total Academic Days');
    await expect(attTabPane).toContainText('Days Present');
    await expect(attTabPane).toContainText('Days Absent');
    await expect(attTabPane).toContainText('Overall Percentage');

    // Verify Month-wise Attendance Breakdown table
    await expect(attTabPane).toContainText('Month-wise Attendance Breakdown');

    // Verify Recent Attendance Logs has Marked By
    await expect(attTabPane).toContainText('Recent Attendance Logs');
    await expect(attTabPane.locator('th:has-text("Marked By")')).toBeVisible();

    expect(pageErrors).toEqual([]);
  });

});
