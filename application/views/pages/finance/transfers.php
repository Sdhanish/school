<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
  <div class="mb-5 p-4 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-3 border border-secondary/20 shadow-xs">
    <span class="material-symbols-outlined text-[22px] text-secondary">check_circle</span>
    <span><?php echo html_escape($this->session->flashdata('success')); ?></span>
  </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
  <div class="mb-5 p-4 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-3 border border-error/20 shadow-xs">
    <span class="material-symbols-outlined text-[22px] text-error">error</span>
    <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
  </div>
<?php endif; ?>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[28px]">swap_horiz</span>
      <h1 class="font-headline-md text-headline-md font-bold text-on-surface">Inter-Account Fund Transfers</h1>
    </div>
    <p class="text-body-md text-on-surface-variant mt-1">Execute contra fund transfers between Cash & Bank accounts with atomic double-entry balance preservation.</p>
  </div>
  <div class="flex items-center gap-2.5 flex-wrap shrink-0">
    <button onclick="openTransferModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-all shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[20px]">sync_alt</span>Transfer Funds
    </button>
    <a href="<?php echo site_url('finance/cash_accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">payments</span>Cash Accounts
    </a>
    <a href="<?php echo site_url('finance/bank_accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_balance</span>Bank Accounts
    </a>
  </div>
</div>

