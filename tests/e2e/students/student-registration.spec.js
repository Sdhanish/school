// @ts-check
const { test, expect } = require('../fixtures/auth.fixture');

test.describe('Student Admission Wizard & Registration E2E Lifecycle', () => {

  test('Happy Path: 3-step admission navigation, phone country code, and step progression', async ({ authenticatedPage: page, baseURL }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    const uniqueId = Date.now().toString().slice(-6);
    const admissionNo = `ADM2026${uniqueId}`;
    const firstName = `Aarav${uniqueId}`;
    const lastName = `Verma`;

    // ── STEP 1: Personal Details ─────────────────────────────────────────────
    await page.goto(`${baseURL}students/add?reset=1`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#step1-form')).toBeVisible();

    // Verify photo picker button exists
    await expect(page.locator('#btn-choose-photo')).toBeVisible();

    // Fill personal information
    await page.fill('#admission_number', admissionNo);
    await page.fill('#first_name', firstName);
    await page.fill('#last_name', lastName);
    await page.selectOption('select[name="gender"]', 'Male');
    await page.fill('#date_of_birth', '2015-06-15');

    // Verify Phone input with international dial code (+91 default)
    const phoneInput = page.locator('#student_phone');
    await expect(phoneInput).toBeVisible();
    await phoneInput.fill('9847011223');
    await phoneInput.blur();

    // Proceed to Step 2
    await page.click('#btn-step1-next');
    await page.waitForURL(/step=2/);
    await expect(page.locator('#step2-form')).toBeVisible();

    // ── STEP 2: Academic Details ─────────────────────────────────────────────
    // Select Class (first available class)
    await page.selectOption('select[name="class_id"], #class_id', { index: 1 });

    // Fill previous school information
    const prevSchool = page.locator('input[name="prev_school_name"], #prev_school_name');
    if (await prevSchool.isVisible()) {
      await prevSchool.fill('Model Academy High School');
    }

    const tcNumber = page.locator('input[name="tc_number"], #tc_number');
    if (await tcNumber.isVisible()) {
      await tcNumber.fill(`TC2026/${uniqueId}`);
    }

    // ── STEP NAVIGATION BACK & FORTH PERSISTENCE ─────────────────────────────
    const backBtn = page.locator('#btn-step2-back, button:has-text("Back")').first();
    if (await backBtn.isVisible()) {
      await backBtn.click();
      await page.waitForURL(/students\/add/);
      await expect(page.locator('#step1-form')).toBeVisible();
      // Verify first name was preserved
      await expect(page.locator('#first_name')).toHaveValue(firstName);
    }

    expect(consoleErrors.filter(e => !e.includes('favicon') && !e.includes('cdn.tailwindcss.com'))).toHaveLength(0);
  });

  test('Validation: Enforce required fields on Step 1 submission', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/add?reset=1`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#step1-form')).toBeVisible();

    // Clear first name and attempt to proceed
    await page.fill('#first_name', '');
    await page.click('#btn-step1-next');

    // Should remain on step 1 and display validation error
    await expect(page.locator('#err-first_name')).toBeVisible();
    await expect(page.locator('#step1-form')).toBeVisible();
  });

});
