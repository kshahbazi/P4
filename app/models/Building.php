<?php

class Building extends Eloquent {

	public $timestamps = false;
	
	// overriding Eloquent's designation of 'id' as primary key
	protected $primaryKey = 'building_id';
	
	# Relationship method
	public function units() {
		# Building has many units
        return $this->hasMany('Unit');        
    }

	public function leases(){
	        return $this->hasManyThrough('Lease', 'Unit');
	}
	
	public static function search($query) {

		# If there is a query, search the library with that query
		if($query) 
		{
			# Eager load units
			$buildings = Building::with('units')
				->where('address', 'LIKE', "%$query%")
				->get();
		}

		return $buildings;	
	}
  
}