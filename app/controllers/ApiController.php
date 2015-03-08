<?php

use Helpers\DataParserInterface;
use Helpers\JsonRPCClientInterface;

class ApiController extends BaseController {

	protected $crypto_type_id = 1;
	protected $user;
	protected $bitcoin_core;
	protected $dataParser;

	public function __construct(JsonRPCClientInterface $bitcoin_core_client, DataParserInterface $dataParser)
	{
		$this->bitcoin_core = $bitcoin_core_client;
		$this->dataParser   = $dataParser;
	}

	public function getIndex() {
		return View::make('hello');
	}

	/**
	 * example.com/api/<guid>/balance?password=xxx&debug=1
	 */
	public function balance($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		if ( Input::get('cryptotype') ) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		// at this point '$this->user' is already set

		$user_balance = Balance::getBalance($this->user->id, $this->crypto_type_id);


		if ( count($user_balance) )
		{
			Log::info( 'Queried balance for ' . $this->user->email. ': ' . $user_balance->balance . ' satoshis. Crypto type id: ' . $this->crypto_type_id );
			$response = [ 'balance' => $user_balance->balance, 'crypto_type' => $this->crypto_type_id ];
		}
		else
		{
			Log::warn('Balance not found for user ' . $this->user->email . 'for crypto type ' . $this->crypto_type_id);
			$response = [ 'error' => 'Balance not found for crypto type ' . $this->crypto_type_id ];
		}
		return Response::json($response);
	}

	public function addressBalance($guid) {
		// not implemented
	}

	/**
	 * example.com/api/<guid>/validate-transaction?txid=xxx&&password=zzz&debug=1
	 */
	public function validateTransaction($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		$tx_id = Input::get( 'txid' );
		if ( !$tx_id )
		{
			Log::error('#validateTransaction: ' . NO_TX_ID );
			return Response::json( ['error' => '#validateTransaction: ' . NO_TX_ID] );
		}

		try
		{
//			$bitcoind_client = new \Helpers\JsonRPCClient($this->user->rpc_connection);
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_info = $this->bitcoin_core->gettransaction( $tx_id );

			//if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
			//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and
			//use the getrawtransaction call to request information about any transaction
			//$strRawHex = $this->jsonrpcclient->getrawtransaction($strTransaction);
			//$objJSON = $this->jsonrpcclient->decoderawtransaction($strRawHex);

			//bind values to variables
			$transaction_id = $tx_info["txid"];

			if ( ( Input::get( 'debug' ) or API_DEBUG == true ) ) {
			$new = "Transaction hash: " . $transaction_id[1]
			       . "\n amount: " . $tx_info["amount"]
			       . "\n confirmations: " . $tx_info["confirmations"]
			       . "\n blockhash: " . $tx_info["blockhash"]
			       . "\n blockindex: " . $tx_info["blockindex"]
			       . "\n blocktime: " . $tx_info["blocktime"]
			       . "\n txid: " . $tx_info["txid"]
			       . "\n time: " . $tx_info["time"]
			       . "\n timereceived: " . $tx_info["timereceived"]
			       . "\n account: " . $tx_info["details"][0]["account"]
			       . "\n address: " . $tx_info["details"][0]["address"]
			       . "\n category: " . $tx_info["details"][0]["category"]
			       . "\n amount: " . $tx_info["details"][0]["amount"]
			       . "\n";
				echo nl2br( $new ) . "\n";
			}
			return Response::json( ['is_valid' => true, 'tx_id' => $tx_id] );
		}
		catch ( Exception $e )
		{
			Log::error( '#validateTransaction: get transaction exception: ' . $e->getMessage() );
			return Response::json( ['error' => '#validateTransaction: get transaction exception: ' . $e->getMessage()] );
		}
	}

	/**
	 * example.com/api/<guid>/validate_address?address=xxx&password=zzz&debug=1
	 */
	public function validateAddress($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		$address = Input::get( 'address' );
		if ( ! $address ) {
			Log::error( "#validateAddress: address ($address) not provided for user " . $this->user->email);
			return Response::json( ['error' => NO_ADDRESS] );
		}

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$address_valid = $this->bitcoin_core->validateaddress( $address );
		} catch ( Exception $e ) {
			Log::error( "#validateAddress: validate address ($address) exception: " . $e->getMessage() );
			return Response::json( ['error' => '#validateAddress: validate address exception: ' . $e->getMessage()] );
		}

		$is_valid = $address_valid["isvalid"];
		if ( ! $is_valid ) {
			Log::error( "#validateAddress: address $address");
			return Response::json( ['error' => '#validateAddress: ' . INVALID_ADDRESS ] );
		}

