<?php

class PPMController extends \BaseController
{

    public function display($country = "", $fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $games = PPM::ppmForDatesCountry($fromdate, $todate, $country);
        $placeholders = PPMPlaceHolder::placeholdersForDatesCountry($fromdate, $todate, $country);
//        return $placeholders;
        $count = array();
        $count_pl = array();
        $league_ids = array();
        foreach ($games as $g) {
            $count[$g->id] = User::find(Auth::user()->id)->ppm()->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
            array_push($league_ids, $g->league_details_id);
        }
        foreach ($placeholders as $g) {
            $count_pl[$g->id] = User::find(Auth::user()->id)->ppm_placeholders()->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
            array_push($league_ids, $g->league_details_id);
        }
        $datarr = array();
        $datarr[0] = $games;
        if (count($league_ids) > 0) {
            $standings = Standings::whereIn('league_details_id', $league_ids)->lists('place', 'team');
        } else {
            $standings = array();
        }
        $league = LeagueDetails::where('country', '=', $country)->where('ppm', '=', 1)->first();
//        $datarr[1] = array();
        return View::make('ppm')->with(['country' => $league->country, 'placeholders' => $placeholders, 'datarr' => $datarr, 'standings' => $standings, 'ppm' => true, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' => $todate, 'count' => $count, 'countpl' => $count_pl, 'big' => $big, 'small' => $small, 'league' => $league, 'base' => 'ppm/country/' . $country]);
    }

    public function displayCountries($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $leagues = PPM::ppmLeaguesForDates($fromdate, $todate);
        $info = array();
        foreach ($leagues as $league) {
            $info[$league->country]['all'] = count(PPM::ppmForDatesCountry($fromdate, $todate, $league->country));
            $info[$league->country]['confirmed'] = PPM::ppmConfirmedForLeague($fromdate, $todate, $league);
        }
        return View::make('ppmcountries')->with(['all_btn' => 'flat', 'data' => $leagues, 'info' => $info, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small, 'all_link' => "ppm/flat/$fromdate/$todate", 'ppm' => true]);
    }

    public static function getOddsForCountry($country, $fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPM::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->select([DB::raw('ppm.id as id, ppm.*')])
            ->get();
        $placeholders = PPMPlaceHolder::placeholdersForDatesCountry($fromdate, $todate, $country);

        $err = Parser::parseMatchOddsForGames($games);
        $err2 = Parser::parseMatchOddsForGames($placeholders);
        if ($err || $err2) {
            return Redirect::back()->with('warning', 'Odds not refreshed correctly');
        }
        return Redirect::back()->with('message', 'Odds refreshed');

    }

    public function getOdds($fromdate = "", $todate = "")
    {
        $start = time();
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPM::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->select([DB::raw('ppm.id as id, ppm.*')])
            ->get();
        Parser::parseMatchOddsForGames($games);
        return Redirect::back()->with('message', 'Odds refreshed ' . (time() - $start) . " sec");
    }

    public static function displaySeries($id)
    {
//        return $id;
        $country = Series::find($id)->team;
        $games = PPM::where('series_id', '=', $id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'bookmaker_id')
            ->join('game_type', 'game_type.id', '=', 'game_type_id')
            ->where('user_id', '=', Auth::user()->id)
            ->orderBy('current_length', 'desc')
            ->get();
        $data = array();
        foreach ($games as $game) {
            if ($game->confirmed == 0) {
                $count = PPM::where('match_id', '=', $game->match_id)
                    ->where('game_type_id', '=', $game->game_type_id)
                    ->where('user_id', '=', $game->user_id)
                    ->where('bookmaker_id', '=', $game->bookmaker_id)
                    ->where('confirmed', '=', 1)
                    ->count();
                if ($count == 0) {
                    array_push($data, $game);
                }
            } else {
                array_push($data, $game);
            }
        }

        return View::make('ppmseriesdetails')->with(['games' => $data, 'league' => $country, 'series' => $id]);
    }

    public static function displayFlatView($fromdate = '', $todate = '')
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $leagues = Settings::where('user_id', '=', Auth::user()->id)
            ->where('game_type_id', '>=', 5)
            ->where('game_type_id', '<=', 8)
            ->lists('league_details_id');
        if (count($leagues) > 0) {
            $matches = Match::where('matchDate', '<=', $todate)
                ->where('matchDate', '>=', $fromdate)
                ->whereIn('league_details_id', $leagues)
                ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
                ->orderBy('matchTime')
                ->select('leagueDetails.country', 'match.*')
                ->get();
        } else {
            $matches = array();
        }
        $res = array();
        foreach ($matches as $m) {
            $all = $m->ppm()->where('user_id', '=', Auth::user()->id)
                ->count();
            $conf = $m->ppm()->where('user_id', '=', Auth::user()->id)
                ->where('confirmed', '=', 1)
                ->count();
            $res[$m->id] = array();
            $res[$m->id]['match'] = $m;
            $res[$m->id]['all'] = $all - $conf;
            $res[$m->id]['conf'] = $conf;
            if ($all == 0) {
                $res[$m->id]['all'] = '-';
                $res[$m->id]['conf'] = '-';
            }
            $res[$m->id][5] = Series::where('end_match_id', '=', $m->id)->where('game_type_id', '=', 5)->where('active', '=', 1)->first();
            if ($res[$m->id][5] != null) {
                $res[$m->id][5] = $res[$m->id][5]->current_length;
            } else {
                $res[$m->id][5] = '-';
            }
            $res[$m->id][6] = Series::where('end_match_id', '=', $m->id)->where('game_type_id', '=', 6)->where('active', '=', 1)->first();
            if ($res[$m->id][6] != null) {
                $res[$m->id][6] = $res[$m->id][6]->current_length;
            } else {
                $res[$m->id][6] = '-';
            }
            $res[$m->id][7] = Series::where('end_match_id', '=', $m->id)->where('game_type_id', '=', 7)->where('active', '=', 1)->first();
            if ($res[$m->id][7] != null) {
                $res[$m->id][7] = $res[$m->id][7]->current_length;
            } else {
                $res[$m->id][7] = '-';
            }
            $res[$m->id][8] = Series::where('end_match_id', '=', $m->id)->where('game_type_id', '=', 8)->where('active', '=', 1)->first();
            if ($res[$m->id][8] != null) {
                $res[$m->id][8] = $res[$m->id][8]->current_length;
            } else {
                $res[$m->id][8] = '-';
            }
        }
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, -1);
        return View::make('flat')->with(['disable_all' => true, 'matches' => $res, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }

}
