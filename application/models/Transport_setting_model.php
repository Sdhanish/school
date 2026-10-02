<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transport_setting_model extends CI_Model {

    protected $table = 'tbl_transport_settings';
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
                'setting_id'                   => 1,
                'school_id'                    => $school_id,
                'enable_transport'             => 1,
                'enforce_capacity'             => 1,
                'allow_capacity_override'      => 0,
                'default_monthly_fee'          => 1500.00,
                'fee_frequency'                => 'Monthly',
                'maintenance_reminder_days'    => 15,
                'document_expiry_reminder_days'=> 30,
                'driver_license_reminder_days' => 30,
                'allow_one_way'                => 1,
                'allow_pickup_only'            => 1,
                'allow_drop_only'              => 1,
                'allow_bulk_assignment'        => 1
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
        }
        $data['school_id'] = $school_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function get_audit_logs($limit = 30)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('log.*, s.full_name as user_name')
            ->from('tbl_transport_audit_logs log')
            ->join('tbl_staff s', 's.staff_id = log.user_id', 'left')
            ->where('log.school_id', $school_id)
            ->order_by('log.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }
}
