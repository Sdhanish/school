// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');
const path = require('path');

test.describe('Core Student Admission Wizard Lifecycle', () => {

  const samplePhoto   = path.resolve(__dirname, '../test_assets/sample_valid.png');
  const sampleTcDoc   = path.resolve(__dirname, '../test_assets/sample_valid.pdf');
  const invalidFile   = path.resolve(__dirname, '../test_assets/sample_invalid.txt');
  const oversizedPhoto = path.resolve(__dirname, '../test_assets/sample_oversized_4mb.jpg');
  const oversizedTc   = path.resolve(__dirname, '../test_assets/sample_oversized_4mb.pdf');

  test('Happy Path: Full 3-step registration with Student Image and TC Document', async ({ authenticatedPage: page, baseURL }) => {
    const uniqueId = Date.now().toString().slice(-6);
    const admissionNo = `ADM2026${uniqueId}`;
    const firstName = `Aarav${uniqueId}`;
    const lastName = `Verma`;

    // ── STEP 1: Student Details & Photograph Upload ──────────────────────────
    await page.goto(`${baseURL}students/add`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#step1-form')).toBeVisible();

    // Verify Student Image label and upload field
    await expect(page.locator('label:has-text("Student Image")')).toBeVisible();

    // Upload Student Photograph
    await page.setInputFiles('#student_image_file', samplePhoto);
    // Wait for AJAX upload to complete and preview to appear
    await expect(page.locator('#photo-preview-img')).toBeVisible();
    await expect(page.locator('#photo-remove-btn')).toBeVisible();
    await expect(page.locator('#photo_temp_path')).not.toHaveValue('');

    // Fill personal information
    await page.fill('#admission_number', admissionNo);
    await page.fill('#first_name', firstName);
    await page.fill('input[name="last_name"]', lastName);
    await page.click('#btn-step1-next');

    // ── STEP 2: Academic Details & Mandatory TC ──────────────────────────────
    await page.waitForURL(/step=2/);
    await expect(page.locator('#step2-form')).toBeVisible();

    // Select Class
    await page.selectOption('#class_id', { index: 1 });

    // Verify Previous School TC Number & Document are labeled as required
    await expect(page.locator('label:has-text("TC Number") span.text-error')).toBeVisible();
    await expect(page.locator('label:has-text("TC Document") span.text-error')).toBeVisible();

    // Fill previous school info
    await page.fill('#prev_school_name', 'St. Xavier High School');
    await page.fill('#tc_number', `TC/2026/${uniqueId}`);

    // Upload TC Document
    await page.setInputFiles('#tc_document_file', sampleTcDoc);
    await expect(page.locator('#tc-file-status')).toBeVisible();
    await expect(page.locator('#tc_temp_path')).not.toHaveValue('');

    // Advance to Step 3 (saving Step 2 into session)
    await page.click('#btn-step2-next');
    await page.waitForURL(/step=3/);
    await expect(page.locator('#step3-form')).toBeVisible();

    // ── TEST BACK / FORTH NAVIGATION PERSISTENCE ─────────────────────────────
    // Go Back from Step 3 to Step 2
    await page.click('#btn-step3-back');
    await page.waitForURL(/step=2/);
    await expect(page.locator('#step2-form')).toBeVisible();
    await expect(page.locator('#prev_school_name')).toHaveValue('St. Xavier High School');
    await expect(page.locator('#tc_number')).toHaveValue(`TC/2026/${uniqueId}`);
    await expect(page.locator('#tc-file-status')).toBeVisible();

    // Go Back from Step 2 to Step 1
    await page.click('#btn-step2-back');
    await page.waitForURL(/students\/add/);
    await expect(page.locator('#step1-form')).toBeVisible();
    await expect(page.locator('#admission_number')).toHaveValue(admissionNo);
    await expect(page.locator('#first_name')).toHaveValue(firstName);
    // Verify photo preview is still intact
    await expect(page.locator('#photo-preview-img')).toBeVisible();

    // Advance to Step 2 again
    await page.click('#btn-step1-next');
    await page.waitForURL(/step=2/);
    await expect(page.locator('#step2-form')).toBeVisible();

    // Advance to Step 3 again
    await page.click('#btn-step2-next');
    await page.waitForURL(/step=3/);
    await expect(page.locator('#step3-form')).toBeVisible();

    // ── STEP 3: Parent/Guardian & Summary Confirmation ───────────────────────
    await page.waitForURL(/step=3/);
    await expect(page.locator('#step3-form')).toBeVisible();

    // Summary should show registration details with photo thumbnail
    await expect(page.locator('text=Registration Summary')).toBeVisible();
    await expect(page.locator(`text=${firstName} ${lastName}`)).toBeVisible();

    // Fill guardian details
    await page.fill('#guardian_name', 'Rajesh Verma');
    await page.fill('#guardian_phone', '9847011223');
    await page.click('#btn-save-student');

    // ── PROFILE VIEW VERIFICATION ────────────────────────────────────────────
    await page.waitForURL(/students\/(profile\/\d+|overview)/);
    // Verify the profile page displays the student's photo image
    await expect(page.locator('img[alt*="Aarav"]')).toBeVisible();
  });

  test('Validation: Reject invalid image types, oversized files (>3MB) and enforce mandatory TC fields', async ({ authenticatedPage: page, baseURL }) => {
    // Navigate to wizard Step 1
    await page.goto(`${baseURL}students/add`);
    await page.waitForLoadState('networkidle');

    // 1. Attempt invalid file upload on Step 1 Image
    await page.setInputFiles('#student_image_file', invalidFile);
    await expect(page.locator('#err-student_image')).toContainText('Please upload a JPG, JPEG, or PNG image.');

    // 2. Attempt oversized image upload (>3MB)
    await page.setInputFiles('#student_image_file', oversizedPhoto);
    await expect(page.locator('#err-student_image')).toContainText('Student image must not exceed 3 MB.');

    // Upload valid image and proceed
    await page.setInputFiles('#student_image_file', samplePhoto);
    await expect(page.locator('#photo-preview-img')).toBeVisible();

    const uniqueId = Date.now().toString().slice(-6);
    await page.fill('#admission_number', `VAL${uniqueId}`);
    await page.fill('#first_name', 'Validation');
    await page.fill('input[name="last_name"]', 'Student');
    await page.click('#btn-step1-next');

    // 3. Step 2 validation checks
    await page.waitForURL(/step=2/);
    await page.selectOption('#class_id', { index: 1 });
    await page.fill('#prev_school_name', 'Previous Model School');

    // Leave TC Number and TC Document blank and submit
    await page.click('#btn-step2-next');

    // Verify required error messages for TC Number and TC Document
    await expect(page.locator('#err-tc_number')).toContainText('TC Number is required.');
    await expect(page.locator('#err-tc_document')).toContainText('TC Document is required.');

    // 4. Attempt invalid TC document file
    await page.setInputFiles('#tc_document_file', invalidFile);
    await expect(page.locator('#err-tc_document')).toContainText('TC Document must be a PDF, JPG, JPEG, or PNG file.');

    // 5. Attempt oversized TC document (>3MB)
    await page.setInputFiles('#tc_document_file', oversizedTc);
    await expect(page.locator('#err-tc_document')).toContainText('TC Document must not exceed 3 MB.');
  });

  test('Failure State: Submitting step 1 without mandatory first name triggers validation', async ({ authenticatedPage: page, baseURL }) => {
    await page.goto(`${baseURL}students/add`);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#step1-form')).toBeVisible();

    // Clear first name input
    await page.fill('#first_name', '');

    // Attempt step 1 next
    await page.click('#btn-step1-next');

    // Verify error shown and remains on step 1
    await expect(page.locator('#err-first_name')).toBeVisible();
    await expect(page.locator('#step1-form')).toBeVisible();
  });

});
