<div class="row">
    <ol class="breadcrumb noPadding">
        <li><a href="{{URL::to('home')}}">home</a></li>

        @foreach(array_get($elements, 'list') as $name => $url)
        <li><a href="{{$url}}">{{$name}}</a></li>
        @endforeach
        <li class="active">{{array_get($elements, 'active')}}</li>
    </ol>
    <div class="pull-right">
        @if(Session::get('message') != null)
        <span id="flash" class="bg-success text-success">{{Session::get('message')}}</span>
        @endif
        @if(Session::get('error') != null)
        <span id="flash" class="bg-danger text-danger">{{Session::get('message')}}</span>
        @endif

        @if(isset($ppsall) && isset($fromdate) && isset($todate))
        <a href="/ppsodds/{{$fromdate}}/{{$todate}}">refresh odds</a> |
        @endif
        @if(isset($group))
        @if($disable)
        recalc |
        @else
        <a href="/recalc/{{$group}}">recalc</a> |
        @endif
        <a href="/confirmall/{{$group}}{{$tail}}">confirm all</a> |
        <a href="/groupodds/{{$group}}">refresh odds</a> |
        @endif
        @if(isset($ppm) && $ppm == true && isset($fromdate) && isset($todate))
        @if(isset($country))
        <a href="/confirmallppm/{{$country}}/{{$fromdate}}/{{$todate}}">confirm all</a> |
        <a href="/ppm/{{$country}}/{{$fromdate}}/{{$todate}}/odds">refresh odds</a> |
        @else
        <a href="/ppmall/{{$fromdate}}/{{$todate}}/odds">refresh odds</a> |
        @endif
        @endif
        @if(isset($save) && $save)
        <a id="save" href="#" class="text-danger">save</a> |
        @endif
        <span>{{ Auth::user()->name }} | <a href="/settings">settings</a> | <a href="/logout">log out</a></span>
    </div>
</div>

<script>
    setTimeout(function () {
        $('#flash').fadeOut('fast');
    }, 1000);
</script>