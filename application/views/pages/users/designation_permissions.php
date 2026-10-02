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
        <div class="flex items-center gap-2 flex-wrap">
          <h2 class="font-headline-md text-headline-md text-on-surface">Permissions Matrix: <?php echo html_escape($designation->designation_name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo html_escape($designation->designation_code); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Configure module, submodule, and operational permissions. Changes immediately cascade to all users with this designation.
        </p>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <?php if (!empty($designations)): ?>
          <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-on-surface-variant">Switch Designation:</label>
            <select onchange="window.location.href='<?php echo site_url('users/designation_permissions/'); ?>' + this.value" class="px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary cursor-pointer">
              <?php foreach ($designations as $d): ?>
                <?php $d_id = $d->designation_id ?? $d->id; ?>
                <option value="<?php echo $d_id; ?>" <?php echo ((int)$d_id === (int)($designation->designation_id ?? $designation->id)) ? 'selected' : ''; ?>>
                  <?php echo html_escape($d->designation_name); ?> (<?php echo html_escape($d->designation_code); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <a href="<?php echo site_url('users/designations'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Designations
        </a>
      </div>
    </div>

    <!-- Permission Matrix Form -->
    <?php echo form_open('users/designation_permissions/' . ($designation->designation_id ?? $designation->id), ['id' => 'designationPermForm']); ?>
      
      <!-- Top Action & Search Bar -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="flex items-center gap-2">
            <button type="button" onclick="selectAll(true)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container-high cursor-pointer">
              <span class="material-symbols-outlined text-[15px]">select_all</span>Select All
            </button>
            <button type="button" onclick="selectAll(false)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold hover:bg-surface-container-high cursor-pointer">
              <span class="material-symbols-outlined text-[15px]">deselect</span>Clear All
            </button>
          </div>

          <div class="relative">
            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-on-surface-variant">search</span>
            <input type="text" id="permFilterInput" onkeyup="filterPermissions(this.value)" placeholder="Filter permissions..." class="pl-8 pr-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low/50 text-xs text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary/20 focus:border-primary w-56" />
          </div>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Permission Matrix
        </button>
      </div>

      <!-- Modules Hierarchical Matrix Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if (!empty($permission_tree)): ?>
          <?php foreach ($permission_tree as $mod): 
            $modId = (int)$mod['id'];
            $allPermIds = !empty($mod['all_permission_ids']) ? implode(',', $mod['all_permission_ids']) : '';
          ?>
            <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden perm-mod-card" data-mod-name="<?php echo strtolower(html_escape($mod['name'])); ?>">
              <!-- Module Header -->
              <div class="p-4 border-b border-outline-variant/50 bg-surface-container-low/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <input type="checkbox" 
                         id="mod_parent_<?php echo $modId; ?>" 
                         class="perm-parent-chk w-4 h-4 rounded text-secondary focus:ring-secondary cursor-pointer" 
                         data-mod-id="<?php echo $modId; ?>"
                         onchange="onModuleParentToggle(<?php echo $modId; ?>, this.checked)" />
                  <span class="material-symbols-outlined text-primary text-[20px]"><?php echo html_escape($mod['icon'] ?: 'folder'); ?></span>
                  <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
                    <?php echo html_escape($mod['name']); ?>
                    <?php if (!empty($mod['is_soon']) || (!empty($mod['badge']) && strtolower($mod['badge']) === 'soon')): ?>
                      <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase tracking-wider">SOON</span>
                    <?php elseif (!empty($mod['badge'])): ?>
                      <span class="px-1.5 py-0.5 text-[9px] rounded-full bg-surface-container-high text-on-surface-variant font-medium"><?php echo html_escape($mod['badge']); ?></span>
                    <?php endif; ?>
                  </h3>
                </div>
                <div class="flex items-center gap-2 text-xs">
                  <button type="button" onclick="toggleModuleCheckboxes(<?php echo $modId; ?>, true)" class="text-primary font-semibold hover:underline cursor-pointer">All</button>
                  <span class="text-on-surface-variant">•</span>
                  <button type="button" onclick="toggleModuleCheckboxes(<?php echo $modId; ?>, false)" class="text-on-surface-variant hover:underline cursor-pointer">None</button>
                </div>
              </div>

              <!-- Module Body: Direct permissions + Subgroups -->
              <div class="p-4 space-y-3">
                <!-- Direct Module Permissions -->
                <?php if (!empty($mod['direct_permissions'])): ?>
                  <div class="space-y-1.5">
                    <?php foreach ($mod['direct_permissions'] as $dp): 
                      $pid = (int)$dp['permission_id'];
                      $checked = in_array($pid, $active_perm_ids, true);
                    ?>
                      <label class="flex items-start gap-2.5 p-1.5 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer perm-item-label" data-perm-text="<?php echo strtolower(html_escape($dp['permission_name'] . ' ' . $dp['permission_key'])); ?>">
                        <input type="checkbox" 
                               name="permissions[]" 
                               value="<?php echo $pid; ?>" 
                               <?php echo $checked ? 'checked' : ''; ?>
                               class="perm-chk perm-mod-<?php echo $modId; ?> w-4 h-4 rounded text-secondary focus:ring-secondary mt-0.5 cursor-pointer" 
                               data-mod-id="<?php echo $modId; ?>"
                               onchange="updateParentCheckboxState(<?php echo $modId; ?>)" />
                        <div class="text-xs space-y-0.5">
                          <strong class="text-on-surface block"><?php echo html_escape($dp['permission_name']); ?></strong>
                          <span class="font-mono text-[10px] text-primary block"><?php echo html_escape($dp['permission_key']); ?></span>
                        </div>
                      </label>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <!-- Submodules / Subgroups -->
                <?php if (!empty($mod['groups'])): ?>
                  <?php foreach ($mod['groups'] as $group): ?>
                    <?php if ($group['type'] === 'submenu'): ?>
                      <!-- Direct Submenu -->
                      <?php if (!empty($group['permissions'])): ?>
                        <div class="space-y-1.5">
                          <?php foreach ($group['permissions'] as $p): 
                            $pid = (int)$p['permission_id'];
                            $checked = in_array($pid, $active_perm_ids, true);
                            $label = $p['permission_name'];
                            if ($group['name'] !== $label && strpos($label, $group['name']) === false && strpos($group['name'], $label) === false) {
                              $label = $group['name'] . ' — ' . $label;
                            }
                          ?>
                            <label class="flex items-start gap-2.5 p-1.5 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer perm-item-label" data-perm-text="<?php echo strtolower(html_escape($label . ' ' . $p['permission_key'])); ?>">
                              <input type="checkbox" 
                                     name="permissions[]" 
                                     value="<?php echo $pid; ?>" 
                                     <?php echo $checked ? 'checked' : ''; ?>
                                     class="perm-chk perm-mod-<?php echo $modId; ?> w-4 h-4 rounded text-secondary focus:ring-secondary mt-0.5 cursor-pointer" 
                                     data-mod-id="<?php echo $modId; ?>"
                                     onchange="updateParentCheckboxState(<?php echo $modId; ?>)" />
                              <div class="text-xs space-y-0.5">
                                <strong class="text-on-surface block"><?php echo html_escape($label); ?></strong>
                                <span class="font-mono text-[10px] text-primary block"><?php echo html_escape($p['permission_key']); ?></span>
                              </div>
                            </label>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                    <?php elseif ($group['type'] === 'group'): ?>
                      <!-- Dropdown Subgroup -->
                      <div class="mt-3 pt-2.5 border-t border-outline-variant/30">
                        <div class="flex items-center gap-1 pb-1.5 px-1 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                          <span class="material-symbols-outlined text-[14px] text-tertiary">folder_open</span>
                          <span><?php echo html_escape($group['name']); ?></span>
                        </div>
                        <div class="pl-2 space-y-1.5 border-l-2 border-outline-variant/40 ml-1">
                          <?php foreach ($group['items'] as $subItem): ?>
                            <?php if (!empty($subItem['permissions'])): ?>
                              <?php foreach ($subItem['permissions'] as $p): 
                                $pid = (int)$p['permission_id'];
                                $checked = in_array($pid, $active_perm_ids, true);
                                $label = $p['permission_name'];
                                if ($subItem['name'] !== $label && strpos($label, $subItem['name']) === false && strpos($subItem['name'], $label) === false) {
                                  $label = $subItem['name'] . ' — ' . $label;
                                }
                              ?>
                                <label class="flex items-start gap-2.5 p-1.5 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer perm-item-label" data-perm-text="<?php echo strtolower(html_escape($label . ' ' . $p['permission_key'])); ?>">
                                  <input type="checkbox" 
                                         name="permissions[]" 
                                         value="<?php echo $pid; ?>" 
                                         <?php echo $checked ? 'checked' : ''; ?>
                                         class="perm-chk perm-mod-<?php echo $modId; ?> w-4 h-4 rounded text-secondary focus:ring-secondary mt-0.5 cursor-pointer" 
                                         data-mod-id="<?php echo $modId; ?>"
                                         onchange="updateParentCheckboxState(<?php echo $modId; ?>)" />
                                  <div class="text-xs space-y-0.5">
                                    <strong class="text-on-surface block"><?php echo html_escape($label); ?></strong>
                                    <span class="font-mono text-[10px] text-primary block"><?php echo html_escape($p['permission_key']); ?></span>
                                  </div>
                                </label>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-2 text-center py-12 text-on-surface-variant">
            No permissions configured in the catalog.
          </div>
        <?php endif; ?>
      </div>

      <!-- Bottom Save Action Bar -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 flex items-center justify-between flex-wrap gap-4 mb-8">
        <span class="text-xs text-on-surface-variant">
          Saving updates permissions immediately across all users holding the <strong><?php echo html_escape($designation->designation_name); ?></strong> designation.
        </span>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Permission Matrix
        </button>
      </div>

    <?php echo form_close(); ?>

    <script>
      function selectAll(check) {
        document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(cb => {
          cb.checked = check;
        });
        document.querySelectorAll('.perm-parent-chk').forEach(parentCb => {
          parentCb.checked = check;
          parentCb.indeterminate = false;
        });
      }

      function onModuleParentToggle(modId, checked) {
        document.querySelectorAll('.perm-mod-' + modId).forEach(cb => {
          cb.checked = checked;
        });
        const parent = document.getElementById('mod_parent_' + modId);
        if (parent) parent.indeterminate = false;
      }

      function toggleModuleCheckboxes(modId, check) {
        document.querySelectorAll('.perm-mod-' + modId).forEach(cb => {
          cb.checked = check;
        });
        updateParentCheckboxState(modId);
      }

      function updateParentCheckboxState(modId) {
        const children = document.querySelectorAll('.perm-mod-' + modId);
        const parent = document.getElementById('mod_parent_' + modId);
        if (!parent || children.length === 0) return;

        let checkedCount = 0;
        children.forEach(cb => {
          if (cb.checked) checkedCount++;
        });

        if (checkedCount === children.length) {
          parent.checked = true;
          parent.indeterminate = false;
        } else if (checkedCount === 0) {
          parent.checked = false;
          parent.indeterminate = false;
        } else {
          parent.checked = false;
          parent.indeterminate = true;
        }
      }

      function filterPermissions(query) {
        const q = query.trim().toLowerCase();
        document.querySelectorAll('.perm-mod-card').forEach(card => {
          const modName = card.getAttribute('data-mod-name') || '';
          let matchAny = false;
          card.querySelectorAll('.perm-item-label').forEach(item => {
            const text = item.getAttribute('data-perm-text') || '';
            if (q === '' || text.includes(q) || modName.includes(q)) {
              item.style.display = '';
              matchAny = true;
            } else {
              item.style.display = 'none';
            }
          });
          if (q === '' || matchAny || modName.includes(q)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      }

      // Initialize parent checkbox state on load
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.perm-parent-chk').forEach(parentCb => {
          const modId = parentCb.getAttribute('data-mod-id');
          if (modId) {
            updateParentCheckboxState(modId);
          }
        });
      });
    </script>
