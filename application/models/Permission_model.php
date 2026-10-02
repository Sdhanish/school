<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {

    protected $table = 'tbl_permissions';
    protected $primaryKey = 'permission_id';

    public function get_all()
    {
        return $this->db
            ->order_by('module', 'ASC')
            ->order_by('permission_key', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_grouped_by_module()
    {
        $perms = $this->get_all();
        $grouped = [];
        foreach ($perms as $p) {
            $grouped[$p->module][] = $p;
        }
        return $grouped;
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where($this->primaryKey, $id)
            ->get($this->table)
            ->row();
    }

    public function get_by_key($key)
    {
        return $this->db
            ->where('permission_key', $key)
            ->get($this->table)
            ->row();
    }

    /**
     * Resolve permission dependencies
     * Example: 'fees.collect' implies 'fees.view'
     */
    public function get_implied_dependencies($permission_keys)
    {
        $resolved = $permission_keys;
        foreach ($permission_keys as $k) {
            $parts = explode('.', $k);
            if (count($parts) === 2) {
                $mod = $parts[0];
                $act = $parts[1];
                if (in_array($act, ['create', 'edit', 'delete', 'mark', 'collect', 'refund', 'generate', 'publish', 'export'])) {
                    $view_key = $mod . '.view';
                    if (!in_array($view_key, $resolved)) {
                        $resolved[] = $view_key;
                    }
                }
            }
        }
        return array_unique($resolved);
    }

    /**
     * Retrieve the unified permission matrix tree from Navigation service.
     * Ensures sidebar and permission matrix share the single database source of truth.
     *
     * @return array
     */
    public function get_menu_permission_matrix()
    {
        if (!isset($this->navigation)) {
            $this->load->library('navigation');
        }
        return $this->navigation->get_permission_matrix_tree();
    }
}

