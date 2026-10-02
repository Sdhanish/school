<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Conversation_model extends CI_Model {

    protected $table = 'tbl_conversations';
    protected $primaryKey = 'conversation_id';

    protected function get_school_id()
    {
        return function_exists('get_current_school_id') ? (int)get_current_school_id() : 1;
    }

    public function get_conversations_for_user($user_id = 1, $user_type = 'Staff')
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('c.*, cp.unread_count, cp.last_read_at, lm.message_text as last_message, lm.created_at as last_message_time, s.full_name as creator_name')
            ->from('tbl_conversations c')
            ->join('tbl_conversation_participants cp', "cp.conversation_id = c.conversation_id AND cp.user_id = {$user_id} AND cp.user_type = '{$user_type}'")
            ->join('tbl_staff s', 's.staff_id = c.created_by', 'left')
            ->join('(SELECT m1.* FROM tbl_messages m1 JOIN (SELECT conversation_id, MAX(message_id) as max_id FROM tbl_messages GROUP BY conversation_id) m2 ON m1.message_id = m2.max_id) lm', 'lm.conversation_id = c.conversation_id', 'left')
            ->where('c.school_id', $school_id)
            ->order_by('COALESCE(lm.created_at, c.created_at)', 'DESC', FALSE)
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        $school_id = $this->get_school_id();
        return $this->db
            ->select('c.*, s.full_name as creator_name')
            ->from('tbl_conversations c')
            ->join('tbl_staff s', 's.staff_id = c.created_by', 'left')
            ->where('c.school_id', $school_id)
            ->where('c.conversation_id', (int)$id)
            ->get()
            ->row();
    }

    public function get_participants($conversation_id)
    {
        return $this->db
            ->select('cp.*, s.full_name as staff_name, s.employee_code, st.first_name, st.last_name, st.guardian_name')
            ->from('tbl_conversation_participants cp')
            ->join('tbl_staff s', "s.staff_id = cp.user_id AND cp.user_type = 'Staff'", 'left')
            ->join('tbl_students st', "st.student_id = cp.user_id AND cp.user_type IN ('Parent', 'Student')", 'left')
            ->where('cp.conversation_id', (int)$conversation_id)
            ->get()
            ->result();
    }

    public function get_messages($conversation_id, $limit = 100)
    {
        return $this->db
            ->select('m.*, s.full_name as sender_staff_name, st.guardian_name as sender_parent_name, st.first_name as sender_student_name')
            ->from('tbl_messages m')
            ->join('tbl_staff s', "s.staff_id = m.sender_id AND m.sender_type = 'Staff'", 'left')
            ->join('tbl_students st', "st.student_id = m.sender_id AND m.sender_type IN ('Parent', 'Student')", 'left')
            ->where('m.conversation_id', (int)$conversation_id)
            ->order_by('m.created_at', 'ASC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function create_conversation($type, $title, $created_by, $participant_ids = [])
    {
        $school_id = $this->get_school_id();
        $this->db->insert($this->table, [
            'school_id'         => $school_id,
            'conversation_type' => $type,
            'title'             => $title,
            'created_by'        => $created_by,
            'created_at'        => date('Y-m-d H:i:s')
        ]);
        $conv_id = $this->db->insert_id();

        // Add creator as participant
        $this->add_participant($conv_id, $created_by, 'Staff');

        // Add other participants
        foreach ($participant_ids as $p) {
            $u_id = is_array($p) ? $p['id'] : $p;
            $u_type = is_array($p) ? ($p['type'] ?? 'Staff') : 'Staff';
            $this->add_participant($conv_id, $u_id, $u_type);
        }

        return $conv_id;
    }

    public function add_participant($conversation_id, $user_id, $user_type = 'Staff')
    {
        $existing = $this->db
            ->where('conversation_id', (int)$conversation_id)
            ->where('user_id', (int)$user_id)
            ->where('user_type', $user_type)
            ->count_all_results('tbl_conversation_participants');

        if ($existing === 0) {
            $this->db->insert('tbl_conversation_participants', [
                'conversation_id' => (int)$conversation_id,
                'user_id'         => (int)$user_id,
                'user_type'       => $user_type,
                'unread_count'    => 0,
                'created_at'      => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function send_message($conversation_id, $sender_id, $sender_type, $text, $attachments = NULL)
    {
        $this->db->insert('tbl_messages', [
            'conversation_id' => (int)$conversation_id,
            'sender_id'       => (int)$sender_id,
            'sender_type'     => $sender_type,
            'message_text'    => $text,
            'attachments'     => $attachments ? json_encode($attachments) : NULL,
            'status'          => 'Sent',
            'created_at'      => date('Y-m-d H:i:s')
        ]);
        $msg_id = $this->db->insert_id();

        // Increment unread count for other participants
        $this->db->query("
            UPDATE tbl_conversation_participants
            SET unread_count = unread_count + 1
            WHERE conversation_id = ? AND NOT (user_id = ? AND user_type = ?)
        ", array((int)$conversation_id, (int)$sender_id, $sender_type));

        return $msg_id;
    }

    public function mark_as_read($conversation_id, $user_id = 1, $user_type = 'Staff')
    {
        $this->db->where('conversation_id', (int)$conversation_id)
            ->where('user_id', (int)$user_id)
            ->where('user_type', $user_type)
            ->update('tbl_conversation_participants', [
                'unread_count' => 0,
                'last_read_at' => date('Y-m-d H:i:s')
            ]);
    }
}
