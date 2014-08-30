<?php

class DetailsController extends \BaseController
{
    public function details($team, $match_id, $game = '')
    {
        $match = Match::find($match_id);
        $games = Games::confirmedGamesForMatch($match, Auth::user()->id, $team);
        list($home, $matchesH, $away, $matchesA, $h2h) = Match::getMatchesForTeams($match);
        return View::make('details')->with(['data' => $games, 'home' => $matchesH, 'hometeam' => $home, 'awayteam' => $away, 'away' => $matchesA, 'h2h' => $h2h, 'team' => $team]);
    }

    public function detailsPPM($match_id, $type)
    {
        $match = Match::find($match_id);
        $games = PPM::getPPMForMatchType($type, $match);
        return View::make('ppmdetails')->with(['games' => $games]);
    }

    public function detailsFree($match_id, $team_id)
    {
//        $match = Match::find($match_id);
        $games = FreeGames::where('team_id', $team_id)->where('match_id', $match_id)->where('user_id', '=', Auth::user()->id)->where('confirmed', '=', 1)->get();
        return View::make('ppmdetails')->with(['games' => $games]);
    }
}