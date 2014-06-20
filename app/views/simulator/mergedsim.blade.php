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
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => ' :: ', 'small' => ""))
@stop

@section('content')

<table class='simulator'>
	<tr>
		<th style="text-align:center;">country</th>
		<th style="text-align:center;">league</th>
		<th style="text-align:center;">multiplier <abbr title="Multiplies BSF to represent the amount of money with which a serie becomes more expensive with each round." class="initialism text-muted">?</abbr></th>
		<th style="text-align:center;">initial <abbr title="The amount of money a serie starts with. Initial worth of a serie." class="initialism text-muted">?</abbr></th>
		<th style="text-align:center;">resets <abbr title="A comma separated list. The simulator will reset BSF and Bet to default values at those rounds. Ex. 11,15,22,30" class="initialism text-muted">?</abbr></th>
	</tr>
		{{Form::open(array('url' => "/simulatormerged"))}}
		{{Form::hidden('count', isset($count)?$count:"")}}
	@for($i = 0; $i < $count; $i ++)
	<tr>
		<td>{{Form::text('country[]', isset($country[$i])?$country[$i]:"")}}</td>
		<td>{{Form::text('league[]', isset($league[$i])?$league[$i]:"")}}</td>
		<td>{{Form::text('multiply[]', isset($multiply[$i])?$multiply[$i]:"")}}</td>
		<td>{{Form::text('init[]', isset($init[$i])?$init[$i]:"")}}</td>
		<td>{{Form::text('rounds[]', isset($rounds[$i])?$rounds[$i]:"")}}</td>
	</tr>
	@endfor
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
				<th></th>
				<th>week</th>
				@for($i = 0; $i < $count; $i ++)
				<th>bsf</th>
				<th>adjustments</th>
				<th>bet</th>
				<th>acc</th>
				<th>series</th>
				<th>acc state</th>
				<th>cash out</th>
				<th>profit</th>
				@endfor
			</tr>
		</thead>
		<tbody>
				<?php
					$t = 0; 
					$k = 53;
				?>
				@for($j = 25; $j < $k; $j ++)
					<tr>
					@if(array_key_exists($j, $data))
						<td></td>
						<td>{{ $j }}</td>
						@foreach($data[$j] as $c => $d)
						<td>{{round($d['bsf'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{round($d['adj'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{round($d['bet'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{round($d['acc'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{$d['draws_played']}} ({{$d['all_played']}})</td>
						<td>{{round($d['real'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{round($d['out'], 0, PHP_ROUND_HALF_UP)}}</td>
						<td>{{round($d['outminadj'], 0, PHP_ROUND_HALF_UP)}}</td>
						@endforeach
					@endif
					</tr>
					<?php
						if ($t == 0 && $j == 52) {
							$t += 1;
							$j = 1;
							$k = 26;
						}
					?>
				@endfor
		
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
@stop