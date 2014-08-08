<!doctype html>
<html id="myDIV">
<head>

    <title>
	@yield('title','P4')
	</title>

    <meta charset='utf-8'>
    <link href='{{ asset('P4.css') }}' rel='stylesheet' type='text/css'>
	<!-- <link rel="stylesheet" href="styles/P4.css" type="text/css"> -->
	<link href='http://fonts.googleapis.com/css?family=Oswald:400' rel='stylesheet' type='text/css'>
	

@yield('head')
</head>

<body>

@if(Session::get('flash_message'))
<div class='flash-message'>{{ Session::get('flash_message') }}</div>
@endif


<div id="container">  
   		<div id="heading"><a href='/'>p4 - Rent Roll Stack</a>
			<div id="login">@if(Auth::check())
				<a href='/logout'>Log out {{ Auth::user()->user_name; }}</a>
				@else
				<a href='/signup'>Sign up</a> or <a href='/login'>Log in</a>
				@endif
			</div>
		</div>
   		<div id="mainContent">
			@yield('content')
	  	</div>

</div>

</body>
@yield('script')
</html>
