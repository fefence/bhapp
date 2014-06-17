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
            <div class="alert alert-info">
                <p class="text-center">Legacy: {{$global->account}} €</p>
            </div>
        </div>
	</div>

<h6>PPS</h6>
<div class="row">
    <table class="table-bordered" style="margin-bottom: 20px;">
        <tr>
            <th style="width: 40%;text-align: center;">Country::League</th>
            <th style="width: 10%;text-align: center;">Profit</th>
            <th style="width: 10%;text-align: center;">Account State</th>
            <th style="width: 10%;text-align: center;">Pool</th>
            <th style="width: 10%;text-align: center;">Amount</th>
            <th style="width: 10%;text-align: center;">Action</th>
        </tr>
        @foreach($ppspools as $ppspool)
        <tr>
            <td><p class="text-center">{{$ppspool->country}} :: {{$ppspool->fullName}}</p></td>
            <td><p class="text-center">{{$global->profit}} €</p></td>
            <td><p class="text-center">{{$global->account}} €</p></td>
            <td><p class="text-center">{{$global->amount}} €</p></td>
            <td><input type="text"></td>
            <td><button class="btn btn-sm btn-primary getbtn" type="button">get</button><button class="btn btn-sm btn-warning insertbtn" type="button">insert</button></td>
        </tr>
        @endforeach
    </table>
</div>

    <h6>PPM</h6>
    <div class="row">
        <table class="table-bordered" style="margin-bottom: 20px;">
            <tr>
                <th style="width: 40%;text-align: center;">Country::League</th>
                <th style="width: 10%;text-align: center;">Profit</th>
                <th style="width: 10%;text-align: center;">Account State</th>
                <th style="width: 10%;text-align: center;">Pool</th>
                <th style="width: 10%;text-align: center;">Amount</th>
                <th style="width: 10%;text-align: center;">Action</th>
            </tr>
            @foreach($ppmpools as $ppmpool)
            <tr>
                <td><p class="text-center">{{$ppmpool->country}} :: {{$ppmpool->fullName}}</p></td>
                <td><p class="text-center">{{$ppmpool->profit}} €</p></td>
                <td><p class="text-center">{{$ppmpool->account}} €</p></td>
                <td><p class="text-center">{{$ppmpool->amount}} €</p></td>
                <td><input type="text"></td>
                <td><button class="btn btn-sm btn-primary getbtn" type="button">get</button><button class="btn btn-sm btn-warning insertbtn" type="button">insert</button></td>
            </tr>
            @endforeach
        </table>
    </div>

    <h6>Free Play</h6>
    <div class="row">
        <table class="table-bordered" style="margin-bottom: 20px;">
            <tr>
                <th style="width: 40%;text-align: center;">Country::League</th>
                <th style="width: 10%;text-align: center;">Profit</th>
                <th style="width: 10%;text-align: center;">Account State</th>
                <th style="width: 10%;text-align: center;">Pool</th>
                <th style="width: 10%;text-align: center;">Amount</th>
                <th style="width: 10%;text-align: center;">Action</th>
            </tr>
            @foreach($ppspools as $ppspool)
            <tr>
                <td><p class="text-center">{{$ppspool->country}} :: {{$ppspool->fullName}}</p></td>
                <td><p class="text-center">{{$global->profit}} €</p></td>
                <td><p class="text-center">{{$global->account}} €</p></td>
                <td><p class="text-center">{{$global->amount}} €</p></td>
                <td><input type="text"></td>
                <td><button class="btn btn-sm btn-primary getbtn" type="button">get</button><button class="btn btn-sm btn-warning insertbtn" type="button">insert</button></td>
            </tr>
            @endforeach
        </table>
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