<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Subject-wise Assignments</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review assignments filtered by subject, monitor cross-class task distributions and completion progress.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Assignment
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('homework/subjects'); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Subject</label>
          <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?php echo $sub->subject_id; ?>" <?php echo ($selected_subject == $sub->subject_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Subject Assignments List (<?php echo count($assignments); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assignment</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Due Date</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Teacher</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submitted</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase min-w-[150px]">Fulfillment</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($assignments)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No assignments found for this subject.</td></tr>
            <?php else: ?>
              <?php foreach ($assignments as $a): ?>
                <?php
                  $st = $a->submission_stats;
                  $pct = $st->completion_pct;
                  $barColor = ($pct >= 80) ? 'bg-secondary' : (($pct >= 40) ? 'bg-primary' : 'bg-amber-500');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <a href="<?php echo site_url('homework/details/' . $a->assignment_id); ?>" class="text-primary hover:underline">
                      <?php echo html_escape($a->title); ?>
                    </a>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface">
                    <?php echo html_escape($a->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($a->class_name . ' ' . $a->section_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($a->due_date)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($a->teacher_name); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-secondary">
                    <?php echo $st->submitted; ?> / <?php echo $st->total_students; ?>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                        <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $pct; ?>%"></div>
                      </div>
                      <span class="text-[11px] font-mono font-semibold text-on-surface-variant shrink-0"><?php echo $pct; ?>%</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($a->status === 'Published') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
