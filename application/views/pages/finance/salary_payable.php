<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
      <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-on-surface-variant">Staff Finance</span>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-primary font-semibold">Salary Payable</span>
    </nav>
    <h2 class="font-headline-md text-headline-md text-on-surface">Staff Salary Payable Liability</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Accrued monthly payroll liabilities, double-entry vouchers (DR 5010 / CR 2010), and staff-wise payable registers.</p>
  </div>
  <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
    <a href="<?php echo site_url('finance/staff_payouts?action=pay'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg bg-emerald-600 text-white text-label-md font-semibold hover:bg-emerald-700 transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[18px]">payments</span>Pay Salary
    </a>
    <a href="<?php echo site_url('finance/salary_processing'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[18px]">calculate</span>Monthly Processing
    </a>
    <a href="<?php echo site_url('finance/ledger_staff'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[18px]">menu_book</span>Staff Ledgers
    </a>
  </div>
</div>

<!-- Accounting Entry Rules Highlight Banner -->
<div class="mb-6 p-4 rounded-2xl bg-surface-container-low border border-outline-variant/60 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
      <span class="material-symbols-outlined text-[24px]">receipt_long</span>
    </div>
    <div>
      <div class="text-[11px] font-semibold text-on-surface uppercase tracking-wider">Payroll Accrual Entry (Phase 10)</div>
      <div class="text-xs font-medium text-on-surface mt-0.5 flex flex-wrap items-center gap-1.5">
        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-900 font-mono text-[11px] font-bold">DR 5010 (Staff Salary Expense)</span>
        <span class="text-on-surface-variant font-bold">=</span>
        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-mono text-[11px] font-bold">CR 2010 (Staff Salary Payable)</span>
      </div>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
      <span class="material-symbols-outlined text-[24px]">payments</span>
    </div>
    <div>
      <div class="text-[11px] font-semibold text-on-surface uppercase tracking-wider">Salary Payment Entry (Phase 11)</div>
      <div class="text-xs font-medium text-on-surface mt-0.5 flex flex-wrap items-center gap-1.5">
        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-mono text-[11px] font-bold">DR 2010 (Staff Salary Payable)</span>
        <span class="text-on-surface-variant font-bold">=</span>
        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 font-mono text-[11px] font-bold">CR 1010/1020 (Bank/Cash)</span>
      </div>
    </div>
  </div>
</div>

<!-- Financial KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="p-5 rounded-2xl bg-amber-50/80 border border-amber-200/70 elevation-1">
    <div class="flex items-center gap-2.5 mb-1.5">
      <span class="material-symbols-outlined text-amber-700 text-[22px]">pending_actions</span>
      <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Total Accrued Payable</span>
    </div>
    <div class="text-3xl font-bold font-mono text-amber-700">₹<?php echo number_format($total_payable, 2); ?></div>
    <div class="text-body-xs text-amber-600 mt-1">Pending salary liability across all approved batches</div>
  </div>

  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
    <div class="flex items-center gap-2.5 mb-1.5">
      <span class="material-symbols-outlined text-primary text-[22px]">groups</span>
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Payable Beneficiaries</span>
    </div>
    <div class="text-3xl font-bold font-mono text-on-surface"><?php echo $staff_count; ?></div>
    <div class="text-body-xs text-on-surface-variant mt-1">Staff members with outstanding payroll credit</div>
  </div>

  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
    <div class="flex items-center gap-2.5 mb-1.5">
      <span class="material-symbols-outlined text-secondary text-[22px]">verified</span>
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Approved Payroll Batches</span>
    </div>
    <div class="text-3xl font-bold font-mono text-on-surface"><?php echo count($batches); ?></div>
    <div class="text-body-xs text-on-surface-variant mt-1">Confirmed monthly periods posted to GL</div>
  </div>
</div>

