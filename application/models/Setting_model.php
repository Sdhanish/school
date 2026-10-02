<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model {

    protected $table = 'tbl_school_settings';
    protected $primaryKey = 'setting_id';

    public function get_settings($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $settings = $this->db
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        if (!$settings) {
            // Check tbl_schools to pre-fill settings for this school
            $school = $this->db->where('id', $school_id)->get('tbl_schools')->row();
            if ($school) {
                $new_settings = array(
                    'school_id'        => $school_id,
                    'school_name'      => $school->school_name,
                    'school_code'      => $school->school_code,
                    'established_year' => date('Y'),
                    'principal_name'   => 'Principal',
                    'phone'            => $school->phone ?: '',
                    'email'            => $school->email ?: '',
                    'website'          => '',
                    'logo'             => $school->logo ?: '',
                    'address'          => $school->address ?: '',
                    'description'      => $school->school_name,
                    'is_deleted'       => 'n',
                );
                $this->db->insert($this->table, $new_settings);
                $new_settings['setting_id'] = $this->db->insert_id();
                return (object)$new_settings;
            }

            // Global fallback
            $settings = $this->db
                ->limit(1)
                ->get($this->table)
                ->row();
        }

        return $settings;
    }

    public function update_settings($data, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $data['school_id'] = $school_id;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $existing = $this->db->where('school_id', $school_id)->where('is_deleted', 'n')->get($this->table)->row();
        if ($existing) {
            $result = $this->db
                ->where('setting_id', $existing->setting_id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
        } else {
            $result = $this->db->insert($this->table, $data);
        }

        // Keep tbl_schools in sync for primary branding fields
        $school_sync = array();
        if (isset($data['school_name'])) $school_sync['school_name'] = $data['school_name'];
        if (isset($data['phone']))       $school_sync['phone']       = $data['phone'];
        if (isset($data['email']))       $school_sync['email']       = $data['email'];
        if (isset($data['address']))     $school_sync['address']     = $data['address'];
        if (isset($data['logo']))        $school_sync['logo']        = $data['logo'];

        if (!empty($school_sync)) {
            $this->db->where('id', $school_id)->update('tbl_schools', $school_sync);
            if (function_exists('clear_school_cache')) {
                clear_school_cache($school_id);
            }
        }

        return $result;
    }
}
