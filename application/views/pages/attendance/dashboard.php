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

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Attendance Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Live overview of student attendance metrics, class breakdowns, and recent activity.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('attendance/class_attendance?date=' . $date); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">co_present</span>Class Attendance
        </a>
        <a href="<?php echo site_url('attendance/reports'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">bar_chart</span>View Reports
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('attendance'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y->academic_year_id; ?>" <?php echo ($year_id == $y->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($y->year_name); ?><?php echo ($y->is_active) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Date</label>
          <input type="date" name="date" value="<?php echo html_escape($date); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($class_id == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach (($divisions ?? $sections) as $sec): ?>
              <?php
                $div_id = $sec->division_id ?? ($sec->section_id ?? null);
                $div_name = $sec->division_name ?? ($sec->section_name ?? '');
                $selected_div = $division_id ?? $section_id;
              ?>
              <option value="<?php echo $div_id; ?>" <?php echo ($selected_div == $div_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($sec->class_name . ' ' . $div_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
      <!-- Total Students -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-on-surface-variant uppercase tracking-wider">Total Students</span>
          <span class="material-symbols-outlined text-[20px] text-primary">groups</span>
        </div>
        <div class="text-2xl font-bold text-on-surface"><?php echo $stats->total_students; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1">Enrolled Active</div>
      </div>

      <!-- Present Today -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-secondary uppercase tracking-wider">Present</span>
          <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        </div>
        <div class="text-2xl font-bold text-secondary"><?php echo $stats->present; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1"><?php echo $stats->present_pct; ?>% of marked</div>
      </div>

      <!-- Absent Today -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-error uppercase tracking-wider">Absent</span>
          <span class="material-symbols-outlined text-[20px] text-error">cancel</span>
        </div>
        <div class="text-2xl font-bold text-error"><?php echo $stats->absent; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1"><?php echo $stats->absent_pct; ?>% of marked</div>
      </div>

      <!-- Late Today -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-amber-600 uppercase tracking-wider">Late</span>
          <span class="material-symbols-outlined text-[20px] text-amber-600">schedule</span>
        </div>
        <div class="text-2xl font-bold text-amber-600"><?php echo $stats->late; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1"><?php echo $stats->late_pct; ?>% of marked</div>
      </div>

      <!-- Half Day Today -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-amber-600 uppercase tracking-wider">Half Day</span>
          <span class="material-symbols-outlined text-[20px] text-amber-600">timelapse</span>
        </div>
        <div class="text-2xl font-bold text-amber-600"><?php echo isset($stats->half_day) ? $stats->half_day : 0; ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1"><?php echo isset($stats->half_day_pct) ? $stats->half_day_pct : 0; ?>% of marked</div>
      </div>

      <!-- Attendance Percentage -->
      <div class="p-4 rounded-xl bg-secondary-container/20 border border-secondary/30 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-secondary uppercase tracking-wider">Rate</span>
          <span class="material-symbols-outlined text-[20px] text-secondary">trending_up</span>
        </div>
        <div class="text-2xl font-bold text-secondary"><?php echo $stats->percentage; ?>%</div>
        <div class="text-[11px] text-on-secondary-container mt-1">Overall Present %</div>
      </div>
    </div>

    <!-- Middle Row: Today's Summary & Progress -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <div class="lg:col-span-1 p-5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">analytics</span>Today's Summary
        </h3>
        <p class="text-body-md text-on-surface-variant mb-4">Date: <span class="font-semibold text-on-surface"><?php echo date('d M Y', strtotime($date)); ?></span></p>

        <div class="space-y-3">
          <div>
            <div class="flex justify-between text-body-md mb-1">
              <span class="text-on-surface-variant">Attendance Marked</span>
              <span class="font-semibold text-on-surface"><?php echo $stats->total_marked; ?> / <?php echo $stats->total_students; ?></span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
              <div class="h-full bg-primary" style="width: <?php echo ($stats->total_students > 0) ? min(100, round(($stats->total_marked / $stats->total_students) * 100)) : 0; ?>%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-body-md mb-1">
              <span class="text-on-surface-variant">Present Percentage</span>
              <span class="font-semibold text-secondary"><?php echo $stats->present_pct; ?>%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
              <div class="h-full bg-secondary" style="width: <?php echo $stats->present_pct; ?>%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-body-md mb-1">
              <span class="text-on-surface-variant">Absent Percentage</span>
              <span class="font-semibold text-error"><?php echo $stats->absent_pct; ?>%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
              <div class="h-full bg-error" style="width: <?php echo $stats->absent_pct; ?>%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-body-md mb-1">
              <span class="text-on-surface-variant">Late Percentage</span>
              <span class="font-semibold text-amber-600"><?php echo $stats->late_pct; ?>%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
              <div class="h-full bg-amber-500" style="width: <?php echo $stats->late_pct; ?>%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-body-md mb-1">
              <span class="text-on-surface-variant">Half Day Percentage</span>
              <span class="font-semibold text-amber-600"><?php echo isset($stats->half_day_pct) ? $stats->half_day_pct : 0; ?>%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
              <div class="h-full bg-amber-400" style="width: <?php echo isset($stats->half_day_pct) ? $stats->half_day_pct : 0; ?>%"></div>
            </div>
          </div>
        </div>

        <?php if ($stats->not_marked > 0): ?>
          <div class="mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 text-amber-800 dark:text-amber-300 text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">info</span>
            <span><strong><?php echo $stats->not_marked; ?></strong> student(s) attendance not yet marked today.</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Recent Attendance Activity -->
      <div class="lg:col-span-2 p-5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">history</span>Recent Attendance Activity
          </h3>
        </div>

        <?php if (empty($recent_activity)): ?>
          <div class="text-center py-8 text-on-surface-variant text-body-md">
            <span class="material-symbols-outlined text-[40px] text-outline mb-2">event_busy</span>
            <div>No recent attendance activity found for this period.</div>
          </div>
        <?php else: ?>
          <div class="divide-y divide-outline-variant/30 max-h-[320px] overflow-y-auto">
            <?php foreach ($recent_activity as $act): ?>
              <?php
                $badgeClass = 'bg-secondary-container text-on-secondary-container';
                if ($act->attendance_status === 'Absent') $badgeClass = 'bg-error-container text-on-error-container';
                elseif (in_array($act->attendance_status, array('Half Day', 'Late / Half Day', 'Half-day'))) $badgeClass = 'bg-amber-100 text-amber-900';
                elseif (in_array($act->attendance_status, array('Late', 'Late Coming'))) $badgeClass = 'bg-indigo-100 text-indigo-900';
              ?>
              <div class="py-2.5 flex items-center justify-between gap-3 text-body-md">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="w-2.5 h-2.5 rounded-full <?php echo ($act->attendance_status === 'Present') ? 'bg-secondary' : (($act->attendance_status === 'Absent') ? 'bg-error' : 'bg-amber-500'); ?> shrink-0"></span>
                  <div class="min-w-0">
                    <div class="font-medium text-on-surface truncate">
                      <?php echo html_escape($act->first_name . ' ' . $act->last_name); ?>
                      <span class="text-[12px] text-on-surface-variant font-normal">(<?php echo html_escape(!empty($act->division_name) ? ($act->class_name . ' ' . $act->division_name) : $act->class_name); ?>)</span>
                    </div>
                    <div class="text-[12px] text-on-surface-variant">
                      <?php echo date('d M Y', strtotime($act->attendance_date)); ?>
                      <?php if (!empty($act->period_name)): ?> · <?php echo html_escape($act->period_name); ?><?php endif; ?>
                      <?php if (!empty($act->marked_by_display) && $act->marked_by_display !== 'Not Available'): ?> · Marked by <?php echo html_escape($act->marked_by_display); ?><?php endif; ?>
                    </div>
                  </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[12px] font-semibold shrink-0 <?php echo $badgeClass; ?>">
                  <?php echo html_escape($act->attendance_status); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Class-Wise Overview Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-5 border-b border-outline-variant/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">table_chart</span>Class-Wise Overview
          </h3>
          <p class="text-body-md text-on-surface-variant mt-0.5">Division-by-division breakdown for <?php echo date('d M Y', strtotime($date)); ?>.</p>
        </div>
        <div class="flex items-center gap-2">
          <a href="<?php echo site_url('attendance/class_attendance?date=' . $date); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-body-md hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[18px]">open_in_new</span>Detailed Class View
          </a>
        </div>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Class & Division</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Total</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-secondary uppercase tracking-wider">Present</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-amber-600 uppercase tracking-wider">Half Day</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-indigo-600 uppercase tracking-wider">Late</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-error uppercase tracking-wider">Absent</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">% Present</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($class_overview)): ?>
              <tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No classes or divisions found.</td></tr>
            <?php else: ?>
              <?php foreach ($class_overview as $row): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-medium text-on-surface whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="w-7 h-7 rounded-lg bg-primary-fixed/30 text-primary flex items-center justify-center font-bold text-[12px]"><?php echo substr($row->class_name, 0, 1); ?></span>
                      <span><?php echo html_escape($row->class_name . ' — ' . ($row->division_name ?? $row->section_name)); ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-on-surface"><?php echo $row->total_students; ?></td>
                  <td class="px-4 py-3 text-right font-semibold text-secondary"><?php echo $row->present_count ?: 0; ?></td>
                  <td class="px-4 py-3 text-right font-semibold text-amber-600"><?php echo $row->half_day_count ?: 0; ?></td>
                  <td class="px-4 py-3 text-right font-semibold text-indigo-600"><?php echo (isset($row->is_higher_sec) && $row->is_higher_sec) ? ($row->late_count ?: 0) : '-'; ?></td>
                  <td class="px-4 py-3 text-right font-semibold text-error"><?php echo $row->absent_count ?: 0; ?></td>
                  <td class="px-4 py-3 text-right font-bold <?php echo ($row->percentage >= 90) ? 'text-secondary' : (($row->percentage >= 75) ? 'text-amber-600' : 'text-error'); ?>">
                    <?php echo $row->percentage; ?>%
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($row->is_marked): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">
                        <span class="material-symbols-outlined text-[14px]">check</span>Marked
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-900">
                        <span class="material-symbols-outlined text-[14px]">pending</span>Not Marked
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('attendance/class_attendance?class_id=' . $row->class_id . '&date=' . $date); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary hover:text-white transition-colors text-[13px] font-medium">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>View Class
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
