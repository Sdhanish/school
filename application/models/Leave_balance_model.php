<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_balance_model extends CI_Model {

    protected $table = 'tbl_leave_balances';
    protected $primaryKey = 'balance_id';

    protected function get_school_id($school_id = NULL)
    {
        if ($school_id !== NULL && (int)$school_id > 0) {
            return (int)$school_id;
        }
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $school_id = NULL)
    {
        $sid = $this->get_school_id($school_id);
        $bal = $this->db
            ->where('school_id', $sid)
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('entity_type', $entity_type)
            ->where('entity_id', (int)$entity_id)
            ->where('leave_type_id', (int)$leave_type_id)
            ->get($this->table)
            ->row();

        if (!$bal) {
            // Auto-initialize balance from leave type default max_days
            $lt = $this->db
                ->where('school_id', $sid)
                ->where('type_id', (int)$leave_type_id)
                ->get('tbl_leave_types')
                ->row();

            $max_days = $lt ? (float)$lt->max_days : 12.0;

            $this->db->insert($this->table, [
                'school_id'          => $sid,
                'academic_year_id'   => (int)$academic_year_id,
                'entity_type'        => $entity_type,
                'entity_id'          => (int)$entity_id,
                'leave_type_id'      => (int)$leave_type_id,
                'allocated_days'     => $max_days,
                'used_days'          => 0.0,
                'pending_days'       => 0.0,
                'carry_forward_days' => 0.0,
                'created_at'         => date('Y-m-d H:i:s')
            ]);
            $bal = $this->get_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $sid);
        }

        $bal->remaining_days = max(0, ($bal->allocated_days + $bal->carry_forward_days) - $bal->used_days);
        return $bal;
    }

    public function deduct_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $days, $school_id = NULL)
    {
        $sid = $this->get_school_id($school_id);
        $bal = $this->get_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $sid);
        $new_used = $bal->used_days + (float)$days;

        return $this->db
            ->where('balance_id', $bal->balance_id)
            ->where('school_id', $sid)
            ->update($this->table, [
                'used_days'  => $new_used,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function restore_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $days, $school_id = NULL)
    {
        $sid = $this->get_school_id($school_id);
        $bal = $this->get_balance($academic_year_id, $entity_type, $entity_id, $leave_type_id, $sid);
        $new_used = max(0.0, $bal->used_days - (float)$days);

        return $this->db
            ->where('balance_id', $bal->balance_id)
            ->where('school_id', $sid)
            ->update($this->table, [
                'used_days'  => $new_used,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function get_all_balances($academic_year_id = NULL, $entity_type = 'Staff', $limit = 50, $school_id = NULL)
    {
        $sid = $this->get_school_id($school_id);
        if ($academic_year_id === NULL) {
            $this->load->model('Academic_year_model');
            $active = $this->Academic_year_model->get_active($sid);
            $academic_year_id = $active ? (int)$active->academic_year_id : 1;
        }

        $this->db
            ->select('b.*, lt.type_name, lt.type_code, s.full_name as staff_name, s.employee_code, des.designation_name, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, c.class_name, div.division_name as division_name, div.division_name as section_name')
            ->from('tbl_leave_balances b')
            ->join('tbl_leave_types lt', 'lt.type_id = b.leave_type_id AND lt.school_id = b.school_id', 'left')
            ->join('tbl_staff s', "s.staff_id = b.entity_id AND b.entity_type = 'Staff' AND s.school_id = b.school_id", 'left')
            ->join('tbl_designations des', 'des.designation_id = s.designation_id AND des.school_id = b.school_id', 'left')
            ->join('tbl_students st', "st.student_id = b.entity_id AND b.entity_type = 'Student' AND st.school_id = b.school_id", 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id AND c.school_id = b.school_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id AND div.school_id = b.school_id', 'left')
            ->where('b.school_id', $sid)
            ->where('b.academic_year_id', (int)$academic_year_id)
            ->where('b.entity_type', $entity_type)
            ->limit($limit);

        $results = $this->db->get()->result();
        foreach ($results as &$r) {
            $r->remaining_days = max(0, ($r->allocated_days + $r->carry_forward_days) - $r->used_days);
        }
        return $results;
    }
}
