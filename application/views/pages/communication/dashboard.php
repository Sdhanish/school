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

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Notification & Communication Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Centralized automated notification engine across SMS, WhatsApp, Email, and In-App channels.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/queue'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">hourglass_top</span>Queue (<?php echo $queued_count; ?>)
        </a>
        <a href="<?php echo site_url('communication/automated_notifications'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">bolt</span>Automation Rules (<?php echo $active_rules_count; ?>)
        </a>
      </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Notifications -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Notifications</span>
          <span class="material-symbols-outlined text-primary text-[22px]">campaign</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_notifications; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->delivered; ?> Delivered</span> (<?php echo $stats->delivery_pct; ?>%)
        </div>
      </div>

      <!-- Pending / Queue -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Pending Queue</span>
          <span class="material-symbols-outlined text-amber-600 text-[22px]">pending</span>
        </div>
        <div class="text-headline-md font-bold text-amber-600"><?php echo $stats->pending; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span><?php echo $stats->scheduled; ?> Scheduled future jobs</span>
        </div>
      </div>

      <!-- Failed Notifications -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Failed Notifications</span>
          <span class="material-symbols-outlined text-error text-[22px]">error</span>
        </div>
        <div class="text-headline-md font-bold text-error"><?php echo $stats->failed; ?></div>
        <div class="text-xs text-on-surface-variant">
          <a href="<?php echo site_url('communication/failed'); ?>" class="text-error font-semibold hover:underline">Review & Retry</a>
        </div>
      </div>

      <!-- Active Automation Rules -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Automated Rules</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">auto_mode</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo $active_rules_count; ?></div>
        <div class="text-xs text-on-surface-variant">
          Multi-module event triggers active
        </div>
      </div>
    </div>

    <!-- CHANNEL SUMMARY BREAKDOWN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <a href="<?php echo site_url('communication/history?channel=In-App'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-on-surface-variant uppercase">In-App</span>
          <span class="material-symbols-outlined text-primary text-[20px]">notifications</span>
        </div>
        <div class="text-title-lg font-bold text-primary mt-1"><?php echo $stats->inapp_sent; ?> Sent</div>
      </a>
      <a href="<?php echo site_url('communication/history?channel=SMS'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-on-surface-variant uppercase">SMS</span>
          <span class="material-symbols-outlined text-secondary text-[20px]">sms</span>
        </div>
        <div class="text-title-lg font-bold text-secondary mt-1"><?php echo $stats->sms_sent; ?> Sent</div>
      </a>
      <a href="<?php echo site_url('communication/history?channel=WhatsApp'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-on-surface-variant uppercase">WhatsApp</span>
          <span class="material-symbols-outlined text-emerald-600 text-[20px]">chat</span>
        </div>
        <div class="text-title-lg font-bold text-emerald-600 mt-1"><?php echo $stats->whatsapp_sent; ?> Sent</div>
      </a>
      <a href="<?php echo site_url('communication/history?channel=Email'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-on-surface-variant uppercase">Email</span>
          <span class="material-symbols-outlined text-amber-600 text-[20px]">mail</span>
        </div>
        <div class="text-title-lg font-bold text-amber-600 mt-1"><?php echo $stats->email_sent; ?> Sent</div>
      </a>
    </div>

    <!-- 2 COLUMN DIVISION: Recent Activity + Failed Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Recent Notifications -->
      <div class="lg:col-span-2 elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">history</span>Recent Notifications
          </h3>
          <a href="<?php echo site_url('communication/history'); ?>" class="text-xs text-primary font-semibold hover:underline">View All History</a>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Source</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Sent Date</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($recent_notifications)): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No notifications recorded yet.</td></tr>
              <?php else: ?>
                <?php foreach ($recent_notifications as $n): ?>
                  <?php
                    $stBadge = ($n->status === 'Delivered') ? 'bg-secondary-container text-on-secondary-container' : (($n->status === 'Sent') ? 'bg-primary-container text-on-primary-container' : (($n->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error'));
                  ?>
                  <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <strong class="text-on-surface block"><?php echo html_escape($n->recipient_name); ?></strong>
                      <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($n->recipient_contact); ?> (<?php echo $n->recipient_type; ?>)</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                      <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-surface-container-high text-primary"><?php echo $n->channel; ?></span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                      <?php echo html_escape($n->source_module); ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                      <?php echo date('d M Y, h:i A', strtotime($n->created_at)); ?>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                        <?php echo html_escape($n->status); ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                      <a href="<?php echo site_url('communication/details/' . $n->message_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>Inspect
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right 1 Col: Failed Notifications & Quick Actions -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-error text-[22px]">error</span>Failed Notifications
          </h3>
          <a href="<?php echo site_url('communication/failed'); ?>" class="text-xs text-error font-semibold hover:underline">All Failed</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($failed_notifications)): ?>
            <div class="py-6 text-center text-on-surface-variant text-xs">No failed notifications. System healthy.</div>
          <?php else: ?>
            <?php foreach (array_slice($failed_notifications, 0, 5) as $f): ?>
              <div class="py-3 space-y-1.5">
                <div class="flex items-center justify-between">
                  <strong class="text-body-md text-on-surface"><?php echo html_escape($f->recipient_name); ?></strong>
                  <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-error-container text-error"><?php echo $f->channel; ?></span>
                </div>
                <div class="text-[12px] text-on-surface-variant font-mono"><?php echo html_escape($f->recipient_contact); ?></div>
                <p class="text-[11px] text-error line-clamp-1 italic"><?php echo html_escape($f->failure_reason ?: 'Delivery failure'); ?></p>
                <div class="pt-1 flex items-center justify-between">
                  <span class="text-[10px] text-on-surface-variant font-mono">Retries: <?php echo $f->retry_count; ?>/<?php echo $f->max_retries; ?></span>
                  <a href="<?php echo site_url('communication/retry_failed/' . $f->message_id); ?>" class="px-2.5 py-1 rounded bg-secondary text-on-secondary text-[11px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors">
                    Retry Now
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
