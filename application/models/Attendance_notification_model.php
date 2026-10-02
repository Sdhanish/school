<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_notification_model extends CI_Model {

    protected $table = 'tbl_attendance_notifications';
    protected $primaryKey = 'notification_id';

    public function get_all($filters = array(), $limit = NULL, $offset = NULL)
    {
        $school_id = get_current_school_id();
        $this->db
            ->select('n.*, st.admission_number, st.first_name, st.last_name, st.guardian_name, st.guardian_relation, st.guardian_phone, st.guardian_email, c.class_name, div.division_name as division_name, div.division_name as section_name, a.attendance_status')
            ->from('tbl_attendance_notifications n')
            ->join('tbl_students st', 'st.student_id = n.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_attendance a', 'a.attendance_id = n.attendance_id', 'left')
            ->where('n.school_id', $school_id)
            ->order_by('n.created_at', 'DESC');

        $this->_apply_filters($filters);

        if ($limit) {
            $this->db->limit($limit, $offset ?: 0);
        }

        return $this->db->get()->result();
    }

    public function count_all($filters = array())
    {
        $school_id = get_current_school_id();
        $this->db
            ->from('tbl_attendance_notifications n')
            ->join('tbl_students st', 'st.student_id = n.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->where('n.school_id', $school_id);

        $this->_apply_filters($filters);

        return $this->db->count_all_results();
    }

    private function _apply_filters($filters)
    {
        if (!empty($filters['status'])) {
            $this->db->where('n.status', $filters['status']);
        }
        if (!empty($filters['notification_type'])) {
            $this->db->where('n.notification_type', $filters['notification_type']);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('n.student_id', $filters['student_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('st.class_id', $filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->where('st.division_id', $filters['division_id']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('n.attendance_date', $filters['date']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('n.attendance_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('n.attendance_date <=', $filters['to_date']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $s)
                ->or_like('st.last_name', $s)
                ->or_like('st.admission_number', $s)
                ->or_like('st.guardian_name', $s)
                ->or_like('n.message', $s)
                ->group_end();
        }
    }

    public function get_by_id($id)
    {
        $school_id = get_current_school_id();
        return $this->db
            ->select('n.*, st.admission_number, st.first_name, st.last_name, st.guardian_name, st.guardian_relation, st.guardian_phone, st.guardian_email, c.class_name, div.division_name as division_name, div.division_name as section_name, a.attendance_status, a.remarks as attendance_remarks')
            ->from('tbl_attendance_notifications n')
            ->join('tbl_students st', 'st.student_id = n.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_attendance a', 'a.attendance_id = n.attendance_id', 'left')
            ->where('n.notification_id', $id)
            ->where('n.school_id', $school_id)
            ->get()
            ->row();
    }

    public function create_for_attendance($student, $attendance_id, $date, $status, $settings)
    {
        $school_id = get_current_school_id();
        // Check if notification is enabled for this status
        $type = NULL;
        $template = '';

        if ($status === 'Absent' && !empty($settings->enable_absent_notification)) {
            $type = 'Absent';
            $template = $settings->absent_template ?: 'Dear Parent, your child {student_name} was marked absent on {date}.';
        } elseif ($status === 'Late' && !empty($settings->enable_late_notification)) {
            $type = 'Late';
            $template = $settings->late_template ?: 'Dear Parent, your child {student_name} was marked late on {date}.';
        } elseif ($status === 'Excused' && !empty($settings->enable_absent_notification)) {
            $type = 'Excused';
            $template = $settings->excused_template ?: 'Dear Parent, your child {student_name} has been excused on {date}.';
        }

        if (!$type) return FALSE;

        // Check if notification already exists for this student + date + type in this school
        $existing = $this->db
            ->where('student_id', $student->student_id)
            ->where('school_id', $school_id)
            ->where('attendance_date', $date)
            ->where('notification_type', $type)
            ->get($this->table)
            ->row();

        $student_name = trim($student->first_name . ' ' . $student->last_name);
        $formatted_date = date('d M Y', strtotime($date));

        $message = str_replace(
            array('{student_name}', '{date}', '{status}'),
            array($student_name, $formatted_date, $status),
            $template
        );

        $data = array(
            'school_id'         => $school_id,
            'student_id'        => $student->student_id,
            'parent_name'       => $student->guardian_name ?: 'Parent / Guardian',
            'parent_phone'      => $student->guardian_phone ?: '',
            'parent_email'      => $student->guardian_email ?: '',
            'attendance_id'     => $attendance_id,
            'attendance_date'   => $date,
            'notification_type' => $type,
            'message'           => $message,
            'status'            => 'Pending',
            'updated_at'        => date('Y-m-d H:i:s')
        );

        if ($existing) {
            $this->db->where('notification_id', $existing->notification_id)->where('school_id', $school_id)->update($this->table, $data);
            return $existing->notification_id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
    }

    public function update_status($id, $status)
    {
        $school_id = get_current_school_id();
        return $this->db
            ->where($this->primaryKey, $id)
            ->where('school_id', $school_id)
            ->update($this->table, array(
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function delete($id)
    {
        $school_id = get_current_school_id();
        return $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->delete($this->table);
    }
}
