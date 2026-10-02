<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- =========================================================================
     Dashboard — School Management
     All figures are live from the database via Dashboard_model.
     All cards and widgets are fully dynamic, interactive, and permission-aware.
     ========================================================================= -->

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="font-headline-md text-headline-md text-on-surface">
      Welcome back, <?php echo html_escape($current_user->name ?? 'User'); ?>
    </h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">
      Here's what's happening across today &mdash; <?php echo date('d M Y', strtotime($today)); ?>.
    </p>
  </div>
  <div class="flex items-center gap-2 shrink-0">
    <?php if (!empty($can_view_students)): ?>
    <a href="<?php echo site_url('students/add'); ?>"
       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-label-md font-semibold hover:bg-primary-dark transition-colors shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[18px]">person_add</span>
      Add Student
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ── Stat Cards ──────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

  <!-- Total Students -->
  <?php if (!empty($can_view_students)): ?>
  <a href="<?php echo site_url('students/all_students?status=All&academic_year_id=' . (int)$year_id . '&class_id=all'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View All Students">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/50 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">group</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo number_format($stats->total_students ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Total Students</span>
      <?php if (!empty($can_view_students)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_students)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Total Teachers -->
  <?php if (!empty($can_view_staff)): ?>
  <a href="<?php echo site_url('staff/teachers'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Teachers Directory">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">person</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo number_format($stats->total_teachers ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Total Teachers</span>
      <?php if (!empty($can_view_staff)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_staff)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Total Staff -->
  <?php if (!empty($can_view_staff)): ?>
  <a href="<?php echo site_url('staff/non_teaching'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Non-Teaching Staff Directory">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">badge</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo number_format($stats->total_staff ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Total Staff</span>
      <?php if (!empty($can_view_staff)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_staff)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Total Classes -->
  <?php if (!empty($can_view_academics)): ?>
  <a href="<?php echo site_url('academics/classes'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Academic Classes">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">meeting_room</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo number_format($stats->total_classes ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Total Classes</span>
      <?php if (!empty($can_view_academics)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_academics)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Today's Attendance -->
  <?php if (!empty($can_view_attendance)): ?>
  <a href="<?php echo site_url('attendance/reports'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Attendance Report">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">fact_check</span>
      </div>
      <?php if (($attendance->total_marked ?? 0) > 0): ?>
        <span class="text-[12px] font-semibold text-primary px-2 py-0.5 rounded-full bg-light-blue border border-sky-blue/30"><?php echo $attendance->pct; ?>%</span>
      <?php endif; ?>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo (($attendance->total_marked ?? 0) > 0) ? ($attendance->pct . '%') : 'N/A'; ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Today's Attendance</span>
      <?php if (!empty($can_view_attendance)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_attendance)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Fees Collected (MTD) - Beige Accent -->
  <?php if (!empty($can_view_fees)): ?>
  <a href="<?php echo site_url('finance/fee_collection'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Fees Collection">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-beige text-dark-text border border-theme-border flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">payments</span>
      </div>
      <span class="text-[11px] font-semibold text-muted-text px-2 py-0.5 rounded-full bg-beige border border-theme-border">Monthly</span>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo school_currency($fees->monthly_collection ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>Fees Collected (MTD)</span>
      <?php if (!empty($can_view_fees)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_fees)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- Pending Fees -->
  <?php if (!empty($can_view_fees)): ?>
  <a href="<?php echo site_url('finance/pending_fees'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Outstanding / Due Fees">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-error-container text-error border border-error/20 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo school_currency($fees->total_pending ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-error transition-colors flex items-center gap-1">
      <span>Pending Fees</span>
      <?php if (!empty($can_view_fees)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_fees)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

  <!-- New Admissions -->
  <?php if (!empty($can_view_students)): ?>
  <a href="<?php echo site_url('students/admissions'); ?>"
     class="block elevation-1 rounded-2xl bg-white border border-outline-variant p-4 hover:border-primary/50 hover:shadow-md transition-all duration-200 cursor-pointer group"
     title="View Student Admissions">
  <?php else: ?>
  <div class="elevation-1 rounded-2xl bg-white border border-outline-variant p-4">
  <?php endif; ?>
    <div class="flex items-center justify-between">
      <div class="w-10 h-10 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-[20px]">person_add</span>
      </div>
    </div>
    <div class="mt-3 font-headline-lg text-headline-lg text-dark-text font-bold">
      <?php echo number_format($stats->new_admissions ?? 0); ?>
    </div>
    <div class="text-body-md font-body-md text-muted-text group-hover:text-primary transition-colors flex items-center gap-1">
      <span>New Admissions (Month)</span>
      <?php if (!empty($can_view_students)): ?>
        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
      <?php endif; ?>
    </div>
  <?php if (!empty($can_view_students)): ?>
  </a>
  <?php else: ?>
  </div>
  <?php endif; ?>

</div>

<!-- ── Middle Row: Attendance + Student Overview ───────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

  <!-- Attendance Summary (2/3 width) -->
  <div class="lg:col-span-2 elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface">Today's Attendance Summary</h3>
      <?php if (!empty($can_view_attendance)): ?>
      <a href="<?php echo site_url('attendance/reports'); ?>"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">chevron_right</span>View Report
      </a>
      <?php endif; ?>
    </div>
    <div class="p-5">
      <?php if (($attendance->total_marked ?? 0) > 0): ?>
        <div class="grid grid-cols-3 gap-4 mb-5 text-center">
          <div class="p-3 rounded-lg bg-surface-container-low/40">
            <div class="font-headline-lg text-headline-lg text-on-secondary-container font-semibold">
              <?php echo number_format($attendance->present ?? 0); ?>
            </div>
            <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Present</div>
          </div>
          <div class="p-3 rounded-lg bg-surface-container-low/40">
            <div class="font-headline-lg text-headline-lg text-amber-600 font-semibold">
              <?php echo number_format($attendance->half_day ?? 0); ?>
            </div>
            <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Half Day</div>
          </div>
          <div class="p-3 rounded-lg bg-surface-container-low/40">
            <div class="font-headline-lg text-headline-lg text-error font-semibold">
              <?php echo number_format($attendance->absent ?? 0); ?>
            </div>
            <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Absent</div>
          </div>
        </div>
        <div class="w-full h-2.5 rounded-full bg-surface-container-high overflow-hidden flex">
          <div class="h-full bg-secondary transition-all duration-300" style="width:<?php echo $attendance->present_pct ?? $attendance->pct; ?>%" title="Present: <?php echo $attendance->present_pct; ?>%"></div>
          <div class="h-full bg-amber-500 transition-all duration-300" style="width:<?php echo $attendance->half_day_pct ?? 0; ?>%" title="Half Day: <?php echo $attendance->half_day_pct; ?>%"></div>
          <div class="h-full bg-error transition-all duration-300" style="width:<?php echo $attendance->absent_pct ?? 0; ?>%" title="Absent: <?php echo $attendance->absent_pct; ?>%"></div>
        </div>
        <div class="flex items-center justify-between text-body-md font-body-md text-on-surface-variant mt-3">
          <span><?php echo $attendance->pct; ?>% attendance across all active divisions today (<?php echo number_format($attendance->total_marked); ?> recorded).</span>
          <?php if (!empty($can_view_attendance)): ?>
            <a href="<?php echo site_url('attendance/reports'); ?>" class="text-secondary hover:underline text-xs font-semibold">Detailed Report &rarr;</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <span class="material-symbols-outlined text-[40px] text-on-surface-variant mb-2 block">event_busy</span>
          <p class="text-body-md font-body-md text-on-surface-variant">No attendance marked yet for today.</p>
          <?php if (!empty($can_mark_attendance)): ?>
          <a href="<?php echo site_url('attendance/mark_attendance'); ?>"
             class="mt-3 inline-flex items-center gap-1 text-secondary text-body-md font-body-md hover:underline font-semibold">
            <span class="material-symbols-outlined text-[16px]">add</span>+ Class Attendance
          </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Student Overview (1/3 width) -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface">Student Overview</h3>
    </div>
    <div class="p-5">
      <div class="space-y-3">

        <!-- New Admissions (this month) -->
        <?php if (!empty($can_view_students)): ?>
        <a href="<?php echo site_url('students/admissions'); ?>"
           class="flex items-center justify-between text-body-md font-body-md hover:text-secondary group transition-colors py-0.5"
           title="View Admissions this month">
          <span class="text-on-surface-variant group-hover:text-secondary transition-colors">New Admissions (this month)</span>
          <span class="font-semibold text-on-surface group-hover:text-secondary transition-colors flex items-center gap-1">
            <?php echo number_format($stats->new_admissions ?? 0); ?>
            <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
          </span>
        </a>
        <?php else: ?>
        <div class="flex items-center justify-between text-body-md font-body-md py-0.5">
          <span class="text-on-surface-variant">New Admissions (this month)</span>
          <span class="font-semibold text-on-surface"><?php echo number_format($stats->new_admissions ?? 0); ?></span>
        </div>
        <?php endif; ?>

        <!-- Active Students -->
        <?php if (!empty($can_view_students)): ?>
        <a href="<?php echo site_url('students/all_students?status=1&academic_year_id=' . (int)$year_id . '&class_id=all'); ?>"
           class="flex items-center justify-between text-body-md font-body-md hover:text-secondary group transition-colors py-0.5"
           title="View Active Students">
          <span class="text-on-surface-variant group-hover:text-secondary transition-colors">Active Students</span>
          <span class="font-semibold text-on-surface group-hover:text-secondary transition-colors flex items-center gap-1">
            <?php echo number_format($stats->active_students ?? 0); ?>
            <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
          </span>
        </a>
        <?php else: ?>
        <div class="flex items-center justify-between text-body-md font-body-md py-0.5">
          <span class="text-on-surface-variant">Active Students</span>
          <span class="font-semibold text-on-surface"><?php echo number_format($stats->active_students ?? 0); ?></span>
        </div>
        <?php endif; ?>

        <!-- Boys / Girls -->
        <div class="flex items-center justify-between text-body-md font-body-md py-0.5">
          <span class="text-on-surface-variant">Boys / Girls</span>
          <?php if (!empty($can_view_students)): ?>
          <span class="font-semibold text-on-surface">
            <a href="<?php echo site_url('students/all_students?gender=Male&academic_year_id=' . (int)$year_id . '&class_id=all'); ?>"
               class="hover:text-secondary hover:underline transition-colors"
               title="Filter by Boys (Male)">
              <?php echo number_format($stats->male_students ?? 0); ?>
            </a>
            <span class="text-on-surface-variant mx-1">/</span>
            <a href="<?php echo site_url('students/all_students?gender=Female&academic_year_id=' . (int)$year_id . '&class_id=all'); ?>"
               class="hover:text-secondary hover:underline transition-colors"
               title="Filter by Girls (Female)">
              <?php echo number_format($stats->female_students ?? 0); ?>
            </a>
          </span>
          <?php else: ?>
          <span class="font-semibold text-on-surface">
            <?php echo number_format($stats->male_students ?? 0); ?> / <?php echo number_format($stats->female_students ?? 0); ?>
          </span>
          <?php endif; ?>
        </div>

        <!-- STUDENTS BY CLASS -->
        <?php if (!empty($students_by_class)): ?>
        <div class="pt-3 border-t border-outline-variant/50">
          <div class="text-label-md font-label-md text-on-surface-variant uppercase mb-2">Students by Class</div>
          <?php
          // Find max student count for proportional bar widths
          $counts_arr = array_column((array)$students_by_class, 'student_count') ?: [1];
          $max_count = max(array_map('intval', $counts_arr));
          if ($max_count < 1) {
            $max_count = 1;
          }

          foreach ($students_by_class as $cls):
            $count = (int)$cls->student_count;
            $pct = round(($count / $max_count) * 100);
          ?>
            <?php if (!empty($can_view_students)): ?>
            <a href="<?php echo site_url('students/all_students?class_id=' . (int)$cls->class_id . '&academic_year_id=' . (int)$year_id); ?>"
               class="flex items-center gap-2 mb-2 p-1.5 -mx-1.5 rounded-lg hover:bg-surface-container-high/60 transition-colors group cursor-pointer"
               title="View <?php echo html_escape($cls->class_name); ?> students">
            <?php else: ?>
            <div class="flex items-center gap-2 mb-2 p-1.5 -mx-1.5">
            <?php endif; ?>

              <span class="w-20 text-[12px] text-on-surface-variant group-hover:text-on-surface font-medium truncate">
                <?php echo html_escape($cls->class_name); ?>
              </span>
              <div class="flex-1 h-2 rounded-full bg-surface-container-high overflow-hidden">
                <div class="h-full bg-primary-container group-hover:bg-secondary transition-all duration-300" style="width:<?php echo $pct; ?>%"></div>
              </div>
              <span class="text-[12px] text-on-surface-variant group-hover:text-on-surface font-semibold w-7 text-right font-mono">
                <?php echo $count; ?>
              </span>

            <?php if (!empty($can_view_students)): ?>
            </a>
            <?php else: ?>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="pt-3 border-t border-outline-variant/50 text-xs text-on-surface-variant">
          No classes configured for this academic year.
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

</div>

<!-- ── Bottom Row: Fees + Events + Notices ────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Fees Overview -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface">Fees Overview</h3>
      <?php if (!empty($can_view_fees)): ?>
      <a href="<?php echo site_url('finance/dashboard'); ?>"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">chevron_right</span>Open
      </a>
      <?php endif; ?>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <div class="text-body-md font-body-md text-on-surface-variant">Today's Collection</div>
          <div class="font-headline-md text-headline-md text-on-surface">
            <?php echo school_currency($fees->today_collection ?? 0); ?>
          </div>
        </div>
        <div>
          <div class="text-body-md font-body-md text-on-surface-variant">Monthly Collection</div>
          <div class="font-headline-md text-headline-md text-on-surface">
            <?php echo school_currency($fees->monthly_collection ?? 0); ?>
          </div>
        </div>
        <div>
          <div class="text-body-md font-body-md text-on-surface-variant">Pending Fees</div>
          <div class="font-headline-md text-headline-md text-on-tertiary-container">
            <?php echo school_currency($fees->total_pending ?? 0); ?>
          </div>
        </div>
        <div>
          <div class="text-body-md font-body-md text-on-surface-variant">Overdue Fees</div>
          <div class="font-headline-md text-headline-md text-error">
            <?php echo school_currency($fees->overdue_amount ?? 0); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Upcoming Events -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface">Upcoming Events</h3>
    </div>
    <div class="p-5">
      <?php if (!empty($upcoming_events)): ?>
        <ul class="space-y-3">
          <?php foreach ($upcoming_events as $ev):
            $ev_date = new DateTime($ev->event_date);
          ?>
          <li class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary text-white flex flex-col items-center justify-center text-[11px] font-semibold leading-none shrink-0">
              <span><?php echo $ev_date->format('d'); ?></span>
              <span><?php echo strtoupper($ev_date->format('M')); ?></span>
            </div>
            <div class="min-w-0">
              <div class="text-body-md font-body-md text-on-surface truncate"><?php echo html_escape($ev->title); ?></div>
              <div class="text-[12px] text-on-surface-variant">
                <?php echo html_escape($ev->audience); ?>
                <?php if (!empty($ev->venue)): ?> &middot; <?php echo html_escape($ev->venue); ?><?php endif; ?>
              </div>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-center py-6">
          <span class="material-symbols-outlined text-[36px] text-on-surface-variant block mb-1">event</span>
          <p class="text-body-md font-body-md text-on-surface-variant">No upcoming events.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Notices -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface">Recent Notices</h3>
      <a href="<?php echo site_url('communication/notices'); ?>"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">chevron_right</span>View All
      </a>
    </div>
    <div class="p-5">
      <?php if (!empty($recent_notices)): ?>
        <ul class="divide-y divide-outline-variant/50">
          <?php foreach ($recent_notices as $notice): ?>
          <li class="py-3 first:pt-0 last:pb-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="text-body-md font-body-md text-on-surface font-medium">
                  <?php echo html_escape($notice->title); ?>
                </div>
                <div class="text-[12px] text-on-surface-variant mt-0.5">
                  <?php echo html_escape($notice->posted_by); ?>
                </div>
              </div>
              <span class="text-[11px] text-on-surface-variant whitespace-nowrap">
                <?php echo school_timeago($notice->publish_date); ?>
              </span>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-center py-6">
          <span class="material-symbols-outlined text-[36px] text-on-surface-variant block mb-1">notifications_none</span>
          <p class="text-body-md font-body-md text-on-surface-variant">No recent notices.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>
