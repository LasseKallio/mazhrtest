<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTags extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_work_history', function($table)
		{
		    $table->text('tags')->nullable();
		});
		Schema::table('user_education', function($table)
		{
		    $table->text('tags')->nullable();
		});
		Schema::create('tags', function($table)
		{
		    $table->increments('id');
		    $table->string('tag');
		    $table->integer('status')->default(1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_work_history', function($table)
		{
		    $table->dropColumn('tags');
		});
		Schema::table('user_education', function($table)
		{
		    $table->dropColumn('tags');
		});	
		Schema::dropIfExists('tags');
	}

}
