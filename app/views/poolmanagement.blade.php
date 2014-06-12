@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	<?php
		$list = array('Home' => URL::to("home"));
		$active = 'Pool Management';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => "Pool management"))
@stop

@section('content')
	<div class="row">
	    <div class="col-xs-3">
			<div class="alert alert-warning">
				<p class="text-center">In Transit: {{$global->in_transit}} €</p>
			</div>
	    </div>
	    <div class="col-xs-3">
			<div class="alert alert-danger">
				<p class="text-center">Profit: {{$global->profit}} €</p>
			</div>
	    </div>
	    <div class="col-xs-3">
			<div class="alert alert-success">
				<p class="text-center">Account State: {{$global->account}} €</p>
			</div>
	    </div>
	    <div class="col-xs-3">
			<div class="alert alert-warning">
				<p class="text-center">Pool: {{$global->amount}} €</p>
			</div>
	    </div>
	</div>

	<!-- PPS -->
	@foreach($ppspools as $ppspool)
	<div class="row">
		<div class="col-xs-12">
			<div class="input-group">
			  <input type="text" value="{{$ppspool->country}} :: {{$ppspool->fullName}}" class="form-control">
			  <span class="input-group-addon">Profit: {{$ppspool->profit}} €</span>
			  <span class="input-group-addon">Account State: {{$ppspool->account}} €</span>
			  <span class="input-group-addon">Pool: {{$ppspool->amount}} €</span>
			  <form id="bobo" method="post">
		      	<input type="text" class="form-control transitvalue" name="amount">
		      	<input type="hidden" name="league" value="{{$ppspool->league_details_id}}">
		      </form>		      
		      <span class="input-group-btn">
		        <button class="btn btn-primary getbtn" type="button">get</button>
		        <button class="btn btn-warning insertbtn" type="button">insert</button>
		      </span>
		    </div>
		</div>
	</div>
	@endforeach
	
	<div class="row">
		<div class="col-xs-12">
			<div class="input-group">
			  <input type="text" value="PPS :: TOTAL" class="form-control">
			  <span class="input-group-addon">Profit: {{$ppstotal->profit}} €</span>
			  <span class="input-group-addon">Account State: {{$ppstotal->account}} €</span>
			  <span class="input-group-addon">Pool: {{$ppstotal->amount}} €</span>
		      <input type="text" class="form-control" disabled>
		      <span class="input-group-btn">
		        <button class="btn btn-primary" type="button" disabled>get</button>
		        <button class="btn btn-warning" type="button" disabled>insert</button>
		      </span>
		    </div>
		</div>
	</div>

	<hr>
	
	<!-- PPM -->
	@foreach($ppmpools as $ppmpool)
	<div class="row">
		<div class="col-xs-12">
			<div class="input-group">
			  <input type="text" value="{{$ppmpool->country}} :: {{$ppmpool->fullName}}" class="form-control">
			  <span class="input-group-addon">Profit: {{$ppmpool->profit}} €</span>
			  <span class="input-group-addon">Account State: {{$ppmpool->account}} €</span>
			  <span class="input-group-addon">Pool: {{$ppmpool->amount}} €</span>
		      <form method="post">
		      	<input type="text" class="form-control transitvalue" name="amount">
		      	<input type="hidden" name="league" value="{{$ppmpool->league_details_id}}">
		      </form>
		      <span class="input-group-btn">
		        <button class="btn btn-primary getbtn" type="button">get</button>
		        <button class="btn btn-warning insertbtn" type="button">insert</button>
		      </span>
		    </div>
		</div>
	</div>
	@endforeach
	
	<div class="row">
		<div class="col-xs-12">
			<div class="input-group">
			  <input type="text" value="PPM :: TOTAL" class="form-control">
			  <span class="input-group-addon">Profit: {{$ppmtotal->profit}} €</span>
			  <span class="input-group-addon">Account State: {{$ppmtotal->account}} €</span>
			  <span class="input-group-addon">Pool: {{$ppmtotal->amount}} €</span>
		      <input type="text" class="form-control" disabled>
		      <span class="input-group-btn">
		        <button class="btn btn-primary" type="button" disabled>get</button>
		        <button class="btn btn-warning" type="button" disabled>insert</button>
		      </span>
		    </div>
		</div>
	</div>

	<hr>
	
	<!-- FREE PLAY -->
	<div class="row">
		<div class="col-xs-12">
			<div class="input-group">
			  <input type="text" value="Country :: League" class="form-control">
			  <span class="input-group-addon">Profit: 3430 €</span>
			  <span class="input-group-addon">Account State: 3430 €</span>
			  <span class="input-group-addon">Pool: 1150 €</span>
		      <input type="text" class="form-control">
		      <span class="input-group-btn">
		        <button class="btn btn-primary" type="button">get</button>
		        <button class="btn btn-warning" type="button">insert</button>
		      </span>
		    </div>
		</div>
	</div>
	<script type="text/javascript">
		$(".getbtn").on('click', function(){
			var form = $(this).parent().siblings("form");
			form.attr('action', 'pools/get');
			form.submit();
		});
		$(".insertbtn").on('click', function(){
			var form = $(this).parent().siblings("form");
			form.attr('action', 'pools/insert');
			form.submit();
		});
	</script>
@stop