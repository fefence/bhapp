@extends('layout')

@section('breadcrumbs')
	<!-- breadcrumbs -->
	<?php
		$list = array('Home' => URL::to("home"));
		$active = 'livescore';
		$elements = array('active' => $active, 'list' => $list);
	?>
	@include('layouts.partials.breadcrumbs', array('elements' => $elements))
@stop

@section('pageHeader')
	@include('layouts.partials.pageheader', array('calendar' => true, 'big' => "Livescore", 'small' => "28-Apr-14 (Mon)"))
@stop

@section('content')
<table id="parts" class="parts-first vertical"><tbody><tr class="stage-header stage-12"><td colspan="3" class="h-part">1st Half</td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">45'</div><div class="icon-box y-card"><span class="icon y-card">&nbsp;</span></div><span class="participant-name"> Signori F. </span></div></td><td class="score" rowspan="2"><span class="p1_home">0</span> - <span class="p1_away">1</span></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box">45'</div><div class="icon-box soccer-ball"><span class="icon soccer-ball">&nbsp;</span></div><span class="participant-name"> Andelkovic S. </span></div></td></tr><tr class="stage-header stage-13"><td colspan="3" class="h-part">2nd Half</td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="score" rowspan="10"><span class="p2_home">1</span> - <span class="p2_away">0</span></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box">49'</div><div class="icon-box y-card"><span class="icon y-card">&nbsp;</span></div><span class="participant-name"> Bolzoni F. </span></div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">53'</div><div class="icon-box y-card"><span class="icon y-card">&nbsp;</span></div><span class="participant-name"> Mazzarani A. </span></div></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box">60'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> Di Gennaro D.</span><span class="substitution-out-name">Stevanović A.<span class="icon substitution-out">&nbsp;</span></span></div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">67'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> Surraco J.</span><span class="substitution-out-name"><span class="icon substitution-out">&nbsp;</span>Rizzo L.</span></div></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">73'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> Stanco F.</span><span class="substitution-out-name"><span class="icon substitution-out">&nbsp;</span>Babacar K.</span></div></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box">75'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> N'Goyi G.</span><span class="substitution-out-name">Lores I.<span class="icon substitution-out">&nbsp;</span></span></div></td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">82'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> Nardini R.</span><span class="substitution-out-name"><span class="icon substitution-out">&nbsp;</span>Manfrin G.</span></div></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box">85'</div><div class="icon-box substitution-in"><span class="icon substitution-in">&nbsp;</span></div><span class="substitution-in-name"> Troianiello G.</span><span class="substitution-out-name">Dybala P.<span class="icon substitution-out">&nbsp;</span></span></div></td></tr><tr class="odd"><td class="summary-vertical fl"><div class="wrapper"><div class="time-box">86'</div><div class="icon-box soccer-ball"><span class="icon soccer-ball">&nbsp;</span></div><span class="participant-name"> Surraco J. </span></div></td><td class="summary-vertical fr"><div class="wrapper">&nbsp;</div></td></tr><tr class="even"><td class="summary-vertical fl"><div class="wrapper">&nbsp;</div></td><td class="summary-vertical fr"><div class="wrapper"><div class="time-box-wide">90+4'</div><div class="icon-box y-card"><span class="icon y-card">&nbsp;</span></div><span class="participant-name"> Terzi C. </span></div></td></tr></tbody></table><div id="secret_hash"></div>
@stop