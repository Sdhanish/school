// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Authentication & Session Management E2E Journey', () => {

  test('Happy Path: User logs in with valid credentials, reaches dashboard, and sees profile and sidebar', async ({ page, authHelper, baseURL }) => {
    const username = process.env.PLAYWRIGHT_TEST_USERNAME || 'admin@gmail.com';
    const password = process.env.PLAYWRIGHT_TEST_PASSWORD || 'password123';

    await authHelper.goToLogin();
    await authHelper.login(username, password);

    // Verify redirected to dashboard
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('[data-testid="app-header"]')).toBeVisible();
    await expect(page.locator('#app-sidebar')).toBeVisible();

    // Verify current user profile in header
    const userNameElem = page.locator('[data-testid="current-user-name"]');
    await expect(userNameElem).toBeVisible();
  });

  test('Failure State: Incorrect password triggers error notification and stays on login', async ({ page, authHelper }) => {
    const username = process.env.PLAYWRIGHT_TEST_USERNAME || 'admin@gmail.com';
    
    await authHelper.goToLogin();
    await authHelper.login(username, 'WrongPassword999!');

    // Should stay on login and display flash error
    await expect(page).toHaveURL(/auth\/login/);
    const errorAlert = page.locator('[data-testid="login-error"]');
    await expect(errorAlert).toBeVisible();
    await expect(errorAlert).toContainText(/invalid/i);
  });

  test('Failure State: Non-existent username triggers error notification', async ({ page, authHelper }) => {
    await authHelper.goToLogin();
    await authHelper.login('nonexistent.user.test@example.com', 'SomePassword123');

    await expect(page).toHaveURL(/auth\/login/);
    const errorAlert = page.locator('[data-testid="login-error"]');
    await expect(errorAlert).toBeVisible();
    await expect(errorAlert).toContainText(/invalid/i);
  });

  test('Failure State: Client-side required attributes prevent empty submission', async ({ page, authHelper }) => {
    await authHelper.goToLogin();

    // Attempt to submit with empty inputs
    await page.click('[data-testid="login-submit"]');

    // Form should still be visible without navigating
    await expect(page.locator('[data-testid="login-form"]')).toBeVisible();
    await expect(page).toHaveURL(/auth\/login/);
  });

  test('Logout Journey: Authenticated user logs out and protected routes redirect back to login', async ({ page, authHelper, baseURL }) => {
    const username = process.env.PLAYWRIGHT_TEST_USERNAME || 'admin@gmail.com';
    const password = process.env.PLAYWRIGHT_TEST_PASSWORD || 'password123';

    await authHelper.goToLogin();
    await authHelper.login(username, password);
    await expect(page).toHaveURL(/dashboard/);

    // Logout
    await authHelper.logout();
    await expect(page).toHaveURL(/auth\/login/);

    // Attempt to access protected dashboard page
    await page.goto(`${baseURL}dashboard`);
    await expect(page).toHaveURL(/auth\/login/);
  });

});
