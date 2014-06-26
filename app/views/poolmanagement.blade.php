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
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Pool management"))
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
            <td><p class="text-center">{{$ppspool->profit}} €</p></td>
            <td><p class="text-center">{{$ppspool->account}} €</p></td>
            <td><p class="text-center">{{$ppspool->amount}} €</p></td>
            <td class="f">
                <form method="post">
                    <input type="text" class="transitvalue" name="amount">
                    <input type="hidden" name="id" value="{{$ppspool->id}}">
                </form>
            </td>
            <td><button class="btn btn-sm btn-warning insertbtn" type="button">+</button><button class="btn btn-sm btn-primary getbtn" type="button">-</button></td>
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
                <td class="f">
                    <form method="post">
                        <input type="text" class="transitvalue" name="amount">
                        <input type="hidden" name="id" value="{{$ppmpool->id}}">
                    </form>
                </td>
                <td><button class="btn btn-sm btn-warning insertbtn" type="button">+</button><button class="btn btn-sm btn-primary getbtn" type="button">-</button></td>
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
<!--           TODO free play stuff-->
        </table>
    </div>

	<script type="text/javascript">
		$(".getbtn").on('click', function(){
			var form = $(this).parent().siblings(".f").children("form");
			form.attr('action', 'pools/get');
			form.submit();
		});
		$(".insertbtn").on('click', function(){
			var form = $(this).parent().siblings(".f").children("form");
			form.attr('action', 'pools/insert');
			form.submit();
		});
	</script>
@stop