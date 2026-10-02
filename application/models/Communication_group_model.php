<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communication_group_model extends CI_Model {

    protected $table = 'tbl_communication_groups';
    protected $primaryKey = 'group_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) $this->db->where('status', 1);
        return $this->db->order_by('group_name', 'ASC')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['is_deleted' => 'y']);
    }
}
