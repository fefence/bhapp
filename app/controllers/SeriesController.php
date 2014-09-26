<?php

class SeriesController extends BaseController
{

    public static function calculatePPMSeries($id)
    {
//        $ids = [1, 6, 17, 35, 39];
//        , 69, 74, 85, 100
        $leagues = LeagueDetails::where('id', '=', $id)->get();

        foreach ($leagues as $league) {
            $matches = Match::where('league_details_id', '=', $league->id)
                ->where('season', '>=', '2010-2011')
                ->orderBy('matchDate')
                ->orderBy('matchTime')
                ->get(array('id', 'resultShort', 'home', 'away', 'matchDate', 'matchTime', 'homeGoals', 'awayGoals'));

            foreach ($matches as $match) {
                for ($i = 5; $i < 9; $i++) {
                    $series = SeriesStats::where('team', '=', $league->country)->where('active', '=', 1)->where('game_type_id', '=', $i)->first();
                    if ($series == NULL) {
                        $series = new SeriesStats;
                        $series->team = $league->country;
                        $series->league_details_id = $league->id;
                        $series->game_type_id = $i;
                        $series->current_length = 0;
                        $series->start_match_id = $match->id;
                        $series->active = 1;
                        $series->save();
                    }
                    $series->current_length = $series->current_length + 1;
                    $series->end_match_id = $match->id;
                    $series->league_details_id = $league->id;
                    if (SeriesController::endSeries($match, $i)) {
                        $series->active = 0;
                        $duplicate = SeriesStats::where('start_match_id', '=', $series->start_match_id)
                            ->where('end_match_id', '=', $series->end_match_id)
                            ->where('team', '=', $league->country)
                            ->where('current_length', '=', $series->current_length)
                            ->where('game_type_id', '=', $series->game_type_id)->first();
                        if ($duplicate) {
                            $duplicate->delete();
                        }
                    }
                    $series->save();
                }
                if ($match->resultShort == '-' || $match->resultShort == '')
                    break 1;
            }
        }
        return "finished";

    }

    public function calculatePPSSeries($country)
    {
        $start = time();
        $ids = LeagueDetails::where('country', '=', $country)->lists('id');
        // $ids = array(20, 21);
        $teams = Match::whereIn('league_details_id', $ids)->distinct('home')->get(array('home'));

        foreach ($teams as $team) {

            $regexp = $team->home;

            // $ids = \DB::table('series')->lists('end_match_id');

            $matches = Match::whereIn('league_details_id', $ids)
                ->where(function ($query) use ($regexp) {
                    $query->where('home', '=', $regexp)
                        ->orWhere('away', '=', $regexp);
                })
                ->orderBy('matchDate', 'asc')->orderBy('matchTime', 'asc')->get();
            $prev_season = '2003-2004';
            $prev_league = '-1';
            foreach ($matches as $match) {
                for ($i = 1; $i < 5; $i++) {
                    $note = "";
                    $series = Series::where('team', '=', $team->home)->where('active', '=', 1)->where('game_type_id', '=', $i)->first();
                    if ($series == NULL) {
                        $series = new Series;
                        $series->team = $team->home;
                        $series->game_type_id = $i;
                        $series->current_length = 0;
                        $series->start_match_id = $match->id;
                        $series->active = 1;
                        $series->save();
                    }

                    $series->current_length = $series->current_length + 1;
                    $series->end_match_id = $match->id;
                    $series->league_details_id = $match->league_details_id;

                    if ($this->endSeries($match, $i)) {
                        $series->active = 0;
                        $duplicate = Series::where('start_match_id', '=', $series->start_match_id)
                            ->where('end_match_id', '=', $series->end_match_id)
                            ->where('team', '=', $team->home)
                            ->where('current_length', '=', $series->current_length)
                            ->where('game_type_id', '=', $series->game_type_id)->first();
                        if ($duplicate) {
                            $duplicate->delete();
                        }
                    }

                    if ($match->league_details_id != $prev_league) {
                        $note = "continued from $prev_league in " . $match->league_details_id . " league";
                    }
                    $series->note = $note;

                    $series->save();
                }
                $prev_league = $match->league_details_id;
                $prev_season = $match->season;
                if ($match->resultShort == '-' || $match->resultShort == '')
                    break 1;
            }
        }
        $time = time() - $start;
        return "ended in $time sec";
    }

