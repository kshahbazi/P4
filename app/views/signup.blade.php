@extends('_master_P4')

@section('title')
Sign up
@stop

@section('content')

<h1>Sign up</h1>

	{{ Form::open(array('url' => 'signup')) }}

		Username<br>
		{{ Form::text('user_name') }}<br><br>

		Email<br>
		{{ Form::text('email') }}<br><br>

		Password:<br>
		{{ Form::password('password') }}<br><br>

		{{ Form::submit('Submit') }}

	{{ Form::close() }}

@stop