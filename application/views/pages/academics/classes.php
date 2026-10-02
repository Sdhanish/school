<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20" data-testid="flash-success">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20" data-testid="flash-error">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="font-headline-md text-headline-md text-on-surface font-bold">Classes & Divisions</h1>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Manage academic classes and their divisions from one place.</p>
      </div>
      <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
        <a href="<?php echo site_url('academics/academic_groups'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-label-md hover:bg-surface-container-high transition-colors shadow-2xs cursor-pointer font-semibold">
          <span class="material-symbols-outlined text-[18px] text-on-surface-variant">category</span>Manage Department / Groups
        </a>
        <button type="button" onclick="openAddClassModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-2xs cursor-pointer font-semibold" data-testid="btn-add-class">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Class & Divisions
        </button>
      </div>
    </div>

    <!-- Classes Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-left" data-testid="classes-table">
          <thead>
            <tr class="bg-surface-container-low/70 border-b border-outline-variant/60">
              <th class="text-left px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Department / Group</th>
              <th class="text-left px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Class Name</th>
              <th class="text-left px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Class Code</th>
              <th class="text-center px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Academic Session</th>
              <th class="text-center px-2 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Capacity</th>
              <th class="text-center px-2 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Enrolled</th>
              <th class="text-left px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Divisions & Teachers</th>
              <th class="text-right px-3 py-2.5 text-[11px] font-semibold tracking-wider text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($classes)): ?>
              <tr>
                <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-outline-variant text-[40px]">school</span>
                    <p class="font-medium text-on-surface">No classes configured</p>
                    <p class="text-xs text-on-surface-variant max-w-sm">Click "Add Class & Divisions" above to create your first class and configure its divisions.</p>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($classes as $cls): ?>
                <tr class="hover:bg-surface-container-low/60 transition-colors" data-testid="class-row-<?php echo $cls->class_id; ?>">
                  <!-- Academic Group -->
                  <td class="px-3 py-2.5 whitespace-nowrap align-middle">
                    <?php if (!empty($cls->group_name)): ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20 tracking-wide">
                        <?php echo html_escape($cls->group_name); ?>
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container-high text-on-surface-variant border border-outline-variant/40">
                        Unassigned
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- Class Name -->
                  <td class="px-3 py-2.5 whitespace-nowrap align-middle">
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-md bg-primary/5 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-[16px]">school</span>
                      </div>
                      <div class="min-w-0">
                        <span class="text-on-surface font-semibold text-body-md block truncate max-w-[190px]" title="<?php echo html_escape($cls->class_name); ?>" data-testid="class-name-<?php echo $cls->class_id; ?>"><?php echo html_escape($cls->class_name); ?></span>
                        <?php if (!empty($cls->description)): ?>
                          <span class="text-[11px] text-on-surface-variant/80 font-normal block truncate max-w-[170px]" title="<?php echo html_escape($cls->description); ?>"><?php echo html_escape($cls->description); ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>

                  <!-- Class Code -->
                  <td class="px-3 py-2.5 whitespace-nowrap align-middle">
                    <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-surface-container-high/60 text-primary border border-outline-variant/40 inline-block" data-testid="class-code-<?php echo $cls->class_id; ?>">
                      <?php echo html_escape($cls->class_code); ?>
                    </span>
                  </td>

                  <!-- Academic Session -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-center align-middle text-on-surface text-body-md">
                    <?php echo html_escape($cls->year_name ?: ($active_academic_year->year_name ?? '—')); ?>
                  </td>

                  <!-- Capacity -->
                  <td class="px-2 py-2.5 whitespace-nowrap text-center align-middle text-on-surface-variant text-body-md font-medium">
                    <?php echo (int)$cls->capacity; ?> <span class="text-xs text-on-surface-variant/70">seats</span>
                  </td>

                  <!-- Enrolled Students -->
                  <td class="px-2 py-2.5 whitespace-nowrap text-center align-middle">
                    <a href="<?php echo site_url('students?class_id=' . $cls->class_id); ?>" class="inline-flex items-center justify-center gap-1 font-semibold text-secondary hover:underline text-body-md" data-testid="class-students-<?php echo $cls->class_id; ?>" title="View enrolled students">
                      <span class="font-bold text-[14px]"><?php echo isset($cls->student_count) ? $cls->student_count : 0; ?></span>
                      <span class="text-xs text-on-surface-variant font-normal">students</span>
                    </a>
                  </td>

                  <!-- Divisions -->
                  <td class="px-3 py-2.5 align-middle">
                    <div class="flex items-center gap-1.5 flex-nowrap" data-testid="class-divisions-<?php echo $cls->class_id; ?>">
                      <?php if (!empty($cls->divisions)): ?>
                        <?php
                          $div_count = count($cls->divisions);
                          $visible_limit = 3;
                          $visible_divs = array_slice($cls->divisions, 0, $visible_limit);
                          $overflow_divs = array_slice($cls->divisions, $visible_limit);
                        ?>
                        <?php foreach ($visible_divs as $d): ?>
                          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-surface-container-high/80 border border-outline-variant/40 text-on-surface text-xs font-medium hover:bg-surface-container-highest transition-colors whitespace-nowrap" title="Division <?php echo html_escape($d->division_name); ?><?php echo !empty($d->class_teacher_name) ? ' &bull; Class Teacher: ' . html_escape($d->class_teacher_name) . (!empty($d->class_teacher_code) ? ' (' . html_escape($d->class_teacher_code) . ')' : '') : ' &bull; No Class Teacher'; ?> &bull; <?php echo (int)($d->student_count ?? 0); ?> students enrolled" data-testid="div-pill-<?php echo $cls->class_id; ?>-<?php echo html_escape($d->division_name); ?>">
                            <span class="sr-only">Division </span>
                            <span class="font-bold text-on-surface"><?php echo html_escape($d->division_name); ?></span>
                            <?php if (!empty($d->class_teacher_name)): ?>
                              <span class="text-outline/60 text-[10px]">&middot;</span>
                              <span class="text-secondary font-semibold text-[11px] inline-flex items-center gap-0.5" title="Class Teacher: <?php echo html_escape($d->class_teacher_name); ?>">
                                <span class="material-symbols-outlined text-[13px] leading-none text-secondary">person</span>
                                <span class="max-w-[75px] truncate"><?php echo html_escape($d->class_teacher_name); ?></span>
                              </span>
                            <?php endif; ?>
                            <span class="text-outline/60 text-[10px]">&middot;</span>
                            <span class="text-on-surface-variant font-medium text-[11px]"><?php echo (int)($d->student_count ?? 0); ?></span>
                          </span>
                        <?php endforeach; ?>

                        <?php if (!empty($overflow_divs)): ?>
                          <div class="relative inline-block text-left">
                            <button type="button" onclick="toggleDivisionPopover(event, 'popover-divs-<?php echo $cls->class_id; ?>')" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-secondary/10 hover:bg-secondary/20 text-secondary border border-secondary/30 text-[11px] font-semibold transition-colors cursor-pointer whitespace-nowrap shadow-2xs" title="View <?php echo count($overflow_divs); ?> more divisions">
                              +<?php echo count($overflow_divs); ?> more
                            </button>
                            <div id="popover-divs-<?php echo $cls->class_id; ?>" class="division-popover hidden absolute right-0 sm:right-auto sm:left-0 top-full mt-1.5 z-40 w-80 p-3 rounded-xl bg-surface-container-lowest border border-outline-variant/70 shadow-xl text-left">
                              <div class="flex items-center justify-between pb-2 mb-2 border-b border-outline-variant/40">
                                <div class="flex items-center gap-1.5 min-w-0">
                                  <span class="text-[11px] font-bold text-on-surface uppercase tracking-wider whitespace-nowrap">More Divisions</span>
                                  <span class="text-[10px] text-on-surface-variant font-medium bg-surface-container-high px-1.5 py-0.5 rounded truncate max-w-[120px]" title="<?php echo html_escape($cls->class_name); ?>"><?php echo html_escape($cls->class_name); ?></span>
                                </div>
                                <button type="button" onclick="closeAllDivisionPopovers()" class="p-0.5 rounded hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer" title="Close">
                                  <span class="material-symbols-outlined text-[14px]">close</span>
                                </button>
                              </div>
                              <div class="flex items-center gap-1.5 flex-wrap max-h-48 overflow-y-auto pr-1">
                                <?php foreach ($overflow_divs as $d): ?>
                                  <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-surface-container-high/80 border border-outline-variant/40 text-on-surface text-xs font-medium hover:bg-surface-container-highest transition-colors whitespace-nowrap" title="Division <?php echo html_escape($d->division_name); ?><?php echo !empty($d->class_teacher_name) ? ' &bull; Class Teacher: ' . html_escape($d->class_teacher_name) : ' &bull; No Class Teacher'; ?> &bull; <?php echo (int)($d->student_count ?? 0); ?> students enrolled" data-testid="div-pill-<?php echo $cls->class_id; ?>-<?php echo html_escape($d->division_name); ?>">
                                    <span class="sr-only">Division </span>
                                    <span class="font-bold text-on-surface"><?php echo html_escape($d->division_name); ?></span>
                                    <?php if (!empty($d->class_teacher_name)): ?>
                                      <span class="text-outline/60 text-[10px]">&middot;</span>
                                      <span class="text-secondary font-semibold text-[11px] inline-flex items-center gap-0.5" title="Class Teacher: <?php echo html_escape($d->class_teacher_name); ?>">
                                        <span class="material-symbols-outlined text-[13px] leading-none text-secondary">person</span>
                                        <span class="max-w-[75px] truncate"><?php echo html_escape($d->class_teacher_name); ?></span>
                                      </span>
                                    <?php endif; ?>
                                    <span class="text-outline/60 text-[10px]">&middot;</span>
                                    <span class="text-on-surface-variant font-medium text-[11px]"><?php echo (int)($d->student_count ?? 0); ?></span>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-on-surface-variant/70 italic bg-surface-container-low border border-outline-variant/30">No divisions</span>
                      <?php endif; ?>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-right align-middle">
                    <div class="flex items-center justify-end gap-1.5">
                      <button type="button" onclick='openEditClassModal(<?php echo htmlspecialchars(json_encode($cls), ENT_QUOTES, "UTF-8"); ?>)' class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-semibold transition-colors border border-outline-variant/50 cursor-pointer shadow-2xs" title="Manage Class & Divisions" data-testid="btn-manage-class-<?php echo $cls->class_id; ?>">
                        <span class="material-symbols-outlined text-[15px] text-secondary">edit</span>Manage
                      </button>
                      <a href="<?php echo site_url('academics/delete_class/' . $cls->class_id); ?>" onclick="return confirm('Deactivate this class and its divisions? Classes with students or active records cannot be deactivated.')" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-on-surface-variant hover:bg-error-container/30 hover:text-error transition-colors cursor-pointer" title="Deactivate" data-testid="btn-delete-class-<?php echo $cls->class_id; ?>">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Unified Modal: Add / Edit Class & Divisions -->
    <div id="modal-class" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant bg-surface-container-low shrink-0">
          <div>
            <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-class-title" data-testid="modal-title">Add Class & Divisions</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Configure class specifications and its associated divisions in one single workflow.</p>
          </div>
          <button type="button" onclick="closeClassModal()" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer" data-testid="btn-close-modal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <?php echo form_open('academics/classes', array('id' => 'class_division_form', 'class' => 'p-6 space-y-4 overflow-y-auto flex-1', 'onsubmit' => 'return handleFormSubmit(event)')); ?>
          <input type="hidden" name="action" id="class_action" value="add"/>
          <input type="hidden" name="class_id" id="modal_class_id"/>
          <input type="hidden" name="deleted_division_ids" id="deleted_division_ids" value=""/>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-label-md mb-1 text-on-surface font-semibold">Academic Session *</label>
              <select name="academic_year_id" id="modal_class_year" required onchange="onModalClassYearChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="select-academic-year">
                <?php foreach ($years as $yr): ?>
                  <option value="<?php echo $yr->academic_year_id; ?>" <?php echo (!empty($yr->is_active)) ? 'selected' : ''; ?>><?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-label-md mb-1 text-on-surface font-semibold">Department / Group *</label>
              <select name="academic_group_id" id="modal_academic_group_id" required onchange="onAcademicGroupChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="select-academic-group">
                <option value="">Select Department / Group</option>
                <?php if (!empty($groups)): ?>
                  <?php foreach ($groups as $grp): ?>
                    <option value="<?php echo $grp->academic_group_id; ?>"><?php echo html_escape($grp->group_name); ?> (<?php echo html_escape($grp->description ?: 'Group'); ?>)</option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface font-semibold">Class Name *</label>
            <div class="flex flex-col sm:flex-row gap-2">
              <select id="modal_class_select" onchange="onClassOptionSelected(this.value)" class="w-full sm:w-1/2 px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="select-standard-class">
                <option value="">-- Choose Standard Class --</option>
              </select>
              <input type="text" name="class_name" id="modal_class_name" required placeholder="Or enter custom class name" oninput="onClassNameInput(this.value)" class="w-full sm:w-1/2 px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-semibold text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="input-class-name"/>
            </div>
            <p class="text-xs text-on-surface-variant mt-1">Select a preconfigured standard class name or type a custom one.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-label-md mb-1 text-on-surface font-semibold">Class Code *</label>
              <input type="text" name="class_code" id="modal_class_code" required placeholder="e.g. CLS-LKG" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest uppercase text-on-surface font-mono text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="input-class-code"/>
            </div>
            <div>
              <label class="block text-label-md mb-1 text-on-surface font-semibold">Class Max Capacity *</label>
              <input type="number" name="capacity" id="modal_class_capacity" value="40" min="1" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="input-class-capacity"/>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 text-on-surface font-semibold">Description / Notes</label>
            <textarea name="description" id="modal_class_description" rows="2" placeholder="Optional notes about curriculum, section tracks, or specializations..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary" data-testid="input-class-description"></textarea>
          </div>

          <!-- Divisions Management Section -->
          <div class="pt-3 border-t border-outline-variant/60">
            <div class="flex items-center justify-between mb-2">
              <div>
                <label class="block text-label-md text-on-surface font-semibold">Divisions & Class Teachers *</label>
                <p class="text-xs text-on-surface-variant">Configure divisions and optionally assign a class teacher to each division.</p>
              </div>
              <button type="button" onclick="addDivisionRow('', '', 0, '')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 text-label-md font-semibold cursor-pointer transition-colors" data-testid="btn-add-division">
                <span class="material-symbols-outlined text-[16px]">add</span>Add Division
              </button>
            </div>

            <!-- Frontend Division Validation Error Message -->
            <div id="division-validation-error" class="hidden mb-2 p-2.5 rounded-lg bg-error-container text-on-error-container text-xs font-semibold flex items-center gap-1.5" data-testid="division-validation-error">
              <span class="material-symbols-outlined text-[16px] text-error">warning</span>
              <span id="division-validation-error-text"></span>
            </div>

            <!-- Division Rows Container -->
            <div id="division_rows_container" class="space-y-2 max-h-56 overflow-y-auto pr-1" data-testid="division-rows-container">
              <!-- Dynamically populated rows -->
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant bg-surface-container-lowest sticky bottom-0">
            <button type="button" onclick="closeClassModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high cursor-pointer text-label-md font-medium" data-testid="btn-cancel-modal">Cancel</button>
            <button type="submit" id="btn_submit_class" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer shadow-sm font-semibold inline-flex items-center gap-1.5" data-testid="btn-save-class">
              <span class="material-symbols-outlined text-[18px]">check</span>
              <span id="btn_submit_class_text">Create Class & Divisions</span>
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      let deletedDivisionIds = [];
      const AVAILABLE_TEACHERS = <?php echo json_encode(array_values(array_map(function($t) {
        return [
          'staff_id'          => (string)$t->staff_id,
          'name'              => (string)$t->full_name,
          'code'              => (string)($t->employee_code ?? ''),
          'academic_group_id' => (string)($t->academic_group_id ?? ''),
          'group_name'        => (string)($t->group_name ?? '')
        ];
      }, $teachers ?? []))); ?>;

      let ACTIVE_TEACHER_ASSIGNMENTS = <?php echo json_encode(array_values(array_map(function($a) {
        return [
          'staff_id'      => (string)$a->staff_id,
          'class_id'      => (string)$a->class_id,
          'division_id'   => (string)$a->division_id,
          'class_name'    => (string)$a->class_name,
          'division_name' => (string)$a->division_name,
          'teacher_name'  => (string)$a->teacher_name,
        ];
      }, $active_assignments ?? []))); ?>;

      function escapeHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function getNextDivisionLetter() {
        const inputs = document.querySelectorAll('#division_rows_container input[name="division_names[]"]');
        const existing = [];
        inputs.forEach(inp => {
          const val = inp.value.trim().toUpperCase();
          if (val) existing.push(val);
        });
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        for (let i = 0; i < alphabet.length; i++) {
          if (!existing.includes(alphabet[i])) {
            return alphabet[i];
          }
        }
        return 'A' + (existing.length + 1);
      }

      function getModalTeacherSelections() {
        const rows = document.querySelectorAll('#division_rows_container [data-testid="division-row"]');
        const selections = [];
        rows.forEach(r => {
          const sel = r.querySelector('select[name="division_teacher_ids[]"]');
          const nameInput = r.querySelector('input[name="division_names[]"]');
          const divIdInput = r.querySelector('input[name="division_ids[]"]');
          if (sel && sel.value) {
            selections.push({
              rowEl: r,
              staffId: String(sel.value),
              divName: (nameInput && nameInput.value.trim().toUpperCase()) || 'Div',
              divId: (divIdInput && divIdInput.value) ? String(divIdInput.value) : ''
            });
          }
        });
        return selections;
      }

      function updateDivisionTeacherOptions() {
        const rows = document.querySelectorAll('#division_rows_container [data-testid="division-row"]');
        const modalSelections = getModalTeacherSelections();

        rows.forEach(r => {
          const sel = r.querySelector('select[name="division_teacher_ids[]"]');
          const divIdInput = r.querySelector('input[name="division_ids[]"]');
          if (!sel) return;

          const currentDivId = divIdInput ? String(divIdInput.value || '') : '';
          const currentVal = String(sel.value || '');

          Array.from(sel.options).forEach(opt => {
            const val = opt.value;
            if (!val) {
              opt.disabled = false;
              return;
            }

            const teacherObj = AVAILABLE_TEACHERS.find(t => String(t.staff_id) === String(val));
            const tName = teacherObj ? teacherObj.name : 'Teacher';
            const tCode = teacherObj ? teacherObj.code : '';

            // 1. Check if assigned in DB for this academic year to ANOTHER class
            const currentModalClassId = (document.getElementById('modal_class_id') && document.getElementById('modal_class_id').value) || '';
            const dbAssigned = ACTIVE_TEACHER_ASSIGNMENTS.find(a => String(a.staff_id) === String(val));
            const isAssignedToThisClass = dbAssigned && currentModalClassId && String(dbAssigned.class_id) === String(currentModalClassId);

            // 2. Check if selected in another row in the same modal
            const selectedOther = modalSelections.find(s => s.staffId === String(val) && s.rowEl !== r);

            if (dbAssigned && !isAssignedToThisClass) {
              opt.disabled = true;
              opt.textContent = `${tName} [${tCode}] — Assigned to ${dbAssigned.class_name} ${dbAssigned.division_name}`;
            } else if (selectedOther) {
              opt.disabled = true;
              opt.textContent = `${tName} [${tCode}] — Selected for Div ${selectedOther.divName}`;
            } else {
              opt.disabled = false;
              opt.textContent = `${tName} [${tCode}]`;
            }
          });
        });
      }

      function onDivisionTeacherChange() {
        updateDivisionTeacherOptions();
        validateDivisionInputs();
      }

      function onModalClassYearChange(yearId) {
        if (!yearId) return;
        fetch('<?php echo site_url("academics/ajax_get_class_teacher_assignments"); ?>?academic_year_id=' + yearId)
          .then(res => res.json())
          .then(data => {
            if (data && data.assignments) {
              ACTIVE_TEACHER_ASSIGNMENTS = data.assignments.map(a => ({
                staff_id: String(a.staff_id),
                class_id: String(a.class_id),
                division_id: String(a.division_id),
                class_name: String(a.class_name),
                division_name: String(a.division_name),
                teacher_name: String(a.teacher_name || '')
              }));
              updateDivisionTeacherOptions();
              validateDivisionInputs();
            }
          })
          .catch(err => console.error('Error loading teacher assignments:', err));
      }

      let CURRENT_GROUP_TEACHERS = [];

      function onAcademicGroupChange(groupId) {
        loadGroupClasses(groupId);
        loadGroupTeachers(groupId, false);
      }

      function loadGroupTeachers(groupId, preserveSelections = false) {
        const container = document.getElementById('division_rows_container');
        const selects = container ? container.querySelectorAll('select[name="division_teacher_ids[]"]') : [];

        if (!groupId) {
          CURRENT_GROUP_TEACHERS = [];
          selects.forEach(sel => {
            sel.innerHTML = '<option value="">-- Select Department / Group first --</option>';
            sel.value = '';
            sel.disabled = true;
          });
          updateDivisionTeacherOptions();
          validateDivisionInputs();
          return;
        }

        // Show loading and disable selects while fetching
        selects.forEach(sel => {
          sel.disabled = true;
          if (!preserveSelections) {
            sel.innerHTML = '<option value="">Loading faculty...</option>';
          }
        });

        fetch('<?php echo site_url("academics/ajax_get_teachers_by_group"); ?>?academic_group_id=' + encodeURIComponent(groupId))
          .then(res => res.json())
          .then(data => {
            CURRENT_GROUP_TEACHERS = (data && data.teachers) ? data.teachers : [];
            const currentSelects = container ? container.querySelectorAll('select[name="division_teacher_ids[]"]') : [];
            currentSelects.forEach(sel => {
              const previousVal = preserveSelections ? (sel.getAttribute('data-initial-val') || sel.value) : '';
              let html = '<option value="">-- Assign Class Teacher (Optional) --</option>';
              if (CURRENT_GROUP_TEACHERS && CURRENT_GROUP_TEACHERS.length > 0) {
                CURRENT_GROUP_TEACHERS.forEach(t => {
                  const isSel = (previousVal && String(previousVal) === String(t.staff_id)) ? 'selected' : '';
                  html += `<option value="${t.staff_id}" ${isSel}>${escapeHtml(t.full_name)} [${escapeHtml(t.employee_code || '')}]</option>`;
                });
              } else {
                html = '<option value="">No teaching faculty in this Department / Group</option>';
              }
              sel.innerHTML = html;
              sel.disabled = false;
              if (previousVal && !CURRENT_GROUP_TEACHERS.some(t => String(t.staff_id) === String(previousVal))) {
                sel.value = '';
              }
            });
            updateDivisionTeacherOptions();
            validateDivisionInputs();
          })
          .catch(err => {
            console.error('Error loading group teachers:', err);
            selects.forEach(sel => {
              sel.disabled = false;
            });
          });
      }

      function addDivisionRow(divId = '', divName = '', studentCount = 0, teacherId = '') {
        const container = document.getElementById('division_rows_container');
        if (!divName) {
          divName = getNextDivisionLetter();
        }

        const rowId = 'div_row_' + Math.random().toString(36).substr(2, 9);
        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'flex flex-col sm:flex-row sm:items-center gap-2 p-2.5 rounded-lg border border-outline-variant/60 bg-surface-container-low transition-all';
        row.setAttribute('data-testid', 'division-row');

        let studentBadge = '';
        if (divId && studentCount > 0) {
          studentBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-secondary/10 text-secondary border border-secondary/20 shrink-0" title="${studentCount} students enrolled" data-testid="badge-students-${divId}">${studentCount} st</span>`;
        }

        const grpEl = document.getElementById('modal_academic_group_id');
        const selectedGroupId = grpEl ? grpEl.value : '';

        let teacherOptions = '';
        let isSelectDisabled = false;

        if (!selectedGroupId) {
          teacherOptions = '<option value="">-- Select Department / Group first --</option>';
          isSelectDisabled = true;
        } else {
          teacherOptions = '<option value="">-- Assign Class Teacher (Optional) --</option>';
          const pool = (CURRENT_GROUP_TEACHERS && CURRENT_GROUP_TEACHERS.length > 0)
            ? CURRENT_GROUP_TEACHERS
            : (AVAILABLE_TEACHERS || []).filter(t => t.academic_group_id && String(t.academic_group_id) === String(selectedGroupId));

          if (pool && pool.length > 0) {
            pool.forEach(t => {
              const sid = t.staff_id;
              const sname = t.full_name || t.name;
              const scode = t.employee_code || t.code || '';
              const isSel = (teacherId && String(teacherId) === String(sid)) ? 'selected' : '';
              teacherOptions += `<option value="${sid}" ${isSel}>${escapeHtml(sname)} [${escapeHtml(scode)}]</option>`;
            });
          } else {
            teacherOptions = '<option value="">No teaching faculty in this Department / Group</option>';
          }
        }

        row.innerHTML = `
          <input type="hidden" name="division_ids[]" value="${divId ? divId : ''}">
          <div class="w-full sm:w-28 shrink-0 flex items-center gap-1.5">
            <span class="text-label-md font-semibold text-on-surface-variant shrink-0">Div:</span>
            <input type="text" name="division_names[]" value="${escapeHtml(divName)}" required placeholder="e.g. A" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-bold text-body-md focus:border-secondary focus:ring-1 focus:ring-secondary uppercase text-center" data-testid="input-division-name" oninput="onDivisionNameChange()">
          </div>
          <div class="flex-1 min-w-0 flex items-center gap-1.5">
            <span class="text-xs font-medium text-on-surface-variant shrink-0 sm:hidden">Teacher:</span>
            <select name="division_teacher_ids[]" data-initial-val="${teacherId ? escapeHtml(teacherId) : ''}" ${isSelectDisabled ? 'disabled' : ''} onchange="onDivisionTeacherChange()" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-sm focus:border-secondary focus:ring-1 focus:ring-secondary disabled:opacity-50 disabled:cursor-not-allowed" data-testid="select-division-teacher">
              ${teacherOptions}
            </select>
          </div>
          <div class="flex items-center justify-end gap-1.5 shrink-0 self-end sm:self-center">
            ${studentBadge}
            <button type="button" onclick="removeDivisionRow('${rowId}', '${divId}', ${studentCount})" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/30 hover:text-error transition-colors shrink-0 cursor-pointer" title="${studentCount > 0 ? 'Warning: Contains ' + studentCount + ' enrolled students' : 'Delete division'}" data-testid="btn-delete-division-row">
              <span class="material-symbols-outlined text-[18px]">delete</span>
            </button>
          </div>
        `;
        container.appendChild(row);
        updateDivisionTeacherOptions();
        validateDivisionInputs();
      }

      function onDivisionNameChange() {
        updateDivisionTeacherOptions();
        validateDivisionInputs();
      }

      function removeDivisionRow(rowId, divId, studentCount) {
        const container = document.getElementById('division_rows_container');
        const rows = container.querySelectorAll('[data-testid="division-row"]');
        if (rows.length <= 1) {
          showDivisionError('A class must have at least one division.');
          return;
        }

        if (studentCount > 0) {
          alert(`Notice: This division has ${studentCount} enrolled students. Deleting it will be blocked by the server to prevent data loss.`);
        }

        if (divId) {
          deletedDivisionIds.push(divId);
          document.getElementById('deleted_division_ids').value = deletedDivisionIds.join(',');
        }

        const row = document.getElementById(rowId);
        if (row) {
          row.remove();
        }
        updateDivisionTeacherOptions();
        validateDivisionInputs();
      }

      function showDivisionError(msg) {
        const errBox = document.getElementById('division-validation-error');
        const errText = document.getElementById('division-validation-error-text');
        if (msg) {
          errText.textContent = msg;
          errBox.classList.remove('hidden');
        } else {
          errText.textContent = '';
          errBox.classList.add('hidden');
        }
      }

      function validateDivisionInputs() {
        const container = document.getElementById('division_rows_container');
        const nameInputs = container.querySelectorAll('input[name="division_names[]"]');
        const names = [];
        let hasEmpty = false;
        let hasDuplicate = false;

        if (nameInputs.length === 0) {
          showDivisionError('A class must have at least one division.');
          return false;
        }

        nameInputs.forEach(inp => {
          const val = inp.value.trim().toUpperCase();
          if (!val) {
            hasEmpty = true;
          } else {
            if (names.includes(val)) {
              hasDuplicate = true;
            }
            names.push(val);
          }
        });

        if (hasEmpty) {
          showDivisionError('Division names cannot be blank.');
          return false;
        }

        if (hasDuplicate) {
          showDivisionError('Duplicate division names are not allowed within the same class.');
          return false;
        }

        // Validate teacher selections: Intra-modal and Cross-class uniqueness
        const modalSelections = getModalTeacherSelections();
        const seenStaff = [];
        for (let i = 0; i < modalSelections.length; i++) {
          const s = modalSelections[i];
          const tObj = AVAILABLE_TEACHERS.find(t => String(t.staff_id) === String(s.staffId));
          const tName = tObj ? tObj.name : 'Teacher';

          // Intra-modal duplicate
          if (seenStaff.includes(s.staffId)) {
            showDivisionError(`${tName} can be assigned as Class Teacher to only one division for this academic year.`);
            return false;
          }
          seenStaff.push(s.staffId);

          // Cross-class duplicate
          const currentModalClassId = (document.getElementById('modal_class_id') && document.getElementById('modal_class_id').value) || '';
          const dbAssigned = ACTIVE_TEACHER_ASSIGNMENTS.find(a => String(a.staff_id) === String(s.staffId));
          const isAssignedToThisClass = dbAssigned && currentModalClassId && String(dbAssigned.class_id) === String(currentModalClassId);

          if (dbAssigned && !isAssignedToThisClass) {
            showDivisionError(`${tName} is already assigned as Class Teacher to ${dbAssigned.class_name} - Division ${dbAssigned.division_name}.`);
            return false;
          }
        }

        showDivisionError('');
        return true;
      }

      function handleFormSubmit(e) {
        if (!validateDivisionInputs()) {
          e.preventDefault();
          return false;
        }
        return true;
      }

      function loadGroupClasses(groupId, selectedClassName = '') {
        const select = document.getElementById('modal_class_select');
        const nameInput = document.getElementById('modal_class_name');
        
        if (!groupId) {
          select.innerHTML = '<option value="">-- Choose Standard Class --</option>';
          return;
        }

        select.innerHTML = '<option value="">Loading standard classes...</option>';

        fetch('<?php echo site_url("academics/ajax_get_group_classes"); ?>?academic_group_id=' + groupId)
          .then(res => res.json())
          .then(data => {
            select.innerHTML = '<option value="">-- Choose Standard Class --</option>';
            if (data.classes && data.classes.length > 0) {
              data.classes.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.class_name;
                opt.textContent = c.class_name;
                if (selectedClassName && selectedClassName === c.class_name) {
                  opt.selected = true;
                }
                select.appendChild(opt);
              });
            }
          })
          .catch(err => {
            console.error('Error loading group classes:', err);
            select.innerHTML = '<option value="">-- Choose Standard Class --</option>';
          });
      }

      function onClassOptionSelected(val) {
        if (val) {
          document.getElementById('modal_class_name').value = val;
          onClassNameInput(val);
        }
      }

      function onClassNameInput(val) {
        const codeInput = document.getElementById('modal_class_code');
        if (!codeInput.value || codeInput.value.startsWith('CLS-')) {
          const clean = val.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
          codeInput.value = clean ? 'CLS-' + clean : '';
        }
      }

      function closeClassModal() {
        document.getElementById('modal-class').classList.add('hidden');
      }

      function openAddClassModal() {
        deletedDivisionIds = [];
        CURRENT_GROUP_TEACHERS = [];
        document.getElementById('deleted_division_ids').value = '';
        document.getElementById('class_action').value = 'add';
        document.getElementById('modal-class-title').textContent = 'Add Class & Divisions';
        document.getElementById('btn_submit_class_text').textContent = 'Create Class & Divisions';
        document.getElementById('modal_class_id').value = '';
        document.getElementById('modal_academic_group_id').value = '';
        document.getElementById('modal_class_name').value = '';
        document.getElementById('modal_class_code').value = '';
        document.getElementById('modal_class_capacity').value = '40';
        document.getElementById('modal_class_description').value = '';
        document.getElementById('modal_class_select').innerHTML = '<option value="">-- Choose Standard Class --</option>';
        var activeYr = window.ACTIVE_ACADEMIC_YEAR_ID || window.CURRENT_ACADEMIC_YEAR_ID;
        if (activeYr && document.getElementById('modal_class_year')) {
          document.getElementById('modal_class_year').value = activeYr;
        }
        showDivisionError('');

        // Initialize with default Division A
        const container = document.getElementById('division_rows_container');
        container.innerHTML = '';
        addDivisionRow('', 'A', 0, '');

        if (document.getElementById('modal_class_year')) {
          onModalClassYearChange(document.getElementById('modal_class_year').value);
        }

        document.getElementById('modal-class').classList.remove('hidden');
      }

      function openEditClassModal(cls) {
        deletedDivisionIds = [];
        CURRENT_GROUP_TEACHERS = [];
        document.getElementById('deleted_division_ids').value = '';
        document.getElementById('class_action').value = 'edit';
        document.getElementById('modal-class-title').textContent = 'Edit Class & Divisions';
        document.getElementById('btn_submit_class_text').textContent = 'Save Changes';
        document.getElementById('modal_class_id').value = cls.class_id;
        document.getElementById('modal_class_name').value = cls.class_name;
        document.getElementById('modal_class_code').value = cls.class_code;
        document.getElementById('modal_class_capacity').value = cls.capacity || 40;
        document.getElementById('modal_class_description').value = cls.description || '';
        showDivisionError('');

        if (cls.academic_year_id) {
          document.getElementById('modal_class_year').value = cls.academic_year_id;
        }

        // Populate existing divisions
        const container = document.getElementById('division_rows_container');
        container.innerHTML = '';

        if (cls.divisions && cls.divisions.length > 0) {
          cls.divisions.forEach(d => {
            addDivisionRow(d.division_id, d.division_name, parseInt(d.student_count || 0, 10), d.class_teacher_id || '');
          });
        } else {
          addDivisionRow('', 'A', 0, '');
        }

        if (cls.academic_group_id) {
          document.getElementById('modal_academic_group_id').value = cls.academic_group_id;
          loadGroupClasses(cls.academic_group_id, cls.class_name);
          loadGroupTeachers(cls.academic_group_id, true);
        } else {
          document.getElementById('modal_academic_group_id').value = '';
          CURRENT_GROUP_TEACHERS = [];
          document.getElementById('modal_class_select').innerHTML = '<option value="">-- Choose Standard Class --</option>';
        }

        if (document.getElementById('modal_class_year')) {
          onModalClassYearChange(document.getElementById('modal_class_year').value);
        }

        document.getElementById('modal-class').classList.remove('hidden');
      }

      function toggleDivisionPopover(event, id) {
        event.stopPropagation();
        const popover = document.getElementById(id);
        if (!popover) return;
        const isHidden = popover.classList.contains('hidden');
        closeAllDivisionPopovers();
        if (isHidden) {
          popover.classList.remove('hidden');
          const rect = popover.getBoundingClientRect();
          if (rect.bottom > window.innerHeight) {
            popover.classList.remove('top-full', 'mt-1.5');
            popover.classList.add('bottom-full', 'mb-1.5');
          }
        }
      }

      function closeAllDivisionPopovers() {
        document.querySelectorAll('.division-popover').forEach(el => {
          el.classList.add('hidden');
          el.classList.remove('bottom-full', 'mb-1.5');
          el.classList.add('top-full', 'mt-1.5');
        });
      }

      document.addEventListener('click', function(e) {
        if (!e.target.closest('.division-popover')) {
          closeAllDivisionPopovers();
        }
      });

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeAllDivisionPopovers();
        }
      });
    </script>
