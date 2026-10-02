<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle_model extends CI_Model {

    protected $table = 'tbl_vehicles';
    protected $primaryKey = 'vehicle_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('v.*, d.driver_name, d.phone as driver_phone, r.route_name, r.route_code')
            ->from('tbl_vehicles v')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = v.assigned_route_id', 'left')
            ->where('v.school_id', $school_id)
            ->where('v.is_deleted', 'n')
            ->order_by('v.vehicle_number', 'ASC');

        if ($active_only) {
            $this->db->where('v.status', 'Active');
        }

        $vehicles = $this->db->get()->result();

        if (!empty($vehicles)) {
            // Batch calculate occupied seats
            $occupied_map = [];
            $assign_res = $this->db->query("SELECT vehicle_id, COUNT(*) as cnt FROM tbl_student_transport_assignments WHERE school_id = {$school_id} AND status = 'Active' AND is_deleted = 'n' GROUP BY vehicle_id")->result();
            foreach ($assign_res as $ar) {
                $occupied_map[$ar->vehicle_id] = (int)$ar->cnt;
            }

            foreach ($vehicles as &$v) {
                $v->occupied_seats = $occupied_map[$v->vehicle_id] ?? 0;
                $v->available_seats = max(0, (int)$v->seating_capacity - $v->occupied_seats);
                $v->occupancy_rate = ($v->seating_capacity > 0) ? round(($v->occupied_seats / $v->seating_capacity) * 100, 1) : 0;
            }
        }

        return $vehicles;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        $v = $this->db
            ->select('v.*, d.driver_name, d.phone as driver_phone, d.license_number, d.license_expiry_date, r.route_name, r.route_code, r.start_point, r.end_point, r.estimated_distance_km')
            ->from('tbl_vehicles v')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = v.assigned_route_id', 'left')
            ->where('v.school_id', $school_id)
            ->where('v.' . $this->primaryKey, (int)$id)
            ->where('v.is_deleted', 'n')
            ->get()
            ->row();

        if ($v) {
            $v->occupied_seats = (int)$this->db
                ->where('school_id', $school_id)
                ->where('vehicle_id', $v->vehicle_id)
                ->where('status', 'Active')
                ->where('is_deleted', 'n')
                ->count_all_results('tbl_student_transport_assignments');
            $v->available_seats = max(0, (int)$v->seating_capacity - $v->occupied_seats);
            $v->occupancy_rate = ($v->seating_capacity > 0) ? round(($v->occupied_seats / $v->seating_capacity) * 100, 1) : 0;
        }

        return $v;
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['is_deleted' => 'y']);
    }
}
