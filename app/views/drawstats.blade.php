@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	
	<?php
		$list = array('Home' => URL::to("home"), 'countries' => URL::to('countries'));
		$active = 'draw stats';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Draw % series", 'small' => $country))
@stop
@section('content')
    
    <table class="table table-bordered">
    	<thead>
    		<th>season</th>
    		<th>all(100%)</th>
    		@for($i = 1; $i < 16; $i ++)
				<th>{{$i}}</th>
    		@endfor
    		<th>15+</th>
    	</thead>
    	<tbody>
    	@foreach($data as $season=>$series)
			<tr>
				<td>{{$season}}</td>
				<td>{{$series[17]}}</td>
				@for($i = 1; $i < 17; $i ++)
					<td>{{$series[$i]}}</td>
				@endfor
			</tr>
			<tr>
				<td></td>
				<td>100%</td>
				@for($i = 1; $i < 17; $i ++)
					<td>{{round($series[$i]/$series[17]*100, 2, PHP_ROUND_HALF_UP)}} %</td>
				@endfor
			</tr>
		@endforeach
		</tbody>
    </table>

@stop
