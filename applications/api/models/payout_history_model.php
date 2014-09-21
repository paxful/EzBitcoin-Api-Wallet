<?php
class Payout_history_model extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	public function insert_new_transaction($data) {
		$this->db->insert('payout_history', $data);
		return $this->db->insert_id();
	}
}