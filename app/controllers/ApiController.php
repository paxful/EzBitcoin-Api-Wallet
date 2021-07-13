<?php

use Carbon\Carbon;
use Helpers\DataParserInterface;
use Helpers\JsonRPCClientInterface;
use Illuminate\Support\Facades\Response;
use Services\Blockchain\Response\UnspentOutputsResponse;

class ApiController extends BaseController
{

	protected $crypto_type_id = 1;
	protected $user;
	protected $bitcoin_core;
	protected $dataParser;
	protected $HOST_NAME;

	const OUTPUTS_CACHE_KEY = 'outputs-cache';

	public function __construct(JsonRPCClientInterface $bitcoin_core_client, DataParserInterface $dataParser)
	{
		$this->bitcoin_core = $bitcoin_core_client;
		$this->dataParser   = $dataParser;
		$this->HOST_NAME    = gethostname();
	}

	public function getIndex()
	{
		return View::make('hello');
	}

	/**
	 * example.com/api/<guid>/balance?password=xxx&debug=1
	 */
	public function balance($guid)
	{
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		// at this point '$this->user' is already set

		$user_balance = Balance::getBalance($this->user->id, $this->crypto_type_id);


		if (count($user_balance)) {
			Log::info('Queried balance for ' . $this->user->email . ': ' . $user_balance->balance . ' satoshis. Crypto type id: ' . $this->crypto_type_id);
			$response = ['balance' => $user_balance->balance, 'crypto_type' => $this->crypto_type_id];
		} else {
			Log::warn('Balance not found for user ' . $this->user->email . 'for crypto type ' . $this->crypto_type_id);
			$response = ['error' => 'Balance not found for crypto type ' . $this->crypto_type_id];
		}
		return Response::json($response);
	}

	/**
	 * Get bitcoin-core core balance, good for accounting by comparing your app users balances sum and bitcoin core balance
	 * example.com/api/<guid>/core-balance?&password=zzz
	 */
	public function coreBalance($guid)
	{
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);

		$balance = $this->bitcoin_core->getbalance();

