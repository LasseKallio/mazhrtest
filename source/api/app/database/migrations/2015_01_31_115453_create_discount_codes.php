<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountCodes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('discount_codes', function($table)
		{
			$table->increments('id');
			$table->integer('test_id')->unsigned();
			$table->foreign('test_id')->references('id')->on('tests');
			$table->string('code')->unique();
			$table->text('description')->nullable();		
			$table->integer('status');
			$table->timestamp('start')->nullable();
			$table->timestamp('end')->nullable();
			$table->integer('usage_limit')->nullable();
			$table->decimal('price', 10, 2);
			$table->timestamps();
		});
		Schema::table('user_tests', function($table)
		{
			$table->integer('discount_code_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_tests', function($table)
		{
			$table->dropColumn('discount_code_id');
		});
		Schema::dropIfExists('discount_codes');
	}

}
