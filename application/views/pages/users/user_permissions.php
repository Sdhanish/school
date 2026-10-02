<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface">User Permission Overrides: <?php echo html_escape($user->name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            Role: <?php echo html_escape($user->role_name); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure individual user-level custom grants or revokes over default role permissions.</p>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('users/details/' . $user->user_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Profile
        </a>
      </div>
    </div>

    <!-- Explanatory Banner -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="flex items-center gap-3 text-xs text-on-surface-variant">
        <span class="material-symbols-outlined text-primary text-[22px]">info</span>
        <span>
          <strong>Role-Based Access:</strong> By default, this user inherits all permissions of the <strong><?php echo html_escape($user->role_name); ?></strong> role. You can explicitly <strong>Grant</strong> an extra permission or <strong>Revoke</strong> a role permission below.
        </span>
      </div>
    </div>

    <!-- Permissions Override Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">All Permissions & User Overrides</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Module</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Permission</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Key</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Role Default</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">User Override</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Effective Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($all_permissions as $p): ?>
              <?php
                $is_in_role = in_array((int)$p->permission_id, $role_perm_ids, true);
                $override = $override_map[$p->permission_id] ?? NULL;
                
                $effective = $is_in_role;
                if ($override === 'Grant') $effective = true;
                if ($override === 'Revoke') $effective = false;
              ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface"><?php echo html_escape($p->module); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-on-surface text-xs font-semibold"><?php echo html_escape($p->permission_name); ?></td>
                <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px] text-primary font-bold"><?php echo html_escape($p->permission_key); ?></td>
                <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs">
                  <?php if ($is_in_role): ?>
                    <span class="text-secondary font-bold">Enabled</span>
                  <?php else: ?>
                    <span class="text-on-surface-variant">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs">
                  <?php if ($override === 'Grant'): ?>
                    <span class="px-2 py-0.5 rounded font-bold bg-secondary-container text-on-secondary-container">Granted (+)</span>
                  <?php elseif ($override === 'Revoke'): ?>
                    <span class="px-2 py-0.5 rounded font-bold bg-error-container text-error">Revoked (-)</span>
                  <?php else: ?>
                    <span class="text-on-surface-variant">Inherit Role</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $effective ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <?php echo $effective ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1">
                    <?php echo form_open('users/user_permissions/' . $user->user_id, ['class' => 'inline']); ?>
                      <input type="hidden" name="permission_id" value="<?php echo $p->permission_id; ?>"/>
                      <input type="hidden" name="action" value="set"/>
                      <?php if (!$effective): ?>
                        <input type="hidden" name="override_type" value="Grant"/>
                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-bold bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant cursor-pointer">Grant</button>
                      <?php else: ?>
                        <input type="hidden" name="override_type" value="Revoke"/>
                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-bold bg-error-container text-error hover:bg-error/20 cursor-pointer">Revoke</button>
                      <?php endif; ?>
                    <?php echo form_close(); ?>

                    <?php if ($override): ?>
                      <?php echo form_open('users/user_permissions/' . $user->user_id, ['class' => 'inline']); ?>
                        <input type="hidden" name="permission_id" value="<?php echo $p->permission_id; ?>"/>
                        <input type="hidden" name="action" value="remove"/>
                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] border border-outline-variant text-on-surface-variant hover:bg-surface-container-high cursor-pointer" title="Reset to Role Default">Reset</button>
                      <?php echo form_close(); ?>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
