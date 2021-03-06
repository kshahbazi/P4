@extends('_master_P4')

@section('title')
Welcome to P4-Buildings
@stop

@section('content')


<br>
	@foreach($errors->all() as $message)
		<div class='error'>{{ $message }}</div>
	@endforeach

	{{ Form::open(array('url' => '/', 'method' => 'POST')) }}

		{{ Form::label('query','Search for a property:') }} &nbsp;
		{{ Form::text('query') }} &nbsp;
		{{ Form::submit('Search!') }}

	{{ Form::close() }}

@if (isset($results))
    <div class='results'>
		{{ $results }}
	</div>
@endif

@stop