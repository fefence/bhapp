@extends('layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        @foreach($matches as $match)
            @include('layouts.partials.square', ['data' => ['match'=>$match], 'game_type' => $type])
        @endforeach
    </div>
    <div class="col-xs-12">
        @foreach($games as $game)
            [BSF: {{$game->bsf}}] {{$game->bet}} @ {{$game->odds}} {{$game->income}} <a role="button" class="btn btn-xs w25 btn-danger" href="/delete{{$isFree}}/{{$game->id}}/{{$game->game_type_id}}">-</a>
        @endforeach
    </div>
</div>

@stop