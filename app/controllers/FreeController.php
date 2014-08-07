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
//        $datarr = array();
//        $datarr = $games;
        if (count($league_ids) > 0) {
            $standings = Standings::whereIn('league_details_id', $league_ids)->lists('place', 'team');
        } else {
            $standings = array();
        }
//        $datarr[1] = array();

//        return $datarr;
        return View::make('freeview')->with(['data' => $games, 'standings' => $standings, 'league_details_id' => -1, 'fromdate' => $fromdate, 'todate' => $todate, 'count' => $count, 'big' => $big, 'small' => $small]);

    }

    public static function manage()
    {
        return View::make('managefree');
    }

    public static function save()
    {
        $url = Input::get("url");
        $parsed = Parser::parseTeamMatches($url);
        $urlarr = explode('/', $url);
        $team_id = explode('=', $urlarr[count($urlarr) - 1])[1];
        $league = LeagueDetails::where('country', '=', $urlarr[4])->where('fullName', '=', $urlarr[5])->first();
        Match::find($parsed[1])->league_details_id = $league->id;
        $team = FreeplayTeams::firstOrNew(['user_id' => Auth::user()->id, 'team_id' => $team_id, 'league_details_id' => $league->id, 'team' => $parsed[0]]);
        $team->match_id = $parsed[1];
        $team->save();
        $game = FreeGames::firstOrNew(['user_id' => Auth::user()->id, 'team' => $team_id, 'match_id' => $parsed[1]]);
        $game->bsf = 0;
        $game->game_type_id = 1;
        $game->bookmaker_id = 1;
        $game->income = 0;
        $game->bet = 0;
        $game->odds = 3;
        $game->save();
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


}