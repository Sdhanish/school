const { test } = require('@playwright/test');
const path = require('path');

test('Capture Student Document Repository UI Screenshots', async ({ page }) => {
  const FIXTURES_DIR = path.resolve(__dirname, '../fixtures/documents');
  const SCREENSHOT_DIR = path.resolve(__dirname, '../../scratch');

  // Login
  await page.goto('http://127.0.0.1/schoolnew/login');
  await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
  await page.locator('input[name="password"]').first().fill('123456');
  await page.locator('button[type="submit"]').first().click();
  await page.waitForURL(/dashboard|students/);

  // 1. Initial Document Repository UI
  await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
  await page.waitForSelector('#tab-documents');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'student_doc_upload_ui.png'), fullPage: false });

  // 2. Client-side invalid format error display
  const fileInput = page.locator('#tab-documents #document_file');
  await fileInput.setInputFiles(path.join(FIXTURES_DIR, 'test.webp'));
  await page.waitForTimeout(300);
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'student_doc_invalid_error.png'), fullPage: false });

  // 3. Document Repository with uploaded documents
  await page.goto('http://127.0.0.1/schoolnew/students/profile/117#documents');
  await page.waitForSelector('#tab-documents table');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'student_doc_repository_table.png'), fullPage: false });
});
