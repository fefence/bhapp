<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Settings extends Eloquent
{
    protected $table = 'settings';

    public $timestamps = false;

    public static $unguarded = true;

    public static function getSettingsAsArray()
    {

        $ppm = array();

        $pps = array();

        $ids = Standings::distinct('league_details_id')->lists('league_details_id');
        $ids2 = Series::where('game_type_id', '>', 4)->lists('league_details_id');
        if (count($ids) == 0) {
            $ids = [-1];
        }
        if (count($ids2) == 0) {
            $ids2 = [-1];
        }
        $league = LeagueDetails::orderBy('country')->whereIn('id', $ids)->orWhereIn('id', $ids2)->get();

        foreach ($league as $l) {
            if ($l->ppm == 1) {
                if (!array_key_exists($l->country, $ppm)) {
                    $ppm[$l->country] = array();
                }
                $ppm[$l->country][0] = $l->id;
                $ppm[$l->country][5] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 5)->where('user_id', '=', Auth::user()->id)->first();
                $ppm[$l->country][6] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 6)->where('user_id', '=', Auth::user()->id)->first();
                $ppm[$l->country][7] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 7)->where('user_id', '=', Auth::user()->id)->first();
                $ppm[$l->country][8] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 8)->where('user_id', '=', Auth::user()->id)->first();

            } else if ($l->pps == 1){
                if (!array_key_exists($l->country, $pps)) {
                    $pps[$l->country] = array();
                }
                if (!array_key_exists($l->fullName, $pps[$l->country])) {
                    $pps[$l->country][$l->fullName] = array();
                }
                $pps[$l->country][$l->fullName][0] = $l->id;
                $pps[$l->country][$l->fullName][1] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 1)->where('user_id', '=', Auth::user()->id)->first();
                $pps[$l->country][$l->fullName][2] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 2)->where('user_id', '=', Auth::user()->id)->first();
                $pps[$l->country][$l->fullName][3] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 3)->where('user_id', '=', Auth::user()->id)->first();
                $pps[$l->country][$l->fullName][4] = Settings::where('league_details_id', '=', $l->id)->where('game_type_id', '=', 4)->where('user_id', '=', Auth::user()->id)->first();
            }
        }
        return array($pps, $ppm);
    }
}

