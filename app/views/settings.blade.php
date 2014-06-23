@extends('layout')

@section('breadcrumbs')
	<?php
		$list = array('home' => URL::to("home"));
		$active = 'settings';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => false, 'big' => "Settings"))
@stop

@section('content')
<!-- tabbed nav -->
<ul class="nav nav-pills" id="myTab" style="border: none;">
  <li class='active'><a href="#myppsleagues">My PPS Leagues</a></li>
  <li><a href="#myppmleagues">My PPM Leagues</a></li>
  <li><a href="#mybookmakers">My Bookmakers</a></li>
  <li><a href="#personal">Personal</a></li>
</ul>

<form id="settingsform" method="post" action="settings/save">

<h6>PPS</h6>
<div class="row">
    <table class="table-bordered" style="margin-bottom: 20px; width: 100%;">
        <tr>
            <th style="width: 15%;text-align: center;">Country::League</th>
            <th style="width: %;text-align: center;">1x2</th>
            <th style="width: %;text-align: center;">0:0</th>
            <th style="width: %;text-align: center;">1:1</th>
            <th style="width: %;text-align: center;">2:2</th>
        </tr>
        <tr>
            <td><p class="text-center">Lithuania :: A Lyga</p></td>
            <td style="padding-left: 10px;"><div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Disabled
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Automatic
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Fixed
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
            </td>
            <td style="padding-left: 10px;"><div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Disabled
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Automatic
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Fixed
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
            </td>
            <td style="padding-left: 10px;"><div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Disabled
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Automatic
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Fixed
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
            </td>
            <td style="padding-left: 10px;"><div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Disabled
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Automatic
                    </label>
                    <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                        Fixed
                    </label>
                        <input type="text" name="optionsRadios" id="optionsRadios2" value="2" style="width: 25px;"> to <input type="text" name="optionsRadios" id="optionsRadios2" value="6" style="width: 25px;"> at <input type="text" name="optionsRadios" id="optionsRadios2" value="0.9" style="width: 25px;">
                </div>
            </td>
      </tr>
    </table>
</div>


