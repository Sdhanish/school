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
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($user->name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container font-mono">
            <?php echo html_escape($user->role_name); ?>
          </span>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($user->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo html_escape($user->status); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1 font-mono">Username: @<?php echo html_escape($user->username); ?> • Type: <?php echo html_escape($user->user_type); ?></p>
      </div>

      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('users/list'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>All Users
        </a>
        <a href="<?php echo site_url('users/edit/' . $user->user_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-secondary bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">edit</span>Edit Profile
        </a>
        <button onclick="document.getElementById('resetPwdModal').showModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">lock_reset</span>Reset Password
        </button>
        <a href="<?php echo site_url('users/user_permissions/' . $user->user_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">tune</span>User Permissions
        </a>
        <a href="<?php echo site_url('users/delete/' . $user->user_id); ?>" onclick="return confirm('Are you sure you want to delete user <?php echo html_escape($user->username); ?>?');" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-error/30 text-error bg-surface-container-lowest text-label-md hover:bg-error-container transition-colors">
          <span class="material-symbols-outlined text-[18px]">delete</span>Delete
        </a>
      </div>
    </div>

    <!-- 2 Column Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 1 Col: Account Profile Information -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50">
            Account Details
          </h3>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Full Name:</span>
              <strong class="text-body-md text-on-surface block"><?php echo html_escape($user->name); ?></strong>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Designation:</span>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="material-symbols-outlined text-[16px] text-primary">badge</span>
                <strong class="text-body-md text-on-surface"><?php echo html_escape(!empty($user->designation_name) ? $user->designation_name : $user->role_name); ?></strong>
                <?php if (!empty($user->designation_code)): ?>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-primary-container text-on-primary-container"><?php echo html_escape($user->designation_code); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Username:</span>
              <span class="text-body-md font-mono text-primary font-bold block"><?php echo html_escape($user->username); ?></span>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Email:</span>
              <span class="text-on-surface font-mono block"><?php echo html_escape($user->email); ?></span>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Phone:</span>
              <span class="text-on-surface font-mono block"><?php echo html_escape($user->phone ?: 'N/A'); ?></span>
            </div>

            <?php if (!empty($user->staff_name)): ?>
              <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant/30">
                <span class="text-primary font-bold block mb-0.5">Linked Staff Record:</span>
                <strong><?php echo html_escape($user->staff_name); ?></strong> (<?php echo html_escape(!empty($user->employee_code) ? $user->employee_code : 'N/A'); ?>)
              </div>
            <?php endif; ?>

            <?php if (!empty($user->student_first_name)): ?>
              <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant/30">
                <span class="text-secondary font-bold block mb-0.5">Linked Student Record:</span>
                <strong><?php echo html_escape(trim(($user->student_first_name ?? '') . ' ' . ($user->student_last_name ?? ''))); ?></strong> (<?php echo html_escape(!empty($user->admission_number) ? $user->admission_number : 'N/A'); ?>)
              </div>
            <?php endif; ?>

            <div class="pt-2 border-t border-outline-variant/40 space-y-2 font-mono">
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Created:</span>
                <span><?php echo !empty($user->created_at) ? date('d-m-Y', strtotime($user->created_at)) : 'N/A'; ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Last Login:</span>
                <span><?php 
                  $raw_last_login = !empty($user->last_login_at) ? $user->last_login_at : (!empty($user->last_login) ? $user->last_login : null);
                  $last_login_time = ($raw_last_login && $raw_last_login !== '0000-00-00 00:00:00') ? strtotime($raw_last_login) : false;
                  echo ($last_login_time && $last_login_time > 0) ? date('d-m-Y h:i A', $last_login_time) : 'Never'; 
                ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Parent Linked Children Card if Parent -->
        <?php if ($user->user_type === 'Parent' && !empty($children)): ?>
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-3">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">family_restroom</span>Linked Children (<?php echo count($children); ?>)
            </h3>
            <div class="divide-y divide-outline-variant/40 text-xs">
              <?php foreach ($children as $ch): ?>
                <div class="py-2.5 flex items-center justify-between">
                  <div>
                    <strong class="text-on-surface block"><?php echo html_escape($ch->first_name . ' ' . $ch->last_name); ?></strong>
                    <span class="text-on-surface-variant font-mono"><?php echo html_escape($ch->class_name . ' - ' . $ch->section_name); ?> (<?php echo html_escape($ch->admission_number); ?>)</span>
                  </div>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary-container text-on-primary-container"><?php echo $ch->relationship; ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right 2 Cols: Effective Permissions Matrix -->
      <div class="lg:col-span-2 p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[22px]">verified_user</span>Effective Permissions
            </h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Calculated from <strong><?php echo html_escape(!empty($user->designation_name) ? $user->designation_name : $user->role_name); ?></strong> base permissions + <?php echo count($overrides); ?> custom overrides.</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-secondary-container text-on-secondary-container">
            <?php echo count($effective_perms); ?> Active Permissions
          </span>
        </div>

        <div class="flex flex-wrap gap-1.5 pt-2">
          <?php if (empty($effective_perms)): ?>
            <div class="py-6 text-center text-on-surface-variant text-xs w-full">No active permissions assigned to this user account.</div>
          <?php else: ?>
            <?php foreach ($effective_perms as $perm): ?>
              <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-surface-container-high text-primary flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-secondary">check</span>
                <?php echo $perm; ?>
              </span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal Dialog -->
    <dialog id="resetPwdModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-md backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Reset User Password</h3>
          <button onclick="document.getElementById('resetPwdModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('users/reset_password/' . $user->user_id); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">New Password *</label>
              <input type="password" name="password" required minlength="6" placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('resetPwdModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Password</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
