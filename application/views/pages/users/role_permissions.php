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
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface">Role Permissions Matrix: <?php echo html_escape($role->role_name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo html_escape($role->role_code); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure enabled modules, actions, and security capabilities for all users assigned to this role.</p>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <?php if (!empty($roles)): ?>
          <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-on-surface-variant">Switch Role:</label>
            <select onchange="window.location.href='<?php echo site_url('users/role_permissions/'); ?>' + this.value" class="px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary cursor-pointer">
              <?php foreach ($roles as $r): ?>
                <option value="<?php echo $r->role_id; ?>" <?php echo ((int)$r->role_id === (int)$role->role_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($r->role_name); ?> (<?php echo html_escape($r->role_code); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <a href="<?php echo site_url('users/roles'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Roles
        </a>
      </div>
    </div>

    <!-- Permission Matrix Form -->
    <?php echo form_open('users/role_permissions/' . $role->role_id); ?>
      
      <!-- Top Action Bar -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
          <button type="button" onclick="selectAll(true)" class="px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container-high">Select All Permissions</button>
          <button type="button" onclick="selectAll(false)" class="px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container-high">Clear All</button>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Permission Matrix
        </button>
      </div>

      <!-- Modules Accordion / Matrix Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php foreach ($grouped_permissions as $module => $perms): ?>
          <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/50 bg-surface-container-low/50 flex items-center justify-between">
              <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">folder</span><?php echo html_escape($module); ?>
              </h3>
              <div class="flex items-center gap-2">
                <button type="button" onclick="toggleModuleGroup('<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $module); ?>', true)" class="text-[11px] text-primary font-semibold hover:underline cursor-pointer">All</button>
                <span class="text-on-surface-variant text-xs">•</span>
                <button type="button" onclick="toggleModuleGroup('<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $module); ?>', false)" class="text-[11px] text-on-surface-variant font-semibold hover:underline cursor-pointer">None</button>
              </div>
            </div>

            <div class="p-4 space-y-3">
              <?php foreach ($perms as $p): ?>
                <?php $checked = in_array((int)$p->permission_id, $active_perm_ids, true); ?>
                <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer">
                  <input type="checkbox" name="permissions[]" value="<?php echo $p->permission_id; ?>" <?php echo $checked ? 'checked' : ''; ?> class="mod-chk-<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $module); ?> w-4 h-4 rounded text-secondary focus:ring-secondary mt-0.5"/>
                  <div class="text-xs space-y-0.5">
                    <strong class="text-on-surface block"><?php echo html_escape($p->permission_name); ?></strong>
                    <span class="font-mono text-[11px] text-primary font-semibold block"><?php echo html_escape($p->permission_key); ?></span>
                    <p class="text-on-surface-variant text-[11px]"><?php echo html_escape($p->description); ?></p>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Bottom Save Action -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex items-center justify-end mb-6">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Permission Matrix
        </button>
      </div>

    <?php echo form_close(); ?>

    <script>
      function selectAll(check) {
        document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(cb => cb.checked = check);
      }
      function toggleModuleGroup(modClass, check) {
        document.querySelectorAll('.mod-chk-' + modClass).forEach(cb => cb.checked = check);
      }
    </script>
