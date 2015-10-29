<?php

class BitcoinAddressesSeeder extends Seeder {

	public function run()
	{
		// not to delete in production just in case lol
		if ( App::environment('production') )
		{
			DB::table('addresses')->delete();
		}

		Address::create(['address' => 'mtqSqRAGB7EuPtgCwvMtX74S2tvTapDWD6', 'user_id' => 1]);
		Address::create(['address' => 'n21cjTZa59QcMBXFvoKx2WoRotBV9mErnJ', 'user_id' => 1]);
		Address::create(['address' => 'mxKRETCDzCuLVLiw9MieJb8xFi1WhkQ9wY', 'user_id' => 1]);
	}

}