<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupToStreaks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('group_to_streaks', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('groups_id');
            $table->integer('streak_length');
            $table->integer('streak_count');
//			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('group_to_streaks');
	}

}