    public function calculatePPSSeriesForTeam($country, $team)
    {
        $start = time();
        $ids = LeagueDetails::where('country', '=', $country)->lists('id');

        $regexp = $team;

        // $ids = \DB::table('series')->lists('end_match_id');

        $matches = Match::whereIn('league_details_id', $ids)
            ->where(function ($query) use ($regexp) {
                $query->where('home', '=', $regexp)
                    ->orWhere('away', '=', $regexp);
            })
            ->orderBy('matchDate', 'asc')->orderBy('matchTime', 'asc')->get();
        $prev_season = '2003-2004';
        $prev_league = '-1';
        foreach ($matches as $match) {
            for ($i = 1; $i < 5; $i++) {
                $note = "";
                $series = Series::where('team', '=', $team)->where('active', '=', 1)->where('game_type_id', '=', $i)->first();
                if ($series == NULL) {
                    $series = new Series;
                    $series->team = $team;
                    $series->game_type_id = $i;
                    $series->current_length = 0;
                    $series->start_match_id = $match->id;
                    $series->active = 1;
                    $series->save();
                }

                $series->current_length = $series->current_length + 1;
                $series->end_match_id = $match->id;
                $series->league_details_id = $match->league_details_id;

                if ($this->endSeries($match, $i)) {
                    $series->active = 0;
                    $duplicate = Series::where('start_match_id', '=', $series->start_match_id)
                        ->where('end_match_id', '=', $series->end_match_id)
                        ->where('team', '=', $team)
                        ->where('current_length', '=', $series->current_length)
                        ->where('game_type_id', '=', $series->game_type_id)->first();
                    if ($duplicate) {
                        $duplicate->delete();
                    }
                }

                if ($match->league_details_id != $prev_league) {
                    $note = "continued from $prev_league in " . $match->league_details_id . " league";
                }
                $series->note = $note;

                $series->save();
            }
            $prev_league = $match->league_details_id;
            $prev_season = $match->season;
            if ($match->resultShort == '-' || $match->resultShort == '')
                break 1;
        }
        $time = time() - $start;
        return "ended in $time sec";
    }

    public static function endSeries($match, $type)
    {
        switch ($type) {
            case '1':
                if ($match->resultShort == 'D')
                    return true;
                else return false;
            case '2':
                if ($match->resultShort == 'D' && $match->homeGoals == 0 && $match->awayGoals == 0)
                    return true;
                else return false;
            case '3':
                if ($match->resultShort == 'D' && $match->homeGoals == 1 && $match->awayGoals == 1)
                    return true;
                else return false;
            case '4':
                if ($match->resultShort == 'D' && $match->homeGoals == 2 && $match->awayGoals == 2)
                    return true;
                else return false;
            case '5':
                if ($match->resultShort == 'D')
                    return true;
                else return false;
            case '6':
                if ($match->resultShort == 'D' && $match->homeGoals == 0 && $match->awayGoals == 0)
                    return true;
                else return false;
            case '7':
                if ($match->resultShort == 'D' && $match->homeGoals == 1 && $match->awayGoals == 1)
                    return true;
                else return false;
            case '8':
                if ($match->resultShort == 'D' && $match->homeGoals == 2 && $match->awayGoals == 2)
                    return true;
                else return false;
        }
    }

    public function getSeries()
    {

        $res = DB::table('series')->join('match', 'match.id', '=', 'series.start_match_id')
            ->where('team', '=', 'Manchester United')->where('season', '=', '2013-2014')->get();
        return $res;
    }

