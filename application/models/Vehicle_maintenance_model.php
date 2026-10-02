<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle_maintenance_model extends CI_Model {

    protected $table = 'tbl_vehicle_maintenance';
    protected $primaryKey = 'maintenance_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all($vehicle_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('m.*, v.vehicle_number, v.registration_number, v.vehicle_type, d.driver_name')
            ->from('tbl_vehicle_maintenance m')
            ->join('tbl_vehicles v', 'v.vehicle_id = m.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->where('m.school_id', $school_id)
            ->order_by('m.service_date', 'DESC');

        if ($vehicle_id) {
            $this->db->where('m.vehicle_id', (int)$vehicle_id);
        }

        $records = $this->db->get()->result();
        $today = date('Y-m-d');

        foreach ($records as &$r) {
            if ($r->status === 'Scheduled' && $r->next_service_date < $today) {
                $r->status = 'Overdue';
            }
        }

        return $records;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('m.*, v.vehicle_number, v.registration_number, v.vehicle_type, d.driver_name')
            ->from('tbl_vehicle_maintenance m')
            ->join('tbl_vehicles v', 'v.vehicle_id = m.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->where('m.school_id', $school_id)
            ->where('m.' . $this->primaryKey, (int)$id)
            ->get()
            ->row();
    }

    public function get_total_cost($vehicle_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->select_sum('cost');
        $this->db->where('school_id', $school_id);
        if ($vehicle_id) {
            $this->db->where('vehicle_id', (int)$vehicle_id);
        }
        $row = $this->db->get($this->table)->row();
        return (float)($row->cost ?? 0.0);
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
