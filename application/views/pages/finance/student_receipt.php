<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
// Helper function to convert numeric amount to Indian Currency Words
if (!function_exists('amount_in_words_inr')) {
    function amount_in_words_inr($number) {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ? " and " . ($words[$point / 10] ?? '') . " " . ($words[$point = $point % 10] ?? '') . " Paise" : '';
        return trim($result) ? "Rupees " . trim($result) . $points . " Only" : "Rupees Zero Only";
    }
}
?>

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

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">
          <a href="<?php echo site_url('finance/fee_receipts'); ?>" class="hover:text-primary transition-colors">Receipts</a>
          <span>/</span>
          <span class="text-primary font-mono"><?php echo html_escape($receipt->receipt_number); ?></span>
        </div>
        <div class="flex items-center gap-3">
          <h2 class="font-headline-md text-headline-md text-on-surface">Payment Receipt</h2>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
            <?php echo html_escape($receipt->status); ?>
          </span>
        </div>
      </div>
      
      <!-- Action Toolbar -->
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <?php if (!empty($receipt->fee_assignment_id)): ?>
          <a href="<?php echo site_url('finance/student_invoice/' . $receipt->fee_assignment_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined text-[18px]">description</span>View Invoice
          </a>
        <?php endif; ?>

        <a href="<?php echo site_url('finance/student_statement/' . $receipt->student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>Student Statement
        </a>

        <!-- Print Receipt Button -->
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Receipt
        </button>

        <!-- Standalone Clean Print Window -->
        <a href="<?php echo site_url('finance/receipt_print/' . $receipt->id); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors" title="Open in Clean Print Tab">
          <span class="material-symbols-outlined text-[18px]">open_in_new</span>Print Tab
        </a>

        <!-- Collect Another Payment -->
        <a href="<?php echo site_url('finance/fee_collection?student_id=' . $receipt->student_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_card</span>Collect Payment
        </a>
      </div>
    </div>

    <!-- Printable Receipt Container -->
    <div id="receipt-printable-area" class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 md:p-8 mb-8 max-w-4xl mx-auto">
      
      <!-- Institution Branding Header -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-outline-variant/60 mb-6">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0 border border-primary/20">
            <span class="material-symbols-outlined text-primary text-[32px]">school</span>
          </div>
          <div>
            <h1 class="font-headline-sm text-headline-sm font-bold text-on-surface">
              <?php echo html_escape($school->school_name ?? $school->name ?? 'INSTITUTION OF EDUCATION'); ?>
            </h1>
            <p class="text-body-sm text-on-surface-variant mt-0.5">
              <?php echo html_escape($school->address ?? 'Campus Address, City, State'); ?>
            </p>
            <div class="flex items-center gap-3 text-xs text-on-surface-variant mt-1">
              <?php if (!empty($school->phone)): ?>
                <span>Ph: <?php echo html_escape($school->phone); ?></span>
                <span>•</span>
              <?php endif; ?>
              <?php if (!empty($school->email)): ?>
                <span>Email: <?php echo html_escape($school->email); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="text-left sm:text-right shrink-0">
          <div class="inline-block px-3 py-1 rounded-lg bg-secondary-container text-on-secondary-container font-mono font-bold text-sm uppercase tracking-wider mb-2">
            OFFICIAL FEE RECEIPT
          </div>
          <div class="text-body-xs text-on-surface-variant">Receipt Number</div>
          <div class="font-mono font-bold text-base text-primary"><?php echo html_escape($receipt->receipt_number); ?></div>
          <div class="text-body-xs text-on-surface-variant mt-1">Date: <span class="font-semibold text-on-surface"><?php echo date('d M Y', strtotime($receipt->receipt_date)); ?></span></div>
          <?php if (!empty($receipt->transaction_number)): ?>
            <div class="text-[11px] text-on-surface-variant font-mono mt-0.5">Voucher: <?php echo html_escape($receipt->transaction_number); ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Student & Payment Information Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        
        <!-- Student Details -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <div class="text-label-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary">person</span> Student Details
          </div>
          <div class="space-y-1.5 text-body-sm">
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Student Name:</span>
              <span class="font-bold text-on-surface"><?php echo html_escape($receipt->first_name . ' ' . $receipt->last_name); ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Admission No:</span>
              <span class="font-mono font-bold text-primary"><?php echo html_escape($receipt->admission_number ?? $receipt->admission_no ?? '—'); ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Class / Division:</span>
              <span class="font-semibold text-on-surface">
                <?php echo html_escape(($receipt->class_name ?? '—') . (!empty($receipt->division_name) ? ' - ' . $receipt->division_name : '')); ?>
              </span>
            </div>
            <?php if (!empty($receipt->parent_guardian_name)): ?>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Parent / Guardian:</span>
                <span class="text-on-surface"><?php echo html_escape($receipt->parent_guardian_name); ?></span>
              </div>
            <?php endif; ?>
            <?php if (!empty($receipt->student_phone)): ?>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Contact Phone:</span>
                <span class="font-mono text-on-surface"><?php echo html_escape($receipt->student_phone); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Payment Particulars -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <div class="text-label-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-secondary">payments</span> Payment Particulars
          </div>
          <div class="space-y-1.5 text-body-sm">
            <div class="flex justify-between items-center">
              <span class="text-on-surface-variant">Payment Mode:</span>
              <?php
                $mode_icons = [
                  'Cash'          => 'payments',
                  'Bank'          => 'account_balance',
                  'Bank Transfer' => 'sync_alt',
                  'UPI'           => 'qr_code_2',
                  'Card'          => 'credit_card',
                  'Cheque'        => 'receipt_long',
                ];
                $m_icon = $mode_icons[$receipt->payment_mode] ?? 'check_circle';
              ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                <span class="material-symbols-outlined text-[14px]"><?php echo $m_icon; ?></span>
                <?php echo html_escape($receipt->payment_mode); ?>
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Deposit Account:</span>
              <span class="font-semibold text-on-surface">
                <?php echo html_escape($receipt->deposit_account_name ?? 'Cash in Hand'); ?>
                <span class="text-xs text-on-surface-variant font-mono">(<?php echo html_escape($receipt->deposit_account_code ?? '1010'); ?>)</span>
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Linked Invoice #:</span>
              <span class="font-mono font-semibold text-primary">
                <?php if (!empty($receipt->invoice_number)): ?>
                  <a href="<?php echo site_url('finance/student_invoice/' . $receipt->fee_assignment_id); ?>" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">description</span>
                    <?php echo html_escape($receipt->invoice_number); ?>
                  </a>
                <?php else: ?>
                  <span class="italic text-on-surface-variant">General / On-Account</span>
                <?php endif; ?>
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Received By:</span>
              <span class="font-semibold text-on-surface flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px] text-on-surface-variant">person_check</span>
                <?php echo html_escape($receipt->received_by_name ?? 'Accounts Cashier'); ?>
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Reference / UTR #:</span>
              <span class="font-mono font-medium text-on-surface">
                <?php echo html_escape($receipt->reference_number ?: '—'); ?>
              </span>
            </div>
            <?php if (!empty($receipt->remarks)): ?>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Remarks / Note:</span>
                <span class="text-on-surface italic"><?php echo html_escape($receipt->remarks); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Fee Breakdown & Allocation Table -->
      <div class="border border-outline-variant/60 rounded-xl overflow-hidden mb-6">
        <table class="w-full text-left border-collapse text-body-sm">
          <thead>
            <tr class="bg-surface-container-low border-b border-outline-variant/60">
              <th class="px-4 py-3 font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider">#</th>
              <th class="px-4 py-3 font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider">Fee Description / Invoice</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider">Invoice Net</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider">Prior Paid</th>
              <th class="px-4 py-3 text-right font-semibold text-secondary text-[11px] uppercase tracking-wider">Amount Paid</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider">Balance Due</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <tr>
              <td class="px-4 py-3 text-on-surface-variant font-mono">1</td>
              <td class="px-4 py-3">
                <div class="font-bold text-on-surface">
                  <?php echo html_escape($receipt->fee_name ?? $receipt->structure_name ?? 'Student Academic Fees'); ?>
                </div>
                <?php if (!empty($receipt->invoice_number)): ?>
                  <div class="text-xs text-on-surface-variant font-mono mt-0.5">
                    Linked Invoice: <a href="<?php echo site_url('finance/student_invoice/' . $receipt->fee_assignment_id); ?>" class="text-primary hover:underline"><?php echo html_escape($receipt->invoice_number); ?></a>
                  </div>
                <?php else: ?>
                  <div class="text-xs text-on-surface-variant mt-0.5 italic">On-Account Fee Collection</div>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface">
                <?php echo !empty($receipt->invoice_net_amount) ? '₹' . number_format($receipt->invoice_net_amount, 2) : '—'; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface-variant">
                <?php 
                  if (!empty($receipt->invoice_net_amount)) {
                    $prior_paid = max(0, (float)$receipt->invoice_paid_amount - (float)$receipt->amount);
                    echo '₹' . number_format($prior_paid, 2);
                  } else {
                    echo '—';
                  }
                ?>
              </td>
              <td class="px-4 py-3 text-right font-mono font-bold text-secondary text-base">
                ₹<?php echo number_format($receipt->amount, 2); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono font-bold <?php echo ((float)($receipt->invoice_due_amount ?? 0) > 0 ? 'text-error' : 'text-secondary'); ?>">
                <?php echo isset($receipt->invoice_due_amount) ? '₹' . number_format($receipt->invoice_due_amount, 2) : '—'; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payment Summary & Amount in Words Banner -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 mb-6">
        <div class="w-full sm:w-auto">
          <div class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Amount Received in Words</div>
          <div class="text-body-md font-bold text-on-surface mt-0.5 italic">
            <?php echo html_escape(amount_in_words_inr($receipt->amount)); ?>
          </div>
        </div>
        <div class="text-right w-full sm:w-auto shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-outline-variant/40">
          <div class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Total Amount Collected</div>
          <div class="font-mono font-bold text-2xl text-secondary">₹<?php echo number_format($receipt->amount, 2); ?></div>
        </div>
      </div>

      <!-- Accounting Double-Entry Verification Banner -->
      <div class="p-4 rounded-xl bg-surface-container border border-outline-variant/50 mb-8">
        <div class="flex items-center justify-between mb-2">
          <div class="text-label-sm font-bold text-on-surface flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
            Accounting Double-Entry Voucher
          </div>
          <span class="text-xs font-mono font-semibold text-on-surface-variant">Txn #: <?php echo html_escape($receipt->transaction_number ?? 'Auto-Posted'); ?></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-body-xs">
          <div class="p-2.5 rounded-lg bg-surface-container-lowest border border-outline-variant/40 flex justify-between items-center">
            <div>
              <span class="font-bold text-primary block">DR: <?php echo html_escape($receipt->deposit_account_name ?? 'Cash in Hand'); ?> (<?php echo html_escape($receipt->deposit_account_code ?? '1010'); ?>)</span>
              <span class="text-on-surface-variant text-[11px]">Asset Increase (<?php echo html_escape($receipt->deposit_account_type ?? 'Cash'); ?> Account)</span>
            </div>
            <span class="font-mono font-bold text-on-surface text-sm">₹<?php echo number_format($receipt->amount, 2); ?></span>
          </div>
          <div class="p-2.5 rounded-lg bg-surface-container-lowest border border-outline-variant/40 flex justify-between items-center">
            <div>
              <span class="font-bold text-secondary block">CR: Student Accounts Receivable (1030)</span>
              <span class="text-on-surface-variant text-[11px]">Asset / Receivable Decrease (Student Sub-Ledger)</span>
            </div>
            <span class="font-mono font-bold text-on-surface text-sm">₹<?php echo number_format($receipt->amount, 2); ?></span>
          </div>
        </div>
      </div>

      <!-- Signatures and Footer -->
      <div class="pt-8 border-t border-outline-variant/60 flex flex-col sm:flex-row justify-between items-end gap-8">
        <div class="text-body-xs text-on-surface-variant space-y-1">
          <div>• This is a computer-generated official receipt issued by the institution.</div>
          <div>• Keep this receipt safely for fee clearance, examinations, and hall ticket verification.</div>
          <div>• All payments are subject to realization of cheque / bank clearance.</div>
        </div>

        <div class="flex items-center gap-12 text-center shrink-0">
          <div>
            <div class="h-10 border-b border-outline-variant/80 w-36 mb-1 text-center font-semibold text-primary text-xs pt-4">
              <?php echo html_escape($receipt->received_by_name ?? 'Accounts Cashier'); ?>
            </div>
            <div class="text-label-xs font-semibold text-on-surface uppercase">Cashier / Received By</div>
          </div>
          <div>
            <div class="h-12 border-b border-outline-variant/80 w-36 mb-1"></div>
            <div class="text-label-xs font-semibold text-on-surface-variant uppercase">Authorized Signatory</div>
          </div>
        </div>
      </div>

    </div>

    <!-- Print CSS Rules -->
    <style>
    @media print {
      body * {
        visibility: hidden !important;
      }
      #receipt-printable-area, #receipt-printable-area * {
        visibility: visible !important;
      }
      #receipt-printable-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        box-shadow: none !important;
        border: none !important;
      }
    }
    </style>
