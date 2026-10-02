<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Teacher Accounts</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Teaching faculty accounts mapped to staff profiles.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('users/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">person_add</span>Add Teacher User
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Registered Teacher Accounts (<?php echo count($users); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Faculty Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Username</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Staff Link</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Email & Phone</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($users)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No teacher accounts found.</td></tr>
            <?php else: ?>
              <?php foreach ($users as $u): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface"><?php echo html_escape($u->name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]"><?php echo html_escape($u->username); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-xs text-on-surface"><?php echo html_escape($u->staff_name ?: 'Direct Account'); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-xs font-mono"><?php echo html_escape($u->email); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo ($u->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>"><?php echo html_escape($u->status); ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('users/details/' . $u->user_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>Profile
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
