<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Games extends Eloquent
{
    protected $table = 'games';

    public $timestamps = false;

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
            if ($recalc) {
                Games::recalculate($setting->league_details_id, $setting->multiplier, $pool->amount);
            }

        }
    }



    public static function confirmGame($game_id)
    {
        $game = Games::find($game_id);
        $nGame = $game->replicate();
        $nGame->save();
        $game->confirmed = 1;
        $game->save();
    }

}

