<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard
 *
 * Loads real-time school statistics from the database and renders
 * the main School dashboard.  All stat cards, attendance summary,
 * fees overview, events and notices are fully dynamic.
 */
class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dashboard_model');
    }

    public function index()
    {
        $requested_year_id = (int)$this->input->get('academic_year_id');
        if ($requested_year_id > 0) {
            set_selected_academic_year($requested_year_id);
            $year_id = $requested_year_id;
        } else {
            $year_id = $this->academic_year_id;
        }

        $today = normalize_date_to_academic_year(date('Y-m-d'), $year_id);

        // All data is fetched with minimal DB round-trips via Dashboard_model
        $stats             = $this->Dashboard_model->get_summary_stats($year_id);
        $attendance        = $this->Dashboard_model->get_today_attendance($today, $year_id);
        $fees              = $this->Dashboard_model->get_fees_summary($year_id);
        $upcoming_events   = $this->Dashboard_model->get_upcoming_events(5);
        $recent_notices    = $this->Dashboard_model->get_recent_notices(5, $year_id);
        $students_by_class = $this->Dashboard_model->get_students_by_class($year_id);

        // Permissions for clickable dashboard cards/widgets
        $can_view_students   = $this->rbac->is_super_admin() || $this->rbac->has_permission('students.view');
        $can_view_staff      = $this->rbac->is_super_admin() || $this->rbac->has_permission('staff.view');
        $can_view_academics  = $this->rbac->is_super_admin() || $this->rbac->has_permission('academics.view');
        $can_view_attendance = $this->rbac->is_super_admin() || $this->rbac->has_permission('attendance.view');
        $can_mark_attendance = $this->rbac->is_super_admin() || $this->rbac->has_permission('attendance.mark');
        $can_view_fees       = $this->rbac->is_super_admin() || $this->rbac->has_permission('fees.view');

        $this->render('pages/dashboard', [
            'title'                => 'Dashboard',
            'page_key'             => 'dashboard',
            'stats'                => $stats,
            'attendance'           => $attendance,
            'fees'                 => $fees,
            'upcoming_events'      => $upcoming_events,
            'recent_notices'       => $recent_notices,
            'students_by_class'    => $students_by_class,
            'today'                => $today,
            'year_id'              => $year_id,
            'can_view_students'    => $can_view_students,
            'can_view_staff'       => $can_view_staff,
            'can_view_academics'   => $can_view_academics,
            'can_view_attendance'  => $can_view_attendance,
            'can_mark_attendance'  => $can_mark_attendance,
            'can_view_fees'        => $can_view_fees,
        ]);
    }

    /**
     * AJAX endpoint to fetch real-time dashboard data for dynamic refreshes
     */
    public function stats_ajax()
    {
        $year_id = (int)($this->input->get_post('academic_year_id') ?: $this->academic_year_id);
        $today   = normalize_date_to_academic_year(date('Y-m-d'), $year_id);

        $stats             = $this->Dashboard_model->get_summary_stats($year_id);
        $attendance        = $this->Dashboard_model->get_today_attendance($today, $year_id);
        $fees              = $this->Dashboard_model->get_fees_summary($year_id);
        $students_by_class = $this->Dashboard_model->get_students_by_class($year_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'            => true,
                'academic_year_id'  => $year_id,
                'today'             => $today,
                'stats'             => $stats,
                'attendance'        => $attendance,
                'fees'              => $fees,
                'students_by_class' => $students_by_class,
                'csrf_token_name'   => $this->security->get_csrf_token_name(),
                'csrf_hash'         => $this->security->get_csrf_hash()
            ]));
    }
}
