<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff / Teacher Management Overview</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Faculty & staff personnel administration, designation assignments, and teaching workload.</p>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap shrink-0">
        <a href="<?php echo site_url('staff/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">person_add</span>Add Staff Member
        </a>
        <a href="<?php echo site_url('staff/directory'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">badge</span>Staff Directory
        </a>
      </div>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <!-- Total Staff -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Staff</span>
          <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->total_staff ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo (int)($stats->active_staff ?? 0); ?> Active</span> • <?php echo (int)($stats->inactive_staff ?? 0); ?> Inactive
        </div>
      </div>

      <!-- Total Teachers -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Teachers</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">school</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo (int)($stats->total_teachers ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Teaching Faculty</span>
        </div>
      </div>

      <!-- Non-Teaching Staff -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Non-Teaching</span>
          <span class="material-symbols-outlined text-blue-600 text-[22px]">engineering</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->non_teaching_staff ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Admin & Support Staff</span>
        </div>
      </div>

      <!-- Designations -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Designations</span>
          <span class="material-symbols-outlined text-purple-600 text-[22px]">badge</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->total_designations ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Official roles</span>
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <a href="<?php echo site_url('staff/teachers'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">person_pin</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-primary">Teacher Profiles</div>
          <div class="text-[11px] text-on-surface-variant truncate">Subject teachers & classes</div>
        </div>
      </a>
      <a href="<?php echo site_url('staff/designations'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">badge</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-secondary">Designations</div>
          <div class="text-[11px] text-on-surface-variant truncate">Faculty designations</div>
        </div>
      </a>
      <a href="<?php echo site_url('staff/workload'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">work_history</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-primary">Workload</div>
          <div class="text-[11px] text-on-surface-variant truncate">Weekly teaching workload</div>
        </div>
      </a>
      <a href="<?php echo site_url('staff/attendance'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">fact_check</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-secondary">Staff Attendance</div>
          <div class="text-[11px] text-on-surface-variant truncate">Daily staff check-ins</div>
        </div>
      </a>
    </div>

    <!-- Main Two Column Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Left 2 Cols: Designation Breakdown -->
      <div class="lg:col-span-2 elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">badge</span>Designation Staff Distribution
          </h3>
          <a href="<?php echo site_url('staff/designations'); ?>" class="text-xs font-semibold text-primary hover:underline">Manage Designations</a>
        </div>

        <?php if (!empty($stats->designations)): ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach ($stats->designations as $dg): ?>
              <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div>
                  <h4 class="font-bold text-xs text-on-surface"><?php echo html_escape($dg->designation_name); ?></h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5"><?php echo html_escape($dg->category); ?> · <?php echo (int)$dg->staff_count; ?> staff assigned</p>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-surface-container-high text-on-surface">
                    <?php echo (int)$dg->staff_count; ?> Members
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No designation distribution records available.</div>
        <?php endif; ?>
      </div>

      <!-- Right 1 Col: Recent Staff Additions -->
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">badge</span>Recent Staff Additions
          </h3>
        </div>

        <?php if (!empty($stats->recent_staff)): ?>
          <div class="space-y-3">
            <?php foreach ($stats->recent_staff as $st): ?>
              <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div class="min-w-0 pr-2">
                  <div class="font-bold text-xs text-on-surface truncate"><?php echo html_escape($st->full_name); ?></div>
                  <div class="text-[11px] text-on-surface-variant flex items-center gap-1">
                    <span><?php echo html_escape($st->designation_name ?? $st->staff_type); ?></span> • <span class="font-mono text-primary"><?php echo html_escape($st->employee_code); ?></span>
                  </div>
                </div>
                <a href="<?php echo site_url('staff/directory'); ?>" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high">
                  <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No recent staff additions.</div>
        <?php endif; ?>
      </div>
    </div>
