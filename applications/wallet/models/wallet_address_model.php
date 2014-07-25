<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wallet_address_model extends CI_Model {

    private static $table_name = 'tbl_wallet_addresses';

    function __construct() {
        parent::__construct();
    }

    public function get_user_by_address($address) {
        $query = $this->db->get_where(Wallet_address_model::$table_name, array('wallet_address' => $address), 1);
        return $query->row();
    }
}