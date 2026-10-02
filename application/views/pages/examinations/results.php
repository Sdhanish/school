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
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Examination Results</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">View student scores, percentage, GPA, pass/fail status, and class rank positions.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/report_cards'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">badge</span>Report Cards
        </a>
        <a href="<?php echo site_url('examinations/publishing'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">publish</span>Publishing Queue
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/results'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam</label>
          <select name="exam_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Exams</option>
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
              <option value="<?php echo $s->division_id; ?>" <?php echo (($filters['division_id'] ?? '') == $s->division_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Result Status</label>
          <select name="pass_status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All (Pass & Fail)</option>
            <option value="Pass" <?php echo ($filters['pass_status'] === 'Pass') ? 'selected' : ''; ?>>Passed Only</option>
            <option value="Fail" <?php echo ($filters['pass_status'] === 'Fail') ? 'selected' : ''; ?>>Failed Only</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search Student</label>
          <div class="flex items-center gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Name or admission..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-3.5 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shrink-0">Filter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Results Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Calculated Results (<?php echo count($results); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Rank</th>
              <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Exam</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Marks</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface uppercase">% Percentage</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Grade</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface uppercase">Result</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($results)): ?>
              <tr><td colspan="10" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No student results found. Make sure marks are verified and result calculation is executed.</td></tr>
            <?php else: ?>
              <?php foreach ($results as $r): ?>
                <?php
                  $isPass = ($r->pass_status === 'Pass');
                  $passBadge = $isPass ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container';
                  $rankVal = $r->class_rank ?: '—';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-3 py-3 text-center">
                    <?php if ($r->class_rank == 1): ?>
                      <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-300 text-amber-950 font-bold text-[13px] shadow-sm">1</span>
                    <?php elseif ($r->class_rank == 2): ?>
                      <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 text-slate-900 font-bold text-[13px]">2</span>
                    <?php elseif ($r->class_rank == 3): ?>
                      <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-amber-50 font-bold text-[13px]">3</span>
                    <?php else: ?>
                      <span class="font-mono text-body-md font-medium text-on-surface-variant"><?php echo $rankVal; ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-3 py-3 text-center font-mono font-bold text-primary whitespace-nowrap">
                    <?php echo html_escape($r->roll_number ?: '—'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <a href="<?php echo site_url('examinations/result_detail/' . $r->result_id); ?>" class="font-bold hover:text-primary hover:underline">
                      <?php echo html_escape($r->first_name . ' ' . $r->last_name); ?>
                    </a>
                    <span class="text-[12px] text-on-surface-variant block font-mono font-normal"><?php echo html_escape($r->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($r->class_name . ' ' . ($r->division_name ?: '')); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($r->exam_name); ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono text-body-md">
                    <span class="font-bold text-on-surface"><?php echo number_format($r->total_marks, 1); ?></span>
                    <span class="text-on-surface-variant">/ <?php echo (int)$r->max_marks; ?></span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-body-md <?php echo ($r->percentage >= 75) ? 'text-secondary' : (($r->percentage >= 40) ? 'text-on-surface' : 'text-error'); ?>">
                    <?php echo number_format($r->percentage, 2); ?>%
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($r->overall_grade); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo $passBadge; ?>">
                      <?php echo html_escape($r->pass_status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Result Detail -->
                      <a href="<?php echo site_url('examinations/result_detail/' . $r->result_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-primary transition-colors cursor-pointer" title="View Detail">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </a>

                      <!-- Report Card -->
                      <a href="<?php echo site_url('examinations/report_card/' . $r->result_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-secondary transition-colors cursor-pointer" title="Report Card">
                        <span class="material-symbols-outlined text-[18px]">badge</span>
                      </a>

                      <!-- Progress Report -->
                      <a href="<?php echo site_url('examinations/progress_report/' . $r->student_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Progress Report">
                        <span class="material-symbols-outlined text-[18px]">trending_up</span>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
