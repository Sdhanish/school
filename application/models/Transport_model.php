<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transport_model extends CI_Model {

    public function get_dashboard_stats()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $total_vehicles = (int)$this->db->where('school_id', $school_id)->where('is_deleted', 'n')->count_all_results('tbl_vehicles');
        $active_vehicles = (int)$this->db->where('school_id', $school_id)->where('status', 'Active')->where('is_deleted', 'n')->count_all_results('tbl_vehicles');
        $inactive_vehicles = (int)$this->db->where('school_id', $school_id)->where('status !=', 'Active')->where('is_deleted', 'n')->count_all_results('tbl_vehicles');
        $total_drivers = (int)$this->db->where('school_id', $school_id)->where('is_deleted', 'n')->count_all_results('tbl_transport_drivers');
        $active_routes = (int)$this->db->where('school_id', $school_id)->where('status', 'Active')->where('is_deleted', 'n')->count_all_results('tbl_transport_routes');
        $total_stops = (int)$this->db->where('school_id', $school_id)->where('is_deleted', 'n')->count_all_results('tbl_route_stops');
        $students_using_transport = (int)$this->db->where('school_id', $school_id)->where('status', 'Active')->where('is_deleted', 'n')->count_all_results('tbl_student_transport_assignments');
        $vehicles_maintenance = (int)$this->db->where('school_id', $school_id)->where('status', 'Maintenance')->where('is_deleted', 'n')->count_all_results('tbl_vehicles');

        // Total capacity & occupancy
        $this->db->select_sum('seating_capacity');
        $this->db->where('school_id', $school_id);
        $this->db->where('status', 'Active');
        $this->db->where('is_deleted', 'n');
        $cap_row = $this->db->get('tbl_vehicles')->row();
        $total_capacity = (int)($cap_row->seating_capacity ?? 0);

        // Pending fees sum for transport users
        $this->db->select_sum('due_amount');
        $this->db->where('school_id', $school_id);
        $this->db->where_in('status', ['Pending', 'Partially_Paid']);
        $this->db->where('is_deleted', 'n');
        $due_row = $this->db->get('tbl_finance_fee_assignments')->row();
        $pending_fees = (float)($due_row->due_amount ?? 0.0);

        return (object)[
            'total_vehicles'           => $total_vehicles,
            'active_vehicles'          => $active_vehicles,
            'inactive_vehicles'        => $inactive_vehicles,
            'total_drivers'            => $total_drivers,
            'active_routes'            => $active_routes,
            'total_stops'              => $total_stops,
            'students_using_transport' => $students_using_transport,
            'vehicles_maintenance'     => $vehicles_maintenance,
            'total_capacity'           => $total_capacity,
            'pending_fees'             => $pending_fees
        ];
    }
}
