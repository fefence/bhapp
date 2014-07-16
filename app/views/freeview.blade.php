@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->

<?php
$list = array('pps' => URL::to("home"));
$active = 'freeview';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Add PPS leagues"))
@stop
@section('content')

@stop
