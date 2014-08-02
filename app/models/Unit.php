<?php

class Unit extends Eloquent {

	public $timestamps = false;
	
	// overriding Eloquent's designation of 'id' as primary key
	protected $primaryKey = 'unit_id';
	
	# Relationship method
	public function building() {

		# Unit belongs to Building 
        return $this->belongsTo('Building');
        
    }

	# Relationship method
	public function lease() {

		# Unit has a Lease 
        return $this->hasOne('Lease');
        
    }
  
}
