<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_model extends CI_Model {

    protected $table = 'tbl_leave_applications';
    protected $primaryKey = 'application_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leave_balance_model');
        $this->load->model('Communication_model');
    }

    public function get_dashboard_stats($year_id = NULL)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();
        $today = date('Y-m-d');

        $total_requests = (int)$this->db->where('school_id', $school_id)->where('is_deleted', 'n')->where('academic_year_id', $year_id)->count_all_results($this->table);
        $pending = (int)$this->db->where('school_id', $school_id)->where('status', 'Pending')->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results($this->table);
        $approved = (int)$this->db->where('school_id', $school_id)->where('status', 'Approved')->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results($this->table);
        $rejected = (int)$this->db->where('school_id', $school_id)->where('status', 'Rejected')->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results($this->table);
        $cancelled = (int)$this->db->where('school_id', $school_id)->where('status', 'Cancelled')->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results($this->table);

        // On leave today
        $students_on_leave_today = (int)$this->db
            ->where('school_id', $school_id)
            ->where('applicant_type', 'Student')
            ->where('status', 'Approved')
            ->where('academic_year_id', $year_id)
            ->where('from_date <=', $today)
            ->where('to_date >=', $today)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);

        $staff_on_leave_today = (int)$this->db
            ->where('school_id', $school_id)
            ->where('applicant_type', 'Staff')
            ->where('status', 'Approved')
            ->where('from_date <=', $today)
            ->where('to_date >=', $today)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);

        return (object)[
            'total_requests'          => $total_requests,
            'pending'                 => $pending,
            'approved'                => $approved,
            'rejected'                => $rejected,
            'cancelled'               => $cancelled,
            'students_on_leave_today' => $students_on_leave_today,
            'staff_on_leave_today'    => $staff_on_leave_today
        ];
    }

    public function get_applications($filters = array(), $limit = 50, $offset = 0)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->select('a.application_id, a.school_id, a.academic_year_id, a.applicant_type, a.student_id, a.staff_id, a.class_id, a.division_id, a.leave_type_id, a.from_date, a.to_date, a.total_days, a.total_days as duration_days, a.is_half_day, a.half_day_type, a.reason, a.document_path, a.status, a.approved_by, a.approved_at, a.rejection_reason, a.applied_date, a.is_deleted, a.created_at,
                      lt.type_name, lt.type_code, s.full_name as staff_name, s.employee_code, des.designation_name, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, c.class_name, div.division_name as division_name, div.division_name as section_name, approver.full_name as approver_name')
            ->from('tbl_leave_applications a')
            ->join('tbl_leave_types lt', 'lt.type_id = a.leave_type_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.staff_id', 'left')
            ->join('tbl_designations des', 'des.designation_id = s.designation_id', 'left')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_staff approver', 'approver.staff_id = a.approved_by', 'left')
            ->where('a.is_deleted', 'n')
            ->where('a.school_id', $school_id)
            ->order_by('a.applied_date', 'DESC')
            ->order_by('a.application_id', 'DESC');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('a.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['applicant_type'])) $this->db->where('a.applicant_type', $filters['applicant_type']);
        if (!empty($filters['status'])) $this->db->where('a.status', $filters['status']);
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('a.division_id', $filters['division_id']);
        if (!empty($filters['leave_type_id'])) $this->db->where('a.leave_type_id', $filters['leave_type_id']);
        if (!empty($filters['student_id'])) $this->db->where('a.student_id', $filters['student_id']);
        if (!empty($filters['staff_id'])) $this->db->where('a.staff_id', $filters['staff_id']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('s.full_name', $q)
                ->or_like('a.reason', $q)
            ->group_end();
        }

        if ($limit) $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    public function get_applications_datatables_data($filters = array(), $limit = 25, $start = 0, $order_col = 0, $order_dir = 'DESC')
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $applicant_type = $filters['applicant_type'] ?? 'Student';

        if ($applicant_type === 'Staff') {
            $allowed_cols = array(
                0 => 's.full_name',
                1 => 'des.designation_name',
                2 => 'lt.type_name',
                3 => 'a.from_date',
                4 => 'a.total_days',
                5 => 'a.reason',
                6 => 'a.status',
                7 => 'a.application_id'
            );
        } else {
            $allowed_cols = array(
                0 => 'st.first_name',
                1 => 'c.class_name',
                2 => 'lt.type_name',
                3 => 'a.from_date',
                4 => 'a.total_days',
                5 => 'a.reason',
                6 => 'a.status',
                7 => 'a.application_id'
            );
        }

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'a.applied_date';
        $dir = (strtoupper($order_dir) === 'ASC') ? 'ASC' : 'DESC';

        $this->db
            ->select('a.application_id, a.school_id, a.academic_year_id, a.applicant_type, a.student_id, a.staff_id, a.class_id, a.division_id, a.leave_type_id, a.from_date, a.to_date, a.total_days, a.total_days as duration_days, a.is_half_day, a.half_day_type, a.reason, a.document_path, a.status, a.approved_by, a.approved_at, a.rejection_reason, a.applied_date, a.is_deleted, a.created_at,
                      lt.type_name, lt.type_code, s.full_name as staff_name, s.employee_code, des.designation_name, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, c.class_name, div.division_name as division_name, div.division_name as section_name, approver.full_name as approver_name')
            ->from('tbl_leave_applications a')
            ->join('tbl_leave_types lt', 'lt.type_id = a.leave_type_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.staff_id', 'left')
            ->join('tbl_designations des', 'des.designation_id = s.designation_id', 'left')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_staff approver', 'approver.staff_id = a.approved_by', 'left')
            ->where('a.is_deleted', 'n')
            ->where('a.school_id', $school_id);

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('a.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['applicant_type'])) $this->db->where('a.applicant_type', $filters['applicant_type']);
        if (!empty($filters['status'])) $this->db->where('a.status', $filters['status']);
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('a.division_id', $filters['division_id']);
        if (!empty($filters['leave_type_id'])) $this->db->where('a.leave_type_id', $filters['leave_type_id']);
        if (!empty($filters['student_id'])) $this->db->where('a.student_id', $filters['student_id']);
        if (!empty($filters['staff_id'])) $this->db->where('a.staff_id', $filters['staff_id']);

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('s.full_name', $q)
                ->or_like('s.employee_code', $q)
                ->or_like('a.reason', $q)
            ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function count_applications_filtered($filters = array())
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->from('tbl_leave_applications a')
            ->join('tbl_staff s', 's.staff_id = a.staff_id', 'left')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->where('a.is_deleted', 'n')
            ->where('a.school_id', $school_id);

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('a.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['applicant_type'])) $this->db->where('a.applicant_type', $filters['applicant_type']);
        if (!empty($filters['status'])) $this->db->where('a.status', $filters['status']);
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('a.division_id', $filters['division_id']);
        if (!empty($filters['leave_type_id'])) $this->db->where('a.leave_type_id', $filters['leave_type_id']);
        if (!empty($filters['student_id'])) $this->db->where('a.student_id', $filters['student_id']);
        if (!empty($filters['staff_id'])) $this->db->where('a.staff_id', $filters['staff_id']);

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('s.full_name', $q)
                ->or_like('s.employee_code', $q)
                ->or_like('a.reason', $q)
            ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function count_applications_all($school_id = NULL, $applicant_type = NULL, $academic_year_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 1);
        $this->db->from($this->table)
            ->where('is_deleted', 'n')
            ->where('school_id', $school_id);

        if ($applicant_type) {
            $this->db->where('applicant_type', $applicant_type);
        }
        if ($academic_year_id) {
            $this->db->where('academic_year_id', (int)$academic_year_id);
        }

        return $this->db->count_all_results();
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db
            ->select('a.application_id, a.school_id, a.academic_year_id, a.applicant_type, a.student_id, a.staff_id, a.class_id, a.division_id, a.leave_type_id, a.from_date, a.to_date, a.total_days, a.total_days as duration_days, a.is_half_day, a.half_day_type, a.reason, a.document_path, a.status, a.approved_by, a.approved_at, a.rejection_reason, a.applied_date, a.is_deleted, a.created_at,
                      lt.type_name, lt.type_code, s.full_name as staff_name, s.employee_code, des.designation_name, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, st.guardian_phone as emergency_phone, c.class_name, div.division_name as division_name, div.division_name as section_name, approver.full_name as approver_name')
            ->from('tbl_leave_applications a')
            ->join('tbl_leave_types lt', 'lt.type_id = a.leave_type_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.staff_id', 'left')
            ->join('tbl_designations des', 'des.designation_id = s.designation_id', 'left')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_staff approver', 'approver.staff_id = a.approved_by', 'left')
            ->where('a.' . $this->primaryKey, $id)
            ->where('a.school_id', $school_id)
            ->where('a.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function calculate_duration($from_date, $to_date, $is_half_day = 0)
    {
        if ($is_half_day) return 0.5;

        $start = new DateTime($from_date);
        $end   = new DateTime($to_date);
        $end->modify('+1 day'); // Include end day

        $interval = $start->diff($end);
        $days = (int)$interval->days;

        // Skip Sundays as standard non-working days
        $working_days = 0;
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        foreach ($period as $dt) {
            if ($dt->format('N') != 7) { // 7 is Sunday
                $working_days++;
            }
        }

        return max(1.0, (float)$working_days);
    }

    public function check_overlapping($applicant_type, $entity_id, $from_date, $to_date, $exclude_id = NULL)
    {
        $this->db
            ->where('applicant_type', $applicant_type)
            ->where_in('status', ['Pending', 'Approved'])
            ->group_start()
                ->where("from_date <= '{$to_date}' AND to_date >= '{$from_date}'")
            ->group_end();

        if ($applicant_type === 'Student') {
            $this->db->where('student_id', $entity_id);
        } else {
            $this->db->where('staff_id', $entity_id);
        }

        if ($exclude_id) {
            $this->db->where('application_id !=', $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function submit_application($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $data['applied_date'] = date('Y-m-d');
        $data['created_at']   = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $app_id = $this->db->insert_id();

        // Add history record
        $this->add_history($app_id, 'Submitted', $data['applicant_type'] === 'Student' ? $data['student_id'] : $data['staff_id'], $data['applicant_type'], NULL, $data['status'], 'Leave request submitted.');

        return $app_id;
    }

    public function approve($id, $approver_id, $comments = '')
    {
        $app = $this->get_by_id($id);
        if (!$app || $app->status === 'Approved') return false;

        $this->db->where($this->primaryKey, $id)->update($this->table, [
            'status'      => 'Approved',
            'approved_by' => $approver_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        // Deduct from leave balance
        $entity_id = ($app->applicant_type === 'Student') ? $app->student_id : $app->staff_id;
        $this->Leave_balance_model->deduct_balance($app->academic_year_id, $app->applicant_type, $entity_id, $app->leave_type_id, $app->duration_days);

        // Add history
        $this->add_history($id, 'Approved', $approver_id, 'Staff', $app->status, 'Approved', $comments ?: 'Leave approved.');

        return true;
    }

    public function reject($id, $approver_id, $rejection_reason)
    {
        $app = $this->get_by_id($id);
        if (!$app) return false;

        $this->db->where($this->primaryKey, $id)->update($this->table, [
            'status'           => 'Rejected',
            'rejection_reason' => $rejection_reason,
            'approved_by'      => $approver_id,
            'approved_at'      => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        $this->add_history($id, 'Rejected', $approver_id, 'Staff', $app->status, 'Rejected', $rejection_reason);
        return true;
    }

    public function request_clarification($id, $approver_id, $notes)
    {
        $app = $this->get_by_id($id);
        if (!$app) return false;

        $this->db->where($this->primaryKey, $id)->update($this->table, [
            'status'              => 'Clarification Required',
            'clarification_notes' => $notes,
            'updated_at'          => date('Y-m-d H:i:s')
        ]);

        $this->add_history($id, 'Clarification Requested', $approver_id, 'Staff', $app->status, 'Clarification Required', $notes);
        return true;
    }

    public function cancel($id, $cancelled_by, $user_type = 'Staff', $reason = 'Cancelled by applicant')
    {
        $app = $this->get_by_id($id);
        if (!$app || in_array($app->status, ['Cancelled', 'Completed'])) return false;

        // If it was already approved, restore the used leave balance
        if ($app->status === 'Approved') {
            $entity_id = ($app->applicant_type === 'Student') ? $app->student_id : $app->staff_id;
            $this->Leave_balance_model->restore_balance($app->academic_year_id, $app->applicant_type, $entity_id, $app->leave_type_id, $app->duration_days);
        }

        $this->db->where($this->primaryKey, $id)->update($this->table, [
            'status'     => 'Cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->add_history($id, 'Cancelled', $cancelled_by, $user_type, $app->status, 'Cancelled', $reason);
        return true;
    }

    public function add_history($app_id, $action, $user_id, $user_type, $prev_status, $new_status, $comments)
    {
        return $this->db->insert('tbl_leave_history', [
            'application_id'    => $app_id,
            'action'            => $action,
            'performed_by'      => $user_id,
            'performed_by_type' => $user_type,
            'previous_status'   => $prev_status,
            'new_status'        => $new_status,
            'comments'          => $comments,
            'created_at'        => date('Y-m-d H:i:s')
        ]);
    }

    public function get_history($app_id)
    {
        return $this->db
            ->select('h.*, s.full_name as user_name')
            ->from('tbl_leave_history h')
            ->join('tbl_staff s', "s.staff_id = h.performed_by AND h.performed_by_type = 'Staff'", 'left')
            ->where('h.application_id', $app_id)
            ->order_by('h.created_at', 'ASC')
            ->get()
            ->result();
    }
}
