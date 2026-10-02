<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-5 p-4 rounded-xl bg-secondary-container/80 border border-secondary text-on-secondary-container text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-[22px] text-secondary">check_circle</span>
        <div><?php echo html_escape($this->session->flashdata('success')); ?></div>
      </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-5 p-4 rounded-xl bg-error-container/40 border border-error/30 text-error text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-[22px] text-error">error</span>
        <div><?php echo html_escape($this->session->flashdata('error')); ?></div>
      </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff Document Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Configure mandatory staff documents required by the school. Any document added here dynamically becomes a required upload field during staff registration.
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[20px]">add_circle</span>+ Add Document
        </button>
      </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-primary text-white flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[22px]">folder_managed</span>
        </div>
        <div>
          <div class="text-[12px] text-on-surface-variant font-medium">Total Configured</div>
          <div class="text-headline-md font-bold text-on-surface"><?php echo count($document_types); ?></div>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[22px]">verified</span>
        </div>
        <div>
          <div class="text-[12px] text-on-surface-variant font-medium">Active (Required)</div>
          <div class="text-headline-md font-bold text-secondary">
            <?php 
              $activeCount = 0;
              foreach ($document_types as $dt) { if ($dt->status === 'Active') $activeCount++; }
              echo $activeCount;
            ?>
          </div>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[22px]">pause_circle</span>
        </div>
        <div>
          <div class="text-[12px] text-on-surface-variant font-medium">Inactive</div>
          <div class="text-headline-md font-bold text-on-surface-variant"><?php echo count($document_types) - $activeCount; ?></div>
        </div>
      </div>
    </div>

    <!-- Main Documents Table Container -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div class="flex items-center gap-2.5">
          <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
          <h3 class="font-headline-md text-headline-md text-on-surface">Configured Staff Documents</h3>
        </div>
        <span class="text-label-md text-on-surface-variant font-medium">All active documents are mandatory on Add Staff</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="text-left px-6 py-3.5 text-label-md text-on-surface-variant uppercase font-semibold">Document Name</th>
              <th class="text-left px-6 py-3.5 text-label-md text-on-surface-variant uppercase font-semibold">Description / Notes</th>
              <th class="text-left px-6 py-3.5 text-label-md text-on-surface-variant uppercase font-semibold">Status</th>
              <th class="text-left px-6 py-3.5 text-label-md text-on-surface-variant uppercase font-semibold">Created By</th>
              <th class="text-right px-6 py-3.5 text-label-md text-on-surface-variant uppercase font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($document_types)): ?>
              <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="max-w-md mx-auto space-y-3">
                    <div class="w-14 h-14 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center mx-auto">
                      <span class="material-symbols-outlined text-[30px]">folder_off</span>
                    </div>
                    <div class="font-headline-md text-headline-md text-on-surface">No Staff Documents Configured</div>
                    <p class="text-body-md text-on-surface-variant">
                      Super Admin can configure required staff documents from here. Added documents will immediately become mandatory on the staff registration form.
                    </p>
                    <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer mt-2">
                      <span class="material-symbols-outlined text-[18px]">add</span>+ Add First Document
                    </button>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($document_types as $doc): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-6 py-4 font-semibold text-on-surface">
                    <div class="flex items-center gap-3">
                      <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                      <div>
                        <span class="text-body-md font-medium text-on-surface"><?php echo html_escape($doc->document_name); ?></span>
                        <span class="text-error text-sm font-bold ml-0.5">*</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-on-surface-variant text-sm max-w-xs truncate">
                    <?php echo html_escape($doc->description ?: '—'); ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($doc->status === 'Active'): ?>
                      <a href="<?php echo site_url('settings/staff_documents/toggle/' . $doc->id); ?>" title="Click to Deactivate" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container hover:opacity-85 transition-opacity">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Active (Required)
                      </a>
                    <?php else: ?>
                      <a href="<?php echo site_url('settings/staff_documents/toggle/' . $doc->id); ?>" title="Click to Activate" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant hover:opacity-85 transition-opacity">
                        <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>Inactive
                      </a>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 text-on-surface-variant text-sm whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="inline-block px-2 py-0.5 rounded bg-primary-fixed/60 text-primary text-[11px] font-semibold">
                        <?php echo html_escape($doc->creator_name ?: 'Super Admin'); ?>
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                      <button type="button" 
                        onclick="openEditModal(<?php echo (int)$doc->id; ?>, '<?php echo html_escape(addslashes($doc->document_name)); ?>', '<?php echo html_escape(addslashes($doc->description ?? '')); ?>', '<?php echo html_escape($doc->status); ?>')" 
                        class="px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high hover:text-primary transition-colors inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">edit</span>Edit
                      </button>
                      <button type="button" 
                        onclick="openDeleteModal(<?php echo (int)$doc->id; ?>, '<?php echo html_escape(addslashes($doc->document_name)); ?>')" 
                        class="px-3 py-1.5 rounded-lg text-error hover:bg-error-container/30 transition-colors inline-flex items-center gap-1 text-label-md">
                        <span class="material-symbols-outlined text-[16px]">delete</span>Delete
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

    <!-- ADD DOCUMENT MODAL -->
    <div id="modal-add-doc" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg overflow-hidden animate-fadeIn">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/60 bg-surface-container-low">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">add_circle</span>
            <h3 class="font-headline-md text-headline-md text-on-surface">Add Required Document</h3>
          </div>
          <button type="button" onclick="closeAddModal()" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('settings/staff_documents/add', array('class' => 'p-6 space-y-4')); ?>
          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Document Name <span class="text-error">*</span></label>
            <input type="text" name="document_name" id="add_doc_name" required placeholder="e.g. Aadhaar, PAN, Passport, Experience Certificate" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all placeholder-on-surface-variant/40"/>
            <p class="text-[12px] text-on-surface-variant mt-1">This document name will dynamically appear as a required upload field on the Add Staff form.</p>
          </div>

          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Description / Instructions (Optional)</label>
            <textarea name="description" rows="2" placeholder="e.g. Upload scanned copy of original document in PDF/Image format" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all placeholder-on-surface-variant/40"></textarea>
          </div>

          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Initial Status</label>
            <select name="status" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-medium text-on-surface">
              <option value="Active">Active (Immediately required on Add Staff)</option>
              <option value="Inactive">Inactive (Saved as draft)</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-outline-variant/60">
            <button type="button" onclick="closeAddModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors">Cancel</button>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer inline-flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">check</span>Save Document
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- EDIT DOCUMENT MODAL -->
    <div id="modal-edit-doc" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg overflow-hidden animate-fadeIn">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/60 bg-surface-container-low">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">edit_note</span>
            <h3 class="font-headline-md text-headline-md text-on-surface">Edit Document Definition</h3>
          </div>
          <button type="button" onclick="closeEditModal()" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <form id="edit_doc_form" method="POST" action="" class="p-6 space-y-4">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"/>
          
          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Document Name <span class="text-error">*</span></label>
            <input type="text" name="document_name" id="edit_doc_name" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all"/>
          </div>

          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Description / Instructions</label>
            <textarea name="description" id="edit_doc_desc" rows="2" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all"></textarea>
          </div>

          <div>
            <label class="block text-label-md text-on-surface font-semibold mb-1.5">Status</label>
            <select name="status" id="edit_doc_status" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-medium text-on-surface">
              <option value="Active">Active (Required on Add Staff)</option>
              <option value="Inactive">Inactive (Hidden from forms)</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-outline-variant/60">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors">Cancel</button>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer inline-flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">save</span>Update Document
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL (SOFT DELETE PRESERVING HISTORICAL DATA) -->
    <div id="modal-delete-doc" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md overflow-hidden animate-fadeIn">
        <div class="p-6">
          <div class="w-12 h-12 rounded-full bg-error-container/40 text-error flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">warning</span>
          </div>
          <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Remove Document Requirement?</h3>
          <p class="text-body-md text-on-surface-variant leading-relaxed">
            Are you sure you want to remove this document requirement (<strong id="delete_doc_title" class="text-on-surface"></strong>)?
          </p>
          <div class="mt-3 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60 text-[13px] text-on-surface-variant">
            <span class="font-semibold text-secondary flex items-center gap-1 mb-1">
              <span class="material-symbols-outlined text-[16px]">shield</span> Historical Data Safe
            </span>
            This will hide the document from future staff registration/edit forms. All previously uploaded documents by existing staff will remain completely preserved in the database.
          </div>
        </div>
        <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-surface-container-low border-t border-outline-variant/60">
          <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors">Cancel</button>
          <a id="delete_confirm_btn" href="#" class="px-4 py-2 rounded-lg bg-error text-on-error text-label-md hover:bg-error/90 transition-colors inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">delete</span>Remove Document
          </a>
        </div>
      </div>
    </div>

    <script>
      function openAddModal() {
        document.getElementById('add_doc_name').value = '';
        document.getElementById('modal-add-doc').classList.remove('hidden');
        document.getElementById('add_doc_name').focus();
      }
      function closeAddModal() {
        document.getElementById('modal-add-doc').classList.add('hidden');
      }

      function openEditModal(id, name, desc, status) {
        document.getElementById('edit_doc_form').action = '<?php echo site_url("settings/staff_documents/edit/"); ?>' + id;
        document.getElementById('edit_doc_name').value = name;
        document.getElementById('edit_doc_desc').value = desc;
        document.getElementById('edit_doc_status').value = status;
        document.getElementById('modal-edit-doc').classList.remove('hidden');
      }
      function closeEditModal() {
        document.getElementById('modal-edit-doc').classList.add('hidden');
      }

      function openDeleteModal(id, name) {
        document.getElementById('delete_doc_title').textContent = name;
        document.getElementById('delete_confirm_btn').href = '<?php echo site_url("settings/staff_documents/delete/"); ?>' + id;
        document.getElementById('modal-delete-doc').classList.remove('hidden');
      }
      function closeDeleteModal() {
        document.getElementById('modal-delete-doc').classList.add('hidden');
      }
    </script>
