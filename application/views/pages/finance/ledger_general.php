<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
        <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-semibold">General Ledger</span>
      </nav>
      <h2 class="font-headline-md text-headline-md text-on-surface">General Ledger Statement</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Full chronological posting register for any Chart of Accounts head with running balance.</p>
    </div>
    <?php if ($selected_account): ?>
    <div class="flex items-center gap-2 shrink-0">
      <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">print</span>Print
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Account Selector & Date Range -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-6">
    <form method="get" action="<?php echo site_url('finance/ledger_general'); ?>" class="flex flex-wrap items-end gap-4">
      <div class="flex-1 min-w-[220px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1.5 uppercase tracking-wide">Select Account Head</label>
        <select name="account_id" id="account-select"
                class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:outline-none focus:border-primary">
          <option value="">— Choose an Account —</option>
          <?php
          $categories_seen = [];
          foreach ($all_accounts as $acc):
            $cat = $acc->group_category ?? 'Other';
            if (!in_array($cat, $categories_seen)):
              if (!empty($categories_seen)) echo '</optgroup>';
              echo '<optgroup label="' . htmlspecialchars($cat) . '">';
              $categories_seen[] = $cat;
            endif;
          ?>
            <option value="<?php echo $acc->id; ?>" <?php echo ($account_id == $acc->id) ? 'selected' : ''; ?>>
              <?php echo html_escape('[' . $acc->account_code . '] ' . $acc->account_name); ?>
            </option>
          <?php endforeach; ?>
          <?php if (!empty($categories_seen)) echo '</optgroup>'; ?>
        </select>
      </div>
      <div>
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1.5 uppercase tracking-wide">From</label>
        <input type="date" name="from_date" value="<?php echo html_escape($from_date ?? ''); ?>"
               class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      <div>
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1.5 uppercase tracking-wide">To</label>
        <input type="date" name="to_date" value="<?php echo html_escape($to_date ?? ''); ?>"
               class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-on-primary font-semibold text-body-md hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">filter_alt</span> View Ledger
        </button>
        <?php if ($account_id): ?>
        <a href="<?php echo site_url('finance/ledger_general?account_id=' . $account_id); ?>"
           class="px-3 py-2.5 rounded-lg border border-outline-variant text-on-surface text-body-sm hover:bg-surface-container transition-colors">
          Reset Dates
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if (!$statement && !$account_id): ?>
  <!-- Empty State: No account selected -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-12 text-center">
    <span class="material-symbols-outlined text-[56px] text-on-surface-variant/40 block mb-4">account_tree</span>
    <h3 class="text-title-md font-bold text-on-surface mb-2">Select an Account to View Ledger</h3>
    <p class="text-body-md text-on-surface-variant max-w-sm mx-auto">
      Choose any account head from the dropdown above to view its full chronological posting history with running balances.
    </p>
  </div>
  <?php elseif ($account_id && !$statement): ?>
  <!-- Account not found -->
  <div class="elevation-1 rounded-2xl bg-error/5 border border-error/20 p-8 text-center">
    <span class="material-symbols-outlined text-error text-[40px] block mb-2">error</span>
    <p class="text-on-surface font-semibold">Account not found or you don't have access to it.</p>
  </div>
  <?php else: ?>
  <?php
    $acc = $statement['account'];
    $lines = $statement['lines'];
    $is_debit_normal = $statement['is_debit_normal'];
    $opening = $statement['opening_balance'];
    $closing = $statement['closing_balance'];
    $total_debit = $statement['total_debit'];
    $total_credit = $statement['total_credit'];
  ?>

  <!-- Account Info Card -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <span class="font-mono text-body-sm font-bold text-primary bg-primary/10 px-2.5 py-0.5 rounded-lg"><?php echo html_escape($acc->account_code); ?></span>
          <span class="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant"><?php echo html_escape($acc->group_category ?? '—'); ?></span>
        </div>
        <h3 class="text-title-lg font-bold text-on-surface"><?php echo html_escape($acc->account_name); ?></h3>
        <p class="text-body-sm text-on-surface-variant"><?php echo html_escape($acc->group_name ?? '—'); ?> · <?php echo html_escape($acc->account_type ?? 'General'); ?></p>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
        <div class="p-3 rounded-xl bg-surface-container-low">
          <div class="text-[11px] text-on-surface-variant uppercase tracking-wide font-semibold mb-0.5">Opening Bal</div>
          <div class="font-mono font-bold text-on-surface text-[15px]">₹<?php echo number_format(abs($opening), 2); ?></div>
        </div>
        <div class="p-3 rounded-xl bg-surface-container-low">
          <div class="text-[11px] text-on-surface-variant uppercase tracking-wide font-semibold mb-0.5">Total Debit</div>
          <div class="font-mono font-bold text-on-surface text-[15px]">₹<?php echo number_format($total_debit, 2); ?></div>
        </div>
        <div class="p-3 rounded-xl bg-surface-container-low">
          <div class="text-[11px] text-on-surface-variant uppercase tracking-wide font-semibold mb-0.5">Total Credit</div>
          <div class="font-mono font-bold text-on-surface text-[15px]">₹<?php echo number_format($total_credit, 2); ?></div>
        </div>
        <div class="p-3 rounded-xl <?php echo $closing >= 0 ? 'bg-emerald-50 border border-emerald-200/50' : 'bg-amber-50 border border-amber-200/50'; ?>">
          <div class="text-[11px] text-on-surface-variant uppercase tracking-wide font-semibold mb-0.5">Closing Bal</div>
          <div class="font-mono font-bold text-[15px] <?php echo $closing >= 0 ? 'text-emerald-700' : 'text-amber-700'; ?>">
            ₹<?php echo number_format(abs($closing), 2); ?>
            <span class="text-[11px] font-normal"><?php echo ($is_debit_normal ? ($closing >= 0 ? 'Dr' : 'Cr') : ($closing >= 0 ? 'Cr' : 'Dr')); ?></span>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-3 pt-3 border-t border-outline-variant/40 text-body-xs text-on-surface-variant">
      Period: <span class="font-semibold"><?php echo date('d M Y', strtotime($from_date)); ?></span> to
      <span class="font-semibold"><?php echo date('d M Y', strtotime($to_date)); ?></span>
      &nbsp;·&nbsp; Normal Balance: <span class="font-semibold"><?php echo $is_debit_normal ? 'Debit' : 'Credit'; ?></span>
    </div>
  </div>

  <!-- General Ledger Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="table-scroll overflow-x-auto">
      <table class="w-full border-collapse text-body-md">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher No.</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Particulars / Narration</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Sub-Ledger</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Running Balance</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <!-- Opening Balance Row -->
          <tr class="bg-surface-container-low/30 italic">
            <td class="px-4 py-3 font-semibold text-on-surface">—</td>
            <td class="px-4 py-3 text-on-surface-variant">—</td>
            <td class="px-4 py-3 font-semibold text-on-surface-variant">Opening Balance</td>
            <td class="px-4 py-3 text-on-surface-variant">Balance brought forward</td>
            <td class="px-4 py-3 text-on-surface-variant">—</td>
            <td class="px-4 py-3 text-right font-mono">—</td>
            <td class="px-4 py-3 text-right font-mono">—</td>
            <td class="px-4 py-3 text-right font-mono font-bold text-on-surface">
              ₹<?php echo number_format(abs($opening), 2); ?>
              <span class="text-[10px] font-normal text-on-surface-variant"><?php echo ($is_debit_normal ? ($opening >= 0 ? 'Dr' : 'Cr') : ($opening >= 0 ? 'Cr' : 'Dr')); ?></span>
            </td>
          </tr>

          <?php if (empty($lines)): ?>
            <tr>
              <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">
                No transactions found for this account in the selected period.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($lines as $line): ?>
              <?php
                $txn_type = $line->transaction_type ?? '';
                $type_color = 'bg-surface-container text-on-surface-variant';
                if (in_array($txn_type, ['Fee_Payment', 'Fee_Invoice'])) $type_color = 'bg-blue-100 text-blue-800';
                elseif (in_array($txn_type, ['Expense', 'Staff_Payout'])) $type_color = 'bg-red-100 text-red-800';
                elseif ($txn_type === 'Transfer') $type_color = 'bg-yellow-100 text-yellow-800';
                elseif ($txn_type === 'Journal_Entry') $type_color = 'bg-purple-100 text-purple-800';
                $running = $line->running_balance;
                $running_dir = $is_debit_normal ? ($running >= 0 ? 'Dr' : 'Cr') : ($running >= 0 ? 'Cr' : 'Dr');
                $running_color = ($running_dir === 'Dr' && $is_debit_normal) || ($running_dir === 'Cr' && !$is_debit_normal) ? 'text-on-surface' : 'text-amber-700';
              ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors">
                <td class="px-4 py-3 text-on-surface whitespace-nowrap text-[13px]"><?php echo date('d-M-Y', strtotime($line->transaction_date)); ?></td>
                <td class="px-4 py-3 font-mono text-[12px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($line->transaction_number ?? '—'); ?></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo $type_color; ?>">
                    <?php echo str_replace('_', ' ', html_escape($txn_type)); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface text-[13px]">
                  <?php echo html_escape($line->description ?: ($line->tx_desc ?? '—')); ?>
                  <?php if (!empty($line->created_by_name)): ?>
                    <span class="block text-[11px] text-on-surface-variant">by <?php echo html_escape($line->created_by_name); ?></span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-[12px] text-on-surface-variant">
                  <?php if (!empty($line->ledger_name)): ?>
                    <span class="font-mono text-primary"><?php echo html_escape($line->ledger_name); ?></span>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface whitespace-nowrap">
                  <?php echo ((float)$line->debit > 0) ? '₹' . number_format((float)$line->debit, 2) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-secondary font-semibold whitespace-nowrap">
                  <?php echo ((float)$line->credit > 0) ? '₹' . number_format((float)$line->credit, 2) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap <?php echo $running_color; ?>">
                  ₹<?php echo number_format(abs($running), 2); ?>
                  <span class="text-[10px] font-normal text-on-surface-variant"><?php echo $running_dir; ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Closing Totals -->
          <tr class="bg-surface-container-low/80 font-bold border-t-2 border-outline-variant">
            <td colspan="4" class="px-4 py-3 text-right text-on-surface uppercase text-body-sm">
              Period Totals & Closing Balance
            </td>
            <td class="px-4 py-3"></td>
            <td class="px-4 py-3 text-right font-mono text-on-surface">₹<?php echo number_format($total_debit, 2); ?></td>
            <td class="px-4 py-3 text-right font-mono text-secondary">₹<?php echo number_format($total_credit, 2); ?></td>
            <td class="px-4 py-3 text-right font-mono text-primary text-title-sm">
              ₹<?php echo number_format(abs($closing), 2); ?>
              <span class="text-[11px] font-normal text-on-surface-variant">
                <?php echo $is_debit_normal ? ($closing >= 0 ? 'Dr' : 'Cr') : ($closing >= 0 ? 'Cr' : 'Dr'); ?>
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
