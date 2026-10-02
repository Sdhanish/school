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
        <h2 class="font-headline-md text-headline-md text-on-surface">Timetable Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Institutional timetable scheduling overview, teacher utilization, period matrixes, and conflict alerts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('timetable/builder'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">edit_calendar</span>Timetable Builder
        </a>
        <a href="<?php echo site_url('timetable/period_setup'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">schedule</span>Period Setup
        </a>
      </div>
    </div>

    <!-- 1. METRICS KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Card 1: Total Slots -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Scheduled Slots</span>
          <span class="p-2 rounded-xl bg-primary text-white"><span class="material-symbols-outlined text-[20px]">calendar_month</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-display-sm font-bold text-on-surface font-mono"><?php echo number_format($stats->total_slots); ?></span>
          <span class="text-body-md text-on-surface-variant">weekly slots</span>
        </div>
        <p class="text-[12px] text-on-surface-variant mt-1">Across <?php echo $stats->scheduled_classes; ?> class divisions</p>
      </div>

      <!-- Card 2: Faculty Utilization -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Teacher Utilization</span>
          <span class="p-2 rounded-xl bg-secondary-container text-on-secondary-container"><span class="material-symbols-outlined text-[20px]">person_check</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-display-sm font-bold text-secondary font-mono"><?php echo $stats->utilization_rate; ?>%</span>
          <span class="text-body-md text-on-surface-variant"><?php echo $stats->active_teachers; ?> / <?php echo $stats->total_faculty; ?> active</span>
        </div>
        <div class="w-full bg-surface-container-high rounded-full h-1.5 mt-2 overflow-hidden">
          <div class="bg-secondary h-1.5 rounded-full" style="width: <?php echo min(100, $stats->utilization_rate); ?>%"></div>
        </div>
      </div>

      <!-- Card 3: Publish Status -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Publish Status</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-primary"><span class="material-symbols-outlined text-[20px]">lock</span></span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-display-sm font-bold text-on-surface font-mono"><?php echo $stats->published_classes; ?></span>
          <span class="text-body-md text-on-surface-variant">published</span>
        </div>
        <p class="text-[12px] text-on-surface-variant mt-1"><?php echo $stats->locked_classes; ?> schedules currently locked</p>
      </div>

      <!-- Card 4: Conflicts Detected -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Active Conflicts</span>
          <span class="p-2 rounded-xl <?php echo ($stats->conflicts_count > 0) ? 'bg-error-container text-on-error-container' : 'bg-secondary-container text-on-secondary-container'; ?>">
            <span class="material-symbols-outlined text-[20px]"><?php echo ($stats->conflicts_count > 0) ? 'warning' : 'verified'; ?></span>
          </span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-display-sm font-bold <?php echo ($stats->conflicts_count > 0) ? 'text-error' : 'text-secondary'; ?> font-mono">
            <?php echo $stats->conflicts_count; ?>
          </span>
          <span class="text-body-md text-on-surface-variant">clashes</span>
        </div>
        <p class="text-[12px] text-on-surface-variant mt-1">
          <?php echo ($stats->conflicts_count > 0) ? '<a href="' . site_url('timetable/conflicts') . '" class="text-error font-semibold hover:underline">Review & resolve clashes</a>' : 'No teacher collisions found'; ?>
        </p>
      </div>
    </div>

    <!-- 2. QUICK ACTIONS & CONFLICTS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Quick Navigation Grid -->
      <div class="lg:col-span-2 p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">apps</span>Timetable Navigation
          </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <a href="<?php echo site_url('timetable/classes'); ?>" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container-high transition-colors flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-primary text-white shrink-0"><span class="material-symbols-outlined text-[22px]">calendar_view_week</span></span>
            <div>
              <strong class="text-body-md font-semibold text-on-surface block">Class Timetable</strong>
              <span class="text-[12px] text-on-surface-variant">View & print weekly class matrices</span>
            </div>
          </a>

          <a href="<?php echo site_url('timetable/teachers'); ?>" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container-high transition-colors flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-secondary-container text-on-secondary-container shrink-0"><span class="material-symbols-outlined text-[22px]">badge</span></span>
            <div>
              <strong class="text-body-md font-semibold text-on-surface block">Teacher Timetable</strong>
              <span class="text-[12px] text-on-surface-variant">Faculty weekly workloads & free periods</span>
            </div>
          </a>

          <a href="<?php echo site_url('timetable/allocations'); ?>" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container-high transition-colors flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-amber-100 text-amber-900 shrink-0"><span class="material-symbols-outlined text-[22px]">tune</span></span>
            <div>
              <strong class="text-body-md font-semibold text-on-surface block">Subject Allocation</strong>
              <span class="text-[12px] text-on-surface-variant">Define required weekly periods quota</span>
            </div>
          </a>

          <a href="<?php echo site_url('timetable/free_periods'); ?>" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container-high transition-colors flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-emerald-100 text-emerald-900 shrink-0"><span class="material-symbols-outlined text-[22px]">swap_horiz</span></span>
            <div>
              <strong class="text-body-md font-semibold text-on-surface block">Free Periods & Substitution</strong>
              <span class="text-[12px] text-on-surface-variant">Assign proxy teacher coverage</span>
            </div>
          </a>
        </div>
      </div>

      <!-- Configured Periods Card (Reused from Attendance) -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">schedule</span>School Periods
          </h3>
          <a href="<?php echo site_url('timetable/period_setup'); ?>" class="text-[12px] text-primary hover:underline font-semibold">Manage</a>
        </div>

        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
          <?php foreach ($periods as $p): ?>
            <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 text-body-md">
              <div>
                <strong class="text-on-surface text-[13px] block"><?php echo html_escape($p->period_name); ?></strong>
                <span class="text-[11px] font-mono text-on-surface-variant"><?php echo date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)); ?></span>
              </div>
              <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-surface-container-high text-primary">#<?php echo $p->period_order; ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- 3. RECENTLY SCHEDULED SLOTS TABLE -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Active Timetable Slots (<?php echo count($recent_entries); ?>)
        </h3>
        <a href="<?php echo site_url('timetable/reports'); ?>" class="text-[12px] text-primary hover:underline font-semibold">View Master Timetable</a>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Day</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Period</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Teacher</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Room</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($recent_entries)): ?>
              <tr><td colspan="6" class="px-4 py-6 text-center text-on-surface-variant">No timetable entries scheduled yet.</td></tr>
            <?php else: ?>
              <?php foreach (array_slice($recent_entries, 0, 10) as $e): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap"><?php echo html_escape($e->day); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="font-medium text-on-surface"><?php echo html_escape($e->period_name); ?></span>
                    <span class="text-[11px] font-mono text-on-surface-variant block"><?php echo date('h:i A', strtotime($e->start_time)) . ' - ' . date('h:i A', strtotime($e->end_time)); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface"><?php echo html_escape($e->class_name . ' ' . ($e->division_name ?: $e->section_name)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-primary"><?php echo html_escape($e->subject_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface"><?php echo html_escape($e->teacher_name); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[12px] text-on-surface-variant"><?php echo html_escape($e->room_no ?: '—'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
