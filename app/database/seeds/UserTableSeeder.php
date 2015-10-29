<?php

class UserTableSeeder extends Seeder {

	public function run()
	{
		// not to delete in production just in case lol
		if ( App::environment('production') )
		{
			DB::table('users')->delete();
		}
		$user = User::create(['guid' => '7xDsRLyXEd1PgJ6Glrhs6d', 'email' => 'foo@bar.com', 'name' => 'John Doe', 'password' => 'strong_pass_plz',
		                      'secret' => 'secretsecret', 'callback_url' => 'https://url.to/send/on/btc/received',
		                      'rpc_connection' => 'http://user:pass@ipaddress:18332', 'blocknotify_callback_url' => 'https://url.to/send/on/btc/received']);

		$btcBalance = new Balance(['crypto_type_id' => '1']); // btc
		$btcBalance->user()->associate($user);
		$user->balances()->save($btcBalance);
	}

}