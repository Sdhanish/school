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

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Fee Collection & Receipts</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Accept student fee payments, apply them to outstanding invoices, and post balanced double-entry vouchers.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/fee_receipts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">receipt</span>Receipts History
        </a>
      </div>
    </div>

    <!-- Student Lookup & Collection Form Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Collection Form Card -->
      <div class="lg:col-span-2 elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[22px]">add_card</span>Record Payment
        </h3>
        <form method="post" action="<?php echo site_url('finance/fee_collection'); ?>">
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Student ID / Admission No <span class="text-error">*</span></label>
                <div class="flex gap-2">
                  <input type="number" name="student_id" id="coll_student_id" required value="<?php echo !empty($student) ? (int)$student->student_id : ''; ?>" placeholder="Enter Student ID" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <button type="button" onclick="loadStudentFeeData()" class="px-3.5 py-2 rounded-lg bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-label-md font-semibold text-on-surface cursor-pointer shrink-0 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">search</span>Load
                  </button>
                </div>
                <?php if (!empty($student)): ?>
                  <div class="text-xs text-secondary font-semibold mt-1">Student: <?php echo html_escape($student->first_name . ' ' . $student->last_name . ' (' . ($student->admission_number ?? $student->admission_no ?? '') . ')'); ?></div>
                <?php endif; ?>
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Linked Invoice (Optional)</label>
                <select name="fee_assignment_id" id="coll_fee_assignment_id" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="">-- General / On-Account Payment --</option>
                  <?php if (!empty($student_fees)): ?>
                    <?php foreach ($student_fees as $sf): ?>
                      <?php $is_sel = (!empty($selected_assignment_id) && (int)$sf->id === (int)$selected_assignment_id); ?>
                      <option value="<?php echo (int)$sf->id; ?>" data-due="<?php echo (float)$sf->due_amount; ?>" <?php echo $is_sel ? 'selected' : ''; ?>>
                        <?php echo html_escape($sf->invoice_number . ' (' . ($sf->fee_name ?? 'Fee') . ') - Due: ₹' . number_format($sf->due_amount, 2)); ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Amount Paid (₹) <span class="text-error">*</span></label>
                <?php 
                  $default_amount = '';
                  if (!empty($selected_assignment_id) && !empty($student_fees)) {
                    foreach ($student_fees as $sf) {
                      if ((int)$sf->id === (int)$selected_assignment_id && (float)$sf->due_amount > 0) {
                        $default_amount = number_format((float)$sf->due_amount, 2, '.', '');
                        break;
                      }
                    }
                  }
                ?>
                <input type="number" step="0.01" min="0.01" name="amount" id="coll_amount" required value="<?php echo $default_amount; ?>" placeholder="0.00" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md font-mono font-bold focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Payment Mode <span class="text-error">*</span></label>
                <select name="payment_mode" id="coll_payment_mode" required onchange="onPaymentModeChange()" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="Cash" data-account-target="Cash">💵 Cash</option>
                  <option value="Bank" data-account-target="Bank">🏦 Bank / Direct Bank</option>
                  <option value="UPI" data-account-target="Bank">📱 UPI / QR Code</option>
                  <option value="Card" data-account-target="Bank">💳 Card (Debit / Credit)</option>
                  <option value="Cheque" data-account-target="Bank">📄 Cheque / DD</option>
                  <option value="Bank Transfer" data-account-target="Bank">🔁 Bank Transfer (NEFT / RTGS / IMPS)</option>
                </select>
              </div>
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-label-md font-semibold text-on-surface">Deposit To (Asset) <span class="text-error">*</span></label>
                  <span id="deposit_type_badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary-container text-on-primary-container">Cash</span>
                </div>
                <select name="deposit_account_id" id="coll_deposit_account_id" required onchange="onDepositAccountChange()" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <?php if (!empty($cash_accounts)): ?>
                    <optgroup label="💵 Cash Accounts">
                      <?php foreach ($cash_accounts as $ca): ?>
                        <option value="<?php echo (int)$ca->id; ?>" data-account-type="Cash" <?php echo ($ca->account_code === '1010' ? 'selected' : ''); ?>>
                          <?php echo html_escape($ca->account_name . ' (' . $ca->account_code . ')'); ?>
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                  <?php if (!empty($bank_accounts)): ?>
                    <optgroup label="🏦 Bank Accounts">
                      <?php foreach ($bank_accounts as $ba): ?>
                        <option value="<?php echo (int)$ba->id; ?>" data-account-type="Bank">
                          <?php echo html_escape($ba->account_name . ' (' . $ba->account_code . ($ba->bank_name ? ' — ' . $ba->bank_name : '') . ')'); ?>
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                  <?php if (empty($cash_accounts) && empty($bank_accounts) && !empty($cash_bank_accounts)): ?>
                    <?php foreach ($cash_bank_accounts as $cba): ?>
                      <option value="<?php echo (int)$cba->id; ?>" data-account-type="<?php echo html_escape($cba->account_type); ?>">
                        <?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ')'); ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Collection Date</label>
                <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              </div>
              <div>
                <label id="ref_number_label" class="block text-label-md font-semibold text-on-surface mb-1">Reference / UTR / Cheque #</label>
                <input type="text" name="reference_number" id="coll_reference_number" placeholder="Optional transaction ID" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              </div>
            </div>

            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Remarks / Note</label>
              <textarea name="remarks" rows="2" placeholder="Optional receipt notes" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden"></textarea>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-outline-variant/40">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-secondary text-on-secondary hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer text-label-md font-semibold flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">receipt_long</span>Collect & Post Journal
            </button>
          </div>
        </form>
      </div>

      <!-- Quick Info & Student Summary Column -->
      <div class="space-y-6">
        <?php if (!empty($student)): ?>
          <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6">
            <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[22px]">account_circle</span>Student Fee Summary
            </h3>
            <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 mb-4">
              <div class="font-bold text-on-surface text-base"><?php echo html_escape($student->first_name . ' ' . $student->last_name); ?></div>
              <div class="text-xs text-on-surface-variant mt-0.5">Adm #: <span class="font-mono font-semibold"><?php echo html_escape($student->admission_number ?? $student->admission_no ?? ''); ?></span></div>
              <div class="text-xs text-on-surface-variant">Class: <span class="font-semibold"><?php echo html_escape(($student->class_name ?? 'N/A') . (!empty($student->division_name) ? ' - ' . $student->division_name : '')); ?></span></div>
            </div>

            <div class="space-y-2 text-sm">
              <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
                <span class="text-on-surface-variant">Total Applicable:</span>
                <span class="font-mono text-on-surface font-bold">₹<?php echo number_format($fee_summary['total_applicable'] ?? 0, 2); ?></span>
              </div>
              <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
                <span class="text-on-surface-variant">Total Paid:</span>
                <span class="font-mono text-secondary font-bold">₹<?php echo number_format($fee_summary['total_paid'] ?? 0, 2); ?></span>
              </div>
              <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
                <span class="font-semibold text-on-surface">Outstanding Due:</span>
                <span class="font-mono text-error font-bold text-base">₹<?php echo number_format($fee_summary['total_due'] ?? 0, 2); ?></span>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex justify-between items-center">
              <a href="<?php echo site_url('finance/student_statement/' . $student->student_id); ?>" class="text-xs text-primary hover:underline font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">receipt_long</span>View Student Statement
              </a>
            </div>
          </div>
        <?php endif; ?>

        <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">account_balance</span>Double-Entry Posting
          </h3>
          <p class="text-body-md text-on-surface-variant leading-relaxed">
            When fee payment is collected:
          </p>
          <div class="mt-3 space-y-2 text-sm">
            <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
              <div>
                <span class="font-semibold text-on-surface block">DR: Cash / Bank Account</span>
                <span class="text-[11px] text-on-surface-variant">Asset Increase (+ Cash in Hand / Bank)</span>
              </div>
              <span class="font-mono text-secondary font-bold">+ Increase</span>
            </div>
            <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
              <div>
                <span class="font-semibold text-on-surface block">CR: Student Accounts Receivable</span>
                <span class="text-[11px] text-on-surface-variant">Account 1030 (Reduces student balance due)</span>
              </div>
              <span class="font-mono text-error font-bold">- Decrease Due</span>
            </div>
          </div>
          <p class="text-xs text-on-surface-variant mt-3 italic">
            Automated double-entry guarantees your General Ledger and Trial Balance remain 100% mathematically balanced.
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Collections Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[20px]">history</span>Recent Fee Collections
        </h3>
        <a href="<?php echo site_url('finance/fee_receipts'); ?>" class="text-xs text-primary hover:underline font-semibold flex items-center gap-1">
          View All Receipts &rarr;
        </a>
      </div>
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Receipt #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Student</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount Paid</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mode</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Deposited In</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($recent_collections)): ?>
              <?php foreach ($recent_collections as $rc): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary">
                    <a href="<?php echo site_url('finance/student_receipt/' . $rc->id); ?>" class="hover:underline">
                      <?php echo html_escape($rc->receipt_number); ?>
                    </a>
                  </td>
                  <td class="px-4 py-3 font-medium text-on-surface"><?php echo html_escape($rc->first_name . ' ' . $rc->last_name); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-secondary">₹<?php echo number_format($rc->amount, 2); ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface"><?php echo html_escape($rc->payment_mode); ?></span>
                  </td>
                  <td class="px-4 py-3 text-sm text-on-surface-variant"><?php echo html_escape($rc->deposit_account_name); ?></td>
                  <td class="px-4 py-3 text-center font-mono text-sm"><?php echo date('d M Y', strtotime($rc->receipt_date)); ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                      <?php echo html_escape($rc->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?php echo site_url('finance/student_receipt/' . $rc->id); ?>" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-block" title="View Receipt">
                      <span class="material-symbols-outlined text-[18px]">receipt</span>
                    </a>
                    <a href="<?php echo site_url('finance/receipt_print/' . $rc->id); ?>" target="_blank" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors inline-block" title="Print Receipt">
                      <span class="material-symbols-outlined text-[18px]">print</span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="px-4 py-6 text-center text-on-surface-variant">No collections logged yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <script>
    function loadStudentFeeData() {
      var sid = document.getElementById('coll_student_id').value;
      if (sid) {
        window.location.href = '<?php echo site_url("finance/fee_collection"); ?>?student_id=' + encodeURIComponent(sid);
      }
    }

    function onDepositAccountChange() {
      var accSelect = document.getElementById('coll_deposit_account_id');
      var typeBadge = document.getElementById('deposit_type_badge');
      if (!accSelect || !typeBadge) return;
      var opt = accSelect.options[accSelect.selectedIndex];
      if (opt) {
        var aType = opt.getAttribute('data-account-type') || 'Asset';
        typeBadge.textContent = aType;
        if (aType === 'Cash') {
          typeBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-primary-container text-on-primary-container';
        } else {
          typeBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-secondary-container text-on-secondary-container';
        }
      }
    }

    function onPaymentModeChange() {
      var modeSelect = document.getElementById('coll_payment_mode');
      var accSelect = document.getElementById('coll_deposit_account_id');
      var refLabel = document.getElementById('ref_number_label');
      var refInput = document.getElementById('coll_reference_number');

      if (!modeSelect || !accSelect) return;
      var mode = modeSelect.value;
      var targetType = (mode === 'Cash') ? 'Cash' : 'Bank';

      // Auto-switch deposit account to matching category if currently mismatched
      var currentOpt = accSelect.options[accSelect.selectedIndex];
      var currentType = currentOpt ? currentOpt.getAttribute('data-account-type') : null;
      if (currentType !== targetType) {
        for (var i = 0; i < accSelect.options.length; i++) {
          if (accSelect.options[i].getAttribute('data-account-type') === targetType) {
            accSelect.selectedIndex = i;
            break;
          }
        }
      }

      onDepositAccountChange();

      // Dynamic placeholder & label
      if (refLabel && refInput) {
        switch (mode) {
          case 'Cash':
            refLabel.textContent = 'Reference / Receipt Memo (Optional)';
            refInput.placeholder = 'Optional counter receipt or voucher memo';
            break;
          case 'Bank':
            refLabel.textContent = 'Bank Ref / Deposit Slip #';
            refInput.placeholder = 'e.g. Bank deposit slip or transaction ref';
            break;
          case 'Bank Transfer':
            refLabel.textContent = 'NEFT / RTGS / IMPS Reference #';
            refInput.placeholder = 'e.g. 16-digit NEFT/RTGS UTR';
            break;
          case 'UPI':
            refLabel.textContent = 'UPI Reference ID / UTR';
            refInput.placeholder = 'e.g. 12-digit UPI UTR number';
            break;
          case 'Card':
            refLabel.textContent = 'Card Auth / POS Slip / Last 4 Digits';
            refInput.placeholder = 'e.g. Approval code or Card •••• 1234';
            break;
          case 'Cheque':
            refLabel.textContent = 'Cheque Number & Drawee Bank';
            refInput.placeholder = 'e.g. Chq #000123, SBI Bank, dated DD/MM';
            break;
        }
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      var invoiceSelect = document.getElementById('coll_fee_assignment_id');
      var amtInput = document.getElementById('coll_amount');
      if (invoiceSelect && amtInput) {
        invoiceSelect.addEventListener('change', function() {
          var opt = this.options[this.selectedIndex];
          if (opt) {
            var due = opt.getAttribute('data-due');
            if (due && parseFloat(due) > 0) {
              amtInput.value = parseFloat(due).toFixed(2);
            }
          }
        });
      }
      onPaymentModeChange();
    });
    </script>
