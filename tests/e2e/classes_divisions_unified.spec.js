// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Unified Class & Division Management E2E Flow', () => {

  test('Comprehensive Journey: Add, Edit, Rename, Duplicate Validation, and Dependency Protection', async ({ authenticatedPage: page, baseURL }) => {
    const uniqueSuffix = Date.now().toString().slice(-4);
    const testClassName = `Grade Test ${uniqueSuffix}`;
    const testClassCode = `CLS-T${uniqueSuffix}`;

    // 1. Open Classes & Divisions page
    await page.goto(`${baseURL}academics/classes`);
    await expect(page).toHaveURL(/academics\/classes/);
    await expect(page.locator('[data-testid="modal-title"]')).not.toBeVisible();
    await expect(page.locator('[data-testid="classes-table"]')).toBeVisible();

    // 2. Click Add Class & Divisions button
    await page.click('[data-testid="btn-add-class"]');
    await expect(page.locator('#modal-class')).toBeVisible();
    await expect(page.locator('[data-testid="modal-title"]')).toContainText(/Add Class/i);

    // 3. Select Academic Year (2026-2027 active session: value 1)
    await page.selectOption('[data-testid="select-academic-year"]', { value: '1' });

    // 4. Select Academic Group
    await page.selectOption('[data-testid="select-academic-group"]', { index: 1 });

    // 5. Enter Class Name
    await page.fill('[data-testid="input-class-name"]', testClassName);

    // 6. Enter Class Code
    await page.fill('[data-testid="input-class-code"]', testClassCode);

    // 7. Enter Capacity
    await page.fill('[data-testid="input-class-capacity"]', '32');

    // 8. Add Divisions: Division A already present by default. Add Division B and Division C
    const divisionRows = page.locator('[data-testid="division-row"]');
    await expect(divisionRows).toHaveCount(1);
    await expect(divisionRows.nth(0).locator('[data-testid="input-division-name"]')).toHaveValue('A');

    // Add Division B
    await page.click('[data-testid="btn-add-division"]');
    await expect(divisionRows).toHaveCount(2);
    await expect(divisionRows.nth(1).locator('[data-testid="input-division-name"]')).toHaveValue('B');

    // Add Division C
    await page.click('[data-testid="btn-add-division"]');
    await expect(divisionRows).toHaveCount(3);
    await expect(divisionRows.nth(2).locator('[data-testid="input-division-name"]')).toHaveValue('C');

    // 11. Submit Form
    await page.click('[data-testid="btn-save-class"]');

    // 12 & 13. Verify Class & all three Divisions created
    await expect(page.locator('[data-testid="flash-success"]')).toBeVisible();
    const classRow = page.locator(`tr:has-text("${testClassName}")`);
    await expect(classRow).toBeVisible();
    await expect(classRow).toContainText('Division A');
    await expect(classRow).toContainText('Division B');
    await expect(classRow).toContainText('Division C');

    // 14. Edit the newly created class
    const manageBtn = classRow.locator('[data-testid^="btn-manage-class-"]');
    await manageBtn.click();
    await expect(page.locator('#modal-class')).toBeVisible();
    await expect(page.locator('[data-testid="modal-title"]')).toContainText(/Edit Class/i);
    await expect(page.locator('[data-testid="input-class-name"]')).toHaveValue(testClassName);

    // Verify existing 3 divisions are prefilled
    await expect(divisionRows).toHaveCount(3);
    await expect(divisionRows.nth(0).locator('[data-testid="input-division-name"]')).toHaveValue('A');
    await expect(divisionRows.nth(1).locator('[data-testid="input-division-name"]')).toHaveValue('B');
    await expect(divisionRows.nth(2).locator('[data-testid="input-division-name"]')).toHaveValue('C');

    // 15. Add Division D
    await page.click('[data-testid="btn-add-division"]');
    await expect(divisionRows).toHaveCount(4);
    await expect(divisionRows.nth(3).locator('[data-testid="input-division-name"]')).toHaveValue('D');

    // 16. Rename Division C -> C1
    await divisionRows.nth(2).locator('[data-testid="input-division-name"]').fill('C1');

    // 17 & 18. Save and verify changes
    await page.click('[data-testid="btn-save-class"]');
    await expect(page.locator('[data-testid="flash-success"]')).toBeVisible();

    const updatedRow = page.locator(`tr:has-text("${testClassName}")`);
    await expect(updatedRow).toContainText('Division A');
    await expect(updatedRow).toContainText('Division B');
    await expect(updatedRow).toContainText('Division C1');
    await expect(updatedRow).toContainText('Division D');

    // 19 & 20. Attempt Duplicate Division (e.g. adding another 'A' or 'a')
    await updatedRow.locator('[data-testid^="btn-manage-class-"]').click();
    await expect(page.locator('#modal-class')).toBeVisible();

    // Add another division and name it 'a' (case-insensitive duplicate of 'A')
    await page.click('[data-testid="btn-add-division"]');
    const allRows = page.locator('[data-testid="division-row"]');
    const lastRow = allRows.last();
    await lastRow.locator('[data-testid="input-division-name"]').fill('a');

    // Attempt to submit
    await page.click('[data-testid="btn-save-class"]');

    // Verify frontend validation catches duplicate division
    const errorAlert = page.locator('[data-testid="division-validation-error"]');
    await expect(errorAlert).toBeVisible();
    await expect(errorAlert).toContainText(/duplicate/i);

    // Cancel modal
    await page.click('[data-testid="btn-cancel-modal"]');
    await expect(page.locator('#modal-class')).not.toBeVisible();

    // 21 & 22. Attempt Unsafe Deletion: Class 1 (LKG) has enrolled students and should NOT be deletable
    const lkgRow = page.locator('tr:has-text("LKG")').first();
    if (await lkgRow.isVisible()) {
      // Set dialog listener to accept confirmation prompt
      page.once('dialog', async dialog => {
        await dialog.accept();
      });
      const lkgDeleteBtn = lkgRow.locator('[data-testid^="btn-delete-class-"]');
      await lkgDeleteBtn.click();

      // Verify flash error prevents deletion because students/dependencies exist
      await expect(page.locator('[data-testid="flash-error"]')).toBeVisible();
      await expect(page.locator('[data-testid="flash-error"]')).toContainText(/cannot be deactivated/i);
    }

    // Finally, clean up our test class (which has 0 enrolled students and is safe to delete)
    page.once('dialog', async dialog => {
      await dialog.accept();
    });
    const testDeleteBtn = updatedRow.locator('[data-testid^="btn-delete-class-"]');
    await testDeleteBtn.click();
    await expect(page.locator('[data-testid="flash-success"]')).toBeVisible();
  });

});
