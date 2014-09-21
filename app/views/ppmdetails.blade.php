@extends('layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        @foreach($matches as $match)
            @include('layouts.partials.square', ['data' => ['match'=>$match], 'game_type' => $type])
        @endforeach
    </div>
</div>
<table class="noMargin noPadding">
    @foreach($games as $game)
    <tr>
        <td>{{$game->bookmakerName}} {{$game->type}} {{$game->bet}} @ {{$game->odds}} {{$game->income}} <a href="/delete{{$isFree}}/{{$game->id}}/{{$game->game_type_id}}">-</a></td>
    </tr>
    @endforeach
</table>
@stop