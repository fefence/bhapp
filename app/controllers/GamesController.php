<?php

class GamesController extends \BaseController
{
    public function getGamesForGroup($league_details_id, $fromdate = "", $todate = "")
    {
        $pool = Pools::getPoolForUserLeague(Auth::user()->id, $league_details_id);
        $gr = Groups::getCurrentGroupId($league_details_id);
        $games = User::find(Auth::user()->id)->games()->where('groups_id', '=', $gr->id)->lists('standings_id');
        $standings = Standings::whereNotIn('id', $games)->lists('team');
//        $matches = Groups::find($gr->id)->matches()->get(['id']);
        $count = array();

        if ($fromdate == '' && $todate == '') {
            $data = Groups::getGamesForGroup($gr->id);
            $grey = Groups::getMatchesNotInGames($gr->id, $standings);
            list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        } else {
            list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
            $data = Groups::getGamesForGroupAndDates($gr->id, $fromdate, $todate);
            $grey = Groups::getMatchesNotInGamesForDates($gr->id, $standings, $fromdate, $todate);
        }
        foreach($data as $g) {
            $match = Match::find($g->match_id);
            $count[$g->id] = count(Games::confirmedGamesForMatch($match, Auth::user()->id, $g->team));
        }
        return View::make('matches')->with(['data' => $data, 'grey' => $grey, 'count' => $count, 'pool' => $pool, 'league_details_id' => $league_details_id, 'group' => $gr->id, 'fromdate' => $fromdate, 'todate' => $todate, 'base' => "group/$league_details_id"]);
    }

    public function confirmGame($game_id, $game_type_id)
    {
        Games::confirmGame($game_id, $game_type_id);
        return Redirect::back();
    }

    public function removeMatch($game_id)
    {
        return Redirect::back();
    }

    public function getMatchOddsForGames($groups_id)
    {
        $games = Games::getGamesForGroupUser($groups_id, Auth::user()->id);
//        return $games;
        Parser::parseMatchOddsForGames($games);
//        return $games;
        return Redirect::back();
    }

    public function saveTable()
    {
        $game_id = Input::get('row_id');
        $game_type_id = Input::get('id');
        if ($game_type_id > 4 && $game_type_id < 9) {
            $game = PPM::find($game_id);
        } else if ($game_type_id > 0 && $game_type_id < 5) {
            $game = Games::find($game_id);
        }
        $value = Input::get('value');
        $col = Input::get('column');
        $bsf = "";
        if ($col == 9 || $col == '9') {
            $game->bsf = $value;
            $game->save();

            if ($game_type_id > 0 && $game_type_id < 5) {
                $m = Match::find($game->match_id);
                $matches = Groups::find($m->groups_id)->matches()->lists('id');
                $bsf = Games::where('user_id', '=', Auth::user()->id)->whereIn('match_id', $matches)->sum('bsf');
                $pool = Pools::where('user_id', '=', Auth::user()->id)->where('league_details_id', '=', $m->league_details_id)->first();
                $pool->current = $bsf;
                $pool->save();
            }
        }
        if ($col == 10 || $col == '10') {
            $game->bet = $value;
            $game->income = $game->odds * $value;
            $game->save();

        }
        if ($col == 11 || $col == '11') {
            $game->odds = $value;
            $game->income = $value * $game->bet;
            $game->save();

        }
        return $game->bsf . "#" . $game->bet . "#" . $game->odds . "#" . $game->income . "#" . $bsf;
    }
}