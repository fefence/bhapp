@extends('layout')

@section('breadcrumbs')
    <!-- breadcrumbs -->
    <?php
      $list = array('home' => URL::to("home"), 
                    'countries' => URL::to('countries'), 
                    array_get($data, 'country') => "/".array_get($data, 'country'),
                    array_get($data, 'league') => "/".array_get($data, 'country')."/".array_get($data, 'league')."/archive");
      $active = array_get($data, 'season');
      $elements = array('active' => $active, 'list' => $list);
    ?>
  @include('layouts.partials.breadcrumbs', array('elements' => $elements))

@stop

@section('pageHeader')
  @include('layouts.partials.pageheader', array('calendar' => true, 'big' => array_get($data, 'country')." - ".array_get($data, 'league')))
@stop

@section('content')
<!-- tabbed nav -->
<ul class="nav nav-tabs" id="myTab" style="border: none">
  <li><a href="#summary">Summary</a></li>
  <li><a href="#teamsForm">Form</a></li>
  <li><a href="#teamsHome">Home</a></li>
  <li><a href="#teamsAway">Away</a></li>
  <li class="active"><a href="#1x2">1x2</a></li>
  <li><a href="#exactscore">Exact Score</a></li>
  <li><a href="#goals">Goals</a></li>
  <li><a href="#goalsscored">Scored</a></li>
</ul>

  <div id='content' class="tab-content">
<!-- table standings -->
    <div class="tab-pane" id="summary">
      <table class="table table-bordered">
        <tr>
          <td>standings</td>
        </tr>
      </table>
    </div>

<!-- table teams form -->
    <div class="tab-pane" id="teamsForm">
      <table class="table table-bordered">
        <tr>
          <td>teams form</td>
        </tr>
      </table>
    </div>

<!-- table teams home stats -->
    <div class="tab-pane" id="teamsHome">
      <table class="table table-bordered">
        <tr>
          <td>teams home stats</td>
        </tr>
      </table>
    </div>

<!-- table teams away stats -->
    <div class="tab-pane" id="teamsAway">
      <table class="table table-bordered">
        <tr>
          <td>teams away stats</td>
        </tr>
      </table>
    </div>
