<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Global_search_model
 *
 * Provides high-performance, parameterized multi-entity searching across:
 *  - Students (admission no, name, roll no, phones, guardian)
 *  - Staff (employee code, name, phone, email, designation)
 *  - Classes & Divisions (name, code, division)
 *  - Subjects (name, code, class)
 *
 * Prioritizes the active academic year while retaining historical record visibility.
 */
class Global_search_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        if (isset($this->load) && !isset($this->db)) {
            $this->load->database();
        }
    }

    /**
     * Clean phone query by stripping +, spaces, dashes, parentheses.
     * Returns cleaned digit string if query consists of phone-like characters.
     */
    protected function extract_phone_digits($raw)
    {
        $trimmed = trim((string)$raw);
        // Only treat as phone if the query only consists of digits and standard phone symbols (+ - space ( ) .)
        if (!preg_match('/^[\+\d\s\-\(\)\.]+$/', $trimmed)) {
            return ['raw_clean' => null, 'local' => null];
        }

        $digits = preg_replace('/[^0-9]/', '', $trimmed);
        if (strlen($digits) < 4) {
            return ['raw_clean' => null, 'local' => null];
        }

        // If query starts with country code 91 and has > 10 digits, also offer local 10 digits
        if (strlen($digits) > 10 && substr($digits, 0, 2) === '91') {
            $digits_local = substr($digits, 2);
        } else {
            $digits_local = null;
        }
        return [
            'raw_clean' => $digits,
            'local'     => $digits_local
        ];
    }

    /**
     * Perform unified global search across permitted categories.
     *
     * @param string $query               Raw search term from user
     * @param int    $limit_per_category  Max items per category (default 5 for dropdown)
     * @param int    $active_year_id      Currently active academic year ID
     * @param array  $permissions         Map of category => boolean permission
     * @return array
     */
    public function search_all($query, $limit_per_category = 5, $active_year_id = null, $permissions = [])
    {
        $query = trim((string)$query);
        if ($query === '') {
            return [
                'students' => ['count' => 0, 'items' => []],
                'staff'    => ['count' => 0, 'items' => []],
                'classes'  => ['count' => 0, 'items' => []],
                'subjects' => ['count' => 0, 'items' => []],
                'total'    => 0,
            ];
        }

        if (empty($permissions)) {
            $permissions = [
                'students' => true,
                'staff'    => true,
                'classes'  => true,
                'subjects' => true,
            ];
        }

        $results = [
            'students' => ['count' => 0, 'items' => []],
            'staff'    => ['count' => 0, 'items' => []],
            'classes'  => ['count' => 0, 'items' => []],
            'subjects' => ['count' => 0, 'items' => []],
            'total'    => 0,
        ];

        // 1. Students
        if (!empty($permissions['students'])) {
            $results['students'] = $this->search_students($query, $limit_per_category, $active_year_id);
        }

        // 2. Staff
        if (!empty($permissions['staff'])) {
            $results['staff'] = $this->search_staff($query, $limit_per_category);
        }

        // 3. Classes
        if (!empty($permissions['classes'])) {
            $results['classes'] = $this->search_classes($query, $limit_per_category, $active_year_id);
        }

        // 4. Subjects
        if (!empty($permissions['subjects'])) {
            $results['subjects'] = $this->search_subjects($query, $limit_per_category, $active_year_id);
        }

        $results['total'] = $results['students']['count']
            + $results['staff']['count']
            + $results['classes']['count']
            + $results['subjects']['count'];

        return $results;
    }

    /**
     * Search Students
     */
    public function search_students($query, $limit = 5, $active_year_id = null, $offset = 0)
    {
        $phone_info = $this->extract_phone_digits($query);
        $phone_clean = $phone_info['raw_clean'];
        $phone_local = $phone_info['local'];

        // Determine if query is purely numeric (potential roll number or admission number)
        $is_numeric = ctype_digit($query);

        // Subquery or conditions
        $where_clauses = [];
        $params = [];

        // Exact or partial admission number
        $where_clauses[] = "st.admission_number LIKE ?";
        $params[] = '%' . $query . '%';

        // Names
        $where_clauses[] = "st.first_name LIKE ?";
        $params[] = '%' . $query . '%';

        $where_clauses[] = "st.last_name LIKE ?";
        $params[] = '%' . $query . '%';

        $where_clauses[] = "CONCAT(TRIM(st.first_name), ' ', TRIM(COALESCE(st.last_name, ''))) LIKE ?";
        $params[] = '%' . $query . '%';

        $where_clauses[] = "CONCAT(TRIM(st.first_name), ' ', TRIM(COALESCE(st.middle_name, '')), ' ', TRIM(COALESCE(st.last_name, ''))) LIKE ?";
        $params[] = '%' . $query . '%';

        // Guardian
        $where_clauses[] = "st.guardian_name LIKE ?";
        $params[] = '%' . $query . '%';

        // Roll number
        if ($is_numeric || strlen($query) <= 10) {
            $where_clauses[] = "st.roll_number = ?";
            $params[] = $query;
        }

        // Phone numbers (search in student_phone, father_phone, mother_phone, guardian_phone, emergency_contact)
        if ($phone_clean) {
            $where_clauses[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(st.student_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?";
            $params[] = '%' . $phone_clean . '%';

            $where_clauses[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(st.father_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?";
            $params[] = '%' . $phone_clean . '%';

            $where_clauses[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(st.guardian_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?";
            $params[] = '%' . $phone_clean . '%';

            if ($phone_local) {
                $where_clauses[] = "st.student_phone LIKE ?";
                $params[] = '%' . $phone_local . '%';
                $where_clauses[] = "st.father_phone LIKE ?";
                $params[] = '%' . $phone_local . '%';
                $where_clauses[] = "st.guardian_phone LIKE ?";
                $params[] = '%' . $phone_local . '%';
            }
        }

        $or_condition = '(' . implode(' OR ', $where_clauses) . ')';

        $school_id = get_current_school_id();
        array_unshift($params, $school_id);

        // Base query
        $base_sql = "
            FROM tbl_students st
            LEFT JOIN tbl_classes cl ON cl.class_id = st.class_id
            LEFT JOIN tbl_divisions dv ON dv.division_id = st.division_id
            LEFT JOIN tbl_academic_years ay ON ay.academic_year_id = st.academic_year_id
            WHERE st.is_deleted = 'n'
              AND st.status >= 0
              AND st.school_id = ?
              AND {$or_condition}
        ";

        // Count total matching
        $count_sql = "SELECT COUNT(*) as total " . $base_sql;
        $count_res = $this->db->query($count_sql, $params)->row();
        $total_count = $count_res ? (int)$count_res->total : 0;

        if ($total_count === 0) {
            return ['count' => 0, 'items' => []];
        }

        // Select items with intelligent ranking
        // 1. Exact match on admission_number
        // 2. Exact match on roll_number
        // 3. Current active academic year
        // 4. Status active
        // 5. student_id DESC
        $order_params = [];
        $order_sql = "
            ORDER BY 
                (st.admission_number = ?) DESC,
                (st.roll_number = ?) DESC,
                " . ($active_year_id ? "(st.academic_year_id = ?) DESC," : "") . "
                (st.status = 1) DESC,
                st.student_id DESC
        ";
        $order_params[] = $query;
        $order_params[] = $query;
        if ($active_year_id) {
            $order_params[] = (int)$active_year_id;
        }

        $limit_int  = (int)$limit;
        $offset_int = (int)$offset;
        $select_sql = "
            SELECT 
                st.student_id,
                st.admission_number,
                st.first_name,
                st.middle_name,
                st.last_name,
                st.roll_number,
                st.student_phone,
                st.father_phone,
                st.guardian_name,
                st.guardian_phone,
                st.gender,
                st.photo,
                st.status,
                st.academic_year_id,
                cl.class_name,
                cl.class_code,
                dv.division_name,
                ay.year_name as academic_year_name,
                ay.is_active as year_is_active
            " . $base_sql . "
            " . $order_sql . "
            LIMIT {$limit_int} OFFSET {$offset_int}
        ";

        $final_params = array_merge($params, $order_params);
        $query_res = $this->db->query($select_sql, $final_params);
        $rows = $query_res->result_array();

        $items = [];
        foreach ($rows as $row) {
            $full_name = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $class_display = $row['class_name'] ? $row['class_name'] . ($row['division_name'] ? ' (' . $row['division_name'] . ')' : '') : 'Unassigned';

            // Match highlight field
            $matched_field = 'Name';
            $matched_val   = $full_name;
            if (stripos($row['admission_number'], $query) !== false) {
                $matched_field = 'Admission No';
                $matched_val   = $row['admission_number'];
            } elseif ($row['roll_number'] && (string)$row['roll_number'] === $query) {
                $matched_field = 'Roll No';
                $matched_val   = $row['roll_number'];
            } elseif ($row['student_phone'] && stripos($row['student_phone'], $phone_clean ?: $query) !== false) {
                $matched_field = 'Student Phone';
                $matched_val   = $row['student_phone'];
            } elseif ($row['father_phone'] && stripos($row['father_phone'], $phone_clean ?: $query) !== false) {
                $matched_field = 'Parent Phone';
                $matched_val   = $row['father_phone'];
            } elseif ($row['guardian_name'] && stripos($row['guardian_name'], $query) !== false) {
                $matched_field = 'Guardian';
                $matched_val   = $row['guardian_name'];
            }

            $items[] = [
                'id'             => (int)$row['student_id'],
                'type'           => 'student',
                'title'          => $full_name,
                'subtitle'       => $class_display . ' · Adm #' . $row['admission_number'],
                'class_name'     => $class_display,
                'admission_no'   => $row['admission_number'],
                'roll_no'        => $row['roll_number'] ?: '-',
                'status'         => (int)$row['status'] === 1 ? 'Active' : 'Inactive',
                'photo'          => $row['photo'] ? base_url('uploads/students/' . $row['photo']) : null,
                'gender'         => $row['gender'],
                'matched_field'  => $matched_field,
                'matched_val'    => $matched_val,
                'academic_year'  => $row['academic_year_name'] ?? '',
                'is_active_year' => ($active_year_id && (int)$row['academic_year_id'] === (int)$active_year_id),
                'url'            => site_url('students/profile/' . $row['student_id']),
            ];
        }

        return [
            'count' => $total_count,
            'items' => $items,
        ];
    }

    /**
     * Search Staff / Teachers
     */
    public function search_staff($query, $limit = 5, $offset = 0)
    {
        $phone_info = $this->extract_phone_digits($query);
        $phone_clean = $phone_info['raw_clean'];
        $phone_local = $phone_info['local'];

        $where_clauses = [];
        $params = [];

        // Employee code
        $where_clauses[] = "s.employee_code LIKE ?";
        $params[] = '%' . $query . '%';

        // Full name
        $where_clauses[] = "s.full_name LIKE ?";
        $params[] = '%' . $query . '%';

        // Email
        $where_clauses[] = "s.email LIKE ?";
        $params[] = '%' . $query . '%';

        // Phone numbers
        if ($phone_clean) {
            $where_clauses[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?";
            $params[] = '%' . $phone_clean . '%';

            $where_clauses[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.alternate_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?";
            $params[] = '%' . $phone_clean . '%';

            if ($phone_local) {
                $where_clauses[] = "s.phone LIKE ?";
                $params[] = '%' . $phone_local . '%';
            }
        }

        $or_condition = '(' . implode(' OR ', $where_clauses) . ')';

        $school_id = get_current_school_id();
        array_unshift($params, $school_id);

        $base_sql = "
            FROM tbl_staff s
            LEFT JOIN tbl_designations dg ON dg.designation_id = s.designation_id
            WHERE s.is_deleted = 'n'
              AND s.school_id = ?
              AND {$or_condition}
        ";

        $count_sql = "SELECT COUNT(*) as total " . $base_sql;
        $count_res = $this->db->query($count_sql, $params)->row();
        $total_count = $count_res ? (int)$count_res->total : 0;

        if ($total_count === 0) {
            return ['count' => 0, 'items' => []];
        }

        $order_params = [$query];
        $order_sql = "
            ORDER BY 
                (s.employee_code = ?) DESC,
                (s.status = 1) DESC,
                s.staff_id DESC
        ";

        $limit_int  = (int)$limit;
        $offset_int = (int)$offset;
        $select_sql = "
            SELECT 
                s.staff_id,
                s.employee_code,
                s.full_name,
                s.email,
                s.phone,
                s.staff_type,
                s.employment_status,
                s.photo,
                s.status,
                dg.designation_name
            " . $base_sql . "
            " . $order_sql . "
            LIMIT {$limit_int} OFFSET {$offset_int}
        ";

        $final_params = array_merge($params, $order_params);
        $query_res = $this->db->query($select_sql, $final_params);
        $rows = $query_res->result_array();

        $items = [];
        foreach ($rows as $row) {
            $role_label = $row['designation_name'] ?: ($row['staff_type'] === 'teacher' ? 'Teacher' : 'Staff');
            $items[] = [
                'id'            => (int)$row['staff_id'],
                'type'          => 'staff',
                'title'         => $row['full_name'],
                'employee_code' => $row['employee_code'],
                'staff_type'    => $row['staff_type'],
                'role'          => $role_label,
                'phone'         => $row['phone'],
                'email'         => $row['email'],
                'photo'         => $row['photo'],
                'status'        => (int)$row['status'],
                'url'           => site_url('staff/profile/' . $row['staff_id']),
            ];
        }

        return [
            'count' => $total_count,
            'items' => $items,
        ];
    }

    /**
     * Search Classes & Divisions
     */
    public function search_classes($query, $limit = 5, $active_year_id = null, $offset = 0)
    {
        $where_clauses = [];
        $params = [];

        // Class name, code
        $where_clauses[] = "c.class_name LIKE ?";
        $params[] = '%' . $query . '%';

        $where_clauses[] = "c.class_code LIKE ?";
        $params[] = '%' . $query . '%';

        // Division name matching via tbl_divisions
        $where_clauses[] = "EXISTS (SELECT 1 FROM tbl_divisions d WHERE d.class_id = c.class_id AND d.division_name LIKE ? AND d.is_deleted = 'n')";
        $params[] = '%' . $query . '%';

        // Combined class + division search (e.g. "Grade 10 A" or "10-A")
        $where_clauses[] = "EXISTS (SELECT 1 FROM tbl_divisions d WHERE d.class_id = c.class_id AND CONCAT(c.class_name, ' ', d.division_name) LIKE ? AND d.is_deleted = 'n')";
        $params[] = '%' . $query . '%';

        $or_condition = '(' . implode(' OR ', $where_clauses) . ')';

        $school_id = get_current_school_id();
        array_unshift($params, $school_id);

        $base_sql = "
            FROM tbl_classes c
            LEFT JOIN tbl_academic_years ay ON ay.academic_year_id = c.academic_year_id
            WHERE c.is_deleted = 'n'
              AND c.school_id = ?
              AND {$or_condition}
        ";

        $count_sql = "SELECT COUNT(*) as total " . $base_sql;
        $count_res = $this->db->query($count_sql, $params)->row();
        $total_count = $count_res ? (int)$count_res->total : 0;

        if ($total_count === 0) {
            return ['count' => 0, 'items' => []];
        }

        $order_params = [];
        $order_sql = "
            ORDER BY 
                (c.class_code = ?) DESC,
                (c.class_name = ?) DESC,
                " . ($active_year_id ? "(c.academic_year_id = ?) DESC," : "") . "
                (c.status = 1) DESC,
                c.class_id DESC
        ";
        $order_params[] = $query;
        $order_params[] = $query;
        if ($active_year_id) {
            $order_params[] = (int)$active_year_id;
        }

        $limit_int  = (int)$limit;
        $offset_int = (int)$offset;
        $select_sql = "
            SELECT 
                c.class_id,
                c.class_name,
                c.class_code,
                c.academic_year_id,
                c.capacity,
                c.status,
                ay.year_name as academic_year_name,
                (SELECT GROUP_CONCAT(d.division_name ORDER BY d.division_name SEPARATOR ', ') 
                 FROM tbl_divisions d 
                 WHERE d.class_id = c.class_id AND d.is_deleted = 'n' AND d.school_id = " . (int)$school_id . ") as division_names
            " . $base_sql . "
            " . $order_sql . "
            LIMIT {$limit_int} OFFSET {$offset_int}
        ";

        $final_params = array_merge($params, $order_params);
        $query_res = $this->db->query($select_sql, $final_params);
        $rows = $query_res->result_array();

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'             => (int)$row['class_id'],
                'type'           => 'class',
                'title'          => $row['class_name'],
                'class_code'     => $row['class_code'],
                'divisions'      => $row['division_names'] ?: 'None',
                'academic_year'  => $row['academic_year_name'] ?? '',
                'is_active_year' => ($active_year_id && (int)$row['academic_year_id'] === (int)$active_year_id),
                'url'            => site_url('academics/classes'),
            ];
        }

        return [
            'count' => $total_count,
            'items' => $items,
        ];
    }

    /**
     * Search Subjects
     */
    public function search_subjects($query, $limit = 5, $active_year_id = null, $offset = 0)
    {
        $where_clauses = [];
        $params = [];

        $where_clauses[] = "sub.subject_name LIKE ?";
        $params[] = '%' . $query . '%';

        $where_clauses[] = "sub.subject_code LIKE ?";
        $params[] = '%' . $query . '%';

        $or_condition = '(' . implode(' OR ', $where_clauses) . ')';

        $school_id = get_current_school_id();
        array_unshift($params, $school_id);

        $base_sql = "
            FROM tbl_subjects sub
            LEFT JOIN tbl_classes cl ON cl.class_id = sub.class_id
            LEFT JOIN tbl_academic_years ay ON ay.academic_year_id = cl.academic_year_id
            WHERE sub.is_deleted = 'n'
              AND sub.school_id = ?
              AND {$or_condition}
        ";

        $count_sql = "SELECT COUNT(*) as total " . $base_sql;
        $count_res = $this->db->query($count_sql, $params)->row();
        $total_count = $count_res ? (int)$count_res->total : 0;

        if ($total_count === 0) {
            return ['count' => 0, 'items' => []];
        }

        $order_params = [];
        $order_sql = "
            ORDER BY 
                (sub.subject_code = ?) DESC,
                (sub.subject_name = ?) DESC,
                " . ($active_year_id ? "(cl.academic_year_id = ?) DESC," : "") . "
                (sub.status = 1) DESC,
                sub.subject_id DESC
        ";
        $order_params[] = $query;
        $order_params[] = $query;
        if ($active_year_id) {
            $order_params[] = (int)$active_year_id;
        }

        $limit_int  = (int)$limit;
        $offset_int = (int)$offset;
        $select_sql = "
            SELECT 
                sub.subject_id,
                sub.subject_name,
                sub.subject_code,
                sub.subject_type,
                sub.class_id,
                cl.class_name,
                cl.academic_year_id,
                ay.year_name as academic_year_name
            " . $base_sql . "
            " . $order_sql . "
            LIMIT {$limit_int} OFFSET {$offset_int}
        ";

        $final_params = array_merge($params, $order_params);
        $query_res = $this->db->query($select_sql, $final_params);
        $rows = $query_res->result_array();

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'             => (int)$row['subject_id'],
                'type'           => 'subject',
                'title'          => $row['subject_name'],
                'subject_code'   => $row['subject_code'],
                'subject_type'   => $row['subject_type'],
                'class_name'     => $row['class_name'] ?: 'General',
                'academic_year'  => $row['academic_year_name'] ?? '',
                'is_active_year' => ($active_year_id && (int)$row['academic_year_id'] === (int)$active_year_id),
                'url'            => site_url('academics/subjects'),
            ];
        }

        return [
            'count' => $total_count,
            'items' => $items,
        ];
    }
}
