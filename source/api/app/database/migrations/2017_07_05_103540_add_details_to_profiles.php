<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDetailsToProfiles extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('users', function(Blueprint $table)
    {
      $table->text('competence_points')->nullable();
      $table->text('behaviour_points')->nullable();
      $table->text('motivation_points')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function(Blueprint $table)
    {
      $table->dropColumn('competence_points');
      $table->dropColumn('behaviour_points');
      $table->dropColumn('motivation_points');
    });
  }

}
