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
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => "Today's matches", 'small' => '28-Apr-14 (Mon)'))
@stop

@section('content')
	<table id="matches">
		<thead>
			<tr>
				<th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="home"></th>
				<th><input type="hidden"></th>
				<th><input type="text" name="search_engine" class="search_init" placeholder="away"></th>
			</tr>
			<tr>
				<th>date</th>
				<th>home</th>
				<td>-</td>
				<th>away</th>
			</tr>
		</thead>
		<tbody>
			@foreach($data as $d)
				<tr>
					<td>{{$d['matchDate']}}</td>
					<td>{{$d['home']}}</td>
					<td>-</td>
					<td>{{$d['away']}}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	<script type="text/javascript">
	var asInitVals = new Array();

	$(document).ready(function(){
		var oTable = $("#matches").dataTable({
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
		    oTable.$('td.editable').editable( '#', {
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
	        }
	    } );
	});
	</script>
@stop
