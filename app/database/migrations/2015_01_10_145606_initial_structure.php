<?php

use Illuminate\Database\Migrations\Migration;

class InitialStructure extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crypto_types', function ($table) {
			$table->bigIncrements('id');
			$table->string('crypto_type', 12);
			$table->timestamps();
		});

		Schema::create('users', function ($table) {
			$table->increments('id');
			$table->string('guid')->unique();
			$table->string('password', 64);
			$table->string('email', 45);
			$table->string('name');
			$table->string('secret');
			$table->text('callback_url');
			$table->text('blocknotify_callback_url');
			$table->text('rpc_connection');
			$table->timestamps();
		});

		Schema::create('transactions', function ($table) {
			$table->bigIncrements('id');
			$table->string('tx_id', 100)->index();
			$table->bigInteger('user_id')->unsigned();
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->string('address_to', 48)->nullable();
			$table->string('address_from', 48)->nullable();
			$table->bigInteger('crypto_amount')->default(0);
			$table->integer('confirmations')->default(0);
			$table->text('response_callback')->nullable();
			$table->boolean('callback_status')->default(false);
			$table->text('callback_url')->nullable();
			$table->text('block_hash')->nullable();
			$table->integer('block_index')->nullable();
			$table->integer('block_time')->nullable();
			$table->integer('tx_time')->default(0);
			$table->integer('tx_timereceived')->default(0);
			$table->string('tx_category', 24)->nullable();
			$table->string('address_account', 100)->nullable();
			$table->bigInteger('balance')->default(0);
			$table->bigInteger('previous_balance')->nullable();
			$table->decimal('bitcoind_balance')->nullable();
			$table->text('note')->nullable();
			$table->string('transaction_type', 25);
			$table->timestamps();


			// $table->foreign('user_id')->references('id')->on('users');
		});

		Schema::create('payout_history', function ($table) {
			$table->bigIncrements('id');
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->bigInteger('crypto_amount')->default(0);
			$table->string('tx_id', 100)->nullable();
			$table->string('address_to', 48)->nullable();
			$table->integer('confirmations')->default(0);
			$table->timestamps();
		});

		Schema::create('balances', function ($table) {
			$table->increments('id');
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->bigInteger('balance')->default(0);
			$table->bigInteger('total_received')->default(0);
			$table->timestamps();
		});

		Schema::create('addresses', function ($table) {
			$table->bigIncrements('id');
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->string('address', 48)->nullable()->unique()->index();
			$table->text('label')->nullable();
			$table->bigInteger('balance')->default(0);
			$table->bigInteger('total_received')->default(0);
			$table->bigInteger('previous_balance')->default(0);
			$table->timestamps();
		});

		Schema::create('invoice_addresses', function ($table) {
			$table->bigIncrements('id');
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->string('address', 48)->nullable()->unique()->index();
			$table->string('destination_address', 48)->nullable();
			$table->text('label')->nullable();
			$table->bigInteger('invoice_amount')->default(0);
			$table->text('callback_url')->nullable();
			$table->boolean('received')->default(false);
			$table->boolean('forward')->default(false);
			$table->bigInteger('received_amount')->default(0);
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
		Schema::drop('users');
		Schema::drop('transactions');
		Schema::drop('payout_history');
		Schema::drop('balances');
		Schema::drop('addresses');
		Schema::drop('invoice_addresses');
		Schema::drop('crypto_types');
	}
}
