@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->
<?php
$list = array('pps' => URL::route("home"));
$active = "<img src='/images/" . strtoupper($league->country) . ".png'> " . $league->displayName. " <a href='http://www.betexplorer.com/soccer/".$league->country."/".$league->fullName."'>[betexpl]</a> <a href='http://www.sportstats.com/soccer/".$league->country."/".$league->fullName."/streaks/#no-raws'>[sportstats]</a>";

$elements = array('active' => $active, 'list' => $list);
$i = 0;
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => true, 'big' => $big, 'small' => $small))
@stop

@section('content')
@foreach($datarr as $data)
<table id="matches{{$i}}" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="hidden"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="r"></th>
<!--        <th><input type="text" name="search_engine" class="search_init" placeholder="game"></th>-->
<!--        <th><input type="text" name="search_engine" class="search_init" placeholder="bookie"></th>-->
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th></th>
        <th style="width:60px;">date</th>
        <th style="width:50px;">time</th>
        <th>home</th>
        <th>away</th>
        <th style="width:10px;">l</th>
        <th>res</th>
        <th style="width:10px;">r</th>
<!--        <th style="width:60px;">game</th>-->
<!--        <th style="width:40px;">bookie</th>-->
        <th style="width:40px;">bsf</th>
        <th style="width:40px;">bet</th>
        <th style="width:40px;">odds</th>
        <th style="width:40px;">income</th>
        <th style="width:40px;">profit</th>
        <th style="width: 30px;"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
    @if(isset($d->match_id))
    <tr class="{{$d->match_id}}" id="{{isset($d->games_id)?$d->games_id:''}}">
        <td class="center"><img class="clickable" src="/images/plus-small.png"></td>
        <td>{{date('d M', strtotime($d->matchDate))}}</td>
        <td>{{substr($d->matchTime, 0, strlen($d->matchTime)-3)}}</td>
        <td>
            @if ($d->team == $d->home)
            <strong>{{$d->home}}</strong>
            @else
            {{$d->home}}
            @endif
            @if(array_key_exists($d->home, $standings))
            ({{$standings[$d->home]}})
            @endif
        </td>
        <td>
            @if ($d->team == $d->away)
            <strong>{{$d->away}}</strong>
            @else
            {{$d->away}}
            @endif
            @if(array_key_exists($d->away, $standings))
            ({{$standings[$d->away]}})
            @endif
        </td>
        <td>{{$d->streak}}</td>
        <td>
            @if ($d->resultShort != '-')
            {{$d->homeGoals}}:{{$d->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$d->resultShort}}</td>
<!--        <td class='text-muted'><em>{{$d->type}}</em></td>-->
        <td @if($d->resultShort == '-') class='editable' @endif id="{{$d->game_type_id}}">{{$d->bsf}}</td>
        <td @if($d->resultShort == '-') class='editable oddsColumn' @endif id="{{$d->game_type_id}}">{{$d->bet}}</td>
        @if ($d->odds == 0 || $d->odds == -1)
            <td style="background-color: #F8E0E6;" @if($d->resultShort == '-') class='editable' @endif id="{{$d->game_type_id}}">3.00</td>
        @else
            <td @if($d->resultShort == '-') class='editable' @endif id="{{$d->game_type_id}}">{{$d->odds}}</td>
        @endif
        <td>{{$d->income}}</td>
        <td>{{round(($d->income - $d->bsf - $d->bet), 2, PHP_ROUND_HALF_UP)}}</td>
        <td>@if($d->resultShort == '-')
            <a role="button" @if ($count[$d->id] != 0) class="btn btn-default btn-xs" @else class="btn btn-primary btn-xs" @endif style="width: 50px" href="/confirm/{{$d->games_id}}/{{$d->game_type_id}}">+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})</a><span style='display: none;'>{{$d->match_id}}</span>
            @elseif ($d->resultShort == 'D')
            <a role="button" class="btn btn-success btn-xs" style="width: 50px" href="#" disabled>+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})</a><span style='display: none;'>{{$d->match_id}}</span>
            @else
            <a role="button" class="btn btn-default btn-xs" style="width: 50px" href="#" disabled>+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})</a><span style='display: none;'>{{$d->match_id}}</span>
            @endif
        </td>
    </tr>
    @else
    @foreach($d as $dd)
    <tr class="{{$dd->id}}" id="no">
        <td class="center"><img class="clickable" src="/images/plus-small.png"></td>
        <td>{{date('d M', strtotime($dd->matchDate))}}</td>
        <td>{{substr($dd->matchTime, 0, strlen($dd->matchTime)-3)}}</td>
        <td>
            @if ($dd->team == $dd->home)
            <strong>{{$dd->home}}</strong>
            @else
            {{$dd->home}}
            @endif
            ({{$standings[$dd->home]}})
        </td>
        <td>
            @if ($dd->team == $dd->away)
            <strong>{{$dd->away}}</strong>
            @else
            {{$dd->away}}
            @endif
            ({{$standings[$dd->away]}})
        </td>
        <td>{{$dd->streak}}</td>
        <td>
            @if ($dd->resultShort != '-')
            {{$dd->homeGoals}}:{{$dd->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$dd->resultShort}}</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
