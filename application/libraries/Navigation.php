<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Navigation Service Library
 *
 * Provides dynamic, database-driven menu tree resolution based on:
 *  - Logged-in user's school_id (multi-tenant isolation & school overrides)
 *  - User's role and effective permissions (tbl_roles, tbl_role_permissions, tbl_user_permissions)
 *  - Super Admin status (global management access)
 *  - Active / Inactive status (is_active in tbl_menu_items)
 *  - School-level menu availability (tbl_school_menus)
 *  - Recursive parent pruning (empty modules/groups are hidden)
 *
 * Employs in-memory caching to eliminate redundant DB queries and avoid N+1 issues.
 */
class Navigation {

    protected $CI;

    /** Per-request cache of computed menu trees keyed by "user_id:school_id" */
    protected $_tree_cache = [];

    /** Per-request cache of all active raw menu items */
    protected $_raw_menu_cache = NULL;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        if (!isset($this->CI->User_model)) {
            $this->CI->load->model('User_model');
        }
    }

    /**
     * Get the dynamic sidebar menu tree for the currently authenticated user.
     *
     * @return array Hierarchical array of accessible modules and submenus.
     */
    public function get_sidebar_for_current_user()
    {
        $user_data = $this->CI->session->userdata('user');
        if (empty($user_data)) {
            return [];
        }

        $user_id   = (int)($user_data['user_id'] ?? 0);
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : (int)($user_data['school_id'] ?? 1);
        $is_admin  = isset($this->CI->rbac) ? $this->CI->rbac->is_super_admin() : FALSE;

        return $this->get_menu_tree($user_id, $school_id, $is_admin);
    }

    /**
     * Build and return the filtered menu tree for a given user and school context.
     *
     * @param  int|null  $user_id
     * @param  int|null  $school_id
     * @param  bool|null $is_super_admin
     * @return array
     */
    public function get_menu_tree($user_id = NULL, $school_id = NULL, $is_super_admin = NULL)
    {
        if ($user_id === NULL) {
            $user_data = $this->CI->session->userdata('user');
            $user_id   = (int)($user_data['user_id'] ?? 0);
        }

        if ($school_id === NULL) {
            $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        }

        if ($is_super_admin === NULL) {
            $is_super_admin = isset($this->CI->rbac) ? $this->CI->rbac->is_super_admin($user_id) : FALSE;
        }

        $cache_key = "{$user_id}:{$school_id}:" . ($is_super_admin ? '1' : '0');
        if (isset($this->_tree_cache[$cache_key])) {
            return $this->_tree_cache[$cache_key];
        }

        // 1. Fetch raw active menu items (cached across the request)
        if ($this->_raw_menu_cache === NULL) {
            $this->_raw_menu_cache = $this->CI->db
                ->where('is_active', 1)
                ->order_by('sort_order', 'ASC')
                ->order_by('id', 'ASC')
                ->get('tbl_menu_items')
                ->result_array();
        }

        // 2. Fetch school-specific menu disabled overrides
        $disabled_school_menus = [];
        if ($school_id > 0) {
            $s_overrides = $this->CI->db
                ->select('menu_id, is_enabled')
                ->where('school_id', $school_id)
                ->get('tbl_school_menus')
                ->result_array();

            foreach ($s_overrides as $so) {
                if ((int)$so['is_enabled'] === 0) {
                    $disabled_school_menus[(int)$so['menu_id']] = TRUE;
                }
            }
        }

        // 3. Fetch effective user permissions
        $effective_permissions = [];
        if (!$is_super_admin && $user_id > 0) {
            $effective_permissions = $this->CI->User_model->get_effective_permissions($user_id);
        }

        // 4. Index raw items by parent_id for fast tree construction
        $by_parent = [];
        foreach ($this->_raw_menu_cache as $item) {
            $pId = $item['parent_id'] !== NULL ? (int)$item['parent_id'] : 0;
            $by_parent[$pId][] = $item;
        }

        // Helper: Check if an individual item is accessible
        $can_access_item = function($item) use ($is_super_admin, $disabled_school_menus, $effective_permissions) {
            $itemId = (int)$item['id'];

            // Exclude if disabled specifically for this school
            if (isset($disabled_school_menus[$itemId])) {
                return FALSE;
            }

            // Exclude super admin only items for non-super admins
            if (!empty($item['is_super_admin_only']) && !$is_super_admin) {
                return FALSE;
            }

            // Super Admin has access to all active, non-disabled items
            if ($is_super_admin) {
                return TRUE;
            }

            // If item has a permission requirement, check against effective permissions
            if (!empty($item['permission_key'])) {
                return in_array($item['permission_key'], $effective_permissions, TRUE);
            }

            // Items with badge 'Soon' or without explicit permission key default to accessible
            // if their parent module/group is permitted
            return TRUE;
        };

        // 5. Build hierarchical tree with recursive empty-parent pruning
        $menu_tree = [];
        $main_modules = $by_parent[0] ?? [];

        foreach ($main_modules as $mod) {
            $modId = (int)$mod['id'];

            // If module itself is not accessible (e.g. disabled for school or super_admin only)
            if (!$can_access_item($mod)) {
                continue;
            }

            $modKey     = $mod['menu_key'];
            $hasPerm    = empty($mod['permission_key']) || $is_super_admin || in_array($mod['permission_key'], $effective_permissions, TRUE);
            $hasChildren= !empty($by_parent[$modId]);

            // Case A: Standalone Top-Level Menu (no children, e.g. Dashboard)
            if (!$hasChildren) {
                if ($hasPerm) {
                    $menu_tree[] = [
                        'id'         => $modId,
                        'key'        => $modKey,
                        'label'      => $mod['menu_name'],
                        'icon'       => !empty($mod['icon']) ? str_replace('-', '_', $mod['icon']) : 'circle',
                        'route'      => $mod['route'] ?: $modKey,
                        'soon'       => ($mod['badge_text'] === 'Soon'),
                        'badge_text' => $mod['badge_text'],
                        'aliases'    => !empty($mod['aliases']) ? explode(',', $mod['aliases']) : [],
                    ];
                }
                continue;
            }

            // Case B: Expandable Module with Children
            $filtered_groups = [];
            $level2_items = $by_parent[$modId] ?? [];

            foreach ($level2_items as $l2) {
                $l2Id = (int)$l2['id'];

                if (!$can_access_item($l2)) {
                    continue;
                }

                if ($l2['menu_type'] === 'submenu') {
                    // Direct Level 2 subitem (e.g. Overview, All Students, Bulk Add)
                    $filtered_groups[] = [
                        'id'         => $l2Id,
                        'key'        => $l2['menu_key'],
                        'label'      => $l2['menu_name'],
                        'route'      => $l2['route'] ?: $l2['menu_key'],
                        'soon'       => ($l2['badge_text'] === 'Soon'),
                        'badge_text' => $l2['badge_text'],
                        'aliases'    => !empty($l2['aliases']) ? explode(',', $l2['aliases']) : [],
                        'perm'       => !empty($l2['is_super_admin_only']) ? 'super_admin' : NULL,
                    ];
                } elseif ($l2['menu_type'] === 'group') {
                    // Level 2 Dropdown Subgroup (e.g. Admissions, Academic Setup)
                    $level3_items = $by_parent[$l2Id] ?? [];
                    $filtered_l3 = [];

                    foreach ($level3_items as $l3) {
                        if ($can_access_item($l3)) {
                            $filtered_l3[] = [
                                'id'         => (int)$l3['id'],
                                'key'        => $l3['menu_key'],
                                'label'      => $l3['menu_name'],
                                'route'      => $l3['route'] ?: $l3['menu_key'],
                                'soon'       => ($l3['badge_text'] === 'Soon'),
                                'badge_text' => $l3['badge_text'],
                                'aliases'    => !empty($l3['aliases']) ? explode(',', $l3['aliases']) : [],
                                'perm'       => !empty($l3['is_super_admin_only']) ? 'super_admin' : NULL,
                            ];
                        }
                    }

                    // Rule 8: If a subgroup has no accessible child items, hide the subgroup
                    if (!empty($filtered_l3)) {
                        $filtered_groups[] = [
                            'id'         => $l2Id,
                            'label'      => $l2['menu_name'],
                            'soon'       => ($l2['badge_text'] === 'Soon'),
                            'badge_text' => $l2['badge_text'],
                            'items'      => $filtered_l3,
                        ];
                    }
                }
            }

            // Rule 8: If a parent module has no accessible child menus, do not display the parent module
            if (!empty($filtered_groups)) {
                $menu_tree[] = [
                    'id'         => $modId,
                    'key'        => $modKey,
                    'label'      => $mod['menu_name'],
                    'icon'       => !empty($mod['icon']) ? str_replace('-', '_', $mod['icon']) : 'folder',
                    'soon'       => ($mod['badge_text'] === 'Soon'),
                    'badge_text' => $mod['badge_text'],
                    'aliases'    => !empty($mod['aliases']) ? explode(',', $mod['aliases']) : [],
                    'groups'     => $filtered_groups,
                ];
            }
        }

        $this->_tree_cache[$cache_key] = $menu_tree;
        return $menu_tree;
    }

    /**
     * Check if a specific menu key is accessible by the current user.
     *
     * @param  string   $menu_key
     * @param  int|null $user_id
     * @return bool
     */
    public function can_access_menu($menu_key, $user_id = NULL)
    {
        $tree = $this->get_sidebar_for_current_user();
        return $this->_search_tree_key($tree, $menu_key);
    }

    /**
     * Recursive search for menu key existence in filtered tree.
     */
    protected function _search_tree_key($tree, $key)
    {
        foreach ($tree as $item) {
            if (isset($item['key']) && $item['key'] === $key) {
                return TRUE;
            }
            if (!empty($item['groups'])) {
                if ($this->_search_tree_key($item['groups'], $key)) {
                    return TRUE;
                }
            }
            if (!empty($item['items'])) {
                if ($this->_search_tree_key($item['items'], $key)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Clear in-memory caches (useful for testing and after updating menus/permissions).
     */
    public function flush_cache()
    {
        $this->_tree_cache     = [];
        $this->_raw_menu_cache = NULL;
    }

    /**
     * Build and return the database-driven School Admin Permission Matrix Tree.
     *
     * Retrieves all active, non-super-admin root modules, subgroups, and submenus
     * from tbl_menu_items along with their linked permissions from tbl_menu_permissions
     * and tbl_permissions.
     *
     * This provides the single source of truth connecting sidebar hierarchy
     * with the School Admin permission selection UI.
     *
     * @return array Array of modules, each with groups, submenus, and permission definitions.
     */
    public function get_permission_matrix_tree()
    {
        // 1. Fetch active, non-super-admin menu items
        $menu_items = $this->CI->db
            ->where('is_active', 1)
            ->where('is_super_admin_only', 0)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get('tbl_menu_items')
            ->result_array();

        if (empty($menu_items)) {
            return [];
        }

        // 2. Fetch all menu-to-permission mappings
        $menu_perms_raw = $this->CI->db
            ->select('mp.menu_id, p.permission_id, p.permission_key, p.permission_name, p.module, p.action, p.description')
            ->from('tbl_menu_permissions mp')
            ->join('tbl_permissions p', 'p.permission_id = mp.permission_id')
            ->where('p.is_deleted', 'n')
            ->order_by('p.permission_id', 'ASC')
            ->get()
            ->result_array();

        $perms_by_menu = [];
        foreach ($menu_perms_raw as $mp) {
            $perms_by_menu[(int)$mp['menu_id']][] = $mp;
        }

        // 3. Fetch all active permissions by key for fallback resolution
        $all_system_perms = $this->CI->db
            ->select('permission_id, permission_key, permission_name, module, action, description')
            ->where('is_deleted', 'n')
            ->get('tbl_permissions')
            ->result_array();

        $perms_by_key = [];
        foreach ($all_system_perms as $p) {
            $perms_by_key[$p['permission_key']] = $p;
        }

        // 4. Index menu items by parent_id
        $by_parent = [];
        foreach ($menu_items as $item) {
            $pId = $item['parent_id'] !== NULL ? (int)$item['parent_id'] : 0;
            $by_parent[$pId][] = $item;
        }

        $matrix_tree = [];
        $root_modules = $by_parent[0] ?? [];

        foreach ($root_modules as $mod) {
            $modId = (int)$mod['id'];

            $mod_node = [
                'id'                 => $modId,
                'key'                => $mod['menu_key'],
                'name'               => $mod['menu_name'],
                'icon'               => $mod['icon'] ?: 'folder',
                'badge'              => $mod['badge_text'],
                'is_soon'            => !empty($mod['is_soon']) || ($mod['badge_text'] === 'Soon'),
                'groups'             => [],
                'direct_permissions' => [],
                'all_permission_ids' => [],
            ];

            // Helper: register a permission ID into the module's all_permission_ids list (deduplicated).
            // This is ONLY for building the parent checkbox data-perm-ids attribute.
            // It does NOT gate whether individual sub-items receive their own permissions.
            $seen_mod_pids = [];
            $track_mod_perm = function($p) use (&$mod_node, &$seen_mod_pids) {
                $pid = (int)($p['permission_id'] ?? 0);
                if ($pid > 0 && !isset($seen_mod_pids[$pid])) {
                    $seen_mod_pids[$pid] = true;
                    $mod_node['all_permission_ids'][] = $pid;
                }
            };

            // Helper: resolve full permissions list for a menu item by ID + fallback key.
            $resolve_perms = function($item_id, $item_key) use ($perms_by_menu, $perms_by_key) {
                $raw = $perms_by_menu[$item_id] ?? [];
                if (empty($raw) && !empty($item_key) && isset($perms_by_key[$item_key])) {
                    $raw[] = $perms_by_key[$item_key];
                }
                return $raw;
            };

            // Helper: deduplicate a raw permissions array (within a single item only).
            $dedup_perms = function($raw) {
                $seen = [];
                $out  = [];
                foreach ($raw as $p) {
                    $pid = (int)($p['permission_id'] ?? 0);
                    if ($pid > 0 && !isset($seen[$pid])) {
                        $seen[$pid] = true;
                        $out[] = $p;
                    }
                }
                return $out;
            };

            $l2_items = $by_parent[$modId] ?? [];

            // 1. Module-level direct permissions
            $direct_raw = $resolve_perms($modId, $mod['permission_key']);

            // If module has no children (e.g. Dashboard), attach direct permissions immediately
            if (empty($l2_items)) {
                $direct_list = $dedup_perms($direct_raw);
                foreach ($direct_list as $dp) {
                    $mod_node['direct_permissions'][] = $dp;
                    $track_mod_perm($dp);
                }
                $mod_node['ops'] = $this->classify_submodule_operations($direct_list);
                $matrix_tree[] = $mod_node;
                continue;
            }

            // 2. Iterate Level 2 items (Submenus and Subgroups)
            foreach ($l2_items as $l2) {
                $l2Id = (int)$l2['id'];

                if ($l2['menu_type'] === 'submenu') {
                    // Each L2 submenu always gets its own permissions (no cross-sibling dedup)
                    $sub_perms = $dedup_perms($resolve_perms($l2Id, $l2['permission_key']));
                    foreach ($sub_perms as $sp) {
                        $track_mod_perm($sp);
                    }

                    $mod_node['groups'][] = [
                        'type'               => 'submenu',
                        'id'                 => $l2Id,
                        'key'                => $l2['menu_key'],
                        'name'               => $l2['menu_name'],
                        'badge'              => $l2['badge_text'],
                        'is_soon'            => !empty($l2['is_soon']) || ($l2['badge_text'] === 'Soon'),
                        'permissions'        => $sub_perms,
                        'ops'                => $this->classify_submodule_operations($sub_perms),
                        'all_permission_ids' => array_map('intval', array_column($sub_perms, 'permission_id')),
                    ];

                } elseif ($l2['menu_type'] === 'group') {
                    // Level 2 Dropdown Subgroup
                    $subgroup_node = [
                        'type'        => 'group',
                        'id'          => $l2Id,
                        'key'         => $l2['menu_key'],
                        'name'        => $l2['menu_name'],
                        'badge'       => $l2['badge_text'],
                        'is_soon'     => !empty($l2['is_soon']) || ($l2['badge_text'] === 'Soon'),
                        'items'       => [],
                        'permissions' => [],
                    ];

                    $seen_sg_pids = [];
                    $l3_items = $by_parent[$l2Id] ?? [];
                    foreach ($l3_items as $l3) {
                        $l3Id      = (int)$l3['id'];
                        $l3_perms  = $dedup_perms($resolve_perms($l3Id, $l3['permission_key']));

                        foreach ($l3_perms as $p) {
                            $track_mod_perm($p);
                            $pid = (int)$p['permission_id'];
                            if (!isset($seen_sg_pids[$pid])) {
                                $seen_sg_pids[$pid] = true;
                                $subgroup_node['permissions'][] = $p;
                            }
                        }

                        $subgroup_node['items'][] = [
                            'id'                 => $l3Id,
                            'key'                => $l3['menu_key'],
                            'name'               => $l3['menu_name'],
                            'badge'              => $l3['badge_text'],
                            'is_soon'            => !empty($l3['is_soon']) || ($l3['badge_text'] === 'Soon'),
                            'permissions'        => $l3_perms,
                            'ops'                => $this->classify_submodule_operations($l3_perms),
                            'all_permission_ids' => array_map('intval', array_column($l3_perms, 'permission_id')),
                        ];
                    }

                    $mod_node['groups'][] = $subgroup_node;
                }
            }

            // 3. Attach any module-level direct permissions that weren't already surfaced
            foreach ($dedup_perms($direct_raw) as $dp) {
                $track_mod_perm($dp);
                // Only add as direct_permission if it isn't already covered by a submenu
                // (avoids duplicating "academics.view" in both Overview and direct_permissions)
                $mod_node['direct_permissions'][] = $dp;
            }

            $matrix_tree[] = $mod_node;
        }

        return $matrix_tree;
    }

    /**
     * Get the count of active school modules available for permission management.
     *
     * @return int
     */
    public function get_active_school_modules_count()
    {
        $tree = $this->get_permission_matrix_tree();
        return count($tree);
    }

    /**
     * Classify a sub-module's permissions into standard matrix operations:
     * create, view, edit, delete, other.
     *
     * @param  array $permissions Array of permission dictionaries
     * @return array Associative array with keys create, view, edit, delete, other
     */
    public function classify_submodule_operations(array $permissions)
    {
        $ops = [
            'create' => null,
            'view'   => null,
            'edit'   => null,
            'delete' => null,
            'other'  => [],
        ];

        foreach ($permissions as $p) {
            $key    = strtolower(trim($p['permission_key'] ?? ''));
            $name   = strtolower(trim($p['permission_name'] ?? ''));
            $action = strtolower(trim($p['action'] ?? ''));

            // 1. Delete
            if (preg_match('/(\.delete|\.destroy|\.remove|\.archive)$/', $key) || 
                preg_match('/\b(delete|destroy|remove|archive)\b/', $name) || 
                preg_match('/\b(delete|destroy|remove|archive)\b/', $action)) {
                if ($ops['delete'] === null) {
                    $ops['delete'] = $p;
                } else {
                    $ops['other'][] = $p;
                }
                continue;
            }

            // 2. Edit / Update
            if (preg_match('/(\.edit|\.update|\.modify|\.change|\.review|\.marks_entry)$/', $key) || 
                preg_match('/\b(edit|update|modify|change|review|marks entry)\b/', $name) || 
                preg_match('/\b(edit|update|modify|change|review|marks entry)\b/', $action)) {
                if ($ops['edit'] === null) {
                    $ops['edit'] = $p;
                } else {
                    $ops['other'][] = $p;
                }
                continue;
            }

            // 3. Create / Add / Register / Admit / Apply
            if (preg_match('/(\.create|\.add|\.insert|\.register|\.admit|\.apply)$/', $key) || 
                preg_match('/\b(create|add|new|admit|register|apply)\b/', $name) || 
                preg_match('/\b(create|add|new|admit|register|apply)\b/', $action)) {
                if ($ops['create'] === null) {
                    $ops['create'] = $p;
                } else {
                    $ops['other'][] = $p;
                }
                continue;
            }

            // 4. View / List / Overview / Search
            if (preg_match('/(\.view|\.list|\.read|\.search|\.overview)$/', $key) || 
                preg_match('/\b(view|list|browse|overview|read)\b/', $name) || 
                preg_match('/\b(view|list|browse|overview|read)\b/', $action)) {
                if ($ops['view'] === null) {
                    $ops['view'] = $p;
                } else {
                    $ops['other'][] = $p;
                }
                continue;
            }

            // 5. If it is a manage permission and edit is empty, place in edit
            if (preg_match('/(\.manage)$/', $key) && $ops['edit'] === null) {
                $ops['edit'] = $p;
                continue;
            }

            // 6. Otherwise place in other
            $ops['other'][] = $p;
        }

        return $ops;
    }
}

