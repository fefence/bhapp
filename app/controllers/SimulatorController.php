<?php

class SimulatorController extends BaseController {

	public function getSimMatches($country='england', $league='premier-league', $seasoncount='5') {

		$starttime = time();
		$league_details_id = LeagueDetails::where('country', '=', $country)
				->where('fullName', '=', $league)->first()->id;
        
		$tmp = Simulator::join('match', 'simulator.match_id', '=', 'match.id')
					->where('user_id', '=', Auth::user()->id)
					->where('league_details_id', '=', $league_details_id)
					->groupBy('season')
					->lists('season');

		if (count($tmp) < $seasoncount) {
			$todelete = Simulator::join('match', 'simulator.match_id', '=', 'match.id')
					->where('user_id', '=', Auth::user()->id)
					->where('match.league_details_id', '=', $league_details_id)
					->select('simulator.id')
					->lists('id');
			if (count($todelete) > 0) {
				Simulator::whereIn('id', $todelete)->delete();
			}
	        $seasons = ImportedSeasons::where('league_details_id', '=', $league_details_id)
	        		->distinct()
	        		->orderBy('season', 'desc')
	        		->take($seasoncount)
	        		->lists('season');

	        sort($seasons);

			$teams = Match::distinct('home')->where('league_details_id', '=', $league_details_id)->whereIn('season', $seasons)->lists('home');
	        
			foreach ($teams as $team) {
				$count = 0;
				$matches = Match::where(function($query) use ($team)
		        {
		            $query->where('home', '=', $team)
		                  ->orWhere('away', '=', $team);
		        })
		        ->join('mapping', 'mapping.round', '=', 'match.round')
        		->join('odds1x2', 'match.id', '=', 'odds1x2.match_id')
        		->where('bookmaker_id', '=', '1')
		        ->where('league_details_id', '=', $league_details_id)
		        ->whereIn('season', $seasons)
		        ->orderBy('season')
		        ->orderBy('int')
		        ->distinct('match.id')
		        ->get(['match.id', 'odds1x2.oddsX', 'match.resultShort']);
		       	
		    
		        foreach ($matches as $match) {
			        	
			        	$played = new Simulator;

			        	$played->match_id = $match->id;
			        	$played->current_length = $count;
			        	$played->team = $team;
			        	$played->odds = $match->oddsX;
			        	$played->user_id = Auth::user()->id;
			        	$played->save();
		        	if ($match->resultShort == 'D') {
		        		$count = 0;
		        	} else {
		        		$count = $count + 1;
		        	}
		        }

			}
		} else {
			$seasoncount = count($tmp);
		}
		$complete = time() - $starttime;
		return View::make('simulator.simulator')->with(array('country' => $country, 'league' => $league, 'count' => 2, 'init' => 50, 'multiply' => 0.9, 'offset' => 1, 'season' => 0, 'lt' => '>', 'action' => '/simulator', 'bsf' => 0, 'seasoncount' => $seasoncount, 'seasonfrom' => 0, 'time' => $complete, 'rounds' => "", 'from' => 2, 'to' => 6));
	}	

	public function newSim() {
		$starttime = time();

		$seasoncount = Input::get('seasoncount');

		$auto = Input::get('auto');
		// return $auto;
		$country = Input::get('country');
		$league = Input::get('league');
		$count = Input::get('count');
		$roundoffset = Input::get('offset');
		$seasonoffset = Input::get('season');
		$seasonfrom = Input::get('seasonfrom');
		$bsfinit = Input::get('bsf');
		$profit = Input::get('profit');
		$mul = Input::get('multiply');
		$init = Input::get('init');
		$lt = Input::get('lt');
		$r = Input::get('rounds');
		$from = Input::get('from');
		$to = Input::get('to');

		$result = SimulatorController::calculate($auto, $from, $to, $init, $bsfinit, $r, $country, $league, $seasoncount, $seasonfrom, $seasonoffset, $roundoffset, $lt, $count, $mul);
		$complete = time()-$starttime;
		return View::make('simulator.simulator')->with(array('data' => $result, 'country' => $country, 'league' => $league, 'count' => $count, 'init' => $init, 'multiply' => $mul, 'offset' => $roundoffset, 'season' => $seasonoffset, 'lt' => $lt, 'action' => '/simulator', 'bsf' => $bsfinit, 'seasoncount' => $seasoncount, 'seasonfrom' => $seasonfrom, 'time' => $complete, 'rounds' => $r, 'from' => $from, 'to' => $to));

	}


