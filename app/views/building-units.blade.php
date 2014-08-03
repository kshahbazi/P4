@extends('_master_P4')

@section('title')
P4 - Building units
@stop

@section('content')


@if (isset($results))
    <div class='results'>
		{{ $results }}
	</div>
@endif


@stop