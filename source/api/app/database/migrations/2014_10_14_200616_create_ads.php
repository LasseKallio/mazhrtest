<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		Schema::create('ads', function($table)
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
		Schema::create('ad_profession_codes', function($table)
		{
		    $table->increments('id');
		    $table->integer('ad_id')->unsigned();
		    $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');;
		   	$table->text('code');
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
		Schema::dropIfExists('ad_profession_codes');
		Schema::dropIfExists('ads');
	}

}
