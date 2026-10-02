<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Document_design_service
 *
 * Centralized service library for managing and applying school document designs (headers & footers).
 * Implements fallback:
 *   Report-specific design -> School Default design -> Existing report layout
 * Supports both web/print views and mPDF page headers/footers.
 */
class Document_design_service {

    const HEADER_WIDTH = 2480;
    const HEADER_HEIGHT = 561;
    const FOOTER_WIDTH = 3539;
    const FOOTER_HEIGHT = 400;

    // Standard CR80 Portrait Card Dimensions (2.125" x 3.375" / 54mm x 86mm)
    // High-resolution 300 DPI: 2.125 * 300 = 638 px, 3.375 * 300 = 1013 px
    const IDCARD_WIDTH = 638;
    const IDCARD_HEIGHT = 1013;
    const IDCARD_RATIO_W = 2.125;
    const IDCARD_RATIO_H = 3.375;
    const IDCARD_MM_W = 54;
    const IDCARD_MM_H = 86;

    protected $CI;

    /**
     * Centralized Registry of Document Types
     */
    protected $document_types = [
        'default' => [
            'key'         => 'default',
            'label'       => 'Default Document Design',
            'description' => 'Global school fallback header & footer for all printable reports and PDFs.',
            'badge'       => 'System Fallback',
        ],
        'overall_report' => [
            'key'         => 'overall_report',
            'label'       => 'Student Overall Academic Report',
            'description' => 'Comprehensive multi-page academic performance, examination and fee summary PDF.',
            'badge'       => 'mPDF',
        ],
        'attendance_report' => [
            'key'         => 'attendance_report',
            'label'       => 'Student Attendance Report',
            'description' => 'Official attendance record with month-wise summary and day/period logs.',
            'badge'       => 'mPDF',
        ],
        'report_card' => [
            'key'         => 'report_card',
            'label'       => 'Official Academic Report Card',
            'description' => 'Term/Annual examination mark statement and grading sheet.',
            'badge'       => 'Print & mPDF',
        ],
        'progress_report' => [
            'key'         => 'progress_report',
            'label'       => 'Multi-Exam Progress Report',
            'description' => 'Longitudinal multi-examination performance trend and trajectory.',
            'badge'       => 'Print & mPDF',
        ],
        'fee_receipt' => [
            'key'         => 'fee_receipt',
            'label'       => 'Fee Payment Receipt',
            'description' => 'Official fee payment voucher and student payment ledger receipt.',
            'badge'       => 'Print & mPDF',
        ],
        'certificate' => [
            'key'         => 'certificate',
            'label'       => 'Student Certificate',
            'description' => 'Merit, participation, course completion and student certificates.',
            'badge'       => 'Print & mPDF',
        ],
        'transfer_certificate' => [
            'key'         => 'transfer_certificate',
            'label'       => 'Transfer Certificate (TC)',
            'description' => 'Official School Leaving / Transfer Certificate with security details.',
            'badge'       => 'Print & mPDF',
        ],
        'id_card' => [
            'key'         => 'id_card',
            'label'       => 'Student ID Card Design',
            'description' => 'Configurable front & back background designs for student identification cards.',
            'badge'       => 'CR80 Portrait',
            'is_id_card'  => true,
            'is_card'     => true,
        ],
        'academic_calendar' => [
            'key'         => 'academic_calendar',
            'label'       => 'Academic Calendar',
            'description' => 'Official academic year event calendar, holidays and schedule sheet.',
            'badge'       => 'Print & mPDF',
        ],
    ];

    /** In-memory cache for resolved designs during request lifecycle */
    protected $cache = [];

    public function __construct()
    {
        if (function_exists('get_instance')) {
            $this->CI =& get_instance();
            if ($this->CI && isset($this->CI->load)) {
                $this->CI->load->model('Document_design_model');
            }
        }
    }

