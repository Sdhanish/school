<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Overall_report_pdf_service
 *
 * Handles professional mPDF generation for the Student Overall Academic Report.
 */
class Overall_report_pdf_service {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Ensure Composer autoloader is initialized
        if (!class_exists('\Mpdf\Mpdf')) {
            $autoload_path = FCPATH . 'vendor/autoload.php';
            if (file_exists($autoload_path)) {
                require_once $autoload_path;
            }
        }
    }

    /**
     * Generate mPDF Student Overall Report
     *
     * @param object $student
     * @param object $settings
     * @param object $academic_year
     * @param array  $exam_reports
     * @param object $attendance_data
     * @param object $fee_summary
     * @param string $teacher_name
     * @param string $principal_name
     * @param string $output_dest 'D' (Download), 'I' (Inline), 'S' (String)
     * @param string $custom_filename
     * @return string|void
     */
    public function generate(
        $student,
        $settings,
        $academic_year,
        $exam_reports,
        $attendance_data,
        $fee_summary,
        $teacher_name = '',
        $principal_name = '',
        $output_dest = 'D',
        $custom_filename = ''
    ) {
        try {
            $target_school_id = !empty($student->school_id) ? (int)$student->school_id : get_current_school_id();
            if (empty($settings) || empty($settings->school_name) || (isset($settings->school_id) && (int)$settings->school_id !== $target_school_id)) {
                $this->CI->load->model('Setting_model');
                $settings = $this->CI->Setting_model->get_settings($target_school_id);
            }

            // 1. School Information & Address
            $school_name = !empty($settings->school_name) ? $settings->school_name : 'School Management System';
            $school_address = !empty($settings->address) ? $settings->address : '';

            $contact_parts = array();
            if (!empty($settings->phone))    $contact_parts[] = 'Phone: ' . $settings->phone;
            if (!empty($settings->email))    $contact_parts[] = 'Email: ' . $settings->email;
            if (!empty($settings->website))  $contact_parts[] = 'Web: ' . $settings->website;
            $school_contact = implode(' | ', $contact_parts);

            // 2. School Logo Resolution — use local filesystem path (not base64) to keep HTML small
            $school_logo_src = '';
            $logo_path = '';
            if (!empty($settings->logo) && file_exists(FCPATH . 'uploads/schools/' . $settings->logo)) {
                $logo_path = FCPATH . 'uploads/schools/' . $settings->logo;
            } elseif (!empty($settings->logo) && file_exists(FCPATH . 'uploads/settings/' . $settings->logo)) {
                $logo_path = FCPATH . 'uploads/settings/' . $settings->logo;
            } elseif (!empty($settings->school_logo) && file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo)) {
                $logo_path = FCPATH . 'uploads/settings/' . $settings->school_logo;
            } elseif (!empty($settings->logo) && file_exists(FCPATH . 'uploads/id_card/' . $settings->logo)) {
                $logo_path = FCPATH . 'uploads/id_card/' . $settings->logo;
            } elseif (file_exists(FCPATH . 'assets/logo.png')) {
                $logo_path = FCPATH . 'assets/logo.png';
            }
            // Pass the absolute path directly — mPDF resolves local paths natively
            if (!empty($logo_path) && file_exists($logo_path)) {
                $school_logo_src = $logo_path;
            }

            // 3. Centralized Asia/Kolkata Timezone Timestamp
            $tz = new DateTimeZone('Asia/Kolkata');
            $now = new DateTime('now', $tz);
            $generated_at = $now->format('d M Y, h:i A');

            // 4. Student Name & Details
            $full_name = school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name);
            $divName = !empty($student->division_name) ? $student->division_name : (!empty($student->section_name) ? $student->section_name : '—');
            $className = trim(($student->class_name ?? '') . ' ' . $divName);
            $groupName = !empty($student->academic_group_name) ? $student->academic_group_name : (!empty($student->group_name) ? $student->group_name : '—');
            $academic_year_name = $academic_year ? $academic_year->year_name : (isset($student->year_name) && $student->year_name !== '' ? $student->year_name : (get_active_academic_year(TRUE)->year_name ?? '—'));

            // Student Photo Resolution — use local filesystem path (not base64) to keep HTML small
            $student_photo_src = '';
            if (!empty($student->photo)) {
                $photo_path = FCPATH . 'uploads/students/' . $student->photo;
                if (file_exists($photo_path)) {
                    $student_photo_src = $photo_path;
                }
            }

            // Initials for fallback photo placeholder
            $nameParts = explode(' ', trim($full_name));
            $initials = '';
            foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
            if (strlen($initials) > 2) $initials = substr($initials, 0, 2);

            // 5. Prepare View Data
            $view_data = array(
                'student'            => $student,
                'student_name'       => $full_name,
                'initials'           => $initials,
                'admission_number'   => $student->admission_number ?? '—',
                'admission_date'     => !empty($student->created_at) ? date('d M Y', strtotime($student->created_at)) : '—',
                'roll_number'        => $student->roll_number ?? '—',
                'gender'             => $student->gender ?? '—',
                'date_of_birth'      => !empty($student->date_of_birth) ? date('d M Y', strtotime($student->date_of_birth)) : '—',
                'blood_group'        => $student->blood_group ?? '—',
                'student_phone'      => $student->student_phone ?? '—',
                'student_email'      => $student->student_email ?? '—',
                'student_address'    => $student->address ?? '—',
                'class_name'         => $className,
                'division_name'      => $divName,
                'group_name'         => $groupName,
                'academic_year_name' => $academic_year_name,
                'status_label'       => (isset($student->status) && $student->status == 1) ? 'Active' : 'Inactive / Transferred',
                'guardian_name'      => $student->guardian_name ?? '—',
                'guardian_relation'  => $student->guardian_relation ?? '—',
                'guardian_phone'     => $student->guardian_phone ?? '—',
                'guardian_email'     => $student->guardian_email ?? '—',
                'parent_address'     => !empty($student->address) ? $student->address : '—',
                'student_photo_src'  => $student_photo_src,
                'school_name'        => $school_name,
                'school_address'     => $school_address,
                'school_contact'     => $school_contact,
                'school_logo_src'    => $school_logo_src,
                'exam_reports'       => $exam_reports,
                'attendance_data'    => $attendance_data,
                'fee_summary'        => $fee_summary,
                'teacher_name'       => $teacher_name,
                'principal_name'     => $principal_name ?: (!empty($settings->principal_name) ? $settings->principal_name : 'Principal / Head'),
                'generated_at'       => $generated_at,
            );

            // Document Design Resolution (School-specific / Default fallback)
            $this->CI->load->library('document_design_service');
            $design = $this->CI->document_design_service->resolve_design($target_school_id, 'overall_report');
            $view_data['document_design'] = $design;

            // Calculate exact mPDF page margins dynamically based on resolved header/footer dimensions and safe clearance
            $margins = $this->CI->document_design_service->calculate_mpdf_margins($design, 210, 297, 'P');

            // 6. Render View HTML
            $html = $this->CI->load->view('pages/students/overall_report_mpdf', $view_data, true);

            // 7. Configure mPDF Instance
            $temp_dir = sys_get_temp_dir() . '/mpdf';
            if (!is_dir($temp_dir)) {
                @mkdir($temp_dir, 0777, true);
            }

            $mpdf_config = array(
                'mode'              => 'utf-8',
                'format'            => 'A4',
                'orientation'       => 'P',
                'margin_left'       => $margins['margin_left'],
                'margin_right'      => $margins['margin_right'],
                'margin_top'        => $margins['margin_top'],
                'margin_bottom'     => $margins['margin_bottom'],
                'margin_header'     => $margins['margin_header'],
                'margin_footer'     => $margins['margin_footer'],
                'autoScriptToLang'  => true,
                'autoLangToFont'    => true,
                'tempDir'           => $temp_dir,
                'default_font'      => 'dejavusans',
            );

            // Raise pcre.backtrack_limit for the duration of this request so mPDF can parse large HTML
            $orig_backtrack_limit = (int)ini_get('pcre.backtrack_limit');
            if ($orig_backtrack_limit < 5000000) {
                @ini_set('pcre.backtrack_limit', 5000000);
            }

            $mpdf = new \Mpdf\Mpdf($mpdf_config);
            $mpdf->setAutoTopMargin = 'stretch';
            $mpdf->setAutoBottomMargin = 'stretch';
            $mpdf->SetTitle($full_name . ' - Overall Academic Report');
            $mpdf->SetAuthor($school_name);
            $mpdf->SetCreator('Login2 School Management System');

            // Set running header and footer programmatically before parsing body content
            if ($margins['has_header']) {
                $header_html = '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                    <img src="' . htmlspecialchars($design->header_path, ENT_QUOTES, 'UTF-8') . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
                </div>';
                $mpdf->DefHTMLHeaderByName('globalHeader', $header_html);
                $mpdf->SetHTMLHeaderByName('globalHeader');
            }

            if ($margins['has_footer']) {
                $footer_html = '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                    <img src="' . htmlspecialchars($design->footer_path, ENT_QUOTES, 'UTF-8') . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
                </div>';
                $mpdf->DefHTMLFooterByName('globalFooter', $footer_html);
                $mpdf->SetHTMLFooterByName('globalFooter');
            }

            // Split WriteHTML into stylesheet + body so each chunk stays small
            if (preg_match('/<style[^>]*>(.*?)<\/style>/si', $html, $css_m)) {
                $mpdf->WriteHTML('<style>' . $css_m[1] . '</style>', \Mpdf\HTMLParserMode::HEADER_CSS);
            }
            $body_html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
            $mpdf->WriteHTML($body_html, \Mpdf\HTMLParserMode::HTML_BODY);

            // Restore original limit
            if ($orig_backtrack_limit < 5000000) {
                @ini_set('pcre.backtrack_limit', $orig_backtrack_limit);
            }

            // 8. Output Filename: Student_Overall_Report_<AdmissionNumber>_<AcademicYear>.pdf
            if (empty($custom_filename)) {
                $clean_adm = preg_replace('/[^A-Za-z0-9_-]/', '_', $student->admission_number ?? 'Student');
                $clean_year = preg_replace('/[^A-Za-z0-9_-]/', '_', $academic_year_name);
                $custom_filename = "Student_Overall_Report_{$clean_adm}_{$clean_year}.pdf";
            }

            // 9. Send Output
            if ($output_dest === 'S') {
                return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            } elseif ($output_dest === 'I') {
                return $mpdf->Output($custom_filename, \Mpdf\Output\Destination::INLINE);
            } else {
                return $mpdf->Output($custom_filename, \Mpdf\Output\Destination::DOWNLOAD);
            }
        } catch (Exception $e) {
            log_message('error', 'Overall_report_pdf_service generation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            show_error('Failed to generate Overall Report PDF: ' . $e->getMessage(), 500);
        }
    }
}
