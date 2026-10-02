// @ts-check
const { test, expect } = require('./fixtures/auth.fixture');

test.describe('Inspect Student Document Upload in Student Profile', () => {
  test('Inspect Documents tab and Upload New Document form', async ({ authenticatedPage }) => {
    const page = authenticatedPage;

    // Navigate to student profile 46
    await page.goto('students/profile/46');
    await page.waitForLoadState('networkidle');

    // Click on Documents tab
    const docTabBtn = page.locator('button[data-tab="documents"], button:has-text("Documents")');
    await expect(docTabBtn).toBeVisible();
    await docTabBtn.click();

    // Verify Tab 5 Documents is visible
    const tabDocs = page.locator('#tab-documents');
    await expect(tabDocs).toBeVisible();

    // Inspect the Upload New Document form
    const form = page.locator('#tab-documents form[action*="upload_document"]');
    await expect(form).toBeVisible();

    const formDetails = await form.evaluate((el) => {
      return {
        action: el.getAttribute('action'),
        method: el.getAttribute('method'),
        enctype: el.getAttribute('enctype'),
        className: el.className,
      };
    });
    console.log('FORM DETAILS:', JSON.stringify(formDetails, null, 2));

    // Inspect Document Type selector
    const docTypeSelect = form.locator('select[name="document_type"]');
    const docTypeDetails = await docTypeSelect.evaluate((el) => {
      return {
        name: el.getAttribute('name'),
        id: el.id,
        required: el.hasAttribute('required'),
        options: Array.from(el.querySelectorAll('option')).map(o => o.value)
      };
    });
    console.log('DOC TYPE SELECT:', JSON.stringify(docTypeDetails, null, 2));

    // Inspect Document Title input
    const docNameInput = form.locator('input[name="document_name"]');
    const docNameDetails = await docNameInput.evaluate((el) => {
      return {
        name: el.getAttribute('name'),
        id: el.id,
        required: el.hasAttribute('required'),
        placeholder: el.getAttribute('placeholder')
      };
    });
    console.log('DOC NAME INPUT:', JSON.stringify(docNameDetails, null, 2));

    // Inspect File Input
    const fileInput = form.locator('input[type="file"][name="document_file"]');
    const fileInputDetails = await fileInput.evaluate((el) => {
      return {
        name: el.getAttribute('name'),
        id: el.id,
        accept: el.getAttribute('accept'),
        required: el.hasAttribute('required'),
        className: el.className,
        parentHtml: el.parentElement ? el.parentElement.innerHTML : null
      };
    });
    console.log('FILE INPUT DETAILS:', JSON.stringify(fileInputDetails, null, 2));

    // Take screenshot of Upload New Document section
    await page.screenshot({ path: 'scratch/student_doc_upload_current.png' });
    console.log('Screenshot saved to scratch/student_doc_upload_current.png');
  });
});
