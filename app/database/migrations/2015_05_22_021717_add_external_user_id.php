<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalUserId extends Migration {

	public function up()
	{
		Schema::table('transactions', function($table) {
			$table->bigInteger('external_user_id')->nullable();
		});
		Schema::table('payout_history', function($table) {
			$table->bigInteger('external_user_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transactions', function($table) {
			$table->dropColumn('external_user_id');
		});
		Schema::table('payout_history', function($table) {
			$table->dropColumn('external_user_id');
		});
	}
}
