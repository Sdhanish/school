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
        <h2 class="font-headline-md text-headline-md text-on-surface">Outstanding Dues</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Real-time outstanding receivables and aging dues across all active student accounts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('finance/fee_collection'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_card</span>Collect Payment
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Invoice #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Student Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Class</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Fee Particulars</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Invoiced (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Paid (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-error uppercase text-[11px] tracking-wider">Outstanding (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Due Date</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Aging</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($pending_fees)): ?>
              <?php $today = date('Y-m-d'); ?>
              <?php foreach ($pending_fees as $pf): ?>
                <?php
                  $is_overdue = $pf->due_date < $today;
                  $days_diff = round((strtotime($today) - strtotime($pf->due_date)) / (60 * 60 * 24));
                ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary whitespace-nowrap">
                    <a href="<?php echo site_url('finance/student_invoice/' . $pf->id); ?>" class="hover:underline flex items-center gap-1" title="View Invoice">
                      <span class="material-symbols-outlined text-[15px]">description</span>
                      <?php echo html_escape($pf->invoice_number); ?>
                    </a>
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-semibold text-on-surface"><?php echo html_escape($pf->first_name . ' ' . $pf->last_name); ?></div>
                    <div class="text-xs font-mono text-on-surface-variant"><?php echo html_escape($pf->admission_no); ?></div>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm"><?php echo html_escape($pf->class_name . ($pf->division_name ? ' - ' . $pf->division_name : '')); ?></td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($pf->fee_name ?? 'Tuition Fee'); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-medium text-on-surface">₹<?php echo number_format($pf->net_amount, 2); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-medium text-secondary">₹<?php echo number_format($pf->paid_amount, 2); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-error">₹<?php echo number_format($pf->due_amount, 2); ?></td>
                  <td class="px-4 py-3 text-center font-mono text-sm"><?php echo date('d M Y', strtotime($pf->due_date)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($is_overdue): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">
                        <?php echo $days_diff; ?>d Overdue
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                        Current
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <a href="<?php echo site_url('finance/student_statement/' . $pf->student_id); ?>" class="p-1 rounded text-on-surface-variant hover:bg-surface-container-high transition-colors inline-block" title="Student Ledger Statement">
                        <span class="material-symbols-outlined text-[16px]">menu_book</span>
                      </a>
                      <a href="<?php echo site_url('finance/student_invoice/' . $pf->id); ?>" class="p-1 rounded text-primary hover:bg-primary/10 transition-colors inline-block" title="View Invoice">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                      </a>
                      <a href="<?php echo site_url('finance/fee_collection?student_id=' . $pf->student_id . '&assignment_id=' . $pf->id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-secondary text-on-secondary text-xs font-semibold hover:bg-secondary/90 transition-colors shadow-2xs">
                        <span class="material-symbols-outlined text-[14px]">add_card</span>Pay
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="px-4 py-8 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-4xl mb-2 text-secondary block">check_circle</span>
                  All dues cleared! No pending or overdue student fees found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
