<?php

class Address_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_new_address($user_id, $new_wallet_addres, $label, $crypto_type) {
        $this->db->insert('addresses', array(
                'address' => $new_wallet_addres,
                'user_id' => $user_id,
                'balance' => 0,
                'label' => $label,
                'crypto_type' => $crypto_type)
        );
        return $this->db->insert_id();
    }

    public function get_address($address) {
        $query = $this->db->get_where('addresses', array('address' => $address), 1);
        return $query->row();
    }

    public function get_address_for_user($address, $user_id) {
        $query = $this->db->get_where('addresses', array('address' => $address, 'user_id' => $user_id), 1);
        return $query->row();
    }

    public function update_total_received_crypto($address, $total_received) {
        $this->db->update('addresses', array('total_received' => $total_received), array('address' => $address));
    }

    public function update_invoice_address($address, $total_received, $received = 1) {
        $this->db->update('invoice_addresses', array(
            'address' => $address,
            'received_amount' => $total_received,
            'received' => $received
        ), array('address' => $address));
    }

    public function update_address_balance($address, $new_balance, $previous_balance) {
        $this->db->update('addresses', array('balance' => $new_balance, 'previous_balance' => $previous_balance), array('address' => $address));
    }

    public function save_invoice_address($address, $receiving_address, $invoice_amount, $label = '', $callback_url, $forward = 1, $log_id, $crypto_type = 'BTC') {
        $this->db->insert('invoice_addresses', array(
            'address' => $address,
            'destination_address' => $receiving_address,
            'invoice_amount' => $invoice_amount,
            'label' => $label,
            'callback_url' => $callback_url,
            'forward' => $forward,
            'log_id' => $log_id,
            'crypto_type' => $crypto_type
        ));
    }

    public function get_invoice_address($address) {
        $query = $this->db->get_where('invoice_addresses', array('address' => $address), 1);
        return $query->row();
    }

}