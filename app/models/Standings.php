<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Standings extends Eloquent {
    protected $table = 'standings';

    public $timestamps = false;

    public static $unguarded = true;


}

