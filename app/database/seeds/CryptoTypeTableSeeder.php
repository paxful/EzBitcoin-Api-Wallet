<?php

class CryptoTypeTableSeeder extends Seeder {

	public function run()
	{
		// not to delete in production just in case lol
		if ( App::environment('production') )
		{
			DB::table('crypto_types')->delete();
		}

		CryptoType::create(['crypto_type' => 'BTC']);
	}

}