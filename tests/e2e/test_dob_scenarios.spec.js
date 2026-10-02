const { test, expect } = require('@playwright/test');

test.describe('Student Registration Date of Birth Full Verification Suite', () => {

  test.beforeEach(async ({ page }) => {
    // Login as Admin
    await page.goto('http://127.0.0.1/schoolnew/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard|students/);
  });

  test('PLAYWRIGHT TEST 1 — NEW STUDENT: DOB is blank and DOM attributes verified', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // 1. Open Student Registration via entry route /students/register
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    // 2. Locate the Date of Birth input
    const dobInput = page.locator('#date_of_birth');
    await expect(dobInput).toBeVisible();

    // 3. Inspect DOM attributes: value, defaultValue, name, id, type, max, autocomplete
    const attrs = await dobInput.evaluate(el => ({
      value: el.value,
      defaultValue: el.defaultValue,
      name: el.name,
      id: el.id,
      type: el.type,
      max: el.getAttribute('max'),
      autocomplete: el.getAttribute('autocomplete')
    }));

    expect(attrs.value).toBe('');
    expect(attrs.defaultValue).toBe('');
    expect(attrs.name).toBe('date_of_birth');
    expect(attrs.id).toBe('date_of_birth');
    expect(attrs.type).toBe('date');
    expect(attrs.autocomplete).toBe('off');

    // 4. Verify no default date (not 2012-06-15, not today, not 06/15/2012)
    const valImmediately = await dobInput.inputValue();
    expect(valImmediately).toBe('');

    // 5. Inspect value after phone component initializes and user interacts with other fields
    await page.locator('#first_name').fill('Anandhu');
    await page.locator('#middle_name').fill('s');
    await page.locator('#last_name').fill('Uthaman');
    await page.locator('#student_phone').fill('9847011223');
    await page.locator('#student_email').fill('anandhu@example.com');
    await page.locator('#city').fill('Ernakulam');

    // Verify DOB is still completely blank after interacting with all other fields
    expect(await dobInput.inputValue()).toBe('');

    // Verify zero console errors
    expect(consoleErrors).toEqual([]);
  });

  test('PLAYWRIGHT TEST 2 — NEW STUDENT AFTER PREVIOUS CANCELLED REGISTRATION', async ({ page }) => {
    // 1. Start New Student
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    // 2. Enter DOB = 2012-06-15
    await page.locator('#date_of_birth').fill('2012-06-15');
    expect(await page.locator('#date_of_birth').inputValue()).toBe('2012-06-15');

    // 3. Cancel registration
    await page.locator('#wizard-cancel-btn').click();
    await page.waitForURL(/students/, { timeout: 10000 });

    // 4. Start New Student again
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    // 5. Verify DOB is empty
    expect(await page.locator('#date_of_birth').inputValue()).toBe('');
  });

  test('PLAYWRIGHT TEST 3 — AFTER SAVING A STUDENT: NEW REGISTRATION IS CLEAN', async ({ page }) => {
    // 1. Complete a full 3-step registration
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    const uniqueNum = Date.now().toString().slice(-6);
    const admNo = 'ADM_T3_' + uniqueNum;

    // Step 1: fill details with DOB
    await page.locator('#admission_number').fill(admNo);
    await page.locator('#first_name').fill('TestSave');
    await page.locator('select[name="gender"]').selectOption('Male');
    await page.locator('#date_of_birth').fill('2014-04-10');
    await page.locator('#btn-step1-next').click();
    await page.waitForURL(/step=2/, { timeout: 10000 });

    // Step 2: fill required academic details
    await page.locator('#class_id').selectOption({ index: 1 });
    await page.locator('#no_previous_school').check();
    await page.locator('#btn-step2-next').click();
    await page.waitForURL(/step=3/, { timeout: 10000 });

    // Step 3: fill parent details and save
    await page.locator('#guardian_name').fill('Guardian ' + uniqueNum);
    await page.locator('#guardian_phone').fill('9847099887');
    await page.locator('#btn-save-student').click();

    // Wait for redirect to profile
    await page.waitForURL(/students\/profile/, { timeout: 15000 });

    // 2. Now start a brand NEW student
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    // 3. Verify DOB is completely blank (not 2014-04-10, not 2012-06-15)
    expect(await page.locator('#date_of_birth').inputValue()).toBe('');
  });

  test('PLAYWRIGHT TEST 4 — MULTI-STEP: USER-ENTERED DOB IS PRESERVED ACROSS STEPS', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    // Verify initial DOB is empty
    expect(await page.locator('#date_of_birth').inputValue()).toBe('');

    // Enter DOB
    const enteredDob = '2016-09-25';
    await page.locator('#date_of_birth').fill(enteredDob);
    await page.locator('#admission_number').fill('ADM_MS_' + Date.now().toString().slice(-6));
    await page.locator('#first_name').fill('MultiStepStudent');
    await page.locator('select[name="gender"]').selectOption('Female');

    // Move to Step 2
    await page.locator('#btn-step1-next').click();
    await page.waitForURL(/step=2/, { timeout: 10000 });

    // Return to Step 1 via Back button
    await page.locator('#btn-step2-back').click();
    await page.waitForURL(/students\/add/, { timeout: 10000 });
    await page.waitForSelector('#step1-form');

    // Verify entered DOB remains preserved
    expect(await page.locator('#date_of_birth').inputValue()).toBe(enteredDob);
  });

  test('PLAYWRIGHT TEST 5 — EDIT STUDENT: LOADS STORED DOB CORRECTLY', async ({ page }) => {
    // Check Student 117 (has 2012-06-15)
    await page.goto('http://127.0.0.1/schoolnew/students/edit/117');
    await page.waitForSelector('#student-edit-form');
    expect(await page.locator('#date_of_birth').inputValue()).toBe('2012-06-15');

    // Check Student 46 (has 2022-03-11)
    await page.goto('http://127.0.0.1/schoolnew/students/edit/46');
    await page.waitForSelector('#student-edit-form');
    expect(await page.locator('#date_of_birth').inputValue()).toBe('2022-03-11');
  });

  test('PLAYWRIGHT TEST 6 — REFRESH NEW REGISTRATION KEEPS DOB EMPTY', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/register');
    await page.waitForSelector('#step1-form');

    expect(await page.locator('#date_of_birth').inputValue()).toBe('');

    // Reload
    await page.reload();
    await page.waitForSelector('#step1-form');

    expect(await page.locator('#date_of_birth').inputValue()).toBe('');
  });

});
