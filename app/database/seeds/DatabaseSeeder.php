<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('CryptoTypeTableSeeder');
		$this->command->info('CryptoType table seeded!');

		$this->call('UserTableSeeder');
		$this->command->info('User table seeded!');

		$this->call('BitcoinAddressesSeeder');
		$this->command->info('Bitcoin addresses seeded!');

		$this->call('ChangeAddressesSettingsSeeder');
		$this->command->info('Change addresses and settings seeded!');
	}

}
