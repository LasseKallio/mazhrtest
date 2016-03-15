<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExampleUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('example_users', function($table)
		{
			$table->increments('id');
			$table->string('avatar')->nullable();
			$table->string('firstname')->nullable();;
			$table->string('lastname')->nullable();;
			$table->string('current')->nullable();
			$table->string('authtoken')->nullable();
			$table->text('progress')->nullable();
			$table->text('skills')->nullable();
			$table->text('experience')->nullable();
			$table->text('education')->nullable();
			$table->text('filters')->nullable();
			$table->string('remember_token')->nullable();
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
		Schema::dropIfExists('example_users');
	}

}
