<?php

class GroupController extends \BaseController {

	

	private static function createOperationalGroup($league_details_id, $round) {
		$current = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => $round, 'state' => 2]);
		$current->save();
		// return $current;
		$next = Groups::firstOrNew(['league_details_id' => $league_details_id, 'round' => ($round + 1), 'state' => 3]);
		$next->save();
		Parser::parseMatchesForGroup($current, $next);
		Parser::parseLeagueSeries($current);
	}

	public function addLeaguesToPlay() {
		$leagues = LeagueDetails::orderBy('country')->get();
		$toPlay = Groups::where('state', '=', 2)->lists('round', 'league_details_id');
		return View::make('addleagues')->with(['leagues' => $leagues, 'toPlay' => $toPlay]);
	}

	public function saveLeagues(){
		$ids = Input::get('ids');
		$notIn = Groups::where('state', '=', 2)->lists('league_details_id');
		foreach ($ids as $id) {
			if (!in_array($id, $notIn)) {
				$round = Input::get('v-'.$id);
				GroupController::createOperationalGroup($id, $round);
			}
		}
		return Redirect::back();
	}
}
