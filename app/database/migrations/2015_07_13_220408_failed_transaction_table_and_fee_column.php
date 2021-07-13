<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FailedTransactionTableAndFeeColumn extends Migration
{

	public function up()
	{
		Schema::create('transactions_failed', function ($table) {
			$table->bigIncrements('id');
			$table->string('tx_id', 100)->nullable()->index();
			$table->integer('user_id');
			// $table->foreign('user_id')->references('id')->on('users');
			$table->integer('crypto_type_id')->default(1);
			// $table->foreign('crypto_type_id')->references('id')->on('crypto_types');
			$table->string('address_to', 48)->nullable();
			$table->bigInteger('crypto_amount')->default(0);
			$table->integer('network_fee')->nullable();
			$table->text('error')->nullable();
			$table->text('user_note')->nullable();
			$table->boolean('sent_to_network')->default(false);
			$table->string('transaction_type', 25);
			$table->bigInteger('external_user_id')->nullable();
			$table->timestamps();
		});

		Schema::table('transactions', function ($table) {
			$table->integer('network_fee')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
