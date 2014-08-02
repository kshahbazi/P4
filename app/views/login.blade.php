@extends('_master_P4')

@section('title')
Login
@stop

@section('content')

<h1>Login</h1>

{{ Form::open(array('url' => 'login')) }}

Username<br>
{{ Form::text('username') }}<br><br>

Password:<br>
{{ Form::password('password') }}<br><br>

{{ Form::submit('Submit') }}

{{ Form::close() }}

@stop