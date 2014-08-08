<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*Route::get('/', 'IndexController@getIndex');

Route::get('/signup', 'UserController@getSignup');
Route::get('/login', 'UserController@getLogin' );
Route::post('/signup', ['before' => 'csrf', 'uses' => 'UserController@postSignup'] );
Route::post('/login', ['before' => 'csrf', 'uses' => 'UserController@postLogin'] );
Route::get('/logout', ['before' => 'auth', 'uses' => 'UserController@getLogout'] );
*/

# the index, or first page, view
Route::get('/', function()
{
	$buildings =  Building::get(array('building_id','address','building_sf'));
	$building_list = "<p><table>";
	foreach($buildings as $building) {
		
		// square footage saved as int in db
		// format to show comma seperator
		$sf= number_format($building->building_sf);
		
		$building_list .=  "<tr><td><ul><li><a href='/building-units/".$building->building_id ."'><img class='buildingImages' src='/images/".
			$building->address.".jpg' alt='".$building->building_id ."'></a></li>";
		
        $building_list .=  "<li>".$building->address."</li>";
        $building_list .=  "<li>".$sf." SF"."</li></ul></td></tr>";
    }
	
	$building_list .= "</table></p>";
	
	return View::make('index')->with('results',$building_list);
});

# process the search on the index page
Route::post('/', array('before' => 'auth', function()
{
	$query = Input::get('query');
	$buildings;
	
	# Step 1) Define the rules
	$rules = array(
		'query' => 'required|min:1'	
	);	

	# Step 2)
	$validator = Validator::make(Input::all(), $rules);

	# Step 3
	if($validator->fails()) {
		return Redirect::to('/')
			->with('flash_message', 'No properties found; please enter new search.')
			->withInput()
			->withErrors($validator);
	}		
	
	
		# Eager load units
		$buildings = Building::with('units')
			->where('address', 'LIKE', "%$query%")
			->get();
	
	
		if(count($buildings) > 0)
		{
			
		$building_list = "<p><table>";
		foreach($buildings as $building) {
		
			// square footage saved as int in db
			// format to show comma seperator
			$sf= number_format($building->building_sf);
		
			$building_list .=  "<tr><td><ul><li><a href='/building-units/".
				$building->building_id ."'><img class='buildingImages' src='/images/".
				$building->address.".jpg' alt='".$building->building_id ."'></a></li>";
		
	        $building_list .=  "<li>".$building->address."</li>";
	        $building_list .=  "<li>".$sf." SF"."</li></ul></td></tr>";
	    }
	
		$building_list .= "</table></p>";
	
		return View::make('/building-units')->with('results',$building_list);
	}
	#
	#
	# need to correct this
	// if query empty return to index page
	else
	{
			return Redirect::to('/')->with('flash_message', 'No matches found.');;
	}
}));

######################################
// the login page, view
Route::get('login', array('before' => 'guest', function()
{
	return View::make('login');
}));

Route::post('login', array('before' => 'csrf', function() {

            $credentials = Input::only('user_name', 'password');

            if (Auth::attempt($credentials, $remember = true)) {
                return Redirect::intended('/')->with('flash_message', 'Welcome Back!');
            }
            else {
                return Redirect::to('/login')->with('flash_message', 'Log in failed; please try again.');
            }

            return Redirect::to('login');
        }
    )
);
	
Route::get('signup', array('before' => 'guest', function() {
	return View::make('signup');
}));

Route::post('signup', array('before' => 'csrf', function() {
	
	# Step 1) Define the rules
	$rules = array(
		'user_name' => 'required|min:3',
		'email' => 'required|email|unique:users,email',
		'password' => 'required|min:6'	
	);	

	# Step 2)
	$validator = Validator::make(Input::all(), $rules);

	# Step 3
	if($validator->fails()) {
		return Redirect::to('/signup')
			->with('flash_message', 'Sign up failed; please fix the errors listed below.')
			->withInput()
			->withErrors($validator);
	}
	
	// create an instance of User
	$user = new User;
	
	// get form inputs
	$user->user_name = Input::get('user_name');
	$user->email = Input::get('email');
	$user->password = Hash::make(Input::get('password'));
	
	try {
		$user->save();
	}
	catch (Exception $e) {
		return Redirect::to('signup')
		->with('flash_message', 'Sign up failed; please try again.')
		->withInput();
	}

	# Log in
	Auth::login($user);

	return Redirect::to('/building-units')->with('flash_message', 'Welcome!');

}));
	
