<?php

class Log_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_log_call($data) {
        $this->db->insert('logs', $data);
        return $this->db->insert_id();
    }

    public function update_log_after_user_validation($log_id, $apikey, $response) {
        $this->db->update('logs', array('apikey' => $apikey, 'response' => $response), array('id' => $log_id));
    }

    public function update_log_response($log_id, $response) {
        $this->db->update('logs', array('response' => $response), array('id' => $log_id));
    }

}