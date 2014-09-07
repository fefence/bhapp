@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->

<?php
$list = array('free' => URL::to("/free"));
$active = 'manage free play';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Add Free play teams"))
@stop
@section('content')
<div class="form-horizontal col-sm-6">
<!-- Form -->
{{ Form::open(["url"=>"/saveteam"]) }}
<!-- Team Form Input -->
<div class="form-group ">
    {{Form::label('url', 'URL:')}}</td>
    {{Form::text('url', null, ['class' => 'form-controll '])}}</td>
    {{Form::submit()}}</td>
</div>
{{ Form::close() }}
</div>

<table id="teams">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="country"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="league"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="team"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th style="width: 100px;">country</th>
        <th style="width: 170px;">league</th>
        <th>team</th>
        <th>l</th>
        <th style="width: 100px;">profit</th>
        <th style="width: 100px;">account</th>
        <th style="width: 100px;">pool</th>
        <th style="width: 40px;"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
    <tr>
        <td><img src="/images/{{strtoupper($d->country)}}.png"> {{$d->country}}</a></td>
        <td>{{$d->displayName}}</td>
        <td>{{$d->team}}</td>
        <td>{{$d->streak}}</td>
        <td>{{$d->profit}}</td>
        <td>{{$d->account}}</td>
        <td>{{$d->amount}}</td>
        @if ($d->hidden == 0)
        <td><a role="button" class="btn btn-primary btn-xs" href="/free/hide/{{$d->team_id}}">hide</a></td>
        @else
        <td><a role="button" class="btn btn-warning btn-xs" href="/free/show/{{$d->team_id}}">show</a></td>
        @endif
    </tr>
    @endforeach
    </tbody>
</table>
<script type="text/javascript">
    var asInitVals = new Array();

    $(document).ready(function () {
        var oTable = $("#teams").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"top"i>t<"bottom"><"clear">'
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
    });
</script>

@stop
