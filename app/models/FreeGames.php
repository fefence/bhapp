<?php


class FreeGames extends Eloquent{

    protected $table = 'freeplay';
    public static $unguarded = true;
    public $timestamps = false;

    public static function gamesForDates($fromdate, $todate)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = FreeGames::where('freeplay.user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'freeplay.match_id')
            ->join('game_type', 'game_type.id', '=', 'freeplay.game_type_id')
            ->join('bookmaker', 'bookmaker.id', '=', 'freeplay.bookmaker_id')
            ->join('freeplay_teams', 'freeplay_teams.team_id', '=', "freeplay.team_id")
            ->where('freeplay_teams.user_id', '=', Auth::user()->id)
            ->where('confirmed', '=', 0)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->orderBy('game_type_id')
            ->select(DB::raw("`game_type`.*, `match`.*, `bookmaker`.*, `freeplay`.*, `freeplay`.id as games_id, freeplay_teams.team"))
            ->get();
        return $games;
    }
}