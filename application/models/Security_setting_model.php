<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Security_setting_model extends CI_Model {

    protected $table = 'tbl_user_security_settings';
    protected $primaryKey = 'setting_id';

    private function _get_school_id($school_id = null)
    {
        if ($school_id !== null && (int)$school_id > 0) {
            return (int)$school_id;
        }
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_settings($school_id = null)
    {
        $sid = $this->_get_school_id($school_id);
        $row = $this->db->where('school_id', $sid)->get($this->table)->row();
        if (!$row) {
            $default = [
                'school_id'                  => $sid,
                'max_failed_attempts'        => 5,
                'lockout_duration_minutes'   => 30,
                'session_timeout_minutes'    => 120,
                'password_min_length'        => 8,
                'require_special_chars'      => 1,
                'require_numbers'            => 1,
                'password_expiry_days'       => 90,
                'allow_concurrent_sessions'  => 1
            ];
            $this->db->insert($this->table, $default);
            return (object)array_merge(['setting_id' => $this->db->insert_id()], $default);
        }
        return $row;
    }

    public function update_settings($data, $school_id = null)
    {
        $sid = $this->_get_school_id($school_id);
        $data['school_id'] = $sid;
        $exists = $this->db->where('school_id', $sid)->get($this->table)->row();
        if ($exists) {
            return $this->db->where('school_id', $sid)->update($this->table, $data);
        } else {
            return $this->db->insert($this->table, $data);
        }
    }
}
