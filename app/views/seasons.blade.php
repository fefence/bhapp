@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array('Home' => URL::to("home"), 'countries' => URL::to('countries'), $country => "/$country");
		$active = $league;
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => $country, 'small' => $league))
@stop

@section('content')
        @foreach($seasons as $season)
    	<a href="{{$season->season}}/stats">{{ $season->season }}</a><br>
 	@endforeach
@stop
