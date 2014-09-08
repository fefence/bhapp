<?php

class DetailsController extends \BaseController
{
    public function details($team, $match_id, $game = '')
    {
        $match = Match::find($match_id);
        $games = Games::confirmedGamesForMatch($match, Auth::user()->id, $team);
        $currentGame = null;
        if (count($games) == 0) {
            $currentGame = Games::where('user_id', '=', Auth::user()->id)
                ->where('match_id', '=', $match->id)
                ->where('confirmed', '=', 0)
                ->join('standings', 'standings.id', '=', 'games.standings_id')
                ->where('team', '=', $team)
                ->first(['games.id', 'groups_id']);
//            return $currentGame;
        }
        list($home, $matchesH, $away, $matchesA, $h2h) = Match::getMatchesForTeams($match);
        return View::make('details')->with(['current_game' => $currentGame, 'data' => $games, 'home' => $matchesH, 'hometeam' => $home, 'awayteam' => $away, 'away' => $matchesA, 'h2h' => $h2h, 'team' => $team]);
    }

    public function detailsPPM($match_id, $type)
    {
        $match = Match::find($match_id);
        $games = PPM::getPPMForMatchType($type, $match, Auth::user()->id);
        return View::make('ppmdetails')->with(['isFree' => '', 'games' => $games]);
    }

    public function detailsFree($match_id, $team_id)
    {
//        $match = Match::find($match_id);
        $games = FreeGames::where('team_id', $team_id)->where('match_id', $match_id)->where('user_id', '=', Auth::user()->id)->where('confirmed', '=', 1)->get();
        return View::make('ppmdetails')->with(['isFree' => 'free', 'games' => $games]);
    }
}