@extends('layout')

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => 'Home team - Away team', 'small' => ''))
@stop

@section('content')
    {{$html}}
@stop
