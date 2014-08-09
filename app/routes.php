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