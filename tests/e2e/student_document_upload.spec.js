const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

test.describe('Student Document Repository Upload UX & Validation', () => {
  const FIXTURES_DIR = path.resolve(__dirname, '../fixtures/documents');

  test.beforeEach(async ({ page }) => {
    // Login as Admin
    await page.goto('http://127.0.0.1/schoolnew/login');
    const emailInput = page.locator('input[name="identity"], input[name="email"], input[type="text"]').first();
    const passInput = page.locator('input[name="password"]').first();
    await emailInput.fill('admin@gmail.com');
    await passInput.fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard|students/);
  });

  test('1. UI format message and accept attribute verification', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/profile/117');
    
    // Switch to documents tab
    await page.click('button.tab-btn[data-tab="documents"]');
    await expect(page.locator('#tab-documents')).toBeVisible();

    // Check file input
    const fileInput = page.locator('#tab-documents #document_file');
    await expect(fileInput).toBeVisible();
    await expect(fileInput).toHaveAttribute('accept', '.pdf,.png,.jpg,.jpeg,.doc,.docx');

    // Check allowed-format message
    const hint = page.locator('#tab-documents #document_file_hint');
    await expect(hint).toBeVisible();
    await expect(hint).toHaveText('Allowed formats: PDF, PNG, JPG/JPEG, DOC/DOCX');

    // Verify error container is hidden by default
    const errorEl = page.locator('#tab-documents #document_file_error');
    await expect(errorEl).toBeHidden();
  });

  test('2. Client-side invalid file rejection (gif, webp, xlsx, txt, zip, rar)', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
    await expect(page.locator('#tab-documents')).toBeVisible();

    const fileInput = page.locator('#tab-documents #document_file');
    const errorEl = page.locator('#tab-documents #document_file_error');

    const invalidFiles = ['test.gif', 'test.webp', 'test.xlsx', 'test.txt', 'test.zip', 'test.rar'];

    for (const fileName of invalidFiles) {
      const filePath = path.join(FIXTURES_DIR, fileName);
      await fileInput.setInputFiles(filePath);

      // Verify error message is shown immediately
      await expect(errorEl).toBeVisible();
      await expect(errorEl).toContainText('Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');

      // Verify file input was cleared/reset
      const val = await fileInput.inputValue();
      expect(val).toBe('');
    }
  });

  test('3. Client-side valid file selection (pdf, png, jpg, jpeg, doc, docx)', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
    await expect(page.locator('#tab-documents')).toBeVisible();

    const fileInput = page.locator('#tab-documents #document_file');
    const errorEl = page.locator('#tab-documents #document_file_error');

    const validFiles = ['test.pdf', 'test.png', 'test.jpg', 'test.jpeg', 'test.doc', 'test.docx'];

    for (const fileName of validFiles) {
      const filePath = path.join(FIXTURES_DIR, fileName);
      await fileInput.setInputFiles(filePath);

      // Verify no error
      await expect(errorEl).toBeHidden();
      const val = await fileInput.inputValue();
      expect(val).toContain(fileName);
    }
  });

  test('4. End-to-end valid document upload and repository verification', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
    await expect(page.locator('#tab-documents')).toBeVisible();

    const docTypeSelect = page.locator('#tab-documents select[name="document_type"]');
    const docNameInput = page.locator('#tab-documents input[name="document_name"]');
    const fileInput = page.locator('#tab-documents #document_file');
    const submitBtn = page.locator('#tab-documents #btn-upload-document');

    const uniqueTitle = 'E2E Test Certificate ' + Date.now();
    await docTypeSelect.selectOption('Birth Certificate');
    await docNameInput.fill(uniqueTitle);
    await fileInput.setInputFiles(path.join(FIXTURES_DIR, 'test.pdf'));

    await submitBtn.click();
    await page.waitForLoadState('networkidle');

    // Check we are back on profile page and tab documents is active
    await expect(page.locator('#tab-documents')).toBeVisible();

    // Verify success flash message
    await expect(page.locator('#tab-documents')).toContainText('Document uploaded successfully.');

    // Verify uploaded document appears in table
    const table = page.locator('#tab-documents table');
    await expect(table).toContainText(uniqueTitle);
    await expect(table).toContainText('Birth Certificate');
  });

  test('5. Existing historical documents preserved', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
    await expect(page.locator('#tab-documents')).toBeVisible();

    const table = page.locator('#tab-documents table');
    // Pre-existing document 'PassBook' must still exist
    await expect(table).toContainText('PassBook');
  });

  test('6. Profile Image rules untouched', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/add');
    // Verify Profile Image helper text is exactly as originally configured
    const photoHelp = page.locator('text=3:4 Portrait · JPG, JPEG, PNG, WEBP · Max 3 MB');
    await expect(photoHelp).toBeVisible();
  });
});
