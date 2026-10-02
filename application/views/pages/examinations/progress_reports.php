<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Progress Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Generate multi-exam comparative performance reports analyzing student improvement across examinations.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/report_cards'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">badge</span>Report Cards
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/progress_reports'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Class *</label>
          <select name="class_id" onchange="if(this.form.querySelector('[name=division_id]')) this.form.querySelector('[name=division_id]').value=''; this.form.submit();" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($filters['class_id'] == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Division</label>
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

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search Student</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Name or admission..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>
      </form>
    </div>

    <!-- Student List for Progress Reports -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Enrolled Students (<?php echo count($students); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Guardian</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($students)): ?>
              <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant text-body-md">Please select a class to load students for progress report generation.</td></tr>
            <?php else: ?>
              <?php foreach ($students as $stu): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 text-center font-mono font-bold text-primary"><?php echo html_escape($stu->roll_number ?: '—'); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <?php echo html_escape($stu->first_name . ' ' . $stu->last_name); ?>
                    <span class="text-[12px] text-on-surface-variant block font-mono font-normal"><?php echo html_escape($stu->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant"><?php echo html_escape($stu->class_name . ' ' . ($stu->division_name ?: '')); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($stu->guardian_name ?: 'Parent'); ?>
                    <span class="text-[12px] block font-mono"><?php echo html_escape($stu->guardian_phone ?: '—'); ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('examinations/progress_report/' . $stu->student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-secondary text-on-secondary text-[12px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
                      <span class="material-symbols-outlined text-[16px]">trending_up</span>Generate Progress Report
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
