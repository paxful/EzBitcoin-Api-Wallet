<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Merchant extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $jsonrpc_conn_string = $this->config->item('jsonrpc_connectionstring');
        $jsonrpc_debug = $this->config->item('bitcoind_debug');
        $this->load->library('jsonrpcclient', array('url' => $jsonrpc_conn_string, 'debug' => $jsonrpc_debug));
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

    public function validate_transaction($txid = '') {
        //test //$strTransaction="10c724bdfe52f95b482949101cc1bb3657c9f92d7f61d469a309eacbb6782d24";

        if(!$txid){
            // TODO: log in db
            echo "No transaction ID";
            return;
        }

        try{
            $txinfo = $this->jsonrpcclient->gettransaction($txid);

            //if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
            //Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and
            //use the getrawtransaction call to request information about any transaction
            //$strRawHex = $this->jsonrpcclient->getrawtransaction($strTransaction);
            //$objJSON = $this->jsonrpcclient->decoderawtransaction($strRawHex);

            //bind values to variables
            $transactionID = 	    $txinfo["txid"] ;

            $new = "Transaction hash: ".$transactionID[1]
                ."\n amount: ".$txinfo["amount"]
                ."\n confirmations: ".$txinfo["confirmations"]
                ."\n blockhash: ".$txinfo["blockhash"]
                ."\n blockindex: ".$txinfo["blockindex"]
                ."\n blocktime: ".$txinfo["blocktime"]
                ."\n txid: ".$txinfo["txid"]
                ."\n time: ".$txinfo["time"]
                ."\n timereceived: ".$txinfo["timereceived"]
                ."\n account: ".$txinfo["details"][0]["account"]
                ."\n address: ".$txinfo["details"][0]["address"]
                ."\n category: ".$txinfo["details"][0]["category"]
                ."\n amount: ".$txinfo["details"][0]["amount"]
                ."\n"
            ;
            if($this->config->item('merchant_debug')) {
                echo nl2br($new) . "\n";
            }
            echo "valid";

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function validate_address($address = '') {
        if(!$address){
            // TODO: log in db
            echo "No address specified";
            return;
        }

        try{
            $address_valid = $this->jsonrpcclient->validateaddress($address) ;
        } catch(Exception $e){
            echo $e->getMessage() ;
            // TODO: report failed address validation
            return;
        }

        $intIsValid = $address_valid["isvalid"];
        if (!$intIsValid) {
            echo "Invalid address specified";
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
            echo json_encode(array( 'isvalid' => $intIsValid, 'address' => "$address", 'ismine' => "$intIsMine" ));
        } else {
            echo "$intIsValid|$address|$intIsMine" ; //die;
        }
    }

    /**
     * create new address in blockchain
     */
    public function new_address() {
        // TODO add labels specific to the app

        try {
            $new_wallet_addres = $this->jsonrpcclient->getnewaddress() ;
            // TODO add address to db
        } catch (Exception $e) {
            echo $e->getMessage();
            // TODO report admin on failure of address creation
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

        if (empty($address) or empty($amount)) {
            echo "Address or amount not specified";
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
}