<?php

class ChangeAddress extends Eloquent {

	protected $table = 'change_addresses';

	protected $fillable = ['address', 'user_id', 'crypto_type_id'];

	public function cryptoType()
	{
		return $this->belongsTo('CryptoType');
	}
}