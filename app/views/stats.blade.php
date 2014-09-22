@extends('layout')

@section('breadcrumbs')
    <!-- breadcrumbs -->
    <?php
      $list = array('pps' => URL::to("home"));
      $active = $season;
      $elements = array('active' => $active, 'list' => $list);
    ?>
  @include('layouts.partials.breadcrumbs', array('elements' => $elements))

@stop

@section('pageHeader')
  @include('layouts.partials.pageheader', array('calendar' => false, 'big' => "$country - $league"))
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
          <td>{{ $all }}</td>
          <td>100 %</td>
        </tr>
        <tr>
          <td>Home win</td>
          <td>{{ $home }}</td>
          <td>{{ round($home/$all*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        <tr>
          <td>Draw</td>
          <td>{{ $draw }}</td>
          <td>{{ round($draw/$all*100, 2, PHP_ROUND_HALF_UP) }} %</td>
        </tr>
        <tr>
          <td>Away win</td>
          <td>{{ $away }}</td>
          <td>{{ round($away/$all*100, 2, PHP_ROUND_HALF_UP) }} %</td>
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
        @foreach($distResults as $dist)
        <tr>
            <td>{{ $dist->homeGoals }} - {{ $dist->awayGoals }}</td>
            <td>{{ $dist->total }}</td>
            <td>{{ round($dist->total/$all*100, 2, PHP_ROUND_HALF_UP) }} %</td>
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
        <td>{{ $goals }}</td>
        <td>100 %</td>
      </tr>
      <tr>
        <td>Home goals</td>
        <td>{{ $homeGoals }}</td>
            <td>{{ round($homeGoals/$goals*100, 2, PHP_ROUND_HALF_UP) }} %</td>
      </tr>
      <tr>
        <td>Away goals</td>
        <td>{{ $awayGoals }} </td>
        <td>{{ round($awayGoals/$goals*100, 2, PHP_ROUND_HALF_UP) }} %</td>
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
        <td>{{ $over }}</td>
        <td>?? %</td>
      </tr>
      <tr>
        <td>Under 2.5</td>
        <td>{{ $under }}</td>
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
  @if($ppm == 1)
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
          @for($i = 1; $i <= $count; $i ++)
            <a href="#" type="button" class="btn btx-xs btn-default w25 w25heading">{{ $i }}</a>
          @endfor
          </td>
        </tr>
      </thead>
      <tbody>
      @foreach($seq as $team => $seq)
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
          @for($i = 1; $i <= $count; $i ++)
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
      @foreach($pps1x2 as $team => $series)
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
      @foreach($pps00 as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)

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
      @foreach($pps11 as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)

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
      @foreach($pps22 as $team => $series)
          <tr>
              <td><strong>{{$team}}</strong></td>
              <td>
              @foreach($series as $s)
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
            @foreach($sSeq as $sSeq)
              <?php
                $d = array('team' => '', 'match' => $sSeq);
              ?>
              @include('layouts.partials.square', array('data' => $d))
            @endforeach
          </td>
        </tr>
      </table>
    </div>
    <div class="tab-pane" id="ppm1x2">
        <table class="table table-bordered">
            <tr>
                <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;
                    <span class="text-danger">Top 5 longest series:
                        @foreach($longest[5] as $l)
                            {{$l-1}},&nbsp;
                        @endforeach
                    </span></td>
            </tr>
            <tr>
                <td>
                    @foreach($ppm1x2 as $s)
                    @if($s->current_length > 1)
                    {{ $s->current_length - 1}}
                    @endif
                    <?php
                    $d = ['team' => '', 'match' => $s]
                    ?>
                    @include('layouts.partials.square', array('data' => $d))

                    @endforeach
                </td>
            </tr>
        </table>
    </div>
    <div class="tab-pane" id="ppm00">
        <table class="table table-bordered">
            <tr>
                <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;
                <span class="text-danger">Top 5 longest series:
                        @foreach($longest[6] as $l)
                            {{$l-1}},&nbsp;
                        @endforeach
                    </span></td>
            </tr>
            <tr>
                <td>
                    @foreach($ppm00 as $s)
                    @if($s->current_length > 1)
                    {{ $s->current_length - 1}}
                    @endif
                    <?php
                    $d = ['team' => '', 'match' => $s]
                    ?>
                    @include('layouts.partials.square', array('data' => $d))

                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    <div class="tab-pane" id="ppm11">
        <table class="table table-bordered">
            <tr>
                <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;
                <span class="text-danger">Top 5 longest series:
                        @foreach($longest[7] as $l)
                            {{$l-1}},&nbsp;
                        @endforeach
                    </span></td>
            </tr>
            <tr>
                <td>
                    @foreach($ppm11 as $s)
                    @if($s->current_length > 1)
                    {{ $s->current_length - 1}}
                    @endif
                    <?php
                        $d = ['team' => '', 'match' => $s]
                    ?>
                    @include('layouts.partials.square', array('data' => $d))
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
<div class="tab-pane" id="ppm22">
    <table class="table table-bordered">
        <tr>
            <td><span class="text-default"><strong>Note:</strong> First match is top left.</span>&nbsp;<span class="text-danger">
            <span class="text-danger">Top 5 longest series:
                        @foreach($longest[8] as $l)
                            {{$l-1}},&nbsp;
                        @endforeach
                    </span></td>
        </tr>
        <tr>
            <td>
                @foreach($ppm22 as $s)
                @if($s->current_length > 1)
                {{ $s->current_length - 1}}
                @endif
                <?php
                $d = ['team' => '', 'match' => $s]
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