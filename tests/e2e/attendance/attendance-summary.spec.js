// @ts-check
const { test, expect } = require('../fixtures/auth.fixture');

test.describe('Core School Workflow: Student Profile & Attendance Summary', () => {

  test('Core Journey: Login -> All Students -> Open Student Profile -> Attendance Summary', async ({ authenticatedPage: page, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    // 1. Navigate to All Students Directory
    await page.goto(`${baseURL}students/all`);
    await page.waitForLoadState('networkidle');

    // Verify DataTable loaded
    await expect(page.locator('#all-students-datatable')).toBeVisible();

    // 2. Open first student profile via data-testid action button
    const viewProfileBtn = page.locator('[data-testid="btn-view-profile"]').first();
    await expect(viewProfileBtn).toBeVisible();
    await viewProfileBtn.click();

    // 3. Verify on Student Profile page
    await page.waitForURL(/students\/profile\/\d+/);
    await page.waitForLoadState('networkidle');

    // 4. Switch to Attendance tab
    const attendanceTab = page.locator('a[href*="tab=attendance"], #tab-btn-attendance').first();
    if (await attendanceTab.isVisible()) {
      await attendanceTab.click();
    } else {
      const url = page.url();
      await page.goto(url.includes('?') ? `${url}&tab=attendance` : `${url}?tab=attendance`);
    }
    await page.waitForLoadState('networkidle');

    // 5. Verify Attendance Filter Form and Download PDF button
    await expect(page.locator('[data-testid="btn-download-attendance-pdf"]')).toBeVisible();
    await expect(page.locator('[data-testid="attendance-filter-form"]')).toBeVisible();

    // Verify academic year filter element
    await expect(page.locator('[data-testid="attendance-filter-year"]')).toBeVisible();

    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

});
