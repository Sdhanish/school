<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
      <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-on-surface-variant">Staff Finance</span>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-primary font-semibold">Salary Processing</span>
    </nav>
    <h2 class="font-headline-md text-headline-md text-on-surface">Monthly Staff Salary Processing</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Calculate monthly payroll, attendance adjustments, statutory withholdings, and preview prior to confirmation.</p>
  </div>
  <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
    <a href="<?php echo site_url('finance/salary_setup'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[18px]">tune</span>Salary Setup
    </a>
    <?php if (!$selected_batch && !$existing_batch && !empty($preview_data['staff_salaries'])): ?>
      <button type="button" onclick="openConfirmModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">verified</span>Confirm & Save Payroll
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Period Selection & Batch Navigation Bar -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
  <form method="get" action="<?php echo site_url('finance/salary_processing'); ?>" class="flex flex-wrap items-end gap-3" id="periodForm">
    <div class="min-w-[160px]">
      <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Payroll Month</label>
      <select name="month" id="sel_month" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm font-medium focus:outline-none focus:border-primary">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>
            <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="min-w-[120px]">
      <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Year</label>
      <select name="year" id="sel_year" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm font-medium focus:outline-none focus:border-primary">
        <?php $curYr = (int)date('Y'); for ($y = $curYr - 2; $y <= $curYr + 1; $y++): ?>
          <option value="<?php echo $y; ?>" <?php echo ($y == $selected_year) ? 'selected' : ''; ?>>
            <?php echo $y; ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="flex-1 flex items-center justify-end gap-2">
      <?php if (!empty($all_batches)): ?>
        <div class="min-w-[220px]">
          <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Saved Batches Archive</label>
          <select onchange="if(this.value) window.location.href='<?php echo site_url('finance/salary_processing?batch_id='); ?>' + this.value; else window.location.href='<?php echo site_url('finance/salary_processing'); ?>';"
                  class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
            <option value="">-- View Active Month Live --</option>
            <?php foreach ($all_batches as $b): ?>
              <option value="<?php echo $b->id; ?>" <?php echo ($selected_batch && $selected_batch->id == $b->id) ? 'selected' : ''; ?>>
                <?php echo date('M Y', mktime(0, 0, 0, $b->payroll_month, 10, $b->payroll_year)) . ' — ' . $b->batch_number . ' (' . $b->total_staff . ' staff)'; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php 
// Resolve active stats depending on whether viewing confirmed batch or live preview
$active_status_label = 'Preview (Draft)';
$is_confirmed = false;
$tot_gross = 0.00;
$tot_att_ded = 0.00;
$tot_stat_ded = 0.00;
$tot_employer = 0.00;
$tot_net = 0.00;
$staff_count = 0;

if ($selected_batch) {
  $is_confirmed = true;
  $active_status_label = 'Confirmed (' . $selected_batch->batch_number . ')';
  $tot_gross = (float)$selected_batch->gross_amount;
  $tot_att_ded = (float)$selected_batch->attendance_deductions;
  $tot_stat_ded = (float)$selected_batch->statutory_deductions;
  $tot_employer = (float)$selected_batch->employer_contributions;
  $tot_net = (float)$selected_batch->net_amount;
  $staff_count = (int)$selected_batch->total_staff;
} elseif ($existing_batch) {
  $is_confirmed = true;
  $active_status_label = 'Confirmed (' . $existing_batch->batch_number . ')';
  $tot_gross = (float)$existing_batch->gross_amount;
  $tot_att_ded = (float)$existing_batch->attendance_deductions;
  $tot_stat_ded = (float)$existing_batch->statutory_deductions;
  $tot_employer = (float)$existing_batch->employer_contributions;
  $tot_net = (float)$existing_batch->net_amount;
  $staff_count = (int)$existing_batch->total_staff;
} elseif ($preview_data) {
  $tot_gross = (float)$preview_data['totals']['gross'];
  $tot_att_ded = (float)$preview_data['totals']['attendance_deductions'];
  $tot_stat_ded = (float)$preview_data['totals']['statutory_deductions'];
  $tot_employer = (float)$preview_data['totals']['employer_contributions'];
  $tot_net = (float)$preview_data['totals']['net'];
  $staff_count = (int)($preview_data['staff_count'] ?? count($preview_data['staff_salaries'] ?? []));
}
?>

