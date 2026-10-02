<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Model
 *
 * Base model providing reusable school data isolation helpers for CodeIgniter 3.
 */
class MY_Model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the active school ID from the centralized context.
     *
     * @return int
     */
    public function get_school_id()
    {
        return get_current_school_id();
    }

    /**
     * Apply WHERE school_id condition to query builder.
     *
     * @param  string      $alias     Optional table alias prefix (e.g. 'st.')
     * @param  int|null    $school_id Override school ID or NULL for active context
     * @return $this
     */
    public function scoped_where($alias = '', $school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $col = !empty($alias) ? rtrim($alias, '.') . '.school_id' : 'school_id';
        $this->db->where($col, $sid);
        return $this;
    }

    /**
     * Verify that a record belongs to the active (or given) school.
     *
     * @param  string   $table
     * @param  string   $pk_col
     * @param  mixed    $pk_val
     * @param  int|null $school_id
     * @return bool
     */
    public function verify_ownership($table, $pk_col, $pk_val, $school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $row = $this->db
            ->select($pk_col)
            ->where($pk_col, $pk_val)
            ->where('school_id', $sid)
            ->where('is_deleted', 'n')
            ->get($table)
            ->row();

        return !empty($row);
    }

    /**
     * Safely insert record ensuring school_id is always set.
     *
     * @param  string $table
     * @param  array  $data
     * @return int|bool Insert ID or false
     */
    public function insert_scoped($table, array $data)
    {
        if (!isset($data['school_id']) || empty($data['school_id'])) {
            $data['school_id'] = $this->get_school_id();
        }
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    /**
     * Safely update record ensuring WHERE school_id is strictly applied.
     *
     * @param  string   $table
     * @param  string   $pk_col
     * @param  mixed    $pk_val
     * @param  array    $data
     * @param  int|null $school_id
     * @return bool
     */
    public function update_scoped($table, $pk_col, $pk_val, array $data, $school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $this->db
            ->where($pk_col, $pk_val)
            ->where('school_id', $sid)
            ->update($table, $data);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Safely soft-delete record ensuring WHERE school_id is strictly applied.
     *
     * @param  string   $table
     * @param  string   $pk_col
     * @param  mixed    $pk_val
     * @param  int|null $school_id
     * @return bool
     */
    public function delete_scoped($table, $pk_col, $pk_val, $school_id = null)
    {
        $sid = $school_id !== null ? (int)$school_id : $this->get_school_id();
        $this->db
            ->where($pk_col, $pk_val)
            ->where('school_id', $sid)
            ->update($table, [
                'is_deleted' => 'y',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->db->affected_rows() > 0;
    }
}
