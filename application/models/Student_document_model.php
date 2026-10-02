<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_document_model extends CI_Model {

    protected $table = 'tbl_student_documents';
    protected $primaryKey = 'document_id';

    public function get_all($filters = array())
    {
        $this->db->select('sd.document_id, sd.school_id, sd.student_id, sd.category_id, sd.document_type, sd.document_name, sd.document_number, sd.file_path, sd.expiry_date, sd.verification_status, sd.verified_by, sd.status, sd.created_at,
                           st.first_name, st.last_name, st.admission_number, c.class_name, div.division_name as division_name, div.division_name as section_name, dc.category_name, dc.code as category_code, u.username as verifier_name')
            ->from('tbl_student_documents sd')
            ->join('tbl_students st', 'st.student_id = sd.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_document_categories dc', 'dc.category_id = sd.category_id', 'left')
            ->join('tbl_users u', 'u.user_id = sd.verified_by', 'left')
            ->where('sd.status', 1);

        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();
        if (!$is_super && $school_id > 0) {
            $this->db->where('sd.school_id', $school_id);
        }

        if (!empty($filters['student_id'])) {
            $this->db->where('sd.student_id', $filters['student_id']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('sd.category_id', $filters['category_id']);
        }
        if (!empty($filters['verification_status'])) {
            $this->db->where('sd.verification_status', $filters['verification_status']);
        }
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('sd.document_name', $q)
                ->or_like('sd.document_number', $q)
                ->group_end();
        }

        $this->db->order_by('sd.document_id', 'DESC');
        $docs = $this->db->get()->result();

        // Calculate dynamic expiry status
        $today = date('Y-m-d');
        foreach ($docs as &$doc) {
            if ($doc->expiry_date) {
                $days_to_expiry = (strtotime($doc->expiry_date) - strtotime($today)) / 86400;
                if ($days_to_expiry < 0) {
                    $doc->expiry_status = 'Expired';
                } elseif ($days_to_expiry <= 30) {
                    $doc->expiry_status = 'Expiring Soon';
                } else {
                    $doc->expiry_status = 'Active';
                }
            } else {
                $doc->expiry_status = 'Permanent / N/A';
            }
        }

        return $docs;
    }

    public function get_datatables_data($filters = array(), $limit = 25, $start = 0, $order_col = 'sd.document_id', $order_dir = 'DESC')
    {
        $allowed_cols = array(
            0 => 'st.first_name',
            1 => 'dc.category_name',
            2 => 'sd.document_name',
            3 => 'sd.expiry_date',
            4 => 'sd.verification_status',
            5 => 'sd.document_id',
            6 => 'sd.document_id'
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'sd.document_id';
        $dir = (strtoupper($order_dir) === 'ASC') ? 'ASC' : 'DESC';

        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);

        $this->db->select('sd.document_id, sd.school_id, sd.student_id, sd.category_id, sd.document_type, sd.document_name, sd.document_number, sd.file_path, sd.expiry_date, sd.verification_status, sd.verified_by, sd.status, sd.created_at,
                           st.first_name, st.last_name, st.admission_number, c.class_name, div.division_name as division_name, div.division_name as section_name, dc.category_name, dc.code as category_code, u.username as verifier_name')
            ->from('tbl_student_documents sd')
            ->join('tbl_students st', 'st.student_id = sd.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_document_categories dc', 'dc.category_id = sd.category_id', 'left')
            ->join('tbl_users u', 'u.user_id = sd.verified_by', 'left')
            ->where('sd.status', 1)
            ->where('sd.school_id', $school_id);

        if (!empty($filters['student_id'])) {
            $this->db->where('sd.student_id', (int)$filters['student_id']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('sd.category_id', (int)$filters['category_id']);
        }
        if (!empty($filters['verification_status'])) {
            $this->db->where('sd.verification_status', $filters['verification_status']);
        }
        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('sd.document_name', $q)
                ->or_like('sd.document_number', $q)
                ->or_like('c.class_name', $q)
                ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        $docs = $this->db->get()->result();

        $today = date('Y-m-d');
        foreach ($docs as &$doc) {
            if ($doc->expiry_date) {
                $days_to_expiry = (strtotime($doc->expiry_date) - strtotime($today)) / 86400;
                if ($days_to_expiry < 0) {
                    $doc->expiry_status = 'Expired';
                } elseif ($days_to_expiry <= 30) {
                    $doc->expiry_status = 'Expiring Soon';
                } else {
                    $doc->expiry_status = 'Active';
                }
            } else {
                $doc->expiry_status = 'Permanent / N/A';
            }
        }

        return $docs;
    }

    public function count_filtered($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);

        $this->db
            ->from('tbl_student_documents sd')
            ->join('tbl_students st', 'st.student_id = sd.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->where('sd.status', 1)
            ->where('sd.school_id', $school_id);

        if (!empty($filters['student_id'])) {
            $this->db->where('sd.student_id', (int)$filters['student_id']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('sd.category_id', (int)$filters['category_id']);
        }
        if (!empty($filters['verification_status'])) {
            $this->db->where('sd.verification_status', $filters['verification_status']);
        }
        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('sd.document_name', $q)
                ->or_like('sd.document_number', $q)
                ->or_like('c.class_name', $q)
                ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function get_datatables_count_all($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 0);
        return $this->db
            ->where('status', 1)
            ->where('school_id', $school_id)
            ->count_all_results($this->table);
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $is_super  = class_exists('CI', FALSE) && isset(CI::$APP->rbac) && CI::$APP->rbac->is_super_admin();

        $this->db->select('sd.document_id, sd.school_id, sd.student_id, sd.category_id, sd.document_type, sd.document_name, sd.document_number, sd.file_path, sd.expiry_date, sd.verification_status, sd.verified_by, sd.status, sd.created_at,
                           st.first_name, st.last_name, st.admission_number, c.class_name, div.division_name as division_name, div.division_name as section_name, dc.category_name')
            ->from('tbl_student_documents sd')
            ->join('tbl_students st', 'st.student_id = sd.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_document_categories dc', 'dc.category_id = sd.category_id', 'left')
            ->where('sd.document_id', $id);

        if (!$is_super && $school_id > 0) {
            $this->db->where('sd.school_id', $school_id);
        }

        return $this->db->get()->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 0;
        $q = $this->db->where($this->primaryKey, $id);
        if ($school_id > 0) {
            $q->where('school_id', $school_id);
        }
        return $q->update($this->table, $data);
    }

    public function verify_document($id, $status, $reason = null, $user_id = null)
    {
        $data = array(
            'verification_status' => $status,
            'rejection_reason'   => ($status === 'Rejected') ? $reason : null,
            'verified_by'        => $user_id,
            'verified_at'        => date('Y-m-d H:i:s')
        );
        return $this->update($id, $data);
    }
}
