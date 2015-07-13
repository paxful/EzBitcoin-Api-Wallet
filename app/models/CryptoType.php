<?php

class CryptoType extends Eloquent {

	protected $table = 'crypto_types';

	protected $fillable = array('crypto_type');

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

	public function balances()
	{
		return $this->hasMany('Balance');
	}

}
