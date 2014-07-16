<?php

class Updater {

	public static function update() {
        $log = "";
		$time = time();
		$allMatches = Updater::getAllMatchesForUpdate();
		// return $allMatches;
		foreach ($allMatches as $match) {
			$match = Updater::updateDetails($match);
			$log = $log.$match->home."-".$match->away.": ".$match->resultShort.'\n';
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
                } else {
                    $streaksHome->streak = $streaksHome->streak + 1;
                    $streaksAway->streak = $streaksAway->streak + 1;
                }
				foreach ($games as $game) {
					Updater::updatePool($game, $match->resultShort);
					if ($game->special == 1) {
						Updater::recalculateGroup($match->groups_id, $game->user_id);
					}
				}
				if (Updater::isLastGameInGroup($match)) {
					$log = $log." new group created for league: ".$match->league_details_id;
                    Updater::updateGroup($match->groups_id);
				}
			}
		}
		return $log." ".(time() - $time);
	}


	public static function updateGroup($groups_id) {
		$matches = Match::where('groups_id', '=', $groups_id)->get(['id', 'resultShort']);
		foreach ($matches as $match) {
			$games = Games::where('match_id', '=', $match->id)->get();
			foreach ($games as $game) {
				Updater::updatePool($game, $match->resultShort);
			}
		}
		$gr = Groups::find($groups_id);
		$current = Groups::firstOrCreate(['league_details_id' => $gr->league_details_id, 'state' => 3, 'round' => ($gr->round + 1)]);
		$gr->state = 1;
		$gr->save();
		$current->state = 2;
		$current->save();
		$next = Groups::firstOrCreate(['league_details_id' => $gr->league_details_id, 'state' => 3, 'round' => ($current->round + 1)]);
		// return $next;
		// Parser::parseMatchesForGroup($next);

        if ($gr->league_details_id == 112) {
            Parser::parseLeagueStandingsUSA($current->league_details_id);
            Parser::parseMatchesForUSA($current, $next);
        } else {
            Parser::parseMatchesForGroup($current, $next);
            Parser::parseLeagueStandings($current->league_details_id);
        }

        $str = Standings::where('league_details_id', '=', $gr->league_details_id)
            ->select(DB::raw('streak, count(*) as c'))
            ->groupBy('streak')
            ->get();
        foreach($str as $s) {
            $g = new GroupToStreaks();
            $g->groups_id = $gr->id;
            $g->streak_length = $s->streak;
            $g->streak_count = $s->c;
            $g->save();
        }
		$ids = Settings::where('league_details_id', '=', $current->league_details_id)->lists('user_id');
		foreach ($ids as $id) {
			Updater::recalculateGroup($current->id, $id);
		}
	}

	public static function isLastGameInGroup($match) {
		$count = Match::where('groups_id', '=', $match->groups_id)->where('resultShort', '=', '-')->count();
		return ($count < 1);
	}

	public static function getAllMatchesForUpdate() {
		date_default_timezone_set('Europe/Sofia');
		$now = date('Y-m-d H:i:s');
		$start = explode(' ', date( "Y-m-d H:i:s", strtotime( "$now - 2 hours" )));
		return Match::where(function($q) use ($start) {
				$q->where('matchDate', '<', $start[0])
					->orWhere(function($query) use ($start)
			            {
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

	public static function updateDetails($match) {
		return Match::updateMatchDetails($match);
	}

	public static function getAllGamesForMatch($match_id) {
		return Games::where('match_id', '=', $match_id)
			->where('confirmed', '=', 1)
			->get();
	}

	public static function updatePool($game, $resultShort) {
		$user_id = $game->user_id;
		$match = Match::find($game->match_id);
		if ($match != NULL){
			$league_details_id = $match->league_details_id;
		} else {
			return;
		}
		$pool = Pools::where('user_id', '=', $user_id)->where('league_details_id', '=', $league_details_id)->first();
		$main = CommonPools::where('user_id', '=', $user_id)->first();
		if ($resultShort == 'D') {
			if ($game->special == 1){
				$pool->amount = $pool->amount - $game->bsf;
			}
			$pool->income = $pool->income + $game->income;
			$main->income = $main->income + $game->income;
		} else {
			if ($game->special == 1){
				$pool->amount = $pool->amount + $game->bet;
			}
		}
		$main->save();
		$pool->save();
	}

	public static function recalculateGroup($groups_id, $user_id) {
		$gr = Groups::find($groups_id);
		$setting = Settings::where('user_id', '=', $user_id)
			->where('league_details_id', '=', $gr->league_details_id)
			->first(['from', 'to', 'multiplier', 'auto']);
		$from = $setting->from;
		$teams = array();
		if ($setting->auto == '2'){
			$teams = Standings::where('league_details_id', '=', $gr->league_details_id)
						->where('streak', '>=', $from)->lists('team', 'id');
		} else if ($setting->auto == '1') {
			$to = $setting->to;
			for($i = 0; $i < 100; $i ++) {
				$count = Standings::where('league_details_id', '=', $gr->league_details_id)
						->where('streak', '>=', $i);
				if ($count->count() <= $to){
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
        $pool = User::find($user_id)->pools()->where('league_details_id', '=', $gr->league_details_id)->first();
        if (count($teams) > 0) {
        $bsfpm = $pool->amount / count($teams);
        $bpm = $pool->amount * $setting->multiplier / count($teams);
        // $bsfpm = $pool;
        } else {
            $bsfpm = $pool->amount;
            $bpm = $pool->amount * $setting->multiplier;
        }
//        $recalc = false;
		foreach ($teams as $st_id => $team) {
			$matches = $gr->matches()->where(function ($query) use ($team) {
	             $query->where('home', '=', $team)
	                   ->orWhere('away', '=', $team);
	        })
	        ->where('resultShort', '=', '-')
	        ->orderBy('matchDate')
			->orderBy('matchTime')
	        ->get();
//            return $matches;
	        if (count($matches) == 0) {
//	        	$recalc = true;
	        } else if (count($matches) == 1) {
	        	$match = $matches[0];
				//TODO: add setting based bookmaker && special match check
				$game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' =>$st_id]);
				$game->bet = $bpm;
				$game->bsf = $bsfpm;
                if ($game->odds == null)
                    $game->odds = 3;
				$game->save();
				$game->income = $game->odds * $game->bet;
				$game->save();
//                print_r($game);
			} else if (count($matches) > 1) {
				$match = $matches[0];
				$game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' =>$st_id]);
				$game->bet = $bpm;
				$game->bsf = $bsfpm;
				if ($game->odds == null)
                    $game->odds = 3;
				$game->special = 1;
				$game->save();

				$game->income = $game->odds * $game->bet;
				$game->save();
			}
		}
//		if ($recalc) {
//			Games::recalculate($gr->id, $setting->multiplier, $pool->amount, $user_id);
//		}
	}

	public static function addPPMMatchForUser($league_details_id, $game_type_id, $user_id) {
		$team = LeagueDetails::find($league_details_id)->country;
		$series = Series::where("team", "=", $team)
			->where('game_type_id', '=', $game_type_id)
			->where('active', '=', 1)
			->first();
		// return $series;
		$game = PPM::firstOrNew(['user_id' => $user_id, 'game_type_id' => $game_type_id, 'country' => $team, 'series_id' => $series->id, 'match_id' => $series->end_match_id]);
		$game->odds = 3;
		$game->save();
	}

	public static function getPPMMatches() {
        $ids = Series::where('game_type_id', '>', 4)
            ->where('game_type_id', '<', 9)
            ->where('active', '=', 1)
            ->lists('end_match_id');
		return Match::whereIn('id', $ids)
            ->where('resultShort', '=', '-')
            ->get();

	}

    public static function updatePPM() {
        $time = time();
        $matches = self::getPPMMatches();
        foreach($matches as $match) {
            $match = self::updateDetails($match);
            Parser::parseLeagueStandings($match->league_details_id);
            if ($match->resultShort != '-') {
                for ($i = 5; $i < 9; $i++) {
                    $serie = Series::where('end_match_id', '=', $match->id)
                        ->where('active', '=', 1)
                        ->where('game_type_id', '=', $i)
                        ->first();
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
                        $news->current_length = $news->current_length + 1;
                        $news->save();
                        foreach ($games as $game) {
                            if ($game->confirmed == 1) {
                                $pool = Pools::where('user_id', '=', $game->user_id)->where('league_details_id', '=', $match->league_details_id)->where('ppm', '=', 1)->first();
                                $pool->income = $pool->income + $game->income;
                                $pool->save();
                            }
                            $newgame = PPM::firstOrNew(['user_id' => $game->user_id, 'series_id' => $news->id, 'match_id' => $next_match->id, 'game_type_id' => $game->game_type_id, 'country' => $game->country, 'confirmed' => 0]);
                            $newgame->bet = 0;
                            $newgame->bsf = $newgame->bsf + $game->bsf+$game->bet;
                            $newgame->odds = 3;
                            $newgame->income = 0;
                            $newgame->save();
                        }
                    } else {
                        $serie->current_length = $serie->current_length + 1;
                        $serie->end_match_id = $next_match->id;
                        $serie->save();

                        foreach ($games as $game) {
                            $newgame = PPM::firstOrNew(['user_id' => $game->user_id, 'series_id' => $serie->id, 'match_id' => $next_match->id, 'game_type_id' => $game->game_type_id, 'country' => $game->country, 'confirmed' => 0]);
                            $newgame->bet = 0;
                            $newgame->bsf = $newgame->bsf + $game->bsf+$game->bet;
                            $newgame->odds = 3;
                            $newgame->income = 0;
                            $newgame->confirmed = 0;
                            $newgame->save();
                            if ($game->confirmed == 1) {
                                $pool = Pools::where('user_id', '=', $game->user_id)->where('league_details_id', '=', $match->league_details_id)->where('ppm', '=', 1)->first();
                                $pool->amount = $pool->amount + $game->bet;
                                $pool->save();
                            }
                        }
                    }

                }

            }
        }
        return (time() - $time);

    }

    public static function getNextPPMMatch($match)
    {
        return Match::where('league_details_id', '=', $match->league_details_id)
            ->where('matchDate', '>=', $match->matchDate)
            ->where('resultShort', '=', '-')
            ->where('season', '=', $match->season)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->first();
    }

}