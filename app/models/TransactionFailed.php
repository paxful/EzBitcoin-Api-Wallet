<?php

class TransactionFailed extends Eloquent {

	protected $table = 'transactions_failed';

	protected $fillable = ['tx_id', 'user_id', 'crypto_type_id', 'address_to', 'crypto_amount', 'network_fee', 'error', 'user_note', 'sent_to_network',
		'transaction_type', 'external_user_id'];

	public static function insertTransaction(array $data)
	{
		return self::create($data);
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
