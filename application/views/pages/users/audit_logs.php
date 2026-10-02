<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Permission Audit Logs</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Immutable security log of all role modifications, permission grants, and user overrides.</p>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Permission Audit Ledger (<?php echo count($logs); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Timestamp</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Admin User</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Target Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($logs)): ?>
              <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No permission audit logs recorded.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant">
                    <?php echo date('d M Y, h:i A', strtotime($l->created_at)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-xs">
                    <strong class="text-on-surface block"><?php echo html_escape($l->user_name ?: 'System Admin'); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($l->username ?: 'admin'); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-primary text-xs">
                    <?php echo html_escape($l->action); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-surface-container-high text-primary"><?php echo $l->target_type; ?> #<?php echo $l->target_id; ?></span>
                  </td>
                  <td class="px-4 py-3 text-xs text-on-surface">
                    <?php echo html_escape($l->details ?: ($l->new_value ?: 'Configuration change')); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
