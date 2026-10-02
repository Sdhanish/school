<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Vehicle Maintenance History Ledger</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit log of all past servicing, replacement parts, and maintenance costs across the fleet.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('transport/maintenance'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Maintenance
        </a>
      </div>
    </div>

    <!-- History Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Service Records (<?php echo count($records); ?>)</span>
        <span class="text-xs text-on-surface-variant font-mono font-bold">Total Cost: ₹<?php echo number_format($total_cost, 2); ?></span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Service Date</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Vehicle</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Maintenance Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Service Provider</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Cost</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($records)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No historical service records found.</td></tr>
            <?php else: ?>
              <?php foreach ($records as $m): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($m->service_date)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($m->vehicle_number); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($m->registration_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                      <?php echo html_escape($m->maintenance_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                    <?php echo html_escape($m->service_provider); ?>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[200px] truncate">
                    <?php echo html_escape($m->description); ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-on-surface">
                    ₹<?php echo number_format($m->cost, 2); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
