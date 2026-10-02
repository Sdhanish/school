<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Reports & Absence Analytics</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Institutional absence metrics, student exemption reports, faculty leave quotas, and CSV/Print exports.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('leave/reports?export=csv'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Total Applications</span>
        <div class="text-title-lg font-bold text-on-surface"><?php echo $stats->total_requests; ?></div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Approved Leaves</span>
        <div class="text-title-lg font-bold text-secondary"><?php echo $stats->approved; ?></div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Students On Leave Today</span>
        <div class="text-title-lg font-bold text-primary"><?php echo $stats->students_on_leave_today; ?></div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Staff On Leave Today</span>
        <div class="text-title-lg font-bold text-secondary"><?php echo $stats->staff_on_leave_today; ?></div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('leave/reports'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category</label>
          <select name="applicant_type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Categories (Student & Staff)</option>
            <option value="Student" <?php echo (($filters['applicant_type'] ?? '') === 'Student') ? 'selected' : ''; ?>>Students Only</option>
            <option value="Staff" <?php echo (($filters['applicant_type'] ?? '') === 'Staff') ? 'selected' : ''; ?>>Staff Only</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Approved" <?php echo (($filters['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?php echo (($filters['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="Pending" <?php echo (($filters['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class (for Students)</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($filters['class_id'] ?? '') == $c->class_id) ? 'selected' : ''; ?>><?php echo html_escape($c->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Report Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Detailed Leave Report (<?php echo count($applications); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">ID</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Applicant</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Leave Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">From</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">To</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Days</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($applications)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No leave records matching the criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($applications as $a): ?>
                <?php
                  $name = ($a->applicant_type === 'Student') ? $a->first_name . ' ' . $a->last_name : $a->staff_name;
                  $stBadge = ($a->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($a->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant">#<?php echo $a->application_id; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface"><?php echo html_escape($name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[12px] text-on-surface-variant"><?php echo $a->applicant_type; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo html_escape($a->type_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo date('d M Y', strtotime($a->from_date)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo date('d M Y', strtotime($a->to_date)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-bold text-on-surface"><?php echo $a->duration_days; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
