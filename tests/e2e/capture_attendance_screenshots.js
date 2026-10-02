const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 1200 } });

  // 1. Login
  await page.goto('http://127.0.0.1/schoolnew/auth/login');
  await page.locator('input[name="identity"], input[name="email"], input[type="text"]').first().fill('admin@gmail.com');
  await page.locator('input[name="password"]').first().fill('123456');
  await page.locator('button[type="submit"]').first().click();
  await page.waitForURL(/dashboard/);
  await page.waitForLoadState('networkidle');

  const artifactDir = 'C:\\Users\\ANANTHU\\.gemini\\antigravity-ide\\brain\\dfcfa93d-50c8-40dd-bbaa-3cbcaabd7ef7';

  // 2. Student 106 (SS Group - Period-wise Attendance & Subject-wise Breakdown)
  await page.goto('http://127.0.0.1/schoolnew/students/profile/106?tab=attendance');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: path.join(artifactDir, 'student_106_period_attendance.png'), fullPage: true });
  console.log('Saved student_106_period_attendance.png');

  // 3. Student 46 (KG's Group - Day-wise Attendance)
  await page.goto('http://127.0.0.1/schoolnew/students/profile/46?tab=attendance');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: path.join(artifactDir, 'student_46_day_attendance.png'), fullPage: true });
  console.log('Saved student_46_day_attendance.png');

  await browser.close();
  console.log('Screenshots captured successfully!');
})();
