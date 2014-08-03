@if ($x != $y)
    @if ($x != 0)
        <span class="text-warning"><strong>{{$x}}/{{$y}}</strong></span>
    @else
        <span class="text-danger"><strong>{{$x}}/{{$y}}</strong></span>
    @endif
@else
    {{$x}}/{{$y}}
@endif