<?php

class MatchController extends BaseController
{

    public function showWelcome($country, $leagueName, $season)
    {

        return View::make('stats');

    }

    public function getStats($country, $leagueName, $season)
    {

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
        $over = '??'; //Match::matchesForSeason($leagueId, $season)->where('homeGoals + awayGoals', '>', 2.5)->count();
        $under = '??'; //Match::matchesForSeason($leagueId, $season)->where('homeGoals + awayGoals', '<', 2.5)->count();

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

    public function getSequences($country, $leagueName, $season)
    {

        $seq = array();
        $leagueId = LeagueDetails::getId($country, $leagueName);
        $teams = Match::matchesForSeason($leagueId, $season)->distinct('home')->get(array('home'));
        foreach ($teams as $team) {
            $regexp = $team->home;

            $matches = Match::matchesForSeason($leagueId, $season)
                ->where(function ($query) use ($regexp) {
                    $query->where('home', '=', $regexp)
                        ->orWhere('away', '=', $regexp);
                })
                ->orderBy('matchDate', 'desc')
                ->get(['home', 'away', 'resultShort', 'homeGoals', 'awayGoals', 'id', 'matchDate']);

            $seq = array_add($seq, $team->home, $matches);
        }

        return $seq;
    }


    private function getUniqueResults($leagueId, $season)
    {

        return Match::where('league_details_id', '=', $leagueId)->where('season', '=', $season)->select('homeGoals', 'awayGoals', DB::raw('count(*) as total'))
            ->groupBy('homeGoals', 'awayGoals')->orderBy('homeGoals', 'ASC')->orderBy('awayGoals', 'ASC')
            ->get();
    }

}