Route::get('/logout', function() {

	# Log out
	Auth::logout();

	# Send them to the homepage
	return Redirect::to('/');

});

######################################

Route::get('/building-units/{id?}', array('before' => 'auth',function($id = '1') {

    $buildings = Building::where('building_id', '=', $id)->first();
	
	#get all the units for this building
	$units = Unit::whereBuilding_id($id)->get();
	
	$building_units = "<h2>".$buildings->address."</h2>
					   <h4>Square footage ".number_format($buildings->building_sf)."</h4>
						<img class='buildingImages' src='/images/".
							$buildings->address.".jpg' alt='".$buildings->building_id ."'>.
					   <p><table id='building_units'>";
	
	# building has units assigned
	if(!$units->isEmpty())
	{
		foreach($units as $unit) 
		{
		
		# with all the units we filtered by passing the building id above
		# we can now get each lease that belongs to a unit within that building
		$lease = Lease::whereUnit_id($unit->unit_id)->first();
		
		# now query the rent table by accessing the lease id
		# so we can get each lease's rent_amount, begin rent and end rent details
		$rent = Rent::whereLease_id($lease->lease_id)->first();
		
		$building_units .= '<tr class="unitRows">
							<td>Unit '.$unit->unit_number.'</td>
							<td>'.$unit->unit_sf.'sf</td>
							<td>'.$lease->tenant.'</td>
							<td>$'.$rent->rent_amount.'</td>
							<td>'.$rent->end_rent.'</td></tr>';
		}					
	 }
	# building has no units assigned, i.e. isEmpty();
	else
    {
		$building_units .= " is empty";
	}
	                  
	$building_units .= "</table></p>";
	
	return View::make('/building-units')->with('results',$building_units);
	
}));

Route::get('/get-environment',function() {

    echo "Environment: ".App::environment();

});

