@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array('home' => URL::to("home"));
		$active = 'series';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => $country, 'small' => 'PPM series'))
@stop

@section('content')
<ul class="nav nav-tabs" id="myTab" style="border: none">
    <li class="active"><a href="#5">PPM 1X2</a></li>
    <li><a href="#6">PPM 0-0</a></li>
    <li><a href="#7">PPM 1-1</a></li>
    <li><a href="#8">PPM 2-2</a></li>
</ul>
<div id='content' class="tab-content">

@foreach($data as $i => $stats)
<div class="tab-pane @if($i == 5) active @endif" id="{{$i}}">
    <table class="table table-bordered">
        <tr>
            <td></td>
            <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;<span class="text-danger">
            <span class="text-danger">Top 5 longest series:
                        @foreach($stats['all'] as $l)
                            {{$l-1}},&nbsp;
                        @endforeach
                    </span></td>
            <td></td>
        </tr>
        @foreach($stats as $season => $el)
        @if($season != 'all')

        <tr>
            <td>{{$season}}</td>
            <td>
                @foreach($el['stats'] as $s)
                @if($s->current_length > 1)
                {{ $s->current_length - 1}}
                @endif
                <?php
                $d = ['team' => '', 'match' => $s]
                ?>
                @include('layouts.partials.square', array('data' => $d))
                @endforeach
            </td>
            <td>
                @foreach($el['longest'] as $l)
                {{$l-1}},&nbsp;
                @endforeach
            </td>
        </tr>
        @endif

        @endforeach
    </table>
</div>
@endforeach
    </div>
<script type="text/javascript">
    $('#myTab a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })
</script>
@stop
