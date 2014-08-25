<?php

class GamesController extends \BaseController
{
    public function getGamesForGroup($league_details_id, $fromdate = "", $todate = "")
    {
        $pool = Pools::getPoolForUserLeague(Auth::user()->id, $league_details_id);

        if ($fromdate == '' && $todate == '') {
            $gr = Groups::getCurrentGroupId($league_details_id);
            $tail = "";
            $offset = 0;
        } else {
            list($f, $t) = StringsUtil::calculateDates($fromdate, $todate);
            $gr = Groups::getGroupIdForDates($league_details_id, $f, $t);
            $tail = "/" . $f . "/" . $t;
        }
        if ($gr != null) {
            $id = $gr->id;
            $games = User::find(Auth::user()->id)->games()->where('groups_id', '=', $gr->id)->lists('standings_id');
            if (count($games) == 0) {
                $games = [-1];
            }
            $standings = Standings::where('league_details_id', '=', $league_details_id)->whereNotIn('id', $games)->lists('team');
            $count = array();

            if ($fromdate == '' && $todate == '') {
                $data = Groups::getGamesForGroup($gr->id);
                $grey = Groups::getMatchesNotInGames($gr->id, $standings);
                list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
            } else {
                $data = Groups::getGamesForGroupAndDates($league_details_id, $fromdate, $todate);
                $grey = Groups::getMatchesNotInGamesForDates($league_details_id, $standings, $fromdate, $todate);
            }
            $disable = false;
            foreach ($data as $g) {
                $match = Match::find($g->match_id);
                $count[$g->id] = count(Games::confirmedGamesForMatch($match, Auth::user()->id, $g->team));
                if ($count[$g->id] > 0) {
                    $disable = true;
                }
            }
        } else {
            $data = array();
            $grey = array();
            $count = 0;
            $id = -1;
            $disable = true;
        }
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, $league_details_id);
        $arr = array();
        $arr[0] = $data;
        $arr[1] = $grey;
        $standings = Standings::where('league_details_id', '=', $league_details_id)->lists('place', 'team');
        $league = LeagueDetails::find($league_details_id);
        if ($tail == "" || isset($offset)) {
            return View::make('matches')->with(['tail' => $tail, 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'base' => "grouphistory/$league_details_id/$offset", 'base_minus' => "grouphistory/$league_details_id/".($offset+1), 'base_plus' => "grouphistory/$league_details_id/".($offset-1), 'big' => "Round ".$gr->round, 'small' => "current", 'disable' => $disable]);
        }
        return View::make('matches')->with(['tail' => $tail, 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'fromdate' => $fromdate, 'todate' => $todate, 'base' => "group/$league_details_id", 'big' => $big, 'small' => $small, 'disable' => $disable]);
    }

    public static function confirmAllPPM($country, $fromdate, $todate)
    {
        $games = PPM::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('ppm.country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->where('bet', '<>', '0')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('game_type_id')
            ->select(DB::raw("`ppm`.*"))
            ->get();
        foreach ($games as $game) {
            Games::confirmGame($game->id, $game->game_type_id);
        }
        return Redirect::back()->with('message', 'Bet confirmed');
//        return $games;
    }

    public function confirmGame($game_id, $game_type_id)
    {
        Games::confirmGame($game_id, $game_type_id);
        return Redirect::back()->with('message', 'Bet confirmed');
    }

    public function deleteGame($game_id, $game_type_id)
    {
        Games::deleteGame($game_id, $game_type_id);
        return Redirect::back()->with('message', 'Bet removed');
    }

    public function addGame($groups_id, $standings_id, $match_id)
    {
        Games::addGame($groups_id, $standings_id, Auth::user()->id, $match_id);
        return Redirect::back()->with('message', 'New game added');
    }

    public static function confirmAllGames($group_id, $fromdate = "", $todate = "")
    {
        $matches = Games::where('groups_id', '=', $group_id)
            ->where('user_id', '=', Auth::user()->id)
            ->where('confirmed', '=', 1)
            ->lists('match_id');
        if (count($matches) == 0) {
            $matches = [-1];
        }
        if ($fromdate == '' && $todate == '') {
            $data = Games::where('groups_id', '=', $group_id)
                ->where('user_id', '=', Auth::user()->id)
                ->where('confirmed', '=', 0)
                ->whereNotIn('match_id', $matches)
                ->get(['games.id', 'game_type_id']);

        } else {
            list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
            $data = Games::join('match', 'match.id', '=', 'games.match_id')
                ->where('games.groups_id', '=', $group_id)
                ->where('user_id', '=', Auth::user()->id)
                ->where('matchDate', '>=', $fromdate)
                ->where('matchDate', '<=', $todate)
                ->whereNotIn('match_id', $matches)
                ->where('confirmed', '=', 0)
                ->get(['games.id', 'game_type_id']);

        }
        foreach ($data as $game) {
            Games::confirmGame($game->id, $game->game_type_id);
        }
        return Redirect::back()->with("message", "All games confirmed");
    }

    public static function recalculateGroup($group_id)
    {
        $data = Games::join('match', 'match.id', '=', 'games.match_id')
            ->where('games.groups_id', '=', $group_id)
            ->where('user_id', '=', Auth::user()->id)
            ->where('resultShort', '=', '-')
            ->where('confirmed', '=', 0)
            ->select(DB::raw('games.*'))
            ->get();
        Parser::parseMatchOddsForGames($data);

        $gr = Groups::find($group_id);
        $setting = Settings::where('user_id', '=', Auth::user()->id)
            ->where('league_details_id', '=', $gr->league_details_id)
            ->first(['from', 'to', 'multiplier', 'auto']);
        $from = $setting->from;
        $teams = array();
        if ($setting->auto == '2') {
            $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                ->where('streak', '>=', $from)->lists('team', 'id');
        } else if ($setting->auto == '1') {
            $to = $setting->to;
            for ($i = 0; $i < 100; $i++) {
                $count = Standings::where('league_details_id', '=', $gr->league_details_id)
                    ->where('streak', '>=', $i);
                if ($count->count() <= $to) {
                    if ($count->count() < $from) {
                        $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                            ->where('streak', '>=', $i - 1)->lists('team', 'id');
                        break 1;
                    } else {
                        $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                            ->where('streak', '>=', $i)->lists('team', 'id');
                    }
                    break 1;
                }
            }
        }

//        return $setting;
        $league_details_id = Groups::find($group_id)->league_details_id;
        $pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $league_details_id)->first();
        if (count($teams) > 0) {
            $bsfpm = $pool->amount / count($teams);
        } else {
            $bsfpm = $pool->amount;
        }
        $setting = Settings::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $league_details_id)->first();
        $betpm = $bsfpm * $setting->multiplier;
        foreach ($data as $game) {
            $game->bsf = $bsfpm;
            $game->bet = $betpm;
            $game->income = $betpm * $game->odds;

            $game->save();
        }

        $pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', Groups::find($group_id)->league_details_id)->first();
        $pool->current = Games::where('groups_id', '=', $group_id)->where('user_id', '=', Auth::user()->id)->sum('bsf');
        $pool->save();
        return Redirect::back()->with('message', 'BSF recalculated');
    }

    public function getMatchOddsForGames($groups_id)
    {
        $games = Games::getGamesForGroupUser($groups_id, Auth::user()->id);
        Parser::parseMatchOddsForGames($games);
        return Redirect::back()->with('message', 'Odds retrieved');
    }

    public function saveTable()
    {
        $game_id = Input::get('row_id');
        $game_type_id = Input::get('id');
        $col = Input::get('column');

        if ($game_type_id > 4 && $game_type_id < 9) {
            $game = PPM::find($game_id);
        } else if ($game_type_id > 0 && $game_type_id < 5) {
            $game = Games::find($game_id);
            $col = $col + 1;
        }
        $value = Input::get('value');
        $bsf = "";
        if ($col == 9 || $col == '9') {
            $bsf = $game->bsf;
            $game->bsf = $value;
            $game->save();
            $league_details_id = Match::find($game->match_id)->league_details_id;
            $pool = Pools::where('user_id', '=', $game->user_id)->where('league_details_id', '=', $league_details_id)->first();
            $pool->current = $pool->current - $bsf + $value;
            $pool->save();
            $bsf = round($pool->current, 2);

            $aLog = new ActionLog;
            $aLog->type = "pps";
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
        if ($game_type_id > 4 && $game_type_id < 9) {
            $game = PPM::find($game_id);
        } else if ($game_type_id > 0 && $game_type_id < 5) {
            $game = Games::find($game_id);
        }
        return $game->bsf . "#" . $game->bet . "#" . $game->odds . "#" . $game->income . "#" . $bsf;
    }

    public static function getMatchOddsForAll($fromdate = '', $todate = '')
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = User::find(Auth::user()->id)
            ->games()
            ->join('match', 'match.id', '=', 'games.match_id')
            ->where('resultShort', '=', '-')
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('confirmed', '=', 0)
            ->select(DB::raw("games.*"))
            ->get();
        Parser::parseMatchOddsForGames($games);
        return Redirect::back()->with('message', 'Odds retrieved');
    }

    public static function getGamesForGroupOffset($league_details_id, $offset)
    {
        if ($offset == -1 || $offset == '-1') {
            $gr = Groups::where('league_details_id', '=', $league_details_id)->where('state', '=', 3)->orderBy('id', 'desc')->first();
        } else if ($offset == 0){
            $gr = Groups::where('league_details_id', '=', $league_details_id)->where('state', '=', 2)->orderBy('id', 'desc')->first();
        } else {
            $groups = Groups::where('league_details_id', '=', $league_details_id)->where('state', '=', 1)->orderBy('id', 'desc')->get();
            $i = 0;
            foreach ($groups as $gr) {
                $i++;
                if ($i == $offset) {
                    break;
                }
            }
        }

        $pool = Pools::getPoolForUserLeague(Auth::user()->id, $league_details_id);

        if ($gr != null) {
            $id = $gr->id;
            $games = User::find(Auth::user()->id)->games()->where('groups_id', '=', $gr->id)->lists('standings_id');
            if (count($games) == 0) {
                $games = [-1];
            }
            $standings = Standings::where('league_details_id', '=', $league_details_id)->whereNotIn('id', $games)->lists('team');
            $count = array();

            $data = Groups::getGamesForGroup($gr->id);
            $grey = Groups::getMatchesNotInGames($gr->id, $standings);
//                list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);

            $disable = false;
            foreach ($data as $g) {
                $match = Match::find($g->match_id);
                $count[$g->id] = count(Games::confirmedGamesForMatch($match, Auth::user()->id, $g->team));
                if ($count[$g->id] > 0) {
                    $disable = true;
                }
            }
        } else {
            $data = array();
            $grey = array();
            $count = 0;
            $id = -1;
            $disable = true;
        }
//        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, $league_details_id);
        $arr = array();
        $arr[0] = $data;
        $arr[1] = $grey;
        $standings = Standings::where('league_details_id', '=', $league_details_id)->lists('place', 'team');
        $league = LeagueDetails::find($league_details_id);
//        return $league;
        return View::make('matches')->with(['tail' => "", 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'base' => "group/$league_details_id/", 'base_minus' => "grouphistory/$league_details_id/".($offset + 1), 'base_plus' => "grouphistory/$league_details_id/".($offset - 1), 'big' => "Round ".$gr->round, 'small' => "", 'disable' => $disable]);

    }
}