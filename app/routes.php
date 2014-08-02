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

Route::get('/', 'IndexController@getIndex');

Route::get('/signup', 'UserController@getSignup');
Route::get('/login', 'UserController@getLogin' );
Route::post('/signup', ['before' => 'csrf', 'uses' => 'UserController@postSignup'] );
Route::post('/login', ['before' => 'csrf', 'uses' => 'UserController@postLogin'] );
Route::get('/logout', ['before' => 'auth', 'uses' => 'UserController@getLogout'] );


# the index, or first page, view
Route::get('/', function()
{
	return View::make('index');
});

/*
######################################
// the login page, view
Route::get('login', function()
{
	return View::make('login');
});

	
Route::get('signup', array('before' => 'guest', function() {
	return View::make('signup');
}));

Route::post('signup', array('before' => 'csrf', function() {
	
	// create an instance of User
	$user = new User;
	
	// get form inputs
	$user->user_name = Input::get('username');
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

	return Redirect::to('signup')->with('flash_message', 'Welcome!');

}));
	
Route::get('/logout', function() {

	# Log out
	Auth::logout();

	# Send them to the homepage
	return Redirect::to('/');

});
*/

######################################

Route::get('/list-buildings', function() {

   	$buildings =  Building::get(array('building_id','address','building_sf'));
	$building_list = "<p><table>";
	foreach($buildings as $building) {
		
		// square footage saved as int in db
		// format to show comma seperator
		$sf= number_format($building->building_sf);
		
		$building_list .=  "<tr><td><a href='/building-units/".$building->building_id ."'><img class='buildingImages' src='/images/".
			$building->address.".jpg' alt='".$building->building_id ."'></a></td>";
		
        $building_list .=  "<td>".$building->address."</td>";
        $building_list .=  "<td>".$sf." SF"."</td></tr>";
    }
	
	$building_list .= "</table></p>";
	
	// append the generated text to the ligenerator page, within results	
	return View::make('index')->with('results',$building_list);
	
});

Route::get('/building-units/{id?}', function($id = '2') {

    $buildings = Building::where('building_id', '=', $id)->first();
	$units = Unit::whereBuilding_id($id)->get();
	$total_sf = 0;
	
	$building_units = "<p><h2>".$buildings->address."</h2><table>";
	
	# building has units assigned
	if(!$units->isEmpty())
	{
		foreach($units as $unit) {
		
		$leases = Lease::whereUnit_id($unit->unit_number)->get();
		/*foreach($leases as $lease){
			alert("tenants ");
			echo $leases->tenant.'<br>';
		} */
		
        $building_units .= '<tr><td>Unit '.$unit->unit_number.'</td><td>'.$unit->unit_sf.'SF'.'</td></tr>';  
      }
	}
	# building has no units assigned
	else
    {
		$building_units .= " is empty";
	}
	                  
	//echo "Total square footage at ".$buildings->address." : ".$total_sf;
	$building_units .= "</table></p>";
	
	return View::make('/building-units')->with('results',$building_units);
	
	/*$units = Unit::whereHas('lease', function($q)
	/{
		$q->where('building_id','like',$buildings->building_id);
	})->get();*/
	
});

Route::get('/building-unit', function()
{
	
	foreach (Unit::with('building')->get() as $unit)
	{
	    echo $unit->building->building_sf.'<br>';
	}
	
});

Route::get('/unit-tenants', function()
{
	
	foreach (Lease::with('unit')->get() as $lease)
	{
	    //echo $unit->unit_sf.'sf '.unit_number;
		echo $lease->leases->tenant.'<br>';
		$phone = User::find(1)->phone;
	}
	
});

Route::get('/add-user', function() {

    # Instantiate a new Book model class
    //$user = new User();

    # Set 
    /*$user->user_name = 'Seaver';
    $user->email = 'steve-o@hotmail.com';
    $user->password = 'steveo';
    
    # This is where the Eloquent ORM magic happens
    $user->save();

    return 'A new user has been added! Check the database to see...';*/

});



Route::get('/db-get', function() {

    # The all() method will fetch all the rows from a Model/table
    $users = User::all();

    # Typically we'd pass $books to a View, but for quick and dirty demonstration, let's just output here...
    foreach($users as $user) {
        echo $user->email.'<br>';
    }

});

Route::get('/db-delete', function() {

    # First get a user to delete
    $user = User::where('user_name', 'LIKE', '%Seaver%')->first();
    $user->delete();

    return "Deleted user; check the database to see if it worked...";

});

Route::get('/debug', function() {

    echo '<pre>';

    echo '<h1>environment.php</h1>';
    $path   = base_path().'/environment.php';

    try {
        $contents = 'Contents: '.File::getRequire($path);
        $exists = 'Yes';
    }
    catch (Exception $e) {
        $exists = 'No. Defaulting to `production`';
        $contents = '';
    }

    echo "Checking for: ".$path.'<br>';
    echo 'Exists: '.$exists.'<br>';
    echo $contents;
    echo '<br>';

    echo '<h1>Environment</h1>';
    echo App::environment().'</h1>';

    echo '<h1>Debugging?</h1>';
    if(Config::get('app.debug')) echo "Yes"; else echo "No";

    echo '<h1>Database Config</h1>';
    print_r(Config::get('database.connections.mysql'));

    echo '<h1>Test Database Connection</h1>';
    try {
        $results = DB::select('SHOW DATABASES;');
        echo '<strong style="background-color:green; padding:5px;">Connection confirmed</strong>';
        echo "<br><br>Your Databases:<br><br>";
        print_r($results);
    } 
    catch (Exception $e) {
        echo '<strong style="background-color:crimson; padding:5px;">Caught exception: ', $e->getMessage(), "</strong>\n";
    }

    echo '</pre>';

});
