<?php

class DetailsController extends \BaseController
{

    public function details($team, $match_id, $game = '')
    {
        $match = Match::find($match_id);
        $games = Games::confirmedGamesForMatch($match, Auth::user()->id);
        list($home, $matchesH, $away, $matchesA, $h2h) = Match::getMatchesForTeams($match);
        return View::make('details')->with(['data' => $games, 'home' => $matchesH, 'hometeam' => $home, 'awayteam' => $away, 'away' => $matchesA, 'h2h' => $h2h]);
    }

    public function detailsPPM($match_id, $game)
    {
        $match = Match::find($match_id);
        $games = $match->ppm()->where('user_id', '=', Auth::user()->id)
            ->join('bookmaker', 'ppm.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'ppm.game_type_id', '=', 'game_type.id')
            ->where('type', '=', $game)
            ->where('confirmed', '=', 1)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds']);
        return View::make('ppmdetails')->with(['games' => $games]);
    }
}