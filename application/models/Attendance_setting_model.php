<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_setting_model extends CI_Model {

    protected $table = 'tbl_attendance_settings';
    protected $primaryKey = 'setting_id';

    public function get_settings($school_id = NULL)
    {
        $school_id = $school_id ?: get_current_school_id();
        $settings = $this->db
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        if (!$settings) {
            $default = array(
                'school_id'                  => $school_id,
                'enable_present'              => 1,
                'enable_absent'               => 1,
                'enable_late'                 => 1,
                'enable_excused'              => 1,
                'enable_period_attendance'    => 1,
                'enable_absent_notification'  => 1,
                'enable_late_notification'    => 1,
                'enable_summary_notification' => 1,
                'absent_template'             => 'Dear Parent, your child {student_name} was marked absent on {date}.',
                'late_template'               => 'Dear Parent, your child {student_name} was marked late on {date}.',
                'excused_template'            => 'Dear Parent, your child {student_name} has been excused on {date}.',
                'summary_template'            => 'Attendance summary for {student_name}: Present {present_days}, Absent {absent_days}, Late {late_days}, Excused {excused_days}.',
                'notification_timing'         => 'On Marking',
            );
            $this->db->insert($this->table, $default);
            return (object) $default;
        }

        return $settings;
    }

    public function update_settings($data, $school_id = NULL)
    {
        $school_id = $school_id ?: get_current_school_id();
        $existing = $this->get_settings($school_id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($existing && isset($existing->setting_id)) {
            return $this->db
                ->where($this->primaryKey, $existing->setting_id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
        } else {
            $data['school_id'] = $school_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert($this->table, $data);
        }
    }
}
