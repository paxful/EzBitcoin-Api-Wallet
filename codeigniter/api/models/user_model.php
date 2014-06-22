<?php

class User_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_user($guid) {
        $query = $this->db->get_where('users', array('guid' => $guid), 1);
        return $query->row();
    }

    public function get_user_balance($user_id, $crypto_type = 'BTC') {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('balances', 'users.id = balances.user_id');
        $this->db->where(array('crypto_type' => $crypto_type));
        $query = $this->db->get();
        return $query->row();
    }

}