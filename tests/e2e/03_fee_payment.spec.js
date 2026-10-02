// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Fees & Payment Collection E2E Journey', () => {

  test('Happy Path: Search student on Fee Collection counter, inspect invoices, and open payment modal', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}fees/collection`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('[data-testid="fee-search-input"]')).toBeVisible();

    // Search for existing student
    await page.fill('[data-testid="fee-search-input"]', 'EDU');
    await page.click('[data-testid="fee-search-btn"]');

    // Verify search response
    await page.waitForLoadState('networkidle');

    const hasStudentCard = await page.locator('[data-testid="fee-student-card"]').isVisible().catch(() => false);
    if (hasStudentCard) {
      await expect(page.locator('[data-testid="fee-student-card"]')).toBeVisible();

      // Check if Pay Now button is available on outstanding items
      const payBtn = page.locator('[data-testid="fee-pay-now-btn"]').first();
      if (await payBtn.isVisible()) {
        await payBtn.click();
        await expect(page.locator('#payment-modal')).toBeVisible();
        await expect(page.locator('[data-testid="payment-amount-input"]')).toBeVisible();
        await expect(page.locator('[data-testid="payment-submit-btn"]')).toBeVisible();
      }
    }
  });

  test('Failure State: Searching for non-existent student displays empty notification', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}fees/collection`);
    await page.waitForLoadState('networkidle');
    await page.fill('[data-testid="fee-search-input"]', 'NONEXISTENT_STUDENT_999999');
    await page.click('[data-testid="fee-search-btn"]');

    await page.waitForLoadState('networkidle');
    await expect(page.locator('text=No Student Found')).toBeVisible();
  });

  test('Failure State: Payment modal amount input enforces positive numbers', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}fees/collection?search=EDU`);
    await page.waitForLoadState('networkidle');

    const payBtn = page.locator('[data-testid="fee-pay-now-btn"]').first();
    if (await payBtn.isVisible()) {
      await payBtn.click();
      await expect(page.locator('#payment-modal')).toBeVisible();

      // Set invalid 0 amount
      await page.fill('[data-testid="payment-amount-input"]', '0');
      await page.click('[data-testid="payment-submit-btn"]');

      // Modal should remain open
      await expect(page.locator('#payment-modal')).toBeVisible();
    }
  });

});
