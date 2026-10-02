<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Attendance_pdf_service
 *
 * Server-side Student Attendance PDF generation engine powered by mPDF.
 * Handles single-page and multi-page reports, running headers, dynamic footers,
 * localized Asia/Kolkata timestamps, school branding, and period/day attendance modes.
 */
class Attendance_pdf_service {

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
     * Generate and output or return the attendance report PDF.
     *
     * @param object $student Student profile object (with attendance property)
     * @param object $settings School settings
     * @param object $academic_year Active or selected academic year
     * @param string $from_date Start date (Y-m-d)
     * @param string $to_date End date (Y-m-d)
     * @param string $output_dest 'I' for inline stream, 'D' for download, 'S' for string
     * @param string $custom_filename Optional override filename
     * @return string|void
     */
    public function generate($student, $settings, $academic_year, $from_date, $to_date, $output_dest = 'D', $custom_filename = '')
    {
        try {
            $target_school_id = !empty($student->school_id) ? (int)$student->school_id : get_current_school_id();
            if (empty($settings) || empty($settings->school_name) || (isset($settings->school_id) && (int)$settings->school_id !== $target_school_id)) {
                $this->CI->load->model('Setting_model');
                $settings = $this->CI->Setting_model->get_settings($target_school_id);
            }

            // 1. Prepare School Information
            $school_name = !empty($settings->school_name) ? $settings->school_name : 'School Management System';

            $address_parts = array();
            if (!empty($settings->address))  $address_parts[] = $settings->address;
            if (!empty($settings->city))     $address_parts[] = $settings->city;
            if (!empty($settings->state))    $address_parts[] = $settings->state;
            if (!empty($settings->pincode))  $address_parts[] = $settings->pincode;
            $school_address = implode(', ', $address_parts);

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

            // 3. Academic Year & Attendance Period Metadata
            $academic_year_name = $academic_year ? $academic_year->year_name : (isset($student->year_name) && $student->year_name !== '' ? $student->year_name : (get_active_academic_year(TRUE)->year_name ?? '—'));
            $attendance_period = date('d M Y', strtotime($from_date)) . ' to ' . date('d M Y', strtotime($to_date));

            // 4. Centralized Asia/Kolkata Timezone Timestamp
            $tz = new DateTimeZone('Asia/Kolkata');
            $now = new DateTime('now', $tz);
            $generated_at = $now->format('d M Y, h:i A');

            // 5. Student Details Preparation
            $full_name = school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name);
            $divName = !empty($student->division_name) ? $student->division_name : (!empty($student->section_name) ? $student->section_name : '—');
            $className = trim(($student->class_name ?? '') . ' ' . $divName);
            $groupName = !empty($student->academic_group_name) ? $student->academic_group_name : '—';
            $guardian_str = trim(($student->guardian_name ?: '—') . ($student->guardian_relation ? ' (' . $student->guardian_relation . ')' : ''));

            // Student Photo Resolution — use local filesystem path (not base64) to keep HTML small
            $student_photo_src = '';
            if (!empty($student->photo)) {
                $photo_path = FCPATH . 'uploads/students/' . $student->photo;
                if (file_exists($photo_path)) {
                    $student_photo_src = $photo_path;
                }
            }

            // 6. View Data Assembly
            $view_data = array(
                'student'            => $student,
                'student_name'       => $full_name,
                'admission_number'   => $student->admission_number ?? '—',
                'roll_number'        => $student->roll_number ?? '',
                'gender'             => $student->gender ?? '',
                'class_name'         => $className,
                'division_name'      => $divName,
                'group_name'         => $groupName,
                'guardian_display'   => $guardian_str,
                'contact_phone'      => $student->guardian_phone ?? '',
                'student_active'     => (isset($student->status) && $student->status == 1),
                'student_photo_src'  => $student_photo_src,
                'school_name'        => $school_name,
                'school_address'     => $school_address,
                'school_contact'     => $school_contact,
                'school_logo_src'    => $school_logo_src,
                'academic_year_name' => $academic_year_name,
                'attendance_period'  => $attendance_period,
                'generated_at'       => $generated_at,
            );

            // Document Design Resolution (School-specific / Default fallback)
            $this->CI->load->library('document_design_service');
            $design = $this->CI->document_design_service->resolve_design($target_school_id, 'attendance_report');
            $view_data['document_design'] = $design;

            // Calculate exact mPDF page margins dynamically based on resolved header/footer dimensions and safe clearance
            $margins = $this->CI->document_design_service->calculate_mpdf_margins($design, 210, 297, 'P');

            // 7. Render HTML Template
            $html = $this->CI->load->view('pages/students/attendance_pdf_mpdf', $view_data, true);

            // 8. Configure mPDF Instance
            $temp_dir = sys_get_temp_dir() . '/mpdf';
            if (!is_dir($temp_dir)) {
                @mkdir($temp_dir, 0777, true);
            }

            $has_custom_header = $margins['has_header'];
            $has_custom_footer = $margins['has_footer'];

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

            $mpdf = new \Mpdf\Mpdf($mpdf_config);
            $mpdf->setAutoTopMargin = 'stretch';
            $mpdf->setAutoBottomMargin = 'stretch';
            $mpdf->SetTitle($full_name . ' - Attendance Report');
            $mpdf->SetAuthor($school_name);
            $mpdf->SetCreator('Login2 School Management System');

            // Running Header (shows from page 2 onward or header image)
            if ($has_custom_header) {
                $running_header = '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                    <img src="' . htmlspecialchars($view_data['document_design']->header_path, ENT_QUOTES, 'UTF-8') . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
                </div>';
            } else {
                $running_header = '
                <div style="margin: 0 10mm;">
                    <table width="100%" style="border-bottom: 0.3mm solid #cbd5e1; font-size: 8pt; color: #64748b; padding-bottom: 2mm; font-family: dejavusans, sans-serif;">
                        <tr>
                            <td align="left" style="font-weight: bold;">' . html_escape($school_name) . ' | Student Attendance Report</td>
                            <td align="right" style="font-weight: bold;">' . html_escape($full_name) . ' (' . html_escape($student->admission_number ?? '') . ')</td>
                        </tr>
                    </table>
                </div>';
            }

            // Document Footer (all pages)
            if ($has_custom_footer) {
                $footer = '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                    <img src="' . htmlspecialchars($view_data['document_design']->footer_path, ENT_QUOTES, 'UTF-8') . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
                </div>';
            } else {
                $footer = '
                <div style="margin: 0 10mm;">
                    <table width="100%" style="border-top: 0.25mm solid #cbd5e1; font-size: 7.5pt; color: #64748b; padding-top: 2mm; font-family: dejavusans, sans-serif;">
                        <tr>
                            <td width="35%" align="left">Official Student Attendance Record</td>
                            <td width="40%" align="center">Generated on: ' . html_escape($generated_at) . ' IST</td>
                            <td width="25%" align="right">Page {PAGENO} of {nbpg}</td>
                        </tr>
                    </table>
                </div>';
            }

            // Raise pcre.backtrack_limit for the duration of this request so mPDF can parse large HTML
            $orig_backtrack_limit = (int)ini_get('pcre.backtrack_limit');
            if ($orig_backtrack_limit < 5000000) {
                @ini_set('pcre.backtrack_limit', 5000000);
            }

            $mpdf->DefHTMLHeaderByName('runningHeader', $running_header);
            $mpdf->SetHTMLFooter($footer);

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

            // 9. Determine Output Filename
            if (empty($custom_filename)) {
                $clean_name = preg_replace('/[^A-Za-z0-9_]/', '_', str_replace(' ', '_', $full_name));
                $clean_year = preg_replace('/[^A-Za-z0-9_-]/', '_', $academic_year_name);
                $custom_filename = "{$clean_name}_Attendance_Report_{$clean_year}.pdf";
            }

            // 10. Send Output
            if ($output_dest === 'S') {
                return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            } elseif ($output_dest === 'I') {
                return $mpdf->Output($custom_filename, \Mpdf\Output\Destination::INLINE);
            } else {
                return $mpdf->Output($custom_filename, \Mpdf\Output\Destination::DOWNLOAD);
            }

        } catch (Exception $e) {
            log_message('error', 'Attendance_pdf_service generation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($output_dest === 'S') {
                return '';
            }
            show_error('An error occurred while generating the student attendance PDF report. Please contact the system administrator.', 500, 'PDF Generation Error');
        }
    }
}
