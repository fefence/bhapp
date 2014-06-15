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

    public function detailsPPM($match_id, $type)
    {
        $match = Match::find($match_id);
        $games = PPM::getPPMForMatchType($type, $match);
        return View::make('ppmdetails')->with(['games' => $games]);
    }
}