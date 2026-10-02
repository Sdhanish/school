<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_category_model extends CI_Model {

    protected $table = 'tbl_document_categories';
    protected $primaryKey = 'category_id';

    public function get_all($status = null)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db->from($this->table);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }
        $this->db->order_by('category_id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db->where($this->primaryKey, $id);
        if (!$is_super && $school_id > 0) {
            $this->db->where('school_id', $school_id);
        }
        return $this->db->get($this->table)->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $q = $this->db->where($this->primaryKey, $id);
        if ($school_id > 0) {
            $q->where('school_id', $school_id);
        }
        return $q->update($this->table, $data);
    }
}
