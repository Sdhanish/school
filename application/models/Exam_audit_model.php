<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_audit_model extends CI_Model {

    protected $table = 'tbl_exam_audit_logs';
    protected $primaryKey = 'log_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function log($user_id, $action, $entity_type, $entity_id, $details = '')
    {
        $school_id = $this->get_school_id();
        $data = [
            'school_id'   => $school_id,
            'user_id'     => $user_id,
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => (int)$entity_id,
            'details'     => $details,
            'created_at'  => date('Y-m-d H:i:s')
        ];
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_logs($limit = 50, $entity_type = NULL, $entity_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('l.*, u.name as user_name, u.user_type as user_role')
            ->from($this->table . ' l')
            ->join('tbl_users u', 'u.user_id = l.user_id', 'left')
            ->where('l.school_id', $school_id)
            ->order_by('l.created_at', 'DESC')
            ->limit($limit);

        if ($entity_type) $this->db->where('l.entity_type', $entity_type);
        if ($entity_id) $this->db->where('l.entity_id', (int)$entity_id);

        return $this->db->get()->result();
    }
}
