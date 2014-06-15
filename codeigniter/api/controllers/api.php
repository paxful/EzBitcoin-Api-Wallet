<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

    private $log_id;
    private $user;
    private $method;
    private $crypto_type = 'BTC';

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
            echo json_encode(array( 'error' => $e->getMessage()));
        }
    }

    public function validate_transaction($tx_id = '') {

        if (!$this->is_authenticated()) {
            return;
        }
        if(!$tx_id){
            echo json_encode(array( 'error' => NO_TX_ID));
            $this->update_log_response_msg($this->log_id, NO_TX_ID);
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
            echo json_encode(array( 'error' => $e->getMessage()));
        }
    }

    public function validate_address($address = '') {

        if (!$this->is_authenticated()) {
            return;
        }
        if(!$address){
            echo json_encode(array( 'error' => NO_ADDRESS));
            $this->update_log_response_msg($this->log_id, NO_ADDRESS);
            return;
        }

        try{
            $address_valid = $this->jsonrpcclient->validateaddress($address) ;
        } catch(Exception $e){
            echo json_encode(array( 'error' => $e->getMessage()));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        $intIsValid = $address_valid["isvalid"];
        if (!$intIsValid) {
            echo json_encode(array( 'error' => INVALID_ADDRESS));
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

        $this->load->model('User_model', '', TRUE);
        try {
            $new_wallet_addres = $this->jsonrpcclient->getnewaddress();

            $this->Log_model->insert_new_address($this->user->id, $new_wallet_addres, $this->crypto_type);
        } catch (Exception $e) {
            echo json_encode(array( 'error' => $e->getMessage()));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        //return error will be wallet address if it works
        if(RETURN_OUTPUTTYPE=="json"){
            echo json_encode(array( 'address' => $new_wallet_addres));
        } else {
            echo $new_wallet_addres;
        }
    }

    /**
     *
     * @param string $to_address
     * @param string $amount
     * @param string $comment
     * @param string $comment_to
     */
    public function sendtoaddress($to_address = '', $amount = '', $comment = '', $comment_to = '') {

        if (!$this->is_authenticated()) {
            return;
        }

        if (empty($to_address) or empty($amount)) {
            echo ADDRESS_AMOUNT_NOT_SPECIFIED;
            $this->update_log_response_msg($this->log_id, ADDRESS_AMOUNT_NOT_SPECIFIED);
            return;
        }

        $this->load->model('Transaction_model', '', TRUE);

		//Request error: -1 - value is type str, expected real
		$int_amount = (float)$amount;

		try{
            $tx_id = $this->jsonrpcclient->sendtoaddress( $to_address , $int_amount , $comment , $comment_to );
            if($tx_id){
                $this->Transaction_model->insert_new_transaction($tx_id, $this->user->id, TX_SEND, $int_amount, $this->crypto_type, $to_address, '', $comment);
            }

        } catch (Exception $e) {
            echo json_encode(array( 'error' => $e->getMessage()));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

		//return will be wallet address if it works
		if (RETURN_OUTPUTTYPE=="json") {
            echo json_encode(array( 'message' => "transaction successful", 'tx_hash' => $tx_id));
        } else {
            echo $tx_id;
        }
    }

    public function sendfrom_toaddress($from_address = '', $to_address = '', $amount = '', $comment = '', $comment_to = '') {
        if (!$this->is_authenticated()) {
            return;
        }

        if (empty($from_address) or empty($to_address) or empty($amount)) {
            echo ADDRESS_AMOUNT_NOT_SPECIFIED;
            $this->update_log_response_msg($this->log_id, ADDRESS_AMOUNT_NOT_SPECIFIED);
            return;
        }

        $this->load->model('Transaction_model', '', TRUE);

        //Request error: -1 - value is type str, expected real
        $int_amount = (float)$amount;

        try{
            $tx_id = $this->jsonrpcclient->sendtoaddress( $to_address , $int_amount , $comment , $comment_to );
            if($tx_id){
                $this->Transaction_model->insert_new_transaction($tx_id, $this->user->id, TX_SEND, $int_amount, $this->crypto_type, $to_address, $from_address = '', $comment);
            }

        } catch (Exception $e) {
            echo json_encode(array( 'error' => $e->getMessage()));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        //return will be wallet address if it works
        if (RETURN_OUTPUTTYPE=="json") {
            echo json_encode(array( 'message' => "transaction successful", 'tx_hash' => $tx_id));
        } else {
            echo $tx_id;
        }
    }

    public function callback() {

        if (!$this->is_authenticated()) {
            return;
        }

        //sends callback on receive notification
        //gets a transaction hash id
        //calls bitcoind d via RPC to get transaction info
        //calls a web url specified in the user account
        //called from /home/api/walletnotify.sh
        //sudo curl http://127.0.0.1/api/callback/?txid=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&local=n00nez&loginname=ammm&password=PsQWsO4sDLwqTxxx&debug=1

        $tx_id = $this->input->get('txid'); // check if not null
        if (!$tx_id) {
            echo json_encode(array( 'error' => NO_TX_ID_PROVIDED));
            return;
        }

        try {
            $tx_info = $this->jsonrpcclient->gettransaction($tx_id);

        } catch (Exception $e) {
            echo json_encode(array( 'error' => $e->getMessage()));
            $this->update_log_response_msg($this->log_id, $e->getMessage());
            return;
        }

        // TODO make it into separate class cause same thing is #validate_transaction() function
        $int_amount =           $tx_info["amount"] ;
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
            ."\n balance: ".$tx_info["balance"]
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
            ."\n fee: ".$tx_info["details"][0]["fee"]  // According to https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_calls_list, fee is returned, but it doesn't seem that way here
        ;
        if($this->config->item('api_is_debug_mode')) {
            echo nl2br($new)."<br>";
        }

        $this->load->model('Address_model', '', TRUE);
        $this->load->model('Transaction_model', '', TRUE);
        $address_model = $this->Address_model->get_address($to_address);

        $int_new_balance = $address_model->crypto_balance + $int_amount;

        $transaction_model = $this->Transaction_model->get_transaction_by_tx_id($tx_id);

        // TODO make a log_id column here to relate this transaction to specific log
        // first callback, because no transaction initially found in db
        if (!$transaction_model) {
            $this->Transaction_model->insert_new_transaction_from_callback($tx_id, $this->user->id, $this->method, TX_RECEIVE, $int_amount,
                $this->crypto_type, $to_address, $address_from, $confirmations, $tx_info["txid"], $block_hash, $block_index, $block_time, $time,
                $time_received, $category, $account_name, $int_new_balance);

                $address_total_received = $address_model->crypto_totalreceived + $int_amount;
                $this->Address_model->update_total_received_crypto($address_model->address, $address_total_received);
        } else {
            // bitcoind sent 2nd callback for the transaction which is 6th confirmation
            $this->Transaction_model($$transaction_model->id, $confirmations);
        }

        // now it is time to fire to the API user callback URL which is his app that is using this server's API

//        $app_response = $this->user->callbackurl."?secret=$strSecret&transaction_hash=$strTransactionID&address=$strAddress&input_address=$strAddress&userid=$strLabel2&value=$intAmount&confirms=$intConfirmations&server=amsterdam";
//        $app_response_json = file_get_contents($app_response);
//        $json_feed = json_decode($app_response_json);
//        $strCallbackResponse = $app_response_json;
//
//        $strReturnError = $strCallbackResponse ;
//        //CATCH ERROR AND EMAIL
//
//        if($strCallbackResponse=="*ok*"){
//            $strSQL2 = " , callback_status=1 ";
//        }
//
//        //if we get back an *ok* from the script then update the transations tbl status
//        $query="UPDATE " . $tbl_Transactions . " SET response_callback='".$strCallbackResponse."' , callback_url='$json_url' $strSQL2 WHERE txid='".$strTransactionID."'" ;
//        if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
//        mysqli_query($DB_LINK, $query)  or funct_die_and_Report(mysqli_error($DB_LINK), "Error updating transaction response.  Admin has been informed", "$strQueryString \n $query \n error= $strReturnError ", $strERRORPage, $intNewLogID) ;
//
//        //if we do not get back an ok we need some method of
//        //hitting the callback url over and over until we get an *ok* how?
//
//        if(RETURN_OUTPUTTYPE=="json"){
//        echo json_encode(array( 'confirmations'=>"$intConfirmations", 'address'=>"$strAddress", 'amount'=>"$intAmount", 'txid'=>"$strTransactionID", 'callback_url'=>"$json_url", 'error'=>"$strReturnError" ));
//        }else{
//            echo $strReturnError ; //die;
//        }

    }

    private function is_authenticated() {
        $method = $this->uri->segment(2, "empty");
        $apikey = $this->input->get('apikey');
        $ipaddress = $this->input->ip_address();;
        $full_query_str = $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');

        if ($this->input->get('cryptotype')) {
            $this->crypto_type = $this->input->get('cryptotype');
        }

        $agent = $this->agent->agent_string();
        $referrer = $this->agent->referrer();
        $error = $this->check_query_required_args();
        $this->log_id = $this->log_call($method, $apikey, $ipaddress, $full_query_str, $agent, $referrer, $error); // log it

        if (!$error) {
            $is_valid_user = $this->validate_user($apikey, $this->log_id); // error is printed inside #validate_user function
        } else {
            echo json_encode(array( 'error' => $error));
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
        $this->method = $method;
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
            echo json_encode(array( 'error' => "no user"));
            $this->Log_model->update_log_after_user_validation($log_id, $apikey, "no user");
            return false;
        }
        if ($user->apipassword != $this->input->get('apipassword')) {
            echo json_encode(array( 'error' => "wrong password"));
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