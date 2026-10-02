<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_type_model extends CI_Model {

    protected $table = 'tbl_exam_types';
    protected $primaryKey = 'exam_type_id';

    /**
     * Standard selected columns for Exam Types (No SELECT *).
     */
    protected $selectFields = 'exam_type_id, type_name, description, status, school_id, created_at, updated_at';

    /**
     * Resolve the current tenant school ID.
     */
    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    /**
     * Retrieve exam types for a school with optional active-only filter.
     * Automatically initializes default exam types from templates if the school has 0 exam types.
     *
     * @param int|null  $school_id
     * @param bool      $active_only
     * @return array
     */
    public function get_exam_types($school_id = null, $active_only = true)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        if ($school_id <= 0) {
            return [];
        }

        // Check if any non-deleted exam types exist for this school
        $existing_count = $this->db
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->count_all_results($this->table);

        // Self-heal / initialize if the school has 0 configured exam types
        if ($existing_count === 0) {
            $this->initialize_school_exam_types($school_id);
        }

        $this->db
            ->select($this->selectFields)
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n');

        if ($active_only) {
            $this->db->where('status', 1);
        }

        return $this->db->order_by('type_name', 'ASC')->get()->result();
    }

    /**
     * Backwards-compatible get_all method.
     *
     * @param bool      $active_only
     * @param int|null  $school_id
     * @return array
     */
    public function get_all($active_only = FALSE, $school_id = null)
    {
        return $this->get_exam_types($school_id, $active_only);
    }

    /**
     * Dynamically initialize default exam types for a school from existing database templates.
     *
     * @param int $school_id
     * @return array Array of seeded exam type objects
     */
    public function initialize_school_exam_types($school_id)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) {
            return [];
        }

        // Prevent duplicate seeding if records already exist
        $current = $this->db
            ->select($this->selectFields)
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get()
            ->result();

        if (!empty($current)) {
            return $current;
        }

        // 1. Fetch distinct template exam types across active schools in the system
        $templates = $this->db
            ->select('type_name, description, status')
            ->from($this->table)
            ->where('is_deleted', 'n')
            ->where('status', 1)
            ->group_by('type_name')
            ->order_by('exam_type_id', 'ASC')
            ->get()
            ->result();

        // 3. System fallback defaults if database table is completely empty
        if (empty($templates)) {
            $templates = [
                (object)['type_name' => 'Unit Test', 'description' => 'Periodic unit test evaluations', 'status' => 1],
                (object)['type_name' => 'Class Test', 'description' => 'Classroom monthly assessment test', 'status' => 1],
                (object)['type_name' => 'First Term Examination', 'description' => 'First term comprehensive exam', 'status' => 1],
                (object)['type_name' => 'Mid Term Examination', 'description' => 'Half-yearly / mid-term examination', 'status' => 1],
                (object)['type_name' => 'Second Term Examination', 'description' => 'Second term evaluations', 'status' => 1],
                (object)['type_name' => 'Annual Examination', 'description' => 'Final annual academic examination', 'status' => 1],
                (object)['type_name' => 'Model Examination', 'description' => 'Pre-board / model preparation exam', 'status' => 1],
                (object)['type_name' => 'Practical Examination', 'description' => 'Laboratory and practical assessment', 'status' => 1],
            ];
        }

        $now = date('Y-m-d H:i:s');
        foreach ($templates as $tmpl) {
            $name = trim($tmpl->type_name);
            if (empty($name)) {
                continue;
            }

            // Check if this type_name already exists for this school
            $exists = $this->db
                ->select('exam_type_id')
                ->from($this->table)
                ->where('school_id', $school_id)
                ->where('type_name', $name)
                ->where('is_deleted', 'n')
                ->get()
                ->row();

            if (!$exists) {
                $this->db->insert($this->table, [
                    'school_id'   => $school_id,
                    'type_name'   => $name,
                    'description' => !empty($tmpl->description) ? $tmpl->description : null,
                    'status'      => isset($tmpl->status) ? (int)$tmpl->status : 1,
                    'is_deleted'  => 'n',
                    'created_at'  => $now,
                    'updated_at'  => $now
                ]);
            }
        }

        return $this->db
            ->select($this->selectFields)
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->order_by('type_name', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_id($id, $school_id = null)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        return $this->db
            ->select($this->selectFields)
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
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data, $school_id = null)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db
            ->where($this->primaryKey, (int)$id)
            ->where('school_id', $school_id)
            ->update($this->table, $data);
    }

    public function toggle_status($id, $school_id = null)
    {
        $type = $this->get_by_id($id, $school_id);
        if (!$type) return FALSE;
        $new_status = ($type->status == 1) ? 0 : 1;
        return $this->update($id, array('status' => $new_status), $school_id);
    }

    public function is_name_exists($name, $exclude_id = NULL, $school_id = null)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $this->db->where('school_id', $school_id);
        $this->db->where('is_deleted', 'n');
        $this->db->where('type_name', $name);
        if ($exclude_id) {
            $this->db->where($this->primaryKey . ' !=', (int)$exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function is_safe_to_delete($id, $school_id = null)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $exam_count = $this->db
            ->where('school_id', $school_id)
            ->where('exam_type_id', (int)$id)
            ->count_all_results('tbl_exams');
        return ($exam_count === 0);
    }

    public function delete($id, $school_id = null)
    {
        $school_id = $school_id !== null ? (int)$school_id : $this->get_school_id();
        if ($this->is_safe_to_delete($id, $school_id)) {
            return $this->db
                ->where($this->primaryKey, (int)$id)
                ->where('school_id', $school_id)
                ->update($this->table, ['is_deleted' => 'y', 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return $this->update($id, array('status' => 0), $school_id);
    }
}
