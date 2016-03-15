<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentSumDefault extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("ALTER TABLE `payment_log` CHANGE COLUMN `sum` `sum` decimal(10,2) NOT NULL DEFAULT '0';");
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE `scores` CHANGE COLUMN `sum` `sum` decimal(10,2) NOT NULL;");

	}

}
