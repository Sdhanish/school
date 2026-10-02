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
        <h2 class="font-headline-md text-headline-md text-on-surface">Fee Heads & Types</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure institutional fee heads and map them to revenue accounts in the Chart of Accounts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openFeeTypeModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add</span>New Fee Type
        </button>
        <a href="<?php echo site_url('finance/fee_structures'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">payments</span>Fee Structures
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type Code</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Fee Type Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Linked Income Account Head</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Description</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($fee_types)): ?>
              <?php foreach ($fee_types as $ft): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary"><?php echo html_escape($ft->type_code); ?></td>
                  <td class="px-4 py-3 font-semibold text-on-surface"><?php echo html_escape($ft->type_name); ?></td>
                  <td class="px-4 py-3 text-on-surface text-sm">
                    <?php if (!empty($ft->account_name)): ?>
                      <span class="font-medium"><?php echo html_escape($ft->account_name); ?></span>
                      <span class="text-xs font-mono text-on-surface-variant ml-1">(<?php echo html_escape($ft->account_code); ?>)</span>
                    <?php else: ?>
                      <span class="text-on-surface-variant italic">Unassigned</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($ft->description ?: '—'); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($ft->status): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Active
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface-variant">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button onclick="editFeeType(<?php echo html_escape(json_encode($ft)); ?>)" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors cursor-pointer mr-1" title="Edit">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <form method="post" action="<?php echo site_url('finance/fee_types'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this fee type?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$ft->id; ?>">
                      <button type="submit" class="p-1 rounded-lg text-error hover:bg-error/10 transition-colors cursor-pointer" title="Delete">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-4xl mb-2 text-on-surface-variant/40 block">payments</span>
                  No fee types configured yet. Click "New Fee Type" to add one.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div id="feeTypeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-scrim/40 backdrop-blur-xs hidden">
      <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl w-full max-w-md p-6 elevation-3">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/40 mb-4">
          <h3 id="modalTitle" class="font-headline-md text-headline-md text-on-surface">New Fee Type</h3>
          <button onclick="closeFeeTypeModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>
        <form method="post" action="<?php echo site_url('finance/fee_types'); ?>">
          <input type="hidden" name="id" id="ft_id" value="">
          <div class="space-y-4">
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Fee Type Name <span class="text-error">*</span></label>
              <input type="text" name="type_name" id="ft_name" required placeholder="e.g. Tuition Fee, Admission Fee" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
            </div>
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Fee Type Code <span class="text-error">*</span></label>
              <input type="text" name="type_code" id="ft_code" required placeholder="e.g. TUIT, ADM, EXAM" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md font-mono uppercase focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
            </div>
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Linked Income Account Head <span class="text-error">*</span></label>
              <select name="account_id" id="ft_account_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                <option value="">-- Select Revenue Account --</option>
                <?php if (!empty($income_accounts)): ?>
                  <?php foreach ($income_accounts as $acc): ?>
                    <option value="<?php echo (int)$acc->id; ?>"><?php echo html_escape($acc->account_name . ' (' . $acc->account_code . ')'); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Description</label>
              <textarea name="description" id="ft_desc" rows="2" placeholder="Optional notes or terms" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden"></textarea>
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" name="status" id="ft_status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary cursor-pointer">
              <label for="ft_status" class="text-label-md text-on-surface cursor-pointer select-none">Active</label>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeFeeTypeModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer text-label-md">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-sm cursor-pointer text-label-md font-semibold">Save Fee Type</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openFeeTypeModal() {
        document.getElementById('modalTitle').textContent = 'New Fee Type';
        document.getElementById('ft_id').value = '';
        document.getElementById('ft_name').value = '';
        document.getElementById('ft_code').value = '';
        document.getElementById('ft_account_id').value = '';
        document.getElementById('ft_desc').value = '';
        document.getElementById('ft_status').checked = true;
        document.getElementById('feeTypeModal').classList.remove('hidden');
      }

      function editFeeType(ft) {
        document.getElementById('modalTitle').textContent = 'Edit Fee Type';
        document.getElementById('ft_id').value = ft.id;
        document.getElementById('ft_name').value = ft.type_name;
        document.getElementById('ft_code').value = ft.type_code;
        document.getElementById('ft_account_id').value = ft.account_id;
        document.getElementById('ft_desc').value = ft.description || '';
        document.getElementById('ft_status').checked = ft.status == 1;
        document.getElementById('feeTypeModal').classList.remove('hidden');
      }

      function closeFeeTypeModal() {
        document.getElementById('feeTypeModal').classList.add('hidden');
      }
    </script>
