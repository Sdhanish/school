<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    protected $table = 'tbl_attendance';
    protected $primaryKey = 'attendance_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Attendance_setting_model');
        $this->load->model('Attendance_notification_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
    }

    /**
     * Join user, role, and staff tables for marked_by and updated_by to avoid N+1 queries.
     */
    public function apply_marker_joins($alias = 'a')
    {
        $this->db
            ->select("u_mb.name as marked_by_user_name, u_mb.username as marked_by_username, u_mb.user_type as marked_by_user_type,
                      r_mb.role_name as marked_by_role,
                      s_mb.full_name as marked_by_staff_name,
                      u_ub.name as updated_by_user_name, u_ub.username as updated_by_username, u_ub.user_type as updated_by_user_type,
                      r_ub.role_name as updated_by_role,
                      s_ub.full_name as updated_by_staff_name")
            ->join('tbl_users u_mb', "u_mb.user_id = {$alias}.marked_by", 'left')
            ->join('tbl_roles r_mb', 'r_mb.role_id = u_mb.role_id', 'left')
            ->join('tbl_staff s_mb', 's_mb.staff_id = u_mb.staff_id', 'left')
            ->join('tbl_users u_ub', "u_ub.user_id = {$alias}.updated_by", 'left')
            ->join('tbl_roles r_ub', 'r_ub.role_id = u_ub.role_id', 'left')
            ->join('tbl_staff s_ub', 's_ub.staff_id = u_ub.staff_id', 'left');
        return $this;
    }

    /**
     * Resolve and format Role · Person Name display for marked_by and updated_by.
     * Historical or unresolvable records cleanly return 'Not Available'.
     */
    public function format_marker_fields($records)
    {
        if (empty($records)) {
            return $records;
        }

        $is_array = is_array($records);
        $items = $is_array ? $records : array($records);

        foreach ($items as &$rec) {
            if (!is_object($rec)) continue;

            // Resolve Marked By
            if (!empty($rec->marked_by)) {
                $role = !empty($rec->marked_by_role) ? $rec->marked_by_role : (!empty($rec->marked_by_user_type) ? $rec->marked_by_user_type : 'User');
                $name = !empty($rec->marked_by_staff_name) ? $rec->marked_by_staff_name : (!empty($rec->marked_by_user_name) ? $rec->marked_by_user_name : $rec->marked_by_username);
                $rec->marked_by_name = $name;
                $rec->marked_by_role_name = $role;
                $rec->marked_by_display = ($role && $name) ? ($role . ' · ' . $name) : ($name ?: ($role ?: 'User'));
            } else {
                $rec->marked_by_name = null;
                $rec->marked_by_role_name = null;
                $rec->marked_by_display = 'Not Available';
            }

            // Resolve Updated By
            if (!empty($rec->updated_by)) {
                $u_role = !empty($rec->updated_by_role) ? $rec->updated_by_role : (!empty($rec->updated_by_user_type) ? $rec->updated_by_user_type : 'User');
                $u_name = !empty($rec->updated_by_staff_name) ? $rec->updated_by_staff_name : (!empty($rec->updated_by_user_name) ? $rec->updated_by_user_name : $rec->updated_by_username);
                $rec->updated_by_name = $u_name;
                $rec->updated_by_role_name = $u_role;
                $rec->updated_by_display = ($u_role && $u_name) ? ($u_role . ' · ' . $u_name) : ($u_name ?: ($u_role ?: 'User'));
            } else {
                $rec->updated_by_name = null;
                $rec->updated_by_role_name = null;
                $rec->updated_by_display = null;
            }
        }
        unset($rec);

        return $is_array ? $items : (isset($items[0]) ? $items[0] : null);
    }

    /* =========================================================================
       1. Dashboard & Statistics
       ========================================================================= */
    public function get_dashboard_stats($date = NULL, $class_id = NULL, $section_id = NULL, $year_id = NULL)
    {
        $school_id = get_current_school_id();
        if (!$date) $date = normalize_date_to_academic_year(NULL, $year_id);
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();

        // Check if class has other configured sections in tbl_sections
        $default_sec_id = $class_id ? $this->Division_model->get_default_division_id($class_id) : null;
        $has_other = $class_id ? $this->Division_model->has_other_divisions($class_id, $default_sec_id) : false;

        // Total active students matching filter
        $this->db->where('status', 1)->where('is_deleted', 'n')->where('school_id', $school_id);
        if ($class_id) $this->db->where('class_id', (int)$class_id);
        if ($year_id) {
            $this->db->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }
        if ($section_id) {
            if ((int)$section_id === (int)$default_sec_id || !$has_other) {
                if (!$has_other && $class_id) {
                    // All students in this class belong to default Section A
                } else {
                    $this->db->group_start()
                        ->where('division_id', (int)$section_id)
                        ->or_where('division_id IS NULL', null, false)
                        ->or_where('division_id', 0)
                        ->group_end();
                }
            } else {
                $this->db->where('division_id', (int)$section_id);
            }
        }
        $total_students = $this->db->count_all_results('tbl_students');

        // Query daily attendance for this date
        $this->db
            ->select('attendance_status, COUNT(*) as count')
            ->from($this->table)
            ->where('attendance_date', $date)
            ->where('attendance_type', 'Daily')
            ->where('is_deleted', 'n')
            ->where('school_id', $school_id);

        if ($class_id) $this->db->where('class_id', (int)$class_id);
        if ($year_id) {
            $this->db->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }
        if ($section_id) {
            if (!$has_other && $class_id) {
                // Class has only default Section A
            } else {
                $this->db->where('division_id', (int)$section_id);
            }
        }

        $counts = $this->db->group_by('attendance_status')->get()->result();

        $present  = 0;
        $half_day = 0;
        $absent   = 0;
        $late     = 0;

        foreach ($counts as $c) {
            if ($c->attendance_status === 'Present') $present = (int)$c->count;
            elseif (in_array($c->attendance_status, array('Half Day', 'Late / Half Day', 'Half-day'))) $half_day += (int)$c->count;
            elseif ($c->attendance_status === 'Absent') $absent = (int)$c->count;
            elseif (in_array($c->attendance_status, array('Late', 'Late Coming'))) $late += (int)$c->count;
        }

        // If class is LKG-10, late count is not applicable
        if ($class_id && !is_higher_secondary_class($class_id)) {
            $late = 0;
        }

        $total_marked = $present + $half_day + $absent + $late;
        $not_marked   = max(0, $total_students - $total_marked);

        $percentage   = ($total_marked > 0) ? round(($present / $total_marked) * 100, 1) : 0;
        $present_pct  = ($total_marked > 0) ? round(($present / $total_marked) * 100, 1) : 0;
        $half_day_pct = ($total_marked > 0) ? round(($half_day / $total_marked) * 100, 1) : 0;
        $absent_pct   = ($total_marked > 0) ? round(($absent / $total_marked) * 100, 1) : 0;
        $late_pct     = ($total_marked > 0) ? round(($late / $total_marked) * 100, 1) : 0;

        return (object) array(
            'date'           => $date,
            'total_students' => $total_students,
            'total_marked'   => $total_marked,
            'not_marked'     => $not_marked,
            'present'        => $present,
            'half_day'       => $half_day,
            'absent'         => $absent,
            'late'           => $late,
            'excused'        => 0,
            'percentage'     => $percentage,
            'present_pct'    => $present_pct,
            'half_day_pct'   => $half_day_pct,
            'absent_pct'     => $absent_pct,
            'late_pct'       => $late_pct,
            'excused_pct'    => 0,
        );
    }

    public function get_class_overview($date = NULL, $year_id = NULL, $class_id = NULL, $section_id = NULL)
    {
        $school_id = get_current_school_id();
        if (!$date) $date = normalize_date_to_academic_year(NULL, $year_id);
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();

        // 1. If a specific class_id is requested
        if ($class_id) {
            $class_row = $this->db->where('class_id', (int)$class_id)->where('school_id', $school_id)->where('status', 1)->get('tbl_classes')->row();
            if (!$class_row) {
                return array();
            }

            // Check if this class has active configured divisions in tbl_divisions
            $configured_divisions = $this->db
                ->where('class_id', (int)$class_id)
                ->where('school_id', $school_id)
                ->where('status', 1)
                ->where('is_deleted', 'n')
                ->order_by('division_name', 'ASC')
                ->get('tbl_divisions')
                ->result();

            $is_higher_sec = is_higher_secondary_class($class_id);

            if (!empty($configured_divisions)) {
                // Class has configured division(s) in DB - load them normally
                if ($is_higher_sec) {
                    $select_fields = "c.class_id, c.class_name, div.division_id, div.division_name,
                        COUNT(DISTINCT st.student_id) as total_students,
                        SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                        SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                        SUM(CASE WHEN a.attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                        0 as excused_count,
                        COUNT(a.attendance_id) as marked_count";
                    $att_type_cond = "(a.attendance_type = 'Daily' OR a.attendance_type = 'Period-wise')";
                } else {
                    $select_fields = "c.class_id, c.class_name, div.division_id, div.division_name,
                        COUNT(DISTINCT st.student_id) as total_students,
                        SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                        SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                        0 as late_count,
                        0 as excused_count,
                        COUNT(a.attendance_id) as marked_count";
                    $att_type_cond = "a.attendance_type = 'Daily'";
                }

                $this->db
                    ->select($select_fields, FALSE)
                    ->from('tbl_classes c')
                    ->join('tbl_divisions div', "div.class_id = c.class_id AND div.status = 1 AND div.is_deleted = 'n'", 'inner')
                    ->join('tbl_students st', "st.class_id = c.class_id AND (st.division_id = div.division_id OR (div.division_name = 'A' AND (st.division_id IS NULL OR st.division_id = 0))) AND st.status = 1 AND st.is_deleted = 'n' AND (st.academic_year_id = " . (int)$year_id . " OR st.academic_year_id IS NULL)", 'left')
                    ->join($this->table . ' a', "a.student_id = st.student_id AND a.attendance_date = " . $this->db->escape($date) . " AND {$att_type_cond} AND a.is_deleted = 'n' AND (a.academic_year_id = " . (int)$year_id . " OR a.academic_year_id IS NULL)", 'left')
                    ->where('c.class_id', (int)$class_id)
                    ->where('c.status', 1)
                    ->group_by(array('c.class_id', 'div.division_id', 'c.class_name', 'div.division_name'))
                    ->order_by('div.division_name', 'ASC');

                if ($section_id) {
                    $this->db->where('div.division_id', (int)$section_id);
                }

                $results = $this->db->get()->result();

                foreach ($results as $row) {
                    $row->section_id = $row->division_id;
                    $row->section_name = $row->division_name;
                    $marked = (int)$row->marked_count;
                    $row->is_marked = ($marked > 0);
                    $row->percentage = ($marked > 0) ? round(($row->present_count / $marked) * 100, 1) : 0;
                    $row->is_higher_sec = $is_higher_sec;
                    if (!$is_higher_sec) {
                        $row->late_count = 0;
                    }
                }

                return $results;
            } else {
                // Class has NO configured divisions -> Fallback automatically to Division A
                $default_sec_id = $this->Division_model->get_default_division_id($class_id);

                // Count active students belonging to this class
                $total_students = (int)$this->db
                    ->where('class_id', (int)$class_id)
                    ->where('school_id', $school_id)
                    ->where('status', 1)
                    ->where('is_deleted', 'n')
                    ->group_start()
                        ->where('academic_year_id', (int)$year_id)
                        ->or_where('academic_year_id IS NULL', null, false)
                    ->group_end()
                    ->count_all_results('tbl_students');

                // Query attendance counts for this class
                if ($is_higher_sec) {
                    $att_select = "
                        SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                        SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                        SUM(CASE WHEN attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                        0 as excused_count,
                        COUNT(attendance_id) as marked_count
                    ";
                } else {
                    $att_select = "
                        SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                        SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                        0 as late_count,
                        0 as excused_count,
                        COUNT(attendance_id) as marked_count
                    ";
                }

                $this->db
                    ->select($att_select, FALSE)
                    ->from($this->table)
                    ->where('class_id', (int)$class_id)
                    ->where('school_id', $school_id)
                    ->where('attendance_date', $date)
                    ->where('is_deleted', 'n');

                if ($is_higher_sec) {
                    $this->db->where_in('attendance_type', array('Daily', 'Period-wise'));
                } else {
                    $this->db->where('attendance_type', 'Daily');
                }

                $att_counts = $this->db
                    ->group_start()
                        ->where('academic_year_id', (int)$year_id)
                        ->or_where('academic_year_id IS NULL', null, false)
                    ->group_end()
                    ->get()
                    ->row();

                $present_c  = $att_counts ? (int)$att_counts->present_count : 0;
                $half_day_c = $att_counts ? (int)$att_counts->half_day_count : 0;
                $absent_c   = $att_counts ? (int)$att_counts->absent_count : 0;
                $late_c     = $att_counts ? (int)$att_counts->late_count : 0;
                $marked_c   = $att_counts ? (int)$att_counts->marked_count : 0;

                if (!$is_higher_sec) {
                    $late_c = 0;
                }

                $sec_overview = (object) array(
                    'class_id'       => (int)$class_id,
                    'class_name'     => $class_row ? $class_row->class_name : 'Class ' . $class_id,
                    'division_id'    => $default_sec_id ?: 0,
                    'division_name'  => 'A',
                    'section_id'     => $default_sec_id ?: 0,
                    'section_name'   => 'A',
                    'total_students' => $total_students,
                    'present_count'  => $present_c,
                    'half_day_count' => $half_day_c,
                    'absent_count'   => $absent_c,
                    'late_count'     => $late_c,
                    'excused_count'  => 0,
                    'marked_count'   => $marked_c,
                    'is_marked'      => ($marked_c > 0),
                    'percentage'     => ($marked_c > 0) ? round(($present_c / $marked_c) * 100, 1) : 0,
                    'is_higher_sec'  => $is_higher_sec,
                );

                return array($sec_overview);
            }
        }

        // 2. Overview across all classes (for Dashboard etc.)
        $classes_query = $this->db->where('status', 1)->where('school_id', $school_id);
        if ($year_id) {
            $classes_query->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }
        $all_classes = $classes_query->order_by('class_id', 'ASC')->get('tbl_classes')->result();

        $results = array();
        foreach ($all_classes as $cls) {
            $cls_overview = $this->get_class_overview($date, $year_id, $cls->class_id, $section_id);
            foreach ($cls_overview as $item) {
                $results[] = $item;
            }
        }

        return $results;
    }

    public function get_recent_activity($limit = 10, $year_id = NULL)
    {
        $school_id = get_current_school_id();
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();

        $this->db
            ->select('a.*, st.first_name, st.last_name, st.admission_number, st.roll_number, c.class_name, div.division_name, p.period_name')
            ->from($this->table . ' a')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = (CASE WHEN a.division_id > 0 THEN a.division_id ELSE st.division_id END)', 'left')
            ->join('tbl_periods p', 'p.period_id = a.period_id', 'left');

        $this->apply_marker_joins('a');
        $this->db->where('a.school_id', $school_id);

        if ($year_id) {
            $this->db->where('a.academic_year_id', $year_id);
        }

        $records = $this->db
            ->order_by('a.updated_at', 'DESC')
            ->order_by('a.attendance_id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();

        return $this->format_marker_fields($records);
    }

    /* =========================================================================
       2. Enrolled Students & Daily Attendance Sheet
       ========================================================================= */

    /**
     * Centralized query helper: applies standard school, academic year, class, and division/section
     * filters to tbl_students queries to ensure consistent, reliable student enrollment across modules.
     *
     * @param int $class_id
     * @param int|null $section_id
     * @param int|null $year_id
     * @param int|null $school_id
     * @param string $alias
     */
    public function apply_student_enrollment_conditions($class_id, $section_id = NULL, $year_id = NULL, $school_id = NULL, $alias = 'st')
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->where($alias . '.school_id', (int)$school_id)
            ->where($alias . '.status', 1)
            ->where($alias . '.is_deleted', 'n')
            ->order_by('CAST(' . $alias . '.roll_number AS UNSIGNED)', 'ASC')
            ->order_by($alias . '.first_name', 'ASC');

        if ($class_id) {
            $this->db->where($alias . '.class_id', (int)$class_id);
        }

        if ($year_id) {
            $this->db->group_start()
                ->where($alias . '.academic_year_id', (int)$year_id)
                ->or_where($alias . '.academic_year_id IS NULL', null, false)
                ->group_end();
        }

        if ($section_id) {
            $default_sec_id = $this->Division_model->get_default_division_id($class_id);
            $has_other = $class_id ? $this->Division_model->has_other_divisions($class_id, $default_sec_id) : false;
            if ((int)$section_id === (int)$default_sec_id || !$has_other) {
                if (!$has_other && $class_id) {
                    // All students in this class belong to default Section A
                } else {
                    $this->db->group_start()
                        ->where($alias . '.division_id', (int)$section_id)
                        ->or_where($alias . '.division_id IS NULL', null, false)
                        ->or_where($alias . '.division_id', 0)
                        ->group_end();
                }
            } else {
                $this->db->where($alias . '.division_id', (int)$section_id);
            }
        }
    }

    /**
     * Get active enrolled students for a class, division/section, academic year, and school.
     * Single reliable source of truth for enrolled students across Mark Attendance, View Attendance, etc.
     *
     * @param int $class_id
     * @param int|null $section_id
     * @param int|null $year_id
     * @param int|null $school_id
     * @return array
     */
    public function get_enrolled_students($class_id, $section_id = NULL, $year_id = NULL, $school_id = NULL)
    {
        $this->db
            ->select('st.student_id, st.admission_number, st.roll_number, st.first_name, st.middle_name, st.last_name, st.gender, st.photo, st.guardian_name, st.guardian_phone, st.guardian_email, c.class_name, COALESCE(div.division_name, "A") as division_name, COALESCE(div.division_name, "A") as section_name')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left');

        $this->apply_student_enrollment_conditions($class_id, $section_id, $year_id, $school_id, 'st');

        return $this->db->get()->result();
    }

    public function get_daily_sheet($date, $class_id = NULL, $section_id = NULL, $year_id = NULL)
    {
        $school_id = get_current_school_id();
        $this->db
            ->select('st.student_id, st.admission_number, st.roll_number, st.first_name, st.middle_name, st.last_name, st.photo, st.guardian_name, st.guardian_phone, st.guardian_email, c.class_name, COALESCE(div.division_name, "A") as division_name, COALESCE(div.division_name, "A") as section_name, a.attendance_id, a.attendance_status, a.remarks, a.attendance_type, a.updated_at, a.created_at, a.marked_by, a.updated_by')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join($this->table . ' a', 'a.student_id = st.student_id AND a.attendance_date = ' . $this->db->escape($date) . ' AND a.attendance_type = "Daily" AND a.is_deleted = "n" AND a.school_id = ' . (int)$school_id, 'left');

        $this->apply_marker_joins('a');

        $this->apply_student_enrollment_conditions($class_id, $section_id, $year_id, $school_id, 'st');

        $records = $this->db->get()->result();
        return $this->format_marker_fields($records);
    }

    public function check_daily_marked($date, $class_id, $section_id, $year_id = NULL)
    {
        $school_id = get_current_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('attendance_date', $date)
            ->where('class_id', (int)$class_id)
            ->where('attendance_type', 'Daily')
            ->where('is_deleted', 'n');

        $default_sec_id = $this->Division_model->get_default_division_id($class_id);
        $has_other = $class_id ? $this->Division_model->has_other_divisions($class_id, $default_sec_id) : false;
        if ($has_other && $section_id) {
            $this->db->where('division_id', (int)$section_id);
        }

        if ($year_id) {
            $this->db->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }
        return ($this->db->count_all_results($this->table) > 0);
    }

    public function save_daily_attendance($attendance_records, $date, $academic_year_id, $class_id, $section_id, $user_id = NULL)
    {
        $school_id = get_current_school_id();
        $settings = $this->Attendance_setting_model->get_settings($school_id);
        $saved_count = 0;

        foreach ($attendance_records as $student_id => $rec) {
            $raw_status = is_array($rec) ? (isset($rec['status']) ? $rec['status'] : 'Present') : $rec;
            $remarks    = is_array($rec) ? (isset($rec['remarks']) ? $rec['remarks'] : '') : '';

            // Strictly enforce allowed statuses for LKG-10: Present, Half Day, Absent
            if (in_array($raw_status, array('Half Day', 'Late / Half Day', 'Half-day'))) {
                $status = 'Half Day';
            } elseif ($raw_status === 'Absent') {
                $status = 'Absent';
            } else {
                // Reject Late, Late Coming, Leave, Excused
                $status = 'Present';
            }

            // Check existing daily attendance for this student and date
            $existing = $this->db
                ->where('student_id', $student_id)
                ->where('school_id', $school_id)
                ->where('attendance_date', $date)
                ->where('attendance_type', 'Daily')
                ->get($this->table)
                ->row();

            $att_id = NULL;

            if ($existing) {
                $update_data = array(
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'updated_by'        => $user_id,
                    'updated_at'        => date('Y-m-d H:i:s')
                );
                // If marked_by was NULL originally, backfill it with $user_id
                if (empty($existing->marked_by) && !empty($user_id)) {
                    $update_data['marked_by'] = $user_id;
                }
                $this->db
                    ->where('attendance_id', $existing->attendance_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, $update_data);
                $att_id = $existing->attendance_id;
            } else {
                $this->db->insert($this->table, array(
                    'school_id'         => $school_id,
                    'student_id'        => $student_id,
                    'academic_year_id'  => $academic_year_id,
                    'class_id'          => $class_id,
                    'division_id'       => !empty($section_id) ? (int)$section_id : NULL,
                    'attendance_date'   => $date,
                    'attendance_type'   => 'Daily',
                    'period_id'         => NULL,
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'marked_by'         => $user_id,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ));
                $att_id = $this->db->insert_id();
            }

            $saved_count++;

            // Handle parent notification generation foundation for Absent
            if ($att_id && $status === 'Absent') {
                $student = $this->db->where('student_id', $student_id)->get('tbl_students')->row();
                if ($student) {
                    $this->Attendance_notification_model->create_for_attendance($student, $att_id, $date, $status, $settings);
                }
            }
        }

        return $saved_count;
    }

    /* =========================================================================
       3. Period-wise Attendance Sheet & Marking
       ========================================================================= */
    public function get_period_sheet($date, $period_id, $class_id = NULL, $section_id = NULL, $year_id = NULL, $subject_id = NULL)
    {
        $school_id = get_current_school_id();
        $subject_join = '';
        if ($subject_id) {
            $subject_join = ' AND (a.subject_id = ' . (int)$subject_id . ' OR a.subject_id IS NULL)';
        }

        $this->db
            ->select('st.student_id, st.admission_number, st.roll_number, st.first_name, st.last_name, st.photo, c.class_name, COALESCE(div.division_name, \'A\') as division_name, COALESCE(div.division_name, \'A\') as section_name, p.period_name, p.period_number, p.start_time, p.end_time, a.attendance_id, a.attendance_status, a.remarks, a.updated_at, a.created_at, a.marked_by, a.updated_by')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = ' . $this->db->escape($period_id), 'left')
            ->join($this->table . ' a', 'a.student_id = st.student_id AND a.attendance_date = ' . $this->db->escape($date) . ' AND a.attendance_type = \'Period-wise\' AND a.period_id = ' . $this->db->escape($period_id) . $subject_join . ' AND a.is_deleted = \'n\'', 'left');

        $this->apply_marker_joins('a');

        $this->apply_student_enrollment_conditions($class_id, $section_id, $year_id, $school_id, 'st');

        $records = $this->db->get()->result();
        return $this->format_marker_fields($records);
    }

    public function check_period_marked($date, $period_id, $class_id, $section_id, $year_id = NULL, $subject_id = NULL)
    {
        $school_id = get_current_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('attendance_date', $date)
            ->where('period_id', $period_id)
            ->where('class_id', (int)$class_id)
            ->where('attendance_type', 'Period-wise')
            ->where('is_deleted', 'n');

        if ($subject_id) {
            $this->db->where('subject_id', (int)$subject_id);
        }

        $default_sec_id = $this->Division_model->get_default_division_id($class_id);
        $has_other = $class_id ? $this->Division_model->has_other_divisions($class_id, $default_sec_id) : false;
        if ($has_other && $section_id) {
            $this->db->where('division_id', (int)$section_id);
        }

        if ($year_id) {
            $this->db->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', null, false)
                ->group_end();
        }
        return ($this->db->count_all_results($this->table) > 0);
    }

    public function save_period_attendance($attendance_records, $date, $period_id, $academic_year_id, $class_id, $section_id, $user_id = NULL, $subject_id = NULL)
    {
        $school_id = get_current_school_id();
        // Enforce that period-wise attendance can ONLY be saved for Academic Groups configured with Period attendance
        if (get_attendance_workflow_type($class_id) !== 'period') {
            return 0;
        }

        $settings = $this->Attendance_setting_model->get_settings($school_id);
        $saved_count = 0;

        foreach ($attendance_records as $student_id => $rec) {
            $raw_status = is_array($rec) ? (isset($rec['status']) ? $rec['status'] : 'Present') : $rec;
            $remarks    = is_array($rec) ? (isset($rec['remarks']) ? $rec['remarks'] : '') : '';

            // Strictly enforce allowed statuses for +1/+2: Present, Half Day, Absent, Late Coming
            if (in_array($raw_status, array('Half Day', 'Late / Half Day', 'Half-day'))) {
                $status = 'Half Day';
            } elseif (in_array($raw_status, array('Late', 'Late Coming'))) {
                $status = 'Late Coming';
            } elseif ($raw_status === 'Absent') {
                $status = 'Absent';
            } else {
                // Reject Leave and Excused
                $status = 'Present';
            }

            $existing_q = $this->db
                ->where('student_id', $student_id)
                ->where('school_id', $school_id)
                ->where('attendance_date', $date)
                ->where('attendance_type', 'Period-wise')
                ->where('period_id', $period_id);

            if ($subject_id) {
                $existing_q->where('subject_id', (int)$subject_id);
            }

            $existing = $existing_q->get($this->table)->row();

            $att_id = NULL;

            if ($existing) {
                $update_data = array(
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'updated_by'        => $user_id,
                    'updated_at'        => date('Y-m-d H:i:s')
                );
                if ($subject_id) {
                    $update_data['subject_id'] = (int)$subject_id;
                }
                if (empty($existing->marked_by) && !empty($user_id)) {
                    $update_data['marked_by'] = $user_id;
                }
                $this->db
                    ->where('attendance_id', $existing->attendance_id)
                    ->update($this->table, $update_data);
                $att_id = $existing->attendance_id;
            } else {
                $this->db->insert($this->table, array(
                    'school_id'         => $school_id,
                    'student_id'        => $student_id,
                    'academic_year_id'  => $academic_year_id,
                    'class_id'          => $class_id,
                    'division_id'       => !empty($section_id) ? (int)$section_id : NULL,
                    'attendance_date'   => $date,
                    'attendance_type'   => 'Period-wise',
                    'period_id'         => $period_id,
                    'subject_id'        => $subject_id ? (int)$subject_id : NULL,
                    'attendance_status' => $status,
                    'remarks'           => $remarks,
                    'marked_by'         => $user_id,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ));
                $att_id = $this->db->insert_id();
            }

            $saved_count++;
        }

        return $saved_count;
    }

    /* =========================================================================
       4. History & Tracking Queries
       ========================================================================= */
    public function get_history($filters = array(), $limit = NULL, $offset = NULL)
    {
        $this->db
            ->select('a.*, st.admission_number, st.roll_number, st.first_name, st.last_name, c.class_name, div.division_name, p.period_name, p.period_number')
            ->from($this->table . ' a')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = a.period_id', 'left');

        $this->apply_marker_joins('a');

        $this->db
            ->order_by('a.attendance_date', 'DESC')
            ->order_by('a.updated_at', 'DESC');

        $this->_apply_history_filters($filters);

        if ($limit) {
            $this->db->limit($limit, $offset ?: 0);
        }

        $records = $this->db->get()->result();
        return $this->format_marker_fields($records);
    }

    public function count_history($filters = array())
    {
        $this->db
            ->from($this->table . ' a')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left');

        $this->_apply_history_filters($filters);

        return $this->db->count_all_results();
    }

    private function _apply_history_filters($filters)
    {
        $school_id = get_current_school_id();
        $this->db->where('a.school_id', $school_id);
        if (!empty($filters['attendance_type'])) {
            $this->db->where('a.attendance_type', $filters['attendance_type']);
        }
        if (!empty($filters['attendance_status'])) {
            $this->db->where('a.attendance_status', $filters['attendance_status']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('a.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('a.class_id', $filters['class_id']);
        }
        $f_div = $filters['division_id'] ?? ($f_div ?? null);
        if (!empty($f_div)) {
            $this->db->where('a.division_id', $f_div);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('a.student_id', $filters['student_id']);
        }
        if (!empty($filters['period_id'])) {
            $this->db->where('a.period_id', $filters['period_id']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('a.attendance_date', $filters['date']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('a.attendance_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('a.attendance_date <=', $filters['to_date']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $s)
                ->or_like('st.last_name', $s)
                ->or_like('st.admission_number', $s)
                ->or_like('a.remarks', $s)
                ->group_end();
        }
    }

    public function get_tracking_records($filters = array(), $limit = 100)
    {
        $school_id = get_current_school_id();
        // Tracking is dedicated for Absent, Late, Excused
        $this->db
            ->select('a.*, st.admission_number, st.roll_number, st.first_name, st.last_name, st.guardian_name, st.guardian_phone, c.class_name, div.division_name, p.period_name')
            ->from($this->table . ' a')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = a.period_id', 'left')
            ->where('a.school_id', $school_id)
            ->where_in('a.attendance_status', array('Absent', 'Late', 'Excused', 'Leave'))
            ->order_by('a.attendance_date', 'DESC')
            ->order_by('a.updated_at', 'DESC');

        if (!empty($filters['status_filter']) && $filters['status_filter'] !== 'All') {
            if ($filters['status_filter'] === 'Excused') {
                $this->db->where_in('a.attendance_status', array('Excused', 'Leave'));
            } else {
                $this->db->where('a.attendance_status', $filters['status_filter']);
            }
        }
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        $f_div = $filters['division_id'] ?? ($f_div ?? null);
        if (!empty($f_div)) $this->db->where('a.division_id', $f_div);
        if (!empty($filters['student_id'])) $this->db->where('a.student_id', $filters['student_id']);
        if (!empty($filters['academic_year_id'])) $this->db->where('a.academic_year_id', $filters['academic_year_id']);
        if (!empty($filters['from_date'])) $this->db->where('a.attendance_date >=', $filters['from_date']);
        if (!empty($filters['to_date'])) $this->db->where('a.attendance_date <=', $filters['to_date']);

        if ($limit) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result();
    }

    /* =========================================================================
       5. Calendar Matrix & Aggregation
       ========================================================================= */
    public function get_calendar_data($year, $month, $class_id = NULL, $section_id = NULL, $student_id = NULL, $type = 'Daily', $academic_year_id = NULL)
    {
        $school_id = get_current_school_id();
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date   = date('Y-m-t', strtotime($start_date));

        $this->db
            ->select('a.attendance_date, a.attendance_status, COUNT(*) as count')
            ->from($this->table . ' a')
            ->where('a.school_id', $school_id)
            ->where('a.attendance_date >=', $start_date)
            ->where('a.attendance_date <=', $end_date)
            ->where('a.attendance_type', $type);

        if ($student_id) $this->db->where('a.student_id', $student_id);
        if ($class_id) $this->db->where('a.class_id', $class_id);
        if ($section_id) $this->db->where('a.division_id', $section_id);
        if ($academic_year_id) $this->db->where('a.academic_year_id', $academic_year_id);

        $records = $this->db
            ->group_by(array('a.attendance_date', 'a.attendance_status'))
            ->get()
            ->result();

        $matrix = array();
        foreach ($records as $r) {
            $date = $r->attendance_date;
            if (!isset($matrix[$date])) {
                $matrix[$date] = array('Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0, 'total' => 0);
            }
            $st = ($r->attendance_status === 'Leave') ? 'Excused' : $r->attendance_status;
            if (isset($matrix[$date][$st])) {
                $matrix[$date][$st] += (int)$r->count;
            }
            $matrix[$date]['total'] += (int)$r->count;
        }

        return $matrix;
    }

    public function get_date_attendance_details($date, $class_id = NULL, $section_id = NULL, $student_id = NULL, $type = 'Daily', $academic_year_id = NULL)
    {
        $school_id = get_current_school_id();
        $this->db
            ->select('a.*, st.first_name, st.last_name, st.admission_number, st.roll_number, c.class_name, div.division_name, p.period_name')
            ->from($this->table . ' a')
            ->join('tbl_students st', 'st.student_id = a.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = a.period_id', 'left');

        $this->apply_marker_joins('a');

        $this->db
            ->where('a.school_id', $school_id)
            ->where('a.attendance_date', $date)
            ->where('a.attendance_type', $type)
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_id', 'ASC')
            ->order_by('CAST(st.roll_number AS UNSIGNED)', 'ASC');

        if ($student_id) $this->db->where('a.student_id', $student_id);
        if ($class_id) $this->db->where('a.class_id', $class_id);
        if ($section_id) $this->db->where('a.division_id', $section_id);
        if ($academic_year_id) $this->db->where('a.academic_year_id', $academic_year_id);

        $records = $this->db->get()->result();
        return $this->format_marker_fields($records);
    }

    /* =========================================================================
       6. Advanced Reports Hub
       ========================================================================= */
    public function get_reports_summary($academic_year_id = NULL, $class_id = NULL)
    {
        $school_id = get_current_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id();

        $this->db
            ->select("c.class_name, div.division_name,
                SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                0 as excused_count,
                COUNT(a.attendance_id) as total_count", FALSE)
            ->from('tbl_divisions div')
            ->join('tbl_classes c', 'c.class_id = div.class_id', 'inner')
            ->join('tbl_attendance a', 'a.division_id = div.division_id AND a.attendance_type = "Daily" AND a.academic_year_id = ' . (int)$academic_year_id, 'left')
            ->where('div.school_id', $school_id)
            ->where('div.status', 1)
            ->where('c.academic_year_id', $academic_year_id)
            ->group_by('div.division_id')
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_name', 'ASC');

        if ($class_id) $this->db->where('c.class_id', $class_id);

        $results = $this->db->get()->result();

        foreach ($results as $row) {
            $effective_total = $row->present_count + $row->half_day_count + $row->absent_count + $row->late_count;
            $row->percentage = $effective_total > 0 ? round(($row->present_count / $effective_total) * 100, 1) : 0;
        }

        return $results;
    }

    public function get_student_report($filters = array())
    {
        $year_id = !empty($filters['academic_year_id']) ? (int)$filters['academic_year_id'] : get_current_academic_year_id();

        $this->db
            ->select("st.student_id, st.admission_number, st.roll_number, st.first_name, st.last_name, c.class_name, div.division_name,
                SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                0 as excused_count,
                COUNT(a.attendance_id) as total_days", FALSE)
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join($this->table . ' a', 'a.student_id = st.student_id AND a.attendance_type = "Daily" AND a.academic_year_id = ' . (int)$year_id, 'left')
            ->where('st.status', 1)
            ->where('st.academic_year_id', $year_id)
            ->group_by('st.student_id')
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_id', 'ASC')
            ->order_by('CAST(st.roll_number AS UNSIGNED)', 'ASC');

        if (!empty($filters['class_id'])) $this->db->where('st.class_id', $filters['class_id']);
        $f_div = $filters['division_id'] ?? ($f_div ?? null);
        if (!empty($f_div)) $this->db->where('st.division_id', $f_div);
        if (!empty($filters['student_id'])) $this->db->where('st.student_id', $filters['student_id']);
        if (!empty($filters['from_date'])) $this->db->where('a.attendance_date >=', $filters['from_date']);
        if (!empty($filters['to_date'])) $this->db->where('a.attendance_date <=', $filters['to_date']);

        $results = $this->db->get()->result();

        foreach ($results as $row) {
            $effective = (int)$row->total_days;
            $row->percentage = $effective > 0 ? round(((int)$row->present_count / $effective) * 100, 1) : 0;
        }

        return $results;
    }

    public function get_monthly_report($filters = array())
    {
        $year_id = !empty($filters['academic_year_id']) ? (int)$filters['academic_year_id'] : get_current_academic_year_id();
        $month = !empty($filters['month']) ? (int)$filters['month'] : (int)date('m');
        $year  = !empty($filters['year']) ? (int)$filters['year'] : (int)date('Y');
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date   = date('Y-m-t', strtotime($start_date));

        $this->db
            ->select("st.student_id, st.admission_number, st.roll_number, st.first_name, st.last_name, c.class_name, div.division_name,
                SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                0 as excused_count,
                COUNT(a.attendance_id) as total_days", FALSE)
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join($this->table . ' a', 'a.student_id = st.student_id AND a.attendance_type = "Daily" AND a.academic_year_id = ' . (int)$year_id . ' AND a.attendance_date >= ' . $this->db->escape($start_date) . ' AND a.attendance_date <= ' . $this->db->escape($end_date), 'left')
            ->where('st.status', 1)
            ->where('st.academic_year_id', $year_id)
            ->group_by('st.student_id')
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_id', 'ASC')
            ->order_by('CAST(st.roll_number AS UNSIGNED)', 'ASC');

        if (!empty($filters['class_id'])) $this->db->where('st.class_id', $filters['class_id']);
        $f_div = $filters['division_id'] ?? ($f_div ?? null);
        if (!empty($f_div)) $this->db->where('st.division_id', $f_div);

        $results = $this->db->get()->result();

        foreach ($results as $row) {
            $effective = (int)$row->total_days;
            $row->percentage = $effective > 0 ? round(((int)$row->present_count / $effective) * 100, 1) : 0;
        }

        return $results;
    }

    public function get_period_wise_report($filters = array())
    {
        $year_id = !empty($filters['academic_year_id']) ? (int)$filters['academic_year_id'] : get_current_academic_year_id();

        $this->db
            ->select("p.period_id, p.period_number, p.period_name, p.start_time, p.end_time,
                SUM(CASE WHEN a.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.attendance_status = 'Half Day' THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN a.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.attendance_status = 'Late Coming' THEN 1 ELSE 0 END) as late_count,
                0 as excused_count,
                COUNT(a.attendance_id) as total_count", FALSE)
            ->from('tbl_periods p')
            ->join($this->table . ' a', 'a.period_id = p.period_id AND a.attendance_type = "Period-wise" AND a.academic_year_id = ' . (int)$year_id, 'left')
            ->where('p.status', 1)
            ->group_by('p.period_id')
            ->order_by('p.period_number', 'ASC')
            ->order_by('p.start_time', 'ASC');

        if (!empty($filters['date'])) $this->db->where('a.attendance_date', $filters['date']);
        if (!empty($filters['from_date'])) $this->db->where('a.attendance_date >=', $filters['from_date']);
        if (!empty($filters['to_date'])) $this->db->where('a.attendance_date <=', $filters['to_date']);
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        $f_div = $filters['division_id'] ?? ($f_div ?? null);
        if (!empty($f_div)) $this->db->where('a.division_id', $f_div);

        $results = $this->db->get()->result();

        foreach ($results as $row) {
            $effective = (int)$row->total_count;
            $row->percentage = $effective > 0 ? round(((int)$row->present_count / $effective) * 100, 1) : 0;
        }

        return $results;
    }

    /* =========================================================================
       7. Student Profile Attendance Tab Enhancement
       ========================================================================= */
    public function get_student_profile_attendance($student_id, $academic_year_id = NULL, $from_date = NULL, $to_date = NULL)
    {
        $student_id = (int)$student_id;
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id();

        // 1. Fetch student info to know class and group
        $this->load->model('Student_model');
        $student = $this->Student_model->get_by_id($student_id);

        $ay = get_academic_year_record($academic_year_id);
        if (!$from_date) {
            $from_date = $ay ? $ay->start_date : date('Y-06-01');
        }
        if (!$to_date) {
            $today = function_exists('school_today') ? school_today() : date('Y-m-d');
            $from_ts = strtotime($from_date);
            $ay_end_ts = $ay ? strtotime($ay->end_date) : strtotime($today);
            $today_ts = strtotime($today);
            if ($today_ts >= $from_ts && $today_ts <= $ay_end_ts) {
                $to_date = $today;
            } elseif ($today_ts > $ay_end_ts) {
                $to_date = $ay ? $ay->end_date : $today;
            } else {
                $to_date = $ay ? $ay->end_date : $today;
            }
        }

        // Check if student belongs to an academic group with Period attendance
        $class_id = $student ? (int)$student->class_id : 0;
        $is_higher_sec = ($class_id > 0) ? (get_attendance_workflow_type($student ?: $class_id) === 'period') : false;

        // 2. Query total existing Daily and Period-wise records for this student within date range
        $this->db->where('student_id', $student_id)
                 ->where('attendance_date >=', $from_date)
                 ->where('attendance_date <=', $to_date)
                 ->where('attendance_type', 'Daily');
        if ($academic_year_id) {
            $this->db->where('academic_year_id', $academic_year_id);
        }
        $count_daily = $this->db->count_all_results($this->table);

        $this->db->where('student_id', $student_id)
                 ->where('attendance_date >=', $from_date)
                 ->where('attendance_date <=', $to_date)
                 ->where('attendance_type', 'Period-wise');
        if ($academic_year_id) {
            $this->db->where('academic_year_id', $academic_year_id);
        }
        $count_period = $this->db->count_all_results($this->table);

        $has_daily = ($count_daily > 0);
        $has_period = ($count_period > 0);

        // Determine active mode
        if ($has_daily && $has_period) {
            $attendance_mode = 'both';
        } elseif ($has_period) {
            $attendance_mode = 'period';
        } elseif ($has_daily) {
            $attendance_mode = 'daily';
        } else {
            $attendance_mode = $is_higher_sec ? 'period' : 'daily';
        }

        // 3. Day-wise Attendance Calculations
        $working_info = $this->get_school_working_days($from_date, $to_date, $academic_year_id);
        $working_days = $working_info->count;

        $day_summary = (object) array(
            'working_days' => $working_days,
            'total_logged' => $count_daily,
            'present'      => 0,
            'absent'       => 0,
            'late'         => 0,
            'half_day'     => 0,
            'excused'      => 0,
            'percentage'   => 0.0,
        );

        $day_monthly = array();
        $day_records = array();

        if ($has_daily || $attendance_mode === 'daily') {
            $this->db->select("
                SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN attendance_status IN ('Late', 'Late Coming') THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN attendance_status IN ('Half Day', 'Late / Half Day', 'Half-day') THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN attendance_status IN ('Excused', 'Leave') THEN 1 ELSE 0 END) as excused_count
            ", FALSE)
            ->from($this->table)
            ->where('student_id', $student_id)
            ->where('attendance_type', 'Daily')
            ->where('attendance_date >=', $from_date)
            ->where('attendance_date <=', $to_date);
            if ($academic_year_id) $this->db->where('academic_year_id', $academic_year_id);
            $day_agg = $this->db->get()->row();

            if ($day_agg) {
                $day_summary->present  = (int)$day_agg->present_count;
                $day_summary->absent   = (int)$day_agg->absent_count;
                $day_summary->late     = (int)$day_agg->late_count;
                $day_summary->half_day = (int)$day_agg->half_day_count;
                $day_summary->excused  = (int)$day_agg->excused_count;
            }

            // Zero-safe attendance %: Present Days ÷ Total Working Days × 100
            if ($working_days > 0) {
                $day_summary->percentage = round(($day_summary->present / $working_days) * 100, 1);
            } elseif ($count_daily > 0) {
                $day_summary->percentage = round(($day_summary->present / $count_daily) * 100, 1);
            } else {
                $day_summary->percentage = 0.0;
            }

            // Day-wise monthly breakdown
            $this->db->select("DATE_FORMAT(attendance_date, '%Y-%m') as ym, DATE_FORMAT(attendance_date, '%M %Y') as month_name,
                SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN attendance_status IN ('Half Day', 'Late / Half Day', 'Half-day') THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN attendance_status IN ('Late', 'Late Coming') THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN attendance_status IN ('Excused', 'Leave') THEN 1 ELSE 0 END) as excused_count,
                COUNT(attendance_id) as total_days", FALSE)
            ->from($this->table)
            ->where('student_id', $student_id)
            ->where('attendance_type', 'Daily')
            ->where('attendance_date >=', $from_date)
            ->where('attendance_date <=', $to_date);
            if ($academic_year_id) $this->db->where('academic_year_id', $academic_year_id);
            $day_monthly = $this->db->group_by('ym')->order_by('ym', 'DESC')->get()->result();

            foreach ($day_monthly as $dm) {
                $tot = (int)$dm->total_days;
                $dm->percentage = ($tot > 0) ? round(((int)$dm->present_count / $tot) * 100, 1) : 0.0;
            }

            // Day-wise detailed records
            $this->db->select('a.*, DAYNAME(a.attendance_date) as day_name')
                     ->from($this->table . ' a');
            $this->apply_marker_joins('a');
            $this->db->where('a.student_id', $student_id)
                     ->where('a.attendance_type', 'Daily')
                     ->where('a.attendance_date >=', $from_date)
                     ->where('a.attendance_date <=', $to_date);
            if ($academic_year_id) $this->db->where('a.academic_year_id', $academic_year_id);
            $day_records = $this->db->order_by('a.attendance_date', 'DESC')->limit(100)->get()->result();
            $day_records = $this->format_marker_fields($day_records);
        }

        // 4. Period-wise & Subject-wise Attendance Calculations
        $period_summary = (object) array(
            'total_periods' => $count_period,
            'present'       => 0,
            'absent'        => 0,
            'late'          => 0,
            'half_day'      => 0,
            'excused'       => 0,
            'percentage'    => 0.0,
        );

        $subject_summary = array();
        $period_monthly  = array();
        $period_records  = array();

        if ($has_period || $attendance_mode === 'period' || $is_higher_sec) {
            // Detailed period-wise records with period name & subject joins
            $this->db->select('a.*, p.period_name, p.period_number, p.start_time, p.end_time,
                               sub.subject_name, sub.subject_code, sub.subject_type,
                               DAYNAME(a.attendance_date) as day_name')
                     ->from($this->table . ' a')
                     ->join('tbl_periods p', 'p.period_id = a.period_id', 'left')
                     ->join('tbl_subjects sub', 'sub.subject_id = a.subject_id', 'left');
            $this->apply_marker_joins('a');
            $this->db->where('a.student_id', $student_id)
                     ->where('a.attendance_type', 'Period-wise')
                     ->where('a.attendance_date >=', $from_date)
                     ->where('a.attendance_date <=', $to_date);
            if ($academic_year_id) $this->db->where('a.academic_year_id', $academic_year_id);
            $period_records = $this->db->order_by('a.attendance_date', 'DESC')
                                       ->order_by('p.period_number', 'ASC')
                                       ->limit(200)
                                       ->get()
                                       ->result();
            $period_records = $this->format_marker_fields($period_records);

            // Fallback: If subject_name is empty on any record, look up via tbl_timetable
            $missing_sub_keys = array();
            foreach ($period_records as $pr) {
                if (empty($pr->subject_name) && !empty($pr->period_id)) {
                    $missing_sub_keys[$pr->day_name . '_' . $pr->period_id] = true;
                }
            }
            if (!empty($missing_sub_keys) && $class_id > 0) {
                $tt_lookup = $this->db->select('tt.day, tt.period_id, sub.subject_id, sub.subject_name, sub.subject_code, sub.subject_type')
                                      ->from('tbl_timetable tt')
                                      ->join('tbl_subjects sub', 'sub.subject_id = tt.subject_id', 'left')
                                      ->where('tt.class_id', $class_id)
                                      ->where('tt.status', 1)
                                      ->where('tt.is_deleted', 'n')
                                      ->get()
                                      ->result();
                $tt_map = array();
                foreach ($tt_lookup as $tt) {
                    $tt_map[$tt->day . '_' . $tt->period_id] = $tt;
                }
                foreach ($period_records as $pr) {
                    if (empty($pr->subject_name)) {
                        $key = $pr->day_name . '_' . $pr->period_id;
                        if (isset($tt_map[$key])) {
                            $pr->subject_id   = $tt_map[$key]->subject_id;
                            $pr->subject_name = $tt_map[$key]->subject_name;
                            $pr->subject_code = $tt_map[$key]->subject_code;
                            $pr->subject_type = $tt_map[$key]->subject_type;
                        }
                    }
                }
            }

            // Period summary counts
            foreach ($period_records as $pr) {
                $st = $pr->attendance_status;
                if ($st === 'Present') $period_summary->present++;
                elseif ($st === 'Absent') $period_summary->absent++;
                elseif (in_array($st, array('Late', 'Late Coming'))) $period_summary->late++;
                elseif (in_array($st, array('Half Day', 'Late / Half Day', 'Half-day'))) $period_summary->half_day++;
                elseif (in_array($st, array('Excused', 'Leave'))) $period_summary->excused++;
            }

            // Zero-safe Period %: Present Periods ÷ Total Scheduled Periods × 100
            $period_summary->total_periods = count($period_records);
            if ($period_summary->total_periods > 0) {
                $period_summary->percentage = round(($period_summary->present / $period_summary->total_periods) * 100, 1);
            } else {
                $period_summary->percentage = 0.0;
            }

            // Subject-wise Breakdown
            $class_subjects = array();
            if ($class_id > 0) {
                $class_subjects = $this->db->select('subject_id, subject_name, subject_code, subject_type')
                                           ->where('class_id', $class_id)
                                           ->where('status', 1)
                                           ->where('is_deleted', 'n')
                                           ->order_by('subject_name', 'ASC')
                                           ->get('tbl_subjects')
                                           ->result();
            }

            $sub_map = array();
            foreach ($class_subjects as $cs) {
                $sub_map[$cs->subject_id] = (object) array(
                    'subject_id'     => (int)$cs->subject_id,
                    'subject_name'   => $cs->subject_name,
                    'subject_code'   => $cs->subject_code,
                    'subject_type'   => $cs->subject_type,
                    'total_classes'  => 0,
                    'present'        => 0,
                    'absent'         => 0,
                    'late'           => 0,
                    'half_day'       => 0,
                    'excused'        => 0,
                    'percentage'     => 0.0,
                    'monthly'        => array()
                );
            }

            // Aggregate period records into subject stats
            foreach ($period_records as $pr) {
                $sub_id = (int)$pr->subject_id;
                if ($sub_id > 0) {
                    if (!isset($sub_map[$sub_id])) {
                        $sub_map[$sub_id] = (object) array(
                            'subject_id'     => $sub_id,
                            'subject_name'   => $pr->subject_name ?: 'Subject #' . $sub_id,
                            'subject_code'   => $pr->subject_code ?: '',
                            'subject_type'   => $pr->subject_type ?: 'Core',
                            'total_classes'  => 0,
                            'present'        => 0,
                            'absent'         => 0,
                            'late'           => 0,
                            'half_day'       => 0,
                            'excused'        => 0,
                            'percentage'     => 0.0,
                            'monthly'        => array()
                        );
                    }
                    $sub_map[$sub_id]->total_classes++;
                    $st = $pr->attendance_status;
                    if ($st === 'Present') $sub_map[$sub_id]->present++;
                    elseif ($st === 'Absent') $sub_map[$sub_id]->absent++;
                    elseif (in_array($st, array('Late', 'Late Coming'))) $sub_map[$sub_id]->late++;
                    elseif (in_array($st, array('Half Day', 'Late / Half Day', 'Half-day'))) $sub_map[$sub_id]->half_day++;
                    elseif (in_array($st, array('Excused', 'Leave'))) $sub_map[$sub_id]->excused++;
                }
            }

            // Calculate percentage for each subject: Subject Present ÷ Subject Total Classes × 100
            foreach ($sub_map as $sid => $sObj) {
                if ($sObj->total_classes > 0) {
                    $sObj->percentage = round(($sObj->present / $sObj->total_classes) * 100, 1);
                } else {
                    $sObj->percentage = 0.0;
                }
            }
            $subject_summary = array_values($sub_map);

            // Period-wise Month Breakdown
            $this->db->select("DATE_FORMAT(attendance_date, '%Y-%m') as ym, DATE_FORMAT(attendance_date, '%M %Y') as month_name,
                SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) as present_periods,
                SUM(CASE WHEN attendance_status IN ('Half Day', 'Late / Half Day', 'Half-day') THEN 1 ELSE 0 END) as half_day_periods,
                SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) as absent_periods,
                SUM(CASE WHEN attendance_status IN ('Late', 'Late Coming') THEN 1 ELSE 0 END) as late_periods,
                SUM(CASE WHEN attendance_status IN ('Excused', 'Leave') THEN 1 ELSE 0 END) as excused_periods,
                COUNT(attendance_id) as total_periods", FALSE)
            ->from($this->table)
            ->where('student_id', $student_id)
            ->where('attendance_type', 'Period-wise')
            ->where('attendance_date >=', $from_date)
            ->where('attendance_date <=', $to_date);
            if ($academic_year_id) $this->db->where('academic_year_id', $academic_year_id);
            $period_monthly = $this->db->group_by('ym')->order_by('ym', 'DESC')->get()->result();

            foreach ($period_monthly as $pm) {
                $tot = (int)$pm->total_periods;
                $pm->percentage = ($tot > 0) ? round(((int)$pm->present_periods / $tot) * 100, 1) : 0.0;
            }
        }

        // 5. Build Unified Recent Records (most recent 30 records)
        $recent_records = array();
        if ($has_period && !$has_daily) {
            $recent_records = array_slice($period_records, 0, 30);
        } elseif ($has_daily && !$has_period) {
            $recent_records = array_slice($day_records, 0, 30);
        } else {
            $combined = array_merge($period_records, $day_records);
            usort($combined, function($a, $b) {
                $c = strcmp($b->attendance_date, $a->attendance_date);
                if ($c !== 0) return $c;
                return ($b->attendance_id - $a->attendance_id);
            });
            $recent_records = array_slice($combined, 0, 30);
        }

        // Backward compatibility properties for legacy views
        $primary_total   = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->total_periods : $day_summary->working_days;
        $primary_present = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->present : $day_summary->present;
        $primary_absent  = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->absent : $day_summary->absent;
        $primary_late    = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->late : $day_summary->late;
        $primary_half_day= ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->half_day : $day_summary->half_day;
        $primary_excused = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->excused : $day_summary->excused;
        $primary_pct     = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_summary->percentage : $day_summary->percentage;
        $primary_monthly = ($attendance_mode === 'period' || ($has_period && !$has_daily)) ? $period_monthly : $day_monthly;

        return (object) array(
            'student_id'       => $student_id,
            'academic_year_id' => $academic_year_id,
            'from_date'        => $from_date,
            'to_date'          => $to_date,
            'has_daily'        => $has_daily,
            'has_period'       => $has_period,
            'attendance_mode'  => $attendance_mode,
            'is_higher_sec'    => $is_higher_sec,

            // Overall Summary Objects
            'day_summary'      => $day_summary,
            'period_summary'   => $period_summary,
            'subject_summary'  => $subject_summary,

            // Monthly Breakdowns
            'day_monthly'      => $day_monthly,
            'period_monthly'   => $period_monthly,

            // Detailed Records
            'day_records'      => $day_records,
            'period_records'   => $period_records,
            'recent'           => $recent_records,
            'recent_records'   => $recent_records,

            // Legacy backward compatibility properties
            'total_days'       => $primary_total,
            'present'          => $primary_present,
            'absent'           => $primary_absent,
            'late'             => $primary_late,
            'half_day'         => $primary_half_day,
            'excused'          => $primary_excused,
            'percentage'       => $primary_pct,
            'monthly'          => $primary_monthly,
        );
    }

    /* =========================================================================
       8. View Attendance Report & Working Days Calculation
       ========================================================================= */

    /**
     * Calculate school working days in a date range excluding weekends and calendar holidays/term breaks
     */
    public function get_school_working_days($from_date, $to_date, $academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $from_ts = strtotime($from_date);
        $to_ts   = strtotime($to_date);

        if (!$from_ts || !$to_ts || $from_ts > $to_ts) {
            return (object) array(
                'count'         => 0,
                'working_dates' => array(),
                'holidays'      => array(),
            );
        }

        // 1. Get configured school working days from Timetable_setting_model
        $working_days_config = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        if (class_exists('Timetable_setting_model') || file_exists(APPPATH . 'models/Timetable_setting_model.php')) {
            $this->load->model('Timetable_setting_model');
            $cfg = $this->Timetable_setting_model->get_working_days_array();
            if (!empty($cfg)) {
                $working_days_config = $cfg;
            }
        }

        // 2. Fetch holidays and term breaks from tbl_academic_calendar
        $this->db->select('title, event_type, start_date, end_date')
                 ->from('tbl_academic_calendar')
                 ->where('school_id', $school_id)
                 ->where('status', 1)
                 ->where_in('event_type', array('Holiday', 'Term Break'))
                 ->where('start_date <=', $to_date)
                 ->where('end_date >=', $from_date);

        if ($academic_year_id) {
            $this->db->group_start()
                     ->where('academic_year_id', $academic_year_id)
                     ->or_where('academic_year_id IS NULL', NULL, FALSE)
                     ->group_end();
        }

        $holiday_records = $this->db->get()->result();
        $holiday_map = array();

        foreach ($holiday_records as $hr) {
            $h_start = max($from_ts, strtotime($hr->start_date));
            $h_end   = min($to_ts, strtotime($hr->end_date));
            for ($cur = $h_start; $cur <= $h_end; $cur += 86400) {
                $d_str = date('Y-m-d', $cur);
                $holiday_map[$d_str] = $hr->title . ' (' . $hr->event_type . ')';
            }
        }

        // 3. Iterate through each day in range
        $working_dates = array();
        for ($cur = $from_ts; $cur <= $to_ts; $cur += 86400) {
            $d_str = date('Y-m-d', $cur);
            $day_name = date('l', $cur);

            // Skip non-working day of week (e.g. Sunday)
            if (!in_array($day_name, $working_days_config)) {
                continue;
            }

            // Skip official holiday or term break
            if (isset($holiday_map[$d_str])) {
                continue;
            }

            $working_dates[] = $d_str;
        }

        return (object) array(
            'count'         => count($working_dates),
            'working_dates' => $working_dates,
            'holidays'      => $holiday_map,
        );
    }

    /**
     * Get aggregated attendance report for students in selected class and date range
     */
    public function get_range_attendance_report($from_date, $to_date, $class_id, $section_id = NULL, $academic_year_id = NULL, $school_id = NULL)
    {
        $school_id        = $school_id ? (int)$school_id : get_current_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id();

        // 1. Calculate school working days
        $working_info       = $this->get_school_working_days($from_date, $to_date, $academic_year_id, $school_id);
        $total_working_days = $working_info->count;
        $working_dates      = $working_info->working_dates;

        // 2. Query active enrolled students using centralized single source of truth
        $students = $this->get_enrolled_students($class_id, $section_id, $academic_year_id, $school_id);

        $is_higher_sec = is_higher_secondary_class($class_id);

        $empty_summary = (object) array(
            'is_higher_sec'      => $is_higher_sec,
            'total_students'     => count($students),
            'total_working_days' => $total_working_days,
            'total_classes'      => 0,
            'total_present'      => 0,
            'total_leave'        => 0,
            'total_half_day'     => 0,
            'total_late'         => 0,
            'total_absent'       => 0,
            'total_excused'      => 0,
        );

        if (empty($students)) {
            return (object) array(
                'is_higher_sec' => $is_higher_sec,
                'summary'       => $empty_summary,
                'students'      => array(),
                'working_info'  => $working_info,
            );
        }

        // 3. Query student attendance records on working days within date range
        $student_ids = array();
        foreach ($students as $st) {
            $student_ids[] = (int)$st->student_id;
        }

        $counts_map = array();
        if (!empty($working_dates) && !empty($student_ids)) {
            $att_type = $is_higher_sec ? 'Period-wise' : 'Daily';
            $this->db->select('student_id, attendance_status, COUNT(*) as count')
                     ->from($this->table)
                     ->where('school_id', $school_id)
                     ->where('is_deleted', 'n')
                     ->where_in('student_id', $student_ids)
                     ->where('attendance_type', $att_type)
                     ->where_in('attendance_date', $working_dates);

            if ($academic_year_id) {
                $this->db->where('academic_year_id', $academic_year_id);
            }

            $rows = $this->db->group_by(array('student_id', 'attendance_status'))
                             ->get()
                             ->result();

            foreach ($rows as $r) {
                $sid = $r->student_id;
                $st  = $r->attendance_status;
                if (!isset($counts_map[$sid])) {
                    $counts_map[$sid] = array();
                }
                $counts_map[$sid][$st] = (int)$r->count;
            }
        }

        // 5. Aggregate per student and overall summary
        $total_present   = 0;
        $total_half_day  = 0;
        $total_late      = 0;
        $total_absent    = 0;
        $total_conducted = 0;

        foreach ($students as $st) {
            $sid = $st->student_id;
            $m   = isset($counts_map[$sid]) ? $counts_map[$sid] : array();

            $present  = isset($m['Present']) ? $m['Present'] : 0;
            $half_day = (isset($m['Half Day']) ? $m['Half Day'] : 0) + (isset($m['Late / Half Day']) ? $m['Late / Half Day'] : 0) + (isset($m['Half-day']) ? $m['Half-day'] : 0);
            $late     = (isset($m['Late']) ? $m['Late'] : 0) + (isset($m['Late Coming']) ? $m['Late Coming'] : 0);
            $absent   = isset($m['Absent']) ? $m['Absent'] : 0;

            if ($is_higher_sec) {
                // +1 / +2: Total periods conducted for this student
                $student_classes = $present + $half_day + $late + $absent;
                $st->total_classes  = $student_classes;
                $st->present_count  = $present;
                $st->half_day_count = $half_day;
                $st->late_count     = $late;
                $st->absent_count   = $absent;
                $st->leave_count    = 0;
                $st->excused_count  = 0;
                // % = Present Periods ÷ Total Classes × 100
                $st->attendance_pct = ($student_classes > 0) ? round(($present / $student_classes) * 100, 1) : 0.0;
                $total_conducted += $student_classes;
            } else {
                // LKG - 10: Working-day based
                $st->working_days   = $total_working_days;
                $st->present_count  = $present;
                $st->half_day_count = $half_day;
                $st->late_count     = 0; // Late coming removed for LKG-10
                $st->absent_count   = $absent;
                $st->leave_count    = 0;
                $st->excused_count  = 0;
                // % = Present Days ÷ Total Working Days × 100
                $st->attendance_pct = ($total_working_days > 0) ? round(($present / $total_working_days) * 100, 1) : 0.0;
            }

            $total_present  += $present;
            $total_half_day += $half_day;
            $total_late     += ($is_higher_sec ? $late : 0);
            $total_absent   += $absent;
        }

        $summary = (object) array(
            'is_higher_sec'      => $is_higher_sec,
            'total_students'     => count($students),
            'total_working_days' => $total_working_days,
            'total_classes'      => $total_conducted,
            'total_present'      => $total_present,
            'total_half_day'     => $total_half_day,
            'total_late'         => $total_late,
            'total_absent'       => $total_absent,
            'total_leave'        => 0,
            'total_excused'      => 0,
        );

        return (object) array(
            'is_higher_sec' => $is_higher_sec,
            'summary'       => $summary,
            'students'      => $students,
            'working_info'  => $working_info,
        );
    }

    /**
     * Get complete individual student attendance details: overall summary + subject-wise breakdown
     */
    public function get_individual_student_attendance($student_id, $from_date, $to_date, $academic_year_id = NULL)
    {
        $student_id = (int)$student_id;
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id();

        // 1. Calculate school working days (excluding non-working days and academic calendar holidays)
        $working_info       = $this->get_school_working_days($from_date, $to_date, $academic_year_id);
        $total_working_days = $working_info->count;
        $working_dates      = $working_info->working_dates;

        // 2. Fetch student details
        $this->load->model('Student_model');
        $student = $this->Student_model->get_by_id($student_id);

        if (!$student) {
            return NULL;
        }

        $is_higher_sec = is_higher_secondary_class($student->class_id);

        if ($is_higher_sec) {
            // Higher Secondary (+1 / +2): Period-wise Attendance & Subject Breakdown
            $period_records = array();
            $present  = 0;
            $half_day = 0;
            $late     = 0;
            $absent   = 0;

            if (!empty($working_dates)) {
                $this->db->select('a.*, p.period_name, p.period_number, DAYNAME(a.attendance_date) as day_name')
                         ->from($this->table . ' a')
                         ->join('tbl_periods p', 'p.period_id = a.period_id', 'left');

                $this->apply_marker_joins('a');

                $this->db->where('a.student_id', $student_id)
                         ->where('a.attendance_type', 'Period-wise')
                         ->where_in('a.attendance_date', $working_dates);

                if ($academic_year_id) {
                    $this->db->where('a.academic_year_id', $academic_year_id);
                }

                $this->db->order_by('a.attendance_date', 'DESC')
                         ->order_by('p.period_number', 'ASC');
                $period_records = $this->db->get()->result();
                $period_records = $this->format_marker_fields($period_records);

                foreach ($period_records as $pr) {
                    $st = $pr->attendance_status;
                    if ($st === 'Present') $present++;
                    elseif (in_array($st, array('Half Day', 'Late / Half Day', 'Half-day'))) $half_day++;
                    elseif (in_array($st, array('Late', 'Late Coming'))) $late++;
                    elseif ($st === 'Absent') $absent++;
                }
            }

            $total_classes = count($period_records);
            // % = Present Periods ÷ Total Classes × 100
            $overall_pct = ($total_classes > 0) ? round(($present / $total_classes) * 100, 2) : 0.0;

            $overall_summary = (object) array(
                'total_classes'      => $total_classes,
                'total_working_days' => $total_working_days,
                'present'            => $present,
                'half_day'           => $half_day,
                'late'               => $late,
                'absent'             => $absent,
                'leave'              => 0,
                'excused'            => 0,
                'attendance_pct'     => $overall_pct,
            );

            // Subject-wise mapping
            $this->db->select('subject_id, subject_name, subject_code, subject_type')
                     ->from('tbl_subjects')
                     ->where('class_id', $student->class_id)
                     ->where('status', 1)
                     ->where('is_deleted', 'n')
                     ->order_by('subject_name', 'ASC');
            $subjects = $this->db->get()->result();

            // Build Timetable lookup: (day, period_id) -> subject_id
            $this->db->select('day, period_id, subject_id')
                     ->from('tbl_timetable')
                     ->where('class_id', $student->class_id)
                     ->where('status', 1)
                     ->where('is_deleted', 'n');
            if ($academic_year_id) {
                $this->db->where('academic_year_id', $academic_year_id);
            }
            $tt_rows = $this->db->get()->result();
            $timetable_map = array();
            foreach ($tt_rows as $tt) {
                $timetable_map[$tt->day . '_' . $tt->period_id] = (int)$tt->subject_id;
            }

            $subject_stats = array();
            foreach ($subjects as $sub) {
                $subject_stats[$sub->subject_id] = (object) array(
                    'subject_id'     => $sub->subject_id,
                    'subject_name'   => $sub->subject_name,
                    'subject_code'   => $sub->subject_code,
                    'subject_type'   => $sub->subject_type,
                    'total_classes'  => 0,
                    'present'        => 0,
                    'half_day'       => 0,
                    'late'           => 0,
                    'absent'         => 0,
                    'leave'          => 0,
                    'excused'        => 0,
                    'attendance_pct' => 0.0,
                );
            }

            $mapped_period_records = 0;
            foreach ($period_records as $pa) {
                $key = $pa->day_name . '_' . $pa->period_id;
                $sub_id = isset($timetable_map[$key]) ? $timetable_map[$key] : NULL;

                if (!$sub_id && !empty($pa->remarks)) {
                    foreach ($subjects as $sub) {
                        if (stripos($pa->remarks, $sub->subject_name) !== false) {
                            $sub_id = $sub->subject_id;
                            break;
                        }
                    }
                }

                if ($sub_id && isset($subject_stats[$sub_id])) {
                    $mapped_period_records++;
                    $st = $pa->attendance_status;
                    $subject_stats[$sub_id]->total_classes++;

                    if ($st === 'Present') $subject_stats[$sub_id]->present++;
                    elseif (in_array($st, array('Half Day', 'Late / Half Day', 'Half-day'))) $subject_stats[$sub_id]->half_day++;
                    elseif (in_array($st, array('Late', 'Late Coming'))) $subject_stats[$sub_id]->late++;
                    elseif ($st === 'Absent') $subject_stats[$sub_id]->absent++;
                }
            }

            foreach ($subject_stats as $sub_id => $stObj) {
                $tc = $stObj->total_classes;
                $stObj->attendance_pct = ($tc > 0) ? round(($stObj->present / $tc) * 100, 2) : 0.0;
            }

            return (object) array(
                'is_higher_sec'         => TRUE,
                'student'               => $student,
                'overall_summary'       => $overall_summary,
                'subject_wise'          => array_values($subject_stats),
                'has_subject_records'   => ($mapped_period_records > 0),
                'daily_records'         => array(),
                'period_records'        => $period_records,
                'working_info'          => $working_info,
            );
        } else {
            // LKG - 10: Daily Attendance Only
            $present  = 0;
            $half_day = 0;
            $absent   = 0;

            $daily_records = array();
            if (!empty($working_dates)) {
                $this->db->select('a.attendance_id, a.attendance_date, a.attendance_status, a.remarks, a.created_at, a.updated_at, a.marked_by, a.updated_by')
                         ->from($this->table . ' a');

                $this->apply_marker_joins('a');

                $this->db->where('a.student_id', $student_id)
                         ->where('a.attendance_type', 'Daily')
                         ->where_in('a.attendance_date', $working_dates);

                if ($academic_year_id) {
                    $this->db->where('a.academic_year_id', $academic_year_id);
                }

                $this->db->order_by('a.attendance_date', 'DESC');
                $daily_records = $this->db->get()->result();
                $daily_records = $this->format_marker_fields($daily_records);

                foreach ($daily_records as $dr) {
                    $st = $dr->attendance_status;
                    if ($st === 'Present') $present++;
                    elseif (in_array($st, array('Half Day', 'Late / Half Day', 'Half-day'))) $half_day++;
                    elseif ($st === 'Absent') $absent++;
                }
            }

            // Overall Attendance % = Present Days ÷ Total Working Days × 100
            $overall_pct = ($total_working_days > 0) ? round(($present / $total_working_days) * 100, 2) : 0.0;

            $overall_summary = (object) array(
                'total_working_days' => $total_working_days,
                'total_classes'      => 0,
                'present'            => $present,
                'half_day'           => $half_day,
                'late'               => 0,
                'absent'             => $absent,
                'leave'              => 0,
                'excused'            => 0,
                'attendance_pct'     => $overall_pct,
            );

            return (object) array(
                'is_higher_sec'         => FALSE,
                'student'               => $student,
                'overall_summary'       => $overall_summary,
                'subject_wise'          => array(),
                'has_subject_records'   => FALSE,
                'daily_records'         => $daily_records,
                'period_records'        => array(),
                'working_info'          => $working_info,
            );
        }
    }
}

