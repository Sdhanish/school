<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communication_setting_model extends CI_Model {

    protected $table = 'tbl_communication_settings';
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
            return (object)[
                'setting_id'                      => 1,
                'school_id'                       => $school_id,
                'enable_inapp'                    => 1,
                'enable_sms'                      => 1,
                'enable_whatsapp'                 => 1,
                'enable_email'                    => 1,
                'sms_provider'                    => 'Generic SMS Gateway',
                'sms_sender_id'                   => 'SCHOLL',
                'whatsapp_provider'               => 'WhatsApp Business API',
                'email_from_name'                 => 'Login2 Management',
                'email_from_address'              => 'notifications@school.edu',
                'enable_scheduled_jobs'           => 1,
                'max_retries'                     => 3,
                'retry_interval_minutes'          => 15,
                'parent_teacher_direct_messaging' => 1
            ];
        }
        return $settings;
    }

    public function update_settings($data)
    {
        $school_id = $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $existing = $this->db->where('school_id', $school_id)->get($this->table)->row();
        if ($existing) {
            return $this->db
                ->where('setting_id', $existing->setting_id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
        } else {
            $data['school_id'] = $school_id;
            return $this->db->insert($this->table, $data);
        }
    }

    public function get_audit_logs($limit = 30)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('log.*, s.full_name as user_name')
            ->from('tbl_communication_audit_logs log')
            ->join('tbl_staff s', 's.staff_id = log.user_id', 'left')
            ->where('log.school_id', $school_id)
            ->order_by('log.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
