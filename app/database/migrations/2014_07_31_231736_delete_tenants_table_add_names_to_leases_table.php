<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteTenantsTableAddNamesToLeasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		# severing the relationship
		# first drop the foreign keys
		Schema::table('leases', function($table) {
			$table->dropForeign('leases_tenant_id_foreign'); # table_fields_foreign
			$table->dropColumn('tenant_id'); 
		});
		
		Schema::drop('tenants');
		
		# now add new column for tenants' name, i.e. 'tenant'
		Schema::table('leases', function($table)
		{
		    $table->string('tenant');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		# go back to previous state
		# create Tenants table
		Schema::create('tenants', function($table) {

		$table->increments('tenant_id');
		$table->string('tenant_name');
		});
		
		# make the Leases table as it was
		Schema::table('leases', function($table)
		{
            # drop the tenants column from the Leases table
			$table->dropColumn('tenant');

			$table->integer('tenant_id')->unsigned(); # Important! FK has to be unsigned because the PK it will reference is auto-incrementing

			# add the tenant_id back to Leases table as foreign key
			# to get the tenant_name	
			$table->foreign('tenant_id')
				  ->references('tenant_id')
				  ->on('tenants')
				  ->onDelete('cascade');
        });
		
	}

}
