<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Financial Reports & Statements</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit-ready statutory financial statements generated from the double-entry accounting ledger.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Statement
        </button>
      </div>
    </div>

    <!-- Report Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6">
      <a href="<?php echo site_url('finance/reports/trial_balance'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'trial_balance') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Trial Balance
      </a>
      <a href="<?php echo site_url('finance/reports/income_expense'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'income_expense') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Income & Expenditure
      </a>
      <a href="<?php echo site_url('finance/reports/balance_sheet'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'balance_sheet') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Balance Sheet
      </a>
      <a href="<?php echo site_url('finance/reports/day_book'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'day_book') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Day Book
      </a>
      <a href="<?php echo site_url('finance/reports/cash_book'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'cash_book') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Cash Book
      </a>
      <a href="<?php echo site_url('finance/reports/bank_book'); ?>" class="px-4 py-2 rounded-xl text-body-sm font-semibold transition-colors shrink-0 <?php echo ($report_type === 'bank_book') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/60 hover:bg-surface-container-high'; ?>">
        Bank Book
      </a>
    </div>

    <!-- Filter Form based on report type -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/reports/' . $report_type); ?>" class="flex flex-wrap items-center gap-3">
        <?php if ($report_type === 'trial_balance' || $report_type === 'balance_sheet'): ?>
          <div class="flex items-center gap-2">
            <label class="text-body-sm text-on-surface font-medium">As of Date:</label>
            <input type="date" name="as_of_date" value="<?php echo html_escape($as_of_date); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
          </div>
        <?php else: ?>
          <div class="flex items-center gap-2">
            <label class="text-body-sm text-on-surface font-medium">From:</label>
            <input type="date" name="from_date" value="<?php echo html_escape($from_date); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-body-sm text-on-surface font-medium">To:</label>
            <input type="date" name="to_date" value="<?php echo html_escape($to_date); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
          </div>
        <?php endif; ?>

        <?php if ($report_type === 'cash_book' || $report_type === 'bank_book'): ?>
          <div class="flex items-center gap-2">
            <label class="text-body-sm text-on-surface font-medium">Account:</label>
            <select name="account_id" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm">
              <?php foreach ($cash_bank_accounts as $cba): ?>
                <?php if (($report_type === 'cash_book' && $cba->account_type === 'Cash') || ($report_type === 'bank_book' && $cba->account_type === 'Bank')): ?>
                  <option value="<?php echo $cba->id; ?>" <?php echo ($cba->id == $account_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ')'); ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          Generate Statement
        </button>
      </form>
    </div>

    <!-- =======================================================================
         REPORT VIEW: 1. TRIAL BALANCE
         ======================================================================= -->
    <?php if ($report_type === 'trial_balance'): ?>
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 p-6">
        <div class="text-center pb-6 border-b border-outline-variant/50 mb-6">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Trial Balance</h3>
          <p class="text-body-sm text-on-surface-variant">As of <?php echo date('d-F-Y', strtotime($as_of_date)); ?></p>
        </div>

        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b-2 border-outline-variant/70 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Code</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Head</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Group Classification</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit Balance (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit Balance (₹)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($report_data['rows'])): ?>
              <?php foreach ($report_data['rows'] as $r): 
                $code = is_object($r) ? ($r->account_code ?? '') : ($r['account_code'] ?? '');
                $name = is_object($r) ? ($r->account_name ?? '') : ($r['account_name'] ?? '');
                $gname = is_object($r) ? ($r->group_name ?? '') : ($r['group_name'] ?? '');
                $gtype = is_object($r) ? ($r->category ?? $r->group_type ?? '') : ($r['group_type'] ?? $r['category'] ?? '');
                $deb = is_object($r) ? ($r->debit_amount ?? $r->debit ?? 0) : ($r['debit'] ?? $r['debit_amount'] ?? 0);
                $cred = is_object($r) ? ($r->credit_amount ?? $r->credit ?? 0) : ($r['credit'] ?? $r['credit_amount'] ?? 0);
              ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary text-sm"><?php echo html_escape($code); ?></td>
                  <td class="px-4 py-3 font-medium text-on-surface"><?php echo html_escape($name); ?></td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($gname . (!empty($gtype) ? ' (' . $gtype . ')' : '')); ?></td>
                  <td class="px-4 py-3 text-right font-mono text-on-surface">
                    <?php echo ($deb > 0) ? ('₹' . number_format((float)$deb, 2)) : '—'; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-on-surface">
                    <?php echo ($cred > 0) ? ('₹' . number_format((float)$cred, 2)) : '—'; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No account balances found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr class="bg-surface-container-low font-bold border-t-2 border-outline-variant">
              <td colspan="3" class="px-4 py-3.5 text-right uppercase text-on-surface">Total:</td>
              <td class="px-4 py-3.5 text-right font-mono text-title-sm text-on-surface">₹<?php echo number_format($report_data['total_debit'] ?? 0, 2); ?></td>
              <td class="px-4 py-3.5 text-right font-mono text-title-sm text-on-surface">₹<?php echo number_format($report_data['total_credit'] ?? 0, 2); ?></td>
            </tr>
            <tr>
              <td colspan="5" class="px-4 py-3 text-center text-body-sm <?php echo !empty($report_data['is_balanced']) ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container'; ?>">
                <?php if (!empty($report_data['is_balanced'])): ?>
                  <strong>✓ Trial Balance is perfectly balanced!</strong> (Total Debits = Total Credits)
                <?php else: ?>
                  <strong>⚠ Warning: Trial Balance is out of balance by ₹<?php echo number_format($report_data['difference'] ?? abs(($report_data['total_debit'] ?? 0) - ($report_data['total_credit'] ?? 0)), 2); ?>!</strong>
                <?php endif; ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

    <!-- =======================================================================
         REPORT VIEW: 2. INCOME & EXPENDITURE
         ======================================================================= -->
    <?php elseif ($report_type === 'income_expense'): ?>
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 p-6">
        <div class="text-center pb-6 border-b border-outline-variant/50 mb-6">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Statement of Income & Expenditure</h3>
          <p class="text-body-sm text-on-surface-variant">Period: <?php echo date('d-M-Y', strtotime($from_date)); ?> to <?php echo date('d-M-Y', strtotime($to_date)); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Income Column -->
          <div class="border border-outline-variant/60 rounded-xl p-4">
            <h4 class="font-semibold text-title-sm text-emerald-800 pb-2 border-b border-outline-variant/40 mb-3 flex items-center justify-between">
              <span>Operating Revenues (Income)</span>
              <span class="material-symbols-outlined">trending_up</span>
            </h4>
            <div class="space-y-2 mb-4">
              <?php if (!empty($report_data['incomes'])): ?>
                <?php foreach ($report_data['incomes'] as $inc): ?>
                  <div class="flex items-center justify-between text-body-sm py-1 border-b border-outline-variant/20">
                    <span class="text-on-surface"><?php echo html_escape($inc['name']); ?></span>
                    <span class="font-mono font-medium text-on-surface">₹<?php echo number_format($inc['balance'], 2); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-body-sm text-on-surface-variant italic py-2">No income entries recorded.</p>
              <?php endif; ?>
            </div>
            <div class="pt-3 border-t-2 border-outline-variant/60 flex items-center justify-between font-bold text-body-md text-emerald-800">
              <span>Total Revenue:</span>
              <span class="font-mono">₹<?php echo number_format($report_data['total_income'], 2); ?></span>
            </div>
          </div>

          <!-- Expense Column -->
          <div class="border border-outline-variant/60 rounded-xl p-4">
            <h4 class="font-semibold text-title-sm text-rose-800 pb-2 border-b border-outline-variant/40 mb-3 flex items-center justify-between">
              <span>Operating Expenditures (Expenses)</span>
              <span class="material-symbols-outlined">trending_down</span>
            </h4>
            <div class="space-y-2 mb-4">
              <?php if (!empty($report_data['expenses'])): ?>
                <?php foreach ($report_data['expenses'] as $exp): ?>
                  <div class="flex items-center justify-between text-body-sm py-1 border-b border-outline-variant/20">
                    <span class="text-on-surface"><?php echo html_escape($exp['name']); ?></span>
                    <span class="font-mono font-medium text-on-surface">₹<?php echo number_format($exp['balance'], 2); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-body-sm text-on-surface-variant italic py-2">No expense entries recorded.</p>
              <?php endif; ?>
            </div>
            <div class="pt-3 border-t-2 border-outline-variant/60 flex items-center justify-between font-bold text-body-md text-rose-800">
              <span>Total Expenditure:</span>
              <span class="font-mono">₹<?php echo number_format($report_data['total_expense'], 2); ?></span>
            </div>
          </div>
        </div>

        <!-- Net Result Banner -->
        <div class="mt-8 p-4 rounded-xl border <?php echo ($report_data['net_profit'] >= 0) ? 'bg-secondary-container/40 border-secondary/30 text-on-secondary-container' : 'bg-error-container/40 border-error/30 text-on-error-container'; ?> flex items-center justify-between">
          <div>
            <span class="text-body-sm uppercase tracking-wider block font-semibold">Net Operating Surplus / (Deficit)</span>
            <span class="text-xs text-on-surface-variant">Institutional surplus transferred to General Fund / Equity.</span>
          </div>
          <span class="text-2xl font-bold font-mono">
            ₹<?php echo number_format($report_data['net_profit'], 2); ?>
          </span>
        </div>
      </div>

    <!-- =======================================================================
         REPORT VIEW: 3. BALANCE SHEET
         ======================================================================= -->
    <?php elseif ($report_type === 'balance_sheet'): ?>
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 p-6">
        <div class="text-center pb-6 border-b border-outline-variant/50 mb-6">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Balance Sheet (Financial Position)</h3>
          <p class="text-body-sm text-on-surface-variant">As of <?php echo date('d-F-Y', strtotime($as_of_date)); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Assets Column -->
          <div class="border border-outline-variant/60 rounded-xl p-4 flex flex-col justify-between">
            <div>
              <h4 class="font-semibold text-title-sm text-blue-800 pb-2 border-b border-outline-variant/40 mb-3">
                Assets (Application of Funds)
              </h4>
              <div class="space-y-2 mb-4">
                <?php if (!empty($report_data['assets'])): ?>
                  <?php foreach ($report_data['assets'] as $a): ?>
                    <div class="flex items-center justify-between text-body-sm py-1 border-b border-outline-variant/20">
                      <span class="text-on-surface"><?php echo html_escape($a['name']); ?></span>
                      <span class="font-mono font-medium text-on-surface">₹<?php echo number_format($a['balance'], 2); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="text-body-sm text-on-surface-variant italic py-2">No asset balances found.</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="pt-3 border-t-2 border-outline-variant/60 flex items-center justify-between font-bold text-body-md text-blue-800">
              <span>Total Assets:</span>
              <span class="font-mono">₹<?php echo number_format($report_data['total_assets'], 2); ?></span>
            </div>
          </div>

          <!-- Liabilities & Equity Column -->
          <div class="border border-outline-variant/60 rounded-xl p-4 flex flex-col justify-between">
            <div>
              <h4 class="font-semibold text-title-sm text-purple-800 pb-2 border-b border-outline-variant/40 mb-3">
                Liabilities & Capital Funds (Source of Funds)
              </h4>
              <div class="space-y-2 mb-4">
                <span class="text-body-xs font-semibold uppercase tracking-wider text-on-surface-variant block">Liabilities:</span>
                <?php if (!empty($report_data['liabilities'])): ?>
                  <?php foreach ($report_data['liabilities'] as $l): ?>
                    <div class="flex items-center justify-between text-body-sm py-1 border-b border-outline-variant/20">
                      <span class="text-on-surface"><?php echo html_escape($l['name']); ?></span>
                      <span class="font-mono font-medium text-on-surface">₹<?php echo number_format($l['balance'], 2); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>

                <span class="text-body-xs font-semibold uppercase tracking-wider text-on-surface-variant block pt-2">Institutional Capital & Reserves:</span>
                <?php if (!empty($report_data['equity'])): ?>
                  <?php foreach ($report_data['equity'] as $e): ?>
                    <div class="flex items-center justify-between text-body-sm py-1 border-b border-outline-variant/20">
                      <span class="text-on-surface"><?php echo html_escape($e['name']); ?></span>
                      <span class="font-mono font-medium text-on-surface">₹<?php echo number_format($e['balance'], 2); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="pt-3 border-t-2 border-outline-variant/60 flex items-center justify-between font-bold text-body-md text-purple-800">
              <span>Total Liabilities & Equity:</span>
              <span class="font-mono">₹<?php echo number_format($report_data['total_liabilities_and_equity'], 2); ?></span>
            </div>
          </div>
        </div>
      </div>

    <!-- =======================================================================
         REPORT VIEW: 4. DAY BOOK
         ======================================================================= -->
    <?php elseif ($report_type === 'day_book'): ?>
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
          <div>
            <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Accounting Day Book</h3>
            <p class="text-body-sm text-on-surface-variant mt-0.5">Chronological record of all debits and credits posted between <?php echo date('d-M-Y', strtotime($from_date)); ?> and <?php echo date('d-M-Y', strtotime($to_date)); ?>.</p>
          </div>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Head</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Sub-Ledger</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration</th>
                <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit (₹)</th>
                <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit (₹)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (!empty($report_data)): ?>
                <?php $total_d = 0; $total_c = 0; ?>
                <?php foreach ($report_data as $row): ?>
                  <?php $total_d += $row->debit; $total_c += $row->credit; ?>
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                    <td class="px-4 py-2.5 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($row->transaction_date)); ?></td>
                    <td class="px-4 py-2.5 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($row->transaction_number); ?></td>
                    <td class="px-4 py-2.5 font-medium text-on-surface whitespace-nowrap">
                      <?php echo html_escape($row->account_name); ?>
                      <span class="block text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($row->account_code); ?></span>
                    </td>
                    <td class="px-4 py-2.5 text-on-surface text-sm whitespace-nowrap"><?php echo html_escape($row->ledger_name ?: '—'); ?></td>
                    <td class="px-4 py-2.5 text-on-surface text-sm"><?php echo html_escape($row->description ?: $row->narration); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono text-on-surface whitespace-nowrap">
                      <?php echo ($row->debit > 0) ? ('₹' . number_format($row->debit, 2)) : '—'; ?>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-on-surface whitespace-nowrap">
                      <?php echo ($row->credit > 0) ? ('₹' . number_format($row->credit, 2)) : '—'; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr class="bg-surface-container-low font-bold border-t-2 border-outline-variant">
                  <td colspan="5" class="px-4 py-3 text-right uppercase text-on-surface">Day Book Totals:</td>
                  <td class="px-4 py-3 text-right font-mono text-title-sm text-on-surface">₹<?php echo number_format($total_d, 2); ?></td>
                  <td class="px-4 py-3 text-right font-mono text-title-sm text-on-surface">₹<?php echo number_format($total_c, 2); ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No transactions found for the period.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <!-- =======================================================================
         REPORT VIEW: 5 & 6. CASH BOOK & BANK BOOK
         ======================================================================= -->
    <?php elseif ($report_type === 'cash_book' || $report_type === 'bank_book'): ?>
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 p-6">
        <div class="text-center pb-6 border-b border-outline-variant/50 mb-6">
          <h3 class="font-headline-md text-title-lg font-bold text-on-surface">
            <?php echo ($report_type === 'cash_book') ? 'Cash Book' : 'Bank Book'; ?>
          </h3>
          <p class="text-body-sm text-on-surface-variant">
            Account: <strong><?php echo html_escape($report_data['account']->account_name ?? 'Primary Account'); ?></strong>
            | Period: <?php echo date('d-M-Y', strtotime($from_date)); ?> to <?php echo date('d-M-Y', strtotime($to_date)); ?>
          </p>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
                <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Particulars / Narration</th>
                <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Receipts (Debit ₹)</th>
                <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Payments (Credit ₹)</th>
                <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Running Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <!-- Opening Balance -->
              <tr class="bg-surface-container-low/30 italic">
                <td class="px-4 py-2.5 font-semibold text-on-surface">—</td>
                <td class="px-4 py-2.5 text-on-surface-variant">—</td>
                <td class="px-4 py-2.5 text-on-surface-variant font-semibold">Opening Balance B/F</td>
                <td class="px-4 py-2.5 text-right font-mono">—</td>
                <td class="px-4 py-2.5 text-right font-mono">—</td>
                <td class="px-4 py-2.5 text-right font-mono font-bold text-on-surface">₹<?php echo number_format($report_data['opening_balance'], 2); ?></td>
              </tr>

              <?php if (!empty($report_data['entries'])): ?>
                <?php foreach ($report_data['entries'] as $e): ?>
                  <tr class="hover:bg-surface-container-low/30 transition-colors">
                    <td class="px-4 py-2.5 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($e->transaction_date)); ?></td>
                    <td class="px-4 py-2.5 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($e->transaction_number); ?></td>
                    <td class="px-4 py-2.5 text-on-surface text-sm">
                      <?php echo html_escape($e->description ?: $e->narration); ?>
                      <?php if (!empty($e->reference_no)): ?>
                        <span class="block text-[11px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($e->reference_no); ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-emerald-700 font-semibold whitespace-nowrap">
                      <?php echo ($e->debit > 0) ? ('₹' . number_format($e->debit, 2)) : '—'; ?>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-rose-700 font-semibold whitespace-nowrap">
                      <?php echo ($e->credit > 0) ? ('₹' . number_format($e->credit, 2)) : '—'; ?>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                      ₹<?php echo number_format($e->running_balance, 2); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>

              <!-- Closing Balance -->
              <tr class="bg-surface-container-low/80 font-bold border-t-2 border-outline-variant">
                <td colspan="3" class="px-4 py-3 text-right uppercase text-on-surface">Closing Book Balance:</td>
                <td class="px-4 py-3 text-right font-mono text-emerald-700">₹<?php echo number_format($report_data['total_debit'], 2); ?></td>
                <td class="px-4 py-3 text-right font-mono text-rose-700">₹<?php echo number_format($report_data['total_credit'], 2); ?></td>
                <td class="px-4 py-3 text-right font-mono text-title-sm text-primary">₹<?php echo number_format($report_data['closing_balance'], 2); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
