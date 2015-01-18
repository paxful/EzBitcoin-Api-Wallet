<?php

class CryptoTypeTableSeeder extends Seeder {

	public function run()
	{
		DB::table('crypto_types')->delete();

		CryptoType::create(['crypto_type' => 'BTC']);
	}

}