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
    <h2 class="font-headline-md text-headline-md text-on-surface">Account Heads</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage accounting heads used for financial transactions.</p>
  </div>
  <div class="flex items-center gap-2 flex-wrap shrink-0">
    <a href="<?php echo site_url('fee-finance/account-groups'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">folder</span>Account Groups
    </a>
    <a href="<?php echo site_url('fee-finance/custom-accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>Custom Accounts
    </a>
    <?php if (!empty($can_create)): ?>
      <button type="button" onclick="openAddHeadModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">add</span>Add Account Head
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Account Heads Table Card -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-outline-variant/50 gap-3">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">General Ledger Account Heads</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">Categorized accounting categories with double-entry live balances.</p>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-body-sm text-on-surface-variant font-medium"><?php echo count($heads ?? []); ?> Total Heads</span>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto p-4">
    <table id="account-heads-table" class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider w-10">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Head</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Group</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account Type</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Code</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Description</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Opening Balance</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Current Balance</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($heads)): ?>
          <?php $idx = 1; foreach ($heads as $h): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors" id="head-row-<?php echo $h->id; ?>">
              <td class="px-4 py-3 font-mono text-on-surface-variant text-xs"><?php echo $idx++; ?></td>
              <td class="px-4 py-3 font-medium text-on-surface">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-on-surface"><?php echo html_escape($h->account_name); ?></span>
                  <?php if (!empty($h->is_system)): ?>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant" title="Built-in core account">System</span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <?php
                  $gCat = $h->group_type ?? $h->group_category ?? '';
                  $gBadge = 'bg-surface-container-high text-on-surface';
                  switch ($gCat) {
                    case 'Asset':     $gBadge = 'bg-blue-100 text-blue-800 border-blue-200'; break;
                    case 'Liability': $gBadge = 'bg-amber-100 text-amber-800 border-amber-200'; break;
                    case 'Equity':    $gBadge = 'bg-purple-100 text-purple-800 border-purple-200'; break;
                    case 'Income':    $gBadge = 'bg-emerald-100 text-emerald-800 border-emerald-200'; break;
                    case 'Expense':   $gBadge = 'bg-rose-100 text-rose-800 border-rose-200'; break;
                  }
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $gBadge; ?>">
                  <?php echo html_escape($h->group_name ?? $gCat); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-sm whitespace-nowrap">
                <?php echo html_escape($h->account_type); ?>
              </td>
              <td class="px-4 py-3 font-mono font-bold text-primary text-sm whitespace-nowrap">
                <?php echo html_escape($h->account_code); ?>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-sm max-w-[200px] truncate" title="<?php echo html_escape($h->description ?: '—'); ?>">
                <?php echo html_escape($h->description ?: '—'); ?>
              </td>
              <td class="px-4 py-3 text-right font-mono text-on-surface whitespace-nowrap text-sm">
                ₹<?php echo number_format($h->opening_balance ?? 0, 2); ?>
                <span class="text-[10px] text-on-surface-variant font-medium">(<?php echo html_escape($h->opening_balance_type ?? 'Debit'); ?>)</span>
              </td>
              <td class="px-4 py-3 text-right font-mono font-bold text-on-surface whitespace-nowrap text-sm">
                <?php $bal = (float)($h->current_balance ?? 0); ?>
                <span class="<?php echo ($bal < 0) ? 'text-rose-700' : 'text-on-surface'; ?>">
                  ₹<?php echo number_format($bal, 2); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if ((int)($h->status ?? 1) === 1): ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Active
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                    Inactive
                  </span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <?php if (!empty($can_edit)): ?>
                    <button type="button" onclick='openEditHeadModal(<?php echo json_encode($h); ?>)' 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer" 
                            title="Edit Account Head">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button type="button" onclick="toggleHeadStatus(<?php echo $h->id; ?>, '<?php echo html_escape(addslashes($h->account_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-amber-50 hover:text-amber-700 transition-colors cursor-pointer" 
                            title="Toggle Status (Active/Inactive)">
                      <span class="material-symbols-outlined text-[18px]"><?php echo ((int)$h->status === 1) ? 'toggle_on' : 'toggle_off'; ?></span>
                    </button>
                  <?php endif; ?>

                  <?php if (!empty($can_delete) && empty($h->is_system)): ?>
                    <button type="button" onclick="deleteHead(<?php echo $h->id; ?>, '<?php echo html_escape(addslashes($h->account_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors cursor-pointer" 
                            title="Delete Account Head">
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