<!-- Status Banner if batch already confirmed -->
<?php if ($is_confirmed): ?>
  <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">verified</span>
      </div>
      <div>
        <h4 class="font-bold text-emerald-900 text-sm">
          Payroll for <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 10, $selected_year)); ?> is Confirmed
        </h4>
        <p class="text-xs text-emerald-700 mt-0.5">
          Batch Number: <strong class="font-mono"><?php echo ($selected_batch ? $selected_batch->batch_number : $existing_batch->batch_number); ?></strong> • Duplicate payroll processing is safely locked.
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-200 text-emerald-900">Locked / Confirmed</span>
      <?php if (!$selected_batch && $existing_batch): ?>
        <a href="<?php echo site_url('finance/salary_processing?batch_id=' . $existing_batch->id); ?>" class="text-xs font-semibold text-emerald-900 underline">View Batch Details</a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Financial KPI Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
  <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="material-symbols-outlined text-primary text-[20px]">payments</span>
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Gross Payroll</span>
    </div>
    <div class="text-2xl font-bold font-mono text-on-surface" id="kpiGross">₹<?php echo number_format($tot_gross, 2); ?></div>
    <div class="text-body-xs text-on-surface-variant mt-0.5"><?php echo $staff_count; ?> active employees</div>
  </div>

  <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-200/70 elevation-1">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="material-symbols-outlined text-rose-700 text-[20px]">event_busy</span>
      <span class="text-[11px] font-semibold text-rose-800 uppercase tracking-wider">Att. Deductions</span>
    </div>
    <div class="text-2xl font-bold font-mono text-rose-700" id="kpiAttDed">₹<?php echo number_format($tot_att_ded, 2); ?></div>
    <div class="text-body-xs text-rose-600 mt-0.5">Unpaid leave adjustments</div>
  </div>

  <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/70 elevation-1">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="material-symbols-outlined text-amber-700 text-[20px]">remove_circle</span>
      <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Employee Ded.</span>
    </div>
    <div class="text-2xl font-bold font-mono text-amber-700" id="kpiStatDed">₹<?php echo number_format($tot_stat_ded, 2); ?></div>
    <div class="text-body-xs text-amber-600 mt-0.5">PF, PT & withholdings</div>
  </div>

  <div class="p-4 rounded-2xl bg-purple-50/80 border border-purple-200/70 elevation-1">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="material-symbols-outlined text-purple-700 text-[20px]">corporate_fare</span>
      <span class="text-[11px] font-semibold text-purple-800 uppercase tracking-wider">Employer Contrib.</span>
    </div>
    <div class="text-2xl font-bold font-mono text-purple-700" id="kpiEmployer">₹<?php echo number_format($tot_employer, 2); ?></div>
    <div class="text-body-xs text-purple-600 mt-0.5">Institutional benefits</div>
  </div>

  <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/70 elevation-1 col-span-2 lg:col-span-1">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="material-symbols-outlined text-emerald-700 text-[20px]">account_balance_wallet</span>
      <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider">Net Payable</span>
    </div>
    <div class="text-2xl font-bold font-mono text-emerald-700" id="kpiNet">₹<?php echo number_format($tot_net, 2); ?></div>
    <div class="text-body-xs text-emerald-600 mt-0.5">Disbursement liability</div>
  </div>
</div>

