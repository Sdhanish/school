<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_template_model extends CI_Model {

    protected $table = 'tbl_certificate_templates';
    protected $primaryKey = 'template_id';

    public $supported_variables = array(
        '{school_name}',
        '{school_code}',
        '{school_address}',
        '{school_phone}',
        '{school_email}',
        '{principal_name}',
        '{student_name}',
        '{admission_number}',
        '{roll_number}',
        '{date_of_birth}',
        '{gender}',
        '{class}',
        '{section}',
        '{academic_year}',
        '{admission_date}',
        '{issue_date}',
        '{certificate_number}',
        '{parent_name}',
        '{guardian_phone}',
        '{student_address}',
        '{date_of_leaving}',
        '{reason_for_leaving}',
        '{attendance_summary}',
        '{conduct_statement}',
    );

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($status = null)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('template_id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->get($this->table)
            ->row();
    }

    public function get_by_type_code($type_code)
    {
        $school_id = $this->get_school_id();
        $res = $this->db
            ->where('type_code', $type_code)
            ->where('school_id', $school_id)
            ->where('status', 'Active')
            ->order_by('template_id', 'ASC')
            ->get($this->table)
            ->row();

        if (!$res) {
            // Fallback to default
            $res = $this->db
                ->where('type_code', $type_code)
                ->where('status', 'Active')
                ->order_by('template_id', 'ASC')
                ->get($this->table)
                ->row();
        }

        return $res;
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    /**
     * Validate whether template text only uses known tokens
     */
    public function validate_template_variables($text)
    {
        preg_match_all('/\{[a-zA-Z0-9_]+\}/', $text, $matches);
        $invalid = array();
        if (!empty($matches[0])) {
            foreach ($matches[0] as $var) {
                if (!in_array($var, $this->supported_variables)) {
                    $invalid[] = $var;
                }
            }
        }
        return $invalid;
    }
}