<!-- table 1x2 -->
    <div class="tab-pane active" id="1x2">
      <table id="dt" class="table table-bordered">
        <thead>
        <tr>
            <th>League progress</th>
            <th width="25%"># of matches</th>
            <th width="25%">%</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>Matches total</td>
          <td>{{ array_get($data, 'all') }}</td>
          <td>100 %</td>
        </tr>
        <tr>
          <td>Home win</td>
          <td>{{ array_get($data, 'home') }}</td>
          <td>{{ round(array_get($data, 'home')/array_get($data, 'all')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        <tr>
          <td>Draw</td>
          <td>{{ array_get($data, 'draw') }}</td>
          <td>{{ round(array_get($data, 'draw')/array_get($data, 'all')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        <tr>
          <td>Away win</td>
          <td>{{ array_get($data, 'away') }}</td>
          <td>{{ round(array_get($data, 'away')/array_get($data, 'all')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        </tbody>
      </table>
  </div>

<!-- table exact score -->
  <div class="tab-pane" id="exactscore">
    <table class="table table-bordered">
      <tr>
          <th>Exact score</th>
          <th width="25%"># of occurences</th>
          <th width="25%">%</th>
      </tr>
        @foreach(array_get($data, 'distResults') as $dist)
        <tr>
            <td>{{ $dist->homeGoals }} - {{ $dist->awayGoals }}</td>
            <td>{{ $dist->total }}</td>
            <td>{{ round($dist->total/array_get($data, 'all')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        @endforeach
    </table>
  </div>

<!-- table goals scored -->
  <div class="tab-pane" id="goals">
    <table class="table table-bordered">
      <tr>
          <th>Goals</th>
          <th width="25%">Total</th>
          <th width="25%">Per Match</th>
      </tr>
      <tr>
        <td>Goals scored</td>
        <td>{{ array_get($data, 'goals') }}</td>
        <td>100 %</td>
      </tr>
      <tr>
        <td>Home goals</td>
        <td>{{ array_get($data, 'homeGoals') }}</td>
            <td>{{ round(array_get($data, 'homeGoals')/array_get($data, 'goals')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
      </tr>
      <tr>
        <td>Away goals</td>
        <td>{{ array_get($data, 'awayGoals') }} </td>
            <td>{{ round(array_get($data, 'awayGoals')/array_get($data, 'goals')*100, 2, PHP_ROUND_HALF_UP) }} %</td>
      </tr>
    </table>
  </div>

<!-- table goals scored -->
  <div class="tab-pane" id="goalsscored">
    <table class="table table-bordered">
      <tr>
          <th>Over/Under 2.5 stats</th>
          <th width="25%">Total</th>
          <th width="25%">%</th>
      </tr>
      <tr>
        <td>Over 2.5</td>
        <td>{{ array_get($data, 'over') }}</td>
        <td>?? %</td>
      </tr>
      <tr>
        <td>Under 2.5</td>
        <td>{{ array_get($data, 'under') }}</td>
        <td>?? %</td>
      </tr>
    </table>
  </div>
</div>

<ul class="nav nav-tabs" id="myTab2" style="border: none">
  <li class='active'><a href="#ppsseq">PPS</a></li>
  <li><a href="#pps1x2">PPS 1X2</a></li>
  <li><a href="#pps00">PPS 0-0</a></li>
  <li><a href="#pps11">PPS 1-1</a></li>
  <li><a href="#pps22">PPS 2-2</a></li>
  @if(array_get($data, 'ppm') == 1)
    <li><a href="#ppmseq">PPM</a></li>
    <li><a href="#ppm1x2">PPM 1X2</a></li>
    <li><a href="#ppm00">PPM 0-0</a></li>
    <li><a href="#ppm11">PPM 1-1</a></li>
    <li><a href="#ppm22">PPM 2-2</a></li>
  @endif
</ul>

<div id='content' class="tab-content">


<!-- table PPS sequences -->
    <div class="tab-pane active" id="ppsseq">
      <table class="table table-bordered">
      <thead>
        <tr>
          <td></td>
          <td>
          @for($i = 1; $i <= array_get($data, 'count'); $i ++)
            <a href="#" type="button" class="btn btx-xs btn-default w25 w25heading">{{ $i }}</a>
          @endfor
          </td>
        </tr>
      </thead>
      <tbody>
      @foreach(array_get($data, 'seq') as $team => $seq)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($seq as $s)
                  <?php
                    $d = array('team' => $team, 'match' => $s);
                  ?>
                @include('layouts.partials.square', array('data' => $d))
              @endforeach
              </td>
          </tr>
      @endforeach
        <tr>
          <td></td>
          <td>
          @for($i = 1; $i <= array_get($data, 'count'); $i ++)
            <a href="#" type="button" class="btn btx-xs btn-default w25 w25heading">{{ $i }}</a>
          @endfor
          </td>
        </tr>
      </tbody>
      </table>
    </div>

<!-- table PPS 1x2 sequences -->
    <div class="tab-pane" id="pps1x2">
      <table class="table table-bordered">
      @foreach(array_get($data, 'pps1x2') as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)
                  <?php
                    $d = array('team' => $team, 'match' => $s);
                  ?>
                @if($s->current_length > 1)
                  {{ $s->current_length - 1}}
                @endif
                @include('layouts.partials.square', array('data' => $d))
              @endforeach
              </td>
          </tr>
      @endforeach
      </table>
    </div>
<!-- table PPS 0-0 sequences -->
    <div class="tab-pane" id="pps00">
      <table class="table table-bordered">
      @foreach(array_get($data, 'pps00') as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)
                  <?php
                    $m = new Match;
                    $m->home = $s->home;
                    $m->away = $s->away;
                    $m->matchDate = $s->matchDate;
                    $m->matchTime = $s->matchTime;
                    $m->resultShort = $s->resultShort;
                    $m->homeGoals = $s->homeGoals;
                    $m->awayGoals = $s->awayGoals;
                    $d = array('team' => $team, 'match' => $m);
                  ?>
                @if($s->current_length > 1)
                  {{ $s->current_length - 1}}
                @endif
                @include('layouts.partials.square', array('data' => $d))
              @endforeach
              </td>
          </tr>
      @endforeach
      </table>
    </div>
<!-- table PPS 1x2 sequences -->
    <div class="tab-pane" id="pps11">
      <table class="table table-bordered">
      @foreach(array_get($data, 'pps11') as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)
                  <?php
                    $m = new Match;
                    $m->home = $s->home;
                    $m->away = $s->away;
                    $m->matchDate = $s->matchDate;
                    $m->matchTime = $s->matchTime;
                    $m->resultShort = $s->resultShort;
                    $m->homeGoals = $s->homeGoals;
                    $m->awayGoals = $s->awayGoals;
                    $d = array('team' => $team, 'match' => $m);
                  ?>
                @if($s->current_length > 1)
                  {{ $s->current_length - 1}}
                @endif
                @include('layouts.partials.square', array('data' => $d))
              @endforeach
              </td>
          </tr>
      @endforeach
      </table>
    </div>
<!-- table PPS 1x2 sequences -->
    <div class="tab-pane" id="pps22">
      <table class="table table-bordered">
      @foreach(array_get($data, 'pps22') as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)
                  <?php
                    $m = new Match;
                    $m->home = $s->home;
                    $m->away = $s->away;
                    $m->matchDate = $s->matchDate;
                    $m->matchTime = $s->matchTime;
                    $m->resultShort = $s->resultShort;
                    $m->homeGoals = $s->homeGoals;
                    $m->awayGoals = $s->awayGoals;
                    $d = array('team' => $team, 'match' => $m);
                  ?>
                @if($s->current_length > 1)
                  {{ $s->current_length - 1}}
                @endif
                @include('layouts.partials.square', array('data' => $d))
              @endforeach
              </td>
          </tr>
      @endforeach
      </table>
    </div>

<!-- table PPM sequence -->
    <div class="tab-pane" id="ppmseq">
      <table class="table table-bordered">
        <tr>
          <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;<span class="text-danger">Top 5 longest series: 13, 12, 12, 9, 5 (<-- hardcoded data, must re-visit!)</span></td>
        </tr>
        <tr>
          <td>
            @foreach(array_get($data, 'sSeq') as $sSeq)
              <?php
                $d = array('team' => '', 'match' => $sSeq);
              ?>
              @include('layouts.partials.square', array('data' => $d))
            @endforeach
          </td>
        </tr>
      </table>
    </div>
  </div>
<!-- js for tabs -->
<script type="text/javascript">
  $('#myTab a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
  $('#myTab2 a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>
@stop