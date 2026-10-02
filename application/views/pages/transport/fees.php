<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Transport Fees & Route Pricing</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure route-based fare structures, monthly bus fees, and view student transport fee billing.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('finance/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">payments</span>Finance Module
        </a>
      </div>
    </div>

    <!-- Route Fare Matrix -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Route Stop Pricing Schedule</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Route</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Stop Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Pickup Schedule</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Distance</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Monthly Fare</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($stops)): ?>
              <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No stop fare rates configured.</td></tr>
            <?php else: ?>
              <?php foreach ($stops as $s): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($s->route_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($s->stop_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($s->stop_code); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo html_escape($s->pickup_time); ?> - <?php echo html_escape($s->drop_time); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[12px] text-on-surface-variant">
                    <?php echo $s->distance_km; ?> km
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-secondary text-title-md">
                    ₹<?php echo number_format($s->fare_amount, 2); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
