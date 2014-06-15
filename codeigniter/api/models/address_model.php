<?php

class Address_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_new_address($user_id, $new_wallet_addres, $crypto_type) {
        $this->db->insert('addresses', array(
            'address' => $new_wallet_addres, 'user_id' => $user_id, 'crypto_type' => $crypto_type)
        );
        return $this->db->insert_id();
    }

    public function get_address($address) {
        $query = $this->db->get_where('addresses', array('address' => $address), 1);
        return $query->row();
    }

    public function update_total_received_crypto($address, $total_received) {
        $this->db->update('addresses', array('crypto_totalreceived' => $total_received), array('address' => $address));
    }

}