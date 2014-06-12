<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Groups extends Eloquent {
    protected $table = 'groups';
    public static $unguarded = true;

    public function matches() {
    	return $this->hasMany("Match");
    }

    public function league_details() {
    	return $this->hasMany("LeagueDetails");
    }

}

