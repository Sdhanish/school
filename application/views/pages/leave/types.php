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
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Types Configuration</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure student and staff leave categories, max allowable days, half-day toggles, and document requirements.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('leaveTypeModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Leave Type
        </button>
      </div>
    </div>

    <!-- Leave Types Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <?php if (empty($types)): ?>
        <div class="col-span-3 p-8 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 text-center text-on-surface-variant">
          No leave types configured. Click 'Create Leave Type' above.
        </div>
      <?php else: ?>
        <?php foreach ($types as $t): ?>
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between space-y-4 hover:border-primary transition-all">
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-primary-container text-on-primary-container font-mono"><?php echo html_escape($t->type_code); ?></span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant"><?php echo html_escape($t->applicable_to); ?></span>
              </div>
              <h3 class="font-headline-md text-title-md font-bold text-on-surface"><?php echo html_escape($t->type_name); ?></h3>
              <p class="text-body-md text-on-surface-variant text-[13px] line-clamp-2">
                <?php echo html_escape($t->description ?: 'Standard school leave quota.'); ?>
              </p>
            </div>

            <div class="space-y-2 text-xs text-on-surface-variant pt-2 border-t border-outline-variant/40">
              <div class="flex items-center justify-between">
                <span>Annual Allocation:</span>
                <strong class="text-on-surface font-bold"><?php echo (int)$t->max_days; ?> Days</strong>
              </div>
              <div class="flex items-center justify-between">
                <span>Half-Day Allowed:</span>
                <span class="<?php echo $t->allow_half_day ? 'text-secondary font-semibold' : 'text-on-surface-variant'; ?>"><?php echo $t->allow_half_day ? 'Yes' : 'No'; ?></span>
              </div>
              <div class="flex items-center justify-between">
                <span>Requires Document:</span>
                <span class="<?php echo $t->requires_document ? 'text-amber-700 font-semibold' : 'text-on-surface-variant'; ?>"><?php echo $t->requires_document ? 'Yes (Medical/Proof)' : 'No'; ?></span>
              </div>
            </div>

            <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between">
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $t->status ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                <?php echo $t->status ? 'Active' : 'Inactive'; ?>
              </span>
              <div>
                <?php echo form_open('leave/types', ['class' => 'inline']); ?>
                  <input type="hidden" name="type_id" value="<?php echo $t->type_id; ?>"/>
                  <input type="hidden" name="action" value="delete"/>
                  <button type="submit" onclick="return confirm('Delete or deactivate this leave type?');" class="p-1 rounded hover:bg-error-container text-error cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Create Leave Type Modal Dialog -->
    <dialog id="leaveTypeModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Configure Leave Type</h3>
          <button onclick="document.getElementById('leaveTypeModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('leave/types'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Leave Type Name *</label>
              <input type="text" name="type_name" required placeholder="e.g. Casual Leave" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Leave Code *</label>
                <input type="text" name="type_code" required placeholder="e.g. CL" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md uppercase font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Applicable To *</label>
                <select name="applicable_to" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Both">Both (Students & Staff)</option>
                  <option value="Students">Students Only</option>
                  <option value="Staff">Staff Only</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Annual Max Days *</label>
              <input type="number" name="max_days" min="1" max="180" value="12" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
              <textarea name="description" rows="2" placeholder="Brief leave policy..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="space-y-2 pt-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="allow_half_day" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Allow Half-Day Requests (0.5 day)</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="requires_document" value="1" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Require Supporting Document (Medical / Proof)</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="allow_carry_forward" value="1" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Allow Annual Carry Forward</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="status" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Active</span>
              </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('leaveTypeModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Leave Type</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
