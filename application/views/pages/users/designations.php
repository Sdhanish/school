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

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface">Designation Management</h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container">
            RBAC
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          School-isolated designations with hierarchical permission matrix and role-based access control.
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('users/list'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">group</span>All Users
        </a>
        <button onclick="document.getElementById('createDesignationModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Custom Designation
        </button>
      </div>
    </div>

    <!-- Designations Table Card -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Designations (<?php echo count($designations); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Designation Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Code</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Active Users</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Permissions</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($designations as $d): ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">badge</span>
                    <span><?php echo html_escape($d->designation_name); ?></span>
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">
                  <?php echo html_escape($d->designation_code); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface">
                    <?php echo html_escape($d->category ?: 'Other'); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface-variant text-body-sm max-w-xs truncate">
                  <?php echo html_escape($d->description ?: '—'); ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-on-surface">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs <?php echo ($d->total_users > 0) ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'bg-surface-container text-on-surface-variant'; ?>">
                    <span class="material-symbols-outlined text-[14px]">person</span>
                    <?php echo (int)$d->total_users; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container font-mono">
                    <?php echo (int)$d->permission_count; ?> Perms
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <?php if ((int)$d->status === 1): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-secondary-container text-on-secondary-container">Active</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-container-high text-on-surface-variant">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <a href="<?php echo site_url('users/designation_permissions/' . $d->designation_id); ?>" class="px-3 py-1 rounded-lg bg-secondary text-on-secondary text-xs font-semibold hover:bg-on-secondary-fixed-variant transition-colors inline-flex items-center gap-1 shadow-xs cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]">tune</span>Edit Permissions
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Custom Designation Modal Dialog -->
    <dialog id="createDesignationModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">badge</span>
            Create Custom Designation
          </h3>
          <button onclick="document.getElementById('createDesignationModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('users/add_designation'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Designation Name *</label>
              <input type="text" name="designation_name" required placeholder="e.g. Senior Academic Coordinator" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category</label>
              <select name="category" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Teaching">Teaching</option>
                <option value="Administration">Administration</option>
                <option value="Finance">Finance</option>
                <option value="Support">Support</option>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
              <textarea name="description" rows="3" placeholder="Defines responsibilities and duties of this designation..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('createDesignationModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md cursor-pointer">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">Create Designation</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
