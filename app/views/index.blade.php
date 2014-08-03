@extends('_master_P4')

@section('title')
Welcome to P4-Buildings
@stop

@section('content')

<a href='/list-buildings'>View Properties</a>

<br><br>

	{{ Form::open(array('url' => '/list', 'method' => 'GET')) }}

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