<?php

class FreeController extends \BaseController
{
    public static function display($fromdate = "", $todate = "")
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, '');
        $games = FreeGames::gamesForDates($fromdate, $todate);
        $count = array();
        $league_ids = array();
        foreach ($games as $g) {
            $count[$g->id] = FreeGames::where('user_id', '=', Auth::user()->id)->where('match_id', '=', $g->match_id)->where('confirmed', '=', 1)->where('game_type_id', '=', $g->game_type_id)->count();
            array_push($league_ids, $g->league_details_id);
        }
        if (count($league_ids) > 0) {
            $standings = Standings::whereIn('league_details_id', $league_ids)->lists('place', 'team');
        } else {
            $standings = array();
        }

        return View::make('freeview')->with(['data' => $games, 'standings' => $standings, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' => $todate, 'count' => $count, 'big' => $big, 'small' => $small]);

    }

    public static function manage()
    {
        return View::make('managefree');
    }

    public static function save()
    {
        $url = Input::get("url");
//        return $parsed;
        $urlarr = explode('/', $url);
        $team_id = explode('=', $urlarr[count($urlarr) - 1])[1];
        $league = LeagueDetails::where('country', '=', $urlarr[4])->where('fullName', '=', $urlarr[5])->first();
        $parsed = Parser::parseTeamMatches($url, $league->id);

        Parser::parseLeagueStandings($league->id);
        Match::find($parsed[1])->league_details_id = $league->id;
        $team = FreeplayTeams::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'league_details_id' => $league->id, 'team' => $parsed[0]]);
        $team->match_id = $parsed[1];
        $team->save();
        $game = FreeGames::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'match_id' => $parsed[1]]);
        $game->bsf = 0;
        $game->game_type_id = 1;
        $game->bookmaker_id = 1;
        $game->income = 0;
        $game->bet = 0;
        $game->odds = 3;
        $game->save();
        $pool = FreePool::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id]);
        $pool->save();
        return Redirect::back()->with("message", "saved");
    }

    public static function test()
    {
        $url = "http://d.livescore.in/x/feed/dc_4_YRPevQZt";
        $curl = curl_init ( $url );

        curl_setopt( $curl, CURLOPT_URL, $url );
        $header = array (
//            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//            'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3',
                'Accept-Encoding:gzip,deflate,sdch',
//            'Accept-Language:en-US,en;q=0.8',
//            'Cache-Control:max-age=0',
//            'Connection:keep-alive',
        "X-Requested-With: XMLHttpRequest",
            "X-GeoIP: 1",
            "Referer: http://d.livescore.in/x/feed/proxy",
            "X-Fsign: SW9D1eZo",
            'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19');
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_REFERER, 'http://d.livescore.in/x/feed/proxy' );
        curl_setopt( $curl, CURLOPT_ENCODING, 'gzip,deflate,sdch' );
        curl_setopt( $curl, CURLOPT_AUTOREFERER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );

        $html = curl_exec( $curl );
        return $html;
        return gzdecode($html);
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTML( $html );
        print_r($html);
    }

    public static function saveTable() {
        $game_id = Input::get('row_id');
        $team_id = Input::get('id');
        $value = Input::get('value');
        $game = FreeGames::find($game_id);
        $col = Input::get('column');
        $bsf = "";
//        return $col;
        if ($col == 9 || $col == '9') {
//            return $value;
            $bsf = $game->bsf;
            $game->bsf = $value;
            $game->save();
            $pool = FreePool::where('user_id', '=', $game->user_id)->where('team_id', '=', $team_id)->first();
            $pool->amount = $pool->amount - $bsf + $value;
            $pool->save();
//            $bsf = round($pool->current, 2);

            $aLog = new ActionLog;
            $aLog->type = "free";
            $aLog->action = "change bsf";
            $aLog->amount = $value;
            $aLog->element_id = $game->id;
            $aLog->save();
//            $bsf = $pool->current();
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
        $game = FreeGames::find($game_id);
        return $game->bsf . "#" . $game->bet . "#" . $game->odds . "#" . $game->income . "#" . $bsf;

//        return FreeGames::find($game_id);
    }

    public static function confirmGame($game_id) {
        $free = FreeGames::find($game_id);

        $aLog = new ActionLog;
        $aLog->action = "confirm";
        $aLog->type = "free";
        $aLog->amount = $free->bet;
        $aLog->element_id = $free->id;
        $aLog->save();

        $pool = FreePool::where('user_id', '=', $free->user_id)
            ->where('team_id', '=', $free->team_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $free->user_id)->first();

        $main->account = $main->account - $free->bet;
        $main->save();
        $pool->account = $pool->account - $free->bet;
        $pool->save();
        $newFree = $free->replicate();
        $free->confirmed = 1;
        $newFree->save();
        $free->save();
        return Redirect::back()->with('message', 'Bet confirmed');

    }

    public static function deleteGame($game_id) {
        $free = FreeGames::find($game_id);

        $aLog = new ActionLog;
        $aLog->action = "delete";
        $aLog->type = "free";
        $aLog->amount = $free->bet;
        $aLog->element_id = $free->id;
        $aLog->save();
        $pool = FreePool::where('user_id', '=', $free->user_id)
            ->where('team_id', '=', $free->team_id)
            ->first();
        $main = CommonPools::where('user_id', '=', $free->user_id)->first();
        $main->account = $main->account + $free->bet;
        $pool->account = $pool->account + $free->bet;
        $pool->save();
        $free->delete();
        return Redirect::back()->with('message', 'Bet deleted');

    }

}