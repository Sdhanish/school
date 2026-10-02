<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificates extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Certificate_model',
            'Certificate_type_model',
            'Certificate_template_model',
            'Certificate_request_model',
            'Student_document_model',
            'Document_category_model',
            'Certificate_setting_model',
            'Student_model',
            'Class_model',
            'Division_model',
            'Section_model',
            'Academic_year_model',
            'Setting_model'
        ));
    }

    private function get_current_user_id()
    {
        return $this->session->userdata('user_id') ?: 1;
    }

    /**
     * Submodule 1: Certificate Dashboard
     */
    public function index()
    {
        $this->require_permission('certificates.view');
        $year_id             = $this->academic_year_id;
        $stats               = $this->Certificate_model->get_dashboard_stats($year_id);
        $recent_certificates = $this->Certificate_model->get_all(array('academic_year_id' => $year_id));
        $recent_certificates = array_slice($recent_certificates, 0, 8);
        $pending_requests    = $this->Certificate_request_model->get_all(array('status' => 'Pending', 'academic_year_id' => $year_id));
        $pending_requests    = array_slice($pending_requests, 0, 5);

        $this->render('pages/certificates/dashboard', array(
            'title'               => 'Certificate Dashboard',
            'page_key'            => 'certificates-dashboard',
            'stats'               => $stats,
            'recent_certificates' => $recent_certificates,
            'pending_requests'    => $pending_requests,
        ));
    }

    /**
     * Submodule 2: Certificate Requests
     */
    public function requests()
    {
        $this->require_permission('certificates.view');
        $year_id = $this->input->get('academic_year_id', TRUE) ?: $this->academic_year_id;
        $filters = array(
            'academic_year_id'    => $year_id,
            'status'              => $this->input->get('status', TRUE),
            'certificate_type_id' => $this->input->get('certificate_type_id', TRUE),
            'class_id'            => $this->input->get('class_id', TRUE),
            'search'              => $this->input->get('search', TRUE),
        );

        $requests          = $this->Certificate_request_model->get_all($filters);
        $certificate_types = $this->Certificate_type_model->get_all('Active');
        $classes           = $this->Class_model->get_all($year_id);
        $students          = $this->Student_model->get_all(array('academic_year_id' => $year_id, 'status' => 1));

        $this->render('pages/certificates/requests', array(
            'title'             => 'Certificate Requests',
            'page_key'          => 'certificates-requests',
            'requests'          => $requests,
            'certificate_types' => $certificate_types,
            'classes'           => $classes,
            'students'          => $students,
            'filters'           => $filters,
        ));
    }

    /**
     * Create Certificate Request (Form Submit)
     */
    public function request_create()
    {
        $this->require_permission('certificates.generate');
        if ($this->input->method() === 'post') {
            $student_id          = (int)$this->input->post('student_id', TRUE);
            $certificate_type_id = (int)$this->input->post('certificate_type_id', TRUE);
            $reason              = trim($this->input->post('reason', TRUE));
            $required_date       = $this->input->post('required_date', TRUE) ?: null;
            $remarks             = trim($this->input->post('remarks', TRUE));

            $student = $this->Student_model->get_by_id($student_id);
            if (!$student) {
                $this->session->set_flashdata('error', 'Selected student record not found.');
                redirect('certificates/requests');
                return;
            }

            // File upload if supporting document attached
            $supporting_doc = null;
            if (!empty($_FILES['supporting_document']['name'])) {
                $config['upload_path']   = './uploads/certificates/';
                $config['allowed_types'] = 'pdf|jpg|jpeg|png';
                $config['max_size']      = 5120;
                $config['encrypt_name']  = TRUE;

                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0777, TRUE);
                }

                $this->load->library('upload', $config);
                if ($this->upload->do_upload('supporting_document')) {
                    $up_data        = $this->upload->data();
                    $supporting_doc = $up_data['file_name'];
                }
            }

            $req_data = array(
                'student_id'          => $student_id,
                'academic_year_id'    => $student->academic_year_id ?: $this->academic_year_id,
                'certificate_type_id' => $certificate_type_id,
                'reason'              => $reason,
                'requested_date'      => date('Y-m-d'),
                'required_date'       => $required_date,
                'remarks'             => $remarks,
                'supporting_document' => $supporting_doc,
                'status'              => 'Pending',
                'requested_by'        => $this->get_current_user_id()
            );

            $req_id = $this->Certificate_request_model->insert($req_data);
            $this->Certificate_setting_model->log_audit('Request Created', 'Certificate Request', $req_id, "Certificate request created for student #{$student_id}", $this->get_current_user_id());

            $this->session->set_flashdata('success', 'Certificate request submitted successfully.');
            redirect('certificates/requests');
        }
    }

    /**
     * Approve Request
     */
    public function approve_request($id)
    {
        $this->require_permission('certificates.generate');
        $req = $this->Certificate_request_model->get_by_id($id);
        if ($req) {
            $this->Certificate_request_model->update_status($id, 'Approved', null, $this->get_current_user_id());
            $this->Certificate_setting_model->log_audit('Request Approved', 'Certificate Request', $id, "Request approved by user #{$this->get_current_user_id()}", $this->get_current_user_id());
            $this->session->set_flashdata('success', 'Certificate request approved.');
        }
        redirect('certificates/requests');
    }

    /**
     * Reject Request
     */
    public function reject_request($id)
    {
        $this->require_permission('certificates.generate');
        $reason = $this->input->post('rejection_reason', TRUE) ?: 'Requirements not met.';
        $req = $this->Certificate_request_model->get_by_id($id);
        if ($req) {
            $this->Certificate_request_model->update_status($id, 'Rejected', $reason, $this->get_current_user_id());
            $this->Certificate_setting_model->log_audit('Request Rejected', 'Certificate Request', $id, "Reason: {$reason}", $this->get_current_user_id());
            $this->session->set_flashdata('success', 'Certificate request rejected.');
        }
        redirect('certificates/requests');
    }

    /**
     * Submodule 3: Certificate Types
     */
    public function types()
    {
        $this->require_permission('certificates.view');
        if ($this->input->method() === 'post') {
            $this->require_permission('certificates.generate');
            $name        = trim($this->input->post('type_name', TRUE));
            $code        = strtoupper(trim($this->input->post('type_code', TRUE)));
            $prefix      = strtoupper(trim($this->input->post('prefix', TRUE)));
            $description = trim($this->input->post('description', TRUE));

            $type_data = array(
                'type_name'   => $name,
                'type_code'   => $code,
                'prefix'      => $prefix,
                'description' => $description,
                'is_system'   => 0,
                'status'      => 'Active'
            );

            $this->Certificate_type_model->insert($type_data);
            $this->session->set_flashdata('success', 'Certificate type added successfully.');
            redirect('certificates/types');
            return;
        }

        $types = $this->Certificate_type_model->get_all();
        $this->render('pages/certificates/types', array(
            'title'    => 'Certificate Types',
            'page_key' => 'certificates-types',
            'types'    => $types,
        ));
    }

    /**
     * Submodule 4: Bonafide Certificate
     */
    public function bonafide()
    {
        $this->require_permission('certificates.view');
        $certificates = $this->Certificate_model->get_all(array('type_code' => 'BONAFIDE', 'academic_year_id' => $this->academic_year_id));
        $students     = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id, 'status' => 1));

        $this->render('pages/certificates/bonafide', array(
            'title'        => 'Bonafide Certificates',
            'page_key'     => 'certificates-bonafide',
            'certificates' => $certificates,
            'students'     => $students,
        ));
    }

    /**
     * Submodule 5: Transfer Certificate
     */
    public function transfer_certificate()
    {
        $this->require_permission('certificates.view');
        $certificates = $this->Certificate_model->get_all(array('type_code' => 'TC', 'academic_year_id' => $this->academic_year_id));
        $students     = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id, 'status' => 1));
        $settings     = $this->Certificate_setting_model->get_settings();

        $this->render('pages/certificates/transfer_certificate', array(
            'title'        => 'Transfer Certificates',
            'page_key'     => 'certificates-transfer',
            'certificates' => $certificates,
            'students'     => $students,
            'settings'     => $settings,
        ));
    }

    /**
     * Submodule 6: Study Certificate
     */
    public function study_certificate()
    {
        $this->require_permission('certificates.view');
        $certificates = $this->Certificate_model->get_all(array('type_code' => 'STUDY', 'academic_year_id' => $this->academic_year_id));
        $students     = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id, 'status' => 1));

        $this->render('pages/certificates/study_certificate', array(
            'title'        => 'Study Certificates',
            'page_key'     => 'certificates-study',
            'certificates' => $certificates,
            'students'     => $students,
        ));
    }

    /**
     * Submodule 7: Conduct Certificate
     */
    public function conduct_certificate()
    {
        $this->require_permission('certificates.view');
        $certificates = $this->Certificate_model->get_all(array('type_code' => 'CONDUCT', 'academic_year_id' => $this->academic_year_id));
        $students     = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id, 'status' => 1));

        $this->render('pages/certificates/conduct_certificate', array(
            'title'        => 'Conduct Certificates',
            'page_key'     => 'certificates-conduct',
            'certificates' => $certificates,
            'students'     => $students,
        ));
    }

    /**
     * Submodule 8: Certificate Generation Engine
     */
    public function generate($req_or_stud_id = null)
    {
        $this->require_permission('certificates.generate');
        if ($this->input->method() === 'post') {
            $student_id          = (int)$this->input->post('student_id', TRUE);
            $certificate_type_id = (int)$this->input->post('certificate_type_id', TRUE);
            $template_id         = (int)$this->input->post('template_id', TRUE);
            $issue_date          = $this->input->post('issue_date', TRUE) ?: date('Y-m-d');
            $remarks             = trim($this->input->post('remarks', TRUE));
            $request_id          = $this->input->post('request_id', TRUE) ? (int)$this->input->post('request_id', TRUE) : null;

            // Extra custom fields for TC, Conduct, Study
            $extra = array(
                'date_of_leaving'    => $this->input->post('date_of_leaving', TRUE),
                'reason_for_leaving' => $this->input->post('reason_for_leaving', TRUE),
                'attendance_summary' => $this->input->post('attendance_summary', TRUE),
                'conduct_statement'  => $this->input->post('conduct_statement', TRUE),
            );

            $student = $this->Student_model->get_by_id($student_id);
            $type    = $this->Certificate_type_model->get_by_id($certificate_type_id);
            $tmpl    = $template_id ? $this->Certificate_template_model->get_by_id($template_id) : $this->Certificate_template_model->get_by_type_code($type->type_code);
            $school  = $this->Setting_model->get_settings();

            if (!$student || !$type || !$tmpl) {
                $this->session->set_flashdata('error', 'Required student, certificate type, or template record is missing.');
                redirect('certificates/dashboard');
                return;
            }

            // Generate unique certificate number
            $cert_no = $this->Certificate_model->generate_certificate_number($type->type_code, date('Y', strtotime($issue_date)));

            // Compile template content
            $compiled_body = $this->Certificate_model->compile_certificate_content($tmpl->body_content, $student, $school, $cert_no, $issue_date, $extra);

            $cert_data = array(
                'certificate_no'        => $cert_no,
                'request_id'            => $request_id,
                'student_id'            => $student_id,
                'academic_year_id'      => $student->academic_year_id ?: $this->academic_year_id,
                'certificate_type_id'   => $certificate_type_id,
                'certificate_type'      => $type->type_name,
                'template_id'           => $tmpl->template_id,
                'issue_date'            => $issue_date,
                'student_data_snapshot' => json_encode(array(
                    'student_name'     => $student->first_name . ' ' . $student->last_name,
                    'admission_number' => $student->admission_number,
                    'class_name'       => $student->class_name,
                    'division_name'     => $student->section_name,
                    'dob'              => $student->date_of_birth,
                    'parent_name'      => $student->guardian_name
                )),
                'generated_content'     => $compiled_body,
                'version'               => 1,
                'remarks'               => $remarks,
                'generated_by'          => $this->get_current_user_id(),
                'status'                => 'Generated'
            );

            $cert_id = $this->Certificate_model->insert($cert_data);

            if ($request_id) {
                $this->Certificate_request_model->update_status($request_id, 'Generated', null, $this->get_current_user_id());
            }

            $this->Certificate_setting_model->log_audit('Certificate Generated', 'Certificate', $cert_id, "Certificate {$cert_no} generated for student #{$student_id}", $this->get_current_user_id());

            $this->session->set_flashdata('success', "Certificate {$cert_no} generated successfully.");
            redirect('certificates/preview/' . $cert_id);
            return;
        }

        // Form View
        $types     = $this->Certificate_type_model->get_all('Active');
        $templates = $this->Certificate_template_model->get_all('Active');
        $students  = $this->Student_model->get_all(array('academic_year_id' => $this->academic_year_id, 'status' => 1));
        $selected_student_id = null;
        $selected_type_id = null;
        $request = null;

        if ($req_or_stud_id) {
            $request = $this->Certificate_request_model->get_by_id($req_or_stud_id);
            if ($request) {
                $selected_student_id = $request->student_id;
                $selected_type_id    = $request->certificate_type_id;
            }
        }

        $this->render('pages/certificates/generate', array(
            'title'               => 'Generate Certificate',
            'page_key'            => 'certificates-generate',
            'types'               => $types,
            'templates'           => $templates,
            'students'            => $students,
            'request'             => $request,
            'selected_student_id' => $selected_student_id,
            'selected_type_id'    => $selected_type_id,
        ));
    }

    /**
     * Submodule 9: Certificate Preview Layout
     */
    public function preview($certificate_id)
    {
        $this->require_permission('certificates.view');
        $cert = $this->Certificate_model->get_by_id($certificate_id);
        if (!$cert) {
            $this->session->set_flashdata('error', 'Certificate not found.');
            redirect('certificates/dashboard');
            return;
        }

        $school   = $this->Setting_model->get_settings();
        $settings = $this->Certificate_setting_model->get_settings();
        $versions = $this->Certificate_model->get_versions($certificate_id);

        $this->render('pages/certificates/preview', array(
            'title'        => 'Certificate Preview - ' . $cert->certificate_no,
            'page_key'     => 'certificates-preview',
            'cert'         => $cert,
            'school'       => $school,
            'settings'     => $settings,
            'versions'     => $versions,
        ));
    }

    /**
     * Print View (Clean standalone template)
     */
    public function print_cert($certificate_id)
    {
        $this->require_permission('certificates.view');
        $cert = $this->Certificate_model->get_by_id($certificate_id);
        if (!$cert) {
            show_404();
            return;
        }

        // Mark as Printed if not issued
        if ($cert->status === 'Generated') {
            $this->Certificate_model->update($certificate_id, array('status' => 'Printed'));
        }

        $school   = $this->Setting_model->get_settings();
        $settings = $this->Certificate_setting_model->get_settings();

        $this->load->library('document_design_service');
        $target_school_id = !empty($cert->school_id) ? (int)$cert->school_id : $this->school_id;
        $document_design = $this->document_design_service->resolve_design($target_school_id, 'certificate');

        $this->load->view('pages/certificates/print', array(
            'cert'            => $cert,
            'school'          => $school,
            'settings'        => $settings,
            'document_design' => $document_design,
        ));
    }

    /**
     * Mark as Issued
     */
    public function issue($certificate_id)
    {
        $this->require_permission('certificates.generate');
        $cert = $this->Certificate_model->get_by_id($certificate_id);
        if ($cert) {
            $this->Certificate_model->update($certificate_id, array(
                'status'    => 'Issued',
                'issued_by' => $this->get_current_user_id()
            ));
            if ($cert->request_id) {
                $this->Certificate_request_model->update_status($cert->request_id, 'Issued', null, $this->get_current_user_id());
            }
            $this->Certificate_setting_model->log_audit('Certificate Issued', 'Certificate', $certificate_id, "Certificate {$cert->certificate_no} issued to student", $this->get_current_user_id());
            $this->session->set_flashdata('success', "Certificate {$cert->certificate_no} marked as Issued.");
        }
        redirect('certificates/preview/' . $certificate_id);
    }

    /**
     * Submodule 10: Certificate Templates
     */
    public function templates()
    {
        $this->require_permission('certificates.view');
        if ($this->input->method() === 'post') {
            $this->require_permission('certificates.generate');
            $name        = trim($this->input->post('template_name', TRUE));
            $type_code   = trim($this->input->post('type_code', TRUE));
            $header      = trim($this->input->post('header_content', TRUE));
            $body        = $this->input->post('body_content', FALSE); // Allow HTML
            $footer      = trim($this->input->post('footer_content', TRUE));
            $logo_pos    = $this->input->post('logo_position', TRUE) ?: 'Top-Center';
            $sig_layout  = $this->input->post('signature_layout', TRUE) ?: 'Principal-Only';

            // Validate variables
            $invalid_vars = $this->Certificate_template_model->validate_template_variables($body);
            if (!empty($invalid_vars)) {
                $this->session->set_flashdata('error', 'Template contains invalid variables: ' . implode(', ', $invalid_vars));
                redirect('certificates/templates');
                return;
            }

            $tmpl_data = array(
                'template_name'    => $name,
                'type_code'        => $type_code,
                'header_content'   => $header,
                'body_content'     => $body,
                'footer_content'   => $footer,
                'logo_position'    => $logo_pos,
                'signature_layout' => $sig_layout,
                'status'           => 'Active'
            );

            $this->Certificate_template_model->insert($tmpl_data);
            $this->session->set_flashdata('success', 'Certificate template created.');
            redirect('certificates/templates');
            return;
        }

        $templates = $this->Certificate_template_model->get_all();
        $types     = $this->Certificate_type_model->get_all('Active');
        $vars      = $this->Certificate_template_model->supported_variables;

        $this->render('pages/certificates/templates', array(
            'title'     => 'Certificate Templates',
            'page_key'  => 'certificates-templates',
            'templates' => $templates,
            'types'     => $types,
            'vars'      => $vars,
        ));
    }

    /**
     * Submodule 11: Student Documents Directory & Upload
     */
    public function documents()
    {
        $this->require_permission('certificates.view');
        $filters = array(
            'student_id'          => $this->input->get('student_id', TRUE),
            'category_id'         => $this->input->get('category_id', TRUE),
            'verification_status' => $this->input->get('verification_status', TRUE),
            'search'              => $this->input->get('search', TRUE),
        );

        if ($this->input->method() === 'post') {
            $this->require_permission('certificates.verify_docs');
            $student_id   = (int)$this->input->post('student_id', TRUE);
            $category_id  = (int)$this->input->post('category_id', TRUE);
            $doc_name     = trim($this->input->post('document_name', TRUE));
            $doc_number   = trim($this->input->post('document_number', TRUE));
            $issue_date   = $this->input->post('issue_date', TRUE) ?: null;
            $expiry_date  = $this->input->post('expiry_date', TRUE) ?: null;
            $remarks      = trim($this->input->post('remarks', TRUE));

            $file_path = '';
            if (!empty($_FILES['document_file']['name'])) {
                $config['upload_path']   = './uploads/student_documents/';
                $config['allowed_types'] = 'pdf|jpg|jpeg|png|doc|docx';
                $config['max_size']      = 10240;
                $config['encrypt_name']  = TRUE;

                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0777, TRUE);
                }

                $this->load->library('upload', $config);
                if ($this->upload->do_upload('document_file')) {
                    $up_data   = $this->upload->data();
                    $file_path = $up_data['file_name'];
                }
            }

            $cat = $this->Document_category_model->get_by_id($category_id);

            $doc_data = array(
                'student_id'          => $student_id,
                'category_id'         => $category_id,
                'document_type'       => $cat ? $cat->category_name : 'General Document',
                'document_name'       => $doc_name,
                'document_number'     => $doc_number,
                'issue_date'          => $issue_date,
                'expiry_date'         => $expiry_date,
                'file_path'           => $file_path,
                'remarks'             => $remarks,
                'verification_status' => 'Pending',
                'status'              => 1
            );

            $doc_id = $this->Student_document_model->insert($doc_data);
            $this->Certificate_setting_model->log_audit('Document Uploaded', 'Student Document', $doc_id, "Document {$doc_name} uploaded for student #{$student_id}", $this->get_current_user_id());

            $this->session->set_flashdata('success', 'Student document uploaded successfully.');
            redirect('certificates/documents');
            return;
        }

        $categories      = $this->Document_category_model->get_all('Active');
        $students        = $this->Student_model->get_all();
        $total_documents = $this->Student_document_model->get_datatables_count_all($this->school_id);

        $this->render('pages/certificates/documents', array(
            'title'           => 'Student Documents',
            'page_key'        => 'certificates-documents',
            'categories'      => $categories,
            'students'        => $students,
            'total_documents' => $total_documents,
            'filters'         => $filters,
        ));
    }

    /**
     * AJAX Server-Side DataTables endpoint for Student Documents
     */
    public function ajax_documents_list()
    {
        $this->require_permission('certificates.view');

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length < 1 || $length > 100) {
            $length = 25;
        }

        $search = $this->input->post('search');
        $search_val = !empty($search['value']) ? trim($search['value']) : '';

        $order = $this->input->post('order');
        $order_col = !empty($order[0]['column']) ? (int)$order[0]['column'] : 6;
        $order_dir = !empty($order[0]['dir']) ? $order[0]['dir'] : 'desc';

        $category_id = $this->input->post('category_id');
        $verification_status = $this->input->post('verification_status');
        $student_id = $this->input->post('student_id');

        $filters = array(
            'school_id' => $this->school_id,
            'search'    => $search_val,
        );
        if (!empty($category_id)) {
            $filters['category_id'] = (int)$category_id;
        }
        if (!empty($verification_status)) {
            $filters['verification_status'] = $verification_status;
        }
        if (!empty($student_id)) {
            $filters['student_id'] = (int)$student_id;
        }

        $records_total    = $this->Student_document_model->get_datatables_count_all($this->school_id);
        $records_filtered = $this->Student_document_model->count_filtered($filters);
        $docs             = $this->Student_document_model->get_datatables_data($filters, $length, $start, $order_col, $order_dir);

        $data = array();
        foreach ($docs as $doc) {
            $vBadge = ($doc->verification_status === 'Verified') 
                ? 'bg-secondary-container text-on-secondary-container' 
                : (($doc->verification_status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
            
            $expBadge = ($doc->expiry_status === 'Active') 
                ? 'text-secondary' 
                : (($doc->expiry_status === 'Expiring Soon') ? 'text-amber-600 font-bold' : (($doc->expiry_status === 'Expired') ? 'text-error font-bold' : 'text-on-surface-variant'));

            $studentCol = '<strong class="text-on-surface block">' . html_escape($doc->first_name . ' ' . $doc->last_name) . '</strong>' .
                          '<span class="text-[11px] text-on-surface-variant font-mono">' . html_escape($doc->admission_number) . ' (' . html_escape(($doc->class_name ?: '') . ' ' . ($doc->section_name ?: '')) . ')</span>';

            $catCol = html_escape($doc->category_name ?: $doc->document_type);

            $titleCol = '<span class="text-on-surface font-semibold block">' . html_escape($doc->document_name) . '</span>' .
                        '<span class="text-[11px] text-on-surface-variant font-mono">' . html_escape($doc->document_number ?: 'N/A') . '</span>';

            $expCol = '<span class="text-[12px] ' . $expBadge . '">' . html_escape($doc->expiry_status) . '</span>';
            if (!empty($doc->expiry_date)) {
                $expCol .= '<span class="block text-[11px] text-on-surface-variant font-mono">' . date('d M Y', strtotime($doc->expiry_date)) . '</span>';
            }

            $verCol = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ' . $vBadge . '">' . html_escape($doc->verification_status) . '</span>';

            if (!empty($doc->file_path)) {
                $fileCol = '<a href="' . base_url('uploads/student_documents/' . $doc->file_path) . '" target="_blank" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">' .
                           '<span class="material-symbols-outlined text-[16px]">visibility</span>View</a>';
            } else {
                $fileCol = '<span class="text-[11px] text-on-surface-variant font-mono">No File</span>';
            }

            $data[] = array(
                $studentCol,
                $catCol,
                $titleCol,
                $expCol,
                $verCol,
                $fileCol
            );
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => $records_total,
            "recordsFiltered" => $records_filtered,
            "data"            => $data,
            "csrf_token_name" => $this->security->get_csrf_token_name(),
            "csrf_hash"       => $this->security->get_csrf_hash(),
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    /**
     * Submodule 12: Document Categories
     */
    public function document_categories()
    {
        $this->require_permission('certificates.view');
        if ($this->input->method() === 'post') {
            $this->require_permission('certificates.verify_docs');
            $name        = trim($this->input->post('category_name', TRUE));
            $code        = strtoupper(trim($this->input->post('code', TRUE)));
            $description = trim($this->input->post('description', TRUE));
            $req         = $this->input->post('is_required', TRUE) ? 1 : 0;
            $exp_req     = $this->input->post('expiry_required', TRUE) ? 1 : 0;
            $ver_req     = $this->input->post('verification_required', TRUE) ? 1 : 0;

            $cat_data = array(
                'category_name'         => $name,
                'code'                  => $code,
                'description'           => $description,
                'applicable_to'         => 'Student',
                'is_required'           => $req,
                'expiry_required'       => $exp_req,
                'verification_required' => $ver_req,
                'status'                => 'Active'
            );

            $this->Document_category_model->insert($cat_data);
            $this->session->set_flashdata('success', 'Document category added.');
            redirect('certificates/document_categories');
            return;
        }

        $categories = $this->Document_category_model->get_all();
        $this->render('pages/certificates/document_categories', array(
            'title'      => 'Document Categories',
            'page_key'   => 'certificates-doc-categories',
            'categories' => $categories,
        ));
    }

    /**
     * Submodule 13: Document Verification Queue
     */
    public function document_verification()
    {
        $this->require_permission('certificates.verify_docs');
        $filters = array('verification_status' => 'Pending');
        $pending_docs = $this->Student_document_model->get_all($filters);

        $this->render('pages/certificates/document_verification', array(
            'title'        => 'Document Verification',
            'page_key'     => 'certificates-doc-verification',
            'pending_docs' => $pending_docs,
        ));
    }

    public function verify_doc($id)
    {
        $this->require_permission('certificates.verify_docs');
        $this->Student_document_model->verify_document($id, 'Verified', null, $this->get_current_user_id());
        $this->Certificate_setting_model->log_audit('Document Verified', 'Student Document', $id, "Verified by user #{$this->get_current_user_id()}", $this->get_current_user_id());
        $this->session->set_flashdata('success', 'Document verified successfully.');
        redirect('certificates/document_verification');
    }

    public function reject_doc($id)
    {
        $this->require_permission('certificates.verify_docs');
        $reason = $this->input->post('rejection_reason', TRUE) ?: 'Document unreadable or invalid.';
        $this->Student_document_model->verify_document($id, 'Rejected', $reason, $this->get_current_user_id());
        $this->Certificate_setting_model->log_audit('Document Rejected', 'Student Document', $id, "Reason: {$reason}", $this->get_current_user_id());
        $this->session->set_flashdata('success', 'Document marked as rejected.');
        redirect('certificates/document_verification');
    }

    /**
     * Submodule 14: Certificate History & Reissue
     */
    public function history()
    {
        $this->require_permission('certificates.view');
        $certificates = $this->Certificate_model->get_all();
        $this->render('pages/certificates/history', array(
            'title'        => 'Certificate History & Reissue Ledger',
            'page_key'     => 'certificates-history',
            'certificates' => $certificates,
        ));
    }

    public function reissue($certificate_id)
    {
        $this->require_permission('certificates.generate');
        if ($this->input->method() === 'post') {
            $reason = trim($this->input->post('reissue_reason', TRUE));
            if (empty($reason)) {
                $this->session->set_flashdata('error', 'Reissue reason is required.');
                redirect('certificates/preview/' . $certificate_id);
                return;
            }

            $this->Certificate_model->reissue_certificate($certificate_id, $reason, $this->get_current_user_id());
            $this->session->set_flashdata('success', 'Certificate reissued. New version recorded.');
            redirect('certificates/preview/' . $certificate_id);
            return;
        }
    }

    /**
     * Submodule 15: Certificate Reports
     */
    public function reports()
    {
        $this->require_permission('certificates.view');
        $type = $this->input->get('type', TRUE) ?: 'issued';
        $export = $this->input->get('export', TRUE);

        $certificates = $this->Certificate_model->get_all();
        $documents    = $this->Student_document_model->get_all();
        $types        = $this->Certificate_type_model->get_all();

        if ($export === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="certificate_report_' . date('Ymd_His') . '.csv"');
            $out = fopen('php://output', 'w');

            if ($type === 'documents') {
                fputcsv($out, array('Document ID', 'Student', 'Admission No', 'Category', 'Document Name', 'Verification', 'Expiry'));
                foreach ($documents as $d) {
                    fputcsv($out, array(
                        $d->document_id,
                        $d->first_name . ' ' . $d->last_name,
                        $d->admission_number,
                        $d->category_name,
                        $d->document_name,
                        $d->verification_status,
                        $d->expiry_status
                    ));
                }
            } else {
                fputcsv($out, array('Certificate No', 'Student', 'Admission No', 'Class', 'Type', 'Issue Date', 'Status', 'Version'));
                foreach ($certificates as $c) {
                    fputcsv($out, array(
                        $c->certificate_no,
                        $c->first_name . ' ' . $c->last_name,
                        $c->admission_number,
                        $c->class_name . ' ' . $c->section_name,
                        $c->certificate_type,
                        $c->issue_date,
                        $c->status,
                        'v' . $c->version
                    ));
                }
            }
            fclose($out);
            exit;
        }

        $this->render('pages/certificates/reports', array(
            'title'        => 'Certificate & Document Reports',
            'page_key'     => 'certificates-reports',
            'type'         => $type,
            'certificates' => $certificates,
            'documents'    => $documents,
            'types'        => $types,
        ));
    }

    /**
     * Submodule 16: Certificate Settings & Policies
     */
    public function settings()
    {
        $this->require_permission('certificates.generate');
        if ($this->input->method() === 'post') {
            $settings_data = array(
                'numbering_format'                   => trim($this->input->post('numbering_format', TRUE)),
                'number_sequence_length'             => (int)$this->input->post('number_sequence_length', TRUE),
                'require_approval'                   => $this->input->post('require_approval', TRUE) ? 1 : 0,
                'require_document_verification'      => $this->input->post('require_document_verification', TRUE) ? 1 : 0,
                'require_fee_clearance_for_tc'       => $this->input->post('require_fee_clearance_for_tc', TRUE) ? 1 : 0,
                'require_library_clearance_for_tc'   => $this->input->post('require_library_clearance_for_tc', TRUE) ? 1 : 0,
                'require_transport_clearance_for_tc' => $this->input->post('require_transport_clearance_for_tc', TRUE) ? 1 : 0,
                'watermark_enabled'                  => $this->input->post('watermark_enabled', TRUE) ? 1 : 0,
                'document_expiry_reminder_days'      => (int)$this->input->post('document_expiry_reminder_days', TRUE),
            );

            $this->Certificate_setting_model->save_settings($settings_data);
            $this->Certificate_setting_model->log_audit('Settings Updated', 'Settings', 1, 'Certificate policies updated', $this->get_current_user_id());
            $this->session->set_flashdata('success', 'Certificate settings updated successfully.');
            redirect('certificates/settings');
            return;
        }

        $settings   = $this->Certificate_setting_model->get_settings();
        $audit_logs = $this->Certificate_setting_model->get_audit_logs(30);

        $this->render('pages/certificates/settings', array(
            'title'      => 'Certificate Settings',
            'page_key'   => 'certificates-settings',
            'settings'   => $settings,
            'audit_logs' => $audit_logs,
        ));
    }
}
