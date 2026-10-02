<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reusable School Admin Permission Matrix Component
 *
 * Module -> Sub Module -> Operations (Create, View, Edit, Delete, Other)
 * Database-driven hierarchy with expand/collapse accordions and tri-state checkboxes.
 *
 * Used by both:
 *  - Register New School Campus (Create)
 *  - Edit School Campus (Edit)
 */
$modules_count = !empty($active_modules_count) ? $active_modules_count : (!empty($matrix_tree) ? count($matrix_tree) : 0);

if (!function_exists('render_perm_matrix_cell')) {
    function render_perm_matrix_cell($op_perm, $modId, $subId) {
        if (empty($op_perm)) {
            return '<td class="text-center px-2 py-2.5 text-on-surface-variant/40 font-mono select-none">—</td>';
        }
        $pid   = (int)$op_perm['permission_id'];
        $pname = html_escape($op_perm['permission_name']);
        $pkey  = html_escape($op_perm['permission_key']);
        return '<td class="text-center px-2 py-2.5">
            <label class="inline-flex items-center justify-center cursor-pointer p-0.5 rounded hover:bg-surface-container-high transition" title="' . $pname . ' (' . $pkey . ')">
                <input type="checkbox"
                       name="permissions[]"
                       value="' . $pid . '"
                       checked
                       class="perm-chk perm-mod-' . $modId . ' perm-submod-' . $subId . ' w-4 h-4 rounded text-secondary focus:ring-secondary cursor-pointer"
                       data-mod-id="' . $modId . '"
                       data-submod-id="' . $subId . '"
                       data-perm-key="' . $pkey . '"
                       onchange="onMatrixChildPermChange(' . $modId . ', ' . $subId . ')" />
            </label>
        </td>';
    }
}

if (!function_exists('render_perm_matrix_other_cell')) {
    function render_perm_matrix_other_cell($other_perms, $modId, $subId) {
        if (empty($other_perms)) {
            return '<td class="text-center px-2 py-2.5 text-on-surface-variant/40 font-mono select-none">—</td>';
        }
        $html = '<td class="text-center px-2 py-2.5"><div class="flex items-center justify-center gap-1.5 flex-wrap">';
        foreach ($other_perms as $p) {
            $pid   = (int)$p['permission_id'];
            $pname = html_escape($p['permission_name']);
            $pkey  = html_escape($p['permission_key']);

            $shortLabel = $pname;
            if (strpos($pkey, '.') !== false) {
                $parts = explode('.', $pkey);
                $actionPart = end($parts);
                $shortLabel = ucfirst(str_replace('_', ' ', $actionPart));
            }

            $html .= '<label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-surface-container-high/70 hover:bg-surface-container-high text-[10px] text-on-surface font-medium cursor-pointer transition" title="' . $pname . ' (' . $pkey . ')">
                <input type="checkbox"
                       name="permissions[]"
                       value="' . $pid . '"
                       checked
                       class="perm-chk perm-mod-' . $modId . ' perm-submod-' . $subId . ' w-3.5 h-3.5 rounded text-secondary focus:ring-secondary cursor-pointer"
                       data-mod-id="' . $modId . '"
                       data-submod-id="' . $subId . '"
                       data-perm-key="' . $pkey . '"
                       onchange="onMatrixChildPermChange(' . $modId . ', ' . $subId . ')" />
                <span>' . html_escape($shortLabel) . '</span>
            </label>';
        }
        $html .= '</div></td>';
        return $html;
    }
}
?>

<div class="space-y-4 pt-2">
  <!-- Section Header & Global Actions -->
  <div class="flex items-center justify-between pb-2 border-b border-outline-variant/50 flex-wrap gap-2">
    <div class="flex items-center gap-2">
      <span class="w-6 h-6 rounded-full bg-tertiary text-on-tertiary text-xs font-bold flex items-center justify-center">3</span>
      <div>
        <h4 class="font-headline-md text-title-md font-bold text-on-surface">School Admin Permissions Matrix</h4>
        <p class="text-[11px] text-on-surface-variant">Hierarchical module &rarr; sub-module &rarr; operation authorization matrix.</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button type="button" onclick="selectAllPerms(true); if(document.getElementById('perm_mode_all')) document.getElementById('perm_mode_all').checked = true;" class="text-[11px] text-primary font-semibold hover:underline cursor-pointer flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px]">select_all</span> Select All
      </button>
      <span class="text-outline text-xs">•</span>
      <button type="button" onclick="selectAllPerms(false); ensureCustomMode();" class="text-[11px] text-on-surface-variant font-semibold hover:underline cursor-pointer flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px]">deselect</span> Clear All
      </button>
    </div>