<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- Total Transferred Volume -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Total Transferred (Year)</p>
      <h3 class="text-headline-sm font-bold text-on-surface mt-1">₹<?php echo number_format($stats->total_amount, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5"><?php echo $stats->completed_count; ?> completed transfers</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
      <span class="material-symbols-outlined text-[26px]">swap_horiz</span>
    </div>
  </div>

  <!-- Cash to Bank Volume -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Cash → Bank Deposits</p>
      <h3 class="text-headline-sm font-bold text-secondary mt-1">₹<?php echo number_format($stats->cash_to_bank_vol, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Physical cash banked</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
      <span class="material-symbols-outlined text-[26px]">account_balance</span>
    </div>
  </div>

  <!-- Bank to Cash / Bank Volume -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Bank → Cash Withdrawals</p>
      <h3 class="text-headline-sm font-bold text-indigo-600 mt-1">₹<?php echo number_format($stats->bank_to_cash_vol, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Withdrawn for petty cash</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
      <span class="material-symbols-outlined text-[26px]">payments</span>
    </div>
  </div>

  <!-- Bank to Bank Inter-Branch -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Bank ↔ Bank Transfers</p>
      <h3 class="text-headline-sm font-bold text-amber-600 mt-1">₹<?php echo number_format($stats->bank_to_bank_vol, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Inter-bank rebalancing</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
      <span class="material-symbols-outlined text-[26px]">sync_alt</span>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
  <form method="get" action="<?php echo site_url('finance/transfers'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
    <!-- Search Query -->
    <div class="lg:col-span-2 relative">
      <span class="material-symbols-outlined absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
      <input type="text" name="search" value="<?php echo html_escape($filters['search']); ?>" placeholder="Search transfer #, ref, narration..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
    </div>

    <!-- From Account -->
    <div>
      <select name="from_account_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Source Accounts</option>
        <?php foreach ($all_accounts as $acc): ?>
          <option value="<?php echo $acc->id; ?>" <?php echo ($filters['from_account_id'] == $acc->id) ? 'selected' : ''; ?>>
            [<?php echo $acc->account_type; ?>] <?php echo html_escape($acc->account_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- To Account -->
    <div>
      <select name="to_account_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Destination Accounts</option>
        <?php foreach ($all_accounts as $acc): ?>
          <option value="<?php echo $acc->id; ?>" <?php echo ($filters['to_account_id'] == $acc->id) ? 'selected' : ''; ?>>
            [<?php echo $acc->account_type; ?>] <?php echo html_escape($acc->account_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Status -->
    <div>
      <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Statuses</option>
        <option value="Completed" <?php echo ($filters['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
        <option value="Reversed" <?php echo ($filters['status'] === 'Reversed') ? 'selected' : ''; ?>>Reversed</option>
      </select>
    </div>

    <!-- Filter Buttons -->
    <div class="flex items-center gap-2">
      <button type="submit" class="w-full px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest transition-colors cursor-pointer text-center">
        Filter
      </button>
      <?php if (!empty($filters['search']) || !empty($filters['from_account_id']) || !empty($filters['to_account_id']) || !empty($filters['status'])): ?>
        <a href="<?php echo site_url('finance/transfers'); ?>" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors shrink-0" title="Reset Filters">
          <span class="material-symbols-outlined text-[20px]">restart_alt</span>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Transfers Data Table -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-8">
  <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[20px]">history</span>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Fund Transfers Ledger</h3>
    </div>
    <span class="text-body-sm text-on-surface-variant"><?php echo count($transfers); ?> transfer records</span>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Transfer #</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Direction / Accounts</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Reference / Narration</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($transfers)): ?>
          <?php foreach ($transfers as $tr): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors <?php echo ($tr->status === 'Reversed') ? 'opacity-70 bg-surface-container-lowest/50' : ''; ?>">
              <!-- Date -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <span class="font-medium"><?php echo date('d-M-Y', strtotime($tr->transfer_date)); ?></span>
              </td>

              <!-- Transfer Number -->
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="font-mono text-[13px] text-primary font-bold">
                  <?php echo html_escape($tr->transfer_number); ?>
                </span>
                <?php if (!empty($tr->attachment)): ?>
                  <a href="<?php echo base_url('uploads/finance/' . $tr->attachment); ?>" target="_blank" class="inline-flex items-center text-on-surface-variant hover:text-primary ml-1" title="View Attachment">
                    <span class="material-symbols-outlined text-[16px]">attachment</span>
                  </a>
                <?php endif; ?>
              </td>

              <!-- Direction: FROM -> TO -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <!-- From Account -->
                  <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container-low border border-outline-variant/60">
                    <span class="material-symbols-outlined text-[16px] <?php echo ($tr->from_account_type === 'Cash') ? 'text-emerald-600' : 'text-blue-600'; ?>">
                      <?php echo ($tr->from_account_type === 'Cash') ? 'payments' : 'account_balance'; ?>
                    </span>
                    <span class="text-body-sm font-medium text-on-surface"><?php echo html_escape($tr->from_account_name); ?></span>
                  </div>

                  <!-- Arrow -->
                  <span class="material-symbols-outlined text-[18px] text-primary font-bold shrink-0">arrow_forward</span>

                  <!-- To Account -->
                  <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container-low border border-outline-variant/60">
                    <span class="material-symbols-outlined text-[16px] <?php echo ($tr->to_account_type === 'Cash') ? 'text-emerald-600' : 'text-blue-600'; ?>">
                      <?php echo ($tr->to_account_type === 'Cash') ? 'payments' : 'account_balance'; ?>
                    </span>
                    <span class="text-body-sm font-medium text-on-surface"><?php echo html_escape($tr->to_account_name); ?></span>
                  </div>
                </div>
              </td>

              <!-- Amount -->
              <td class="px-4 py-3 text-right font-mono font-bold text-[14px] text-on-surface whitespace-nowrap">
                ₹<?php echo number_format($tr->amount, 2); ?>
              </td>

              <!-- Reference & Narration -->
              <td class="px-4 py-3 text-on-surface text-body-sm">
                <?php if (!empty($tr->reference_no)): ?>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono bg-surface-container-high font-medium text-on-surface-variant mr-1">
                    Ref: <?php echo html_escape($tr->reference_no); ?>
                  </span>
                <?php endif; ?>
                <span><?php echo html_escape($tr->description ?: ($tr->notes ?: 'Inter-account fund transfer')); ?></span>
              </td>

              <!-- Status -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if ($tr->status === 'Completed'): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Completed
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>Reversed
                  </span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1">
                  <!-- View Voucher -->
                  <button type="button" onclick="viewTransferVoucher(<?php echo $tr->id; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer" title="View Transfer Voucher">
                    <span class="material-symbols-outlined text-[18px]">receipt</span>
                  </button>

                  <!-- Reverse Transfer (if Completed) -->
                  <?php if ($tr->status === 'Completed'): ?>
                    <button type="button" onclick="openReverseTransferModal(<?php echo $tr->id; ?>, '<?php echo html_escape($tr->transfer_number); ?>', '<?php echo number_format($tr->amount, 2); ?>')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Reverse / Void Transfer">
                      <span class="material-symbols-outlined text-[18px]">undo</span>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="px-4 py-12 text-center text-on-surface-variant">
              <span class="material-symbols-outlined text-[48px] text-outline mb-2">swap_horiz</span>
              <p class="text-body-lg font-medium text-on-surface">No Transfers Found</p>
              <p class="text-body-sm text-on-surface-variant mt-1">Move cash to bank or withdraw operating funds whenever required.</p>
              <button onclick="openTransferModal()" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">sync_alt</span>Transfer Funds
              </button>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: NEW INTER-ACCOUNT FUND TRANSFER -->
<!-- ========================================================================= -->
<div id="transferModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">swap_horiz</span>
        <h3 class="text-title-lg font-bold text-on-surface">Record Fund Transfer</h3>
      </div>
      <button onclick="closeTransferModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Direction Indicator Preview -->
    <div class="mt-4 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/60 flex items-center justify-between gap-3 text-center">
      <div class="flex-1">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-error">Debit Source (-Funds)</span>
        <div id="preview_from" class="font-bold text-on-surface text-body-md mt-0.5 truncate">Select Source</div>
      </div>
      <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
      </div>
      <div class="flex-1">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-secondary">Credit Dest (+Funds)</span>
        <div id="preview_to" class="font-bold text-on-surface text-body-md mt-0.5 truncate">Select Destination</div>
      </div>
    </div>

    <form id="transferForm" method="post" action="<?php echo site_url('finance/transfers'); ?>" enctype="multipart/form-data" class="mt-4 space-y-4" onsubmit="return validateTransferForm();">
      <!-- Date & Amount in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Transfer Date -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Transfer Date <span class="text-error">*</span></label>
          <input type="date" name="transfer_date" id="trf_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        </div>

        <!-- Transfer Amount -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Transfer Amount (₹) <span class="text-error">*</span></label>
          <input type="number" step="0.01" min="0.01" name="amount" id="trf_amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold text-on-surface focus:outline-none focus:border-primary">
        </div>
      </div>

      <!-- Source and Destination Accounts in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- From Account -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">From Account (Source) <span class="text-error">*</span></label>
          <select name="from_account_id" id="trf_from_account" required onchange="updateTransferPreview()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
            <option value="">-- Select Source Account --</option>
            <?php foreach ($cash_bank_accounts as $acc): ?>
              <option value="<?php echo $acc->id; ?>" data-name="<?php echo html_escape($acc->account_name); ?>" data-type="<?php echo $acc->account_type; ?>" data-balance="<?php echo $acc->current_balance; ?>">
                [<?php echo $acc->account_type; ?>] <?php echo html_escape($acc->account_name); ?> (₹<?php echo number_format($acc->current_balance, 2); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- To Account -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">To Account (Destination) <span class="text-error">*</span></label>
          <select name="to_account_id" id="trf_to_account" required onchange="updateTransferPreview()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
            <option value="">-- Select Destination Account --</option>
            <?php foreach ($cash_bank_accounts as $acc): ?>
              <option value="<?php echo $acc->id; ?>" data-name="<?php echo html_escape($acc->account_name); ?>" data-type="<?php echo $acc->account_type; ?>" data-balance="<?php echo $acc->current_balance; ?>">
                [<?php echo $acc->account_type; ?>] <?php echo html_escape($acc->account_name); ?> (₹<?php echo number_format($acc->current_balance, 2); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Warning Message if accounts match -->
      <div id="sameAccountWarning" class="hidden p-3 rounded-lg bg-error-container text-on-error-container text-body-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">warning</span>
        <span>Source Account and Destination Account cannot be the same!</span>
      </div>

      <!-- Reference Number -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Reference Number (Cheque # / UTR # / Slip #)</label>
        <input type="text" name="reference_no" id="trf_ref_no" placeholder="e.g. CHQ-890212, UTR-HDFC982138" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
      </div>

      <!-- Description / Narration -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Description / Narration</label>
        <textarea name="description" id="trf_desc" rows="2" placeholder="e.g. Deposited daily tuition fee collections from petty cash counter into SBI account" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary"></textarea>
      </div>

      <!-- Receipt / Challan Attachment -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Attachment (Deposit Slip / Cheque Copy)</label>
        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm text-on-surface file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest cursor-pointer">
        <p class="text-[11px] text-on-surface-variant mt-0.5">JPG, PNG, PDF up to 5MB.</p>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant mt-6">
        <button type="button" onclick="closeTransferModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="transferSubmitBtn" class="px-5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          Post Fund Transfer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VIEW TRANSFER VOUCHER DETAILS & JOURNAL LINES -->
<!-- ========================================================================= -->
<div id="viewTransferModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">receipt</span>
        <h3 class="text-title-lg font-bold text-on-surface">Transfer Voucher Details</h3>
      </div>
      <button onclick="closeViewTransferModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <div id="viewTransferLoading" class="py-12 text-center text-on-surface-variant">
      <span class="material-symbols-outlined animate-spin text-[32px] text-primary">progress_activity</span>
      <p class="text-body-md mt-2">Loading voucher details...</p>
    </div>

    <div id="viewTransferContent" class="hidden mt-4 space-y-5">
      <!-- Header Voucher Banner -->
      <div class="p-4 rounded-xl bg-surface-container-low flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold" id="vt_number"></span>
          <div class="text-headline-sm font-bold font-mono text-on-surface mt-1" id="vt_amount"></div>
          <span class="text-[12px] text-on-surface-variant" id="vt_date"></span>
        </div>
        <div class="text-right">
          <span id="vt_status_badge"></span>
          <p class="text-[12px] text-on-surface-variant mt-1" id="vt_created_by"></p>
        </div>
      </div>

      <!-- Route Flow Preview -->
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/60 flex items-center justify-between text-center gap-3">
        <div class="flex-1">
          <span class="text-[11px] uppercase tracking-wider font-semibold text-on-surface-variant">Source Account (Credit)</span>
          <h5 class="font-bold text-on-surface text-body-md mt-0.5" id="vt_from_name"></h5>
          <span class="text-[11px] font-mono text-on-surface-variant" id="vt_from_code"></span>
        </div>
        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
          <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </div>
        <div class="flex-1">
          <span class="text-[11px] uppercase tracking-wider font-semibold text-on-surface-variant">Destination Account (Debit)</span>
          <h5 class="font-bold text-on-surface text-body-md mt-0.5" id="vt_to_name"></h5>
          <span class="text-[11px] font-mono text-on-surface-variant" id="vt_to_code"></span>
        </div>
      </div>

      <!-- Reference & Narration -->
      <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60 text-body-sm">
        <span class="text-[11px] text-on-surface-variant">Narration / Notes</span>
        <p class="text-body-sm text-on-surface mt-0.5" id="vt_description"></p>
      </div>

      <!-- Reversal Box if Reversed -->
      <div id="vt_reversal_box" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-body-sm text-rose-800">
        <div class="flex items-center gap-2 font-bold">
          <span class="material-symbols-outlined text-[18px]">undo</span>
          <span>This fund transfer was reversed</span>
        </div>
        <p class="mt-1" id="vt_reversed_reason"></p>
        <p class="text-[11px] text-rose-600 mt-1" id="vt_reversed_meta"></p>
      </div>

      <!-- Accounting Journal Lines (Double-Entry Verification) -->
      <div>
        <h5 class="text-label-md font-bold text-on-surface mb-2">Accounting Double-Entry Posting</h5>
        <div class="border border-outline-variant/60 rounded-xl overflow-hidden">
          <table class="w-full text-body-sm">
            <thead class="bg-surface-container-low/70 border-b border-outline-variant/60">
              <tr>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Account</th>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Type</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Debit (+)</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Credit (-)</th>
              </tr>
            </thead>
            <tbody id="vt_journal_tbody" class="divide-y divide-outline-variant/40">
              <!-- Injected via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REVERSE / VOID TRANSFER -->
<!-- ========================================================================= -->
<div id="reverseTransferModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-xl border border-outline-variant">
    <div class="flex items-center justify-between pb-3 border-b border-outline-variant">
      <div class="flex items-center gap-2 text-error">
        <span class="material-symbols-outlined text-[24px]">undo</span>
        <h3 class="text-title-lg font-bold text-on-surface">Reverse Fund Transfer</h3>
      </div>
      <button onclick="closeReverseTransferModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form method="post" action="<?php echo site_url('finance/reverse_transfer'); ?>" class="mt-4 space-y-4">
      <input type="hidden" name="transfer_id" id="rev_transfer_id" value="">

      <div class="p-3.5 rounded-xl bg-error-container/20 border border-error/20 text-body-sm text-on-surface">
        <p class="font-medium text-error flex items-center gap-1.5 mb-1">
          <span class="material-symbols-outlined text-[18px]">warning</span>Reversal Warning
        </p>
        <p>You are reversing transfer <strong id="rev_trf_num" class="font-mono"></strong> of <strong id="rev_trf_amt"></strong>. A counter-balancing accounting journal entry will be automatically posted to restore both account balances.</p>
      </div>

      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Reversal Reason <span class="text-error">*</span></label>
        <textarea name="reason" id="rev_reason" required rows="3" placeholder="Provide mandatory financial justification for reversing this transfer..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-error"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant">
        <button type="button" onclick="closeReverseTransferModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-error text-on-error text-label-md font-semibold hover:bg-error/90 transition-colors shadow-sm cursor-pointer">
          Confirm Reversal
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript Logic -->
<script>
function openTransferModal() {
  document.getElementById('transferForm').reset();
  document.getElementById('trf_date').value = new Date().toISOString().split('T')[0];
  document.getElementById('preview_from').innerText = 'Select Source';
  document.getElementById('preview_to').innerText = 'Select Destination';
  document.getElementById('sameAccountWarning').classList.add('hidden');
  document.getElementById('transferModal').classList.remove('hidden');
}

function closeTransferModal() {
  document.getElementById('transferModal').classList.add('hidden');
}

function updateTransferPreview() {
  const fromSel = document.getElementById('trf_from_account');
  const toSel   = document.getElementById('trf_to_account');
  const fromOpt = fromSel.options[fromSel.selectedIndex];
  const toOpt   = toSel.options[toSel.selectedIndex];

  if (fromSel.value) {
    document.getElementById('preview_from').innerText = fromOpt.getAttribute('data-name');
  } else {
    document.getElementById('preview_from').innerText = 'Select Source';
  }

  if (toSel.value) {
    document.getElementById('preview_to').innerText = toOpt.getAttribute('data-name');
  } else {
    document.getElementById('preview_to').innerText = 'Select Destination';
  }

  if (fromSel.value && toSel.value && fromSel.value === toSel.value) {
    document.getElementById('sameAccountWarning').classList.remove('hidden');
  } else {
    document.getElementById('sameAccountWarning').classList.add('hidden');
  }
}

function validateTransferForm() {
  const fromId = document.getElementById('trf_from_account').value;
  const toId   = document.getElementById('trf_to_account').value;
  const amount = parseFloat(document.getElementById('trf_amount').value);

  if (fromId === toId) {
    alert('Source Account and Destination Account cannot be the same!');
    return false;
  }
  if (!amount || amount <= 0) {
    alert('Please enter a valid positive transfer amount.');
    return false;
  }

  const btn = document.getElementById('transferSubmitBtn');
  btn.disabled = true;
  btn.innerText = 'Posting Transfer...';
  return true;
}

function viewTransferVoucher(id) {
  document.getElementById('viewTransferModal').classList.remove('hidden');
  document.getElementById('viewTransferLoading').classList.remove('hidden');
  document.getElementById('viewTransferContent').classList.add('hidden');

  fetch('<?php echo site_url("finance/view_transfer_ajax/"); ?>' + id)
    .then(res => res.json())
    .then(data => {
      document.getElementById('viewTransferLoading').classList.add('hidden');
      document.getElementById('viewTransferContent').classList.remove('hidden');

      const tr = data.transfer;
      document.getElementById('vt_number').innerText = tr.transfer_number;
      document.getElementById('vt_amount').innerText = '₹' + parseFloat(tr.amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('vt_date').innerText = 'Date: ' + tr.transfer_date + (tr.reference_no ? ' • Ref: ' + tr.reference_no : '');
      document.getElementById('vt_created_by').innerText = 'Created by ' + (tr.created_by_name || 'System');
      document.getElementById('vt_from_name').innerText = tr.from_account_name;
      document.getElementById('vt_from_code').innerText = tr.from_account_code;
      document.getElementById('vt_to_name').innerText = tr.to_account_name;
      document.getElementById('vt_to_code').innerText = tr.to_account_code;
      document.getElementById('vt_description').innerText = tr.description || tr.notes || 'Inter-account fund contra transfer.';

      document.getElementById('vt_status_badge').innerHTML = (tr.status === 'Completed')
        ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">Completed</span>'
        : '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">Reversed</span>';

      const revBox = document.getElementById('vt_reversal_box');
      if (tr.status === 'Reversed') {
        revBox.classList.remove('hidden');
        document.getElementById('vt_reversed_reason').innerText = 'Reason: ' + (tr.reversed_reason || 'Reversed by admin');
        document.getElementById('vt_reversed_meta').innerText = 'By ' + (tr.reversed_by_name || 'System') + ' on ' + (tr.reversed_at || '');
      } else {
        revBox.classList.add('hidden');
      }

      const tbody = document.getElementById('vt_journal_tbody');
      tbody.innerHTML = '';
      if (data.lines && data.lines.length > 0) {
        data.lines.forEach(l => {
          const deb = parseFloat(l.debit) > 0 ? ('₹' + parseFloat(l.debit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          const cred = parseFloat(l.credit) > 0 ? ('₹' + parseFloat(l.credit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          tbody.innerHTML += `
            <tr class="hover:bg-surface-container-low/40">
              <td class="px-3 py-2 text-on-surface font-medium">${l.account_name} <span class="font-mono text-[11px] text-on-surface-variant">(${l.account_code})</span></td>
              <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ${l.entry_type === 'Debit' ? 'bg-secondary/10 text-secondary' : 'bg-primary/10 text-primary'}">${l.entry_type}</span></td>
              <td class="px-3 py-2 text-right font-mono font-bold text-secondary whitespace-nowrap">${deb}</td>
              <td class="px-3 py-2 text-right font-mono font-bold text-primary whitespace-nowrap">${cred}</td>
            </tr>
          `;
        });
      }
    })
    .catch(err => {
      alert('Error loading transfer voucher details.');
      closeViewTransferModal();
    });
}

function closeViewTransferModal() {
  document.getElementById('viewTransferModal').classList.add('hidden');
}

function openReverseTransferModal(id, number, amount) {
  document.getElementById('rev_transfer_id').value = id;
  document.getElementById('rev_trf_num').innerText = number;
  document.getElementById('rev_trf_amt').innerText = '₹' + amount;
  document.getElementById('rev_reason').value = '';
  document.getElementById('reverseTransferModal').classList.remove('hidden');
}

function closeReverseTransferModal() {
  document.getElementById('reverseTransferModal').classList.add('hidden');
}
</script>
