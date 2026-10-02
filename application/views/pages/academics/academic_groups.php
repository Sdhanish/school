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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Department / Groups</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage higher-level department / groupings (KG's, LP, UP, HS, SS) that categorize classes.</p>
      </div>
      <?php if (!empty($can_create)): ?>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddGroupModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Department / Group
        </button>
      </div>
      <?php endif; ?>
    </div>

    <!-- Department / Groups Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Order</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Department / Group Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Description</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Attendance Type</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Configured Classes</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Last Updated</th>
              <?php if (!empty($can_edit) || !empty($can_delete)): ?>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($groups)): ?>
              <tr>
                <td colspan="<?php echo (!empty($can_edit) || !empty($can_delete)) ? '8' : '7'; ?>" class="px-4 py-8 text-center text-on-surface-variant">
                  No department / groups found.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($groups as $grp): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-mono text-on-surface-variant font-medium whitespace-nowrap">
                    #<?php echo (int)$grp->display_order; ?>
                  </td>
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                      <span class="font-bold text-on-surface"><?php echo html_escape($grp->group_name); ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant">
                    <?php echo html_escape($grp->description ?: '-'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <?php if (($grp->attendance_type ?? 'daily') === 'period'): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <span class="material-symbols-outlined text-[14px]">schedule</span>Period
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="material-symbols-outlined text-[14px]">today</span>Daily
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 font-medium whitespace-nowrap">
                    <div class="flex flex-col">
                      <a href="<?php echo site_url('academics/classes?group_id=' . $grp->academic_group_id); ?>" class="inline-flex items-center gap-1 text-primary hover:underline font-semibold">
                        <span class="material-symbols-outlined text-[16px]">school</span>
                        <?php echo isset($grp->class_count) ? $grp->class_count : 0; ?> active classes
                      </a>
                      <?php if (!empty($grp->configured_classes_text)): ?>
                        <span class="text-[11px] text-on-surface-variant truncate max-w-[200px]" title="<?php echo html_escape($grp->configured_classes_text); ?>">
                          <?php echo html_escape($grp->configured_classes_text); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <?php if ((int)$grp->status === 1): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Active
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                        Disabled
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm whitespace-nowrap">
                    <?php echo !empty($grp->updated_at) ? date('M d, Y', strtotime($grp->updated_at)) : (!empty($grp->created_at) ? date('M d, Y', strtotime($grp->created_at)) : '-'); ?>
                  </td>
                  <?php if (!empty($can_edit) || !empty($can_delete)): ?>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      <?php if (!empty($can_edit)): ?>
                      <button onclick="openEditGroupModal(<?php echo $grp->academic_group_id; ?>, '<?php echo html_escape(addslashes($grp->group_name)); ?>', '<?php echo html_escape(addslashes($grp->description ?: '')); ?>', <?php echo (int)$grp->display_order; ?>, <?php echo (int)$grp->status; ?>, '<?php echo html_escape(addslashes($grp->configured_classes_text ?? '')); ?>', '<?php echo html_escape(addslashes($grp->attendance_type ?? 'daily')); ?>')" 
                              class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors cursor-pointer" 
                              title="Edit Department / Group">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <?php if ((int)$grp->status === 1): ?>
                        <a href="<?php echo site_url('academics/toggle_group_status/' . $grp->academic_group_id . '/0'); ?>" 
                           onclick="return confirm('Disable department / group <?php echo html_escape($grp->group_name); ?>?')" 
                           class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" 
                           title="Disable Department / Group">
                          <span class="material-symbols-outlined text-[18px]">block</span>
                        </a>
                      <?php else: ?>
                        <a href="<?php echo site_url('academics/toggle_group_status/' . $grp->academic_group_id . '/1'); ?>" 
                           class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" 
                           title="Enable Department / Group">
                          <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        </a>
                      <?php endif; ?>
                      <?php endif; ?>

                      <?php if (!empty($can_delete)): ?>
                      <a href="<?php echo site_url('academics/delete_academic_group/' . $grp->academic_group_id); ?>" 
                         onclick="return confirm('Deactivate department / group <?php echo html_escape($grp->group_name); ?>?')" 
                         class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors cursor-pointer" 
                         title="Deactivate">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                      </a>
                      <?php endif; ?>
                    </div>
                  </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Add / Edit Department / Group -->
    <?php if (!empty($can_create) || !empty($can_edit)): ?>
    <div id="modal-group" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-group-title">Add Department / Group</h3>
          <button onclick="document.getElementById('modal-group').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <?php echo form_open('academics/academic_groups', array('class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="action" id="group_action" value="add"/>
          <input type="hidden" name="academic_group_id" id="modal_group_id"/>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Department / Group Name *</label>
            <input type="text" name="group_name" id="modal_group_name" required placeholder="e.g. KG's, LP, UP, HS, SS" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary"/>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Description</label>
            <textarea name="description" id="modal_group_description" rows="2" placeholder="e.g. Kindergarten classes (LKG, UKG)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary"></textarea>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Attendance Type *</label>
            <select name="attendance_type" id="modal_group_attendance_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary">
              <option value="daily">Daily</option>
              <option value="period">Period</option>
            </select>
            <p class="text-[11px] text-on-surface-variant mt-1">Determines if attendance is marked once per day (Daily) or per period (Period).</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 text-on-surface">Display Order</label>
              <input type="number" name="display_order" id="modal_group_order" min="0" value="1" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary"/>
            </div>
            <div>
              <label class="block text-label-md mb-1 text-on-surface">Status</label>
              <select name="status" id="modal_group_status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary">
                <option value="1">Active</option>
                <option value="0">Disabled</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Configured Classes</label>
            <input type="text" name="classes" id="modal_group_classes" placeholder="e.g. Grade 1, Grade 2, Grade 3, Grade 4" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary"/>
            <p class="text-[11px] text-on-surface-variant mt-1">Comma-separated class names categorized under this department / group.</p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('modal-group').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high cursor-pointer text-label-md">
              Cancel
            </button>
            <button type="submit" id="modal_group_submit_btn" class="px-4 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 cursor-pointer text-label-md shadow-sm">
              Save Department / Group
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openAddGroupModal() {
        document.getElementById('modal-group-title').textContent = 'Add Department / Group';
        document.getElementById('group_action').value = 'add';
        document.getElementById('modal_group_id').value = '';
        document.getElementById('modal_group_name').value = '';
        document.getElementById('modal_group_description').value = '';
        document.getElementById('modal_group_attendance_type').value = 'daily';
        document.getElementById('modal_group_order').value = '<?php echo count($groups) + 1; ?>';
        document.getElementById('modal_group_status').value = '1';
        document.getElementById('modal_group_classes').value = '';
        document.getElementById('modal_group_submit_btn').textContent = 'Save Department / Group';
        document.getElementById('modal-group').classList.remove('hidden');
      }

      function openEditGroupModal(id, name, desc, order, status, classes, attendanceType) {
        document.getElementById('modal-group-title').textContent = 'Edit Department / Group';
        document.getElementById('group_action').value = 'edit';
        document.getElementById('modal_group_id').value = id;
        document.getElementById('modal_group_name').value = name;
        document.getElementById('modal_group_description').value = desc;
        document.getElementById('modal_group_attendance_type').value = attendanceType || 'daily';
        document.getElementById('modal_group_order').value = order;
        document.getElementById('modal_group_status').value = (status !== undefined) ? status : '1';
        document.getElementById('modal_group_classes').value = classes || '';
        document.getElementById('modal_group_submit_btn').textContent = 'Update Department / Group';
        document.getElementById('modal-group').classList.remove('hidden');
      }
    </script>
    <?php endif; ?>
