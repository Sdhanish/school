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
        <h2 class="font-headline-md text-headline-md text-on-surface">Communication Groups</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage target recipient lists and broadcast cohorts (Teachers, Parents, Management).</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('groupModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">group_add</span>Create Group
        </button>
      </div>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <?php if (empty($groups)): ?>
        <div class="col-span-3 p-8 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 text-center text-on-surface-variant text-body-md">
          No communication groups created yet. Click 'Create Group' above.
        </div>
      <?php else: ?>
        <?php foreach ($groups as $g): ?>
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between space-y-4 hover:border-primary transition-all">
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant"><?php echo html_escape($g->group_type); ?></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $g->status ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                  <?php echo $g->status ? 'Active' : 'Inactive'; ?>
                </span>
              </div>
              <h3 class="font-headline-md text-title-md font-bold text-on-surface"><?php echo html_escape($g->group_name); ?></h3>
              <p class="text-body-md text-on-surface-variant text-[13px] line-clamp-2">
                <?php echo html_escape($g->description ?: 'No description provided.'); ?>
              </p>
            </div>

            <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between">
              <span class="text-xs font-semibold text-primary">Pre-configured Cohort</span>
              <div>
                <?php echo form_open('communication/groups', ['class' => 'inline']); ?>
                  <input type="hidden" name="group_id" value="<?php echo $g->group_id; ?>"/>
                  <input type="hidden" name="action" value="delete"/>
                  <button type="submit" onclick="return confirm('Delete this group?');" class="p-1 rounded hover:bg-error-container text-error cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Create Group Modal Dialog -->
    <dialog id="groupModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-md backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Create Communication Group</h3>
          <button onclick="document.getElementById('groupModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('communication/groups'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Group Name *</label>
              <input type="text" name="group_name" required placeholder="e.g. Grade 10 Science Faculty" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Group Type *</label>
              <select name="group_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Teachers">Teachers</option>
                <option value="Parents">Parents</option>
                <option value="Management">Management</option>
                <option value="Custom">Custom Group</option>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
              <textarea name="description" rows="3" placeholder="Brief group purpose..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer pt-1">
              <input type="checkbox" name="status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
              <span class="text-body-md text-on-surface">Active Group</span>
            </label>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('groupModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Group</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