<!-- Approved Batches Table (Accounting Accruals) -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/30">
    <div>
      <h3 class="font-headline-md text-title-md font-bold text-on-surface">Approved Monthly Payroll Batches</h3>
      <p class="text-body-xs text-on-surface-variant">Confirmed payroll batches with double-entry journal vouchers posted.</p>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md" id="batches-table">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Batch Number</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Period</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Staff Count</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Gross Payroll</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Deductions</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Payable</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Journal Voucher</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($batches)): ?>
          <?php $bIdx = 1; foreach ($batches as $b): ?>
            <?php $isSelected = ($selected_batch_id == $b->id); ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors <?php echo $isSelected ? 'bg-primary/5' : ''; ?>">
              <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $bIdx++; ?></td>
              <td class="px-4 py-3 font-mono font-bold text-primary text-[13px]">
                <?php echo html_escape($b->batch_number); ?>
              </td>
              <td class="px-4 py-3 font-semibold text-on-surface text-[13px]">
                <?php echo date('F Y', mktime(0, 0, 0, $b->payroll_month, 10, $b->payroll_year)); ?>
              </td>
              <td class="px-4 py-3 text-center text-[13px]">
                <?php echo $b->total_staff; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface">
                ₹<?php echo number_format((float)$b->gross_amount, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-[13px] text-amber-700">
                -₹<?php echo number_format((float)$b->statutory_deductions + (float)$b->attendance_deductions, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-[13px] font-bold text-emerald-700">
                ₹<?php echo number_format((float)$b->net_amount, 2); ?>
              </td>
              <td class="px-4 py-3 text-center font-mono text-xs">
                <?php if (!empty($b->transaction_number)): ?>
                  <span class="px-2 py-0.5 rounded bg-surface-container-high text-primary border border-outline-variant font-semibold">
                    <?php echo html_escape($b->transaction_number); ?>
                  </span>
                <?php else: ?>
                  <span class="text-on-surface-variant italic">Pending</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  <?php echo html_escape($b->status); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <a href="<?php echo site_url('finance/salary_payable?batch_id=' . $b->id); ?>" class="p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-block" title="Filter Staff Payable by Batch">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                  </a>
                  <a href="<?php echo site_url('finance/salary_processing?batch_id=' . $b->id); ?>" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors inline-block" title="View Batch Register">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                  </a>
                  <?php if ($b->status !== 'Paid'): ?>
                    <a href="<?php echo site_url('finance/staff_payouts?batch_id=' . $b->id . '&action=pay'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors shadow-2xs" title="Pay Batch Payroll">
                      <span class="material-symbols-outlined text-[14px]">payments</span>Pay
                    </a>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-secondary-container text-on-secondary-container">Paid</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Staff-Wise Salary Payable Breakdown Register -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-container-low/30">
    <div>
      <h3 class="font-headline-md text-title-md font-bold text-on-surface">Staff-Wise Salary Payable Ledger</h3>
      <p class="text-body-xs text-on-surface-variant">
        <?php if ($selected_batch): ?>
          Showing liabilities for Batch: <strong class="font-mono text-primary"><?php echo $selected_batch->batch_number; ?></strong> (<?php echo date('F Y', mktime(0, 0, 0, $selected_batch->payroll_month, 10, $selected_batch->payroll_year)); ?>)
          <a href="<?php echo site_url('finance/salary_payable'); ?>" class="ml-2 text-primary text-xs underline font-normal">Show All Batches</a>
        <?php else: ?>
          Aggregated payable liability across all confirmed payroll periods.
        <?php endif; ?>
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-on-surface-variant font-medium">Account Head:</span>
      <span class="px-2.5 py-1 rounded-lg bg-surface-container-high border border-outline-variant/60 font-mono text-xs font-bold text-on-surface">
        2010 — Staff Payable
      </span>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md" id="staff-payable-table">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Employee Name</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Staff Code</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Designation</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Accrued Periods</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Payable Balance (CR)</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Ledger Statement</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($staff_payables)): ?>
          <?php $sIdx = 1; foreach ($staff_payables as $sp): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors">
              <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $sIdx++; ?></td>
              <td class="px-4 py-3 font-semibold text-on-surface text-[13px]">
                <?php echo html_escape($sp->full_name); ?>
              </td>
              <td class="px-4 py-3 font-mono text-on-surface-variant text-[12px]">
                <?php echo html_escape($sp->employee_code ?: 'ID:' . $sp->staff_id); ?>
              </td>
              <td class="px-4 py-3 text-[13px] text-on-surface">
                <?php echo html_escape($sp->designation_name ?: 'Staff'); ?>
                <span class="block text-[10px] text-on-surface-variant uppercase"><?php echo $sp->staff_type; ?></span>
              </td>
              <td class="px-4 py-3 text-center text-[12px]">
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-mono text-on-surface font-semibold">
                  <?php echo (int)$sp->months_accrued; ?> month(s)
                </span>
              </td>
              <td class="px-4 py-3 text-right font-mono font-bold text-amber-800 text-[14px]">
                ₹<?php echo number_format((float)$sp->total_payable_accrued, 2); ?>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                  Accrued Payable
                </span>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <a href="<?php echo site_url('finance/staff_payouts?staff_id=' . $sp->staff_id . ($selected_batch_id ? '&batch_id=' . $selected_batch_id : '') . '&action=pay'); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors shadow-2xs" title="Pay Salary to Staff Member">
                    <span class="material-symbols-outlined text-[14px]">payments</span>Pay Salary
                  </a>
                  <a href="<?php echo site_url('finance/staff_statement/' . $sp->staff_id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-high text-on-surface text-xs font-semibold hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-[14px]">receipt</span>Statement
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($staff_payables)): ?>
        <tfoot class="border-t-2 border-outline-variant/60 bg-surface-container-low/40 font-bold">
          <tr>
            <td colspan="5" class="px-4 py-3 text-right text-xs uppercase tracking-wider text-on-surface">Total Staff Salary Payable:</td>
            <td class="px-4 py-3 text-right font-mono text-amber-800 text-base">₹<?php echo number_format($total_payable, 2); ?></td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var bTable = document.getElementById('batches-table');
  if (bTable && typeof $.fn.DataTable !== 'undefined') {
    $(bTable).DataTable({
      responsive: true,
      pageLength: 10,
      order: [[2, 'desc']],
      columnDefs: [{ orderable: false, targets: [0, 9] }],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search batches...",
        emptyTable: "No approved payroll batches found. Approve a monthly payroll under Salary Processing to generate payable accruals."
      }
    });
  }

  var sTable = document.getElementById('staff-payable-table');
  if (sTable && typeof $.fn.DataTable !== 'undefined') {
    $(sTable).DataTable({
      responsive: true,
      pageLength: 25,
      order: [[1, 'asc']],
      columnDefs: [{ orderable: false, targets: [0, 7] }],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search staff payables...",
        emptyTable: "No outstanding salary payable balances recorded."
      }
    });
  }
});
</script>
