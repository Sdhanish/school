<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
if (!function_exists('amount_in_words_inr_print')) {
    function amount_in_words_inr_print($number) {
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Receipt — <?php echo html_escape($receipt->receipt_number); ?></title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: #0f172a;
      background: #f8fafc;
      padding: 30px;
    }
    .receipt-container {
      max-width: 800px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 36px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .header-table {
      width: 100%;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 16px;
      margin-bottom: 20px;
    }
    .school-name {
      font-size: 22px;
      font-weight: 700;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .school-address {
      font-size: 13px;
      color: #475569;
      margin-top: 3px;
    }
    .receipt-badge-title {
      font-size: 14px;
      font-weight: 700;
      color: #0369a1;
      text-transform: uppercase;
      letter-spacing: 1px;
      text-align: right;
    }
    .receipt-num {
      font-family: monospace;
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
      text-align: right;
      margin-top: 2px;
    }
    .meta-date {
      font-size: 12px;
      color: #475569;
      text-align: right;
    }
    .meta-box-grid {
      display: flex;
      gap: 16px;
      margin-bottom: 20px;
    }
    .meta-card {
      flex: 1;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 14px;
      font-size: 13px;
    }
    .meta-card-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #64748b;
      margin-bottom: 8px;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 4px;
    }
    .meta-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
    }
    .meta-label { color: #64748b; }
    .meta-val { font-weight: 600; color: #0f172a; }
    
    .fee-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      font-size: 13px;
    }
    .fee-table th {
      background: #f1f5f9;
      padding: 10px 12px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #475569;
      border: 1px solid #cbd5e1;
    }
    .fee-table td {
      padding: 10px 12px;
      border: 1px solid #cbd5e1;
      color: #0f172a;
    }
    .text-right { text-align: right; }
    .font-mono { font-family: monospace; }
    
    .words-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .words-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #64748b;
    }
    .words-text {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      font-style: italic;
      margin-top: 2px;
    }
    .amount-box {
      text-align: right;
    }
    .amount-total {
      font-size: 20px;
      font-weight: 700;
      color: #0284c7;
      font-family: monospace;
    }
    
    .accounting-box {
      background: #f1f5f9;
      border-left: 4px solid #0284c7;
      border-radius: 4px;
      padding: 10px 14px;
      font-size: 12px;
      color: #475569;
      margin-bottom: 30px;
    }
    .accounting-box strong { color: #0f172a; }
    
    .signature-row {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      padding-top: 10px;
    }
    .sig-block {
      text-align: center;
      width: 180px;
    }
    .sig-line {
      border-bottom: 1px dashed #64748b;
      margin-bottom: 6px;
      height: 40px;
    }
    .sig-title {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      color: #64748b;
    }
    
    .actions-bar {
      max-width: 800px;
      margin: 20px auto 0;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    .btn {
      padding: 8px 18px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-primary { background: #0284c7; color: #fff; border: none; }
    .btn-secondary { background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; }

    @media print {
      body {
        padding: 0;
        background: #fff;
      }
      .receipt-container {
        border: none;
        box-shadow: none;
        padding: 10px;
        max-width: 100%;
      }
      .actions-bar { display: none; }
    }
  </style>
</head>
<body>

  <div class="actions-bar">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <button onclick="window.close()" class="btn btn-secondary">Close Window</button>
  </div>

  <div class="receipt-container">
    
    <!-- Header -->
    <table class="header-table">
      <tr>
        <td style="vertical-align: top;">
          <div class="school-name"><?php echo html_escape($school->school_name ?? $school->name ?? 'INSTITUTION OF EDUCATION'); ?></div>
          <div class="school-address"><?php echo html_escape($school->address ?? 'Campus Address, City, State'); ?></div>
          <?php if (!empty($school->phone) || !empty($school->email)): ?>
            <div class="school-address">Phone: <?php echo html_escape($school->phone ?? '—'); ?> | Email: <?php echo html_escape($school->email ?? '—'); ?></div>
          <?php endif; ?>
        </td>
        <td style="vertical-align: top; text-align: right;">
          <div class="receipt-badge-title">Official Fee Receipt</div>
          <div class="receipt-num"><?php echo html_escape($receipt->receipt_number); ?></div>
          <div class="meta-date">Date: <?php echo date('d M Y', strtotime($receipt->receipt_date)); ?></div>
          <?php if (!empty($receipt->transaction_number)): ?>
            <div class="meta-date font-mono">Voucher: <?php echo html_escape($receipt->transaction_number); ?></div>
          <?php endif; ?>
        </td>
      </tr>
    </table>

    <!-- Student & Payment Particulars Cards -->
    <div class="meta-box-grid">
      <div class="meta-card">
        <div class="meta-card-title">Student Information</div>
        <div class="meta-row">
          <span class="meta-label">Student Name:</span>
          <span class="meta-val"><?php echo html_escape($receipt->first_name . ' ' . $receipt->last_name); ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Admission No:</span>
          <span class="meta-val font-mono"><?php echo html_escape($receipt->admission_number ?? $receipt->admission_no ?? '—'); ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Class / Div:</span>
          <span class="meta-val"><?php echo html_escape(($receipt->class_name ?? '—') . (!empty($receipt->division_name) ? ' - ' . $receipt->division_name : '')); ?></span>
        </div>
        <?php if (!empty($receipt->parent_guardian_name)): ?>
          <div class="meta-row">
            <span class="meta-label">Parent/Guardian:</span>
            <span class="meta-val"><?php echo html_escape($receipt->parent_guardian_name); ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="meta-card">
        <div class="meta-card-title">Payment & Deposit Account</div>
        <div class="meta-row">
          <span class="meta-label">Payment Mode:</span>
          <span class="meta-val"><?php echo html_escape($receipt->payment_mode); ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Deposit Account:</span>
          <span class="meta-val"><?php echo html_escape($receipt->deposit_account_name ?? 'Cash in Hand'); ?> (<?php echo html_escape($receipt->deposit_account_code ?? '1010'); ?>)</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Ref / UTR / Cheque #:</span>
          <span class="meta-val font-mono"><?php echo html_escape($receipt->reference_number ?: '—'); ?></span>
        </div>
        <?php if (!empty($receipt->remarks)): ?>
          <div class="meta-row">
            <span class="meta-label">Remarks:</span>
            <span class="meta-val"><?php echo html_escape($receipt->remarks); ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Particulars Table -->
    <table class="fee-table">
      <thead>
        <tr>
          <th style="width: 40px;">#</th>
          <th>Fee Description / Particulars</th>
          <th class="text-right" style="width: 120px;">Invoice Total</th>
          <th class="text-right" style="width: 120px;">Prior Paid</th>
          <th class="text-right" style="width: 130px;">Amount Paid (₹)</th>
          <th class="text-right" style="width: 120px;">Balance Due</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="font-mono text-center">1</td>
          <td>
            <strong><?php echo html_escape($receipt->fee_name ?? $receipt->structure_name ?? 'Student Academic Fees'); ?></strong>
            <?php if (!empty($receipt->invoice_number)): ?>
              <div style="font-size: 11px; color: #64748b; font-family: monospace;">Invoice: <?php echo html_escape($receipt->invoice_number); ?></div>
            <?php endif; ?>
          </td>
          <td class="text-right font-mono">
            <?php echo !empty($receipt->invoice_net_amount) ? '₹' . number_format($receipt->invoice_net_amount, 2) : '—'; ?>
          </td>
          <td class="text-right font-mono">
            <?php 
              if (!empty($receipt->invoice_net_amount)) {
                $prior_paid = max(0, (float)$receipt->invoice_paid_amount - (float)$receipt->amount);
                echo '₹' . number_format($prior_paid, 2);
              } else {
                echo '—';
              }
            ?>
          </td>
          <td class="text-right font-mono" style="font-weight: 700; color: #0284c7; font-size: 14px;">
            ₹<?php echo number_format($receipt->amount, 2); ?>
          </td>
          <td class="text-right font-mono" style="font-weight: 600;">
            <?php echo isset($receipt->invoice_due_amount) ? '₹' . number_format($receipt->invoice_due_amount, 2) : '—'; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Amount in Words -->
    <div class="words-box">
      <div>
        <div class="words-label">Amount in Words</div>
        <div class="words-text"><?php echo html_escape(amount_in_words_inr_print($receipt->amount)); ?></div>
      </div>
      <div class="amount-box">
        <div class="words-label">Total Received</div>
        <div class="amount-total">₹<?php echo number_format($receipt->amount, 2); ?></div>
      </div>
    </div>

    <!-- Double-Entry Audit Line -->
    <div class="accounting-box">
      <strong>Accounting Double-Entry:</strong>
      DR: <?php echo html_escape($receipt->deposit_account_name ?? 'Cash in Hand'); ?> (<?php echo html_escape($receipt->deposit_account_code ?? '1010'); ?>) ₹<?php echo number_format($receipt->amount, 2); ?> &nbsp;|&nbsp;
      CR: Student Accounts Receivable (1030) ₹<?php echo number_format($receipt->amount, 2); ?> &nbsp;|&nbsp;
      Status: Posted to General Ledger
    </div>

    <!-- Signatures -->
    <div class="signature-row">
      <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-title">Cashier / Accountant</div>
      </div>
      <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-title">Authorized Signatory / Seal</div>
      </div>
    </div>

  </div>

</body>
</html>
