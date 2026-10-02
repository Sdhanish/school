<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Document_design_model
 *
 * Model for managing school-specific document headers and footers.
 * Adheres strictly to:
 *  - Strict school isolation
 *  - NO SELECT * (all queries project explicit columns)
 *  - Soft-delete handling (is_deleted = 'n')
 *  - MVC separation (no presentation or HTML)
 */
class Document_design_model extends CI_Model {

    protected $table = 'tbl_document_designs';

    /**
     * Columns list to avoid SELECT *
     */
    protected $select_columns = 'id, school_id, document_type, header_image, footer_image, design_config, status, created_by, updated_by, created_at, updated_at, is_deleted';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get specific document design by school_id and document_type.
     *
     * @param int $school_id
     * @param string $document_type
     * @return object|null
     */
    public function get_by_type($school_id, $document_type)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0 || empty($document_type)) {
            return null;
        }

        return $this->db
            ->select($this->select_columns)
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('document_type', $document_type)
            ->where('is_deleted', 'n')
            ->limit(1)
            ->get()
            ->row();
    }

    /**
     * Get all active designs for a school, keyed by document_type.
     *
     * @param int $school_id
     * @return array
     */
    public function get_all_for_school($school_id)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0) {
            return [];
        }

        $records = $this->db
            ->select($this->select_columns)
            ->from($this->table)
            ->where('school_id', $school_id)
            ->where('is_deleted', 'n')
            ->get()
            ->result();

        $result = [];
        foreach ($records as $r) {
            $result[$r->document_type] = $r;
        }
        return $result;
    }

    /**
     * Save (insert or update) a document design for a school.
     *
     * @param int $school_id
     * @param string $document_type
     * @param array $data Fields to update (header_image, footer_image, status)
     * @param int $user_id
     * @return bool|int
     */
    public function save_design($school_id, $document_type, array $data, $user_id = 1)
    {
        $school_id = (int)$school_id;
        if ($school_id <= 0 || empty($document_type)) {
            return false;
        }

        $existing = $this->get_by_type($school_id, $document_type);

        if ($existing) {
            $update_data = [
                'updated_by' => (int)$user_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (array_key_exists('header_image', $data)) {
                $update_data['header_image'] = $data['header_image'];
            } elseif (array_key_exists('front_image', $data)) {
                $update_data['header_image'] = $data['front_image'];
            }

            if (array_key_exists('footer_image', $data)) {
                $update_data['footer_image'] = $data['footer_image'];
            } elseif (array_key_exists('back_image', $data)) {
                $update_data['footer_image'] = $data['back_image'];
            }

            if (array_key_exists('status', $data)) {
                $update_data['status'] = in_array($data['status'], ['Active', 'Inactive']) ? $data['status'] : 'Active';
            }

            if (array_key_exists('design_config', $data)) {
                $update_data['design_config'] = is_array($data['design_config']) ? json_encode($data['design_config']) : $data['design_config'];
            }

            $this->db->where('id', $existing->id);
            $this->db->where('school_id', $school_id);
            $this->db->update($this->table, $update_data);
            return $existing->id;
        } else {
            $insert_data = [
                'school_id'     => $school_id,
                'document_type' => $document_type,
                'header_image'  => $data['header_image'] ?? $data['front_image'] ?? null,
                'footer_image'  => $data['footer_image'] ?? $data['back_image'] ?? null,
                'design_config' => isset($data['design_config']) ? (is_array($data['design_config']) ? json_encode($data['design_config']) : $data['design_config']) : null,
                'status'        => isset($data['status']) && in_array($data['status'], ['Active', 'Inactive']) ? $data['status'] : 'Active',
                'created_by'    => (int)$user_id,
                'updated_by'    => (int)$user_id,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'is_deleted'    => 'n',
            ];

            $this->db->insert($this->table, $insert_data);
            return $this->db->insert_id();
        }
    }

    /**
     * Remove header or footer image for a document design.
     * Supports both 'header'/'footer' and 'front'/'back' identifiers.
     *
     * @param int $school_id
     * @param string $document_type
     * @param string $image_type 'header', 'footer', 'front', or 'back'
     * @param int $user_id
     * @return string|null The filename of the removed image so it can be unlinked safely
     */
    public function remove_image($school_id, $document_type, $image_type, $user_id = 1)
    {
        $school_id = (int)$school_id;
        $existing = $this->get_by_type($school_id, $document_type);
        if (!$existing) {
            return null;
        }

        $img_type_lower = strtolower($image_type);
        $field = ($img_type_lower === 'header' || $img_type_lower === 'front') ? 'header_image' : 'footer_image';
        $old_file = $existing->$field;

        $this->db->where('id', $existing->id);
        $this->db->where('school_id', $school_id);
        $this->db->update($this->table, [
            $field       => null,
            'updated_by' => (int)$user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $old_file;
    }

    /**
     * Soft delete document design record.
     *
     * @param int $school_id
     * @param string $document_type
     * @param int $user_id
     * @return bool
     */
    public function soft_delete($school_id, $document_type, $user_id = 1)
    {
        $school_id = (int)$school_id;
        $existing = $this->get_by_type($school_id, $document_type);
        if (!$existing) {
            return false;
        }

        $this->db->where('id', $existing->id);
        $this->db->where('school_id', $school_id);
        return $this->db->update($this->table, [
            'is_deleted' => 'y',
            'updated_by' => (int)$user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
