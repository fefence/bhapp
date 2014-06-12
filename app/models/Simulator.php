<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Simulator extends Eloquent {
    protected $table = 'simulator';

    public $timestamps = false;
    public static $unguarded = true;

    // public function match() {
    // 	return $this->hasOne('Match');
    // }

}

