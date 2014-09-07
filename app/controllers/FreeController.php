<?php

class FreeController extends \BaseController
{
    public static function display($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $games = FreeGames::gamesForDates($fromdate, $todate);
        $count = array();
        $league_ids = FreeplayTeams::where('user_id', '=', Auth::user()->id)->lists('league_details_id');
        foreach ($games as $g) {
            $count[$g->id] = FreeGames::where('user_id', '=', Auth::user()->id)->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('team_id', '=', $g->team_id)->where('game_type_id', '=', $g->game_type_id)->count();
        }

        if (count($league_ids) > 0) {
            $standings = Standings::whereIn('league_details_id', $league_ids)->lists('place', 'team');
        } else {
            $standings = array();
        }

        return View::make('freeview')->with(['hide_all' => true, 'data' => $games, 'standings' => $standings, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' => $todate, 'count' => $count, 'big' => $big, 'small' => $small, 'free' => true]);

    }

    public static function manage()
    {
        $teams = FreeplayTeams::where('freeplay_teams.user_id', '=', Auth::user()->id)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'freeplay_teams.league_details_id')
            ->join('standings', 'standings.team', '=', 'freeplay_teams.team')
            ->join('free_pool', 'free_pool.team_id', '=', 'freeplay_teams.team_id')
            ->where('free_pool.user_id', '=', Auth::user()->id)
            ->get();
        return View::make('managefree')->with(['data' => $teams]);
    }

    public static function save()
    {
        $url = Input::get("url");
//        return $parsed;
        $urlarr = explode('/', $url);
        $team_id = explode('=', $urlarr[count($urlarr) - 1])[1];
        $league = LeagueDetails::where('country', '=', $urlarr[4])->where('fullName', '=', $urlarr[5])->first();
        $parsed = Parser::parseTeamMatches($url, $league->id);

        Parser::parseLeagueStandings($league->id);
        Match::find($parsed[1])->league_details_id = $league->id;
        $team = FreeplayTeams::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'league_details_id' => $league->id, 'team' => $parsed[0]]);
        $team->match_id = $parsed[1];
        $team->save();
        $aLog = new ActionLog;
        $aLog->type = "free";
        $aLog->action = "add";
        $aLog->element_id = $team->id;
        $aLog->user_id = Auth::user()->id;
        $aLog->league_details_id = $league->id;
        $aLog->description = $team->team." added to free play view";
        $aLog->game_type_id = 1;
        $aLog->save();
        $game = FreeGames::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'match_id' => $parsed[1]]);
        $game->bsf = 0;
        $game->game_type_id = 1;
        $game->bookmaker_id = 1;
        $game->income = 0;
        $game->bet = 0;
        $game->odds = 3;
        $game->save();
        $pool = FreePool::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id]);
        $pool->save();
        return Redirect::back()->with("message", "saved");
    }

    public static function saveTable()
    {
        $game_id = Input::get('row_id');
        $team_id = Input::get('id');
        $value = Input::get('value');
        $game = FreeGames::find($game_id);
        $col = Input::get('column');
        $bsf = "";
//        return $col;
        if ($col == 9 || $col == '9') {
//            return $value;
            $bsf = $game->bsf;
            $game->bsf = $value;
            $game->save();
            $pool = FreePool::where('user_id', '=', $game->user_id)->where('team_id', '=', $team_id)->first();
            $pool->amount = $pool->amount - $bsf + $value;
            $pool->save();
//            $bsf = round($pool->current, 2);

            $aLog = new ActionLog;
            $aLog->type = "free";
            $aLog->action = "change bsf";
            $aLog->amount = $value;
            $aLog->element_id = $game->id;
            $aLog->save();
//            $bsf = $pool->current();
        }
        if ($col == 10 || $col == '10') {
            $game->bet = $value;
            $game->income = $game->odds * $value;
            $game->save();

        }
        if ($col == 11 || $col == '11') {
            $game->odds = $value;
            $game->income = $value * $game->bet;
            $game->save();

        }
        $game = FreeGames::find($game_id);
        return $game->bsf . "#" . $game->bet . "#" . $game->odds . "#" . $game->income . "#" . $bsf;

//        return FreeGames::find($game_id);
    }

    public static function confirmGame($game_id)
    {
        $free = FreeGames::find($game_id);

        $aLog = new ActionLog;
        $aLog->action = "confirm";
        $aLog->type = "free";
        $aLog->element_id = $free->id;
        $team = FreeplayTeams::where('team_id', '=', $free->team_id)->where('user_id', '=', $free->user_id)->first()->team;
        $match = Match::find($free->match_id);
        $aLog->description = $match->home . " - " . $match->away . " confirmed " . $free->bet . "@" . $free->odds . " series for " . $team;
        $aLog->user_id = $free->user_id;
        $aLog->game_type_id = $free->game_type_id;
        $aLog->league_details_id = $match->league_details_id;
        $aLog->save();

        $pool = FreePool::where('user_id', '=', $free->user_id)
            ->where('team_id', '=', $free->team_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $free->user_id)->first();

        $main->account = $main->account - $free->bet;
        $main->save();
        $pool->account = $pool->account - $free->bet;
        $pool->save();
        $newFree = $free->replicate();
        $free->confirmed = 1;
        $newFree->save();
        $free->save();
        return Redirect::back()->with('message', 'Bet confirmed');

    }

    public static function deleteGame($game_id)
    {
        $free = FreeGames::find($game_id);

        $aLog = new ActionLog;
        $aLog->action = "delete";
        $aLog->type = "free";
        $aLog->amount = $free->bet;
        $aLog->element_id = $free->id;
        $pool = FreePool::where('user_id', '=', $free->user_id)
            ->where('team_id', '=', $free->team_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $free->user_id)->first();
        $main->account = $main->account + $free->bet;
        $pool->account = $pool->account + $free->bet;
        $pool->save();
        $team = FreeplayTeams::where('team_id', '=', $free->team_id)->where('user_id', '=', $free->user_id)->first()->team;
        $match = Match::find($free->match_id);
        $aLog->description = $match->home . " - " . $match->away . " deleted " . $free->bet . "@" . $free->odds . " series for " . $team;
        $aLog->user_id = $free->user_id;
        $aLog->game_type_id = $free->game_type_id;
        $aLog->league_details_id = $match->league_details_id;
        $free->delete();

        return Redirect::back()->with('message', 'Bet deleted');

    }

    public static function refreshOdds($fromdate = "", $todate = "")
    {
        $start = time();
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = FreeGames::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'freeplay.match_id')
//            ->where('game_type_id', '=', 5)
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->select([DB::raw('freeplay.id as id, freeplay.*')])
            ->get();
