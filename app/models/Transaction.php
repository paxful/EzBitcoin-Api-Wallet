<?php

class Transaction extends Eloquent {

	protected $table = 'transactions';

	protected $fillable = ['tx_id', 'user_id', 'crypto_type_id', 'address_to', 'address_from', 'crypto_amount', 'crypto_type', 'confirmations', 'response_callback',
		'block_hash', 'block_index', 'block_time', 'tx_time', 'tx_timereceived', 'tx_category', 'balance', 'previous_balance', 'bitcoind_balance',
		'note', 'transaction_type'];

	public static function getTransactionByTxId($txId) {
		return self::where('tx_id', $txId);
	}

	public static function updateTxConfirmation($transactionModel, $confirms, $block_hash, $block_index, $block_index)
	{
		$transactionModel->confirmations = $confirms;
		$transactionModel->block_hash = $block_hash;
		$transactionModel->block_index = $block_index;
		$transactionModel->save();
	}

	public static function insertNewTransaction($data) {
		return self::create($data);
	}

	public static function updateTxOnAppResponse( $transaction_model, $app_response, $full_callback_url, $callback_status ) {
		$transaction_model->response_callback = $app_response;
		$transaction_model->callback_url = $full_callback_url;
		$transaction_model->callback_status = $callback_status;
		$transaction_model->save();
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
