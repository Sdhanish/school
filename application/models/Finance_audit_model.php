<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_audit_model extends CI_Model {

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function log($action, $entity_type, $entity_id, $details = '', $previous_val = null, $new_val = null)
    {
        $school_id = $this->get_school_id();
        $user_id = $this->session->userdata('user_id');
        $this->db->insert('tbl_finance_audit_logs', array(
            'school_id'      => $school_id,
            'user_id'        => $user_id,
            'action'         => $action,
            'entity_type'    => $entity_type,
            'entity_id'      => $entity_id,
            'details'        => $details,
            'previous_value' => is_array($previous_val) ? json_encode($previous_val) : $previous_val,
            'new_value'      => is_array($new_val) ? json_encode($new_val) : $new_val,
            'created_at'     => date('Y-m-d H:i:s')
        ));
    }

    public function get_logs($limit = 50)
    {
        $school_id = $this->get_school_id();
        return $this->db->select('al.*, u.name as user_name')
                        ->from('tbl_finance_audit_logs al')
                        ->join('tbl_users u', 'u.user_id = al.user_id', 'left')
                        ->where('al.school_id', $school_id)
                        ->order_by('al.log_id', 'DESC')
                        ->limit($limit)
                        ->get()
                        ->result();
    }
}