</div>
  <!-- Preset Selection Radio -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <label class="flex items-start gap-3 p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/60 hover:bg-surface-container-low cursor-pointer transition">
      <input type="radio" id="perm_mode_all" name="permission_mode" value="all" checked onchange="togglePermMode('all')" class="mt-0.5 text-secondary focus:ring-secondary cursor-pointer" />
      <div>
        <div class="text-body-md font-bold text-on-surface">Full Campus Administrator (Recommended)</div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">
          Grants complete operational rights across all <span id="active_modules_count_display" class="font-semibold text-primary"><?php echo (int)$modules_count; ?></span> school modules.
        </div>
      </div>
    </label>
    <label class="flex items-start gap-3 p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/60 hover:bg-surface-container-low cursor-pointer transition">
      <input type="radio" id="perm_mode_custom" name="permission_mode" value="custom" onchange="togglePermMode('custom')" class="mt-0.5 text-secondary focus:ring-secondary cursor-pointer" />
      <div>
        <div class="text-body-md font-bold text-on-surface">Custom Selected Permissions</div>
        <div class="text-[11px] text-on-surface-variant mt-0.5">Select specific granular permissions for this campus administrator.</div>
      </div>
    </label>
  </div>

  <!-- Permission Matrix Dynamic Accordions Container -->
  <div id="permissionMatrixContainer" class="p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 space-y-3 max-h-[460px] overflow-y-auto">
    <?php if (!empty($matrix_tree)): ?>
      <?php foreach ($matrix_tree as $mod): 
        $modId = (int)$mod['id'];
        $isSoon = !empty($mod['is_soon']) || (!empty($mod['badge']) && strtolower($mod['badge']) === 'soon');
      ?>
        <div class="perm-mod-group rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-xs overflow-hidden perm-module-accordion" id="mod_accordion_<?php echo $modId; ?>">
          <!-- Main Module Accordion Header -->
          <div class="perm-mod-header flex items-center justify-between p-3 bg-surface-container-low/50 hover:bg-surface-container-low transition cursor-pointer select-none" onclick="toggleAccordion(<?php echo $modId; ?>)">
            <div class="flex items-center gap-2.5">
              <span id="mod_chevron_<?php echo $modId; ?>" class="material-symbols-outlined text-[20px] text-on-surface-variant transition-transform duration-200">keyboard_arrow_down</span>
              
              <input type="checkbox" 
                     id="mod_master_<?php echo $modId; ?>" 
                     class="perm-mod-master-chk w-4 h-4 rounded text-secondary focus:ring-secondary cursor-pointer" 
                     data-mod-id="<?php echo $modId; ?>"
                     checked 
                     onclick="event.stopPropagation()"
                     onchange="onModMasterToggle(<?php echo $modId; ?>, this.checked)" />
              
              <span class="material-symbols-outlined text-[19px] text-primary"><?php echo html_escape($mod['icon'] ?: 'folder'); ?></span>
              
              <span class="text-[13px] font-bold text-on-surface uppercase tracking-wide flex items-center gap-2">
                <?php echo html_escape($mod['name']); ?>
                <?php if ($isSoon): ?>
                  <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30 uppercase tracking-wider">SOON</span>
                <?php elseif (!empty($mod['badge'])): ?>
                  <span class="px-1.5 py-0.5 text-[9px] rounded-full bg-surface-container-high text-on-surface-variant font-medium"><?php echo html_escape($mod['badge']); ?></span>
                <?php endif; ?>
              </span>
            </div>

            <!-- Module All / None Controls -->
            <div class="flex items-center gap-2 text-[11px]" onclick="event.stopPropagation()">
              <button type="button" onclick="toggleModAllNone(<?php echo $modId; ?>, true)" class="text-primary font-semibold hover:underline cursor-pointer">All</button>
              <span class="text-outline text-xs">•</span>
              <button type="button" onclick="toggleModAllNone(<?php echo $modId; ?>, false)" class="text-on-surface-variant font-semibold hover:underline cursor-pointer">None</button>
            </div>
          </div>

          <!-- Collapsible Table Body -->
          <div id="mod_body_<?php echo $modId; ?>" class="perm-mod-body overflow-x-auto border-t border-outline-variant/40">
            <table class="w-full text-[12px] border-collapse">
              <thead>
                <tr class="bg-surface-container-low/40 border-b border-outline-variant/40 text-on-surface-variant font-bold uppercase text-[10px] tracking-wider">
                  <th class="text-left px-3.5 py-2 w-5/12">Menu / Sub Module</th>
                  <th class="text-center px-2 py-2 w-[11%]">Create</th>
                  <th class="text-center px-2 py-2 w-[11%]">View</th>
                  <th class="text-center px-2 py-2 w-[11%]">Edit</th>
                  <th class="text-center px-2 py-2 w-[11%]">Delete</th>
                  <th class="text-center px-2 py-2 w-[15%]">Other</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/20">
                <?php if (empty($mod['groups'])): ?>
                  <!-- Standalone Module with Direct Permissions (e.g. Dashboard) -->
                  <?php $ops = $mod['ops'] ?? []; ?>
                  <tr class="hover:bg-surface-container-low/30 transition">
                    <td class="px-3.5 py-2.5">
                      <label class="flex items-center gap-2 cursor-pointer font-medium text-on-surface">
                        <input type="checkbox"
                               id="submod_chk_<?php echo $modId; ?>"
                               class="perm-submod-master-chk w-3.5 h-3.5 rounded text-secondary focus:ring-secondary cursor-pointer"
                               data-mod-id="<?php echo $modId; ?>"
                               data-submod-id="<?php echo $modId; ?>"
                               checked
                               onchange="onSubmodMasterToggle(<?php echo $modId; ?>, this.checked)" />
                        <span><?php echo html_escape($mod['name']); ?></span>
                      </label>
                    </td>
                    <?php echo render_perm_matrix_cell($ops['create'] ?? null, $modId, $modId); ?>
                    <?php echo render_perm_matrix_cell($ops['view'] ?? null, $modId, $modId); ?>
                    <?php echo render_perm_matrix_cell($ops['edit'] ?? null, $modId, $modId); ?>
                    <?php echo render_perm_matrix_cell($ops['delete'] ?? null, $modId, $modId); ?>
                    <?php echo render_perm_matrix_other_cell($ops['other'] ?? [], $modId, $modId); ?>
                  </tr>

                <?php else: ?>
                  <?php foreach ($mod['groups'] as $group): ?>
                    <?php if ($group['type'] === 'submenu'): 
                      $subId = (int)$group['id'];
                      $ops = $group['ops'] ?? [];
                      $isSubSoon = !empty($group['is_soon']) || (!empty($group['badge']) && strtolower($group['badge']) === 'soon');
                    ?>
                      <tr class="hover:bg-surface-container-low/30 transition">
                        <td class="px-3.5 py-2.5">
                          <label class="flex items-center gap-2 cursor-pointer font-medium text-on-surface">
                            <input type="checkbox"
                                   id="submod_chk_<?php echo $subId; ?>"
                                   class="perm-submod-master-chk w-3.5 h-3.5 rounded text-secondary focus:ring-secondary cursor-pointer"
                                   data-mod-id="<?php echo $modId; ?>"
                                   data-submod-id="<?php echo $subId; ?>"
                                   checked
                                   onchange="onSubmodMasterToggle(<?php echo $subId; ?>, this.checked)" />
                            <span><?php echo html_escape($group['name']); ?></span>
                            <?php if ($isSubSoon): ?>
                              <span class="px-1.5 py-0.2 text-[8px] rounded-full bg-amber-500/15 text-amber-700 font-bold uppercase tracking-wider">SOON</span>
                            <?php elseif (!empty($group['badge'])): ?>
                              <span class="px-1 py-0.2 text-[8px] rounded bg-surface-container-high text-on-surface-variant font-medium"><?php echo html_escape($group['badge']); ?></span>
                            <?php endif; ?>
                          </label>
                        </td>
                        <?php echo render_perm_matrix_cell($ops['create'] ?? null, $modId, $subId); ?>
                        <?php echo render_perm_matrix_cell($ops['view'] ?? null, $modId, $subId); ?>
                        <?php echo render_perm_matrix_cell($ops['edit'] ?? null, $modId, $subId); ?>
                        <?php echo render_perm_matrix_cell($ops['delete'] ?? null, $modId, $subId); ?>
                        <?php echo render_perm_matrix_other_cell($ops['other'] ?? [], $modId, $subId); ?>
                      </tr>

                    <?php elseif ($group['type'] === 'group'): ?>
                      <!-- Dropdown Subgroup Header Row -->
                      <tr class="bg-surface-container-high/30 text-[11px] font-bold text-on-surface">
                        <td colspan="6" class="px-3.5 py-1.5 border-t border-b border-outline-variant/30">
                          <div class="flex items-center gap-1.5 text-tertiary">
                            <span class="material-symbols-outlined text-[15px]">folder_open</span>
                            <span><?php echo html_escape($group['name']); ?></span>
                            <?php if (!empty($group['is_soon'])): ?>
                              <span class="px-1.5 py-0.2 text-[8px] rounded-full bg-amber-500/15 text-amber-700 font-bold uppercase tracking-wider">SOON</span>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>

                      <?php foreach ($group['items'] as $item): 
                        $subId = (int)$item['id'];
                        $ops = $item['ops'] ?? [];
                        $isItemSoon = !empty($item['is_soon']) || (!empty($item['badge']) && strtolower($item['badge']) === 'soon');
                      ?>
                        <tr class="hover:bg-surface-container-low/30 transition">
                          <td class="px-3.5 py-2.5 pl-7">
                            <label class="flex items-center gap-2 cursor-pointer font-medium text-on-surface">
                              <input type="checkbox"
                                     id="submod_chk_<?php echo $subId; ?>"
                                     class="perm-submod-master-chk w-3.5 h-3.5 rounded text-secondary focus:ring-secondary cursor-pointer"
                                     data-mod-id="<?php echo $modId; ?>"
                                     data-submod-id="<?php echo $subId; ?>"
                                     checked
                                     onchange="onSubmodMasterToggle(<?php echo $subId; ?>, this.checked)" />
                              <span><?php echo html_escape($item['name']); ?></span>
                              <?php if ($isItemSoon): ?>
                                <span class="px-1.5 py-0.2 text-[8px] rounded-full bg-amber-500/15 text-amber-700 font-bold uppercase tracking-wider">SOON</span>
                              <?php elseif (!empty($item['badge'])): ?>
                                <span class="px-1 py-0.2 text-[8px] rounded bg-surface-container-high text-on-surface-variant font-medium"><?php echo html_escape($item['badge']); ?></span>
                              <?php endif; ?>
                            </label>
                          </td>
                          <?php echo render_perm_matrix_cell($ops['create'] ?? null, $modId, $subId); ?>
                          <?php echo render_perm_matrix_cell($ops['view'] ?? null, $modId, $subId); ?>
                          <?php echo render_perm_matrix_cell($ops['edit'] ?? null, $modId, $subId); ?>
                          <?php echo render_perm_matrix_cell($ops['delete'] ?? null, $modId, $subId); ?>
                          <?php echo render_perm_matrix_other_cell($ops['other'] ?? [], $modId, $subId); ?>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center py-8 text-on-surface-variant text-body-sm">
        No operational school modules found in database.
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
/**
 * Permission Matrix Tri-State & Accordion Controller
 */
