<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExampleAds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('example_ads', function($table)
		{
		    $table->increments('id');
		   	$table->string('uuid')->unique();
			$table->string('source');
		    $table->string('title');
		    $table->string('area');
		    $table->text('json_ad')->nullable();
		    $table->dateTime('published');
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
		//Schema::dropIfExists('example_ads');
	}

}
