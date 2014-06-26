<div class="row">
	<ol class="breadcrumb noPadding">
	@foreach(array_get($elements, 'list') as $name => $url)
		<li><a href="{{$url}}">{{$name}}</a></li>
	@endforeach
	<li class="active">{{array_get($elements, 'active')}}</li>
	</ol>
	<div class="pull-right">
		@if(isset($group))
        <a href="/recalc/{{$group}}">recalc</a> |
        <a href="/confirmall/{{$group}}{{$tail}}">confirm all</a> |
        <a href="/groupodds/{{$group}}">refresh odds</a> |
		@endif
		@if(isset($ppm) && $ppm == true && isset($fromdate) && isset($todate))
		<a href="/ppm/{{$fromdate}}/{{$todate}}/odds">refresh odds</a> |
		@endif
		@if(isset($save) && $save)
		<a id="save" href="#" class="text-danger">save</a> |
		@endif
		<span>{{ Auth::user()->name }} | <a href="/settings">settings</a> | <a href="/logout">log out</a></span>
	</div>
</div>