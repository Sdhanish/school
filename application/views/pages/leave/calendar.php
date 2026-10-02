<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Calendar</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Visual schedule of approved faculty and student absences across the academic term.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('leave/request'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Apply Leave
        </a>
      </div>
    </div>

    <!-- Calendar View Container -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-6 mb-6">
      <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">calendar_today</span>Absence Schedule (<?php echo date('F Y'); ?>)
        </h3>
        <div class="flex items-center gap-3 text-xs">
          <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-primary"></span>Student Absence</span>
          <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>Staff Absence</span>
        </div>
      </div>

      <!-- Schedule Cards List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($leaves)): ?>
          <div class="col-span-3 py-12 text-center text-on-surface-variant text-body-md">
            No approved leaves scheduled for this month.
          </div>
        <?php else: ?>
          <?php foreach ($leaves as $l): ?>
            <?php
              $name = ($l->applicant_type === 'Student') ? $l->first_name . ' ' . $l->last_name : $l->staff_name;
              $isSt = ($l->applicant_type === 'Student');
            ?>
            <div class="p-4 rounded-xl border border-outline-variant/50 bg-surface-container-low/40 space-y-2 hover:border-primary transition-all">
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.2 rounded text-[10px] font-bold <?php echo $isSt ? 'bg-primary-container text-on-primary-container' : 'bg-secondary-container text-on-secondary-container'; ?>">
                  <?php echo $l->applicant_type; ?>
                </span>
                <span class="text-[11px] font-mono text-on-surface-variant"><?php echo $l->duration_days; ?> day(s)</span>
              </div>
              <h4 class="text-body-md font-bold text-on-surface line-clamp-1"><?php echo html_escape($name); ?></h4>
              <div class="text-[12px] text-on-surface-variant">
                <span><?php echo html_escape($l->type_name); ?></span> • 
                <span class="font-mono"><?php echo date('d M', strtotime($l->from_date)) . ' to ' . date('d M', strtotime($l->to_date)); ?></span>
              </div>
              <p class="text-[11px] text-on-surface-variant/80 line-clamp-1 italic">"<?php echo html_escape($l->reason); ?>"</p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
