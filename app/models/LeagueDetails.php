<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class LeagueDetails extends Eloquent
{
    protected $table = 'leagueDetails';

    public $timestamps = false;

    public function pools()
    {
        return $this->hasMany("Pools");
    }

    public function groups()
    {
        return $this->hasMany("Groups");
    }

    public function importedSeasons()
    {
        return $this->hasMany("ImportedSeasons");
    }

    public static function getId($country, $leagueName)
    {
        $leagueDetails = LeagueDetails::where('country', '=', $country)->where('fullName', '=', $leagueName)->first();

        return $leagueDetails->id;
    }

    public static function getLeaguesAsArray()
    {
        $countries = LeagueDetails::distinct()->orderBy('country')->get(['country']);
        $data = array();
        foreach ($countries as $country) {
            $leagues = LeagueDetails::where('country', '=', $country->country)->get(array('fullName', 'id'));
            $names = array();
            foreach ($leagues as $league) {
                $seasons = ImportedSeasons::distinct()->where('league_details_id', '=', $league->id)->orderBy('season', 'DESC')->get();
                $s = array();
                foreach ($seasons as $season) {
                    array_push($s, $season->season);
                }
                $names[$league->fullName] = $s;
                // array_push($names, $league->fullName['season']);
            }
            $data[$country->country] = $names;
        }
        return $data;
    }

    public static function getLeaguesWithMatches($fromdate, $todate)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $league_details_ids = Settings::where('user_id', '=', Auth::user()->id)
            ->where('game_type_id', '<', 5)
            ->lists('league_details_id');

        if (count($league_details_ids) > 0) {
            $ids = Groups::whereIn('groups.league_details_id', $league_details_ids)
                ->join('match', 'match.groups_id', '=', 'groups.id')
                ->where('matchDate', '>=', $fromdate)
                ->where('matchDate', '<=', $todate)
                ->select('match.league_details_id as lids', 'groups.id')
                ->lists('lids', 'id');
            return $ids;
            if (count($ids) > 0) {
                $data = LeagueDetails::whereIn('id', $ids)->get(['country', 'fullName', 'id']);
                return $data;
            } else {
                $data = array();
                return $data;
            }
        } else {
            $data = array();
            return $data;
        }
    }
}

