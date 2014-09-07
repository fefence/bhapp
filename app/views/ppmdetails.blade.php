@extends('layout')

@section('content')
<table class="noMargin noPadding">
    @foreach($games as $game)
    <tr>
        <td>{{$game->bookmakerName}} {{$game->type}} {{$game->bet}} @ {{$game->odds}} {{$game->income}} <a href="/delete{{$isFree}}/{{$game->id}}/{{$game->game_type_id}}">-</a></td>
    </tr>
    @endforeach
</table>
@stop