<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePpmPlaceholder extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ppm_placeholder', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->integer('series_id');
            $table->decimal('bsf');
            $table->decimal('bet');
            $table->decimal('odds');
            $table->decimal('income');
            $table->string('match_id');
            $table->integer('game_type_id');
            $table->integer('bookmaker_id');
            $table->string('country');
            $table->boolean('confirmed');
            $table->boolean('active');
            $table->integer('current_length');
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
		Schema::drop('ppm_placeholder');
	}

}
