@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	<?php
		$list = array();
		$active = 'livescore';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => $big, 'small' => $small))
@stop

@section('content')
<table id="matches" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="league"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
<!--        <th><input type="hidden"></th>-->
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th style="width:60px;">date</th>
        <th style="width:60px;">time</th>
        <th style="width:100px;">league</th>
        <th>home</th>
        <th>away</th>
        <th style="width: 50px;">s</th>
        <th>bet 1x2</th>
<!--        <th>l</th>-->
        <th style="width: 40px;">res</th>
        <th style="width: 40px;">res</th>
    </tr>
    </thead>
    <tbody>
    @foreach($matches as $d)
    <tr id="{{$d['match']->id}}">
        @if(isset($d['game']))
        <td><span style="font-weight: bold">{{date('d M', strtotime($d['match']->matchDate))}}</span></td>
        <td><span style="font-weight: bold">{{substr($d['match']->matchTime, 0, strlen($d['match']->matchTime)-3)}}</span></td>
        @else
        <td><span>{{date('d M', strtotime($d['match']->matchDate))}}</span></td>
        <td><span>{{substr($d['match']->matchTime, 0, strlen($d['match']->matchTime)-3)}}</span></td>
        @endif
        <td><img src="/images/{{strtoupper($d['match']->country)}}.png">&nbsp;<a href= @if(isset($d['match']) && ($d['match']->ppm == 1)) "/ppm/country/{{$d['match']->country}}/{{$d['match']->matchDate}}/{{$d['match']->matchDate}}" @else "/pps/group/{{$d['match']->league_details_id}}" @endif>{{$d['match']->alias}}</a></td>
        <td><a href="/livescore/match/{{$d['match']->id}}">{{$d['match']->home}}</a></td>
        <td><a href="/livescore/match/{{$d['match']->id}}">{{$d['match']->away}}</a></td>
        <td>{{$d['streak']}}</td>
        @if(isset($d['game']))
        <td>{{$d['game']->bet}}<span>@</span>{{$d['game']->odds}}</td>
        @else
        <td></td>
        @endif
        <td @if($d['match']->resultShort == '-' && $d['match']->matchTime <= date('H:i:s', time()) && $d['match']->matchDate <= date('Y-m-d', time())) class="res" @endif>
            @if ($d['match']->resultShort != '-')
            {{$d['match']->homeGoals}}:{{$d['match']->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$d['match']->resultShort}}</td>

    </tr>
    @endforeach
    </tbody>
</table>
<script type="text/javascript">

    var asInitVals = new Array();

    $(document).ready(function () {

        var oTable = $("#matches").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sDom": '<"top"i>t<"bottom"><p"clear">',
            "sPaginationType": "full_numbers",
            "aaSorting": []
        });
        setInterval(function() {
            $("#matches tr .res").each(function() {
                var id =$(this).closest('tr').prop('id');
                var td = $(this);
                $.post( "/getres/" + id, function( data ) {
                    td.html(data);
                });
            })

        }, 30000);
        setInterval(function() {
            $("#matches tr .res span").each(function() {
                $(this).toggleClass('oddsColumn');
            })
        }, 2000);
    });
</script>

@stop