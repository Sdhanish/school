<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$student_id = $student->student_id ?? ($student->id ?? 0);
$entries = !empty($statement['entries']) ? $statement['entries'] : (!empty($statement['lines']) ? $statement['lines'] : []);
$closing_bal = (float)($statement['closing_balance'] ?? 0);
$opening_bal = (float)($statement['opening_balance'] ?? 0);
$total_debit = (float)($statement['total_debit'] ?? 0);
$total_credit = (float)($statement['total_credit'] ?? 0);
$ledger_code = $statement['ledger']->ledger_code ?? ($student->ledger_code ?? '—');
$class_display = $student->class_name ?? '';
if (!empty($student->division_name)) {
    $class_display .= ' (' . $student->division_name . ')';
}
?>

<style>
@media print {
  .no-print, nav, aside, header { display: none !important; }
  body { background: #fff !important; color: #000 !important; font-size: 12px !important; }
  .print-card { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>

    <!-- Header & Action Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 no-print">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <a href="<?php echo site_url('finance/ledger_students'); ?>" class="text-[13px] text-primary hover:underline flex items-center gap-1 font-semibold">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>Back to Student Ledger Directory
          </a>
          <span class="text-on-surface-variant/40">•</span>
          <span class="text-[13px] text-on-surface-variant">Student Statement</span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Accounting Statement</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Audit trail of fee invoices debited, payments credited, and running receivable balance.</p>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap shrink-0">
        <?php if (!empty($students_list)): ?>
          <!-- Quick Student Selector -->
          <div class="relative min-w-[210px]">
            <select onchange="if(this.value) window.location.href='<?php echo site_url('finance/student_statement/'); ?>' + this.value"
                    class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm text-on-surface focus:outline-none focus:border-primary shadow-2xs">
              <option value="">Switch Student...</option>
              <?php foreach ($students_list as $st_item): ?>
                <option value="<?php echo $st_item->student_id; ?>" <?php echo ((int)$st_item->student_id === (int)$student_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($st_item->first_name . ' ' . $st_item->last_name . ' (' . ($st_item->admission_number ?: 'ID:' . $st_item->student_id) . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <?php if ($closing_bal > 0): ?>
          <a href="<?php echo site_url('finance/fee_collection?student_id=' . $student_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add_card</span>Collect Fee
          </a>
        <?php endif; ?>

        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer shadow-2xs">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Statement
        </button>
      </div>
    </div>

    <!-- Student Profile & Ledger Header Card -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6 print-card">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-outline-variant/50 mb-4">
        <div class="flex items-center gap-3.5">
          <div class="w-14 h-14 rounded-full bg-primary/15 text-primary font-bold text-xl flex items-center justify-center shrink-0 border border-primary/20">
            <?php echo strtoupper(substr($student->first_name ?? 'S', 0, 1)); ?>
          </div>
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h3 class="font-headline-md text-title-lg font-bold text-on-surface">
                <?php echo html_escape(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')); ?>
              </h3>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-primary/10 text-primary border border-primary/20">
                <?php echo html_escape($ledger_code); ?>
              </span>
            </div>
            <div class="flex items-center gap-3 text-body-sm text-on-surface-variant mt-1 flex-wrap">
              <span class="font-mono">Admission #: <strong><?php echo html_escape($student->admission_number ?? '—'); ?></strong></span>
              <?php if (!empty($class_display)): ?>
                <span class="text-outline-variant">•</span>
                <span>Class: <strong><?php echo html_escape($class_display); ?></strong></span>
              <?php endif; ?>
              <?php if (!empty($student->guardian_name)): ?>
                <span class="text-outline-variant">•</span>
                <span>Guardian: <?php echo html_escape($student->guardian_name); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="sm:text-right">
          <span class="text-[11px] text-on-surface-variant uppercase tracking-wider block font-semibold">Net Balance Receivable</span>
          <div class="text-2xl font-bold font-mono mt-0.5 <?php echo ($closing_bal > 0) ? 'text-amber-700' : ($closing_bal < 0 ? 'text-emerald-700' : 'text-on-surface'); ?>">
            <?php
              if ($closing_bal > 0) {
                echo '₹' . number_format($closing_bal, 2) . ' <span class="text-sm font-semibold uppercase">Dr (Due)</span>';
              } elseif ($closing_bal < 0) {
                echo '₹' . number_format(abs($closing_bal), 2) . ' <span class="text-sm font-semibold uppercase">Cr (Advance)</span>';
              } else {
                echo '₹0.00 <span class="text-sm font-normal text-emerald-700">Clear</span>';
              }
            ?>
          </div>
        </div>
      </div>

      <!-- Financial Metric Tiles -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-body-md">
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant/30">
          <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider block">Opening Balance</span>
          <span class="font-bold font-mono text-base text-on-surface mt-0.5 block">₹<?php echo number_format($opening_bal, 2); ?></span>
          <span class="text-[11px] text-on-surface-variant">Brought forward</span>
        </div>
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant/30">
          <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider block">Total Debited (Invoiced)</span>
          <span class="font-bold font-mono text-base text-on-surface mt-0.5 block">₹<?php echo number_format($total_debit, 2); ?></span>
          <span class="text-[11px] text-primary font-medium">Fee charges / Debit</span>
        </div>
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant/30">
          <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider block">Total Credited (Paid)</span>
          <span class="font-bold font-mono text-base text-emerald-700 mt-0.5 block">₹<?php echo number_format($total_credit, 2); ?></span>
          <span class="text-[11px] text-emerald-600 font-medium">Receipts / Credit</span>
        </div>
        <div class="p-3.5 rounded-xl border <?php echo ($closing_bal > 0) ? 'bg-amber-50/70 border-amber-200' : 'bg-surface-container-low border-outline-variant/30'; ?>">
          <span class="text-[11px] font-semibold uppercase tracking-wider block <?php echo ($closing_bal > 0) ? 'text-amber-800' : 'text-on-surface-variant'; ?>">Current Outstanding</span>
          <span class="font-bold font-mono text-base mt-0.5 block <?php echo ($closing_bal > 0) ? 'text-amber-700' : 'text-emerald-700'; ?>">
            ₹<?php echo number_format($closing_bal, 2); ?>
          </span>
          <span class="text-[11px] <?php echo ($closing_bal > 0) ? 'text-amber-600' : 'text-emerald-600'; ?>">
            <?php echo ($closing_bal > 0) ? 'Pending payment' : 'No dues pending'; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Date Range Filter -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 no-print">
      <form method="get" action="<?php echo site_url('finance/student_statement/' . $student_id); ?>" class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-body-xs font-semibold text-on-surface uppercase tracking-wider">From:</label>
          <input type="date" name="from_date" value="<?php echo html_escape($from_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-body-xs font-semibold text-on-surface uppercase tracking-wider">To:</label>
          <input type="date" name="to_date" value="<?php echo html_escape($to_date ?? ''); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm"/>
        </div>
        <button type="submit" class="px-4 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">filter_alt</span> Filter
        </button>
        <a href="<?php echo site_url('finance/student_statement/' . $student_id); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">
          Reset
        </a>
        <?php if (!empty($from_date) || !empty($to_date)): ?>
          <span class="text-body-xs text-primary font-medium ml-2">
            Showing records from <?php echo html_escape($from_date ?: 'Start'); ?> to <?php echo html_escape($to_date ?: 'Present'); ?>
          </span>
        <?php endif; ?>
      </form>
    </div>

    <!-- Transaction Ledger Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 print-card">
      <div class="px-5 py-3 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/30">
        <span class="text-title-sm font-semibold text-on-surface flex items-center gap-1.5">
          <span class="material-symbols-outlined text-primary text-[18px]">receipt_long</span>
          Ledger Transactions
          <span class="text-body-xs font-normal text-on-surface-variant">(<?php echo count($entries); ?> entries)</span>
        </span>
        <div class="text-body-xs text-on-surface-variant flex items-center gap-3">
          <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Invoice = Debit</span>
          <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Payment = Credit</span>
        </div>
      </div>
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md" id="statement-table">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Txn / Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Transaction Type</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration / Particulars</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit (Invoice)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit (Payment)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Balance (Outstanding)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <!-- Opening Balance Row -->
            <tr class="bg-surface-container-low/30 italic">
              <td class="px-4 py-3 font-semibold text-on-surface">—</td>
              <td class="px-4 py-3 text-on-surface-variant font-mono text-[12px]">—</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant border border-outline-variant/40">
                  Opening Balance
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-[13px]">Balance brought forward</td>
              <td class="px-4 py-3 text-right font-mono text-on-surface-variant">—</td>
              <td class="px-4 py-3 text-right font-mono text-on-surface-variant">—</td>
              <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap <?php echo ($opening_bal > 0) ? 'text-amber-700' : 'text-on-surface'; ?>">
                ₹<?php echo number_format($opening_bal, 2); ?>
              </td>
            </tr>

            <?php if (!empty($entries)): ?>
              <?php foreach ($entries as $e): ?>
                <?php
                  $is_payment = in_array($e->transaction_type, ['Fee_Payment', 'Fee Payment', 'Payment', 'Credit']);
                  $is_invoice = in_array($e->transaction_type, ['Fee_Invoice', 'Fee Invoice', 'Invoice', 'Debit']);
                  $type_label = str_replace('_', ' ', $e->transaction_type);
                  $particulars = !empty($e->description) ? $e->description : (!empty($e->tx_desc) ? $e->tx_desc : ($e->narration ?? '—'));
                  $debit_val  = (float)$e->debit;
                  $credit_val = (float)$e->credit;
                  $bal_val    = (float)$e->running_balance;
                ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap text-[13px]"><?php echo date('d-M-Y', strtotime($e->transaction_date)); ?></td>
                  <td class="px-4 py-3 font-mono text-[12px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($e->transaction_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold inline-block
                      <?php echo $is_payment ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($is_invoice ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-surface-container-high text-on-surface-variant'); ?>">
                      <?php echo html_escape($type_label); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface">
                    <div class="font-medium text-[13px]"><?php echo html_escape($particulars); ?></div>
                    <?php if (!empty($e->reference_no)): ?>
                      <span class="inline-block text-[11px] font-mono text-on-surface-variant mt-0.5">Ref: <?php echo html_escape($e->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-on-surface font-semibold whitespace-nowrap">
                    <?php echo ($debit_val > 0) ? ('₹' . number_format($debit_val, 2)) : '<span class="text-on-surface-variant/40">—</span>'; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-emerald-700 font-semibold whitespace-nowrap">
                    <?php echo ($credit_val > 0) ? ('₹' . number_format($credit_val, 2)) : '<span class="text-on-surface-variant/40">—</span>'; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap">
                    <?php
                      if ($bal_val > 0) {
                        echo '<span class="text-amber-700">₹' . number_format($bal_val, 2) . ' Dr</span>';
                      } elseif ($bal_val < 0) {
                        echo '<span class="text-emerald-700">₹' . number_format(abs($bal_val), 2) . ' Cr</span>';
                      } else {
                        echo '<span class="text-on-surface-variant">₹0.00</span>';
                      }
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[36px] block mb-1 text-on-surface-variant/40">receipt_long</span>
                  No posted transactions found for this student in the selected period.
                </td>
              </tr>
            <?php endif; ?>

            <!-- Closing Totals Row -->
            <tr class="bg-surface-container-low/80 font-bold border-t-2 border-outline-variant">
              <td colspan="4" class="px-4 py-3 text-right text-on-surface uppercase text-body-sm tracking-wide">
                Period Totals & Outstanding Balance
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface text-[14px]">
                ₹<?php echo number_format($total_debit, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-emerald-700 text-[14px]">
                ₹<?php echo number_format($total_credit, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-[14px]">
                <span class="<?php echo ($closing_bal > 0) ? 'text-amber-700' : ($closing_bal < 0 ? 'text-emerald-700' : 'text-on-surface'); ?>">
                  <?php
                    if ($closing_bal > 0) {
                      echo '₹' . number_format($closing_bal, 2) . ' Dr';
                    } elseif ($closing_bal < 0) {
                      echo '₹' . number_format(abs($closing_bal), 2) . ' Cr';
                    } else {
                      echo '₹0.00';
                    }
                  ?>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
