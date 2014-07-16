<?php

class FreeController extends \BaseController
{
    public static function display($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $games = FreeGames::gamesForDates($fromdate, $todate);
        $count = array();
        $league_ids = array();
        foreach ($games as $g) {
            $count[$g->id] = FreeGames::where('user_id', '=', Auth::user()->id)->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
            array_push($league_ids,$g->league_details_id);
        }
        $datarr = array();
        $datarr[0] = $games;
        if (count($league_ids) > 0) {
            $standings = Standings::whereIn('league_details_id', $league_ids)->lists('place', 'team');
        } else {
            $standings = array();
        }
//        $datarr[1] = array();
        return View::make('matches')->with(['datarr' => $datarr, 'standings' => $standings, 'ppm' => true, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' => $todate, 'count' => $count, 'big' => $big, 'small' => $small]);
    }

    public static function manage()
    {
        return View::make('managefree');
    }

    public static function save()
    {
        $team_id = Input::get("team");
        $league_id = Input::get("league_id");
        $match_id = Parser::parseTeamMatches($team_id, $league_id);
        $team = FreeplayTeams::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'league_details_id' => $league_id]);
        $team->match_id = null;
        $team->save();
        $game = FreeGames::firstOrNew(['user_id' => Auth::user()->id, 'team' => $team_id, 'match_id' => $match_id]);
        $game->bsf = 0;
        $game->game_type_id = 1;
        $game->bookmaker_id = 1;
        $game->income = 0;
        $game->bet = 0;
        $game->odds = 3;
        $game->save();
        return Redirect::back()->with("message", "saved");
    }
}