	public static function calculate ($auto, $from, $to, $init, $bsfinit, $r, $country, $league, $seasoncount, $seasonfrom, $seasonoffset, $roundoffset, $lt, $count, $mul) {
		$bet = $init;
		$bsf = $bsfinit;
		$account = -$init;
		$income = 0;
		$roundsArr = explode(',', $r);
		$accountstate = 0;
		$adjcount = 0;

		$league_details_id = LeagueDetails::where('country', '=', $country)
					->where('fullName', '=', $league)->first()->id;


		$seasons = ImportedSeasons::where('league_details_id', '=', $league_details_id)
	        		->distinct()
	        		->orderBy('season', 'desc')
	        		->take($seasoncount)
	        		->lists('season');
	    // return $roundoffset;
        //sort($seasons);
        $result = array();
		for($i = $seasonfrom; $i >= $seasonoffset; $i --) {
			$rounds = Match::where('season', '=', $seasons[$i])
				->where('league_details_id', '=', $league_details_id)
				->distinct('round')
				->lists('round');

			if ($i == $seasonfrom) {
				$start = $roundoffset;
			} else {
				$start = 1;
			}
			for($j = $start; $j <= count($rounds); $j ++) {

				$cwDate = Match::join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
				    ->where('league_details_id', '=', $league_details_id)
				    ->select(['matchDate', DB::raw('count(*) as c')])
				    ->groupBy('matchDate')
				    ->orderBy('c', 'desc')
				    ->first();
				if($cwDate) {
					$cwDate = $cwDate->matchDate;
				} else {
					$cwDate = '2000-01-01';
				}
				$wn = date("W", strtotime($cwDate));
				$result[$seasons[$i]][$wn] = array();

				
				$res_c = Match::join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
				    ->where('league_details_id', '=', $league_details_id);
				$result[$seasons[$i]][$wn]['all_matches'] = $res_c->count();
				$result[$seasons[$i]][$wn]['all_draws'] = $res_c->where('resultShort', '=', 'D')->count();
				$result[$seasons[$i]][$wn]['round'] = $j;

				if (isset($auto) && $auto == "true") {
					for ($t = 0; $t < 100; $t++) {
						$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
				        	->join('mapping', 'mapping.round', '=', 'match.round')
							->where('int', '=', $j)
							->where('season', '=', $seasons[$i])
							->where('user_id', '=', Auth::user()->id)
						    ->where('league_details_id', '=', $league_details_id)
							->where('current_length', '>', $t)
					        ->orderBy('season')
					        ->orderBy('home');
					    $roundMatches = $res->get(['simulator.id']);

						$cm = count($roundMatches);
						if ($cm <= $to){
							if ($cm < $from) {
								$count = $t - 1;
								$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						        	->join('mapping', 'mapping.round', '=', 'match.round')
									->where('int', '=', $j)
									->where('season', '=', $seasons[$i])
									->where('user_id', '=', Auth::user()->id)
								    ->where('league_details_id', '=', $league_details_id)
									->where('current_length', $lt, $count)
							        ->orderBy('season')
							        ->orderBy('home');
							    $roundMatches = $res->get(['simulator.id']);
								$cm = count($roundMatches);

								break 1;
							} else { 
								$count = $t;
							}
							break 1;
						} 
					}
				} else {
					// return $count;
					$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
		        	->join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
					->where('user_id', '=', Auth::user()->id)
				    ->where('league_details_id', '=', $league_details_id)
					->where('current_length', $lt, $count)
			        ->orderBy('season')
			        ->orderBy('home');
				// $roundMatches = $res->get();
					$roundMatches = $res->get(['simulator.id']);

					$cm = count($roundMatches);
				}

				$result[$seasons[$i]][$wn]['filter'] = $count;
				if ($cm > 0) {
					$result[$seasons[$i]][$wn]['all_played'] = $cm;
					$bsfpm = $bsf/$cm;
					$betpm = $bet/$cm;
					foreach ($roundMatches as $rm) {
						$simulator = Simulator::where('id', '=', $rm->id)->where('user_id', '=', Auth::user()->id)->first();
				    	$simulator->bsf = $bsfpm;
				    	$simulator->bet = $betpm;
				    	$simulator->income = $simulator->bet*$simulator->odds;
				    	$simulator->profit = $simulator->bet*$simulator->odds - $simulator->bet - $simulator->bsf;
				    	$simulator->save();
					}


					$draws = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						->join('mapping', 'mapping.round', '=', 'match.round')
						->where('int', '=', $j)
						->where('resultShort', '=', 'D')
						->where('season', '=', $seasons[$i])
						->where('user_id', '=', Auth::user()->id)
					    ->where('league_details_id', '=', $league_details_id)
						->where('current_length', $lt, $count);

					$income = $draws->sum('income');

				    $result[$seasons[$i]][$wn]['draws_played'] = $draws->count();

				    if ($income == NULL || $income == '' || !$income) {
						$income = 0;
					} 

					

					$result[$seasons[$i]][$wn]['bet'] = $bet;
					$result[$seasons[$i]][$wn]['bsf'] = $bsf;
					$result[$seasons[$i]][$wn]['income'] = $income;


					if ($account < 0) {
						$adj = -$account;
						$account = 0;
					} else {
						$adj = 0;
					}
					// $account = $accountstate - $bet;

					$accountstate = $account + $income;

					$result[$seasons[$i]][$wn]['acc'] = $account;
					$result[$seasons[$i]][$wn]['adj'] = $adj;
					$adjcount = $adjcount + $adj;





					$try = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						->join('mapping', 'mapping.round', '=', 'match.round')
						->where('int', '=', $j)
						->where('season', '=', $seasons[$i])
						->where('user_id', '=', Auth::user()->id)
					    ->where('league_details_id', '=', $league_details_id)
						->where('current_length', $lt, $count)
				        ->where('resultShort', '<>', 'D')
				        ->get([DB::raw('sum(bet) as bet'), DB::raw('sum(bsf) as bsf')]);

				    // return $try;

					$bet = $try[0]->bet;
					$bsf = $try[0]->bsf + $bet;

				    if ($bsf == 0) {
				    	$bsf = $init;
				    }
					$bet = $bsf*$mul;
					$result[$seasons[$i]][$wn]['outminadj'] = $accountstate - $adjcount;

					if (in_array($j, $roundsArr)) {
						$bet = $init;
						$result[$seasons[$i]][$wn]['removed_bsf'] = $bsf;
						$result[$seasons[$i]][$wn]['out'] = $accountstate;
						$result[$seasons[$i]][$wn]['real'] = 0;
						$adjcount = 0;
						$account = -$init;
						$bsf = 0;
						// return $bsf;
					} else {
						$result[$seasons[$i]][$wn]['out'] = 0;
						$result[$seasons[$i]][$wn]['removed_bsf'] = 0;
						$result[$seasons[$i]][$wn]['real'] = $accountstate;
						$account = $account - $bet + $income;
					}

					// return View::make('simulator.simulator')->with(array('data' => $res->get(), 'country' => $country, 'league' => $league, 'count' => $count, 'init' => $init, 'multiply' => $mul, 'offset' => $roundoffset, 'season' => $seasonoffset, 'lt' => $lt));
				} else {
					$result[$seasons[$i]][$wn]['acc'] = $account;
					$result[$seasons[$i]][$wn]['bet'] = $bet;
					$result[$seasons[$i]][$wn]['bsf'] = $bsf;
					$result[$seasons[$i]][$wn]['income'] = $income;
					$result[$seasons[$i]][$wn]['adj'] = 0;
					$result[$seasons[$i]][$wn]['real'] = $account + $income;
					$result[$seasons[$i]][$wn]['all_played'] = 0;
					$result[$seasons[$i]][$wn]['draws_played'] = 0;
					$result[$seasons[$i]][$wn]['out'] = 0;
					$result[$seasons[$i]][$wn]['removed_bsf'] = 0;
					$result[$seasons[$i]][$wn]['outminadj'] = 0;
				}

			}
		}
		
		return $result;
	}

