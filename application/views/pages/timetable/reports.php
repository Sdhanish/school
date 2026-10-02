<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Institutional Timetable Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Master timetable schedules, class allocations, faculty workload distributions, and print exports.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
        <a href="<?php echo site_url('timetable/reports?' . http_build_query(array_merge($_GET, array('export' => 'csv')))); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 print:hidden">
      <form method="get" action="<?php echo site_url('timetable/reports'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($academic_years as $ay): ?>
              <option value="<?php echo $ay->academic_year_id; ?>" <?php echo ($selected_year == $ay->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($filters['class_id'] ?? '') == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Faculty</label>
          <select name="teacher_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Faculty</option>
            <?php foreach ($teachers as $t): ?>
              <option value="<?php echo $t->staff_id; ?>" <?php echo (($filters['teacher_id'] ?? '') == $t->staff_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($t->full_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Print Header (Visible only on print) -->
    <div class="hidden print:block text-center pb-4 mb-4 border-b border-outline-variant">
      <h1 class="text-2xl font-bold text-on-surface uppercase">Login2</h1>
      <h2 class="text-lg font-semibold text-primary mt-1">Master Timetable Schedule Report</h2>
    </div>

    <!-- Master Report Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Scheduled Slots List (<?php echo count($report_data); ?> entries)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Day</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Period</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Faculty</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Room</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($report_data)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No timetable records found matching the filter criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($report_data as $r): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap"><?php echo html_escape($r->day); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="font-medium text-on-surface"><?php echo html_escape($r->period_name); ?></span>
                    <span class="text-[11px] font-mono text-on-surface-variant block"><?php echo date('h:i A', strtotime($r->start_time)) . ' - ' . date('h:i A', strtotime($r->end_time)); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface"><?php echo html_escape($r->class_name . ' ' . ($r->division_name ?: $r->section_name)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-primary"><?php echo html_escape($r->subject_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface"><?php echo html_escape($r->teacher_name); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[12px] text-on-surface-variant"><?php echo html_escape($r->room_no ?: '—'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
