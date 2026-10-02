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
        <h2 class="font-headline-md text-headline-md text-on-surface">Exam Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Create and manage examinations, term structures, applicable classes, and lifecycle statuses.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button type="button" onclick="openExamModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Exam
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/exams'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Academic Years</option>
            <?php foreach ($academic_years as $ay): ?>
              <option value="<?php echo $ay->academic_year_id; ?>" <?php echo ($filters['academic_year_id'] == $ay->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam Type</label>
          <select name="exam_type_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Types</option>
            <?php foreach ($exam_types as $et): ?>
              <option value="<?php echo $et->exam_type_id; ?>" <?php echo ($filters['exam_type_id'] == $et->exam_type_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($et->type_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <?php foreach (['Draft', 'Scheduled', 'Ongoing', 'Completed', 'Marks Pending', 'Under Verification', 'Published', 'Cancelled'] as $st): ?>
              <option value="<?php echo $st; ?>" <?php echo ($filters['status'] === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex items-center gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Exam name..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shrink-0">Filter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Exams Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Examinations (<?php echo count($exams); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Exam Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Academic Year</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Dates</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Classes</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($exams)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No examinations found. Click "Create Exam" to get started.</td></tr>
            <?php else: ?>
              <?php foreach ($exams as $e): ?>
                <?php
                  $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                  if ($e->status === 'Published') $badgeClass = 'bg-emerald-100 text-emerald-800';
                  elseif ($e->status === 'Ongoing') $badgeClass = 'bg-secondary-container text-on-secondary-container';
                  elseif ($e->status === 'Scheduled') $badgeClass = 'bg-primary text-white';
                  elseif ($e->status === 'Under Verification') $badgeClass = 'bg-amber-100 text-amber-900';
                  elseif ($e->status === 'Cancelled') $badgeClass = 'bg-error-container text-on-error-container';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <div class="font-bold text-on-surface"><?php echo html_escape($e->exam_name); ?></div>
                    <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($e->description ?: 'No description'); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant"><?php echo html_escape($e->year_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md font-medium text-on-surface"><?php echo html_escape($e->type_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[13px] text-on-surface">
                    <?php echo date('d M Y', strtotime($e->start_date)) . ' – ' . date('d M Y', strtotime($e->end_date)); ?>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant max-w-[200px] truncate">
                    <?php echo implode(', ', $e->class_names); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                      <?php echo html_escape($e->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Schedule Subjects -->
                      <a href="<?php echo site_url('examinations/schedules?exam_id=' . $e->exam_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-primary transition-colors cursor-pointer" title="View/Manage Schedule">
                        <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                      </a>

                      <!-- Enter Marks -->
                      <a href="<?php echo site_url('examinations/marks_entry?exam_id=' . $e->exam_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-secondary transition-colors cursor-pointer" title="Enter Marks">
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                      </a>

                      <!-- View Results -->
                      <a href="<?php echo site_url('examinations/results?exam_id=' . $e->exam_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-emerald-700 transition-colors cursor-pointer" title="View Results">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                      </a>

                      <!-- Edit Modal -->
                      <button type="button" onclick='editExamModal(<?php echo json_encode($e); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Edit Exam">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <!-- Delete -->
                      <?php echo form_open('examinations/exams', array('class' => 'inline', 'onsubmit' => 'return confirm("Delete this exam and all its schedules?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="exam_id" value="<?php echo $e->exam_id; ?>"/>
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

    <!-- CREATE / EDIT EXAM MODAL -->
    <div id="exam-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-exam-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">quiz</span>Create Examination
          </h3>
          <button onclick="closeExamModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/exams', array('id' => 'exam-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="exam_id" id="modal-exam-id" value="0"/>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam Name *</label>
            <input type="text" name="exam_name" id="modal-exam-name" required placeholder="e.g. First Term Examination 2026" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam Type *</label>
              <select name="exam_type_id" id="modal-exam-type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Select Exam Type</option>
                <?php foreach ($exam_types as $et): ?>
                  <option value="<?php echo $et->exam_type_id; ?>"><?php echo html_escape($et->type_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year *</label>
              <select name="academic_year_id" id="modal-academic-year" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($academic_years as $ay): ?>
                  <option value="<?php echo $ay->academic_year_id; ?>" <?php echo (!empty($ay->is_active)) ? 'selected' : ''; ?>>
                    <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Start Date *</label>
              <input type="date" name="start_date" id="modal-start-date" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">End Date *</label>
              <input type="date" name="end_date" id="modal-end-date" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status *</label>
            <select name="status" id="modal-status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php foreach (['Draft', 'Scheduled', 'Ongoing', 'Completed', 'Marks Pending', 'Under Verification', 'Published', 'Cancelled'] as $st): ?>
                <option value="<?php echo $st; ?>"><?php echo $st; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Applicable Classes</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 max-h-36 overflow-y-auto">
              <?php foreach ($classes as $c): ?>
                <label class="flex items-center gap-2 text-body-md text-on-surface cursor-pointer">
                  <input type="checkbox" name="classes[]" value="<?php echo $c->class_id; ?>" class="modal-class-checkbox w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                  <span><?php echo html_escape($c->class_name); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description / Instructions</label>
            <textarea name="description" id="modal-description" rows="2" placeholder="General description or examination instructions..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeExamModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Exam
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal Scripts -->
    <script>
      function openExamModal() {
        document.getElementById('modal-exam-id').value = '0';
        document.getElementById('modal-exam-title').textContent = 'Create Examination';
        document.getElementById('modal-exam-name').value = '';
        document.getElementById('modal-exam-type').value = '';
        document.getElementById('modal-start-date').value = '';
        document.getElementById('modal-end-date').value = '';
        document.getElementById('modal-description').value = '';
        document.getElementById('modal-status').value = 'Draft';

        var activeYr = window.ACTIVE_ACADEMIC_YEAR_ID || window.CURRENT_ACADEMIC_YEAR_ID;
        if (activeYr && document.getElementById('modal-academic-year')) {
          document.getElementById('modal-academic-year').value = activeYr;
        }

        document.querySelectorAll('.modal-class-checkbox').forEach(function(cb) {
          cb.checked = true;
        });

        var modal = document.getElementById('exam-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function editExamModal(item) {
        document.getElementById('modal-exam-id').value = item.exam_id;
        document.getElementById('modal-exam-title').textContent = 'Edit Examination';
        document.getElementById('modal-exam-name').value = item.exam_name;
        document.getElementById('modal-exam-type').value = item.exam_type_id;
        document.getElementById('modal-academic-year').value = item.academic_year_id;
        document.getElementById('modal-start-date').value = item.start_date;
        document.getElementById('modal-end-date').value = item.end_date;
        document.getElementById('modal-description').value = item.description || '';
        document.getElementById('modal-status').value = item.status;

        var assignedClasses = item.class_ids || [];
        document.querySelectorAll('.modal-class-checkbox').forEach(function(cb) {
          cb.checked = (assignedClasses.length === 0 || assignedClasses.indexOf(parseInt(cb.value)) !== -1);
        });

        var modal = document.getElementById('exam-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeExamModal() {
        var modal = document.getElementById('exam-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
