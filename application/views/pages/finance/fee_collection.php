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
                      <option value="<?php echo (int)$sf->id; ?>" data-due="<?php echo (float)$sf->due_amount; ?>"><?php echo html_escape($sf->invoice_number . ' (' . ($sf->fee_name ?? 'Fee') . ') - Due: ₹' . number_format($sf->due_amount, 2)); ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Amount Paid (₹) <span class="text-error">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" id="coll_amount" required placeholder="0.00" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md font-mono font-bold focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Payment Mode <span class="text-error">*</span></label>
                <select name="payment_mode" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="Cash">Cash</option>
                  <option value="UPI">UPI</option>
                  <option value="Bank Transfer">Bank Transfer / NEFT</option>
                  <option value="Cheque">Cheque</option>
                  <option value="Card">Debit / Credit Card</option>
                </select>
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Deposit To (Asset) <span class="text-error">*</span></label>
                <select name="deposit_account_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <?php if (!empty($cash_bank_accounts)): ?>
                    <?php foreach ($cash_bank_accounts as $cba): ?>
                      <option value="<?php echo (int)$cba->id; ?>"><?php echo html_escape($cba->account_name . ' (' . $cba->account_code . ')'); ?></option>
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
                <label class="block text-label-md font-semibold text-on-surface mb-1">Reference / UTR / Cheque #</label>
                <input type="text" name="reference_number" placeholder="Optional transaction ID" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
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
            <span class="material-symbols-outlined text-primary text-[22px]">info</span>Double-Entry Posting
          </h3>
          <p class="text-body-md text-on-surface-variant leading-relaxed">
            When you collect a payment:
          </p>
          <div class="mt-3 space-y-2 text-sm">
            <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
              <span class="font-semibold text-on-surface">Debit: Cash/Bank Account</span>
              <span class="font-mono text-secondary font-bold">+ Increase</span>
            </div>
            <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex justify-between items-center">
              <span class="font-semibold text-on-surface">Credit: Student Receivables (1030)</span>
              <span class="font-mono text-error font-bold">- Decrease Due</span>
            </div>
          </div>
          <p class="text-xs text-on-surface-variant mt-3 italic">
            Atomic transactions guarantee your General Ledger and Trial Balance are always 100% mathematically balanced.
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Collections Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[20px]">history</span>Recent Fee Collections
        </h3>
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
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($recent_collections)): ?>
              <?php foreach ($recent_collections as $rc): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary"><?php echo html_escape($rc->receipt_number); ?></td>
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
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-6 text-center text-on-surface-variant">No collections logged yet.</td>
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
        window.location.href = '<?php echo site_url("fees/collection"); ?>?student_id=' + encodeURIComponent(sid);
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
            if (due && (!amtInput.value || parseFloat(amtInput.value) <= 0)) {
              amtInput.value = parseFloat(due).toFixed(2);
            }
          }
        });
      }
    });
    </script>
