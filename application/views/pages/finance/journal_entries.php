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
        <h2 class="font-headline-md text-headline-md text-on-surface">Journal Entries (Double-Entry Engine)</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Post balanced general journal vouchers, adjustments, opening balances, and reversal audit trails.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openJournalModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">post_add</span>New Journal Voucher
        </button>
        <a href="<?php echo site_url('finance/reports/day_book'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">calendar_today</span>Day Book
        </a>
      </div>
    </div>

    <!-- Journal Transactions Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Posted Journal Entries</h3>
        <span class="text-body-sm text-on-surface-variant"><?php echo count($transactions); ?> Transactions</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Voucher #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Narration / Particulars</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($transactions)): ?>
              <?php foreach ($transactions as $t): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d-M-Y', strtotime($t->transaction_date)); ?></td>
                  <td class="px-4 py-3 font-mono text-[13px] text-primary font-semibold whitespace-nowrap"><?php echo html_escape($t->transaction_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface">
                      <?php echo html_escape($t->transaction_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface">
                    <?php echo html_escape($t->narration); ?>
                    <?php if (!empty($t->reference_no)): ?>
                      <span class="block text-[11px] font-mono text-on-surface-variant">Ref: <?php echo html_escape($t->reference_no); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($t->total_amount, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($t->total_amount, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($t->is_reversed): ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-error-container text-on-error-container">Reversed</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Balanced</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <?php if (!$t->is_reversed): ?>
                      <button onclick="openReversalModal(<?php echo $t->id; ?>, '<?php echo html_escape($t->transaction_number); ?>')" class="inline-flex items-center gap-1 text-[12px] font-semibold text-error hover:underline cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">undo</span>Reverse
                      </button>
                    <?php else: ?>
                      <span class="text-[12px] text-on-surface-variant">Locked</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No journal entries posted yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- NEW JOURNAL ENTRY MODAL -->
    <div id="journal-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-4xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[24px]">post_add</span>Post Journal Voucher (Double-Entry)
          </h3>
          <button onclick="closeJournalModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/journal_entries', array('id' => 'journal-form', 'class' => 'space-y-4')); ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Voucher Date *</label>
              <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reference / Voucher No</label>
              <input type="text" name="reference_no" placeholder="e.g. JV-2026-001" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Narration / Master Particulars *</label>
            <input type="text" name="narration" required placeholder="e.g. Annual library books depreciation or opening fund balance adjustment" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <!-- Journal Lines Table -->
          <div class="border border-outline-variant/60 rounded-xl overflow-hidden">
            <div class="p-3 bg-surface-container-low flex items-center justify-between">
              <span class="font-semibold text-body-md text-on-surface">Journal Lines</span>
              <button type="button" onclick="addJournalLine()" class="inline-flex items-center gap-1 text-[13px] font-semibold text-primary hover:underline cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>Add Line
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-body-sm" id="journal-lines-table">
                <thead>
                  <tr class="bg-surface-container-low/50 border-b border-outline-variant/40">
                    <th class="p-2.5 text-left font-semibold text-on-surface-variant">General Ledger Head *</th>
                    <th class="p-2.5 text-left font-semibold text-on-surface-variant">Sub-Ledger (Optional)</th>
                    <th class="p-2.5 text-left font-semibold text-on-surface-variant">Line Description</th>
                    <th class="p-2.5 text-right font-semibold text-on-surface-variant w-32">Debit (₹)</th>
                    <th class="p-2.5 text-right font-semibold text-on-surface-variant w-32">Credit (₹)</th>
                    <th class="p-2.5 text-center w-10"></th>
                  </tr>
                </thead>
                <tbody id="journal-lines-body">
                  <!-- Row 1 (Debit default) -->
                  <tr class="border-b border-outline-variant/30">
                    <td class="p-2">
                      <select name="account_id[]" required class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
                        <option value="">Select Account</option>
                        <?php foreach ($all_accounts as $acc): ?>
                          <option value="<?php echo $acc->id; ?>"><?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ')'); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td class="p-2">
                      <select name="ledger_id[]" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
                        <option value="">None</option>
                        <?php foreach ($all_ledgers as $l): ?>
                          <option value="<?php echo $l->id; ?>"><?php echo html_escape($l->ledger_name . ' (' . $l->entity_type . ')'); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td class="p-2">
                      <input type="text" name="description[]" placeholder="Line note" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm"/>
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.5" min="0" name="debit[]" value="0.00" oninput="calculateJournalTotals()" class="line-debit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.5" min="0" name="credit[]" value="0.00" oninput="calculateJournalTotals()" class="line-credit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
                    </td>
                    <td class="p-2 text-center"></td>
                  </tr>
                  <!-- Row 2 (Credit default) -->
                  <tr class="border-b border-outline-variant/30">
                    <td class="p-2">
                      <select name="account_id[]" required class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
                        <option value="">Select Account</option>
                        <?php foreach ($all_accounts as $acc): ?>
                          <option value="<?php echo $acc->id; ?>"><?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ')'); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td class="p-2">
                      <select name="ledger_id[]" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
                        <option value="">None</option>
                        <?php foreach ($all_ledgers as $l): ?>
                          <option value="<?php echo $l->id; ?>"><?php echo html_escape($l->ledger_name . ' (' . $l->entity_type . ')'); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td class="p-2">
                      <input type="text" name="description[]" placeholder="Line note" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm"/>
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.5" min="0" name="debit[]" value="0.00" oninput="calculateJournalTotals()" class="line-debit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.5" min="0" name="credit[]" value="0.00" oninput="calculateJournalTotals()" class="line-credit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
                    </td>
                    <td class="p-2 text-center"></td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="bg-surface-container-low font-bold">
                    <td colspan="3" class="p-2.5 text-right uppercase text-on-surface text-body-sm">Totals:</td>
                    <td class="p-2.5 text-right font-mono text-on-surface" id="total-debit-display">₹0.00</td>
                    <td class="p-2.5 text-right font-mono text-on-surface" id="total-credit-display">₹0.00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="6" class="p-2.5 text-center text-body-sm" id="balance-warning-banner">
                      <span class="text-error font-medium">Entries must have equal Debits and Credits before saving.</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeJournalModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" id="journal-submit-btn" disabled class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm disabled:opacity-50 cursor-pointer">
              Post Journal Voucher
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- REVERSAL MODAL -->
    <div id="reversal-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-error text-[24px]">undo</span>Reverse Accounting Transaction
          </h3>
          <button onclick="closeReversalModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/reverse_transaction', array('id' => 'reversal-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="transaction_id" id="rev-transaction-id" value="0"/>

          <p class="text-body-sm text-on-surface-variant">
            You are reversing transaction <strong id="rev-transaction-number" class="text-primary font-mono"></strong>.
            This will create an equal and opposite reversal journal entry in the General Ledger. The original transaction will not be deleted.
          </p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Reversal *</label>
            <textarea name="reason" required rows="3" placeholder="Explain why this transaction is being reversed (e.g. Entered incorrect amount or cancelled cheque)..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeReversalModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-error text-white text-label-md font-semibold hover:bg-error/90 transition-colors shadow-sm cursor-pointer">
              Confirm Reversal
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openJournalModal() {
        var m = document.getElementById('journal-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        calculateJournalTotals();
      }

      function closeJournalModal() {
        var m = document.getElementById('journal-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function openReversalModal(id, num) {
        document.getElementById('rev-transaction-id').value = id;
        document.getElementById('rev-transaction-number').textContent = num;
        var m = document.getElementById('reversal-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeReversalModal() {
        var m = document.getElementById('reversal-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function addJournalLine() {
        var tbody = document.getElementById('journal-lines-body');
        var tr = document.createElement('tr');
        tr.className = 'border-b border-outline-variant/30';
        tr.innerHTML = `
          <td class="p-2">
            <select name="account_id[]" required class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
              <option value="">Select Account</option>
              <?php foreach ($all_accounts as $acc): ?>
                <option value="<?php echo $acc->id; ?>"><?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="p-2">
            <select name="ledger_id[]" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm">
              <option value="">None</option>
              <?php foreach ($all_ledgers as $l): ?>
                <option value="<?php echo $l->id; ?>"><?php echo html_escape($l->ledger_name . ' (' . $l->entity_type . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="p-2">
            <input type="text" name="description[]" placeholder="Line note" class="w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm"/>
          </td>
          <td class="p-2">
            <input type="number" step="0.5" min="0" name="debit[]" value="0.00" oninput="calculateJournalTotals()" class="line-debit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
          </td>
          <td class="p-2">
            <input type="number" step="0.5" min="0" name="credit[]" value="0.00" oninput="calculateJournalTotals()" class="line-credit w-full px-2 py-1.5 rounded border border-outline-variant bg-surface-container-lowest text-body-sm font-mono text-right font-semibold"/>
          </td>
          <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove(); calculateJournalTotals();" class="text-error hover:text-error/80 cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">remove_circle</span>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
        calculateJournalTotals();
      }

      function calculateJournalTotals() {
        var debits = document.querySelectorAll('.line-debit');
        var credits = document.querySelectorAll('.line-credit');
        var totalD = 0;
        var totalC = 0;

        debits.forEach(function(d) { totalD += parseFloat(d.value) || 0; });
        credits.forEach(function(c) { totalC += parseFloat(c.value) || 0; });

        document.getElementById('total-debit-display').textContent = '₹' + totalD.toFixed(2);
        document.getElementById('total-credit-display').textContent = '₹' + totalC.toFixed(2);

        var banner = document.getElementById('balance-warning-banner');
        var btn = document.getElementById('journal-submit-btn');

        if (totalD > 0 && totalC > 0 && Math.abs(totalD - totalC) < 0.01) {
          banner.innerHTML = '<span class="text-secondary font-semibold">✓ Journal Entry is balanced (₹' + totalD.toFixed(2) + '). Ready to post.</span>';
          btn.disabled = false;
        } else {
          var diff = Math.abs(totalD - totalC);
          banner.innerHTML = '<span class="text-error font-medium">Difference: ₹' + diff.toFixed(2) + '. Debits must equal Credits to post.</span>';
          btn.disabled = true;
        }
      }
    </script>
