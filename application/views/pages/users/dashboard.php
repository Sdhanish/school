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
        <h2 class="font-headline-md text-headline-md text-on-surface">User & Permission Management Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Centralized Role-Based Access Control (RBAC), multi-role accounts, permissions, and security monitoring.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('users/roles'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>Roles & Permissions
        </a>
        <a href="<?php echo site_url('users/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">person_add</span>Add User
        </a>
      </div>
    </div>

    <!-- Top KPI Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <!-- Total Users -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Users</span>
          <span class="material-symbols-outlined text-primary text-[22px]">group</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_users; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->active_users; ?> Active</span> • <?php echo $stats->inactive_users; ?> Inactive
        </div>
      </div>

      <!-- Locked / Suspended -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Security Lockouts</span>
          <span class="material-symbols-outlined text-error text-[22px]">lock_clock</span>
        </div>
        <div class="text-headline-md font-bold <?php echo ($stats->locked_users > 0) ? 'text-error' : 'text-on-surface'; ?>"><?php echo $stats->locked_users; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span><?php echo $stats->suspended_users; ?> Suspended accounts</span>
        </div>
      </div>

      <!-- Teachers & Staff -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Faculty & Staff</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">badge</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo $stats->teacher_users + $stats->staff_users; ?></div>
        <div class="text-xs text-on-surface-variant">
          <?php echo $stats->teacher_users; ?> Teachers • <?php echo $stats->staff_users; ?> Admin/Staff
        </div>
      </div>

      <!-- Parents & Students -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Portal Users</span>
          <span class="material-symbols-outlined text-primary text-[22px]">family_restroom</span>
        </div>
        <div class="text-headline-md font-bold text-primary"><?php echo $stats->parent_users + $stats->student_users; ?></div>
        <div class="text-xs text-on-surface-variant">
          <?php echo $stats->parent_users; ?> Parents • <?php echo $stats->student_users; ?> Students
        </div>
      </div>
    </div>

    <!-- 2 Column Layout: Role Breakdown Table + Recent Login Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Role Summary Breakdown -->
      <div class="lg:col-span-2 elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">shield_person</span>Role Distribution & Active Access
          </h3>
          <a href="<?php echo site_url('users/roles'); ?>" class="text-xs text-primary font-semibold hover:underline">Manage All Roles</a>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Role Name</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Classification</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Total Users</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Active</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Inactive</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Permissions</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($roles_summary as $r): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($r->role_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($r->role_code); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-surface-container-high text-primary"><?php echo $r->user_type; ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-on-surface">
                    <?php echo $r->total_users; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-secondary font-semibold">
                    <?php echo $r->active_users; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-on-surface-variant">
                    <?php echo $r->inactive_users; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container font-mono">
                      <?php echo $r->permission_count; ?> Perms
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('users/role_permissions/' . $r->role_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-secondary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">tune</span>Matrix
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right 1 Col: Recent Login Activity -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">history</span>Recent Logins
          </h3>
          <a href="<?php echo site_url('users/login_activity'); ?>" class="text-xs text-primary font-semibold hover:underline">All Logs</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($recent_activity)): ?>
            <div class="py-6 text-center text-on-surface-variant text-xs">No recent login activities.</div>
          <?php else: ?>
            <?php foreach ($recent_activity as $act): ?>
              <?php
                $stBadge = ($act->status === 'Successful') ? 'text-secondary' : (($act->status === 'Failed') ? 'text-amber-600' : 'text-error font-bold');
              ?>
              <div class="py-3 space-y-1">
                <div class="flex items-center justify-between">
                  <strong class="text-body-md text-on-surface"><?php echo html_escape($act->username); ?></strong>
                  <span class="text-[11px] font-bold <?php echo $stBadge; ?>"><?php echo $act->status; ?></span>
                </div>
                <div class="text-[11px] text-on-surface-variant font-mono flex items-center justify-between">
                  <span>IP: <?php echo html_escape($act->ip_address ?: '—'); ?></span>
                  <span><?php echo date('d M, h:i A', strtotime($act->created_at)); ?></span>
                </div>
                <?php if ($act->failure_reason): ?>
                  <p class="text-[10px] text-error line-clamp-1 italic"><?php echo html_escape($act->failure_reason); ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
