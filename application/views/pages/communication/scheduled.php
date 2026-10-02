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
        <h2 class="font-headline-md text-headline-md text-on-surface">Scheduled Notifications Queue</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Pending and queued automated communications waiting for their scheduled trigger time.</p>
      </div>
    </div>

    <!-- Scheduled Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Pending in Queue (<?php echo count($scheduled); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Message Text</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Scheduled Time</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($scheduled)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No notifications currently scheduled.</td></tr>
            <?php else: ?>
              <?php foreach ($scheduled as $s): ?>
                <?php
                  $chBadge = ($s->channel === 'WhatsApp') ? 'bg-emerald-100 text-emerald-800' : (($s->channel === 'SMS') ? 'bg-amber-100 text-amber-800' : 'bg-primary-container text-on-primary-container');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold <?php echo $chBadge; ?>"><?php echo html_escape($s->channel); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($s->recipient_name ?: $s->recipient_type); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($s->recipient_contact); ?></span>
                  </td>
                  <td class="px-4 py-3 text-[13px] text-on-surface">
                    <?php if ($s->subject): ?>
                      <strong class="block text-primary"><?php echo html_escape($s->subject); ?></strong>
                    <?php endif; ?>
                    <span class="line-clamp-2"><?php echo html_escape(substr(strip_tags($s->message), 0, 120)); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-amber-800 font-semibold">
                    <?php echo date('d M Y, h:i A', strtotime($s->scheduled_at)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                      <?php echo html_escape($s->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('communication/scheduled?cancel=' . $s->message_id); ?>" onclick="return confirm('Cancel this scheduled notification?');" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-error-container text-error text-[11px] font-semibold hover:bg-error/20">
                      <span class="material-symbols-outlined text-[14px]">cancel</span>Cancel
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
