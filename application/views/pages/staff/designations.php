<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Page Header (Department / Groups & Designations) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Designations</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Staff Management / Designations</p>
      </div>
      <?php if (!empty($can_create)): ?>
        <div class="flex items-center gap-2 shrink-0">
          <button type="button" id="btn-open-add-desig" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">badge</span>Add Designation
          </button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Flash Notifications -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-body-md flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span><?php echo html_escape($this->session->flashdata('success')); ?></span>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-700 dark:text-rose-300 text-body-md flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
      </div>
    <?php endif; ?>

    <!-- Two-Card Responsive Grid: Department / Groups & Designations -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

      <!-- Section 1: Department / Groups Section -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">category</span>Department / Groups (<span id="group-total-count"><?php echo count($academic_groups ?? []); ?></span>)
          </h3>
          <?php if (!empty($can_create_group)): ?>
            <button type="button" id="btn-open-add-group" class="btn-quick-add-group inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:underline cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">add</span>Add Group
            </button>
          <?php endif; ?>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse">
            <thead>
              <tr class="border-b border-outline-variant/60">
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Department / Group Name</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Staff Count</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Status</th>
                <?php if (!empty($can_edit_group)): ?>
                  <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody id="group-table-body" class="divide-y divide-outline-variant/30 text-body-md">
              <?php if (empty($academic_groups)): ?>
                <tr>
                  <td colspan="<?php echo !empty($can_edit_group) ? '4' : '3'; ?>" class="px-4 py-8 text-center text-on-surface-variant">
                    No department / groups found. Click "Add Department / Group" to create one.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($academic_groups as $grp): ?>
                  <tr class="hover:bg-surface-container-low transition-colors" id="row-group-<?php echo $grp->academic_group_id; ?>">
                    <td class="px-4 py-3 text-on-surface font-semibold">
                      <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-[18px]">category</span>
                        <span class="group-name-cell"><?php echo html_escape($grp->group_name); ?></span>
                      </div>
                      <?php if (!empty($grp->description)): ?>
                        <div class="text-[12px] text-on-surface-variant font-normal group-desc-cell mt-0.5"><?php echo html_escape($grp->description); ?></div>
                      <?php endif; ?>
                      <?php if (!empty($grp->configured_classes_text)): ?>
                        <div class="text-[11px] text-on-surface-variant/80 font-normal mt-0.5" title="Configured classes: <?php echo html_escape($grp->configured_classes_text); ?>">
                          <span class="font-medium text-on-surface-variant">Classes:</span> <?php echo html_escape($grp->configured_classes_text); ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-mono text-primary font-medium whitespace-nowrap">
                      <?php echo isset($grp->staff_count) ? (int)$grp->staff_count : 0; ?> staff
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <?php if ((int)$grp->status === 1): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Disabled</span>
                      <?php endif; ?>
                    </td>
                    <?php if (!empty($can_edit_group)): ?>
                      <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button type="button" class="btn-edit-group p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-flex items-center cursor-pointer" 
                                data-id="<?php echo $grp->academic_group_id; ?>"
                                data-name="<?php echo html_escape($grp->group_name); ?>"
                                data-description="<?php echo html_escape($grp->description ?? ''); ?>"
                                data-display-order="<?php echo (int)$grp->display_order; ?>"
                                data-status="<?php echo (int)$grp->status; ?>"
                                data-attendance-type="<?php echo html_escape($grp->attendance_type ?? 'daily'); ?>"
                                data-classes="<?php echo html_escape($grp->configured_classes_text ?? ''); ?>"
                                title="Edit Department / Group">
                          <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Section 2: Designations Section -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">badge</span>Designations (<span id="desig-total-count"><?php echo count($designations); ?></span>)
          </h3>
          <?php if (!empty($can_create)): ?>
            <button type="button" class="btn-quick-add-desig inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">add</span>Add Designation
            </button>
          <?php endif; ?>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse">
            <thead>
              <tr class="border-b border-outline-variant/60">
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Designation Name</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Category</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Staff Count</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Status</th>
                <?php if (!empty($can_edit) || !empty($can_delete)): ?>
                  <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody id="desig-table-body" class="divide-y divide-outline-variant/30 text-body-md">
              <?php if (empty($designations)): ?>
                <tr>
                  <td colspan="<?php echo (!empty($can_edit) || !empty($can_delete)) ? '5' : '4'; ?>" class="px-4 py-8 text-center text-on-surface-variant">
                    No designations found. Click "Add Designation" to create one.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($designations as $dg): ?>
                  <tr class="hover:bg-surface-container-low transition-colors" id="row-desig-<?php echo $dg->designation_id; ?>">
                    <td class="px-4 py-3 text-on-surface font-semibold">
                      <div class="flex items-center gap-2">
                        <span class="desig-name-cell"><?php echo html_escape($dg->designation_name); ?></span>
                      </div>
                      <?php if (!empty($dg->description)): ?>
                        <div class="text-[12px] text-on-surface-variant font-normal desig-desc-cell mt-0.5"><?php echo html_escape($dg->description); ?></div>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-on-surface-variant desig-cat-cell"><?php echo html_escape($dg->category); ?></td>
                    <td class="px-4 py-3 font-mono text-primary font-medium whitespace-nowrap">
                      <?php echo isset($dg->staff_count) ? (int)$dg->staff_count : 0; ?> staff
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <?php if ((int)$dg->status === 1): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <?php if (!empty($can_edit) || !empty($can_delete)): ?>
                      <td class="px-4 py-3 text-right whitespace-nowrap">
                        <?php if (!empty($can_edit)): ?>
                          <button type="button" class="btn-edit-desig p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-flex items-center cursor-pointer" 
                                  data-id="<?php echo $dg->designation_id; ?>"
                                  data-name="<?php echo html_escape($dg->designation_name); ?>"
                                  data-category="<?php echo html_escape($dg->category); ?>"
                                  data-description="<?php echo html_escape($dg->description ?? ''); ?>"
                                  data-status="<?php echo (int)$dg->status; ?>"
                                  title="Edit Designation">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                          </button>
                        <?php endif; ?>
                        <?php if (!empty($can_delete)): ?>
                          <button type="button" class="btn-delete-desig p-1.5 rounded-lg text-error hover:bg-error/10 transition-colors inline-flex items-center ml-1 cursor-pointer" 
                                  data-id="<?php echo $dg->designation_id; ?>"
                                  data-name="<?php echo html_escape($dg->designation_name); ?>"
                                  data-staff-count="<?php echo isset($dg->staff_count) ? (int)$dg->staff_count : 0; ?>"
                                  title="Delete Designation">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                          </button>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal: Add / Edit Department / Group -->
    <div id="modal-group" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 id="modal-group-title" class="font-headline-md text-headline-md text-on-surface">Add Department / Group</h3>
          <button type="button" onclick="closeGroupModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="form-group" method="post" action="<?php echo site_url('staff/designations'); ?>" class="p-6 space-y-4">
          <input type="hidden" name="action" id="group_action" value="add_group" />
          <input type="hidden" name="academic_group_id" id="group_id" value="" />
          <div id="group-alert" class="hidden p-3 rounded-lg text-body-sm"></div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Department / Group Name *</label>
            <input type="text" name="group_name" id="group_name" required placeholder="e.g. KG's, LP, UP, HS, SS" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary" />
          </div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Description</label>
            <textarea name="description" id="group_desc" rows="2" placeholder="e.g. Primary section classes (1 to 4)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary resize-none"></textarea>
          </div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Attendance Type *</label>
            <select name="attendance_type" id="group_attendance_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary">
              <option value="daily">Daily</option>
              <option value="period">Period</option>
            </select>
            <p class="text-[11px] text-on-surface-variant mt-1">Determines if attendance is marked once per day (Daily) or per period (Period).</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md font-medium text-on-surface mb-1">Display Order</label>
              <input type="number" name="display_order" id="group_order" min="0" value="1" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary" />
            </div>
            <div>
              <label class="block text-label-md font-medium text-on-surface mb-1">Status</label>
              <select name="status" id="group_status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary">
                <option value="1">Active</option>
                <option value="0">Disabled</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Configured Classes</label>
            <input type="text" name="classes" id="group_classes" placeholder="e.g. Grade 1, Grade 2, Grade 3" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary" />
            <p class="text-[11px] text-on-surface-variant mt-1">Comma-separated class names categorized under this department / group.</p>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="closeGroupModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
            <button type="submit" id="btn-save-group" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-secondary/90 transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>
              <span id="btn-save-group-text">Save Department / Group</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add / Edit Designation -->
    <div id="modal-desig" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 id="modal-desig-title" class="font-headline-md text-headline-md text-on-surface">Add Designation</h3>
          <button type="button" onclick="closeDesigModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="form-desig" method="post" action="<?php echo site_url('staff/designations'); ?>" class="p-6 space-y-4">
          <input type="hidden" name="action" id="desig_action" value="add_designation" />
          <input type="hidden" name="designation_id" id="desig_id" value="" />
          <div id="desig-alert" class="hidden p-3 rounded-lg text-body-sm"></div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Designation Name *</label>
            <input type="text" name="designation_name" id="desig_name" required placeholder="e.g. Senior Lab Instructor" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary" />
          </div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Category *</label>
            <select name="category" id="desig_cat" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
              <?php 
                $cat_options = !empty($categories) ? $categories : ['Teaching', 'Administration', 'Finance', 'Support'];
                foreach ($cat_options as $cat): 
              ?>
                <option value="<?php echo html_escape($cat); ?>"><?php echo html_escape($cat); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-label-md font-medium text-on-surface mb-1">Description</label>
            <textarea name="description" id="desig_desc" rows="3" placeholder="Designation duties and responsibilities" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
          </div>

          <div id="desig-status-group">
            <label class="block text-label-md font-medium text-on-surface mb-1">Status</label>
            <select name="status" id="desig_status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="closeDesigModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
            <button type="submit" id="btn-save-desig" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>
              <span id="btn-save-desig-text">Save Designation</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Delete Confirmation (Designation Only) -->
    <div id="modal-delete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md overflow-hidden">
        <div class="p-6">
          <div class="flex items-start gap-4">
            <div class="p-3 rounded-xl bg-rose-500/10 text-error flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[28px]">warning</span>
            </div>
            <div>
              <h3 id="delete-title" class="font-headline-md text-headline-md text-on-surface">Confirm Delete</h3>
              <p id="delete-message" class="text-body-md text-on-surface-variant mt-2 leading-relaxed">
                Are you sure you want to delete this designation?
              </p>
              <div id="delete-alert" class="hidden mt-3 p-3 rounded-lg text-body-sm"></div>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 bg-surface-container-low/50 border-t border-outline-variant">
          <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
          <button type="button" id="btn-confirm-delete" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-error text-on-error text-label-md hover:bg-error/90 transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">delete</span>
            <span>Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var baseUrl = "<?php echo base_url(); ?>";

  // Modal references
  var modalGroup  = document.getElementById('modal-group');
  var modalDesig  = document.getElementById('modal-desig');
  var modalDelete = document.getElementById('modal-delete');

  // Active delete state (for designations only)
  var deleteTarget = {
    id: null,
    name: ''
  };

  // Helper: update CSRF
  function updateCsrf(data) {
    if (data && data.csrf_token_name && data.csrf_hash) {
      window.CSRF_TOKEN_NAME = data.csrf_token_name;
      window.CSRF_HASH = data.csrf_hash;
    }
  }

  // Helper: show inline form alert
  function showAlert(elemId, msg, isError) {
    var el = document.getElementById(elemId);
    if (!el) return;
    el.classList.remove('hidden', 'bg-rose-500/10', 'text-rose-700', 'border-rose-500/20', 'bg-emerald-500/10', 'text-emerald-700', 'border-emerald-500/20', 'border');
    if (isError) {
      el.classList.add('bg-rose-500/10', 'text-rose-700', 'border-rose-500/20', 'border');
    } else {
      el.classList.add('bg-emerald-500/10', 'text-emerald-700', 'border-emerald-500/20', 'border');
    }
    el.innerHTML = msg;
  }

  function hideAlert(elemId) {
    var el = document.getElementById(elemId);
    if (el) el.classList.add('hidden');
  }

  /* =========================================================================
     Department / Groups Handlers (Edit & Add only; No Delete action)
     ========================================================================= */
  window.closeGroupModal = function () {
    if (modalGroup) modalGroup.classList.add('hidden');
    hideAlert('group-alert');
    var form = document.getElementById('form-group');
    if (form) form.reset();
    document.getElementById('group_id').value = '';
    document.getElementById('group_action').value = 'add_group';
  };

  function openAddGroupModal() {
    var form = document.getElementById('form-group');
    if (form) form.reset();
    document.getElementById('group_id').value = '';
    document.getElementById('group_action').value = 'add_group';
    document.getElementById('modal-group-title').textContent = 'Add Department / Group';
    document.getElementById('btn-save-group-text').textContent = 'Save Department / Group';
    document.getElementById('group_attendance_type').value = 'daily';
    document.getElementById('group_order').value = '<?php echo count($academic_groups ?? []) + 1; ?>';
    document.getElementById('group_status').value = '1';
    document.getElementById('group_classes').value = '';
    hideAlert('group-alert');
    if (modalGroup) modalGroup.classList.remove('hidden');
    var nameInput = document.getElementById('group_name');
    if (nameInput) nameInput.focus();
  }

  var btnAddGroup = document.getElementById('btn-open-add-group');
  if (btnAddGroup) {
    btnAddGroup.addEventListener('click', openAddGroupModal);
  }

  $(document).on('click', '.btn-quick-add-group', function () {
    openAddGroupModal();
  });

  $(document).on('click', '.btn-edit-group', function () {
    var id       = $(this).data('id');
    var name     = $(this).data('name');
    var desc     = $(this).data('description');
    var order    = $(this).data('display-order');
    var status   = $(this).data('status');
    var attType  = $(this).data('attendance-type');
    var classes  = $(this).data('classes');

    document.getElementById('group_id').value = id;
    document.getElementById('group_action').value = 'edit_group';
    document.getElementById('group_name').value = name || '';
    document.getElementById('group_desc').value = desc || '';
    document.getElementById('group_attendance_type').value = attType || 'daily';
    document.getElementById('group_order').value = (order !== undefined) ? order : '1';
    document.getElementById('group_status').value = (status !== undefined) ? String(status) : '1';
    document.getElementById('group_classes').value = classes || '';
    document.getElementById('modal-group-title').textContent = 'Edit Department / Group';
    document.getElementById('btn-save-group-text').textContent = 'Update Department / Group';
    hideAlert('group-alert');
    if (modalGroup) modalGroup.classList.remove('hidden');
    var nameInput = document.getElementById('group_name');
    if (nameInput) nameInput.focus();
  });

  $('#form-group').on('submit', function (e) {
    e.preventDefault();
    var name = $.trim($('#group_name').val());
    if (!name) {
      showAlert('group-alert', 'Department / Group name is required.', true);
      return;
    }

    var btn = $('#btn-save-group');
    btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

    var postData = {
      academic_group_id: $('#group_id').val(),
      group_name: name,
      description: $('#group_desc').val(),
      attendance_type: $('#group_attendance_type').val(),
      display_order: $('#group_order').val(),
      status: $('#group_status').val(),
      classes: $('#group_classes').val()
    };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: baseUrl + 'staff/ajax_save_academic_group',
      type: 'POST',
      dataType: 'json',
      data: postData,
      success: function (res) {
        updateCsrf(res);
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        if (res.status) {
          window.location.reload();
        } else {
          showAlert('group-alert', res.message || 'Error saving department / group.', true);
        }
      },
      error: function (xhr) {
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        var err = 'Server communication failed. Please try again.';
        try {
          var res = JSON.parse(xhr.responseText);
          updateCsrf(res);
          if (res.message) err = res.message;
        } catch(e) {}
        showAlert('group-alert', err, true);
      }
    });
  });

  /* =========================================================================
     Designation Handlers
     ========================================================================= */
  window.closeDesigModal = function () {
    if (modalDesig) modalDesig.classList.add('hidden');
    hideAlert('desig-alert');
    var form = document.getElementById('form-desig');
    if (form) form.reset();
    document.getElementById('desig_id').value = '';
    document.getElementById('desig_action').value = 'add_designation';
  };

  function openAddDesigModal() {
    var form = document.getElementById('form-desig');
    if (form) form.reset();
    document.getElementById('desig_id').value = '';
    document.getElementById('desig_action').value = 'add_designation';
    document.getElementById('modal-desig-title').textContent = 'Add Designation';
    document.getElementById('btn-save-desig-text').textContent = 'Save Designation';
    document.getElementById('desig_cat').value = 'Teaching';
    document.getElementById('desig_status').value = '1';
    hideAlert('desig-alert');
    if (modalDesig) modalDesig.classList.remove('hidden');
    var nameInput = document.getElementById('desig_name');
    if (nameInput) nameInput.focus();
  }

  var btnAddDesig = document.getElementById('btn-open-add-desig');
  if (btnAddDesig) {
    btnAddDesig.addEventListener('click', openAddDesigModal);
  }

  $(document).on('click', '.btn-quick-add-desig', function () {
    openAddDesigModal();
  });

  $(document).on('click', '.btn-edit-desig', function () {
    var id       = $(this).data('id');
    var name     = $(this).data('name');
    var cat      = $(this).data('category');
    var desc     = $(this).data('description');
    var status   = $(this).data('status');

    document.getElementById('desig_id').value = id;
    document.getElementById('desig_action').value = 'edit_designation';
    document.getElementById('desig_name').value = name;
    document.getElementById('desig_cat').value = cat || 'Teaching';
    document.getElementById('desig_desc').value = desc || '';
    document.getElementById('desig_status').value = String(status);
    document.getElementById('modal-desig-title').textContent = 'Edit Designation';
    document.getElementById('btn-save-desig-text').textContent = 'Update Designation';
    hideAlert('desig-alert');
    if (modalDesig) modalDesig.classList.remove('hidden');
    var nameInput = document.getElementById('desig_name');
    if (nameInput) nameInput.focus();
  });

  $('#form-desig').on('submit', function (e) {
    e.preventDefault();
    var name = $.trim($('#desig_name').val());
    if (!name) {
      showAlert('desig-alert', 'Designation name is required.', true);
      return;
    }

    var btn = $('#btn-save-desig');
    btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

    var postData = {
      designation_id: $('#desig_id').val(),
      designation_name: name,
      category: $('#desig_cat').val(),
      description: $('#desig_desc').val(),
      status: $('#desig_status').val()
    };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: baseUrl + 'staff/ajax_save_designation',
      type: 'POST',
      dataType: 'json',
      data: postData,
      success: function (res) {
        updateCsrf(res);
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        if (res.status) {
          window.location.reload();
        } else {
          showAlert('desig-alert', res.message || 'Error saving designation.', true);
        }
      },
      error: function (xhr) {
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        var err = 'Server communication failed. Please try again.';
        try {
          var res = JSON.parse(xhr.responseText);
          updateCsrf(res);
          if (res.message) err = res.message;
        } catch(e) {}
        showAlert('desig-alert', err, true);
      }
    });
  });

  /* =========================================================================
     Delete Handlers (Designations only)
     ========================================================================= */
  window.closeDeleteModal = function () {
    if (modalDelete) modalDelete.classList.add('hidden');
    hideAlert('delete-alert');
    deleteTarget.id   = null;
    deleteTarget.name = '';
  };

  $(document).on('click', '.btn-delete-desig', function () {
    deleteTarget.id   = $(this).data('id');
    deleteTarget.name = $(this).data('name');
    var staffCount    = parseInt($(this).data('staff-count') || 0, 10);

    document.getElementById('delete-title').textContent = 'Delete Designation';
    var msg = 'Are you sure you want to delete <strong class="text-on-surface">' + $('<div>').text(deleteTarget.name).html() + '</strong>?';
    if (staffCount > 0) {
      msg += '<br><span class="text-rose-600 font-medium text-[13px] mt-1 inline-block">⚠️ Warning: ' + staffCount + ' staff member(s) are currently assigned this designation.</span>';
    }
    document.getElementById('delete-message').innerHTML = msg;
    hideAlert('delete-alert');
    if (modalDelete) modalDelete.classList.remove('hidden');
  });

  $('#btn-confirm-delete').on('click', function () {
    if (!deleteTarget.id) return;

    var btn = $(this);
    btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

    var postData = {};
    postData['designation_id'] = deleteTarget.id;
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: baseUrl + 'staff/ajax_delete_designation',
      type: 'POST',
      dataType: 'json',
      data: postData,
      success: function (res) {
        updateCsrf(res);
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        if (res.status) {
          window.location.reload();
        } else {
          showAlert('delete-alert', res.message || 'Cannot delete record.', true);
        }
      },
      error: function (xhr) {
        btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        var err = 'Deletion failed. Please try again.';
        try {
          var res = JSON.parse(xhr.responseText);
          updateCsrf(res);
          if (res.message) err = res.message;
        } catch(e) {}
        showAlert('delete-alert', err, true);
      }
    });
  });

});
</script>