    /**
     * Get centralized configuration for Student ID Card dimensions.
     *
     * @return array
     */
    public function get_id_card_config()
    {
        return [
            'width'        => self::IDCARD_WIDTH,
            'height'       => self::IDCARD_HEIGHT,
            'width_px'     => self::IDCARD_WIDTH,
            'height_px'    => self::IDCARD_HEIGHT,
            'width_in'     => self::IDCARD_RATIO_W,
            'height_in'    => self::IDCARD_RATIO_H,
            'width_mm'     => self::IDCARD_MM_W,
            'height_mm'    => self::IDCARD_MM_H,
            'mm_width'     => self::IDCARD_MM_W,
            'mm_height'    => self::IDCARD_MM_H,
            'aspect_ratio' => self::IDCARD_WIDTH / self::IDCARD_HEIGHT,
            'aspect_str'   => '2.125:3.375',
            'size_label'   => self::IDCARD_WIDTH . ' × ' . self::IDCARD_HEIGHT . ' px',
            'format'       => 'CR80 Portrait (54mm × 86mm / 2.125" × 3.375")',
        ];
    }

    /**
     * Get list of all registered document types.
     *
     * @return array
     */
    public function get_document_types()
    {
        return $this->document_types;
    }

    /**
     * Check if a document type is registered.
     *
     * @param string $doc_type
     * @return bool
     */
    public function is_valid_type($doc_type)
    {
        return isset($this->document_types[$doc_type]);
    }

    /**
     * Get storage directory relative to FCPATH.
     *
     * @param int $school_id
     * @param string $document_type
     * @return string
     */
    public function get_upload_dir($school_id, $document_type = '')
    {
        $school_id = (int)$school_id;
        $dir = 'uploads/document_design/school_' . $school_id . '/';
        if (!empty($document_type)) {
            $dir .= preg_replace('/[^a-zA-Z0-9_-]/', '', $document_type) . '/';
        }
        return $dir;
    }

    /**
     * Ensure storage directory exists and has index.html.
     *
     * @param int $school_id
     * @param string $document_type
     * @return string Absolute system path
     */
    public function ensure_upload_dir($school_id, $document_type = '')
    {
        $rel = $this->get_upload_dir($school_id, $document_type);
        $abs = FCPATH . $rel;
        if (!is_dir($abs)) {
            @mkdir($abs, 0755, true);
        }
        if (is_dir($abs) && !file_exists($abs . 'index.html')) {
            @file_put_contents($abs . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }
        return $abs;
    }

    /**
     * Get exact required dimensions and aspect ratio for header, footer, or ID card front/back.
     *
     * @param string $type 'header', 'footer', 'front', or 'back'
     * @param string|null $document_type
     * @return array
     */
    public function get_required_dimensions($type = 'header', $document_type = null)
    {
        $type_lower = strtolower($type);
        $is_id_card = ($document_type === 'id_card') || in_array($type_lower, ['front', 'back']);

        if ($is_id_card) {
            $is_back = ($type_lower === 'footer' || $type_lower === 'back');
            return [
                'type'         => $is_back ? 'footer' : 'header',
                'field_key'    => $is_back ? 'back' : 'front',
                'label'        => $is_back ? 'Back Design Image' : 'Front Design Image',
                'width'        => self::IDCARD_WIDTH,
                'height'       => self::IDCARD_HEIGHT,
                'aspect_ratio' => self::IDCARD_WIDTH / self::IDCARD_HEIGHT,
                'aspect_str'   => '638:1013',
                'size_label'   => self::IDCARD_WIDTH . ' × ' . self::IDCARD_HEIGHT . ' px',
                'format'       => 'CR80 Portrait (54mm × 86mm / 2.125" × 3.375")',
            ];
        }

        $is_footer = ($type_lower === 'footer');
        $width = $is_footer ? self::FOOTER_WIDTH : self::HEADER_WIDTH;
        $height = $is_footer ? self::FOOTER_HEIGHT : self::HEADER_HEIGHT;

        return [
            'type'         => $is_footer ? 'footer' : 'header',
            'field_key'    => $is_footer ? 'footer' : 'header',
            'label'        => $is_footer ? 'Footer Image' : 'Header Image',
            'width'        => $width,
            'height'       => $height,
            'aspect_ratio' => $width / $height,
            'aspect_str'   => $width . ':' . $height,
            'size_label'   => $width . ' × ' . $height . ' px',
            'format'       => 'Full Width Banner',
        ];
    }

    /**
     * Process, validate, and save cropped image data (base64 string) to destination directory.
     * Enforces exact required dimensions:
     *   Header: 2480 × 561 px
     *   Footer: 3539 × 400 px
     *   ID Card Front/Back: 638 × 1013 px (CR80 Portrait)
     *
     * @param string $base64_data Data URI or raw base64 string
     * @param string $type 'header', 'footer', 'front', or 'back'
     * @param string $upload_dir_abs Target absolute directory
     * @param string|null $document_type Optional document type ('id_card')
     * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
     */
    public function process_cropped_base64($base64_data, $type, $upload_dir_abs, $document_type = null)
    {
        $type_clean = (in_array(strtolower($type), ['footer', 'back'])) ? 'footer' : 'header';
        $dim = $this->get_required_dimensions($type, $document_type);

        if (empty($base64_data) || !is_string($base64_data)) {
            return ['success' => false, 'filename' => null, 'error' => 'No image data provided for ' . $dim['label'] . '.'];
        }

        // Clean base64 header if present
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches)) {
            $base64_clean = substr($base64_data, strpos($base64_data, ',') + 1);
        } else {
            $base64_clean = $base64_data;
        }

