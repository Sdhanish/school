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
        <h2 class="font-headline-md text-headline-md text-on-surface">Class Fee Structures</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Define standard fee amounts and payment frequencies for specific classes. Payment due dates are set during Student Fee Assignment.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openStructureModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add</span>New Fee Structure
        </button>
        <a href="<?php echo site_url('finance/fee_assignments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">assignment_ind</span>Fee Assignments
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Structure Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Class</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Fee Head</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Amount (₹)</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Frequency</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($structures)): ?>
              <?php foreach ($structures as $st): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-semibold text-on-surface"><?php echo html_escape($st->structure_name); ?></td>
                  <td class="px-4 py-3 font-medium text-on-surface"><?php echo html_escape($st->class_name ?: 'All Classes'); ?></td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($st->type_name); ?></td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-on-surface">₹<?php echo number_format($st->amount, 2); ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface"><?php echo html_escape($st->frequency); ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($st->status): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Active
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface-variant">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button onclick="editStructure(<?php echo html_escape(json_encode($st)); ?>)" class="p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors cursor-pointer mr-1" title="Edit">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <form method="post" action="<?php echo site_url('finance/fee_structures'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this fee structure?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$st->id; ?>">
                      <button type="submit" class="p-1 rounded-lg text-error hover:bg-error/10 transition-colors cursor-pointer" title="Delete">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-4xl mb-2 text-on-surface-variant/40 block">account_balance_wallet</span>
                  No class fee structures configured yet. Click "New Fee Structure" to configure one.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div id="structureModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-scrim/40 backdrop-blur-xs hidden">
      <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl w-full max-w-lg p-6 elevation-3">
        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/40 mb-4">
          <h3 id="modalTitle" class="font-headline-md text-headline-md text-on-surface">New Fee Structure</h3>
          <button onclick="closeStructureModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>
        <form method="post" action="<?php echo site_url('finance/fee_structures'); ?>">
          <input type="hidden" name="id" id="fs_id" value="">
          <div class="space-y-4">
            <div>
              <label class="block text-label-md font-semibold text-on-surface mb-1">Structure Name <span class="text-error">*</span></label>
              <input type="text" name="structure_name" id="fs_name" required placeholder="e.g. Grade 1 Annual Tuition 2026-27" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Class <span class="text-error">*</span></label>
                <select name="class_id" id="fs_class_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="">-- Select Class --</option>
                  <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $c): ?>
                      <option value="<?php echo (int)$c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Fee Type / Head <span class="text-error">*</span></label>
                <select name="fee_type_id" id="fs_fee_type_id" required class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="">-- Select Fee Head --</option>
                  <?php if (!empty($fee_types)): ?>
                    <?php foreach ($fee_types as $ft): ?>
                      <option value="<?php echo (int)$ft->id; ?>"><?php echo html_escape($ft->type_name); ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Amount (₹) <span class="text-error">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" id="fs_amount" required placeholder="0.00" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md font-mono focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
              </div>
              <div>
                <label class="block text-label-md font-semibold text-on-surface mb-1">Frequency</label>
                <select name="frequency" id="fs_frequency" class="w-full px-3 py-2 border border-outline rounded-lg text-on-surface bg-surface text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-hidden">
                  <option value="Annual">Annual</option>
                  <option value="Term-Wise">Term-Wise</option>
                  <option value="Monthly">Monthly</option>
                  <option value="One-Time">One-Time</option>
                </select>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" name="status" id="fs_status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary cursor-pointer">
              <label for="fs_status" class="text-label-md text-on-surface cursor-pointer select-none">Active</label>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-outline-variant/40">
            <button type="button" onclick="closeStructureModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer text-label-md">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-sm cursor-pointer text-label-md font-semibold">Save Structure</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openStructureModal() {
        document.getElementById('modalTitle').textContent = 'New Fee Structure';
        document.getElementById('fs_id').value = '';
        document.getElementById('fs_name').value = '';
        document.getElementById('fs_class_id').value = '';
        document.getElementById('fs_fee_type_id').value = '';
        document.getElementById('fs_amount').value = '';
        document.getElementById('fs_frequency').value = 'Annual';
        document.getElementById('fs_status').checked = true;
        document.getElementById('structureModal').classList.remove('hidden');
      }

      function editStructure(st) {
        document.getElementById('modalTitle').textContent = 'Edit Fee Structure';
        document.getElementById('fs_id').value = st.id;
        document.getElementById('fs_name').value = st.structure_name;
        document.getElementById('fs_class_id').value = st.class_id;
        document.getElementById('fs_fee_type_id').value = st.fee_type_id;
        document.getElementById('fs_amount').value = st.amount;
        document.getElementById('fs_frequency').value = st.frequency;
        document.getElementById('fs_status').checked = st.status == 1;
        document.getElementById('structureModal').classList.remove('hidden');
      }

      function closeStructureModal() {
        document.getElementById('structureModal').classList.add('hidden');
      }
    </script>
