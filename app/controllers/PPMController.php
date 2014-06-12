<?php

class PPMController extends \BaseController {

	public function display($fromdate = "", $todate = ""){
		if ($fromdate == "") {
			$fromdate = date("Y-m-d", time());
		}
		if ($todate == "") {
			$todate = date("Y-m-d", time());
		}


		//TODO get from db as ppm games
		$game_types = [5, 6, 7, 8];
		$games = PPM::where('user_id', '=', Auth::user()->id)
			->join('match', 'match.id', '=', 'ppm.match_id')
			->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
			->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
			->join('series', 'series.id', '=', 'ppm.series_id')
			->whereIn('ppm.game_type_id', $game_types)
			->where('matchDate', '>=', $fromdate)
			->where('matchDate', '<=', $todate)
			->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `series`.`current_length` as 'streak'"))
			->get();
		return View::make('matches')->with(['data' => $games, 'grey' => array(), 'ppm' => true, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' =>$todate]);
	}

	public function getOdds($fromdate = "", $todate = "") {
		if ($fromdate == "") {
			$fromdate = date("Y-m-d", time());
		}
		if ($todate == "") {
			$todate = date("Y-m-d", time());
		}
		$game_types = [5, 6, 7, 8];
		$games = PPM::where('user_id', '=', Auth::user()->id)
			->join('match', 'match.id', '=', 'ppm.match_id')
			->whereIn('game_type_id', $game_types)
			->where('matchDate', '>=', $fromdate)
			->where('matchDate', '<=', $todate)
			->get();
		// $games = PPM::whereIn('match_id', $ids)->where('user_id', '=', Auth::user()->id)->get();
		// return $games;
		foreach ($games as $game) {
			Games::getMatchOddsForGame($game, 1);			
		}
		return Redirect::back();
	}
}
