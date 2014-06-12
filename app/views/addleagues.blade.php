@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	
	<?php
		$list = array('Home' => URL::to("home"));
		$active = 'add';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Add PPS leagues"))
@stop
@section('content')
   <!--  {{Form::open(array('url' => '/saveleaguestoplay', 'method' => 'post'))}}
    	{{Form::submit()}}<br>

    @foreach($leagues as $league)
    	{{Form::label($league->id, $league->country."/".$league->fullName)}}
    	@if(array_key_exists($league->id, $toPlay))
    		{{Form::checkbox("ids[]", $league->id, ['checked'])}} 
    	@else
    		{{Form::checkbox("ids[]", $league->id)}} 
    	@endif
    	@if(array_key_exists($league->id, $toPlay))
    		{{Form::text($league->id, $toPlay[$league->id])}} 
    	@else
    		{{Form::text("", "")}}
    	@endif
    	 <br>
    @endforeach

    {{Form::close()}} -->
    <form method="post" action='/saveleaguestoplay'>
    	<input type="submit">
    	@foreach($leagues as $league)
    		{{$league->country."/".$league->fullName}}
    	@if(array_key_exists($league->id, $toPlay))
    		<input type="checkbox" name="ids[]" checked value="{{$league->id}}"> 
    	@else
    		<input type="checkbox" name="ids[]" value="{{$league->id}}"> 
    	@endif
    	@if(array_key_exists($league->id, $toPlay))
    		<input type="text" name="v-{{$league->id}}" id="{{$league->id}}" value="{{$toPlay[$league->id]}}">
    	@else
    		<input type="text" name="v-{{$league->id}}" id="{{$league->id}}">
    	@endif
    	 <br>
    @endforeach
    </form>
@stop