<!--        <td>-</td>-->
        <td>-</td>
        <td>-</td>
        <td><a role="button" class="btn btn-xs btn-info" style="width: 50px" href="/addgame/{{$dd->groups_id}}/{{$dd->standings_id}}/{{$dd->id}}">+</a> <span style='display: none;'>{{$dd->id}}</span>
        </td>
    </tr>
    @endforeach
    @endif
    @endforeach

    </tbody>
</table>
<?php
$i++;
?>
@endforeach
@if(isset($settings))
<div class="row">
    <form class="form-horizontal" role="form" action="" method="post" id="sett">
        <div class="col-xs-2">
            <div class="input-group">
                <span class="input-group-addon">+</span>
                <input type="text" class="form-control" value="{{$plus}}" disabled>
                <span class="input-group-addon">-</span>
                <input type="text" class="form-control" value="{{$minus}}" disabled>
            </div>
        </div>
        <div class="col-xs-2">
            <input type="button" value="edit" onclick="return false;" class="form-control btn-xs" id="edit">
        </div>
        <div class="col-xs-2">
            <input type="text" class="form-control" name="from" id="from" placeholder="from" value="{{$settings->from}}" readonly="readonly">
        </div>
        <div class="col-xs-2">
            <input type="text" class="form-control" name="to" id="to" placeholder="to" value="{{$settings->to}}" readonly="readonly">
        </div>
        <div class="col-xs-2">
            <input type="text" class="form-control" name="multiplier" id="multiplier" placeholder="multiplier"  value="{{$settings->multiplier}}" readonly="readonly">
            <input type="hidden" name="league_details_id" value="{{$league->id}}">
        </div>
        <div class="col-xs-2">
            <input type="submit" value="save" class="form-control btn-xs" id="subm" disabled>
        </div>
    </form>
</div>
@endif
<script type="text/javascript">
    $('#edit').on("click", function(){
        if ( $(this).attr('value') == 'edit') {
            $(this).attr({
                value: 'cancel'
            });
            $("#sett").attr({
                action: "/settings/saveforleague"
            });
            $("#from").attr({
                readonly: false
            });
            $("#to").attr({
                readonly: false
            });
            $("#multiplier").attr({
                readonly: false
            });
            $("#subm").attr({
                disabled: false,
                class: "form-control btn-xs btn-primary"
            });
        } else {
            $(this).attr({
                value: 'edit'
            });
            $("#sett").attr({
                action: ""
            });
            $("#from").attr({
                readonly: true
            });
            $("#to").attr({
                readonly: true
            });
            $("#multiplier").attr({
                readonly: true
            });
            $("#subm").attr({
                disabled: true,
                class: "form-control btn-xs"
            });
        }
    });

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
function fnFormatDetails(oTable, nTr) {
    var text = '';
    var aData = oTable.fnGetData(nTr);
    var team = '';
    if (aData[3].indexOf("<strong>") > -1) {
        var re = new RegExp("<strong>(.*?)</strong>");
        var m = re.exec(aData[3]);
        team = m[1];
    } else if (aData[4].indexOf("<strong>") > -1) {
        var re = new RegExp("<strong>(.*?)</strong>");
        var m = re.exec(aData[4]);
        team = m[1];
    } else {
        team = 'ppm';
    }
    var re = new RegExp('display: none;">(.*?)</span>');
    var m = re.exec(aData[13]);
    var id = m[1];
//		alert(id);
//    var re2 = new RegExp('<em>(.*?)</em>');
//    var game = re2.exec(aData[8])[1];
    // var d = aData[1].replace(/\//g, '-');
    var promise = testAjax(team, id);
    promise.success(function (data) {
        text = data;
    });
    return text;
}

function testAjax(team, mDate, game) {
    var url = "/details/" + team + "/" + mDate;
//        alert(url);
    return $.ajax({
        async: false,
        url: url
    });
}

var asInitVals = new Array();


