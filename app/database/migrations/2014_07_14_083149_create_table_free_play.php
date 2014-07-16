<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFreePlay extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('freeplay', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('match_id');
            $table->integer('game_type_id');
            $table->integer('bookmaker_id');
            $table->integer('user_id');
            $table->decimal('bet');
            $table->decimal('odds');
            $table->decimal('bsf');
            $table->decimal('income');
            $table->string('team');
            $table->boolean('confirmed');

        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('freeplay');
	}

}
