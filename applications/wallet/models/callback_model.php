<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Callback_model extends CI_Model {

    private static $table_name = 'tbl_callbacks';

    function __construct() {
        parent::__construct();
    }

    public function insert_callback($data) {
        $this->db->insert(Callback_model::$table_name, $data);
        return $this->db->insert_id();
    }
}