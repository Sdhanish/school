<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Student_academic_model
 *
 * Handles CRUD for:
 *   - tbl_student_previous_school   (previous school + TC + prior performance)
 *   - tbl_student_activities        (academic achievements + extracurricular)
 *
 * Loaded by Students controller during wizard_save.
 * All writes are wrapped inside the controller's transaction.
 */
class Student_academic_model extends CI_Model
{
    /* =========================================================================
       Previous School
    ========================================================================= */

    /**
     * Insert a previous school record.
     *
     * @param  array $data  Columns matching tbl_student_previous_school
     * @return int          Inserted ID
     */
    public function insert_previous_school(array $data, $school_id = NULL)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $school_id ? (int)$school_id : get_current_school_id();
        }
        $this->db->insert('tbl_student_previous_school', $data);
        return (int)$this->db->insert_id();
    }

    /**
     * Get previous school record for a student.
     *
     * @param  int $student_id
     * @param  int|null $school_id
     * @return object|null
     */
    public function get_previous_school($student_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('student_id', (int)$student_id)
            ->where('school_id', $school_id)
            ->where('status', 1)
            ->order_by('prev_school_id', 'DESC')
            ->get('tbl_student_previous_school')
            ->row();
    }

    /* =========================================================================
       Activities (Academic & Extracurricular)
    ========================================================================= */

    /**
     * Insert a single activity record.
     *
     * @param  array $data  Columns matching tbl_student_activities
     * @param  int|null $school_id
     * @return int          Inserted ID
     */
    public function insert_activity(array $data, $school_id = NULL)
    {
        if (empty($data['school_id'])) {
            $data['school_id'] = $school_id ? (int)$school_id : get_current_school_id();
        }
        $this->db->insert('tbl_student_activities', $data);
        return (int)$this->db->insert_id();
    }

    /**
     * Bulk-insert multiple activity records.
     * Uses batch insert for efficiency.
     *
     * @param  array $rows  Array of data arrays
     * @param  int|null $school_id
     * @return bool
     */
    public function insert_activities_batch(array $rows, $school_id = NULL)
    {
        if (empty($rows)) {
            return TRUE;
        }

        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        foreach ($rows as $row) {
            if (is_array($row) && !empty($row['activity_name'])) {
                $clean_row = array();
                foreach ($row as $k => $v) {
                    $clean_row[$k] = is_array($v) ? json_encode($v) : $v;
                }
                if (empty($clean_row['school_id'])) {
                    $clean_row['school_id'] = $school_id;
                }
                $this->db->insert('tbl_student_activities', $clean_row);
            }
        }
        return TRUE;
    }

    /**
     * Get all activities for a student, optionally filtered by category.
     *
     * @param  int         $student_id
     * @param  string|null $category  'Academic' | 'Extracurricular' | null (all)
     * @param  int|null    $school_id
     * @return array
     */
    public function get_activities($student_id, $category = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db->where('student_id', (int)$student_id)
                 ->where('school_id', $school_id)
                 ->where('status', 1);

        if ($category !== NULL) {
            $this->db->where('category', $category);
        }

        return $this->db
            ->order_by('category', 'ASC')
            ->order_by('activity_type', 'ASC')
            ->order_by('year', 'DESC')
            ->get('tbl_student_activities')
            ->result();
    }

    /**
     * Save (insert or update) a previous school record for a student.
     *
     * @param  int   $student_id
     * @param  array $data
     * @param  int|null $school_id
     * @return int   Record ID
     */
    public function save_previous_school($student_id, array $data, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $data['school_id'] = $school_id;
        $existing = $this->get_previous_school($student_id, $school_id);
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('prev_school_id', (int)$existing->prev_school_id)
                     ->where('school_id', $school_id)
                     ->update('tbl_student_previous_school', $data);
            return (int)$existing->prev_school_id;
        } else {
            $data['student_id'] = (int)$student_id;
            $data['status']     = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->insert_previous_school($data, $school_id);
        }
    }

    /**
     * Sync activities for a student (Academic or Extracurricular).
     *
     * @param  int         $student_id
     * @param  array       $activities
     * @param  string|null $category
     * @param  int|null    $school_id
     * @return bool
     */
    public function save_activities($student_id, array $activities, $category = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();

        $this->db->where('student_id', (int)$student_id)
                 ->where('school_id', $school_id);

        if ($category !== NULL) {
            $this->db->where('category', $category);
        }
        $this->db->delete('tbl_student_activities');

        if (!empty($activities)) {
            foreach ($activities as $act) {
                if (is_array($act) && !empty($act['activity_name'])) {
                    $cat = !empty($act['category']) ? $act['category'] : ($category ?: 'Academic');
                    $row = array(
                        'school_id'       => $school_id,
                        'student_id'      => (int)$student_id,
                        'category'        => $cat,
                        'activity_type'   => !empty($act['activity_type']) ? $act['activity_type'] : ($cat === 'Academic' ? 'Achievement' : 'Sports'),
                        'activity_name'   => trim($act['activity_name']),
                        'level'           => !empty($act['level']) ? $act['level'] : NULL,
                        'position_result' => !empty($act['position_result']) ? $act['position_result'] : NULL,
                        'year'            => !empty($act['year']) && is_numeric($act['year']) ? (int)$act['year'] : (int)date('Y'),
                        'description'     => !empty($act['description']) ? $act['description'] : NULL,
                        'status'          => 1,
                        'created_at'      => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('tbl_student_activities', $row);
                }
            }
        }
        return TRUE;
    }

    /**
     * Get TC document for a student.
     *
     * @param  int      $student_id
     * @param  int|null $document_id
     * @param  int|null $school_id
     * @return object|null
     */
    public function get_tc_document($student_id, $document_id = NULL, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $this->db->where('student_id', (int)$student_id)
                 ->where('school_id', $school_id);
        if ($document_id) {
            $this->db->where('document_id', (int)$document_id);
        } else {
            $this->db->where('document_type', 'Transfer Certificate');
        }
        return $this->db->where('status', 1)
                        ->order_by('document_id', 'DESC')
                        ->get('tbl_student_documents')
                        ->row();
    }

    /**
     * Update an existing TC document in tbl_student_documents.
     *
     * @param  int    $document_id
     * @param  string $file_path
     * @param  string $tc_number
     * @param  int|null $school_id
     * @return int    document_id
     */
    public function update_tc_document($document_id, $file_path, $tc_number = '', $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $data = array(
            'document_name' => 'TC - ' . ($tc_number ?: 'Transfer Certificate'),
            'file_path'     => $file_path,
            'updated_at'    => date('Y-m-d H:i:s'),
        );
        $this->db->where('document_id', (int)$document_id)
                 ->where('school_id', $school_id)
                 ->update('tbl_student_documents', $data);
        return (int)$document_id;
    }

    /* =========================================================================
       TC Document (via tbl_student_documents — reused existing table)
    ========================================================================= */

    /**
     * Insert a TC document into tbl_student_documents.
     * Returns the document_id so it can be stored in tbl_student_previous_school.
     *
     * @param  int    $student_id
     * @param  string $file_path    Relative path e.g. 'uploads/documents/tc_xxx.pdf'
     * @param  string $tc_number    Original TC number for naming
     * @param  int|null $school_id
     * @return int                  document_id
     */
    public function insert_tc_document($student_id, $file_path, $tc_number = '', $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        $data = array(
            'school_id'     => $school_id,
            'student_id'    => (int)$student_id,
            'document_type' => 'Transfer Certificate',
            'document_name' => 'TC - ' . ($tc_number ?: 'Transfer Certificate'),
            'file_path'     => $file_path,
            'status'        => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        );
        $this->db->insert('tbl_student_documents', $data);
        return (int)$this->db->insert_id();
    }

    /**
     * Deactivate previous school record for a student with school scoping.
     *
     * @param  int      $prev_school_id
     * @param  int|null $school_id
     * @return bool
     */
    public function deactivate_previous_school($prev_school_id, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('prev_school_id', (int)$prev_school_id)
            ->where('school_id', $school_id)
            ->update('tbl_student_previous_school', array(
                'status'     => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    /**
     * Update TC number on student document with school scoping.
     *
     * @param  int      $document_id
     * @param  string   $tc_number
     * @param  int|null $school_id
     * @return bool
     */
    public function update_tc_number($document_id, $tc_number, $school_id = NULL)
    {
        $school_id = $school_id ? (int)$school_id : get_current_school_id();
        return $this->db
            ->where('document_id', (int)$document_id)
            ->where('school_id', $school_id)
            ->update('tbl_student_documents', array(
                'document_name'   => 'TC - ' . $tc_number,
                'document_number' => $tc_number,
                'updated_at'      => date('Y-m-d H:i:s'),
            ));
    }
}
