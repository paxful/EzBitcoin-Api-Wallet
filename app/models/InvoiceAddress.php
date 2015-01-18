<?php

class InvoiceAddress extends Eloquent {

	protected $table = 'invoice_addresses';

	protected $fillable = ['address', 'destination_address', 'label', 'invoice_amount', 'transaction_hash', 'input_transaction_hash', 'crypto_type_id',
		'user_id', 'callback_url', 'received', 'forward', 'received_amount'];

	public static function saveInvoiceAddress($data) {
		return self::create($data);
	}

	public static function getAddress($address) {
		return self::where('address', $address)->first();
	}

	public static function updateReceived($invoice_address_model, $total_received) {
		$invoice_address_model->received_amount = $total_received;
		$invoice_address_model->received = 1;
		$invoice_address_model->save();
	}

}
