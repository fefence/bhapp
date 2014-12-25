<?php


class Placeholder extends Eloquent{
    protected $table = 'placeholders';

    public $timestamps = false;

    public static $unguarded = true;

    public static function createPlaceholders($matches, $game_type_id, $user_id) {
    }
}