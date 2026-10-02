<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_request_model extends CI_Model {

    protected $table = 'tbl_certificate_requests';
    protected $primaryKey = 'request_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db->select('cr.*, st.first_name, st.last_name, st.admission_number, st.guardian_name, st.guardian_phone, c.class_name, div.division_name as division_name, div.division_name as section_name, ct.type_name, ct.type_code, ct.prefix, ay.year_name, u.username as requester_name')
            ->from('tbl_certificate_requests cr')
            ->join('tbl_students st', 'st.student_id = cr.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_certificate_types ct', 'ct.type_id = cr.certificate_type_id', 'left')
            ->join('tbl_academic_years ay', 'ay.academic_year_id = cr.academic_year_id', 'left')
            ->join('tbl_users u', 'u.user_id = cr.requested_by', 'left')
            ->where('cr.school_id', $school_id);

        if (!empty($filters['status'])) {
            $this->db->where('cr.status', $filters['status']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('cr.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['certificate_type_id'])) {
            $this->db->where('cr.certificate_type_id', $filters['certificate_type_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('st.class_id', $filters['class_id']);
        }
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('cr.reason', $q)
                ->group_end();
        }

        $this->db->order_by('cr.request_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db->select('cr.*, st.first_name, st.last_name, st.admission_number, st.date_of_birth, st.gender, st.address as student_address, st.guardian_name, st.guardian_phone, st.created_at as admission_date, c.class_name, div.division_name as division_name, div.division_name as section_name, ct.type_name, ct.type_code, ct.prefix, ay.year_name, u.username as requester_name')
            ->from('tbl_certificate_requests cr')
            ->join('tbl_students st', 'st.student_id = cr.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_certificate_types ct', 'ct.type_id = cr.certificate_type_id', 'left')
            ->join('tbl_academic_years ay', 'ay.academic_year_id = cr.academic_year_id', 'left')
            ->join('tbl_users u', 'u.user_id = cr.requested_by', 'left')
            ->where('cr.school_id', $school_id)
            ->where('cr.request_id', (int)$id)
            ->get()
            ->row();
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

    public function update_status($id, $status, $reason = null, $user_id = null)
    {
        $data = array('status' => $status);
        if ($reason !== null) {
            $data['rejection_reason'] = $reason;
        }
        if ($status === 'Approved' && $user_id) {
            $data['approved_by'] = $user_id;
        }
        if ($status === 'Under Verification' && $user_id) {
            $data['verified_by'] = $user_id;
        }
        return $this->update($id, $data);
    }
}
