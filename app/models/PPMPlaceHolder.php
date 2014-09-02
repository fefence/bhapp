<?php



class PPMPlaceHolder extends Eloquent{
    protected $table = 'ppm_placeholder';

    public static $unguarded = true;

    public function match()
    {
        return $this->belongsTo('Match');
    }

    public static function getForGame($game) {
        return PPMPlaceHolder::where('user_id', '=', $game->user_id)
            ->where('series_id', '=', $game->series_id)
            ->where('confirmed', '=', 0)
            ->where('current_length', '=', $game->current_length + 1)
            ->first();
    }

    public static function placeholdersForDatesCountry($fromdate, $todate, $country)
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $games = PPMPlaceHolder::where('user_id', '=', Auth::user()->id)
            ->join('match', 'match.id', '=', 'ppm_placeholder.match_id')
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->join('game_type', 'game_type.id', '=', 'ppm_placeholder.game_type_id')
//            ->join('bookmaker', 'bookmaker.id', '=', 'ppm_placeholder.bookmaker_id')
//            ->join('series', 'series.id', '=', 'ppm.series_id')
            ->where('confirmed', '=', 0)
            ->where('active', '=', 1)
            ->where('ppm_placeholder.country', '=', $country)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('home')
            ->orderBy('game_type_id')
            ->select(DB::raw("`game_type`.*, `match`.*, `ppm_placeholder`.*, `ppm_placeholder`.id as games_id, `ppm_placeholder`.`current_length` as 'streak', `leagueDetails`.country"))
            ->get();
        return $games;
    }
}