    public function updateAllPPSSeries()
    {
        $starttime = time();
        $today = date('Y-m-d', time());

        $date = strtotime($today . "-1 month");
        $start = date('Y-m-d', $date);

        $series = Series::join('match', 'match.id', '=', 'series.end_match_id')
            ->where('matchDate', '<', $today)
            ->where('matchDate', '>=', $start)
            ->get();
        foreach ($series as $serie) {
            $next = Match::getNextMatchForTeam($serie->team, $serie);
            if ($next != NULL) {
                $serie->current_length = $serie->current_length + 1;
                $serie->end_match_id = $next->id;
                $next_id = $next->id;
                if ($this->endSeries($serie, $serie->game_type_id)) {
                    $serie->active = 0;
                    $duplicate = Series::where('start_match_id', '=', $serie->start_match_id)
                        ->where('end_match_id', '=', $serie->end_match_id)
                        ->where('team', '=', $serie->team)
                        ->where('current_length', '=', $serie->current_length)
                        ->where('game_type_id', '=', $serie->game_type_id)->first();
                    if ($duplicate) {
                        $duplicate->delete();
                    }
                    $serie->save();
                    $s = new Series;
                    $s->team = $serie->team;
                    $s->game_type_id = $serie->game_type_id;
                    $s->current_length = 1;
                    $s->start_match_id = $next_id;
                    $s->end_match_id = $next_id;
                    $s->active = 1;
                    $s->save();

                } else {
                    $serie->end_match_id = $next_id;
                    $serie->save();
                }
            }
        }
        $endtime = time() - $starttime;
        return $endtime . " sec. for " . count($matches) . " matches";
    }

    public static function getSeriesForMatches($league_details_id, $season, $game_type_id)
    {
        $teams = Match::matchesForSeason($league_details_id, $season)->distinct('home')->get(array('home'));
        $res = array();
        foreach ($teams as $team) {
            // $seq[$team->home] = array();
            $res[$team->home] = DB::table('series')
                ->join('match', 'match.id', '=', 'series.end_match_id')
                ->where('season', '=', $season)
                ->where('team', '=', $team->home)
                ->where('game_type_id', '=', $game_type_id)
                ->orderBy('matchDate', 'asc')
                ->get();
        }
        return $res;
    }

    public static function getSeriesForSeason($league_details_id, $game_type_id, $season)
    {
        $series = SeriesStats::where('match.league_details_id', '=', $league_details_id)
            ->where('game_type_id', '=', $game_type_id)
            ->join('match', 'match.id', '=', 'series_stats.end_match_id')
            ->where('season', '=', $season)
            ->orderBy('matchDate')
            ->get();
        return $series;
    }

    public static function getLongestPPMSeries($league_details_id, $season)
    {
        $res = array();
        $res[5] = SeriesStats::where('match.league_details_id', '=', $league_details_id)
            ->where('game_type_id', '=', 5)
            ->join('match', 'match.id', '=', 'series_stats.end_match_id')
            ->where('season', '=', $season)
            ->orderBy('current_length', "desc")
            ->take(5)
            ->lists('current_length');
        $res[6] = SeriesStats::where('match.league_details_id', '=', $league_details_id)
            ->where('game_type_id', '=', 6)
            ->join('match', 'match.id', '=', 'series_stats.end_match_id')
            ->where('season', '=', $season)
            ->orderBy('current_length', "desc")
            ->take(5)
            ->lists('current_length');
        $res[7] = SeriesStats::where('match.league_details_id', '=', $league_details_id)
            ->where('game_type_id', '=', 7)
            ->join('match', 'match.id', '=', 'series_stats.end_match_id')
            ->where('season', '=', $season)
            ->orderBy('current_length', "desc")
            ->take(5)
            ->lists('current_length');
        $res[8] = SeriesStats::where('match.league_details_id', '=', $league_details_id)
            ->where('game_type_id', '=', 8)
            ->join('match', 'match.id', '=', 'series_stats.end_match_id')
            ->where('season', '=', $season)
            ->orderBy('current_length', "desc")
            ->take(5)
            ->lists('current_length');
        return $res;
    }

    public function percentStat($country, $league)
    {
        $leagueDetails = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $league)->first();
        $res = array();
        $seasons = ImportedSeasons::distinct()->where('league_details_id', '=', $leagueDetails->id)->get();
        foreach ($seasons as $season) {
            $res[$season->season] = array();
            for ($i = 1; $i < 16; $i++) {
                $count = Series::join('match', 'match.id', '=', 'series.end_match_id')
                    ->where('match.league_details_id', '=', $leagueDetails->id)
                    ->where('game_type_id', '=', 1)
                    ->where('season', '=', $season->season)->where('current_length', '=', $i)->count();
                $res[$season->season][$i] = $count;
            }
            $res[$season->season][16] = Series::join('match', 'match.id', '=', 'series.end_match_id')
                ->where('match.league_details_id', '=', $leagueDetails->id)
                ->where('game_type_id', '=', 1)
                ->where('season', '=', $season->season)->where('current_length', '>', 15)->count();
            $res[$season->season][17] = Series::join('match', 'match.id', '=', 'series.end_match_id')
                ->where('match.league_details_id', '=', $leagueDetails->id)
                ->where('game_type_id', '=', 1)
                ->where('season', '=', $season->season)->count();
        }

