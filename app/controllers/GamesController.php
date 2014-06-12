<?php

class GamesController extends \BaseController {
	protected $layout = 'layout';
	
	public function getGamesForGroup($league_details_id) {
		$games = User::find(Auth::user()->id)->games()->lists('match_id');
		// return $games;
		$pool = User::find(Auth::user()->id)->pools()->where('league_details_id', '=', $league_details_id)->first();
		$gr = Groups::where('league_details_id', '=', $league_details_id)
				->where('state', '=', '2')
				->first();
		$games = Games::where('user_id', '=', Auth::user()->id)->where('groups_id', '=', $gr->id)->lists('standings_id');
		$data = $gr->matches()
				->join('games', 'games.match_id', '=', 'match.id')
				->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
				->join('game_type', 'games.game_type_id', '=', 'game_type.id')
				->join('standings', 'games.standings_id', '=', 'standings.id')
				->select(DB::raw('`games`.id as games_id, `games`.*, `standings`.*, `match`.*, bookmaker.*, game_type.*'))
				->where('user_id', '=', Auth::user()->id)
				->where('confirmed', '=', 0)
				->orderBy('matchDate')
				->orderBy('matchTime')
				->orderBy('streak')
				->get();
		// return $data;
		$standings = Standings::whereNotIn('id', $games)->lists('team');
		$grey = array();
		// foreach ($standings as $st) {
			$m1 = $gr->matches()
				->whereIn('home', $standings)
				->join('standings', 'match.home', '=', 'standings.team')
				->orderBy('matchDate')
				->orderBy('matchTime')
				->orderBy('streak')
				->get();
		// }
			$m2 = $gr->matches()
				->whereIn('away', $standings)
				->join('standings', 'match.away', '=', 'standings.team')
				->orderBy('matchDate')
				->orderBy('matchTime')
				->orderBy('streak')
				->get();
		$grey = [$m1, $m2];
		return View::make('matches')->with(['data' => $data, 'grey' => $grey, 'pool' => $pool, 'league_details_id' => $league_details_id, 'group' => $gr->id]);
	} 