function toggleAccordion(modId) {
  const body = document.getElementById('mod_body_' + modId);
  const chevron = document.getElementById('mod_chevron_' + modId);
  if (!body) return;

  if (body.classList.contains('hidden')) {
    body.classList.remove('hidden');
    if (chevron) chevron.style.transform = 'rotate(0deg)';
  } else {
    body.classList.add('hidden');
    if (chevron) chevron.style.transform = 'rotate(-90deg)';
  }
}

function onSubmodMasterToggle(subId, checked) {
  document.querySelectorAll('.perm-submod-' + subId).forEach(cb => {
    cb.checked = checked;
  });

  const subChk = document.getElementById('submod_chk_' + subId);
  if (subChk) subChk.indeterminate = false;

  const modId = subChk?.getAttribute('data-mod-id');
  if (modId) {
    updateModuleMasterState(modId);
  }

  ensureCustomMode();
}

function onMatrixChildPermChange(modId, subId) {
  updateSubmodMasterState(subId);
  updateModuleMasterState(modId);
  ensureCustomMode();
}

function updateSubmodMasterState(subId) {
  const subChk = document.getElementById('submod_chk_' + subId);
  if (!subChk) return;

  const children = document.querySelectorAll('.perm-submod-' + subId);
  if (!children.length) return;

  let checkedCount = 0;
  children.forEach(cb => {
    if (cb.checked) checkedCount++;
  });

  if (checkedCount === children.length) {
    subChk.checked = true;
    subChk.indeterminate = false;
  } else if (checkedCount === 0) {
    subChk.checked = false;
    subChk.indeterminate = false;
  } else {
    subChk.checked = false;
    subChk.indeterminate = true;
  }
}