//        return $games;
        Parser::parseMatchOddsForGames($games);
        return Redirect::back()->with('message', 'Odds refreshed ' . (time() - $start) . " sec");
    }

    public static function showTeam($team_id)
    {
        $team = FreeplayTeams::where('team_id', '=', $team_id)
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        $team->hidden = 0;
        $team->save();
        $aLog = new ActionLog;
        $aLog->type = "free";
        $aLog->action = "show";
        $aLog->element_id = $team->id;
        $aLog->user_id = Auth::user()->id;
        $aLog->league_details_id = $team->league_details_id;
        $aLog->description = $team->team." shown in free play view";
        $aLog->game_type_id = 1;
        $aLog->save();
        return Redirect::back()->with('message', $team->team . " shown");
    }

    public static function hideTeam($team_id)
    {
        $team = FreeplayTeams::where('team_id', '=', $team_id)
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        $team->hidden = 1;
        $team->save();
        $aLog = new ActionLog;
        $aLog->type = "free";
        $aLog->action = "hide";
        $aLog->element_id = $team->id;
        $aLog->user_id = Auth::user()->id;
        $aLog->league_details_id = $team->league_details_id;
        $aLog->description = $team->team." hidden from free play view";
        $aLog->game_type_id = 1;
        $aLog->save();
        return Redirect::back()->with('message', $team->team . " hidden");
    }


}