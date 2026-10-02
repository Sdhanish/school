<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Homework_model extends CI_Model {

    protected $table = 'tbl_assignments';
    protected $primaryKey = 'assignment_id';

    public function get_all($filters = array(), $limit = NULL, $offset = 0)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db
            ->select('a.*, t.type_name, c.class_name, div.division_name as division_name, div.division_name as section_name, sub.subject_name, sub.subject_code, s.full_name as teacher_name, y.year_name')
            ->from('tbl_assignments a')
            ->join('tbl_assignment_types t', 't.type_id = a.assignment_type_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = a.subject_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.teacher_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = a.academic_year_id', 'left')
            ->where('a.is_deleted', 'n')
            ->where('a.school_id', $school_id)
            ->order_by('a.assigned_date', 'DESC')
            ->order_by('a.assignment_id', 'DESC');

        if (!empty($filters['academic_year_id'])) $this->db->where('a.academic_year_id', $filters['academic_year_id']);
        if (!empty($filters['class_id'])) $this->db->where('a.class_id', $filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('a.division_id', $filters['division_id']);
        if (!empty($filters['subject_id'])) $this->db->where('a.subject_id', $filters['subject_id']);
        if (!empty($filters['teacher_id'])) $this->db->where('a.teacher_id', $filters['teacher_id']);
        if (!empty($filters['assignment_type_id'])) $this->db->where('a.assignment_type_id', $filters['assignment_type_id']);
        if (!empty($filters['status'])) $this->db->where('a.status', $filters['status']);
        if (!empty($filters['due_date'])) $this->db->where('a.due_date', $filters['due_date']);
        if (!empty($filters['date_from'])) $this->db->where('a.assigned_date >=', $filters['date_from']);
        if (!empty($filters['date_to'])) $this->db->where('a.assigned_date <=', $filters['date_to']);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('a.title', $s)
                ->or_like('a.description', $s)
                ->or_like('sub.subject_name', $s)
                ->or_like('s.full_name', $s)
            ->group_end();
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $assignments = $this->db->get()->result();

        // Attach submission counts to each assignment
        foreach ($assignments as &$asgn) {
            $asgn->submission_stats = $this->get_submission_summary($asgn->assignment_id, $asgn->class_id, $asgn->division_id);
        }

        return $assignments;
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $asgn = $this->db
            ->select('a.*, t.type_name, c.class_name, div.division_name as division_name, div.division_name as section_name, sub.subject_name, sub.subject_code, s.full_name as teacher_name, s.employee_code, y.year_name')
            ->from('tbl_assignments a')
            ->join('tbl_assignment_types t', 't.type_id = a.assignment_type_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_subjects sub', 'sub.subject_id = a.subject_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.teacher_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = a.academic_year_id', 'left')
            ->where('a.assignment_id', $id)
            ->where('a.school_id', $school_id)
            ->get()
            ->row();

        if ($asgn) {
            $asgn->submission_stats = $this->get_submission_summary($asgn->assignment_id, $asgn->class_id, $asgn->division_id);
        }
        return $asgn;
    }

    public function get_submission_summary($assignment_id, $class_id, $division_id = NULL)
    {
        $this->db
            ->where('class_id', $class_id)
            ->where('status', 1);

        if (!empty($division_id)) {
            $this->db->where('division_id', $division_id);
        }

        $total_students = (int)$this->db->count_all_results('tbl_students');

        $submitted = (int)$this->db
            ->where('assignment_id', $assignment_id)
            ->where_in('status', ['Submitted', 'Late', 'Reviewed', 'Returned'])
            ->count_all_results('tbl_assignment_submissions');

        $late = (int)$this->db
            ->where('assignment_id', $assignment_id)
            ->where('is_late', 1)
            ->count_all_results('tbl_assignment_submissions');

        $reviewed = (int)$this->db
            ->where('assignment_id', $assignment_id)
            ->where('status', 'Reviewed')
            ->count_all_results('tbl_assignment_submissions');

        $pending = max(0, $total_students - $submitted);
        $completion_pct = ($total_students > 0) ? round(($submitted / $total_students) * 100, 1) : 0;

        return (object)[
            'total_students' => $total_students,
            'submitted'      => $submitted,
            'late'           => $late,
            'reviewed'       => $reviewed,
            'pending'        => $pending,
            'completion_pct' => $completion_pct
        ];
    }

    public function get_dashboard_stats($year_id = NULL)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();
        $total = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status !=', 'Archived')->count_all_results('tbl_assignments');
        $active = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Published')->where('due_date >=', date('Y-m-d'))->count_all_results('tbl_assignments');
        
        $submitted = (int)$this->db->query("
            SELECT COUNT(*) as cnt FROM tbl_assignment_submissions sub
            JOIN tbl_assignments a ON a.assignment_id = sub.assignment_id
            WHERE a.school_id = ? AND a.academic_year_id = ? AND sub.status IN ('Submitted', 'Late', 'Reviewed', 'Returned')
        ", array($school_id, $year_id))->row()->cnt;

        $reviewed = (int)$this->db->query("
            SELECT COUNT(*) as cnt FROM tbl_assignment_submissions sub
            JOIN tbl_assignments a ON a.assignment_id = sub.assignment_id
            WHERE a.school_id = ? AND a.academic_year_id = ? AND sub.status = 'Reviewed'
        ", array($school_id, $year_id))->row()->cnt;

        $overdue = (int)$this->db->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Published')->where('due_date <', date('Y-m-d'))->count_all_results('tbl_assignments');

        // Total target submissions
        $total_expected = 0;
        $active_asgns = $this->db->select('assignment_id, class_id, division_id')->where('school_id', $school_id)->where('academic_year_id', $year_id)->where('status', 'Published')->get('tbl_assignments')->result();
        foreach ($active_asgns as $as) {
            $this->db->where('school_id', $school_id)->where('class_id', $as->class_id)->where('status', 1)->where('academic_year_id', $year_id);
            if (!empty($as->division_id)) {
                $this->db->where('division_id', $as->division_id);
            }
            $total_expected += (int)$this->db->count_all_results('tbl_students');
        }
        $pending = max(0, $total_expected - $submitted);
        $completion_pct = ($total_expected > 0) ? round(($submitted / $total_expected) * 100, 1) : 0;

        return (object)[
            'total_assignments' => $total,
            'active_assignments'=> $active,
            'submitted'         => $submitted,
            'reviewed'          => $reviewed,
            'pending'           => $pending,
            'overdue'           => $overdue,
            'completion_pct'    => $completion_pct
        ];
    }

    public function get_upcoming_deadlines($year_id = NULL, $limit = 5)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();
        return $this->db
            ->select('a.*, sub.subject_name, c.class_name, div.division_name as division_name, div.division_name as section_name, s.full_name as teacher_name')
            ->from('tbl_assignments a')
            ->join('tbl_subjects sub', 'sub.subject_id = a.subject_id', 'left')
            ->join('tbl_classes c', 'c.class_id = a.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = a.division_id', 'left')
            ->join('tbl_staff s', 's.staff_id = a.teacher_id', 'left')
            ->where('a.school_id', $school_id)
            ->where('a.academic_year_id', $year_id)
            ->where('a.status', 'Published')
            ->where('a.due_date >=', date('Y-m-d'))
            ->order_by('a.due_date', 'ASC')
            ->order_by('a.due_time', 'ASC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        $this->log_audit('ASSIGNMENT_CREATED', $id, 'Created new assignment: ' . ($data['title'] ?? ''));
        return $id;
    }

    public function update($id, $data)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->update($this->table, $data);
        $this->log_audit('ASSIGNMENT_UPDATED', $id, 'Updated assignment details');
        return TRUE;
    }

    public function duplicate($id)
    {
        $orig = $this->db->where($this->primaryKey, $id)->get($this->table)->row_array();
        if (!$orig) return FALSE;

        unset($orig['assignment_id']);
        $orig['title'] = $orig['title'] . ' (Copy)';
        $orig['status'] = 'Draft';
        $orig['assigned_date'] = date('Y-m-d');
        $orig['due_date'] = date('Y-m-d', strtotime('+3 days'));
        $orig['created_at'] = date('Y-m-d H:i:s');
        $orig['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $orig);
        $new_id = $this->db->insert_id();
        $this->log_audit('ASSIGNMENT_DUPLICATED', $new_id, 'Duplicated from Assignment #' . $id);
        return $new_id;
    }

    public function can_permanently_delete($id)
    {
        $submissions_cnt = $this->db->where('assignment_id', $id)->count_all_results('tbl_assignment_submissions');
        return ($submissions_cnt === 0);
    }

    public function delete($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->update($this->table, ['is_deleted' => 'y', 'status' => 'Archived']);
        $this->log_audit('ASSIGNMENT_DELETED', $id, 'Soft deleted assignment #' . $id);
        return TRUE;
    }

    public function log_audit($action, $entity_id, $details = '')
    {
        $user_id = $this->session->userdata('user_id') ?: 1;
        $this->db->insert('tbl_homework_audit_logs', [
            'user_id'     => $user_id,
            'action'      => $action,
            'entity_type' => 'tbl_assignments',
            'entity_id'   => $entity_id,
            'details'     => $details,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}
