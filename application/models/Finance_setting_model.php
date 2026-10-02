<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_setting_model extends CI_Model {

    public function get_settings($school_id = NULL)
    {
        $school_id = $school_id ?: get_current_school_id();
        $settings = $this->db->get_where('tbl_finance_settings', array('school_id' => $school_id))->row();
        if (!$settings) {
            $default = array(
                'school_id'                  => $school_id,
                'currency_symbol'            => '₹',
                'currency_code'              => 'INR',
                'receipt_prefix'             => 'REC-' . str_pad($school_id, 2, '0', STR_PAD_LEFT) . '-',
                'next_receipt_number'        => 1001,
                'receipt_footer'             => 'Thank you for your fee payment. This is a computer generated receipt.',
                'authorized_signature_title' => 'Accounts Officer',
                'allow_partial_payments'     => 1,
                'allow_overpayment'          => 0,
                'require_transaction_ref'    => 0,
                'grace_period_days'          => 7,
                'discount_approval_required' => 1,
                'reminder_template_upcoming' => 'Dear Parent, the fee amount of {amount} for {student_name} is due on {due_date}.',
                'reminder_template_overdue'  => 'Dear Parent, the fee amount of {amount} for {student_name} is overdue by {days_overdue} days.',
                'reminder_template_payment'  => 'Payment of {amount} has been received for {student_name}. Receipt No: {receipt_no}.',
            );
            $this->db->insert('tbl_finance_settings', $default);
            return (object)$default;
        }
        return $settings;
    }

    public function update_settings($data, $school_id = NULL)
    {
        $school_id = $school_id ?: get_current_school_id();
        $existing = $this->get_settings($school_id);
        if ($existing) {
            $this->db->where('school_id', $school_id)->update('tbl_finance_settings', $data);
        } else {
            $data['school_id'] = $school_id;
            $this->db->insert('tbl_finance_settings', $data);
        }
    }

    public function generate_next_receipt_number($school_id = NULL)
    {
        $school_id = $school_id ?: get_current_school_id();
        $settings = $this->get_settings($school_id);
        $prefix = $settings->receipt_prefix ?: ('REC-' . str_pad($school_id, 2, '0', STR_PAD_LEFT) . '-');
        $num = (int)$settings->next_receipt_number ?: 1001;

        $receipt_no = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

        // Increment next receipt number for this school
        $this->db->where('school_id', $school_id)->update('tbl_finance_settings', array(
            'next_receipt_number' => $num + 1
        ));

        return $receipt_no;
    }
}