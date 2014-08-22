<?php

class Updater
{

    public static function update()
    {
        $log = "";
        $time = time();
        $allMatches = Updater::getAllMatchesForUpdate();
        // return $allMatches;
        foreach ($allMatches as $match) {
            $match = Updater::updateDetails($match);
//            return $match;
            Parser::parseLeagueStandings($match->league_details_id);
            $log = $log . $match->home . "-" . $match->away . ": " . $match->resultShort . '\n';
            try {
                $match->id;
            } catch (ErrorException $e) {
                continue;
            }
            if ($match->groups_id != 0) {
                $games = Updater::getAllGamesForMatch($match->id);
                $streaksHome = Standings::where('league_details_id', '=', $match->league_details_id)
                    ->where('team', '=', $match->home)
                    ->first();
                $streaksAway = Standings::where('league_details_id', '=', $match->league_details_id)
                    ->where('team', '=', $match->away)
                    ->first();
                if ($match->resultShort == 'D') {
                    $streaksHome->streak = 0;
                    $streaksAway->streak = 0;
                } else if ($match->resultShort == 'A' || $match->resultShort == 'H') {
                    $streaksHome->streak = $streaksHome->streak + 1;
                    $streaksAway->streak = $streaksAway->streak + 1;
                }
                $streaksAway->save();
                $streaksHome->save();
                foreach ($games as $game) {
                    Updater::updatePool($game, $match->resultShort);
//                    if ($game->special == 1) {
//                        Updater::recalculateGroup($match->groups_id, $game->user_id, $game->game_type_id);
//                    }
                }
                if (Updater::isLastGameInGroup($match)) {
                    $log = $log . " new group created for league: " . $match->league_details_id;
                    Updater::updateGroup($match->groups_id);
                }
            }
        }
        return $log . " " . (time() - $time);
    }


