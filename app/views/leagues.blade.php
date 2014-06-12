@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	
	<?php
		$list = array('Home' => URL::to("home"), 'countries' => URL::to('countries'));
		$active = array_get($data, 'country');
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => "Archive", 'small' => array_get($data, 'country')))
@stop
@section('content')
    @foreach(array_get($data, 'leagues') as $league)
    	<a href="{{ URL::route('archive', array('country' => $league->country, 'league' => $league->fullName)) }}">{{ $league->fullName }}</a><br>
 	@endforeach
@stop
