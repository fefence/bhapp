<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class PPM extends Eloquent {
    protected $table = 'ppm';

    public static $unguarded = true;

    public function match()
    {
        return $this->belongsTo('Match');
    }

    public static function ppmForDates($fromdate, $todate)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPM::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm.match_id')
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
            ->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->orderBy('game_type_id')
            ->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `series`.`current_length` as 'streak', `leagueDetails`.country"))
            ->get();
        return $games;
    }

    public static function getPPMForMatchType($type, $match)
    {
        $games = $match->ppm()->where('user_id', '=', Auth::user()->id)
            ->join('bookmaker', 'ppm.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'ppm.game_type_id', '=', 'game_type.id')
            ->where('type', '=', $type)
            ->where('confirmed', '=', 1)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds']);
        return $games;
    }

}