<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class CommonPools extends Eloquent {
    protected $table = 'common_pools';

    public function user() {
    	return $this->belongsTo('User');
    }

}

