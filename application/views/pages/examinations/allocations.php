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
        <h2 class="font-headline-md text-headline-md text-on-surface">Add Schedule</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Schedule examination papers, timings, maximum/passing marks, and invigilator teachers across class subjects.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/schedules'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">calendar_month</span>View Schedules
        </a>
      </div>
    </div>

    <!-- Filter Selector Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/allocations'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Exam *</label>
          <select name="exam_id" onchange="showLoading(); this.form.submit();" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Exam --</option>
            <?php foreach ($exams as $e): ?>
              <option value="<?php echo $e->exam_id; ?>" <?php echo ($selected_exam == $e->exam_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($e->exam_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Class *</label>
          <select name="class_id" onchange="if(this.form.querySelector('[name=division_id]')) this.form.querySelector('[name=division_id]').value=''; showLoading(); this.form.submit();" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Division *</label>
          <select name="division_id" onchange="showLoading(); this.form.submit();" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Division --</option>
            <?php if (!empty($selected_class)): ?>
              <?php if (!empty($divisions)): ?>
                <option value="all" <?php echo (($selected_division ?? '') === 'all') ? 'selected' : ''; ?>>All Divisions</option>
                <?php foreach ($divisions as $s): ?>
                  <option value="<?php echo $s->division_id; ?>" <?php echo ((string)($selected_division ?? '') === (string)$s->division_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
                  </option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="" disabled>No divisions found for this class</option>
              <?php endif; ?>
            <?php endif; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Loading State Indicator -->
    <div id="filter-loading" class="hidden elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-12 text-center text-on-surface-variant mb-6">
      <span class="material-symbols-outlined text-[40px] text-primary animate-spin mb-3">progress_activity</span>
      <h3 class="font-headline-md text-title-md font-bold text-on-surface">Loading subjects...</h3>
      <p class="text-body-md mt-1 text-on-surface-variant">Please wait while the class subjects and schedule data are being loaded.</p>
    </div>

    <div id="allocation-content">
      <!-- Allocation Form -->
      <?php if ($selected_exam && $selected_class && ($selected_division ?? $selected_section)): ?>
        <?php echo form_open('examinations/allocations'); ?>
          <input type="hidden" name="exam_id" value="<?php echo $selected_exam; ?>"/>
          <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>"/>
          <input type="hidden" name="division_id" value="<?php echo $selected_division ?? $selected_section; ?>"/>
          <input type="hidden" name="academic_year_id" value="<?php echo !empty($academic_year_id) ? (int)$academic_year_id : (int)(get_active_academic_year_id(TRUE) ?: 1); ?>"/>

          <?php if (($selected_division ?? '') === 'all'): ?>
            <div class="mb-4 p-4 rounded-xl bg-primary-fixed/40 text-on-surface border border-primary/20 flex items-start gap-3">
              <span class="material-symbols-outlined text-primary text-[24px] shrink-0 mt-0.5">info</span>
              <div>
                <div class="font-semibold text-primary text-body-md">All Divisions Selected</div>
                <p class="text-body-md text-on-surface-variant mt-0.5">
                  Saving will create separate exam schedule records for each division in <strong><?php echo html_escape($selected_class_name ?: 'this class'); ?></strong>:
                  <span class="font-semibold text-on-surface"><?php
                    $div_names = array_map(function($d) { return html_escape($d->class_name . ' ' . $d->division_name); }, $divisions);
                    echo implode(', ', $div_names);
                  ?></span> (<?php echo count($divisions); ?> separate records per selected subject).
                </p>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($subjects)): ?>
            <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
              <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
                <span class="text-body-md font-semibold text-on-surface">Available Subjects to Schedule <?php echo (($selected_division ?? '') === 'all') ? '<span class="text-primary text-[13px] font-normal ml-1.5">(Applies to all ' . count($divisions) . ' divisions)</span>' : ''; ?></span>
                <div class="flex items-center gap-2">
                  <button type="button" onclick="toggleSelectAll(true)" class="text-[12px] text-primary font-medium hover:underline cursor-pointer">Select All</button>
                  <span class="text-on-surface-variant">•</span>
                  <button type="button" onclick="toggleSelectAll(false)" class="text-[12px] text-on-surface-variant font-medium hover:underline cursor-pointer">Deselect All</button>
                </div>
              </div>

              <div class="table-scroll overflow-x-auto">
                <table class="w-full data-table zebra border-collapse">
                  <thead>
                    <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                      <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-12">Select</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Exam Date</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Start Time</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">End Time</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Max Marks</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Passing Marks</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Room</th>
                      <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Invigilator</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-outline-variant/40">
                    <?php foreach ($subjects as $sub): ?>
                      <?php
                        $isAllocated = isset($allocated_map[$sub->subject_id]);
                        $alloc = $isAllocated ? $allocated_map[$sub->subject_id] : NULL;
                      ?>
                      <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-3 py-3 text-center">
                          <input type="checkbox" name="subjects[<?php echo $sub->subject_id; ?>][selected]" value="1" <?php echo $isAllocated ? 'checked' : ''; ?> class="alloc-checkbox w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                          <?php echo html_escape($sub->subject_name); ?>
                          <span class="text-[11px] text-on-surface-variant block font-mono font-normal"><?php echo html_escape($sub->subject_code); ?></span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="date" name="subjects[<?php echo $sub->subject_id; ?>][exam_date]" value="<?php echo $alloc ? $alloc->exam_date : date('Y-m-d'); ?>" class="px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="time" name="subjects[<?php echo $sub->subject_id; ?>][start_time]" value="<?php echo $alloc ? $alloc->start_time : '09:30'; ?>" class="px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="time" name="subjects[<?php echo $sub->subject_id; ?>][end_time]" value="<?php echo $alloc ? $alloc->end_time : '12:30'; ?>" class="px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="number" step="0.5" name="subjects[<?php echo $sub->subject_id; ?>][max_marks]" value="<?php echo $alloc ? (int)$alloc->max_marks : 100; ?>" class="w-20 px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px] text-right font-mono"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="number" step="0.5" name="subjects[<?php echo $sub->subject_id; ?>][passing_marks]" value="<?php echo $alloc ? (int)$alloc->passing_marks : 35; ?>" class="w-20 px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px] text-right font-mono"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input type="text" name="subjects[<?php echo $sub->subject_id; ?>][room_no]" value="<?php echo $alloc ? html_escape($alloc->room_no) : 'Hall 1'; ?>" class="w-24 px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]"/>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <select name="subjects[<?php echo $sub->subject_id; ?>][teacher_id]" class="px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]">
                            <option value="">Unassigned</option>
                            <?php foreach ($teachers as $t): ?>
                              <option value="<?php echo $t->staff_id; ?>" <?php echo ($alloc && $alloc->teacher_id == $t->staff_id) ? 'selected' : ''; ?>>
                                <?php echo html_escape($t->full_name); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
              <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">save</span>Save Schedule
              </button>
            </div>
          <?php else: ?>
            <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-12 text-center text-on-surface-variant mb-6">
              <span class="material-symbols-outlined text-[48px] text-on-surface-variant/40 mb-3">menu_book</span>
              <h3 class="font-headline-md text-title-lg font-bold text-on-surface">No subjects are assigned to this class.</h3>
              <p class="text-body-md mt-1 max-w-md mx-auto">There are no active subjects allocated to <strong><?php echo html_escape($selected_class_name ?: 'this class'); ?></strong> for this academic year. Please allocate subjects to this class in Academic Management before creating exam schedules.</p>
            </div>
          <?php endif; ?>
        <?php echo form_close(); ?>
      <?php else: ?>
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-12 text-center text-on-surface-variant">
          <span class="material-symbols-outlined text-[48px] text-primary mb-3">tune</span>
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Select Exam, Class & Division</h3>
          <p class="text-body-md mt-1 max-w-md mx-auto">Please choose an exam, class, and division (or &ldquo;All Divisions&rdquo;) from the filters above to configure examination schedules.</p>
        </div>
      <?php endif; ?>
    </div>

    <script>
      function showLoading() {
        var loadingEl = document.getElementById('filter-loading');
        var contentEl = document.getElementById('allocation-content');
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (contentEl) contentEl.classList.add('hidden');
      }

      function toggleSelectAll(checked) {
        document.querySelectorAll('.alloc-checkbox').forEach(function(cb) {
          cb.checked = checked;
        });
      }
    </script>
