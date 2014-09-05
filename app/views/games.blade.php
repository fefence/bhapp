@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->
<?php
$list = array();
$active = 'pps';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => true, 'big' => $big, 'small' => $small))
@stop

@section('content')
<table id="matches">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="league"></th>
<!--        <th><input type="hidden"></th>-->
        <th><input type="hidden"></th>
<!--        <th><input type="hidden"></th>-->
<!--        <th><input type="hidden"></th>-->
    </tr>
    <tr>
        <th>league</th>
<!--        <th>bsf</th>-->
        <th>conf</th>
<!--        <th>-</th>-->
<!--        <th>+</th>-->
    </tr>
    </thead>
    <tbody>
    @foreach($data as $id=>$d)
    <tr class="{{$id}}">
        <td><a href="/pps/group/{{$id}}"><img src="/images/{{strtoupper($d['league']->country)}}.png"> {{$d['league']->displayName}}</a>&nbsp;(<a href="/pps/group/{{$id}}/{{isset($fromdate)?$fromdate.'/':''}}{{isset($todate)?$todate:''}}">today</a>)</td>
<!--        <td>@if(isset($d['prev'])) {{$d['prev']}} @endif</td>-->
        <td>@include('layouts.partials.xofy', ['x' => $d['conf'], 'y' => $d['today'], 'all' => $d['all']])</td>
<!--        @foreach($d['filter'] as $filter=>$count)-->
<!--        <td>-->
<!--            {{$count}}-->
<!--        </td>-->
<!--        @endforeach-->
<!--    <tr></tr>-->
<!--    <tr></tr>-->
    </tr>
    @endforeach
    </tbody>
</table>
<script type="text/javascript">
    $(document).ready(function () {
        var oTable = $("#matches").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"top"i>t<"bottom"><"clear">'
        });

    });
</script>
@stop
