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
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container uppercase tracking-wider">Disbursements & Returns</span>
          <span class="text-body-sm text-on-surface-variant font-medium"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Active Session'); ?></span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Refund Transactions</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Disburse student fee returns, caution deposit repayments, and excess collections with audit compliance.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openRefundModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-error text-white text-label-md font-semibold hover:bg-error/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">currency_exchange</span>Process Refund
        </button>
        <a href="<?php echo site_url('finance/adjustments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">tune</span>Adjustments
        </a>
        <a href="<?php echo site_url('finance/ledger_students'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">school</span>Student Ledgers
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Year Refunds -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Session Refunds</span>
          <span class="p-2 rounded-xl bg-error/10 text-error flex items-center justify-center">
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

      <!-- Today's Refunds -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Today</span>
          <span class="p-2 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">today</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['today_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1"><?php echo date('d M Y'); ?></div>
      </div>

      <!-- Count -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Refund Vouchers</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface"><?php echo number_format($stats['total_count'] ?? 0); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Processed refund vouchers</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/refunds'); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Voucher #, Student, Ref..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="<?php echo html_escape($filters['from_date'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="<?php echo html_escape($filters['to_date'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
        </div>

        <!-- Payment Method -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Payment Method</label>
          <select name="payment_method" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary">
            <option value="">All Modes</option>
            <?php foreach (['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Online', 'Card', 'Other'] as $m): ?>
              <option value="<?php echo $m; ?>" <?php echo (($filters['payment_method'] ?? '') === $m) ? 'selected' : ''; ?>><?php echo $m; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Status</label>
          <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary">
            <option value="">All Statuses</option>
            <option value="active" <?php echo (($filters['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="reversed" <?php echo (($filters['status'] ?? '') === 'reversed') ? 'selected' : ''; ?>>Reversed</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-[16px]">filter_list</span>Filter
          </button>
          <a href="<?php echo site_url('finance/refunds'); ?>" class="p-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Reset Filters">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
          </a>
        </div>
      </form>
    </div>

    <!-- Refunds Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Refunds Registry</h3>
          <p class="text-body-sm text-on-surface-variant">Showing processed fee returns and caution deposit disbursements</p>
        </div>
        <span class="text-body-sm font-medium text-on-surface-variant"><?php echo count($transactions); ?> Records</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Beneficiary</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Accounts (Dr &larr; Cr)</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mode & Ref</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($transactions)): ?>
              <?php foreach ($transactions as $t): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap text-body-sm">
                    <?php echo date('d-M-Y', strtotime($t->transaction_date)); ?>
                  </td>
                  <td class="px-4 py-3 font-mono text-[13px] text-error font-semibold whitespace-nowrap">
                    <?php echo html_escape($t->transaction_number); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface">
                    <div class="font-medium text-body-sm"><?php echo html_escape($t->party_name ?: 'Beneficiary'); ?></div>
                    <span class="inline-block text-[11px] px-1.5 py-0.2 rounded bg-surface-container-high text-on-surface-variant font-medium">
                      <?php echo html_escape($t->party_type ?: 'Student'); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-body-sm">
                    <div class="flex items-center gap-1 font-mono text-[12px]">
                      <span class="text-error font-semibold"><?php echo html_escape($t->debit_accounts_str ?: 'Refund Account'); ?></span>
                      <span class="material-symbols-outlined text-[14px] text-on-surface-variant">arrow_back</span>
                      <span class="text-secondary font-semibold"><?php echo html_escape($t->credit_accounts_str ?: 'Cash/Bank'); ?></span>
                    </div>
                    <?php if (!empty($t->narration)): ?>
                      <div class="text-[11px] text-on-surface-variant mt-0.5 truncate max-w-xs"><?php echo html_escape($t->narration); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-body-sm whitespace-nowrap">
                    <span class="font-medium"><?php echo html_escape($t->payment_method ?: 'Cash'); ?></span>
                    <?php if (!empty($t->reference_no)): ?>
                      <div class="text-[11px] font-mono text-on-surface-variant">#<?php echo html_escape($t->reference_no); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($t->total_amount, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if (!empty($t->is_reversed)): ?>
                      <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-error-container text-on-error-container">Reversed</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                      <button onclick="viewVoucher(<?php echo $t->id; ?>)" class="inline-flex items-center gap-1 text-[12px] font-semibold text-primary hover:underline cursor-pointer" title="View Full Voucher">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>View
                      </button>
                      <?php if (empty($t->is_reversed)): ?>
                        <button onclick="openReversalModal(<?php echo $t->id; ?>, '<?php echo html_escape($t->transaction_number); ?>')" class="inline-flex items-center gap-1 text-[12px] font-semibold text-error hover:underline cursor-pointer" title="Reverse Refund">
                          <span class="material-symbols-outlined text-[16px]">undo</span>Reverse
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[36px] text-outline-variant mb-2 block">currency_exchange</span>
                  No refund transactions found for the selected criteria.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: Process Refund                                                     -->
    <!-- ========================================================================= -->
    <div id="refundModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
      <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full border border-outline-variant/60 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/40 bg-surface-container-low/40">
          <div>
            <h3 class="text-title-md font-bold text-on-surface">Process Refund Voucher</h3>
            <p class="text-body-sm text-on-surface-variant">Disburse funds with audit reasons and atomic ledger posting</p>
          </div>
          <button onclick="closeRefundModal()" class="p-1 rounded-full text-on-surface-variant hover:bg-surface-container-high cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <form method="post" action="<?php echo site_url('finance/refunds'); ?>" enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-4">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"/>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Refund Amount (₹) <span class="text-error">*</span></label>
              <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-headline-sm font-bold font-mono focus:ring-1 focus:ring-error"/>
            </div>

            <!-- Date -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Transaction Date <span class="text-error">*</span></label>
              <input type="date" name="transaction_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Paid From Account (Credit) -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Disbursed From (Cash / Bank) <span class="text-error">*</span></label>
              <select name="paid_from_account_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary">
                <option value="">-- Select Cash/Bank Account --</option>
                <?php foreach ($cash_bank_accounts as $acc): ?>
                  <option value="<?php echo $acc->id; ?>">
                    <?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ') - ₹' . number_format($acc->current_balance, 2)); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="text-[11px] text-on-surface-variant">Asset Account (Credit - funds decrease)</span>
            </div>

            <!-- Refund Account (Debit) -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Refund Account Head <span class="text-error">*</span></label>
              <select name="refund_account_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary">
                <option value="">-- Select Refund Account --</option>
                <?php foreach ($refund_accounts as $racc): ?>
                  <option value="<?php echo $racc->id; ?>">
                    <?php echo html_escape($racc->account_name . ' (' . $racc->account_code . ') - ' . $racc->category); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="text-[11px] text-on-surface-variant">Expense or Liability Account (Debit)</span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payment Mode -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Payment Method</label>
              <select name="payment_method" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary">
                <?php foreach (['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Online', 'Card', 'Other'] as $m): ?>
                  <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Reference No -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Reference / Cheque / UTR #</label>
              <input type="text" name="reference_no" placeholder="e.g. REF-2024-819" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary"/>
            </div>
          </div>

          <!-- Beneficiary Information -->
          <div class="p-3.5 rounded-xl bg-surface-container-low/50 border border-outline-variant/40 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-label-md font-bold text-on-surface">Beneficiary Details</span>
              <span class="text-[11px] text-on-surface-variant">Linked party record</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Party Type</label>
                <select name="party_type" id="refund_party_type" onchange="toggleRefundPartySelector()" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary">
                  <option value="Student">Student</option>
                  <option value="Staff">Staff Member</option>
                  <option value="Other">Other Party</option>
                </select>
              </div>

              <!-- Student selector -->
              <div id="refund_party_student_box">
                <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Select Student</label>
                <select name="party_student_id" id="refund_party_student_id" onchange="handleRefundStudentSelect()" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary">
                  <option value="">-- Select Student --</option>
                  <?php foreach ($students as $st): ?>
                    <option value="<?php echo $st->student_id; ?>" data-name="<?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?>">
                      <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Staff selector -->
              <div id="refund_party_staff_box" class="hidden">
                <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Select Staff</label>
                <select name="party_staff_id" id="refund_party_staff_id" onchange="handleRefundStaffSelect()" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary">
                  <option value="">-- Select Staff --</option>
                  <?php foreach ($staff_members as $sf): ?>
                    <option value="<?php echo $sf->staff_id; ?>" data-name="<?php echo html_escape($sf->full_name . ' (' . $sf->employee_code . ')'); ?>">
                      <?php echo html_escape($sf->full_name . ' (' . $sf->employee_code . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Beneficiary Name manual input -->
              <div id="refund_party_name_box" class="sm:col-span-1">
                <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Beneficiary Name</label>
                <input type="text" name="party_name" id="refund_party_name" placeholder="Full name of beneficiary" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary"/>
                <input type="hidden" name="party_id" id="refund_party_id" value="0"/>
              </div>
            </div>
          </div>

          <!-- Mandatory Reason -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Mandatory Reason for Refund <span class="text-error">*</span></label>
            <textarea name="reason" rows="2" required placeholder="State justification (e.g. Caution deposit return on graduation, admission cancellation)..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-error"></textarea>
          </div>

          <!-- Description / Remarks -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Additional Remarks / Particulars</label>
            <textarea name="description" rows="2" placeholder="Cheque remarks, bank details or student account reference..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Supporting Application / Claim Document (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-body-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-body-sm file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest cursor-pointer"/>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeRefundModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-sm font-medium cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-error text-white text-body-sm font-semibold hover:bg-error/90 transition-colors shadow-sm cursor-pointer">
              Disburse Refund Voucher
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: View Voucher (AJAX)                                                -->
    <!-- ========================================================================= -->
    <div id="viewVoucherModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
      <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full border border-outline-variant/60 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/40 bg-surface-container-low/40">
          <div>
            <h3 class="text-title-md font-bold text-on-surface" id="voucher_header_title">Refund Voucher</h3>
            <p class="text-body-sm text-on-surface-variant font-mono" id="voucher_number_display">#VCH-0000</p>
          </div>
          <button onclick="closeVoucherModal()" class="p-1 rounded-full text-on-surface-variant hover:bg-surface-container-high cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4" id="voucher_content_area">
          <div class="py-8 text-center text-on-surface-variant">Loading voucher details...</div>
        </div>

        <div class="flex items-center justify-between px-6 py-3 border-t border-outline-variant/40 bg-surface-container-low/20">
          <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-body-sm font-medium text-on-surface hover:bg-surface-container-high">
            <span class="material-symbols-outlined text-[16px]">print</span>Print
          </button>
          <button type="button" onclick="closeVoucherModal()" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: Reverse Transaction                                                -->
    <!-- ========================================================================= -->
    <div id="reversalModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
      <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full border border-outline-variant/60 shadow-xl overflow-hidden">
        <div class="p-6">
          <div class="w-12 h-12 rounded-full bg-error-container text-on-error-container flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[28px] text-error">undo</span>
          </div>
          <h3 class="text-title-md font-bold text-on-surface">Reverse Refund?</h3>
          <p class="text-body-sm text-on-surface-variant mt-1" id="reversal_voucher_subtext">You are reversing voucher <span class="font-mono font-semibold" id="reversal_txn_number"></span>.</p>
          <div class="p-3 my-3 rounded-lg bg-error/10 border border-error/20 text-body-sm text-error">
            This will create an equal and opposite reversal journal entry and reinstate account balances non-destructively.
          </div>

          <form method="post" action="<?php echo site_url('finance/reverse_transaction'); ?>" class="space-y-4">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"/>
            <input type="hidden" name="transaction_id" id="reversal_transaction_id" value="0"/>
            <input type="hidden" name="return_url" value="finance/refunds"/>

            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Reason for Reversal <span class="text-error">*</span></label>
              <textarea name="reason" rows="3" required placeholder="State exact reason for reversing this refund..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-error"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button type="button" onclick="closeReversalModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-body-sm font-medium hover:bg-surface-container-high">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 rounded-lg bg-error text-white text-body-sm font-semibold hover:bg-error/90">
                Confirm Reversal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Client Script -->
    <script>
      function openRefundModal() {
        document.getElementById('refundModal').classList.remove('hidden');
      }
      function closeRefundModal() {
        document.getElementById('refundModal').classList.add('hidden');
      }

      function toggleRefundPartySelector() {
        const type = document.getElementById('refund_party_type').value;
        const studentBox = document.getElementById('refund_party_student_box');
        const staffBox = document.getElementById('refund_party_staff_box');

        studentBox.classList.add('hidden');
        staffBox.classList.add('hidden');

        if (type === 'Student') {
          studentBox.classList.remove('hidden');
        } else if (type === 'Staff') {
          staffBox.classList.remove('hidden');
        }
      }

      function handleRefundStudentSelect() {
        const select = document.getElementById('refund_party_student_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
          document.getElementById('refund_party_name').value = opt.getAttribute('data-name');
          document.getElementById('refund_party_id').value = opt.value;
        }
      }

      function handleRefundStaffSelect() {
        const select = document.getElementById('refund_party_staff_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
          document.getElementById('refund_party_name').value = opt.getAttribute('data-name');
          document.getElementById('refund_party_id').value = opt.value;
        }
      }

      function viewVoucher(id) {
        document.getElementById('viewVoucherModal').classList.remove('hidden');
        const content = document.getElementById('voucher_content_area');
        content.innerHTML = '<div class="py-8 text-center text-on-surface-variant"><span class="material-symbols-outlined animate-spin text-[32px] text-primary">progress_activity</span><p class="mt-2 text-body-sm">Loading voucher lines...</p></div>';

        fetch('<?php echo site_url("finance/view_transaction_ajax"); ?>/' + id)
          .then(res => res.json())
          .then(data => {
            if (!data.success || !data.transaction) {
              content.innerHTML = '<div class="p-4 rounded-lg bg-error-container text-on-error-container text-body-sm">' + (data.message || 'Error loading transaction.') + '</div>';
              return;
            }
            const txn = data.transaction;
            document.getElementById('voucher_number_display').innerText = txn.transaction_number;
            document.getElementById('voucher_header_title').innerText = txn.transaction_type + ' Voucher';

            let itemsHtml = '';
            let totalDebit = 0, totalCredit = 0;
            if (txn.items && txn.items.length) {
              txn.items.forEach(it => {
                const dr = parseFloat(it.debit || 0);
                const cr = parseFloat(it.credit || 0);
                totalDebit += dr;
                totalCredit += cr;
                itemsHtml += `
                  <tr class="border-b border-outline-variant/40">
                    <td class="py-2.5 px-3">
                      <div class="font-semibold text-on-surface text-body-sm">${it.account_name}</div>
                      <div class="text-[11px] font-mono text-on-surface-variant">${it.account_code} &bull; ${it.group_name || ''}</div>
                    </td>
                    <td class="py-2.5 px-3 text-center text-[12px] font-medium ${it.entry_type === 'Debit' ? 'text-error font-bold' : 'text-secondary font-bold'}">
                      ${it.entry_type}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-body-sm font-semibold text-on-surface">
                      ${dr > 0 ? '₹' + dr.toFixed(2) : '-'}
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono text-body-sm font-semibold text-on-surface">
                      ${cr > 0 ? '₹' + cr.toFixed(2) : '-'}
                    </td>
                  </tr>
                `;
              });
            }

            content.innerHTML = `
              <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-surface-container-low/50 text-body-sm border border-outline-variant/40">
                <div><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Date:</span><span class="font-medium">${txn.transaction_date}</span></div>
                <div><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Payment Mode:</span><span class="font-medium">${txn.payment_method || 'Cash'}</span></div>
                <div><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Beneficiary:</span><span class="font-medium">${txn.party_name || 'General'}</span></div>
                <div><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Reference #:</span><span class="font-mono text-[12px]">${txn.reference_no || '-'}</span></div>
                ${txn.narration ? '<div class="col-span-2"><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Reason / Particulars:</span><span class="font-medium text-body-sm">' + txn.narration + '</span></div>' : ''}
              </div>

              <div class="mt-4 border border-outline-variant/50 rounded-xl overflow-hidden">
                <table class="w-full text-left text-body-sm">
                  <thead class="bg-surface-container-low/80 text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">
                    <tr>
                      <th class="py-2 px-3">Account</th>
                      <th class="py-2 px-3 text-center">Type</th>
                      <th class="py-2 px-3 text-right">Debit (₹)</th>
                      <th class="py-2 px-3 text-right">Credit (₹)</th>
                    </tr>
                  </thead>
                  <tbody>${itemsHtml}</tbody>
                  <tfoot class="bg-surface-container-low/40 font-mono font-bold text-body-sm border-t border-outline-variant/60">
                    <tr>
                      <td colspan="2" class="py-2 px-3 text-right font-sans uppercase text-[11px] text-on-surface-variant">Totals:</td>
                      <td class="py-2 px-3 text-right text-error">₹${totalDebit.toFixed(2)}</td>
                      <td class="py-2 px-3 text-right text-secondary">₹${totalCredit.toFixed(2)}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              ${txn.is_reversed == 1 ? '<div class="p-3 rounded-lg bg-error-container text-on-error-container text-[12px] font-medium"><span class="font-bold">Reversed:</span> ' + (txn.reversed_reason || 'N/A') + ' by ' + (txn.reversed_by_name || 'Admin') + '</div>' : ''}
            `;
          })
          .catch(err => {
            content.innerHTML = '<div class="p-4 rounded-lg bg-error-container text-on-error-container text-body-sm">Failed to fetch voucher details.</div>';
          });
      }
      function closeVoucherModal() {
        document.getElementById('viewVoucherModal').classList.add('hidden');
      }

      function openReversalModal(id, number) {
        document.getElementById('reversal_transaction_id').value = id;
        document.getElementById('reversal_txn_number').innerText = '#' + number;
        document.getElementById('reversalModal').classList.remove('hidden');
      }
      function closeReversalModal() {
        document.getElementById('reversalModal').classList.add('hidden');
      }
    </script>
