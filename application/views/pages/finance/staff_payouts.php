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
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Staff Payout</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Disburse monthly staff salaries, advances, bonuses, and allowances with automated double-entry ledger integration.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openPayoutModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">payments</span>Disburse Payout
        </button>
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

    <!-- NEW PAYOUT MODAL -->
    <div id="payout-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-lg w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[24px]">payments</span>Record Staff Payout
          </h3>
          <button onclick="closePayoutModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('finance/staff_payouts', array('id' => 'payout-form', 'class' => 'space-y-4')); ?>
          <!-- Staff Selection -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Staff Member *</label>
            <select name="staff_id" id="staff-select" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">Select Staff Member</option>
              <?php foreach ($staff_members as $sf): ?>
                <option value="<?php echo $sf->staff_id; ?>" data-salary="<?php echo html_escape($sf->salary ?? '0.00'); ?>">
                  <?php echo html_escape($sf->full_name . ' (' . ($sf->employee_code ?: 'ID: ' . $sf->staff_id) . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payout Type -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payout Type *</label>
              <select name="payout_type" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Salary">Salary</option>
                <option value="Advance">Salary Advance</option>
                <option value="Bonus">Performance Bonus</option>
                <option value="Allowance">Allowance</option>
                <option value="Reimbursement">Reimbursement</option>
                <option value="Other">Other Payout</option>
              </select>
            </div>

            <!-- Amount -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Amount (₹) *</label>
              <input type="number" step="0.01" min="1" name="amount" id="payout-amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Paid From Account -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Disbursed From *</label>
              <select name="paid_from_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Select Cash/Bank</option>
                <?php foreach ($cash_bank_accounts as $cba): ?>
                  <option value="<?php echo $cba->id; ?>">
                    <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ') [₹' . number_format($cba->current_balance ?? 0, 2) . ']'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Date -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payout Date *</label>
              <input type="date" name="payout_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payment Mode -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Mode *</label>
              <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="UPI">UPI / QR</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Reference # -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Cheque / UTR / Txn Ref #</label>
              <input type="text" name="reference_no" placeholder="e.g. UTR-2026849" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description / Remarks</label>
            <textarea name="remarks" rows="2" placeholder="e.g. March salary payout with transport allowance bonus..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload Bank Advice / Slip (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-primary file:text-on-primary hover:file:bg-primary/90"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closePayoutModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
              Disburse Payout
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
      function openPayoutModal() {
        var m = document.getElementById('payout-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closePayoutModal() {
        var m = document.getElementById('payout-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
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
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Ledger / Expense Head</span><span class="text-on-surface">' + (d.expense_account_name || 'Salaries & Allowances') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Disbursed From</span><span class="text-on-surface">' + (d.payment_account_name || '—') + '</span></div>';
            html += '</div>';

            if (d.description) {
              html += '<div class="p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/30"><span class="block text-[11px] text-on-surface-variant font-medium">Particulars</span><p class="text-on-surface text-sm mt-0.5">' + d.description + '</p></div>';
            }

            // Double Entry Lines
            if (d.lines && d.lines.length > 0) {
              html += '<div class="mt-4"><h4 class="font-semibold text-on-surface text-sm mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-primary">account_balance</span>Double-Entry Ledger Postings</h4>';
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
