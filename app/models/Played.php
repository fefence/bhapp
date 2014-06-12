<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Played extends Eloquent {
    protected $table = 'played';

    public $timestamps = false;

}

