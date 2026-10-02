<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Edit User: <?php echo html_escape($user->name); ?></h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Update profile information and role assignment.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('users/details/' . $user->user_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Profile
        </a>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6 max-w-2xl mb-6">
      <?php echo form_open('users/edit/' . $user->user_id); ?>
        <div class="space-y-4">
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Full Name *</label>
            <input type="text" name="name" value="<?php echo html_escape($user->name); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Username (Fixed)</label>
            <input type="text" value="<?php echo html_escape($user->username); ?>" disabled class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-body-md font-mono text-on-surface-variant"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Phone Number</label>
            <input type="tel" name="phone" value="<?php echo html_escape($user->phone); ?>" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assigned Designation *</label>
              <select name="designation_id" id="selDesignationId" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php if (!empty($designations)): ?>
                  <?php foreach ($designations as $d): ?>
                    <?php $d_id = $d->designation_id ?? $d->id; ?>
                    <option value="<?php echo $d_id; ?>" <?php echo ((int)$user->designation_id === (int)$d_id) ? 'selected' : ''; ?>>
                      <?php echo html_escape($d->designation_name . ' (' . $d->designation_code . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <?php foreach ($roles as $r): ?>
                    <option value="<?php echo $r->role_id; ?>" <?php echo ($user->role_id == $r->role_id) ? 'selected' : ''; ?>><?php echo html_escape($r->role_name); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <input type="hidden" name="role_id" id="hiddenRoleId" value="<?php echo (int)($user->role_id ?: 1); ?>">
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">User Type *</label>
              <select name="user_type" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Admin" <?php echo ($user->user_type === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Principal" <?php echo ($user->user_type === 'Principal') ? 'selected' : ''; ?>>Principal</option>
                <option value="Teacher" <?php echo ($user->user_type === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="Accountant" <?php echo ($user->user_type === 'Accountant') ? 'selected' : ''; ?>>Accountant</option>
                <option value="Transport Manager" <?php echo ($user->user_type === 'Transport Manager') ? 'selected' : ''; ?>>Transport Manager</option>
                <option value="Receptionist" <?php echo ($user->user_type === 'Receptionist') ? 'selected' : ''; ?>>Receptionist</option>
                <option value="Parent" <?php echo ($user->user_type === 'Parent') ? 'selected' : ''; ?>>Parent</option>
                <option value="Student" <?php echo ($user->user_type === 'Student') ? 'selected' : ''; ?>>Student</option>
                <option value="Staff" <?php echo ($user->user_type === 'Staff') ? 'selected' : ''; ?>>Staff</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/50">
            <a href="<?php echo site_url('users/details/' . $user->user_id); ?>" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Changes</button>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>
