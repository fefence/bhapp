<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFreePlayTeams extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('freeplay_teams', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->string('team_id');
            $table->string('match_id');
            $table->string('league_details_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('freeplay_teams');
	}

}
