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
            $log = $log . $match->home . "-" . $match->away . ": " . $match->resultShort . '<br>';
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
//                $ppm = LeagueDetails::find($match->league_details_id)->ppm;
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
        $gr = Groups::find($groups_id);
        try {

            $current = Groups::where('league_details_id', '=', $gr->league_details_id)
                ->where('state', '=', 3)
                ->where('round', '=', ($gr->round + 1))
                ->firstOrFail();
            $gr->state = 1;
            $gr->save();
            $current->state = 2;
            $current->save();
            $next = Groups::firstOrCreate(['league_details_id' => $gr->league_details_id, 'state' => 3, 'round' => ($current->round + 1)]);

            if ($gr->league_details_id == 112) {
                Parser::parseLeagueSeriesUSA($current->league_details_id);
                Parser::parseMatchesForUSA($current, $next);
            } else {
                Parser::parseLeagueSeries($current->league_details_id);
                Parser::parseMatchesForGroup($current, $next);

            }
        } catch (ErrorException $e) {

        }

        $str = Standings::where('league_details_id', '=', $gr->league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach ($str as $s) {
            $g = GroupToStreaks::firstOrNew(['groups_id' => $gr->id, 'streak_length' => $s->streak]);
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
        $bigger_than = 0;
        $i = 0;
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
                        $bigger_than = $i - 1;
                        break 1;
                    } else {
                        $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                            ->where('streak', '>=', $i)->lists('team', 'id');
                        $bigger_than = $i - 1;
                    }
                    break 1;
                }
            }
        }

        if (count($teams) < Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->count()) {
            Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->delete();
        }
        $pool = User::find($user_id)->pools()->where('league_details_id', '=', $gr->league_details_id)
            ->where('game_type_id', '=', $game_type_id)
            ->first();
        $gr_to_bsf = GroupToBSF::firstOrNew(['user_id' => $user_id, 'groups_id' => $groups_id]);
        $gr_to_bsf->bsf = $pool->amount;
        $gr_to_bsf->streak_bigger_than = $i;
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
        $game = PPM::firstOrNew(['user_id' => $user_id, 'game_type_id' => $game_type_id, 'country' => $team, 'series_id' => $series->id, 'match_id' => $series->end_match_id]);
        $game->current_length = $series->current_length;
        $game->odds = 3;
        $game->save();
    }

    public static function getPPMMatches($league_details_id)
    {

        $ids = Series::where('game_type_id', '>', 4)
            ->where('game_type_id', '<', 9)
            ->where('active', '=', 1)
            ->where('league_details_id', '=', $league_details_id)
            ->lists('end_match_id');
        $matches = Match::whereIn('id', $ids)
            ->get();
        $res = array();
        foreach ($matches as $m) {
            $all = Match::where('league_details_id', '=', $m->league_details_id)
                ->where('season', '=', $m->season)
                ->where('matchDate', '=', $m->matchDate)
                ->where('matchTime', '=', $m->matchTime)
                ->get();
            foreach ($all as $match) {
                array_push($res, $match);
            }
        }
        return $res;
    }

    public static function updatePPM($league_details_id)
    {
        $time = time();
        $matches = self::getPPMMatches($league_details_id);
        foreach ($matches as $match) {
            Parser::parseLeagueStandings($match->league_details_id);
            if ($match->resultShort != '-') {

                for ($i = 5; $i < 9; $i++) {

                    $serie = Series::where('end_match_id', '=', $match->id)
                        ->where('active', '=', 1)
                        ->where('game_type_id', '=', $i)
                        ->first();
                    if ($serie == null) {
                        $serie = Series::where('active', '=', 1)
                            ->where('league_details_id', '=', $match->league_details_id)
                            ->where('game_type_id', '=', $i)
                            ->first();
                    }
                    $next_matches = self::getNextPPMMatches($match);
                    if ($next_matches == null) {
                        continue;
                    }
                    foreach ($next_matches as $nm) {
                        $placeholder = PPMPlaceHolder::where('match_id', '=', $nm->id)
                            ->get();
                        foreach ($placeholder as $p) {
                            $p->active = 0;
                            $p->save();
                        }
                    }
                    $next_match = self::getNextPPMMatch($match);
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
                        $settings = Settings::where('league_details_id', '=', $match->league_details_id)->where('game_type_id', '=', $i)->get();
                        foreach ($settings as $stngs) {
                            $confirmedppms = PPM::where('user_id', '=', $stngs->user_id)->where('match_id', '=', $match->id)->where('game_type_id', '=', $i)->where('confirmed', '=', 1)->get();
                            if ($confirmedppms == null) {
                                $pool = Pools::where('user_id', '=', $stngs->user_id)
                                    ->where('league_details_id', '=', $match->league_details_id)
                                    ->where('game_type_id', '=', $i)
                                    ->first();
                                $pool->amount = 0;
                                $pool->profit = $pool->profit - $pool->bsf;
                                $main = CommonPools::where('user_id', '=', $pool->user_id)->first();
                                $main->profit = $main->profit - $pool->bsf;
                                $main->amount = $main->amount - $pool->bsf;
                                $main->save();
                            }
                        }
                        foreach ($games as $game) {
                            $pool = Pools::where('user_id', '=', $game->user_id)
                                ->where('league_details_id', '=', $match->league_details_id)
                                ->where('game_type_id', '=', $i)
                                ->first();
                            if ($game->confirmed == 1) {
                                $main = CommonPools::where('user_id', '=', $game->user_id)->first();
                                $main->profit = $main->profit + $game->income - $game->bsf - $game->bet;
                                $main->account = $main->account + $game->income;
                                $main->amount = $main->amount - $game->bsf;
                                $main->save();
                                $pool->amount = $pool->amount - $game->bsf;
                                $pool->profit = $pool->profit + $game->income - $game->bsf - $game->bet;
                                $pool->account = $pool->account + $game->income;

                            }
                            $pool->save();

                        }
                        Updater::createPPMGames($next_matches, $i, $serie);

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
                        Updater::createPPMGames($next_matches, $i, $serie);
                    }

                }
                for ($i = 1; $i < 5; $i++) {
                    $user = User::find($i);
                    if (count($next_matches) > 0) {
                        foreach ($next_matches as $next) {
                            $conf = PPM::where('match_id', '=', $next->id)
                                ->where('bet', '<>', 0)
                                ->where('confirmed', '=', 1)
                                ->where('user_id', '=', $i)
                                ->lists('game_type_id');
                            if (count($conf) == 0) {
                                $conf = [-1];
                            }
                            $ppms = PPM::where('match_id', '=', $next->id)
                                ->where('bet', '<>', 0)
                                ->where('user_id', '=', $i)
                                ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
                                ->select(DB::raw("ppm.*, game_type.type as type"))
                                ->orderBy('game_type_id')
                                ->get();
                            if (count($ppms) > 0) {
                                $league = LeagueDetails::find($match->league_details_id);
                                $text = "<a href='" . URL::to("/") . "/confirmallppm/" . $league->country . "/" . $next->matchDate . "/" . $next->matchDate . "'>" . $next->home . " - " . $next->away . "</a><br>";
                                foreach ($ppms as $ppm) {
                                    if (in_array($ppm->game_type_id, $conf)) {
                                        if ($ppm->confirmed == 1) {
                                            $text = $text . "<p>" . $ppm->type . " Length: " . $ppm->current_length . " (confirmed)<br>BSF: " . $ppm->bsf . "€<br> Bet: " . $ppm->bet . "€<br>Odds: " . $ppm->odds . "<br>Profit: " . ($ppm->income - $ppm->bet - $ppm->bsf) . "€</p>";
                                        }
                                    } else {
                                        $text = $text . "<p>" . $ppm->type . " Length: " . $ppm->current_length . "<br>BSF: " . $ppm->bsf . "€<br> Bet: " . $ppm->bet . "€<br>Odds: " . $ppm->odds . "<br>Profit: " . ($ppm->income - $ppm->bet - $ppm->bsf) . "€</p>";
                                    }
                                }
                                $text = $text . "<a href='" . URL::to("/") . "/ppm/country/" . $league->country . "/" . $next->matchDate . "/" . $next->matchDate . "'>Go to group</a>";
                                Mail::send('emails.email', ['data' => $text], function ($message) use ($user, $league) {
                                    $message->to([$user->email => $user->name])
                                        ->subject("PPM games available for confirm [" . $league->country . "]");
                                });
                            }
                        }
                    } else {
                        echo 'No next matches for ' . $serie->league_details_id;
                    }
                }

            }
        }
        return (time() - $time);

    }

    public static function createPPMGames($next_matches, $i, $serie)
    {
        $settings = Settings::where('game_type_id', '=', $i)->where('league_details_id', '=', $serie->league_details_id)->get();
        foreach ($settings as $sett) {
            $pool = Pools::where('user_id', '=', $sett->user_id)->where('league_details_id', '=', $sett->league_details_id)->where('game_type_id', '=', $sett->game_type_id)->first();
            foreach ($next_matches as $n) {
                $newgame = PPM::firstOrNew(['user_id' => $sett->user_id, 'series_id' => $serie->id, 'match_id' => $n->id, 'game_type_id' => $i, 'country' => $serie->team, 'confirmed' => 0]);
                $newgame->bsf = ($pool->amount) / count($next_matches);
                $newgame->bookmaker_id = 1;
                $newgame->current_length = $serie->current_length;
                PPMPlaceHolder::createPlaceholder($newgame);
                $placeholders = PPMPlaceHolder::getPlaceholder($newgame);
                if (count($placeholders) > 0) {
                    foreach ($placeholders as $placeholder) {
                        $newgame->bet = $placeholder->bet;
                        $newgame->odds = $placeholder->odds;
                        $newgame->income = $placeholder->income;
                        $newgame->save();
                        $newg = Games::confirmGame($newgame->id, $newgame->game_type_id, false);
                        $placeholder->active = 0;
                        $placeholder->save();
                        $newgame = $newg;
//                                        $newgame = PPM::firstOrNew(['user_id' => $sett->user_id, 'series_id' => $serie->id, 'match_id' => $n->id, 'game_type_id' => $i, 'country' => $serie->team, 'confirmed' => 0]);
                    }
                } else {
                    $newgame->bet = 0;
                    $newgame->income = $newgame->bet * $newgame->odds;
                    $newgame->save();
                    try {
                        $warn = Parser::parseMatchOddsForGames([$newgame]);
                        $league = LeagueDetails::find($newgame->league_details_id);
                        $user = User::find($newgame->user_id);
                        if ($warn) {
                            $text = "Odds for match " . $n->home . " - " . $n->away . " are not retrieved correctly.<br>Please go to " . URL::to("/") . $league->country . "/" . $n->matchDate . "/" . $n->matchDate . " to confirm the game manually";
                            Mail::send('emails.email', ['data' => $text], function ($message) use ($user) {
                                $message->to([$user->email => $user->name])
                                    ->subject('PPM game available');
                            });
                        } else {
                            $newgame->bet = round((20 + $newgame->bsf) / ($newgame->odds - 1), 2, PHP_ROUND_HALF_UP);
                            $newgame->income = $newgame->bet * $newgame->odds;
                            $newgame->save();
                        }
                    } catch (ErrorException $e) {

                    }
//                    PPMPlaceHolder::createPlaceholder($newgame);

                }
            }
        }
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
                    foreach ($pools as $pl) {
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
        if ($next == null) {
            return null;
        }
        return Match::where('league_details_id', '=', $match->league_details_id)
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
            ->where('season', '=', $match->season)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->first();
    }

}