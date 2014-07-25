<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

	public function index() {
        // parent controller method that should be called on restricted pages
        // inside redirects to front page
        $this->data['message'] = $this->session->flashdata('message');
        if (!$this->is_authenticated()) {
            return;
        }

        $data['title'] = 'Welcome';
		$this->load->view('welcome', $data);
	}

    public function process_order() {
        $secret = $this->input->get('secret'); // this I entered into the blockchain wallet form

        if (!$secret) {
            // TODO send bad response
            return;
        }

        $tx_hash  = $this->input->get('transaction_hash');
        $address  = $this->input->get('input_address'); // The bitcoin address that received the transaction
        $value    = $this->input->get('value'); // value in satoshi TODO check value of satoshi is not below zero
        $confirms = $this->input->get('confirms');

        $this->load->model('Wallet_address_model', '', TRUE);
        $wallet_address_model = $this->Wallet_address_model->get_user_by_address($address);

        if (!$wallet_address_model) {
            // TODO no user found for the address, wtf ?
        }


        $this->load->library('user_agent');
        $data = array(
            'url' => $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'],
            'url_referrer' => $this->agent->referrer(),
            'value_satoshi' => $value,
            'address' => $address,
            'tx_hash' => $tx_hash,
            'user_id' => $wallet_address_model->user_id,
            'ip_address' => $this->input->ip_address(),
            'ip_address2' => $_SERVER['HTTP_X_FORWARDED_FOR']

        );
        $this->load->model('Callback_model', '', TRUE);
        $new_callback_id = $this->Callback_model->insert_callback($data);

        // TODO validate transaction hash
        // TODO validate address that exists in bitcoind

        $this->load->model('Transactions_model', '', TRUE);
        $transaction_model = $this->Transactions_model->get_transaction_by_tx_hash($tx_hash);
        if ($transaction_model) {
            // TODO update confirmations for that transaction
        } else {

        }

        $this->load->model('User_model', '', TRUE);
        $user_model = $this->User_model->get_user_by_wallet_address($address);
        if (!$user_model) {
            // TODO lost address, no such user for such address
        }

        $query = "INSERT INTO ".TBL_TRANSACTIONS.
            " ( status,	fiat_amt, 		credit,		crypto_amt, balance_prev,	balance_curr,	type,	fiat_type, 			fiat_rate, 	user_id, 	user_id_sentto, cryptotype,			walletaddress_sentto,	walletaddress_from,	 wallet_location,		hash_transaction,	receiver_name, 		receiver_email, receiver_phone,		datetime_created,	datetime ) VALUES ".
            " ( 1, 		$intTotalFiat, 	$crypto_amt,$crypto_amt,'$balance_prev','$balance_curr','bcget','$strCurrencyCode', $intRate, 	$intUserID,	$intUserID,		'$strCryptoCode', 	'$input_address',		'',					'$strWalletLocation',	'$transaction_hash','$strNameReceiver',	'$strEmail',	'$strPhone',		$intTime,			NOW() ) " ;

        if($intNewCallBackID){ $strSQLTransUpdate= " , callback_id= $intNewCallBackID " ;}
        $query="UPDATE " .TBL_TRANSACTIONS. " SET ".
            " status=1 ".$strSQLTransUpdate.
            " WHERE transaction_id=".$intNewOrderID ;

        $query="UPDATE " .TBL_ORDERS_CALLBACKS. " SET ".
            " errorcode='{$strError} {$strDebugSqlTxt}' ".
            " WHERE callback_id=".$intNewCallBackID ;
        // TODO send *ok* to API
    }

}
