<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container noPadding">
		<div class="navbar-collapse collapse">
		  <ul class="nav navbar-nav">
		    <li class="{{Request::path() == 'home' ? 'active' : '';}}" ><a href="{{ URL::to('home') }}">pps</a></li>
		    <li><a href="{{URL::to('/ppm')}}">ppm</a></li>
		    <li><a href="{{URL::to('/livescore')}}">livescore</a></li>
		    <li><a href="{{URL::to('/poolmanagement')}}">pool management</a></li>
		    <li class="{{Request::path() == 'countries' ? 'active' : '';}}"><a href="{{ URL::to('countries') }}">stats</a></li>
		    
		    <li class="dropdown">
		      <a href="#" class="dropdown-toggle" data-toggle="dropdown">tools <b class="caret"></b></a>
		      <ul class="dropdown-menu">
		        <li><a href="{{URL::to('/simulator')}}">sim</a></li>
		        <li><a href="{{URL::to('/addleagues')}}">add leagues</a></li>
		        <li><a href="{{URL::to('/simulatormerged')}}">merged sim</a></li>
		        <li class="divider"></li>
		        <li class="dropdown-header">Nav header</li>
		        <li><a href="#">Separated link</a></li>
		        <li><a href="#">One more separated link</a></li>
		      </ul>
		    </li>
		  </ul>
		  <ul class="nav navbar-nav navbar-right">
		    <li class="dropdown">
<!-- 		      <a href="#" class="dropdown-toggle" data-toggle="dropdown">dropdown <b class="caret"></b></a>
		      <ul class="dropdown-menu">
		        <li><a href="#">Action</a></li>
		        <li><a href="#">Another action</a></li>
		        <li><a href="#">Something else here</a></li>
		        <li class="divider"></li>
		        <li class="dropdown-header">Nav header</li>
		        <li><a href="#">Separated link</a></li>
		        <li><a href="#">One more separated link</a></li>
		      </ul> -->
		    </li>
		    @if(isset($pool))
		    <li><p class="navbar-text"><span class="text-default">P: {{$pool->amount}} (<strong>{{$pool->current}}</strong>)</span></p></li>
		    <li><p class="navbar-text"><span class="text-default">I: {{$pool->income}}</span></p></li>
		    @endif
		    @if(isset($global))
		    <li><p class="navbar-text"><span class="text-success">{{$global->amount}}</span></p></li>
		    <li><p class="navbar-text"><span class="text-success">{{$global->income}}</span></p></li>
		    @endif
		  </ul>
		</div><!--/.nav-collapse -->
	</div>
</div>