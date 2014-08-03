<?php

class Portfolio {

	# Properties...
	public $path; # String
	public $buildings; # Array

	# Methods...

		# This __construct method gets called by default whenever an Object is instantiated from this Class
		public function __construct($path) {
			$this->set_path($path);
		}

		public function get_path() {	
			return $this->path;
		}

		public function set_path($new_path) {
			$this->path = $new_path;
		}

		public function get_buildings($refresh = false) {

		if($this->buildings && !$refresh) {
			return $this->buildings;
		}

	# Set the class param
	$this->buildings = $buildings;

	return $buildings;

}


/**
* @param String $query
* @return Array $results
*/
public function search($query) {

	# Get the buildings
	$buildings = $this->get_buildings();

		# If any buildings match our query, they'll get stored in this array
		$results = Array();

		# Loop through the buildings looking for matches
		foreach($buildings as $address => $building) 
		{
			# compare the query against the address
			if(stristr($address,$query)) 
			{
				# There's a match - add this building to the $results array
				$results[$address] = $building;
			}
		}
		
	return $results;
}
