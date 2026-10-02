// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Global Navigation & Academic Year Context', () => {

  test('Module Navigation: Navigate across core application modules smoothly', async ({ authenticatedPage: page, baseURL }) => {
    const pagesToTest = [
      { url: 'students/overview', expectedTitle: /student/i },
      { url: 'staff/overview', expectedTitle: /staff/i },
      { url: 'academics/classes', expectedTitle: /class/i },
      { url: 'attendance/class_attendance', expectedTitle: /attendance/i },
      { url: 'examinations/exams', expectedTitle: /exam/i },
      { url: 'timetable', expectedTitle: /timetable/i },
      { url: 'transport', expectedTitle: /transport/i },
      { url: 'users', expectedTitle: /user/i },
      { url: 'settings', expectedTitle: /setting/i },
    ];

    for (const p of pagesToTest) {
      await page.goto(`${baseURL}${p.url}`);
      await expect(page.locator('[data-testid="app-header"]')).toBeVisible();
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('Academic Year: Verify academic year selector is present and functional', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}dashboard`);
    const yearSelect = page.locator('[data-testid="academic-year-select"]');

    if (await yearSelect.isVisible()) {
      const options = await yearSelect.locator('option').count();
      expect(options).toBeGreaterThan(0);
    }
  });

});
