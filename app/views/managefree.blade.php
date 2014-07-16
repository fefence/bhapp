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
@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Add PPS leagues"))
@stop
@section('content')
<!-- Form -->
{{ Form::open(["url"=>"/saveteam"]) }}
<!-- Team Form Input -->
<div class="form-group">
    {{Form::label('team', 'Team ID:')}}</td>
    {{Form::text('team', null, ['class' => 'form-controll'])}}</td>
</div>
<!-- League_id Form Input -->
<div class="form-group">
    {{Form::label('league_id', 'League Id:')}}</td>
    {{Form::text('league_id', null, ['class' => 'form-controll'])}}</td>
</div>
<div class="form-group">
    {{Form::submit()}}</td>
</div>
{{ Form::close() }}
@stop
