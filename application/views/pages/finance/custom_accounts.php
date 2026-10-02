<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Flash Messages -->
<div id="flash-message-container">
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
</div>

<!-- Header & Quick Navigation -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="font-headline-md text-headline-md text-on-surface">Custom Accounts</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">Create and manage school-specific financial accounts.</p>
  </div>
  <div class="flex items-center gap-2 flex-wrap shrink-0">
    <a href="<?php echo site_url('fee-finance/account-groups'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">folder</span>Account Groups
    </a>
    <a href="<?php echo site_url('fee-finance/account-heads'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_tree</span>Account Heads
    </a>
    <?php if (!empty($can_create)): ?>
      <button type="button" onclick="openAddCustomModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">add</span>Add Custom Account
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Custom Accounts Table Card -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-outline-variant/50 gap-3">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Operational Accounts</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">School bank accounts, physical cash counters, petty cash, and custom ledgers.</p>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-body-sm text-on-surface-variant font-medium"><?php echo count($custom_accounts ?? []); ?> Accounts Configured</span>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto p-4">
    <table id="custom-accounts-table" class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider w-10">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Name</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Head</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Number</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Type</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Opening Balance</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Current Balance</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Created At</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($custom_accounts)): ?>
          <?php $idx = 1; foreach ($custom_accounts as $ca): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors" id="custom-row-<?php echo $ca->id; ?>">
              <td class="px-4 py-3.5 font-mono text-on-surface-variant text-xs"><?php echo $idx++; ?></td>
              <td class="px-4 py-3.5 font-medium text-on-surface">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-[20px]">
                    <?php echo ($ca->account_type === 'Bank') ? 'account_balance' : (($ca->account_type === 'Cash') ? 'payments' : 'account_balance_wallet'); ?>
                  </span>
                  <span class="font-bold text-on-surface"><?php echo html_escape($ca->account_name); ?></span>
                </div>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span class="text-xs font-semibold text-on-surface block"><?php echo html_escape($ca->account_head_name); ?></span>
                <span class="text-[10px] font-mono text-on-surface-variant">Code: <?php echo html_escape($ca->account_head_code); ?></span>
              </td>
              <td class="px-4 py-3.5 font-mono text-on-surface text-sm whitespace-nowrap">
                <?php echo html_escape($ca->masked_account_number ?? '—'); ?>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <?php
                  $t = $ca->account_type;
                  $tCls = 'bg-surface-container-high text-on-surface border-theme-border';
                  if ($t === 'Cash') $tCls = 'bg-teal-50 text-teal-700 border-teal-200';
                  elseif ($t === 'Bank') $tCls = 'bg-blue-50 text-blue-700 border-blue-200';
                  elseif ($t === 'Receivable') $tCls = 'bg-amber-50 text-amber-700 border-amber-200';
                  elseif ($t === 'Payable') $tCls = 'bg-rose-50 text-rose-700 border-rose-200';
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $tCls; ?>">
                  <?php echo html_escape($t); ?>
                </span>
              </td>
              <td class="px-4 py-3.5 text-right font-mono text-on-surface whitespace-nowrap text-sm">
                ₹<?php echo number_format($ca->opening_balance ?? 0, 2); ?>
                <span class="text-[10px] text-on-surface-variant font-medium">(<?php echo html_escape($ca->opening_balance_type ?? 'Debit'); ?>)</span>
              </td>
              <td class="px-4 py-3.5 text-right font-mono font-bold text-on-surface whitespace-nowrap text-sm">
                <?php $bal = (float)($ca->current_balance ?? 0); ?>
                <span class="<?php echo ($bal < 0) ? 'text-rose-700' : 'text-on-surface'; ?>">
                  ₹<?php echo number_format($bal, 2); ?>
                </span>
              </td>
              <td class="px-4 py-3.5 text-center whitespace-nowrap">
                <?php if ((int)($ca->status ?? 1) === 1): ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Active
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                    Inactive
                  </span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3.5 text-on-surface-variant text-xs whitespace-nowrap">
                <?php echo !empty($ca->created_at) ? date('d-M-Y', strtotime($ca->created_at)) : '—'; ?>
              </td>
              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <?php if (!empty($can_edit)): ?>
                    <button type="button" onclick='openEditCustomModal(<?php echo json_encode($ca); ?>)' 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer" 
                            title="Edit Custom Account">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button type="button" onclick="toggleCustomStatus(<?php echo $ca->id; ?>, '<?php echo html_escape(addslashes($ca->account_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-amber-50 hover:text-amber-700 transition-colors cursor-pointer" 
                            title="Toggle Status (Active/Inactive)">
                      <span class="material-symbols-outlined text-[18px]"><?php echo ((int)$ca->status === 1) ? 'toggle_on' : 'toggle_off'; ?></span>
                    </button>
                  <?php endif; ?>

                  <?php if (!empty($can_delete)): ?>
                    <button type="button" onclick="deleteCustomAccount(<?php echo $ca->id; ?>, '<?php echo html_escape(addslashes($ca->account_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors cursor-pointer" 
                            title="Delete Custom Account">
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Custom Account Modal -->
<div id="custom-modal" class="fixed inset-0 bg-dark-text/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs">
  <div class="w-full max-w-lg bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl overflow-hidden animate-in fade-in duration-200">
    <div class="px-6 py-4 border-b border-outline-variant/50 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
        </span>
        <h3 id="modal-custom-title" class="font-headline-md text-title-md font-semibold text-on-surface">Add Custom Account</h3>
      </div>
      <button type="button" onclick="closeCustomModal()" class="w-8 h-8 rounded-lg text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center transition cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="custom-form" onsubmit="handleCustomSubmit(event)" class="p-6 space-y-4">
      <input type="hidden" id="modal-custom-id" name="id" value="" />

      <!-- Account Name * -->
      <div>
        <label for="modal-custom-name" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Name <span class="text-error">*</span></label>
        <input type="text" id="modal-custom-name" name="account_name" required placeholder="e.g. SBI Main School Account, Primary Cash Drawer"
               class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
      </div>

      <!-- Account Head * & Account Type * -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="modal-custom-head" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Head <span class="text-error">*</span></label>
          <select id="modal-custom-head" name="account_head_id" required
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
            <option value="">— Select Account Head —</option>
            <?php if (!empty($account_heads)): ?>
              <?php foreach ($account_heads as $ah): ?>
                <option value="<?php echo $ah->id; ?>" data-type="<?php echo html_escape($ah->account_type); ?>" data-category="<?php echo html_escape($ah->group_type ?? $ah->group_category ?? ''); ?>">
                  <?php echo html_escape($ah->account_name . ' (' . $ah->account_code . ')'); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div>
          <label for="modal-custom-type" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Type <span class="text-error">*</span></label>
          <select id="modal-custom-type" name="account_type" required
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
            <option value="Bank">Bank Account</option>
            <option value="Cash">Cash Account / Counter</option>
            <option value="Receivable">Receivable Account</option>
            <option value="Payable">Payable Account</option>
            <option value="Other">Other Operational</option>
          </select>
        </div>
      </div>

      <!-- Account Number -->
      <div>
        <label for="modal-custom-number" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Number (Optional)</label>
        <input type="text" id="modal-custom-number" name="account_number" placeholder="e.g. 50200012345678"
               class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        <span class="text-[11px] text-on-surface-variant block mt-1">Bank account numbers will be safely masked in public tables.</span>
      </div>

      <!-- Opening Balance & Type -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="modal-custom-opening-bal" class="block text-body-sm font-semibold text-on-surface mb-1.5">Opening Balance (₹)</label>
          <input type="number" step="0.01" id="modal-custom-opening-bal" name="opening_balance" value="0.00"
                 class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        </div>
        <div>
          <label for="modal-custom-opening-type" class="block text-body-sm font-semibold text-on-surface mb-1.5">Opening Balance Type</label>
          <select id="modal-custom-opening-type" name="opening_balance_type"
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
            <option value="Debit">Debit (Asset / Expense)</option>
            <option value="Credit">Credit (Liability / Income / Equity)</option>
          </select>
        </div>
      </div>

      <!-- Description -->
      <div>
        <label for="modal-custom-description" class="block text-body-sm font-semibold text-on-surface mb-1.5">Description</label>
        <textarea id="modal-custom-description" name="description" rows="2" placeholder="Bank branch name, IFSC, or operational details"
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition"></textarea>
      </div>

      <!-- Status Checkbox -->
      <div>
        <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-outline-variant/60 bg-surface-container-low/40 cursor-pointer">
          <input type="checkbox" id="modal-custom-status" name="status" value="1" checked class="w-4 h-4 rounded text-primary focus:ring-primary cursor-pointer" />
          <span class="text-body-sm font-medium text-on-surface">Active Custom Account</span>
        </label>
      </div>

      <!-- Modal Footer -->
      <div class="pt-4 border-t border-outline-variant/50 flex items-center justify-end gap-3">
        <button type="button" onclick="closeCustomModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-md font-semibold transition cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="modal-custom-submit-btn" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Custom Account
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let customDataTable = null;

  $(document).ready(function() {
    initCustomDataTable();

    // Auto set account type when head changes
    $('#modal-custom-head').on('change', function() {
      const opt = $(this).find(':selected');
      const t = opt.data('type');
      const cat = opt.data('category');

      if (t === 'Bank') $('#modal-custom-type').val('Bank');
      else if (t === 'Cash') $('#modal-custom-type').val('Cash');
      else if (t === 'Receivable') $('#modal-custom-type').val('Receivable');
      else if (t === 'Payable') $('#modal-custom-type').val('Payable');
      else $('#modal-custom-type').val('Other');

      if (cat === 'Asset' || cat === 'Expense') {
        $('#modal-custom-opening-type').val('Debit');
      } else {
        $('#modal-custom-opening-type').val('Credit');
      }
    });
  });

  function initCustomDataTable() {
    if ($.fn.DataTable.isDataTable('#custom-accounts-table')) {
      $('#custom-accounts-table').DataTable().destroy();
    }
    customDataTable = $('#custom-accounts-table').DataTable({
      paging: true,
      pageLength: 15,
      order: [[1, 'asc']],
      columnDefs: [
        { orderable: false, targets: [9] }
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search custom accounts...",
        emptyTable: `
          <div class="flex flex-col items-center justify-center gap-2 py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-on-surface-variant/40">account_balance_wallet</span>
            <span class="font-semibold text-base text-on-surface">No custom accounts found.</span>
            <p class="text-sm text-on-surface-variant/70 max-w-sm text-center">Create school-specific bank, cash, or operational accounts under your account heads.</p>
            <?php if (!empty($can_create)): ?>
              <button type="button" onclick="openAddCustomModal()" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>Add Custom Account
              </button>
            <?php endif; ?>
          </div>
        `
      }
    });
  }

  function openAddCustomModal() {
    $('#modal-custom-title').text('Add Custom Account');
    $('#modal-custom-id').val('');
    $('#modal-custom-name').val('');
    $('#modal-custom-head').val('');
    $('#modal-custom-number').val('');
    $('#modal-custom-type').val('Bank');
    $('#modal-custom-opening-bal').val('0.00');
    $('#modal-custom-opening-type').val('Debit');
    $('#modal-custom-description').val('');
    $('#modal-custom-status').prop('checked', true);
    $('#custom-modal').removeClass('hidden');
  }

  function openEditCustomModal(ca) {
    $('#modal-custom-title').text('Edit Custom Account');
    $('#modal-custom-id').val(ca.id);
    $('#modal-custom-name').val(ca.account_name || '');
    $('#modal-custom-head').val(ca.account_head_id || '');
    $('#modal-custom-number').val(ca.account_number || '');
    $('#modal-custom-type').val(ca.account_type || 'Other');
    $('#modal-custom-opening-bal').val(parseFloat(ca.opening_balance || 0).toFixed(2));
    $('#modal-custom-opening-type').val(ca.opening_balance_type || 'Debit');
    $('#modal-custom-description').val(ca.description || '');
    $('#modal-custom-status').prop('checked', parseInt(ca.status) === 1);
    $('#custom-modal').removeClass('hidden');
  }

  function closeCustomModal() {
    $('#custom-modal').addClass('hidden');
  }

  function handleCustomSubmit(e) {
    e.preventDefault();
    const btn = $('#modal-custom-submit-btn');
    btn.prop('disabled', true).addClass('opacity-70');

    const payload = {
      id: $('#modal-custom-id').val(),
      account_name: $('#modal-custom-name').val(),
      account_head_id: $('#modal-custom-head').val(),
      account_number: $('#modal-custom-number').val(),
      account_type: $('#modal-custom-type').val(),
      opening_balance: $('#modal-custom-opening-bal').val(),
      opening_balance_type: $('#modal-custom-opening-type').val(),
      description: $('#modal-custom-description').val(),
      status: $('#modal-custom-status').is(':checked') ? 1 : 0
    };

    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/save_custom_account_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        btn.prop('disabled', false).removeClass('opacity-70');
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res.status === 'success') {
          closeCustomModal();
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 700);
        } else {
          showNotification(res.message || 'Failed to save custom account.', 'error');
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).removeClass('opacity-70');
        let msg = 'An unexpected error occurred.';
        try {
          const err = JSON.parse(xhr.responseText);
          if (err.message) msg = err.message;
          if (err.csrf_hash) window.CSRF_HASH = err.csrf_hash;
        } catch(e) {}
        showNotification(msg, 'error');
      }
    });
  }

  function toggleCustomStatus(id, name) {
    if (!confirm('Toggle active status for custom account "' + name + '"?')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/toggle_custom_account_status_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res.status === 'success') {
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 600);
        } else {
          showNotification(res.message || 'Failed to update status.', 'error');
        }
      },
      error: function(xhr) {
        showNotification('Failed to update status.', 'error');
      }
    });
  }

  function deleteCustomAccount(id, name) {
    if (!confirm('Are you sure you want to delete the custom account "' + name + '"?\nNote: Deletion will be rejected if this account has posted transactions.')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/delete_custom_account_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res.status === 'success') {
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 600);
        } else {
          alert(res.message || 'Cannot delete custom account. Deactivate it instead.');
          showNotification(res.message, 'error');
        }
      },
      error: function(xhr) {
        alert('Cannot delete custom account.');
      }
    });
  }

  function showNotification(msg, type) {
    const isSuccess = (type === 'success');
    const container = $('#flash-message-container');
    const bgCls = isSuccess ? 'bg-secondary-container text-on-secondary-container border-secondary/20' : 'bg-error-container text-on-error-container border-error/20';
    const icon = isSuccess ? 'check_circle' : 'error';

    const html = `
      <div class="mb-4 p-3.5 rounded-xl ${bgCls} text-body-md font-medium flex items-center gap-2 border animate-in fade-in duration-200">
        <span class="material-symbols-outlined text-[20px]">${icon}</span>
        <span>${msg}</span>
      </div>
    `;
    container.html(html);
  }
</script>
