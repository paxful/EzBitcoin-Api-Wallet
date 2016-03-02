<?php

class User extends Eloquent {

	protected $table = 'users';

	protected $fillable = array(
		'guid',
		'email',
		'name',
		'callback_url',
		'users_callback_url',
		'rpc_connection',
		'ignore_balance'
	);
	protected $hidden = array(
		'password',
		'secret'
	);

	public static function getUserByGuid($guid) {
		return self::where('guid', $guid)->first();
	}

	public function balances() {
		return $this->hasMany('Balance');
	}

	public function addresses()
	{
		return $this->hasMany('Address');
	}

	public function transactions()
	{
		return $this->hasMany('Transaction');
	}

	public function failedTransactions()
	{
		return $this->hasMany('TransactionFailed');
	}

	public function payoutHistories()
	{
		return $this->hasMany('PayoutHistory');
	}

	public function cryptoTypes()
	{
		return $this->hasMany('CryptoType');
	}

}
