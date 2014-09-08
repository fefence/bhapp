@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	
	<?php
		$list = array('pps' => URL::to("home"), 'countries' => URL::to('countries'));
		$active = $country;
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Archive", 'small' => $country))
@stop
@section('content')
    @foreach($leagues as $league)
    	<a href="{{ URL::route('archive', array('country' => $league->country, 'league' => $league->fullName)) }}">{{ $league->fullName }}</a><br>
 	@endforeach
@stop
