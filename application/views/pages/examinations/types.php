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
        <h2 class="font-headline-md text-headline-md text-on-surface">Exam Types</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure standard examination evaluation categories (Unit Tests, Mid-Terms, Annual, Practicals).</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button type="button" onclick="openTypeModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Exam Type
        </button>
      </div>
    </div>

    <!-- Exam Types Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Types (<?php echo count($exam_types); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($exam_types)): ?>
              <tr><td colspan="4" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No exam types configured.</td></tr>
            <?php else: ?>
              <?php foreach ($exam_types as $t): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <?php echo html_escape($t->type_name); ?>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant">
                    <?php echo html_escape($t->description ?: '—'); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php echo form_open('examinations/types', array('class' => 'inline')); ?>
                      <input type="hidden" name="action" value="toggle"/>
                      <input type="hidden" name="exam_type_id" value="<?php echo $t->exam_type_id; ?>"/>
                      <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer <?php echo ($t->status == 1) ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                        <?php echo ($t->status == 1) ? 'Active' : 'Inactive'; ?>
                      </button>
                    <?php echo form_close(); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Edit -->
                      <button type="button" onclick='editTypeModal(<?php echo json_encode($t); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <!-- Delete -->
                      <?php echo form_open('examinations/types', array('class' => 'inline', 'onsubmit' => 'return confirm("Delete this exam type?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="exam_type_id" value="<?php echo $t->exam_type_id; ?>"/>
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

    <!-- CREATE / EDIT TYPE MODAL -->
    <div id="type-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-type-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">category</span>Add Exam Type
          </h3>
          <button onclick="closeTypeModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/types', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="exam_type_id" id="modal-type-id" value="0"/>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Type Name *</label>
            <input type="text" name="type_name" id="modal-type-name" required placeholder="e.g. Unit Test, Model Exam" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
            <textarea name="description" id="modal-type-description" rows="3" placeholder="Description of this examination category..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <label class="flex items-center gap-2 text-body-md text-on-surface cursor-pointer">
            <input type="checkbox" name="status" id="modal-type-status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            <span>Active Status</span>
          </label>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeTypeModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Exam Type
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal Scripts -->
    <script>
      function openTypeModal() {
        document.getElementById('modal-type-id').value = '0';
        document.getElementById('modal-type-title').textContent = 'Add Exam Type';
        document.getElementById('modal-type-name').value = '';
        document.getElementById('modal-type-description').value = '';
        document.getElementById('modal-type-status').checked = true;

        var modal = document.getElementById('type-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function editTypeModal(item) {
        document.getElementById('modal-type-id').value = item.exam_type_id;
        document.getElementById('modal-type-title').textContent = 'Edit Exam Type';
        document.getElementById('modal-type-name').value = item.type_name;
        document.getElementById('modal-type-description').value = item.description || '';
        document.getElementById('modal-type-status').checked = (item.status == 1);

        var modal = document.getElementById('type-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeTypeModal() {
        var modal = document.getElementById('type-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
