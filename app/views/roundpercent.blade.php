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
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Draw matches % per round", 'small' => '{{$country}} {{$league}}'))
@stop
@section('content')
    
    <table class="table table-bordered">
		@foreach($data as $season=>$rounds)
		<thead>
			<th>season</th>
			@foreach($rounds as $round=>$d)
			<th><?php echo explode('.', $round)[0]; ?></th>
			@endforeach
		</thead>
    	<tbody>
    		<tr>
    			<td>{{$season}}</td>
    			@foreach($rounds as $round=>$d)
				<td>{{$d[1]}}/{{$d[0]}}</td>
				@endforeach
    		</tr>
		</tbody>
		@endforeach
    </table>

@stop
