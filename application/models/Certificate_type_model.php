<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_type_model extends CI_Model {

    protected $table = 'tbl_certificate_types';
    protected $primaryKey = 'type_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($status = null)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('type_id', 'ASC');
        $res = $this->db->get()->result();

        if (empty($res)) {
            // Fallback for types
            $this->db->from($this->table);
            if ($status !== null) {
                $this->db->where('status', $status);
            }
            $this->db->order_by('type_id', 'ASC');
            $res = $this->db->get()->result();
        }

        return $res;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->get($this->table)
            ->row();
    }

    public function get_by_code($code)
    {
        $school_id = $this->get_school_id();
        $res = $this->db
            ->where('type_code', $code)
            ->where('school_id', $school_id)
            ->get($this->table)
            ->row();

        if (!$res) {
            $res = $this->db->where('type_code', $code)->get($this->table)->row();
        }

        return $res;
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

    public function toggle_status($id)
    {
        $type = $this->get_by_id($id);
        if (!$type) return false;
        $new_status = ($type->status === 'Active') ? 'Inactive' : 'Active';
        return $this->update($id, array('status' => $new_status));
    }
}
