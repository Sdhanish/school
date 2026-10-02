<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Result_model extends CI_Model {

    protected $table = 'tbl_student_results';
    protected $primaryKey = 'result_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Grade_model');
        $this->load->model('Exam_setting_model');
        $this->load->model('Exam_mark_model');
        $this->load->model('Exam_audit_model');
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    /* =========================================================================
       1. Core Backend Result Calculation Engine
       ========================================================================= */
    public function calculate_results_for_exam($exam_id, $class_id = NULL, $division_id = NULL, $user_id = NULL)
    {
        $school_id = $this->get_school_id();
        $settings = $this->Exam_setting_model->get_settings();

        // 1. Identify students with approved marks for this exam
        $this->db
            ->select('st.student_id, st.academic_year_id, st.class_id, st.division_id')
            ->from('tbl_students st')
            ->join('tbl_exam_marks m', 'm.student_id = st.student_id AND m.exam_id = ' . (int)$exam_id, 'inner')
            ->where('st.school_id', $school_id)
            ->where('st.status', 1)
            ->group_by('st.student_id');

        if ($class_id) $this->db->where('st.class_id', (int)$class_id);
        if ($division_id) $this->db->where('st.division_id', (int)$division_id);

        $students = $this->db->get()->result();

        $processed_count = 0;

        foreach ($students as $stu) {
            $student_id = $stu->student_id;

            // Pull all subject marks for this student in this exam
            $marks = $this->Exam_mark_model->get_student_subject_marks($exam_id, $student_id);

            $total_marks = 0.00;
            $max_marks   = 0.00;
            $failed_count = 0;
            $gpa_sum = 0.00;
            $subject_count = 0;

            foreach ($marks as $m) {
                $sub_max  = (float)$m->max_marks ?: 100.00;
                $sub_pass = (float)$m->passing_marks ?: 35.00;

                $max_marks += $sub_max;

                if ($m->is_absent) {
                    $failed_count++;
                    $subject_count++;
                } elseif ($m->is_exempted) {
                    // Exempted does not count against total/pass
                } else {
                    $obtained = (float)$m->marks_obtained;
                    $total_marks += $obtained;

                    if ($obtained < $sub_pass && $settings->subject_pass_mark_rule) {
                        $failed_count++;
                    }

                    $gpa_sum += (float)$m->grade_point;
                    $subject_count++;
                }
            }

            $percentage = ($max_marks > 0) ? round(($total_marks / $max_marks) * 100, (int)$settings->decimal_precision) : 0.00;
            $gpa = ($subject_count > 0) ? round($gpa_sum / $subject_count, 2) : 0.00;

            $overall_grade_obj = $this->Grade_model->resolve_grade_for_percentage($percentage);
            $overall_grade = $overall_grade_obj->grade_name;

            // Determine Pass/Fail Status
            $pass_status = 'Pass';
            if ($percentage < (float)$settings->overall_pass_percentage) {
                $pass_status = 'Fail';
            }
            if ($failed_count > 0 && $settings->single_subject_fail_overall) {
                $pass_status = 'Fail';
            }

            // Insert / Update tbl_student_results
            $existing = $this->db
                ->where('school_id', $school_id)
                ->where('exam_id', (int)$exam_id)
                ->where('student_id', (int)$student_id)
                ->get($this->table)
                ->row();

            $data = [
                'school_id'             => $school_id,
                'exam_id'               => (int)$exam_id,
                'student_id'            => (int)$student_id,
                'academic_year_id'      => $stu->academic_year_id,
                'class_id'              => $stu->class_id,
                'division_id'           => $stu->division_id,
                'total_marks'           => $total_marks,
                'max_marks'             => $max_marks,
                'percentage'            => $percentage,
                'overall_grade'         => $overall_grade,
                'gpa'                   => $gpa,
                'pass_status'           => $pass_status,
                'failed_subjects_count' => $failed_count,
                'updated_at'            => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $this->db
                    ->where('result_id', $existing->result_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert($this->table, $data);
            }

            $processed_count++;
        }

        // 2. Rank Calculation Engine with Dense/Standard Competition Ties
        $this->recalculate_ranks_for_exam($exam_id, $class_id, $division_id);

        if ($user_id) {
            $this->Exam_audit_model->log($user_id, 'RESULT_CALCULATED', 'tbl_exams', $exam_id, "Calculated results for {$processed_count} students.");
        }

        return $processed_count;
    }

    /* =========================================================================
       2. Rank Engine (Handles Ties: 1, 2, 2, 4)
       ========================================================================= */
    public function recalculate_ranks_for_exam($exam_id, $class_id = NULL, $division_id = NULL)
    {
        $school_id = $this->get_school_id();
        $settings = $this->Exam_setting_model->get_settings();
        $sort_field = 'percentage';
        if ($settings->rank_criteria === 'Total Marks') $sort_field = 'total_marks';
        elseif ($settings->rank_criteria === 'GPA') $sort_field = 'gpa';

        // 1. Calculate Class-Wise Ranks
        $this->db
            ->select('result_id, student_id, class_id, division_id, ' . $sort_field . ' as score, pass_status')
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->order_by('class_id', 'ASC')
            ->order_by($sort_field, 'DESC');

        if ($class_id) $this->db->where('class_id', (int)$class_id);

        $results = $this->db->get()->result();

        // Group by class
        $by_class = [];
        foreach ($results as $r) {
            $by_class[$r->class_id][] = $r;
        }

        foreach ($by_class as $c_id => $rows) {
            $passed_count = 0;
            $rank = 1;
            $prev_score = NULL;

            foreach ($rows as $row) {
                if (!$settings->include_failed_in_rank && $row->pass_status === 'Fail') {
                    $this->db
                        ->where('result_id', $row->result_id)
                        ->where('school_id', $school_id)
                        ->update($this->table, ['class_rank' => NULL]);
                    continue;
                }

                $score = (float)$row->score;
                if ($prev_score !== null && $score == $prev_score) {
                    // keep same rank
                } else {
                    $rank = $passed_count + 1;
                }

                $this->db
                    ->where('result_id', $row->result_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, ['class_rank' => $rank]);
                $prev_score = $score;
                $passed_count++;
            }
        }

        // 2. Calculate Division-Wise Ranks
        $this->db
            ->select('result_id, class_id, division_id, ' . $sort_field . ' as score, pass_status')
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->order_by('class_id', 'ASC')
            ->order_by('division_id', 'ASC')
            ->order_by($sort_field, 'DESC');

        if ($class_id) $this->db->where('class_id', (int)$class_id);
        if ($division_id) $this->db->where('division_id', (int)$division_id);

        $div_results = $this->db->get()->result();
        $by_division = [];
        foreach ($div_results as $r) {
            $key = $r->class_id . '_' . $r->division_id;
            $by_division[$key][] = $r;
        }

        foreach ($by_division as $div_key => $rows) {
            $passed_count = 0;
            $rank = 1;
            $prev_score = NULL;

            foreach ($rows as $row) {
                if (!$settings->include_failed_in_rank && $row->pass_status === 'Fail') {
                    $this->db
                        ->where('result_id', $row->result_id)
                        ->where('school_id', $school_id)
                        ->update($this->table, ['division_rank' => NULL]);
                    continue;
                }

                $score = (float)$row->score;
                if ($prev_score !== null && $score == $prev_score) {
                    // keep same rank
                } else {
                    $rank = $passed_count + 1;
                }

                $this->db
                    ->where('result_id', $row->result_id)
                    ->where('school_id', $school_id)
                    ->update($this->table, ['division_rank' => $rank]);
                $prev_score = $score;
                $passed_count++;
            }
        }
    }

    /* =========================================================================
       3. Queries for Student Results & Ranks
       ========================================================================= */
    public function get_results_list($filters = array(), $limit = NULL, $offset = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->select('r.*, e.exam_name, e.status as exam_status, st.admission_number, st.roll_number, st.first_name, st.last_name, st.photo,
                c.class_name, d.division_name as division_name, y.year_name')
            ->from('tbl_student_results r')
            ->join('tbl_exams e', 'e.exam_id = r.exam_id', 'left')
            ->join('tbl_students st', 'st.student_id = r.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = r.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = r.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = r.academic_year_id', 'left')
            ->where('r.school_id', $school_id)
            ->order_by('r.percentage', 'DESC')
            ->order_by('CAST(st.roll_number AS UNSIGNED)', 'ASC');

        $this->_apply_result_filters($filters);

        if ($limit) {
            $this->db->limit($limit, $offset ?: 0);
        }

        return $this->db->get()->result();
    }

    public function count_results($filters = array())
    {
        $school_id = $this->get_school_id();
        $this->db
            ->from('tbl_student_results r')
            ->join('tbl_students st', 'st.student_id = r.student_id', 'left')
            ->where('r.school_id', $school_id);

        $this->_apply_result_filters($filters);

        return $this->db->count_all_results();
    }

    private function _apply_result_filters($filters)
    {
        if (!empty($filters['exam_id'])) $this->db->where('r.exam_id', (int)$filters['exam_id']);
        if (!empty($filters['academic_year_id'])) $this->db->where('r.academic_year_id', (int)$filters['academic_year_id']);
        if (!empty($filters['class_id'])) $this->db->where('r.class_id', (int)$filters['class_id']);
        if (!empty($filters['division_id'])) $this->db->where('r.division_id', (int)$filters['division_id']);
        if (!empty($filters['student_id'])) $this->db->where('r.student_id', (int)$filters['student_id']);
        if (!empty($filters['pass_status'])) $this->db->where('r.pass_status', $filters['pass_status']);
        if (isset($filters['is_published']) && $filters['is_published'] !== '') {
            $this->db->where('r.is_published', (int)$filters['is_published']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start()
                ->like('st.first_name', $s)
                ->or_like('st.last_name', $s)
                ->or_like('st.admission_number', $s)
                ->group_end();
        }
    }

    public function get_result_by_id($result_id)
    {
        $school_id = $this->get_school_id();
        $result = $this->db
            ->select('r.*, e.exam_name, e.start_date as exam_start_date, e.end_date as exam_end_date, t.type_name as exam_type,
                st.admission_number, st.roll_number, st.first_name, st.last_name, st.gender, st.date_of_birth, st.photo,
                st.guardian_name, st.guardian_phone,
                c.class_name, d.division_name as division_name, y.year_name')
            ->from('tbl_student_results r')
            ->join('tbl_exams e', 'e.exam_id = r.exam_id', 'left')
            ->join('tbl_exam_types t', 't.exam_type_id = e.exam_type_id', 'left')
            ->join('tbl_students st', 'st.student_id = r.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = r.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = r.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = r.academic_year_id', 'left')
            ->where('r.school_id', $school_id)
            ->where('r.result_id', (int)$result_id)
            ->get()
            ->row();

        if ($result) {
            $result->subject_marks = $this->Exam_mark_model->get_student_subject_marks($result->exam_id, $result->student_id);
        }

        return $result;
    }

    public function get_student_exam_result($student_id, $exam_id)
    {
        $school_id = $this->get_school_id();
        $row = $this->db
            ->where('school_id', $school_id)
            ->where('student_id', (int)$student_id)
            ->where('exam_id', (int)$exam_id)
            ->get($this->table)
            ->row();

        if ($row) {
            return $this->get_result_by_id($row->result_id);
        }
        return NULL;
    }

    /* =========================================================================
       4. Result Publishing & Security Locking
       ========================================================================= */
    public function publish_results($exam_id, $class_id = NULL, $division_id = NULL, $user_id = NULL)
    {
        $school_id = $this->get_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id);
        if ($class_id) $this->db->where('class_id', (int)$class_id);
        if ($division_id) $this->db->where('division_id', (int)$division_id);

        $updated = $this->db->update($this->table, [
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
            'published_by' => $user_id
        ]);

        // Update exam status to Published
        $this->db
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->update('tbl_exams', ['status' => 'Published']);

        if ($user_id) {
            $this->Exam_audit_model->log($user_id, 'RESULT_PUBLISHED', 'tbl_exams', $exam_id, 'Published exam results and locked modifications.');
        }

        return $updated;
    }

    public function unlock_results_for_correction($exam_id, $user_id, $reason = '')
    {
        $school_id = $this->get_school_id();
        $this->db
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->update($this->table, [
                'is_published' => 0
            ]);

        $this->db
            ->where('school_id', $school_id)
            ->where('exam_id', (int)$exam_id)
            ->update('tbl_exams', ['status' => 'Marks Pending']);

        $this->Exam_audit_model->log($user_id, 'RESULT_UNLOCKED', 'tbl_exams', $exam_id, "Unlocked results for correction. Reason: {$reason}");
        return TRUE;
    }

    /* =========================================================================
       5. Multi-Exam Progress Report Generator
       ========================================================================= */
    public function get_student_progress_report($student_id, $academic_year_id = NULL)
    {
        $school_id = $this->get_school_id();
        $student = $this->db
            ->select('st.*, c.class_name, d.division_name as division_name, y.year_name')
            ->from('tbl_students st')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions d', 'd.division_id = st.division_id', 'left')
            ->join('tbl_academic_years y', 'y.academic_year_id = st.academic_year_id', 'left')
            ->where('st.school_id', $school_id)
            ->where('st.student_id', (int)$student_id)
            ->get()
            ->row();

        if (!$student) return NULL;

        // Fetch all exams for this student
        $this->db
            ->select('r.*, e.exam_name, e.start_date, t.type_name')
            ->from('tbl_student_results r')
            ->join('tbl_exams e', 'e.exam_id = r.exam_id', 'inner')
            ->join('tbl_exam_types t', 't.exam_type_id = e.exam_type_id', 'left')
            ->where('r.school_id', $school_id)
            ->where('r.student_id', (int)$student_id)
            ->order_by('e.start_date', 'ASC');

        if ($academic_year_id) $this->db->where('r.academic_year_id', (int)$academic_year_id);

        $exam_results = $this->db->get()->result();

        // Build subject-wise comparison matrix
        $subject_matrix = [];
        $exams_list = [];

        foreach ($exam_results as $er) {
            $exams_list[] = [
                'exam_id'   => $er->exam_id,
                'exam_name' => $er->exam_name,
                'percentage'=> $er->percentage,
                'grade'     => $er->overall_grade
            ];

            $sub_marks = $this->Exam_mark_model->get_student_subject_marks($er->exam_id, $student_id);
            foreach ($sub_marks as $sm) {
                $sub_name = $sm->subject_name;
                if (!isset($subject_matrix[$sub_name])) {
                    $subject_matrix[$sub_name] = [
                        'subject_code' => $sm->subject_code,
                        'exams'        => []
                    ];
                }
                $subject_matrix[$sub_name]['exams'][$er->exam_id] = [
                    'marks'      => $sm->marks_obtained,
                    'max_marks'  => $sm->max_marks,
                    'percentage' => ($sm->max_marks > 0) ? round(($sm->marks_obtained / $sm->max_marks) * 100, 1) : 0,
                    'grade'      => $sm->grade
                ];
            }
        }

        // Calculate trends (Improvement, Stable, Decline)
        foreach ($subject_matrix as $sub_name => &$data) {
            $scores = [];
            foreach ($data['exams'] as $ex) {
                if ($ex['marks'] !== null) $scores[] = (float)$ex['percentage'];
            }
            if (count($scores) >= 2) {
                $first = $scores[0];
                $last = end($scores);
                $diff = $last - $first;
                if ($diff > 3.0) $data['trend'] = 'Improving';
                elseif ($diff < -3.0) $data['trend'] = 'Declining';
                else $data['trend'] = 'Stable';
            } else {
                $data['trend'] = 'Consistent';
            }
        }

        return (object) [
            'student'        => $student,
            'exams'          => $exams_list,
            'results'        => $exam_results,
            'subject_matrix' => $subject_matrix
        ];
    }

    /**
     * Fetch enrolled students eligible for multi-exam progress reports
     *
     * @param array $filters
     * @return array
     */
    public function get_progress_report_students($filters = [])
    {
        if (empty($filters['class_id'])) {
            return [];
        }

        $this->load->model('Student_model');
        return $this->Student_model->get_all($filters);
    }
}
