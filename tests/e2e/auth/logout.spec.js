// @ts-check
const { test, expect } = require('../fixtures/auth.fixture');

test.describe('Logout & Session Invalidation E2E Journey', () => {

  test('Logout Journey: Authenticated user logs out and protected routes redirect back to login', async ({ page, authHelper, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    const username = process.env.PLAYWRIGHT_TEST_USERNAME || 'admin@gmail.com';
    const password = process.env.PLAYWRIGHT_TEST_PASSWORD || '123456';

    // Log in
    await authHelper.goToLogin();
    await authHelper.login(username, password);
    await expect(page).toHaveURL(/dashboard/);

    // Perform logout
    await authHelper.logout();
    await expect(page).toHaveURL(/auth\/login/);

    // Attempt to access protected dashboard page after logout
    const targetURL = baseURL || process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1/schoolnew/';
    await page.goto(`${targetURL}dashboard`);
    await expect(page).toHaveURL(/auth\/login/);

    // Attempt to access protected students directory after logout
    await page.goto(`${targetURL}students`);
    await expect(page).toHaveURL(/auth\/login/);

    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

});
