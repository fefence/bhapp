@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array('Home' => URL::to("home"));
		$active = '';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => '', 'small' => ''))
@stop

@section('content')
<canvas id="myChart" width="400" height="400"></canvas>

<script>
    var data = {
        labels: ["January", "February", "March", "April", "May", "June", "July"],
        datasets: [
            {
                label: "My First dataset",
                fillColor: "rgba(220,220,220,0.2)",
                strokeColor: "rgba(220,220,220,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: [65, 59, 80, 81, 56, 55, 40]
            },
            {
                label: "My Second dataset",
                fillColor: "rgba(151,187,205,0.2)",
                strokeColor: "rgba(151,187,205,1)",
                pointColor: "rgba(151,187,205,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: [28, 48, 40, 19, 86, 27, 90]
            }
        ]
    };
    var ctx = document.getElementById("myChart").getContext("2d");
    var myNewChart = new Chart(ctx).Bar(data);
</script>
<script src="js/Chart.js"></script>
@stop
