<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPrivacyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_privacy', function($table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->boolean('public_profile')->default(0);
			$table->text('show')->nullable();
			$table->string('profile_token')->unique();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migration
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_privacy');
	}

}
