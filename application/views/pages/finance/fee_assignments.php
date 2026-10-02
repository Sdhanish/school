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
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Fee Assignments & Invoices</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Assign fee structures to entire classes or individual students and auto-generate accounting invoices.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openAssignModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_task</span>Assign Fee Structure
        </button>
        <a href="<?php echo site_url('finance/fee_collection'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">point_of_sale</span>Collect Payment
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
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Fee Head</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Assigned (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Paid (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Due (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Due Date</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($assignments)): ?>
              <?php foreach ($assignments as $a): ?>
                <?php
                  $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                  if ($a->status === 'Paid') $badgeClass = 'bg-secondary-container text-on-secondary-container';
                  elseif ($a->status === 'Partially_Paid') $badgeClass = 'bg-amber-100 text-amber-900 font-semibold';
                  elseif ($a->status === 'Pending') $badgeClass = 'bg-error-container text-on-error-container font-semibold';
                ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary"><?php echo html_escape($a->invoice_number); ?></td>
                  <td class="px-4 py-3">
                    <div class="font-semibold text-on-surface"><?php echo html_escape($a->first_name . ' ' . $a->last_name); ?></div>
                    <div class="text-xs font-mono text-on-surface-variant"><?php echo html_escape($a->admission_no); ?></div>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-sm"><?php echo html_escape($a->class_name . ($a->division_name ? ' - ' . $a->division_name : '')); ?></td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($a->fee_name ?? 'Fee Head'); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface">₹<?php echo number_format($a->net_amount, 2); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-secondary">₹<?php echo number_format($a->paid_amount, 2); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold <?php echo ($a->due_amount > 0) ? 'text-error' : 'text-on-surface-variant'; ?>">₹<?php echo number_format($a->due_amount, 2); ?></td>
                  <td class="px-4 py-3 text-center font-mono text-sm"><?php echo date('d M Y', strtotime($a->due_date)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $badgeClass; ?>">
                      <?php echo html_escape(str_replace('_', ' ', $a->status)); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?php echo site_url('finance/student_statement/' . $a->student_id); ?>" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-block mr-1" title="View Statement">
                      <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </a>
                    <?php if ($a->due_amount > 0): ?>
                      <a href="<?php echo site_url('finance/fee_collection?student_id=' . $a->student_id); ?>" class="p-1 rounded-lg text-secondary hover:bg-secondary/10 transition-colors inline-block" title="Collect Payment">
                        <span class="material-symbols-outlined text-[18px]">add_card</span>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="px-4 py-8 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-4xl mb-2 text-on-surface-variant/40 block">assignment_ind</span>
                  No fee invoices assigned yet. Click "Assign Fee Structure" to invoice students.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div id="assignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-scrim/40 backdrop-blur-xs hidden">
      <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl w-full max-w-md p-6 elevation-3">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/40 mb-4">
          <h3 class="font-headline-md text-headline-md text-on-surface">Assign Fee Structure</h3>
          <button onclick="closeAssignModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>
        <form method="post" action="<?php echo site_url('finance/fee_assignments'); ?>">
          <div class="space-y-4">
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Target Class <span class="text-error">*</span></label>
              <select name="class_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- Select Class --</option>
                <?php if (!empty($classes)): ?>
                  <?php foreach ($classes as $c): ?>
                    <option value="<?php echo (int)$c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Fee Structure <span class="text-error">*</span></label>
              <select name="fee_structure_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- Select Structure --</option>
                <?php if (!empty($structures)): ?>
                  <?php foreach ($structures as $st): ?>
                    <option value="<?php echo (int)$st->id; ?>"><?php echo html_escape($st->structure_name . ' (₹' . number_format($st->amount, 2) . ')'); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Custom Due Date (Optional)</label>
              <input type="date" name="due_date" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeAssignModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer text-label-md">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-sm cursor-pointer text-label-md font-semibold">Assign to Students</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openAssignModal() {
        document.getElementById('assignModal').classList.remove('hidden');
      }
      function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
      }
    </script>