	public function getGroups($fromdate = "", $todate = ""){
		if ($fromdate == "") {
			$fromdate = date("Y-m-d", time());
		}
		if ($todate == "") {
			$todate = date("Y-m-d", time());
		}
		$league_details_ids = Settings::where('user_id', '=', Auth::user()->id)->lists('league_details_id');
		
		if (count($league_details_ids) > 0) {
			$ids = Groups::whereIn('groups.league_details_id', $league_details_ids)
			->join('match', 'match.groups_id', '=', 'groups.id')
			->where('matchDate', '>=', $fromdate)
			->where('matchDate', '<=', $todate)
			->select('match.league_details_id as lids')
			->lists('lids');
			if (count($ids) > 0){ 
				$data = LeagueDetails::whereIn('id', $ids)->get(['country', 'fullName', 'id']);
			} else {
				$data = array();
			}
		} else {
			$data = array();
		}

		if ($fromdate == $todate && $fromdate == date('Y-m-d', time())) {
			$big = "Today's matches";
			$small = date('d-M-y (D)', time());
		} else if ($fromdate == $todate && $fromdate == date('Y-m-d', time()+86400)) {
			$big = "Tomorow's matches";
			$small = date('d-M-y (D)', time() + 86400);
		} else if ($fromdate == $todate && $fromdate == date('Y-m-d', time()-86400)) {
			$big = "Yesterdays's matches";
			$small = date('d-M-y (D)', time() - 86400);
		} else if ($fromdate == $todate) {
			$big = "Matches";
			$small = date('d-M-y (D)', strtotime($fromdate));
		} else {
			$big = "Matches";
			$small = date('d-M-y (D)', strtotime($fromdate)) ." to ". date('d-M-y (D)', strtotime($todate));
		}

		return View::make('games')->with(['data' => $data, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
	}

	public function getMatchOddsForGames($groups_id) {
		$ids = Groups::find($groups_id)->matches()->lists('id');

		$games = Games::whereIn('match_id', $ids)->where('user_id', '=', Auth::user()->id)->get();
		// return $games;
		foreach ($games as $game) {
			Games::getMatchOddsForGame($game, 1);			
		}
		return Redirect::back();
	}

	public function saveTable() {

		$game_id = Input::get('row_id');
		$game_type_id = Input::get('id');
		// return $game_id;
		if ($game_type_id > 4 && $game_type_id < 9) {
			$game = PPM::find($game_id);
		} else if($game_type_id > 0 && $game_type_id) {
			$game = Games::find($game_id);
		}
		// return $game->id;
		$value = Input::get('value');
		$col = Input::get('column');
		$bsf = "";
		if ($col == 9 || $col == '9') {
			$game->bsf = $value;
			$game->save();

			if ($game_type_id > 0 && $game_type_id) {
				$m = Match::find($game->match_id);
				$matches = Groups::find($m->groups_id)->matches()->lists('id');
				// return $matches;
				$bsf = Games::where('user_id', '=', Auth::user()->id)->whereIn('match_id', $matches)->sum('bsf');
				$pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $m->league_details_id)->first();
				$pool->current = $bsf;
				$pool->save();
			}
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
			// 	break;
			// case 8:
			// 	$game->bet = $value;
			// 	$game->income = $game->odds * $value;
			// 	$game->save();
			// 	break;
			// case 9:
			// 	$game->odds = $value;
			// 	$game->income = $value * $game->bet;
			// 	$game->save();
			// 	break;

		
		return $game->bsf."#".$game->bet."#".$game->odds."#".$game->income."#".$bsf;
	}

	public function confirmGame($game_id) {
		$game = Games::find($game_id);
		$nGame = $game->replicate();
		$nGame->save();
		$game->confirmed = 1;
		$game->save();

		return Redirect::back();

	}

	public function removeMatch($game_id) {
		$game = Games::find($game_id);
		$m = Match::find($game->match_id);
		// return $gr;
		$setting = Settings::where('user_id', '=', Auth::user()->id)
			->where('league_details_id', '=', $m->league_details_id)
			->first(['multiplier']);
		$pool = User::find(Auth::user()->id)->pools()->where('league_details_id', '=', $m->league_details_id)->first();
		$game->delete();
		Games::recalculate($m->groups_id, $setting->multiplier, $pool->amount, Auth::user()->id);
		return Redirect::back();

	}


	public function details($team, $match_id) {
		// $date_str = \DateTime::createFromFormat("d-m-y", $date)->format("Y-m-d");
		$match = Match::find($match_id);
		$games = Games::where('user_id', '=', Auth::user()->id)
			->join('match', 'games.match_id', '=', 'match.id')
			->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
			->join('game_type', 'games.game_type_id', '=', 'game_type.id')
			->join('standings', 'games.standings_id', '=', 'standings.id')
			->where('match_id', '=', $match_id)
			->where('confirmed', '=', 1)
			->get();
			// return $games;
		$home = $match->home;
		$matchesH = Match::matchesForSeason($match->league_details_id, $match->season)
            ->where(function($query) use ($home)
            {
                $query->where('home', '=', $home)
                      ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
			->orderBy('matchDate', 'desc')
			->take(10)
			->get();
		$away = $match->away;
		$matchesA = Match::matchesForSeason($match->league_details_id, $match->season)
            ->where(function($query) use ($away)
            {
                $query->where('home', '=', $away)
                      ->orWhere('away', '=', $away);
            })
            ->where('resultShort', '<>', '-')
			->orderBy('matchDate', 'desc')
			->take(10)
			->get();

		$h2h = Match::where('league_details_id', '=', $match->league_details_id)
            ->where(function($query) use ($away)
            {
                $query->where('home', '=', $away)
                      ->orWhere('away', '=', $away);
            })
            ->where(function($query) use ($home)
            {
                $query->where('home', '=', $home)
                      ->orWhere('away', '=', $home);
            })
            ->where('resultShort', '<>', '-')
			->orderBy('matchDate', 'desc')
			->take(10)
			->get();
		return View::make('details')->with(['data' => $games, 'home' => $matchesH, 'hometeam' => $home, 'awayteam' => $away, 'away' => $matchesA, 'h2h' => $h2h]);
	}

	public function detailsPPM($home, $away, $date) {
		return View::make('ppmdetails');
	}

}