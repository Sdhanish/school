<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Homework & Assignment Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Institutional completion reports, subject distributions, student submission rates, and evaluation summaries.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
        <a href="<?php echo site_url('homework/reports?' . http_build_query(array_merge($_GET, array('export' => 'csv')))); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 print:hidden">
      <form method="get" action="<?php echo site_url('homework/reports'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Subject</label>
          <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?php echo $sub->subject_id; ?>" <?php echo (($filters['subject_id'] ?? '') == $sub->subject_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($sub->subject_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block text-center pb-4 mb-4 border-b border-outline-variant">
      <h1 class="text-2xl font-bold text-on-surface uppercase">Model School</h1>
      <h2 class="text-lg font-semibold text-primary mt-1">Assignment Completion Report</h2>
    </div>

    <!-- Reports Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Assignment Completion Analysis (<?php echo count($assignments); ?> records)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assignment</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Teacher</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Total Students</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-secondary uppercase whitespace-nowrap">Submitted</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Reviewed</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-amber-600 uppercase whitespace-nowrap">Late</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase min-w-[140px]">Completion %</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($assignments)): ?>
              <tr><td colspan="9" class="px-4 py-8 text-center text-on-surface-variant">No assignment report records found.</td></tr>
            <?php else: ?>
              <?php foreach ($assignments as $a): ?>
                <?php
                  $st = $a->submission_stats;
                  $pct = $st->completion_pct;
                  $barColor = ($pct >= 80) ? 'bg-secondary' : (($pct >= 40) ? 'bg-primary' : 'bg-amber-500');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <?php echo html_escape($a->title); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface">
                    <?php echo html_escape($a->class_name . ' ' . $a->section_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($a->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($a->teacher_name); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono">
                    <?php echo $st->total_students; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-secondary">
                    <?php echo $st->submitted; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-primary">
                    <?php echo $st->reviewed; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-amber-600">
                    <?php echo $st->late; ?>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                        <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $pct; ?>%"></div>
                      </div>
                      <span class="text-[11px] font-mono font-semibold text-on-surface-variant shrink-0"><?php echo $pct; ?>%</span>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
