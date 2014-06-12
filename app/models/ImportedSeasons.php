<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class ImportedSeasons extends Eloquent {
	protected $table = 'importedSeasons';

    public $timestamps = false;

    public function leagueDetails() {

    	return $this->belongsTo("LeagueDetails");

    }
}

