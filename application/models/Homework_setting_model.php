<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Homework_setting_model extends CI_Model {

    protected $table = 'tbl_homework_settings';
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
                'setting_id'                       => 1,
                'school_id'                        => $school_id,
                'default_submission_deadline_days' => 3,
                'allow_late_submissions_default'  => 1,
                'max_upload_size_mb'               => 10,
                'allowed_file_extensions'          => 'pdf,doc,docx,jpg,jpeg,png,zip,txt',
                'enable_grading'                   => 1,
                'enable_parent_notifications'      => 1
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
            return $this->db->where('school_id', $school_id)->update($this->table, $data);
        } else {
            $data['school_id'] = $school_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert($this->table, $data);
        }
    }

    public function get_audit_logs($limit = 50)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('log.*, s.full_name as user_name')
            ->from('tbl_homework_audit_logs log')
            ->join('tbl_staff s', 's.staff_id = log.user_id', 'left')
            ->where('log.school_id', $school_id)
            ->order_by('log.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
