<?php

class PPMController extends \BaseController {

	public function display($fromdate = "", $todate = ""){
		if ($fromdate == "") {
			$fromdate = date("Y-m-d", time());
		}
		if ($todate == "") {
			$todate = date("Y-m-d", time());
		}


		$games = PPM::where('user_id', '=', Auth::user()->id)
			->join('match', 'match.id', '=', 'ppm.match_id')
			->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
			->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
			->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
			->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `series`.`current_length` as 'streak'"))
			->get();

        $count = array();
        foreach($games as $g) {
            $count[$g->id] = User::find(Auth::user()->id)->ppm()->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
        }
		return View::make('matches')->with(['data' => $games, 'grey' => array(), 'ppm' => true, 'league_details_id' => -1, 'from' => $fromdate, 'to' =>$todate, 'count' => $count]);
	}

	public function getOdds($fromdate = "", $todate = "") {
		if ($fromdate == "") {
			$fromdate = date("Y-m-d", time());
		}
		if ($todate == "") {
			$todate = date("Y-m-d", time());
		}
		$games = PPM::where('user_id', '=', Auth::user()->id)
			->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('game_type_id', '=', 5)
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
			->where('matchDate', '<=', $todate)
			->get();
		Parser::parseMatchOddsForGames($games);
        return Redirect::back();
	}
}
