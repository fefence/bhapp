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
            ->join('game_type', 'game_type.id', '=', 'ppm.game_type_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'ppm.bookmaker_id')
            ->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `ppm`.*, `ppm`.id as games_id, `series`.`current_length` as 'streak'"))
            ->get();
        return $games;
    }
}