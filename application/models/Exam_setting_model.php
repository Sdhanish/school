<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_setting_model extends CI_Model {

    protected $table = 'tbl_examination_settings';
    protected $primaryKey = 'setting_id';

    protected $selectFields = 'setting_id, school_id, decimal_precision, default_max_marks, default_passing_marks, subject_pass_mark_rule, overall_pass_percentage, single_subject_fail_overall, rank_criteria, include_failed_in_rank, show_rank_on_report_card, show_attendance_on_report_card, report_card_header, principal_signature_title, teacher_signature_title, created_at, updated_at, is_deleted';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_settings($school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        $settings = $this->db
            ->select($this->selectFields)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        if (!$settings) {
            $school_row = $this->db->select('school_name')->where('id', $school_id)->get('tbl_schools')->row();
            $school_title = ($school_row && !empty($school_row->school_name)) ? trim($school_row->school_name) : 'School';

            $default = [
                'school_id'                      => $school_id,
                'decimal_precision'              => 2,
                'default_max_marks'              => 100.00,
                'default_passing_marks'          => 35.00,
                'subject_pass_mark_rule'         => 1,
                'overall_pass_percentage'        => 35.00,
                'single_subject_fail_overall'    => 1,
                'rank_criteria'                  => 'Percentage',
                'include_failed_in_rank'         => 0,
                'show_rank_on_report_card'       => 1,
                'show_attendance_on_report_card' => 1,
                'report_card_header'             => $school_title . ' - Official Academic Report Card',
                'principal_signature_title'      => 'Principal',
                'teacher_signature_title'        => 'Class Teacher',
                'created_at'                     => date('Y-m-d H:i:s'),
                'updated_at'                     => date('Y-m-d H:i:s'),
                'is_deleted'                     => 'n'
            ];
            $this->db->insert($this->table, $default);
            $new_id = $this->db->insert_id();
            $default['setting_id'] = $new_id;
            return (object)$default;
        }
        return $settings;
    }

    public function update_settings($data, $school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $exists = $this->db
            ->select('setting_id')
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        if ($exists) {
            return $this->db
                ->where('setting_id', $exists->setting_id)
                ->where('school_id', $school_id)
                ->update($this->table, $data);
        }
        $data['school_id'] = $school_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_deleted'] = 'n';
        return $this->db->insert($this->table, $data);
    }
}
