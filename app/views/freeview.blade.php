@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->

<?php
$list = array();
$active = 'freeview';
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
        <th><input type="text" name="search_engine" class="search_init" placeholder="r"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="game"></th>
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
        <th style="width:60px;">game</th>
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
    <tr id="{{isset($d->games_id)?$d->games_id:''}}">
        <td class="center"><img class="clickable" src="/images/plus-small.png"></td>
        <td>{{date('d M', strtotime($d->matchDate))}}</td>
        <td>{{substr($d->matchTime, 0, strlen($d->matchTime)-3)}}</td>
        <td>
            @if ($d->team == $d->home)
            <strong>{{$d->home}}</strong>
            @else
            {{$d->home}}
            @endif
            ({{$standings[$d->home]}})
        </td>
        <td>
            @if ($d->team == $d->away)
            <strong>{{$d->away}}</strong>
            @else
            {{$d->away}}
            @endif
            ({{$standings[$d->away]}})
        </td>
        <td>{{$d->current_length}}</td>
        <td>
            @if ($d->resultShort != '-')
            {{$d->homeGoals}}:{{$d->awayGoals}}
            @else
            -
            @endif
        </td>
        <td>{{$d->resultShort}}</td>
        <td>{{$d->type}}</td>
        <td>{{$d->bsf}}</td>
        <td class='editable oddsColumn' id="{{$d->team_id}}">{{$d->bet}}</td>
        <td class='editable' id="{{$d->team_id}}">{{$d->odds}}</td>
        <td>{{$d->income}}</td>
        <td>{{round(($d->income - $d->bsf - $d->bet), 2, PHP_ROUND_HALF_UP)}}</td>
        <td>@if($d->resultShort == '-')
            <a role="button" @if ($count[$d->id] != 0) class="btn btn-default btn-xs" @else class="btn btn-primary btn-xs" @endif style="width: 50px" href="/confirmfree/{{$d->games_id}}">+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})<span style='display: none;'>{{$d->match_id}}#{{$d->team_id}}</span></a>
            @elseif ($d->resultShort == 'D')
            <a role="button" class="btn btn-success btn-xs" style="width: 50px" href="#" disabled>+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})</a><span style='display: none;'>{{$d->match_id}}#{{$d->team_id}}</span>
            @else
            <a role="button" class="btn btn-default btn-xs" style="width: 50px" href="#" disabled>+&nbsp({{ (array_key_exists($d->match_id, $count))?$count[$d->match_id]:$count[$d->id] }})</a><span style='display: none;'>{{$d->match_id}}#{{$d->team_id}}</span>
            @endif
        </td>
    </tr>

    @endif
    @endforeach

    </tbody>
</table>
<script type="text/javascript">
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
        team_arr = m[1].split("#");
        var team = team_arr[1];
        var match = team_arr[0];
    } else {
        team = 'ppm';
    }
    var re = new RegExp('display: none;">(.*?)</span>');
    var m = re.exec(aData[14]);
    team_arr = m[1].split("#");
    var team = team_arr[1];
    var id = team_arr[0];
//		alert(id);
    // var d = aData[1].replace(/\//g, '-');
    var promise = testAjax(team, id, aData[8]);
    promise.success(function (data) {
        text = data;
    });
    return text;
}

function testAjax(team, mDate, game) {
    var url = "/detailsfree/" + mDate + "/" + team;
//        alert(url);/
    return $.ajax({
        async: false,
        url: url
    });
}
var asInitVals = new Array();

$(document).ready(function () {

    var oTable = $("#matches").dataTable({
        "iDisplayLength": 100,
        "bJQueryUI": true,
        "sDom": '<"top"i>t<"bottom"><"clear">',
        "sPaginationType": "full_numbers",
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 0 ] }
        ]
    });


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
    oTable.$('td.editable').editable('/savefree', {
        "callback": function (sValue, y) {
            var aPos = oTable.fnGetPosition(this);
//                alert(sValue);
            var arr = sValue.split("#");
            oTable.fnUpdate(arr[0], aPos[0], 9);
            oTable.fnUpdate(arr[1], aPos[0], 10);
            oTable.fnUpdate(arr[2], aPos[0], 11);
            oTable.fnUpdate(arr[3], aPos[0], 12);
            var a = arr[3] - arr[0] - arr[1];
            oTable.fnUpdate(a.toFixed(2), aPos[0], 13);

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

    $('#matches tbody').on('click', '.clickable', function () {
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


    // if ($("#crr").text() != $("#pool").text()) {
    //   		$("#crr").html("<strong>"+$("#crr").text()+"</strong>");
    //   	} else {
    //   		$("#crr").html(arr[4]);
    //   	}

});
</script>
@stop
