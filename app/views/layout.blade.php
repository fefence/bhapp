<!DOCTYPE html>
<html lang="en">
    <head>
        <title>
            @section('title')
			bhapp.eu
            @show
        </title>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">

		@include('layouts.partials.includes_css')
	    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	    <!--[if lt IE 9]>
	      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	    <![endif]-->
	</head>


	<body>  
		<div class="container noPadding">

			@include('layouts.partials.includes_js')
			@include('layouts.partials.navigation')
			@yield('breadcrumbs')
			@yield('pageHeader')
			@yield('content')
<!--			@include('layouts.partials.footer')-->
			@include('layouts.partials.qtip')

		</div>
    </body>
</html>