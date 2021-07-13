<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeAddressesSettingsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('change_addresses', function($table)
		{
			$table->bigIncrements('id');
			$table->string('address', 48)->nullable();
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->timestamps();
		});

		Schema::create('settings', function($table) {
			$table->increments('id');
			$table->string('key', 128)->unique();
			$table->text('value');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('change_addresses');
		Schema::drop('settings');
	}

}
