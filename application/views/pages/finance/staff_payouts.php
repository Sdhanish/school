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

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container uppercase tracking-wider">Payroll & Disbursements</span>
          <span class="text-body-sm text-on-surface-variant font-medium"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Active Session'); ?></span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Salary Payment & Disbursements</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Pay approved monthly salaries against Staff Salary Payable (DR 2010 / CR Bank/Cash), or issue advances and allowances with real-time double-entry ledger integration.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openPayoutModal('Salary')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-label-md font-semibold hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">payments</span>Pay Salary
        </button>
        <a href="<?php echo site_url('finance/salary_payable'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">pending_actions</span>Salary Payable
        </a>
        <a href="<?php echo site_url('finance/ledger_staff'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">badge</span>Staff Ledgers
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Academic Year -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Year Total</span>
          <span class="p-2 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">calendar_today</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Current Academic Year'); ?></div>
      </div>

      <!-- Total This Month -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">This Month</span>
          <span class="p-2 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">date_range</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['month_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-secondary font-medium mt-1"><?php echo date('F Y'); ?></div>
      </div>

      <!-- Today's Total -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Today's Payouts</span>
          <span class="p-2 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">today</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['today_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1"><?php echo date('d M Y'); ?></div>
      </div>

      <!-- Total Count -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Disbursements</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface"><?php echo number_format($stats['total_count'] ?? 0); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Total payout vouchers</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/staff_payouts'); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Voucher #, Staff, Ref..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Staff Member -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Staff Member</label>
          <select name="staff_id" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary">
            <option value="">All Staff Members</option>
            <?php foreach ($staff_members as $sf): ?>
              <option value="<?php echo $sf->staff_id; ?>" <?php echo ((int)($filters['staff_id'] ?? 0) === (int)$sf->staff_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($sf->full_name . ' (' . ($sf->employee_code ?: 'EMP-' . $sf->staff_id) . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Payout Type -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Payout Type</label>
          <select name="payout_type" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary">
            <option value="">All Types</option>
            <option value="Salary" <?php echo (($filters['payout_type'] ?? '') === 'Salary') ? 'selected' : ''; ?>>Salary</option>
            <option value="Advance" <?php echo (($filters['payout_type'] ?? '') === 'Advance') ? 'selected' : ''; ?>>Advance</option>
            <option value="Bonus" <?php echo (($filters['payout_type'] ?? '') === 'Bonus') ? 'selected' : ''; ?>>Bonus</option>
            <option value="Allowance" <?php echo (($filters['payout_type'] ?? '') === 'Allowance') ? 'selected' : ''; ?>>Allowance</option>
            <option value="Reimbursement" <?php echo (($filters['payout_type'] ?? '') === 'Reimbursement') ? 'selected' : ''; ?>>Reimbursement</option>
            <option value="Other" <?php echo (($filters['payout_type'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="<?php echo html_escape($filters['date_from'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="<?php echo html_escape($filters['date_to'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Actions -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 px-4 py-1.5 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
            Filter
          </button>
          <a href="<?php echo site_url('finance/staff_payouts'); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Payouts Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Staff Disbursement History</h3>
          <p class="text-[12px] text-on-surface-variant">Real-time payroll disbursements linked to staff sub-ledgers</p>
        </div>
        <span class="text-body-sm text-on-surface-variant font-medium"><?php echo count($payouts); ?> Payouts</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Staff Member</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Disbursed From</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mode</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($payouts)): ?>
              <?php foreach ($payouts as $p): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap text-sm"><?php echo date('d-M-Y', strtotime($p->expense_date)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <button type="button" onclick="viewExpenseVoucher(<?php echo $p->id; ?>)" class="font-mono text-[13px] text-primary font-bold hover:underline cursor-pointer">
                      <?php echo html_escape($p->expense_number); ?>
                    </button>
                    <?php if (!empty($p->reference_no)): ?>
                      <span class="block text-[10px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($p->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm">
                    <span class="font-semibold"><?php echo html_escape($p->payee_name); ?></span>
                    <?php if (!empty($p->staff_id)): ?>
                      <a href="<?php echo site_url('finance/staff_statement/' . $p->staff_id); ?>" class="block text-[11px] text-primary hover:underline">
                        View Staff Statement &rarr;
                      </a>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($p->payout_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm whitespace-nowrap">
                    <?php echo html_escape($p->payment_account_name ?: 'Bank Account'); ?>
                    <span class="block text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($p->payment_account_code); ?></span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($p->amount, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($p->payment_mode); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($p->status === 'Reversed'): ?>
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">Reversed</span>
                    <?php else: ?>
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">Disbursed</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      <button type="button" onclick="viewExpenseVoucher(<?php echo $p->id; ?>)" title="View Voucher Details" class="p-1 rounded hover:bg-surface-container-high text-primary cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </button>
                      <?php if (!empty($p->attachment)): ?>
                        <a href="<?php echo base_url($p->attachment); ?>" target="_blank" title="View Attachment" class="p-1 rounded hover:bg-surface-container-high text-on-surface-variant">
                          <span class="material-symbols-outlined text-[18px]">attach_file</span>
                        </a>
                      <?php endif; ?>
                      <?php if ($p->status !== 'Reversed'): ?>
                        <button type="button" onclick="openExpenseReversalModal(<?php echo $p->id; ?>, '<?php echo html_escape($p->expense_number); ?>', '<?php echo number_format($p->amount, 2); ?>')" title="Reverse / Void Voucher" class="p-1 rounded hover:bg-surface-container-high text-error cursor-pointer">
                          <span class="material-symbols-outlined text-[18px]">undo</span>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[40px] text-outline mb-2">payments</span>
                  <p class="text-body-md font-medium">No staff payout records found.</p>
                  <p class="text-[12px] text-on-surface-variant mt-1">Click "Disburse Payout" to issue a salary, advance, or allowance to a staff member.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SALARY PAYMENT & STAFF PAYOUT MODAL -->
    <div id="payout-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-xl w-full p-6 elevation-3 space-y-4 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div>
            <span id="modal-mode-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">SALARY PAYMENT</span>
            <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2 mt-1">
              <span class="material-symbols-outlined text-emerald-600 text-[24px]">payments</span><span id="modal-title-text">Disburse Salary Payment</span>
            </h3>
          </div>
          <button onclick="closePayoutModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- Mode Toggle Tabs: Salary Payment vs General Payout -->
        <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-surface-container-low border border-outline-variant/40 text-xs font-semibold">
          <button type="button" id="tab-salary" onclick="setPaymentCategory('Salary')" class="py-2 px-3 rounded-lg bg-emerald-600 text-white shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">receipt_long</span>Salary Payment (Payable)
          </button>
          <button type="button" id="tab-general" onclick="setPaymentCategory('General')" class="py-2 px-3 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">wallet</span>General Payout / Advance
          </button>
        </div>

        <?php echo form_open_multipart('finance/staff_payouts', array('id' => 'payout-form', 'class' => 'space-y-4', 'onsubmit' => 'return validatePayoutSubmit();')); ?>
          <input type="hidden" name="payout_type" id="payout-type-input" value="Salary"/>

          <!-- SALARY PAYMENT SECTION -->
          <div id="salary-payment-section" class="space-y-3.5">
            <!-- Payment Scope Switcher: Staff-Wise vs Batch-Wise -->
            <div>
              <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1.5">Payment Target Scope *</label>
              <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-outline-variant bg-surface-container-low cursor-pointer has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50">
                  <input type="radio" name="salary_scope" value="staff" checked onchange="onScopeChange('staff')" class="text-emerald-600 focus:ring-emerald-500"/>
                  <span class="text-xs font-semibold text-on-surface">Staff-Wise Payment</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-outline-variant bg-surface-container-low cursor-pointer has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50">
                  <input type="radio" name="salary_scope" value="batch" onchange="onScopeChange('batch')" class="text-emerald-600 focus:ring-emerald-500"/>
                  <span class="text-xs font-semibold text-on-surface">Payroll Batch-Wise Payment</span>
                </label>
              </div>
            </div>

            <!-- Staff Selector (Staff-Wise Scope) -->
            <div id="staff-scope-container">
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Staff Member *</label>
              <select name="staff_id" id="salary-staff-select" onchange="onStaffSelected(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                <option value="">-- Choose Staff Member with Pending Payable --</option>
                <?php foreach ($staff_members as $sf): ?>
                  <option value="<?php echo $sf->staff_id; ?>">
                    <?php echo html_escape($sf->full_name . ' (' . ($sf->employee_code ?: 'ID: ' . $sf->staff_id) . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Batch Selector (Batch-Wise Scope) -->
            <div id="batch-scope-container" class="hidden">
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Approved Payroll Batch *</label>
              <select name="payroll_batch_id" id="salary-batch-select" onchange="onBatchSelected(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                <option value="">-- Choose Approved Payroll Batch --</option>
                <?php if (!empty($payroll_batches)): ?>
                  <?php foreach ($payroll_batches as $pb): ?>
                    <option value="<?php echo $pb->id; ?>">
                      <?php echo html_escape($pb->batch_number . ' — ' . date('F Y', mktime(0, 0, 0, $pb->payroll_month, 10, $pb->payroll_year)) . ' (' . $pb->total_staff . ' Staff, Net: ₹' . number_format($pb->net_amount, 2) . ') [' . $pb->status . ']'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- Dynamic Payable Info Card -->
            <div id="payable-info-card" class="hidden p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 space-y-2">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-amber-800 font-semibold text-xs uppercase tracking-wider">
                  <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                  <span>Approved Salary Payable Liability</span>
                </div>
                <button type="button" onclick="fillFullDue()" class="px-2 py-0.5 rounded bg-emerald-600 text-white font-semibold text-[11px] hover:bg-emerald-700 transition-colors cursor-pointer">
                  Auto-Fill Due
                </button>
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                <div>
                  <span class="text-[11px] text-amber-700 block">Accrued Periods</span>
                  <strong id="card-period-text" class="text-on-surface text-[12px]">—</strong>
                </div>
                <div>
                  <span class="text-[11px] text-amber-700 block">Net Salary</span>
                  <strong id="card-net-text" class="font-mono text-on-surface text-[12px]">₹0.00</strong>
                </div>
                <div>
                  <span class="text-[11px] text-amber-700 block">Outstanding Due</span>
                  <strong id="card-due-text" class="font-mono text-amber-900 font-bold text-[13px]">₹0.00</strong>
                </div>
              </div>
            </div>

            <!-- Overpayment / Zero Payable Warning Alert -->
            <div id="payable-warning-alert" class="hidden p-3 rounded-xl bg-error-container text-on-error-container text-xs flex items-center gap-2">
              <span class="material-symbols-outlined text-[18px] text-error shrink-0">error</span>
              <span id="payable-warning-msg">No approved pending salary payable found. Payment cannot proceed.</span>
            </div>

            <!-- Accounting Standard Rule Note -->
            <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-200/70 flex items-center gap-2 text-xs text-emerald-900">
              <span class="material-symbols-outlined text-emerald-700 text-[18px] shrink-0">verified</span>
              <span>Posting Rule: <strong>DR 2010 Staff Salary Payable</strong> (Staff Subledger) &rarr; <strong>CR Selected Bank/Cash</strong></span>
            </div>
          </div>

          <!-- GENERAL PAYOUT SECTION (Advance / Bonus / Allowance) -->
          <div id="general-payout-section" class="hidden space-y-3.5">
            <!-- Staff Selector -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Staff Member *</label>
              <select name="general_staff_id" id="general-staff-select" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Select Staff Member</option>
                <?php foreach ($staff_members as $sf): ?>
                  <option value="<?php echo $sf->staff_id; ?>" data-salary="<?php echo html_escape($sf->salary ?? '0.00'); ?>">
                    <?php echo html_escape($sf->full_name . ' (' . ($sf->employee_code ?: 'ID: ' . $sf->staff_id) . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Payout Type -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payout Type *</label>
              <select id="general-payout-type-select" onchange="onGeneralTypeChange(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Advance">Salary Advance</option>
                <option value="Bonus">Performance Bonus</option>
                <option value="Allowance">Allowance</option>
                <option value="Reimbursement">Reimbursement</option>
                <option value="Other">Other Payout</option>
              </select>
            </div>
          </div>

          <!-- COMMON TRANSACTION FIELDS -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block font-label-md text-label-md text-on-surface font-medium">Amount (₹) *</label>
                <span id="max-due-badge" class="hidden text-[11px] font-mono text-on-surface-variant font-medium"></span>
              </div>
              <input type="number" step="0.01" min="0.01" name="amount" id="payout-amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"/>
              <span id="overpayment-error" class="hidden text-[11px] text-error font-medium mt-1">Amount exceeds outstanding payable balance!</span>
            </div>

            <!-- Disbursed From Cash/Bank Account -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Disbursed From (Credit) *</label>
              <select name="paid_from_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                <option value="">Select Cash or Bank</option>
                <?php foreach ($cash_bank_accounts as $cba): ?>
                  <option value="<?php echo $cba->id; ?>">
                    <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ') [₹' . number_format($cba->current_balance ?? 0, 2) . ']'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payout Date -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payout Date *</label>
              <input type="date" name="payout_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"/>
            </div>

            <!-- Payment Mode -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Mode *</label>
              <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="UPI">UPI / QR</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Cheque / UTR / Transaction Reference #</label>
            <input type="text" name="reference_no" placeholder="e.g. UTR-2026849" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"/>
          </div>

          <!-- Description / Remarks -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Remarks / Description</label>
            <textarea name="remarks" id="payout-remarks" rows="2" placeholder="e.g. Disbursed approved monthly salary via NEFT..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload Bank Advice / Voucher Slip (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-primary file:text-on-primary hover:file:bg-primary/90"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closePayoutModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" id="submit-payout-btn" class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white text-label-md font-semibold hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">verified</span>
              <span id="submit-btn-text">Disburse Salary Payment</span>
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- VIEW PAYOUT VOUCHER MODAL -->
    <div id="view-voucher-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-2xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div>
            <span id="vv-submodule-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-container-high text-on-surface">STAFF PAYOUT</span>
            <h3 class="font-headline-md text-title-lg text-on-surface font-semibold mt-1" id="vv-title">Payout Voucher</h3>
          </div>
          <button onclick="closeViewVoucherModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="space-y-4 text-body-sm" id="vv-content">
          <div class="p-8 text-center text-on-surface-variant">Loading payout data...</div>
        </div>

        <div class="flex items-center justify-end pt-3 border-t border-outline-variant/50">
          <button type="button" onclick="closeViewVoucherModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- REVERSAL MODAL -->
    <div id="expense-reversal-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-error text-[24px]">undo</span>Reverse Staff Payout
          </h3>
          <button onclick="closeExpenseReversalModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/reverse_expense', array('id' => 'expense-reversal-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="expense_id" id="rev-expense-id" value="0"/>
          <input type="hidden" name="return_url" value="finance/staff_payouts"/>

          <p class="text-body-sm text-on-surface-variant">
            You are reversing staff payout <strong id="rev-expense-number" class="text-error font-mono"></strong> for amount <strong id="rev-expense-amount" class="text-on-surface font-mono"></strong>.
            This will reverse the entry in both the Cash/Bank ledger and the Staff Member's ledger.
          </p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reversal Reason *</label>
            <textarea name="reversal_reason" rows="2" required placeholder="State why this staff payout is being reversed..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-error/20 focus:border-error"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeExpenseReversalModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-error text-white text-label-md font-semibold hover:bg-error/90 transition-colors shadow-sm cursor-pointer">
              Confirm Reversal
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Scripts -->
    <script>
      const PENDING_PAYABLES  = <?php echo json_encode($pending_payables ?? []); ?>;
      const PAYROLL_BATCHES   = <?php echo json_encode($payroll_batches ?? []); ?>;
      const PREFILL_STAFF_ID  = <?php echo (int)($prefill_staff_id ?? 0); ?>;
      const PREFILL_BATCH_ID  = <?php echo (int)($prefill_batch_id ?? 0); ?>;
      const PREFILL_ITEM_ID   = <?php echo (int)($prefill_item_id ?? 0); ?>;
      const AUTO_OPEN_ACTION  = '<?php echo html_escape($action ?? ''); ?>';

      let currentCategory = 'Salary';
      let currentScope    = 'staff';
      let currentMaxDue   = 0;

      function openPayoutModal(category) {
        var m = document.getElementById('payout-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        setPaymentCategory(category || 'Salary');
      }

      function closePayoutModal() {
        var m = document.getElementById('payout-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function setPaymentCategory(cat) {
        currentCategory = cat;
        var tabSalary  = document.getElementById('tab-salary');
        var tabGeneral = document.getElementById('tab-general');
        var salarySec  = document.getElementById('salary-payment-section');
        var generalSec = document.getElementById('general-payout-section');
        var typeInput  = document.getElementById('payout-type-input');
        var badge      = document.getElementById('modal-mode-badge');
        var titleText  = document.getElementById('modal-title-text');
        var btnText    = document.getElementById('submit-btn-text');
        var submitBtn  = document.getElementById('submit-payout-btn');

        if (cat === 'Salary') {
          tabSalary.className = 'py-2 px-3 rounded-lg bg-emerald-600 text-white shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer';
          tabGeneral.className = 'py-2 px-3 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all flex items-center justify-center gap-1.5 cursor-pointer';
          salarySec.classList.remove('hidden');
          generalSec.classList.add('hidden');
          typeInput.value = 'Salary';
          badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200';
          badge.textContent = 'SALARY PAYMENT';
          titleText.textContent = 'Disburse Salary Payment';
          btnText.textContent = 'Disburse Salary Payment';
          submitBtn.className = 'px-6 py-2.5 rounded-lg bg-emerald-600 text-white text-label-md font-semibold hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer flex items-center gap-1.5';
          document.getElementById('general-staff-select').required = false;
        } else {
          tabGeneral.className = 'py-2 px-3 rounded-lg bg-primary text-on-primary shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer';
          tabSalary.className = 'py-2 px-3 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all flex items-center justify-center gap-1.5 cursor-pointer';
          salarySec.classList.add('hidden');
          generalSec.classList.remove('hidden');
          typeInput.value = document.getElementById('general-payout-type-select').value || 'Advance';
          badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary-container text-on-primary-container';
          badge.textContent = 'GENERAL PAYOUT';
          titleText.textContent = 'Record Staff Disbursement';
          btnText.textContent = 'Disburse Payout';
          submitBtn.className = 'px-6 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer flex items-center gap-1.5';
          document.getElementById('general-staff-select').required = true;
          document.getElementById('payable-info-card').classList.add('hidden');
          document.getElementById('payable-warning-alert').classList.add('hidden');
          document.getElementById('max-due-badge').classList.add('hidden');
          document.getElementById('overpayment-error').classList.add('hidden');
          submitBtn.disabled = false;
        }
      }

      function onGeneralTypeChange(val) {
        document.getElementById('payout-type-input').value = val;
      }

      function onScopeChange(scope) {
        currentScope = scope;
        var staffContainer = document.getElementById('staff-scope-container');
        var batchContainer = document.getElementById('batch-scope-container');
        var staffSelect     = document.getElementById('salary-staff-select');
        var batchSelect     = document.getElementById('salary-batch-select');

        if (scope === 'staff') {
          staffContainer.classList.remove('hidden');
          batchContainer.classList.add('hidden');
          batchSelect.value = '';
          onStaffSelected(staffSelect.value);
        } else {
          staffContainer.classList.add('hidden');
          batchContainer.classList.remove('hidden');
          staffSelect.value = '';
          onBatchSelected(batchSelect.value);
        }
      }

      function onStaffSelected(staffId) {
        staffId = parseInt(staffId, 10);
        var card = document.getElementById('payable-info-card');
        var warn = document.getElementById('payable-warning-alert');
        var submitBtn = document.getElementById('submit-payout-btn');
        var maxDueBadge = document.getElementById('max-due-badge');

        if (!staffId || staffId <= 0) {
          card.classList.add('hidden');
          warn.classList.add('hidden');
          maxDueBadge.classList.add('hidden');
          currentMaxDue = 0;
          return;
        }

        // Filter PENDING_PAYABLES for this staff
        var matching = PENDING_PAYABLES.filter(function(p) {
          return parseInt(p.staff_id, 10) === staffId;
        });

        if (matching.length === 0) {
          card.classList.add('hidden');
          warn.classList.remove('hidden');
          document.getElementById('payable-warning-msg').textContent = 'This staff member does not have any approved pending salary payable liabilities.';
          submitBtn.disabled = true;
          maxDueBadge.classList.add('hidden');
          currentMaxDue = 0;
          return;
        }

        warn.classList.add('hidden');
        submitBtn.disabled = false;

        var totalNet = 0;
        var totalDue = 0;
        var periods  = [];

        matching.forEach(function(m) {
          totalNet += parseFloat(m.net_salary || 0);
          totalDue += parseFloat(m.due_amount || 0);
          var pName = m.payroll_month + '/' + m.payroll_year;
          if (!periods.includes(pName)) periods.push(pName);
        });

        currentMaxDue = totalDue;

        document.getElementById('card-period-text').textContent = periods.join(', ') + ' (' + matching.length + ' item' + (matching.length > 1 ? 's' : '') + ')';
        document.getElementById('card-net-text').textContent = '₹' + totalNet.toFixed(2);
        document.getElementById('card-due-text').textContent = '₹' + totalDue.toFixed(2);
        card.classList.remove('hidden');

        maxDueBadge.textContent = 'Due Cap: ₹' + totalDue.toFixed(2);
        maxDueBadge.classList.remove('hidden');

        // Auto-fill amount input
        document.getElementById('payout-amount').value = totalDue.toFixed(2);
        document.getElementById('payout-amount').max   = totalDue.toFixed(2);
        document.getElementById('payout-remarks').value = 'Salary disbursement for ' + (matching[0].full_name || '') + ' (' + periods.join(', ') + ')';
      }

      function onBatchSelected(batchId) {
        batchId = parseInt(batchId, 10);
        var card = document.getElementById('payable-info-card');
        var warn = document.getElementById('payable-warning-alert');
        var submitBtn = document.getElementById('submit-payout-btn');
        var maxDueBadge = document.getElementById('max-due-badge');

        if (!batchId || batchId <= 0) {
          card.classList.add('hidden');
          warn.classList.add('hidden');
          maxDueBadge.classList.add('hidden');
          currentMaxDue = 0;
          return;
        }

        var matching = PENDING_PAYABLES.filter(function(p) {
          return parseInt(p.batch_id, 10) === batchId;
        });

        if (matching.length === 0) {
          card.classList.add('hidden');
          warn.classList.remove('hidden');
          document.getElementById('payable-warning-msg').textContent = 'This payroll batch does not have any pending payable items or is already fully paid.';
          submitBtn.disabled = true;
          maxDueBadge.classList.add('hidden');
          currentMaxDue = 0;
          return;
        }

        warn.classList.add('hidden');
        submitBtn.disabled = false;

        var totalNet = 0;
        var totalDue = 0;
        var bNum = matching[0].batch_number || ('Batch #' + batchId);
        var periodStr = matching[0].payroll_month + '/' + matching[0].payroll_year;

        matching.forEach(function(m) {
          totalNet += parseFloat(m.net_salary || 0);
          totalDue += parseFloat(m.due_amount || 0);
        });

        currentMaxDue = totalDue;

        document.getElementById('card-period-text').textContent = periodStr + ' (' + matching.length + ' Staff)';
        document.getElementById('card-net-text').textContent = '₹' + totalNet.toFixed(2);
        document.getElementById('card-due-text').textContent = '₹' + totalDue.toFixed(2);
        card.classList.remove('hidden');

        maxDueBadge.textContent = 'Batch Due: ₹' + totalDue.toFixed(2);
        maxDueBadge.classList.remove('hidden');

        document.getElementById('payout-amount').value = totalDue.toFixed(2);
        document.getElementById('payout-amount').max   = totalDue.toFixed(2);
        document.getElementById('payout-remarks').value = 'Full batch salary disbursement for ' + bNum + ' (' + periodStr + ')';
      }

      function fillFullDue() {
        if (currentMaxDue > 0) {
          document.getElementById('payout-amount').value = currentMaxDue.toFixed(2);
          document.getElementById('overpayment-error').classList.add('hidden');
        }
      }

      // Live amount overpayment validation
      document.addEventListener('DOMContentLoaded', function() {
        var amtInput = document.getElementById('payout-amount');
        if (amtInput) {
          amtInput.addEventListener('input', function() {
            if (currentCategory === 'Salary' && currentMaxDue > 0) {
              var val = parseFloat(this.value || 0);
              var errEl = document.getElementById('overpayment-error');
              if (val > (currentMaxDue + 0.001)) {
                errEl.classList.remove('hidden');
                document.getElementById('submit-payout-btn').disabled = true;
              } else {
                errEl.classList.add('hidden');
                document.getElementById('submit-payout-btn').disabled = false;
              }
            }
          });
        }

        // Auto-Open or Prefill from URL query params
        if (AUTO_OPEN_ACTION === 'pay' || PREFILL_STAFF_ID > 0 || PREFILL_BATCH_ID > 0) {
          openPayoutModal('Salary');
          if (PREFILL_BATCH_ID > 0) {
            document.querySelector('input[name="salary_scope"][value="batch"]').checked = true;
            onScopeChange('batch');
            document.getElementById('salary-batch-select').value = PREFILL_BATCH_ID;
            onBatchSelected(PREFILL_BATCH_ID);
          } else if (PREFILL_STAFF_ID > 0) {
            document.querySelector('input[name="salary_scope"][value="staff"]').checked = true;
            onScopeChange('staff');
            document.getElementById('salary-staff-select').value = PREFILL_STAFF_ID;
            onStaffSelected(PREFILL_STAFF_ID);
          }
        }
      });

      function validatePayoutSubmit() {
        var amt = parseFloat(document.getElementById('payout-amount').value || 0);
        if (amt <= 0) {
          alert('Please enter a valid disbursement amount greater than 0.');
          return false;
        }

        if (currentCategory === 'Salary') {
          if (currentMaxDue > 0 && amt > (currentMaxDue + 0.001)) {
            alert('Payment amount (₹' + amt.toFixed(2) + ') cannot exceed outstanding payable balance (₹' + currentMaxDue.toFixed(2) + '). Overpayment is strictly prevented.');
            return false;
          }
          if (currentScope === 'staff') {
            var stf = document.getElementById('salary-staff-select').value;
            if (!stf) {
              alert('Please select a Staff Member.');
              return false;
            }
          } else {
            var bth = document.getElementById('salary-batch-select').value;
            if (!bth) {
              alert('Please select an Approved Payroll Batch.');
              return false;
            }
          }
        } else {
          var genStf = document.getElementById('general-staff-select').value;
          if (!genStf) {
            alert('Please select a Staff Member.');
            return false;
          }
          // Copy general staff id to main staff_id
          document.getElementById('salary-staff-select').value = genStf;
        }
        return true;
      }

      function openExpenseReversalModal(id, num, amt) {
        document.getElementById('rev-expense-id').value = id;
        document.getElementById('rev-expense-number').textContent = num;
        document.getElementById('rev-expense-amount').textContent = '₹' + amt;
        var m = document.getElementById('expense-reversal-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeExpenseReversalModal() {
        var m = document.getElementById('expense-reversal-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function openViewVoucherModal() {
        var m = document.getElementById('view-voucher-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeViewVoucherModal() {
        var m = document.getElementById('view-voucher-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function viewExpenseVoucher(id) {
        openViewVoucherModal();
        var container = document.getElementById('vv-content');
        container.innerHTML = '<div class="p-8 text-center text-on-surface-variant"><span class="material-symbols-outlined animate-spin text-[30px] text-primary">progress_activity</span><p class="mt-2">Loading voucher details...</p></div>';

        fetch('<?php echo site_url("finance/view_expense_ajax/"); ?>' + id)
          .then(function(res) { return res.json(); })
          .then(function(res) {
            if (!res.success) {
              container.innerHTML = '<div class="p-4 rounded-xl bg-error-container text-on-error-container">' + (res.message || 'Failed to load voucher.') + '</div>';
              return;
            }
            var d = res.data;
            document.getElementById('vv-title').textContent = d.expense_number;
            document.getElementById('vv-submodule-badge').textContent = 'STAFF PAYOUT (' + (d.payout_type || 'Salary') + ')';

            var html = '';
            html += '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payout Date</span><span class="font-semibold text-on-surface">' + (d.expense_date || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Disbursed Amount</span><span class="font-mono font-bold text-primary">₹' + parseFloat(d.amount).toFixed(2) + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payment Mode</span><span class="font-semibold text-on-surface">' + (d.payment_mode || 'Bank') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Status</span><span class="font-bold ' + (d.status === 'Reversed' ? 'text-error' : 'text-secondary') + '">' + (d.status || 'Paid') + '</span></div>';
            html += '</div>';

            html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Staff Member</span><strong class="text-on-surface">' + (d.payee_name || '—') + '</strong></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Reference Number</span><span class="font-mono text-on-surface">' + (d.reference_no || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Ledger / Expense Head</span><span class="text-on-surface font-semibold">' + (d.expense_account_name || 'Staff Payable') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Disbursed From</span><span class="text-on-surface">' + (d.payment_account_name || '—') + '</span></div>';
            html += '</div>';

            if (d.description) {
              html += '<div class="p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/30"><span class="block text-[11px] text-on-surface-variant font-medium">Particulars</span><p class="text-on-surface text-sm mt-0.5">' + d.description + '</p></div>';
            }

            // Double Entry Lines
            if (d.lines && d.lines.length > 0) {
              html += '<div class="mt-4"><h4 class="font-semibold text-on-surface text-sm mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-emerald-600">account_balance</span>Double-Entry Ledger Postings</h4>';
              html += '<div class="border border-outline-variant/60 rounded-xl overflow-hidden"><table class="w-full text-xs text-left">';
              html += '<thead class="bg-surface-container-low text-on-surface-variant border-b border-outline-variant/40 font-semibold"><tr><th class="p-2.5">Account / Ledger Head</th><th class="p-2.5">Type</th><th class="p-2.5 text-right">Debit (₹)</th><th class="p-2.5 text-right">Credit (₹)</th></tr></thead>';
              html += '<tbody class="divide-y divide-outline-variant/30">';
              var totDeb = 0, totCred = 0;
              d.lines.forEach(function(l) {
                var deb = parseFloat(l.debit || 0);
                var cred = parseFloat(l.credit || 0);
                totDeb += deb;
                totCred += cred;
                html += '<tr>';
                html += '<td class="p-2.5 font-medium text-on-surface">' + (l.account_name || '') + ' <span class="text-on-surface-variant font-mono">(' + (l.account_code || '') + ')</span>' + (l.ledger_name ? '<span class="block text-[10px] text-primary font-medium">' + l.ledger_name + '</span>' : '') + '</td>';
                html += '<td class="p-2.5 font-semibold ' + (l.entry_type === 'Debit' ? 'text-error' : 'text-secondary') + '">' + l.entry_type + '</td>';
                html += '<td class="p-2.5 text-right font-mono font-bold">' + (deb > 0 ? '₹' + deb.toFixed(2) : '—') + '</td>';
                html += '<td class="p-2.5 text-right font-mono font-bold">' + (cred > 0 ? '₹' + cred.toFixed(2) : '—') + '</td>';
                html += '</tr>';
              });
              html += '<tr class="bg-surface-container-low font-bold"><td colspan="2" class="p-2.5 text-right text-on-surface uppercase">Balanced Totals:</td><td class="p-2.5 text-right font-mono text-error">₹' + totDeb.toFixed(2) + '</td><td class="p-2.5 text-right font-mono text-secondary">₹' + totCred.toFixed(2) + '</td></tr>';
              html += '</tbody></table></div></div>';
            }

            // Audit
            html += '<div class="pt-2 text-[11px] text-on-surface-variant border-t border-outline-variant/40 flex items-center justify-between flex-wrap gap-2">';
            html += '<span>Created by: <strong>' + (d.created_by_name || 'System Admin') + '</strong> on ' + (d.created_at || '—') + '</span>';
            if (d.status === 'Reversed') {
              html += '<span class="text-error font-medium">Reversed by: <strong>' + (d.reversed_by_name || 'Admin') + '</strong> | Reason: ' + (d.reversal_reason || '—') + '</span>';
            }
            html += '</div>';

            container.innerHTML = html;
          })
          .catch(function(err) {
            container.innerHTML = '<div class="p-4 rounded-xl bg-error-container text-on-error-container">Network error while fetching voucher.</div>';
          });
      }
    </script>
