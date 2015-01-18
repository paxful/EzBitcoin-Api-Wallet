<?php

class Balance extends Eloquent {

	protected $table = 'balances';

	protected $fillable = array('crypto_type_id', 'balance', 'total_received');

	public static function getBalance($user_id, $crypto_type_id) {
		return self::where('user_id', $user_id)->where('crypto_type_id', $crypto_type_id)->first();
	}

	public static function updateUserBalance($user_balance, $new_balance, $total_received = null) {
		$user_balance->balance = $new_balance;
		if ($total_received) {
			$user_balance->total_received = $total_received;
		}
		$user_balance->save();
		return $user_balance;
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
