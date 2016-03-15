<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendedEducation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_education', function($table)
		{
			$table->string('level');
			$table->renameColumn('tags', 'keywords');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_education', function($table)
		{
			$table->dropColumn('level');
			$table->renameColumn('keywords', 'tags');
		});
	}

}
