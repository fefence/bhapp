<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class LeagueDetails extends Eloquent
{
    protected $table = 'leagueDetails';

    public $timestamps = false;

    public function pools()
    {
        return $this->hasMany("Pools");
    }

    public function groups()
    {
        return $this->hasMany("Groups");
    }

    public function importedSeasons()
    {
        return $this->hasMany("ImportedSeasons");
    }

    public static function getId($country, $leagueName)
    {
        $leagueDetails = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $leagueName)->first();

        return $leagueDetails->id;
    }

}

