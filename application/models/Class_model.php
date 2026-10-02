<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_model extends CI_Model {

    protected $table = 'tbl_classes';
    protected $primaryKey = 'class_id';

    public function get_all($academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        if ($academic_year_id === TRUE || $academic_year_id === NULL) {
            $academic_year_id = get_current_academic_year_id($school_id);
        } elseif ($academic_year_id === 'all' || $academic_year_id === FALSE) {
            $academic_year_id = NULL;
        }

        $this->db
            ->select('c.class_id, c.class_name, c.class_code, c.academic_year_id, c.school_id, c.class_teacher_id, c.academic_group_id, c.capacity, c.description, c.status, ag.group_name, ag.attendance_type, y.year_name, s.full_name as class_teacher_name, (SELECT COUNT(division_id) FROM tbl_divisions WHERE class_id = c.class_id AND status = 1 AND is_deleted = \'n\' AND school_id = ' . (int)$school_id . ') as division_count, (SELECT COUNT(student_id) FROM tbl_students WHERE class_id = c.class_id AND status = 1 AND is_deleted = \'n\' AND school_id = ' . (int)$school_id . ') as student_count')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = c.academic_year_id', 'left')
            ->join('tbl_staff s', 's.staff_id = c.class_teacher_id', 'left')
            ->where('c.status', 1)
            ->where('c.is_deleted', 'n')
            ->where('c.school_id', $school_id)
            ->order_by('ag.display_order', 'ASC')
            ->order_by('c.class_id', 'ASC');

        if ($academic_year_id) {
            $this->db->where('c.academic_year_id', (int)$academic_year_id);
        }

        return $this->db->get()->result();
    }

    /**
     * Get active classes belonging to a specific Academic Group.
     *
     * @param int $academic_group_id
     * @param int|null $academic_year_id
     * @param int|null $school_id
     * @return array
     */
    public function get_by_group($academic_group_id, $academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->select('c.class_id, c.class_name, c.academic_year_id, c.school_id, c.class_teacher_id, c.academic_group_id, c.status, ag.group_name, ag.attendance_type, y.year_name')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = c.academic_year_id', 'left')
            ->where('c.academic_group_id', (int)$academic_group_id)
            ->where('c.status', 1)
            ->where('c.is_deleted', 'n')
            ->where('c.school_id', $school_id)
            ->order_by('c.class_id', 'ASC');

        if ($academic_year_id) {
            $this->db->where('c.academic_year_id', (int)$academic_year_id);
        }

        return $this->db->get()->result();
    }

    /**
     * Get academic group information for a specific class.
     *
     * @param int $class_id
     * @param int|null $school_id
     * @return object|null
     */
    public function get_class_group($class_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('c.class_id, c.academic_group_id, ag.group_name')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id AND ag.school_id = c.school_id AND ag.is_deleted = \'n\'', 'left')
            ->where('c.class_id', (int)$class_id)
            ->where('c.school_id', $school_id)
            ->where('c.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function get_dropdown($academic_year_id = NULL, $academic_group_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        if ($academic_year_id === TRUE || $academic_year_id === NULL) {
            $academic_year_id = get_current_academic_year_id($school_id);
        } elseif ($academic_year_id === 'all' || $academic_year_id === FALSE) {
            $academic_year_id = NULL;
        }

        $this->db
            ->select('class_id, class_name, academic_group_id')
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->where('school_id', $school_id)
            ->order_by('class_id', 'ASC');

        if ($academic_year_id) {
            $this->db->where('academic_year_id', (int)$academic_year_id);
        }
        if ($academic_group_id) {
            $this->db->where('academic_group_id', (int)$academic_group_id);
        }

        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('c.class_id, c.class_name, c.academic_year_id, c.school_id, c.class_teacher_id, c.academic_group_id, c.status, ag.group_name, ag.attendance_type, y.year_name, s.full_name as class_teacher_name')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = c.academic_year_id', 'left')
            ->join('tbl_staff s', 's.staff_id = c.class_teacher_id', 'left')
            ->where('c.class_id', (int)$id)
            ->where('c.school_id', $school_id)
            ->where('c.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function count_classes($academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        if ($academic_year_id === TRUE || $academic_year_id === NULL) {
            $academic_year_id = get_current_academic_year_id($school_id);
        } elseif ($academic_year_id === 'all' || $academic_year_id === FALSE) {
            $academic_year_id = NULL;
        }

        if ($academic_year_id) {
            $this->db->where('academic_year_id', (int)$academic_year_id);
        }
        return $this->db
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->where('school_id', $school_id)
            ->count_all_results($this->table);
    }

    public function insert($data)
    {
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }
        if (!isset($data['is_deleted'])) {
            $data['is_deleted'] = 'n';
        }
        if (!isset($data['school_id'])) {
            $data['school_id'] = get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        unset($data['school_id']);
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function soft_delete($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['status' => 0, 'is_deleted' => 'y']);
    }

    /**
     * Soft-delete a class and cascade to all its divisions in one transaction.
     *
     * @param  int      $id
     * @param  int|null $school_id
     * @return bool
     */
    public function soft_delete_with_divisions($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db->trans_begin();

        $this->soft_delete($id, $school_id);
        $this->db
            ->where('class_id', (int)$id)
            ->update('tbl_divisions', ['status' => 0, 'is_deleted' => 'y']);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
    }

    /**
     * Get all active classes for an academic year with their divisions attached
     * in a single optimized query (avoiding N+1 queries).
     *
     * @param int|null $academic_year_id
     * @return array
     */
    public function get_all_with_divisions($academic_year_id = NULL)
    {
        $classes = $this->get_all($academic_year_id);
        if (empty($classes)) {
            return [];
        }

        $class_ids = array_map(function($c) { return (int)$c->class_id; }, $classes);

        $divisions = $this->db
            ->select('d.*,
                      c.academic_year_id,
                      COALESCE(ct.staff_id, d.class_teacher_id) as class_teacher_id,
                      s.full_name as class_teacher_name,
                      s.employee_code as class_teacher_code,
                      (SELECT COUNT(student_id) FROM tbl_students WHERE division_id = d.division_id AND status = 1 AND is_deleted = \'n\') as student_count')
            ->from('tbl_divisions d')
            ->join('tbl_classes c', 'c.class_id = d.class_id', 'left')
            ->join('tbl_class_teachers ct', 'ct.division_id = d.division_id AND ct.academic_year_id = c.academic_year_id AND ct.status = 1 AND ct.is_deleted = "n"', 'left')
            ->join('tbl_staff s', 's.staff_id = COALESCE(ct.staff_id, d.class_teacher_id) AND s.is_deleted = "n"', 'left')
            ->where_in('d.class_id', $class_ids)
            ->where('d.status', 1)
            ->where('d.is_deleted', 'n')
            ->order_by('d.division_name', 'ASC')
            ->get()->result();

        $divs_by_class = [];
        foreach ($divisions as $d) {
            $divs_by_class[$d->class_id][] = $d;
        }

        foreach ($classes as &$c) {
            $c->divisions = isset($divs_by_class[$c->class_id]) ? $divs_by_class[$c->class_id] : [];
        }

        return $classes;
    }

    /**
     * Check if a class has dependent records across modules.
     * Prevents accidental data loss or foreign key violations.
     *
     * @param int $class_id
     * @return array|false
     */
    public function has_dependencies($class_id)
    {
        $class_id = (int)$class_id;
        $dependencies = [];

        // 1. Students enrolled
        $student_count = $this->db->where('class_id', $class_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_students');
        if ($student_count > 0) {
            $dependencies['students'] = $student_count;
        }

        // 2. Attendance records
        $att_count = $this->db->where('class_id', $class_id)->where('is_deleted', 'n')->count_all_results('tbl_attendance');
        if ($att_count > 0) {
            $dependencies['attendance'] = $att_count;
        }

        // 3. Exam marks
        $marks_count = $this->db->where('class_id', $class_id)->where('is_deleted', 'n')->count_all_results('tbl_exam_marks');
        if ($marks_count > 0) {
            $dependencies['exam_marks'] = $marks_count;
        }

        // 4. Exam schedules
        $schedules_count = $this->db->where('class_id', $class_id)->where('is_deleted', 'n')->count_all_results('tbl_exam_schedules');
        if ($schedules_count > 0) {
            $dependencies['exam_schedules'] = $schedules_count;
        }

        // 5. Timetables
        $timetable_count = $this->db->where('class_id', $class_id)->count_all_results('tbl_timetable');
        if ($timetable_count > 0) {
            $dependencies['timetable'] = $timetable_count;
        }

        // 6. Fee structures
        $fees_count = $this->db->where('class_id', $class_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_finance_fee_structures');
        if ($fees_count > 0) {
            $dependencies['fee_structures'] = $fees_count;
        }

        return !empty($dependencies) ? $dependencies : false;
    }

    /**
     * Check if a class with the same name already exists in the given academic year.
     *
     * @param string $class_name
     * @param int $academic_year_id
     * @param int|null $exclude_id
     * @return bool
     */
    public function check_duplicate($class_name, $academic_year_id, $exclude_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('school_id', $school_id)
            ->where('LOWER(TRIM(class_name))', strtolower(trim($class_name)))
            ->where('status', 1)
            ->where('is_deleted', 'n');

        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Reset active class teacher allocations for a class in a given academic year.
     *
     * @param int      $class_id
     * @param int      $academic_year_id
     * @param int|null $school_id
     * @return bool
     */
    public function reset_class_teacher_allocations($class_id, $academic_year_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('academic_year_id', (int)$academic_year_id)
            ->where('class_id', (int)$class_id)
            ->where('school_id', $school_id)
            ->update('tbl_class_teachers', array('status' => 0, 'is_deleted' => 'y'));
    }

    /**
     * Get academic overview metrics and summary for a school.
     *
     * @param int|null $school_id
     * @return array
     */
    public function get_overview_metrics($school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        $total_classes     = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_classes');
        $total_divisions   = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_divisions');
        $total_subjects    = $this->db->where('school_id', $school_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_subjects');
        $assigned_teachers = $this->db->where('school_id', $school_id)->where('class_teacher_id IS NOT NULL', NULL, FALSE)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_divisions');

        $classes_summary = $this->db->query("
            SELECT c.class_id, c.class_name, 
                   (SELECT COUNT(*) FROM tbl_divisions d WHERE d.class_id = c.class_id AND d.school_id = c.school_id AND d.status = 1 AND d.is_deleted = 'n') AS division_count,
                   (SELECT COUNT(*) FROM tbl_students st WHERE st.class_id = c.class_id AND st.school_id = c.school_id AND st.status = 1 AND st.is_deleted = 'n') AS student_count
            FROM tbl_classes c
            WHERE c.school_id = ? AND c.status = 1 AND c.is_deleted = 'n'
            ORDER BY c.class_id ASC
        ", [(int)$school_id])->result();

        foreach ($classes_summary as &$cs) {
            $cs->section_count = $cs->division_count;
        }

        $calendar_events = $this->db
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->order_by('start_date', 'ASC')
            ->limit(6)
            ->get('tbl_academic_calendar')
            ->result();

        return array(
            'total_classes'     => $total_classes,
            'total_divisions'   => $total_divisions,
            'total_sections'    => $total_divisions,
            'total_subjects'    => $total_subjects,
            'assigned_teachers' => $assigned_teachers,
            'classes_summary'   => $classes_summary,
            'calendar_events'   => $calendar_events,
        );
    }

    public function get_classes_by_group($group_id, $school_id = null)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('c.class_id, c.class_name, c.numeric_value, c.academic_group_id, ag.group_name')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id AND ag.school_id = c.school_id AND ag.is_deleted = \'n\'', 'left')
            ->where('c.academic_group_id', (int)$group_id)
            ->where('c.school_id', $school_id)
            ->where('c.status', 1)
            ->where('c.is_deleted', 'n')
            ->order_by('c.numeric_value', 'ASC')
            ->order_by('c.class_name', 'ASC')
            ->get()
            ->result();
    }
}

