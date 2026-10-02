<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timetable_model extends CI_Model {

    protected $table = 'tbl_timetable';
    protected $primaryKey = 'timetable_id';

    public function get_entries($filters = array())
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->select('tt.*, y.year_name, c.class_name, d.division_name as division_name, d.division_name as section_name, p.period_name, p.period_number, p.start_time, p.end_time, p.period_order, sub.subject_name, sub.subject_code, s.full_name as teacher_name, s.employee_code')
            ->from('tbl_timetable tt')
            ->join('tbl_academic_years y', 'y.academic_year_id = tt.academic_year_id', 'left')
            ->join('tbl_classes c', 'c.class_id = tt.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = tt.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = tt.period_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = tt.subject_id', 'left')
            ->join('tbl_staff s', 's.staff_id = tt.teacher_id', 'left')
            ->where('tt.status', 1)
            ->where('tt.school_id', $school_id)
            ->order_by('FIELD(tt.day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")')
            ->order_by('p.period_order', 'ASC')
            ->order_by('p.start_time', 'ASC');

        if (!empty($filters['academic_year_id'])) {
            $this->db->where('tt.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('tt.class_id', $filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->where('tt.division_id', $filters['division_id']);
        }
        if (!empty($filters['day'])) {
            $this->db->where('tt.day', $filters['day']);
        }
        if (!empty($filters['teacher_id'])) {
            $this->db->where('tt.teacher_id', $filters['teacher_id']);
        }
        if (!empty($filters['period_id'])) {
            $this->db->where('tt.period_id', $filters['period_id']);
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db
            ->select('tt.*, y.year_name, c.class_name, d.division_name as division_name, d.division_name as section_name, p.period_name, p.start_time, p.end_time, sub.subject_name, sub.subject_code, s.full_name as teacher_name')
            ->from('tbl_timetable tt')
            ->join('tbl_academic_years y', 'y.academic_year_id = tt.academic_year_id', 'left')
            ->join('tbl_classes c', 'c.class_id = tt.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = tt.division_id', 'left')
            ->join('tbl_periods p', 'p.period_id = tt.period_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = tt.subject_id', 'left')
            ->join('tbl_staff s', 's.staff_id = tt.teacher_id', 'left')
            ->where('tt.timetable_id', $id)
            ->where('tt.school_id', $school_id)
            ->get()
            ->row();
    }

    public function get_matrix_for_class($year_id, $class_id, $section_id)
    {
        $entries = $this->get_entries([
            'academic_year_id' => $year_id,
            'class_id'         => $class_id,
            'division_id'       => $section_id
        ]);

        $matrix = [];
        foreach ($entries as $e) {
            $matrix[$e->day][$e->period_id] = $e;
        }
        return $matrix;
    }

    public function get_matrix_for_teacher($year_id, $teacher_id)
    {
        $entries = $this->get_entries([
            'academic_year_id' => $year_id,
            'teacher_id'       => $teacher_id
        ]);

        $matrix = [];
        foreach ($entries as $e) {
            $matrix[$e->day][$e->period_id] = $e;
            if (!empty($e->period_number)) {
                $matrix[$e->day]['num_' . $e->period_number] = $e;
            }
        }
        return $matrix;
    }

    public function get_dashboard_stats($year_id = 1)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $total_slots = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 1)->count_all_results('tbl_timetable');
        
        $scheduled_classes = (int)$this->db->query("SELECT COUNT(DISTINCT CONCAT(class_id, '-', division_id)) as cnt FROM tbl_timetable WHERE school_id = ? AND academic_year_id = ? AND status = 1", array($school_id, $year_id))->row()->cnt;
        
        $active_teachers = (int)$this->db->query("SELECT COUNT(DISTINCT teacher_id) as cnt FROM tbl_timetable WHERE school_id = ? AND academic_year_id = ? AND status = 1", array($school_id, $year_id))->row()->cnt;
        
        $published_classes = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Published')->count_all_results('tbl_timetable_publish');
        $locked_classes = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Locked')->count_all_results('tbl_timetable_publish');

        // Total faculty count (scoped to school)
        $total_faculty = (int)$this->db->where('school_id', $school_id)->where('staff_type', 'teacher')->where('status', 1)->count_all_results('tbl_staff');
        $utilization = ($total_faculty > 0) ? round(($active_teachers / $total_faculty) * 100, 1) : 0;

        // Conflicts count
        $conflicts = $this->detect_all_conflicts($year_id);

        return (object)[
            'total_slots'        => $total_slots,
            'scheduled_classes'  => $scheduled_classes,
            'active_teachers'    => $active_teachers,
            'total_faculty'      => $total_faculty,
            'utilization_rate'   => $utilization,
            'published_classes'  => $published_classes,
            'locked_classes'     => $locked_classes,
            'conflicts_count'    => count($conflicts)
        ];
    }

    public function check_conflicts($data, $exclude_id = NULL)
    {
        $errors = array();
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;

        // 1. Conflict Check: Class + Section + Day + Period
        $this->db
            ->select('tt.*, sub.subject_name, s.full_name as teacher_name')
            ->from('tbl_timetable tt')
            ->join('tbl_subjects sub', 'sub.subject_id = tt.subject_id', 'left')
            ->join('tbl_staff s', 's.staff_id = tt.teacher_id', 'left')
            ->where('tt.school_id', $school_id)
            ->where('tt.academic_year_id', $data['academic_year_id'])
            ->where('tt.class_id', $data['class_id'])
            ->where('tt.division_id', $data['division_id'])
            ->where('tt.day', $data['day'])
            ->where('tt.period_id', $data['period_id'])
            ->where('tt.status', 1);

        if ($exclude_id) {
            $this->db->where('tt.timetable_id !=', $exclude_id);
        }
        $classClash = $this->db->get()->row();
        if ($classClash) {
            $errors[] = "Class already has '{$classClash->subject_name}' ({$classClash->teacher_name}) assigned on {$data['day']} during this period.";
        }

        // 2. Conflict Check: Teacher collision (Same teacher in another class at the same period)
        if (!empty($data['teacher_id'])) {
            $this->db
                ->select('tt.*, c.class_name, d.division_name as division_name, d.division_name as section_name, sub.subject_name, s.full_name as teacher_name')
                ->from('tbl_timetable tt')
                ->join('tbl_classes c', 'c.class_id = tt.class_id', 'left')
                ->join('tbl_divisions d', 'd.division_id = tt.division_id', 'left')
                ->join('tbl_subjects sub', 'sub.subject_id = tt.subject_id', 'left')
                ->join('tbl_staff s', 's.staff_id = tt.teacher_id', 'left')
                ->where('tt.school_id', $school_id)
                ->where('tt.academic_year_id', $data['academic_year_id'])
                ->where('tt.teacher_id', $data['teacher_id'])
                ->where('tt.day', $data['day'])
                ->where('tt.period_id', $data['period_id'])
                ->where('tt.status', 1);

            if ($exclude_id) {
                $this->db->where('tt.timetable_id !=', $exclude_id);
            }
            $teacherClash = $this->db->get()->row();
            if ($teacherClash) {
                $errors[] = "Teacher '{$teacherClash->teacher_name}' is already teaching {$teacherClash->subject_name} in {$teacherClash->class_name} {$teacherClash->section_name} on {$data['day']} during this period.";
            }
        }

        return $errors;
    }

    public function detect_all_conflicts($year_id = 1)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        // Find all duplicate teacher assignments (Teacher in >1 class on same day+period)
        $teacher_clashes = $this->db->query("
            SELECT tt1.timetable_id as id1, tt2.timetable_id as id2,
                   tt1.day, p.period_name, s.full_name as teacher_name,
                   c1.class_name as class1, sec1.division_name as sec1, sub1.subject_name as sub1,
                   c2.class_name as class2, sec2.division_name as sec2, sub2.subject_name as sub2
            FROM tbl_timetable tt1
            JOIN tbl_timetable tt2 ON tt1.academic_year_id = tt2.academic_year_id
                 AND tt1.day = tt2.day
                 AND tt1.period_id = tt2.period_id
                 AND tt1.teacher_id = tt2.teacher_id
                 AND tt1.timetable_id < tt2.timetable_id
                 AND tt1.school_id = tt2.school_id
            JOIN tbl_staff s ON s.staff_id = tt1.teacher_id
            JOIN tbl_periods p ON p.period_id = tt1.period_id
            JOIN tbl_classes c1 ON c1.class_id = tt1.class_id
            JOIN tbl_divisions sec1 ON sec1.division_id = tt1.division_id
            JOIN tbl_subjects sub1 ON sub1.subject_id = tt1.subject_id
            JOIN tbl_classes c2 ON c2.class_id = tt2.class_id
            JOIN tbl_divisions sec2 ON sec2.division_id = tt2.division_id
            JOIN tbl_subjects sub2 ON sub2.subject_id = tt2.subject_id
            WHERE tt1.academic_year_id = ? AND tt1.school_id = ? AND tt1.status = 1 AND tt2.status = 1
        ", array($year_id, $school_id))->result();

        $conflicts = [];
        foreach ($teacher_clashes as $tc) {
            $conflicts[] = (object)[
                'type'        => 'Teacher Double-Booking',
                'severity'    => 'Critical',
                'description' => "{$tc->teacher_name} is simultaneously assigned to {$tc->class1} {$tc->sec1} ({$tc->sub1}) and {$tc->class2} {$tc->sec2} ({$tc->sub2}) on {$tc->day} - {$tc->period_name}.",
                'day'         => $tc->day,
                'period_name' => $tc->period_name,
                'teacher'     => $tc->teacher_name
            ];
        }

        return $conflicts;
    }

    public function is_schedule_locked($year_id, $class_id, $section_id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $pub = $this->db
            ->where('school_id', $school_id)
            ->where('academic_year_id', $year_id)
            ->where('class_id', $class_id)
            ->where('division_id', $section_id)
            ->get('tbl_timetable_publish')
            ->row();

        return ($pub && $pub->status === 'Locked');
    }

    public function save_entry($data, $id = NULL)
    {
        // Check lock status
        if ($this->is_schedule_locked($data['academic_year_id'], $data['class_id'], $data['division_id'])) {
            return array('success' => FALSE, 'message' => 'This class timetable is currently LOCKED against modifications. Please unlock it in Publish/Lock management first.');
        }

        // Validate teacher
        if (!empty($data['teacher_id'])) {
            $staff = $this->db->where('staff_id', $data['teacher_id'])->where('staff_type', 'teacher')->get('tbl_staff')->row();
            if (!$staff) {
                return array('success' => FALSE, 'message' => 'Only teaching faculty can be assigned to timetable periods.');
            }
        }

        $conflicts = $this->check_conflicts($data, $id);
        if (!empty($conflicts)) {
            return array('success' => FALSE, 'message' => implode(' ', $conflicts));
        }

        if ($id) {
            $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->update($this->table, $data);
            return array('success' => TRUE, 'id' => $id);
        } else {
            if (empty($data['school_id']) && function_exists('get_current_school_id')) {
                $data['school_id'] = (int)get_current_school_id();
            }
            $data['status'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return array('success' => TRUE, 'id' => $this->db->insert_id());
        }
    }

    public function delete_entry($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $entry = $this->get_by_id($id);
        if ($entry && $this->is_schedule_locked($entry->academic_year_id, $entry->class_id, $entry->division_id ?? $entry->section_id)) {
            return array('success' => FALSE, 'message' => 'Cannot delete slot: class timetable is LOCKED.');
        }
        $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->update($this->table, ['is_deleted' => 'y']);
        return array('success' => TRUE);
    }
}
