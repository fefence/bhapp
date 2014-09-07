<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container noPadding">
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="{{Request::path() == 'pps' ? 'active' : '';}}"><a href="{{ URL::to('pps') }}">pps</a></li>
                <li class="{{Request::path() == 'ppm' ? 'active' : '';}}"><a href="{{URL::to('/ppm')}}">ppm</a></li>
                <li class="{{Request::path() == 'livescore' ? 'active' : '';}}"><a href="{{URL::to('/livescore')}}">live</a></li>
                <li class="{{Request::path() == 'free' ? 'active' : '';}}"><a href="{{URL::to('/free')}}">free <span class="badge">@if(isset($free_count) && $free_count != 0){{$free_count}}@endif</span></a></li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">tools <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li class="{{Request::path() == 'pool' ? 'active' : '';}}"><a href="{{URL::to('/pool')}}">pool</a></li>
                        <li class="{{Request::path() == 'log' ? 'active' : '';}}"><a href="{{URL::to('/log')}}">log</a></li>
                        <li class="{{Request::path() == 'countries' ? 'active' : '';}}"><a href="{{ URL::to('countries') }}">stats</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">Simulators</li>
                        <li><a href="{{URL::to('/simulator')}}">sim</a></li>
                        <li><a href="{{URL::to('/simulatormerged')}}">merged sim</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">Admin</li>
                        <li><a href="{{URL::to('/addleagues')}}">add leagues</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                </li>
                @if(isset($pool))
                <li><p class="navbar-text"><span class="text-default"><abbr title="Profit">{{$pool->profit}}</abbr></span></p></li>
                <li><p class="navbar-text"><span class="text-default"><span id="pool"><abbr title="Account state">{{$pool->account}}</abbr></span></span></p></li>
                <li><p class="navbar-text"><span class="text-default"><abbr title="Pool">{{$pool->amount}}</abbr></span></p></li>
                @else
                @if(isset($global))
                <li><p class="navbar-text"><span class="text-success">{{$global->account}}</span></p></li>
                <li><p class="navbar-text"><span class="text-success">{{$global->amount}}</span></p></li>
                @endif
                @endif
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</div>