@extends('_master_P4')

@section('title')
P4 - All propertied
@stop

@section('content')


@if(trim($query) != ""))
<p>You searched for <strong>{{{ $query }}}</strong></p>

@endif


@stop