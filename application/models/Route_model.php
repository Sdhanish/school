<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Route_model extends CI_Model {

    protected $table = 'tbl_transport_routes';
    protected $primaryKey = 'route_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($active_only = FALSE)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('r.*, v.vehicle_number, v.registration_number, v.seating_capacity, d.driver_name, d.phone as driver_phone')
            ->from('tbl_transport_routes r')
            ->join('tbl_vehicles v', 'v.vehicle_id = r.assigned_vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = r.assigned_driver_id', 'left')
            ->where('r.school_id', $school_id)
            ->where('r.is_deleted', 'n')
            ->order_by('r.route_name', 'ASC');

        if ($active_only) {
            $this->db->where('r.status', 'Active');
        }

        $routes = $this->db->get()->result();

        if (!empty($routes)) {
            // Batch calculate stops count
            $stops_map = [];
            $stops_res = $this->db->query("SELECT route_id, COUNT(*) as cnt FROM tbl_route_stops WHERE school_id = {$school_id} AND status = 1 AND is_deleted = 'n' GROUP BY route_id")->result();
            foreach ($stops_res as $sr) {
                $stops_map[$sr->route_id] = (int)$sr->cnt;
            }

            // Batch calculate students count
            $students_map = [];
            $students_res = $this->db->query("SELECT route_id, COUNT(*) as cnt FROM tbl_student_transport_assignments WHERE school_id = {$school_id} AND status = 'Active' AND is_deleted = 'n' GROUP BY route_id")->result();
            foreach ($students_res as $str) {
                $students_map[$str->route_id] = (int)$str->cnt;
            }

            foreach ($routes as &$r) {
                $r->stops_count = $stops_map[$r->route_id] ?? 0;
                $r->students_count = $students_map[$r->route_id] ?? 0;
            }
        }

        return $routes;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        $r = $this->db
            ->select('r.*, v.vehicle_number, v.registration_number, v.vehicle_type, v.seating_capacity, d.driver_name, d.phone as driver_phone, d.license_number')
            ->from('tbl_transport_routes r')
            ->join('tbl_vehicles v', 'v.vehicle_id = r.assigned_vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = r.assigned_driver_id', 'left')
            ->where('r.school_id', $school_id)
            ->where('r.' . $this->primaryKey, (int)$id)
            ->where('r.is_deleted', 'n')
            ->get()
            ->row();

        if ($r) {
            $r->stops_count = (int)$this->db
                ->where('school_id', $school_id)
                ->where('route_id', $r->route_id)
                ->where('status', 1)
                ->where('is_deleted', 'n')
                ->count_all_results('tbl_route_stops');

            $r->students_count = (int)$this->db
                ->where('school_id', $school_id)
                ->where('route_id', $r->route_id)
                ->where('status', 'Active')
                ->where('is_deleted', 'n')
                ->count_all_results('tbl_student_transport_assignments');
        }

        return $r;
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
