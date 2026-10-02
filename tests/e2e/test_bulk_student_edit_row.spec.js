// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');

test.describe('Bulk Student Add — Edit Row Feature Suite', () => {

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

  test('TEST 1 — EDIT ERROR ROW: Fix duplicate admission number, row revalidates to Valid, notes cleared, counts update', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/duplicate_admission_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    // Wait for validation table
    const resultsContainer = page.locator('#validation-results-container');
    await expect(resultsContainer).toBeVisible({ timeout: 10000 });

    // Initial state: Row 1 is Error, Row 2 is Valid
    const summaryTotal = page.locator('#val-summary-total');
    const summaryValid = page.locator('#val-summary-valid');
    const summaryErrors = page.locator('#val-summary-errors');
    const importBtn = page.locator('#btn-execute-import');
    const importLabel = page.locator('#btn-execute-import-label');
    const errorReportBtn = page.locator('#btn-download-error-report');

    await expect(summaryTotal).toHaveText('2 Total Rows');
    await expect(summaryValid).toHaveText('1 Valid');
    await expect(summaryErrors).toHaveText('1 Errors');
    await expect(importLabel).toHaveText('Import 1 Valid Students');
    await expect(errorReportBtn).toBeVisible();

    const row1 = page.locator('#val-row-1');
    await expect(row1.locator('text=Error')).toBeVisible();
    await expect(row1).toContainText('already exists in database');

    // Click Edit on Row 1
    const editBtnRow1 = page.locator('[data-testid="btn-edit-row-1"]');
    await expect(editBtnRow1).toBeVisible();
    await editBtnRow1.click();

    // Verify modal is open and populated
    const modal = page.locator('#bulk-edit-row-modal');
    await expect(modal).toBeVisible();
    await expect(page.locator('#edit-modal-row-indicator')).toContainText('Row #1');
    await expect(page.locator('#edit-admission-number')).toHaveValue('SCH20260115467');
    await expect(page.locator('#edit-first-name')).toHaveValue('Aarav');

    // Change admission number to unique non-existent admission number
    const uniqueAdm = 'EDU2026999' + Math.floor(Math.random() * 10000);
    await page.locator('#edit-admission-number').fill(uniqueAdm);

    // Click Save Changes
    const saveBtn = page.locator('#btn-save-edit-row');
    await saveBtn.click();

    // Modal should close upon successful revalidation
    await expect(modal).toBeHidden();

    // Check Row 1 status badge changed to Valid
    await expect(row1.locator('text=Valid')).toBeVisible();
    // Check error notes cleared to em dash
    await expect(row1.locator('td:nth-child(10)')).toContainText('—');
    // Check admission number updated in table
    await expect(row1.locator('td:nth-child(9)')).toHaveText(uniqueAdm);

    // Summary counts updated
    await expect(summaryTotal).toHaveText('2 Total Rows');
    await expect(summaryValid).toHaveText('2 Valid');
    await expect(summaryErrors).toHaveText('0 Errors');

    // Import button updated
    await expect(importLabel).toHaveText('Import 2 Valid Students');

    // Error report button hidden when 0 errors
    await expect(errorReportBtn).toBeHidden();

    // Verify no unexpected console errors
    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });

  test('TEST 2 — EDIT VALID ROW: Change guardian name, remains Valid, updates UI values', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    await expect(page.locator('#validation-results-container')).toBeVisible();

    const row2 = page.locator('#val-row-2');
    await expect(row2.locator('text=Valid')).toBeVisible();
    await expect(row2).toContainText('Suresh Nair');

    // Click Edit on Row 2
    await page.locator('[data-testid="btn-edit-row-2"]').click();
    const modal = page.locator('#bulk-edit-row-modal');
    await expect(modal).toBeVisible();
    await expect(page.locator('#edit-guardian-name')).toHaveValue('Suresh Nair');

    // Update Guardian Name
    await page.locator('#edit-guardian-name').fill('Ramesh Nair');
    await page.locator('#btn-save-edit-row').click();

    await expect(modal).toBeHidden();

    // Row 2 remains Valid and shows updated Guardian Name
    await expect(row2.locator('text=Valid')).toBeVisible();
    await expect(row2).toContainText('Ramesh Nair');
    await expect(page.locator('#val-summary-valid')).toHaveText('2 Valid');
  });

  test('TEST 3 — INVALID EDIT BLOCKED: Modal stays open, displays error, summary not updated', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    await expect(page.locator('#validation-results-container')).toBeVisible();

    const row1 = page.locator('#val-row-1');
    await expect(row1.locator('text=Valid')).toBeVisible();

    // Edit Row 1 and enter invalid phone
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    const modal = page.locator('#bulk-edit-row-modal');
    await expect(modal).toBeVisible();

    await page.locator('#edit-guardian-phone').fill('123'); // Invalid phone
    await page.locator('#btn-save-edit-row').click();

    // Modal MUST remain open
    await expect(modal).toBeVisible();

    // Error alert inside modal must be visible
    const errorAlert = page.locator('#edit-modal-error-alert');
    await expect(errorAlert).toBeVisible();
    await expect(errorAlert).toContainText('digits');

    // Close / Cancel modal
    await page.locator('#btn-cancel-edit-row').click();
    await expect(modal).toBeHidden();

    // Row 1 in summary table must still retain original valid phone
    await expect(row1).toContainText('+919876543210');
    await expect(row1.locator('text=Valid')).toBeVisible();
  });

  test('TEST 4 — CANCEL DISCARDS CHANGES: Editing field then clicking Cancel does not change table', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    await expect(page.locator('#validation-results-container')).toBeVisible();

    const row1 = page.locator('#val-row-1');
    await expect(row1).toContainText('Aarav Verma');

    // Click Edit
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    const modal = page.locator('#bulk-edit-row-modal');
    await expect(modal).toBeVisible();

    // Change first name
    await page.locator('#edit-first-name').fill('ChangedFirstName');

    // Click Cancel
    await page.locator('#btn-cancel-edit-row').click();
    await expect(modal).toBeHidden();

    // Summary table must still show original Aarav Verma
    await expect(row1).toContainText('Aarav Verma');
    await expect(row1).not.toContainText('ChangedFirstName');
  });

  test('TEST 5 — REOPEN MODAL: Reopening edit modal reflects newly saved values, not original excel', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    await expect(page.locator('#validation-results-container')).toBeVisible();

    // Edit Row 1 address
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    await page.locator('#edit-address').fill('Updated Coastal Avenue 42');
    await page.locator('#btn-save-edit-row').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();

    // Reopen modal for Row 1
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeVisible();
    await expect(page.locator('#edit-address')).toHaveValue('Updated Coastal Avenue 42');

    // Close modal
    await page.locator('#btn-close-edit-modal-x').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();
  });

  test('TEST 6 — MULTI-ROW ISOLATION: Editing Row 1 does not affect Row 2, and editing Row 2 does not affect Row 1', async ({ page }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();

    await expect(page.locator('#validation-results-container')).toBeVisible();

    const row1 = page.locator('#val-row-1');
    const row2 = page.locator('#val-row-2');

    // Edit Row 1 first name to "AaravEdited"
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    await page.locator('#edit-first-name').fill('AaravEdited');
    await page.locator('#btn-save-edit-row').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();

    // Verify Row 1 changed, Row 2 unaffected
    await expect(row1).toContainText('AaravEdited Verma');
    await expect(row2).toContainText('Ananya K Nair');

    // Edit Row 2 first name to "AnanyaEdited"
    await page.locator('[data-testid="btn-edit-row-2"]').click();
    await page.locator('#edit-first-name').fill('AnanyaEdited');
    await page.locator('#btn-save-edit-row').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();

    // Verify Row 2 changed, Row 1 still has its own edit
    await expect(row1).toContainText('AaravEdited Verma');
    await expect(row2).toContainText('AnanyaEdited K Nair');
  });

  test('TEST 7 — NO DATABASE INSERT DURING EDIT: Database student count remains identical before and after edits', async ({ page, request }) => {
    const consoleErrors = [];
    await setupBulkAddPage(page, consoleErrors);

    // Initial student count via API / search or existing total
    const fileInput = page.locator('#import_file_input');
    const uploadBtn = page.locator('#btn-validate-upload');
    const csvPath = path.resolve(__dirname, '../fixtures/bulk/valid_students.csv');

    await fileInput.setInputFiles(csvPath);
    await uploadBtn.click();
    await expect(page.locator('#validation-results-container')).toBeVisible();

    // Perform multiple edits on both rows
    await page.locator('[data-testid="btn-edit-row-1"]').click();
    await page.locator('#edit-first-name').fill('TestNoInsert');
    await page.locator('#btn-save-edit-row').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();

    await page.locator('[data-testid="btn-edit-row-2"]').click();
    await page.locator('#edit-first-name').fill('TestNoInsertTwo');
    await page.locator('#btn-save-edit-row').click();
    await expect(page.locator('#bulk-edit-row-modal')).toBeHidden();

    // Query database directly using CLI command to verify student was NOT inserted
    const { execSync } = require('child_process');
    const result = execSync('php -r "$conn = new mysqli(\'localhost\', \'root\', \'\', \'db_school\'); $res = $conn->query(\\"SELECT COUNT(*) as c FROM tbl_students WHERE first_name IN (\'TestNoInsert\', \'TestNoInsertTwo\')\\"); echo $res->fetch_assoc()[\'c\'];"', { cwd: 'c:\\xampp\\htdocs\\schoolnew' }).toString().trim();
    expect(Number(result)).toBe(0);
  });

});
