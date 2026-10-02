const { test, expect } = require('@playwright/test');

test.describe('Student Registration Step 1 & Phone Validation Tests', () => {

  test.beforeEach(async ({ page }) => {
    // Login as Admin before each test
    await page.goto('http://127.0.0.1/schoolnew/login');
    await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
    await page.locator('input[name="password"]').first().fill('123456');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/dashboard|students/);
  });

  test('TEST 1 — New Student with Valid Indian Number advances to Step 2', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // 1. Open Student Registration
    await page.goto('http://127.0.0.1/schoolnew/students/add?reset=1');
    await page.waitForSelector('#step1-form');

    // 2. Verify Step 1 is visible
    expect(await page.locator('#step1-form').isVisible()).toBe(true);

    // 3. Locate Student Phone
    const phoneInput = page.locator('#student_phone');
    expect(await phoneInput.isVisible()).toBe(true);

    // 4. Verify India is selected & +91 appears in country selector
    const dialCode = await page.locator('.iti__selected-dial-code').first().textContent();
    expect(dialCode.trim()).toBe('+91');

    // 5. Verify the phone input is empty initially
    expect(await phoneInput.inputValue()).toBe('');

    // 6. Enter: 9847011223
    await phoneInput.fill('9847011223');
    await phoneInput.blur();

    // 7. Verify phone input does NOT contain +91 and contains only local/national formatted number
    const val = await phoneInput.inputValue();
    expect(val).not.toContain('+91');
    expect(val.replace(/\s+/g, '')).toBe('9847011223');

    // 8. Fill required Step 1 fields
    await page.locator('#admission_number').fill('ADM' + Date.now());
    await page.locator('#first_name').fill('Aarav');
    await page.locator('select[name="gender"]').selectOption('Male');
    await page.locator('#date_of_birth').fill('2015-06-10');

    // 9. Click Next
    await page.locator('#btn-step1-next').click();

    // 10. Verify Step 2 becomes active
    await page.waitForURL(/step=2/, { timeout: 10000 });
    expect(page.url()).toContain('step=2');
    expect(await page.locator('#step2-form').isVisible()).toBe(true);
    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 2 — Empty Optional Phone allows advancing to Step 2', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // 1. Open Student Registration
    await page.goto('http://127.0.0.1/schoolnew/students/add?reset=1');
    await page.waitForSelector('#step1-form');

    // 2. Leave Student Phone, Email, Address empty
    const phoneInput = page.locator('#student_phone');
    await phoneInput.fill('');

    // 3. Fill required fields
    await page.locator('#admission_number').fill('ADM' + Date.now());
    await page.locator('#first_name').fill('Ananya');
    await page.locator('select[name="gender"]').selectOption('Female');
    await page.locator('#date_of_birth').fill('2016-03-20');

    // 4. Click Next
    await page.locator('#btn-step1-next').click();

    // 5. Verify Step 2 opens
    await page.waitForURL(/step=2/, { timeout: 10000 });
    expect(page.url()).toContain('step=2');
    expect(await page.locator('#step2-form').isVisible()).toBe(true);
    expect(consoleErrors).toHaveLength(0);
  });

  test('TEST 3 — Invalid Phone blocks advancing to Step 2 and displays error', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/add?reset=1');
    await page.waitForSelector('#step1-form');

    // Fill required fields
    await page.locator('#admission_number').fill('ADM' + Date.now());
    await page.locator('#first_name').fill('TestStudent');
    await page.locator('select[name="gender"]').selectOption('Male');
    await page.locator('#date_of_birth').fill('2015-05-15');

    // Enter invalid Indian phone (starts with 1)
    const phoneInput = page.locator('#student_phone');
    await phoneInput.fill('1234567890');
    await phoneInput.blur();

    // Click Next
    await page.locator('#btn-step1-next').click();

    // Verify still on Step 1
    await page.waitForTimeout(1000);
    expect(page.url()).not.toContain('step=2');
    expect(await page.locator('#step1-form').isVisible()).toBe(true);

    // Verify error message is visible
    const errorMsg = page.locator('.phone-feedback:visible, #err-student_phone:visible');
    expect(await errorMsg.count()).toBeGreaterThan(0);
    const errText = await errorMsg.first().textContent();
    expect(errText.toLowerCase()).toContain('indian mobile number');
  });

  test('TEST 4 — Existing Student in Edit form displays national number without +91', async ({ page }) => {
    // Open Edit Student 117 (which has student_phone = +918565656565)
    await page.goto('http://127.0.0.1/schoolnew/students/edit/117');
    await page.waitForSelector('#student-edit-form');

    const phoneInput = page.locator('#student_phone');
    const phoneVal = await phoneInput.inputValue();
    console.log('Edit student 117 phone value:', phoneVal);

    // Verify country selector has +91
    const dialCode = await page.locator('.iti__selected-dial-code').first().textContent();
    expect(dialCode.trim()).toBe('+91');

    // Verify phone input does NOT contain +91
    expect(phoneVal).not.toContain('+91');
    expect(phoneVal.replace(/\s+/g, '')).toBe('8565656565');

    // Verify guardian_phone also does NOT contain +91 in visible input
    const guardianInput = page.locator('#guardian_phone');
    const guardianVal = await guardianInput.inputValue();
    expect(guardianVal).not.toContain('+91');
    expect(guardianVal.replace(/\s+/g, '')).toBe('9678647864');
  });

  test('TEST 5 — Country Change does not produce corrupt numbers and revalidates', async ({ page }) => {
    await page.goto('http://127.0.0.1/schoolnew/students/add?reset=1');
    await page.waitForSelector('#step1-form');

    const phoneInput = page.locator('#student_phone');
    await phoneInput.fill('9847011223');
    await phoneInput.blur();

    // Click country selector button
    await page.locator('.iti__selected-country').first().click();
    await page.waitForSelector('.iti__country-listbox, .iti__country-list');

    // Select United Kingdom (gb)
    const ukOption = page.locator('li[data-iso2="gb"], .iti__country[data-iso2="gb"]').first();
    await ukOption.click();

    // Verify dial code changed to +44
    const dialCode = await page.locator('.iti__selected-dial-code').first().textContent();
    expect(dialCode.trim()).toBe('+44');

    // Verify visible input does NOT contain +44+918565656565 or +91
    const afterChangeVal = await phoneInput.inputValue();
    expect(afterChangeVal).not.toContain('+44');
    expect(afterChangeVal).not.toContain('+91');
  });

  test('TEST 6 — Repeated Step Navigation works reliably without errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // 1. Visit Step 1
    await page.goto('http://127.0.0.1/schoolnew/students/add?reset=1');
    await page.waitForSelector('#step1-form');

    // Fill Step 1
    await page.locator('#admission_number').fill('ADM' + Date.now());
    await page.locator('#first_name').fill('RepeatedNavStudent');
    await page.locator('select[name="gender"]').selectOption('Male');
    await page.locator('#date_of_birth').fill('2015-08-15');
    await page.locator('#student_phone').fill('9847011223');

    // Advance to Step 2
    await page.locator('#btn-step1-next').click();
    await page.waitForURL(/step=2/, { timeout: 10000 });
    expect(await page.locator('#step2-form').isVisible()).toBe(true);

    // Return to Step 1
    await page.goto('http://127.0.0.1/schoolnew/students/add?step=1');
    await page.waitForSelector('#step1-form');

    // Verify phone value is still 98470 11223 (NO +91)
    const phoneVal = await page.locator('#student_phone').inputValue();
    expect(phoneVal).not.toContain('+91');
    expect(phoneVal.replace(/\s+/g, '')).toBe('9847011223');

    // Advance to Step 2 again
    await page.locator('#btn-step1-next').click();
    await page.waitForURL(/step=2/, { timeout: 10000 });
    expect(await page.locator('#step2-form').isVisible()).toBe(true);

    // Return to Step 1 again
    await page.goto('http://127.0.0.1/schoolnew/students/add?step=1');
    await page.waitForSelector('#step1-form');

    // Advance to Step 2 third time
    await page.locator('#btn-step1-next').click();
    await page.waitForURL(/step=2/, { timeout: 10000 });
    expect(await page.locator('#step2-form').isVisible()).toBe(true);

    expect(consoleErrors).toHaveLength(0);
  });

});
