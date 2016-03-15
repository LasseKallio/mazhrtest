<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixUserExtras extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_extras', function($table)
		{
			$table->dropUnique('user_extras_key_unique');
			$table->unique(array('key', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_extras', function($table)
		{
			$table->dropUnique(array('user_extras_key_unique', 'user_extras_user_id_unique'));
			$table->unique('key');
		});
	}

}
