<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGames extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('games', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');
			$table->integer('standings_id');
			$table->decimal('bet');
			$table->decimal('odds');
			$table->decimal('bsf');
			$table->decimal('income');
			$table->string('match_id');
			$table->integer('game_type_id')->default(1);
			$table->integer('bookmaker_id')->default(1);
			$table->boolean('special');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('games');
	}

}
