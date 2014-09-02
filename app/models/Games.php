<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Games extends Eloquent
{
    protected $table = 'games';

    public static $unguarded = true;

    public function group()
    {
        return $this->belongsTo("Groups");
    }

    public function match()
    {
        return $this->belongsTo('Match');
    }

    public static function recalculate($groups_id, $multiplier, $amount, $user_id)
    {
        $games = Games::where('groups_id', '=', $groups_id)->where('user_id', '=', $user_id)->get();
        $bsfpm = $amount / count($games);
        $bpm = $amount * $multiplier / count($games);
        foreach ($games as $game) {
            $game->bet = $bpm;
            $game->bsf = $bsfpm;
            $game->income = $game->odds * $game->bet;
            $game->save();
        }

    }

    public function updateGames($match)
    {
        $games = Games::where('match_id', '=', $match->id)->get();
        foreach ($games as $game) {
            if ($game->special == 1) {
                if ($match->resultShort == 'D') {

                }
                Games::updateGamesForUser($game->user_id, $match->league_details_id);
            }
            $lastforgroup = Groups::find($match->groups_id)
                ->matches()
                ->orderBy('matchDate', 'desc')
                ->orderBy('matchTime', 'desc')
                ->first();
            if ($lastforgroup->id == $match->id) {
                $all_ids = Groups::find($match->groups_id)
                    ->matches();
            }
        }
    }

    public static function updateGamesForUser($user_id, $league_details_id)
    {
        $settings = Settings::where('user_id', '=', $user_id)
            ->where('league_details_id', '=', $league_details_id)
            ->get(['from', 'to', 'multiplier']);
        foreach ($settings as $setting) {

            $gr = Groups::where('league_details_id', '=', $setting->league_details_id)->where('state', '=', 2)->first();
            // Parser::parseMatchesForGroup($gr);
            // Parser::parseLeagueSeries($gr);
            // return $gr;
            $from = $setting->from;
            $to = $setting->to;
            $teams = array();
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
            $pool = User::find($user_id)->pools()->where('league_details_id', '=', $gr->league_details_id)->first();
            $bsfpm = $pool->amount / count($teams);
            $bpm = $pool->amount * $setting->multiplier / count($teams);
            // $bsfpm = $pool;
            $recalc = false;
            foreach ($teams as $st_id => $team) {
                $match = $gr->matches()->where(function ($query) use ($team) {
                    $query->where('home', '=', $team)
                        ->orWhere('away', '=', $team);
                })->where(function ($query) {
                        $query->where('resultShort', '=', '-')
                            ->orWhere('resultShort', '=', '');
                    })
                    ->orderBy('matchDate')
                    ->orderBy('matchTime')
                    ->get();
                if (count($match) == 0) {
                    $recalc = true;
                } else if (count($match) == 1) {
                    // return $match;
                    $match = $match[0];
                    //TODO: add setting based bookmaker && special match check
                    $game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' => $st_id]);
                    $game->bet = $bpm;
                    $game->bsf = $bsfpm;
                    $game->odds = 3;
                    $game->income = $game->odds * $game->bet;
                    $game->save();
                } else if (count($match) > 1) {
                    $match = $match[0];
                    $game = Games::firstOrCreate(['user_id' => $user_id, 'match_id' => $match->id, 'game_type_id' => 1, 'bookmaker_id' => 1, 'standings_id' => $st_id]);
                    $game->bet = $bpm;
                    $game->bsf = $bsfpm;
                    $game->odds = 3;
                    $game->special = 1;
                    $game->income = $game->odds * $game->bet;
                    $game->save();
                }
            }
//            if ($recalc) {
//                Games::recalculate($setting->league_details_id, $setting->multiplier, $pool->amount, $user_id);
//            }

        }
    }


    public static function confirmGame($game_id, $game_type_id, $pl)
    {
        $aLog = new ActionLog;
        $aLog->action = "confirm";
        if ($game_type_id < 5) {
            $game = Games::find($game_id);
            $aLog->type = "pps";
        } else if ($game_type_id >= 5 && $game_type_id < 9) {
            if ($pl) {
                $game = PPMPlaceHolder::find($game_id);
                $aLog->type = "ppm_placeholder";
            } else {
                $game = PPM::find($game_id);
                $aLog->type = "ppm";
                $plh = PPMPlaceHolder::getForGame($game);
                if ($plh != null) {
                    $plh->bsf = $plh->bsf + $game->bet;
                    $plh->save();
                }
            }
        }
        $aLog->amount = $game->bet;
        $aLog->element_id = $game->id;
        $aLog->save();

        $league = Match::find($game->match_id);
        $pool = Pools::where('user_id', '=', $game->user_id)
            ->where('league_details_id', '=', $league->league_details_id)
            ->where('game_type_id', '=', $game_type_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $game->user_id)->first();

        $main->account = $main->account - $game->bet;
        $main->save();
        $pool->account = $pool->account - $game->bet;
        $pool->save();

        $nGame = $game->replicate();
        $nGame->save();
        $game->confirmed = 1;
        $game->save();
    }

    public static function deleteGame($game_id, $game_type_id)
    {
        $aLog = new ActionLog;
        $aLog->action = "delete";
        if ($game_type_id < 5) {
            $game = Games::find($game_id);
            $aLog->type = "pps";
        } else if ($game_type_id >= 5 && $game_type_id < 9) {
            $game = PPM::find($game_id);
            $aLog->type = "ppm";
        }
        $aLog->amount = $game->bet;
        $aLog->element_id = $game->id;
        $aLog->save();
        $league = Match::find($game->match_id);
        $pool = Pools::where('user_id', '=', $game->user_id)
            ->where('league_details_id', '=', $league->league_details_id)
            ->where('game_type_id', '=', $game_type_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $game->user_id)->first();
        $main->account = $main->account + $game->bet;
        $pool->account = $pool->account + $game->bet;
        $pool->save();
        $game->delete();
    }

    public static function addGame($groups_id, $standings_id, $user_id, $match_id)
    {
        $game = new Games;
        $game->user_id = $user_id;
        $game->bet = 0;
        $game->odds = 3;
        $game->match_id = $match_id;
        $game->game_type_id = 1;
        $game->special = 0;
        $game->standings_id = $standings_id;
        $game->groups_id = $groups_id;
        $game->save();
    }

    /**
     * @param $match
     * @return mixed
     */
    public static function confirmedGamesForMatch($match, $user_id, $team)
    {
        $games = $match->games()->where('user_id', '=', $user_id)
            ->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'games.game_type_id', '=', 'game_type.id')
            ->join('standings', 'games.standings_id', '=', 'standings.id')
            ->where('confirmed', '=', 1)
            ->where('team', '=', $team)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds', 'games.id', 'game_type_id']);
        return $games;
    }

    /**
     * @param $match
     * @return mixed
     */
    public static function notConfirmedGamesForMatch($match, $user_id)
    {
        $games = $match->games()->where('user_id', '=', $user_id)
            ->join('bookmaker', 'games.bookmaker_id', '=', 'bookmaker.id')
            ->join('game_type', 'games.game_type_id', '=', 'game_type.id')
            ->join('standings', 'games.standings_id', '=', 'standings.id')
            ->where('confirmed', '=', 0)
            ->get(['bookmakerName', 'type', 'bet', 'bsf', 'income', 'odds', 'games.id', 'game_type_id']);
        return $games;
    }

    /**
     * @param $groups_id
     * @param $user_id
     * @return mixed
     */
    public static function getGamesForGroupUser($groups_id, $user_id)
    {
        $games = User::find($user_id)
            ->games()
            ->where('groups_id', '=', $groups_id)
            ->where('confirmed', '=', 0)
            ->get();
        return $games;
    }
}

