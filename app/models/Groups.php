<?php

class Groups extends Eloquent {
    protected $table = 'groups';
    public static $unguarded = true;

    public function matches() {
    	return $this->hasMany("Match");
    }

    public function league_details() {
    	return $this->hasMany("LeagueDetails");
    }

    public static function getCurrentGroupId($league_details_id)
    {
        return Groups::where('league_details_id', '=', $league_details_id)
            ->where('state', '=', '2')
            ->first();
    }

    public static function getGroupIdForDates($league_details_id, $fromdate, $todate) {
        $m = Match::where('league_details_id', '=', $league_details_id)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->first();
        if ($m != null) {
            return Groups::find($m->groups_id);
        }
        return null;
    }

    public static function getGamesForGroup($group_id, $user_id) {
        return Groups::find($group_id)->matches()
            ->join('games', 'games.match_id', '=', 'match.id')
            ->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'games.game_type_id', '=', 'game_type.id')
            ->join('standings', 'games.standings_id', '=', 'standings.id')
            ->select(DB::raw('`games`.id as games_id, `games`.*, `standings`.*, `match`.home,`match`.away,`match`.matchDate,`match`.matchTime, `match`.resultShort, homeGoals, awayGoals, bookmaker.bookmakerName, game_type.type'))
            ->where('user_id', '=', $user_id)
            ->where('confirmed', '=', 0)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('home')
            ->orderBy('streak')
            ->get();
    }

    public static function getGamesForGroupAndDates($league_detials_id, $fromdate, $todate, $user_id) {
        return Match::where('match.league_details_id', '=', $league_detials_id)
            ->join('games', 'games.match_id', '=', 'match.id')
            ->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'games.game_type_id', '=', 'game_type.id')
            ->join('standings', 'games.standings_id', '=', 'standings.id')
            ->select(DB::raw('`games`.id as games_id, `games`.*, `standings`.*, `match`.home,`match`.away,`match`.matchDate,`match`.matchTime, `match`.resultShort, homeGoals, awayGoals, bookmaker.bookmakerName, game_type.type'))
            ->where('user_id', '=', $user_id)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->where('confirmed', '=', 0)
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('home')
            ->orderBy('streak')
            ->get();
    }

    public static function getMatchesNotInGames($group_id, $standings)
    {
        $gr = Groups::find($group_id);
        $m1 = $gr->matches()
            ->whereIn('home', $standings)
            ->join('standings', 'match.home', '=', 'standings.team')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('streak')
            ->select(DB::raw("standings.id as standings_id, home, away, matchDate, matchTime, streak, team, place, match.id, resultShort, groups_id, homeGoals, awayGoals"))
            ->get();
                    // }
        $m2 = $gr->matches()
            ->whereIn('away', $standings)
            ->join('standings', 'match.away', '=', 'standings.team')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('streak')
            ->select(DB::raw("standings.id as standings_id, home, away, matchDate, matchTime, streak, team, place, match.id, resultShort, groups_id, homeGoals, awayGoals"))
            ->get();
        return array($m1, $m2);
    }

    public static function getMatchesNotInGamesForDates($league_detials_id, $standings, $fromdate, $todate)
    {
        $m1 = Match::where('match.league_details_id', '=', $league_detials_id)
            ->whereIn('home', $standings)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->join('standings', 'match.home', '=', 'standings.team')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('streak')
            ->select(DB::raw("standings.id as standings_id, home, away, matchDate, matchTime, streak, team, place, match.id, resultShort, groups_id, homeGoals, awayGoals"))
            ->get();        // }
        $m2 = Match::where('match.league_details_id', '=', $league_detials_id)
            ->whereIn('away', $standings)
            ->where('matchDate', '>=', $fromdate)
            ->where('matchDate', '<=', $todate)
            ->join('standings', 'match.away', '=', 'standings.team')
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->orderBy('streak')
            ->select(DB::raw("standings.id as standings_id, home, away, matchDate, matchTime, streak, team, place, match.id, resultShort, groups_id, homeGoals, awayGoals"))
            ->get();
        return array($m1, $m2);
    }

    public static function recalculatePPSGroup($group_id, $user_id)
    {
        $data = Games::join('match', 'match.id', '=', 'games.match_id')
            ->where('games.groups_id', '=', $group_id)
            ->where('user_id', '=', $user_id)
            ->where('resultShort', '=', '-')
            ->where('confirmed', '=', 0)
            ->select(DB::raw('games.*'))
            ->get();
        Parser::parseMatchOddsForGames($data);

        $gr = Groups::find($group_id);
        $setting = Settings::where('user_id', '=', $user_id)
            ->where('league_details_id', '=', $gr->league_details_id)
            ->first(['from', 'to', 'multiplier', 'auto']);
        $from = $setting->from;
        $teams = array();
        if ($setting->auto == '2') {
            $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                ->where('streak', '>=', $from)->lists('team', 'id');
        } else if ($setting->auto == '1') {
            $to = $setting->to;
            for ($i = 0; $i < 100; $i++) {
                $count = Standings::where('league_details_id', '=', $gr->league_details_id)
                    ->where('streak', '>=', $i);
                if ($count->count() <= $to) {
                    if ($count->count() < $from) {
                        $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                            ->where('streak', '>=', $i - 1)->lists('team', 'id');
                        break 1;
                    } else {
                        $teams = Standings::where('league_details_id', '=', $gr->league_details_id)
                            ->where('streak', '>=', $i)->lists('team', 'id');
                    }
                    break 1;
                }
            }
        }

//        return $setting;
        $league_details_id = Groups::find($group_id)->league_details_id;
        $pool = Pools::where('user_id', '=', $user_id)->where('league_details_id', '=', $league_details_id)->first();
        if (count($teams) > 0) {
            $bsfpm = $pool->amount / count($teams);
        } else {
            $bsfpm = $pool->amount;
        }
        $setting = Settings::where('user_id', '=', $user_id)->where('league_details_id', '=', $league_details_id)->first();
        $betpm = $bsfpm * $setting->multiplier;
        foreach ($data as $game) {
            $game->bsf = $bsfpm;
            $game->bet = $betpm;
            $game->income = $betpm * $game->odds;

            $game->save();
        }

        $pool = Pools::where('user_id', '=', $user_id)->where('league_details_id', '=', Groups::find($group_id)->league_details_id)->first();
        $pool->current = Games::where('groups_id', '=', $group_id)->where('user_id', '=', $user_id)->sum('bsf');
        $pool->save();
    }

}

