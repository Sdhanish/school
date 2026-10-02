<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Documents</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Central repository for student birth certificates, TC records, identity cards, and health certificates.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="document.getElementById('upload-doc-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">upload_file</span>Upload Document
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <select onchange="applyFilter('document_type', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Document Types</option>
        <option value="Birth Certificate" <?php echo ($this->input->get('document_type') === 'Birth Certificate') ? 'selected' : ''; ?>>Birth Certificate</option>
        <option value="Aadhaar Card" <?php echo ($this->input->get('document_type') === 'Aadhaar Card') ? 'selected' : ''; ?>>Aadhaar / ID Card</option>
        <option value="Transfer Certificate" <?php echo ($this->input->get('document_type') === 'Transfer Certificate') ? 'selected' : ''; ?>>Transfer Certificate</option>
        <option value="Medical Certificate" <?php echo ($this->input->get('document_type') === 'Medical Certificate') ? 'selected' : ''; ?>>Medical Certificate</option>
      </select>
      <select onchange="applyFilter('class_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Classes</option>
        <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
        <?php endforeach; ?>
      </select>
      <a href="<?php echo site_url('students/documents'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Documents Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Document Title</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Document Type</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Student Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Admission No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Upload Date</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($documents)): ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-body-md text-on-surface-variant">No student documents found.</td>
              </tr>
            <?php endif; ?>
            <?php foreach ($documents as $doc): ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 text-body-md font-medium text-on-surface whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                    <?php echo html_escape($doc->document_name); ?>
                  </div>
                </td>
                <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface"><?php echo html_escape($doc->document_type); ?></span>
                </td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap">
                  <a href="<?php echo site_url('students/profile/' . $doc->student_id); ?>" class="font-medium text-primary hover:underline"><?php echo html_escape($doc->first_name . ' ' . $doc->last_name); ?></a>
                </td>
                <td class="px-4 py-3 text-body-md font-mono text-on-surface-variant whitespace-nowrap"><?php echo html_escape($doc->admission_number); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($doc->class_name . ' ' . $doc->section_name); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap"><?php echo date('d M Y', strtotime($doc->created_at)); ?></td>
                <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="<?php echo base_url($doc->file_path); ?>" target="_blank" class="px-2.5 py-1 rounded bg-surface-container-high text-on-surface text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">visibility</span>View</a>
                    <a href="<?php echo site_url('students/delete_document/' . $doc->document_id . '?redirect_to=' . urlencode(current_url())); ?>" onclick="return confirm('Remove this document record?')" class="px-2.5 py-1 rounded text-error hover:bg-error-container/20 transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">delete</span>Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 border-t border-outline-variant/50 text-body-md font-body-md text-on-surface-variant">
        <span>Total <?php echo count($documents); ?> document(s)</span>
      </div>
    </div>

    <!-- Upload Document Modal -->
    <div id="upload-doc-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">Upload Student Document</h3>
          <button onclick="document.getElementById('upload-doc-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open_multipart('students/upload_document', array('class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="redirect_to" value="<?php echo current_url(); ?>"/>
          <div>
            <label class="block text-label-md mb-1">Select Student *</label>
            <select name="student_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <?php foreach ($students as $st): ?>
                <option value="<?php echo $st->student_id; ?>"><?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">Document Type *</label>
            <select name="document_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="Birth Certificate">Birth Certificate</option>
              <option value="Aadhaar Card">Aadhaar Card / ID Proof</option>
              <option value="Transfer Certificate">Transfer Certificate</option>
              <option value="Medical Certificate">Medical Certificate</option>
              <option value="Previous School TC">Previous School TC</option>
              <option value="Other">Other Document</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">Document Name / Title *</label>
            <input type="text" name="document_name" required placeholder="e.g. Birth Certificate (Official)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>
          <div>
            <label for="modal_document_file" class="block text-label-md mb-1">Select File</label>
            <input type="file" id="modal_document_file" name="document_file" accept="<?php echo student_document_accept_attribute(); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface-variant"/>
            <p class="text-xs text-on-surface-variant mt-1.5" id="modal_document_file_hint"><?php echo student_document_format_notice(); ?></p>
            <p class="text-xs text-error mt-1 hidden font-medium flex items-center gap-1" id="modal_document_file_error" role="alert"></p>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('upload-doc-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Upload Document</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        window.location.href = url.toString();
      }

      document.addEventListener('DOMContentLoaded', function() {
        var mFile = document.getElementById('modal_document_file');
        var mErr = document.getElementById('modal_document_file_error');
        var allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx'];

        if (mFile) {
          mFile.addEventListener('change', function() {
            if (mErr) {
              mErr.textContent = '';
              mErr.classList.add('hidden');
            }
            mFile.classList.remove('!border-error');

            if (!mFile.files || mFile.files.length === 0) return;
            var ext = (mFile.files[0].name || '').split('.').pop().toLowerCase();
            if (allowedExtensions.indexOf(ext) === -1) {
              if (mErr) {
                mErr.textContent = 'Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.';
                mErr.classList.remove('hidden');
              }
              mFile.classList.add('!border-error');
              mFile.value = '';
            }
          });
        }
      });
    </script>
