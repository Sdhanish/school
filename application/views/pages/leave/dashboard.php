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
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Management Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Monitor student and faculty absences, review pending leave applications, and track leave balances.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('leave/approval'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">verified</span>Review Approvals
        </a>
        <a href="<?php echo site_url('leave/request'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Apply for Leave
        </a>
      </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Requests -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Requests</span>
          <span class="material-symbols-outlined text-primary text-[22px]">event_note</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_requests; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->approved; ?></span> Approved • <span class="font-semibold text-error"><?php echo $stats->rejected; ?></span> Rejected
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Pending Approvals</span>
          <span class="material-symbols-outlined text-amber-500 text-[22px]">pending_actions</span>
        </div>
        <div class="text-headline-md font-bold text-amber-600"><?php echo $stats->pending; ?></div>
        <div class="text-xs text-on-surface-variant">
          Awaiting class teacher / principal review
        </div>
      </div>

      <!-- Students On Leave Today -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Students on Leave Today</span>
          <span class="material-symbols-outlined text-primary text-[22px]">school</span>
        </div>
        <div class="text-headline-md font-bold text-primary"><?php echo $stats->students_on_leave_today; ?></div>
        <div class="text-xs text-on-surface-variant">
          Excused from daily attendance
        </div>
      </div>

      <!-- Staff On Leave Today -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Staff on Leave Today</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">badge</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo $stats->staff_on_leave_today; ?></div>
        <div class="text-xs text-on-surface-variant">
          Substitutions scheduled in timetable
        </div>
      </div>
    </div>

    <!-- 2 Column Division: Pending Requests Actionable Desk + Student Leaves -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <!-- Pending Approvals Desk -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500 text-[22px]">pending</span>Actionable Approvals Desk
          </h3>
          <a href="<?php echo site_url('leave/approval'); ?>" class="text-body-md text-primary hover:underline font-semibold text-xs">View All (<?php echo $stats->pending; ?>)</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($pending_approvals)): ?>
            <div class="py-6 text-center text-on-surface-variant text-body-md">No pending leave requests requiring review.</div>
          <?php else: ?>
            <?php foreach ($pending_approvals as $p): ?>
              <?php
                $name = ($p->applicant_type === 'Student') ? $p->first_name . ' ' . $p->last_name : $p->staff_name;
                $scope = ($p->applicant_type === 'Student') ? ($p->class_name . ' ' . $p->section_name) : ($p->designation_name ?: "Faculty");
              ?>
              <div class="py-3 flex items-start justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <strong class="text-body-md text-on-surface"><?php echo html_escape($name); ?></strong>
                    <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-primary-container text-on-primary-container"><?php echo $p->applicant_type; ?></span>
                    <span class="px-2 py-0.2 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant"><?php echo html_escape($p->type_name); ?></span>
                  </div>
                  <div class="text-[12px] text-on-surface-variant">
                    <span><?php echo html_escape($scope); ?></span> • 
                    <span>Dates: <strong><?php echo date('d M', strtotime($p->from_date)) . ' - ' . date('d M Y', strtotime($p->to_date)); ?></strong> (<?php echo $p->duration_days; ?> days)</span>
                  </div>
                  <p class="text-[12px] text-on-surface-variant line-clamp-1 italic">"<?php echo html_escape($p->reason); ?>"</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                  <a href="<?php echo site_url('leave/approve/' . $p->application_id); ?>" class="p-1 rounded-lg bg-secondary-container text-on-secondary-container hover:bg-secondary/20 text-xs font-semibold px-2.5 py-1">Approve</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Staff Leaves -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">badge</span>Faculty & Staff Leaves
          </h3>
          <a href="<?php echo site_url('leave/staff_leave'); ?>" class="text-body-md text-primary hover:underline font-semibold text-xs">View Staff Leave</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($recent_staff_leaves)): ?>
            <div class="py-6 text-center text-on-surface-variant text-body-md">No recent staff leaves recorded.</div>
          <?php else: ?>
            <?php foreach ($recent_staff_leaves as $sl): ?>
              <?php
                $stBadge = ($sl->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($sl->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
              ?>
              <div class="py-3 flex items-start justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <strong class="text-body-md text-on-surface"><?php echo html_escape($sl->staff_name); ?></strong>
                    <span class="text-xs text-on-surface-variant">(<?php echo html_escape($sl->designation_name); ?>)</span>
                  </div>
                  <div class="text-[12px] text-on-surface-variant">
                    <span><?php echo html_escape($sl->type_name); ?></span> • 
                    <span><?php echo date('d M', strtotime($sl->from_date)) . ' to ' . date('d M Y', strtotime($sl->to_date)); ?></span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>"><?php echo $sl->status; ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
