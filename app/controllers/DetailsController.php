<?php

class DetailsController extends \BaseController
{
    public function details($team, $match_id)
    {
        // $date_str = \DateTime::createFromFormat("d-m-y", $date)->format("Y-m-d");
        $match = Match::find($match_id);
        $games = $match->games()->where('user_id', '=', Auth::user()->id)
            ->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'games.game_type_id', '=', 'game_type.id')
            ->join('standings', 'games.standings_id', '=', 'standings.id')
            ->where('match_id', '=', $match_id)
            ->where('confirmed', '=', 1)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds']);
        // return $games;
        $home = $match->home;
        $matchesH = Match::matchesForSeason($match->league_details_id, $match->season)
            ->where(function ($query) use ($home) {
                $query->where('home', '=', $home)
                    ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);
        $away = $match->away;
        $matchesA = Match::matchesForSeason($match->league_details_id, $match->season)
            ->where(function ($query) use ($away) {
                $query->where('home', '=', $away)
                    ->orWhere('away', '=', $away);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);

        $h2h = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($query) use ($away) {
                $query->where('home', '=', $away)
                    ->orWhere('away', '=', $away);
            })
            ->where(function ($query) use ($home) {
                $query->where('home', '=', $home)
                    ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
            ->orderBy('matchDate', 'desc')
            ->take(10)
            ->get(['home', 'away', 'homeGoals', 'awayGoals', 'matchDate', 'resultShort']);
        return View::make('details')->with(['data' => $games, 'home' => $matchesH, 'hometeam' => $home, 'awayteam' => $away, 'away' => $matchesA, 'h2h' => $h2h]);
    }

    public function detailsPPM($home, $away, $date)
    {
        return View::make('ppmdetails');
    }
}