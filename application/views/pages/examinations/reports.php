<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Examination Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Analytical examination performance reports by exam, class, subject, and student rank.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
        <a href="<?php echo site_url('examinations/reports?' . http_build_query(array_merge($_GET, array('export' => 'csv')))); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
      </div>
    </div>

    <!-- Report Tabs -->
    <div class="flex gap-2 border-b border-outline-variant/60 mb-6 overflow-x-auto">
      <?php
        $curTab = $report_type ?: 'exam_performance';
        $report_tabs = [
          'exam_performance'    => 'Exam Performance',
          'student_performance' => 'Student Performance',
          'class_performance'   => 'Class Performance',
          'rank_report'         => 'Rank & Merit Report',
        ];
      ?>
      <?php foreach ($report_tabs as $k => $label): ?>
        <?php $isActive = ($curTab === $k); ?>
        <a href="<?php echo site_url('examinations/reports?' . http_build_query(array_merge($_GET, array('type' => $k)))); ?>" class="px-4 py-2.5 text-body-md font-medium border-b-2 <?php echo $isActive ? 'border-secondary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'; ?> transition-colors whitespace-nowrap">
          <?php echo $label; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/reports'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <input type="hidden" name="type" value="<?php echo html_escape($report_type); ?>"/>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam *</label>
          <select name="exam_id" onchange="this.form.submit()" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Exam --</option>
            <?php foreach ($exams as $e): ?>
              <option value="<?php echo $e->exam_id; ?>" <?php echo ($filters['exam_id'] == $e->exam_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($e->exam_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="if(this.form.querySelector('[name=division_id]')) this.form.querySelector('[name=division_id]').value=''; this.form.submit();" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($filters['class_id'] == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($divisions as $s): ?>
              <?php if (!$filters['class_id'] || $s->class_id == $filters['class_id']): ?>
                <option value="<?php echo $s->division_id; ?>" <?php echo (($filters['division_id'] ?? '') == $s->division_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Reports Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Report Data (<?php echo count($results); ?> records)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Rank</th>
              <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class & Division</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Marks</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-secondary uppercase">Percentage</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Grade</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($results)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant text-body-md">Please select an exam from the filters above to generate reports.</td></tr>
            <?php else: ?>
              <?php foreach ($results as $r): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-3 py-3 text-center font-mono font-bold text-primary"><?php echo $r->class_rank ?: '—'; ?></td>
                  <td class="px-3 py-3 text-center font-mono font-bold text-primary"><?php echo html_escape($r->roll_number ?: '—'); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <a href="<?php echo site_url('examinations/result_detail/' . $r->result_id); ?>" class="hover:text-primary hover:underline">
                      <?php echo html_escape($r->first_name . ' ' . $r->last_name); ?>
                    </a>
                    <span class="text-[12px] text-on-surface-variant block font-mono font-normal"><?php echo html_escape($r->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant"><?php echo html_escape($r->class_name . ' ' . ($r->division_name ?: '')); ?></td>
                  <td class="px-4 py-3 text-right font-mono text-body-md"><?php echo number_format($r->total_marks, 1); ?> / <?php echo (int)$r->max_marks; ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-secondary text-body-md"><?php echo number_format($r->percentage, 2); ?>%</td>
                  <td class="px-4 py-3 text-center font-bold"><?php echo html_escape($r->overall_grade); ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($r->pass_status === 'Pass') ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container'; ?>">
                      <?php echo html_escape($r->pass_status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
