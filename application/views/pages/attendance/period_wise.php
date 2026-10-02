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
        <h2 class="font-headline-md text-headline-md text-on-surface">Period-wise Attendance</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Record and manage period-specific student attendance for subject classes (+1 and +2).</p>
      </div>
    </div>

    <!-- Filter Bar (Year, Date, Class, Section, Period) -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-5">
      <form method="get" action="<?php echo site_url('attendance/period_wise'); ?>" id="period-filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Academic Year *</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y->academic_year_id; ?>" <?php echo ($year_id == $y->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($y->year_name); ?><?php echo (!empty($y->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Date *</label>
          <input type="date" name="date" value="<?php echo html_escape($date); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class *</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($class_id == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Division *</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Select Division --</option>
            <?php foreach ($sections as $sec): ?>
              <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo ($section_id == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>>
                <?php echo html_escape($sec->class_name . ' ' . ($sec->division_name ?? $sec->section_name)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Period *</label>
          <select name="period_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Select Period --</option>
            <?php foreach ($periods as $p): ?>
              <option value="<?php echo $p->period_id; ?>" <?php echo ($period_id == $p->period_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($p->period_name . ' (' . date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)) . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <?php if (!$class_id || !$section_id || !$period_id): ?>
      <!-- Prompt to Select Filters -->
      <div class="p-8 rounded-xl bg-surface-container-lowest border border-outline-variant/50 text-center elevation-1">
        <span class="material-symbols-outlined text-[48px] text-primary/60 mb-2">schedule</span>
        <h3 class="font-title-md text-title-md text-on-surface font-semibold">Select Class, Division, and Period</h3>
        <p class="text-body-md text-on-surface-variant max-w-md mx-auto mt-1">Please select all required parameters above to load the student list and record period attendance.</p>
      </div>
    <?php else: ?>

      <!-- Already Marked Notice / Edit Mode -->
      <?php if ($is_already_marked): ?>
        <div class="mb-5 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 elevation-1">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[24px] text-amber-600 shrink-0">info</span>
            <div>
              <div class="font-semibold text-body-md">Period attendance already marked.</div>
              <div class="text-[13px] text-amber-800 dark:text-amber-300">You are in <strong>Edit Attendance</strong> mode for this period. Existing records will be updated safely.</div>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-[12px] font-semibold bg-amber-200 text-amber-900 shrink-0 self-start sm:self-center">Edit Mode</span>
        </div>
      <?php endif; ?>

      <?php echo form_open('attendance/period_wise', array('id' => 'period-attendance-form')); ?>
        <input type="hidden" name="date" value="<?php echo html_escape($date); ?>"/>
        <input type="hidden" name="academic_year_id" value="<?php echo html_escape($year_id); ?>"/>
        <input type="hidden" name="class_id" value="<?php echo html_escape($class_id); ?>"/>
        <input type="hidden" name="division_id" value="<?php echo html_escape($section_id); ?>"/>
        <input type="hidden" name="period_id" value="<?php echo html_escape($period_id); ?>"/>

        <!-- Control Bar & Live Counters -->
        <?php
          $marker_info = null;
          $updater_info = null;
          if (!empty($students)) {
              foreach ($students as $s_rec) {
                  if (!empty($s_rec->marked_by_display) && $s_rec->marked_by_display !== 'Not Available') {
                      $marker_info = $s_rec->marked_by_display;
                      $updater_info = $s_rec->updated_by_display;
                      break;
                  }
              }
          }
        ?>
        <?php if ($is_already_marked && ($marker_info || $updater_info)): ?>
          <div class="mb-4 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 flex items-center gap-4 text-[12px] text-on-surface-variant flex-wrap">
            <?php if ($marker_info): ?>
              <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px] text-primary">person</span> Originally marked by: <strong class="text-on-surface"><?php echo html_escape($marker_info); ?></strong></span>
            <?php endif; ?>
            <?php if ($updater_info): ?>
              <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px] text-amber-600">edit_note</span> Last updated by: <strong class="text-on-surface"><?php echo html_escape($updater_info); ?></strong></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Bulk Action Buttons -->
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-body-md font-medium text-on-surface mr-1">Quick Mark:</span>
            <button type="button" onclick="markAllPeriodStatus('Present')" class="px-3 py-1.5 rounded-lg bg-secondary-container text-on-secondary-container hover:opacity-90 transition-opacity text-label-md font-semibold flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">done_all</span>All Present
            </button>
            <button type="button" onclick="markAllPeriodStatus('Half Day')" class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-900 hover:opacity-90 transition-opacity text-label-md font-semibold flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">timelapse</span>All Half Day
            </button>
            <button type="button" onclick="markAllPeriodStatus('Absent')" class="px-3 py-1.5 rounded-lg bg-error-container text-on-error-container hover:opacity-90 transition-opacity text-label-md font-semibold flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">close</span>All Absent
            </button>
          </div>

          <!-- Live Summary Counters before saving -->
          <div class="flex items-center gap-3 flex-wrap text-[13px] font-medium">
            <span class="px-3 py-1 rounded-lg bg-surface-container-high text-on-surface">Total: <strong id="p-cnt-total"><?php echo count($students); ?></strong></span>
            <span class="px-3 py-1 rounded-lg bg-secondary-container text-on-secondary-container">Present: <strong id="p-cnt-present">0</strong></span>
            <span class="px-3 py-1 rounded-lg bg-amber-100 text-amber-900">Half Day: <strong id="p-cnt-halfday">0</strong></span>
            <span class="px-3 py-1 rounded-lg bg-indigo-100 text-indigo-900">Late: <strong id="p-cnt-late">0</strong></span>
            <span class="px-3 py-1 rounded-lg bg-error-container text-on-error-container">Absent: <strong id="p-cnt-absent">0</strong></span>
          </div>

          <!-- Save Button -->
          <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer shrink-0">
            <span class="material-symbols-outlined text-[18px]">save</span>
            <?php echo ($is_already_marked) ? 'Update Period Attendance' : 'Save Period Attendance'; ?>
          </button>
        </div>

        <!-- Student Period Attendance Table -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
          <div class="table-scroll overflow-x-auto">
            <table class="w-full data-table zebra border-collapse">
              <thead>
                <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                  <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student Name</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Period</th>
                  <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Attendance Status</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Remarks</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/40">
                <?php if (empty($students)): ?>
                  <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No students found in this class and division.</td></tr>
                <?php else: ?>
                  <?php foreach ($students as $st): ?>
                    <?php
                      $fullName = trim($st->first_name . ' ' . $st->last_name);
                      $curStatus = $st->attendance_status ?: 'Present';
                      if (in_array($curStatus, array('Leave', 'Excused'))) $curStatus = 'Present';
                    ?>
                    <tr class="hover:bg-surface-container-low transition-colors period-att-row" data-student-id="<?php echo $st->student_id; ?>">
                      <!-- Roll Number -->
                      <td class="px-3 py-3 text-center font-mono font-semibold text-on-surface whitespace-nowrap">
                        <?php echo html_escape($st->roll_number ?: '—'); ?>
                      </td>

                      <!-- Student Name -->
                      <td class="px-4 py-3 text-on-surface whitespace-nowrap font-medium">
                        <?php echo html_escape($fullName); ?>
                      </td>

                      <!-- Period Info -->
                      <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                        <span class="font-semibold text-on-surface"><?php echo html_escape($st->period_name); ?></span>
                        <span class="text-[12px] block text-on-surface-variant/80"><?php echo date('h:i A', strtotime($st->start_time)); ?> - <?php echo date('h:i A', strtotime($st->end_time)); ?></span>
                      </td>

                      <!-- Attendance Status Radio Controls -->
                      <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 p-1 rounded-xl bg-surface-container-low border border-outline-variant/40">
                          <!-- Present -->
                          <label class="cursor-pointer">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Present" class="sr-only peer p-att-radio" <?php echo ($curStatus === 'Present') ? 'checked' : ''; ?> onchange="updatePeriodSummaryCounters()">
                            <span class="px-3 py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1 border border-transparent text-on-surface-variant peer-checked:bg-secondary-container peer-checked:text-on-secondary-container peer-checked:border-secondary transition-all">
                              <span class="material-symbols-outlined text-[16px]">check</span>Present
                            </span>
                          </label>

                          <!-- Half Day -->
                          <label class="cursor-pointer">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Half Day" class="sr-only peer p-att-radio" <?php echo (in_array($curStatus, array('Half Day', 'Late / Half Day', 'Half-day'))) ? 'checked' : ''; ?> onchange="updatePeriodSummaryCounters()">
                            <span class="px-3 py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1 border border-transparent text-on-surface-variant peer-checked:bg-amber-100 peer-checked:text-amber-900 peer-checked:border-amber-400 transition-all">
                              <span class="material-symbols-outlined text-[16px]">timelapse</span>Half Day
                            </span>
                          </label>

                          <!-- Late Coming -->
                          <label class="cursor-pointer">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Late Coming" class="sr-only peer p-att-radio" <?php echo (in_array($curStatus, array('Late', 'Late Coming'))) ? 'checked' : ''; ?> onchange="updatePeriodSummaryCounters()">
                            <span class="px-3 py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1 border border-transparent text-on-surface-variant peer-checked:bg-indigo-100 peer-checked:text-indigo-900 peer-checked:border-indigo-400 transition-all">
                              <span class="material-symbols-outlined text-[16px]">schedule</span>Late Coming
                            </span>
                          </label>

                          <!-- Absent -->
                          <label class="cursor-pointer">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Absent" class="sr-only peer p-att-radio" <?php echo ($curStatus === 'Absent') ? 'checked' : ''; ?> onchange="updatePeriodSummaryCounters()">
                            <span class="px-3 py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1 border border-transparent text-on-surface-variant peer-checked:bg-error-container peer-checked:text-on-error-container peer-checked:border-error transition-all">
                              <span class="material-symbols-outlined text-[16px]">close</span>Absent
                            </span>
                          </label>
                        </div>
                      </td>

                      <!-- Remarks Field -->
                      <td class="px-4 py-3 whitespace-nowrap min-w-[180px]">
                        <input type="text" name="attendance[<?php echo $st->student_id; ?>][remarks]" value="<?php echo html_escape($st->remarks); ?>" placeholder="Notes..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-1 focus:ring-primary focus:border-primary placeholder-on-surface-variant/40"/>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Bottom Save Action Bar -->
        <?php if (!empty($students)): ?>
          <div class="flex items-center justify-between p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
            <span class="text-body-md text-on-surface-variant font-medium">Ready to save period attendance.</span>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>
              <?php echo ($is_already_marked) ? 'Update Period Attendance' : 'Save Period Attendance'; ?>
            </button>
          </div>
        <?php endif; ?>
      <?php echo form_close(); ?>

    <?php endif; ?>

    <!-- Period Live Counter Script -->
    <script>
      function updatePeriodSummaryCounters() {
        var total = document.querySelectorAll('.period-att-row').length;
        var present = document.querySelectorAll('input.p-att-radio[value="Present"]:checked').length;
        var halfday = document.querySelectorAll('input.p-att-radio[value="Half Day"]:checked').length;
        var late = document.querySelectorAll('input.p-att-radio[value="Late Coming"]:checked, input.p-att-radio[value="Late"]:checked').length;
        var absent = document.querySelectorAll('input.p-att-radio[value="Absent"]:checked').length;

        var elTot = document.getElementById('p-cnt-total');
        var elPres = document.getElementById('p-cnt-present');
        var elHalf = document.getElementById('p-cnt-halfday');
        var elLate = document.getElementById('p-cnt-late');
        var elAbs = document.getElementById('p-cnt-absent');

        if (elTot) elTot.textContent = total;
        if (elPres) elPres.textContent = present;
        if (elHalf) elHalf.textContent = halfday;
        if (elLate) elLate.textContent = late;
        if (elAbs) elAbs.textContent = absent;
      }

      function markAllPeriodStatus(statusValue) {
        var inputs = document.querySelectorAll('input.p-att-radio[value="' + statusValue + '"]');
        inputs.forEach(function(inp) {
          inp.checked = true;
        });
        updatePeriodSummaryCounters();
      }

      document.addEventListener('DOMContentLoaded', function() {
        updatePeriodSummaryCounters();
      });
    </script>
