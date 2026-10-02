<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$staff_id = $staff->staff_id ?? ($staff->id ?? 0);
?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="<?php echo site_url('finance/ledger_staff'); ?>" class="text-[13px] text-primary hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>Back to Staff Ledgers
          </a>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff Accounting Ledger Statement</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit trail of salary accruals, payouts, advances, and running payable balance.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/staff_payouts'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">payments</span>Record Payout
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Statement
        </button>
      </div>
    </div>

    <!-- Staff Info Card -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-outline-variant/50 mb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-purple-700 text-white font-bold text-lg flex items-center justify-center shrink-0">
            <?php echo strtoupper(substr($staff->full_name ?? ($staff->first_name ?? 'S'), 0, 1)); ?>
          </div>
          <div>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">
              <?php echo html_escape($staff->full_name ?? ($staff->first_name . ' ' . $staff->last_name)); ?>
            </h3>
            <span class="text-body-md text-on-surface-variant font-mono">Employee Code: <?php echo html_escape($staff->employee_code ?? ('EMP' . $staff->id)); ?></span>
          </div>
        </div>
        <div class="text-right">
          <span class="text-body-xs text-on-surface-variant uppercase tracking-wider block font-semibold">Net Balance (Payable)</span>
          <span class="text-2xl font-bold font-mono text-on-surface">
            ₹<?php echo number_format($statement['closing_balance'], 2); ?>
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-body-md">
        <div class="p-3 bg-surface-container-low rounded-xl">
          <span class="text-on-surface-variant text-[12px] block">Opening Balance</span>
          <span class="font-semibold font-mono text-on-surface">₹<?php echo number_format($statement['opening_balance'], 2); ?></span>
        </div>
        <div class="p-3 bg-surface-container-low rounded-xl">
          <span class="text-on-surface-variant text-[12px] block">Total Debited (Disbursed)</span>
          <span class="font-semibold font-mono text-secondary">₹<?php echo number_format($statement['total_debit'], 2); ?></span>
        </div>
        <div class="p-3 bg-surface-container-low rounded-xl">
          <span class="text-on-surface-variant text-[12px] block">Total Credited (Accrued)</span>
          <span class="font-semibold font-mono text-on-surface">₹<?php echo number_format($statement['total_credit'], 2); ?></span>
        </div>
        <div class="p-3 bg-surface-container-low rounded-xl">
          <span class="text-on-surface-variant text-[12px] block">Current Net Balance</span>
          <span class="font-semibold font-mono text-on-surface">₹<?php echo number_format($statement['closing_balance'], 2); ?></span>
        </div>
      </div>
    </div>

    <!-- Date Range Filter -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/staff_statement/' . $staff_id); ?>" class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-body-sm text-on-surface font-medium">From:</label>
          <input type="date" name="from_date" value="<?php echo html_escape($from_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-body-sm text-on-surface font-medium">To:</label>
          <input type="date" name="to_date" value="<?php echo html_escape($to_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          Filter
        </button>
        <a href="<?php echo site_url('finance/staff_statement/' . $staff_id); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">
          Reset
        </a>
      </form>
    </div>

    <!-- Transaction Ledger Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Txn / Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Transaction Type</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration / Particulars</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit (Paid/Deducted)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit (Accrued)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Running Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <!-- Opening Balance Row -->
            <tr class="bg-surface-container-low/30 italic">
              <td class="px-4 py-3 font-semibold text-on-surface">—</td>
              <td class="px-4 py-3 text-on-surface-variant">—</td>
              <td class="px-4 py-3 text-on-surface-variant font-semibold">Opening Balance</td>
              <td class="px-4 py-3 text-on-surface-variant">Balance brought forward</td>
              <td class="px-4 py-3 text-right font-mono">—</td>
              <td class="px-4 py-3 text-right font-mono">—</td>
              <td class="px-4 py-3 text-right font-mono font-bold text-on-surface">₹<?php echo number_format($statement['opening_balance'], 2); ?></td>
            </tr>

            <?php if (!empty($statement['entries'])): ?>
              <?php foreach ($statement['entries'] as $e): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($e->transaction_date)); ?></td>
                  <td class="px-4 py-3 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($e->transaction_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-800">
                      <?php echo html_escape($e->transaction_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface">
                    <?php echo html_escape($e->description ?: $e->narration); ?>
                    <?php if (!empty($e->reference_no)): ?>
                      <span class="block text-[11px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($e->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-secondary font-semibold whitespace-nowrap">
                    <?php echo ($e->debit > 0) ? ('₹' . number_format($e->debit, 2)) : '—'; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-on-surface whitespace-nowrap">
                    <?php echo ($e->credit > 0) ? ('₹' . number_format($e->credit, 2)) : '—'; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($e->running_balance, 2); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>

            <!-- Closing Totals Row -->
            <tr class="bg-surface-container-low/80 font-bold border-t-2 border-outline-variant">
              <td colspan="4" class="px-4 py-3 text-right text-on-surface uppercase text-body-sm">Period Totals & Closing Balance</td>
              <td class="px-4 py-3 text-right font-mono text-secondary">₹<?php echo number_format($statement['total_debit'], 2); ?></td>
              <td class="px-4 py-3 text-right font-mono text-on-surface">₹<?php echo number_format($statement['total_credit'], 2); ?></td>
              <td class="px-4 py-3 text-right font-mono text-primary text-title-sm">₹<?php echo number_format($statement['closing_balance'], 2); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
