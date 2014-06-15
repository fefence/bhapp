<?php

class PPMController extends \BaseController
{

    public function display($fromdate = "", $todate = "")
    {
        $games = PPM::ppmForDates($fromdate, $todate);
        $count = array();
        foreach ($games as $g) {
            $count[$g->id] = User::find(Auth::user()->id)->ppm()->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
        }
        return View::make('matches')->with(['data' => $games, 'grey' => array(), 'ppm' => true, 'league_details_id' => -1, 'from' => $fromdate, 'to' => $todate, 'count' => $count]);
    }

    public function getOdds($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPM::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('game_type_id', '=', 5)
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->select([DB::raw('ppm.id as id, ppm.*')])
            ->get();
        Parser::parseMatchOddsForGames($games);
        return Redirect::back();
    }


}
