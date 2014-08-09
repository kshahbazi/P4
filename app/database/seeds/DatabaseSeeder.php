<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		
		# Portfolio already seeded
		// $this->call('PortfolioSeeder');
	}
	

}
