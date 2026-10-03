<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$student_name = trim(($invoice->first_name ?? '') . ' ' . ($invoice->last_name ?? ''));
$is_overdue = (strtotime($invoice->due_date) < time() && (float)$invoice->due_amount > 0);

$badge_class = 'bg-surface-container-high text-on-surface-variant';
if ($invoice->status === 'Paid') {
    $badge_class = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
} elseif ($invoice->status === 'Partially_Paid') {
    $badge_class = 'bg-amber-100 text-amber-900 border border-amber-300';
} elseif ($invoice->status === 'Pending') {
    $badge_class = 'bg-rose-100 text-rose-800 border border-rose-300';
}
?>

    <!-- Print Stylesheet -->
    <style>
      @media print {
        header, sidebar, nav, .app-sidebar, .app-header, .no-print, footer, #gemini-nav {
          display: none !important;
        }
        body, main, .app-main, .main-content {
          margin: 0 !important;
          padding: 0 !important;
          background: #ffffff !important;
        }
        .invoice-container {
          border: none !important;
          box-shadow: none !important;
          padding: 0 !important;
          max-width: 100% !important;
        }
        .page-break {
          page-break-after: always;
        }
      }
    </style>

    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 no-print">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <a href="<?php echo site_url('finance/fee_assignments'); ?>" class="text-[13px] font-semibold text-primary hover:underline flex items-center gap-1 transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>Back to Fee Assignments & Invoices
          </a>
          <span class="text-on-surface-variant/40">•</span>
          <a href="<?php echo site_url('finance/student_statement/' . $invoice->student_id); ?>" class="text-[13px] text-on-surface-variant hover:text-on-surface flex items-center gap-1 transition-colors">
            <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>Student Ledger
          </a>
        </div>
        <div class="flex items-center gap-3">
          <h2 class="font-headline-md text-headline-md text-on-surface">Student Fee Invoice</h2>
          <span class="font-mono text-sm font-bold px-2.5 py-0.5 rounded-lg bg-surface-container-high text-primary border border-outline-variant/60">
            <?php echo html_escape($invoice->invoice_number); ?>
          </span>
        </div>
      </div>
      
      <!-- Actions: View, Print, Export PDF, Collect Payment -->
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <?php if ((float)$invoice->due_amount > 0): ?>
          <a href="<?php echo site_url('finance/fee_collection?student_id=' . $invoice->student_id . '&assignment_id=' . $invoice->id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add_card</span>Collect Payment
          </a>
        <?php endif; ?>

        <!-- Print Invoice Button -->
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Invoice
        </button>

        <!-- Standalone Print Window -->
        <a href="<?php echo site_url('finance/invoice_print/' . $invoice->id); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors" title="Open in Clean Print Tab">
          <span class="material-symbols-outlined text-[18px]">open_in_new</span>Print Tab
        </a>

        <!-- PDF Export (Future/Optional Functionality) -->
        <button type="button" onclick="exportPdfNotice()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer" title="Export as PDF Document">
          <span class="material-symbols-outlined text-[18px] text-rose-600">picture_as_pdf</span>Export PDF
        </button>
      </div>
    </div>

    <!-- Main Invoice Document Card -->
    <div class="invoice-container elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 p-6 sm:p-10 mb-8 max-w-4xl mx-auto">
      
      <!-- 1. Header: School / College Identity & Invoice Label -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b-2 border-outline-variant/40">
        <div>
          <span class="text-[12px] font-bold tracking-widest text-primary uppercase block mb-1">Official Student Fee Billing</span>
          <h1 class="text-2xl sm:text-3xl font-bold font-headline-md text-on-surface tracking-tight">
            <?php echo html_escape($school->school_name ?? 'Educational Institution'); ?>
          </h1>
          <?php if (!empty($school->address)): ?>
            <p class="text-body-sm text-on-surface-variant mt-1 max-w-md"><?php echo html_escape($school->address); ?></p>
          <?php endif; ?>
          <div class="flex flex-wrap items-center gap-4 text-body-xs text-on-surface-variant font-medium mt-2">
            <?php if (!empty($school->phone)): ?>
              <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span><?php echo html_escape($school->phone); ?></span>
            <?php endif; ?>
            <?php if (!empty($school->email)): ?>
              <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">mail</span><?php echo html_escape($school->email); ?></span>
            <?php endif; ?>
            <?php if (!empty($school->website)): ?>
              <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">language</span><?php echo html_escape($school->website); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="sm:text-right shrink-0">
          <div class="text-xl sm:text-2xl font-bold font-mono text-on-surface tracking-tight">
            <?php echo html_escape($invoice->invoice_number); ?>
          </div>
          <div class="mt-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold tracking-wide uppercase <?php echo $badge_class; ?>">
              <span class="w-2 h-2 rounded-full <?php echo ($invoice->status === 'Paid') ? 'bg-emerald-600' : (($invoice->status === 'Partially_Paid') ? 'bg-amber-600' : 'bg-rose-600'); ?>"></span>
              <?php echo html_escape(str_replace('_', ' ', $invoice->status)); ?>
            </span>
          </div>
          <div class="text-body-xs text-on-surface-variant mt-2 font-mono">
            Academic Year: 2026–2027
          </div>
        </div>
      </div>

      <!-- 2. Invoice Dates & Status Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-5 border-b border-outline-variant/40 bg-surface-container-low/40 rounded-xl px-5 my-6">
        <div>
          <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block">Invoice Date</span>
          <span class="text-body-md font-bold text-on-surface mt-0.5 block font-mono">
            <?php echo date('d M Y', strtotime($invoice->invoice_date)); ?>
          </span>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block">Payment Due Date</span>
          <span class="text-body-md font-bold mt-0.5 block font-mono <?php echo $is_overdue ? 'text-error flex items-center gap-1' : 'text-on-surface'; ?>">
            <?php if ($is_overdue): ?><span class="material-symbols-outlined text-[16px]">warning</span><?php endif; ?>
            <?php echo date('d M Y', strtotime($invoice->due_date)); ?>
          </span>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block">Net Invoiced</span>
          <span class="text-body-md font-bold text-primary mt-0.5 block font-mono">
            ₹<?php echo number_format($invoice->net_amount, 2); ?>
          </span>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block">Balance Outstanding</span>
          <span class="text-body-md font-bold mt-0.5 block font-mono <?php echo ((float)$invoice->due_amount > 0) ? 'text-error' : 'text-emerald-700'; ?>">
            ₹<?php echo number_format($invoice->due_amount, 2); ?>
          </span>
        </div>
      </div>

      <!-- 3. Two Columns: Billed Student & Accounting Status -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-outline-variant/40">
        <!-- Student Information -->
        <div class="p-5 rounded-xl bg-surface-container-low/50 border border-outline-variant/40">
          <div class="flex items-center gap-2 mb-3">
            <span class="p-1.5 rounded-lg bg-primary/10 text-primary material-symbols-outlined text-[18px]">person</span>
            <span class="text-label-md font-bold text-on-surface uppercase tracking-wider">Billed To (Student)</span>
          </div>
          <div class="text-lg font-bold text-on-surface mb-1">
            <?php echo html_escape($student_name); ?>
          </div>
          <div class="space-y-1 text-body-sm text-on-surface-variant">
            <div>Admission Number: <strong class="text-on-surface font-mono"><?php echo html_escape($invoice->admission_number); ?></strong></div>
            <div>Class & Section: <strong class="text-on-surface"><?php echo html_escape($invoice->class_name . ($invoice->division_name ? ' - ' . $invoice->division_name : '')); ?></strong></div>
            <?php if (!empty($invoice->student_phone)): ?>
              <div>Phone: <span class="font-mono"><?php echo html_escape($invoice->student_phone); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($invoice->student_email)): ?>
              <div>Email: <span><?php echo html_escape($invoice->student_email); ?></span></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Double-Entry Accounting Information -->
        <div class="p-5 rounded-xl bg-surface-container-low/50 border border-outline-variant/40">
          <div class="flex items-center gap-2 mb-3">
            <span class="p-1.5 rounded-lg bg-secondary/10 text-secondary material-symbols-outlined text-[18px]">account_balance</span>
            <span class="text-label-md font-bold text-on-surface uppercase tracking-wider">Double-Entry Accounting Reference & Ledger Status</span>
          </div>
          <div class="space-y-2 text-body-sm text-on-surface-variant">
            <div class="flex items-start justify-between">
              <span>Journal Voucher Ref:</span>
              <strong class="font-mono text-on-surface"><?php echo html_escape($invoice->transaction_number ?: 'TXN-INV-' . $invoice->id); ?></strong>
            </div>
            <div class="flex items-start justify-between">
              <span>Debited Account (Asset):</span>
              <strong class="font-mono text-primary text-right">1030 Student Accounts Receivable</strong>
            </div>
            <div class="flex items-start justify-between">
              <span>Credited Account (Income):</span>
              <strong class="font-mono text-secondary text-right">
                <?php echo html_escape(($invoice->revenue_account_code ?: '4010') . ' ' . ($invoice->revenue_account_name ?: 'Tuition Fee Income')); ?>
              </strong>
            </div>
            <div class="flex items-start justify-between pt-2 border-t border-outline-variant/40">
              <span>Total Student Ledger Due:</span>
              <strong class="font-mono <?php echo ($ledger_balance > 0) ? 'text-error' : 'text-emerald-700'; ?>">
                ₹<?php echo number_format($ledger_balance, 2); ?>
              </strong>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Fee Particulars Table -->
      <div class="mt-6 mb-6">
        <h3 class="text-label-lg font-bold text-on-surface mb-3 uppercase tracking-wider flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[18px] text-primary">list_alt</span>
          Fee Particulars & Line Items
        </h3>
        <div class="rounded-xl border border-outline-variant/60 overflow-hidden">
          <table class="w-full text-body-md border-collapse">
            <thead>
              <tr class="bg-surface-container-low text-on-surface-variant border-b border-outline-variant/60 text-[11px] uppercase tracking-wider font-semibold">
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Fee Particulars / Head</th>
                <th class="px-4 py-3 text-center">Frequency</th>
                <th class="px-4 py-3 text-right">Gross Amount (₹)</th>
                <th class="px-4 py-3 text-right">Discount (₹)</th>
                <th class="px-4 py-3 text-right">Net Payable (₹)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
              <tr class="hover:bg-surface-container-low/20">
                <td class="px-4 py-3.5 font-mono text-on-surface-variant">01</td>
                <td class="px-4 py-3.5">
                  <div class="font-bold text-on-surface text-body-md">
                    <?php echo html_escape($invoice->structure_name ?: 'Institutional Fee'); ?>
                  </div>
                  <div class="text-body-xs text-on-surface-variant mt-0.5">
                    Category Head: <span class="font-semibold"><?php echo html_escape($invoice->type_name ?: 'Tuition Fee'); ?></span>
                    <span class="font-mono text-xs">(Code: <?php echo html_escape($invoice->type_code ?: 'TUIT'); ?>)</span>
                  </div>
                </td>
                <td class="px-4 py-3.5 text-center">
                  <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-surface-container-high text-on-surface">
                    <?php echo html_escape($invoice->frequency ?: 'Annual'); ?>
                  </span>
                </td>
                <td class="px-4 py-3.5 text-right font-mono font-semibold text-on-surface">
                  ₹<?php echo number_format($invoice->assigned_amount, 2); ?>
                </td>
                <td class="px-4 py-3.5 text-right font-mono text-on-surface-variant">
                  ₹<?php echo number_format($invoice->discount_amount, 2); ?>
                </td>
                <td class="px-4 py-3.5 text-right font-mono font-bold text-primary text-body-lg">
                  ₹<?php echo number_format($invoice->net_amount, 2); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Totals & Financial Summary Card -->
      <div class="flex flex-col sm:flex-row justify-end mb-8">
        <div class="w-full sm:w-80 p-5 rounded-xl bg-surface-container-low/60 border border-outline-variant/40 space-y-2.5 text-body-md">
          <div class="flex justify-between text-on-surface-variant text-body-sm">
            <span>Gross Invoiced:</span>
            <span class="font-mono font-semibold text-on-surface">₹<?php echo number_format($invoice->assigned_amount, 2); ?></span>
          </div>
          <div class="flex justify-between text-on-surface-variant text-body-sm">
            <span>Concessions / Discounts:</span>
            <span class="font-mono text-on-surface-variant">₹<?php echo number_format($invoice->discount_amount, 2); ?></span>
          </div>
          <div class="flex justify-between font-bold text-body-md text-on-surface border-t border-outline-variant/40 pt-2">
            <span>Net Invoiced Amount:</span>
            <span class="font-mono text-primary">₹<?php echo number_format($invoice->net_amount, 2); ?></span>
          </div>
          <div class="flex justify-between text-emerald-800 font-semibold text-body-sm">
            <span>Total Amount Paid:</span>
            <span class="font-mono text-emerald-700">₹<?php echo number_format($invoice->paid_amount, 2); ?></span>
          </div>
          <div class="flex justify-between font-bold text-lg border-t-2 border-outline-variant/60 pt-2.5 <?php echo ((float)$invoice->due_amount > 0) ? 'text-error' : 'text-emerald-700'; ?>">
            <span>Balance Due:</span>
            <span class="font-mono">₹<?php echo number_format($invoice->due_amount, 2); ?></span>
          </div>
        </div>
      </div>

      <!-- 6. Payment Collection Records / Receipts History -->
      <div class="mb-8 pt-6 border-t border-outline-variant/40">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-label-lg font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px] text-secondary">receipt</span>
            Payment Collections & Receipts History
          </h3>
          <span class="text-body-xs font-mono text-on-surface-variant"><?php echo count($collections ?? []); ?> Payment(s) Recorded</span>
        </div>

        <?php if (!empty($collections)): ?>
          <div class="rounded-xl border border-outline-variant/60 overflow-hidden">
            <table class="w-full text-body-sm border-collapse">
              <thead>
                <tr class="bg-surface-container-low text-on-surface-variant text-[11px] uppercase tracking-wider font-semibold border-b border-outline-variant/60">
                  <th class="px-4 py-2.5 text-left">Receipt Number</th>
                  <th class="px-4 py-2.5 text-left">Date</th>
                  <th class="px-4 py-2.5 text-left">Payment Mode</th>
                  <th class="px-4 py-2.5 text-left">Transaction Reference</th>
                  <th class="px-4 py-2.5 text-center">Status</th>
                  <th class="px-4 py-2.5 text-right">Amount Paid (₹)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php foreach ($collections as $c): ?>
                  <tr class="hover:bg-surface-container-low/20">
                    <td class="px-4 py-2.5 font-mono font-bold text-primary">
                      <a href="<?php echo site_url('finance/student_receipt/' . $c->id); ?>" class="hover:underline flex items-center gap-1" title="View Official Receipt">
                        <span class="material-symbols-outlined text-[15px]">receipt</span>
                        <?php echo html_escape($c->receipt_number); ?>
                      </a>
                    </td>
                    <td class="px-4 py-2.5 font-mono"><?php echo date('d M Y', strtotime($c->receipt_date)); ?></td>
                    <td class="px-4 py-2.5 font-medium"><?php echo html_escape($c->payment_mode); ?></td>
                    <td class="px-4 py-2.5 font-mono text-xs text-on-surface-variant"><?php echo html_escape($c->reference_number ?: '—'); ?></td>
                    <td class="px-4 py-2.5 text-center">
                      <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                        <?php echo html_escape($c->status ?: 'Valid'); ?>
                      </span>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-emerald-700">
                      ₹<?php echo number_format($c->amount, 2); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-5 rounded-xl bg-surface-container-low/30 border border-outline-variant/30 text-center text-on-surface-variant text-body-sm">
            <span class="material-symbols-outlined text-3xl mb-1 text-on-surface-variant/40 block">pending_actions</span>
            No payment receipts recorded against this invoice yet.
            <?php if ((float)$invoice->due_amount > 0): ?>
              <div class="mt-2">
                <a href="<?php echo site_url('finance/fee_collection?student_id=' . $invoice->student_id . '&assignment_id=' . $invoice->id); ?>" class="text-secondary font-semibold hover:underline text-body-xs inline-flex items-center gap-1">
                  <span class="material-symbols-outlined text-[14px]">point_of_sale</span>Collect Payment Now
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 7. Institutional Signatures & Legal Footer -->
      <div class="pt-8 border-t-2 border-outline-variant/40 flex flex-col sm:flex-row items-center justify-between gap-6 text-on-surface-variant text-body-xs">
        <div>
          <p class="font-semibold text-on-surface">Terms & Instructions:</p>
          <ul class="list-disc list-inside mt-1 space-y-0.5 text-on-surface-variant/80">
            <li>Payment is strictly due on or before <strong><?php echo date('d M Y', strtotime($invoice->due_date)); ?></strong>.</li>
            <li>All fee remittances must reference invoice number <strong><?php echo html_escape($invoice->invoice_number); ?></strong>.</li>
            <li>This document is a computer-generated institutional accounting invoice.</li>
          </ul>
        </div>
        <div class="text-center sm:text-right shrink-0 pt-6 sm:pt-0">
          <div class="w-44 border-b border-outline-variant/80 mb-1.5 mx-auto sm:ml-auto"></div>
          <span class="text-xs font-semibold text-on-surface uppercase tracking-wider block">Authorized Signatory</span>
          <span class="text-[11px] text-on-surface-variant">Accounts & Finance Office</span>
        </div>
      </div>

    </div>

    <!-- Toast Notification for PDF Export -->
    <div id="pdfExportToast" class="fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-surface-container-highest border border-outline-variant shadow-lg text-body-sm text-on-surface hidden flex items-center gap-3 transition-all duration-300">
      <span class="material-symbols-outlined text-primary text-[24px]">info</span>
      <div>
        <div class="font-bold">PDF Export Ready</div>
        <div class="text-xs text-on-surface-variant">Use the browser print dialog to "Save as PDF" for pixel-perfect vectorized output. Direct server PDF queue will be enabled in future release.</div>
      </div>
      <button onclick="document.getElementById('pdfExportToast').classList.add('hidden')" class="p-1 text-on-surface-variant hover:text-on-surface cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>

    <script>
      function exportPdfNotice() {
        const toast = document.getElementById('pdfExportToast');
        toast.classList.remove('hidden');
        setTimeout(() => {
          // Trigger print dialog where user can choose "Save as PDF"
          window.print();
        }, 600);
      }
    </script>
