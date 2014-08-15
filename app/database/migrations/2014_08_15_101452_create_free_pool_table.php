<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreePoolTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('free_pool', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('team_id');
            $table->integer('user_id');
            $table->decimal('amount');
            $table->decimal('profit');
            $table->decimal('account_state');
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
		Schema::drop('free_pool');
	}

}
