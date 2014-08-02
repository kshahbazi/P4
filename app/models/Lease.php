<?php

class Lease extends Eloquent {

	public $timestamps = false;
	
	// overriding Eloquent's designation of 'id' as primary key
	protected $primaryKey = 'lease_id';
	
	# Relationship method
	public function unit() {

		# Lease belongs to Unit 
        return $this->belongsTo('Unit');
        
    }

	# Relationship method
	public function rent() {

		# Unit has a Lease 
        return $this->hasMany('Rent');
        
    }
  
}