	public function newSimPercent() {
		$starttime = time();

		$seasoncount = Input::get('seasoncount');

		$auto = Input::get('auto');
		// return $auto;
		$country = Input::get('country');
		$league = Input::get('league');
		$count = Input::get('count');
		$roundoffset = Input::get('offset');
		$seasonoffset = Input::get('season');
		$seasonfrom = Input::get('seasonfrom');
		$bsfinit = Input::get('bsf');
		$profit = Input::get('profit');
		$mul = Input::get('multiply');
		$init = Input::get('init');
		$lt = Input::get('lt');
		$r = Input::get('rounds');
		$from = Input::get('from');
		$to = Input::get('to');

		$bet = $init;
		$bsf = $bsfinit;
		$account = $init * 100 * $mul - $init;
		$income = 0;
		$roundsArr = explode(',', $r);
		$accountstate = $account;
		$adjcount = 0;

		$league_details_id = LeagueDetails::where('country', '=', $country)
					->where('fullName', '=', $league)->first()->id;


		$seasons = ImportedSeasons::where('league_details_id', '=', $league_details_id)
	        		->distinct()
	        		->orderBy('season', 'desc')
	        		->take($seasoncount)
	        		->lists('season');
	    // return $roundoffset;
        //sort($seasons);
        $result = array();
		for($i = $seasonfrom; $i >= $seasonoffset; $i --) {
			$rounds = Match::where('season', '=', $seasons[$i])
				->where('league_details_id', '=', $league_details_id)
				->distinct('round')
				->lists('round');

			if ($i == $seasonfrom) {
				$start = $roundoffset;
			} else {
				$start = 1;
			}
			for($j = $start; $j <= count($rounds); $j ++) {

				$result[$seasons[$i]][$j] = array();
				
				$cwDate = Match::join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
				    ->where('league_details_id', '=', $league_details_id)
				    ->select(['matchDate', DB::raw('count(*) as c')])
				    ->groupBy('matchDate')
				    ->orderBy('c', 'desc')
				    ->first();
				if($cwDate) {
					$cwDate = $cwDate->matchDate;
				} else {
					$cwDate = '2000-01-01';
				}

				$result[$seasons[$i]][$j] = array();
				
				$res_c = Match::join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
				    ->where('league_details_id', '=', $league_details_id);
				    
				$result[$seasons[$i]][$j]['all_matches'] = $res_c->count();
				$result[$seasons[$i]][$j]['all_draws'] = $res_c->where('resultShort', '=', 'D')->count();
				$result[$seasons[$i]][$j]['wn'] = date("W", strtotime($cwDate));

				if (isset($auto) && $auto == "true") {
					for ($t = 0; $t < 100; $t++) {
						$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
				        	->join('mapping', 'mapping.round', '=', 'match.round')
							->where('int', '=', $j)
							->where('season', '=', $seasons[$i])
							->where('user_id', '=', Auth::user()->id)
						    ->where('league_details_id', '=', $league_details_id)
							->where('current_length', $lt, $t)
					        ->orderBy('season')
					        ->orderBy('home');
					    $roundMatches = $res->get(['simulator.id']);

						$cm = count($roundMatches);

						if ($cm <= $to){
							if ($cm < $from) {
								$count = $t - 1;
								$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						        	->join('mapping', 'mapping.round', '=', 'match.round')
									->where('int', '=', $j)
									->where('season', '=', $seasons[$i])
									->where('user_id', '=', Auth::user()->id)
								    ->where('league_details_id', '=', $league_details_id)
									->where('current_length', $lt, $count)
							        ->orderBy('season')
							        ->orderBy('home');
							    $roundMatches = $res->get(['simulator.id']);

								$cm = count($roundMatches);
								break 1;
							} else { 
								$count = $t;
							}
							break 1;
						} 
					}
				} else {
					// return $count;
					$res = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
		        	->join('mapping', 'mapping.round', '=', 'match.round')
					->where('int', '=', $j)
					->where('season', '=', $seasons[$i])
					->where('user_id', '=', Auth::user()->id)
				    ->where('league_details_id', '=', $league_details_id)
					->where('current_length', $lt, $count)
			        ->orderBy('season')
			        ->orderBy('home');
				// $roundMatches = $res->get();
					$roundMatches = $res->get(['simulator.id']);

					$cm = count($roundMatches);
				}

				$result[$seasons[$i]][$j]['filter'] = $count;
				if ($cm > 0) {
					$result[$seasons[$i]][$j]['all_played'] = $cm;
					$bsfpm = $bsf/$cm;
					$betpm = $bet/$cm;
					foreach ($roundMatches as $rm) {
						$simulator = Simulator::where('id', '=', $rm->id)->where('user_id', '=', Auth::user()->id)->first();
				    	$simulator->bsf = $bsfpm;
				    	$simulator->bet = $betpm;
				    	$simulator->income = $simulator->bet*$simulator->odds;
				    	$simulator->profit = $simulator->bet*$simulator->odds - $simulator->bet - $simulator->bsf;
				    	$simulator->save();
					}


					$draws = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						->join('mapping', 'mapping.round', '=', 'match.round')
						->where('int', '=', $j)
						->where('resultShort', '=', 'D')
						->where('season', '=', $seasons[$i])
						->where('user_id', '=', Auth::user()->id)
					    ->where('league_details_id', '=', $league_details_id)
						->where('current_length', $lt, $count);

					$income = $draws->sum('income');

				    $result[$seasons[$i]][$j]['draws_played'] = $draws->count();

				    if ($income == NULL || $income == '' || !$income) {
						$income = 0;
					} 

					

					$result[$seasons[$i]][$j]['bet'] = $bet;
					$result[$seasons[$i]][$j]['bsf'] = $bsf;
					$result[$seasons[$i]][$j]['income'] = $income;


					if ($account < 0) {
						$adj = -$account;
						$account = 0;
					} else {
						$adj = 0;
					}
					// $account = $accountstate - $bet;

					$accountstate = $account + $income;

					$result[$seasons[$i]][$j]['acc'] = $account;
					$result[$seasons[$i]][$j]['adj'] = $adj;
					$adjcount = $adjcount + $adj;





					$try = Match::join('simulator', 'simulator.match_id', '=', 'match.id')
						->join('mapping', 'mapping.round', '=', 'match.round')
						->where('int', '=', $j)
						->where('season', '=', $seasons[$i])
						->where('user_id', '=', Auth::user()->id)
					    ->where('league_details_id', '=', $league_details_id)
						->where('current_length', $lt, $count)
				        ->where('resultShort', '<>', 'D')
				        ->get([DB::raw('sum(bet) as bet'), DB::raw('sum(bsf) as bsf')]);

				    // return $try;

					// $bet = $try[0]->bet;
					// $bsf = $try[0]->bsf + $bet;

				    if ($bsf == 0) {
				    	$bsf = $init;
				    }
					$bet = $accountstate*$mul;
					$result[$seasons[$i]][$j]['outminadj'] = $accountstate - $adjcount;

					if (in_array($j, $roundsArr)) {
						$bet = $init;
						$result[$seasons[$i]][$j]['removed_bsf'] = $bsf;
						$result[$seasons[$i]][$j]['out'] = $accountstate;
						$result[$seasons[$i]][$j]['real'] = 0;
						$adjcount = 0;
						$account = $init / $mul - $init;
						$bsf = 0;
						// return $bsf;
					} else {
						$result[$seasons[$i]][$j]['out'] = 0;
						$result[$seasons[$i]][$j]['removed_bsf'] = 0;
						$result[$seasons[$i]][$j]['real'] = $accountstate;
						$account = $account - $bet + $income;
					}

					// return View::make('simulator.simulator')->with(array('data' => $res->get(), 'country' => $country, 'league' => $league, 'count' => $count, 'init' => $init, 'multiply' => $mul, 'offset' => $roundoffset, 'season' => $seasonoffset, 'lt' => $lt));
				} else {
					$result[$seasons[$i]][$j]['acc'] = $account;
					$result[$seasons[$i]][$j]['bet'] = $bet;
					$result[$seasons[$i]][$j]['bsf'] = $bsf;
					$result[$seasons[$i]][$j]['income'] = $income;
					$result[$seasons[$i]][$j]['adj'] = 0;
					$result[$seasons[$i]][$j]['real'] = $account + $income;
					$result[$seasons[$i]][$j]['all_played'] = 0;
					$result[$seasons[$i]][$j]['draws_played'] = 0;
					$result[$seasons[$i]][$j]['out'] = 0;
					$result[$seasons[$i]][$j]['removed_bsf'] = 0;
					$result[$seasons[$i]][$j]['outminadj'] = 0;
				}

			}
		}
		
		$complete = time()-$starttime;

		// return $result;
		return View::make('simulator.simulator')->with(array('data' => $result, 'country' => $country, 'league' => $league, 'count' => $count, 'init' => $init, 'multiply' => $mul, 'offset' => $roundoffset, 'season' => $seasonoffset, 'lt' => $lt, 'action' => '/simulator', 'bsf' => $bsfinit, 'seasoncount' => $seasoncount, 'seasonfrom' => $seasonfrom, 'time' => $complete, 'rounds' => $r, 'from' => $from, 'to' => $to));

	}

