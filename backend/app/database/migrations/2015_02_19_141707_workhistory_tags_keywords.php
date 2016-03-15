<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WorkhistoryTagsKeywords extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_work_history', function($table)
		{
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
		Schema::table('user_workhistory', function($table)
		{
			$table->renameColumn('keywords', 'tags');
		});
	}

}