		return Response::json(['balance' => $balance]);
	}

	public function addressBalance($guid)
	{
		// not implemented
	}

	/**
	 * example.com/api/<guid>/validate-transaction?txid=xxx&&password=zzz&debug=1
	 */
	public function validateTransaction($guid)
	{
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$tx_id = Input::get('txid');
		if (!$tx_id) {
			Log::error('#validateTransaction: ' . NO_TX_ID);
			return Response::json(['error' => '#validateTransaction: ' . NO_TX_ID]);
		}

		try {
			//			$bitcoind_client = new \Helpers\JsonRPCClient($this->user->rpc_connection);
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_info = $this->bitcoin_core->gettransaction($tx_id);

			//if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
			//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and
			//use the getrawtransaction call to request information about any transaction
			//$strRawHex = $this->jsonrpcclient->getrawtransaction($strTransaction);
			//$objJSON = $this->jsonrpcclient->decoderawtransaction($strRawHex);

			//bind values to variables
			$transaction_id = $tx_info["txid"];

			if ((Input::get('debug') or API_DEBUG == true)) {
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
				echo nl2br($new) . "\n";
			}
			return Response::json(['is_valid' => true, 'tx_id' => $tx_id]);
		} catch (Exception $e) {
			Log::error('#validateTransaction: get transaction exception: ' . $e->getMessage());
			return Response::json(['error' => '#validateTransaction: get transaction exception: ' . $e->getMessage()]);
		}
	}

	/**
	 * example.com/api/<guid>/validate_address?address=xxx&password=zzz&debug=1
	 */
	public function validateAddress($guid)
	{
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$address = Input::get('address');
		if (!$address) {
			Log::error("#validateAddress: address ($address) not provided for user " . $this->user->email);
			return Response::json(['error' => NO_ADDRESS]);
		}

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$address_valid = $this->bitcoin_core->validateaddress($address);
		} catch (Exception $e) {
			Log::error("#validateAddress: validate address ($address) exception: " . $e->getMessage());
			return Response::json(['error' => '#validateAddress: validate address exception: ' . $e->getMessage()]);
		}

		$is_valid = $address_valid["isvalid"];
		if (!$is_valid) {
			Log::error("#validateAddress: address $address");
			return Response::json(['error' => '#validateAddress: ' . INVALID_ADDRESS]);
		}

		$address = $address_valid["address"];
		$is_mine = false;

		$user_address = Address::getAddressForUser($address, $this->user->id);

		if (count($user_address)) {
			$is_mine = true;
		}

		return Response::json(['isvalid' => $is_valid, 'address' => $address, 'ismine' => $is_mine]);
	}

	/**
	 * example.com/api/<guid>/new_address?label=xxx&password=zzz&debug=1
	 */
	public function newAddress($guid)
	{
		// because it can be a long process, set execution time to a lot more
		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');

		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$new_wallet_address = $this->bitcoin_core->getnewaddress();

			$label = Input::get('label');
			if (!$label) {
				$label = null;
			}

			Address::insertNewAddress([
				'user_id'        => $this->user->id,
				'address'        => $new_wallet_address,
				'label'          => $label,
				'crypto_type_id' => $this->crypto_type_id
			]);
		} catch (Exception $e) {
			Log::error('#newAddress: get new address exception: ' . $e->getMessage());
			return Response::json(['error' => '#newAddress: get new address exception: ' . $e->getMessage()]);
		}

		return Response::json(['address' => $new_wallet_address, 'label' => $label]);
	}

	public function txConfirmations($guid)
	{
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$tx_id = Input::get('txid');
		if (!$tx_id) {
			Log::error('#getTransactionConfirmations: ' . NO_TX_ID);
			return Response::json(['error' => '#getTransactionConfirmations: ' . NO_TX_ID]);
		}

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_info = $this->bitcoin_core->gettransaction($tx_id);

			//bind values to variables
			$transaction_id = $tx_info["txid"];

			if ((Input::get('debug') or API_DEBUG == true)) {
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
				echo nl2br($new) . "\n";
			}

			return Response::json(['confirmations' => $tx_info["confirmations"]]);
		} catch (Exception $e) {
			Log::error('#getTransactionConfirmations: get transaction exception: ' . $e->getMessage());
			return Response::json(['error' => '#getTransactionConfirmations: ' . $e->getMessage()]);
		}
	}

	/**
	 * example.com/api/<guid>/payment?to=xxx&amount=satoshis&note=yyy&password=zzz&debug=1
	 */
	public function payment($guid)
	{
		// because it can be a long process, set execution time to a lot more
		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');

		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$to_address = Input::get('to');
		$amount_satoshi     = Input::get('amount');
		//		$note       = Input::get( 'note' );
		$note       = '';
		$external_user_id = Input::get('external_user_id', null);

		if (!$note) {
			$note = '';
		}

		Log::info("=== PAYMENT to $to_address, note: $note, amount: " . self::satoshiToBtc($amount_satoshi));

		if (empty($to_address) or empty($amount_satoshi)) {
			Log::error('#payment: ' . ADDRESS_AMOUNT_NOT_SPECIFIED);
			return Response::json(['error' => '#payment: ' . ADDRESS_AMOUNT_NOT_SPECIFIED]);
		}

		$isValidAddress = BitcoinHelper::isValid($to_address);
		if (!$isValidAddress) {
			Log::error('#payment: ' . INVALID_ADDRESS);
			return Response::json(['error' => '#payment: ' . INVALID_ADDRESS]);
		}

		DB::beginTransaction(); // begin DB transaction

		$user_balance = Balance::getBalance($this->user->id, $this->crypto_type_id);

		if ($user_balance->balance < $amount_satoshi) {
			DB::rollback();
			Log::error('#payment: ' . NO_FUNDS);
			return Response::json(['error' => '#payment: ' . NO_FUNDS]);
		}

		$amount_satoshi = abs($amount_satoshi); // make it sure its positive

		$new_balance = bcsub($user_balance->balance, $amount_satoshi);

		Log::info('User initial balance: ' . self::satoshiToBtc($user_balance->balance) . ' BTC, new balance: ' . self::satoshiToBtc($new_balance));

		Balance::setNewUserBalance($user_balance, $new_balance);

		$bitcoin_amount = self::satoshiToBtc($amount_satoshi); // return float

		$sent = false;

		$message  = "Sent $bitcoin_amount, crypto type id: " . $this->crypto_type_id . " to $to_address";

		try {
			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
			$tx_id = $this->bitcoin_core->sendtoaddress($to_address, $bitcoin_amount, $note);
			$sent = true;
			if ($tx_id) {
				// if it fails here on inserting new transaction, then this transaction will be rolled back - user balance not updated, but jsonrpcclient will send out.
				// think of a clever way on which step it failed and accordingly let know if balance was updated or not
				$new_transaction = Transaction::insertNewTransaction([
					'tx_id' => $tx_id,
					'user_id' => $this->user->id,
					'transaction_type' => TX_SEND,
					'crypto_amount' => $amount_satoshi,
					'crypto_type_id' => $this->crypto_type_id,
					'address_to' => $to_address,
					'note' => $note,
					'external_user_id' => $external_user_id
				]);
			}
		} catch (Exception $e) {
			DB::rollback();
			Log::error("#payment: send to address exception: " . $e->getMessage());

			// create identical data first
			$tx_data = [
				'user_id' => $this->user->id,
				'address_to' => $to_address,
				'crypto_amount' => $amount_satoshi,
				'error' => $e->getMessage(),
				'user_note' => $note,
				'sent_to_network' => $sent,
				'transaction_type' => TX_SEND,
				'external_user_id' => $external_user_id,
			];

			// because transaction was sent to network, decrease API user balance and also insert transaction hash
			if ($sent) {
				Balance::setNewUserBalance($user_balance, $new_balance); // also decrease balance
				$tx_data['tx_id'] = $tx_id; // because was sent to network, we know tx_id
				TransactionFailed::insertTransaction($tx_data);
				return Response::json(['message' => $message, 'tx_hash' => $tx_id]);
			} else {
				TransactionFailed::insertTransaction($tx_data);
			}
			return Response::json(['error' => "#payment: send to address exception: " . $e->getMessage()]);
		}
		DB::commit();

		if (isset($new_transaction)) {
			$this->saveFee($new_transaction);
		}

		return Response::json(['message' => $message, 'tx_hash' => $tx_id]);
	}

	/**
	 * example.com/api/<guid>/sendmany?to=xxx&amount=satoshis&note=yyy&password=zzz&account=invoice&debug=1
	 */
	public function sendmany($guid)
	{
		// because it can be a long process, set execution time to a lot more
		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');

		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}

		$recipients_json = Input::get('recipients');
		//		$note       = Input::get( 'note' );
		$note       = '';
		$external_user_id = Input::get('external_user_id', null);
		$account = Input::get('account', "");

		if (!$note) {
			$note = '';
		}

		/* validate that recipients is JSON object with address:satoshi pairs */
		$recipients = json_decode($recipients_json);
		$is_valid_recipients = $this->validateSendManyJson($recipients);

		if (!$is_valid_recipients) {
			return Response::json(['error' => '#sendmany: ' . ADDRESS_AMOUNT_NOT_SPECIFIED_SEND_MANY]);
		}

		Log::info('=== START PAYMENT to MANY');
		foreach ($recipients as $btc_address => $satoshi_amount) {
			Log::info("Payment to $btc_address, note: $note, amount: " . self::satoshiToBtc($satoshi_amount));
		}
		Log::info('=== END PAYMENT to MANY');

		$total_satoshis = $this->getSendManyTotalAmount($recipients);

		DB::beginTransaction(); // begin DB transaction

		// ignore balance when using bitGo
		if (!$this->user->ignore_balance) {
			$user_balance = Balance::getBalance($this->user->id, $this->crypto_type_id);

			if ($user_balance->balance < $total_satoshis) {
				DB::rollback();
				Log::error('#sendmany: ' . NO_FUNDS);
				return Response::json(['error' => '#payment: ' . NO_FUNDS]);
			}

			$total_satoshis = abs($total_satoshis); // make it sure its positive

			$new_balance = bcsub($user_balance->balance, $total_satoshis);

			Log::info('User initial balance: ' . self::satoshiToBtc($user_balance->balance) . ' BTC, new balance: ' . self::satoshiToBtc($new_balance));

			Balance::setNewUserBalance($user_balance, $new_balance);
		}

		$sent = false;

		try {
			// convert satoshis to btc
			$recipients_copy = clone $recipients; // need to copy to another array, since satoshis are converted to bitcoin denomination by reference

			$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);

			$addedExtraOutputs = false;

			/* check if that functionality is there */
			if (BitcoinHelper::isMonitoringOutputsEnabled()) {
				// if anything fails in here, just continue sending
				try {
					/* Check here if there are enough confirmed UTXOs and whether more need to be added
					 * Check of outputs were not checked recently, otherwise query for unspents */
					$checkedOutputsRecently = Cache::get(self::OUTPUTS_CACHE_KEY);
					if (!$checkedOutputsRecently) {
						$outputs = $this->bitcoin_core->listunspent(1);
						$outputsResponse = new UnspentOutputsResponse($outputs);
						$total = $outputsResponse->getTotal();
						$outputsThreshold = BitcoinHelper::getOutputsThreshold();
						Log::info('Initiating checking outputs, total outputs: ' . $total . '. Outputs threshold: ' . $outputsThreshold);
						// if total less than 150, create 125 more
						if ($total < $outputsThreshold) {
							// send to own addresses some 0.06 or something
							// get 125 more addresses and create pairs for them
							$recipients_copy = BitcoinHelper::addOutputsToChangeAddresses($recipients_copy, $this->user->id);
							// sending email of new transaction hash to email at the end of this method
							$addedExtraOutputs = true;
						} else {
							// not added, just email about it
							MailHelper::sendAdminEmail([
								'subject' => 'Enough outputs exists',
								'text'    => 'No need to add extra. Number of outputs: ' . $outputsResponse->getTotal(),
							]);
						}

						// set cache for 45 minutes until next check for outputs
						$outputsCacheDuration = BitcoinHelper::getOutputsCacheDuration();
						Cache::put(self::OUTPUTS_CACHE_KEY, 1, $outputsCacheDuration);
					}
				} catch (Exception $e) {
					MailHelper::sendAdminEmail([
						'subject' => 'Failed in adding more outputs',
						'text'    => "Message\n" . $e->getMessage() . "\nTrace\n" . $e,
					]);

					// replace back only original recipients
					$recipients_copy = clone $recipients;
				}
			}

			$recipients_bitcoin_denomination_obj = $this->convertSendManySatoshisToBtc($recipients_copy, false);

			// if user is BitGoD, need to call specific walletpassphrase command to unlock sending
			if ($this->user->name == 'BitGoD') {
				BitGoHelper::unlockWallet($this->bitcoin_core);
			}
			$tx_id = $this->bitcoin_core->sendmany($account, $recipients_bitcoin_denomination_obj, 0, $note);
			$sent = true;
			if ($tx_id) {
				// if it fails here on inserting new transaction, then this transaction will be rolled back - user balance not updated, but jsonrpcclient will send out.
				// think of a clever way on which step it failed and accordingly let know if balance was updated or not
				foreach ($recipients as $address => $amount_satoshi) {
					$new_transaction = Transaction::insertNewTransaction([
						'tx_id' => $tx_id,
						'user_id' => $this->user->id,
						'transaction_type' => TX_SEND,
						'crypto_amount' => $amount_satoshi,
						'crypto_type_id' => $this->crypto_type_id,
						'address_to' => $address,
						'note' => $note,
						'external_user_id' => $external_user_id
					]);
				}
			}
			if ($this->user->name == 'BitGoD') {
				BitGoHelper::lockWallet($this->bitcoin_core);
			}
		} catch (Exception $e) {
			DB::rollback();
			Log::error("#sendmany: send to address exception: " . $e->getMessage());

			foreach ($recipients as $address => $amount_satoshi) {
				// create identical data first
				$tx_data = [
					'user_id' => $this->user->id,
					'address_to' => $address,
					'crypto_amount' => $amount_satoshi,
					'error' => $e->getMessage(),
					'user_note' => $note,
					'sent_to_network' => $sent,
					'transaction_type' => TX_SEND,
					'external_user_id' => $external_user_id,
				];

				// because transaction was sent to network, decrease API user balance and also insert transaction hash
				if ($sent) {
					// ignore balance when using bitGo
					if (!$this->user->ignore_balance) {
						Balance::setNewUserBalance($user_balance, $new_balance); // also decrease balance
					}
					$tx_data['tx_id'] = $tx_id; // because was sent to network, we know tx_id
					TransactionFailed::insertTransaction($tx_data);
					return Response::json(['message' => "#sendmany: send to address exception: " . $e->getMessage(), 'tx_hash' => $tx_id]);
				} else {
					TransactionFailed::insertTransaction($tx_data);
				}
			}
			// send email
			MailHelper::sendAdminEmail([
				'subject' => 'Failed in sendmany',
				'text'    => "Message\n" . $e->getMessage() . "\nTrace\n" . $e,
			]);
			return Response::json(['error' => "#sendmany: send to address exception: " . $e->getMessage()]);
		}
		DB::commit();

		if (isset($new_transaction)) {
			$this->saveFee($new_transaction);

			if ($addedExtraOutputs) {
				// send email about extra outputs with tx hash
				MailHelper::sendAdminEmail([
					'subject' => 'Added extra outputs',
					'text'    => "Tx id: $tx_id",
				]);
			}
		}
		return Response::json(['message' => 'Sent To Multiple Recipients', 'tx_hash' => $tx_id]);
	}

	public function listUnspent()
	{
		$min_confirms = (int)Input::get('confirms', 1);
		if (!$this->attemptAuth()) {
			return Response::json(['error' => AUTHENTICATION_FAIL]);
		}
		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
		$unspent = $this->bitcoin_core->listunspent($min_confirms);
		return Response::json($unspent);
	}

	/**
	 * Callback is initiated when:
	 * Receiving transaction gets into mempool
	 * Sending out transaction - has negative amount
	 * Transaction gets 1st confirmation
	 * This is most important function in whole thing, but very spaghetti. Has to be refactored
	 */
	public function callback()
	{
		Log::info('=== CALLBACK. ' . 'User id: ' . Input::get('userid') . ', tx hash: ' . Input::get('txid'));

		/* the url structure is different, so different segments of URI */
		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		$server_callback_secret = Config::get('bitcoin.callback_secret');
		if ($server_callback_secret != Input::get('secret')) {
			Log::error('#callback: ' . SECRET_MISMATCH . ', full URL:  ' . Request::fullUrl());
			return Response::json(['#callback: ' . SECRET_MISMATCH]);
		}
		/*--------------------------------------------*/

		/*sends callback on receive notification
		gets a transaction hash id
		calls bitcoind via RPC to get transaction info
		calls a web url specified in the user account
		called from /home/api/walletnotify.sh
		sudo curl http://127.0.0.1/api/callback/?txid=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&local=n00nez&loginname=ammm&password=PsQWsO4sDLwqTxxx&debug=1*/

		$tx_id = Input::get('txid'); // check if not null
		if (!$tx_id) {
			Log::error("#callback, no tx id: " . NO_TX_ID);
			return Response::json(['error' => NO_TX_ID]);
		}

		$user_id = Input::get('userid');
		if (!$user_id) {
			// TODO daaaaamn
			// return here with error
		}
		$this->user = User::find($user_id);

		$bitcoind_timestamp = Input::get('time');

		// TODO if user is not set here. decide how to set user
		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);

		try {
			$tx_info = $this->bitcoin_core->gettransaction($tx_id);
		} catch (Exception $e) {
			Log::error('#callback: get transaction exception: ' . $e->getMessage());
			return Response::json(['error' => '#callback: get transaction exception: ' . $e->getMessage()]);
		}

		$confirms      = $tx_info['confirmations'];
		$block_hash    = isset($tx_info['blockhash']) ? $tx_info['blockhash'] : null;
		$block_index   = isset($tx_info['blockindex']) ? $tx_info['blockindex'] : null;
		$block_time    = isset($tx_info['blocktime']) ? $tx_info['blocktime'] : null;
		$time          = $tx_info['time'];
		$time_received = $tx_info['timereceived'];
		$fee           = isset($tx_info['fee']) ? abs(bcmul($tx_info['fee'], SATOSHIS_FRACTION)) : null;

		$transaction_details = $tx_info["details"];

		foreach ($transaction_details as $tx) {
			$to_address    = $tx['address']; // address where transaction was sent to. from address may be multiple inputs which means many addresses
			$account_name  = $tx['account'];
			$address_from  = ''; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
			$category      = $tx['category'];
			$btc_amount    = $tx["amount"];

			if ((Input::get('debug') or API_DEBUG == true)) {
				$this->print_debug($tx_id, $tx_info, $block_hash, $block_index, $block_time, $account_name, $to_address, $category, $btc_amount);
			}

			/******************* START of checking if its outgoing transaction *******************/
			if ($btc_amount < 0) {
				$this->processOutgoingTransaction($user_id, $btc_amount, $to_address, $tx_id, $confirms);
				continue; // loop more in case there is something
			}
			/******************* END of checking if its outgoing transaction *******************/

			Log::info("Address $to_address, amount (BTC): $btc_amount, confirms: $confirms received transaction id $tx_id");

			/* whether new transaction or notify was fired on 1st confirmation */
			$transaction_model = Transaction::getTransactionByTxIdAndAddress($tx_id, $to_address);
			$satoshi_amount    = bcmul($btc_amount, SATOSHIS_FRACTION);
			$is_own_address = false; // if not own address, then unknown address received transaction

			/* create common data for transaction */
			$common_data = [
				'tx_id'             => $tx_id,
				'user_id'           => $this->user->id,
				'crypto_amount'     => $satoshi_amount,
				'network_fee'       => $fee,
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
			];

			/******************* START processing the invoicing callback **************/
			$invoice_address_model = InvoiceAddress::getAddress($to_address);
			if ($invoice_address_model) {
				$is_own_address = true;
				$this->processInvoiceAddress($transaction_model, $invoice_address_model, $common_data, $satoshi_amount);
				continue; // continue into the next loop
			}
			/*************** END processing the invoicing callback **************/

			/*************************************************************************************/
			/* at this point its not the invoicing address, lookup address in address table
			/*************************************************************************************/

			/************* It is incoming transaction, because it is sent to some of the inner addresses *************/
			$address_model = Address::getAddress($to_address);
			if ($address_model) {
				$is_own_address = true;
				$this->processUserAddress($transaction_model, $address_model, $common_data, $satoshi_amount);
				continue;
			}

			/* The receiving address wasn't in the database, so its not tied to any API user, but generated outside API directly in bitcoin core*/
			if (!$is_own_address) {
				/* This can be enabled when needed to save payment for unknown address received
				 * but because we have change addresses that are used in 'sendmany', we don't want to bloat transactions table */
				//$this->processUnknownAddress( $confirms, $transaction_model, $common_data, $btc_amount, $to_address, $satoshi_amount );
				continue;
			}
		}

		return '*ok*'; // just dummy return, since it's not being processed on other end. the return is processed in fetchUrl()

	}

	/* example.com/api/receive?method=create&address=xxx&callback=https://callback_url.com&label=xxx&forward=1&userid=1
	/ if forward = 0, then don't forward to address. label needed just in this case, when forward 0 and it has a role of note */
	public function receive()
	{
		// because it can be a long process, set execution time to a lot more
		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');

		Log::info('=== RECEIVE STARTED ===');

		$ip_address = Request::ip();

		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		$method = Input::get('method');
		if ($method != 'create') {
			Log::error('#receive: ' . NO_CREATE_METHOD_ON_INVOICE);
			return Response::json(['error' => NO_CREATE_METHOD_ON_INVOICE]);
		}

		/* if API server chose private invoicing, rather than blockchain.info style invoicing API
		/ then have to check if secret is included in URL */
		$isPrivate = Config::get('bitcoin.private_invoicing');

		if ($isPrivate) {
			$secret = Config::get('bitcoin.callback_secret');
			if ($secret != Input::get('secret')) {
				Log::error('#receive: secret mismatch, full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address);
				return Response::json(['error' => '#receive: ' . SECRET_MISMATCH]);
			}

			$user_id = Input::get('userid');
			if (!$user_id) {
				Log::error('#receive: ' . NO_USER . ', full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address);
				return Response::json(['error' => '#receive: ' . NO_USER]);
			}
			$this->user = User::find($user_id);
		} else {
			$this->user = User::find(1); // because its not private, then in API server its by default first user
		}

		$receiving_address = Input::get('address');
		$receiving_address = isset($receiving_address) ? $receiving_address : '';
		$callback_url      = Input::get('callback');
		$label             = Input::get('label');
		$forward           = Input::get('forward');
		$invoice_amount    = Input::get('amount'); // BTC

		if (!empty($invoice_amount)) {
			$invoice_amount = bcmul($invoice_amount, SATOSHIS_FRACTION);
		} else {
			$invoice_amount = 0;
		}

		if (empty($receiving_address)) {
			$forward = 0;
		}

		if (!empty($receiving_address)) {
			$isValidAddress = BitcoinHelper::isValid($receiving_address);
			if (!$isValidAddress) {
				Log::error("#receive: non valid address: $receiving_address from url: " . Request::fullUrl() . ', ip address: ' . $ip_address);
				return Response::json(['error' => '#receive: ' . INVALID_ADDRESS]);
			}
		}

		$this->bitcoin_core->setRpcConnection($this->user->rpc_connection);
		$input_address = $this->bitcoin_core->getnewaddress('invoice');

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

		Log::info('=== RECEIVING ADDRESS: ' . $receiving_address . ', input address' . $input_address . ' ===');

		$response = array(
			'fee_percent'   => 0,
			'forward'       => $forward,
			'destination'   => $receiving_address,
			'input_address' => $input_address,
			'callback_url'  => $callback_url,
		);

		return Response::json($response);
	}

	public function blocknotify()
	{
		Log::info('=== BLOCK NOTIFY. ' . 'User id: ' . Input::get('userid') . ', block hash: ' . Input::get('blockhash'));

		$ip_address = Request::ip();

		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		$server_callback_secret = Config::get('bitcoin.callback_secret');

		if ($server_callback_secret != Input::get('secret')) {
			Log::error('#blocknotify: ' . SECRET_MISMATCH . ', full URL:  ' . Request::fullUrl() . ', ip address: ' . $ip_address);
			return Response::json(['#blocknotify: ' . SECRET_MISMATCH]);
		}

		$user_id = Input::get('userid');
		if (!$user_id) {
			Log::error('#blocknotify: ' . NO_USER);
			return Response::json(['error' => '#blocknotify: ' . NO_USER]);
		}
		$this->user = User::find($user_id);

		// no point to add secret
		$full_callback_url = $this->user->blocknotify_callback_url . '?blockhash=' . Input::get('blockhash') . '&host=' . gethostname();

		$full_callback_url_with_secret = $full_callback_url . "&secret=" . $this->user->secret; // don't include secret in a log
		$app_response                  = $this->dataParser->fetchUrl($full_callback_url_with_secret); // TODO wrap in exception - means the host did not respond

		return Response::json('*ok*');
	}

	/**
	 * @param $user_id
	 * @param $btc_amount
	 * @param $to_address
	 * @param $tx_id
	 * @param $confirms
	 */
	private function processOutgoingTransaction($user_id, $btc_amount, $to_address, $tx_id, $confirms)
	{
		$payout = PayoutHistory::getByTxId($tx_id);
		if ($payout) {
			// update confirms
			if ($confirms > 0) {
				PayoutHistory::updateTxConfirmation($payout, $confirms);
			}
		} else {
			Log::info("Sent out $btc_amount bitcoins to $to_address, tx_id $tx_id");

			PayoutHistory::insertNewTransaction([
				'user_id'        => $user_id,
				'tx_id'          => $tx_id,
				'crypto_amount'  => bcmul($btc_amount, SATOSHIS_FRACTION),
				'crypto_type_id' => $this->crypto_type_id,
				'address_to'     => $to_address,
				'confirmations'  => $confirms,
			]);
		}
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
		if (Input::get('cryptotype')) {
			$this->crypto_type_id = Input::get('cryptotype');
		}

		$error        = $this->checkQueryRequiredArgs();

		if ($error) {
			Log::error("Query arguments not correct. Error: $error. Arguments - GUID: $guid, method: $method, ipAddress: $ip_address. Full URL: $fullUrl");
			return false;
		}
		$user_valid = $this->validateUser($guid); // error is printed inside #validateUser function

		if ($user_valid['status'] == 'error') {
			Log::error('User not validated. Error: ' . $user_valid['message'] . ". Arguments - GUID: $guid, method: $method, ipAddress: $ip_address. Full URL: $fullUrl");
			return false;
		}

		return true;
	}

	private function checkQueryRequiredArgs()
	{
		$method        = Request::segment(3);
		$guid          = Request::segment(2);
		$password      = Input::get('password');
		$invalid_query = array();
		if (!$method) {
			$invalid_query[] = 'no method';
		}
		if (!$guid) {
			$invalid_query[] = 'no guid';
		}
		if (!$password) {
			$invalid_query[] = 'no password';
		}
		return isset($invalid_query) ? implode($invalid_query, ', ') : null; // if some of required query params were missing, then return null
	}

	private function validateUser($guid)
	{
		$user = User::getUserByGuid($guid);
		if (Input::get('debug') or API_DEBUG == true) {
			echo "GUID: " . $guid . "\n";
		}

		// no user found
		if (!count($user)) {
			return ['status' => 'error', 'message' => NO_USER];
		}
		if ($user->password != Input::get('password')) {
			return ['status' => 'error', 'message' => WRONG_PASSWD];
		}
		$this->user = $user;

		return ['status' => 'success'];
	}

	private function satoshiToBtc($satoshis)
	{
		return (float) bcdiv($satoshis, SATOSHIS_FRACTION, 8);
	}

	private function saveFee($new_transaction)
	{
		$tx_hash = $new_transaction->tx_id;
		/* get for that transaction a miners fee, at this point we know it already */
		$bitcoinCli = $this->bitcoin_core;
		$date = Carbon::now()->addSeconds(30);
		Queue::later($date, function ($job) use ($tx_hash, $bitcoinCli) {
			// get_transaction from bitcoin core
			$tx_info         = $bitcoinCli->gettransaction($tx_hash);
			$fee             = isset($tx_info['fee']) ? abs($tx_info['fee'] * SATOSHIS_FRACTION) : null;
			// save fee for that transaction hash
			Transaction::on('pgsql')
				->where('tx_id', $tx_hash)
				->update(['network_fee' => $fee]);

			$job->delete();
		});
	}

	private function processInvoiceAddress(Transaction $transaction_model = null, InvoiceAddress $invoice_address_model, array $common_data, $satoshi_amount)
	{
		Log::info('Processing invoicing address ' . $common_data['address_to'] . ', destination address: ' . $invoice_address_model->destination_address .
			', label: ' . $invoice_address_model->label . ', amount satoshi: ' . $invoice_address_model->received_amount .
			', callback url: ' . $invoice_address_model->callback_url . ', forward: ' . $invoice_address_model->forward);

		$forward_tx_id = 0; // needed for callback. stays 0 when forwarding is not chosen

		DB::beginTransaction();

		if (!$transaction_model) {
			$initialUserBalance = Balance::getBalance($this->user->id, $this->crypto_type_id);
			// first callback, because no transaction initially found in db
			$common_data['transaction_type'] = TX_RECEIVE_INVOICING; // new API user balance
			$common_data['user_balance']     = bcadd($initialUserBalance->balance, $satoshi_amount);
			$common_data['balance']          = bcadd($invoice_address_model->balance, $satoshi_amount); // new address balance
			$common_data['previous_balance'] = $invoice_address_model->balance; // address balance before that transaction
			$common_data['bitcoind_balance'] = bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION); // bitcoind balance on received! that means this transaction is not included, because it has 0 conf;

			$transaction_model = Transaction::insertNewTransaction($common_data);

			$total_received = bcadd($invoice_address_model->received_amount, $satoshi_amount);
			InvoiceAddress::updateReceived($invoice_address_model, $total_received); // update amount and mark as received
			/* update API user balance */
			$user_balance_updated = Balance::updateUserBalance($this->user, $satoshi_amount);

			// check if needs to be forwarded
			if ($invoice_address_model->forward == 1) {
				$bitcoin_amount = bcdiv($satoshi_amount, SATOSHIS_FRACTION, 8); // division
				Log::info('Starting to forward ' . $satoshi_amount . ' satoshis which is ' . $bitcoin_amount . ' bitcoins');
				try {
					$forward_data = [
						'user_id'           => $this->user->id,
						'transaction_type'  => TX_SEND,
						'crypto_amount'     => $satoshi_amount,
						'crypto_type_id'    => $this->crypto_type_id,
						'address_to'        => $common_data['address_to'],
						'note'              => 'invoice forwarding',
						'balance'           => bcsub($transaction_model->balance, $satoshi_amount),
					];
					$forward_tx_id = $this->bitcoin_core->sendtoaddress($invoice_address_model->destination_address, (float) $bitcoin_amount);
					if ($forward_tx_id) {
						$forward_data['tx_id'] = $forward_tx_id;
						$forward_data['previous_balance']  = $transaction_model->balance;
						$forward_data['bitcoind_balance']  = bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION);
						Transaction::insertNewTransaction($forward_data);
						Balance::updateUserBalance($this->user, $satoshi_amount);
						Log::info('Forwarded ' . $bitcoin_amount . ' bitcoins to ' . $common_data['address_to']);
					} // TODO fucked when sendtoaddress throws exception, should send to server still the response.
				} catch (Exception $e) {
					Log::error("#callback: send to address exception: " . $e->getMessage());

					// add to transaction entry anyway, with failed forwarding and send warning out email !
					$forward_data['note'] = 'failed invoice forwarding';
					Transaction::insertNewTransaction($forward_data);
					DB::commit();

					MailHelper::sendEmailPlain([
						'email'     => Config::get('mail.admin_email'),
						'subject'   => 'FAILED INVOICE FORWARDING!',
						'text'      => 'Failed to forward to ' . $common_data['address_to'] . ', ' . $satoshi_amount . ' satoshis, tx hash: ' . $common_data['tx_id'],
					]);
				}
			}

			// add data that is specific to invoice address
			$common_data['transaction_hash'] = $forward_tx_id;
			$common_data['destination_address'] = $invoice_address_model->destination_address;

			$response = $this->sendUrl(
				$common_data,
				$satoshi_amount,
				TX_INVOICE,
				$invoice_address_model->callback_url,
				Config::get('bitcoin.app_secret')
			);

			// if we get back an *ok* from the script then update the transactions status
			Transaction::updateTxOnAppResponse($transaction_model, $response['app_response'], $response['callback_url'], $response['callback_status'], $response['external_user_id']);
		} else {
			/* bitcoind sent 2nd callback for the transaction which is 1st confirmation
			 * no need to shoot to the application, since application is updating first confirmation anyway on block-notify */
			Transaction::updateTxConfirmation($transaction_model, $common_data);
		}

		DB::commit();
	}

	private function processUserAddress(Transaction $transaction_model = null, Address $address_model, array $common_data, $satoshi_amount)
	{
		$this->user = $address_model->user;

		DB::beginTransaction();
		// 0 conf, just hit mempool, not 1-st confirmation
		if (!$transaction_model) {
			// first callback, because no transaction initially found in db
			Log::info('Received address ' . $address_model->address . ' label: ' . $address_model->label .
				', user guid: ' . $this->user->guid . ', email: ' . $this->user->email);

			$common_data['transaction_type'] = TX_RECEIVE;

			$new_address_balance = bcadd($address_model->balance, $satoshi_amount);

			$initialUserBalance = Balance::getBalance($this->user->id, $this->crypto_type_id);

			/* add data that is related to user address only */
			$common_data['balance']             = $new_address_balance;
			$common_data['user_balance']        = bcadd($initialUserBalance->balance, $satoshi_amount);
			$common_data['previous_balance']    = $address_model->balance;
			$common_data['bitcoind_balance']    = bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION);

			// insert new transaction
			$transaction_model = Transaction::insertNewTransaction($common_data);

			// update address balance
			Address::updateBalance($address_model, $satoshi_amount);

			/* update API user balance */
			Balance::updateUserBalance($this->user, $satoshi_amount);

			/* send to to application the response! */
			$response = $this->sendUrl(
				$common_data,
				$satoshi_amount,
				TX_API_USER,
				$this->user->callback_url,
				$this->user->secret
			);

			// if we get back an *ok* from the script then update the transactions status
			Transaction::updateTxOnAppResponse($transaction_model, $response['app_response'], $response['callback_url'], $response['callback_status'], $response['external_user_id']);
		} else {
			/* bitcoind sent 2nd callback for the transaction which is 1st confirmation
			 * no need to shoot to the application, since application is updating first confirmation anyway on block-notify */
			Transaction::updateTxConfirmation($transaction_model, $common_data);
		}
		DB::commit();
	}

	/**
	 * @param $tx_id
	 * @param $tx_info
	 * @param $block_hash
	 * @param $block_index
	 * @param $block_time
	 * @param $account_name
	 * @param $to_address
	 * @param $category
	 * @param $btc_amount
	 */
	private function print_debug($tx_id, $tx_info, $block_hash, $block_index, $block_time, $account_name, $to_address, $category, $btc_amount)
	{
		$new = "Transaction hash: " . $tx_id
			. "\n amount: " . $tx_info['amount']
			. "\n confirmations: " . $tx_info["confirmations"]
			. "\n blockhash: " . $block_hash
			. "\n blockindex: " . $block_index
			. "\n blocktime: " . $block_time
			. "\n txid: " . $tx_info["txid"]
			. "\n time: " . $tx_info["time"]
			. "\n timereceived: " . $tx_info["timereceived"]
			. "\n account: " . $account_name
			. "\n address: " . $to_address
			. "\n category: " . $category
			. "\n amount address: " . $btc_amount;
		echo nl2br($new) . "\n";
	}

	/**
	 * @param $confirms
	 * @param $transaction_model
	 * @param $common_data
	 * @param $btc_amount
	 * @param $to_address
	 * @param $satoshi_amount
	 *
	 * @return mixed
	 */
	private function processUnknownAddress($confirms, $transaction_model = null, $common_data, $btc_amount, $to_address, $satoshi_amount)
	{
		if ($confirms > 0) {
			/* bitcoind sent 2nd callback for the transaction which is 1st confirmation
			 * no need to shoot to the application, since application is updating first confirmation anyway on block-notify */
			Transaction::updateTxConfirmation($transaction_model, $common_data);
		} else {
			/* either its change address or somebody sent to some address that is not registered in db!
			 * say some shit that address is unknown, and mail too! */
			MailHelper::sendEmailPlain([
				'email'   => Config::get('mail.admin_email'),
				'subject' => 'RECEIVED BITCOINS TO UNKNOWN ADDRESS',
				'text'    => 'RECEIVED ' . $btc_amount . ' BITCOINS TO UNKNOWN ADDRESS. Address that received it: ' . $to_address,
			]);

			$initialUserBalance = Balance::getBalance($this->user->id, $this->crypto_type_id);

			$common_data['transaction_type'] = TX_RECEIVE;
			$common_data['note']             = TX_UNREGISTERED_ADDRESS;
			$common_data['user_balance']     = bcadd($initialUserBalance, $satoshi_amount); // new API user balance
			$common_data['previous_balance'] = $initialUserBalance->balance; // API user balance before that transaction, because user balance has not been updated yet
			$common_data['bitcoind_balance'] = bcmul($this->bitcoin_core->getbalance(), SATOSHIS_FRACTION); // bitcoind balance on received! that means this transaction is not included, because it has 0 conf

			// insert new transaction anyway
			Transaction::insertNewTransaction($common_data);

			Log::warning('Received payment to unregistered address');
		}
	}

	private function sendUrl(array $common_data, $satoshi_amount, $type, $callback_url, $secret)
	{
		/* Now it is time to fire to the API user callback URL which is his app that is using this server's API
		 * mind the secret here, that app has to verify that it is coming from the API server not somebody else */
		$queryString = http_build_query([
			'value'                  => $satoshi_amount,
			'input_address'          => $common_data['address_to'],
			'confirmations'          => $common_data['confirmations'],
			'input_transaction_hash' => $common_data['tx_id'],
			'host'                   => $this->HOST_NAME,
			'type'                   => $type,
		]);

		$full_callback_url = $callback_url . '?' . $queryString;
		$full_callback_url_with_secret = $full_callback_url . "&secret=" . $secret; // don't include secret in a log

		// wrapped in exception - means the host did not respond or something else happened,
		// so save as 'non-notified' response, and with cron job shoot notification again to app
		try {
			$app_response = $this->dataParser->fetchUrl($full_callback_url_with_secret);
		} catch (Exception $e) {
			$app_response = 'non-notified';
			$amontBtc = self::satoshiToBtc($satoshi_amount);
			// email about non notified transaction
			MailHelper::sendAdminEmail([
				'subject' => 'App didnt return response on notifying about new transaction',
				'text'    => "Full callback URL: $full_callback_url\nAmount: $amontBtc BTC",
			]);
		}

		$callback_status = false;
		$external_user_id = null;
		if ($app_response == "*ok*") {
			$callback_status = 1;
		} else {
			$json_response = json_decode($app_response);
			if ($json_response and isset($app_response->external_user_id)) {
				$external_user_id = $app_response->external_user_id;
				$callback_status = 1;
			}
		}

		return [
			'app_response'      => $app_response,
			'callback_status'   => $callback_status,
			'callback_url'      => $full_callback_url,
			'external_user_id'  => $external_user_id,
		];
	}

	private function validateSendManyJson(stdClass $recipients)
	{
		if ($recipients === null) {
			return false;
		}
		foreach ($recipients as $btc_address => $satoshi_amount) {
			// is valid bitcoin address
			// satoshis are in integer
			// satoshi is more than zero
			if (!BitcoinHelper::isValid($btc_address) or !is_int($satoshi_amount) or $satoshi_amount <= 0) {
				return false;
			}
		}
		return true;
	}

	private function getSendManyTotalAmount(stdClass $recipients)
	{
		$total_satoshis = 0;
		foreach ($recipients as $btc_address => $satoshi_amount) {
			$total_satoshis = bcadd($satoshi_amount, $total_satoshis);
		}
		return $total_satoshis;
	}

	private function convertSendManySatoshisToBtc(stdClass $recipients, $to_json_string = true)
	{
		foreach ($recipients as &$satoshi_amount) {
			$satoshi_amount = self::satoshiToBtc($satoshi_amount);
		}
		if ($to_json_string) {
			$recipients = json_encode($recipients);
		}
		return $recipients;
	}

	public function missingMethod($parameters = array())
	{
		throw new RuntimeException('Bro, error');
		//		return Response::json( ['error' => 'unknown method'] );
	}
}
