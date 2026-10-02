<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <a href="<?php echo site_url('examinations/results'); ?>" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
          </a>
          <h2 class="font-headline-md text-headline-md text-on-surface">Student Result Statement</h2>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Detailed subject performance and evaluation breakdown for <?php echo html_escape($result->first_name . ' ' . $result->last_name); ?>.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print
        </button>
        <a href="<?php echo site_url('examinations/report_card/' . $result->result_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">badge</span>Official Report Card
        </a>
      </div>
    </div>

    <!-- Student & Exam Information Card -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-body-md">
        <div>
          <span class="text-[12px] text-on-surface-variant block">Student Name</span>
          <strong class="text-on-surface text-title-md"><?php echo html_escape($result->first_name . ' ' . $result->last_name); ?></strong>
          <span class="text-[12px] text-on-surface-variant block font-mono">Admission #: <?php echo html_escape($result->admission_number); ?></span>
        </div>
        <div>
          <span class="text-[12px] text-on-surface-variant block">Class & Division</span>
          <strong class="text-on-surface font-semibold"><?php echo html_escape($result->class_name . ' ' . ($result->division_name ?: '')); ?></strong>
          <span class="text-[12px] text-on-surface-variant block">Roll #: <strong class="text-primary font-mono"><?php echo html_escape($result->roll_number ?: '—'); ?></strong></span>
        </div>
        <div>
          <span class="text-[12px] text-on-surface-variant block">Examination</span>
          <strong class="text-on-surface font-semibold"><?php echo html_escape($result->exam_name); ?></strong>
          <span class="text-[12px] text-on-surface-variant block">Year: <?php echo html_escape($result->year_name); ?></span>
        </div>
        <div>
          <span class="text-[12px] text-on-surface-variant block">Final Result & Rank</span>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="px-2.5 py-0.5 rounded-full text-[12px] font-bold <?php echo ($result->pass_status === 'Pass') ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container'; ?>">
              <?php echo html_escape($result->pass_status); ?>
            </span>
            <span class="text-on-surface-variant text-[13px]">Rank: <strong class="text-primary"><?php echo $result->class_rank ?: '—'; ?></strong></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
        <div class="text-2xl font-bold font-mono text-on-surface"><?php echo number_format($result->total_marks, 1); ?> <span class="text-on-surface-variant text-base font-normal">/ <?php echo (int)$result->max_marks; ?></span></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Total Marks Obtained</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
        <div class="text-2xl font-bold font-mono text-secondary"><?php echo number_format($result->percentage, 2); ?>%</div>
        <div class="text-[12px] text-on-surface-variant mt-1">Overall Percentage</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
        <div class="text-2xl font-bold font-mono text-primary"><?php echo html_escape($result->overall_grade); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Overall Grade</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
        <div class="text-2xl font-bold font-mono text-amber-600"><?php echo number_format($result->gpa, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Cumulative GPA</div>
      </div>
    </div>

    <!-- Subject Marks Breakdown Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Subject-wise Evaluation Breakdown</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Max Marks</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Pass Marks</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Marks Obtained</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Percentage</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase whitespace-nowrap">Grade</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Grade Point</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase whitespace-nowrap">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($result->subject_marks)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No subject mark records logged for this student.</td></tr>
            <?php else: ?>
              <?php foreach ($result->subject_marks as $sm): ?>
                <?php
                  $maxM = (float)$sm->max_marks ?: 100.00;
                  $passM = (float)$sm->passing_marks ?: 35.00;
                  $obtM = ($sm->marks_obtained !== null) ? (float)$sm->marks_obtained : 0.00;
                  $subPct = ($maxM > 0) ? round(($obtM / $maxM) * 100, 1) : 0;
                  $isSubPass = (!$sm->is_absent && $obtM >= $passM);
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <?php echo html_escape($sm->subject_name); ?>
                    <span class="text-[11px] text-on-surface-variant block font-mono font-normal"><?php echo html_escape($sm->subject_code); ?></span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-body-md text-on-surface-variant"><?php echo (int)$maxM; ?></td>
                  <td class="px-4 py-3 text-right font-mono text-body-md text-on-surface-variant"><?php echo (int)$passM; ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-body-md text-on-surface">
                    <?php if ($sm->is_absent): ?>
                      <span class="text-error">ABS</span>
                    <?php elseif ($sm->is_exempted): ?>
                      <span class="text-primary">EXM</span>
                    <?php else: ?>
                      <?php echo number_format($obtM, 1); ?>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-body-md font-medium text-on-surface">
                    <?php echo ($sm->is_absent || $sm->is_exempted) ? '—' : $subPct . '%'; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($sm->grade ?: '—'); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-primary text-body-md">
                    <?php echo number_format($sm->grade_point, 1); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?php echo $isSubPass ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container'; ?>">
                      <?php echo $isSubPass ? 'Pass' : 'Fail'; ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