	public function getSimMerged($count = 3) {
        $country = ['france', 'france', 'france'];
        $league = ['ligue-1', 'ligue-2', 'national'];
        $init = ['100', '100', '100'];
        $multiply = ['0.9', '0.9', '0.9'];
        $rounds = ['2,5', '2,5', '2,5'];
		return View::make('simulator.mergedsim')->with(['count' => $count, 'country' => $country, 'league' => $league, 'multiply' => $multiply, 'init' => $init, 'rounds' => $rounds]);
	}
	public function newSimMerged() {
		$count = Input::get('count');
		$country = Input::get('country');
		$league = Input::get('league');
		$multiply = Input::get('multiply');
		$init = Input::get('init');
		$rounds = Input::get('rounds');
		$res = array();
		for($i = 0; $i < $count; $i ++) {
			$arr = SimulatorController::calculate (true, 2, 6, $init[$i], 0, $rounds[$i], $country[$i], $league[$i], 5, 0, 0, 1, '>', 1, $multiply[$i]);
			foreach ($arr as $season => $value) {
				foreach ($value as $wn => $vals) {
					if(!array_key_exists($wn, $res)) {
						$res[$wn] = array();
					}
					$res[$wn][$country[$i]."#".$league[$i]] = $vals;
				}
			}
		}

		// return $res;
		return View::make("simulator.mergedsim")->with(['data' => $res, 'count' => $count, 'country' => $country, 'league' => $league, 'multiply' => $multiply, 'init' => $init]);
	}

}