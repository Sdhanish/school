<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-6">

  <!-- =========================================================================
       PAGE HEADER
       ========================================================================= -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-emerald-800 text-white flex items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-[22px]">group_add</span>
      </span>
      <div>
        <h2 class="font-bold text-xl text-slate-900 leading-tight">Bulk Student Add</h2>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
          <span>Student Management</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-slate-800 font-medium">Bulk Student Add</span>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="<?php echo site_url('students/all_students'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-[18px]">groups</span>
        <span>View All Students</span>
      </a>
      <a href="<?php echo site_url('students/register'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-button-primary text-white hover:bg-button-primary-hover text-xs font-bold transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        <span>Single Registration</span>
      </a>
    </div>
  </div>

  <!-- =========================================================================
       STEP 1: COMMON ACADEMIC SELECTION
       ========================================================================= -->
  <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
      <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">1</span>
      <h3 class="font-bold text-sm text-slate-900">Target Academic Class & Division</h3>
      <span class="text-xs text-slate-400 font-medium ml-auto">Required for all bulk enrollments</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
      
      <!-- Academic Year -->
      <div>
        <label for="bulk-academic-year" class="block font-bold text-slate-700 mb-1.5">
          Academic Year <span class="text-rose-600">*</span>
        </label>
        <select id="bulk-academic-year" onchange="onBulkYearChanged(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <?php foreach ($years as $y): ?>
            <?php $isActive = (!empty($y->is_active)); ?>
            <option value="<?php echo $y->academic_year_id; ?>" <?php echo ((!empty($selected_year) ? $selected_year : $y->is_active) == $y->academic_year_id) ? 'selected' : ''; ?>>
              <?php echo html_escape($y->year_name); ?><?php echo $isActive ? ' (Active)' : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Class / Grade -->
      <div>
        <label for="bulk-class-id" class="block font-bold text-slate-700 mb-1.5">
          Class / Grade <span class="text-rose-600">*</span>
        </label>
        <select id="bulk-class-id" onchange="onBulkClassChanged(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">-- Select Class --</option>
          <?php foreach ($classes as $cls): ?>
            <option value="<?php echo $cls->class_id; ?>" <?php echo ($selected_class == $cls->class_id) ? 'selected' : ''; ?>>
              <?php echo html_escape($cls->class_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Session / Division -->
      <div>
        <label for="bulk-section-id" class="block font-bold text-slate-700 mb-1.5">
          Session / Division <span class="text-rose-600">*</span>
        </label>
        <select id="bulk-section-id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $sec): ?>
              <option value="<?php echo $sec->section_id; ?>">
                Section <?php echo html_escape($sec->section_name); ?>
              </option>
            <?php endforeach; ?>
          <?php else: ?>
            <option value="12" selected>Division A (Default)</option>
          <?php endif; ?>
        </select>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       STEP 2: DUAL MODE TABS (EXCEL/CSV IMPORT VS BULK ENTRY)
       ========================================================================= -->
  <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
    
    <!-- Tab Navigation Ribbon -->
    <div class="flex items-center border-b border-slate-200 bg-slate-50/70 px-4 pt-2 overflow-x-auto">
      <button type="button" onclick="switchBulkTab('import')" id="tab-btn-import" class="flex items-center gap-2 px-5 py-3 text-xs font-bold border-b-2 border-emerald-700 text-emerald-800 bg-white rounded-t-xl transition-all shadow-2xs shrink-0">
        <span class="material-symbols-outlined text-[18px]">upload_file</span>
        <span>Import Excel / CSV</span>
      </button>
      <button type="button" onclick="switchBulkTab('entry')" id="tab-btn-entry" class="flex items-center gap-2 px-5 py-3 text-xs font-semibold text-slate-600 hover:text-slate-900 border-b-2 border-transparent transition-all shrink-0">
        <span class="material-symbols-outlined text-[18px]">grid_on</span>
        <span>Spreadsheet Bulk Entry</span>
      </button>
    </div>

    <div class="p-6">

      <!-- =====================================================================
           TAB 1: EXCEL / CSV IMPORT WORKFLOW
           ===================================================================== -->
      <div id="bulk-tab-import" class="space-y-6">
        
        <!-- Upload & Download Template Banner -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          
          <!-- Left 8 Cols: Drag & Drop Zone -->
          <div class="lg:col-span-8 space-y-3">
            <label class="block font-bold text-xs text-slate-800">
              Upload Student Spreadsheet (.csv, .xlsx, .xls)
            </label>
            
            <div id="drop-zone" onclick="document.getElementById('import_file_input').click()" class="border-2 border-dashed border-slate-300 hover:border-emerald-600 bg-slate-50/50 hover:bg-emerald-50/20 rounded-2xl p-8 text-center cursor-pointer transition-all">
              <input type="file" id="import_file_input" accept=".csv, .xlsx, .xls, .txt" class="hidden" onchange="handleFileSelected(this.files[0])"/>
              <div class="flex flex-col items-center justify-center space-y-2">
                <span class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center">
                  <span class="material-symbols-outlined text-[26px]">cloud_upload</span>
                </span>
                <div class="text-xs font-bold text-slate-800">
                  <span class="text-emerald-700 hover:underline">Click to browse</span> or drag and drop your file here
                </div>
                <p class="text-[11px] text-slate-500">Supported formats: Microsoft Excel (.xlsx, .xls) or CSV (.csv) up to 10 MB</p>
                <div id="selected-file-badge" class="hidden items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold mt-2">
                  <span class="material-symbols-outlined text-[15px]">description</span>
                  <span id="selected-file-name">filename.csv</span>
                  <span class="text-slate-400 font-normal" id="selected-file-size">(0 KB)</span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-2">
              <span class="text-xs text-slate-500">Ensure columns match the official template structure before uploading.</span>
              <button type="button" id="btn-validate-upload" onclick="uploadAndValidateFile()" disabled class="px-5 py-2.5 rounded-xl bg-button-primary text-white font-bold text-xs hover:bg-button-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-2xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[17px]">fact_check</span>
                <span>Upload & Validate</span>
              </button>
            </div>
          </div>

          <!-- Right 4 Cols: Template Info & Download Box -->
          <div class="lg:col-span-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4 text-xs">
            <div class="flex items-center gap-2 font-bold text-slate-900">
              <span class="material-symbols-outlined text-emerald-700 text-[20px]">download</span>
              <span>Sample Template</span>
            </div>
            <p class="text-slate-600 leading-relaxed">
              Download our pre-formatted CSV template with sample student records and standard headers.
            </p>
            <div class="space-y-1.5 text-[11px] text-slate-500">
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-emerald-600 text-[14px]">check_circle</span>
                <span>Required: First Name, DOB, Gender, Guardian, Contact Number</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-emerald-600 text-[14px]">check_circle</span>
                <span>Admission No auto-generated if left blank</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-emerald-600 text-[14px]">check_circle</span>
                <span>DOB formats: YYYY-MM-DD or DD-MM-YYYY</span>
              </div>
            </div>
            <a href="<?php echo site_url('students/bulk_template'); ?>" download class="w-full py-2.5 px-4 rounded-xl border border-emerald-700 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-xs transition-colors flex items-center justify-center gap-2">
              <span class="material-symbols-outlined text-[16px]">file_download</span>
              <span>Download Sample CSV</span>
            </a>
          </div>

        </div>

        <!-- Validation & Preview Results Section (Hidden until file validated) -->
        <div id="validation-results-container" class="hidden space-y-4 pt-4 border-t border-slate-200 max-w-full">
          
          <!-- Validation Success Notification Banner -->
          <div id="validation-success-alert" data-testid="validation-success-alert" class="hidden items-center justify-between gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-2xs transition-all scroll-mt-24 max-w-full" role="status" aria-live="polite">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="material-symbols-outlined text-[20px] text-emerald-700 shrink-0">check_circle</span>
              <span id="validation-success-message" class="break-words">File uploaded successfully. Validation completed.</span>
            </div>
            <button type="button" onclick="dismissValidationSuccessAlert()" class="text-emerald-700 hover:text-emerald-950 p-1 rounded-lg hover:bg-emerald-100/70 transition-colors shrink-0" aria-label="Dismiss notification">
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>

          <!-- Summary Banner -->
          <div id="validation-summary" data-testid="validation-summary" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200 scroll-mt-24 max-w-full">
            <div class="flex items-center gap-3">
              <span class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">analytics</span>
              </span>
              <div>
                <div class="font-bold text-xs text-slate-900 flex items-center gap-2">
                  <span>Validation Summary:</span>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-800" id="val-summary-total">0 Total Rows</span>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800" id="val-summary-valid">0 Valid</span>
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800" id="val-summary-errors">0 Errors</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5" id="val-summary-desc">Review records below before committing to database.</p>
              </div>
            </div>

            <!-- Import Action Controls -->
            <div class="flex items-center gap-2 shrink-0">
              <button type="button" onclick="downloadErrorReport()" id="btn-download-error-report" class="hidden px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100 text-xs font-bold transition-colors">
                Download Error Report
              </button>
              <button type="button" onclick="confirmAndExecuteImport()" id="btn-execute-import" class="px-5 py-2 rounded-xl bg-button-primary hover:bg-button-primary-hover text-white font-bold text-xs transition-colors shadow-2xs flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[17px]">cloud_done</span>
                <span id="btn-execute-import-label">Import Valid Students</span>
              </button>
            </div>
          </div>

          <!-- Preview Table -->
          <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-[420px] overflow-y-auto">
            <table class="w-full text-left text-xs">
              <thead class="sticky top-0 z-10 bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                  <th class="p-3 pl-4">#</th>
                  <th class="p-3">Status</th>
                  <th class="p-3">Student Name</th>
                  <th class="p-3">DOB</th>
                  <th class="p-3">Gender</th>
                  <th class="p-3">Blood</th>
                  <th class="p-3">Guardian</th>
                  <th class="p-3">Contact <span class="text-rose-600">*</span></th>
                  <th class="p-3">Adm No.</th>
                  <th class="p-3">Validation Notes</th>
                  <th class="p-3 pr-4 text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="validation-preview-tbody" class="divide-y divide-slate-100 bg-white">
                <!-- Dynamically populated via AJAX validation -->
              </tbody>
            </table>
          </div>

        </div>

      </div>

      <!-- =====================================================================
           TAB 2: SPREADSHEET-STYLE BULK ENTRY TABLE
           ===================================================================== -->
      <div id="bulk-tab-entry" class="hidden space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-100">
          <div>
            <h4 class="font-bold text-xs text-slate-900">Direct Spreadsheet Student Entry</h4>
            <p class="text-[11px] text-slate-500">Fill in the student details below. Leave Admission No blank to auto-generate sequentially.</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" onclick="addBulkEntryRows(1)" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">add</span>
              <span>+1 Row</span>
            </button>
            <button type="button" onclick="addBulkEntryRows(5)" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">add_box</span>
              <span>+5 Rows</span>
            </button>
            <button type="button" onclick="clearAllBulkEntryRows()" class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-xs font-bold text-rose-800 shadow-2xs">
              Clear All
            </button>
          </div>
        </div>

        <!-- Spreadsheet Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-[500px] overflow-y-auto">
          <table class="w-full text-left text-xs min-w-[1000px]">
            <thead class="sticky top-0 z-10 bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
              <tr>
                <th class="p-2.5 pl-3 w-10 text-center">#</th>
                <th class="p-2.5 min-w-[140px]">First Name <span class="text-rose-600">*</span></th>
                <th class="p-2.5 min-w-[120px]">Last Name</th>
                <th class="p-2.5 min-w-[130px]">DOB <span class="text-rose-600">*</span></th>
                <th class="p-2.5 min-w-[90px]">Gender <span class="text-rose-600">*</span></th>
                <th class="p-2.5 min-w-[80px]">Blood</th>
                <th class="p-2.5 min-w-[140px]">Parent/Guardian <span class="text-rose-600">*</span></th>
                <th class="p-2.5 min-w-[100px]">Relation</th>
                <th class="p-2.5 min-w-[120px]">Contact <span class="text-rose-600">*</span></th>
                <th class="p-2.5 min-w-[130px]">Admission No.</th>
                <th class="p-2.5 min-w-[70px]">Roll No</th>
                <th class="p-2.5 pr-3 w-10 text-center">Action</th>
              </tr>
            </thead>
            <tbody id="bulk-entry-tbody" class="divide-y divide-slate-100 bg-white">
              <!-- Initial 5 editable rows generated by JS -->
            </tbody>
          </table>
        </div>

        <!-- Entry Footer & Submit Button -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
          <span class="text-xs text-slate-500" id="entry-rows-count-indicator">Total: 5 student rows</span>
          <button type="button" onclick="submitBulkEntryForm()" id="btn-save-bulk-entry" class="px-6 py-2.5 rounded-xl bg-button-primary hover:bg-button-primary-hover text-white font-bold text-xs transition-colors shadow-2xs flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span>Save All Students</span>
          </button>
        </div>

      </div>

    </div>
  </div>

</div>

<!-- =========================================================================
     SUCCESS RESULT MODAL
     ========================================================================= -->
<div id="bulk-success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 hidden">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 text-center space-y-4">
    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto shadow-2xs">
      <span class="material-symbols-outlined text-[32px]">check_circle</span>
    </div>
    
    <div>
      <h3 class="font-bold text-lg text-slate-900" id="success-modal-title">Students Added Successfully</h3>
      <p class="text-xs text-slate-500 mt-1" id="success-modal-desc">
        All valid student profiles were created and enrolled.
      </p>
    </div>

    <!-- Summary Box -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-left space-y-2 font-medium">
      <div class="flex justify-between">
        <span class="text-slate-500">Successfully Added:</span>
        <span class="font-bold text-emerald-800 font-mono text-sm" id="success-modal-count">0</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Academic Year:</span>
        <span class="font-bold text-slate-800" id="success-modal-year">-</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Class & Division:</span>
        <span class="font-bold text-slate-800" id="success-modal-class-sec">-</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-2 gap-3 pt-2">
      <button type="button" onclick="location.reload()" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs">
        Add More Students
      </button>
      <a href="<?php echo site_url('students/all_students'); ?>" id="success-modal-view-all-link" class="w-full py-2.5 px-4 rounded-xl bg-button-primary hover:bg-button-primary-hover text-white font-bold text-xs shadow-2xs flex items-center justify-center">
        View in All Students
      </a>
    </div>
  </div>
</div>

<!-- =========================================================================
     EDIT IMPORTED ROW MODAL
     ========================================================================= -->
<div id="bulk-edit-row-modal" data-testid="bulk-edit-row-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 hidden">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-2xl w-full p-6 space-y-4 max-h-[92vh] overflow-y-auto">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">edit_note</span>
        </span>
        <div>
          <h3 class="font-bold text-sm text-slate-900 leading-tight">Edit Imported Student</h3>
          <p class="text-[11px] text-slate-500 mt-0.5" id="edit-modal-row-indicator">Row Details</p>
        </div>
      </div>
      <button type="button" id="btn-close-edit-modal-x" onclick="closeEditRowModal()" data-testid="btn-close-edit-modal" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" aria-label="Close modal">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Error Alert Box in Modal (hidden by default) -->
    <div id="edit-modal-error-alert" data-testid="edit-modal-error-alert" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
      <div class="flex items-center gap-2 font-bold text-rose-900">
        <span class="material-symbols-outlined text-[17px] text-rose-700">error</span>
        <span>Please correct the following errors:</span>
      </div>
      <ul id="edit-modal-error-list" data-testid="edit-modal-error-list" class="list-disc list-inside text-[11px] text-rose-700 pl-1 space-y-0.5 font-medium"></ul>
    </div>

    <!-- Form Fields Grid -->
    <form id="form-edit-imported-row" onsubmit="event.preventDefault(); saveEditedRow();" class="space-y-3.5 text-xs">
      <input type="hidden" id="edit-row-num" name="row_num" value="" />

      <!-- Row 1: First Name & Middle Name -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-first-name" class="block font-bold text-slate-700 mb-1">
            First Name <span class="text-rose-600">*</span>
          </label>
          <input type="text" id="edit-first-name" name="first_name" required placeholder="First Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
        <div>
          <label for="edit-middle-name" class="block font-semibold text-slate-700 mb-1">
            Middle Name <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
          </label>
          <input type="text" id="edit-middle-name" name="middle_name" placeholder="Middle Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
      </div>

      <!-- Row 2: Last Name & Date of Birth -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-last-name" class="block font-semibold text-slate-700 mb-1">
            Last Name <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
          </label>
          <input type="text" id="edit-last-name" name="last_name" placeholder="Last Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
        <div>
          <label for="edit-date-of-birth" class="block font-bold text-slate-700 mb-1">
            Date of Birth <span class="text-rose-600">*</span>
          </label>
          <input type="date" id="edit-date-of-birth" name="date_of_birth" required max="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-medium focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
      </div>

      <!-- Row 3: Gender & Blood Group -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-gender" class="block font-bold text-slate-700 mb-1">
            Gender <span class="text-rose-600">*</span>
          </label>
          <select id="edit-gender" name="gender" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label for="edit-blood-group" class="block font-semibold text-slate-700 mb-1">
            Blood Group <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
          </label>
          <select id="edit-blood-group" name="blood_group" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            <option value="">- Select Blood Group -</option>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
          </select>
        </div>
      </div>

      <!-- Row 4: Guardian Name & Guardian Relation -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-guardian-name" class="block font-bold text-slate-700 mb-1">
            Guardian Name <span class="text-rose-600">*</span>
          </label>
          <input type="text" id="edit-guardian-name" name="guardian_name" required placeholder="Parent / Guardian Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-semibold focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
        <div>
          <label for="edit-guardian-relation" class="block font-semibold text-slate-700 mb-1">
            Guardian Relation
          </label>
          <select id="edit-guardian-relation" name="guardian_relation" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            <option value="Father">Father</option>
            <option value="Mother">Mother</option>
            <option value="Guardian">Guardian</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <!-- Row 5: Guardian Phone & Guardian Email -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-guardian-phone" class="block font-bold text-slate-700 mb-1">
            Guardian Contact <span class="text-rose-600">*</span>
          </label>
          <input type="tel" id="edit-guardian-phone" name="guardian_phone" required placeholder="e.g. 9876543210 or +91 9876543210" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-mono focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
        <div>
          <label for="edit-guardian-email" class="block font-semibold text-slate-700 mb-1">
            Guardian Email <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
          </label>
          <input type="email" id="edit-guardian-email" name="guardian_email" placeholder="guardian@example.com" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
      </div>

      <!-- Row 6: Admission Number & Roll Number -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label for="edit-admission-number" class="block font-semibold text-slate-700 mb-1">
            Admission Number <span class="text-[11px] text-slate-400 font-normal">(Leave blank to auto-generate)</span>
          </label>
          <input type="text" id="edit-admission-number" name="admission_number" placeholder="Auto-generate or enter custom" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-mono font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
        <div>
          <label for="edit-roll-number" class="block font-semibold text-slate-700 mb-1">
            Roll Number <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
          </label>
          <input type="text" id="edit-roll-number" name="roll_number" placeholder="Roll No" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-mono focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
        </div>
      </div>

      <!-- Row 7: Address -->
      <div>
        <label for="edit-address" class="block font-semibold text-slate-700 mb-1">
          Address <span class="text-[11px] text-slate-400 font-normal">(Optional)</span>
        </label>
        <input type="text" id="edit-address" name="address" placeholder="Residential Address / City" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
        <button type="button" id="btn-cancel-edit-row" onclick="closeEditRowModal()" data-testid="btn-cancel-edit-row" class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition-colors">
          Cancel
        </button>
        <button type="button" id="btn-save-edit-row" onclick="saveEditedRow()" data-testid="btn-save-edit-row" class="px-5 py-2 rounded-xl bg-button-primary hover:bg-button-primary-hover text-white font-bold text-xs shadow-2xs flex items-center gap-1.5 transition-colors">
          <span class="material-symbols-outlined text-[16px]">check</span>
          <span id="btn-save-edit-row-label">Save Changes</span>
        </button>
      </div>

    </form>
  </div>
</div>

<!-- =========================================================================
     CLIENT-SIDE JAVASCRIPT LOGIC
     ========================================================================= -->
<script>
  let currentFile = null;
  let validatedResultCache = null;

  document.addEventListener('DOMContentLoaded', () => {
    // Initialise 5 rows in Bulk Entry table
    addBulkEntryRows(5);

    // Setup drag & drop
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
      ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
          e.preventDefault(); e.stopPropagation();
          dropZone.classList.add('border-emerald-600', 'bg-emerald-50/40');
        }, false);
      });
      ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
          e.preventDefault(); e.stopPropagation();
          dropZone.classList.remove('border-emerald-600', 'bg-emerald-50/40');
        }, false);
      });
      dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
          handleFileSelected(dt.files[0]);
        }
      });
    }
  });

  // Switch between Import and Entry tabs
  function switchBulkTab(tab) {
    const btnImport = document.getElementById('tab-btn-import');
    const btnEntry  = document.getElementById('tab-btn-entry');
    const viewImport = document.getElementById('bulk-tab-import');
    const viewEntry  = document.getElementById('bulk-tab-entry');

    if (tab === 'import') {
      btnImport.className = "flex items-center gap-2 px-5 py-3 text-xs font-bold border-b-2 border-emerald-700 text-emerald-800 bg-white rounded-t-xl transition-all shadow-2xs";
      btnEntry.className  = "flex items-center gap-2 px-5 py-3 text-xs font-semibold text-slate-600 hover:text-slate-900 border-b-2 border-transparent transition-all";
      viewImport.classList.remove('hidden');
      viewEntry.classList.add('hidden');
    } else {
      btnEntry.className  = "flex items-center gap-2 px-5 py-3 text-xs font-bold border-b-2 border-emerald-700 text-emerald-800 bg-white rounded-t-xl transition-all shadow-2xs";
      btnImport.className = "flex items-center gap-2 px-5 py-3 text-xs font-semibold text-slate-600 hover:text-slate-900 border-b-2 border-transparent transition-all";
      viewEntry.classList.remove('hidden');
      viewImport.classList.add('hidden');
    }
  }

  // Academic Year Cascade
  function onBulkYearChanged(yearId) {
    if (!yearId) return;
    $.ajax({
      url: '<?php echo site_url('students/get_classes_ajax'); ?>',
      type: 'GET',
      data: { academic_year_id: yearId },
      dataType: 'json',
      success: function(res) {
        if (res && res.classes) {
          let html = '<option value="">-- Select Class --</option>';
          res.classes.forEach(c => {
            html += `<option value="${c.class_id}">${escapeHtml(c.class_name)}</option>`;
          });
          $('#bulk-class-id').html(html);
          if (res.classes.length > 0) {
            $('#bulk-class-id').val(res.classes[0].class_id);
            onBulkClassChanged(res.classes[0].class_id);
          } else {
            $('#bulk-section-id').html('<option value="12" selected>Division A (Default)</option>');
          }
        }
      }
    });
  }

  // Class Cascade (Adheres strictly to Division A default rule)
  function onBulkClassChanged(classId) {
    if (!classId) {
      $('#bulk-section-id').html('<option value="12" selected>Division A (Default)</option>');
      return;
    }
    $.ajax({
      url: '<?php echo site_url('students/get_divisions_ajax'); ?>',
      type: 'GET',
      data: { class_id: classId },
      dataType: 'json',
      success: function(res) {
        let secHtml = '';
        if (res && ((res.divisions && res.divisions.length > 0) || (res.sections && res.sections.length > 0))) {
          const list = res.divisions || res.sections;
          list.forEach((sec, idx) => {
            const divId = sec.division_id || sec.section_id;
            const divName = sec.division_name || sec.section_name;
            secHtml += `<option value="${divId}" ${idx === 0 ? 'selected' : ''}>Division ${escapeHtml(divName)}</option>`;
          });
        } else {
          secHtml = '<option value="12" selected>Division A (Default)</option>';
        }
        $('#bulk-section-id').html(secHtml);
      },
      error: function() {
        $('#bulk-section-id').html('<option value="12" selected>Division A (Default)</option>');
      }
    });
  }

  let isValidationInProgress = false;

  // File selection handler
  function handleFileSelected(file) {
    if (!file) return;
    currentFile = file;
    document.getElementById('selected-file-name').textContent = file.name;
    document.getElementById('selected-file-size').textContent = `(${(file.size / 1024).toFixed(1)} KB)`;
    document.getElementById('selected-file-badge').classList.remove('hidden');
    document.getElementById('selected-file-badge').classList.add('inline-flex');
    document.getElementById('btn-validate-upload').disabled = false;
    hideValidationSuccessAlert();
  }

  // Notification handling
  function showValidationSuccessAlert(message) {
    const alertEl = document.getElementById('validation-success-alert');
    const msgEl = document.getElementById('validation-success-message');
    if (msgEl) {
      msgEl.textContent = message || 'File uploaded successfully. Validation completed.';
    }
    if (alertEl) {
      alertEl.classList.remove('hidden');
      alertEl.classList.add('flex');
    }
  }

  function hideValidationSuccessAlert() {
    const alertEl = document.getElementById('validation-success-alert');
    if (alertEl) {
      alertEl.classList.add('hidden');
      alertEl.classList.remove('flex');
    }
  }

  function dismissValidationSuccessAlert() {
    hideValidationSuccessAlert();
  }

  // Smooth scroll to Validation Summary with dynamic sticky-header offset
  function scrollToValidationSummary() {
    const summaryEl = document.getElementById('validation-summary') || document.getElementById('validation-results-container');
    if (!summaryEl) return;

    const alertEl = document.getElementById('validation-success-alert');
    const isAlertVisible = alertEl && !alertEl.classList.contains('hidden') && alertEl.offsetHeight > 0;
    
    // Choose scroll target: if alert is active, scroll to alert so both alert & summary are in viewport
    const scrollTarget = isAlertVisible ? alertEl : summaryEl;

    // Dynamically query the application sticky header
    const header = document.querySelector('header[data-testid="app-header"]') || document.querySelector('header');
    const headerHeight = (header && typeof header.offsetHeight === 'number' && header.offsetHeight > 0) ? header.offsetHeight : 64;

    const breathingPadding = 16;
    const targetRect = scrollTarget.getBoundingClientRect();
    const targetTop = targetRect.top + window.pageYOffset;
    const scrollY = Math.max(0, targetTop - headerHeight - breathingPadding);

    try {
      window.scrollTo({
        top: scrollY,
        behavior: 'smooth'
      });
    } catch (e) {
      window.scrollTo(0, scrollY);
    }
  }

  // Upload & Validate File AJAX
  function uploadAndValidateFile() {
    if (isValidationInProgress) return;

    const yearId = $('#bulk-academic-year').val();
    const classId = $('#bulk-class-id').val();
    const secId   = $('#bulk-section-id').val();

    if (!classId) {
      hideValidationSuccessAlert();
      alert('Please select a Class / Grade first.');
      return;
    }
    if (!currentFile) {
      hideValidationSuccessAlert();
      alert('Please choose a file to upload.');
      return;
    }

    isValidationInProgress = true;
    const btn = document.getElementById('btn-validate-upload');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[17px]">progress_activity</span> Validating...';

    // Hide previous notification while processing new upload
    hideValidationSuccessAlert();

    const formData = new FormData();
    formData.append('academic_year_id', yearId);
    formData.append('class_id', classId);
    formData.append('division_id', secId);
    formData.append('section_id', secId);
    formData.append('import_file', currentFile);
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    }

    $.ajax({
      url: '<?php echo site_url('students/bulk_validate_ajax'); ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        isValidationInProgress = false;
        btn.disabled = false;
        btn.innerHTML = origText;
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res && res.status) {
          validatedResultCache = res;
          renderValidationResults(res);
          showValidationSuccessAlert('File uploaded successfully. Validation completed.');
          // Ensure DOM has laid out the newly rendered elements before smooth scrolling
          if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(function() {
              scrollToValidationSummary();
            });
          } else {
            scrollToValidationSummary();
          }
        } else {
          hideValidationSuccessAlert();
          alert(res ? res.message : 'Error validating file.');
        }
      },
      error: function(xhr, status, err) {
        isValidationInProgress = false;
        btn.disabled = false;
        btn.innerHTML = origText;
        hideValidationSuccessAlert();
        alert('Server error while parsing file: ' + err);
      }
    });
  }

  // Render Validation Table
  function renderValidationResults(data) {
    document.getElementById('val-summary-total').textContent = `${data.total_count} Total Rows`;
    document.getElementById('val-summary-valid').textContent = `${data.valid_count} Valid`;
    document.getElementById('val-summary-errors').textContent = `${data.error_count} Errors`;

    const errReportBtn = document.getElementById('btn-download-error-report');
    if (data.error_count > 0) {
      errReportBtn.classList.remove('hidden');
    } else {
      errReportBtn.classList.add('hidden');
    }

    const execBtn = document.getElementById('btn-execute-import');
    if (data.valid_count > 0) {
      execBtn.disabled = false;
      document.getElementById('btn-execute-import-label').textContent = `Import ${data.valid_count} Valid Students`;
    } else {
      execBtn.disabled = true;
      document.getElementById('btn-execute-import-label').textContent = `No Valid Rows to Import`;
    }

    let tbodyHtml = '';
    data.rows.forEach(r => {
      const statusBadge = r.is_valid
        ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Valid</span>'
        : '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">Error</span>';
      
      const errorNotes = r.errors && r.errors.length > 0
        ? `<div class="text-rose-700 font-semibold text-[11px]">${r.errors.map(e => `• ${escapeHtml(e)}`).join('<br>')}</div>`
        : '<span class="text-slate-400">—</span>';

      const contactDisplay = (r.guardian_phone && r.guardian_phone !== '—' && r.guardian_phone !== '-')
        ? escapeHtml(r.guardian_phone)
        : '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Missing</span>';

      tbodyHtml += `
        <tr class="hover:bg-slate-50/80 ${!r.is_valid ? 'bg-rose-50/30' : ''}" id="val-row-${r.row_num}" data-row-num="${r.row_num}">
          <td class="p-3 pl-4 font-mono font-bold text-slate-500">${r.row_num}</td>
          <td class="p-3">${statusBadge}</td>
          <td class="p-3 font-bold text-slate-900">${escapeHtml(r.full_name)}</td>
          <td class="p-3 font-mono text-slate-700">${escapeHtml(r.date_of_birth || '—')}</td>
          <td class="p-3">${escapeHtml(r.gender)}</td>
          <td class="p-3 font-semibold">${escapeHtml(r.blood_group || '—')}</td>
          <td class="p-3">${escapeHtml(r.guardian_name)}</td>
          <td class="p-3 font-mono">${contactDisplay}</td>
          <td class="p-3 font-mono font-bold text-emerald-800">${escapeHtml(r.admission_number)}</td>
          <td class="p-3">${errorNotes}</td>
          <td class="p-3 pr-4 text-center">
            <button type="button" onclick="openEditRowModal(${r.row_num})" data-testid="btn-edit-row-${r.row_num}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 hover:text-emerald-800 text-[11px] font-bold shadow-2xs transition-colors">
              <span class="material-symbols-outlined text-[14px]">edit</span>
              <span>Edit</span>
            </button>
          </td>
        </tr>
      `;
    });

    $('#validation-preview-tbody').html(tbodyHtml);
    document.getElementById('validation-results-container').classList.remove('hidden');
  }

  // =========================================================================
  // EDIT IMPORTED ROW MODAL INTERACTIVITY
  // =========================================================================
  let currentEditingRowNum = null;

  function openEditRowModal(rowNum) {
    if (!validatedResultCache || !validatedResultCache.rows) return;
    const row = validatedResultCache.rows.find(r => (parseInt(r.row_num, 10) === parseInt(rowNum, 10)));
    if (!row) return;

    currentEditingRowNum = parseInt(rowNum, 10);
    document.getElementById('edit-row-num').value = rowNum;
    document.getElementById('edit-modal-row-indicator').textContent = `Row #${rowNum} — ${escapeHtml(row.full_name || 'Student Details')}`;

    // Populate form fields with row's current values
    document.getElementById('edit-first-name').value = row.first_name || '';
    document.getElementById('edit-middle-name').value = row.middle_name || '';
    document.getElementById('edit-last-name').value = row.last_name || '';

    // Date of birth: if DD-MM-YYYY or DD/MM/YYYY convert to YYYY-MM-DD for native date input
    let dobVal = row.date_of_birth || '';
    if (dobVal) {
      if (dobVal.match(/^\d{2}-\d{2}-\d{4}$/)) {
        const p = dobVal.split('-');
        dobVal = `${p[2]}-${p[1]}-${p[0]}`;
      } else if (dobVal.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        const p = dobVal.split('/');
        dobVal = `${p[2]}-${p[1]}-${p[0]}`;
      }
    }
    document.getElementById('edit-date-of-birth').value = dobVal;

    document.getElementById('edit-gender').value = row.gender || 'Male';
    document.getElementById('edit-blood-group').value = row.blood_group || '';
    document.getElementById('edit-guardian-name').value = row.guardian_name || '';
    document.getElementById('edit-guardian-relation').value = row.guardian_relation || 'Father';

    const phoneVal = (row.guardian_phone && row.guardian_phone !== '—' && row.guardian_phone !== '-') ? row.guardian_phone : '';
    document.getElementById('edit-guardian-phone').value = phoneVal;

    document.getElementById('edit-guardian-email').value = row.guardian_email || '';
    document.getElementById('edit-address').value = row.address || '';

    const admVal = (row.admission_number && row.admission_number !== '(Auto-generate)') ? row.admission_number : '';
    document.getElementById('edit-admission-number').value = admVal;
    document.getElementById('edit-roll-number').value = row.roll_number || '';

    // Clear and hide errors
    const errorAlert = document.getElementById('edit-modal-error-alert');
    const errorList = document.getElementById('edit-modal-error-list');
    errorList.innerHTML = '';
    errorAlert.classList.add('hidden');

    // Show modal
    document.getElementById('bulk-edit-row-modal').classList.remove('hidden');
  }

  function closeEditRowModal() {
    currentEditingRowNum = null;
    document.getElementById('bulk-edit-row-modal').classList.add('hidden');
    document.getElementById('edit-modal-error-alert').classList.add('hidden');
  }

  function saveEditedRow() {
    if (!currentEditingRowNum) return;
    const rowNum = currentEditingRowNum;

    const fn   = document.getElementById('edit-first-name').value.trim();
    const mn   = document.getElementById('edit-middle-name').value.trim();
    const ln   = document.getElementById('edit-last-name').value.trim();
    const dob  = document.getElementById('edit-date-of-birth').value.trim();
    const g    = document.getElementById('edit-gender').value;
    const bg   = document.getElementById('edit-blood-group').value;
    const gn   = document.getElementById('edit-guardian-name').value.trim();
    const gr   = document.getElementById('edit-guardian-relation').value;
    const gp   = document.getElementById('edit-guardian-phone').value.trim();
    const ge   = document.getElementById('edit-guardian-email').value.trim();
    const addr = document.getElementById('edit-address').value.trim();
    const adm  = document.getElementById('edit-admission-number').value.trim();
    const roll = document.getElementById('edit-roll-number').value.trim();

    // Client-side quick check
    const clientErrors = [];
    if (!fn) clientErrors.push('First Name is required.');
    if (!dob) clientErrors.push('Date of Birth is required.');
    if (!gn) clientErrors.push('Guardian Name is required.');
    if (!gp) clientErrors.push('Parent/Guardian Contact Number is required.');

    const errorAlert = document.getElementById('edit-modal-error-alert');
    const errorList = document.getElementById('edit-modal-error-list');

    if (clientErrors.length > 0) {
      errorList.innerHTML = clientErrors.map(e => `<li>${escapeHtml(e)}</li>`).join('');
      errorAlert.classList.remove('hidden');
      return;
    }

    const saveBtn = document.getElementById('btn-save-edit-row');
    const origText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span> Validating...';

    const yearId = $('#bulk-academic-year').val();
    const classId = $('#bulk-class-id').val();
    const secId   = $('#bulk-section-id').val();

    const postData = {
      row_num: rowNum,
      academic_year_id: yearId,
      class_id: classId,
      division_id: secId,
      section_id: secId,
      first_name: fn,
      middle_name: mn,
      last_name: ln,
      date_of_birth: dob,
      gender: g,
      blood_group: bg,
      guardian_name: gn,
      guardian_relation: gr,
      guardian_phone: gp,
      guardian_email: ge,
      address: addr,
      admission_number: adm,
      roll_number: roll,
      all_rows: JSON.stringify(validatedResultCache.rows)
    };

    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url('students/bulk_revalidate_row_ajax'); ?>',
      type: 'POST',
      data: postData,
      dataType: 'json',
      success: function(res) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = origText;
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res && res.status && res.is_valid) {
          // Revalidation succeeded! Update client-side dataset
          if (res.rows) {
            validatedResultCache.rows = res.rows;
            validatedResultCache.total_count = res.total_count;
            validatedResultCache.valid_count = res.valid_count;
            validatedResultCache.error_count = res.error_count;
          } else if (res.updated_row) {
            const idx = validatedResultCache.rows.findIndex(r => r.row_num === rowNum);
            if (idx !== -1) validatedResultCache.rows[idx] = res.updated_row;
            validatedResultCache.valid_count = validatedResultCache.rows.filter(r => r.is_valid).length;
            validatedResultCache.error_count = validatedResultCache.rows.filter(r => !r.is_valid).length;
          }

          // Close modal
          closeEditRowModal();

          // Refresh the validation summary table, chips, and import button
          renderValidationResults(validatedResultCache);
        } else {
          // Validation failed: Keep modal OPEN and display errors
          const errs = (res && res.errors && res.errors.length > 0) ? res.errors : [res ? res.message : 'Validation failed for edited row.'];
          errorList.innerHTML = errs.map(e => `<li>${escapeHtml(e)}</li>`).join('');
          errorAlert.classList.remove('hidden');
        }
      },
      error: function(xhr, status, err) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = origText;
        errorList.innerHTML = `<li>Server error during validation: ${escapeHtml(err)}</li>`;
        errorAlert.classList.remove('hidden');
      }
    });
  }

  // Execute Import of Valid Rows
  function confirmAndExecuteImport() {
    if (!validatedResultCache || validatedResultCache.valid_count <= 0) {
      alert('No valid student rows to import.');
      return;
    }

    const yearId = $('#bulk-academic-year').val();
    const classId = $('#bulk-class-id').val();
    const secId   = $('#bulk-section-id').val();

    if (!confirm(`Are you sure you want to import ${validatedResultCache.valid_count} valid student(s)?`)) {
      return;
    }

    const btn = document.getElementById('btn-execute-import');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[17px]">progress_activity</span> Importing...';

    const postData = {
      academic_year_id: yearId,
      class_id: classId,
      division_id: secId,
      section_id: secId
    };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url('students/bulk_import_ajax'); ?>',
      type: 'POST',
      data: postData,
      dataType: 'json',
      success: function(res) {
        btn.disabled = false;
        btn.innerHTML = origText;
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res && res.status) {
          showSuccessModal(res.inserted_count, $('#bulk-academic-year option:selected').text(), $('#bulk-class-id option:selected').text() + ' - ' + $('#bulk-section-id option:selected').text(), classId, yearId);
        } else {
          alert(res ? res.message : 'Error during bulk import.');
        }
      },
      error: function(xhr, status, err) {
        btn.disabled = false;
        btn.innerHTML = origText;
        alert('Server error during import: ' + err);
      }
    });
  }

  // Download Error Report CSV
  function downloadErrorReport() {
    if (!validatedResultCache || !validatedResultCache.rows) return;
    const errorRows = validatedResultCache.rows.filter(r => !r.is_valid);
    if (errorRows.length === 0) return;

    let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
    csvContent += "Row,First Name,Middle Name,Last Name,DOB,Gender,Blood Group,Guardian Name,Guardian Phone,Admission Number,Errors\n";

    errorRows.forEach(r => {
      const line = [
        r.row_num,
        `"${(r.first_name || '').replace(/"/g, '""')}"`,
        `"${(r.middle_name || '').replace(/"/g, '""')}"`,
        `"${(r.last_name || '').replace(/"/g, '""')}"`,
        `"${(r.date_of_birth || '')}"`,
        `"${(r.gender || '')}"`,
        `"${(r.blood_group || '')}"`,
        `"${(r.guardian_name || '').replace(/"/g, '""')}"`,
        `"${(r.guardian_phone || '')}"`,
        `"${(r.admission_number || '')}"`,
        `"${(r.errors || []).join('; ').replace(/"/g, '""')}"`
      ].join(',');
      csvContent += line + "\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `bulk_student_errors_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // =========================================================================
  // TAB 2: SPREADSHEET BULK ENTRY JAVASCRIPT
  // =========================================================================
  let bulkEntryRowIndex = 0;

  function addBulkEntryRows(count) {
    const tbody = document.getElementById('bulk-entry-tbody');
    for (let i = 0; i < count; i++) {
      bulkEntryRowIndex++;
      const rowId = `entry-row-${bulkEntryRowIndex}`;
      const tr = document.createElement('tr');
      tr.id = rowId;
      tr.className = 'hover:bg-slate-50/80 transition-colors';
      tr.innerHTML = `
        <td class="p-2 pl-3 font-mono font-bold text-slate-400 text-center text-xs row-index-cell">${tbody.children.length + 1}</td>
        <td class="p-1.5"><input type="text" name="first_name" placeholder="First Name *" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 font-medium"/></td>
        <td class="p-1.5"><input type="text" name="last_name" placeholder="Last Name" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5"><input type="date" name="date_of_birth" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5">
          <select name="gender" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600">
            <option value="Male" selected>Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </td>
        <td class="p-1.5">
          <select name="blood_group" class="w-full px-1.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600">
            <option value="">-</option>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
          </select>
        </td>
        <td class="p-1.5"><input type="text" name="guardian_name" placeholder="Parent / Guardian *" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5">
          <select name="guardian_relation" class="w-full px-1.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600">
            <option value="Father" selected>Father</option>
            <option value="Mother">Mother</option>
            <option value="Guardian">Guardian</option>
          </select>
        </td>
        <td class="p-1.5"><input type="text" name="guardian_phone" placeholder="Phone *" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5"><input type="text" name="admission_number" placeholder="Auto-generate" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono text-emerald-800 placeholder-slate-400 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5"><input type="text" name="roll_number" placeholder="Roll #" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono text-center focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600"/></td>
        <td class="p-1.5 text-center pr-3">
          <button type="button" onclick="removeBulkEntryRow('${rowId}')" title="Delete Row" class="p-1 text-slate-400 hover:text-rose-600 transition-colors">
            <span class="material-symbols-outlined text-[18px]">delete</span>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    }
    updateBulkEntryCounts();
  }

  function removeBulkEntryRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
      row.remove();
      updateBulkEntryCounts();
    }
  }

  function clearAllBulkEntryRows() {
    if (confirm('Clear all entered student rows?')) {
      document.getElementById('bulk-entry-tbody').innerHTML = '';
      bulkEntryRowIndex = 0;
      addBulkEntryRows(5);
    }
  }

  function updateBulkEntryCounts() {
    const count = document.getElementById('bulk-entry-tbody').children.length;
    document.getElementById('entry-rows-count-indicator').textContent = `Total: ${count} student rows`;
    // Re-index cell labels
    const cells = document.querySelectorAll('.row-index-cell');
    cells.forEach((c, idx) => { c.textContent = idx + 1; });
  }

  function submitBulkEntryForm() {
    const yearId = $('#bulk-academic-year').val();
    const classId = $('#bulk-class-id').val();
    const secId   = $('#bulk-section-id').val();

    if (!classId) {
      alert('Please select a Class / Grade first.');
      return;
    }

    const rows = document.getElementById('bulk-entry-tbody').children;
    const entries = [];
    let hasClientErrors = false;
    let clientErrorMessages = [];

    // Reset error styling on all rows and inputs
    for (let i = 0; i < rows.length; i++) {
      const tr = rows[i];
      tr.classList.remove('bg-rose-50/40');
      tr.querySelectorAll('input, select').forEach(el => {
        el.classList.remove('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
      });
    }

    for (let i = 0; i < rows.length; i++) {
      const tr = rows[i];
      const fnInput = tr.querySelector('input[name="first_name"]');
      const gpInput = tr.querySelector('input[name="guardian_phone"]');
      const dobInput = tr.querySelector('input[name="date_of_birth"]');
      const gnInput = tr.querySelector('input[name="guardian_name"]');

      const fn = fnInput.value.trim();
      const ln = tr.querySelector('input[name="last_name"]').value.trim();
      const dob = dobInput.value.trim();
      const g = tr.querySelector('select[name="gender"]').value;
      const bg = tr.querySelector('select[name="blood_group"]').value;
      const gn = gnInput.value.trim();
      const gr = tr.querySelector('select[name="guardian_relation"]').value;
      const gp = gpInput.value.trim();
      const adm = tr.querySelector('input[name="admission_number"]').value.trim();
      const roll = tr.querySelector('input[name="roll_number"]').value.trim();

      // Check if this row is filled/active
      if (fn || ln || gn || adm || gp || dob) {
        let rowErrors = [];

        if (!fn) {
          fnInput.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
          rowErrors.push('First Name is required');
        }
        if (!gp) {
          gpInput.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
          rowErrors.push('Parent/Guardian Contact Number is required');
        } else {
          const digits = gp.replace(/\D/g, '');
          if (!gp.startsWith('+') && digits.length === 10) {
            if (!['6','7','8','9'].includes(digits.charAt(0)) || /^(\d)\1{9}$/.test(digits)) {
              gpInput.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
              rowErrors.push('Enter a valid 10-digit Indian mobile number');
            }
          }
        }
        if (!dob) {
          dobInput.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
          rowErrors.push('Date of Birth is required');
        }

        if (rowErrors.length > 0) {
          hasClientErrors = true;
          tr.classList.add('bg-rose-50/40');
          clientErrorMessages.push(`Row ${i + 1}: ${rowErrors.join(', ')}`);
        }

        entries.push({
          first_name: fn,
          last_name: ln,
          date_of_birth: dob,
          gender: g,
          blood_group: bg,
          guardian_name: gn,
          guardian_relation: gr,
          guardian_phone: gp,
          admission_number: adm,
          roll_number: roll
        });
      }
    }

    if (entries.length === 0) {
      alert('Please enter details for at least one student.');
      return;
    }

    if (hasClientErrors) {
      alert('Please fix the following validation error(s):\n\n' + clientErrorMessages.join('\n'));
      return;
    }

    const btn = document.getElementById('btn-save-bulk-entry');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span> Saving Students...';

    const postData = {
      academic_year_id: yearId,
      class_id: classId,
      division_id: secId,
      section_id: secId,
      entries: entries
    };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url('students/bulk_entry_save_ajax'); ?>',
      type: 'POST',
      data: postData,
      dataType: 'json',
      success: function(res) {
        btn.disabled = false;
        btn.innerHTML = origText;
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res && res.status) {
          showSuccessModal(res.inserted_count, $('#bulk-academic-year option:selected').text(), $('#bulk-class-id option:selected').text() + ' - ' + $('#bulk-section-id option:selected').text(), classId, yearId);
        } else {
          if (res && res.rows && res.error_count > 0) {
            let errMsg = `Validation failed on ${res.error_count} row(s):\n\n`;
            res.rows.forEach(r => {
              if (!r.is_valid) {
                errMsg += `Row ${r.row_num}: ${r.errors.join(', ')}\n`;
                const targetRow = rows[r.row_num - 1];
                if (targetRow) {
                  targetRow.classList.add('bg-rose-50/40');
                  if (r.errors.some(e => e.includes('Contact Number'))) {
                    const gField = targetRow.querySelector('input[name="guardian_phone"]');
                    if (gField) gField.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
                  }
                  if (r.errors.some(e => e.includes('First Name'))) {
                    const fnField = targetRow.querySelector('input[name="first_name"]');
                    if (fnField) fnField.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
                  }
                  if (r.errors.some(e => e.includes('Date of Birth'))) {
                    const dobField = targetRow.querySelector('input[name="date_of_birth"]');
                    if (dobField) dobField.classList.add('!border-rose-500', '!ring-rose-500', '!bg-rose-50/50');
                  }
                }
              }
            });
            alert(errMsg);
          } else {
            alert(res ? res.message : 'Error saving students.');
          }
        }
      },
      error: function(xhr, status, err) {
        btn.disabled = false;
        btn.innerHTML = origText;
        alert('Server error while saving: ' + err);
      }
    });
  }

  function showSuccessModal(count, yearName, classSecName, classId, yearId) {
    document.getElementById('success-modal-count').textContent = count;
    document.getElementById('success-modal-year').textContent = yearName;
    document.getElementById('success-modal-class-sec').textContent = classSecName;
    document.getElementById('success-modal-view-all-link').href = `<?php echo site_url('students/all_students'); ?>?class_id=${classId}&academic_year_id=${yearId}`;
    document.getElementById('bulk-success-modal').classList.remove('hidden');
  }

  function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
  }
</script>
