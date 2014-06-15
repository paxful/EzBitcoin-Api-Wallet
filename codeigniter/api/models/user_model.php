<?php

class User_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_user($apikey) {
        $query = $this->db->get_where('users', array('apikey' => $apikey), 1);
        return $query->row();
    }

}