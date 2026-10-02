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

<!-- Header & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="font-headline-md text-headline-md text-on-surface">Account Groups</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">Organize financial accounts into major accounting categories.</p>
  </div>
  <div class="flex items-center gap-2 flex-wrap shrink-0">
    <a href="<?php echo site_url('fee-finance/account-heads'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_tree</span>View Account Heads
    </a>
    <?php if (!empty($can_create)): ?>
      <button type="button" onclick="openAddGroupModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
        <span class="material-symbols-outlined text-[18px]">add</span>Add Account Group
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Account Groups Table Card -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-outline-variant/50 gap-3">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Chart of Accounts — Groups</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">Top-level accounting classifications for <?php echo html_escape($this->current_school->school_name ?? 'this school'); ?>.</p>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-body-sm text-on-surface-variant font-medium"><span id="group-count-badge"><?php echo count($groups ?? []); ?></span> Groups Configured</span>
    </div>
  </div>

  <div class="table-scroll overflow-x-auto p-4">
    <table id="account-groups-table" class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider w-12">#</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Group Name</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Group Type</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Description</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Display Order</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Created At</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($groups)): ?>
          <?php $idx = 1; foreach ($groups as $grp): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors" id="group-row-<?php echo $grp->id; ?>">
              <td class="px-4 py-3.5 font-mono text-on-surface-variant text-xs"><?php echo $idx++; ?></td>
              <td class="px-4 py-3.5 font-medium text-on-surface">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-[20px]">folder</span>
                  <span class="font-bold text-on-surface"><?php echo html_escape($grp->group_name); ?></span>
                  <?php if (!empty($grp->is_system)): ?>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant" title="Standard core system classification">System</span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <?php
                  $cat = $grp->group_type ?? $grp->category ?? '';
                  $badgeCls = 'bg-surface-container-high text-on-surface';
                  switch ($cat) {
                    case 'Asset':     $badgeCls = 'bg-blue-100 text-blue-800 border-blue-200'; break;
                    case 'Liability': $badgeCls = 'bg-amber-100 text-amber-800 border-amber-200'; break;
                    case 'Equity':    $badgeCls = 'bg-purple-100 text-purple-800 border-purple-200'; break;
                    case 'Income':    $badgeCls = 'bg-emerald-100 text-emerald-800 border-emerald-200'; break;
                    case 'Expense':   $badgeCls = 'bg-rose-100 text-rose-800 border-rose-200'; break;
                  }
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeCls; ?>">
                  <?php echo html_escape($cat); ?>
                </span>
              </td>
              <td class="px-4 py-3.5 text-on-surface-variant text-sm max-w-[260px] truncate" title="<?php echo html_escape($grp->description ?: '—'); ?>">
                <?php echo html_escape($grp->description ?: '—'); ?>
              </td>
              <td class="px-4 py-3.5 text-center font-mono font-medium text-on-surface text-sm">
                <?php echo (int)($grp->display_order ?? 0); ?>
              </td>
              <td class="px-4 py-3.5 text-center whitespace-nowrap">
                <?php if ((int)($grp->status ?? 1) === 1): ?>
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
                <?php echo !empty($grp->created_at) ? date('d-M-Y', strtotime($grp->created_at)) : '—'; ?>
              </td>
              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <?php if (!empty($can_edit)): ?>
                    <button type="button" onclick='openEditGroupModal(<?php echo json_encode($grp); ?>)' 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer" 
                            title="Edit Account Group">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <button type="button" onclick="toggleGroupStatus(<?php echo $grp->id; ?>, '<?php echo html_escape(addslashes($grp->group_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-amber-50 hover:text-amber-700 transition-colors cursor-pointer" 
                            title="Toggle Status (Active/Inactive)">
                      <span class="material-symbols-outlined text-[18px]"><?php echo ((int)$grp->status === 1) ? 'toggle_on' : 'toggle_off'; ?></span>
                    </button>
                  <?php endif; ?>

                  <?php if (!empty($can_delete) && empty($grp->is_system)): ?>
                    <button type="button" onclick="deleteGroup(<?php echo $grp->id; ?>, '<?php echo html_escape(addslashes($grp->group_name)); ?>')" 
                            class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors cursor-pointer" 
                            title="Delete Account Group">
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

