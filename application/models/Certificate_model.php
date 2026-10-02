<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_model extends CI_Model {

    protected $table = 'tbl_certificates';
    protected $primaryKey = 'certificate_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Certificate_setting_model');
    }

    public function get_all($filters = array())
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $this->db->select('cert.*, st.first_name, st.last_name, st.admission_number, st.guardian_name, c.class_name, div.division_name as division_name, div.division_name as section_name, ct.type_name, ct.type_code, ct.prefix, ay.year_name, u.username as generator_name')
            ->from('tbl_certificates cert')
            ->join('tbl_students st', 'st.student_id = cert.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_certificate_types ct', 'ct.type_id = cert.certificate_type_id', 'left')
            ->join('tbl_academic_years ay', 'ay.academic_year_id = cert.academic_year_id', 'left')
            ->join('tbl_users u', 'u.user_id = cert.generated_by', 'left')
            ->where('cert.is_deleted', 'n')
            ->where('cert.school_id', $school_id);

        if (!empty($filters['status'])) {
            $this->db->where('cert.status', $filters['status']);
        }
        if (!empty($filters['academic_year_id'])) {
            $this->db->where('cert.academic_year_id', (int)$filters['academic_year_id']);
        }
        if (!empty($filters['certificate_type_id'])) {
            $this->db->where('cert.certificate_type_id', $filters['certificate_type_id']);
        }
        if (!empty($filters['type_code'])) {
            $this->db->where('ct.type_code', $filters['type_code']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('st.class_id', $filters['class_id']);
        }
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $this->db->group_start()
                ->like('cert.certificate_no', $q)
                ->or_like('st.first_name', $q)
                ->or_like('st.last_name', $q)
                ->or_like('st.admission_number', $q)
                ->group_end();
        }

        $this->db->order_by('cert.certificate_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db->select('cert.*, st.first_name, st.last_name, st.admission_number, st.date_of_birth, st.gender, st.address as student_address, st.guardian_name, st.guardian_phone, st.created_at as admission_date, c.class_name, div.division_name as division_name, div.division_name as section_name, ct.type_name, ct.type_code, ct.prefix, ay.year_name, tmpl.header_content, tmpl.footer_content, tmpl.logo_position, tmpl.signature_layout, tmpl.paper_size, tmpl.orientation, u.username as generator_name')
            ->from('tbl_certificates cert')
            ->join('tbl_students st', 'st.student_id = cert.student_id', 'left')
            ->join('tbl_classes c', 'c.class_id = st.class_id', 'left')
            ->join('tbl_divisions div', 'div.division_id = st.division_id', 'left')
            ->join('tbl_certificate_types ct', 'ct.type_id = cert.certificate_type_id', 'left')
            ->join('tbl_academic_years ay', 'ay.academic_year_id = cert.academic_year_id', 'left')
            ->join('tbl_certificate_templates tmpl', 'tmpl.template_id = cert.template_id', 'left')
            ->join('tbl_users u', 'u.user_id = cert.generated_by', 'left')
            ->where('cert.certificate_id', $id)
            ->where('cert.school_id', $school_id)
            ->where('cert.is_deleted', 'n')
            ->get()
            ->row();
    }

    public function get_by_certificate_no($no)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db->where('certificate_no', $no)->where('school_id', $school_id)->get($this->table)->row();
    }

    public function insert($data)
    {
        if (empty($data['school_id']) && function_exists('get_current_school_id')) {
            $data['school_id'] = (int)get_current_school_id();
        }
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        return $this->db->where($this->primaryKey, $id)->where('school_id', $school_id)->update($this->table, $data);
    }

    /**
     * Generate unique, sequential certificate number
     * Format: {PREFIX}{YEAR}-{NUMBER} (e.g. BON-2026-00001, TC-2026-00002)
     */
    public function generate_certificate_number($type_code = 'CERT', $year = null)
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        if (!$year) $year = date('Y');

        $type = $this->db->where('type_code', $type_code)->where('school_id', $school_id)->get('tbl_certificate_types')->row();
        if (!$type) {
            $type = $this->db->where('type_code', $type_code)->get('tbl_certificate_types')->row();
        }
        $prefix = $type ? $type->prefix : 'CERT-';

        $settings = $this->Certificate_setting_model->get_settings();
        $len = $settings->number_sequence_length ?: 5;

        // Count existing certificates for this type, year, and school
        $count = $this->db
            ->where('school_id', $school_id)
            ->like('certificate_no', $prefix . $year, 'after')
            ->count_all_results($this->table);
        $next_num = $count + 1;

        $cert_no = $prefix . $year . '-' . str_pad($next_num, $len, '0', STR_PAD_LEFT);

        // Ensure uniqueness within school
        while ($this->get_by_certificate_no($cert_no)) {
            $next_num++;
            $cert_no = $prefix . $year . '-' . str_pad($next_num, $len, '0', STR_PAD_LEFT);
        }

        return $cert_no;
    }

    /**
     * Dynamic Template Compiler: replaces all supported placeholders with real database records
     */
    public function compile_certificate_content($body_template, $student, $school, $cert_no, $issue_date, $extra = array())
    {
        $dob_formatted = $student->date_of_birth ? date('d-m-Y', strtotime($student->date_of_birth)) : 'N/A';
        $adm_date_formatted = $student->admission_date ? date('d-m-Y', strtotime($student->admission_date)) : ($student->created_at ? date('d-m-Y', strtotime($student->created_at)) : 'N/A');
        $issue_formatted = date('d-m-Y', strtotime($issue_date));
        $dol_formatted = !empty($extra['date_of_leaving']) ? date('d-m-Y', strtotime($extra['date_of_leaving'])) : date('d-m-Y');

        $replacements = array(
            '{school_name}'          => html_escape($school->school_name ?? 'Login2'),
            '{school_code}'          => html_escape($school->school_code ?? 'SCH-01'),
            '{school_address}'       => html_escape($school->address ?? ''),
            '{school_phone}'         => html_escape($school->phone ?? ''),
            '{school_email}'         => html_escape($school->email ?? ''),
            '{principal_name}'       => html_escape($school->principal_name ?? 'Principal'),
            '{student_name}'         => html_escape($student->first_name . ' ' . $student->last_name),
            '{admission_number}'     => html_escape($student->admission_number),
            '{roll_number}'          => html_escape($student->roll_number ?? 'N/A'),
            '{date_of_birth}'        => $dob_formatted,
            '{gender}'               => html_escape($student->gender ?? 'N/A'),
            '{class}'                => html_escape($student->class_name ?? 'N/A'),
            '{section}'              => html_escape($student->section_name ?? 'A'),
            '{academic_year}'        => html_escape($student->year_name ?? date('Y')),
            '{admission_date}'       => $adm_date_formatted,
            '{issue_date}'           => $issue_formatted,
            '{certificate_number}'   => html_escape($cert_no),
            '{parent_name}'          => html_escape($student->guardian_name ?? 'Guardian'),
            '{guardian_phone}'       => html_escape($student->guardian_phone ?? 'N/A'),
            '{student_address}'      => html_escape($student->student_address ?? $student->address ?? 'N/A'),
            '{date_of_leaving}'      => $dol_formatted,
            '{reason_for_leaving}'   => html_escape($extra['reason_for_leaving'] ?? 'Course Completed / Relocation'),
            '{attendance_summary}'   => html_escape($extra['attendance_summary'] ?? '188 / 205 Days (91.7%)'),
            '{conduct_statement}'    => html_escape($extra['conduct_statement'] ?? 'Exemplary and Good'),
        );

        return strtr($body_template, $replacements);
    }

    /**
     * Reissue Certificate with version tracking
     */
    public function reissue_certificate($certificate_id, $reason, $user_id, $new_content = null)
    {
        $cert = $this->get_by_id($certificate_id);
        if (!$cert) return false;

        // Archive previous version in tbl_certificate_versions
        $version_data = array(
            'certificate_id'   => $cert->certificate_id,
            'version_number'   => $cert->version,
            'certificate_no'   => $cert->certificate_no,
            'content_snapshot' => $cert->generated_content,
            'reason'           => $reason,
            'changed_by'       => $user_id,
            'created_at'       => date('Y-m-d H:i:s')
        );
        $this->db->insert('tbl_certificate_versions', $version_data);

        // Update certificate
        $update_data = array(
            'version'        => $cert->version + 1,
            'is_reissued'    => 1,
            'reissue_reason' => $reason,
            'status'         => 'Generated',
            'updated_at'     => date('Y-m-d H:i:s')
        );
        if ($new_content !== null) {
            $update_data['generated_content'] = $new_content;
        }

        $this->update($certificate_id, $update_data);
        $this->Certificate_setting_model->log_audit('Certificate Reissued', 'Certificate', $certificate_id, "Reissued version {$update_data['version']}: {$reason}", $user_id);
        return true;
    }

    public function get_versions($certificate_id)
    {
        return $this->db->select('cv.*, u.username as changer_name')
            ->from('tbl_certificate_versions cv')
            ->join('tbl_users u', 'u.user_id = cv.changed_by', 'left')
            ->where('cv.certificate_id', $certificate_id)
            ->order_by('cv.version_number', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Dashboard Statistics
     */
    public function get_dashboard_stats($year_id = NULL)
    {
        $year_id = $year_id ? (int)$year_id : get_current_academic_year_id();

        $total_certificates = $this->db->where('academic_year_id', $year_id)->where('is_deleted', 'n')->count_all_results($this->table);
        $pending_requests   = $this->db->where('academic_year_id', $year_id)->where_in('status', array('Pending', 'Under Verification'))->where('is_deleted', 'n')->count_all_results('tbl_certificate_requests');
        $approved_requests  = $this->db->where('academic_year_id', $year_id)->where('status', 'Approved')->where('is_deleted', 'n')->count_all_results('tbl_certificate_requests');
        $generated_certs    = $this->db->where('academic_year_id', $year_id)->where_in('status', array('Generated', 'Printed', 'Issued'))->where('is_deleted', 'n')->count_all_results($this->table);
        $printed_certs      = $this->db->where('academic_year_id', $year_id)->where_in('status', array('Printed', 'Issued'))->where('is_deleted', 'n')->count_all_results($this->table);
        $issued_certs       = $this->db->where('academic_year_id', $year_id)->where('status', 'Issued')->where('is_deleted', 'n')->count_all_results($this->table);

        $total_docs         = $this->db->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_student_documents');
        $pending_docs       = $this->db->where('verification_status', 'Pending')->where('status', 1)->where('is_deleted', 'n')->count_all_results('tbl_student_documents');

        // Type breakdowns
        $bonafide_count = $this->db->where('academic_year_id', $year_id)->like('certificate_type', 'Bonafide')->where('is_deleted', 'n')->count_all_results($this->table);
        $tc_count       = $this->db->where('academic_year_id', $year_id)->group_start()->like('certificate_type', 'Transfer')->or_like('certificate_type', 'TC')->group_end()->where('is_deleted', 'n')->count_all_results($this->table);
        $study_count    = $this->db->where('academic_year_id', $year_id)->like('certificate_type', 'Study')->where('is_deleted', 'n')->count_all_results($this->table);
        $conduct_count  = $this->db->where('academic_year_id', $year_id)->group_start()->like('certificate_type', 'Conduct')->or_like('certificate_type', 'Character')->group_end()->where('is_deleted', 'n')->count_all_results($this->table);

        return (object) array(
            'total_certificates' => $total_certificates,
            'pending_requests'   => $pending_requests,
            'approved_requests'  => $approved_requests,
            'generated_certs'    => $generated_certs,
            'printed_certs'      => $printed_certs,
            'issued_certs'       => $issued_certs,
            'total_docs'         => $total_docs,
            'pending_docs'       => $pending_docs,
            'bonafide_count'     => $bonafide_count,
            'tc_count'           => $tc_count,
            'study_count'        => $study_count,
            'conduct_count'      => $conduct_count,
        );
    }
}
