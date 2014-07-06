@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	
	<?php
		$list = array('home' => URL::to("home"), 'countries' => URL::to('countries'));
		$active = 'draw stats';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Draw matches % ", 'small' => 'all leagues'))
@stop
@section('content')
    	@foreach($data as $country=>$league)
		<h4>{{ $country }}</h4>
    
    <table class="table table-bordered">
		<thead>
    		<th>league</th>
    		@for($i = 1; $i < 11; $i ++)
				<th>{{$i}}</th>
    		@endfor
    	</thead>
    	<tbody>
    		@foreach($league as $name=>$season)
    		<tr>
				<td>{{$name}}</td>
				@foreach($season as $n=>$s)
				<td>{{$n}} {{round($s[0]/$s[1] * 100, 2, PHP_ROUND_HALF_UP)}} %</td>
				@endforeach
			</tr>
			@endforeach
		</tbody>
    </table>
    		@endforeach


@stop
