<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model {

    protected $table = 'tbl_events';
    protected $primaryKey = 'event_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all()
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('event_date', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_upcoming($limit = 3)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->where('event_date >=', date('Y-m-d'))
            ->order_by('event_date', 'ASC')
            ->limit($limit)
            ->get($this->table)
            ->result();
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
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function soft_delete($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['status' => 0, 'is_deleted' => 'y']);
    }
}
