<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stop_model extends CI_Model {

    protected $table = 'tbl_route_stops';
    protected $primaryKey = 'stop_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_all_by_route($route_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('s.*, r.route_name, r.route_code')
            ->from('tbl_route_stops s')
            ->join('tbl_transport_routes r', 'r.route_id = s.route_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.is_deleted', 'n')
            ->order_by('s.route_id', 'ASC')
            ->order_by('s.sequence_order', 'ASC');

        if ($route_id) {
            $this->db->where('s.route_id', (int)$route_id);
        }

        $stops = $this->db->get()->result();

        if (!empty($stops)) {
            $student_counts = [];
            $assign_res = $this->db->query("
                SELECT pickup_stop_id as stop_id, COUNT(*) as cnt 
                FROM tbl_student_transport_assignments 
                WHERE school_id = {$school_id} AND status = 'Active' AND is_deleted = 'n' AND pickup_stop_id IS NOT NULL 
                GROUP BY pickup_stop_id
                UNION ALL
                SELECT drop_stop_id as stop_id, COUNT(*) as cnt 
                FROM tbl_student_transport_assignments 
                WHERE school_id = {$school_id} AND status = 'Active' AND is_deleted = 'n' AND drop_stop_id IS NOT NULL AND drop_stop_id != pickup_stop_id 
                GROUP BY drop_stop_id
            ")->result();

            foreach ($assign_res as $ar) {
                $sid = (int)$ar->stop_id;
                $student_counts[$sid] = ($student_counts[$sid] ?? 0) + (int)$ar->cnt;
            }

            foreach ($stops as &$st) {
                $st->students_count = $student_counts[$st->stop_id] ?? 0;
            }
        }

        return $stops;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('s.*, r.route_name, r.route_code')
            ->from('tbl_route_stops s')
            ->join('tbl_transport_routes r', 'r.route_id = s.route_id', 'left')
            ->where('s.school_id', $school_id)
            ->where('s.' . $this->primaryKey, (int)$id)
            ->where('s.is_deleted', 'n')
            ->get()
            ->row();
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
