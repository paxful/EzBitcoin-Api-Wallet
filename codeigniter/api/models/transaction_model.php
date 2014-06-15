<?php

class Transaction_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_new_transaction($tx_id, $user_id, $tx_type, $int_amount, $crypto_type, $to_address, $from_address, $comment) {
        $this->db->insert('transaction', array(
            'tx_id' => $tx_id,
            'user_id' => $user_id,
            'transaction_type' => $tx_type,
            'crypto_amount' => $int_amount,
            'crypto_type' => $crypto_type,
            'address_to' => $to_address,
            'account_to' => $to_address,
            'address_from' => $from_address,
            'account_from' => $from_address,
            'messagetext' => $comment
        ));
        return $this->db->insert_id();
    }

}