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
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-tertiary-container text-on-tertiary-container uppercase tracking-wider">Petty Cash & Miscellaneous</span>
          <span class="text-body-sm text-on-surface-variant font-medium"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Active Session'); ?></span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Other Expenses</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Manage petty cash, emergency purchases, event contingencies, and miscellaneous approved expenses requiring justification.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openOtherExpenseModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-tertiary text-on-tertiary text-label-md font-semibold hover:bg-tertiary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Record Other Expense
        </button>
        <a href="<?php echo site_url('finance/cash_bank'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>Petty Cash Accounts
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Academic Year -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Year Total</span>
          <span class="p-2 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center">
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
          <span class="text-body-sm font-medium text-on-surface-variant">Today's Total</span>
          <span class="p-2 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">today</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['today_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1"><?php echo date('d M Y'); ?></div>
      </div>

      <!-- Total Count -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Vouchers</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface"><?php echo number_format($stats['total_count'] ?? 0); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Total recorded vouchers</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/other_expenses'); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Voucher #, Payee, Reason..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary"/>
        </div>

        <!-- Expense Head -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Expense Head</label>
          <select name="expense_account_id" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary">
            <option value="">All Expense Heads</option>
            <?php foreach ($expense_accounts as $acc): ?>
              <option value="<?php echo $acc->id; ?>" <?php echo ((int)($filters['expense_account_id'] ?? 0) === (int)$acc->id) ? 'selected' : ''; ?>>
                <?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="<?php echo html_escape($filters['date_from'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary"/>
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="<?php echo html_escape($filters['date_to'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary"/>
        </div>

        <!-- Payment Mode -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Payment Method</label>
          <select name="payment_method" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary">
            <option value="">All Methods</option>
            <option value="Cash" <?php echo (($filters['payment_mode'] ?? '') === 'Cash') ? 'selected' : ''; ?>>Cash</option>
            <option value="Bank Transfer" <?php echo (($filters['payment_mode'] ?? '') === 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
            <option value="UPI" <?php echo (($filters['payment_mode'] ?? '') === 'UPI') ? 'selected' : ''; ?>>UPI</option>
            <option value="Cheque" <?php echo (($filters['payment_mode'] ?? '') === 'Cheque') ? 'selected' : ''; ?>>Cheque</option>
            <option value="Card" <?php echo (($filters['payment_mode'] ?? '') === 'Card') ? 'selected' : ''; ?>>Card</option>
            <option value="Other" <?php echo (($filters['payment_mode'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 px-4 py-1.5 rounded-lg bg-tertiary text-on-tertiary text-body-sm font-semibold hover:bg-tertiary/90 transition-colors cursor-pointer">
            Filter
          </button>
          <a href="<?php echo site_url('finance/other_expenses'); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Expenses Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Other & Miscellaneous Expenses</h3>
          <p class="text-[12px] text-on-surface-variant">Approved miscellaneous expenses with mandatory justification reasons</p>
        </div>
        <span class="text-body-sm text-on-surface-variant font-medium"><?php echo count($expenses); ?> Vouchers</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Payee & Reason</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Expense Head</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Paid From</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mode</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($expenses)): ?>
              <?php foreach ($expenses as $exp): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap text-sm"><?php echo date('d-M-Y', strtotime($exp->expense_date)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <button type="button" onclick="viewExpenseVoucher(<?php echo $exp->id; ?>)" class="font-mono text-[13px] text-tertiary font-bold hover:underline cursor-pointer">
                      <?php echo html_escape($exp->expense_number); ?>
                    </button>
                    <?php if (!empty($exp->reference_no)): ?>
                      <span class="block text-[10px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($exp->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm">
                    <span class="font-semibold"><?php echo html_escape($exp->payee_name); ?></span>
                    <?php if (!empty($exp->reason)): ?>
                      <span class="block text-[11px] text-tertiary font-medium">Reason: <?php echo html_escape($exp->reason); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($exp->description)): ?>
                      <span class="block text-[10px] text-on-surface-variant truncate max-w-xs"><?php echo html_escape($exp->description); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm whitespace-nowrap">
                    <?php echo html_escape($exp->expense_account_name ?: 'Miscellaneous Expense'); ?>
                    <span class="block text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($exp->expense_account_code); ?></span>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm whitespace-nowrap">
                    <?php echo html_escape($exp->payment_account_name ?: 'Cash / Petty Cash'); ?>
                    <span class="block text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($exp->payment_account_code); ?></span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($exp->amount, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($exp->payment_mode); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($exp->status === 'Reversed'): ?>
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">Reversed</span>
                    <?php else: ?>
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">Paid</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      <button type="button" onclick="viewExpenseVoucher(<?php echo $exp->id; ?>)" title="View Voucher Details" class="p-1 rounded hover:bg-surface-container-high text-primary cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </button>
                      <?php if (!empty($exp->attachment)): ?>
                        <a href="<?php echo base_url($exp->attachment); ?>" target="_blank" title="View Attachment" class="p-1 rounded hover:bg-surface-container-high text-on-surface-variant">
                          <span class="material-symbols-outlined text-[18px]">attach_file</span>
                        </a>
                      <?php endif; ?>
                      <?php if ($exp->status !== 'Reversed'): ?>
                        <button type="button" onclick="openExpenseReversalModal(<?php echo $exp->id; ?>, '<?php echo html_escape($exp->expense_number); ?>', '<?php echo number_format($exp->amount, 2); ?>')" title="Reverse / Void Voucher" class="p-1 rounded hover:bg-surface-container-high text-error cursor-pointer">
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
                  <span class="material-symbols-outlined text-[40px] text-outline mb-2">savings</span>
                  <p class="text-body-md font-medium">No other expense vouchers recorded.</p>
                  <p class="text-[12px] text-on-surface-variant mt-1">Click "Record Other Expense" to enter petty cash or emergency operational expenses.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- NEW OTHER EXPENSE MODAL -->
    <div id="other-expense-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-lg w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-tertiary text-[24px]">add_circle</span>Record Other / Miscellaneous Expense
          </h3>
          <button onclick="closeOtherExpenseModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('finance/other_expenses', array('id' => 'other-expense-form', 'class' => 'space-y-4')); ?>
          <!-- Mandatory Reason -->
          <div class="p-3.5 rounded-xl bg-tertiary-container/30 border border-tertiary/20">
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold flex items-center gap-1.5">
              <span class="material-symbols-outlined text-tertiary text-[18px]">verified</span>Reason / Justification *
            </label>
            <input type="text" name="reason" required placeholder="e.g. Emergency plumbing pipe repair in student hostel, Refreshments for inspector visit" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"/>
            <span class="text-[11px] text-on-surface-variant mt-1 block">A clear justification is strictly required for miscellaneous and petty cash expenses.</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expense Amount (₹) *</label>
              <input type="number" step="0.01" min="0.5" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"/>
            </div>

            <!-- Date -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expense Date *</label>
              <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Expense Account Head -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expense Account Head *</label>
              <select name="expense_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary">
                <option value="">Select Account</option>
                <?php foreach ($expense_accounts as $ea): ?>
                  <option value="<?php echo $ea->id; ?>">
                    <?php echo html_escape($ea->account_name . ' (' . $ea->account_code . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Paid From Account -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Paid From (Cash/Bank) *</label>
              <select name="paid_from_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary">
                <option value="">Select Account</option>
                <?php foreach ($cash_bank_accounts as $cba): ?>
                  <option value="<?php echo $cba->id; ?>">
                    <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ') [₹' . number_format($cba->current_balance ?? 0, 2) . ']'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Paid To -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Paid To / Payee Name</label>
            <input type="text" name="payee_name" placeholder="e.g. City Hardware Store, Local Carpenter, Driver Daily Allowance" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"/>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payment Mode -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Mode *</label>
              <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary">
                <option value="Cash">Cash / Petty Cash</option>
                <option value="UPI">UPI / QR Code</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
                <option value="Card">Card</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Reference Number -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Bill / Cash Memo / Ref #</label>
              <input type="text" name="reference_no" placeholder="e.g. CASH-MEMO-201" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"/>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Additional Description (Optional)</label>
            <textarea name="description" rows="2" placeholder="Additional notes or specifics..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-tertiary/20 focus:border-tertiary"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload Receipt / Bill Attachment (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-tertiary file:text-on-tertiary hover:file:bg-tertiary/90"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeOtherExpenseModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-tertiary text-on-tertiary text-label-md font-semibold hover:bg-tertiary/90 transition-colors shadow-sm cursor-pointer">
              Post Other Expense
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- VIEW OTHER EXPENSE VOUCHER MODAL -->
    <div id="view-voucher-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-2xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div>
            <span id="vv-submodule-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-container-high text-on-surface">OTHER EXPENSE</span>
            <h3 class="font-headline-md text-title-lg text-on-surface font-semibold mt-1" id="vv-title">Expense Voucher</h3>
          </div>
          <button onclick="closeViewVoucherModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="space-y-4 text-body-sm" id="vv-content">
          <div class="p-8 text-center text-on-surface-variant">Loading voucher data...</div>
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
            <span class="material-symbols-outlined text-error text-[24px]">undo</span>Reverse Expense Voucher
          </h3>
          <button onclick="closeExpenseReversalModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/reverse_expense', array('id' => 'expense-reversal-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="expense_id" id="rev-expense-id" value="0"/>
          <input type="hidden" name="return_url" value="finance/other_expenses"/>

          <p class="text-body-sm text-on-surface-variant">
            You are reversing expense voucher <strong id="rev-expense-number" class="text-error font-mono"></strong> for amount <strong id="rev-expense-amount" class="text-on-surface font-mono"></strong>.
            This will reverse the entry in the General Ledger.
          </p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reversal Reason *</label>
            <textarea name="reversal_reason" rows="2" required placeholder="State why this expense voucher is being reversed..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-error/20 focus:border-error"></textarea>
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
      function openOtherExpenseModal() {
        var m = document.getElementById('other-expense-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeOtherExpenseModal() {
        var m = document.getElementById('other-expense-modal');
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
            document.getElementById('vv-submodule-badge').textContent = 'OTHER EXPENSE';

            var html = '';
            html += '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Expense Date</span><span class="font-semibold text-on-surface">' + (d.expense_date || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Disbursed Amount</span><span class="font-mono font-bold text-tertiary">₹' + parseFloat(d.amount).toFixed(2) + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payment Mode</span><span class="font-semibold text-on-surface">' + (d.payment_mode || 'Cash') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Status</span><span class="font-bold ' + (d.status === 'Reversed' ? 'text-error' : 'text-secondary') + '">' + (d.status || 'Paid') + '</span></div>';
            html += '</div>';

            html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payee</span><strong class="text-on-surface">' + (d.payee_name || '—') + '</strong></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Reference Number</span><span class="font-mono text-on-surface">' + (d.reference_no || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Expense Account Head</span><span class="text-on-surface">' + (d.expense_account_name || 'Miscellaneous') + ' (' + (d.expense_account_code || '') + ')</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Paid From</span><span class="text-on-surface">' + (d.payment_account_name || '—') + '</span></div>';
            html += '</div>';

            if (d.reason) {
              html += '<div class="p-3 rounded-lg bg-tertiary-container/30 border border-tertiary/20"><span class="block text-[11px] text-tertiary font-bold uppercase">Mandatory Reason / Justification</span><p class="text-on-surface text-sm mt-0.5 font-medium">' + d.reason + '</p></div>';
            }

            if (d.description) {
              html += '<div class="p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/30"><span class="block text-[11px] text-on-surface-variant font-medium">Description / Particulars</span><p class="text-on-surface text-sm mt-0.5">' + d.description + '</p></div>';
            }

            // Double Entry Lines
            if (d.lines && d.lines.length > 0) {
              html += '<div class="mt-4"><h4 class="font-semibold text-on-surface text-sm mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-primary">account_balance</span>Double-Entry Accounting Postings</h4>';
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
