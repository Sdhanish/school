<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Balances Matrix</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Track annual leave quotas, utilized days, and remaining balances for staff and eligible students.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('leave/balances?type=Staff'); ?>" class="px-3.5 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'Staff') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
          Staff Quotas
        </a>
        <a href="<?php echo site_url('leave/balances?type=Student'); ?>" class="px-3.5 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'Student') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
          Student Quotas
        </a>
      </div>
    </div>

    <!-- Balance Matrix Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface"><?php echo $type; ?> Balances (<?php echo count($balances); ?> records)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Person</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Designation / Class</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Leave Type</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Allocated</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Used</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Carry Fwd</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Remaining</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($balances)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No balance allocations found. Balances auto-initialize upon first request.</td></tr>
            <?php else: ?>
              <?php foreach ($balances as $b): ?>
                <?php
                  $name = ($type === 'Student') ? $b->first_name . ' ' . $b->last_name : $b->staff_name;
                  $scope = ($type === 'Student') ? ($b->class_name . ' - ' . $b->section_name) : ($b->designation_name ?: 'Faculty');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($b->employee_code ?: ($b->admission_number ?? $b->admission_no ?? '')); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($scope); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                      <?php echo html_escape($b->type_name); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[13px] text-on-surface">
                    <?php echo (float)$b->allocated_days; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[13px] text-error font-bold">
                    <?php echo (float)$b->used_days; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[13px] text-on-surface-variant">
                    <?php echo (float)$b->carry_forward_days; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[13px] text-secondary font-bold">
                    <?php echo (float)$b->remaining_days; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
