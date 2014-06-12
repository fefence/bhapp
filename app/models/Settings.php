<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Settings extends Eloquent {
    protected $table = 'settings';

    public $timestamps = false;

    public static $unguarded = true;


}

