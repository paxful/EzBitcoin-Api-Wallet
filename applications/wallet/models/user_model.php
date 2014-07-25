<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

    private static $profile_table_name = 'user_profiles';
    private static $account_table_name = 'user_accounts';

    function __construct() {
        parent::__construct();
    }

    public function get_user_by_wallet_address($address) {
        $this->db->select('*');
        $this->db->from('user_accounts');
        $this->db->join('user_profiles', 'user_accounts.id = user_profiles.user_account_id');
        $this->db->where(array('wallet_address' => $address));
        $query = $this->db->get();
        return $query->row();
    }


}