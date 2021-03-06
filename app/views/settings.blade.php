@extends('layout')

@section('breadcrumbs')
<?php
$list = array();
$active = 'settings';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Settings"))
@stop

@section('content')
<!-- tabbed nav -->
<ul class="nav nav-pills" id="myTab" style="border: none;">
    <li><a href="#myppsleagues">My PPS Leagues</a></li>
    <li class='active'><a href="#myppmleagues">My PPM Leagues</a></li>
    <li><a href="#mybookmakers">My Bookmakers</a></li>
    <li><a href="#personal">Personal</a></li>
</ul>

<form id="settingsform" method="post" action="settings/save">
    <div id='content' class="tab-content">
        <!-- tab::myleagues -->
        <div class="tab-pane" id="myppsleagues">

            <div class="row">
                <table class="table-bordered" style="margin-bottom: 20px; width: 100%;">
                    <tr>
                        <?php $i = 0; ?>
                    @foreach($pps as $country=>$leagues)
                    @foreach($leagues as $name=>$s)
                        @if ($i % 3 == 0)
                            <tr>
                        @endif
                        <td><p class="text-center"><img src="/images/{{strtoupper($country)}}.png"> {{$name}}</p></td>
                        <td style="padding-left: 10px;">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="{{$s[0]}}-{{1}}-opt" id="0" value="0" {{($s[1]['auto'] == 0)?"checked":""}}>
                                    Disabled
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="{{$s[0]}}-{{1}}-opt" id="1" value="1" {{($s[1]['auto'] == 1)?"checked":""}}>
                                    Automatic
                                </label>
                                <input type="text" name="{{$s[0]}}-from-{{1}}" id="{{$s[0]}}-from-{{1}}" value="{{($s[1]['auto'] == 1)?$s[1]['from']:''}}" style="width: 25px;"> to
                                <input type="text" name="{{$s[0]}}-to-{{1}}" id="{{$s[0]}}-to-{{1}}" value="{{($s[1]['auto'] == 1)?$s[1]['to']:''}}" style="width: 25px;"> at
                                <input type="text" name="{{$s[0]}}-mul-{{1}}" id="{{$s[0]}}-mul-{{1}}" value="{{($s[1]['auto'] == 1)?$s[1]['multiplier']:''}}" style="width: 25px;">
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="{{$s[0]}}-{{1}}-opt" id="2" value="2" {{($s[1]['auto'] == 2)?"checked":""}}>
                                    Fixed
                                </label> >
                                <input type="text" name="{{$s[0]}}-gt-{{1}}" id="{{$s[0]}}-gt-{{1}}" value="{{($s[1]['auto'] == 2)?$s[1]['from']:''}}" style="width: 25px;"> at
                                <input type="text" name="{{$s[0]}}-mult-{{1}}" id="{{$s[0]}}-mult-{{1}}" value="{{($s[1]['auto'] == 2)?$s[1]['multiplier']:''}}" style="width: 25px;">
                            </div>
                        </td>
                        @if ($i % 3 == 2)
                    </tr>
                        @endif
                    <?php $i ++; ?>

                        @endforeach
                        @endforeach
                    </tr>
                </table>
            </div>
        </div>

        <!-- tab::myppmleagues -->
        <div class="tab-pane active" id="myppmleagues">
            <h6>PPM</h6>

            <div class="row">
                <table class="table-bordered" style="margin-bottom: 20px; width: 100%;">
                    <tr>
                        <th style="width: 15%;text-align: center;">Country::League</th>
                        <th style="width: %;text-align: center;">1x2</th>
                        <th style="width: %;text-align: center;">0:0</th>
                        <th style="width: %;text-align: center;">1:1</th>
                        <th style="width: %;text-align: center;">2:2</th>
                        <th style="width: %;text-align: center;">0:1</th>
                        <th style="width: %;text-align: center;">0:2</th>
                        <th style="width: %;text-align: center;">1:0</th>
                        <th style="width: %;text-align: center;">2:0</th>
                        <th style="width: %;text-align: center;">1:2</th>
                        <th style="width: %;text-align: center;">2:1</th>
                    </tr>
                    @foreach($ppm as $country=>$s)
                    <tr>
                        <td><p class="text-center"><img src="/images/{{strtoupper($country)}}.png">&nbsp;{{ $country }}</p></td>
                        @for($j = 5; $j < 15; $j ++)
                        <td><p class="text-center">
                                <input name="ppm[]" class="activate_league_for_play" type="checkbox"
                                       value='{{$s[0]}}#{{$j}}' {{(count($s[$j]) > 0)?"checked":""}}>
                            </p>
                        </td>
                        @endfor
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <!-- tab::mybookmakers -->
        <div class="tab-pane" id="mybookmakers">
            <h6>Bookmakers</h6>

            <div class="row">
                <table class="table-bordered" style="margin-bottom: 20px; width: 100%;">
                    <tr>
                        <td style="padding-top: 15px; width: 200px;">
                            <abbr title="Used as the default to calculate profit and income.">Primary Bookmaker</abbr>
                        </td>
                        <td>
                            <select name="default_bookmaker" class="form-control" style="width: 200px;">
                                <option value='1'>bet365</option>
                                <option value='2'>betfair</option>
                                <option value='3'>will hill</option>
                                <option value='5'>unibet</option>
                                <option value='4'>pinnacle sport</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 15px;">
                            <abbr title="Which odds to be displayed on the main view.">Display Odds</abbr>
                        </td>
                        <td>
                            <div class="btn-group">
                                <input type="button" name="bookies-1" value="bet365" class="btn btn-default"
                                       data-toggle="button">
                                <button value='1' type="button" class="btn btn-default" data-toggle="button">bet365
                                </button>
                                <button value='2' type="button" class="btn btn-default" data-toggle="button">betfair
                                </button>
                                <button value='3' type="button" class="btn btn-default" data-toggle="button">bwin
                                </button>
                                <button value='5' type="button" class="btn btn-default" data-toggle="button">unibet
                                </button>
                                <button value='4' type="button" class="btn btn-default" data-toggle="button">pinnacle
                                    sport
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 15px;">
                            <abbr title="Auto confirms the input in all views upon match end.">Auto confirm</abbr>
                        </td>
                        <td>
                            <select class="form-control" style="width: 200px;">
                                <option>Enabled</option>
                                <option>Disabled</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- tab::personal -->
        <div class="tab-pane" id="personal">
            <h6>Personal</h6>

            <div class="row">
                <table class="table-bordered" style="margin-bottom: 20px; width: 100%;">
                    <tr>
                        <td>
                            Current Password
                        </td>
                        <td>
                            <input type="password">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            New Password
                        </td>
                        <td>
                            <input type="password">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Repeat New Password
                        </td>
                        <td>
                            <input type="password">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</form>
<!-- js for tabs -->
<script type="text/javascript">
    $('.opt').change(function () {
        var optionSelected = $(this).find("option:selected");
        var valueSelected = optionSelected.val();
        if (valueSelected == "auto") {
            $(this).siblings("#inpts").show();
            $(this).siblings("#ltspan").hide();
        } else if (valueSelected == "fixed") {
            $(this).siblings("#ltspan").show();
            $(this).siblings("#inpts").hide();
        } else {
            $(this).siblings("#ltspan").hide();
            $(this).siblings("#inpts").hide();
        }
    });

    $("#save").on('click', function () {
        $("#settingsform").submit();
    });

    $('#myTab a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    });
</script>
@stop