<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * templates/sidebar.php
 *
 * Dynamic Database-Driven Sidebar Navigation View.
 * Renders the menu tree provided by the Navigation service ($sidebar_items).
 *
 * Preserves 100% UI fidelity:
 *  - Green background (bg-secondary)
 *  - Login2 brand logo with school icon
 *  - Material Symbols icons
 *  - Single-module accordion behavior (bg-white active module block)
 *  - Subgroup dropdown toggles (nav-level-2)
 *  - Active menu highlighting
 *  - "Soon" badges
 *  - Mobile overlay & toggle buttons
 *  - Logout button
 */

$sidebar_items = $sidebar_items ?? [];
$active_key    = $page_key ?? 'dashboard';

// Helper to determine active item
$normActiveKey = strtolower(str_replace('_', '-', (string)$active_key));

$matches_key = function($k, $aliases = []) use ($normActiveKey) {
    if (!$k) return false;
    $nk = strtolower(str_replace('_', '-', (string)$k));
    if ($nk === $normActiveKey) return true;
    if (!empty($aliases)) {
        foreach ((array)$aliases as $a) {
            if (strtolower(str_replace('_', '-', (string)$a)) === $normActiveKey) {
                return true;
            }
        }
    }
    return false;
};

// Resolve active hierarchy
$activeModuleKey = NULL;
$activeGroupLabel = NULL;
$activePageKey = NULL;

foreach ($sidebar_items as $mod) {
    $mKey = $mod['key'] ?? '';
    if (empty($mod['groups'])) {
        if ($matches_key($mKey, $mod['aliases'] ?? [])) {
            $activeModuleKey = $mKey;
            $activePageKey   = $mKey;
            break;
        }
    } else {
        foreach ($mod['groups'] as $grp) {
            if (!empty($grp['key'])) {
                if ($matches_key($grp['key'], $grp['aliases'] ?? [])) {
                    $activeModuleKey = $mKey;
                    $activePageKey   = $grp['key'];
                    break 2;
                }
            } elseif (!empty($grp['items'])) {
                foreach ($grp['items'] as $item) {
                    if ($matches_key($item['key'], $item['aliases'] ?? [])) {
                        $activeModuleKey  = $mKey;
                        $activeGroupLabel = $grp['label'];
                        $activePageKey    = $item['key'];
                        break 3;
                    }
                }
            }
        }
    }
}

// Fallback to module key prefix match
if (!$activeModuleKey) {
    foreach ($sidebar_items as $mod) {
        $mKey = $mod['key'] ?? '';
        if ($normActiveKey === $mKey || strpos($normActiveKey, $mKey . '-') === 0) {
            $activeModuleKey = $mKey;
            break;
        }
    }
}
if (!$activeModuleKey && $normActiveKey === 'dashboard') {
    $activeModuleKey = 'dashboard';
    $activePageKey   = 'dashboard';
}
?>

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-dark-text/30 z-30 hidden lg:hidden"></div>