        $decoded = base64_decode($base64_clean, true);
        if ($decoded === false) {
            return ['success' => false, 'filename' => null, 'error' => 'Invalid base64 encoding for ' . $dim['label'] . '.'];
        }

        // Max 10MB decoded
        if (strlen($decoded) > 10 * 1024 * 1024) {
            return ['success' => false, 'filename' => null, 'error' => $dim['label'] . ' exceeds the maximum allowed file size of 10MB.'];
        }

        if (!function_exists('imagecreatefromstring')) {
            return ['success' => false, 'filename' => null, 'error' => 'PHP GD extension is required to process cropped images.'];
        }

        $src_img = @imagecreatefromstring($decoded);
        if (!$src_img) {
            return ['success' => false, 'filename' => null, 'error' => 'Corrupt or unreadable image data for ' . $dim['label'] . '.'];
        }

        $src_w = imagesx($src_img);
        $src_h = imagesy($src_img);

        $target_w = $dim['width'];
        $target_h = $dim['height'];

        // Enforce exact dimensions (allow tolerance of at most 4px for canvas pixel rounding before normalization)
        if (abs($src_w - $target_w) > 4 || abs($src_h - $target_h) > 4) {
            imagedestroy($src_img);
            return [
                'success'  => false,
                'filename' => null,
                'error'    => $dim['label'] . ' must be cropped to exactly ' . $dim['size_label'] . '. (Received: ' . $src_w . ' × ' . $src_h . ' px).'
            ];
        }

        // Create canvas with exact dimensions
        $final_img = imagecreatetruecolor($target_w, $target_h);
        // Preserve alpha transparency for PNG
        imagealphablending($final_img, false);
        imagesavealpha($final_img, true);
        $transparent = imagecolorallocatealpha($final_img, 255, 255, 255, 127);
        imagefilledrectangle($final_img, 0, 0, $target_w, $target_h, $transparent);

        imagecopyresampled($final_img, $src_img, 0, 0, 0, 0, $target_w, $target_h, $src_w, $src_h);
        imagedestroy($src_img);

        // Generate filename with semantic prefix
        $file_prefix = ($document_type === 'id_card')
            ? (($type_clean === 'footer') ? 'back' : 'front')
            : $type_clean;
        $new_filename = $file_prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.png';
        $dest_path = rtrim($upload_dir_abs, '/\\') . DIRECTORY_SEPARATOR . $new_filename;

