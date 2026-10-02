<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_setting_model extends CI_Model {

    protected $table = 'tbl_leave_settings';
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
                'setting_id'               => 1,
                'school_id'                => $school_id,
                'enable_student_leave'     => 1,
                'enable_staff_leave'       => 1,
                'enable_half_day'          => 1,
                'working_days_only'        => 1,
                'student_approval_workflow'=> 'Class Teacher -> Principal',
                'staff_approval_workflow'  => 'Supervisor -> Principal',
                'enable_balance_tracking'  => 1,
                'allow_carry_forward'      => 1,
                'max_carry_forward_days'   => 5,
                'require_document_default' => 0,
                'max_file_size_mb'         => 10
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
            ->from('tbl_leave_audit_logs log')
            ->join('tbl_staff s', 's.staff_id = log.user_id', 'left')
            ->where('log.school_id', $school_id)
            ->order_by('log.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
