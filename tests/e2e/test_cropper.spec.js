const { test, expect } = require('./fixtures/auth.fixture');

test('Staff Profile Image and Cropper Modal E2E browser test', async ({ authenticatedPage: page, baseURL }) => {
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

  // Navigate to Add Staff
  await page.goto(`${targetURL}staff/add`);
  await page.waitForLoadState('networkidle');

  // Verify Cropper is defined and loaded without errors
  const isCropperDefined = await page.evaluate(() => typeof window.Cropper === 'function');
  expect(isCropperDefined).toBe(true);

  // Verify elements on Add Staff page
  await expect(page.locator('#btn-choose-photo')).toBeVisible();
  await expect(page.locator('#cropper-modal')).toBeHidden();

  // Test opening cropper modal via simulated file data
  await page.evaluate(() => {
    // Generate sample 100x133 portrait canvas dataURL
    const canvas = document.createElement('canvas');
    canvas.width = 100;
    canvas.height = 133;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#4f46e5';
    ctx.fillRect(0, 0, 100, 133);
    const dataUrl = canvas.toDataURL('image/jpeg');

    // Call openCropperModal
    openCropperModal(dataUrl);
  });

  // Verify modal is now visible
  await expect(page.locator('#cropper-modal')).toBeVisible();
  await expect(page.locator('#btn-cropper-zoom-in')).toBeVisible();
  await expect(page.locator('#btn-cropper-zoom-out')).toBeVisible();
  await expect(page.locator('#btn-cropper-rotate-left')).toBeVisible();
  await expect(page.locator('#btn-cropper-rotate-right')).toBeVisible();
  await expect(page.locator('#btn-cropper-reset')).toBeVisible();
  await expect(page.locator('#btn-cropper-apply')).toBeVisible();

  // Test Zoom in
  await page.click('#btn-cropper-zoom-in');
  // Test Rotate
  await page.click('#btn-cropper-rotate-right');
  await page.click('#btn-cropper-rotate-left');
  // Test Reset
  await page.click('#btn-cropper-reset');

  // Test Apply Crop
  await page.click('#btn-cropper-apply');
  await expect(page.locator('#cropper-modal')).toBeHidden();

  // Verify cropped preview is shown
  await expect(page.locator('#staff-photo-preview-img')).toBeVisible();
  await expect(page.locator('#btn-remove-photo')).toBeVisible();

  // Filter out any Tailwind CDN warning if present
  const fatalErrors = consoleErrors.filter(e => !e.includes('cdn.tailwindcss.com'));
  expect(fatalErrors).toEqual([]);
});
