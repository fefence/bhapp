<?php


class PPM extends Eloquent {
    protected $table = 'ppm';

    public static $unguarded = true;

    public function match()
    {
        return $this->belongsTo('Match');
    }

//    public static function ppmForDates($fromdate, $todate)
//    {
//        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
//        $games = PPM::where('user_id', '=', Auth::user()->id)
//            ->join('match', 'match.id', '=', 'ppm.match_id')
//            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
//            ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
//            ->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
////            ->join('series', 'series.id', '=', 'ppm.series_id')
//            ->where('confirmed', '=', 0)
//            ->where('matchDate', '>=', $fromdate)
//            ->where('matchDate', '<=', $todate)
//            ->orderBy('matchDate')
//            ->orderBy('matchTime')
//            ->orderBy('game_type_id')
//            ->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `ppm`.`current_length` as 'streak', `leagueDetails`.country"))
//            ->get();
//        return $games;
//    }

    public static function ppmForDatesCountry($fromdate, $todate, $country, $user_id)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
//            ->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 0)
            ->where('ppm.country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('home')
            ->orderBy('game_type_id')
            ->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `ppm`.`current_length` as 'streak', `leagueDetails`.country"))
            ->get();
        return $games;
    }



    public static function ppmLeaguesForDates($fromdate, $todate, $user_id)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $leagues = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->groupBy('match.league_details_id')
            ->select(DB::raw("`leagueDetails`.*"))
            ->get();
        return $leagues;
    }

    public static function ppmConfirmedForLeague($fromdate, $todate, $league, $user_id)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $c = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
            ->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 1)
            ->where('team', '=', $league->country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->groupBy('ppm.match_id')
//            ->distinct('ppm.game_type_id')
            ->select(DB::raw('count(distinct(ppm.game_type_id)) as c'))
            ->get(['c']);
//        return $c;
        $count = 0;
        foreach($c as $co) {
            $count = $count + $co->c;
        }
        return $count;
    }

    public static function getPPMForMatchType($type, $match, $user_id)
    {
        $games = $match->ppm()->where('user_id', '=', $user_id)
            ->join('bookmaker', 'ppm.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'ppm.game_type_id', '=', 'game_type.id')
            ->where('type', '=', $type)
            ->where('confirmed', '=', 1)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds', 'ppm.id', 'ppm.game_type_id']);
        return $games;
    }

    public static function getPPMForConfirm($country, $fromdate, $todate, $user_id)
    {
        $conf = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 1)
            ->where('ppm.country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
//            ->where('bet', '<>', '0')
            ->lists('game_type_id');
        if (count($conf) == 0) {
            $conf = [-1];
        }
        $games = PPM::where('user_id', '=', $user_id)
            ->whereNotIn('game_type_id', $conf)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('ppm.country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->where('bet', '<>', '0')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('game_type_id')
            ->select(DB::raw("`ppm`.*"))
            ->get();
        return $games;
    }

    public static function getPPMForCountryDates($country, $fromdate, $todate, $user_id)
    {
        $games = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->select([DB::raw('ppm.id as id, ppm.*')])
            ->get();
        return $games;
    }

    public function getPPMForDates($fromdate, $todate, $user_id)
    {
        $games = PPM::where('user_id', '=', $user_id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('resultShort', '=', '-')
            ->select([DB::raw('ppm.id as id, ppm.*')])
            ->get();
        return $games;
    }
}