function updateModuleMasterState(modId) {
  const modMaster = document.getElementById('mod_master_' + modId);
  if (!modMaster) return;

  const children = document.querySelectorAll('.perm-mod-' + modId);
  if (!children.length) return;

  let checkedCount = 0;
  children.forEach(cb => {
    if (cb.checked) checkedCount++;
  });

  if (checkedCount === children.length) {
    modMaster.checked = true;
    modMaster.indeterminate = false;
  } else if (checkedCount === 0) {
    modMaster.checked = false;
    modMaster.indeterminate = false;
  } else {
    modMaster.checked = false;
    modMaster.indeterminate = true;
  }
}

function onModMasterToggle(modId, checked) {
  toggleModAllNone(modId, checked);
}

function toggleModAllNone(modId, checked) {
  document.querySelectorAll('.perm-mod-' + modId).forEach(cb => {
    cb.checked = checked;
  });

  const modAccordion = document.getElementById('mod_accordion_' + modId);
  if (modAccordion) {
    modAccordion.querySelectorAll('.perm-submod-master-chk').forEach(subChk => {
      subChk.checked = checked;
      subChk.indeterminate = false;
    });
  }

  const modMaster = document.getElementById('mod_master_' + modId);
  if (modMaster) {
    modMaster.checked = checked;
    modMaster.indeterminate = false;
  }

  ensureCustomMode();
}

function selectAllPerms(check) {
  document.querySelectorAll('.perm-chk').forEach(cb => cb.checked = check);
  document.querySelectorAll('.perm-submod-master-chk').forEach(cb => {
    cb.checked = check;
    cb.indeterminate = false;
  });
  document.querySelectorAll('.perm-mod-master-chk').forEach(cb => {
    cb.checked = check;
    cb.indeterminate = false;
  });
}

function updateAllMatrixStates() {
  document.querySelectorAll('.perm-submod-master-chk').forEach(subChk => {
    const subId = subChk.getAttribute('data-submod-id');
    if (subId) updateSubmodMasterState(subId);
  });

  document.querySelectorAll('.perm-mod-master-chk').forEach(modMaster => {
    const modId = modMaster.getAttribute('data-mod-id');
    if (modId) updateModuleMasterState(modId);
  });
}

function ensureCustomMode() {
  const customRadio = document.getElementById('perm_mode_custom');
  if (customRadio && !customRadio.checked) {
    customRadio.checked = true;
  }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
  updateAllMatrixStates();
});
</script>
