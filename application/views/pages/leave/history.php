<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave History & Audit Trail</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Full historical log of all leave applications, approvals, rejections, and cancellations.</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('leave/history'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Approved" <?php echo (($filters['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?php echo (($filters['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="Pending" <?php echo (($filters['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Cancelled" <?php echo (($filters['status'] ?? '') === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Applicant name, reason..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div class="flex items-end">
          <button type="submit" class="w-full py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
            Filter History
          </button>
        </div>
      </form>
    </div>

    <!-- History Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Application Records (<?php echo count($applications); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Applicant</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Dates</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Days</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason / Remarks</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Approver</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($applications)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No leave records found.</td></tr>
            <?php else: ?>
              <?php foreach ($applications as $a): ?>
                <?php
                  $name = ($a->applicant_type === 'Student') ? $a->first_name . ' ' . $a->last_name : $a->staff_name;
                  $stBadge = ($a->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($a->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($name); ?></strong>
                    <span class="text-[10px] text-on-surface-variant"><?php echo $a->applicant_type; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[12px] font-mono text-on-surface"><?php echo html_escape($a->type_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M', strtotime($a->from_date)) . ' - ' . date('d M Y', strtotime($a->to_date)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-bold text-on-surface"><?php echo $a->duration_days; ?></td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[200px] truncate">
                    <?php echo html_escape($a->reason); ?>
                    <?php if ($a->rejection_reason): ?>
                      <span class="block text-error font-semibold">Rejected: <?php echo html_escape($a->rejection_reason); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[12px] text-on-surface-variant font-medium">
                    <?php echo html_escape($a->approver_name ?: 'System / Pending'); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('leave/details/' . $a->application_id); ?>" class="p-1.5 rounded hover:bg-surface-container-high text-primary font-semibold text-xs">
                      View
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
