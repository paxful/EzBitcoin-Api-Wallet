<?php

class PayoutHistory extends Eloquent {

	protected $table = 'payout_history';

	protected $fillable = array('crypto_amount', 'crypto_type_id', 'user_id', 'tx_id', 'address_to', 'confirmations');

	public static function insertNewTransaction($data) {
		return self::create($data);
	}

	public static function getByTxId($tx_id, $lock_for_update = false) {
		if ($lock_for_update) {
			return self::where('tx_id', $tx_id)->lockForUpdate()->first();
		}
		return self::where('tx_id', $tx_id)->first();
	}

	public static function updateTxConfirmation($payoutModel, $confirms)
	{
		$payoutModel->confirmations = $confirms;
		$payoutModel->save();
		return $payoutModel;
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
