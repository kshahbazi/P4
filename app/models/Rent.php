<?php

class Rent extends Eloquent {

	public $timestamps = false;
	
	// overriding Eloquent's designation of 'id' as primary key
	protected $primaryKey = 'rent_id';
	
	# Relationship method
	public function lease() {

		# Unit belongs to Building 
        return $this->belongsTo('Lease');
        
    }
  
}
