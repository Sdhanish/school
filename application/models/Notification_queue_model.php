<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_queue_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_queue($filters = [])
    {
        $school_id = $this->get_school_id();
        $this->db->select('m.*, t.template_name')
            ->from('tbl_communication_messages m')
            ->join('tbl_communication_templates t', 't.template_id = m.template_id', 'left')
            ->where('m.school_id', $school_id);

        if (!empty($filters['status'])) {
            $this->db->where('m.status', $filters['status']);
        } else {
            $this->db->where_in('m.status', ['Pending', 'Scheduled', 'Processing', 'Failed']);
        }

        if (!empty($filters['channel'])) {
            $this->db->where('m.channel', $filters['channel']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('m.priority', $filters['priority']);
        }
        if (!empty($filters['source_module'])) {
            $this->db->where('m.source_module', $filters['source_module']);
        }

        return $this->db->order_by('m.message_id', 'DESC')->get()->result();
    }

    public function get_failed($filters = [])
    {
        $school_id = $this->get_school_id();
        $this->db->select('m.*, t.template_name')
            ->from('tbl_communication_messages m')
            ->join('tbl_communication_templates t', 't.template_id = m.template_id', 'left')
            ->where('m.school_id', $school_id)
            ->where('m.status', 'Failed');

        if (!empty($filters['channel'])) {
            $this->db->where('m.channel', $filters['channel']);
        }
        if (!empty($filters['source_module'])) {
            $this->db->where('m.source_module', $filters['source_module']);
        }

        return $this->db->order_by('m.message_id', 'DESC')->get()->result();
    }

    public function process_item($message_id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->where('message_id', (int)$message_id)
            ->where('school_id', $school_id)
            ->update('tbl_communication_messages', [
                'status'       => 'Delivered',
                'sent_at'      => date('Y-m-d H:i:s'),
                'delivered_at' => date('Y-m-d H:i:s')
            ]);
    }
}
