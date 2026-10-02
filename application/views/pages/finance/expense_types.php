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
        <h2 class="font-headline-md text-headline-md text-on-surface">Expense Categories & Types</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Classify and organize institutional operational expenses and associate them with Chart of Accounts heads.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openCategoryModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add</span>New Expense Category
        </button>
        <a href="<?php echo site_url('finance/expenses'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">receipt</span>Expenses List
        </a>
      </div>
    </div>

    <!-- Category List Table -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Category Name</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Linked General Ledger Account</th>
              <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Description</th>
              <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
              <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (!empty($expense_types)): ?>
              <?php foreach ($expense_types as $et): ?>
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                  <td class="px-4 py-3 font-semibold text-on-surface"><?php echo html_escape($et->name); ?></td>
                  <td class="px-4 py-3 text-on-surface text-sm">
                    <?php if (!empty($et->account_name)): ?>
                      <?php echo html_escape($et->account_name . ' (' . $et->account_code . ')'); ?>
                    <?php else: ?>
                      <span class="text-on-surface-variant italic">Default General Expenses</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant text-sm"><?php echo html_escape($et->description ?: '—'); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if (!empty($et->is_active) || !empty($et->status)): ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-highest text-on-surface-variant">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <button type="button" onclick='editCategory(<?php echo json_encode($et); ?>)' class="p-1 rounded-lg text-primary hover:bg-primary/10 cursor-pointer" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>
                      <form method="post" action="<?php echo site_url('finance/expense_types'); ?>" class="inline" onsubmit="return confirm('Delete this expense category?');">
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="expense_type_id" value="<?php echo $et->id; ?>"/>
                        <button type="submit" class="p-1 rounded-lg text-error hover:bg-error/10 cursor-pointer" title="Delete">
                          <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No expense categories created yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CATEGORY MODAL -->
    <div id="category-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="cat-modal-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[24px]">category</span>Create Expense Category
          </h3>
          <button onclick="closeCategoryModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('finance/expense_types', array('id' => 'category-form', 'class' => 'space-y-4')); ?>
          <input type="hidden" name="expense_type_id" id="cat-id" value="0"/>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category Name *</label>
            <input type="text" name="name" id="cat-name" required placeholder="e.g. Science Lab Supplies" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Linked Expense Account</label>
            <select name="account_id" id="cat-account-id" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">Auto-resolve from general expenses</option>
              <?php foreach ($expense_accounts as $ea): ?>
                <option value="<?php echo $ea->id; ?>"><?php echo html_escape($ea->account_name . ' (' . $ea->account_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
            <textarea name="description" id="cat-desc" rows="2" placeholder="Optional notes for this category..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="is_active" id="cat-active" value="1" checked class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/20"/>
              <span class="text-body-md text-on-surface font-medium">Active Category</span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
              Save Category
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openCategoryModal() {
        document.getElementById('cat-modal-title').innerHTML = '<span class="material-symbols-outlined text-primary text-[24px]">category</span>Create Expense Category';
        document.getElementById('cat-id').value = '0';
        document.getElementById('cat-name').value = '';
        document.getElementById('cat-account-id').value = '';
        document.getElementById('cat-desc').value = '';
        document.getElementById('cat-active').checked = true;

        var m = document.getElementById('category-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function editCategory(cat) {
        document.getElementById('cat-modal-title').innerHTML = '<span class="material-symbols-outlined text-primary text-[24px]">edit</span>Edit Expense Category';
        document.getElementById('cat-id').value = cat.id;
        document.getElementById('cat-name').value = cat.name;
        document.getElementById('cat-account-id').value = cat.account_id || '';
        document.getElementById('cat-desc').value = cat.description || '';
        document.getElementById('cat-active').checked = (cat.is_active == 1 || cat.status == 1);

        var m = document.getElementById('category-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
      }

      function closeCategoryModal() {
        var m = document.getElementById('category-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
      }
    </script>
