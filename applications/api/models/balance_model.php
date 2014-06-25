<?php

class Balance_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function update_user_balance($new_balance, $user_id) {
        $this->db->update('balances', array('balance' => $new_balance), array('user_id' => $user_id));
    }
}