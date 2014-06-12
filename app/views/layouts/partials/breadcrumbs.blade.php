<div class="row">
	<ol class="breadcrumb noPadding">
	@foreach(array_get($elements, 'list') as $name => $url)
		<li><a href="{{$url}}">{{$name}}</a></li>
	@endforeach
	<li class="active">{{array_get($elements, 'active')}}</li>
	</ol>
	<div class="pull-right">
		@if(isset($group))
		<a href="/group/{{$group}}/odds">refresh odds</a> |
		@endif
		@if(isset($ppm) && $ppm == true && isset($from) && isset($to))
		<a href="/ppm/{{$from}}/{{$to}}/odds">refresh odds</a> |
		@endif
		@if(isset($save) && $save)
		<a id="save" href="#" class="text-danger">save</a> |
		@endif
		<span>{{ Auth::user()->name }} | <a href="/settings">settings</a> | <a href="/logout">log out</a></span>
	</div>
</div>