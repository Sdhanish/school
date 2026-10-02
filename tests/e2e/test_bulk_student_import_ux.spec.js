// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');

test.describe('Bulk Student Add — Import Excel/CSV UX Enhancements', () => {

  // Helper to log in and navigate to Bulk Student Add
  async function setupBulkAddPage(page, consoleErrors = []) {
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // Login as Admin
    await page.goto('http://127.0.0.1/schoolnew/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard|students/);

    // Navigate to Bulk Student Add
    await page.goto('http://127.0.0.1/schoolnew/students/bulk_add');
    await page.waitForLoadState('networkidle');

    // Ensure Class is selected
    const classSelect = page.locator('#bulk-class-id');
    const classVal = await classSelect.inputValue();
    if (!classVal) {
      const firstValidOption = await classSelect.locator('option:not([value=""])').first().getAttribute('value');
      if (firstValidOption) {
        await classSelect.selectOption(firstValidOption);
      }
    }
  }

  test('TEST 1 — SUCCESSFUL VALIDATION: Alert appears, single notification, smooth scroll to Validation Summary', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const header = page.locator('header[data-testid="app-header"]');
    const successAlert = page.locator('#validation-success-alert');
    const summaryContainer = page.locator('#validation-summary');
    const previewTable = page.locator('#validation-preview-tbody');

    // Initial state
    await expect(uploadBtn).toBeDisabled();
    await expect(successAlert).toBeHidden();
    await expect(page.locator('#validation-results-container')).toBeHidden();

    // Select valid CSV file
    const validCsvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');
    await fileInput.setInputFiles(validCsvPath);
    await expect(uploadBtn).toBeEnabled();

    // Record initial scroll position
    const initialScrollY = await page.evaluate(() => window.scrollY);
    expect(initialScrollY).toBe(0);

    // Track network request to ensure single request
    let requestCount = 0;
    page.on('request', req => {
      if (req.url().includes('bulk_validate_ajax')) requestCount++;
    });

    // Click Upload & Validate
    const [response] = await Promise.all([
      page.waitForResponse(res => res.url().includes('bulk_validate_ajax')),
      uploadBtn.click()
    ]);

    expect(response.status()).toBe(200);
    expect(requestCount).toBe(1);

    // Wait for validation rendering and scroll
    await expect(successAlert).toBeVisible();
    await expect(page.locator('#validation-success-message')).toHaveText(/File uploaded successfully\. Validation completed\./);
    await expect(summaryContainer).toBeVisible();

    // Verify exactly one success notification banner exists
    const alertCount = await page.locator('#validation-success-alert').count();
    expect(alertCount).toBe(1);

    // Verify summary counts
    await expect(page.locator('#val-summary-total')).toHaveText('2 Total Rows');
    await expect(page.locator('#val-summary-valid')).toHaveText('2 Valid');
    await expect(page.locator('#val-summary-errors')).toHaveText('0 Errors');

    // Allow smooth scroll to settle
    await page.waitForTimeout(1000);

    // Verify page scroll position changed
    const finalScrollY = await page.evaluate(() => window.scrollY);
    expect(finalScrollY).toBeGreaterThan(0);

    // Verify Validation Summary is visible in viewport and NOT hidden behind sticky header
    const headerBottom = await header.evaluate(el => el.getBoundingClientRect().bottom);
    const summaryRect = await summaryContainer.evaluate(el => {
      const r = el.getBoundingClientRect();
      return { top: r.top, bottom: r.bottom, height: r.height };
    });

    console.log(`Header bottom: ${headerBottom}, Summary top: ${summaryRect.top}`);
    // Summary top must be comfortably below the sticky header bottom
    expect(summaryRect.top).toBeGreaterThanOrEqual(headerBottom);

    // Verify validation table rows rendered below summary
    const rowsCount = await previewTable.locator('tr').count();
    expect(rowsCount).toBe(2);

    // Verify import button is enabled and labeled properly
    const importBtn = page.locator('#btn-execute-import');
    await expect(importBtn).toBeEnabled();
    await expect(page.locator('#btn-execute-import-label')).toHaveText(/Import 2 Valid Students/);

    // Dismiss alert test
    await page.locator('#validation-success-alert button').click();
    await expect(successAlert).toBeHidden();
    await expect(summaryContainer).toBeVisible();

    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 2 — INVALID ROWS: Processed message appears and user can review errors in summary', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const header = page.locator('header[data-testid="app-header"]');
    const successAlert = page.locator('#validation-success-alert');
    const summaryContainer = page.locator('#validation-summary');

    // Select mixed valid/invalid CSV file
    const mixedCsvPath = path.resolve(__dirname, '../fixtures/bulk/mixed_students.csv');
    await fileInput.setInputFiles(mixedCsvPath);

    await Promise.all([
      page.waitForResponse(res => res.url().includes('bulk_validate_ajax')),
      uploadBtn.click()
    ]);

    await expect(successAlert).toBeVisible();
    await expect(summaryContainer).toBeVisible();

    // Verify summary counts: 2 total, 1 valid, 1 error
    await expect(page.locator('#val-summary-total')).toHaveText('2 Total Rows');
    await expect(page.locator('#val-summary-valid')).toHaveText('1 Valid');
    await expect(page.locator('#val-summary-errors')).toHaveText('1 Errors');

    // Download error report button should be visible
    await expect(page.locator('#btn-download-error-report')).toBeVisible();

    // Settle scroll
    await page.waitForTimeout(1000);

    // Check visibility below header
    const headerBottom = await header.evaluate(el => el.getBoundingClientRect().bottom);
    const summaryRect = await summaryContainer.evaluate(el => el.getBoundingClientRect());
    expect(summaryRect.top).toBeGreaterThanOrEqual(headerBottom);

    // Verify Error row styling in preview table
    const tableBody = page.locator('#validation-preview-tbody');
    const errorBadge = tableBody.locator('span:has-text("Error")');
    await expect(errorBadge).toBeVisible();

    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 3 — COMPLETE UPLOAD FAILURE: No file selected does not trigger false success or scroll', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const uploadBtn = page.locator('#btn-validate-upload');
    const successAlert = page.locator('#validation-success-alert');

    // Check button disabled
    await expect(uploadBtn).toBeDisabled();

    // Listen for alert dialog
    let alertMsg = '';
    page.on('dialog', async dialog => {
      alertMsg = dialog.message();
      await dialog.accept();
    });

    // Force click if possible or trigger function directly
    await page.evaluate(() => {
      // @ts-ignore
      uploadAndValidateFile();
    });

    expect(alertMsg).toContain('Please choose a file to upload.');
    await expect(successAlert).toBeHidden();
    await expect(page.locator('#validation-results-container')).toBeHidden();

    const scrollY = await page.evaluate(() => window.scrollY);
    expect(scrollY).toBe(0);

    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 4 — INVALID FILE TYPE: Unsupported file triggers error and no false success or scroll', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const successAlert = page.locator('#validation-success-alert');

    const invalidFilePath = path.resolve(__dirname, '../fixtures/bulk/invalid_file.txt');
    await fileInput.setInputFiles(invalidFilePath);

    const dialogPromise = page.waitForEvent('dialog');
    await uploadBtn.click();
    const dialog = await dialogPromise;

    expect(dialog.message()).toMatch(/empty or does not contain valid student rows|Unsupported file type/);
    await dialog.accept();

    await expect(successAlert).toBeHidden();
    await expect(page.locator('#validation-results-container')).toBeHidden();

    const scrollY = await page.evaluate(() => window.scrollY);
    expect(scrollY).toBe(0);

    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 5 — REPEATED UPLOAD: Only one request per click, fresh summary and scroll', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const successAlert = page.locator('#validation-success-alert');

    // First upload: valid file
    const validCsvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');
    await fileInput.setInputFiles(validCsvPath);

    await Promise.all([
      page.waitForResponse(res => res.url().includes('bulk_validate_ajax')),
      uploadBtn.click()
    ]);

    await expect(successAlert).toBeVisible();
    await expect(page.locator('#val-summary-total')).toHaveText('2 Total Rows');

    // Scroll back to top
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(300);

    // Second upload: large file (6 rows)
    const largeCsvPath = path.resolve(__dirname, '../fixtures/bulk/large_students.csv');
    await fileInput.setInputFiles(largeCsvPath);

    let upload2Requests = 0;
    const reqListener = req => {
      if (req.url().includes('bulk_validate_ajax')) upload2Requests++;
    };
    page.on('request', reqListener);

    await Promise.all([
      page.waitForResponse(res => res.url().includes('bulk_validate_ajax')),
      uploadBtn.click()
    ]);

    expect(upload2Requests).toBe(1);

    await expect(successAlert).toBeVisible();
    await expect(page.locator('#val-summary-total')).toHaveText('6 Total Rows');
    await expect(page.locator('#val-summary-valid')).toHaveText('6 Valid');

    await page.waitForTimeout(1000);
    const scrollYAfter2nd = await page.evaluate(() => window.scrollY);
    expect(scrollYAfter2nd).toBeGreaterThan(0);

    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 6 — RESPONSIVE VIEWPORTS: Desktop, Tablet, Mobile scroll positioning and visibility', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const viewports = [
      { name: 'Desktop', width: 1280, height: 800 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Mobile', width: 375, height: 667 }
    ];

    for (const vp of viewports) {
      console.log(`Testing viewport: ${vp.name} (${vp.width}x${vp.height})`);
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.goto('http://127.0.0.1/schoolnew/students/bulk_add');
      await page.waitForLoadState('networkidle');

      const fileInput = page.locator('#import_file_input');
      const uploadBtn = page.locator('#btn-validate-upload');
      const header = page.locator('header[data-testid="app-header"]');
      const summaryContainer = page.locator('#validation-summary');

      const validCsvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');
      await fileInput.setInputFiles(validCsvPath);

      await Promise.all([
        page.waitForResponse(res => res.url().includes('bulk_validate_ajax')),
        uploadBtn.click()
      ]);

      await expect(summaryContainer).toBeVisible();
      await page.waitForTimeout(1000);

      // Check header bottom vs summary top
      const headerBottom = await header.evaluate(el => el.getBoundingClientRect().bottom);
      const summaryRect = await summaryContainer.evaluate(el => el.getBoundingClientRect());
      expect(summaryRect.top).toBeGreaterThanOrEqual(headerBottom);

      // Verify validation summary and success alert do not cause horizontal overflow
      const elementsBounds = await page.evaluate(() => {
        const summary = document.getElementById('validation-summary');
        const alert = document.getElementById('validation-success-alert');
        const container = document.getElementById('validation-results-container');
        return {
          windowWidth: window.innerWidth,
          summaryRight: summary ? summary.getBoundingClientRect().right : 0,
          alertRight: alert ? alert.getBoundingClientRect().right : 0,
          containerRight: container ? container.getBoundingClientRect().right : 0,
        };
      });

      expect(elementsBounds.summaryRight).toBeLessThanOrEqual(elementsBounds.windowWidth + 2);
      expect(elementsBounds.alertRight).toBeLessThanOrEqual(elementsBounds.windowWidth + 2);
      expect(elementsBounds.containerRight).toBeLessThanOrEqual(elementsBounds.windowWidth + 2);
    }

    expect(consoleErrors).toHaveLength(0);
  });

});
