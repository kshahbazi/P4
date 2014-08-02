@extends('_master_P4')

@section('title')
P4 - All properties
@stop

@section('content')


@if (isset($results))
    <div class='results'>
		{{ $results }}
	</div>
@endif


@stop