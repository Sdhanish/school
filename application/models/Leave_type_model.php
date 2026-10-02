<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_type_model extends CI_Model {

    protected $table = 'tbl_leave_types';
    protected $primaryKey = 'type_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($applicable_to = NULL, $active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) $this->db->where('status', 1);
        if ($applicable_to) {
            $this->db->group_start()
                ->where('applicable_to', $applicable_to)
                ->or_where('applicable_to', 'Both')
            ->group_end();
        }
        $res = $this->db->order_by('type_name', 'ASC')->get()->result();

        if (empty($res)) {
            // Fallback
            $this->db->from($this->table);
            $this->db->where('is_deleted', 'n');
            if ($active_only) $this->db->where('status', 1);
            if ($applicable_to) {
                $this->db->group_start()
                    ->where('applicable_to', $applicable_to)
                    ->or_where('applicable_to', 'Both')
                ->group_end();
            }
            $res = $this->db->order_by('type_name', 'ASC')->get()->result();
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

    public function delete_or_deactivate($id)
    {
        $school_id = $this->get_school_id();
        // Check if historical applications exist
        $hasHistory = $this->db
            ->where('school_id', $school_id)
            ->where('leave_type_id', (int)$id)
            ->count_all_results('tbl_leave_applications') > 0;
        if ($hasHistory) {
            return $this->update($id, ['status' => 0]);
        }
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['is_deleted' => 'y']);
    }
}
