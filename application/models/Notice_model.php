<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_model extends CI_Model {

    protected $table = 'tbl_notices';
    protected $primaryKey = 'notice_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($filters = array(), $limit = NULL, $offset = 0)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('n.*, n.posted_by AS posted_by_name, n.publish_date AS date, c.class_name, div.division_name as division_name, div.division_name as section_name')
            ->from('tbl_notices n')
            ->join('tbl_classes c', 'c.class_id = n.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = n.division_id', 'left')
            ->where('n.school_id', $school_id)
            ->order_by('n.publish_date', 'DESC')
            ->order_by('n.notice_id', 'DESC');

        if (!empty($filters['academic_year_id'])) $this->db->where('n.academic_year_id', (int)$filters['academic_year_id']);
        if (!empty($filters['category'])) $this->db->where('n.category', $filters['category']);
        if (!empty($filters['priority'])) $this->db->where('n.priority', $filters['priority']);
        if (!empty($filters['target_role']) && $filters['target_role'] !== 'All') $this->db->where('n.target_role', $filters['target_role']);
        if (!empty($filters['status'])) {
            $this->db->where('n.status', $filters['status']);
        } else {
            // Default active
            $this->db->where_in('n.status', ['Published', 'Scheduled']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('n.title', $s)
                ->or_like('n.content', $s)
                ->or_like('n.posted_by', $s)
            ->group_end();
        }

        if ($limit) $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    public function get_recent($limit = 5, $academic_year_id = NULL)
    {
        $school_id = $this->get_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id();
        return $this->db
            ->select('n.*, n.posted_by AS posted_by_name, n.publish_date AS date')
            ->from('tbl_notices n')
            ->where('n.school_id', $school_id)
            ->where('n.academic_year_id', $academic_year_id)
            ->where('n.status', 'Published')
            ->order_by('n.publish_date', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('n.*, n.posted_by AS posted_by_name, n.publish_date AS date, c.class_name, div.division_name as division_name, div.division_name as section_name')
            ->from('tbl_notices n')
            ->join('tbl_classes c', 'c.class_id = n.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = n.division_id', 'left')
            ->where('n.school_id', $school_id)
            ->where('n.' . $this->primaryKey, (int)$id)
            ->get()
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

    public function archive($id)
    {
        return $this->update($id, ['status' => 'Archived']);
    }
}
