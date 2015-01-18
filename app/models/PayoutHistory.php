<?php

class PayoutHistory extends Eloquent {

	protected $table = 'payout_history';

	protected $fillable = array('crypto_amount', 'crypto_type_id', 'user_id', 'tx_id', 'address_to', 'confirmations');

	public static function insertNewTransaction($data) {
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
