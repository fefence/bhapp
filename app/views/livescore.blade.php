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
    <tr class="{{$d['match']->match_id}}">
        <td>{{date('d M', strtotime($d['match']->matchDate))}}</td>
        <td>{{substr($d['match']->matchTime, 0, strlen($d['match']->matchTime)-3)}}</td>
        <td><img src="/images/{{strtoupper($d['match']->country)}}.png">&nbsp;<a href= @if(isset($d['game']) && ($d['game']->game_type_id > 4)) "/ppm/country/{{$d['match']->country}}/" @else "/group/{{$d['match']->league_details_id}}" @endif>{{$d['match']->alias}}</a></td>
        <td><a href="/livescorematch/{{$d['match']->id}}">{{$d['match']->home}}</a></td>
        <td><a href="/livescorematch/{{$d['match']->id}}">{{$d['match']->away}}</a></td>
        <td>{{$d['streak']}}</td>
        @if(isset($d['game']))
        <td>{{$d['game']->bet}}<span>@</span>{{$d['game']->odds}}</td>
<!--        <td>{{$d['game']->current_length}}</td>-->
        @else
        <td></td>
        <td></td>
        @endif
        <td>
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
    // $('#get_from_pool').on("click", function(){
    // 	var a = $('#amount').val();
    // 	$.post("/pools/get",
    //            {
    //                // "_token": $( this ).find( 'input[name=_token]' ).val(),
    //                "league": $(this).parent().parent().attr("id"),
    //                "game": $(this).parent().attr("id"),
    //            },
    //            function( data ) {
    //                alert(data)
    //                //do something with data/response returned by server
    //            },
    //            'json'
    //        );
    // });

    var asInitVals = new Array();

    $(document).ready(function () {

        var oTable = $("#matches").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sDom": '<"top"i>t<"bottom"><p"clear">',
            "sPaginationType": "full_numbers",
            "aoColumnDefs": [
                { 'bSortable': false, 'aTargets': [ 0 ] }
            ],
            "order": [[ 1, "asc" ]]
        });
    });
</script>

@stop