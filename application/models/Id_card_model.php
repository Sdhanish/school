<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Id_card_model — Handles ID Card settings, generation tracking, and dynamic student data.
 */
class Id_card_model extends CI_Model {

    protected $settings_table = 'tbl_id_card_settings';
    protected $history_table  = 'tbl_student_id_cards';

    public function __construct()
    {
        parent::__construct();
        $this->_ensure_tables_exist();
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    /**
     * Ensure dedicated ID card tables exist without requiring manual DB migrations.
     */
    private function _ensure_tables_exist()
    {
        // 1. Settings Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_id_card_settings` (
            `setting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `card_title` VARCHAR(100) NOT NULL DEFAULT 'STUDENT IDENTITY CARD',
            `school_name` VARCHAR(255) NULL,
            `school_code` VARCHAR(50) NULL,
            `school_address` TEXT NULL,
            `phone` VARCHAR(50) NULL,
            `email` VARCHAR(100) NULL,
            `website` VARCHAR(100) NULL,
            `emergency_contact` VARCHAR(50) NULL,
            `principal_name` VARCHAR(100) NULL,
            `principal_signature` VARCHAR(255) NULL,
            `school_logo` VARCHAR(255) NULL,
            `return_text` TEXT NULL,
            `validity_text` VARCHAR(100) NULL,
            `accent_color` VARCHAR(20) NOT NULL DEFAULT '#091426',
            `secondary_color` VARCHAR(20) NOT NULL DEFAULT '#006c4a',
            `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_id`),
            KEY `idx_idcard_settings_school` (`school_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. History Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_student_id_cards` (
            `id_card_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `card_number` VARCHAR(50) NOT NULL,
            `student_id` INT UNSIGNED NOT NULL,
            `academic_year_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `class_id` INT UNSIGNED NULL,
            `division_id` INT UNSIGNED NULL,
            `card_version` INT UNSIGNED NOT NULL DEFAULT 1,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Generated',
            `generated_by` INT UNSIGNED NULL,
            `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_printed_at` DATETIME NULL,
            `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_card_id`),
            KEY `idx_idcard_school` (`school_id`),
            KEY `idx_idcard_student` (`student_id`),
            KEY `idx_idcard_year` (`academic_year_id`),
            KEY `idx_idcard_card_no` (`card_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Get ID Card settings, falling back to tbl_school_settings if fields are not explicitly set.
     *
     * @return object
     */
    public function get_settings($school_id = NULL)
    {
        $sid = ($school_id !== NULL && (int)$school_id > 0) ? (int)$school_id : $this->get_school_id();
        $id_settings = $this->db->where('school_id', $sid)->limit(1)->get($this->settings_table)->row();

        // Fetch general school settings for this school
        $school_settings = $this->db->where('school_id', $sid)->limit(1)->get('tbl_school_settings')->row();
        $school_info     = $this->db->where('id', $sid)->limit(1)->get('tbl_schools')->row();

        $school_name = $school_info ? $school_info->school_name : ($school_settings->school_name ?? 'School');
        $school_code = $school_info ? $school_info->school_code : ($school_settings->school_code ?? 'SCH');

        $default = (object)[
            'setting_id'          => $id_settings ? $id_settings->setting_id : 0,
            'school_id'           => $sid,
            'card_title'          => !empty($id_settings->card_title) ? $id_settings->card_title : 'STUDENT IDENTITY CARD',
            'school_name'         => !empty($id_settings->school_name) ? $id_settings->school_name : $school_name,
            'school_code'         => !empty($id_settings->school_code) ? $id_settings->school_code : $school_code,
            'school_address'      => !empty($id_settings->school_address) ? $id_settings->school_address : ($school_settings->address ?? ''),
            'phone'               => !empty($id_settings->phone) ? $id_settings->phone : ($school_settings->phone ?? ''),
            'email'               => !empty($id_settings->email) ? $id_settings->email : ($school_settings->email ?? ''),
            'website'             => !empty($id_settings->website) ? $id_settings->website : ($school_settings->website ?? ''),
            'emergency_contact'   => !empty($id_settings->emergency_contact) ? $id_settings->emergency_contact : ($school_settings->phone ?? ''),
            'principal_name'      => !empty($id_settings->principal_name) ? $id_settings->principal_name : ($school_settings->principal_name ?? 'Principal'),
            'principal_signature' => !empty($id_settings->principal_signature) ? $id_settings->principal_signature : '',
            'school_logo'         => !empty($id_settings->school_logo) ? $id_settings->school_logo : ($school_info->logo ?? ''),
            'return_text'         => !empty($id_settings->return_text) ? $id_settings->return_text : 'If found, please return this card to the school.',
            'validity_text'       => !empty($id_settings->validity_text) ? $id_settings->validity_text : 'Academic Session',
            'accent_color'        => !empty($id_settings->accent_color) ? $id_settings->accent_color : '#006c4a',
            'secondary_color'     => !empty($id_settings->secondary_color) ? $id_settings->secondary_color : '#ea580c',
        ];

        return $default;
    }

    /**
     * Save ID Card settings.
     *
     * @param array $data
     * @return bool
     */
    public function save_settings(array $data)
    {
        $school_id = $this->get_school_id();
        $existing = $this->db->where('school_id', $school_id)->limit(1)->get($this->settings_table)->row();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->where('setting_id', $existing->setting_id)->where('school_id', $school_id)->update($this->settings_table, $data);
        } else {
            $data['school_id'] = $school_id;
            return $this->db->insert($this->settings_table, $data);
        }
    }

    /**
     * Fetch complete, fresh student data for card generation.
     *
     * @param int $student_id
     * @return object|null
     */
    public function get_student_card_data($student_id)
    {
        $school_id = $this->get_school_id();
        $student = $this->db
            ->select('st.*, c.class_name, c.class_code, div.division_name as division_name, div.division_name as section_name, y.year_name, y.start_date, y.end_date')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = st.academic_year_id', 'left')
            ->where('st.school_id', $school_id)
            ->where('st.student_id', (int)$student_id)
            ->where('st.is_deleted', 'n')
            ->get()
            ->row();

        if (!$student) {
            return null;
        }

        // Attach last generated card history info if present
        $last_card = $this->db
            ->where('school_id', $school_id)
            ->where('student_id', (int)$student_id)
            ->order_by('id_card_id', 'DESC')
            ->limit(1)
            ->get($this->history_table)
            ->row();

        $student->card_history = $last_card ?: null;
        return $student;
    }

    /**
     * Fetch multiple students for bulk ID card generation.
     *
     * @param array $student_ids
     * @return array
     */
    public function get_students_bulk(array $student_ids)
    {
        if (empty($student_ids)) {
            return [];
        }

        $school_id = $this->get_school_id();
        $sanitized_ids = array_map('intval', $student_ids);

        return $this->db
            ->select('st.*, c.class_name, c.class_code, div.division_name as division_name, div.division_name as section_name, y.year_name, y.start_date, y.end_date')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = st.academic_year_id', 'left')
            ->where('st.school_id', $school_id)
            ->where_in('st.student_id', $sanitized_ids)
            ->where('st.is_deleted', 'n')
            ->order_by('c.class_id', 'ASC')
            ->order_by('div.division_id', 'ASC')
            ->order_by('st.roll_number', 'ASC')
            ->order_by('st.first_name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Record generation event in tbl_student_id_cards.
     *
     * @param int $student_id
     * @param int $academic_year_id
     * @param int $user_id
     * @param string $action ('Generated', 'Printed', 'Re-generated')
     * @return object
     */
    public function record_generation($student_id, $academic_year_id = null, $user_id = null, $action = 'Generated')
    {
        $school_id = $this->get_school_id();
        $academic_year_id = ($academic_year_id !== null && (int)$academic_year_id > 0)
            ? (int)$academic_year_id
            : (int)get_current_academic_year_id();
        $user_id = ($user_id !== null && (int)$user_id > 0)
            ? (int)$user_id
            : (int)(get_current_user_id() ?: 1);

        $student = $this->db
            ->where('school_id', $school_id)
            ->where('student_id', (int)$student_id)
            ->get('tbl_students')
            ->row();
        if (!$student) {
            return null;
        }

        $existing = $this->db
            ->where('school_id', $school_id)
            ->where('student_id', (int)$student_id)
            ->where('academic_year_id', (int)$academic_year_id)
            ->order_by('id_card_id', 'DESC')
            ->limit(1)
            ->get($this->history_table)
            ->row();

        $now = date('Y-m-d H:i:s');
        $card_number = 'IDC-' . date('Y') . '-' . str_pad($student_id, 5, '0', STR_PAD_LEFT);

        if ($existing) {
            $new_version = $existing->card_version + 1;
            $update_data = [
                'card_version'    => $new_version,
                'class_id'        => $student->class_id,
                'division_id'     => $student->division_id,
                'status'          => ($action === 'Regenerate') ? 'Re-generated' : $action,
                'generated_by'    => $user_id,
                'generated_at'    => $now,
                'last_printed_at' => ($action === 'Printed') ? $now : $existing->last_printed_at,
                'updated_at'      => $now
            ];
            $this->db
                ->where('id_card_id', $existing->id_card_id)
                ->where('school_id', $school_id)
                ->update($this->history_table, $update_data);
            $id_card_id = $existing->id_card_id;
        } else {
            $insert_data = [
                'school_id'        => $school_id,
                'card_number'      => $card_number,
                'student_id'       => (int)$student_id,
                'academic_year_id' => (int)$academic_year_id,
                'class_id'         => $student->class_id,
                'division_id'      => $student->division_id,
                'card_version'     => 1,
                'status'           => $action,
                'generated_by'     => $user_id,
                'generated_at'     => $now,
                'last_printed_at'  => ($action === 'Printed') ? $now : null,
                'updated_at'       => $now
            ];
            $this->db->insert($this->history_table, $insert_data);
            $id_card_id = $this->db->insert_id();
        }

        return $this->db
            ->where('id_card_id', $id_card_id)
            ->where('school_id', $school_id)
            ->get($this->history_table)
            ->row();
    }

    /**
     * Get ID Card history for DataTables with search, filters, pagination.
     */
    public function get_history($filters = [], $limit = 25, $offset = 0, $order_col = 'h.id_card_id', $order_dir = 'DESC')
    {
        $school_id = $this->get_school_id();
        $this->_apply_history_filters($filters);

        $allowed_cols = [
            0 => 'h.card_number',
            1 => 'st.first_name',
            2 => 'c.class_name',
            3 => 'h.card_version',
            4 => 'h.generated_at',
            5 => 'h.status',
            6 => 'h.id_card_id'
        ];

        $col = $allowed_cols[$order_col] ?? 'h.id_card_id';
        $dir = (strtoupper($order_dir) === 'ASC') ? 'ASC' : 'DESC';

        $this->db->select('h.*, st.admission_number, st.first_name, st.last_name, st.photo, st.gender, st.roll_number,
                          c.class_name, div.division_name as division_name, div.division_name as section_name, y.year_name, u.full_name as generated_by_name');
        $this->db->from($this->history_table . ' h');
        $this->db->join('tbl_students st', 'st.student_id = h.student_id', 'left');
        $this->db->join('tbl_classes c', 'c.class_id = st.class_id', 'left');
        $this->db->join('tbl_divisions div', 'div.division_id = st.division_id', 'left');
        $this->db->join('tbl_academic_years y', 'y.academic_year_id = h.academic_year_id', 'left');
        $this->db->join('tbl_users u', 'u.user_id = h.generated_by', 'left');
        $this->db->where('h.school_id', $school_id);

        $this->db->order_by($col, $dir);

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Count filtered history records for DataTables pagination.
     */
    public function get_history_count($filters = [])
    {
        $school_id = $this->get_school_id();
        $this->_apply_history_filters($filters);
        $this->db->from($this->history_table . ' h');
        $this->db->join('tbl_students st', 'st.student_id = h.student_id', 'left');
        $this->db->join('tbl_classes c', 'c.class_id = st.class_id', 'left');
        $this->db->where('h.school_id', $school_id);
        return $this->db->count_all_results();
    }

    /**
     * Apply filter conditions for history queries.
     */
    private function _apply_history_filters($filters = [])
    {
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('h.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('st.class_id', (int)$filters['class_id']);
        }
        if (!empty($filters['division_id'])) {
            $this->db->where('st.division_id', (int)$filters['division_id']);
        }
        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $this->db->where('h.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $this->db->group_start()
                ->like('h.card_number', $s)
                ->or_like('st.first_name', $s)
                ->or_like('st.last_name', $s)
                ->or_like('st.admission_number', $s)
                ->or_like('st.roll_number', $s)
                ->or_like('c.class_name', $s)
            ->group_end();
        }
    }
}
