<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setting_model');
        $this->load->model('Staff_document_type_model');
        $this->load->model('Document_design_model');
        $this->load->library('document_design_service');
    }

    public function index()
    {
        $this->require_permission('settings.view');

        if ($this->input->method() === 'post') {
            $this->require_permission('settings.edit');
            $data = array(
                'school_name'      => $this->input->post('school_name', TRUE),
                'school_code'      => $this->input->post('school_code', TRUE),
                'established_year' => $this->input->post('established_year', TRUE),
                'principal_name'   => $this->input->post('principal_name', TRUE),
                'phone'            => $this->input->post('phone', TRUE),
                'email'            => $this->input->post('email', TRUE),
                'website'          => $this->input->post('website', TRUE),
                'address'          => $this->input->post('address', TRUE),
                'description'      => $this->input->post('description', TRUE),
            );
            $this->Setting_model->update_settings($data);
            $this->session->set_flashdata('success', 'School settings updated successfully.');
            redirect('settings');
            return;
        }

        $settings = $this->Setting_model->get_settings();

        $this->render('pages/settings/index', array(
            'title'    => 'School Settings',
            'page_key' => 'settings',
            'settings' => $settings,
        ));
    }

    /* =========================================================================
       Staff Document Settings (Super Admin Only)
       ========================================================================= */
    public function staff_documents()
    {
        $this->require_permission('settings.edit');

        $document_types = $this->Staff_document_type_model->get_all_for_settings();

        $this->render('pages/settings/staff_documents', array(
            'title'          => 'Staff Document Settings',
            'page_key'       => 'staff-document-settings',
            'breadcrumb'     => array('Login2 Settings', 'Staff Document'),
            'document_types' => $document_types,
        ));
    }

    public function staff_documents_add()
    {
        $this->require_permission('settings.edit');

        $doc_name = trim($this->input->post('document_name', TRUE));
        $desc     = trim($this->input->post('description', TRUE));
        $status   = $this->input->post('status') === 'Inactive' ? 'Inactive' : 'Active';

        if (empty($doc_name)) {
            $this->session->set_flashdata('error', 'Document name is required.');
            redirect('settings/staff_documents');
            return;
        }

        // Get max order via model
        $maxOrder = $this->Staff_document_type_model->get_max_display_order();

        $this->Staff_document_type_model->insert(array(
            'document_name' => $doc_name,
            'description'   => $desc ?: NULL,
            'status'        => $status,
            'display_order' => $maxOrder + 1,
            'created_by'    => $this->current_user->user_id ?? 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'is_deleted'    => 'n',
        ));

        $this->session->set_flashdata('success', 'Staff document requirement "' . html_escape($doc_name) . '" added successfully.');
        redirect('settings/staff_documents');
    }

    public function staff_documents_edit($id = NULL)
    {
        $this->require_permission('settings.edit');

        if (empty($id)) {
            redirect('settings/staff_documents');
            return;
        }

        $docType = $this->Staff_document_type_model->get_by_id($id);
        if (!$docType) {
            $this->session->set_flashdata('error', 'Document type not found.');
            redirect('settings/staff_documents');
            return;
        }

        $doc_name = trim($this->input->post('document_name', TRUE));
        $desc     = trim($this->input->post('description', TRUE));
        $status   = $this->input->post('status') === 'Inactive' ? 'Inactive' : 'Active';

        if (empty($doc_name)) {
            $this->session->set_flashdata('error', 'Document name cannot be empty.');
            redirect('settings/staff_documents');
            return;
        }

        $this->Staff_document_type_model->update($id, array(
            'document_name' => $doc_name,
            'description'   => $desc ?: NULL,
            'status'        => $status,
        ));

        $this->session->set_flashdata('success', 'Staff document "' . html_escape($doc_name) . '" updated successfully.');
        redirect('settings/staff_documents');
    }

    public function staff_documents_delete($id = NULL)
    {
        $this->require_permission('settings.edit');

        if (!empty($id)) {
            $docType = $this->Staff_document_type_model->get_by_id($id);
            if ($docType) {
                $this->Staff_document_type_model->soft_delete($id, $this->current_user->user_id ?? 1);
                $this->session->set_flashdata('success', 'Document requirement "' . html_escape($docType->document_name) . '" has been removed from future forms. Existing uploaded documents remain preserved.');
            }
        }

        redirect('settings/staff_documents');
    }

    public function staff_documents_toggle($id = NULL)
    {
        $this->require_permission('settings.edit');

        if (!empty($id)) {
            $this->Staff_document_type_model->toggle_status($id);
            $this->session->set_flashdata('success', 'Document requirement status updated.');
        }

        redirect('settings/staff_documents');
    }

    public function academic_groups()
    {
        redirect('academics/academic_groups');
    }

    /* =========================================================================
       Document Design Settings (Super Admin & School Admin)
       ========================================================================= */

    /**
     * Helper to resolve the authorized school_id for document design.
     * Super Admin can switch via GET/POST param 'school_id'.
     * School Admin is strictly pinned to their session school_id.
     *
     * @return int
     */
    private function _resolve_target_school_id()
    {
        if ($this->rbac->is_super_admin()) {
            $req_school_id = (int)($this->input->get_post('school_id', TRUE) ?: 0);
            if ($req_school_id > 0) {
                return $req_school_id;
            }
        }
        if (!empty($this->school_id) && (int)$this->school_id > 0) {
            return (int)$this->school_id;
        }
        $this->load->model('School_model');
        $default = $this->School_model->get_default_school();
        return $default ? (int)$default->id : 1;
    }

    /**
     * Document Design Management Page
     */
    public function document_design()
    {
        $this->require_permission('document_design.view');

        $is_super_admin = $this->rbac->is_super_admin();
        $target_school_id = $this->_resolve_target_school_id();

        // Get target school details
        $this->load->model('School_model');
        $target_school = $this->School_model->get_by_id($target_school_id);
        if (!$target_school) {
            $default = $this->School_model->get_default_school();
            $target_school = $default;
            $target_school_id = $default ? (int)$default->id : 1;
        }

        $all_schools = $is_super_admin ? $this->School_model->get_all() : [];
        $doc_types = $this->document_design_service->get_document_types();
        $configured_designs = $this->Document_design_model->get_all_for_school($target_school_id);

        // Pre-resolve status for each document type
        $designs_summary = [];
        $default_design = $this->document_design_service->resolve_design($target_school_id, 'default');

        foreach ($doc_types as $key => $meta) {
            $design = $this->document_design_service->resolve_design($target_school_id, $key);
            $raw = $configured_designs[$key] ?? null;

            $designs_summary[$key] = [
                'meta'               => $meta,
                'raw'                => $raw,
                'resolved'           => $design,
                'is_configured'      => ($raw && (!empty($raw->header_image) || !empty($raw->footer_image))),
                'has_header'         => $design->has_header,
                'has_footer'         => $design->has_footer,
                'header_url'         => $design->header_url,
                'footer_url'         => $design->footer_url,
                'is_fallback_header' => $design->is_fallback_header,
                'is_fallback_footer' => $design->is_fallback_footer,
            ];
        }

        $this->render('pages/settings/document_design', [
            'title'              => 'Document Design Settings',
            'page_key'           => 'document-design',
            'breadcrumb'         => ['Login2 Settings', 'Document Design'],
            'is_super_admin'     => $is_super_admin,
            'target_school_id'   => $target_school_id,
            'target_school'      => $target_school,
            'all_schools'        => $all_schools,
            'designs_summary'      => $designs_summary,
            'default_design'       => $default_design,
            'id_card_field_config' => $this->document_design_service->get_id_card_field_config($target_school_id),
        ]);
    }

    /**
     * Save / Upload Document Design (Header / Footer Image)
     */
    public function document_design_save()
    {
        $this->require_permission('document_design.edit');

        $target_school_id = $this->_resolve_target_school_id();
        $document_type = trim($this->input->post('document_type', TRUE));
        $status = $this->input->post('status') === 'Inactive' ? 'Inactive' : 'Active';

        if (!$this->document_design_service->is_valid_type($document_type)) {
            $this->session->set_flashdata('error', 'Invalid document type selected.');
            redirect('settings/document_design?school_id=' . $target_school_id);
            return;
        }

        $user_id = $this->current_user->user_id ?? 1;
        $upload_dir_abs = $this->document_design_service->ensure_upload_dir($target_school_id, $document_type);

        $existing = $this->Document_design_model->get_by_type($target_school_id, $document_type);
        $header_filename = $existing->header_image ?? null;
        $footer_filename = $existing->footer_image ?? null;

        $errors = [];

        // Helper closure to process cropped base64 or validated uploaded file
        $service = $this->document_design_service;
        $input = $this->input;

        $handle_image_field = function($field_type) use ($upload_dir_abs, &$errors, $service, $input, $document_type) {
            $cropped_field = $field_type . '_image_cropped';
            $file_field = $field_type . '_image';
            $dim = $service->get_required_dimensions($field_type, $document_type);
            $label = $dim['label'];

            $cropped_val = $input->post($cropped_field);
            if (!empty($cropped_val)) {
                // Process cropped base64
                $res = $service->process_cropped_base64($cropped_val, $field_type, $upload_dir_abs, $document_type);
                if (!$res['success']) {
                    $errors[] = $res['error'];
                    return null;
                }
                return $res['filename'];
            }

            // Fallback to direct $_FILES if uploaded (requires exact dimensions)
            if (!empty($_FILES[$file_field]['name']) && is_uploaded_file($_FILES[$file_field]['tmp_name'])) {
                $tmp_path = $_FILES[$file_field]['tmp_name'];
                $file_size = $_FILES[$file_field]['size'];

                if ($file_size > 10 * 1024 * 1024) {
                    $errors[] = "$label file size must be less than 10MB.";
                    return null;
                }

                $img_info = @getimagesize($tmp_path);
                if ($img_info === false) {
                    $errors[] = "$label is not a valid image.";
                    return null;
                }

                $w = (int)$img_info[0];
                $h = (int)$img_info[1];

                // Verify exact dimensions
                if ($w !== (int)$dim['width'] || $h !== (int)$dim['height']) {
                    $errors[] = "$label must be cropped to exactly {$dim['size_label']}. (Received: {$w} × {$h} px). Please use the crop tool.";
                    return null;
                }

                // Extension & MIME validation
                $allowed_mimes = [
                    'image/png'   => 'png',
                    'image/jpeg'  => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/webp'  => 'webp',
                ];
                $mime = mime_content_type($tmp_path);
                if (!isset($allowed_mimes[$mime])) {
                    $errors[] = "$label must be a valid PNG, JPG, or WEBP image.";
                    return null;
                }

                $canonical_ext = $allowed_mimes[$mime];
                $prefix = ($document_type === 'id_card')
                    ? ((in_array($field_type, ['footer', 'back'])) ? 'back' : 'front')
                    : ((in_array($field_type, ['footer', 'back'])) ? 'footer' : 'header');
                $new_filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $canonical_ext;
                $dest_path = rtrim($upload_dir_abs, '/\\') . DIRECTORY_SEPARATOR . $new_filename;

                if (!@move_uploaded_file($tmp_path, $dest_path)) {
                    $errors[] = "Failed to save $label to disk.";
                    return null;
                }

                return $new_filename;
            }

            return null;
        };

        // Process Header / Front
        $new_header = $handle_image_field('header');
        if ($new_header === null && $document_type === 'id_card') {
            $new_header = $handle_image_field('front');
        }
        if ($new_header !== null) {
            if (!empty($header_filename) && file_exists($upload_dir_abs . $header_filename)) {
                @unlink($upload_dir_abs . $header_filename);
            }
            $header_filename = $new_header;
        }

        // Process Footer / Back
        $new_footer = $handle_image_field('footer');
        if ($new_footer === null && $document_type === 'id_card') {
            $new_footer = $handle_image_field('back');
        }
        if ($new_footer !== null) {
            if (!empty($footer_filename) && file_exists($upload_dir_abs . $footer_filename)) {
                @unlink($upload_dir_abs . $footer_filename);
            }
            $footer_filename = $new_footer;
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('settings/document_design?school_id=' . $target_school_id);
            return;
        }

        // Save to DB
        $save_data = [
            'header_image' => $header_filename,
            'footer_image' => $footer_filename,
            'status'       => $status,
        ];
        $this->Document_design_model->save_design($target_school_id, $document_type, $save_data, $user_id);

        $doc_meta = $this->document_design_service->get_document_types()[$document_type] ?? null;
        $doc_label = $doc_meta['label'] ?? $document_type;
        $this->session->set_flashdata('success', 'Document design for "' . html_escape($doc_label) . '" saved successfully.');

        redirect('settings/document_design?school_id=' . $target_school_id);
    }

    /**
     * Remove Header or Footer Image (or Front / Back for ID Card)
     */
    public function document_design_remove_image()
    {
        $this->require_permission('document_design.edit');

        $target_school_id = $this->_resolve_target_school_id();
        $document_type = trim($this->input->post('document_type', TRUE));
        $raw_image_type = trim($this->input->post('image_type', TRUE)); // 'header', 'footer', 'front', 'back'

        $valid_types = ['header', 'footer', 'front', 'back'];
        if (!$this->document_design_service->is_valid_type($document_type) || !in_array($raw_image_type, $valid_types)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['status' => false, 'message' => 'Invalid parameters.']));
            return;
        }

        $image_type = (in_array($raw_image_type, ['footer', 'back'])) ? 'footer' : 'header';
        $label = ($document_type === 'id_card')
            ? (($image_type === 'footer') ? 'Back' : 'Front')
            : ucfirst($image_type);

        $user_id = $this->current_user->user_id ?? 1;
        $old_file = $this->Document_design_model->remove_image($target_school_id, $document_type, $image_type, $user_id);

        if ($old_file) {
            $upload_dir_abs = $this->document_design_service->ensure_upload_dir($target_school_id, $document_type);
            if (file_exists($upload_dir_abs . $old_file)) {
                @unlink($upload_dir_abs . $old_file);
            }
        }

        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => true,
                'message' => $label . ' image removed successfully.',
            ]));
            return;
        }

        $this->session->set_flashdata('success', $label . ' image removed successfully.');
        redirect('settings/document_design?school_id=' . $target_school_id);
    }

    /**
     * Visual Preview of Document Design (Modal / Standalone)
     */
    public function document_design_preview($school_id = null, $document_type = null)
    {
        $this->require_permission('document_design.view');

        $school_id = (int)($school_id ?: $this->_resolve_target_school_id());
        if (!$this->rbac->is_super_admin()) {
            $school_id = (int)$this->school_id;
        }

        $document_type = $document_type ?: $this->input->get('document_type', TRUE);
        if (!$this->document_design_service->is_valid_type($document_type)) {
            $document_type = 'default';
        }

        $this->load->model('School_model');
        $school = $this->School_model->get_by_id($school_id);
        $design = $this->document_design_service->resolve_design($school_id, $document_type);
        $doc_meta = $this->document_design_service->get_document_types()[$document_type] ?? null;

        $data = [
            'school'        => $school,
            'school_id'     => $school_id,
            'document_type' => $document_type,
            'doc_meta'      => $doc_meta,
            'design'        => $design,
        ];

        $this->load->view('pages/settings/document_design_preview', $data);
    }

    /**
     * Save ID Card Dynamic Field Positions (AJAX)
     */
    public function document_design_save_fields()
    {
        $this->require_permission('document_design.edit');

        $target_school_id = $this->_resolve_target_school_id();
        $fields_json = $this->input->post('fields', FALSE);

        if (empty($fields_json)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'No field configuration data provided.'
            ]));
            return;
        }

        $fields = is_string($fields_json) ? json_decode($fields_json, true) : $fields_json;
        if (!is_array($fields)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode([
                'status'  => false,
                'message' => 'Invalid JSON field format.'
            ]));
            return;
        }

        $allowed_fields = ['photo', 'student_name', 'student_details', 'admission_number', 'guardian_name', 'class_division', 'roll_number', 'date_of_birth', 'blood_group'];
        $sanitized_fields = [];
        foreach ($allowed_fields as $fk) {
            if (isset($fields[$fk]) && is_array($fields[$fk])) {
                $f = $fields[$fk];
                if ($fk === 'student_details') {
                    $sanitized_fields['student_details'] = [
                        'enabled'     => !empty($f['enabled']),
                        'top'         => isset($f['top']) ? max(0, min(100, floatval($f['top']))) : 61,
                        'left'        => isset($f['left']) ? max(0, min(100, floatval($f['left']))) : 7,
                        'width'       => isset($f['width']) ? max(5, min(100, floatval($f['width']))) : 86,
                        'font_size'   => isset($f['font_size']) ? preg_replace('/[^0-9.a-z%]/i', '', $f['font_size']) : '7pt',
                        'label_width' => isset($f['label_width']) ? preg_replace('/[^0-9.a-z%]/i', '', $f['label_width']) : '36%',
                        'label_color' => isset($f['label_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $f['label_color']) ? $f['label_color'] : '#0f766e',
                        'value_color' => isset($f['value_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $f['value_color']) ? $f['value_color'] : '#0f172a',
                        'line_height' => isset($f['line_height']) ? preg_replace('/[^0-9.]/i', '', $f['line_height']) : '1.5',
                    ];
                } elseif (in_array($fk, ['admission_number', 'guardian_name', 'class_division', 'roll_number', 'date_of_birth', 'blood_group'])) {
                    $sanitized_fields[$fk] = [
                        'enabled' => !empty($f['enabled']),
                        'label'   => isset($f['label']) ? trim(strip_tags($f['label'])) : '',
                    ];
                } else {
                    $sanitized_fields[$fk] = [
                        'enabled'   => !empty($f['enabled']),
                        'top'       => isset($f['top']) ? max(0, min(100, floatval($f['top']))) : 50,
                        'left'      => isset($f['left']) ? max(0, min(100, floatval($f['left']))) : 10,
                        'width'     => isset($f['width']) ? max(5, min(100, floatval($f['width']))) : 80,
                        'height'    => isset($f['height']) ? max(5, min(100, floatval($f['height']))) : 30,
                        'font_size' => isset($f['font_size']) ? preg_replace('/[^0-9.a-z%]/i', '', $f['font_size']) : '8pt',
                        'color'     => isset($f['color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $f['color']) ? $f['color'] : '#0f172a',
                        'weight'    => (isset($f['weight']) && in_array($f['weight'], ['bold', 'normal', '600', '700', '800', '900'])) ? $f['weight'] : 'normal',
                        'align'     => (isset($f['align']) && in_array($f['align'], ['left', 'center', 'right'])) ? $f['align'] : 'center',
                        'label'     => isset($f['label']) ? trim(strip_tags($f['label'])) : '',
                    ];
                }
            }
        }

        $user_id = $this->current_user->user_id ?? 1;
        $this->Document_design_model->save_design($target_school_id, 'id_card', [
            'design_config' => ['fields' => $sanitized_fields]
        ], $user_id);

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'  => true,
            'message' => 'Student ID card field positions saved successfully.'
        ]));
    }
}

