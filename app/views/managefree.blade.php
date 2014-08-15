@extends('layout')

@section('breadcrumbs')
<!-- breadcrumbs -->

<?php
$list = array('pps' => URL::to("home"));
$active = 'manage free play';
$elements = array('active' => $active, 'list' => $list);
?>
@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Add Free play teams"))
@stop
@section('content')
<!-- Form -->
{{ Form::open(["url"=>"/saveteam"]) }}
<!-- Team Form Input -->
<div class="form-group">
    {{Form::label('url', 'URL:')}}</td>
    {{Form::text('url', null, ['class' => 'form-controll'])}}</td>
</div>
<div class="form-group">
    {{Form::submit()}}</td>
</div>
{{ Form::close() }}
@stop
