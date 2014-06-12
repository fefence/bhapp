@extends('layout')

@section('breadcrumbs')
	<?php
		$list = array('Home' => URL::to("home"), 'countries' => URL::to('countries'));
		$active = 'Simulator';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => $country.' :: '.$league, 'small' => $seasoncount.' seasons '.$time.'s'))
@stop

@section('content')

<table class='simulator'>
		{{Form::open(array('url' => $action."/".$country."/".$league))}}
	<tr>
		<td class="leading">{{Form::label('country', 'Country')}}</td>
		<td style="width: 85%;">{{Form::text('country', $country, array('readonly'))}}</td>
	</tr>
	<tr>
		<td>{{Form::label('league', 'League')}}</td>
		<td>{{Form::text('league', $league, array('readonly'))}}</td>
	</tr>
	<tr>
		<td>{{Form::label('seasoncount', 'Available seasons')}}</td>
		<td>{{Form::text('seasoncount', $seasoncount, array('readonly'))}} <abbr title="To make more seasons available add /{country}/{league}/{number} to the url. Ex: /sweden/allsvenskan/8." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('seasonfrom', 'From season')}}</td>
		<td>{{Form::text('seasonfrom', $seasonfrom)}} <abbr title="Season offset. Larger integer represents older year up to {Available seasons}. Ex. From season 2 to season 0 means 2011/2012 - 2012/2013 - 2013/2014." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('season', 'To season')}}</td>
		<td>{{Form::text('season', $season)}} <abbr title="Season offset. Larger integer represents older year up to {Available seasons}. Ex. From season 2 to season 0 means 2011/2012 - 2012/2013 - 2013/2014." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('count', 'Length')}}</td>
		<td>{{Form::text('lt', $lt, array('class' => 'direction'))}}{{Form::text('count', $count, array('class' => 'length'))}} <abbr title="Type of series to play. Any series outside the selected range will be discarded. Ex. > 2 means a team will need to have at leat two matches in a row without a draw to be included." class="initialism text-muted">?</abbr>
</td>
	</tr>
	<tr>
		<td>{{Form::label('offset', 'Starting round')}}</td>
		<td>{{Form::text('offset', $offset)}} <abbr title="Round offset. Ex. 15 will start the simulation from round 15 of the season entered above." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('bsf', 'BSF')}}</td>
		<td>{{Form::text('bsf', $bsf)}} <abbr title="Bet so far. The amount of money а serie is worth." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('multiply', 'Multiplier')}}</td>
		<td>{{Form::text('multiply', $multiply)}} <abbr title="Multiplies BSF to represent the amount of money with which a serie becomes more expensive with each round." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('init', 'Initial €')}}</td>
		<td>{{Form::text('init', $init)}} <abbr title="The amount of money a serie starts with. Initial worth of a serie." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('auto', 'Automatic mode')}}</td>
		<td>{{Form::checkbox('auto', 'true', true, array('class' => 'direction'))}}{{Form::text('from', $from, array('class' => 'direction'))}}{{Form::text('to', $to, array('class' => 'direction'))}} <abbr title="Automatic mode attempts to keep the number of active series between the represented numbers at all times." class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td>{{Form::label('rounds', 'Rounds for reset')}}</td>
		<td>{{Form::text('rounds', $rounds)}} <abbr title="A comma separated list. The simulator will reset BSF and Bet to default values at those rounds. Ex. 11,15,22,30" class="initialism text-muted">?</abbr></td>
	</tr>
	<tr>
		<td></td>
		<td>{{Form::submit('Start')}}</td>
	</tr>
		{{Form::close()}}
</table>

	@if(isset($data))
		<table id="sim">
		<thead>
			<tr>
				<th>season</th>
				<th>round</th>
				<th>bsf</th>
				<th>adjustments</th>
				<th>bet</th>
				<th>acc</th>
				<th>matches</th>
				<th>series</th>
				<th>income</th>
				<th>acc state</th>
				<th>cash out</th>
				<th>profit</th>
				<th>filter</th>
				<th>week</th>
			</tr>
		</thead>
		<tbody>
			@foreach($data as $season=>$dd)
				@foreach($dd as $wn=>$d)
					<tr>
						<td>{{$season}}</td>
						<td>{{$d['round']}}</td>
						<td>{{ round($d['bsf'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ round($d['adj'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ round($d['bet'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ round($d['acc'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{$d['all_draws']}} ({{$d['all_matches']}})</td>
						<td>{{$d['draws_played']}} ({{$d['all_played']}})</td>
						<td>{{ round($d['income'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ round($d['real'], 0, PHP_ROUND_HALF_UP) }}</td>
						<!-- @if (isset($d['removed_bsf']))
						<td>{{ $d['removed_bsf'] }}</td>
						@else
						<td>-</td>
						@endif -->
						<td>{{ round($d['out'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ round($d['outminadj'], 0, PHP_ROUND_HALF_UP) }}</td>
						<td>{{ $d['filter'] }}</td>
						<td>{{ $wn }}</td>
					</tr>
				@endforeach
			@endforeach
			</tr>
		</tbody>
	</table>

	<script type="text/javascript">
	function fnFormatDetails ( oTable, nTr )
	{
		var text = '';
		var aData = oTable.fnGetData( nTr );
		var team = '';
		if (aData[5].indexOf("<strong>") > -1) {
			  var re = new RegExp("<strong>(.*?)\\s<");
			  var m = re.exec(aData[5]);
			  team = m[1];
		} else if (aData[7].indexOf("<strong>") > -1) {
			  var re = new RegExp("<strong>(.*?)\\s<");
			  var m = re.exec(aData[7]);
			  team = m[1];
		}
		var promise = testAjax(team, aData[2]);
		promise.success(function (data) {
		  text = data;
		});
		return text;
	}


	$( "tbody>tr" ).hover(
		function() {
			var claz = $(this).attr('class');
			var st = claz.split(' ');
			var firstClass = st[0];

			var id="."+firstClass;
			//alert(id);
			if ($(id).length > 1) {
				$(id+">td").addClass("text-danger");
			}
			//$(id).attr("style", "color: red");
			//$( this ).append( $( "<span> ***</span>" ) );
		}, function() {
			var claz = $(this).attr('class');
			var st = claz.split(' ');
			var firstClass = st[0];

			var id="."+firstClass;
			//alert(id);
			$(id+">td").removeClass("text-danger");
			//$(id).addClass("test");			
		}
	);

	var asInitVals = new Array();

	$(document).ready(function(){
		var oTable = $("#sim").dataTable({
	    	    "iDisplayLength": 100,
	    	    "bJQueryUI": true,
	    	    "sPaginationType": "full_numbers",
	    	    "sDom": '<"top" Tlf>irpti<"bottom"pT><"clear">',
					"oTableTools": {
						"sSwfPath": "/swf/copy_csv_xls_pdf.swf"
					}
			});
		$("thead input").keyup( function () {
		/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $("thead input").index(this));
		} );
		
		/*
		 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
		 * the footer
		 */
		$("thead input").each( function (i) {
			asInitVals[i] = this.value;
		} );
		
		$("thead input").focus( function () {
			if ( this.className == "search_init" )
			{
				this.className = "";
				this.value = "";
			}
		} );
		
		$("thead input").blur( function (i) {
			if ( this.value == "" )
			{
				this.className = "search_init";
				this.value = asInitVals[$("thead input").index(this)];
			}
		} );
	});
	</script>
	@endif
	@if(isset($time)) 
		Time elapsed: {{$time}} sec
	@endif
@stop
