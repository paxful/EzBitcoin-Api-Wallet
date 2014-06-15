<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

    private $log_id;
    private $user;

    public function __construct()
    {
        parent::__construct();
        $jsonrpc_conn_string = $this->config->item('jsonrpc_connectionstring');
        $jsonrpc_debug = $this->config->item('bitcoind_is_debug_mode');
        $this->load->library('jsonrpcclient', array('url' => $jsonrpc_conn_string, 'debug' => $jsonrpc_debug));

        $this->load->library('user_agent');
        $this->load->model('Log_model', '', TRUE); // load up log model to log all requests
        $this->load->model('User_model', '', TRUE); // load up user model
    }

	public function index() {
		$this->load->view('welcome_message');
	}

    public function balance($account = '') {
        try {
            if ($account) {
                $balance = $this->jsonrpcclient->getbalance($account);
            } else {
                $balance = $this->jsonrpcclient->getbalance();
            }
            echo $balance;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function validate_transaction($tx_id = '') {

        if (!$this->is_authenticated()) {
            return;
        }
        if(!$tx_id){
            $this->update_log_response_msg($this->log_id, NO_TX_ID);
            echo NO_TX_ID;
            return;
        }

        try{
            $tx_info = $this->jsonrpcclient->gettransaction($tx_id);

            //if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
            //Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and
            //use the getrawtransaction call to request information about any transaction
            //$strRawHex = $this->jsonrpcclient->getrawtransaction($strTransaction);
            //$objJSON = $this->jsonrpcclient->decoderawtransaction($strRawHex);

            //bind values to variables
            $transaction_id = 	    $tx_info["txid"] ;

            $new = "Transaction hash: ".$transaction_id[1]
                ."\n amount: ".$tx_info["amount"]
                ."\n confirmations: ".$tx_info["confirmations"]
                ."\n blockhash: ".$tx_info["blockhash"]
                ."\n blockindex: ".$tx_info["blockindex"]
                ."\n blocktime: ".$tx_info["blocktime"]
                ."\n txid: ".$tx_info["txid"]
                ."\n time: ".$tx_info["time"]
                ."\n timereceived: ".$tx_info["timereceived"]
                ."\n account: ".$tx_info["details"][0]["account"]
                ."\n address: ".$tx_info["details"][0]["address"]
                ."\n category: ".$tx_info["details"][0]["category"]
                ."\n amount: ".$tx_info["details"][0]["amount"]
                ."\n"
            ;
            if($this->config->item('api_is_debug_mode')) {
                echo nl2br($new) . "\n";
            }
            echo "valid";

        } catch (Exception $e) {
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            echo $e->getMessage();
        }
    }

    public function validate_address($address = '') {

        if (!$this->is_authenticated()) {
            return;
        }
        if(!$address){
            echo NO_ADDRESS;
            $this->update_log_response_msg($this->log_id, NO_ADDRESS);
            return;
        }

        try{
            $address_valid = $this->jsonrpcclient->validateaddress($address) ;
        } catch(Exception $e){
            echo $e->getMessage();
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        $intIsValid = $address_valid["isvalid"];
        if (!$intIsValid) {
            echo INVALID_ADDRESS;
            $this->update_log_response_msg($this->log_id, INVALID_ADDRESS);
            return;
        }

        $address = $address_valid["address"];
        $intIsMine = $address_valid["ismine"];

        //return the address is the bitcoind and the current merchant owns the address
        if($intIsMine AND $address){
            // TODO get stuff from db
        }

        //return error will be wallet address if it works
        if(RETURN_OUTPUTTYPE == "json"){
            echo json_encode(array( 'isvalid' => $intIsValid, 'address' => $address, 'ismine' => $intIsMine ));
        } else {
            echo "$intIsValid|$address|$intIsMine" ;
        }
    }

    /**
     * create new address in blockchain
     */
    public function new_address() {

        if (!$this->is_authenticated()) {
            return;
        }

        // TODO add labels specific to the app

        $crypto_type = 'BTC';
        $this->load->model('User_model', '', TRUE);
        try {
            $new_wallet_addres = $this->jsonrpcclient->getnewaddress();

            if ($this->input->get('cryptotype')) {
                $crypto_type = $this->input->get('cryptotype');
            }

            $this->Log_model->insert_new_address($this->user->id, $new_wallet_addres, $crypto_type);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        //return error will be wallet address if it works
        if(RETURN_OUTPUTTYPE=="json"){
            echo json_encode(array( 'address'=>"$new_wallet_addres"));
        } else {
            echo $new_wallet_addres;
        }
    }

    public function sendfromaddress($address = '', $amount = '', $comment = '', $comment_to = '') {

        if (!$this->is_authenticated()) {
            return;
        }

        if (empty($address) or empty($amount)) {
            echo ADDRESS_AMOUNT_NOT_SPECIFIED;
            $this->update_log_response_msg($this->log_id, ADDRESS_AMOUNT_NOT_SPECIFIED);
            return;
        }

		//Request error: -1 - value is type str, expected real
		$int_amount = (float)$amount;

		try{
            $txid = $this->jsonrpcclient->sendtoaddress( $address , $int_amount , $comment , $comment_to );
            if($txid){
                // TODO insert the transaction to db
            }

        } catch (Exception $e) {
            // TODO report admin of failure of sending out
            echo json_encode(array( 'error' => $e->getMessage()));
            return;
        }

		//return will be wallet address if it works
		if (RETURN_OUTPUTTYPE=="json") {
            echo json_encode(array( 'message'=>"transaction successful", 'tx_hash'=>"$txid"));
        } else {
            echo $txid;
        }
    }

    public function sendfrom_toaddress($address = '', $amount = '', $comment = '', $comment_to = '') {

    }

    public function callback() {

        if (!$this->is_authenticated()) {
            return;
        }

    }

    private function is_authenticated() {
        $method = $this->uri->segment(2, "empty");
        $apikey = $this->input->get('apikey');
        $ipaddress = $this->input->ip_address();;
        $full_query_str = $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');
        $agent = $this->agent->agent_string();
        $referrer = $this->agent->referrer();
        $error = $this->check_query_required_args();
        $this->log_id = $this->log_call($method, $apikey, $ipaddress, $full_query_str, $agent, $referrer, $error); // log it
        if (!$error) {
            $is_valid_user = $this->validate_user($apikey, $this->log_id); // error is printed inside #validate_user function
        } else {
            echo $error; // print error to screen
            return false;
        }
        if (!$is_valid_user) {
            return false;
        }
        return true;
    }

    private function log_call($method, $apikey, $ipaddress, $querystring, $agent, $referrer, $response) {
        $data = array(
            'method' => $method,
            'apikey' => $apikey = isset($apikey) == true ? $apikey : '',
            'ipaddress' => $ipaddress,
            'querystring' => $querystring,
            'agent' => $agent,
            'referrer' => $referrer,
            'response' => $response
        );
        return $this->Log_model->insert_log_call($data);
    }

    private function check_query_required_args() {
        $method = $this->uri->segment(2);
        $apikey = $this->input->get('apikey');
        $apipassword = $this->input->get('apipassword');
        $invalid_query = array();
        if (!$method) $invalid_query[] = 'no method';
        if (!$apikey) $invalid_query[] = 'no apikey';
        if (!$apipassword) $invalid_query[] = 'no apipassword';
        return isset($invalid_query) == true ? implode($invalid_query, ', ') : null; // if some of required query params were missing, then return null
    }

    /**
     * Validate user and update log table row if validation was not successful
     * @param $apikey
     * @param $apipassword
     * @param $log_id
     */
    private function validate_user($apikey, $log_id) {
        $user = $this->User_model->get_user($apikey);

        if (!$user) {
            echo "No user<br/>";
            $this->Log_model->update_log_after_user_validation($log_id, $apikey, "no user");
            return false;
        }
        if ($user->apipassword != $this->input->get('apipassword')) {
            echo "wrong password<br />";
            $this->Log_model->update_log_after_user_validation($log_id, $apikey, "wrong password");
            return false;
        }
        $this->user = $user;
        return true;
    }

    private function update_log_response_msg($log_id, $response) {
        $this->Log_model->update_log_response($log_id, $response);
    }
}