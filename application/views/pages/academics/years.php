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
        <h2 class="font-headline-md text-headline-md text-on-surface">Academic Years</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage school academic sessions and configure the active current academic year.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddYearModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Academic Year
        </button>
      </div>
    </div>

    <!-- Academic Years Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Academic Session</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Start Date</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">End Date</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Current Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php foreach ($years as $yr): ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>
                    <?php echo html_escape($yr->year_name); ?>
                  </div>
                </td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($yr->start_date)); ?></td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($yr->end_date)); ?></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <?php if ($yr->is_active == 1): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[12px] font-bold bg-secondary-container text-on-secondary-container">
                      <span class="w-2 h-2 rounded-full bg-secondary"></span>Active Current Year
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Archived / Past</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <?php if ($yr->is_active != 1): ?>
                      <a href="<?php echo site_url('academics/set_active_year/' . $yr->academic_year_id); ?>" class="px-3 py-1 rounded-lg bg-surface-container-high text-primary text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">check_circle</span>Set Active</a>
                    <?php endif; ?>
                    <button onclick="openEditYearModal(<?php echo $yr->academic_year_id; ?>, '<?php echo html_escape(addslashes($yr->year_name)); ?>', '<?php echo $yr->start_date; ?>', '<?php echo $yr->end_date; ?>', <?php echo $yr->is_active; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors cursor-pointer" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                    <a href="<?php echo site_url('academics/delete_year/' . $yr->academic_year_id); ?>" onclick="return confirm('Permanently delete this academic year record?')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Add / Edit Academic Year -->
    <div id="modal-year" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-year-title">Add Academic Year</h3>
          <button onclick="document.getElementById('modal-year').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('academics/years', array('class' => 'p-6 space-y-4', 'id' => 'academic-year-form')); ?>
          <input type="hidden" name="action" id="year_action" value="add"/>
          <input type="hidden" name="academic_year_id" id="modal_year_id"/>
          <div>
            <label class="block text-label-md mb-1">Academic Year Name *</label>
            <input type="text" name="year_name" id="modal_year_name" required placeholder="e.g. 2027 - 2028" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1">Start Date *</label>
              <input type="date" name="start_date" id="modal_start_date" required value="<?php echo date('Y-06-01'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">End Date *</label>
              <input type="date" name="end_date" id="modal_end_date" required value="<?php echo date('Y-03-31', strtotime('+1 year')); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
          <div id="date-range-error" class="hidden text-[12px] text-error flex items-center gap-1 -mt-2">
            <span class="material-symbols-outlined text-[15px]">error</span>
            <span>End Date must be greater than Start Date.</span>
          </div>
          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="modal_is_active" value="1" class="rounded text-primary"/>
            <label for="modal_is_active" class="text-body-md text-on-surface">Set as Current Active Academic Year</label>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('modal-year').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant">Cancel</button>
            <button type="submit" id="btn-save-session" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Save Session</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      let isSubmittingYear = false;

      function resetSubmitButton() {
        isSubmittingYear = false;
        const btn = document.getElementById('btn-save-session');
        if (btn) {
          btn.disabled = false;
          btn.classList.remove('opacity-70', 'cursor-not-allowed');
          btn.textContent = 'Save Session';
        }
      }

      function validateAcademicDates() {
        const startInput = document.getElementById('modal_start_date');
        const endInput = document.getElementById('modal_end_date');
        const errorEl = document.getElementById('date-range-error');
        if (!startInput || !endInput) return true;

        const startVal = startInput.value;
        const endVal = endInput.value;

        if (startVal && endVal) {
          if (endVal <= startVal) {
            endInput.classList.add('border-error');
            startInput.classList.add('border-error');
            if (errorEl) errorEl.classList.remove('hidden');
            return false;
          }
        }

        endInput.classList.remove('border-error');
        startInput.classList.remove('border-error');
        if (errorEl) errorEl.classList.add('hidden');
        return true;
      }

      function openAddYearModal() {
        document.getElementById('year_action').value = 'add';
        document.getElementById('modal-year-title').textContent = 'Add Academic Year';
        document.getElementById('modal_year_id').value = '';
        document.getElementById('modal_year_name').value = '';
        document.getElementById('modal_is_active').checked = false;
        resetSubmitButton();
        validateAcademicDates();
        document.getElementById('modal-year').classList.remove('hidden');
      }

      function openEditYearModal(id, name, start, end, isActive) {
        document.getElementById('year_action').value = 'edit';
        document.getElementById('modal-year-title').textContent = 'Edit Academic Year';
        document.getElementById('modal_year_id').value = id;
        document.getElementById('modal_year_name').value = name;
        document.getElementById('modal_start_date').value = start;
        document.getElementById('modal_end_date').value = end;
        document.getElementById('modal_is_active').checked = (isActive == 1);
        resetSubmitButton();
        validateAcademicDates();
        document.getElementById('modal-year').classList.remove('hidden');
      }

      document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('modal_start_date');
        const endInput = document.getElementById('modal_end_date');

        if (startInput) {
          startInput.addEventListener('change', validateAcademicDates);
          startInput.addEventListener('input', validateAcademicDates);
        }
        if (endInput) {
          endInput.addEventListener('change', validateAcademicDates);
          endInput.addEventListener('input', validateAcademicDates);
        }

        const form = document.getElementById('academic-year-form') || document.querySelector('#modal-year form');
        if (form) {
          form.addEventListener('submit', function(e) {
            if (!validateAcademicDates()) {
              e.preventDefault();
              return false;
            }

            if (isSubmittingYear) {
              e.preventDefault();
              return false;
            }
            isSubmittingYear = true;
            const btn = document.getElementById('btn-save-session');
            if (btn) {
              btn.classList.add('opacity-70', 'cursor-not-allowed');
              btn.innerHTML = '<span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>Saving...</span>';
            }
          });
        }
      });
    </script>
