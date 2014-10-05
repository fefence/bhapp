<div class="row">
	<div class="col-xs-5 noPadding">
	<!-- main content -->
		<div class="page-header">
			<h3 style="margin: 0px 0px 10px 0px;">{{ $big }}
			@if(isset($small))
				<small>{{ $small }}</small>
			@endif
			</h3>
		</div>
	</div>
	@if ($calendar)
	@include('layouts.partials.calendar')
	@endif
</div>