// @ts-check
require('dotenv').config();
const { test: base, expect } = require('@playwright/test');

const test = base.extend({
  // Helper to perform manual login actions
  authHelper: async ({ page, baseURL }, use) => {
    const targetURL = baseURL || process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1/schoolnew/';
    const helper = {
      async goToLogin() {
        await page.goto(`${targetURL}auth/login`);
        await expect(page.locator('[data-testid="login-form"]')).toBeVisible();
      },
      async login(username, password) {
        await page.goto(`${targetURL}auth/login`);
        await page.fill('[data-testid="login-email"]', username);
        await page.fill('[data-testid="login-password"]', password);
        await page.click('[data-testid="login-submit"]');
      },
      async logout() {
        await page.click('[data-testid="profile-menu-btn"]');
        await expect(page.locator('[data-testid="profile-menu"]')).toBeVisible();
        await page.click('[data-testid="logout-btn"]');
        await page.waitForURL(/auth\/login/);
      }
    };
    await use(helper);
  },

  // Fixture providing an already-authenticated page without hardcoded credentials
  authenticatedPage: async ({ page, baseURL }, use) => {
    const targetURL = baseURL || process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1/schoolnew/';
    const username = process.env.PLAYWRIGHT_TEST_USERNAME || 'admin@gmail.com';
    const password = process.env.PLAYWRIGHT_TEST_PASSWORD || '123456';

    await page.goto(`${targetURL}auth/login`);
    await page.fill('[data-testid="login-email"]', username);
    await page.fill('[data-testid="login-password"]', password);
    await page.click('[data-testid="login-submit"]');
    
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('[data-testid="app-header"]')).toBeVisible();
    await use(page);
  }
});

module.exports = { test, expect };
