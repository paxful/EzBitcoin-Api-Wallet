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
        if (DEBUG_API) {
            $this->output->enable_profiler(TRUE); // for local testing
        }

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

	public function index()
	{
		$this->load->view('start');
	}

    /**
     * example.com/api/<guid>/balance?password=xxx&debug=1
     */
    public function balance() {
        if (!$this->is_authenticated()) {
            return;
        }

        $user_info = $this->User_model->get_user_balance($this->user->id, $this->crypto_type);
        $response = null;

	    log_message('info', 'Queried balance for '.$user_info->email.': '.self::satoshiToBtc($user_info->balance).' bitcoins');

        if ($user_info) {
            $response = json_encode(array('balance' => $user_info->balance, 'crypto_type' => $this->crypto_type));
            $this->output
                ->set_content_type($this->input->get('debug') == 1 ? 'text/html' : 'application/json') // TODO think of better way
                ->set_output($response);
        } else {
            $response = json_encode(array('error' => 'balance not found for crypto type '.$this->crypto_type));
            $this->output
                ->set_content_type('application/json')
                ->set_output($response);
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    /**
     * Untested
     */
    public function address_balance() {
        if (!$this->is_authenticated()) {
            return;
        }

        $address = $this->input->get('address');
        $confirmations = $this->input->get('confirmations');
        if (!$confirmations) {
            $confirmations = 0;
        }
        if (!$address) {
            $address = 'all';
        }

        try {
            if ($address) {
                /* TODO getting address balance with certain confirmations is more complicated, need to scan through all transactions and
                / sum all transactions related to that address where confirmations >= n */

                $user_address = $this->Address_model->get_address_for_user($address, $this->user->id);

                if ($user_address) {
                    $response = json_encode(array('balance' => $user_address->balance, 'address' => $address, 'total_received' => $user_address->total_received, 'crypto_type' => $this->crypto_type));
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output($response);
                } else {
                    $response = json_encode(array('error' => NO_USER_ADDRESS));
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output($response);
                }
            } else {
                $user_balance = $this->User_model->get_user_balance($this->user->id);
                $response = json_encode(array('balance' => $user_balance->balance, 'address' => $address, 'total_received' => $user_balance->total_received, 'crypto_type' => $this->crypto_type));
                $this->output
                    ->set_content_type('application/json')
                    ->set_output($response);
            }

        } catch (Exception $e) {
            $response = json_encode(array( 'error' => $e->getMessage()));
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    /**
     * example.com/api/<guid>/validate_transaction?txid=xxx&&password=zzz&debug=1
     */
    public function validate_transaction() {

        if (!$this->is_authenticated()) {
            return;
        }

        $tx_id = $this->input->get('txid');
        if(!$tx_id){
            $this->log_exception_response("#validate_transaction, no tx id: ".NO_TX_ID);
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
            $response = "valid|txid:".$tx_id;
            echo $response;

        } catch (Exception $e) {
            $this->log_exception_response("#validate_transaction, get transaction exception: ".$e->getMessage());
            return;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    /**
     * example.com/api/<guid>/validate_address?address=xxx&password=zzz&debug=1
     */
    public function validate_address() {

        if (!$this->is_authenticated()) {
            return;
        }
        $address = $this->input->get('address');
        if(!$address){
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output(json_encode(array( 'error' => NO_ADDRESS)));
            $this->update_log_response_msg($this->log_id, NO_ADDRESS);
            return;
        }

        try{
            $address_valid = $this->jsonrpcclient->validateaddress($address) ;
        } catch(Exception $e) {
            $this->log_exception_response("#validate_address, validate address exception: ".$e->getMessage());
            return;
        }

        $is_valid = $address_valid["isvalid"];
        if (!$is_valid) {
            $this->log_exception_response("#validate_address, invalid address: ".INVALID_ADDRESS);
            return;
        }

        $address = $address_valid["address"];
//      $address_valid["ismine"]; // this is for all users of the API inside bitcoind
        $is_mine = false;

        $user_address = $this->Address_model->get_address_for_user($address, $this->user->id);

        if($user_address){
            $is_mine = true;
        }

        $response = null;
        if(RETURN_OUTPUTTYPE == "json") {
            $response = json_encode(array( 'isvalid' => $is_valid, 'address' => $address, 'ismine' => $is_mine ));
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
        } else {
            $response = "$is_valid|$address|$is_mine";
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    /**
     * example.com/api/<guid>/new_address?label=xxx&password=zzz&debug=1
     */
    public function new_address() {

        if (!$this->is_authenticated()) {
            return;
        }

	    $this->load->model('User_model', '', TRUE);
        try {
            $new_wallet_address = $this->jsonrpcclient->getnewaddress($this->user->id); // bitcoind account is the id of the API user in db

            $label = $this->input->get('label');
            if (!$label) {
                $label = '';
            }

            $this->Address_model->insert_new_address($this->user->id, $new_wallet_address, $label, $this->crypto_type);
        } catch (Exception $e) {
            $this->log_exception_response("#new_address, get new address exception: ".$e->getMessage());
            return;
        }

	    log_message('info', "Created new address $new_wallet_address for user id: ".$this->user->email);

        //return error will be wallet address if it works
        $response = null;
        if(RETURN_OUTPUTTYPE=="json") {
            $response = json_encode(array( 'address' => $new_wallet_address, 'label' => $label));
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
        } else {
            $response = $new_wallet_address;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

    /**
     * example.com/api/<guid>/payment?to=xxx&amount=satoshis&note=yyy&password=zzz&debug=1
     */
    public function payment() {

        if (!$this->is_authenticated()) {
            return;
        }

        $to_address =  $this->input->get('to');
        $amount =   $this->input->get('amount');
        $note =     $this->input->get('note');
        $cryptotype = $this->input->get('cryptotype');


        if (!$note) {
            $note = '';
        }

	    log_message('info', "=== Starting payment to $to_address, note: $note, amount: ".self::satoshiToBtc($amount).' ===');

        if (empty($to_address) or empty($amount)) {
            $this->log_exception_response("#payment, empty address or amount: ".ADDRESS_AMOUNT_NOT_SPECIFIED);
            return;
        }

        $isTestnet = $this->config->item('is_testnet');
        $this->load->library('bitcoinaddressvalidator');
        $isValidAddress = $this->bitcoinaddressvalidator->isValid($to_address, $isTestnet ? BitcoinAddressValidator::TESTNET : null);
        if (!$isValidAddress) {
            $this->log_exception_response("#payment, non valid address: ".INVALID_ADDRESS);
            return;
        }

        $this->db->trans_start();

        $user_balance = $this->User_model->get_user_balance($this->user->id);

        log_message('info', 'User initial balance: '.self::satoshiToBtc($user_balance->balance).' bitcoins');

        if ($user_balance->balance < $amount) {
            $this->log_exception_response("#payment, insufficient funds: ".NO_FUNDS);
            return;
        }

        $this->load->model('Transaction_model', '', TRUE);
        $this->load->model('Balance_model', '', TRUE);

        $new_balance = bcsub($user_balance->balance, $amount);

	    log_message('info', 'User new balance: '.self::satoshiToBtc($new_balance).' bitcoins');

//        $this->Address_model->update_address_balance($to_address, ); // TODO shit how do we know from which address was spent... ?
        $this->Balance_model->update_user_balance($new_balance, $this->user->id);

        $bitcoin_amount = self::satoshiToBtc($amount); // return float

        try{
            $tx_id = $this->jsonrpcclient->sendtoaddress( $to_address , $bitcoin_amount, $note);
            if ($tx_id) {
                // if it fails here on inserting new transaction, then this transaction will be rolled back - user balance not updated, but jsonrpcclient will send out.
                // think of a clever way on which step it failed and accordingly let know if balance was updated or not
                $this->Transaction_model->insert_new_transaction($tx_id, $this->user->id, TX_SEND, $amount, $this->crypto_type, $to_address, '', $note, $this->log_id);
            }

        } catch (Exception $e) {
            $this->log_exception_response("#payment, send to address exception: ".$e->getMessage());
            return;
        }
        $this->db->trans_complete();

	    log_message('info', "=== Sending to  $to_address, amount: ".self::satoshiToBtc($amount).' completed ===');

        $response = null;
        $message = 'Sent ' . $bitcoin_amount . ' ' . $this->crypto_type. ' to ' . $to_address;
		if (RETURN_OUTPUTTYPE=="json") {
            $response = json_encode(array( 'message' => $message, 'tx_hash' => $tx_id));
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
        } else {
            $response = $message.'|'.$tx_id;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
    }

	/**
	 * Callback is initiated when:
	 * Receiving transaction gets into mempool
	 * Sending out transaction - has negative amount
	 * Transaction gets 1st confirmation
	 */
    public function callback() {

        log_message('info', '====== CALLBACK STARTED ======');
        /* the url structure is different, so different segments of URI */
        $method =       $this->uri->segment(2);
        $ipaddress =    $this->input->ip_address();
        $full_query_str = $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');

        if ($this->input->get('cryptotype')) {
            $this->crypto_type = $this->input->get('cryptotype');
        }

        $agent = $this->agent->agent_string();
        $referrer = $this->agent->referrer();
        $this->log_id = $this->log_call($method, '', $ipaddress, $full_query_str, $agent, $referrer, ''); // log it

        $secret = $this->input->get('secret');
        $server_callback_secret = $this->config->item('callback_secret');

        if ($secret != $server_callback_secret) {
            $this->log_exception_response("#callback, secret mismatch: ".NO_SECRET_FOR_CALLBACK);
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
            $this->log_exception_response("#callback, no tx id: ".NO_TX_ID_PROVIDED);
            return;
        }

        try {
            $tx_info = $this->jsonrpcclient->gettransaction($tx_id);

        } catch (Exception $e) {
            $this->log_exception_response("#callback, get transaction exception: ".$e->getMessage());
            return;
        }

        // TODO make it into separate class cause same thing is #validate_transaction() function
        echo "<pre>".print_r($tx_info)."</pre><br />";
        $btc_amount =           $tx_info["amount"] ;
        $confirms = 	        $tx_info["confirmations"] ;
        $account_name = 		$tx_info["details"][0]["account"] ;
        $to_address =           $tx_info["details"][0]["address"]; // address where transaction was sent to. from address may be multiple inputs which means many addresses
        $address_from = 		"" ; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
        $time = 				$tx_info["time"] ;
        $time_received = 		$tx_info["timereceived"];
        $category = 			$tx_info["details"][0]["category"];
        $block_hash = 		    isset($tx_info["blockhash"]) ? $tx_info["blockhash"] : null;
        $block_index = 		    isset($tx_info["blockindex"]) ? $tx_info["blockindex"] : null;
        $block_time = 		    isset($tx_info["blocktime"]) ? $tx_info["blocktime"] : null;

        $new = "Transaction hash: ".$tx_id
            ."\n amount: ".$tx_info['details'][0]["amount"]
            ."\n confirmations: ".$tx_info["confirmations"]
            ."\n blockhash: ".$block_hash
            ."\n blockindex: ".$block_index
            ."\n blocktime: ".$block_time
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

	    /******************* START of checking if its outgoing transaction *******************/
	    if ($btc_amount < 0):
	        log_message('info', "Sent out $btc_amount bitcoins to $to_address, tx_id $tx_id and log_id: ".$this->log_id);

		    $data = array(
			    'tx_id' => $tx_id,
				'crypto_amount' => bcmul($btc_amount, SATOSHIS_FRACTION),
				'crypto_type' => $this->crypto_type,
				'address_to' => $to_address,
			    'confirmations' => $confirms,
				'log_id' => $this->log_id
		    );

		    $this->load->model('Payout_history_model', '', TRUE);
		    $this->Payout_history_model->insert_new_transaction($data);
		    $response = null;
		    if (RETURN_OUTPUTTYPE == "json")
		    {
			    $response = json_encode(array(
				    'sent_out_amount' => $btc_amount, 'address' => $to_address, 'txid' => $tx_id, 'log_id' => $this->log_id));
			    $this->output
				    ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
				    ->set_output($response);
		    }
		    else
		    {
			    $response = "sent_out_amount:$btc_amount|address:$to_address|txid:$tx_id|log_id:".$this->log_id;
			    echo $response;
		    }

		    // do the response to server from either 0 confirmation or 1 confirmation
		    $this->update_log_response_msg($this->log_id, $response);
		    return; // step out from callback
	    endif;
	    /******************* END of checking if its outgoing transaction *******************/


        $this->load->model('Transaction_model', '', TRUE);

        // whether new transaction or notify was fired on 1st confirmation
        $transaction_model  = $this->Transaction_model->get_transaction_by_tx_id($tx_id);
        $satoshi_amount     = bcmul($btc_amount, SATOSHIS_FRACTION);

        log_message('info', "Address $to_address, amount (BTC): $btc_amount, amount (satoshi): $satoshi_amount, confirms: $confirms received transaction id $tx_id");

        $HOST_NAME = gethostname();

        $this->db->trans_start();

        /******************* START processing the invoicing callback **************/
        $invoice_address_model = $this->Address_model->get_invoice_address($to_address);
        if ($invoice_address_model) {

            log_message('info', 'Processing invoicing address '.$to_address.', destination address: '.$invoice_address_model->destination_address.
                ', label: '.$invoice_address_model->label.', amount satoshi: '.$invoice_address_model->received_amount.
                ', callback url: '.$invoice_address_model->callback_url.', forward: '.$invoice_address_model->forward);

            $forward_tx_id = 0; // needed for callback. stays 0 when forwarding is not chosen

            if (!$transaction_model)
            {   // first callback, because no transaction initially found in db
                $transaction_model_id = $this->Transaction_model->insert_new_transaction_from_callback(
                    $tx_id, 0, TX_RECEIVE_INVOICING, $satoshi_amount, $this->crypto_type,
                    $to_address, $address_from, $confirms, $block_hash, $block_index,
                    $block_time, $time, $time_received, $category, $account_name, $satoshi_amount, $this->log_id
                );
                log_message('info', 'Inserted new invoicing transaction');

                $total_received = bcadd($invoice_address_model->received_amount, $satoshi_amount);
                $this->Address_model->update_invoice_address($invoice_address_model->address, $total_received, 1); // update amount and mark as received

                $transaction_model = $this->Transaction_model->get_transaction_by_id($transaction_model_id);

                // check if needs to be forwarded
                if ($invoice_address_model->forward == 1)
                {
                    $bitcoin_amount = bcdiv($satoshi_amount, SATOSHIS_FRACTION, 8); // division
                    log_message('info', 'Starting to forward '.$satoshi_amount.' satoshis which is '.$bitcoin_amount.' bitcoins');
                    try
                    {
                        $forward_tx_id = $this->jsonrpcclient->sendtoaddress( $invoice_address_model->destination_address , (float)$bitcoin_amount);
                        if ($forward_tx_id)
                        {
                            $this->Transaction_model->insert_new_transaction($forward_tx_id, 0, TX_SEND, $satoshi_amount, $this->crypto_type, $to_address, '', 'invoice forwarding', $this->log_id);
                            log_message('info', 'Forwarded '.$bitcoin_amount.' bitcoins to '.$to_address);
                        } // TODO fucked when sendtoaddress throws exception, should send to server still the response.
                    } catch (Exception $e) {
                        $this->log_exception_response("#callback, send to address exception: ".$e->getMessage());
                        $this->db->trans_complete();
                        return;
                    }
                }
                else
                {
                    log_message('info', 'No forwarding');
                }
            }
            else
            {
                /* bitcoind sent 2nd callback for the transaction which is 1st confirmation */
                log_message('info', 'Updating confirmations to '.$confirms.' for transaction id: '.$transaction_model->id);
                $this->Transaction_model->update_tx_confirmations($transaction_model->id, $confirms, $block_hash, $block_index, $block_index);
            }

            $full_callback_url = $invoice_address_model->callback_url.'?value='.$satoshi_amount.'&input_address='.$invoice_address_model->address.'&confirms='.$confirms.'&transaction_hash='.$forward_tx_id.'&input_transaction_hash='.$tx_id.'&destination_address='.$invoice_address_model->destination_address.'&host='.$HOST_NAME.'&type='.TX_INVOICE;
            $full_callback_url_with_secret = $full_callback_url.'&secret='.$this->config->item('app_secret'); // don't include secret in log
            log_message('info', 'Sending callback to: '.$full_callback_url);
            $app_response = file_get_contents($full_callback_url_with_secret); // TODO wrap in exception - means the host did not respond
            log_message('info', 'Received response from server: '.$app_response);
            $callback_status = null;
            if($app_response == "*ok*") {
                $callback_status = 1;
            }

            //if we get back an *ok* from the script then update the transactions status
            $this->Transaction_model->update_tx_on_app_callback($transaction_model->id, $app_response, $full_callback_url, $callback_status);

            log_message('info', 'Updated transaction id '.$transaction_model->id.' with response: '.$app_response.', callback status: '.$callback_status);

            $response = null;
            if (RETURN_OUTPUTTYPE == "json")
            {
                $response = json_encode(array(
                    'confirmations' => $confirms, 'address' => $to_address, 'amount' => $btc_amount, 'txid' => $tx_id, 'callback_url' => $full_callback_url, 'response' => $app_response ));
                $this->output
                    ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                    ->set_output($response);
            }
            else
            {
                $response = $app_response;
                echo $response;
            }

            // do the response to server from either 0 confirmation or 1 confirmation
            $this->update_log_response_msg($this->log_id, $response);
            $this->db->trans_complete();
            return; // step out from callback
        }
        /******************* END processing the invoicing callback **************/

        // at this point its not the invoicing address, lookup address in address table

        log_message('info', 'Getting user\'s address');
        $address_model = $this->Address_model->get_address($to_address);

        /* It is incoming transaction, because it is sent to some of the inner adresses */
        if ($address_model) {
            $this->user = $this->User_model->get_user_balance($address_model->user_id);
            if (!$transaction_model) { // first callback, because no transaction initially found in db

                log_message('info', 'Received address '.$address_model->address.' label: '.$address_model->label.
                    ', user guid: '.$this->user->guid.', email: '.$this->user->email);

                $new_address_balance = bcadd($address_model->balance, $satoshi_amount);

                // insert new transaction
                $transaction_model_id = $this->Transaction_model->insert_new_transaction_from_callback(
                    $tx_id, $address_model->user_id, TX_RECEIVE, $satoshi_amount, $this->crypto_type,
                    $to_address, $address_from, $confirms, $block_hash, $block_index,
                    $block_time, $time, $time_received, $category, $account_name, $new_address_balance, $this->log_id
                );
                log_message('info', 'Inserted new transaction to db. Tx id: '.$tx_id.', user id: '.$address_model->user_id.', satoshi amount: '.
                    $satoshi_amount.', address new balance: '.$new_address_balance);

                // update address total received
                $address_total_received = bcadd($address_model->total_received, $satoshi_amount); // TODO set final balance to address, but how?
                $this->Address_model->update_total_received_crypto($address_model, $address_total_received);
                log_message('info', 'Updated address with new total received: '.$address_total_received.', previous balance: '.$address_model->total_received.', added amount: '.$satoshi_amount);

                // update API user balance
                $new_user_balance   = bcadd($this->user->balance, $satoshi_amount);
                $total_received     = bcadd($this->user->total_received, $satoshi_amount);
                $this->load->model('Balance_model', '', TRUE); // load balance model to update user's balance
                $this->Balance_model->update_user_balance($new_user_balance, $this->user->id, $total_received);
                log_message('info', 'Updated user balance: '.$new_user_balance.', previous balance: '.$this->user->balance.', added amount: '.$satoshi_amount);

                // get the newly inserted model for updating the info with response from external server
                $transaction_model = $this->Transaction_model->get_transaction_by_id($transaction_model_id);
            }
            else
            {
                /* bitcoind sent 2nd callback for the transaction which is 1st confirmation */
                $this->Transaction_model->update_tx_confirmations($transaction_model->id, $confirms, $block_hash, $block_index, $block_index);
            }
        } else
        {
            // either its change address or somebody sent to some address that is not registered in db!
            // say some shit that address is unknown, and maybe mail too!
            $this->sendEmail('RECEIVED '.$btc_amount.' BITCOINS TO UNKNOWN ADDRESS', 'Address that received it: '.$to_address);
            $this->db->trans_complete();
            return;
        }

        // now it is time to fire to the API user callback URL which is his app that is using this server's API
        // mind the secret here, that app has to verify that it is coming from the API server not somebody else
        $full_callback_url = $this->user->callbackurl.'?input_transaction_hash='.$tx_id.'&input_address='.$to_address.'&value='.$satoshi_amount.'&confirms='.$confirms.'&host='.$HOST_NAME.'&type='.TX_API_USER;

        log_message('info', 'Sending callback to: '.$full_callback_url);

        $full_callback_url_with_secret = $full_callback_url."&secret=".$this->user->secret; // don't include secret in a log
        $app_response = file_get_contents($full_callback_url_with_secret); // TODO wrap in exception - means the host did not respond
        log_message('info', 'Received response from server: '.$app_response);

        $callback_status = null;
        if($app_response == "*ok*") {
            $callback_status = 1;
        }

        //if we get back an *ok* from the script then update the transactions status
        $this->Transaction_model->update_tx_on_app_callback($transaction_model->id, $app_response, $full_callback_url, $callback_status);

        log_message('info', 'Updated transaction id '.$transaction_model->id.' with response: '.$app_response.', callback status: '.$callback_status);

        $response = null;
        if (RETURN_OUTPUTTYPE == "json") {
            $response = json_encode(array(
                'confirmations' => $confirms, 'address' => $to_address, 'amount' => $btc_amount, 'txid' => $tx_id, 'callback_url' => $full_callback_url, 'response' => $app_response ));
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
        } else {
            $response = $app_response;
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
        $this->db->trans_complete();
    }

    /* example.com/api/receive?method=create&address=xxx&callback=https://callbackurl.com&label=xxx&forward=1
    // if forward = 0, then dont forward to address. label needed just in this case, when forward 0 and it has a role of note
    */
    public function receive() {

        log_message('info', '====== RECEIVE STARTED ======');

        /* the url structure is different, so different segments of URI */
        $method =           $this->uri->segment(2); // receive
        $ipaddress =        $this->input->ip_address();
        $full_query_str =   $this->uri->uri_string().'?'.$this->input->server('QUERY_STRING');

        if ($this->input->get('cryptotype')) {
            $this->crypto_type = $this->input->get('cryptotype');
        }

        $agent      = $this->agent->agent_string();
        $referrer   = $this->agent->referrer();

        $this->log_id = $this->log_call($method, '', $ipaddress, $full_query_str, $agent, $referrer, ''); // log it
        log_message('info', '====== RECEIVE LOGGED A CALL ======');

        $method = $this->input->get('method');
        if ($method != 'create') {
            $response = json_encode(array('error' => NO_CREATE_METHOD_ON_INVOICE));
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
            $this->update_log_response_msg($this->log_id, $response);
            return;
        }

        $isPrivate = $this->config->item('private_invoicing');
        if ($isPrivate) {
            $input_secret = $this->input->get('secret');
            $server_secret = $this->config->item('callback_secret');
            if ($input_secret != $server_secret) {
                $this->log_exception_response("#receive, secret mismatch: ".NO_SECRET_FOR_CALLBACK);
                return;
            }
        }

        log_message('info', '====== RECEIVE METHOD AND PRIVATE INVOICING VALIDATION CHECKED ======');

        $receiving_address  = $this->input->get('address');
        $receiving_address  = isset($receiving_address) == true ? $receiving_address : '';
        $callback_url       = $this->input->get('callback');
        $label              = $this->input->get('label');
        $forward            = $this->input->get('forward');
        $invoice_amount     = $this->input->get('amount'); // BTC
        if (!empty($invoice_amount))
        {
            $invoice_amount = bcmul($invoice_amount, SATOSHIS_FRACTION);
        } else
        {
            $invoice_amount = 0;
        }

        if (empty($receiving_address)) {
            $forward = 0;
        }

        if (!empty($receiving_address)) {
            $isTestnet = $this->config->item('is_testnet');
            $this->load->library('bitcoinaddressvalidator');
            $isValidAddress = $this->bitcoinaddressvalidator->isValid($receiving_address, $isTestnet ? BitcoinAddressValidator::TESTNET : null);
            if (!$isValidAddress) {
                $this->log_exception_response("#receive, non valid address: ".INVALID_ADDRESS);
                return;
            }
        }

        $input_address = $this->jsonrpcclient->getnewaddress('invoice'); // the argument is the account name in bitcoind, could possibly use domain from the callback url

        $this->Address_model->save_invoice_address($input_address, $receiving_address, $invoice_amount, $label, $callback_url, $forward, $this->log_id); // save newly created address in invoice_adddresses table

        log_message('info', '====== RECEIVE NEW ADDRESS GENERATED AND SAVED which is receiving address: '.$receiving_address.', input address'.$input_address.' ======');

        $response = array(
            'fee_percent' => 0,
            'forward' => $forward,
            'destination' => $receiving_address,
            'input_address' => $input_address,
            'callback_url' => $callback_url,
        );

        if (RETURN_OUTPUTTYPE == "json") {
            $response = json_encode($response);
            $this->output
                ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
                ->set_output($response);
            log_message('info', '====== RECEIVE RETURNED RESPONSE ======');
        } else {
            echo $response;
        }
        $this->update_log_response_msg($this->log_id, $response);
        log_message('info', '====== RECEIVE FINALLY UPDATED LOG WITH RESPONSE ======');
    }

    public function test()
    {
        log_message('error', 'Test writing to log file');
    }

    public function sendEmail($subjct, $message) {
        $this->load->library('email'); // set mail server parameters in config/email.php
        $admin_email = $this->config->item('admin_email'); // due to shitty codeigniter, this is in config.php
        $this->email->from('easybitz+api@easybitz.com');
        $this->email->to($admin_email);
        $this->email->subject($subjct);
        $this->email->message($message);
        $this->email->send();
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

        if ($this->input->get('debug') or $this->jsonrpc_debug == true) {
            echo "GUID: ".$guid."\n";
        }

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

    private function log_exception_response($message)
    {
        $message = 'log id: '.$this->log_id.'|'.$message;
        log_message('error', $message);
        $response = json_encode(array('error' => $message));
        $this->output
            ->set_content_type(DEBUG_API == TRUE ? 'text/html' : 'application/json')
            ->set_output($response);
        $this->update_log_response_msg($this->log_id, $response);
        if ($this->config->item('enable_email_logging'))
        {
            $this->sendEmail("API ERROR", $message);
        }
    }

	private function satoshiToBtc($satoshis)
	{
		return (float)bcdiv($satoshis, SATOSHIS_FRACTION, 8);
	}
}