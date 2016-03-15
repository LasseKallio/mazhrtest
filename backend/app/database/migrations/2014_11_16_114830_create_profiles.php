<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfiles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profiles', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('code');
			$table->timestamps();
		});

		Schema::create('profile_professioncodes', function($table)
		{
			$table->increments('id');
			$table->string('code');
			$table->integer('profile_id')->unsigned();
			$table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
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
		Schema::dropIfExists('profile_professioncodes');
		Schema::dropIfExists('profiles');
	}

}
