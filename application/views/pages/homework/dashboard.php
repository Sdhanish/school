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
        <h2 class="font-headline-md text-headline-md text-on-surface">Homework & Assignments Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Real-time statistics on daily assignments, submission tracking, evaluation workloads, and student completion.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/calendar'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">calendar_month</span>Calendar
        </a>
        <a href="<?php echo site_url('homework/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Assignment
        </a>
      </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Assignments -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Assignments</span>
          <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_assignments; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-primary"><?php echo $stats->active_assignments; ?></span> currently active
        </div>
      </div>

      <!-- Submitted -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Student Submissions</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">inventory_2</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo $stats->submitted; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->reviewed; ?></span> evaluated & graded
        </div>
      </div>

      <!-- Pending Submissions -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Pending Submissions</span>
          <span class="material-symbols-outlined text-amber-500 text-[22px]">pending_actions</span>
        </div>
        <div class="text-headline-md font-bold text-amber-600"><?php echo $stats->pending; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-error"><?php echo $stats->overdue; ?></span> overdue assignments
        </div>
      </div>

      <!-- Overall Completion Rate -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Completion Rate</span>
          <span class="material-symbols-outlined text-primary text-[22px]">donut_large</span>
        </div>
        <div class="text-headline-md font-bold text-primary"><?php echo $stats->completion_pct; ?>%</div>
        <div class="w-full bg-surface-container-high rounded-full h-1.5 overflow-hidden mt-1">
          <div class="bg-primary h-1.5 rounded-full" style="width: <?php echo min(100, $stats->completion_pct); ?>%"></div>
        </div>
      </div>
    </div>

    <!-- 2 Column Division: Recent Assignments + Upcoming Deadlines -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Recent Assignments (2 Cols) -->
      <div class="lg:col-span-2 p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>Recent Assignments
          </h3>
          <a href="<?php echo site_url('homework/assignments'); ?>" class="text-body-md text-primary hover:underline font-semibold text-xs">View All</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($recent_assignments)): ?>
            <div class="py-8 text-center text-on-surface-variant text-body-md">No recent assignments found.</div>
          <?php else: ?>
            <?php foreach ($recent_assignments as $asgn): ?>
              <?php
                $st = $asgn->submission_stats;
                $statusBadge = ($asgn->status === 'Published') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant';
              ?>
              <div class="py-3.5 flex items-start justify-between gap-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <a href="<?php echo site_url('homework/details/' . $asgn->assignment_id); ?>" class="font-bold text-on-surface hover:text-primary transition-colors text-body-md">
                      <?php echo html_escape($asgn->title); ?>
                    </a>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?php echo $statusBadge; ?>">
                      <?php echo html_escape($asgn->status); ?>
                    </span>
                  </div>
                  <div class="text-[12px] text-on-surface-variant flex items-center gap-3">
                    <span><strong><?php echo html_escape($asgn->subject_name); ?></strong> (<?php echo html_escape($asgn->class_name . ' ' . $asgn->section_name); ?>)</span>
                    <span>•</span>
                    <span>Teacher: <?php echo html_escape($asgn->teacher_name); ?></span>
                    <span>•</span>
                    <span>Due: <strong><?php echo date('d M Y', strtotime($asgn->due_date)); ?></strong></span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="text-label-md font-mono font-bold text-primary block"><?php echo $st->submitted; ?> / <?php echo $st->total_students; ?></span>
                  <span class="text-[11px] text-on-surface-variant">Submitted (<?php echo $st->completion_pct; ?>%)</span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Upcoming Deadlines (1 Col) -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500 text-[22px]">alarm</span>Upcoming Deadlines
          </h3>
        </div>

        <div class="space-y-3">
          <?php if (empty($upcoming_deadlines)): ?>
            <div class="py-6 text-center text-on-surface-variant text-body-md">No pending upcoming deadlines.</div>
          <?php else: ?>
            <?php foreach ($upcoming_deadlines as $ud): ?>
              <?php
                $daysLeft = round((strtotime($ud->due_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                $dayTag = ($daysLeft == 0) ? 'Due Today' : (($daysLeft == 1) ? 'Due Tomorrow' : "In {$daysLeft} days");
                $dayColor = ($daysLeft <= 1) ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-surface-container-low border-outline-variant/40';
              ?>
              <div class="p-3.5 rounded-xl border <?php echo $dayColor; ?> space-y-1">
                <div class="flex items-center justify-between">
                  <strong class="text-on-surface text-body-md line-clamp-1"><?php echo html_escape($ud->title); ?></strong>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white shrink-0"><?php echo $dayTag; ?></span>
                </div>
                <div class="text-[12px] text-on-surface-variant flex items-center justify-between">
                  <span><?php echo html_escape($ud->subject_name); ?> (<?php echo html_escape($ud->class_name . ' ' . $ud->section_name); ?>)</span>
                  <span class="font-mono"><?php echo date('d M', strtotime($ud->due_date)); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
