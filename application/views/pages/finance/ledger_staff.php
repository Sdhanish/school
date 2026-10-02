<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
        <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-semibold">Staff Ledgers</span>
      </nav>
      <h2 class="font-headline-md text-headline-md text-on-surface">Staff Ledger Accounts</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Individual staff payable accounts — salary accruals, payouts, advances & running balances.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0 flex-wrap">
      <a href="<?php echo site_url('finance/staff_payouts'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
        <span class="material-symbols-outlined text-[18px]">payments</span>Staff Payout
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-5 rounded-2xl bg-purple-50 border border-purple-200/70 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-purple-600 text-[22px]">account_balance_wallet</span>
        <span class="text-body-sm font-semibold text-purple-800 uppercase tracking-wide">Total Payable</span>
      </div>
      <div class="text-2xl font-bold font-mono text-purple-700">₹<?php echo number_format($total_payable, 2); ?></div>
      <div class="text-body-xs text-purple-600 mt-1">Aggregate staff payables</div>
    </div>
    <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
        <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wide">Total Staff</span>
      </div>
      <div class="text-2xl font-bold font-mono text-on-surface"><?php echo number_format($total_count); ?></div>
      <div class="text-body-xs text-on-surface-variant mt-1">Active ledger accounts</div>
    </div>
    <div class="p-5 rounded-2xl bg-blue-50 border border-blue-200/70 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-blue-600 text-[22px]">calendar_month</span>
        <span class="text-body-sm font-semibold text-blue-800 uppercase tracking-wide">Current Month</span>
      </div>
      <div class="text-2xl font-bold font-mono text-blue-700"><?php echo date('M Y'); ?></div>
      <div class="text-body-xs text-blue-600 mt-1">Payroll period</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-5">
    <form method="get" action="<?php echo site_url('finance/ledger_staff'); ?>" class="flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Search Staff</label>
        <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>"
               placeholder="Name, employee code, ledger code…"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      
      <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">search</span> Filter
        </button>
        <a href="<?php echo site_url('finance/ledger_staff'); ?>" class="px-3 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">Reset</a>
      </div>
    </form>
  </div>

  <!-- Staff Ledger Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="px-5 py-3 border-b border-outline-variant/40 flex items-center justify-between">
      <span class="text-title-sm font-semibold text-on-surface">
        <span class="material-symbols-outlined text-purple-600 align-middle text-[18px] mr-1">badge</span>
        Staff Payable Ledgers
        <span class="ml-2 text-body-xs text-on-surface-variant font-normal">(<?php echo $total_count; ?> accounts)</span>
      </span>
    </div>
    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md" id="staff-ledger-table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Staff Member</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Employee Code</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Ledger Code</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Accrued (Cr)</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Paid (Dr)</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Payable</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($ledgers)): ?>
            <tr>
              <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface-variant/50">badge</span>
                No staff ledger accounts found.
                <p class="text-body-sm mt-1">Staff ledgers are created automatically when a staff payout is recorded.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php $idx = 1; foreach ($ledgers as $l): ?>
              <?php
                $balance = $l->current_balance;
                $bal_badge = $balance > 0 ? 'bg-purple-100 text-purple-800' : ($balance < 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-on-surface-variant');
              ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors cursor-pointer" onclick="window.location.href='<?php echo site_url('finance/staff_statement/' . $l->staff_id); ?>'">
                <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $idx++; ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-purple-600 text-white font-bold text-sm flex items-center justify-center shrink-0">
                      <?php echo strtoupper(substr($l->full_name ?? 'S', 0, 1)); ?>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($l->full_name ?: ($l->ledger_name ?? '—')); ?></div>
                      <div class="text-[11px] text-on-surface-variant"><?php echo html_escape($l->designation_name ?? '—'); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-on-surface"><?php echo html_escape($l->employee_code ?? '—'); ?></td>
                <td class="px-4 py-3 font-mono text-[12px] text-purple-700 font-semibold"><?php echo html_escape($l->ledger_code ?? '—'); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface">₹<?php echo number_format((float)$l->total_credit, 2); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-emerald-700 font-semibold">₹<?php echo number_format((float)$l->total_debit, 2); ?></td>
                <td class="px-4 py-3 text-right">
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono <?php echo $bal_badge; ?>">
                    <?php echo ($balance > 0 ? '₹' . number_format($balance, 2) . ' Payable' : ($balance < 0 ? '₹' . number_format(abs($balance), 2) . ' Overpaid' : '₹0.00 Clear')); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <a href="<?php echo site_url('finance/staff_statement/' . $l->staff_id); ?>"
                     onclick="event.stopPropagation()"
                     class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-purple-600/10 text-purple-700 text-body-xs font-semibold hover:bg-purple-600 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>Statement
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('staff-ledger-table');
    if (table && typeof $.fn.DataTable !== 'undefined') {
      $(table).DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']],
        language: {
          emptyTable: "No staff ledgers found.",
          search: "Quick search:",
          lengthMenu: "Show _MENU_ per page",
          info: "_START_ – _END_ of _TOTAL_ staff",
          infoEmpty: "No records",
          paginate: { next: 'Next →', previous: '← Prev' }
        },
        columnDefs: [{ orderable: false, targets: [0, 8] }]
      });
    }
  });
  </script>