    public static function updateGroup($groups_id)
    {
//        $matches = Match::where('groups_id', '=', $groups_id)->get(['id', 'resultShort']);
////        foreach ($matches as $match) {
////            $games = Games::where('match_id', '=', $match->id)->get();
////            foreach ($games as $game) {
////                Updater::updatePool($game, $match->resultShort);
////            }
////        }
        $gr = Groups::find($groups_id);
        $current = Groups::firstOrCreate(['league_details_id' => $gr->league_details_id, 'state' => 3, 'round' => ($gr->round + 1)]);
        $gr->state = 1;
        $gr->save();
        $current->state = 2;
        $current->save();
        $next = Groups::firstOrCreate(['league_details_id' => $gr->league_details_id, 'state' => 3, 'round' => ($current->round + 1)]);
//         return $current;
        // Parser::parseMatchesForGroup($next);

        if ($gr->league_details_id == 112) {
            Parser::parseLeagueSeriesUSA($current->league_details_id);
            Parser::parseMatchesForUSA($current, $next);
        } else {
            Parser::parseLeagueSeries($current->league_details_id);
            Parser::parseMatchesForGroup($current, $next);

        }

        $str = Standings::where('league_details_id', '=', $gr->league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach ($str as $s) {
            $g = new GroupToStreaks();
            $g->groups_id = $gr->id;
            $g->streak_length = $s->streak;
            $g->streak_count = $s->c;
            $g->save();
        }
        $ids = Settings::where('league_details_id', '=', $current->league_details_id)->where('game_type_id', '<=', 4)->lists('user_id');
        foreach ($ids as $id) {
            Updater::recalculateGroup($current->id, $id, 1);
        }
    }

    public static function isLastGameInGroup($match)
    {
        $count = Match::where('groups_id', '=', $match->groups_id)->where('resultShort', '=', '-')->count();
        return ($count < 1);
    }

    public static function getAllMatchesForUpdate()
    {
        date_default_timezone_set('Europe/Sofia');
        $now = date('Y-m-d H:i:s');
        $start = explode(' ', date("Y-m-d H:i:s", strtotime("$now - 100 minutes")));
        return Match::where(function ($q) use ($start) {
            $q->where('matchDate', '<', $start[0])
                ->orWhere(function ($query) use ($start) {
                    $query->where('matchDate', '=', $start[0])
                        ->where('matchTime', '<=', $start[1]);
                });
        })
            ->where('resultShort', '=', '-')
            ->where('groups_id', '<>', 0)
            ->where('state', '<>', 'canceled')
            ->where('state', '<>', 'Awarded')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->get();
        // return Groups::find(2)->matches;
    }

    public static function updateDetails($match)
    {
        return Match::updateMatchDetailsLivescore($match);
    }

    public static function getAllGamesForMatch($match_id)
    {
        return Games::where('match_id', '=', $match_id)
            ->where('confirmed', '=', 1)
            ->get();
    }

    public static function updatePool($game, $resultShort)
    {
        $user_id = $game->user_id;
        $match = Match::find($game->match_id);
        if ($match != NULL) {
            $league_details_id = $match->league_details_id;
        } else {
            return;
        }
        $pool = Pools::where('user_id', '=', $user_id)->where('league_details_id', '=', $league_details_id)->where('game_type_id', '=', $game->game_type_id)->first();
        $main = CommonPools::where('user_id', '=', $user_id)->first();
        if ($resultShort == 'D') {
            $pool->amount = $pool->amount - $game->bsf;
            $pool->account = $pool->account + $game->income;
            $main->account = $main->account + $game->income;
            $main->amount = $main->amount - $game->bsf;
        } else if ($resultShort == 'A' || $resultShort == 'H') {
            $pool->amount = $pool->amount + $game->bet;
            $main->amount = $main->amount + $game->bet;
        }
        $main->save();
        $pool->save();
    }

    public static function recalculateGroup($groups_id, $user_id, $game_type_id)
    {
        $gr = Groups::find($groups_id);
        $setting = Settings::where('user_id', '=', $user_id)
            ->where('league_details_id', '=', $gr->league_details_id)
            ->where('game_type_id', '=', $game_type_id)
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
//        return $teams;

        if (count($teams) < Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->count()) {
            Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->delete();
        }
        $pool = User::find($user_id)->pools()->where('league_details_id', '=', $gr->league_details_id)
            ->where('game_type_id', '=', $game_type_id)
            ->first();
        if (count($teams) > 0) {
            $bsfpm = $pool->amount / count($teams);
            $bpm = $pool->amount * $setting->multiplier / count($teams);
        } else {
            $bsfpm = $pool->amount;
            $bpm = $pool->amount * $setting->multiplier;
        }
        foreach ($teams as $st_id => $team) {
            $matches = $gr->matches()->where(function ($query) use ($team) {
                $query->where('home', '=', $team)
                    ->orWhere('away', '=', $team);
            })
                ->where('resultShort', '=', '-')
                ->orderBy('matchDate')
                ->orderBy('matchTime')
                ->get();
            if (count($matches) == 0) {
            } else if (count($matches) == 1) {
                $match = $matches[0];
                //TODO: add setting based bookmaker && special match check
                $game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => $game_type_id, 'bookmaker_id' => 1, 'standings_id' => $st_id]);
                $game->bet = $bpm;
                $game->bsf = $bsfpm;
                if ($game->odds == null)
                    $game->odds = 3;
                $game->current_length = Standings::find($st_id)->streak;
                $game->save();
                $game->income = $game->odds * $game->bet;
                $game->save();
            } else if (count($matches) > 1) {
                $match = $matches[0];
                $game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => $game_type_id, 'bookmaker_id' => 1, 'standings_id' => $st_id]);
                $game->bet = $bpm;
                $game->bsf = $bsfpm;
                if ($game->odds == null)
                    $game->odds = 3;
                $game->special = 1;
                $game->current_length = Standings::find($st_id)->streak;
                $game->save();
                $game->income = $game->odds * $game->bet;
                $game->save();
            }
        }
    }

    public static function addPPMMatchForUser($league_details_id, $game_type_id, $user_id)
    {
        $team = LeagueDetails::find($league_details_id)->country;
        $series = Series::where("team", "=", $team)
            ->where('game_type_id', '=', $game_type_id)
            ->where('active', '=', 1)
            ->first();
        // return $series;
        $game = PPM::firstOrNew(['user_id' => $user_id, 'game_type_id' => $game_type_id, 'country' => $team, 'series_id' => $series->id, 'match_id' => $series->end_match_id]);
        $game->current_length = $series->current_length;
        $game->odds = 3;
        $game->save();
    }

    public static function getPPMMatches()
    {

        $ids = Series::where('game_type_id', '>', 4)
            ->where('game_type_id', '<', 9)
            ->where('active', '=', 1)
            ->lists('end_match_id');
        $matches = Match::whereIn('id', $ids)
//                ->where('resultShort', '=', '-')
            ->get();
        $res = array();
        foreach ($matches as $m) {
            $all = Match::where('league_details_id', '=', $m->league_details_id)
//                ->where('resultShort', '=', '-')
                ->where('season', '=', $m->season)
                ->where('matchDate', '=', $m->matchDate)
                ->where('matchTime', '=', $m->matchTime)
                ->get();
            foreach ($all as $match) {
                array_push($res, $match);
            }
        }
        return $res;
//        date_default_timezone_set('Europe/Sofia');
//        $now = date('Y-m-d H:i:s');
//        $start = explode(' ', date("Y-m-d H:i:s", strtotime("$now - 100 minutes")));
//
//        $ppm_leagues = LeagueDetails::where('ppm', '=', 1)->lists('id');
//        return Match::whereIn('league_details_id', $ppm_leagues)
//            ->where(function ($q) use ($start) {
//                $q->where('matchDate', '<', $start[0])
//                    ->orWhere(function ($query) use ($start) {
//                        $query->where('matchDate', '=', $start[0])
//                            ->where('matchTime', '<=', $start[1]);
//                    });
//            })
//            ->where('resultShort', '=', '-')
//            ->where('state', '<>', 'canceled')
//            ->where('state', '<>', 'Awarded')
//            ->orderBy('matchDate')
//            ->orderBy('matchTime')
//            ->get();
    }

    public static function updatePPM()
    {
        $time = time();
        $matches = self::getPPMMatches();
        foreach ($matches as $match) {
            $match = Match::updateMatchDetailsLivescore($match);
//print_r($match);
            if (Updater::isLastGameInGroup($match)) {
                Updater::updateGroup($match->groups_id);
            }
            Parser::parseLeagueStandings($match->league_details_id);
            if ($match->resultShort != '-') {
                for ($i = 5; $i < 9; $i++) {
                    $serie = Series::where('end_match_id', '=', $match->id)
                        ->where('active', '=', 1)
                        ->where('game_type_id', '=', $i)
                        ->first();
//                    print_r($serie);
//                    dd($serie);
                    $next_matches = self::getNextPPMMatches($match);
                    if ($next_matches == null) {
                        continue;
                    }
                    $next_match = self::getNextPPMMatch($match);
//                    return $next_matches;
                    $games = $match->ppm()->where('game_type_id', '=', $i)->get();
                    if (SeriesController::endSeries($match, $i)) {
                        $news = $serie->replicate();
                        $news->current_length = 1;
                        $news->start_match_id = $next_match->id;
                        $news->active = 1;
                        $serie->active = 0;
                        $serie->save();
                        $news->end_match_id = $next_match->id;
                        $news->save();
                        foreach ($games as $game) {
                            $pool = Pools::where('user_id', '=', $game->user_id)
                                ->where('league_details_id', '=', $match->league_details_id)
                                ->where('game_type_id', '=', $i)
                                ->first();
                            $pool->amount = 0;
                            if ($game->confirmed == 1) {
                                $pool->profit = $pool->profit + $game->income - $game->bsf - $game->bet;
                                $pool->account = $pool->account + $game->income;
                                $main = CommonPools::where('user_id', '=', $game->user_id)->first();
                                $main->profit = $main->profit + $game->income - $game->bsf - $game->bet;
                                $main->account = $main->account + $game->income;
                                $main->save();
                            }
                            $pool->save();
                            foreach ($next_matches as $n) {
                                $newgame = PPM::firstOrNew(['user_id' => $game->user_id, 'series_id' => $news->id, 'match_id' => $n->id, 'game_type_id' => $game->game_type_id, 'country' => $game->country, 'confirmed' => 0]);
                                $newgame->bet = 0;
                                $newgame->bsf = 0;
                                $newgame->odds = 3;
                                $newgame->current_length = $news->current_length;
                                $newgame->income = 0;
                                $newgame->save();
                            }
                        }
                    } else {
                        $serie->current_length = $serie->current_length + 1;
                        $serie->end_match_id = $next_match->id;
                        $serie->save();

                        foreach ($games as $game) {
                            $pool = Pools::where('user_id', '=', $game->user_id)
                                ->where('game_type_id', '=', $i)
                                ->where('league_details_id', '=', $match->league_details_id)
                                ->first();
                            if ($game->confirmed == 1) {
                                $pool->amount = $pool->amount + $game->bet;
                                $pool->save();
                                $main = CommonPools::where('user_id', '=', $game->user_id)->first();
                                $main->amount = $main->amount + $game->bet;
                                $main->save();
                            }

                        }
                        $settings = Settings::where('game_type_id', '=', $i)->where('league_details_id', '=', $serie->league_details_id)->get();
                        foreach ($settings as $sett) {
                            $pool = Pools::where('user_id', '=', $sett->user_id)->where('league_details_id', '=', $sett->league_details_id)->where('game_type_id', '=', $sett->game_type_id)->first();
                            foreach ($next_matches as $n) {
                                $newgame = PPM::firstOrNew(['user_id' => $sett->user_id, 'series_id' => $serie->id, 'match_id' => $n->id, 'game_type_id' => $i, 'country' => $serie->team, 'confirmed' => 0]);
                                $newgame->bet = 0;
                                $newgame->bsf = ($pool->amount) / count($next_matches);
                                $newgame->odds = 3;
                                $newgame->income = 0;
                                $newgame->current_length = $serie->current_length;
                                $newgame->save();
                            }
                        }
                    }

                }

            }
        }
        return (time() - $time);

    }

    public static function updateFree()
    {
        $teams = FreeplayTeams::all();

        foreach ($teams as $team) {
            $match = Match::find($team->match_id);
            if ($match->resultShort == '-') {
                $match = Match::updateMatchDetailsLivescore($match);
                if ($match->resultShort != '-') {
                    $standings = Standings::where('team', '=', $team->team)->where('league_details_id', '=', $team->league_details_id)->first();
                    if ($match->resultShort == 'D') {
                        $standings->streak = 0;
                    } else {
                        $standings->streak = $standings->streak + 1;
                    }
                    $standings->save();
                }
                $games = FreeGames::where('match_id', '=', $match->id)->where('confirmed', '=', 1)->get();
                if ($match->resultShort == 'D') {
                    $pools = FreePool::where('team_id', '=', $team->team_id);
                    foreach($pools as $pl){
                        $pl->amount = 0;
                        $pl->save();
                    }
                    foreach ($games as $game) {
                        $pool = FreePool::where('user_id', '=', $game->user_id)->where('team_id', '=', $team->team_id)->first();
                        $main = CommonPools::where('user_id', '=', $game->user_id)->first();
                        $pool->profit = $pool->profit + $game->income - $game->bsf - $game->bet;
                        $pool->account = $pool->account + $game->income;
                        $main->profit = $main->profit + $game->income - $game->bsf - $game->bet;
                        $main->account = $main->account + $game->income;
                        $pool->save();
                        $main->save();
                    }
                } else if ($match->resultShort == 'H' || $match->resultShort == 'A') {
                    foreach ($games as $game) {
                        $pool = FreePool::where('user_id', '=', $game->user_id)->where('team_id', '=', $team->team_id)->first();
                        $main = CommonPools::where('user_id', '=', $game->user_id)->first();
                        $pool->amount = $pool->amount + $game->bet;
                        $main->amount = $main->amount + $game->bet;
                        $pool->save();
                        $main->save();
                    }
                }
                if ($match->resultShort != '-') {
                    $ps = FreePool::where('team_id', '=', $team->team_id)->get();
                    $league = LeagueDetails::find($team->league_details_id);
                    $url = "http://www.betexplorer.com/soccer/" . $league->country . "/" . $league->fullName . "/teaminfo.php?team_id=" . $team->team_id;
                    $parsed = Parser::parseTeamMatches($url, $league->id);

                    Parser::parseLeagueStandings($league->id);
                    Match::find($parsed[1])->league_details_id = $league->id;
//                    return $ps;
                    foreach ($ps as $p) {
                        $team = FreeplayTeams::firstOrNew(['user_id' => $p->user_id, 'team_id' => $team->team_id, 'league_details_id' => $league->id, 'team' => $parsed[0]]);
                        $team->match_id = $parsed[1];
                        $team->save();
                        $game = FreeGames::firstOrNew(['user_id' => $p->user_id, 'team_id' => $team->team_id, 'match_id' => $parsed[1]]);
                        $game->bsf = $p->amount;
                        $game->game_type_id = 1;
                        $game->bookmaker_id = 1;
                        $game->income = 0;
                        $game->odds = 3;
                        $game->save();
                    }
                }

            }
        }
    }

    public static function getNextPPMMatches($match)
    {
        $next = self::getNextPPMMatch($match);
//        return $next;
        if ($next == null) {
            return null;
        }
        return Match::where('league_details_id', '=', $match->league_details_id)
//            ->where('resultShort', '=', '-')
            ->where('season', '=', $match->season)
            ->where('matchDate', '=', $next->matchDate)
            ->where('matchTime', '=', $next->matchTime)
            ->get();
    }

    public static function getNextPPMMatch($match)
    {
        $current = Groups::where('league_details_id', '=', $match->league_details_id)
            ->where('state', '=', 2)->first();
        $next = Groups::where('league_details_id', '=', $match->league_details_id)
            ->where('state', '=', 3)->first();
        if ($next != null && $current != null) {
            Parser::parseMatchesForGroup($current, $next);
        }

        return Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function ($q) use ($match) {
                $q->where('matchDate', '>', $match->matchDate)
                    ->orWhere(function ($query) use ($match) {
                        $query->where('matchDate', '=', $match->matchDate)
                            ->where('matchTime', '>', $match->matchTime);
                    });
            })
//            ->where('resultShort', '=', '-')
            ->where('season', '=', $match->season)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->first();
    }

}