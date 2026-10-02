<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="p-6 max-w-7xl mx-auto space-y-6">

  <!-- Header & Actions -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-label-md text-on-surface-variant mb-1">
        <a href="<?php echo site_url('attendance'); ?>" class="hover:text-primary transition-colors">Attendance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="<?php echo site_url('attendance/class_attendance'); ?>" class="hover:text-primary transition-colors">Class Attendance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-on-surface font-semibold">View Attendance</span>
      </div>
      <h2 class="font-headline-md text-headline-md text-on-surface font-bold">View Attendance</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">View detailed attendance records for students within a selected date range.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <a href="<?php echo site_url('attendance/class_attendance?class_id=' . $class_id . '&academic_year_id=' . $academic_year_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Class Attendance
      </a>
      <a href="<?php echo site_url('attendance'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">dashboard</span>Overview
      </a>
    </div>
  </div>

  <!-- Warning Alert if From Date > To Date -->
  <?php if (!empty($date_warning)): ?>
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3">
      <span class="material-symbols-outlined text-amber-600 text-[22px] shrink-0 mt-0.5">warning</span>
      <div class="text-body-md font-medium">
        <?php echo htmlspecialchars($date_warning); ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Filters Bar -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
    <form method="get" action="<?php echo site_url('student-attendance/view'); ?>" id="attendanceViewFilterForm">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        
        <!-- Academic Year -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Academic Year *</label>
          <select name="academic_year_id" id="filter_academic_year" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y->academic_year_id; ?>" <?php echo ((int)$academic_year_id === (int)$y->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($y->year_name); ?><?php echo ($y->is_active) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- From Date -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">From Date *</label>
          <input type="date" name="from_date" id="filter_from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>

        <!-- To Date -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">To Date *</label>
          <input type="date" name="to_date" id="filter_to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>

        <!-- Academic Group -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Department / Group</label>
          <select id="filter_academic_group" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Department / Groups</option>
            <?php if (!empty($groups)): foreach ($groups as $grp): ?>
              <option value="<?php echo $grp->academic_group_id; ?>" <?php echo (!empty($selected_group_id) && $selected_group_id == $grp->academic_group_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($grp->group_name); ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Class -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class *</label>
          <select name="class_id" id="filter_class" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" data-group="<?php echo (int)($c->academic_group_id ?? 0); ?>" <?php echo ((int)$class_id === (int)$c->class_id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c->class_name); ?>
                (<?php echo ucfirst($c->attendance_type ?? 'daily'); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Session / Division -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Session / Division *</label>
          <select name="division_id" id="filter_division" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php if (empty($sections)): ?>
              <option value="<?php echo htmlspecialchars($division_id ?? ''); ?>" selected>Division A (Default)</option>
            <?php else: ?>
              <?php foreach ($sections as $s): ?>
                <option value="<?php echo ($s->division_id ?? $s->section_id); ?>" <?php echo ((int)$section_id === (int)($s->division_id ?? $s->section_id)) ? 'selected' : ''; ?>>
                  Division <?php echo htmlspecialchars($s->division_name ?? $s->section_name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between gap-4 mt-5 pt-4 border-t border-outline-variant/30 flex-wrap">
        <div class="flex items-center gap-2 text-label-md text-on-surface-variant font-medium">
          <span class="material-symbols-outlined text-[18px] text-secondary">info</span>
          <span>Actual school working days automatically exclude Sundays and calendar holidays.</span>
        </div>
        <div class="flex items-center gap-2">
          <a href="<?php echo site_url('student-attendance/view'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-medium">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset
          </a>
          <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span>Apply Filters
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Working Days Notice & Calendar Status -->
  <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-secondary text-[20px]">calendar_today</span>
      <span class="font-medium text-on-surface">Date Range:</span>
      <span class="font-semibold text-primary"><?php echo date('d M Y', strtotime($from_date)); ?></span>
      <span class="text-on-surface-variant">to</span>
      <span class="font-semibold text-primary"><?php echo date('d M Y', strtotime($to_date)); ?></span>
    </div>
    <div class="flex items-center gap-4 text-label-md">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high font-medium text-on-surface">
        <span class="material-symbols-outlined text-[15px] text-secondary">work</span>
        Total Working Days: <strong class="text-primary font-bold"><?php echo $summary->total_working_days; ?></strong>
      </span>
      <?php if (!empty($working_info->holidays)): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-medium text-[12px]" title="<?php echo htmlspecialchars(implode(', ', $working_info->holidays)); ?>">
          <span class="material-symbols-outlined text-[15px] text-amber-700">beach_access</span>
          <?php echo count($working_info->holidays); ?> Holiday(s) Excluded
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Attendance Summary Metric Cards Grid -->
  <?php if ($is_higher_sec): ?>
    <!-- +1 and +2 Cards (Total Classes, Present, Half Day, Late Coming, Absent) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <!-- Total Students -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Students</span>
          <span class="material-symbols-outlined text-[18px] text-primary">groups</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $summary->total_students; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Enrolled</div>
      </div>

      <!-- Total Classes Conducted -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Total Classes</span>
          <span class="material-symbols-outlined text-[18px] text-secondary">schedule</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $summary->total_classes; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Conducted Periods</div>
      </div>

      <!-- Present -->
      <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-emerald-800 flex items-center justify-between">
          <span>Present</span>
          <span class="material-symbols-outlined text-[18px] text-emerald-600">check_circle</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-emerald-900"><?php echo $summary->total_present; ?></div>
        <div class="text-[11px] text-emerald-700 mt-0.5">🟢 Present Periods</div>
      </div>

      <!-- Half Day -->
      <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-amber-900 flex items-center justify-between">
          <span>Half Day</span>
          <span class="material-symbols-outlined text-[18px] text-amber-600">timelapse</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-amber-900"><?php echo $summary->total_half_day; ?></div>
        <div class="text-[11px] text-amber-800 mt-0.5">🟡 Half Day</div>
      </div>

      <!-- Late Coming -->
      <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-indigo-900 flex items-center justify-between">
          <span>Late Coming</span>
          <span class="material-symbols-outlined text-[18px] text-indigo-600">schedule</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-indigo-900"><?php echo $summary->total_late; ?></div>
        <div class="text-[11px] text-indigo-700 mt-0.5">Late Entries</div>
      </div>

      <!-- Absent -->
      <div class="p-4 rounded-xl bg-red-50/60 border border-red-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-red-900 flex items-center justify-between">
          <span>Absent</span>
          <span class="material-symbols-outlined text-[18px] text-red-600">cancel</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-red-900"><?php echo $summary->total_absent; ?></div>
        <div class="text-[11px] text-red-700 mt-0.5">Absent Periods</div>
      </div>
    </div>
  <?php else: ?>
    <!-- LKG - 10 Cards (Total Students, Working Days, Present, Half Day, Absent) -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <!-- Total Students -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Students</span>
          <span class="material-symbols-outlined text-[18px] text-primary">groups</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $summary->total_students; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Enrolled</div>
      </div>

      <!-- Total Working Days -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Working Days</span>
          <span class="material-symbols-outlined text-[18px] text-secondary">calendar_month</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $summary->total_working_days; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">School Days</div>
      </div>

      <!-- Total Present -->
      <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-emerald-800 flex items-center justify-between">
          <span>Present</span>
          <span class="material-symbols-outlined text-[18px] text-emerald-600">check_circle</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-emerald-900"><?php echo $summary->total_present; ?></div>
        <div class="text-[11px] text-emerald-700 mt-0.5">🟢 Full Attendance</div>
      </div>

      <!-- Total Half Day -->
      <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-amber-900 flex items-center justify-between">
          <span>Half Day</span>
          <span class="material-symbols-outlined text-[18px] text-amber-600">timelapse</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-amber-900"><?php echo $summary->total_half_day; ?></div>
        <div class="text-[11px] text-amber-800 mt-0.5">🟡 Half Day</div>
      </div>

      <!-- Total Absent -->
      <div class="p-4 rounded-xl bg-red-50/60 border border-red-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-red-900 flex items-center justify-between">
          <span>Absent</span>
          <span class="material-symbols-outlined text-[18px] text-red-600">cancel</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-red-900"><?php echo $summary->total_absent; ?></div>
        <div class="text-[11px] text-red-700 mt-0.5">Absent Days</div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Student-wise Attendance Table Card -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="p-5 border-b border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="font-headline-md text-headline-md text-on-surface font-bold flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[22px]">badge</span>
          Student-wise Attendance
        </h3>
        <p class="text-body-md text-on-surface-variant mt-0.5">
          Showing enrolled students for <strong class="text-on-surface"><?php echo htmlspecialchars($selected_class ? $selected_class->class_name : 'Class'); ?></strong> — 
          <strong class="text-on-surface">Division <?php echo htmlspecialchars(($selected_division ?? $selected_section) ? (($selected_division ?? $selected_section)->division_name ?? ($selected_division ?? $selected_section)->section_name) : 'A'); ?></strong>
        </p>
      </div>
      <div class="text-label-md font-semibold text-on-surface-variant bg-surface-container-high px-3 py-1.5 rounded-lg self-start sm:self-auto">
        Formula: <span class="text-primary font-bold"><?php echo $is_higher_sec ? 'Present ÷ Total Classes × 100' : 'Present ÷ Total Working Days × 100'; ?></span>
      </div>
    </div>

    <?php if (empty($students)): ?>
      <div class="text-center py-16 px-4">
        <span class="material-symbols-outlined text-[48px] text-outline mb-3 block">person_off</span>
        <h4 class="text-title-md font-bold text-on-surface">No Students Found</h4>
        <p class="text-body-md text-on-surface-variant mt-1 max-w-md mx-auto">
          There are no active enrolled students found for <?php echo htmlspecialchars($selected_class ? $selected_class->class_name : 'the selected class'); ?> in Division <?php echo htmlspecialchars(($selected_division ?? $selected_section) ? (($selected_division ?? $selected_section)->division_name ?? ($selected_division ?? $selected_section)->section_name) : 'A'); ?> for this academic year.
        </p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-high/70 text-on-surface border-b border-outline-variant/40 text-label-md uppercase tracking-wider font-semibold text-[11px]">
              <th class="py-3 px-4 w-12 text-center">#</th>
              <th class="py-3 px-4 min-w-[200px]">Student Name</th>
              <th class="py-3 px-4 text-center">Roll No</th>
              <?php if ($is_higher_sec): ?>
                <th class="py-3 px-4 text-center">Total Classes</th>
                <th class="py-3 px-4 text-center">Present</th>
                <th class="py-3 px-4 text-center">Half Day</th>
                <th class="py-3 px-4 text-center">Late Coming</th>
                <th class="py-3 px-4 text-center">Absent</th>
              <?php else: ?>
                <th class="py-3 px-4 text-center">Working Days</th>
                <th class="py-3 px-4 text-center">Present</th>
                <th class="py-3 px-4 text-center">Half Day</th>
                <th class="py-3 px-4 text-center">Absent</th>
              <?php endif; ?>
              <th class="py-3 px-4 text-center min-w-[140px]">Attendance %</th>
              <th class="py-3 px-4 text-center w-24">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php foreach ($students as $idx => $st): ?>
              <?php
                $pct = $st->attendance_pct;
                $barColor = 'bg-emerald-500';
                $pillColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                if ($pct < 75) {
                    $barColor = 'bg-red-500';
                    $pillColor = 'bg-red-100 text-red-800 border-red-200';
                } elseif ($pct < 85) {
                    $barColor = 'bg-amber-500';
                    $pillColor = 'bg-amber-100 text-amber-900 border-amber-200';
                }
              ?>
              <tr class="hover:bg-surface-container-high/40 transition-colors">
                <td class="py-3.5 px-4 text-center text-on-surface-variant font-medium text-[13px]"><?php echo $idx + 1; ?></td>
                <td class="py-3.5 px-4 min-w-[200px]">
                  <a href="<?php echo site_url('student-attendance/details?student_id=' . $st->student_id . '&academic_year_id=' . $academic_year_id . '&from_date=' . $from_date . '&to_date=' . $to_date); ?>" class="flex items-center gap-3 group" title="View individual attendance details">
                    <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-[13px] shrink-0 group-hover:scale-105 transition-transform">
                      <?php echo strtoupper(substr($st->first_name, 0, 1)); ?>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface group-hover:text-primary transition-colors flex items-center gap-1">
                        <?php echo htmlspecialchars($st->first_name . ' ' . $st->last_name); ?>
                        <span class="material-symbols-outlined text-[15px] opacity-0 group-hover:opacity-100 transition-opacity">open_in_new</span>
                      </div>
                      <div class="text-[11px] text-on-surface-variant">Adm: <?php echo htmlspecialchars($st->admission_number ?: 'N/A'); ?></div>
                    </div>
                  </a>
                </td>
                <td class="py-3.5 px-4 text-center font-medium text-on-surface">
                  <?php echo htmlspecialchars($st->roll_number ?: '-'); ?>
                </td>
                
                <?php if ($is_higher_sec): ?>
                  <td class="py-3.5 px-4 text-center text-on-surface font-semibold">
                    <?php echo isset($st->total_classes) ? $st->total_classes : 0; ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                      🟢 <?php echo $st->present_count; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                      🟡 <?php echo $st->half_day_count; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-indigo-100 text-indigo-900 border border-indigo-200">
                      <?php echo $st->late_count; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-red-100 text-red-800 border border-red-200">
                      <?php echo $st->absent_count; ?>
                    </span>
                  </td>
                <?php else: ?>
                  <td class="py-3.5 px-4 text-center text-on-surface font-semibold">
                    <?php echo $st->working_days; ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                      🟢 <?php echo $st->present_count; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                      🟡 <?php echo $st->half_day_count; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-red-100 text-red-800 border border-red-200">
                      <?php echo $st->absent_count; ?>
                    </span>
                  </td>
                <?php endif; ?>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex flex-col items-center gap-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[12px] font-bold border <?php echo $pillColor; ?>">
                      <?php echo number_format($pct, 1); ?>%
                    </span>
                    <div class="w-20 bg-surface-container-high rounded-full h-1.5 overflow-hidden">
                      <div class="<?php echo $barColor; ?> h-1.5 rounded-full" style="width: <?php echo min(100, max(0, $pct)); ?>%"></div>
                    </div>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <a href="<?php echo site_url('student-attendance/details?student_id=' . $st->student_id . '&academic_year_id=' . $academic_year_id . '&from_date=' . $from_date . '&to_date=' . $to_date); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-primary hover:bg-surface-container-high text-[12px] font-semibold transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[15px]">visibility</span>Details
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <script>
    (function () {
      var groupSelect = document.getElementById('filter_academic_group');
      var classSelect = document.getElementById('filter_class');
      var divSelect   = document.getElementById('filter_division');

      if (groupSelect && classSelect) {
        groupSelect.addEventListener('change', function () {
          var gId = this.value;
          var firstVisible = null;
          for (var i = 0; i < classSelect.options.length; i++) {
            var opt = classSelect.options[i];
            var optGroup = opt.getAttribute('data-group');
            if (!gId || optGroup == gId) {
              opt.style.display = '';
              if (!firstVisible) firstVisible = opt.value;
            } else {
              opt.style.display = 'none';
            }
          }
          if (firstVisible && classSelect.value !== firstVisible) {
            classSelect.value = firstVisible;
            triggerClassChange(firstVisible);
          }
        });
      }

      if (classSelect && divSelect) {
        classSelect.addEventListener('change', function () {
          triggerClassChange(this.value);
        });
      }

      function triggerClassChange(classId) {
        if (!classId) return;
        var opt = classSelect.querySelector('option[value="' + classId + '"]');
        if (opt && groupSelect) {
          var optGroup = opt.getAttribute('data-group');
          if (optGroup && !groupSelect.value) {
            groupSelect.value = optGroup;
          }
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo site_url("students/get_divisions_ajax"); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var data = 'class_id=' + encodeURIComponent(classId);
        if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
          data += '&' + encodeURIComponent(window.CSRF_TOKEN_NAME) + '=' + encodeURIComponent(window.CSRF_HASH);
        }
        xhr.onload = function () {
          if (xhr.status === 200) {
            try {
              var r = JSON.parse(xhr.responseText);
              if (r.csrf_hash) window.CSRF_HASH = r.csrf_hash;
              var items = r.divisions || r.sections || [];
              if (items.length > 0) {
                var opts = '';
                items.forEach(function (d, idx) {
                  opts += '<option value="' + (d.division_id || d.section_id) + '" ' + (idx === 0 ? 'selected' : '') + '>Division ' + (d.division_name || d.section_name) + '</option>';
                });
                divSelect.innerHTML = opts;
              } else {
                divSelect.innerHTML = '<option value="" selected>Division A (Default)</option>';
              }
            } catch (e) {
              divSelect.innerHTML = '<option value="" selected>Division A (Default)</option>';
            }
          }
        };
        xhr.send(data);
      }
    })();
  </script>
</div>
