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
      <span class="material-symbols-outlined text-primary text-[28px]">payments</span>
      <h1 class="font-headline-md text-headline-md font-bold text-on-surface">Cash Accounts</h1>
    </div>
    <p class="text-body-md text-on-surface-variant mt-1">Manage physical cash registers, petty cash chests, and liquid operational funds with live running balances.</p>
  </div>
  <div class="flex items-center gap-2.5 flex-wrap shrink-0">
    <button onclick="openCashAccountModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-all shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[20px]">add</span>Add Cash Account
    </button>
    <a href="<?php echo site_url('finance/bank_accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_balance</span>Bank Accounts
    </a>
    <a href="<?php echo site_url('finance/transfers'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">swap_horiz</span>Transfers
    </a>
  </div>
</div>

<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- Total Cash Balance -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Total Cash in Hand</p>
      <h3 class="text-headline-sm font-bold mt-1 <?php echo ($stats->total_balance >= 0) ? 'text-secondary' : 'text-error'; ?>">
        ₹<?php echo number_format($stats->total_balance, 2); ?>
      </h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Across all active cash accounts</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
      <span class="material-symbols-outlined text-[26px]">account_balance_wallet</span>
    </div>
  </div>

  <!-- Total Accounts Count -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Cash Accounts</p>
      <h3 class="text-headline-sm font-bold text-on-surface mt-1"><?php echo $stats->total_accounts; ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5"><?php echo $stats->active_accounts; ?> active / <?php echo ($stats->total_accounts - $stats->active_accounts); ?> inactive</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
      <span class="material-symbols-outlined text-[26px]">point_of_sale</span>
    </div>
  </div>

  <!-- Today's Inflow -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Today's Inflow (Receipts)</p>
      <h3 class="text-headline-sm font-bold text-secondary mt-1">₹<?php echo number_format($stats->today_inflow, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Cash collected today</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-secondary-container flex items-center justify-center text-secondary">
      <span class="material-symbols-outlined text-[26px]">arrow_downward</span>
    </div>
  </div>

  <!-- Today's Outflow -->
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between">
    <div>
      <p class="text-label-md text-on-surface-variant font-medium">Today's Outflow (Payments)</p>
      <h3 class="text-headline-sm font-bold text-error mt-1">₹<?php echo number_format($stats->today_outflow, 2); ?></h3>
      <p class="text-[12px] text-on-surface-variant mt-0.5">Cash disbursed today</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-error-container flex items-center justify-center text-error">
      <span class="material-symbols-outlined text-[26px]">arrow_upward</span>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
  <form method="get" action="<?php echo site_url('finance/cash_accounts'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <!-- Search Query -->
    <div class="sm:col-span-2 relative">
      <span class="material-symbols-outlined absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
      <input type="text" name="search" value="<?php echo html_escape($filters['search']); ?>" placeholder="Search cash accounts by name, code or description..." class="w-full pl-9 pr-4 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary transition-colors">
    </div>

    <!-- Status Filter -->
    <div class="flex items-center gap-2">
      <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
        <option value="">All Statuses</option>
        <option value="1" <?php echo ($filters['status'] === '1') ? 'selected' : ''; ?>>Active Only</option>
        <option value="0" <?php echo ($filters['status'] === '0') ? 'selected' : ''; ?>>Inactive Only</option>
      </select>
      <button type="submit" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest transition-colors cursor-pointer shrink-0">
        Filter
      </button>
      <?php if (!empty($filters['search']) || $filters['status'] !== ''): ?>
        <a href="<?php echo site_url('finance/cash_accounts'); ?>" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Reset Filters">
          <span class="material-symbols-outlined text-[20px]">restart_alt</span>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Cash Accounts Data Table -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-8">
  <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[20px]">list_alt</span>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Cash Accounts Directory</h3>
    </div>
    <span class="text-body-sm text-on-surface-variant"><?php echo count($accounts); ?> accounts registered</span>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Details</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Code</th>
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
              <!-- Name & Meta -->
              <td class="px-4 py-3 text-on-surface">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                  </div>
                  <div>
                    <div class="font-medium text-on-surface flex items-center gap-1.5">
                      <?php echo html_escape($acc->account_name); ?>
                      <?php if (!empty($acc->is_system)): ?>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary">System Default</span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($acc->description)): ?>
                      <p class="text-[12px] text-on-surface-variant line-clamp-1"><?php echo html_escape($acc->description); ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Code -->
              <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[12px] font-semibold bg-surface-container-high text-on-surface">
                  <?php echo html_escape($acc->account_code); ?>
                </span>
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
                  <button type="button" onclick="viewCashAccountDetails(<?php echo $acc->id; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer" title="View Account Profile">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>

                  <!-- Account Ledger -->
                  <button type="button" onclick="openAccountLedger(<?php echo $acc->id; ?>, '<?php echo addslashes($acc->account_name); ?>')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-secondary hover:bg-surface-container-high transition-colors cursor-pointer" title="View Account Running Ledger">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                  </button>

                  <!-- Edit Account -->
                  <button type="button" onclick='editCashAccount(<?php echo json_encode($acc); ?>)' class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors cursor-pointer" title="Edit Account">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>

                  <!-- Toggle Active Status -->
                  <form method="post" action="<?php echo site_url('finance/toggle_account_status'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to <?php echo ($acc->status == 1) ? 'deactivate' : 'activate'; ?> this cash account?');">
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
            <td colspan="7" class="px-4 py-12 text-center text-on-surface-variant">
              <span class="material-symbols-outlined text-[48px] text-outline mb-2">payments</span>
              <p class="text-body-lg font-medium text-on-surface">No Cash Accounts Found</p>
              <p class="text-body-sm text-on-surface-variant mt-1">Get started by creating your first cash counter or petty cash account.</p>
              <button onclick="openCashAccountModal()" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>Add Cash Account
              </button>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT CASH ACCOUNT -->
<!-- ========================================================================= -->
<div id="cashAccountModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-xl border border-outline-variant max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">payments</span>
        <h3 id="cashModalTitle" class="text-title-lg font-bold text-on-surface">Add Cash Account</h3>
      </div>
      <button onclick="closeCashAccountModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="cashAccountForm" method="post" action="<?php echo site_url('finance/save_cash_account'); ?>" class="mt-5 space-y-4">
      <input type="hidden" name="account_id" id="cash_account_id" value="">

      <!-- Account Name -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Account Name <span class="text-error">*</span></label>
        <input type="text" name="account_name" id="cash_account_name" required placeholder="e.g. Office Petty Cash, Canteen Cash Counter" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
      </div>

      <!-- Account Code & Head in Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Account Code -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Code</label>
          <input type="text" name="account_code" id="cash_account_code" placeholder="Auto-generated if empty" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
          <p class="text-[11px] text-on-surface-variant mt-0.5">e.g. 1011, CASH-002</p>
        </div>

        <!-- Account Head from Chart of Accounts -->
        <div>
          <label class="block text-label-md font-medium text-on-surface mb-1">Account Head</label>
          <select name="parent_account_id" id="cash_parent_account_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
            <option value="">Default (Cash in Hand)</option>
            <?php foreach ($parent_heads as $ph): ?>
              <?php if ($ph->account_type === 'Cash' || $ph->account_code === '1010'): ?>
                <option value="<?php echo $ph->id; ?>"><?php echo html_escape($ph->account_code . ' - ' . $ph->account_name); ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Opening Balance -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Opening Balance (₹)</label>
        <input type="number" step="0.01" min="0" name="opening_balance" id="cash_opening_balance" value="0.00" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-on-surface focus:outline-none focus:border-primary">
        <p id="opening_bal_hint" class="text-[11px] text-on-surface-variant mt-0.5">Initial cash on hand at the start of accounting tracking. Cannot be modified after transactions exist.</p>
      </div>

      <!-- Description / Purpose -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Description / Location</label>
        <textarea name="description" id="cash_description" rows="2" placeholder="e.g. Kept in main administrative safe for petty school office supplies" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary"></textarea>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-label-md font-medium text-on-surface mb-1">Status</label>
        <select name="status" id="cash_status" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:outline-none focus:border-primary">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant mt-6">
        <button type="button" onclick="closeCashAccountModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="cashSubmitBtn" class="px-5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          Save Cash Account
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: VIEW CASH ACCOUNT PROFILE & RECENT ACTIVITY -->
<!-- ========================================================================= -->
<div id="viewAccountModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-outline-variant max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">visibility</span>
        <h3 class="text-title-lg font-bold text-on-surface">Cash Account Profile</h3>
      </div>
      <button onclick="closeViewAccountModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <div id="viewAccountLoading" class="py-12 text-center text-on-surface-variant">
      <span class="material-symbols-outlined animate-spin text-[32px] text-primary">progress_activity</span>
      <p class="text-body-md mt-2">Loading account details...</p>
    </div>

    <div id="viewAccountContent" class="hidden mt-4 space-y-5">
      <!-- Profile Header -->
      <div class="p-4 rounded-xl bg-surface-container-low flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold" id="va_code"></span>
          <h4 class="text-headline-xs font-bold text-on-surface mt-1" id="va_name"></h4>
          <p class="text-[12px] text-on-surface-variant" id="va_head"></p>
        </div>
        <div class="text-right">
          <span class="text-[12px] text-on-surface-variant">Live Balance</span>
          <div class="text-headline-sm font-bold font-mono" id="va_current_balance"></div>
          <span id="va_status_badge"></span>
        </div>
      </div>

      <!-- Metadata Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-body-sm">
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Opening Balance</span>
          <p class="font-bold font-mono text-on-surface text-[14px]" id="va_opening_balance"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Created By</span>
          <p class="font-medium text-on-surface" id="va_created_by"></p>
        </div>
        <div class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
          <span class="text-on-surface-variant text-[11px]">Created Date</span>
          <p class="font-medium text-on-surface" id="va_created_at"></p>
        </div>
      </div>

      <div id="va_desc_wrap" class="p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/60">
        <span class="text-on-surface-variant text-[11px]">Description</span>
        <p class="text-body-sm text-on-surface mt-0.5" id="va_description"></p>
      </div>

      <!-- Recent 10 Transactions -->
      <div>
        <h5 class="text-label-lg font-bold text-on-surface mb-2 flex items-center justify-between">
          <span>Recent Activity (Last 10 Postings)</span>
          <button type="button" id="va_full_ledger_btn" class="text-primary hover:underline text-[12px] cursor-pointer">Open Full Ledger →</button>
        </h5>
        <div class="border border-outline-variant/60 rounded-xl overflow-hidden">
          <table class="w-full text-body-sm">
            <thead class="bg-surface-container-low/60 border-b border-outline-variant/60">
              <tr>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Date</th>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Txn #</th>
                <th class="px-3 py-2 text-left font-semibold text-[11px] text-on-surface-variant">Description</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Debit</th>
                <th class="px-3 py-2 text-right font-semibold text-[11px] text-on-surface-variant">Credit</th>
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
<!-- MODAL: ACCOUNT RUNNING LEDGER STATEMENT -->
<!-- ========================================================================= -->
<div id="accountLedgerModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-surface-container-lowest rounded-2xl max-w-4xl w-full p-6 shadow-xl border border-outline-variant max-h-[92vh] flex flex-col">
    <div class="flex items-center justify-between pb-4 border-b border-outline-variant shrink-0">
      <div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[24px]">receipt_long</span>
          <h3 class="text-title-lg font-bold text-on-surface" id="ledgerModalTitle">Cash Account Running Ledger</h3>
        </div>
        <p class="text-body-sm text-on-surface-variant mt-0.5" id="ledgerModalSubtitle">Complete double-entry transaction trail</p>
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

function openCashAccountModal() {
  document.getElementById('cashModalTitle').innerText = 'Add Cash Account';
  document.getElementById('cash_account_id').value = '';
  document.getElementById('cashAccountForm').reset();
  document.getElementById('cash_opening_balance').disabled = false;
  document.getElementById('opening_bal_hint').innerText = 'Initial cash on hand at the start of accounting tracking. Cannot be modified after transactions exist.';
  document.getElementById('cashAccountModal').classList.remove('hidden');
}

function editCashAccount(acc) {
  document.getElementById('cashModalTitle').innerText = 'Edit Cash Account';
  document.getElementById('cash_account_id').value = acc.id;
  document.getElementById('cash_account_name').value = acc.account_name;
  document.getElementById('cash_account_code').value = acc.account_code;
  document.getElementById('cash_parent_account_id').value = acc.parent_account_id || '';
  document.getElementById('cash_opening_balance').value = parseFloat(acc.opening_balance).toFixed(2);
  document.getElementById('cash_description').value = acc.description || '';
  document.getElementById('cash_status').value = acc.status;
  document.getElementById('cashAccountModal').classList.remove('hidden');
}

function closeCashAccountModal() {
  document.getElementById('cashAccountModal').classList.add('hidden');
}

function viewCashAccountDetails(id) {
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
      document.getElementById('va_head').innerText = 'Head: ' + (a.parent_head_name || 'Assets');
      document.getElementById('va_current_balance').innerText = '₹' + parseFloat(a.current_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('va_current_balance').className = 'text-headline-sm font-bold font-mono ' + (a.current_balance >= 0 ? 'text-secondary' : 'text-error');
      document.getElementById('va_opening_balance').innerText = '₹' + parseFloat(a.opening_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('va_created_by').innerText = a.created_by_name || 'System';
      document.getElementById('va_created_at').innerText = a.created_at || '—';
      document.getElementById('va_description').innerText = a.description || 'No description entered.';

      document.getElementById('va_status_badge').innerHTML = (a.status == 1)
        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Active</span>'
        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">Inactive</span>';

      document.getElementById('va_full_ledger_btn').onclick = function() {
        closeViewAccountModal();
        openAccountLedger(a.id, a.account_name);
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
      alert('Error fetching account details.');
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
  tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-8 text-center text-on-surface-variant">Loading running statement...</td></tr>';

  fetch(`<?php echo site_url("finance/account_ledger_ajax/"); ?>${activeLedgerAccountId}?date_from=${from}&date_to=${to}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('ledger_opening_bal').innerText = '₹' + parseFloat(data.opening_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
      document.getElementById('ledger_closing_bal').innerText = '₹' + parseFloat(data.closing_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});

      tbody.innerHTML = '';
      // Row 0: Opening Balance
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
