<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
			$table->increments('id');
			$table->string('first');
			$table->string('last');
			$table->string('email')->unique();
			$table->string('password')->nullable();
			$table->text('linkedin_data')->nullable();
			$table->string('linkedin_id')->unique()->nullable();			
			$table->integer('user_status');
			$table->integer('activity_status');
			$table->string('remember_token')->nullable();
			$table->timestamps();
		});
		Schema::create('user_tests', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('instrument_id');
			$table->integer('language_id')->nullable();
			$table->boolean('paid')->default(0);
			$table->string('score_key')->nullable();
			$table->string('score_url')->nullable();
			$table->text('score')->nullable();
			$table->timestamps();
		});
		Schema::create('user_profiles', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('profile');
			$table->integer('score');
			$table->timestamps();
		});
		Schema::create('user_filters', function($table)
		{
		    $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		   	$table->string('type');
		   	$table->text('value');
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
		Schema::dropIfExists('user_filters');
		Schema::dropIfExists('user_tests');
		Schema::dropIfExists('user_profiles');
		Schema::dropIfExists('users');
	}

}
