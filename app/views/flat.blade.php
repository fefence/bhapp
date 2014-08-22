@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array('ppm' => URL::to("home"));
		$active = 'flat';
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
        <th><input type="hidden"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <td></td>
        <th style="width:60px;">date</th>
        <th style="width:50px;">time</th>
        <th>home</th>
        <th>away</th>
        <th>1x2</th>
        <th>0-0</th>
        <th>1-1</th>
        <th>2-2</th>
        <th></th>
        <th>res</th>
        <th>res</th>
    </tr>
    </thead>
    <tbody>
    @foreach($matches as $key => $d)
    <tr>
        <td><img src="/images/{{strtoupper($d['match']->country)}}.png"></td>
        <td>{{date('d M', strtotime($d['match']->matchDate))}}</td>
        <td>{{substr($d['match']->matchTime, 0, strlen($d['match']->matchTime)-3)}}</td>
        <td>{{$d['match']->home}}</td>
        <td>{{$d['match']->away}}</td>
        <td>{{$d[5]}}</td>
        <td>{{$d[6]}}</td>
        <td>{{$d[7]}}</td>
        <td>{{$d[8]}}</td>
        <td>@include('layouts.partials.xofy', ['x' => $d['conf'], 'y' => $d['all']])</td>
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
            "sDom": '<"top"i>t<"bottom"><"clear">',
            "sPaginationType": "full_numbers",
            "aoColumnDefs": [
                { 'bSortable': false, 'aTargets': [ 0,1 ] }
            ]
        });
    });
</script>

@stop
