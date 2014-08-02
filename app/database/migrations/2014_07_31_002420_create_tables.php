<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration {


	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		# Create the buildings table
		Schema::create('buildings', function($table) {

		# AI, PK
		# would still need to explicitly declare PK in the building model file
		$table->increments('building_id');

		# General data...
		$table->string('address');
		$table->string('type');
		$table->integer('building_sf');
		$table->string('photo');
		
		# no foreign keys needed

		});

		# Create the units table
		Schema::create('units', function($table) {

		# AI, PK
		# would still need to explicitly declare PK in the unit model file
		$table->increments('unit_id');

		# General data...
		$table->integer('building_id')->unsigned(); # Important! FK has to be unsigned because the PK it will reference is auto-incrementing
		$table->integer('unit_number');
		$table->integer('unit_sf');
		
		# Set the occupany of units to default to vacant
		$table->enum('occupied', array('occupied', 'vacant'))
			  ->default('vacant');
		

		# Define foreign keys...
		$table->foreign('building_id')
			  ->references('building_id')
			  ->on('buildings')
			  ->onDelete('cascade');

		});
		
		# Create the tenants table
		Schema::create('tenants', function($table) {

		# AI, PK
		# would still need to explicitly declare PK in the Tenant model file
		# since Eloquent assumes that each table has a primary key column named id. 
		$table->increments('tenant_id');

		# General data...
		$table->string('tenant_name');
		
		# No foreign keys...
		
		});
		
		
		# Create the leases table
		Schema::create('leases', function($table) {

		# AI, PK
		# would still need to explicitly declare PK in the Lease model file
		# since Eloquent assumes that each table has a primary key column named id. 
		$table->increments('lease_id');

		# General data...
		$table->integer('unit_id')->unsigned(); # Important! FK has to be unsigned because the PK it will reference is auto-incrementing
		$table->integer('tenant_id')->unsigned(); # Important! FK has to be unsigned because the PK it will reference is auto-incrementing
		
		# Define foreign keys...
		# to relate to the units table
		$table->foreign('unit_id')
			  ->references('unit_id')
			  ->on('units')
			  ->onDelete('cascade');
		
		# to get the tenant_name	
		$table->foreign('tenant_id')
			  ->references('tenant_id')
			  ->on('tenants')
			  ->onDelete('cascade');

		});
		
		
		# Create the rents table
		Schema::create('rents', function($table) {

		# AI, PK
		# would still need to explicitly declare PK in the Rent model file
		# since Eloquent assumes that each table has a primary key column named id. 
		$table->increments('rent_id');

		# General data...
		$table->integer('lease_id')->unsigned(); # Important! FK has to be unsigned because the PK it will reference is auto-incrementing
		$table->integer('rent_amount');
		$table->date('begin_rent');
		$table->date('end_rent');
	
		# Define foreign keys...
		# to relate to the units table
		$table->foreign('lease_id')
			  ->references('lease_id')
			  ->on('leases')
			  ->onDelete('cascade');
		
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
		# reverse my steps by severing the relationship
		# first drop the foreign keys
		Schema::table('leases', function($table) {
			$table->dropForeign('leases_unit_id_foreign');
			$table->dropForeign('leases_tenant_id_foreign'); # table_fields_foreign
		});
		
		Schema::table('rents', function($table) {
			$table->dropForeign('rents_lease_id_foreign'); # table_fields_foreign
		});
		
		Schema::table('units', function($table) {
			$table->dropForeign('units_building_id_foreign'); # table_fields_foreign
		});
		
		# now we can drop the tables
		Schema::drop('buildings');
		Schema::drop('units');
		Schema::drop('leases');
		Schema::drop('tenants');
		Schema::drop('rents');	
		
	}	

}
