<div class="col-xs-3" style="padding-top:1px;text-align:right;">
	@if(isset($fromdate) && isset($todate)) 
	<span><a href="/{{$base}}/{{date('Y-m-d', strtotime($fromdate) - 86400)}}/{{date('Y-m-d', strtotime($fromdate) - 86400)}}" class="btn btn-default"><</a></span>&nbsp;<span><a href="/{{$base}}/{{date('Y-m-d', time())}}/{{date('Y-m-d', time())}}" class="btn btn-default">today</a></span><span><a href="/{{isset($all_link)?$all_link:$base}}" class="btn btn-default">all</a></span>&nbsp;<span><a href="/{{$base}}/{{date('Y-m-d', strtotime($fromdate) + 86400)}}/{{date('Y-m-d', strtotime($fromdate) + 86400)}}" class="btn btn-default">></a></span>
	@else
	<span><a href="/{{$base_minus}}" class="btn btn-default"><</a></span>&nbsp;<span><a href="/{{$base}}" class="btn btn-default">today</a>&nbsp;<a href="/{{$base_plus}}" class="btn btn-default">></a>
	@endif
</div>
<form id="dateform" action="{{$base}}" method="get"></form>
<div class="col-xs-3 noPadding">
  <!-- calendar -->
  <div class="form-group pull-right noMargin" id="datepickid">
    <div class="input-group date">
		<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
    	<input id="date" type="text" class="form-control input-sm" style="height: 36px; font-size: 110%;" readonly="readonly">
		<span class="input-group-addon"><a id="go" href="#" class="btn btn-xs">GO</a></span>
    </div>
  </div>
</div>

<script>

$(document).ready(function(){
	var dp = $('#datepickid div').datepicker({
			format: "dd.mm.yy",
			weekStart: 1,
			orientation: "top left",
			// autoclose: false,
			todayHighlight: true,
			multidate: 2,
			multidateSeparator: " to ",
			beforeShowDay: function (date) {
			  // if (date.getMonth() == (new Date()).getMonth())
			    // switch (date.getDate()){
			    //   case 4:
			    //     return {
			    //       tooltip: 'Example tooltip',
			    //       classes: 'text-danger'
			    //     };
			    //   case 8:
			    //     return false;
			    //   case 12:
			    //     return "green";
			    // }
			}
		});


    $("#go").on("click", function(){
		// alert(dp.datepicker('getDates')[0]+" - "+dp.datepicker('getDates')[1]);
		var d = new Date(dp.datepicker('getDates')[0]);
		var d1 = new Date(dp.datepicker('getDates')[1]);
		if (d1 == 'Invalid Date') {
            d1 = d;
        }
		// alert(dateFormat(date, 'yyyy-mm-dd'));
		var curr_date = d.getDate();
		var curr_month = d.getMonth() + 1; //Months are zero based
		if (curr_month < 10) {
			curr_month = '0'+curr_month;
		}
		if (curr_date < 10) {
			curr_date = '0'+curr_date;
		}
		var curr_year = d.getFullYear();
		var curr_date1 = d1.getDate();
		var curr_month1 = d1.getMonth() + 1; //Months are zero based
		if (curr_month1 < 10) {
			curr_month1 = '0'+curr_month1;
		}
		if (curr_date1 < 10) {
			curr_date1 = '0'+curr_date1;
		}
		var curr_year1 = d1.getFullYear();
		var dstr = curr_year+"-"+curr_month+'-'+curr_date;
		var dstr1 = curr_year1+"-"+curr_month1+'-'+curr_date1;
		if (dstr1 >= dstr) {
			var a = "/{{$base}}/"+dstr+"/"+dstr1;
		} else {
			var a = "/{{$base}}/"+dstr1+"/"+dstr;
		}
		// alert(a);
		// $("#dateform").attr('action', a);
		// $("#dateform").submit();
		window.location.href = a;
	});
});
</script>