<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserEducationNullableStart extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE `user_education` MODIFY `start_year` INTEGER UNSIGNED NULL;');
		DB::statement('ALTER TABLE `user_education` MODIFY `start_month` INTEGER UNSIGNED NULL;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE `user_education` MODIFY `start_year` INTEGER UNSIGNED;');
		DB::statement('ALTER TABLE `user_education` MODIFY `start_month` INTEGER UNSIGNED;');
	}

}
