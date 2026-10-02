<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Student_model',
            'Staff_model',
            'Attendance_model',
            'Exam_model',
            'Transport_assignment_model'
        ));
    }

    public function index()
    {
        $this->require_permission('reports.view');
        $school_id = $this->school_id;
        $today     = date('Y-m-d');
        $year_id   = $this->academic_year_id;

        $total_students = $this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status >=', 0)->where('is_deleted', 'n')->count_all_results('tbl_students');
        $total_staff = $this->db->where('school_id', $school_id)->where('status >=', 0)->where('is_deleted', 'n')->count_all_results('tbl_staff');
        
        $att_stats = $this->Attendance_model->get_dashboard_stats($today, $year_id);
        $attendance_pct = is_object($att_stats) ? ($att_stats->percentage ?? 0) : (!empty($att_stats['percentage']) ? $att_stats['percentage'] : 0);
        
        $fin_collected = $this->db->select_sum('credit')
                                  ->where('school_id', $school_id)
                                  ->where('academic_year_id', $year_id)
                                  ->where('student_id IS NOT NULL', null, false)
                                  ->get('tbl_finance_transaction_items')
                                  ->row();
        $fee_collected = (float)($fin_collected->credit ?? 0);
        $fin_assigned = $this->db->select_sum('debit')
                                 ->where('school_id', $school_id)
                                 ->where('academic_year_id', $year_id)
                                 ->where('student_id IS NOT NULL', null, false)
                                 ->get('tbl_finance_transaction_items')
                                 ->row();
        $fee_pending = max(0, (float)($fin_assigned->debit ?? 0) - $fee_collected);
        
        $total_exams = $this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results('tbl_exams');
        $transport_users = $this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Active')->count_all_results('tbl_student_transport_assignments');

        $stats = array(
            'total_students'  => $total_students,
            'total_staff'     => $total_staff,
            'attendance_pct'  => $attendance_pct,
            'fee_collected'   => $fee_collected,
            'fee_pending'     => $fee_pending,
            'total_exams'     => $total_exams,
            'transport_users' => $transport_users,
        );

        $this->render('pages/reports/index', array(
            'title'    => 'Reports Dashboard',
            'page_key' => 'reports',
            'stats'    => $stats,
        ));
    }
}
