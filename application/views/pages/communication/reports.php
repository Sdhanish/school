<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Notification Delivery Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Multi-channel performance analytics, delivery percentages, and per-module statistics.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/reports?export=csv'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
      </div>
    </div>

    <!-- Overview Stats Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Overall Sent</span>
        <div class="text-title-lg font-bold text-on-surface mt-1"><?php echo $stats->total_sent; ?> Messages</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Total Delivered</span>
        <div class="text-title-lg font-bold text-secondary mt-1"><?php echo $stats->delivered; ?> (<?php echo $stats->delivery_pct; ?>%)</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Failed / Undelivered</span>
        <div class="text-title-lg font-bold text-error mt-1"><?php echo $stats->failed; ?> Messages</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Pending Queue</span>
        <div class="text-title-lg font-bold text-amber-600 mt-1"><?php echo $stats->pending; ?> Jobs</div>
      </div>
    </div>

    <!-- 2 Column Reports: Channel Analytics & Module Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <!-- By Channel Table -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">cell_tower</span>Performance by Channel
          </h3>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Channel</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Sent</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Delivered</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Failed</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Delivery Rate</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($reports['channel_report'] as $ch): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface"><?php echo $ch->channel; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono"><?php echo $ch->total; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-secondary font-semibold"><?php echo $ch->delivered; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-error"><?php echo $ch->failed; ?></td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-primary"><?php echo $ch->rate; ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- By Module Table -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">view_module</span>Performance by Module
          </h3>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Module</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Total Sent</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Delivered</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Failed</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Delivery Rate</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($reports['module_report'] as $m): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface"><?php echo $m->module; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono"><?php echo $m->total; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-secondary font-semibold"><?php echo $m->delivered; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-error"><?php echo $m->failed; ?></td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-primary"><?php echo $m->rate; ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
