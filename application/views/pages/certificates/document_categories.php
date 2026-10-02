<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Document Categories Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure student document categories, mandatory requirements, and expiration rules.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('catModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Category
        </button>
      </div>
    </div>

    <!-- Categories Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Document Categories (<?php echo count($categories); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Code</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Required</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Expiry Tracking</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Verification</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($categories as $cat): ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                  <?php echo html_escape($cat->category_name); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[13px]">
                  <?php echo html_escape($cat->code); ?>
                </td>
                <td class="px-4 py-3 text-[13px] text-on-surface-variant max-w-[250px] truncate">
                  <?php echo html_escape($cat->description); ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?php echo $cat->is_required ? 'bg-primary-container text-on-primary-container font-bold' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <?php echo $cat->is_required ? 'Mandatory' : 'Optional'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="text-xs font-semibold <?php echo $cat->expiry_required ? 'text-amber-600' : 'text-on-surface-variant'; ?>">
                    <?php echo $cat->expiry_required ? 'Yes' : 'No'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="text-xs font-semibold <?php echo $cat->verification_required ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <?php echo $cat->verification_required ? 'Required' : 'None'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container">
                    <?php echo html_escape($cat->status); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Category Modal Dialog -->
    <dialog id="catModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Add Document Category</h3>
          <button onclick="document.getElementById('catModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('certificates/document_categories'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category Name *</label>
              <input type="text" name="category_name" required placeholder="e.g. Migration Certificate" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category Code *</label>
              <input type="text" name="code" required placeholder="MIGRATION_CERT" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md uppercase font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
              <textarea name="description" rows="2" placeholder="Usage criteria..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="space-y-2 pt-2 border-t border-outline-variant/40">
              <label class="flex items-center gap-2 cursor-pointer text-xs text-on-surface font-medium">
                <input type="checkbox" name="is_required" value="1" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span>Mandatory document for all student admissions</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs text-on-surface font-medium">
                <input type="checkbox" name="expiry_required" value="1" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span>Requires document expiration tracking</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs text-on-surface font-medium">
                <input type="checkbox" name="verification_required" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span>Requires verification by authorized staff</span>
              </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('catModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Category</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