<div id='content' class="tab-content">
	<!-- tab::myleagues -->
	<div class="tab-pane active" id="myppsleagues">
		<?php $i = 0; ?>
			@foreach($pps as $country=>$leagues)
				<div class="col-xs-12 noPadding">
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
					    <div class="panel-heading">
				      <h4 class="panel-title">
				        <a data-toggle="collapse" data-parent="#accordion" href="#{{ $country }}">
				          {{ $country }}
				        </a>
				      </h4>
					    </div>
					    <div id="{{ $country }}" class="panel-collapse">
				      <div class="panel-body">
						<table id="pps" class="table table-bordered">
							<tr>
							  <td>
							  	&nbsp;
							  </td>
							  <td style="text-align:center;width: 250px;">
							  	<abbr title="Enables leagues series in the play per series mode at the specified legnth">1x2</abbr> (<abbr title="Number of matches before a series becomes active. Example: If Liverpool has not made a draw for 3 matches in a row and length is set to 3 the next Liverpool match will become available to play.">?</abbr>) 
							  </td>
							  <td>
							  	0:0
							  </td>
							  <td>
							  	1:1
							  </td>
							  <td>
							  	2:2
							  </td>
							</tr>
							  @foreach($leagues as $name=>$s)
							<tr class="pps-settings" id="{{$s[0]}}">
							  <td>
							  	{{ $name }}
							  </td>
							  <td id="1">
								  	<p>
                                            <select name="{{$s[0]}}-opt" class="form-control opt" style="width: 100px; height: 30px; font-size: 90%; padding: 3px; display: inline;">
                                              <option value="disabled" {{($s[1]['auto'] == 0)?"selected":""}}>Disabled</option>
                                              <option value="auto" {{($s[1]['auto'] == 1)?"selected":""}}>Automatic</option>
                                              <option value="fixed" {{($s[1]['auto'] == 2)?"selected":""}}>Fixed</option>
                                            </select> <span id='inpts' style="{{($s[1]['auto'] != 1)?'display: none;':''}}"> from <input id="from" name="{{$s[0]}}-from" class="min_start" type="text" style="width: 25px;" value="{{($s[1]['auto'] == 1)?$s[1]['from']:''}}"> to <input id="to" name="{{$s[0]}}-to" class="min_start" type="text" style="width: 25px;" value="{{($s[1]['auto'] == 1)?$s[1]['to']:''}}"> <input id="multiplier" name="{{$s[0]}}-mul" class="min_start" type="text" style="width: 25px;" value="{{($s[1]['auto'] == 1)?$s[1]['multiplier']:''}}"> </span><span id="ltspan" style="{{($s[1]['auto'] != 2)?'display: none;':''}}">from <input id="lt" name="{{$s[0]}}-lt" class="min_start" type="text" style="width: 25px;" value="{{($s[1]['auto'] == 2)?$s[1]['from']:''}}"><input id="multiplier" name="{{$s[0]}}-mul1"class="min_start" type="text" style="width: 25px;" value="{{($s[1]['auto'] == 2)?$s[1]['multiplier']:''}}"></span>
									</p>
								</td>
							  @for($j = 2; $j < 5; $j ++)
								  <td id="{{$j}}">
								  	<p>
				  			  	      	<select class="form-control" style="width: 100px; height: 30px; font-size: 90%; padding: 3px; display: inline;">
										  <option value="disabled">Disabled</option>
										  <option value="enabled">Enabled</option>
										</select> 
									</p>
								  </td>
							  @endfor
							</tr>
							
							@endforeach
						</table>						
				      </div>
					    </div>
			    	</div>
		    	</div>
			</div>
			<?php $i ++; ?>
			@endforeach
	</div>
	<!-- tab::myppmleagues -->
	<div class="tab-pane" id="myppmleagues">
		<div class="row">
			<div class="col-xs-12 noPadding">
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
					    <div class="panel-heading">
				      <h4 class="panel-title">
				        <a data-toggle="collapse" data-parent="#accordion" href="#collapsePPM">
				          PPM
				        </a>
				      </h4>
					    </div>
					    <div id="collapsePPM" class="panel-collapse collapse in">
				      <div class="panel-body">
						<table id="ppm" class="table">
							<tr>
							  <td>
							  	
							  </td>
							  <td>
							  	Series
							  </td>
							  <td>
							  	0:0
							  </td>
							  <td>
							  	1:1
							  </td>
							  <td>
							  	2:2
							  </td>
							</tr>
							
							@foreach($ppm as $country=>$s)

							<tr>
							  <td>
							  	{{ $country }}
							  </td>
							  @for($j = 5; $j < 9; $j ++)
								  <td>
				  			  	      	<input name="ppm[]" class="activate_league_for_play" type="checkbox" value='{{$s[0]}}#{{$j}}' {{(count($s[$j]) > 0)?"checked":""}}> 
								  </td>
							  @endfor
							</tr>

							@endforeach

						</table>						
				      </div>
					    </div>
			    	</div>
		    	</div>
			</div>
		</div>
	</div>
	<!-- tab::mybookmakers -->
	<div class="tab-pane" id="mybookmakers">
		<table class="table table-condensed">
			<tr>
			  <td style="padding-top: 15px; width: 200px;">
				<abbr title="Used as the default to calculate profit and income.">Primary Bookmaker</abbr>
			  </td>
			  <td>
				<select name="default_bookmaker" class="form-control" style="width: 200px;">
				  <option value='1'>bet365</option>
				  <option value='2'>betfair</option>
				  <option value='3'>will hill</option>
				  <option value='5'>unibet</option>
				  <option value='4'>pinnacle sport</option>
				</select>
			  </td>
			</tr>
			<tr>
			  <td style="padding-top: 15px;">
				<abbr title="Which odds to be displayed on the main view.">Display Odds</abbr>
			  </td>
			  <td>
				<div class="btn-group">
				<input type="button" name="bookies-1" value="bet365" class="btn btn-default" data-toggle="button">
				<button value='1' type="button" class="btn btn-default" data-toggle="button">bet365</button>
				<button value='2' type="button" class="btn btn-default" data-toggle="button">betfair</button>
				<button value='3' type="button" class="btn btn-default" data-toggle="button">bwin</button>
				<button value='5' type="button" class="btn btn-default" data-toggle="button">unibet</button>
				<button value='4' type="button" class="btn btn-default" data-toggle="button">pinnacle sport</button>
				</div>
			  </td>
			</tr>
			<tr>
			  <td style="padding-top: 15px;">
				<abbr title="Auto confirms the input in all views upon match end.">Auto confirm</abbr>
			  </td>
			  <td>
				<select class="form-control" style="width: 200px;">
				  <option>Enabled</option>
				  <option>Disabled</option>
				</select>
			  </td>
			</tr>
		</table>
	</div>
	<!-- tab::personal -->
	<div class="tab-pane" id="personal">
		<table class="table table-bordered">
			<tr>
			  <td>
			  	Current Password
			  </td>
			  <td>
			  	<input type="password">
			  </td>
			</tr>
			<tr>
			  <td>
			  	New Password
			  </td>
			  <td>
			  	<input type="password">
			  </td>
			</tr>
			<tr>
			  <td>
			  	Repeat New Password
			  </td>
			  <td>
			  	<input type="password">
			  </td>
			</tr>
		</table>
	</div>
</div>

</form>
<!-- js for tabs -->
<script type="text/javascript">
	$('.opt').change(function(){
		var optionSelected = $(this).find("option:selected");
		var valueSelected  = optionSelected.val();
		if(valueSelected == "auto"){
			$(this).siblings("#inpts").show();
			$(this).siblings("#ltspan").hide();
		} else if (valueSelected == "fixed") {
			$(this).siblings("#ltspan").show();
			$(this).siblings("#inpts").hide();
		} else {
			$(this).siblings("#ltspan").hide();
			$(this).siblings("#inpts").hide();
		}
	});

	$("#save").on('click', function(){
		$("#settingsform").submit();
	});
	
  $('#myTab a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
});
</script>
@stop