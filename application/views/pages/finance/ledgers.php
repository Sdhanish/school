<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Sub-Ledger Directory</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Individual subsidiary ledgers for Students, Staff, Vendors, and Control Accounts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>Chart of Accounts
        </a>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/ledgers'); ?>" class="flex flex-col sm:flex-row items-center gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <a href="<?php echo site_url('finance/ledgers'); ?>" class="px-3.5 py-2 rounded-lg text-body-sm font-semibold transition-colors <?php echo empty($active_type) ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface'; ?>">
            All (<?php echo count($ledgers); ?>)
          </a>
          <a href="<?php echo site_url('finance/ledgers?type=Student'); ?>" class="px-3.5 py-2 rounded-lg text-body-sm font-semibold transition-colors <?php echo ($active_type === 'Student') ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface'; ?>">
            Students
          </a>
          <a href="<?php echo site_url('finance/ledgers?type=Staff'); ?>" class="px-3.5 py-2 rounded-lg text-body-sm font-semibold transition-colors <?php echo ($active_type === 'Staff') ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface'; ?>">
            Staff
          </a>
          <a href="<?php echo site_url('finance/ledgers?type=General'); ?>" class="px-3.5 py-2 rounded-lg text-body-sm font-semibold transition-colors <?php echo ($active_type === 'General') ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface'; ?>">
            General
          </a>
        </div>
        <div class="relative flex-1 w-full">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
          <input type="text" name="search" value="<?php echo html_escape($search ?? ''); ?>" placeholder="Search ledger by name, admission no, or code..." class="w-full pl-9 pr-4 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
          <?php if (!empty($active_type)): ?>
            <input type="hidden" name="type" value="<?php echo html_escape($active_type); ?>"/>
          <?php endif; ?>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-body-sm font-medium transition-colors shrink-0">
          Filter
        </button>
      </form>
    </div>

    <!-- Ledgers Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Ledger Code</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Entity Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Category</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Control Account</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Opening Bal</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Current Balance</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Statement</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($ledgers)): ?>
              <?php foreach ($ledgers as $l): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary text-sm whitespace-nowrap"><?php echo html_escape($l->ledger_code); ?></td>
                  <td class="px-4 py-3 font-medium text-on-surface">
                    <?php echo html_escape($l->ledger_name); ?>
                    <?php if (!empty($l->phone)): ?>
                      <span class="block text-[12px] text-on-surface-variant font-mono"><?php echo html_escape($l->phone); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                      <?php
                        switch ($l->entity_type) {
                          case 'Student': echo 'bg-blue-100 text-blue-800'; break;
                          case 'Staff': echo 'bg-purple-100 text-purple-800'; break;
                          case 'Vendor': echo 'bg-amber-100 text-amber-800'; break;
                          default: echo 'bg-surface-container-high text-on-surface'; break;
                        }
                      ?>">
                      <?php echo html_escape($l->entity_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm whitespace-nowrap">
                    <?php echo html_escape(($l->control_account_name ?? '—') . ' (' . ($l->control_account_code ?? '') . ')'); ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-sm whitespace-nowrap">
                    ₹<?php echo number_format($l->opening_balance ?? 0, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($l->current_balance ?? 0, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <?php if ($l->entity_type === 'Student' && !empty($l->entity_id)): ?>
                      <a href="<?php echo site_url('finance/student_statement/' . $l->entity_id); ?>" class="inline-flex items-center gap-1 text-[13px] font-semibold text-primary hover:underline">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>Statement
                      </a>
                    <?php elseif ($l->entity_type === 'Staff' && !empty($l->entity_id)): ?>
                      <a href="<?php echo site_url('finance/staff_statement/' . $l->entity_id); ?>" class="inline-flex items-center gap-1 text-[13px] font-semibold text-primary hover:underline">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>Statement
                      </a>
                    <?php else: ?>
                      <span class="text-on-surface-variant text-[13px]">General</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No sub-ledgers found matching criteria.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
