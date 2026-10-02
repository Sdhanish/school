<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Failed Notifications Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review undelivered messages, examine gateway failure errors, and execute manual retries.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('communication/history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">history</span>All History
        </a>
      </div>
    </div>

    <!-- Failed Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-error">Failed Notifications Queue (<?php echo count($failed_list); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">ID</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Source</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Failure Reason</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Retry Count</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($failed_list)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No failed notifications found. System is running cleanly.</td></tr>
            <?php else: ?>
              <?php foreach ($failed_list as $f): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">#<?php echo $f->message_id; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($f->recipient_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($f->recipient_contact); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-error-container text-error font-mono"><?php echo $f->channel; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                    <?php echo html_escape($f->source_module); ?>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-error font-mono max-w-[280px]">
                    <div class="line-clamp-2"><?php echo html_escape($f->failure_reason ?: 'Provider timeout / unreachable'); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs">
                    <?php echo $f->retry_count; ?> / <?php echo $f->max_retries; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <a href="<?php echo site_url('communication/retry_failed/' . $f->message_id); ?>" class="px-3 py-1 rounded bg-secondary text-on-secondary text-xs font-semibold hover:bg-on-secondary-fixed-variant inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">replay</span>Retry
                      </a>
                      <a href="<?php echo site_url('communication/cancel_queue_item/' . $f->message_id); ?>" onclick="return confirm('Cancel this notification?');" class="px-2 py-1 rounded border border-outline-variant text-on-surface-variant text-xs hover:bg-surface-container-high">
                        Cancel
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
