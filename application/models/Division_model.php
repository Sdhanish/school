<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Division_model extends CI_Model {

    protected $table = 'tbl_divisions';
    protected $primaryKey = 'division_id';

    /**
     * Get all divisions for a class or across all classes.
     * If a specific class_id is passed and has no division records, automatically provides default Division 'A'.
     *
     * @param int|null $class_id
     * @return array
     */
    public function get_all($class_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        $this->db
            ->select('div.*, c.class_name, c.academic_group_id, c.academic_year_id, ag.group_name, s.full_name as class_teacher_name, (SELECT COUNT(student_id) FROM tbl_students WHERE division_id = div.division_id AND status = 1 AND is_deleted = \'n\' AND school_id = ' . (int)$school_id . ') as student_count')
            ->from('tbl_divisions div')
            ->join('tbl_classes c', 'c.class_id = div.class_id', 'left')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
            ->join('tbl_staff s', 's.staff_id = div.class_teacher_id', 'left')
            ->where('div.status', 1)
            ->where('div.is_deleted', 'n')
            ->where('c.is_deleted', 'n')
            ->where('div.school_id', $school_id)
            ->order_by('ag.display_order', 'ASC')
            ->order_by('div.class_id', 'ASC')
            ->order_by('div.division_name', 'ASC');

        if ($class_id) {
            $this->db->where('div.class_id', (int)$class_id);
        }

        $results = $this->db->get()->result();

        // If a specific class was requested and no divisions exist in DB, provide default Division 'A'
        if ($class_id && empty($results)) {
            $default_div_id = $this->get_default_division_id($class_id, $school_id);
            if ($default_div_id) {
                $this->db
                    ->select('div.*, c.class_name, c.academic_group_id, c.academic_year_id, ag.group_name, s.full_name as class_teacher_name, 0 as student_count')
                    ->from('tbl_divisions div')
                    ->join('tbl_classes c', 'c.class_id = div.class_id', 'left')
                    ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
                    ->join('tbl_staff s', 's.staff_id = div.class_teacher_id', 'left')
                    ->where('div.division_id', $default_div_id)
                    ->where('div.school_id', $school_id);
                $results = $this->db->get()->result();
            }
        }

        // Provide backward-compatible section_id & section_name properties on records
        foreach ($results as &$r) {
            if (!isset($r->section_id) && isset($r->division_id)) {
                $r->section_id = $r->division_id;
            }
            if (!isset($r->section_name) && isset($r->division_name)) {
                $r->section_name = $r->division_name;
            }
        }

        return $results;
    }

    /**
     * Centralized method: Get all divisions belonging to a specific class.
     * Ensures Division 'A' is always present either from DB or as default fallback.
     *
     * @param int $class_id
     * @return array
     */
    public function get_divisions_for_class($class_id)
    {
        return $this->get_all($class_id);
    }

    /**
     * Alias for get_all($class_id)
     */
    public function get_by_class($class_id)
    {
        return $this->get_all($class_id);
    }

    /**
     * Alias for get_all($class_id)
     */
    public function get_divisions_by_class($class_id)
    {
        return $this->get_all($class_id);
    }

    /**
     * Dropdown list of divisions.
     *
     * @param int|null $class_id
     * @return array
     */
    public function get_dropdown($class_id = NULL)
    {
        return $this->get_all($class_id);
    }

    /**
     * Get single division by ID.
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $row = $this->db
            ->select('div.*, c.class_name, c.academic_year_id, s.full_name as class_teacher_name')
            ->from('tbl_divisions div')
            ->join('tbl_classes c', 'c.class_id = div.class_id', 'left')
            ->join('tbl_staff s', 's.staff_id = div.class_teacher_id', 'left')
            ->where('div.division_id', (int)$id)
            ->where('div.school_id', $school_id)
            ->where('div.is_deleted', 'n')
            ->get()
            ->row();

        if ($row) {
            $row->section_id   = $row->division_id;
            $row->section_name = $row->division_name;
        }

        return $row;
    }

    /**
     * Calculate the next alphabetical division name starting from 'B'.
     * Since 'A' is the reserved system default:
     * - No divisions in DB (or only A) -> returns 'B'
     * - B exists in DB -> returns 'C'
     * - B, C exist in DB -> returns 'D'
     * - B, C, D exist in DB -> returns 'E', etc.
     *
     * @param int $class_id
     * @return string Next available letter (B..Z)
     */
    public function get_next_division_name($class_id)
    {
        $class_id = (int)$class_id;

        // Query existing division names for this class
        $existing = $this->db
            ->select('division_name')
            ->where('class_id', $class_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->order_by('division_name', 'ASC')
            ->get($this->table)
            ->result_array();

        $existing_names = array_map(function($r) {
            return strtoupper(trim($r['division_name']));
        }, $existing);

        // Iterate through alphabet starting from 'B' (ASCII 66) to 'Z' (ASCII 90)
        for ($i = 66; $i <= 90; $i++) {
            $candidate = chr($i);
            if (!in_array($candidate, $existing_names, true)) {
                return $candidate;
            }
        }

        // Fallback if all B..Z are exhausted: A1, A2...
        return 'Div-' . (count($existing_names) + 1);
    }

    /**
     * Get a valid database division_id for Division 'A'.
     * Uses direct query to avoid polluting CodeIgniter Active Record state.
     *
     * @param int|null $class_id
     * @param int|null $school_id
     * @return int
     */
    public function get_default_division_id($class_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        static $cached_default_ids = array();
        $cache_key = ($class_id ? (int)$class_id : 'global') . '_' . $school_id;
        if (isset($cached_default_ids[$cache_key])) {
            return $cached_default_ids[$cache_key];
        }

        if ($class_id) {
            $class_id = (int)$class_id;
            $row = $this->db->query("SELECT division_id FROM tbl_divisions WHERE class_id = ? AND division_name = 'A' AND status = 1 AND is_deleted = 'n' AND school_id = ? LIMIT 1", [$class_id, $school_id])->row();
            if ($row) {
                $cached_default_ids[$cache_key] = (int)$row->division_id;
                return $cached_default_ids[$cache_key];
            }

            // Check if any other active division exists for this class
            $any_class_div = $this->db->query("SELECT division_id FROM tbl_divisions WHERE class_id = ? AND status = 1 AND is_deleted = 'n' AND school_id = ? ORDER BY division_id ASC LIMIT 1", [$class_id, $school_id])->row();
            if ($any_class_div) {
                $cached_default_ids[$cache_key] = (int)$any_class_div->division_id;
                return $cached_default_ids[$cache_key];
            }

            // Provision a default Division 'A' specifically for this class
            $this->db->insert('tbl_divisions', [
                'class_id'      => $class_id,
                'division_name' => 'A',
                'status'        => 1,
                'is_deleted'    => 'n',
                'school_id'     => $school_id,
                'created_at'    => date('Y-m-d H:i:s')
            ]);
            $new_id = (int)$this->db->insert_id();
            if ($new_id > 0) {
                $cached_default_ids[$cache_key] = $new_id;
                return $cached_default_ids[$cache_key];
            }
        }

        $row_a = $this->db->query("SELECT division_id FROM tbl_divisions WHERE division_name = 'A' AND status = 1 AND is_deleted = 'n' AND school_id = ? LIMIT 1", [$school_id])->row();
        if ($row_a) {
            $cached_default_ids[$cache_key] = (int)$row_a->division_id;
            return $cached_default_ids[$cache_key];
        }

        $any_div = $this->db->query("SELECT division_id FROM tbl_divisions WHERE status = 1 AND is_deleted = 'n' AND school_id = ? LIMIT 1", [$school_id])->row();
        $cached_default_ids[$cache_key] = $any_div ? (int)$any_div->division_id : 12;
        return $cached_default_ids[$cache_key];
    }

    /**
     * Check if a division belongs to a given class.
     *
     * @param int $division_id
     * @param int $class_id
     * @return bool
     */
    public function is_valid_division_for_class($division_id, $class_id)
    {
        if (empty($division_id) || empty($class_id)) {
            return false;
        }

        $class_id = (int)$class_id;
        $division_id = (int)$division_id;

        $count = $this->db
            ->where('division_id', $division_id)
            ->where('class_id', $class_id)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);

        return ($count > 0);
    }

    /**
     * Check if a class has other configured divisions besides default Division A.
     * Uses direct query to avoid polluting CodeIgniter Active Record state.
     *
     * @param int $class_id
     * @param int|null $default_div_id
     * @return bool
     */
    public function has_other_divisions($class_id, $default_div_id = NULL)
    {
        if (!$class_id) return false;
        if ($default_div_id === NULL) {
            $default_div_id = $this->get_default_division_id($class_id);
        }
        $row = $this->db->query(
            "SELECT COUNT(*) as c FROM tbl_divisions WHERE class_id = ? AND division_id != ? AND status = 1 AND is_deleted = 'n'",
            [(int)$class_id, (int)$default_div_id]
        )->row();
        return ($row && (int)$row->c > 0);
    }

    public function insert($data)
    {
        // Remap section_* to division_* if present
        if (isset($data['section_name']) && !isset($data['division_name'])) {
            $data['division_name'] = $data['section_name'];
            unset($data['section_name']);
        }
        if (isset($data['section_id']) && !isset($data['division_id'])) {
            $data['division_id'] = $data['section_id'];
            unset($data['section_id']);
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
        if (isset($data['section_name']) && !isset($data['division_name'])) {
            $data['division_name'] = $data['section_name'];
            unset($data['section_name']);
        }
        if (isset($data['section_id']) && !isset($data['division_id'])) {
            $data['division_id'] = $data['section_id'];
            unset($data['section_id']);
        }
        unset($data['school_id']);
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    /**
     * Check for duplicate division name in the same class.
     *
     * @param int $class_id
     * @param string $division_name
     * @param int|null $exclude_id
     * @param int|null $school_id
     * @return bool
     */
    public function check_duplicate($class_id, $division_name, $exclude_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->where('class_id', (int)$class_id)
            ->where('school_id', $school_id)
            ->where('LOWER(TRIM(division_name))', strtolower(trim($division_name)))
            ->where('status', 1)
            ->where('is_deleted', 'n');

        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function soft_delete($id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, ['status' => 0, 'is_deleted' => 'y']);

        $this->db
            ->where('division_id', (int)$id)
            ->where('school_id', $school_id)
            ->update('tbl_class_teachers', ['status' => 0, 'is_deleted' => 'y']);

        return TRUE;
    }

    public function delete($id, $school_id = NULL)
    {
        return $this->soft_delete($id, $school_id);
    }

    /**
     * Check if a division has dependent records across modules.
     * Prevents accidental deletion of divisions with active students, attendance, exams, etc.
     *
     * @param int $division_id
     * @return array|false
     */
    public function has_dependencies($division_id)
    {
        $division_id = (int)$division_id;
        $dependencies = [];

        // 1. Students enrolled in this division
        $student_count = $this->db->where('division_id', $division_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_students');
        if ($student_count > 0) {
            $dependencies['students'] = $student_count;
        }

        // 2. Attendance records
        $att_count = $this->db->where('division_id', $division_id)->where('is_deleted', 'n')->count_all_results('tbl_attendance');
        if ($att_count > 0) {
            $dependencies['attendance'] = $att_count;
        }

        // 3. Exam marks
        $marks_count = $this->db->where('division_id', $division_id)->where('is_deleted', 'n')->count_all_results('tbl_exam_marks');
        if ($marks_count > 0) {
            $dependencies['exam_marks'] = $marks_count;
        }

        // 4. Exam schedules
        $schedules_count = $this->db->where('division_id', $division_id)->where('is_deleted', 'n')->count_all_results('tbl_exam_schedules');
        if ($schedules_count > 0) {
            $dependencies['exam_schedules'] = $schedules_count;
        }

        // 5. Timetable
        $timetable_count = $this->db->where('division_id', $division_id)->count_all_results('tbl_timetable');
        if ($timetable_count > 0) {
            $dependencies['timetable'] = $timetable_count;
        }

        // 6. Homework
        $homework_count = $this->db->where('division_id', $division_id)->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_homework');
        if ($homework_count > 0) {
            $dependencies['homework'] = $homework_count;
        }

        return !empty($dependencies) ? $dependencies : false;
    }


    /**
     * Get grouped hierarchy for listing:
     * Academic Group | Class | Divisions
     *
     * @param int|null $academic_group_id
     * @param int|null $class_id
     * @return array
     */
    public function get_hierarchy($academic_group_id = NULL, $class_id = NULL)
    {
        $this->db
            ->select('c.class_id, c.class_name, c.academic_group_id, COALESCE(ag.group_name, "General") as group_name, COALESCE(ag.display_order, 99) as display_order')
            ->from('tbl_classes c')
            ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'left')
            ->where('c.status', 1)
            ->where('c.is_deleted', 'n')
            ->order_by('display_order', 'ASC')
            ->order_by('c.class_id', 'ASC');

        if ($academic_group_id) {
            $this->db->where('c.academic_group_id', (int)$academic_group_id);
        }
        if ($class_id) {
            $this->db->where('c.class_id', (int)$class_id);
        }

        $classes = $this->db->get()->result();
        $hierarchy = [];

        foreach ($classes as $cls) {
            $divisions = $this->get_all($cls->class_id);
            $div_names = [];
            foreach ($divisions as $d) {
                $div_names[] = $d->division_name;
            }
            $hierarchy[] = (object)[
                'academic_group_id' => $cls->academic_group_id,
                'group_name'        => $cls->group_name,
                'class_id'          => $cls->class_id,
                'class_name'        => $cls->class_name,
                'divisions'         => $divisions,
                'division_names'    => implode(', ', $div_names),
                'division_count'    => count($divisions),
            ];
        }

        return $hierarchy;
    }

    // =========================================================================
    // Backward-Compatibility Aliases
    // =========================================================================
    public function get_sections_for_class($class_id) { return $this->get_divisions_for_class($class_id); }
    public function get_sections_by_class($class_id) { return $this->get_divisions_by_class($class_id); }
    public function get_next_section_name($class_id) { return $this->get_next_division_name($class_id); }
    public function get_default_section_id($class_id = NULL) { return $this->get_default_division_id($class_id); }
    public function has_other_sections($class_id, $default_id = NULL) { return $this->has_other_divisions($class_id, $default_id); }
}
