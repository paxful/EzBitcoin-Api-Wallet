<?php

class Balance_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function update_user_balance($new_balance, $user_id, $total_received = null) {
        if ($total_received) {
            $new_values = array('balance' => $new_balance, 'total_received' => $total_received);
        } else {
            $new_values = array('balance' => $new_balance);
        }
        $this->db->update(
            'balances',
            $new_values,
            array('user_id' => $user_id)
        );
    }
}