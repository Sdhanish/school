<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Attendance Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Generate comprehensive analytical reports by class, division, student, month, or timetable periods.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <!-- Print Button -->
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>

        <!-- CSV Export Button -->
        <a href="<?php echo site_url('attendance/reports?' . http_build_query(array_merge($_GET, array('export' => 'csv')))); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
      </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="flex gap-2 border-b border-outline-variant/60 mb-6 overflow-x-auto">
      <?php
        $curTab = $report_type ?: 'class_summary';
        $report_tabs = array(
          'class_summary' => 'Class Overview',
          'daily'         => 'Daily Report',
          'student'       => 'Student Report',
          'section'       => 'Division Report',
          'monthly'       => 'Monthly Report',
          'period'        => 'Period-wise Report',
        );
      ?>
      <?php foreach ($report_tabs as $k => $label): ?>
        <?php $isActive = ($curTab === $k); ?>
        <a href="<?php echo site_url('attendance/reports?' . http_build_query(array_merge($_GET, array('type' => $k)))); ?>" class="px-4 py-2.5 text-body-md font-medium border-b-2 <?php echo $isActive ? 'border-secondary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'; ?> transition-colors whitespace-nowrap">
          <?php echo $label; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('attendance/reports'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <input type="hidden" name="type" value="<?php echo html_escape($report_type); ?>"/>

        <!-- Class Filter -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($filters['class_id'] == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Division Filter -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($sections as $sec): ?>
              <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo ($filters['section_id'] == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>>
                <?php echo html_escape($sec->class_name . ' ' . ($sec->division_name ?? $sec->section_name)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($report_type === 'daily'): ?>
          <!-- Date for Daily Report -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Date</label>
            <input type="date" name="date" value="<?php echo html_escape($filters['date']); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>
        <?php elseif ($report_type === 'monthly'): ?>
          <!-- Month for Monthly Report -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Month</label>
            <select name="month" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($filters['month'] == $m) ? 'selected' : ''; ?>>
                  <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
        <?php else: ?>
          <!-- Date Range for others -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">From Date</label>
            <input type="date" name="from_date" value="<?php echo html_escape($filters['from_date']); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">To Date</label>
            <div class="flex items-center gap-2">
              <input type="date" name="to_date" value="<?php echo html_escape($filters['to_date']); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shrink-0">Filter</button>
            </div>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Reports Table Container -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Report Results (<?php echo count($reports); ?> records)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <?php if ($report_type === 'student' || $report_type === 'monthly'): ?>
            <!-- Student / Monthly Report Layout -->
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class & Division</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-secondary uppercase">Present</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-amber-600 uppercase">Half Day</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-error uppercase">Absent</th>
                <?php if ($is_higher_sec): ?>
                  <th class="text-right px-4 py-3 text-label-md font-semibold text-indigo-600 uppercase">Late</th>
                  <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Classes</th>
                <?php else: ?>
                  <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Days</th>
                <?php endif; ?>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">% Present</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($reports)): ?>
                <tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No student records found matching the filter criteria.</td></tr>
              <?php else: ?>
                <?php foreach ($reports as $r): ?>
                  <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-3 py-3 text-center font-mono font-bold text-primary whitespace-nowrap"><?php echo html_escape($r->roll_number ?: '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                      <a href="<?php echo site_url('student-attendance/details?student_id=' . $r->student_id . '&academic_year_id=' . $year_id); ?>" class="hover:text-primary hover:underline">
                        <?php echo html_escape($r->first_name . ' ' . $r->last_name); ?>
                      </a>
                      <span class="text-[12px] text-on-surface-variant block font-normal"><?php echo html_escape($r->admission_number); ?></span>
                    </td>
                    <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap"><?php echo html_escape($r->class_name . ' ' . ($r->division_name ?? $r->section_name)); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-secondary"><?php echo $r->present_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-amber-600"><?php echo $r->half_day_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-error"><?php echo $r->absent_count ?: 0; ?></td>
                    <?php if ($is_higher_sec): ?>
                      <td class="px-4 py-3 text-right font-semibold text-indigo-600"><?php echo $r->late_count ?: 0; ?></td>
                    <?php endif; ?>
                    <td class="px-4 py-3 text-right font-semibold text-on-surface"><?php echo $r->total_days ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-bold <?php echo ($r->percentage >= 90) ? 'text-secondary' : (($r->percentage >= 75) ? 'text-amber-600' : 'text-error'); ?>">
                      <?php echo $r->percentage; ?>%
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>

          <?php elseif ($report_type === 'period'): ?>
            <!-- Period-wise Report Layout -->
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-20">Period #</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Period Name</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Timings</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-secondary uppercase">Present</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-amber-600 uppercase">Half Day</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-indigo-600 uppercase">Late</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-error uppercase">Absent</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Total Recorded</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">% Rate</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($reports)): ?>
                <tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No period attendance records logged.</td></tr>
              <?php else: ?>
                <?php foreach ($reports as $r): ?>
                  <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-3 py-3 text-center font-mono font-bold text-primary"><?php echo html_escape($r->period_number); ?></td>
                    <td class="px-4 py-3 font-semibold text-on-surface"><?php echo html_escape($r->period_name); ?></td>
                    <td class="px-4 py-3 text-body-md text-on-surface-variant font-mono text-[13px]"><?php echo date('h:i A', strtotime($r->start_time)) . ' - ' . date('h:i A', strtotime($r->end_time)); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-secondary"><?php echo $r->present_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-amber-600"><?php echo $r->half_day_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-indigo-600"><?php echo $r->late_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-error"><?php echo $r->absent_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-on-surface"><?php echo $r->total_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-bold <?php echo ($r->percentage >= 90) ? 'text-secondary' : 'text-amber-600'; ?>"><?php echo $r->percentage; ?>%</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>

          <?php else: ?>
            <!-- Class / Division Summary Report Layout -->
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Division</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-secondary uppercase">Present</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-amber-600 uppercase">Half Day</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-error uppercase">Absent</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-indigo-600 uppercase">Late</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Total Marked</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Attendance %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($reports)): ?>
                <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No report data found for this selection.</td></tr>
              <?php else: ?>
                <?php foreach ($reports as $r): ?>
                  <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap"><?php echo html_escape($r->class_name); ?></td>
                    <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap"><?php echo html_escape(($r->division_name ?? $r->section_name)); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-secondary"><?php echo $r->present_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-amber-600"><?php echo $r->half_day_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-error"><?php echo $r->absent_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-indigo-600"><?php echo (isset($r->class_name) && is_higher_secondary_class($r->class_name)) ? ($r->late_count ?: 0) : '-'; ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-on-surface"><?php echo $r->total_count ?: 0; ?></td>
                    <td class="px-4 py-3 text-right font-bold <?php echo ($r->percentage >= 90) ? 'text-secondary' : (($r->percentage >= 75) ? 'text-amber-600' : 'text-error'); ?>">
                      <?php echo $r->percentage; ?>%
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          <?php endif; ?>
        </table>
      </div>
    </div>
