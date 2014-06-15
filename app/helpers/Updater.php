<?php

class Updater {

	public static function update() {
		$time = time();
		$allMatches = Updater::getAllMatchesForUpdate();
		// return $allMatches;
		foreach ($allMatches as $match) {
			$match = Updater::updateDetails($match);
			echo $match->resultShort.'<br>';
			try {
				$match->id;
			} catch (ErrorException $e) {
				continue;
			}
			if ($match->groups_id != 0) {
				$games = Updater::getAllGamesForMatch($match->id);
				foreach ($games as $game) {
					Updater::updatePool($game, $match->resultShort);
					if ($game->special == 1) {
						Updater::recalculateGroup($match->groups_id, $game->user_id);
					}
				}
				if (Updater::isLastGameInGroup($match)) {
					Updater::updateGroup($match->groups_id);
				}
			}
		}
		return (time() - $time)." sec for ".count($allMatches)." matches";
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
		Parser::parseMatchesForGroup($current, $next);
		Parser::parseLeagueSeries($current);
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
		// return $gr;
		if ($setting->auto == '2'){
			$teams = Standings::where('league_details_id', '=', $gr->league_details_id)
						->where('streak', '>', $from)->lists('team', 'id');
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
		$pool = User::find($user_id)->pools()->where('league_details_id', '=', $gr->league_details_id)->first();
		$bsfpm = $pool->amount / count($teams);
		$bpm = $pool->amount * $setting->multiplier / count($teams);
		// $bsfpm = $pool;
		$recalc = false;
		if (count($teams) < Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->count()) {
			Games::where('user_id', '=', $user_id)->where('groups_id', '=', $gr->id)->delete();
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
	        	$recalc = true;
	        } else if (count($matches) == 1) {
	        	$match = $matches[0];
				//TODO: add setting based bookmaker && special match check
				$game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' =>$st_id]);
				$game->bet = $bpm;
				$game->bsf = $bsfpm;
				// $game->odds = 3;
				$game->save();
				$game->income = $game->odds * $game->bet;
				$game->save();
				
			} else if (count($matches) > 1) {
				$match = $matches[0];
				$game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'groups_id' => $match->groups_id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' =>$st_id]);
				$game->bet = $bpm;
				$game->bsf = $bsfpm;
				// $game->odds = 3;
				$game->special = 1;
				$game->save();
				
				$game->income = $game->odds * $game->bet;
				$game->save();
			}
		}
		if ($recalc) {
			Games::recalculate($gr->id, $setting->multiplier, $pool->amount, $user_id);
		}
	}

	public static function addPPMMatchForUser($league_details_id, $game_type_id, $user_id) {
		$team = LeagueDetails::find($league_details_id)->country;
		$series = Series::where("team", "=", $team)
			->where('game_type_id', '=', $game_type_id)
			->where('active', '=', 1)
			->first();
		// return $series;
		$game = PPM::firstOrNew(['user_id' => Auth::user()->id, 'game_type_id' => $game_type_id, 'country' => $team, 'series_id' => $series->id, 'match_id' => $series->end_match_id]);
		$game->odds = 3;
		$game->save();

	}

	public static function updatePPM() {
		$games = [5,6,7,8];
		
	}

	public static function getPPMMatches() {
		$leagues = LeagueDetails::where('ppm', '=', 1)->lists('id');
		return Match::whereIn('league_details_id', $leagues)
			->where('resultShort', '=', '-')
			->get();

	}

}