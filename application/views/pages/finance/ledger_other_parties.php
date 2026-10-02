<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
        <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-semibold">Other Party Ledgers</span>
      </nav>
      <h2 class="font-headline-md text-headline-md text-on-surface">Other Party Ledger Accounts</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Vendor, supplier, contractor & external party payable ledger accounts.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <button type="button" onclick="document.getElementById('create-party-modal').classList.remove('hidden')"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add</span>Add Party
      </button>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-5 rounded-2xl bg-orange-50 border border-orange-200/70 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-orange-600 text-[22px]">storefront</span>
        <span class="text-body-sm font-semibold text-orange-800 uppercase tracking-wide">Total Payable</span>
      </div>
      <div class="text-2xl font-bold font-mono text-orange-700">₹<?php echo number_format($total_payable, 2); ?></div>
      <div class="text-body-xs text-orange-600 mt-1">Aggregate party payables</div>
    </div>
    <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-primary text-[22px]">groups</span>
        <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wide">Total Parties</span>
      </div>
      <div class="text-2xl font-bold font-mono text-on-surface"><?php echo number_format($total_count); ?></div>
      <div class="text-body-xs text-on-surface-variant mt-1">Registered parties</div>
    </div>
    <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-teal-600 text-[22px]">domain</span>
        <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wide">Vendor Payables</span>
      </div>
      <div class="text-2xl font-bold font-mono text-on-surface"><?php echo number_format($total_count); ?></div>
      <div class="text-body-xs text-on-surface-variant mt-1">Active vendor accounts</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-5">
    <form method="get" action="<?php echo site_url('finance/ledger_other_parties'); ?>" class="flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Search Party</label>
        <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>"
               placeholder="Name, code, contact…"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      <div class="min-w-[160px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Party Type</label>
        <select name="party_type" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
          <option value="">All Types</option>
          <option value="Vendor" <?php echo ($filters['party_type'] === 'Vendor') ? 'selected' : ''; ?>>Vendor</option>
          <option value="Supplier" <?php echo ($filters['party_type'] === 'Supplier') ? 'selected' : ''; ?>>Supplier</option>
          <option value="Contractor" <?php echo ($filters['party_type'] === 'Contractor') ? 'selected' : ''; ?>>Contractor</option>
          <option value="Other" <?php echo ($filters['party_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">search</span> Filter
        </button>
        <a href="<?php echo site_url('finance/ledger_other_parties'); ?>" class="px-3 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">Reset</a>
      </div>
    </form>
  </div>

  <!-- Ledger Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="px-5 py-3 border-b border-outline-variant/40">
      <span class="text-title-sm font-semibold text-on-surface">
        <span class="material-symbols-outlined text-orange-600 align-middle text-[18px] mr-1">storefront</span>
        Other Party Ledger Accounts
        <span class="ml-2 text-body-xs text-on-surface-variant font-normal">(<?php echo $total_count; ?> parties)</span>
      </span>
    </div>
    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md" id="party-ledger-table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Party Name</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Code</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Contact</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Credited</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Paid (Dr)</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Payable</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($ledgers)): ?>
            <tr>
              <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface-variant/50">storefront</span>
                No other party ledger accounts found.
                <p class="text-body-sm mt-1">Click "Add Party" to create a new vendor/supplier ledger account.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php $idx = 1; foreach ($ledgers as $l): ?>
              <?php
                $balance = $l->current_balance;
                $bal_badge = $balance > 0 ? 'bg-orange-100 text-orange-800' : ($balance < 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-on-surface-variant');
              ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors">
                <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $idx++; ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-orange-600/20 text-orange-700 font-bold text-sm flex items-center justify-center shrink-0">
                      <?php echo strtoupper(substr($l->ledger_name ?? 'P', 0, 1)); ?>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($l->ledger_name ?? '—'); ?></div>
                      <div class="text-[11px] text-on-surface-variant"><?php echo html_escape($l->control_account_name ?? 'Accounts Payable'); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-orange-700 font-semibold"><?php echo html_escape($l->ledger_code ?? '—'); ?></td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container text-on-surface-variant">
                    <?php echo html_escape($l->party_type ?? 'Vendor'); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface text-[12px]"><?php echo html_escape($l->contact ?: '—'); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface">₹<?php echo number_format((float)$l->total_credit, 2); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-emerald-700 font-semibold">₹<?php echo number_format((float)$l->total_debit, 2); ?></td>
                <td class="px-4 py-3 text-right">
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono <?php echo $bal_badge; ?>">
                    <?php echo ($balance > 0 ? '₹' . number_format($balance, 2) . ' Payable' : ($balance < 0 ? '₹' . number_format(abs($balance), 2) . ' Credit' : '₹0.00 Clear')); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <a href="<?php echo site_url('finance/other_party_statement/' . $l->ledger_id); ?>"
                     class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-orange-600/10 text-orange-700 text-body-xs font-semibold hover:bg-orange-600 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>Statement
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create Party Modal -->
  <div id="create-party-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-dark-text/40 backdrop-blur-sm">
    <div class="w-full max-w-md bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-5">
        <h3 class="text-title-md font-bold text-on-surface">Add New Party Ledger</h3>
        <button type="button" onclick="document.getElementById('create-party-modal').classList.add('hidden')"
                class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors">
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>
      <form method="post" action="<?php echo site_url('finance/ledger_other_parties'); ?>">
        <input type="hidden" name="action" value="create_party"/>
        <div class="mb-4">
          <label class="block text-body-sm font-semibold text-on-surface mb-1">Party / Vendor Name <span class="text-error">*</span></label>
          <input type="text" name="party_name" required placeholder="e.g., ABC Suppliers Pvt Ltd"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:outline-none focus:border-primary"/>
        </div>
        <div class="mb-4">
          <label class="block text-body-sm font-semibold text-on-surface mb-1">Party Type</label>
          <select name="party_type" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:outline-none focus:border-primary">
            <option value="Vendor">Vendor</option>
            <option value="Supplier">Supplier</option>
            <option value="Contractor">Contractor</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-body-sm font-semibold text-on-surface mb-1">Contact (Phone / Email)</label>
          <input type="text" name="contact" placeholder="Optional"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:outline-none focus:border-primary"/>
        </div>
        <div class="mb-5">
          <label class="block text-body-sm font-semibold text-on-surface mb-1">Notes</label>
          <textarea name="notes" rows="2" placeholder="Optional notes"
                    class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md focus:outline-none focus:border-primary resize-none"></textarea>
        </div>
        <div class="flex gap-3">
          <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary text-on-primary font-semibold text-body-md hover:bg-primary/90 transition-colors">
            Create Ledger
          </button>
          <button type="button" onclick="document.getElementById('create-party-modal').classList.add('hidden')"
                  class="px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface text-body-md hover:bg-surface-container transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('party-ledger-table');
    if (table && typeof $.fn.DataTable !== 'undefined') {
      $(table).DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']],
        language: {
          emptyTable: "No vendor/party ledgers found.",
          search: "Quick search:",
          lengthMenu: "Show _MENU_ per page",
          info: "_START_ – _END_ of _TOTAL_ parties",
          infoEmpty: "No records",
          paginate: { next: 'Next →', previous: '← Prev' }
        },
        columnDefs: [{ orderable: false, targets: [0, 8] }]
      });
    }
  });
  </script>
