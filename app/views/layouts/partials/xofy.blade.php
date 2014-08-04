@if ($x != $y)
    @if ($x != 0)
        <span class="text-warning"><strong>{{$x}}/{{$y}} @if(isset($all)) ({{$all}})@endif</strong></span>
    @else
        <span class="text-danger"><strong>{{$x}}/{{$y}} @if(isset($all)) ({{$all}})@endif</strong></span>
    @endif
@else
    {{$x}}/{{$y}} @if(isset($all)) ({{$all}})@endif
@endif