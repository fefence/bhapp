@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->

<?php
$list = array('ppm' => URL::to("ppm"));
$active = 'series details';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "<img src='/images/".strtoupper($league).".png'> ".$league, 'small' => ''))
@stop

@section('content')
<table id="matches" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="r"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="game"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="bookie"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th style="width:60px;">date</th>
        <th style="width:50px;">time</th>
        <th>home</th>
        <th>away</th>
        <th style="width:10px;">l</th>
        <th>res</th>
        <th style="width:10px;">r</th>
        <th style="width:60px;">game</th>
        <th style="width:40px;">bookie</th>
        <th style="width:40px;">bsf</th>
        <th style="width:40px;">bet</th>
        <th style="width:40px;">odds</th>
        <th style="width:40px;">income</th>
        <th style="width:40px;">profit</th>
    </tr>
    </thead>
    <tbody>
    @foreach($games as $d)
    <tr class="{{$d->match_id}}">
        <td>{{date('d M', strtotime($d->matchDate))}}</td>
        <td>{{substr($d->matchTime, 0, strlen($d->matchTime)-3)}}</td>
        <td>{{$d->home}}</td>
        <td>{{$d->away}}</td>
        <td>{{$d->current_length}}</td>
        <td>
            @if ($d->resultShort != '-')
            {{$d->homeGoals}}:{{$d->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$d->resultShort}}</td>
        <td class='editabledd warning'>{{$d->type}}</td>
        <td class='editabledd warning'>{{$d->bookmakerName}}</td>
        <td class='editable warning' id="{{$d->game_type_id}}">{{$d->bsf}}</td>
        <td class='editable warning' id="{{$d->game_type_id}}">{{$d->bet}}</td>
        <td class='editable warning' id="{{$d->game_type_id}}">{{$d->odds}}</td>
        <td>{{$d->income}}</td>
        <td>{{round(($d->income - $d->bsf - $d->bet), 2, PHP_ROUND_HALF_UP)}}</td>
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
