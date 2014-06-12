<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class PPM extends Eloquent {
    protected $table = 'ppm';

    public static $unguarded = true;

}