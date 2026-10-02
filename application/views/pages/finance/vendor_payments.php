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
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container uppercase tracking-wider">Suppliers & Creditors</span>
          <span class="text-body-sm text-on-surface-variant font-medium"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Active Session'); ?></span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Vendor Payment</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Disburse payments to vendors, suppliers, contractors, and service providers with automatic creditor ledger tracking.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openVendorPaymentModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">shopping_cart_checkout</span>Make Vendor Payment
        </button>
        <a href="<?php echo site_url('finance/ledger_other_parties'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">store</span>Vendor Ledgers
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Academic Year -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Year Total</span>
          <span class="p-2 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center">
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
          <span class="p-2 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">date_range</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['month_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-secondary font-medium mt-1"><?php echo date('F Y'); ?></div>
      </div>

      <!-- Today's Total -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Today's Payments</span>
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
          <span class="text-body-sm font-medium text-on-surface-variant">Payments</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface"><?php echo number_format($stats['total_count'] ?? 0); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Total vendor vouchers</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/vendor_payments'); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Voucher #, Vendor, Ref..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-secondary"/>
        </div>

        <!-- Vendor Selection -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Vendor / Creditor</label>
          <select name="vendor_id" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-secondary">
            <option value="">All Vendors</option>
            <?php foreach ($vendors as $v): ?>
              <option value="<?php echo $v->id; ?>" <?php echo ((int)($filters['vendor_id'] ?? 0) === (int)$v->id) ? 'selected' : ''; ?>>
                <?php echo html_escape($v->ledger_name . ' (' . $v->ledger_code . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="<?php echo html_escape($filters['date_from'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-secondary"/>
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="<?php echo html_escape($filters['date_to'] ?? ''); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-secondary"/>
        </div>

        <!-- Payment Mode -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Payment Method</label>
          <select name="payment_method" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-secondary">
            <option value="">All Methods</option>
            <option value="Bank Transfer" <?php echo (($filters['payment_mode'] ?? '') === 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
            <option value="Cheque" <?php echo (($filters['payment_mode'] ?? '') === 'Cheque') ? 'selected' : ''; ?>>Cheque</option>
            <option value="Cash" <?php echo (($filters['payment_mode'] ?? '') === 'Cash') ? 'selected' : ''; ?>>Cash</option>
            <option value="UPI" <?php echo (($filters['payment_mode'] ?? '') === 'UPI') ? 'selected' : ''; ?>>UPI</option>
            <option value="Other" <?php echo (($filters['payment_mode'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="flex items-end gap-2">
          <button type="submit" class="flex-1 px-4 py-1.5 rounded-lg bg-secondary text-on-secondary text-body-sm font-semibold hover:bg-secondary/90 transition-colors cursor-pointer">
            Filter
          </button>
          <a href="<?php echo site_url('finance/vendor_payments'); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Payments Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Vendor Disbursement History</h3>
          <p class="text-[12px] text-on-surface-variant">All payments made to suppliers, contractors, and service vendors</p>
        </div>
        <span class="text-body-sm text-on-surface-variant font-medium"><?php echo count($payments); ?> Payments</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Vendor / Creditor</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account / Head</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Paid From</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mode</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($payments)): ?>
              <?php foreach ($payments as $p): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap text-sm"><?php echo date('d-M-Y', strtotime($p->expense_date)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <button type="button" onclick="viewExpenseVoucher(<?php echo $p->id; ?>)" class="font-mono text-[13px] text-secondary font-bold hover:underline cursor-pointer">
                      <?php echo html_escape($p->expense_number); ?>
                    </button>
                    <?php if (!empty($p->reference_no)): ?>
                      <span class="block text-[10px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($p->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm">
                    <span class="font-semibold"><?php echo html_escape($p->payee_name); ?></span>
                    <?php if (!empty($p->vendor_id)): ?>
                      <a href="<?php echo site_url('finance/other_party_statement/' . $p->vendor_id); ?>" class="block text-[11px] text-secondary hover:underline">
                        View Vendor Statement &rarr;
                      </a>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm whitespace-nowrap">
                    <?php echo html_escape($p->expense_account_name ?: 'Accounts Payable'); ?>
                    <span class="block text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($p->expense_account_code); ?></span>
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
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">Paid</span>
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
                  <span class="material-symbols-outlined text-[40px] text-outline mb-2">store</span>
                  <p class="text-body-md font-medium">No vendor payments recorded yet.</p>
                  <p class="text-[12px] text-on-surface-variant mt-1">Click "Make Vendor Payment" to issue a payment to a vendor, supplier, or contractor.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- NEW VENDOR PAYMENT MODAL -->
    <div id="vendor-payment-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-lg w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[24px]">shopping_cart_checkout</span>Make Vendor Payment
          </h3>
          <button onclick="closeVendorPaymentModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('finance/vendor_payments', array('id' => 'vendor-payment-form', 'class' => 'space-y-4')); ?>
          <!-- Vendor Selection (Existing Ledger or New Vendor) -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Registered Vendor</label>
            <select name="vendor_id" id="vp-vendor-id" onchange="onVendorSelectChange(this)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
              <option value="">— Select an existing vendor ledger —</option>
              <?php foreach ($vendors as $v): ?>
                <option value="<?php echo $v->id; ?>" data-name="<?php echo html_escape($v->ledger_name); ?>">
                  <?php echo html_escape($v->ledger_name . ' (' . $v->ledger_code . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Vendor Name (Manual input or auto-filled) -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Vendor / Supplier Name *</label>
            <input type="text" name="vendor_name" id="vp-vendor-name" required placeholder="e.g. Apex Stationery Supplies, FastNet ISP, Metro Bus Transport" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary"/>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Amount (₹) *</label>
              <input type="number" step="0.01" min="1" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-secondary/20 focus:border-secondary"/>
            </div>

            <!-- Date -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Date *</label>
              <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Debit / Payable / Expense Account Head -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Debit Account Head *</label>
              <select name="debit_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                <optgroup label="Liabilities / Payables">
                  <?php foreach ($payable_accounts as $pa): ?>
                    <option value="<?php echo $pa->id; ?>" <?php echo ($pa->account_code === '2020') ? 'selected' : ''; ?>>
                      <?php echo html_escape($pa->account_name . ' (' . $pa->account_code . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
                <optgroup label="Direct Operational Expenses">
                  <?php foreach ($expense_accounts as $ea): ?>
                    <option value="<?php echo $ea->id; ?>">
                      <?php echo html_escape($ea->account_name . ' (' . $ea->account_code . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              </select>
            </div>

            <!-- Paid From Account -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Disbursed From (Bank/Cash) *</label>
              <select name="paid_from_account_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                <option value="">Select Account</option>
                <?php foreach ($cash_bank_accounts as $cba): ?>
                  <option value="<?php echo $cba->id; ?>">
                    <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ') [₹' . number_format($cba->current_balance ?? 0, 2) . ']'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Payment Mode -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Payment Mode *</label>
              <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                <option value="Cheque">Cheque</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI / QR</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Reference / Invoice # -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Invoice / UTR / Cheque Ref #</label>
              <input type="text" name="reference_no" placeholder="e.g. INV-84920 or CHQ-0021" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-secondary/20 focus:border-secondary"/>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Narration / Bill Particulars</label>
            <textarea name="description" rows="2" placeholder="e.g. Clearance of invoice #INV-84920 for exam answer sheets and stationery supplies" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload Vendor Invoice / Payment Proof (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-secondary file:text-on-secondary hover:file:bg-secondary/90"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeVendorPaymentModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
              Post Vendor Payment
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- VIEW VENDOR PAYMENT VOUCHER MODAL -->
    <div id="view-voucher-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-2xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div>
            <span id="vv-submodule-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-container-high text-on-surface">VENDOR PAYMENT</span>
            <h3 class="font-headline-md text-title-lg text-on-surface font-semibold mt-1" id="vv-title">Payment Voucher</h3>
          </div>
          <button onclick="closeViewVoucherModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="space-y-4 text-body-sm" id="vv-content">
          <div class="p-8 text-center text-on-surface-variant">Loading payment data...</div>
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
            <span class="material-symbols-outlined text-error text-[24px]">undo</span>Reverse Vendor Payment
          </h3>
          <button onclick="closeExpenseReversalModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/reverse_expense', array('id' => 'expense-reversal-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="expense_id" id="rev-expense-id" value="0"/>
          <input type="hidden" name="return_url" value="finance/vendor_payments"/>

          <p class="text-body-sm text-on-surface-variant">
            You are reversing vendor payment <strong id="rev-expense-number" class="text-error font-mono"></strong> for amount <strong id="rev-expense-amount" class="text-on-surface font-mono"></strong>.
            This will reverse the payment in both the Cash/Bank ledger and the Vendor's account ledger.
          </p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reversal Reason *</label>
            <textarea name="reversal_reason" rows="2" required placeholder="State why this vendor payment is being reversed..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-error/20 focus:border-error"></textarea>
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
      function onVendorSelectChange(select) {
        var opt = select.options[select.selectedIndex];
        var nameInput = document.getElementById('vp-vendor-name');
        if (opt && opt.getAttribute('data-name')) {
          nameInput.value = opt.getAttribute('data-name');
        }
      }

      function openVendorPaymentModal() {
        var m = document.getElementById('vendor-payment-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeVendorPaymentModal() {
        var m = document.getElementById('vendor-payment-modal');
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
            document.getElementById('vv-submodule-badge').textContent = 'VENDOR PAYMENT';

            var html = '';
            html += '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payment Date</span><span class="font-semibold text-on-surface">' + (d.expense_date || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Disbursed Amount</span><span class="font-mono font-bold text-secondary">₹' + parseFloat(d.amount).toFixed(2) + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Payment Mode</span><span class="font-semibold text-on-surface">' + (d.payment_mode || 'Bank') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Status</span><span class="font-bold ' + (d.status === 'Reversed' ? 'text-error' : 'text-secondary') + '">' + (d.status || 'Paid') + '</span></div>';
            html += '</div>';

            html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Vendor / Creditor</span><strong class="text-on-surface">' + (d.payee_name || '—') + '</strong></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Reference / Invoice #</span><span class="font-mono text-on-surface">' + (d.reference_no || '—') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Debit Head</span><span class="text-on-surface">' + (d.expense_account_name || 'Accounts Payable') + '</span></div>';
            html += '<div><span class="block text-[11px] text-on-surface-variant font-medium">Paid From</span><span class="text-on-surface">' + (d.payment_account_name || '—') + '</span></div>';
            html += '</div>';

            if (d.description) {
              html += '<div class="p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/30"><span class="block text-[11px] text-on-surface-variant font-medium">Description / Narration</span><p class="text-on-surface text-sm mt-0.5">' + d.description + '</p></div>';
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
                html += '<td class="p-2.5 font-medium text-on-surface">' + (l.account_name || '') + ' <span class="text-on-surface-variant font-mono">(' + (l.account_code || '') + ')</span>' + (l.ledger_name ? '<span class="block text-[10px] text-secondary font-medium">' + l.ledger_name + '</span>' : '') + '</td>';
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
