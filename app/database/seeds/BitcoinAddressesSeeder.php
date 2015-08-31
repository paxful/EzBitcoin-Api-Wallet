<?php

class BitcoinAddressesSeeder extends Seeder {

	public function run()
	{
		DB::table('addresses')->delete();

		Address::create(['address' => 'mtqSqRAGB7EuPtgCwvMtX74S2tvTapDWD6', 'user_id' => 1]);
		Address::create(['address' => 'n21cjTZa59QcMBXFvoKx2WoRotBV9mErnJ', 'user_id' => 1]);
		Address::create(['address' => 'mxKRETCDzCuLVLiw9MieJb8xFi1WhkQ9wY', 'user_id' => 1]);
	}

}