@extends('_master_P4')

@section('title')
P4 - Building units
@stop

@section('content')
<a href='/'>View All Properties</a>

@if (isset($results))
    <div class='results'>
		{{ $results }}
	</div>
@endif


@stop