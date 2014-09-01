@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->

	<?php
		$list = array();
		$active = 'action log';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => 'Action log', 'small' => ''))
@stop

@section('content')
<table id="matches" style="margin-bottom: 30px;">
    <thead>
    <tr>
        <th><input type="text" name="search_engine" class="search_init" placeholder="date"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="user"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="type"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="action"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="amount"></th>
        <th><input type="text" name="search_engine" class="search_init" placeholder="description"></th>
    </tr>
    <tr>
        <th>date</th>
        <th>user</th>
        <th>type</th>
        <th>action</th>
        <th>amount</th>
        <th>description</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $id => $d)
    <tr>
        <td>{{$d['log']->created_at}}</td>
        <td>{{$d['user']}}</td>
        <td>{{$d['log']->type}}</td>
        <td>{{$d['log']->action}}</td>
        <td>{{$d['log']->amount}}</td>
        <td>{{$d['descr']}}</td>
    </tr>
    @endforeach

    </tbody>
</table>
<script type="text/javascript">
        var asInitVals = new Array();

    $(document).ready(function () {

        var oTable = $("#matches").dataTable({
            "iDisplayLength": 100,
            "bJQueryUI": true,
            "sDom": '<"top"i>t<"bottom"><p"clear">',
            "sPaginationType": "full_numbers"
        });


        $("thead input").keyup(function () {
            /* Filter on the column (the index) of this element */
            oTable.fnFilter(this.value, $("thead input").index(this));
        });

        /*
         * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
         * the footer
         */
        $("thead input").each(function (i) {
            asInitVals[i] = this.value;
        });

        $("thead input").focus(function () {
            if (this.className == "search_init") {
                this.className = "";
                this.value = "";
            }
        });

        $("thead input").blur(function () {
            if (this.value == "") {
                this.className = "search_init";
                this.value = asInitVals[$("thead input").index(this)];
            }
        });

        /* Apply the jEditable handlers to the table */

    });
</script>
@stop
