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

    <!-- Summary KPI Cards -->
    <?php
      $total_assigned = 0.00;
      $total_paid = 0.00;
      $total_due = 0.00;
      $count_pending = 0;
      if (!empty($assignments)) {
        foreach ($assignments as $a) {
          $total_assigned += (float)$a->net_amount;
          $total_paid += (float)$a->paid_amount;
          $total_due += (float)$a->due_amount;
          if ($a->status === 'Pending' || $a->status === 'Partially_Paid') {
            $count_pending++;
          }
        }
      }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wider">Total Invoiced</span>
          <span class="p-2 rounded-xl bg-primary/10 text-primary material-symbols-outlined text-[20px]">receipt_long</span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-on-surface mt-2">₹<?php echo number_format($total_assigned, 2); ?></div>
        <div class="text-body-xs text-on-surface-variant mt-1"><?php echo count($assignments ?? []); ?> Total Invoices Issued</div>
      </div>
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wider">Total Collected</span>
          <span class="p-2 rounded-xl bg-secondary/10 text-secondary material-symbols-outlined text-[20px]">payments</span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-secondary mt-2">₹<?php echo number_format($total_paid, 2); ?></div>
        <div class="text-body-xs text-on-surface-variant mt-1">Realized Student Revenue</div>
      </div>
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wider">Student Receivable</span>
          <span class="p-2 rounded-xl bg-error/10 text-error material-symbols-outlined text-[20px]">account_balance_wallet</span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-error mt-2">₹<?php echo number_format($total_due, 2); ?></div>
        <div class="text-body-xs text-on-surface-variant mt-1">Outstanding Dues (Code 1030)</div>
      </div>
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 elevation-1">
        <div class="flex items-center justify-between">
          <span class="text-body-sm font-semibold text-on-surface-variant uppercase tracking-wider">Pending Invoices</span>
          <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 material-symbols-outlined text-[20px]">pending_actions</span>
        </div>
        <div class="text-headline-sm font-bold font-mono text-amber-700 mt-2"><?php echo $count_pending; ?></div>
        <div class="text-body-xs text-on-surface-variant mt-1">Awaiting Complete Collection</div>
      </div>
    </div>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Fee Assignments & Invoices</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Assign fees via Bulk (Class-wide) or Individual mode. Generates official invoices, sets payment due dates, and debits Student Accounts Receivable.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openAssignModal('bulk')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">group_add</span>Bulk Class Assignment
        </button>
        <button onclick="openAssignModal('individual')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-label-md font-semibold hover:bg-surface-container-highest transition-colors border border-outline-variant/60 cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">person_add</span>Individual Assignment
        </button>
        <a href="<?php echo site_url('finance/fee_collection'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">point_of_sale</span>Collect Payment
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-3 bg-surface-container-low/30">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">assignment_turned_in</span>
          <span class="font-semibold text-on-surface text-body-md">Invoiced Fee Records</span>
        </div>
        <div class="flex items-center gap-2">
          <input type="text" id="invoiceTableSearch" placeholder="Search invoice, student, adm #..." onkeyup="filterInvoiceTable()" class="px-3 py-1.5 border border-outline rounded-lg text-body-sm bg-surface text-on-surface focus:border-primary outline-hidden w-64">
        </div>
      </div>
      <div class="table-scroll overflow-x-auto">
        <table id="invoiceTable" class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Invoice #</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Student Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Class</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Fee Head</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Amount (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Paid (₹)</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Balance Due (₹)</th>
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
                  <td class="px-4 py-3 text-center font-mono text-sm font-semibold <?php echo (strtotime($a->due_date) < time() && $a->due_amount > 0) ? 'text-error' : ''; ?>">
                    <?php echo date('d M Y', strtotime($a->due_date)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $badgeClass; ?>">
                      <?php echo html_escape(str_replace('_', ' ', $a->status)); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <!-- Quick View Invoice Modal -->
                    <button type="button" onclick="viewInvoiceDetails(<?php echo (int)$a->id; ?>)" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-block mr-1 cursor-pointer" title="Quick Invoice Preview">
                      <span class="material-symbols-outlined text-[18px]">receipt</span>
                    </button>
                    <!-- Dedicated Full Invoice View Page -->
                    <a href="<?php echo site_url('finance/student_invoice/' . $a->id); ?>" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors inline-block mr-1 cursor-pointer" title="Open Dedicated Invoice Page">
                      <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <!-- Student Statement / Ledger -->
                    <a href="<?php echo site_url('finance/student_statement/' . $a->student_id); ?>" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors inline-block mr-1" title="Student Ledger Statement">
                      <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </a>
                    <!-- Collect Payment Shortcut -->
                    <?php if ($a->due_amount > 0): ?>
                      <a href="<?php echo site_url('finance/fee_collection?student_id=' . $a->student_id . '&assignment_id=' . $a->id); ?>" class="p-1 rounded-lg text-secondary hover:bg-secondary/10 transition-colors inline-block" title="Collect Payment">
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
                  No fee invoices assigned yet. Click "Bulk Class Assignment" or "Individual Assignment" to create invoices.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assignment Modal (Bulk & Individual) -->
    <div id="assignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-scrim/40 backdrop-blur-xs hidden">
      <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl w-full max-w-lg p-6 elevation-3 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/40 mb-4">
          <div>
            <h3 id="assignModalTitle" class="font-headline-md text-headline-md text-on-surface">Assign Fee & Generate Invoice</h3>
            <p class="text-body-xs text-on-surface-variant mt-0.5">Posts receivable debit to student sub-ledger and credits revenue head.</p>
          </div>
          <button onclick="closeAssignModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- Mode Toggle Tabs -->
        <div class="flex rounded-xl bg-surface-container-low p-1 mb-5 border border-outline-variant/40">
          <button type="button" id="tabBulk" onclick="switchAssignMode('bulk')" class="flex-1 py-2 text-center text-label-md font-semibold rounded-lg bg-surface-container-lowest text-primary shadow-xs transition-all cursor-pointer">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">group</span>Bulk (Class-Wide)</span>
          </button>
          <button type="button" id="tabIndividual" onclick="switchAssignMode('individual')" class="flex-1 py-2 text-center text-label-md font-semibold rounded-lg text-on-surface-variant hover:text-on-surface transition-all cursor-pointer">
            <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">person</span>Individual Student</span>
          </button>
        </div>

        <form method="post" action="<?php echo site_url('finance/fee_assignments'); ?>" id="assignFeeForm">
          <input type="hidden" name="assignment_mode" id="assignment_mode" value="bulk">

          <div class="space-y-4">
            <!-- Class Selection (Used by both modes) -->
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">
                Target Class <span class="text-error">*</span>
              </label>
              <select name="class_id" id="assign_class_id" required onchange="onClassChange()" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- Select Class --</option>
                <?php if (!empty($classes)): ?>
                  <?php foreach ($classes as $c): ?>
                    <option value="<?php echo (int)$c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- Bulk: Optional Division -->
            <div id="divisionFieldContainer">
              <label class="block text-label-md font-semibold text-on-surface mb-1">
                Division / Section <span class="text-on-surface-variant font-normal text-xs">(Optional — defaults to all students in class)</span>
              </label>
              <select name="division_id" id="assign_division_id" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="0">All Sections / Entire Class</option>
              </select>
            </div>

            <!-- Individual: Student Selection -->
            <div id="studentFieldContainer" class="hidden">
              <label class="block text-label-md font-semibold text-on-surface mb-1">
                Select Student <span class="text-error">*</span>
              </label>
              <select name="student_id" id="assign_student_id" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- First Select a Class Above --</option>
              </select>
              <p id="studentLoadingMsg" class="text-xs text-primary mt-1 hidden flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] animate-spin">refresh</span>Loading students...
              </p>
            </div>

            <!-- Fee Structure Selection -->
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Fee Structure <span class="text-error">*</span></label>
              <select name="fee_structure_id" id="assign_structure_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- Select Fee Structure --</option>
                <?php if (!empty($structures)): ?>
                  <?php foreach ($structures as $st): ?>
                    <option value="<?php echo (int)$st->id; ?>" data-class-id="<?php echo (int)$st->class_id; ?>">
                      <?php echo html_escape($st->structure_name . ' — ₹' . number_format($st->amount, 2) . ' (' . ($st->class_name ?: 'General') . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- Payment Due Date -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-label-md font-semibold text-on-surface">Payment Due Date <span class="text-error">*</span></label>
                <span class="text-xs text-on-surface-variant font-mono">Mandatory on Assignment</span>
              </div>
              <input type="date" name="due_date" id="assign_due_date" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md font-mono focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              <p class="text-[11px] text-on-surface-variant mt-1">Due date determines when overdue notifications and ledger aging are triggered.</p>
            </div>

            <!-- Accounting Audit Notice -->
            <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/40 text-body-xs text-on-surface-variant flex items-start gap-2">
              <span class="material-symbols-outlined text-secondary text-[18px] shrink-0 mt-0.5">verified</span>
              <div>
                <strong class="text-on-surface">Double-Entry Posting:</strong> Assigning will debit <strong class="text-primary">1030 Student Accounts Receivable</strong> in student sub-ledger and credit the fee revenue account head.
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeAssignModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer text-label-md">Cancel</button>
            <button type="submit" id="btnSubmitAssign" class="px-5 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-sm cursor-pointer text-label-md font-semibold">Generate Invoice(s)</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Invoice Modal -->
    <div id="viewInvoiceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-scrim/40 backdrop-blur-xs hidden">
      <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl w-full max-w-2xl p-6 elevation-3 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/40 mb-4">
          <div class="flex items-center gap-2">
            <span class="p-2 rounded-xl bg-primary/10 text-primary material-symbols-outlined text-[20px]">receipt_long</span>
            <div>
              <h3 class="font-headline-md text-headline-md text-on-surface">Student Fee Invoice</h3>
              <div id="modalInvSubtitle" class="text-body-xs text-on-surface-variant font-mono">INV-00000000-00000</div>
            </div>
          </div>
          <button onclick="closeInvoiceModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div id="invoiceLoading" class="py-12 text-center text-on-surface-variant">
          <span class="material-symbols-outlined text-4xl animate-spin text-primary block mb-2">refresh</span>
          Fetching invoice and ledger records...
        </div>

        <div id="invoiceContent" class="space-y-4 hidden">
          <!-- Top Row: Student & Voucher Meta -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-surface-container-low rounded-xl border border-outline-variant/40 text-body-sm">
            <div>
              <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">Billed To (Student)</span>
              <div id="invStudentName" class="font-bold text-on-surface text-body-md">Student Name</div>
              <div id="invStudentAdm" class="text-xs font-mono text-on-surface-variant">Adm: SCH000</div>
              <div id="invStudentClass" class="text-xs text-on-surface mt-0.5">Class: Grade 1</div>
            </div>
            <div>
              <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">Invoice Status & Dates</span>
              <div class="flex items-center gap-2">
                <span id="invStatusBadge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">Pending</span>
              </div>
              <div class="text-xs text-on-surface-variant mt-1.5">Invoice Date: <span id="invDate" class="font-mono text-on-surface">--</span></div>
              <div class="text-xs text-on-surface-variant">Payment Due: <span id="invDueDate" class="font-mono font-bold text-on-surface">--</span></div>
            </div>
          </div>

          <!-- Line Items Table -->
          <div class="rounded-xl border border-outline-variant/60 overflow-hidden">
            <table class="w-full text-body-sm">
              <thead class="bg-surface-container-low/60 border-b border-outline-variant/40 text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">
                <tr>
                  <th class="px-3 py-2 text-left">Fee Description</th>
                  <th class="px-3 py-2 text-center">Frequency</th>
                  <th class="px-3 py-2 text-right">Amount (₹)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/40">
                <tr>
                  <td class="px-3 py-2.5">
                    <div id="invStructureName" class="font-semibold text-on-surface">Fee Structure</div>
                    <div id="invFeeHead" class="text-xs text-on-surface-variant">Tuition Fee</div>
                  </td>
                  <td id="invFrequency" class="px-3 py-2.5 text-center font-medium">Annual</td>
                  <td id="invAssignedAmount" class="px-3 py-2.5 text-right font-mono font-bold text-on-surface">₹0.00</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals Breakdown -->
          <div class="p-3 bg-surface-container-low/50 rounded-xl space-y-1.5 text-body-sm">
            <div class="flex justify-between text-on-surface-variant">
              <span>Gross Fee Amount:</span>
              <span id="invGross" class="font-mono font-semibold">₹0.00</span>
            </div>
            <div class="flex justify-between text-on-surface-variant">
              <span>Discount / Waiver:</span>
              <span id="invDiscount" class="font-mono font-semibold">₹0.00</span>
            </div>
            <div class="flex justify-between font-bold text-on-surface border-t border-outline-variant/40 pt-1">
              <span>Net Invoiced Amount:</span>
              <span id="invNet" class="font-mono text-primary">₹0.00</span>
            </div>
            <div class="flex justify-between text-secondary font-semibold">
              <span>Total Paid Amount:</span>
              <span id="invPaid" class="font-mono">₹0.00</span>
            </div>
            <div class="flex justify-between font-bold text-body-md border-t border-outline-variant/40 pt-1.5">
              <span>Balance Due:</span>
              <span id="invDue" class="font-mono text-error">₹0.00</span>
            </div>
          </div>

          <!-- Double Entry Accounting Trail Box -->
          <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-xs text-on-surface-variant">
            <div class="font-bold text-on-surface text-xs uppercase tracking-wider mb-1 flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px] text-primary">account_balance</span>
              Double-Entry Ledger Audit Trail
            </div>
            <div class="grid grid-cols-2 gap-2 mt-1">
              <div>
                <span class="block text-[10px] text-on-surface-variant uppercase">Debited Account (Asset)</span>
                <span class="font-mono font-semibold text-on-surface">1030 Student Accounts Receivable</span>
              </div>
              <div>
                <span class="block text-[10px] text-on-surface-variant uppercase">Credited Account (Income)</span>
                <span id="invRevenueAccount" class="font-mono font-semibold text-on-surface">4010 Tuition Fee Income</span>
              </div>
            </div>
            <div class="mt-2 text-[11px] pt-1.5 border-t border-outline-variant/40 flex justify-between">
              <span>Journal Voucher: <strong id="invVoucherNo" class="font-mono text-on-surface">TXN-INV-N/A</strong></span>
              <span>Student Total Ledger Receivable: <strong id="invStudentBalance" class="font-mono text-error">₹0.00</strong></span>
            </div>
          </div>

          <!-- Payment History Receipts (if any) -->
          <div id="invReceiptsSection" class="hidden">
            <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1.5">Payments Collected Against This Invoice</span>
            <div class="rounded-xl border border-outline-variant/50 overflow-hidden">
              <table class="w-full text-body-xs">
                <thead class="bg-surface-container-low text-[10px] uppercase text-on-surface-variant">
                  <tr>
                    <th class="px-2.5 py-1.5 text-left">Receipt #</th>
                    <th class="px-2.5 py-1.5 text-left">Date</th>
                    <th class="px-2.5 py-1.5 text-left">Mode</th>
                    <th class="px-2.5 py-1.5 text-right">Amount (₹)</th>
                  </tr>
                </thead>
                <tbody id="invReceiptsBody" class="divide-y divide-outline-variant/30"></tbody>
              </table>
            </div>
          </div>

          <!-- Modal Action Bar -->
          <div class="flex items-center justify-between pt-4 border-t border-outline-variant/40 mt-4">
            <a id="btnCollectPayment" href="#" class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-xs">
              <span class="material-symbols-outlined text-[16px]">add_card</span>Collect Payment
            </a>
            <div class="flex items-center gap-2">
              <a id="btnDedicatedInvoice" href="#" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors text-label-md font-semibold cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">visibility</span>Full Invoice Page
              </a>
              <a id="btnPrintInvoice" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high transition-colors text-label-md font-semibold cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>Print
              </a>
              <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors text-label-md cursor-pointer">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script>
      let currentAssignMode = 'bulk';

      function openAssignModal(mode = 'bulk') {
        switchAssignMode(mode);
        document.getElementById('assignModal').classList.remove('hidden');
      }

      function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
      }

      function switchAssignMode(mode) {
        currentAssignMode = mode;
        document.getElementById('assignment_mode').value = mode;

        const tabBulk = document.getElementById('tabBulk');
        const tabInd = document.getElementById('tabIndividual');
        const divContainer = document.getElementById('divisionFieldContainer');
        const stuContainer = document.getElementById('studentFieldContainer');
        const studentSelect = document.getElementById('assign_student_id');
        const modalTitle = document.getElementById('assignModalTitle');
        const btnSubmit = document.getElementById('btnSubmitAssign');

        if (mode === 'bulk') {
          tabBulk.className = 'flex-1 py-2 text-center text-label-md font-semibold rounded-lg bg-surface-container-lowest text-primary shadow-xs transition-all cursor-pointer';
          tabInd.className = 'flex-1 py-2 text-center text-label-md font-semibold rounded-lg text-on-surface-variant hover:text-on-surface transition-all cursor-pointer';
          divContainer.classList.remove('hidden');
          stuContainer.classList.add('hidden');
          studentSelect.removeAttribute('required');
          modalTitle.textContent = 'Bulk Class Fee Assignment';
          btnSubmit.textContent = 'Assign to Class Students';
        } else {
          tabInd.className = 'flex-1 py-2 text-center text-label-md font-semibold rounded-lg bg-surface-container-lowest text-primary shadow-xs transition-all cursor-pointer';
          tabBulk.className = 'flex-1 py-2 text-center text-label-md font-semibold rounded-lg text-on-surface-variant hover:text-on-surface transition-all cursor-pointer';
          divContainer.classList.add('hidden');
          stuContainer.classList.remove('hidden');
          studentSelect.setAttribute('required', 'required');
          modalTitle.textContent = 'Individual Student Fee Assignment';
          btnSubmit.textContent = 'Generate Student Invoice';

          // Trigger student load if class is already selected
          const classId = document.getElementById('assign_class_id').value;
          if (classId) {
            loadStudentsForClass(classId);
          }
        }
      }

      function onClassChange() {
        const classId = document.getElementById('assign_class_id').value;
        if (currentAssignMode === 'individual' && classId) {
          loadStudentsForClass(classId);
        }
      }

      function loadStudentsForClass(classId) {
        const studentSelect = document.getElementById('assign_student_id');
        const loadingMsg = document.getElementById('studentLoadingMsg');
        studentSelect.innerHTML = '<option value="">-- Loading students... --</option>';
        loadingMsg.classList.remove('hidden');

        fetch('<?php echo site_url("finance/get_class_students_ajax"); ?>/' + classId)
          .then(res => res.json())
          .then(data => {
            loadingMsg.classList.add('hidden');
            studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
            if (data.success && data.students && data.students.length > 0) {
              data.students.forEach(st => {
                const opt = document.createElement('option');
                opt.value = st.student_id;
                const rollStr = st.roll_number ? ` (Roll: ${st.roll_number})` : '';
                const divStr = st.division_name ? ` [Sec: ${st.division_name}]` : '';
                opt.textContent = `${st.first_name} ${st.last_name} — Adm: ${st.admission_number}${rollStr}${divStr}`;
                studentSelect.appendChild(opt);
              });
            } else {
              studentSelect.innerHTML = '<option value="">No active students found in this class</option>';
            }
          })
          .catch(err => {
            loadingMsg.classList.add('hidden');
            studentSelect.innerHTML = '<option value="">Failed to load students</option>';
            console.error('Error loading students:', err);
          });
      }

      // View Invoice AJAX
      function viewInvoiceDetails(assignmentId) {
        document.getElementById('viewInvoiceModal').classList.remove('hidden');
        document.getElementById('invoiceLoading').classList.remove('hidden');
        document.getElementById('invoiceContent').classList.add('hidden');

        fetch('<?php echo site_url("finance/view_invoice_ajax"); ?>/' + assignmentId)
          .then(res => res.json())
          .then(data => {
            document.getElementById('invoiceLoading').classList.add('hidden');
            if (!data.success || !data.invoice) {
              alert(data.message || 'Invoice details could not be loaded.');
              closeInvoiceModal();
              return;
            }

            const inv = data.invoice;
            document.getElementById('modalInvSubtitle').textContent = inv.invoice_number;
            document.getElementById('invStudentName').textContent = `${inv.first_name} ${inv.last_name}`;
            document.getElementById('invStudentAdm').textContent = `Adm #: ${inv.admission_number || '—'}`;
            document.getElementById('invStudentClass').textContent = `Class: ${inv.class_name || ''} ${inv.division_name ? '- ' + inv.division_name : ''}`;
            
            // Format dates
            document.getElementById('invDate').textContent = formatDateStr(inv.invoice_date);
            document.getElementById('invDueDate').textContent = formatDateStr(inv.due_date);

            // Status badge
            const badge = document.getElementById('invStatusBadge');
            badge.textContent = (inv.status || 'Pending').replace('_', ' ');
            if (inv.status === 'Paid') {
              badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container';
            } else if (inv.status === 'Partially_Paid') {
              badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900';
            } else {
              badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container';
            }

            // Fee description & amounts
            document.getElementById('invStructureName').textContent = inv.structure_name || 'Standard Class Fee';
            document.getElementById('invFeeHead').textContent = `${inv.type_name || 'Tuition Fee'} (${inv.type_code || 'TUIT'})`;
            document.getElementById('invFrequency').textContent = inv.frequency || 'Annual';
            document.getElementById('invAssignedAmount').textContent = '₹' + parseFloat(inv.assigned_amount).toFixed(2);
            document.getElementById('invGross').textContent = '₹' + parseFloat(inv.assigned_amount).toFixed(2);
            document.getElementById('invDiscount').textContent = '₹' + parseFloat(inv.discount_amount).toFixed(2);
            document.getElementById('invNet').textContent = '₹' + parseFloat(inv.net_amount).toFixed(2);
            document.getElementById('invPaid').textContent = '₹' + parseFloat(inv.paid_amount).toFixed(2);
            document.getElementById('invDue').textContent = '₹' + parseFloat(inv.due_amount).toFixed(2);

            // Double Entry Info
            document.getElementById('invVoucherNo').textContent = inv.transaction_number || ('TXN-INV-' + inv.id);
            document.getElementById('invRevenueAccount').textContent = `${inv.revenue_account_code || '4010'} ${inv.revenue_account_name || 'Tuition Fee Income'}`;
            document.getElementById('invStudentBalance').textContent = '₹' + parseFloat(data.ledger_balance || 0).toFixed(2);

            // Action Buttons
            document.getElementById('btnDedicatedInvoice').href = '<?php echo site_url("finance/student_invoice"); ?>/' + inv.id;
            document.getElementById('btnPrintInvoice').href = '<?php echo site_url("finance/invoice_print"); ?>/' + inv.id;
            const collectBtn = document.getElementById('btnCollectPayment');
            if (parseFloat(inv.due_amount) > 0) {
              collectBtn.href = '<?php echo site_url("finance/fee_collection"); ?>?student_id=' + inv.student_id + '&assignment_id=' + inv.id;
              collectBtn.classList.remove('hidden');
            } else {
              collectBtn.classList.add('hidden');
            }

            // Receipts table
            const receiptsSec = document.getElementById('invReceiptsSection');
            const receiptsBody = document.getElementById('invReceiptsBody');
            receiptsBody.innerHTML = '';
            if (data.collections && data.collections.length > 0) {
              receiptsSec.classList.remove('hidden');
              data.collections.forEach(c => {
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td class="px-2.5 py-1 font-mono font-bold">${c.receipt_number}</td>
                  <td class="px-2.5 py-1">${formatDateStr(c.receipt_date)}</td>
                  <td class="px-2.5 py-1">${c.payment_mode}</td>
                  <td class="px-2.5 py-1 text-right font-mono font-bold text-secondary">₹${parseFloat(c.amount).toFixed(2)}</td>
                `;
                receiptsBody.appendChild(row);
              });
            } else {
              receiptsSec.classList.add('hidden');
            }

            document.getElementById('invoiceContent').classList.remove('hidden');
          })
          .catch(err => {
            document.getElementById('invoiceLoading').classList.add('hidden');
            alert('Failed to connect to server.');
            console.error('Invoice load error:', err);
            closeInvoiceModal();
          });
      }

      function closeInvoiceModal() {
        document.getElementById('viewInvoiceModal').classList.add('hidden');
      }

      function formatDateStr(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
      }

      function filterInvoiceTable() {
        const input = document.getElementById('invoiceTableSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('invoiceTable');
        const trs = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < trs.length; i++) {
          const text = trs[i].textContent || trs[i].innerText;
          if (text.toLowerCase().indexOf(filter) > -1) {
            trs[i].style.display = '';
          } else {
            trs[i].style.display = 'none';
          }
        }
      }
    </script>
