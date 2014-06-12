@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	<?php
		$list = array();
		$active = 'Home';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Japan simulation", 'small' => 'alpha'))
@stop

@section('content')
<p>
	{{Form::open(array('url' => '/tmp'))}}

	{{Form::label('count', 'Start series length')}}
	{{Form::text('count')}}

	{{Form::label('multiply', 'x')}}
	{{Form::text('multiply')}}

	{{Form::label('init', 'Initial bet')}}
	{{Form::text('init')}}

	{{Form::hidden('round', $round)}}
	{{Form::hidden('season', $season)}}

	{{Form::submit('Start new sim')}}
	{{Form::close()}}
</p>
<p>
	{{Form::open(array('action' => 'MatchController@next'))}}
	{{Form::submit('next')}}<br>

	{{Form::hidden('count', $count)}}
	{{Form::hidden('round', $round)}}
	{{Form::hidden('season', $season)}}
	{{Form::hidden('multiply', $multiply)}}

	{{Form::label('income', 'Income')}}
	{{Form::text('income', $income, array('readonly'))}}<br>
	{{Form::label('count', 'Profit')}}
	{{Form::text('profit', $profit, array('readonly'))}}

	{{Form::close()}}

</p>

	<table id="sim">
		
		<tbody>
			<?php 
				$totalbet = 0;
				$totalbsf = 0;
			?>
			@foreach($data as $d)
				<?php
					$totalbet += $d->bet;
					$totalbsf += $d->bsf;
				?>
				<tr class="{{$d->match_id}}" id="{{$d->match_id}}">
					<td>{{$d->matchDate}}</td>
					<td>{{$d->matchTime}}</td>
					<td>{{$d->round}}</td>
					<td>{{$d->season}}</td>
					<td>
					@if($d->team == $d->home)
					<strong>{{$d->home}}</strong>
					@else
					{{$d->home}}
					@endif
					</td>
					<td>
					@if($d->team == $d->away)
					<strong>{{$d->away}}</strong>
					@else
					{{$d->away}}
					@endif
					</td>
					<td>{{$d->resultShort}}</td>
					<td>{{$d->current_length}}</td>
					<td>{{$d->bsf}}</td>
					<td class="editable" id="{{$d->id}}">{{$d->bet}}</td>
					<td class="editable" id="{{$d->id}}">{{$d->odds}}</td>
					<td>{{$d->income}}</td>
					<td>{{$d->profit}}</td>

				</tr>
			@endforeach
		</tbody>
		<thead>
			<tr>
				<th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="time"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="round"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="season"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="res"></th>
				<th><input type="hidden"></th>
				<th><input type="hidden"></th>
				<th><input type="hidden"></th>
				<th><input type="hidden"></th>
				<th><input type="hidden"></th>
				<th><input type="hidden"></th>
			</tr>
			<tr>
				<th>date</th>
				<th>time</th>
				<th>round</th>
				<th>season</th>
				<th>home</th>
				<th>away</th>
				<th>result</th>
				<th>length</th>
				<th>bsf</th>
				<th>bet</th>
				<th>odds</th>
				<th>income</th>
				<th>profit</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>{{$totalbsf}}</th>
				<th>{{$totalbet}}</th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
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
	    	    "sPaginationType": "full_numbers"
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

		/* Apply the jEditable handlers to the table */
		    oTable.$('td.editable').editable( '/save', {
	        "callback": function( sValue, y ) {
	           //	alert(y[0]);
	            var aPos = oTable.fnGetPosition( this );
	            var arr = sValue.split("#");

	            oTable.fnUpdate( arr[0], aPos[0], 9 );
	           	oTable.fnUpdate( arr[1], aPos[0], 10 );
	            oTable.fnUpdate( arr[2], aPos[0], 11 );
	            oTable.fnUpdate( arr[3], aPos[0], 12 );
	            oTable.fnUpdate( arr[4], aPos[0], 8 );
	    
	            //oTable.fnClearTable();
            	//oTable.fnReloadAjax() ;
	        },
	        "submitdata": function ( value, settings ) {
	            return {
	                "row_id": this.parentNode.getAttribute('id'),
	                "column": oTable.fnGetPosition( this )[2]
	            };
	        },
	        "height": "25px",
	        "width": "40px"
	    } );
	});
	</script>
@stop
