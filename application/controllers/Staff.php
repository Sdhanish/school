<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Staff_model');
        $this->load->model('Staff_document_type_model');
        $this->load->model('Designation_model');
        $this->load->model('Subject_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Academic_year_model');
        $this->load->model('Academic_group_model');
        $this->load->library('form_validation');
        $this->load->library('phone_validator');
    }

    /* =========================================================================
       1. Staff Management Overview
       ========================================================================= */
    public function index()
    {
        $this->overview();
    }

    public function overview()
    {
        $this->require_permission('staff.view');
        $stats = $this->Staff_model->get_dashboard_stats();

        $this->render('pages/staff/overview', array(
            'title'        => 'Staff Management Overview',
            'page_key'     => 'staff',
            'breadcrumb'   => array('Staff Management', 'Overview'),
            'stats'        => $stats,
        ));
    }

    public function directory()
    {
        $this->require_permission('staff.view');
        $desig_id          = $this->input->get('designation_id');
        $staff_type        = $this->input->get('staff_type');
        $academic_group_id = $this->input->get('academic_group_id');
        $status            = $this->input->get('status');
        $search            = $this->input->get('search');

        $staff = $this->Staff_model->get_all(array(
            'designation_id'    => $desig_id,
            'staff_type'        => $staff_type,
            'academic_group_id' => $academic_group_id,
            'status'            => ($status !== NULL && $status !== '') ? $status : '1',
            'search'            => $search,
            'school_id'         => $this->school_id,
        ));

        $designations    = $this->Designation_model->get_all(false, $this->school_id);
        $academic_groups = $this->Academic_group_model->get_groups_for_dropdown($this->school_id);

        $this->render('pages/staff/index', array(
            'title'           => 'All Staff',
            'page_key'        => 'staff-directory',
            'breadcrumb'      => array('Staff Management', 'All Staff'),
            'staff'           => $staff,
            'designations'    => $designations,
            'academic_groups' => $academic_groups,
        ));
    }

    public function ajax_list()
    {
        $this->require_permission('staff.view');

        $draw   = (int)$this->input->post('draw');
        $start  = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $order  = $this->input->post('order');
        $search = $this->input->post('search');

        $order_col_idx = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir     = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';
        $search_val    = isset($search['value']) ? trim($search['value']) : '';

        $filters = array(
            'designation_id'    => $this->input->post('designation_id') ?: $this->input->get('designation_id'),
            'staff_type'        => $this->input->post('staff_type') ?: $this->input->get('staff_type'),
            'academic_group_id' => $this->input->post('academic_group_id') ?: $this->input->get('academic_group_id'),
            'status'            => $this->input->post('status') !== NULL ? $this->input->post('status') : $this->input->get('status'),
            'search'            => $search_val,
            'school_id'         => $this->school_id,
        );

        $records_total    = $this->Staff_model->get_datatables_count_all(array('school_id' => $this->school_id));
        $records_filtered = $this->Staff_model->count_filtered($filters);
        $staff_list       = $this->Staff_model->get_datatables_data($filters, $length, $start, $order_col_idx, $order_dir);

        $data = array();
        foreach ($staff_list as $s) {
            $initials = school_initials($s->full_name);

            $statusBadge = ($s->status == 1)
                ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>'
                : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive</span>';

            $codeCol = '<a href="' . site_url('staff/profile/' . $s->staff_id) . '" class="text-primary font-medium hover:underline font-mono">' . html_escape($s->employee_code ?: '—') . '</a>';

            $avatarImg = (!empty($s->photo) && file_exists(FCPATH . 'uploads/staff/' . $s->photo))
                ? '<img src="' . base_url('uploads/staff/' . $s->photo) . '" alt="' . html_escape($s->full_name) . '" class="w-8 h-8 rounded-full object-cover shrink-0 border border-outline-variant/60 shadow-sm"/>'
                : '<div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center text-[11px] font-semibold shrink-0">' . html_escape($initials) . '</div>';

            $nameCol = '<div class="flex items-center gap-2.5">' .
                $avatarImg .
                '<div>' .
                    '<div class="font-medium text-on-surface">' . html_escape($s->full_name) . '</div>' .
                    '<div class="text-[12px] text-on-surface-variant">' . html_escape($s->staff_type === 'teacher' ? 'Teaching Staff' : 'Non-Teaching Staff') . '</div>' .
                '</div>' .
            '</div>';

            $groupCol = '<span class="text-on-surface-variant/60">—</span>';
            if ($s->staff_type === 'teacher') {
                if (!empty($s->group_name)) {
                    $groupCol = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-primary-container text-on-primary-container">' . html_escape($s->group_name) . '</span>';
                }
            }

            $contactCol = '<div>' .
                '<div class="text-on-surface">' . html_escape($s->email ?: '—') . '</div>' .
                '<div class="text-[12px] text-on-surface-variant">' . html_escape($s->phone ?: '—') . '</div>' .
            '</div>';

            $actionsCol = '<div class="flex items-center justify-end gap-1.5">' .
                '<a href="' . site_url('staff/profile/' . $s->staff_id) . '" title="View Profile" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">visibility</span></a>' .
                '<a href="' . site_url('staff/edit/' . $s->staff_id) . '" title="Edit Staff" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span></a>' .
            '</div>';

            $data[] = array(
                $codeCol,
                $nameCol,
                $groupCol,
                html_escape($s->designation_name ?: '—'),
                $contactCol,
                $statusBadge,
                $actionsCol
            );
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => $records_total,
            "recordsFiltered" => $records_filtered,
            "data"            => $data,
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($output));
    }

    /* =========================================================================
       2. Teachers Directory
       ========================================================================= */
    public function teachers()
    {
        $this->require_permission('staff.view');
        $desig_id          = $this->input->get('designation_id');
        $academic_group_id = $this->input->get('academic_group_id');
        $subject           = $this->input->get('subject');
        $search            = $this->input->get('search');

        $teachers = $this->Staff_model->get_teachers(array(
            'designation_id'    => $desig_id,
            'academic_group_id' => $academic_group_id,
            'subject_name'      => $subject,
            'search'            => $search,
            'school_id'         => $this->school_id,
        ));

        $designations    = $this->Designation_model->get_all(false, $this->school_id);
        $academic_groups = $this->Academic_group_model->get_groups_for_dropdown($this->school_id);

        $this->render('pages/staff/teachers', array(
            'title'           => 'Teachers',
            'page_key'        => 'teachers',
            'breadcrumb'      => array('Staff Management', 'All Staff', 'Teachers'),
            'teachers'        => $teachers,
            'designations'    => $designations,
            'academic_groups' => $academic_groups,
        ));
    }

    /* =========================================================================
       3. Non-Teaching Staff Directory
       ========================================================================= */
    public function non_teaching()
    {
        $this->require_permission('staff.view');
        $desig_id = $this->input->get('designation_id');
        $search   = $this->input->get('search');

        $staff = $this->Staff_model->get_non_teaching(array(
            'designation_id' => $desig_id,
            'search'         => $search,
            'school_id'      => $this->school_id,
        ));

        $designations = $this->Designation_model->get_all(false, $this->school_id);

        $this->render('pages/staff/non_teaching', array(
            'title'        => 'Non-Teaching Staff',
            'page_key'     => 'non-teaching-staff',
            'breadcrumb'   => array('Staff Management', 'All Staff', 'Non-Teaching Staff'),
            'staff'        => $staff,
            'designations' => $designations,
        ));
    }

    /* =========================================================================
       4. Staff Registration / Add
       ========================================================================= */
    public function add()
    {
        return $this->register();
    }

    public function register()
    {
        $this->require_permission('staff.create');

        $document_types = $this->Staff_document_type_model->get_active_types();
        $doc_errors = array();
        $photo_error = NULL;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('full_name', 'Staff Name', 'required|trim');
            $this->form_validation->set_rules('employee_code', 'Employee ID', 'required|trim');
            $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');
            $this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|trim');
            $this->form_validation->set_rules('staff_type', 'Staff Type', 'required');
            $staffTypeInput = $this->input->post('staff_type');
            if ($staffTypeInput === 'teacher') {
                $this->form_validation->set_rules('academic_group_id', 'Group', 'required|numeric', array(
                    'required' => 'The Group field is mandatory for Teaching Staff.'
                ));
            }
            $this->form_validation->set_rules('designation_id', 'Designation', 'required');
            $this->form_validation->set_rules('joining_date', 'Joining Date', 'required');

            // 1. Process Staff Profile Photo (Crop / Upload)
            $photo_result = $this->process_staff_photo();
            $photo_filename = NULL;
            if ($photo_result['success'] === FALSE) {
                $photo_error = $photo_result['error'];
            } else {
                $photo_filename = $photo_result['file_name'];
            }

            // 2. Validate Dynamic Required Staff Documents
            $allowedExtensions = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
            $maxFileSize = 10 * 1024 * 1024; // 10MB

            foreach ($document_types as $dt) {
                $typeId = $dt->id;
                $hasFile = isset($_FILES['staff_doc_file']['name'][$typeId]) && 
                           !empty($_FILES['staff_doc_file']['name'][$typeId]) &&
                           isset($_FILES['staff_doc_file']['error'][$typeId]) && 
                           $_FILES['staff_doc_file']['error'][$typeId] === UPLOAD_ERR_OK;

                if (!$hasFile) {
                    $doc_errors[] = 'Please upload the required ' . $dt->document_name . ' document.';
                } else {
                    $fileName = $_FILES['staff_doc_file']['name'][$typeId];
                    $fileSize = $_FILES['staff_doc_file']['size'][$typeId];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowedExtensions)) {
                        $doc_errors[] = $dt->document_name . ': Invalid file format (.' . $ext . '). Allowed: ' . implode(', ', $allowedExtensions) . '.';
                    }
                    if ($fileSize > $maxFileSize || $fileSize <= 0) {
                        $doc_errors[] = $dt->document_name . ': File size exceeds the 10MB limit.';
                    }
                }
            }

            // Phone validation
            $phone_errors = array();

            $p_country = $this->input->post('phone_country', TRUE) ?: 'IN';
            $p_raw = $this->input->post('phone', TRUE);
            $p_check = $this->phone_validator->validate_and_normalize($p_raw, $p_country, true);
            if (!$p_check['valid']) {
                $phone_errors['phone'] = $p_check['error'];
            }
            $p_norm = $p_check['normalized'];

            $alt_country = $this->input->post('alternate_phone_country', TRUE) ?: 'IN';
            $alt_raw = $this->input->post('alternate_phone', TRUE);
            $alt_norm = NULL;
            if (!empty($alt_raw)) {
                $alt_check = $this->phone_validator->validate_and_normalize($alt_raw, $alt_country, false);
                if (!$alt_check['valid']) {
                    $phone_errors['alternate_phone'] = $alt_check['error'];
                } else {
                    $alt_norm = $alt_check['normalized'];
                }
            }

            $formValid = $this->form_validation->run();

            if ($formValid === TRUE && empty($doc_errors) && empty($photo_error) && empty($phone_errors)) {
                $staffType = $this->input->post('staff_type');
                $desig_id  = (int)$this->input->post('designation_id');
                $desig_obj = $this->Designation_model->get_by_id($desig_id, $this->school_id);
                $category  = ($desig_obj && !empty($desig_obj->category)) ? $desig_obj->category : (($staffType === 'teacher') ? 'Teaching' : 'Non-Teaching');

                $data = array(
                    'employee_code'     => $this->input->post('employee_code'),
                    'full_name'         => $this->input->post('full_name'),
                    'gender'            => $this->input->post('gender') ?: 'Male',
                    'date_of_birth'     => $this->input->post('date_of_birth') ?: NULL,
                    'blood_group'       => $this->input->post('blood_group') ?: NULL,
                    'phone'             => $p_norm,
                    'alternate_phone'   => $alt_norm,
                    'email'             => $this->input->post('email'),
                    'address'           => $this->input->post('address') ?: NULL,
                    'staff_type'        => $staffType,
                    'category'          => $category,
                    'academic_group_id' => ($staffType === 'teacher' && !empty($this->input->post('academic_group_id'))) ? (int)$this->input->post('academic_group_id') : NULL,
                    'designation_id'    => $desig_id,
                    'joining_date'      => $this->input->post('joining_date'),
                    'salary'            => $this->input->post('salary') ? floatval($this->input->post('salary')) : 0.00,
                    'qualification'     => $this->input->post('qualification') ?: NULL,
                    'experience'        => $this->input->post('experience') ?: NULL,
                    'specialization'    => ($staffType === 'teacher') ? ($this->input->post('specialization') ?: NULL) : NULL,
                    'employment_status' => $this->input->post('employment_status') ?: 'Active',
                    'photo'             => $photo_filename,
                    'status'            => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                );

                $staff_id = $this->Staff_model->insert($data);

                // Process Document Uploads
                $uploadDir = FCPATH . 'uploads/staff_docs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($document_types as $dt) {
                    $typeId = $dt->id;
                    if (isset($_FILES['staff_doc_file']['name'][$typeId]) && !empty($_FILES['staff_doc_file']['name'][$typeId])) {
                        $origName = $_FILES['staff_doc_file']['name'][$typeId];
                        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                        $safeName = 'doc_' . $staff_id . '_' . $typeId . '_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
                        $destPath = $uploadDir . $safeName;

                        if (move_uploaded_file($_FILES['staff_doc_file']['tmp_name'][$typeId], $destPath)) {
                            $mimeType = $_FILES['staff_doc_file']['type'][$typeId] ?: 'application/octet-stream';
                            $fileSize = $_FILES['staff_doc_file']['size'][$typeId];

                            $this->Staff_model->add_document(array(
                                'staff_id'         => $staff_id,
                                'document_type_id' => $typeId,
                                'document_type'    => $dt->document_name,
                                'document_name'    => $dt->document_name,
                                'file_name'        => $origName,
                                'file_path'        => 'uploads/staff_docs/' . $safeName,
                                'file_type'        => $mimeType,
                                'file_size'        => $fileSize,
                                'mime_type'        => $mimeType,
                                'uploaded_by'      => $this->current_user->user_id ?? 1,
                                'status'           => 1,
                                'is_deleted'       => 'n',
                                'created_at'       => date('Y-m-d H:i:s'),
                            ));
                        }
                    }
                }

                $this->session->set_flashdata('success', 'Staff member registered successfully!');
                redirect('staff/profile/' . $staff_id);
                return;
            }
        }

        $designations    = $this->Designation_model->get_all(false, $this->school_id);
        $academic_groups = $this->Academic_group_model->get_groups_for_dropdown($this->school_id);

        $this->render('pages/staff/add', array(
            'title'           => 'Staff Registration',
            'page_key'        => 'staff_add',
            'breadcrumb'      => array('Staff Management', 'Add Staff'),
            'designations'    => $designations,
            'academic_groups' => $academic_groups,
            'document_types'  => $document_types,
            'doc_errors'      => $doc_errors,
            'photo_error'     => $photo_error,
            'phone_errors'    => $phone_errors ?? array(),
        ));
    }

    /* =========================================================================
       5. Edit Staff
       ========================================================================= */
    public function edit($staff_id = NULL)
    {
        $this->require_permission('staff.edit');

        if (empty($staff_id)) {
            redirect('staff');
        }

        $staff = $this->Staff_model->get_by_id($staff_id, $this->school_id);
        if (!$staff) {
            show_404();
        }

        $document_types    = $this->Staff_document_type_model->get_active_types();
        $existing_docs_map = $this->Staff_model->get_staff_documents_map($staff_id, $this->school_id);
        $doc_errors        = array();
        $photo_error       = NULL;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('full_name', 'Staff Name', 'required|trim');
            $this->form_validation->set_rules('employee_code', 'Employee ID', 'required|trim');
            $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');
            $this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|trim');

            $submitted_staff_type = $this->input->post('staff_type') ?: $staff->staff_type;
            $is_teacher = in_array(strtolower(trim($submitted_staff_type)), array('teacher', 'teaching'));
            if ($is_teacher && $this->input->post('academic_group_id') !== NULL && $this->input->post('academic_group_id') !== '') {
                $this->form_validation->set_rules('academic_group_id', 'Department / Group', 'numeric');
            }

            // Handle Photo Removal if requested
            if ($this->input->post('remove_photo') === '1') {
                $this->Staff_model->delete_photo($staff_id);
                $staff->photo = NULL;
            }

            // Handle New/Replaced Photo Upload
            $new_photo = NULL;
            $photo_result = $this->process_staff_photo($staff_id);
            if ($photo_result['success'] === FALSE) {
                $photo_error = $photo_result['error'];
            } elseif (!empty($photo_result['file_name'])) {
                $new_photo = $photo_result['file_name'];
            }

            $allowedExtensions = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
            $maxFileSize = 10 * 1024 * 1024; // 10MB

            // Validate any replacement documents uploaded
            if (!empty($_FILES['staff_doc_file']['name'])) {
                foreach ($_FILES['staff_doc_file']['name'] as $typeId => $name) {
                    if (!empty($name) && isset($_FILES['staff_doc_file']['error'][$typeId]) && $_FILES['staff_doc_file']['error'][$typeId] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $size = $_FILES['staff_doc_file']['size'][$typeId];
                        if (!in_array($ext, $allowedExtensions)) {
                            $doc_errors[] = 'Invalid file format for replacement document (' . $name . ').';
                        }
                        if ($size > $maxFileSize || $size <= 0) {
                            $doc_errors[] = 'File size exceeds 10MB limit (' . $name . ').';
                        }
                    }
                }
            }

            // Phone validation
            $phone_errors = array();

            $p_country = $this->input->post('phone_country', TRUE) ?: 'IN';
            $p_raw = $this->input->post('phone', TRUE);
            $p_check = $this->phone_validator->validate_and_normalize($p_raw, $p_country, true);
            if (!$p_check['valid']) {
                $phone_errors['phone'] = $p_check['error'];
            }
            $p_norm = $p_check['normalized'];

            $alt_country = $this->input->post('alternate_phone_country', TRUE) ?: 'IN';
            $alt_raw = $this->input->post('alternate_phone', TRUE);
            $alt_norm = NULL;
            if (!empty($alt_raw)) {
                $alt_check = $this->phone_validator->validate_and_normalize($alt_raw, $alt_country, false);
                if (!$alt_check['valid']) {
                    $phone_errors['alternate_phone'] = $alt_check['error'];
                } else {
                    $alt_norm = $alt_check['normalized'];
                }
            }

            if ($this->form_validation->run() === TRUE && empty($doc_errors) && empty($photo_error) && empty($phone_errors)) {
                $staffType = $this->input->post('staff_type') ?: $staff->staff_type;
                $is_teacher = in_array(strtolower(trim($staffType)), array('teacher', 'teaching'));
                $desig_id  = (int)($this->input->post('designation_id') ?: $staff->designation_id);
                $desig_obj = $this->Designation_model->get_by_id($desig_id, $this->school_id);
                $category  = ($desig_obj && !empty($desig_obj->category)) ? $desig_obj->category : ($is_teacher ? 'Teaching' : 'Non-Teaching');

                $raw_grp = $this->input->post('academic_group_id');
                $new_group_id = ($is_teacher && !empty($raw_grp)) ? (int)$raw_grp : NULL;

                // Check whether changing the teacher's group conflicts with existing class assignments
                $conflict_check = $this->Staff_model->check_teacher_group_assignment_conflicts($staff_id, $new_group_id, $this->school_id);
                if (!$conflict_check['allowed']) {
                    $this->session->set_flashdata('warning', $conflict_check['warning']);
                }

                $data = array(
                    'employee_code'     => $this->input->post('employee_code'),
                    'full_name'         => $this->input->post('full_name'),
                    'gender'            => $this->input->post('gender') ?: $staff->gender,
                    'date_of_birth'     => $this->input->post('date_of_birth') ?: $staff->date_of_birth,
                    'blood_group'       => $this->input->post('blood_group') ?: $staff->blood_group,
                    'phone'             => $p_norm,
                    'alternate_phone'   => $alt_norm,
                    'email'             => $this->input->post('email'),
                    'address'           => $this->input->post('address'),
                    'staff_type'        => $staffType,
                    'category'          => $category,
                    'academic_group_id' => $new_group_id,
                    'designation_id'    => $desig_id,
                    'joining_date'      => $this->input->post('joining_date'),
                    'salary'            => $this->input->post('salary') ? floatval($this->input->post('salary')) : $staff->salary,
                    'qualification'     => $this->input->post('qualification'),
                    'experience'        => $this->input->post('experience'),
                    'specialization'    => ($staffType === 'teacher') ? $this->input->post('specialization') : NULL,
                    'employment_status' => $this->input->post('employment_status') ?: $staff->employment_status,
                    'updated_at'        => date('Y-m-d H:i:s'),
                );

                if (!empty($new_photo)) {
                    $oldPhoto = $staff->photo;
                    $data['photo'] = $new_photo;
                }

                $this->Staff_model->update($staff_id, $data);

                // If photo was successfully updated and an old photo file existed, unlink the old file
                if (!empty($new_photo) && !empty($oldPhoto) && $oldPhoto !== $new_photo) {
                    $oldFilePath = FCPATH . 'uploads/staff/' . $oldPhoto;
                    if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }

                // Process replacement or newly uploaded documents
                $uploadDir = FCPATH . 'uploads/staff_docs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (!empty($_FILES['staff_doc_file']['name'])) {
                    foreach ($_FILES['staff_doc_file']['name'] as $typeId => $origName) {
                        if (!empty($origName) && isset($_FILES['staff_doc_file']['error'][$typeId]) && $_FILES['staff_doc_file']['error'][$typeId] === UPLOAD_ERR_OK) {
                            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                            $safeName = 'doc_' . $staff_id . '_' . $typeId . '_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
                            $destPath = $uploadDir . $safeName;

                            if (move_uploaded_file($_FILES['staff_doc_file']['tmp_name'][$typeId], $destPath)) {
                                $mimeType = $_FILES['staff_doc_file']['type'][$typeId] ?: 'application/octet-stream';
                                $fileSize = $_FILES['staff_doc_file']['size'][$typeId];

                                // Find doc type name
                                $dtObj = $this->Staff_document_type_model->get_by_id($typeId);
                                $docName = $dtObj ? $dtObj->document_name : 'Staff Document';

                                // Deactivate old document if replacing
                                if (isset($existing_docs_map[$typeId])) {
                                    $this->Staff_model->delete_document($existing_docs_map[$typeId]->document_id);
                                }

                                $this->Staff_model->add_document(array(
                                    'staff_id'         => $staff_id,
                                    'document_type_id' => $typeId,
                                    'document_type'    => $docName,
                                    'document_name'    => $docName,
                                    'file_name'        => $origName,
                                    'file_path'        => 'uploads/staff_docs/' . $safeName,
                                    'file_type'        => $mimeType,
                                    'file_size'        => $fileSize,
                                    'mime_type'        => $mimeType,
                                    'uploaded_by'      => $this->current_user->user_id ?? 1,
                                    'status'           => 1,
                                    'is_deleted'       => 'n',
                                    'created_at'       => date('Y-m-d H:i:s'),
                                ));
                            }
                        }
                    }
                }

                $this->session->set_flashdata('success', 'Staff details updated successfully!');
                redirect('staff/edit/' . $staff_id);
                return;
            }
        }

        $designations    = $this->Designation_model->get_all(false, $this->school_id);
        $academic_groups = $this->Academic_group_model->get_groups_for_dropdown($this->school_id);

        $this->render('pages/staff/edit', array(
            'title'             => 'Edit Staff: ' . $staff->full_name,
            'page_key'          => 'staff_edit',
            'breadcrumb'        => array('Staff Management', 'Edit Staff'),
            'staff'             => $staff,
            'staff_id'          => $staff_id,
            'designations'      => $designations,
            'academic_groups'   => $academic_groups,
            'document_types'    => $document_types,
            'existing_docs_map' => $existing_docs_map,
            'doc_errors'        => $doc_errors,
            'photo_error'       => $photo_error,
            'phone_errors'      => $phone_errors ?? array(),
        ));
    }

    public function remove_photo($staff_id = NULL)
    {
        $this->require_permission('staff.edit');

        if (empty($staff_id)) {
            show_404();
            return;
        }

        $this->Staff_model->delete_photo($staff_id, $this->school_id);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => TRUE, 'message' => 'Staff photo removed successfully.')));
            return;
        }

        $this->session->set_flashdata('success', 'Staff photo removed.');
        $redirect = $this->input->get('redirect_to') ?: ('staff/edit/' . $staff_id);
        redirect($redirect);
    }

    /* =========================================================================
       Private: Process and Validate Staff Photo Upload / Base64 Cropped Data
       ========================================================================= */
    private function process_staff_photo($staff_id = NULL)
    {
        $uploadDir = FCPATH . 'uploads/staff/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $maxSize = 3 * 1024 * 1024; // 3 MB
        $allowedExts = array('jpg', 'jpeg', 'png', 'webp');
        $allowedMimes = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp');
        $allowedTypes = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
        if (defined('IMAGETYPE_WEBP')) {
            $allowedTypes[] = IMAGETYPE_WEBP;
        }

        // 1. Check if cropped image payload is submitted (Base64 data URL from Cropper)
        $croppedData = $this->input->post('cropped_image_data');
        if (!empty($croppedData)) {
            if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,([A-Za-z0-9+\/=\r\n]+)$/i', $croppedData, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $decoded = base64_decode($matches[2]);

                if ($decoded === FALSE || strlen($decoded) < 50) {
                    return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
                }

                if (strlen($decoded) > $maxSize) {
                    return array('success' => FALSE, 'error' => 'Staff image must not exceed 3 MB.');
                }

                $imgInfo = @getimagesizefromstring($decoded);
                if ($imgInfo === FALSE) {
                    return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
                }

                $detectedType = isset($imgInfo[2]) ? $imgInfo[2] : 0;
                $detectedMime = isset($imgInfo['mime']) ? strtolower($imgInfo['mime']) : '';
                if (!in_array($detectedType, $allowedTypes, TRUE) && !in_array($detectedMime, $allowedMimes, TRUE)) {
                    return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
                }

                $safeName = 'staff_' . ($staff_id ?: 'new') . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.' . $ext;
                $destPath = $uploadDir . $safeName;

                if (file_put_contents($destPath, $decoded) !== FALSE) {
                    return array('success' => TRUE, 'file_name' => $safeName);
                } else {
                    return array('success' => FALSE, 'error' => 'Unable to upload staff image. Please try again.');
                }
            } else {
                return array('success' => FALSE, 'error' => 'Invalid cropped image format.');
            }
        }

        // 2. Fallback check: Direct standard file upload $_FILES['staff_image']
        if (isset($_FILES['staff_image']['name']) && !empty($_FILES['staff_image']['name'])) {
            $fileError = isset($_FILES['staff_image']['error']) ? (int)$_FILES['staff_image']['error'] : UPLOAD_ERR_NO_FILE;
            if ($fileError !== UPLOAD_ERR_OK) {
                if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
                    return array('success' => FALSE, 'error' => 'Staff image must not exceed 3 MB.');
                }
                return array('success' => FALSE, 'error' => 'Unable to upload staff image. Please try again.');
            }

            $origName = $_FILES['staff_image']['name'];
            $fileSize = $_FILES['staff_image']['size'];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts, TRUE)) {
                return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
            }

            if ($fileSize > $maxSize || $fileSize <= 0) {
                return array('success' => FALSE, 'error' => 'Staff image must not exceed 3 MB.');
            }

            $imgInfo = @getimagesize($_FILES['staff_image']['tmp_name']);
            if ($imgInfo === FALSE) {
                return array('success' => FALSE, 'error' => 'The selected file is not a valid image.');
            }

            $detectedType = isset($imgInfo[2]) ? $imgInfo[2] : 0;
            $detectedMime = isset($imgInfo['mime']) ? strtolower($imgInfo['mime']) : '';
            if (!in_array($detectedType, $allowedTypes, TRUE) && !in_array($detectedMime, $allowedMimes, TRUE)) {
                return array('success' => FALSE, 'error' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.');
            }

            if ($ext === 'jpeg') $ext = 'jpg';
            $safeName = 'staff_' . ($staff_id ?: 'new') . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.' . $ext;
            $destPath = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES['staff_image']['tmp_name'], $destPath)) {
                return array('success' => TRUE, 'file_name' => $safeName);
            } else {
                return array('success' => FALSE, 'error' => 'Unable to upload staff image. Please try again.');
            }
        }

        // No image provided
        return array('success' => TRUE, 'file_name' => NULL);
    }

    /* =========================================================================
       6. Delete Staff (Safe Deactivation)
       ========================================================================= */
    public function delete($staff_id = NULL)
    {
        $this->require_permission('staff.delete');

        if (!empty($staff_id)) {
            $this->Staff_model->soft_delete($staff_id);
            $this->session->set_flashdata('success', 'Staff member deactivated safely.');
        }
        redirect('staff');
    }

    /* =========================================================================
       7. Staff Profile View
       ========================================================================= */
    public function profile($staff_id = NULL)
    {
        $this->require_permission('staff.view');

        if (empty($staff_id)) {
            redirect('staff');
        }

        $staff = $this->Staff_model->get_profile($staff_id);
        if (!$staff || (int)$staff->school_id !== (int)$this->school_id) {
            show_404();
        }

        $designations      = $this->Designation_model->get_all(false, $this->school_id);
        $years             = $this->Academic_year_model->get_all($this->school_id);
        $classes           = $this->Class_model->get_all(NULL, $this->school_id);
        $sections          = $this->Division_model->get_all(NULL, $this->school_id);
        $subjects          = $this->Subject_model->get_all(NULL, $this->school_id);
        $document_types    = $this->Staff_document_type_model->get_active_types($this->school_id);
        $existing_docs_map = $this->Staff_model->get_staff_documents_map($staff_id, $this->school_id);

        $this->render('pages/staff/profile', array(
            'title'             => 'Staff Profile: ' . $staff->full_name,
            'page_key'          => ($staff->staff_type === 'teacher') ? 'teachers' : 'non-teaching-staff',
            'breadcrumb'        => array('Staff Management', 'Staff Profile'),
            'staff'             => $staff,
            'staff_id'          => $staff_id,
            'designations'      => $designations,
            'years'             => $years,
            'classes'           => $classes,
            'sections'          => $sections,
            'subjects'          => $subjects,
            'document_types'    => $document_types,
            'existing_docs_map' => $existing_docs_map,
        ));
    }

    /* =========================================================================
       8. Designations Management
       ========================================================================= */
    public function departments_designations()
    {
        redirect('staff/designations');
    }

    public function designations()
    {
        $this->require_permission('staff.view');

        $can_create = $this->rbac->has_permission('staff.create');
        $can_edit   = $this->rbac->has_permission('staff.edit');
        $can_delete = $this->rbac->has_permission('staff.delete');

        $is_super_admin   = $this->rbac->is_super_admin();
        $can_create_group = $is_super_admin || $this->rbac->has_permission('academic_groups.create') || $this->rbac->has_permission('academics.manage') || $this->rbac->has_permission('staff.create');
        $can_edit_group   = $is_super_admin || $this->rbac->has_permission('academic_groups.edit') || $this->rbac->has_permission('academics.manage') || $this->rbac->has_permission('staff.edit');

        // Form POST fallback
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'add_designation') {
                $this->require_permission('staff.create');
                $name = trim($this->input->post('designation_name'));
                if (!empty($name)) {
                    $this->Designation_model->insert(array(
                        'school_id'        => $this->school_id,
                        'designation_name' => $name,
                        'category'         => $this->input->post('category') ?: 'Teaching',
                        'description'      => trim($this->input->post('description')),
                        'status'           => 1,
                        'created_at'       => date('Y-m-d H:i:s')
                    ));
                    $this->session->set_flashdata('success', 'Designation added successfully!');
                }
            } elseif ($action === 'edit_designation') {
                $this->require_permission('staff.edit');
                $id = (int)$this->input->post('designation_id');
                $name = trim($this->input->post('designation_name'));
                if ($id > 0 && !empty($name)) {
                    $this->Designation_model->update($id, array(
                        'designation_name' => $name,
                        'category'         => $this->input->post('category') ?: 'Teaching',
                        'description'      => trim($this->input->post('description')),
                        'status'           => $this->input->post('status') !== null ? ((int)$this->input->post('status') ? 1 : 0) : 1
                    ), $this->school_id);
                    $this->session->set_flashdata('success', 'Designation updated successfully!');
                }
            } elseif ($action === 'delete_designation') {
                $this->require_permission('staff.delete');
                $id = (int)$this->input->post('designation_id');
                if ($id > 0) {
                    $desig = $this->Designation_model->get_by_id($id, $this->school_id);
                    if ($desig) {
                        $s_usage = $this->Designation_model->count_staff_usage($id, $this->school_id);
                        $u_usage = $this->Designation_model->count_user_usage($id, $this->school_id);
                        if ($s_usage > 0 || $u_usage > 0) {
                            $this->session->set_flashdata('error', 'Cannot delete designation: assigned to active staff or users. You can deactivate it instead.');
                        } else {
                            $this->Designation_model->soft_delete($id, $this->school_id);
                            $this->session->set_flashdata('success', 'Designation deleted successfully!');
                        }
                    }
                }
            } elseif ($action === 'add_group') {
                if (!$can_create_group) {
                    show_error('Access restricted. You do not have permission to create Department / Groups.', 403, '403 Forbidden');
                    return;
                }
                $name = trim($this->input->post('group_name'));
                if (empty($name)) {
                    $this->session->set_flashdata('error', 'Department / Group name is required.');
                } elseif ($this->Academic_group_model->check_duplicate($name)) {
                    $this->session->set_flashdata('error', 'Department / Group "' . html_escape($name) . '" already exists.');
                } else {
                    $classes_input = $this->input->post('classes', TRUE);
                    $status_val = $this->input->post('status') !== null ? (int)$this->input->post('status') : 1;
                    $attendance_type = strtolower(trim($this->input->post('attendance_type') ?: 'daily'));
                    if (!in_array($attendance_type, array('daily', 'period'))) {
                        $attendance_type = 'daily';
                    }
                    $this->Academic_group_model->insert(array(
                        'school_id'       => $this->school_id,
                        'group_name'      => $name,
                        'description'     => $this->input->post('description', TRUE),
                        'display_order'   => (int)$this->input->post('display_order') ?: 0,
                        'status'          => $status_val,
                        'attendance_type' => $attendance_type
                    ), $classes_input);
                    $this->session->set_flashdata('success', 'Department / Group created successfully!');
                }
            } elseif ($action === 'edit_group') {
                if (!$can_edit_group) {
                    show_error('Access restricted. You do not have permission to edit Department / Groups.', 403, '403 Forbidden');
                    return;
                }
                $id = (int)$this->input->post('academic_group_id');
                $group = $this->Academic_group_model->get_by_id($id, $this->school_id);
                if (!$group) {
                    show_error('Department / Group not found or access denied.', 404, '404 Not Found');
                    return;
                }
                $name = trim($this->input->post('group_name'));
                if (empty($name)) {
                    $this->session->set_flashdata('error', 'Department / Group name is required.');
                } elseif ($this->Academic_group_model->check_duplicate($name, $id)) {
                    $this->session->set_flashdata('error', 'Department / Group "' . html_escape($name) . '" already exists.');
                } else {
                    $classes_input = $this->input->post('classes', TRUE);
                    $status_val = $this->input->post('status') !== null ? (int)$this->input->post('status') : (int)$group->status;
                    $attendance_type = strtolower(trim($this->input->post('attendance_type') ?: ($group->attendance_type ?? 'daily')));
                    if (!in_array($attendance_type, array('daily', 'period'))) {
                        $attendance_type = 'daily';
                    }
                    $this->Academic_group_model->update($id, array(
                        'group_name'      => $name,
                        'description'     => $this->input->post('description', TRUE),
                        'display_order'   => (int)$this->input->post('display_order') ?: 0,
                        'status'          => $status_val,
                        'attendance_type' => $attendance_type,
                    ), $classes_input);
                    $this->session->set_flashdata('success', 'Department / Group updated successfully!');
                }
            }
            redirect('staff/designations');
        }

        $designations    = $this->Designation_model->get_all(false, $this->school_id);
        $categories      = $this->Designation_model->get_categories($this->school_id);
        $academic_groups = $this->Academic_group_model->get_groups_with_staff_counts($this->school_id, true);

        $this->render('pages/staff/designations', array(
            'title'            => 'Designations',
            'page_key'         => 'designations',
            'breadcrumb'       => array('Staff Management', 'Designations'),
            'designations'     => $designations,
            'categories'       => $categories,
            'academic_groups'  => $academic_groups,
            'can_create'       => $can_create,
            'can_edit'         => $can_edit,
            'can_delete'       => $can_delete,
            'can_create_group' => $can_create_group,
            'can_edit_group'   => $can_edit_group,
        ));
    }

    public function departments()
    {
        redirect('staff/designations');
    }

    /* AJAX: Get single designation for Edit Modal */
    public function ajax_get_designation($id = null)
    {
        if (!$this->rbac->has_permission('staff.view')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Unauthorized access.'
            )));
        }

        $id = (int)($id ?: $this->input->get('id'));
        $desig = $this->Designation_model->get_by_id($id, $this->school_id);
        if (!$desig) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Designation not found or access denied.'
            )));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => true,
            'data'   => $desig
        )));
    }

    /* AJAX: Save (Add or Edit) Designation */
    public function ajax_save_designation()
    {
        $id = (int)$this->input->post('designation_id');
        $required_perm = ($id > 0) ? 'staff.edit' : 'staff.create';

        if (!$this->rbac->has_permission($required_perm)) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Permission denied for this action.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $designation_name = trim($this->input->post('designation_name'));
        $category         = trim($this->input->post('category')) ?: 'Teaching';
        $description      = trim($this->input->post('description'));
        $status           = $this->input->post('status') !== null ? ((int)$this->input->post('status') ? 1 : 0) : 1;

        if (empty($designation_name)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Designation name is required.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Check duplicate name within current school
        if ($this->Designation_model->check_name_exists($designation_name, ($id > 0 ? $id : null), $this->school_id)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'A designation with this name already exists in your school.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        if ($id > 0) {
            // Edit
            $existing = $this->Designation_model->get_by_id($id, $this->school_id);
            if (!$existing) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status'          => false,
                    'message'         => 'Designation not found or belongs to another school.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                )));
            }

            $update_data = array(
                'designation_name' => $designation_name,
                'category'         => $category,
                'description'      => $description,
                'status'           => $status
            );

            $this->Designation_model->update($id, $update_data, $this->school_id);

            $msg = 'Designation updated successfully.';
        } else {
            // Add
            $id = $this->Designation_model->insert(array(
                'school_id'        => $this->school_id,
                'designation_name' => $designation_name,
                'category'         => $category,
                'description'      => $description,
                'status'           => $status,
                'created_at'       => date('Y-m-d H:i:s')
            ));

            $msg = 'Designation created successfully.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'          => true,
            'message'         => $msg,
            'designation_id'  => $id,
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        )));
    }

    /* AJAX: Delete Designation */
    public function ajax_delete_designation()
    {
        if (!$this->rbac->has_permission('staff.delete')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Permission denied to delete designations.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $id = (int)$this->input->post('designation_id');
        if ($id <= 0) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Invalid designation ID.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $desig = $this->Designation_model->get_by_id($id, $this->school_id);
        if (!$desig) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Designation not found or belongs to another school.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Dependency protection: Staff
        $staff_count = $this->Designation_model->count_staff_usage($id, $this->school_id);
        if ($staff_count > 0) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Cannot delete designation: ' . $staff_count . ' active staff member(s) are currently assigned this designation. Please reassign them first or deactivate the designation.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Dependency protection: Users
        $user_count = $this->Designation_model->count_user_usage($id, $this->school_id);
        if ($user_count > 0) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Cannot delete designation: ' . $user_count . ' active user account(s) are linked to this designation. Please reassign them first or deactivate the designation.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $res = $this->Designation_model->soft_delete($id, $this->school_id);
        if (!$res) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Failed to delete designation.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'          => true,
            'message'         => 'Designation deleted successfully.',
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        )));
    }

    /* AJAX: Get single Department / Group for Edit Modal */
    public function ajax_get_academic_group($id = null)
    {
        if (!$this->rbac->has_permission('staff.view')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Unauthorized access.'
            )));
        }

        $id = (int)($id ?: $this->input->get('id'));
        $group = $this->Academic_group_model->get_by_id($id, $this->school_id);
        if (!$group) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => false,
                'message' => 'Department / Group not found or access denied.'
            )));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => true,
            'data'   => $group
        )));
    }

    /* AJAX: Save (Add or Edit) Department / Group */
    public function ajax_save_academic_group()
    {
        $id = (int)$this->input->post('academic_group_id');
        $is_super_admin = $this->rbac->is_super_admin();
        $can_create = $is_super_admin || $this->rbac->has_permission('academic_groups.create') || $this->rbac->has_permission('academics.manage') || $this->rbac->has_permission('staff.create');
        $can_edit   = $is_super_admin || $this->rbac->has_permission('academic_groups.edit') || $this->rbac->has_permission('academics.manage') || $this->rbac->has_permission('staff.edit');

        $allowed = ($id > 0) ? $can_edit : $can_create;
        if (!$allowed) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Permission denied for this action.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        $group_name      = trim($this->input->post('group_name'));
        $description     = trim($this->input->post('description'));
        $attendance_type = strtolower(trim($this->input->post('attendance_type') ?: 'daily'));
        if (!in_array($attendance_type, array('daily', 'period'))) {
            $attendance_type = 'daily';
        }
        $display_order   = (int)$this->input->post('display_order') ?: 0;
        $status          = $this->input->post('status') !== null ? ((int)$this->input->post('status') ? 1 : 0) : 1;
        $classes_input   = $this->input->post('classes', TRUE);

        if (empty($group_name)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'Department / Group name is required.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        // Check duplicate name within current school
        if ($this->Academic_group_model->check_duplicate($group_name, ($id > 0 ? $id : null))) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'          => false,
                'message'         => 'A department / group with this name already exists in your school.',
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_hash'       => $this->security->get_csrf_hash()
            )));
        }

        if ($id > 0) {
            $existing = $this->Academic_group_model->get_by_id($id, $this->school_id);
            if (!$existing) {
                return $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status'          => false,
                    'message'         => 'Department / Group not found or belongs to another school.',
                    'csrf_token_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash'       => $this->security->get_csrf_hash()
                )));
            }

            $update_data = array(
                'group_name'      => $group_name,
                'description'     => $description,
                'attendance_type' => $attendance_type,
                'display_order'   => $display_order,
                'status'          => $status
            );

            $this->Academic_group_model->update($id, $update_data, $classes_input);
            $msg = 'Department / Group updated successfully.';
        } else {
            $insert_data = array(
                'school_id'       => $this->school_id,
                'group_name'      => $group_name,
                'description'     => $description,
                'attendance_type' => $attendance_type,
                'display_order'   => $display_order,
                'status'          => $status
            );

            $id = $this->Academic_group_model->insert($insert_data, $classes_input);
            $msg = 'Department / Group created successfully.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'            => true,
            'message'           => $msg,
            'academic_group_id' => $id,
            'csrf_token_name'   => $this->security->get_csrf_token_name(),
            'csrf_hash'         => $this->security->get_csrf_hash()
        )));
    }

    /* =========================================================================
       9. Staff Documents
       ========================================================================= */
    public function documents()
    {
        $this->require_permission('staff.view');

        $staff_id  = $this->input->get('staff_id');
        $doc_type  = $this->input->get('document_type');

        $staff_list     = $this->Staff_model->get_all(array('status' => 1, 'school_id' => $this->school_id));
        $document_types = $this->Staff_document_type_model->get_active_types($this->school_id);

        $this->render('pages/staff/documents', array(
            'title'          => 'Staff Documents',
            'page_key'       => 'staff_documents',
            'breadcrumb'     => array('Staff Management', 'Staff Documents'),
            'staff_list'     => $staff_list,
            'document_types' => $document_types,
        ));
    }

    /**
     * Server-side DataTables endpoint for Staff Documents.
     */
    public function ajax_documents_list()
    {
        $this->require_permission('staff.view');

        $draw          = (int)$this->input->post('draw');
        $start         = (int)$this->input->post('start');
        $length        = (int)$this->input->post('length');
        $search_val    = $this->input->post('search')['value'] ?? $this->input->post('search');
        $order_arr     = $this->input->post('order');
        $order_col_idx = isset($order_arr[0]['column']) ? (int)$order_arr[0]['column'] : 0;
        $order_dir     = isset($order_arr[0]['dir']) ? $order_dir = $order_arr[0]['dir'] : 'DESC';

        $filters = array(
            'staff_id'      => $this->input->post('staff_id') ?: $this->input->get('staff_id'),
            'document_type' => $this->input->post('document_type') ?: $this->input->get('document_type'),
            'search'        => $search_val,
            'school_id'     => $this->school_id,
        );

        $records_total    = $this->Staff_model->get_documents_count_all($this->school_id);
        $records_filtered = $this->Staff_model->count_documents_filtered($filters);
        $docs_list        = $this->Staff_model->get_documents_datatables_data($filters, $length, $start, $order_col_idx, $order_dir);

        $data = array();
        foreach ($docs_list as $doc) {
            $titleCol = '<div class="flex items-center gap-2 font-semibold text-on-surface whitespace-nowrap">' .
                '<span class="material-symbols-outlined text-primary text-[20px]">description</span>' .
                html_escape($doc->document_name) .
            '</div>';

            $typeCol = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface whitespace-nowrap">' .
                html_escape($doc->document_type) .
            '</span>';

            $memberCol = '<a href="' . site_url('staff/profile/' . $doc->staff_id) . '" class="hover:underline text-primary font-medium whitespace-nowrap">' .
                html_escape($doc->full_name) .
            '</a>';

            $codeCol = '<span class="font-mono text-on-surface-variant whitespace-nowrap">' . html_escape($doc->employee_code ?: '—') . '</span>';
            $desigCol = '<span class="text-on-surface whitespace-nowrap">' . html_escape($doc->designation_name ?: '—') . '</span>';
            $dateCol = '<span class="text-on-surface-variant whitespace-nowrap">' . date('d M Y', strtotime($doc->created_at)) . '</span>';

            $actionsCol = '<div class="flex items-center justify-end gap-1.5 whitespace-nowrap">' .
                '<a href="' . site_url('staff/view_document/' . $doc->document_id) . '" target="_blank" class="px-2.5 py-1 rounded bg-surface-container-high text-on-surface text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">visibility</span>View</a>' .
                '<a href="' . site_url('staff/download_document/' . $doc->document_id) . '" class="px-2.5 py-1 rounded bg-surface-container-high text-secondary text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">download</span>Download</a>' .
                '<a href="' . site_url('staff/delete_document/' . $doc->document_id . '?redirect_to=' . urlencode(site_url('staff/documents'))) . '" onclick="return confirm(\'Delete this staff document?\')" class="px-2.5 py-1 rounded text-error hover:bg-error-container/20 transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">delete</span>Delete</a>' .
            '</div>';

            $data[] = array(
                $titleCol,
                $typeCol,
                $memberCol,
                $codeCol,
                $desigCol,
                $dateCol,
                $actionsCol
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

    public function upload_document()
    {
        $this->require_permission('staff.edit');

        $staff_id = (int)$this->input->post('staff_id');
        $type_id  = (int)$this->input->post('document_type_id');
        $doc_name = trim($this->input->post('document_name'));
        $redirect = $this->input->post('redirect_to') ?: ('staff/profile/' . $staff_id);

        $targetStaff = $this->Staff_model->get_by_id($staff_id, $this->school_id);
        if (!$targetStaff) {
            $this->session->set_flashdata('error', 'Staff member not found or access denied.');
            redirect('staff');
            return;
        }

        $dtObj = $this->Staff_document_type_model->get_by_id($type_id, $this->school_id);
        $typeName = $dtObj ? $dtObj->document_name : ($this->input->post('document_type') ?: 'Other');
        if (empty($doc_name)) {
            $doc_name = $typeName;
        }

        if (!empty($staff_id) && !empty($_FILES['document_file']['name']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
            $origName = $_FILES['document_file']['name'];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExtensions)) {
                $uploadDir = FCPATH . 'uploads/staff_docs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $safeName = 'doc_' . $staff_id . '_' . ($type_id ?: 'other') . '_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
                if (move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadDir . $safeName)) {
                    $mimeType = $_FILES['document_file']['type'] ?: 'application/octet-stream';
                    $fileSize = $_FILES['document_file']['size'];

                    $this->Staff_model->add_document(array(
                        'school_id'        => $this->school_id,
                        'staff_id'         => $staff_id,
                        'document_type_id' => $type_id ?: NULL,
                        'document_type'    => $typeName,
                        'document_name'    => $doc_name,
                        'file_name'        => $origName,
                        'file_path'        => 'uploads/staff_docs/' . $safeName,
                        'file_type'        => $mimeType,
                        'file_size'        => $fileSize,
                        'mime_type'        => $mimeType,
                        'uploaded_by'      => $this->current_user->user_id ?? 1,
                        'status'           => 1,
                        'is_deleted'       => 'n',
                        'created_at'       => date('Y-m-d H:i:s'),
                    ));
                    $this->session->set_flashdata('success', 'Staff document uploaded successfully!');
                }
            } else {
                $this->session->set_flashdata('error', 'Invalid file type. Allowed formats: PDF, JPG, PNG, DOC, DOCX.');
            }
        }

        redirect($redirect);
    }

    public function delete_document($id = NULL)
    {
        $this->require_permission('staff.edit');

        $redirect = $this->input->get('redirect_to') ?: 'staff/documents';
        if (!empty($id)) {
            $doc = $this->Staff_model->get_document_by_id((int)$id, $this->school_id);
            if ($doc) {
                $this->Staff_model->delete_document((int)$id, $this->school_id);
                $this->session->set_flashdata('success', 'Staff document removed.');
            } else {
                $this->session->set_flashdata('error', 'Document not found or access denied.');
            }
        }
        redirect($redirect);
    }

    /* =========================================================================
       Secure Document View & Download Handlers
       ========================================================================= */
    public function view_document($document_id = NULL)
    {
        $this->require_permission('staff.view');

        if (empty($document_id)) {
            show_404();
            return;
        }

        $doc = $this->Staff_model->get_document_by_id((int)$document_id, $this->school_id);
        if (!$doc || empty($doc->file_path)) {
            show_404();
            return;
        }

        $filePath = FCPATH . $doc->file_path;
        if (!file_exists($filePath)) {
            show_error('Document file not found on server.', 404, '404 File Not Found');
            return;
        }

        $mime = $doc->mime_type ?: mime_content_type($filePath) ?: 'application/octet-stream';
        $fileName = $doc->file_name ?: basename($filePath);

        // Security headers
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        
        readfile($filePath);
        exit;
    }

    public function download_document($document_id = NULL)
    {
        $this->require_permission('staff.view');

        if (empty($document_id)) {
            show_404();
            return;
        }

        $doc = $this->Staff_model->get_document_by_id((int)$document_id, $this->school_id);
        if (!$doc || empty($doc->file_path)) {
            show_404();
            return;
        }

        $filePath = FCPATH . $doc->file_path;
        if (!file_exists($filePath)) {
            show_error('Document file not found on server.', 404, '404 File Not Found');
            return;
        }

        $fileName = $doc->file_name ?: basename($filePath);

        // Download headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        
        readfile($filePath);
        exit;
    }

    /* =========================================================================
       10. Teacher Workload Management
       ========================================================================= */
    public function workload()
    {
        $this->require_permission('staff.view');

        if ($this->input->method() === 'post') {
            $staff_id = (int)$this->input->post('staff_id');
            $class_id = (int)$this->input->post('class_id');
            // Ensure selected staff is a teacher belonging to current school
            $staffMember = $this->Staff_model->get_by_id($staff_id, $this->school_id);
            if ($staffMember && $staffMember->staff_type === 'teacher') {
                $group_id = (int)$this->input->post('academic_group_id');
                if ($group_id > 0) {
                    $tg_check = $this->Staff_model->validate_teacher_group($staff_id, $group_id, $this->school_id);
                    if (!$tg_check['valid']) {
                        $this->session->set_flashdata('error', $tg_check['error']);
                        redirect('staff/workload');
                        return;
                    }
                }

                // Server-side validation: Teacher and class must belong to the same group
                $group_check = $this->Staff_model->validate_teacher_class_group($staff_id, $class_id, $this->school_id);
                if (!$group_check['valid']) {
                    $this->session->set_flashdata('error', $group_check['error']);
                    redirect('staff/workload');
                    return;
                }

                $workload_id = $this->input->post('workload_id');
                $workload_data = array(
                    'school_id'        => $this->school_id,
                    'staff_id'         => $staff_id,
                    'academic_year_id' => $this->input->post('academic_year_id') ?: ($this->academic_year_id ?: NULL),
                    'subject_id'       => $this->input->post('subject_id'),
                    'class_id'         => $class_id,
                    'division_id'      => $this->input->post('division_id') ?: ($this->input->post('section_id') ?: NULL),
                    'periods'          => $this->input->post('periods') ? intval($this->input->post('periods')) : 5,
                    'working_days'     => $this->input->post('working_days') ?: 'Mon,Tue,Wed,Thu,Fri',
                    'remarks'          => $this->input->post('remarks'),
                    'status'           => 1,
                );

                if (!empty($workload_id)) {
                    $existing = $this->Staff_model->get_workload_by_id((int)$workload_id, $this->school_id);
                    if ($existing) {
                        $this->Staff_model->update_workload((int)$workload_id, $workload_data, $this->school_id);
                        $this->session->set_flashdata('success', 'Teacher workload updated successfully!');
                    } else {
                        $this->session->set_flashdata('error', 'Workload record not found or access denied.');
                    }
                } else {
                    $workload_data['created_at'] = date('Y-m-d H:i:s');
                    $this->Staff_model->add_workload($workload_data);
                    $this->session->set_flashdata('success', 'Teacher workload assigned successfully!');
                }
            } else {
                $this->session->set_flashdata('error', 'Workload can only be assigned to teaching staff of this school.');
            }
            redirect('staff/workload');
        }

        $filters = array(
            'school_id'        => $this->school_id,
            'staff_id'         => $this->input->get('staff_id'),
            'academic_year_id' => $this->input->get('academic_year_id'),
            'class_id'         => $this->input->get('class_id'),
            'division_id'      => $this->input->get('division_id') ?: $this->input->get('section_id'),
            'subject_id'       => $this->input->get('subject_id'),
        );

        $workloads = $this->Staff_model->get_workloads($filters, $this->school_id);
        $teachers  = $this->Staff_model->get_teachers(array('school_id' => $this->school_id));
        $years     = $this->Academic_year_model->get_all($this->school_id);
        $classes   = $this->Class_model->get_all(NULL, $this->school_id);
        $divisions = $this->Division_model->get_all(NULL, $this->school_id);
        $subjects  = $this->Subject_model->get_all(NULL, $this->school_id);

        $groups    = $this->Academic_group_model->get_all();

        $this->render('pages/staff/workload', array(
            'title'       => 'Teacher Workload',
            'page_key'    => 'teacher_workload',
            'breadcrumb'  => array('Staff Management', 'Teacher Workload'),
            'workloads'   => $workloads,
            'teachers'    => $teachers,
            'years'       => $years,
            'classes'     => $classes,
            'groups'      => $groups,
            'divisions'   => $divisions,
            'sections'    => $divisions,
            'subjects'    => $subjects,
        ));
    }

    public function ajax_get_teachers_by_group($group_id = null)
    {
        $this->require_permission('staff.view');
        $gid = $group_id ?: $this->input->get_post('academic_group_id') ?: $this->input->get_post('group_id');
        $teachers = $gid ? $this->Staff_model->get_teachers_by_group((int)$gid, $this->school_id) : array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'           => true,
                'status'            => true,
                'academic_group_id' => $gid ? (int)$gid : null,
                'teachers'          => $teachers,
                'csrf_token_name'   => $this->security->get_csrf_token_name(),
                'csrf_hash'         => $this->security->get_csrf_hash()
            )));
    }

    public function ajax_get_teacher_group($staff_id = null)
    {
        $this->require_permission('staff.view');
        $sid = $staff_id ?: $this->input->get_post('staff_id');
        $group = $this->Staff_model->get_teacher_group($sid, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'           => true,
                'staff_id'          => (int)$sid,
                'academic_group_id' => $group ? (int)$group->academic_group_id : null,
                'group_name'        => $group ? $group->group_name : null,
            )));
    }

    public function ajax_get_classes_by_teacher($staff_id = null)
    {
        $this->require_permission('staff.view');
        $sid = $staff_id ?: $this->input->get_post('staff_id');
        $group = $this->Staff_model->get_teacher_group($sid, $this->school_id);

        if (!$group || empty($group->academic_group_id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'           => false,
                    'error'             => 'Teacher is not assigned to any academic group.',
                    'academic_group_id' => null,
                    'group_name'        => null,
                    'classes'           => array()
                )));
            return;
        }

        $classes = $this->Class_model->get_classes_by_group($group->academic_group_id, $this->school_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'           => true,
                'academic_group_id' => (int)$group->academic_group_id,
                'group_name'        => $group->group_name,
                'classes'           => $classes
            )));
    }

    public function delete_workload($id = NULL)
    {
        $this->require_permission('staff.edit');

        $redirect = $this->input->get('redirect_to') ?: 'staff/workload';
        if (!empty($id)) {
            $existing = $this->Staff_model->get_workload_by_id((int)$id, $this->school_id);
            if ($existing) {
                $this->Staff_model->delete_workload((int)$id, $this->school_id);
                $this->session->set_flashdata('success', 'Workload record removed.');
            } else {
                $this->session->set_flashdata('error', 'Workload record not found or access denied.');
            }
        }
        redirect($redirect);
    }

    /* =========================================================================
       11. Staff Daily Attendance
       ========================================================================= */
    public function attendance()
    {
        $this->require_permission('staff.view');

        $date = $this->input->get('date') ?: date('Y-m-d');

        if ($this->input->method() === 'post') {
            $postDate = $this->input->post('attendance_date') ?: $date;
            $records  = $this->input->post('attendance');
            $user_id  = (int)$this->session->userdata('user_id');
            $this->Staff_model->save_attendance_batch($postDate, $records, $user_id, $this->school_id);
            $this->session->set_flashdata('success', 'Staff attendance saved successfully for ' . date('d M Y', strtotime($postDate)));
            redirect('staff/attendance?date=' . $postDate);
        }

        $attendance_list = $this->Staff_model->get_attendance_for_date($date, $this->school_id);

        $this->render('pages/staff/attendance', array(
            'title'           => 'Staff Attendance',
            'page_key'        => 'staff_attendance',
            'breadcrumb'      => array('Staff Management', 'Staff Attendance'),
            'attendance_list' => $attendance_list,
            'date'            => $date,
        ));
    }

    /* =========================================================================
       12. Staff Leave Management
       ========================================================================= */
    public function leave()
    {
        $this->require_permission('staff.view');

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            if ($action === 'apply') {
                $staff_id = $this->input->post('staff_id');
                if (empty($staff_id)) {
                    $this->session->set_flashdata('error', 'Please select a staff member.');
                    redirect('staff/leave');
                    return;
                }
                $staff = $this->Staff_model->get_by_id((int)$staff_id, $this->school_id);
                if (!$staff) {
                    $this->session->set_flashdata('error', 'Staff member not found or access denied.');
                    redirect('staff/leave');
                    return;
                }
                $from = $this->input->post('from_date');
                $to   = $this->input->post('to_date');
                if (strtotime($from) > strtotime($to)) {
                    $this->session->set_flashdata('error', 'From Date cannot be after To Date.');
                } else {
                    $days = max(1, round((strtotime($to) - strtotime($from)) / (60 * 60 * 24)) + 1);
                    $this->Staff_model->apply_leave(array(
                        'school_id'    => $this->school_id,
                        'staff_id'     => (int)$staff_id,
                        'leave_type'   => $this->input->post('leave_type'),
                        'from_date'    => $from,
                        'to_date'      => $to,
                        'total_days'   => $days,
                        'reason'       => $this->input->post('reason'),
                        'status'       => 'Pending',
                        'applied_date' => date('Y-m-d'),
                        'created_at'   => date('Y-m-d H:i:s')
                    ));
                    $this->session->set_flashdata('success', 'Leave request submitted successfully!');
                }
            } elseif ($action === 'update_status') {
                $leave_id = $this->input->post('leave_id');
                $status   = $this->input->post('status');
                $remarks  = $this->input->post('remarks') ?: '';
                $this->Staff_model->update_leave_status((int)$leave_id, $status, $this->session->userdata('user_id'), $remarks, $this->school_id);
                $this->session->set_flashdata('success', 'Leave request ' . strtolower($status) . ' successfully.');
            }
            redirect('staff/leave');
        }

        $filters = array(
            'school_id' => $this->school_id,
            'status'    => $this->input->get('status'),
            'staff_id'  => $this->input->get('staff_id'),
        );

        $leaves     = $this->Staff_model->get_leaves($filters, $this->school_id);
        $staff_list = $this->Staff_model->get_all(array('status' => 1, 'school_id' => $this->school_id));

        $this->render('pages/staff/leave', array(
            'title'      => 'Staff Leave Management',
            'page_key'   => 'staff_leave',
            'breadcrumb' => array('Staff Management', 'Leave Management'),
            'leaves'     => $leaves,
            'staff_list' => $staff_list,
        ));
    }
}
