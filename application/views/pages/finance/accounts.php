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
        <h2 class="font-headline-md text-headline-md text-on-surface">Chart of Accounts (COA)</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Structured accounting heads categorised across Assets, Liabilities, Equity, Revenue, and Expenses.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openAccountModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add</span>Create New Account
        </button>
      </div>
    </div>

    <!-- Accounts Table grouped or sorted -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-title-md font-semibold text-on-surface">System & Custom Accounts</h3>
        <span class="text-body-sm text-on-surface-variant"><?php echo count($accounts); ?> Total Accounts</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Code</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Group / Classification</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Live Balance</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($accounts)): ?>
              <?php foreach ($accounts as $acc): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary text-sm whitespace-nowrap"><?php echo html_escape($acc->account_code); ?></td>
                  <td class="px-4 py-3 font-medium text-on-surface">
                    <div class="flex items-center gap-2">
                      <span><?php echo html_escape($acc->account_name); ?></span>
                      <?php if ($acc->is_system == 1): ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant" title="Built-in core account">System</span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($acc->bank_name) || !empty($acc->account_number)): ?>
                      <div class="text-[12px] text-on-surface-variant font-mono mt-0.5">
                        <?php echo html_escape($acc->bank_name); ?> - A/C: <?php echo html_escape($acc->account_number); ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                      <?php
                        $gType = $acc->group_type ?? $acc->group_category ?? '';
                        switch ($gType) {
                          case 'Asset': echo 'bg-blue-100 text-blue-800'; break;
                          case 'Liability': echo 'bg-amber-100 text-amber-800'; break;
                          case 'Equity': echo 'bg-purple-100 text-purple-800'; break;
                          case 'Income': echo 'bg-emerald-100 text-emerald-800'; break;
                          case 'Expense': echo 'bg-rose-100 text-rose-800'; break;
                          default: echo 'bg-surface-container-high text-on-surface'; break;
                        }
                      ?>">
                      <?php echo html_escape($acc->group_name . ($gType ? " ({$gType})" : '')); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm whitespace-nowrap"><?php echo html_escape($acc->account_type); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap">
                    ₹<?php echo number_format($acc->current_balance ?? 0, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if (!empty($acc->is_active) || !empty($acc->status)): ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-highest text-on-surface-variant">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <button type="button" onclick='editAccount(<?php echo json_encode($acc); ?>)' class="p-1 rounded-lg text-primary hover:bg-primary/10 cursor-pointer" title="Edit Account">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>
                      <?php if ($acc->is_system != 1): ?>
                        <form method="post" action="<?php echo site_url('finance/accounts'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                          <input type="hidden" name="action" value="delete"/>
                          <input type="hidden" name="account_id" value="<?php echo $acc->id; ?>"/>
                          <button type="submit" class="p-1 rounded-lg text-error hover:bg-error/10 cursor-pointer" title="Delete Account">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No accounts found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ACCOUNT CREATE / EDIT MODAL -->
    <div id="account-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[24px]">account_balance_wallet</span>Create New Account Head
          </h3>
          <button onclick="closeAccountModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/accounts', array('id' => 'account-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="account_id" id="acc-id" value="0"/>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Account Code *</label>
              <input type="text" name="account_code" id="acc-code" required placeholder="e.g. 1040" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Account Group *</label>
              <select name="account_group_id" id="acc-group-id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Select Account Group</option>
                <?php foreach ($groups as $g): ?>
                  <option value="<?php echo $g->id; ?>"><?php echo html_escape(($g->group_name ?? $g->name ?? '') . ' (' . ($g->category ?? $g->type ?? '') . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Account Name *</label>
            <input type="text" name="account_name" id="acc-name" required placeholder="e.g. Science Laboratory Fee Account" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Account Type *</label>
              <select name="account_type" id="acc-type" onchange="toggleBankFields(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Standard">Standard General Ledger</option>
                <option value="Bank">Bank Account</option>
                <option value="Cash">Cash Book</option>
              </select>
            </div>
            <div class="flex items-center pt-6">
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" id="acc-active" value="1" checked class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/20"/>
                <span class="text-body-md text-on-surface font-medium">Is Active Account</span>
              </label>
            </div>
          </div>

          <!-- Bank Details Section (shown when type is Bank) -->
          <div id="bank-fields" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-3 hidden">
            <h4 class="font-semibold text-body-md text-on-surface flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">account_balance</span>Bank Details
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-body-xs text-on-surface-variant mb-1 font-medium">Bank Name</label>
                <input type="text" name="bank_name" id="acc-bank-name" placeholder="e.g. State Bank of India" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary"/>
              </div>
              <div>
                <label class="block text-body-xs text-on-surface-variant mb-1 font-medium">Account Number</label>
                <input type="text" name="account_number" id="acc-number" placeholder="e.g. 38472910394" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm font-mono focus:ring-1 focus:ring-primary"/>
              </div>
              <div>
                <label class="block text-body-xs text-on-surface-variant mb-1 font-medium">Branch Name</label>
                <input type="text" name="branch_name" id="acc-branch" placeholder="e.g. Main Branch" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm focus:ring-1 focus:ring-primary"/>
              </div>
              <div>
                <label class="block text-body-xs text-on-surface-variant mb-1 font-medium">IFSC Code</label>
                <input type="text" name="ifsc_code" id="acc-ifsc" placeholder="e.g. SBIN0001234" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-sm font-mono uppercase focus:ring-1 focus:ring-primary"/>
              </div>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description / Remarks</label>
            <textarea name="description" id="acc-desc" rows="2" placeholder="Optional notes for this account..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeAccountModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
              Save Account
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openAccountModal() {
        document.getElementById('modal-title').innerHTML = '<span class="material-symbols-outlined text-primary text-[24px]">account_balance_wallet</span>Create New Account Head';
        document.getElementById('acc-id').value = '0';
        document.getElementById('acc-code').value = '';
        document.getElementById('acc-code').readOnly = false;
        document.getElementById('acc-name').value = '';
        document.getElementById('acc-group-id').value = '';
        document.getElementById('acc-type').value = 'Standard';
        document.getElementById('acc-bank-name').value = '';
        document.getElementById('acc-number').value = '';
        document.getElementById('acc-branch').value = '';
        document.getElementById('acc-ifsc').value = '';
        document.getElementById('acc-desc').value = '';
        document.getElementById('acc-active').checked = true;
        toggleBankFields('Standard');

        var m = document.getElementById('account-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function editAccount(acc) {
        document.getElementById('modal-title').innerHTML = '<span class="material-symbols-outlined text-primary text-[24px]">edit</span>Edit Account Head';
        document.getElementById('acc-id').value = acc.id;
        document.getElementById('acc-code').value = acc.account_code;
        document.getElementById('acc-code').readOnly = (acc.is_system == 1);
        document.getElementById('acc-name').value = acc.account_name;
        document.getElementById('acc-group-id').value = acc.account_group_id;
        document.getElementById('acc-type').value = acc.account_type || 'Standard';
        document.getElementById('acc-bank-name').value = acc.bank_name || '';
        document.getElementById('acc-number').value = acc.account_number || '';
        document.getElementById('acc-branch').value = acc.branch_name || acc.branch || '';
        document.getElementById('acc-ifsc').value = acc.ifsc_code || '';
        document.getElementById('acc-desc').value = acc.description || '';
        document.getElementById('acc-active').checked = (acc.is_active == 1 || acc.status == 1);
        toggleBankFields(acc.account_type || 'Standard');

        var m = document.getElementById('account-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeAccountModal() {
        var m = document.getElementById('account-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }

      function toggleBankFields(type) {
        var b = document.getElementById('bank-fields');
        if (type === 'Bank') {
          b.classList.remove('hidden');
        } else {
          b.classList.add('hidden');
        }
      }
    </script>