		$address = $address_valid["address"];
		$is_mine = false;

		$user_address = Address::getAddressForUser( $address, $this->user->id );

		if ( count($user_address) ) {
			$is_mine = true;
		}

		return Response::json( ['isvalid' => $is_valid, 'address' => $address, 'ismine' => $is_mine] );
	}

	/**
	 * example.com/api/<guid>/new_address?label=xxx&password=zzz&debug=1
	 */
	public function newAddress($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		if ( Input::get('cryptotype') ) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		try
		{
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$new_wallet_address = $this->bitcoin_core->getnewaddress();

			$label = Input::get( 'label' );
			if ( ! $label ) {
				$label = null;
			}

			Address::insertNewAddress([
					'user_id'        => $this->user->id,
					'address'        => $new_wallet_address,
					'label'          => $label,
					'crypto_type_id' => $this->crypto_type_id
				]);
		} catch ( Exception $e ) {
			Log::error( '#newAddress: get new address exception: ' . $e->getMessage() );
			return Response::json( ['error' => '#newAddress: get new address exception: ' . $e->getMessage()] );
		}

		Log::info( "Created new address $new_wallet_address for user: " . $this->user->email );

		return Response::json( ['address' => $new_wallet_address, 'label' => $label] );
	}

	public function txConfirmations($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		$tx_id = Input::get( 'txid' );
		if ( ! $tx_id ) {
			Log::error( '#getTransactionConfirmations: ' . NO_TX_ID);
			return Response::json( ['error' => '#getTransactionConfirmations: ' . NO_TX_ID] );
		}

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_info = $this->bitcoin_core->gettransaction( $tx_id );

			//bind values to variables
			$transaction_id = $tx_info["txid"];

			if ( ( Input::get( 'debug' ) or API_DEBUG == true ) ) {
			$new = "Transaction hash: " . $transaction_id[1]
			       . "\n amount: " . $tx_info["amount"]
			       . "\n confirmations: " . $tx_info["confirmations"]
			       . "\n blockhash: " . $tx_info["blockhash"]
			       . "\n blockindex: " . $tx_info["blockindex"]
			       . "\n blocktime: " . $tx_info["blocktime"]
			       . "\n txid: " . $tx_info["txid"]
			       . "\n time: " . $tx_info["time"]
			       . "\n timereceived: " . $tx_info["timereceived"]
			       . "\n account: " . $tx_info["details"][0]["account"]
			       . "\n address: " . $tx_info["details"][0]["address"]
			       . "\n category: " . $tx_info["details"][0]["category"]
			       . "\n amount: " . $tx_info["details"][0]["amount"]
			       . "\n";
				echo nl2br( $new ) . "\n";
			}

			return Response::json( ['confirmations' => $tx_info["confirmations"]] );

		} catch ( Exception $e ) {
			Log::error( '#getTransactionConfirmations: get transaction exception: ' . $e->getMessage() );
			return Response::json( ['error' => '#getTransactionConfirmations: ' . $e->getMessage()] );
		}
	}

	/**
	 * example.com/api/<guid>/payment?to=xxx&amount=satoshis&note=yyy&password=zzz&debug=1
	 */
	public function payment($guid)
	{
		if ( ! $this->attemptAuth() ) {
			return Response::json( ['error' => AUTHENTICATION_FAIL] );
		}

		$to_address = Input::get( 'to' );
		$amount     = Input::get( 'amount' );
		$note       = Input::get( 'note' );

		if ( ! $note ) {
			$note = '';
		}

		Log::info( "=== Starting payment to $to_address, note: $note, amount: " . self::satoshiToBtc( $amount ) . ' ===' );

		if ( empty( $to_address ) or empty( $amount ) ) {
			Log::error( '#payment: ' . ADDRESS_AMOUNT_NOT_SPECIFIED );
			return Response::json( ['error' => '#payment: ' . ADDRESS_AMOUNT_NOT_SPECIFIED ] );
		}

		$isValidAddress = BitcoinHelper::isValid($to_address);
		if ( ! $isValidAddress ) {
			Log::error( '#payment: ' . INVALID_ADDRESS );
			return Response::json( ['error' => '#payment: ' . INVALID_ADDRESS] );
		}

		$user_balance = Balance::getBalance( $this->user->id, $this->crypto_type_id );

		Log::info( 'User initial balance: ' . self::satoshiToBtc( $user_balance->balance ) . ' bitcoins' );

		if ( $user_balance->balance < $amount ) {
			Log::error( '#payment: ' . NO_FUNDS );
			return Response::json( ['error' => '#payment: ' . NO_FUNDS] );
		}

		$new_balance = bcsub( $user_balance->balance, $amount );

		Log::info( 'User new balance: ' . self::satoshiToBtc( $new_balance ) . ' bitcoins' );

		DB::beginTransaction(); // begin DB transaction

		Balance::updateUserBalance($user_balance, $new_balance);

		$bitcoin_amount = self::satoshiToBtc( $amount ); // return float

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_id = $this->bitcoin_core->sendtoaddress( $to_address, $bitcoin_amount, $note );
			if ( $tx_id ) {
				// if it fails here on inserting new transaction, then this transaction will be rolled back - user balance not updated, but jsonrpcclient will send out.
				// think of a clever way on which step it failed and accordingly let know if balance was updated or not
				Transaction::insertNewTransaction([
					'tx_id' => $tx_id,
					'user_id' => $this->user->id,
					'transaction_type' => TX_SEND,
					'crypto_amount' => $amount,
					'crypto_type_id' => $this->crypto_type_id,
					'address_to' => $to_address,
					'note' => $note,
				]);
			}

		} catch ( Exception $e ) {
			DB::rollback();
			// TODO may insert to some new table of unsuccessful payments
			Log::error( "#payment: send to address exception: " . $e->getMessage() );
			return Response::json( ['error' => "#payment: send to address exception: " . $e->getMessage()] );
		}
		DB::commit();

		Log::info( "=== Sending to  $to_address, amount: " . self::satoshiToBtc( $amount ) . ' completed ===' );

		$message  = "Sent $bitcoin_amount, crypto type id: " . $this->crypto_type_id . " to $to_address";
		return Response::json( ['message' => $message, 'tx_hash' => $tx_id] );
	}

	/**
	 * Callback is initiated when:
	 * Receiving transaction gets into mempool
	 * Sending out transaction - has negative amount
	 * Transaction gets 1st confirmation
	 */
	public function callback()
	{
		Log::info('=== CALLBACK STARTED ===' );
		Log::info('User id: '.Input::get('userid').', tx hash: '.Input::get('txid'));

		/* the url structure is different, so different segments of URI */
		if ( Input::get( 'cryptotype' ) ) {
			$this->crypto_type_id = Input::get( 'cryptotype' );
		}

		$server_callback_secret = Config::get( 'bitcoin.callback_secret' );

		if ( $server_callback_secret != Input::get( 'secret' )) {
			Log::error( '#callback: ' . SECRET_MISMATCH . ', full URL:  ' . Request::fullUrl() );
			return Response::json( ['#callback: ' . SECRET_MISMATCH] );
		}
		/*--------------------------------------------*/

		/*sends callback on receive notification
		gets a transaction hash id
		calls bitcoind via RPC to get transaction info
		calls a web url specified in the user account
		called from /home/api/walletnotify.sh
		sudo curl http://127.0.0.1/api/callback/?txid=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&local=n00nez&loginname=ammm&password=PsQWsO4sDLwqTxxx&debug=1*/

		$tx_id = Input::get( 'txid' ); // check if not null
		if ( ! $tx_id ) {
			Log::error( "#callback, no tx id: " . NO_TX_ID );
			return Response::json( ['error' => NO_TX_ID] );
		}

		$user_id = Input::get('userid');
		if ( ! $user_id ) {
			// TODO daaaaamn
		}
		$this->user = User::find($user_id);

		$bitcoind_timestamp = Input::get( 'time' );

		// TODO if user is not set here. decide how to set user
		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);

		try {
			$tx_info = $this->bitcoin_core->gettransaction( $tx_id );
		} catch ( Exception $e ) {
			Log::error( '#callback: get transaction exception: ' . $e->getMessage() );
			return Response::json( ['error' => '#callback: get transaction exception: ' . $e->getMessage()] );
		}

		$btc_amount    = $tx_info['amount'];
		$confirms      = $tx_info['confirmations'];
		$account_name  = $tx_info['details'][0]['account'];
		$to_address    = $tx_info['details'][0]['address']; // address where transaction was sent to. from address may be multiple inputs which means many addresses
		$address_from  = ''; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
		$time          = $tx_info['time'];
		$time_received = $tx_info['timereceived'];
		$category      = $tx_info['details'][0]['category'];
		$block_hash    = isset( $tx_info['blockhash'] ) ? $tx_info['blockhash'] : null;
		$block_index   = isset( $tx_info['blockindex'] ) ? $tx_info['blockindex'] : null;
		$block_time    = isset( $tx_info['blocktime'] ) ? $tx_info['blocktime'] : null;

		if ( ( Input::get( 'debug' ) or API_DEBUG == true ) ) {
		$new = "Transaction hash: " . $tx_id
		       . "\n amount: " . $tx_info['details'][0]["amount"]
		       . "\n confirmations: " . $tx_info["confirmations"]
		       . "\n blockhash: " . $block_hash
		       . "\n blockindex: " . $block_index
		       . "\n blocktime: " . $block_time
		       . "\n txid: " . $tx_info["txid"]
		       . "\n time: " . $tx_info["time"]
		       . "\n timereceived: " . $tx_info["timereceived"]
		       . "\n account: " . $tx_info["details"][0]["account"]
		       . "\n address: " . $tx_info["details"][0]["address"]
		       . "\n category: " . $tx_info["details"][0]["category"]
		       . "\n amount: " . $tx_info["details"][0]["amount"];
			echo nl2br( $new ) . "\n";
		}

		Log::info( "Starting to process $btc_amount bitcoins, tx id: $tx_id with timestamp $bitcoind_timestamp" );

		/******************* START of checking if its outgoing transaction *******************/
		if ( $btc_amount < 0 )
		{
			$this->processOutgoingTransaction( $user_id, $btc_amount, $to_address, $tx_id, $confirms );
			return Response::json( ['sent_out_amount' => $btc_amount, 'address' => $to_address, 'txid' => $tx_id, 'crypto_type' => $this->crypto_type_id,] );
		}
		/******************* END of checking if its outgoing transaction *******************/

		// whether new transaction or notify was fired on 1st confirmation
		$transaction_model = Transaction::getTransactionByTxId( $tx_id );
		$satoshi_amount    = bcmul( $btc_amount, SATOSHIS_FRACTION );

		Log::info( "Address $to_address, amount (BTC): $btc_amount, amount (satoshi): $satoshi_amount, confirms: $confirms received transaction id $tx_id" );

		$HOST_NAME = gethostname();

		DB::beginTransaction();

		/******************* START processing the invoicing callback **************/
		$invoice_address_model = InvoiceAddress::getAddress( $to_address );
		if ( count($invoice_address_model) ) {

			Log::info( 'Processing invoicing address ' . $to_address . ', destination address: ' . $invoice_address_model->destination_address .
			                     ', label: ' . $invoice_address_model->label . ', amount satoshi: ' . $invoice_address_model->received_amount .
			                     ', callback url: ' . $invoice_address_model->callback_url . ', forward: ' . $invoice_address_model->forward );

			$forward_tx_id = 0; // needed for callback. stays 0 when forwarding is not chosen

			if ( !count($transaction_model) )
			{
				$initialUserBalance = Balance::getBalance($this->user->id, $this->crypto_type_id);
				// first callback, because no transaction initially found in db
				$invoice_tx_data = [
					'tx_id'             => $tx_id,
					'user_id'           => $this->user->id,
					'transaction_type'  => TX_RECEIVE_INVOICING,
					'crypto_amount'     => $satoshi_amount,
					'crypto_type_id'    => $this->crypto_type_id,
					'address_to'        => $to_address,
					'address_from'      => $address_from,
					'confirmations'     => $confirms,
					'block_hash'        => $block_hash,
					'block_index'       => $block_index,
					'block_time'        => $block_time,
					'tx_time'           => $time,
					'tx_timereceived'   => $time_received,
					'tx_category'       => $category,
					'address_account'   => $account_name,
					'balance'           => bcadd($initialUserBalance, $satoshi_amount), // new API user balance
					'previous_balance'  => $initialUserBalance->balance, // API user balance before that transaction, because user balance has not been updated yet
					'bitcoind_balance'  => bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION), // bitcoind balance on received! that means this transaction is not included, because it has 0 conf
				];
				$transaction_model = Transaction::insertNewTransaction($invoice_tx_data);

				$total_received = bcadd( $invoice_address_model->received_amount, $satoshi_amount );
				InvoiceAddress::updateReceived($invoice_address_model, $total_received);// update amount and mark as received

				// check if needs to be forwarded
				if ( $invoice_address_model->forward == 1 )
				{
					$bitcoin_amount = bcdiv( $satoshi_amount, SATOSHIS_FRACTION, 8 ); // division
					Log::info( 'Starting to forward ' . $satoshi_amount . ' satoshis which is ' . $bitcoin_amount . ' bitcoins' );
					try
					{
						$forward_data = [
							'user_id' => $this->user->id,
							'transaction_type' => TX_SEND,
							'crypto_amount' => $satoshi_amount,
							'crypto_type_id' => $this->crypto_type_id,
							'address_to' => $to_address,
							'note' => 'invoice forwarding',
						];
						$forward_tx_id = $this->bitcoin_core->sendtoaddress( $invoice_address_model->destination_address, (float) $bitcoin_amount );
						if ( $forward_tx_id )
						{
							$forward_data['tx_id'] = $forward_tx_id;
							Transaction::insertNewTransaction($forward_data);
							Log::info( 'Forwarded ' . $bitcoin_amount . ' bitcoins to ' . $to_address );
						} // TODO fucked when sendtoaddress throws exception, should send to server still the response.
					}
					catch ( Exception $e )
					{
						Log::error( "#callback: send to address exception: " . $e->getMessage() );

						// add to transaction entry anyway, with failed forwarding and send warning out email !
						$forward_data['note'] = 'failed invoice forwarding';
						Transaction::insertNewTransaction($forward_data);
						DB::commit();

						MailHelper::sendEmailPlain([
							'email'     => Config::get('mail.admin_email'),
							'subject'   => 'FAILED INVOICE FORWARDING!',
							'text'      => 'Failed to forward to ' . $to_address . ', ' . $satoshi_amount . ' satoshis, tx hash: ' . $tx_id,
						]);

						return Response::json( ['invoice forwarding failed'] ); // TODO some proper warning
					}
				}
			}
			else
			{
				/* bitcoind sent 2nd callback for the transaction which is 1st confirmation */
				Log::info( 'Updating confirmations to ' . $confirms . ' for transaction id: ' . $transaction_model->id . ' and transaction hash: ' . $tx_id);
				Transaction::updateTxConfirmation( $transaction_model, $confirms, $block_hash, $block_index, $block_index );
			}

			$queryString = http_build_query([
				'value'                     => $satoshi_amount,
				'input_address'             => $invoice_address_model->address,
				'confirmations'             => $confirms,
				'transaction_hash'          => $forward_tx_id,
				'input_transaction_hash'    => $tx_id,
				'destination_address'       => $invoice_address_model->destination_address,
				'host'                      => $HOST_NAME,
				'type'                      => TX_INVOICE
			]);
			$full_callback_url              = $invoice_address_model->callback_url . '?' . $queryString;
			$full_callback_url_with_secret  = $full_callback_url . '&secret=' . Config::get( 'bitcoin.app_secret' ); // don't include secret in log

			Log::info( 'Sending callback to: ' . $full_callback_url );

			$app_response = $this->dataParser->fetchUrl( $full_callback_url_with_secret ); // TODO wrap in exception - means the host did not respond

			Log::info( 'Received response from server: ' . $app_response );

			$callback_status = null;
			if ( $app_response == '*ok*' ) {
				$callback_status = 1;
			}

			//if we get back an *ok* from the script then update the transactions status
			Transaction::updateTxOnAppResponse( $transaction_model, $app_response, $full_callback_url, $callback_status );

			Log::info( 'Updated transaction id ' . $transaction_model->id . ' with response: ' . $app_response . ', callback status: ' . $callback_status );

			DB::commit();

			// do the response to server from either 0 confirmation or 1 confirmation
			$response = [
				'confirmations' => $confirms,
				'address'       => $to_address,
				'amount'        => $btc_amount,
				'txid'          => $tx_id,
				'callback_url'  => $full_callback_url,
				'response'      => $app_response,
			];
			return Response::json( $response );
		}
		/*************** END processing the invoicing callback **************/

		/*************************************************************************************/
		/* at this point its not the invoicing address, lookup address in address table
		/*************************************************************************************/

		Log::info( 'Getting user address' );

		$address_model = Address::getAddress( $to_address );

		/************* It is incoming transaction, because it is sent to some of the inner addresses *************/
		if ( $address_model )
		{
			$this->user = $address_model->user;

			// 0 conf, just hit mempool, not 1-st confirmation
			if ( !$transaction_model )
			{
				// first callback, because no transaction initially found in db
				Log::info( 'Received address ' . $address_model->address . ' label: ' . $address_model->label .
				                     ', user guid: ' . $this->user->guid . ', email: ' . $this->user->email );

				$new_address_balance = bcadd( $address_model->balance, $satoshi_amount );

				$data = [
					'tx_id'             => $tx_id,
					'user_id'           => $address_model->user_id,
					'transaction_type'  => TX_RECEIVE,
					'crypto_amount'     => $satoshi_amount,
					'crypto_type_id'    => $this->crypto_type_id,
					'address_to'        => $to_address,
					'address_from'      => $address_from,
					'confirmations'     => $confirms,
					'block_hash'        => $block_hash,
					'block_index'       => $block_index,
					'block_time'        => $block_time,
					'tx_time'           => $time,
					'tx_timereceived'   => $time_received,
					'tx_category'       => $category,
					'address_account'   => $account_name,
					'balance'           => $new_address_balance,
					'previous_balance'  => $address_model->balance,
					'bitcoind_balance'  => bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION), // bitcoind balance on received! that means this transaction is not included, because it has 0 conf
				];

				// insert new transaction
				$transaction_model = Transaction::insertNewTransaction( $data );

				Log::info( 'Inserted new transaction to db. Tx id: ' . $tx_id . ', user id: ' . $address_model->user_id . ', satoshi amount: ' .
				                     $satoshi_amount . ', address new balance: ' . $new_address_balance );

				// update address balance
				Address::updateBalance($address_model, $satoshi_amount);
				Log::info( 'Updated address ' . $address_model->address . ', added amount: ' . $satoshi_amount );

				/* update API user balance */
				$old_balance = $this->user->balances()->first()->balance;
				$new_balance = bcadd( $this->user->balance, $satoshi_amount );
				$total_received   = bcadd( $this->user->total_received, $satoshi_amount );
				$balance_model = Balance::getBalance($this->user->id, $this->crypto_type_id);

				Balance::updateUserBalance($balance_model, $new_balance, $total_received);
				Log::info( 'Updated user (' . $this->user->email . ') balance: ' . $new_balance . ', previous balance: ' . $old_balance . ', added amount: ' . $satoshi_amount );

			}
			else
			{
				/* bitcoind sent 2nd callback for the transaction which is 1st confirmation */
				Transaction::updateTxConfirmation( $transaction_model, $confirms, $block_hash, $block_index, $block_index );
			}
		}
		else
		{
			/* either its change address or somebody sent to some address that is not registered in db!
			/ say some shit that address is unknown, and mail too! */
			MailHelper::sendEmailPlain([
				'email'     => Config::get('mail.admin_email'),
				'subject'   => 'RECEIVED BITCOINS TO UNKNOWN ADDRESS',
				'text'      => 'RECEIVED ' . $btc_amount . ' BITCOINS TO UNKNOWN ADDRESS. Address that received it: ' . $to_address,
			]);

			$initialUserBalance = Balance::getBalance($this->user->id, $this->crypto_type_id);

			$data = [
				'tx_id'             => $tx_id,
				'user_id'           => $this->user->id,
				'transaction_type'  => TX_RECEIVE,
				'crypto_amount'     => $satoshi_amount,
				'crypto_type_id'    => $this->crypto_type_id,
				'address_to'        => $to_address,
				'address_from'      => $address_from,
				'confirmations'     => $confirms,
				'block_hash'        => $block_hash,
				'block_index'       => $block_index,
				'block_time'        => $block_time,
				'tx_time'           => $time,
				'tx_timereceived'   => $time_received,
				'tx_category'       => $category,
				'address_account'   => $account_name,
				'note'              => TX_UNREGISTERED_ADDRESS,
				'balance'           => bcadd($initialUserBalance, $satoshi_amount), // new API user balance
				'previous_balance'  => $initialUserBalance->balance, // API user balance before that transaction, because user balance has not been updated yet
				'bitcoind_balance'  => bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION), // bitcoind balance on received! that means this transaction is not included, because it has 0 conf
			];

			// insert new transaction anyway
			Transaction::insertNewTransaction( $data );

			DB::commit();

			Log::warning('Received payment to unregistered address');

			return 'fuck yea';
		}

		/* Now it is time to fire to the API user callback URL which is his app that is using this server's API
		/ mind the secret here, that app has to verify that it is coming from the API server not somebody else */
		$queryString = http_build_query([
			'input_transaction_hash' => $tx_id,
			'input_address'          => $to_address,
			'value'                  => $satoshi_amount,
			'confirmations'          => $confirms,
			'host'                   => $HOST_NAME,
			'type'                   => TX_API_USER,
		]);

		$full_callback_url = $this->user->callback_url . '?'. $queryString;
		$full_callback_url_with_secret = $full_callback_url . "&secret=" . $this->user->secret; // don't include secret in a log
		Log::info( 'Sending callback to: ' . $full_callback_url );

		$app_response = $this->dataParser->fetchUrl( $full_callback_url_with_secret ); // TODO wrap in exception - means the host did not respond
		Log::info( 'Received response from server: ' . $app_response );

		$callback_status = null;
		if ( $app_response == "*ok*" ) {
			$callback_status = 1;
		}

		//if we get back an *ok* from the script then update the transactions status
		Transaction::updateTxOnAppResponse( $transaction_model, $app_response, $full_callback_url, $callback_status );

		DB::commit();

		Log::info( 'Updated transaction id ' . $transaction_model->id . ' with response: ' . $app_response . ', callback status: ' . $callback_status );

		$response = [
			'confirmations' => $confirms,
			'address'       => $to_address,
			'amount'        => $btc_amount,
			'txid'          => $tx_id,
			'callback_url'  => $full_callback_url,
			'response'      => $app_response,
		];

		return Response::json($response);
	}

	/* example.com/api/receive?method=create&address=xxx&callback=https://callback_url.com&label=xxx&forward=1&userid=1
	/ if forward = 0, then don't forward to address. label needed just in this case, when forward 0 and it has a role of note */
	public function receive()
	{
		Log::info( '=== RECEIVE STARTED ===' );

		$ip_address = Request::ip();

		if ( Input::get( 'cryptotype' ) ) {
			$this->crypto_type_id = Input::get( 'cryptotype' );
		}

		$method = Input::get( 'method' );
		if ( $method != 'create' ) {
			Log::error('#receive: ' . NO_CREATE_METHOD_ON_INVOICE);
			return Response::json( ['error' => NO_CREATE_METHOD_ON_INVOICE] );
		}

		/* if API server chose private invoicing, rather than blockchain.info style invoicing API
		/ then have to check if secret is included in URL */
		$isPrivate = Config::get( 'bitcoin.private_invoicing' );

		if ( $isPrivate ) {
			$secret = Config::get( 'bitcoin.callback_secret' );
			if ( $secret != Input::get( 'secret' ) )
			{
				Log::error( '#receive: secret mismatch, full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address );
				return Response::json( ['error' => '#receive: ' . SECRET_MISMATCH] );
			}

			$user_id = Input::get('userid');
			if ( ! $user_id ) {
				Log::error( '#receive: ' . NO_USER . ', full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address );
				return Response::json( ['error' => '#receive: ' . NO_USER] );
			}
			$this->user = User::find($user_id);
		}
		else
		{
			$this->user = User::find(1); // because its not private, then in API server its by default first user
		}

		$receiving_address = Input::get( 'address' );
		$receiving_address = isset( $receiving_address ) ? $receiving_address : '';
		$callback_url      = Input::get( 'callback' );
		$label             = Input::get( 'label' );
		$forward           = Input::get( 'forward' );
		$invoice_amount    = Input::get( 'amount' ); // BTC

		if ( ! empty( $invoice_amount ) ) {
			$invoice_amount = bcmul( $invoice_amount, SATOSHIS_FRACTION );
		} else {
			$invoice_amount = 0;
		}

		if ( empty( $receiving_address ) ) {
			$forward = 0;
		}

		if ( ! empty( $receiving_address ) )
		{
			$isValidAddress = BitcoinHelper::isValid( $receiving_address );
			if ( ! $isValidAddress )
			{
				Log::error( "#receive: non valid address: $receiving_address from url: " . Request::fullUrl() . ', ip address: ' . $ip_address);
				return Response::json( ['error' => '#receive: ' . INVALID_ADDRESS] );
			}
		}

		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
		$input_address = $this->bitcoin_core->getnewaddress( 'invoice' );

		InvoiceAddress::saveInvoiceAddress([
			'address'               => $input_address,
			'destination_address'   => $receiving_address,
			'invoice_amount'        => $invoice_amount,
			'label'                 => $label,
			'callback_url'          => $callback_url,
			'forward'               => $forward,
			'crypto_type_id'        => $this->crypto_type_id,
			'user_id'               => $this->user->id,
		]);

		Log::info( '=== RECEIVE NEW ADDRESS GENERATED AND SAVED which is receiving address: ' . $receiving_address . ', input address' . $input_address . ' ===' );

		$response = array(
			'fee_percent'   => 0,
			'forward'       => $forward,
			'destination'   => $receiving_address,
			'input_address' => $input_address,
			'callback_url'  => $callback_url,
		);

		return Response::json( $response );
	}

	public function blocknotify()
	{
		Log::info( '=== BLOCK NOTIFY CALLBACK STARTED ===' );
		Log::info('User id: '.Input::get('userid').', block hash: '.Input::get('blockhash'));

		$ip_address = Request::ip();

		if ( Input::get( 'cryptotype' ) ) {
			$this->crypto_type_id = Input::get( 'cryptotype' );
		}

		$server_callback_secret = Config::get( 'bitcoin.callback_secret' );

		if ( $server_callback_secret != Input::get( 'secret' )) {
			Log::error( '#blocknotify: ' . SECRET_MISMATCH . ', full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address );
			return Response::json( ['#blocknotify: ' . SECRET_MISMATCH] );
		}

		$user_id = Input::get('userid');
		if ( ! $user_id ) {
			Log::error( '#blocknotify: ' . NO_USER );
			return Response::json( ['error' => '#blocknotify: ' . NO_USER] );
		}
		$this->user = User::find($user_id);

		// no point to add secret
		$full_callback_url = $this->user->blocknotify_callback_url . '?blockhash=' . Input::get('blockhash') . '&host=' . gethostname();

		Log::info( 'Sending callback to: ' . $full_callback_url );

		$full_callback_url_with_secret = $full_callback_url . "&secret=" . $this->user->secret; // don't include secret in a log
		$app_response                  = $this->dataParser->fetchUrl( $full_callback_url_with_secret ); // TODO wrap in exception - means the host did not respond

		Log::info( 'Received response from server: ' . $app_response );

		return Response::json('ok :)');
	}

	/**
	 * @param $user_id
	 * @param $btc_amount
	 * @param $to_address
	 * @param $tx_id
	 * @param $confirms
	 */
	private function processOutgoingTransaction( $user_id, $btc_amount, $to_address, $tx_id, $confirms )
	{
		Log::info( "Sent out $btc_amount bitcoins to $to_address, tx_id $tx_id" );

		PayoutHistory::insertNewTransaction( [
			'user_id'        => $user_id,
			'tx_id'          => $tx_id,
			'crypto_amount'  => bcmul( $btc_amount, SATOSHIS_FRACTION ),
			'crypto_type_id' => $this->crypto_type_id,
			'address_to'     => $to_address,
			'confirmations'  => $confirms,
		] );
	}

	/**
	 * Attempt to authenticate. True on success, also sets $this->user to authenticated user
	 * @return bool whether user authentication was successful
	 */
	private function attemptAuth()
	{
		$guid           = Request::segment(2);
		$method         = Request::segment(3, 'empty');
		$ip_address     = Request::ip();
		$fullUrl        = Request::fullUrl(); // if request fails, only then log the full query string

		if ( Input::get( 'cryptotype' ) ) {
			$this->crypto_type_id = Input::get( 'cryptotype' );
		}

		$error        = $this->checkQueryRequiredArgs();

		if ( $error ) {
			Log::error("Query arguments not correct. Error: $error. Arguments - GUID: $guid, method: $method, ipAddress: $ip_address. Full URL: $fullUrl");
			return false;
		}
		$user_valid = $this->validateUser( $guid ); // error is printed inside #validateUser function
		if ( $user_valid['status'] == 'error' ) {
			Log::error('User not validated. Error: ' . $user_valid['message'] . ". Arguments - GUID: $guid, method: $method, ipAddress: $ip_address. Full URL: $fullUrl");
			return false;
		}

		return true;
	}

	private function checkQueryRequiredArgs() {
		$method        = Request::segment( 3 );
		$guid          = Request::segment( 2 );
		$password      = Input::get( 'password' );
		$invalid_query = array();
		if ( ! $method ) {
			$invalid_query[] = 'no method';
		}
		if ( ! $guid ) {
			$invalid_query[] = 'no guid';
		}
		if ( ! $password ) {
			$invalid_query[] = 'no password';
		}

		return isset( $invalid_query ) ? implode( $invalid_query, ', ' ) : null; // if some of required query params were missing, then return null
	}

	private function validateUser( $guid ) {
		$user = User::getUserByGuid($guid);

		if ( Input::get( 'debug' ) or API_DEBUG == true ) {
			echo "GUID: " . $guid . "\n";
		}

		// no user found
		if ( !count($user) )
		{
			return ['status' => 'error', 'message' => NO_USER];
		}
		if ( $user->password != Input::get( 'password' ) )
		{
			return ['status' => 'error', 'message' => WRONG_PASSWD ];
		}
		$this->user = $user;

		return ['status' => 'success'];
	}

	private function satoshiToBtc( $satoshis ) {
		return (float) bcdiv( $satoshis, SATOSHIS_FRACTION, 8 );
	}

	public function missingMethod($parameters = array())
	{
		return Response::json( ['error' => 'unknown method'] );
	}

}