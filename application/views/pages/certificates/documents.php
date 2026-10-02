<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Document Repository</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage birth certificates, ID cards, transfer certificates, medical records, and verification status.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('certificates/document_verification'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">verified_user</span>Verification Queue
        </a>
        <button onclick="document.getElementById('docModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">upload_file</span>Upload Document
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Category</label>
          <select id="filter_category_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat->category_id; ?>" <?php echo (($filters['category_id'] ?? '') == $cat->category_id) ? 'selected' : ''; ?>><?php echo html_escape($cat->category_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Verification Status</label>
          <select id="filter_verification_status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo (($filters['verification_status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending Verification</option>
            <option value="Verified" <?php echo (($filters['verification_status'] ?? '') === 'Verified') ? 'selected' : ''; ?>>Verified</option>
            <option value="Rejected" <?php echo (($filters['verification_status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Student</label>
          <select id="filter_student_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Students</option>
            <?php foreach ($students as $st): ?>
              <option value="<?php echo $st->student_id; ?>" <?php echo (($filters['student_id'] ?? '') == $st->student_id) ? 'selected' : ''; ?>><?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Documents Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Uploaded Documents (<span id="total-docs-badge"><?php echo (int)($total_documents ?? 0); ?></span>)</span>
      </div>

      <div class="table-scroll overflow-x-auto p-2">
        <table id="student-documents-table" class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Document Title & #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Expiry Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Verification</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">File</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <!-- Loaded dynamically via server-side DataTables -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Document Modal Dialog -->
    <dialog id="docModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Upload Student Document</h3>
          <button onclick="document.getElementById('docModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('certificates/documents'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student *</label>
              <select name="student_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Student --</option>
                <?php foreach ($students as $st): ?>
                  <option value="<?php echo $st->student_id; ?>"><?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category *</label>
                <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat->category_id; ?>"><?php echo html_escape($cat->category_name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Title *</label>
                <input type="text" name="document_name" required placeholder="e.g. Birth Certificate" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Number</label>
                <input type="text" name="document_number" placeholder="Certificate / UID #" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expiry Date (If applicable)</label>
                <input type="date" name="expiry_date" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload File *</label>
              <input type="file" name="document_file" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('docModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Document</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof School !== 'undefined' && School.DataTable) {
        var dt = School.DataTable.serverSide('#student-documents-table', {
          ajaxUrl: '<?php echo site_url("certificates/ajax_documents_list"); ?>',
          order: [[2, 'asc']],
          columns: [
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true, className: 'text-center' },
            { orderable: false, className: 'text-center' }
          ],
          extraData: function(d) {
            d.category_id = document.getElementById('filter_category_id').value;
            d.verification_status = document.getElementById('filter_verification_status').value;
            d.student_id = document.getElementById('filter_student_id').value;
          }
        });

        $('#filter_category_id, #filter_verification_status, #filter_student_id').on('change', function() {
          if (dt) {
            dt.ajax.reload();
          }
        });
      }
    });
    </script>
