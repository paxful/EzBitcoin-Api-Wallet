<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    private static $table_name = 'tbl_transactions';

    function __construct() {
        parent::__construct();
    }

    public function get_transaction_by_tx_hash($tx_hash) {
        $query = $this->db->get_where(Transaction_model::$table_name, array('tx_hash' => $tx_hash), 1);
        return $query->row();
    }

    public function add_transaction($data) {
        $this->db->insert('transactions', array(
            'tx_id' => $tx_id,
            'user_id' => $user_id,
            'transaction_type' => $tx_type,
            'crypto_amount' => $int_amount,
            'crypto_type' => $crypto_type,
            'address_to' => $to_address,
            'account_to' => $to_address,
            'address_from' => $from_address,
            'account_from' => $from_address,
            'messagetext' => $comment,
            'log_id' => $log_id
        ));
        return $this->db->insert_id();
    }

}