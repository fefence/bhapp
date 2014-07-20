@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->
<?php
if (isset($group)) {
    $list = array('pps' => URL::route("home"));
    $active = $league->country.":".$league->fullName;
} else {
    $list = array();
    $active = "ppm";
}
$elements = array('active' => $active, 'list' => $list);
$i = 0;
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => true, 'big' => isset($big)?$big:"Matches", 'small' => isset($small)?$small:"some date here"))
@stop

@section('content')
@foreach($datarr as $data)
<table id="matches{{$i}}" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
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
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th></th>
        <th></th>
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
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
    @if(isset($d->match_id))
    <tr class="{{$d->match_id}}" id="{{isset($d->games_id)?$d->games_id:''}}">
        <td class="center"><img class="clickable" src="/images/plus-small.png"></td>
        @if (isset($ppm) && $ppm)
        <td class="center"><img src="/images/{{strtoupper($d->country)}}.png"></td>
        @else
        <td></td>
        @endif
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
        <td>{{$d->streak}}</td>
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
        <td>{{$d->income - $d->bsf - $d->bet}}</td>
        <td><a href="/confirm/{{$d->games_id}}/{{$d->game_type_id}}">+&nbsp;<span style='display: none;'>{{$d->match_id}}</span></a>({{ (array_key_exists($d->match_id,
            $count))?$count[$d->match_id]:$count[$d->id] }})
        </td>
    </tr>
    @else
    @foreach($d as $dd)
    <tr id="no">
        <td class="center"><img class="clickable" src="/images/plus-small.png"></td>
        <td></td>
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
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td><a href="/addgame/{{$dd->groups_id}}/{{$dd->standings_id}}/{{$dd->id}}">+</a> <span style='display: none;'>{{$dd->id}}</span></td>
    </tr>
    @endforeach
    @endif
    @endforeach

    </tbody>
</table>
<?php
    $i ++;
?>
@endforeach
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
    function fnFormatDetails(oTable, nTr) {
        var text = '';
        var aData = oTable.fnGetData(nTr);
        var team = '';
        if (aData[4].indexOf("<strong>") > -1) {
            var re = new RegExp("<strong>(.*?)</strong>");
            var m = re.exec(aData[4]);
            team = m[1];
        } else if (aData[5].indexOf("<strong>") > -1) {
            var re = new RegExp("<strong>(.*?)</strong>");
            var m = re.exec(aData[5]);
            team = m[1];
        } else {
            team = 'ppm';
        }
        var re = new RegExp('display: none;">(.*?)</span>');
        var m = re.exec(aData[16]);
        var id = m[1];
//		alert(id);
        // var d = aData[1].replace(/\//g, '-');
        var promise = testAjax(team, id, aData[9]);
        promise.success(function (data) {
            text = data;
        });
        return text;
    }

    function testAjax(team, mDate, game) {
        var url = "/details/" + team + "/" + mDate + "/" + game;
//        alert(url);
        return $.ajax({
            async: false,
            url: url
        });
    }

    $("tbody>tr").hover(
        function () {
            var claz = $(this).attr('class');
            var st = claz.split(' ');
            var firstClass = st[0];
            var id = "." + firstClass;
            // alert(id);
            if ($(id).length > 1 && firstClass != 'odd' && firstClass != even) {
                $(id + ">td").addClass("dt-doublematch");
            }
            //$(id).attr("style", "color: red");
            //$( this ).append( $( "<span> ***</span>" ) );
        }, function () {
            var claz = $(this).attr('class');
            var st = claz.split(' ');
            var firstClass = st[0];

            var id = "." + firstClass;
            //alert(id);
            $(id + ">td").removeClass("dt-doublematch");
            //$(id).addClass("test");
        }
    );

    var asInitVals = new Array();

    $(document).ready(function () {

        var oTable = $("#matches0").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sDom": '<"top"i>t<"bottom"><"clear">',
            "sPaginationType": "full_numbers",
            "aoColumnDefs": [
                { 'bSortable': false, 'aTargets': [ 0,1 ] }
            ]
        });

        var oTable1 = $("#matches1").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sDom": '<"top"i>t<"bottom"><"clear">',
            "sPaginationType": "full_numbers",
            "aoColumnDefs": [
                { 'bSortable': false, 'aTargets': [ 0,1 ] }
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
        oTable.$('td.editable').editable('/save', {
            "callback": function (sValue, y) {
                var aPos = oTable.fnGetPosition(this);
//                alert(sValue);
                var arr = sValue.split("#");
                oTable.fnUpdate(arr[0], aPos[0], 11);
                oTable.fnUpdate(arr[1], aPos[0], 12);
                oTable.fnUpdate(arr[2], aPos[0], 13);
                oTable.fnUpdate(arr[3], aPos[0], 14);
                oTable.fnUpdate(arr[3] - arr[0] - arr[1], aPos[0], 15);

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
