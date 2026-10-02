<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_model extends CI_Model {

    protected $table = 'tbl_staff';
    protected $primaryKey = 'staff_id';

    public function get_dashboard_stats($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        $total_staff = $this->db->where('school_id', $school_id)->where('is_deleted', 'n')->count_all_results('tbl_staff');
        $active_staff = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_staff');
        $inactive_staff = $this->db->where('school_id', $school_id)->where('status', 0)->where('is_deleted', 'n')->count_all_results('tbl_staff');

        $total_teachers = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')
            ->group_start()
                ->where('staff_type', 'Teacher')
                ->or_where('staff_type', 'teaching')
                ->or_where('category', 'Teaching')
            ->group_end()
            ->count_all_results('tbl_staff');

        $non_teaching = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')
            ->group_start()
                ->where('staff_type', 'non_teaching')
                ->or_where('category', 'Non-Teaching')
            ->group_end()
            ->count_all_results('tbl_staff');

        // Recent staff
        $recent_staff = $this->db
            ->select('s.staff_id, s.school_id, s.employee_code, s.full_name, s.gender, s.email, s.phone, s.photo, s.status, s.staff_type, s.category, dg.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.status', 1)
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id)
            ->order_by('s.staff_id', 'DESC')
            ->limit(8)
            ->get()
            ->result();

        $total_designations = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_designations');

        // Designation breakdown
        $designations = $this->db
            ->select('dg.designation_id, dg.designation_name, dg.category, COUNT(s.staff_id) as staff_count')
            ->from('tbl_designations dg')
            ->join('tbl_staff s', 's.designation_id = dg.designation_id AND s.school_id = dg.school_id AND s.status = 1 AND s.is_deleted = \'n\'', 'left')
            ->where('dg.school_id', $school_id)
            ->where('dg.status', 1)
            ->where('dg.is_deleted', 'n')
            ->group_by('dg.designation_id, dg.designation_name, dg.category')
            ->order_by('staff_count', 'DESC')
            ->limit(8)
            ->get()
            ->result();

        return (object)[
            'total_staff'        => $total_staff,
            'active_staff'       => $active_staff,
            'inactive_staff'     => $inactive_staff,
            'total_teachers'     => $total_teachers,
            'non_teaching_staff' => $non_teaching,
            'total_designations' => $total_designations,
            'designations'       => $designations,
            'recent_staff'       => $recent_staff,
        ];
    }

    public function get_all($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('s.staff_id, s.school_id, s.employee_code, s.full_name, s.gender, s.email, s.phone, s.photo, s.status, s.staff_type, s.category, s.academic_group_id, ag.group_name,
                      SUBSTRING_INDEX(s.full_name, " ", 1) AS first_name,
                      TRIM(SUBSTRING(s.full_name, LENGTH(SUBSTRING_INDEX(s.full_name, " ", 1)) + 1)) AS last_name,
                      s.category AS designation_category,
                      dg.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.status >=', 0)
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id)
            ->order_by('s.staff_id', 'ASC');

        if (!empty($filters['staff_type'])) {
            $this->db->where('s.staff_type', $filters['staff_type']);
        }
        if (!empty($filters['academic_group_id'])) {
            $this->db->where('s.academic_group_id', (int)$filters['academic_group_id']);
        }
        if (!empty($filters['designation_id'])) {
            $this->db->where('s.designation_id', $filters['designation_id']);
        }
        if (!empty($filters['employment_status'])) {
            $this->db->where('s.employment_status', $filters['employment_status']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('s.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('s.full_name', $s)
                ->or_like('s.employee_code', $s)
                ->or_like('s.email', $s)
                ->or_like('s.phone', $s)
                ->group_end();
        }

        return $this->db->get()->result();
    }

    public function count_filtered($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->from('tbl_staff s')
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id);

        if (!empty($filters['staff_type'])) {
            $this->db->where('s.staff_type', $filters['staff_type']);
        }
        if (!empty($filters['academic_group_id'])) {
            $this->db->where('s.academic_group_id', (int)$filters['academic_group_id']);
        }
        if (!empty($filters['designation_id'])) {
            $this->db->where('s.designation_id', (int)$filters['designation_id']);
        }
        if (!empty($filters['employment_status'])) {
            $this->db->where('s.employment_status', $filters['employment_status']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('s.status', (int)$filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('s.full_name', $s)
                ->or_like('s.employee_code', $s)
                ->or_like('s.email', $s)
                ->or_like('s.phone', $s)
                ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function get_datatables_data($filters = array(), $limit = 25, $start = 0, $order_col = 's.staff_id', $order_dir = 'ASC')
    {
        $allowed_cols = array(
            0 => 's.employee_code',
            1 => 's.full_name',
            2 => 'ag.group_name',
            3 => 'dg.designation_name',
            4 => 's.email',
            5 => 's.status',
            6 => 's.staff_id'
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 's.staff_id';
        $dir = (strtoupper($order_dir) === 'DESC') ? 'DESC' : 'ASC';

        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('s.staff_id, s.employee_code, s.full_name, s.gender, s.email, s.phone, s.photo, s.status, s.staff_type, s.academic_group_id, ag.group_name,
                      dg.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id);

        if (!empty($filters['staff_type'])) {
            $this->db->where('s.staff_type', $filters['staff_type']);
        }
        if (!empty($filters['academic_group_id'])) {
            $this->db->where('s.academic_group_id', (int)$filters['academic_group_id']);
        }
        if (!empty($filters['designation_id'])) {
            $this->db->where('s.designation_id', (int)$filters['designation_id']);
        }
        if (!empty($filters['employment_status'])) {
            $this->db->where('s.employment_status', $filters['employment_status']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('s.status', (int)$filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('s.full_name', $s)
                ->or_like('s.employee_code', $s)
                ->or_like('s.email', $s)
                ->or_like('s.phone', $s)
                ->or_like('dg.designation_name', $s)
                ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function get_datatables_count_all($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();
        return $this->db->where('is_deleted', 'n')->where('school_id', $school_id)->count_all_results('tbl_staff');
    }

    public function get_teachers($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('s.staff_id, s.school_id, s.employee_code, s.full_name, s.gender, s.email, s.phone, s.photo, s.qualification, s.experience, s.specialization, s.joining_date, s.status, s.staff_type, s.category, s.academic_group_id, ag.group_name,
                      SUBSTRING_INDEX(s.full_name, " ", 1) AS first_name,
                      TRIM(SUBSTRING(s.full_name, LENGTH(SUBSTRING_INDEX(s.full_name, " ", 1)) + 1)) AS last_name,
                      s.category AS designation_category,
                      dg.designation_name,
                      (SELECT GROUP_CONCAT(DISTINCT sub.subject_name SEPARATOR ", ") FROM tbl_subjects sub WHERE sub.teacher_id = s.staff_id AND sub.status = 1 AND sub.school_id = ' . (int)$school_id . ') as subjects_handled,
                      (SELECT GROUP_CONCAT(DISTINCT sub.subject_name SEPARATOR ", ") FROM tbl_subjects sub WHERE sub.teacher_id = s.staff_id AND sub.status = 1 AND sub.school_id = ' . (int)$school_id . ') as subject_specialization,
                      (SELECT GROUP_CONCAT(DISTINCT c.class_name SEPARATOR ", ") FROM tbl_subjects sub JOIN tbl_classes c ON c.class_id = sub.class_id WHERE sub.teacher_id = s.staff_id AND sub.status = 1 AND sub.school_id = ' . (int)$school_id . ') as classes_handled,
                      (SELECT GROUP_CONCAT(DISTINCT CONCAT(c.class_name, " - ", `div`.division_name) SEPARATOR ", ") FROM tbl_divisions `div` JOIN tbl_classes c ON c.class_id = `div`.class_id WHERE (`div`.class_teacher_id = s.staff_id OR `div`.division_id IN (SELECT ct.division_id FROM tbl_class_teachers ct WHERE ct.staff_id = s.staff_id AND ct.status = 1 AND ct.is_deleted = "n" AND ct.school_id = ' . (int)$school_id . ')) AND `div`.status = 1 AND `div`.is_deleted = "n" AND `div`.school_id = ' . (int)$school_id . ') as sections_handled')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.status >=', 0)
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id)
            ->group_start()
                ->where('s.staff_type', 'Teacher')
                ->or_where('s.staff_type', 'teacher')
                ->or_where('s.category', 'Teaching')
            ->group_end();

        if (!empty($filters['academic_group_id'])) {
            $this->db->where('s.academic_group_id', (int)$filters['academic_group_id']);
        }
        if (!empty($filters['designation_id'])) {
            $this->db->where('s.designation_id', $filters['designation_id']);
        }
        if (!empty($filters['employment_status'])) {
            $this->db->where('s.employment_status', $filters['employment_status']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'All') {
            $this->db->where('s.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('s.full_name', $s)
                ->or_like('s.employee_code', $s)
                ->or_like('s.email', $s)
                ->or_like('s.phone', $s)
                ->group_end();
        }
        if (!empty($filters['subject_name'])) {
            $this->db->having('subjects_handled LIKE', '%' . $filters['subject_name'] . '%');
        }

        $results = $this->db->get()->result();
        foreach ($results as $row) {
            $row->divisions_handled = $row->sections_handled;
        }
        return $results;
    }

    public function get_teaching_staff($filters = array())
    {
        return $this->get_teachers($filters);
    }

    public function get_non_teaching($filters = array())
    {
        $school_id = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('s.staff_id, s.school_id, s.employee_code, s.full_name, s.gender, s.email, s.phone, s.photo, s.qualification, s.experience, s.specialization, s.joining_date, s.status, s.staff_type, s.category, s.academic_group_id, ag.group_name,
                      SUBSTRING_INDEX(s.full_name, " ", 1) AS first_name,
                      TRIM(SUBSTRING(s.full_name, LENGTH(SUBSTRING_INDEX(s.full_name, " ", 1)) + 1)) AS last_name,
                      s.category AS designation_category,
                      dg.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.status >=', 0)
            ->where('s.is_deleted', 'n')
            ->where('s.school_id', $school_id)
            ->where('s.staff_type', 'non_teaching')
            ->order_by('s.staff_id', 'ASC');
        if (!empty($filters['designation_id'])) {
            $this->db->where('s.designation_id', $filters['designation_id']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('s.full_name', $s)
                ->or_like('s.employee_code', $s)
                ->or_like('s.email', $s)
                ->or_like('s.phone', $s)
                ->group_end();
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        return $this->db
            ->select('s.staff_id, s.school_id, s.employee_code, s.full_name, s.gender, s.date_of_birth, s.blood_group, s.phone, s.alternate_phone, s.email, s.address, s.staff_type, s.academic_group_id, s.category, s.designation_id, s.joining_date, s.salary, s.qualification, s.experience, s.specialization, s.employment_status, s.photo, s.status, s.created_at, s.updated_at, s.is_deleted,
                      ag.group_name,
                      SUBSTRING_INDEX(s.full_name, " ", 1) AS first_name,
                      TRIM(SUBSTRING(s.full_name, LENGTH(SUBSTRING_INDEX(s.full_name, " ", 1)) + 1)) AS last_name,
                      s.category AS designation_category,
                      dg.designation_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = s.school_id', 'left')
            ->where('s.staff_id', (int)$id)
            ->where('s.school_id', $school_id)
            ->where('s.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function get_profile($id)
    {
        $staff = $this->get_by_id($id);
        if (!$staff) return NULL;
        $school_id = (int)$staff->school_id;

        // 1. Documents
        $staff->documents = $this->db
            ->where('staff_id', $id)
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->order_by('document_id', 'DESC')
            ->get('tbl_staff_documents')
            ->result();

        // 2. Workload (Only for teachers)
        if ($staff->staff_type === 'teacher') {
            $staff->workload = $this->db
                ->select('w.*, sub.subject_name, c.class_name, div.division_name, y.year_name')
                ->from('tbl_teacher_workload w')
                ->join('tbl_subjects sub', 'sub.subject_id = w.subject_id AND sub.school_id = ' . $school_id, 'left')
                ->join('tbl_classes c', 'c.class_id = w.class_id AND c.school_id = ' . $school_id, 'left')
                ->join('tbl_divisions div', 'div.division_id = w.division_id AND div.school_id = ' . $school_id, 'left')
                ->join('tbl_academic_years y', 'y.academic_year_id = w.academic_year_id AND y.school_id = ' . $school_id, 'left')
                ->where('w.staff_id', $id)
                ->where('w.school_id', $school_id)
                ->where('w.status', 1)
                ->where('w.is_deleted', 'n')
                ->order_by('w.workload_id', 'DESC')
                ->get()
                ->result();
        } else {
            $staff->workload = array();
        }

        // 3. Attendance Summary
        $att_total = $this->db->where('staff_id', $id)->where('school_id', $school_id)->count_all_results('tbl_staff_attendance');
        $att_present = $this->db->where('staff_id', $id)->where('school_id', $school_id)->where('attendance_status', 'Present')->count_all_results('tbl_staff_attendance');
        $att_leave = $this->db->where('staff_id', $id)->where('school_id', $school_id)->where('attendance_status', 'Leave')->count_all_results('tbl_staff_attendance');
        $att_absent = $this->db->where('staff_id', $id)->where('school_id', $school_id)->where('attendance_status', 'Absent')->count_all_results('tbl_staff_attendance');
        $staff->attendance = (object) array(
            'total_days' => $att_total,
            'present'    => $att_present,
            'leave'      => $att_leave,
            'absent'     => $att_absent,
            'percentage' => ($att_total > 0) ? round(($att_present / $att_total) * 100, 1) : 100
        );

        // 4. Leave History
        $staff->leaves = $this->db
            ->where('staff_id', $id)
            ->where('school_id', $school_id)
            ->order_by('leave_id', 'DESC')
            ->get('tbl_staff_leave')
            ->result();

        return $staff;
    }

    /* =========================================================================
       Staff Documents
       ========================================================================= */
    public function get_all_documents($filters = array(), $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : (!empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id());
        $this->db
            ->select('d.document_id, d.staff_id, d.school_id, d.document_type_id, d.document_type, d.document_name, d.file_path, d.created_at, d.status,
                      s.employee_code, s.full_name, s.staff_type, desig.designation_name, dt.document_name as type_name')
            ->from('tbl_staff_documents d')
            ->join('tbl_staff s', 's.staff_id = d.staff_id AND s.school_id = ' . (int)$sid, 'left')
            ->join('tbl_designations desig', 'desig.designation_id = s.designation_id AND desig.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff_document_types dt', 'dt.id = d.document_type_id AND dt.school_id = ' . (int)$sid, 'left')
            ->where('d.school_id', (int)$sid)
            ->where('d.status', 1)
            ->where('d.is_deleted', 'n')
            ->order_by('d.document_id', 'DESC');

        if (!empty($filters['staff_id'])) {
            $this->db->where('d.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['document_type_id'])) {
            $this->db->where('d.document_type_id', (int)$filters['document_type_id']);
        }
        if (!empty($filters['document_type'])) {
            $this->db->group_start()
                ->where('d.document_type', $filters['document_type'])
                ->or_where('d.document_name', $filters['document_type'])
                ->or_where('dt.document_name', $filters['document_type'])
                ->group_end();
        }

        return $this->db->get()->result();
    }

    public function get_documents_datatables_data($filters = array(), $limit = 25, $start = 0, $order_col = 'd.document_id', $order_dir = 'DESC')
    {
        $allowed_cols = array(
            0 => 'd.document_name',
            1 => 'd.document_type',
            2 => 's.full_name',
            3 => 's.employee_code',
            4 => 'desig.designation_name',
            5 => 'd.created_at',
            6 => 'd.document_id'
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'd.document_id';
        $dir = (strtoupper($order_dir) === 'ASC') ? 'ASC' : 'DESC';

        $sid = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->select('d.document_id, d.staff_id, d.school_id, d.document_type_id, d.document_type, d.document_name, d.file_path, d.created_at, d.status,
                      s.employee_code, s.full_name, s.staff_type, desig.designation_name, dt.document_name as type_name')
            ->from('tbl_staff_documents d')
            ->join('tbl_staff s', 's.staff_id = d.staff_id AND s.school_id = ' . (int)$sid, 'left')
            ->join('tbl_designations desig', 'desig.designation_id = s.designation_id AND desig.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff_document_types dt', 'dt.id = d.document_type_id AND dt.school_id = ' . (int)$sid, 'left')
            ->where('d.school_id', (int)$sid)
            ->where('d.status', 1)
            ->where('d.is_deleted', 'n');

        if (!empty($filters['staff_id'])) {
            $this->db->where('d.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['document_type'])) {
            $this->db->group_start()
                ->where('d.document_type', $filters['document_type'])
                ->or_where('d.document_name', $filters['document_type'])
                ->or_where('dt.document_name', $filters['document_type'])
                ->group_end();
        }
        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('d.document_name', $q)
                ->or_like('d.document_type', $q)
                ->or_like('s.full_name', $q)
                ->or_like('s.employee_code', $q)
                ->or_like('desig.designation_name', $q)
                ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function count_documents_filtered($filters = array())
    {
        $sid = !empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id();

        $this->db
            ->from('tbl_staff_documents d')
            ->join('tbl_staff s', 's.staff_id = d.staff_id AND s.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff_document_types dt', 'dt.id = d.document_type_id AND dt.school_id = ' . (int)$sid, 'left')
            ->where('d.school_id', (int)$sid)
            ->where('d.status', 1)
            ->where('d.is_deleted', 'n');

        if (!empty($filters['staff_id'])) {
            $this->db->where('d.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['document_type'])) {
            $this->db->group_start()
                ->where('d.document_type', $filters['document_type'])
                ->or_where('d.document_name', $filters['document_type'])
                ->or_where('dt.document_name', $filters['document_type'])
                ->group_end();
        }
        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('d.document_name', $q)
                ->or_like('d.document_type', $q)
                ->or_like('s.full_name', $q)
                ->or_like('s.employee_code', $q)
                ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function get_documents_count_all($school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('school_id', (int)$sid)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->count_all_results('tbl_staff_documents');
    }

    public function get_staff_documents_map($staff_id, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        $docs = $this->db
            ->where('staff_id', (int)$staff_id)
            ->where('school_id', (int)$sid)
            ->where('is_deleted', 'n')
            ->where('status', 1)
            ->order_by('document_id', 'DESC')
            ->get('tbl_staff_documents')
            ->result();

        $map = array();
        foreach ($docs as $doc) {
            if (!empty($doc->document_type_id) && !isset($map[$doc->document_type_id])) {
                $map[$doc->document_type_id] = $doc;
            }
            // Also map by lowercase document name as fallback
            $nameKey = strtolower(trim($doc->document_type ?: $doc->document_name));
            if (!isset($map[$nameKey])) {
                $map[$nameKey] = $doc;
            }
        }
        return $map;
    }

    public function get_document_by_id($document_id, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();

        $this->db
            ->select('d.*, s.full_name, s.employee_code')
            ->from('tbl_staff_documents d')
            ->join('tbl_staff s', 's.staff_id = d.staff_id AND s.school_id = ' . (int)$sid, 'left')
            ->where('d.document_id', (int)$document_id)
            ->where('d.school_id', (int)$sid)
            ->where('d.is_deleted', 'n');

        return $this->db->get()->row();
    }

    public function add_document($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['is_deleted'])) {
            $data['is_deleted'] = 'n';
        }
        $this->db->insert('tbl_staff_documents', $data);
        return $this->db->insert_id();
    }

    public function update_document($id, $data, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where('document_id', (int)$id)
            ->where('school_id', (int)$sid)
            ->update('tbl_staff_documents', $data);
    }

    public function delete_document($id, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('document_id', (int)$id)
            ->where('school_id', (int)$sid)
            ->update('tbl_staff_documents', [
                'is_deleted' => 'y',
                'status'     => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /* =========================================================================
       Teacher Workload
       ========================================================================= */
    public function get_workloads($filters = array(), $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : (!empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id());
        $this->db
            ->select('w.*, s.full_name, s.employee_code, sub.subject_name, c.class_name, div.division_name, y.year_name')
            ->from('tbl_teacher_workload w')
            ->join('tbl_staff s', 's.staff_id = w.staff_id AND s.school_id = ' . (int)$sid, 'inner')
            ->join('tbl_subjects sub', 'sub.subject_id = w.subject_id AND sub.school_id = ' . (int)$sid, 'left')
            ->join('tbl_classes c', 'c.class_id = w.class_id AND c.school_id = ' . (int)$sid, 'left')
            ->join('tbl_divisions div', 'div.division_id = w.division_id AND div.school_id = ' . (int)$sid, 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = w.academic_year_id AND y.school_id = ' . (int)$sid, 'left')
            ->where('w.school_id', (int)$sid)
            ->where('w.status', 1)
            ->where('w.is_deleted', 'n')
            ->where('s.status', 1)
            ->where('s.is_deleted', 'n')
            ->where('s.staff_type', 'teacher')
            ->order_by('w.workload_id', 'DESC');

        if (!empty($filters['staff_id'])) {
            $this->db->where('w.staff_id', $filters['staff_id']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('w.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('w.class_id', $filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->where('w.division_id', $filters['division_id']);
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('w.subject_id', $filters['subject_id']);
        }

        return $this->db->get()->result();
    }

    public function get_workload_by_id($id, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('w.*, s.full_name, s.employee_code, sub.subject_name, c.class_name, div.division_name, y.year_name')
            ->from('tbl_teacher_workload w')
            ->join('tbl_staff s', 's.staff_id = w.staff_id AND s.school_id = ' . (int)$sid, 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = w.subject_id AND sub.school_id = ' . (int)$sid, 'left')
            ->join('tbl_classes c', 'c.class_id = w.class_id AND c.school_id = ' . (int)$sid, 'left')
            ->join('tbl_divisions div', 'div.division_id = w.division_id AND div.school_id = ' . (int)$sid, 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = w.academic_year_id AND y.school_id = ' . (int)$sid, 'left')
            ->where('w.workload_id', (int)$id)
            ->where('w.school_id', (int)$sid)
            ->where('w.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function add_workload($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert('tbl_teacher_workload', $data);
        return $this->db->insert_id();
    }

    public function update_workload($id, $data, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where('workload_id', (int)$id)
            ->where('school_id', (int)$sid)
            ->update('tbl_teacher_workload', $data);
    }

    public function delete_workload($id, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('workload_id', (int)$id)
            ->where('school_id', (int)$sid)
            ->update('tbl_teacher_workload', [
                'is_deleted' => 'y',
                'status'     => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /* =========================================================================
       Staff Attendance
       ========================================================================= */
    public function get_attendance_for_date($date, $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->select('s.staff_id, s.employee_code, s.full_name, s.staff_type, dg.designation_name, att.attendance_status, att.remarks, att.attendance_id, att.marked_by, att.updated_by, att.created_at, att.updated_at,
                      u_mb.name as marked_by_user_name, u_mb.username as marked_by_username, u_mb.user_type as marked_by_user_type,
                      r_mb.role_name as marked_by_role,
                      s_mb.full_name as marked_by_staff_name,
                      u_ub.name as updated_by_user_name, u_ub.username as updated_by_username, u_ub.user_type as updated_by_user_type,
                      r_ub.role_name as updated_by_role,
                      s_ub.full_name as updated_by_staff_name')
            ->from('tbl_staff s')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff_attendance att', 'att.staff_id = s.staff_id AND att.school_id = ' . (int)$sid . ' AND att.attendance_date = ' . $this->db->escape($date), 'left')
            ->join('tbl_users u_mb', 'u_mb.user_id = att.marked_by AND u_mb.school_id = ' . (int)$sid, 'left')
            ->join('tbl_roles r_mb', 'r_mb.role_id = u_mb.role_id AND r_mb.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff s_mb', 's_mb.staff_id = u_mb.staff_id AND s_mb.school_id = ' . (int)$sid, 'left')
            ->join('tbl_users u_ub', 'u_ub.user_id = att.updated_by AND u_ub.school_id = ' . (int)$sid, 'left')
            ->join('tbl_roles r_ub', 'r_ub.role_id = u_ub.role_id AND r_ub.school_id = ' . (int)$sid, 'left')
            ->join('tbl_staff s_ub', 's_ub.staff_id = u_ub.staff_id AND s_ub.school_id = ' . (int)$sid, 'left')
            ->where('s.school_id', (int)$sid)
            ->where('s.is_deleted', 'n')
            ->where('s.status', 1)
            ->order_by('s.staff_id', 'ASC');

        $records = $this->db->get()->result();
        foreach ($records as &$st) {
            if (!empty($st->marked_by)) {
                $role = !empty($st->marked_by_role) ? $st->marked_by_role : (!empty($st->marked_by_user_type) ? $st->marked_by_user_type : 'User');
                $name = !empty($st->marked_by_staff_name) ? $st->marked_by_staff_name : (!empty($st->marked_by_user_name) ? $st->marked_by_user_name : $st->marked_by_username);
                $st->marked_by_name = $name;
                $st->marked_by_role_name = $role;
                $st->marked_by_display = ($role && $name) ? ($role . ' · ' . $name) : ($name ?: ($role ?: 'User'));
            } else {
                $st->marked_by_name = null;
                $st->marked_by_role_name = null;
                $st->marked_by_display = 'Not Available';
            }

            if (!empty($st->updated_by)) {
                $u_role = !empty($st->updated_by_role) ? $st->updated_by_role : (!empty($st->updated_by_user_type) ? $st->updated_by_user_type : 'User');
                $u_name = !empty($st->updated_by_staff_name) ? $st->updated_by_staff_name : (!empty($st->updated_by_user_name) ? $st->updated_by_user_name : $st->updated_by_username);
                $st->updated_by_name = $u_name;
                $st->updated_by_role_name = $u_role;
                $st->updated_by_display = ($u_role && $u_name) ? ($u_role . ' · ' . $u_name) : ($u_name ?: ($u_role ?: 'User'));
            } else {
                $st->updated_by_name = null;
                $st->updated_by_role_name = null;
                $st->updated_by_display = null;
            }
        }
        unset($st);

        return $records;
    }

    public function save_attendance_batch($date, $records, $user_id = NULL, $school_id = NULL)
    {
        if (empty($records) || !is_array($records)) return FALSE;
        $sid = $school_id ? (int)$school_id : get_current_school_id();

        $this->db->trans_start();

        foreach ($records as $staff_id => $data) {
            $status = isset($data['status']) ? $data['status'] : 'Present';
            $remarks = isset($data['remarks']) ? $data['remarks'] : '';

            $existing = $this->db
                ->where('staff_id', (int)$staff_id)
                ->where('school_id', (int)$sid)
                ->where('attendance_date', $date)
                ->get('tbl_staff_attendance')
                ->row();

            if ($existing) {
                $update_data = array(
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'updated_by'        => $user_id,
                    'updated_at'        => date('Y-m-d H:i:s')
                );
                if (empty($existing->marked_by) && !empty($user_id)) {
                    $update_data['marked_by'] = $user_id;
                }
                $this->db
                    ->where('attendance_id', $existing->attendance_id)
                    ->where('school_id', (int)$sid)
                    ->update('tbl_staff_attendance', $update_data);
            } else {
                $this->db->insert('tbl_staff_attendance', array(
                    'school_id'         => (int)$sid,
                    'staff_id'          => (int)$staff_id,
                    'attendance_date'   => $date,
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'marked_by'         => $user_id,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ));
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /* =========================================================================
       Staff Leave Management
       ========================================================================= */
    public function get_leaves($filters = array(), $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : (!empty($filters['school_id']) ? (int)$filters['school_id'] : get_current_school_id());
        $this->db
            ->select('l.leave_id, l.school_id, l.staff_id, l.leave_type, l.from_date, l.to_date, l.total_days, l.reason, l.status, l.applied_date, l.approved_by, l.remarks, l.created_at, l.updated_at, l.is_deleted, s.full_name, s.employee_code, s.staff_type, dg.designation_name')
            ->from('tbl_staff_leave l')
            ->join('tbl_staff s', 's.staff_id = l.staff_id AND s.school_id = ' . (int)$sid, 'inner')
            ->join('tbl_designations dg', 'dg.designation_id = s.designation_id AND dg.school_id = ' . (int)$sid, 'left')
            ->where('l.school_id', (int)$sid)
            ->order_by('l.leave_id', 'DESC');

        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $this->db->where('l.status', $filters['status']);
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('l.staff_id', (int)$filters['staff_id']);
        }

        return $this->db->get()->result();
    }

    public function apply_leave($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert('tbl_staff_leave', $data);
        return $this->db->insert_id();
    }

    public function update_leave_status($leave_id, $status, $approved_by = NULL, $remarks = '', $school_id = NULL)
    {
        $sid = $school_id ? (int)$school_id : get_current_school_id();
        $update = array(
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        );
        if ($approved_by) $update['approved_by'] = $approved_by;
        if ($remarks) $update['remarks'] = $remarks;

        return $this->db
            ->where('leave_id', (int)$leave_id)
            ->where('school_id', (int)$sid)
            ->update('tbl_staff_leave', $update);
    }

    /* =========================================================================
       CRUD Helpers
       ========================================================================= */
    public function insert($data)
    {
        if (!isset($data['school_id']) || empty($data['school_id'])) {
            $data['school_id'] = get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        $insert_id = (int)$this->db->insert_id();

        // Automatically initialize staff financial sub-ledger (Staff Payable)
        if ($insert_id > 0) {
            try {
                if (!isset($this->Finance_model)) {
                    $this->load->model('Finance_model');
                }
                $this->Finance_model->get_or_create_staff_ledger($data['school_id'], $insert_id);
            } catch (Exception $e) {
                log_message('error', 'Failed to initialize staff ledger: ' . $e->getMessage());
            }
        }

        return $insert_id;
    }

    public function update($id, $data, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        unset($data['school_id']);
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function soft_delete($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, array('status' => 0, 'is_deleted' => 'y', 'employment_status' => 'Resigned'));
        return ($this->db->affected_rows() > 0);
    }

    public function count_staff($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db->where('status', 1)->where('is_deleted', 'n')->where('school_id', $school_id)->count_all_results($this->table);
    }

    public function count_teachers($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db->where('status', 1)->where('is_deleted', 'n')->where('school_id', $school_id)->where('staff_type', 'teacher')->count_all_results($this->table);
    }

    public function update_photo($staff_id, $photo_filename, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$staff_id)
            ->where('school_id', $school_id)
            ->update($this->table, array('photo' => $photo_filename, 'updated_at' => date('Y-m-d H:i:s')));
    }

    public function delete_photo($staff_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $staff = $this->get_by_id($staff_id, $school_id);
        if ($staff && !empty($staff->photo)) {
            $filePath = FCPATH . 'uploads/staff/' . $staff->photo;
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
        return $this->db
            ->where($this->primaryKey, (int)$staff_id)
            ->where('school_id', $school_id)
            ->update($this->table, array('photo' => NULL, 'updated_at' => date('Y-m-d H:i:s')));
    }

    /**
     * Retrieve the teacher's current group from the database.
     *
     * @param int $staff_id
     * @param int|null $school_id
     * @return object|null
     */
    public function get_teacher_group($staff_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('s.staff_id, s.academic_group_id, ag.group_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->where('s.staff_id', (int)$staff_id)
            ->where('s.school_id', (int)$school_id)
            ->where('s.is_deleted', 'n')
            ->get()
            ->row();
    }

    /**
     * Retrieve active teaching faculty belonging to a specific Department / Group.
     * Selects only required fields (no SELECT *).
     *
     * @param int $group_id
     * @param int|null $school_id
     * @return array
     */
    public function get_teachers_by_group($group_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        if (empty($group_id)) {
            return array();
        }

        return $this->db
            ->select('s.staff_id, s.employee_code, s.full_name, s.academic_group_id, ag.group_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->where('s.academic_group_id', (int)$group_id)
            ->where('s.school_id', (int)$school_id)
            ->where('s.status', 1)
            ->where('s.is_deleted', 'n')
            ->group_start()
                ->where('s.staff_type', 'Teacher')
                ->or_where('s.staff_type', 'teacher')
                ->or_where('s.category', 'Teaching')
            ->group_end()
            ->order_by('s.full_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Server-side validation: Verify whether a teacher belongs to a specific Department / Group.
     * Rule: teacher.academic_group_id == group_id
     *
     * @param int $staff_id
     * @param int $group_id
     * @param int|null $school_id
     * @return array Array with keys 'valid' (bool) and 'error' (string|null)
     */
    public function validate_teacher_group($staff_id, $group_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        if (empty($staff_id) || empty($group_id)) {
            return array('valid' => false, 'error' => 'Please select both Department / Group and Teacher.');
        }

        $teacher = $this->db
            ->select('s.staff_id, s.full_name, s.staff_type, s.category, s.academic_group_id, s.status, ag.group_name')
            ->from('tbl_staff s')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = s.academic_group_id AND ag.school_id = s.school_id AND ag.is_deleted = \'n\'', 'left')
            ->where('s.staff_id', (int)$staff_id)
            ->where('s.school_id', (int)$school_id)
            ->where('s.is_deleted', 'n')
            ->get()
            ->row();

        if (!$teacher) {
            return array('valid' => false, 'error' => 'Invalid teacher selected.');
        }

        $is_teacher = in_array(strtolower($teacher->staff_type ?? ''), ['teacher']) ||
                      in_array(strtolower($teacher->category ?? ''), ['teacher', 'teaching']);
        if (!$is_teacher || (int)$teacher->status !== 1) {
            return array('valid' => false, 'error' => 'Only active teaching faculty can be assigned.');
        }

        if (empty($teacher->academic_group_id)) {
            return array('valid' => false, 'error' => "Teacher \"{$teacher->full_name}\" has not been assigned to any Department / Group.");
        }

        if ((int)$teacher->academic_group_id !== (int)$group_id) {
            $grp = $this->db->select('group_name')->where('academic_group_id', (int)$group_id)->get('tbl_academic_groups')->row();
            $grp_name = $grp ? $grp->group_name : 'the selected Department / Group';
            return array(
                'valid' => false,
                'error' => "Teacher \"{$teacher->full_name}\" belongs to Department / Group \"{$teacher->group_name}\", but \"{$grp_name}\" was selected."
            );
        }

        return array('valid' => true, 'error' => null);
    }

    /**
     * Server-side validation: Verify whether a teacher and a class belong to the SAME group.
     * Rule: teacher.academic_group_id == class.academic_group_id
     *
     * @param int $staff_id
     * @param int $class_id
     * @param int|null $school_id
     * @return bool
     */
    public function validate_teacher_class_group($staff_id, $class_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        $teacher = $this->db
            ->select('staff_id, staff_type, category, academic_group_id, status')
            ->where('staff_id', (int)$staff_id)
            ->where('school_id', (int)$school_id)
            ->where('is_deleted', 'n')
            ->get('tbl_staff')
            ->row();

        $class = $this->db
            ->select('academic_group_id, class_name')
            ->where('class_id', (int)$class_id)
            ->where('school_id', (int)$school_id)
            ->where('is_deleted', 'n')
            ->get('tbl_classes')
            ->row();

        if (!$teacher || !$class) {
            return array('valid' => false, 'error' => 'Invalid teacher or class selected.');
        }

        // Must be active teaching faculty
        $is_teacher = in_array(strtolower($teacher->staff_type ?? ''), ['teacher']) ||
                      in_array(strtolower($teacher->category ?? ''), ['teacher', 'teaching']);
        if (!$is_teacher || (int)$teacher->status !== 1) {
            return array('valid' => false, 'error' => 'Only active teaching faculty can be assigned to classes.');
        }

        // Teacher must be assigned to a Department / Group
        if (empty($teacher->academic_group_id)) {
            return array('valid' => false, 'error' => 'Teacher must be assigned to a Department / Group before class assignment.');
        }

        // Class must belong to a Department / Group
        if (empty($class->academic_group_id)) {
            return array('valid' => false, 'error' => 'Selected class has no Department / Group configured.');
        }

        // Both teacher and class must belong to the exact same Department / Group
        if ((int)$teacher->academic_group_id !== (int)$class->academic_group_id) {
            return array('valid' => false, 'error' => 'Teacher and class must belong to the same Department / Group.');
        }

        return array('valid' => true, 'error' => null);
    }

    /**
     * Check if changing a teacher's group would cause existing assignments to violate the group rule.
     * Does NOT modify or delete assignments automatically.
     *
     * @param int $staff_id
     * @param int|null $new_group_id
     * @param int|null $school_id
     * @return array Array with keys 'allowed', 'conflicts', and 'warning'
     */
    public function check_teacher_group_assignment_conflicts($staff_id, $new_group_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $conflicts = [];

        // 1. Check Class Teacher assignments
        $this->db
            ->select('c.class_name, ag.group_name, "Class Teacher" as assignment_type')
            ->from('tbl_class_teachers ct')
            ->join('tbl_classes c', 'c.class_id = ct.class_id AND c.school_id = ' . (int)$school_id, 'inner')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id AND ag.school_id = ' . (int)$school_id, 'left')
            ->where('ct.staff_id', (int)$staff_id)
            ->where('ct.school_id', (int)$school_id)
            ->where('ct.status', 1)
            ->where('ct.is_deleted', 'n')
            ->where('c.is_deleted', 'n');

        if (!empty($new_group_id)) {
            $this->db->where('c.academic_group_id !=', (int)$new_group_id);
        }

        $ct = $this->db->get()->result();
        if (!empty($ct)) {
            $conflicts = array_merge($conflicts, $ct);
        }

        // 2. Check Subject Teacher assignments
        $this->db
            ->select('c.class_name, ag.group_name, "Subject Teacher" as assignment_type')
            ->from('tbl_subject_teachers st')
            ->join('tbl_classes c', 'c.class_id = st.class_id AND c.school_id = ' . (int)$school_id, 'inner')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id AND ag.school_id = ' . (int)$school_id, 'left')
            ->where('st.staff_id', (int)$staff_id)
            ->where('st.school_id', (int)$school_id)
            ->where('st.status', 1)
            ->where('st.is_deleted', 'n')
            ->where('c.is_deleted', 'n');

        if (!empty($new_group_id)) {
            $this->db->where('c.academic_group_id !=', (int)$new_group_id);
        }

        $st = $this->db->get()->result();
        if (!empty($st)) {
            $conflicts = array_merge($conflicts, $st);
        }

        // 3. Check Teacher Workload assignments
        $this->db
            ->select('c.class_name, ag.group_name, "Teacher Workload" as assignment_type')
            ->from('tbl_teacher_workload w')
            ->join('tbl_classes c', 'c.class_id = w.class_id AND c.school_id = ' . (int)$school_id, 'inner')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id AND ag.school_id = ' . (int)$school_id, 'left')
            ->where('w.staff_id', (int)$staff_id)
            ->where('w.school_id', (int)$school_id)
            ->where('w.status', 1)
            ->where('w.is_deleted', 'n')
            ->where('c.is_deleted', 'n');

        if (!empty($new_group_id)) {
            $this->db->where('c.academic_group_id !=', (int)$new_group_id);
        }

        $wl = $this->db->get()->result();
        if (!empty($wl)) {
            $conflicts = array_merge($conflicts, $wl);
        }

        if (!empty($conflicts)) {
            $conflict_details = [];
            foreach ($conflicts as $c) {
                $conflict_details[] = $c->assignment_type . ' in ' . $c->class_name . ' (' . ($c->group_name ?: 'Department / Group') . ')';
            }
            return array(
                'allowed'   => false,
                'conflicts' => $conflicts,
                'warning'   => 'Cannot change teacher Department / Group because this teacher is currently assigned to classes in a different Department / Group: ' . implode(', ', array_unique($conflict_details)) . '. Please reassign or update those class assignments first.'
            );
        }

        return array(
            'allowed'   => true,
            'conflicts' => [],
            'warning'   => null
        );
    }
}

