<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grade_model extends CI_Model {

    protected $table = 'tbl_grades';
    protected $primaryKey = 'grade_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) {
            $this->db->where('status', 1);
        }
        return $this->db->order_by('min_percentage', 'DESC')->get()->result();
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

    public function resolve_grade_for_percentage($percentage)
    {
        $school_id = $this->get_school_id();
        $percentage = (float)$percentage;
        $grade = $this->db
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->where('status', 1)
            ->where('min_percentage <=', $percentage)
            ->where('max_percentage >=', $percentage)
            ->order_by('min_percentage', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        if ($grade) {
            return $grade;
        }

        // Fallback lowest grade within school
        $lowest = $this->db
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->where('status', 1)
            ->order_by('min_percentage', 'ASC')
            ->limit(1)
            ->get($this->table)
            ->row();
        if ($lowest) return $lowest;

        return (object) [
            'grade_name'     => ($percentage >= 35) ? 'P' : 'F',
            'grade_point'    => ($percentage >= 35) ? 4.00 : 0.00,
            'description'    => ($percentage >= 35) ? 'Pass' : 'Fail',
            'min_percentage' => 0,
            'max_percentage' => 100
        ];
    }

    public function check_overlap($min_pct, $max_pct, $exclude_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        $this->db->where('status', 1);
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        $this->db->where('min_percentage <', $max_pct);
        $this->db->where('max_percentage >', $min_pct);
        return $this->db->get($this->table)->row();
    }
}
