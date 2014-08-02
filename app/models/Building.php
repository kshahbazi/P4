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

	public function leases()
	    {
	        return $this->hasManyThrough('Lease', 'Unit');
	    }
	
  
}