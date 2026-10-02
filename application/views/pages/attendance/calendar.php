<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Attendance Calendar</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Interactive monthly calendar matrix displaying student and class attendance patterns.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('attendance/class_attendance'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">co_present</span>Class Attendance
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('attendance/calendar'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Month & Year Controls -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Month & Year</label>
          <div class="flex items-center gap-2">
            <select name="month" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($month == $m) ? 'selected' : ''; ?>>
                  <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
              <?php endfor; ?>
            </select>
            <select name="year" onchange="this.form.submit()" class="w-28 px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                  <?php echo $y; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <!-- Class Filter -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($class_id == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Division Filter -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($sections as $sec): ?>
              <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo ($section_id == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>>
                <?php echo html_escape($sec->class_name . ' ' . ($sec->division_name ?? $sec->section_name)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Student Specific (if loaded) -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Student (Optional)</label>
          <select name="student_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Students (Aggregated)</option>
            <?php foreach ($students as $st): ?>
              <option value="<?php echo $st->student_id; ?>" <?php echo ($student_id == $st->student_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Calendar Month Navigation & Title -->
    <?php
      $prevMonth = ($month == 1) ? 12 : $month - 1;
      $prevYear  = ($month == 1) ? $year - 1 : $year;
      $nextMonth = ($month == 12) ? 1 : $month + 1;
      $nextYear  = ($month == 12) ? $year + 1 : $year;

      $firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
      $daysInMonth = date('t', $firstDayTimestamp);
      $firstDayOfWeek = date('w', $firstDayTimestamp); // 0 (Sun) to 6 (Sat)
      $monthName = date('F Y', $firstDayTimestamp);
    ?>
    <div class="flex items-center justify-between p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-5">
      <a href="<?php echo site_url('attendance/calendar?' . http_build_query(array_merge($_GET, array('month' => $prevMonth, 'year' => $prevYear)))); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high text-body-md font-medium transition-colors">
        <span class="material-symbols-outlined text-[18px]">chevron_left</span>Previous Month
      </a>
      <h3 class="font-headline-md text-title-lg font-bold text-on-surface"><?php echo $monthName; ?></h3>
      <a href="<?php echo site_url('attendance/calendar?' . http_build_query(array_merge($_GET, array('month' => $nextMonth, 'year' => $nextYear)))); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high text-body-md font-medium transition-colors">
        Next Month<span class="material-symbols-outlined text-[18px]">chevron_right</span>
      </a>
    </div>

    <!-- Calendar Grid -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 p-4">
      <!-- Weekday Headers -->
      <div class="grid grid-cols-7 gap-2 text-center font-bold text-label-md text-on-surface-variant uppercase mb-2">
        <div class="p-2 text-error">Sun</div>
        <div class="p-2">Mon</div>
        <div class="p-2">Tue</div>
        <div class="p-2">Wed</div>
        <div class="p-2">Thu</div>
        <div class="p-2">Fri</div>
        <div class="p-2 text-error">Sat</div>
      </div>

      <!-- Calendar Days Grid -->
      <div class="grid grid-cols-7 gap-2">
        <!-- Empty leading cells -->
        <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
          <div class="min-h-[100px] p-2 rounded-xl bg-surface-container-low/30 border border-outline-variant/20 opacity-40"></div>
        <?php endfor; ?>

        <!-- Days of Month -->
        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
          <?php
            $currentDateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayOfWeek = date('w', strtotime($currentDateStr));
            $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
            $isToday = ($currentDateStr === date('Y-m-d'));
            $data = isset($matrix[$currentDateStr]) ? $matrix[$currentDateStr] : NULL;
          ?>
          <a href="<?php echo site_url('attendance/calendar?' . http_build_query(array_merge($_GET, array('date' => $currentDateStr)))); ?>" class="min-h-[100px] p-2.5 rounded-xl border <?php echo $isToday ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-outline-variant/40 bg-surface-container-lowest hover:border-primary/50'; ?> flex flex-col justify-between transition-all cursor-pointer">
            <div class="flex items-center justify-between">
              <span class="font-mono text-body-md font-bold <?php echo $isWeekend ? 'text-error' : 'text-on-surface'; ?>">
                <?php echo $day; ?>
              </span>
              <?php if ($isToday): ?>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-primary text-on-primary uppercase">Today</span>
              <?php endif; ?>
            </div>

            <!-- Attendance Data Indicators -->
            <div class="space-y-1 mt-1.5">
              <?php if ($data && $data['total'] > 0): ?>
                <?php if ($student_id): ?>
                  <!-- Single Student Status -->
                  <?php if ($data['Present'] > 0): ?>
                    <span class="block px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container text-center">Present</span>
                  <?php elseif ($data['Absent'] > 0): ?>
                    <span class="block px-2 py-0.5 rounded text-[11px] font-semibold bg-error-container text-on-error-container text-center">Absent</span>
                  <?php elseif ($data['Late'] > 0): ?>
                    <span class="block px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-900 text-center">Late</span>
                  <?php elseif ($data['Excused'] > 0): ?>
                    <span class="block px-2 py-0.5 rounded text-[11px] font-semibold bg-primary text-white text-center">Excused</span>
                  <?php endif; ?>
                <?php else: ?>
                  <!-- Aggregated Class Indicators -->
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="font-medium text-secondary">P: <?php echo $data['Present']; ?></span>
                    <span class="font-medium text-error">A: <?php echo $data['Absent']; ?></span>
                    <?php if ($data['Late'] > 0): ?>
                      <span class="font-medium text-amber-600">L: <?php echo $data['Late']; ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="w-full h-1.5 rounded-full bg-surface-container-high overflow-hidden flex">
                    <div class="h-full bg-secondary" style="width: <?php echo round(($data['Present'] / $data['total']) * 100); ?>%"></div>
                    <div class="h-full bg-error" style="width: <?php echo round(($data['Absent'] / $data['total']) * 100); ?>%"></div>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-[11px] text-on-surface-variant/50 italic block text-center mt-2">
                  <?php echo $isWeekend ? 'Weekend' : 'No data'; ?>
                </span>
              <?php endif; ?>
            </div>
          </a>
        <?php endfor; ?>
      </div>
    </div>

    <!-- DATE DETAILS MODAL / DRAWER (If date clicked) -->
    <?php if ($selected_date): ?>
      <div id="date-details-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-2xl w-full p-6 elevation-3 space-y-4 max-h-[85vh] flex flex-col">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50 shrink-0">
            <div>
              <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">calendar_today</span>
                Attendance for <?php echo date('d F Y', strtotime($selected_date)); ?>
              </h3>
              <p class="text-body-md text-on-surface-variant"><?php echo count($day_details); ?> student record(s) logged.</p>
            </div>
            <a href="<?php echo site_url('attendance/calendar?' . http_build_query(array_diff_key($_GET, array('date' => '')))); ?>" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant">
              <span class="material-symbols-outlined text-[20px]">close</span>
            </a>
          </div>

          <div class="overflow-y-auto flex-1 divide-y divide-outline-variant/40">
            <?php if (empty($day_details)): ?>
              <div class="text-center py-8 text-on-surface-variant text-body-md">
                No attendance was marked on this date.
              </div>
            <?php else: ?>
              <table class="w-full data-table zebra border-collapse">
                <thead>
                  <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                    <th class="text-left px-3 py-2.5 text-label-md uppercase">Student</th>
                    <th class="text-left px-3 py-2.5 text-label-md uppercase">Class</th>
                    <th class="text-center px-3 py-2.5 text-label-md uppercase">Status</th>
                    <th class="text-left px-3 py-2.5 text-label-md uppercase">Remarks</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                  <?php foreach ($day_details as $d): ?>
                    <?php
                      $badgeClass = 'bg-secondary-container text-on-secondary-container';
                      if ($d->attendance_status === 'Absent') $badgeClass = 'bg-error-container text-on-error-container';
                      elseif ($d->attendance_status === 'Late') $badgeClass = 'bg-amber-100 text-amber-900';
                      elseif (in_array($d->attendance_status, array('Excused', 'Leave'))) $badgeClass = 'bg-primary text-white';
                    ?>
                    <tr>
                      <td class="px-3 py-2.5 font-medium text-on-surface whitespace-nowrap">
                        <?php echo html_escape($d->first_name . ' ' . $d->last_name); ?>
                        <span class="text-[12px] text-on-surface-variant">(<?php echo html_escape($d->admission_number); ?>)</span>
                      </td>
                      <td class="px-3 py-2.5 text-body-md text-on-surface-variant whitespace-nowrap">
                        <?php echo html_escape($d->class_name . ' ' . ($d->division_name ?? $d->section_name)); ?>
                      </td>
                      <td class="px-3 py-2.5 text-center whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                          <?php echo html_escape($d->attendance_status); ?>
                        </span>
                      </td>
                      <td class="px-3 py-2.5 text-body-md text-on-surface-variant">
                        <?php echo html_escape($d->remarks ?: '—'); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-outline-variant/50 shrink-0">
            <a href="<?php echo site_url('attendance/class_attendance?date=' . $selected_date); ?>" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
              <span class="material-symbols-outlined text-[16px]">visibility</span>View Class Attendance
            </a>
            <a href="<?php echo site_url('attendance/calendar?' . http_build_query(array_diff_key($_GET, array('date' => '')))); ?>" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">
              Close
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
