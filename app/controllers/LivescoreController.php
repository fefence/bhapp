<?php

class LivescoreController extends \BaseController
{
    public function livescore($fromdate = '', $todate = '')
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        //TODO parse from livescore
        $today = date('Y-m-d', time());
//        $ids = PPM::where('user_id', '=', Auth::user()->id)->lists('match_id');
//        return $ids;
        $leagues = Settings::where('user_id', '=', Auth::user()->id)
            ->where('game_type_id', '>=', 5)
            ->where('game_type_id', '<=', 8)
            ->lists('league_details_id');
        $ms = Match::where('matchDate', '<=', $todate)
            ->where('matchDate', '>=', $fromdate)
            ->whereIn('league_details_id', $leagues)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->select('match.id as id')
            ->lists('match.id');
        $pps = Games::where('user_id', '=', Auth::user()->id)->lists('match_id');
        $matches = Match::where('matchDate', '<=', $todate)
            ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
            ->where('matchDate', '>=', $fromdate)
            ->where(function($query) use ($ms, $pps){
                $query->whereIn('match.id', $ms)
                    ->orWhereIn('match.id', $pps);
            })
            ->orderBy('matchDate')
            ->orderBy('matchTime')
            ->select(DB::raw("`match`.*, `leagueDetails`.country, `leagueDetails`.displayName"))
            ->get();
        $res = array();
//        return $matches;
        foreach($matches as $match) {
            if (in_array($match->id, $ms)) {
                $game = PPM::where('match_id', '=', $match->id)
                    ->where('confirmed', '=', 1)
                    ->where('user_id', '=', Auth::user()->id)
                    ->orderBy('id')
                    ->first();
            } else {
                $game = Games::where('match_id', '=', $match->id)
                    ->where('confirmed', '=', 1)
                    ->where('user_id', '=', Auth::user()->id)
                    ->orderBy('id')
                    ->first();
            }
            $res[$match->id] = array();
            $res[$match->id]['match'] = $match;
            $res[$match->id]['game'] = $game;
        }
//        return $res;
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, -1);
//        foreach($matches as $match){
//            Match::getScore($match);
//        }
//        return $matches;
        return View::make('livescore')->with(['matches' => $res, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }

    public static function matchScore($match_id) {

        $url = "http://d.livescore.in/x/feed/d_su_".$match_id."_en_4";
        $curl = curl_init ( $url );

        curl_setopt( $curl, CURLOPT_URL, $url );
        $header = array (
            'Accept-Encoding:gzip,deflate,sdch',
            "X-Fsign: SW9D1eZo",
            'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19');
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_REFERER, 'http://kat.ph' );
        curl_setopt( $curl, CURLOPT_ENCODING, 'gzip,deflate,sdch' );
        curl_setopt( $curl, CURLOPT_AUTOREFERER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );

        $html = curl_exec($curl);
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTML($html);

        return Parser::parseLivescoreForMatch($dom);
        return $html;
    }
}