<!-- Add / Edit Account Group Modal -->
<div id="group-modal" class="fixed inset-0 bg-dark-text/40 z-50 hidden flex items-center justify-center p-4 backdrop-blur-xs">
  <div class="w-full max-w-md bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl overflow-hidden animate-in fade-in duration-200">
    <div class="px-6 py-4 border-b border-outline-variant/50 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
          <span class="material-symbols-outlined text-[20px]">folder</span>
        </span>
        <h3 id="modal-group-title" class="font-headline-md text-title-md font-semibold text-on-surface">Add Account Group</h3>
      </div>
      <button type="button" onclick="closeGroupModal()" class="w-8 h-8 rounded-lg text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center transition cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <form id="group-form" onsubmit="handleGroupSubmit(event)" class="p-6 space-y-4">
      <input type="hidden" id="modal-group-id" name="id" value="" />

      <!-- Group Name * -->
      <div>
        <label for="modal-group-name" class="block text-body-sm font-semibold text-on-surface mb-1.5">Group Name <span class="text-error">*</span></label>
        <input type="text" id="modal-group-name" name="group_name" required placeholder="e.g. Current Assets, Operating Expenses"
               class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
      </div>

      <!-- Group Type * -->
      <div>
        <label for="modal-group-type" class="block text-body-sm font-semibold text-on-surface mb-1.5">Group Type <span class="text-error">*</span></label>
        <select id="modal-group-type" name="group_type" required
                class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition cursor-pointer">
          <option value="">— Select Classification —</option>
          <option value="Asset">Asset (Cash, Bank, Receivables, Property)</option>
          <option value="Liability">Liability (Payables, Loans, Provisions)</option>
          <option value="Equity">Equity (Capital, Reserves, Retained Surplus)</option>
          <option value="Income">Income (Tuition Fees, Admissions, Grants)</option>
          <option value="Expense">Expense (Salaries, Utilities, Maintenance)</option>
        </select>
      </div>

      <!-- Description -->
      <div>
        <label for="modal-group-description" class="block text-body-sm font-semibold text-on-surface mb-1.5">Description</label>
        <textarea id="modal-group-description" name="description" rows="2" placeholder="Optional details about this accounting group"
                  class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition"></textarea>
      </div>

      <!-- Display Order & Status -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="modal-group-order" class="block text-body-sm font-semibold text-on-surface mb-1.5">Display Order</label>
          <input type="number" id="modal-group-order" name="display_order" value="0" min="0"
                 class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden transition" />
        </div>
        <div>
          <label class="block text-body-sm font-semibold text-on-surface mb-1.5">Status</label>
          <label class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg border border-outline-variant/60 bg-surface-container-low/40 cursor-pointer">
            <input type="checkbox" id="modal-group-status" name="status" value="1" checked class="w-4 h-4 rounded text-primary focus:ring-primary cursor-pointer" />
            <span class="text-body-sm font-medium text-on-surface">Active</span>
          </label>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="pt-4 border-t border-outline-variant/50 flex items-center justify-end gap-3">
        <button type="button" onclick="closeGroupModal()" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-md font-semibold transition cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="modal-group-submit-btn" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Group
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let groupsDataTable = null;

  $(document).ready(function() {
    initGroupsDataTable();
  });

  function initGroupsDataTable() {
    if ($.fn.DataTable.isDataTable('#account-groups-table')) {
      $('#account-groups-table').DataTable().destroy();
    }
    groupsDataTable = $('#account-groups-table').DataTable({
      paging: true,
      pageLength: 10,
      order: [[4, 'asc'], [1, 'asc']],
      columnDefs: [
        { orderable: false, targets: [7] }
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search account groups...",
        emptyTable: `
          <div class="flex flex-col items-center justify-center gap-2 py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-[40px] text-on-surface-variant/40">folder_off</span>
            <span class="font-semibold text-base text-on-surface">No account groups found.</span>
            <p class="text-sm text-on-surface-variant/70 max-w-sm text-center">Create your first account group to start building the Chart of Accounts.</p>
            <?php if (!empty($can_create)): ?>
              <button type="button" onclick="openAddGroupModal()" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary/90 transition shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>Add Account Group
              </button>
            <?php endif; ?>
          </div>
        `
      }
    });
  }

  function openAddGroupModal() {
    $('#modal-group-title').text('Add Account Group');
    $('#modal-group-id').val('');
    $('#modal-group-name').val('');
    $('#modal-group-type').val('');
    $('#modal-group-description').val('');
    $('#modal-group-order').val('0');
    $('#modal-group-status').prop('checked', true);
    $('#group-modal').removeClass('hidden');
  }

  function openEditGroupModal(grp) {
    $('#modal-group-title').text('Edit Account Group');
    $('#modal-group-id').val(grp.id);
    $('#modal-group-name').val(grp.group_name || grp.name || '');
    $('#modal-group-type').val(grp.group_type || grp.category || '');
    $('#modal-group-description').val(grp.description || '');
    $('#modal-group-order').val(grp.display_order || 0);
    $('#modal-group-status').prop('checked', parseInt(grp.status) === 1);
    $('#group-modal').removeClass('hidden');
  }

  function closeGroupModal() {
    $('#group-modal').addClass('hidden');
  }

  function handleGroupSubmit(e) {
    e.preventDefault();
    const btn = $('#modal-group-submit-btn');
    btn.prop('disabled', true).addClass('opacity-70');

    const payload = {
      id: $('#modal-group-id').val(),
      group_name: $('#modal-group-name').val(),
      group_type: $('#modal-group-type').val(),
      description: $('#modal-group-description').val(),
      display_order: $('#modal-group-order').val(),
      status: $('#modal-group-status').is(':checked') ? 1 : 0
    };

    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/save_account_group_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        btn.prop('disabled', false).removeClass('opacity-70');
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        if (res.status === 'success') {
          closeGroupModal();
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 700);
        } else {
          showNotification(res.message || 'Failed to save account group.', 'error');
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

  function toggleGroupStatus(id, name) {
    if (!confirm('Toggle active status for group "' + name + '"?')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/toggle_account_group_status_ajax"); ?>',
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

  function deleteGroup(id, name) {
    if (!confirm('Are you sure you want to delete the account group "' + name + '"?\nThis action cannot be undone.')) return;

    const payload = { id: id };
    if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
      payload[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
    }

    $.ajax({
      url: '<?php echo site_url("finance/delete_account_group_ajax"); ?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res.status === 'success') {
          showNotification(res.message, 'success');
          setTimeout(() => window.location.reload(), 600);
        } else {
          // Warning displayed directly as requested
          alert(res.message || 'This account group is currently in use and cannot be deleted.');
          showNotification(res.message, 'error');
        }
      },
      error: function(xhr) {
        alert('This account group is currently in use and cannot be deleted.');
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
