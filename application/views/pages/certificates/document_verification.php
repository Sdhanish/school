<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Document Verification Queue</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review uploaded identification, medical, and previous academic records for authenticity.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('certificates/documents'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">folder</span>All Documents
        </a>
      </div>
    </div>

    <!-- Verification Queue Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Documents Pending Verification (<?php echo count($pending_docs); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Document Title & #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Uploaded Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">File View</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Verification Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($pending_docs)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No documents currently pending verification.</td></tr>
            <?php else: ?>
              <?php foreach ($pending_docs as $doc): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($doc->first_name . ' ' . $doc->last_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($doc->admission_number); ?> (<?php echo html_escape($doc->class_name . ' ' . $doc->section_name); ?>)</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($doc->category_name ?: $doc->document_type); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-on-surface font-semibold block"><?php echo html_escape($doc->document_name); ?></span>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($doc->document_number ?: 'N/A'); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($doc->created_at)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($doc->file_path): ?>
                      <a href="<?php echo base_url('uploads/student_documents/' . $doc->file_path); ?>" target="_blank" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>Inspect File
                      </a>
                    <?php else: ?>
                      <span class="text-[11px] text-on-surface-variant font-mono">No File</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                      <a href="<?php echo site_url('certificates/verify_doc/' . $doc->document_id); ?>" class="px-3 py-1 rounded-lg bg-secondary text-on-secondary text-xs font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
                        Verify
                      </a>
                      <button onclick="openRejectModal(<?php echo $doc->document_id; ?>)" class="px-3 py-1 rounded-lg bg-error-container text-error text-xs font-semibold hover:bg-error/20 transition-colors">
                        Reject
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reject Document Modal Dialog -->
    <dialog id="rejectDocModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-md backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Reject Student Document</h3>
          <button onclick="document.getElementById('rejectDocModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <form id="rejectForm" method="post" action="">
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Rejection Reason *</label>
              <textarea name="rejection_reason" rows="3" required placeholder="State why document is invalid or rejected (e.g. illegible scan, name mismatch)..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('rejectDocModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-error text-on-error text-label-md font-semibold hover:bg-error/80 transition-colors shadow-sm">Confirm Rejection</button>
            </div>
          </div>
        </form>
      </div>
    </dialog>

    <script>
      function openRejectModal(docId) {
        document.getElementById('rejectForm').action = '<?php echo site_url("certificates/reject_doc/"); ?>' + docId;
        document.getElementById('rejectDocModal').showModal();
      }
    </script>
