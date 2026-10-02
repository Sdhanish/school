<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Homework_type_model extends CI_Model {

    protected $table = 'tbl_assignment_types';
    protected $primaryKey = 'type_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) {
            $this->db->where('status', 1);
        }
        return $this->db->order_by('type_name', 'ASC')->get($this->table)->result();
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
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        // Safe delete or toggle
        $used_cnt = $this->db
            ->where('school_id', $school_id)
            ->where('assignment_type_id', (int)$id)
            ->count_all_results('tbl_assignments');
        if ($used_cnt === 0) {
            return $this->db
                ->where($this->primaryKey, (int)$id)
                ->where('school_id', $school_id)
                ->update($this->table, ['is_deleted' => 'y']);
        }
        return $this->update($id, ['status' => 0]);
    }
}
