<style type="text/css">
	.centeredBox {
		text-align: center;
		position: fixed;
		top: 40%;
		left: 50%;
		width: 160px;
		height: 80px;
		margin-left: -80px;
		margin-top: -40px;
	}
	.centeredBox input {
		border: 1px solid #ccc;
		height: 24px;
		line-height: 1em;
		margin-bottom: 1px;
	}
</style>

	@if (Session::get('flash_message'))
		{{ Session::get('flash_message') }}
	@endif
<div class="centeredBox">
{{ Form::open (array('route' => 'sessions.store'))}}
	<!-- {{ Form::label ('name', 'Username:') }} -->
	{{ Form::text ('name') }}

	<!-- {{ Form::label ('password', 'Password:') }} -->
	{{ Form::password ('password') }}

	{{ Form::submit() }}
{{ Form::close() }}
</div>