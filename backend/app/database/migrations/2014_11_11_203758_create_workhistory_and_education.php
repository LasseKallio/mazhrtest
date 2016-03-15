<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkhistoryAndEducation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_work_history', function($table)
		{
		    $table->increments('id');
		    $table->integer('linkedin_id')->nullable();
		    $table->integer('user_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		    $table->integer('start_month')->unsigned()->nullable();
		    $table->integer('start_year')->unsigned()->nullable();
		    $table->integer('end_month')->unsigned()->nullable();
		    $table->integer('end_year')->unsigned()->nullable();		    
		   	$table->string('title');
		   	$table->string('company');
		   	$table->boolean('current');
		    $table->timestamps();
		});
		
		Schema::create('user_education', function($table)
		{
		    $table->increments('id');
		    $table->integer('user_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		    $table->integer('start_month')->unsigned();
		    $table->integer('start_year')->unsigned();
		    $table->integer('end_month')->unsigned()->nullable();
		    $table->integer('end_year')->unsigned()->nullable();		    
		   	$table->string('degree');
		   	$table->string('school');
		   	$table->boolean('current');
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
		Schema::dropIfExists('user_work_history');
		Schema::dropIfExists('user_education');
	}

}
