<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Academic_year_model extends CI_Model {

    protected $table = 'tbl_academic_years';
    protected $primaryKey = 'academic_year_id';

    public function get_all($school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('school_id', $sid)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('start_date', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_dropdown($school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        return $this->db
            ->select('academic_year_id, year_name, is_active, start_date, end_date, school_id')
            ->where('school_id', $sid)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('start_date', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_available_years($school_id = null)
    {
        return $this->get_dropdown($school_id);
    }

    public function get_by_id($id, $school_id = null)
    {
        static $cached_ids = [];
        $id = (int)$id;
        $cache_key = $id . '_' . ($school_id !== null ? (int)$school_id : 'any');
        if (isset($cached_ids[$cache_key])) {
            return $cached_ids[$cache_key];
        }

        $this->db
            ->where($this->primaryKey, $id)
            ->where('is_deleted', 'n');

        if ($school_id !== null && (int)$school_id > 0) {
            $this->db->where('school_id', (int)$school_id);
        }

        $res = $this->db->get($this->table)->row();
        $cached_ids[$cache_key] = $res;
        return $res;
    }

    /**
     * Retrieve ONLY the academic year explicitly marked is_active = 1 for a school.
     * Does NOT fall back to newest/oldest year.
     *
     * @param  int|null $school_id
     * @return object|null
     */
    protected static $_cached_strictly_active = [];
    protected static $_cached_active = [];

    public function clear_cache()
    {
        self::$_cached_strictly_active = [];
        self::$_cached_active = [];
    }

    /**
     * Retrieve ONLY the academic year explicitly marked is_active = 1 for the school.
     *
     * @param  int|null $school_id
     * @return object|null
     */
    public function get_strictly_active_year($school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        if (isset(self::$_cached_strictly_active[$sid])) {
            return self::$_cached_strictly_active[$sid] ?: NULL;
        }

        $active = $this->db
            ->where('school_id', $sid)
            ->where('is_active', 1)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();

        self::$_cached_strictly_active[$sid] = $active ?: FALSE;
        return $active ?: NULL;
    }

    /**
     * Retrieve all academic years currently marked is_active = 1 for the school.
     *
     * @param  int|null $school_id
     * @return array
     */
    public function get_all_active_years($school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('school_id', $sid)
            ->where('is_active', 1)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('start_date', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_active_year($school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        if (isset(self::$_cached_active[$sid])) {
            return self::$_cached_active[$sid];
        }

        $active = $this->get_strictly_active_year($sid);

        if (!$active) {
            log_message('error', "Academic_year_model: No active academic year configured for school {$sid}. Falling back to latest start_date.");
            // Fallback to latest valid academic year for this school
            $active = $this->db
                ->where('school_id', $sid)
                ->where('status', 1)
                ->where('is_deleted', 'n')
                ->order_by('start_date', 'DESC')
                ->limit(1)
                ->get($this->table)
                ->row();
        }

        self::$_cached_active[$sid] = $active;
        return $active;
    }

    public function get_active($school_id = null)
    {
        return $this->get_active_year($school_id);
    }

    /**
     * Atomically set an academic year as active for its school.
     *
     * @param  int      $id
     * @param  int|null $school_id
     * @return bool
     */
    public function set_active($id, $school_id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        $target = $this->get_by_id($id);
        if (!$target || $target->is_deleted === 'y' || (int)$target->status !== 1) {
            log_message('error', "Academic_year_model::set_active failed: Academic year ID {$id} not found, deleted, or disabled.");
            return false;
        }

        if ($school_id !== null && (int)$school_id > 0 && (int)$target->school_id !== (int)$school_id) {
            log_message('error', "Academic_year_model::set_active unauthorized cross-school attempt: target school {$target->school_id} vs requested {$school_id}");
            return false;
        }

        $sid = (int)$target->school_id;

        $this->db->trans_begin();

        try {
            // Step 1: Set only this school's academic years to inactive
            $this->db->where('school_id', $sid)->update($this->table, array('is_active' => 0));

            // Step 2: Set target academic year to active
            $this->db
                ->where($this->primaryKey, $id)
                ->where('school_id', $sid)
                ->update($this->table, array('is_active' => 1));

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                log_message('error', "Academic_year_model::set_active transaction failed for ID {$id}");
                return false;
            }

            $this->db->trans_commit();
            $this->clear_cache();
            if (function_exists('clear_academic_year_cache')) {
                clear_academic_year_cache();
            }
            return true;
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', "Academic_year_model::set_active exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check whether an academic year name already exists in tbl_academic_years for the school.
     * Normalizes input whitespace and checks both exact and formatted variants.
     *
     * @param string   $year_name
     * @param int|null $exclude_id  Optional record ID to exclude during edits
     * @param int|null $school_id   School ID context
     * @return bool
     */
    public function is_year_name_exists($year_name, $exclude_id = NULL, $school_id = NULL)
    {
        $year_name = trim(preg_replace('/\s+/', ' ', (string)$year_name));
        if ($year_name === '') {
            return false;
        }

        $sid = $school_id !== null ? (int)$school_id : get_current_school_id();
        $compact_name = preg_replace('/\s*-\s*/', '-', $year_name);
        $spaced_name  = preg_replace('/\s*-\s*/', ' - ', $year_name);

        $this->db->where('school_id', $sid);
        $this->db->group_start()
            ->where('year_name', $year_name)
            ->or_where('year_name', $compact_name)
            ->or_where('year_name', $spaced_name)
            ->group_end();

        if ($exclude_id !== NULL && (int)$exclude_id > 0) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }

        return ($this->db->count_all_results($this->table) > 0);
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = get_current_school_id();
        }

        if (!empty($data['is_active'])) {
            $this->db->where('school_id', (int)$data['school_id'])->update($this->table, array('is_active' => 0));
        }

        // Defensive handling for race condition on uk_school_year_name unique key
        $saved_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $res = $this->db->insert($this->table, $data);
        $err = $this->db->error();

        $this->db->db_debug = $saved_debug;

        if (!$res) {
            if (isset($err['code']) && (int)$err['code'] === 1062) {
                log_message('error', 'Duplicate academic year entry caught on insert: ' . ($err['message'] ?? ''));
                return false;
            }
            if ($saved_debug) {
                $this->db->display_error($err['message'] ?? 'Database error');
            }
            return false;
        }

        $this->clear_cache();
        if (function_exists('clear_academic_year_cache')) {
            clear_academic_year_cache();
        }

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['is_active'])) {
            $this->db->update($this->table, array('is_active' => 0));
        }

        // Defensive handling for race condition on uk_academic_year_name unique key
        $saved_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $res = $this->db
            ->where($this->primaryKey, $id)
            ->update($this->table, $data);
        $err = $this->db->error();

        $this->db->db_debug = $saved_debug;

        if (!$res) {
            if (isset($err['code']) && (int)$err['code'] === 1062) {
                log_message('error', 'Duplicate academic year entry caught on update: ' . ($err['message'] ?? ''));
                return false;
            }
            if ($saved_debug) {
                $this->db->display_error($err['message'] ?? 'Database error');
            }
            return false;
        }

        $this->clear_cache();
        if (function_exists('clear_academic_year_cache')) {
            clear_academic_year_cache();
        }

        return $res;
    }

    /**
     * Get all dependent records referencing an academic year.
     * Returns an associative array of [label => count] for each table with records > 0.
     *
     * @param int $id
     * @param int|null $school_id
     * @return array
     */
    public function get_dependencies($id, $school_id = null)
    {
        $id = (int)$id;
        $dependencies = [];

        $tables = [
            'tbl_students'                       => ['col' => 'academic_year_id', 'label' => 'Students'],
            'tbl_classes'                        => ['col' => 'academic_year_id', 'label' => 'Classes'],
            'tbl_attendance'                     => ['col' => 'academic_year_id', 'label' => 'Attendance Records'],
            'tbl_finance_fee_structures'        => ['col' => 'academic_year_id', 'label' => 'Fee Structures'],
            'tbl_finance_fee_assignments'       => ['col' => 'academic_year_id', 'label' => 'Fee Invoices'],
            'tbl_exams'                          => ['col' => 'academic_year_id', 'label' => 'Examinations'],
            'tbl_exam_schedules'                 => ['col' => 'academic_year_id', 'label' => 'Exam Schedules'],
            'tbl_exam_marks'                     => ['col' => 'academic_year_id', 'label' => 'Exam Marks'],
            'tbl_timetable'                      => ['col' => 'academic_year_id', 'label' => 'Timetable Entries'],
            'tbl_subject_allocations'            => ['col' => 'academic_year_id', 'label' => 'Subject Allocations'],
            'tbl_class_teachers'                 => ['col' => 'academic_year_id', 'label' => 'Class Teachers'],
            'tbl_subject_teachers'               => ['col' => 'academic_year_id', 'label' => 'Subject Teachers'],
            'tbl_academic_calendar'              => ['col' => 'academic_year_id', 'label' => 'Academic Calendar'],
            'tbl_student_promotions'             => ['col' => 'from_academic_year_id', 'label' => 'Student Promotions'],
            'tbl_student_results'                => ['col' => 'academic_year_id', 'label' => 'Student Results'],
            'tbl_admissions'                     => ['col' => 'academic_year_id', 'label' => 'Admissions'],
            'tbl_assignments'                    => ['col' => 'academic_year_id', 'label' => 'Assignments'],
            'tbl_certificates'                   => ['col' => 'academic_year_id', 'label' => 'Certificates'],
            'tbl_student_id_cards'               => ['col' => 'academic_year_id', 'label' => 'ID Cards'],
            'tbl_student_transfers'              => ['col' => 'academic_year_id', 'label' => 'Transfers'],
            'tbl_teacher_workload'               => ['col' => 'academic_year_id', 'label' => 'Teacher Workload'],
        ];

        foreach ($tables as $tbl => $cfg) {
            if ($this->db->table_exists($tbl)) {
                $this->db->where($cfg['col'], $id);
                if ($school_id !== null && (int)$school_id > 0) {
                    $this->db->where('school_id', (int)$school_id);
                }
                $count = $this->db->count_all_results($tbl);
                if ($count > 0) {
                    $dependencies[$cfg['label']] = $count;
                }
            }
        }

        // Also check tbl_student_promotions to_academic_year_id
        if ($this->db->table_exists('tbl_student_promotions')) {
            $this->db->where('to_academic_year_id', $id);
            if ($school_id !== null && (int)$school_id > 0) {
                $this->db->where('school_id', (int)$school_id);
            }
            $to_count = $this->db->count_all_results('tbl_student_promotions');
            if ($to_count > 0) {
                $dependencies['Student Promotions (Target)'] = $to_count;
            }
        }

        return $dependencies;
    }

    /**
     * Permanently delete an academic year record from tbl_academic_years.
     * Uses database transaction and defensively catches foreign key violations.
     *
     * @param int $id
     * @return bool
     */
    public function permanent_delete($id)
    {
        $id = (int)$id;

        $saved_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        $this->db->trans_start();
        $this->db->where($this->primaryKey, $id)->delete($this->table);
        $this->db->trans_complete();

        $status = $this->db->trans_status();
        $err = $this->db->error();

        $this->db->db_debug = $saved_debug;

        if (!$status) {
            if (isset($err['code']) && (int)$err['code'] === 1451) {
                log_message('error', 'Foreign key constraint prevents deleting academic year ID: ' . $id);
                return false;
            }
            if ($saved_debug && !empty($err['message'])) {
                $this->db->display_error($err['message']);
            }
            return false;
        }

        return true;
    }

    public function soft_delete($id)
    {
        return $this->db
            ->where($this->primaryKey, $id)
            ->update($this->table, ['status' => 0, 'is_deleted' => 'y']);
    }
}
