<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-6">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="font-headline-md text-headline-md text-on-surface">Reports Dashboard</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Centralized reporting overview and access to all academic, administrative, and financial reports.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface-variant text-label-md">
        <span class="material-symbols-outlined text-[16px]">calendar_today</span>
        <?php echo date('d M Y'); ?>
      </span>
    </div>
  </div>

  <!-- High-Level Reporting Statistics -->
  <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Students -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">school</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">Students</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface"><?php echo number_format($stats['total_students']); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Total Enrolled</div>
      </div>
    </div>

    <!-- Attendance Percentage -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">fact_check</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-secondary">Today</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface"><?php echo number_format($stats['attendance_pct'], 1); ?>%</div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Attendance Rate</div>
      </div>
    </div>

    <!-- Total Staff -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">badge</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">Staff</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface"><?php echo number_format($stats['total_staff']); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Active Employees</div>
      </div>
    </div>

    <!-- Fee Collection -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">payments</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-secondary">Collected</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface">₹ <?php echo number_format($stats['fee_collected'], 2); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Total Fee Collections</div>
      </div>
    </div>

    <!-- Pending Fees -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-error-container text-on-error-container flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-error">Pending</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface">₹ <?php echo number_format($stats['fee_pending'], 2); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Outstanding Dues</div>
      </div>
    </div>

    <!-- Total Exams -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-tertiary-container/10 text-on-tertiary-container flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">Exams</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface"><?php echo number_format($stats['total_exams']); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Configured Exams</div>
      </div>
    </div>

    <!-- Transport Users -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">directions_bus</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">Transport</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface"><?php echo number_format($stats['transport_users']); ?></div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">Students on Route</div>
      </div>
    </div>

    <!-- Status -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">verified</span>
        </div>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-secondary">System</span>
      </div>
      <div class="mt-3">
        <div class="font-headline-lg text-headline-lg text-on-surface">Active</div>
        <div class="text-body-md font-body-md text-on-surface-variant mt-0.5">All Modules Synchronized</div>
      </div>
    </div>
  </div>

  <!-- Reports Navigation Grid -->
  <div>
    <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Report Categories</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      
      <!-- Student Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">school</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Student Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Class-wise, section-wise, admission status, profiles, and student demographics.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
          <a href="<?php echo site_url('students'); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            <span class="material-symbols-outlined text-[16px]">visibility</span> Open Reports
          </a>
        </div>
      </div>

      <!-- Attendance Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">fact_check</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Attendance Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Daily attendance, period-wise summaries, class averages, absent lists, and tracking.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
          <a href="<?php echo site_url('attendance/reports'); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            <span class="material-symbols-outlined text-[16px]">visibility</span> Open Reports
          </a>
        </div>
      </div>

      <!-- Finance Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">payments</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Finance Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Fee collection logs, outstanding dues, payment receipts, discounts, and breakdowns.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center gap-2">
          <a href="<?php echo site_url('finance/reports'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            Fee Reports
          </a>
          <a href="<?php echo site_url('finance/reports'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            Collection Reports
          </a>
        </div>
      </div>

      <!-- Examination & Results -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-tertiary-container/10 text-on-tertiary-container flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Examination & Results</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Exam marks analysis, progress cards, rank rosters, and student result publishing.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center gap-2">
          <a href="<?php echo site_url('examinations/reports'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            Exam Reports
          </a>
          <a href="<?php echo site_url('examinations/results'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            Result Reports
          </a>
        </div>
      </div>

      <!-- Staff Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">badge</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Staff Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Designation-wise listings, teacher workloads, and attendance.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
          <a href="<?php echo site_url('staff'); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            <span class="material-symbols-outlined text-[16px]">visibility</span> Open Reports
          </a>
        </div>
      </div>

      <!-- Academic Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">menu_book</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Academic Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Class capacity, section distributions, subject allocations, and academic calendars.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
          <a href="<?php echo site_url('academics/years'); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            <span class="material-symbols-outlined text-[16px]">visibility</span> Open Reports
          </a>
        </div>
      </div>

      <!-- Transport Reports -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-[20px]">directions_bus</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Transport Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Vehicle assignments, fleet maintenance logs, driver allocations, and routes.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
          <a href="<?php echo site_url('transport/reports'); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-semibold">
            <span class="material-symbols-outlined text-[16px]">visibility</span> Open Reports
          </a>
        </div>
      </div>

      <!-- Library Reports (Second Phase) -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between opacity-75">
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center">
              <span class="material-symbols-outlined text-[20px]">local_library</span>
            </div>
            <span class="shrink-0 rounded-full bg-tertiary-container/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-on-tertiary-container">Coming Soon</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Library Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Book inventories, circulation trends, overdue returns, and fines (Phase 2).</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40">
          <span class="text-xs text-on-surface-variant/70 italic">Scheduled for Second Phase</span>
        </div>
      </div>

      <!-- Inventory Reports (Second Phase) -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 flex flex-col justify-between opacity-75">
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center">
              <span class="material-symbols-outlined text-[20px]">inventory_2</span>
            </div>
            <span class="shrink-0 rounded-full bg-tertiary-container/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-on-tertiary-container">Coming Soon</span>
          </div>
          <h4 class="font-headline-md text-on-surface text-base font-semibold">Inventory Reports</h4>
          <p class="text-body-md font-body-md text-on-surface-variant mt-1">Stock balance, purchase orders, inventory consumption, and valuations (Phase 2).</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/40">
          <span class="text-xs text-on-surface-variant/70 italic">Scheduled for Second Phase</span>
        </div>
      </div>

    </div>
  </div>
</div>
