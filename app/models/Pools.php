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
}

