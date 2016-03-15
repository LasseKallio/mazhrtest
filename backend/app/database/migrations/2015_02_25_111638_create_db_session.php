<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbSession extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mazhr_sessions', function($table)
		{
		    $table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
		   	$table->string('key')->nullable();
		   	$table->text('value')->nullable();
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
		Schema::dropIfExists('mazhr_sessions');
	}

}
