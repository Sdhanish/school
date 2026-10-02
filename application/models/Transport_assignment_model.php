<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transport_assignment_model extends CI_Model {

    protected $table = 'tbl_student_transport_assignments';
    protected $primaryKey = 'assignment_id';

    public function get_all($filters = array(), $limit = 100, $offset = 0)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->select('ta.assignment_id, ta.school_id, ta.academic_year_id, ta.student_id, ta.class_id, ta.division_id, ta.route_id, ta.pickup_stop_id, ta.drop_stop_id, ta.vehicle_id, ta.transport_type, ta.monthly_fee, ta.start_date, ta.end_date, ta.status, ta.created_at, ta.updated_at, ta.is_deleted, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, st.guardian_phone as emergency_phone, c.class_name, div.division_name as division_name, div.division_name as section_name, r.route_name, r.route_code, v.vehicle_number, v.registration_number, pstop.stop_name as pickup_stop_name, pstop.pickup_time, dstop.stop_name as drop_stop_name, dstop.drop_time, d.driver_name, d.phone as driver_phone')
            ->from('tbl_student_transport_assignments ta')
            ->join('tbl_students st', 'st.student_id = ta.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = ta.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ta.division_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = ta.route_id', 'left')
            ->join('tbl_vehicles v', 'v.vehicle_id = ta.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_route_stops pstop', 'pstop.stop_id = ta.pickup_stop_id', 'left')
            ->join('tbl_route_stops dstop', 'dstop.stop_id = ta.drop_stop_id', 'left')
            ->where('ta.school_id', $school_id)
            ->where('ta.is_deleted', 'n')
            ->order_by('ta.assignment_id', 'DESC');

        if (!empty($filters['academic_year_id'])) $this->db->where('ta.academic_year_id', $filters['academic_year_id']);
        if (!empty($filters['class_id'])) $this->db->where('ta.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('ta.division_id', $filters['division_id']);
        if (!empty($filters['route_id'])) $this->db->where('ta.route_id', $filters['route_id']);
        if (!empty($filters['vehicle_id'])) $this->db->where('ta.vehicle_id', $filters['vehicle_id']);
        if (!empty($filters['stop_id'])) {
            $this->db->group_start()
                ->where('ta.pickup_stop_id', $filters['stop_id'])
                ->or_where('ta.drop_stop_id', $filters['stop_id'])
            ->group_end();
        }
        if (!empty($filters['status'])) $this->db->where('ta.status', $filters['status']);

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
            ->group_end();
        }

        if ($limit) $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    public function get_datatables_data($filters = array(), $start = 0, $limit = 10, $order_col = 0, $order_dir = 'desc')
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $allowed_cols = array(
            0 => 'st.first_name',
            1 => 'c.class_name',
            2 => 'r.route_name',
            3 => 'pstop.stop_name',
            4 => 'ta.transport_type',
            5 => 'ta.monthly_fee',
            6 => 'ta.status',
            7 => 'ta.assignment_id'
        );

        $col = isset($allowed_cols[$order_col]) ? $allowed_cols[$order_col] : 'ta.assignment_id';
        $dir = (strtoupper($order_dir) === 'ASC') ? 'ASC' : 'DESC';

        $this->db
            ->select('ta.assignment_id, ta.school_id, ta.academic_year_id, ta.student_id, ta.class_id, ta.division_id, ta.route_id, ta.pickup_stop_id, ta.drop_stop_id, ta.vehicle_id, ta.transport_type, ta.monthly_fee, ta.start_date, ta.end_date, ta.status, ta.created_at, ta.updated_at, ta.is_deleted, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, st.guardian_phone as emergency_phone, c.class_name, div.division_name as division_name, div.division_name as section_name, r.route_name, r.route_code, v.vehicle_number, v.registration_number, pstop.stop_name as pickup_stop_name, pstop.pickup_time, dstop.stop_name as drop_stop_name, dstop.drop_time, d.driver_name, d.phone as driver_phone')
            ->from('tbl_student_transport_assignments ta')
            ->join('tbl_students st', 'st.student_id = ta.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = ta.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ta.division_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = ta.route_id', 'left')
            ->join('tbl_vehicles v', 'v.vehicle_id = ta.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_route_stops pstop', 'pstop.stop_id = ta.pickup_stop_id', 'left')
            ->join('tbl_route_stops dstop', 'dstop.stop_id = ta.drop_stop_id', 'left')
            ->where('ta.school_id', $school_id)
            ->where('ta.is_deleted', 'n');

        if (!empty($filters['academic_year_id'])) $this->db->where('ta.academic_year_id', $filters['academic_year_id']);
        if (!empty($filters['class_id'])) $this->db->where('ta.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('ta.division_id', $filters['division_id']);
        if (!empty($filters['route_id'])) $this->db->where('ta.route_id', $filters['route_id']);
        if (!empty($filters['vehicle_id'])) $this->db->where('ta.vehicle_id', $filters['vehicle_id']);
        if (!empty($filters['stop_id'])) {
            $this->db->group_start()
                ->where('ta.pickup_stop_id', $filters['stop_id'])
                ->or_where('ta.drop_stop_id', $filters['stop_id'])
            ->group_end();
        }
        if (!empty($filters['status'])) $this->db->where('ta.status', $filters['status']);

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('r.route_name', $q)
                ->or_like('v.vehicle_number', $q)
                ->or_like('pstop.stop_name', $q)
            ->group_end();
        }

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $start);
        }

        return $this->db->get()->result();
    }

    public function count_filtered($filters = array())
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->from('tbl_student_transport_assignments ta')
            ->join('tbl_students st', 'st.student_id = ta.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = ta.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ta.division_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = ta.route_id', 'left')
            ->join('tbl_vehicles v', 'v.vehicle_id = ta.vehicle_id', 'left')
            ->join('tbl_route_stops pstop', 'pstop.stop_id = ta.pickup_stop_id', 'left')
            ->where('ta.school_id', $school_id)
            ->where('ta.is_deleted', 'n');

        if (!empty($filters['academic_year_id'])) $this->db->where('ta.academic_year_id', $filters['academic_year_id']);
        if (!empty($filters['class_id'])) $this->db->where('ta.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('ta.division_id', $filters['division_id']);
        if (!empty($filters['route_id'])) $this->db->where('ta.route_id', $filters['route_id']);
        if (!empty($filters['vehicle_id'])) $this->db->where('ta.vehicle_id', $filters['vehicle_id']);
        if (!empty($filters['stop_id'])) {
            $this->db->group_start()
                ->where('ta.pickup_stop_id', $filters['stop_id'])
                ->or_where('ta.drop_stop_id', $filters['stop_id'])
            ->group_end();
        }
        if (!empty($filters['status'])) $this->db->where('ta.status', $filters['status']);

        if (!empty($filters['search'])) {
            $q = trim($filters['search']);
            $this->db->group_start()
                ->like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->or_like('r.route_name', $q)
                ->or_like('v.vehicle_number', $q)
                ->or_like('pstop.stop_name', $q)
            ->group_end();
        }

        return $this->db->count_all_results();
    }

    public function count_all($school_id = NULL, $academic_year_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : (function_exists('get_current_school_id') ? (int)get_current_school_id() : 1);
        $this->db->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n');

        if ($academic_year_id) {
            $this->db->where('academic_year_id', (int)$academic_year_id);
        }

        return $this->db->count_all_results();
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db
            ->select('ta.assignment_id, ta.school_id, ta.academic_year_id, ta.student_id, ta.class_id, ta.division_id, ta.route_id, ta.pickup_stop_id, ta.drop_stop_id, ta.vehicle_id, ta.transport_type, ta.monthly_fee, ta.start_date, ta.end_date, ta.status, ta.created_at, ta.updated_at, ta.is_deleted, st.first_name, st.last_name, st.admission_number, st.admission_number as admission_no, st.guardian_name, st.guardian_phone as emergency_phone, c.class_name, div.division_name as division_name, div.division_name as section_name, r.route_name, r.route_code, v.vehicle_number, v.registration_number, pstop.stop_name as pickup_stop_name, pstop.pickup_time, dstop.stop_name as drop_stop_name, dstop.drop_time, d.driver_name, d.phone as driver_phone')
            ->from('tbl_student_transport_assignments ta')
            ->join('tbl_students st', 'st.student_id = ta.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = ta.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = ta.division_id', 'left')
            ->join('tbl_transport_routes r', 'r.route_id = ta.route_id', 'left')
            ->join('tbl_vehicles v', 'v.vehicle_id = ta.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_route_stops pstop', 'pstop.stop_id = ta.pickup_stop_id', 'left')
            ->join('tbl_route_stops dstop', 'dstop.stop_id = ta.drop_stop_id', 'left')
            ->where('ta.' . $this->primaryKey, $id)
            ->where('ta.school_id', $school_id)
            ->where('ta.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function get_active_by_student($student_id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db
            ->select('ta.assignment_id, ta.school_id, ta.academic_year_id, ta.student_id, ta.class_id, ta.division_id, ta.route_id, ta.pickup_stop_id, ta.drop_stop_id, ta.vehicle_id, ta.transport_type, ta.monthly_fee, ta.start_date, ta.end_date, ta.status, ta.created_at, ta.updated_at, ta.is_deleted, r.route_name, r.route_code, v.vehicle_number, v.registration_number, pstop.stop_name as pickup_stop_name, dstop.stop_name as drop_stop_name, d.driver_name, d.phone as driver_phone')
            ->from('tbl_student_transport_assignments ta')
            ->join('tbl_transport_routes r', 'r.route_id = ta.route_id', 'left')
            ->join('tbl_vehicles v', 'v.vehicle_id = ta.vehicle_id', 'left')
            ->join('tbl_transport_drivers d', 'd.driver_id = v.assigned_driver_id', 'left')
            ->join('tbl_route_stops pstop', 'pstop.stop_id = ta.pickup_stop_id', 'left')
            ->join('tbl_route_stops dstop', 'dstop.stop_id = ta.drop_stop_id', 'left')
            ->where('ta.school_id', $school_id)
            ->where('ta.student_id', $student_id)
            ->where('ta.is_deleted', 'n')
            ->where('ta.status', 'Active')
            ->get()
            ->row();
    }

    public function validate_capacity($vehicle_id, $additional_seats = 1)
    {
        $v = $this->db->where('vehicle_id', $vehicle_id)->get('tbl_vehicles')->row();
        if (!$v) return false;

        $occupied = (int)$this->db
            ->where('vehicle_id', $vehicle_id)
            ->where('status', 'Active')
            ->count_all_results($this->table);

        return (($occupied + $additional_seats) <= (int)$v->seating_capacity);
    }

    public function assign_student($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $school_id = $data['school_id'] ?? 1;

        $existing = $this->db
            ->where('school_id', $school_id)
            ->where('student_id', $data['student_id'])
            ->where('status', 'Active')
            ->get($this->table)
            ->row();

        if ($existing) {
            // Cancel existing before creating new
            $this->db->where('assignment_id', $existing->assignment_id)->update($this->table, ['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $data['start_date'] = $data['start_date'] ?? date('Y-m-d');
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $new_id = $this->db->insert_id();

        // Add history log
        $this->db->insert('tbl_transport_assignment_history', [
            'assignment_id'       => $new_id,
            'student_id'          => $data['student_id'],
            'action'              => $existing ? 'Reassigned' : 'Assigned',
            'previous_route_id'   => $existing ? $existing->route_id : NULL,
            'new_route_id'        => $data['route_id'],
            'previous_stop_id'    => $existing ? $existing->pickup_stop_id : NULL,
            'new_stop_id'         => $data['pickup_stop_id'],
            'previous_vehicle_id' => $existing ? $existing->vehicle_id : NULL,
            'new_vehicle_id'      => $data['vehicle_id'],
            'effective_date'      => date('Y-m-d'),
            'changed_by'          => 1,
            'comments'            => 'Transport assignment created.',
            'created_at'          => date('Y-m-d H:i:s')
        ]);

        return $new_id;
    }

    public function remove_assignment($assignment_id, $reason = 'Cancelled by admin')
    {
        $assign = $this->get_by_id($assignment_id);
        if (!$assign) return false;

        $this->db->where($this->primaryKey, $assignment_id)->update($this->table, [
            'status'     => 'Cancelled',
            'end_date'   => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->insert('tbl_transport_assignment_history', [
            'assignment_id'       => $assignment_id,
            'student_id'          => $assign->student_id,
            'action'              => 'Cancelled',
            'previous_route_id'   => $assign->route_id,
            'new_route_id'        => NULL,
            'previous_stop_id'    => $assign->pickup_stop_id,
            'new_stop_id'         => NULL,
            'previous_vehicle_id' => $assign->vehicle_id,
            'new_vehicle_id'      => NULL,
            'effective_date'      => date('Y-m-d'),
            'changed_by'          => 1,
            'comments'            => $reason,
            'created_at'          => date('Y-m-d H:i:s')
        ]);

        return true;
    }
}
