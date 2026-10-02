<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Permissions Catalog</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Full registry of granular permissions across all 16 Login2 Management modules.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('users/roles'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>Roles
        </a>
      </div>
    </div>

    <!-- Grouped Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <?php foreach ($grouped_permissions as $module => $perms): ?>
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden flex flex-col justify-between">
          <div class="p-4 border-b border-outline-variant/50 bg-surface-container-low/50 flex items-center justify-between">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">folder</span><?php echo html_escape($module); ?>
            </h3>
            <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
              <?php echo count($perms); ?> Actions
            </span>
          </div>

          <div class="p-4 space-y-2.5 divide-y divide-outline-variant/40 flex-1">
            <?php foreach ($perms as $p): ?>
              <div class="pt-2 first:pt-0 space-y-0.5">
                <div class="flex items-center justify-between">
                  <strong class="text-xs text-on-surface"><?php echo html_escape($p->permission_name); ?></strong>
                  <span class="font-mono text-[11px] text-primary font-bold"><?php echo html_escape($p->permission_key); ?></span>
                </div>
                <p class="text-[11px] text-on-surface-variant line-clamp-2"><?php echo html_escape($p->description ?: 'Module action access control'); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
