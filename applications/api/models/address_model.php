<?php

class Address_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_new_address($user_id, $new_wallet_addres, $label, $crypto_type) {
        $this->db->insert('addresses', array(
                'address' => $new_wallet_addres,
                'user_id' => $user_id,
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

    public function update_address_balance($address, $new_balance, $previous_balance) {
        $this->db->update('addresses', array('balance' => $new_balance, 'previous_balance' => $previous_balance), array('address' => $address));
    }

}