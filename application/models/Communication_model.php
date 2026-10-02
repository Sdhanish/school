<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communication_model extends CI_Model {

    protected $table = 'tbl_communication_messages';
    protected $primaryKey = 'message_id';

    /**
     * Get the most recent communication audit log entries.
     *
     * @param  int $limit Number of entries to return
     * @return array
     */
    public function get_recent_audit_logs($limit = 30)
    {
        return $this->db
            ->order_by('log_id', 'DESC')
            ->limit((int)$limit)
            ->get('tbl_communication_audit_logs')
            ->result();
    }

    public function get_dashboard_stats()
    {
        $school_id = function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
        $total_notifications = (int)$this->db->where('school_id', $school_id)->count_all_results($this->table);
        $total_sent = (int)$this->db->where('school_id', $school_id)->where_in('status', ['Sent', 'Delivered'])->count_all_results($this->table);
        $delivered = (int)$this->db->where('school_id', $school_id)->where('status', 'Delivered')->count_all_results($this->table);
        $pending = (int)$this->db->where('school_id', $school_id)->where_in('status', ['Pending', 'Processing'])->count_all_results($this->table);
        $failed = (int)$this->db->where('school_id', $school_id)->where('status', 'Failed')->count_all_results($this->table);
        $scheduled = (int)$this->db->where('school_id', $school_id)->where('status', 'Scheduled')->count_all_results($this->table);
        $cancelled = (int)$this->db->where('school_id', $school_id)->where('status', 'Cancelled')->count_all_results($this->table);

        // Channels
        $inapp_sent = (int)$this->db->where('school_id', $school_id)->where('channel', 'In-App')->count_all_results($this->table);
        $sms_sent = (int)$this->db->where('school_id', $school_id)->where('channel', 'SMS')->count_all_results($this->table);
        $whatsapp_sent = (int)$this->db->where('school_id', $school_id)->where('channel', 'WhatsApp')->count_all_results($this->table);
        $email_sent = (int)$this->db->where('school_id', $school_id)->where('channel', 'Email')->count_all_results($this->table);

        $delivery_pct = ($total_sent > 0) ? round(($delivered / $total_sent) * 100, 1) : 100;

        return (object)[
            'total_notifications' => $total_notifications,
            'total_sent'          => $total_sent,
            'delivered'           => $delivered,
            'pending'             => $pending,
            'failed'              => $failed,
            'scheduled'           => $scheduled,
            'cancelled'           => $cancelled,
            'inapp_sent'          => $inapp_sent,
            'sms_sent'            => $sms_sent,
            'whatsapp_sent'       => $whatsapp_sent,
            'email_sent'          => $email_sent,
            'delivery_pct'        => $delivery_pct
        ];
    }

    public function get_templates($filters = array())
    {
        $this->db->order_by('template_name', 'ASC');
        if (!empty($filters['channel'])) $this->db->where('channel', $filters['channel']);
        if (!empty($filters['category'])) $this->db->where('category', $filters['category']);
        if (!empty($filters['type'])) $this->db->where('communication_type', $filters['type']);
        if (isset($filters['status']) && $filters['status'] !== '') $this->db->where('status', $filters['status']);
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('template_name', $filters['search'])
                ->or_like('template_code', $filters['search'])
                ->or_like('message_template', $filters['search'])
                ->group_end();
        }
        return $this->db->get('tbl_communication_templates')->result();
    }

    public function get_template_by_id($id)
    {
        return $this->db->where('template_id', $id)->get('tbl_communication_templates')->row();
    }

    public function get_template_by_code($code)
    {
        return $this->db->where('template_code', $code)->get('tbl_communication_templates')->row();
    }

    public function insert_template($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('tbl_communication_templates', $data);
        return $this->db->insert_id();
    }

    public function update_template($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('template_id', $id)->update('tbl_communication_templates', $data);
    }

    public function duplicate_template($template_id)
    {
        $tmpl = $this->get_template_by_id($template_id);
        if (!$tmpl) return FALSE;

        $new_code = $tmpl->template_code . '_COPY_' . time();
        $newData = [
            'template_name'      => $tmpl->template_name . ' (Copy)',
            'template_code'      => $new_code,
            'category'           => $tmpl->category,
            'communication_type' => $tmpl->communication_type,
            'channel'            => $tmpl->channel,
            'subject'            => $tmpl->subject,
            'message_template'   => $tmpl->message_template,
            'variables'          => $tmpl->variables,
            'character_limit'    => $tmpl->character_limit,
            'description'        => 'Duplicate of ' . $tmpl->template_name,
            'is_system'          => 0,
            'status'             => 'Active',
            'created_at'         => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_communication_templates', $newData);
        return $this->db->insert_id();
    }

    public function delete_template($id)
    {
        // Check if used in history
        $used = $this->db->where('template_id', $id)->count_all_results($this->table);
        if ($used > 0) {
            // Soft deactivate instead of permanently delete
            return $this->update_template($id, ['status' => 'Inactive']);
        }
        return $this->db->where('template_id', $id)->delete('tbl_communication_templates');
    }

    public function get_messages($filters = array(), $limit = 50, $offset = 0)
    {
        $this->db->select('m.*, t.template_name')
            ->from($this->table . ' m')
            ->join('tbl_communication_templates t', 't.template_id = m.template_id', 'left');

        if (!empty($filters['channel'])) $this->db->where('m.channel', $filters['channel']);
        if (!empty($filters['source_module'])) $this->db->where('m.source_module', $filters['source_module']);
        if (!empty($filters['status'])) $this->db->where('m.status', $filters['status']);
        if (!empty($filters['recipient_type'])) $this->db->where('m.recipient_type', $filters['recipient_type']);
        if (!empty($filters['start_date'])) $this->db->where('DATE(m.created_at) >=', $filters['start_date']);
        if (!empty($filters['end_date'])) $this->db->where('DATE(m.created_at) <=', $filters['end_date']);

        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('m.recipient_name', $filters['search'])
                ->or_like('m.recipient_contact', $filters['search'])
                ->or_like('m.subject', $filters['search'])
                ->or_like('m.template_code', $filters['search'])
                ->or_like('m.rendered_message', $filters['search'])
                ->group_end();
        }

        $this->db->order_by('m.message_id', 'DESC');
        if ($limit > 0) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_message_by_id($message_id)
    {
        return $this->db->select('m.*, t.template_name')
            ->from($this->table . ' m')
            ->join('tbl_communication_templates t', 't.template_id = m.template_id', 'left')
            ->where('m.message_id', $message_id)
            ->get()->row();
    }

    public function get_delivery_reports($filters = [])
    {
        // Channel stats
        $channels = ['In-App', 'SMS', 'WhatsApp', 'Email'];
        $channel_report = [];

        foreach ($channels as $ch) {
            $total = (int)$this->db->where('channel', $ch)->count_all_results($this->table);
            $delivered = (int)$this->db->where('channel', $ch)->where('status', 'Delivered')->count_all_results($this->table);
            $failed = (int)$this->db->where('channel', $ch)->where('status', 'Failed')->count_all_results($this->table);
            $pending = (int)$this->db->where('channel', $ch)->where_in('status', ['Pending', 'Scheduled', 'Processing'])->count_all_results($this->table);
            $pct = ($total > 0) ? round(($delivered / $total) * 100, 1) : 0;

            $channel_report[] = (object)[
                'channel'   => $ch,
                'total'     => $total,
                'delivered' => $delivered,
                'failed'    => $failed,
                'pending'   => $pending,
                'rate'      => $pct
            ];
        }

        // Module stats
        $modules = ['Attendance', 'Fees', 'Homework', 'Examination', 'Leave', 'Transport', 'Certificates', 'Direct'];
        $module_report = [];

        foreach ($modules as $mod) {
            $total = (int)$this->db->where('source_module', $mod)->count_all_results($this->table);
            $delivered = (int)$this->db->where('source_module', $mod)->where('status', 'Delivered')->count_all_results($this->table);
            $failed = (int)$this->db->where('source_module', $mod)->where('status', 'Failed')->count_all_results($this->table);
            $pct = ($total > 0) ? round(($delivered / $total) * 100, 1) : 0;

            $module_report[] = (object)[
                'module'    => $mod,
                'total'     => $total,
                'delivered' => $delivered,
                'failed'    => $failed,
                'rate'      => $pct
            ];
        }

        return [
            'channel_report' => $channel_report,
            'module_report'  => $module_report
        ];
    }
}
