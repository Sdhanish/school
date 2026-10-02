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
        <h2 class="font-headline-md text-headline-md text-on-surface">Examination Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure evaluation pass/fail thresholds, rank position formulas, grading precision, and report card signature labels.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/grades'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">grade</span>Manage Grade Scales
        </a>
      </div>
    </div>

    <?php echo form_open('examinations/settings'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- DIVISION 1: Evaluation & Pass/Fail Rules -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">rule</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Pass / Fail & Evaluation Rules</h3>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Default Max Marks</label>
              <input type="number" step="0.5" name="default_max_marks" value="<?php echo (float)$settings->default_max_marks; ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Default Pass Marks</label>
              <input type="number" step="0.5" name="default_passing_marks" value="<?php echo (float)$settings->default_passing_marks; ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Overall Pass Percentage (%)</label>
              <input type="number" step="0.5" name="overall_pass_percentage" value="<?php echo (float)$settings->overall_pass_percentage; ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Decimal Precision</label>
              <select name="decimal_precision" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="0" <?php echo ($settings->decimal_precision == 0) ? 'selected' : ''; ?>>0 (Round to Integer)</option>
                <option value="1" <?php echo ($settings->decimal_precision == 1) ? 'selected' : ''; ?>>1 Decimal Place</option>
                <option value="2" <?php echo ($settings->decimal_precision == 2) ? 'selected' : ''; ?>>2 Decimal Places (Default)</option>
              </select>
            </div>
          </div>

          <div class="space-y-3 pt-2">
            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Subject Pass Mark Rule</div>
                <div class="text-[12px] text-on-surface-variant">Require student to pass every individual subject to receive an overall Pass.</div>
              </div>
              <input type="checkbox" name="subject_pass_mark_rule" value="1" <?php echo ($settings->subject_pass_mark_rule) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Single Subject Failure Rule</div>
                <div class="text-[12px] text-on-surface-variant">Failing a single subject marks the whole examination as Fail.</div>
              </div>
              <input type="checkbox" name="single_subject_fail_overall" value="1" <?php echo ($settings->single_subject_fail_overall) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-error focus:ring-error"/>
            </label>
          </div>
        </div>

        <!-- DIVISION 2: Ranking & Report Card Configuration -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">leaderboard</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Rank Calculation & Display</h3>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Rank Criteria</label>
            <select name="rank_criteria" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="Percentage" <?php echo ($settings->rank_criteria === 'Percentage') ? 'selected' : ''; ?>>Percentage (Standard)</option>
              <option value="Total Marks" <?php echo ($settings->rank_criteria === 'Total Marks') ? 'selected' : ''; ?>>Total Marks</option>
              <option value="GPA" <?php echo ($settings->rank_criteria === 'GPA') ? 'selected' : ''; ?>>Grade Point Average (GPA)</option>
            </select>
          </div>

          <div class="space-y-3">
            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Include Failed Students in Rank</div>
                <div class="text-[12px] text-on-surface-variant">If disabled, failed students receive an unranked status (—).</div>
              </div>
              <input type="checkbox" name="include_failed_in_rank" value="1" <?php echo ($settings->include_failed_in_rank) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-primary focus:ring-primary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Show Merit Rank on Report Card</div>
                <div class="text-[12px] text-on-surface-variant">Print the student's class merit position on official report cards.</div>
              </div>
              <input type="checkbox" name="show_rank_on_report_card" value="1" <?php echo ($settings->show_rank_on_report_card) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Show Attendance on Report Card</div>
                <div class="text-[12px] text-on-surface-variant">Include academic working days and attendance % in the report card footer.</div>
              </div>
              <input type="checkbox" name="show_attendance_on_report_card" value="1" <?php echo ($settings->show_attendance_on_report_card) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>
          </div>
        </div>
      </div>

      <!-- DIVISION 3: Report Card Template & Signature Settings -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5 mb-6">
        <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
          <span class="material-symbols-outlined text-primary text-[24px]">badge</span>
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Report Card Template & Signatures</h3>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Report Card Banner Text</label>
          <input type="text" name="report_card_header" value="<?php echo html_escape($settings->report_card_header); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Principal Signature Title</label>
            <input type="text" name="principal_signature_title" value="<?php echo html_escape($settings->principal_signature_title); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class Teacher Signature Title</label>
            <input type="text" name="teacher_signature_title" value="<?php echo html_escape($settings->teacher_signature_title); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Examination Settings
        </button>
      </div>
    <?php echo form_close(); ?>
