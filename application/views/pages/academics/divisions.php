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

    <!-- Unified Workflow Announcement Banner -->
    <div class="mb-6 p-4 rounded-xl bg-primary/5 border border-primary/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-primary text-[24px]">info</span>
        <div>
          <h4 class="text-on-surface font-semibold text-body-md">Unified Class & Division Management</h4>
          <p class="text-on-surface-variant text-xs mt-0.5">Classes and their divisions can now be created, configured, and managed together in a single workflow.</p>
        </div>
      </div>
      <a href="<?php echo site_url('academics/classes'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors shrink-0 shadow-xs font-semibold">
        <span class="material-symbols-outlined text-[16px]">open_in_new</span>Go to Classes & Divisions
      </a>
    </div>


    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Divisions</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage class divisions structured under Department / Group &rarr; Class &rarr; Division hierarchy.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddDivisionModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Division
        </button>
      </div>
    </div>

    <!-- Hierarchy & Filter Bar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-3 mb-4">
      <!-- Tabs for Hierarchy vs Detailed view -->
      <div class="inline-flex p-1 rounded-xl bg-surface-container-high border border-outline-variant/40">
        <button id="tab-btn-hierarchy" onclick="switchView('hierarchy')" class="px-4 py-2 rounded-lg text-label-md font-medium transition-all bg-surface-container-lowest text-on-surface shadow-xs cursor-pointer">
          <span class="material-symbols-outlined text-[16px] inline-block mr-1 align-middle">account_tree</span>Academic Hierarchy
        </button>
        <button id="tab-btn-detailed" onclick="switchView('detailed')" class="px-4 py-2 rounded-lg text-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface cursor-pointer">
          <span class="material-symbols-outlined text-[16px] inline-block mr-1 align-middle">format_list_bulleted</span>All Divisions
        </button>
      </div>

      <div class="flex items-center gap-2">
        <select onchange="window.location.href='<?php echo site_url('academics/divisions'); ?>' + (this.value ? '?class_id=' + this.value : '')" class="px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface-variant">
          <option value="">All Classes</option>
          <?php foreach ($classes as $cls): ?>
            <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
          <?php endforeach; ?>
        </select>
        <a href="<?php echo site_url('academics/divisions'); ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors" title="Reset Filters"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
      </div>
    </div>

    <!-- 1. ACADEMIC HIERARCHY TABLE (Academic Group | Class | Divisions) -->
    <div id="view-hierarchy" class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-5 py-3.5 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Department / Group</th>
              <th class="text-left px-5 py-3.5 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Class</th>
              <th class="text-left px-5 py-3.5 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Divisions</th>
              <th class="text-center px-4 py-3.5 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Total</th>
              <th class="text-right px-5 py-3.5 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($hierarchy)): ?>
              <tr>
                <td colspan="5" class="px-5 py-8 text-center text-on-surface-variant">
                  No classes or divisions configured.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($hierarchy as $h): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-5 py-3.5 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                      <?php echo html_escape($h->group_name); ?>
                    </span>
                  </td>
                  <td class="px-5 py-3.5 font-semibold text-on-surface whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-primary text-[20px]">school</span>
                      <a href="<?php echo site_url('academics/divisions?class_id=' . $h->class_id); ?>" class="hover:underline text-on-surface font-bold">
                        <?php echo html_escape($h->class_name); ?>
                      </a>
                    </div>
                  </td>
                  <td class="px-5 py-3.5">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <?php if (!empty($h->divisions)): ?>
                        <?php foreach ($h->divisions as $d): ?>
                          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-high border border-outline-variant/40 text-on-surface text-sm font-semibold">
                            Division <?php echo html_escape($d->division_name); ?>
                          </span>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <span class="text-on-surface-variant text-sm italic">None (Defaults to Division A)</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-center font-bold text-on-surface whitespace-nowrap">
                    <?php echo (int)$h->division_count; ?>
                  </td>
                  <td class="px-5 py-3.5 text-right whitespace-nowrap">
                    <button onclick="openAddDivisionModalForClass(<?php echo $h->class_id; ?>, '<?php echo html_escape(addslashes($h->class_name)); ?>', '<?php echo html_escape(addslashes($h->group_name)); ?>')" class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 text-label-md font-medium inline-flex items-center gap-1 cursor-pointer transition-colors">
                      <span class="material-symbols-outlined text-[16px]">add</span>Add Division
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 2. DETAILED DIVISIONS TABLE -->
    <div id="view-detailed" class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Department / Group</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Class</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Division</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Class Teacher</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Room No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Capacity</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Students</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php 
              $div_list = $divisions ?? $sections ?? [];
              foreach ($div_list as $div): 
                $div_id = $div->division_id ?? $div->section_id;
                $div_name = $div->division_name ?? $div->section_name;
            ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                    <?php echo html_escape($div->group_name ?? 'General'); ?>
                  </span>
                </td>
                <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap"><?php echo html_escape($div->class_name); ?></td>
                <td class="px-4 py-3 font-bold text-primary whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-surface-container-high text-on-surface">Division <?php echo html_escape($div_name); ?></span>
                  <?php if (!empty($div->description)): ?>
                    <div class="text-[11px] text-on-surface-variant font-normal mt-0.5"><?php echo html_escape($div->description); ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                  <?php if (!empty($div->class_teacher_name)): ?>
                    <span class="inline-flex items-center gap-1.5 font-medium text-secondary">
                      <span class="material-symbols-outlined text-[18px]">person</span><?php echo html_escape($div->class_teacher_name); ?>
                    </span>
                  <?php else: ?>
                    <?php
                      $assign_url = site_url('academics/classes');
                    ?>
                    <a href="<?php echo $assign_url; ?>" class="text-[12px] text-primary hover:underline">+ Assign Teacher</a>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo html_escape($div->room_no ?: '—'); ?></td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo $div->capacity; ?> seats</td>
                <td class="px-4 py-3 font-bold text-secondary whitespace-nowrap">
                  <a href="<?php echo site_url('students?division_id=' . $div_id); ?>" class="hover:underline"><?php echo isset($div->student_count) ? $div->student_count : 0; ?> students</a>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <?php if (empty($div->is_default)): ?>
                      <button onclick="openEditDivisionModal(<?php echo $div_id; ?>, <?php echo $div->class_id; ?>, '<?php echo html_escape(addslashes($div_name)); ?>', '<?php echo html_escape(addslashes($div->room_no ?: '')); ?>', <?php echo $div->capacity; ?>, '<?php echo html_escape(addslashes($div->description ?: '')); ?>')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors cursor-pointer" title="Edit Division"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                      <a href="<?php echo site_url('academics/delete_division/' . $div_id); ?>" onclick="return confirm('Deactivate division <?php echo html_escape($div_name); ?>?')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors cursor-pointer" title="Deactivate"><span class="material-symbols-outlined text-[18px]">delete</span></a>
                    <?php else: ?>
                      <span class="text-[11px] text-on-surface-variant italic px-2">Default</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Add / Edit Division -->
    <div id="modal-division" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-division-title">Add Division</h3>
          <button onclick="document.getElementById('modal-division').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('academics/divisions', array('class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="action" id="division_action" value="add"/>
          <input type="hidden" name="division_id" id="modal_division_id"/>
          <input type="hidden" name="section_id" id="modal_section_id"/>
          
          <div>
            <label class="block text-label-md mb-1 text-on-surface">Class *</label>
            <select name="class_id" id="modal_division_class" required onchange="onDivisionClassChanged(this)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface">
              <option value="">Select Class</option>
              <?php foreach ($classes as $cls): ?>
                <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo html_escape($cls->group_name ?? 'General'); ?>"><?php echo html_escape($cls->class_name); ?><?php echo !empty($cls->group_name) ? ' (' . html_escape($cls->group_name) . ')' : ''; ?></option>
              <?php endforeach; ?>
            </select>
            <!-- Auto-derived Academic Group Notice -->
            <div id="derived-group-banner" class="mt-1.5 p-2 rounded-md bg-surface-container-high border border-outline-variant/40 text-xs text-on-surface-variant flex items-center gap-1.5 hidden">
              <span class="material-symbols-outlined text-[16px] text-primary">category</span>
              <span>Belongs to Department / Group: <strong id="derived-group-name" class="text-primary font-bold"></strong></span>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Division Name *</label>
            <input type="text" name="division_name" id="modal_division_name" required placeholder="e.g. A, B, C, D" maxlength="10" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface uppercase"/>
            <p class="text-xs text-on-surface-variant mt-1">Unique within this class.</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 text-on-surface">Room No.</label>
              <input type="text" name="room_no" id="modal_division_room" placeholder="e.g. 101" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface"/>
            </div>
            <div>
              <label class="block text-label-md mb-1 text-on-surface">Max Capacity</label>
              <input type="number" name="capacity" id="modal_division_capacity" value="40" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface"/>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface">Description / Notes</label>
            <textarea name="description" id="modal_division_description" rows="2" placeholder="Optional notes about this division..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('modal-division').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant cursor-pointer text-label-md">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer shadow-sm">Save Division</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function switchView(mode) {
        const vHier = document.getElementById('view-hierarchy');
        const vDet = document.getElementById('view-detailed');
        const bHier = document.getElementById('tab-btn-hierarchy');
        const bDet = document.getElementById('tab-btn-detailed');

        if (mode === 'hierarchy') {
          vHier.classList.remove('hidden');
          vDet.classList.add('hidden');
          bHier.classList.add('bg-surface-container-lowest', 'text-on-surface', 'shadow-xs');
          bHier.classList.remove('text-on-surface-variant');
          bDet.classList.remove('bg-surface-container-lowest', 'text-on-surface', 'shadow-xs');
          bDet.classList.add('text-on-surface-variant');
        } else {
          vHier.classList.add('hidden');
          vDet.classList.remove('hidden');
          bDet.classList.add('bg-surface-container-lowest', 'text-on-surface', 'shadow-xs');
          bDet.classList.remove('text-on-surface-variant');
          bHier.classList.remove('bg-surface-container-lowest', 'text-on-surface', 'shadow-xs');
          bHier.classList.add('text-on-surface-variant');
        }
      }

      function onDivisionClassChanged(sel) {
        const selectedOpt = sel.options[sel.selectedIndex];
        const group = selectedOpt ? selectedOpt.getAttribute('data-group') : '';
        const banner = document.getElementById('derived-group-banner');
        const nameSpan = document.getElementById('derived-group-name');

        if (group && sel.value) {
          nameSpan.textContent = group;
          banner.classList.remove('hidden');
        } else {
          banner.classList.add('hidden');
        }

        if (sel.value && document.getElementById('division_action').value === 'add') {
          fetch('<?php echo site_url("academics/get_next_division_ajax"); ?>?class_id=' + sel.value)
            .then(res => res.json())
            .then(data => {
              if (data.next_name) {
                document.getElementById('modal_division_name').value = data.next_name;
              }
            });
        }
      }

      function openAddDivisionModalForClass(classId, className, groupName) {
        openAddDivisionModal();
        const sel = document.getElementById('modal_division_class');
        sel.value = classId;
        onDivisionClassChanged(sel);
      }

      function openAddDivisionModal() {
        document.getElementById('division_action').value = 'add';
        document.getElementById('modal-division-title').textContent = 'Add Division';
        document.getElementById('modal_division_id').value = '';
        document.getElementById('modal_section_id').value = '';
        document.getElementById('modal_division_name').value = 'A';
        document.getElementById('modal_division_room').value = '';
        document.getElementById('modal_division_capacity').value = '40';
        document.getElementById('modal_division_description').value = '';
        document.getElementById('derived-group-banner').classList.add('hidden');

        const filterClass = '<?php echo $this->input->get("class_id"); ?>';
        if (filterClass) {
          const sel = document.getElementById('modal_division_class');
          sel.value = filterClass;
          onDivisionClassChanged(sel);
        }

        document.getElementById('modal-division').classList.remove('hidden');
      }

      function openEditDivisionModal(id, classId, name, room, capacity, desc) {
        document.getElementById('division_action').value = 'edit';
        document.getElementById('modal-division-title').textContent = 'Edit Division';
        document.getElementById('modal_division_id').value = id;
        document.getElementById('modal_section_id').value = id;
        document.getElementById('modal_division_class').value = classId;
        onDivisionClassChanged(document.getElementById('modal_division_class'));
        document.getElementById('modal_division_name').value = name;
        document.getElementById('modal_division_room').value = room || '';
        document.getElementById('modal_division_capacity').value = capacity;
        document.getElementById('modal_division_description').value = desc || '';
        document.getElementById('modal-division').classList.remove('hidden');
      }
    </script>
