<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="p-6 max-w-7xl mx-auto space-y-6">

  <!-- Breadcrumbs & Navigation -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-label-md text-on-surface-variant mb-1 flex-wrap">
        <a href="<?php echo site_url('attendance'); ?>" class="hover:text-primary transition-colors">Attendance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="<?php echo site_url('attendance/class_attendance'); ?>" class="hover:text-primary transition-colors">Class Attendance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <a href="<?php echo site_url('student-attendance/view?class_id=' . $student->class_id . '&academic_year_id=' . $academic_year_id); ?>" class="hover:text-primary transition-colors">View Attendance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-on-surface font-semibold">Student Attendance Details</span>
      </div>
      <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Student Attendance Details</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Complete attendance profile and subject-wise breakdown for <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <a href="<?php echo site_url('student-attendance/view?class_id=' . $student->class_id . '&academic_year_id=' . $academic_year_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-medium">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Attendance List
      </a>
      <a href="<?php echo site_url('students/view/' . $student->student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-primary bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-medium">
        <span class="material-symbols-outlined text-[18px]">account_circle</span>Student Profile
      </a>
    </div>
  </div>

  <!-- Student Profile Hero Card -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center gap-5">
      <?php if (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo)): ?>
        <img src="<?php echo base_url('uploads/students/' . $student->photo); ?>" alt="<?php echo htmlspecialchars($student->first_name); ?>" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary/20 shadow-sm shrink-0">
      <?php else: ?>
        <div class="w-20 h-20 rounded-2xl bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-[28px] shadow-sm shrink-0 border border-primary/20">
          <?php echo strtoupper(substr($student->first_name, 0, 1)); ?>
        </div>
      <?php endif; ?>

      <div class="space-y-1">
        <div class="flex items-center gap-3 flex-wrap">
          <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
            <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>
          </h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
            Active Student
          </span>
        </div>

        <div class="flex items-center gap-4 text-body-md text-on-surface-variant flex-wrap pt-0.5">
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[17px] text-secondary">tag</span>
            <span>Adm No: <strong class="text-on-surface"><?php echo htmlspecialchars($student->admission_number ?: 'N/A'); ?></strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[17px] text-secondary">pin</span>
            <span>Roll No: <strong class="text-on-surface"><?php echo htmlspecialchars($student->roll_number ?: '-'); ?></strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[17px] text-secondary">school</span>
            <span>Class: <strong class="text-on-surface"><?php echo htmlspecialchars($student->class_name); ?></strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[17px] text-secondary">meeting_room</span>
            <span>Division: <strong class="text-on-surface"><?php echo htmlspecialchars(!empty(($student->division_name ?? $student->section_name)) ? ($student->division_name ?? $student->section_name) : 'A'); ?></strong></span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[17px] text-secondary">calendar_month</span>
            <span>Academic Year: <strong class="text-on-surface"><?php echo htmlspecialchars($current_year ? $current_year->year_name : ''); ?></strong></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Percentage Badge -->
    <?php
      $overallPct = $overall_summary ? $overall_summary->attendance_pct : 0.0;
      $badgeBg = 'bg-emerald-500';
      $badgeText = 'text-emerald-700 bg-emerald-50 border-emerald-200';
      if ($overallPct < 75) {
          $badgeBg = 'bg-red-500';
          $badgeText = 'text-red-700 bg-red-50 border-red-200';
      } elseif ($overallPct < 85) {
          $badgeBg = 'bg-amber-500';
          $badgeText = 'text-amber-800 bg-amber-50 border-amber-200';
      }
    ?>
    <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center p-4 rounded-xl border <?php echo $badgeText; ?> shrink-0 min-w-[160px]">
      <div class="text-[12px] font-semibold uppercase tracking-wider">Overall Attendance</div>
      <div class="text-headline-md font-extrabold tracking-tight"><?php echo number_format($overallPct, 2); ?>%</div>
      <div class="w-full bg-surface-container-high rounded-full h-1.5 overflow-hidden mt-1.5 hidden md:block">
        <div class="<?php echo $badgeBg; ?> h-1.5 rounded-full" style="width: <?php echo min(100, max(0, $overallPct)); ?>%"></div>
      </div>
    </div>
  </div>

  <!-- Date Range Warning Alert -->
  <?php if (!empty($date_warning)): ?>
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3">
      <span class="material-symbols-outlined text-amber-600 text-[22px] shrink-0 mt-0.5">warning</span>
      <div class="text-body-md font-medium">
        <?php echo htmlspecialchars($date_warning); ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Academic Year & Date Range Selector -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
    <form method="get" action="<?php echo site_url('student-attendance/details'); ?>" id="studentAttendanceFilterForm">
      <input type="hidden" name="student_id" value="<?php echo $student->student_id; ?>">

      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
          
          <!-- Academic Year -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Academic Year</label>
            <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php foreach ($years as $y): ?>
                <option value="<?php echo $y->academic_year_id; ?>" <?php echo ((int)$academic_year_id === (int)$y->academic_year_id) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($y->year_name); ?><?php echo ($y->is_active) ? ' (Active)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Attendance Period Mode -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Attendance Period</label>
            <select name="period_mode" id="period_mode" onchange="toggleCustomDates(this.value)" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="academic_year" <?php echo ($period_mode === 'academic_year') ? 'selected' : ''; ?>>Full Academic Year</option>
              <option value="custom" <?php echo ($period_mode === 'custom') ? 'selected' : ''; ?>>Custom Date Range</option>
            </select>
          </div>

          <!-- From Date -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">From Date</label>
            <input type="date" name="from_date" id="input_from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
          </div>

          <!-- To Date -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">To Date</label>
            <input type="date" name="to_date" id="input_to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
          </div>

        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 shrink-0">
          <a href="<?php echo site_url('student-attendance/details?student_id=' . $student->student_id . '&academic_year_id=' . $academic_year_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-medium">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset
          </a>
          <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span>Apply
          </button>
        </div>

      </div>
    </form>
  </div>

  <!-- Working Days Notice & Calendar Status -->
  <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-secondary text-[20px]">date_range</span>
      <span class="font-medium text-on-surface">Calculated Period:</span>
      <span class="font-semibold text-primary"><?php echo date('d M Y', strtotime($from_date)); ?></span>
      <span class="text-on-surface-variant">to</span>
      <span class="font-semibold text-primary"><?php echo date('d M Y', strtotime($to_date)); ?></span>
    </div>
    <div class="flex items-center gap-4 text-label-md">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high font-medium text-on-surface">
        <span class="material-symbols-outlined text-[15px] text-secondary">work</span>
        Total Working Days: <strong class="text-primary font-bold"><?php echo $overall_summary ? $overall_summary->total_working_days : 0; ?></strong>
      </span>
      <?php if (!empty($working_info->holidays)): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-medium text-[12px]" title="<?php echo htmlspecialchars(implode(', ', $working_info->holidays)); ?>">
          <span class="material-symbols-outlined text-[15px] text-amber-700">beach_access</span>
          <?php echo count($working_info->holidays); ?> Holiday(s) Excluded
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Overall Attendance Summary Cards -->
  <?php if ($is_higher_sec): ?>
    <!-- Higher Secondary (+1 / +2) Cards: Total Classes, Present, Half Day, Late Coming, Absent, Attendance % -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <!-- Total Classes -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Total Classes</span>
          <span class="material-symbols-outlined text-[18px] text-secondary">schedule</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $overall_summary ? $overall_summary->total_classes : 0; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Conducted Periods</div>
      </div>

      <!-- Present -->
      <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-emerald-800 flex items-center justify-between">
          <span>Present</span>
          <span class="material-symbols-outlined text-[18px] text-emerald-600">check_circle</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-emerald-900"><?php echo $overall_summary ? $overall_summary->present : 0; ?></div>
        <div class="text-[11px] text-emerald-700 mt-0.5">🟢 Present Periods</div>
      </div>

      <!-- Half Day -->
      <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-amber-900 flex items-center justify-between">
          <span>Half Day</span>
          <span class="material-symbols-outlined text-[18px] text-amber-600">timelapse</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-amber-900"><?php echo $overall_summary ? $overall_summary->half_day : 0; ?></div>
        <div class="text-[11px] text-amber-800 mt-0.5">🟡 Half Day</div>
      </div>

      <!-- Late Coming -->
      <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-indigo-900 flex items-center justify-between">
          <span>Late Coming</span>
          <span class="material-symbols-outlined text-[18px] text-indigo-600">schedule</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-indigo-900"><?php echo $overall_summary ? $overall_summary->late : 0; ?></div>
        <div class="text-[11px] text-indigo-700 mt-0.5">Late Entries</div>
      </div>

      <!-- Absent -->
      <div class="p-4 rounded-xl bg-red-50/70 border border-red-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-red-900 flex items-center justify-between">
          <span>Absent</span>
          <span class="material-symbols-outlined text-[18px] text-red-600">cancel</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-red-900"><?php echo $overall_summary ? $overall_summary->absent : 0; ?></div>
        <div class="text-[11px] text-red-700 mt-0.5">Missed Classes</div>
      </div>

      <!-- Attendance % -->
      <div class="p-4 rounded-xl bg-surface-container-high border border-outline-variant/60 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Attendance</span>
          <span class="material-symbols-outlined text-[18px] text-primary">percent</span>
        </div>
        <div class="mt-2 text-headline-sm font-extrabold text-primary"><?php echo number_format($overallPct, 2); ?>%</div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Present ÷ Classes</div>
      </div>
    </div>
  <?php else: ?>
    <!-- LKG - 10 Cards: Working Days, Present, Half Day, Absent, Attendance % -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <!-- Working Days -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Working Days</span>
          <span class="material-symbols-outlined text-[18px] text-secondary">calendar_month</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-on-surface"><?php echo $overall_summary ? $overall_summary->total_working_days : 0; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">School Days</div>
      </div>

      <!-- Present -->
      <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-emerald-800 flex items-center justify-between">
          <span>Present</span>
          <span class="material-symbols-outlined text-[18px] text-emerald-600">check_circle</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-emerald-900"><?php echo $overall_summary ? $overall_summary->present : 0; ?></div>
        <div class="text-[11px] text-emerald-700 mt-0.5">🟢 Full Attendance</div>
      </div>

      <!-- Half Day -->
      <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-amber-900 flex items-center justify-between">
          <span>Half Day</span>
          <span class="material-symbols-outlined text-[18px] text-amber-600">timelapse</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-amber-900"><?php echo $overall_summary ? $overall_summary->half_day : 0; ?></div>
        <div class="text-[11px] text-amber-800 mt-0.5">🟡 Half Day</div>
      </div>

      <!-- Absent -->
      <div class="p-4 rounded-xl bg-red-50/70 border border-red-200 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-red-900 flex items-center justify-between">
          <span>Absent</span>
          <span class="material-symbols-outlined text-[18px] text-red-600">cancel</span>
        </div>
        <div class="mt-2 text-headline-sm font-bold text-red-900"><?php echo $overall_summary ? $overall_summary->absent : 0; ?></div>
        <div class="text-[11px] text-red-700 mt-0.5">Absent Days</div>
      </div>

      <!-- Attendance % -->
      <div class="p-4 rounded-xl bg-surface-container-high border border-outline-variant/60 elevation-1 flex flex-col justify-between">
        <div class="text-[12px] font-medium text-on-surface-variant flex items-center justify-between">
          <span>Attendance</span>
          <span class="material-symbols-outlined text-[18px] text-primary">percent</span>
        </div>
        <div class="mt-2 text-headline-sm font-extrabold text-primary"><?php echo number_format($overallPct, 2); ?>%</div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Present ÷ Working Days</div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($is_higher_sec): ?>
    <!-- Subject-wise Attendance Section (+1 and +2 ONLY) -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="p-5 border-b border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-headline-md text-headline-md text-on-surface font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">menu_book</span>
            Subject-wise Attendance
          </h3>
          <p class="text-body-md text-on-surface-variant mt-0.5">
            Detailed class breakdown across all enrolled subjects for <strong class="text-on-surface"><?php echo htmlspecialchars($student->class_name); ?></strong>.
          </p>
        </div>
        <div class="text-label-md font-semibold text-on-surface-variant bg-surface-container-high px-3 py-1.5 rounded-lg self-start sm:self-auto">
          Formula: <span class="text-primary font-bold">Present ÷ Total Classes × 100</span>
        </div>
      </div>

      <?php if (empty($subject_wise)): ?>
        <div class="text-center py-12 px-4">
          <span class="material-symbols-outlined text-[44px] text-outline mb-2 block">library_books</span>
          <h4 class="text-title-md font-bold text-on-surface">No Subjects Configured</h4>
          <p class="text-body-md text-on-surface-variant mt-1 max-w-md mx-auto">
            No subjects have been configured for <?php echo htmlspecialchars($student->class_name); ?> yet.
          </p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-high/70 text-on-surface border-b border-outline-variant/40 text-label-md uppercase tracking-wider font-semibold text-[11px]">
                <th class="py-3 px-4 w-12 text-center">#</th>
                <th class="py-3 px-4 min-w-[200px]">Subject</th>
                <th class="py-3 px-4 text-center">Type</th>
                <th class="py-3 px-4 text-center">Total Classes</th>
                <th class="py-3 px-4 text-center">Present</th>
                <th class="py-3 px-4 text-center">Half Day</th>
                <th class="py-3 px-4 text-center">Late Coming</th>
                <th class="py-3 px-4 text-center">Absent</th>
                <th class="py-3 px-4 text-center min-w-[140px]">Attendance %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-body-md">
              <?php foreach ($subject_wise as $idx => $sub): ?>
                <?php
                  $sPct = $sub->attendance_pct;
                  $sBarColor = 'bg-emerald-500';
                  $sPillColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                  if ($sub->total_classes > 0) {
                      if ($sPct < 75) {
                          $sBarColor = 'bg-red-500';
                          $sPillColor = 'bg-red-100 text-red-800 border-red-200';
                      } elseif ($sPct < 85) {
                          $sBarColor = 'bg-amber-500';
                          $sPillColor = 'bg-amber-100 text-amber-900 border-amber-200';
                      }
                  } else {
                      $sBarColor = 'bg-outline-variant';
                      $sPillColor = 'bg-surface-container-high text-on-surface-variant border-outline-variant/40';
                  }
                ?>
                <tr class="hover:bg-surface-container-high/40 transition-colors">
                  <td class="py-3.5 px-4 text-center text-on-surface-variant font-medium text-[13px]"><?php echo $idx + 1; ?></td>
                  <td class="py-3.5 px-4 min-w-[200px]">
                    <div class="font-semibold text-on-surface"><?php echo htmlspecialchars($sub->subject_name); ?></div>
                    <?php if (!empty($sub->subject_code)): ?>
                      <div class="text-[11px] text-on-surface-variant font-mono"><?php echo htmlspecialchars($sub->subject_code); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container-high text-on-surface-variant">
                      <?php echo htmlspecialchars($sub->subject_type ?: 'Core'); ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center font-bold text-on-surface">
                    <?php echo $sub->total_classes; ?>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                      🟢 <?php echo $sub->present; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                      🟡 <?php echo $sub->half_day; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-indigo-100 text-indigo-900 border border-indigo-200">
                      <?php echo $sub->late; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold bg-red-100 text-red-900 border border-red-200">
                      <?php echo $sub->absent; ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="flex flex-col items-center gap-1">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[12px] font-bold border <?php echo $sPillColor; ?>">
                        <?php echo ($sub->total_classes > 0) ? number_format($sPct, 2) . '%' : 'N/A'; ?>
                      </span>
                      <?php if ($sub->total_classes > 0): ?>
                        <div class="w-20 bg-surface-container-high rounded-full h-1.5 overflow-hidden">
                          <div class="<?php echo $sBarColor; ?> h-1.5 rounded-full" style="width: <?php echo min(100, max(0, $sPct)); ?>%"></div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Attendance History Log for this Student -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-headline-md text-headline-md text-on-surface font-bold flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">history</span>
        <?php echo $is_higher_sec ? 'Period-wise Attendance History (' . count($period_records) . ' Recorded Periods)' : 'Daily Attendance History (' . count($daily_records) . ' Recorded Days)'; ?>
      </h3>
    </div>

    <?php
      $history_records = $is_higher_sec ? $period_records : $daily_records;
    ?>

    <?php if (empty($history_records)): ?>
      <div class="text-center py-8 text-on-surface-variant text-body-md">
        <span class="material-symbols-outlined text-[36px] text-outline mb-1.5 block">event_busy</span>
        No attendance records found for the selected period.
      </div>
    <?php else: ?>
      <div class="divide-y divide-outline-variant/30 max-h-[360px] overflow-y-auto">
        <?php foreach ($history_records as $rec): ?>
          <?php
            $badge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            $icon  = 'check_circle';
            if ($rec->attendance_status === 'Absent') {
                $badge = 'bg-red-100 text-red-800 border-red-200';
                $icon  = 'cancel';
            } elseif (in_array($rec->attendance_status, array('Half Day', 'Late / Half Day', 'Half-day'))) {
                $badge = 'bg-amber-100 text-amber-900 border-amber-200';
                $icon  = 'timelapse';
            } elseif (in_array($rec->attendance_status, array('Late', 'Late Coming'))) {
                $badge = 'bg-indigo-100 text-indigo-900 border-indigo-200';
                $icon  = 'schedule';
            }
          ?>
          <div class="py-3 flex items-center justify-between gap-3 text-body-md">
            <div class="flex items-center gap-3 min-w-0">
              <span class="material-symbols-outlined text-on-surface-variant text-[20px]">event</span>
              <div>
                <div class="font-semibold text-on-surface">
                  <?php echo date('D, d M Y', strtotime($rec->attendance_date)); ?>
                  <?php if ($is_higher_sec && !empty($rec->period_name)): ?>
                    <span class="ml-2 text-label-md px-2 py-0.5 rounded bg-surface-container-high text-on-surface font-normal">
                      <?php echo htmlspecialchars($rec->period_name); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($rec->remarks)): ?>
                  <div class="text-[12px] text-on-surface-variant italic"><?php echo htmlspecialchars($rec->remarks); ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex items-center gap-4 shrink-0">
              <div class="text-right text-body-sm">
                <div class="text-on-surface font-medium">
                  <?php if (!empty($rec->marked_by_display) && $rec->marked_by_display !== 'Not Available'): ?>
                    <span class="text-on-surface-variant text-[11px] block font-normal">Marked by</span>
                    <span><?php echo html_escape($rec->marked_by_display); ?></span>
                  <?php else: ?>
                    <span class="text-on-surface-variant/70 italic text-[12px]">Not Available</span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($rec->updated_by_display)): ?>
                  <div class="text-[10px] text-on-surface-variant/80">Edited by: <?php echo html_escape($rec->updated_by_display); ?></div>
                <?php endif; ?>
              </div>
              <div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[12px] font-bold border <?php echo $badge; ?>">
                  <span class="material-symbols-outlined text-[14px]"><?php echo $icon; ?></span>
                  <?php echo htmlspecialchars($rec->attendance_status); ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
function toggleCustomDates(mode) {
  // Can be used for dynamic interactivity if needed
}
</script>