$(document).ready(function () {

    var oTable = $("#matches0").dataTable({
        "iDisplayLength": 100,
        "bJQueryUI": true,
        "sDom": '<"top"i>t<"bottom"><"clear">',
        "sPaginationType": "full_numbers",
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 0, 1 ] }
        ]
    });

    var oTable1 = $("#matches1").dataTable({
        "iDisplayLength": 100,
        "bJQueryUI": true,
        "sDom": '<"top"i>t<"bottom"><"clear">',
        "sPaginationType": "full_numbers",
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 0 ] }
        ],
        "order": [[ 1, "asc" ], [ 2, "asc" ], [ 3, "asc" ]]
    });
    var cls = "text-danger";

    $('#matches0 tbody')
        .on( 'mouseover', 'tr', function () {
            $(this).children().addClass(cls);
            $('#matches1 tbody .' + $(this).attr("class").split(' ')[0]).addClass(cls);
            $('#matches0 tbody .' + $(this).attr("class").split(' ')[0]).addClass(cls);
//            alert($(this).attr("class").split(' ')[0]);
        } )
        .on( 'mouseleave', 'tr', function () {
            $(this).children().removeClass(cls);
            $('#matches1 tbody .' + $(this).attr("class").split(' ')[0]).removeClass(cls);
            $('#matches0 tbody .' + $(this).attr("class").split(' ')[0]).removeClass(cls);

        } );

    $('#matches1 tbody')
        .on( 'mouseover', 'tr', function () {
            $(this).children().addClass(cls);
            $('#matches0 tbody .' + $(this).attr("class").split(' ')[0]).addClass(cls);
            $('#matches1 tbody .' + $(this).attr("class").split(' ')[0]).addClass(cls);
//            alert($(this).attr("class").split(' ')[0]);
        } )
        .on( 'mouseleave', 'tr', function () {
            $(this).children().removeClass(cls);
            $('#matches0 tbody .' + $(this).attr("class").split(' ')[0]).removeClass(cls);
            $('#matches1 tbody .' + $(this).attr("class").split(' ')[0]).removeClass(cls);

        } );

    $("thead input").keyup(function () {
        /* Filter on the column (the index) of this element */
        oTable.fnFilter(this.value, $("thead input").index(this));
    });

    /*
     * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
     * the footer
     */
    $("thead input").each(function (i) {
        asInitVals[i] = this.value;
    });

    $("thead input").focus(function () {
        if (this.className == "search_init") {
            this.className = "";
            this.value = "";
        }
    });

    $("thead input").blur(function () {
        if (this.value == "") {
            this.className = "search_init";
            this.value = asInitVals[$("thead input").index(this)];
        }
    });

    /* Apply the jEditable handlers to the table */
    oTable.$('td.editable').editable('/save', {
        "callback": function (sValue, y) {
            var aPos = oTable.fnGetPosition(this);
//                alert(sValue);
            var arr = sValue.split("#");
            oTable.fnUpdate(arr[0], aPos[0], 8);
            oTable.fnUpdate(arr[1], aPos[0], 9);
            oTable.fnUpdate(arr[2], aPos[0], 10);
            oTable.fnUpdate(arr[3], aPos[0], 11);
            var a = arr[3] - arr[0] - arr[1];
            oTable.fnUpdate(a.toFixed(2), aPos[0], 12);

            if (arr[4] != "") {
                if (parseFloat(arr[4]) != parseFloat($("#pool").text())) {
                    $("#curr").html(" <strong>(" + arr[4] + ")</strong>");
                } else {
//                        alert(arr[4]);
                    $("#curr").html(" (" + $("#pool").text() + ")");
                }

            }
        },
        "submitdata": function (value, settings) {
            return {
                "row_id": this.parentNode.getAttribute('id'),
                "column": oTable.fnGetPosition(this)[2]
            };
        },
        "height": "25px",
        "width": "40px"
    });

    oTable.$('td.editabledd').editable('#', {
        "callback": function (sValue, y) {
            var aPos = oTable.fnGetPosition(this);
            var arr = sValue.split("#");

            //oTable.fnUpdate( arr[0], aPos[0], 7 );
        },
        "submitdata": function (value, settings) {
            return {
                "row_id": this.parentNode.getAttribute('id'),
                "column": oTable.fnGetPosition(this)[2]
            };
        },
        "height": "25px",
        "width": "40px"
    });

    $('#matches0 tbody').on('click', '.clickable', function () {
        var nTr = this.parentNode.parentNode;
        if (this.src.match('minus-small')) {
            /* This row is already open - close it */
            this.src = "/images/plus-small.png";
            oTable.fnClose(nTr);
        }
        else {
            /* Open this row */
            this.src = "/images/minus-small.png";
            oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
        }
    });

    $('#matches1 tbody').on('click', '.clickable', function () {
        var nTr = this.parentNode.parentNode;
        if (this.src.match('minus-small')) {
            /* This row is already open - close it */
            this.src = "/images/plus-small.png";
            oTable1.fnClose(nTr);
        }
        else {
            /* Open this row */
            this.src = "/images/minus-small.png";
            oTable1.fnOpen(nTr, fnFormatDetails(oTable1, nTr), 'details');
        }
    });
    // if ($("#crr").text() != $("#pool").text()) {
    //   		$("#crr").html("<strong>"+$("#crr").text()+"</strong>");
    //   	} else {
    //   		$("#crr").html(arr[4]);
    //   	}

});
</script>
@stop
