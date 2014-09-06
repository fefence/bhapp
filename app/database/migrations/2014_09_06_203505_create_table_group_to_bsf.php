<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGroupToBsf extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('group_to_bsf', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->integer('groups_id');
            $table->integer('streak_bigger_than');
            $table->decimal('bsf');
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
		Schema::drop('group_to_bsf');
	}

}
