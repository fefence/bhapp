<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Pools extends Eloquent {
    protected $table = 'pools';

    public static $unguarded = true;

    public function user() {
    	return $this->belongsTo("User");
    }

    public function league_details() {
    	return $this->belongsTo("LeagueDetails");
    }

    public static function getPoolForUserLeague($user_id, $league_details_id)
    {
        return User::find($user_id)->pools()->where('league_details_id', '=', $league_details_id)->first();
    }

    public static function getPPSPoolsQForUser($user_id)
    {
        $ppmpoolsq = Pools::where('user_id', '=', $user_id)
            ->where('pools.ppm', '=', 1)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'pools.league_details_id')
            ->select([DB::raw('pools.*, leagueDetails.displayName, leagueDetails.country')]);
        return $ppmpoolsq;
    }

    public static function getPPMPoolsQForUser($user_id)
    {
        $ppspoolsq = Pools::where('user_id', '=', $user_id)
            ->where('pools.ppm', '=', 0)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'pools.league_details_id')
            ->select([DB::raw('pools.*, leagueDetails.displayName, leagueDetails.country')]);
        return $ppspoolsq;
    }
}

