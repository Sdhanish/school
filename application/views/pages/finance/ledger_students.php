<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
        <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-semibold">Student Ledgers</span>
      </nav>
      <h2 class="font-headline-md text-headline-md text-on-surface">Student Ledger Accounts</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Individual student receivable accounts – fee invoices, payments & running balances.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0 flex-wrap">
      <a href="<?php echo site_url('finance/fee_collection'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add_card</span>Collect Fee
      </a>
      <a href="<?php echo site_url('finance/fee_assignments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">assignment</span>Assign Fees
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200/70 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-amber-600 text-[22px]">account_balance_wallet</span>
        <span class="text-body-sm font-semibold text-amber-800 uppercase tracking-wide">Total Outstanding</span>
      </div>
      <div class="text-2xl font-bold font-mono text-amber-700">₹<?php echo number_format($total_outstanding, 2); ?></div>
      <div class="text-body-xs text-amber-600 mt-1">Aggregate receivable due</div>
    </div>
    <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-primary text-[22px]">group</span>
        <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wide">Total Students</span>
      </div>
      <div class="text-2xl font-bold font-mono text-on-surface"><?php echo number_format($total_count); ?></div>
      <div class="text-body-xs text-on-surface-variant mt-1">Active ledger accounts</div>
    </div>
    <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200/70 elevation-1">
      <div class="flex items-center gap-3 mb-2">
        <span class="material-symbols-outlined text-emerald-600 text-[22px]">check_circle</span>
        <span class="text-body-sm font-semibold text-emerald-800 uppercase tracking-wide">Clear / Credit</span>
      </div>
      <div class="text-2xl font-bold font-mono text-emerald-700"><?php echo number_format($credit_count); ?></div>
      <div class="text-body-xs text-emerald-600 mt-1">Students with no dues</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-5">
    <form method="get" action="<?php echo site_url('finance/ledger_students'); ?>" class="flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Search Student</label>
        <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>"
               placeholder="Name, admission no, ledger code…"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      <?php if (!empty($classes)): ?>
      <div class="min-w-[160px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Class</label>
        <select name="class_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
          <option value="">All Classes</option>
          <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($filters['class_id'] == $cls->class_id) ? 'selected' : ''; ?>>
            <?php echo html_escape($cls->class_name); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">search</span> Filter
        </button>
        <a href="<?php echo site_url('finance/ledger_students'); ?>" class="px-3 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">Reset</a>
      </div>
    </form>
  </div>

  <!-- Ledger Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
    <div class="px-5 py-3 border-b border-outline-variant/40 flex items-center justify-between">
      <span class="text-title-sm font-semibold text-on-surface">
        <span class="material-symbols-outlined text-primary align-middle text-[18px] mr-1">menu_book</span>
        Student Ledger Accounts
        <span class="ml-2 text-body-xs text-on-surface-variant font-normal">(<?php echo $total_count; ?> accounts)</span>
      </span>
      <span class="text-body-xs text-on-surface-variant">Click a row to view full statement</span>
    </div>
    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md" id="student-ledger-table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Student</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Admission No.</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Class</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Ledger Code</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Debited</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Total Paid</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Balance Due</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($ledgers)): ?>
            <tr>
              <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface-variant/50">menu_book</span>
                No student ledger accounts found.
                <?php if (!empty($filters['search'])): ?>
                  <a href="<?php echo site_url('finance/ledger_students'); ?>" class="text-primary hover:underline text-body-sm block mt-1">Clear search</a>
                <?php else: ?>
                  <p class="text-body-sm mt-1">Student ledgers are created automatically when fees are assigned.</p>
                <?php endif; ?>
              </td>
            </tr>
          <?php else: ?>
            <?php $idx = 1; foreach ($ledgers as $l): ?>
              <?php
                $balance = $l->current_balance;
                $bal_class = $balance > 0 ? 'text-amber-700' : ($balance < 0 ? 'text-emerald-700' : 'text-on-surface-variant');
                $bal_badge = $balance > 0 ? 'bg-amber-100 text-amber-800' : ($balance < 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-on-surface-variant');
              ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors cursor-pointer" onclick="window.location.href='<?php echo site_url('finance/student_statement/' . $l->student_id); ?>'">
                <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $idx++; ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-primary/15 text-primary font-bold text-sm flex items-center justify-center shrink-0">
                      <?php echo strtoupper(substr($l->first_name ?? 'S', 0, 1)); ?>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($l->student_name ?: ($l->ledger_name ?? '—')); ?></div>
                      <div class="text-[11px] text-on-surface-variant"><?php echo html_escape($l->control_account_name ?? 'Accounts Receivable'); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-on-surface"><?php echo html_escape($l->admission_no_display ?? '—'); ?></td>
                <td class="px-4 py-3 text-on-surface text-[13px]">
                  <?php echo html_escape($l->class_name ?? '—'); ?>
                  <?php if (!empty($l->division_name)): ?>
                    <span class="text-on-surface-variant">(<?php echo html_escape($l->division_name); ?>)</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-primary font-semibold"><?php echo html_escape($l->ledger_code ?? '—'); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface">₹<?php echo number_format((float)$l->total_debit, 2); ?></td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-emerald-700 font-semibold">₹<?php echo number_format((float)$l->total_credit, 2); ?></td>
                <td class="px-4 py-3 text-right">
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono <?php echo $bal_badge; ?>">
                    <?php echo ($balance > 0 ? '₹' . number_format($balance, 2) . ' Dr' : ($balance < 0 ? '₹' . number_format(abs($balance), 2) . ' Cr' : '₹0.00 Clear')); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <a href="<?php echo site_url('finance/student_statement/' . $l->student_id); ?>"
                     onclick="event.stopPropagation()"
                     class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary text-body-xs font-semibold hover:bg-primary hover:text-on-primary transition-colors">
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

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('student-ledger-table');
    if (table && typeof $.fn.DataTable !== 'undefined') {
      $(table).DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']],
        language: {
          emptyTable: "No student ledgers found for the selected school.",
          search: "Quick search:",
          lengthMenu: "Show _MENU_ per page",
          info: "_START_ – _END_ of _TOTAL_ students",
          infoEmpty: "No records",
          paginate: { next: 'Next →', previous: '← Prev' }
        },
        columnDefs: [
          { orderable: false, targets: [0, 8] }
        ]
      });
    }
  });
  </script>
