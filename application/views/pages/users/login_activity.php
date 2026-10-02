<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Login Activity & Audits</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit trail of successful authentications, failed attempts, and lockout triggers.</p>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Authentication Activity Log (<?php echo count($activity); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Timestamp</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Username</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">IP Address</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Device / User Agent</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($activity)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No authentication activity records found.</td></tr>
            <?php else: ?>
              <?php foreach ($activity as $a): ?>
                <?php
                  $stBadge = ($a->status === 'Successful') ? 'bg-secondary-container text-on-secondary-container' : (($a->status === 'Failed') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error font-bold');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant">
                    <?php echo date('d M Y, h:i A', strtotime($a->created_at)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">
                    <?php echo html_escape($a->username); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-on-surface">
                    <?php echo html_escape($a->ip_address ?: '—'); ?>
                  </td>
                  <td class="px-4 py-3 text-xs text-on-surface-variant max-w-[280px]">
                    <div class="line-clamp-1 font-mono text-[11px]"><?php echo html_escape($a->user_agent); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-xs text-on-surface">
                    <?php echo html_escape($a->failure_reason ?: 'Authorized login session'); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
