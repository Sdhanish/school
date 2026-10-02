<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Teacher Timetable Matrix</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review individual faculty weekly schedules, teaching loads, and period commitments.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Faculty Schedule
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 print:hidden">
      <form method="get" action="<?php echo site_url('timetable/teachers'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Department / Group</label>
          <select name="academic_group_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Departments / Groups</option>
            <?php if (!empty($groups)): ?>
              <?php foreach ($groups as $g): ?>
                <option value="<?php echo $g->academic_group_id; ?>" <?php echo (($selected_group ?? '') == $g->academic_group_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($g->group_name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Faculty / Teacher</label>
          <select name="teacher_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($teachers as $t): ?>
              <option value="<?php echo $t->staff_id; ?>" <?php echo ($selected_teacher == $t->staff_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($t->full_name . ' (' . $t->employee_code . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Teacher Summary Card -->
    <?php if ($current_teacher): ?>
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-primary text-on-primary font-bold text-lg flex items-center justify-center shrink-0">
            <?php echo strtoupper(substr($current_teacher->full_name, 0, 1)); ?>
          </div>
          <div>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface"><?php echo html_escape($current_teacher->full_name); ?></h3>
            <span class="text-body-md text-on-surface-variant font-mono">Code: <?php echo html_escape($current_teacher->employee_code); ?> | Phone: <?php echo html_escape($current_teacher->phone ?: '—'); ?></span>
          </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 text-center min-w-[120px]">
            <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Weekly Workload</span>
            <strong class="text-title-md font-mono text-primary"><?php echo $weekly_periods_count; ?> Periods</strong>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Print Header (Visible only on print) -->
    <div class="hidden print:block text-center pb-4 mb-4 border-b border-outline-variant">
      <h1 class="text-2xl font-bold text-on-surface uppercase">Login2</h1>
      <h2 class="text-lg font-semibold text-primary mt-1">
        Teacher Timetable — <?php echo html_escape($current_teacher ? $current_teacher->full_name : ''); ?>
      </h2>
    </div>

    <!-- TEACHER TIMETABLE GRID MATRIX -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b-2 border-outline-variant bg-surface-container-low">
              <th class="text-left px-4 py-3 text-label-md font-bold text-on-surface-variant uppercase w-28 sticky left-0 bg-surface-container-low z-10">Day / Period</th>
              <?php foreach ($periods as $p): ?>
                <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase border-l border-outline-variant/40 min-w-[150px]">
                  <span class="block text-primary font-bold"><?php echo html_escape($p->period_name); ?></span>
                  <span class="text-[11px] font-mono text-on-surface-variant font-normal block mt-0.5">
                    <?php echo date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)); ?>
                  </span>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($working_days as $day): ?>
              <tr class="hover:bg-surface-container-low/50 transition-colors">
                <!-- Day Column -->
                <td class="px-4 py-4 font-bold text-on-surface whitespace-nowrap bg-surface-container-low/30 sticky left-0 z-10 border-r border-outline-variant/40">
                  <?php echo html_escape($day); ?>
                </td>

                <!-- Period Columns -->
                <?php foreach ($periods as $p): ?>
                  <?php $entry = $matrix[$day][$p->period_id] ?? ($matrix[$day]['num_' . $p->period_number] ?? null); ?>
                  <td class="p-2 border-l border-outline-variant/40 align-top text-center">
                    <?php if ($entry): ?>
                      <div class="p-2.5 rounded-xl bg-secondary-container/20 border border-secondary/30 space-y-1">
                        <div class="font-bold text-secondary text-[13px] line-clamp-1" title="<?php echo html_escape($entry->class_name . ' ' . ($entry->division_name ?: $entry->section_name)); ?>">
                          <?php echo html_escape($entry->class_name . ' ' . ($entry->division_name ?: $entry->section_name)); ?>
                        </div>
                        <div class="text-[12px] font-medium text-on-surface line-clamp-1" title="<?php echo html_escape($entry->subject_name); ?>">
                          <?php echo html_escape($entry->subject_name); ?>
                        </div>
                        <?php if ($entry->room_no): ?>
                          <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono bg-surface-container-high text-on-surface-variant">
                            Room: <?php echo html_escape($entry->room_no); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <div class="h-16 flex items-center justify-center text-on-surface-variant/40 text-[12px] italic">— Free —</div>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
