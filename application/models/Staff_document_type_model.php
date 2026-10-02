<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_document_type_model extends CI_Model {

    protected $table = 'tbl_staff_document_types';
    protected $primaryKey = 'id';

    /**
     * Get all active document types (for Add Staff and Edit Staff forms)
     */
    public function get_active_types()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db
            ->where('is_deleted', 'n')
            ->where('status', 'Active');

        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }

        return $this->db
            ->order_by('display_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    /**
     * Get all document types for Settings list (including active and inactive, but not soft-deleted)
     */
    public function get_all_for_settings()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db
            ->select('dt.*, u.name as creator_name, r.role_name as creator_role')
            ->from($this->table . ' dt')
            ->join('tbl_users u', 'u.user_id = dt.created_by', 'left')
            ->join('tbl_roles r', 'r.role_id = u.role_id', 'left')
            ->where('dt.is_deleted', 'n');

        if (!$is_super && $school_id > 0) {
            $this->db->where('dt.school_id', $school_id);
        }

        return $this->db
            ->order_by('dt.display_order', 'ASC')
            ->order_by('dt.id', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get single document type by ID
     */
    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db
            ->where('id', (int)$id)
            ->where('is_deleted', 'n');

        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }

        return $this->db
            ->get($this->table)
            ->row();
    }

    /**
     * Insert new document type
     */
    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['is_deleted'])) {
            $data['is_deleted'] = 'n';
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update existing document type
     */
    public function update($id, $data)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $q = $this->db->where('id', (int)$id);
        if ($school_id > 0) {
            $q->where('school_id', $school_id);
        }
        return $q->update($this->table, $data);
    }

    /**
     * Soft delete document type (preserves all historical staff uploaded documents)
     */
    public function soft_delete($id, $user_id = NULL)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $q = $this->db->where('id', (int)$id);
        if ($school_id > 0) {
            $q->where('school_id', $school_id);
        }
        return $q->update($this->table, array(
            'is_deleted' => 'y',
            'status'     => 'Inactive',
            'deleted_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Toggle document type active/inactive status
     */
    public function toggle_status($id)
    {
        $type = $this->get_by_id($id);
        if (!$type) return FALSE;

        $newStatus = ($type->status === 'Active') ? 'Inactive' : 'Active';
        return $this->update($id, array('status' => $newStatus));
    }

    /**
     * Count active document types
     */
    public function count_active()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db
            ->where('is_deleted', 'n')
            ->where('status', 'Active');

        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Get the current maximum display_order value for ordering new document types.
     *
     * @return int
     */
    public function get_max_display_order()
    {
        $row = $this->db
            ->select_max('display_order')
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();
        return $row ? (int)$row->display_order : 0;
    }
}
