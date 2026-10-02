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
        <h2 class="font-headline-md text-headline-md text-on-surface">Grade Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure grading scales, percentage ranges, grade points (GPA), and descriptive classifications.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button type="button" onclick="openGradeModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Grade Scale
        </button>
      </div>
    </div>

    <!-- Grade Scales Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Grading Scale (<?php echo count($grades); ?> levels)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-20">Grade</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Min %</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Max %</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Grade Point</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($grades)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No grading scales configured.</td></tr>
            <?php else: ?>
              <?php foreach ($grades as $g): ?>
                <?php
                  $badgeColor = 'bg-primary text-white font-bold';
                  if ($g->grade_name === 'A+' || $g->grade_name === 'A') $badgeColor = 'bg-secondary-container text-on-secondary-container font-bold';
                  elseif ($g->grade_name === 'F') $badgeColor = 'bg-error-container text-on-error-container font-bold';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-body-md <?php echo $badgeColor; ?>">
                      <?php echo html_escape($g->grade_name); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-body-md text-on-surface"><?php echo number_format($g->min_percentage, 2); ?>%</td>
                  <td class="px-4 py-3 text-right font-mono text-body-md text-on-surface"><?php echo number_format($g->max_percentage, 2); ?>%</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-primary text-body-md"><?php echo number_format($g->grade_point, 2); ?></td>
                  <td class="px-4 py-3 text-body-md text-on-surface font-medium"><?php echo html_escape($g->description ?: '—'); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo ($g->status == 1) ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                      <?php echo ($g->status == 1) ? 'Active' : 'Inactive'; ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Edit -->
                      <button type="button" onclick='editGradeModal(<?php echo json_encode($g); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <!-- Delete -->
                      <?php echo form_open('examinations/grades', array('class' => 'inline', 'onsubmit' => 'return confirm("Delete this grade scale?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="grade_id" value="<?php echo $g->grade_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors cursor-pointer" title="Delete">
                          <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                      <?php echo form_close(); ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CREATE / EDIT GRADE MODAL -->
    <div id="grade-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-grade-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">grade</span>Add Grade Scale
          </h3>
          <button onclick="closeGradeModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/grades', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="grade_id" id="modal-grade-id" value="0"/>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Grade Name *</label>
              <input type="text" name="grade_name" id="modal-grade-name" required placeholder="e.g. A+" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Grade Point *</label>
              <input type="number" step="0.1" name="grade_point" id="modal-grade-point" required placeholder="e.g. 10.0" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono"/>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Minimum % *</label>
              <input type="number" step="0.01" min="0" max="100" name="min_percentage" id="modal-grade-min" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Maximum % *</label>
              <input type="number" step="0.01" min="0" max="100" name="max_percentage" id="modal-grade-max" required placeholder="100.00" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono"/>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
            <input type="text" name="description" id="modal-grade-desc" placeholder="e.g. Outstanding, Excellent, Fail" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <label class="flex items-center gap-2 text-body-md text-on-surface cursor-pointer">
            <input type="checkbox" name="status" id="modal-grade-status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            <span>Active Grade Scale</span>
          </label>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeGradeModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Grade
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal Scripts -->
    <script>
      function openGradeModal() {
        document.getElementById('modal-grade-id').value = '0';
        document.getElementById('modal-grade-title').textContent = 'Add Grade Scale';
        document.getElementById('modal-grade-name').value = '';
        document.getElementById('modal-grade-point').value = '9.0';
        document.getElementById('modal-grade-min').value = '80.00';
        document.getElementById('modal-grade-max').value = '89.99';
        document.getElementById('modal-grade-desc').value = '';
        document.getElementById('modal-grade-status').checked = true;

        var modal = document.getElementById('grade-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function editGradeModal(item) {
        document.getElementById('modal-grade-id').value = item.grade_id;
        document.getElementById('modal-grade-title').textContent = 'Edit Grade Scale';
        document.getElementById('modal-grade-name').value = item.grade_name;
        document.getElementById('modal-grade-point').value = item.grade_point;
        document.getElementById('modal-grade-min').value = item.min_percentage;
        document.getElementById('modal-grade-max').value = item.max_percentage;
        document.getElementById('modal-grade-desc').value = item.description || '';
        document.getElementById('modal-grade-status').checked = (item.status == 1);

        var modal = document.getElementById('grade-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeGradeModal() {
        var modal = document.getElementById('grade-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
