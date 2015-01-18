<?php

class Address extends Eloquent {

	protected $table = 'addresses';

	protected $fillable = array('address', 'label', 'balance', 'total_received', 'previous_balance', 'crypto_type_id', 'user_id');

	public static function getAddress($address) {
		return self::where('address', $address)->first(); // TODO get with user ::with('user')
	}

	public static function getAddressForUser($address, $user_id) {
		return self::where('address', $address)->where('user_id', $user_id)->first();
	}

	public static function insertNewAddress( $data ) {
		return self::create($data);
	}

	public static function updateBalance($address_model, $received_amount) {
		$address_total_received = bcadd( $address_model->total_received, $received_amount);
		$new_balance            = bcadd($address_model->balance, $received_amount);
		$address_model->total_received = $address_total_received;
		$address_model->balance = $new_balance;
		$address_model->save();
	}

	public function user()
	{
		return $this->belongsTo('User');
	}
}
