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

    <!-- Header & Action Links -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-5 sm:mb-6">
      <div class="min-w-0">
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
          <h2 class="font-headline-md text-[18px] sm:text-headline-md text-on-surface font-semibold">Mark Attendance</h2>
          <?php if ($is_higher_sec): ?>
            <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-[12px] font-semibold bg-primary text-white border border-primary/30">
              Period Attendance (+1 &amp; +2)
            </span>
          <?php else: ?>
            <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[11px] sm:text-[12px] font-semibold bg-secondary-container text-on-secondary-container border border-secondary/30">
              Daily Attendance (LKG – 10)
            </span>
          <?php endif; ?>
        </div>
        <p class="text-[13px] sm:text-body-md text-on-surface-variant mt-1 leading-snug">
          <?php if ($is_higher_sec): ?>
            Take period-wise attendance for Higher Secondary (+1 &amp; +2). Allowed statuses: Present, Half Day, Absent, Late Coming.
          <?php else: ?>
            Take one daily morning attendance per student for LKG through Class 10. Allowed statuses: Present, Half Day, Absent.
          <?php endif; ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('attendance/class_attendance?class_id=' . $class_id . '&date=' . $date . '&academic_year_id=' . $year_id); ?>" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-[12px] sm:text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[17px] sm:text-[18px]">co_present</span><span class="truncate">Class Attendance (View)</span>
        </a>
        <a href="<?php echo site_url('student-attendance/view?class_id=' . $class_id . '&academic_year_id=' . $year_id); ?>" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg bg-secondary text-on-secondary text-[12px] sm:text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[17px] sm:text-[18px]">visibility</span><span class="truncate">VIEW ATTENDANCE</span>
        </a>
      </div>
    </div>

    <!-- Filter Bar (Academic Year, Date, Class, Section, and Subject/Period for +1/+2) -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-3.5 sm:p-5 mb-5 sm:mb-6">
      <form method="get" action="<?php echo site_url('attendance/mark_attendance'); ?>" class="grid grid-cols-1 sm:grid-cols-2 <?php echo $is_higher_sec ? 'lg:grid-cols-7' : 'lg:grid-cols-5'; ?> gap-3 sm:gap-4">
        <!-- Academic Year -->
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

        <!-- Date (Can select any past date, not restricted to today) -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Date *</label>
          <input type="date" name="date" value="<?php echo html_escape($date); ?>" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <!-- Academic Group -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Department / Group</label>
          <select id="filter_academic_group" onchange="onAttendanceGroupChanged(this.value)" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Department / Groups</option>
            <?php if (!empty($groups)): foreach ($groups as $grp): ?>
              <option value="<?php echo $grp->academic_group_id; ?>" <?php echo (!empty($selected_group_id) && $selected_group_id == $grp->academic_group_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($grp->group_name); ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Class -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class *</label>
          <select id="attendance_class_select" name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" data-attendance-type="<?php echo html_escape($cls->attendance_type ?? 'daily'); ?>" <?php echo ($class_id == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
                (<?php echo ucfirst($cls->attendance_type ?? 'daily'); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Division / Session -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Division / Session *</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php if (empty($sections)): ?>
              <option value="<?php echo $section_id ?: 0; ?>">Division A (Default)</option>
            <?php else: ?>
              <?php foreach ($sections as $sec): ?>
                <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo ($section_id == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>>
                  Division <?php echo html_escape($sec->division_name ?? $sec->section_name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <?php if ($is_higher_sec): ?>
          <!-- Subject (Period attendance only) -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Subject</label>
            <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- All Subjects --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub->subject_id; ?>" <?php echo ($subject_id == $sub->subject_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($sub->subject_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Period (Period attendance only) -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Period *</label>
            <select name="period_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-semibold">
              <option value="">-- Choose Period --</option>
              <?php foreach ($periods as $p): ?>
                <option value="<?php echo $p->period_id; ?>" <?php echo ($period_id == $p->period_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($p->period_name . ' (' . date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)) . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Period Attendance Prompt if Period is not selected -->
    <?php if ($is_higher_sec && empty($period_id)): ?>
      <div class="p-6 sm:p-8 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 text-center elevation-1 mb-8">
        <span class="material-symbols-outlined text-[48px] text-primary mb-2">schedule</span>
        <h4 class="font-title-md text-title-md text-on-surface font-semibold">Select a Period to Begin</h4>
        <p class="text-body-md text-on-surface-variant mt-1 max-w-md mx-auto">
          This Department / Group is configured for period-wise attendance. Please select a period from the filter above to load students.
        </p>
      </div>
    <?php elseif (empty($students)): ?>
      <!-- No students found in section -->
      <div class="p-6 sm:p-8 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 text-center elevation-1 mb-8">
        <span class="material-symbols-outlined text-[48px] text-outline mb-2">group_off</span>
        <h4 class="font-title-md text-title-md text-on-surface font-semibold">No Enrolled Students Found</h4>
        <p class="text-body-md text-on-surface-variant mt-1">There are no active students enrolled in this class and division for the selected academic year.</p>
      </div>
    <?php else: ?>
      <!-- Attendance Roll Sheet Container -->
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-8">
        <!-- Sheet Header Bar -->
        <div class="p-3.5 sm:p-5 border-b border-outline-variant/40 flex flex-col md:flex-row md:items-center justify-between gap-3.5 sm:gap-4 bg-surface-container-low/40">
          <div class="min-w-0">
            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
              <h3 class="font-title-md sm:font-title-lg text-title-md sm:text-title-lg font-bold text-on-surface">
                <?php echo html_escape($selected_class ? $selected_class->class_name : 'Class'); ?> — Division <?php echo html_escape($selected_section ? ($selected_section->division_name ?? $selected_section->section_name) : 'A'); ?>
              </h3>
              <?php if ($is_already_marked): ?>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 shrink-0">
                  <span class="material-symbols-outlined text-[14px]">edit</span>Editing Saved Attendance
                </span>
              <?php else: ?>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container border border-secondary/30 flex items-center gap-1 shrink-0">
                  <span class="material-symbols-outlined text-[14px]">add_circle</span>New Attendance
                </span>
              <?php endif; ?>
            </div>
            <p class="text-[13px] sm:text-body-md text-on-surface-variant mt-1">
              Date: <strong class="text-on-surface"><?php echo date('d M Y', strtotime($date)); ?></strong>
              <?php if ($is_higher_sec && $period_id): ?>
                <?php
                  $cur_p = null;
                  foreach ($periods as $p) { if ((int)$p->period_id === (int)$period_id) { $cur_p = $p; break; } }
                ?>
                • Period: <strong class="text-on-surface"><?php echo html_escape($cur_p ? $cur_p->period_name : 'Period ' . $period_id); ?></strong>
              <?php endif; ?>
            </p>
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
              <div class="mt-2 text-[12px] flex items-center gap-3 text-on-surface-variant flex-wrap">
                <?php if ($marker_info): ?>
                  <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px] text-primary">person</span> Originally marked by: <strong class="text-on-surface"><?php echo html_escape($marker_info); ?></strong></span>
                <?php endif; ?>
                <?php if ($updater_info): ?>
                  <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px] text-amber-600">edit_note</span> Last updated by: <strong class="text-on-surface"><?php echo html_escape($updater_info); ?></strong></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Quick Mark Actions -->
          <div class="w-full md:w-auto pt-3 md:pt-0 border-t md:border-t-0 border-outline-variant/30 flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="flex items-center justify-between sm:justify-start">
              <span class="text-label-md font-semibold text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] text-primary">flash_on</span>Quick Mark:
              </span>
            </div>
            <div class="grid grid-cols-3 <?php echo $is_higher_sec ? 'grid-cols-2 sm:grid-cols-4' : 'grid-cols-3'; ?> sm:flex sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto">
              <button type="button" onclick="markAllStatus('Present')" class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg bg-secondary-container text-on-secondary-container hover:opacity-90 transition-opacity text-[11px] sm:text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer min-h-[38px] sm:min-h-0">
                <span class="material-symbols-outlined text-[15px] sm:text-[16px]">done_all</span>All Present
              </button>
              <button type="button" onclick="markAllStatus('Half Day')" class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg bg-amber-100 text-amber-900 hover:opacity-90 transition-opacity text-[11px] sm:text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer min-h-[38px] sm:min-h-0">
                <span class="material-symbols-outlined text-[15px] sm:text-[16px]">timelapse</span>All Half Day
              </button>
              <?php if ($is_higher_sec): ?>
                <button type="button" onclick="markAllStatus('Late Coming')" class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg bg-indigo-100 text-indigo-900 hover:opacity-90 transition-opacity text-[11px] sm:text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer min-h-[38px] sm:min-h-0">
                  <span class="material-symbols-outlined text-[15px] sm:text-[16px]">schedule</span>All Late
                </button>
              <?php endif; ?>
              <button type="button" onclick="markAllStatus('Absent')" class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg bg-error-container text-on-error-container hover:opacity-90 transition-opacity text-[11px] sm:text-label-md font-semibold flex items-center justify-center gap-1 cursor-pointer min-h-[38px] sm:min-h-0">
                <span class="material-symbols-outlined text-[15px] sm:text-[16px]">close</span>All Absent
              </button>
            </div>
          </div>
        </div>

        <?php echo form_open('attendance/mark_attendance', array('id' => 'mark-attendance-form')); ?>
          <input type="hidden" name="date" value="<?php echo html_escape($date); ?>"/>
          <input type="hidden" name="academic_year_id" value="<?php echo html_escape($year_id); ?>"/>
          <input type="hidden" name="class_id" value="<?php echo html_escape($class_id); ?>"/>
          <input type="hidden" name="division_id" value="<?php echo html_escape($section_id); ?>"/>
          <?php if ($is_higher_sec): ?>
            <input type="hidden" name="period_id" value="<?php echo html_escape($period_id); ?>"/>
            <input type="hidden" name="subject_id" value="<?php echo html_escape($subject_id); ?>"/>
          <?php endif; ?>

          <!-- Students Table & Mobile Card Roll Sheet -->
          <div class="attendance-table-responsive md:overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead class="hidden md:table-header-group">
                <tr class="bg-surface-container-high/70 text-on-surface border-b border-outline-variant/40 text-label-md uppercase tracking-wider font-semibold text-[11px]">
                  <th class="py-3 px-4 w-12 text-center">Roll #</th>
                  <th class="py-3 px-4 min-w-[200px]">Student</th>
                  <th class="py-3 px-4 text-center">Attendance Status</th>
                  <th class="py-3 px-4 min-w-[200px]">Remarks</th>
                </tr>
              </thead>
              <tbody class="block md:table-row-group md:divide-y md:divide-outline-variant/30">
                <?php foreach ($students as $st): ?>
                  <?php
                    $fullName  = trim($st->first_name . ' ' . $st->last_name);
                    $curStatus = $st->attendance_status ?: 'Present';
                    // Re-map historical Leave or Excused safely
                    if (in_array($curStatus, array('Leave', 'Excused'))) {
                        $curStatus = 'Present';
                    }
                    if (!$is_higher_sec && in_array($curStatus, array('Late', 'Late Coming'))) {
                        $curStatus = 'Present';
                    }
                  ?>
                  <tr class="block md:table-row hover:bg-surface-container-low/40 md:hover:bg-surface-container-low/60 transition-colors att-student-row">
                    <!-- Roll Number (Desktop Table Cell) -->
                    <td class="att-col-roll hidden md:table-cell py-3.5 px-4 text-center font-mono font-semibold text-on-surface whitespace-nowrap">
                      <?php echo html_escape($st->roll_number ?: '—'); ?>
                    </td>

                    <!-- Student Name, Avatar & Admission (Card Header on Mobile) -->
                    <td class="att-col-student block md:table-cell pb-1 md:py-3.5 md:px-4 md:whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <?php if (!empty($st->photo)): ?>
                          <img src="<?php echo base_url('uploads/students/' . $st->photo); ?>" alt="Photo" class="w-10 h-10 md:w-9 md:h-9 rounded-full object-cover border border-outline-variant/40 shrink-0"/>
                        <?php else: ?>
                          <div class="w-10 h-10 md:w-9 md:h-9 rounded-full bg-light-blue text-primary flex items-center justify-center font-bold text-[13px] md:text-[12px] shrink-0 border border-sky-blue/30">
                            <?php echo strtoupper(substr($st->first_name ?: 'S', 0, 1)); ?>
                          </div>
                        <?php endif; ?>
                        <div class="min-w-0 flex-1">
                          <div class="font-semibold text-on-surface text-[15px] md:text-body-md leading-snug">
                            <?php echo html_escape($fullName); ?>
                          </div>
                          <div class="text-[12px] text-muted-text font-mono flex items-center gap-2 mt-0.5 flex-wrap">
                            <span>Roll: <strong class="text-on-surface font-semibold"><?php echo html_escape($st->roll_number ?: '—'); ?></strong></span>
                            <span class="text-outline-variant">•</span>
                            <span>Adm: <strong class="text-on-surface font-semibold"><?php echo html_escape($st->admission_number); ?></strong></span>
                            <?php if (!empty($selected_class)): ?>
                              <span class="md:hidden text-outline-variant">•</span>
                              <span class="md:hidden text-muted-text"><?php echo html_escape($selected_class->class_name); ?><?php echo !empty($selected_section) ? ' (' . html_escape($selected_section->division_name ?? $selected_section->section_name) . ')' : ''; ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Attendance Status Controls (One compact row on Mobile) -->
                    <td class="att-col-status block md:table-cell py-1 md:py-3.5 md:px-4 text-left md:text-center md:whitespace-nowrap">
                      <div class="mt-2.5 md:mt-0">
                        <div class="grid <?php echo $is_higher_sec ? 'grid-cols-4 gap-1.5' : 'grid-cols-3 gap-2'; ?> md:inline-flex md:items-center md:gap-1.5 md:p-1 md:rounded-xl md:bg-surface-container-low md:border md:border-outline-variant/40 w-full md:w-auto">
                          <!-- Present -->
                          <label class="cursor-pointer block">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Present" class="sr-only peer att-radio-input" <?php echo ($curStatus === 'Present') ? 'checked' : ''; ?>>
                            <span class="w-full justify-center md:w-auto px-2 sm:px-3 h-10 md:h-auto md:py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1.5 border border-theme-border bg-white text-muted-text peer-checked:bg-secondary-container peer-checked:text-on-secondary-container peer-checked:border-secondary peer-checked:shadow-xs transition-all text-center select-none">
                              <span class="material-symbols-outlined text-[17px] md:text-[16px]">check_circle</span>Present
                            </span>
                          </label>

                          <!-- Half Day -->
                          <label class="cursor-pointer block">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Half Day" class="sr-only peer att-radio-input" <?php echo (in_array($curStatus, array('Half Day', 'Late / Half Day', 'Half-day'))) ? 'checked' : ''; ?>>
                            <span class="w-full justify-center md:w-auto px-2 sm:px-3 h-10 md:h-auto md:py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1.5 border border-theme-border bg-white text-muted-text peer-checked:bg-amber-100 peer-checked:text-amber-900 peer-checked:border-amber-400 peer-checked:shadow-xs transition-all text-center select-none">
                              <span class="material-symbols-outlined text-[17px] md:text-[16px]">timelapse</span>Half Day
                            </span>
                          </label>

                          <!-- Late Coming (+1 / +2 ONLY) -->
                          <?php if ($is_higher_sec): ?>
                            <label class="cursor-pointer block">
                              <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Late Coming" class="sr-only peer att-radio-input" <?php echo (in_array($curStatus, array('Late', 'Late Coming'))) ? 'checked' : ''; ?>>
                              <span class="w-full justify-center md:w-auto px-1.5 sm:px-3 h-10 md:h-auto md:py-1.5 rounded-lg text-[12px] sm:text-[13px] font-semibold flex items-center gap-1 border border-theme-border bg-white text-muted-text peer-checked:bg-indigo-100 peer-checked:text-indigo-900 peer-checked:border-indigo-400 peer-checked:shadow-xs transition-all text-center select-none">
                                <span class="material-symbols-outlined text-[17px] md:text-[16px]">schedule</span>Late
                              </span>
                            </label>
                          <?php endif; ?>

                          <!-- Absent -->
                          <label class="cursor-pointer block">
                            <input type="radio" name="attendance[<?php echo $st->student_id; ?>][status]" value="Absent" class="sr-only peer att-radio-input" <?php echo ($curStatus === 'Absent') ? 'checked' : ''; ?>>
                            <span class="w-full justify-center md:w-auto px-2 sm:px-3 h-10 md:h-auto md:py-1.5 rounded-lg text-[13px] font-semibold flex items-center gap-1.5 border border-theme-border bg-white text-muted-text peer-checked:bg-error-container peer-checked:text-on-error-container peer-checked:border-error peer-checked:shadow-xs transition-all text-center select-none">
                              <span class="material-symbols-outlined text-[17px] md:text-[16px]">cancel</span>Absent
                            </span>
                          </label>
                        </div>
                      </div>
                    </td>

                    <!-- Remarks Input (Full width below status on Mobile) -->
                    <td class="att-col-remarks block md:table-cell pt-1.5 md:pt-0 md:py-3.5 md:px-4 md:whitespace-nowrap">
                      <div class="mt-2 md:mt-0">
                        <input type="text" name="attendance[<?php echo $st->student_id; ?>][remarks]" value="<?php echo html_escape($st->remarks ?? ''); ?>" placeholder="Optional remarks..." class="w-full px-3 py-2 md:py-1.5 rounded-lg border border-theme-border bg-surface-container-lowest text-[13px] text-dark-text focus:ring-1 focus:ring-primary focus:border-primary placeholder:text-muted-text/60 transition-colors"/>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Bottom Sticky Submission Bar -->
          <div class="p-3.5 sm:p-5 border-t border-outline-variant/40 bg-white/95 backdrop-blur-md flex items-center justify-between gap-3 sticky bottom-0 z-10 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] sm:shadow-none">
            <div class="text-[13px] sm:text-body-md text-on-surface-variant font-medium shrink-0">
              <span class="hidden sm:inline">Total Students:</span><span class="sm:hidden">Total:</span> <strong class="text-on-surface"><?php echo count($students); ?></strong>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:px-6 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer shrink-0">
              <span class="material-symbols-outlined text-[18px]">save</span>
              <span><?php echo ($is_already_marked) ? 'Update Attendance' : 'Save Attendance'; ?></span>
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>

      <script>
        function markAllStatus(status) {
          document.querySelectorAll('.att-radio-input[value="' + status + '"]').forEach(function(radio) {
            radio.checked = true;
          });
        }
      </script>
    <?php endif; ?>

    <script>
      function onAttendanceGroupChanged(groupId) {
        var select = document.getElementById('attendance_class_select');
        if (!select) return;
        var firstMatch = null;
        for (var i = 0; i < select.options.length; i++) {
          var opt = select.options[i];
          var optGroup = opt.getAttribute('data-group');
          if (!groupId || optGroup == groupId) {
            opt.style.display = '';
            if (!firstMatch) firstMatch = opt.value;
          } else {
            opt.style.display = 'none';
          }
        }
        if (firstMatch && select.value !== firstMatch) {
          select.value = firstMatch;
          select.form.submit();
        }
      }
    </script>
