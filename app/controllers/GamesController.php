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
                $data = Groups::getGamesForGroup($gr->id, Auth::user()->id);
                $grey = Groups::getMatchesNotInGames($gr->id, $standings);
                list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
            } else {
                $data = Groups::getGamesForGroupAndDates($league_details_id, $fromdate, $todate, Auth::user()->id);
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
        $settings = Settings::where('league_details_id', '=', $league->id)->where('user_id', '=', Auth::user()->id)->where('game_type_id', '=', 1)->first();

        $plusminus = GroupController::getMatchesCountForChangedSettings(Auth::user()->id, $league_details_id, $gr->id);
        if ($tail == "" || isset($offset)) {
            return View::make('matches')->with(['all_link' => "pps/group/$league_details_id", 'all_active' => true, 'plus' => $plusminus[0], 'minus'=>$plusminus[1], 'settings' => $settings, 'tail' => $tail, 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'base' => "pps/group/$league_details_id/$fromdate/$todate", 'base_minus' => "pps/group/$league_details_id/" . ($offset + 1), 'base_plus' => "pps/group/$league_details_id/" . ($offset - 1), 'big' => "Round " . $gr->round, 'small' => "current", 'disable' => $disable]);
        }
        return View::make('matches')->with(['plus' => $plusminus[0],'minus'=>$plusminus[1],'settings' => $settings, 'tail' => $tail, 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'fromdate' => $fromdate, 'todate' => $todate, 'base' => "pps/group/$league_details_id", 'big' => $big, 'small' => $small, 'disable' => $disable]);
    }

    public static function getGamesForGroupOffset($league_details_id, $offset)
    {
        $current_active = false;
        if ($offset == -1 || $offset == '-1') {
            $gr = Groups::where('league_details_id', '=', $league_details_id)->where('state', '=', 3)->orderBy('id', 'desc')->first();
        } else if ($offset == 0) {
            return Redirect::to("/pps/group/$league_details_id");
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

            $data = Groups::getGamesForGroup($gr->id, Auth::user()->id);
            $grey = Groups::getMatchesNotInGames($gr->id, $standings);

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
        $arr = array();
        $arr[0] = $data;
        $arr[1] = $grey;
        $standings = Standings::where('league_details_id', '=', $league_details_id)->lists('place', 'team');
        $league = LeagueDetails::find($league_details_id);
        return View::make('matches')->with(['hide_all' => true, 'current_active' => $current_active, 'today_btn' => 'current', 'tail' => "", 'league' => $league, 'standings' => $standings, 'datarr' => $arr, 'count' => $count, 'pool' => $pool, 'group' => $id, 'base' => "pps/group/$league_details_id/", 'base_minus' => "pps/group/$league_details_id/" . ($offset + 1), 'base_plus' => "pps/group/$league_details_id/" . ($offset - 1), 'big' => "Round " . $gr->round, 'small' => "", 'disable' => $disable]);

    }

    public static function confirmAllPPM($country, $fromdate, $todate)
    {
        $user_id = Auth::user()->id;
        $games = PPM::getPPMForConfirm($country, $fromdate, $todate, $user_id);
        foreach ($games as $game) {
            Games::confirmGame($game->id, $game->game_type_id, false);
        }
        try {
            return Redirect::back()->with('message', 'Bet confirmed');
        } catch (InvalidArgumentException $e) {
            return Redirect::to(URL::to("/ppm/country/" . $country . "/" . $fromdate . "/" . $todate));

        }
    }

    public function confirmGame($game_id, $game_type_id, $pl = false)
    {
        Games::confirmGame($game_id, $game_type_id, $pl);
        try {
            return Redirect::back()->with('message', 'Bet confirmed');
        } catch (InvalidArgumentException $e) {
            if ($game_type_id < 5) {
                $game = Games::find($game_id);
                $match = $game->match;
                return Redirect::to(URL::to("/pps/group/" . $match->league_details_id . "/" . $match->matchDate . "/" . $match->matchDate));
            } else {
                $game = PPM::find($game_id);
                $match = $game->match;
                $league = LeagueDetails::find($match->league_details_id);
                return Redirect::to(URL::to("/ppm/country/" . $league->country . "/" . $match->matchDate . "/" . $match->matchDate));
            }

        }
    }

    public function deleteGame($game_id, $game_type_id)
    {
        Games::deleteGame($game_id, $game_type_id);
        return Redirect::back()->with('message', 'Bet removed');
    }

    public function addGame($groups_id, $standings_id, $match_id)
    {
        $user_id = Auth::user()->id;
        Games::addGame($groups_id, $standings_id, Auth::user()->id, $match_id);
        $bsfsum = 0;
        $games = Games::getGamesForGroupUser($groups_id, $user_id);
        Games::basicRecalc($games, $bsfsum);
        return Redirect::back()->with('message', 'New game added');
    }

    public static function confirmAllGames($group_id, $fromdate = "", $todate = "")
    {
        $data = Games::getPPSForConfirm($group_id, $fromdate, $todate);
        foreach ($data as $game) {
            Games::confirmGame($game->id, $game->game_type_id, false);
        }
        return Redirect::back()->with("message", "All games confirmed");
    }

    public static function recalculateGroup($group_id)
    {
        $user_id = Auth::user()->id;
        Groups::recalculatePPSGroup($group_id, $user_id);
        return Redirect::back()->with('message', 'BSF recalculated');
    }

    public function getMatchOddsForGames($groups_id)
    {
        $games = Games::getGamesForGroupUser($groups_id, Auth::user()->id);
        $warn = Parser::parseMatchOddsForGames($games);
        if ($warn) {
            return Redirect::back()->with('warning', 'Odds not retrieved');
        }
        return Redirect::back()->with('message', 'Odds retrieved');
    }

    public function saveTable()
    {
//        return Input::all();
        $game_id = Input::get('row_id');
        $game_type_id = Input::get('id');
        $col = Input::get('column');
        $pl = false;
        if ($game_type_id > 4 && $game_type_id < 9) {
            if (str_contains($game_id, '!')) {
                $game_id = str_replace('!', '', $game_id);
                $game = PPMPlaceHolder::find($game_id);
                $pl = true;
            } else {
                $game = PPM::find($game_id);
            }
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
            if ($pl) {
                $game = PPMPlaceHolder::find($game_id);
            } else {
                $game = PPM::find($game_id);
            }
        } else if ($game_type_id > 0 && $game_type_id < 5) {
            $game = Games::find($game_id);
        }
        return $game->bsf . "#" . $game->bet . "#" . $game->odds . "#" . $game->income . "#" . $bsf;
    }

    public static function getMatchOddsForAll($fromdate = '', $todate = '')
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $data = LeagueDetails::getLeaguesWithMatches($fromdate, $todate);
        $ids = array_keys($data);
//        return $ids;
        $games = User::find(Auth::user()->id)
            ->games()
            ->join('match', 'match.id', '=', 'games.match_id')
            ->where('resultShort', '=', '-')
            ->whereIn('match.groups_id', $ids)
            ->where('confirmed', '=', 0)
            ->select(DB::raw("games.*"))
            ->get();
        Parser::parseMatchOddsForGames($games);
        return Redirect::back()->with('message', 'Odds retrieved');
    }



    public static function removeGameFromGroup($games_id, $groups_id) {
        $to_delete = Games::find($games_id);
        $bsfsum = $to_delete->bsf;
        $to_delete->delete();
        $games = Games::getGamesForGroupUser($groups_id, Auth::user()->id);
        Games::basicRecalc($games, $bsfsum);
        return Redirect::back()->with('message', 'Game removed');
    }
}