<!-- Payroll Table: Staff List & Calculations -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-container-low/30">
    <div>
      <h3 class="font-headline-md text-title-md font-bold text-on-surface">
        Staff Payroll Register — <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 10, $selected_year)); ?>
      </h3>
      <p class="text-body-xs text-on-surface-variant">
        <?php echo $is_confirmed ? 'Official confirmed payroll batch records.' : 'Live calculation preview based on Phase 8 salary setup & active attendance.'; ?>
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $is_confirmed ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-primary/10 text-primary border border-primary/20'; ?>">
        <?php echo $active_status_label; ?>
      </span>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md" id="payroll-table">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Employee</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Designation</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Gross Salary</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Att. / Days</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Att. Deduction</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Statutory Ded.</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Employer Contrib.</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Salary</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if ($is_confirmed && !empty($batch_items)): ?>
          <!-- Render Saved Batch Items -->
          <?php $idx = 1; foreach ($batch_items as $bi): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors">
              <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $idx++; ?></td>
              <td class="px-4 py-3">
                <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($bi->full_name); ?></div>
                <div class="text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($bi->employee_code ?: 'ID:' . $bi->staff_id); ?></div>
              </td>
              <td class="px-4 py-3 text-[13px] text-on-surface">
                <?php echo html_escape($bi->designation_name ?: 'Staff'); ?>
                <span class="block text-[10px] text-on-surface-variant uppercase"><?php echo $bi->staff_type; ?></span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface font-semibold text-[13px]">
                ₹<?php echo number_format((float)$bi->gross_salary, 2); ?>
              </td>
              <td class="px-4 py-3 text-center text-xs">
                <span class="font-mono"><?php echo (float)$bi->present_days; ?></span> / <?php echo (float)$bi->total_working_days; ?>
                <?php if ((float)$bi->unpaid_leave_days > 0): ?>
                  <span class="block text-[10px] text-rose-600 font-semibold">(<?php echo (float)$bi->unpaid_leave_days; ?> absent)</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-rose-700 font-semibold text-[13px]">
                <?php echo ((float)$bi->attendance_deduction > 0) ? '-₹' . number_format((float)$bi->attendance_deduction, 2) : '—'; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-amber-700 font-semibold text-[13px]">
                -₹<?php echo number_format((float)$bi->statutory_deductions, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-purple-700 text-[13px]">
                ₹<?php echo number_format((float)$bi->employer_contributions, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-emerald-700 font-bold text-sm">
                ₹<?php echo number_format((float)$bi->net_salary, 2); ?>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  Confirmed
                </span>
              </td>
            </tr>
          <?php endforeach; ?>

        <?php elseif (!$is_confirmed && !empty($preview_data['staff_salaries'])): ?>
          <!-- Render Live Interactive Preview Items -->
          <?php $pIdx = 1; foreach ($preview_data['staff_salaries'] as $ps): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors preview-row" data-staff-id="<?php echo $ps['staff_id']; ?>">
              <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $pIdx++; ?></td>
              <td class="px-4 py-3">
                <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($ps['full_name']); ?></div>
                <div class="text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($ps['employee_code'] ?: 'ID:' . $ps['staff_id']); ?></div>
              </td>
              <td class="px-4 py-3 text-[13px] text-on-surface">
                <?php echo html_escape($ps['designation_name']); ?>
                <span class="block text-[10px] text-on-surface-variant uppercase"><?php echo $ps['staff_type']; ?></span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface font-semibold text-[13px]">
                ₹<span class="col-gross"><?php echo number_format((float)$ps['gross_salary'], 2); ?></span>
              </td>
              <td class="px-4 py-3 text-center text-xs">
                <!-- Inline Editable Absent / Unpaid Days for custom adjustment -->
                <div class="inline-flex items-center gap-1 justify-center">
                  <span class="text-on-surface-variant text-[11px]">Absent:</span>
                  <input type="number" step="0.5" min="0" max="<?php echo $ps['working_days']; ?>"
                         value="<?php echo (float)$ps['unpaid_leave_days']; ?>"
                         data-working-days="<?php echo $ps['working_days']; ?>"
                         data-gross="<?php echo $ps['gross_salary']; ?>"
                         data-stat-ded="<?php echo $ps['statutory_deductions']; ?>"
                         oninput="recalcRowAttendance(this)"
                         class="inp-absent w-14 px-1.5 py-0.5 text-center text-xs rounded border border-outline-variant bg-surface font-mono"/>
                  <span class="text-on-surface-variant text-[11px]">/ <?php echo (float)$ps['working_days']; ?>d</span>
                </div>
              </td>
              <td class="px-4 py-3 text-right font-mono text-rose-700 font-semibold text-[13px]">
                <span class="col-att-ded"><?php echo ((float)$ps['attendance_deduction'] > 0) ? '-₹' . number_format((float)$ps['attendance_deduction'], 2) : '—'; ?></span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-amber-700 font-semibold text-[13px]">
                -₹<span class="col-stat-ded"><?php echo number_format((float)$ps['statutory_deductions'], 2); ?></span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-purple-700 text-[13px]">
                ₹<span class="col-empyr"><?php echo number_format((float)$ps['employer_contributions'], 2); ?></span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-emerald-700 font-bold text-sm">
                ₹<span class="col-net"><?php echo number_format((float)$ps['net_salary'], 2); ?></span>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo $ps['has_structure'] ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-surface-container text-on-surface-variant'; ?>">
                  <?php echo $ps['has_structure'] ? 'Structure' : 'Base Profile'; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>

        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- CONFIRMATION MODAL -->
<div id="confirmModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
    <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/40">
      <div>
        <h3 class="font-headline-md text-title-md font-bold text-on-surface">Confirm Payroll Execution</h3>
        <p class="text-body-xs text-on-surface-variant mt-0.5"><?php echo date('F Y', mktime(0, 0, 0, $selected_month, 10, $selected_year)); ?></p>
      </div>
      <button onclick="closeConfirmModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form method="post" action="<?php echo site_url('finance/salary_processing'); ?>" id="payrollConfirmForm" class="p-6 space-y-4">
      <input type="hidden" name="action" value="confirm_payroll">
      <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
      <input type="hidden" name="year" value="<?php echo $selected_year; ?>">

      <!-- Hidden item inputs injected via JS before submission -->
      <div id="hiddenItemsContainer"></div>

      <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/50 space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-on-surface-variant">Total Staff:</span>
          <strong class="text-on-surface" id="modalStaffCount"><?php echo $staff_count; ?></strong>
        </div>
        <div class="flex justify-between">
          <span class="text-on-surface-variant">Gross Salary:</span>
          <strong class="font-mono text-on-surface" id="modalGross">₹<?php echo number_format($tot_gross, 2); ?></strong>
        </div>
        <div class="flex justify-between">
          <span class="text-on-surface-variant">Attendance Deductions:</span>
          <strong class="font-mono text-rose-700" id="modalAttDed">₹<?php echo number_format($tot_att_ded, 2); ?></strong>
        </div>
        <div class="flex justify-between">
          <span class="text-on-surface-variant">Statutory Deductions:</span>
          <strong class="font-mono text-amber-700" id="modalStatDed">₹<?php echo number_format($tot_stat_ded, 2); ?></strong>
        </div>
        <div class="flex justify-between pt-2 border-t border-outline-variant/40 font-bold text-sm">
          <span class="text-emerald-900">Net Take-Home Payroll:</span>
          <strong class="font-mono text-emerald-700 text-base" id="modalNet">₹<?php echo number_format($tot_net, 2); ?></strong>
        </div>
      </div>

      <div>
        <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Confirmation Remarks / Notes</label>
        <textarea name="remarks" rows="2" placeholder="e.g. Approved monthly payroll batch"
                  class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"></textarea>
      </div>

      <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] shrink-0">info</span>
        <span>Confirming creates official payslips and locks this month from duplicate processing.</span>
      </div>

      <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-end gap-2">
        <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          Confirm & Save Payroll
        </button>
      </div>
    </form>
  </div>
</div>

<script>
var rawPreviewData = <?php echo json_encode($preview_data['staff_salaries'] ?? []); ?>;

function recalcRowAttendance(inp) {
  var row = inp.closest('.preview-row');
  var gross = parseFloat(inp.getAttribute('data-gross') || 0);
  var statDed = parseFloat(inp.getAttribute('data-stat-ded') || 0);
  var wdays = parseFloat(inp.getAttribute('data-working-days') || 30);
  var absent = parseFloat(inp.value || 0);

  if (absent < 0) absent = 0;
  if (absent > wdays) absent = wdays;
  inp.value = absent;

  var perDayRate = (wdays > 0) ? (gross / wdays) : 0;
  var attDed = Math.round(perDayRate * absent * 100) / 100;
  var net = Math.max(0, Math.round((gross - statDed - attDed) * 100) / 100);

  var attCol = row.querySelector('.col-att-ded');
  if (attCol) {
    attCol.textContent = (attDed > 0) ? '-₹' + attDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—';
  }

  var netCol = row.querySelector('.col-net');
  if (netCol) {
    netCol.textContent = net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  // Update in memory array
  var staffId = parseInt(row.getAttribute('data-staff-id'));
  var matched = rawPreviewData.find(function(s) { return parseInt(s.staff_id) === staffId; });
  if (matched) {
    matched.unpaid_leave_days = absent;
    matched.present_days = Math.max(0, wdays - absent);
    matched.attendance_deduction = attDed;
    matched.net_salary = net;
  }

  recalculateKPISummaries();
}

function recalculateKPISummaries() {
  var totalGross = 0;
  var totalAttDed = 0;
  var totalStatDed = 0;
  var totalEmployer = 0;
  var totalNet = 0;

  rawPreviewData.forEach(function(s) {
    totalGross += parseFloat(s.gross_salary || 0);
    totalAttDed += parseFloat(s.attendance_deduction || 0);
    totalStatDed += parseFloat(s.statutory_deductions || 0);
    totalEmployer += parseFloat(s.employer_contributions || 0);
    totalNet += parseFloat(s.net_salary || 0);
  });

  document.getElementById('kpiGross').textContent = '₹' + totalGross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('kpiAttDed').textContent = '₹' + totalAttDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('kpiStatDed').textContent = '₹' + totalStatDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('kpiEmployer').textContent = '₹' + totalEmployer.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('kpiNet').textContent = '₹' + totalNet.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  document.getElementById('modalGross').textContent = '₹' + totalGross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('modalAttDed').textContent = '₹' + totalAttDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('modalStatDed').textContent = '₹' + totalStatDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('modalNet').textContent = '₹' + totalNet.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function openConfirmModal() {
  var container = document.getElementById('hiddenItemsContainer');
  container.innerHTML = '';

  rawPreviewData.forEach(function(s) {
    container.innerHTML += `
      <input type="hidden" name="items[staff_id][]" value="${s.staff_id}"/>
      <input type="hidden" name="items[structure_id][]" value="${s.structure_id || ''}"/>
      <input type="hidden" name="items[basic_salary][]" value="${s.basic_salary || 0}"/>
      <input type="hidden" name="items[gross_salary][]" value="${s.gross_salary || 0}"/>
      <input type="hidden" name="items[working_days][]" value="${s.working_days || 30}"/>
      <input type="hidden" name="items[present_days][]" value="${s.present_days || 30}"/>
      <input type="hidden" name="items[absent_days][]" value="${s.unpaid_leave_days || 0}"/>
      <input type="hidden" name="items[att_ded][]" value="${s.attendance_deduction || 0}"/>
      <input type="hidden" name="items[stat_ded][]" value="${s.statutory_deductions || 0}"/>
      <input type="hidden" name="items[employer_contrib][]" value="${s.employer_contributions || 0}"/>
      <input type="hidden" name="items[net_salary][]" value="${s.net_salary || 0}"/>
    `;
  });

  document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
  document.getElementById('confirmModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
  var tbl = document.getElementById('payroll-table');
  if (tbl && typeof $.fn.DataTable !== 'undefined') {
    $(tbl).DataTable({
      responsive: true,
      pageLength: 25,
      order: [[1, 'asc']],
      language: {
        search: "Filter employee:",
        lengthMenu: "Show _MENU_ staff",
        info: "_START_ – _END_ of _TOTAL_ staff",
        infoEmpty: "0 staff",
        paginate: { next: 'Next →', previous: '← Prev' },
        emptyTable: '<div class="py-12 text-center text-on-surface-variant"><span class="material-symbols-outlined text-[44px] block mb-2 text-on-surface-variant/40">groups</span>No active staff found for this school or period.</div>'
      },
      columnDefs: [{ orderable: false, targets: [0, 9] }]
    });
  }
});
</script>