<aside id="app-sidebar"
  class="fixed lg:sticky top-0 left-0 h-screen w-[264px] shrink-0 bg-sky-blue border-r border-sidebar-border
  flex flex-col z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200">

  <!-- Header / Brand -->
  <div class="h-16 flex items-center gap-3 px-4 border-b border-white/20 shrink-0">
    <div class="w-9 h-9 rounded-xl bg-white text-primary shadow-xs flex items-center justify-center shrink-0 border border-white/40">
      <span class="material-symbols-outlined text-primary text-[20px]">school</span>
    </div>
    <a href="<?php echo site_url('dashboard'); ?>" class="sidebar-label font-headline-md text-headline-md text-white font-bold tracking-tight truncate">Login2</a>
    <button id="sidebar-collapse-btn" type="button" class="ml-auto hidden lg:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-white/20 text-white/80 hover:text-white transition-colors">
      <span class="material-symbols-outlined text-[20px]">dock_to_right</span>
    </button>
    <button id="sidebar-close-btn" type="button" class="ml-auto lg:hidden flex items-center justify-center w-8 h-8 rounded-lg hover:bg-white/20 text-white/80 hover:text-white transition-colors">
      <span class="material-symbols-outlined text-[20px]">close</span>
    </button>
  </div>

  <!-- Navigation Menu List -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
    <?php foreach ($sidebar_items as $item): ?>
      <?php
        $modKey       = $item['key'] ?? '';
        $isModuleOpen = ($modKey === $activeModuleKey);
        $groups       = $item['groups'] ?? [];
      ?>

      <?php if (empty($groups)): ?>
        <!-- Single Top-Level Module (e.g. Dashboard) -->
        <?php $isActive = ($isModuleOpen && $modKey === $activePageKey); ?>
        <div class="nav-group mb-1">
          <a href="<?php echo site_url($item['route'] ?? $modKey); ?>" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-body-md font-body-md transition-all duration-200 <?php echo $isActive ? 'bg-white text-primary font-bold shadow-sm' : 'text-white/90 hover:bg-white/15 hover:text-white'; ?>">
            <span class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?php echo $isActive ? 'text-primary' : 'text-white/90'; ?>"><?php echo html_escape(str_replace('-', '_', $item['icon'] ?? 'circle')); ?></span>
              <span class="sidebar-label truncate"><?php echo html_escape($item['label'] ?? ''); ?></span>
            </span>
            <?php if (!empty($item['soon'])): ?>
              <span class="sidebar-label shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white border border-white/30">Soon</span>
            <?php endif; ?>
          </a>
        </div>

      <?php else: ?>
        <!-- Expandable Module with Groups / Submenus -->
        <div class="nav-group mb-1 <?php echo $isModuleOpen ? 'bg-white text-primary rounded-xl p-1.5 shadow-sm' : ''; ?>" data-module-key="<?php echo html_escape($modKey); ?>">
          <button type="button" data-toggle-module="<?php echo html_escape($modKey); ?>"
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-body-md font-body-md transition-all duration-200 <?php echo $isModuleOpen ? 'text-primary font-bold bg-transparent' : 'text-white/90 hover:bg-white/15 hover:text-white'; ?>">
            <span class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px] <?php echo $isModuleOpen ? 'text-primary' : 'text-white/90'; ?>"><?php echo html_escape(str_replace('-', '_', $item['icon'] ?? 'folder')); ?></span>
              <span class="sidebar-label truncate"><?php echo html_escape($item['label'] ?? ''); ?></span>
            </span>
            <?php if (!empty($item['soon'])): ?>
              <span class="sidebar-label shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white border border-white/30">Soon</span>
            <?php else: ?>
              <span class="material-symbols-outlined sidebar-label text-[18px] transition-transform <?php echo $isModuleOpen ? 'rotate-180 text-primary' : 'text-white/70'; ?>">expand_more</span>
            <?php endif; ?>
          </button>

          <div class="nav-groups mt-1 pt-1 border-t border-theme-border/60 space-y-0.5 <?php echo $isModuleOpen ? '' : 'hidden'; ?>">
            <?php foreach ($groups as $group): ?>
              <?php if (!empty($group['key'])): ?>
                <!-- Direct Level 2 Submenu Item -->
                <?php
                  $isItemActive = $isModuleOpen && ($group['key'] === $activePageKey || $matches_key($group['key'], $group['aliases'] ?? []));
                ?>
                <a href="<?php echo site_url($group['route'] ?? $group['key']); ?>" class="flex items-center justify-between pl-7 pr-3 py-1.5 rounded-lg text-xs font-body-md transition-colors <?php echo $isItemActive ? 'bg-light-blue text-primary font-bold shadow-2xs' : 'text-dark-text/80 hover:bg-light-blue/60 hover:text-dark-text'; ?>">
                  <span class="truncate"><?php echo html_escape($group['label'] ?? ''); ?></span>
                  <?php if (!empty($group['badge_text'])): ?>
                    <span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary"><?php echo html_escape($group['badge_text']); ?></span>
                  <?php elseif (!empty($group['soon'])): ?>
                    <span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary">Soon</span>
                  <?php endif; ?>
                </a>

              <?php elseif (!empty($group['items'])): ?>
                <!-- Collapsible Level 2 Subgroup -->
                <?php
                  $isGroupOpen = $isModuleOpen && (
                    ($group['label'] ?? '') === $activeGroupLabel ||
                    array_reduce($group['items'], function($carry, $child) use ($matches_key, $activePageKey) {
                        return $carry || ($child['key'] === $activePageKey) || $matches_key($child['key'], $child['aliases'] ?? []);
                    }, false)
                  );
                ?>
                <div class="nav-level-2" data-group-label="<?php echo html_escape($group['label'] ?? ''); ?>">
                  <button type="button" data-toggle-subgroup
                    class="w-full flex items-center justify-between pl-7 pr-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider transition-colors <?php echo $isGroupOpen ? 'text-primary font-bold' : 'text-dark-text/75 hover:bg-light-blue/60 hover:text-dark-text'; ?>">
                    <span class="truncate"><?php echo html_escape($group['label'] ?? ''); ?></span>
                    <span class="material-symbols-outlined text-[16px] text-primary transition-transform <?php echo $isGroupOpen ? 'rotate-90' : ''; ?>">chevron_right</span>
                  </button>
                  <div class="nav-sub-items mt-0.5 space-y-0.5 <?php echo $isGroupOpen ? '' : 'hidden'; ?>">
                    <?php foreach ($group['items'] as $child): ?>
                      <?php
                        $isChildActive = $isModuleOpen && ($child['key'] === $activePageKey || $matches_key($child['key'], $child['aliases'] ?? []));
                      ?>
                      <a href="<?php echo site_url($child['route'] ?? $child['key']); ?>" class="flex items-center justify-between pl-10 pr-3 py-1.5 rounded-lg text-xs font-body-md transition-colors <?php echo $isChildActive ? 'bg-light-blue text-primary font-bold shadow-2xs' : 'text-dark-text/80 hover:bg-light-blue/60 hover:text-dark-text'; ?>">
                        <span class="truncate"><?php echo html_escape($child['label'] ?? ''); ?></span>
                        <?php if (!empty($child['badge_text'])): ?>
                          <span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary"><?php echo html_escape($child['badge_text']); ?></span>
                        <?php elseif (!empty($child['soon'])): ?>
                          <span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary">Soon</span>
                        <?php endif; ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <!-- Footer / Logout Button -->
  <div class="border-t border-white/20 p-3">
    <a href="<?php echo site_url('auth/logout'); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-body-md font-body-md text-white/90 hover:bg-white/15 hover:text-white transition-colors">
      <span class="material-symbols-outlined text-[20px] text-white">logout</span>
      <span class="sidebar-label">Log Out</span>
    </a>
  </div>
</aside>