Route::get('/seed-portfolio',function() {

  # Clear the tables to a blank slate
  DB::statement('SET FOREIGN_KEY_CHECKS=0'); # Disable FK constraints so that all rows can be deleted, even if there's an associated FK
  DB::statement('TRUNCATE buildings');
  DB::statement('TRUNCATE units');
  DB::statement('TRUNCATE leases');
  DB::statement('TRUNCATE rents');
  
  #begin seeding 1st building
  $building1              = new Building;
  $building1->address     = '63 Stanhoper St, Boston MA';
  $building1->type        = 'Office';
  $building1->building_sf = '91000';
  $building1->save();
  
  # Associate has to be called *before* the unit is created (save())
  # Equivalent of $unit->building_id = $building1->building_id
  
  # Associate has to be called *before* the lease is created (save())
  # Equivalent of $lease->unit_id = $unit1->unit_id etc...
  
  # Associate has to be called *before* the rent is created (save())
  # Equivalent of $rent->lease_id = $lease1->lease_id etc...
  
  $unit1              = new Unit;
  $unit1->unit_number = 101;
  $unit1->unit_sf     = 1200;
  $unit1->occupied    = 'occupied';
  $unit1->building()->associate($building1);
  $unit1->save();
  
  $unit2              = new Unit;
  $unit2->unit_number = 102;
  $unit2->unit_sf     = 1800;
  $unit2->occupied    = 'occupied';
  $unit2->building()->associate($building1);
  $unit2->save();
  
  $unit3              = new Unit;
  $unit3->unit_number = 103;
  $unit3->unit_sf     = 1000;
  $unit3->occupied    = 'occupied';
  $unit3->building()->associate($building1);
  $unit3->save();
  
  $unit4              = new Unit;
  $unit4->unit_number = 201;
  $unit4->unit_sf     = 1000;
  $unit4->occupied    = 'occupied';
  $unit4->building()->associate($building1);
  $unit4->save();
  
  $unit5              = new Unit;
  $unit5->unit_number = 202;
  $unit5->unit_sf     = 1000;
  $unit5->occupied    = 'occupied';
  $unit5->building()->associate($building1);
  $unit5->save();
  
  $unit6              = new Unit;
  $unit6->unit_number = 203;
  $unit6->unit_sf     = 1000;
  $unit6->occupied    = 'occupied';
  $unit6->building()->associate($building1);
  $unit6->save();
  
  $unit7              = new Unit;
  $unit7->unit_number = 204;
  $unit7->unit_sf     = 1000;
  $unit7->occupied    = 'occupied';
  $unit7->building()->associate($building1);
  $unit7->save();
  
  $unit8              = new Unit;
  $unit8->unit_number = 300;
  $unit8->unit_sf     = 3000;
  $unit8->occupied    = 'occupied';
  $unit8->building()->associate($building1);
  $unit8->save();
  
  $lease1         = new Lease;
  $lease1->tenant = 'Zachariah & Zachariah, LLP';
  $lease1->unit()->associate($unit1);
  $lease1->save();
  
  $lease2         = new Lease;
  $lease2->tenant = 'Ivax Corp.';
  $lease2->unit()->associate($unit2);
  $lease2->save();
  
  $lease3         = new Lease;
  $lease3->tenant = 'Stanley Rental';
  $lease3->unit()->associate($unit3);
  $lease3->save();
  
  $lease4         = new Lease;
  $lease4->tenant = 'Quintilium';
  $lease4->unit()->associate($unit4);
  $lease4->save();
  
  $lease5         = new Lease;
  $lease5->tenant = 'Pilgrims Pride';
  $lease5->unit()->associate($unit5);
  $lease5->save();
  
  $lease6         = new Lease;
  $lease6->tenant = 'A.G. Edwards Inc.';
  $lease6->unit()->associate($unit6);
  $lease6->save();
  
  $lease7         = new Lease;
  $lease7->tenant = 'First Data Corp.';
  $lease7->unit()->associate($unit7);
  $lease7->save();
  
  $lease8         = new Lease;
  $lease8->tenant = 'Praxomide';
  $lease8->unit()->associate($unit8);
  $lease8->save();
  
  $rent1              = new Rent;
  $rent1->rent_amount = 18;
  $rent1->begin_rent  = '2010-01-01';
  $rent1->end_rent    = '2012-12-31';
  $rent1->lease()->associate($lease1);
  $rent1->save();
  
  $rent2              = new Rent;
  $rent2->rent_amount = 25.5;
  $rent2->begin_rent  = '2009-05-01';
  $rent2->end_rent    = '2014-10-31';
  $rent2->lease()->associate($lease2);
  $rent2->save();
  
  $rent3              = new Rent;
  $rent3->rent_amount = 22.50;
  $rent3->begin_rent  = '2013-12-03';
  $rent3->end_rent    = '2018-12-02';
  $rent3->lease()->associate($lease3);
  $rent3->save();
  
  $rent4              = new Rent;
  $rent4->rent_amount = 28;
  $rent4->begin_rent  = '2010-09-01';
  $rent4->end_rent    = '2014-08-31';
  $rent4->lease()->associate($lease4);
  $rent4->save();
  
  $rent5              = new Rent;
  $rent5->rent_amount = 16.5;
  $rent5->begin_rent  = '2014-05-01';
  $rent5->end_rent    = '2016-04-30';
  $rent5->lease()->associate($lease5);
  $rent5->save();
  
  $rent6              = new Rent;
  $rent6->rent_amount = 21.75;
  $rent6->begin_rent  = '2012-06-06';
  $rent6->end_rent    = '2022-06-05';
  $rent6->lease()->associate($lease6);
  $rent6->save();
  
  $rent7              = new Rent;
  $rent7->rent_amount = 16.5;
  $rent7->begin_rent  = '2014-05-01';
  $rent7->end_rent    = '2016-04-30';
  $rent7->lease()->associate($lease7);
  $rent7->save();
  
  $rent8              = new Rent;
  $rent8->rent_amount = 21.75;
  $rent8->begin_rent  = '2012-06-06';
  $rent8->end_rent    = '2022-06-05';
  $rent8->lease()->associate($lease8);
  $rent8->save();
  
  #begin seeding 2nd building
  $building2              = new Building;
  $building2->address     = '6 Harcourt Lane, Brookline MA';
  $building2->type        = 'Office';
  $building2->building_sf = '51500';
  $building2->save();
  
  # Associate has to be called *before* the unit is created (save())
  # Equivalent of $unit->building_id = $building1->building_id

  # Associate has to be called *before* the lease is created (save())
  # Equivalent of $lease->unit_id = $unit1->unit_id etc...
  
  # Associate has to be called *before* the rent is created (save())
  # Equivalent of $rent->lease_id = $lease1->lease_id etc...

  $unit9              = new Unit;
  $unit9->unit_number = 100;
  $unit9->unit_sf     = 750;
  $unit9->occupied    = 'occupied';
  $unit9->building()->associate($building2);
  $unit9->save();
  
  $unit10              = new Unit;
  $unit10->unit_number = 111;
  $unit10->unit_sf     = 5000;
  $unit10->occupied    = 'occupied';
  $unit10->building()->associate($building2);
  $unit10->save();
  
  
  $unit11              = new Unit;
  $unit11->unit_number = 115;
  $unit11->unit_sf     = 2000;
  $unit11->occupied    = 'occupied';
  $unit11->building()->associate($building2);
  $unit11->save();
  
  $unit12              = new Unit;
  $unit12->unit_number = 201;
  $unit12->unit_sf     = 1750;
  $unit12->occupied    = 'occupied';
  $unit12->building()->associate($building2);
  $unit12->save();
  
  $unit13              = new Unit;
  $unit13->unit_number = 202;
  $unit13->unit_sf     = 1000;
  $unit13->occupied    = 'occupied';
  $unit13->building()->associate($building2);
  $unit13->save();
  
  $unit14              = new Unit;
  $unit14->unit_number = 203;
  $unit14->unit_sf     = 3500;
  $unit14->occupied    = 'occupied';
  $unit14->building()->associate($building2);
  $unit14->save();
  
  $unit15              = new Unit;
  $unit15->unit_number = 215;
  $unit15->unit_sf     = 1500;
  $unit15->occupied    = 'occupied';
  $unit15->building()->associate($building2);
  $unit15->save();
  
  $unit16              = new Unit;
  $unit16->unit_number = 300;
  $unit16->unit_sf     = 7750;
  $unit16->occupied    = 'occupied';
  $unit16->building()->associate($building2);
  $unit16->save();
  
  $unit17              = new Unit;
  $unit17->unit_number = 400;
  $unit17->unit_sf     = 3750;
  $unit17->occupied    = 'occupied';
  $unit17->building()->associate($building2);
  $unit17->save();
  
  $unit18              = new Unit;
  $unit18->unit_number = 450;
  $unit18->unit_sf     = 4000;
  $unit18->occupied    = 'occupied';
  $unit18->building()->associate($building2);
  $unit18->save();
  
  $lease9         = new Lease;
  $lease9->tenant = 'LSAT Helpers';
  $lease9->unit()->associate($unit9);

  $lease9->save();
  
  $lease10         = new Lease;
  $lease10->tenant = 'Movifone';
  $lease10->unit()->associate($unit10);
  $lease10->save();
  
  $lease11         = new Lease;
  $lease11->tenant = 'Reachout Brother';
  $lease11->unit()->associate($unit11);
  $lease11->save();
  
  $lease12         = new Lease;
  $lease12->tenant = 'Kilpatrick Corp.';
  $lease12->unit()->associate($unit12);
  $lease12->save();
  
  $lease13         = new Lease;
  $lease13->tenant = 'Albert, Stanley & Howard';
  $lease13->unit()->associate($unit13);
  $lease13->save();
  
  $lease14         = new Lease;
  $lease14->tenant = 'Newburgh Pediatrics';
  $lease14->unit()->associate($unit14);
  $lease14->save();
  
  $lease15         = new Lease;
  $lease15->tenant = 'Prideloni & Macaroni';
  $lease15->unit()->associate($unit15);
  $lease15->save();
  
  $lease16         = new Lease;
  $lease16->tenant = 'Inc. Magazine';
  $lease16->unit()->associate($unit16);
  $lease16->save();
  
  $lease17         = new Lease;
  $lease17->tenant = 'Youbiz';
  $lease17->unit()->associate($unit17);
  $lease17->save();
  
  $lease18         = new Lease;
  $lease18->tenant = 'Baxter International Inc. ';
  $lease18->unit()->associate($unit18);
  $lease18->save();
  
  
  $rent9              = new Rent;
  $rent9->rent_amount = 18;
  $rent9->begin_rent  = '2010-01-01';
  $rent9->end_rent    = '2012-12-31';
  $rent9->lease()->associate($lease9);
  $rent9->save();
  
  $rent10              = new Rent;
  $rent10->rent_amount = 25.5;
  $rent10->begin_rent  = '2006-01-01';
  $rent10->end_rent    = '2015-10-31';
  $rent10->lease()->associate($lease10);
  $rent10->save();
  
  $rent11              = new Rent;
  $rent11->rent_amount = 22.50;
  $rent11->begin_rent  = '2010-10-01';
  $rent11->end_rent    = '2016-11-02';
  $rent11->lease()->associate($lease11);
  $rent11->save();
  
  $rent12              = new Rent;
  $rent12->rent_amount = 28;
  $rent12->begin_rent  = '2013-09-01';
  $rent12->end_rent    = '2015-08-31';
  $rent12->lease()->associate($lease12);
  $rent12->save();
  
  $rent13              = new Rent;
  $rent13->rent_amount = '16.50';
  $rent13->begin_rent  = '2014-05-01';
  $rent13->end_rent    = '2016-04-30';
  $rent13->lease()->associate($lease13);
  $rent13->save();
  
  $rent14              = new Rent;
  $rent14->rent_amount = '21.75';
  $rent14->begin_rent  = '2012-06-06';
  $rent14->end_rent    = '2022-06-05';
  $rent14->lease()->associate($lease14);
  $rent14->save();
  
  $rent15              = new Rent;
  $rent15->rent_amount = 16.5;
  $rent15->begin_rent  = '2014-05-01';
  $rent15->end_rent    = '2016-04-30';
  $rent15->lease()->associate($lease15);
  $rent15->save();
  
  $rent16              = new Rent;
  $rent16->rent_amount = '21.25';
  $rent16->begin_rent  = '2012-06-06';
  $rent16->end_rent    = '2022-06-05';
  $rent16->lease()->associate($lease16);
  $rent16->save();
  
  $rent17              = new Rent;
  $rent17->rent_amount = '16.5';
  $rent17->begin_rent  = '2014-05-01';
  $rent17->end_rent    = '2016-04-30';
  $rent17->lease()->associate($lease17);
  $rent17->save();
  
  $rent18              = new Rent;
  $rent18->rent_amount = '21.25';
  $rent18->begin_rent  = '2012-06-06';
  $rent18->end_rent    = '2022-06-05';
  $rent18->lease()->associate($lease18);
  $rent18->save();
  
  
  ################################################
  
  #seed other buildings
  $building3              = new Building;
  $building3->address     = '3 Serendipity Rd, Waltham MA';
  $building3->type        = 'Office';
  $building3->building_sf = '201000';
  $building3->save();
  
  $building14              = new Building;
  $building14->address     = '1001 Mass Ave, Boston MA';
  $building14->type        = 'Office';
  $building14->building_sf = '18000';
  $building14->save();
  
  $building5              = new Building;
  $building5->address     = '335 Upton Junction, Southborough MA';
  $building5->type        = 'Office';
  $building5->building_sf = '165000';
  $building5->save();
  
  $building6              = new Building;
  $building6->address     = '11 Daniel Webster Highway, Nashua NH';
  $building6->type        = 'Office';
  $building6->building_sf = '41000';
  $building6->save();
  
  $building7              = new Building;
  $building7->address     = '100 Old Mill Road, Salem NH';
  $building7->type        = 'Office';
  $building7->building_sf = '113750';
  $building7->save();
  
  $building8              = new Building;
  $building8->address     = '200 Memorial Dr, Cambridge MA';
  $building8->type        = 'Office';
  $building8->building_sf = '11000';
  $building8->save();
  
  $building9              = new Building;
  $building9->address     = '6973 Greenwich Rd, Stamford CT';
  $building9->type        = 'Office';
  $building9->building_sf = '107000';
  $building9->save();
  
  $building10              = new Building;
  $building10->address     = '55 Washington St, Dedham MA';
  $building10->type        = 'Office';
  $building10->building_sf = '65000';
  $building10->save();
  
  
  $building11              = new Building;
  $building11->address     = '141 Morehead Dr, Westborough MA';
  $building11->type        = 'Office';
  $building11->building_sf = '181000';
  $building11->save();
  
  $building12              = new Building;
  $building12->address     = '25 Dearbourne Rd, Danvers MA';
  $building12->type        = 'Office';
  $building12->building_sf = '23750';
  $building12->save();
  
  $building13              = new Building;
  $building13->address     = '33 Henshaw Drive, Stoughton MA';
  $building13->type        = 'Office';
  $building13->building_sf = '37000';
  $building13->save();
  
  $building14              = new Building;
  $building14->address     = '111 Washington St, Boston MA';
  $building14->type        = 'Office';
  $building14->building_sf = '92000';
  $building14->save();
  echo "Buildings seeded";

});