        return View::make('drawstats')->with(array('country' => $country, 'league' => $league, 'data' => $res));

    }


    public function percentDraws()
    {
        $leagueDetails = LeagueDetails::all();
        $res = array();
        foreach ($leagueDetails as $league) {

            $seasons = ImportedSeasons::distinct()->where('league_details_id', '=', $league->id)->get();
            foreach ($seasons as $season) {
                $count = DB::table('match')
                    ->where('league_details_id', '=', $league->id)
                    ->where('resultShort', '=', 'D')
                    ->where('season', '=', $season->season)
                    ->count();
                $countAll = DB::table('match')
                    ->where('league_details_id', '=', $league->id)
                    ->where('season', '=', $season->season)
                    ->count();

                $res[$league->country][$league->fullName][$season->season][0] = $count;
                $res[$league->country][$league->fullName][$season->season][1] = $countAll;
            }


        }
        // return $res;
        return View::make('drawspercent')->with(array('data' => $res));

    }

    public function percentDrawsPerRound($country, $league)
    {
        $leagueDetails = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $league)->first();
        $res = array();
        $seasons = ImportedSeasons::distinct()->where('league_details_id', '=', $leagueDetails->id)->get();
        foreach ($seasons as $season) {
            $res[$season->season] = array();
            $rounds = Match::where('league_details_id', '=', $leagueDetails->id)
                ->where('season', '=', $season->season)
                ->orderBy('matchDate')
                ->lists('round');
            foreach ($rounds as $round) {
                $res[$season->season][$round][0] = Match::where('league_details_id', '=', $leagueDetails->id)
                    ->where('season', '=', $season->season)
                    ->where('round', '=', $round)
                    ->orderBy('matchDate')
                    ->select(DB::raw('count(*) as total'))
                    ->first()->total;
                $res[$season->season][$round][1] = Match::where('league_details_id', '=', $leagueDetails->id)
                    ->where('season', '=', $season->season)
                    ->where('resultShort', '=', 'D')
                    ->where('round', '=', $round)
                    ->orderBy('matchDate')
                    ->select(DB::raw('count(*) as total'))
                    ->first()->total;
            }
        }
        return View::make('roundpercent')->with(array('country' => $country, 'league' => $league, 'data' => $res));

    }

    public static function getPPMSeries($country)
    {
        $league = LeagueDetails::where('country', '=', $country)->where('ppm', '=', 1)->first();
        $seasons = Match::where('match.league_details_id', '=', $league->id)
            ->join('series_stats', 'series_stats.end_match_id', '=', 'match.id')
            ->distinct('season')
            ->get(['season']);
        $res = array();
        for ($i = 5; $i < 9; $i++) {
            foreach ($seasons as $s) {
                $res[$i][$s->season]['stats'] = SeriesStats::where('match.league_details_id', '=', $league->id)
                    ->join('match', 'series_stats.end_match_id', '=', 'match.id')
                    ->join('game_type', 'series_stats.game_type_id', '=', 'game_type.id')
                    ->where('season', '=', $s->season)
                    ->where('game_type_id', '=', $i)
                    ->get();
                $res[$i][$s->season]['longest'] = SeriesStats::where('match.league_details_id', '=', $league->id)
                    ->where('game_type_id', '=', $i)
                    ->join('match', 'match.id', '=', 'series_stats.end_match_id')
                    ->where('season', '=', $s->season)
                    ->orderBy('current_length', "desc")
                    ->take(5)
                    ->lists('current_length');
            }
            $res[$i]['all'] = SeriesStats::where('match.league_details_id', '=', $league->id)
                ->where('game_type_id', '=', $i)
                ->join('match', 'match.id', '=', 'series_stats.end_match_id')
                ->orderBy('current_length', "desc")
                ->take(5)
                ->lists('current_length');
        }
//        return $res;
//        $country = LeagueDetails::find($league_details_id)->country;
        return View::make('ppmseries')->with(['data' => $res, 'country' => $country]);

    }

}