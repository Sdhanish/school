<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Receipts</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit log and official documentation for all verified fee payment receipts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/fee_collection'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_card</span>New Collection
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Receipt #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Student Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Class</th>
              <th class="px-4 py-3 text-right font-semibold text-secondary uppercase text-[11px] tracking-wider">Amount Paid (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Payment Mode</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Deposited In</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Ref / UTR #</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($collections)): ?>
              <?php foreach ($collections as $c): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary"><?php echo html_escape($c->receipt_number); ?></td>
                  <td class="px-4 py-3">
                    <div class="font-semibold text-on-surface"><?php echo html_escape($c->first_name . ' ' . $c->last_name); ?></div>
                    <div class="text-xs font-mono text-on-surface-variant"><?php echo html_escape($c->admission_no); ?></div>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm"><?php echo html_escape($c->class_name ?: '—'); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-secondary">₹<?php echo number_format($c->amount, 2); ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface"><?php echo html_escape($c->payment_mode); ?></span>
                  </td>
                  <td class="px-4 py-3 text-sm text-on-surface-variant"><?php echo html_escape($c->deposit_account_name ?: 'Cash'); ?></td>
                  <td class="px-4 py-3 font-mono text-xs text-on-surface-variant"><?php echo html_escape($c->reference_number ?: '—'); ?></td>
                  <td class="px-4 py-3 text-center font-mono text-sm"><?php echo date('d M Y', strtotime($c->receipt_date)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                      <?php echo html_escape($c->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?php echo site_url('finance/student_statement/' . $c->student_id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-high text-primary hover:bg-primary hover:text-white transition-colors text-xs font-semibold" title="View Statement">
                      <span class="material-symbols-outlined text-[15px]">visibility</span>Statement
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="px-4 py-8 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-4xl mb-2 text-on-surface-variant/40 block">receipt</span>
                  No payment receipts recorded yet. Click "New Collection" to collect student fees.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
