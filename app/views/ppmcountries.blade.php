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
        <th>bsf (1x2)</th>
        <th>bsf (0:0)</th>
        <th>bsf (1:1)</th>
        <th>bsf (2:2)</th>
        <th>bsf (0:1)</th>
        <th>bsf (0:2)</th>
        <th>bsf (1:0)</th>
        <th>bsf (2:0)</th>
        <th>bsf (1:2)</th>
        <th>bsf (2:1)</th>
        <th>conf</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
    <tr>
        <td><a href="/ppm/country/{{$d->country}}/{{isset($fromdate)?$fromdate.'/':''}}{{isset($todate)?$todate:''}}"><img src="/images/{{strtoupper($d->country)}}.png"> {{$d->country}}</a></td>
        <td>{{$info[$d->country][5]}}</td>
        <td>{{$info[$d->country][6]}}</td>
        <td>{{$info[$d->country][7]}}</td>
        <td>{{$info[$d->country][8]}}</td>
        <td>{{$info[$d->country][9]}}</td>
        <td>{{$info[$d->country][10]}}</td>
        <td>{{$info[$d->country][11]}}</td>
        <td>{{$info[$d->country][12]}}</td>
        <td>{{$info[$d->country][13]}}</td>
        <td>{{$info[$d->country][14]}}</td>
        <td>{{$info[$d->country][55]}}</td>
        <td>{{$info[$d->country][66]}}</td>
        <td>{{$info[$d->country][77]}}</td>
        <td>{{$info[$d->country][88]}}</td>
        <td>{{$info[$d->country][99]}}</td>
        <td>{{$info[$d->country][1010]}}</td>
        <td>{{$info[$d->country][1111]}}</td>
        <td>{{$info[$d->country][1212]}}</td>
        <td>{{$info[$d->country][1313]}}</td>
        <td>{{$info[$d->country][1414]}}</td>
        <td>@include('layouts.partials.xofy', ['x' => $info[$d->country]['confirmed'], 'y' => $info[$d->country]['all']])</td>
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
