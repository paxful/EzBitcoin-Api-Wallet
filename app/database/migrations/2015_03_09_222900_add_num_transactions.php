<?php

use Illuminate\Database\Migrations\Migration;

class AddNumTransactions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::table('balances', function($table)
		{
			$table->integer('num_transactions')->default(0);
		});

		Schema::table('transactions', function($table) {
			$table->bigInteger('user_balance')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('balances', function($table)
		{
			$table->dropColumn('num_transactions');
		});
		Schema::table('transactions', function($table)
		{
			$table->dropColumn('user_balance');
		});
	}

}