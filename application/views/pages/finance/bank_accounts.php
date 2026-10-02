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
      <span class="material-symbols-outlined text-primary text-[28px]">account_balance</span>
      <h1 class="font-headline-md text-headline-md font-bold text-on-surface">Bank Accounts</h1>
    </div>
    <p class="text-body-md text-on-surface-variant mt-1">Manage institutional school bank accounts, branch credentials, IFSC codes, and live reconciliation balances.</p>
  </div>
  <div class="flex items-center gap-2.5 flex-wrap shrink-0">
    <button onclick="openBankAccountModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-all shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[20px]">add</span>Add Bank Account
    </button>
    <a href="<?php echo site_url('finance/cash_accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">payments</span>Cash Accounts
    </a>
    <a href="<?php echo site_url('finance/transfers'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">swap_horiz</span>Transfers
    </a>
  </div>
</div>

<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- Total Bank Balance -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Total Bank Balance</p>
      <h3 class="text-headline-sm font-bold mt-1 <?php echo ($stats->total_balance >= 0) ? 'text-secondary' : 'text-error'; ?>">
        ₹<?php echo number_format($stats->total_balance, 2); ?>
      </h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Liquid funds in active bank accounts</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
      <span class="material-symbols-outlined text-[26px]">account_balance</span>
    </div>
  </div>

  <!-- Total Accounts Count -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Bank Accounts</p>
      <h3 class="text-headline-sm font-bold text-on-surface mt-1"><?php echo $stats->total_accounts; ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5"><?php echo $stats->active_accounts; ?> operational / <?php echo ($stats->total_accounts - $stats->active_accounts); ?> inactive</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
      <span class="material-symbols-outlined text-[26px]">credit_card</span>
    </div>
  </div>

  <!-- Today's Inflow -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Today's Inflow (Credits)</p>
      <h3 class="text-headline-sm font-bold text-secondary mt-1">₹<?php echo number_format($stats->today_inflow, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Bank deposits & transfers in</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-secondary-container flex items-center justify-center text-secondary">
      <span class="material-symbols-outlined text-[26px]">arrow_downward</span>
    </div>
  </div>

  <!-- Today's Outflow -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Today's Outflow (Debits)</p>
      <h3 class="text-headline-sm font-bold text-error mt-1">₹<?php echo number_format($stats->today_outflow, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Vendor payments, payouts & transfers</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-error-container flex items-center justify-center text-error">
      <span class="material-symbols-outlined text-[26px]">arrow_upward</span>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
  <form method="get" action="<?php echo site_url('finance/bank_accounts'); ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
    <!-- Search Query -->
    <div class="relative sm:col-span-1">
      <span class="material-symbols-outlined absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
      <input type="text" name="search" value="<?php echo html_escape($filters['search']); ?>" placeholder="Search bank, account, branch..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
    </div>

    <!-- Bank Name Filter -->
    <div>
      <input type="text" name="bank_name" value="<?php echo html_escape($filters['bank_name']); ?>" placeholder="Filter by Bank Name..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
    </div>

    <!-- Account Type Filter -->
    <div>
      <select name="bank_account_type" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Account Types</option>
        <option value="Savings" <?php echo ($filters['bank_account_type'] === 'Savings') ? 'selected' : ''; ?>>Savings</option>
        <option value="Current" <?php echo ($filters['bank_account_type'] === 'Current') ? 'selected' : ''; ?>>Current</option>
        <option value="Other" <?php echo ($filters['bank_account_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
      </select>
    </div>

    <!-- Status & Submit -->
    <div class="flex items-center gap-2">
      <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Statuses</option>
        <option value="1" <?php echo ($filters['status'] === '1') ? 'selected' : ''; ?>>Active</option>
        <option value="0" <?php echo ($filters['status'] === '0') ? 'selected' : ''; ?>>Inactive</option>
      </select>
      <button type="submit" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest transition-colors cursor-pointer shrink-0">
        Filter
      </button>
      <?php if (!empty($filters['search']) || !empty($filters['bank_name']) || !empty($filters['bank_account_type']) || $filters['status'] !== ''): ?>
        <a href="<?php echo site_url('finance/bank_accounts'); ?>" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Reset Filters">
          <span class="material-symbols-outlined text-[20px]">restart_alt</span>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Bank Accounts Data Table -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-8">
  <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[20px]">account_balance</span>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Bank Accounts Directory</h3>
    </div>
    <span class="text-body-sm text-on-surface-variant"><?php echo count($accounts); ?> bank accounts</span>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Bank & Account Name</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Masked Account Number</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Type</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Branch & IFSC</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Head</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Opening Balance</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Current Balance</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($accounts)): ?>
          <?php foreach ($accounts as $acc): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors">
              <!-- Bank & Account Name -->
              <td class="px-4 py-3 text-on-surface">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>
                  </div>
                  <div>
                    <div class="font-medium text-on-surface flex items-center gap-1.5">
                      <?php echo html_escape($acc->account_name); ?>
                      <?php if (!empty($acc->is_system)): ?>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary">System Default</span>
                      <?php endif; ?>
                    </div>
                    <span class="block text-[12px] text-on-surface-variant font-semibold"><?php echo html_escape($acc->bank_name ?: 'Bank Head'); ?></span>
                  </div>
                </div>
              </td>

              <!-- Masked Account Number -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <span class="font-mono text-[13px] text-on-surface font-medium">
                  <?php echo html_escape($acc->masked_account_number); ?>
                </span>
                <span class="block text-[11px] text-on-surface-variant font-mono">Code: <?php echo html_escape($acc->account_code); ?></span>
              </td>

              <!-- Account Type -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <?php $btype = $acc->bank_account_type ?: 'Savings'; ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium <?php echo ($btype === 'Current') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-700 border border-amber-200'; ?>">
                  <?php echo html_escape($btype); ?>
                </span>
              </td>

              <!-- Branch & IFSC -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap text-body-sm">
                <div><?php echo html_escape($acc->branch ?: '—'); ?></div>
                <?php if (!empty($acc->ifsc_code)): ?>
                  <span class="font-mono text-[11px] text-on-surface-variant"><?php echo html_escape($acc->ifsc_code); ?></span>
                <?php endif; ?>
              </td>

              <!-- Account Head -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <span class="text-body-sm text-on-surface-variant">
                  <?php echo html_escape($acc->parent_head_name ?: ($acc->group_name ?: 'Assets')); ?>
                </span>
                <?php if (!empty($acc->parent_head_code)): ?>
                  <span class="block text-[11px] text-on-surface-variant/70 font-mono"><?php echo html_escape($acc->parent_head_code); ?></span>
                <?php endif; ?>
              </td>

              <!-- Opening Balance -->
              <td class="px-4 py-3 text-right font-mono text-on-surface-variant whitespace-nowrap">
                ₹<?php echo number_format($acc->opening_balance, 2); ?>
              </td>

              <!-- Current Balance -->
              <td class="px-4 py-3 text-right font-mono whitespace-nowrap">
                <span class="font-bold text-[14px] <?php echo ($acc->current_balance >= 0) ? 'text-secondary' : 'text-error'; ?>">
                  ₹<?php echo number_format($acc->current_balance, 2); ?>
                </span>
              </td>

              <!-- Status -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if ($acc->status == 1): ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Active
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactive
                  </span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1">
                  <!-- View Details -->
                  <button type="button" onclick="viewBankAccountDetails(<?php echo $acc->id; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer" title="View Account Profile & Credentials">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>

                  <!-- Account Ledger -->
                  <button type="button" onclick="openAccountLedger(<?php echo $acc->id; ?>, '<?php echo addslashes($acc->bank_name . ' - ' . $acc->account_name); ?>')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-secondary hover:bg-surface-container-high transition-colors cursor-pointer" title="View Bank Book Statement">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                  </button>

                  <!-- Edit Account -->
                  <button type="button" onclick='editBankAccount(<?php echo json_encode($acc); ?>)' class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer" title="Edit Bank Account">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>

                  <!-- Toggle Active Status -->
                  <form method="post" action="<?php echo site_url('finance/toggle_account_status'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to <?php echo ($acc->status == 1) ? 'deactivate' : 'activate'; ?> this bank account?');">
                    <input type="hidden" name="account_id" value="<?php echo $acc->id; ?>">
                    <button type="submit" class="p-1.5 rounded-lg text-on-surface-variant hover:text-error hover:bg-surface-container-high transition-colors cursor-pointer" title="<?php echo ($acc->status == 1) ? 'Deactivate' : 'Activate'; ?>">
                      <span class="material-symbols-outlined text-[18px]"><?php echo ($acc->status == 1) ? 'block' : 'check_circle'; ?></span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
              <span class="material-symbols-outlined text-[48px] text-outline mb-2">account_balance</span>
              <p class="text-body-lg font-medium text-on-surface">No Bank Accounts Found</p>
              <p class="text-body-sm text-on-surface-variant mt-1">Add your school's operating or fee collection bank accounts.</p>
              <button onclick="openBankAccountModal()" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>Add Bank Account
              </button>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT BANK ACCOUNT -->
<!-- ========================================================================= -->
<div id="bankAccountModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">account_balance</span>
        <h3 id="bankModalTitle" class="text-title-lg font-bold text-on-surface">Add Bank Account</h3>
      </div>
      <button onclick="closeBankAccountModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="bankAccountForm" method="post" action="<?php echo site_url('finance/save_bank_account'); ?>" class="mt-5 space-y-4">
      <input type="hidden" name="account_id" id="bank_account_id" value="">

      <!-- Bank Name & Display Name in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Bank Name -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Bank Name <span class="text-error">*</span></label>
          <input type="text" name="bank_name" id="bank_name_input" required placeholder="e.g. State Bank of India, HDFC Bank" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        </div>

        <!-- Account Display Name -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Display Name <span class="text-error">*</span></label>
          <input type="text" name="account_name" id="bank_account_name" required placeholder="e.g. Main Fee Collection A/C" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        </div>
      </div>

      <!-- Account Number & Account Type in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Account Number -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Number <span class="text-error">*</span></label>
          <input type="text" name="account_number" id="bank_account_number" required placeholder="e.g. 987654321012" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
        </div>

        <!-- Account Type -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Type</label>
          <select name="bank_account_type" id="bank_account_type" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
            <option value="Savings">Savings Account</option>
            <option value="Current">Current Account</option>
            <option value="Other">Other Institutional A/C</option>
          </select>
        </div>
      </div>

      <!-- Branch & IFSC in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Branch -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Branch Name</label>
          <input type="text" name="branch" id="bank_branch" placeholder="e.g. Central City Branch" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        </div>

        <!-- IFSC Code -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">IFSC Code</label>
          <input type="text" name="ifsc_code" id="bank_ifsc_code" placeholder="e.g. SBIN0001234" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono uppercase text-on-surface focus:outline-none focus:border-primary">
        </div>
      </div>

      <!-- Account Head & Code in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Account Head from Chart of Accounts -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Head</label>
          <select name="parent_account_id" id="bank_parent_account_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
            <option value="">Default (School Bank Account)</option>
            <?php foreach ($parent_heads as $ph): ?>
              <?php if ($ph->account_type === 'Bank' || $ph->account_code === '1020'): ?>
                <option value="<?php echo $ph->id; ?>"><?php echo html_escape($ph->account_code . ' - ' . $ph->account_name); ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Account Code -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Code</label>
          <input type="text" name="account_code" id="bank_account_code" placeholder="Auto-generated if empty" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
          <p class="text-[11px] text-on-surface-variant mt-0.5">e.g. 1021, BANK-001</p>
        </div>
      </div>

      <!-- Opening Balance -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Opening Balance (₹)</label>
        <input type="number" step="0.01" min="0" name="opening_balance" id="bank_opening_balance" value="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
        <p class="text-[11px] text-on-surface-variant mt-0.5">Initial bank ledger balance as of account inception.</p>
      </div>

      <!-- Description / Purpose -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Description / Notes</label>
        <textarea name="description" id="bank_description" rows="2" placeholder="e.g. Primary institutional account for RTGS/NEFT online fee collections and vendor settlements" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary"></textarea>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Status</label>
        <select name="status" id="bank_status" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant mt-6">
        <button type="button" onclick="closeBankAccountModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          Save Bank Account
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VIEW BANK ACCOUNT CREDENTIALS & PROFILE -->
<!-- ========================================================================= -->
<div id="viewAccountModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">account_balance</span>
        <h3 class="text-title-lg font-bold text-on-surface">Bank Account Profile</h3>
      </div>
      <button onclick="closeViewAccountModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <div id="viewAccountLoading" class="py-12 text-center text-on-surface-variant">
      <span class="material-symbols-outlined animate-spin text-[32px] text-primary">progress_activity</span>
      <p class="text-body-md mt-2">Loading bank account details...</p>
    </div>

    <div id="viewAccountContent" class="hidden mt-4 space-y-5">
      <!-- Profile Header -->
      <div class="p-4 rounded-xl bg-surface-container-low flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold" id="va_code"></span>
          <h4 class="text-headline-xs font-bold text-on-surface mt-1" id="va_name"></h4>
          <p class="text-[13px] text-primary font-semibold" id="va_bank"></p>
        </div>
        <div class="text-right">
          <span class="text-[12px] text-on-surface-variant">Live Balance</span>
          <div class="text-headline-sm font-bold font-mono" id="va_current_balance"></div>
          <span id="va_status_badge"></span>
        </div>
      </div>

      <!-- Full Banking Credentials Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-body-sm">
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Full Account Number</span>
          <p class="font-bold font-mono text-on-surface text-[14px]" id="va_full_acc_no"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Account Type</span>
          <p class="font-medium text-on-surface" id="va_type"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">IFSC Code</span>
          <p class="font-mono font-bold text-on-surface" id="va_ifsc"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Branch</span>
          <p class="font-medium text-on-surface" id="va_branch"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Opening Balance</span>
          <p class="font-bold font-mono text-on-surface" id="va_opening_balance"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Account Head</span>
          <p class="font-medium text-on-surface" id="va_head"></p>
        </div>
      </div>

      <div id="va_desc_wrap" class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
        <span class="text-on-surface-variant text-[11px]">Description / Purpose</span>
        <p class="text-body-sm text-on-surface mt-0.5" id="va_description"></p>
      </div>

      <!-- Recent 10 Transactions -->
      <div>
        <h5 class="text-label-lg font-bold text-on-surface mb-2 flex items-center justify-between">
          <span>Recent Activity (Last 10 Postings)</span>
          <button type="button" id="va_full_ledger_btn" class="text-primary hover:underline text-[12px] cursor-pointer">Open Full Bank Book →</button>
        </h5>
        <div class="border border-outline-variant/60 rounded-xl overflow-hidden">
          <table class="w-full text-body-sm">
            <thead class="bg-surface-container-low/60 border-b border-outline-variant/60">
              <tr>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Date</th>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Txn #</th>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Description</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Debit (+)</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Credit (-)</th>
              </tr>
            </thead>
            <tbody id="va_recent_tbody" class="divide-y divide-outline-variant/40">
              <!-- Injected via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: BANK ACCOUNT RUNNING LEDGER STATEMENT (BANK BOOK) -->
<!-- ========================================================================= -->
<div id="accountLedgerModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-4xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] flex flex-col">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant shrink-0">
      <div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[24px]">receipt_long</span>
          <h3 class="text-title-lg font-bold text-on-surface" id="ledgerModalTitle">Bank Account Running Ledger</h3>
        </div>
        <p class="text-body-sm text-on-surface-variant mt-0.5" id="ledgerModalSubtitle">Complete running statement & audit trail</p>
      </div>
      <button onclick="closeAccountLedgerModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Date Range Filter inside Modal -->
    <div class="flex items-center gap-3 py-3 shrink-0">
      <input type="date" id="ledger_date_from" value="<?php echo date('Y-m-01'); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant text-body-sm bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary">
      <span class="text-on-surface-variant text-body-sm">to</span>
      <input type="date" id="ledger_date_to" value="<?php echo date('Y-m-d'); ?>" class="px-3 py-1.5 rounded-lg border border-outline-variant text-body-sm bg-surface-container-lowest text-on-surface focus:outline-none focus:border-primary">
      <button type="button" onclick="refreshAccountLedger()" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-label-sm font-medium hover:bg-surface-container-highest transition-colors cursor-pointer">
        Apply Range
      </button>
    </div>

    <!-- Opening / Closing Summary Banner -->
    <div class="grid grid-cols-2 gap-3 py-2 shrink-0">
      <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-sm text-on-surface-variant font-medium">Opening Balance</span>
        <span class="font-mono font-bold text-[15px] text-on-surface" id="ledger_opening_bal">₹0.00</span>
      </div>
      <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-sm text-on-surface-variant font-medium">Closing Running Balance</span>
        <span class="font-mono font-bold text-[15px] text-secondary" id="ledger_closing_bal">₹0.00</span>
      </div>
    </div>

    <!-- Scrollable Statement Table -->
    <div class="overflow-y-auto flex-1 border border-outline-variant/60 rounded-xl my-2">
      <table class="w-full text-body-sm">
        <thead class="bg-surface-container-low/70 sticky top-0 border-b border-outline-variant/60">
          <tr>
            <th class="px-3 py-2.5 text-left font-semibold text-[11px] text-on-surface-variant">Date</th>
            <th class="px-3 py-2.5 text-left font-semibold text-[11px] text-on-surface-variant">Voucher / Txn #</th>
            <th class="px-3 py-2.5 text-left font-semibold text-[11px] text-on-surface-variant">Type</th>
            <th class="px-3 py-2.5 text-left font-semibold text-[11px] text-on-surface-variant">Description</th>
            <th class="px-3 py-2.5 text-right font-semibold text-[11px] text-on-surface-variant">Debit (+)</th>
            <th class="px-3 py-2.5 text-right font-semibold text-[11px] text-on-surface-variant">Credit (-)</th>
            <th class="px-3 py-2.5 text-right font-semibold text-[11px] text-on-surface-variant">Running Balance</th>
          </tr>
        </thead>
        <tbody id="ledger_tbody" class="divide-y divide-outline-variant/40">
          <!-- Injected via AJAX -->
        </tbody>
      </table>
    </div>

    <div class="flex justify-end pt-3 border-t border-outline-variant shrink-0">
      <button type="button" onclick="closeAccountLedgerModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest transition-colors cursor-pointer">
        Close
      </button>
    </div>
  </div>
</div>

<!-- JavaScript Logic -->
<script>
let activeLedgerAccountId = null;

function openBankAccountModal() {
  document.getElementById('bankModalTitle').innerText = 'Add Bank Account';
  document.getElementById('bank_account_id').value = '';
  document.getElementById('bankAccountForm').reset();
  document.getElementById('bank_opening_balance').disabled = false;
  document.getElementById('bankAccountModal').classList.remove('hidden');
}

function editBankAccount(acc) {
  document.getElementById('bankModalTitle').innerText = 'Edit Bank Account';
  document.getElementById('bank_account_id').value = acc.id;
  document.getElementById('bank_name_input').value = acc.bank_name || '';
  document.getElementById('bank_account_name').value = acc.account_name || '';
  document.getElementById('bank_account_number').value = acc.account_number || '';
  document.getElementById('bank_account_type').value = acc.bank_account_type || 'Savings';
  document.getElementById('bank_branch').value = acc.branch || '';
  document.getElementById('bank_ifsc_code').value = acc.ifsc_code || '';
  document.getElementById('bank_parent_account_id').value = acc.parent_account_id || '';
  document.getElementById('bank_account_code').value = acc.account_code || '';
  document.getElementById('bank_opening_balance').value = parseFloat(acc.opening_balance).toFixed(2);
  document.getElementById('bank_description').value = acc.description || '';
  document.getElementById('bank_status').value = acc.status;
  document.getElementById('bankAccountModal').classList.remove('hidden');
}

function closeBankAccountModal() {
  document.getElementById('bankAccountModal').classList.add('hidden');
}

function viewBankAccountDetails(id) {
  document.getElementById('viewAccountModal').classList.remove('hidden');
  document.getElementById('viewAccountLoading').classList.remove('hidden');
  document.getElementById('viewAccountContent').classList.add('hidden');

  fetch('<?php echo site_url("finance/view_account_ajax/"); ?>' + id)
    .then(res => res.json())
    .then(data => {
      document.getElementById('viewAccountLoading').classList.add('hidden');
      document.getElementById('viewAccountContent').classList.remove('hidden');

      const a = data.account;
      document.getElementById('va_code').innerText = a.account_code;
      document.getElementById('va_name').innerText = a.account_name;
      document.getElementById('va_bank').innerText = a.bank_name || 'Bank';
      document.getElementById('va_full_acc_no').innerText = a.account_number || '—';
      document.getElementById('va_type').innerText = (a.bank_account_type || 'Savings') + ' Account';
      document.getElementById('va_ifsc').innerText = a.ifsc_code || '—';
      document.getElementById('va_branch').innerText = a.branch || '—';
      document.getElementById('va_head').innerText = a.parent_head_name || 'Assets';

      document.getElementById('va_current_balance').innerText = '₹' + parseFloat(a.current_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('va_current_balance').className = 'text-headline-sm font-bold font-mono ' + (a.current_balance >= 0 ? 'text-secondary' : 'text-error');
      document.getElementById('va_opening_balance').innerText = '₹' + parseFloat(a.opening_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('va_description').innerText = a.description || 'No description entered.';

      document.getElementById('va_status_badge').innerHTML = (a.status == 1)
        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Active</span>'
        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">Inactive</span>';

      document.getElementById('va_full_ledger_btn').onclick = function() {
        closeViewAccountModal();
        openAccountLedger(a.id, (a.bank_name ? a.bank_name + ' - ' : '') + a.account_name);
      };

      const tbody = document.getElementById('va_recent_tbody');
      tbody.innerHTML = '';
      if (data.recent_lines && data.recent_lines.length > 0) {
        data.recent_lines.forEach(l => {
          const deb = parseFloat(l.debit) > 0 ? ('₹' + parseFloat(l.debit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          const cred = parseFloat(l.credit) > 0 ? ('₹' + parseFloat(l.credit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          tbody.innerHTML += `
            <tr class="hover:bg-surface-container-low/40">
              <td class="px-3 py-2 whitespace-nowrap text-on-surface">${l.transaction_date}</td>
              <td class="px-3 py-2 whitespace-nowrap font-mono text-primary text-[12px] font-medium">${l.transaction_number}</td>
              <td class="px-3 py-2 text-on-surface line-clamp-1">${l.tx_desc || l.description || '—'}</td>
              <td class="px-3 py-2 text-right font-mono text-secondary whitespace-nowrap">${deb}</td>
              <td class="px-3 py-2 text-right font-mono text-error whitespace-nowrap">${cred}</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-6 text-center text-on-surface-variant">No transactions posted yet.</td></tr>';
      }
    })
    .catch(err => {
      alert('Error fetching bank account details.');
      closeViewAccountModal();
    });
}

function closeViewAccountModal() {
  document.getElementById('viewAccountModal').classList.add('hidden');
}

function openAccountLedger(accountId, accountName) {
  activeLedgerAccountId = accountId;
  document.getElementById('ledgerModalTitle').innerText = 'Ledger: ' + accountName;
  document.getElementById('accountLedgerModal').classList.remove('hidden');
  refreshAccountLedger();
}

function refreshAccountLedger() {
  if (!activeLedgerAccountId) return;
  const from = document.getElementById('ledger_date_from').value;
  const to   = document.getElementById('ledger_date_to').value;

  const tbody = document.getElementById('ledger_tbody');
  tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-8 text-center text-on-surface-variant">Loading bank book statement...</td></tr>';

  fetch(`<?php echo site_url("finance/account_ledger_ajax/"); ?>${activeLedgerAccountId}?date_from=${from}&date_to=${to}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('ledger_opening_bal').innerText = '₹' + parseFloat(data.opening_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('ledger_closing_bal').innerText = '₹' + parseFloat(data.closing_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});

      tbody.innerHTML = '';
      tbody.innerHTML += `
        <tr class="bg-surface-container-low/40 font-medium">
          <td class="px-3 py-2 text-on-surface whitespace-nowrap">${data.date_from}</td>
          <td class="px-3 py-2 font-mono text-[11px] text-on-surface-variant">—</td>
          <td class="px-3 py-2 text-[11px] uppercase tracking-wider text-on-surface-variant">Opening</td>
          <td class="px-3 py-2 text-on-surface font-semibold">Opening Balance Brought Forward</td>
          <td class="px-3 py-2 text-right font-mono">—</td>
          <td class="px-3 py-2 text-right font-mono">—</td>
          <td class="px-3 py-2 text-right font-mono font-bold text-on-surface">₹${parseFloat(data.opening_balance).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
        </tr>
      `;

      if (data.lines && data.lines.length > 0) {
        data.lines.forEach(l => {
          const deb = parseFloat(l.debit) > 0 ? ('₹' + parseFloat(l.debit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          const cred = parseFloat(l.credit) > 0 ? ('₹' + parseFloat(l.credit).toLocaleString('en-IN', {minimumFractionDigits: 2})) : '—';
          tbody.innerHTML += `
            <tr class="hover:bg-surface-container-low/30">
              <td class="px-3 py-2 whitespace-nowrap text-on-surface">${l.transaction_date}</td>
              <td class="px-3 py-2 font-mono text-primary text-[12px] font-semibold whitespace-nowrap">${l.transaction_number}</td>
              <td class="px-3 py-2 whitespace-nowrap"><span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high">${l.transaction_type}</span></td>
              <td class="px-3 py-2 text-on-surface">${l.tx_desc || l.description || '—'}</td>
              <td class="px-3 py-2 text-right font-mono text-secondary font-semibold whitespace-nowrap">${deb}</td>
              <td class="px-3 py-2 text-right font-mono text-error font-semibold whitespace-nowrap">${cred}</td>
              <td class="px-3 py-2 text-right font-mono font-bold text-on-surface whitespace-nowrap">₹${parseFloat(l.running_balance).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML += `
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-on-surface-variant">No transaction postings found in this date period.</td>
          </tr>
        `;
      }
    })
    .catch(err => {
      tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-6 text-center text-error">Failed to load statement.</td></tr>';
    });
}

function closeAccountLedgerModal() {
  document.getElementById('accountLedgerModal').classList.add('hidden');
}
</script>
