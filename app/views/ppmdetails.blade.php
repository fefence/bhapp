@extends('layout')

@section('content')
<table class="noMargin noPadding">
    @foreach($games as $game)
    <tr>
        <td>{{$game->bookmakerName}} {{$game->type}} {{$game->bet}} @ {{$game->odds}} {{$game->income}}</td>
    </tr>
    @endforeach
</table>
@stop