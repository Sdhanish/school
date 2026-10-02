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
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Management Overview</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Holistic student lifecycle monitoring, enrollment demographics, and cohort analytics.</p>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap shrink-0">
        <a href="<?php echo site_url('students/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">person_add</span>Register Student
        </a>
        <a href="<?php echo site_url('students/list'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">view_list</span>Student Directory
        </a>
      </div>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <!-- Total Students -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Students</span>
          <span class="material-symbols-outlined text-primary text-[22px]">school</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->total_students ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo (int)($stats->active_students ?? 0); ?> Active</span> • <?php echo (int)($stats->inactive_students ?? 0); ?> Inactive
        </div>
      </div>

      <!-- New Admissions -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Admissions</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">how_to_reg</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo (int)($stats->new_admissions ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Current academic session</span>
        </div>
      </div>

      <!-- Male Students -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Boys</span>
          <span class="material-symbols-outlined text-blue-600 text-[22px]">boy</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->male_students ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span><?php echo ($stats->total_students > 0) ? round(($stats->male_students / $stats->total_students) * 100, 1) : 0; ?>% of total</span>
        </div>
      </div>

      <!-- Female Students -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Girls</span>
          <span class="material-symbols-outlined text-pink-600 text-[22px]">girl</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)($stats->female_students ?? 0); ?></div>
        <div class="text-xs text-on-surface-variant">
          <span><?php echo ($stats->total_students > 0) ? round(($stats->female_students / $stats->total_students) * 100, 1) : 0; ?>% of total</span>
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <a href="<?php echo site_url('students/register'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">person_add</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-primary">Add Student</div>
          <div class="text-[11px] text-on-surface-variant truncate">Register new admission</div>
        </div>
      </a>
      <a href="<?php echo site_url('students/id_cards'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">badge</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-secondary">ID Cards</div>
          <div class="text-[11px] text-on-surface-variant truncate">Generate & print cards</div>
        </div>
      </a>
      <a href="<?php echo site_url('students/promotion'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">upgrade</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-primary">Promotion</div>
          <div class="text-[11px] text-on-surface-variant truncate">Academic progression</div>
        </div>
      </a>
      <a href="<?php echo site_url('students/transfers'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-error-container text-on-error-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">move_up</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-error">Transfers / TC</div>
          <div class="text-[11px] text-on-surface-variant truncate">Issue transfer certificates</div>
        </div>
      </a>
    </div>

    <!-- Main Two Column Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Left 2 Cols: Class-wise Distribution -->
      <div class="lg:col-span-2 elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">groups</span>Class-wise Student Enrollment
          </h3>
          <a href="<?php echo site_url('students/list'); ?>" class="text-xs font-semibold text-primary hover:underline">View All Students</a>
        </div>

        <?php if (!empty($stats->class_counts)): ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach ($stats->class_counts as $c): ?>
              <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div>
                  <h4 class="font-bold text-xs text-on-surface"><?php echo html_escape($c->class_name); ?></h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5"><?php echo (int)$c->male_count; ?> Boys • <?php echo (int)$c->female_count; ?> Girls</p>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-surface-container-high text-on-surface">
                    <?php echo (int)$c->student_count; ?> Students
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No class distribution records available yet.</div>
        <?php endif; ?>
      </div>

      <!-- Right 1 Col: Recent Admissions -->
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">person_check</span>Recent Admissions
          </h3>
        </div>

        <?php if (!empty($stats->recent_admissions)): ?>
          <div class="space-y-3">
            <?php foreach ($stats->recent_admissions as $st): ?>
              <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div class="min-w-0 pr-2">
                  <a href="<?php echo site_url('students/profile/' . (int)$st->student_id); ?>" class="font-bold text-xs text-on-surface hover:text-primary transition-colors truncate block">
                    <?php echo html_escape($st->first_name . ' ' . $st->last_name); ?>
                  </a>
                  <div class="text-[11px] text-on-surface-variant flex items-center gap-1 font-mono">
                    <span>#<?php echo html_escape($st->admission_number); ?></span> • <span><?php echo html_escape($st->class_name . ' ' . ($st->section_name ?? '')); ?></span>
                  </div>
                </div>
                <a href="<?php echo site_url('students/profile/' . (int)$st->student_id); ?>" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors" title="View Profile">
                  <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No recent student registrations.</div>
        <?php endif; ?>
      </div>
    </div>