        // Save high quality PNG
        $saved = imagepng($final_img, $dest_path, 6);
        imagedestroy($final_img);

        if (!$saved || !file_exists($dest_path)) {
            return ['success' => false, 'filename' => null, 'error' => 'Failed to save cropped ' . $dim['label'] . ' to disk.'];
        }

        return ['success' => true, 'filename' => $new_filename, 'error' => null];
    }

    /**
     * Resolve document design for a given school and document type with full fallback.
     *
     * Fallback rules:
     *   1. Check specific document_type for school_id
     *   2. If header/footer missing, check 'default' for school_id
     *   3. If still missing, flag as not configured so caller can use original layout
     *
     * @param int $school_id
     * @param string $document_type
     * @return object
     */
    public function resolve_design($school_id, $document_type)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) {
            $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        }

        $cache_key = "{$school_id}:{$document_type}";
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        // Fetch specific design
        $specific = $this->CI->Document_design_model->get_by_type($school_id, $document_type);

        // Fetch default design for fallback if needed (exclude id_card from falling back to general A4 letterhead)
        $default = null;
        if ($document_type !== 'default' && $document_type !== 'id_card') {
            $default = $this->CI->Document_design_model->get_by_type($school_id, 'default');
        }

        $res = new stdClass();
        $res->school_id           = $school_id;
        $res->document_type       = $document_type;
        $res->is_id_card          = ($document_type === 'id_card');
        $res->has_header          = false;
        $res->header_url          = '';
        $res->header_path         = '';
        $res->header_file         = '';
        $res->is_fallback_header  = false;

        $res->has_footer          = false;
        $res->footer_url          = '';
        $res->footer_path         = '';
        $res->footer_file         = '';
        $res->is_fallback_footer  = false;

        // Resolve Header (or Front for ID Card)
        if ($specific && !empty($specific->header_image) && $specific->status === 'Active') {
            $rel = $this->get_upload_dir($school_id, $document_type) . $specific->header_image;
            if (file_exists(FCPATH . $rel)) {
                $res->has_header = true;
                $res->header_file = $specific->header_image;
                $res->header_path = FCPATH . $rel;
                $res->header_url  = base_url($rel);
            }
        }
        if (!$res->has_header && $default && !empty($default->header_image) && $default->status === 'Active') {
            $rel = $this->get_upload_dir($school_id, 'default') . $default->header_image;
            if (file_exists(FCPATH . $rel)) {
                $res->has_header = true;
                $res->header_file = $default->header_image;
                $res->header_path = FCPATH . $rel;
                $res->header_url  = base_url($rel);
                $res->is_fallback_header = true;
            }
        }

        // Resolve Footer (or Back for ID Card)
        if ($specific && !empty($specific->footer_image) && $specific->status === 'Active') {
            $rel = $this->get_upload_dir($school_id, $document_type) . $specific->footer_image;
            if (file_exists(FCPATH . $rel)) {
                $res->has_footer = true;
                $res->footer_file = $specific->footer_image;
                $res->footer_path = FCPATH . $rel;
                $res->footer_url  = base_url($rel);
            }
        }
        if (!$res->has_footer && $default && !empty($default->footer_image) && $default->status === 'Active') {
            $rel = $this->get_upload_dir($school_id, 'default') . $default->footer_image;
            if (file_exists(FCPATH . $rel)) {
                $res->has_footer = true;
                $res->footer_file = $default->footer_image;
                $res->footer_path = FCPATH . $rel;
                $res->footer_url  = base_url($rel);
                $res->is_fallback_footer = true;
            }
        }

        // Semantic aliases for Student ID Card (Front and Back)
        $res->has_front         = $res->has_header;
        $res->front_url         = $res->header_url;
        $res->front_path        = $res->header_path;
        $res->front_file        = $res->header_file;
        $res->is_fallback_front = $res->is_fallback_header;

        $res->has_back          = $res->has_footer;
        $res->back_url          = $res->footer_url;
        $res->back_path         = $res->footer_path;
        $res->back_file         = $res->footer_file;
        $res->is_fallback_back  = $res->is_fallback_footer;

        $res->has_design        = ($res->has_header || $res->has_footer);
        $res->front_image       = $res->front_file;
        $res->back_image        = $res->back_file;
        $res->field_config      = ($document_type === 'id_card') ? $this->get_id_card_field_config($school_id) : [];

        $this->cache[$cache_key] = $res;
        return $res;
    }

    /**
     * Get standard default dynamic student field positioning configuration (relative percentages).
     *
     * @return array
     */
    public function get_default_id_card_field_config()
    {
        return [
            'photo' => [
                'enabled'   => true,
                'top'       => 24,
                'left'      => 31,
                'width'     => 38,
                'height'    => 26,
                'border'    => '2px solid #cbd5e1',
                'radius'    => '10px',
            ],
            'student_name' => [
                'enabled'   => true,
                'top'       => 53,
                'left'      => 5,
                'width'     => 90,
                'font_size' => '9.5pt',
                'color'     => '#0f172a',
                'weight'    => 'bold',
                'align'     => 'center',
            ],
            'student_details' => [
                'enabled'     => true,
                'top'         => 61,
                'left'        => 7,
                'width'       => 86,
                'font_size'   => '7pt',
                'label_width' => '36%',
                'label_color' => '#0f766e',
                'value_color' => '#0f172a',
                'line_height' => '1.5',
            ],
            'admission_number' => [
                'enabled'   => true,
                'label'     => 'ID',
            ],
            'guardian_name' => [
                'enabled'   => true,
                'label'     => "FATHER'S NAME",
            ],
            'class_division' => [
                'enabled'   => true,
                'label'     => 'CLASS & DIV',
            ],
            'roll_number' => [
                'enabled'   => true,
                'label'     => 'ROLL NO.',
            ],
            'date_of_birth' => [
                'enabled'   => true,
                'label'     => 'D.O.B',
            ],
            'blood_group' => [
                'enabled'   => true,
                'label'     => 'BLOOD GROUP',
            ],
        ];
    }

    /**
     * Render structured student-details table HTML for custom ID card overlays.
     * Guarantees consistent LABEL : VALUE alignment across preview, print, and PDF.
     *
     * @param object|array $student
     * @param array $fc Merged field configuration
     * @param array $options ['is_print' => bool, 'context' => string]
     * @return string HTML
     */
    public function render_student_details_table($student, array $fc = [], array $options = [])
    {
        if (empty($fc)) {
            $fc = $this->get_default_id_card_field_config();
        }

        $sdt = $fc['student_details'] ?? [];
        if (isset($sdt['enabled']) && empty($sdt['enabled'])) {
            return '';
        }

        // Normalize student data
        $isObj = is_object($student);
        $adm = $isObj ? ($student->admission_number ?? '') : ($student['admission_number'] ?? '');
        $father = $isObj ? ($student->guardian_name ?? '') : ($student['guardian_name'] ?? '');
        $cName = $isObj ? ($student->class_name ?? '') : ($student['class_name'] ?? '');
        $dName = $isObj ? ($student->division_name ?? $student->section_name ?? '') : ($student['division_name'] ?? $student['section_name'] ?? '');
        $classDiv = trim($cName . ($dName ? ' - ' . $dName : ''));
        if (empty($classDiv)) {
            $classDiv = $isObj ? ($student->class_display ?? '—') : ($student['class_display'] ?? '—');
        }
        $roll = $isObj ? ($student->roll_number ?? '') : ($student['roll_number'] ?? '');
        
        $dobRaw = $isObj ? ($student->date_of_birth ?? '') : ($student['date_of_birth'] ?? '');
        $dob = '';
        if (!empty($dobRaw) && $dobRaw !== 'N/A' && $dobRaw !== '0000-00-00') {
            $dob = date('d-m-Y', strtotime($dobRaw));
        } elseif ($isObj && !empty($student->dob_formatted) && $student->dob_formatted !== 'N/A') {
            $dob = $student->dob_formatted;
        } elseif (!$isObj && !empty($student['dob_formatted']) && $student['dob_formatted'] !== 'N/A') {
            $dob = $student['dob_formatted'];
        }

        $blood = $isObj ? ($student->blood_group ?? '') : ($student['blood_group'] ?? '');

        // Positioning & typography
        $top       = floatval($sdt['top'] ?? 61);
        $left      = floatval($sdt['left'] ?? 7);
        $width     = floatval($sdt['width'] ?? 86);
        $fontSize  = htmlspecialchars($sdt['font_size'] ?? '7pt', ENT_QUOTES, 'UTF-8');
        $lblWidth  = htmlspecialchars($sdt['label_width'] ?? '36%', ENT_QUOTES, 'UTF-8');
        $lblColor  = htmlspecialchars($sdt['label_color'] ?? '#0f766e', ENT_QUOTES, 'UTF-8');
        $valColor  = htmlspecialchars($sdt['value_color'] ?? '#0f172a', ENT_QUOTES, 'UTF-8');
        $lineH     = htmlspecialchars($sdt['line_height'] ?? '1.5', ENT_QUOTES, 'UTF-8');

        // Fields definition
        $fields = [
            'admission_number' => [
                'label'   => $fc['admission_number']['label'] ?? 'ID',
                'value'   => $adm ?: '—',
                'is_mono' => true,
            ],
            'guardian_name' => [
                'label'   => $fc['guardian_name']['label'] ?? "FATHER'S NAME",
                'value'   => $father ?: '—',
            ],
            'class_division' => [
                'label'   => $fc['class_division']['label'] ?? 'CLASS & DIV',
                'value'   => $classDiv ?: '—',
            ],
            'roll_number' => [
                'label'   => $fc['roll_number']['label'] ?? 'ROLL NO.',
                'value'   => $roll ?: '—',
                'is_mono' => true,
            ],
            'date_of_birth' => [
                'label'   => $fc['date_of_birth']['label'] ?? 'D.O.B',
                'value'   => $dob ?: '—',
            ],
            'blood_group' => [
                'label'    => $fc['blood_group']['label'] ?? 'BLOOD GROUP',
                'value'    => $blood ?: '—',
                'is_blood' => true,
            ],
        ];

        $addId = empty($options['is_print']) || empty($options['is_bulk']);
        $rowsHtml = '';
        foreach ($fields as $key => $fData) {
            $isEnabled = !isset($fc[$key]['enabled']) || !empty($fc[$key]['enabled']);
            if (!$isEnabled) {
                continue;
            }

            $lbl = htmlspecialchars($fData['label'], ENT_QUOTES, 'UTF-8');
            $val = htmlspecialchars($fData['value'], ENT_QUOTES, 'UTF-8');
            $valStyle = "font-weight: 700; color: {$valColor}; vertical-align: top; padding: 1.5px 0 1.5px 4px; word-break: break-word;";
            if (!empty($fData['is_blood']) && $val !== '—') {
                $valStyle .= " color: #e11d48; font-weight: 800;";
            }
            if (!empty($fData['is_mono'])) {
                $valStyle .= " font-family: monospace;";
            }

            $idAttrRow = $addId ? " id=\"df-row-{$key}\"" : "";
            $idAttrLbl = $addId ? " id=\"df-lbl-{$key}\"" : "";
            $idAttrVal = $addId ? " id=\"df-val-{$key}\"" : "";

            $rowsHtml .= "<tr class=\"df-row df-row-{$key}\"{$idAttrRow}>" .
                "<td class=\"df-lbl-col df-lbl-{$key}\"{$idAttrLbl} style=\"width: {$lblWidth}; font-weight: 700; color: {$lblColor}; text-transform: uppercase; vertical-align: top; padding: 1.5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">{$lbl}</td>" .
                "<td class=\"df-sep-col\" style=\"width: 10px; text-align: center; color: #94a3b8; font-weight: 700; vertical-align: top; padding: 1.5px 0;\">:</td>" .
                "<td class=\"df-val-col df-val-{$key}\"{$idAttrVal} style=\"{$valStyle}\">{$val}</td>" .
                "</tr>";
        }

        $idAttrCont = $addId ? " id=\"df-front-student_details\"" : "";
        $padLeft = !empty($options['is_print']) ? '2.8mm' : '10px';
        $html = "<div{$idAttrCont} class=\"student-details-container\" style=\"position: absolute; top: {$top}%; left: {$left}%; width: {$width}%; pointer-events: none; z-index: 15; box-sizing: border-box; padding-left: {$padLeft};\">" .
            "<table class=\"student-details-table\" style=\"width: 100%; border-collapse: collapse; table-layout: fixed; font-size: {$fontSize}; line-height: {$lineH}; text-align: left;\">" .
            "<tbody>{$rowsHtml}</tbody>" .
            "</table>" .
            "</div>";

        return $html;
    }

    /**
     * Get merged ID card field positioning config for a school.
     *
     * @param int $school_id
     * @return array
     */
    public function get_id_card_field_config($school_id)
    {
        $defaults = $this->get_default_id_card_field_config();
        if (!isset($this->CI) || !isset($this->CI->Document_design_model)) {
            return $defaults;
        }

        $design = $this->CI->Document_design_model->get_by_type($school_id, 'id_card');
        if ($design && !empty($design->design_config)) {
            $parsed = is_string($design->design_config) ? json_decode($design->design_config, true) : $design->design_config;
            if (is_array($parsed)) {
                $fields = isset($parsed['fields']) ? $parsed['fields'] : $parsed;
                foreach ($defaults as $k => $def) {
                    if (isset($fields[$k]) && is_array($fields[$k])) {
                        $defaults[$k] = array_merge($def, $fields[$k]);
                    }
                }
            }
        }
        return $defaults;
    }

    /**
     * Render header HTML tag for print/web views.
     *
     * @param object $design Output from resolve_design()
     * @param string $fallback_html HTML to render if no header image is configured
     * @param string $extra_class Additional CSS classes
     * @return string
     */
    public function render_header_html($design, $fallback_html = '', $extra_class = '')
    {
        if (!empty($design->has_header) && !empty($design->header_url)) {
            $url = html_escape($design->header_url);
            $cls = html_escape($extra_class);
            return '<div class="document-design-header w-full mb-4 ' . $cls . '">
                <img src="' . $url . '" alt="Document Header" class="w-full h-auto block" />
            </div>';
        }
        return $fallback_html;
    }

    /**
     * Render footer HTML tag for print/web views.
     *
     * @param object $design Output from resolve_design()
     * @param string $fallback_html HTML to render if no footer image is configured
     * @param string $extra_class Additional CSS classes
     * @return string
     */
    public function render_footer_html($design, $fallback_html = '', $extra_class = '')
    {
        if (!empty($design->has_footer) && !empty($design->footer_url)) {
            $url = html_escape($design->footer_url);
            $cls = html_escape($extra_class);
            return '<div class="document-design-footer w-full mt-6 ' . $cls . '">
                <img src="' . $url . '" alt="Document Footer" class="w-full h-auto block" />
            </div>';
        }
        return $fallback_html;
    }

    /**
     * Helper to render header specifically for mPDF HTML templates.
     * Generates a 210mm edge-to-edge header without distortion.
     *
     * @param object $design Output from resolve_design()
     * @param string $fallback_html
     * @return string
     */
    public function render_mpdf_header_html($design, $fallback_html = '')
    {
        if (!empty($design->has_header) && !empty($design->header_path) && file_exists($design->header_path)) {
            $path = htmlspecialchars($design->header_path, ENT_QUOTES, 'UTF-8');
            return '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                <img src="' . $path . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
            </div>';
        }
        return $fallback_html;
    }

    /**
     * Helper to render footer specifically for mPDF HTML templates.
     * Generates a 210mm edge-to-edge footer without distortion.
     *
     * @param object $design Output from resolve_design()
     * @param string $fallback_html
     * @return string
     */
    public function render_mpdf_footer_html($design, $fallback_html = '')
    {
        if (!empty($design->has_footer) && !empty($design->footer_path) && file_exists($design->footer_path)) {
            $path = htmlspecialchars($design->footer_path, ENT_QUOTES, 'UTF-8');
            return '<div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                <img src="' . $path . '" style="width: 210mm; display: block; margin: 0; padding: 0;" />
            </div>';
        }
        return $fallback_html;
    }

    /**
     * Calculate exact mPDF page margins dynamically based on resolved header/footer dimensions and safe clearance.
     *
     * Concept:
     *   Page:
     *   ├── Header area (0 to renderedHeaderHeight)
     *   ├── Reserved top margin = ceil(renderedHeaderHeight + safe_spacing)
     *   │      └── Report content flows here
     *   ├── Reserved bottom margin = ceil(renderedFooterHeight + safe_spacing)
     *   └── Footer area (page_height - renderedFooterHeight to page_height)
     *
     * @param object $design Output from resolve_design()
     * @param float $page_width_mm Standard A4 portrait is 210mm
     * @param float $page_height_mm Standard A4 portrait is 297mm
     * @param string $orientation 'P' or 'L'
     * @return array Array of margin settings in mm suitable for mPDF configuration
     */
    public function calculate_mpdf_margins($design, $page_width_mm = 210, $page_height_mm = 297, $orientation = 'P')
    {
        if (strtoupper($orientation) === 'L') {
            $tmp = $page_width_mm;
            $page_width_mm = $page_height_mm;
            $page_height_mm = $tmp;
        }

        $has_header = !empty($design->has_header) && !empty($design->header_path) && file_exists($design->header_path);
        $has_footer = !empty($design->has_footer) && !empty($design->footer_path) && file_exists($design->footer_path);

        // Header margin calculation
        if ($has_header) {
            $img_info = @getimagesize($design->header_path);
            if ($img_info && !empty($img_info[0]) && !empty($img_info[1])) {
                $aspect_ratio = (float)$img_info[1] / (float)$img_info[0];
            } else {
                $aspect_ratio = (float)self::HEADER_HEIGHT / (float)self::HEADER_WIDTH; // 561 / 2480 = ~0.2262
            }
            $rendered_header_height = $page_width_mm * $aspect_ratio; // ~47.5mm on A4
            $safe_spacing_top = 8.5; // Dedicated clearance in mm between header and content
            $margin_top = (int)ceil($rendered_header_height + $safe_spacing_top); // 56mm
        } else {
            $rendered_header_height = 0;
            $margin_top = 28; // Standard fallback header margin
        }

        // Footer margin calculation
        if ($has_footer) {
            $img_info = @getimagesize($design->footer_path);
            if ($img_info && !empty($img_info[0]) && !empty($img_info[1])) {
                $aspect_ratio = (float)$img_info[1] / (float)$img_info[0];
            } else {
                $aspect_ratio = (float)self::FOOTER_HEIGHT / (float)self::FOOTER_WIDTH; // 400 / 3539 = ~0.1130
            }
            $rendered_footer_height = $page_width_mm * $aspect_ratio; // ~23.7mm on A4
            $safe_spacing_bottom = 6.0; // Dedicated clearance in mm between content and footer
            $margin_bottom = (int)ceil($rendered_footer_height + $safe_spacing_bottom); // ~30mm
        } else {
            $rendered_footer_height = 0;
            $margin_bottom = 14; // Standard fallback footer margin
        }

        return [
            'margin_left'            => 0,
            'margin_right'           => 0,
            'margin_top'             => $margin_top,
            'margin_bottom'          => $margin_bottom,
            'margin_header'          => 0,
            'margin_footer'          => 0,
            'rendered_header_height' => $rendered_header_height,
            'rendered_footer_height' => $rendered_footer_height,
            'has_header'             => $has_header,
            'has_footer'             => $has_footer,
        ];
    }
}
