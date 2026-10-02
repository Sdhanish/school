<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard_model
 *
 * Consolidated dashboard queries for the main School dashboard.
 * All metrics are dynamic and reflect real-time database state
 * respecting the active academic year context and business rules.
 */
class Dashboard_model extends CI_Model {

    // -------------------------------------------------------------------------
    // Summary Stats Cards
    // -------------------------------------------------------------------------

    /**
     * Returns a single object with school-wide counts for the top stat cards.
     * Respects active academic year, active status, and soft-delete flags.
     *
     * @param  int|null $academic_year_id Filter to a specific academic year
     * @return object
     */
    public function get_summary_stats($academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id($school_id);

        // -- Student counts (total, active, male, female, other, new admissions this month) --
        $student_row = $this->db->query("
            SELECT
                COUNT(*)                                                                             AS total_students,
                SUM(CASE WHEN s.status = 1 THEN 1 ELSE 0 END)                                       AS active_students,
                SUM(CASE WHEN s.gender = 'Male'   AND s.status = 1 THEN 1 ELSE 0 END)                AS male_students,
                SUM(CASE WHEN s.gender = 'Female' AND s.status = 1 THEN 1 ELSE 0 END)                AS female_students,
                SUM(CASE WHEN s.gender NOT IN ('Male', 'Female') AND s.status = 1 THEN 1 ELSE 0 END) AS other_students,
                SUM(CASE WHEN s.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END)     AS new_admissions
            FROM tbl_students s
            WHERE s.is_deleted = 'n'
              AND s.academic_year_id = ?
              AND s.school_id = ?
        ", [$academic_year_id, $school_id])->row();

        // -- Staff count (distinguish teachers from non-teaching staff without duplication) --
        $staff_row = $this->db->query("
            SELECT
                COUNT(*) AS total_all_staff,
                SUM(CASE WHEN LOWER(s.staff_type) IN ('teacher', 'teaching') OR s.category = 'Teaching' THEN 1 ELSE 0 END) AS total_teachers,
                SUM(CASE WHEN LOWER(s.staff_type) = 'non_teaching' OR s.category = 'Non-Teaching' OR LOWER(s.staff_type) NOT IN ('teacher', 'teaching') THEN 1 ELSE 0 END) AS total_non_teaching_staff
            FROM tbl_staff s
            WHERE s.is_deleted = 'n'
              AND s.status = 1
              AND s.school_id = ?
        ", [$school_id])->row();

        // -- Class count for current academic year --
        $class_count = (int)$this->db->query("
            SELECT COUNT(*) AS cnt 
            FROM tbl_classes 
            WHERE is_deleted = 'n' 
              AND status = 1 
              AND (academic_year_id = ? OR academic_year_id IS NULL)
              AND school_id = ?
        ", [$academic_year_id, $school_id])->row()->cnt;

        return (object)[
            'total_students'   => (int)($student_row->total_students   ?? 0),
            'active_students'  => (int)($student_row->active_students  ?? 0),
            'male_students'    => (int)($student_row->male_students    ?? 0),
            'female_students'  => (int)($student_row->female_students  ?? 0),
            'other_students'   => (int)($student_row->other_students   ?? 0),
            'new_admissions'   => (int)($student_row->new_admissions   ?? 0),
            'total_teachers'   => (int)($staff_row->total_teachers     ?? 0),
            'total_staff'      => (int)($staff_row->total_non_teaching_staff ?? 0),
            'total_classes'    => $class_count,
        ];
    }

    // -------------------------------------------------------------------------
    // Today's Attendance Summary
    // -------------------------------------------------------------------------

    /**
     * Returns present / half-day / absent counts for today (or a given date).
     * Strictly complies with attendance architecture:
     *   - LKG -> Class 10: Daily morning attendance (Present, Half Day, Absent)
     *   - Grade 11 & 12 (+1 / +2): Period-wise attendance
     *   - Leaves and Excused statuses are removed/omitted.
     *
     * @param  string   $date  Y-m-d  (defaults to today)
     * @param  int|null $academic_year_id
     * @param  int|null $school_id
     * @return object
     */
    public function get_today_attendance($date = NULL, $academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $date = $date ?: date('Y-m-d');
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id($school_id);

        // Total active students in this academic year
        $total_students = (int)$this->db->query("
            SELECT COUNT(*) as cnt 
            FROM tbl_students 
            WHERE is_deleted = 'n' 
              AND status = 1 
              AND academic_year_id = ?
              AND school_id = ?
        ", [$academic_year_id, $school_id])->row()->cnt;

        // Query attendance records for this date and academic year
        $records = $this->db->query("
            SELECT 
                a.attendance_id,
                a.student_id,
                a.class_id,
                c.class_name,
                a.attendance_type,
                a.attendance_status,
                a.period_id
            FROM tbl_attendance a
            JOIN tbl_classes c ON c.class_id = a.class_id AND c.is_deleted = 'n' AND c.school_id = ?
            WHERE a.attendance_date = ?
              AND a.academic_year_id = ?
              AND a.is_deleted = 'n'
              AND a.school_id = ?
        ", [$school_id, $date, $academic_year_id, $school_id])->result();

        $student_status = [];
        foreach ($records as $rec) {
            $sid = (int)$rec->student_id;
            $is_hs = is_higher_secondary_class($rec->class_name);

            if (!$is_hs) {
                // LKG -> Class 10: Daily attendance
                if ($rec->attendance_type === 'Daily') {
                    $st = $rec->attendance_status;
                    // Disallow removed Leave / Excused statuses
                    if (in_array($st, ['Leave', 'Excused'])) continue;
                    if (in_array($st, ['Half Day', 'Late / Half Day', 'Half-day'])) {
                        $student_status[$sid] = 'Half Day';
                    } elseif ($st === 'Present') {
                        $student_status[$sid] = 'Present';
                    } elseif ($st === 'Absent') {
                        $student_status[$sid] = 'Absent';
                    }
                }
            } else {
                // Grade 11 & 12 (+1 / +2): Period-wise attendance
                if ($rec->attendance_type === 'Period-wise') {
                    $st = $rec->attendance_status;
                    if (in_array($st, ['Leave', 'Excused'])) continue;
                    if (!isset($student_status[$sid])) {
                        $student_status[$sid] = [];
                    }
                    if (is_array($student_status[$sid])) {
                        $student_status[$sid][] = $st;
                    }
                }
            }
        }

        // Aggregate resolved attendance counts
        $present  = 0;
        $half_day = 0;
        $absent   = 0;

        foreach ($student_status as $sid => $status) {
            if (is_array($status)) {
                // Higher secondary period-wise aggregation
                $has_present = in_array('Present', $status);
                $has_absent  = in_array('Absent', $status);
                $has_half    = in_array('Half Day', $status) || in_array('Half-day', $status);

                if ($has_present && $has_absent) {
                    $resolved = 'Half Day';
                } elseif ($has_present) {
                    $resolved = 'Present';
                } elseif ($has_half) {
                    $resolved = 'Half Day';
                } elseif ($has_absent) {
                    $resolved = 'Absent';
                } else {
                    $resolved = 'Present';
                }
            } else {
                $resolved = $status;
            }

            if ($resolved === 'Present') {
                $present++;
            } elseif ($resolved === 'Half Day') {
                $half_day++;
            } elseif ($resolved === 'Absent') {
                $absent++;
            }
        }

        $total_marked = $present + $half_day + $absent;
        $pct          = ($total_marked > 0) ? round(($present / $total_marked) * 100, 1) : 0;
        $present_pct  = ($total_marked > 0) ? round(($present / $total_marked) * 100, 1) : 0;
        $half_day_pct = ($total_marked > 0) ? round(($half_day / $total_marked) * 100, 1) : 0;
        $absent_pct   = ($total_marked > 0) ? round(($absent / $total_marked) * 100, 1) : 0;

        return (object)[
            'present'        => $present,
            'half_day'       => $half_day,
            'absent'         => $absent,
            'total_marked'   => $total_marked,
            'total_students' => $total_students,
            'pct'            => $pct,
            'present_pct'    => $present_pct,
            'half_day_pct'   => $half_day_pct,
            'absent_pct'     => $absent_pct,
            'date'           => $date,
        ];
    }

    // -------------------------------------------------------------------------
    // Fees Summary
    // -------------------------------------------------------------------------

    /**
     * Returns today's collection, monthly collection (MTD), total pending, and overdue fees.
     * Filtered by active academic year and school_id.
     *
     * @param  int|null $academic_year_id
     * @param  int|null $school_id
     * @return object
     */
    public function get_fees_summary($academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $today            = date('Y-m-d');
        $month_start      = date('Y-m-01');
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id($school_id);

        // Month-to-date and today's collections from tbl_finance_transaction_items
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(CASE WHEN t.transaction_date = ? THEN ti.credit ELSE 0 END), 0) AS today_collection,
                COALESCE(SUM(CASE WHEN t.transaction_date >= ? AND t.transaction_date <= ? THEN ti.credit ELSE 0 END), 0) AS monthly_collection
            FROM tbl_finance_transaction_items ti
            JOIN tbl_finance_transactions t ON t.id = ti.transaction_id AND t.status = 'Posted'
            WHERE t.school_id = ?
              AND (t.academic_year_id = ? OR t.academic_year_id IS NULL)
              AND ti.student_id IS NOT NULL
        ", [$today, $month_start, $today, $school_id, $academic_year_id])->row();

        // Total pending fees and overdue fees from tbl_finance_fee_assignments
        $pending_row = $this->db->query("
            SELECT
                COALESCE(SUM(due_amount), 0) AS total_pending,
                COALESCE(SUM(CASE WHEN due_date < ? THEN due_amount ELSE 0 END), 0) AS overdue_amount
            FROM tbl_finance_fee_assignments
            WHERE is_deleted = 'n'
              AND status IN ('Pending','Partially_Paid')
              AND academic_year_id = ?
              AND school_id = ?
        ", [$today, $academic_year_id, $school_id])->row();

        return (object)[
            'today_collection'   => (float)($row->today_collection          ?? 0),
            'monthly_collection' => (float)($row->monthly_collection        ?? 0),
            'total_pending'      => (float)($pending_row->total_pending     ?? 0),
            'overdue_amount'     => (float)($pending_row->overdue_amount    ?? 0),
        ];
    }

    // -------------------------------------------------------------------------
    // Upcoming Events
    // -------------------------------------------------------------------------

    /**
     * Returns upcoming events on or after today, ordered by date ASC.
     *
     * @param  int $limit
     * @param  int|null $school_id
     * @return array
     */
    public function get_upcoming_events($limit = 5, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db->query("
            SELECT event_id, title, event_date, audience, venue
            FROM tbl_events
            WHERE event_date >= CURDATE()
              AND is_deleted = 'n'
              AND status = 1
              AND school_id = ?
            ORDER BY event_date ASC
            LIMIT ?
        ", [$school_id, (int)$limit])->result();
    }

    // -------------------------------------------------------------------------
    // Recent Notices
    // -------------------------------------------------------------------------

    /**
     * Returns recently published notices, ordered by publish_date DESC.
     *
     * @param  int $limit
     * @param  int|null $academic_year_id
     * @param  int|null $school_id
     * @return array
     */
    public function get_recent_notices($limit = 5, $academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id($school_id);

        return $this->db->query("
            SELECT notice_id, title, posted_by, publish_date, category, priority
            FROM tbl_notices
            WHERE status = 'Published'
              AND is_deleted = 'n'
              AND (academic_year_id = ? OR academic_year_id IS NULL)
              AND school_id = ?
            ORDER BY publish_date DESC, created_at DESC
            LIMIT ?
        ", [$academic_year_id, $school_id, (int)$limit])->result();
    }

    // -------------------------------------------------------------------------
    // Students by Class (Ordered naturally by Academic Groups)
    // -------------------------------------------------------------------------

    /**
     * Returns student count per class for the dashboard overview widget.
     * Respects active classes, academic year, school_id, and active student status.
     *
     * @param  int|null $academic_year_id
     * @param  int|null $school_id
     * @return array  Each row: class_id, class_name, student_count
     */
    public function get_students_by_class($academic_year_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $academic_year_id = $academic_year_id ? (int)$academic_year_id : get_current_academic_year_id($school_id);

        return $this->db->query("
            SELECT c.class_id, c.class_name,
                   COUNT(s.student_id) AS student_count
            FROM tbl_classes c
            LEFT JOIN tbl_academic_groups ag ON ag.academic_group_id = c.academic_group_id AND ag.is_deleted = 'n' AND ag.school_id = ?
            LEFT JOIN tbl_students s
                   ON s.class_id = c.class_id
                  AND s.is_deleted = 'n'
                  AND s.status = 1
                  AND s.academic_year_id = ?
                  AND s.school_id = ?
            WHERE c.is_deleted = 'n'
              AND c.status = 1
              AND (c.academic_year_id = ? OR c.academic_year_id IS NULL)
              AND c.school_id = ?
            GROUP BY c.class_id, c.class_name, ag.display_order
            ORDER BY COALESCE(ag.display_order, 999) ASC, c.class_id ASC
        ", [$school_id, $academic_year_id, $school_id, $academic_year_id, $school_id])->result();
    }
}
