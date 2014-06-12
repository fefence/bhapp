<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class PlayedSim extends Eloquent {
    protected $table = 'played_sim';

    public $timestamps = false;
    public static $unguarded = true;

}

