<?php

class Transaction extends Eloquent {

	protected $table = 'transactions';

	protected $fillable = ['tx_id', 'user_id', 'crypto_type_id', 'address_to', 'address_from', 'crypto_amount', 'confirmations', 'response_callback',
		'block_hash', 'block_index', 'block_time', 'tx_time', 'tx_timereceived', 'tx_category', 'balance', 'previous_balance', 'bitcoind_balance',
		'note', 'transaction_type', 'user_balance', 'external_user_id', 'network_fee'];

	public static function getTransactionByTxId($txId) {
		return self::where('tx_id', $txId)->first();
	}

	public static function getTransactionByTxIdAndAddress($txId, $address) {
		return self::where('tx_id', $txId)->where('address_to', $address)->first();
	}

	public static function updateTxConfirmation($transactionModel, $data)
	{
		$transactionModel->confirmations = $data['confirmations'];
		$transactionModel->block_hash = $data['block_hash'];
		$transactionModel->block_index = $data['block_index'];
		$transactionModel->save();
		return $transactionModel;
	}

	public static function insertNewTransaction($data) {
		return self::create($data);
	}

	public static function updateTxOnAppResponse( $transaction_model, $app_response, $full_callback_url, $callback_status, $external_user_id = null ) {
		$transaction_model->response_callback = $app_response;
		$transaction_model->callback_url = $full_callback_url;
		$transaction_model->callback_status = $callback_status;
		$transaction_model->external_user_id = $external_user_id;
		$transaction_model->save();
		return $transaction_model;
	}

	public function user()
	{
		return $this->belongsTo('User');
	}

	public function cryptoType()
	{
		return $this->belongsTo('CryptoType');
	}

}
