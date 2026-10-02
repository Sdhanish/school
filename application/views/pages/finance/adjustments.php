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
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-tertiary-container text-on-tertiary-container uppercase tracking-wider">Audit & Corrections</span>
          <span class="text-body-sm text-on-surface-variant font-medium"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Active Session'); ?></span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Accounting Adjustments</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Post corrective debit/credit adjustments, write-offs, depreciation, and ledger rectifications with mandatory justification.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openAdjustmentModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-tertiary text-on-tertiary text-label-md font-semibold hover:bg-tertiary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">tune</span>Post Adjustment
        </button>
        <a href="<?php echo site_url('finance/journal_entries'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">post_add</span>Journal Entries
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Year Adjustments -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-sm font-medium text-on-surface-variant">Session Adjustments</span>
          <span class="p-2 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">calendar_today</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface">₹<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1"><?php echo html_escape($current_academic_year->academic_year_name ?? 'Current Academic Year'); ?></div>
      </div>

      <!-- This Month -->
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

      <!-- Today's Adjustments -->
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
          <span class="text-body-sm font-medium text-on-surface-variant">Adjustment Entries</span>
          <span class="p-2 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface"><?php echo number_format($stats['total_count'] ?? 0); ?></div>
        <div class="text-[12px] text-on-surface-variant mt-1">Audit-tracked vouchers</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('finance/adjustments'); ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant uppercase mb-1">Search</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Voucher #, Reason, Ref..." class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"/>
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
          <a href="<?php echo site_url('finance/adjustments'); ?>" class="p-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Reset Filters">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
          </a>
        </div>
      </form>
    </div>

    <!-- Adjustments Registry Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <div>
          <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Adjustments Registry</h3>
          <p class="text-body-sm text-on-surface-variant">Showing balanced accounting corrections and ledger adjustments</p>
        </div>
        <span class="text-body-sm font-medium text-on-surface-variant"><?php echo count($transactions); ?> Records</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Adjustment Route (Dr &rarr; Cr)</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Mandatory Reason & Narration</th>
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
                  <td class="px-4 py-3 font-mono text-[13px] text-tertiary font-semibold whitespace-nowrap">
                    <?php echo html_escape($t->transaction_number); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-body-sm">
                    <div class="flex items-center gap-1 font-mono text-[12px]">
                      <span class="text-secondary font-semibold"><?php echo html_escape($t->debit_accounts_str ?: 'Debit Account'); ?></span>
                      <span class="material-symbols-outlined text-[14px] text-on-surface-variant">arrow_forward</span>
                      <span class="text-primary font-semibold"><?php echo html_escape($t->credit_accounts_str ?: 'Credit Account'); ?></span>
                    </div>
                    <?php if (!empty($t->reference_no)): ?>
                      <div class="text-[11px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($t->reference_no); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface">
                    <div class="font-medium text-body-sm text-on-surface line-clamp-1"><?php echo html_escape($t->narration ?: 'No narration provided'); ?></div>
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
                        <button onclick="openReversalModal(<?php echo $t->id; ?>, '<?php echo html_escape($t->transaction_number); ?>')" class="inline-flex items-center gap-1 text-[12px] font-semibold text-error hover:underline cursor-pointer" title="Reverse Adjustment">
                          <span class="material-symbols-outlined text-[16px]">undo</span>Reverse
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-12 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[36px] text-outline-variant mb-2 block">tune</span>
                  No accounting adjustments found for the selected criteria.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: Post Adjustment                                                    -->
    <!-- ========================================================================= -->
    <div id="adjustmentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
      <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full border border-outline-variant/60 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/40 bg-surface-container-low/40">
          <div>
            <h3 class="text-title-md font-bold text-on-surface">Post Accounting Adjustment</h3>
            <p class="text-body-sm text-on-surface-variant">Perform double-entry ledger corrections with audit reason tracking</p>
          </div>
          <button onclick="closeAdjustmentModal()" class="p-1 rounded-full text-on-surface-variant hover:bg-surface-container-high cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <form method="post" action="<?php echo site_url('finance/adjustments'); ?>" enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-4">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"/>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Amount -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Adjustment Amount (₹) <span class="text-error">*</span></label>
              <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-headline-sm font-bold font-mono focus:ring-1 focus:ring-tertiary"/>
            </div>

            <!-- Date -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Transaction Date <span class="text-error">*</span></label>
              <input type="date" name="transaction_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary"/>
            </div>
          </div>

          <!-- Adjustment Direction Type -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Adjustment Action Type <span class="text-error">*</span></label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low cursor-pointer hover:bg-surface-container-high transition-colors">
                <input type="radio" name="adjustment_type" value="Debit_Adjustment" checked class="text-primary focus:ring-primary"/>
                <div>
                  <div class="text-body-sm font-bold text-on-surface">Debit Target Account</div>
                  <div class="text-[11px] text-on-surface-variant">Debit Target Account &bull; Credit Offset Account</div>
                </div>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low cursor-pointer hover:bg-surface-container-high transition-colors">
                <input type="radio" name="adjustment_type" value="Credit_Adjustment" class="text-primary focus:ring-primary"/>
                <div>
                  <div class="text-body-sm font-bold text-on-surface">Credit Target Account</div>
                  <div class="text-[11px] text-on-surface-variant">Credit Target Account &bull; Debit Offset Account</div>
                </div>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Target Account -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Target Account <span class="text-error">*</span></label>
              <select name="account_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary">
                <option value="">-- Select Target Account --</option>
                <?php foreach ($all_accounts as $acc): ?>
                  <option value="<?php echo $acc->id; ?>">
                    <?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ') - ' . $acc->category); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="text-[11px] text-on-surface-variant">Account to adjust balance</span>
            </div>

            <!-- Offsetting Account -->
            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Offsetting Account <span class="text-error">*</span></label>
              <select name="offset_account_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary">
                <option value="">-- Select Offsetting Account --</option>
                <?php foreach ($all_accounts as $acc): ?>
                  <option value="<?php echo $acc->id; ?>">
                    <?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ') - ' . $acc->category); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="text-[11px] text-on-surface-variant">Counterbalancing ledger account</span>
            </div>
          </div>

          <!-- Reference No -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Reference / Audit Order #</label>
            <input type="text" name="reference_no" placeholder="e.g. ADJ-REC-2024-001" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:ring-1 focus:ring-primary"/>
          </div>

          <!-- Mandatory Reason -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Audit Reason for Adjustment <span class="text-error">*</span></label>
            <textarea name="reason" rows="2" required placeholder="Mandatory explanation required for financial audit compliance..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-tertiary"></textarea>
          </div>

          <!-- Narration -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Additional Narration / Particulars</label>
            <textarea name="narration" rows="2" placeholder="Additional details..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-primary"></textarea>
          </div>

          <!-- Attachment -->
          <div>
            <label class="block text-body-sm font-semibold text-on-surface mb-1">Approval Document / Proof (Optional)</label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-body-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-body-sm file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest cursor-pointer"/>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeAdjustmentModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-sm font-medium cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-tertiary text-on-tertiary text-body-sm font-semibold hover:bg-tertiary/90 transition-colors shadow-sm cursor-pointer">
              Post Adjustment Voucher
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
            <h3 class="text-title-md font-bold text-on-surface" id="voucher_header_title">Adjustment Voucher</h3>
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
          <h3 class="text-title-md font-bold text-on-surface">Reverse Adjustment?</h3>
          <p class="text-body-sm text-on-surface-variant mt-1" id="reversal_voucher_subtext">You are reversing voucher <span class="font-mono font-semibold" id="reversal_txn_number"></span>.</p>
          <div class="p-3 my-3 rounded-lg bg-error/10 border border-error/20 text-body-sm text-error">
            This will create an equal and opposite reversal journal entry and reinstate account balances non-destructively.
          </div>

          <form method="post" action="<?php echo site_url('finance/reverse_transaction'); ?>" class="space-y-4">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"/>
            <input type="hidden" name="transaction_id" id="reversal_transaction_id" value="0"/>
            <input type="hidden" name="return_url" value="finance/adjustments"/>

            <div>
              <label class="block text-body-sm font-semibold text-on-surface mb-1">Reason for Reversal <span class="text-error">*</span></label>
              <textarea name="reason" rows="3" required placeholder="State exact reason for reversing this adjustment..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:ring-1 focus:ring-error"></textarea>
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
      function openAdjustmentModal() {
        document.getElementById('adjustmentModal').classList.remove('hidden');
      }
      function closeAdjustmentModal() {
        document.getElementById('adjustmentModal').classList.add('hidden');
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
                    <td class="py-2.5 px-3 text-center text-[12px] font-medium ${it.entry_type === 'Debit' ? 'text-secondary font-bold' : 'text-primary font-bold'}">
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
                <div><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Reference #:</span><span class="font-mono text-[12px]">${txn.reference_no || '-'}</span></div>
                ${txn.narration ? '<div class="col-span-2"><span class="text-on-surface-variant text-[11px] block uppercase font-semibold">Audit Narration / Reason:</span><span class="font-medium text-body-sm">' + txn.narration + '</span></div>' : ''}
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
                      <td class="py-2 px-3 text-right text-secondary">₹${totalDebit.toFixed(2)}</td>
                      <td class="py-2 px-3 text-right text-primary">₹${totalCredit.toFixed(2)}</td>
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
