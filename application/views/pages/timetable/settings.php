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
        <h2 class="font-headline-md text-headline-md text-on-surface">Timetable Configuration Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure institutional working days, maximum teacher workloads, consecutive class constraints, and period definitions.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('timetable/period_setup'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">schedule</span>Period Setup
        </a>
      </div>
    </div>

    <!-- Settings Form -->
    <?php echo form_open('timetable/settings'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- DIVISION 1: Institutional Working Days -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">calendar_view_week</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Institutional Working Days</h3>
          </div>

          <p class="text-[13px] text-on-surface-variant">Select active days displayed in class schedules and faculty matrices.</p>

          <?php 
            $all_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
          ?>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
            <?php foreach ($all_days as $d): ?>
              <?php $isChecked = in_array($d, $working_days_selected); ?>
              <label class="flex items-center gap-2 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container cursor-pointer transition-colors">
                <input type="checkbox" name="working_days[]" value="<?php echo $d; ?>" <?php echo $isChecked ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md font-medium text-on-surface"><?php echo $d; ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- DIVISION 2: Workload & Period Constraints -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">tune</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Scheduling Constraints</h3>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Max Periods per Day</label>
              <input type="number" min="1" max="15" name="max_periods_per_day" value="<?php echo (int)$settings->max_periods_per_day; ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Max Consecutive Classes</label>
              <input type="number" min="1" max="10" name="max_consecutive_periods" value="<?php echo (int)$settings->max_consecutive_periods; ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="space-y-3 pt-2">
            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Strict Conflict Prevention</div>
                <div class="text-[12px] text-on-surface-variant">Block saving slots if teacher collision or class double-booking is detected.</div>
              </div>
              <input type="checkbox" name="allow_teacher_overlap" value="0" <?php echo (!$settings->allow_teacher_overlap) ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Reused Period Management</div>
                <div class="text-[12px] text-on-surface-variant">All start/end timings sync dynamically from Student Attendance period definitions.</div>
              </div>
              <span class="material-symbols-outlined text-secondary text-[24px]">verified</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Timetable Settings
        </button>
      </div>
    <?php echo form_close(); ?>
