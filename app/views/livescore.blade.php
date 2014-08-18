@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	<?php
		$list = array('pps' => URL::to("home"));
		$active = 'livescore';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => "Livescore", 'small' => "28-Apr-14 (Mon)"))
@stop

@section('content')
<table id="matches" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="league"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th>league</th>
        <th style="width:60px;">date</th>
        <th style="width:50px;">time</th>
        <th style="width:120px;">home</th>
        <th style="width:120px;">away</th>
        <th>res</th>
        <th>res</th>
    </tr>
    </thead>
    <tbody>
    @foreach($matches as $d)
    <tr class="{{$d->match_id}}">
        <td><img src='/images/{{strtoupper($d->country)}}.png'>&nbsp;{{$d->displayName}}</td>
        <td>{{date('d M', strtotime($d->matchDate))}}</td>
        <td>{{substr($d->matchTime, 0, strlen($d->matchTime)-3)}}</td>
        <td><a href="/livescorematch/{{$d->id}}">{{$d->home}}</a></td>
        <td><a href="/livescorematch/{{$d->id}}">{{$d->away}}</a></td>
        <td>
            @if ($d->resultShort != '-')
            {{$d->homeGoals}}:{{$d->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$d->resultShort}}</td>
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
                { 'bSortable': false, 'aTargets': [ 0 ] }
            ],
            "order": [[ 1, "asc" ]]
        });
    });
</script>

@stop