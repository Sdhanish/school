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
        <h2 class="font-headline-md text-headline-md text-on-surface">Notification Queue</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Real-time pipeline of scheduled, pending, and processing multi-channel notifications.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('communication/history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">history</span>Delivery History
        </a>
      </div>
    </div>

    <!-- Queue Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Queued Items (<?php echo count($queue); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">ID</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Event / Template</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Rendered Message</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Priority</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($queue)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">Notification queue is currently empty.</td></tr>
            <?php else: ?>
              <?php foreach ($queue as $q): ?>
                <?php
                  $stBadge = ($q->status === 'Processing') ? 'bg-primary-container text-on-primary-container' : (($q->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-surface-container-high text-on-surface-variant');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">#<?php echo $q->message_id; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($q->recipient_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($q->recipient_contact); ?> (<?php echo $q->recipient_type; ?>)</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-container-high text-primary font-mono"><?php echo $q->channel; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block text-[13px]"><?php echo html_escape($q->event_name ?: $q->source_module); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($q->template_code); ?></span>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[280px]">
                    <div class="line-clamp-2 font-mono text-[11px]"><?php echo html_escape($q->rendered_message ?: $q->message); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($q->priority === 'Urgent') ? 'bg-error-container text-error' : (($q->priority === 'Important') ? 'bg-amber-100 text-amber-900' : 'bg-surface-container-high text-on-surface-variant'); ?>">
                      <?php echo $q->priority; ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>"><?php echo $q->status; ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <a href="<?php echo site_url('communication/process_queue_item/' . $q->message_id); ?>" class="p-1 rounded bg-secondary text-on-secondary text-xs font-semibold hover:bg-on-secondary-fixed-variant inline-flex items-center gap-1">
                        Dispatch
                      </a>
                      <a href="<?php echo site_url('communication/cancel_queue_item/' . $q->message_id); ?>" onclick="return confirm('Cancel this notification?');" class="p-1 rounded bg-error-container text-error text-xs font-semibold hover:bg-error/20 inline-flex items-center gap-1">
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