<!-- Add / Edit Account Head Modal -->
<div id="head-modal" class="fixed inset-0 bg-dark-text/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs">
  <div class="w-full max-w-lg bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl overflow-hidden animate-in fade-in duration-200">
    <div class="px-6 py-4 border-b border-outline-variant/50 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">account_tree</span>
        </span>
        <h3 id="modal-head-title" class="font-headline-md text-title-md font-semibold text-on-surface">Add Account Head</h3>
      </div>
      <button type="button" onclick="closeHeadModal()" class="w-8 h-8 rounded-lg text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center transition cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="head-form" onsubmit="handleHeadSubmit(event)" class="p-6 space-y-4">
      <input type="hidden" id="modal-head-id" name="id" value="" />

      <!-- Account Group * -->
      <div>
        <label for="modal-head-group" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Group <span class="text-error">*</span></label>
        <select id="modal-head-group" name="account_group_id" required onchange="onGroupSelectChange()"
                class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
          <option value="">— Select Account Group —</option>
          <?php if (!empty($groups)): ?>
            <?php foreach ($groups as $g): ?>
              <option value="<?php echo $g->id; ?>" data-category="<?php echo html_escape($g->category); ?>">
                <?php echo html_escape($g->group_name . ' (' . $g->category . ')'); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- Account Head Name * & Code -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
          <label for="modal-head-name" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Head Name <span class="text-error">*</span></label>
          <input type="text" id="modal-head-name" name="account_name" required placeholder="e.g. Laboratory Fee, Electricity Bills"
                 class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        </div>
        <div>
          <label for="modal-head-code" class="block text-body-sm font-semibold text-on-surface mb-1.5">Account Code</label>
          <input type="text" id="modal-head-code" name="account_code" placeholder="e.g. 4060"
                 class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        </div>
      </div>

      <!-- Opening Balance & Opening Balance Type -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="modal-head-opening-bal" class="block text-body-sm font-semibold text-on-surface mb-1.5">Opening Balance (₹)</label>
          <input type="number" step="0.01" id="modal-head-opening-bal" name="opening_balance" value="0.00"
                 class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface font-mono text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        </div>
        <div>
          <label for="modal-head-opening-type" class="block text-body-sm font-semibold text-on-surface mb-1.5">Opening Balance Type</label>
          <select id="modal-head-opening-type" name="opening_balance_type"
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
            <option value="Debit">Debit (Asset / Expense)</option>
            <option value="Credit">Credit (Liability / Income / Equity)</option>
          </select>
        </div>
      </div>

      <!-- Description -->
      <div>
        <label for="modal-head-description" class="block text-body-sm font-semibold text-on-surface mb-1.5">Description</label>
        <textarea id="modal-head-description" name="description" rows="2" placeholder="Optional details about this account head"
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition"></textarea>
      </div>

      <!-- Status Checkbox -->
      <div>
        <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-outline-variant/60 bg-surface-container-low/40 cursor-pointer">
          <input type="checkbox" id="modal-head-status" name="status" value="1" checked class="w-4 h-4 rounded text-primary focus:ring-primary cursor-pointer" />
          <span class="text-body-sm font-medium text-on-surface">Active Account Head</span>
        </label>
      </div>

      <!-- Modal Footer -->
      <div class="pt-4 border-t border-outline-variant/50 flex items-center justify-end gap-3">
        <button type="button" onclick="closeHeadModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-md font-semibold transition cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="modal-head-submit-btn" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Account Head
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let headsDataTable = null;

  $(document).ready(function() {
    initHeadsDataTable();
  });

  function initHeadsDataTable() {
    if ($.fn.DataTable.isDataTable('#account-heads-table')) {
      $('#account-heads-table').DataTable().destroy();
    }
    headsDataTable = $('#account-heads-table').DataTable({
      paging: true,
      pageLength: 15,
      order: [[4, 'asc']],
      columnDefs: [
        { orderable: false, targets: [9] }
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search account heads...",
        emptyTable: `
          <div class="flex flex-col items-center justify-center gap-2 py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-on-surface-variant/40">account_tree</span>
            <span class="font-semibold text-base text-on-surface">No account heads found.</span>
            <p class="text-sm text-on-surface-variant/70 max-w-sm text-center">Create account heads under your account groups to configure the Chart of Accounts.</p>
            <?php if (!empty($can_create)): ?>
              <button type="button" onclick="openAddHeadModal()" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>Add Account Head
              </button>
            <?php endif; ?>
          </div>
        `
      }
    });
  }

  function onGroupSelectChange() {
    const sel = $('#modal-head-group');
    const gid = sel.val();
    if (!gid) return;

    const opt = sel.find(':selected');
    const cat = opt.data('category');

    // Auto set typical opening balance type
    if (cat === 'Asset' || cat === 'Expense') {
      $('#modal-head-opening-type').val('Debit');
    } else {
      $('#modal-head-opening-type').val('Credit');
    }

    // Only auto-generate code if currently empty or adding new
    if (!$('#modal-head-id').val() || !$('#modal-head-code').val()) {
      $.ajax({
        url: '<?php echo site_url("finance/generate_account_code_ajax"); ?>',
        type: 'GET',
        dataType: 'json',
        data: { group_id: gid },
        success: function(res) {
          if (res && res.code) {
            $('#modal-head-code').val(res.code);
          }
        }
      });
    }
  }

  function openAddHeadModal() {
    $('#modal-head-title').text('Add Account Head');
    $('#modal-head-id').val('');
    $('#modal-head-group').val('');
    $('#modal-head-name').val('');
    $('#modal-head-code').val('');
    $('#modal-head-opening-bal').val('0.00');
    $('#modal-head-opening-type').val('Debit');
    $('#modal-head-description').val('');
    $('#modal-head-status').prop('checked', true);
    $('#head-modal').removeClass('hidden');
  }

  function openEditHeadModal(h) {
    $('#modal-head-title').text('Edit Account Head');
    $('#modal-head-id').val(h.id);
    $('#modal-head-group').val(h.account_group_id || '');
    $('#modal-head-name').val(h.account_name || '');
    $('#modal-head-code').val(h.account_code || '');
    $('#modal-head-opening-bal').val(parseFloat(h.opening_balance || 0).toFixed(2));
    $('#modal-head-opening-type').val(h.opening_balance_type || 'Debit');
    $('#modal-head-description').val(h.description || '');
    $('#modal-head-status').prop('checked', parseInt(h.status) === 1);
    $('#head-modal').removeClass('hidden');
  }

  function closeHeadModal() {
    $('#head-modal').addClass('hidden');
  }

  function handleHeadSubmit(e) {
    e.preventDefault();
    const btn = $('#modal-head-submit-btn');
    btn.prop('disabled', true).addClass('opacity-70');

    const payload = {
      id: $('#modal-head-id').val(),
      account_group_id: $('#modal-head-group').val(),
      account_name: $('#modal-head-name').val(),
      account_code: $('#modal-head-code').val(),
      opening_balance: $('#modal-head-opening-bal').val(),
      opening_balance_type: $('#modal-head-opening-type').val(),
      description: $('#modal-head-description').val(),
      status: $('#modal-head-status').is(':checked') ? 1 : 0
    };

    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/save_account_head_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        btn.prop('disabled', false).removeClass('opacity-70');
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res.status === 'success') {
          closeHeadModal();
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 700);
        } else {
          showNotification(res.message || 'Failed to save account head.', 'error');
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

  function toggleHeadStatus(id, name) {
    if (!confirm('Toggle active status for account head "' + name + '"?')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/toggle_account_head_status_ajax"); ?>',
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

  function deleteHead(id, name) {
    if (!confirm('Are you sure you want to delete the account head "' + name + '"?\nNote: Deletion will be rejected if this account head is already in use.')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/delete_account_head_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res.status === 'success') {
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 600);
        } else {
          alert(res.message || 'Cannot delete account head. Deactivate it instead.');
          showNotification(res.message, 'error');
        }
      },
      error: function(xhr) {
        alert('Cannot delete account head with active associations.');
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
