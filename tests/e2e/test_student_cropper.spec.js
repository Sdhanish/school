const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Student Profile Image & Cropper E2E Tests', () => {

  test('Add Student: Image Upload & 3:4 Cropper Modal Functionality', async ({ authenticatedPage: page, baseURL }) => {
    const targetURL = baseURL || 'http://localhost/schoolnew/';
    const consoleErrors = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });
    page.on('pageerror', err => {
      consoleErrors.push(err.message);
    });

    // 1. Navigate to Add Student (Wizard Step 1)
    await page.goto(`${targetURL}students/add`);
    await page.waitForLoadState('networkidle');

    // Verify Cropper is defined
    const isCropperDefined = await page.evaluate(() => typeof window.Cropper === 'function');
    expect(isCropperDefined).toBe(true);

    // Verify UI components on Add Student
    await expect(page.locator('#btn-choose-photo')).toBeVisible();
    await expect(page.locator('#student-cropper-modal')).toBeHidden();

    // 2. Open Cropper Modal via sample 150x200 3:4 portrait data URL
    await page.evaluate(() => {
      const canvas = document.createElement('canvas');
      canvas.width = 150;
      canvas.height = 200;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#0f766e';
      ctx.fillRect(0, 0, 150, 200);
      const dataUrl = canvas.toDataURL('image/jpeg');
      window.openStudentCropperModal(dataUrl, 'sample_student.jpg');
    });

    // 3. Verify Cropper Modal opens
    await expect(page.locator('#student-cropper-modal')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-zoom-in')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-zoom-out')).toBeVisible();
    await expect(page.locator('#student-cropper-zoom-range')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-rotate-left')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-rotate-right')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-reset')).toBeVisible();
    await expect(page.locator('#btn-student-cropper-apply')).toBeVisible();

    // 4. Test controls
    await page.click('#btn-student-cropper-zoom-in');
    await page.click('#btn-student-cropper-zoom-out');
    await page.click('#btn-student-cropper-rotate-right');
    await page.click('#btn-student-cropper-rotate-left');
    await page.click('#btn-student-cropper-reset');

    // 5. Apply crop
    await page.click('#btn-student-cropper-apply');
    await expect(page.locator('#student-cropper-modal')).toBeHidden();

    // 6. Verify preview updated and remove button is visible
    await expect(page.locator('#photo-preview-img')).toBeVisible();
    await expect(page.locator('#photo-remove-btn')).toBeVisible();

    // 7. Verify no fatal console errors
    const fatalErrors = consoleErrors.filter(e => !e.includes('cdn.tailwindcss.com') && !e.includes('favicon'));
    expect(fatalErrors).toEqual([]);
  });

  test('Edit Student: Image Upload, Preview, Cropper & Removal', async ({ authenticatedPage: page, baseURL }) => {
    const targetURL = baseURL || 'http://localhost/schoolnew/';
    const consoleErrors = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });
    page.on('pageerror', err => {
      consoleErrors.push(err.message);
    });

    // 1. Navigate to Edit Student (ID 1)
    await page.goto(`${targetURL}students/edit/1`);
    await page.waitForLoadState('networkidle');

    // Verify Cropper is defined
    const isCropperDefined = await page.evaluate(() => typeof window.Cropper === 'function');
    expect(isCropperDefined).toBe(true);

    // Verify Edit Student elements
    await expect(page.locator('#btn-choose-photo')).toBeVisible();
    await expect(page.locator('#student-cropper-modal')).toBeHidden();

    // 2. Open Cropper Modal via sample 150x200 portrait data URL
    await page.evaluate(() => {
      const canvas = document.createElement('canvas');
      canvas.width = 150;
      canvas.height = 200;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#047857';
      ctx.fillRect(0, 0, 150, 200);
      const dataUrl = canvas.toDataURL('image/jpeg');
      window.openStudentCropperModal(dataUrl, 'student_edit_sample.jpg');
    });

    // 3. Verify Cropper Modal opens
    await expect(page.locator('#student-cropper-modal')).toBeVisible();

    // 4. Test zoom & rotate controls
    await page.click('#btn-student-cropper-zoom-in');
    await page.click('#btn-student-cropper-rotate-right');
    await page.click('#btn-student-cropper-apply');

    // 5. Verify modal closes & preview is updated
    await expect(page.locator('#student-cropper-modal')).toBeHidden();
    await expect(page.locator('#photo-preview-img')).toBeVisible();
    await expect(page.locator('#photo-remove-btn')).toBeVisible();

    // 6. Test Remove Photo button
    await page.click('#photo-remove-btn');
    await expect(page.locator('#photo-remove-btn')).toBeHidden();
    const removePhotoVal = await page.locator('#remove_photo').inputValue();
    expect(removePhotoVal).toBe('1');

    // 7. Verify no fatal console errors
    const fatalErrors = consoleErrors.filter(e => !e.includes('cdn.tailwindcss.com') && !e.includes('favicon'));
    expect(fatalErrors).toEqual([]);
  });

});

