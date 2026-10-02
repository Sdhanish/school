<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <a href="<?php echo site_url('finance/ledger_other_parties'); ?>" class="text-[13px] text-primary hover:underline flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>Back to Party Ledgers
        </a>
      </div>
      <h2 class="font-headline-md text-headline-md text-on-surface">Vendor / Party Ledger Statement</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit trail of payables, disbursements, and running balance for this party.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">print</span>Print Statement
      </button>
    </div>
  </div>

  <?php if (!$statement): ?>
  <div class="elevation-1 rounded-2xl bg-error/5 border border-error/20 p-8 text-center">
    <span class="material-symbols-outlined text-error text-[40px] block mb-2">error</span>
    <p class="text-on-surface font-semibold">Statement could not be loaded. The ledger may not exist for this school.</p>
  </div>
  <?php else:
    $s = $statement;
    $led = $s['ledger'];
  ?>

  <!-- Party Info Card -->
  <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-outline-variant/50 mb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-orange-600 text-white font-bold text-lg flex items-center justify-center shrink-0">
          <?php echo strtoupper(substr($led->ledger_name ?? 'P', 0, 1)); ?>
        </div>
        <div>
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface"><?php echo html_escape($led->ledger_name ?? '—'); ?></h3>
          <span class="text-body-md text-on-surface-variant font-mono">Code: <?php echo html_escape($led->ledger_code ?? '—'); ?></span>
          <?php if (!empty($led->phone)): ?>
            <span class="ml-3 text-body-sm text-on-surface-variant">📞 <?php echo html_escape($led->phone); ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-right">
        <span class="text-body-xs text-on-surface-variant uppercase tracking-wider block font-semibold">Net Balance (Payable)</span>
        <span class="text-2xl font-bold font-mono <?php echo $s['closing_balance'] > 0 ? 'text-orange-700' : 'text-emerald-700'; ?>">
          ₹<?php echo number_format(abs($s['closing_balance']), 2); ?>
          <span class="text-[14px] font-normal text-on-surface-variant"><?php echo $s['closing_balance'] > 0 ? 'Payable' : 'Credit'; ?></span>
        </span>
      </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-body-md">
      <div class="p-3 bg-surface-container-low rounded-xl">
        <span class="text-on-surface-variant text-[12px] block">Opening Balance</span>
        <span class="font-semibold font-mono text-on-surface">₹<?php echo number_format($s['opening_balance'], 2); ?></span>
      </div>
      <div class="p-3 bg-surface-container-low rounded-xl">
        <span class="text-on-surface-variant text-[12px] block">Total Credited (Invoiced)</span>
        <span class="font-semibold font-mono text-on-surface">₹<?php echo number_format($s['total_credit'], 2); ?></span>
      </div>
      <div class="p-3 bg-surface-container-low rounded-xl">
        <span class="text-on-surface-variant text-[12px] block">Total Debited (Paid)</span>
        <span class="font-semibold font-mono text-emerald-700">₹<?php echo number_format($s['total_debit'], 2); ?></span>
      </div>
      <div class="p-3 bg-surface-container-low rounded-xl">
        <span class="text-on-surface-variant text-[12px] block">Closing Balance</span>
        <span class="font-semibold font-mono <?php echo $s['closing_balance'] > 0 ? 'text-orange-700' : 'text-emerald-700'; ?>">
          ₹<?php echo number_format(abs($s['closing_balance']), 2); ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Date Range Filter -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
    <form method="get" action="<?php echo site_url('finance/other_party_statement/' . $ledger->id); ?>" class="flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <label class="text-body-sm text-on-surface font-medium">From:</label>
        <input type="date" name="from_date" value="<?php echo html_escape($from_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
      </div>
      <div class="flex items-center gap-2">
        <label class="text-body-sm text-on-surface font-medium">To:</label>
        <input type="date" name="to_date" value="<?php echo html_escape($to_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
      </div>
      <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">Filter</button>
      <a href="<?php echo site_url('finance/other_party_statement/' . $ledger->id); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">Reset</a>
    </form>
  </div>

  <!-- Transaction Ledger Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Transaction Type</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration / Particulars</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit (Paid)</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit (Invoiced)</th>
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
            <td class="px-4 py-3 text-right font-mono font-bold text-on-surface">₹<?php echo number_format($s['opening_balance'], 2); ?></td>
          </tr>

          <?php if (!empty($s['lines'])): ?>
            <?php foreach ($s['lines'] as $e): ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors">
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($e->transaction_date)); ?></td>
                <td class="px-4 py-3 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($e->transaction_number); ?></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800">
                    <?php echo str_replace('_', ' ', html_escape($e->transaction_type ?? '—')); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface"><?php echo html_escape($e->description ?: ($e->tx_desc ?? '—')); ?></td>
                <td class="px-4 py-3 text-right font-mono text-emerald-700 font-semibold whitespace-nowrap">
                  <?php echo ((float)$e->debit > 0) ? '₹' . number_format((float)$e->debit, 2) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-right font-mono text-on-surface whitespace-nowrap">
                  <?php echo ((float)$e->credit > 0) ? '₹' . number_format((float)$e->credit, 2) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap <?php echo $e->running_balance > 0 ? 'text-orange-700' : 'text-on-surface'; ?>">
                  ₹<?php echo number_format($e->running_balance, 2); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Closing Totals -->
          <tr class="bg-surface-container-low/80 font-bold border-t-2 border-outline-variant">
            <td colspan="4" class="px-4 py-3 text-right text-on-surface uppercase text-body-sm">Period Totals & Closing Balance</td>
            <td class="px-4 py-3 text-right font-mono text-emerald-700">₹<?php echo number_format($s['total_debit'], 2); ?></td>
            <td class="px-4 py-3 text-right font-mono text-on-surface">₹<?php echo number_format($s['total_credit'], 2); ?></td>
            <td class="px-4 py-3 text-right font-mono text-primary text-title-sm">₹<?php echo number_format(abs($s['closing_balance']), 2); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
