<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_setting_model extends CI_Model {

    protected $table = 'tbl_certificate_settings';
    protected $primaryKey = 'setting_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_settings()
    {
        $school_id = $this->get_school_id();
        $settings = $this->db->where('school_id', $school_id)->get($this->table)->row();
        if (!$settings) {
            $settings = $this->db->where($this->primaryKey, 1)->get($this->table)->row();
        }
        if (!$settings) {
            $default = array(
                'school_id' => $school_id,
                'numbering_format' => '{PREFIX}{YEAR}-{NUMBER}',
                'number_sequence_length' => 5,
                'require_approval' => 1,
                'require_document_verification' => 0,
                'require_fee_clearance_for_tc' => 1,
                'require_library_clearance_for_tc' => 0,
                'require_transport_clearance_for_tc' => 0,
                'watermark_enabled' => 1,
                'default_paper_size' => 'A4',
                'default_orientation' => 'Portrait',
                'document_expiry_reminder_days' => 30
            );
            $this->db->insert($this->table, $default);
            return (object)$default;
        }
        return $settings;
    }

    public function save_settings($data)
    {
        $school_id = $this->get_school_id();
        $exists = $this->db->where('school_id', $school_id)->get($this->table)->row();
        if ($exists) {
            return $this->db->where('school_id', $school_id)->update($this->table, $data);
        } else {
            $data['school_id'] = $school_id;
            return $this->db->insert($this->table, $data);
        }
    }

    public function log_audit($action, $entity_type, $entity_id, $details = null, $user_id = null)
    {
        $school_id = $this->get_school_id();
        $data = array(
            'school_id'   => $school_id,
            'user_id'     => $user_id,
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => (int)$entity_id,
            'details'     => is_array($details) ? json_encode($details) : $details,
            'created_at'  => date('Y-m-d H:i:s')
        );
        return $this->db->insert('tbl_certificate_audit_logs', $data);
    }

    public function get_audit_logs($limit = 50)
    {
        $school_id = $this->get_school_id();
        return $this->db->select('al.*, u.username')
            ->from('tbl_certificate_audit_logs al')
            ->join('tbl_users u', 'u.user_id = al.user_id', 'left')
            ->where('al.school_id', $school_id)
            ->order_by('al.log_id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
