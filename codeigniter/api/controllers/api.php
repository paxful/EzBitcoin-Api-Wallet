<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

    private $log_id;
    private $user;
    private $method;
    private $crypto_type = 'BTC';
    private $jsonrpc_debug;

    public function __construct()
    {
        parent::__construct();
        $jsonrpc_conn_string = $this->config->item('jsonrpc_connectionstring');
        $this->jsonrpc_debug = $this->config->item('bitcoind_is_debug_mode');
        $this->load->library('jsonrpcclient', array('url' => $jsonrpc_conn_string, 'debug' => $this->jsonrpc_debug));

        $this->load->library('user_agent');
        $this->load->model('Log_model', '', TRUE); // load up log model to log all requests
        $this->load->model('User_model', '', TRUE); // load up user model
        $this->load->model('Address_model', '', TRUE);

        if ($this->input->get('debug') or $this->jsonrpc_debug == true) {
            echo "URI segment: ".$this->uri->uri_string()."\n";
            echo "Controller: ".$this->uri->segment(1).", GUID: ".$this->uri->segment(2).", method: ".$this->uri->segment(3)."\n";
        }
    }

	public function index() {
		$this->load->view('welcome_message');
	}

    public function balance() {
        if (!$this->is_authenticated()) {
            return;
        }

        $user_info = $this->User_model->get_user_balance($this->user->id, $this->crypto_type);
        $response = null;


        if ($user_info) {
            $response = json_encode(array('balance' => $user_info->balance, 'crypto_type' => $this->crypto_type));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        } else {
            $response = json_encode(array('error' => 'balance not found for crypto type '.$this->crypto_type));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function address_balance() {
        if (!$this->is_authenticated()) {
            return;
        }

        $address = $this->input->get('address');
        $confirmations = $this->input->get('confirmations');
        if (!$confirmations) {
            $confirmations = 0;
        }

        try {
            if ($address) {
                $balance = $this->jsonrpcclient->getreceivedbyaddress($address, $confirmations);
            } else {
                // TODO query user db for his all adresses total
                $balance = $this->jsonrpcclient->getbalance();
            }
            // TODO convert to satoshis, check how to know total received
            // TODO set content type applicaiton/json
            echo json_encode(array('balance' => $balance, 'address' => $address, 'total_received' => $balance));
            $this->update_log_response_msg($this->log_id, $balance);
        } catch (Exception $e) {
            $response = json_encode(array( 'error' => $e->getMessage()));
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function validate_transaction() {

        if (!$this->is_authenticated()) {
            return;
        }

        $tx_id = $this->input->get('txid');
        if(!$tx_id){
            $response = json_encode(array( 'error' => NO_TX_ID));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
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
            if(($this->input->get('debug') or $this->jsonrpc_debug == true)) {
                echo nl2br($new) . "\n";
            }
            $response = "valid";
            echo $response;
            $this->update_log_response_msg($this->log_id, $response);

        } catch (Exception $e) {
            $response = json_encode(array( 'error' => $e->getMessage()));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function validate_address() {

        if (!$this->is_authenticated()) {
            return;
        }
        $address = $this->input->get('address');
        if(!$address){
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array( 'error' => NO_ADDRESS)));
            $this->update_log_response_msg($this->log_id, NO_ADDRESS);
            return;
        }

        try{
            $address_valid = $this->jsonrpcclient->validateaddress($address) ;
        } catch(Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array( 'error' => $e->getMessage())));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        $is_valid = $address_valid["isvalid"];
        if (!$is_valid) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array( 'error' => INVALID_ADDRESS)));
            $this->update_log_response_msg($this->log_id, INVALID_ADDRESS);
            return;
        }

        $address = $address_valid["address"];
//        $is_mine = $address_valid["ismine"]; // this is for all users of the API inside bitcoind
        $is_mine = false;

        $user_address = $this->Address_model->get_address_for_user($address, $this->user->id);

        if($user_address){
            $is_mine = true;
        }

        $response = null;
        if(RETURN_OUTPUTTYPE == "json") {
            $response = json_encode(array( 'isvalid' => $is_valid, 'address' => $address, 'ismine' => $is_mine ));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        } else {
            $response = "$is_valid|$address|$is_mine";
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function new_address() {

        if (!$this->is_authenticated()) {
            return;
        }

        $this->load->model('User_model', '', TRUE);
        try {
            $new_wallet_addres = $this->jsonrpcclient->getnewaddress();

            $label = $this->input->get('label');
            if (!$label) {
                $label = '';
            }

            $this->Address_model->insert_new_address($this->user->id, $new_wallet_addres, $label, $this->crypto_type);
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array( 'error' => $e->getMessage())));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        //return error will be wallet address if it works
        $response = null;
        if(RETURN_OUTPUTTYPE=="json") {
            $response = json_encode(array( 'address' => $new_wallet_addres, 'label' => $label));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        } else {
            $response = $new_wallet_addres;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function payment() {

        if (!$this->is_authenticated()) {
            return;
        }

        $to_address =  $this->input->get('to');
        $amount =   $this->input->get('amount');
        $fee =      $this->input->get('fee');
        $note =     $this->input->get('note');
        $cryptotype = $this->input->get('cryptotype');

        if (!$note) {
            $note = '';
        }

        if (empty($to_address) or empty($amount)) {
            $response = json_encode(array('error' => ADDRESS_AMOUNT_NOT_SPECIFIED));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }

        $this->db->trans_start();

        $user_balance = $this->User_model->get_user_balance($this->user->id);
        if ($user_balance->balance < $amount) {
            $response = json_encode(array('error' => NO_FUNDS));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }

        $this->load->model('Transaction_model', '', TRUE);
        $this->load->model('Balance_model', '', TRUE);

        $new_balance = $user_balance->balance - $amount;
        $this->Balance_model->update_user_balance($new_balance, $this->user->id);

        $bitcoin_amount = (float) ($amount / SATOSHIS_FRACTION);
		try{
            $tx_id = $this->jsonrpcclient->sendtoaddress( $to_address , $bitcoin_amount, $note);
            if ($tx_id) {
                $this->Transaction_model->insert_new_transaction($tx_id, $this->user->id, TX_SEND, $bitcoin_amount, $this->crypto_type, $to_address, '', $note, $this->log_id);
            } // TODO in else should throw exception when tx_id is not returned ?

        } catch (Exception $e) {
            $response = json_encode(array( 'error' => $e->getMessage()));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }
        $this->db->trans_complete();

        $response = null;
        $message = 'Sent ' . $bitcoin_amount . ' ' . $cryptotype. ' to ' . $to_address;
		if (RETURN_OUTPUTTYPE=="json") {
            $response = json_encode(array( 'message' => $message, 'tx_hash' => $tx_id));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        } else {
            $response = $message.'|'.$tx_id;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    public function callback() {

        /* the url structure is different, so different segments of URI */
        $method =       $this->uri->segment(1);
        $ipaddress =    $this->input->ip_address();;
        $full_query_str = $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');

        if ($this->input->get('cryptotype')) {
            $this->crypto_type = $this->input->get('cryptotype');
        }

        $agent = $this->agent->agent_string();
        $referrer = $this->agent->referrer();
        $this->log_id = $this->log_call($method, '', $ipaddress, $full_query_str, $agent, $referrer, ''); // log it

        $secret = $this->input->get('secret');
        if ($secret != 'testingbtc12') {
            $response = json_encode(array( 'error' => NO_SECRET_FOR_CALLBACK));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }
        /*--------------------------------------------*/


        //sends callback on receive notification
        //gets a transaction hash id
        //calls bitcoind d via RPC to get transaction info
        //calls a web url specified in the user account
        //called from /home/api/walletnotify.sh
        //sudo curl http://127.0.0.1/api/callback/?txid=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&local=n00nez&loginname=ammm&password=PsQWsO4sDLwqTxxx&debug=1

        $tx_id = $this->input->get('txid'); // check if not null
        if (!$tx_id) {
            $response = json_encode(array( 'error' => NO_TX_ID_PROVIDED));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }

        try {
            $tx_info = $this->jsonrpcclient->gettransaction($tx_id);

        } catch (Exception $e) {
            $response = json_encode(array( 'error' => $e->getMessage()));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }

        // TODO make it into separate class cause same thing is #validate_transaction() function
        echo "<pre>".print_r($tx_info)."</pre><br />";
        $btc_amount =           $tx_info["amount"] ;
        $confirmations = 	    $tx_info["confirmations"] ;
        $account_name = 		$tx_info["details"][0]["account"] ;
        $to_address =           $tx_info["details"][0]["address"]; // address where transaction was sent to. from address may be multiple inputs which means many addresses
        $address_from = 		"" ; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
        $time = 				$tx_info["time"] ;
        $time_received = 		$tx_info["timereceived"];
        $category = 			$tx_info["details"][0]["category"];
        $block_hash = 		    $tx_info["blockhash"];
        $block_index = 		    $tx_info["blockindex"];
        $block_time = 		    $tx_info["blocktime"];

        $new = "Transaction hash: ".$tx_id
            ."\n amount: ".$tx_info['details'][0]["amount"]
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
        ;
        if(($this->input->get('debug') or $this->jsonrpc_debug == true)) {
            echo nl2br($new)."\n";
        }

        $this->load->model('Transaction_model', '', TRUE);
        $this->load->model('Balance_model', '', TRUE);
        $address_model = $this->Address_model->get_address($to_address);
        $transaction_model = $this->Transaction_model->get_transaction_by_tx_id($tx_id);

        $satoshi_amount = $btc_amount * SATOSHIS_FRACTION;
        $new_address_balance = $address_model->crypto_balance + $satoshi_amount;

        $this->db->trans_start();

        /* It is incoming transaction, because it is sent to some of the inner adresses */
        if ($address_model) {
            if (!$transaction_model) { // first callback, because no transaction initially found in db
                $transaction_model_id = $this->Transaction_model->insert_new_transaction_from_callback(
                    $tx_id, $this->user->id, TX_RECEIVE, $btc_amount, $this->crypto_type,
                    $to_address, $address_from, $confirmations, $block_hash, $block_index,
                    $block_time, $time, $time_received, $category, $account_name, $new_address_balance, $this->log_id
                );

                $address_total_received = $address_model->crypto_totalreceived + $btc_amount; // TODO set final balance to
                $this->Address_model->update_total_received_crypto($address_model->address, $address_total_received);
                $transaction_model = $this->Transaction_model->get_transaction_by_id($transaction_model_id);
                $new_user_balance = $this->user->balance + $satoshi_amount;
                $this->Balance_model->update_user_balance($new_user_balance, $this->user->id);
            } else {
                /* bitcoind sent 2nd callback for the transaction which is 6th confirmation */
                $this->Transaction_model->update_tx_confirmations($transaction_model->id, $confirmations);
            }


        /* It is outgoing transaction and the change is sent back to some of the change address */
        } else {
            $this->Transaction_model->update_tx_confirmations($transaction_model->id, $confirmations); // assuming the transaction was found in db
        }

        $this->db->trans_complete();


        // now it is time to fire to the API user callback URL which is his app that is using this server's API
        // mind the secret here, that app has to verify that it is coming from the API server not somebody else
        $full_callback_url = $this->user->callbackurl."?secret=".$this->user->secret."&transaction_hash=".$tx_id."&input_address=".$to_address."&value=".$satoshi_amount."&confirms=".$confirmations;
        $app_response = file_get_contents($full_callback_url);

        $callback_status = null;
        if($app_response == "*ok*") {
            $callback_status = 1;
        }

        //if we get back an OK from the script then update the transactions status
        $this->Transaction_model->update_tx_on_app_callback($transaction_model->id, $app_response, $full_callback_url, $callback_status);

        $response = null;
        if (RETURN_OUTPUTTYPE == "json") {
            $response = json_encode(array(
                'confirmations' => $confirmations, 'address' => $to_address, 'amount' => $btc_amount, 'txid' => $tx_id, 'callback_url' => $full_callback_url, 'response' => $app_response ));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        } else {
            $response = $app_response;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    private function is_authenticated() {
        $guid =         $this->uri->segment(2);
        $method =       $this->uri->segment(3, "empty");
        $ipaddress =    $this->input->ip_address();;
        $full_query_str = $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');

        if ($this->input->get('cryptotype')) {
            $this->crypto_type = $this->input->get('cryptotype');
        }

        $agent = $this->agent->agent_string();
        $referrer = $this->agent->referrer();
        $error = $this->check_query_required_args();
        $this->log_id = $this->log_call($method, $guid, $ipaddress, $full_query_str, $agent, $referrer, $error); // log it

        if (!$error) {
            $is_valid_user = $this->validate_user($guid, $this->log_id); // error is printed inside #validate_user function
        } else {
            echo json_encode(array( 'error' => $error));
            return false;
        }
        if (!$is_valid_user) {
            return false;
        }
        return true;
    }

    private function log_call($method, $guid, $ipaddress, $querystring, $agent, $referrer, $response) {
        $data = array(
            'method' => $method,
            'guid' => $guid = isset($guid) == true ? $guid : '',
            'ipaddress' => $ipaddress,
            'querystring' => $querystring,
            'agent' => $agent,
            'referrer' => $referrer,
            'response' => $response
        );
        return $this->Log_model->insert_log_call($data);
    }

    private function check_query_required_args() {
        $method =       $this->uri->segment(3);
        $guid =         $this->uri->segment(2);
        $password =     $this->input->get('password');
        $invalid_query = array();
        if (!$method) $invalid_query[] = 'no method';
        if (!$guid) $invalid_query[] = 'no guid';
        if (!$password) $invalid_query[] = 'no password';
        $this->method = $method;
        return isset($invalid_query) == true ? implode($invalid_query, ', ') : null; // if some of required query params were missing, then return null
    }

    /**
     * Validate user and update log table row if validation was not successful
     * @param $guid
     * @param $log_id
     */
    private function validate_user($guid, $log_id) {
        $user = $this->User_model->get_user($guid);

        if (!$user) {
            echo json_encode(array( 'error' => NO_USER));
            $this->Log_model->update_log_after_user_validation($log_id, $guid, NO_USER);
            return false;
        }
        if ($user->password != $this->input->get('password')) {
            echo json_encode(array( 'error' => WRONG_PASSWD));
            $this->Log_model->update_log_after_user_validation($log_id, $guid, WRONG_PASSWD);
            return false;
        }
        $this->user = $user;
        return true;
    }

    private function update_log_response_msg($log_id, $response) {
        $this->Log_model->update_log_response($log_id, $response);
    }
}