<?php

class LivescoreController extends \BaseController
{
    public function livescore($fromdate = '', $todate = '')
    {
        list($fromdate, $todate) = StringsUtil::calculateDates($fromdate, $todate);
        $todate2 = date('Y-m-d', strtotime($todate. ' + 1 days'));;

//        $ids = PPM::where('user_id', '=', Auth::user()->id)->lists('match_id');
//        return $ids;
        $user_id = Auth::user()->id;
        $leagues = Settings::where('user_id', '=', $user_id)
            ->where('game_type_id', '>=', 5)
            ->where('game_type_id', '<=', 8)
            ->lists('league_details_id');
        if (count($leagues) > 0) {
            $ms = Match::where(function ($q) use ($fromdate, $todate, $todate2) {
                $q->where(function ($q) use ($fromdate, $todate, $todate2) {
                    $q->where('matchDate', '>=', $fromdate)
                        ->where('matchDate', '<=', $todate);
                });
                $q->orWhere(function ($q) use ($fromdate, $todate, $todate2) {
                    $q->where('matchDate', '=', $todate2)
                        ->where('matchTime', '<', '11:00:00');
                });
            })
                ->whereIn('league_details_id', $leagues)
                ->join('leagueDetails', 'leagueDetails.id', '=', 'match.league_details_id')
                ->select('match.id as id')
                ->lists('match.id');
        } else {
            $ms = array();
        }
        $pps = Games::where('user_id', '=', $user_id)->lists('match_id');
        $free = FreeGames::where('freeplay_teams.user_id', '=', $user_id)
            ->where('freeplay.user_id', '=', $user_id)
            ->join('freeplay_teams', "freeplay_teams.team_id", '=', 'freeplay.team_id')
            ->where('hidden', '=', 0)
            ->select(DB::raw('freeplay.match_id as match_id'))
            ->lists('match_id');

        $all_ids = array_merge(array_merge($ms, $pps), $free);
        $matches = Match::getAllMatchesForDates($fromdate, $todate, $todate2, $all_ids);
        $res = array();
        foreach ($matches as $match) {
            $res[$match->id] = array();
            $res[$match->id]['streak'] = "";
            if (in_array($match->id, $ms)) {
                $game = PPM::where('match_id', '=', $match->id)
                    ->where('confirmed', '=', 1)
                    ->where('user_id', '=', $user_id)
                    ->orderBy('id')
                    ->first();
                if($game != null){
                    $res[$match->id]['streak'] = $game->current_length;
                }
            } if (in_array($match->id, $free)) {
                $game = FreeGames::where('match_id', '=', $match->id)
                    ->where('confirmed', '=', 1)
                    ->where('user_id', '=', $user_id)
                    ->orderBy('id')
                    ->first();
                if($game != null){
                    $res[$match->id]['streak'] = $game->current_length;
                }
            } else if (in_array($match->id, $pps)) {
                $game = Games::where('match_id', '=', $match->id)
                    ->where('confirmed', '=', 1)
                    ->where('user_id', '=', $user_id)
                    ->orderBy('id')
                    ->first();
                if ($game != null) {
                    $res[$match->id]['streak'] = Standings::where('league_details_id', '=', $match->league_details_id)
                            ->where('team', '=', $match->home)
                            ->first()->streak . "/" . Standings::where('league_details_id', '=', $match->league_details_id)
                            ->where('team', '=', $match->away)
                            ->first()->streak;
                }
            }
            $res[$match->id]['match'] = $match;
            $res[$match->id]['game'] = $game;

        }
//        return $res;
        list($big, $small) = StringsUtil::calculateHeading($fromdate, $todate, -1);
//        foreach($matches as $match){
//            Match::getScore($match);
//        }
//        return $matches;
        return View::make('livescore')->with(['hide_all' => true, 'matches' => $res, 'fromdate' => $fromdate, 'todate' => $todate, 'big' => $big, 'small' => $small]);
    }

    public static function matchScore($match_id)
    {

        $url = "http://d.livescore.in/x/feed/d_su_" . $match_id . "_en_4";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_URL, $url);
        $header = array(
            'Accept-Encoding:gzip,deflate,sdch',
            "X-Fsign: SW9D1eZo",
            'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        );
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_REFERER, 'http://kat.ph');
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $html = curl_exec($curl);
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTML($html);

//        return $html;
//        return View::make('matchfeed')->with(['html' => Parser::parseLivescoreForMatch($dom)]);
        return $html;
    }

    public static function getMatchCurrentRes($id) {
        $html = LivescoreController::matchScore($id);
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTML($html);
        $finder = new DomXPath($dom);
        $classname="p1_home";
        $h1 = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $home = '';
        $away = '';
        if ($h1->length > 0) {
            $home = $h1->item(0)->nodeValue;
        }
        $classname="p2_home";
        $h2 = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        if ($h2->length > 0) {
            $home = $home + $h2->item(0)->nodeValue;
        }
        $classname="p1_away";
        $a1 = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        if ($a1->length > 0) {
            $away = $a1->item(0)->nodeValue;
        }
        $classname="p2_away";
        $a2 = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        if ($a2->length > 0) {
            $away = $away + $a2->item(0)->nodeValue;
        }
        return $home." <span>:</span> ".$away;
    }


}