@extends('layout')

@section('content')
<table class="table-bordered noMargin noPadding matchDetails">
	<tr>
		<th style="width: 26%;">{{$hometeam}} last 10 matches</th>
		<th style="width: 26%;">{{$awayteam}} last 10 matches</th>
		<th style="width: 26%;">head 2 head</th>
		<th style="width: 22%;">betting history</th>
	</tr>
	<tr>
		<td>
			<table class="noMargin noPadding">
				@foreach($home as $h)
				<tr>
					<?php
	                    $d = array('team' => $hometeam, 'match' => $h);
	                  ?>
	                
					<td>@include('layouts.partials.square', array('data' => $d)) {{$h->home}} - {{$h->away}} <a href="#">{{$h->homeGoals}}:{{$h->awayGoals}}</a> {{$h->matchDate}}</td>
				</tr>
				@endforeach
			</table>
		</td>
		<td>
			<table class="noMargin noPadding">
				@foreach($away as $h)
				<tr>
					<?php
	                    $d = array('team' => $awayteam, 'match' => $h);
	                  ?>
					<td>@include('layouts.partials.square', array('data' => $d)) {{$h->home}} - {{$h->away}} <a href="#">{{$h->homeGoals}}:{{$h->awayGoals}}</a> {{$h->matchDate}}</td>
				</tr>
				@endforeach
			</table>
		</td>
		<td>
			<table class="noMargin noPadding">
				@foreach($h2h as $h)
				<tr>
					<?php
	                    $d = array('team' => $awayteam, 'match' => $h);
	                  ?>
					<td>@include('layouts.partials.square', array('data' => $d)) {{$h->home}} - {{$h->away}} <a href="#">{{$h->homeGoals}}:{{$h->awayGoals}}</a> {{$h->matchDate}}</td>
				</tr>
				@endforeach
			</table>
		</td>
		<td>
			<table class="noMargin noPadding">
				@foreach($data as $game)
					<tr>
						<td>{{$game->bookmakerName}} {{$game->type}} bsf: {{$game->bsf}} {{$game->bet}} @ {{$game->odds}} {{$game->income}} <a href="/delete/{{$game->id}}/{{$game->game_type_id}}">-</a> </td>
					</tr>
				@endforeach
			</table>
		</td>
	</tr>
</table>
@stop