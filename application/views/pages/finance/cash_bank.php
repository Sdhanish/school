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

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Cash & Bank Liquidity Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Real-time ledger balances for physical cash counters and institutional bank accounts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/transfers'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">sync_alt</span>Inter-Account Transfer
        </a>
        <a href="<?php echo site_url('finance/accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">add</span>Add Bank Account
        </a>
      </div>
    </div>

    <!-- Accounts Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <?php if (!empty($accounts)): ?>
        <?php foreach ($accounts as $acc): ?>
          <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-3">
                <span class="px-2.5 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider <?php echo ($acc->account_type === 'Cash') ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'; ?>">
                  <?php echo html_escape($acc->account_type); ?> Book
                </span>
                <span class="font-mono text-body-sm font-semibold text-primary"><?php echo html_escape($acc->account_code); ?></span>
              </div>
              <h3 class="font-headline-md text-title-md font-bold text-on-surface mb-2"><?php echo html_escape($acc->account_name); ?></h3>
              
              <?php if (!empty($acc->account_number)): ?>
                <div class="space-y-1 mb-4 text-body-sm text-on-surface-variant font-mono bg-surface-container-low p-3 rounded-xl border border-outline-variant/30">
                  <div>Bank: <strong class="text-on-surface"><?php echo html_escape($acc->bank_name); ?></strong></div>
                  <div>A/C #: <strong class="text-on-surface"><?php echo html_escape($acc->account_number); ?></strong></div>
                  <div>IFSC: <strong class="text-on-surface"><?php echo html_escape($acc->ifsc_code ?? '—'); ?></strong></div>
                  <div>Branch: <strong class="text-on-surface"><?php echo html_escape($acc->branch_name ?? '—'); ?></strong></div>
                </div>
              <?php else: ?>
                <div class="mb-4 text-body-sm text-on-surface-variant bg-surface-container-low p-3 rounded-xl border border-outline-variant/30">
                  Physical school counter / register cash ledger.
                </div>
              <?php endif; ?>
            </div>

            <div class="pt-4 border-t border-outline-variant/40 flex items-center justify-between">
              <div>
                <span class="text-body-xs text-on-surface-variant block uppercase tracking-wider">Book Balance</span>
                <span class="text-2xl font-bold font-mono text-on-surface">₹<?php echo number_format($acc->current_balance ?? 0, 2); ?></span>
              </div>
              <div class="flex items-center gap-1">
                <?php if ($acc->account_type === 'Cash'): ?>
                  <a href="<?php echo site_url('finance/reports/cash_book?account_id=' . $acc->id); ?>" class="inline-flex items-center gap-1 text-[13px] font-semibold text-primary hover:underline">
                    Cash Book &rarr;
                  </a>
                <?php else: ?>
                  <a href="<?php echo site_url('finance/reports/bank_book?account_id=' . $acc->id); ?>" class="inline-flex items-center gap-1 text-[13px] font-semibold text-primary hover:underline">
                    Bank Book &rarr;
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full py-12 text-center text-on-surface-variant bg-surface-container-lowest rounded-2xl border border-outline-variant/50">
          No Cash or Bank accounts found.
        </div>
      <?php endif; ?>
    </div>

    <!-- Recent Inter-Account Contra Transfers -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Recent Contra Transfers</h3>
          <p class="text-body-sm text-on-surface-variant mt-0.5">Internal fund movements between institutional accounts.</p>
        </div>
        <a href="<?php echo site_url('finance/transfers'); ?>" class="text-[13px] font-semibold text-primary hover:underline">All Transfers &rarr;</a>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Transfer #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Source (From)</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Destination (To)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration / Ref</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($recent_transfers)): ?>
              <?php foreach ($recent_transfers as $tr): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($tr->transfer_date)); ?></td>
                  <td class="px-4 py-3 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($tr->transfer_no); ?></td>
                  <td class="px-4 py-3 text-on-surface font-medium whitespace-nowrap"><?php echo html_escape($tr->from_account_name); ?></td>
                  <td class="px-4 py-3 text-on-surface font-medium whitespace-nowrap"><?php echo html_escape($tr->to_account_name); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">₹<?php echo number_format($tr->amount, 2); ?></td>
                  <td class="px-4 py-3 text-on-surface text-body-sm"><?php echo html_escape($tr->description ?: $tr->reference_no ?: '—'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No transfers recorded yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
