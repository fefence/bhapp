<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Goals extends Eloquent {
    protected $table = 'goals';

    public $timestamps = false;

}

