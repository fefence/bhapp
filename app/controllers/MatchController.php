
<?php

class MatchController extends BaseController {

	public function showWelcome($country, $leagueName, $season)	{
		
		return View::make('stats');
	
	}

	public function getStats($country, $leagueName, $season) {
		
		$league = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $leagueName)->first();
		
		$distResults = $this->getUniqueResults($league->id, $season);

		$allCount = Match::matchesForSeason($league->id, $season)->count();
		$drawCount = Match::matchesForSeason($league->id, $season)->where('resultShort', '=', 'D')->count();
		$homeCount = Match::matchesForSeason($league->id, $season)->where('resultShort', '=', 'H')->count();
		$awayCount = Match::matchesForSeason($league->id, $season)->where('resultShort', '=', 'A')->count();
		$seq = $this->getSequences($country, $leagueName, $season);
		$sSeq = Match::matchesForSeason($league->id, $season)->get(array('resultShort', 'home', 'away', 'matchDate', 'matchTime', 'homeGoals', 'awayGoals'));
		$pps1x2 = SeriesController::getSeriesForMatches($league->id, $season, 1);
		$pps00 = SeriesController::getSeriesForMatches($league->id, $season, 2);
		$pps11 = SeriesController::getSeriesForMatches($league->id, $season, 3);
		$pps22 = SeriesController::getSeriesForMatches($league->id, $season, 4);
		$homeGoals = Match::matchesForSeason($league->id, $season)->sum('homeGoals');
		$awayGoals = Match::matchesForSeason($league->id, $season)->sum('awayGoals');
		$goals = $homeGoals + $awayGoals;
		$over = '??';//Match::matchesForSeason($leagueId, $season)->where('homeGoals + awayGoals', '>', 2.5)->count();
		$under = '??';//Match::matchesForSeason($leagueId, $season)->where('homeGoals + awayGoals', '<', 2.5)->count();
		
		$count = Match::where('league_details_id', '=', $league->id)->where('season', '=', $season)->groupBy('home')->count();
		$count = $count * 2;

		$array = array('count' => $count,
						'league' => $leagueName, 
						'ppm' => $league->ppm, 
						'country' => $country,
						'season' => $season,
						'all' => $allCount, 
						'draw' => $drawCount, 
						'home' => $homeCount,
						'away' => $awayCount,
						'distResults' => $distResults, 
						'seq' => $seq, 
						'sSeq' => $sSeq, 
						'pps1x2' => $pps1x2,
						'pps00' => $pps00,
						'pps11' => $pps11,
						'pps22' => $pps22,
						'goals' => $goals,
						'homeGoals' => $homeGoals,
						'awayGoals' => $awayGoals,
						'over' => $over,
						'under' => $under);

		return View::make('stats')->with('data', $array);
	
	}

	public function getSequences($country, $leagueName, $season) {

		$seq = array();
		$leagueId = LeagueDetails::getId($country, $leagueName);
		$teams = Match::matchesForSeason($leagueId, $season)->distinct('home')->get(array('home'));
		foreach ($teams as $team) {
			$res = array();
			$regexp = $team->home;
			
			$matches = Match::matchesForSeason($leagueId, $season)
            ->where(function($query) use ($regexp)
            {
                $query->where('home', '=', $regexp)
                      ->orWhere('away', '=', $regexp);
            })
			->orderBy('matchDate', 'desc')->get();
			
			$seq = array_add($seq, $team->home, $matches);
		}	

		return $seq;
	}


	private function getUniqueResults($leagueId, $season) {

		return Match::where('league_details_id', '=', $leagueId)->where('season', '=', $season)->select('homeGoals', 'awayGoals', DB::raw('count(*) as total'))
                 ->groupBy('homeGoals', 'awayGoals')->orderBy('homeGoals', 'ASC')->orderBy('awayGoals', 'ASC')
                 ->get();
	}

	public function getTodaysMatches($from = '2014-05-05', $to = '2014-05-05'){

	 	$res = Match::whereIn('league_details_id', array(7))->where('matchDate', '>', '2014-04-05')->get();
	 	
		return View::make('home')->with(array('data' => $res));
	}

	public function getMatches($from = "", $to = "") {

        $res = Match::join('played', 'played.match_id', '=', 'match.id')
        	->join('game_type', 'game_type.id', '=', 'played.game_type_id')
        	->join('settings', 'settings.id', '=', 'played.settings_id')
        	->join('leagueDetails', 'leagueDetails.id', '=', 'settings.league_details_id')
	        ->where('user_id', '=', Auth::user()->id)
	        ->where('ignore', '=', 0)
	        ->get(); 

		return View::make('matches')->with(array('data' => $res));
	}

	public function getNextMatchesForPlay($country, $league) {
		$league_details_id = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $league)->first(['id']);
		$teams = Match::distinct('home')->where('league_details_id', '=', $league_details_id->id)->where('season', '=', '2013-2014')->lists('home');
		$m = array();
		$i = 0;
		foreach ($teams as $team) {
			$m[$i] = Match::getNextMatchForTeamLeague($team, $league_details_id->id)->toArray();
			$i ++;
		}
		// $m = array_unique($m);
		return View::make('temptoplay')->with(array('data' => $m));
	}


	// public function save() {
	// 	$input = Input::all();
	// 	if ($input['column'] === 9 || $input['column'] === '9') {
		
	// 		$played = PlayedSim::where('id', '=', $input['id'])->where('match_id', '=', $input['row_id'])->where('user_id', '=', Auth::user()->id)->first();
	// 		$played->bet = $input['value'];
	// 		$played->income = $played->bet*$played->odds;
	// 		$played->profit = $played->income - $played->bsf - $played->bet;
	// 		$played->save();

	// 	}
	// 	if ($input['column'] === 8 || $input['column'] ==='8') {

	// 		$played = PlayedSim::where('id', '=', $input['id'])->where('match_id', '=', $input['row_id'])->where('user_id', '=', Auth::user()->id)->first();
	// 		$played->bsf = $input['value'];
	// 		$played->income = $played->bet*$played->odds;
	// 		$played->profit = $played->income - $played->bsf - $played->bet;
	// 		$played->save();
	// 	}

	// 	$p = PlayedSim::where('id', '=', $input['id'])->where('match_id', '=', $input['row_id'])->where('user_id', '=', Auth::user()->id)->firstOrFail();

	// 	echo $p->bet."#".$p->odds."#".$p->income."#".$p->profit."#".$p->bsf;
	// }

}
