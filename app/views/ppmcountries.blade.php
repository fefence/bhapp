@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->
<?php
$list = array();
$active = 'ppm';
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
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
        <th><input type="hidden"></th>
    </tr>
    <tr>
        <th>league</th>
        <th>1x2</th>
        <th>0:0</th>
        <th>1:1</th>
        <th>2:2</th>
        <th>0:1</th>
        <th>0:2</th>
        <th>1:0</th>
        <th>2:0</th>
        <th>1:2</th>
        <th>2:1</th>
        <th>conf</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
    <tr>
        <td>
            @if($info[$d->country]['all'] == 0)
            <img src="/images/{{strtoupper($d->country)}}.png"> <em>{{$d->country}}</em>
            @else
            <a href="/ppm/country/{{$d->country}}/{{isset($fromdate)?$fromdate.'/':''}}{{isset($todate)?$todate:''}}"><img src="/images/{{strtoupper($d->country)}}.png"> {{$d->country}}</a>
            @endif
            </td>
        <td @if($info[$d->country][5] > $info[$d->country][55]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][5]}}</td>
        <td @if($info[$d->country][6] > $info[$d->country][66]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][6]}}</td>
        <td @if($info[$d->country][7] > $info[$d->country][77]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][7]}}</td>
        <td @if($info[$d->country][8] > $info[$d->country][88]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][8]}}</td>
        <td @if($info[$d->country][9] > $info[$d->country][99]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][9]}}</td>
        <td @if($info[$d->country][10] > $info[$d->country][1010]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][10]}}</td>
        <td @if($info[$d->country][11] > $info[$d->country][1111]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][11]}}</td>
        <td @if($info[$d->country][12] > $info[$d->country][1212]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][12]}}</td>
        <td @if($info[$d->country][13] > $info[$d->country][1313]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][13]}}</td>
        <td @if($info[$d->country][14] > $info[$d->country][1414]) class="text-danger" style='font-weight: bold' @endif>{{$info[$d->country][14]}}</td>
        @if($info[$d->country]['all'] == 0)
        <td>-</td>
        @else
        <td>@include('layouts.partials.xofy', ['x' => $info[$d->country]['confirmed'], 'y' => $info[$d->country]['all']])</td>
        @endif
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
