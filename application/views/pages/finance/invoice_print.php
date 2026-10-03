<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fee Invoice — <?php echo html_escape($invoice->invoice_number); ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: #1e293b;
      margin: 0;
      padding: 30px;
      background: #f8fafc;
    }
    .invoice-card {
      max-width: 800px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 36px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .header-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #f1f5f9;
      padding-bottom: 20px;
      margin-bottom: 24px;
    }
    .school-title {
      font-size: 24px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 4px 0;
    }
    .invoice-tag {
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #0284c7;
    }
    .invoice-meta {
      text-align: right;
    }
    .inv-num {
      font-family: monospace;
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
    }
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 9999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      margin-top: 6px;
    }
    .badge-paid { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fee2e2; color: #991b1b; }
    .badge-partial { background: #fef3c7; color: #92400e; }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
      padding: 16px;
      background: #f8fafc;
      border-radius: 8px;
    }
    .info-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #64748b;
      font-weight: 600;
      margin-bottom: 4px;
    }
    .info-val {
      font-size: 14px;
      color: #1e293b;
      font-weight: 600;
    }
    .info-sub {
      font-size: 12px;
      color: #64748b;
      font-family: monospace;
      margin-top: 2px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }
    th {
      background: #f1f5f9;
      color: #475569;
      text-align: left;
      padding: 10px 14px;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    td {
      padding: 12px 14px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 14px;
    }
    .text-right { text-align: right; }
    .font-mono { font-family: monospace; }
    .font-bold { font-weight: 700; }

    .summary-box {
      margin-left: auto;
      width: 320px;
      margin-bottom: 24px;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 6px 0;
      font-size: 14px;
      color: #475569;
    }
    .summary-total {
      border-top: 2px solid #e2e8f0;
      padding-top: 10px;
      margin-top: 6px;
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
    }

    .accounting-trail {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      padding: 14px;
      margin-bottom: 24px;
      font-size: 12px;
      color: #475569;
    }
    .accounting-trail strong { color: #0f172a; }

    .actions-bar {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 20px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 18px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-primary { background: #0284c7; color: #fff; border: none; }
    .btn-primary:hover { background: #0369a1; }
    .btn-secondary { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background: #f8fafc; }

    @media print {
      body { background: #fff; padding: 0; }
      .invoice-card { border: none; box-shadow: none; padding: 0; width: 100%; max-width: 100%; }
      .actions-bar { display: none; }
    }
  </style>
</head>
<body>

  <div class="invoice-card">
    <div class="header-row">
      <div>
        <div class="invoice-tag">Student Fee Invoice</div>
        <h1 class="school-title"><?php echo html_escape($school->school_name ?? 'Educational Institution'); ?></h1>
        <?php if (!empty($school->address)): ?>
          <div style="font-size: 13px; color: #475569; margin-top: 2px;"><?php echo html_escape($school->address); ?></div>
        <?php endif; ?>
        <?php if (!empty($school->phone) || !empty($school->email)): ?>
          <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
            <?php echo html_escape($school->phone ? 'Tel: ' . $school->phone : ''); ?>
            <?php echo html_escape($school->email ? ' | ' . $school->email : ''); ?>
          </div>
        <?php endif; ?>
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Academic Year: 2026–2027</div>
      </div>
      <div class="invoice-meta">
        <div class="inv-num"><?php echo html_escape($invoice->invoice_number); ?></div>
        <div style="font-size: 13px; color: #64748b; margin-top: 3px;">Date: <?php echo date('d M Y', strtotime($invoice->invoice_date)); ?></div>
        <div style="font-size: 13px; color: #64748b;">Due Date: <strong><?php echo date('d M Y', strtotime($invoice->due_date)); ?></strong></div>
        <?php
          $status_class = 'badge-pending';
          if ($invoice->status === 'Paid') $status_class = 'badge-paid';
          elseif ($invoice->status === 'Partially_Paid') $status_class = 'badge-partial';
        ?>
        <div><span class="badge <?php echo $status_class; ?>"><?php echo html_escape(str_replace('_', ' ', $invoice->status)); ?></span></div>
      </div>
    </div>

    <div class="grid-2">
      <div>
        <div class="info-label">Student Details (Billed To)</div>
        <div class="info-val"><?php echo html_escape($invoice->first_name . ' ' . $invoice->last_name); ?></div>
        <div class="info-sub">Admission #: <?php echo html_escape($invoice->admission_number); ?></div>
        <div style="font-size: 13px; color: #334155; margin-top: 4px;">
          Class: <strong><?php echo html_escape($invoice->class_name . ($invoice->division_name ? ' - ' . $invoice->division_name : '')); ?></strong>
        </div>
        <?php if (!empty($invoice->student_phone)): ?>
          <div style="font-size: 12px; color: #64748b;">Phone: <?php echo html_escape($invoice->student_phone); ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="info-label">Accounting & Ledger Status</div>
        <div class="info-val">General Journal Entry Posted</div>
        <div class="info-sub">Ref Voucher: <?php echo html_escape($invoice->transaction_number ?: 'TXN-INV-N/A'); ?></div>
        <div style="font-size: 13px; color: #334155; margin-top: 4px;">
          Debited: <strong>Student Accounts Receivable (1030)</strong>
        </div>
        <div style="font-size: 13px; color: #334155;">
          Credited: <strong><?php echo html_escape(($invoice->revenue_account_name ?: 'Tuition Fee Income') . ' (' . ($invoice->revenue_account_code ?: '4010') . ')'); ?></strong>
        </div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Fee Description / Head</th>
          <th>Frequency</th>
          <th class="text-right">Amount (₹)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>
            <strong><?php echo html_escape($invoice->structure_name ?: 'Standard Fee'); ?></strong>
            <div style="font-size: 12px; color: #64748b;"><?php echo html_escape($invoice->type_name ?: 'Tuition Fee'); ?> (Code: <?php echo html_escape($invoice->type_code ?: 'TUIT'); ?>)</div>
          </td>
          <td><?php echo html_escape($invoice->frequency ?: 'Annual'); ?></td>
          <td class="text-right font-mono font-bold">₹<?php echo number_format($invoice->assigned_amount, 2); ?></td>
        </tr>
      </tbody>
    </table>

    <div class="summary-box">
      <div class="summary-row">
        <span>Invoiced Amount:</span>
        <span class="font-mono">₹<?php echo number_format($invoice->assigned_amount, 2); ?></span>
      </div>
      <div class="summary-row">
        <span>Discount / Waiver:</span>
        <span class="font-mono">₹<?php echo number_format($invoice->discount_amount, 2); ?></span>
      </div>
      <div class="summary-row" style="font-weight: 600; color: #1e293b;">
        <span>Net Amount:</span>
        <span class="font-mono">₹<?php echo number_format($invoice->net_amount, 2); ?></span>
      </div>
      <div class="summary-row" style="color: #166534; font-weight: 600;">
        <span>Amount Paid:</span>
        <span class="font-mono">₹<?php echo number_format($invoice->paid_amount, 2); ?></span>
      </div>
      <div class="summary-row summary-total" style="<?php echo ($invoice->due_amount > 0) ? 'color: #b91c1c;' : 'color: #166534;'; ?>">
        <span>Balance Due:</span>
        <span class="font-mono">₹<?php echo number_format($invoice->due_amount, 2); ?></span>
      </div>
    </div>

    <?php if (!empty($collections)): ?>
      <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
        <div class="info-label" style="margin-bottom: 8px;">Receipts & Payments Collected</div>
        <table>
          <thead>
            <tr>
              <th>Receipt #</th>
              <th>Date</th>
              <th>Mode</th>
              <th>Reference</th>
              <th class="text-right">Amount (₹)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($collections as $c): ?>
              <tr>
                <td class="font-mono font-bold"><?php echo html_escape($c->receipt_number); ?></td>
                <td><?php echo date('d M Y', strtotime($c->receipt_date)); ?></td>
                <td><?php echo html_escape($c->payment_mode); ?></td>
                <td class="font-mono text-sm"><?php echo html_escape($c->reference_number ?: '—'); ?></td>
                <td class="text-right font-mono font-bold" style="color: #166534;">₹<?php echo number_format($c->amount, 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="accounting-trail">
      <strong>Double-Entry Ledger Note:</strong> This fee invoice debited Student Accounts Receivable (Code 1030, Student Sub-Ledger) and credited Fee Revenue (<?php echo html_escape($invoice->revenue_account_code ?: '4010'); ?>). Payment due date is strictly binding on <strong><?php echo date('d M Y', strtotime($invoice->due_date)); ?></strong>. Current running student ledger balance: <strong>₹<?php echo number_format($ledger_balance, 2); ?></strong>.
    </div>

    <div class="actions-bar">
      <button onclick="window.print()" class="btn btn-primary">
        <span>🖨️</span> Print Invoice
      </button>
      <button onclick="window.close()" class="btn btn-secondary">
        Close Window
      </button>
    </div>
  </div>

</body>
</html>
