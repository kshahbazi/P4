<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::create('users', function($table) {
				// Primary, Auto-Incrementing field.
				$table->increments('user_id');
				$table->string('user_name');
				$table->string('email')->unique();
				$table->string('password');
				$table->boolean('remember_token');
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
	
		Schema::drop('users');
	}

}
