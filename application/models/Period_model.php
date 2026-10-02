<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Period_model extends CI_Model {

    protected $table = 'tbl_periods';
    protected $primaryKey = 'period_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    /**
     * Get all periods with optional active filter and type filter
     */
    public function get_all($active_only = TRUE, $type = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) {
            $this->db->where('status', 1);
        }
        if ($type !== NULL) {
            if (is_array($type)) {
                $this->db->where_in('period_type', $type);
            } else {
                $this->db->where('period_type', $type);
            }
        }
        return $this->db
            ->order_by('period_order', 'ASC')
            ->order_by('start_time', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get periods configured for a specific Academic Group
     */
    public function get_by_group($academic_group_id, $active_only = TRUE, $type = NULL, $year_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->from($this->table);
        $this->db->where('school_id', $school_id);
        $this->db->where('academic_group_id', (int)$academic_group_id);
        $this->db->where('is_deleted', 'n');
        if ($active_only) {
            $this->db->where('status', 1);
        }
        if ($type !== NULL) {
            if (is_array($type)) {
                $this->db->where_in('period_type', $type);
            } else {
                $this->db->where('period_type', $type);
            }
        }
        if ($year_id !== NULL) {
            $this->db->group_start()
                ->where('academic_year_id', (int)$year_id)
                ->or_where('academic_year_id IS NULL', NULL, FALSE)
                ->group_end();
        }
        return $this->db
            ->order_by('period_order', 'ASC')
            ->order_by('start_time', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get periods for a given Class by deriving its Academic Group
     */
    public function get_by_class($class_id, $active_only = TRUE, $type = NULL, $year_id = NULL)
    {
        $school_id = $this->get_school_id();
        $class = $this->db->select('academic_group_id')
            ->where('school_id', $school_id)
            ->where('class_id', (int)$class_id)
            ->get('tbl_classes')
            ->row();
        $group_id = ($class && !empty($class->academic_group_id)) ? (int)$class->academic_group_id : NULL;

        if ($group_id) {
            $periods = $this->get_by_group($group_id, $active_only, $type, $year_id);
            if (!empty($periods)) {
                return $periods;
            }
        }

        // Fallback to all periods of requested type or all periods
        return $this->get_all($active_only, $type);
    }

    /**
     * Get the unique set of teaching Periods for a Teacher Timetable view.
     */
    public function get_for_teacher($teacher_id, $year_id)
    {
        $school_id  = $this->get_school_id();
        $teacher_id = (int)$teacher_id;
        $year_id    = (int)$year_id;

        // 1. Identify all classes associated with this teacher
        $class_ids = [];

        // Check tbl_subject_teachers
        $st_rows = $this->db
            ->select('DISTINCT class_id', FALSE)
            ->from('tbl_subject_teachers')
            ->where('school_id', $school_id)
            ->where('staff_id', $teacher_id)
            ->where('academic_year_id', $year_id)
            ->where('status', 1)
            ->where('is_deleted', 'n')
            ->get()
            ->result();
        foreach ($st_rows as $r) {
            $class_ids[] = (int)$r->class_id;
        }

        // Check tbl_timetable entries
        $tt_classes = $this->db
            ->select('DISTINCT class_id', FALSE)
            ->from('tbl_timetable')
            ->where('school_id', $school_id)
            ->where('teacher_id', $teacher_id)
            ->where('academic_year_id', $year_id)
            ->where('status', 1)
            ->get()
            ->result();
        foreach ($tt_classes as $r) {
            $class_ids[] = (int)$r->class_id;
        }

        // Fallback to tbl_subject_allocations
        if (empty($class_ids)) {
            $sa_rows = $this->db
                ->select('DISTINCT class_id', FALSE)
                ->from('tbl_subject_allocations')
                ->where('school_id', $school_id)
                ->where('teacher_id', $teacher_id)
                ->where('academic_year_id', $year_id)
                ->where('is_deleted', 'n')
                ->get()
                ->result();
            foreach ($sa_rows as $r) {
                $class_ids[] = (int)$r->class_id;
            }
        }

        $class_ids = array_unique(array_filter($class_ids));

        // 2. Identify academic groups for those classes
        $group_ids = [];
        if (!empty($class_ids)) {
            $class_rows = $this->db
                ->select('DISTINCT academic_group_id', FALSE)
                ->where('school_id', $school_id)
                ->where_in('class_id', $class_ids)
                ->where('is_deleted', 'n')
                ->get('tbl_classes')
                ->result();
            foreach ($class_rows as $r) {
                if (!empty($r->academic_group_id)) {
                    $group_ids[] = (int)$r->academic_group_id;
                }
            }
        }

        // 3. Fetch active teaching periods
        $this->db->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->where('status', 1)
            ->where('period_type', 'Period');

        if (!empty($group_ids)) {
            $this->db->where_in('academic_group_id', $group_ids);
        }

        // Order by academic_group_id DESC (so higher groups provide standard timings), then period_number
        $all_periods = $this->db
            ->order_by('academic_group_id', 'DESC')
            ->order_by('period_number', 'ASC')
            ->get()
            ->result();

        // 4. Strict de-duplication by period_number: exactly 1 column per period number
        $seen = [];
        $unique = [];
        foreach ($all_periods as $p) {
            $num = (int)$p->period_number;
            if (!isset($seen[$num])) {
                $seen[$num] = true;
                $unique[] = $p;
            }
        }

        usort($unique, function($a, $b) {
            return (int)$a->period_number <=> (int)$b->period_number;
        });

        return $unique;
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        if (!isset($data['period_order']) && isset($data['period_number'])) {
            $data['period_order'] = $data['period_number'];
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = $this->get_school_id();
        if (isset($data['period_number']) && !isset($data['period_order'])) {
            $data['period_order'] = $data['period_number'];
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function toggle_status($id)
    {
        $period = $this->get_by_id($id);
        if (!$period) return FALSE;
        $new_status = ($period->status == 1) ? 0 : 1;
        return $this->update($id, array('status' => $new_status));
    }

    public function check_number_exists($period_number, $exclude_id = NULL, $group_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('period_number', $period_number);
        $this->db->where('is_deleted', 'n');
        if ($group_id !== NULL) {
            $this->db->where('academic_group_id', (int)$group_id);
        }
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function check_overlap($start_time, $end_time, $exclude_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('status', 1);
        $this->db->where('is_deleted', 'n');
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        $this->db->where("start_time <", $end_time);
        $this->db->where("end_time >", $start_time);
        return $this->db->get($this->table)->row();
    }

    public function check_group_overlap($academic_group_id, $start_time, $end_time, $exclude_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('academic_group_id', (int)$academic_group_id);
        $this->db->where('status', 1);
        $this->db->where('is_deleted', 'n');
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        $this->db->where("start_time <", $end_time);
        $this->db->where("end_time >", $start_time);
        return $this->db->get($this->table)->row();
    }

    /**
     * Save an entire period schedule for an Academic Group dynamically
     */
    public function save_group_periods($academic_group_id, array $slots, $year_id = NULL)
    {
        $school_id = $this->get_school_id();
        $academic_group_id = (int)$academic_group_id;
        if ($academic_group_id <= 0) {
            return ['success' => false, 'message' => 'Invalid Academic Group selected.'];
        }

        if (empty($slots)) {
            return ['success' => false, 'message' => 'At least one period or break must be defined.'];
        }

        // 1. Sort slots by start_time to normalize timeline order
        usort($slots, function ($a, $b) {
            $stA = strtotime($a['start_time'] ?? '00:00');
            $stB = strtotime($b['start_time'] ?? '00:00');
            if ($stA === $stB) return 0;
            return ($stA < $stB) ? -1 : 1;
        });

        // 2. Validate all slots
        $allowed_types = ['Period', 'Break', 'Lunch Break'];
        $teaching_period_count = 0;
        $intervals = [];

        foreach ($slots as $idx => $slot) {
            $name = trim($slot['name'] ?? '');
            $type = trim($slot['type'] ?? 'Period');
            $start = trim($slot['start_time'] ?? '');
            $end = trim($slot['end_time'] ?? '');

            if (empty($name)) {
                return ['success' => false, 'message' => "Slot #" . ($idx + 1) . " name cannot be empty."];
            }

            if (!in_array($type, $allowed_types, true)) {
                return ['success' => false, 'message' => "Invalid slot type '{$type}' for slot '{$name}'."];
            }

            if (empty($start) || empty($end)) {
                return ['success' => false, 'message' => "Start and End times are required for '{$name}'."];
            }

            $start_ts = strtotime($start);
            $end_ts = strtotime($end);

            if ($start_ts === false || $end_ts === false) {
                return ['success' => false, 'message' => "Invalid time format for '{$name}'."];
            }

            if ($end_ts <= $start_ts) {
                return ['success' => false, 'message' => "End Time must be after Start Time for '{$name}' ({$start} - {$end})."];
            }

            // Check overlap against previous intervals in this submission
            foreach ($intervals as $prev) {
                if ($start_ts < $prev['end_ts'] && $end_ts > $prev['start_ts']) {
                    return [
                        'success' => false,
                        'message' => "Time overlap detected between '{$name}' ({$start} - {$end}) and '{$prev['name']}' ({$prev['start']} - {$prev['end']})."
                    ];
                }
            }

            if ($type === 'Period') {
                $teaching_period_count++;
            }

            $intervals[] = [
                'name'     => $name,
                'start'    => $start,
                'end'      => $end,
                'start_ts' => $start_ts,
                'end_ts'   => $end_ts
            ];
        }

        if ($teaching_period_count === 0) {
            return ['success' => false, 'message' => 'You must include at least one teaching Period in the schedule.'];
        }

        // 3. Database transaction
        $this->db->trans_start();

        // Get existing active periods for this group in this school
        $existing = $this->db
            ->where('school_id', $school_id)
            ->where('academic_group_id', $academic_group_id)
            ->where('is_deleted', 'n')
            ->get($this->table)
            ->result();

        $existing_by_id = [];
        foreach ($existing as $ex) {
            $existing_by_id[(int)$ex->period_id] = $ex;
        }

        $processed_ids = [];
        $seq_period_number = 1;
        $order = 1;

        foreach ($slots as $slot) {
            $period_id = !empty($slot['period_id']) ? (int)$slot['period_id'] : 0;
            $type = $slot['type'];
            $name = trim($slot['name']);
            $start = date('H:i:s', strtotime($slot['start_time']));
            $end = date('H:i:s', strtotime($slot['end_time']));

            $period_number = ($type === 'Period') ? $seq_period_number++ : 0;

            $record_data = [
                'school_id'         => $school_id,
                'academic_group_id' => $academic_group_id,
                'academic_year_id'  => $year_id ?: NULL,
                'period_number'     => $period_number,
                'period_name'       => $name,
                'period_type'       => $type,
                'start_time'        => $start,
                'end_time'          => $end,
                'period_order'      => $order++,
                'status'            => 1,
                'is_deleted'        => 'n',
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            if ($period_id > 0 && isset($existing_by_id[$period_id])) {
                // Update existing period
                $this->db
                    ->where($this->primaryKey, $period_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, $record_data);
                $processed_ids[] = $period_id;
            } else {
                // Insert new period
                $record_data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert($this->table, $record_data);
                $processed_ids[] = $this->db->insert_id();
            }
        }

        // Soft-delete any existing periods for this group that were removed by the user
        foreach ($existing_by_id as $ex_id => $ex_row) {
            if (!in_array($ex_id, $processed_ids, true)) {
                $this->db
                    ->where($this->primaryKey, $ex_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, [
                        'is_deleted' => 'y',
                        'status'     => 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Database transaction failed while saving periods.'];
        }

        return ['success' => true, 'message' => 'Period setup saved successfully!'];
    }

    public function is_safe_to_delete($id)
    {
        $school_id = $this->get_school_id();
        $tt_count = $this->db
            ->where('school_id', $school_id)
            ->where('period_id', (int)$id)
            ->count_all_results('tbl_timetable');
        $att_count = $this->db
            ->where('school_id', $school_id)
            ->where('period_id', (int)$id)
            ->count_all_results('tbl_attendance');
        return ($tt_count === 0 && $att_count === 0);
    }

    public function delete($id)
    {
        $school_id = $this->get_school_id();
        if ($this->is_safe_to_delete($id)) {
            return $this->db
                ->where($this->primaryKey, (int)$id)
                ->where('school_id', $school_id)
                ->update($this->table, ['is_deleted' => 'y']);
        }
        return $this->update($id, array('status' => 0));
    }
}
