@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array('Home' => URL::to("home"), 'countries' => URL::to('countries'), array_get($data, 'country') => "/".array_get($data, 'country'));
		$active = array_get($data, 'league');
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => array_get($data, 'country'), 'small' => array_get($data, 'league')))
@stop

@section('content')
        @foreach(array_get($data, 'seasons') as $season)
    	<a href="{{$season->season}}/stats">{{ $season->season }}</a><br>
 	@endforeach
@stop
