// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Attendance Audit Trail ("Marked By") Verification', () => {

  async function loginAs(page, email, password) {
    await page.goto('http://127.0.0.1/schoolnew/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill(email);
    await page.locator('input[name="password"]').first().fill(password);
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard/);
    await page.waitForLoadState('networkidle');
  }

  test('1. Teacher marks attendance and verifies role and teacher name', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', err => {
      pageErrors.push(err.message);
    });

    // Login as Teacher
    await loginAs(page, 'teacher@school.com', '123456');

    // Navigate to Mark Attendance
    await page.goto('http://127.0.0.1/schoolnew/attendance/mark_attendance?class_id=1&section_id=12&date=2026-09-09&academic_year_id=1');
    await page.waitForLoadState('networkidle');

    // Submit attendance
    const submitBtn = page.locator('button[type="submit"]:has-text("Save Attendance"), button[type="submit"]:has-text("Update Attendance"), button[type="submit"]').last();
    await submitBtn.click();
    await page.waitForLoadState('networkidle');

    // Verify banner shows Originally marked by: Teacher · Arun Krishnan
    const sheetHeader = page.locator('body');
    await expect(sheetHeader).toContainText('Teacher · Arun Krishnan');
    expect(pageErrors).toEqual([]);
  });

  test('2. Principal marks attendance on different date', async ({ page }) => {
    // Login as Principal
    await loginAs(page, 'principal@school.com', '123456');

    // Navigate to Mark Attendance
    await page.goto('http://127.0.0.1/schoolnew/attendance/mark_attendance?class_id=1&section_id=12&date=2026-09-11&academic_year_id=1');
    await page.waitForLoadState('networkidle');

    const submitBtn = page.locator('button[type="submit"]').last();
    await submitBtn.click();
    await page.waitForLoadState('networkidle');

    // Verify banner shows Originally marked by: Principal · Antony Xavier
    const body = page.locator('body');
    await expect(body).toContainText('Principal · Antony Xavier');
  });

  test('3. Student Profile displays Marked By column, Role · Name markers, and historical Not Available', async ({ page }) => {
    // Login as Admin
    await loginAs(page, 'admin@gmail.com', '123456');

    // Navigate to student profile 46
    await page.goto('http://127.0.0.1/schoolnew/students/profile/46');
    await page.waitForLoadState('networkidle');

    // Switch to Attendance tab
    await page.locator('button[data-tab="attendance"]').click();
    await expect(page.locator('#tab-attendance')).toBeVisible();

    // Verify Table Header has MARKED BY
    const tableHeader = page.locator('#tab-attendance th:has-text("MARKED BY")');
    await expect(tableHeader).toBeVisible();

    // Verify row displays Teacher · Arun Krishnan
    await expect(page.locator('#tab-attendance')).toContainText('Teacher · Arun Krishnan');

    // Verify row displays Principal · Antony Xavier
    await expect(page.locator('#tab-attendance')).toContainText('Principal · Antony Xavier');

    // Verify historical displays Not Available
    await expect(page.locator('#tab-attendance')).toContainText('Not Available');
  });

  test('4. Attendance Dashboard Recent Activity displays marker Role and Name', async ({ page }) => {
    await loginAs(page, 'admin@gmail.com', '123456');

    await page.goto('http://127.0.0.1/schoolnew/attendance');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('body')).toContainText('Marked by');
  });

  test('5. Staff Attendance displays Marked By column and historical Not Available', async ({ page }) => {
    await loginAs(page, 'admin@gmail.com', '123456');

    await page.goto('http://127.0.0.1/schoolnew/staff/attendance?date=2026-08-18');
    await page.waitForLoadState('networkidle');

    const markedByHeader = page.locator('th:has-text("Marked By")');
    await expect(markedByHeader).toBeVisible();

    // Historical staff attendance shows Not Available
    await expect(page.locator('body')).toContainText('Not Available');
  });
});
