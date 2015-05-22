<?php

class Balance extends Eloquent {

	protected $table = 'balances';

	protected $fillable = array('crypto_type_id', 'balance', 'total_received', 'num_transactions');

	public static function getBalance($user_id, $crypto_type_id = 1) {
		return self::where('user_id', $user_id)->where('crypto_type_id', $crypto_type_id)->lockForUpdate()->first();
	}

	public static function setNewUserBalance($user_balance, $new_balance, $total_received = null) {
		$user_balance->balance = $new_balance;
		if ($total_received) {
			$user_balance->total_received = $total_received;
		}
		$user_balance->num_transactions = bcadd($user_balance->num_transactions, 1);
		$user_balance->save();
		return $user_balance;
	}

	public static function updateUserBalance($user, $amount_to_add, $crypto_type_id = 1) {
		$balance = self::getBalance($user->id, $crypto_type_id);
		$new_balance = bcadd($balance->balance, $amount_to_add);
		$balance->balance = $new_balance;
		$balance->total_received = bcadd($balance->total_recieved, $amount_to_add);
		$balance->num_transactions = bcadd($balance->num_transactions, 1);
		$balance->save();
		return $balance;
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
